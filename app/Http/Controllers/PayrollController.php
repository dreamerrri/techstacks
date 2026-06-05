<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\Payroll\PayrollComputationEngine;
use App\Services\SssContributionService;
use App\Services\PhilHealthContributionService;
use App\Services\PagIbigContributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
    /**
     * Show payroll preview for employees
     * Admin and HR can see all employees' payroll
     * Regular employees can only see their own payroll
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $isHR = $user->isHR();

        // Filter employees based on role
        if ($isAdmin || $isHR) {
            // Admin and HR can see all active employees
            $query = Employee::active();
        } else {
            // Regular employees can only see their own payroll
            $employee = $user->employee;
            if (!$employee) {
                return redirect()->route('dashboard')
                    ->with('error', 'No employee record found for your account.');
            }
            $query = Employee::where('id', $employee->id);
        }

        // Search functionality
        if ($request->filled('search') && ($isAdmin || $isHR)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter by department (admin and HR only)
        if ($request->filled('department') && ($isAdmin || $isHR)) {
            $query->where('department', $request->department);
        }

        $employees = $query->orderByRaw('CASE WHEN EXISTS (SELECT 1 FROM payroll_inputs WHERE payroll_inputs.employee_id = employees.id) THEN 0 ELSE 1 END')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();
        $departments = Employee::active()->distinct()->pluck('department');

        // Calculate payroll for each employee
        $payrollData = [];
        foreach ($employees as $employee) {
            $payrollData[$employee->id] = $this->calculatePayroll($employee);
        }

        return view('payroll.index', compact('employees', 'departments', 'payrollData', 'isAdmin', 'isHR'));
    }

    /**
     * Show detailed payroll preview for a specific employee
     */
    public function show(Employee $employee)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $isHR = $user->isHR();

        // Check access: admin and HR can see any employee, regular employees can only see themselves
        if (!$isAdmin && !$isHR) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $employee->id) {
                return redirect()->route('payroll.index')
                    ->with('error', 'You can only view your own payroll.');
            }
        }

        $payrollData = $this->calculatePayroll($employee);

        return view('payroll.show', compact('employee', 'payrollData', 'isAdmin', 'isHR'));
    }

    /**
     * Calculate payroll for an employee using PayrollComputationEngine
     */
    private function calculatePayroll(Employee $employee): array
    {
        $basicSalary = $employee->basic_salary;
        $salaryType = $employee->salary_type;

        // Calculate hourly and daily rates based on salary type
        // Using same formula as ManualPayrollAttendanceController for consistency: (basic_salary * 12) / 52 / 40 * 8
        $dailyRate = match ($salaryType) {
            'Monthly' => round(($basicSalary * 12) / 52 / 40 * 8, 2),
            'Daily' => $basicSalary,
            'Hourly' => $basicSalary * 8,
            default => round(($basicSalary * 12) / 52 / 40 * 8, 2),
        };

        $hourlyRate = match ($salaryType) {
            'Monthly' => $dailyRate / 8,
            'Daily' => $basicSalary / 8,
            'Hourly' => $basicSalary,
            default => $dailyRate / 8,
        };

        // Get payroll input data for current payroll period
        $payrollInput = $employee->currentPayrollInput();

        // If no current payroll input, try to get the latest payroll input record
        if (!$payrollInput) {
            $payrollInput = $employee->latestPayrollInput();
            \Log::info('No current payroll period input, using latest payroll input for employee ' . $employee->id);
        }

        // Prepare attendance array for engine (handle null payroll input)
        $attendanceData = [];
        if ($payrollInput) {
            $attendanceData = [
                'days_worked' => $payrollInput->days_worked ?? 0,
                'regular_hours' => ($payrollInput->days_worked ?? 0) * 8, // Calculate regular hours from days worked
                'overtime_hours' => $payrollInput->overtime_hours ?? 0,
                'late_hours' => $payrollInput->late_hours ?? 0,
                'night_differential_hours' => $payrollInput->night_differential_hours ?? 0,
                'holiday_days' => $payrollInput->holiday_days ?? 0,
            ];
            \Log::info('Payroll input data found for employee ' . $employee->id, $attendanceData);
        } else {
            // Default to zero if no payroll input record
            $attendanceData = [
                'days_worked' => 0,
                'regular_hours' => 0,
                'overtime_hours' => 0,
                'late_hours' => 0,
                'night_differential_hours' => 0,
                'holiday_days' => 0,
            ];
            \Log::warning('No payroll input data found for employee ' . $employee->id);
        }

        // Prepare employee data for engine
        // Use daily rate converted to monthly salary for consistency with ManualPayrollAttendanceController
        $employeeData = [
            'monthly_salary' => $dailyRate * 22,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ];

        // Fetch active allowances and benefits
        $allowances = $employee->activeAllowances()->pluck('amount')->toArray();
        $benefits = $employee->activeBenefits()->pluck('amount')->toArray();

        // Calculate government contributions based on gross pay
        // First, calculate gross pay without deductions to get contribution bases
        $engine = new PayrollComputationEngine();
        $previewResult = $engine->compute($employeeData, $attendanceData, [], $benefits, $allowances, [], true);
        $grossPay = $previewResult['gross_pay'];

        // Government contributions using official bracket tables or custom values if set
        // SSS Contribution using official bracket table (Circular No. 2024-006)
        $sssService = new SssContributionService();
        $sssCalculation = $sssService->calculate($grossPay);
        $sssContribution = $employee->custom_sss_contribution ?? $sssCalculation['employee_share'];

        // PhilHealth Contribution using official 2025/2026 table
        $philHealthService = new PhilHealthContributionService();
        $philHealthCalculation = $philHealthService->calculate($grossPay);
        $philhealthContribution = $employee->custom_philhealth_contribution ?? $philHealthCalculation['employee_share'];

        // Pag-IBIG Contribution using official 2026 table
        $pagIbigService = new PagIbigContributionService();
        $pagIbigCalculation = $pagIbigService->calculate($grossPay);
        $pagibigContribution = $employee->custom_pagibig_contribution ?? $pagIbigCalculation['employee_share'];

        // Calculate withholding tax
        $totalContributions = $sssContribution + $philhealthContribution + $pagibigContribution;
        $taxableIncome = $grossPay - $totalContributions;
        \Log::info('Withholding tax calculation', [
            'gross_pay' => $grossPay,
            'sss_contribution' => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution' => $pagibigContribution,
            'total_contributions' => $totalContributions,
            'taxable_income' => $taxableIncome,
        ]);
        $withholdingTax = $this->calculateTax($grossPay, $totalContributions);
        \Log::info('Withholding tax result', ['withholding_tax' => $withholdingTax]);

        // Get manual deductions from payroll input
        $manualDeductions = $payrollInput ? ($payrollInput->deductions ?? 0) : 0;

        // Total deductions
        $deductions = [$sssContribution, $philhealthContribution, $pagibigContribution, $withholdingTax, $manualDeductions];

        // Calculate final payroll with deductions
        $result = $engine->compute($employeeData, $attendanceData, $deductions, $benefits, $allowances, [], false);

        // Add additional fields for view compatibility
        return [
            'basic_salary' => $basicSalary,
            'salary_type' => $salaryType,
            'hourly_rate' => $hourlyRate,
            'daily_rate' => $dailyRate,
            'base_pay' => $result['basic_salary'],
            'overtime_pay' => $result['overtime_pay'],
            'night_differential_pay' => $result['night_differential'],
            'holiday_pay' => $result['holiday_pay'],
            'late_deduction' => $result['late_deduction'],
            'allowance_benefits' => $result['allowances'] + $result['benefits'],
            'allowances' => $result['allowances'],
            'benefits' => $result['benefits'],
            'gross_pay' => $result['gross_pay'],
            'sss_contribution' => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution' => $pagibigContribution,
            'withholding_tax' => $withholdingTax,
            'manual_deductions' => $manualDeductions,
            'total_deductions' => $result['deductions'],
            'net_pay' => $result['net_pay'],
            'taxable_income' => $taxableIncome,
            'attendance_data' => $attendanceData,
        ];
    }

    /**
     * Calculate withholding tax based on annual computation
     * Formula: (Salary - Contributions) * 12 = Annual Total
     * Annual Total - 250,000 = Taxable Annual Total
     * Taxable Annual Total * 15% = Tax Rate
     * Tax Rate / 12 = Gross Pay Tax
     */
    private function calculateTax(float $grossPay, float $totalContributions): float
    {
        // Calculate annual total: (Salary - Contributions) * 12
        $annualTotal = ($grossPay - $totalContributions) * 12;
        
        // Subtract tax exemption (250,000 from income tax table)
        $taxableAnnualTotal = $annualTotal - 250000;
        
        // If taxable annual total is zero or negative, no tax
        if ($taxableAnnualTotal <= 0) {
            return 0;
        }
        
        // Apply 15% tax rate (graduated tax rates from tax table)
        $taxRate = $taxableAnnualTotal * 0.15;
        
        // Convert back to monthly tax
        $grossPayTax = $taxRate / 12;
        
        return round($grossPayTax, 2);
    }

public function downloadPayslip(Employee $employee)
{
    $user = Auth::user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();

    if (!$isAdmin && !$isHR) {
        $userEmployee = $user->employee;
        if (!$userEmployee || $userEmployee->id !== $employee->id) {
            return redirect()->route('payroll.index')
                ->with('error', 'You can only download your own payslip.');
        }
    }

    $payrollData = $this->calculatePayroll($employee);

    $pdf = Pdf::loadView('payroll.payslip', [
        'employee'    => $employee,
        'payroll'     => $payrollData,
        'generatedAt' => now()->format('F d, Y h:i A'),
    ])->setPaper('a4', 'portrait');

    return $pdf->download("payslip-{$employee->employee_id}.pdf");
}
}
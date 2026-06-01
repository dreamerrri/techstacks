<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\Payroll\PayrollComputationEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $hourlyRate = match ($salaryType) {
            'Monthly' => $basicSalary / 22 / 8, // 22 days, 8 hours per day
            'Daily' => $basicSalary / 8,
            'Hourly' => $basicSalary,
            default => $basicSalary / 22 / 8,
        };

        $dailyRate = match ($salaryType) {
            'Monthly' => $basicSalary / 22,
            'Daily' => $basicSalary,
            'Hourly' => $basicSalary * 8,
            default => $basicSalary / 22,
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
        $employeeData = [
            'monthly_salary' => $basicSalary,
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

        // Government contributions (use employee-specific rates or default Philippine standard rates)
        $sssRate = $employee->sss_rate ?? 0.045;
        $sssCap = $employee->sss_cap ?? 900;
        $philhealthRate = $employee->philhealth_rate ?? 0.0225;
        $philhealthCap = $employee->philhealth_cap ?? 1500;
        $pagibigRate = $employee->pagibig_rate ?? 0.02;
        $pagibigCap = $employee->pagibig_cap ?? 100;

        $sssContribution = min($grossPay * $sssRate, $sssCap);
        $philhealthContribution = min($grossPay * $philhealthRate, $philhealthCap);
        $pagibigContribution = min($grossPay * $pagibigRate, $pagibigCap);

        // Calculate withholding tax
        $taxableIncome = $grossPay - $sssContribution - $philhealthContribution - $pagibigContribution;
        $withholdingTax = $this->calculateTax($taxableIncome);

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
     * Calculate withholding tax based on Philippine tax brackets
     */
    private function calculateTax(float $taxableIncome): float
    {
        if ($taxableIncome <= 20832) {
            return 0;
        } elseif ($taxableIncome <= 33333) {
            return ($taxableIncome - 20832) * 0.20;
        } elseif ($taxableIncome <= 66667) {
            return 2500 + ($taxableIncome - 33333) * 0.25;
        } elseif ($taxableIncome <= 166667) {
            return 10833.33 + ($taxableIncome - 66667) * 0.30;
        } elseif ($taxableIncome <= 666667) {
            return 40833.33 + ($taxableIncome - 166667) * 0.32;
        } else {
            return 200833.33 + ($taxableIncome - 666667) * 0.35;
        }
    }
}

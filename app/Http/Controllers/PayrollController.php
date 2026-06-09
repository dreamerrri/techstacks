<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollPeriod;
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

        // Load all payroll periods for the dropdown
        $payrollPeriods = PayrollPeriod::orderByDesc('cutoff_start')->get();

        // Determine selected period (default to latest)
        $selectedPeriodId = $request->input('payroll_period_id');
        $selectedPeriod = $selectedPeriodId
            ? $payrollPeriods->firstWhere('id', $selectedPeriodId)
            : $payrollPeriods->first();

        // Filter employees based on role
        if ($isAdmin || $isHR) {
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

        $employees = $query
            ->with([
                'payrollInputs' => fn($q) => $q->where('payroll_period_id', optional($selectedPeriod)->id),
                'allowances'    => fn($q) => $q->where('is_active', true),
                'benefits'      => fn($q) => $q->where('is_active', true),
            ])
            ->orderByRaw('CASE WHEN EXISTS (SELECT 1 FROM payroll_inputs WHERE payroll_inputs.employee_id = employees.id) THEN 0 ELSE 1 END')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        $departments = Employee::active()->distinct()->pluck('department');

        // Calculate payroll for each employee using the selected period
        $payrollData = [];
        foreach ($employees as $employee) {
            $payrollData[$employee->id] = $this->calculatePayroll($employee, $selectedPeriod);
        }

        return view('payroll.index', compact(
            'employees', 'departments', 'payrollData',
            'isAdmin', 'isHR', 'payrollPeriods', 'selectedPeriod'
        ));
    }

    /**
     * Show detailed payroll preview for a specific employee
     */
    public function show(Employee $employee, Request $request)
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

        // Respect period filter carried over from index
        $selectedPeriod = null;
        if ($request->filled('payroll_period_id')) {
            $selectedPeriod = PayrollPeriod::find($request->payroll_period_id);
        }

        $employee->load([
            'payrollInputs' => fn($q) => $q->where('payroll_period_id', optional($selectedPeriod)->id),
            'allowances'    => fn($q) => $q->where('is_active', true),
            'benefits'      => fn($q) => $q->where('is_active', true),
        ]);

        $payrollData = $this->calculatePayroll($employee, $selectedPeriod);

        return view('payroll.show', compact('employee', 'payrollData', 'isAdmin', 'isHR', 'selectedPeriod'));
    }

    /**
     * Calculate payroll for an employee using PayrollComputationEngine.
     * If a specific PayrollPeriod is given, data is scoped to that period.
     * Otherwise falls back to current → latest input.
     */
    private function calculatePayroll(Employee $employee, ?PayrollPeriod $period = null): array
    {
        $basicSalary = $employee->basic_salary;
        $salaryType = $employee->salary_type;

        // Calculate hourly and daily rates based on salary type
        $hourlyRate = match ($salaryType) {
            'Monthly' => $basicSalary / 22 / 8,
            'Daily'   => $basicSalary / 8,
            'Hourly'  => $basicSalary,
            default   => $basicSalary / 22 / 8,
        };

        $dailyRate = match ($salaryType) {
            'Monthly' => $basicSalary / 22,
            'Daily'   => $basicSalary,
            'Hourly'  => $basicSalary * 8,
            default   => $basicSalary / 22,
        };

        // Resolve payroll input — use already-loaded relation if available, else query
        if ($period) {
            $payrollInput = $employee->relationLoaded('payrollInputs')
                ? $employee->payrollInputs->first()
                : $employee->payrollInputs()->where('payroll_period_id', $period->id)->first();

            if (!$payrollInput) {
                \Log::info("No payroll input for employee {$employee->id} in period {$period->id}");
            }
        } else {
            $payrollInput = $employee->currentPayrollInput();

            if (!$payrollInput) {
                $payrollInput = $employee->latestPayrollInput();
                \Log::info('No current payroll period input, using latest payroll input for employee ' . $employee->id);
            }
        }

        // Prepare attendance array for engine (handle null payroll input)
        if ($payrollInput) {
            $attendanceData = [
                'days_worked'              => $payrollInput->days_worked ?? 0,
                'regular_hours'            => ($payrollInput->days_worked ?? 0) * 8,
                'overtime_hours'           => $payrollInput->overtime_hours ?? 0,
                'late_hours'               => $payrollInput->late_hours ?? 0,
                'night_differential_hours' => $payrollInput->night_differential_hours ?? 0,
                'holiday_days'             => $payrollInput->holiday_days ?? 0,
            ];
            \Log::info('Payroll input data found for employee ' . $employee->id, $attendanceData);
        } else {
            $attendanceData = [
                'days_worked'              => 0,
                'regular_hours'            => 0,
                'overtime_hours'           => 0,
                'late_hours'               => 0,
                'night_differential_hours' => 0,
                'holiday_days'             => 0,
            ];
            \Log::warning('No payroll input data found for employee ' . $employee->id);
        }

        // Prepare employee data for engine
        $employeeData = [
            'monthly_salary'         => $basicSalary,
            'working_days_per_month' => 22,
            'working_hours_per_day'  => 8,
        ];

        // Fetch active allowances and benefits — use loaded relations if available
        $allowances = $employee->relationLoaded('allowances')
            ? $employee->allowances->pluck('amount')->toArray()
            : $employee->activeAllowances()->pluck('amount')->toArray();

        $benefits = $employee->relationLoaded('benefits')
            ? $employee->benefits->pluck('amount')->toArray()
            : $employee->activeBenefits()->pluck('amount')->toArray();

        // First pass: compute gross pay without deductions
        $engine        = new PayrollComputationEngine();
        $previewResult = $engine->compute($employeeData, $attendanceData, [], $benefits, $allowances, [], true);
        $grossPay      = $previewResult['gross_pay'];

        // Government contributions
        // Use custom values if set, otherwise use official bracket tables (for consistency with PayrollInput::computePay())
        $sssService      = new SssContributionService();
        $sssCalculation  = $sssService->calculate($grossPay);
        $sssContribution = $employee->custom_sss_contribution ?? $sssCalculation['employee_share'];

        $philHealthService      = new PhilHealthContributionService();
        $philHealthCalculation  = $philHealthService->calculate($grossPay);
        $philhealthContribution = $employee->custom_philhealth_contribution ?? $philHealthCalculation['employee_share'];

        $pagIbigService      = new PagIbigContributionService();
        $pagIbigCalculation  = $pagIbigService->calculate($grossPay);
        $pagibigContribution = $employee->custom_pagibig_contribution ?? $pagIbigCalculation['employee_share'];

        // Withholding tax
        $totalContributions = $sssContribution + $philhealthContribution + $pagibigContribution;
        $taxableIncome      = $grossPay - $totalContributions;

        \Log::info('Withholding tax calculation', [
            'gross_pay'               => $grossPay,
            'sss_contribution'        => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution'    => $pagibigContribution,
            'total_contributions'     => $totalContributions,
            'taxable_income'          => $taxableIncome,
        ]);

        $withholdingTax = $this->calculateTax($grossPay, $totalContributions);
        \Log::info('Withholding tax result', ['withholding_tax' => $withholdingTax]);

        // Manual deductions from payroll input
        $manualDeductions = $payrollInput ? ($payrollInput->deductions ?? 0) : 0;

        // Second pass: compute net pay with all deductions
        $deductions = [$sssContribution, $philhealthContribution, $pagibigContribution, $withholdingTax, $manualDeductions];
        $result     = $engine->compute($employeeData, $attendanceData, $deductions, $benefits, $allowances, [], false);

        return [
            'basic_salary'            => $basicSalary,
            'salary_type'             => $salaryType,
            'hourly_rate'             => $hourlyRate,
            'daily_rate'              => $dailyRate,
            'base_pay'                => $result['basic_salary'],
            'overtime_pay'            => $result['overtime_pay'],
            'night_differential_pay'  => $result['night_differential'],
            'holiday_pay'             => $result['holiday_pay'],
            'late_deduction'          => $result['late_deduction'],
            'allowance_benefits'      => $result['allowances'] + $result['benefits'],
            'allowances'              => $result['allowances'],
            'benefits'                => $result['benefits'],
            'gross_pay'               => $result['gross_pay'],
            'sss_contribution'        => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution'    => $pagibigContribution,
            'withholding_tax'         => $withholdingTax,
            'manual_deductions'       => $manualDeductions,
            'total_deductions'        => $result['deductions'],
            'net_pay'                 => $result['net_pay'],
            'taxable_income'          => $taxableIncome,
            'attendance_data'         => $attendanceData,
        ];
    }

    /**
     * Calculate withholding tax based on annual computation.
     * Formula: (gross_pay - contributions) * 12 → subtract 250k exemption → 15% → ÷ 12
     */
    private function calculateTax(float $grossPay, float $totalContributions): float
    {
        $annualTotal        = ($grossPay - $totalContributions) * 12;
        $taxableAnnualTotal = $annualTotal - 250000;

        if ($taxableAnnualTotal <= 0) {
            return 0;
        }

        $taxRate     = $taxableAnnualTotal * 0.15;
        $grossPayTax = $taxRate / 12;

        return round($grossPayTax, 2);
    }

    /**
     * Download payslip PDF for an employee.
     */
    public function downloadPayslip(Employee $employee, Request $request)
    {
        $user    = Auth::user();
        $isAdmin = $user->isAdmin();
        $isHR    = $user->isHR();

        if (!$isAdmin && !$isHR) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $employee->id) {
                return redirect()->route('payroll.index')
                    ->with('error', 'You can only download your own payslip.');
            }
        }

        // Respect period filter if passed
        $selectedPeriod = null;
        if ($request->filled('payroll_period_id')) {
            $selectedPeriod = PayrollPeriod::find($request->payroll_period_id);
        }

        $employee->load([
            'payrollInputs' => fn($q) => $q->where('payroll_period_id', optional($selectedPeriod)->id),
            'allowances'    => fn($q) => $q->where('is_active', true),
            'benefits'      => fn($q) => $q->where('is_active', true),
        ]);

        $payrollData = $this->calculatePayroll($employee, $selectedPeriod);

        $pdf = Pdf::loadView('payroll.payslip', [
            'employee'    => $employee,
            'payroll'     => $payrollData,
            'generatedAt' => now()->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("payslip-{$employee->employee_id}.pdf");
    }
}
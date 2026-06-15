<?php

namespace App\Http\Controllers;

use App\Models\PayrollInput;
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

        // If no period is selected and none exist, handle gracefully
        if (!$selectedPeriod && $payrollPeriods->isNotEmpty()) {
            $selectedPeriod = $payrollPeriods->first();
        }

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
            ->orderByRaw('CASE WHEN EXISTS (SELECT 1 FROM payroll_inputs WHERE payroll_inputs.employee_id = employees.id AND payroll_inputs.payroll_period_id = ' . (optional($selectedPeriod)->id ?? 0) . ') THEN 0 ELSE 1 END')
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

        // Calculate working days from payroll period if available, otherwise use default 22
        $workingDaysPerMonth = $period 
            ? PayrollPeriod::calculateWorkingDays($period->cutoff_start, $period->cutoff_end) 
            : 22;

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

        // Use daily_rate from payroll input if available, otherwise calculate from basic salary
        if ($payrollInput && $payrollInput->daily_rate) {
            $dailyRate = $payrollInput->daily_rate;
            $hourlyRate = $dailyRate / 8;
        } else {
            // Calculate hourly and daily rates based on salary type and working days
            $hourlyRate = match ($salaryType) {
                'Monthly' => $basicSalary / $workingDaysPerMonth / 8,
                'Daily'   => $basicSalary / 8,
                'Hourly'  => $basicSalary,
                default   => $basicSalary / $workingDaysPerMonth / 8,
            };

            $dailyRate = match ($salaryType) {
                'Monthly' => $basicSalary / $workingDaysPerMonth,
                'Daily'   => $basicSalary,
                'Hourly'  => $basicSalary * 8,
                default   => $basicSalary / $workingDaysPerMonth,
            };
        }

        // Prepare attendance array for engine (handle null payroll input)
        if ($payrollInput) {
            $attendanceData = [
                'days_worked'              => $payrollInput->days_worked ?? 0,
                'regular_hours'            => ($payrollInput->days_worked ?? 0) * 8,
                'overtime_hours'           => $payrollInput->overtime_hours ?? 0,
                'late_hours'               => $payrollInput->late_hours ?? 0,
                'night_differential_hours' => $payrollInput->night_differential_hours ?? 0,
                'night_diff_hours'        => $payrollInput->night_differential_hours ?? 0,
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
                'night_diff_hours'        => 0,
                'holiday_days'             => 0,
            ];
            \Log::warning('No payroll input data found for employee ' . $employee->id);
        }

        // Prepare employee data for engine
        $employeeData = [
            'monthly_salary'         => $basicSalary,
            'daily_rate'             => $dailyRate,
            'working_hours_per_day'  => 8,
        ];

        // Fetch active allowances and benefits — use loaded relations if available
        $allowances = $employee->relationLoaded('allowances')
            ? $employee->allowances->pluck('amount')->toArray()
            : $employee->activeAllowances()->pluck('amount')->toArray();

        $benefits = $employee->relationLoaded('benefits')
            ? $employee->benefits->pluck('amount')->toArray()
            : $employee->activeBenefits()->pluck('amount')->toArray();

        // Get cutoff dates from payroll period if available
        $cutoffStart = $period ? $period->cutoff_start->toDateString() : null;
        $cutoffEnd = $period ? $period->cutoff_end->toDateString() : null;

        // First pass: compute gross pay without deductions
        $engine        = new PayrollComputationEngine();
        $previewResult = $engine->compute($employeeData, $attendanceData, [], $benefits, $allowances, [], true, $cutoffStart, $cutoffEnd);
        $grossPay      = $previewResult['gross_pay'];

        // Government contributions are fixed based on monthly basic salary and divided per cutoff (semi-monthly)
        // SSS Contribution using official bracket table (Circular No. 2024-006)
        $sssService      = new SssContributionService();
        $sssCalculation  = $sssService->calculate($employee->basic_salary);
        $sssMonthly      = $employee->custom_sss_contribution ?? $sssCalculation['employee_share'];
        $sssContribution = round($sssMonthly / 2, 2);

        $philHealthService      = new PhilHealthContributionService();
        $philHealthCalculation  = $philHealthService->calculate($employee->basic_salary);
        $philHealthMonthly      = $employee->custom_philhealth_contribution ?? $philHealthCalculation['employee_share'];
        $philhealthContribution = round($philHealthMonthly / 2, 2);

        $pagIbigService      = new PagIbigContributionService();
        $pagIbigCalculation  = $pagIbigService->calculate($employee->basic_salary);
        $pagIbigMonthly      = $employee->custom_pagibig_contribution ?? $pagIbigCalculation['employee_share'];
        $pagibigContribution = round($pagIbigMonthly / 2, 2);

        // Current cutoff contributions
        $currentCutoffContributions = $sssContribution + $philhealthContribution + $pagibigContribution;

        // Withholding tax calculation only if period is in second half of month (16-30,31)
        $withholdingTax = 0;
        $firstCutoffGrossPay = 0;
        $firstCutoffNetPay = 0;
        $firstCutoffContributions = 0;

        if ($period) {
            // Fetch 1st cutoff data for the same month
            $firstCutoffPeriod = PayrollPeriod::whereYear('cutoff_start', $period->cutoff_start->year)
                ->whereMonth('cutoff_start', $period->cutoff_start->month)
                ->whereDay('cutoff_start', '<=', 15)
                ->first();
            
            if ($firstCutoffPeriod) {
                $firstCutoffPayrollInput = PayrollInput::where('payroll_period_id', $firstCutoffPeriod->id)
                    ->where('employee_id', $employee->id)
                    ->first();
                if ($firstCutoffPayrollInput) {
                    $firstCutoffGrossPay = $firstCutoffPayrollInput->gross_pay;
                    $firstCutoffNetPay = $firstCutoffPayrollInput->net_pay;
                    // For 1st cutoff, contributions are also fixed
                    $firstCutoffContributions = $currentCutoffContributions;
                }
            }
        }

        $totalMonthlyGross = $firstCutoffGrossPay + $grossPay;
        $totalMonthlyContributions = $currentCutoffContributions * 2; // Total monthly fixed contributions
        
        // Calculate total monthly allowances (current cutoff + first cutoff)
        $currentCutoffAllowances = array_sum($allowances);
        $firstCutoffAllowances = 0;
        if ($firstCutoffPayrollInput) {
            $firstCutoffAllowances = $firstCutoffPayrollInput->allowances;
        }
        $totalMonthlyAllowances = $firstCutoffAllowances + $currentCutoffAllowances;

        if ($period && $period->isSecondHalfOfMonth()) {
            \Log::info('Withholding tax calculation in PayrollController', [
                'total_monthly_gross'         => $totalMonthlyGross,
                'total_monthly_contributions' => $totalMonthlyContributions,
                'total_monthly_allowances'    => $totalMonthlyAllowances,
            ]);

            $withholdingTax = $this->calculateTax($totalMonthlyGross, $totalMonthlyContributions, $totalMonthlyAllowances);
            \Log::info('Withholding tax result', ['withholding_tax' => $withholdingTax]);
        }

        $taxableIncome = $totalMonthlyGross - $totalMonthlyContributions;

        // Manual deductions and reimbursements from payroll input
        $manualDeductions = $payrollInput ? ($payrollInput->deductions ?? 0) : 0;
        $reimbursements = $payrollInput ? ($payrollInput->reimbursements ?? 0) : 0;

        // Second pass: compute net pay with all deductions
        $deductions = [$sssContribution, $philhealthContribution, $pagibigContribution, $withholdingTax, $manualDeductions];
        $result     = $engine->compute($employeeData, $attendanceData, $deductions, $benefits, $allowances, [], false, $cutoffStart, $cutoffEnd);

        $currentCutoffNetPay = $result['net_pay'] + $reimbursements;

        return [
            'basic_salary'            => $basicSalary,
            'salary_type'             => $salaryType,
            'hourly_rate'             => $result['hourly_rate'],
            'daily_rate'              => $result['daily_rate'],
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
            'reimbursements'          => $reimbursements,
            'total_deductions'        => $result['deductions'],
            'net_pay'                 => $currentCutoffNetPay,
            'taxable_income'          => $taxableIncome,
            'attendance_data'         => $attendanceData,
            
            // Cutoff-based breakdown
            'first_cutoff_gross_pay'   => $firstCutoffGrossPay,
            'second_cutoff_gross_pay'  => $period && $period->isSecondHalfOfMonth() ? $grossPay : 0,
            'first_cutoff_net_pay'     => $firstCutoffNetPay,
            'second_cutoff_net_pay'    => $period && $period->isSecondHalfOfMonth() ? $currentCutoffNetPay : 0,
            'first_cutoff_contributions' => $firstCutoffContributions,
            'second_cutoff_contributions' => $period && $period->isSecondHalfOfMonth() ? $currentCutoffContributions : ($period && !$period->isSecondHalfOfMonth() ? 0 : 0),
            'current_cutoff_contributions' => $currentCutoffContributions,
            'total_monthly_gross_pay'  => $totalMonthlyGross,
            'total_monthly_net_pay'    => $firstCutoffNetPay + ($period && $period->isSecondHalfOfMonth() ? $currentCutoffNetPay : 0),
        ];
    }

    /**
     * Calculate withholding tax based on monthly computation
     * Formula: (Total Monthly Gross - Total Monthly Contributions - Total Monthly Allowances) - 33,333 = taxablePay
     * taxablePay * 20% + 1875 = Withholding Tax
     */
    private function calculateTax(float $totalMonthlyGross, float $totalMonthlyContributions, float $totalMonthlyAllowances = 0): float
    {
        // Calculate taxable income: Total Gross - Total Monthly Contributions - Total Monthly Allowances
        // Allowances are considered as advance paychecks and should be deducted from taxable income
        $taxableIncome = $totalMonthlyGross - $totalMonthlyContributions - $totalMonthlyAllowances;
        
        // Subtract lower limit of bracket (33,333) to get taxablePay (excess)
        $taxablePay = $taxableIncome - 33333;
        
        // If taxable income is below 33,333, no tax for this bracket
        if ($taxablePay <= 0) {
            return 0;
        }
        
        // Apply formula: taxablePay * 20% + 1875
        $withholdingTax = ($taxablePay * 0.20) + 1875;
        
        return round($withholdingTax, 2);
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
            if (!$selectedPeriod) {
                return redirect()->route('payroll.index')
                    ->with('error', 'Payroll period not found.');
            }
        }

        $employee->load([
            'payrollInputs' => fn($q) => $q->where('payroll_period_id', optional($selectedPeriod)->id),
            'allowances'    => fn($q) => $q->where('is_active', true),
            'benefits'      => fn($q) => $q->where('is_active', true),
        ]);

        // Check if payroll input exists for the selected period
        if ($selectedPeriod) {
            $payrollInput = $employee->payrollInputs->first();
            if (!$payrollInput) {
                return redirect()->route('payroll.index', ['payroll_period_id' => $selectedPeriod->id])
                    ->with('error', 'No payroll data found for this employee in the selected period.');
            }
        }

        $payrollData = $this->calculatePayroll($employee, $selectedPeriod);

        $pdf = Pdf::loadView('payroll.payslip', [
    'employee'            => $employee,
    'payroll'             => $payrollData,
    'selectedPeriod' => $selectedPeriod,
    'generatedAt'         => now()->format('F d, Y h:i A'),
    'authorizedSignatory' => strtoupper($user->name),  // already have $user above
])->setPaper('a4', 'portrait');

        return $pdf->download("payslip-{$employee->employee_id}.pdf");
    }
}
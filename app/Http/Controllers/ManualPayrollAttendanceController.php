<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollInput;
use App\Models\PayrollAdjustment;
use App\Models\WorkRequest;
use App\Models\FinancialRequest;
use App\Models\CashAdvancePayment;
use App\Services\Payroll\PayrollComputationEngine;
use App\Services\SssContributionService;
use App\Services\PhilHealthContributionService;
use App\Services\PagIbigContributionService;
use App\Services\WithholdingTaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManualPayrollAttendanceController extends Controller
{
    /**
     * GET /manual-payroll-attendance
     * Show the manual payroll attendance encoding interface
     */
 public function index(Request $request)
{
    $query = PayrollPeriod::whereNotIn('status', ['archived']);

    // Apply year filter
    if ($request->filled('year')) {
        $query->whereYear('cutoff_start', $request->year);
    }

    // Apply month filter
    if ($request->filled('month')) {
        $query->whereMonth('cutoff_start', $request->month);
    }

    // Apply phase filter
    if ($request->filled('phase')) {
        if ($request->phase == '1') {
            $query->whereDay('cutoff_start', '<=', 15);
        } elseif ($request->phase == '2') {
            $query->whereDay('cutoff_start', '>', 15);
        }
    }

    // Apply status filter
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    try {
        $periods = $query->with('payrollInputs')
            ->orderByDesc('cutoff_start')
            ->get();
    } catch (\Exception $e) {
        $periods = $query->orderByDesc('cutoff_start')
            ->get();
    }

    // Get available years, months for filters
    $availableYears = PayrollPeriod::whereNotIn('status', ['archived'])
        ->selectRaw('DISTINCT YEAR(cutoff_start) as year')
        ->orderByDesc('year')
        ->pluck('year')
        ->toArray();

    $availableMonths = PayrollPeriod::whereNotIn('status', ['archived'])
        ->selectRaw('DISTINCT MONTH(cutoff_start) as month')
        ->orderBy('month')
        ->pluck('month')
        ->toArray();

    return Inertia::render('ManualPayrollAttendance/Index', [
        'periods' => $periods->map(fn($period) => [
            'id'            => $period->id,
            'cutoff_start'  => $period->cutoff_start?->toDateString(),
            'cutoff_end'    => $period->cutoff_end?->toDateString(),
            'payroll_date'  => $period->payroll_date?->toDateString(),
            'status'        => $period->status,
            'period_label'  => $period->period_label,
            'encoded_count' => $period->payrollInputs ? $period->payrollInputs->count() : 0,
            'total_gross'   => $period->total_gross_pay,
            'total_net'     => $period->total_net_pay,
        ]),
        'availableYears'  => $availableYears,
        'availableMonths' => $availableMonths,
        'filters'         => [
            'year'   => $request->year,
            'month'  => $request->month,
            'phase'  => $request->phase,
            'status' => $request->status,
        ],
    ]);
}

    /**
     * GET /manual-payroll-attendance/period/{payrollPeriod}
     * Show encoding form for a specific payroll period
     */
    public function showPeriod(PayrollPeriod $payrollPeriod)
    {
        if (!$payrollPeriod) {
            abort(404, 'Payroll period not found');
        }

        try {
            $payrollPeriod->load(['payrollInputs.employee', 'payrollInputs.adjustments']);
        } catch (\Exception $e) {
            // If loading fails, try loading without nested relationships
            $payrollPeriod->load('payrollInputs');
        }

        // Auto-encode employees who have attendance records but no payroll input
        $encodedIds = $payrollPeriod->payrollInputs ? $payrollPeriod->payrollInputs->pluck('employee_id')->toArray() : [];

        // Get employees with attendance records for this period
        $employeesWithAttendance = \App\Models\Attendance::whereBetween('date', [$payrollPeriod->cutoff_start->toDateString(), $payrollPeriod->cutoff_end->toDateString()])
            ->distinct()
            ->pluck('employee_id')
            ->toArray();

        // For each employee with attendance but no payroll input, create one
        foreach ($employeesWithAttendance as $employeeId) {
            if (!in_array($employeeId, $encodedIds)) {
                $employee = Employee::find($employeeId);
                if ($employee && !$employee->is_archived) {
                    // Get computed days from attendance
                    $computedDays = \App\Models\Attendance::where('employee_id', $employeeId)
                        ->whereBetween('date', [$payrollPeriod->cutoff_start->toDateString(), $payrollPeriod->cutoff_end->toDateString()])
                        ->sum('computed_days');

                    // Get approved work requests for this period (only if work date has passed)
                    $approvedRequests = WorkRequest::where('employee_id', $employeeId)
                        ->where('status', 'approved')
                        ->whereBetween('work_date', [$payrollPeriod->cutoff_start->toDateString(), $payrollPeriod->cutoff_end->toDateString()])
                        ->where('work_date', '<=', now()->toDateString())
                        ->get();

                    // Calculate special work from approved requests
                    $weekendsWorked = $approvedRequests->where('request_type', 'weekend')->count();
                    $overtimeHours = $approvedRequests->where('request_type', 'overtime')->sum('calculated_overtime_hours');
                    $holidayDays = $approvedRequests->where('request_type', 'holiday')->count();

                    // Only create if there are computed days or special work
                    if ($computedDays > 0 || $weekendsWorked > 0 || $overtimeHours > 0 || $holidayDays > 0) {
                        $dailyRate = $this->computeDailyRate($employee);

                        $payrollInput = new PayrollInput([
                            'payroll_period_id' => $payrollPeriod->id,
                            'employee_id' => $employeeId,
                            'daily_rate' => $dailyRate,
                            'rate_type' => 'daily',
                            'days_worked' => $computedDays,
                            'weekends_worked' => $weekendsWorked,
                            'overtime_hours' => $overtimeHours,
                            'late_hours' => 0,
                            'holiday_days' => $holidayDays,
                            'night_differential_hours' => 0,
                            'allowances' => round(($employee->activeAllowances->sum('amount') + $employee->activeBenefits->sum('amount')) / 2, 2),
                            'deductions' => 0,
                            'deductions_remarks' => '',
                            'reimbursements' => 0,
                            'reimbursements_remarks' => '',
                        ]);
                        $payrollInput->computePay()->save();

                        // Add to encoded IDs
                        $encodedIds[] = $employeeId;
                    }
                }
            }
        }

        // Reload payroll period with newly created inputs
        $payrollPeriod->load(['payrollInputs.employee', 'payrollInputs.adjustments']);
        $encodedIds = $payrollPeriod->payrollInputs ? $payrollPeriod->payrollInputs->pluck('employee_id')->toArray() : [];

        // Get employees not yet encoded for this period
        if (empty($encodedIds)) {
            $unencodedEmployees = Employee::where('is_archived', false)->get();
        } else {
            $unencodedEmployees = Employee::whereNotIn('id', $encodedIds)
                ->where('is_archived', false)
                ->get();
        }

        return Inertia::render('ManualPayrollAttendance/Period', [
            'payrollPeriod' => $this->periodPayload($payrollPeriod),
            'unencodedEmployees' => $unencodedEmployees->map(fn($emp) => [
                'id'               => $emp->id,
                'employee_id'      => $emp->employee_id,
                'first_name'       => $emp->first_name,
                'last_name'        => $emp->last_name,
                'department'       => $emp->department,
                'employment_status'=> $emp->employment_status,
            ])->values(),
        ]);
    }

    /**
     * GET /manual-payroll-attendance/employee/{employee}/period/{payrollPeriod}
     * Show encoding form for a specific employee in a payroll period
     */
    public function showEmployeeForm(PayrollPeriod $payrollPeriod, Employee $employee)
    {
        if (!$payrollPeriod || !$employee) {
            abort(404, 'Payroll period or employee not found');
        }

        // Check if employee already has payroll input for this period
        $payrollInput = PayrollInput::where('payroll_period_id', $payrollPeriod->id)
            ->where('employee_id', $employee->id)
            ->with('adjustments')
            ->first();

        // Compute daily rate based on employee's salary type
        $dailyRate = $this->computeDailyRate($employee);

        // Load employee's active allowances and benefits
        $employee->load(['activeAllowances', 'activeBenefits']);

        // Get computed days from attendance records for this payroll period
        $computedDaysFromAttendance = \App\Models\Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$payrollPeriod->cutoff_start->toDateString(), $payrollPeriod->cutoff_end->toDateString()])
            ->sum('computed_days');

        // Get approved work requests for this period to auto-populate special work fields (only if work date has passed)
        $approvedRequests = \App\Models\WorkRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('work_date', [$payrollPeriod->cutoff_start->toDateString(), $payrollPeriod->cutoff_end->toDateString()])
            ->where('work_date', '<=', now()->toDateString())
            ->get();

        // Calculate special work from approved requests
        $weekendsWorked = $approvedRequests->where('request_type', 'weekend')->count();
        $overtimeHours = $approvedRequests->where('request_type', 'overtime')->sum('calculated_overtime_hours');
        $holidayDays = $approvedRequests->where('request_type', 'holiday')->count();

        // If payroll input exists, use the actual saved values from database
        // Work request values are shown as reference in the approved requests section above
        // HR can manually override these values as needed

        // Cash advance info
        $allCashAdvances = FinancialRequest::where('employee_id', $employee->id)
            ->where('request_type', 'cash_advance')
            ->where('status', 'approved')
            ->get();
        $outstandingCashAdvances = $allCashAdvances->filter(fn($r) => $r->amount_paid < $r->amount);
        $fullyPaidCashAdvances = $allCashAdvances->filter(fn($r) => $r->amount_paid >= $r->amount);
        $totalOutstandingBalance = $outstandingCashAdvances->sum(fn($r) => $r->amount - $r->amount_paid);
        $existingPeriodPayments = CashAdvancePayment::where('payroll_period_id', $payrollPeriod->id)
            ->whereHas('financialRequest', fn($q) => $q->where('employee_id', $employee->id))
            ->exists();

        $totalAllowances = $employee->activeAllowances->sum('amount');
        $totalBenefits = $employee->activeBenefits->sum('amount');

        return Inertia::render('ManualPayrollAttendance/EmployeeForm', [
            'payrollPeriod' => $this->periodPayload($payrollPeriod),
            'employee' => [
                'id'                => $employee->id,
                'employee_id'       => $employee->employee_id,
                'first_name'        => $employee->first_name,
                'last_name'         => $employee->last_name,
                'position'          => $employee->position,
                'department'        => $employee->department,
                'basic_salary'      => $employee->basic_salary,
                'active_allowances' => $employee->activeAllowances->map(fn($a) => [
                    'name'   => $a->name,
                    'type'   => $a->type,
                    'amount' => $a->amount,
                ])->values(),
                'active_benefits'   => $employee->activeBenefits->map(fn($b) => [
                    'name'   => $b->name,
                    'type'   => $b->type,
                    'amount' => $b->amount,
                ])->values(),
            ],
            'payrollInput' => $payrollInput ? [
                'id'                        => $payrollInput->id,
                'daily_rate'                => $payrollInput->daily_rate,
                'days_worked'               => $payrollInput->days_worked,
                'weekends_worked'           => $payrollInput->weekends_worked,
                'overtime_hours'            => $payrollInput->overtime_hours,
                'late_hours'                => $payrollInput->late_hours,
                'holiday_days'              => $payrollInput->holiday_days,
                'night_differential_hours'  => $payrollInput->night_differential_hours,
                'allowances'                => $payrollInput->allowances,
                'deductions'                => $payrollInput->deductions,
                'deductions_remarks'        => $payrollInput->deductions_remarks,
                'reimbursements'            => $payrollInput->reimbursements,
                'reimbursements_remarks'    => $payrollInput->reimbursements_remarks,
            ] : null,
            'dailyRate' => $dailyRate,
            'computedDaysFromAttendance' => $computedDaysFromAttendance,
            'approvedRequests' => $approvedRequests->map(fn($r) => [
                'id'                        => $r->id,
                'request_type'              => $r->request_type,
                'work_date'                 => $r->work_date?->toDateString(),
                'calculated_overtime_hours' => $r->calculated_overtime_hours,
                'estimated_hours'           => $r->estimated_hours,
            ])->values(),
            'weekendsWorked' => $weekendsWorked,
            'overtimeHours' => $overtimeHours,
            'holidayDays' => $holidayDays,
            'isEdit' => $payrollInput !== null,
            'isSecondHalfOfMonth' => $payrollPeriod->isSecondHalfOfMonth(),
            'cashAdvance' => [
                'fully_paid'       => $fullyPaidCashAdvances->map(fn($r) => [
                    'amount'       => $r->amount,
                    'amount_paid'  => $r->amount_paid,
                    'request_date' => $r->request_date?->toDateString(),
                ])->values(),
                'outstanding'      => $outstandingCashAdvances->map(fn($r) => [
                    'amount'       => $r->amount,
                    'amount_paid'  => $r->amount_paid,
                    'request_date' => $r->request_date?->toDateString(),
                ])->values(),
                'total_outstanding'   => $totalOutstandingBalance,
                'existing_payments'   => $existingPeriodPayments,
            ],
            'totalAllowancesAndBenefits' => round(($totalAllowances + $totalBenefits) / 2, 2),
        ]);
    }

    /**
     * POST /manual-payroll-attendance/preview
     * Compute payroll preview without saving
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'daily_rate' => 'required|numeric|min:0',
            'rate_type' => 'required|in:daily',
            'days_worked' => 'required|numeric|min:0|max:31',
            'weekends_worked' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'late_hours' => 'nullable|numeric|min:0',
            'holiday_days' => 'nullable|numeric|min:0',
            'night_differential_hours' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'deductions_remarks' => 'nullable|string|max:255',
            'reimbursements' => 'nullable|numeric|min:0',
            'reimbursements_remarks' => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // Get payroll period if provided to calculate working days
        $cutoffStart = null;
        $cutoffEnd = null;
        $workingDaysPerMonth = 22; // Default fallback
        $period = null;
        if (!empty($validated['payroll_period_id'])) {
            $period = PayrollPeriod::find($validated['payroll_period_id']);
            if ($period) {
                $cutoffStart = $period->cutoff_start->toDateString();
                $cutoffEnd = $period->cutoff_end->toDateString();
                $workingDaysPerMonth = PayrollPeriod::calculateWorkingDays($cutoffStart, $cutoffEnd);
            }
        }

        // Use PayrollComputationEngine for consistency with PayrollInput::computePay()
        $engine = new PayrollComputationEngine();
        
        $employeeData = [
            'daily_rate' => $validated['daily_rate'],
            'working_hours_per_day' => 8,
        ];

        $attendance = [
            'days_worked' => $validated['days_worked'],
            'weekends_worked' => $validated['weekends_worked'] ?? 0,
            'overtime_hours' => $validated['overtime_hours'] ?? 0,
            'late_hours' => $validated['late_hours'] ?? 0,
            'holiday_days' => $validated['holiday_days'] ?? 0,
            'night_differential_hours' => $validated['night_differential_hours'] ?? 0,
        ];

        $allowances = [$validated['allowances'] ?? 0];

        // Fetch active benefits from employee (for consistency with PayrollController::calculatePayroll())
        $benefits = $employee->activeBenefits()->pluck('amount')->toArray();

        // First, calculate gross pay without deductions to get contribution bases
        $previewResult = $engine->compute($employeeData, $attendance, [], $benefits, $allowances, [], true, $cutoffStart, $cutoffEnd);
        $grossPay = $previewResult['gross_pay'];

        // Government contributions are fixed based on monthly basic salary and divided per cutoff (semi-monthly)
        // SSS Contribution using official bracket table (Circular No. 2024-006)
        $sssService = new SssContributionService();
        $sssCalculation = $sssService->calculate($employee->basic_salary);
        $sssMonthly = $employee->custom_sss_contribution ?? $sssCalculation['employee_share'];
        $sssContribution = round($sssMonthly / 2, 2);

        // PhilHealth Contribution using official 2025/2026 table
        $philHealthService = new PhilHealthContributionService();
        $philHealthCalculation = $philHealthService->calculate($employee->basic_salary);
        $philHealthMonthly = $employee->custom_philhealth_contribution ?? $philHealthCalculation['employee_share'];
        $philhealthContribution = round($philHealthMonthly / 2, 2);

        // Pag-IBIG Contribution using official 2026 table
        $pagIbigService = new PagIbigContributionService();
        $pagIbigCalculation = $pagIbigService->calculate($employee->basic_salary);
        $pagIbigMonthly = $employee->custom_pagibig_contribution ?? $pagIbigCalculation['employee_share'];
        $pagibigContribution = round($pagIbigMonthly / 2, 2);

        // Calculate withholding tax only if period is in second half of month (16-30,31)
        $withholdingTax = 0;
        $firstCutoffGrossPay = 0;
        $secondCutoffGrossPay = 0;
        $firstCutoffNetPay = 0;
        $secondCutoffNetPay = 0;
        
        if ($period && $period->isSecondHalfOfMonth()) {
            $totalContributions = $sssContribution + $philhealthContribution + $pagibigContribution;
            \Log::info('Withholding tax calculation in ManualPayrollAttendanceController', [
                'gross_pay' => $grossPay,
                'sss_contribution' => $sssContribution,
                'philhealth_contribution' => $philhealthContribution,
                'pagibig_contribution' => $pagibigContribution,
                'total_contributions' => $totalContributions,
            ]);
            
            // Fetch 1st cutoff pay for the same month
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
                }
            }
            
            $secondCutoffGrossPay = $grossPay;
            $totalMonthlyGross = $firstCutoffGrossPay + $secondCutoffGrossPay;
            $totalMonthlyContributions = $totalContributions * 2; // Since contributions are halved per cutoff
            
            // Use total monthly allowances for withholding tax calculation
            // Since allowances are divided by 2 per cutoff, multiply by 2 to get total monthly
            $totalMonthlyAllowances = ($validated['allowances'] ?? 0) * 2;

$withholdingTaxService = new WithholdingTaxService();
$taxResult = $withholdingTaxService->calculate($totalMonthlyGross, $totalMonthlyContributions, $totalMonthlyAllowances);
$withholdingTax = $taxResult['tax'];
            \Log::info('Withholding tax result in ManualPayrollAttendanceController', [
                'total_monthly_gross' => $totalMonthlyGross,
                'total_monthly_contributions' => $totalMonthlyContributions,
                'total_monthly_allowances' => $totalMonthlyAllowances,
                'withholding_tax' => $withholdingTax
            ]);
        }

        // Total government contributions
        $governmentDeductions = $sssContribution + $philhealthContribution + $pagibigContribution + $withholdingTax;

        // Add manual deductions
        $manualDeductions = $validated['deductions'] ?? 0;

        // Cash advance payment deduction (50% of net pay per cutoff)
        $cashAdvancePayment = 0;
        $cashAdvanceDeduction = 0;
        $approvedCashAdvances = $employee->financialRequests()
            ->where('request_type', 'cash_advance')
            ->where('status', 'approved')
            ->where('amount_paid', '<', \DB::raw('amount'))
            ->get();

        if ($approvedCashAdvances->isNotEmpty()) {
            // Check if cash advance payments have already been processed for this payroll period
            $existingPayments = CashAdvancePayment::where(function ($query) use ($period) {
                    $period ? $query->where('payroll_period_id', $period->id) : $query->whereNull('payroll_period_id');
                })
                ->whereHas('financialRequest', function($query) use ($employee) {
                    $query->where('employee_id', $employee->id);
                })
                ->exists();

            if (!$existingPayments) {
                // Calculate total outstanding balance
                $totalOutstanding = $approvedCashAdvances->sum(function($request) {
                    return $request->amount - $request->amount_paid;
                });

                // Calculate net pay before cash advance deduction
                $deductionsBeforeCashAdvance = [$sssContribution, $philhealthContribution, $pagibigContribution, $withholdingTax, $manualDeductions];
                $resultBeforeCashAdvance = $engine->compute($employeeData, $attendance, $deductionsBeforeCashAdvance, $benefits, $allowances, [], false, $cutoffStart, $cutoffEnd);
                $netPayBeforeCashAdvance = $resultBeforeCashAdvance['net_pay'] + ($validated['reimbursements'] ?? 0);

                // Calculate payment as 50% of net pay (or remaining balance, whichever is lower)
                $cashAdvancePayment = min($netPayBeforeCashAdvance * 0.5, $totalOutstanding);
                $cashAdvancePayment = round($cashAdvancePayment, 2);
                $cashAdvanceDeduction = $cashAdvancePayment;
            }
        }

        // Total deductions
        $totalDeductions = $governmentDeductions + $manualDeductions + $cashAdvanceDeduction;

        // Calculate final payroll with all deductions (including cash advance)
        $result = $engine->compute(
            $employeeData,
            $attendance,
            [$totalDeductions],
            $benefits,
            $allowances,
            [],
            false, // not preview mode for final calculation
            $cutoffStart,
            $cutoffEnd
        );

        // Add government contribution breakdown to result
        $result['sss_contribution'] = $sssContribution;
        $result['philhealth_contribution'] = $philhealthContribution;
        $result['pagibig_contribution'] = $pagibigContribution;
        $result['withholding_tax'] = $withholdingTax;
        $result['government_deductions'] = $governmentDeductions;
        $result['manual_deductions'] = $manualDeductions;
        $result['cash_advance_deduction'] = $cashAdvanceDeduction;
        $result['reimbursements'] = $validated['reimbursements'] ?? 0;
        
        // Add cutoff pay breakdown
        $result['first_cutoff_gross_pay'] = $firstCutoffGrossPay;
        $result['second_cutoff_gross_pay'] = $secondCutoffGrossPay;
        $result['total_monthly_gross_pay'] = $firstCutoffGrossPay + $secondCutoffGrossPay;

        $result['first_cutoff_net_pay'] = $firstCutoffNetPay;
        $result['second_cutoff_net_pay'] = $result['net_pay'] + ($validated['reimbursements'] ?? 0);
        $result['total_monthly_net_pay'] = $firstCutoffNetPay + $result['second_cutoff_net_pay'];

        return response()->json([
            'success' => true,
            'preview' => $result,
        ]);
    }

    /**
     * Calculate withholding tax based on monthly computation
     * Formula: (Total Monthly Gross - Total Monthly Contributions - Total Monthly Allowances) - 33,333 = taxablePay
     * taxablePay * 20% + 1875 = Withholding Tax
     */


    /**
     * POST /manual-payroll-attendance/save
     * Save payroll input for an employee
     */
    public function save(Request $request)
    {
        \Log::info('Save payroll input attempt', $request->all());

        try {
            $validated = $request->validate([
                'payroll_period_id' => 'required|exists:payroll_periods,id',
                'employee_id' => 'required|exists:employees,id',
                'daily_rate' => 'required|numeric|min:0',
                'rate_type' => 'required|in:daily',
                'days_worked' => 'nullable|numeric|min:0|max:31',
                'weekends_worked' => 'nullable|numeric|min:0',
                'overtime_hours' => 'nullable|numeric|min:0',
                'late_hours' => 'nullable|numeric|min:0',
                'holiday_days' => 'nullable|numeric|min:0',
                'night_differential_hours' => 'nullable|numeric|min:0',
                'allowances' => 'nullable|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
                'deductions_remarks' => 'nullable|string|max:255',
                'reimbursements' => 'nullable|numeric|min:0',
                'reimbursements_remarks' => 'nullable|string|max:255',
            ]);

            \Log::info('Validation passed', $validated);

            // Guard: period must still be a draft
            $period = PayrollPeriod::findOrFail($validated['payroll_period_id']);
            if ($period->isFinalized()) {
                \Log::warning('Attempted to edit finalized payroll period', ['period_id' => $period->id]);
                return back()->with('error', 'Cannot edit a finalized payroll period.');
            }

            // If days_worked is null or empty, default to attendance computed days
            if (!isset($validated['days_worked']) || $validated['days_worked'] === '' || $validated['days_worked'] === null) {
                $computedDaysFromAttendance = \App\Models\Attendance::where('employee_id', $validated['employee_id'])
                    ->whereBetween('date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
                    ->sum('computed_days');
                $validated['days_worked'] = $computedDaysFromAttendance > 0 ? $computedDaysFromAttendance : 0;
            }

            // If special work fields are null or empty, use approved work request values
            $approvedRequests = \App\Models\WorkRequest::where('employee_id', $validated['employee_id'])
                ->where('status', 'approved')
                ->whereBetween('work_date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
                ->where('work_date', '<=', now()->toDateString())
                ->get();

            if (!isset($validated['weekends_worked']) || $validated['weekends_worked'] === '' || $validated['weekends_worked'] === null) {
                $validated['weekends_worked'] = $approvedRequests->where('request_type', 'weekend')->count();
            }

            if (!isset($validated['overtime_hours']) || $validated['overtime_hours'] === '' || $validated['overtime_hours'] === null) {
                $validated['overtime_hours'] = $approvedRequests->where('request_type', 'overtime')->sum('calculated_overtime_hours');
            }

            if (!isset($validated['holiday_days']) || $validated['holiday_days'] === '' || $validated['holiday_days'] === null) {
                $validated['holiday_days'] = $approvedRequests->where('request_type', 'holiday')->count();
            }

            // Check if payroll input already exists
            $payrollInput = PayrollInput::where('payroll_period_id', $validated['payroll_period_id'])
                ->where('employee_id', $validated['employee_id'])
                ->first();

            // Get employee for cash advance processing
            $employee = Employee::findOrFail($validated['employee_id']);

            if ($payrollInput) {
                \Log::info('Updating existing payroll input', ['payroll_input_id' => $payrollInput->id]);
                // Update existing
                $payrollInput->fill([
                    'daily_rate' => $validated['daily_rate'],
                    'rate_type' => $validated['rate_type'],
                    'days_worked' => $validated['days_worked'],
                    'weekends_worked' => $validated['weekends_worked'] ?? 0,
                    'overtime_hours' => $validated['overtime_hours'] ?? 0,
                    'late_hours' => $validated['late_hours'] ?? 0,
                    'holiday_days' => $validated['holiday_days'] ?? 0,
                    'night_differential_hours' => $validated['night_differential_hours'] ?? 0,
                    'allowances' => $validated['allowances'] ?? 0,
                    'deductions' => $validated['deductions'] ?? 0,
                    'deductions_remarks' => $validated['deductions_remarks'] ?? '',
                    'reimbursements' => $validated['reimbursements'] ?? 0,
                    'reimbursements_remarks' => $validated['reimbursements_remarks'] ?? '',
                ]);
                // First compute payroll to get net pay
                $payrollInput->computePay()->save();
                
                // Process cash advance payments after computing payroll
                $cashAdvanceDeduction = $this->processCashAdvancePayments($employee, $period, $payrollInput);
                
                // Add cash advance deduction to manual deductions if applicable
                if ($cashAdvanceDeduction > 0) {
                    $currentDeductions = $payrollInput->deductions ?? 0;
                    $payrollInput->deductions = $currentDeductions + $cashAdvanceDeduction;
                    $payrollInput->deductions_remarks = trim(($payrollInput->deductions_remarks ?? '') . ' Cash advance deduction: ₱' . number_format($cashAdvanceDeduction, 2));
                    
                    // Recompute payroll with cash advance deduction included
                    $payrollInput->computePay()->save();
                }
                
                \Log::info('Payroll input updated successfully', ['payroll_input_id' => $payrollInput->id]);
            } else {
                \Log::info('Creating new payroll input');
                // Create new
                $payrollInput = new PayrollInput([
                    'payroll_period_id' => $validated['payroll_period_id'],
                    'employee_id' => $validated['employee_id'],
                    'daily_rate' => $validated['daily_rate'],
                    'rate_type' => $validated['rate_type'],
                    'days_worked' => $validated['days_worked'],
                    'weekends_worked' => $validated['weekends_worked'] ?? 0,
                    'overtime_hours' => $validated['overtime_hours'] ?? 0,
                    'late_hours' => $validated['late_hours'] ?? 0,
                    'holiday_days' => $validated['holiday_days'] ?? 0,
                    'night_differential_hours' => $validated['night_differential_hours'] ?? 0,
                    'allowances' => $validated['allowances'] ?? 0,
                    'deductions' => $validated['deductions'] ?? 0,
                    'deductions_remarks' => $validated['deductions_remarks'] ?? '',
                    'reimbursements' => $validated['reimbursements'] ?? 0,
                    'reimbursements_remarks' => $validated['reimbursements_remarks'] ?? '',
                ]);
                
                // First compute payroll to get net pay
                $payrollInput->computePay()->save();
                
                // Process cash advance payments after computing payroll
                $cashAdvanceDeduction = $this->processCashAdvancePayments($employee, $period, $payrollInput);
                
                // Add cash advance deduction to manual deductions if applicable
                if ($cashAdvanceDeduction > 0) {
                    $currentDeductions = $payrollInput->deductions ?? 0;
                    $payrollInput->deductions = $currentDeductions + $cashAdvanceDeduction;
                    $payrollInput->deductions_remarks = trim(($payrollInput->deductions_remarks ?? '') . ' Cash advance deduction: ₱' . number_format($cashAdvanceDeduction, 2));
                    
                    // Recompute payroll with cash advance deduction included
                    $payrollInput->computePay()->save();
                }
                
                \Log::info('Payroll input created successfully', ['payroll_input_id' => $payrollInput->id]);
            }

            return redirect()->route('manual-payroll-attendance.period', $period)
                ->with('success', 'Payroll input saved successfully.');
        } catch (\Exception $e) {
            \Log::error('Error saving payroll input', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error saving payroll input: ' . $e->getMessage());
        }
    }

    /**
     * Process cash advance payments for an employee
     * This method handles the automatic deduction and recording of cash advance payments
     */
    protected function processCashAdvancePayments(Employee $employee, PayrollPeriod $period, PayrollInput $payrollInput)
    {
        // Check if cash advance payments have already been processed for this payroll period and employee
        $existingPayments = CashAdvancePayment::where('payroll_period_id', $period->id)
            ->whereHas('financialRequest', function($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            })
            ->exists();

        if ($existingPayments) {
            // Payments already processed for this period, skip to avoid duplication
            return 0;
        }

        // Get approved cash advances with remaining balance
        $approvedCashAdvances = $employee->financialRequests()
            ->where('request_type', 'cash_advance')
            ->where('status', 'approved')
            ->where('amount_paid', '<', \DB::raw('amount'))
            ->get();

        if ($approvedCashAdvances->isEmpty()) {
            return 0;
        }

        // Check if any cash advances are already fully paid
        $fullyPaidAdvances = $approvedCashAdvances->filter(function($request) {
            return $request->amount_paid >= $request->amount;
        });

        if ($fullyPaidAdvances->isNotEmpty()) {
            // Remove fully paid advances from the list
            $approvedCashAdvances = $approvedCashAdvances->reject(function($request) {
                return $request->amount_paid >= $request->amount;
            });
        }

        if ($approvedCashAdvances->isEmpty()) {
            // All cash advances are fully paid
            return 0;
        }

        // Calculate total outstanding balance
        $totalOutstanding = $approvedCashAdvances->sum(function($request) {
            return $request->amount - $request->amount_paid;
        });

        // Calculate net pay for cash advance deduction (50% of net pay)
        $netPay = $payrollInput->net_pay + ($payrollInput->reimbursements ?? 0);
        $cashAdvancePayment = min($netPay * 0.5, $totalOutstanding);
        $cashAdvancePayment = round($cashAdvancePayment, 2);

        if ($cashAdvancePayment <= 0) {
            return 0;
        }

        // Update cash advance payment records
        $remainingPayment = $cashAdvancePayment;
        foreach ($approvedCashAdvances as $cashAdvance) {
            if ($remainingPayment <= 0) break;
            
            $balance = $cashAdvance->amount - $cashAdvance->amount_paid;
            $payment = min($remainingPayment, $balance);
            
            $cashAdvance->increment('amount_paid', $payment);
            
            // Record payment history
            CashAdvancePayment::create([
                'financial_request_id' => $cashAdvance->id,
                'payroll_period_id' => $period->id,
                'amount' => $payment,
                'description' => 'Automatic payment deduction (50% of net pay)',
            ]);
            
            $remainingPayment -= $payment;
        }

        return $cashAdvancePayment;
    }

    /**
     * GET /manual-payroll-attendance/period/{payrollPeriod}/summary
     * Get payroll summary for a period
     */
    public function getPeriodSummary(PayrollPeriod $payrollPeriod): JsonResponse
    {
        $payrollPeriod->load('payrollInputs.employee', 'payrollInputs.adjustments');

        $summary = [
            'period' => [
                'id' => $payrollPeriod->id,
                'cutoff_start' => $payrollPeriod->cutoff_start->toDateString(),
                'cutoff_end' => $payrollPeriod->cutoff_end->toDateString(),
                'payroll_date' => $payrollPeriod->payroll_date->toDateString(),
                'status' => $payrollPeriod->status,
            ],
            'total_employees' => Employee::where('is_archived', false)->count(),
            'encoded_employees' => $payrollPeriod->payrollInputs->count(),
            'total_gross_pay' => $payrollPeriod->total_gross_pay,
            'total_net_pay' => $payrollPeriod->total_net_pay,
            'total_deductions' => $payrollPeriod->total_deductions,
            'employees' => $payrollPeriod->payrollInputs->map(fn($input) => [
                'id' => $input->id,
                'employee_id' => $input->employee_id,
                'employee_name' => $input->employee->first_name . ' ' . $input->employee->last_name,
                'employee_code' => $input->employee->employee_id,
                'days_worked' => $input->days_worked,
                'overtime_hours' => $input->overtime_hours,
                'late_hours' => $input->late_hours,
                'allowances' => $input->allowances,
                'deductions' => $input->deductions,
                'gross_pay' => $input->gross_pay,
                'net_pay' => $input->net_pay,
                'adjustments' => $input->adjustments,
            ]),
        ];

        return response()->json($summary);
    }

    // ── Private helpers ────────────────────────────────────────

    /**
     * Build the shared payroll-period payload for Inertia pages.
     */
    private function periodPayload(PayrollPeriod $payrollPeriod): array
    {
        $inputs = $payrollPeriod->payrollInputs ? $payrollPeriod->payrollInputs : collect();

        return [
            'id'              => $payrollPeriod->id,
            'cutoff_start'    => $payrollPeriod->cutoff_start?->toDateString(),
            'cutoff_end'      => $payrollPeriod->cutoff_end?->toDateString(),
            'payroll_date'    => $payrollPeriod->payroll_date?->toDateString(),
            'status'          => $payrollPeriod->status,
            'period_label'    => $payrollPeriod->period_label,
            'is_draft'        => $payrollPeriod->isDraft(),
            'is_finalized'    => $payrollPeriod->isFinalized(),
            'is_second_half'  => $payrollPeriod->isSecondHalfOfMonth(),
            'total_gross'     => $payrollPeriod->total_gross_pay,
            'total_net'       => $payrollPeriod->total_net_pay,
            'total_deductions'=> $payrollPeriod->total_deductions,
            'payroll_inputs'  => $inputs->map(fn($input) => [
                'id'           => $input->id,
                'employee_id'  => $input->employee_id,
                'employee'     => $input->employee ? [
                    'id'          => $input->employee->id,
                    'employee_id' => $input->employee->employee_id,
                    'first_name'  => $input->employee->first_name,
                    'last_name'   => $input->employee->last_name,
                ] : null,
                'days_worked'  => $input->days_worked,
                'overtime_hours' => $input->overtime_hours,
                'late_hours'   => $input->late_hours,
                'allowances'   => $input->allowances,
                'deductions'   => $input->deductions,
                'gross_pay'    => $input->gross_pay,
                'net_pay'      => $input->net_pay,
                'adjustments'  => $input->adjustments ? $input->adjustments->map(fn($a) => [
                    'id'              => $a->id,
                    'adjustment_type' => $a->adjustment_type,
                    'amount'          => $a->amount,
                    'remarks'         => $a->remarks,
                ])->values() : [],
            ])->values(),
        ];
    }

    /**
     * Convert employees.basic_salary to a daily rate using the formula:
     * BSM X 12 / 52 / 40 X 8
     * Where: BSM = Basic Salary per Month, 12 = number of months, 52 = number of weeks,
     * 40 = hours per week, 8 = hours per day
     */
    private function computeDailyRate(Employee $employee): float
    {
        return round(($employee->basic_salary * 12) / 52 / 40 * 8, 2);
    }
}

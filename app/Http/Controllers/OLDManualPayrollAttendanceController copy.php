<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollInput;
use App\Models\PayrollAdjustment;
use App\Models\WorkRequest;
use App\Services\Payroll\PayrollComputationEngine;
use App\Services\SssContributionService;
use App\Services\PhilHealthContributionService;
use App\Services\PagIbigContributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Services\WithholdingTaxService;

class ManualPayrollAttendanceController extends Controller
{
    /**
     * GET /manual-payroll-attendance
     * Show the manual payroll attendance encoding interface
     */
 public function index()
{
    try {
        $periods = PayrollPeriod::with('payrollInputs')
            ->whereNotIn('status', ['archived'])
            ->orderByDesc('cutoff_start')
            ->get();
    } catch (\Exception $e) {
        $periods = PayrollPeriod::whereNotIn('status', ['archived'])
            ->orderByDesc('cutoff_start')
            ->get();
    }

    return view('manual-payroll-attendance.index', compact('periods'));
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
                    $overtimeHours = $approvedRequests->where('request_type', 'overtime')->sum('estimated_hours');
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
                            'allowances' => $employee->activeAllowances->sum('amount') + $employee->activeBenefits->sum('amount'),
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

        return view('manual-payroll-attendance.period', compact('payrollPeriod', 'unencodedEmployees'));
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
        $overtimeHours = $approvedRequests->where('request_type', 'overtime')->sum('estimated_hours');
        $holidayDays = $approvedRequests->where('request_type', 'holiday')->count();

        // If payroll input exists, use approved request values as defaults (don't save to DB)
        // This allows HR to see the auto-populated values and decide whether to use them
        if ($payrollInput) {
            // Override with approved request values for display purposes
            $payrollInput->weekends_worked = $weekendsWorked;
            $payrollInput->overtime_hours = $overtimeHours;
            $payrollInput->holiday_days = $holidayDays;
        }

        return view('manual-payroll-attendance.employee-form', compact(
            'payrollPeriod',
            'employee',
            'payrollInput',
            'dailyRate',
            'computedDaysFromAttendance',
            'approvedRequests',
            'weekendsWorked',
            'overtimeHours',
            'holidayDays'
        ));
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
            
            // Only use current cutoff allowances for withholding tax calculation
            // Allowances are advance paychecks and should not be doubled across cutoffs
            $currentCutoffAllowances = $validated['allowances'] ?? 0;

$withholdingTaxService = new WithholdingTaxService();
$taxResult = $withholdingTaxService->calculate($totalMonthlyGross, $totalMonthlyContributions, $currentCutoffAllowances);
$withholdingTax = $taxResult['tax'];
            \Log::info('Withholding tax result in ManualPayrollAttendanceController', [
                'total_monthly_gross' => $totalMonthlyGross,
                'total_monthly_contributions' => $totalMonthlyContributions,
                'current_cutoff_allowances' => $currentCutoffAllowances,
                'withholding_tax' => $withholdingTax
            ]);
        }

        // Total government contributions
        $governmentDeductions = $sssContribution + $philhealthContribution + $pagibigContribution + $withholdingTax;

        // Add manual deductions
        $manualDeductions = $validated['deductions'] ?? 0;

        // Total deductions
        $totalDeductions = $governmentDeductions + $manualDeductions;

        // Calculate final payroll with all deductions
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
    public function save(Request $request): JsonResponse
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
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit a finalized payroll period.',
                ], 422);
            }

            // If days_worked is null or empty, default to attendance computed days
            if (!isset($validated['days_worked']) || $validated['days_worked'] === '' || $validated['days_worked'] === null) {
                $computedDaysFromAttendance = \App\Models\Attendance::where('employee_id', $validated['employee_id'])
                    ->whereBetween('date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
                    ->sum('computed_days');
                $validated['days_worked'] = $computedDaysFromAttendance > 0 ? $computedDaysFromAttendance : 0;
            }

            // Check if payroll input already exists
            $payrollInput = PayrollInput::where('payroll_period_id', $validated['payroll_period_id'])
                ->where('employee_id', $validated['employee_id'])
                ->first();

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
                $payrollInput->computePay()->save();
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
                $payrollInput->computePay()->save();
                \Log::info('Payroll input created successfully', ['payroll_input_id' => $payrollInput->id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payroll input saved successfully.',
                'payroll_input' => $payrollInput->load('employee', 'adjustments'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving payroll input', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error saving payroll input: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /manual-payroll-attendance/adjustments
     * Add or update payroll adjustment
     */
    public function saveAdjustment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payroll_input_id' => 'required|exists:payroll_inputs,id',
            'adjustment_type' => 'required|string',
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        // Guard: payroll period must still be a draft
        $payrollInput = PayrollInput::findOrFail($validated['payroll_input_id']);
        if ($payrollInput->payrollPeriod->isFinalized()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add adjustments to a finalized payroll period.',
            ], 422);
        }

        $adjustment = PayrollAdjustment::create($validated);

        // Recompute payroll
        $payrollInput->computePay()->save();

        return response()->json([
            'success' => true,
            'message' => 'Adjustment saved successfully.',
            'adjustment' => $adjustment,
        ]);
    }

    /**
     * DELETE /manual-payroll-attendance/adjustments/{adjustment}
     * Delete a payroll adjustment
     */
    public function deleteAdjustment(PayrollAdjustment $adjustment): JsonResponse
    {
        // Guard: payroll period must still be a draft
        if ($adjustment->payrollInput->payrollPeriod->isFinalized()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete adjustments from a finalized payroll period.',
            ], 422);
        }

        $payrollInput = $adjustment->payrollInput;
        $adjustment->delete();

        // Recompute payroll
        $payrollInput->computePay()->save();

        return response()->json([
            'success' => true,
            'message' => 'Adjustment deleted successfully.',
        ]);
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

<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollInput;
use App\Models\PayrollAdjustment;
use App\Services\Payroll\PayrollComputationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManualPayrollAttendanceController extends Controller
{
    /**
     * GET /manual-payroll-attendance
     * Show the manual payroll attendance encoding interface
     */
    public function index()
    {
        try {
            $periods = PayrollPeriod::with('payrollInputs')->orderByDesc('cutoff_start')->get();
        } catch (\Exception $e) {
            // If eager loading fails, try without it
            $periods = PayrollPeriod::orderByDesc('cutoff_start')->get();
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
        
        // Get employees not yet encoded for this period
        $encodedIds = $payrollPeriod->payrollInputs ? $payrollPeriod->payrollInputs->pluck('employee_id')->toArray() : [];
        
        // Handle empty array case for whereNotIn
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

        return view('manual-payroll-attendance.employee-form', compact(
            'payrollPeriod',
            'employee',
            'payrollInput',
            'dailyRate'
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
            'daily_rate' => 'required|numeric|min:0',
            'rate_type' => 'required|in:daily,monthly',
            'days_worked' => 'required|numeric|min:0|max:31',
            'regular_hours' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'late_hours' => 'nullable|numeric|min:0',
            'holiday_days' => 'nullable|numeric|min:0',
            'night_differential_hours' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // Use PayrollComputationEngine for consistency with PayrollInput::computePay()
        $engine = new PayrollComputationEngine();
        
        // Convert rate to monthly salary based on rate type
        if ($validated['rate_type'] === 'monthly') {
            $monthlySalary = $validated['daily_rate'];
        } else {
            $monthlySalary = $validated['daily_rate'] * 22; // Convert daily rate to monthly
        }
        
        $employeeData = [
            'monthly_salary' => $monthlySalary,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ];

        $attendance = [
            'days_worked' => $validated['days_worked'],
            'regular_hours' => $validated['regular_hours'] ?? 0,
            'overtime_hours' => $validated['overtime_hours'] ?? 0,
            'late_hours' => $validated['late_hours'] ?? 0,
            'holiday_days' => $validated['holiday_days'] ?? 0,
            'night_differential_hours' => $validated['night_differential_hours'] ?? 0,
        ];

        $allowances = [$validated['allowances'] ?? 0];

        // First, calculate gross pay without deductions to get contribution bases
        $previewResult = $engine->compute($employeeData, $attendance, [], [], $allowances, [], true);
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
            [],
            $allowances,
            [],
            false // not preview mode for final calculation
        );

        // Add government contribution breakdown to result
        $result['sss_contribution'] = $sssContribution;
        $result['philhealth_contribution'] = $philhealthContribution;
        $result['pagibig_contribution'] = $pagibigContribution;
        $result['withholding_tax'] = $withholdingTax;
        $result['government_deductions'] = $governmentDeductions;
        $result['manual_deductions'] = $manualDeductions;

        return response()->json([
            'success' => true,
            'preview' => $result,
        ]);
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
                'rate_type' => 'required|in:daily,monthly',
                'days_worked' => 'required|numeric|min:0|max:31',
                'regular_hours' => 'nullable|numeric|min:0',
                'overtime_hours' => 'nullable|numeric|min:0',
                'late_hours' => 'nullable|numeric|min:0',
                'holiday_days' => 'nullable|numeric|min:0',
                'night_differential_hours' => 'nullable|numeric|min:0',
                'allowances' => 'nullable|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
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
                    'regular_hours' => $validated['regular_hours'] ?? 0,
                    'overtime_hours' => $validated['overtime_hours'] ?? 0,
                    'late_hours' => $validated['late_hours'] ?? 0,
                    'holiday_days' => $validated['holiday_days'] ?? 0,
                    'night_differential_hours' => $validated['night_differential_hours'] ?? 0,
                    'allowances' => $validated['allowances'] ?? 0,
                    'deductions' => $validated['deductions'] ?? 0,
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
                    'regular_hours' => $validated['regular_hours'] ?? 0,
                    'overtime_hours' => $validated['overtime_hours'] ?? 0,
                    'late_hours' => $validated['late_hours'] ?? 0,
                    'holiday_days' => $validated['holiday_days'] ?? 0,
                    'night_differential_hours' => $validated['night_differential_hours'] ?? 0,
                    'allowances' => $validated['allowances'] ?? 0,
                    'deductions' => $validated['deductions'] ?? 0,
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
     * Convert employees.basic_salary to a daily rate based on salary_type.
     */
    private function computeDailyRate(Employee $employee): float
    {
        return round(match($employee->salary_type) {
            'Monthly' => $employee->basic_salary / 22,
            'Hourly'  => $employee->basic_salary * 8,
            default   => $employee->basic_salary,   // Daily
        }, 2);
    }
}

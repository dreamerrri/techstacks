<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollInput;
use App\Models\PayrollAdjustment;
use App\Services\Payroll\PayrollComputationEngine;
use App\Services\SssContributionService;
use App\Services\PhilHealthContributionService;
use App\Services\PagIbigContributionService;
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
            'rate_type' => 'required|in:daily',
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
        
        // Convert daily rate to monthly salary using the formula: daily_rate * 22
        $monthlySalary = $validated['daily_rate'] * 22;
        
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

        // Government contributions are based on gross pay for payroll preview
        // Use custom values if set, otherwise use official bracket tables
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
        \Log::info('Withholding tax calculation in ManualPayrollAttendanceController', [
            'gross_pay' => $grossPay,
            'sss_contribution' => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution' => $pagibigContribution,
            'total_contributions' => $totalContributions,
        ]);
        $withholdingTax = $this->calculateTax($grossPay, $totalContributions);
        \Log::info('Withholding tax result in ManualPayrollAttendanceController', ['withholding_tax' => $withholdingTax]);

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

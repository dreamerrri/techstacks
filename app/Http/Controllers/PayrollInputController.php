<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollInput;
use App\Models\PayrollPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollInputController extends Controller
{
    /**
     * GET /payroll-inputs?payroll_period_id=1
     * List all inputs for a given period, with employee info pre-filled.
     * Also returns employees NOT yet encoded so the UI can show them.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
        ]);

        $periodId = $request->payroll_period_id;

        // Already encoded
        $inputs = PayrollInput::with(['employee', 'adjustments'])
            ->where('payroll_period_id', $periodId)
            ->get()
            ->map(fn($i) => $this->formatInput($i));

        // Employees not yet encoded for this period
        $encodedIds = PayrollInput::where('payroll_period_id', $periodId)
            ->pluck('employee_id');

        $unencoded = Employee::whereNotIn('id', $encodedIds)
            ->where('is_archived', false)
            ->get()
            ->map(fn($e) => [
                'employee_id'   => $e->id,
                'employee_code' => $e->employee_id,
                'employee_name' => $e->first_name . ' ' . $e->last_name,
                'position'      => $e->position,
                'department'    => $e->department,
                'suggested_daily_rate' => $this->computeDailyRate($e),
            ]);

        return response()->json([
            'inputs'    => $inputs,
            'unencoded' => $unencoded,
        ]);
    }

    /**
     * POST /payroll-inputs
     * Save (create) a payroll input for one employee.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'employee_id'       => 'required|exists:employees,id',
            'daily_rate'        => 'required|numeric|min:0',
            'days_worked'       => 'required|numeric|min:0|max:31',
            'overtime_hours'    => 'nullable|numeric|min:0',
            'late_hours'        => 'nullable|numeric|min:0',
            'allowances'        => 'nullable|numeric|min:0',
            'deductions'        => 'nullable|numeric|min:0',
        ]);

        // Guard: period must still be a draft
        $period = PayrollPeriod::findOrFail($validated['payroll_period_id']);
        if ($period->isFinalized()) {
            return response()->json(['message' => 'Cannot edit a finalized payroll period.'], 422);
        }

        // Guard: no duplicate entry per period
        $exists = PayrollInput::where('payroll_period_id', $validated['payroll_period_id'])
            ->where('employee_id', $validated['employee_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Employee already encoded for this period. Use PUT to update.'], 422);
        }

        $input = new PayrollInput([
            'payroll_period_id' => $validated['payroll_period_id'],
            'employee_id'       => $validated['employee_id'],
            'daily_rate'        => $validated['daily_rate'],
            'days_worked'       => $validated['days_worked'] ?? 0,
            'overtime_hours'    => $validated['overtime_hours'] ?? 0,
            'late_hours'        => $validated['late_hours'] ?? 0,
            'allowances'        => $validated['allowances'] ?? 0,
            'deductions'        => $validated['deductions'] ?? 0,
        ]);

        $input->computePay()->save();

        return response()->json($this->formatInput($input->load('employee', 'adjustments')), 201);
    }

    /**
     * PUT /payroll-inputs/{id}
     * Update an existing payroll input and recompute pay.
     */
    public function update(Request $request, PayrollInput $payrollInput): JsonResponse
    {
        // Guard: period must still be a draft
        if ($payrollInput->payrollPeriod->isFinalized()) {
            return response()->json(['message' => 'Cannot edit a finalized payroll period.'], 422);
        }

        $validated = $request->validate([
            'daily_rate'     => 'sometimes|numeric|min:0',
            'days_worked'    => 'sometimes|numeric|min:0|max:31',
            'overtime_hours' => 'sometimes|numeric|min:0',
            'late_hours'     => 'sometimes|numeric|min:0',
            'allowances'     => 'sometimes|numeric|min:0',
            'deductions'     => 'sometimes|numeric|min:0',
        ]);

        $payrollInput->fill($validated);
        $payrollInput->computePay()->save();

        return response()->json($this->formatInput($payrollInput->load('employee', 'adjustments')));
    }

    /**
     * DELETE /payroll-inputs/{id}
     * Remove an input (and its adjustments via cascade).
     */
    public function destroy(PayrollInput $payrollInput): JsonResponse
    {
        if ($payrollInput->payrollPeriod->isFinalized()) {
            return response()->json(['message' => 'Cannot delete from a finalized payroll period.'], 422);
        }

        $payrollInput->delete();

        return response()->json(['message' => 'Payroll input deleted.']);
    }

    // ── Private helpers ────────────────────────────────────────

    private function formatInput(PayrollInput $i): array
    {
        return [
            'id'             => $i->id,
            'payroll_period_id' => $i->payroll_period_id,
            'employee_id'    => $i->employee_id,
            'employee_code'  => $i->employee->employee_id,
            'employee_name'  => $i->employee->first_name . ' ' . $i->employee->last_name,
            'position'       => $i->employee->position,
            'department'     => $i->employee->department,
            'daily_rate'     => $i->daily_rate,
            'days_worked'    => $i->days_worked,
            'overtime_hours' => $i->overtime_hours,
            'late_hours'     => $i->late_hours,
            'allowances'     => $i->allowances,
            'deductions'     => $i->deductions,
            'gross_pay'      => $i->gross_pay,
            'net_pay'        => $i->net_pay,
            'adjustments'    => $i->adjustments ?? [],
        ];
    }

    /**
     * Convert employees.basic_salary to a daily rate based on salary_type.
     * Using same formula as ManualPayrollAttendanceController for consistency: (basic_salary * 12) / 52 / 40 * 8
     * Monthly: use formula (basic_salary * 12) / 52 / 40 * 8
     * Daily:   use as-is
     * Hourly:  multiply by 8
     */
    private function computeDailyRate(Employee $employee): float
    {
        return round(match($employee->salary_type) {
            'Monthly' => ($employee->basic_salary * 12) / 52 / 40 * 8,
            'Hourly'  => $employee->basic_salary * 8,
            default   => $employee->basic_salary,   // Daily
        }, 2);
    }
}
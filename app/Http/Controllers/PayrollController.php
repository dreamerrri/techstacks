<?php

namespace App\Http\Controllers;

use App\Models\PayrollInput;
use App\Models\PayrollPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * POST /payroll/compute
     * Live computation endpoint — takes raw inputs, returns gross/net.
     * Used by the frontend to show real-time preview without saving.
     */
    public function compute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'daily_rate'     => 'required|numeric|min:0',
            'days_worked'    => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'late_hours'     => 'nullable|numeric|min:0',
            'allowances'     => 'nullable|numeric|min:0',
            'deductions'     => 'nullable|numeric|min:0',
        ]);

        $input = new PayrollInput([
            'daily_rate'     => $validated['daily_rate'],
            'days_worked'    => $validated['days_worked'],
            'overtime_hours' => $validated['overtime_hours'] ?? 0,
            'late_hours'     => $validated['late_hours'] ?? 0,
            'allowances'     => $validated['allowances'] ?? 0,
            'deductions'     => $validated['deductions'] ?? 0,
        ]);

        $input->computePay();

        return response()->json([
            'gross_pay' => $input->gross_pay,
            'net_pay'   => $input->net_pay,
        ]);
    }

    /**
     * POST /payroll/finalize
     * Finalize a draft payroll period.
     * - Requires at least one encoded employee.
     * - Recomputes all inputs before locking.
     * - Sets status to 'finalized' — no further edits allowed.
     */
    public function finalize(Request $request): JsonResponse
    {
        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
        ]);

        $period = PayrollPeriod::with('payrollInputs')->findOrFail($request->payroll_period_id);

        if ($period->isFinalized()) {
            return response()->json(['message' => 'Payroll period is already finalized.'], 422);
        }

        if ($period->payrollInputs->isEmpty()) {
            return response()->json(['message' => 'Cannot finalize a period with no encoded employees.'], 422);
        }

        // Recompute all inputs before locking
        foreach ($period->payrollInputs as $input) {
            $input->computePay()->save();
        }

        $period->update(['status' => 'finalized']);

        // Reload totals after recompute
        $period->load('payrollInputs');

        return response()->json([
            'message'      => 'Payroll period finalized successfully.',
            'period_id'    => $period->id,
            'status'       => $period->status,
            'total_gross'  => $period->total_gross_pay,
            'total_net'    => $period->total_net_pay,
            'employee_count' => $period->payrollInputs->count(),
        ]);
    }

    public function index()
{
    return view('payroll.index');
}
}
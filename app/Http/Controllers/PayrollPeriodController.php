<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollPeriodController extends Controller
{
    /**
     * GET /payroll-periods/create
     * Show form to create a new payroll period.
     */
    public function create()
    {
        return view('payroll-periods.create');
    }

    /**
     * GET /payroll-periods
     * List all payroll periods, latest first.
     */
    public function index(): JsonResponse
    {
        $periods = PayrollPeriod::with('createdBy')
            ->orderByDesc('cutoff_start')
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'cutoff_start' => $p->cutoff_start->toDateString(),
                'cutoff_end'   => $p->cutoff_end->toDateString(),
                'payroll_date' => $p->payroll_date->toDateString(),
                'status'       => $p->status,
                'created_by'   => $p->createdBy?->name,
                'created_at'   => $p->created_at->toDateTimeString(),
            ]);

        return response()->json($periods);
    }

    /**
     * POST /payroll-periods
     * Create a new payroll period (draft).
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'cutoff_start' => 'required|date',
        'cutoff_end'   => 'required|date|after_or_equal:cutoff_start',
        'payroll_date' => 'required|date|after_or_equal:cutoff_end',
    ]);

    $start = \Carbon\Carbon::parse($validated['cutoff_start']);
    $end   = \Carbon\Carbon::parse($validated['cutoff_end']);

    // 1. Enforce semi-monthly phase boundaries
    $phase = $start->day <= 15 ? 1 : 2;

    if ($phase === 1 && ($start->day !== 1 || $end->day !== 15)) {
        return back()->withErrors(['cutoff_start' => 'Phase 1 must be the 1st to 15th of the month.']);
    }

    if ($phase === 2 && ($start->day !== 16 || $end->day !== $end->daysInMonth)) {
        return back()->withErrors(['cutoff_start' => 'Phase 2 must be the 16th to last day of the month.']);
    }

    // 2. Block duplicate phase for same month
    if (PayrollPeriod::existsForMonthAndPhase($start->year, $start->month, $phase)) {
        $label = $phase === 1 ? '1st half' : '2nd half';
        return back()->withErrors([
            'cutoff_start' => "A {$label} payroll period for {$start->format('F Y')} already exists.",
        ]);
    }

    // 3. Enforce 15-period cap
    if (PayrollPeriod::isAtCapacity()) {
        return back()->withErrors([
            'cutoff_start' => 'Maximum of 15 payroll periods reached. Archive or delete old periods first.',
        ]);
    }

    $period = PayrollPeriod::create(array_merge($validated, [
        'status'     => 'draft',
        'created_by' => Auth::id(),
    ]));

    return redirect()->route('manual-payroll-attendance.index')
        ->with('success', "Payroll period created: {$period->period_label}");
}   
    /**
     * GET /payroll-periods/{id}
     * Return a single period with its inputs and employee details.
     */
    public function show(PayrollPeriod $payrollPeriod): JsonResponse
    {
        $payrollPeriod->load([
            'payrollInputs.employee',
            'payrollInputs.adjustments',
        ]);

        return response()->json([
            'id'            => $payrollPeriod->id,
            'cutoff_start'  => $payrollPeriod->cutoff_start->toDateString(),
            'cutoff_end'    => $payrollPeriod->cutoff_end->toDateString(),
            'payroll_date'  => $payrollPeriod->payroll_date->toDateString(),
            'status'        => $payrollPeriod->status,
            'total_gross'   => $payrollPeriod->total_gross_pay,
            'total_net'     => $payrollPeriod->total_net_pay,
            'total_deductions' => $payrollPeriod->total_deductions,
            'inputs'        => $payrollPeriod->payrollInputs->map(fn($i) => [
                'id'             => $i->id,
                'employee_id'    => $i->employee_id,
                'employee_name'  => $i->employee->first_name . ' ' . $i->employee->last_name,
                'employee_code'  => $i->employee->employee_id,
                'position'       => $i->employee->position,
                'daily_rate'     => $i->daily_rate,
                'days_worked'    => $i->days_worked,
                'overtime_hours' => $i->overtime_hours,
                'late_hours'     => $i->late_hours,
                'allowances'     => $i->allowances,
                'deductions'     => $i->deductions,
                'gross_pay'      => $i->gross_pay,
                'net_pay'        => $i->net_pay,
                'adjustments'    => $i->adjustments,
            ]),
        ]);
    }

    /**
     * POST /payroll-periods/{id}/finalize
     * Finalize a payroll period (change status from draft to finalized).
     */
    public function finalize(PayrollPeriod $payrollPeriod): JsonResponse
    {
        if ($payrollPeriod->isFinalized()) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll period is already finalized.',
            ], 422);
        }

        if ($payrollPeriod->payrollInputs->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot finalize a payroll period with no encoded employees.',
            ], 422);
        }

        $payrollPeriod->update(['status' => 'finalized']);

        return response()->json([
            'success' => true,
            'message' => 'Payroll period finalized successfully.',
            'payroll_period' => $payrollPeriod,
        ]);
    }

    /**
 * DELETE /payroll-periods/{id}
 * Super admin only — hard deletes the period and cascades to inputs.
 */
public function destroy(PayrollPeriod $payrollPeriod): JsonResponse
{
    if (!Auth::user()->isAdmin()) {
        return response()->json(['message' => 'Unauthorized.'], 403);
    }

    $payrollPeriod->delete();

    return response()->json(['message' => 'Payroll period deleted.']);
}
}
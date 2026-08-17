<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Traits\LogsAudit;
class PayrollPeriodController extends Controller
{
    /**
     * GET /payroll-periods/create
     * Show form to create a new payroll period.
     */
    public function create()
    {
        return Inertia::render('PayrollPeriods/Create');
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
    public function finalize(PayrollPeriod $payrollPeriod)
    {
        if ($payrollPeriod->isFinalized()) {
            return back()->with('error', 'Payroll period is already finalized.');
        }

        if ($payrollPeriod->payrollInputs->count() === 0) {
            return back()->with('error', 'Cannot finalize a payroll period with no encoded employees.');
        }

        $payrollPeriod->update(['status' => 'finalized']);
        LogsAudit::logAction('finalize', 'payroll_period', "Finalized payroll period ID {$payrollPeriod->id}");

        return back()->with('success', 'Payroll period finalized successfully.');
    }


    /**
 * DELETE /payroll-periods/{id}
 * Super admin only — hard deletes the period and cascades to inputs.
 */
public function archive(PayrollPeriod $payrollPeriod)
{
    if (!Auth::user()->isAdmin()) {
        return back()->with('error', 'Unauthorized.');
    }

    $payrollPeriod->update(['status' => 'archived']);
    LogsAudit::logAction('archive', 'payroll_period', "Archived payroll period ID {$payrollPeriod->id}");

    return back()->with('success', 'Payroll period archived.');
}

public function archived()
{
    $periods = PayrollPeriod::with('createdBy')
        ->where('status', 'archived')
        ->orderByDesc('cutoff_start')
        ->get();

    return Inertia::render('ManualPayrollAttendance/Archived', [
        'periods' => $periods->map(fn($period) => [
            'id'            => $period->id,
            'cutoff_start'  => $period->cutoff_start?->toDateString(),
            'cutoff_end'    => $period->cutoff_end?->toDateString(),
            'payroll_date'  => $period->payroll_date?->toDateString(),
            'period_label'  => $period->period_label,
            'created_by'    => $period->createdBy?->name,
            'encoded_count' => $period->payrollInputs ? $period->payrollInputs->count() : 0,
            'total_gross'   => $period->total_gross_pay,
        ]),
    ]);
}

public function restore(PayrollPeriod $payrollPeriod)
{
    if (!Auth::user()->isAdmin()) {
        return back()->with('error', 'Unauthorized.');
    }

    $payrollPeriod->update(['status' => 'draft']);
    LogsAudit::logAction('restore', 'payroll_period', "Restored payroll period ID {$payrollPeriod->id}");

    return back()->with('success', 'Payroll period restored.');
}
}
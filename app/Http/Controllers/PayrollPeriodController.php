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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cutoff_start' => 'required|date',
            'cutoff_end'   => 'required|date|after_or_equal:cutoff_start',
            'payroll_date' => 'required|date|after_or_equal:cutoff_end',
        ]);

        $period = PayrollPeriod::create([
            ...$validated,
            'status'     => 'draft',
            'created_by' => Auth::id(),
        ]);

        return response()->json($period, 201);
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
}
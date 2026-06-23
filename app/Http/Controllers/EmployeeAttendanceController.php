<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceController extends Controller
{
    /**
     * GET /employee-attendance
     * Show employee's attendance records
     */
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'No employee record found for this user.');
        }

        // Get current payroll period
        $currentPeriod = PayrollPeriod::where('status', 'draft')
            ->where('cutoff_start', '<=', now())
            ->where('cutoff_end', '>=', now())
            ->first();

        // Get attendance records for current period
        $attendances = [];
        $totalHours = 0;
        $totalDays = 0;

        if ($currentPeriod) {
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$currentPeriod->cutoff_start->toDateString(), $currentPeriod->cutoff_end->toDateString()])
                ->orderBy('date')
                ->get();

            $totalHours = $attendances->sum('rendered_hours');
            $totalDays = $attendances->sum('computed_days');
        }

        // Get recent attendance records (last 30 days)
        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->orderBy('date', 'desc')
            ->get();

        return view('employee-attendance.index', compact(
            'employee',
            'currentPeriod',
            'attendances',
            'totalHours',
            'totalDays',
            'recentAttendances'
        ));
    }

    /**
     * GET /employee-attendance/create
     * Show form to add attendance record
     */
    public function create()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'No employee record found for this user.');
        }

        return view('employee-attendance.create', compact('employee'));
    }

    /**
     * POST /employee-attendance
     * Save attendance record
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee record found for this user.',
            ], 403);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Log the received data for debugging
        \Log::info('Attendance save attempt', [
            'time_in' => $validated['time_in'] ?? 'null',
            'time_out' => $validated['time_out'] ?? 'null',
            'time_in_type' => gettype($validated['time_in'] ?? null),
            'time_out_type' => gettype($validated['time_out'] ?? null),
        ]);

        // Check if attendance already exists for this date
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $validated['date'])
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record already exists for this date. Please edit the existing record.',
            ], 422);
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'time_in' => $validated['time_in'] ?? null,
            'time_out' => $validated['time_out'] ?? null,
            'remarks' => $validated['remarks'] ?? '',
        ]);

        // Log the created attendance
        \Log::info('Attendance created', [
            'id' => $attendance->id,
            'time_in' => $attendance->time_in,
            'time_out' => $attendance->time_out,
            'rendered_hours' => $attendance->rendered_hours,
            'computed_days' => $attendance->computed_days,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully.',
            'attendance' => $attendance,
        ]);
    }

    /**
     * GET /employee-attendance/{attendance}/edit
     * Show form to edit attendance record
     */
    public function edit(Attendance $attendance)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee || $attendance->employee_id !== $employee->id) {
            abort(403, 'You can only edit your own attendance records.');
        }

        return view('employee-attendance.edit', compact('attendance', 'employee'));
    }

    /**
     * PUT /employee-attendance/{attendance}
     * Update attendance record
     */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee || $attendance->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own attendance records.',
            ], 403);
        }

        $validated = $request->validate([
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Log the update attempt
        \Log::info('Attendance update attempt', [
            'attendance_id' => $attendance->id,
            'time_in' => $validated['time_in'] ?? 'null',
            'time_out' => $validated['time_out'] ?? 'null',
            'remarks' => $validated['remarks'] ?? 'null',
        ]);

        $attendance->fill([
            'time_in' => $validated['time_in'] ?? null,
            'time_out' => $validated['time_out'] ?? null,
            'remarks' => $validated['remarks'] ?? '',
        ]);
        $attendance->save();

        // Log the updated attendance
        \Log::info('Attendance updated', [
            'id' => $attendance->id,
            'time_in' => $attendance->time_in,
            'time_out' => $attendance->time_out,
            'rendered_hours' => $attendance->rendered_hours,
            'computed_days' => $attendance->computed_days,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully.',
            'attendance' => $attendance,
        ]);
    }

    /**
     * DELETE /employee-attendance/{attendance}
     * Delete attendance record
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee || $attendance->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own attendance records.',
            ], 403);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully.',
        ]);
    }

    /**
     * POST /employee-attendance/compute-period
     * Get attendance summary for a specific period
     */
    public function getPeriodSummary(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee record found for this user.',
            ], 403);
        }

        $validated = $request->validate([
            'period_id' => 'nullable|exists:payroll_periods,id',
        ]);

        if (!empty($validated['period_id'])) {
            $period = PayrollPeriod::find($validated['period_id']);
        } else {
            $period = PayrollPeriod::where('status', 'draft')
                ->where('cutoff_start', '<=', now())
                ->where('cutoff_end', '>=', now())
                ->first();
        }

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'No payroll period found.',
            ], 404);
        }

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'period' => [
                'id' => $period->id,
                'cutoff_start' => $period->cutoff_start->toDateString(),
                'cutoff_end' => $period->cutoff_end->toDateString(),
            ],
            'attendances' => $attendances,
            'total_hours' => $attendances->sum('rendered_hours'),
            'total_days' => $attendances->sum('computed_days'),
        ]);
    }
}

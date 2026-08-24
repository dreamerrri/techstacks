<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

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

        // Admin/HR without employee record should be redirected to manual payroll attendance
        if (!$employee) {
            if ($user->isAdmin() || $user->isHR()) {
                return redirect()->route('manual-payroll-attendance.index');
            }
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

        return Inertia::render('EmployeeAttendance/Index', [
            'employee'          => $employee,
            'currentPeriod'     => $currentPeriod,
            'attendances'       => $attendances,
            'totalHours'        => $totalHours,
            'totalDays'         => $totalDays,
            'recentAttendances' => $recentAttendances,
        ]);
    }

    /**
     * GET /employee-attendance/create
     * Show form to add attendance record
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        // Admin/HR without employee record should be redirected to manual payroll attendance
        if (!$employee) {
            if ($user->isAdmin() || $user->isHR()) {
                return redirect()->route('manual-payroll-attendance.index');
            }
            abort(403, 'No employee record found for this user.');
        }

        // Check if attendance exists for today (only if not creating a new record)
        $todayAttendance = null;
        if (!$request->has('new') || $request->get('new') !== 'true') {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', now()->toDateString())
                ->first();
        }

        return Inertia::render('EmployeeAttendance/Create', [
            'employee'         => $employee,
            'todayAttendance'  => $todayAttendance,
            'isNew'            => $request->has('new') && $request->get('new') === 'true',
        ]);
    }

    /**
     * POST /employee-attendance
     * Save attendance record
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'No employee record found for this user.');
        }

        $validated = $request->validate([
            // Self-service: no future dates, backdating limited to 7 days (HR/Admin
            // encode older records via Manual Payroll Attendance instead)
            'date' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subDays(7)->toDateString(),
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Auto-clock-out if exceeding 9 hours
        $autoClockOutApplied = false;
        $originalTimeOut = $validated['time_out'] ?? null;
        
        if (!empty($validated['time_in']) && !empty($validated['time_out'])) {
            $timeIn = Carbon::createFromFormat('H:i', $validated['time_in']);
            $timeOut = Carbon::createFromFormat('H:i', $validated['time_out']);
            
            // Calculate total minutes
            $totalMinutes = $timeOut->diffInMinutes($timeIn);
            if ($totalMinutes < 0) {
                $totalMinutes = abs($totalMinutes);
            }
            
            // Convert to hours and apply break logic (1 hour break for shifts > 4 hours)
            $hours = $totalMinutes / 60;
            if ($hours > 4) {
                $hours -= 1;
            }
            
            // If exceeding 9 hours, auto-clock-out at 9 hours
            if ($hours > 9) {
                // Calculate the time_out that would result in exactly 9 hours
                // 9 hours + 1 hour break = 10 hours total from time_in
                $maxTimeOut = $timeIn->copy()->addHours(10);
                $validated['time_out'] = $maxTimeOut->format('H:i');
                $autoClockOutApplied = true;
                
                \Log::info('Auto clock-out applied: exceeded 9 hours', [
                    'original_time_out' => $timeOut->format('H:i'),
                    'auto_time_out' => $validated['time_out'],
                    'employee_id' => $employee->id,
                    'date' => $validated['date'],
                ]);
            }
        }

        // Log the received data for debugging
        \Log::info('Attendance save attempt', [
            'time_in' => $validated['time_in'] ?? 'null',
            'time_out' => $validated['time_out'] ?? 'null',
            'time_in_type' => gettype($validated['time_in'] ?? null),
            'time_out_type' => gettype($validated['time_out'] ?? null),
        ]);

        // Check if attendance already exists for this date, update if it does
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $validated['date'])
            ->first();

        if ($existingAttendance) {
            // Prevent employees from changing any time entry once clocked out
            // (rendered_hours/computed_days feed payroll, so time_in is protected too)
            if ($existingAttendance->time_out && !$user->isAdmin() && !$user->isHR()) {
                $existingIn = $existingAttendance->time_in ? Carbon::parse($existingAttendance->time_in)->format('H:i') : null;
                $existingOut = Carbon::parse($existingAttendance->time_out)->format('H:i');
                $timeOutChanged = $validated['time_out'] && $validated['time_out'] !== $existingOut;
                $timeInChanged = $validated['time_in'] !== $existingIn;
                if ($timeOutChanged || $timeInChanged) {
                    return back()->with('error', 'You cannot change your attendance after clocking out. Contact HR/Admin for assistance.');
                }
            }

            $existingAttendance->update([
                'time_in' => $validated['time_in'] ?? null,
                'time_out' => $validated['time_out'] ?? null,
                'remarks' => $validated['remarks'] ?? '',
            ]);
            $attendance = $existingAttendance;
        } else {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $validated['date'],
                'time_in' => $validated['time_in'] ?? null,
                'time_out' => $validated['time_out'] ?? null,
                'remarks' => $validated['remarks'] ?? '',
            ]);
        }

        // Log the created attendance
        \Log::info('Attendance created', [
            'id' => $attendance->id,
            'time_in' => $attendance->time_in,
            'time_out' => $attendance->time_out,
            'rendered_hours' => $attendance->rendered_hours,
            'computed_days' => $attendance->computed_days,
        ]);

        $message = 'Attendance recorded successfully.';
        if ($autoClockOutApplied) {
            $message = 'Attendance recorded successfully. Auto clock-out applied (exceeded 9 hours).';
        }

        return redirect()->route('employee-attendance.index')
            ->with('success', $message);
    }

    /**
     * PUT /employee-attendance/{attendance}
     * Update attendance record (admin/HR only)
     */
    public function update(Request $request, Attendance $attendance)
    {
        $user = Auth::user();

        // Only admin and HR can update attendance records
        if (!$user->isAdmin() && !$user->isHR()) {
            return back()->with('error', 'Only administrators and HR can update attendance records.');
        }

        // Log incoming request for debugging
        \Log::info('Attendance update request', [
            'all' => $request->all(),
            'time_in' => $request->input('time_in'),
            'time_out' => $request->input('time_out'),
        ]);

        $validated = $request->validate([
            'date' => 'required|date',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Handle time_in separately
        $timeIn = $request->input('time_in');
        if ($timeIn !== null && trim($timeIn) !== '') {
            try {
                Carbon::createFromFormat('H:i', $timeIn);
                $validated['time_in'] = $timeIn;
            } catch (\Exception $e) {
                return back()->with('error', 'The time in field must match the format H:i.');
            }
        } else {
            $validated['time_in'] = null;
        }

        // Handle time_out separately
        $timeOut = $request->input('time_out');
        if ($timeOut !== null && trim($timeOut) !== '') {
            try {
                Carbon::createFromFormat('H:i', $timeOut);
                $validated['time_out'] = $timeOut;
            } catch (\Exception $e) {
                return back()->with('error', 'The time out field must match the format H:i.');
            }
        } else {
            $validated['time_out'] = null;
        }

        // Auto-clock-out if exceeding 9 hours
        $autoClockOutApplied = false;
        
        if (!empty($validated['time_in']) && !empty($validated['time_out'])) {
            $timeIn = Carbon::createFromFormat('H:i', $validated['time_in']);
            $timeOut = Carbon::createFromFormat('H:i', $validated['time_out']);
            
            // Calculate total minutes
            $totalMinutes = $timeOut->diffInMinutes($timeIn);
            if ($totalMinutes < 0) {
                $totalMinutes = abs($totalMinutes);
            }
            
            // Convert to hours and apply break logic (1 hour break for shifts > 4 hours)
            $hours = $totalMinutes / 60;
            if ($hours > 4) {
                $hours -= 1;
            }
            
            // If exceeding 9 hours, auto-clock-out at 9 hours
            if ($hours > 9) {
                // Calculate the time_out that would result in exactly 9 hours
                // 9 hours + 1 hour break = 10 hours total from time_in
                $maxTimeOut = $timeIn->copy()->addHours(10);
                $validated['time_out'] = $maxTimeOut->format('H:i');
                $autoClockOutApplied = true;
                
                \Log::info('Auto clock-out applied (HR/Admin edit): exceeded 9 hours', [
                    'original_time_out' => $timeOut->format('H:i'),
                    'auto_time_out' => $validated['time_out'],
                    'attendance_id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'date' => $validated['date'],
                    'edited_by' => $user->id,
                ]);
            }
        }

        $attendance->update([
            'date' => $validated['date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'remarks' => $validated['remarks'],
        ]);

        // Log the updated attendance
        \Log::info('Attendance updated by HR/Admin', [
            'id' => $attendance->id,
            'time_in' => $attendance->time_in,
            'time_out' => $attendance->time_out,
            'rendered_hours' => $attendance->rendered_hours,
            'computed_days' => $attendance->computed_days,
            'edited_by' => $user->id,
        ]);

        $message = 'Attendance updated successfully.';
        if ($autoClockOutApplied) {
            $message = 'Attendance updated successfully. Auto clock-out applied (exceeded 9 hours).';
        }

        return back()->with('success', $message);
    }

    /**
     * DELETE /employee-attendance/{attendance}
     * Delete attendance record (only admin/HR)
     */
    public function destroy(Attendance $attendance)
    {
        $user = Auth::user();

        // Only admin and HR can delete attendance records
        if (!$user->isAdmin() && !$user->isHR()) {
            return back()->with('error', 'Only administrators and HR can delete attendance records.');
        }

        $attendance->delete();

        return back()->with('success', 'Attendance deleted successfully.');
    }

    /**
     * GET /employee-attendance/employee/{employee}
     * Show attendance records for a specific employee (HR/Admin only)
     */
    public function showEmployee(Employee $employee)
    {
        $user = Auth::user();

        // Only admin and HR can view other employees' attendance
        if (!$user->isAdmin() && !$user->isHR()) {
            abort(403, 'Only administrators and HR can view employee attendance records.');
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

        return Inertia::render('EmployeeAttendance/ShowEmployee', [
            'employee'          => $employee,
            'currentPeriod'     => $currentPeriod,
            'attendances'       => $attendances,
            'totalHours'        => $totalHours,
            'totalDays'         => $totalDays,
            'recentAttendances' => $recentAttendances,
        ]);
    }
}

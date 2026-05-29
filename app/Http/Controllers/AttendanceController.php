<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Show the form for creating/editing attendance for an employee
     */
    public function create(Employee $employee)
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        
        // Check if attendance already exists for current month
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        if ($attendance) {
            return redirect()->route('attendance.edit', [$employee, $attendance]);
        }

        return view('attendance.create', compact('employee', 'currentMonth', 'currentYear'));
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'days_worked' => 'required|integer|min:0|max:31',
            'regular_hours' => 'required|integer|min:0|max:744',
            'overtime_hours' => 'required|integer|min:0|max:500',
            'late_hours' => 'required|integer|min:0|max:100',
            'night_differential_hours' => 'required|integer|min:0|max:500',
            'regular_holiday_worked' => 'required|integer|min:0|max:31',
        ]);

        // Check if attendance already exists for this month/year
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Attendance record already exists for this month/year.');
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'month' => $validated['month'],
            'year' => $validated['year'],
            'days_worked' => $validated['days_worked'],
            'regular_hours' => $validated['regular_hours'],
            'overtime_hours' => $validated['overtime_hours'],
            'late_hours' => $validated['late_hours'],
            'night_differential_hours' => $validated['night_differential_hours'],
            'regular_holiday_worked' => $validated['regular_holiday_worked'],
        ]);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Show the form for editing the specified attendance
     */
    public function edit($employeeId, $attendanceId)
    {
        $employee = Employee::findOrFail($employeeId);
        $attendance = Attendance::where('id', $attendanceId)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        return view('attendance.edit', compact('employee', 'attendance'));
    }

    /**
     * Update the specified attendance
     */
    public function update(Request $request, $employeeId, $attendanceId)
    {
        $employee = Employee::findOrFail($employeeId);
        $attendance = Attendance::where('id', $attendanceId)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        $validated = $request->validate([
            'days_worked' => 'required|integer|min:0|max:31',
            'regular_hours' => 'required|integer|min:0|max:744',
            'overtime_hours' => 'required|integer|min:0|max:500',
            'late_hours' => 'required|integer|min:0|max:100',
            'night_differential_hours' => 'required|integer|min:0|max:500',
            'regular_holiday_worked' => 'required|integer|min:0|max:31',
        ]);

        // Update using fill to ensure fillable fields are respected
        $attendance->fill([
            'days_worked' => $validated['days_worked'],
            'regular_hours' => $validated['regular_hours'],
            'overtime_hours' => $validated['overtime_hours'],
            'late_hours' => $validated['late_hours'],
            'night_differential_hours' => $validated['night_differential_hours'],
            'regular_holiday_worked' => $validated['regular_holiday_worked'],
        ]);
        $attendance->save();

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified attendance
     */
    public function destroy($employeeId, $attendanceId)
    {
        $employee = Employee::findOrFail($employeeId);
        $attendance = Attendance::where('id', $attendanceId)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        $attendance->delete();

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Attendance record deleted successfully.');
    }
}

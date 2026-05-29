@extends('layouts.app')

@section('title', 'Edit Attendance - ' . $employee->full_name)

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $colorDark = $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2');
    $monthName = date('F', mktime(0, 0, 0, $attendance->month, 1));
@endphp

<div style="margin-bottom:20px;">
    <a href="{{ route('employees.show', $employee) }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
        <i class="fas fa-arrow-left"></i> Back to Employee Profile
    </a>
</div>

<div class="card">
    <h2><i class="fas fa-clock" style="color:#dc2626;"></i> Edit Attendance — {{ $employee->full_name }} ({{ $monthName }} {{ $attendance->year }})</h2>

    <form method="POST" action="{{ route('attendance.update', [$employee->id, $attendance->id]) }}">
        @csrf @method('PUT')

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-bottom:20px;">
            <div>
                <label style="display:block; font-weight:600; color:#1f2937; margin-bottom:8px; font-size:14px;">Days Worked</label>
                <input type="number" name="days_worked" value="{{ $attendance->days_worked }}" min="0" max="31" required
                       style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:10px; font-size:14px;">
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">Number of days worked in the month</div>
            </div>

            <div>
                <label style="display:block; font-weight:600; color:#1f2937; margin-bottom:8px; font-size:14px;">Regular Hours</label>
                <input type="number" name="regular_hours" value="{{ $attendance->regular_hours }}" min="0" max="744" required
                       style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:10px; font-size:14px;">
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">Total regular working hours</div>
            </div>

            <div>
                <label style="display:block; font-weight:600; color:#1f2937; margin-bottom:8px; font-size:14px;">Overtime Hours</label>
                <input type="number" name="overtime_hours" value="{{ $attendance->overtime_hours }}" min="0" max="500" required
                       style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:10px; font-size:14px;">
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">Total overtime hours</div>
            </div>

            <div>
                <label style="display:block; font-weight:600; color:#1f2937; margin-bottom:8px; font-size:14px;">Late Hours</label>
                <input type="number" name="late_hours" value="{{ $attendance->late_hours }}" min="0" max="100" required
                       style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:10px; font-size:14px;">
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">Total late arrival hours</div>
            </div>

            <div>
                <label style="display:block; font-weight:600; color:#1f2937; margin-bottom:8px; font-size:14px;">Night Differential Hours</label>
                <input type="number" name="night_differential_hours" value="{{ $attendance->night_differential_hours }}" min="0" max="500" required
                       style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:10px; font-size:14px;">
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">Night shift differential hours</div>
            </div>

            <div>
                <label style="display:block; font-weight:600; color:#1f2937; margin-bottom:8px; font-size:14px;">Regular Holiday Worked</label>
                <input type="number" name="regular_holiday_worked" value="{{ $attendance->regular_holiday_worked }}" min="0" max="31" required
                       style="width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:10px; font-size:14px;">
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">Days worked on regular holidays</div>
            </div>
        </div>

        <div style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
            <button type="submit"
                    style="padding:10px 24px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;">
                <i class="fas fa-save"></i> Update Attendance
            </button>
            <a href="{{ route('employees.show', $employee) }}"
               style="padding:10px 24px; background:#f3f4f6; color:#374151; border-radius:6px; text-decoration:none; font-weight:600;">
                Cancel
            </a>
        </div>
    </form>

    <form method="POST" action="{{ route('attendance.destroy', [$employee->id, $attendance->id]) }}"
          data-confirm="This attendance record will be permanently deleted."
          data-confirm-title="Delete Attendance Record?"
          data-confirm-icon="warning"
          data-confirm-btn="Yes, delete"
          style="margin-top:12px;">
        @csrf @method('DELETE')
        <button type="submit"
                style="padding:10px 24px; background:#fecaca; color:#991b1b; border:none; border-radius:6px; cursor:pointer; font-weight:600;">
            <i class="fas fa-trash"></i> Delete Record
        </button>
    </form>
</div>

@endsection
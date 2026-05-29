@extends('layouts.app')

@section('title', 'Edit Attendance - ' . $employee->full_name)

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $monthName = date('F', mktime(0, 0, 0, $attendance->month, 1));
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-clock"></i> Attendance Management
        </div>
        <p style="color:#6b7280; margin:0;">Edit attendance record for {{ $employee->full_name }} ({{ $monthName }} {{ $attendance->year }})</p>
    </div>
    <a href="{{ route('employees.show', $employee) }}"
       style="padding:10px 20px; background:#f3f4f6; color:#6b7280; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
        <i class="fas fa-arrow-left"></i> Back to Employee
    </a>
</div>

 {{-- Employee Info Card --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
        <div style="width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2') }}); display:flex; align-items:center; justify-content:center; color:white; font-size:24px; font-weight:700; flex-shrink:0;">
            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
        </div>
        <div style="flex:1;">
            <h2 style="margin:0 0 4px; font-size:18px; color:#1f2937;">{{ $employee->full_name }}</h2>
            <div style="font-size:14px; color:#6b7280;">
                <span>{{ $employee->position }}</span> — <span>{{ $employee->department }}</span>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:12px; color:#6b7280;">Period</div>
            <div style="font-size:16px; font-weight:600; color:#1f2937;">{{ $monthName }} {{ $attendance->year }}</div>
        </div>
    </div>
</div>

 {{-- Attendance Form --}}
<div class="card">
    <h2 style="margin:0 0 20px 0;">Attendance Details</h2>

    <form method="POST" action="{{ route('attendance.update', [$employee, $attendance]) }}">
        @csrf @method('PUT')

        {{-- Attendance Values --}}
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

        {{-- Submit Button --}}
        <div style="display:flex; gap:12px; margin-top:24px;">
            <button type="submit"
                    style="padding:12px 24px; background:{{ $color }}; color:white; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600;">
                <i class="fas fa-save"></i> Update Attendance Record
            </button>
            <form method="POST" action="{{ route('attendance.destroy', [$employee, $attendance]) }}"
                  data-confirm="This attendance record will be permanently deleted."
                  data-confirm-title="Delete Attendance Record?"
                  data-confirm-icon="warning"
                  data-confirm-btn="Yes, delete"
                  style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit"
                        style="padding:12px 24px; background:#fecaca; color:#991b1b; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
            <a href="{{ route('employees.show', $employee) }}"
               style="padding:12px 24px; background:#f3f4f6; color:#6b7280; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection

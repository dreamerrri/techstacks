@extends('layouts.app')

@section('title', 'My Attendance')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Dashboard</a>
    <i class="icon-[ph--caret-right-fill]" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">My Attendance</span>
@endsection

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $colorDark = $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2');
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="icon-[ph--clock-fill]"></i> My Attendance
        </div>
        <h2 style="margin:8px 0 4px 0;">Attendance Records</h2>
        <p style="color:#6b7280; margin:0;">
            Track your daily time-in/time-out records
        </p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('employee-attendance.create', ['new' => 'true']) }}"
           style="padding:12px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="icon-[ph--plus-fill]"></i> Add New Attendance
        </a>
        <a href="{{ route('employee-attendance.create') }}"
           style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="icon-[ph--pencil-fill]"></i> Edit Today's Attendance
        </a>
    </div>
</div>

{{-- Current Period Summary --}}
@if($currentPeriod)
<div class="card" style="padding:24px; margin-bottom:24px;">
    <h3 style="margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
        <i class="icon-[ph--calendar-fill]" style="color:#6b7280;"></i> Current Payroll Period
    </h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
        <div style="padding:16px; background:#f9fafb; border-radius:8px; border-left:4px solid #3b82f6;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Period</div>
            <div style="font-size:14px; font-weight:600; color:#1f2937;">
                {{ $currentPeriod->cutoff_start->format('M d') }} - {{ $currentPeriod->cutoff_end->format('M d, Y') }}
            </div>
        </div>
        <div style="padding:16px; background:#f9fafb; border-radius:8px; border-left:4px solid #10b981;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Total Rendered Hours</div>
            <div style="font-size:18px; font-weight:700; color:#10b981;">
                {{ number_format($totalHours, 2) }} hrs
            </div>
        </div>
        <div style="padding:16px; background:#f9fafb; border-radius:8px; border-left:4px solid #8b5cf6;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Total Computed Days</div>
            <div style="font-size:18px; font-weight:700; color:#8b5cf6;">
                {{ number_format($totalDays, 2) }} days
            </div>
        </div>
    </div>
</div>
@endif

{{-- Attendance Records for Current Period --}}
@if($currentPeriod && $attendances->count() > 0)
<div class="card" style="padding:0; overflow:hidden; margin-bottom:24px;">
    <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb;">
        <h3 style="margin:0;">Attendance Records - Current Period</h3>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Date</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Time In</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Time Out</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Rendered Hours</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Computed Days</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Remarks</th>
                    <th style="padding:12px 16px; text-align:center; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $attendance->date->format('M d, Y') }}
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $attendance->time_in ? $attendance->time_in : '-' }}
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $attendance->time_out ? $attendance->time_out : '-' }}
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937; font-weight:600;">
                        {{ number_format($attendance->rendered_hours, 2) }} hrs
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937; font-weight:600;">
                        {{ number_format($attendance->computed_days, 2) }} days
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#6b7280;">
                        {{ $attendance->remarks ?? '-' }}
                    </td>
                    <td style="padding:12px 16px; text-align:center;">
                        <a href="{{ route('employee-attendance.create') }}"
                           style="padding:6px 12px; background:#3b82f6; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px; text-decoration:none; display:inline-block; margin-right:4px;">
                            <i class="icon-[ph--pencil-fill]"></i>
                        </a>
                        <button onclick="deleteAttendance({{ $attendance->id }})"
                                style="padding:6px 12px; background:#ef4444; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
                            <i class="icon-[ph--trash-fill]"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Recent Attendance --}}
@if($recentAttendances->count() > 0)
<div class="card" style="padding:0; overflow:hidden;">
    <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb;">
        <h3 style="margin:0;">Recent Attendance (Last 30 Days)</h3>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Date</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Time In</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Time Out</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Rendered Hours</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Computed Days</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentAttendances as $attendance)
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $attendance->date->format('M d, Y') }}
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $attendance->time_in ? $attendance->time_in : '-' }}
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $attendance->time_out ? $attendance->time_out : '-' }}
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937; font-weight:600;">
                        {{ number_format($attendance->rendered_hours, 2) }} hrs
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937; font-weight:600;">
                        {{ number_format($attendance->computed_days, 2) }} days
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>

    function deleteAttendance(attendanceId) {
    Swal.fire({
        title: 'Delete Attendance?',
        text: 'Are you sure you want to delete this attendance record?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Back',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch('{{ route('employee-attendance.destroy', ':id') }}'.replace(':id', attendanceId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.notyf.success(data.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                window.notyf.error(data.message);
            }
        })
        .catch(error => {
            window.notyf.error('Failed to delete attendance: ' + error.message);
        });
    });
}



</script>
@endsection

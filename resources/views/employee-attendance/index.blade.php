@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR    = $user->isHR();
    // Role-based accent for the primary action button — admin/HR/employee each get a
    // distinct, but still theme-aware, color instead of a hardcoded hex per role.
    $roleBtnClass = $isAdmin ? 'btn-error' : ($isHR ? 'btn-info' : 'btn-primary');
@endphp

{{-- Header --}}
<div class="flex justify-between items-center flex-wrap gap-3 mb-6">
    <div>
        <span class="badge badge-soft badge-info mb-2">
            <i class="icon-[tabler--clock]"></i> My Attendance
        </span>
        <h2 class="text-lg font-bold text-base-content mt-2 mb-1">Attendance Records</h2>
        <p class="text-subtle m-0">
            Track your daily time-in/time-out records
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('employee-attendance.create', ['new' => 'true']) }}" class="btn btn-soft {{ $roleBtnClass }}">
            <i class="icon-[tabler--plus]"></i> Add New Attendance
        </a>
        <a href="{{ route('employee-attendance.create') }}" class="btn btn-soft">
            <i class="icon-[tabler--pencil]"></i> Edit Today's Attendance
        </a>
    </div>
</div>

{{-- Current Period Summary --}}
@if($currentPeriod)
<div class="card bg-base-100 border border-base-300 p-6 mb-6">
    <h3 class="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
        <i class="icon-[tabler--calendar] text-subtle"></i> Current Payroll Period
    </h3>
    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="p-4 bg-base-200 rounded-lg border-l-4 border-info">
            <div class="text-xs text-subtle mb-1">Period</div>
            <div class="text-sm font-semibold text-base-content">
                {{ $currentPeriod->cutoff_start->format('M d') }} - {{ $currentPeriod->cutoff_end->format('M d, Y') }}
            </div>
        </div>
        <div class="p-4 bg-base-200 rounded-lg border-l-4 border-success">
            <div class="text-xs text-subtle mb-1">Total Rendered Hours</div>
            <div class="text-lg font-bold text-success">
                {{ number_format($totalHours, 2) }} hrs
            </div>
        </div>
        <div class="p-4 bg-base-200 rounded-lg border-l-4 border-accent">
            <div class="text-xs text-subtle mb-1">Total Computed Days</div>
            <div class="text-lg font-bold text-accent">
                {{ number_format($totalDays, 2) }} days
            </div>
        </div>
    </div>
</div>
@endif

{{-- Attendance Records for Current Period --}}
@if($currentPeriod && $attendances->count() > 0)
<div class="card bg-base-100 border border-base-300 p-0 overflow-hidden mb-6">
    <div class="px-6 py-5 border-b border-base-300">
        <h3 class="text-sm font-bold text-base-content m-0">Attendance Records — Current Period</h3>
    </div>

    <x-data-table maxHeight="50vh">
        <x-slot:head>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Rendered Hours</th>
            <th>Computed Days</th>
            <th>Remarks</th>
            <th class="text-center">Actions</th>
        </x-slot:head>

        @foreach($attendances as $attendance)
            <tr class="row-hover">
                <td class="text-base-content">{{ $attendance->date->format('M d, Y') }}</td>
                <td class="text-base-content">{{ $attendance->time_in ?: '-' }}</td>
                <td class="text-base-content">{{ $attendance->time_out ?: '-' }}</td>
                <td class="text-base-content font-semibold">{{ number_format($attendance->rendered_hours, 2) }} hrs</td>
                <td class="text-base-content font-semibold">{{ number_format($attendance->computed_days, 2) }} days</td>
                <td class="text-subtle">{{ $attendance->remarks ?? '-' }}</td>
                <td class="text-center">
                    <div class="flex gap-2 justify-center">
                        <a href="{{ route('employee-attendance.create') }}" class="btn btn-soft btn-info btn-sm">
                            <i class="icon-[tabler--pencil]"></i>
                        </a>
                        <button onclick="deleteAttendance({{ $attendance->id }})" class="btn btn-soft btn-error btn-sm">
                            <i class="icon-[tabler--trash]"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</div>
@endif

{{-- Recent Attendance --}}
@if($recentAttendances->count() > 0)
<div class="card bg-base-100 border border-base-300 p-0 overflow-hidden">
    <div class="px-6 py-5 border-b border-base-300">
        <h3 class="text-sm font-bold text-base-content m-0">Recent Attendance (Last 30 Days)</h3>
    </div>

    <x-data-table maxHeight="50vh">
        <x-slot:head>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Rendered Hours</th>
            <th>Computed Days</th>
        </x-slot:head>

        @foreach($recentAttendances as $attendance)
            <tr class="row-hover">
                <td class="text-base-content">{{ $attendance->date->format('M d, Y') }}</td>
                <td class="text-base-content">{{ $attendance->time_in ?: '-' }}</td>
                <td class="text-base-content">{{ $attendance->time_out ?: '-' }}</td>
                <td class="text-base-content font-semibold">{{ number_format($attendance->rendered_hours, 2) }} hrs</td>
                <td class="text-base-content font-semibold">{{ number_format($attendance->computed_days, 2) }} days</td>
            </tr>
        @endforeach
    </x-data-table>
</div>
@endif

@endsection

@section('scripts')
<script>
function deleteAttendance(attendanceId) {
    const style = getComputedStyle(document.documentElement);
    const errorColor   = style.getPropertyValue('--color-error').trim();
    const neutralColor = style.getPropertyValue('--color-neutral').trim();

    Swal.fire({
        title: 'Delete Attendance?',
        text: 'Are you sure you want to delete this attendance record?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: errorColor || '#ef4444',
        cancelButtonColor: neutralColor || '#6b7280',
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
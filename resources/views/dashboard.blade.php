@extends('layouts.app')

@section('title')
    @if($user->role === 'admin') Admin Dashboard
    @elseif($user->role === 'hr') HR Dashboard
    @else Dashboard
    @endif
@endsection

@section('content')

@php
    $isAdmin = $user->role === 'admin';
    $isHR    = $user->role === 'hr';
@endphp

{{-- Role access badge --}}
@if($isAdmin)
    <span class="badge badge-soft badge-success mb-4">
        <i class="icon-[tabler--shield-check]"></i> Administrator Access
    </span>
@elseif($isHR)
    <span class="badge badge-soft badge-success mb-4">
        <i class="icon-[tabler--user]"></i> HR Department Access
    </span>
@endif

<div class="text-base-content/60 text-lg mb-5">
    Welcome back, <strong>{{ $user->name }}</strong>
    @if($isAdmin) — You have full administrative access.
    @elseif($isHR) — You have HR access privileges.
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">

    @if($isAdmin)
        <a href="{{ route('users.index') }}" class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #dc2626; background: #dc262620;">
                <i class="icon-[tabler--users]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['total_users'] }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Total Users</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #991b1b; background: #991b1b20;">
                <i class="icon-[tabler--shield-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['admin_users'] }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Admin Users</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #fbbf24; background: #fbbf2420;">
                <i class="icon-[tabler--user]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['hr_users'] }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">HR Personnel</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #10b981; background: #10b98120;">
                <i class="icon-[tabler--circle-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['active_users'] }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Active Accounts</div>
        </a>

    @elseif($isHR)
        <a href="{{ route('employees.index') }}" class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #2563eb; background: #2563eb20;">
                <i class="icon-[tabler--users]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['total_employees'] }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Total Employees</div>
        </a>

        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #1e40af; background: #1e40af20;">
                <i class="icon-[ph--calendar-check-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['regular'] }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Regular</div>
        </div>

        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #fbbf24; background: #fbbf2420;">
                <i class="icon-[ph--clock-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['probationary'] }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Probationary</div>
        </div>

        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #6b7280; background: #6b728020;">
                <i class="icon-[ph--archive-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['archived'] }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Archived</div>
        </div>

    @else
        {{-- Employee stats pulled from their own record --}}
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #667eea; background: #667eea20;">
                <i class="icon-[ph--buildings-fill]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $user->employee?->department ?? '—' }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Department</div>
        </div>

        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #764ba2; background: #764ba220;">
                <i class="icon-[ph--identification-badge-fill]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $user->employee?->position ?? '—' }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Position</div>
        </div>

        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #fbbf24; background: #fbbf2420;">
                <i class="icon-[ph--briefcase-fill]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $user->employee?->employment_status ?? '—' }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Employment Status</div>
        </div>

        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: #10b981; background: #10b98120;">
                <i class="icon-[ph--calendar-fill]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">
                {{ $user->employee?->date_hired ? \Carbon\Carbon::parse($user->employee->date_hired)->format('M d, Y') : '—' }}
            </div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Date Hired</div>
        </div>
    @endif

</div>

{{-- Quick Actions --}}
<div class="card bg-base-100 shadow-sm p-6 mb-5">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 mb-4 flex items-center gap-2">
        <i class="icon-[ph--lightning-fill]"></i>
        @if($isAdmin) Administrative Actions
        @elseif($isHR) HR Actions
        @else Quick Actions
        @endif
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

@if($isAdmin)
    <a href="{{ route('employees.create') }}" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--user-plus-fill]"></i>
        </div>
        <span>Create Users</span>
    </a>
    <a href="{{ route('roles.index') }}" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--lock-key-fill]"></i>
        </div>
        <span>Manage Roles</span>
    </a>
    <a href="#" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--database-fill]"></i>
        </div>
        <span>System Backup</span>
    </a>
    <a href="{{ route('audit-logs.index') }}" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--clock-counter-clockwise-fill]"></i>
        </div>
        <span>View Logs</span>
    </a>

@elseif($isHR)
    <a href="{{ route('employees.create') }}" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--user-plus-fill]"></i>
        </div>
        <span>Add Employee</span>
    </a>
    <a href="{{ route('payroll.index') }}" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--calculator-fill]"></i>
        </div>
        <span>Payroll</span>
    </a>
    <a href="#" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--tray-fill]"></i>
        </div>
        <span>Leave Requests</span>
    </a>
    <a href="#" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--file-pdf-fill]"></i>
        </div>
        <span>Reports</span>
    </a>

@else
    <a href="{{ route('profile.show') }}" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[tabler--user]"></i>
        </div>
        <span>My Profile</span>
    </a>
    <a href="{{ route('payroll.index') }}" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--receipt-fill]"></i>
        </div>
        <span>Payslips</span>
    </a>
    <a href="#" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--calendar-x-fill]"></i>
        </div>
        <span>Leave Request</span>
    </a>
    <a href="#" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="color: #23806a; background: #23806a20;">
            <i class="icon-[ph--clock-fill]"></i>
        </div>
        <span>Attendance</span>
    </a>
@endif
    </div>
</div>

{{-- System Information --}}
<div class="card bg-base-100 shadow-sm p-6">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 mb-4 flex items-center gap-2">
        <i class="icon-[ph--identification-badge-fill]"></i>
        System Information
    </h2>
    <div class="flex flex-col">
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-base-content/40 font-medium">Name</span>
            <span class="font-semibold text-base-content text-right">{{ $user->name }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-base-content/40 font-medium">Email</span>
            <span class="font-semibold text-base-content text-right">{{ $user->email }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-base-content/40 font-medium">Role</span>
            <span class="font-semibold text-base-content text-right">
                @if($isAdmin) Administrator
                @elseif($isHR) HR Personnel
                @else Employee
                @endif
            </span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-base-content/40 font-medium">Account Status</span>
            <span class="font-semibold text-base-content text-right">
                @if($user->is_active)
                    <span class="badge badge-soft badge-success">
                        <i class="icon-[tabler--circle-check]"></i> Active
                    </span>
                @else
                    <span class="badge badge-soft badge-error">
                        <i class="icon-[tabler--circle-x]"></i> Inactive
                    </span>
                @endif
            </span>
        </div>
        <div class="flex justify-between items-center py-3">
            <span class="text-base-content/40 font-medium">Last Login</span>
            <span class="font-semibold text-base-content text-right">
                @if($user->last_login_at)
                    {{ $user->last_login_at->format('M d, Y h:i A') }}
                @else
                    First Login
                @endif
            </span>
        </div>
    </div>
</div>

@endsection
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
    <span class="badge badge-soft badge-primary mb-4">
        <i class="icon-[tabler--shield-check]"></i> Administrator Access
    </span>
@elseif($isHR)
    <span class="badge badge-soft badge-primary mb-4">
        <i class="icon-[tabler--user]"></i> HR Department Access
    </span>
@endif

<div class="text-base-content text-lg mb-5">
    Welcome back, <strong>{{ $user->name }}</strong>
    @if($isAdmin) — You have full administrative access.
    @elseif($isHR) — You have HR access privileges.
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">

    @if($isAdmin)
        <a href="{{ route('users.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                <i class="icon-[tabler--users]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['total_users'] }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest">Total Users</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-error bg-error/10">
                <i class="icon-[tabler--shield-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['admin_users'] }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Admin Users</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-warning bg-warning/10">
                <i class="icon-[tabler--user]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['hr_users'] }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">HR Personnel</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-success bg-success/10">
                <i class="icon-[tabler--circle-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['active_users'] }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Active Accounts</div>
        </a>

    @elseif($isHR)
        <a href="{{ route('employees.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                <i class="icon-[tabler--users]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['total_employees'] }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Total Employees</div>
        </a>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-success bg-success/10">
                <i class="icon-[tabler--calendar-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['regular'] }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Regular</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-warning bg-warning/10">
                <i class="icon-[tabler--clock]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['probationary'] }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Probationary</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-base-content bg-base-200">
                <i class="icon-[tabler--archive]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['archived'] }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Archived</div>
        </div>

    @else
        {{-- Employee stats pulled from their own record --}}
        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                <i class="icon-[tabler--building]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $user->employee?->department ?? '—' }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Department</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-secondary bg-secondary/10">
                <i class="icon-[tabler--id-badge]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $user->employee?->position ?? '—' }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Position</div>
        </div>

        @php
            $empStatus = $user->employee?->employment_status;
            $empStatusColor = match($empStatus) {
                'Regular'      => 'text-success bg-success/10',
                'Probationary' => 'text-warning bg-warning/10',
                'Contractual'  => 'text-info bg-info/10',
                'Part-time'    => 'text-base-content bg-base-200',
                default        => 'text-base-content bg-base-200',
            };
        @endphp
        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 {{ $empStatusColor }}">
                <i class="icon-[tabler--briefcase]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $empStatus ?? '—' }}</div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Employment Status</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-accent bg-accent/10">
                <i class="icon-[tabler--calendar]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">
                {{ $user->employee?->date_hired ? \Carbon\Carbon::parse($user->employee->date_hired)->format('M d, Y') : '—' }}
            </div>
            <div class="text-xs text-secondary-content uppercase tracking-widest ">Date Hired</div>
        </div>
    @endif

</div>

{{-- Quick Actions --}}
<div class="card border border-base-300 shadow-sm p-6 mb-5">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
        <i class="icon-[ph--lightning-fill]"></i>
        @if($isAdmin) Administrative Actions
        @elseif($isHR) HR Actions
        @else Quick Actions
        @endif
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

@if($isAdmin)
    <a href="{{ route('employees.create') }}" class="btn btn-outline  flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--user-plus]"></i>
        </div>
        <span class="text-secondary-content">Create Users</span>
    </a>
    <a href="{{ route('roles.index') }}" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--lock]"></i>
        </div>
        <span class="text-secondary-content">Manage Roles</span>
    </a>
    <a href="#" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--database]"></i>
        </div>
        <span class="text-secondary-content">System Backup</span>
    </a>
    <a href="{{ route('audit-logs.index') }}" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--history]"></i>
        </div>
        <span class="text-secondary-content">View Logs</span>
    </a>

@elseif($isHR)
    <a href="{{ route('employees.create') }}" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--user-plus]"></i>
        </div>
        <span class="text-secondary-content">Add Employee</span>
    </a>
    <a href="{{ route('payroll.index') }}" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--calculator]"></i>
        </div>
        <span class="text-secondary-content">Payroll</span>
    </a>
    <a href="#" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--inbox]"></i>
        </div>
        <span class="text-secondary-content">Leave Requests</span>
    </a>
    <a href="#" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--file-type-pdf]"></i>
        </div>
        <span class="text-secondary-content">Reports</span>
    </a>

@else
    <a href="{{ route('profile.show') }}" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--user]"></i>
        </div>
        <span class="text-secondary-content">My Profile</span>
    </a>
    <a href="{{ route('payroll.index') }}" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--receipt]"></i>
        </div>
        <span class="text-secondary-content">Payslips</span>
    </a>
    <a href="#" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--calendar-off]"></i>
        </div>
        <span class="text-secondary-content">Leave Request</span>
    </a>
    <a href="#" class="btn btn-outline flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--clock]"></i>
        </div>
        <span class="text-secondary-content">Attendance</span>
    </a>
@endif
    </div>
</div>

{{-- System Information --}}
<div class="card bg-base-100 border border-base-300 p-6">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
        <i class="icon-[tabler--id-badge]"></i>
        System Information
    </h2>
    <div class="flex flex-col">
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-secondary-content ">Name</span>
            <span class="font-semibold text-base-content text-right">{{ $user->name }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-secondary-content ">Email</span>
            <span class="font-semibold text-base-content text-right">{{ $user->email }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-secondary-content ">Role</span>
            <span class="font-semibold text-base-content text-right">
                @if($isAdmin) Administrator
                @elseif($isHR) HR Personnel
                @else Employee
                @endif
            </span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-secondary-content ">Account Status</span>
            <span class="font-semibold text-base-content text-right">
                @if($user->is_active)
                    <span class="badge badge-soft badge-primary">
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
            <span class="text-secondary-content ">Last Login</span>
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
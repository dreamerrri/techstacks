<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - HR Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

@php
    $user    = auth()->user();
    $isAdmin = $user->role === 'admin';
    $isHR    = $user->role === 'hr';
@endphp

<div style="display: grid; grid-template-columns: 250px 1fr; min-height: 100vh;">

    {{-- Sidebar --}}
    <div class="sidebar sidebar-{{ $isAdmin ? 'admin' : ($isHR ? 'hr' : 'user') }}"
         style="position: relative; display: flex; flex-direction: column;">

        <div class="sidebar-header">
            <h1>HR System</h1>
            <p>
                @if($isAdmin) Admin Portal
                @elseif($isHR) HR Portal
                @else Employee Portal
                @endif
            </p>
        </div>

        <nav style="flex: 1;">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>

            @if($isAdmin)
                <a href="#" class="nav-item">
                    <i class="fas fa-users"></i><span>All Users</span>
                </a>
                <a href="{{ route('employees.index') }}"
                   class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i><span>Manage Staff</span>
                </a>
                <a href="#" class="nav-item"><i class="fas fa-lock"></i><span>Access Control</span></a>
                <a href="#" class="nav-item"><i class="fas fa-shield-alt"></i><span>System Security</span></a>
                <a href="#" class="nav-item"><i class="fas fa-cogs"></i><span>Settings</span></a>
                <a href="#" class="nav-item"><i class="fas fa-file-alt"></i><span>Audit Logs</span></a>

            @elseif($isHR)
                <a href="{{ route('employees.index') }}"
                   class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i><span>Employees</span>
                </a>
                <a href="#" class="nav-item"><i class="fas fa-money-bill"></i><span>Payroll</span></a>
                <a href="#" class="nav-item"><i class="fas fa-calendar-check"></i><span>Attendance</span></a>
                <a href="#" class="nav-item"><i class="fas fa-suitcase"></i><span>Leave Requests</span></a>
                <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>

            @else
                <a href="#" class="nav-item"><i class="fas fa-user"></i><span>My Profile</span></a>
                <a href="#" class="nav-item"><i class="fas fa-file-invoice-dollar"></i><span>Payslips</span></a>
                <a href="#" class="nav-item"><i class="fas fa-calendar-times"></i><span>Leave Request</span></a>
                <a href="#" class="nav-item"><i class="fas fa-clock"></i><span>Attendance</span></a>
            @endif
        </nav>

        <div style="padding-bottom: 20px;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Main Content --}}
    <div style="display: flex; flex-direction: column;">

        {{-- Topbar --}}
        <div class="topbar">
            <h2 style="margin: 0; color: #1f2937;">@yield('title')</h2>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="user-avatar avatar-{{ $isAdmin ? 'admin' : ($isHR ? 'hr' : 'user') }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:14px; font-weight:600; color:#1f2937;">{{ $user->name }}</div>
                    <div style="font-size:12px; color:#6b7280;">
                        @if($isAdmin) Administrator
                        @elseif($isHR) HR Personnel
                        @else Employee
                        @endif
                    </div>
                </div>
                <span class="role-badge badge-{{ $isAdmin ? 'admin' : ($isHR ? 'hr' : 'user') }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
        </div>

        {{-- Flash Messages --}}
        <div style="padding: 0 40px; padding-top: 20px;">
            @if(session('success'))
                <div style="background:#d1fae5; color:#065f46; padding:12px 16px; border-radius:8px; margin-bottom:0;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:0;">
                    <i class="fas fa-times-circle"></i> {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        <div class="content">
            @yield('content')
        </div>

    </div>
</div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - HR Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

@php
    $user    = auth()->user();
    $isAdmin = $user->role === 'admin';
    $isHR    = $user->role === 'hr';
    $role    = $isAdmin ? 'admin' : ($isHR ? 'hr' : 'user');
@endphp

<div class="app-layout">

    {{-- Mobile topbar (visible on mobile only) --}}
    <div class="mobile-topbar sidebar-{{ $role }}">
        <div style="display:flex; align-items:center; gap:10px;">
            <button class="burger-btn" id="burgerBtn" aria-label="Toggle navigation" aria-expanded="false" aria-controls="burgerDropdown">
                <i class="fas fa-bars" id="burgerIcon"></i>
            </button>
            <span style="font-size:18px; font-weight:700; color:white;">HR System</span>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="user-avatar avatar-{{ $role }}" style="width:34px;height:34px;font-size:13px;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <span class="role-badge badge-{{ $role }}">{{ ucfirst($user->role) }}</span>
        </div>
    </div>

    {{-- Burger dropdown (visible on mobile only) --}}
    <div class="burger-dropdown sidebar-{{ $role }}" id="burgerDropdown">
        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>

        @if($isAdmin)
            <a href="{{ route('users.index') }}"
               class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i><span>All Users</span>
            </a>
            <a href="{{ route('employees.index') }}"
               class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i class="fas fa-user-tie"></i><span>Employees</span>
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

        <div style="padding: 10px 20px 15px;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Desktop Sidebar (visible on desktop only) --}}
    <div class="sidebar sidebar-{{ $role }} desktop-sidebar">
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
                <a href="{{ route('users.index') }}"
                   class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i><span>All Users</span>
                </a>
                <a href="{{ route('employees.index') }}"
                   class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i><span>Employees</span>
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

    {{-- Main Content Area --}}
    <div class="main-content">
        {{-- Desktop Topbar (visible on desktop only) --}}
        <div class="topbar desktop-topbar">
            <h2 style="margin: 0; color: #1f2937;">@yield('title')</h2>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="user-avatar avatar-{{ $role }}">
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
                <span class="role-badge badge-{{ $role }}">{{ ucfirst($user->role) }}</span>
            </div>
        </div>

        {{-- Page Content (rendered once) --}}
        <div class="content">
            @yield('content')
        </div>
    </div>

</div>

<script>
    (function() {
        function handleResponsiveLayout() {
            const isMobile = window.innerWidth <= 768;
            
            // Toggle navigation elements
            const desktopSidebar = document.querySelector('.desktop-sidebar');
            const mobileTopbar = document.querySelector('.mobile-topbar');
            const burgerDropdown = document.querySelector('.burger-dropdown');
            const desktopTopbar = document.querySelector('.desktop-topbar');
            
            if (desktopSidebar) desktopSidebar.style.display = isMobile ? 'none' : 'flex';
            if (mobileTopbar) mobileTopbar.style.display = isMobile ? 'flex' : 'none';
            if (burgerDropdown) burgerDropdown.style.display = isMobile ? 'block' : 'none';
            if (desktopTopbar) desktopTopbar.style.display = isMobile ? 'none' : 'flex';
            
            // Toggle table/cards in employee views
            const tableWrapper = document.querySelector('.user-table-wrapper');
            const mobileCards = document.querySelector('.user-mobile-cards');
            
            if (tableWrapper) tableWrapper.style.display = isMobile ? 'none' : 'block';
            if (mobileCards) mobileCards.style.display = isMobile ? 'block' : 'none';
        }
        
        // Run on load and resize
        handleResponsiveLayout();
        window.addEventListener('resize', handleResponsiveLayout);
    })();
</script>

{{-- ── Flash toasts ── --}}
@if(session('success') || session('error') || session('warning') || session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            window.Toast?.fire({ icon: 'success', title: @json(session('success')) });
        @endif
        @if(session('error'))
            window.Toast?.fire({ icon: 'error',   title: @json(session('error'))   });
        @endif
        @if(session('warning'))
            window.Toast?.fire({ icon: 'warning', title: @json(session('warning')) });
        @endif
        @if(session('info'))
            window.Toast?.fire({ icon: 'info',    title: @json(session('info'))    });
        @endif
    });
</script>
@endif

</body>
</html>
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

{{-- ═══════════════════════════════════════
     MOBILE LAYOUT  (hidden on desktop)
     ═══════════════════════════════════════ --}}
<div class="mobile-layout">

    {{-- Mobile topbar --}}
    <div class="mobile-topbar sidebar-{{ $role }}">
        <div style="display:flex; align-items:center; gap:10px;">
            <button class="burger-btn" id="burgerBtn" aria-label="Toggle navigation" aria-expanded="false" aria-controls="burgerDropdown">
                <i class="fas fa-bars" id="burgerIcon"></i>
            </button>
            <span style="font-size:18px; font-weight:700; color:white;">HR System</span>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="user-avatar avatar-{{ $role }}" style="overflow:hidden; padding:0;">
    @if($user->profile_photo)
        <img src="{{ asset('storage/' . $user->profile_photo) }}"
             alt="{{ $user->name }}"
             style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
    @else
        {{ strtoupper(substr($user->name, 0, 1)) }}
    @endif
</div>
            <span class="role-badge badge-{{ $role }}">{{ ucfirst($user->role) }}</span>
        </div>
    </div>

    {{-- Burger dropdown --}}
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
            <a href="{{ route('government-contributions.index') }}"
               class="nav-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i><span>Gov. Contributions</span>
            </a>
            <a href="{{ route('payroll.index') }}"
               class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill"></i><span>Payroll</span>
            </a>
            <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}"><i class="fas fa-calendar-check"></i><span>Attendance</span></a>
            <a href="#" class="nav-item"><i class="fas fa-lock"></i><span>Access Control</span></a>
            <a href="#" class="nav-item"><i class="fas fa-shield-alt"></i><span>System Security</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cogs"></i><span>Settings</span></a>
            <a href="#" class="nav-item"><i class="fas fa-file-alt"></i><span>Audit Logs</span></a>
        @elseif($isHR)
            <a href="{{ route('employees.index') }}"
               class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i><span>Employees</span>
            </a>
            <a href="{{ route('government-contributions.index') }}"
               class="nav-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i><span>Gov. Contributions</span>
            </a>
            <a href="{{ route('payroll.index') }}"
               class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill"></i><span>Payroll</span>
            </a>
            <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}"><i class="fas fa-calendar-check"></i><span>Attendance</span></a>
            <a href="#" class="nav-item"><i class="fas fa-suitcase"></i><span>Leave Requests</span></a>
            <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
            <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
        @else
<a href="{{ route('profile.show') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="fas fa-user"></i><span>My Profile</span></a>            <a href="{{ route('payroll.index') }}"
               class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i><span>My Payslip</span>
            </a>
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

    {{-- Mobile page content --}}
    <div class="mobile-content bg-{{ $role }}">
        <div class="content">
            @yield('content')
        </div>
        @yield('scripts')
    </div>

</div>

{{-- ═══════════════════════════════════════
     DESKTOP LAYOUT  (hidden on mobile)
     ═══════════════════════════════════════ --}}
<div class="desktop-layout" style="display: grid; grid-template-columns: 250px 1fr; height: 100vh; overflow: hidden;">

    {{-- Sidebar --}}
    <div class="sidebar sidebar-{{ $role }}"
         style="position: sticky; top: 0; height: 100vh; display: flex; flex-direction: column; overflow-y: auto;">

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
                <a href="{{ route('government-contributions.index') }}"
                   class="nav-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                    <i class="fas fa-id-card"></i><span>Gov. Contributions</span>
                </a>
                <a href="{{ route('payroll.index') }}"
                   class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill"></i><span>Payroll</span>
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
                <a href="{{ route('government-contributions.index') }}"
                   class="nav-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                    <i class="fas fa-id-card"></i><span>Gov. Contributions</span>
                </a>
                <a href="{{ route('payroll.index') }}"
                   class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill"></i><span>Payroll</span>
                </a>
                <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}"><i class="fas fa-calendar-check"></i><span>Attendance</span></a>
                <a href="#" class="nav-item"><i class="fas fa-suitcase"></i><span>Leave Requests</span></a>
                <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="#" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>

            @else
            <a href="{{ route('profile.show') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="fas fa-user"></i><span>My Profile</span></a>
                <a href="{{ route('payroll.index') }}"
                   class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i><span>My Payslip</span>
                </a>
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
<div class="bg-{{ $role }}" style="display: flex; flex-direction: column; height: 100vh; overflow: hidden;">
        {{-- Topbar --}}
<div class="topbar" style="position:sticky; top:0; z-index:999;">    
            <h2 style="margin: 0; color: #1f2937;">@yield('title')</h2>
            <div style="display: flex; align-items: center; gap: 15px;">
                {{-- Notification Bell --}}
@php
    $unassigned = \App\Models\Employee::active()
        ->where(function($q) {
            $q->where('department', 'Unassigned')
              ->orWhere('position', 'Unassigned');
        })->get();
    $notifCount = $unassigned->count();
@endphp

<div style="position:relative;">
    <button id="notifBtn" onclick="document.getElementById('notifDropdown').classList.toggle('notif-open')"
            style="background:none; border:none; cursor:pointer; color:white; font-size:18px; position:relative; padding:4px;">
        <i class="fas fa-bell"></i>
        @if($notifCount > 0)
            <span style="position:absolute; top:-4px; right:-4px; background:#ef4444; color:white; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; line-height:1;">
                {{ $notifCount > 9 ? '9+' : $notifCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div id="notifDropdown"
         style="display:none; position:absolute; right:0; top:calc(100% + 10px); width:300px; background:white; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid #e5e7eb; z-index:999;">
        <div style="padding:12px 16px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:700; color:#1f2937; font-size:14px;"><i class="fas fa-bell" style="color:#dc2626;"></i> Notifications</span>
            @if($notifCount > 0)
                <span style="background:#fee2e2; color:#991b1b; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px;">{{ $notifCount }} pending</span>
            @endif
        </div>

        <div style="max-height:300px; overflow-y:auto;">
            @if($notifCount > 0)
                @foreach($unassigned as $emp)
                    <a href="{{ route('employees.edit', $emp) }}"
                       style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #f3f4f6; text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                        <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:12px; font-weight:700; flex-shrink:0;">
                            {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:13px; font-weight:600; color:#1f2937;">{{ $emp->full_name }}</div>
                            <div style="font-size:11px; color:#dc2626;"><i class="fas fa-exclamation-circle"></i> Needs department/position assignment</div>
                        </div>
                    </a>
                @endforeach
            @else
                <div style="padding:24px; text-align:center; color:#9ca3af; font-size:13px;">
                    <i class="fas fa-check-circle" style="font-size:24px; color:#10b981; display:block; margin-bottom:8px;"></i>
                    No pending actions
                </div>
            @endif
        </div>
    </div>
</div>
           <div class="user-avatar avatar-{{ $role }}" style="width:34px;height:34px;font-size:13px; overflow:hidden; padding:0;">
    @if($user->profile_photo)
        <img src="{{ asset('storage/' . $user->profile_photo) }}"
             alt="{{ $user->name }}"
             style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
    @else
        {{ strtoupper(substr($user->name, 0, 1)) }}
    @endif
</div>
                <div>
                   <div class="user-name" style="font-size:14px; font-weight:600;">{{ $user->name }}</div>
<div class="user-role" style="font-size:12px;">
                        @if($isAdmin) Administrator
                        @elseif($isHR) HR Personnel
                        @else Employee
                        @endif
                    </div>
                </div>
                <span class="role-badge badge-{{ $role }}">{{ ucfirst($user->role) }}</span>
            </div>
        </div>

        {{-- Page Content --}}
<div class="content" style="flex:1; overflow-y:auto;">
                @yield('content')
        </div>
        @yield('scripts')

    </div>
</div>

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
<script>
document.addEventListener('click', function(e) {
    const btn = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');
    if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('notif-open');
    }
});
</script>
@endif

</body>
</html>
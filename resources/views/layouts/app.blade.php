<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - HR Management System</title>
    {{-- ⚡ Must be first: restore sidebar + dropdown states before first paint — prevents flash --}}
    <script>
        if (sessionStorage.getItem('sidebar_collapsed') === '1') {
            document.documentElement.classList.add('sidebar-pre-collapsed');
        }
        // Dropdowns: add .open + disable transitions before paint, then re-enable after
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.nav-dropdown').forEach(function(dropdown) {
                var label = dropdown.querySelector('.nav-dropdown-trigger span');
                if (!label) return;
                var key = 'dropdown_' + label.textContent.trim();
                if (sessionStorage.getItem(key) === 'open') {
                    dropdown.classList.add('open', 'no-transition');
                }
            });
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    document.querySelectorAll('.nav-dropdown.no-transition')
                        .forEach(function(el) { el.classList.remove('no-transition'); });
                });
            });
        });
    </script>
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

    // ── Notification queries (defined here so both mobile & desktop can use them) ──

    // 1. Unassigned department/position
    $unassigned = \App\Models\Employee::active()
        ->where(function($q) {
            $q->where('department', 'Unassigned')
              ->orWhere('position', 'Unassigned');
        })->get();

    // 2. Missing government IDs
    $missingGovIds = \App\Models\Employee::active()
        ->where(function($q) {
            $q->whereNull('sss_number')
              ->orWhereNull('philhealth_number')
              ->orWhereNull('pagibig_number')
              ->orWhereNull('tin_number');
        })->get();

    // 3. Draft payrolls whose payroll_date has passed
    $overduePayrolls = \App\Models\PayrollPeriod::where('status', 'draft')
        ->where('payroll_date', '<', now()->toDateString())
        ->get();

    // 4. Allowances & benefits expiring within 7 days
    $expiringAllowances = \App\Models\Allowance::where('is_active', 1)
        ->whereNotNull('end_date')
        ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
        ->with('employee')
        ->get();

    $expiringBenefits = \App\Models\Benefit::where('is_active', 1)
        ->whereNotNull('end_date')
        ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
        ->with('employee')
        ->get();

    $notifCount = $unassigned->count()
        + $missingGovIds->count()
        + $overduePayrolls->count()
        + $expiringAllowances->count()
        + $expiringBenefits->count();
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

            {{-- Notification Bell (mobile) --}}
            <div style="position:relative;">
                <button id="notifBtnMobile"
                        onclick="document.getElementById('notifDropdownMobile').classList.toggle('notif-open')"
                        style="background:none; border:none; cursor:pointer; color:white; font-size:18px; position:relative; padding:4px;">
                    <i class="fas fa-bell"></i>
                    @if($notifCount > 0)
                        <span style="position:absolute; top:-4px; right:-4px; background:#ef4444; color:white; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; line-height:1;">
                            {{ $notifCount > 9 ? '9+' : $notifCount }}
                        </span>
                    @endif
                </button>

                <div id="notifDropdownMobile"
                     style="display:none; position:fixed; right:8px; top:60px; width:calc(100vw - 16px); max-width:340px; background:white; border-radius:14px; box-shadow:0 12px 32px rgba(0,0,0,0.14); border:1px solid #e5e7eb; z-index:999; overflow:hidden;">
                    <div style="padding:13px 16px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:14px; font-weight:700; color:#111827; display:flex; align-items:center; gap:7px;">
                            <i class="fas fa-bell" style="font-size:13px; color:#6b7280;"></i> Notifications
                        </span>
                        @if($notifCount > 0)
                            <span style="background:#f3f4f6; color:#374151; font-size:11px; font-weight:600; padding:3px 9px; border-radius:20px;">{{ $notifCount }} pending</span>
                        @endif
                    </div>
                    @include('partials.notifications-list')
                </div>
            </div>

            <a href="{{ route('profile.show') }}" style="display:flex; align-items:center; text-decoration:none;">
                <div class="user-avatar avatar-{{ $role }}" style="overflow:hidden; padding:0;">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}"
                             alt="{{ $user->name }}"
                             style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
            </a>
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
            <a href="{{ route('roles.index') }}" class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}"><i class="fas fa-lock"></i><span>Roles</span></a>
            <a href="{{ route('permissions.index') }}" class="nav-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}"><i class="fas fa-shield-alt"></i><span>Permissions</span></a>
            <a href="{{ route('audit-logs.index') }}" class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i><span>Audit Logs</span></a>
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
<div class="desktop-layout" style="grid-template-rows: auto 1fr; height: 100vh; overflow: hidden;">

    {{-- Global Topbar (spans full width above sidebar + content) --}}
    <div class="topbar desktop-topbar topbar-{{ $role }}" style="grid-column: 1 / -1; grid-row: 1;">
        <div style="display:flex; flex-direction:column; justify-content:center;">
            <h1 style="margin:0; font-size:20px; font-weight:700; letter-spacing:0.5px; color:white;">HR System</h1>
            <p style="margin:0; font-size:11px; color:white; opacity:0.55; letter-spacing:1.5px; text-transform:uppercase;">
                @if($isAdmin) Admin Portal
                @elseif($isHR) HR Portal
                @else Employee Portal
                @endif
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">

            {{-- Notification Bell (desktop) --}}
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

                {{-- Desktop Dropdown --}}
                <div id="notifDropdown"
                     style="display:none; position:absolute; right:0; top:calc(100% + 10px); width:320px; background:white; border-radius:14px; box-shadow:0 12px 32px rgba(0,0,0,0.14); border:1px solid #e5e7eb; z-index:999; overflow:hidden;">
                    <div style="padding:13px 16px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:14px; font-weight:700; color:#111827; display:flex; align-items:center; gap:7px;">
                            <i class="fas fa-bell" style="font-size:13px; color:#6b7280;"></i> Notifications
                        </span>
                        @if($notifCount > 0)
                            <span style="background:#f3f4f6; color:#374151; font-size:11px; font-weight:600; padding:3px 9px; border-radius:20px;">{{ $notifCount }} pending</span>
                        @endif
                    </div>
                    @include('partials.notifications-list')
                </div>
            </div>

            {{-- Clickable Profile --}}
            <a href="{{ route('profile.show') }}" style="display:flex; align-items:center; gap:10px; text-decoration:none;">
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
            </a>

            <span class="role-badge badge-{{ $role }}">{{ ucfirst($user->role) }}</span>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="sidebar sidebar-{{ $role }}" style="grid-column: 1; grid-row: 2;">

        <nav style="flex: 1; position: relative; z-index: 1;">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>

            @if($isAdmin)
                @php
                    $userMgmtOpen   = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                    $empMgmtOpen    = request()->routeIs('employees.*') || request()->routeIs('manual-payroll-attendance.*');
                    $payrollMgmtOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                    $monitoringOpen = request()->routeIs('audit-logs.*') || request()->routeIs('reports.*');
                @endphp

                {{-- ▼ User Management --}}
                <div class="nav-dropdown {{ $userMgmtOpen ? 'open' : '' }}">
                    <button class="nav-item nav-dropdown-trigger" type="button">
                        <i class="fas fa-users-cog"></i><span>User Management</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('users.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i><span>Users</span>
                        </a>
                        <a href="{{ route('roles.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <i class="fas fa-lock"></i><span>Roles</span>
                        </a>
                        <a href="{{ route('permissions.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                            <i class="fas fa-shield-alt"></i><span>Permissions</span>
                        </a>
                    </div>
                </div>

                {{-- ▼ Employee Management --}}
                <div class="nav-dropdown {{ $empMgmtOpen ? 'open' : '' }}">
                    <button class="nav-item nav-dropdown-trigger" type="button">
                        <i class="fas fa-user-tie"></i><span>Employee Management</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('employees.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="fas fa-id-badge"></i><span>Employees</span>
                        </a>
                        <a href="{{ route('manual-payroll-attendance.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check"></i><span>Attendance</span>
                        </a>
                        <a href="#" class="nav-item nav-sub-item">
                            <i class="fas fa-suitcase"></i><span>Leave Requests</span>
                        </a>
                    </div>
                </div>

                {{-- ▼ Payroll Management --}}
                <div class="nav-dropdown {{ $payrollMgmtOpen ? 'open' : '' }}">
                    <button class="nav-item nav-dropdown-trigger" type="button">
                        <i class="fas fa-money-bill-wave"></i><span>Payroll Management</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('payroll.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                            <i class="fas fa-money-bill"></i><span>Payroll</span>
                        </a>
                        <a href="{{ route('government-contributions.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                            <i class="fas fa-id-card"></i><span>Gov. Contributions</span>
                        </a>
                    </div>
                </div>

                {{-- ▼ Monitoring --}}
                <div class="nav-dropdown {{ $monitoringOpen ? 'open' : '' }}">
                    <button class="nav-item nav-dropdown-trigger" type="button">
                        <i class="fas fa-chart-line"></i><span>Monitoring</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('audit-logs.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i><span>Audit Logs</span>
                        </a>
                        <a href="#" class="nav-item nav-sub-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i><span>Reports</span>
                        </a>
                    </div>
                </div>

                {{-- ⚙ Settings --}}
                <a href="#" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i><span>Settings</span>
                </a>

            @elseif($isHR)
                @php
                    $hrEmpMgmtOpen  = request()->routeIs('employees.*') || request()->routeIs('manual-payroll-attendance.*');
                    $hrPayrollOpen  = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                @endphp

                {{-- ▼ Employee Management --}}
                <div class="nav-dropdown {{ $hrEmpMgmtOpen ? 'open' : '' }}">
                    <button class="nav-item nav-dropdown-trigger" type="button">
                        <i class="fas fa-user-tie"></i><span>Employee Management</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('employees.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="fas fa-id-badge"></i><span>Employees</span>
                        </a>
                        <a href="{{ route('manual-payroll-attendance.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check"></i><span>Attendance</span>
                        </a>
                        <a href="#" class="nav-item nav-sub-item">
                            <i class="fas fa-suitcase"></i><span>Leave Requests</span>
                        </a>
                    </div>
                </div>

                {{-- ▼ Payroll --}}
                <div class="nav-dropdown {{ $hrPayrollOpen ? 'open' : '' }}">
                    <button class="nav-item nav-dropdown-trigger" type="button">
                        <i class="fas fa-money-bill-wave"></i><span>Payroll</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('payroll.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                            <i class="fas fa-money-bill"></i><span>Payroll</span>
                        </a>
                        <a href="{{ route('government-contributions.index') }}"
                           class="nav-item nav-sub-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                            <i class="fas fa-id-card"></i><span>Gov. Contributions</span>
                        </a>
                    </div>
                </div>

                {{-- 📊 Reports --}}
                <a href="#" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i><span>Reports</span>
                </a>

                {{-- ⚙ Settings --}}
                <a href="#" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i><span>Settings</span>
                </a>

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

        <div style="padding-bottom: 20px; margin-top: auto; padding-top: 100px;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Sidebar toggle button --}}
    <button id="sidebar-toggle">
        <i class="fas fa-chevron-left" id="sidebar-arrow"></i>
    </button>

    {{-- Main Content --}}
    <div class="bg-{{ $role }}" style="grid-column: 2; grid-row: 2; overflow-y: auto;">
        <div class="content">
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
@endif

{{-- Close desktop notif dropdown on outside click --}}
<script>
document.addEventListener('click', function(e) {
    const btn      = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');
    if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('notif-open');
    }
});
</script>

{{-- Close mobile notif dropdown on outside click --}}
<script>
document.addEventListener('click', function(e) {
    const btnM  = document.getElementById('notifBtnMobile');
    const dropM = document.getElementById('notifDropdownMobile');
    if (btnM && dropM && !btnM.contains(e.target) && !dropM.contains(e.target)) {
        dropM.classList.remove('notif-open');
    }
});
</script>

</body>
</html>
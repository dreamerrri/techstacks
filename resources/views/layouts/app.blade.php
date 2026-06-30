    <!DOCTYPE html>
    <html lang="en" data-theme="light">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <title>@yield('title') - HR Management System</title>

        {{-- ⚡ Must be first: restore sidebar + dropdown states before first paint — prevents flash --}}
        <script>
            if (sessionStorage.getItem('sidebar_collapsed') === '1') {
                document.documentElement.classList.add('sidebar-pre-collapsed');
            }
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

        {{-- CSS --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @yield('styles')
    </head>
    <body>

    @php
        $user    = auth()->user();
        $isAdmin = $user->role === 'admin';
        $isHR    = $user->role === 'hr';
        $role    = $isAdmin ? 'admin' : ($isHR ? 'hr' : 'user');

        function profilePhotoUrl($path) {
            if (app()->environment('production')) {
                return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($path, now()->addHours(24));
            }
            return asset('images/placeholder-avatar.png');
        }

        // Get notifications for current user based on role
        $notifications = \App\Models\Notification::forCurrentUser()
            ->where(function($q) {
                $q->where('is_read', false)
                  ->orWhere('is_resolved', false);
            })
            ->latest()
            ->limit(50)
            ->get();
        $notifCount = $notifications->count();
    @endphp

        {{-- ═══════════════════════════════════════
            MOBILE LAYOUT
            ═══════════════════════════════════════ --}}
        <div class="mobile-layout">

        <div class="mobile-topbar sidebar-{{ $role }}">
            <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:8px; text-decoration:none; color:white;">
                <svg fill="currentColor" height="1.4em" viewBox="0 0 1813 1441" width="1.4em" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0; opacity:0.95;">
                    <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fill-rule="evenodd"></path>
                    <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fill-rule="evenodd"></path>
                </svg>
                <div style="display:flex; flex-direction:column; justify-content:center;">
                    <span style="font-size:16px; font-weight:700; line-height:1.2;">Techstacks</span>
                    <span style="font-size:10px; opacity:0.55; letter-spacing:1.5px; text-transform:uppercase; line-height:1.2;">
                        @if($isAdmin) Admin Portal
                        @elseif($isHR) HR Portal
                        @else Employee Portal
                        @endif
                    </span>
                </div>
            </a>

            <div style="display:flex; align-items:center; gap:10px;">
                <div style="position:relative;">
                    <button id="notifBtnMobile"
                            onclick="document.getElementById('notifDropdownMobile').classList.toggle('notif-open')"
                            style="background:none; border:none; cursor:pointer; color:white; font-size:18px; position:relative; padding:4px;">
                        <i class="icon-[ph--bell-fill]"></i>
                        @if($notifCount > 0)
                            <span id="notifCountMobile" style="position:absolute; top:-4px; right:-4px; background:#ef4444; color:white; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; line-height:1;">
                                {{ $notifCount > 9 ? '9+' : $notifCount }}
                            </span>
                        @endif
                    </button>

                    <div id="notifDropdownMobile"
                        style="display:none; position:fixed; right:8px; top:60px; width:calc(100vw - 16px); max-width:340px; background:white; border-radius:14px; box-shadow:0 12px 32px rgba(0,0,0,0.14); border:1px solid #e5e7eb; z-index:999; overflow:hidden;">
                        <div style="padding:13px 16px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:14px; font-weight:700; color:#111827; display:flex; align-items:center; gap:7px;">
                                <i class="icon-[ph--bell-fill]" style="font-size:13px; color:#6b7280;"></i> Notifications
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
                            <img src="{{ profilePhotoUrl($user->profile_photo) }}"
                                alt="{{ $user->name }}"
                                style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                </a>


                <button id="burgerBtn"
                        style="background:none; border:none; cursor:pointer; color:white; font-size:20px; padding:4px; display:flex; align-items:center;">
                    <i class="icon-[ph--list-fill]" id="burgerIcon"></i>
                </button>
            </div>
        </div>

        <div class="burger-dropdown sidebar-{{ $role }}" id="burgerDropdown">
            <a href="{{ route('dashboard') }}"
            class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="icon-[ph--house-fill]"></i><span>Dashboard</span>
            </a>

            @if($isAdmin)
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="icon-[ph--users-fill]"></i><span>All Users</span>
                </a>
                <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="icon-[ph--users-three-fill]"></i><span>Employees</span>
                </a>
                <a href="{{ route('government-contributions.index') }}" class="nav-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                    <i class="icon-[ph--identification-card-fill]"></i><span>Gov. Contributions</span>
                </a>
                <a href="{{ route('payroll.index') }}" class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                    <i class="icon-[ph--money-fill]"></i><span>Payroll</span>
                </a>
                <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}">
                    <i class="icon-[ph--calendar-check-fill]"></i><span>Attendance</span>
                </a>
                <a href="{{ route('roles.index') }}" class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <i class="icon-[ph--lock-fill]"></i><span>Roles</span>
                </a>
                <a href="{{ route('permissions.index') }}" class="nav-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                    <i class="icon-[ph--shield-check-fill]"></i><span>Permissions</span>
                </a>
                <a href="{{ route('audit-logs.index') }}" class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                    <i class="icon-[ph--file-text-fill]"></i><span>Audit Logs</span>
                </a>
            @elseif($isHR)
                <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="icon-[ph--identification-badge-fill]"></i><span>Employees</span>
                </a>
                <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}">
                    <i class="icon-[ph--calendar-check-fill]"></i><span>Attendance</span>
                </a>
                <a href="{{ route('work-requests.index') }}" class="nav-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}"><i class="icon-[ph--calendar-check-fill]"></i><span>Work Requests</span></a>
                <a href="{{ route('payroll.index') }}"
                class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                    <i class="icon-[ph--money-fill]"></i><span>Payroll</span>
                </a>
                <a href="{{ route('government-contributions.index') }}" class="nav-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                    <i class="icon-[ph--identification-card-fill]"></i><span>Gov. Contributions</span>
                </a>
                <a href="#" class="nav-item"><i class="icon-[ph--chart-bar-fill]"></i><span>Reports</span></a>
                <a href="#" class="nav-item"><i class="icon-[ph--gear-fill]"></i><span>Settings</span></a>
            @else
                <a href="{{ route('profile.show') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="icon-[ph--user-fill]"></i><span>My Profile</span>
                </a>
                <a href="{{ route('payroll.index') }}" class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                    <i class="icon-[ph--receipt-fill]"></i><span>My Payslip</span>
                </a>
                <a href="#" class="nav-item"><i class="icon-[ph--calendar-x-fill]"></i><span>Leave Request</span></a>
                <a href="{{ route('employee-attendance.index') }}" class="nav-item {{ request()->routeIs('employee-attendance.*') ? 'active' : '' }}"><i class="icon-[ph--clock-fill]"></i><span>Attendance</span></a>
            @endif

            <div style="padding: 10px 20px 15px;">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="icon-[ph--sign-out-fill]"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="mobile-content bg-{{ $role }}">
            <div class="content">
                @yield('content')
            </div>
        </div>

        </div>{{-- end .mobile-layout --}}

    {{-- ═══════════════════════════════════════
        DESKTOP LAYOUT
        ═══════════════════════════════════════ --}}
    <div class="desktop-layout" style="grid-template-rows: auto 1fr; height: 100vh; overflow: hidden;">

        <div class="topbar desktop-topbar topbar-{{ $role }}" style="grid-column: 1 / -1; grid-row: 1;">

            <a class="techicon" href="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:10px; text-decoration:none; color:white;">
                <svg  fill="currentColor" height="2em" viewBox="0 0 1813 1441" width="2em" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0; opacity:0.95;">
                    <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fill-rule="evenodd"></path>
                    <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fill-rule="evenodd"></path>
                </svg>
                <div class="tech" style="display:flex; flex-direction:column; justify-content:center;">
                    <span style="margin:0; font-size:18px; font-weight:700; letter-spacing:0.3px; line-height:1.2;">Techstacks</span>
                    <span style="margin:0; font-size:10px; opacity:0.55; letter-spacing:1.5px; text-transform:uppercase; line-height:1.2;">
                        @if($isAdmin) Admin Portal
                        @elseif($isHR) HR Portal
                        @else Employee Portal
                        @endif
                    </span>
                </div>
            </a>

            <div class="topbar-breadcrumb" style="display:flex; align-items:center; gap:6px; color:rgba(255,255,255,0.55); font-size:13px;">
                <span style="width:1px; height:28px; background:rgba(255,255,255,0.35); margin-right:10px; margin-left:15px;"></span>
                @yield('breadcrumb')
            </div>

            <div style="display:flex; align-items:center; gap:15px; margin-left:auto;">

                <div style="position:relative; z-index:1000; pointer-events:auto;">
                    <button id="notifBtn"
                            style="cursor:pointer; color:white; font-size:20px; z-index:1001; pointer-events:auto; margin-top:7px;">
                <i class="icon-[fluent--alert-24-filled]"></i>
                        @if($notifCount > 0)
                            <span id="notifCountDesktop" style="position:absolute; top:-4px; right:-4px; background:#ef4444; color:white; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; line-height:1;">
                                {{ $notifCount > 9 ? '9+' : $notifCount }}
                            </span>
                        @endif
                    </button>
                    <div id="notifDropdown"
                        style="display:none; position:absolute; right:0; top:calc(100% + 10px); width:320px; background:white; border-radius:14px; box-shadow:0 12px 32px rgba(0,0,0,0.14); border:1px solid #e5e7eb; z-index:999; overflow:hidden;">
                        <div style="padding:13px 16px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:14px; font-weight:700; color:#111827; display:flex; align-items:center; gap:7px;">
                                <i class="icon-[ph--bell-fill]" style="font-size:13px; color:#6b7280;"></i> Notifications
                            </span>
                            @if($notifCount > 0)
                                <span style="background:#f3f4f6; color:#374151; font-size:11px; font-weight:600; padding:3px 9px; border-radius:20px;">{{ $notifCount }} pending</span>
                            @endif
                        </div>
                        @include('partials.notifications-list')
                    </div>
                </div>

                <a href="{{ route('profile.show') }}" style="display:flex; align-items:center; gap:10px; text-decoration:none;">
                    <div class="user-avatar avatar-{{ $role }}" style="width:34px;height:34px;font-size:13px; overflow:hidden; padding:0;">
                        @if($user->profile_photo)
                            <img src="{{ profilePhotoUrl($user->profile_photo) }}"
                                alt="{{ $user->name }}"
                                style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="profile-group">
                        <div class="user-name" style="font-size:14px; font-weight:600;">{{ $user->name }}</div>
                        <div class="user-role" style="font-size:12px;">
                            @if($isAdmin) Administrator
                            @elseif($isHR) HR Personnel
                            @else Employee
                            @endif
                        </div>
                    </div>
                </a>


            </div>

        </div>

        {{-- Sidebar --}}
        <div id="main-sidebar" class="sidebar sidebar-{{ $role }} overlay [--auto-close:false]" style="grid-column: 1; grid-row: 2;">
            <div class="sidebar-toggle-row">
                <button id="sidebar-toggle" type="button"
                        aria-haspopup="true" aria-expanded="false" aria-label="Toggle sidebar"
                        data-overlay-minifier="#main-sidebar">
                    <i class="icon-[ph--caret-left-fill]" id="sidebar-arrow"></i>
                </button>
            </div>
            <nav style="flex: 1;">
                <a href="{{ route('dashboard') }}"
                class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="icon-[ph--house-fill]"></i><span>Dashboard</span>
                </a>

                @if($isAdmin)
                    @php
                        $userMgmtOpen    = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                        $empMgmtOpen     = request()->routeIs('employees.*') || request()->routeIs('manual-payroll-attendance.*');
                        $payrollMgmtOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                        $monitoringOpen  = request()->routeIs('audit-logs.*') || request()->routeIs('reports.*');
                    @endphp

                    <div class="nav-dropdown {{ $userMgmtOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--user-gear-fill]"></i><span>Manage Users</span>
                            <i class="icon-[ph--caret-down-fill]"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('users.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="icon-[ph--users-fill]"></i><span>Users</span>
                            </a>
                            <a href="{{ route('roles.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <i class="icon-[ph--lock-fill]"></i><span>Roles</span>
                            </a>
                            <a href="{{ route('permissions.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                                <i class="icon-[ph--shield-check-fill]"></i><span>Permissions</span>
                            </a>
                        </div>
                    </div>

                    <div class="nav-dropdown {{ $empMgmtOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--users-three-fill]"></i><span>Manage Employees</span>
                            <i class="icon-[ph--caret-down-fill]"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('employees.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="icon-[ph--identification-badge-fill]"></i><span>Employees</span>
                            </a>
                            <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}">
                                <i class="icon-[ph--calendar-check-fill]"></i><span>Attendance</span>
                            </a>
                            <a href="{{ route('work-requests.index') }}"
                            class="nav-item nav-sub-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}">
                                <i class="icon-[ph--calendar-check-fill]"></i><span>Work Requests</span>
                            </a>
                        </div>
                    </div>

                    <div class="nav-dropdown {{ $payrollMgmtOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--wallet-fill]"></i><span>Manage Payroll</span>
                            <i class="icon-[ph--caret-down-fill]"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('payroll.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                                <i class="icon-[ph--money-fill]"></i><span>Payroll</span>
                            </a>
                            <a href="{{ route('government-contributions.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                                <i class="icon-[ph--identification-card-fill]"></i><span>Gov. Contributions</span>
                            </a>
                        </div>
                    </div>

                    <div class="nav-dropdown {{ $monitoringOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--chart-line-fill]"></i><span>Monitoring</span>
                            <i class="icon-[ph--caret-down-fill]"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('audit-logs.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                                <i class="icon-[ph--file-text-fill]"></i><span>Audit Logs</span>
                            </a>
                            <a href="#" class="nav-item nav-sub-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="icon-[ph--chart-bar-fill]"></i><span>Reports</span>
                            </a>
                        </div>
                    </div>

                @elseif($isHR)
                    @php
                        $hrEmpOpen     = request()->routeIs('employees.*') || request()->routeIs('manual-payroll-attendance.*');
                        $hrPayrollOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                        $hrOtherOpen   = request()->routeIs('reports.*');
                    @endphp

                    <div class="nav-dropdown {{ $hrEmpOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--users-three-fill]"></i><span>Employee Management</span>
                            <i class="icon-[ph--caret-down-fill]"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('employees.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="icon-[ph--identification-badge-fill]"></i><span>Employees</span>
                            </a>
                            <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('manual-payroll-attendance.*') ? 'active' : '' }}">
                                <i class="icon-[ph--calendar-check-fill]"></i><span>Attendance</span>
                            </a>
                            <a href="{{ route('work-requests.index') }}"
                            class="nav-item nav-sub-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}">
                                <i class="icon-[ph--calendar-check-fill]"></i><span>Work Requests</span>
                            </a>
                        </div>
                    </div>

                    <div class="nav-dropdown {{ $hrPayrollOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--wallet-fill]"></i><span>Payroll</span>
                            <i class="icon-[ph--caret-down-fill]"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('payroll.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                                <i class="icon-[ph--money-fill]"></i><span>Payroll</span>
                            </a>
                            <a href="{{ route('government-contributions.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('government-contributions.*') ? 'active' : '' }}">
                                <i class="icon-[ph--identification-card-fill]"></i><span>Gov. Contributions</span>
                            </a>
                        </div>
                    </div>

                    <div class="nav-dropdown {{ $hrOtherOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--chart-line-fill]"></i><span>Reports & Settings</span>
                            <i class="icon-[ph--caret-down-fill]"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="#" class="nav-item nav-sub-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="icon-[ph--chart-bar-fill]"></i><span>Reports</span>
                            </a>


                            
                            <a href="#" class="nav-item nav-sub-item">
                                <i class="icon-[ph--gear-fill]"></i><span>Settings</span>
                            </a>
                        </div>
                    </div>

                @else
                    <a href="{{ route('profile.show') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="icon-[ph--user-fill]"></i><span>My Profile</span>
                    </a>
                    <a href="{{ route('payroll.index') }}" class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                        <i class="icon-[ph--receipt-fill]"></i><span>My Payslip</span>
                    </a>
                    <a href="{{ route('work-requests.index') }}" class="nav-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}"><i class="icon-[ph--calendar-check-fill]"></i><span>Work Requests</span></a>
                    <a href="{{ route('employee-attendance.index') }}" class="nav-item {{ request()->routeIs('employee-attendance.*') ? 'active' : '' }}"><i class="icon-[ph--clock-fill]"></i><span>Attendance</span></a>
                @endif
            </nav>

            <div style="padding-bottom: 20px; margin-top: auto; padding-top: 100px;">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="icon-[ph--sign-out-fill]"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="bg-{{ $role }}" style="grid-column: 2; grid-row: 2; overflow-y: auto;">
            <div class="content">
                @yield('content')
            </div>
        </div>

    </div>{{-- end .desktop-layout --}}

    {{-- Desktop notif dropdown --}}
    <script>
    document.addEventListener('click', function(e) {
        const btn      = document.getElementById('notifBtn');
        const dropdown = document.getElementById('notifDropdown');
        if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('notif-open');
        }
    });
    document.getElementById('notifBtn')?.addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('notifDropdown')?.classList.toggle('notif-open');
    });
    </script>

    {{-- Mobile notif dropdown --}}
    <script>
    document.addEventListener('click', function(e) {
        const btnM  = document.getElementById('notifBtnMobile');
        const dropM = document.getElementById('notifDropdownMobile');
        if (btnM && dropM && !btnM.contains(e.target) && !dropM.contains(e.target)) {
            dropM.classList.remove('notif-open');
        }
    });
    </script>

    {{-- Flash toasts --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
    <script>
        @if(session('success'))
            window.notyf?.success(@json(session('success')));
        @endif
        @if(session('error'))
            window.notyf?.error(@json(session('error')));
        @endif
        @if(session('warning'))
            window.notyf?.open({ type: 'warning', message: @json(session('warning')) });
        @endif
        @if(session('info'))
            window.notyf?.open({ type: 'info', message: @json(session('info')) });
        @endif
    </script>
    @endif

    {{-- SweetAlert2 for confirm dialogs only --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('scripts')

    </body>
    </html>
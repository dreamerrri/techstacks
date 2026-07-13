<!DOCTYPE html>
    <html lang="en" data-theme="mintlify">
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

        // Attendance nav item should also light up for payroll-period routes
        // (create / archived / store / finalize / archive / restore), since
        // those pages are conceptually part of the attendance/payroll workflow
        // even though they live under a separate 'payroll-periods.' route name.
        $attendanceActive = request()->routeIs('manual-payroll-attendance.*') || request()->routeIs('payroll-periods.*');

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
        $notifCount = \App\Models\Notification::forCurrentUser()->unread()->count();
    @endphp

    {{-- ═══════════════════════════════════════
        APP SHELL
        One shell, one content region. Mobile topbar/burger-menu and
        desktop topbar/sidebar are both always in the DOM as siblings;
        CSS (app-shell.css) decides which set is visible per breakpoint.
        @yield('content') renders exactly once — do NOT duplicate it,
        that's what caused every ID-based JS component (tabs, dropdowns,
        the allowance/benefit toggle) to silently target the wrong copy.
        ═══════════════════════════════════════ --}}
    <div class="app-shell">

        {{-- Mobile topbar --}}
        <div class="mobile-topbar sidebar-{{ $role }}">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <svg fill="currentColor" height="1.4em" viewBox="0 0 1813 1441" width="1.4em" xmlns="http://www.w3.org/2000/svg" class="brand-logo-icon">
                    <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fill-rule="evenodd"></path>
                    <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fill-rule="evenodd"></path>
                </svg>
                <div class="brand-text">
                    <span class="brand-title">Techstacks</span>
                    <span class="brand-subtitle">
                        @if($isAdmin) Admin Portal
                        @elseif($isHR) HR Portal
                        @else Employee Portal
                        @endif
                    </span>
                </div>
            </a>

            <div class="topbar-actions">
                <div class="notif-trigger">
                    <button id="notifBtnMobile"
                            onclick="document.getElementById('notifDropdownMobile').classList.toggle('notif-open')"
                            class="icon-btn icon-btn--notif">
                        <i class="icon-[ph--bell-fill]"></i>
                        @if($notifCount > 0)
                            <span class="notif-badge">
                                {{ $notifCount > 9 ? '9+' : $notifCount }}
                            </span>
                        @endif
                    </button>

                    <div id="notifDropdownMobile" class="notif-panel notif-panel--mobile">
                        <div class="notif-panel-header">
                            <span class="notif-panel-title">
                                <i class="icon-[ph--bell-fill]"></i> Notifications
                            </span>
                            @if($notifCount > 0)
                                <span class="notif-panel-count">{{ $notifCount }} pending</span>
                            @endif
                        </div>
                        @include('partials.notifications-list')
                    </div>
                </div>

                <a href="{{ route('profile.show') }}" class="avatar-link">
                    <div class="user-avatar avatar-{{ $role }}">
                        @if($user->profile_photo)
                            <img src="{{ profilePhotoUrl($user->profile_photo) }}"
                                alt="{{ $user->name }}"
                                class="avatar-img">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                </a>

                <button id="burgerBtn" class="icon-btn">
                    <i class="icon-[ph--list-fill]" id="burgerIcon"></i>
                </button>
            </div>
        </div>

        {{-- Mobile burger menu --}}
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
                <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item {{ $attendanceActive ? 'active' : '' }}">
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
                <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item {{ $attendanceActive ? 'active' : '' }}">
                    <i class="icon-[ph--calendar-check-fill]"></i><span>Attendance</span>
                </a>
                <a href="{{ route('work-requests.index') }}" class="nav-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}"><i class="icon-[ph--note-pencil-fill]"></i><span>Work Requests</span></a>
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

            <div class="burger-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="icon-[ph--sign-out-fill]"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- Desktop topbar --}}
        <div class="topbar desktop-topbar topbar-{{ $role }}">

            <a class="techicon brand-link" href="{{ route('dashboard') }}">
                <svg  fill="currentColor" height="2em" viewBox="0 0 1813 1441" width="2em" xmlns="http://www.w3.org/2000/svg" class="brand-logo-icon">
                    <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fill-rule="evenodd"></path>
                    <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fill-rule="evenodd"></path>
                </svg>
                <div class="tech brand-text">
                    <span class="brand-title">Techstacks</span>
                    <span class="brand-subtitle">
                        @if($isAdmin) Admin Portal
                        @elseif($isHR) HR Portal
                        @else Employee Portal
                        @endif
                    </span>
                </div>
            </a>

            <div class="topbar-breadcrumb">
                <span class="topbar-divider"></span>
                {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
            </div>

            <div class="topbar-actions">

                <div class="notif-trigger">
                    <button id="notifBtn"
                            class="icon-btn icon-btn--bell-desktop">
                <i class="icon-[fluent--alert-24-filled]"></i>
                        @if($notifCount > 0)
                            <span class="notif-badge">
                                {{ $notifCount > 9 ? '9+' : $notifCount }}
                            </span>
                        @endif
                    </button>
                    <div id="notifDropdown" class="notif-panel notif-panel--desktop">
                        <div class="notif-panel-header">
                            <span class="notif-panel-title">
                                <i class="icon-[ph--bell-fill]"></i> Notifications
                            </span>
                            @if($notifCount > 0)
                                <span class="notif-panel-count">{{ $notifCount }} pending</span>
                            @endif
                        </div>
                        @include('partials.notifications-list')
                    </div>
                </div>

                <a href="{{ route('profile.show') }}" class="avatar-link">
                    <div class="user-avatar avatar-{{ $role }} user-avatar--sm">
                        @if($user->profile_photo)
                            <img src="{{ profilePhotoUrl($user->profile_photo) }}"
                                alt="{{ $user->name }}"
                                class="avatar-img">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="profile-group">
                        <div class="user-name">{{ $user->name }}</div>
                        <div class="user-role">
                            @if($isAdmin) Administrator
                            @elseif($isHR) HR Personnel
                            @else Employee
                            @endif
                        </div>
                    </div>
                </a>

            </div>

        </div>

        {{-- Desktop sidebar --}}
        <div id="main-sidebar" class="sidebar sidebar-{{ $role }} overlay [--auto-close:false]">
            <div class="sidebar-toggle-row">
                <button id="sidebar-toggle" type="button"
                        aria-haspopup="true" aria-expanded="false" aria-label="Toggle sidebar"
                        data-overlay-minifier="#main-sidebar">
                    <i class="icon-[ph--caret-left-fill] bg-white" id="sidebar-arrow"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}"
                class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="icon-[ph--house-fill]"></i><span>Dashboard</span>
                </a>

                @if($isAdmin)
                    @php
                        $userMgmtOpen    = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                        $empMgmtOpen     = request()->routeIs('employees.*') || $attendanceActive;
                        $payrollMgmtOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                        $monitoringOpen  = request()->routeIs('audit-logs.*') || request()->routeIs('reports.*');
                    @endphp

                    <div class="nav-dropdown {{ $userMgmtOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger swap swap-rotate" type="button">
                            <i class="icon-[ph--user-gear-fill]"></i><span>Access Control</span>
                            <i class="dropdown-arrow icon-[ph--caret-down-fill]  w-4 h-4"></i>
                        </button>
                        <div class="nav-dropdown-menu" style="hidden">
                            <a href="{{ route('users.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="icon-[ph--users-fill] icon"></i><span>Users</span>
                            </a>
                            <a href="{{ route('roles.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <i class="icon-[ph--lock-fill] icon"></i><span>Roles</span>
                            </a>
                            <a href="{{ route('permissions.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                                <i class="icon-[ph--shield-check-fill] icon"></i><span>Permissions</span>
                            </a>
                        </div>
                    </div>

                    <div class="nav-dropdown {{ $empMgmtOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--users-three-fill]"></i><span>Workforce</span>
                            <i class="dropdown-arrow icon-[ph--caret-down-fill] w-4 h-4"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('employees.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="icon-[ph--identification-badge-fill]"></i><span>Employees</span>
                            </a>
                            <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item nav-sub-item {{ $attendanceActive ? 'active' : '' }}">
                                <i class="icon-[ph--calendar-check-fill]"></i><span>Attendance</span>
                            </a>
                            <a href="{{ route('work-requests.index') }}"
                            class="nav-item nav-sub-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}">
                                <i class="icon-[ph--note-pencil-fill]"></i><span>Work Requests</span>
                            </a>
                        </div>
                    </div>

                    <div class="nav-dropdown {{ $payrollMgmtOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--wallet-fill]"></i><span>Finance</span>
                            <i class="dropdown-arrow icon-[ph--caret-down-fill] w-4 h-4"></i>
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
                            <i class="icon-[ph--chart-line-fill]"></i><span >Monitoring</span>
                           <i class="dropdown-arrow icon-[ph--caret-down-fill] w-4 h-4" ></i>
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
                        $hrEmpOpen     = request()->routeIs('employees.*') || $attendanceActive;
                        $hrPayrollOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                        $hrOtherOpen   = request()->routeIs('reports.*');
                    @endphp

                    <div class="nav-dropdown {{ $hrEmpOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--users-three-fill]"></i><span>Workforce</span>
                            <i class="dropdown-arrow icon-[ph--caret-down-fill] w-4 h-4"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('employees.index') }}" class="nav-item nav-sub-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="icon-[ph--identification-badge-fill]"></i><span>Employees</span>
                            </a>
                            <a href="{{ route('manual-payroll-attendance.index') }}" class="nav-item nav-sub-item {{ $attendanceActive ? 'active' : '' }}">
                                <i class="icon-[ph--calendar-check-fill]"></i><span>Attendance</span>
                            </a>
                            <a href="{{ route('work-requests.index') }}"
                            class="nav-item nav-sub-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}">
                                <i class="icon-[ph--note-pencil-fill]"></i><span>Work Requests</span>
                            </a>
                        </div>
                    </div>

                    <div class="nav-dropdown {{ $hrPayrollOpen ? 'open' : '' }}">
                        <button class="nav-item nav-dropdown-trigger" type="button">
                            <i class="icon-[ph--wallet-fill]"></i><span>Finance</span>
                           <i class="dropdown-arrow icon-[ph--caret-down-fill] w-4 h-4"></i>
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
                            <i class="icon-[ph--chart-line-fill]"></i><span>Settings</span>
                           <i class="dropdown-arrow icon-[ph--caret-down-fill] w-4 h-4"></i>
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
                    <a href="{{ route('work-requests.index') }}" class="nav-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}"><i class="icon-[ph--note-pencil-fill]"></i><span>Work Requests</span></a>
                    <a href="{{ route('employee-attendance.index') }}" class="nav-item {{ request()->routeIs('employee-attendance.*') ? 'active' : '' }}"><i class="icon-[ph--clock-fill]"></i><span>Attendance</span></a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="icon-[ph--sign-out-fill]"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- Main content — rendered ONCE, positioned per-breakpoint by CSS --}}
        <div class="main-content bg-{{ $role }}">
            <div class="content">
                @yield('content')
            </div>
        </div>

    </div>{{-- end .app-shell --}}

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
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endif

    {{-- FlyonUI JS is loaded once via the Vite bundle (resources/js/app.js
         has `import 'flyonui/flyonui'`). Do NOT add a second
         <script src="../node_modules/flyonui/flyonui.js"> here — that was
         double-initializing every FlyonUI JS component against the
         (now-removed) duplicated DOM. --}}

    {{-- SweetAlert2 for confirm dialogs only --}}
   {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

   {{-- SweetAlert2's CDN script is also commented out, but app.js still calls Swal.fire(...) in confirmAction and the data-confirm form handler. Every data-confirm form (archive, delete allowance, delete benefit) will throw Swal is not defined in console and silently fail to submit. --}}


   @yield('scripts')
 {{-- @stack('scripts') --}}
    </body>
    </html>
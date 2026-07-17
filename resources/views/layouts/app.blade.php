<!DOCTYPE html>
<html lang="en" data-theme="{{ auth()->check() ? (auth()->user()->theme ?? 'techstacks') : 'techstacks' }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        {{-- FlyonUI theme fonts — required for corporate, ghibli, gourmet, luxury,
     slack, soft, valorant, claude, pastel, spotify, vscode themes --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Amaranth:ital,wght@0,400;0,700;1,400;1,700&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Archivo:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&family=Fira+Code:wght@300..700&display=swap" rel="stylesheet">


        <title>@yield('title') - HR Management System</title>

        {{-- ⚡ Must be first: restore the minified/collapsed sidebar state before
             first paint — prevents a flash of the expanded sidebar on load.
             (No dropdown-restore script needed anymore: which nav group starts
             "open" is now computed server-side per request, see $xxxOpen below,
             so there's nothing left for client JS to restore.) --}}
        <script>
            if (sessionStorage.getItem('sidebar_collapsed') === '1') {
                document.documentElement.classList.add('sidebar-pre-collapsed');
            }
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
        One shell, one content region, one sidebar. The old setup rendered a
        completely separate `.burger-dropdown` nav for mobile and a
        `.sidebar` for desktop — two copies of the same links, kept in sync
        by hand (and they'd already drifted: mobile's employee menu had
        "Leave Request" where desktop has "Work Requests"). That's gone now.

        #main-sidebar below is the ONE nav, for every breakpoint. FlyonUI's
        overlay+drawer component renders it as a slide-in drawer on mobile
        (triggered by the topbar's menu button via data-overlay) and, at
        768px (matching this shell's existing breakpoint — see shell.css),
        as the static collapsible rail — same DOM, same links, no
        duplication. @yield('content') still renders exactly once.
        ═══════════════════════════════════════ --}}
    <div class="app-shell">

        {{-- Mobile topbar --}}
        <div class="mobile-topbar topbar-{{ $role }}">
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

            {{-- mobile topbar --}}
<x-search-box id="search-modal-mobile" />

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

                {{-- Opens #main-sidebar as a slide-in drawer. FlyonUI wires this up
                     automatically for any element with data-overlay="#main-sidebar" —
                     no custom JS, no icon swap. See burger.js removal note in app.js. --}}
                <button type="button"
                        class="icon-btn"
                        aria-haspopup="dialog"
                        aria-expanded="false"
                        aria-controls="main-sidebar"
                        aria-label="Open navigation menu"
                        data-overlay="#main-sidebar">
                    <i class="icon-[ph--list-fill]"></i>
                </button>
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

{{-- Desktop topbar --}}
<x-search-box id="search-modal-desktop" />

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

        {{-- ═══════════════════════════════════════
            UNIFIED SIDEBAR — drawer on mobile, collapsible rail on desktop.
            Classes match FlyonUI's own overlay+drawer+minifier sample, with
            two deliberate changes:
              1. Breakpoint is 768px (min-[768px]:...), not Tailwind's
                 default sm: (640px) — matches this shell's existing
                 mobile/desktop split so there's no broken zone between
                 640-768px where sidebar and topbar would disagree.
              2. The sample uses `sm:absolute sm:z-0` to place itself at
                 desktop widths, which assumes it's the only positioned
                 thing on the page. We already have a CSS Grid shell
                 (.app-shell), so instead shell.css forces `position: static`
                 back on at 768px+ and lets the grid do the placement —
                 same idea, adapted to fit the existing layout.
            ═══════════════════════════════════════ --}}
        <aside id="main-sidebar"
               class="sidebar-{{ $role }} overlay [--auto-close:768] drawer drawer-start hidden w-[250px] min-[768px]:flex min-[768px]:translate-x-0 min-[768px]:shadow-none border-e border-base-content/15"
               role="dialog"
               tabindex="-1"
               aria-label="Main navigation">

            <div class="drawer-header overlay-minified:justify-center overlay-minified:px-0 py-3 border-b border-base-content/10">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 overlay-minified:hidden">
                    <svg fill="currentColor" height="1.5em" viewBox="0 0 1813 1441" width="1.5em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fill-rule="evenodd"></path>
                        <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fill-rule="evenodd"></path>
                    </svg>
                    <span class="drawer-title text-base font-semibold">Techstacks</span>
                </a>

                {{-- Desktop-only minify toggle (icon-rail collapse). Same
                     #main-sidebar id, so the existing minify-persistence
                     logic in app.js needs no changes. --}}
                <button type="button"
                        class="btn btn-circle btn-text hidden min-[768px]:flex"
                        id="sidebar-toggle"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-label="Toggle sidebar width"
                        data-overlay-minifier="#main-sidebar">
                    <i class="icon-[ph--caret-left-fill] transition-transform overlay-minified:rotate-180" id="sidebar-arrow"></i>
                </button>

                {{-- Mobile-only close button — the sample relies on
                     backdrop-click/Escape alone, but an explicit close
                     affordance is worth the one extra button. --}}
                <button type="button"
                        class="btn btn-circle btn-text min-[768px]:hidden"
                        aria-label="Close navigation menu"
                        data-overlay="#main-sidebar">
                    <i class="icon-[ph--x]"></i>
                </button>
            </div>

            <nav class="drawer-body px-2 pt-3">
                <ul class="menu p-0 sidebar-menu">

                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'menu-active' : '' }}">
                            <span class="icon-[ph--house-fill] size-5"></span>
                            <span class="overlay-minified:hidden">Dashboard</span>
                        </a>
                    </li>

                    @if($isAdmin)
                        @php
                            $userMgmtOpen    = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                            $empMgmtOpen     = request()->routeIs('employees.*') || $attendanceActive;
                            $payrollMgmtOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                            $monitoringOpen  = request()->routeIs('audit-logs.*') || request()->routeIs('reports.*');
                        @endphp

                        <x-sidebar-nav-group id="dropdown-access-control" label="Access Control" icon="ph--user-gear-fill" :open="$userMgmtOpen">
                            <li>
                                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--users-fill] size-5"></span> Users
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--lock-fill] size-5"></span> Roles
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('permissions.index') }}" class="{{ request()->routeIs('permissions.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--shield-check-fill] size-5"></span> Permissions
                                </a>
                            </li>
                        </x-sidebar-nav-group>

                        <x-sidebar-nav-group id="dropdown-workforce" label="Workforce" icon="ph--users-three-fill" :open="$empMgmtOpen">
                            <li>
                                <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--identification-badge-fill] size-5"></span> Employees
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('manual-payroll-attendance.index') }}" class="{{ $attendanceActive ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--calendar-check-fill] size-5"></span> Attendance
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('work-requests.index') }}" class="{{ request()->routeIs('work-requests.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--note-pencil-fill] size-5"></span> Work Requests
                                </a>
                            </li>
                        </x-sidebar-nav-group>

                        <x-sidebar-nav-group id="dropdown-finance" label="Finance" icon="ph--wallet-fill" :open="$payrollMgmtOpen">
                            <li>
                                <a href="{{ route('payroll.index') }}" class="{{ request()->routeIs('payroll.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--money-fill] size-5"></span> Payroll
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('government-contributions.index') }}" class="{{ request()->routeIs('government-contributions.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--identification-card-fill] size-5"></span> Gov. Contributions
                                </a>
                            </li>
                        </x-sidebar-nav-group>

                        <x-sidebar-nav-group id="dropdown-monitoring" label="Monitoring" icon="ph--chart-line-fill" :open="$monitoringOpen">
                            <li>
                                <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--file-text-fill] size-5"></span> Audit Logs
                                </a>
                            </li>
                            <li>
                                <a href="#" class="{{ request()->routeIs('reports.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--chart-bar-fill] size-5"></span> Reports
                                </a>
                            </li>
                        </x-sidebar-nav-group>

                    @elseif($isHR)
                        @php
                            $hrEmpOpen     = request()->routeIs('employees.*') || $attendanceActive || request()->routeIs('work-requests.*');
                            $hrPayrollOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                            $hrOtherOpen   = request()->routeIs('reports.*');
                        @endphp

                        <x-sidebar-nav-group id="dropdown-workforce" label="Workforce" icon="ph--users-three-fill" :open="$hrEmpOpen">
                            <li>
                                <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--identification-badge-fill] size-5"></span> Employees
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('manual-payroll-attendance.index') }}" class="{{ $attendanceActive ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--calendar-check-fill] size-5"></span> Attendance
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('work-requests.index') }}" class="{{ request()->routeIs('work-requests.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--note-pencil-fill] size-5"></span> Work Requests
                                </a>
                            </li>
                        </x-sidebar-nav-group>

                        <x-sidebar-nav-group id="dropdown-finance" label="Finance" icon="ph--wallet-fill" :open="$hrPayrollOpen">
                            <li>
                                <a href="{{ route('payroll.index') }}" class="{{ request()->routeIs('payroll.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--money-fill] size-5"></span> Payroll
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('government-contributions.index') }}" class="{{ request()->routeIs('government-contributions.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--identification-card-fill] size-5"></span> Gov. Contributions
                                </a>
                            </li>
                        </x-sidebar-nav-group>

                        {{-- NOTE: this group was labeled "Settings" in the old markup
                             but used the chart-line icon and only ever contained
                             Reports + a dead Settings link (both href="#"). Kept
                             as-is content-wise — flagging in case that was drift
                             rather than intentional. --}}
                        <x-sidebar-nav-group id="dropdown-settings" label="Settings" icon="ph--chart-line-fill" :open="$hrOtherOpen">
                            <li>
                                <a href="#" class="{{ request()->routeIs('reports.*') ? 'menu-active' : '' }}">
                                    <span class="icon-[ph--chart-bar-fill] size-5"></span> Reports
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <span class="icon-[ph--gear-fill] size-5"></span> Settings
                                </a>
                            </li>
                        </x-sidebar-nav-group>

                    @else
                        {{-- Employee nav: the old mobile burger-dropdown and desktop
                             sidebar had actually drifted apart here — mobile had
                             "Leave Request" (href="#", no route) instead of
                             "Work Requests" (a real route). Since this is now one
                             nav for both breakpoints, I went with the desktop/
                             route-backed set below. Swap this back to Leave
                             Request if that was actually intentional. --}}
                        <li>
                            <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'menu-active' : '' }}">
                                <span class="icon-[ph--user-fill] size-5"></span>
                                <span class="overlay-minified:hidden">My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('payroll.index') }}" class="{{ request()->routeIs('payroll.*') ? 'menu-active' : '' }}">
                                <span class="icon-[ph--receipt-fill] size-5"></span>
                                <span class="overlay-minified:hidden">My Payslip</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('work-requests.index') }}" class="{{ request()->routeIs('work-requests.*') ? 'menu-active' : '' }}">
                                <span class="icon-[ph--note-pencil-fill] size-5"></span>
                                <span class="overlay-minified:hidden">Work Requests</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('employee-attendance.index') }}" class="{{ request()->routeIs('employee-attendance.*') ? 'menu-active' : '' }}">
                                <span class="icon-[ph--clock-fill] size-5"></span>
                                <span class="overlay-minified:hidden">Attendance</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>

            <div class="drawer-footer overlay-minified:justify-center overlay-minified:px-2 border-t border-base-content/10">
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="logout-btn w-full">
                        <span class="icon-[ph--sign-out-fill] size-5"></span>
                        <span class="overlay-minified:hidden">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

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
   @stack('modals')
 {{-- @stack('scripts') --}}
    </body>
    </html>
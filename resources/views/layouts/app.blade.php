<!DOCTYPE html>
<html lang="en" data-theme="{{ auth()->check() ? (auth()->user()->theme ?? 'techstacks') : 'techstacks' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - HR Management System</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')

    <style>
    /* Single source of truth for sidebar width.
       Sidebar element width AND main-content start-padding
       both read from these two variables — never hardcode
       the width in more than one place. */
    :root {
        --sidebar-w: 16.5rem;   /* expanded width, matches old w-66 */
        --sidebar-w-mini: 4.25rem; /* minified width, matches old w-17 */
    }
</style>
</head>
<body>

@php
    $user    = auth()->user();
    $isAdmin = $user->role === 'admin';
    $isHR    = $user->role === 'hr';
    $role    = $isAdmin ? 'admin' : ($isHR ? 'hr' : 'user');

    $attendanceActive = request()->routeIs('manual-payroll-attendance.*') || request()->routeIs('payroll-periods.*');

    function profilePhotoUrl($path) {
        if (app()->environment('production')) {
            return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($path, now()->addHours(24));
        }
        return asset('images/placeholder-avatar.png');
    }

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

<div class="flex flex-col flex-1 min-w-0 sm:ps-[var(--sidebar-w)] overlay-minified:sm:ps-[var(--sidebar-w-mini)] transition-[padding] duration-300">

    {{-- Sidebar --}}
<aside id="collapsible-mini-sidebar"
       class="overlay [--auto-close:sm] sm:shadow-none overlay-open:translate-x-0 drawer drawer-start hidden sm:fixed sm:inset-y-0 sm:start-0 sm:z-10 sm:flex sm:translate-x-0 border-e border-base-content/20 overflow-y-auto w-[var(--sidebar-w)] overlay-minified:w-[var(--sidebar-w-mini)]"
       role="dialog" tabindex="-1">

        <div class="drawer-header overlay-minified:px-3.75 py-2 w-full flex items-center justify-between gap-3">
             <x-techicon>
        @if($isAdmin) Admin Portal
        @elseif($isHR) HR Portal
        @else Employee Portal
        @endif
    </x-techicon>
          
        </div>

        

        <div class="drawer-body px-2 pt-4">
            <ul class="menu p-0">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="icon-[tabler--home] size-5"></span>
                        <span class="overlay-minified:hidden">Dashboard</span>
                    </a>
                </li>

                @if($isAdmin)
                    @php
                        $userMgmtOpen    = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                        $empMgmtOpen     = request()->routeIs('employees.*') || $attendanceActive || request()->routeIs('work-requests.*');
                        $payrollMgmtOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                        $monitoringOpen  = request()->routeIs('audit-logs.*') || request()->routeIs('reports.*');
                    @endphp

                    <li class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
                        <button type="button" class="dropdown-toggle" aria-haspopup="menu" aria-expanded="false">
                            <span class="icon-[tabler--lock] size-5"></span>
                            <span class="overlay-minified:hidden">Access Control</span>
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
                        </button>
<ul class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-60 ms-6 ps-2 border-s border-base-content/20 rounded-none {{ $userMgmtOpen ? 'open' : '' }}" role="menu">
                                <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><span class="icon-[tabler--users] size-5"></span>Users</a></li>
                            <li><a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"><span class="icon-[tabler--shield] size-5"></span>Roles</a></li>
                            <li><a href="{{ route('permissions.index') }}" class="{{ request()->routeIs('permissions.*') ? 'active' : '' }}"><span class="icon-[tabler--shield-check] size-5"></span>Permissions</a></li>
                        </ul>
                    </li>

                    <li class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
                        <button type="button" class="dropdown-toggle" aria-haspopup="menu" aria-expanded="false">
                            <span class="icon-[tabler--users-group] size-5"></span>
                            <span class="overlay-minified:hidden">Workforce</span>
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
                        </button>
                        <ul class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-60 ms-6 ps-2 border-s border-base-content/20 rounded-none {{ $empMgmtOpen ? 'open' : '' }}" role="menu">
                            <li><a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}"><span class="icon-[tabler--id] size-5"></span>Employees</a></li>
                            <li><a href="{{ route('manual-payroll-attendance.index') }}" class="{{ $attendanceActive ? 'active' : '' }}"><span class="icon-[tabler--calendar-check] size-5"></span>Attendance</a></li>
                            <li><a href="{{ route('work-requests.index') }}" class="{{ request()->routeIs('work-requests.*') ? 'active' : '' }}"><span class="icon-[tabler--notes] size-5"></span>Work Requests</a></li>
                        </ul>
                    </li>

                    <li class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
                        <button type="button" class="dropdown-toggle" aria-haspopup="menu" aria-expanded="false">
                            <span class="icon-[tabler--wallet] size-5"></span>
                            <span class="overlay-minified:hidden">Finance</span>
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
                        </button>
                        <ul class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-60 ms-6 ps-2 border-s border-base-content/20 rounded-none {{ $payrollMgmtOpen ? 'open' : '' }}" role="menu">
                            <li><a href="{{ route('payroll.index') }}" class="{{ request()->routeIs('payroll.*') ? 'active' : '' }}"><span class="icon-[tabler--cash] size-5"></span>Payroll</a></li>
                            <li><a href="{{ route('government-contributions.index') }}" class="{{ request()->routeIs('government-contributions.*') ? 'active' : '' }}"><span class="icon-[tabler--id-badge] size-5"></span>Gov. Contributions</a></li>
                        </ul>
                    </li>

                    <li class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
                        <button type="button" class="dropdown-toggle" aria-haspopup="menu" aria-expanded="false">
                            <span class="icon-[tabler--chart-line] size-5"></span>
                            <span class="overlay-minified:hidden">Monitoring</span>
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
                        </button>
                        <ul class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-60 ms-6 ps-2 border-s border-base-content/20 rounded-none {{ $monitoringOpen ? 'open' : '' }}" role="menu">
                            <li><a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}"><span class="icon-[tabler--file-text] size-5"></span>Audit Logs</a></li>
                            <li><a href="#" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><span class="icon-[tabler--chart-bar] size-5"></span>Reports</a></li>
                        </ul>
                    </li>

                @elseif($isHR)
                    @php
                        $hrEmpOpen     = request()->routeIs('employees.*') || $attendanceActive || request()->routeIs('work-requests.*');
                        $hrPayrollOpen = request()->routeIs('payroll.*') || request()->routeIs('government-contributions.*');
                    @endphp

                    <li class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
                        <button type="button" class="dropdown-toggle" aria-haspopup="menu" aria-expanded="false">
                            <span class="icon-[tabler--users-group] size-5"></span>
                            <span class="overlay-minified:hidden">Workforce</span>
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
                        </button>
                        <ul class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-60 ms-6 ps-2 border-s border-base-content/20 rounded-none {{ $hrEmpOpen ? 'open' : '' }}" role="menu">
                            <li><a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}"><span class="icon-[tabler--id] size-5"></span>Employees</a></li>
                            <li><a href="{{ route('manual-payroll-attendance.index') }}" class="{{ $attendanceActive ? 'active' : '' }}"><span class="icon-[tabler--calendar-check] size-5"></span>Attendance</a></li>
                            <li><a href="{{ route('work-requests.index') }}" class="{{ request()->routeIs('work-requests.*') ? 'active' : '' }}"><span class="icon-[tabler--notes] size-5"></span>Work Requests</a></li>
                        </ul>
                    </li>

                    <li class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
                        <button type="button" class="dropdown-toggle" aria-haspopup="menu" aria-expanded="false">
                            <span class="icon-[tabler--wallet] size-5"></span>
                            <span class="overlay-minified:hidden">Finance</span>
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
                        </button>
                        <ul class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-60 ms-6 ps-2 border-s border-base-content/20 rounded-none {{ $hrPayrollOpen ? 'open' : '' }}" role="menu">
                            <li><a href="{{ route('payroll.index') }}" class="{{ request()->routeIs('payroll.*') ? 'active' : '' }}"><span class="icon-[tabler--cash] size-5"></span>Payroll</a></li>
                            <li><a href="{{ route('government-contributions.index') }}" class="{{ request()->routeIs('government-contributions.*') ? 'active' : '' }}"><span class="icon-[tabler--id-badge] size-5"></span>Gov. Contributions</a></li>
                        </ul>
                    </li>

                @else
                    <li>
                        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <span class="icon-[tabler--user] size-5"></span>
                            <span class="overlay-minified:hidden">My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payroll.index') }}" class="{{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                            <span class="icon-[tabler--receipt] size-5"></span>
                            <span class="overlay-minified:hidden">My Payslip</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('employee-attendance.index') }}" class="{{ request()->routeIs('employee-attendance.*') ? 'active' : '' }}">
                            <span class="icon-[tabler--clock] size-5"></span>
                            <span class="overlay-minified:hidden">Attendance</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('work-requests.index') }}" class="{{ request()->routeIs('work-requests.*') ? 'active' : '' }}">
                            <span class="icon-[tabler--notes] size-5"></span>
                            <span class="overlay-minified:hidden">Work Requests</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="drawer-footer p-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-soft btn-block">
                    <span class="icon-[tabler--logout] size-5"></span>
                    <span class="overlay-minified:hidden">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main column: navbar + page content --}}
    <div class="flex flex-col flex-1 min-w-0 ">

        {{-- Navbar --}}
        <nav class="navbar bg-base-100  gap-4 shadow-base-300/20 shadow-sm  ">
            <div class="navbar-start items-center gap-2">
                <button type="button" class="btn btn-text max-sm:btn-square sm:hidden"
                        aria-haspopup="dialog" aria-expanded="false" aria-controls="collapsible-mini-sidebar"
                        data-overlay="#collapsible-mini-sidebar">
                    <span class="icon-[tabler--menu-2] size-5"></span>
                </button>
            {{-- <a class="link text-base-content link-neutral text-xl font-bold no-underline" href="{{ route('dashboard') }}">
                    @if($isAdmin) Admin Portal
                    @elseif($isHR) HR Portal
                    @else Employee Portal
                    @endif
                </a>  --}}    

                  <div class="hidden sm:block">
                <button type="button" class="btn btn-circle btn-text" aria-haspopup="dialog" aria-expanded="false"
                        aria-controls="collapsible-mini-sidebar" aria-label="Minify navigation"
                        data-overlay-minifier="#collapsible-mini-sidebar">
                    <span class="icon-[tabler--menu-2] size-5"></span>
                </button>
            </div>
            </div>
            

            <div class="navbar-end flex items-center gap-4">

                <x-search-box id="search-modal" />

                {{-- Notifications --}}
                <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                    <button id="notif-dropdown" type="button" class="dropdown-toggle btn btn-text btn-circle relative"
                            aria-haspopup="menu" aria-expanded="false" aria-label="Notifications">
                        <span class="icon-[tabler--bell] size-5"></span>
                        @if($notifCount > 0)
                            <span class="badge badge-error badge-sm absolute -top-1 -end-1">
                                {{ $notifCount > 9 ? '9+' : $notifCount }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-open:opacity-100 hidden min-w-72 z-50" role="menu" aria-labelledby="notif-dropdown">
                        @include('partials.notifications-list')
                    </div>
                </div>

                {{-- User avatar dropdown --}}
                <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                    <button id="dropdown-avatar" type="button" class="dropdown-toggle flex items-center" aria-haspopup="menu" aria-expanded="false">
                        <div class="avatar">
                            <div class="size-9.5 rounded-full">
                                @if($user->profile_photo)
                                    <img src="{{ profilePhotoUrl($user->profile_photo) }}" alt="{{ $user->name }}">
                                @else
                                    <span class="flex items-center justify-center bg-base-200 size-full rounded-full">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-labelledby="dropdown-avatar">
                        <li class="dropdown-header gap-2">
                            <div>
                                <h6 class="text-base-content text-base font-semibold">{{ $user->name }}</h6>
                                <small class="text-base-content/50">
                                    @if($isAdmin) Administrator
                                    @elseif($isHR) HR Personnel
                                    @else Employee
                                    @endif
                                </small>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                <span class="icon-[tabler--user]"></span>
                                My Profile
                            </a>
                        </li>
                        <li class="dropdown-footer gap-2">
                            <form action="{{ route('logout') }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="btn btn-error btn-soft btn-block">
                                    <span class="icon-[tabler--logout]"></span>
                                    Sign out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        {{-- Page content --}}
        <main class="p-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@yield('scripts')
@stack('modals')
{{-- @stack('scripts') --}}

</body>
</html>
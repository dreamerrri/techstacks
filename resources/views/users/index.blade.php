@extends('layouts.app')

@section('title', 'All Users')
@section('breadcrumb')
    <span>Manage Users</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-medium">Users</span>
@endsection

@section('content')

    {{-- Header --}}
    <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
        <div>
            <span class="badge badge-soft badge-success mb-2">
                <i class="fas fa-users-cog"></i> User Management
            </span>
            <p class="text-gray-500 m-0">Manage system accounts, roles, and access.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-red-600 bg-red-100">
                <i class="fas fa-users"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\User::count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Total Users</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-red-800 bg-red-100">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\User::where('role','admin')->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Admins</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-amber-600 bg-amber-100">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\User::where('role','hr')->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">HR Personnel</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-emerald-600 bg-emerald-100">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\User::where('is_active', true)->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Active Accounts</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card bg-base-100 shadow-sm overflow-hidden flex flex-col p-0">

        {{-- Card header --}}
        <div class="sticky top-0 z-10 bg-white px-7 pt-5 rounded-t-2xl">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 flex items-center gap-2 m-0">
                    <i class="fas fa-list"></i> User Accounts
                </h2>
            </div>

            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('users.index') }}"
                  class="flex flex-wrap gap-2 pb-4 border-b border-gray-200">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name or email..."
                       class="input input-bordered input-sm flex-1 min-w-40">
                <select name="role" class="select select-bordered select-sm">
                    <option value="">All Roles</option>
                    <option value="admin"    {{ request('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                    <option value="hr"       {{ request('role') === 'hr'       ? 'selected' : '' }}>HR</option>
                    <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                </select>
                <select name="status" class="select select-bordered select-sm">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-soft btn-error btn-sm">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request()->hasAny(['search','role','status']))
                    <a href="{{ route('users.index') }}" class="btn btn-soft btn-sm">Clear</a>
                @endif
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="table-responsive overflow-y-auto max-h-[53vh] px-7 hidden md:block">
            <table class="table table-hover w-full text-sm">
                <thead class="sticky top-0 z-5">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>
                            @php
                                $loginDir = (request('sort') === 'last_login_at' && request('direction') === 'asc') ? 'desc' : 'asc';
                                $loginActive = request('sort') === 'last_login_at';
                            @endphp
                            <a href="{{ route('users.index', array_merge(request()->except(['sort','direction','page']), ['sort' => 'last_login_at', 'direction' => $loginDir])) }}"
                               class="inline-flex items-center gap-1 no-underline uppercase tracking-wider text-xs {{ $loginActive ? 'text-red-600 font-bold' : 'text-gray-500 font-semibold' }}">
                                Last Login
                                <span class="inline-flex flex-col leading-none gap-px">
                                    <i class="fas fa-caret-up text-[9px] {{ ($loginActive && request('direction') === 'asc') ? 'text-red-600' : 'text-gray-300' }}"></i>
                                    <i class="fas fa-caret-down text-[9px] {{ ($loginActive && request('direction') === 'desc') ? 'text-red-600' : 'text-gray-300' }}"></i>
                                </span>
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $roleClass = match($user->role) {
                                'admin'    => 'badge-soft badge-error',
                                'hr'       => 'badge-soft badge-warning',
                                'employee' => 'badge-soft badge-info',
                                default    => 'badge-soft',
                            };
                        @endphp
                        <tr>
                            {{-- Name --}}
                            <td class="font-semibold text-gray-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
                                        @if($user->profile_photo)
                                            <img src="{{ config('filesystems.default') === 's3'
                                                ? \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($user->profile_photo, now()->addHours(24))
                                                : \Illuminate\Support\Facades\Storage::url($user->profile_photo) }}"
                                                alt="{{ $user->name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-xs font-bold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    @if($user->employee)
                                        <a href="{{ route('employees.show', $user->employee) }}" class="text-gray-800 no-underline font-semibold hover:text-emerald-600">
                                            {{ $user->name }}
                                        </a>
                                    @else
                                        {{ $user->name }}
                                    @endif
                                    @if($user->id === auth()->id())
                                        <span class="badge badge-soft badge-success text-[10px] px-2 py-0 normal-case tracking-normal">You</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-gray-500">{{ $user->email }}</td>

                            {{-- Role selector --}}
                            <td>
                                <form method="POST" action="{{ route('users.role', $user) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <select name="role" onchange="this.form.submit()"
                                            {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                            class="select select-bordered select-xs rounded-full {{ $roleClass }}">
                                        <option value="admin"    {{ $user->role === 'admin'    ? 'selected' : '' }}>Admin</option>
                                        <option value="hr"       {{ $user->role === 'hr'       ? 'selected' : '' }}>HR</option>
                                        <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                                    </select>
                                </form>
                            </td>

                            {{-- Status --}}
                            <td>
                                @if($user->is_active)
                                    <span class="badge badge-soft badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                @else
                                    <span class="badge badge-soft badge-error"><i class="fas fa-times-circle"></i> Inactive</span>
                                @endif
                            </td>

                            <td class="text-gray-500 text-xs">
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}
                            </td>

                            {{-- Toggle action --}}
                            <td>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.toggle', $user) }}"
                                          data-confirm="{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}"
                                          data-confirm-title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                          data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                          data-confirm-btn="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-soft btn-xs {{ $user->is_active ? 'btn-error' : 'btn-success' }}">
                                            <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-400">
                                <i class="fas fa-users text-3xl mb-2 block"></i>
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="user-mobile-cards p-4 md:hidden">
            @forelse($users as $user)
                @php
                    $roleClass = match($user->role) {
                        'admin'    => 'badge-soft badge-error',
                        'hr'       => 'badge-soft badge-warning',
                        'employee' => 'badge-soft badge-info',
                        default    => 'badge-soft',
                    };
                @endphp
                <div class="card bg-base-100 border border-gray-200 p-4 mb-3">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                @if($user->profile_photo)
                                    <img src="{{ config('filesystems.default') === 's3'
                                        ? \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($user->profile_photo, now()->addHours(24))
                                        : \Illuminate\Support\Facades\Storage::url($user->profile_photo) }}"
                                        alt="{{ $user->name }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-sm font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800 text-sm flex items-center gap-1">
                                    @if($user->employee)
                                        <a href="{{ route('employees.show', $user->employee) }}" class="text-gray-800 no-underline font-semibold">{{ $user->name }}</a>
                                    @else
                                        {{ $user->name }}
                                    @endif
                                    @if($user->id === auth()->id())
                                        <span class="badge badge-soft badge-success text-[10px] px-2 py-0 normal-case">You</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </div>
                        </div>
                        @if($user->is_active)
                            <span class="badge badge-soft badge-success whitespace-nowrap"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge badge-soft badge-error whitespace-nowrap"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </div>

                    <div class="flex justify-between items-center flex-wrap gap-2 pt-3 border-t border-gray-100">
                        <form method="POST" action="{{ route('users.role', $user) }}" class="inline">
                            @csrf @method('PATCH')
                            <select name="role" onchange="this.form.submit()"
                                    {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                    class="select select-bordered select-xs rounded-full {{ $roleClass }}">
                                <option value="admin"    {{ $user->role === 'admin'    ? 'selected' : '' }}>Admin</option>
                                <option value="hr"       {{ $user->role === 'hr'       ? 'selected' : '' }}>HR</option>
                                <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                            </select>
                        </form>

                        <span class="text-xs text-gray-400">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never logged in' }}
                        </span>

                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle', $user) }}"
                                  data-confirm="{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}"
                                  data-confirm-title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                  data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                  data-confirm-btn="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-soft btn-xs {{ $user->is_active ? 'btn-error' : 'btn-success' }}">
                                    <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-gray-400">
                    <i class="fas fa-users text-3xl mb-2 block"></i>
                    No users found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="px-7 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>

    </div>

@endsection
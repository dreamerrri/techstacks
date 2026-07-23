@extends('layouts.app')

@section('title', 'All Users')

@section('content')

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5 ">
        <div class="card bg-base-100 border border-base-300  p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                <i class="icon-[tabler--users]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\User::count() }}</div>
            <div class="text-xs text-base-content/80 uppercase tracking-widest font-medium">Total Users</div>
        </div>
        <div class="card bg-base-100 border border-base-300  p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-error bg-error/10">
                <i class="icon-[tabler--shield-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\User::where('role','admin')->count() }}</div>
            <div class="text-xs text-base-content/80 uppercase tracking-widest font-medium">Admins</div>
        </div>
        <div class="card bg-base-100 border border-base-300  p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-warning bg-warning/10">
                <i class="icon-[tabler--user]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\User::where('role','hr')->count() }}</div>
            <div class="text-xs text-base-content/80 uppercase tracking-widest font-medium">HR Personnel</div>
        </div>
        <div class="card bg-base-100 border border-base-300  p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-success bg-success/10">
                <i class="icon-[tabler--circle-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\User::where('is_active', true)->count() }}</div>
            <div class="text-xs text-base-content/80 uppercase tracking-widest font-medium">Active Accounts</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <x-table-card action="{{ route('users.index') }}">
        <x-slot:title>
            <x-dot-loader /> <p class="text-base-content">User Accounts</p>
            <x-info-tooltip>
                Manage system accounts, roles, and access.
            </x-info-tooltip>
        </x-slot:title>

        <x-slot:filters>
            {{-- Search group --}}
            <div class="join flex-none w-64 min-w-40">
                <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                       placeholder="Search name or email..."
                       oninput="clearTimeout(this._t); this._t = setTimeout(() => this.closest('form').submit(), 400)"
                       class="input input-bordered input-sm bg-base-200  join-item w-full ">
               <button type="submit" class="btn btn-outline btn-primary btn-sm join-item">
                    <i class="icon-[tabler--search]"></i>
                </button>
            </div>

            {{-- Filters group --}}
            <div class="flex flex-row gap-2 md:ml-auto">
                <select name="role" id="role-select" onchange="this.closest('form').submit()"
                        class="select select-bordered select-sm">
                    <option value="">All Roles</option>
                    <option value="admin"    {{ request('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                    <option value="hr"       {{ request('role') === 'hr'       ? 'selected' : '' }}>HR</option>
                    <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                </select>
                <select name="status" id="status-select" onchange="this.closest('form').submit()"
                        class="select select-bordered select-sm">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @if(request()->hasAny(['search','role','status']))
                    <a href="{{ route('users.index') }}" class="btn  btn-sm">Clear</a>
                @endif
            </div>
        </x-slot:filters>

        {{-- Desktop Table --}}
        <x-data-table maxHeight="53vh">
            <x-slot:head>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <x-sortable-th sort-key="last_login_at" label="Last Login" route="users.index" />
                <th>Actions</th>
            </x-slot:head>

            @forelse($users as $user)
                @php
                    $roleClass = match($user->role) {
                        'admin'    => 'badge-soft badge-error',
                        'hr'       => 'badge-soft badge-warning',
                        'employee' => 'badge-soft badge-info',
                        default    => 'badge-soft',
                    };
                @endphp
                <tr class="row-hover">
                    <td class="font-semibold text-base-content">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
                                @if($user->profile_photo)
                                    <img src="{{ config('filesystems.default') === 's3'
                                        ? \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($user->profile_photo, now()->addHours(24))
                                        : \Illuminate\Support\Facades\Storage::url($user->profile_photo) }}"
                                        alt="{{ $user->name }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-primary-content text-xs font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            @if($user->employee)
                                <a href="{{ route('employees.show', $user->employee) }}" class="text-base-content no-underline font-semibold hover:text-primary">
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

                    <td class="text-base-content/60">{{ $user->email }}</td>

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

                    <td>
                        @if($user->is_active)
                            <span class="badge badge-soft badge-success"><i class="icon-[tabler--circle-check]"></i> Active</span>
                        @else
                            <span class="badge badge-soft badge-error"><i class="icon-[tabler--circle-x]"></i> Inactive</span>
                        @endif
                    </td>

                    <td class="text-base-content/60 text-xs">
                        {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}
                    </td>

                    <td>
                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle', $user) }}"
                                  data-confirm="{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}"
                                  data-confirm-title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                  data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                  data-confirm-btn="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}">
                                @csrf @method('PATCH')
                                <button class="btn  btn-xs {{ $user->is_active ? 'btn-error' : 'btn-success' }}">
                                    <i class="{{ $user->is_active ? 'icon-[tabler--ban]' : 'icon-[tabler--check]' }}"></i>
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        @else
                            <span class="text-base-content/80 text-xs">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-10 text-center text-base-content/80">
                        <i class="icon-[tabler--user] text-3xl mb-2 block"></i>
                        No users found.
                    </td>
                </tr>
            @endforelse
        </x-data-table>

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
                <div class="card bg-base-100 border border-base-300 p-4 mb-3">
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
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-primary-content text-sm font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="font-semibold text-base-content text-sm flex items-center gap-1">
                                    @if($user->employee)
                                        <a href="{{ route('employees.show', $user->employee) }}" class="text-base-content no-underline font-semibold">{{ $user->name }}</a>
                                    @else
                                        {{ $user->name }}
                                    @endif
                                    @if($user->id === auth()->id())
                                        <span class="badge badge-soft badge-success text-[10px] px-2 py-0 normal-case">You</span>
                                    @endif
                                </div>
                                <div class="text-xs text-base-content/60">{{ $user->email }}</div>
                            </div>
                        </div>
                        @if($user->is_active)
                            <span class="badge badge-soft badge-success whitespace-nowrap"><i class="icon-[tabler--circle-check]"></i> Active</span>
                        @else
                            <span class="badge badge-soft badge-error whitespace-nowrap"><i class="icon-[tabler--circle-x]"></i> Inactive</span>
                        @endif
                    </div>

                    <div class="flex justify-between items-center flex-wrap gap-2 pt-3 border-t border-base-300">
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

                        <span class="text-xs text-base-content/80">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never logged in' }}
                        </span>

                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle', $user) }}"
                                  data-confirm="{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}"
                                  data-confirm-title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                  data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                  data-confirm-btn="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}">
                                @csrf @method('PATCH')
                                <button class="btn  btn-xs {{ $user->is_active ? 'btn-error' : 'btn-success' }}">
                                    <i class="{{ $user->is_active ? 'icon-[tabler--ban]' : 'icon-[tabler--check]' }}"></i>
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        @else
                            <span class="text-base-content/80 text-xs">—</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-base-content/80">
                    <i class="icon-[tabler--user] text-3xl mb-2 block"></i>
                    No users found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-base-300">
            {{ $users->links('vendor.pagination.pagination') }}
        </div>
    </x-table-card>

@endsection
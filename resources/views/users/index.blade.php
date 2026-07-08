@extends('layouts.app')

@section('title', 'All Users')
@section('breadcrumb')
    <span>Manage Users</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-medium">Users</span>
@endsection

@section('content')



    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-red-600 bg-red-100">
                                <i class="icon-[ph--users-fill]"></i>

            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\User::count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Total Users</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-red-800 bg-red-100">
                                <i class="icon-[ph--shield-check-fill]"></i>

            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\User::where('role','admin')->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Admins</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-amber-600 bg-amber-100">
                <i class="icon-[ph--user-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\User::where('role','hr')->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">HR Personnel</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-emerald-600 bg-emerald-100">
                <i class="icon-[ph--check-circle-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\User::where('is_active', true)->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Active Accounts</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card bg-base-100 shadow-sm overflow-hidden flex flex-col p-0">

 {{-- NEW --}}
    <div class="sticky top-0 z-10 bg-white px-7 pt-5 rounded-t-2xl">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 flex items-center gap-2 m-0">
               <x-dot-loader /> User Accounts


                               <div class="tooltip [--placement:right]">
    <span class="tooltip-toggle cursor-pointer text-gray-400 hover:text-gray-600" aria-label="More info">
        <i class="icon-[ph--info-fill]"></i>
    </span>
    <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
        <span class="tooltip-body  bg-success/67 shadow-md rounded-lg px-3 py-2 text-xs normal-case">
           Manage system accounts, roles, and access.
        </span>
    </span>
</div>
            </h2>
            
        </div>

     <form id="filter-form" method="GET" action="{{ route('users.index') }}"
      class="flex flex-col md:flex-row md:items-center gap-3 pb-4">

    {{-- Search group --}}
    <div class="join flex-none w-64 min-w-40">
        <input type="text" name="search" id="search-input" value="{{ request('search') }}"
               placeholder="Search name or email..."
               oninput="clearTimeout(this._t); this._t = setTimeout(() => this.closest('form').submit(), 400)"
               class="input input-bordered input-sm join-item w-full border-gray-300">
               
        <button type="submit" class="btn btn-outline btn-sm join-item border-gray-300">
            <i class="icon-[ph--magnifying-glass-fill]"></i>
        </button>
        
    </div>

    

    {{-- Filters group --}}
    <div class="flex flex-row gap-2 md:ml-auto">
        <select name="role" id="role-select" 
        onchange="this.closest('form').submit()"
        class="select select-bordered select-sm">
             <option value="">All Roles</option>
                    <option value="admin"    {{ request('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                    <option value="hr"       {{ request('role') === 'hr'       ? 'selected' : '' }}>HR</option>
                    <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
        </select>
        <select name="status" id="status-select"
        onchange="this.closest('form').submit()" 
        class="select select-bordered select-sm">
         
             <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
@if(request()->hasAny(['search','role','status']))
<a href="{{ route('users.index') }}" class="btn btn-soft btn-sm">Clear</a>
        @endif
    </div>
</form>
   
</div>









        {{-- Desktop Table --}}
        <div class="table-responsive overflow-y-auto max-h-[53vh] hidden md:block table-borderless">
            <table class="table table-hover w-full text-sm">
               <thead class="sticky top-0 z-5" style="background: white text-xs">
                   <tr class="bg-success/67 shadow-md text-white">
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
                               class="inline-flex items-center gap-1 no-underline uppercase tracking-wider {{ $loginActive ? 'text-white' : 'text-white' }}">
                                Last Login
                                <span class="inline-flex flex-col leading-none">
                                    <i class="icon-[ph--caret-up-fill] text-[9px] {{ ($loginActive && request('direction') === 'asc') ? 'text-red-600' : 'text-white' }}"></i>
                                    <i class="icon-[ph--caret-down-fill] text-[9px] {{ ($loginActive && request('direction') === 'desc') ? 'text-red-600' : 'text-white' }}"></i>
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
                        <tr class="row-hover">
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
                                    <span class="badge badge-soft badge-success"><i class="icon-[ph--check-circle-fill]"></i> Active</span>
                                @else
                                    <span class="badge badge-soft badge-error"><i class="icon-[ph--x-circle-fill]"></i> Inactive</span>
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
                                <i class="icon-[ph--user-fill] text-3xl mb-2 block"></i>
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
                            <span class="badge badge-soft badge-success whitespace-nowrap"><i class="icon-[ph--check-circle-fill]"></i> Active</span>
                        @else
                            <span class="badge badge-soft badge-error whitespace-nowrap"><i class="icon-[ph--x-circle-fill]"></i> Inactive</span>
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
                    <i class="icon-[ph--user-fill] text-3xl mb-2 block"></i>
                    No users found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-200">
    {{ $users->links('vendor.pagination.pagination') }}
</div>

    </div>

@endsection
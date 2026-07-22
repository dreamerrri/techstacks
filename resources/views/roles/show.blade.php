@extends('layouts.app')

@section('title', 'Role Details')


@section('content')

    <div class="mb-5">
        <a href="{{ route('roles.index') }}" class="back-link text-base-content/60 no-underline text-sm hover:text-emerald-600">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Roles
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <i class="icon-[tabler--circle-check]"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">
            <i class="icon-[tabler--circle-x]"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                    {{ strtoupper(substr($role->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-base-content m-0">{{ $role->name }}</h2>
                    <code class="text-xs text-base-content/60 bg-gray-100 px-2 py-0.5 rounded">{{ $role->slug }}</code>
                </div>
            </div>
            <a href="{{ route('roles.edit', $role) }}" class="btn  btn-error btn-sm">
                <i class="icon-[ph--pencil-fill]"></i> Edit Role
            </a>
        </div>

        {{-- Role Information --}}
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-red-200 pb-2 mb-4">
                <i class="icon-[tabler--user] text-red-600"></i> Role Information
            </h3>
            <div class="flex flex-col">
                <div class="flex justify-between items-center py-3 border-b border-base-200">
                    <span class="text-base-content/40 font-medium">Description</span>
                    <span class="text-base-content/80 text-right">{{ $role->description ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-base-200">
                    <span class="text-base-content/40 font-medium">Status</span>
                    <span>
                        @if($role->is_active)
                            <span class="badge badge-soft badge-success"><i class="icon-[tabler--circle-check]"></i> Active</span>
                        @else
                            <span class="badge badge-soft badge-error"><i class="icon-[tabler--circle-x]"></i> Inactive</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-base-200">
                    <span class="text-base-content/40 font-medium">Total Users</span>
                    <span class="font-semibold text-base-content">{{ $role->users->count() }}</span>
                </div>
                <div class="flex justify-between items-center py-3">
                    <span class="text-base-content/40 font-medium">Total Permissions</span>
                    <span class="font-semibold text-base-content">{{ $role->permissions->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-red-200 pb-2 mb-4">
                <i class="icon-[ph--key-fill] text-red-600"></i> Permissions ({{ $role->permissions->count() }})
            </h3>
            @if($role->permissions->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($role->permissions->groupBy('module') as $module => $modulePerms)
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                            <div class="text-xs font-bold text-base-content/60 uppercase tracking-widest mb-3">
                                {{ ucfirst($module) }}
                            </div>
                            @foreach($modulePerms as $permission)
                                <div class="flex items-center gap-2 text-xs text-base-content/80 mb-1.5">
                                    <i class="icon-[tabler--circle-check] text-emerald-500 text-[11px] flex-shrink-0"></i>
                                    {{ $permission->name }}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-base-content/40 text-sm m-0">No permissions assigned to this role.</p>
            @endif
        </div>

        {{-- Assigned Users --}}
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-red-200 pb-2 mb-4">
                <i class="icon-[tabler--user] text-red-600"></i> Assigned Users ({{ $role->users->count() }})
            </h3>

            @if($availableUsers->count() > 0)
                <form method="POST" action="{{ route('roles.assign.user', $role) }}" class="flex gap-2 items-center flex-wrap mb-5">
                    @csrf
                    <select name="user_id" required class="select select-bordered  select-sm flex-1 min-w-48">
                        <option value="">Select a user to assign...</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn  btn-error btn-sm">
                        <i class="icon-[ph--user-plus-fill]"></i> Assign User
                    </button>
                </form>
            @endif

            @if($role->users->count() > 0)
                <div class="flex flex-col gap-2">
                    @foreach($role->users as $user)
                        <div class="flex justify-between items-center p-3 border border-gray-200 rounded-xl hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white font-bold flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-base-content text-sm">{{ $user->name }}</div>
                                    <div class="text-base-content/60 text-xs">{{ $user->email }}</div>
                                </div>
                            </div>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('roles.remove.user', [$role, $user]) }}"
                                      data-confirm="This user will be removed from the {{ $role->name }} role."
                                      data-confirm-title="Remove User?"
                                      data-confirm-icon="warning"
                                      data-confirm-btn="Yes, remove">
                                    @csrf @method('DELETE')
                                    <button class="btn  btn-error btn-sm">
                                        <i class="icon-[ph--user-minus-fill]"></i> Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-base-content/40 text-sm m-0">No users assigned to this role.</p>
            @endif
        </div>

    </div>

@endsection
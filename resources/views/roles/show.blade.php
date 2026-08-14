@extends('layouts.app')

@section('title', 'Role Details')

@section('content')

    <div class="mb-5">
        <a href="{{ route('roles.index') }}" class="back-link text-subtle no-underline text-sm hover:text-success">
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
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                    {{ strtoupper(substr($role->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-base-content m-0">{{ $role->name }}</h2>
                    <code class="text-xs text-subtle bg-base-200 px-2 py-0.5 rounded">{{ $role->slug }}</code>
                </div>
            </div>
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-soft btn-error btn-sm">
                <i class="icon-[tabler--pencil]"></i> Edit Role
            </a>
        </div>

        {{-- Role Information --}}
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-faint border-b-2 border-error/20 pb-2 mb-4">
                <i class="icon-[tabler--user] text-error"></i> Role Information
            </h3>
            <div class="flex flex-col">
                <x-detail-row label="Description">
                    <span class="text-muted">{{ $role->description ?? '—' }}</span>
                </x-detail-row>

                <x-detail-row label="Status">
                    @if($role->is_active)
                        <span class="badge badge-soft badge-success"><i class="icon-[tabler--circle-check]"></i> Active</span>
                    @else
                        <span class="badge badge-soft badge-error"><i class="icon-[tabler--circle-x]"></i> Inactive</span>
                    @endif
                </x-detail-row>

                <x-detail-row label="Total Users">
                    {{ $role->users->count() }}
                </x-detail-row>

                <x-detail-row label="Total Permissions" :border="false">
                    {{ $role->permissions->count() }}
                </x-detail-row>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-faint border-b-2 border-error/20 pb-2 mb-4">
                <i class="icon-[ph--key-fill] text-error"></i> Permissions ({{ $role->permissions->count() }})
            </h3>
            @if($role->permissions->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($role->permissions->groupBy('module') as $module => $modulePerms)
                        <div class="bg-base-200 border border-base-300 rounded-xl p-4">
                            <div class="text-xs font-bold text-subtle uppercase tracking-widest mb-3">
                                {{ ucfirst($module) }}
                            </div>
                            @foreach($modulePerms as $permission)
                                <div class="flex items-center gap-2 text-xs text-muted mb-1.5">
                                    <i class="icon-[tabler--circle-check] text-success text-[11px] flex-shrink-0"></i>
                                    {{ $permission->name }}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-faint text-sm m-0">No permissions assigned to this role.</p>
            @endif
        </div>

        {{-- Assigned Users --}}
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-widest text-faint border-b-2 border-error/20 pb-2 mb-4">
                <i class="icon-[tabler--user] text-error"></i> Assigned Users ({{ $role->users->count() }})
            </h3>

            @if($availableUsers->count() > 0)
                <form method="POST" action="{{ route('roles.assign.user', $role) }}" class="flex gap-2 items-center flex-wrap mb-5">
                    @csrf
                    <select name="user_id" required class="select select-bordered select-sm flex-1 min-w-48">
                        <option value="">Select a user to assign...</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-soft btn-error btn-sm">
                        <i class="icon-[tabler--user-plus]"></i> Assign User
                    </button>
                </form>
            @endif

            @if($role->users->count() > 0)
                <div class="flex flex-col gap-2">
                    @foreach($role->users as $user)
                        <div class="flex justify-between items-center p-3 border border-base-300 rounded-xl hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white font-bold flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-base-content text-sm">{{ $user->name }}</div>
                                    <div class="text-subtle text-xs">{{ $user->email }}</div>
                                </div>
                            </div>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('roles.remove.user', [$role, $user]) }}"
                                      data-confirm="This user will be removed from the {{ $role->name }} role."
                                      data-confirm-title="Remove User?"
                                      data-confirm-icon="warning"
                                      data-confirm-btn="Yes, remove">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-soft btn-error btn-sm">
                                        <i class="icon-[tabler--user-minus]"></i> Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-faint text-sm m-0">No users assigned to this role.</p>
            @endif
        </div>

    </div>

@endsection
@extends('layouts.app')

@section('title', 'Permission Details')


@section('content')

    <div class="mb-5">
        <a href="{{ route('permissions.index') }}" class="back-link text-base-content/60 no-underline text-sm hover:text-success">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Permissions
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <i class="icon-[tabler--circle-check]"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white text-xl flex-shrink-0">
                    <i class="icon-[ph--key-fill]"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-base-content m-0">{{ $permission->name }}</h2>
                    <code class="text-xs text-base-content/60 bg-base-200 px-2 py-0.5 rounded">{{ $permission->slug }}</code>
                </div>
            </div>
            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-soft btn-error btn-sm">
                <i class="icon-[ph--pencil-fill]"></i> Edit Permission
            </a>
        </div>

        {{-- Permission Information --}}
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-error/20 pb-2 mb-4">
                <i class="icon-[ph--info-fill] text-error"></i> Permission Information
            </h3>
            <div class="flex flex-col">
                <div class="flex justify-between items-center py-3 border-b border-base-200">
                    <span class="text-base-content/40 font-medium">Module</span>
                    <span class="font-semibold text-base-content">{{ ucfirst($permission->module) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-base-200">
                    <span class="text-base-content/40 font-medium">Status</span>
                    <span>
                        @if($permission->is_active)
                            <span class="badge badge-soft badge-success"><i class="icon-[tabler--circle-check]"></i> Active</span>
                        @else
                            <span class="badge badge-soft badge-error"><i class="icon-[tabler--circle-x]"></i> Inactive</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center py-3 {{ $permission->description ? 'border-b border-base-200' : '' }}">
                    <span class="text-base-content/40 font-medium">Assigned to Roles</span>
                    <span class="font-semibold text-base-content">{{ $permission->roles->count() }}</span>
                </div>
                @if($permission->description)
                    <div class="flex flex-col gap-1 py-3">
                        <span class="text-base-content/40 font-medium">Description</span>
                        <span class="text-base-content/80 text-sm">{{ $permission->description }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Roles with this Permission --}}
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-error/20 pb-2 mb-4">
                <i class="icon-[tabler--user]-tag text-error"></i> Roles with this Permission ({{ $permission->roles->count() }})
            </h3>

            @if($permission->roles->count() > 0)
                <div class="flex flex-col gap-2">
                    @foreach($permission->roles as $role)
                        <div class="flex justify-between items-center p-3 border border-base-300 rounded-xl hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white font-bold flex-shrink-0">
                                    {{ strtoupper(substr($role->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-base-content text-sm">{{ $role->name }}</div>
                                    <code class="text-xs text-base-content/60">{{ $role->slug }}</code>
                                </div>
                            </div>
                            <a href="{{ route('roles.show', $role) }}" class="btn btn-soft btn-info btn-sm">
                                <i class="icon-[ph--eye-fill]"></i> View Role
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-base-content/40 text-sm m-0">No roles have this permission.</p>
            @endif
        </div>

    </div>

@endsection
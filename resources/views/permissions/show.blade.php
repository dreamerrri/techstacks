@extends('layouts.app')

@section('title', 'Permission Details')
@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="text-white/55 no-underline hover:text-white">Manage Users</a>
    <i class="fas fa-chevron-right text-xs"></i>
    <a href="{{ route('permissions.index') }}" class="text-white/55 no-underline hover:text-white">Permissions</a>
    <i class="fas fa-chevron-right text-xs"></i>
    <span class="text-white font-semibold">{{ $permission->name }}</span>
@endsection

@section('content')

    <div class="mb-5">
        <a href="{{ route('permissions.index') }}" class="text-gray-500 no-underline text-sm hover:text-emerald-600">
            <i class="fas fa-arrow-left"></i> Back to Permissions
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-xl flex-shrink-0">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 m-0">{{ $permission->name }}</h2>
                    <code class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $permission->slug }}</code>
                </div>
            </div>
            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-soft btn-error btn-sm">
                <i class="fas fa-edit"></i> Edit Permission
            </a>
        </div>

        {{-- Permission Information --}}
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 border-b-2 border-red-200 pb-2 mb-4">
                <i class="fas fa-info-circle text-red-600"></i> Permission Information
            </h3>
            <div class="flex flex-col">
                <div class="flex justify-between items-center py-3 border-b border-base-200">
                    <span class="text-gray-400 font-medium">Module</span>
                    <span class="font-semibold text-gray-800">{{ ucfirst($permission->module) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-base-200">
                    <span class="text-gray-400 font-medium">Status</span>
                    <span>
                        @if($permission->is_active)
                            <span class="badge badge-soft badge-success"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge badge-soft badge-error"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center py-3 {{ $permission->description ? 'border-b border-base-200' : '' }}">
                    <span class="text-gray-400 font-medium">Assigned to Roles</span>
                    <span class="font-semibold text-gray-800">{{ $permission->roles->count() }}</span>
                </div>
                @if($permission->description)
                    <div class="flex flex-col gap-1 py-3">
                        <span class="text-gray-400 font-medium">Description</span>
                        <span class="text-gray-700 text-sm">{{ $permission->description }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Roles with this Permission --}}
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 border-b-2 border-red-200 pb-2 mb-4">
                <i class="fas fa-user-tag text-red-600"></i> Roles with this Permission ({{ $permission->roles->count() }})
            </h3>

            @if($permission->roles->count() > 0)
                <div class="flex flex-col gap-2">
                    @foreach($permission->roles as $role)
                        <div class="flex justify-between items-center p-3 border border-gray-200 rounded-xl hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white font-bold flex-shrink-0">
                                    {{ strtoupper(substr($role->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 text-sm">{{ $role->name }}</div>
                                    <code class="text-xs text-gray-500">{{ $role->slug }}</code>
                                </div>
                            </div>
                            <a href="{{ route('roles.show', $role) }}" class="btn btn-soft btn-info btn-sm">
                                <i class="fas fa-eye"></i> View Role
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-sm m-0">No roles have this permission.</p>
            @endif
        </div>

    </div>

@endsection
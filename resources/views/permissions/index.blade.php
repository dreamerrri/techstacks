@extends('layouts.app')

@section('title', 'Permissions Management')
@section('breadcrumb')
    <span>Manage Users</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-medium">Permissions</span>
@endsection

@section('content')

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
        <h2 class="text-xl font-bold text-gray-800 m-0">Permissions Management</h2>
        <a href="{{ route('permissions.create') }}" class="btn btn-soft btn-error btn-sm">
            <i class="icon-[ph--plus-fill]"></i> Create Permission
        </a>

        
    </div>

    @foreach($permissions as $module => $modulePermissions)
        <div class="mb-6">

            {{-- Module header --}}
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-linear-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr($module, 0, 1)) }}
                </div>
                <h3 class="text-base font-bold text-gray-800 m-0">{{ ucfirst($module) }}</h3>
                <span class="badge badge-soft badge-success text-xs normal-case tracking-normal">
                    {{ $modulePermissions->count() }}
                </span>
            </div>

            <div class="card bg-base-100 shadow-sm overflow-hidden p-0">

                {{-- Desktop Table --}}
                <div class="table-responsive hidden md:block">
                    <table class="table table-hover w-full text-sm">
                        <thead>
                           <tr class="bg-success/67 text-white">
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Roles</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modulePermissions as $permission)
                                <tr class="row-hover">
                                    <td class="font-semibold text-gray-800">{{ $permission->name }}</td>
                                    <td><code class="bg-gray-100 text-red-600 text-xs px-1.5 py-0.5 rounded">{{ $permission->slug }}</code></td>
                                    <td class="text-gray-500">{{ $permission->description ?? '—' }}</td>
                                    <td class="text-gray-500">{{ $permission->roles->count() }}</td>
                                    <td>
                                        @if($permission->is_active)
                                            <span class="badge badge-soft badge-success"><i class="icon-[ph--check-circle-fill]"></i> Active</span>
                                        @else
                                            <span class="badge badge-soft badge-error"><i class="icon-[ph--x-circle-fill]"></i> Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex gap-2 items-center">
                                            <a href="{{ route('permissions.show', $permission) }}" class="btn btn-soft btn-info btn-sm">
                                                <i class="icon-[ph--eye-fill]"></i>
                                            </a>
                                            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-soft btn-warning btn-sm">
                                                <i class="icon-[ph--pencil-fill]"></i>
                                            </a>
                                            @if($permission->roles->count() == 0)
                                                <form method="POST" action="{{ route('permissions.destroy', $permission) }}"
                                                      data-confirm="This permission will be permanently deleted."
                                                      data-confirm-title="Delete Permission?"
                                                      data-confirm-icon="warning"
                                                      data-confirm-btn="Yes, delete">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-soft btn-error btn-sm">
                                                        <i class="icon-[ph--trash-fill]"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden p-4 flex flex-col gap-3">
                    @foreach($modulePermissions as $permission)
                        <div class="card bg-base-100 border border-gray-200 p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white flex-shrink-0">
                                        <i class="icon-[ph--key-fill] text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-800 text-sm">{{ $permission->name }}</div>
                                        <code class="text-xs text-gray-500">{{ $permission->slug }}</code>
                                    </div>
                                </div>
                                @if($permission->is_active)
                                    <span class="badge badge-soft badge-success whitespace-nowrap"><i class="icon-[ph--check-circle-fill]"></i> Active</span>
                                @else
                                    <span class="badge badge-soft badge-error whitespace-nowrap"><i class="icon-[ph--x-circle-fill]"></i> Inactive</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mt-2">
                                <span><i class="icon-[ph--user-tag-fill] w-3.5"></i>{{ $permission->roles->count() }} roles</span>
                            </div>

                            @if($permission->description)
                                <div class="text-xs text-gray-400 mt-1">{{ $permission->description }}</div>
                            @endif

                            <div class="flex gap-2 flex-wrap mt-3 pt-3 border-t border-gray-100">
                                <a href="{{ route('permissions.show', $permission) }}" class="btn btn-soft btn-info btn-sm">
                                    <i class="icon-[ph--eye-fill]"></i> View
                                </a>
                                <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-soft btn-warning btn-sm">
                                    <i class="icon-[ph--pencil-fill]"></i> Edit
                                </a>
                                @if($permission->roles->count() == 0)
                                    <form method="POST" action="{{ route('permissions.destroy', $permission) }}"
                                          data-confirm="This permission will be permanently deleted."
                                          data-confirm-title="Delete Permission?"
                                          data-confirm-icon="warning"
                                          data-confirm-btn="Yes, delete">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-soft btn-error btn-sm">
                                            <i class="icon-[ph--trash-fill]"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    @endforeach

@endsection
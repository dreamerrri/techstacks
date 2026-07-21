@extends('layouts.app')

@section('title', 'Permissions Management')


@section('content')

<x-table-card>
    <x-slot:title>
        <x-dot-loader /> Permissions Management
        <x-info-tooltip>
            Manage all system permissions and assigned roles.
        </x-info-tooltip>
    </x-slot:title>

    <x-slot:actions>
        <a href="{{ route('permissions.create') }}" class="btn btn-soft btn-error btn-sm">
            <i class="icon-[ph--plus-fill]"></i> Create Permission
        </a>
    </x-slot:actions>

    @foreach($permissions as $module => $modulePermissions)
        <div class="mb-6 px-6 pt-4">

            {{-- Module header --}}
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-linear-to-br bg-warning flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr($module, 0, 1)) }}
                </div>
                <h3 class="text-base font-bold text-base-content m-0">{{ ucfirst($module) }}</h3>
                <span class="badge badge-soft badge-success text-xs normal-case tracking-normal">
                    {{ $modulePermissions->count() }}
                </span>
            </div>

            {{-- Desktop Table --}}
            <x-data-table>
                <x-slot:head>
                    <th class="w-60">Name</th>
                    <th class="w-60">Slug</th>
                    <th>Description</th>
                    <th class="w-20 text-right">Roles</th>
                    <th class="w-40 text-right">Status</th>
                    <th class="w-40 text-right">Actions</th>
                </x-slot:head>

                @forelse($modulePermissions as $permission)
                    <tr class="row-hover">
                        <td class="font-semibold text-base-content">{{ $permission->name }}</td>
                        <td><code class="bg-gray-100 text-red-600 text-xs px-1.5 py-0.5 rounded">{{ $permission->slug }}</code></td>
                        <td class="text-base-content/60">
                            <span class="truncate block" title="{{ $permission->description }}">{{ $permission->description ?? '—' }}</span>
                        </td>
                        <td class="text-base-content/60 text-right">{{ $permission->roles->count() }}</td>
                        <td class="text-right">
                            @if($permission->is_active)
                                <span class="badge badge-soft badge-success"><i class="icon-[tabler--circle-check]"></i> Active</span>
                            @else
                                <span class="badge badge-soft badge-error"><i class="icon-[tabler--circle-x]"></i> Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex gap-2 items-center justify-end">
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
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-base-content/40">
                            <div class="flex flex-col items-center">
                                <i class="icon-[ph--tray-fill] text-3xl mb-2"></i>
                                <span>No data found.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-data-table>

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
                                    <div class="font-semibold text-base-content text-sm">{{ $permission->name }}</div>
                                    <code class="text-xs text-base-content/60">{{ $permission->slug }}</code>
                                </div>
                            </div>
                            @if($permission->is_active)
                                <span class="badge badge-soft badge-success whitespace-nowrap"><i class="icon-[tabler--circle-check]"></i> Active</span>
                            @else
                                <span class="badge badge-soft badge-error whitespace-nowrap"><i class="icon-[tabler--circle-x]"></i> Inactive</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                            <span><i class="icon-[tabler--user] w-3.5"></i>{{ $permission->roles->count() }} roles</span>
                        </div>

                        @if($permission->description)
                            <div class="text-xs text-base-content/40 mt-1">{{ $permission->description }}</div>
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
    @endforeach
</x-table-card>

@endsection 
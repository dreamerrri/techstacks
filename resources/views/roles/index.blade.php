@extends('layouts.app')

@section('title', 'Roles Management')


@section('content')


        {{-- Desktop Table --}}
    
<x-table-card>
 <x-slot:title>
        <x-dot-loader /> Roles Management
        <x-info-tooltip>
            Manage all system roles and their assigned permissions.
        </x-info-tooltip>
    </x-slot:title>

    <x-slot:actions>
        <a href="{{ route('roles.create') }}" class="btn btn-soft btn-error btn-sm">
            <i class="icon-[ph--plus-fill]"></i> Create Role
        </a>
    </x-slot:actions>


<x-data-table>
    <x-slot:head>
        <th class="w-40">Name</th>
        <th class="w-24">Slug</th>
        <th>Description</th>
        <th class="w-40 text-right">Users</th>
        <th class="w-40 text-right">Permissions</th>
        <th class="w-40 text-right">Status</th>
        <th class="w-40 text-right">Actions</th>
    </x-slot:head>

    @forelse($roles as $role)
        <tr class="row-hover">
            <td class="font-semibold text-gray-800">{{ $role->name }}</td>
            <td><code class="bg-gray-100 text-red-600 text-xs px-1.5 py-0.5 rounded">{{ $role->slug }}</code></td>
            <td class="text-gray-500 truncate">{{ $role->description ?? '—' }}</td>
            <td class="text-gray-500 text-right">{{ $role->users_count }}</td>
            <td class="text-gray-500 text-right">{{ $role->permissions->count() }}</td>
            <td class="text-right">
                @if($role->is_active)
                    <span class="badge badge-soft badge-success"><i class="icon-[ph--check-circle-fill]"></i> Active</span>
                @else
                    <span class="badge badge-soft badge-error"><i class="icon-[ph--x-circle-fill]"></i> Inactive</span>
                @endif
            </td>
            <td class="text-right">
                <div class="flex gap-2 items-center justify-end">
                    <a href="{{ route('roles.show', $role) }}" class="btn btn-soft btn-info btn-sm">
                        <i class="icon-[ph--eye-fill]"></i>
                    </a>
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-soft btn-warning btn-sm">
                        <i class="icon-[ph--pencil-fill]"></i>
                    </a>
                    @if($role->users_count == 0)
                        <form method="POST" action="{{ route('roles.destroy', $role) }}"
                              data-confirm="This role will be permanently deleted."
                              data-confirm-title="Delete Role?"
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
            <td colspan="7" class="py-10 text-gray-400">
                <div class="flex flex-col items-center">
                    <i class="icon-[ph--tray-fill] text-3xl mb-2"></i>
                    <span>No data found.</span>
                </div>
            </td>
        </tr>
    @endforelse
</x-data-table>
</x-table-card>


        {{-- Mobile Cards --}}
        <div class="md:hidden p-4 flex flex-col gap-3">
            @forelse($roles as $role)
                <div class="card bg-base-100 border border-gray-200 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                {{ strtoupper(substr($role->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800 text-sm">{{ $role->name }}</div>
                                <code class="text-xs text-gray-500">{{ $role->slug }}</code>
                            </div>
                        </div>
                        @if($role->is_active)
                            <span class="badge badge-soft badge-success whitespace-nowrap"><i class="icon-[ph--check-circle-fill]"></i> Active</span>
                        @else
                            <span class="badge badge-soft badge-error whitespace-nowrap"><i class="icon-[ph--x-circle-fill]"></i> Inactive</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mt-2">
                        <span><i class="icon-[ph--user-fill]s w-3.5"></i> {{ $role->users_count }} users</span>
                        <span><i class="icon-[ph--key-fill] w-3.5"></i> {{ $role->permissions->count() }} permissions</span>
                    </div>

                    @if($role->description)
                        <div class="text-xs text-gray-400 mt-1">{{ $role->description }}</div>
                    @endif

                    <div class="flex gap-2 flex-wrap mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('roles.show', $role) }}" class="btn btn-soft btn-info btn-sm">
                            <i class="icon-[ph--eye-fill]"></i> View
                        </a>
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-soft btn-warning btn-sm">
                            <i class="icon-[ph--pencil-fill]"></i> Edit
                        </a>
                        @if($role->users_count == 0)
                            <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                  data-confirm="This role will be permanently deleted."
                                  data-confirm-title="Delete Role?"
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
            @empty
                <div class="py-10 text-center text-gray-400">
                    <i class="icon-[ph--user-fill]-tag text-3xl mb-2 block"></i>
                    No roles found.
                </div>
            @endforelse
        </div>

    </div>

@endsection


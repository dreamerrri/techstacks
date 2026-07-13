@extends('layouts.app')

@section('title', 'Audit Logs')


@section('content')
<x-table-card action="{{ route('audit-logs.index') }}">
    <x-slot:title>
        <x-dot-loader /> Audit Logs
         
        <x-info-tooltip>
            Track and review all system activity and changes.
        </x-info-tooltip>
    </x-slot:title>

    <x-slot:filters>
     <div class="flex flex-wrap items-end gap-2">
            <div>
                <label class="label text-xs font-semibold uppercase tracking-wider text-gray-400">Module</label>
                <select name="module" class="select select-bordered select-sm w-full">
                    <option value="">All Modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ ucfirst($module) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fieldset">
                <label class="label text-xs font-semibold uppercase tracking-wider text-gray-400">Action</label>
                <select name="action" class="select select-bordered select-sm w-full">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fieldset">
                <label class="label text-xs font-semibold uppercase tracking-wider text-gray-400">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="input input-bordered input-sm w-full">
            </div>
            <div class="fieldset">
                <label class="label text-xs font-semibold uppercase tracking-wider text-gray-400">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="input input-bordered input-sm w-full">
            </div>
            <div>
                <button type="submit" class="btn btn-soft btn-error btn-sm">
                    <i class="icon-[ph--funnel-fill]"></i> Filter
                </button>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-soft btn-sm">Clear</a>
            </div>
        </div>
    </x-slot:filters>

    {{-- Desktop Table --}}
    <x-data-table>
        <x-slot:head>
            <th>Date/Time</th>
            <th>User</th>
            <th>Action</th>
            <th>Module</th>
            <th>Description</th>
            <th>IP Address</th>
            <th>Actions</th>
        </x-slot:head>

        @forelse($logs as $log)
            @php
                $actionClass = match(strtolower($log->action)) {
                    'create' => 'badge-soft badge-success text-xs',
                    'update' => 'badge-soft badge-warning text-xs',
                    'delete' => 'badge-soft badge-error text-xs',
                    'login'  => 'badge-soft badge-info text-xs',
                    'logout' => 'badge-soft badge-neutral text-xs',
                    default  => 'badge-soft text-xs',
                };
            @endphp
            <tr class="row-hover">
                <td class="text-gray-500 text-xs whitespace-nowrap">
                    {{ $log->created_at->format('M d, Y') }}<br>
                    {{ $log->created_at->format('h:i:s A') }}
                </td>
                <td class="font-semibold text-gray-800">{{ $log->user?->name ?? '—' }}</td>
                <td>
                    <span class="badge {{ $actionClass }}">{{ ucfirst($log->action) }}</span>
                </td>
                <td class="text-gray-500">{{ ucfirst($log->module) }}</td>
                <td class="text-gray-500 max-w-[200px] truncate">{{ $log->description }}</td>
                <td class="text-gray-500 font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                <td>
                    <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-soft btn-info btn-sm">
                        <i class="icon-[ph--eye-fill]"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="py-10 text-center text-gray-400">
                    <i class="icon-[ph--clock-counter-clockwise-fill] text-3xl mb-2 block"></i>
                    No audit logs found.
                </td>
            </tr>
        @endforelse
    </x-data-table>

    {{-- Mobile Cards --}}
    <div class="md:hidden p-4 flex flex-col gap-3">
        @forelse($logs as $log)
            @php
                $actionClass = match(strtolower($log->action)) {
                    'create' => 'badge-soft badge-success',
                    'update' => 'badge-soft badge-warning',
                    'delete' => 'badge-soft badge-error',
                    'login'  => 'badge-soft badge-info',
                    'logout' => 'badge-soft badge-neutral',
                    default  => 'badge-soft',
                };
            @endphp
            <div class="card bg-base-100 border border-gray-200 p-4">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-500 to-gray-700 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800 text-sm">{{ $log->user?->name ?? 'System' }}</div>
                            <div class="text-xs text-gray-400">{{ $log->created_at->format('M d, Y h:i:s A') }}</div>
                        </div>
                    </div>
                    <span class="badge {{ $actionClass }} whitespace-nowrap">{{ ucfirst($log->action) }}</span>
                </div>

                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mt-2">
                    <span><i class="icon-[ph--cube-fill] w-3.5"></i> {{ ucfirst($log->module) }}</span>
                    <span><i class="icon-[ph--graph-fill] w-3.5"></i> {{ $log->ip_address ?? '—' }}</span>
                </div>

                <div class="text-xs text-gray-400 mt-1">{{ $log->description }}</div>

                <div class="mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-soft btn-info btn-sm">
                        <i class="icon-[ph--eye-fill]"></i> View Details
                    </a>
                </div>
            </div>
        @empty
            <div class="py-10 text-center text-gray-400">
                <i class="icon-[ph--clock-counter-clockwise-fill] text-3xl mb-2 block"></i>
                No audit logs found.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $logs->links('vendor.pagination.pagination') }}
    </div>
</x-table-card>
@endsection

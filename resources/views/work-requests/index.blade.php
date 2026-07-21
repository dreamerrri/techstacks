@extends('layouts.app')

@section('title', 'Work Requests')


@section('content')

@php
    $user = auth()->user();
    $admin = $user->isAdmin();
    $hr = $user->isHR();
    $color = $admin ? '#dc2626' : ($hr ? '#2563eb' : '#667eea');
    $colorDark = $admin ? '#991b1b' : ($hr ? '#1e40af' : '#764ba2');
@endphp

<x-table-card action="{{ route('work-requests.index') }}">
    <x-slot:title>
        <x-dot-loader /> Work Requests
        <x-info-tooltip>
            {{ $admin || $hr ? 'Manage employee work requests' : 'View and manage your work requests' }}
        </x-info-tooltip>
    </x-slot:title>

    <x-slot:actions>
        <div class="flex gap-2">
            @if($admin || $hr)
                @if($pendingCount > 0)
                    <a href="{{ route('work-requests.pending') }}"
                       class="px-5 py-3 bg-amber-500 text-white border-none rounded-field cursor-pointer text-sm font-semibold no-underline inline-flex items-center gap-2">
                        <i class="icon-[ph--clock-fill]"></i> Pending ({{ $pendingCount }})
                    </a>
                @endif
            @else
                <a href="{{ route('work-requests.create') }}"
                   class="px-5 py-3 text-white border-none rounded-field cursor-pointer text-sm font-semibold no-underline inline-flex items-center gap-2" style="background:{{ $color }};">
                    <i class="icon-[ph--plus-fill]"></i> New Request
                </a>
            @endif
        </div>
    </x-slot:actions>

    {{-- Filters --}}
    <x-slot:filters>
        <div>
            <label class="text-xs text-base-content/60 mb-1 block">Status</label>
            <select name="status" class="p-2 border border-base-300 rounded-field text-sm min-w-[150px]">
                <option value="">All</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <label class="text-xs text-base-content/60 mb-1 block">Type</label>
            <select name="type" class="p-2 border border-base-300 rounded-field text-sm min-w-[150px]">
                <option value="">All</option>
                <option value="weekend" {{ $type === 'weekend' ? 'selected' : '' }}>Weekend</option>
                <option value="holiday" {{ $type === 'holiday' ? 'selected' : '' }}>Holiday</option>
                <option value="overtime" {{ $type === 'overtime' ? 'selected' : '' }}>Overtime</option>
            </select>
        </div>
        <div class="mt-5">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white border-none rounded-field cursor-pointer text-sm font-semibold">
                <i class="icon-[ph--funnel-fill]"></i> Filter
            </button>
            <a href="{{ route('work-requests.index') }}" class="px-4 py-2 bg-base-200 text-base-content border border-base-300 rounded-field cursor-pointer text-sm font-semibold no-underline inline-block ml-2">
                Clear
            </a>
        </div>
    </x-slot:filters>

    {{-- Work Requests Table --}}
    @if($workRequests->count() > 0)
        <x-data-table>
            <x-slot:head>
                <th class="px-4 py-3 text-left text-xs font-semibold">Date</th>
                @if($admin || $hr)
                    <th class="px-4 py-3 text-left text-xs font-semibold">Employee</th>
                @endif
                <th class="px-4 py-3 text-left text-xs font-semibold">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold">Work Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold">Time</th>
                <th class="px-4 py-3 text-left text-xs font-semibold">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold">Actions</th>
            </x-slot:head>

            @foreach($workRequests as $request)
                <tr class="row-hover border-b border-base-300">
                    <td class="px-4 py-3 text-sm text-base-content">
                        {{ $request->created_at->format('M d, Y') }}
                    </td>
                    @if($admin || $hr)
                        <td class="px-4 py-3 text-sm text-base-content">
                            {{ $request->employee->full_name }}
                        </td>
                    @endif
                    <td class="px-4 py-3 text-sm text-base-content">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $request->request_type === 'weekend' ? 'bg-blue-100 text-blue-800' : ($request->request_type === 'holiday' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800') }}">
                            {{ ucfirst($request->request_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-base-content">
                        {{ $request->work_date->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-base-content/60">
                        {{ $request->start_time ? $request->start_time : '-' }}
                        @if($request->end_time) - {{ $request->end_time }}@endif
                    </td>
                    <td style="padding:12px 16px; font-size:14px;">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $request->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($request->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($request->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                                    <a href="{{ route('work-requests.show', $request) }}" class="btn btn-soft btn-info btn-sm">
    <i class="icon-[ph--eye-fill]"></i>
</a>
                        {{-- Only employees can edit/cancel their own pending requests --}}
                        @if(!$admin && !$hr && $request->canBeCancelled())
                            <a href="{{ route('work-requests.edit', $request) }}"
                               class="px-3 py-1.5 bg-amber-500 text-white border-none rounded-field cursor-pointer text-xs no-underline inline-block mr-1">
                                <i class="icon-[ph--pencil-fill]"></i>
                            </a>
                            <button onclick="cancelRequest({{ $request->id }}, '{{ route('work-requests.destroy', $request) }}')"
                                    class="px-3 py-1.5 bg-red-500 text-white border-none rounded-field cursor-pointer text-xs">
                                <i class="icon-[ph--x]"></i>
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    @else
        <div class="card p-12 text-center">
            <i class="icon-[ph--calendar-x-fill] text-5xl text-base-content/30 mb-4 block"></i>
            <h3 class="m-0 mb-2 text-base-content/60">No Work Requests Found</h3>
            <p class="text-base-content/40 m-0 mb-6">
                @if(!$admin && !$hr)
                    {{ $status || $type ? 'Try adjusting your filters or' : 'Get started by' }} creating a new work request.
                @else
                    No work requests match your current filters.
                @endif
            </p>
            @if(!$admin && !$hr)
                <a href="{{ route('work-requests.create') }}"
                   class="px-5 py-3 text-white border-none rounded-field cursor-pointer text-sm font-semibold no-underline inline-flex items-center gap-2" style="background:{{ $color }};">
                    <i class="icon-[ph--plus-fill]"></i> New Request
                </a>
            @endif
        </div>
    @endif

    {{-- Mobile Cards --}}
    <div class="md:hidden p-4 flex flex-col gap-3">
        @if($workRequests->count() > 0)
            @foreach($workRequests as $request)
                <div class="card bg-base-100 border border-base-300 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="text-sm text-base-content font-semibold">
                                {{ $request->created_at->format('M d, Y') }}
                            </div>
                            @if($admin || $hr)
                                <div class="text-xs text-base-content/60">{{ $request->employee->full_name }}</div>
                            @endif
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $request->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($request->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($request->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $request->request_type === 'weekend' ? 'bg-blue-100 text-blue-800' : ($request->request_type === 'holiday' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800') }}">
                            {{ ucfirst($request->request_type) }}
                        </span>
                        <span><i class="icon-[ph--calendar-fill] w-3.5"></i> {{ $request->work_date->format('M d, Y') }}</span>
                        <span><i class="icon-[ph--clock-fill] w-3.5"></i> {{ $request->start_time ? $request->start_time : '-' }} @if($request->end_time) - {{ $request->end_time }}@endif</span>
                    </div>

                    <div class="flex gap-2 flex-wrap mt-3 pt-3 border-t border-base-200">
                        <a href="{{ route('work-requests.show', $request) }}" class="btn btn-soft btn-info btn-sm">
                            <i class="icon-[ph--eye-fill]"></i> View
                        </a>
                        @if(!$admin && !$hr && $request->canBeCancelled())
                            <a href="{{ route('work-requests.edit', $request) }}" class="btn btn-soft btn-warning btn-sm">
                                <i class="icon-[ph--pencil-fill]"></i> Edit
                            </a>
                            <button onclick="cancelRequest({{ $request->id }}, '{{ route('work-requests.destroy', $request) }}')" class="btn btn-soft btn-error btn-sm">
                                <i class="icon-[ph--x]"></i> Cancel
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="py-10 text-center text-base-content/40">
                <i class="icon-[ph--calendar-x-fill] text-3xl mb-2 block"></i>
                No work requests found.
            </div>
        @endif
    </div>
</x-table-card>
@endsection

@section('scripts')
<script>
function cancelRequest(requestId, url) {
    Swal.fire({
        title: 'Cancel Work Request',
        text: 'Are you sure you want to cancel this work request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Cancel',
        cancelButtonText: 'Keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('notyf_success', data.message);
                    window.location.reload();
                } else {
                    window.notyf.error(data.message ?? 'Something went wrong.');
                }
            })
            .catch(error => {
                window.notyf.error('Failed to cancel request: ' + error.message);
            });
        }
    });
}
</script>
@endsection
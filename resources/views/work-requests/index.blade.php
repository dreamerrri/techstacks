@extends('layouts.app')

@section('title', 'Work Requests')

@section('content')

@php
    $user  = auth()->user();
    $admin = $user->isAdmin();
    $hr    = $user->isHR();
@endphp

<x-table-card action="{{ route('work-requests.index') }}">
    <x-slot:title>
        <x-dot-loader />
        <p class="text-base-content"> Work Requests</p>
        <x-info-tooltip>
            {{ $admin || $hr ? 'Manage employee work requests' : 'View and manage your work requests' }}
        </x-info-tooltip>
    </x-slot:title>

    <x-slot:actions>
        <div class="flex gap-2">
            @if($admin || $hr)
                @if($pendingCount > 0)
                    <a href="{{ route('work-requests.pending') }}" class="btn btn-soft btn-warning btn-sm">
                        <i class="icon-[tabler--clock]"></i> Pending ({{ $pendingCount }})
                    </a>
                @endif
            @else
                <a href="{{ route('work-requests.create') }}" class="btn btn-soft btn-primary btn-sm">
                    <i class="icon-[tabler--plus]"></i> New Request
                </a>
            @endif
        </div>
    </x-slot:actions>

    {{-- Filters --}}
    <x-slot:filters>
        <div class="flex flex-wrap items-end gap-2">
            <div class="fieldset">
                
                <select name="status" class="select select-bordered select-sm min-w-[150px]">
                    <option value="">All</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="fieldset">
         
                <select name="type" class="select select-bordered select-sm min-w-[150px]">
                    <option value="">All</option>
                    <option value="weekend" {{ $type === 'weekend' ? 'selected' : '' }}>Weekend</option>
                    <option value="holiday" {{ $type === 'holiday' ? 'selected' : '' }}>Holiday</option>
                    <option value="overtime" {{ $type === 'overtime' ? 'selected' : '' }}>Overtime</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-soft btn-error btn-sm">
                    <i class="icon-[tabler--filter]"></i> Filter
                </button>
                <a href="{{ route('work-requests.index') }}" class="btn btn-soft btn-sm">Clear</a>
            </div>
        </div>
    </x-slot:filters>

    {{-- Work Requests Table --}}
    @if($workRequests->count() > 0)
        <x-data-table>
            <x-slot:head>
                <th>Date</th>
                @if($admin || $hr)
                    <th>Employee</th>
                @endif
                <th>Type</th>
                <th>Work Date</th>
                <th>Time</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </x-slot:head>

            @foreach($workRequests as $request)
                @php
                    $typeClass = match($request->request_type) {
                        'weekend'  => 'badge-soft badge-info',
                        'holiday'  => 'badge-soft badge-warning',
                        'overtime' => 'badge-soft badge-secondary',
                        default    => 'badge-soft',
                    };
                    $statusClass = match($request->status) {
                        'pending'   => 'badge-soft badge-warning',
                        'approved'  => 'badge-soft badge-success',
                        'rejected'  => 'badge-soft badge-error',
                        'cancelled' => 'badge-soft',
                        default     => 'badge-soft',
                    };
                @endphp
                <tr class="row-hover border-b border-base-300">
                    <td class="text-base-content">
                        {{ $request->created_at->format('M d, Y') }}
                    </td>
                    @if($admin || $hr)
                        <td class="text-base-content">
                            {{ $request->employee->full_name }}
                        </td>
                    @endif
                    <td>
                        <span class="badge {{ $typeClass }}">{{ ucfirst($request->request_type) }}</span>
                    </td>
                    <td class="text-base-content">
                        {{ $request->work_date->format('M d, Y') }}
                    </td>
                    <td class="text-base-content/60">
                        {{ $request->start_time ?: '-' }}
                        @if($request->end_time) - {{ $request->end_time }}@endif
                    </td>
                    <td>
                        <span class="badge {{ $statusClass }}">{{ ucfirst($request->status) }}</span>
                    </td>
                    <td class="text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('work-requests.show', $request) }}" class="btn btn-soft btn-info btn-sm">
                                <i class="icon-[tabler--eye]"></i>
                            </a>
                            {{-- Only employees can edit/cancel their own pending requests --}}
                            @if(!$admin && !$hr && $request->canBeCancelled())
                                <a href="{{ route('work-requests.edit', $request) }}" class="btn btn-soft btn-warning btn-sm">
                                    <i class="icon-[tabler--pencil]"></i>
                                </a>
                                <button onclick="cancelRequest({{ $request->id }}, '{{ route('work-requests.destroy', $request) }}')"
                                        class="btn btn-soft btn-error btn-sm">
                                    <i class="icon-[tabler--x]"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    @else
        <div class="card p-12 text-center">
            <i class="icon-[tabler--calendar-off] text-3xl text-base-content/40 mb-4 block"></i>
            <h3 class="text-base-content/60 font-semibold mb-2">No Work Requests Found</h3>
            <p class="text-base-content/40 mb-6">
                @if(!$admin && !$hr)
                    {{ $status || $type ? 'Try adjusting your filters or' : 'Get started by' }} creating a new work request.
                @else
                    No work requests match your current filters.
                @endif
            </p>
            @if(!$admin && !$hr)
                <a href="{{ route('work-requests.create') }}" class="btn btn-soft btn-primary">
                    <i class="icon-[tabler--plus]"></i> New Request
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
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $request->status === 'pending' ? 'bg-warning/10 text-warning' : ($request->status === 'approved' ? 'bg-success/10 text-success' : ($request->status === 'rejected' ? 'bg-error/10 text-error' : 'bg-base-200 text-base-content')) }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $request->request_type === 'weekend' ? 'bg-info/10 text-info' : ($request->request_type === 'holiday' ? 'bg-warning/10 text-warning' : 'bg-secondary/10 text-secondary') }}">
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
    const style = getComputedStyle(document.documentElement);
    const errorColor   = style.getPropertyValue('--color-error').trim();
    const neutralColor = style.getPropertyValue('--color-neutral').trim();

    Swal.fire({
        title: 'Cancel Work Request',
        text: 'Are you sure you want to cancel this work request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: errorColor || '#ef4444',
        cancelButtonColor: neutralColor || '#6b7280',
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
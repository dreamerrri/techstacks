@extends('layouts.app')

@section('title', 'Financial Requests')

@section('content')

@php
    $user  = auth()->user();
    $admin = $user->isAdmin();
    $hr    = $user->isHR();
@endphp

<x-table-card action="{{ route('financial-requests.index') }}">
    <x-slot:title>
        <x-dot-loader />
        <p class="text-base-content"> Financial Requests</p>
        <x-info-tooltip>
            {{ $admin || $hr ? 'Manage employee cash advance and reimbursement requests' : 'View and manage your cash advance and reimbursement requests' }}
        </x-info-tooltip>
    </x-slot:title>

    <x-slot:actions>
        <div class="flex gap-2">
            @if($admin || $hr)
                @if($pendingCount > 0)
                    <a href="{{ route('financial-requests.index', ['status' => 'pending']) }}" class="btn btn-soft btn-warning btn-sm">
                        <i class="icon-[tabler--clock]"></i> Pending ({{ $pendingCount }})
                    </a>
                @endif
            @else
                <a href="{{ route('financial-requests.create') }}" class="btn btn-soft btn-primary btn-sm">
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
                    <option value="cash_advance" {{ $type === 'cash_advance' ? 'selected' : '' }}>Cash Advance</option>
                    <option value="reimbursement" {{ $type === 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-soft btn-error btn-sm">
                    <i class="icon-[tabler--filter]"></i> Filter
                </button>
                <a href="{{ route('financial-requests.index') }}" class="btn btn-soft btn-sm">Clear</a>
            </div>
        </div>
    </x-slot:filters>

    {{-- Financial Requests Table --}}
    @if($financialRequests->count() > 0)
        <x-data-table>
            <x-slot:head>
                <th>Date</th>
                @if($admin || $hr)
                    <th>Employee</th>
                @endif
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </x-slot:head>

            @foreach($financialRequests as $request)
                @php
                    $typeClass = match($request->request_type) {
                        'cash_advance'   => 'badge-soft badge-info',
                        'reimbursement' => 'badge-soft badge-success',
                        default          => 'badge-soft',
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
                        <span class="badge {{ $typeClass }}">
                            {{ $request->request_type === 'cash_advance' ? 'Cash Advance' : 'Reimbursement' }}
                        </span>
                    </td>
                    <td class="text-base-content font-semibold">
                        ₱{{ number_format($request->amount, 2) }}
                    </td>
                    <td class="text-base-content/60">
                        {{ $request->description ?: '-' }}
                    </td>
                    <td>
                        <span class="badge {{ $statusClass }}">{{ ucfirst($request->status) }}</span>
                    </td>
                    <td class="text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('financial-requests.show', $request) }}" class="btn btn-soft btn-info btn-sm">
                                <i class="icon-[tabler--eye]"></i>
                            </a>
                            {{-- Only employees can edit/cancel their own pending requests --}}
                            @if(!$admin && !$hr && $request->canBeCancelled())
                                <a href="{{ route('financial-requests.edit', $request) }}" class="btn btn-soft btn-warning btn-sm">
                                    <i class="icon-[tabler--pencil]"></i>
                                </a>
                                <button onclick="cancelRequest({{ $request->id }}, '{{ route('financial-requests.destroy', $request) }}')"
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
            <i class="icon-[tabler--cash] text-3xl text-base-content/40 mb-4 block"></i>
            <h3 class="text-base-content/60 font-semibold mb-2">No Financial Requests Found</h3>
            <p class="text-base-content/40 mb-6">
                @if(!$admin && !$hr)
                    {{ $status || $type ? 'Try adjusting your filters or' : 'Get started by' }} creating a new financial request.
                @else
                    No financial requests match your current filters.
                @endif
            </p>
            @if(!$admin && !$hr)
                <a href="{{ route('financial-requests.create') }}" class="btn btn-soft btn-primary">
                    <i class="icon-[tabler--plus]"></i> New Request
                </a>
            @endif
        </div>
    @endif

    {{-- Mobile Cards --}}
    <div class="md:hidden p-4 flex flex-col gap-3">
        @if($financialRequests->count() > 0)
            @foreach($financialRequests as $request)
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
                        <span class="badge {{ $typeClass }}">
                            {{ $request->request_type === 'cash_advance' ? 'Cash Advance' : 'Reimbursement' }}
                        </span>
                        <span>₱{{ number_format($request->amount, 2) }}</span>
                    </div>

                    @if($request->description)
                        <div class="text-xs text-base-content/60 mt-2">
                            {{ $request->description }}
                        </div>
                    @endif

                    <div class="flex gap-2 mt-3 justify-end">
                        <a href="{{ route('financial-requests.show', $request) }}" class="btn btn-soft btn-info btn-sm">
                            <i class="icon-[tabler--eye]"></i>
                        </a>
                        @if(!$admin && !$hr && $request->canBeCancelled())
                            <a href="{{ route('financial-requests.edit', $request) }}" class="btn btn-soft btn-warning btn-sm">
                                <i class="icon-[tabler--pencil]"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="card p-8 text-center">
                <i class="icon-[tabler--cash] text-2xl text-base-content/40 mb-3 block"></i>
                <h3 class="text-base-content/60 font-semibold mb-2">No Financial Requests Found</h3>
                <p class="text-base-content/40 text-sm">
                    @if(!$admin && !$hr)
                        {{ $status || $type ? 'Try adjusting your filters or' : 'Get started by' }} creating a new financial request.
                    @else
                        No financial requests match your current filters.
                    @endif
                </p>
                @if(!$admin && !$hr)
                    <a href="{{ route('financial-requests.create') }}" class="btn btn-soft btn-primary btn-sm mt-3">
                        <i class="icon-[tabler--plus]"></i> New Request
                    </a>
                @endif
            </div>
        @endif
    </div>
</x-table-card>

@endsection

@section('scripts')
<script>
function cancelRequest(id, url) {
    if (confirm('Are you sure you want to cancel this financial request?')) {
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('financial-requests.index') }}';
            } else {
                alert(data.message || 'Failed to cancel request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the request');
        });
    }
}
</script>
@endsection
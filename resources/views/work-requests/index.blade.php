@extends('layouts.app')

@section('title', 'Work Requests')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Work Requests</span>
@endsection

@section('content')

@php
    $user = auth()->user();
    $admin = $user->isAdmin();
    $hr = $user->isHR();
    $color = $admin ? '#dc2626' : ($hr ? '#2563eb' : '#667eea');
    $colorDark = $admin ? '#991b1b' : ($hr ? '#1e40af' : '#764ba2');
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-calendar-check"></i> Work Requests
        </div>
        <h2 style="margin:8px 0 4px 0;">Work Requests</h2>
        <p style="color:#6b7280; margin:0;">
            {{ $admin || $hr ? 'Manage employee work requests' : 'View and manage your work requests' }}
        </p>
    </div>
    <div style="display:flex; gap:8px;">
        @if($admin || $hr)
            @if($pendingCount > 0)
                <a href="{{ route('work-requests.pending') }}"
                   style="padding:12px 20px; background:#f59e0b; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fas fa-clock"></i> Pending ({{ $pendingCount }})
                </a>
            @endif
        @else
            <a href="{{ route('work-requests.create') }}"
               style="padding:12px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-plus"></i> New Request
            </a>
        @endif
    </div>
</div>

{{-- Filters --}}
<div class="card" style="padding:20px; margin-bottom:24px;">
    <form method="GET" action="{{ route('work-requests.index') }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        <div>
            <label style="font-size:12px; color:#6b7280; margin-bottom:4px; display:block;">Status</label>
            <select name="status" style="padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; min-width:150px;">
                <option value="">All</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <label style="font-size:12px; color:#6b7280; margin-bottom:4px; display:block;">Type</label>
            <select name="type" style="padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; min-width:150px;">
                <option value="">All</option>
                <option value="weekend" {{ $type === 'weekend' ? 'selected' : '' }}>Weekend</option>
                <option value="holiday" {{ $type === 'holiday' ? 'selected' : '' }}>Holiday</option>
                <option value="overtime" {{ $type === 'overtime' ? 'selected' : '' }}>Overtime</option>
            </select>
        </div>
        <div style="margin-top:20px;">
            <button type="submit" style="padding:8px 16px; background:#3b82f6; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600;">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('work-requests.index') }}" style="padding:8px 16px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-block; margin-left:8px;">
                Clear
            </a>
        </div>
    </form>
</div>

{{-- Work Requests Table --}}
@if($workRequests->count() > 0)
<div class="card" style="padding:0; overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Date</th>
                    @if($admin || $hr)
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Employee</th>
                    @endif
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Type</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Work Date</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Time</th>
                    <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Status</th>
                    <th style="padding:12px 16px; text-align:center; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workRequests as $request)
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $request->created_at->format('M d, Y') }}
                    </td>
                    @if($admin || $hr)
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $request->employee->full_name }}
                    </td>
                    @endif
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        <span style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:600; 
                            {{ $request->request_type === 'weekend' ? 'background:#dbeafe; color:#1e40af;' : 
                               ($request->request_type === 'holiday' ? 'background:#fef3c7; color:#92400e;' : 'background:#e0e7ff; color:#3730a3;') }}">
                            {{ ucfirst($request->request_type) }}
                        </span>
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                        {{ $request->work_date->format('M d, Y') }}
                    </td>
                    <td style="padding:12px 16px; font-size:14px; color:#6b7280;">
                        {{ $request->start_time ? $request->start_time : '-' }} 
                        @if($request->end_time) - {{ $request->end_time }}@endif
                    </td>
                    <td style="padding:12px 16px; font-size:14px;">
                        <span style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:600;
                            {{ $request->status === 'pending' ? 'background:#fef3c7; color:#92400e;' :
                               ($request->status === 'approved' ? 'background:#d1fae5; color:#065f46;' :
                               ($request->status === 'rejected' ? 'background:#fee2e2; color:#991b1b;' : 'background:#f3f4f6; color:#374151;')) }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td style="padding:12px 16px; text-align:center;">
                        <a href="{{ route('work-requests.show', $request) }}"
                           style="padding:6px 12px; background:#3b82f6; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px; text-decoration:none; display:inline-block; margin-right:4px;">
                            <i class="fas fa-eye"></i>
                        </a>
                        {{-- Only employees can edit/cancel their own pending requests --}}
                        @if(!$admin && !$hr && $request->canBeCancelled())
                            <a href="{{ route('work-requests.edit', $request) }}"
                               style="padding:6px 12px; background:#f59e0b; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px; text-decoration:none; display:inline-block; margin-right:4px;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="cancelRequest({{ $request->id }})"
                                    style="padding:6px 12px; background:#ef4444; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="card" style="padding:48px; text-align:center;">
    <i class="fas fa-calendar-times" style="font-size:48px; color:#d1d5db; margin-bottom:16px;"></i>
    <h3 style="margin:0 0 8px 0; color:#6b7280;">No Work Requests Found</h3>
    <p style="color:#9ca3af; margin:0 0 24px 0;">
        @if(!$admin && !$hr)
            {{ $status || $type ? 'Try adjusting your filters or' : 'Get started by' }} creating a new work request.
        @else
            No work requests match your current filters.
        @endif
    </p>
    @if(!$admin && !$hr)
        <a href="{{ route('work-requests.create') }}"
           style="padding:12px 24px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="fas fa-plus"></i> New Request
        </a>
    @endif
</div>
@endif

@endsection

@section('scripts')
<script>


function cancelRequest(requestId) {
    Swal.fire({
        title: 'Cancel Work Request',
        text: 'Are you sure you want to cancel this work request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Cancel',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route('work-requests.destroy', ':id') }}'.replace(':id', requestId), {
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

@extends('layouts.app')

@section('title', 'Pending Work Requests')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Dashboard</a>
    <i class="icon-[ph--caret-right-fill]" style="font-size:11px;"></i>
    <a href="{{ route('work-requests.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Work Requests</a>
    <i class="icon-[ph--caret-right-fill]" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Pending Requests</span>
@endsection

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <div style="display:inline-block; background:#fef3c7; color:#92400e; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="icon-[ph--clock-fill]"></i> Pending Requests
        </div>
        <h2 style="margin:8px 0 4px 0;">Pending Work Requests</h2>
        <p style="color:#6b7280; margin:0;">
            Review and approve or reject pending work requests
        </p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('work-requests.index') }}"
           style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="icon-[ph--list-fill]"></i> All Requests
        </a>
    </div>
</div>

@if($pendingRequests->count() > 0)
    {{-- Pending Requests Table --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Employee</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Type</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Work Date</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Time</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Reason</th>
                        <th style="padding:12px 16px; text-align:center; font-size:12px; font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingRequests as $request)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                            <div style="font-weight:600;">{{ $request->employee->full_name }}</div>
                            <div style="font-size:12px; color:#6b7280;">{{ $request->employee->employee_id }}</div>
                            <div style="font-size:12px; color:#6b7280;">{{ $request->employee->position }}</div>
                        </td>
                        <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                            <span style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:600; 
                                {{ $request->request_type === 'weekend' ? 'background:#dbeafe; color:#1e40af;' : 
                                   ($request->request_type === 'holiday' ? 'background:#fef3c7; color:#92400e;' : 'background:#e0e7ff; color:#3730a3;') }}">
                                {{ ucfirst($request->request_type) }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                            {{ $request->work_date->format('M d, Y') }}
                            <div style="font-size:12px; color:#6b7280;">{{ $request->work_date->format('l') }}</div>
                        </td>
                        <td style="padding:12px 16px; font-size:14px; color:#6b7280;">
                            {{ $request->start_time ? $request->start_time : '-' }} 
                            @if($request->end_time) - {{ $request->end_time }}@endif
                            @if($request->estimated_hours)
                            <div style="font-size:12px; color:#6b7280;">{{ number_format($request->estimated_hours, 2) }} hrs</div>
                            @endif
                        </td>
                        <td style="padding:12px 16px; font-size:14px; color:#6b7280; max-width:200px;">
                            {{ $request->reason ? \Illuminate\Support\Str::limit($request->reason, 50) : '-' }}
                        </td>
                        <td style="padding:12px 16px; text-align:center;">
                            <a href="{{ route('work-requests.show', $request) }}"
                               style="padding:6px 12px; background:#3b82f6; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px; text-decoration:none; display:inline-block; margin-right:4px;">
                                <i class="icon-[ph--eye-fill]"></i>
                            </a>
                            <button type="button" onclick="approveRequest({{ $request->id }})"
                                    style="padding:6px 12px; background:#10b981; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-right:4px;">
                                <i class="icon-[ph--check-fill]"></i>
                            </button>
                            <button type="button" onclick="showRejectModal({{ $request->id }})"
                                    style="padding:6px 12px; background:#ef4444; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
                                <i class="icon-[ph--x-fill]"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="card" style="padding:48px; text-align:center;">
        <i class="icon-[ph--check-circle-fill]" style="font-size:48px; color:#10b981; margin-bottom:16px;"></i>
        <h3 style="margin:0 0 8px 0; color:#6b7280;">All Caught Up!</h3>
        <p style="color:#9ca3af; margin:0 0 24px 0;">
            There are no pending work requests to review.
        </p>
        <a href="{{ route('work-requests.index') }}"
           style="padding:12px 24px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="icon-[ph--list-fill]"></i> View All Requests
        </a>
    </div>
@endif

@endsection

@section('scripts')
<script>
function approveRequest(requestId) {
    Swal.fire({
        title: 'Approve Work Request',
        text: 'Are you sure you want to approve this work request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/work-requests/' + requestId + '/approve', {
                method: 'POST',
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
                window.notyf.error('Failed to approve request: ' + error.message);
            });
        }
    });
}

function showRejectModal(requestId) {
    Swal.fire({
        title: 'Reject Request',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Please provide a reason for rejection...',
        inputAttributes: {
            maxlength: 500,
            required: true,
        },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
        preConfirm: (reason) => {
            if (!reason) {
                Swal.showValidationMessage('Please provide a rejection reason');
            }
            return reason;
        },
    }).then(result => {
        if (result.isConfirmed) {
            rejectRequest(requestId, result.value);
        }
    });
}



function rejectRequest(requestId, reason) {
    const formData = new FormData();
    formData.append('rejection_reason', reason);

    fetch('/work-requests/' + requestId + '/reject', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            sessionStorage.setItem('notyf_success', data.message);
            window.location.reload();
        } else {
            window.notyf.error(data.message ?? 'Something went wrong.');
        }
    })
    .catch(error => {
        window.notyf.error('Failed to reject request: ' + error.message);
    });
}


</script>
@endsection

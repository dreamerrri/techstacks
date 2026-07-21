@extends('layouts.app')

@section('title', 'Work Request Details')



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
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="icon-[ph--file-fill]"></i> Request Details
        </div>
        <h2 style="margin:8px 0 4px 0;">Work Request #{{ $workRequest->id }}</h2>
        <p style="color:#6b7280; margin:0;">
            Submitted on {{ $workRequest->created_at->format('M d, Y \a\t g:i A') }}
        </p>
    </div>
    <a href="{{ route('work-requests.index') }}"
       style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
        <i class="icon-[ph--arrow-left-fill]"></i> Back to Requests
    </a>
</div>

{{-- Status Banner --}}
<div style="padding:16px 24px; border-radius:8px; margin-bottom:24px; 
    {{ $workRequest->status === 'pending' ? 'background:#fef3c7; border:1px solid #f59e0b;' :
       ($workRequest->status === 'approved' ? 'background:#d1fae5; border:1px solid #10b981;' :
       ($workRequest->status === 'rejected' ? 'background:#fee2e2; border:1px solid #ef4444;' : 'background:#f3f4f6; border:1px solid #d1d5db;')) }}">
    <div style="display:flex; align-items:center; gap:12px;">
        <i class="fas {{ $workRequest->status === 'pending' ? 'fa-clock' : 
                          ($workRequest->status === 'approved' ? 'fa-check-circle' : 
                          ($workRequest->status === 'rejected' ? 'fa-times-circle' : 'fa-ban')) }}" 
           style="font-size:24px; 
           {{ $workRequest->status === 'pending' ? 'color:#92400e;' :
              ($workRequest->status === 'approved' ? 'color:#065f46;' :
              ($workRequest->status === 'rejected' ? 'color:#991b1b;' : 'color:#374151;')) }}"></i>
        <div>
            <div style="font-size:16px; font-weight:700; 
                {{ $workRequest->status === 'pending' ? 'color:#92400e;' :
                   ($workRequest->status === 'approved' ? 'color:#065f46;' :
                   ($workRequest->status === 'rejected' ? 'color:#991b1b;' : 'color:#374151;')) }}">
                {{ ucfirst($workRequest->status) }}
            </div>
            @if($workRequest->status === 'approved' && $workRequest->approved_at)
                <div style="font-size:12px; color:#6b7280;">
                    Approved on {{ $workRequest->approved_at->format('M d, Y \a\t g:i A') }}
                    @if($workRequest->approvedBy)
                        by {{ $workRequest->approvedBy->name }}
                    @endif
                </div>
            @endif
            @if($workRequest->status === 'rejected' && $workRequest->rejection_reason)
                <div style="font-size:12px; color:#991b1b; margin-top:4px;">
                    Reason: {{ $workRequest->rejection_reason }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Request Details --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px; margin-bottom:24px;">
    {{-- Employee Info --}}
    @if($isAdmin || $isHR)
    <div class="card" style="padding:24px;">
        <h3 style="margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
            <i class="icon-[tabler--user]" style="color:#6b7280;"></i> Employee
        </h3>
        <div style="font-size:14px; font-weight:600; color:#1f2937;">
            {{ $workRequest->employee->full_name }}
        </div>
        <div style="font-size:12px; color:#6b7280; margin-top:4px;">
            {{ $workRequest->employee->employee_id }}
        </div>
        <div style="font-size:12px; color:#6b7280; margin-top:2px;">
            {{ $workRequest->employee->position }}
        </div>
    </div>
    @endif

    {{-- Request Type --}}
    <div class="card" style="padding:24px;">
        <h3 style="margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
            <i class="icon-[ph--tag-fill]" style="color:#6b7280;"></i> Request Type
        </h3>
        <span style="padding:6px 12px; border-radius:12px; font-size:14px; font-weight:600; display:inline-block;
            {{ $workRequest->request_type === 'weekend' ? 'background:#dbeafe; color:#1e40af;' : 
               ($workRequest->request_type === 'holiday' ? 'background:#fef3c7; color:#92400e;' : 'background:#e0e7ff; color:#3730a3;') }}">
            {{ ucfirst($workRequest->request_type) }} Work
        </span>
    </div>

    {{-- Work Date --}}
    <div class="card" style="padding:24px;">
        <h3 style="margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
            <i class=" icon-[ph--calendar-fill]" style="color:#6b7280;"></i> Work Date
        </h3>
        <div style="font-size:14px; font-weight:600; color:#1f2937;">
            {{ $workRequest->work_date->format('l, F d, Y') }}
        </div>
    </div>

    {{-- Time Range --}}
    @if($workRequest->start_time || $workRequest->end_time)
    <div class="card" style="padding:24px;">
        <h3 style="margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
            <i class="icon-[ph--clock-fill]" style="color:#6b7280;"></i> Time Range
        </h3>
        <div style="font-size:14px; font-weight:600; color:#1f2937;">
            {{ $workRequest->start_time ? $workRequest->start_time : 'Not specified' }}
            @if($workRequest->end_time) - {{ $workRequest->end_time }}@endif
        </div>
        @if($workRequest->estimated_hours)
        <div style="font-size:12px; color:#6b7280; margin-top:4px;">
            Estimated: {{ number_format($workRequest->estimated_hours, 2) }} hours
        </div>
        @endif
    </div>
    @endif
</div>

{{-- Reason --}}
@if($workRequest->reason)
<div class="card" style="padding:24px; margin-bottom:24px;">
    <h3 style="margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
        <i class="icon-[ph--chat-text-fill]" style="color:#6b7280;"></i> Reason
    </h3>
    <div style="font-size:14px; color:#374151; line-height:1.6; white-space:pre-wrap;">
        {{ $workRequest->reason }}
    </div>
</div>
@endif

{{-- Actions --}}
@if($workRequest->canBeCancelled() || ($isAdmin || $isHR && $workRequest->canBeApproved()))
<div class="card" style="padding:24px;">
    <h3 style="margin:0 0 16px 0;">Actions</h3>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        {{-- Employee actions: edit and cancel own pending requests --}}
        @if(!$isAdmin && !$isHR && $workRequest->canBeCancelled())
            <a href="{{ route('work-requests.edit', $workRequest) }}"
               style="padding:12px 24px; background:#f59e0b; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                <i class="icon-[ph--pencil-fill]"></i> Edit Request
            </a>
            <button onclick="cancelRequest({{ $workRequest->id }})"
                    style="padding:12px 24px; background:#ef4444; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; display:inline-flex; align-items:center; gap:8px;">
                <i class="icon-[ph--x]"></i> Cancel Request
            </button>
        @endif
        {{-- HR/Admin actions: approve/reject pending requests --}}
        @if($isAdmin || $isHR && $workRequest->canBeApproved())
        @if($workRequest->status === 'pending')
    <button onclick="approveRequest({{ $workRequest->id }})"
                    style="padding:12px 24px; background:#10b981; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; display:inline-flex; align-items:center; gap:8px;">
                <i class="icon-[tabler--check]"></i> Approve
            </button>
            <button onclick="showRejectModal({{ $workRequest->id }})"
                    style="padding:12px 24px; background:#ef4444; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; display:inline-flex; align-items:center; gap:8px;">
                <i class="icon-[ph--x]"></i> Reject
            </button>
@endif
        @endif
    </div>
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
            fetch('{{ route('work-requests.destroy', $workRequest->id) }}'.replace('{{ $workRequest->id }}', requestId), {
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
                    window.location.href = '{{ route('work-requests.index') }}';
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
            fetch('{{ route('work-requests.approve', $workRequest->id) }}'.replace('{{ $workRequest->id }}', requestId), {
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

    fetch('{{ route('work-requests.reject', $workRequest->id) }}'.replace('{{ $workRequest->id }}', requestId), {
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

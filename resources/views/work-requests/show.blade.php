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
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg p-4 mb-4">
            <i class="icon-[ph--file-fill]"></i> Request Details
        </div>
        <h2 class="text-base-content">Work Request #{{ $workRequest->id }}</h2>
        <p class="text-base-content/60">
            Submitted on {{ $workRequest->created_at->format('M d, Y \a\t g:i A') }}
        </p>
    </div>
    <a href="{{ route('work-requests.index') }}"
       class="inline-flex items-center font-semibold text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4 gap-3 cursor-pointer no-underline">
        <i class="icon-[ph--arrow-left-fill]"></i> Back to Requests
    </a>
</div>

{{-- Status Banner --}}
<div class="p-4 rounded-lg mb-6 border
    {{ $workRequest->status === 'pending' ? 'bg-warning/10 border-warning text-warning-content' :
       ($workRequest->status === 'approved' ? 'bg-success/10 border-success text-success-content' :
       ($workRequest->status === 'rejected' ? 'bg-error/10 border-error text-error-content' : 'bg-base-200 border-base-300')) }}">
    <div class="flex items-center gap-3">
        <i class="fas {{ $workRequest->status === 'pending' ? 'fa-clock' : 
                          ($workRequest->status === 'approved' ? 'fa-check-circle' : 
                          ($workRequest->status === 'rejected' ? 'fa-times-circle' : 'fa-ban')) }}" 
           class="text-2xl"></i>
        <div>
            <div class="text-base font-bold">
                {{ ucfirst($workRequest->status) }}
            </div>
            @if($workRequest->status === 'approved' && $workRequest->approved_at)
                <div class="text-xs text-base-content/60">
                    Approved on {{ $workRequest->approved_at->format('M d, Y \a\t g:i A') }}
                    @if($workRequest->approvedBy)
                        by {{ $workRequest->approvedBy->name }}
                    @endif
                </div>
            @endif
            @if($workRequest->status === 'rejected' && $workRequest->rejection_reason)
                <div class="text-xs mt-2">
                    Reason: {{ $workRequest->rejection_reason }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Request Details --}}
<div class="grid mb-4 gap-3">
    {{-- Employee Info --}}
    @if($isAdmin || $isHR)
    <div class="card p-4">
        <h3 class="flex items-center gap-3">
            <i class="icon-[tabler--user] text-base-content/60"></i> Employee
        </h3>
        <div class="font-semibold text-sm text-base-content">
            {{ $workRequest->employee->full_name }}
        </div>
        <div class="text-xs text-base-content/60 mt-2">
            {{ $workRequest->employee->employee_id }}
        </div>
        <div class="text-xs text-base-content/60 mt-2">
            {{ $workRequest->employee->position }}
        </div>
    </div>
    @endif

    {{-- Request Type --}}
    <div class="card p-4">
        <h3 class="flex items-center gap-3">
            <i class="icon-[ph--tag-fill] text-base-content/60"></i> Request Type
        </h3>
        <span class="badge badge-info badge-sm
            {{ $workRequest->request_type === 'weekend' ? 'badge-info' :
               ($workRequest->request_type === 'holiday' ? 'badge-warning' : 'badge-primary') }}">
            {{ ucfirst($workRequest->request_type) }} Work
        </span>
    </div>

    {{-- Work Date --}}
    <div class="card p-4">
        <h3 class="flex items-center gap-3">
            <i class=" icon-[ph--calendar-fill] text-base-content/60"></i> Work Date
        </h3>
        <div class="font-semibold text-sm text-base-content">
            {{ $workRequest->work_date->format('l, F d, Y') }}
        </div>
    </div>

    {{-- Time Range --}}
    @if($workRequest->start_time || $workRequest->end_time)
    <div class="card p-4">
        <h3 class="flex items-center gap-3">
            <i class="icon-[ph--clock-fill] text-base-content/60"></i> Time Range
        </h3>
        <div class="font-semibold text-sm text-base-content">
            {{ $workRequest->start_time ? $workRequest->start_time : 'Not specified' }}
            @if($workRequest->end_time) - {{ $workRequest->end_time }}@endif
        </div>
        @if($workRequest->estimated_hours)
        <div class="text-xs text-base-content/60 mt-2">
            Estimated: {{ number_format($workRequest->estimated_hours, 2) }} hours
        </div>
        @endif
    </div>
    @endif
</div>

{{-- Reason --}}
@if($workRequest->reason)
<div class="card p-4 mb-4">
    <h3 class="flex items-center gap-3">
        <i class="icon-[ph--chat-text-fill] text-base-content/60"></i> Reason
    </h3>
    <div class="text-sm text-base-content">
        {{ $workRequest->reason }}
    </div>
</div>
@endif

{{-- Actions --}}
@if($workRequest->canBeCancelled() || ($isAdmin || $isHR && $workRequest->canBeApproved()))
<div class="card p-4">
    <h3 class="text-base-content">Actions</h3>
    <div class="flex flex-wrap gap-3">
        {{-- Employee actions: edit and cancel own pending requests --}}
        @if(!$isAdmin && !$isHR && $workRequest->canBeCancelled())
            <a href="{{ route('work-requests.edit', $workRequest) }}"
               class="inline-flex items-center font-semibold text-sm rounded-lg p-4 gap-3 cursor-pointer no-underline">
                <i class="icon-[ph--pencil-fill]"></i> Edit Request
            </a>
            <button onclick="cancelRequest({{ $workRequest->id }})"
                    class="inline-flex items-center font-semibold text-sm rounded-lg p-4 gap-3 cursor-pointer">
                <i class="icon-[ph--x]"></i> Cancel Request
            </button>
        @endif
        {{-- HR/Admin actions: approve/reject pending requests --}}
        @if($isAdmin || $isHR && $workRequest->canBeApproved())
        @if($workRequest->status === 'pending')
    <button onclick="approveRequest({{ $workRequest->id }})"
                    class="inline-flex items-center font-semibold text-sm rounded-lg p-4 gap-3 cursor-pointer">
                <i class="icon-[tabler--check]"></i> Approve
            </button>
            <button onclick="showRejectModal({{ $workRequest->id }})"
                    class="inline-flex items-center font-semibold text-sm rounded-lg p-4 gap-3 cursor-pointer">
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

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
        <div class="inline-block bg-info/10 text-info px-3.5 py-1.5 rounded-full text-xs font-semibold mb-2">
            <i class="icon-[ph--file-fill]"></i> Request Details
        </div>
        <h2 style="margin:8px 0 4px 0;">Work Request #{{ $workRequest->id }}</h2>
        <p class="text-base-content/60 m-0">
            Submitted on {{ $workRequest->created_at->format('M d, Y \a\t g:i A') }}
        </p>
    </div>
    <a href="{{ route('work-requests.index') }}"
       class="px-5 py-3 bg-base-200 text-base-content border border-base-300 rounded-field cursor-pointer text-sm font-semibold no-underline inline-flex items-center gap-2">
        <i class="icon-[ph--arrow-left-fill]"></i> Back to Requests
    </a>
</div>

{{-- Status Banner --}}
<div class="p-4 rounded-lg mb-6 {{ $workRequest->status === 'pending' ? 'bg-warning/10 border border-warning' : ($workRequest->status === 'approved' ? 'bg-success/10 border border-success' : ($workRequest->status === 'rejected' ? 'bg-error/10 border border-error' : 'bg-base-200 border border-base-300')) }}">
    <div style="display:flex; align-items:center; gap:12px;">
        <i class="fas {{ $workRequest->status === 'pending' ? 'fa-clock' : 
                          ($workRequest->status === 'approved' ? 'fa-check-circle' : 
                          ($workRequest->status === 'rejected' ? 'fa-times-circle' : 'fa-ban')) }} text-2xl
           {{ $workRequest->status === 'pending' ? 'text-warning' :
              ($workRequest->status === 'approved' ? 'text-success' :
              ($workRequest->status === 'rejected' ? 'text-error' : 'text-base-content')) }}"></i>
        <div>
            <div class="text-base font-bold {{ $workRequest->status === 'pending' ? 'text-warning' :
                   ($workRequest->status === 'approved' ? 'text-success' :
                   ($workRequest->status === 'rejected' ? 'text-error' : 'text-base-content')) }}">
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
                <div class="text-xs text-error mt-1">
                    Reason: {{ $workRequest->rejection_reason }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Request Details --}}
<div class="grid grid-cols-[repeat(auto-fit,minmax(300px,1fr))] gap-6 mb-6">
    {{-- Employee Info --}}
    @if($isAdmin || $isHR)
    <div class="card p-6">
        <h3 class="m-0 mb-4 flex items-center gap-2">
            <i class="icon-[tabler--user] text-base-content/60"></i> Employee
        </h3>
        <div class="text-sm font-semibold text-base-content">
            {{ $workRequest->employee->full_name }}
        </div>
        <div class="text-xs text-base-content/60 mt-1">
            {{ $workRequest->employee->employee_id }}
        </div>
        <div class="text-xs text-base-content/60 mt-0.5">
            {{ $workRequest->employee->position }}
        </div>
    </div>
    @endif

    {{-- Request Type --}}
    <div class="card p-6">
        <h3 class="m-0 mb-4 flex items-center gap-2">
            <i class="icon-[ph--tag-fill] text-base-content/60"></i> Request Type
        </h3>
        <span class="px-3 py-1.5 rounded-full text-sm font-semibold inline-block {{ $workRequest->request_type === 'weekend' ? 'bg-info/10 text-info' : ($workRequest->request_type === 'holiday' ? 'bg-warning/10 text-warning' : 'bg-secondary/10 text-secondary') }}">
            {{ ucfirst($workRequest->request_type) }} Work
        </span>
    </div>

    {{-- Work Date --}}
    <div class="card p-6">
        <h3 class="m-0 mb-4 flex items-center gap-2">
            <i class="icon-[ph--calendar-fill] text-base-content/60"></i> Work Date
        </h3>
        <div class="text-sm font-semibold text-base-content">
            {{ $workRequest->work_date->format('l, F d, Y') }}
        </div>
    </div>

    {{-- Time Range --}}
    @if($workRequest->start_time || $workRequest->end_time)
    <div class="card p-6">
        <h3 class="m-0 mb-4 flex items-center gap-2">
            <i class="icon-[ph--clock-fill] text-base-content/60"></i> Time Range
        </h3>
        <div class="text-sm font-semibold text-base-content">
            {{ $workRequest->start_time ? $workRequest->start_time : 'Not specified' }}
            @if($workRequest->end_time) - {{ $workRequest->end_time }}@endif
        </div>
        @if($workRequest->estimated_hours)
        <div class="text-xs text-base-content/60 mt-1">
            Estimated: {{ number_format($workRequest->estimated_hours, 2) }} hours
        </div>
        @endif
    </div>
    @endif
</div>

{{-- Reason --}}
@if($workRequest->reason)
<div class="card p-6 mb-6">
    <h3 class="m-0 mb-4 flex items-center gap-2">
        <i class="icon-[ph--chat-text-fill] text-base-content/60"></i> Reason
    </h3>
    <div class="text-sm text-base-content leading-relaxed whitespace-pre-wrap">
        {{ $workRequest->reason }}
    </div>
</div>
@endif

{{-- Actions --}}
@if($workRequest->canBeCancelled() || ($isAdmin || $isHR && $workRequest->canBeApproved()))
<div class="card p-6">
    <h3 class="m-0 mb-4">Actions</h3>
    <div class="flex gap-3 flex-wrap">
        {{-- Employee actions: edit and cancel own pending requests --}}
        @if(!$isAdmin && !$isHR && $workRequest->canBeCancelled())
            <a href="{{ route('work-requests.edit', $workRequest) }}"
               class="px-6 py-3 bg-warning text-white border-none rounded-field cursor-pointer text-sm font-semibold no-underline inline-flex items-center gap-2">
                <i class="icon-[ph--pencil-fill]"></i> Edit Request
            </a>
            <button onclick="cancelRequest({{ $workRequest->id }})"
                    class="px-6 py-3 bg-error text-white border-none rounded-field cursor-pointer text-sm font-semibold inline-flex items-center gap-2">
                <i class="icon-[ph--x]"></i> Cancel Request
            </button>
        @endif
        {{-- HR/Admin actions: approve/reject pending requests --}}
        @if($isAdmin || $isHR && $workRequest->canBeApproved())
        @if($workRequest->status === 'pending')
    <button onclick="approveRequest({{ $workRequest->id }})"
                    class="px-6 py-3 bg-success text-white border-none rounded-field cursor-pointer text-sm font-semibold inline-flex items-center gap-2">
                <i class="icon-[tabler--check]"></i> Approve
            </button>
            <button onclick="showRejectModal({{ $workRequest->id }})"
                    class="px-6 py-3 bg-error text-white border-none rounded-field cursor-pointer text-sm font-semibold inline-flex items-center gap-2">
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

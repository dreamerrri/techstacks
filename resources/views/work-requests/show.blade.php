@extends('layouts.app')

@section('title', 'Work Request Details')

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();

    $statusMeta = [
        'pending'   => ['color' => 'warning', 'icon' => 'tabler--clock'],
        'approved'  => ['color' => 'success', 'icon' => 'tabler--circle-check'],
        'rejected'  => ['color' => 'error',   'icon' => 'tabler--circle-x'],
        'cancelled' => ['color' => 'neutral', 'icon' => 'tabler--ban'],
    ][$workRequest->status] ?? ['color' => 'neutral', 'icon' => 'tabler--info-circle'];

    $typeColor = match ($workRequest->request_type) {
        'weekend' => 'info',
        'holiday' => 'warning',
        default => 'primary',
    };

    // Was: $isAdmin || $isHR && $workRequest->canBeApproved()
    // && binds tighter than ||, so admins bypassed canBeApproved() entirely.
    $canApprove = ($isAdmin || $isHR) && $workRequest->canBeApproved();
@endphp

<a href="{{ route('work-requests.index') }}"
   class="inline-flex items-center gap-1 text-sm text-base-content no-underline hover:text-primary mb-4">
    <span class="icon-[tabler--arrow-left] size-4"></span> Back to Requests
</a>

<div class="card bg-base-100 shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-base-content">Work Request #{{ $workRequest->id }}</h2>
            <p class="text-muted">
                Submitted on {{ $workRequest->created_at->format('M d, Y \a\t g:i A') }}
            </p>
        </div>
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
                <div class="text-xs text-muted">
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

    {{-- Request Details --}}
    <div class="card shadow-sm p-4 mb-4">
        <h3 class="text-muted mb-3">Details</h3>
        <div class="flex flex-wrap gap-2">
            @if($isAdmin || $isHR)
            <span class="badge badge-soft badge-neutral gap-1.5">
                <span class="icon-[tabler--user] size-4"></span>
                {{ $workRequest->employee->full_name }}
            </span>
            @endif

    {{-- Request Type --}}
    <div class="card p-6">
        <h3 class="m-0 mb-4 flex items-center gap-2">
            <i class="icon-[ph--tag-fill] text-subtle"></i> Request Type
        </h3>
        <span class="px-3 py-1.5 rounded-full text-sm font-semibold inline-block {{ $workRequest->request_type === 'weekend' ? 'bg-info/10 text-info' : ($workRequest->request_type === 'holiday' ? 'bg-warning/10 text-warning' : 'bg-secondary/10 text-secondary') }}">
            {{ ucfirst($workRequest->request_type) }} Work
        </span>
    </div>

            <span class="badge badge-soft badge-neutral gap-1.5">
                <span class="icon-[tabler--calendar] size-4"></span>
                {{ $workRequest->work_date->format('M d, Y') }}
            </span>

            @if($workRequest->start_time || $workRequest->end_time)
            <span class="badge badge-soft badge-neutral gap-1.5">
                <span class="icon-[tabler--clock] size-4"></span>
                {{ $workRequest->start_time ?: 'Not specified' }}@if($workRequest->end_time) - {{ $workRequest->end_time }}@endif
            </span>
            @endif
        </div>

        @if(($isAdmin || $isHR) || $workRequest->estimated_hours)
        <div class="text-xs text-muted mt-3 space-y-1">
            @if($isAdmin || $isHR)
                <div>{{ $workRequest->employee->employee_id }} · {{ $workRequest->employee->position }}</div>
            @endif
            @if($workRequest->estimated_hours)
                <div>Estimated: {{ number_format($workRequest->estimated_hours, 2) }} hours</div>
            @endif
        </div>
        @endif
    </div>

    {{-- Reason --}}
    @if($workRequest->reason)
    <div class="card shadow-sm p-4 mb-4">
        <h3 class="flex items-center gap-2 text-muted">
            <span class="icon-[tabler--message] size-4"></span> Reason
        </h3>
        <div class="text-sm text-base-content mt-2">
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
    @endif
</div>

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
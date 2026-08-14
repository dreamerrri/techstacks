@extends('layouts.app')

@section('title', 'Financial Request Details')


@section('content')

@php
    $user  = auth()->user();
    $admin = $user->isAdmin();
    $hr    = $user->isHR();
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg p-4 mb-4">
            <i class="icon-[ph--cash-fill]"></i> Request Details
        </div>
        <h2 class="text-base-content">Financial Request #{{ $financialRequest->id }}</h2>
        <p class="text-subtle">
            {{ $financialRequest->request_type === 'cash_advance' ? 'Cash Advance' : 'Reimbursement' }} Request
        </p>
    </div>
    <a href="{{ route('financial-requests.index') }}"
       class="inline-flex items-center font-semibold text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4 gap-3 cursor-pointer no-underline">
        <i class="icon-[ph--arrow-left-fill]"></i> Back to Requests
    </a>
</div>

{{-- Request Details Card --}}
<div class="card p-5 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Employee Info --}}
        <div>
            <h3 class="text-sm font-bold text-base-content mb-3 flex items-center gap-2">
                <i class="icon-[ph--user-fill] text-error"></i> Employee Information
            </h3>
            <div class="flex flex-col text-sm">
                <div class="flex justify-between items-start py-2 border-b border-base-200">
                    <span class="text-faint">Name</span>
                    <span class="font-semibold text-base-content">{{ $financialRequest->employee->full_name }}</span>
                </div>
                <div class="flex justify-between items-start py-2 border-b border-base-200">
                    <span class="text-faint">Employee ID</span>
                    <span class="font-semibold text-base-content">{{ $financialRequest->employee->employee_id }}</span>
                </div>
                <div class="flex justify-between items-start py-2">
                    <span class="text-faint">Department</span>
                    <span class="font-semibold text-base-content">{{ $financialRequest->employee->department }}</span>
                </div>
            </div>
        </div>

        {{-- Request Info --}}
        <div>
            <h3 class="text-sm font-bold text-base-content mb-3 flex items-center gap-2">
                <i class="icon-[ph--file-text-fill] text-error"></i> Request Information
            </h3>
            <div class="flex flex-col text-sm">
                <div class="flex justify-between items-start py-2 border-b border-base-200">
                    <span class="text-faint">Request Type</span>
                    <span class="font-semibold text-base-content">
                        {{ $financialRequest->request_type === 'cash_advance' ? 'Cash Advance' : 'Reimbursement' }}
                    </span>
                </div>
                <div class="flex justify-between items-start py-2 border-b border-base-200">
                    <span class="text-faint">Amount</span>
                    <span class="font-bold text-error text-base">₱{{ number_format($financialRequest->amount, 2) }}</span>
                </div>
                @php
                    $maxAmount = $financialRequest->request_type === 'cash_advance' 
                        ? $financialRequest->employee->basic_salary 
                        : 15000;
                    $limitDescription = $financialRequest->request_type === 'cash_advance' 
                        ? '100% of monthly salary' 
                        : '₱15,000 maximum';
                @endphp
                <div class="flex justify-between items-start py-2 border-b border-base-200">
                    <span class="text-faint">Maximum Allowed</span>
                    <span class="font-semibold text-subtle">₱{{ number_format($maxAmount, 2) }} ({{ $limitDescription }})</span>
                </div>
                <div class="flex justify-between items-start py-2">
                    <span class="text-faint">Request Date</span>
                    <span class="font-semibold text-base-content">{{ $financialRequest->request_date->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between items-start py-2">
                    <span class="text-faint">Status</span>
                    @php
                        $statusClass = match($financialRequest->status) {
                            'pending'   => 'badge-soft badge-warning',
                            'approved'  => 'badge-soft badge-success',
                            'rejected'  => 'badge-soft badge-error',
                            'cancelled' => 'badge-soft',
                            default     => 'badge-soft',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($financialRequest->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if($financialRequest->description)
        <div class="mt-4">
            <h3 class="text-sm font-bold text-base-content mb-2">Description</h3>
            <p class="text-sm text-muted bg-base-200 rounded-lg p-4">
                {{ $financialRequest->description }}
            </p>
        </div>
    @endif

    {{-- Reason --}}
    @if($financialRequest->reason)
        <div class="mt-4">
            <h3 class="text-sm font-bold text-base-content mb-2">Reason</h3>
            <p class="text-sm text-muted bg-base-200 rounded-lg p-4">
                {{ $financialRequest->reason }}
            </p>
        </div>
    @endif

    {{-- Receipt Image (for reimbursements) --}}
    @if($financialRequest->request_type === 'reimbursement' && $financialRequest->receipt_image)
        <div class="mt-4">
            <h3 class="text-sm font-bold text-base-content mb-2">Receipt</h3>
            <div class="bg-base-200 rounded-lg p-4">
                <img src="{{ asset('storage/' . $financialRequest->receipt_image) }}" 
                     alt="Receipt" 
                     class="max-w-full h-auto rounded-lg border border-base-300"
                     style="max-height: 400px;">
            </div>
        </div>
    @endif

    {{-- Payment Information (for approved cash advances) --}}
    @if($financialRequest->request_type === 'cash_advance' && $financialRequest->status === 'approved')
        <div class="mt-4">
            <h3 class="text-sm font-bold text-base-content mb-2">Payment Information</h3>
            <div class="bg-base-200 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-subtle">Total Amount:</span>
                    <span class="font-bold text-base-content">₱{{ number_format($financialRequest->amount, 2) }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-sm text-subtle">Amount Paid:</span>
                    <span class="font-bold text-success">₱{{ number_format($financialRequest->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between items-center mt-2 pt-2 border-t border-base-300">
                    <span class="text-sm text-subtle">Remaining Balance:</span>
                    <span class="font-bold text-error">₱{{ number_format($financialRequest->remaining_balance, 2) }}</span>
                </div>
                <div class="flex justify-between items-center mt-2 pt-2 border-t border-base-300">
                    <span class="text-sm text-subtle">Payment Progress:</span>
                    <span class="font-semibold text-base-content">{{ $financialRequest->payment_progress }}%</span>
                </div>
                <div class="mt-3 pt-3 border-t border-base-300">
                    <p class="text-xs text-subtle text-center">
                        <i class="icon-[ph--info-fill]"></i>
                        Payments are automatically deducted at 50% of net pay per payroll cutoff
                    </p>
                </div>
                @if($financialRequest->isFullyPaid())
                    <div class="mt-3 text-center">
                        <span class="badge badge-soft badge-success">Fully Paid</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payment History --}}
        @if($financialRequest->cashAdvancePayments->count() > 0)
            <div class="mt-4">
                <h3 class="text-sm font-bold text-base-content mb-2">Payment History</h3>
                <div class="bg-base-200 rounded-lg p-4">
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Payroll Period</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($financialRequest->cashAdvancePayments->sortByDesc('created_at') as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                        <td class="text-success font-semibold">₱{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->description ?? '-' }}</td>
                                        <td>
                                            @if($payment->payrollPeriod)
                                                {{ $payment->payrollPeriod->cutoff_start->format('M d') }} - {{ $payment->payrollPeriod->cutoff_end->format('M d, Y') }}
                                            @else
                                                Manual
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Approval Information --}}
    @if($financialRequest->approved_by)
        <div class="mt-4">
            <h3 class="text-sm font-bold text-base-content mb-2">Approval Information</h3>
            <div class="bg-base-200 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-subtle">Approved By:</span>
                    <span class="font-semibold text-base-content">{{ $financialRequest->approvedBy->name }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-sm text-subtle">Approved At:</span>
                    <span class="font-semibold text-base-content">{{ $financialRequest->approved_at->format('M d, Y g:i A') }}</span>
                </div>
                @if($financialRequest->rejection_reason)
                    <div class="mt-3 pt-3 border-t border-base-300">
                        <span class="text-sm text-subtle">Rejection Reason:</span>
                        <p class="text-sm text-error mt-1">{{ $financialRequest->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

 {{-- Actions --}}
<div class="card p-4">
    <div class="flex gap-3 flex-wrap">
        {{-- HR/Admin Actions --}}
        @if($admin || $hr)
            @if($financialRequest->canBeApproved())
                <button onclick="approveRequest({{ $financialRequest->id }})"
                        class="btn btn-soft btn-success">
                    <i class="icon-[ph--check-fill]"></i> Approve
                </button>
                <button onclick="showRejectModal({{ $financialRequest->id }})"
                        class="btn btn-soft btn-error">
                    <i class="icon-[ph--x-fill]"></i> Reject
                </button>
            @endif
        @endif

        {{-- Employee Actions --}}
        @if(!$admin && !$hr && $financialRequest->canBeCancelled())
            <a href="{{ route('financial-requests.edit', $financialRequest) }}" 
               class="btn btn-soft btn-warning">
                <i class="icon-[ph--pencil-fill]"></i> Edit
            </a>
            <button onclick="cancelRequest({{ $financialRequest->id }})"
                    class="btn btn-soft btn-error">
                <i class="icon-[ph--x-fill]"></i> Cancel
            </button>
        @endif
    </div>
</div>

 {{-- Reject Modal --}}
<div id="rejectModal" class="modal modal-open" style="display: none;">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Reject Financial Request</h3>
        <div class="py-4">
            <label class="label">
                <span class="label-text">Rejection Reason <span class="text-error">*</span></span>
            </label>
            <textarea id="rejection_reason" rows="4" maxlength="1000"
                      class="textarea textarea-bordered w-full"
                      placeholder="Please provide a reason for rejection..."></textarea>
        </div>
        <div class="modal-action">
            <button onclick="closeRejectModal()" class="btn btn-soft">Cancel</button>
            <button onclick="confirmReject({{ $financialRequest->id }})" class="btn btn-soft btn-error">
                Confirm Rejection
            </button>
        </div>
    </div>
    <div class="modal-backdrop" onclick="closeRejectModal()"></div>
</div>

@endsection

@section('scripts')
<script>
function approveRequest(id) {
    if (confirm('Are you sure you want to approve this financial request?')) {
        fetch('{{ route('financial-requests.approve', $financialRequest) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to approve request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the request');
        });
    }
}

function showRejectModal(id) {
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('rejection_reason').value = '';
}

function confirmReject(id) {
    const reason = document.getElementById('rejection_reason').value.trim();
    
    if (!reason) {
        alert('Please provide a rejection reason');
        return;
    }
    
    fetch('{{ route('financial-requests.reject', $financialRequest) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ rejection_reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to reject request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the request');
    });
}

function cancelRequest(id) {
    if (confirm('Are you sure you want to cancel this financial request?')) {
        fetch('{{ route('financial-requests.destroy', $financialRequest) }}', {
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
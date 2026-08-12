@extends('layouts.app')

@section('title', 'Edit Financial Request')


@section('content')

@php
    $user = auth()->user();
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg p-4 mb-4">
            <i class="icon-[ph--cash-fill]"></i> Edit Request
        </div>
        <h2 class="text-base-content">Edit Financial Request #{{ $financialRequest->id }}</h2>
        <p class="text-base-content/60">
            {{ $financialRequest->request_type === 'cash_advance' ? 'Cash Advance' : 'Reimbursement' }} Request
        </p>
    </div>
    <a href="{{ route('financial-requests.show', $financialRequest) }}"
       class="inline-flex items-center font-semibold text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4 gap-3 cursor-pointer no-underline">
        <i class="icon-[ph--arrow-left-fill]"></i> Back to Request
    </a>
</div>

{{-- Form --}}
<div class="card p-4">
    <form id="financialRequestForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        {{-- Request Type (Read-only) --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-base-content mb-4">
                Request Type
            </label>
            <input type="text" value="{{ $financialRequest->request_type === 'cash_advance' ? 'Cash Advance' : 'Reimbursement' }}" 
                   readonly
                   class="w-full text-sm border border-base-300 rounded-lg p-4 bg-base-200">
        </div>

        {{-- Amount --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-base-content mb-2">
                Amount (₱) <span class="text-error">*</span>
            </label>
            <input type="number" name="amount" id="amount" min="0" step="0.01"
                   value="{{ $financialRequest->amount }}"
                   class="w-full text-sm border border-base-300 rounded-lg p-4"
                   placeholder="0.00"
                   {{ $financialRequest->request_type === 'cash_advance' ? 'readonly' : '' }}>
            <p class="text-xs text-base-content/60 mt-2" id="amount_help_text">
                @if($financialRequest->request_type === 'cash_advance')
                    Amount is automatically set to your basic salary: ₱{{ number_format($financialRequest->employee->basic_salary, 2) }}
                @else
                    Enter the amount you are requesting (maximum ₱15,000)
                @endif
            </p>
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-base-content mb-4">
                Description
            </label>
            <input type="text" name="description" id="description" maxlength="255"
                   value="{{ $financialRequest->description }}"
                   class="w-full text-sm border border-base-300 rounded-lg p-4"
                   placeholder="Brief description of the request">
            <p class="text-xs text-base-content/60 mt-2">
                Short description (optional)
            </p>
        </div>

        {{-- Reason --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-base-content mb-4">
                Reason
            </label>
            <textarea name="reason" id="reason" rows="4" maxlength="1000"
                      class="w-full text-sm border border-base-300 rounded-lg p-4 resize-y"
                      placeholder="Provide a detailed reason for this financial request...">{{ $financialRequest->reason }}</textarea>
            <p class="text-xs text-base-content/60 mt-2">
                Maximum 1000 characters
            </p>
        </div>

        {{-- Receipt Image (for reimbursements only) --}}
        @if($financialRequest->request_type === 'reimbursement')
            <div class="mb-4">
                <label class="font-semibold text-sm text-base-content mb-4">
                    Receipt Image
                </label>
                <input type="file" name="receipt_image" id="receipt_image" accept="image/*"
                       class="w-full text-sm border border-base-300 rounded-lg p-4">
                <p class="text-xs text-base-content/60 mt-2">
                    Upload new receipt image (optional, max 2MB)
                </p>
                @if($financialRequest->receipt_image)
                    <div class="mt-2">
                        <p class="text-xs text-base-content/60 mb-1">Current receipt:</p>
                        <img src="{{ asset('storage/' . $financialRequest->receipt_image) }}" 
                             alt="Current Receipt" 
                             class="max-w-full h-auto rounded-lg border border-base-300"
                             style="max-height: 200px;">
                    </div>
                @endif
            </div>
        @endif

        {{-- Submit Button --}}
        <div class="flex gap-3">
            <button type="submit"
                    class="font-semibold text-sm bg-primary rounded-lg p-4 cursor-pointer">
                <i class="icon-[ph--floppy-disk-fill]"></i> Update Request
            </button>
            <a href="{{ route('financial-requests.show', $financialRequest) }}"
               class="inline-flex items-center font-semibold text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4 cursor-pointer no-underline">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#financialRequestForm');
    const amountInput = document.querySelector('#amount');
    
    // Monthly salary for validation
    const monthlySalary = {{ $financialRequest->employee->basic_salary }};
    const requestType = '{{ $financialRequest->request_type }}';
    
    // Set amount limit based on request type
    function setAmountLimit() {
        let maxAmount = 0;
        let limitDescription = '';
        
        if (requestType === 'cash_advance') {
            maxAmount = monthlySalary; // 100% of monthly salary
            limitDescription = 'Fixed to basic salary';
            
            // Ensure amount is set to basic salary and is readonly
            amountInput.value = monthlySalary.toFixed(2);
            amountInput.readOnly = true;
        } else {
            maxAmount = 15000; // Fixed ₱15,000 limit
            limitDescription = '₱15,000 maximum';
            
            // Ensure amount is editable
            amountInput.readOnly = false;
        }
        
        amountInput.max = maxAmount;
        amountInput.placeholder = requestType === 'cash_advance' ? '' : `Max: ₱${maxAmount.toFixed(2)}`;
        
        // Update help text
        const helpText = document.getElementById('amount_help_text');
        if (helpText) {
            if (requestType === 'cash_advance') {
                helpText.textContent = `Amount is automatically set to your basic salary: ₱${monthlySalary.toFixed(2)}`;
            } else {
                helpText.textContent = `Maximum amount: ₱${maxAmount.toFixed(2)} (${limitDescription})`;
            }
        }
    }
    
    // Initialize limit
    setAmountLimit();
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="icon-[ph--spinner] animate-spin"></i> Updating...';
        
        fetch('{{ route('financial-requests.update', $financialRequest) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('financial-requests.show', $financialRequest) }}';
            } else {
                alert(data.message || 'Failed to update request');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the request');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
@endsection
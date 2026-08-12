@extends('layouts.app')

@section('title', 'New Financial Request')


@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg p-4 mb-4">
            <i class="icon-[ph--cash-fill]"></i> New Request
        </div>
        <h2 class="text-base-content">Create Financial Request</h2>
        <p class="text-base-content/60">
            Submit a request for cash advance or reimbursement
        </p>
    </div>
    <a href="{{ route('financial-requests.index') }}"
       class="inline-flex items-center font-semibold text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4 gap-3 cursor-pointer no-underline">
        <i class="icon-[ph--arrow-left-fill]"></i> Back to Requests
    </a>
</div>

{{-- Form --}}
<div class="card p-4">
    <form id="financialRequestForm" enctype="multipart/form-data">
        @csrf
        
        {{-- Request Type --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-base-content mb-4">
                Request Type <span class="text-error">*</span>
            </label>
            <select name="request_type" id="request_type" required
                    class="w-full text-sm border border-base-300 rounded-lg p-4">
                <option value="">Select type...</option>
                <option value="cash_advance">Cash Advance</option>
                <option value="reimbursement">Reimbursement</option>
            </select>
            <p class="text-xs text-base-content/60 mt-2">
                Choose the type of financial request
            </p>
        </div>

        {{-- Amount --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-base-content mb-2">
                Amount (₱) <span class="text-error">*</span>
            </label>
            <input type="number" name="amount" id="amount" min="0" step="0.01"
                   class="w-full text-sm border border-base-300 rounded-lg p-4"
                   placeholder="0.00">
            <p class="text-xs text-base-content/60 mt-2" id="amount_help_text">
                Enter the amount you are requesting
            </p>
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-base-content mb-4">
                Description
            </label>
            <input type="text" name="description" id="description" maxlength="255"
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
                      placeholder="Provide a detailed reason for this financial request..."></textarea>
            <p class="text-xs text-base-content/60 mt-2">
                Maximum 1000 characters
            </p>
        </div>

        {{-- Receipt Image (for reimbursements only) --}}
        <div class="mb-4" id="receipt_section" style="display: none;">
            <label class="font-semibold text-sm text-base-content mb-4">
                Receipt Image <span class="text-error">*</span>
            </label>
            <input type="file" name="receipt_image" id="receipt_image" accept="image/*"
                   class="w-full text-sm border border-base-300 rounded-lg p-4">
            <p class="text-xs text-base-content/60 mt-2">
                Upload receipt image (required for reimbursements, max 2MB)
            </p>
        </div>

        {{-- Submit Button --}}
        <div class="flex gap-3">
            <button type="submit"
                    class="font-semibold text-sm bg-primary rounded-lg p-4 cursor-pointer">
                <i class="icon-[ph--paper-plane-fill]"></i> Submit Request
            </button>
            <a href="{{ route('financial-requests.index') }}"
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
    const requestType = document.querySelector('#request_type');
    const receiptSection = document.querySelector('#receipt_section');
    const receiptImage = document.querySelector('#receipt_image');
    const amountInput = document.querySelector('#amount');
    
    // Monthly salary for validation
    const monthlySalary = {{ $employee->basic_salary }};
    
    // Show/hide fields based on request type
    requestType.addEventListener('change', function() {
        if (this.value === 'reimbursement') {
            receiptSection.style.display = 'block';
            receiptImage.required = true;
            updateAmountLimit('reimbursement');
        } else if (this.value === 'cash_advance') {
            receiptSection.style.display = 'none';
            receiptImage.required = false;
            updateAmountLimit('cash_advance');
        } else {
            receiptSection.style.display = 'none';
            receiptImage.required = false;
        }
    });
    
    // Update amount limit based on request type
    function updateAmountLimit(type) {
        let maxAmount = 0;
        let limitDescription = '';
        
        if (type === 'cash_advance') {
            maxAmount = monthlySalary; // 100% of monthly salary
            limitDescription = 'Fixed to basic salary';
            
            // Make amount readonly and set to basic salary
            amountInput.readOnly = true;
            amountInput.value = monthlySalary.toFixed(2);
            amountInput.required = false;
        } else {
            maxAmount = 15000; // Fixed ₱15,000 limit
            limitDescription = '₱15,000 maximum';
            
            // Make amount editable
            amountInput.readOnly = false;
            amountInput.value = '';
            amountInput.required = true;
        }
        
        amountInput.max = maxAmount;
        amountInput.placeholder = type === 'cash_advance' ? '' : `Max: ₱${maxAmount.toFixed(2)}`;
        
        // Update help text
        const helpText = document.getElementById('amount_help_text');
        if (helpText) {
            if (type === 'cash_advance') {
                helpText.textContent = `Amount is automatically set to your basic salary: ₱${monthlySalary.toFixed(2)}`;
            } else {
                helpText.textContent = `Maximum amount: ₱${maxAmount.toFixed(2)} (${limitDescription})`;
            }
        }
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="icon-[ph--spinner] animate-spin"></i> Submitting...';
        
        fetch('{{ route('financial-requests.store') }}', {
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
                window.location.href = '{{ route('financial-requests.index') }}';
            } else {
                alert(data.message || 'Failed to submit request');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the request');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
@endsection
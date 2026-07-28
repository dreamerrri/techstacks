@extends('layouts.app')

@section('title', 'New Work Request')


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
            <i class=" icon-[ph--calendar-fill]-plus"></i> New Request
        </div>
        <h2 class="text-base-content">Create Work Request</h2>
        <p class="text-base-content/60">
            Submit a request for weekend, holiday, or overtime work
        </p>
    </div>
    <a href="{{ route('work-requests.index') }}"
       class="inline-flex items-center font-semibold text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4 gap-3 cursor-pointer no-underline">
        <i class="icon-[ph--arrow-left-fill]"></i> Back to Requests
    </a>
</div>

{{-- Form --}}
<div class="card p-4">
    <form id="workRequestForm">
        @csrf
        
        {{-- Request Type --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-base-content mb-4">
                Request Type <span class="text-error">*</span>
            </label>
            <select name="request_type" id="request_type" required
                    class="w-full text-sm border border-base-300 rounded-lg p-4">
                <option value="">Select type...</option>
                <option value="weekend">Weekend Work</option>
                <option value="holiday">Holiday Work</option>
                <option value="overtime">Overtime</option>
                <option value="half_day">Half Day</option>
            </select>
            <p class="text-xs text-base-content/60 mt-2">
                Choose the type of work you're requesting
            </p>
        </div>

        {{-- Work Date --}}
        <div class="mb-6">
            <label class="block text-sm font-semibold text-base-content mb-2">
                Work Date <span class="text-error">*</span>
            </label>
            <input type="date" name="work_date" id="work_date" required min="{{ now()->toDateString() }}"
                   class="w-full text-sm border border-base-300 rounded-lg p-4">
            <p class="text-xs text-base-content/60 mt-2">
                Date must be today or in the future
            </p>
        </div>

        {{-- Time Range --}}
        <div class="grid mb-4 gap-3">
            <div>
                <label class="font-semibold text-sm text-base-content mb-4">
                    Start Time
                </label>
                <input type="time" name="start_time" id="start_time"
                       class="w-full text-sm border border-base-300 rounded-lg p-4">
            </div>
            <div>
                <label class="font-semibold text-sm text-base-content mb-4">
                    End Time
                </label>
                <input type="time" name="end_time" id="end_time"
                       class="w-full text-sm border border-base-300 rounded-lg p-4">
            </div>
        </div>

        {{-- Estimated Hours --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-base-content mb-4">
                Estimated Hours
            </label>
            <input type="number" name="estimated_hours" id="estimated_hours" min="0" max="24" step="0.5"
                   class="w-full text-sm border border-base-300 rounded-lg p-4">
            <p class="text-xs text-base-content/60 mt-2">
                Estimated duration of work (optional)
            </p>
            <div id="overtime_hours_display" class="hidden bg-info/10 border-s-4 border-primary rounded-lg p-4 mt-2">
                <span class="font-semibold text-xs text-info">
                    <i class="icon-[ph--clock-fill] text-base-content"></i>
                    Approximate Overtime Hours: <span id="calculated_overtime_hours">0</span>
                </span>
            </div>
        </div>

        {{-- Reason --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-base-content mb-4">
                Reason
            </label>
            <textarea name="reason" id="reason" rows="4" maxlength="500"
                      class="w-full text-sm border border-base-300 rounded-lg p-4 resize-y"
                      placeholder="Provide a reason for this work request..."></textarea>
            <p class="text-xs text-base-content/60 mt-2">
                Maximum 500 characters
            </p>
        </div>

        {{-- Submit Button --}}
        <div class="flex gap-3">
            <button type="submit"
                    class="font-semibold text-sm bg-primary rounded-lg p-4 cursor-pointer">
                <i class="icon-[ph--paper-plane-fill]-plane"></i> Submit Request
            </button>
            <a href="{{ route('work-requests.index') }}"
               class="inline-flex items-center font-semibold text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4 cursor-pointer no-underline">
                Cancel
            </a>
        </div>
    </form>
</div>

{{-- Upcoming Holidays Reference --}}
@if($upcomingHolidays->count() > 0)
<div class="card p-4 mt-2">
    <h3 class="flex items-center gap-3">
        <i class="icon-[ph--calendar-fill] text-base-content/60"></i> Upcoming Holidays
    </h3>
    <div class="grid gap-3">
        @foreach($upcomingHolidays as $holiday)
        <div class="bg-base-200 border-s-4 border-primary rounded-lg p-4">
            <div class="font-semibold text-sm text-base-content">{{ $holiday->name }}</div>
            <div class="text-xs text-base-content/60 mt-2">{{ $holiday->date->format('M d, Y') }}</div>
            <div class="text-base-content/60 mt-2">
                <span class="font-semibold rounded-lg p-4">
                    {{ ucfirst($holiday->type) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
const getVisibleForm = (forms) => {
    for (let form of forms) {
        if (form.offsetParent !== null) {
            return form;
        }
    }
    return forms[0]; // Fallback to first if none visible
};

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('#workRequestForm');
    
    if (forms.length > 0) {
        const form = getVisibleForm(forms);
        
        if (form) {
            const requestType = form.querySelector('#request_type');
            const startTime = form.querySelector('#start_time');
            const endTime = form.querySelector('#end_time');
            const estimatedHours = form.querySelector('#estimated_hours');
            const overtimeDisplay = form.querySelector('#overtime_hours_display');
            const calculatedOvertime = form.querySelector('#calculated_overtime_hours');
            
            // Function to calculate overtime hours
            const calculateOvertime = () => {
                if (requestType.value !== 'overtime') {
                    overtimeDisplay.style.display = 'none';
                    return;
                }
                
                const start = startTime.value;
                const end = endTime.value;
                
                if (start && end) {
                    const startParts = start.split(':');
                    const endParts = end.split(':');
                    
                    const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
                    const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
                    
                    let totalMinutes = endMinutes - startMinutes;
                    
                    // Handle overnight work (e.g., 22:00 to 02:00)
                    if (totalMinutes < 0) {
                        totalMinutes += 24 * 60;
                    }
                    
                    const totalHours = totalMinutes / 60;
                    
                    // Overtime is hours worked beyond regular 8 hours
                    const regularHours = 8;
                    let overtimeHours = totalHours - regularHours;
                    
                    if (overtimeHours < 0) {
                        overtimeHours = 0;
                    }
                    
                    // Round to 1 decimal place
                    overtimeHours = Math.round(overtimeHours * 10) / 10;
                    
                    calculatedOvertime.textContent = overtimeHours.toFixed(1);
                    overtimeDisplay.style.display = 'block';
                    
                    // Auto-fill estimated hours with overtime hours
                    if (estimatedHours.value === '' || parseFloat(estimatedHours.value) === 0) {
                        estimatedHours.value = overtimeHours.toFixed(1);
                    }
                } else {
                    overtimeDisplay.style.display = 'none';
                }
            };
            
            // Listen for changes
            requestType.addEventListener('change', calculateOvertime);
            startTime.addEventListener('change', calculateOvertime);
            endTime.addEventListener('change', calculateOvertime);
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                // Add calculated overtime hours if overtime is selected
                if (requestType.value === 'overtime' && startTime.value && endTime.value) {
                    formData.append('calculated_overtime_hours', calculatedOvertime.textContent);
                }
                
                fetch('{{ route('work-requests.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Request Submitted!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route('work-requests.index') }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to submit request: ' + error.message
                    });
                });
            });
        }
    }
});
</script>
@endsection

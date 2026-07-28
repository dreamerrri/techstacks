@extends('layouts.app')

@section('title', 'Edit Work Request')


@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
@endphp

{{-- Header --}}
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <div>
        <div class="badge badge-info gap-1 mb-2">
            <i class="icon-[ph--pencil-fill]"></i> Edit Request
        </div>
        <h2 class="text-2xl font-bold text-base-content mt-2 mb-1">Edit Work Request #{{ $workRequest->id }}</h2>
        <p class="text-base-content/60 m-0">
            Modify your pending work request
        </p>
    </div>
    <a href="{{ route('work-requests.show', $workRequest) }}"
       class="btn btn-soft gap-2">
        <i class="icon-[ph--arrow-left-fill]"></i> Back to Request
    </a>
</div>

{{-- Form --}}
<div class="card bg-base-100 border border-base-300 p-8 max-w-3xl">
    <form id="workRequestForm">
        @csrf
        @method('PUT')
        
        {{-- Request Type --}}
        <div class="mb-6">
            <label class="label text-sm font-semibold text-base-content mb-2">
                Request Type <span class="text-error">*</span>
            </label>
            <select name="request_type" id="request_type" required
                    class="select select-bordered w-full">
                <option value="">Select type...</option>
                <option value="weekend" {{ $workRequest->request_type === 'weekend' ? 'selected' : '' }}>Weekend Work</option>
                <option value="holiday" {{ $workRequest->request_type === 'holiday' ? 'selected' : '' }}>Holiday Work</option>
                <option value="overtime" {{ $workRequest->request_type === 'overtime' ? 'selected' : '' }}>Overtime</option>
                <option value="half_day" {{ $workRequest->request_type === 'half_day' ? 'selected' : '' }}>Half Day</option>
            </select>
        </div>

        {{-- Work Date --}}
        <div class="mb-6">
            <label class="block text-sm font-semibold text-base-content mb-2">
                Work Date <span class="text-error">*</span>
            </label>
            <input type="date" name="work_date" id="work_date" required min="{{ now()->toDateString() }}"
                   value="{{ $workRequest->work_date->format('Y-m-d') }}"
                   class="input input-bordered w-full">
        </div>

        {{-- Time Range --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="label text-sm font-semibold text-base-content mb-2">
                    Start Time
                </label>
                <input type="time" name="start_time" id="start_time" value="{{ $workRequest->start_time }}"
                       class="input input-bordered w-full">
            </div>
            <div>
                <label class="label text-sm font-semibold text-base-content mb-2">
                    End Time
                </label>
                <input type="time" name="end_time" id="end_time" value="{{ $workRequest->end_time }}"
                       class="input input-bordered w-full">
            </div>
        </div>

        {{-- Estimated Hours --}}
        <div class="mb-6">
            <label class="label text-sm font-semibold text-base-content mb-2">
                Estimated Hours
            </label>
            <input type="number" name="estimated_hours" id="estimated_hours" min="0" max="24" step="0.5"
                   value="{{ $workRequest->estimated_hours }}"
                   class="input input-bordered w-full">
            <div id="overtime_hours_display" class="hidden mt-2 p-2 px-3 bg-info/10 rounded-lg border-s-4 border-info">
                <span class="text-xs font-semibold text-info">
                    <i class="icon-[ph--clock-fill] me-1"></i>
                    Approximate Overtime Hours: <span id="calculated_overtime_hours">0</span>
                </span>
            </div>
        </div>

        {{-- Reason --}}
        <div class="mb-8">
            <label class="label text-sm font-semibold text-base-content mb-2">
                Reason
            </label>
            <textarea name="reason" id="reason" rows="4" maxlength="500"
                      class="textarea textarea-bordered w-full resize-y"
                      placeholder="Provide a reason for this work request...">{{ $workRequest->reason }}</textarea>
        </div>

        {{-- Submit Button --}}
        <div class="flex gap-3">
            <button type="submit"
                    class="btn btn-soft btn-primary">
                <i class="icon-[ph--floppy-disk-fill]"></i> Update Request
            </button>
            <a href="{{ route('work-requests.show', $workRequest) }}" class="btn btn-soft btn-outline">
                Cancel
            </a>
        </div>
    </form>
</div>

{{-- Upcoming Holidays Reference --}}
@if($upcomingHolidays->count() > 0)
<div class="card bg-base-100 border border-base-300 p-6 mt-6">
    <h3 class="m-0 mb-4 flex items-center gap-2">
        <i class="icon-[ph--calendar-fill] text-base-content/60"></i> Upcoming Holidays
    </h3>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(200px,1fr))] gap-3">
        @foreach($upcomingHolidays as $holiday)
        <div class="p-3 bg-base-200 rounded-lg border-s-4 {{ $holiday->type === 'regular' ? 'border-warning' : 'border-info' }}">
            <div class="text-sm font-semibold text-base-content">{{ $holiday->name }}</div>
            <div class="text-xs text-base-content/60 mt-1">{{ $holiday->date->format('M d, Y') }}</div>
            <div class="text-[11px] text-base-content/60 mt-0.5">
                <span class="badge badge-neutral badge-sm font-semibold">
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
                    
                    // Auto-fill estimated hours with overtime hours if empty
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
            
            // Calculate on load if overtime is already selected
            if (requestType.value === 'overtime') {
                calculateOvertime();
            }
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                // Add calculated overtime hours if overtime is selected
                if (requestType.value === 'overtime' && startTime.value && endTime.value) {
                    formData.append('calculated_overtime_hours', calculatedOvertime.textContent);
                }
                
                fetch('{{ route('work-requests.update', $workRequest) }}', {
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
                            title: 'Updated!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route('work-requests.show', $workRequest) }}';
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
                        text: 'Failed to update request: ' + error.message
                    });
                });
            });
        }
    }
});
</script>
@endsection

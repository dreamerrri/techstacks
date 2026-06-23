@extends('layouts.app')

@section('title', 'Add Attendance')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('employee-attendance.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">My Attendance</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Add Attendance</span>
@endsection

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $colorDark = $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2');
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <a href="{{ route('employee-attendance.index') }}"
           style="color:#6b7280; text-decoration:none; font-size:14px; display:inline-flex; align-items:center; gap:6px; margin-bottom:8px;">
            <i class="fas fa-arrow-left"></i> Back to Attendance
        </a>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-plus"></i> Add Attendance
        </div>
        <h2 style="margin:8px 0 4px 0;">Record Attendance</h2>
        <p style="color:#6b7280; margin:0;">
            Add your time-in/time-out record for a specific date
        </p>
    </div>
</div>

<div class="card" style="padding:0; overflow:hidden; max-width:600px;">
    <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb;">
        <h3 style="margin:0;">Attendance Details</h3>
    </div>

    <form id="attendanceForm" style="padding:24px;">
        @csrf
        <input type="hidden" name="employee_id" value="{{ $employee->id }}">

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Date</label>
            <input type="date" name="date" required
                   style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
            <p style="color:#6b7280; font-size:12px; margin-top:4px;">Select the date for this attendance record</p>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
            <div>
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Time In</label>
                <input type="time" name="time_in"
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">When you started work</p>
            </div>
            <div>
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Time Out</label>
                <input type="time" name="time_out"
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">When you finished work</p>
            </div>
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Remarks</label>
            <input type="text" name="remarks"
                   placeholder="Optional notes (e.g., worked from home, late arrival, etc.)"
                   style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
        </div>

        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:16px; margin-bottom:24px;">
            <div style="font-size:13px; font-weight:600; color:#166534; margin-bottom:8px;">
                <i class="fas fa-info-circle"></i> Computation Rules
            </div>
            <ul style="margin:0; padding-left:20px; font-size:13px; color:#166534;">
                <li>Less than 4 hours = 0 days</li>
                <li>4-8 hours = 0.5 days</li>
                <li>8 hours or more = 1 day</li>
                <li>1 hour break is automatically deducted for shifts > 4 hours</li>
            </ul>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" id="saveAttendanceBtn"
                    style="flex:1; padding:12px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600;">
                <i class="fas fa-save"></i> Save Attendance
            </button>
            <a href="{{ route('employee-attendance.index') }}"
               style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; text-decoration:none; text-align:center;">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
// Helper function to get visible element from duplicates
const getVisibleElement = (elements) => {
    for (let element of elements) {
        if (element.offsetParent !== null) {
            return element;
        }
    }
    return elements[0]; // Fallback to first if none visible
};

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('#attendanceForm');
    if (forms.length === 0) return;

    // Get the visible form
    const visibleForm = getVisibleElement(forms);
    if (!visibleForm) return;

    // Helper function to get visible input by name
    const getVisibleInput = (name) => {
        const inputs = document.querySelectorAll(`input[name="${name}"]`);
        for (let input of inputs) {
            if (input.offsetParent !== null) {
                return input;
            }
        }
        return inputs[0]; // Fallback to first if none visible
    };

    // Auto-set time_out to 9 hours after time_in (8 hours work + 1 hour lunch)
    const timeInInput = getVisibleInput('time_in');
    const timeOutInput = getVisibleInput('time_out');

    if (timeInInput && timeOutInput) {
        timeInInput.addEventListener('change', function() {
            const timeInValue = this.value;
            if (timeInValue) {
                const [hours, minutes] = timeInValue.split(':').map(Number);
                const newHours = (hours + 9) % 24;
                const formattedHours = newHours.toString().padStart(2, '0');
                const formattedMinutes = minutes.toString().padStart(2, '0');
                timeOutInput.value = `${formattedHours}:${formattedMinutes}`;
            }
        });
    }

    visibleForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Get visible inputs for form data
        const date = getVisibleInput('date');
        const timeIn = getVisibleInput('time_in');
        const timeOut = getVisibleInput('time_out');
        const remarks = getVisibleInput('remarks');

        const formData = new FormData();
        formData.append('date', date ? date.value : '');
        formData.append('time_in', timeIn ? timeIn.value : '');
        formData.append('time_out', timeOut ? timeOut.value : '');
        formData.append('remarks', remarks ? remarks.value : '');
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route('employee-attendance.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '{{ route('employee-attendance.index') }}';
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
                text: 'Failed to save attendance: ' + error.message
            });
        });
    });
});
</script>
@endsection

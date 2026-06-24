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

<style>
    .clock-tooltip {
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .clock-icon-wrapper:hover .clock-tooltip {
        visibility: visible;
        opacity: 1;
    }
</style>

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
            <input type="date" name="date" readonly
                   value="{{ $todayAttendance ? $todayAttendance->date->format('Y-m-d') : '' }}"
                   style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:#f9fafb;">
            <p style="color:#6b7280; font-size:12px; margin-top:4px;">Auto-set when you clock in</p>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
            <div>
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Time In</label>
                <input type="time" name="time_in"
                       value="{{ $todayAttendance && $todayAttendance->time_in ? (is_string($todayAttendance->time_in) ? substr($todayAttendance->time_in, 0, 5) : $todayAttendance->time_in->format('H:i')) : '' }}"
                       {{ $todayAttendance && $todayAttendance->time_in ? 'readonly' : '' }}
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:#f9fafb;">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">Auto-set when you clock in</p>
            </div>
            <div>
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Time Out</label>
                <input type="time" name="time_out"
                       value="{{ $todayAttendance && $todayAttendance->time_out ? (is_string($todayAttendance->time_out) ? substr($todayAttendance->time_out, 0, 5) : $todayAttendance->time_out->format('H:i')) : '' }}"
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:#f9fafb;">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">Auto-set when you clock out</p>
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-bottom:24px;">
            <button type="button" id="clockInBtn" onclick="clockIn()" {{ $todayAttendance && $todayAttendance->time_in ? 'disabled' : '' }}
                    style="flex:1; padding:14px 20px; background:#10b981; color:white; border:none; border-radius:6px; cursor:pointer; font-size:15px; font-weight:600; {{ $todayAttendance && $todayAttendance->time_in ? 'opacity:0.5;' : '' }}">
                <i class="fas fa-sign-in-alt"></i> {{ $todayAttendance && $todayAttendance->time_in ? 'Clocked In' : 'Clock In' }}
            </button>
            <button type="button" id="clockOutBtn" onclick="clockOut()" {{ $todayAttendance && $todayAttendance->time_out ? 'disabled' : ($todayAttendance && $todayAttendance->time_in ? '' : 'disabled') }}
                    style="flex:1; padding:14px 20px; background:#f59e0b; color:white; border:none; border-radius:6px; cursor:pointer; font-size:15px; font-weight:600; {{ $todayAttendance && $todayAttendance->time_out ? 'opacity:0.5;' : ($todayAttendance && $todayAttendance->time_in ? '' : 'opacity:0.5;') }}">
                <i class="fas fa-sign-out-alt"></i> {{ $todayAttendance && $todayAttendance->time_out ? 'Clocked Out' : 'Clock Out' }}
            </button>
        </div>

        @if($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out)
        @php
            $timeInParts = explode(':', $todayAttendance->time_in);
            $expectedHours = (intval($timeInParts[0]) + 9) % 24;
            $expectedMinutes = $timeInParts[1];
            $period = $expectedHours >= 12 ? 'PM' : 'AM';
            $displayHours = $expectedHours % 12;
            $displayHours = $displayHours ? $displayHours : 12; // Convert 0 to 12
            $expectedTimeOut12Hour = $displayHours . ':' . $expectedMinutes . ' ' . $period;
        @endphp
        <div style="margin-bottom:24px; display:flex; align-items:center; gap:8px;">
            <div class="clock-icon-wrapper" style="position:relative; display:inline-block; padding:5px;">
                <i class="fas fa-clock" style="font-size:28px; color:#f59e0b; cursor:help;"></i>
                <div class="clock-tooltip" style="position:absolute; z-index:1000; bottom:140%; left:0; width:280px; background:#1f2937; color:white; text-align:center; border-radius:8px; padding:12px; font-size:13px; pointer-events:none; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-weight:600; margin-bottom:6px;">Expected Clock Out Time</div>
                    <div style="font-size:14px; margin-bottom:4px;"><strong>{{ $expectedTimeOut12Hour }}</strong></div>
                    <div style="font-size:12px; color:#d1d5db;">9 hours after clock in, including 1-hour lunch break</div>
                    <div style="margin-top:8px; padding-top:8px; border-top:1px solid #374151; font-size:11px; color:#9ca3af;">Click "Clock Out" to record actual time</div>
                    <div style="position:absolute; top:100%; left:15px; border-width:5px; border-style:solid; border-color:#1f2937 transparent transparent transparent;"></div>
                </div>
            </div>
            <span style="color:#6b7280; font-size:13px;">Hover to see expected clock out time</span>
        </div>
        @endif

        <div style="margin-bottom:24px;">
            <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Remarks</label>
            <input type="text" name="remarks"
                   value="{{ $todayAttendance ? $todayAttendance->remarks : '' }}"
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
            <div id="hoursDisplay" style="margin-top:12px; padding-top:12px; border-top:1px solid #bbf7d0; font-size:14px; font-weight:600; color:#166534; display:none;">
                <i class="fas fa-calculator"></i> Rendered Hours: <span id="renderedHoursValue">0.00</span> hrs
            </div>
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

// Clock In - sets date and time_in only (does NOT auto-set time_out)
function clockIn() {
    const dateInputs = document.querySelectorAll('input[name="date"]');
    const timeInInputs = document.querySelectorAll('input[name="time_in"]');
    const clockInBtn = document.getElementById('clockInBtn');
    const clockOutBtn = document.getElementById('clockOutBtn');

    // Set date
    for (let input of dateInputs) {
        if (input.offsetParent !== null) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            input.value = `${year}-${month}-${day}`;
            break;
        }
    }

    // Set time_in
    for (let input of timeInInputs) {
        if (input.offsetParent !== null) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = `${hours}:${minutes}`;
            break;
        }
    }

    // Enable clock out button, disable clock in button
    if (clockInBtn) {
        clockInBtn.disabled = true;
        clockInBtn.style.opacity = '0.5';
        clockInBtn.innerHTML = '<i class="fas fa-check"></i> Clocked In';
    }
    if (clockOutBtn) {
        clockOutBtn.disabled = false;
        clockOutBtn.style.opacity = '1';
    }
}

// Clock Out - sets time_out to current time (not the auto-set 9-hour value)
function clockOut() {
    const timeOutInputs = document.querySelectorAll('input[name="time_out"]');
    const clockOutBtn = document.getElementById('clockOutBtn');

    // Set time_out to current time
    for (let input of timeOutInputs) {
        if (input.offsetParent !== null) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = `${hours}:${minutes}`;
            break;
        }
    }

    // Disable clock out button
    if (clockOutBtn) {
        clockOutBtn.disabled = true;
        clockOutBtn.style.opacity = '0.5';
        clockOutBtn.innerHTML = '<i class="fas fa-check"></i> Clocked Out';
    }

    // Compute rendered hours
    computeRenderedHours();
}

// Compute rendered hours
function computeRenderedHours() {
    const timeInInputs = document.querySelectorAll('input[name="time_in"]');
    const timeOutInputs = document.querySelectorAll('input[name="time_out"]');
    const hoursDisplay = document.getElementById('hoursDisplay');
    const renderedHoursValue = document.getElementById('renderedHoursValue');

    let timeIn = null;
    let timeOut = null;

    for (let input of timeInInputs) {
        if (input.offsetParent !== null) {
            timeIn = input;
            break;
        }
    }

    for (let input of timeOutInputs) {
        if (input.offsetParent !== null) {
            timeOut = input;
            break;
        }
    }

    if (timeIn && timeOut && timeIn.value && timeOut.value) {
        const [inHours, inMinutes] = timeIn.value.split(':').map(Number);
        const [outHours, outMinutes] = timeOut.value.split(':').map(Number);

        const inTotalMinutes = inHours * 60 + inMinutes;
        const outTotalMinutes = outHours * 60 + outMinutes;

        let totalMinutes = outTotalMinutes - inTotalMinutes;

        // Handle overnight shifts
        if (totalMinutes < 0) {
            totalMinutes += 24 * 60;
        }

        let hours = totalMinutes / 60;

        // Deduct 1 hour break for shifts > 4 hours
        if (hours > 4) {
            hours -= 1;
        }

        hours = Math.max(0, hours);

        if (hoursDisplay && renderedHoursValue) {
            hoursDisplay.style.display = 'block';
            renderedHoursValue.textContent = hours.toFixed(2);
        }
    }
}

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

    visibleForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Get visible inputs for form data
        const date = getVisibleInput('date');
        const timeIn = getVisibleInput('time_in');
        const timeOut = getVisibleInput('time_out');
        const remarks = getVisibleInput('remarks');

        // Validate time_in is present before submitting
        if (!timeIn || !timeIn.value) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please clock in before saving attendance.'
            });
            return;
        }

        // Validate time_in format
        const timeInValue = timeIn.value;
        const timeInRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        if (!timeInRegex.test(timeInValue)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid time format. Please use HH:MM format. Current value: ' + timeInValue
            });
            return;
        }

        const formData = new FormData();
        formData.append('date', date ? date.value : '');
        formData.append('time_in', timeInValue);
        formData.append('time_out', timeOut ? timeOut.value : '');
        formData.append('remarks', remarks ? remarks.value : '');
        formData.append('_token', '{{ csrf_token() }}');

        // Debug: log the form data
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(key, ':', value);
        }
        
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

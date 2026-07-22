@extends('layouts.app')

@section('title', 'Add Attendance')

@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR    = $user->isHR();
    $roleBtnClass = $isAdmin ? 'btn-error' : ($isHR ? 'btn-info' : 'btn-primary');
@endphp

{{-- Header --}}
<div class="mb-6">
    <a href="{{ route('employee-attendance.index') }}"
       class="text-base-content/60 no-underline text-sm inline-flex items-center gap-1.5 mb-2 hover:text-primary">
        <i class="icon-[tabler--arrow-left]"></i> Back to Attendance
    </a>
    <div>
        <span class="badge badge-soft badge-info mb-2">
            <i class="icon-[tabler--plus]"></i> Add Attendance
        </span>
    </div>
    <h2 class="text-lg font-bold text-base-content mt-2 mb-1">Record Attendance</h2>
    <p class="text-base-content/60 m-0">
        Add your time-in/time-out record for a specific date
    </p>
</div>

<div class="card bg-base-100 border border-base-300 p-0 overflow-hidden max-w-[600px]">
    <div class="px-6 py-5 border-b border-base-300">
        <h3 class="text-sm font-bold text-base-content m-0">Attendance Details</h3>
    </div>

    <form id="attendanceForm" class="p-6">
        @csrf
        <input type="hidden" name="employee_id" value="{{ $employee->id }}">

        <div class="mb-5">
            <label class="label text-sm font-semibold text-base-content">Date</label>
            <input type="date" name="date" readonly
                   value="{{ $todayAttendance ? $todayAttendance->date->format('Y-m-d') : '' }}"
                   class="input input-bordered w-full bg-base-200">
            <p class="text-base-content/60 text-xs mt-1">Auto-set when you clock in</p>
        </div>

        <div class="grid grid-cols-2 gap-5 mb-5">
            <div>
                <label class="label text-sm font-semibold text-base-content">Time In</label>
                <input type="time" name="time_in"
                       value="{{ $todayAttendance && $todayAttendance->time_in ? (is_string($todayAttendance->time_in) ? substr($todayAttendance->time_in, 0, 5) : $todayAttendance->time_in->format('H:i')) : '' }}"
                       {{ $todayAttendance && $todayAttendance->time_in ? 'readonly' : '' }}
                       class="input input-bordered w-full bg-base-200">
                <p class="text-base-content/60 text-xs mt-1">Auto-set when you clock in</p>
            </div>
            <div>
                <label class="label text-sm font-semibold text-base-content">Time Out</label>
                <input type="time" name="time_out" readonly
                       value="{{ $todayAttendance && $todayAttendance->time_out ? (is_string($todayAttendance->time_out) ? substr($todayAttendance->time_out, 0, 5) : $todayAttendance->time_out->format('H:i')) : '' }}"
                       class="input input-bordered w-full bg-base-200">
                <p class="text-base-content/60 text-xs mt-1">Auto-set when you clock out</p>
            </div>
        </div>

        <div class="flex gap-3 mb-6">
            <button type="button" id="clockInBtn" onclick="clockIn()"
                    {{ $todayAttendance && $todayAttendance->time_in ? 'disabled' : '' }}
                    class="btn btn-success flex-1 disabled:opacity-50">
                <i class="icon-[tabler--login-2]"></i>
                {{ $todayAttendance && $todayAttendance->time_in ? 'Clocked In' : 'Clock In' }}
            </button>
            <button type="button" id="clockOutBtn" onclick="clockOut()"
                    {{ $todayAttendance && $todayAttendance->time_out ? 'disabled' : ($todayAttendance && $todayAttendance->time_in ? '' : 'disabled') }}
                    class="btn btn-warning flex-1 disabled:opacity-50">
                <i class="icon-[tabler--logout-2]"></i>
                {{ $todayAttendance && $todayAttendance->time_out ? 'Clocked Out' : 'Clock Out' }}
            </button>
        </div>

        @if($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out)
        @php
            $timeInParts = explode(':', $todayAttendance->time_in);
            $expectedHours = (intval($timeInParts[0]) + 9) % 24;
            $expectedMinutes = $timeInParts[1];
            $period = $expectedHours >= 12 ? 'PM' : 'AM';
            $displayHours = $expectedHours % 12;
            $displayHours = $displayHours ? $displayHours : 12;
            $expectedTimeOut12Hour = $displayHours . ':' . $expectedMinutes . ' ' . $period;
        @endphp
        <div class="flex items-center gap-2 mb-6">
            <div class="tooltip [--placement:top]">
                <span class="tooltip-toggle text-warning text-2xl cursor-help">
                    <i class="icon-[tabler--clock]"></i>
                </span>
                <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
                    <span class="tooltip-body bg-neutral/95 shadow-md rounded-lg px-3 py-2.5 text-xs normal-case text-neutral-content font-medium w-64 block">
                        <span class="block font-semibold mb-1">Expected Clock Out Time</span>
                        <span class="block text-sm mb-1"><strong>{{ $expectedTimeOut12Hour }}</strong></span>
                        <span class="block text-neutral-content/70">9 hours after clock in, including 1-hour lunch break</span>
                        <span class="block mt-2 pt-2 border-t border-neutral-content/20 text-[11px] text-neutral-content/60">
                            Click "Clock Out" to record actual time
                        </span>
                    </span>
                </span>
            </div>
            <span class="text-base-content/60 text-sm">Hover to see expected clock out time</span>
        </div>
        @endif

        <div class="mb-6">
            <label class="label text-sm font-semibold text-base-content">Remarks</label>
            <input type="text" name="remarks"
                   value="{{ $todayAttendance ? $todayAttendance->remarks : '' }}"
                   placeholder="Optional notes (e.g., worked from home, late arrival, etc.)"
                   class="input input-bordered w-full">
        </div>

        <div class="bg-success/10 border border-success/20 rounded-lg p-4 mb-6">
            <div class="text-sm font-semibold text-success mb-2 flex items-center gap-1.5">
                <i class="icon-[tabler--info-circle]"></i> Computation Rules
            </div>
            <ul class="m-0 pl-5 text-sm text-success/90 list-disc">
                <li>Less than 4 hours = 0 days</li>
                <li>4-8 hours = 0.5 days</li>
                <li>8 hours or more = 1 day</li>
                <li>1 hour break is automatically deducted for shifts &gt; 4 hours</li>
            </ul>
            <div id="hoursDisplay" class="hidden mt-3 pt-3 border-t border-success/20 text-sm font-semibold text-success">
                <i class="icon-[tabler--calculator]"></i> Rendered Hours: <span id="renderedHoursValue">0.00</span> hrs
            </div>
        </div>

        <div class="bg-warning/10 border border-warning/20 rounded-lg p-4 mb-6">
            <div class="text-sm font-semibold text-warning mb-2 flex items-center gap-1.5">
                <i class="icon-[tabler--alert-triangle]"></i> Auto Clock-Out
            </div>
            <p class="m-0 text-sm text-warning/90">
                Attendance will automatically clock out at 9 hours (including 1-hour break). Any time beyond 9 hours will not be recorded.
            </p>
        </div>

        <div class="flex gap-3">
            <button type="submit" id="saveAttendanceBtn" class="btn {{ $roleBtnClass }} flex-1">
                <i class="icon-[tabler--device-floppy]"></i> Save Attendance
            </button>
            <a href="{{ route('employee-attendance.index') }}" class="btn btn-soft">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
const getVisibleElement = (elements) => {
    for (let element of elements) {
        if (element.offsetParent !== null) {
            return element;
        }
    }
    return elements[0];
};

function clockIn() {
    const dateInputs = document.querySelectorAll('input[name="date"]');
    const timeInInputs = document.querySelectorAll('input[name="time_in"]');
    const clockInBtn = document.getElementById('clockInBtn');
    const clockOutBtn = document.getElementById('clockOutBtn');

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

    for (let input of timeInInputs) {
        if (input.offsetParent !== null) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = `${hours}:${minutes}`;
            break;
        }
    }

    if (clockInBtn) {
        clockInBtn.disabled = true;
        clockInBtn.classList.add('opacity-50');
        clockInBtn.innerHTML = '<i class="icon-[tabler--check]"></i> Clocked In';
    }
    if (clockOutBtn) {
        clockOutBtn.disabled = false;
        clockOutBtn.classList.remove('opacity-50');
    }
}

function clockOut() {
    const timeOutInputs = document.querySelectorAll('input[name="time_out"]');
    const clockOutBtn = document.getElementById('clockOutBtn');

    for (let input of timeOutInputs) {
        if (input.offsetParent !== null) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = `${hours}:${minutes}`;
            break;
        }
    }

    if (clockOutBtn) {
        clockOutBtn.disabled = true;
        clockOutBtn.classList.add('opacity-50');
        clockOutBtn.innerHTML = '<i class="icon-[tabler--check]"></i> Clocked Out';
    }

    computeRenderedHours();
}

function computeRenderedHours() {
    const timeInInputs = document.querySelectorAll('input[name="time_in"]');
    const timeOutInputs = document.querySelectorAll('input[name="time_out"]');
    const hoursDisplay = document.getElementById('hoursDisplay');
    const renderedHoursValue = document.getElementById('renderedHoursValue');

    let timeIn = null;
    let timeOut = null;

    for (let input of timeInInputs) {
        if (input.offsetParent !== null) { timeIn = input; break; }
    }
    for (let input of timeOutInputs) {
        if (input.offsetParent !== null) { timeOut = input; break; }
    }

    if (timeIn && timeOut && timeIn.value && timeOut.value) {
        const [inHours, inMinutes] = timeIn.value.split(':').map(Number);
        const [outHours, outMinutes] = timeOut.value.split(':').map(Number);

        const inTotalMinutes = inHours * 60 + inMinutes;
        const outTotalMinutes = outHours * 60 + outMinutes;

        let totalMinutes = outTotalMinutes - inTotalMinutes;
        if (totalMinutes < 0) totalMinutes += 24 * 60;

        let hours = totalMinutes / 60;
        if (hours > 4) hours -= 1;
        hours = Math.max(0, hours);

        if (hoursDisplay && renderedHoursValue) {
            hoursDisplay.classList.remove('hidden');
            renderedHoursValue.textContent = hours.toFixed(2);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('#attendanceForm');
    if (forms.length === 0) return;

    const visibleForm = getVisibleElement(forms);
    if (!visibleForm) return;

    const getVisibleInput = (name) => {
        const inputs = document.querySelectorAll(`input[name="${name}"]`);
        for (let input of inputs) {
            if (input.offsetParent !== null) return input;
        }
        return inputs[0];
    };

    visibleForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const date = getVisibleInput('date');
        const timeIn = getVisibleInput('time_in');
        const timeOut = getVisibleInput('time_out');
        const remarks = getVisibleInput('remarks');

        if (!timeIn || !timeIn.value) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Please clock in before saving attendance.' });
            return;
        }

        const timeInValue = timeIn.value;
        const timeInRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        if (!timeInRegex.test(timeInValue)) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Invalid time format. Please use HH:MM format. Current value: ' + timeInValue });
            return;
        }

        const formData = new FormData();
        formData.append('date', date ? date.value : '');
        formData.append('time_in', timeInValue);
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
            if (data.success) {
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
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(error => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save attendance: ' + error.message });
        });
    });
});
</script>
@endsection
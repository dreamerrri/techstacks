@extends('layouts.app')

@section('title', 'Create Payroll Period')
@section('breadcrumb')
    <a href="{{ route('employees.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Manage Employees</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('manual-payroll-attendance.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Attendance</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Create Payroll Period</span>
@endsection
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
        <a href="{{ route('manual-payroll-attendance.index') }}"
           style="color:#6b7280; text-decoration:none; font-size:14px; display:inline-flex; align-items:center; gap:6px; margin-bottom:8px;">
            <i class="fas fa-arrow-left"></i> Back to Payroll Periods
        </a>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-calendar-plus"></i> Create Payroll Period
        </div>
        <h2 style="margin:8px 0 4px 0;">New Payroll Period</h2>
        <p style="color:#6b7280; margin:0;">Pick a start date — end date and pay date are computed automatically</p>
    </div>
</div>

<div style="max-width:600px;">
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 25px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="margin:0;">Payroll Period Details</h3>
            <span id="phase_badge"
                  style="display:none; padding:4px 14px; border-radius:20px; font-size:12px; font-weight:600;"></span>
        </div>

        <form action="{{ route('payroll-periods.store') }}" method="POST"
              style="padding:25px;" data-color="{{ $color }}">
            @csrf

            {{-- Cutoff Start (only user input) --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">
                    Cutoff Start Date
                </label>
                <input type="date" name="cutoff_start" id="cutoff_start" required
                       value="{{ old('cutoff_start') }}"
                       style="width:100%; padding:10px 12px; border:1px solid {{ $errors->has('cutoff_start') ? '#dc2626' : '#d1d5db' }}; border-radius:6px; font-size:14px;">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">
                    The period will cover 15 days starting from this date.
                </p>
                @error('cutoff_start')
                    <p style="color:#dc2626; font-size:13px; margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cutoff End (read-only preview) --}}
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">
                    Cutoff End Date
                    <span style="font-weight:400; color:#9ca3af; font-size:12px;">— auto</span>
                </label>
                <input type="text" id="preview_end" disabled placeholder="Computed after picking start date"
                       style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:14px; background:#f9fafb; color:#6b7280;">
            </div>

            {{-- Payroll Date (read-only preview) --}}
            <div style="margin-bottom:28px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">
                    Payroll Date
                    <span style="font-weight:400; color:#9ca3af; font-size:12px;">— auto (5 days after end)</span>
                </label>
                <input type="text" id="preview_payroll" disabled placeholder="Computed after picking start date"
                       style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:14px; background:#f9fafb; color:#6b7280;">
            </div>

            {{-- Summary card (hidden until date is picked) --}}
            <div id="period_summary"
                 style="display:none; background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:14px 16px; margin-bottom:24px;">
                <p style="margin:0 0 6px 0; font-size:13px; font-weight:600; color:#0369a1;">
                    <i class="fas fa-info-circle"></i> Period Summary
                </p>
                <p id="summary_text" style="margin:0; font-size:13px; color:#0c4a6e; line-height:1.6;"></p>
            </div>

            {{-- Hidden fields submitted to backend --}}
            <input type="hidden" name="cutoff_end" id="cutoff_end">
            <input type="hidden" name="payroll_date" id="payroll_date">

            <div style="display:flex; gap:12px;">
                <button type="submit" id="submit_btn" disabled
                        style="flex:1; padding:12px 20px; background:#9ca3af; color:white; border:none; border-radius:6px; cursor:not-allowed; font-size:14px; font-weight:600; transition:background 0.2s;">
                    <i class="fas fa-save"></i> Create Payroll Period
                </button>
                <a href="{{ route('manual-payroll-attendance.index') }}"
                   style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var btnColor = '{{ $color }}';

    function fmt(d) {
        return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function toInputDate(d) {
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + mm + '-' + dd;
    }

    function computeDates(val) {
        if (!val) { resetPreviews(); return; }

        var parts   = val.split('-');
        var year    = parseInt(parts[0]);
        var month   = parseInt(parts[1]);
        var day     = parseInt(parts[2]);

        var start   = new Date(year, month - 1, day);
        var isP1    = day <= 15;
        var end, payroll;

        if (isP1) {
    // Phase 1: 1st to 15th, payroll on the 20th
    end     = new Date(year, month - 1, 15);
    payroll = new Date(year, month - 1, 20);
} else {
    // Phase 2: 16th to last day of month, payroll on 5th of next month
    var lastDay = new Date(year, month, 0);
    end     = lastDay;
    payroll = new Date(year, month, 5);
}

        document.querySelectorAll('#preview_end').forEach(function(el)   { el.value = fmt(end); });
        document.querySelectorAll('#preview_payroll').forEach(function(el){ el.value = fmt(payroll); });
        document.querySelectorAll('#cutoff_end').forEach(function(el)    { el.value = toInputDate(end); });
        document.querySelectorAll('#payroll_date').forEach(function(el)  { el.value = toInputDate(payroll); });

        document.querySelectorAll('#phase_badge').forEach(function(badge) {
            badge.textContent      = isP1 ? '1st Half' : '2nd Half';
            badge.style.display    = 'inline-block';
            badge.style.background = isP1 ? '#dbeafe' : '#ede9fe';
            badge.style.color      = isP1 ? '#1e40af' : '#6d28d9';
        });

        var summaryHTML =
            '<strong>Start:</strong> '    + fmt(start)   + '<br>' +
            '<strong>End:</strong> '      + fmt(end)     + '<br>' +
            '<strong>Pay Date:</strong> ' + fmt(payroll) + '<br>' +
            '<strong>Phase:</strong> '    + (isP1 ? '1st Half' : '2nd Half');

        document.querySelectorAll('#summary_text').forEach(function(el)   { el.innerHTML = summaryHTML; });
        document.querySelectorAll('#period_summary').forEach(function(el) { el.style.display = 'block'; });

        document.querySelectorAll('#submit_btn').forEach(function(btn) {
            btn.disabled         = false;
            btn.style.background = btnColor;
            btn.style.cursor     = 'pointer';
        });
    }

    function resetPreviews() {
        document.querySelectorAll('#preview_end').forEach(function(el)    { el.value = ''; });
        document.querySelectorAll('#preview_payroll').forEach(function(el){ el.value = ''; });
        document.querySelectorAll('#cutoff_end').forEach(function(el)     { el.value = ''; });
        document.querySelectorAll('#payroll_date').forEach(function(el)   { el.value = ''; });
        document.querySelectorAll('#phase_badge').forEach(function(el)    { el.style.display = 'none'; });
        document.querySelectorAll('#period_summary').forEach(function(el) { el.style.display = 'none'; });
        document.querySelectorAll('#submit_btn').forEach(function(btn) {
            btn.disabled         = true;
            btn.style.background = '#9ca3af';
            btn.style.cursor     = 'not-allowed';
        });
    }

    document.querySelectorAll('#cutoff_start').forEach(function(input) {
        input.addEventListener('change', function () {
            document.querySelectorAll('#cutoff_start').forEach(function(el) { el.value = input.value; });
            computeDates(input.value);
        });
        input.addEventListener('input', function () {
            document.querySelectorAll('#cutoff_start').forEach(function(el) { el.value = input.value; });
            computeDates(input.value);
        });
    });

    var firstInput = document.getElementById('cutoff_start');
    if (firstInput && firstInput.value) { computeDates(firstInput.value); }
}());
</script>

@endsection

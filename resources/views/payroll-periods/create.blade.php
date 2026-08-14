@extends('layouts.app')

@section('title', 'Create Payroll Period')

@section('content')

{{-- SHINKU PLS COME HOME --}}

  <div class="mb-5">
        <a href="{{ route('manual-payroll-attendance.index') }}" 
        class="text-subtle no-underline text-sm inline-flex items-center gap-1.5 mb-2 hover:text-primary">
             <i class="icon-[tabler--arrow-left]"></i> Back to Payroll Periods
        </a>
    </div>

    <div>
        <span class="badge badge-soft badge-info mb-2">
            <i class="icon-[tabler--calendar-plus]"></i> Create Payroll Period
        </span>
    </div>
    <h2 class="text-lg font-bold text-base-content mt-2 mb-1">New Payroll Period</h2>
    <p class="text-subtle m-0">Pick a start date — end date and pay date are computed automatically</p>
</div>

<div class="max-w-[600px]">
    <div class="card bg-base-100 border border-base-300 p-0 overflow-hidden">
        <div class="px-6 py-5 border-b border-base-300 flex items-center justify-between">
            <h3 class="text-sm font-bold text-base-content m-0">Payroll Period Details</h3>
            <span id="phase_badge" class="badge hidden"></span>
        </div>

        <form action="{{ route('payroll-periods.store') }}" method="POST" class="p-6">
            @csrf

            {{-- Cutoff Start (only user input) --}}
            <div class="mb-5">
                <label class="label text-sm font-semibold text-base-content">
                    Cutoff Start Date
                </label>
                <input type="date" name="cutoff_start" id="cutoff_start" required
                       value="{{ old('cutoff_start') }}"
                       class="input input-bordered w-full {{ $errors->has('cutoff_start') ? 'border-error' : '' }}">
                <p class="text-subtle text-xs mt-1">
                    The period will cover 15 days starting from this date.
                </p>
                @error('cutoff_start')
                    <p class="text-error text-sm mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cutoff End (read-only preview) --}}
            <div class="mb-5">
                <label class="label text-sm font-semibold text-base-content">
                    Cutoff End Date
                    <span class="font-normal text-faint text-xs">— auto</span>
                </label>
                <input type="text" id="preview_end" disabled placeholder="Computed after picking start date"
                       class="input input-bordered w-full bg-base-200">
            </div>

            {{-- Payroll Date (read-only preview) --}}
            <div class="mb-7">
                <label class="label text-sm font-semibold text-base-content">
                    Payroll Date
                    <span class="font-normal text-faint text-xs">— auto (5 days after end)</span>
                </label>
                <input type="text" id="preview_payroll" disabled placeholder="Computed after picking start date"
                       class="input input-bordered w-full bg-base-200">
            </div>

            {{-- Summary card (hidden until date is picked) --}}
            <div id="period_summary" class="hidden bg-info/10 border border-info/20 rounded-lg px-4 py-3.5 mb-6">
                <p class="m-0 mb-1.5 text-sm font-semibold text-info flex items-center gap-1.5">
                    <i class="icon-[tabler--info-circle]"></i> Period Summary
                </p>
                <p id="summary_text" class="m-0 text-sm text-info/90 leading-relaxed"></p>
            </div>

            {{-- Hidden fields submitted to backend --}}
            <input type="hidden" name="cutoff_end" id="cutoff_end">
            <input type="hidden" name="payroll_date" id="payroll_date">

            <div class="flex gap-3">
                <button type="submit" id="submit_btn" disabled class="btn btn-soft btn-primary flex-1 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="icon-[tabler--device-floppy]"></i> Create Payroll Period
                </button>
                <a href="{{ route('manual-payroll-attendance.index') }}" class="btn btn-soft btn-error">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {

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

        var parts = val.split('-');
        var year  = parseInt(parts[0]);
        var month = parseInt(parts[1]);
        var day   = parseInt(parts[2]);

        var start = new Date(year, month - 1, day);
        var isP1  = day <= 15;
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

        // Phase badge: toggle real theme classes instead of setting inline hex —
        // no JS-side color values to keep in sync with the theme at all.
        document.querySelectorAll('#phase_badge').forEach(function(badge) {
            badge.textContent = isP1 ? '1st Half' : '2nd Half';
            badge.classList.remove('hidden', 'badge-soft', 'badge-info', 'badge-secondary');
            badge.classList.add('badge-soft', isP1 ? 'badge-info' : 'badge-secondary');
        });

        var summaryHTML =
            '<strong>Start:</strong> '    + fmt(start)   + '<br>' +
            '<strong>End:</strong> '      + fmt(end)     + '<br>' +
            '<strong>Pay Date:</strong> ' + fmt(payroll) + '<br>' +
            '<strong>Phase:</strong> '    + (isP1 ? '1st Half' : '2nd Half');

        document.querySelectorAll('#summary_text').forEach(function(el)   { el.innerHTML = summaryHTML; });
        document.querySelectorAll('#period_summary').forEach(function(el) { el.classList.remove('hidden'); });

        document.querySelectorAll('#submit_btn').forEach(function(btn) {
            btn.disabled = false;
        });
    }

    function resetPreviews() {
        document.querySelectorAll('#preview_end').forEach(function(el)    { el.value = ''; });
        document.querySelectorAll('#preview_payroll').forEach(function(el){ el.value = ''; });
        document.querySelectorAll('#cutoff_end').forEach(function(el)     { el.value = ''; });
        document.querySelectorAll('#payroll_date').forEach(function(el)   { el.value = ''; });
        document.querySelectorAll('#phase_badge').forEach(function(el)    { el.classList.add('hidden'); });
        document.querySelectorAll('#period_summary').forEach(function(el) { el.classList.add('hidden'); });
        document.querySelectorAll('#submit_btn').forEach(function(btn)    { btn.disabled = true; });
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
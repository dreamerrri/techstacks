@extends('layouts.app')

@section('title', 'Manual Payroll Attendance Encoding')
@section('breadcrumb')
    <span>Manage Employees</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-medium">Attendance</span>
@endsection

@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR    = $user->isHR();
@endphp

{{-- Header --}}
<div class="flex justify-between items-center flex-wrap gap-3 mb-6">
    <div>
        <span class="badge badge-soft badge-info mb-2">
            <i class="icon-[ph--keyboard-fill]"></i> Manual Payroll Attendance Encoding
        </span>
        <p class="text-gray-500 m-0">
            Manually encode attendance totals, overtime, allowances, and deductions for payroll processing.
        </p>
    </div>

    <div class="flex items-center gap-2">
        @if($isAdmin)
            <a href="{{ route('payroll-periods.archived') }}" class="btn btn-soft btn-neutral whitespace-nowrap">
                <i class="icon-[ph--archive-fill]"></i> Archived
            </a>
        @endif
        @if($isAdmin || $isHR)
            <a href="{{ route('payroll-periods.create') }}" class="btn btn-soft btn-error whitespace-nowrap">
                <i class="icon-[ph--plus-fill]"></i> Create Payroll Period
            </a>
        @endif
    </div>
</div>

{{-- Payroll Periods List --}}
<div class="card bg-base-100 shadow-sm overflow-hidden p-0">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-base font-bold text-gray-800 m-0">Payroll Periods</h2>
        <p class="text-gray-500 text-sm mt-1 mb-0">Select a payroll period to start encoding attendance</p>
    </div>

    {{-- Filters --}}
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <form method="GET" action="{{ route('manual-payroll-attendance.index') }}">
            <div class="flex flex-wrap gap-3 items-end">
                {{-- Year Filter --}}
                <div style="min-width: 120px;">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                    <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="">All Years</option>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Month Filter --}}
                <div style="min-width: 140px;">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
                    <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="">All Months</option>
                        @foreach($availableMonths as $month)
                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Phase Filter --}}
                <div style="min-width: 140px;">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phase</label>
                    <select name="phase" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="">All Phases</option>
                        <option value="1" {{ request('phase') == '1' ? 'selected' : '' }}>1st Half (1-15)</option>
                        <option value="2" {{ request('phase') == '2' ? 'selected' : '' }}>2nd Half (16-End)</option>
                    </select>
                </div>

                {{-- Clear Filters --}}
                @if(request()->hasAny(['year', 'month', 'phase']))
                <button type="button" onclick="window.location.href='{{ route('manual-payroll-attendance.index') }}'"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                    <i class="icon-[ph--x-fill]"></i> Clear
                </button>
                @endif
            </div>
        </form>
    </div>
      

    @if($periods->count() > 0)
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($periods as $period)
                    @if($period)
                        <div id="period-row-{{ $period->id }}"
                             class="border border-gray-200 rounded-xl p-5 cursor-pointer transition-all hover:border-red-400 hover:shadow-md"
                             onclick="window.location.href='{{ route('manual-payroll-attendance.period', $period) }}'">

                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="font-semibold text-gray-800 text-base">
                                        {{ $period->cutoff_start ? $period->cutoff_start->format('M d') : 'N/A' }}
                                        -
                                        {{ $period->cutoff_end ? $period->cutoff_end->format('M d, Y') : 'N/A' }}
                                    </div>
                                    <div class="text-gray-500 text-xs mt-1">
                                        Payroll Date: {{ $period->payroll_date ? $period->payroll_date->format('M d, Y') : 'N/A' }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="badge {{ $period->status === 'finalized' ? 'badge-soft badge-success' : 'badge-soft badge-warning' }} whitespace-nowrap">
                                        {{ ucfirst($period->status) }}
                                    </span>
                                    @if($isAdmin)
                                    <button onclick="event.stopPropagation(); confirmDelete({{ $period->id }}, '{{ $period->period_label }}', '{{ route('payroll-periods.archive', $period) }}')"
        class="btn btn-soft btn-error btn-xs">
    <i class="icon-[ph--trash-fill]"></i>
</button>
                                    @endif
                                </div>
                            </div>

                            <div class="flex gap-4 mt-3 pt-3 border-t border-gray-100 text-xs">
                                <div>
                                    <span class="text-gray-500">Employees Encoded:</span>
                                    <span class="font-semibold text-gray-800 ml-1">{{ $period->payrollInputs ? $period->payrollInputs->count() : 0 }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Total Gross:</span>
                                    <span class="font-semibold text-emerald-600 ml-1">₱{{ number_format($period->total_gross_pay ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @else
        <div class="py-16 px-6 text-center">
            <i class="icon-[ph--calendar-fill] text-5xl text-gray-300 mb-4 block"></i>
            <h3 class="text-gray-500 m-0 mb-2">No Payroll Periods Found</h3>
            <p class="text-gray-400 m-0">Create a payroll period to start encoding attendance.</p>
            @if($isAdmin || $isHR)
                <a href="{{ route('payroll-periods.create') }}" class="btn btn-soft btn-error mt-4">
                    <i class="icon-[ph--plus-fill]"></i> Create Payroll Period
                </a>
            @endif
        </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
    function confirmDelete(periodId, label, url) {
    Swal.fire({
        title: 'Archive Payroll Period?',
        text: `"${label}" and all its encoded attendance data will be archived.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor:  '#6b7280',
        confirmButtonText:  'Yes, archive it',
        cancelButtonText:   'Cancel',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById(`period-row-${periodId}`)?.remove();
            window.notyf.success(data.message);
        })
        .catch(() => window.notyf.error('Something went wrong.'));
    });
}
</script>
@endsection 
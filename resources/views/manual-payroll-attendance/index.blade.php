@extends('layouts.app')

@section('title', 'Manual Payroll Attendance Encoding')


@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR    = $user->isHR();
@endphp

<x-table-card action="{{ route('manual-payroll-attendance.index') }}">
    <x-slot:title>
        <x-dot-loader />
        <p class="text-base-content"> Payroll Attendance Encoding</p>
        <x-info-tooltip>
            Manually encode attendance totals, overtime, allowances, and deductions for payroll processing.
        </x-info-tooltip>
    </x-slot:title>

    <x-slot:actions>
        <div class="flex items-center gap-2">
            @if($isAdmin)
                <a href="{{ route('payroll-periods.archived') }}" class="btn btn-soft btn-primary whitespace-nowrap">
                    <i class="icon-[ph--archive-fill]"></i> Archived
                </a>
            @endif
            @if($isAdmin || $isHR)
                <a href="{{ route('payroll-periods.create') }}" class="btn btn-soft  btn-error whitespace-nowrap">
                    <i class="icon-[ph--plus-fill]"></i> Create Payroll Period
                </a>
            @endif
        </div>
    </x-slot:actions>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="form-control">
                <label class="label label-text">Year</label>
                <select id="filter-year" class="select select-bordered select-sm w-32">
                    <option value="">All Years</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control">
                <label class="label label-text">Month</label>
                <select id="filter-month" class="select select-bordered select-sm w-40">
                    <option value="">All Months</option>
                    @foreach($availableMonths as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-control">
                <label class="label label-text">Phase</label>
                <select id="filter-phase" class="select select-bordered select-sm w-36">
                    <option value="">All Phases</option>
                    <option value="1" {{ request('phase') == '1' ? 'selected' : '' }}>1st Half</option>
                    <option value="2" {{ request('phase') == '2' ? 'selected' : '' }}>2nd Half</option>
                </select>
            </div>

            <div class="form-control">
                <label class="label label-text">Status</label>
                <select id="filter-status" class="select select-bordered select-sm w-36">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="finalized" {{ request('status') == 'finalized' ? 'selected' : '' }}>Finalized</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button id="clear-filters" class="btn btn-soft btn-neutral btn-sm">
                    <i class="icon-[ph--x-fill]"></i> Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Payroll Periods List --}}
    <div class="card bg-base-100 shadow-sm overflow-hidden p-0">

        @if($periods->count() > 0)
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($periods as $period)
                        @if($period)
                            <div id="period-row-{{ $period->id }}"
                                 class="border border-primary rounded-xl p-5 cursor-pointer transition-all hover:border-error hover:shadow-md"
                                 onclick="window.location.href='{{ route('manual-payroll-attendance.period', $period) }}'">

                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="font-semibold text-base-content text-base">
                                            {{ $period->cutoff_start ? $period->cutoff_start->format('M d') : 'N/A' }}
                                            -
                                            {{ $period->cutoff_end ? $period->cutoff_end->format('M d, Y') : 'N/A' }}
                                        </div>
                                        <div class="text-base-content/60 text-xs mt-1">
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

                                <div class="flex gap-4 mt-3 pt-3 border-t border-base-200 text-xs">
                                    <div>
                                        <span class="text-base-content/60">Employees Encoded:</span>
                                        <span class="font-semibold text-base-content ml-1">{{ $period->payrollInputs ? $period->payrollInputs->count() : 0 }}</span>
                                    </div>
                                    <div>
                                        <span class="text-base-content/60">Total Gross:</span>
                                        <span class="font-semibold text-success ml-1">₱{{ number_format($period->total_gross_pay ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @else
            <div class="py-16 px-6 text-center">
                <i class="icon-[ph--calendar-fill] text-5xl text-base-content/30 mb-4 block"></i>
                <h3 class="text-base-content/60 m-0 mb-2">No Payroll Periods Found</h3>
                <p class="text-base-content/40 m-0">Create a payroll period to start encoding attendance.</p>
                @if($isAdmin || $isHR)
                    <a href="{{ route('payroll-periods.create') }}" class="btn btn-soft btn-error mt-4">
                        <i class="icon-[ph--plus-fill]"></i> Create Payroll Period
                    </a>
                @endif
            </div>
        @endif
    </div>
</x-table-card>
@endsection

@section('scripts')
<script>
    // Filter functionality
    const filterYear = document.getElementById('filter-year');
    const filterMonth = document.getElementById('filter-month');
    const filterPhase = document.getElementById('filter-phase');
    const filterStatus = document.getElementById('filter-status');
    const clearFiltersBtn = document.getElementById('clear-filters');

    function applyFilters() {
        const params = new URLSearchParams();
        if (filterYear.value) params.append('year', filterYear.value);
        if (filterMonth.value) params.append('month', filterMonth.value);
        if (filterPhase.value) params.append('phase', filterPhase.value);
        if (filterStatus.value) params.append('status', filterStatus.value);

        fetch(`{{ route('manual-payroll-attendance.index') }}?${params.toString()}`, {
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newPeriodsList = doc.querySelector('.card.bg-base-100.shadow-sm.overflow-hidden.p-0');
            const currentPeriodsList = document.querySelector('.card.bg-base-100.shadow-sm.overflow-hidden.p-0');
            if (newPeriodsList && currentPeriodsList) {
                currentPeriodsList.innerHTML = newPeriodsList.innerHTML;
            }
        })
        .catch(() => window.notyf.error('Failed to apply filters.'));
    }

    // Add change event listeners to all filters
    [filterYear, filterMonth, filterPhase, filterStatus].forEach(select => {
        select.addEventListener('change', applyFilters);
    });

    // Clear filters
    clearFiltersBtn.addEventListener('click', () => {
        filterYear.value = '';
        filterMonth.value = '';
        filterPhase.value = '';
        filterStatus.value = '';
        applyFilters();
    });

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
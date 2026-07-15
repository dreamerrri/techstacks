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
        <x-dot-loader /> Payroll Attendance Encoding
        <x-info-tooltip>
            Manually encode attendance totals, overtime, allowances, and deductions for payroll processing.
        </x-info-tooltip>
    </x-slot:title>

    <x-slot:actions>
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
    </x-slot:actions>

    {{-- Payroll Periods List --}}
    <div class="card bg-base-100 shadow-sm overflow-hidden p-0">

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

                                <div class="flex gap-4 mt-3 pt-3 border-t border-gray-100 text-xs">
                                    <div>
                                        <span class="text-base-content/60">Employees Encoded:</span>
                                        <span class="font-semibold text-base-content ml-1">{{ $period->payrollInputs ? $period->payrollInputs->count() : 0 }}</span>
                                    </div>
                                    <div>
                                        <span class="text-base-content/60">Total Gross:</span>
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
@extends('layouts.app')

@section('title', 'Archived Payroll Periods')
@section('breadcrumb')
    <span>Manage Employees</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <a href="{{ route('manual-payroll-attendance.index') }}" class="text-white/55 no-underline">Attendance</a>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-medium">Archived Periods</span>
@endsection

@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();
@endphp

{{-- Header --}}
<div class="flex justify-between items-center flex-wrap gap-3 mb-6">
    <div>
        <a href="{{ route('manual-payroll-attendance.index') }}"
           class="text-gray-500 text-sm no-underline inline-flex items-center gap-1 mb-2">
            <i class="fas fa-arrow-left"></i> Back to Attendance
        </a>
        <span class="badge badge-soft badge-neutral mb-2 block w-fit">
            <i class="fas fa-archive"></i> Archived Payroll Periods
        </span>
        <p class="text-gray-500 m-0">Archived periods are read-only and can be restored if needed.</p>
    </div>
</div>

{{-- Archived Periods List --}}
<div class="card bg-base-100 shadow-sm overflow-hidden p-0">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-base font-bold text-gray-800 m-0">Archived Periods</h2>
        <p class="text-gray-500 text-sm mt-1 mb-0">{{ $periods->count() }} archived payroll {{ Str::plural('period', $periods->count()) }}</p>
    </div>

    @if($periods->count() > 0)
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($periods as $period)
                    <div id="period-row-{{ $period->id }}"
                         class="border border-gray-200 rounded-xl p-5 transition-all hover:border-gray-400 hover:shadow-md">

                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="font-semibold text-gray-800 text-base">
                                    {{ $period->cutoff_start->format('M d') }} - {{ $period->cutoff_end->format('M d, Y') }}
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Payroll Date: {{ $period->payroll_date->format('M d, Y') }}
                                </div>
                                <div class="text-gray-400 text-xs mt-1">
                                    Created by: {{ $period->createdBy?->name ?? 'N/A' }}
                                </div>
                            </div>
                            <span class="badge badge-soft badge-neutral whitespace-nowrap">Archived</span>
                        </div>

                        <div class="flex gap-4 mt-3 pt-3 border-t border-gray-100 text-xs mb-4">
                            <div>
                                <span class="text-gray-500">Employees Encoded:</span>
                                <span class="font-semibold text-gray-800 ml-1">{{ $period->payrollInputs ? $period->payrollInputs->count() : 0 }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Total Gross:</span>
                                <span class="font-semibold text-emerald-600 ml-1">₱{{ number_format($period->total_gross_pay ?? 0, 2) }}</span>
                            </div>
                        </div>

                        @if($isAdmin)
                            <button onclick="confirmRestore({{ $period->id }}, '{{ $period->period_label }}', '{{ route('payroll-periods.restore', $period) }}')"
                                    class="btn btn-soft btn-success btn-sm w-full">
                                <i class="fas fa-undo"></i> Restore
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="py-16 px-6 text-center">
            <i class="fas fa-archive text-5xl text-gray-300 mb-4 block"></i>
            <h3 class="text-gray-500 m-0 mb-2">No Archived Periods</h3>
            <p class="text-gray-400 m-0">Archived payroll periods will appear here.</p>
        </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
function confirmRestore(periodId, label, url) {
    Swal.fire({
        title: 'Restore Payroll Period?',
        text: `"${label}" will be restored to draft status.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor:  '#6b7280',
        confirmButtonText:  'Yes, restore it',
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
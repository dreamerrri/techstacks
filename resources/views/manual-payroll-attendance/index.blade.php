@extends('layouts.app')

@section('title', 'Manual Payroll Attendance Encoding')
@section('breadcrumb')
    <span>Manage Employees</span>
    <i class="fas fa-chevron-right text-xs"></i>
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
            <i class="fas fa-keyboard"></i> Manual Payroll Attendance Encoding
        </span>
        <p class="text-gray-500 m-0">
            Manually encode attendance totals, overtime, allowances, and deductions for payroll processing.
        </p>
    </div>
    @if($isAdmin || $isHR)
        <a href="{{ route('payroll-periods.create') }}" class="btn btn-soft btn-error whitespace-nowrap">
            <i class="fas fa-plus"></i> Create Payroll Period
        </a>
    @endif
</div>

{{-- Payroll Periods List --}}
<div class="card bg-base-100 shadow-sm overflow-hidden p-0">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-base font-bold text-gray-800 m-0">Payroll Periods</h2>
        <p class="text-gray-500 text-sm mt-1 mb-0">Select a payroll period to start encoding attendance</p>
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
                                        <button onclick="event.stopPropagation(); confirmDelete({{ $period->id }}, '{{ $period->period_label }}')"
                                                class="btn btn-soft btn-error btn-xs">
                                            <i class="fas fa-trash"></i>
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
            <i class="fas fa-calendar-alt text-5xl text-gray-300 mb-4 block"></i>
            <h3 class="text-gray-500 m-0 mb-2">No Payroll Periods Found</h3>
            <p class="text-gray-400 m-0">Create a payroll period to start encoding attendance.</p>
            @if($isAdmin || $isHR)
                <a href="{{ route('payroll-periods.create') }}" class="btn btn-soft btn-error mt-4">
                    <i class="fas fa-plus"></i> Create Payroll Period
                </a>
            @endif
        </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
function confirmDelete(periodId, label) {
    Swal.fire({
        title: 'Delete Payroll Period?',
        text: `"${label}" and all its encoded attendance data will be permanently deleted.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`/payroll-periods/${periodId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            window.notyf.success(data.message);
            document.getElementById(`period-row-${periodId}`)?.remove();
        })
        .catch(() => {
            window.notyf.error('Something went wrong.');
        });
    });
}
</script>
@endsection 
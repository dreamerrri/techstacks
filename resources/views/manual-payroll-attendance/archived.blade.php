@extends('layouts.app')

@section('title', 'Archived Payroll Periods')


@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();
@endphp

              <div class="mb-5">
        <a href="{{ route('manual-payroll-attendance.index') }}" 
        class="text-base-content/60 no-underline text-sm inline-flex items-center gap-1.5 mb-2 hover:text-primary">
             <i class="icon-[tabler--arrow-left]"></i> Back to Attendance page
        </a>
    </div>
            

<x-table-card>
    <x-slot:title>
        <x-dot-loader/><p class="text-base-content"> Arhived Payroll Periods</p>
        <x-info-tooltip>
           Archived periods are read-only and can be restored if needed
        </x-info-tooltip>
    </x-slot:title>


{{-- Archived Periods List --}}

    <div class="px-6 py-5 border-b border-base-300">
        <h2 class="text-base font-bold text-base-content m-0">Archived Periods</h2>
        <p class="text-base-content/60 text-sm mt-1 mb-0">{{ $periods->count() }} archived payroll {{ Str::plural('period', $periods->count()) }}</p>
    </div>

    @if($periods->count() > 0)
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($periods as $period)
                    <div id="period-row-{{ $period->id }}"
                         class="border border-base-300 rounded-xl p-5 transition-all hover:border-base-400 hover:shadow-md">

                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="font-semibold text-base-content text-base">
                                    {{ $period->cutoff_start->format('M d') }} - {{ $period->cutoff_end->format('M d, Y') }}
                                </div>
                                <div class="text-base-content/60 text-xs mt-1">
                                    Payroll Date: {{ $period->payroll_date->format('M d, Y') }}
                                </div>
                                <div class="text-base-content/40 text-xs mt-1">
                                    Created by: {{ $period->createdBy?->name ?? 'N/A' }}
                                </div>
                            </div>
                            <span class="badge badge-soft badge-neutral whitespace-nowrap">Archived</span>
                        </div>

                        <div class="flex gap-4 mt-3 pt-3 border-t border-base-200 text-xs mb-4">
                            <div>
                                <span class="text-base-content/60">Employees Encoded:</span>
                                <span class="font-semibold text-base-content ml-1">{{ $period->payrollInputs ? $period->payrollInputs->count() : 0 }}</span>
                            </div>
                            <div>
                                <span class="text-base-content/60">Total Gross:</span>
                                <span class="font-semibold text-success ml-1">₱{{ number_format($period->total_gross_pay ?? 0, 2) }}</span>
                            </div>
                        </div>

                        @if($isAdmin)
                            <button onclick="confirmRestore({{ $period->id }}, '{{ $period->period_label }}', '{{ route('payroll-periods.restore', $period) }}')"
                                    class="btn btn-soft  btn-success btn-sm w-full">
                                <i class="icon-[ph--arrow-counter-clockwise-fill]"></i> Restore
                            </button>
                        @endif
                    </div>
                @endforeach
      </div>
      </div>
    @else
        <div class="py-16 px-6 text-center">
            <i class="icon-[ph--archive-fill] text-5xl text-base-content/30 mb-4 block"></i>
            <h3 class="text-base-content/60 m-0 mb-2">No Archived Periods</h3>
            <p class="text-base-content/40 m-0">Archived payroll periods will appear here.</p>
        </div>
    @endif


   

</x-table-card>
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
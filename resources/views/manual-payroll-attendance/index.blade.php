@extends('layouts.app')

@section('title', 'Manual Payroll Attendance Encoding')

@section('content')

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
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-keyboard"></i> Manual Payroll Attendance Encoding
        </div>
        <p style="color:#6b7280; margin:0;">
            Manually encode attendance totals, overtime, allowances, and deductions for payroll processing.
        </p>
    </div>
    @if($isAdmin || $isHR)
    <a href="{{ route('payroll-periods.create') }}"
       style="padding:10px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
        <i class="fas fa-plus"></i> Create Payroll Period
    </a>
    @endif
</div>

{{-- Payroll Periods List --}}
<div class="card" style="padding:0; overflow:hidden;">
    <div style="padding:20px 25px; border-bottom:1px solid #e5e7eb;">
        <h2 style="margin:0;">Payroll Periods</h2>
        <p style="color:#6b7280; margin:8px 0 0 0; font-size:14px;">Select a payroll period to start encoding attendance</p>
    </div>

    @if($periods->count() > 0)
    <div style="padding:25px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px;">
            @foreach($periods as $period)
            @if($period)
            <div style="border:1px solid #e5e7eb; border-radius:8px; padding:20px; transition:all 0.2s; cursor:pointer;"
                 onclick="window.location.href='{{ route('manual-payroll-attendance.period', $period) }}'"
                 onmouseover="this.style.borderColor='{{ $color }}'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                 onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
    <div>
        <div style="font-weight:600; color:#1f2937; font-size:16px;">
            {{ $period->cutoff_start ? $period->cutoff_start->format('M d') : 'N/A' }} - {{ $period->cutoff_end ? $period->cutoff_end->format('M d, Y') : 'N/A' }}
        </div>
        <div style="color:#6b7280; font-size:13px; margin-top:4px;">
            Payroll Date: {{ $period->payroll_date ? $period->payroll_date->format('M d, Y') : 'N/A' }}
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:8px;">
        <span style="padding:4px 12px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; 
            {{ $period->status === 'finalized' ? 'background:#dcfce7; color:#166534;' : 'background:#fef3c7; color:#92400e;' }}">
            {{ ucfirst($period->status) }}
        </span>
        @if($isAdmin)
        <button
            onclick="event.stopPropagation(); confirmDelete({{ $period->id }}, '{{ $period->period_label }}')"
            style="padding:4px 8px; background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; border-radius:5px; font-size:12px; cursor:pointer; line-height:1;">
            <i class="fas fa-trash"></i>
        </button>
        @endif
    </div>
</div>
                
                <div style="display:flex; gap:16px; margin-top:12px; padding-top:12px; border-top:1px solid #f3f4f6; font-size:13px;">
                    <div>
                        <span style="color:#6b7280;">Employees Encoded:</span>
                        <span style="font-weight:600; color:#1f2937; margin-left:4px;">{{ $period->payrollInputs ? $period->payrollInputs->count() : 0 }}</span>
                    </div>
                    <div>
                        <span style="color:#6b7280;">Total Gross:</span>
                        <span style="font-weight:600; color:#10b981; margin-left:4px;">₱{{ number_format($period->total_gross_pay ?? 0, 2) }}</span>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div style="padding:60px 25px; text-align:center;">
        <i class="fas fa-calendar-alt" style="font-size:48px; color:#d1d5db; margin-bottom:16px;"></i>
        <h3 style="color:#6b7280; margin:0 0 8px 0;">No Payroll Periods Found</h3>
        <p style="color:#9ca3af; margin:0;">Create a payroll period to start encoding attendance.</p>
        @if($isAdmin || $isHR)
        <a href="{{ route('payroll-periods.create') }}"
           style="display:inline-block; margin-top:16px; padding:10px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; text-decoration:none;">
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
    @if($isAdmin)
    Swal.fire({
        title: 'Delete Payroll Period?',
        html: `<span style="color:#6b7280; font-size:14px;"><strong>${label}</strong> and all its encoded attendance data will be permanently deleted.</span>`,
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
            Toast.fire({ icon: 'success', title: data.message });
            document.getElementById(`period-row-${periodId}`)?.remove();
        })
        .catch(() => {
            Toast.fire({ icon: 'error', title: 'Something went wrong.' });
        });
    });
    @else
    return;
    @endif
}
</script>

@endsection



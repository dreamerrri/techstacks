@extends('layouts.app')

@section('title', 'Payroll Details - ' . $employee->full_name)


@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR    = $user->isHR();
    $payroll = $payrollData ?? [];
@endphp

{{-- Top nav --}}
<div class="flex justify-between items-center flex-wrap gap-3 mb-5">
    <a href="{{ route('payroll.index') }}" 
           class="back-link text-gray-500 no-underline text-sm hover:text-emerald-600 flex items-center gap-1">
.        <i class="icon-[ph--arrow-left-fill]"></i> Back to Payroll List
    </a>
    @if(($payroll['gross_pay'] ?? 0) > 0)
        <a href="{{ route('payroll.payslip', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
           class="btn btn-soft btn-info btn-sm">
            <i class="icon-[ph--file-arrow-down-fill]"></i> Download Payslip
        </a>
    @endif
</div>

{{-- Profile Header --}}
<div class="card bg-base-100 shadow-sm p-5 flex items-center gap-5 flex-wrap mb-5">
    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
    </div>
    <div class="flex-1">
        <h2 class="text-xl font-bold text-gray-800 m-0 mb-1">{{ $employee->full_name }}</h2>
        <p class="text-gray-500 m-0">{{ $employee->position }} — {{ $employee->department }}</p>
        <div class="flex flex-wrap gap-3 mt-1 text-xs text-gray-500">
            <span><i class="icon-[ph--identification-badge-fill] w-3.5"></i> {{ $employee->employee_id }}</span>
            <span><i class=" icon-[ph--calendar-fill] w-3.5"></i> {{ $employee->date_hired->format('M d, Y') }}</span>
            <span><i class="icon-[ph--money-fill] w-3.5"></i> {{ $employee->salary_type }} Salary</span>
        </div>
    </div>
    @php
        $statusClass = match($employee->employment_status) {
            'Regular'      => 'badge-soft badge-success',
            'Probationary' => 'badge-soft badge-warning',
            'Contractual'  => 'badge-soft badge-info',
            'Part-time'    => 'badge-soft badge-neutral',
            default        => 'badge-soft',
        };
    @endphp
    <span class="badge {{ $statusClass }}">{{ $employee->employment_status }}</span>
</div>
{{-- Government IDs --}}
<div class="card bg-base-100 shadow-sm p-5 mb-5">
    <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon-[ph--identification-card-fill] text-red-600"></i> Government IDs
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['SSS Number',        $employee->sss_number,        'icon-[ph--shield-check-fill]', 'text-emerald-600', 'bg-emerald-100'],
            ['PhilHealth Number', $employee->philhealth_number,  'icon-[ph--heart-fill]',        'text-blue-600',    'bg-blue-100'],
            ['Pag-IBIG Number',   $employee->pagibig_number,     'icon-[ph--house-fill]',        'text-amber-500',   'bg-amber-100'],
            ['TIN Number',        $employee->tin_number,         'icon-[ph--receipt-fill]',      'text-violet-600',  'bg-violet-100'],
        ] as [$label, $value, $icon, $color, $bg])
            <div class="card bg-base-100 shadow-sm p-4 text-center">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 {{ $color }} {{ $bg }}">
                    <i class="{{ $icon }} size-5"></i>
                </div>
                <div class="text-xs text-gray-400 uppercase tracking-widest font-medium mb-1">{{ $label }}</div>
                <div class="font-bold font-mono text-gray-800 text-xs break-all">{{ $value ?? '—' }}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
    <div class="card-stat card-stat-green">
        <div class="stat-icon-wrap"><i class="icon-[ph--coins-fill]"></i></div>
        <div class="stat-label">Gross Pay</div>
        <div class="stat-value">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</div>
        <div class="stat-sub">For this cutoff</div>
    </div>
    <div class="card-stat card-stat-red">
        <div class="stat-icon-wrap"><i class="icon-[ph--minus-circle-fill]"></i></div>
        <div class="stat-label">Total Deductions</div>
        <div class="stat-value">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</div>
        <div class="stat-sub">Gov't & Manual Deductions</div>
    </div>
    <div class="card-stat card-stat-net">
        <div class="stat-icon-wrap"><i class="icon-[ph--wallet-fill]"></i></div>
        <div class="stat-label">Net Pay</div>
        <div class="stat-value">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</div>
        <div class="stat-sub">Take-home for this cutoff</div>
    </div>
</div>

{{-- Monthly Breakdown (2nd cutoff only) --}}
@if($selectedPeriod && $selectedPeriod->isSecondHalfOfMonth())
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
    <div class="card bg-base-100 shadow-sm p-5 border-l-4 border-blue-600 bg-blue-50">
        <div class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-3">Monthly Gross Breakdown</div>
        <div class="flex flex-col gap-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">1st Cutoff Gross:</span><span class="font-semibold text-gray-800">₱{{ number_format($payroll['first_cutoff_gross_pay'] ?? 0, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">2nd Cutoff Gross:</span><span class="font-semibold text-gray-800">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</span></div>
            <div class="flex justify-between pt-2 border-t border-blue-200 font-bold text-blue-700"><span>Total Monthly Gross:</span><span>₱{{ number_format($payroll['total_monthly_gross_pay'] ?? 0, 2) }}</span></div>
        </div>
    </div>
    <div class="card bg-base-100 shadow-sm p-5 border-l-4 border-red-700 bg-red-50">
        <div class="text-xs font-bold text-red-700 uppercase tracking-wider mb-3">Monthly Contributions</div>
        <div class="flex flex-col gap-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">1st Cutoff Gov't:</span><span class="font-semibold text-gray-800">₱{{ number_format($payroll['first_cutoff_contributions'] ?? 0, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">2nd Cutoff Gov't:</span><span class="font-semibold text-gray-800">₱{{ number_format($payroll['current_cutoff_contributions'] ?? 0, 2) }}</span></div>
            <div class="flex justify-between pt-2 border-t border-red-200 font-bold text-red-700"><span>Total Monthly Gov't:</span><span>₱{{ number_format(($payroll['first_cutoff_contributions'] ?? 0) + ($payroll['current_cutoff_contributions'] ?? 0), 2) }}</span></div>
        </div>
    </div>
    <div class="card bg-base-100 shadow-sm p-5 border-l-4 border-green-700 bg-green-50">
        <div class="text-xs font-bold text-green-700 uppercase tracking-wider mb-3">Monthly Net Pay Breakdown</div>
        <div class="flex flex-col gap-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">1st Cutoff Net:</span><span class="font-semibold text-gray-800">₱{{ number_format($payroll['first_cutoff_net_pay'] ?? 0, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">2nd Cutoff Net:</span><span class="font-semibold text-gray-800">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</span></div>
            <div class="flex justify-between pt-2 border-t border-green-200 font-bold text-green-700"><span>Total Monthly Net:</span><span>₱{{ number_format($payroll['total_monthly_net_pay'] ?? 0, 2) }}</span></div>
        </div>
    </div>
</div>
@endif

{{-- Detail Cards Grid --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    {{-- Attendance-Based Earnings --}}
    <div class="card bg-base-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-emerald-100 flex items-center justify-center text-emerald-600 text-xs flex-shrink-0">
                <i class="icon-[ph--clock-fill]"></i>
            </span>
            Attendance-Based Earnings
        </h2>
        <div class="flex flex-col text-sm">
            @foreach([
                ['Base Pay',           $payroll['attendance_data']['regular_hours'] ?? 0 .' hrs × ₱'. number_format($payroll['hourly_rate'] ?? 0, 2) .'/hr', $payroll['base_pay'] ?? 0, 'text-gray-800', ''],
                ['Weekend Pay',        ($payroll['attendance_data']['weekends_worked'] ?? 0) .' weekend days × 0.30 × ₱'. number_format($payroll['daily_rate'] ?? 0, 2) .'/day', $payroll['weekend_pay'] ?? 0, 'text-emerald-600', '+'],
                ['Overtime Pay',       ($payroll['attendance_data']['overtime_hours'] ?? 0) .' OT hrs × 1.25 × ₱'. number_format($payroll['hourly_rate'] ?? 0, 2) .'/hr', $payroll['overtime_pay'] ?? 0, 'text-emerald-600', '+'],
                ['Night Differential', ($payroll['attendance_data']['night_diff_hours'] ?? 0) .' ND hrs × 1.10 × ₱'. number_format($payroll['hourly_rate'] ?? 0, 2) .'/hr', $payroll['night_differential_pay'] ?? 0, 'text-emerald-600', '+'],
                ['Holiday Pay',        ($payroll['attendance_data']['holiday_days'] ?? 0) .' holiday days × 2 × ₱'. number_format($payroll['daily_rate'] ?? 0, 2) .'/day', $payroll['holiday_pay'] ?? 0, 'text-emerald-600', '+'],
                ['Benefits',           'Total active benefits', $payroll['benefits'] ?? 0, 'text-emerald-600', '+'],
            ] as [$label, $sub, $val, $cls, $prefix])
                <div class="flex justify-between items-start py-2.5 border-b border-gray-100">
                    <div>
                        <div class="text-gray-500">{{ $label }}</div>
                        <div class="text-xs text-gray-400">{{ $sub }}</div>
                    </div>
                    <span class="font-semibold {{ $cls }} ml-4 whitespace-nowrap">{{ $prefix }}₱{{ number_format($val, 2) }}</span>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between items-center mt-3 pt-3 border-t-2 border-gray-200 font-bold text-sm">
            <span class="text-gray-800">Total Earnings</span>
            <span class="text-emerald-600">₱{{ number_format(($payroll['base_pay'] ?? 0) + ($payroll['weekend_pay'] ?? 0) + ($payroll['overtime_pay'] ?? 0) + ($payroll['night_differential_pay'] ?? 0) + ($payroll['holiday_pay'] ?? 0) + ($payroll['benefits'] ?? 0), 2) }}</span>
        </div>
    </div>

    {{-- Government Contributions & Deductions --}}
    <div class="card bg-base-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-blue-100 flex items-center justify-center text-blue-600 text-xs flex-shrink-0">
                <i class="icon-[ph--bank-fill]"></i>
            </span>
            Government Contributions & Deductions
        </h2>
        <div class="flex flex-col text-sm">
            @foreach([
                ['SSS Contribution',       "4.5% of gross pay (capped at ₱900)",               $payroll['sss_contribution'] ?? 0],
                ['PhilHealth Contribution',"2.25% of gross pay (capped at ₱1,500)",             $payroll['philhealth_contribution'] ?? 0],
                ['Pag-IBIG Contribution',  "2% of gross pay (capped at ₱100)",                  $payroll['pagibig_contribution'] ?? 0],
                ['Late Deduction',         ($payroll['attendance_data']['late_hours'] ?? 0) .' late hrs × ₱'. number_format($payroll['hourly_rate'] ?? 0, 2) .'/hr', $payroll['late_deduction'] ?? 0],
                ['Allowances',             'Total active allowances',                            $payroll['allowances'] ?? 0],
                ['Manual Deductions',      'Deductions from manual payroll attendance',          $payroll['manual_deductions'] ?? 0],
            ] as [$label, $sub, $val])
                <div class="flex justify-between items-start py-2.5 border-b border-gray-100">
                    <div>
                        <div class="text-gray-500">{{ $label }}</div>
                        <div class="text-xs text-gray-400">{{ $sub }}</div>
                    </div>
                    <span class="font-semibold text-red-600 ml-4 whitespace-nowrap">-₱{{ number_format($val, 2) }}</span>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between items-center mt-3 pt-3 border-t-2 border-gray-200 font-bold text-sm">
            <span class="text-gray-800">Net Contributions & Deductions</span>
            <span class="text-red-600">-₱{{ number_format(($payroll['sss_contribution'] ?? 0) + ($payroll['philhealth_contribution'] ?? 0) + ($payroll['pagibig_contribution'] ?? 0) + ($payroll['late_deduction'] ?? 0) + ($payroll['manual_deductions'] ?? 0) + ($payroll['allowances'] ?? 0), 2) }}</span>
        </div>
    </div>

    {{-- Tax Information --}}
    <div class="card bg-base-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-red-100 flex items-center justify-center text-red-600 text-xs flex-shrink-0">
                <i class="icon-[ph--file-text-fill]"></i>
            </span>
            Tax Information
        </h2>
        <div class="flex flex-col text-sm">
            <div class="flex justify-between items-start py-2.5 border-b border-gray-100">
                <div>
                    <div class="text-gray-500">Taxable Income</div>
                    <div class="text-xs text-gray-400">After government contributions</div>
                </div>
                <span class="font-semibold text-gray-800 ml-4">₱{{ number_format($payroll['taxable_income'] ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between items-start py-2.5">
                <div>
                    <div class="text-gray-500">Withholding Tax</div>
                    <div class="text-xs text-gray-400">Based on Philippine tax brackets</div>
                </div>
                <span class="font-semibold text-red-600 ml-4">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</span>
            </div>
        </div>
        <div class="mt-4 p-4 bg-gray-50 rounded-xl text-xs text-gray-500 leading-relaxed">
            <strong class="text-gray-700">Tax Bracket Reference:</strong><br>
            • ₱0 – ₱20,832: 0%<br>
            • ₱20,833 – ₱33,333: 20%<br>
            • ₱33,334 – ₱66,667: 25%<br>
            • ₱66,668 – ₱166,667: 30%<br>
            • ₱166,668 – ₱666,667: 32%<br>
            • Above ₱666,667: 35%
        </div>
    </div>

    {{-- Pay Summary (full width) --}}
    <div class="card bg-base-100 shadow-sm p-5 md:col-span-3">
        <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="icon-[ph--receipt-fill] text-red-600"></i> Pay Summary
        </h2>
        <div class="flex flex-col text-sm">
            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                <span class="text-gray-500">Gross Pay</span>
                <span class="font-semibold text-gray-800">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                <span class="text-red-600">Less: Government Contributions & Deductions</span>
                <span class="font-semibold text-red-600">-₱{{ number_format(($payroll['sss_contribution'] ?? 0) + ($payroll['philhealth_contribution'] ?? 0) + ($payroll['pagibig_contribution'] ?? 0) + ($payroll['late_deduction'] ?? 0) + ($payroll['manual_deductions'] ?? 0), 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b-2 border-gray-200">
                <span class="text-red-600">Less: Withholding Tax</span>
                <span class="font-semibold text-red-600">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-4">
                <span class="text-xl font-bold text-gray-800">NET PAY</span>
                <span class="text-3xl font-extrabold text-emerald-600">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

</div>

@endsection
@extends('layouts.app')

@section('title', 'Payroll Details - ' . $employee->full_name)

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $colorDark = $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2');
    $payroll = $payrollData ?? [];
@endphp

{{-- Top nav --}}
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
    <a href="{{ route('payroll.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
        <i class="fas fa-arrow-left"></i> Back to Payroll List
    </a>
</div>

{{-- Profile Header --}}
<div class="card" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
    <div style="width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $colorDark }}); display:flex; align-items:center; justify-content:center; color:white; font-size:28px; font-weight:700; flex-shrink:0;">
        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
    </div>
    <div style="flex:1;">
        <h2 style="margin:0 0 4px; font-size:22px;">{{ $employee->full_name }}</h2>
        <p style="margin:0; color:#6b7280;">{{ $employee->position }} — {{ $employee->department }}</p>
        <div style="display:flex; flex-wrap:wrap; gap:12px; margin-top:6px; font-size:13px; color:#6b7280;">
            <span><i class="fas fa-id-badge" style="width:14px;"></i> {{ $employee->employee_id }}</span>
            <span><i class="fas fa-calendar" style="width:14px;"></i> {{ $employee->date_hired->format('M d, Y') }}</span>
            <span><i class="fas fa-money-bill-wave" style="width:14px;"></i> {{ $employee->salary_type }} Salary</span>
        </div>
    </div>
    <div style="text-align:right;">
        <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;
            {{ $employee->employment_status === 'Regular'      ? 'background:#d1fae5; color:#065f46;'  : '' }}
            {{ $employee->employment_status === 'Probationary' ? 'background:#fef3c7; color:#92400e;'  : '' }}
            {{ $employee->employment_status === 'Contractual'  ? 'background:#dbeafe; color:#1e40af;'  : '' }}
            {{ $employee->employment_status === 'Part-time'    ? 'background:#f3f4f6; color:#374151;'  : '' }}
        ">{{ $employee->employment_status }}</span>
    </div>
</div>

{{-- Stat Cards Row --}}
<div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-bottom:20px;">
    {{-- Gross Pay --}}
    <div class="card card-stat card-stat-green" style="margin-bottom:0;">
        <div class="stat-icon-wrap"><i class="fas fa-coins"></i></div>
        <div class="stat-label">Gross Pay</div>
        <div class="stat-value">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</div>
        <div class="stat-sub">Based on attendance data</div>
    </div>

    {{-- Total Deductions --}}
    <div class="card card-stat card-stat-red" style="margin-bottom:0;">
        <div class="stat-icon-wrap"><i class="fas fa-minus-circle"></i></div>
        <div class="stat-label">Total Deductions</div>
        <div class="stat-value">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</div>
        <div class="stat-sub">{{ number_format(($payroll['total_deductions'] ?? 0) / max($payroll['gross_pay'] ?? 1, 1) * 100, 1) }}% of gross</div>
    </div>

    {{-- Net Pay --}}
    <div class="card card-stat card-stat-net" style="margin-bottom:0;">
        <div class="stat-icon-wrap"><i class="fas fa-wallet"></i></div>
        <div class="stat-label">Net Pay</div>
        <div class="stat-value">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</div>
        <div class="stat-sub">Take-home amount</div>
    </div>

</div>

{{-- Detail Cards Grid --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">

    {{-- Attendance-Based Earnings --}}
    <div class="card">
        <h2><i class="fas fa-clock" style="background:rgba(16,185,129,0.1); color:#10b981;"></i> Attendance-Based Earnings</h2>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 0; color:#6b7280; width:60%; vertical-align:top;">
                    Base Pay
                    <div style="font-size:12px; color:#9ca3af;">{{ $payroll['attendance_data']['regular_hours'] ?? 0 }} hrs × ₱{{ number_format($payroll['hourly_rate'] ?? 0, 2) }}/hr</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#1f2937; text-align:right;">₱{{ number_format($payroll['base_pay'] ?? 0, 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    Overtime Pay
                    <div style="font-size:12px; color:#9ca3af;">{{ $payroll['attendance_data']['overtime_hours'] ?? 0 }} OT hrs × 1.25 × ₱{{ number_format($payroll['hourly_rate'] ?? 0, 2) }}/hr</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#10b981; text-align:right;">+₱{{ number_format($payroll['overtime_pay'] ?? 0, 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    Night Differential
                    <div style="font-size:12px; color:#9ca3af;">{{ $payroll['attendance_data']['night_differential_hours'] ?? 0 }} night hrs × 10% × ₱{{ number_format($payroll['hourly_rate'] ?? 0, 2) }}/hr</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#10b981; text-align:right;">+₱{{ number_format($payroll['night_differential_pay'] ?? 0, 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    Holiday Pay
                    <div style="font-size:12px; color:#9ca3af;">{{ $payroll['attendance_data']['regular_holiday_worked'] ?? 0 }} holiday days × 2 × ₱{{ number_format($payroll['daily_rate'] ?? 0, 2) }}/day</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#10b981; text-align:right;">+₱{{ number_format($payroll['holiday_pay'] ?? 0, 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    Late Deduction
                    <div style="font-size:12px; color:#9ca3af;">{{ $payroll['attendance_data']['late_hours'] ?? 0 }} late hrs × ₱{{ number_format($payroll['hourly_rate'] ?? 0, 2) }}/hr</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#dc2626; text-align:right;">-₱{{ number_format($payroll['late_deduction'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    Allowance & Benefits
                    <div style="font-size:12px; color:#9ca3af;">Total active allowances and benefits</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#10b981; text-align:right;">+₱{{ number_format($payroll['allowance_benefits'] ?? 0, 2) }}</td>
            </tr>
        </table>
        <div style="margin-top:10px; padding-top:10px; border-top:2px solid #e5e7eb; display:flex; justify-content:space-between; font-weight:700; font-size:15px;">
            <span style="color:#1f2937;">Total Earnings</span>
            <span style="color:#10b981;">₱{{ number_format(($payroll['base_pay'] ?? 0) + ($payroll['overtime_pay'] ?? 0) + ($payroll['night_differential_pay'] ?? 0) + ($payroll['holiday_pay'] ?? 0) + ($payroll['allowance_benefits'] ?? 0), 2) }}</span>
        </div>
    </div>

    {{-- Government Contributions --}}
    <div class="card">
        <h2><i class="fas fa-landmark" style="background:rgba(37,99,235,0.1); color:#2563eb;"></i> Government Contributions</h2>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    SSS Contribution
                    <div style="font-size:12px; color:#9ca3af;">4.5% of gross pay (capped at ₱900)</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#dc2626; text-align:right;">-₱{{ number_format($payroll['sss_contribution'] ?? 0, 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    PhilHealth Contribution
                    <div style="font-size:12px; color:#9ca3af;">2.25% of gross pay (capped at ₱1,500)</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#dc2626; text-align:right;">-₱{{ number_format($payroll['philhealth_contribution'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    Pag-IBIG Contribution
                    <div style="font-size:12px; color:#9ca3af;">2% of gross pay (capped at ₱100)</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#dc2626; text-align:right;">-₱{{ number_format($payroll['pagibig_contribution'] ?? 0, 2) }}</td>
            </tr>
        </table>
        <div style="margin-top:10px; padding-top:10px; border-top:2px solid #e5e7eb; display:flex; justify-content:space-between; font-weight:700; font-size:15px;">
            <span style="color:#1f2937;">Total Contributions</span>
            <span style="color:#dc2626;">-₱{{ number_format(($payroll['sss_contribution'] ?? 0) + ($payroll['philhealth_contribution'] ?? 0) + ($payroll['pagibig_contribution'] ?? 0), 2) }}</span>
        </div>
    </div>

    {{-- Tax Information --}}
    <div class="card">
        <h2><i class="fas fa-file-invoice-dollar" style="background:rgba(220,38,38,0.1); color:#dc2626;"></i> Tax Information</h2>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    Taxable Income
                    <div style="font-size:12px; color:#9ca3af;">After government contributions</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#1f2937; text-align:right;">₱{{ number_format($payroll['taxable_income'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0; color:#6b7280; vertical-align:top;">
                    Withholding Tax
                    <div style="font-size:12px; color:#9ca3af;">Based on Philippine tax brackets</div>
                </td>
                <td style="padding:10px 0; font-weight:600; color:#dc2626; text-align:right;">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</td>
            </tr>
        </table>
        <div style="margin-top:14px; padding:12px; background:#f9fafb; border-radius:6px; font-size:12px; color:#6b7280; line-height:1.7;">
            <strong>Tax Bracket Reference:</strong><br>
            • ₱0 – ₱20,832: 0%<br>
            • ₱20,833 – ₱33,333: 20%<br>
            • ₱33,334 – ₱66,667: 25%<br>
            • ₱66,668 – ₱166,667: 30%<br>
            • ₱166,668 – ₱666,667: 32%<br>
            • Above ₱666,667: 35%
        </div>
    </div>

    {{-- Government IDs (full-width) --}}
    <div class="card" style="grid-column: 1 / -1;">
        <h2><i class="fas fa-id-card" style="background:rgba(220,38,38,0.1); color:#dc2626;"></i> Government IDs</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:16px;">
            @foreach([
                ['SSS Number',       $employee->sss_number,       'fa-shield-alt'],
                ['PhilHealth Number',$employee->philhealth_number, 'fa-heart'],
                ['Pag-IBIG Number',  $employee->pagibig_number,    'fa-home'],
                ['TIN Number',       $employee->tin_number,        'fa-file-invoice'],
            ] as [$label, $value, $icon])
            <div style="background:#f9fafb; padding:16px; border-radius:8px; text-align:center;">
                <div style="color:#dc2626; font-size:20px; margin-bottom:8px;"><i class="fas {{ $icon }}"></i></div>
                <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">{{ $label }}</div>
                <div style="font-weight:600; font-family:monospace; color:#1f2937; font-size:13px; word-break:break-all;">{{ $value ?? '—' }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Pay Summary (full-width) --}}
    <div class="card" style="grid-column: 1 / -1;">
        <h2><i class="fas fa-receipt" style="background:rgba(220,38,38,0.1); color:#dc2626;"></i> Pay Summary</h2>
        <table style="width:100%; border-collapse:collapse; font-size:15px;">
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:12px 0; color:#6b7280;">Gross Pay</td>
                <td style="padding:12px 0; font-weight:600; color:#1f2937; text-align:right;">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:12px 0; color:#dc2626;">Less: Government Contributions</td>
                <td style="padding:12px 0; font-weight:600; color:#dc2626; text-align:right;">-₱{{ number_format(($payroll['sss_contribution'] ?? 0) + ($payroll['philhealth_contribution'] ?? 0) + ($payroll['pagibig_contribution'] ?? 0), 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:12px 0; color:#dc2626;">Less: Withholding Tax</td>
                <td style="padding:12px 0; font-weight:600; color:#dc2626; text-align:right;">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</td>
            </tr>
            <tr style="border-bottom:2px solid #e5e7eb;">
                <td style="padding:12px 0; color:#dc2626;">Less: Late Deductions</td>
                <td style="padding:12px 0; font-weight:600; color:#dc2626; text-align:right;">-₱{{ number_format($payroll['late_deduction'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:16px 0; font-weight:700; color:#1f2937; font-size:18px;">NET PAY</td>
                <td style="padding:16px 0; font-weight:700; color:#10b981; font-size:24px; text-align:right;">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

</div>

@endsection
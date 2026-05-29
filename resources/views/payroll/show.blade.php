@extends('layouts.app')

@section('title', 'Payroll Details - ' . $employee->full_name)

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $payroll = $payrollData ?? [];
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <div style="display:inline-block; background:#fef3c7; color:#92400e; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-money-bill-wave"></i> Payroll Details
        </div>
        <p style="color:#6b7280; margin:0;">Detailed payroll calculation for {{ $employee->full_name }}</p>
    </div>
    <a href="{{ route('payroll.index') }}"
       style="padding:10px 20px; background:#f3f4f6; color:#6b7280; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

{{-- Employee Info Card --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
        <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2') }}); display:flex; align-items:center; justify-content:center; color:white; font-size:32px; font-weight:700; flex-shrink:0;">
            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
        </div>
        <div style="flex:1;">
            <h2 style="margin:0 0 8px 0; color:#1f2937;">{{ $employee->full_name }}</h2>
            <div style="display:flex; flex-wrap:wrap; gap:15px; font-size:14px; color:#6b7280;">
                <span><i class="fas fa-id-badge" style="width:16px;"></i> {{ $employee->employee_id }}</span>
                <span><i class="fas fa-building" style="width:16px;"></i> {{ $employee->department }}</span>
                <span><i class="fas fa-briefcase" style="width:16px;"></i> {{ $employee->position }}</span>
                <span><i class="fas fa-calendar" style="width:16px;"></i> {{ $employee->date_hired->format('M d, Y') }}</span>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="display:inline-block; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; background:#d1fae5; color:#065f46;">
                {{ $employee->employment_status }}
            </div>
            <div style="margin-top:8px; font-size:13px; color:#6b7280;">
                {{ $employee->salary_type }} Salary
            </div>
        </div>
    </div>
</div>

{{-- Payroll Breakdown --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px; margin-bottom:20px;">
    {{-- Gross Pay Card --}}
    <div class="card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:28px; color:#10b981; margin-bottom:8px;"><i class="fas fa-coins"></i></div>
        <div style="font-size:14px; color:#6b7280; margin-bottom:4px;">Monthly Gross Pay</div>
        <div style="font-size:28px; font-weight:700; color:#1f2937;">₱{{ number_format($payroll['monthly_salary'] ?? 0, 2) }}</div>
        <div style="font-size:12px; color:#6b7280; margin-top:4px;">
            Basic: ₱{{ number_format($payroll['basic_salary'] ?? 0, 2) }} ({{ $payroll['salary_type'] ?? 'Monthly' }})
        </div>
    </div>

    {{-- Total Deductions Card --}}
    <div class="card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:28px; color:#dc2626; margin-bottom:8px;"><i class="fas fa-minus-circle"></i></div>
        <div style="font-size:14px; color:#6b7280; margin-bottom:4px;">Total Deductions</div>
        <div style="font-size:28px; font-weight:700; color:#dc2626;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</div>
        <div style="font-size:12px; color:#6b7280; margin-top:4px;">
            {{ number_format(($payroll['total_deductions'] ?? 0) / ($payroll['monthly_salary'] ?? 1) * 100, 1) }}% of gross
        </div>
    </div>

    {{-- Net Pay Card --}}
    <div class="card" style="text-align:center; margin-bottom:0; border:2px solid #10b981;">
        <div style="font-size:28px; color:#10b981; margin-bottom:8px;"><i class="fas fa-wallet"></i></div>
        <div style="font-size:14px; color:#6b7280; margin-bottom:4px;">Net Pay</div>
        <div style="font-size:32px; font-weight:700; color:#10b981;">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</div>
        <div style="font-size:12px; color:#6b7280; margin-top:4px;">
            Take-home amount
        </div>
    </div>
</div>

{{-- Detailed Breakdown --}}
<div class="card">
    <h2 style="margin:0 0 20px 0;">Detailed Breakdown</h2>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(350px, 1fr)); gap:30px;">
        {{-- Government Contributions --}}
        <div>
            <h3 style="margin:0 0 15px 0; color:#1f2937; font-size:18px; border-bottom:2px solid #e5e7eb; padding-bottom:8px;">
                <i class="fas fa-landmark" style="color:#2563eb;"></i> Government Contributions
            </h3>
            
            <div style="margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="color:#6b7280;">SSS Contribution</span>
                    <span style="font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['sss_contribution'] ?? 0, 2) }}</span>
                </div>
                <div style="font-size:12px; color:#9ca3af;">4.5% of monthly salary (capped at ₱900)</div>
            </div>

            <div style="margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="color:#6b7280;">PhilHealth Contribution</span>
                    <span style="font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['philhealth_contribution'] ?? 0, 2) }}</span>
                </div>
                <div style="font-size:12px; color:#9ca3af;">2.25% of monthly salary (capped at ₱1,500)</div>
            </div>

            <div style="margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="color:#6b7280;">Pag-IBIG Contribution</span>
                    <span style="font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['pagibig_contribution'] ?? 0, 2) }}</span>
                </div>
                <div style="font-size:12px; color:#9ca3af;">2% of monthly salary (capped at ₱100)</div>
            </div>

            <div style="padding-top:10px; border-top:1px solid #e5e7eb;">
                <div style="display:flex; justify-content:space-between; font-weight:600;">
                    <span style="color:#1f2937;">Total Contributions</span>
                    <span style="color:#dc2626;">-₱{{ number_format(($payroll['sss_contribution'] ?? 0) + ($payroll['philhealth_contribution'] ?? 0) + ($payroll['pagibig_contribution'] ?? 0), 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Tax Information --}}
        <div>
            <h3 style="margin:0 0 15px 0; color:#1f2937; font-size:18px; border-bottom:2px solid #e5e7eb; padding-bottom:8px;">
                <i class="fas fa-file-invoice-dollar" style="color:#dc2626;"></i> Tax Information
            </h3>
            
            <div style="margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="color:#6b7280;">Taxable Income</span>
                    <span style="font-weight:600; color:#1f2937;">₱{{ number_format($payroll['taxable_income'] ?? 0, 2) }}</span>
                </div>
                <div style="font-size:12px; color:#9ca3af;">After government contributions</div>
            </div>

            <div style="margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="color:#6b7280;">Withholding Tax</span>
                    <span style="font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</span>
                </div>
                <div style="font-size:12px; color:#9ca3af;">Based on Philippine tax brackets</div>
            </div>

            <div style="background:#f9fafb; padding:12px; border-radius:6px; font-size:12px; color:#6b7280;">
                <strong>Tax Bracket Reference:</strong><br>
                • ₱0 - ₱20,832: 0%<br>
                • ₱20,833 - ₱33,333: 20%<br>
                • ₱33,334 - ₱66,667: 25%<br>
                • ₱66,668 - ₱166,667: 30%<br>
                • ₱166,668 - ₱666,667: 32%<br>
                • Above ₱666,667: 35%
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div style="margin-top:30px; padding-top:20px; border-top:2px solid #e5e7eb;">
        <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:16px;">
            <span style="color:#6b7280;">Gross Monthly Pay</span>
            <span style="font-weight:600; color:#1f2937;">₱{{ number_format($payroll['monthly_salary'] ?? 0, 2) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:16px;">
            <span style="color:#dc2626;">Less: Government Contributions</span>
            <span style="font-weight:600; color:#dc2626;">-₱{{ number_format(($payroll['sss_contribution'] ?? 0) + ($payroll['philhealth_contribution'] ?? 0) + ($payroll['pagibig_contribution'] ?? 0), 2) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:16px;">
            <span style="color:#dc2626;">Less: Withholding Tax</span>
            <span style="font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:15px; padding-top:15px; border-top:2px solid #10b981; font-size:20px;">
            <span style="font-weight:700; color:#1f2937;">NET PAY</span>
            <span style="font-weight:700; color:#10b981; font-size:24px;">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</span>
        </div>
    </div>
</div>

{{-- Government IDs --}}
<div class="card">
    <h2 style="margin:0 0 20px 0;">Government IDs</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
        <div style="padding:15px; background:#f9fafb; border-radius:8px;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">SSS Number</div>
            <div style="font-weight:600; color:#1f2937;">{{ $employee->sss_number ?? 'N/A' }}</div>
        </div>
        <div style="padding:15px; background:#f9fafb; border-radius:8px;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">PhilHealth Number</div>
            <div style="font-weight:600; color:#1f2937;">{{ $employee->philhealth_number ?? 'N/A' }}</div>
        </div>
        <div style="padding:15px; background:#f9fafb; border-radius:8px;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Pag-IBIG Number</div>
            <div style="font-weight:600; color:#1f2937;">{{ $employee->pagibig_number ?? 'N/A' }}</div>
        </div>
        <div style="padding:15px; background:#f9fafb; border-radius:8px;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">TIN Number</div>
            <div style="font-weight:600; color:#1f2937;">{{ $employee->tin_number ?? 'N/A' }}</div>
        </div>
    </div>
</div>

@endsection

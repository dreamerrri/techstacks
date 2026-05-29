@extends('layouts.app')

@section('title', 'Payroll Preview')

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <div style="display:inline-block; background:#fef3c7; color:#92400e; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-money-bill-wave"></i> Payroll Preview
        </div>
        <p style="color:#6b7280; margin:0;">
            @if($isAdmin || $isHR)
                View payroll calculations for all employees.
            @else
                View your payroll calculation.
            @endif
        </p>
    </div>
</div>

{{-- Filters (Admin and HR only) --}}
@if($isAdmin || $isHR)
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="{{ route('payroll.index') }}"
          style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:0;">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name, ID..."
               style="flex:1; min-width:160px; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
        <select name="department"
                style="border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        <button type="submit"
                style="padding:8px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px;">
            <i class="fas fa-search"></i> Search
        </button>
        @if(request()->hasAny(['search','department']))
            <a href="{{ route('payroll.index') }}"
               style="padding:8px 16px; background:#f3f4f6; color:#6b7280; border-radius:6px; text-decoration:none; font-size:14px;">
                Clear
            </a>
        @endif
    </form>
</div>
@endif

{{-- Payroll Table --}}
<div class="card">
    <h2 style="margin:0 0 20px 0;">Payroll Summary</h2>

    {{-- Desktop Table --}}
    <div class="user-table-wrapper">
        <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:900px;">
            <thead>
                <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Employee</th>
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Department</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Monthly Salary</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">SSS</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">PhilHealth</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Pag-IBIG</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Tax</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Total Deductions</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Net Pay</th>
                    <th style="padding:12px; text-align:center; color:#6b7280; font-size:12px; text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php
                        $payroll = $payrollData[$employee->id] ?? [];
                    @endphp
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:12px;">
                            <div style="font-weight:600; color:#1f2937;">{{ $employee->full_name }}</div>
                            <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
                        </td>
                        <td style="padding:12px; color:#6b7280;">{{ $employee->department }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#1f2937;">
                            ₱{{ number_format($payroll['monthly_salary'] ?? 0, 2) }}
                        </td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['sss_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['philhealth_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['pagibig_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; font-weight:700; color:#10b981; font-size:16px;">
                            ₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}
                        </td>
                        <td style="padding:12px; text-align:center;">
                            <a href="{{ route('payroll.show', $employee) }}"
                               style="padding:5px 10px; background:#dbeafe; color:#1e40af; border-radius:5px; font-size:12px; text-decoration:none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="padding:40px; text-align:center; color:#9ca3af;">
                            <i class="fas fa-money-bill-wave" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                            No payroll data found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="user-mobile-cards">
        @forelse($employees as $employee)
            @php
                $payroll = $payrollData[$employee->id] ?? [];
            @endphp
            <div class="user-card">
                <div class="user-card-header">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2') }}); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:700; flex-shrink:0;">
                            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $employee->full_name }}</div>
                            <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
                        </div>
                    </div>
                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; background:#fef3c7; color:#92400e;">
                        {{ $employee->employment_status }}
                    </span>
                </div>

                <div style="margin-top:10px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                    <span><i class="fas fa-building" style="width:14px;"></i> {{ $employee->department }}</span>
                    <span><i class="fas fa-briefcase" style="width:14px;"></i> {{ $employee->position }}</span>
                </div>

                <div style="margin-top:12px; padding-top:12px; border-top:1px solid #f3f4f6;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                        <span style="color:#6b7280;">Monthly Salary:</span>
                        <span style="font-weight:600; color:#1f2937;">₱{{ number_format($payroll['monthly_salary'] ?? 0, 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                        <span style="color:#6b7280;">SSS:</span>
                        <span style="color:#dc2626;">-₱{{ number_format($payroll['sss_contribution'] ?? 0, 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                        <span style="color:#6b7280;">PhilHealth:</span>
                        <span style="color:#dc2626;">-₱{{ number_format($payroll['philhealth_contribution'] ?? 0, 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                        <span style="color:#6b7280;">Pag-IBIG:</span>
                        <span style="color:#dc2626;">-₱{{ number_format($payroll['pagibig_contribution'] ?? 0, 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                        <span style="color:#6b7280;">Tax:</span>
                        <span style="color:#dc2626;">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                        <span style="color:#6b7280; font-weight:600;">Total Deductions:</span>
                        <span style="color:#dc2626; font-weight:600;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:700; color:#10b981;">
                        <span>Net Pay:</span>
                        <span>₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</span>
                    </div>
                </div>

                <div class="user-card-meta" style="margin-top:10px; padding-top:10px; border-top:1px solid #f3f4f6;">
                    <a href="{{ route('payroll.show', $employee) }}"
                       style="padding:5px 12px; background:#dbeafe; color:#1e40af; border-radius:5px; font-size:12px; text-decoration:none;">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
        @empty
            <div style="padding:40px; text-align:center; color:#9ca3af;">
                <i class="fas fa-money-bill-wave" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                No payroll data found.
            </div>
        @endforelse
    </div>

    <div style="margin-top:20px;">{{ $employees->links() }}</div>
</div>

@endsection

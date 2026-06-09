@extends('layouts.app')

@section('title', 'Payroll Preview')

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $colorDark = $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2');
@endphp

{{-- Breakdown Modal --}}
<div id="payrollBreakdownModal"
     style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.45); align-items:center; justify-content:center; padding:16px;">
    <div style="background:white; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.2);">

        {{-- Modal header --}}
        <div style="padding:20px 24px 16px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:white; z-index:1; border-radius:16px 16px 0 0;">
            <div>
                <div style="font-size:16px; font-weight:700; color:#1a1a2e;" id="modalEmployeeName">—</div>
                <div style="font-size:12px; color:#6b7280; font-family:monospace;" id="modalEmployeeId">—</div>
            </div>
            <button onclick="closeBreakdownModal()"
                    style="background:#f3f4f6; border:none; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px; color:#6b7280; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal body --}}
        <div style="padding:20px 24px;">

            {{-- Attendance --}}
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; color:#9ca3af; margin-bottom:10px;">Attendance</div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:20px;">
                <div style="background:#f9fafb; border-radius:10px; padding:12px; text-align:center;">
                    <div style="font-size:20px; font-weight:700; color:#1a1a2e;" id="modalDaysWorked">0</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:2px;">Days Worked</div>
                </div>
                <div style="background:#f9fafb; border-radius:10px; padding:12px; text-align:center;">
                    <div style="font-size:20px; font-weight:700; color:#f59e0b;" id="modalOvertimeHours">0</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:2px;">Overtime Hrs</div>
                </div>
                <div style="background:#f9fafb; border-radius:10px; padding:12px; text-align:center;">
                    <div style="font-size:20px; font-weight:700; color:#8b5cf6;" id="modalHolidayDays">0</div>
                    <div style="font-size:11px; color:#6b7280; margin-top:2px;">Holiday Days</div>
                </div>
            </div>

            {{-- Earnings --}}
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; color:#9ca3af; margin-bottom:10px;">Earnings</div>
            <div style="background:#f9fafb; border-radius:10px; padding:4px 0; margin-bottom:20px;">
                <div class="modal-row">
                    <span>Basic Pay</span>
                    <span id="modalBasicPay" style="color:#1a1a2e; font-weight:600;">₱0.00</span>
                </div>
                <div class="modal-row">
                    <span>Overtime Pay</span>
                    <span id="modalOvertimePay" style="color:#f59e0b;">₱0.00</span>
                </div>
                <div class="modal-row">
                    <span>Holiday Pay</span>
                    <span id="modalHolidayPay" style="color:#8b5cf6;">₱0.00</span>
                </div>
                <div class="modal-row">
                    <span>Night Differential</span>
                    <span id="modalNightDiff" style="color:#0ea5e9;">₱0.00</span>
                </div>
                <div class="modal-row">
                    <span>Allowances & Benefits</span>
                    <span id="modalAllowances" style="color:#10b981;">₱0.00</span>
                </div>
                <div class="modal-row" style="border-top:1px solid #e5e7eb; margin-top:4px; padding-top:4px;">
                    <span style="font-weight:700; color:#1a1a2e;">Gross Pay</span>
                    <span id="modalGrossPay" style="color:#1a1a2e; font-weight:700; font-size:15px;">₱0.00</span>
                </div>
            </div>

            {{-- Deductions --}}
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; color:#9ca3af; margin-bottom:10px;">Deductions</div>
            <div style="background:#fff5f5; border-radius:10px; padding:4px 0; margin-bottom:20px;">
                <div class="modal-row">
                    <span>SSS</span>
                    <span id="modalSss" style="color:#dc2626;">₱0.00</span>
                </div>
                <div class="modal-row">
                    <span>PhilHealth</span>
                    <span id="modalPhilHealth" style="color:#dc2626;">₱0.00</span>
                </div>
                <div class="modal-row">
                    <span>Pag-IBIG</span>
                    <span id="modalPagIbig" style="color:#dc2626;">₱0.00</span>
                </div>
                <div class="modal-row">
                    <span>Withholding Tax</span>
                    <span id="modalTax" style="color:#dc2626;">₱0.00</span>
                </div>
                <div class="modal-row" style="border-top:1px solid #fecaca; margin-top:4px; padding-top:4px;">
                    <span style="font-weight:700; color:#dc2626;">Total Deductions</span>
                    <span id="modalTotalDeductions" style="color:#dc2626; font-weight:700; font-size:15px;">₱0.00</span>
                </div>
            </div>

            {{-- Net Pay --}}
            <div style="background:linear-gradient(135deg,#d1fae5,#a7f3d0); border-radius:12px; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:15px; font-weight:700; color:#065f46;">Net Pay</span>
                <span id="modalNetPay" style="font-size:22px; font-weight:800; color:#065f46;">₱0.00</span>
            </div>

        </div>

        {{-- Modal footer --}}
        <div style="padding:16px 24px; border-top:1px solid #e5e7eb; display:flex; gap:8px; justify-content:flex-end;">
            <a id="modalPayslipLink" href="#"
               style="padding:8px 18px; background:#d1fae5; color:#065f46; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <i class="fas fa-file-download"></i> Payslip
            </a>
            <a id="modalDetailLink" href="#"
               style="padding:8px 18px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <i class="fas fa-external-link-alt"></i> Full Details
            </a>
        </div>

    </div>
</div>

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <span class="aurora-badge" style="background:#fef3c7; color:#92400e; margin-bottom:8px;">
            <i class="fas fa-money-bill-wave"></i> Payroll Preview
        </span>
        <p style="color:#6b7280; margin:0;">
            @if($isAdmin || $isHR)
                View payroll calculations for all employees.
            @else
                View your payroll calculation.
            @endif
        </p>
    </div>
</div>

{{-- Filters + Table --}}
<div class="aurora-card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">

    {{-- Sticky header --}}
    <div style="position:sticky; top:0; z-index:10; background:white; padding:20px 28px 0; border-radius:20px 20px 0 0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <h2 class="aurora-card-title" style="margin:0;">
    <i class="fas fa-list"></i> Payroll Summary
</h2>
<button onclick="printPayrollTable()"
        style="padding:8px 18px; background:#1e40af; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
    <i class="fas fa-print"></i> Print
</button>

<button onclick="exportPayrollCSV()"
        style="padding:8px 18px; background:#065f46; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
    <i class="fas fa-file-csv"></i> Export CSV
</button>
        </div>

        @if($isAdmin || $isHR)
        <form method="GET" action="{{ route('payroll.index') }}"
              style="display:flex; flex-wrap:wrap; gap:10px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, ID..."
                   oninput="clearTimeout(this._t); this._t = setTimeout(() => this.closest('form').submit(), 500)"
                   style="flex:1; min-width:160px; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">

            <select name="department"
                    onchange="this.closest('form').submit()"
                    style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>

            {{-- Cutoff Period Filter --}}
            <select name="payroll_period_id"
                    onchange="this.closest('form').submit()"
                    style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none; min-width:230px;">
                <option value="">Latest Cutoff</option>
                @foreach($payrollPeriods as $period)
                    <option value="{{ $period->id }}"
                        {{ (string) request('payroll_period_id') === (string) $period->id ? 'selected' : '' }}>
                        {{ $period->cutoff_start->format('M d') }} – {{ $period->cutoff_end->format('M d, Y') }}
                        ({{ ucfirst($period->status) }})
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-danger btn-sm" style="display:none;">
                <i class="fas fa-search"></i> Search
            </button>

            @if(request()->hasAny(['search', 'department', 'payroll_period_id']))
                <a href="{{ route('payroll.index') }}"
                   style="padding:8px 16px; background:#f3f4f6; color:#6b7280; border-radius:8px; text-decoration:none; font-size:14px;">
                    Clear
                </a>
            @endif
        </form>
        @else
        <div style="border-bottom:1px solid #e5e7eb; margin-bottom:0;"></div>
        @endif
    </div>

    {{-- Active cutoff banner --}}
    @if($selectedPeriod ?? null)
    <div style="padding:10px 28px; background:#eff6ff; border-bottom:1px solid #dbeafe; font-size:13px; color:#1e40af; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <i class="fas fa-calendar-alt"></i>
        <span>Showing payroll for cutoff:</span>
        <strong>{{ $selectedPeriod->cutoff_start->format('M d, Y') }} – {{ $selectedPeriod->cutoff_end->format('M d, Y') }}</strong>
        <span style="padding:2px 10px; border-radius:10px; font-size:11px; font-weight:600;
            {{ $selectedPeriod->status === 'finalized' ? 'background:#d1fae5; color:#065f46;' : 'background:#fef3c7; color:#92400e;' }}">
            {{ ucfirst($selectedPeriod->status) }}
        </span>
        <span style="color:#6b7280; font-size:12px; margin-left:4px;">
            Payroll date: {{ $selectedPeriod->payroll_date->format('M d, Y') }}
        </span>
    </div>
    @endif

    {{-- Desktop Table --}}
    <div class="user-table-wrapper" style="overflow-y:auto; max-height:53vh; padding:0 28px;">
        <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:900px;">
            <thead style="position:sticky; top:0; z-index:5;">
                <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Employee</th>
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Department</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Total Deductions</th>
                    <th style="padding:12px; text-align:center; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php $payroll = $payrollData[$employee->id] ?? []; @endphp
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:12px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $colorDark }}); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700; flex-shrink:0;">
                                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('employees.show', $employee) }}"
                                       style="font-weight:600; color:#1a1a2e; text-decoration:none;">{{ $employee->full_name }}</a>
                                    <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px; color:#6b7280;">{{ $employee->department }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center;">
                                @if(($payroll['gross_pay'] ?? 0) == 0)
                                    <a href="javascript:void(0)"
                                       onclick="alert('This employee has no payroll data for the selected cutoff period.')"
                                       style="padding:5px 10px; background:#f3f4f6; color:#9ca3af; border-radius:8px; font-size:12px; text-decoration:none; cursor:not-allowed;"
                                       title="No payroll data">
                                        <i class="fas fa-table"></i>
                                    </a>
                                    <a href="javascript:void(0)"
                                       onclick="alert('This employee has no payroll data for the selected cutoff period.')"
                                       style="padding:5px 10px; background:#f3f4f6; color:#9ca3af; border-radius:8px; font-size:12px; text-decoration:none; cursor:not-allowed;"
                                       title="No payroll data">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <button type="button"
                                            class="btn-breakdown"
                                            data-payroll="{{ htmlspecialchars(json_encode([
                                                'name'                   => $employee->full_name,
                                                'employee_id'            => $employee->employee_id,
                                                'days_worked'            => $payroll['attendance_data']['days_worked'] ?? 0,
                                                'overtime_hours'         => $payroll['attendance_data']['overtime_hours'] ?? 0,
                                                'holiday_days'           => $payroll['attendance_data']['holiday_days'] ?? 0,
                                                'base_pay'               => $payroll['base_pay'] ?? 0,
                                                'overtime_pay'           => $payroll['overtime_pay'] ?? 0,
                                                'holiday_pay'            => $payroll['holiday_pay'] ?? 0,
                                                'night_differential_pay' => $payroll['night_differential_pay'] ?? 0,
                                                'allowance_benefits'     => $payroll['allowance_benefits'] ?? 0,
                                                'gross_pay'              => $payroll['gross_pay'] ?? 0,
                                                'sss_contribution'       => $payroll['sss_contribution'] ?? 0,
                                                'philhealth_contribution' => $payroll['philhealth_contribution'] ?? 0,
                                                'pagibig_contribution'   => $payroll['pagibig_contribution'] ?? 0,
                                                'withholding_tax'        => $payroll['withholding_tax'] ?? 0,
                                                'total_deductions'       => $payroll['total_deductions'] ?? 0,
                                                'net_pay'                => $payroll['net_pay'] ?? 0,
                                                'show_url'               => route('payroll.show', [$employee, 'payroll_period_id' => request('payroll_period_id')]),
                                                'payslip_url'            => route('payroll.payslip', [$employee, 'payroll_period_id' => request('payroll_period_id')]),
                                            ]), ENT_QUOTES, 'UTF-8') }}"
                                            style="padding:5px 10px; background:#fef9c3; color:#854d0e; border-radius:8px; font-size:12px; border:none; cursor:pointer;"
                                            title="Quick breakdown">
                                        <i class="fas fa-table"></i>
                                    </button>
                                    <a href="{{ route('payroll.show', [$employee, 'payroll_period_id' => request('payroll_period_id')]) }}"
                                       style="padding:5px 10px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;"
                                       title="Full details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('payroll.payslip', [$employee, 'payroll_period_id' => request('payroll_period_id')]) }}"
                                       style="padding:5px 10px; background:#d1fae5; color:#065f46; border-radius:8px; font-size:12px; text-decoration:none;"
                                       title="Download payslip">
                                        <i class="fas fa-file-download"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding:40px; text-align:center; color:#9ca3af;">
                            <i class="fas fa-money-bill-wave" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                            No payroll data found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="user-mobile-cards" style="padding:16px;">
        @forelse($employees as $employee)
            @php $payroll = $payrollData[$employee->id] ?? []; @endphp
            <div class="user-card">
                <div class="user-card-header">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $colorDark }}); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:700; flex-shrink:0;">
                            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:600; color:#1a1a2e; font-size:14px;">
                                <a href="{{ route('employees.show', $employee) }}"
                                   style="color:#1a1a2e; text-decoration:none; font-weight:600;">
                                    {{ $employee->full_name }}
                                </a>
                            </div>
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

                {{-- Collapsible breakdown --}}
                <div style="margin-top:12px; border-top:1px solid #f3f4f6;">
                    <button type="button"
                            onclick="var d=this.nextElementSibling; var i=this.querySelector('i'); d.style.display=d.style.display==='none'?'block':'none'; i.classList.toggle('fa-chevron-down'); i.classList.toggle('fa-chevron-up');"
                            style="width:100%; padding:10px 0; background:none; border:none; cursor:pointer; display:flex; justify-content:space-between; align-items:center; font-size:13px; font-weight:600; color:#6b7280;">
                        <span>View Payroll Breakdown</span>
                        <i class="fas fa-chevron-down" style="font-size:11px;"></i>
                    </button>
                    <div style="display:none; padding-bottom:4px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                            <span style="color:#6b7280;">Days Worked:</span>
                            <span style="font-weight:600; color:#1a1a2e;">{{ $payroll['attendance_data']['days_worked'] ?? 0 }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                            <span style="color:#6b7280;">Overtime Hours:</span>
                            <span style="color:#f59e0b;">{{ $payroll['attendance_data']['overtime_hours'] ?? 0 }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                            <span style="color:#6b7280;">Holiday Days:</span>
                            <span style="color:#8b5cf6;">{{ $payroll['attendance_data']['holiday_days'] ?? 0 }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                            <span style="color:#6b7280;">Basic Pay:</span>
                            <span style="font-weight:600; color:#1a1a2e;">₱{{ number_format($payroll['base_pay'] ?? 0, 2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                            <span style="color:#6b7280;">Gross Pay:</span>
                            <span style="font-weight:600; color:#1a1a2e;">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                            <span style="color:#6b7280;">Allowance & Benefits:</span>
                            <span style="color:#10b981;">+₱{{ number_format($payroll['allowance_benefits'] ?? 0, 2) }}</span>
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
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px; font-weight:600;">
                            <span style="color:#6b7280;">Total Deductions:</span>
                            <span style="color:#dc2626;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:700; color:#10b981; padding-bottom:10px;">
                            <span>Net Pay:</span>
                            <span>₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="user-card-meta">
                    @if(($payroll['gross_pay'] ?? 0) == 0)
                        <a href="javascript:void(0)"
                           onclick="alert('This employee has no payroll data for the selected cutoff period.')"
                           style="padding:5px 12px; background:#f3f4f6; color:#9ca3af; border-radius:8px; font-size:12px; text-decoration:none; cursor:not-allowed;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    @else
                        <a href="{{ route('payroll.show', [$employee, 'payroll_period_id' => request('payroll_period_id')]) }}"
                           style="padding:5px 12px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('payroll.payslip', [$employee, 'payroll_period_id' => request('payroll_period_id')]) }}"
                           style="padding:5px 12px; background:#d1fae5; color:#065f46; border-radius:8px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-file-download"></i> Payslip
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div style="padding:40px; text-align:center; color:#9ca3af;">
                <i class="fas fa-money-bill-wave" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                No payroll data found.
            </div>
        @endforelse
    </div>

</div>

    <div style="padding:16px 28px; border-top:1px solid #e5e7eb;">{{ $employees->links() }}</div>

{{-- Modal row helper style --}}
<style>
.modal-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 14px;
    font-size: 13px;
    color: #374151;
}
.modal-row:not(:last-child) {
    border-bottom: 1px solid rgba(0,0,0,0.04);
}
</style>

@endsection

@section('scripts')
<script>
function fmt(n) {
    return '₱' + parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function openBreakdownModal(data) {
    document.getElementById('modalEmployeeName').textContent    = data.name;
    document.getElementById('modalEmployeeId').textContent      = data.employee_id;
    document.getElementById('modalDaysWorked').textContent      = data.days_worked;
    document.getElementById('modalOvertimeHours').textContent   = data.overtime_hours;
    document.getElementById('modalHolidayDays').textContent     = data.holiday_days;
    document.getElementById('modalBasicPay').textContent        = fmt(data.base_pay);
    document.getElementById('modalOvertimePay').textContent     = fmt(data.overtime_pay);
    document.getElementById('modalHolidayPay').textContent      = fmt(data.holiday_pay);
    document.getElementById('modalNightDiff').textContent       = fmt(data.night_differential_pay);
    document.getElementById('modalAllowances').textContent      = fmt(data.allowance_benefits);
    document.getElementById('modalGrossPay').textContent        = fmt(data.gross_pay);
    document.getElementById('modalSss').textContent             = fmt(data.sss_contribution);
    document.getElementById('modalPhilHealth').textContent      = fmt(data.philhealth_contribution);
    document.getElementById('modalPagIbig').textContent         = fmt(data.pagibig_contribution);
    document.getElementById('modalTax').textContent             = fmt(data.withholding_tax);
    document.getElementById('modalTotalDeductions').textContent = fmt(data.total_deductions);
    document.getElementById('modalNetPay').textContent          = fmt(data.net_pay);
    document.getElementById('modalDetailLink').href             = data.show_url;
    document.getElementById('modalPayslipLink').href            = data.payslip_url;

    const modal = document.getElementById('payrollBreakdownModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeBreakdownModal() {
    document.getElementById('payrollBreakdownModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Use delegated jQuery click — consistent with app.js pattern
$(document).on('click', '.btn-breakdown', function () {
    const data = JSON.parse($(this).attr('data-payroll'));
    openBreakdownModal(data);
});

// Close on backdrop click
$('#payrollBreakdownModal').on('click', function (e) {
    if (e.target === this) closeBreakdownModal();
});

// Close on Escape
$(document).on('keydown', function (e) {
    if (e.key === 'Escape') closeBreakdownModal();
});

function printPayrollTable() {
    const rows = document.querySelectorAll('table tbody tr');

    // Build header
    const headers = [
        'Employee', 'Dept', 'Gross Pay', 'Allowance & Benefits',
        'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Total Deductions', 'Net Pay'
    ];

    let tableHTML = `
        <table>
            <thead>
                <tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr>
            </thead>
            <tbody>
    `;

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (!cells.length) return; // skip empty state row

        const emp    = cells[0].querySelector('a')?.innerText.trim() ?? '';
        const empId  = cells[0].querySelector('div[style*="monospace"]')?.innerText.trim() ?? '';
        const dept   = cells[1].innerText.trim();
        const gross  = cells[2].innerText.trim();
        const allow  = cells[3].innerText.trim();
        const sss    = cells[4].innerText.trim();
        const phil   = cells[5].innerText.trim();
        const pagibig= cells[6].innerText.trim();
        const tax    = cells[7].innerText.trim();
        const totDed = cells[8].innerText.trim();
        const net    = cells[9].innerText.trim();

        tableHTML += `
            <tr>
                <td><strong>${emp}</strong><br><small>${empId}</small></td>
                <td>${dept}</td>
                <td>${gross}</td>
                <td>${allow}</td>
                <td>${sss}</td>
                <td>${phil}</td>
                <td>${pagibig}</td>
                <td>${tax}</td>
                <td>${totDed}</td>
                <td>${net}</td>
            </tr>
        `;
    });

    tableHTML += `</tbody></table>`;

    // Detect active filters for the report header
    const searchVal  = document.querySelector('input[name="search"]')?.value ?? '';
    const deptVal    = document.querySelector('select[name="department"]')?.value ?? '';
    const periodText = document.querySelector('select[name="payroll_period_id"] option:checked')?.innerText ?? '';
    const filterNote = [
        searchVal  ? `Search: "${searchVal}"` : '',
        deptVal    ? `Department: ${deptVal}` : '',
        periodText ? `Cutoff: ${periodText}` : ''
    ].filter(Boolean).join(' | ') || 'All Employees';

    const win = window.open('', '_blank');
    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payroll Summary Report</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; font-size: 11px; color: #111; padding: 20px; }
                .report-header { margin-bottom: 16px; }
                .report-header h1 { font-size: 16px; color: #1a1a2e; }
                .report-header p  { font-size: 11px; color: #6b7280; margin-top: 4px; }
                table { width: 100%; border-collapse: collapse; }
                th {
                    background: #1e40af; color: white;
                    padding: 7px 8px; text-align: left;
                    font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;
                }
                td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
                tr:nth-child(even) td { background: #f9fafb; }
                small { color: #6b7280; font-size: 10px; font-family: monospace; }
                td:nth-child(3), td:nth-child(4) { color: #065f46; font-weight: 600; }
                td:nth-child(5), td:nth-child(6),
                td:nth-child(7), td:nth-child(8),
                td:nth-child(9) { color: #dc2626; }
                td:nth-child(10) { color: #065f46; font-weight: 700; }
                @media print {
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
            <div class="report-header">
                <h1>Payroll Summary Report</h1>
                <p>Filter: ${filterNote} &nbsp;|&nbsp; Printed: ${new Date().toLocaleString()}</p>
            </div>
            ${tableHTML}
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
    win.print();
}

function exportPayrollCSV() {
    const rows = document.querySelectorAll('table tbody tr');

    const headers = [
        'Employee', 'Employee ID', 'Department',
        'Gross Pay', 'Allowance & Benefits',
        'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax',
        'Total Deductions', 'Net Pay'
    ];

    function cleanAmount(val) {
        return val.replace(/[₱,+\-]/g, '').trim();
    }

    let csvRows = [headers.join(',')];

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (!cells.length) return;

        const emp     = cells[0].querySelector('a')?.innerText.trim() ?? '';
        const empId   = cells[0].querySelector('div[style*="monospace"]')?.innerText.trim() ?? '';
        const dept    = cells[1].innerText.trim();
        const gross   = cleanAmount(cells[2].innerText);
        const allow   = cleanAmount(cells[3].innerText);
        const sss     = cleanAmount(cells[4].innerText);
        const phil    = cleanAmount(cells[5].innerText);
        const pagibig = cleanAmount(cells[6].innerText);
        const tax     = cleanAmount(cells[7].innerText);
        const totDed  = cleanAmount(cells[8].innerText);
        const net     = cleanAmount(cells[9].innerText);

        const line = [
            `"${emp}"`, `"${empId}"`, `"${dept}"`,
            gross, allow, sss, phil, pagibig, tax, totDed, net
        ].join(',');

        csvRows.push(line);
    });

    // Include period in filename if selected
    const periodText = document.querySelector('select[name="payroll_period_id"] option:checked')?.innerText ?? '';
    const deptVal    = document.querySelector('select[name="department"]')?.value ?? '';
    const periodSlug = periodText && periodText !== 'Latest Cutoff'
        ? `_${periodText.replace(/[^a-zA-Z0-9]/g, '_').replace(/_+/g, '_')}`
        : '';
    const filename = deptVal
        ? `payroll_${deptVal.replace(/\s+/g, '_')}${periodSlug}.csv`
        : `payroll_all${periodSlug}.csv`;

    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
@extends('layouts.app')

@section('title', 'Payroll Preview')
@section('breadcrumb')
    <span>Manage Payroll</span>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:500;">Payroll</span>
@endsection
@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $colorDark = $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2');

    // Build department breakdown data for JS (all employees currently on page)
    $deptBreakdownData = [];
    foreach ($employees as $emp) {
        $p = $payrollData[$emp->id] ?? [];
        if (($p['gross_pay'] ?? 0) == 0) continue;
        $deptBreakdownData[] = [
            'name'                    => $emp->full_name,
            'employee_id'             => $emp->employee_id,
            'department'              => $emp->department,
            'basic_pay'               => $p['base_pay'] ?? 0,
            'gross_pay'               => $p['gross_pay'] ?? 0,
            'sss_contribution'        => $p['sss_contribution'] ?? 0,
            'philhealth_contribution' => $p['philhealth_contribution'] ?? 0,
            'pagibig_contribution'    => $p['pagibig_contribution'] ?? 0,
            'withholding_tax'         => $p['withholding_tax'] ?? 0,
            'total_deductions'        => $p['total_deductions'] ?? 0,
            'net_pay'                 => $p['net_pay'] ?? 0,
            'show_url'                => route('payroll.show', [$emp, 'payroll_period_id' => request('payroll_period_id')]),
            'payslip_url'             => route('payroll.payslip', [$emp, 'payroll_period_id' => request('payroll_period_id')]),
        ];
    }
    $deptLabel = request('department') ?: 'All Departments';
@endphp

{{-- ═══════════════════════════════════════════════
     DEPARTMENT BREAKDOWN MODAL
     ═══════════════════════════════════════════════ --}}
<div id="deptBreakdownModal"
     style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:flex-start; justify-content:center; padding:20px; overflow-y:auto;">
    <div style="background:white; border-radius:16px; width:100%; max-width:90vw; margin:auto; box-shadow:0 24px 64px rgba(0,0,0,0.2); overflow:hidden;">

        {{-- Modal header --}}
        <div style="padding:20px 28px 16px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:white; border-radius:16px 16px 0 0;">
            <div>
                <div style="font-size:17px; font-weight:700; color:#1a1a2e; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-layer-group" style="color:#2563eb;"></i>
                    <span id="deptModalTitle">Department Breakdown</span>
                </div>
                <div style="font-size:12px; color:#6b7280; margin-top:3px;" id="deptModalMeta">—</div>
            </div>
            <button onclick="closeDeptModal()"
                    style="background:#f3f4f6; border:none; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px; color:#6b7280; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal table --}}
<div style="overflow-x:auto; overflow-y:auto; max-height:50vh; padding:0 0 4px;">
                <table id="deptBreakdownTable" style="width:100%; border-collapse:collapse; font-size:13px; min-width:800px;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:11px 16px; text-align:left; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Employee</th>
                        <th style="padding:11px 16px; text-align:left; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Dept</th>
                        <th style="padding:11px 16px; text-align:right; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Basic Pay</th>
                        <th style="padding:11px 16px; text-align:right; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">SSS</th>
                        <th style="padding:11px 16px; text-align:right; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">PhilHealth</th>
                        <th style="padding:11px 16px; text-align:right; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Pag-IBIG</th>
                        <th style="padding:11px 16px; text-align:right; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Tax</th>
                        <th style="padding:11px 16px; text-align:right; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Total Deductions</th>
                        <th style="padding:11px 16px; text-align:right; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Net Pay</th>
                    </tr>
                </thead>
                <tbody id="deptBreakdownBody">
                    {{-- filled by JS --}}
                </tbody>
                <tfoot id="deptBreakdownFoot">
                    {{-- per-column totals row, filled by JS --}}
                </tfoot>
            </table>
            <div id="deptBreakdownEmpty" style="display:none; padding:40px; text-align:center; color:#9ca3af;">
                <i class="fas fa-inbox" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                No payroll data for the current filter.
            </div>
        </div>

        {{-- Total Net Pay → Gross Pay summary bar --}}
        <div id="deptGrossPayBar" style="display:none; margin:0 24px 0; padding:14px 20px; background:linear-gradient(135deg,#d1fae5,#a7f3d0); border-radius:0 0 12px 12px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <div style="font-size:14px; font-weight:700; color:#065f46;">
                <i class="fas fa-money-bill-wave" style="margin-right:6px;"></i>
                Total Gross Pay:
            </div>
            <span id="deptTotalGrossPay" style="font-size:20px; font-weight:800; color:#065f46; letter-spacing:-0.5px;">₱0.00</span>
        </div>

        {{-- Modal footer: Print, Export CSV, Close --}}
        <div style="padding:14px 24px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button onclick="printPayrollTable()"
                        style="padding:8px 18px; background:#1e40af; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fas fa-print"></i> Print PDF
                </button>
                <button onclick="exportPayrollCSV()"
                        style="padding:8px 18px; background:#065f46; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </div>
            <button onclick="closeDeptModal()"
                    style="padding:8px 20px; background:#f3f4f6; color:#374151; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer;">
                Close
            </button>
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
           <div style="display:flex; gap:8px; flex-wrap:wrap;">
    @if($isAdmin || $isHR)
    <button onclick="openDeptModal()"
            style="padding:8px 18px; background:#4f46e5; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-layer-group"></i> Dept Breakdown
    </button>
    @endif
</div>
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

            <button type="submit" style="display:none;"></button>

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
    <div class="user-table-wrapper" style="overflow-y:auto; max-height:47vh; padding:0 28px;">
        <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:900px;">
            <thead style="position:sticky; top:0; z-index:5;">
                <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Employee</th>
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Department</th>
                    <th style="padding:12px; text-align:center; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Days Worked</th>
                    <th style="padding:12px; text-align:center; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">OT Hrs</th>
                    <th style="padding:12px; text-align:center; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Holiday</th>
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
                        <td style="padding:12px; text-align:center; font-weight:600; color:#1a1a2e;">{{ $payroll['attendance_data']['days_worked'] ?? 0 }}</td>
                        <td style="padding:12px; text-align:center; font-weight:600; color:#f59e0b;">{{ $payroll['attendance_data']['overtime_hours'] ?? 0 }}</td>
                        <td style="padding:12px; text-align:center; font-weight:600; color:#8b5cf6;">{{ $payroll['attendance_data']['holiday_days'] ?? 0 }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center;">
                                @if(($payroll['gross_pay'] ?? 0) == 0 && empty($payroll['attendance_data']['days_worked']))
                                    <a href="javascript:void(0)"
                                       onclick="alert('This employee has no payroll data for the selected cutoff period.')"
                                       style="padding:5px 10px; background:#f3f4f6; color:#9ca3af; border-radius:8px; font-size:12px; text-decoration:none; cursor:not-allowed;"
                                       title="No payroll data">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <a href="{{ route('payroll.show', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
                                       style="padding:5px 10px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;"
                                       title="Full details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('payroll.payslip', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
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
                        <td colspan="7" style="padding:40px; text-align:center; color:#9ca3af;">
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
                    @if(($payroll['gross_pay'] ?? 0) == 0 && empty($payroll['attendance_data']['days_worked']))
                        <a href="javascript:void(0)"
                           onclick="alert('This employee has no payroll data for the selected cutoff period.')"
                           style="padding:5px 12px; background:#f3f4f6; color:#9ca3af; border-radius:8px; font-size:12px; text-decoration:none; cursor:not-allowed;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    @else
                        <a href="{{ route('payroll.show', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
                           style="padding:5px 12px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('payroll.payslip', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
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

@endsection

@section('scripts')
<script>
// ─── Department breakdown data from PHP ───────────────────────────────────────
const DEPT_BREAKDOWN_DATA = @json($deptBreakdownData);
const DEPT_LABEL          = @json($deptLabel);

// ─── Helpers ─────────────────────────────────────────────────────────────────
function fmt(n) {
    return '₱' + parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ─── Department Breakdown Modal ───────────────────────────────────────────────
function openDeptModal() {
    const data  = DEPT_BREAKDOWN_DATA;
    const body  = document.getElementById('deptBreakdownBody');
    const foot  = document.getElementById('deptBreakdownFoot');
    const empty = document.getElementById('deptBreakdownEmpty');
    const table = document.getElementById('deptBreakdownTable');
    const bar   = document.getElementById('deptGrossPayBar');

    document.getElementById('deptModalTitle').textContent =
        DEPT_LABEL !== 'All Departments'
            ? DEPT_LABEL + ' — Payroll Breakdown'
            : 'All Departments — Payroll Breakdown';

    const periodText = document.querySelector('select[name="payroll_period_id"] option:checked')?.innerText ?? 'Latest Cutoff';
    document.getElementById('deptModalMeta').textContent =
        data.length + ' employee' + (data.length !== 1 ? 's' : '') + ' · Cutoff: ' + periodText;

    if (!data.length) {
        table.style.display = 'none';
        bar.style.display   = 'none';
        empty.style.display = 'block';
        document.getElementById('deptBreakdownModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        return;
    }

    table.style.display = '';
    empty.style.display = 'none';

    // Accumulate totals
    let totBasic = 0, totSss = 0, totPhil = 0, totPagibig = 0, totTax = 0, totDed = 0, totNet = 0;

    body.innerHTML = data.map((emp, idx) => {
        totBasic   += parseFloat(emp.basic_pay);
        totSss     += parseFloat(emp.sss_contribution);
        totPhil    += parseFloat(emp.philhealth_contribution);
        totPagibig += parseFloat(emp.pagibig_contribution);
        totTax     += parseFloat(emp.withholding_tax);
        totDed     += parseFloat(emp.total_deductions);
        totNet     += parseFloat(emp.net_pay);

        const rowBg = idx % 2 === 0 ? '' : 'background:#f9fafb;';
        return `
            <tr style="border-bottom:1px solid #f3f4f6; ${rowBg}">
                <td style="padding:10px 16px;">
                    <div style="font-weight:600; color:#1a1a2e; font-size:13px;">${emp.name}</div>
                    <div style="font-size:11px; color:#9ca3af; font-family:monospace;">${emp.employee_id}</div>
                </td>
                <td style="padding:10px 16px; color:#6b7280; font-size:13px;">${emp.department}</td>
                <td style="padding:10px 16px; text-align:right; font-weight:600; color:#1a1a2e;">${fmt(emp.basic_pay)}</td>
                <td style="padding:10px 16px; text-align:right; color:#dc2626;">${fmt(emp.sss_contribution)}</td>
                <td style="padding:10px 16px; text-align:right; color:#dc2626;">${fmt(emp.philhealth_contribution)}</td>
                <td style="padding:10px 16px; text-align:right; color:#dc2626;">${fmt(emp.pagibig_contribution)}</td>
                <td style="padding:10px 16px; text-align:right; color:#dc2626;">${fmt(emp.withholding_tax)}</td>
                <td style="padding:10px 16px; text-align:right; font-weight:600; color:#dc2626;">${fmt(emp.total_deductions)}</td>
                <td style="padding:10px 16px; text-align:right; font-weight:700; color:#065f46; font-size:14px;">${fmt(emp.net_pay)}</td>
            </tr>`;
    }).join('');

    // Clear tfoot — no totals row
    foot.innerHTML = '';

    // Total Gross Pay bar = sum of net pays
    document.getElementById('deptTotalGrossPay').textContent = fmt(totNet);
    bar.style.display = 'flex';

    document.getElementById('deptBreakdownModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeptModal() {
    document.getElementById('deptBreakdownModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Move modal to body on load — escapes overflow containers
document.addEventListener('DOMContentLoaded', function() {
    document.body.appendChild(document.getElementById('deptBreakdownModal'));
    document.getElementById('deptBreakdownModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeptModal();
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeptModal();
});

// ─── Print PDF ────────────────────────────────────────────────────────────────
function printPayrollTable() {
    const data = DEPT_BREAKDOWN_DATA;
    if (!data.length) { alert('No payroll data to print.'); return; }

    const searchVal  = document.querySelector('input[name="search"]')?.value ?? '';
    const deptVal    = document.querySelector('select[name="department"]')?.value ?? '';
    const periodText = document.querySelector('select[name="payroll_period_id"] option:checked')?.innerText ?? '';
    const filterNote = [
        searchVal  ? `Search: "${searchVal}"` : '',
        deptVal    ? `Department: ${deptVal}` : '',
        periodText ? `Cutoff: ${periodText}` : ''
    ].filter(Boolean).join(' | ') || 'All Employees';

    let totBasic = 0, totSss = 0, totPhil = 0, totPagibig = 0, totTax = 0, totDed = 0, totNet = 0;

    const headers = ['Employee', 'Dept', 'Basic Pay', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Total Deductions', 'Net Pay'];

    let rows = '';
    data.forEach(d => {
        totBasic   += parseFloat(d.basic_pay);
        totSss     += parseFloat(d.sss_contribution);
        totPhil    += parseFloat(d.philhealth_contribution);
        totPagibig += parseFloat(d.pagibig_contribution);
        totTax     += parseFloat(d.withholding_tax);
        totDed     += parseFloat(d.total_deductions);
        totNet     += parseFloat(d.net_pay);
        rows += `
            <tr>
                <td><strong>${d.name}</strong><br><small>${d.employee_id}</small></td>
                <td>${d.department}</td>
                <td class="num">${fmt(d.basic_pay)}</td>
                <td class="num red">${fmt(d.sss_contribution)}</td>
                <td class="num red">${fmt(d.philhealth_contribution)}</td>
                <td class="num red">${fmt(d.pagibig_contribution)}</td>
                <td class="num red">${fmt(d.withholding_tax)}</td>
                <td class="num red bold">${fmt(d.total_deductions)}</td>
                <td class="num green bold">${fmt(d.net_pay)}</td>
            </tr>`;
    });

    const win = window.open('', '_blank');
    win.document.write(`
        <!DOCTYPE html><html><head>
        <title>Payroll Summary Report</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family:Arial,sans-serif; font-size:11px; color:#111; padding:20px; }
            h1 { font-size:16px; color:#1a1a2e; margin-bottom:4px; }
            .meta { font-size:11px; color:#6b7280; margin-bottom:16px; }
            table { width:100%; border-collapse:collapse; }
            thead th { background:#1e40af; color:white; padding:7px 8px; font-size:10px; text-transform:uppercase; letter-spacing:0.04em; text-align:left; }
            thead th.num { text-align:right; }
            td { padding:6px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top; }
            td.num { text-align:right; }
            tr:nth-child(even) td { background:#f9fafb; }
            small { color:#6b7280; font-size:10px; font-family:monospace; }
            .red { color:#dc2626; }
            .green { color:#065f46; }
            .bold { font-weight:700; }
            tfoot tr td { background:#dbeafe; font-weight:700; border-top:2px solid #93c5fd; padding:8px; }
            tfoot tr td.num { text-align:right; }
            .gross-bar { margin-top:12px; background:#d1fae5; border-radius:8px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; }
            .gross-bar span { font-size:11px; color:#065f46; }
            .gross-bar strong { font-size:15px; color:#065f46; }
            @media print { body { padding:0; } }
        </style>
        </head><body>
        <h1>Payroll Summary Report</h1>
        <div class="meta">Filter: ${filterNote} &nbsp;|&nbsp; Printed: ${new Date().toLocaleString()}</div>
        <table>
            <thead><tr>${headers.map((h, i) => i >= 2 ? `<th class="num">${h}</th>` : `<th>${h}</th>`).join('')}</tr></thead>
            <tbody>${rows}</tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Totals (${data.length} employees)</td>
                    <td class="num">${fmt(totBasic)}</td>
                    <td class="num red">${fmt(totSss)}</td>
                    <td class="num red">${fmt(totPhil)}</td>
                    <td class="num red">${fmt(totPagibig)}</td>
                    <td class="num red">${fmt(totTax)}</td>
                    <td class="num red bold">${fmt(totDed)}</td>
                    <td class="num green bold">${fmt(totNet)}</td>
                </tr>
            </tfoot>
        </table>
        <div class="gross-bar">
            <span><strong>Total Gross Pay</strong> (sum of all net pays)</span>
            <strong>${fmt(totNet)}</strong>
        </div>
        </body></html>`);
    win.document.close();
    win.focus();
    win.print();
}

// ─── Export CSV ───────────────────────────────────────────────────────────────
function exportPayrollCSV() {
    const data = DEPT_BREAKDOWN_DATA;
    if (!data.length) { alert('No payroll data to export.'); return; }

    const headers = [
        'Employee', 'Employee ID', 'Department',
        'Basic Pay', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax',
        'Total Deductions', 'Net Pay'
    ];

    let csvRows = [headers.join(',')];
    let totNet = 0;

    data.forEach(d => {
        totNet += parseFloat(d.net_pay);
        csvRows.push([
            `"${d.name}"`,
            `"${d.employee_id}"`,
            `"${d.department}"`,
            d.basic_pay,
            d.sss_contribution,
            d.philhealth_contribution,
            d.pagibig_contribution,
            d.withholding_tax,
            d.total_deductions,
            d.net_pay
        ].join(','));
    });

    // Totals row
    csvRows.push(['', '', '"TOTALS"', '', '', '', '', '', totNet.toFixed(2)].join(','));
    csvRows.push(['', '', '"Total Gross Pay (sum of net pays)"', '', '', '', '', '', totNet.toFixed(2)].join(','));

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
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
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

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
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

{{-- Filters + Table --}}
<div class="card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">

    {{-- Sticky header --}}
    <div style="position:sticky; top:0; z-index:10; background:white; padding:20px 25px 0; border-radius:10px 10px 0 0; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <h2 style="margin:0;">Payroll Summary</h2>
        </div>

        @if($isAdmin || $isHR)
        <form method="GET" action="{{ route('payroll.index') }}"
              style="display:flex; flex-wrap:wrap; gap:10px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">
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
        @else
        <div style="border-bottom:1px solid #e5e7eb; margin-bottom:0;"></div>
        @endif
    </div>

    {{-- Desktop Table --}}
    <div class="user-table-wrapper" style="overflow-y:auto; max-height:55vh; padding:0 25px;">
        <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:900px;">
            <thead style="position:sticky; top:0; z-index:5;">
                <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Employee</th>
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Department</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Gross Pay</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Allowance & Benefits</th>
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
                    @php $payroll = $payrollData[$employee->id] ?? []; @endphp
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:12px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $colorDark }}); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700; flex-shrink:0;">
                                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                </div>
                                <div>
<a href="{{ route('employees.show', $employee) }}"
   style="font-weight:600; color:#1f2937; text-decoration:none;">{{ $employee->full_name }}</a>                                    <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px; color:#6b7280;">{{ $employee->department }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#1f2937;">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#10b981;">+₱{{ number_format($payroll['allowance_benefits'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['sss_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['philhealth_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['pagibig_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; font-weight:700; color:#10b981; font-size:16px;">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:center;">
                            @if(($payroll['gross_pay'] ?? 0) == 0)
                                <a href="javascript:void(0)"
                                   onclick="alert('This employee has no payroll data yet.')"
                                   style="padding:5px 10px; background:#f3f4f6; color:#9ca3af; border-radius:5px; font-size:12px; text-decoration:none; cursor:not-allowed;">
                                    <i class="fas fa-eye"></i> 
                                </a>
                      @else
    <a href="{{ route('payroll.show', $employee) }}"
       style="padding:5px 10px; background:#dbeafe; color:#1e40af; border-radius:5px; font-size:12px; text-decoration:none;">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('payroll.payslip', $employee) }}"
       style="padding:5px 10px; background:#d1fae5; color:#065f46; border-radius:5px; font-size:12px; text-decoration:none; margin-left:4px;">
        <i class="fas fa-file-download"></i>
    </a>
@endif
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
</div>{{-- end table wrapper --}}

</div>{{-- end card --}}

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
<div style="font-weight:600; color:#1f2937; font-size:14px;">
    <a href="{{ route('employees.show', $employee) }}"
       style="color:#1f2937; text-decoration:none; font-weight:600;">
        {{ $employee->full_name }}
    </a>
</div>                            <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
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

    <div style="display:none;">
        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
            <span style="color:#6b7280;">Gross Pay:</span>
            <span style="font-weight:600; color:#1f2937;">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</span>
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
        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
            <span style="color:#6b7280; font-weight:600;">Total Deductions:</span>
            <span style="color:#dc2626; font-weight:600;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:700; color:#10b981; padding-bottom:10px;">
            <span>Net Pay:</span>
            <span>₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</span>
        </div>
    </div>
</div>

                <div class="user-card-meta" style="margin-top:10px; padding-top:10px; border-top:1px solid #f3f4f6;">
                    @if(($payroll['gross_pay'] ?? 0) == 0)
                        <a href="javascript:void(0)"
                           onclick="alert('This employee has no payroll data yet.')"
                           style="padding:5px 12px; background:#f3f4f6; color:#9ca3af; border-radius:5px; font-size:12px; text-decoration:none; cursor:not-allowed;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    @else
                        <a href="{{ route('payroll.show', $employee) }}"
                           style="padding:5px 12px; background:#dbeafe; color:#1e40af; border-radius:5px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View Details
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
  {{-- Pagination (once, outside the table) --}}
    <div style="padding:16px 25px; border-top:1px solid #e5e7eb;">{{ $employees->links() }}</div>
@endsection
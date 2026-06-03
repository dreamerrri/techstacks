<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }

  .header { background: #00b09b; color: white; padding: 24px 32px; }
  .header h1 { font-size: 22px; font-weight: 700; letter-spacing: 1px; }
  .header p  { font-size: 11px; opacity: 0.8; margin-top: 4px; }

  .body { padding: 24px 32px; }

  .employee-block { display: flex; justify-content: space-between; margin-bottom: 20px; }
  .emp-info h2 { font-size: 16px; font-weight: 700; }
  .emp-info p  { font-size: 11px; color: #6b7280; margin-top: 2px; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px;
           font-weight: 700; background: #dbeafe; color: #1e40af; }

  .meta { font-size: 10px; color: #6b7280; text-align: right; }
  .meta strong { color: #1f2937; }

  .section-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin: 18px 0 10px;
  }

  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  td    { padding: 6px 0; vertical-align: top; }
  td.label { color: #6b7280; width: 65%; }
  td.label small { display: block; font-size: 10px; color: #9ca3af; }
  td.amount { text-align: right; font-weight: 600; }
  td.green  { color: #059669; }
  td.red    { color: #dc2626; }

  .divider { border: none; border-top: 1px solid #e5e7eb; margin: 6px 0; }
  .divider-bold { border: none; border-top: 2px solid #e5e7eb; margin: 8px 0; }

  .net-pay-row { margin-top: 8px; padding: 14px 16px;
                 background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;
                 display: flex; justify-content: space-between; align-items: center; }
  .net-pay-row .label { font-size: 14px; font-weight: 700; color: #1f2937; }
  .net-pay-row .value { font-size: 20px; font-weight: 700; color: #059669; }

  .govids { display: flex; gap: 12px; margin-top: 10px; }
  .govid  { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb;
            border-radius: 6px; padding: 8px 10px; text-align: center; }
  .govid .gid-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.3px; }
  .govid .gid-value { font-size: 11px; font-weight: 700; color: #1f2937; font-family: monospace; margin-top: 2px; }

  .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #e5e7eb;
            display: flex; justify-content: space-between; font-size: 10px; color: #9ca3af; }
  .sig-block { text-align: center; }
  .sig-block .sig-line { border-top: 1px solid #1f2937; width: 160px; margin: 28px auto 4px; }
  .sig-block .sig-name { font-size: 11px; font-weight: 700; color: #1f2937; }
  .sig-block .sig-role { font-size: 10px; color: #6b7280; }
</style>
</head>
<body>

{{-- Header --}}
{{-- ✅ Fixed --}}
<div class="header">
   
    <h1>Techstacks</h1>
    <p>LogiPay &mdash; Human Resources</p>
</div>


<div class="body">

    {{-- Employee Info + Meta --}}
    <div class="employee-block">
        <div class="emp-info">
            <h2>{{ $employee->full_name }}</h2>
            <p>{{ $employee->position }} &mdash; {{ $employee->department }}</p>
            <p>{{ $employee->employee_id }} &nbsp;|&nbsp; Hired: {{ $employee->date_hired->format('M d, Y') }}</p>
            <div style="margin-top:6px;">
                <span class="badge">{{ $employee->employment_status }}</span>
                <span class="badge" style="background:#fef3c7; color:#92400e; margin-left:6px;">
                    {{ $employee->salary_type }} Rate
                </span>
            </div>
        </div>
        <div class="meta">
            <strong>Date Generated</strong><br>{{ $generatedAt }}<br><br>
            <strong>Pay Period</strong><br>{{ now()->format('F Y') }}
        </div>
    </div>

    {{-- Government IDs --}}
    <div class="govids">
        @foreach([
            ['SSS',       $employee->sss_number],
            ['PhilHealth',$employee->philhealth_number],
            ['Pag-IBIG',  $employee->pagibig_number],
            ['TIN',       $employee->tin_number],
        ] as [$label, $value])
        <div class="govid">
            <div class="gid-label">{{ $label }}</div>
            <div class="gid-value">{{ $value ?? '—' }}</div>
        </div>
        @endforeach
    </div>

    {{-- Earnings --}}
    <div class="section-title">Earnings</div>
    <table>
        <tr>
            <td class="label">
                Base Pay
                <small>{{ $payroll['attendance_data']['regular_hours'] ?? 0 }} hrs &times; &#8369;{{ number_format($payroll['hourly_rate'] ?? 0, 2) }}/hr</small>
            </td>
            <td class="amount">&#8369;{{ number_format($payroll['base_pay'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">
                Overtime Pay
                <small>{{ $payroll['attendance_data']['overtime_hours'] ?? 0 }} OT hrs &times; 1.25</small>
            </td>
            <td class="amount green">+&#8369;{{ number_format($payroll['overtime_pay'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">
                Night Differential
                <small>{{ $payroll['attendance_data']['night_differential_hours'] ?? 0 }} night hrs &times; 10%</small>
            </td>
            <td class="amount green">+&#8369;{{ number_format($payroll['night_differential_pay'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">
                Holiday Pay
                <small>{{ $payroll['attendance_data']['holiday_days'] ?? 0 }} days &times; 2</small>
            </td>
            <td class="amount green">+&#8369;{{ number_format($payroll['holiday_pay'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Allowances &amp; Benefits</td>
            <td class="amount green">+&#8369;{{ number_format($payroll['allowance_benefits'] ?? 0, 2) }}</td>
        </tr>
        <hr class="divider">
        <tr>
            <td class="label" style="font-weight:700; color:#1f2937;">Gross Pay</td>
            <td class="amount" style="font-weight:700;">&#8369;{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</td>
        </tr>
    </table>

    {{-- Deductions --}}
    <div class="section-title">Deductions</div>
    <table>
        <tr>
            <td class="label">SSS Contribution <small>4.5% (capped &#8369;900)</small></td>
            <td class="amount red">-&#8369;{{ number_format($payroll['sss_contribution'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">PhilHealth Contribution <small>2.25% (capped &#8369;1,500)</small></td>
            <td class="amount red">-&#8369;{{ number_format($payroll['philhealth_contribution'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Pag-IBIG Contribution <small>2% (capped &#8369;100)</small></td>
            <td class="amount red">-&#8369;{{ number_format($payroll['pagibig_contribution'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">
                Late Deduction
                <small>{{ $payroll['attendance_data']['late_hours'] ?? 0 }} late hrs</small>
            </td>
            <td class="amount red">-&#8369;{{ number_format($payroll['late_deduction'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Manual Deductions</td>
            <td class="amount red">-&#8369;{{ number_format($payroll['manual_deductions'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Withholding Tax <small>Per BIR tax brackets</small></td>
            <td class="amount red">-&#8369;{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</td>
        </tr>
        <hr class="divider">
        <tr>
            <td class="label" style="font-weight:700; color:#dc2626;">Total Deductions</td>
            <td class="amount red" style="font-weight:700;">-&#8369;{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</td>
        </tr>
    </table>

    {{-- Net Pay --}}
    <div class="net-pay-row" style="margin-top:16px;">
        <span class="label">NET PAY</span>
        <span class="value">&#8369;{{ number_format($payroll['net_pay'] ?? 0, 2) }}</span>
    </div>

    {{-- Signature --}}
    <div class="footer">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name">{{ $employee->full_name }}</div>
            <div class="sig-role">Employee's Signature</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name">HR / Authorized Signatory</div>
            <div class="sig-role">Prepared by</div>
        </div>
        <div style="font-size:10px; color:#9ca3af; align-self:flex-end; text-align:right;">
            This is a system-generated payslip.<br>
            Generated: {{ $generatedAt }}
        </div>
    </div>

</div>
</body>
</html>
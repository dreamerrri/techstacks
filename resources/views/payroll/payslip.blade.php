<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; background: #fff; }

  /* ── Header ── */
  .header { text-align: center; padding: 28px 40px 16px; border-bottom: 2px solid #111; }
  .header-logo { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px; }
  .logo-icon { width: 36px; height: 36px; }
  .logo-name { font-size: 22px; font-weight: 700; letter-spacing: 1px; }
  .header h2 { font-size: 13px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 4px; }
  .header address { font-style: normal; font-size: 10px; color: #333; line-height: 1.6; }

  /* ── Employee + Meta row ── */
  .emp-meta { display: flex; justify-content: space-between; align-items: flex-start;
              padding: 16px 40px; border-bottom: 1px solid #ccc; }
  .emp-info h3 { font-size: 13px; font-weight: 700; text-transform: uppercase; }
  .emp-info p  { font-size: 10px; color: #333; margin-top: 2px; line-height: 1.7; }
  .meta-grid   { display: grid; grid-template-columns: auto auto; gap: 1px 14px;
                 font-size: 10px; text-align: left; }
  .meta-grid .mk { color: #333; }
  .meta-grid .mv { font-weight: 700; text-align: right; }

  /* ── Two-column earnings / deductions ── */
  .columns { display: flex; padding: 0 40px; border-bottom: 1px solid #ccc; }
  .col     { flex: 1; padding: 14px 0; }
  .col + .col { border-left: 1px solid #ccc; padding-left: 20px; }

  .col-title { font-size: 11px; font-weight: 700; text-transform: uppercase;
               letter-spacing: 0.5px; border-bottom: 1px solid #111; padding-bottom: 5px;
               margin-bottom: 8px; display: flex; justify-content: space-between; }
  .col-title span { font-weight: 700; }

  table.items { width: 100%; border-collapse: collapse; font-size: 10.5px; }
  table.items td { padding: 3px 0; vertical-align: top; }
  table.items td.iname { color: #222; }
  table.items td.iamt  { text-align: right; font-weight: 600; }

  tr.subtotal td { border-top: 1px solid #bbb; padding-top: 5px; margin-top: 4px; font-weight: 700; font-size: 11px; }
  tr.subtotal td.iamt { font-weight: 700; }

  /* ── Net Pay banner ── */
  .net-pay { text-align: center; padding: 18px 40px; border-bottom: 1px solid #ccc; }
  .net-pay .np-amount { font-size: 24px; font-weight: 700; }
  .net-pay .np-label  { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 2px; }

  /* ── Signatures ── */
  .sigs { display: flex; justify-content: space-between; padding: 28px 60px 20px; }
  .sig-block { text-align: center; min-width: 160px; }
  .sig-block .sig-img { height: 44px; margin-bottom: 0; }
  .sig-block .sig-line { border-top: 1px solid #111; width: 180px; margin: 0 auto 5px; }
  .sig-block .sig-name { font-size: 11px; font-weight: 700; text-transform: uppercase; }
  .sig-block .sig-role { font-size: 10px; color: #555; }

  /* ── Footer note ── */
  .footnote { text-align: center; font-size: 9.5px; color: #666;
              padding: 8px 40px 16px; font-style: italic; }
</style>
</head>
<body>

{{-- ── HEADER ── --}}
<div class="header">
    <div class="header-logo">
        {{-- Inline SVG arrow logo matching Techstacks brand --}}
        <svg class="logo-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="20" cy="20" r="19" fill="#111" stroke="#111" stroke-width="1"/>
            <path d="M10 28 L20 10 L30 28" stroke="white" stroke-width="3.5" stroke-linejoin="round" fill="none"/>
            <path d="M14 22 L26 22" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
        </svg>
        <span class="logo-name">Techstacks</span>
    </div>
    <h2>Payslip</h2>
    <address>
        TECHSTACKS ITSERVICES INC.<br>
        2ND FLR GARCIA BLDG LAMBINGAN ST.,<br>
        DAANG SARILE, CABANATUAN CITY, NUEVA ECIJA 3100
    </address>
</div>

{{-- ── EMPLOYEE + META ── --}}
<div class="emp-meta">
    <div class="emp-info">
        <h3>{{ $employee->full_name }}</h3>
        <p>
            {{ $employee->position }}<br>
            {{ $employee->department }}
        </p>
    </div>
    <div class="meta-grid">
        <span class="mk">Employee ID:</span>  <span class="mv">{{ $employee->employee_id }}</span>
        <span class="mk">Pay Period:</span>   <span class="mv">{{ $selectedPeriod ? $selectedPeriod->cutoff_start->format('F d') . ' – ' . $selectedPeriod->cutoff_end->format('F d, Y') : now()->format('F Y') }}</span>
        <span class="mk">Worked Days:</span>  <span class="mv">{{ $payroll['attendance_data']['days_worked'] ?? 0 }}</span>
        <span class="mk">Rate Per Day:</span> <span class="mv">PHP{{ number_format($payroll['daily_rate'] ?? 0, 2) }}</span>
        <span class="mk">Overtime Hrs:</span> <span class="mv">{{ $payroll['attendance_data']['overtime_hours'] ?? 0 }}</span>
        <span class="mk">Late Hrs:</span>    <span class="mv">{{ $payroll['attendance_data']['late_hours'] ?? 0 }}</span>
        <span class="mk">Holiday/s:</span>    <span class="mv">{{ $payroll['attendance_data']['holiday_days'] ?? 0 }}</span>
    </div>
</div>

{{-- ── EARNINGS + DEDUCTIONS ── --}}
<div class="columns">

    {{-- Earnings --}}
    <div class="col">
        <div class="col-title">
            <span>Earnings</span>
            <span>Amount</span>
        </div>
        <table class="items">
            <tr>
                <td class="iname">Basic Salary</td>
                <td class="iamt">PHP{{ number_format($payroll['base_pay'] ?? 0, 2) }}</td>
            </tr>
            @if(($payroll['weekend_pay'] ?? 0) > 0)
            <tr>
                <td class="iname">Weekend Pay</td>
                <td class="iamt">PHP{{ number_format($payroll['weekend_pay'], 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="iname">Allowance per cut</td>
                <td class="iamt">PHP{{ number_format($payroll['allowance_benefits'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="iname">Overtime Pay</td>
                <td class="iamt">PHP{{ number_format($payroll['overtime_pay'] ?? 0, 2) }}</td>
            </tr>
            @if(($payroll['night_differential_pay'] ?? 0) > 0)
            <tr>
                <td class="iname">Night Differential</td>
                <td class="iamt">PHP{{ number_format($payroll['night_differential_pay'], 2) }}</td>
            </tr>
            @endif
            @if(($payroll['holiday_pay'] ?? 0) > 0)
            <tr>
                <td class="iname">Holiday Pay</td>
                <td class="iamt">PHP{{ number_format($payroll['holiday_pay'], 2) }}</td>
            </tr>
            @endif
            <tr class="subtotal">
                <td class="iname" style="text-align:right; padding-right:12px;">Total Earnings</td>
                <td class="iamt">PHP{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Deductions --}}
    <div class="col">
        <div class="col-title">
            <span>Contributions &amp; Deductions</span>
            <span>Amount</span>
        </div>
        <table class="items">
            <tr>
                <td class="iname">PhilHealth</td>
                <td class="iamt">PHP{{ number_format($payroll['philhealth_contribution'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="iname">SSS</td>
                <td class="iamt">PHP{{ number_format($payroll['sss_contribution'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="iname">Pag-IBIG</td>
                <td class="iamt">PHP{{ number_format($payroll['pagibig_contribution'] ?? 0, 2) }}</td>
            </tr>
            @if(($payroll['late_deduction'] ?? 0) > 0)
            <tr>
                <td class="iname">Late Deduction</td>
                <td class="iamt">PHP{{ number_format($payroll['late_deduction'], 2) }}</td>
            </tr>
            @endif
            @if(($payroll['manual_deductions'] ?? 0) > 0)
            <tr>
                <td class="iname">Manual Deductions</td>
                <td class="iamt">PHP{{ number_format($payroll['manual_deductions'], 2) }}</td>
            </tr>
            @endif
            @if(($payroll['withholding_tax'] ?? 0) > 0)
            <tr>
                <td class="iname">Withholding Tax</td>
                <td class="iamt">PHP{{ number_format($payroll['withholding_tax'], 2) }}</td>
            </tr>
            @endif
            <tr class="subtotal">
                <td class="iname" style="text-align:right; padding-right:12px;">Total Deductions</td>
                <td class="iamt">PHP{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</td>
            </tr>
            <tr class="subtotal" style="font-size:12px;">
                <td class="iname" style="text-align:right; padding-right:12px;">Net Pay</td>
                <td class="iamt">PHP{{ number_format($payroll['net_pay'] ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

</div>

{{-- ── NET PAY BANNER ── --}}
<div class="net-pay">
    <div class="np-amount">PHP{{ number_format($payroll['net_pay'] ?? 0, 2) }}</div>
    <div class="np-label">Net Pay</div>
</div>

{{-- ── SIGNATURES ── --}}
<div class="sigs">
    <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-name">{{ $authorizedSignatory ?? 'RENZ ANDREW S. GWAPO' }}</div>
        <div class="sig-role">Admin/HR</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-name">{{ $employee->full_name }}</div>
        <div class="sig-role">Employee's Signature</div>
    </div>
</div>

{{-- ── FOOTNOTE ── --}}
<div class="footnote">This is a system generated payslip.</div>

</body>
</html> 
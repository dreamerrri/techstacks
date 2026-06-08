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
                   style="flex:1; min-width:160px; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
            <select name="department"
                    style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-danger btn-sm" style="padding:8px 20px; font-size:14px;">
                <i class="fas fa-search"></i> Search
            </button>
            @if(request()->hasAny(['search','department']))
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

    {{-- Desktop Table --}}
    <div class="user-table-wrapper" style="overflow-y:auto; max-height:55vh; padding:0 28px;">
        <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:900px;">
            <thead style="position:sticky; top:0; z-index:5;">
                <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Employee</th>
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Department</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Gross Pay</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Allowance & Benefits</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">SSS</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">PhilHealth</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Pag-IBIG</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Tax</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Total Deductions</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Net Pay</th>
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
                        <td style="padding:12px; text-align:right; font-weight:600; color:#1a1a2e;">₱{{ number_format($payroll['gross_pay'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#10b981;">+₱{{ number_format($payroll['allowance_benefits'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['sss_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['philhealth_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['pagibig_contribution'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; color:#dc2626;">-₱{{ number_format($payroll['withholding_tax'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#dc2626;">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:right; font-weight:700; color:#10b981; font-size:15px;">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</td>
                        <td style="padding:12px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center;">
                                @if(($payroll['gross_pay'] ?? 0) == 0)
                                    <a href="javascript:void(0)"
                                       onclick="alert('This employee has no payroll data yet.')"
                                       style="padding:5px 10px; background:#f3f4f6; color:#9ca3af; border-radius:8px; font-size:12px; text-decoration:none; cursor:not-allowed;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <a href="{{ route('payroll.show', $employee) }}"
                                       style="padding:5px 10px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('payroll.payslip', $employee) }}"
                                       style="padding:5px 10px; background:#d1fae5; color:#065f46; border-radius:8px; font-size:12px; text-decoration:none;">
                                        <i class="fas fa-file-download"></i>
                                    </a>
                                @endif
                            </div>
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
                           onclick="alert('This employee has no payroll data yet.')"
                           style="padding:5px 12px; background:#f3f4f6; color:#9ca3af; border-radius:8px; font-size:12px; text-decoration:none; cursor:not-allowed;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    @else
                        <a href="{{ route('payroll.show', $employee) }}"
                           style="padding:5px 12px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('payroll.payslip', $employee) }}"
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

        // Extract text cleanly per cell
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
    const searchVal = document.querySelector('input[name="search"]')?.value ?? '';
    const deptVal   = document.querySelector('select[name="department"]')?.value ?? '';
    const filterNote = [
        searchVal ? `Search: "${searchVal}"` : '',
        deptVal   ? `Department: ${deptVal}` : ''
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

    // Strip ₱ signs and commas so numbers are clean in spreadsheet
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

        // Wrap strings in quotes in case they have commas
        const line = [
            `"${emp}"`, `"${empId}"`, `"${dept}"`,
            gross, allow, sss, phil, pagibig, tax, totDed, net
        ].join(',');

        csvRows.push(line);
    });

    // Active filter label for the filename
    const deptVal  = document.querySelector('select[name="department"]')?.value ?? '';
    const filename = deptVal
        ? `payroll_${deptVal.replace(/\s+/g, '_')}.csv`
        : 'payroll_all.csv';

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
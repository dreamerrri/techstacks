@extends('layouts.app')

@section('title', $employee->full_name . ' - Government Contributions')

@section('content')

    {{-- Top nav --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <a href="{{ route('government-contributions.index') }}" style="color:#9ca3af; text-decoration:none; font-size:14px; font-weight:500; display:inline-flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Back to Government Contributions
        </a>
        <div style="display:flex; gap:8px;">
            <button onclick="printContributionDetail()"
                    style="padding:8px 16px; background:#1e40af; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                <i class="fas fa-print"></i> Print
            </button>
            <button onclick="exportContributionDetailCSV()"
                    style="padding:8px 16px; background:#065f46; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- Profile Header --}}
    <div class="aurora-card" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
        <div style="width:70px; height:70px; border-radius:50%; overflow:hidden; flex-shrink:0;">
            @if($employee->user?->profile_photo)
                <img src="{{ asset('storage/' . $employee->user->profile_photo) }}"
                     alt="{{ $employee->full_name }}"
                     style="width:100%; height:100%; object-fit:cover;">
            @else
                <div style="width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:28px; font-weight:700;">
                    {{ strtoupper(substr($employee->first_name, 0, 1)) }}
                </div>
            @endif
        </div>
        <div>
            <h2 style="margin:0 0 4px; font-size:22px; color:#1a1a2e; font-weight:700;">{{ $employee->full_name }}</h2>
            <p style="margin:0; color:#6b7280;">{{ $employee->position }} — {{ $employee->department }}</p>
            <p style="margin:4px 0 0; color:#1a1a2e; font-weight:600; font-size:14px;">Basic Salary: ₱{{ number_format($employee->basic_salary, 2) }}</p>
            <span style="display:inline-block; margin-top:6px; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;
                {{ $employee->employment_status === 'Regular'      ? 'background:#d1fae5; color:#065f46;'  : '' }}
                {{ $employee->employment_status === 'Probationary' ? 'background:#fef3c7; color:#92400e;'  : '' }}
                {{ $employee->employment_status === 'Contractual'  ? 'background:#dbeafe; color:#1e40af;'  : '' }}
                {{ $employee->employment_status === 'Part-time'    ? 'background:#f3f4f6; color:#374151;'  : '' }}
            ">{{ $employee->employment_status }}</span>
        </div>
    </div>

    {{-- Government Contributions --}}
    <div class="aurora-card">
        <h2 class="aurora-card-title">
            <i class="fas fa-id-card" style="color:#dc2626;"></i> Government Contributions
        </h2>

        {{-- ID Numbers Grid --}}
        <div class="aurora-stats-grid">
            @foreach([
                ['SSS Number',  $employee->sss_number,       'fa-shield-alt', '#10b981', 'rgba(16,185,129,0.1)'],
                ['PhilHealth',  $employee->philhealth_number, 'fa-heart',      '#3b82f6', 'rgba(59,130,246,0.1)'],
                ['Pag-IBIG',    $employee->pagibig_number,    'fa-home',       '#f59e0b', 'rgba(245,158,11,0.1)'],
                ['TIN Number',  $employee->tin_number,        'fa-file-invoice','#8b5cf6','rgba(139,92,246,0.1)'],
            ] as [$label, $value, $icon, $color, $bg])
            <div class="aurora-stat-card" style="text-align:center;">
                <div class="aurora-stat-icon" style="color:{{ $color }}; background:{{ $bg }};">
                    <i class="fas {{ $icon }}"></i>
                </div>
                <div style="font-size:12px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.07em; font-weight:500; margin-bottom:6px;">{{ $label }}</div>
                <div style="font-weight:700; font-family:monospace; color:#1a1a2e; font-size:13px; word-break:break-all;">{{ $value ?? '—' }}</div>
            </div>
            @endforeach
        </div>

        {{-- SSS Contribution Breakdown --}}
        <div style="margin-top:24px; padding:20px; background:#eff6ff; border-radius:16px; border:1px solid #bfdbfe;">
            <h4 style="margin:0 0 16px; font-size:13px; color:#1e40af; font-weight:600; text-transform:uppercase; letter-spacing:0.08em;">
                <i class="fas fa-calculator"></i> &nbsp;SSS Contribution (Circular No. 2024-006)
            </h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                @foreach([
                    ['Monthly Salary Credit', '₱' . number_format($sssContribution['salary_credit'], 2)],
                    ['Employee Share',        '₱' . number_format($sssContribution['employee_share'], 2)],
                    ['Total Contribution',    '₱' . number_format($sssContribution['total'], 2)],
                ] as [$lbl, $val])
                <div style="background:white; padding:14px; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em;">{{ $lbl }}</div>
                    <div style="font-weight:700; color:#1a1a2e; font-size:16px;">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- PhilHealth Contribution Breakdown --}}
        <div style="margin-top:16px; padding:20px; background:#ecfdf5; border-radius:16px; border:1px solid #a7f3d0;">
            <h4 style="margin:0 0 16px; font-size:13px; color:#065f46; font-weight:600; text-transform:uppercase; letter-spacing:0.08em;">
                <i class="fas fa-heartbeat"></i> &nbsp;PhilHealth Contribution (2025/2026)
            </h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                @foreach([
                    ['Salary Basis',   '₱' . number_format($philHealthContribution['salary_basis'], 2)],
                    ['Employee Rate',  number_format($philHealthContribution['employee_rate'] * 100, 1) . '%'],
                    ['Employee Share', '₱' . number_format($philHealthContribution['employee_share'], 2)],
                ] as [$lbl, $val])
                <div style="background:white; padding:14px; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em;">{{ $lbl }}</div>
                    <div style="font-weight:700; color:#1a1a2e; font-size:16px;">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Pag-IBIG Contribution Breakdown --}}
        <div style="margin-top:16px; padding:20px; background:#fef3c7; border-radius:16px; border:1px solid #fcd34d;">
            <h4 style="margin:0 0 16px; font-size:13px; color:#92400e; font-weight:600; text-transform:uppercase; letter-spacing:0.08em;">
                <i class="fas fa-home"></i> &nbsp;Pag-IBIG Contribution (2026)
            </h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                <div style="background:white; padding:14px; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em;">Monthly Salary</div>
                    <div style="font-weight:700; color:#1a1a2e; font-size:16px;">₱{{ number_format($pagIbigContribution['salary'], 2) }}</div>
                </div>
                @if($pagIbigContribution['employee_rate'] !== null)
                <div style="background:white; padding:14px; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em;">Employee Rate</div>
                    <div style="font-weight:700; color:#1a1a2e; font-size:16px;">{{ number_format($pagIbigContribution['employee_rate'] * 100, 1) }}%</div>
                </div>
                @endif
                <div style="background:white; padding:14px; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em;">Employee Share</div>
                    <div style="font-weight:700; color:#dc2626; font-size:16px;">₱{{ number_format($pagIbigContribution['employee_share'], 2) }}</div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
<script>
    // Data passed from Blade to JS for export
    const employeeData = {
        id:         '{{ $employee->employee_id }}',
        name:       '{{ addslashes($employee->full_name) }}',
        department: '{{ addslashes($employee->department) }}',
        position:   '{{ addslashes($employee->position) }}',
        status:     '{{ $employee->employment_status }}',
        salary:     '{{ number_format($employee->basic_salary, 2) }}',
        sssNumber:  '{{ $employee->sss_number ?? "—" }}',
        philNumber: '{{ $employee->philhealth_number ?? "—" }}',
        pagNumber:  '{{ $employee->pagibig_number ?? "—" }}',
        tinNumber:  '{{ $employee->tin_number ?? "—" }}',
        // SSS
        sssSalaryCredit:  '{{ number_format($sssContribution["salary_credit"], 2) }}',
        sssEmployeeShare: '{{ number_format($sssContribution["employee_share"], 2) }}',
        sssTotal:         '{{ number_format($sssContribution["total"], 2) }}',
        // PhilHealth
        philSalaryBasis:   '{{ number_format($philHealthContribution["salary_basis"], 2) }}',
        philEmployeeRate:  '{{ number_format($philHealthContribution["employee_rate"] * 100, 1) }}',
        philEmployeeShare: '{{ number_format($philHealthContribution["employee_share"], 2) }}',
        // Pag-IBIG
        pagSalary:       '{{ number_format($pagIbigContribution["salary"], 2) }}',
        pagEmployeeRate: '{{ $pagIbigContribution["employee_rate"] !== null ? number_format($pagIbigContribution["employee_rate"] * 100, 1) : "—" }}',
        pagEmployeeShare:'{{ number_format($pagIbigContribution["employee_share"], 2) }}',
    };

function printContributionDetail() {
    const d = employeeData;
    const win = window.open('', '_blank');
    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Government Contributions — ${d.name}</title>
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body { font-family:Arial, sans-serif; font-size:11px; color:#111; padding:20px; }
                .report-header { margin-bottom:20px; }
                .report-header h1 { font-size:16px; color:#1a1a2e; }
                .report-header p  { font-size:11px; color:#6b7280; margin-top:4px; }
                .profile-block { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:14px; margin-bottom:16px; display:flex; gap:24px; flex-wrap:wrap; }
                .profile-block .field { min-width:140px; }
                .profile-block .label { font-size:9px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:2px; }
                .profile-block .value { font-weight:600; color:#1a1a2e; font-size:12px; }
                .section { margin-bottom:16px; }
                .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:6px 10px; margin-bottom:8px; border-radius:4px; }
                .sss-title   { background:#dbeafe; color:#1e40af; }
                .phil-title  { background:#d1fae5; color:#065f46; }
                .pagibig-title { background:#fef3c7; color:#92400e; }
                .ids-title   { background:#f3f4f6; color:#374151; }
                table { width:100%; border-collapse:collapse; }
                th { background:#1e40af; color:white; padding:6px 10px; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.04em; }
                td { padding:6px 10px; border-bottom:1px solid #e5e7eb; font-size:11px; }
                td.amount { font-weight:600; }
                @media print { body { padding:0; } }
            </style>
        </head>
        <body>
            <div class="report-header">
                <h1>Government Contributions Report</h1>
                <p>Generated: ${new Date().toLocaleString()}</p>
            </div>

            <div class="profile-block">
                <div class="field"><div class="label">Employee ID</div><div class="value" style="font-family:monospace;">${d.id}</div></div>
                <div class="field"><div class="label">Full Name</div><div class="value">${d.name}</div></div>
                <div class="field"><div class="label">Department</div><div class="value">${d.department}</div></div>
                <div class="field"><div class="label">Position</div><div class="value">${d.position}</div></div>
                <div class="field"><div class="label">Basic Salary</div><div class="value">₱${d.salary}</div></div>
                <div class="field"><div class="label">Status</div><div class="value">${d.status}</div></div>
            </div>

            <div class="section">
                <div class="section-title ids-title">Government ID Numbers</div>
                <table>
                    <thead><tr><th>SSS Number</th><th>PhilHealth Number</th><th>Pag-IBIG Number</th><th>TIN Number</th></tr></thead>
                    <tbody>
                        <tr>
                            <td style="font-family:monospace;">${d.sssNumber}</td>
                            <td style="font-family:monospace;">${d.philNumber}</td>
                            <td style="font-family:monospace;">${d.pagNumber}</td>
                            <td style="font-family:monospace;">${d.tinNumber}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-title sss-title">SSS Contribution (Circular No. 2024-006)</div>
                <table>
                    <thead><tr><th>Monthly Salary Credit</th><th>Employee Share</th><th>Total Contribution</th></tr></thead>
                    <tbody>
                        <tr>
                            <td class="amount">₱${d.sssSalaryCredit}</td>
                            <td class="amount">₱${d.sssEmployeeShare}</td>
                            <td class="amount">₱${d.sssTotal}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-title phil-title">PhilHealth Contribution (2025/2026)</div>
                <table>
                    <thead><tr><th>Salary Basis</th><th>Employee Rate</th><th>Employee Share</th></tr></thead>
                    <tbody>
                        <tr>
                            <td class="amount">₱${d.philSalaryBasis}</td>
                            <td class="amount">${d.philEmployeeRate}%</td>
                            <td class="amount">₱${d.philEmployeeShare}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-title pagibig-title">Pag-IBIG Contribution (2026)</div>
                <table>
                    <thead><tr><th>Monthly Salary</th><th>Employee Rate</th><th>Employee Share</th></tr></thead>
                    <tbody>
                        <tr>
                            <td class="amount">₱${d.pagSalary}</td>
                            <td class="amount">${d.pagEmployeeRate !== '—' ? d.pagEmployeeRate + '%' : '—'}</td>
                            <td class="amount">₱${d.pagEmployeeShare}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
    win.print();
}

function exportContributionDetailCSV() {
    const d = employeeData;

    const rows = [
        // Employee info header block
        ['Field', 'Value'],
        ['Employee ID', d.id],
        ['Full Name',   d.name],
        ['Department',  d.department],
        ['Position',    d.position],
        ['Basic Salary',d.salary],
        ['Status',      d.status],
        [],
        // ID numbers
        ['SSS Number', 'PhilHealth Number', 'Pag-IBIG Number', 'TIN Number'],
        [d.sssNumber, d.philNumber, d.pagNumber, d.tinNumber],
        [],
        // SSS
        ['SSS — Monthly Salary Credit', 'SSS — Employee Share', 'SSS — Total Contribution'],
        [d.sssSalaryCredit, d.sssEmployeeShare, d.sssTotal],
        [],
        // PhilHealth
        ['PhilHealth — Salary Basis', 'PhilHealth — Employee Rate', 'PhilHealth — Employee Share'],
        [d.philSalaryBasis, d.philEmployeeRate + '%', d.philEmployeeShare],
        [],
        // Pag-IBIG
        ['Pag-IBIG — Monthly Salary', 'Pag-IBIG — Employee Rate', 'Pag-IBIG — Employee Share'],
        [d.pagSalary, d.pagEmployeeRate !== '—' ? d.pagEmployeeRate + '%' : '—', d.pagEmployeeShare],
    ];

    const csv = rows.map(row =>
        row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
    ).join('\n');

    const filename = `contributions_${d.id}.csv`;
    const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
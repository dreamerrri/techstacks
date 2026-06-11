@extends('layouts.app')

@section('title', 'Government Contributions')
@section('breadcrumb')
    <span>Manage Payroll</span>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:500;">Gov. Contributions</span>
@endsection
@section('content')

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div>
            <span class="aurora-badge aurora-badge-hr" style="margin-bottom:8px;">
                <i class="fas fa-id-card"></i> Government Contributions
            </span>
            <p style="color:#6b7280; margin:0;">View and manage employee government contribution rates.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="aurora-stats-grid" style="margin-bottom:24px;">
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#dc2626; background:rgba(220,38,38,0.1);">
                <i class="fas fa-users"></i>
            </div>
            <div class="aurora-stat-value">{{ $employees->total() }}</div>
            <div class="aurora-stat-label">Total Employees</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#10b981; background:rgba(16,185,129,0.1);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::active()->whereNotNull('sss_number')->count() }}</div>
            <div class="aurora-stat-label">With SSS</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#3b82f6; background:rgba(59,130,246,0.1);">
                <i class="fas fa-heart"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::active()->whereNotNull('philhealth_number')->count() }}</div>
            <div class="aurora-stat-label">With PhilHealth</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#f59e0b; background:rgba(245,158,11,0.1);">
                <i class="fas fa-home"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::active()->whereNotNull('pagibig_number')->count() }}</div>
            <div class="aurora-stat-label">With Pag-IBIG</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="aurora-card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">

        {{-- Sticky header: title + search --}}
        <div style="position:sticky; top:0; z-index:10; background:white; padding:20px 28px 0; border-radius:20px 20px 0 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
                <h2 class="aurora-card-title" style="margin:0; font-size:15px;">
                    <i class="fas fa-list"></i> Employee List
                </h2>
                <div style="display:flex; gap:8px;">
                    <button onclick="printContributionsTable()"
                            style="padding:8px 16px; background:#1e40af; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button onclick="exportContributionsCSV()"
                            style="padding:8px 16px; background:#065f46; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                </div>
            </div>

            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('government-contributions.index') }}"
                  style="display:flex; flex-wrap:wrap; gap:10px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, ID, email..."
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
                    <a href="{{ route('government-contributions.index') }}"
                       style="padding:8px 16px; background:#f3f4f6; color:#6b7280; border-radius:8px; text-decoration:none; font-size:14px;">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="user-table-wrapper" style="overflow-y:auto; max-height:53vh; padding:0 28px;">
            <table id="contributions-table" style="width:100%; border-collapse:collapse; font-size:14px; min-width:700px;">
                <thead style="position:sticky; top:0; z-index:5;">
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Employee ID</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Full Name</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Department</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Position</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Basic Salary</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">SSS Share</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">PhilHealth Share</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Pag-IBIG Share</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Status</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        @php
                            $statusStyles = [
                                'Regular'      => 'background:#d1fae5; color:#065f46;',
                                'Probationary' => 'background:#fef3c7; color:#92400e;',
                                'Contractual'  => 'background:#dbeafe; color:#1e40af;',
                                'Part-time'    => 'background:#f3f4f6; color:#374151;',
                            ];
                        @endphp
                        <tr style="border-bottom:1px solid #f3f4f6; transition:background 0.15s;">
                            <td style="padding:12px; font-family:monospace; color:#6b7280;">{{ $employee->employee_id }}</td>
                            <td style="padding:12px; font-weight:600; color:#1a1a2e;">
                                <a href="{{ route('government-contributions.show', $employee) }}"
                                   style="color:#1a1a2e; text-decoration:none;">
                                    {{ $employee->full_name }}
                                </a>
                            </td>
                            <td style="padding:12px; color:#6b7280;">{{ $employee->department }}</td>
                            <td style="padding:12px; color:#6b7280;">{{ $employee->position }}</td>
                            <td style="padding:12px; font-weight:600; color:#1a1a2e;">₱{{ number_format($employee->basic_salary, 2) }}</td>
                            <td style="padding:12px; font-weight:600; color:#dc2626;">₱{{ number_format($employee->sss_employee_share, 2) }}</td>
                            <td style="padding:12px; font-weight:600; color:#3b82f6;">₱{{ number_format($employee->philhealth_employee_share, 2) }}</td>
                            <td style="padding:12px; font-weight:600; color:#f59e0b;">₱{{ number_format($employee->pagibig_employee_share, 2) }}</td>
                            <td style="padding:12px;">
                                <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; {{ $statusStyles[$employee->employment_status] ?? '' }}">
                                    {{ $employee->employment_status }}
                                </span>
                            </td>
                            <td style="padding:12px;">
                                <a href="{{ route('government-contributions.show', $employee) }}"
                                   class="btn btn-sm" style="background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none; padding:5px 10px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding:40px; text-align:center; color:#9ca3af;">
                                <i class="fas fa-users" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="user-mobile-cards" style="padding:16px;">
            @forelse($employees as $employee)
                @php
                    $statusStyles = [
                        'Regular'      => 'background:#d1fae5; color:#065f46;',
                        'Probationary' => 'background:#fef3c7; color:#92400e;',
                        'Contractual'  => 'background:#dbeafe; color:#1e40af;',
                        'Part-time'    => 'background:#f3f4f6; color:#374151;',
                    ];
                @endphp
                <div class="user-card">
                    <div class="user-card-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:38px; height:38px; border-radius:50%; overflow:hidden; flex-shrink:0;">
                                @if($employee->user?->profile_photo)
                                    <img src="{{ asset('storage/' . $employee->user->profile_photo) }}"
                                         alt="{{ $employee->full_name }}"
                                         style="width:100%; height:100%; object-fit:cover;">
                                @else
                                    <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:700;">
                                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1a1a2e; font-size:14px;">
                                    <a href="{{ route('government-contributions.show', $employee) }}"
                                       style="color:#1a1a2e; text-decoration:none;">
                                        {{ $employee->full_name }}
                                    </a>
                                </div>
                                <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                        <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; {{ $statusStyles[$employee->employment_status] ?? '' }}">
                            {{ $employee->employment_status }}
                        </span>
                    </div>

                    <div style="margin-top:10px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                        <span><i class="fas fa-building" style="width:14px;"></i> {{ $employee->department }}</span>
                        <span><i class="fas fa-briefcase" style="width:14px;"></i> {{ $employee->position }}</span>
                        <span><i class="fas fa-money-bill-wave" style="width:14px;"></i> ₱{{ number_format($employee->basic_salary, 2) }}</span>
                    </div>

                    <div class="user-card-meta">
                        <a href="{{ route('government-contributions.show', $employee) }}"
                           style="padding:5px 12px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View Contributions
                        </a>
                    </div>
                </div>
            @empty
                <div style="padding:40px; text-align:center; color:#9ca3af;">
                    <i class="fas fa-users" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                    No employees found.
                </div>
            @endforelse
        </div>

    </div>

    <div style="padding:16px 28px; border-top:1px solid #e5e7eb;">{{ $employees->links() }}</div>

@endsection

@section('scripts')
<script>
function printContributionsTable() {
    const rows = document.querySelectorAll('#contributions-table tbody tr');

    const headers = ['Employee ID', 'Full Name', 'Department', 'Position', 'Basic Salary', 'Status'];

    let tableHTML = `
        <table>
            <thead>
                <tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr>
            </thead>
            <tbody>
    `;

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (!cells.length) return;

        const empId  = cells[0].innerText.trim();
        const name   = cells[1].querySelector('a')?.innerText.trim() ?? cells[1].innerText.trim();
        const dept   = cells[2].innerText.trim();
        const pos    = cells[3].innerText.trim();
        const salary = cells[4].innerText.trim();
        const status = cells[5].innerText.trim();

        tableHTML += `
            <tr>
                <td><span class="mono">${empId}</span></td>
                <td><strong>${name}</strong></td>
                <td>${dept}</td>
                <td>${pos}</td>
                <td>${salary}</td>
                <td>${status}</td>
            </tr>
        `;
    });

    tableHTML += `</tbody></table>`;

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
            <title>Government Contributions Report</title>
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body { font-family:Arial, sans-serif; font-size:11px; color:#111; padding:20px; }
                .report-header { margin-bottom:16px; }
                .report-header h1 { font-size:16px; color:#1a1a2e; }
                .report-header p  { font-size:11px; color:#6b7280; margin-top:4px; }
                table { width:100%; border-collapse:collapse; }
                th {
                    background:#1e40af; color:white;
                    padding:7px 8px; text-align:left;
                    font-size:10px; text-transform:uppercase; letter-spacing:0.04em;
                }
                td { padding:6px 8px; border-bottom:1px solid #e5e7eb; vertical-align:middle; }
                tr:nth-child(even) td { background:#f9fafb; }
                .mono { font-family:monospace; color:#6b7280; }
                @media print { body { padding:0; } }
            </style>
        </head>
        <body>
            <div class="report-header">
                <h1>Government Contributions Report</h1>
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

function exportContributionsCSV() {
    const searchVal = document.querySelector('input[name="search"]')?.value ?? '';
    const deptVal   = document.querySelector('select[name="department"]')?.value ?? '';

    const params = new URLSearchParams();
    if (searchVal) params.append('search', searchVal);
    if (deptVal) params.append('department', deptVal);

    const url = `{{ route('government-contributions.api.all-with-contributions') }}?${params.toString()}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const headers = ['Employee ID', 'Full Name', 'Department', 'Position', 'Basic Salary', 'SSS Share', 'PhilHealth Share', 'Pag-IBIG Share', 'Status'];

            function cleanAmount(val) {
                return val.replace(/[₱,]/g, '').trim();
            }

            let csvRows = [headers.join(',')];

            data.forEach(employee => {
                csvRows.push([
                    `"${employee.employee_id}"`,
                    `"${employee.full_name}"`,
                    `"${employee.department}"`,
                    `"${employee.position}"`,
                    cleanAmount(employee.basic_salary),
                    cleanAmount(employee.sss_employee_share),
                    cleanAmount(employee.philhealth_employee_share),
                    cleanAmount(employee.pagibig_employee_share),
                    `"${employee.employment_status}"`
                ].join(','));
            });

            const filename = deptVal
                ? `contributions_${deptVal.replace(/\s+/g, '_')}.csv`
                : 'contributions_all.csv';

            const blob = new Blob([csvRows.join('\n')], { type:'text/csv;charset=utf-8;' });
            const blobUrl  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = blobUrl;
            a.download = filename;
            a.click();
            URL.revokeObjectURL(blobUrl);
        })
        .catch(error => {
            console.error('Error exporting CSV:', error);
            alert('Error exporting CSV. Please try again.');
        });
}
</script>
@endsection
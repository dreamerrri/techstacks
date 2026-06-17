@extends('layouts.app')

@section('title', 'Government Contributions')
@section('breadcrumb')
    <span>Manage Payroll</span>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:500;">Gov. Contributions</span>
@endsection
@section('content')

@php
    // Build contribution data for JS (all employees currently on page)
    $contribData = [];
    foreach ($employees as $emp) {
        $sss     = app(\App\Services\SSSContributionService::class)->calculate($emp->basic_salary);
        $phil    = app(\App\Services\PhilHealthContributionService::class)->calculate($emp->basic_salary);
        $pagibig = app(\App\Services\PagIbigContributionService::class)->calculate($emp->basic_salary);

        $contribData[] = [
            'id'               => $emp->employee_id,
            'name'             => $emp->full_name,
            'department'       => $emp->department,
            'position'         => $emp->position,
            'status'           => $emp->employment_status,
            'salary'           => number_format($emp->basic_salary, 2),
            'show_url'         => route('government-contributions.show', $emp),
            // SSS
            'sssSalaryCredit'  => number_format($sss['salary_credit'], 2),
            'sssEmployeeShare' => number_format($emp->custom_sss_contribution ?? $sss['employee_share'], 2),
            'sssTotal'         => number_format($sss['total'], 2),
            // PhilHealth
            'philSalaryBasis'   => number_format($phil['salary_basis'], 2),
            'philEmployeeRate'  => number_format($phil['employee_rate'] * 100, 1),
            'philEmployeeShare' => number_format($emp->custom_philhealth_contribution ?? $phil['employee_share'], 2),
            // Pag-IBIG
            'pagSalary'        => number_format($pagibig['salary'], 2),
            'pagEmployeeRate'  => $pagibig['employee_rate'] !== null ? number_format($pagibig['employee_rate'] * 100, 1) : '—',
            'pagEmployeeShare' => number_format($emp->custom_pagibig_contribution ?? $pagibig['employee_share'], 2),
        ];
    }
    $deptLabel = request('department') ?: 'All Departments';
@endphp

{{-- ═══════════════════════════════════════════════
     CONTRIBUTION BREAKDOWN MODAL
     ═══════════════════════════════════════════════ --}}
<div id="contribBreakdownModal"
     style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:flex-start; justify-content:center; padding:20px; overflow-y:auto;">
    <div style="background:white; border-radius:16px; width:100%; max-width:90vw; margin:auto; box-shadow:0 24px 64px rgba(0,0,0,0.2); overflow:hidden;">

        {{-- Modal header --}}
        <div style="padding:20px 28px 16px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:white; border-radius:16px 16px 0 0;">
            <div>
                <div style="font-size:17px; font-weight:700; color:#1a1a2e; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-layer-group" style="color:#dc2626;"></i>
                    <span id="contribModalTitle">Contribution Breakdown</span>
                </div>
                <div style="font-size:12px; color:#6b7280; margin-top:3px;" id="contribModalMeta">—</div>
            </div>
            <button onclick="closeContribModal()"
                    style="background:#f3f4f6; border:none; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px; color:#6b7280; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal table --}}
        <div style="overflow-x:auto; overflow-y:auto; max-height:50vh; padding:0 0 4px;">
            <table id="contribBreakdownTable" style="width:100%; border-collapse:collapse; font-size:13px; min-width:900px;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:11px 16px; text-align:left;   color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Employee</th>
                        <th style="padding:11px 16px; text-align:left;   color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Dept</th>
                        <th style="padding:11px 16px; text-align:right;  color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Basic Salary</th>
                        <th style="padding:11px 16px; text-align:right;  color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">SSS Share</th>
                        <th style="padding:11px 16px; text-align:right;  color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">PhilHealth Share</th>
                        <th style="padding:11px 16px; text-align:right;  color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Pag-IBIG Share</th>
                        <th style="padding:11px 16px; text-align:right;  color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Total Contribs</th>
                        <th style="padding:11px 16px; text-align:center; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Status</th>
                    </tr>
                </thead>
                <tbody id="contribBreakdownBody">
                    {{-- filled by JS --}}
                </tbody>
                <tfoot id="contribBreakdownFoot">
                    {{-- totals row, filled by JS --}}
                </tfoot>
            </table>
            <div id="contribBreakdownEmpty" style="display:none; padding:40px; text-align:center; color:#9ca3af;">
                <i class="fas fa-inbox" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                No contribution data for the current filter.
            </div>
        </div>

        {{-- Modal footer --}}
        <div style="padding:14px 24px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button onclick="printContribBreakdown()"
                        style="padding:8px 18px; background:#1e40af; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fas fa-print"></i> Print PDF
                </button>
                <button onclick="exportContribBreakdownCSV()"
                        style="padding:8px 18px; background:#065f46; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </div>
            <button onclick="closeContribModal()"
                    style="padding:8px 20px; background:#f3f4f6; color:#374151; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer;">
                Close
            </button>
        </div>

    </div>
</div>

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <span class="aurora-badge aurora-badge-hr" style="margin-bottom:8px;">
            <i class="fas fa-id-card"></i> Government Contributions
        </span>
        <p style="color:#6b7280; margin:0;">View and manage employee government contribution rates.</p>
    </div>
</div>


{{-- Filters + Table --}}
<div class="aurora-card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">

    {{-- Sticky header --}}
    <div style="position:sticky; top:0; z-index:0; background:white; padding:20px 28px 0; border-radius:20px 20px 0 0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <h2 class="aurora-card-title" style="margin:0; font-size:15px;">
                <i class="fas fa-list"></i> Employee List
            </h2>
            <div style="display:flex; gap:8px;">
                <button onclick="openContribModal()"
                        style="padding:8px 18px; background:#dc2626; color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fas fa-layer-group"></i> Breakdown
                </button>
            </div>
        </div>

        {{-- Search & Filters — inline handlers, same pattern as payroll page --}}
        <form id="filter-form" method="GET" action="{{ route('government-contributions.index') }}"
              style="display:flex; flex-wrap:wrap; gap:10px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">

            <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                   placeholder="Search name, ID, email..."
                   oninput="clearTimeout(this._t); this._t = setTimeout(() => this.closest('form').submit(), 400)"
                   style="flex:1; min-width:160px; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">

            <select name="department" id="department-select"
                    onchange="this.closest('form').submit()"
                    style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>

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
                        <td style="padding:12px;">
                            <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; {{ $statusStyles[$employee->employment_status] ?? '' }}">
                                {{ $employee->employment_status }}
                            </span>
                        </td>
                        <td style="padding:12px;">
                            <a href="{{ route('government-contributions.show', $employee) }}"
                               class="btn btn-sm" style="background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none; padding:5px 10px;">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px; text-align:center; color:#9ca3af;">
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
// ─── Data from PHP ────────────────────────────────────────────────────────────
const CONTRIB_DATA = @json($contribData);
const DEPT_LABEL   = @json($deptLabel);

// ─── Helpers ─────────────────────────────────────────────────────────────────
function fmt(n) {
    return '₱' + parseFloat(String(n).replace(/,/g, '') || 0)
        .toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ─── Open modal ──────────────────────────────────────────────────────────────
function openContribModal() {
    const data  = CONTRIB_DATA;
    const body  = document.getElementById('contribBreakdownBody');
    const foot  = document.getElementById('contribBreakdownFoot');
    const empty = document.getElementById('contribBreakdownEmpty');
    const table = document.getElementById('contribBreakdownTable');

    document.getElementById('contribModalTitle').textContent =
        DEPT_LABEL !== 'All Departments'
            ? DEPT_LABEL + ' — Contribution Breakdown'
            : 'All Departments — Contribution Breakdown';

    document.getElementById('contribModalMeta').textContent =
        data.length + ' employee' + (data.length !== 1 ? 's' : '') + ' on this page';

    if (!data.length) {
        table.style.display = 'none';
        empty.style.display = 'block';
        document.getElementById('contribBreakdownModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        return;
    }

    table.style.display = '';
    empty.style.display = 'none';

    const statusColors = {
        'Regular':      'background:#d1fae5; color:#065f46;',
        'Probationary': 'background:#fef3c7; color:#92400e;',
        'Contractual':  'background:#dbeafe; color:#1e40af;',
        'Part-time':    'background:#f3f4f6; color:#374151;',
    };

    let totSss = 0, totPhil = 0, totPag = 0, totAll = 0;

    body.innerHTML = data.map((emp, idx) => {
        const sss  = parseFloat(String(emp.sssEmployeeShare).replace(/,/g, ''));
        const phil = parseFloat(String(emp.philEmployeeShare).replace(/,/g, ''));
        const pag  = parseFloat(String(emp.pagEmployeeShare).replace(/,/g, ''));
        const total = sss + phil + pag;

        totSss  += sss;
        totPhil += phil;
        totPag  += pag;
        totAll  += total;

        const rowBg  = idx % 2 === 0 ? '' : 'background:#f9fafb;';
        const badge  = statusColors[emp.status] ?? 'background:#f3f4f6; color:#374151;';

        return `
            <tr style="border-bottom:1px solid #f3f4f6; ${rowBg}">
                <td style="padding:10px 16px;">
                    <div style="font-weight:600; color:#1a1a2e; font-size:13px;">
                        <a href="${emp.show_url}" style="color:#1a1a2e; text-decoration:none;">${emp.name}</a>
                    </div>
                    <div style="font-size:11px; color:#9ca3af; font-family:monospace;">${emp.id}</div>
                </td>
                <td style="padding:10px 16px; color:#6b7280; font-size:13px;">${emp.department}</td>
                <td style="padding:10px 16px; text-align:right; font-weight:600; color:#1a1a2e;">₱${emp.salary}</td>
                <td style="padding:10px 16px; text-align:right; color:#dc2626;">${fmt(sss)}</td>
                <td style="padding:10px 16px; text-align:right; color:#3b82f6;">${fmt(phil)}</td>
                <td style="padding:10px 16px; text-align:right; color:#f59e0b;">${fmt(pag)}</td>
                <td style="padding:10px 16px; text-align:right; font-weight:700; color:#1a1a2e;">${fmt(total)}</td>
                <td style="padding:10px 16px; text-align:center;">
                    <span style="${badge} padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap;">${emp.status}</span>
                </td>
            </tr>`;
    }).join('');

    foot.innerHTML = '';

    document.getElementById('contribBreakdownModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeContribModal() {
    document.getElementById('contribBreakdownModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Move modal to body on load
document.addEventListener('DOMContentLoaded', function () {
    document.body.appendChild(document.getElementById('contribBreakdownModal'));
    document.getElementById('contribBreakdownModal').addEventListener('click', function (e) {
        if (e.target === this) closeContribModal();
    });
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeContribModal();
});

// ─── Print PDF ────────────────────────────────────────────────────────────────
function printContribBreakdown() {
    const data = CONTRIB_DATA;
    if (!data.length) { alert('No data to print.'); return; }

    const searchVal  = document.getElementById('search-input')?.value ?? '';
    const deptVal    = document.getElementById('department-select')?.value ?? '';
    const filterNote = [
        searchVal ? `Search: "${searchVal}"` : '',
        deptVal   ? `Department: ${deptVal}` : ''
    ].filter(Boolean).join(' | ') || 'All Employees';

    let totSss = 0, totPhil = 0, totPag = 0, totAll = 0;
    let rows = '';

    data.forEach(emp => {
        const sss  = parseFloat(String(emp.sssEmployeeShare).replace(/,/g, ''));
        const phil = parseFloat(String(emp.philEmployeeShare).replace(/,/g, ''));
        const pag  = parseFloat(String(emp.pagEmployeeShare).replace(/,/g, ''));
        const total = sss + phil + pag;
        totSss += sss; totPhil += phil; totPag += pag; totAll += total;

        rows += `
            <tr>
                <td><strong>${emp.name}</strong><br><small>${emp.id}</small></td>
                <td>${emp.department}</td>
                <td class="num">₱${emp.salary}</td>
                <td class="num red">${fmt(sss)}</td>
                <td class="num blue">${fmt(phil)}</td>
                <td class="num amber">${fmt(pag)}</td>
                <td class="num bold">${fmt(total)}</td>
                <td>${emp.status}</td>
            </tr>`;
    });

    const win = window.open('', '_blank');
    win.document.write(`
        <!DOCTYPE html><html><head>
        <title>Government Contributions Report</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family:Arial,sans-serif; font-size:11px; color:#111; padding:20px; }
            h1  { font-size:16px; color:#1a1a2e; margin-bottom:4px; }
            .meta { font-size:11px; color:#6b7280; margin-bottom:16px; }
            table { width:100%; border-collapse:collapse; }
            thead th { background:#1e40af; color:white; padding:7px 8px; font-size:10px; text-transform:uppercase; letter-spacing:0.04em; text-align:left; }
            thead th.num { text-align:right; }
            td { padding:6px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top; }
            td.num   { text-align:right; }
            td.red   { color:#dc2626; }
            td.blue  { color:#2563eb; }
            td.amber { color:#d97706; }
            td.bold  { font-weight:700; }
            tr:nth-child(even) td { background:#f9fafb; }
            small { color:#6b7280; font-size:10px; font-family:monospace; }
            tfoot tr td { background:#fef2f2; font-weight:700; border-top:2px solid #fecaca; padding:8px; }
            tfoot tr td.num { text-align:right; }
            .total-bar { margin-top:12px; background:#fee2e2; border-radius:8px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; }
            .total-bar span { font-size:11px; color:#991b1b; }
            .total-bar strong { font-size:15px; color:#991b1b; }
            @media print { body { padding:0; } }
        </style>
        </head><body>
        <h1>Government Contributions Report</h1>
        <div class="meta">Filter: ${filterNote} &nbsp;|&nbsp; Printed: ${new Date().toLocaleString()}</div>
        <table>
            <thead>
                <tr>
                    <th>Employee</th><th>Dept</th>
                    <th class="num">Basic Salary</th>
                    <th class="num">SSS Share</th>
                    <th class="num">PhilHealth Share</th>
                    <th class="num">Pag-IBIG Share</th>
                    <th class="num">Total Contribs</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Totals (${data.length} employees)</td>
                    <td class="num">—</td>
                    <td class="num red">${fmt(totSss)}</td>
                    <td class="num blue">${fmt(totPhil)}</td>
                    <td class="num amber">${fmt(totPag)}</td>
                    <td class="num bold">${fmt(totAll)}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <div class="total-bar">
            <span><strong>Total Government Contributions</strong></span>
            <strong>${fmt(totAll)}</strong>
        </div>
        </body></html>`);
    win.document.close();
    win.focus();
    win.print();
}

// ─── Export CSV ───────────────────────────────────────────────────────────────
function exportContribBreakdownCSV() {
    const data = CONTRIB_DATA;
    if (!data.length) { alert('No data to export.'); return; }

    const headers = ['Employee', 'Employee ID', 'Department', 'Basic Salary', 'SSS Share', 'PhilHealth Share', 'Pag-IBIG Share', 'Total Contributions', 'Status'];
    let csvRows = [headers.join(',')];
    let totAll  = 0;

    data.forEach(emp => {
        const sss   = parseFloat(String(emp.sssEmployeeShare).replace(/,/g, ''));
        const phil  = parseFloat(String(emp.philEmployeeShare).replace(/,/g, ''));
        const pag   = parseFloat(String(emp.pagEmployeeShare).replace(/,/g, ''));
        const total = sss + phil + pag;
        totAll += total;

        csvRows.push([
            `"${emp.name}"`,
            `"${emp.id}"`,
            `"${emp.department}"`,
            emp.salary,
            sss.toFixed(2),
            phil.toFixed(2),
            pag.toFixed(2),
            total.toFixed(2),
            `"${emp.status}"`,
        ].join(','));
    });

    csvRows.push(['', '', '"TOTALS"', '', '', '', '', totAll.toFixed(2), ''].join(','));

    const deptVal  = document.getElementById('department-select')?.value ?? '';
    const filename = deptVal
        ? `contributions_${deptVal.replace(/\s+/g, '_')}.csv`
        : 'contributions_all.csv';

    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
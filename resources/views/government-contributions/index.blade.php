@extends('layouts.app')

@section('title', 'Government Contributions')
@section('breadcrumb')
    <span>Manage Payroll</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-medium">Gov. Contributions</span>
@endsection

@section('content')

@php
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
            'sssSalaryCredit'  => number_format($sss['salary_credit'], 2),
            'sssEmployeeShare' => number_format($emp->custom_sss_contribution ?? $sss['employee_share'], 2),
            'sssTotal'         => number_format($sss['total'], 2),
            'philSalaryBasis'   => number_format($phil['salary_basis'], 2),
            'philEmployeeRate'  => number_format($phil['employee_rate'] * 100, 1),
            'philEmployeeShare' => number_format($emp->custom_philhealth_contribution ?? $phil['employee_share'], 2),
            'pagSalary'        => number_format($pagibig['salary'], 2),
            'pagEmployeeRate'  => $pagibig['employee_rate'] !== null ? number_format($pagibig['employee_rate'] * 100, 1) : '—',
            'pagEmployeeShare' => number_format($emp->custom_pagibig_contribution ?? $pagibig['employee_share'], 2),
        ];
    }
    $deptLabel = request('department') ?: 'All Departments';
@endphp

{{-- Contribution Breakdown Modal --}}
<div id="contribBreakdownModal"
     class="hidden fixed inset-0 z-[9999] bg-black/50 items-start justify-center p-5 overflow-y-auto">
    <div class="bg-white rounded-2xl w-full max-w-[90vw] mx-auto shadow-2xl overflow-hidden">

        {{-- Modal header --}}
        <div class="px-7 py-5 border-b border-gray-200 flex justify-between items-center">
            <div>
                <div class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <i class="icon-[ph--stack-fill] text-red-600"></i>
                    <span id="contribModalTitle">Contribution Breakdown</span>
                </div>
                <div class="text-xs text-gray-500 mt-1" id="contribModalMeta">—</div>
            </div>
            <button onclick="closeContribModal()" class="btn btn-ghost btn-sm btn-circle">
                <i class="icon-[ph--x-fill]"></i>
            </button>
        </div>

        {{-- Modal table --}}
        <div class="overflow-x-auto overflow-y-auto max-h-[50vh]">
            <table id="contribBreakdownTable" class="table table-hover w-full text-xs min-w-[900px]">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Dept</th>
                        <th class="text-right">Basic Salary</th>
                        <th class="text-right">SSS Share</th>
                        <th class="text-right">PhilHealth Share</th>
                        <th class="text-right">Pag-IBIG Share</th>
                        <th class="text-right">Total Contribs</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="contribBreakdownBody"></tbody>
                <tfoot id="contribBreakdownFoot"></tfoot>
            </table>
            <div id="contribBreakdownEmpty" class="hidden py-10 text-center text-gray-400">
                <i class="icon-[ph--tray-fill] text-3xl mb-2 block"></i>
                No contribution data for the current filter.
            </div>
        </div>

        {{-- Modal footer --}}
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center flex-wrap gap-2">
            <div class="flex gap-2 flex-wrap">
                <button onclick="printContribBreakdown()" class="btn btn-soft btn-info btn-sm">
                    <i class="icon-[ph--printer-fill]"></i> Print PDF
                </button>
                <button onclick="exportContribBreakdownCSV()" class="btn btn-soft btn-success btn-sm">
                    <i class="icon-[ph--file-csv-fill]"></i> Export CSV
                </button>
            </div>
            <button onclick="closeContribModal()" class="btn btn-soft btn-sm">Close</button>
        </div>
    </div>
</div>

{{-- Header --}}
<div class="flex justify-between items-center flex-wrap gap-3 mb-6">
    <div>
        <span class="badge badge-soft badge-success mb-2">
            <i class="icon-[ph--identification-card-fill]"></i> Government Contributions
        </span>
        <p class="text-gray-500 m-0">View and manage employee government contribution rates.</p>
    </div>
</div>

{{-- Filters + Table --}}
<div class="card bg-base-100 shadow-sm overflow-hidden flex flex-col p-0">

    {{-- Card header --}}
    <div class="sticky top-0 z-10 bg-white px-7 pt-5 rounded-t-2xl">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 flex items-center gap-2 m-0">
                <i class="icon-[ph--list-fill]"></i> Employee List
            </h2>
            <button onclick="openContribModal()" class="btn btn-soft btn-error btn-sm">
                <i class="icon-[ph--stack-fill]"></i> Breakdown
            </button>
        </div>

     <form id="filter-form" method="GET" action="{{ route('government-contributions.index') }}"
      class="flex flex-col md:flex-row md:items-center gap-3 pb-4 border-b border-gray-200">

    {{-- Search group --}}
    <div class="join flex-none w-64 min-w-40">
        <input type="text" name="search" id="search-input" value="{{ request('search') }}"
               placeholder="Search name, ID, email..."
               oninput="clearTimeout(this._t); this._t = setTimeout(() => this.closest('form').submit(), 400)"
               class="input input-bordered input-sm join-item w-full">
        <button type="submit" class="btn btn-soft btn-error btn-sm join-item">
            <i class="icon-[ph--magnifying-glass-fill]"></i>
        </button>
        
    </div>

    

    {{-- Filters group --}}
    <div class="flex flex-row gap-2 md:ml-auto">
        <select name="department" id="department-select" 
        onchange="this.closest('form').submit()"
        class="select select-bordered select-sm">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        <select name="status" id="status-select"
        onchange="this.closest('form').submit()" 
        class="select select-bordered select-sm">
            <option value="">All Status</option>
            @foreach(['Regular','Probationary','Contractual','Part-time'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','department','status']))
<a href="{{ route('government-contributions.index') }}" class="btn btn-soft btn-sm">Clear</a>
        @endif
    </div>
</form>
   
</div>



    {{-- Desktop Table --}}
    <div class="table-responsive overflow-y-auto max-h-[53vh] px-7 hidden md:block">
        <table id="contributions-table" class="table table-hover w-full text-sm">
           <thead class="sticky top-0 z-5" style="background: white">
                <tr>
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php
                        $statusClass = match($employee->employment_status) {
                            'Regular'      => 'badge-soft badge-success',
                            'Probationary' => 'badge-soft badge-warning',
                            'Contractual'  => 'badge-soft badge-info',
                            'Part-time'    => 'badge-soft badge-neutral',
                            default        => 'badge-soft',
                        };
                    @endphp
                    <tr>
                        <td class="font-mono text-gray-500">{{ $employee->employee_id }}</td>
                        <td class="font-semibold text-gray-800">
                            <a href="{{ route('government-contributions.show', $employee) }}"
                               class="text-gray-800 no-underline hover:text-emerald-600">
                                {{ $employee->full_name }}
                            </a>
                        </td>
                        <td class="text-gray-500">{{ $employee->department }}</td>
                        <td class="text-gray-500">{{ $employee->position }}</td>
                        <td><span class="badge {{ $statusClass }}">{{ $employee->employment_status }}</span></td>
                        <td>
                            <a href="{{ route('government-contributions.show', $employee) }}"
                               class="btn btn-soft btn-info btn-sm">
                                <i class="icon-[ph--eye-fill]"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-gray-400">
                            <i class="icon-[ph--user-fill] text-3xl mb-2 block"></i>
                            No employees found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden p-4 flex flex-col gap-3">
        @forelse($employees as $employee)
            @php
                $statusClass = match($employee->employment_status) {
                    'Regular'      => 'badge-soft badge-success',
                    'Probationary' => 'badge-soft badge-warning',
                    'Contractual'  => 'badge-soft badge-info',
                    'Part-time'    => 'badge-soft badge-neutral',
                    default        => 'badge-soft',
                };
            @endphp
            <div class="card bg-base-100 border border-gray-200 p-4">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                            @if($employee->user?->profile_photo)
                                <img src="{{ asset('storage/' . $employee->user->profile_photo) }}"
                                     alt="{{ $employee->full_name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-sm font-bold">
                                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('government-contributions.show', $employee) }}"
                               class="font-semibold text-gray-800 no-underline text-sm hover:text-emerald-600">
                                {{ $employee->full_name }}
                            </a>
                            <div class="text-xs text-gray-500 font-mono">{{ $employee->employee_id }}</div>
                        </div>
                    </div>
                    <span class="badge {{ $statusClass }} whitespace-nowrap">{{ $employee->employment_status }}</span>
                </div>

                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mt-2">
                    <span><i class="icon-[ph--buildings-fill] w-3.5"></i> {{ $employee->department }}</span>
                    <span><i class="icon-[ph--briefcase-fill] w-3.5"></i> {{ $employee->position }}</span>
                    <span><i class="icon-[ph--money-fill] w-3.5"></i> ₱{{ number_format($employee->basic_salary, 2) }}</span>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('government-contributions.show', $employee) }}"
                       class="btn btn-soft btn-info btn-sm">
                        <i class="icon-[ph--eye-fill]"></i> View Contributions
                    </a>
                </div>
            </div>
        @empty
            <div class="py-10 text-center text-gray-400">
                <i class="icon-[ph--user-fill] text-3xl mb-2 block"></i>
                No employees found.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="px-7 py-4 border-t border-gray-200">
        {{ $employees->links() }}
    </div>

</div>

@endsection

@section('scripts')
<script>
const CONTRIB_DATA = @json($contribData);
const DEPT_LABEL   = @json($deptLabel);

function fmt(n) {
    return '₱' + parseFloat(String(n).replace(/,/g, '') || 0)
        .toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function openContribModal() {
    const data  = CONTRIB_DATA;
    const body  = document.getElementById('contribBreakdownBody');
    const foot  = document.getElementById('contribBreakdownFoot');
    const empty = document.getElementById('contribBreakdownEmpty');
    const table = document.getElementById('contribBreakdownTable');
    const modal = document.getElementById('contribBreakdownModal');

    document.getElementById('contribModalTitle').textContent =
        DEPT_LABEL !== 'All Departments'
            ? DEPT_LABEL + ' — Contribution Breakdown'
            : 'All Departments — Contribution Breakdown';

    document.getElementById('contribModalMeta').textContent =
        data.length + ' employee' + (data.length !== 1 ? 's' : '') + ' on this page';

    if (!data.length) {
        table.classList.add('hidden');
        empty.classList.remove('hidden');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        return;
    }

    table.classList.remove('hidden');
    empty.classList.add('hidden');

    let totSss = 0, totPhil = 0, totPag = 0, totAll = 0;

    body.innerHTML = data.map((emp) => {
        const sss   = parseFloat(String(emp.sssEmployeeShare).replace(/,/g, ''));
        const phil  = parseFloat(String(emp.philEmployeeShare).replace(/,/g, ''));
        const pag   = parseFloat(String(emp.pagEmployeeShare).replace(/,/g, ''));
        const total = sss + phil + pag;
        totSss += sss; totPhil += phil; totPag += pag; totAll += total;

        const statusClass = {
            'Regular':      'badge-soft badge-success',
            'Probationary': 'badge-soft badge-warning',
            'Contractual':  'badge-soft badge-info',
            'Part-time':    'badge-soft badge-neutral',
        }[emp.status] ?? 'badge-soft';

        return `
            <tr>
                <td><div class="font-semibold text-gray-800"><a href="${emp.show_url}" class="text-gray-800 no-underline hover:text-emerald-600">${emp.name}</a></div><div class="text-xs text-gray-400 font-mono">${emp.id}</div></td>
                <td class="text-gray-500">${emp.department}</td>
                <td class="text-right font-semibold">₱${emp.salary}</td>
                <td class="text-right text-red-600">${fmt(sss)}</td>
                <td class="text-right text-blue-600">${fmt(phil)}</td>
                <td class="text-right text-amber-600">${fmt(pag)}</td>
                <td class="text-right font-bold">${fmt(total)}</td>
                <td class="text-center"><span class="badge ${statusClass} whitespace-nowrap">${emp.status}</span></td>
            </tr>`;
    }).join('');

    foot.innerHTML = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeContribModal() {
    const modal = document.getElementById('contribBreakdownModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function () {
    document.body.appendChild(document.getElementById('contribBreakdownModal'));
    document.getElementById('contribBreakdownModal').addEventListener('click', function (e) {
        if (e.target === this) closeContribModal();
    });
});

document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeContribModal(); });

function printContribBreakdown() {
    const data = CONTRIB_DATA;
    if (!data.length) { window.notyf.error('No data to print.'); return; }

    const searchVal  = document.getElementById('search-input')?.value ?? '';
    const deptVal    = document.getElementById('department-select')?.value ?? '';
    const filterNote = [searchVal ? `Search: "${searchVal}"` : '', deptVal ? `Department: ${deptVal}` : ''].filter(Boolean).join(' | ') || 'All Employees';

    let totSss = 0, totPhil = 0, totPag = 0, totAll = 0;
    let rows = '';

    data.forEach(emp => {
        const sss   = parseFloat(String(emp.sssEmployeeShare).replace(/,/g, ''));
        const phil  = parseFloat(String(emp.philEmployeeShare).replace(/,/g, ''));
        const pag   = parseFloat(String(emp.pagEmployeeShare).replace(/,/g, ''));
        const total = sss + phil + pag;
        totSss += sss; totPhil += phil; totPag += pag; totAll += total;
        rows += `<tr><td><strong>${emp.name}</strong><br><small>${emp.id}</small></td><td>${emp.department}</td><td class="num">₱${emp.salary}</td><td class="num red">${fmt(sss)}</td><td class="num blue">${fmt(phil)}</td><td class="num amber">${fmt(pag)}</td><td class="num bold">${fmt(total)}</td><td>${emp.status}</td></tr>`;
    });

    const win = window.open('', '_blank');
    win.document.write(`<!DOCTYPE html><html><head><title>Government Contributions Report</title>
        <style>* { margin:0; padding:0; box-sizing:border-box; } body { font-family:Arial,sans-serif; font-size:11px; color:#111; padding:20px; }
        h1 { font-size:16px; color:#1a1a2e; margin-bottom:4px; } .meta { font-size:11px; color:#6b7280; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; } thead th { background:#1e40af; color:white; padding:7px 8px; font-size:10px; text-transform:uppercase; text-align:left; }
        thead th.num { text-align:right; } td { padding:6px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top; }
        td.num { text-align:right; } td.red { color:#dc2626; } td.blue { color:#2563eb; } td.amber { color:#d97706; } td.bold { font-weight:700; }
        tr:nth-child(even) td { background:#f9fafb; } small { color:#6b7280; font-size:10px; font-family:monospace; }
        tfoot tr td { background:#fef2f2; font-weight:700; border-top:2px solid #fecaca; padding:8px; } tfoot tr td.num { text-align:right; }
        .total-bar { margin-top:12px; background:#fee2e2; border-radius:8px; padding:12px 16px; display:flex; justify-content:space-between; }
        .total-bar span, .total-bar strong { font-size:11px; color:#991b1b; } @media print { body { padding:0; } }</style>
        </head><body>
        <h1>Government Contributions Report</h1>
        <div class="meta">Filter: ${filterNote} | Printed: ${new Date().toLocaleString()}</div>
        <table><thead><tr><th>Employee</th><th>Dept</th><th class="num">Basic Salary</th><th class="num">SSS Share</th><th class="num">PhilHealth Share</th><th class="num">Pag-IBIG Share</th><th class="num">Total</th><th>Status</th></tr></thead>
        <tbody>${rows}</tbody>
        <tfoot><tr><td colspan="2">Totals (${data.length} employees)</td><td class="num">—</td><td class="num red">${fmt(totSss)}</td><td class="num blue">${fmt(totPhil)}</td><td class="num amber">${fmt(totPag)}</td><td class="num bold">${fmt(totAll)}</td><td></td></tr></tfoot>
        </table>
        <div class="total-bar"><span><strong>Total Government Contributions</strong></span><strong>${fmt(totAll)}</strong></div>
        </body></html>`);
    win.document.close(); win.focus(); win.print();
}

function exportContribBreakdownCSV() {
    const data = CONTRIB_DATA;
    if (!data.length) { window.notyf.error('No data to export.'); return; }

    const headers = ['Employee','Employee ID','Department','Basic Salary','SSS Share','PhilHealth Share','Pag-IBIG Share','Total Contributions','Status'];
    let csvRows = [headers.join(',')];
    let totAll = 0;

    data.forEach(emp => {
        const sss   = parseFloat(String(emp.sssEmployeeShare).replace(/,/g, ''));
        const phil  = parseFloat(String(emp.philEmployeeShare).replace(/,/g, ''));
        const pag   = parseFloat(String(emp.pagEmployeeShare).replace(/,/g, ''));
        const total = sss + phil + pag;
        totAll += total;
        csvRows.push([`"${emp.name}"`,`"${emp.id}"`,`"${emp.department}"`,emp.salary,sss.toFixed(2),phil.toFixed(2),pag.toFixed(2),total.toFixed(2),`"${emp.status}"`].join(','));
    });

    csvRows.push(['','','"TOTALS"','','','','',totAll.toFixed(2),''].join(','));

    const deptVal  = document.getElementById('department-select')?.value ?? '';
    const filename = deptVal ? `contributions_${deptVal.replace(/\s+/g, '_')}.csv` : 'contributions_all.csv';
    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
@extends('layouts.app')

@section('title', 'Payroll Preview')
@section('breadcrumb')
    <span>Manage Payroll</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-medium">Payroll</span>
@endsection

@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR    = $user->isHR();

    $avatarClass = $isAdmin ? 'from-red-600 to-red-800'
                 : ($isHR   ? 'from-blue-600 to-blue-800'
                            : 'from-violet-500 to-violet-700');

    $deptBreakdownData = [];
    foreach ($employees as $emp) {
        $p = $payrollData[$emp->id] ?? [];
        if (($p['gross_pay'] ?? 0) == 0) continue;
        $deptBreakdownData[] = [
            'name'                    => $emp->full_name,
            'employee_id'             => $emp->employee_id,
            'department'              => $emp->department,
            'basic_pay'               => $p['base_pay'] ?? 0,
            'allowance_benefits'      => $p['allowance_benefits'] ?? 0,
            'overtime_pay'            => $p['overtime_pay'] ?? 0,
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

{{-- Department Breakdown Modal --}}
<div id="deptBreakdownModal"
     class="hidden fixed inset-0 z-[9999] bg-black/50 items-start justify-center p-5 overflow-y-auto">
    <div class="bg-white rounded-2xl w-full max-w-[90vw] mx-auto shadow-2xl overflow-hidden">
        <div class="px-7 py-5 border-b border-gray-200 flex justify-between items-center">
            <div>
                <div class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <i class="icon-[ph--stack-fill] text-blue-600"></i>
                    <span id="deptModalTitle">Department Breakdown</span>
                </div>
                <div class="text-xs text-gray-500 mt-1" id="deptModalMeta">—</div>
            </div>
            <button onclick="closeDeptModal()" class="btn btn-ghost btn-sm btn-circle">
                <i class="icon-[ph--x-fill]"></i>
            </button>
        </div>

        <div class="overflow-x-hidden overflow-y-auto max-h-[50vh]">
            <table id="deptBreakdownTable" class="table table-hover table-fixed w-full text-xs">
                <colgroup>
                    <col class="w-44">  {{-- Employee --}}
                    <col class="w-28">  {{-- Dept --}}
                    <col class="w-24">  {{-- Basic Pay --}}
                    <col class="w-24">  {{-- Allowance --}}
                    <col class="w-20">  {{-- OT Pay --}}
                    <col class="w-24">  {{-- Earnings --}}
                    <col class="w-20">  {{-- SSS --}}
                    <col class="w-24">  {{-- PhilHealth --}}
                    <col class="w-20">  {{-- Pag-IBIG --}}
                    <col class="w-16">  {{-- Tax --}}
                    <col class="w-28">  {{-- Total Deductions --}}
                    <col class="w-24">  {{-- Net Pay --}}
                </colgroup>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Dept</th>
                        <th class="text-right">Basic Pay</th>
                        <th class="text-right">Allowance</th>
                        <th class="text-right">OT Pay</th>
                        <th class="text-right">Earnings</th>
                        <th class="text-right">SSS</th>
                        <th class="text-right">PhilHealth</th>
                        <th class="text-right">Pag-IBIG</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Total Deductions</th>
                        <th class="text-right">Net Pay</th>
                    </tr>
                </thead>
                <tbody id="deptBreakdownBody"></tbody>
                <tfoot id="deptBreakdownFoot"></tfoot>
            </table>
            <div id="deptBreakdownEmpty" class="hidden py-10 text-center text-gray-400">
                <i class="icon-[ph--tray-fill] text-3xl mb-2 block"></i>
                No payroll data for the current filter.
            </div>
        </div>

        <div id="deptGrossPayBar" class="hidden mx-6 px-5 py-3 bg-emerald-100 rounded-b-xl flex justify-between items-center flex-wrap gap-3">
            <div class="text-sm font-bold text-emerald-800">
                <i class="icon-[ph--money-fill] mr-1"></i> Total Gross Pay:
            </div>
            <span id="deptTotalGrossPay" class="text-xl font-extrabold text-emerald-800">₱0.00</span>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center flex-wrap gap-2">
            <div class="flex gap-2 flex-wrap">
                <button onclick="printPayrollTable()" class="btn btn-soft btn-info btn-sm">
                    <i class="icon-[ph--printer-fill]"></i> Print PDF
                </button>
                <button onclick="exportPayrollCSV()" class="btn btn-soft btn-success btn-sm">
                    <i class="icon-[ph--file-csv-fill]"></i> Export CSV
                </button>
            </div>
            <button onclick="closeDeptModal()" class="btn btn-soft btn-sm">Close</button>
        </div>
    </div>
</div>

{{-- Header --}}
<div class="flex justify-between items-center flex-wrap gap-3 mb-6">
    <div>
        <span class="badge badge-soft badge-warning mb-2">
            <i class="icon-[ph--money-fill]"></i> Payroll Preview
        </span>
        <p class="text-gray-500 m-0">
            @if($isAdmin || $isHR)
                View payroll calculations for all employees.
            @else
                View your payroll calculation.
            @endif
        </p>
    </div>
</div>

{{-- Filters + Table --}}
<div class="card bg-base-100 shadow-sm overflow-hidden flex flex-col p-0">

    <div class="sticky top-0 z-10 bg-white px-7 pt-5 rounded-t-2xl">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 flex items-center gap-2 m-0">
                <i class="icon-[ph--list-fill]"></i> Payroll Summary
            </h2>
            @if($isAdmin || $isHR)
                <button onclick="openDeptModal()" class="btn btn-soft btn-primary btn-sm">
                    <i class="icon-[ph--stack-fill]"></i> Breakdown
                </button>
            @endif
        </div>

        <form method="GET" action="{{ route('payroll.index') }}"
      class="flex flex-col md:flex-row md:items-center gap-3 pb-4 ">
 @if($isAdmin || $isHR)
    {{-- Search group --}}
    <div class="join flex-none w-64 min-w-40">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name, ID, email..."
                oninput="clearTimeout(this._t); this._t = setTimeout(() => this.closest('form').submit(), 400)"   {{-- Could be removed for consistency, come back later --}}
               class="input input-bordered input-sm join-item w-full border-gray-300">
               
        <button type="submit" class="btn btn-outline btn-sm join-item border-gray-300">
            <i class="icon-[ph--magnifying-glass-fill]"></i>
        </button>
    </div>
    
  @endif
          @if($selectedPeriod ?? null)
        <div class="px-7 py-2 text-blue-700">
            <i class="icon-[ph--calendar-fill]"></i>
            <span>Showing payroll for cutoff:</span>
            <strong>{{ $selectedPeriod->cutoff_start->format('M d, Y') }} – {{ $selectedPeriod->cutoff_end->format('M d, Y') }}</strong>
            <span class="badge {{ $selectedPeriod->status === 'finalized' ? 'badge-soft badge-success' : 'badge-soft badge-warning' }} badge-xs">
                {{ ucfirst($selectedPeriod->status) }}
            </span>
            <span class="text-gray-400 ml-1">Payroll date: {{ $selectedPeriod->payroll_date->format('M d, Y') }}</span>
        </div>
    @endif


   {{-- Filters group --}}
<div class="flex flex-row gap-2 md:ml-auto">
    @if($isAdmin || $isHR)
        <select name="department"
                onchange="this.closest('form').submit()"
                class="select select-bordered select-sm">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
    @endif
    <select name="payroll_period_id"
            onchange="this.closest('form').submit()"  {{-- auto reloads form --}}
            class="select select-bordered select-sm">
        <option value="">Latest Cutoff</option>
        @foreach($payrollPeriods as $period)
            <option value="{{ $period->id }}"
                {{ (string) request('payroll_period_id') === (string) $period->id ? 'selected' : '' }}>
                {{ $period->cutoff_start->format('M d') }} – {{ $period->cutoff_end->format('M d, Y') }}
                ({{ ucfirst($period->status) }})
            </option>
        @endforeach
    </select>
    @if(request()->hasAny(['search', 'department', 'payroll_period_id']))
        <a href="{{ route('payroll.index') }}" class="btn btn-soft btn-sm">Clear</a>
    @endif
</div>
</form> 
    </div>

  

    {{-- Desktop Table --}}
    <div class="overflow-x-hidden overflow-y-auto max-h-[47vh]  hidden md:block">
        @php
            $s    = request('sort');
            $d    = request('direction', 'asc');
            $base = array_merge(request()->except(['sort','direction','page']));
            function payrollSortTh(string $key, string $label, string $align, array $base, ?string $s, string $d): string {
                $active  = $s === $key;
                $nextDir = ($active && $d === 'asc') ? 'desc' : 'asc';
                $url     = route('payroll.index', array_merge($base, ['sort' => $key, 'direction' => $nextDir]));
                $upCol   = ($active && $d === 'asc')  ? '#dc2626' : '#d1d5db';
                $dnCol   = ($active && $d === 'desc') ? '#dc2626' : '#d1d5db';
                $color   = $active ? 'text-red-600 font-bold' : 'text-gray-500 font-semibold';
                return '<th class="text-' . $align . '"><a href="' . $url . '" class="inline-flex items-center gap-1 no-underline uppercase tracking-wider text-xs ' . $color . '">'
                     . $label
                     . '<span class="inline-flex flex-col leading-none gap-px">'
                     . '<i class="icon-[ph--caret-up-fill]" style="font-size:9px; color:' . $upCol . ';"></i>'
                     . '<i class="icon-[ph--caret-down-fill]" style="font-size:9px; color:' . $dnCol . ';"></i>'
                     . '</span></a></th>';
            }
        @endphp
        <table class="table table-hover table-fixed w-full text-sm table-borderless">
            <colgroup>
                <col class="w-48">  {{-- Employee --}}
                <col class="w-32">  {{-- Department --}}
                <col class="w-28">  {{-- Basic Pay --}}
                <col class="w-24">  {{-- Days Worked --}}
                <col class="w-20">  {{-- OT Hrs --}}
                <col class="w-20">  {{-- Holiday --}}
                <col class="w-28">  {{-- Total Deductions --}}
                <col class="w-28">  {{-- Net Pay --}}
                <col class="w-24">  {{-- Actions --}}
            </colgroup>
           <thead class="sticky top-0 z-5" style="background: white">
               <tr class="bg-success/20">
                    <th>Employee</th>
                    <th>Department</th>
                    {!! payrollSortTh('base_pay',         'Basic Pay',        'right',  $base, $s, $d) !!}
                    {!! payrollSortTh('days_worked',      'Days Worked',      'center', $base, $s, $d) !!}
                    {!! payrollSortTh('overtime_hours',   'OT Hrs',           'center', $base, $s, $d) !!}
                    {!! payrollSortTh('holiday_days',     'Holiday',          'center', $base, $s, $d) !!}
                    {!! payrollSortTh('total_deductions', 'Total Deductions', 'right',  $base, $s, $d) !!}
                    {!! payrollSortTh('net_pay',          'Net Pay',          'right',  $base, $s, $d) !!}
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php $payroll = $payrollData[$employee->id] ?? []; @endphp
                    <tr class="row-hover">
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $avatarClass }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('employees.show', $employee) }}"
                                       class="font-semibold text-gray-800 no-underline hover:text-emerald-600 truncate block">
                                        {{ $employee->full_name }}
                                    </a>
                                    <div class="text-xs text-gray-500 font-mono">{{ $employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-gray-500 truncate">{{ $employee->department }}</td>
                        <td class="text-right font-semibold text-gray-800">₱{{ number_format($payroll['base_pay'] ?? 0, 2) }}</td>
                        <td class="text-center font-semibold text-gray-800">{{ $payroll['attendance_data']['days_worked'] ?? 0 }}</td>
                        <td class="text-center font-semibold text-amber-500">{{ $payroll['attendance_data']['overtime_hours'] ?? 0 }}</td>
                        <td class="text-center font-semibold text-violet-500">{{ $payroll['attendance_data']['holiday_days'] ?? 0 }}</td>
                        <td class="text-right font-semibold text-red-600">-₱{{ number_format($payroll['total_deductions'] ?? 0, 2) }}</td>
                        <td class="text-right font-semibold text-emerald-600">₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</td>
                        <td class="text-center">
                            <div class="flex gap-2 justify-center">
                                @if(($payroll['gross_pay'] ?? 0) == 0 && empty($payroll['attendance_data']['days_worked']))
                                    <button class="btn btn-soft btn-sm btn-disabled" title="No payroll data">
                                        <i class="icon-[ph--eye-fill]"></i>
                                    </button>
                                @else
                                    <a href="{{ route('payroll.show', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
                                       class="btn btn-soft btn-info btn-sm" title="Full details">
                                        <i class="icon-[ph--eye-fill]"></i>
                                    </a>
                                    <a href="{{ route('payroll.payslip', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
                                       class="btn btn-soft btn-success btn-sm" title="Download payslip">
                                        <i class="icon-[ph--file-arrow-down-fill]"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-gray-400">
                            <i class="icon-[ph--money-fill] text-3xl mb-2 block"></i>
                            No payroll data found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden p-4 flex flex-col gap-3">
        @forelse($employees as $employee)
            @php $payroll = $payrollData[$employee->id] ?? []; @endphp
            <div class="card bg-base-100 border border-gray-200 p-4">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $avatarClass }} flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <a href="{{ route('employees.show', $employee) }}"
                               class="font-semibold text-gray-800 no-underline text-sm hover:text-emerald-600">
                                {{ $employee->full_name }}
                            </a>
                            <div class="text-xs text-gray-500 font-mono">{{ $employee->employee_id }}</div>
                        </div>
                    </div>
                    <span class="badge badge-soft badge-warning whitespace-nowrap">{{ $employee->employment_status }}</span>
                </div>

                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mt-2">
                    <span><i class="icon-[ph--buildings-fill] w-3.5"></i> {{ $employee->department }}</span>
                    <span><i class="icon-[ph--briefcase-fill] w-3.5"></i> {{ $employee->position }}</span>
                </div>

                <div class="mt-3 border-t border-gray-100">
                    <button type="button"
                            onclick="var d=this.nextElementSibling; var i=this.querySelector('i'); d.classList.toggle('hidden'); i.classList.toggle('fa-chevron-down'); i.classList.toggle('fa-chevron-up');"
                            class="w-full py-2 bg-transparent border-none cursor-pointer flex justify-between items-center text-xs font-semibold text-gray-500">
                        <span>View Payroll Breakdown</span>
                        <i class="icon-[ph--caret-down-fill] text-[11px]"></i>
                    </button>
                    <div class="hidden pb-1 flex flex-col gap-2 text-xs">
                        @foreach([
                            ['Days Worked',          $payroll['attendance_data']['days_worked'] ?? 0,               'text-gray-800 font-semibold'],
                            ['Overtime Hours',        $payroll['attendance_data']['overtime_hours'] ?? 0,            'text-amber-500'],
                            ['Holiday Days',          $payroll['attendance_data']['holiday_days'] ?? 0,              'text-violet-500'],
                            ['Basic Pay',             '₱'.number_format($payroll['base_pay'] ?? 0, 2),              'text-gray-800 font-semibold'],
                            ['Gross Pay',             '₱'.number_format($payroll['gross_pay'] ?? 0, 2),             'text-gray-800 font-semibold'],
                            ['Allowance & Benefits',  '+₱'.number_format($payroll['allowance_benefits'] ?? 0, 2),   'text-emerald-600'],
                            ['SSS',                   '-₱'.number_format($payroll['sss_contribution'] ?? 0, 2),     'text-red-600'],
                            ['PhilHealth',            '-₱'.number_format($payroll['philhealth_contribution'] ?? 0, 2), 'text-red-600'],
                            ['Pag-IBIG',              '-₱'.number_format($payroll['pagibig_contribution'] ?? 0, 2), 'text-red-600'],
                            ['Tax',                   '-₱'.number_format($payroll['withholding_tax'] ?? 0, 2),      'text-red-600'],
                            ['Total Deductions',      '-₱'.number_format($payroll['total_deductions'] ?? 0, 2),     'text-red-600 font-semibold'],
                        ] as [$label, $val, $cls])
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">{{ $label }}:</span>
                                <span class="{{ $cls }}">{{ $val }}</span>
                            </div>
                        @endforeach
                        <div class="flex justify-between items-center text-sm font-bold text-emerald-600 pt-1 border-t border-gray-100">
                            <span>Net Pay:</span>
                            <span>₱{{ number_format($payroll['net_pay'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 flex-wrap mt-3 pt-3 border-t border-gray-100">
                    @if(($payroll['gross_pay'] ?? 0) == 0 && empty($payroll['attendance_data']['days_worked']))
                        <button class="btn btn-soft btn-sm btn-disabled">
                            <i class="icon-[ph--eye-fill]"></i> View Details
                        </button>
                    @else
                        <a href="{{ route('payroll.show', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
                           class="btn btn-soft btn-info btn-sm">
                            <i class="icon-[ph--eye-fill]"></i> View Details
                        </a>
                        <a href="{{ route('payroll.payslip', [$employee->id, 'payroll_period_id' => optional($selectedPeriod)->id]) }}"
                           class="btn btn-soft btn-success btn-sm">
                            <i class="icon-[ph--file-arrow-down-fill]"></i> Payslip
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-10 text-center text-gray-400">
                <i class="icon-[ph--money-fill] text-3xl mb-2 block"></i>
                No payroll data found.
            </div>
        @endforelse
    </div>

   {{-- Pagination --}}
      <div class="px-6 py-4 border-t border-gray-200">
    {{ $employees->links('vendor.pagination.pagination') }}
</div>

</div>

@endsection

@section('scripts')
<script>
const DEPT_BREAKDOWN_DATA = @json($deptBreakdownData);
const DEPT_LABEL          = @json($deptLabel);

function fmt(n) {
    return '₱' + parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function openDeptModal() {
    const data  = DEPT_BREAKDOWN_DATA;
    const body  = document.getElementById('deptBreakdownBody');
    const foot  = document.getElementById('deptBreakdownFoot');
    const empty = document.getElementById('deptBreakdownEmpty');
    const table = document.getElementById('deptBreakdownTable');
    const bar   = document.getElementById('deptGrossPayBar');
    const modal = document.getElementById('deptBreakdownModal');

    document.getElementById('deptModalTitle').textContent =
        DEPT_LABEL !== 'All Departments' ? DEPT_LABEL + ' — Payroll Breakdown' : 'All Departments — Payroll Breakdown';

    const periodText = document.querySelector('select[name="payroll_period_id"] option:checked')?.innerText ?? 'Latest Cutoff';
    document.getElementById('deptModalMeta').textContent =
        data.length + ' employee' + (data.length !== 1 ? 's' : '') + ' · Cutoff: ' + periodText;

    if (!data.length) {
        table.classList.add('hidden'); bar.classList.add('hidden'); empty.classList.remove('hidden');
        modal.classList.remove('hidden'); modal.classList.add('flex');
        document.body.style.overflow = 'hidden'; return;
    }

    table.classList.remove('hidden'); empty.classList.add('hidden');

    let totBasic = 0, totSss = 0, totPhil = 0, totPagibig = 0, totTax = 0, totDed = 0, totNet = 0;

    body.innerHTML = data.map((emp) => {
        totBasic   += parseFloat(emp.basic_pay);
        totSss     += parseFloat(emp.sss_contribution);
        totPhil    += parseFloat(emp.philhealth_contribution);
        totPagibig += parseFloat(emp.pagibig_contribution);
        totTax     += parseFloat(emp.withholding_tax);
        totDed     += parseFloat(emp.total_deductions);
        totNet     += parseFloat(emp.net_pay);
        return `
            <tr>
                <td><div class="font-semibold text-gray-800 truncate">${emp.name}</div><div class="text-xs text-gray-400 font-mono">${emp.employee_id}</div></td>
                <td class="text-gray-500 truncate">${emp.department}</td>
                <td class="text-right font-semibold">${fmt(emp.basic_pay)}</td>
                <td class="text-right">${fmt(emp.allowance_benefits)}</td>
                <td class="text-right">${fmt(emp.overtime_pay)}</td>
                <td class="text-right font-semibold">${fmt(emp.gross_pay)}</td>
                <td class="text-right text-red-600">${fmt(emp.sss_contribution)}</td>
                <td class="text-right text-red-600">${fmt(emp.philhealth_contribution)}</td>
                <td class="text-right text-red-600">${fmt(emp.pagibig_contribution)}</td>
                <td class="text-right text-red-600">${fmt(emp.withholding_tax)}</td>
                <td class="text-right font-semibold text-red-600">${fmt(emp.total_deductions)}</td>
                <td class="text-right font-bold text-emerald-700 text-sm">${fmt(emp.net_pay)}</td>
            </tr>`;
    }).join('');

    foot.innerHTML = '';
    document.getElementById('deptTotalGrossPay').textContent = fmt(totNet);
    bar.classList.remove('hidden'); bar.classList.add('flex');
    modal.classList.remove('hidden'); modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDeptModal() {
    const modal = document.getElementById('deptBreakdownModal');
    modal.classList.add('hidden'); modal.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
    document.body.appendChild(document.getElementById('deptBreakdownModal'));
    document.getElementById('deptBreakdownModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeptModal();
    });
});

document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDeptModal(); });

function printPayrollTable() {
    const data = DEPT_BREAKDOWN_DATA;
    if (!data.length) { window.notyf.error('No payroll data to print.'); return; }

    const searchVal  = document.querySelector('input[name="search"]')?.value ?? '';
    const deptVal    = document.querySelector('select[name="department"]')?.value ?? '';
    const periodText = document.querySelector('select[name="payroll_period_id"] option:checked')?.innerText ?? '';
    const filterNote = [searchVal ? `Search: "${searchVal}"` : '', deptVal ? `Department: ${deptVal}` : '', periodText ? `Cutoff: ${periodText}` : ''].filter(Boolean).join(' | ') || 'All Employees';

    let totBasic = 0, totAllowance = 0, totOt = 0, totGross = 0, totSss = 0, totPhil = 0, totPagibig = 0, totTax = 0, totDed = 0, totNet = 0;
    const headers = ['Employee', 'Dept', 'Basic Pay', 'Allowance', 'OT Pay', 'Earnings', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Total Deductions', 'Net Pay'];
    let rows = '';
    data.forEach(d => {
        totBasic += parseFloat(d.basic_pay); totAllowance += parseFloat(d.allowance_benefits);
        totOt += parseFloat(d.overtime_pay); totGross += parseFloat(d.gross_pay);
        totSss += parseFloat(d.sss_contribution); totPhil += parseFloat(d.philhealth_contribution);
        totPagibig += parseFloat(d.pagibig_contribution); totTax += parseFloat(d.withholding_tax);
        totDed += parseFloat(d.total_deductions); totNet += parseFloat(d.net_pay);
        rows += `<tr><td><strong>${d.name}</strong><br><small>${d.employee_id}</small></td><td>${d.department}</td><td class="num">${fmt(d.basic_pay)}</td><td class="num">${fmt(d.allowance_benefits)}</td><td class="num">${fmt(d.overtime_pay)}</td><td class="num">${fmt(d.gross_pay)}</td><td class="num red">${fmt(d.sss_contribution)}</td><td class="num red">${fmt(d.philhealth_contribution)}</td><td class="num red">${fmt(d.pagibig_contribution)}</td><td class="num red">${fmt(d.withholding_tax)}</td><td class="num red bold">${fmt(d.total_deductions)}</td><td class="num green bold">${fmt(d.net_pay)}</td></tr>`;
    });

    const win = window.open('', '_blank');
    win.document.write(`<!DOCTYPE html><html><head><title>Payroll Summary Report</title>
    <style>* { margin:0; padding:0; box-sizing:border-box; } body { font-family:Arial,sans-serif; font-size:11px; color:#111; padding:20px; }
    h1 { font-size:16px; color:#1a1a2e; margin-bottom:4px; } .meta { font-size:11px; color:#6b7280; margin-bottom:16px; }
    table { width:100%; border-collapse:collapse; } thead th { background:#1e40af; color:white; padding:7px 8px; font-size:10px; text-transform:uppercase; text-align:left; }
    thead th.num { text-align:right; } td { padding:6px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top; } td.num { text-align:right; }
    tr:nth-child(even) td { background:#f9fafb; } small { color:#6b7280; font-size:10px; font-family:monospace; }
    .red { color:#dc2626; } .green { color:#065f46; } .bold { font-weight:700; }
    tfoot tr td { background:#dbeafe; font-weight:700; border-top:2px solid #93c5fd; padding:8px; } tfoot tr td.num { text-align:right; }
    .gross-bar { margin-top:12px; background:#d1fae5; border-radius:8px; padding:12px 16px; display:flex; justify-content:space-between; }
    .gross-bar span, .gross-bar strong { font-size:11px; color:#065f46; } @media print { body { padding:0; } }</style>
    </head><body><h1>Payroll Summary Report</h1><div class="meta">Filter: ${filterNote} | Printed: ${new Date().toLocaleString()}</div>
    <table><thead><tr>${headers.map((h,i) => i>=2?`<th class="num">${h}</th>`:`<th>${h}</th>`).join('')}</tr></thead>
    <tbody>${rows}</tbody>
    <tfoot><tr><td colspan="2">Totals (${data.length} employees)</td><td class="num">${fmt(totBasic)}</td><td class="num">${fmt(totAllowance)}</td><td class="num">${fmt(totOt)}</td><td class="num">${fmt(totGross)}</td><td class="num red">${fmt(totSss)}</td><td class="num red">${fmt(totPhil)}</td><td class="num red">${fmt(totPagibig)}</td><td class="num red">${fmt(totTax)}</td><td class="num red bold">${fmt(totDed)}</td><td class="num green bold">${fmt(totNet)}</td></tr></tfoot>
    </table><div class="gross-bar"><span><strong>Total Gross Pay</strong></span><strong>${fmt(totNet)}</strong></div></body></html>`);
    win.document.close(); win.focus(); win.print();
}

function exportPayrollCSV() {
    const data = DEPT_BREAKDOWN_DATA;
    if (!data.length) { window.notyf.error('No payroll data to export.'); return; }

    const headers = ['Employee','Employee ID','Department','Basic Pay','Allowance','OT Pay','Earnings','SSS','PhilHealth','Pag-IBIG','Tax','Total Deductions','Net Pay'];
    let csvRows = [headers.join(',')], totNet = 0;
    data.forEach(d => {
        totNet += parseFloat(d.net_pay);
        csvRows.push([`"${d.name}"`,`"${d.employee_id}"`,`"${d.department}"`,d.basic_pay,d.allowance_benefits,d.overtime_pay,d.gross_pay,d.sss_contribution,d.philhealth_contribution,d.pagibig_contribution,d.withholding_tax,d.total_deductions,d.net_pay].join(','));
    });
    csvRows.push(['','','"TOTALS"','','','','','',totNet.toFixed(2)].join(','));

    const periodText = document.querySelector('select[name="payroll_period_id"] option:checked')?.innerText ?? '';
    const deptVal    = document.querySelector('select[name="department"]')?.value ?? '';
    const periodSlug = periodText && periodText !== 'Latest Cutoff' ? `_${periodText.replace(/[^a-zA-Z0-9]/g,'_').replace(/_+/g,'_')}` : '';
    const filename   = deptVal ? `payroll_${deptVal.replace(/\s+/g,'_')}${periodSlug}.csv` : `payroll_all${periodSlug}.csv`;

    const blob = new Blob([csvRows.join('\n')], { type:'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
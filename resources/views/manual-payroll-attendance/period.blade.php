@extends('layouts.app')

@section('title', 'Encode Attendance - ' . ($payrollPeriod->cutoff_start ? $payrollPeriod->cutoff_start->format('M d') : 'N/A') . ' to ' . ($payrollPeriod->cutoff_end ? $payrollPeriod->cutoff_end->format('M d, Y') : 'N/A'))

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
        <a href="{{ route('manual-payroll-attendance.index') }}"
           class="text-base-content/60 no-underline text-sm inline-flex items-center gap-1.5 mb-2">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Periods
        </a>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="icon-[ph--calendar-fill]"></i> Payroll Period
        </div>
        <h2 style="margin:8px 0 4px 0;">
            {{ $payrollPeriod->cutoff_start ? $payrollPeriod->cutoff_start->format('F d') : 'N/A' }} - {{ $payrollPeriod->cutoff_end ? $payrollPeriod->cutoff_end->format('F d, Y') : 'N/A' }}
        </h2>
        <p class="text-base-content/60 m-0">
            Payroll Date: {{ $payrollPeriod->payroll_date ? $payrollPeriod->payroll_date->format('F d, Y') : 'N/A' }} | 
            Status: <span style="font-weight:600; {{ $payrollPeriod->status === 'finalized' ? 'color:#166534;' : 'color:#92400e;' }}">{{ ucfirst($payrollPeriod->status) }}</span>
        </p>
    </div>
    @if($isAdmin || $isHR)
    <div style="display:flex; gap:10px;">
        @if($payrollPeriod->isDraft() && $payrollPeriod->payrollInputs && $payrollPeriod->payrollInputs->count() > 0)
        <button onclick="finalizePayroll()"
                class="px-5 py-2.5 bg-success text-white border-none rounded-field cursor-pointer text-sm inline-flex items-center gap-2">
            <i class="icon-[tabler--circle-check]"></i> Finalize Payroll
        </button>
        @endif
        <button onclick="loadPeriodSummary()"
                class="px-5 py-2.5 text-white border-none rounded-field cursor-pointer text-sm inline-flex items-center gap-2" style="background:{{ $color }};">
            <i class="icon-[ph--arrows-clockwise-fill]"></i> Refresh Summary
        </button>
    </div>
    @endif
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
    <div class="bg-base-100 border border-base-300 rounded-xl p-5">
        <div class="text-base-content/60 text-xs mb-1">Total Employees</div>
        <div class="text-2xl font-bold text-base-content" id="totalEmployees">{{ $payrollPeriod->payrollInputs ? $payrollPeriod->payrollInputs->count() : 0 }}</div>
    </div>
    <div class="bg-base-100 border border-base-300 rounded-xl p-5">
        <div class="text-base-content/60 text-xs mb-1">Total Gross Pay</div>
        <div class="text-2xl font-bold text-success" id="totalGrossPay">₱{{ number_format($payrollPeriod->total_gross_pay ?? 0, 2) }}</div>
    </div>
    <div class="bg-base-100 border border-base-300 rounded-xl p-5">
        <div class="text-base-content/60 text-xs mb-1">Total Net Pay</div>
        <div class="text-2xl font-bold text-info" id="totalNetPay">₱{{ number_format($payrollPeriod->total_net_pay ?? 0, 2) }}</div>
    </div>
    <div class="bg-base-100 border border-base-300 rounded-xl p-5">
        <div class="text-base-content/60 text-xs mb-1">Total Deductions</div>
        <div class="text-2xl font-bold text-error" id="totalDeductions">₱{{ number_format($payrollPeriod->total_deductions ?? 0, 2) }}</div>
    </div>
</div>

 {{-- Encoded Employees Table --}}
<div class="card" style="padding:0; overflow:hidden; margin-bottom:24px;">
    <div class="p-5 border-b border-base-300">
        <h3 style="margin:0;">Encoded Employees</h3>
        <p class="text-base-content/60 mt-2 mb-0 text-sm">Employees with attendance data for this period</p>
    </div>

    @if($payrollPeriod->payrollInputs && $payrollPeriod->payrollInputs->count() > 0)
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead class="bg-base-200">
                <tr>
                    <th class="px-6 py-3 text-left text-base-content/60 text-xs uppercase">Employee</th>
                    <th class="px-3 py-3 text-right text-base-content/60 text-xs uppercase">Days Worked</th>
                    <th class="px-3 py-3 text-right text-base-content/60 text-xs uppercase">OT Hours</th>
                    <th class="px-3 py-3 text-right text-base-content/60 text-xs uppercase">Late Hours</th>
                    <th class="px-3 py-3 text-right text-base-content/60 text-xs uppercase">Allowances</th>
                    <th class="px-3 py-3 text-right text-base-content/60 text-xs uppercase">Deductions</th>
                    <th class="px-3 py-3 text-right text-base-content/60 text-xs uppercase">Gross Pay</th>
                    <th class="px-3 py-3 text-right text-base-content/60 text-xs uppercase">Net Pay</th>
                    <th class="px-3 py-3 text-center text-base-content/60 text-xs uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrollPeriod->payrollInputs as $input)
                    @if($input && $input->employee)
                <tr class="border-b border-base-300">
                    <td class="px-6 py-3">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $colorDark }}); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700;">
                                {{ strtoupper(substr($input->employee->first_name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-base-content">{{ $input->employee->first_name ?? 'Unknown' }} {{ $input->employee->last_name ?? '' }}</div>
                                <div class="text-xs text-base-content/60 font-mono">{{ $input->employee->employee_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-right">{{ number_format($input->days_worked ?? 0, 1) }}</td>
                    <td class="px-3 py-3 text-right">{{ number_format($input->overtime_hours ?? 0, 1) }}</td>
                    <td class="px-3 py-3 text-right">{{ number_format($input->late_hours ?? 0, 1) }}</td>
                    <td class="px-3 py-3 text-right text-success">₱{{ number_format($input->allowances ?? 0, 2) }}</td>
                    <td class="px-3 py-3 text-right text-error">₱{{ number_format($input->deductions ?? 0, 2) }}</td>
                    <td class="px-3 py-3 text-right font-semibold text-base-content">₱{{ number_format($input->gross_pay ?? 0, 2) }}</td>
                    <td class="px-3 py-3 text-right font-bold text-success">₱{{ number_format($input->net_pay ?? 0, 2) }}</td>
                    <td class="px-3 py-3 text-center">
                        @if($payrollPeriod->isDraft())
                        <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollPeriod, $input->employee]) }}"
                           class="px-3 py-1.5 bg-base-200 text-primary rounded-field text-xs no-underline inline-flex items-center gap-1.5">
                            <i class="icon-[ph--pencil-fill]"></i> Edit
                        </a>
                        @else
                        <span class="text-base-content/40 text-xs">Finalized</span>
                        @endif
                    </td>
                </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-10 text-center text-base-content/40">
        <i class="icon-[ph--clipboard-text-fill]" style="font-size:32px; margin-bottom:10px; display:block;"></i>
        No employees encoded yet for this period.
    </div>
    @endif
</div>

 {{-- Unencoded Employees --}}
@if($unencodedEmployees->count() > 0 && $payrollPeriod->isDraft())
<div class="card" style="padding:0; overflow:hidden;">
    <div class="p-5 border-b border-base-300">
        <h3 style="margin:0;">Pending Encoding</h3>
        <p class="text-base-content/60 mt-2 mb-0 text-sm">Employees without attendance data for this period</p>
    </div>

    {{-- Filters --}}
    <div class="p-5 bg-base-200 border-b border-base-300">
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <div style="flex:1; min-width:250px;">
                <input type="text" id="searchEmployee" name="searchEmployee" placeholder="Search by name or employee ID..."
                       class="w-full p-2.5 border border-base-300 rounded-field text-sm"
                       oninput="filterEmployees()">
            </div>
            <div style="min-width:180px;">
                <select id="filterDepartment" name="filterDepartment"
                        class="w-full p-2.5 border border-base-300 rounded-field text-sm"
                        onchange="filterEmployees()">
                    <option value="">All Departments</option>
                    <option value="Human Resources">Human Resources</option>
                    <option value="Sales">Sales</option>
                    <option value="Finance">Finance</option>
                    <option value="Marketing">Marketing</option>
                    <option value="IT">IT</option>
                    <option value="Operations">Operations</option>
                    @foreach($unencodedEmployees->pluck('department')->unique()->sort() as $dept)
                        @if($dept && !in_array($dept, ['Human Resources', 'Sales', 'Finance', 'Marketing', 'IT', 'Operations']))
                        <option value="{{ $dept }}">{{ $dept }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div style="min-width:160px;">
                <select id="filterStatus" name="filterStatus"
                        class="w-full p-2.5 border border-base-300 rounded-field text-sm"
                        onchange="filterEmployees()">
                    <option value="">All Status</option>
                    <option value="Regular">Regular</option>
                    <option value="Probationary">Probationary</option>
                    <option value="Contractual">Contractual</option>
                    <option value="Part-time">Part-time</option>
                </select>
            </div>
            <button onclick="clearFilters()"
                    class="px-4 py-2.5 bg-base-content text-base-100 border-none rounded-field cursor-pointer text-sm">
                <i class="icon-[ph--x]"></i> Clear
            </button>
        </div>
        <div class="mt-2 text-xs text-base-content/60">
            Showing <span id="filteredCount">{{ $unencodedEmployees->count() }}</span> of {{ $unencodedEmployees->count() }} employees
        </div>
    </div>

    <div style="padding:25px;">
        <div id="employeeGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px;">
            @foreach($unencodedEmployees as $employee)
            @if($employee)
            <div class="employee-card" data-name="{{ strtolower($employee->first_name ?? '') }} {{ strtolower($employee->last_name ?? '') }}"
                 data-employee-id="{{ strtolower($employee->employee_id ?? '') }}"
                 data-department="{{ $employee->department ?? '' }}"
                 data-status="{{ $employee->employment_status ?? '' }}"
                 class="border border-base-300 rounded-field p-4 flex justify-between items-center">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center text-base-content/60 text-xs font-bold">
                        {{ strtoupper(substr($employee->first_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-base-content text-sm">{{ $employee->first_name ?? 'Unknown' }} {{ $employee->last_name ?? '' }}</div>
                        <div class="text-xs text-base-content/60 font-mono">{{ $employee->employee_id ?? 'N/A' }}</div>
                        <div class="text-[11px] text-base-content/40 mt-0.5">{{ $employee->department ?? 'N/A' }} • {{ $employee->employment_status ?? 'N/A' }}</div>
                    </div>
                </div>
                <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollPeriod, $employee]) }}"
                   class="px-3 py-1.5 text-white rounded-field text-xs no-underline" style="background:{{ $color }};">
                    <i class="icon-[ph--keyboard-fill]"></i> Encode
                </a>
            </div>
            @endif
            @endforeach
        </div>
        <div id="noResults" class="hidden p-10 text-center text-base-content/40">
            <i class="icon-[tabler--search]" style="font-size:32px; margin-bottom:10px; display:block;"></i>
            No employees match your filters.
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>


const getVisibleInput = (inputs) => {
    for (let input of inputs) {
        if (input.offsetParent !== null) return input;
    }
    return inputs[0];
};

function filterEmployees() {
    const searchInput     = getVisibleInput(document.getElementsByName('searchEmployee'));
    const departmentInput = getVisibleInput(document.getElementsByName('filterDepartment'));
    const statusInput     = getVisibleInput(document.getElementsByName('filterStatus'));

    const searchTerm       = searchInput     ? searchInput.value.toLowerCase() : '';
    const departmentFilter = departmentInput ? departmentInput.value : '';
    const statusFilter     = statusInput     ? statusInput.value : '';

    let visibleCount = 0;

    document.querySelectorAll('.employee-card').forEach(card => {
        const matchesSearch     = (card.dataset.name || '').includes(searchTerm) || (card.dataset.employeeId || '').includes(searchTerm);
        const matchesDepartment = !departmentFilter || card.dataset.department === departmentFilter;
        const matchesStatus     = !statusFilter     || card.dataset.status     === statusFilter;

        const visible = matchesSearch && matchesDepartment && matchesStatus;
        card.style.display = visible ? 'flex' : 'none';
        if (visible) visibleCount++;
    });

    document.getElementById('filteredCount').textContent = visibleCount;
    document.getElementById('noResults').style.display   = visibleCount === 0 ? 'block' : 'none';
}

function clearFilters() {
    const searchInput     = getVisibleInput(document.getElementsByName('searchEmployee'));
    const departmentInput = getVisibleInput(document.getElementsByName('filterDepartment'));
    const statusInput     = getVisibleInput(document.getElementsByName('filterStatus'));

    if (searchInput)     searchInput.value     = '';
    if (departmentInput) departmentInput.value = '';
    if (statusInput)     statusInput.value     = '';

    filterEmployees();
}

function loadPeriodSummary() {
    fetch(`{{ route('manual-payroll-attendance.summary', $payrollPeriod) }}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('totalEmployees').textContent  = data.encoded_employees;
            document.getElementById('totalGrossPay').textContent   = '₱' + parseFloat(data.total_gross_pay).toLocaleString('en-US',  { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('totalNetPay').textContent     = '₱' + parseFloat(data.total_net_pay).toLocaleString('en-US',    { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('totalDeductions').textContent = '₱' + parseFloat(data.total_deductions).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            location.reload();
        })
.catch(() => window.notyf.error('Failed to load summary.'));
}

function finalizePayroll() {
    window.confirmAction({
        url:         '{{ route('payroll-periods.finalize', $payrollPeriod) }}',
        csrfToken:   '{{ csrf_token() }}',
        title:       'Finalize Payroll Period?',
        text:        'This action cannot be undone.',
        confirmText: 'Yes, finalize it',
    });
}
</script>
@endsection
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
<div class="flex items-center justify-between mb-4">
    <div>
        <a href="{{ route('manual-payroll-attendance.index') }}"
           class="inline-flex items-center text-sm text-base-content/60 mb-4 gap-3 no-underline">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Periods
        </a>
        <div class="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg p-4 mb-4">
            <i class="icon-[ph--calendar-fill]"></i> Payroll Period
        </div>
        <h2 class="text-base-content">
            {{ $payrollPeriod->cutoff_start ? $payrollPeriod->cutoff_start->format('F d') : 'N/A' }} - {{ $payrollPeriod->cutoff_end ? $payrollPeriod->cutoff_end->format('F d, Y') : 'N/A' }}
        </h2>
        <p class="text-base-content/60">
            Payroll Date: {{ $payrollPeriod->payroll_date ? $payrollPeriod->payroll_date->format('F d, Y') : 'N/A' }} | 
            Status: <span class="font-semibold">{{ ucfirst($payrollPeriod->status) }}</span>
        </p>
    </div>
    @if($isAdmin || $isHR)
    <div class="flex gap-3">
        @if($payrollPeriod->isDraft() && $payrollPeriod->payrollInputs && $payrollPeriod->payrollInputs->count() > 0)
        <button onclick="finalizePayroll()"
                class="inline-flex items-center text-sm rounded-lg p-4 gap-3 cursor-pointer">
            <i class="icon-[tabler--circle-check]"></i> Finalize Payroll
        </button>
        @endif
        <button onclick="loadPeriodSummary()"
                class="inline-flex items-center text-sm bg-primary rounded-lg p-4 gap-3 cursor-pointer">
            <i class="icon-[ph--arrows-clockwise-fill]"></i> Refresh Summary
        </button>
    </div>
    @endif
</div>

{{-- Summary Cards --}}
<div class="grid mb-4 gap-3">
    <div class="bg-base-100 border border-base-300 rounded-lg p-4">
        <div class="text-xs text-base-content/60 mb-4">Total Employees</div>
        <div class="font-bold text-2xl text-base-content" id="totalEmployees">{{ $payrollPeriod->payrollInputs ? $payrollPeriod->payrollInputs->count() : 0 }}</div>
    </div>
    <div class="bg-base-100 border border-base-300 rounded-lg p-4">
        <div class="text-xs text-base-content/60 mb-4">Total Gross Pay</div>
        <div class="font-bold text-2xl text-success" id="totalGrossPay">₱{{ number_format($payrollPeriod->total_gross_pay ?? 0, 2) }}</div>
    </div>
    <div class="bg-base-100 border border-base-300 rounded-lg p-4">
        <div class="text-xs text-base-content/60 mb-4">Total Net Pay</div>
        <div class="font-bold text-2xl text-info" id="totalNetPay">₱{{ number_format($payrollPeriod->total_net_pay ?? 0, 2) }}</div>
    </div>
    <div class="bg-base-100 border border-base-300 rounded-lg p-4">
        <div class="text-xs text-base-content/60 mb-4">Total Deductions</div>
        <div class="font-bold text-2xl text-error" id="totalDeductions">₱{{ number_format($payrollPeriod->total_deductions ?? 0, 2) }}</div>
    </div>
</div>

 {{-- Encoded Employees Table --}}
<div class="card overflow-hidden p-4 mb-4">
    <div class="border-base-300 p-4">
        <h3 class="text-base-content">Encoded Employees</h3>
        <p class="text-sm text-base-content/60">Employees with attendance data for this period</p>
    </div>

    @if($payrollPeriod->payrollInputs && $payrollPeriod->payrollInputs->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-base-200">
                <tr>
                    <th class="text-xs text-base-content/60 p-4">Employee</th>
                    <th class="text-right text-xs text-base-content/60 p-4">Days Worked</th>
                    <th class="text-right text-xs text-base-content/60 p-4">OT Hours</th>
                    <th class="text-right text-xs text-base-content/60 p-4">Late Hours</th>
                    <th class="text-right text-xs text-base-content/60 p-4">Allowances</th>
                    <th class="text-right text-xs text-base-content/60 p-4">Deductions</th>
                    <th class="text-right text-xs text-base-content/60 p-4">Gross Pay</th>
                    <th class="text-right text-xs text-base-content/60 p-4">Net Pay</th>
                    <th class="text-center text-xs text-base-content/60 p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrollPeriod->payrollInputs as $input)
                    @if($input && $input->employee)
                <tr class="border-base-300">
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center font-bold text-xs rounded-lg">
                                {{ strtoupper(substr($input->employee->first_name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-base-content">{{ $input->employee->first_name ?? 'Unknown' }} {{ $input->employee->last_name ?? '' }}</div>
                                <div class="text-xs text-base-content/60 font-mono">{{ $input->employee->employee_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-right p-4">{{ number_format($input->days_worked ?? 0, 1) }}</td>
                    <td class="text-right p-4">{{ number_format($input->overtime_hours ?? 0, 1) }}</td>
                    <td class="text-right p-4">{{ number_format($input->late_hours ?? 0, 1) }}</td>
                    <td class="text-right text-success p-4">₱{{ number_format($input->allowances ?? 0, 2) }}</td>
                    <td class="text-right text-error p-4">₱{{ number_format($input->deductions ?? 0, 2) }}</td>
                    <td class="text-right font-semibold text-base-content p-4">₱{{ number_format($input->gross_pay ?? 0, 2) }}</td>
                    <td class="text-right font-bold text-success p-4">₱{{ number_format($input->net_pay ?? 0, 2) }}</td>
                    <td class="text-center p-4">
                        @if($payrollPeriod->isDraft())
                        <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollPeriod, $input->employee]) }}"
                           class="inline-flex items-center text-xs text-info bg-info/10 rounded-lg p-4 gap-3 no-underline">
                            <i class="icon-[ph--pencil-fill]"></i> Edit
                        </a>
                        @else
                        <span class="text-xs text-base-content/60">Finalized</span>
                        @endif
                    </td>
                </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center text-base-content/60 p-4">
        <i class="icon-[ph--clipboard-text-fill] text-2xl mb-4"></i>
        No employees encoded yet for this period.
    </div>
    @endif
</div>

 {{-- Unencoded Employees --}}
@if($unencodedEmployees->count() > 0 && $payrollPeriod->isDraft())
<div class="card overflow-hidden p-4">
    <div class="border-base-300 p-4">
        <h3 class="text-base-content">Pending Encoding</h3>
        <p class="text-sm text-base-content/60">Employees without attendance data for this period</p>
    </div>

    {{-- Filters --}}
    <div class="bg-base-200 border-base-300 p-4">
        <div class="flex items-center flex-wrap gap-3">
            <div class="text-base-content">
                <input type="text" id="searchEmployee" name="searchEmployee" placeholder="Search by name or employee ID..."
                       class="w-full text-sm border border-base-300 rounded-lg p-4"
                       oninput="filterEmployees()">
            </div>
            <div class="text-base-content">
                <select id="filterDepartment" name="filterDepartment"
                        class="w-full text-sm border border-base-300 rounded-lg p-4"
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
            <div class="text-base-content">
                <select id="filterStatus" name="filterStatus"
                        class="w-full text-sm border border-base-300 rounded-lg p-4"
                        onchange="filterEmployees()">
                    <option value="">All Status</option>
                    <option value="Regular">Regular</option>
                    <option value="Probationary">Probationary</option>
                    <option value="Contractual">Contractual</option>
                    <option value="Part-time">Part-time</option>
                </select>
            </div>
            <button onclick="clearFilters()"
                    class="text-sm rounded-lg p-4 cursor-pointer">
                <i class="icon-[ph--x]"></i> Clear
            </button>
        </div>
        <div class="text-xs text-base-content/60 mt-2">
            Showing <span id="filteredCount">{{ $unencodedEmployees->count() }}</span> of {{ $unencodedEmployees->count() }} employees
        </div>
    </div>

    <div class="p-4">
        <div id="employeeGrid" class="grid gap-3">
            @foreach($unencodedEmployees as $employee)
            @if($employee)
            <div class="employee-card" data-name="{{ strtolower($employee->first_name ?? '') }} {{ strtolower($employee->last_name ?? '') }}"
                 data-employee-id="{{ strtolower($employee->employee_id ?? '') }}"
                 data-department="{{ $employee->department ?? '' }}"
                 data-status="{{ $employee->employment_status ?? '' }}"
                 class="flex items-center justify-between border border-base-300 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center font-bold text-xs text-base-content/60 bg-base-200 rounded-lg">
                        {{ strtoupper(substr($employee->first_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-sm text-base-content">{{ $employee->first_name ?? 'Unknown' }} {{ $employee->last_name ?? '' }}</div>
                        <div class="text-xs text-base-content/60 font-mono">{{ $employee->employee_id ?? 'N/A' }}</div>
                        <div class="text-base-content/60 mt-2">{{ $employee->department ?? 'N/A' }} • {{ $employee->employment_status ?? 'N/A' }}</div>
                    </div>
                </div>
                <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollPeriod, $employee]) }}"
                   class="text-xs bg-primary rounded-lg p-4 no-underline">
                    <i class="icon-[ph--keyboard-fill]"></i> Encode
                </a>
            </div>
            @endif
            @endforeach
        </div>
        <div id="noResults" class="hidden text-center text-base-content/60 p-4">
            <i class="icon-[tabler--search] text-2xl mb-4"></i>
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
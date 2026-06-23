@extends('layouts.app')

@section('title', 'Encode Attendance - ' . ($payrollPeriod->cutoff_start ? $payrollPeriod->cutoff_start->format('M d') : 'N/A') . ' to ' . ($payrollPeriod->cutoff_end ? $payrollPeriod->cutoff_end->format('M d, Y') : 'N/A'))
@section('breadcrumb')
    <a href="{{ route('employees.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Manage Employees</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('manual-payroll-attendance.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Attendance</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Attendance Encoding</span>
@endsection
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
           style="color:#6b7280; text-decoration:none; font-size:14px; display:inline-flex; align-items:center; gap:6px; margin-bottom:8px;">
            <i class="fas fa-arrow-left"></i> Back to Periods
        </a>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-calendar-alt"></i> Payroll Period
        </div>
        <h2 style="margin:8px 0 4px 0;">
            {{ $payrollPeriod->cutoff_start ? $payrollPeriod->cutoff_start->format('F d') : 'N/A' }} - {{ $payrollPeriod->cutoff_end ? $payrollPeriod->cutoff_end->format('F d, Y') : 'N/A' }}
        </h2>
        <p style="color:#6b7280; margin:0;">
            Payroll Date: {{ $payrollPeriod->payroll_date ? $payrollPeriod->payroll_date->format('F d, Y') : 'N/A' }} | 
            Status: <span style="font-weight:600; {{ $payrollPeriod->status === 'finalized' ? 'color:#166534;' : 'color:#92400e;' }}">{{ ucfirst($payrollPeriod->status) }}</span>
        </p>
    </div>
    @if($isAdmin || $isHR)
    <div style="display:flex; gap:10px;">
        @if($payrollPeriod->isDraft() && $payrollPeriod->payrollInputs && $payrollPeriod->payrollInputs->count() > 0)
        <button onclick="finalizePayroll()"
                style="padding:10px 20px; background:#10b981; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; display:inline-flex; align-items:center; gap:8px;">
            <i class="fas fa-check-circle"></i> Finalize Payroll
        </button>
        @endif
        <button onclick="loadPeriodSummary()"
                style="padding:10px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; display:inline-flex; align-items:center; gap:8px;">
            <i class="fas fa-sync"></i> Refresh Summary
        </button>
    </div>
    @endif
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
    <div style="background:white; border:1px solid #e5e7eb; border-radius:8px; padding:20px;">
        <div style="color:#6b7280; font-size:13px; margin-bottom:4px;">Total Employees</div>
        <div style="font-size:28px; font-weight:700; color:#1f2937;" id="totalEmployees">{{ $payrollPeriod->payrollInputs ? $payrollPeriod->payrollInputs->count() : 0 }}</div>
    </div>
    <div style="background:white; border:1px solid #e5e7eb; border-radius:8px; padding:20px;">
        <div style="color:#6b7280; font-size:13px; margin-bottom:4px;">Total Gross Pay</div>
        <div style="font-size:28px; font-weight:700; color:#10b981;" id="totalGrossPay">₱{{ number_format($payrollPeriod->total_gross_pay ?? 0, 2) }}</div>
    </div>
    <div style="background:white; border:1px solid #e5e7eb; border-radius:8px; padding:20px;">
        <div style="color:#6b7280; font-size:13px; margin-bottom:4px;">Total Net Pay</div>
        <div style="font-size:28px; font-weight:700; color:#2563eb;" id="totalNetPay">₱{{ number_format($payrollPeriod->total_net_pay ?? 0, 2) }}</div>
    </div>
    <div style="background:white; border:1px solid #e5e7eb; border-radius:8px; padding:20px;">
        <div style="color:#6b7280; font-size:13px; margin-bottom:4px;">Total Deductions</div>
        <div style="font-size:28px; font-weight:700; color:#dc2626;" id="totalDeductions">₱{{ number_format($payrollPeriod->total_deductions ?? 0, 2) }}</div>
    </div>
</div>

 {{-- Encoded Employees Table --}}
<div class="card" style="padding:0; overflow:hidden; margin-bottom:24px;">
    <div style="padding:20px 25px; border-bottom:1px solid #e5e7eb;">
        <h3 style="margin:0;">Encoded Employees</h3>
        <p style="color:#6b7280; margin:8px 0 0 0; font-size:14px;">Employees with attendance data for this period</p>
    </div>

    @if($payrollPeriod->payrollInputs && $payrollPeriod->payrollInputs->count() > 0)
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead style="background:#f9fafb;">
                <tr>
                    <th style="padding:12px 25px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Employee</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Days Worked</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">OT Hours</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Late Hours</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Allowances</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Deductions</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Gross Pay</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Net Pay</th>
                    <th style="padding:12px; text-align:center; color:#6b7280; font-size:12px; text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrollPeriod->payrollInputs as $input)
                    @if($input && $input->employee)
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:12px 25px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,{{ $color }},{{ $colorDark }}); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700;">
                                {{ strtoupper(substr($input->employee->first_name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1f2937;">{{ $input->employee->first_name ?? 'Unknown' }} {{ $input->employee->last_name ?? '' }}</div>
                                <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $input->employee->employee_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px; text-align:right;">{{ number_format($input->days_worked ?? 0, 1) }}</td>
                    <td style="padding:12px; text-align:right;">{{ number_format($input->overtime_hours ?? 0, 1) }}</td>
                    <td style="padding:12px; text-align:right;">{{ number_format($input->late_hours ?? 0, 1) }}</td>
                    <td style="padding:12px; text-align:right; color:#10b981;">₱{{ number_format($input->allowances ?? 0, 2) }}</td>
                    <td style="padding:12px; text-align:right; color:#dc2626;">₱{{ number_format($input->deductions ?? 0, 2) }}</td>
                    <td style="padding:12px; text-align:right; font-weight:600; color:#1f2937;">₱{{ number_format($input->gross_pay ?? 0, 2) }}</td>
                    <td style="padding:12px; text-align:right; font-weight:700; color:#10b981;">₱{{ number_format($input->net_pay ?? 0, 2) }}</td>
                    <td style="padding:12px; text-align:center;">
                        @if($payrollPeriod->isDraft())
                        <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollPeriod, $input->employee]) }}"
                           style="padding:6px 12px; background:#dbeafe; color:#1e40af; border-radius:5px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @else
                        <span style="color:#9ca3af; font-size:12px;">Finalized</span>
                        @endif
                    </td>
                </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="padding:40px 25px; text-align:center; color:#9ca3af;">
        <i class="fas fa-clipboard-list" style="font-size:32px; margin-bottom:10px; display:block;"></i>
        No employees encoded yet for this period.
    </div>
    @endif
</div>

 {{-- Unencoded Employees --}}
@if($unencodedEmployees->count() > 0 && $payrollPeriod->isDraft())
<div class="card" style="padding:0; overflow:hidden;">
    <div style="padding:20px 25px; border-bottom:1px solid #e5e7eb;">
        <h3 style="margin:0;">Pending Encoding</h3>
        <p style="color:#6b7280; margin:8px 0 0 0; font-size:14px;">Employees without attendance data for this period</p>
    </div>

    {{-- Filters --}}
    <div style="padding:20px 25px; background:#f9fafb; border-bottom:1px solid #e5e7eb;">
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <div style="flex:1; min-width:250px;">
                <input type="text" id="searchEmployee" name="searchEmployee" placeholder="Search by name or employee ID..."
                       style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                       oninput="filterEmployees()">
            </div>
            <div style="min-width:180px;">
                <select id="filterDepartment" name="filterDepartment"
                        style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
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
                        style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                        onchange="filterEmployees()">
                    <option value="">All Status</option>
                    <option value="Regular">Regular</option>
                    <option value="Probationary">Probationary</option>
                    <option value="Contractual">Contractual</option>
                    <option value="Part-time">Part-time</option>
                </select>
            </div>
            <button onclick="clearFilters()"
                    style="padding:10px 16px; background:#6b7280; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px;">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
        <div style="margin-top:8px; font-size:13px; color:#6b7280;">
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
                 style="border:1px solid #e5e7eb; border-radius:6px; padding:16px; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:32px; height:32px; border-radius:50%; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#6b7280; font-size:13px; font-weight:700;">
                        {{ strtoupper(substr($employee->first_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $employee->first_name ?? 'Unknown' }} {{ $employee->last_name ?? '' }}</div>
                        <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id ?? 'N/A' }}</div>
                        <div style="font-size:11px; color:#9ca3af; margin-top:2px;">{{ $employee->department ?? 'N/A' }} • {{ $employee->employment_status ?? 'N/A' }}</div>
                    </div>
                </div>
                <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollPeriod, $employee]) }}"
                   style="padding:6px 12px; background:{{ $color }}; color:white; border-radius:5px; font-size:12px; text-decoration:none;">
                    <i class="fas fa-keyboard"></i> Encode
                </a>
            </div>
            @endif
            @endforeach
        </div>
        <div id="noResults" style="display:none; padding:40px; text-align:center; color:#9ca3af;">
            <i class="fas fa-search" style="font-size:32px; margin-bottom:10px; display:block;"></i>
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
        .catch(() => notyf.error('Failed to load summary.'));
}

function finalizePayroll() {
    Swal.fire({
        title: 'Finalize Payroll Period?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, finalize it',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`{{ route('payroll-periods.finalize', $payrollPeriod) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                notyf.success('Success!');
                setTimeout(() => location.reload(), 1500);
            } else {
                notyf.error(data.message ?? 'Something went wrong.');
            }
        })
        .catch(() => notyf.error('Error finalizing payroll period.'));
    });
}
</script>
@endsection
@extends('layouts.app')

@section('title', 'Encode Attendance - ' . ($employee->first_name ?? 'Employee') . ' ' . ($employee->last_name ?? ''))

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
    $colorDark = $isAdmin ? '#991b1b' : ($isHR ? '#1e40af' : '#764ba2');
    $isEdit = $payrollInput !== null;
    $isSecondHalfOfMonth = $payrollPeriod->isSecondHalfOfMonth();
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-4">
    <div>
        <a href="{{ route('manual-payroll-attendance.period', $payrollPeriod) }}"
           class="inline-flex items-center text-sm text-base-content/60 mb-4 gap-3 no-underline">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Period
        </a>
        <div class="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg p-4 mb-4">
            <i class="icon-[tabler--user]-edit"></i> {{ $isEdit ? 'Edit' : 'Encode' }} Attendance
        </div>
        <h2 class="text-base-content">
            {{ $employee->first_name ?? 'Employee' }} {{ $employee->last_name ?? '' }}
        </h2>
        <p class="text-base-content/60">
            {{ $employee->employee_id ?? 'N/A' }} | {{ $employee->position ?? 'N/A' }} | {{ $employee->department ?? 'N/A' }} |
            Period: {{ $payrollPeriod->cutoff_start->format('M d') }} - {{ $payrollPeriod->cutoff_end->format('M d, Y') }}
        </p>
    </div>
</div>

<div class="payroll-form-grid grid gap-3">
    {{-- Attendance Encoding Form --}}
    <div class="card overflow-hidden p-4">
        <div class="border-base-300 p-4">
            <h3 class="text-base-content">Attendance Details</h3>
            <p class="text-sm text-base-content/60">Enter attendance totals for the payroll period</p>
        </div>

        <form id="attendanceForm" class="p-4">
            @csrf
            <input type="hidden" name="payroll_period_id" value="{{ $payrollPeriod->id }}">
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">

            {{-- Approved Work Requests Section --}}
            @php
                $approvedRequests = \App\Models\WorkRequest::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereBetween('work_date', [$payrollPeriod->cutoff_start->toDateString(), $payrollPeriod->cutoff_end->toDateString()])
                    ->where('work_date', '<=', now()->toDateString())
                    ->get();
            @endphp
            @if($approvedRequests->count() > 0)
            <div class="bg-success/10 border border-base-300 rounded-lg p-4 mb-4">
                <div class="flex items-center mb-4 gap-3">
                    <i class=" icon-[ph--calendar-fill]-check text-success"></i>
                    <span class="font-semibold text-success">Approved Work Requests ({{ $approvedRequests->count() }})</span>
                </div>
                <div class="text-sm text-success-content">
                    @foreach($approvedRequests as $request)
                    <div class="py-2 border-b border-success/20">
                        <span style="font-weight:600;">{{ ucfirst($request->request_type) }}</span>
                        <span class="text-base-content/60">|</span>
                        <span>{{ $request->work_date->format('M d, Y') }}</span>
                        @if($request->request_type === 'overtime' && $request->calculated_overtime_hours)
                        <span class="text-base-content/60">|</span>
                        <span>{{ number_format($request->calculated_overtime_hours, 1) }} OT hrs</span>
                        @elseif($request->estimated_hours)
                        <span class="text-base-content/60">|</span>
                        <span>{{ number_format($request->estimated_hours, 1) }} hrs</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-success-content mt-2 mb-0">
                    These requests have been auto-populated to the payroll fields below.
                </p>
            </div>
            @endif

            <div class="mb-4">
                <label class="font-semibold text-sm text-base-content mb-4">Rate Type</label>
                <div class="text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4">
                    <input type="hidden" name="rate_type" value="daily">
                    <span class="font-semibold">Daily Rate</span>
                    <span class="text-base-content/60">(computed using BSM X 12 / 52 / 40 X 8)</span>
                </div>
            </div>

            <div class="grid mb-4 gap-3">
                <div>
                    <label class="font-semibold text-sm text-base-content mb-4">Daily Rate</label>
                    <input type="number" name="daily_rate" step="0.01" min="0" required
                           value="{{ $isEdit ? $payrollInput->daily_rate : $dailyRate }}"
                           class="w-full text-sm border border-base-300 rounded-lg p-4"
                           oninput="window.dailyRateValue = this.value">
                    <p class="text-xs text-base-content/60 mt-2">Based on basic salary (₱{{ number_format($employee->basic_salary ?? 0, 2) }})</p>
                </div>
                <div>
                    <label class="font-semibold text-sm text-base-content mb-4">Days Worked</label>
                    <input type="number" name="days_worked" step="0.5" min="0" max="31"
                           value="{{ $isEdit ? $payrollInput->days_worked : ($computedDaysFromAttendance ?? '0') }}"
                           class="w-full text-sm border border-base-300 rounded-lg p-4"
                           oninput="window.daysWorkedValue = this.value"
                           placeholder="{{ $computedDaysFromAttendance ? 'Leave blank to use ' . number_format($computedDaysFromAttendance, 2) . ' days from attendance' : 'Enter days worked' }}">
                    <p class="text-xs text-base-content/60 mt-2">@if($computedDaysFromAttendance) Leave blank to use {{ number_format($computedDaysFromAttendance, 2) }} days from attendance records, or enter a custom value. @else Enter days worked for the period. @endif</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="font-semibold text-sm text-base-content mb-4">Weekends Worked</label>
                <input type="number" name="weekends_worked" step="0.5" min="0"
                       value="{{ $isEdit ? ($payrollInput->weekends_worked ?? '0') : ($weekendsWorked ?? '0') }}"
                       class="w-full text-sm border border-base-300 rounded-lg p-4"
                       oninput="window.weekendsWorkedValue = this.value">
                <p class="text-xs text-base-content/60 mt-2">Number of weekend days worked (paid at 30% premium of daily rate)</p>
            </div>

            <div class="grid mb-4 gap-3">
                <div>
                    <label class="font-semibold text-sm text-base-content mb-4">Overtime Hours</label>
                    <input type="number" name="overtime_hours" step="0.5" min="0"
                           value="{{ $isEdit ? $payrollInput->overtime_hours : ($overtimeHours ?? '0') }}"
                           class="w-full text-sm border border-base-300 rounded-lg p-4"
                           oninput="window.overtimeHoursValue = this.value">
                </div>
                <div>
                    <label class="font-semibold text-sm text-base-content mb-4">Late Hours</label>
                    <input type="number" name="late_hours" step="0.5" min="0"
                           value="{{ $isEdit ? $payrollInput->late_hours : '0' }}"
                           class="w-full text-sm border border-base-300 rounded-lg p-4"
                           oninput="window.lateHoursValue = this.value">
                </div>
            </div>

            <div class="mb-4">
                <label class="font-semibold text-sm text-base-content mb-4">Holiday Days Worked</label>
                <input type="number" name="holiday_days" step="0.5" min="0"
                       value="{{ $isEdit ? ($payrollInput->holiday_days ?? '0') : ($holidayDays ?? '0') }}"
                       class="w-full text-sm border border-base-300 rounded-lg p-4"
                       oninput="window.holidayDaysValue = this.value">
                <p class="text-xs text-base-content/60 mt-2">Number of regular holidays worked (paid at 200% of daily rate)</p>
            </div>

            <div class="mb-4">
                <label class="font-semibold text-sm text-base-content mb-4">Night Differential Hours</label>
                <input type="number" name="night_differential_hours" step="0.5" min="0"
                       value="{{ $isEdit ? ($payrollInput->night_differential_hours ?? '0') : '0' }}"
                       class="w-full text-sm border border-base-300 rounded-lg p-4"
                       oninput="window.nightDifferentialHoursValue = this.value">
                <p class="text-xs text-base-content/60 mt-2">Hours worked during night shift (paid at 10% premium of hourly rate)</p>
            </div>

            {{-- Allowances & Benefits Section --}}
            <div class="mb-4">
                <label class="font-semibold text-sm text-base-content mb-4">Allowances & Benefits</label>
                
                @php
                    $totalAllowances = $employee->activeAllowances->sum('amount');
                    $totalBenefits = $employee->activeBenefits->sum('amount');
                    $totalAllowancesAndBenefits = round(($totalAllowances + $totalBenefits) / 2, 2);
                    $totalAllowancesPerCutoff = round($totalAllowances / 2, 2);
                    $totalBenefitsPerCutoff = round($totalBenefits / 2, 2);
                @endphp
                
                <div class="bg-base-200 border border-base-300 rounded-lg p-4">
                    @if($employee->activeAllowances->count() > 0 || $employee->activeBenefits->count() > 0)
                        <div class="grid mb-4 gap-3">
                            @foreach($employee->activeAllowances as $allowance)
                                <div class="flex items-center justify-between bg-base-100 border-s-4 border-primary rounded-lg p-4">
                                    <div>
                                        <div class="font-semibold text-xs text-base-content">{{ $allowance->name }}</div>
                                        <div class="text-base-content/60">{{ $allowance->type }}</div>
                                    </div>
                                    <div class="font-bold text-success text-sm">₱{{ number_format(round($allowance->amount / 2, 2), 2) }}</div>
                                </div>
                            @endforeach
                            @foreach($employee->activeBenefits as $benefit)
                                <div class="flex items-center justify-between bg-base-100 border-s-4 border-primary rounded-lg p-4">
                                    <div>
                                        <div class="font-semibold text-xs text-base-content">{{ $benefit->name }}</div>
                                        <div class="text-base-content/60">{{ $benefit->type }}</div>
                                    </div>
                                    <div class="font-bold text-sm">₱{{ number_format(round($benefit->amount / 2, 2), 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-center justify-between border-base-300">
                            <span class="font-semibold text-sm text-base-content">Total Allowances & Benefits (per cutoff):</span>
                            <span class="font-bold text-success">₱{{ number_format($totalAllowancesAndBenefits, 2) }}</span>
                        </div>
                    @else
                        <div class="text-center text-xs text-base-content/60 p-4">
                            No allowances or benefits configured for this employee.
                        </div>
                    @endif
                </div>
                <input type="hidden" name="allowances" value="{{ $totalAllowancesAndBenefits }}">
            </div>

            <div class="mb-4">
                <label class="font-semibold text-sm text-base-content mb-4">Deductions</label>
                <input type="number" name="deductions" step="0.01" min="0"
                       value="{{ $isEdit ? $payrollInput->deductions : '0' }}"
                       class="w-full text-sm border border-base-300 rounded-lg p-4"
                       oninput="window.deductionsValue = this.value">
                <input type="text" name="deductions_remarks"
                       value="{{ $isEdit ? ($payrollInput->deductions_remarks ?? '') : '' }}"
                       placeholder="Remarks (optional)"
                       class="w-full text-sm border border-base-300 rounded-lg p-4 mt-2"
                       oninput="window.deductionsRemarksValue = this.value">
            </div>

            <div class="mb-4">
                <label class="font-semibold text-sm text-base-content mb-4">Reimbursements</label>
                <input type="number" name="reimbursements" step="0.01" min="0"
                       value="{{ $isEdit ? ($payrollInput->reimbursements ?? '0') : '0' }}"
                       class="w-full text-sm border border-base-300 rounded-lg p-4"
                       oninput="window.reimbursementsValue = this.value">
                <input type="text" name="reimbursements_remarks"
                       value="{{ $isEdit ? ($payrollInput->reimbursements_remarks ?? '') : '' }}"
                       placeholder="Remarks (optional)"
                       class="w-full text-sm border border-base-300 rounded-lg p-4 mt-2"
                       oninput="window.reimbursementsRemarksValue = this.value">
                <p class="text-xs text-base-content/60 mt-2">Expense reimbursements to be added to net pay</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" id="saveAttendanceBtn" onclick="handleSaveAttendance(event)"
                        class="font-semibold text-sm bg-primary rounded-lg p-4 cursor-pointer">
                    <i class="icon-[ph--floppy-disk-fill]"></i> {{ $isEdit ? 'Update' : 'Save' }} Attendance
                </button>
                <button type="button" id="previewBtn" onclick="previewPayroll(); return false;"
                        class="text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-4 cursor-pointer">
                    <i class="icon-[ph--calculator-fill]"></i> Preview
                </button>
            </div>
        </form>
    </div>

    {{-- Payroll Preview Panel --}}
    <div class="card overflow-hidden p-4">
        <div class="bg-base-200 border-base-300 p-4">
            <h3 class="flex items-center gap-3">
                <i class="icon-[ph--receipt-fill] text-base-content/60"></i> Payroll Preview
            </h3>
        </div>

        <div class="p-4" id="previewPanel">
            <div class="text-center text-base-content/60 p-4">
                <i class="icon-[ph--calculator-fill] text-2xl mb-4"></i>
                <p class="text-sm">Click "Preview" to see payroll computation</p>
            </div>
        </div>
    </div>
</div>


@endsection


@section('scripts')
<script>
// Initialize global variables with default values
window.dailyRateValue = '{{ $isEdit ? $payrollInput->daily_rate : $dailyRate }}';
window.daysWorkedValue = '{{ $isEdit ? $payrollInput->days_worked : ($computedDaysFromAttendance ?? '0') }}';
window.weekendsWorkedValue = '{{ $isEdit ? ($payrollInput->weekends_worked ?? '0') : '0' }}';
window.overtimeHoursValue = '{{ $isEdit ? $payrollInput->overtime_hours : '0' }}';
window.lateHoursValue = '{{ $isEdit ? $payrollInput->late_hours : '0' }}';
window.holidayDaysValue = '{{ $isEdit ? ($payrollInput->holiday_days ?? '0') : '0' }}';
window.nightDifferentialHoursValue = '{{ $isEdit ? ($payrollInput->night_differential_hours ?? '0') : '0' }}';
window.allowancesValue = '{{ $totalAllowancesAndBenefits ?? '0' }}';
window.deductionsValue = '{{ $isEdit ? $payrollInput->deductions : '0' }}';
window.deductionsRemarksValue = '{{ $isEdit ? ($payrollInput->deductions_remarks ?? '') : '' }}';
window.reimbursementsValue = '{{ $isEdit ? ($payrollInput->reimbursements ?? '0') : '0' }}';
window.reimbursementsRemarksValue = '{{ $isEdit ? ($payrollInput->reimbursements_remarks ?? '') : '' }}';
window.isSecondHalfOfMonth = {{ $isSecondHalfOfMonth ? 'true' : 'false' }};

function handleSaveAttendance(event) {
    event.preventDefault();
    
    const form = document.getElementById('attendanceForm');
    if (!form) {
        return;
    }
    
    // Read values directly from input elements for reliability
    const formData = new FormData();
    formData.append('payroll_period_id', form.querySelector('[name="payroll_period_id"]').value);
    formData.append('employee_id', form.querySelector('[name="employee_id"]').value);
    formData.append('daily_rate', form.querySelector('[name="daily_rate"]').value);
    formData.append('rate_type', form.querySelector('[name="rate_type"]').value);
    formData.append('days_worked', form.querySelector('[name="days_worked"]').value);
    formData.append('weekends_worked', form.querySelector('[name="weekends_worked"]').value);
    formData.append('overtime_hours', form.querySelector('[name="overtime_hours"]').value);
    formData.append('late_hours', form.querySelector('[name="late_hours"]').value);
    formData.append('holiday_days', form.querySelector('[name="holiday_days"]').value);
    formData.append('night_differential_hours', form.querySelector('[name="night_differential_hours"]').value);
    formData.append('allowances', form.querySelector('[name="allowances"]').value);
    formData.append('deductions', form.querySelector('[name="deductions"]').value);
    formData.append('deductions_remarks', form.querySelector('[name="deductions_remarks"]').value);
    formData.append('reimbursements', form.querySelector('[name="reimbursements"]').value);
    formData.append('reimbursements_remarks', form.querySelector('[name="reimbursements_remarks"]').value);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route('manual-payroll-attendance.save') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if(data.success) {
            window.notyf.success('Attendance saved successfully!');
            window.location.href = '{{ route('manual-payroll-attendance.period', $payrollPeriod) }}';
        } else {
            window.notyf.error(data.message ?? 'Something went wrong.');
        }
    })
    .catch(error => {
        window.notyf.error('Error saving attendance: ' + error.message);
    });
}

let previewTimeout = null;

function previewPayroll() {
    const form = document.getElementById('attendanceForm');
    if (!form) {
        return;
    }

    const formData = new FormData();
    formData.append('payroll_period_id', form.querySelector('[name="payroll_period_id"]').value);
    formData.append('employee_id', form.querySelector('[name="employee_id"]').value);
    formData.append('daily_rate', window.dailyRateValue);
    formData.append('rate_type', form.querySelector('[name="rate_type"]').value);
    formData.append('days_worked', window.daysWorkedValue);
    formData.append('weekends_worked', window.weekendsWorkedValue);
    formData.append('overtime_hours', window.overtimeHoursValue);
    formData.append('late_hours', window.lateHoursValue);
    formData.append('holiday_days', window.holidayDaysValue);
    formData.append('night_differential_hours', window.nightDifferentialHoursValue);
    formData.append('allowances', window.allowancesValue);
    formData.append('deductions', window.deductionsValue);
    formData.append('deductions_remarks', window.deductionsRemarksValue);
    formData.append('reimbursements', window.reimbursementsValue);
    formData.append('reimbursements_remarks', window.reimbursementsRemarksValue);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route('manual-payroll-attendance.preview') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if(data.success) {
            const previewData = data.preview || {};
            if(previewData.gross_pay !== undefined && previewData.net_pay !== undefined) {
                // Update all preview panels (both mobile and desktop)
                const previewPanels = document.querySelectorAll('#previewPanel');
                previewPanels.forEach((previewPanel, index) => {
                    previewPanel.innerHTML = `
                        <div class="mb-4">
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Basic Salary:</span>
                                <span class="font-semibold text-base-content">₱${previewData.basic_salary ? previewData.basic_salary.toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Weekend Rate:</span>
                                <span class="text-success">+₱${previewData.weekend_pay ? previewData.weekend_pay.toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Overtime Rate:</span>
                                <span class="text-success">+₱${previewData.overtime_pay ? previewData.overtime_pay.toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Holiday Rate:</span>
                                <span class="text-success">+₱${previewData.holiday_pay ? previewData.holiday_pay.toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Night Differential:</span>
                                <span class="text-success">+₱${previewData.night_differential ? previewData.night_differential.toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Allowances:</span>
                                <span class="text-success">+₱${previewData.allowances ? previewData.allowances.toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Late Deduction:</span>
                                <span class="text-error">-₱${previewData.late_deduction ? previewData.late_deduction.toFixed(2) : '0.00'}</span>
                            </div>
                        </div>
                        <div class="bg-base-200 rounded-lg p-4 mb-4">
                            <div class="flex justify-between font-bold text-sm text-base-content">
                                <span>Gross Pay:</span>
                                <span>₱${previewData.gross_pay.toFixed(2)}</span>
                            </div>
                        </div>
                        ${window.isSecondHalfOfMonth && (previewData.first_cutoff_gross_pay > 0 || previewData.second_cutoff_gross_pay > 0) ? `
                        <div class="bg-info/10 border border-base-300 rounded-lg p-4 mb-4">
                            <div class="font-semibold text-xs text-info mb-4">Monthly Cutoff Breakdown</div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">1st Cutoff Pay:</span>
                                <span class="font-semibold text-base-content">₱${previewData.first_cutoff_gross_pay ? parseFloat(previewData.first_cutoff_gross_pay).toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">2nd Cutoff Pay:</span>
                                <span class="font-semibold text-base-content">₱${previewData.second_cutoff_gross_pay ? parseFloat(previewData.second_cutoff_gross_pay).toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between font-bold text-sm text-info border-base-300">
                                <span>Total Monthly Gross:</span>
                                <span>₱${previewData.total_monthly_gross_pay ? parseFloat(previewData.total_monthly_gross_pay).toFixed(2) : '0.00'}</span>
                            </div>
                        </div>
                        ` : ''}
                        <div class="mb-4">
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">SSS Contribution:</span>
                                <span class="text-error">-₱${previewData.sss_contribution ? parseFloat(previewData.sss_contribution).toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">PhilHealth Contribution:</span>
                                <span class="text-error">-₱${previewData.philhealth_contribution ? parseFloat(previewData.philhealth_contribution).toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Pag-IBIG Contribution:</span>
                                <span class="text-error">-₱${previewData.pagibig_contribution ? parseFloat(previewData.pagibig_contribution).toFixed(2) : '0.00'}</span>
                            </div>
                            ${window.isSecondHalfOfMonth ? `
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Withholding Tax:</span>
                                <span class="text-error">-₱${previewData.withholding_tax ? parseFloat(previewData.withholding_tax).toFixed(2) : '0.00'}</span>
                            </div>
                            ` : ''}
                            @if($isEdit)
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Manual Deductions:</span>
                                <span class="text-error">-₱${previewData.manual_deductions ? parseFloat(previewData.manual_deductions).toFixed(2) : '0.00'}</span>
                            </div>
                            @endif
                            <div class="flex justify-between font-semibold text-xs text-base-content border-base-300 mb-4">
                                <span>Total Deductions:</span>
                                <span class="text-error">-₱${previewData.deductions ? parseFloat(previewData.deductions).toFixed(2) : '0.00'}</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">Reimbursements:</span>
                                <span class="text-success">+₱${window.reimbursementsValue ? parseFloat(window.reimbursementsValue).toFixed(2) : '0.00'}</span>
                            </div>
                        </div>
                        ${window.isSecondHalfOfMonth && (previewData.first_cutoff_net_pay > 0 || previewData.second_cutoff_net_pay > 0) ? `
                        <div class="bg-success/10 border border-base-300 rounded-lg p-4 mb-4">
                            <div class="font-semibold text-xs mb-4">Monthly Net Pay Breakdown</div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">1st Cutoff Net:</span>
                                <span class="font-semibold text-base-content">₱${previewData.first_cutoff_net_pay ? parseFloat(previewData.first_cutoff_net_pay).toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between text-xs mb-4">
                                <span class="text-base-content/60">2nd Cutoff Net:</span>
                                <span class="font-semibold text-base-content">₱${previewData.second_cutoff_net_pay ? parseFloat(previewData.second_cutoff_net_pay).toFixed(2) : '0.00'}</span>
                            </div>
                            <div class="flex justify-between font-bold text-sm border-base-300">
                                <span>Total Monthly Net:</span>
                                <span>₱${previewData.total_monthly_net_pay ? parseFloat(previewData.total_monthly_net_pay).toFixed(2) : '0.00'}</span>
                            </div>
                        </div>
                        ` : ''}
                        <div class="rounded-lg p-4">
                            <div class="flex justify-between font-bold">
                                <span>Net Pay:</span>
                                <span>₱${(parseFloat(previewData.net_pay) + parseFloat(window.reimbursementsValue || 0)).toFixed(2)}</span>
                            </div>
                        </div>
                    `;
                });
            } else {
                window.notyf.error('Missing gross_pay or net_pay in response');
            }
        } else {
            window.notyf.error('Preview failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        window.notyf.error('Error previewing payroll: ' + error.message);
    });
}

// Auto-preview on input change with debounce
function triggerAutoPreview() {
    if (previewTimeout) {
        clearTimeout(previewTimeout);
    }
    previewTimeout = setTimeout(() => {
        previewPayroll();
    }, 500);
}
</script>
@endsection

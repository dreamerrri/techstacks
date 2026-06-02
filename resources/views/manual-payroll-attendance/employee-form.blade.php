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
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <a href="{{ route('manual-payroll-attendance.period', $payrollPeriod) }}"
           style="color:#6b7280; text-decoration:none; font-size:14px; display:inline-flex; align-items:center; gap:6px; margin-bottom:8px;">
            <i class="fas fa-arrow-left"></i> Back to Period
        </a>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-user-edit"></i> {{ $isEdit ? 'Edit' : 'Encode' }} Attendance
        </div>
        <h2 style="margin:8px 0 4px 0;">
            {{ $employee->first_name ?? 'Employee' }} {{ $employee->last_name ?? '' }}
        </h2>
        <p style="color:#6b7280; margin:0;">
            {{ $employee->employee_id ?? 'N/A' }} | {{ $employee->position ?? 'N/A' }} | {{ $employee->department ?? 'N/A' }} |
            Period: {{ $payrollPeriod->cutoff_start->format('M d') }} - {{ $payrollPeriod->cutoff_end->format('M d, Y') }}
        </p>
    </div>
</div>

<div class="payroll-form-grid" style="display:grid; grid-template-columns:1fr 350px; gap:24px;">
    {{-- Attendance Encoding Form --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 25px; border-bottom:1px solid #e5e7eb;">
            <h3 style="margin:0;">Attendance Details</h3>
            <p style="color:#6b7280; margin:8px 0 0 0; font-size:14px;">Enter attendance totals for the payroll period</p>
        </div>

        <form id="attendanceForm" style="padding:25px;">
            @csrf
            <input type="hidden" name="payroll_period_id" value="{{ $payrollPeriod->id }}">
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Rate Type</label>
                <div style="padding:10px 12px; background:#f9fafb; border:1px solid #d1d5db; border-radius:6px; font-size:14px; color:#374151;">
                    <input type="hidden" name="rate_type" value="{{ $employee->salary_type === 'Monthly' ? 'monthly' : 'daily' }}">
                    <span style="font-weight:600;">{{ ucfirst($employee->salary_type === 'Monthly' ? 'Monthly' : 'Daily') }} Rate</span>
                    <span style="color:#6b7280; margin-left:8px;">(based on employee salary type)</span>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">{{ $employee->salary_type === 'Monthly' ? 'Monthly' : 'Daily' }} Rate</label>
                    <input type="number" name="daily_rate" step="0.01" min="0" required
                           value="{{ $isEdit ? $payrollInput->daily_rate : $dailyRate }}"
                           style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                           oninput="window.dailyRateValue = this.value; console.log('Daily rate changed:', this.value)">
                    <p style="color:#6b7280; font-size:12px; margin-top:4px;">Based on {{ $employee->salary_type ?? 'N/A' }} salary (₱{{ number_format($employee->basic_salary ?? 0, 2) }})</p>
                </div>
                <div>
                    <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Days Worked</label>
                    <input type="number" name="days_worked" step="0.5" min="0" max="31" required
                           value="{{ $isEdit ? $payrollInput->days_worked : '0' }}"
                           style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                           oninput="window.daysWorkedValue = this.value; console.log('Days worked changed:', this.value)">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Regular Hours</label>
                <input type="number" name="regular_hours" step="0.5" min="0"
                       value="{{ $isEdit ? ($payrollInput->regular_hours ?? '0') : '0' }}"
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                       oninput="window.regularHoursValue = this.value; console.log('Regular hours changed:', this.value)">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">Total regular hours worked (excluding overtime)</p>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Overtime Hours</label>
                    <input type="number" name="overtime_hours" step="0.5" min="0"
                           value="{{ $isEdit ? $payrollInput->overtime_hours : '0' }}"
                           style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                           oninput="window.overtimeHoursValue = this.value; console.log('Overtime hours changed:', this.value)">
                </div>
                <div>
                    <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Late Hours</label>
                    <input type="number" name="late_hours" step="0.5" min="0"
                           value="{{ $isEdit ? $payrollInput->late_hours : '0' }}"
                           style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                           oninput="window.lateHoursValue = this.value; console.log('Late hours changed:', this.value)">
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Holiday Days Worked</label>
                <input type="number" name="holiday_days" step="0.5" min="0"
                       value="{{ $isEdit ? ($payrollInput->holiday_days ?? '0') : '0' }}"
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                       oninput="window.holidayDaysValue = this.value; console.log('Holiday days changed:', this.value)">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">Number of regular holidays worked (paid at 200% of daily rate)</p>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Night Differential Hours</label>
                <input type="number" name="night_differential_hours" step="0.5" min="0"
                       value="{{ $isEdit ? ($payrollInput->night_differential_hours ?? '0') : '0' }}"
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                       oninput="window.nightDifferentialHoursValue = this.value; console.log('Night differential hours changed:', this.value)">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">Hours worked during night shift (paid at 10% premium of hourly rate)</p>
            </div>

            {{-- Allowances & Benefits Section --}}
            <div style="margin-bottom:24px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:12px; font-size:14px;">Allowances & Benefits</label>
                
                @php
                    $totalAllowances = $employee->activeAllowances->sum('amount');
                    $totalBenefits = $employee->activeBenefits->sum('amount');
                    $totalAllowancesAndBenefits = $totalAllowances + $totalBenefits;
                @endphp
                
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:16px;">
                    @if($employee->activeAllowances->count() > 0 || $employee->activeBenefits->count() > 0)
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:12px;">
                            @foreach($employee->activeAllowances as $allowance)
                                <div style="background:#fff; padding:10px; border-radius:4px; border-left:3px solid #10b981; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <div style="font-weight:600; color:#1f2937; font-size:13px;">{{ $allowance->name }}</div>
                                        <div style="color:#6b7280; font-size:11px;">{{ $allowance->type }}</div>
                                    </div>
                                    <div style="font-weight:700; color:#10b981; font-size:14px;">₱{{ number_format($allowance->amount, 2) }}</div>
                                </div>
                            @endforeach
                            @foreach($employee->activeBenefits as $benefit)
                                <div style="background:#fff; padding:10px; border-radius:4px; border-left:3px solid #3b82f6; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <div style="font-weight:600; color:#1f2937; font-size:13px;">{{ $benefit->name }}</div>
                                        <div style="color:#6b7280; font-size:11px;">{{ $benefit->type }}</div>
                                    </div>
                                    <div style="font-weight:700; color:#3b82f6; font-size:14px;">₱{{ number_format($benefit->amount, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div style="padding-top:12px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:600; color:#374151; font-size:14px;">Total Allowances & Benefits:</span>
                            <span style="font-weight:700; color:#10b981; font-size:16px;">₱{{ number_format($totalAllowancesAndBenefits, 2) }}</span>
                        </div>
                    @else
                        <div style="text-align:center; color:#9ca3af; font-size:13px; padding:8px;">
                            No allowances or benefits configured for this employee.
                        </div>
                    @endif
                </div>
                <input type="hidden" name="allowances" value="{{ $totalAllowancesAndBenefits }}">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Deductions</label>
                <input type="number" name="deductions" step="0.01" min="0"
                       value="{{ $isEdit ? $payrollInput->deductions : '0' }}"
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;"
                       oninput="window.deductionsValue = this.value; console.log('Deductions changed:', this.value)">
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit" id="saveAttendanceBtn" onclick="handleSaveAttendance(event)"
                        style="flex:1; padding:12px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; pointer-events:auto !important; z-index:1000 !important;">
                    <i class="fas fa-save"></i> {{ $isEdit ? 'Update' : 'Save' }} Attendance
                </button>
                <button type="button" id="previewBtn" onclick="previewPayroll(); return false;"
                        style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px;">
                    <i class="fas fa-calculator"></i> Preview
                </button>
            </div>
        </form>
    </div>

    {{-- Payroll Preview Panel --}}
    <div class="card" style="padding:0; overflow:hidden; height:fit-content;">
        <div style="padding:20px 25px; border-bottom:1px solid #e5e7eb; background:#f9fafb;">
            <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-receipt" style="color:#6b7280;"></i> Payroll Preview
            </h3>
        </div>

        <div style="padding:25px;" id="previewPanel">
            <div style="text-align:center; padding:40px 20px; color:#9ca3af;">
                <i class="fas fa-calculator" style="font-size:32px; margin-bottom:12px; display:block;"></i>
                <p style="margin:0; font-size:14px;">Click "Preview" to see payroll computation</p>
            </div>
        </div>
    </div>
</div>

 {{-- Adjustments Section --}}
@if($isEdit)
<div class="card" style="padding:0; overflow:hidden; margin-top:24px;">
    <div style="padding:20px 25px; border-bottom:1px solid #e5e7eb;">
        <h3 style="margin:0; display:flex; justify-content:space-between; align-items:center;">
            Payroll Adjustments
            <button type="button" onclick="showAddAdjustmentModal()"
                    style="padding:8px 16px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13px;">
                <i class="fas fa-plus"></i> Add Adjustment
            </button>
        </h3>
    </div>

    @if($payrollInput->adjustments && $payrollInput->adjustments->count() > 0)
    <div style="padding:0;">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead style="background:#f9fafb;">
                <tr>
                    <th style="padding:12px 25px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Type</th>
                    <th style="padding:12px; text-align:right; color:#6b7280; font-size:12px; text-transform:uppercase;">Amount</th>
                    <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Remarks</th>
                    <th style="padding:12px; text-align:center; color:#6b7280; font-size:12px; text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrollInput->adjustments as $adjustment)
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:12px 25px;">
                        <span style="padding:4px 10px; background:#dbeafe; color:#1e40af; border-radius:4px; font-size:12px; font-weight:600;">
                            {{ ucfirst($adjustment->adjustment_type) }}
                        </span>
                    </td>
                    <td style="padding:12px; text-align:right; font-weight:600; color:#1f2937;">
                        ₱{{ number_format($adjustment->amount, 2) }}
                    </td>
                    <td style="padding:12px; color:#6b7280;">{{ $adjustment->remarks ?? '-' }}</td>
                    <td style="padding:12px; text-align:center;">
                        @if($payrollPeriod->isDraft())
                        <button type="button" onclick="deleteAdjustment({{ $adjustment->id }})"
                                style="padding:6px 10px; background:#fee2e2; color:#dc2626; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="padding:40px 25px; text-align:center; color:#9ca3af;">
        <i class="fas fa-sliders-h" style="font-size:32px; margin-bottom:10px; display:block;"></i>
        No adjustments added.
    </div>
    @endif
</div>
@endif

@endsection

@section('scripts')
<script>
console.log('JavaScript loaded successfully');

// Initialize global variables with default values
window.dailyRateValue = '{{ $isEdit ? $payrollInput->daily_rate : $dailyRate }}';
window.daysWorkedValue = '{{ $isEdit ? $payrollInput->days_worked : '0' }}';
window.regularHoursValue = '{{ $isEdit ? ($payrollInput->regular_hours ?? '0') : '0' }}';
window.overtimeHoursValue = '{{ $isEdit ? $payrollInput->overtime_hours : '0' }}';
window.lateHoursValue = '{{ $isEdit ? $payrollInput->late_hours : '0' }}';
window.holidayDaysValue = '{{ $isEdit ? ($payrollInput->holiday_days ?? '0') : '0' }}';
window.nightDifferentialHoursValue = '{{ $isEdit ? ($payrollInput->night_differential_hours ?? '0') : '0' }}';
window.allowancesValue = '{{ $totalAllowancesAndBenefits ?? '0' }}';
window.deductionsValue = '{{ $isEdit ? $payrollInput->deductions : '0' }}';


function handleSaveAttendance(event) {
    event.preventDefault();
    console.log('handleSaveAttendance called via onclick');
    
    const form = document.getElementById('attendanceForm');
    if (!form) {
        console.error('Form not found in handleSaveAttendance');
        return;
    }
    
    // Use stored values from global variables instead of reading from input elements
    const formData = new FormData();
    formData.append('payroll_period_id', form.querySelector('[name="payroll_period_id"]').value);
    formData.append('employee_id', form.querySelector('[name="employee_id"]').value);
    formData.append('daily_rate', window.dailyRateValue);
    formData.append('rate_type', form.querySelector('[name="rate_type"]').value);
    formData.append('days_worked', window.daysWorkedValue);
    formData.append('regular_hours', window.regularHoursValue);
    formData.append('overtime_hours', window.overtimeHoursValue);
    formData.append('late_hours', window.lateHoursValue);
    formData.append('holiday_days', window.holidayDaysValue);
    formData.append('night_differential_hours', window.nightDifferentialHoursValue);
    formData.append('allowances', window.allowancesValue);
    formData.append('deductions', window.deductionsValue);
    formData.append('_token', '{{ csrf_token() }}');
    
    console.log('FormData created in handleSaveAttendance');
    console.log('Using stored values:', {
        daily_rate: window.dailyRateValue,
        days_worked: window.daysWorkedValue,
        regular_hours: window.regularHoursValue,
        overtime_hours: window.overtimeHoursValue,
        late_hours: window.lateHoursValue,
        holiday_days: window.holidayDaysValue,
        night_differential_hours: window.nightDifferentialHoursValue,
        allowances: window.allowancesValue,
        deductions: window.deductionsValue
    });
    
    // Log form data for debugging
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    fetch('{{ route('manual-payroll-attendance.save') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('Response received:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if(data.success) {
            alert('Attendance saved successfully!');
            window.location.href = '{{ route('manual-payroll-attendance.period', $payrollPeriod) }}';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving attendance:', error);
        alert('Error saving attendance: ' + error.message);
    });
}

function previewPayroll() {
    console.log('Preview button clicked');
    const form = document.getElementById('attendanceForm');
    if (!form) {
        console.error('Form not found');
        return;
    }

    const formData = new FormData();

    formData.append('payroll_period_id', form.querySelector('[name="payroll_period_id"]').value);
    formData.append('employee_id', form.querySelector('[name="employee_id"]').value);
    formData.append('daily_rate', window.dailyRateValue);
    formData.append('rate_type', form.querySelector('[name="rate_type"]').value);
    formData.append('days_worked', window.daysWorkedValue);
    formData.append('regular_hours', window.regularHoursValue);
    formData.append('overtime_hours', window.overtimeHoursValue);
    formData.append('late_hours', window.lateHoursValue);
    formData.append('holiday_days', window.holidayDaysValue);
    formData.append('night_differential_hours', window.nightDifferentialHoursValue);
    formData.append('allowances', window.allowancesValue);
    formData.append('deductions', window.deductionsValue);
    formData.append('_token', '{{ csrf_token() }}');

    console.log('Previewing payroll with values:', {
        daily_rate: window.dailyRateValue,
        days_worked: window.daysWorkedValue,
        regular_hours: window.regularHoursValue,
        overtime_hours: window.overtimeHoursValue,
        late_hours: window.lateHoursValue,
        holiday_days: window.holidayDaysValue,
        night_differential_hours: window.nightDifferentialHoursValue,
        allowances: window.allowancesValue,
        deductions: window.deductionsValue
    });

    fetch('{{ route('manual-payroll-attendance.preview') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Preview response data:', data);
        if(data.success) {
            const previewData = data.preview || {};
            if(previewData.gross_pay !== undefined && previewData.net_pay !== undefined) {
                // Update all preview panels (both mobile and desktop)
                const previewPanels = document.querySelectorAll('#previewPanel');
                console.log('Found preview panels:', previewPanels.length);
                previewPanels.forEach((previewPanel, index) => {
                    console.log('Updating preview panel', index);
                    previewPanel.innerHTML = `
                        <div style="margin-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Basic Salary:</span>
                                <span style="font-weight:600; color:#1f2937;">₱${previewData.basic_salary ? previewData.basic_salary.toFixed(2) : '0.00'}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Regular Hours:</span>
                                <span style="font-weight:600; color:#1f2937;">${window.regularHoursValue || '0.00'} hrs</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Overtime Pay:</span>
                                <span style="color:#10b981;">+₱${previewData.overtime_pay ? previewData.overtime_pay.toFixed(2) : '0.00'}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Holiday Pay:</span>
                                <span style="color:#10b981;">+₱${previewData.holiday_pay ? previewData.holiday_pay.toFixed(2) : '0.00'}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Night Differential:</span>
                                <span style="color:#10b981;">+₱${previewData.night_differential ? previewData.night_differential.toFixed(2) : '0.00'}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Allowances:</span>
                                <span style="color:#10b981;">+₱${previewData.allowances ? previewData.allowances.toFixed(2) : '0.00'}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Late Deduction:</span>
                                <span style="color:#dc2626;">-₱${previewData.late_deduction ? previewData.late_deduction.toFixed(2) : '0.00'}</span>
                            </div>
                        </div>
                        <div style="padding:12px; background:#f9fafb; border-radius:6px; margin-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:700; color:#1f2937;">
                                <span>Gross Pay:</span>
                                <span>₱${previewData.gross_pay.toFixed(2)}</span>
                            </div>
                        </div>
                        <div style="margin-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">SSS Contribution:</span>
                                <span style="color:#dc2626;">-₱${previewData.sss_contribution ? parseFloat(previewData.sss_contribution).toFixed(2) : '0.00'}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">PhilHealth Contribution:</span>
                                <span style="color:#dc2626;">-₱${previewData.philhealth_contribution ? parseFloat(previewData.philhealth_contribution).toFixed(2) : '0.00'}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Pag-IBIG Contribution:</span>
                                <span style="color:#dc2626;">-₱${previewData.pagibig_contribution ? parseFloat(previewData.pagibig_contribution).toFixed(2) : '0.00'}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Withholding Tax:</span>
                                <span style="color:#dc2626;">-₱${previewData.withholding_tax ? parseFloat(previewData.withholding_tax).toFixed(2) : '0.00'}</span>
                            </div>
                            @if($isEdit)
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                                <span style="color:#6b7280;">Manual Deductions:</span>
                                <span style="color:#dc2626;">-₱${previewData.manual_deductions ? parseFloat(previewData.manual_deductions).toFixed(2) : '0.00'}</span>
                            </div>
                            @endif
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px; font-weight:600; color:#1f2937; padding-top:8px; border-top:1px solid #e5e7eb;">
                                <span>Total Deductions:</span>
                                <span style="color:#dc2626;">-₱${previewData.deductions ? parseFloat(previewData.deductions).toFixed(2) : '0.00'}</span>
                            </div>
                        </div>
                        <div style="padding:16px; background:linear-gradient(135deg,{{ $color }},{{ $colorDark }}); border-radius:6px;">
                            <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:700; color:white;">
                                <span>Net Pay:</span>
                                <span>₱${previewData.net_pay.toFixed(2)}</span>
                            </div>
                        </div>
                    `;
                });
                console.log('All preview panels updated successfully');
            } else {
                console.error('Missing gross_pay or net_pay in response:', data);
            }
        } else {
            console.error('Preview failed:', data.message || 'Unknown error');
            alert('Preview failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error previewing payroll:', error);
        alert('Error previewing payroll: ' + error.message);
    });
}

let previewTimeout;
</script>
@endsection

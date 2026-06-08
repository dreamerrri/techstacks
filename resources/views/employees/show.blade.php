@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')

<style>
.hidden-form {
    display: none !important;
}
</style>

    {{-- Top nav --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <a href="{{ route('employees.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Employee List
        </a>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('employees.edit', $employee) }}"
               style="padding:8px 18px; background:#fef3c7; color:#92400e; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600;">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST" action="{{ route('employees.archive', $employee) }}"
                  data-confirm="This employee will be moved to the archive."
                  data-confirm-title="Archive Employee?"
                  data-confirm-icon="warning"
                  data-confirm-btn="Yes, archive">
                @csrf @method('PATCH')
                <button style="padding:8px 18px; background:#fecaca; color:#991b1b; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600;">
                    <i class="fas fa-archive"></i> Archive
                </button>
            </form>
        </div>
    </div>

    {{-- Profile Header --}}
    <div class="card" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
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
            <h2 style="margin:0 0 4px; font-size:22px;">{{ $employee->full_name }}</h2>
            <p style="margin:0; color:#6b7280;">{{ $employee->position }} — {{ $employee->department }}</p>
            <span style="display:inline-block; margin-top:6px; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;
                {{ $employee->employment_status === 'Regular'      ? 'background:#d1fae5; color:#065f46;'  : '' }}
                {{ $employee->employment_status === 'Probationary' ? 'background:#fef3c7; color:#92400e;'  : '' }}
                {{ $employee->employment_status === 'Contractual'  ? 'background:#dbeafe; color:#1e40af;'  : '' }}
                {{ $employee->employment_status === 'Part-time'    ? 'background:#f3f4f6; color:#374151;'  : '' }}
            ">{{ $employee->employment_status }}</span>
        </div>
    </div>

    {{-- Info grid: stacks to 1 col on mobile --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">

        {{-- Personal Info --}}
        <div class="card">
            <h2><i class="fas fa-user" style="color:#dc2626;"></i> Personal Information</h2>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                @foreach([
                    ['Employee ID', $employee->employee_id],
                    ['Birthdate',   $employee->birthdate->format('F d, Y')],
                    ['Gender',      $employee->gender],
                    ['Civil Status',$employee->civil_status],
                    ['Contact No.', $employee->contact_number],
                    ['Email',       $employee->email],
                    ['Address',     $employee->address],
                ] as [$label, $value])
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:10px 0; color:#6b7280; width:40%; vertical-align:top;">{{ $label }}</td>
                    <td style="padding:10px 0; font-weight:600; color:#1f2937; word-break:break-word;">{{ $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        {{-- Employment Details --}}
        <div class="card">
            <h2><i class="fas fa-briefcase" style="color:#dc2626;"></i> Employment Details</h2>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                @foreach([
                    ['Department',  $employee->department],
                    ['Position',    $employee->position],
                    ['Date Hired',  $employee->date_hired->format('F d, Y')],
                    ['Salary Type', $employee->salary_type],
                ] as [$label, $value])
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:10px 0; color:#6b7280; width:40%;">{{ $label }}</td>
                    <td style="padding:10px 0; font-weight:600; color:#1f2937;">{{ $value }}</td>
                </tr>
                @endforeach
                <tr>
                    <td style="padding:10px 0; color:#6b7280;">Basic Salary</td>
                    <td style="padding:10px 0; font-weight:700; color:#dc2626; font-size:16px;">₱{{ number_format($employee->basic_salary, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- Payroll Input Summary --}}
        <div class="card" style="grid-column: span 2;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin:0;"><i class="fas fa-clock" style="color:#dc2626;"></i> Payroll Input Summary</h2>
                @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                    @php
                        $payrollInput = $employee->latestPayrollInput();
                    @endphp
                    @if($payrollInput)
                        <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollInput->payrollPeriod, $employee]) }}"
                           style="padding:6px 12px; background:#dbeafe; color:#1e40af; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @else
                        <a href="{{ route('manual-payroll-attendance.index') }}"
                           style="padding:6px 12px; background:#d1fae5; color:#065f46; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;">
                            <i class="fas fa-plus"></i> Add Payroll Input
                        </a>
                    @endif
                @endif
            </div>
            <div style="background:#f9fafb; padding:20px; border-radius:8px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:16px;">
                    @php
                        $payrollInput = $employee->latestPayrollInput();
                        $daysWorked = $payrollInput->days_worked ?? 0;
                        $overtimeHours = $payrollInput->overtime_hours ?? 0;
                        $lateHours = $payrollInput->late_hours ?? 0;
                        $allowances = $payrollInput->allowances ?? 0;
                        $deductions = $payrollInput->deductions ?? 0;
                        $grossPay = $payrollInput->gross_pay ?? 0;
                        $netPay = $payrollInput->net_pay ?? 0;
                        $period = $payrollInput ? $payrollInput->payrollPeriod : null;
                    @endphp

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Days Worked</div>
                        <div style="font-size:20px; font-weight:700; color:#1f2937;">{{ number_format($daysWorked, 1) }} Days</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Overtime Hours</div>
                        <div style="font-size:20px; font-weight:700; color:#dc2626;">{{ number_format($overtimeHours, 1) }} Hours</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Late Hours</div>
                        <div style="font-size:20px; font-weight:700; color:#dc2626;">{{ number_format($lateHours, 1) }} Hour{{ $lateHours != 1 ? 's' : '' }}</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Allowances</div>
                        <div style="font-size:20px; font-weight:700; color:#10b981;">₱{{ number_format($allowances, 2) }}</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Deductions</div>
                        <div style="font-size:20px; font-weight:700; color:#dc2626;">₱{{ number_format($deductions, 2) }}</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Net Pay</div>
                        <div style="font-size:20px; font-weight:700; color:#10b981;">₱{{ number_format($netPay, 2) }}</div>
                    </div>
                </div>

                @if($payrollInput && $period)
                    <div style="margin-top:16px; padding:12px; background:#dbeafe; border-radius:6px; font-size:12px; color:#1e40af; text-align:center;">
                        <i class="fas fa-info-circle"></i> Showing payroll input for period: {{ $period->cutoff_start->format('M d') }} - {{ $period->cutoff_end->format('M d, Y') }}
                    </div>
                @else
                    <div style="margin-top:16px; padding:12px; background:#fef3c7; border-radius:6px; font-size:12px; color:#92400e; text-align:center;">
                        <i class="fas fa-info-circle"></i> No payroll input data found
                    </div>
                @endif
            </div>
        </div>

        {{-- Government Contributions Link (full-width) --}}
        <div class="card" style="grid-column: 1 / -1;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="margin:0;"><i class="fas fa-id-card" style="color:#dc2626;"></i> Government Contributions</h2>
                    <p style="margin:4px 0 0 0; color:#6b7280; font-size:13px;">View and manage government contribution rates for this employee.</p>
                </div>
                <a href="{{ route('government-contributions.show', $employee) }}"
                   style="padding:8px 16px; background:#dbeafe; color:#1e40af; border-radius:6px; text-decoration:none; font-size:13px; font-weight:600;">
                    <i class="fas fa-eye"></i> View Contributions
                </a>
            </div>
        </div>

        {{-- Allowances and Benefits (admin and HR only) --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isHR())
        <div class="card" style="grid-column: 1 / -1;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin:0;"><i class="fas fa-gift" style="color:#dc2626;"></i> Allowances & Benefits</h2>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="var form = document.getElementById('allowanceForm'); var other = document.getElementById('benefitForm'); if(form) { form.classList.remove('hidden-form'); } if(other) { other.classList.add('hidden-form'); }"
                            style="padding:6px 12px; background:#d1fae5; color:#065f46; border:none; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600;">
                        <i class="fas fa-plus"></i> Add Allowance
                    </button>
                    <button type="button" onclick="var form = document.getElementById('benefitForm'); var other = document.getElementById('allowanceForm'); if(form) { form.classList.remove('hidden-form'); } if(other) { other.classList.add('hidden-form'); }"
                            style="padding:6px 12px; background:#dbeafe; color:#1e40af; border:none; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600;">
                        <i class="fas fa-plus"></i> Add Benefit
                    </button>
                </div>
            </div>

            {{-- Allowances Section --}}
            <div style="margin-bottom:24px;">
                <h3 style="margin:0 0 12px 0; font-size:16px; color:#1f2937;">Allowances</h3>
                @if($employee->activeAllowances()->count() > 0)
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                        @foreach($employee->activeAllowances as $allowance)
                            <div style="background:#f9fafb; padding:12px; border-radius:6px; border-left:3px solid #10b981;">
                                <div style="display:flex; justify-content:space-between; align-items:start;">
                                    <div>
                                        <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $allowance->name }}</div>
                                        <div style="color:#6b7280; font-size:12px; margin-top:2px;">{{ $allowance->type }}</div>
                                        @if($allowance->description)
                                            <div style="color:#9ca3af; font-size:11px; margin-top:4px;">{{ $allowance->description }}</div>
                                        @endif
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-weight:700; color:#10b981; font-size:16px;">₱{{ number_format($allowance->amount, 2) }}</div>
                                        <form method="POST" action="{{ route('allowances.destroy', [$employee, $allowance]) }}"
                                              style="margin-top:4px;"
                                              onsubmit="return confirm('Are you sure you want to delete this allowance?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="padding:2px 6px; background:#fecaca; color:#991b1b; border:none; border-radius:4px; cursor:pointer; font-size:10px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:12px; background:#f9fafb; border-radius:6px; text-align:center; color:#9ca3af; font-size:13px;">
                        No allowances added
                    </div>
                @endif
            </div>

            {{-- Benefits Section --}}
            <div>
                <h3 style="margin:0 0 12px 0; font-size:16px; color:#1f2937;">Benefits</h3>
                @if($employee->activeBenefits()->count() > 0)
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                        @foreach($employee->activeBenefits() as $benefit)
                            <div style="background:#f9fafb; padding:12px; border-radius:6px; border-left:3px solid #3b82f6;">
                                <div style="display:flex; justify-content:space-between; align-items:start;">
                                    <div>
                                        <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $benefit->name }}</div>
                                        <div style="color:#6b7280; font-size:12px; margin-top:2px;">{{ $benefit->type }}</div>
                                        @if($benefit->description)
                                            <div style="color:#9ca3af; font-size:11px; margin-top:4px;">{{ $benefit->description }}</div>
                                        @endif
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-weight:700; color:#3b82f6; font-size:16px;">₱{{ number_format($benefit->amount, 2) }}</div>
                                        <form method="POST" action="{{ route('benefits.destroy', [$employee, $benefit]) }}"
                                              style="margin-top:4px;"
                                              onsubmit="return confirm('Are you sure you want to delete this benefit?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="padding:2px 6px; background:#fecaca; color:#991b1b; border:none; border-radius:4px; cursor:pointer; font-size:10px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:12px; background:#f9fafb; border-radius:6px; text-align:center; color:#9ca3af; font-size:13px;">
                        No benefits added
                    </div>
                @endif
            </div>

            {{-- Add Allowance Form (hidden by default) --}}
            <div id="allowanceForm" style="margin-top:20px; padding:16px; background:#f0fdf4; border-radius:8px; border:1px solid #bbf7d0;">
                <h4 style="margin:0 0 12px 0; font-size:14px; color:#065f46;">Add Allowance</h4>
                <form method="POST" action="{{ route('allowances.store', $employee) }}">
                    @csrf
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Name</label>
                            <input type="text" name="name" required
                                   style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0" required
                                   style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Type</label>
                            <select name="type" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                                <option value="monthly">Monthly</option>
                                <option value="one-time">One-time</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Description (optional)</label>
                        <textarea name="description" rows="2"
                                  style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; resize:vertical;"></textarea>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" style="padding:8px 16px; background:#10b981; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600;">
                            <i class="fas fa-save"></i> Save Allowance
                        </button>
                        <button type="button" onclick="document.getElementById('allowanceForm').classList.add('hidden-form');"
                                style="padding:8px 16px; background:#9ca3af; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13px;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Add Benefit Form (hidden by default) --}}
            <div id="benefitForm" style="margin-top:20px; padding:16px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe;">
                <h4 style="margin:0 0 12px 0; font-size:14px; color:#1e40af;">Add Benefit</h4>
                <form method="POST" action="{{ route('benefits.store', $employee) }}">
                    @csrf
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Name</label>
                            <input type="text" name="name" required
                                   style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0" required
                                   style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Type</label>
                            <select name="type" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                                <option value="monthly">Monthly</option>
                                <option value="one-time">One-time</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Description (optional)</label>
                        <textarea name="description" rows="2"
                                  style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; resize:vertical;"></textarea>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" style="padding:8px 16px; background:#3b82f6; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600;">
                            <i class="fas fa-save"></i> Save Benefit
                        </button>
                        <button type="button" onclick="document.getElementById('benefitForm').classList.add('hidden-form');"
                                style="padding:8px 16px; background:#9ca3af; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13px;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>

@endsection
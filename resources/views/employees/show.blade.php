@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')

<style>
.hidden-form { display: none !important; }
</style>

    {{-- Top nav --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <a href="{{ route('employees.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Employee List
        </a>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST" action="{{ route('employees.archive', $employee) }}"
                  data-confirm="This employee will be moved to the archive."
                  data-confirm-title="Archive Employee?"
                  data-confirm-icon="warning"
                  data-confirm-btn="Yes, archive">
                @csrf @method('PATCH')
                <button class="btn btn-danger">
                    <i class="fas fa-archive"></i> Archive
                </button>
            </form>
        </div>
    </div>

    {{-- Profile Header --}}
    <div class="aurora-card" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
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
            <h2 style="margin:0 0 4px; font-size:22px; color:#1a1a2e;">{{ $employee->full_name }}</h2>
            <p style="margin:0; color:#6b7280;">{{ $employee->position }} — {{ $employee->department }}</p>
            <span class="aurora-status {{ $employee->employment_status === 'Regular' ? 'aurora-status-active' : 'aurora-status-inactive' }}"
                  style="margin-top:8px; display:inline-flex;
                      {{ $employee->employment_status === 'Probationary' ? 'background:#fef3c7; border-color:#fde68a; color:#92400e;' : '' }}
                      {{ $employee->employment_status === 'Contractual'  ? 'background:#dbeafe; border-color:#bfdbfe; color:#1e40af;'  : '' }}
                      {{ $employee->employment_status === 'Part-time'    ? 'background:#f3f4f6; border-color:#e5e7eb; color:#374151;'  : '' }}">
                {{ $employee->employment_status }}
            </span>
        </div>
    </div>

    {{-- Info grid --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">

        {{-- Personal Info --}}
        <div class="aurora-card">
            <h2 class="aurora-card-title"><i class="fas fa-user"></i> Personal Information</h2>
            <div class="aurora-info-list">
                @foreach([
                    ['Employee ID', $employee->employee_id],
                    ['Birthdate',   $employee->birthdate->format('F d, Y')],
                    ['Gender',      $employee->gender],
                    ['Civil Status',$employee->civil_status],
                    ['Contact No.', $employee->contact_number],
                    ['Email',       $employee->email],
                    ['Address',     $employee->address],
                ] as [$label, $value])
                <div class="aurora-info-row">
                    <span class="aurora-info-label">{{ $label }}</span>
                    <span class="aurora-info-value">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Employment Details --}}
        <div class="aurora-card">
            <h2 class="aurora-card-title"><i class="fas fa-briefcase"></i> Employment Details</h2>
            <div class="aurora-info-list">
                @foreach([
                    ['Department',  $employee->department],
                    ['Position',    $employee->position],
                    ['Date Hired',  $employee->date_hired->format('F d, Y')],
                    ['Salary Type', $employee->salary_type],
                ] as [$label, $value])
                <div class="aurora-info-row">
                    <span class="aurora-info-label">{{ $label }}</span>
                    <span class="aurora-info-value">{{ $value }}</span>
                </div>
                @endforeach
                <div class="aurora-info-row">
                    <span class="aurora-info-label">Basic Salary</span>
                    <span class="aurora-info-value" style="color:#dc2626; font-size:17px;">₱{{ number_format($employee->basic_salary, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Payroll Input Summary --}}
        <div class="aurora-card" style="grid-column: span 2;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 class="aurora-card-title" style="margin:0;"><i class="fas fa-clock"></i> Payroll Input Summary</h2>
                @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                    @php $payrollInput = $employee->latestPayrollInput(); @endphp
                    @if($payrollInput)
                        <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollInput->payrollPeriod, $employee]) }}"
                           class="btn btn-info btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @else
                        <a href="{{ route('manual-payroll-attendance.index') }}"
                           class="btn btn-sm" style="background:#d1fae5; color:#065f46;">
                            <i class="fas fa-plus"></i> Add Payroll Input
                        </a>
                    @endif
                @endif
            </div>
            <div style="background:#f9fafb; padding:20px; border-radius:12px;">
                @include('employees.partials.payroll-summary', ['employee' => $employee])
            </div>
        </div>

        {{-- Allowances & Benefits --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isHR())
        <div class="aurora-card" style="grid-column: span 2;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
                <h2 class="aurora-card-title" style="margin:0;"><i class="fas fa-gift"></i> Allowances & Benefits</h2>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="btn btn-sm" style="background:#d1fae5; color:#065f46;"
                            onclick="document.getElementById('allowanceForm').classList.toggle('hidden-form');">
                        <i class="fas fa-plus"></i> Add Allowance
                    </button>
                    <button type="button" class="btn btn-info btn-sm"
                            onclick="document.getElementById('benefitForm').classList.toggle('hidden-form');">
                        <i class="fas fa-plus"></i> Add Benefit
                    </button>
                </div>
            </div>

            {{-- Allowances --}}
            <div style="margin-bottom:24px;">
                <h3 style="margin:0 0 12px; font-size:14px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.06em;">Allowances</h3>
                @if($employee->activeAllowances()->count() > 0)
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                        @foreach($employee->activeAllowances() as $allowance)
                            <div style="background:#f9fafb; padding:12px; border-radius:10px; border-left:3px solid #10b981;">
                                <div style="display:flex; justify-content:space-between; align-items:start;">
                                    <div>
                                        <div style="font-weight:600; color:#1a1a2e; font-size:14px;">{{ $allowance->name }}</div>
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
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding:2px 6px; font-size:10px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:12px; background:#f9fafb; border-radius:10px; text-align:center; color:#9ca3af; font-size:13px;">
                        No allowances added
                    </div>
                @endif
            </div>

            {{-- Benefits --}}
            <div>
                <h3 style="margin:0 0 12px; font-size:14px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.06em;">Benefits</h3>
                @if($employee->activeBenefits()->count() > 0)
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                        @foreach($employee->activeBenefits() as $benefit)
                            <div style="background:#f9fafb; padding:12px; border-radius:10px; border-left:3px solid #3b82f6;">
                                <div style="display:flex; justify-content:space-between; align-items:start;">
                                    <div>
                                        <div style="font-weight:600; color:#1a1a2e; font-size:14px;">{{ $benefit->name }}</div>
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
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding:2px 6px; font-size:10px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:12px; background:#f9fafb; border-radius:10px; text-align:center; color:#9ca3af; font-size:13px;">
                        No benefits added
                    </div>
                @endif
            </div>

            {{-- Add Allowance Form (hidden by default) --}}
            <div id="allowanceForm" class="hidden-form" style="margin-top:20px; padding:16px; background:#f0fdf4; border-radius:10px; border:1px solid #bbf7d0;">
                <h4 style="margin:0 0 12px; font-size:14px; color:#065f46;">Add Allowance</h4>
                <form method="POST" action="{{ route('allowances.store', $employee) }}">
                    @csrf
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px;">Name</label>
                            <input type="text" name="name" required
                                   style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px;">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0" required
                                   style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px;">Type</label>
                            <select name="type" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                                <option value="monthly">Monthly</option>
                                <option value="one-time">One-time</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px;">Description (optional)</label>
                        <textarea name="description" rows="2"
                                  style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; resize:vertical;"></textarea>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" class="btn btn-sm" style="background:#10b981; color:white; font-weight:600;">
                            <i class="fas fa-save"></i> Save Allowance
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm"
                                onclick="document.getElementById('allowanceForm').classList.add('hidden-form');">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Add Benefit Form (hidden by default) --}}
            <div id="benefitForm" class="hidden-form" style="margin-top:20px; padding:16px; background:#eff6ff; border-radius:10px; border:1px solid #bfdbfe;">
                <h4 style="margin:0 0 12px; font-size:14px; color:#1e40af;">Add Benefit</h4>
                <form method="POST" action="{{ route('benefits.store', $employee) }}">
                    @csrf
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px;">Name</label>
                            <input type="text" name="name" required
                                   style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px;">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0" required
                                   style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px;">Type</label>
                            <select name="type" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                                <option value="monthly">Monthly</option>
                                <option value="one-time">One-time</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px;">Description (optional)</label>
                        <textarea name="description" rows="2"
                                  style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; resize:vertical;"></textarea>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" class="btn btn-info btn-sm" style="font-weight:600;">
                            <i class="fas fa-save"></i> Save Benefit
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm"
                                onclick="document.getElementById('benefitForm').classList.add('hidden-form');">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>

@endsection
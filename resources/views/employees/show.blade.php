@extends('layouts.app')

@section('title', $employee->full_name)
@section('breadcrumb')
    <a href="{{ route('employees.index') }}" class="text-white/55 no-underline">Manage Employees</a>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <a href="{{ route('employees.index') }}" class="text-white/55 no-underline">Employees</a>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-semibold">{{ $employee->full_name }}</span>
@endsection

@section('content')

    {{-- Top nav --}}
    <div class="flex justify-between items-center flex-wrap gap-3 mb-5">
        <a href="{{ route('employees.index') }}" class="back-link text-gray-500 no-underline text-sm hover:text-emerald-600">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Employee List
        </a>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-soft btn-warning btn-sm">
                <i class="icon-[ph--pencil-fill]"></i> Edit
            </a>
            <form method="POST" action="{{ route('employees.archive', $employee) }}"
                  data-confirm="This employee will be moved to the archive."
                  data-confirm-title="Archive Employee?"
                  data-confirm-icon="warning"
                  data-confirm-btn="Yes, archive">
                @csrf @method('PATCH')
                <button class="btn btn-soft btn-error btn-sm">
                    <i class="icon-[ph--archive-fill]"></i> Archive
                </button>
            </form>
        </div>
    </div>

    {{-- Profile Header --}}
    <div class="card bg-base-100 shadow-sm p-5 flex items-center gap-5 flex-wrap mb-5">
        <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
            @if($employee->user?->profile_photo)
                <img src="{{ asset('storage/' . $employee->user->profile_photo) }}"
                     alt="{{ $employee->full_name }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-2xl font-bold">
                    {{ strtoupper(substr($employee->first_name, 0, 1)) }}
                </div>
            @endif
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800 m-0 mb-1">{{ $employee->full_name }}</h2>
            <p class="text-gray-500 m-0">{{ $employee->position }} — {{ $employee->department }}</p>
            @php
                $statusClass = match($employee->employment_status) {
                    'Regular'      => 'badge-soft badge-success',
                    'Probationary' => 'badge-soft badge-warning',
                    'Contractual'  => 'badge-soft badge-info',
                    'Part-time'    => 'badge-soft badge-neutral',
                    default        => 'badge-soft',
                };
            @endphp
            <span class="badge {{ $statusClass }} mt-2">{{ $employee->employment_status }}</span>
        </div>
    </div>

    {{-- Info grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Personal Info --}}
        <div class="card bg-base-100 shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="icon-[ph--user-fill] text-red-600"></i> Personal Information
            </h2>
            <div class="flex flex-col text-sm">
                @foreach([
                    ['Employee ID', $employee->employee_id],
                    ['Birthdate',   $employee->birthdate->format('F d, Y')],
                    ['Gender',      $employee->gender],
                    ['Civil Status',$employee->civil_status],
                    ['Contact No.', $employee->contact_number],
                    ['Email',       $employee->email],
                    ['Address',     $employee->address],
                ] as [$label, $value])
                    <div class="flex justify-between items-start py-2 border-b border-base-200 gap-4">
                        <span class="text-gray-400 w-2/5 flex-shrink-0">{{ $label }}</span>
                        <span class="font-semibold text-gray-800 text-right break-words">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Employment Details --}}
        <div class="card bg-base-100 shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="icon-[ph--briefcase-fill] text-red-600"></i> Employment Details
            </h2>
            <div class="flex flex-col text-sm">
                @foreach([
                    ['Department',  $employee->department],
                    ['Position',    $employee->position],
                    ['Date Hired',  $employee->date_hired->format('F d, Y')],
                    ['Salary Type', $employee->salary_type],
                ] as [$label, $value])
                    <div class="flex justify-between items-center py-2 border-b border-base-200">
                        <span class="text-gray-400">{{ $label }}</span>
                        <span class="font-semibold text-gray-800">{{ $value }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-400">Basic Salary</span>
                    <span class="font-bold text-red-600 text-base">₱{{ number_format($employee->basic_salary, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Payroll Input Summary --}}
        <div class="card bg-base-100 shadow-sm p-5 md:col-span-2">
            @php
                $payrollInput  = $employee->latestPayrollInput();
                $daysWorked    = $payrollInput->days_worked ?? 0;
                $overtimeHours = $payrollInput->overtime_hours ?? 0;
                $lateHours     = $payrollInput->late_hours ?? 0;
                $allowances    = $payrollInput->allowances ?? 0;
                $deductions    = $payrollInput->deductions ?? 0;
                $netPay        = $payrollInput->net_pay ?? 0;
                $period        = $payrollInput ? $payrollInput->payrollPeriod : null;
            @endphp
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-sm font-bold text-gray-800 m-0 flex items-center gap-2">
                    <i class="icon-[ph--clock-fill] text-red-600"></i> Payroll Input Summary
                </h2>
                @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                    @if($payrollInput)
                        <a href="{{ route('manual-payroll-attendance.employee-form', [$payrollInput->payrollPeriod, $employee]) }}"
                           class="btn btn-soft btn-info btn-xs">
                            <i class="icon-[ph--pencil-fill]"></i> Edit
                        </a>
                    @else
                        <a href="{{ route('manual-payroll-attendance.index') }}"
                           class="btn btn-soft btn-success btn-xs">
                            <i class="icon-[ph--plus-fill]"></i> Add Payroll Input
                        </a>
                    @endif
                @endif
            </div>
            <div class="bg-gray-50 rounded-xl p-5">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-center">
                    @foreach([
                        ['Days Worked',    number_format($daysWorked, 1).' Days',   'text-gray-800'],
                        ['Overtime Hours', number_format($overtimeHours, 1).' Hrs', 'text-red-600'],
                        ['Late Hours',     number_format($lateHours, 1).' Hrs',     'text-red-600'],
                        ['Allowances',     '₱'.number_format($allowances, 2),       'text-emerald-600'],
                        ['Deductions',     '₱'.number_format($deductions, 2),       'text-red-600'],
                        ['Net Pay',        '₱'.number_format($netPay, 2),           'text-emerald-600'],
                    ] as [$label, $value, $cls])
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ $label }}</div>
                            <div class="text-lg font-bold {{ $cls }}">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>
                @if($payrollInput && $period)
                    <div class="mt-4 px-4 py-2 bg-blue-50 rounded-lg text-xs text-blue-700 text-center">
                        <i class="icon-[ph--info-fill]"></i>
                        Showing payroll input for period: {{ $period->cutoff_start->format('M d') }} - {{ $period->cutoff_end->format('M d, Y') }}
                    </div>
                @else
                    <div class="mt-4 px-4 py-2 bg-amber-50 rounded-lg text-xs text-amber-700 text-center">
                        <i class="icon-[ph--info-fill]"></i> No payroll input data found
                    </div>
                @endif
            </div>
        </div>

        {{-- Government Contributions --}}
        <div class="card bg-base-100 shadow-sm p-5 md:col-span-2">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold text-gray-800 m-0 flex items-center gap-2">
                        <i class="icon-[ph--identification-card-fill] text-red-600"></i> Government Contributions
                    </h2>
                    <p class="text-gray-500 text-xs mt-1 mb-0">View and manage government contribution rates for this employee.</p>
                </div>
                <a href="{{ route('government-contributions.show', $employee) }}" class="btn btn-soft btn-info btn-sm">
                    <i class="icon-[ph--eye-fill]"></i> View Contributions
                </a>
            </div>
        </div>

        {{-- Attendance Records Link (full-width) --}}
        <div class="card bg-base-100 shadow-sm p-5 md:col-span-2">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-bold text-gray-800 m-0 flex items-center gap-2">
                        <i class="icon-[ph--clock-fill] text-red-600"></i> Attendance Records
                    </h2>
                    <p class="text-gray-500 text-xs mt-1 mb-0">View daily time-in/time-out records and attendance history for this employee.</p>
                </div>
                <a href="{{ route('employee-attendance.show-employee', $employee) }}" class="btn btn-soft btn-success btn-sm">
                    <i class="icon-[ph--eye-fill]"></i> View Attendance
                </a>
            </div>
        </div>

        {{-- Allowances and Benefits (admin and HR only) --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isHR())
        <div class="card bg-base-100 shadow-sm p-5 md:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-sm font-bold text-gray-800 m-0 flex items-center gap-2">
                    <i class="icon-[ph--gift-fill] text-red-600"></i> Allowances & Benefits
                </h2>
                <div class="flex gap-2">
                    <button type="button"
                            id="showAllowanceBtn"
                            class="btn btn-soft btn-success btn-xs">
                        <i class="icon-[ph--plus-fill]"></i> Add Allowance
                    </button>
                    <button type="button"
                            id="showBenefitBtn"
                            class="btn btn-soft btn-info btn-xs">
                        <i class="icon-[ph--plus-fill]"></i> Add Benefit
                    </button>
                </div>
            </div>

            {{-- Allowances --}}
            <div class="mb-6">
                <h3 class="text-sm font-bold text-gray-700 mb-3">Allowances</h3>
                @if($employee->activeAllowances()->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($employee->activeAllowances as $allowance)
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-emerald-500">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-semibold text-gray-800 text-sm">{{ $allowance->name }}</div>
                                        <div class="text-gray-500 text-xs mt-0.5">{{ $allowance->type }}</div>
                                        @if($allowance->description)
                                            <div class="text-gray-400 text-xs mt-1">{{ $allowance->description }}</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-emerald-600 text-sm">₱{{ number_format($allowance->amount, 2) }}</div>
                                        <form method="POST" action="{{ route('allowances.destroy', [$employee, $allowance]) }}"
                                              class="mt-1"
                                              data-confirm="This allowance will be permanently deleted."
                                              data-confirm-title="Delete Allowance?"
                                              data-confirm-btn="Yes, delete it">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-soft btn-error btn-xs">
                                                <i class="icon-[ph--trash-fill]"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-3 bg-gray-50 rounded-lg text-center text-gray-400 text-xs">No allowances added</div>
                @endif
            </div>

            {{-- Benefits --}}
            <div class="mb-4">
                <h3 class="text-sm font-bold text-gray-700 mb-3">Benefits</h3>
                @if($employee->activeBenefits->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($employee->activeBenefits as $benefit)
                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-blue-500">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-semibold text-gray-800 text-sm">{{ $benefit->name }}</div>
                                        <div class="text-gray-500 text-xs mt-0.5">{{ $benefit->type }}</div>
                                        @if($benefit->description)
                                            <div class="text-gray-400 text-xs mt-1">{{ $benefit->description }}</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-blue-600 text-sm">₱{{ number_format($benefit->amount, 2) }}</div>
                                        <form method="POST" action="{{ route('benefits.destroy', [$employee, $benefit]) }}"
                                              class="mt-1"
                                              data-confirm="This benefit will be permanently deleted."
                                              data-confirm-title="Delete Benefit?"
                                              data-confirm-btn="Yes, delete it">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-soft btn-error btn-xs">
                                                <i class="icon-[ph--trash-fill]"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-3 bg-gray-50 rounded-lg text-center text-gray-400 text-xs">No benefits added</div>
                @endif
            </div>

            {{-- Add Allowance Form --}}
            <div id="allowanceForm" class="mt-4 p-4 bg-emerald-50 rounded-xl border border-emerald-200" style="display: none;">
                <h4 class="text-sm font-bold text-emerald-800 mb-3">Add Allowance</h4>
                <form method="POST" action="{{ route('allowances.store', $employee) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <div class="fieldset">
                            <label class="label text-xs text-gray-500">Name</label>
                            <input type="text" name="name" required class="input input-bordered input-sm w-full">
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs text-gray-500">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0" required class="input input-bordered input-sm w-full">
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs text-gray-500">Type</label>
                            <select name="type" class="select select-bordered select-sm w-full">
                                <option value="monthly">Monthly</option>
                                <option value="one-time">One-time</option>
                            </select>
                        </div>
                    </div>
                    <div class="fieldset mb-3">
                        <label class="label text-xs text-gray-500">Description (optional)</label>
                        <textarea name="description" rows="2" class="textarea textarea-bordered textarea-sm w-full"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-soft btn-success btn-sm">
                            <i class="icon-[ph--floppy-disk-fill]"></i> Save Allowance
                        </button>
                        <button type="button"
                                id="hideAllowanceBtn"
                                class="btn btn-soft btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Add Benefit Form --}}
            <div id="benefitForm" class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-200" style="display: none;">
                <h4 class="text-sm font-bold text-blue-800 mb-3">Add Benefit</h4>
                <form method="POST" action="{{ route('benefits.store', $employee) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <div class="fieldset">
                            <label class="label text-xs text-gray-500">Name</label>
                            <input type="text" name="name" required class="input input-bordered input-sm w-full">
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs text-gray-500">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0" required class="input input-bordered input-sm w-full">
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs text-gray-500">Type</label>
                            <select name="type" class="select select-bordered select-sm w-full">
                                <option value="monthly">Monthly</option>
                                <option value="one-time">One-time</option>
                            </select>
                        </div>
                    </div>
                    <div class="fieldset mb-3">
                        <label class="label text-xs text-gray-500">Description (optional)</label>
                        <textarea name="description" rows="2" class="textarea textarea-bordered textarea-sm w-full"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-soft btn-info btn-sm">
                            <i class="icon-[ph--floppy-disk-fill]"></i> Save Benefit
                        </button>
                        <button type="button"
                                id="hideBenefitBtn"
                                class="btn btn-soft btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

        </div>
        @endif

    </div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper function to get visible element (handles duplicate IDs across mobile/desktop layouts)
    const getVisibleElement = (id) => {
        const elements = document.querySelectorAll('[id="' + id + '"]');
        for (let element of elements) {
            if (element.offsetParent !== null) {
                return element;
            }
        }
        // Fallback: prefer desktop layout element
        const desktopElement = document.querySelector('.desktop-layout [id="' + id + '"]');
        if (desktopElement) return desktopElement;
        return elements[0]; // Final fallback
    };

    const showAllowanceBtn = getVisibleElement('showAllowanceBtn');
    const showBenefitBtn = getVisibleElement('showBenefitBtn');
    const hideAllowanceBtn = getVisibleElement('hideAllowanceBtn');
    const hideBenefitBtn = getVisibleElement('hideBenefitBtn');
    const allowanceForm = getVisibleElement('allowanceForm');
    const benefitForm = getVisibleElement('benefitForm');

    if (showAllowanceBtn && allowanceForm && benefitForm) {
        showAllowanceBtn.addEventListener('click', function(e) {
            e.preventDefault();
            allowanceForm.style.display = 'block';
            allowanceForm.style.visibility = 'visible';
            benefitForm.style.display = 'none';
            benefitForm.style.visibility = 'hidden';
        });
    }

    if (showBenefitBtn && allowanceForm && benefitForm) {
        showBenefitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            benefitForm.style.display = 'block';
            benefitForm.style.visibility = 'visible';
            allowanceForm.style.display = 'none';
            allowanceForm.style.visibility = 'hidden';
        });
    }

    if (hideAllowanceBtn && allowanceForm) {
        hideAllowanceBtn.addEventListener('click', function(e) {
            e.preventDefault();
            allowanceForm.style.display = 'none';
            allowanceForm.style.visibility = 'hidden';
        });
    }

    if (hideBenefitBtn && benefitForm) {
        hideBenefitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            benefitForm.style.display = 'none';
            benefitForm.style.visibility = 'hidden';
        });
    }
});
</script>
@endsection

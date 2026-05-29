@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')

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
        <div style="width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:28px; font-weight:700; flex-shrink:0;">
            {{ strtoupper(substr($employee->first_name, 0, 1)) }}
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

        {{-- Attendance Summary --}}
        <div class="card" style="grid-column: span 2;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin:0;"><i class="fas fa-clock" style="color:#dc2626;"></i> Attendance Summary</h2>
                @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                    @php
                        $attendance = $employee->currentAttendance();
                    @endphp
                    @if($attendance)
                        <a href="{{ route('attendance.edit', [$employee, $attendance]) }}"
                           style="padding:6px 12px; background:#dbeafe; color:#1e40af; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @else
                        <a href="{{ route('attendance.create', $employee) }}"
                           style="padding:6px 12px; background:#d1fae5; color:#065f46; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;">
                            <i class="fas fa-plus"></i> Add Attendance
                        </a>
                    @endif
                @endif
            </div>
            <div style="background:#f9fafb; padding:20px; border-radius:8px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:16px;">
                    @php
                        $attendance = $employee->currentAttendance();
                        $daysWorked = $attendance->days_worked ?? 0;
                        $regularHours = $attendance->regular_hours ?? 0;
                        $overtimeHours = $attendance->overtime_hours ?? 0;
                        $lateHours = $attendance->late_hours ?? 0;
                        $nightDifferentialHours = $attendance->night_differential_hours ?? 0;
                        $regularHolidayWorked = $attendance->regular_holiday_worked ?? 0;
                    @endphp

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Days Worked</div>
                        <div style="font-size:20px; font-weight:700; color:#1f2937;">{{ $daysWorked }} Days</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Regular Hours</div>
                        <div style="font-size:20px; font-weight:700; color:#1f2937;">{{ $regularHours }} Hours</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Overtime Hours</div>
                        <div style="font-size:20px; font-weight:700; color:#dc2626;">{{ $overtimeHours }} Hours</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Late Hours</div>
                        <div style="font-size:20px; font-weight:700; color:#dc2626;">{{ $lateHours }} Hour{{ $lateHours != 1 ? 's' : '' }}</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Night Diff Hours</div>
                        <div style="font-size:20px; font-weight:700; color:#1f2937;">{{ $nightDifferentialHours }} Hours</div>
                    </div>

                    <div style="text-align:center;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Regular Holiday</div>
                        <div style="font-size:20px; font-weight:700; color:#1f2937;">{{ $regularHolidayWorked }} Day{{ $regularHolidayWorked != 1 ? 's' : '' }}</div>
                    </div>
                </div>

                @if(!$attendance)
                    <div style="margin-top:16px; padding:12px; background:#fef3c7; border-radius:6px; font-size:12px; color:#92400e; text-align:center;">
                        <i class="fas fa-info-circle"></i> No attendance data for {{ date('F Y') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Government Contributions (full-width) --}}
        <div class="card" style="grid-column: 1 / -1;">
            <h2><i class="fas fa-id-card" style="color:#dc2626;"></i> Government Contributions</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:16px;">
                @foreach([
                    ['SSS Number',  $employee->sss_number,       'fa-shield-alt'],
                    ['PhilHealth',  $employee->philhealth_number, 'fa-heart'],
                    ['Pag-IBIG',    $employee->pagibig_number,    'fa-home'],
                    ['TIN Number',  $employee->tin_number,        'fa-file-invoice'],
                ] as [$label, $value, $icon])
                <div style="background:#f9fafb; padding:16px; border-radius:8px; text-align:center;">
                    <div style="color:#dc2626; font-size:20px; margin-bottom:8px;"><i class="fas {{ $icon }}"></i></div>
                    <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">{{ $label }}</div>
                    <div style="font-weight:600; font-family:monospace; color:#1f2937; font-size:13px; word-break:break-all;">{{ $value ?? '—' }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

@endsection
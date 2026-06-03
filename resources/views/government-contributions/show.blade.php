@extends('layouts.app')

@section('title', $employee->full_name . ' - Government Contributions')

@section('content')

    {{-- Top nav --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <a href="{{ route('government-contributions.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Government Contributions
        </a>
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
            <p style="margin:4px 0 0; color:#1f2937; font-weight:600; font-size:14px;">Basic Salary: ₱{{ number_format($employee->basic_salary, 2) }}</p>
            <span style="display:inline-block; margin-top:6px; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;
                {{ $employee->employment_status === 'Regular'      ? 'background:#d1fae5; color:#065f46;'  : '' }}
                {{ $employee->employment_status === 'Probationary' ? 'background:#fef3c7; color:#92400e;'  : '' }}
                {{ $employee->employment_status === 'Contractual'  ? 'background:#dbeafe; color:#1e40af;'  : '' }}
                {{ $employee->employment_status === 'Part-time'    ? 'background:#f3f4f6; color:#374151;'  : '' }}
            ">{{ $employee->employment_status }}</span>
        </div>
    </div>

    {{-- Government Contributions --}}
    <div class="card" style="margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="margin:0;"><i class="fas fa-id-card" style="color:#dc2626;"></i> Government Contributions</h2>
        </div>
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

        {{-- SSS Contribution Breakdown --}}
        <div style="margin-top:24px; padding:20px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe;">
            <h4 style="margin:0 0 16px 0; font-size:14px; color:#1e40af; font-weight:600;">
                <i class="fas fa-calculator"></i> SSS Contribution (Circular No. 2024-006)
            </h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Monthly Salary Credit</div>
                    <div style="font-weight:700; color:#1f2937; font-size:16px;">₱{{ number_format($sssContribution['salary_credit'], 2) }}</div>
                </div>
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Employee Share</div>
                    <div style="font-weight:700; color:#dc2626; font-size:16px;">₱{{ number_format($sssContribution['employee_share'], 2) }}</div>
                </div>
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Total Contribution</div>
                    <div style="font-weight:700; color:#1f2937; font-size:16px;">₱{{ number_format($sssContribution['total'], 2) }}</div>
                </div>
            </div>
        </div>

        {{-- PhilHealth Contribution Breakdown --}}
        <div style="margin-top:24px; padding:20px; background:#ecfdf5; border-radius:8px; border:1px solid #a7f3d0;">
            <h4 style="margin:0 0 16px 0; font-size:14px; color:#065f46; font-weight:600;">
                <i class="fas fa-heartbeat"></i> PhilHealth Contribution (2025/2026)
            </h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Salary Basis</div>
                    <div style="font-weight:700; color:#1f2937; font-size:16px;">₱{{ number_format($philHealthContribution['salary_basis'], 2) }}</div>
                </div>
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Employee Rate</div>
                    <div style="font-weight:700; color:#1f2937; font-size:16px;">{{ number_format($philHealthContribution['employee_rate'] * 100, 1) }}%</div>
                </div>
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Employee Share</div>
                    <div style="font-weight:700; color:#dc2626; font-size:16px;">₱{{ number_format($philHealthContribution['employee_share'], 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Pag-IBIG Contribution Breakdown --}}
        <div style="margin-top:24px; padding:20px; background:#fef3c7; border-radius:8px; border:1px solid #fcd34d;">
            <h4 style="margin:0 0 16px 0; font-size:14px; color:#92400e; font-weight:600;">
                <i class="fas fa-home"></i> Pag-IBIG Contribution (2026)
            </h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Monthly Salary</div>
                    <div style="font-weight:700; color:#1f2937; font-size:16px;">₱{{ number_format($pagIbigContribution['salary'], 2) }}</div>
                </div>
                @if($pagIbigContribution['employee_rate'] !== null)
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Employee Rate</div>
                    <div style="font-weight:700; color:#1f2937; font-size:16px;">{{ number_format($pagIbigContribution['employee_rate'] * 100, 1) }}%</div>
                </div>
                @endif
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Employee Share</div>
                    <div style="font-weight:700; color:#dc2626; font-size:16px;">₱{{ number_format($pagIbigContribution['employee_share'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

@endsection

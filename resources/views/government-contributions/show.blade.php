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

        {{-- Government Contribution Rates Form (HR/Admin only) --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isHR())
        <div id="govContribForm" style="margin-top:20px; padding:20px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe;">
            <h4 style="margin:0 0 16px 0; font-size:14px; color:#1e40af;">Edit Government Contribution Rates</h4>
            <form method="POST" action="{{ route('government-contributions.update', $employee) }}">
                @csrf @method('PATCH')
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:16px;">
                    {{-- SSS --}}
                    <div style="background:#f9fafb; padding:12px; border-radius:6px;">
                        <h5 style="margin:0 0 8px 0; font-size:13px; color:#1f2937; font-weight:600;">SSS</h5>
                        <div style="margin-bottom:8px;">
                            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:4px;">Rate (%)</label>
                            <input type="number" name="sss_rate" step="0.0001" min="0" max="1" value="{{ $employee->sss_rate }}"
                                   style="width:100%; padding:6px; border:1px solid #d1d5db; border-radius:4px; font-size:12px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:4px;">Cap (₱)</label>
                            <input type="number" name="sss_cap" step="0.01" min="0" value="{{ $employee->sss_cap }}"
                                   style="width:100%; padding:6px; border:1px solid #d1d5db; border-radius:4px; font-size:12px;">
                        </div>
                    </div>

                    {{-- PhilHealth --}}
                    <div style="background:#f9fafb; padding:12px; border-radius:6px;">
                        <h5 style="margin:0 0 8px 0; font-size:13px; color:#1f2937; font-weight:600;">PhilHealth</h5>
                        <div style="margin-bottom:8px;">
                            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:4px;">Rate (%)</label>
                            <input type="number" name="philhealth_rate" step="0.0001" min="0" max="1" value="{{ $employee->philhealth_rate }}"
                                   style="width:100%; padding:6px; border:1px solid #d1d5db; border-radius:4px; font-size:12px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:4px;">Cap (₱)</label>
                            <input type="number" name="philhealth_cap" step="0.01" min="0" value="{{ $employee->philhealth_cap }}"
                                   style="width:100%; padding:6px; border:1px solid #d1d5db; border-radius:4px; font-size:12px;">
                        </div>
                    </div>

                    {{-- Pag-IBIG --}}
                    <div style="background:#f9fafb; padding:12px; border-radius:6px;">
                        <h5 style="margin:0 0 8px 0; font-size:13px; color:#1f2937; font-weight:600;">Pag-IBIG</h5>
                        <div style="margin-bottom:8px;">
                            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:4px;">Rate (%)</label>
                            <input type="number" name="pagibig_rate" step="0.0001" min="0" max="1" value="{{ $employee->pagibig_rate }}"
                                   style="width:100%; padding:6px; border:1px solid #d1d5db; border-radius:4px; font-size:12px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:4px;">Cap (₱)</label>
                            <input type="number" name="pagibig_cap" step="0.01" min="0" value="{{ $employee->pagibig_cap }}"
                                   style="width:100%; padding:6px; border:1px solid #d1d5db; border-radius:4px; font-size:12px;">
                        </div>
                    </div>

                    {{-- Withholding Tax --}}
                    <div style="background:#f9fafb; padding:12px; border-radius:6px;">
                        <h5 style="margin:0 0 8px 0; font-size:13px; color:#1f2937; font-weight:600;">Withholding Tax</h5>
                        <div>
                            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:4px;">Rate (%)</label>
                            <input type="number" name="withholding_tax_rate" step="0.0001" min="0" max="1" value="{{ $employee->withholding_tax_rate ?? 0.0000 }}"
                                   style="width:100%; padding:6px; border:1px solid #d1d5db; border-radius:4px; font-size:12px;">
                        </div>
                        <div style="margin-top:8px; font-size:10px; color:#6b7280;">
                            Note: 0.0000 = Use standard Philippine tax brackets
                        </div>
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" style="padding:8px 16px; background:#3b82f6; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

@endsection

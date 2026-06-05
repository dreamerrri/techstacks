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
        <div style="margin-bottom:16px;">
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

        {{-- SSS --}}
        <div style="margin-top:24px; padding:20px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe;">
            <h4 style="margin:0 0 16px 0; font-size:14px; color:#1e40af; font-weight:600;">
                <i class="fas fa-calculator"></i> SSS Contribution (Circular No. 2024-006)
            </h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Monthly Salary Credit</div>
                    <div style="font-weight:700; color:#1f2937; font-size:16px;">₱{{ number_format($sssContribution['salary_credit'], 2) }}</div>
                </div>

                {{-- Editable: SSS Employee Share --}}
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                        Employee Share
                        <button class="js-edit-btn" data-key="sss" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:12px; padding:0;" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
                    <div class="js-display" data-key="sss" style="font-weight:700; color:#dc2626; font-size:16px;">
                        ₱{{ number_format($sssEmployeeShare, 2) }}
                    </div>
                    <div class="js-edit-form" data-key="sss" style="display:none;">
                        <input class="js-input" data-key="sss" type="number" step="0.01" min="0"
                               value="{{ $sssEmployeeShare }}"
                               style="width:100%; padding:6px; border:1px solid #3b82f6; border-radius:4px; font-size:14px; box-sizing:border-box;">
                        <div style="display:flex; gap:6px; margin-top:6px;">
                            <button class="js-save-btn" data-key="sss" data-field="custom_sss_contribution"
                                    style="flex:1; padding:5px; background:#10b981; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px; font-weight:600;">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="js-cancel-btn" data-key="sss"
                                    style="flex:1; padding:5px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:4px; cursor:pointer; font-size:12px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:4px;">Total Contribution</div>
                    <div style="font-weight:700; color:#1f2937; font-size:16px;">₱{{ number_format($sssContribution['total'], 2) }}</div>
                </div>
            </div>
        </div>

        {{-- PhilHealth --}}
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

                {{-- Editable: PhilHealth Employee Share --}}
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                        Employee Share
                        <button class="js-edit-btn" data-key="philhealth" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:12px; padding:0;" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
                    <div class="js-display" data-key="philhealth" style="font-weight:700; color:#dc2626; font-size:16px;">
                        ₱{{ number_format($philHealthEmployeeShare, 2) }}
                    </div>
                    <div class="js-edit-form" data-key="philhealth" style="display:none;">
                        <input class="js-input" data-key="philhealth" type="number" step="0.01" min="0"
                               value="{{ $philHealthEmployeeShare }}"
                               style="width:100%; padding:6px; border:1px solid #3b82f6; border-radius:4px; font-size:14px; box-sizing:border-box;">
                        <div style="display:flex; gap:6px; margin-top:6px;">
                            <button class="js-save-btn" data-key="philhealth" data-field="custom_philhealth_contribution"
                                    style="flex:1; padding:5px; background:#10b981; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px; font-weight:600;">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="js-cancel-btn" data-key="philhealth"
                                    style="flex:1; padding:5px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:4px; cursor:pointer; font-size:12px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pag-IBIG --}}
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

                {{-- Editable: Pag-IBIG Employee Share --}}
                <div style="background:white; padding:12px; border-radius:6px;">
                    <div style="font-size:11px; color:#6b7280; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                        Employee Share
                        <button class="js-edit-btn" data-key="pagibig" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:12px; padding:0;" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
                    <div class="js-display" data-key="pagibig" style="font-weight:700; color:#dc2626; font-size:16px;">
                        ₱{{ number_format($pagIbigEmployeeShare, 2) }}
                    </div>
                    <div class="js-edit-form" data-key="pagibig" style="display:none;">
                        <input class="js-input" data-key="pagibig" type="number" step="0.01" min="0"
                               value="{{ $pagIbigEmployeeShare }}"
                               style="width:100%; padding:6px; border:1px solid #3b82f6; border-radius:4px; font-size:14px; box-sizing:border-box;">
                        <div style="display:flex; gap:6px; margin-top:6px;">
                            <button class="js-save-btn" data-key="pagibig" data-field="custom_pagibig_contribution"
                                    style="flex:1; padding:5px; background:#10b981; color:white; border:none; border-radius:4px; cursor:pointer; font-size:12px; font-weight:600;">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="js-cancel-btn" data-key="pagibig"
                                    style="flex:1; padding:5px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:4px; cursor:pointer; font-size:12px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>

$(function () {

    const updateUrl = '{{ route('government-contributions.update', $employee) }}';
    const csrfToken = '{{ csrf_token() }}';

    // Track original values so cancel can restore them
    const originals = {
        sss:        {{ $sssEmployeeShare }},
        philhealth: {{ $philHealthEmployeeShare }},
        pagibig:    {{ $pagIbigEmployeeShare }},
    };

    // Open edit mode
    $(document).on('click', '.js-edit-btn', function () {
        const key = $(this).data('key');
        $(`.js-display[data-key="${key}"]`).hide();
        $(`.js-edit-form[data-key="${key}"]`).show();
        $(`.js-input[data-key="${key}"]`).focus();
        $(this).hide();
    });

    // Cancel — restore original value and close
    $(document).on('click', '.js-cancel-btn', function () {
        const key = $(this).data('key');
        $(`.js-input[data-key="${key}"]`).val(originals[key]);
        $(`.js-edit-form[data-key="${key}"]`).hide();
        $(`.js-display[data-key="${key}"]`).show();
        $(`.js-edit-btn[data-key="${key}"]`).show();
    });

    // Save via PATCH
    $(document).on('click', '.js-save-btn', function () {
        const key   = $(this).data('key');
        const field = $(this).data('field');
        const value = parseFloat($(`.js-input[data-key="${key}"]`).val()) || 0;

        const payload = {};
        payload[field] = value;

        $.ajax({
            url:         updateUrl,
            method:      'PATCH',
            contentType: 'application/json',
            headers:     { 'X-CSRF-TOKEN': csrfToken },
            data:        JSON.stringify(payload),
            success: function (res) {
                if (res.success) {
                    originals[key] = value;
                    $(`.js-display[data-key="${key}"]`).text('₱' + value.toFixed(2));
                    // Trigger cancel to close edit UI cleanly
                    $(`.js-cancel-btn[data-key="${key}"]`).trigger('click');
                } else {
                    alert('Error: ' + (res.message || 'Failed to save'));
                }
            },
            error: function (xhr) {
                alert('Request failed: ' + xhr.statusText);
            }
        });
    });

    // Allow pressing Enter in input to save
    $(document).on('keydown', '.js-input', function (e) {
        if (e.key === 'Enter') {
            const key = $(this).data('key');
            $(`.js-save-btn[data-key="${key}"]`).trigger('click');
        }
        if (e.key === 'Escape') {
            const key = $(this).data('key');
            $(`.js-cancel-btn[data-key="${key}"]`).trigger('click');
        }
    });

});
</script>
@endsection
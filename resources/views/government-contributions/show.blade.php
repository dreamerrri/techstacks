@extends('layouts.app')

@section('title', $employee->full_name . ' - Government Contributions')


@section('content')

    {{-- Top nav --}}
    <div class="flex justify-between items-center flex-wrap gap-3 mb-5">
        <a href="{{ route('government-contributions.index') }}"
           class="back-link text-gray-500 no-underline text-sm hover:text-emerald-600 flex items-center gap-1">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Government Contributions
        </a>
        <div class="flex gap-2">
            <button onclick="printContributionDetail()" class="btn btn-soft btn-info btn-sm">
                <i class="icon-[ph--printer-fill]"></i> Print
            </button>
            <button onclick="exportContributionDetailCSV()" class="btn btn-soft btn-success btn-sm">
                <i class="icon-[ph--file-csv-fill]"></i> Export CSV
            </button>
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
            <p class="text-gray-800 font-semibold text-sm mt-1 m-0">Basic Salary: ₱{{ number_format($employee->basic_salary, 2) }}</p>
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

    {{-- Government Contributions --}}
    <div class="card bg-base-100 shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-800 mb-5 flex items-center gap-2">
            <i class="icon-[ph--identification-card-fill] text-red-600"></i> Government Contributions
        </h2>

        {{-- ID Numbers Grid --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['SSS Number',   $employee->sss_number,        'icon-[ph--shield-check-fill]', 'text-emerald-600', 'bg-emerald-100'],
        ['PhilHealth',   $employee->philhealth_number,  'icon-[ph--heart-fill]',        'text-blue-600',    'bg-blue-100'],
        ['Pag-IBIG',     $employee->pagibig_number,     'icon-[ph--house-fill]',        'text-amber-500',   'bg-amber-100'],
        ['TIN Number',   $employee->tin_number,         'icon-[ph--receipt-fill]',      'text-violet-600',  'bg-violet-100'],
    ] as [$label, $value, $icon, $color, $bg])
        <div class="card bg-base-100 shadow-sm p-4 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 {{ $color }} {{ $bg }}">
                <i class="{{ $icon }}"></i>
            </div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium mb-1">{{ $label }}</div>
            <div class="font-bold font-mono text-gray-800 text-xs break-all">{{ $value ?? '—' }}</div>
        </div>
    @endforeach
</div>

        {{-- SSS --}}
        <div class="mt-4 p-5 bg-blue-50 rounded-2xl border border-blue-200">
            <h4 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-4">
                <i class="icon-[ph--calculator-fill]"></i> SSS Contribution (Circular No. 2024-006)
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Monthly Salary Credit</div>
                    <div class="font-bold text-gray-800 text-lg">₱{{ number_format($sssContribution['salary_credit'], 2) }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Employee Share</div>
                    <input type="number" name="custom_sss_contribution" id="custom_sss_contribution"
                           value="{{ $employee->custom_sss_contribution ?? $sssContribution['employee_share'] }}"
                           step="0.01" min="0"
                           class="input input-bordered w-full font-bold text-red-600 text-lg">
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Contribution</div>
                    <div class="font-bold text-gray-800 text-lg">₱{{ number_format($sssContribution['total'], 2) }}</div>
                </div>
            </div>
        </div>

        {{-- PhilHealth --}}
        <div class="mt-4 p-5 bg-emerald-50 rounded-2xl border border-emerald-200">
            <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-widest mb-4">
                <i class="icon-[ph--heartbeat-fill]"></i> PhilHealth Contribution (2025/2026)
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Salary Basis</div>
                    <div class="font-bold text-gray-800 text-lg">₱{{ number_format($philHealthContribution['salary_basis'], 2) }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Employee Rate</div>
                    <div class="font-bold text-gray-800 text-lg">{{ number_format($philHealthContribution['employee_rate'] * 100, 1) }}%</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Employee Share</div>
                    <input type="number" name="custom_philhealth_contribution" id="custom_philhealth_contribution"
                           value="{{ $employee->custom_philhealth_contribution ?? $philHealthContribution['employee_share'] }}"
                           step="0.01" min="0"
                           class="input input-bordered w-full font-bold text-red-600 text-lg">
                </div>
            </div>
        </div>

        {{-- Pag-IBIG --}}
        <div class="mt-4 p-5 bg-amber-50 rounded-2xl border border-amber-200">
            <h4 class="text-xs font-bold text-amber-800 uppercase tracking-widest mb-4">
                <i class="icon-[ph--house-fill]"></i> Pag-IBIG Contribution (2026)
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Monthly Salary</div>
                    <div class="font-bold text-gray-800 text-lg">₱{{ number_format($pagIbigContribution['salary'], 2) }}</div>
                </div>
                @if($pagIbigContribution['employee_rate'] !== null)
                    <div class="bg-white p-4 rounded-xl shadow-sm">
                        <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Employee Rate</div>
                        <div class="font-bold text-gray-800 text-lg">{{ number_format($pagIbigContribution['employee_rate'] * 100, 1) }}%</div>
                    </div>
                @endif
                <div class="bg-white p-4 rounded-xl shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Employee Share</div>
                    <input type="number" name="custom_pagibig_contribution" id="custom_pagibig_contribution"
                           value="{{ $employee->custom_pagibig_contribution ?? $pagIbigContribution['employee_share'] }}"
                           step="0.01" min="0"
                           class="input input-bordered w-full font-bold text-red-600 text-lg">
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="mt-6 text-right">
            <button onclick="saveCustomContributions()" class="btn btn-soft btn-error">
                <i class="icon-[ph--floppy-disk-fill]"></i> Save Custom Contributions
            </button>
        </div>

    </div>

@endsection

@section('scripts')
<script>
    const getVisibleInput = (inputs) => {
        for (let input of inputs) { if (input.offsetParent !== null) return input; }
        return inputs[0];
    };

    function saveCustomContributions() {
        const sssInput  = getVisibleInput(document.getElementsByName('custom_sss_contribution'));
        const philInput = getVisibleInput(document.getElementsByName('custom_philhealth_contribution'));
        const pagInput  = getVisibleInput(document.getElementsByName('custom_pagibig_contribution'));

        fetch(`/government-contributions/{{ $employee->employee_id }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({
                custom_sss_contribution:       sssInput.value || null,
                custom_philhealth_contribution: philInput.value || null,
                custom_pagibig_contribution:    pagInput.value || null,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Toast?.fire({ icon: 'success', title: 'Custom contributions updated successfully!' });
                setTimeout(() => location.reload(), 1200);
            } else {
                Toast?.fire({ icon: 'error', title: 'Error updating contributions.' });
            }
        })
        .catch(() => Toast?.fire({ icon: 'error', title: 'Error updating contributions.' }));
    }

    const employeeData = {
        id:         '{{ $employee->employee_id }}',
        name:       '{{ addslashes($employee->full_name) }}',
        department: '{{ addslashes($employee->department) }}',
        position:   '{{ addslashes($employee->position) }}',
        status:     '{{ $employee->employment_status }}',
        salary:     '{{ number_format($employee->basic_salary, 2) }}',
        sssNumber:  '{{ $employee->sss_number ?? "—" }}',
        philNumber: '{{ $employee->philhealth_number ?? "—" }}',
        pagNumber:  '{{ $employee->pagibig_number ?? "—" }}',
        tinNumber:  '{{ $employee->tin_number ?? "—" }}',
        sssSalaryCredit:  '{{ number_format($sssContribution["salary_credit"], 2) }}',
        sssEmployeeShare: '{{ number_format($sssContribution["employee_share"], 2) }}',
        sssTotal:         '{{ number_format($sssContribution["total"], 2) }}',
        philSalaryBasis:   '{{ number_format($philHealthContribution["salary_basis"], 2) }}',
        philEmployeeRate:  '{{ number_format($philHealthContribution["employee_rate"] * 100, 1) }}',
        philEmployeeShare: '{{ number_format($philHealthContribution["employee_share"], 2) }}',
        pagSalary:        '{{ number_format($pagIbigContribution["salary"], 2) }}',
        pagEmployeeRate:  '{{ $pagIbigContribution["employee_rate"] !== null ? number_format($pagIbigContribution["employee_rate"] * 100, 1) : "—" }}',
        pagEmployeeShare: '{{ number_format($pagIbigContribution["employee_share"], 2) }}',
    };

    function printContributionDetail() {
        const d = employeeData;
        const win = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html><html><head><title>Government Contributions — ${d.name}</title>
        <style>* { margin:0; padding:0; box-sizing:border-box; } body { font-family:Arial,sans-serif; font-size:11px; color:#111; padding:20px; }
        h1 { font-size:16px; color:#1a1a2e; margin-bottom:4px; } p { font-size:11px; color:#6b7280; margin-bottom:16px; }
        .profile { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:14px; margin-bottom:16px; display:flex; gap:24px; flex-wrap:wrap; }
        .field .label { font-size:9px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:2px; }
        .field .value { font-weight:600; color:#1a1a2e; font-size:12px; }
        .section { margin-bottom:16px; }
        .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:6px 10px; margin-bottom:8px; border-radius:4px; }
        .sss-title { background:#dbeafe; color:#1e40af; } .phil-title { background:#d1fae5; color:#065f46; }
        .pagibig-title { background:#fef3c7; color:#92400e; } .ids-title { background:#f3f4f6; color:#374151; }
        table { width:100%; border-collapse:collapse; } th { background:#1e40af; color:white; padding:6px 10px; text-align:left; font-size:10px; text-transform:uppercase; }
        td { padding:6px 10px; border-bottom:1px solid #e5e7eb; font-size:11px; } td.amount { font-weight:600; }
        @media print { body { padding:0; } }</style></head><body>
        <h1>Government Contributions Report</h1><p>Generated: ${new Date().toLocaleString()}</p>
        <div class="profile">
            <div class="field"><div class="label">Employee ID</div><div class="value" style="font-family:monospace;">${d.id}</div></div>
            <div class="field"><div class="label">Full Name</div><div class="value">${d.name}</div></div>
            <div class="field"><div class="label">Department</div><div class="value">${d.department}</div></div>
            <div class="field"><div class="label">Position</div><div class="value">${d.position}</div></div>
            <div class="field"><div class="label">Basic Salary</div><div class="value">₱${d.salary}</div></div>
            <div class="field"><div class="label">Status</div><div class="value">${d.status}</div></div>
        </div>
        <div class="section"><div class="section-title ids-title">Government ID Numbers</div>
        <table><thead><tr><th>SSS Number</th><th>PhilHealth Number</th><th>Pag-IBIG Number</th><th>TIN Number</th></tr></thead>
        <tbody><tr><td style="font-family:monospace;">${d.sssNumber}</td><td style="font-family:monospace;">${d.philNumber}</td><td style="font-family:monospace;">${d.pagNumber}</td><td style="font-family:monospace;">${d.tinNumber}</td></tr></tbody></table></div>
        <div class="section"><div class="section-title sss-title">SSS Contribution (Circular No. 2024-006)</div>
        <table><thead><tr><th>Monthly Salary Credit</th><th>Employee Share</th><th>Total Contribution</th></tr></thead>
        <tbody><tr><td class="amount">₱${d.sssSalaryCredit}</td><td class="amount">₱${d.sssEmployeeShare}</td><td class="amount">₱${d.sssTotal}</td></tr></tbody></table></div>
        <div class="section"><div class="section-title phil-title">PhilHealth Contribution (2025/2026)</div>
        <table><thead><tr><th>Salary Basis</th><th>Employee Rate</th><th>Employee Share</th></tr></thead>
        <tbody><tr><td class="amount">₱${d.philSalaryBasis}</td><td class="amount">${d.philEmployeeRate}%</td><td class="amount">₱${d.philEmployeeShare}</td></tr></tbody></table></div>
        <div class="section"><div class="section-title pagibig-title">Pag-IBIG Contribution (2026)</div>
        <table><thead><tr><th>Monthly Salary</th><th>Employee Rate</th><th>Employee Share</th></tr></thead>
        <tbody><tr><td class="amount">₱${d.pagSalary}</td><td class="amount">${d.pagEmployeeRate !== '—' ? d.pagEmployeeRate+'%' : '—'}</td><td class="amount">₱${d.pagEmployeeShare}</td></tr></tbody></table></div>
        </body></html>`);
        win.document.close(); win.focus(); win.print();
    }

    function exportContributionDetailCSV() {
        const d = employeeData;
        const rows = [
            ['Field','Value'],['Employee ID',d.id],['Full Name',d.name],['Department',d.department],
            ['Position',d.position],['Basic Salary',d.salary],['Status',d.status],[],
            ['SSS Number','PhilHealth Number','Pag-IBIG Number','TIN Number'],
            [d.sssNumber,d.philNumber,d.pagNumber,d.tinNumber],[],
            ['SSS — Monthly Salary Credit','SSS — Employee Share','SSS — Total Contribution'],
            [d.sssSalaryCredit,d.sssEmployeeShare,d.sssTotal],[],
            ['PhilHealth — Salary Basis','PhilHealth — Employee Rate','PhilHealth — Employee Share'],
            [d.philSalaryBasis,d.philEmployeeRate+'%',d.philEmployeeShare],[],
            ['Pag-IBIG — Monthly Salary','Pag-IBIG — Employee Rate','Pag-IBIG — Employee Share'],
            [d.pagSalary,d.pagEmployeeRate !== '—' ? d.pagEmployeeRate+'%' : '—',d.pagEmployeeShare],
        ];
        const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url; a.download = `contributions_${d.id}.csv`; a.click();
        URL.revokeObjectURL(url);
    }
</script>
@endsection
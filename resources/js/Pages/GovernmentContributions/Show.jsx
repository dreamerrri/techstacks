import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import { toast } from '../../components/toast';

const fmt = (n) => '₱' + parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtRate = (r) => (r === null || r === undefined ? '—' : (parseFloat(r) * 100).toFixed(1) + '%');

// Escape user-controlled values before interpolating into the print window's HTML
const esc = (v) =>
    String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const EMPLOYMENT_BADGE = {
    Regular: 'badge-soft badge-success',
    Probationary: 'badge-soft badge-warning',
    Contractual: 'badge-soft badge-info',
    'Part-time': 'badge-soft badge-neutral',
};

function ContributionInput({ form, name, value, error }) {
    return (
        <div className="bg-base-200 p-4 rounded-xl shadow-sm">
            <div className="text-xs text-faint uppercase tracking-wider mb-1">Employee Share</div>
            <input
                type="number"
                step="0.01"
                min="0"
                value={value ?? ''}
                onChange={(e) => form.setData(name, e.target.value === '' ? null : e.target.value)}
                className="input input-bordered w-full font-bold text-error text-lg"
            />
            <div className="text-xs text-subtle mt-1 italic">Leave blank to use calculated contribution based on salary</div>
            {error && <div className="text-xs text-error mt-1">{error}</div>}
        </div>
    );
}

export default function GovernmentContributionsShow({ employee, sssContribution, philHealthContribution, pagIbigContribution }) {
    const form = useForm({
        custom_sss_contribution: employee.custom_sss_contribution ?? sssContribution.employee_share,
        custom_philhealth_contribution: employee.custom_philhealth_contribution ?? philHealthContribution.employee_share,
        custom_pagibig_contribution: employee.custom_pagibig_contribution ?? pagIbigContribution.employee_share,
    });

    const submit = (e) => {
        e.preventDefault();
        form.patch(`/government-contributions/${employee.employee_id}`, {
            preserveScroll: true,
            onSuccess: () => toast('success', 'Custom contributions updated successfully!'),
            onError: () => toast('error', 'Error updating contributions.'),
        });
    };

    const govIds = [
        { label: 'SSS Number', value: employee.sss_number, icon: 'tabler--shield-check', color: 'text-success', bg: 'bg-success/10' },
        { label: 'PhilHealth Number', value: employee.philhealth_number, icon: 'tabler--heart', color: 'text-info', bg: 'bg-info/10' },
        { label: 'Pag-IBIG Number', value: employee.pagibig_number, icon: 'tabler--home', color: 'text-notification', bg: 'bg-notification/10' },
        { label: 'TIN Number', value: employee.tin_number, icon: 'tabler--file-text', color: 'text-secondary', bg: 'bg-secondary/10' },
    ];

    const employeeData = {
        id: employee.employee_id,
        name: employee.full_name,
        department: employee.department,
        position: employee.position,
        status: employee.employment_status,
        salary: fmt(employee.basic_salary).replace('₱', ''),
        sssNumber: employee.sss_number || '—',
        philNumber: employee.philhealth_number || '—',
        pagNumber: employee.pagibig_number || '—',
        tinNumber: employee.tin_number || '—',
        sssSalaryCredit: fmt(sssContribution.salary_credit).replace('₱', ''),
        sssEmployeeShare: fmt(sssContribution.employee_share).replace('₱', ''),
        sssTotal: fmt(sssContribution.total).replace('₱', ''),
        philSalaryBasis: fmt(philHealthContribution.salary_basis).replace('₱', ''),
        philEmployeeRate: fmtRate(philHealthContribution.employee_rate),
        philEmployeeShare: fmt(philHealthContribution.employee_share).replace('₱', ''),
        pagSalary: fmt(pagIbigContribution.salary).replace('₱', ''),
        pagEmployeeRate: pagIbigContribution.employee_rate !== null ? (pagIbigContribution.employee_rate * 100).toFixed(1) : '—',
        pagEmployeeShare: fmt(pagIbigContribution.employee_share).replace('₱', ''),
    };

    const printDetail = () => {
        const d = employeeData;
        const win = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html><html><head><title>Government Contributions — ${esc(d.name)}</title>
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
            <div class="field"><div class="label">Employee ID</div><div class="value" style="font-family:monospace;">${esc(d.id)}</div></div>
            <div class="field"><div class="label">Full Name</div><div class="value">${esc(d.name)}</div></div>
            <div class="field"><div class="label">Department</div><div class="value">${esc(d.department)}</div></div>
            <div class="field"><div class="label">Position</div><div class="value">${esc(d.position)}</div></div>
            <div class="field"><div class="label">Basic Salary</div><div class="value">₱${d.salary}</div></div>
            <div class="field"><div class="label">Status</div><div class="value">${esc(d.status)}</div></div>
        </div>
        <div class="section"><div class="section-title ids-title">Government ID Numbers</div>
        <table><thead><tr><th>SSS Number</th><th>PhilHealth Number</th><th>Pag-IBIG Number</th><th>TIN Number</th></tr></thead>
        <tbody><tr><td style="font-family:monospace;">${esc(d.sssNumber)}</td><td style="font-family:monospace;">${esc(d.philNumber)}</td><td style="font-family:monospace;">${esc(d.pagNumber)}</td><td style="font-family:monospace;">${esc(d.tinNumber)}</td></tr></tbody></table></div>
        <div class="section"><div class="section-title sss-title">SSS Contribution (Circular No. 2024-006)</div>
        <table><thead><tr><th>Monthly Salary Credit</th><th>Employee Share</th><th>Total Contribution</th></tr></thead>
        <tbody><tr><td class="amount">₱${d.sssSalaryCredit}</td><td class="amount">₱${d.sssEmployeeShare}</td><td class="amount">₱${d.sssTotal}</td></tr></tbody></table></div>
        <div class="section"><div class="section-title phil-title">PhilHealth Contribution (2025/2026)</div>
        <table><thead><tr><th>Salary Basis</th><th>Employee Rate</th><th>Employee Share</th></tr></thead>
        <tbody><tr><td class="amount">₱${d.philSalaryBasis}</td><td class="amount">${d.philEmployeeRate}</td><td class="amount">₱${d.philEmployeeShare}</td></tr></tbody></table></div>
        <div class="section"><div class="section-title pagibig-title">Pag-IBIG Contribution (2026)</div>
        <table><thead><tr><th>Monthly Salary</th><th>Employee Rate</th><th>Employee Share</th></tr></thead>
        <tbody><tr><td class="amount">₱${d.pagSalary}</td><td class="amount">${d.pagEmployeeRate !== '—' ? d.pagEmployeeRate + '%' : '—'}</td><td class="amount">₱${d.pagEmployeeShare}</td></tr></tbody></table></div>
        </body></html>`);
        win.document.close(); win.focus(); win.print();
    };

    const exportCSV = () => {
        const d = employeeData;
        const rows = [
            ['Field', 'Value'], ['Employee ID', d.id], ['Full Name', d.name], ['Department', d.department],
            ['Position', d.position], ['Basic Salary', d.salary], ['Status', d.status], [],
            ['SSS Number', 'PhilHealth Number', 'Pag-IBIG Number', 'TIN Number'],
            [d.sssNumber, d.philNumber, d.pagNumber, d.tinNumber], [],
            ['SSS — Monthly Salary Credit', 'SSS — Employee Share', 'SSS — Total Contribution'],
            [d.sssSalaryCredit, d.sssEmployeeShare, d.sssTotal], [],
            ['PhilHealth — Salary Basis', 'PhilHealth — Employee Rate', 'PhilHealth — Employee Share'],
            [d.philSalaryBasis, d.philEmployeeRate, d.philEmployeeShare], [],
            ['Pag-IBIG — Monthly Salary', 'Pag-IBIG — Employee Rate', 'Pag-IBIG — Employee Share'],
            [d.pagSalary, d.pagEmployeeRate, d.pagEmployeeShare],
        ];
        const csv = rows.map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = `contributions_${d.id}.csv`; a.click();
        URL.revokeObjectURL(url);
    };

    return (
        <AppLayout title={`${employee.full_name} - Government Contributions`}>
            <Head title={`${employee.full_name} - Government Contributions`} />
            <div className="p-2 sm:p-4">
                <div className="flex justify-between items-center flex-wrap gap-3 mb-5">
                    <Link href="/government-contributions" className="back-link text-subtle no-underline text-sm hover:text-success flex items-center gap-1">
                        <Icon name="tabler--arrow-left" className="size-4" /> Back to Government Contributions
                    </Link>
                    <div className="flex gap-2">
                        <button onClick={printDetail} className="btn btn-soft btn-info btn-sm">
                            <Icon name="tabler--printer" className="size-4" /> Print
                        </button>
                        <button onClick={exportCSV} className="btn btn-soft btn-success btn-sm">
                            <Icon name="tabler--file-type-csv" className="size-4" /> Export CSV
                        </button>
                    </div>
                </div>

                <div className="card bg-base-100 shadow-sm p-5 flex items-center gap-5 flex-wrap mb-5">
                    <div className="w-16 h-16 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                        {(employee.full_name || '?').charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h2 className="text-xl font-bold text-base-content m-0 mb-1">{employee.full_name}</h2>
                        <p className="text-subtle m-0">{employee.position} — {employee.department}</p>
                        <p className="text-base-content font-semibold text-sm mt-1 m-0">Basic Salary: {fmt(employee.basic_salary)}</p>
                        <span className={`badge ${EMPLOYMENT_BADGE[employee.employment_status] || 'badge-soft'} mt-2`}>{employee.employment_status}</span>
                    </div>
                </div>

                <form onSubmit={submit} className="card bg-base-100 shadow-sm p-6">
                    <h2 className="text-sm font-bold text-base-content mb-5 flex items-center gap-2">
                        <Icon name="tabler--id" className="text-error size-4" /> Government Contributions
                    </h2>

                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        {govIds.map(({ label, value, icon, color, bg }) => (
                            <div key={label} className="card bg-base-100 shadow-sm p-4 text-center">
                                <div className={`w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 ${color} ${bg}`}>
                                    <Icon name={icon} className="size-6" />
                                </div>
                                <div className="text-xs text-faint uppercase tracking-widest font-medium mb-1">{label}</div>
                                <div className="font-bold font-mono text-base-content text-xs break-all">{value || '—'}</div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-4 p-5 bg-info/10 rounded-2xl border border-info/20">
                        <h4 className="text-xs font-bold text-info uppercase tracking-widest mb-4">
                            <Icon name="tabler--calculator" className="size-4" /> SSS Contribution (Circular No. 2024-006)
                        </h4>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="bg-base-200 p-4 rounded-xl shadow-sm">
                                <div className="text-xs text-faint uppercase tracking-wider mb-1">Monthly Salary Credit</div>
                                <div className="font-bold text-base-content text-lg">{fmt(sssContribution.salary_credit)}</div>
                            </div>
                            <ContributionInput
                                form={form}
                                name="custom_sss_contribution"
                                value={form.data.custom_sss_contribution}
                                error={form.errors.custom_sss_contribution}
                            />
                            <div className="bg-base-200 p-4 rounded-xl shadow-sm">
                                <div className="text-xs text-faint uppercase tracking-wider mb-1">Total Contribution</div>
                                <div className="font-bold text-base-content text-lg">{fmt(sssContribution.total)}</div>
                            </div>
                        </div>
                    </div>

                    <div className="mt-4 p-5 bg-success/10 rounded-2xl border border-success/20">
                        <h4 className="text-xs font-bold text-success uppercase tracking-widest mb-4">
                            <Icon name="tabler--heartbeat" className="size-4" /> PhilHealth Contribution (2025/2026)
                        </h4>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="bg-base-200 p-4 rounded-xl shadow-sm">
                                <div className="text-xs text-faint uppercase tracking-wider mb-1">Salary Basis</div>
                                <div className="font-bold text-base-content text-lg">{fmt(philHealthContribution.salary_basis)}</div>
                            </div>
                            <div className="bg-base-200 p-4 rounded-xl shadow-sm">
                                <div className="text-xs text-faint uppercase tracking-wider mb-1">Employee Rate</div>
                                <div className="font-bold text-base-content text-lg">{fmtRate(philHealthContribution.employee_rate)}</div>
                            </div>
                            <ContributionInput
                                form={form}
                                name="custom_philhealth_contribution"
                                value={form.data.custom_philhealth_contribution}
                                error={form.errors.custom_philhealth_contribution}
                            />
                        </div>
                    </div>

                    <div className="mt-4 p-5 bg-warning/10 rounded-2xl border border-warning/20">
                        <h4 className="text-xs font-bold text-warning uppercase tracking-widest mb-4">
                            <Icon name="tabler--home" className="size-4" /> Pag-IBIG Contribution (2026)
                        </h4>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="bg-base-200 p-4 rounded-xl shadow-sm">
                                <div className="text-xs text-faint uppercase tracking-wider mb-1">Monthly Salary</div>
                                <div className="font-bold text-base-content text-lg">{fmt(pagIbigContribution.salary)}</div>
                            </div>
                            {pagIbigContribution.employee_rate !== null && (
                                <div className="bg-base-200 p-4 rounded-xl shadow-sm">
                                    <div className="text-xs text-faint uppercase tracking-wider mb-1">Employee Rate</div>
                                    <div className="font-bold text-base-content text-lg">{fmtRate(pagIbigContribution.employee_rate)}</div>
                                </div>
                            )}
                            <ContributionInput
                                form={form}
                                name="custom_pagibig_contribution"
                                value={form.data.custom_pagibig_contribution}
                                error={form.errors.custom_pagibig_contribution}
                            />
                        </div>
                    </div>

                    <div className="mt-6 text-right">
                        <button type="submit" disabled={form.processing} className="btn btn-soft btn-error">
                            <Icon name="tabler--device-floppy" className="size-4" /> {form.processing ? 'Saving...' : 'Save Custom Contributions'}
                        </button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
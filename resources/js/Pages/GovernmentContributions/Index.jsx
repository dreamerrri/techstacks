import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import DataTable from '../../Components/DataTable';
import { toast } from '../../Components/toast';

const fmt = (n) => '₱' + parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtNum = (n) => parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

const EMPLOYMENT_BADGE = {
    Regular: 'badge-soft badge-success text-xs',
    Probationary: 'badge-soft badge-warning text-xs',
    Contractual: 'badge-soft badge-info text-xs',
    'Part-time': 'badge-soft badge-neutral text-xs',
};

function statusBadge(status) {
    return EMPLOYMENT_BADGE[status] || 'badge-soft text-xs';
}

function ContributionBreakdownModal({ open, onClose, data, label }) {
    if (!open) return null;

    const totals = data.reduce(
        (acc, e) => {
            acc.sss += parseFloat(e.sss_employee_share) || 0;
            acc.phil += parseFloat(e.philhealth_employee_share) || 0;
            acc.pag += parseFloat(e.pagibig_employee_share) || 0;
            return acc;
        },
        { sss: 0, phil: 0, pag: 0 }
    );
    totals.all = totals.sss + totals.phil + totals.pag;

    const printBreakdown = () => {
        if (!data.length) return toast('error', 'No data to print.');
        const searchVal = window.history.state?.url ?? '';
        const deptVal = label !== 'All Departments' ? label : '';
        const filterNote = [searchVal.includes('search=') ? `Search: "${decodeURIComponent((searchVal.match(/search=([^&]*)/) || [])[1] || '')}"` : '', deptVal ? `Department: ${deptVal}` : ''].filter(Boolean).join(' | ') || 'All Employees';

        let rows = '';
        let t = { sss: 0, phil: 0, pag: 0, all: 0 };
        data.forEach((emp) => {
            const sss = parseFloat(emp.sss_employee_share) || 0;
            const phil = parseFloat(emp.philhealth_employee_share) || 0;
            const pag = parseFloat(emp.pagibig_employee_share) || 0;
            const total = sss + phil + pag;
            t.sss += sss; t.phil += phil; t.pag += pag; t.all += total;
            rows += `<tr><td><strong>${emp.full_name}</strong><br><small>${emp.employee_id}</small></td><td>${emp.department}</td><td class="num">${fmt(emp.basic_salary)}</td><td class="num red">${fmt(sss)}</td><td class="num blue">${fmt(phil)}</td><td class="num amber">${fmt(pag)}</td><td class="num bold">${fmt(total)}</td><td>${emp.employment_status}</td></tr>`;
        });
        const win = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html><html><head><title>Government Contributions Report</title>
        <style>* { margin:0; padding:0; box-sizing:border-box; } body { font-family:Arial,sans-serif; font-size:11px; color:#111; padding:20px; }
        h1 { font-size:16px; color:#1a1a2e; margin-bottom:4px; } .meta { font-size:11px; color:#6b7280; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; } thead th { background:#1e40af; color:white; padding:7px 8px; font-size:10px; text-transform:uppercase; text-align:left; }
        thead th.num { text-align:right; } td { padding:6px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top; }
        td.num { text-align:right; } td.red { color:#dc2626; } td.blue { color:#2563eb; } td.amber { color:#d97706; } td.bold { font-weight:700; }
        tr:nth-child(even) td { background:#f9fafb; } small { color:#6b7280; font-size:10px; font-family:monospace; }
        tfoot tr td { background:#fef2f2; font-weight:700; border-top:2px solid #fecaca; padding:8px; } tfoot tr td.num { text-align:right; }
        .total-bar { margin-top:12px; background:#fee2e2; border-radius:8px; padding:12px 16px; display:flex; justify-content:space-between; }
        .total-bar span, .total-bar strong { font-size:11px; color:#991b1b; } @media print { body { padding:0; } }</style>
        </head><body>
        <h1>Government Contributions Report</h1>
        <div class="meta">Filter: ${filterNote} | Printed: ${new Date().toLocaleString()}</div>
        <table><thead><tr><th>Employee</th><th>Dept</th><th class="num">Basic Salary</th><th class="num">SSS Share</th><th class="num">PhilHealth Share</th><th class="num">Pag-IBIG Share</th><th class="num">Total</th><th>Status</th></tr></thead>
        <tbody>${rows}</tbody>
        <tfoot><tr><td colspan="2">Totals (${data.length} employees)</td><td class="num">—</td><td class="num red">${fmt(t.sss)}</td><td class="num blue">${fmt(t.phil)}</td><td class="num amber">${fmt(t.pag)}</td><td class="num bold">${fmt(t.all)}</td><td></td></tr></tfoot>
        </table>
        <div class="total-bar"><span><strong>Total Government Contributions</strong></span><strong>${fmt(t.all)}</strong></div>
        </body></html>`);
        win.document.close(); win.focus(); win.print();
    };

    const exportCSV = () => {
        if (!data.length) return toast('error', 'No data to export.');
        const headers = ['Employee', 'Employee ID', 'Department', 'Basic Salary', 'SSS Share', 'PhilHealth Share', 'Pag-IBIG Share', 'Total Contributions', 'Status'];
        const csvRows = [headers.join(',')];
        let totAll = 0;
        data.forEach((emp) => {
            const sss = parseFloat(emp.sss_employee_share) || 0;
            const phil = parseFloat(emp.philhealth_employee_share) || 0;
            const pag = parseFloat(emp.pagibig_employee_share) || 0;
            const total = sss + phil + pag;
            totAll += total;
            csvRows.push([`"${emp.full_name}"`, `"${emp.employee_id}"`, `"${emp.department}"`, parseFloat(emp.basic_salary || 0).toFixed(2), sss.toFixed(2), phil.toFixed(2), pag.toFixed(2), total.toFixed(2), `"${emp.employment_status}"`].join(','));
        });
        csvRows.push(['', '', '"TOTALS"', '', '', '', '', totAll.toFixed(2), ''].join(','));
        const filename = label !== 'All Departments' ? `contributions_${label.replace(/\s+/g, '_')}.csv` : 'contributions_all.csv';
        const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = filename; a.click();
        URL.revokeObjectURL(url);
    };

    return (
        <div className="fixed inset-0 z-[9999] bg-black/50 items-start justify-center p-5 overflow-y-auto flex" onClick={onClose}>
            <div className="bg-base-100 rounded-2xl w-full max-w-[90vw] mx-auto shadow-2xl overflow-hidden" onClick={(e) => e.stopPropagation()}>
                <div className="px-7 py-5 border-b border-base-300 flex justify-between items-center">
                    <div>
                        <div className="text-base font-bold text-base-content flex items-center gap-2">
                            <Icon name="tabler--stack" className="text-error size-5" /> Contribution Breakdown
                        </div>
                        <div className="text-xs text-base-content/60 mt-1">
                            {data.length} employee{data.length !== 1 ? 's' : ''} | {label}
                        </div>
                    </div>
                    <button onClick={onClose} className="btn btn-soft btn-error btn-sm btn-circle">
                        <Icon name="tabler--x" className="size-4" />
                    </button>
                </div>

                {data.length ? (
                    <>
                        <div className="overflow-y-auto overflow-x-auto max-h-[70vh]">
                            <table className="table w-full text-sm table-borderless min-w-[900px]">
                                <thead className="sticky top-0 z-5 bg-base-100">
                                    <tr className="bg-success/67 shadow-md text-success-content text-xs">
                                        <th>Employee</th>
                                        <th>Dept</th>
                                        <th>Basic Salary</th>
                                        <th>SSS Share</th>
                                        <th>PhilHealth Share</th>
                                        <th>Pag-IBIG Share</th>
                                        <th>Total Contribs</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.map((emp) => {
                                        const sss = parseFloat(emp.sss_employee_share) || 0;
                                        const phil = parseFloat(emp.philhealth_employee_share) || 0;
                                        const pag = parseFloat(emp.pagibig_employee_share) || 0;
                                        const total = sss + phil + pag;
                                        return (
                                            <tr key={emp.id} className="row-hover text-xs">
                                                <td>
                                                    <div className="font-semibold text-base-content">
                                                        <Link href={`/government-contributions/${emp.id}`} className="text-base-content no-underline hover:text-primary">{emp.full_name}</Link>
                                                    </div>
                                                    <div className="text-xs text-base-content/40 font-mono">{emp.employee_id}</div>
                                                </td>
                                                <td className="text-base-content/60">{emp.department}</td>
                                                <td className="text-right font-semibold text-base-content">{fmt(emp.basic_salary)}</td>
                                                <td className="text-right text-error">{fmt(sss)}</td>
                                                <td className="text-right text-info">{fmt(phil)}</td>
                                                <td className="text-right text-warning">{fmt(pag)}</td>
                                                <td className="text-right font-bold text-base-content">{fmt(total)}</td>
                                                <td className="text-center">
                                                    <span className={`badge ${statusBadge(emp.employment_status)} whitespace-nowrap`}>{emp.employment_status}</span>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                                <tfoot>
                                    <tr className="bg-error/10 font-bold text-error border-t-2 border-error/30">
                                        <td colSpan="7">Totals ({data.length} employees)</td>
                                        <td className="text-right">{fmt(totals.all)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div className="px-6 py-4 border-t border-base-300 flex justify-between items-center flex-wrap gap-2">
                            <div className="flex gap-2 flex-wrap">
                                <button onClick={printBreakdown} className="btn btn-soft btn-info btn-sm">
                                    <Icon name="tabler--printer" className="size-4" /> Print PDF
                                </button>
                                <button onClick={exportCSV} className="btn btn-soft btn-success btn-sm">
                                    <Icon name="tabler--file-type-csv" className="size-4" /> Export CSV
                                </button>
                            </div>
                        </div>
                    </>
                ) : (
                    <div className="py-10 text-base-content/40 flex flex-col items-center justify-center gap-2 w-full">
                        <Icon name="tabler--inbox" className="size-8" />
                        <span>No contribution data for the current filter.</span>
                    </div>
                )}
            </div>
        </div>
    );
}

export default function GovernmentContributionsIndex({ employees, departments, filters }) {
    const [open, setOpen] = useState(false);

    const deptLabel = filters.department || 'All Departments';

    const breakdownData = (employees.data || []).map((emp) => ({
        id: emp.id,
        employee_id: emp.employee_id,
        full_name: emp.full_name,
        department: emp.department,
        employment_status: emp.employment_status,
        basic_salary: emp.basic_salary || 0,
        sss_employee_share: emp.contribution?.sss_employee_share || 0,
        philhealth_employee_share: emp.contribution?.philhealth_employee_share || 0,
        pagibig_employee_share: emp.contribution?.pagibig_employee_share || 0,
    }));

    return (
        <AppLayout title="Government Contributions">
            <Head title="Government Contributions" />
            <div className="p-2 sm:p-4">
                <ContributionBreakdownModal
                    open={open}
                    onClose={() => setOpen(false)}
                    data={breakdownData}
                    label={deptLabel}
                />

                <DataTable
                    title="Employee List"
                    icon="tabler--stack"
                    tooltip="View and manage employee government contribution rates."
                    baseUrl="/government-contributions"
                    search
                    searchPlaceholder="Search name or email..."
                    filters={[
                        {
                            name: 'department',
                            value: filters.department || '',
                            options: [
                                { value: '', label: 'All Departments' },
                                ...departments.map((dept) => ({ value: dept, label: dept })),
                            ],
                        },
                        {
                            name: 'status',
                            value: filters.status || '',
                            options: [
                                { value: '', label: 'All Status' },
                                { value: 'Regular', label: 'Regular' },
                                { value: 'Probationary', label: 'Probationary' },
                                { value: 'Contractual', label: 'Contractual' },
                                { value: 'Part-time', label: 'Part-time' },
                            ],
                        },
                    ]}
                    paginator={employees}
                    empty="No employees found."
                    actions={
                        <button onClick={() => setOpen(true)} className="btn btn-soft btn-error btn-sm">
                            <Icon name="tabler--stack" className="size-4" /> Breakdown
                        </button>
                    }
                >
                    <div className="overflow-x-auto overflow-y-auto hidden md:block" style={{ maxHeight: '55vh' }}>
                        <table className="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th className="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {(employees.data || []).map((employee) => (
                                    <tr key={employee.id} className="row-hover">
                                        <td className="font-mono text-base-content/60">{employee.employee_id}</td>
                                        <td className="font-semibold text-base-content">
                                            <Link href={`/government-contributions/${employee.id}`} className="text-base-content no-underline hover:text-primary">
                                                {employee.full_name}
                                            </Link>
                                        </td>
                                        <td className="text-base-content/60">{employee.department}</td>
                                        <td className="text-base-content/60">{employee.position}</td>
                                        <td>
                                            <span className={`badge ${statusBadge(employee.employment_status)} whitespace-nowrap`}>{employee.employment_status}</span>
                                        </td>
                                        <td className="text-center">
                                            <Link href={`/government-contributions/${employee.id}`} className="btn btn-soft btn-info btn-sm" title="View contributions">
                                                <Icon name="tabler--eye" className="size-4" />
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="md:hidden p-4 flex flex-col gap-3">
                        {(employees.data || []).map((employee) => (
                            <div key={employee.id} className="card bg-base-100 border border-base-300 p-4">
                                <div className="flex justify-between items-start mb-2">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                            {(employee.full_name || '?').charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <Link href={`/government-contributions/${employee.id}`} className="font-semibold text-base-content no-underline text-sm hover:text-primary">
                                                {employee.full_name}
                                            </Link>
                                            <div className="text-xs text-base-content/60 font-mono">{employee.employee_id}</div>
                                        </div>
                                    </div>
                                    <span className={`badge ${statusBadge(employee.employment_status)} whitespace-nowrap`}>{employee.employment_status}</span>
                                </div>

                                <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                                    <span><Icon name="tabler--building" className="size-3.5 inline" /> {employee.department}</span>
                                    <span><Icon name="tabler--briefcase" className="size-3.5 inline" /> {employee.position}</span>
                                    <span><Icon name="tabler--cash" className="size-3.5 inline" /> {fmt(employee.basic_salary)}</span>
                                </div>

                                <div className="mt-3 pt-3 border-t border-base-200">
                                    <Link href={`/government-contributions/${employee.id}`} className="btn btn-soft btn-info btn-sm">
                                        <Icon name="tabler--eye" className="size-4" /> View Contributions
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                </DataTable>
            </div>
        </AppLayout>
    );
}

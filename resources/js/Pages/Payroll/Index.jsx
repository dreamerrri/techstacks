import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import DataTable from '../../components/DataTable';
import { toast } from '../../components/toast';

const fmt = (n) => '₱' + parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtNum = (n) => parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

// Escape user-controlled values before interpolating into the print window's HTML
const esc = (v) =>
    String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

function SortableTh({ label, sortKey, filters, align = 'right' }) {
    const active = filters.sort === sortKey;
    const direction = active && filters.direction === 'asc' ? 'desc' : 'asc';

    const handleSort = () => {
        router.get('/payroll', { ...filters, sort: sortKey, direction }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    };

    return (
        <th className={align === 'right' ? 'text-right' : 'text-center'}>
            <button type="button" onClick={handleSort} className="inline-flex items-center gap-1 normal-case font-medium hover:text-primary cursor-pointer">
                {label}
                {active ? (
                    <Icon name={filters.direction === 'asc' ? 'tabler--arrow-up' : 'tabler--arrow-down'} className="size-3.5" />
                ) : (
                    <Icon name="tabler--arrows-sort" className="size-3.5 opacity-40" />
                )}
            </button>
        </th>
    );
}

function DeptBreakdownModal({ open, onClose, data, label, periodLabel }) {
    if (!open) return null;

    const totals = data.reduce(
        (acc, e) => {
            acc.basic += parseFloat(e.basic_pay) || 0;
            acc.allowance += parseFloat(e.allowance_benefits) || 0;
            acc.ot += parseFloat(e.overtime_pay) || 0;
            acc.gross += parseFloat(e.gross_pay) || 0;
            acc.sss += parseFloat(e.sss_contribution) || 0;
            acc.phil += parseFloat(e.philhealth_contribution) || 0;
            acc.pagibig += parseFloat(e.pagibig_contribution) || 0;
            acc.tax += parseFloat(e.withholding_tax) || 0;
            acc.ded += parseFloat(e.total_deductions) || 0;
            acc.net += parseFloat(e.net_pay) || 0;
            return acc;
        },
        { basic: 0, allowance: 0, ot: 0, gross: 0, sss: 0, phil: 0, pagibig: 0, tax: 0, ded: 0, net: 0 }
    );

    const printPayrollTable = () => {
        if (!data.length) return toast('error', 'No payroll data to print.');
        const headers = ['Employee', 'Dept', 'Basic Pay', 'Allowance', 'OT Pay', 'Earnings', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Total Deductions', 'Net Pay'];
        let rows = '';
        let t = { basic: 0, allowance: 0, ot: 0, gross: 0, sss: 0, phil: 0, pagibig: 0, tax: 0, ded: 0, net: 0 };
        data.forEach((d) => {
            t.basic += +d.basic_pay; t.allowance += +d.allowance_benefits; t.ot += +d.overtime_pay;
            t.gross += +d.gross_pay; t.sss += +d.sss_contribution; t.phil += +d.philhealth_contribution;
            t.pagibig += +d.pagibig_contribution; t.tax += +d.withholding_tax; t.ded += +d.total_deductions; t.net += +d.net_pay;
            rows += `<tr><td><strong>${esc(d.name)}</strong><br><small>${esc(d.employee_id)}</small></td><td>${esc(d.department)}</td><td class="num">${fmt(d.basic_pay)}</td><td class="num">${fmt(d.allowance_benefits)}</td><td class="num">${fmt(d.overtime_pay)}</td><td class="num">${fmt(d.gross_pay)}</td><td class="num red">${fmt(d.sss_contribution)}</td><td class="num red">${fmt(d.philhealth_contribution)}</td><td class="num red">${fmt(d.pagibig_contribution)}</td><td class="num red">${fmt(d.withholding_tax)}</td><td class="num red bold">${fmt(d.total_deductions)}</td><td class="num green bold">${fmt(d.net_pay)}</td></tr>`;
        });
        const win = window.open('', '_blank');
        win.document.write(`<!DOCTYPE html><html><head><title>Payroll Summary Report</title>
        <style>* { margin:0; padding:0; box-sizing:border-box; } body { font-family:Arial,sans-serif; font-size:11px; color:#111; padding:20px; }
        h1 { font-size:16px; color:#1a1a2e; margin-bottom:4px; } .meta { font-size:11px; color:#6b7280; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; } thead th { background:#1e40af; color:white; padding:7px 8px; font-size:10px; text-transform:uppercase; text-align:left; }
        thead th.num { text-align:right; } td { padding:6px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top; } td.num { text-align:right; }
        tr:nth-child(even) td { background:#f9fafb; } small { color:#6b7280; font-size:10px; font-family:monospace; }
        .red { color:#dc2626; } .green { color:#065f46; } .bold { font-weight:700; }
        tfoot tr td { background:#dbeafe; font-weight:700; border-top:2px solid #93c5fd; padding:8px; } tfoot tr td.num { text-align:right; }
        .gross-bar { margin-top:12px; background:#d1fae5; border-radius:8px; padding:12px 16px; display:flex; justify-content:space-between; }
        .gross-bar span, .gross-bar strong { font-size:11px; color:#065f46; } @media print { body { padding:0; } }</style>
        </head><body><h1>Payroll Summary Report</h1><div class="meta">Filter: ${esc(label)} | Cutoff: ${esc(periodLabel)} | Printed: ${new Date().toLocaleString()}</div>
        <table><thead><tr>${headers.map((h, i) => i >= 2 ? `<th class="num">${h}</th>` : `<th>${h}</th>`).join('')}</tr></thead>
        <tbody>${rows}</tbody>
        <tfoot><tr><td colspan="2">Totals (${data.length} employees)</td><td class="num">${fmt(t.basic)}</td><td class="num">${fmt(t.allowance)}</td><td class="num">${fmt(t.ot)}</td><td class="num">${fmt(t.gross)}</td><td class="num red">${fmt(t.sss)}</td><td class="num red">${fmt(t.phil)}</td><td class="num red">${fmt(t.pagibig)}</td><td class="num red">${fmt(t.tax)}</td><td class="num red bold">${fmt(t.ded)}</td><td class="num green bold">${fmt(t.net)}</td></tr></tfoot>
        </table><div class="gross-bar"><span><strong>Total Gross Pay</strong></span><strong>${fmt(t.net)}</strong></div></body></html>`);
        win.document.close(); win.focus(); win.print();
    };

    const exportPayrollCSV = () => {
        if (!data.length) return toast('error', 'No payroll data to export.');
        const headers = ['Employee', 'Employee ID', 'Department', 'Basic Pay', 'Allowance', 'OT Pay', 'Earnings', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Total Deductions', 'Net Pay'];
        const csvRows = [headers.join(',')];
        let totNet = 0;
        data.forEach((d) => {
            totNet += parseFloat(d.net_pay) || 0;
            csvRows.push([`"${d.name}"`, `"${d.employee_id}"`, `"${d.department}"`, d.basic_pay, d.allowance_benefits, d.overtime_pay, d.gross_pay, d.sss_contribution, d.philhealth_contribution, d.pagibig_contribution, d.withholding_tax, d.total_deductions, d.net_pay].join(','));
        });
        csvRows.push(['', '', '"TOTALS"', '', '', '', '', '', totNet.toFixed(2)].join(','));
        const periodSlug = periodLabel ? `_${periodLabel.replace(/[^a-zA-Z0-9]/g, '_').replace(/_+/g, '_')}` : '';
        const filename = label !== 'All Departments' ? `payroll_${label.replace(/\s+/g, '_')}${periodSlug}.csv` : `payroll_all${periodSlug}.csv`;
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
                        <div className="text-base font-bold text-base-content">{label === 'All Departments' ? 'All Departments' : label} Breakdown</div>
                        <div className="text-xs text-subtle mt-1">{data.length} employee{data.length !== 1 ? 's' : ''} | {periodLabel}</div>
                    </div>
                    <button onClick={onClose} className="btn btn-soft btn-error btn-sm btn-circle">
                        <Icon name="tabler--x" className="size-4" />
                    </button>
                </div>

                {data.length ? (
                    <>
                        <div className="overflow-y-auto overflow-x-auto max-h-[70vh]">
                            <table className="table w-full text-sm table-borderless">
                                <thead className="sticky top-0 z-5 bg-base-100">
                                    <tr className="bg-success/67 shadow-md text-success-content text-xs">
                                        <th>Employee</th>
                                        <th>Dept</th>
                                        <th>Basic Pay</th>
                                        <th>Allowance</th>
                                        <th>OT Pay</th>
                                        <th>Earnings</th>
                                        <th>SSS</th>
                                        <th>PhilHealth</th>
                                        <th>Pag-IBIG</th>
                                        <th>Tax</th>
                                        <th>Total Deductions</th>
                                        <th>Net Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.map((emp, i) => (
                                        <tr key={i} className="row-hover text-xs">
                                            <td>
                                                <div className="font-semibold text-base-content truncate">{emp.name}</div>
                                                <div className="text-xs text-subtle font-mono">{emp.employee_id}</div>
                                            </td>
                                            <td className="text-base-content truncate">{emp.department}</td>
                                            <td className="text-right font-semibold text-base-content">{fmt(emp.basic_pay)}</td>
                                            <td className="text-right text-base-content">{fmt(emp.allowance_benefits)}</td>
                                            <td className="text-right text-base-content">{fmt(emp.overtime_pay)}</td>
                                            <td className="text-right font-semibold text-base-content">{fmt(emp.gross_pay)}</td>
                                            <td className="text-right text-error">{fmt(emp.sss_contribution)}</td>
                                            <td className="text-right text-error">{fmt(emp.philhealth_contribution)}</td>
                                            <td className="text-right text-error">{fmt(emp.pagibig_contribution)}</td>
                                            <td className="text-right text-error">{fmt(emp.withholding_tax)}</td>
                                            <td className="text-right font-semibold text-error">{fmt(emp.total_deductions)}</td>
                                            <td className="text-right font-bold text-success text-sm">{fmt(emp.net_pay)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot>
                                    <tr className="bg-success/15 font-bold text-success border-t-2 border-success/30">
                                        <td colSpan="11">Gross Pay ({data.length} employee{data.length !== 1 ? 's' : ''})</td>
                                        <td className="text-right">{fmt(totals.gross)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div className="px-6 py-4 border-t border-base-300 flex justify-between items-center flex-wrap gap-2">
                            <div className="flex gap-2 flex-wrap">
                                <button onClick={printPayrollTable} className="btn btn-soft btn-info btn-sm">
                                    <Icon name="tabler--printer" className="size-4" /> Print PDF
                                </button>
                                <button onClick={exportPayrollCSV} className="btn btn-soft btn-success btn-sm">
                                    <Icon name="tabler--file-type-csv" className="size-4" /> Export CSV
                                </button>
                            </div>
                        </div>
                    </>
                ) : (
                    <div className="py-10 text-base-content flex flex-col items-center justify-center gap-2 w-full">
                        <Icon name="tabler--inbox" className="size-8" />
                        <span>No payroll data for the current filter.</span>
                    </div>
                )}
            </div>
        </div>
    );
}

export default function PayrollIndex({ employees, departments, payrollData, payrollPeriods, selectedPeriod, filters }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isHR = auth?.user?.role === 'hr';
    const canViewAll = isAdmin || isHR;
    const avatarClass = isAdmin ? 'from-error to-error/80' : isHR ? 'from-info to-info/80' : 'from-secondary to-secondary/80';

    const [deptOpen, setDeptOpen] = useState(false);

    const deptBreakdownData = (employees.data || [])
        .map((emp) => {
            const p = payrollData[emp.id] || {};
            if ((p.gross_pay || 0) === 0) return null;
            return {
                name: emp.full_name,
                employee_id: emp.employee_id,
                department: emp.department,
                basic_pay: p.base_pay || 0,
                allowance_benefits: p.allowance_benefits || 0,
                overtime_pay: p.overtime_pay || 0,
                gross_pay: p.gross_pay || 0,
                sss_contribution: p.sss_contribution || 0,
                philhealth_contribution: p.philhealth_contribution || 0,
                pagibig_contribution: p.pagibig_contribution || 0,
                withholding_tax: p.withholding_tax || 0,
                total_deductions: p.total_deductions || 0,
                net_pay: p.net_pay || 0,
            };
        })
        .filter(Boolean);

    const deptLabel = filters.department || 'All Departments';
    const periodLabel = selectedPeriod ? `${selectedPeriod.cutoff_start} – ${selectedPeriod.cutoff_end} (${selectedPeriod.status})` : 'Latest Cutoff';

    const periodOptions = [
        { value: '', label: 'Latest Cutoff' },
        ...payrollPeriods.map((p) => ({
            value: String(p.id),
            label: `${p.cutoff_start} – ${p.cutoff_end} (${p.status.charAt(0).toUpperCase() + p.status.slice(1)})`,
        })),
    ];

    return (
        <AppLayout title="Payroll Preview">
            <Head title="Payroll Preview" />
            <div className="p-2 sm:p-4">
                <DeptBreakdownModal
                    open={deptOpen}
                    onClose={() => setDeptOpen(false)}
                    data={deptBreakdownData}
                    label={deptLabel}
                    periodLabel={periodLabel}
                />

                <DataTable
                    title="Payroll Summary"
                    icon="tabler--cash"
                    tooltip={canViewAll ? 'View payroll calculations for all employees.' : 'View your payroll calculation.'}
                    baseUrl="/payroll"
                    search={canViewAll}
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
                            name: 'payroll_period_id',
                            value: filters.period ? String(filters.period) : '',
                            options: periodOptions,
                        },
                    ]}
                    paginator={employees}
                    empty="No payroll data found."
                    actions={
                        canViewAll && (
                            <button onClick={() => setDeptOpen(true)} className="btn btn-soft btn-error btn-sm">
                                <Icon name="tabler--stack" className="size-4" /> Breakdown
                            </button>
                        )
                    }
                >
                    {selectedPeriod && (
                        <div className="px-4 sm:px-7 pb-4 flex flex-col sm:flex-row sm:items-center gap-2 text-sm text-info">
                            <div className="flex items-center gap-2 flex-wrap">
                                <Icon name="tabler--calendar" className="size-4" />
                                <strong>{selectedPeriod.cutoff_start} – {selectedPeriod.cutoff_end}</strong>
                                <span className={`badge ${selectedPeriod.status === 'finalized' ? 'badge-soft badge-success' : 'badge-soft badge-warning'} badge-sm`}>
                                    {selectedPeriod.status.charAt(0).toUpperCase() + selectedPeriod.status.slice(1)}
                                </span>
                            </div>
                            <span className="hidden sm:inline text-base-content">|</span>
                            <span className="text-error/80">
                                <strong>Payroll date: {selectedPeriod.payroll_date}</strong>
                            </span>
                        </div>
                    )}

                    <div className="overflow-x-auto overflow-y-auto hidden md:block" style={{ maxHeight: '55vh' }}>
                        <table className="table table-hover">
                            <thead>
                                <tr>
                                    <th className="w-44">Employee</th>
                                    <th className="w-28">Department</th>
                                    <SortableTh label="Basic Pay" sortKey="base_pay" filters={filters} />
                                    <SortableTh label="Days Worked" sortKey="days_worked" filters={filters} align="center" />
                                    <SortableTh label="OT Hrs" sortKey="overtime_hours" filters={filters} align="center" />
                                    <SortableTh label="Holiday" sortKey="holiday_days" filters={filters} align="center" />
                                    <SortableTh label="Total Deductions" sortKey="total_deductions" filters={filters} />
                                    <SortableTh label="Net Pay" sortKey="net_pay" filters={filters} />
                                    <th className="w-20 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {(employees.data || []).map((employee) => {
                                    const p = payrollData[employee.id] || {};
                                    const hasData = (p.gross_pay || 0) !== 0 || (p.attendance_data?.days_worked || 0) !== 0;
                                    return (
                                        <tr key={employee.id} className="row-hover">
                                            <td>
                                                <div className="flex items-center gap-2">
                                                    <div className={`w-8 h-8 rounded-full bg-gradient-to-br ${avatarClass} flex items-center justify-center text-white text-xs font-bold flex-shrink-0`}>
                                                        {(employee.full_name || '?').charAt(0).toUpperCase()}
                                                    </div>
                                                    <div className="min-w-0">
                                                        <Link href={`/employees/${employee.id}`} className="font-semibold text-base-content no-underline hover:text-success truncate block">
                                                            {employee.full_name}
                                                        </Link>
                                                        <div className="text-xs text-subtle font-mono">{employee.employee_id}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="text-base-content truncate">{employee.department}</td>
                                            <td className="text-right font-semibold text-base-content">{fmt(p.base_pay)}</td>
                                            <td className="text-center font-semibold text-base-content">{p.attendance_data?.days_worked ?? 0}</td>
                                            <td className="text-center font-semibold text-warning">{fmtNum(p.attendance_data?.overtime_hours)}</td>
                                            <td className="text-center font-semibold text-secondary">{fmtNum(p.attendance_data?.holiday_days)}</td>
                                            <td className="text-right font-semibold text-error">-{fmt(p.total_deductions)}</td>
                                            <td className="text-right font-semibold text-success">{fmt(p.net_pay)}</td>
                                            <td className="text-center">
                                                <div className="flex gap-2 justify-center">
                                                    {!hasData ? (
                                                        <button className="btn btn-soft btn-sm btn-disabled" title="No payroll data">
                                                            <Icon name="tabler--eye" className="size-4" />
                                                        </button>
                                                    ) : (
                                                        <>
                                                            <Link href={`/payroll/${employee.id}${filters.period ? `?payroll_period_id=${filters.period}` : ''}`} className="btn btn-soft btn-info btn-sm" title="Full details">
                                                                <Icon name="tabler--eye" className="size-4" />
                                                            </Link>
                                                            <Link href={`/payroll/${employee.id}/payslip${filters.period ? `?payroll_period_id=${filters.period}` : ''}`} className="btn btn-soft btn-success btn-sm" title="Download payslip">
                                                                <Icon name="tabler--file-download" className="size-4" />
                                                            </Link>
                                                        </>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    <div className="md:hidden p-4 flex flex-col gap-3">
                        {(employees.data || []).map((employee) => {
                            const p = payrollData[employee.id] || {};
                            const hasData = (p.gross_pay || 0) !== 0 || (p.attendance_data?.days_worked || 0) !== 0;
                            return (
                                <div key={employee.id} className="card bg-base-100 border border-base-300 p-4">
                                    <div className="flex justify-between items-start mb-2">
                                        <div className="flex items-center gap-3">
                                            <div className={`w-10 h-10 rounded-full bg-linear-to-br ${avatarClass} flex items-center justify-center text-white text-sm font-bold flex-shrink-0`}>
                                                {(employee.full_name || '?').charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <Link href={`/employees/${employee.id}`} className="font-semibold text-base-content no-underline text-sm hover:text-success">
                                                    {employee.full_name}
                                                </Link>
                                                <div className="text-xs text-subtle font-mono">{employee.employee_id}</div>
                                            </div>
                                        </div>
                                        <span className="badge badge-soft badge-warning whitespace-nowrap">{employee.employment_status}</span>
                                    </div>

                                    <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content mt-2">
                                        <span><Icon name="tabler--building" className="size-3.5 inline" /> {employee.department}</span>
                                        <span><Icon name="tabler--briefcase" className="size-3.5 inline" /> {employee.position}</span>
                                    </div>

                                    <div className="mt-3 border-t border-base-300 flex flex-col gap-2 text-xs">
                                        <div className="flex justify-between items-center">
                                            <span className="text-base-content">Days Worked:</span>
                                            <span className="text-base-content font-semibold">{p.attendance_data?.days_worked ?? 0}</span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className="text-base-content">Basic Pay:</span>
                                            <span className="text-base-content font-semibold">{fmt(p.base_pay)}</span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className="text-base-content">Gross Pay:</span>
                                            <span className="text-base-content font-semibold">{fmt(p.gross_pay)}</span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className="text-base-content">Total Deductions:</span>
                                            <span className="text-error font-semibold">-{fmt(p.total_deductions)}</span>
                                        </div>
                                        <div className="flex justify-between items-center text-sm font-bold text-success pt-1 border-t border-base-300">
                                            <span>Net Pay:</span>
                                            <span>{fmt(p.net_pay)}</span>
                                        </div>
                                    </div>

                                    <div className="flex gap-2 flex-wrap mt-3 pt-3 border-t border-base-300">
                                        {!hasData ? (
                                            <button className="btn btn-soft btn-sm btn-disabled">
                                                <Icon name="tabler--eye" className="size-4" /> View Details
                                            </button>
                                        ) : (
                                            <>
                                                <Link href={`/payroll/${employee.id}${filters.period ? `?payroll_period_id=${filters.period}` : ''}`} className="btn btn-soft btn-info btn-sm">
                                                    <Icon name="tabler--eye" className="size-4" /> View Details
                                                </Link>
                                                <Link href={`/payroll/${employee.id}/payslip${filters.period ? `?payroll_period_id=${filters.period}` : ''}`} className="btn btn-soft btn-success btn-sm">
                                                    <Icon name="tabler--file-download" className="size-4" /> Payslip
                                                </Link>
                                            </>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </DataTable>
            </div>
        </AppLayout>
    );
}
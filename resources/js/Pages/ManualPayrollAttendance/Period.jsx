import { useMemo, useState } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import ConfirmButton from '../../components/ConfirmButton';

const fmtDate = (value, opts = { month: 'long', day: '2-digit', year: 'numeric' }) => {
    if (!value) return 'N/A';
    return new Date(value + 'T00:00:00').toLocaleDateString('en-US', opts);
};

const fmtMoney = (value) => '₱' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtNum = (value, digits = 2) => Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });

const EMPLOYMENT_STATUSES = ['Regular', 'Probationary', 'Contractual', 'Part-time'];

export default function ManualPayrollPeriod({ payrollPeriod, unencodedEmployees }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isHR = auth?.user?.role === 'hr';
    const canEdit = isAdmin || isHR;

    const inputs = payrollPeriod.payroll_inputs || [];
    const canFinalize = payrollPeriod.is_draft && inputs.length > 0;

    const [search, setSearch] = useState('');
    const [department, setDepartment] = useState('');
    const [empStatus, setEmpStatus] = useState('');

    const departments = useMemo(() => {
        const set = new Set(unencodedEmployees.map((e) => e.department).filter(Boolean));
        return [...set].sort();
    }, [unencodedEmployees]);

    const filtered = useMemo(() => {
        const term = search.toLowerCase();
        return unencodedEmployees.filter((e) => {
            const matchesSearch = !term || (e.first_name || '').toLowerCase().includes(term) || (e.last_name || '').toLowerCase().includes(term) || (e.employee_id || '').toLowerCase().includes(term);
            const matchesDept = !department || e.department === department;
            const matchesStatus = !empStatus || e.employment_status === empStatus;
            return matchesSearch && matchesDept && matchesStatus;
        });
    }, [unencodedEmployees, search, department, empStatus]);

    return (
        <AppLayout title={`Encode Attendance - ${payrollPeriod.cutoff_start ? fmtDate(payrollPeriod.cutoff_start, { month: 'short', day: '2-digit' }) : ''} to ${fmtDate(payrollPeriod.cutoff_end)}`}>
            <Head title="Encode Attendance" />
            <div className="p-2 sm:p-4">
                <Link href="/manual-payroll-attendance" className="inline-flex items-center text-sm text-subtle mb-4 gap-3 no-underline hover:text-primary">
                    <Icon name="tabler--arrow-left" className="size-4" /> Back to Periods
                </Link>

                <div className="flex items-center justify-between flex-wrap gap-3 mb-4">
                    <div>
                        <span className="badge badge-soft badge-info mb-2">
                            <Icon name="tabler--calendar" className="size-3.5" /> Payroll Period
                        </span>
                        <h2 className="text-lg font-bold text-base-content mt-2 mb-1">
                            {payrollPeriod.cutoff_start ? fmtDate(payrollPeriod.cutoff_start, { month: 'long', day: '2-digit' }) : 'N/A'} - {fmtDate(payrollPeriod.cutoff_end)}
                        </h2>
                        <p className="text-subtle m-0">
                            Payroll Date: {fmtDate(payrollPeriod.payroll_date)} &nbsp;|&nbsp; Status:{' '}
                            <span className="font-semibold">{payrollPeriod.status ? payrollPeriod.status.charAt(0).toUpperCase() + payrollPeriod.status.slice(1) : ''}</span>
                        </p>
                    </div>
                    {canEdit && (
                        <div className="flex gap-3">
                            {canFinalize && (
                                <ConfirmButton
                                    title="Finalize Payroll Period?"
                                    text="This action cannot be undone."
                                    confirmText="Yes, finalize it"
                                    cancelText="Cancel"
                                    url={`/payroll-periods/${payrollPeriod.id}/finalize`}
                                    method="post"
                                    confirmButtonColor="#10b981"
                                    className="btn btn-success btn-soft"
                                >
                                    <Icon name="tabler--circle-check" className="size-4" /> Finalize Payroll
                                </ConfirmButton>
                            )}
                            <button
                                onClick={() => router.reload({ only: ['payrollPeriod', 'unencodedEmployees'], preserveScroll: true })}
                                className="btn btn-primary"
                            >
                                <Icon name="tabler--refresh" className="size-4" /> Refresh Summary
                            </button>
                        </div>
                    )}
                </div>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <div className="bg-base-100 border border-base-300 rounded-lg p-4">
                        <div className="text-xs text-subtle mb-1">Total Employees</div>
                        <div className="font-bold text-xl sm:text-2xl text-base-content">{inputs.length}</div>
                    </div>
                    <div className="bg-base-100 border border-base-300 rounded-xl p-4">
                        <div className="text-xs text-subtle mb-1">Total Gross Pay</div>
                        <div className="text-lg sm:text-2xl font-bold text-success break-words">{fmtMoney(payrollPeriod.total_gross)}</div>
                    </div>
                    <div className="bg-base-100 border border-base-300 rounded-xl p-4">
                        <div className="text-xs text-subtle mb-1">Total Net Pay</div>
                        <div className="text-lg sm:text-2xl font-bold text-info break-words">{fmtMoney(payrollPeriod.total_net)}</div>
                    </div>
                    <div className="bg-base-100 border border-base-300 rounded-xl p-4">
                        <div className="text-xs text-subtle mb-1">Total Deductions</div>
                        <div className="text-lg sm:text-2xl font-bold text-error break-words">{fmtMoney(payrollPeriod.total_deductions)}</div>
                    </div>
                </div>

                <div className="card bg-base-100 border border-base-300 overflow-hidden p-0 mb-4">
                    <div className="px-6 py-4 border-b border-base-300">
                        <h3 className="text-sm font-bold text-base-content m-0">Encoded Employees</h3>
                        <p className="text-sm text-subtle m-0">Employees with attendance data for this period</p>
                    </div>

                    {inputs.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="table table-sm w-full">
                                <thead className="bg-base-200">
                                    <tr>
                                        <th className="text-xs text-subtle">Employee</th>
                                        <th className="text-right text-xs text-subtle">Days Worked</th>
                                        <th className="text-right text-xs text-subtle">OT Hours</th>
                                        <th className="text-right text-xs text-subtle">Late Hours</th>
                                        <th className="text-right text-xs text-subtle">Allowances</th>
                                        <th className="text-right text-xs text-subtle">Deductions</th>
                                        <th className="text-right text-xs text-subtle">Gross Pay</th>
                                        <th className="text-right text-xs text-subtle">Net Pay</th>
                                        <th className="text-center text-xs text-subtle">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {inputs.map((input) => (
                                        <tr key={input.id}>
                                            <td className="p-3">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex items-center justify-center font-bold text-xs rounded-lg w-8 h-8 bg-base-200">
                                                        {(input.employee?.first_name || '?').charAt(0).toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div className="font-semibold text-base-content text-sm">{input.employee?.first_name || 'Unknown'} {input.employee?.last_name || ''}</div>
                                                        <div className="text-xs text-subtle font-mono">{input.employee?.employee_id || 'N/A'}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="text-right p-3">{fmtNum(input.days_worked, 1)}</td>
                                            <td className="text-right p-3">{fmtNum(input.overtime_hours, 1)}</td>
                                            <td className="text-right p-3">{fmtNum(input.late_hours, 1)}</td>
                                            <td className="text-right text-success p-3">{fmtMoney(input.allowances)}</td>
                                            <td className="text-right text-error p-3">{fmtMoney(input.deductions)}</td>
                                            <td className="text-right font-semibold text-base-content p-3">{fmtMoney(input.gross_pay)}</td>
                                            <td className="text-right font-bold text-success p-3">{fmtMoney(input.net_pay)}</td>
                                            <td className="text-center p-3">
                                                {payrollPeriod.is_draft ? (
                                                    <Link href={`/manual-payroll-attendance/period/${payrollPeriod.id}/employee/${input.employee_id}`} className="btn btn-soft btn-info btn-xs">
                                                        <Icon name="tabler--pencil" className="size-3.5" /> Edit
                                                    </Link>
                                                ) : (
                                                    <span className="text-xs text-subtle">Finalized</span>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="text-center text-subtle p-10">
                            <Icon name="tabler--clipboard-text" className="size-6 mx-auto mb-3" />
                            No employees encoded yet for this period.
                        </div>
                    )}
                </div>

                {unencodedEmployees.length > 0 && payrollPeriod.is_draft && (
                    <div className="card bg-base-100 border border-base-300 overflow-hidden p-0">
                        <div className="px-6 py-4 border-b border-base-300">
                            <h3 className="text-sm font-bold text-base-content m-0">Pending Encoding</h3>
                            <p className="text-sm text-subtle m-0">Employees without attendance data for this period</p>
                        </div>

                        <div className="bg-base-200 p-4">
                            <div className="flex items-center flex-wrap gap-3">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by name or employee ID..."
                                    className="w-full sm:w-auto flex-1 min-w-40 text-sm input input-bordered input-sm"
                                />
                                <select className="select select-bordered select-sm min-w-40" value={department} onChange={(e) => setDepartment(e.target.value)}>
                                    <option value="">All Departments</option>
                                    {departments.map((d) => (
                                        <option key={d} value={d}>{d}</option>
                                    ))}
                                </select>
                                <select className="select select-bordered select-sm min-w-36" value={empStatus} onChange={(e) => setEmpStatus(e.target.value)}>
                                    <option value="">All Status</option>
                                    {EMPLOYMENT_STATUSES.map((s) => (
                                        <option key={s} value={s}>{s}</option>
                                    ))}
                                </select>
                                <button onClick={() => { setSearch(''); setDepartment(''); setEmpStatus(''); }} className="btn btn-soft btn-neutral btn-sm">
                                    <Icon name="tabler--x" className="size-4" /> Clear
                                </button>
                            </div>
                            <div className="text-xs text-subtle mt-2">
                                Showing <span className="font-semibold">{filtered.length}</span> of {unencodedEmployees.length} employees
                            </div>
                        </div>

                        <div className="p-4">
                            {filtered.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    {filtered.map((employee) => (
                                        <div key={employee.id} className="employee-card flex items-center justify-between border border-base-300 rounded-lg p-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex items-center justify-center font-bold text-xs text-subtle bg-base-200 rounded-lg w-8 h-8">
                                                    {(employee.first_name || '?').charAt(0).toUpperCase()}
                                                </div>
                                                <div>
                                                    <div className="font-semibold text-sm text-base-content">{employee.first_name || 'Unknown'} {employee.last_name || ''}</div>
                                                    <div className="text-xs text-subtle font-mono">{employee.employee_id || 'N/A'}</div>
                                                    <div className="text-xs text-subtle mt-1">{employee.department || 'N/A'}{employee.employment_status ? ` • ${employee.employment_status}` : ''}</div>
                                                </div>
                                            </div>
                                            <Link href={`/manual-payroll-attendance/period/${payrollPeriod.id}/employee/${employee.id}`} className="btn btn-primary btn-sm">
                                                <Icon name="tabler--keyboard" className="size-4" /> Encode
                                            </Link>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center text-subtle p-8">
                                    <Icon name="tabler--search" className="size-6 mx-auto mb-3" />
                                    No employees match your filters.
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
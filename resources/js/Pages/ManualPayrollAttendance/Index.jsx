import { useState } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import ConfirmButton from '../../components/ConfirmButton';

const MONTH_NAMES = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const fmtDate = (value, opts = { month: 'short', day: '2-digit', year: 'numeric' }) => {
    if (!value) return '—';
    return new Date(value + 'T00:00:00').toLocaleDateString('en-US', opts);
};

const fmtShort = (value) => {
    if (!value) return 'N/A';
    return new Date(value + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit' });
};

const fmtMoney = (value) => '₱' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export default function ManualPayrollAttendanceIndex({ periods, availableYears, availableMonths, filters }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isHR = auth?.user?.role === 'hr';
    const canCreate = isAdmin || isHR;

    const [year, setYear] = useState(filters?.year || '');
    const [month, setMonth] = useState(filters?.month || '');
    const [phase, setPhase] = useState(filters?.phase || '');
    const [status, setStatus] = useState(filters?.status || '');

    const applyFilters = (updates = {}) => {
        const params = {};
        if (year) params.year = year;
        if (month) params.month = month;
        if (phase) params.phase = phase;
        if (status) params.status = status;
        Object.entries(updates).forEach(([k, v]) => {
            if (v) params[k] = v;
            else delete params[k];
        });
        router.get('/manual-payroll-attendance', params, { preserveState: true, preserveScroll: true, replace: true });
    };

    const clearFilters = () => {
        setYear('');
        setMonth('');
        setPhase('');
        setStatus('');
        router.get('/manual-payroll-attendance', {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    return (
        <AppLayout title="Payroll Attendance Encoding">
            <Head title="Payroll Attendance Encoding" />
            <div className="p-2 sm:p-4">
                <div className="flex justify-between items-center flex-wrap gap-3 mb-6">
                    <div>
                        <span className="inline-flex items-center gap-1 rounded-full border border-transparent bg-highlight/12 px-2.5 py-0.5 text-xs font-medium text-highlight mb-2">
                            <Icon name="tabler--calendar-stats" className="size-3.5" /> Payroll Attendance Encoding
                        </span>
                        <h2 className="text-lg font-bold text-base-content mt-2 mb-1">Payroll Periods</h2>
                        <p className="text-dim-foreground m-0">Manually encode attendance totals, overtime, allowances, and deductions for payroll processing.</p>
                    </div>
                    <div className="flex gap-2">
                        {isAdmin && (
                            <Link href="/payroll-periods/archived" className="btn btn-soft btn-primary whitespace-nowrap">
                                <Icon name="tabler--archive" className="size-4" /> Archived
                            </Link>
                        )}
                        {canCreate && (
                            <Link href="/payroll-periods/create" className="btn btn-soft btn-error whitespace-nowrap">
                                <Icon name="tabler--plus" className="size-4" /> Create Payroll Period
                            </Link>
                        )}
                    </div>
                </div>

                <div className="rounded-xl border border-edge bg-card p-4 mb-4">
                    <div className="flex flex-wrap items-end gap-3">
                        <div className="form-control">
                            <label className="label label-text">Year</label>
                            <select className="select select-bordered select-sm w-32" value={year} onChange={(e) => { setYear(e.target.value); applyFilters({ year: e.target.value }); }}>
                                <option value="">All Years</option>
                                {availableYears.map((y) => (
                                    <option key={y} value={y}>{y}</option>
                                ))}
                            </select>
                        </div>
                        <div className="form-control">
                            <label className="label label-text">Month</label>
                            <select className="select select-bordered select-sm w-40" value={month} onChange={(e) => { setMonth(e.target.value); applyFilters({ month: e.target.value }); }}>
                                <option value="">All Months</option>
                                {availableMonths.map((m) => (
                                    <option key={m} value={m}>{MONTH_NAMES[m]}</option>
                                ))}
                            </select>
                        </div>
                        <div className="form-control">
                            <label className="label label-text">Phase</label>
                            <select className="select select-bordered select-sm w-36" value={phase} onChange={(e) => { setPhase(e.target.value); applyFilters({ phase: e.target.value }); }}>
                                <option value="">All Phases</option>
                                <option value="1">1st Half</option>
                                <option value="2">2nd Half</option>
                            </select>
                        </div>
                        <div className="form-control">
                            <label className="label label-text">Status</label>
                            <select className="select select-bordered select-sm w-36" value={status} onChange={(e) => { setStatus(e.target.value); applyFilters({ status: e.target.value }); }}>
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="finalized">Finalized</option>
                            </select>
                        </div>
                        <div className="flex gap-2">
                            <button onClick={clearFilters} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge px-3 text-xs font-medium no-underline transition-colors hover:bg-dim">
                                <Icon name="tabler--x" className="size-4" /> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-edge bg-card overflow-hidden p-0">
                    {periods.length > 0 ? (
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {periods.map((period) => (
                                    <div
                                        key={period.id}
                                        className="border border-primary rounded-xl p-5 cursor-pointer transition-all hover:border-error hover:shadow-md"
                                        onClick={() => router.get(`/manual-payroll-attendance/period/${period.id}`)}
                                    >
                                        <div className="flex justify-between items-start mb-3">
                                            <div>
                                                <div className="font-semibold text-base-content text-base">
                                                    {fmtShort(period.cutoff_start)} - {fmtDate(period.cutoff_end)}
                                                </div>
                                                <div className="text-dim-foreground text-xs mt-1">
                                                    Payroll Date: {fmtDate(period.payroll_date)}
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span className={`badge whitespace-nowrap ${period.status === 'finalized' ? 'badge-soft badge-success' : 'badge-soft badge-warning'}`}>
                                                    {period.status ? period.status.charAt(0).toUpperCase() + period.status.slice(1) : ''}
                                                </span>
                                                {isAdmin && (
                                                    <ConfirmButton
                                                        title="Archive Payroll Period?"
                                                        text={`"${period.period_label}" and all its encoded attendance data will be archived.`}
                                                        confirmText="Yes, archive it"
                                                        cancelText="Cancel"
                                                        url={`/payroll-periods/${period.id}/archive`}
                                                        method="patch"
                                                        className="btn btn-soft btn-error btn-xs"
                                                    >
                                                        <Icon name="tabler--trash" className="size-4" />
                                                    </ConfirmButton>
                                                )}
                                            </div>
                                        </div>
                                        <div className="flex gap-4 mt-3 pt-3 border-t border-edge/60 text-xs">
                                            <div>
                                                <span className="text-dim-foreground">Employees Encoded:</span>
                                                <span className="font-semibold text-base-content ml-1">{period.encoded_count}</span>
                                            </div>
                                            <div>
                                                <span className="text-dim-foreground">Total Gross:</span>
                                                <span className="font-semibold text-success ml-1">{fmtMoney(period.total_gross)}</span>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ) : (
                        <div className="py-16 px-6 text-center">
                            <Icon name="tabler--calendar-off" className="size-10 text-dim-foreground/70 mx-auto mb-4" />
                            <h3 className="text-dim-foreground font-semibold mb-2">No Payroll Periods Found</h3>
                            <p className="text-dim-foreground/70 mb-0">Create a payroll period to start encoding attendance.</p>
                            {canCreate && (
                                <Link href="/payroll-periods/create" className="btn btn-soft btn-error mt-4">
                                    <Icon name="tabler--plus" className="size-4" /> Create Payroll Period
                                </Link>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
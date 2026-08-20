import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import ConfirmButton from '../../components/ConfirmButton';

const fmtDate = (value, opts = { month: 'short', day: '2-digit', year: 'numeric' }) => {
    if (!value) return 'N/A';
    return new Date(value + 'T00:00:00').toLocaleDateString('en-US', opts);
};

const fmtMoney = (value) => '₱' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export default function ArchivedPayrollPeriods({ periods }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';

    return (
        <AppLayout title="Archived Payroll Periods">
            <Head title="Archived Payroll Periods" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/manual-payroll-attendance" className="text-subtle no-underline text-sm inline-flex items-center gap-1.5 mb-2 hover:text-primary">
                        <Icon name="tabler--arrow-left" className="size-4" /> Back to Attendance page
                    </Link>
                </div>

                <div className="card bg-base-100 border border-base-300 shadow-sm overflow-hidden p-0">
                    <div className="px-6 py-5 border-b border-base-300">
                        <span className="badge badge-soft badge-neutral mb-2">
                            <Icon name="tabler--archive" className="size-3.5" /> Archived Payroll Periods
                        </span>
                        <h2 className="text-base font-bold text-base-content mt-2 mb-1">Archived Periods</h2>
                        <p className="text-subtle text-sm m-0">Archived periods are read-only and can be restored if needed. {periods.length} archived payroll {periods.length === 1 ? 'period' : 'periods'}</p>
                    </div>

                    {periods.length > 0 ? (
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {periods.map((period) => (
                                    <div key={period.id} className="border border-base-300 rounded-xl p-5 transition-all hover:border-base-400 hover:shadow-md">
                                        <div className="flex justify-between items-start mb-3">
                                            <div>
                                                <div className="font-semibold text-base-content text-base">
                                                    {fmtDate(period.cutoff_start, { month: 'short', day: '2-digit' })} - {fmtDate(period.cutoff_end)}
                                                </div>
                                                <div className="text-subtle text-xs mt-1">Payroll Date: {fmtDate(period.payroll_date)}</div>
                                                <div className="text-faint text-xs mt-1">Created by: {period.created_by || 'N/A'}</div>
                                            </div>
                                            <span className="badge badge-soft badge-neutral whitespace-nowrap">Archived</span>
                                        </div>
                                        <div className="flex gap-4 mt-3 pt-3 border-t border-base-200 text-xs mb-4">
                                            <div>
                                                <span className="text-subtle">Employees Encoded:</span>
                                                <span className="font-semibold text-base-content ml-1">{period.encoded_count}</span>
                                            </div>
                                            <div>
                                                <span className="text-subtle">Total Gross:</span>
                                                <span className="font-semibold text-success ml-1">{fmtMoney(period.total_gross)}</span>
                                            </div>
                                        </div>
                                        {isAdmin && (
                                            <ConfirmButton
                                                title="Restore Payroll Period?"
                                                text={`"${period.period_label}" will be restored to draft status.`}
                                                icon="question"
                                                confirmText="Yes, restore it"
                                                cancelText="Cancel"
                                                confirmButtonColor="#10b981"
                                                url={`/payroll-periods/${period.id}/restore`}
                                                method="patch"
                                                className="btn btn-soft btn-success btn-sm w-full"
                                            >
                                                <Icon name="tabler--arrow-back-up" className="size-4" /> Restore
                                            </ConfirmButton>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    ) : (
                        <div className="py-16 px-6 text-center">
                            <Icon name="tabler--archive-off" className="size-10 text-faint mx-auto mb-4" />
                            <h3 className="text-subtle font-semibold mb-2">No Archived Periods</h3>
                            <p className="text-faint m-0">Archived payroll periods will appear here.</p>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
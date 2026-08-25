import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import StatusBadge from '../../components/StatusBadge';
import DataTable from '../../components/DataTable';
import ConfirmButton from '../../components/ConfirmButton';

const STATUS_META = {
    pending: 'warning',
    approved: 'success',
    rejected: 'error',
    cancelled: 'neutral',
};

const typeBadge = (type) => (type === 'weekend' ? 'info' : type === 'holiday' ? 'warning' : type === 'overtime' ? 'primary' : 'neutral');

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
};

const fmtTime = (value) => (value ? value.slice(0, 5) : null);

export default function WorkRequestsIndex({ workRequests, pendingCount, status, type }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isHR = auth?.user?.role === 'hr';
    const canManage = isAdmin || isHR;
    const hasFilters = status || type;

    return (
        <AppLayout>
            <Head title="Work Requests" />
            <div className="p-2 sm:p-4">
                <DataTable
                    title="Work Requests"
                    icon="tabler--notes"
                    tooltip={canManage ? 'Manage employee work requests' : 'View and manage your work requests'}
                    baseUrl="/work-requests"
                    actions={
                        canManage ? (
                            pendingCount > 0 && (
                                <Link href="/work-requests/pending" className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-warning/40 bg-warning/10 px-3 text-xs font-medium text-warning no-underline transition-colors hover:bg-warning/20">
                                    <Icon name="tabler--clock" className="size-4" /> Pending ({pendingCount})
                                </Link>
                            )
                        ) : (
                            <Link href="/work-requests/create" className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">
                                <Icon name="tabler--plus" className="size-4" /> New Request
                            </Link>
                        )
                    }
                    filters={[
                        {
                            name: 'status',
                            value: status || '',
                            options: [
                                { value: '', label: 'All Status' },
                                { value: 'pending', label: 'Pending' },
                                { value: 'approved', label: 'Approved' },
                                { value: 'rejected', label: 'Rejected' },
                                { value: 'cancelled', label: 'Cancelled' },
                            ],
                        },
                        {
                            name: 'type',
                            value: type || '',
                            options: [
                                { value: '', label: 'All Types' },
                                { value: 'weekend', label: 'Weekend' },
                                { value: 'holiday', label: 'Holiday' },
                                { value: 'overtime', label: 'Overtime' },
                            ],
                        },
                    ]}
                >
                    {workRequests.length > 0 ? (
                        <>
                            <div className="overflow-x-auto overflow-y-auto hidden md:block" style={{ maxHeight: '53vh' }}>
                                <table className="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            {canManage && <th>Employee</th>}
                                            <th>Type</th>
                                            <th>Work Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th className="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {workRequests.map((wr) => (
                                            <tr key={wr.id} className="row-hover border-b border-edge">
                                                <td className="text-base-content">{fmtDate(wr.created_at)}</td>
                                                {canManage && (
                                                    <td className="text-base-content font-semibold">{wr.employee?.full_name}</td>
                                                )}
                                                <td>
                                                    <StatusBadge type={typeBadge(wr.request_type)}>
                                                        {wr.request_type.charAt(0).toUpperCase() + wr.request_type.slice(1)}
                                                    </StatusBadge>
                                                </td>
                                                <td className="text-base-content">{fmtDate(wr.work_date)}</td>
                                                <td className="text-dim-foreground">
                                                    {fmtTime(wr.start_time) || '-'}
                                                    {wr.end_time ? ` - ${fmtTime(wr.end_time)}` : ''}
                                                </td>
                                                <td>
                                                    <StatusBadge type={STATUS_META[wr.status] ?? 'neutral'}>
                                                        {wr.status.charAt(0).toUpperCase() + wr.status.slice(1)}
                                                    </StatusBadge>
                                                </td>
                                                <td className="text-center">
                                                    <div className="flex gap-2 justify-center">
                                                        <Link href={`/work-requests/${wr.id}`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">
                                                            <Icon name="tabler--eye" className="size-4" />
                                                        </Link>
                                                        {!canManage && wr.status === 'pending' && (
                                                            <>
                                                                <Link href={`/work-requests/${wr.id}/edit`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-warning/40 bg-warning/10 px-3 text-xs font-medium text-warning no-underline transition-colors hover:bg-warning/20">
                                                                    <Icon name="tabler--pencil" className="size-4" />
                                                                </Link>
                                                                <ConfirmButton
                                                                    title="Cancel Work Request"
                                                                    text="Are you sure you want to cancel this work request?"
                                                                    confirmText="Yes, Cancel"
                                                                    url={`/work-requests/${wr.id}`}
                                                                    method="delete"
                                                                    className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10"
                                                                >
                                                                    <Icon name="tabler--x" className="size-4" />
                                                                </ConfirmButton>
                                                            </>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <div className="md:hidden p-4 flex flex-col gap-3">
                                {workRequests.map((wr) => (
                                    <div key={wr.id} className="rounded-xl border border-edge bg-card p-4">
                                        <div className="flex justify-between items-start mb-2">
                                            <div>
                                                <div className="text-sm text-base-content font-semibold">{fmtDate(wr.created_at)}</div>
                                                {canManage && (
                                                    <div className="text-xs text-dim-foreground">{wr.employee?.full_name}</div>
                                                )}
                                            </div>
                                            <StatusBadge type={STATUS_META[wr.status] ?? 'neutral'}>
                                                {wr.status.charAt(0).toUpperCase() + wr.status.slice(1)}
                                            </StatusBadge>
                                        </div>

                                        <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-dim-foreground mt-2">
                                            <StatusBadge type={typeBadge(wr.request_type)}>
                                                {wr.request_type.charAt(0).toUpperCase() + wr.request_type.slice(1)}
                                            </StatusBadge>
                                            <span><Icon name="ph--calendar-fill" className="size-3.5 inline" /> {fmtDate(wr.work_date)}</span>
                                            <span><Icon name="ph--clock-fill" className="size-3.5 inline" /> {fmtTime(wr.start_time) || '-'}{wr.end_time ? ` - ${fmtTime(wr.end_time)}` : ''}</span>
                                        </div>

                                        <div className="flex gap-2 flex-wrap mt-3 pt-3 border-t border-edge/60">
                                            <Link href={`/work-requests/${wr.id}`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">
                                                <Icon name="ph--eye-fill" className="size-4" /> View
                                            </Link>
                                            {!canManage && wr.status === 'pending' && (
                                                <>
                                                    <Link href={`/work-requests/${wr.id}/edit`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-warning/40 bg-warning/10 px-3 text-xs font-medium text-warning no-underline transition-colors hover:bg-warning/20">
                                                        <Icon name="ph--pencil-fill" className="size-4" /> Edit
                                                    </Link>
                                                    <ConfirmButton
                                                        title="Cancel Work Request"
                                                        text="Are you sure you want to cancel this work request?"
                                                        confirmText="Yes, Cancel"
                                                        url={`/work-requests/${wr.id}`}
                                                        method="delete"
                                                        className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10"
                                                    >
                                                        <Icon name="ph--x" className="size-4" /> Cancel
                                                    </ConfirmButton>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </>
                    ) : (
                        <div className="card p-12 text-center">
                            <Icon name="tabler--calendar-off" className="size-10 text-dim-foreground/70 mx-auto mb-3" />
                            <h3 className="text-dim-foreground font-semibold mb-2">No Work Requests Found</h3>
                            <p className="text-dim-foreground/70 mb-6">
                                {!canManage
                                    ? `${hasFilters ? 'Try adjusting your filters or' : 'Get started by'} creating a new work request.`
                                    : 'No work requests match your current filters.'}
                            </p>
                            {!canManage && (
                                <Link href="/work-requests/create" className="btn btn-soft btn-primary">
                                    <Icon name="tabler--plus" className="size-4" /> New Request
                                </Link>
                            )}
                        </div>
                    )}
                </DataTable>
            </div>
        </AppLayout>
    );
}

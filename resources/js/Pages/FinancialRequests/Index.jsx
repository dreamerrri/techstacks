import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import StatusBadge from '../../Components/StatusBadge';
import DataTable from '../../Components/DataTable';
import ConfirmButton from '../../Components/ConfirmButton';

const STATUS_META = {
    pending: 'warning',
    approved: 'success',
    rejected: 'error',
    cancelled: 'neutral',
};

const typeBadge = (type) => (type === 'cash_advance' ? 'info' : 'success');

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
};

const fmtMoney = (value) => `₱${Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const typeLabel = (type) => (type === 'cash_advance' ? 'Cash Advance' : 'Reimbursement');

export default function FinancialRequestsIndex({ financialRequests, pendingCount, status, type }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isHR = auth?.user?.role === 'hr';
    const canManage = isAdmin || isHR;
    const hasFilters = status || type;

    return (
        <AppLayout>
            <Head title="Financial Requests" />
            <div className="p-2 sm:p-4">
                <DataTable
                    title="Financial Requests"
                    icon="tabler--cash"
                    tooltip={canManage ? 'Manage employee cash advance and reimbursement requests' : 'View and manage your cash advance and reimbursement requests'}
                    baseUrl="/financial-requests"
                    actions={
                        canManage ? (
                            pendingCount > 0 && (
                                <Link href="/financial-requests?status=pending" className="btn btn-soft btn-warning btn-sm">
                                    <Icon name="tabler--clock" className="size-4" /> Pending ({pendingCount})
                                </Link>
                            )
                        ) : (
                            <Link href="/financial-requests/create" className="btn btn-soft btn-primary btn-sm">
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
                                { value: 'cash_advance', label: 'Cash Advance' },
                                { value: 'reimbursement', label: 'Reimbursement' },
                            ],
                        },
                    ]}
                >
                    {financialRequests.length > 0 ? (
                        <>
                            <div className="overflow-x-auto overflow-y-auto hidden md:block" style={{ maxHeight: '53vh' }}>
                                <table className="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            {canManage && <th>Employee</th>}
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th className="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {financialRequests.map((req) => (
                                            <tr key={req.id} className="row-hover border-b border-base-300">
                                                <td className="text-base-content">{fmtDate(req.created_at)}</td>
                                                {canManage && (
                                                    <td className="text-base-content font-semibold">{req.employee?.full_name}</td>
                                                )}
                                                <td>
                                                    <StatusBadge type={typeBadge(req.request_type)}>{typeLabel(req.request_type)}</StatusBadge>
                                                </td>
                                                <td className="text-base-content font-semibold">{fmtMoney(req.amount)}</td>
                                                <td className="text-base-content/60">{req.description || '-'}</td>
                                                <td>
                                                    <StatusBadge type={STATUS_META[req.status] ?? 'neutral'}>
                                                        {req.status.charAt(0).toUpperCase() + req.status.slice(1)}
                                                    </StatusBadge>
                                                </td>
                                                <td className="text-center">
                                                    <div className="flex gap-2 justify-center">
                                                        <Link href={`/financial-requests/${req.id}`} className="btn btn-soft btn-info btn-sm">
                                                            <Icon name="tabler--eye" className="size-4" />
                                                        </Link>
                                                        {!canManage && req.status === 'pending' && (
                                                            <>
                                                                <Link href={`/financial-requests/${req.id}/edit`} className="btn btn-soft btn-warning btn-sm">
                                                                    <Icon name="tabler--pencil" className="size-4" />
                                                                </Link>
                                                                <ConfirmButton
                                                                    title="Cancel Financial Request"
                                                                    text="Are you sure you want to cancel this financial request?"
                                                                    confirmText="Yes, Cancel"
                                                                    url={`/financial-requests/${req.id}`}
                                                                    method="delete"
                                                                    className="btn btn-soft btn-error btn-sm"
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
                                {financialRequests.map((req) => (
                                    <div key={req.id} className="card bg-base-100 border border-base-300 p-4">
                                        <div className="flex justify-between items-start mb-2">
                                            <div>
                                                <div className="text-sm text-base-content font-semibold">{fmtDate(req.created_at)}</div>
                                                {canManage && (
                                                    <div className="text-xs text-base-content/60">{req.employee?.full_name}</div>
                                                )}
                                            </div>
                                            <StatusBadge type={STATUS_META[req.status] ?? 'neutral'}>
                                                {req.status.charAt(0).toUpperCase() + req.status.slice(1)}
                                            </StatusBadge>
                                        </div>

                                        <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                                            <StatusBadge type={typeBadge(req.request_type)}>{typeLabel(req.request_type)}</StatusBadge>
                                            <span className="font-semibold text-base-content">{fmtMoney(req.amount)}</span>
                                        </div>

                                        {req.description && (
                                            <div className="text-xs text-base-content/60 mt-2">{req.description}</div>
                                        )}

                                        <div className="flex gap-2 flex-wrap mt-3 pt-3 border-t border-base-200">
                                            <Link href={`/financial-requests/${req.id}`} className="btn btn-soft btn-info btn-sm">
                                                <Icon name="tabler--eye" className="size-4" /> View
                                            </Link>
                                            {!canManage && req.status === 'pending' && (
                                                <>
                                                    <Link href={`/financial-requests/${req.id}/edit`} className="btn btn-soft btn-warning btn-sm">
                                                        <Icon name="tabler--pencil" className="size-4" /> Edit
                                                    </Link>
                                                    <ConfirmButton
                                                        title="Cancel Financial Request"
                                                        text="Are you sure you want to cancel this financial request?"
                                                        confirmText="Yes, Cancel"
                                                        url={`/financial-requests/${req.id}`}
                                                        method="delete"
                                                        className="btn btn-soft btn-error btn-sm"
                                                    >
                                                        <Icon name="tabler--x" className="size-4" /> Cancel
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
                            <Icon name="tabler--cash" className="size-10 text-base-content/30 mx-auto mb-3" />
                            <h3 className="text-base-content/60 font-semibold mb-2">No Financial Requests Found</h3>
                            <p className="text-base-content/40 mb-6">
                                {!canManage
                                    ? `${hasFilters ? 'Try adjusting your filters or' : 'Get started by'} creating a new financial request.`
                                    : 'No financial requests match your current filters.'}
                            </p>
                            {!canManage && (
                                <Link href="/financial-requests/create" className="btn btn-soft btn-primary">
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
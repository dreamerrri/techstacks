import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import DetailRow from '../../components/DetailRow';
import StatusBadge from '../../components/StatusBadge';
import ConfirmButton from '../../components/ConfirmButton';
import { toast } from '../../components/toast';

const STATUS_META = {
    pending: 'warning',
    approved: 'success',
    rejected: 'error',
    cancelled: 'neutral',
};

const fmtDate = (value, opts = {}) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric', ...opts });
};

const fmtDateTime = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const fmtMoney = (value) => `₱${Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

export default function FinancialRequestsShow({ financialRequest }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isHR = auth?.user?.role === 'hr';
    const canManage = isAdmin || isHR;

    const isCashAdvance = financialRequest.request_type === 'cash_advance';
    const isApproved = financialRequest.status === 'approved';
    const isPending = financialRequest.status === 'pending';

    const amountPaid = Number(financialRequest.amount_paid || 0);
    const totalAmount = Number(financialRequest.amount || 0);
    const remaining = Number(financialRequest.remaining_balance || 0);
    const progress = Number(financialRequest.payment_progress || 0);
    const fullyPaid = isCashAdvance && amountPaid >= totalAmount;

    const maxAmount = isCashAdvance ? Number(financialRequest.employee?.basic_salary || 0) : 15000;
    const limitDescription = isCashAdvance ? '100% of monthly salary' : '₱15,000 maximum';

    const [rejectOpen, setRejectOpen] = useState(false);
    const [rejectReason, setRejectReason] = useState('');
    const [rejecting, setRejecting] = useState(false);

    const approve = () => {
        router.post(`/financial-requests/${financialRequest.id}/approve`, {}, {
            preserveScroll: true,
            onError: (err) => toast('error', Object.values(err)[0] ?? 'Failed to approve request.'),
        });
    };

    const confirmReject = () => {
        if (!rejectReason.trim()) {
            toast('error', 'Please provide a rejection reason.');
            return;
        }
        setRejecting(true);
        router.post(`/financial-requests/${financialRequest.id}/reject`, { rejection_reason: rejectReason.trim() }, {
            preserveScroll: true,
            onError: (err) => {
                setRejecting(false);
                toast('error', Object.values(err)[0] ?? 'Failed to reject request.');
            },
            onFinish: () => {
                setRejecting(false);
                setRejectOpen(false);
            },
        });
    };

    const payments = (financialRequest.cash_advance_payments || []).slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

    return (
        <AppLayout>
            <Head title="Financial Request Details" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/financial-requests" className="back-link text-dim-foreground no-underline text-sm hover:text-brand">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Requests
                    </Link>
                </div>

                <div className="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg px-3 py-1.5 mb-4">
                    <Icon name="ph--cash-register-fill" className="size-4 inline" /> Request Details
                </div>
                <h2 className="text-lg font-bold text-base-content mb-1">Financial Request #{financialRequest.id}</h2>
                <p className="text-dim-foreground text-sm mb-6">{isCashAdvance ? 'Cash Advance' : 'Reimbursement'} Request</p>

                <div className="rounded-xl border border-edge bg-card p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 className="text-sm font-bold text-base-content mb-3 flex items-center gap-2">
                                <Icon name="ph--user-fill" className="size-4 text-error" /> Employee Information
                            </h3>
                            <div className="flex flex-col text-sm">
                                <DetailRow label="Name">{financialRequest.employee?.full_name}</DetailRow>
                                <DetailRow label="Employee ID">{financialRequest.employee?.employee_id}</DetailRow>
                                <DetailRow label="Department" border={false}>{financialRequest.employee?.department}</DetailRow>
                            </div>
                        </div>

                        <div>
                            <h3 className="text-sm font-bold text-base-content mb-3 flex items-center gap-2">
                                <Icon name="ph--file-text-fill" className="size-4 text-error" /> Request Information
                            </h3>
                            <div className="flex flex-col text-sm">
                                <DetailRow label="Request Type">{isCashAdvance ? 'Cash Advance' : 'Reimbursement'}</DetailRow>
                                <DetailRow label="Amount">
                                    <span className="font-bold text-error text-base">{fmtMoney(totalAmount)}</span>
                                </DetailRow>
                                <DetailRow label="Maximum Allowed">
                                    <span className="text-dim-foreground">{fmtMoney(maxAmount)} ({limitDescription})</span>
                                </DetailRow>
                                <DetailRow label="Request Date">{fmtDate(financialRequest.request_date)}</DetailRow>
                                <DetailRow label="Status" border={false}>
                                    <StatusBadge type={STATUS_META[financialRequest.status] ?? 'neutral'}>
                                        {financialRequest.status.charAt(0).toUpperCase() + financialRequest.status.slice(1)}
                                    </StatusBadge>
                                </DetailRow>
                            </div>
                        </div>
                    </div>

                    {financialRequest.description && (
                        <div className="mt-4">
                            <h3 className="text-sm font-bold text-base-content mb-2">Description</h3>
                            <p className="text-sm text-dim-foreground bg-base-200 rounded-lg p-4">{financialRequest.description}</p>
                        </div>
                    )}

                    {financialRequest.reason && (
                        <div className="mt-4">
                            <h3 className="text-sm font-bold text-base-content mb-2">Reason</h3>
                            <p className="text-sm text-dim-foreground bg-base-200 rounded-lg p-4">{financialRequest.reason}</p>
                        </div>
                    )}

                    {!isCashAdvance && financialRequest.receipt_image && (
                        <div className="mt-4">
                            <h3 className="text-sm font-bold text-base-content mb-2">Receipt</h3>
                            <div className="bg-base-200 rounded-lg p-4">
                                <img
                                    src={`/storage/${financialRequest.receipt_image}`}
                                    alt="Receipt"
                                    className="max-w-full h-auto rounded-lg border border-edge"
                                    style={{ maxHeight: '400px' }}
                                />
                            </div>
                        </div>
                    )}

                    {isCashAdvance && isApproved && (
                        <div className="mt-4">
                            <h3 className="text-sm font-bold text-base-content mb-2">Payment Information</h3>
                            <div className="bg-base-200 rounded-lg p-4">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-dim-foreground">Total Amount:</span>
                                    <span className="font-bold text-base-content">{fmtMoney(totalAmount)}</span>
                                </div>
                                <div className="flex justify-between items-center mt-2">
                                    <span className="text-sm text-dim-foreground">Amount Paid:</span>
                                    <span className="font-bold text-success">{fmtMoney(amountPaid)}</span>
                                </div>
                                <div className="flex justify-between items-center mt-2 pt-2 border-t border-edge">
                                    <span className="text-sm text-dim-foreground">Remaining Balance:</span>
                                    <span className="font-bold text-error">{fmtMoney(remaining)}</span>
                                </div>
                                <div className="flex justify-between items-center mt-2 pt-2 border-t border-edge">
                                    <span className="text-sm text-dim-foreground">Payment Progress:</span>
                                    <span className="font-semibold text-base-content">{progress}%</span>
                                </div>
                                <div className="mt-3 pt-3 border-t border-edge">
                                    <p className="text-xs text-dim-foreground text-center">
                                        <Icon name="ph--info-fill" className="size-3.5 inline" /> Payments are automatically deducted at 50% of net pay per payroll cutoff
                                    </p>
                                </div>
                                {fullyPaid && (
                                    <div className="mt-3 text-center">
                                        <StatusBadge type="success">Fully Paid</StatusBadge>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {isCashAdvance && isApproved && payments.length > 0 && (
                        <div className="mt-4">
                            <h3 className="text-sm font-bold text-base-content mb-2">Payment History</h3>
                            <div className="bg-base-200 rounded-lg p-4">
                                <div className="overflow-x-auto">
                                    <table className="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Description</th>
                                                <th>Payroll Period</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {payments.map((payment) => (
                                                <tr key={payment.id}>
                                                    <td>{fmtDate(payment.created_at)}</td>
                                                    <td className="text-success font-semibold">{fmtMoney(payment.amount)}</td>
                                                    <td>{payment.description || '-'}</td>
                                                    <td>
                                                        {payment.payroll_period
                                                            ? `${fmtDate(payment.payroll_period.cutoff_start, { month: 'short', day: '2-digit' })} - ${fmtDate(payment.payroll_period.cutoff_end)}`
                                                            : 'Manual'}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    )}

                    {financialRequest.approved_by && (
                        <div className="mt-4">
                            <h3 className="text-sm font-bold text-base-content mb-2">Approval Information</h3>
                            <div className="bg-base-200 rounded-lg p-4">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-dim-foreground">Approved By:</span>
                                    <span className="font-semibold text-base-content">{financialRequest.approved_by?.name}</span>
                                </div>
                                <div className="flex justify-between items-center mt-2">
                                    <span className="text-sm text-dim-foreground">Approved At:</span>
                                    <span className="font-semibold text-base-content">{fmtDateTime(financialRequest.approved_at)}</span>
                                </div>
                                {financialRequest.rejection_reason && (
                                    <div className="mt-3 pt-3 border-t border-edge">
                                        <span className="text-sm text-dim-foreground">Rejection Reason:</span>
                                        <p className="text-sm text-error mt-1">{financialRequest.rejection_reason}</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                {(canManage && isPending) || (!canManage && isPending) ? (
                    <div className="rounded-xl border border-edge bg-card p-4 mt-4">
                        <div className="flex gap-3 flex-wrap">
                            {canManage && (
                                <>
                                    <button type="button" onClick={approve} className="btn btn-soft btn-success">
                                        <Icon name="ph--check-fill" className="size-4" /> Approve
                                    </button>
                                    <button type="button" onClick={() => setRejectOpen(true)} className="btn btn-soft btn-error">
                                        <Icon name="ph--x-fill" className="size-4" /> Reject
                                    </button>
                                </>
                            )}
                            {!canManage && (
                                <>
                                    <Link href={`/financial-requests/${financialRequest.id}/edit`} className="btn btn-soft btn-warning">
                                        <Icon name="ph--pencil-fill" className="size-4" /> Edit
                                    </Link>
                                    <ConfirmButton
                                        title="Cancel Financial Request"
                                        text="Are you sure you want to cancel this financial request?"
                                        confirmText="Yes, Cancel"
                                        url={`/financial-requests/${financialRequest.id}`}
                                        method="delete"
                                        className="btn btn-soft btn-error"
                                    >
                                        <Icon name="ph--x-fill" className="size-4" /> Cancel
                                    </ConfirmButton>
                                </>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>

            {rejectOpen && (
                <div className="modal modal-open" style={{ display: 'flex' }}>
                    <div className="modal-box">
                        <h3 className="font-bold text-lg mb-4">Reject Financial Request</h3>
                        <div className="py-4">
                            <label className="label">
                                <span className="label-text">Rejection Reason <span className="text-error">*</span></span>
                            </label>
                            <textarea
                                rows="4"
                                maxLength="1000"
                                value={rejectReason}
                                onChange={(e) => setRejectReason(e.target.value)}
                                className="textarea textarea-bordered w-full"
                                placeholder="Please provide a reason for rejection..."
                            />
                        </div>
                        <div className="modal-action">
                            <button type="button" onClick={() => setRejectOpen(false)} className="btn btn-soft" disabled={rejecting}>Cancel</button>
                            <button type="button" onClick={confirmReject} className="btn btn-soft btn-error" disabled={rejecting}>
                                {rejecting ? 'Rejecting...' : 'Confirm Rejection'}
                            </button>
                        </div>
                    </div>
                    <div className="modal-backdrop" onClick={() => !rejecting && setRejectOpen(false)}></div>
                </div>
            )}
        </AppLayout>
    );
}
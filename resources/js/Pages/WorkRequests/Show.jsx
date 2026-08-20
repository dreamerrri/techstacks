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

const fmt = (value, opts = {}) => {
    if (!value) return '—';
    return new Date(value).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', ...opts });
};

const fmtTime = (value) => (value ? value.slice(0, 5) : null);

export default function WorkRequestsShow({ workRequest }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isHR = auth?.user?.role === 'hr';
    const canManage = isAdmin || isHR;
    const isPending = workRequest.status === 'pending';

    const [rejectOpen, setRejectOpen] = useState(false);
    const [rejectReason, setRejectReason] = useState('');
    const [rejecting, setRejecting] = useState(false);

    const approve = () => {
        router.post(`/work-requests/${workRequest.id}/approve`, {}, {
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
        router.post(`/work-requests/${workRequest.id}/reject`, { rejection_reason: rejectReason.trim() }, {
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

    const statusColor = workRequest.status === 'pending' ? 'text-warning' : workRequest.status === 'approved' ? 'text-success' : workRequest.status === 'rejected' ? 'text-error' : 'text-base-content';
    const statusBg = workRequest.status === 'pending' ? 'bg-warning/10 border border-warning' : workRequest.status === 'approved' ? 'bg-success/10 border border-success' : workRequest.status === 'rejected' ? 'bg-error/10 border border-error' : 'bg-base-200 border border-base-300';

    return (
        <AppLayout>
            <Head title="Work Request Details" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/work-requests" className="back-link text-subtle no-underline text-sm hover:text-primary">
                        <Icon name="tabler--arrow-left" className="size-4" /> Back to Requests
                    </Link>
                </div>

                <div className="card bg-base-100 shadow-sm p-6">
                    <div className="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <div>
                            <h2 className="text-lg font-bold text-base-content mb-1">Work Request #{workRequest.id}</h2>
                            <p className="text-subtle text-sm m-0">Submitted on {fmt(workRequest.created_at)}</p>
                        </div>
                    </div>

                    <div className={`p-4 rounded-lg mb-6 ${statusBg}`}>
                        <div className="flex items-center gap-3">
                            <Icon name={workRequest.status === 'pending' ? 'ph--clock-fill' : workRequest.status === 'approved' ? 'ph--check-circle-fill' : workRequest.status === 'rejected' ? 'ph--x-circle-fill' : 'ph--prohibit-fill'} className={`text-2xl ${statusColor}`} />
                            <div>
                                <div className={`text-base font-bold ${statusColor}`}>
                                    {workRequest.status.charAt(0).toUpperCase() + workRequest.status.slice(1)}
                                </div>
                                {workRequest.status === 'approved' && workRequest.approved_at && (
                                    <div className="text-xs text-muted">
                                        Approved on {fmt(workRequest.approved_at)}
                                        {workRequest.approved_by ? ` by ${workRequest.approved_by.name}` : ''}
                                    </div>
                                )}
                                {workRequest.status === 'rejected' && workRequest.rejection_reason && (
                                    <div className="text-xs text-error mt-1">Reason: {workRequest.rejection_reason}</div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="card bg-base-200/50 border border-base-300 shadow-sm p-4 mb-4">
                        <h3 className="text-muted mb-3 text-sm font-bold flex items-center gap-2">
                            <Icon name="tabler--info-circle" className="size-4" /> Details
                        </h3>
                        <div className="flex flex-wrap gap-2 mb-2">
                            {canManage && (
                                <span className="badge badge-soft badge-neutral gap-1.5">
                                    <Icon name="tabler--user" className="size-4" /> {workRequest.employee?.full_name}
                                </span>
                            )}
                            <span className="badge badge-soft badge-neutral gap-1.5">
                                <Icon name="tabler--calendar" className="size-4" /> {new Date(workRequest.work_date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}
                            </span>
                            {(workRequest.start_time || workRequest.end_time) && (
                                <span className="badge badge-soft badge-neutral gap-1.5">
                                    <Icon name="tabler--clock" className="size-4" />
                                    {fmtTime(workRequest.start_time) || 'Not specified'}
                                    {workRequest.end_time ? ` - ${fmtTime(workRequest.end_time)}` : ''}
                                </span>
                            )}
                        </div>

                        <div className="flex flex-col text-sm">
                            <DetailRow label="Request Type">
                                <StatusBadge type={workRequest.request_type === 'weekend' ? 'info' : workRequest.request_type === 'holiday' ? 'warning' : 'primary'}>
                                    {workRequest.request_type.charAt(0).toUpperCase() + workRequest.request_type.slice(1)} Work
                                </StatusBadge>
                            </DetailRow>
                            <DetailRow label="Work Date">
                                {new Date(workRequest.work_date).toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: '2-digit', year: 'numeric' })}
                            </DetailRow>
                            <DetailRow label="Time" border={false}>
                                {fmtTime(workRequest.start_time) || 'Not specified'}
                                {workRequest.end_time ? ` - ${fmtTime(workRequest.end_time)}` : ''}
                            </DetailRow>
                            {workRequest.estimated_hours != null && (
                                <DetailRow label="Estimated Hours">
                                    {Number(workRequest.estimated_hours).toFixed(2)} hours
                                </DetailRow>
                            )}
                            {workRequest.calculated_overtime_hours != null && (
                                <DetailRow label="Overtime Hours" border={false}>
                                    {Number(workRequest.calculated_overtime_hours).toFixed(2)} hours
                                </DetailRow>
                            )}
                        </div>
                    </div>

                    {workRequest.reason && (
                        <div className="card bg-base-200/50 border border-base-300 shadow-sm p-4 mb-4">
                            <h3 className="flex items-center gap-2 text-muted text-sm font-bold">
                                <Icon name="tabler--message" className="size-4" /> Reason
                            </h3>
                            <div className="text-sm text-base-content mt-2">{workRequest.reason}</div>
                        </div>
                    )}

                    {(isPending && canManage) || (!canManage && isPending) ? (
                        <div className="card bg-base-200/50 border border-base-300 shadow-sm p-4">
                            <h3 className="m-0 mb-4 text-sm font-bold text-base-content">Actions</h3>
                            <div className="flex gap-3 flex-wrap">
                                {!canManage && (
                                    <>
                                        <Link href={`/work-requests/${workRequest.id}/edit`} className="btn btn-soft btn-warning">
                                            <Icon name="ph--pencil-fill" className="size-4" /> Edit Request
                                        </Link>
                                        <ConfirmButton
                                            title="Cancel Work Request"
                                            text="Are you sure you want to cancel this work request?"
                                            confirmText="Yes, Cancel"
                                            url={`/work-requests/${workRequest.id}`}
                                            method="delete"
                                            className="btn btn-soft btn-error"
                                        >
                                            <Icon name="ph--x" className="size-4" /> Cancel Request
                                        </ConfirmButton>
                                    </>
                                )}
                                {canManage && (
                                    <>
                                        <button type="button" onClick={approve} className="btn btn-soft btn-success">
                                            <Icon name="tabler--check" className="size-4" /> Approve
                                        </button>
                                        <button type="button" onClick={() => setRejectOpen(true)} className="btn btn-soft btn-error">
                                            <Icon name="ph--x" className="size-4" /> Reject
                                        </button>
                                    </>
                                )}
                            </div>
                        </div>
                    ) : null}
                </div>
            </div>

            {rejectOpen && (
                <div className="modal modal-open" style={{ display: 'flex' }}>
                    <div className="modal-box">
                        <h3 className="font-bold text-lg mb-4">Reject Work Request</h3>
                        <div className="py-4">
                            <label className="label">
                                <span className="label-text">Rejection Reason <span className="text-error">*</span></span>
                            </label>
                            <textarea
                                rows="4"
                                maxLength="500"
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

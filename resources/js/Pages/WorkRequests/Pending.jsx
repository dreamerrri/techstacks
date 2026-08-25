import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import StatusBadge from '../../components/StatusBadge';
import { toast } from '../../components/toast';

const typeBadge = (type) => (type === 'weekend' ? 'info' : type === 'holiday' ? 'warning' : 'primary');

const fmtDate = (value) => {
    if (!value) return '—';
    const d = new Date(value);
    return `${d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })} · ${d.toLocaleDateString('en-US', { weekday: 'long' })}`;
};

const fmtTime = (value) => (value ? value.slice(0, 5) : null);

export default function WorkRequestsPending({ pendingRequests }) {
    const [rejectOpen, setRejectOpen] = useState(false);
    const [targetId, setTargetId] = useState(null);
    const [rejectReason, setRejectReason] = useState('');
    const [rejecting, setRejecting] = useState(false);

    const openReject = (id) => {
        setTargetId(id);
        setRejectReason('');
        setRejectOpen(true);
    };

    const approve = (id) => {
        router.post(`/work-requests/${id}/approve`, {}, {
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
        router.post(`/work-requests/${targetId}/reject`, { rejection_reason: rejectReason.trim() }, {
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

    return (
        <AppLayout>
            <Head title="Pending Work Requests" />
            <div className="p-2 sm:p-4">
                <div className="card w-full min-w-0 border border-edge flex flex-col p-0">
                    <div className="sticky top-0 px-4 sm:px-7 pt-5 rounded-t-2xl bg-base-100 z-10">
                        <div className="flex justify-between items-center mb-4 flex-wrap gap-2">
                            <h2 className="text-sm font-semibold uppercase tracking-widest text-dim-foreground/70 flex items-center gap-2 m-0">
                                <Icon name="tabler--clock" className="size-4 text-brand" />
                                <span>Pending Work Requests</span>
                            </h2>
                            <Link href="/work-requests" className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-warning/40 bg-warning/10 px-3 text-xs font-medium text-warning no-underline transition-colors hover:bg-warning/20">
                                <Icon name="tabler--notes" className="size-4" /> All Requests
                            </Link>
                        </div>
                    </div>

                    {pendingRequests.length > 0 ? (
                        <>
                            <div className="overflow-x-auto overflow-y-auto hidden md:block" style={{ maxHeight: '53vh' }}>
                                <table className="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Type</th>
                                            <th>Work Date</th>
                                            <th>Time</th>
                                            <th>Reason</th>
                                            <th className="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {pendingRequests.map((req) => (
                                            <tr key={req.id} className="row-hover">
                                                <td>
                                                    <div className="font-semibold text-base-content">{req.employee?.full_name}</div>
                                                    <div className="text-xs text-dim-foreground">{req.employee?.employee_id}</div>
                                                    <div className="text-xs text-dim-foreground">{req.employee?.position}</div>
                                                </td>
                                                <td>
                                                    <StatusBadge type={typeBadge(req.request_type)}>
                                                        {req.request_type.charAt(0).toUpperCase() + req.request_type.slice(1)}
                                                    </StatusBadge>
                                                </td>
                                                <td className="text-sm text-base-content">{fmtDate(req.work_date)}</td>
                                                <td className="text-sm text-base-content">
                                                    {fmtTime(req.start_time) || '-'}
                                                    {req.end_time ? ` - ${fmtTime(req.end_time)}` : ''}
                                                    {req.estimated_hours != null && (
                                                        <div className="text-xs text-dim-foreground">{Number(req.estimated_hours).toFixed(2)} hrs</div>
                                                    )}
                                                </td>
                                                <td className="text-dim-foreground text-sm max-w-52 truncate">{req.reason || '-'}</td>
                                                <td>
                                                    <div className="flex gap-2 justify-end">
                                                        <Link href={`/work-requests/${req.id}`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">
                                                            <Icon name="ph--eye-fill" className="size-4" />
                                                        </Link>
                                                        <button type="button" onClick={() => approve(req.id)} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-transparent bg-brand px-3 text-xs font-medium text-brand-foreground no-underline transition-colors hover:bg-brand/90">
                                                            <Icon name="tabler--check" className="size-4" />
                                                        </button>
                                                        <button type="button" onClick={() => openReject(req.id)} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10">
                                                            <Icon name="ph--x" className="size-4" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <div className="md:hidden p-4 flex flex-col gap-3">
                                {pendingRequests.map((req) => (
                                    <div key={req.id} className="rounded-xl border border-edge bg-card p-4">
                                        <div className="flex justify-between items-start mb-2">
                                            <div>
                                                <div className="text-sm text-base-content font-semibold">{req.employee?.full_name}</div>
                                                <div className="text-xs text-dim-foreground">{req.employee?.employee_id}</div>
                                                <div className="text-xs text-dim-foreground">{req.employee?.position}</div>
                                            </div>
                                            <StatusBadge type={typeBadge(req.request_type)}>
                                                {req.request_type.charAt(0).toUpperCase() + req.request_type.slice(1)}
                                            </StatusBadge>
                                        </div>

                                        <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-dim-foreground mt-2">
                                            <span><Icon name="ph--calendar-fill" className="size-3.5 inline" /> {fmtDate(req.work_date)}</span>
                                            <span><Icon name="ph--clock-fill" className="size-3.5 inline" /> {fmtTime(req.start_time) || '-'}{req.end_time ? ` - ${fmtTime(req.end_time)}` : ''}</span>
                                            {req.estimated_hours != null && (
                                                <span><Icon name="ph--hourglass-fill" className="size-3.5 inline" /> {Number(req.estimated_hours).toFixed(2)} hrs</span>
                                            )}
                                        </div>

                                        {req.reason && (
                                            <div className="text-xs text-dim-foreground mt-2">
                                                <Icon name="ph--text-align-left-fill" className="size-3.5 inline" /> {req.reason}
                                            </div>
                                        )}

                                        <div className="flex gap-2 flex-wrap mt-3 pt-3 border-t border-edge/60">
                                            <Link href={`/work-requests/${req.id}`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">
                                                <Icon name="ph--eye-fill" className="size-4" /> View
                                            </Link>
                                            <button type="button" onClick={() => approve(req.id)} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-transparent bg-brand px-3 text-xs font-medium text-brand-foreground no-underline transition-colors hover:bg-brand/90">
                                                <Icon name="ph--check-fill" className="size-4" /> Approve
                                            </button>
                                            <button type="button" onClick={() => openReject(req.id)} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10">
                                                <Icon name="ph--x" className="size-4" /> Reject
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </>
                    ) : (
                        <div className="card text-center p-8 m-4">
                            <Icon name="tabler--circle-check" className="text-success size-10 mb-4" />
                            <h3 className="text-dim-foreground">All Caught Up!</h3>
                            <p className="text-dim-foreground mb-4">There are no pending work requests to review.</p>
                            <Link href="/work-requests" className="btn btn-soft btn-primary inline-flex items-center gap-2 mx-auto">
                                <Icon name="ph--list-fill" className="size-4" /> View All Requests
                            </Link>
                        </div>
                    )}
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
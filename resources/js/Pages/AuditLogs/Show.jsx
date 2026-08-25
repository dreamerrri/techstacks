import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import DetailRow from '../../components/DetailRow';

const ACTION_BADGE = {
    create: 'badge-soft badge-success',
    update: 'badge-soft badge-warning',
    delete: 'badge-soft badge-error',
    login: 'badge-soft badge-info',
    logout: 'badge-soft badge-neutral',
};

export default function AuditLogsShow({ auditLog }) {
    const action = String(auditLog.action || '').toLowerCase();
    const badgeClass = ACTION_BADGE[action] || 'badge-soft';

    const fmtDateTime = (value) => {
        if (!value) return '—';
        return new Date(value).toLocaleString('en-US', {
            month: 'short', day: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true,
        });
    };

    return (
        <AppLayout title="Audit Log Detail">
            <Head title="Audit Log Detail" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/audit-logs" className="back-link text-dim-foreground no-underline text-sm hover:text-brand flex items-center gap-1">
                        <Icon name="tabler--arrow-left" className="size-4" /> Back to Audit Logs
                    </Link>
                </div>
                <div className="mb-6">
                    <span className="inline-flex items-center gap-1 rounded-full border border-transparent bg-brand/12 px-2.5 py-0.5 text-xs font-medium text-brand mb-2">
                        <Icon name="tabler--history" className="size-3.5" /> Audit Log Detail
                    </span>
                    <p className="text-dim-foreground m-0">Detailed view of a single audit log entry.</p>
                </div>

                <div className="rounded-xl border border-edge bg-card p-0 max-w-2xl">
                    <div className="flex flex-col text-sm px-5">
                        <DetailRow label="Date / Time">
                            {fmtDateTime(auditLog.created_at)}
                        </DetailRow>

                        <DetailRow label="User">
                            {auditLog.user?.name || '—'}
                        </DetailRow>

                        <DetailRow label="Action">
                            <span className={`badge ${badgeClass}`}>{auditLog.action ? auditLog.action.charAt(0).toUpperCase() + auditLog.action.slice(1) : ''}</span>
                        </DetailRow>

                        <DetailRow label="Module">
                            {auditLog.module ? auditLog.module.charAt(0).toUpperCase() + auditLog.module.slice(1) : ''}
                        </DetailRow>

                        <DetailRow label="Description">
                            {auditLog.description}
                        </DetailRow>

                        <DetailRow label="IP Address">
                            <span className="font-mono">{auditLog.ip_address || '—'}</span>
                        </DetailRow>

                        <DetailRow label="User Agent" border={!(auditLog.old_values || auditLog.new_values)}>
                            <span className="text-dim-foreground text-xs break-all">{auditLog.user_agent || '—'}</span>
                        </DetailRow>

                        {auditLog.old_values && (
                            <div className={`flex flex-col gap-2 py-3 ${auditLog.new_values ? 'border-b border-edge' : ''}`}>
                                <span className="text-xs font-semibold uppercase tracking-wider text-dim-foreground/70">Old Values</span>
                                <pre className="m-0 text-xs bg-base-200 p-3 rounded-xl overflow-x-auto">{JSON.stringify(auditLog.old_values, null, 2)}</pre>
                            </div>
                        )}

                        {auditLog.new_values && (
                            <div className="flex flex-col gap-2 py-3">
                                <span className="text-xs font-semibold uppercase tracking-wider text-dim-foreground/70">New Values</span>
                                <pre className="m-0 text-xs bg-base-200 p-3 rounded-xl overflow-x-auto">{JSON.stringify(auditLog.new_values, null, 2)}</pre>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';

const ACTION_BADGE = {
    create: 'badge-soft badge-success text-xs',
    update: 'badge-soft badge-warning text-xs',
    delete: 'badge-soft badge-error text-xs',
    login: 'badge-soft badge-info text-xs',
    logout: 'badge-soft badge-neutral text-xs',
};

function actionBadge(action) {
    return ACTION_BADGE[String(action || '').toLowerCase()] || 'badge-soft text-xs';
}

export default function AuditLogsIndex({ logs, modules, actions, filters }) {
    const [module, setModule] = useState(filters?.module || '');
    const [action, setAction] = useState(filters?.action || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');

    const applyFilters = () => {
        const params = {};
        if (module) params.module = module;
        if (action) params.action = action;
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;
        router.get('/audit-logs', params, { preserveState: true, preserveScroll: true, replace: true });
    };

    const clearFilters = () => {
        setModule('');
        setAction('');
        setDateFrom('');
        setDateTo('');
        router.get('/audit-logs', {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    const hasFilters = module || action || dateFrom || dateTo;

    const fmtDateTime = (value) => {
        if (!value) return '—';
        const d = new Date(value);
        return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    };
    const fmtTime = (value) => {
        if (!value) return '';
        return new Date(value).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    };

    return (
        <AppLayout title="Audit Logs">
            <Head title="Audit Logs" />
            <div className="p-2 sm:p-4">
                <div className="flex justify-between items-center flex-wrap gap-3 mb-6">
                    <div>
                        <span className="badge badge-soft badge-info mb-2">
                            <Icon name="tabler--history" className="size-3.5" /> Audit Logs
                        </span>
                        <h2 className="text-lg font-bold text-base-content mt-2 mb-1">System Activity</h2>
                        <p className="text-subtle m-0">Track and review all system activity and changes.</p>
                    </div>
                </div>

                <div className="card bg-base-100 shadow-sm p-4 mb-4">
                    <div className="flex flex-wrap items-end gap-3">
                        <div className="form-control">
                            <label className="label label-text">Module</label>
                            <select className="select select-bordered select-sm w-40" value={module} onChange={(e) => setModule(e.target.value)}>
                                <option value="">All Modules</option>
                                {modules.map((m) => (
                                    <option key={m} value={m}>{m.charAt(0).toUpperCase() + m.slice(1)}</option>
                                ))}
                            </select>
                        </div>
                        <div className="form-control">
                            <label className="label label-text">Action</label>
                            <select className="select select-bordered select-sm w-36" value={action} onChange={(e) => setAction(e.target.value)}>
                                <option value="">All Actions</option>
                                {actions.map((a) => (
                                    <option key={a} value={a}>{a.charAt(0).toUpperCase() + a.slice(1)}</option>
                                ))}
                            </select>
                        </div>
                        <div className="form-control">
                            <label className="label label-text">Date From</label>
                            <input type="date" className="input input-bordered input-sm w-40" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} />
                        </div>
                        <div className="form-control">
                            <label className="label label-text">Date To</label>
                            <input type="date" className="input input-bordered input-sm w-40" value={dateTo} onChange={(e) => setDateTo(e.target.value)} />
                        </div>
                        <div className="flex gap-2">
                            <button onClick={applyFilters} className="btn btn-soft btn-error btn-sm">
                                <Icon name="tabler--filter" className="size-4" /> Filter
                            </button>
                            {hasFilters && (
                                <button onClick={clearFilters} className="btn btn-soft btn-neutral btn-sm">
                                    <Icon name="tabler--x" className="size-4" /> Clear
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                <div className="card bg-base-100 shadow-sm overflow-hidden p-0">
                    {logs.total > 0 ? (
                        <>
                            <div className="overflow-x-auto overflow-y-auto hidden md:block">
                                <table className="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Module</th>
                                            <th>Description</th>
                                            <th>IP Address</th>
                                            <th className="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {logs.data.map((log) => (
                                            <tr key={log.id} className="row-hover">
                                                <td className="text-subtle text-xs whitespace-nowrap">
                                                    {fmtDateTime(log.created_at)}<br />
                                                    {fmtTime(log.created_at)}
                                                </td>
                                                <td className="font-semibold text-base-content">{log.user?.name || '—'}</td>
                                                <td>
                                                    <span className={`badge ${actionBadge(log.action)} whitespace-nowrap`}>{log.action ? log.action.charAt(0).toUpperCase() + log.action.slice(1) : ''}</span>
                                                </td>
                                                <td className="text-subtle">{log.module ? log.module.charAt(0).toUpperCase() + log.module.slice(1) : ''}</td>
                                                <td className="text-subtle max-w-[200px] truncate">{log.description}</td>
                                                <td className="text-subtle font-mono text-xs">{log.ip_address || '—'}</td>
                                                <td className="text-center">
                                                    <Link href={`/audit-logs/${log.id}`} className="btn btn-soft btn-info btn-sm" title="View details">
                                                        <Icon name="tabler--eye" className="size-4" />
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <div className="md:hidden p-4 flex flex-col gap-3">
                                {logs.data.map((log) => (
                                    <div key={log.id} className="card bg-base-100 border border-base-300 p-4">
                                        <div className="flex justify-between items-start mb-2">
                                            <div className="flex items-center gap-3">
                                                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-neutral to-neutral/80 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                                    {log.user?.name ? log.user.name.charAt(0).toUpperCase() : 'S'}
                                                </div>
                                                <div>
                                                    <div className="font-semibold text-base-content text-sm">{log.user?.name || 'System'}</div>
                                                    <div className="text-xs text-faint">{fmtDateTime(log.created_at)} {fmtTime(log.created_at)}</div>
                                                </div>
                                            </div>
                                            <span className={`badge ${actionBadge(log.action)} whitespace-nowrap`}>{log.action ? log.action.charAt(0).toUpperCase() + log.action.slice(1) : ''}</span>
                                        </div>

                                        <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-subtle mt-2">
                                            <span><Icon name="tabler--cube" className="size-3.5 inline" /> {log.module ? log.module.charAt(0).toUpperCase() + log.module.slice(1) : ''}</span>
                                            <span><Icon name="tabler--network" className="size-3.5 inline" /> {log.ip_address || '—'}</span>
                                        </div>

                                        <div className="text-xs text-faint mt-1">{log.description}</div>

                                        <div className="mt-3 pt-3 border-t border-base-200">
                                            <Link href={`/audit-logs/${log.id}`} className="btn btn-info btn-sm">
                                                <Icon name="tabler--eye" className="size-4" /> View Details
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="px-6 py-4 border-t border-base-300">
                                <Pagination paginator={logs} />
                            </div>
                        </>
                    ) : (
                        <div className="py-16 px-6 text-center">
                            <Icon name="tabler--history" className="size-10 text-faint mx-auto mb-4" />
                            <h3 className="text-subtle font-semibold mb-2">No Audit Logs Found</h3>
                            <p className="text-faint mb-0">No audit log entries match your filters.</p>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
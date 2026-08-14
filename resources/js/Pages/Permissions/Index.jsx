import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import StatusBadge from '../../Components/StatusBadge';
import ConfirmButton from '../../Components/ConfirmButton';

export default function PermissionsIndex({ permissions }) {
    return (
        <AppLayout>
            <Head title="Permissions Management" />
            <div className="p-2 sm:p-4">
                <div className="card w-full min-w-0 border border-base-300 flex flex-col p-0">
                    <div className="sticky top-0 px-4 sm:px-7 pt-5 rounded-t-2xl bg-base-100 z-10">
                        <div className="flex justify-between items-center mb-4 flex-wrap gap-2">
                            <h2 className="text-sm font-semibold uppercase tracking-widest text-base-content/40 flex items-center gap-2 m-0">
                                <Icon name="tabler--key" className="size-4 text-primary" />
                                <span>Permissions Management</span>
                                <span className="tooltip [--placement:right]">
                                    <span className="tooltip-toggle cursor-pointer text-base-content">
                                        <Icon name="tabler--info-circle" className="size-4" />
                                    </span>
                                    <span className="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
                                        <span className="tooltip-body bg-primary shadow-md rounded-lg px-3 py-2 text-xs normal-case text-primary-content">
                                            Manage all system permissions and assigned roles.
                                        </span>
                                    </span>
                                </span>
                            </h2>
                            <Link href="/permissions/create" className="btn btn-soft btn-error btn-sm">
                                <Icon name="ph--plus-fill" className="size-4" /> Create Permission
                            </Link>
                        </div>
                    </div>

                    {Object.keys(permissions || {}).map((module) => {
                        const modulePermissions = permissions[module];
                        return (
                            <div key={module} className="mb-6 px-6 pt-4">
                                <div className="flex items-center gap-3 mb-3">
                                    <div className="w-8 h-8 rounded-full bg-linear-to-br bg-warning flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        {module.charAt(0).toUpperCase()}
                                    </div>
                                    <h3 className="text-base font-bold text-base-content m-0">{module.charAt(0).toUpperCase() + module.slice(1)}</h3>
                                    <span className="badge badge-soft badge-success text-xs normal-case tracking-normal">{modulePermissions.length}</span>
                                </div>

                                <div className="overflow-x-auto hidden md:block">
                                    <table className="table table-hover">
                                        <thead>
                                            <tr>
                                                <th className="w-60">Name</th>
                                                <th className="w-60">Slug</th>
                                                <th>Description</th>
                                                <th className="w-20 text-right">Roles</th>
                                                <th className="w-40 text-right">Status</th>
                                                <th className="w-40 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {modulePermissions.map((permission) => (
                                                <tr key={permission.id} className="row-hover">
                                                    <td className="font-semibold text-base-content">{permission.name}</td>
                                                    <td><code className="bg-base-200 text-error text-xs px-1.5 py-0.5 rounded">{permission.slug}</code></td>
                                                    <td className="text-base-content/60">
                                                        <span className="truncate block max-w-64" title={permission.description}>{permission.description || '—'}</span>
                                                    </td>
                                                    <td className="text-base-content/60 text-right">{permission.roles?.length ?? 0}</td>
                                                    <td className="text-right">
                                                        {permission.is_active ? <StatusBadge type="success">Active</StatusBadge> : <StatusBadge type="error">Inactive</StatusBadge>}
                                                    </td>
                                                    <td className="text-right">
                                                        <div className="flex gap-2 items-center justify-end">
                                                            <Link href={`/permissions/${permission.id}`} className="btn btn-soft btn-info btn-sm">
                                                                <Icon name="ph--eye-fill" className="size-4" />
                                                            </Link>
                                                            <Link href={`/permissions/${permission.id}/edit`} className="btn btn-soft btn-warning btn-sm">
                                                                <Icon name="ph--pencil-fill" className="size-4" />
                                                            </Link>
                                                            {permission.roles?.length === 0 && (
                                                                <ConfirmButton
                                                                    title="Delete Permission?"
                                                                    text="This permission will be permanently deleted."
                                                                    confirmText="Yes, delete"
                                                                    url={`/permissions/${permission.id}`}
                                                                    method="delete"
                                                                    className="btn btn-soft btn-error btn-sm"
                                                                >
                                                                    <Icon name="ph--trash-fill" className="size-4" />
                                                                </ConfirmButton>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="md:hidden flex flex-col gap-3">
                                    {modulePermissions.map((permission) => (
                                        <div key={permission.id} className="card bg-base-100 border border-base-300 p-4">
                                            <div className="flex justify-between items-start mb-2">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-10 h-10 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white flex-shrink-0">
                                                        <Icon name="ph--key-fill" className="size-4" />
                                                    </div>
                                                    <div>
                                                        <div className="font-semibold text-base-content text-sm">{permission.name}</div>
                                                        <code className="text-xs text-base-content/60">{permission.slug}</code>
                                                    </div>
                                                </div>
                                                {permission.is_active ? <StatusBadge type="success">Active</StatusBadge> : <StatusBadge type="error">Inactive</StatusBadge>}
                                            </div>
                                            <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                                                <span><Icon name="tabler--users" className="size-3.5 inline" /> {permission.roles?.length ?? 0} roles</span>
                                            </div>
                                            {permission.description && <div className="text-xs text-base-content/40 mt-1">{permission.description}</div>}
                                            <div className="flex gap-2 flex-wrap mt-3 pt-3 border-t border-base-200">
                                                <Link href={`/permissions/${permission.id}`} className="btn btn-soft btn-info btn-sm">
                                                    <Icon name="ph--eye-fill" className="size-4" /> View
                                                </Link>
                                                <Link href={`/permissions/${permission.id}/edit`} className="btn btn-soft btn-warning btn-sm">
                                                    <Icon name="ph--pencil-fill" className="size-4" /> Edit
                                                </Link>
                                                {permission.roles?.length === 0 && (
                                                    <ConfirmButton
                                                        title="Delete Permission?"
                                                        text="This permission will be permanently deleted."
                                                        confirmText="Yes, delete"
                                                        url={`/permissions/${permission.id}`}
                                                        method="delete"
                                                        className="btn btn-soft btn-error btn-sm"
                                                    >
                                                        <Icon name="ph--trash-fill" className="size-4" /> Delete
                                                    </ConfirmButton>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </AppLayout>
    );
}
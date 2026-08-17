import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import DetailRow from '../../Components/DetailRow';
import StatusBadge from '../../Components/StatusBadge';

export default function PermissionsShow({ permission }) {
    return (
        <AppLayout>
            <Head title="Permission Details" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/permissions" className="back-link text-base-content/60 no-underline text-sm hover:text-success">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Permissions
                    </Link>
                </div>

                <div className="card bg-base-100 shadow-sm p-6">
                    <div className="flex items-center justify-between flex-wrap gap-3 mb-6">
                        <div className="flex items-center gap-4">
                            <div className="w-14 h-14 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white text-xl flex-shrink-0">
                                <Icon name="ph--key-fill" className="size-6" />
                            </div>
                            <div>
                                <h2 className="text-xl font-bold text-base-content m-0">{permission.name}</h2>
                                <code className="text-xs text-base-content/60 bg-base-200 px-2 py-0.5 rounded">{permission.slug}</code>
                            </div>
                        </div>
                        <Link href={`/permissions/${permission.id}/edit`} className="btn btn-soft btn-error btn-sm">
                            <Icon name="ph--pencil-fill" className="size-4" /> Edit Permission
                        </Link>
                    </div>

                    <div className="mb-8">
                        <h3 className="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-error/20 pb-2 mb-4">
                            <Icon name="ph--info-fill" className="size-4 text-error inline" /> Permission Information
                        </h3>
                        <div className="flex flex-col">
                            <DetailRow label="Module">
                                <span className="text-base-content">{permission.module.charAt(0).toUpperCase() + permission.module.slice(1)}</span>
                            </DetailRow>
                            <DetailRow label="Status">
                                {permission.is_active ? <StatusBadge type="success">Active</StatusBadge> : <StatusBadge type="error">Inactive</StatusBadge>}
                            </DetailRow>
                            <DetailRow label="Assigned to Roles" border={!!permission.description}>
                                <span className="text-base-content">{permission.roles?.length ?? 0}</span>
                            </DetailRow>
                            {permission.description && (
                                <div className="flex flex-col gap-1 py-3">
                                    <span className="text-base-content/40 font-medium">Description</span>
                                    <span className="text-base-content/80 text-sm">{permission.description}</span>
                                </div>
                            )}
                        </div>
                    </div>

                    <div>
                        <h3 className="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-error/20 pb-2 mb-4">
                            <Icon name="tabler--user-tag" className="size-4 text-error inline" /> Roles with this Permission ({permission.roles?.length ?? 0})
                        </h3>
                        {permission.roles?.length > 0 ? (
                            <div className="flex flex-col gap-2">
                                {permission.roles.map((role) => (
                                    <div key={role.id} className="flex justify-between items-center p-3 border border-base-300 rounded-xl hover:shadow-md transition-shadow">
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white font-bold flex-shrink-0">
                                                {role.name.charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <div className="font-semibold text-base-content text-sm">{role.name}</div>
                                                <code className="text-xs text-base-content/60">{role.slug}</code>
                                            </div>
                                        </div>
                                        <Link href={`/roles/${role.id}`} className="btn btn-soft btn-info btn-sm">
                                            <Icon name="ph--eye-fill" className="size-4" /> View Role
                                        </Link>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-base-content/40 text-sm m-0">No roles have this permission.</p>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
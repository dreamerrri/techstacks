import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import StatusBadge from '../../Components/StatusBadge';
import DataTable from '../../Components/DataTable';
import ConfirmButton from '../../Components/ConfirmButton';

export default function RolesIndex({ roles }) {
    return (
        <AppLayout>
            <Head title="Roles Management" />
            <div className="p-2 sm:p-4">
                <DataTable
                    title="Roles Management"
                    icon="tabler--lock"
                    tooltip="Manage all system roles and their assigned permissions."
                    baseUrl="/roles"
                    empty="No data found."
                    actions={
                        <Link href="/roles/create" className="btn btn-soft btn-error btn-sm">
                            <Icon name="ph--plus-fill" className="size-4" /> Create Role
                        </Link>
                    }
                >
                    <div className="overflow-x-auto hidden md:block">
                        <table className="table table-hover">
                            <thead>
                                <tr>
                                    <th className="w-40">Name</th>
                                    <th className="w-24">Slug</th>
                                    <th>Description</th>
                                    <th className="w-40 text-right">Users</th>
                                    <th className="w-40 text-right">Permissions</th>
                                    <th className="w-40 text-right">Status</th>
                                    <th className="w-40 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {roles.map((role) => (
                                    <tr key={role.id} className="row-hover">
                                        <td className="font-semibold text-base-content">{role.name}</td>
                                        <td><code className="bg-base-200 text-error text-xs px-1.5 py-0.5 rounded">{role.slug}</code></td>
                                        <td className="text-subtle truncate max-w-64">{role.description || '—'}</td>
                                        <td className="text-subtle text-right">{role.users_count}</td>
                                        <td className="text-subtle text-right">{role.permissions?.length ?? 0}</td>
                                        <td className="text-right">
                                            {role.is_active ? (
                                                <StatusBadge type="success">Active</StatusBadge>
                                            ) : (
                                                <StatusBadge type="error">Inactive</StatusBadge>
                                            )}
                                        </td>
                                        <td className="text-right">
                                            <div className="flex gap-2 items-center justify-end">
                                                <Link href={`/roles/${role.id}`} className="btn btn-soft btn-info btn-sm">
                                                    <Icon name="ph--eye-fill" className="size-4" />
                                                </Link>
                                                <Link href={`/roles/${role.id}/edit`} className="btn btn-soft btn-warning btn-sm">
                                                    <Icon name="ph--pencil-fill" className="size-4" />
                                                </Link>
                                                {role.users_count === 0 && (
                                                    <ConfirmButton
                                                        title="Delete Role?"
                                                        text="This role will be permanently deleted."
                                                        confirmText="Yes, delete"
                                                        url={`/roles/${role.id}`}
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

                    <div className="md:hidden p-4 flex flex-col gap-3">
                        {roles.map((role) => (
                            <div key={role.id} className="card bg-base-100 border border-base-300 p-4">
                                <div className="flex justify-between items-start mb-2">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                            {role.name.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <div className="font-semibold text-base-content text-sm">{role.name}</div>
                                            <code className="text-xs text-subtle">{role.slug}</code>
                                        </div>
                                    </div>
                                    {role.is_active ? <StatusBadge type="success">Active</StatusBadge> : <StatusBadge type="error">Inactive</StatusBadge>}
                                </div>
                                <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-subtle mt-2">
                                    <span><Icon name="tabler--users" className="size-3.5 inline" /> {role.users_count} users</span>
                                    <span><Icon name="ph--key-fill" className="size-3.5 inline" /> {role.permissions?.length ?? 0} permissions</span>
                                </div>
                                {role.description && <div className="text-xs text-faint mt-1">{role.description}</div>}
                                <div className="flex gap-2 flex-wrap mt-3 pt-3 border-t border-base-200">
                                    <Link href={`/roles/${role.id}`} className="btn btn-info btn-sm">
                                        <Icon name="ph--eye-fill" className="size-4" /> View
                                    </Link>
                                    <Link href={`/roles/${role.id}/edit`} className="btn btn-soft btn-warning btn-sm">
                                        <Icon name="ph--pencil-fill" className="size-4" /> Edit
                                    </Link>
                                    {role.users_count === 0 && (
                                        <ConfirmButton
                                            title="Delete Role?"
                                            text="This role will be permanently deleted."
                                            confirmText="Yes, delete"
                                            url={`/roles/${role.id}`}
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
                </DataTable>
            </div>
        </AppLayout>
    );
}
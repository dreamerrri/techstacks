import { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import DetailRow from '../../components/DetailRow';
import StatusBadge from '../../components/StatusBadge';
import ConfirmButton from '../../components/ConfirmButton';
import NativeSelect from '../../components/NativeSelect';

const groupByModule = (items) => {
    const groups = {};
    (items || []).forEach((item) => {
        const key = item.module || 'General';
        (groups[key] = groups[key] || []).push(item);
    });
    return groups;
};

export default function RolesShow({ roleData: role, availableUsers }) {
    const { auth } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({ user_id: '' });
    const permissionGroups = groupByModule(role.permissions);

    const assignUser = (e) => {
        e.preventDefault();
        if (!data.user_id) return;
        post(`/roles/${role.id}/assign-user`);
    };

    return (
        <AppLayout>
            <Head title="Role Details" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/roles" className="back-link text-dim-foreground no-underline text-sm hover:text-success">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Roles
                    </Link>
                </div>

                <div className="rounded-xl border border-edge bg-card p-6">
                    <div className="flex items-center justify-between flex-wrap gap-3 mb-6">
                        <div className="flex items-center gap-4">
                            <div className="w-14 h-14 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                                {role.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h2 className="text-xl font-bold text-base-content m-0">{role.name}</h2>
                                <code className="text-xs text-dim-foreground bg-base-200 px-2 py-0.5 rounded">{role.slug}</code>
                            </div>
                        </div>
                        <Link href={`/roles/${role.id}/edit`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10">
                            <Icon name="tabler--pencil" className="size-4" /> Edit Role
                        </Link>
                    </div>

                    <div className="mb-8">
                        <h3 className="text-xs font-semibold uppercase tracking-widest text-dim-foreground/70 border-b-2 border-error/20 pb-2 mb-4">
                            <Icon name="tabler--user" className="size-4 text-error inline" /> Role Information
                        </h3>
                        <div className="flex flex-col">
                            <DetailRow label="Description">
                                <span className="text-dim-foreground">{role.description || '—'}</span>
                            </DetailRow>
                            <DetailRow label="Status">
                                {role.is_active ? <StatusBadge type="success">Active</StatusBadge> : <StatusBadge type="error">Inactive</StatusBadge>}
                            </DetailRow>
                            <DetailRow label="Total Users">{role.users?.length ?? 0}</DetailRow>
                            <DetailRow label="Total Permissions" border={false}>{role.permissions?.length ?? 0}</DetailRow>
                        </div>
                    </div>

                    <div className="mb-8">
                        <h3 className="text-xs font-semibold uppercase tracking-widest text-dim-foreground/70 border-b-2 border-error/20 pb-2 mb-4">
                            <Icon name="ph--key-fill" className="size-4 text-error inline" /> Permissions ({role.permissions?.length ?? 0})
                        </h3>
                        {role.permissions?.length > 0 ? (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                {Object.entries(permissionGroups).map(([module, perms]) => (
                                    <div key={module} className="bg-base-200 border border-edge rounded-xl p-4">
                                        <div className="text-xs font-bold text-dim-foreground uppercase tracking-widest mb-3">
                                            {module.charAt(0).toUpperCase() + module.slice(1)}
                                        </div>
                                        {perms.map((permission) => (
                                            <div key={permission.id} className="flex items-center gap-2 text-xs text-dim-foreground mb-1.5">
                                                <Icon name="tabler--circle-check" className="size-3.5 text-success flex-shrink-0" />
                                                {permission.name}
                                            </div>
                                        ))}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-dim-foreground/70 text-sm m-0">No permissions assigned to this role.</p>
                        )}
                    </div>

                    <div>
                        <h3 className="text-xs font-semibold uppercase tracking-widest text-dim-foreground/70 border-b-2 border-error/20 pb-2 mb-4">
                            <Icon name="tabler--user" className="size-4 text-error inline" /> Assigned Users ({role.users?.length ?? 0})
                        </h3>

                        {availableUsers?.length > 0 && (
                            <form onSubmit={assignUser} className="flex gap-2 items-center flex-wrap mb-5">
                                <NativeSelect
                                    name="user_id"
                                    value={data.user_id}
                                    onChange={(e) => setData('user_id', e.target.value)}
                                    required
                                    className="select select-bordered select-sm flex-1 min-w-48"
                                >
                                    <option value="">Select a user to assign...</option>
                                    {availableUsers.map((user) => (
                                        <option key={user.id} value={user.id}>{user.name} ({user.email})</option>
                                    ))}
                                </NativeSelect>
                                <button type="submit" className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10" disabled={processing}>
                                    <Icon name="tabler--user-plus" className="size-4" /> Assign User
                                </button>
                            </form>
                        )}
                        {errors.user_id && <p className="label text-error text-xs mt-1">{errors.user_id}</p>}

                        {role.users?.length > 0 ? (
                            <div className="flex flex-col gap-2">
                                {role.users.map((user) => (
                                    <div key={user.id} className="flex justify-between items-center p-3 border border-edge rounded-xl hover:shadow-md transition-shadow">
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white font-bold flex-shrink-0">
                                                {user.name.charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <div className="font-semibold text-base-content text-sm">{user.name}</div>
                                                <div className="text-dim-foreground text-xs">{user.email}</div>
                                            </div>
                                        </div>
                                        {user.id !== auth?.user?.id && (
                                            <ConfirmButton
                                                title="Remove User?"
                                                text={`This user will be removed from the ${role.name} role.`}
                                                confirmText="Yes, remove"
                                                url={`/roles/${role.id}/users/${user.id}`}
                                                method="delete"
                                                className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10"
                                            >
                                                <Icon name="tabler--user-minus" className="size-4" /> Remove
                                            </ConfirmButton>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-dim-foreground/70 text-sm m-0">No users assigned to this role.</p>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
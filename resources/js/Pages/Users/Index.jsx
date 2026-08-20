import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import Avatar from '../../components/Avatar';
import StatusBadge from '../../components/StatusBadge';
import DataTable from '../../components/DataTable';
import ConfirmButton from '../../components/ConfirmButton';

const ROLE_BADGE = {
    admin: 'badge-soft badge-error',
    hr: 'badge-soft badge-warning',
    employee: 'badge-soft badge-info',
};

export default function UsersIndex({ users, filters, stats }) {
    const { auth } = usePage().props;
    const currentUserId = auth?.user?.id;

    const statCards = [
        { icon: 'icon-[tabler--users]', color: 'text-primary bg-primary/10', value: stats.total_users, label: 'Total Users' },
        { icon: 'icon-[tabler--shield-check]', color: 'text-error bg-error/10', value: stats.admin_users, label: 'Admins' },
        { icon: 'icon-[tabler--user]', color: 'text-warning bg-warning/10', value: stats.hr_users, label: 'HR Personnel' },
        { icon: 'icon-[tabler--circle-check]', color: 'text-success bg-success/10', value: stats.active_users, label: 'Active Accounts' },
    ];

    const changeRole = (user, role) => {
        if (role === user.role) return;
        router.patch(`/users/${user.id}/role`, { role }, { preserveScroll: true });
    };

    const formatDate = (value) => {
        if (!value) return 'Never';
        return new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) +
            ' ' + new Date(value).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    };

    const nameCell = (user) => (
        <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
                {user.photo_url ? (
                    <img src={user.photo_url} alt={user.name} className="w-full h-full object-cover" />
                ) : (
                    <div className="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-primary-content text-xs font-bold">
                        {user.name.charAt(0).toUpperCase()}
                    </div>
                )}
            </div>
            {user.employee ? (
                <Link href={`/employees/${user.employee.id}`} className="text-base-content no-underline font-semibold hover:text-primary">
                    {user.name}
                </Link>
            ) : (
                <span className="text-base-content">{user.name}</span>
            )}
            {user.id === currentUserId && (
                <span className="badge badge-soft badge-success text-[10px] px-2 py-0 normal-case tracking-normal">You</span>
            )}
        </div>
    );

    const roleSelect = (user) => (
        <select
            name="role"
            value={user.role}
            disabled={user.id === currentUserId}
            onChange={(e) => changeRole(user, e.target.value)}
            className={`select select-bordered select-xs rounded-full ${ROLE_BADGE[user.role] ?? 'badge-soft'}`}
        >
            <option value="admin">Admin</option>
            <option value="hr">HR</option>
            <option value="employee">Employee</option>
        </select>
    );

    const toggleButton = (user) => {
        if (user.id === currentUserId) {
            return <span className="text-muted text-xs">—</span>;
        }
        return (
            <ConfirmButton
                title={user.is_active ? 'Deactivate User' : 'Activate User'}
                text={user.is_active ? 'Deactivate this user account?' : 'Activate this user account?'}
                icon={user.is_active ? 'warning' : 'question'}
                confirmText={user.is_active ? 'Yes, deactivate' : 'Yes, activate'}
                url={`/users/${user.id}/toggle`}
                method="patch"
                className={`btn btn-soft btn-xs ${user.is_active ? 'btn-error' : 'btn-success'}`}
            >
                <Icon name={user.is_active ? 'tabler--ban' : 'tabler--check'} className="size-4" />
                {user.is_active ? 'Deactivate' : 'Activate'}
            </ConfirmButton>
        );
    };

    return (
        <AppLayout>
            <Head title="All Users" />
            <div className="p-2 sm:p-4 space-y-6">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {statCards.map((s) => (
                        <div key={s.label} className="card bg-base-100 border border-base-300 p-5 text-center">
                            <div className={`w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 ${s.color}`}>
                                <Icon name={s.icon} className="size-5" />
                            </div>
                            <div className="text-3xl font-bold text-base-content mb-1">{s.value}</div>
                            <div className="text-xs text-muted uppercase tracking-widest font-medium">{s.label}</div>
                        </div>
                    ))}
                </div>

                <DataTable
                    title="User Accounts"
                    icon="tabler--users"
                    tooltip="Manage system accounts, roles, and access."
                    baseUrl="/users"
                    search
                    searchPlaceholder="Search name or email..."
                    filters={[
                        {
                            name: 'role',
                            value: filters.role || '',
                            options: [
                                { value: '', label: 'All Roles' },
                                { value: 'admin', label: 'Admin' },
                                { value: 'hr', label: 'HR' },
                                { value: 'employee', label: 'Employee' },
                            ],
                        },
                        {
                            name: 'status',
                            value: filters.status || '',
                            options: [
                                { value: '', label: 'All Status' },
                                { value: 'active', label: 'Active' },
                                { value: 'inactive', label: 'Inactive' },
                            ],
                        },
                    ]}
                    paginator={users}
                    empty="No users found."
                >
                    <div className="overflow-x-auto overflow-y-auto hidden md:block" style={{ maxHeight: '53vh' }}>
                        <table className="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.data.map((user) => (
                                    <tr key={user.id} className="row-hover">
                                        <td className="font-semibold text-base-content">{nameCell(user)}</td>
                                        <td className="text-subtle">{user.email}</td>
                                        <td>{roleSelect(user)}</td>
                                        <td>
                                            {user.is_active ? (
                                                <StatusBadge type="success">Active</StatusBadge>
                                            ) : (
                                                <StatusBadge type="error">Inactive</StatusBadge>
                                            )}
                                        </td>
                                        <td className="text-subtle text-xs">{formatDate(user.last_login_at)}</td>
                                        <td>{toggleButton(user)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="md:hidden p-4">
                        {users.data.map((user) => (
                            <div key={user.id} className="card bg-base-100 border border-base-300 p-4 mb-3">
                                <div className="flex justify-between items-start mb-3">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                            {user.photo_url ? (
                                                <img src={user.photo_url} alt={user.name} className="w-full h-full object-cover" />
                                            ) : (
                                                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-primary-content text-sm font-bold">
                                                    {user.name.charAt(0).toUpperCase()}
                                                </div>
                                            )}
                                        </div>
                                        <div>
                                            <div className="font-semibold text-base-content text-sm flex items-center gap-1">
                                                {user.employee ? (
                                                    <Link href={`/employees/${user.employee.id}`} className="text-base-content no-underline font-semibold">{user.name}</Link>
                                                ) : (
                                                    user.name
                                                )}
                                                {user.id === currentUserId && (
                                                    <span className="badge badge-soft badge-success text-[10px] px-2 py-0 normal-case">You</span>
                                                )}
                                            </div>
                                            <div className="text-xs text-subtle">{user.email}</div>
                                        </div>
                                    </div>
                                    {user.is_active ? <StatusBadge type="success">Active</StatusBadge> : <StatusBadge type="error">Inactive</StatusBadge>}
                                </div>
                                <div className="flex justify-between items-center flex-wrap gap-2 pt-3 border-t border-base-300">
                                    {roleSelect(user)}
                                    <span className="text-xs text-muted">{formatDate(user.last_login_at)}</span>
                                    {toggleButton(user)}
                                </div>
                            </div>
                        ))}
                    </div>
                </DataTable>
            </div>
        </AppLayout>
    );
}
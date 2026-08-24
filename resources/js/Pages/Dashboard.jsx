import { lazy, Suspense } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '../components/AppLayout';

// FullCalendar is heavy (~250KB with plugins) — load it in the background
// instead of blocking the dashboard's first paint on it
const FullCalendarWidget = lazy(() => import('../components/FullCalendarWidget'));

function CalendarSkeleton() {
    return (
        <div className="card bg-base-100 border border-base-300 p-6 mb-4 animate-pulse">
            <div className="h-6 w-40 bg-base-200 rounded mb-4"></div>
            <div className="h-64 bg-base-200 rounded"></div>
        </div>
    );
}

export default function Dashboard({ counts }) {
    const { user } = usePage().props.auth;
    const isAdmin = user.role === 'admin';
    const isHR = user.role === 'hr';
    const emp = user.employee || null;
    const empStatus = emp?.employment_status;
    const empStatusColor = {
        Regular: 'text-success bg-success/10',
        Probationary: 'text-warning bg-warning/10',
        Contractual: 'text-info bg-info/10',
        'Part-time': 'text-base-content bg-base-200',
    }[empStatus] || 'text-base-content bg-base-200';

    const statCard = (href, icon, colorClass, value, label, small) => (
        <Link href={href || '#'} className={`${href ? 'hover:shadow-md transition-shadow cursor-pointer' : ''} card bg-base-100 border border-base-300 p-5 text-center`}>
            <div className={`w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 ${colorClass}`}>
                <i className={icon}></i>
            </div>
            <div className={`${small ? 'text-lg sm:text-2xl font-small break-words' : 'text-2xl sm:text-3xl font-bold'} text-base-content mb-1`}>{value}</div>
            <div className="text-xs text-muted uppercase tracking-widest">{label}</div>
        </Link>
    );

    const actionBtn = (href, icon, label) => (
        <a href={href || '#'} className="btn btn-soft flex-col h-auto py-5 gap-2">
            <div className="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
                <i className={icon}></i>
            </div>
            <span className="text-muted">{label}</span>
        </a>
    );

    return (
        <AppLayout>
            <Head title={isAdmin ? 'Admin Dashboard' : isHR ? 'HR Dashboard' : 'Dashboard'} />
            <div className="p-6 space-y-6">
                {isAdmin && (
                    <span className="badge badge-soft badge-primary">
                        <i className="icon-[tabler--shield-check]"></i> Administrator Access
                    </span>
                )}
                {isHR && (
                    <span className="badge badge-soft badge-primary">
                        <i className="icon-[tabler--user]"></i> HR Department Access
                    </span>
                )}

                <div className="text-base-content text-lg">
                    Welcome back, <strong>{user.name}</strong>
                    {isAdmin && ' — You have full administrative access.'}
                    {isHR && ' — You have HR access privileges.'}
                </div>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {isAdmin && (
                        <>
                            {statCard('/users', 'icon-[tabler--users]', 'text-primary bg-primary/10', counts.total_users, 'Total Users')}
                            {statCard('/users', 'icon-[tabler--shield-check]', 'text-error bg-error/10', counts.admin_users, 'Admin Users')}
                            {statCard('/users', 'icon-[tabler--user]', 'text-warning bg-warning/10', counts.hr_users, 'HR Personnel')}
                            {statCard('/users', 'icon-[tabler--circle-check]', 'text-success bg-success/10', counts.active_users, 'Active Accounts')}
                        </>
                    )}
                    {isHR && (
                        <>
                            {statCard('/employees', 'icon-[tabler--users]', 'text-primary bg-primary/10', counts.total_employees, 'Total Employees')}
                            {statCard(null, 'icon-[tabler--calendar-check]', 'text-success bg-success/10', counts.regular, 'Regular')}
                            {statCard(null, 'icon-[tabler--clock]', 'text-warning bg-warning/10', counts.probationary, 'Probationary')}
                            {statCard(null, 'icon-[tabler--archive]', 'text-base-content bg-base-200', counts.archived, 'Archived')}
                        </>
                    )}
                    {!isAdmin && !isHR && (
                        <>
                            {statCard(null, 'icon-[tabler--building]', 'text-primary bg-primary/10', emp?.department ?? '—', 'Department', true)}
                            {statCard(null, 'icon-[tabler--id-badge]', 'text-secondary bg-secondary/10', emp?.position ?? '—', 'Position', true)}
                            {statCard(null, 'icon-[tabler--briefcase]', empStatusColor, empStatus ?? '—', 'Employment Status', true)}
                            {statCard(
                                null,
                                'icon-[tabler--calendar]',
                                'text-accent bg-accent/10',
                                emp?.date_hired ? new Date(emp.date_hired).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '—',
                                'Date Hired',
                                true
                            )}
                        </>
                    )}
                </div>

                <div className="card border border-base-300 shadow-sm p-6">
                    <h2 className="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
                        <i className="icon-[ph--lightning-fill]"></i>
                        {isAdmin ? 'Administrative Actions' : isHR ? 'HR Actions' : 'Quick Actions'}
                    </h2>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                        {isAdmin && (
                            <>
                                {actionBtn('/employees/create', 'icon-[tabler--user-plus]', 'Create Users')}
                                {actionBtn('/roles', 'icon-[tabler--lock]', 'Manage Roles')}
                                {actionBtn(null, 'icon-[tabler--database]', 'System Backup')}
                                {actionBtn('/audit-logs', 'icon-[tabler--history]', 'View Logs')}
                            </>
                        )}
                        {isHR && (
                            <>
                                {actionBtn('/employees/create', 'icon-[tabler--user-plus]', 'Add Employee')}
                                {actionBtn('/payroll', 'icon-[tabler--calculator]', 'Payroll')}
                                {actionBtn(null, 'icon-[tabler--inbox]', 'Leave Requests')}
                                {actionBtn(null, 'icon-[tabler--file-type-pdf]', 'Reports')}
                            </>
                        )}
                        {!isAdmin && !isHR && (
                            <>
                                {actionBtn('/profile', 'icon-[tabler--user]', 'My Profile')}
                                {actionBtn('/payroll', 'icon-[tabler--receipt]', 'Payslips')}
                                {actionBtn(null, 'icon-[tabler--calendar-off]', 'Leave Request')}
                                {actionBtn(null, 'icon-[tabler--clock]', 'Attendance')}
                            </>
                        )}
                    </div>
                </div>

                <div className="card border border-base-300 shadow-sm p-6">
                    <h2 className="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
                        <i className="icon-[tabler--calendar]"></i>
                        Calendar
                    </h2>
                    <div className="card flex not-prose p-4 w-full">
                        <Suspense fallback={<CalendarSkeleton />}>
                            <FullCalendarWidget />
                        </Suspense>
                    </div>
                </div>

                <div className="card bg-base-100 border border-base-300 p-6">
                    <h2 className="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
                        <i className="icon-[tabler--id-badge]"></i>
                        System Information
                    </h2>
                    <div className="flex flex-col">
                        <div className="flex justify-between items-center py-3 border-b border-base-200">
                            <span className="text-muted">Name</span>
                            <span className="font-semibold text-base-content text-right">{user.name}</span>
                        </div>
                        <div className="flex justify-between items-center py-3 border-b border-base-200">
                            <span className="text-muted">Email</span>
                            <span className="font-semibold text-base-content text-right">{user.email}</span>
                        </div>
                        <div className="flex justify-between items-center py-3 border-b border-base-200">
                            <span className="text-muted">Role</span>
                            <span className="font-semibold text-base-content text-right">
                                {isAdmin ? 'Administrator' : isHR ? 'HR Personnel' : 'Employee'}
                            </span>
                        </div>
                        <div className="flex justify-between items-center py-3 border-b border-base-200">
                            <span className="text-muted">Account Status</span>
                            <span className="font-semibold text-base-content text-right">
                                {user.is_active ? (
                                    <span className="badge badge-soft badge-primary">
                                        <i className="icon-[tabler--circle-check]"></i> Active
                                    </span>
                                ) : (
                                    <span className="badge badge-soft badge-error">
                                        <i className="icon-[tabler--circle-x]"></i> Inactive
                                    </span>
                                )}
                            </span>
                        </div>
                        <div className="flex justify-between items-center py-3">
                            <span className="text-muted">Last Login</span>
                            <span className="font-semibold text-base-content text-right">
                                {user.last_login_at ? new Date(user.last_login_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) + ' ' + new Date(user.last_login_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'First Login'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
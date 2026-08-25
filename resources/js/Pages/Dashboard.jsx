import { lazy, Suspense } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';
import AppLayout from '../components/AppLayout';
import { Badge } from '@/components/ui/badge';

// FullCalendar is heavy (~250KB with plugins) — load it in the background
// instead of blocking the dashboard's first paint on it
const FullCalendarWidget = lazy(() => import('../components/FullCalendarWidget'));

function CalendarSkeleton() {
    return (
        <div className="mb-4 animate-pulse space-y-3">
            <div className="h-6 w-40 rounded bg-dim" />
            <div className="h-64 rounded bg-dim" />
        </div>
    );
}

const EMP_STATUS_COLOR = {
    Regular: 'text-success bg-success/10',
    Probationary: 'text-warning bg-warning/10',
    Contractual: 'text-highlight bg-highlight/10',
    'Part-time': 'text-dim-foreground bg-dim',
};

export default function Dashboard() {
    const { props } = usePage();
    const user = props.auth.user;
    const counts = props.counts ?? {};
    const emp = user.employee;
    const isAdmin = user.role === 'admin';
    const isHR = user.role === 'hr';
    const empStatus = emp?.employment_status;
    const empStatusColor =
        EMP_STATUS_COLOR[empStatus] || 'text-canvas-foreground bg-dim';

    const statCard = (href, icon, colorClass, value, label) => (
        <Link
            href={href || '#'}
            className={`${href ? 'cursor-pointer transition-shadow hover:shadow-md' : ''} block rounded-xl border border-edge bg-card p-4 text-center no-underline sm:p-5`}
        >
            <div className={`mx-auto mb-2 flex size-10 items-center justify-center rounded-lg ${colorClass}`}>
                <span className={`icon-[${icon}] size-5`} />
            </div>
            <div className="mb-1 break-words text-lg font-bold text-canvas-foreground sm:text-xl">{value}</div>
            <div className="text-[11px] uppercase tracking-widest text-dim-foreground">{label}</div>
        </Link>
    );

    const actionBtn = (href, icon, label) => (
        <Link
            href={href || '#'}
            className="flex h-auto flex-col items-center gap-2 rounded-xl border border-edge bg-card p-4 no-underline transition-colors hover:bg-dim"
        >
            <span className={`icon-[${icon}] size-6 text-brand`} />
            <span className="text-xs text-dim-foreground">{label}</span>
        </Link>
    );

    return (
        <AppLayout title="Dashboard">
            <Head title="Dashboard" />
            <div className="space-y-5 p-3 sm:p-4">
                <div>
                    {isAdmin && (
                        <Badge variant="outline" className="border-brand/50 text-brand">
                            Administrator Access
                        </Badge>
                    )}
                    {isHR && (
                        <Badge variant="outline" className="border-highlight/50 text-highlight">
                            HR Department Access
                        </Badge>
                    )}
                    {!isAdmin && !isHR && (
                        <Badge variant="outline">Employee Portal</Badge>
                    )}

                    <h1 className="mt-3 text-lg font-bold sm:text-xl">
                        Welcome back, {user.name}
                        {isAdmin && ' — full administrative access'}
                        {isHR && ' — HR access privileges'}
                    </h1>
                </div>

                {(isAdmin || isHR) && (
                    <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
                        {isAdmin &&
                            [
                                ['/users', 'tabler--users', 'text-brand bg-brand/10', counts.total_users, 'Total Users'],
                                ['/users', 'tabler--shield-check', 'text-danger bg-danger/10', counts.admin_users, 'Admins'],
                                ['/users', 'tabler--user', 'text-warning bg-warning/10', counts.hr_users, 'HR Personnel'],
                                ['/users', 'tabler--circle-check', 'text-success bg-success/10', counts.active_users, 'Active Accounts'],
                            ].map(([href, icon, color, value, label]) => statCard(href, icon, color, value ?? 0, label))}
                        {isHR &&
                            [
                                ['/employees', 'tabler--users-group', 'text-brand bg-brand/10', counts.total_employees, 'Total Employees'],
                                [null, 'tabler--calendar-check', 'text-success bg-success/10', counts.regular, 'Regular'],
                                [null, 'tabler--clock', 'text-warning bg-warning/10', counts.probationary, 'Probationary'],
                                [null, 'tabler--archive', 'text-dim-foreground bg-dim', counts.archived, 'Archived'],
                            ].map(([href, icon, color, value, label]) => statCard(href, icon, color, value ?? 0, label))}
                    </div>
                )}

                {!isAdmin && !isHR && (
                    <div className="grid grid-cols-2 gap-3">
                        {statCard(null, 'tabler--building', 'text-brand bg-brand/10', emp?.department ?? '—', 'Department')}
                        {statCard(null, 'tabler--id-badge', 'text-secondary/80 bg-secondary/10', emp?.position ?? '—', 'Position')}
                        {statCard(null, 'tabler--briefcase', empStatusColor, empStatus ?? '—', 'Employment Status')}
                        {statCard(
                            null,
                            'tabler--calendar',
                            'text-highlight bg-highlight/10',
                            emp?.date_hired
                                ? new Date(emp.date_hired).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
                                : '—',
                            'Date Hired'
                        )}
                    </div>
                )}

                {/* Quick actions */}
                <div>
                    <h2 className="mb-3 text-xs font-semibold uppercase tracking-widest text-dim-foreground">Quick Actions</h2>
                    <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
                        {isAdmin && (
                            <>
                                {actionBtn('/users', 'tabler--users', 'Manage Users')}
                                {actionBtn('/employees/create', 'tabler--user-plus', 'Add Employee')}
                                {actionBtn('/roles', 'tabler--lock', 'Manage Roles')}
                                {actionBtn('/audit-logs', 'tabler--history', 'View Logs')}
                            </>
                        )}
                        {isHR && (
                            <>
                                {actionBtn('/employees/create', 'tabler--user-plus', 'Add Employee')}
                                {actionBtn('/payroll', 'tabler--calculator', 'Payroll')}
                                {actionBtn('/work-requests', 'tabler--inbox', 'Work Requests')}
                                {actionBtn('/government-contributions', 'tabler--file-type-pdf', 'Contributions')}
                            </>
                        )}
                        {!isAdmin && !isHR && (
                            <>
                                {actionBtn('/profile', 'tabler--user', 'My Profile')}
                                {actionBtn('/payroll', 'tabler--receipt', 'Payslips')}
                                {actionBtn('/work-requests/create', 'tabler--calendar-off', 'New Request')}
                                {actionBtn('/employee-attendance', 'tabler--clock', 'Attendance')}
                            </>
                        )}
                    </div>
                </div>

                <div className="rounded-xl border border-edge bg-card p-5">
                    <h2 className="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-brand">
                        <CalendarDays className="size-4" />
                        Calendar
                    </h2>
                    <Suspense fallback={<CalendarSkeleton />}>
                        <FullCalendarWidget />
                    </Suspense>
                </div>

                <div className="rounded-xl border border-edge bg-card p-5">
                    <h2 className="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-brand">
                        <span className="icon-[tabler--id-badge] size-4" />
                        System Information
                    </h2>
                    <div className="flex flex-col">
                        {[
                            ['Name', user.name],
                            ['Email', user.email],
                            ['Role', isAdmin ? 'Administrator' : isHR ? 'HR Personnel' : 'Employee'],
                            ['Last Login', user.last_login_at
                                ? new Date(user.last_login_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) +
                                  ' ' +
                                  new Date(user.last_login_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
                                : 'First Login'],
                        ].map(([label, value], i, arr) => (
                            <div key={label} className={`flex items-center justify-between gap-4 py-3 ${i < arr.length - 1 ? 'border-b border-edge' : ''}`}>
                                <span className="text-sm text-dim-foreground">{label}</span>
                                <span className="text-right text-sm font-semibold">{value}</span>
                            </div>
                        ))}
                        <div className="flex items-center justify-between py-3">
                            <span className="text-sm text-dim-foreground">Account Status</span>
                            <Badge className={user.is_active ? 'border-transparent bg-brand/15 text-brand' : 'bg-danger/15 text-danger'}>
                                {user.is_active ? 'Active' : 'Inactive'}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

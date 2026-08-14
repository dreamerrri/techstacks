import React from 'react';
import { usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';

export default function Dashboard() {
    const { props } = usePage();
    const { user, counts } = props;

    const isAdmin = user.role === 'admin';
    const isHR = user.role === 'hr';
    const isEmployee = !isAdmin && !isHR;

    const getRoleBadge = () => {
        if (isAdmin) {
            return (
                <span className="inline-flex items-center gap-2 badge badge-soft badge-primary mb-4">
                    <i className="icon-[tabler--shield-check]"></i> Administrator Access
                </span>
            );
        } else if (isHR) {
            return (
                <span className="inline-flex items-center gap-2 badge badge-soft badge-primary mb-4">
                    <i className="icon-[tabler--user]"></i> HR Department Access
                </span>
            );
        }
        return null;
    };

    const getWelcomeMessage = () => {
        if (isAdmin) {
            return ` — You have full administrative access.`;
        } else if (isHR) {
            return ` — You have HR access privileges.`;
        }
        return '';
    };

    const StatCard = ({ href, icon, value, label, color = 'primary' }) => (
        <Link
            href={href}
            className={`card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer`}
        >
            <div className={`w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-${color} bg-${color}/10`}>
                <i className={icon}></i>
            </div>
            <div className="text-3xl font-bold text-base-content mb-1">{value}</div>
            <div className="text-xs text-base-content/80 uppercase tracking-widest">{label}</div>
        </Link>
    );

    const InfoCard = ({ icon, value, label, color = 'primary' }) => (
        <div className={`card bg-base-100 border border-base-300 p-5 text-center`}>
            <div className={`w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-${color} bg-${color}/10`}>
                <i className={icon}></i>
            </div>
            <div className="text-2xl font-small text-base-content mb-1">{value}</div>
            <div className="text-xs text-base-content/80 uppercase tracking-widest">{label}</div>
        </div>
    );

    const ActionButton = ({ href, icon, label }) => (
        <Link
            href={href}
            className="btn btn-soft flex-col h-auto py-5 gap-2"
        >
            <div className="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
                <i className={icon}></i>
            </div>
            <span className="text-base-content/80">{label}</span>
        </Link>
    );

    return (
        <div className="min-h-screen bg-base-200">
            {/* Header */}
            <div className="bg-white border-b border-base-300">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {getRoleBadge()}
                    <div className="text-base-content text-lg mb-5">
                        Welcome back, <strong>{user.name}</strong>
                        {getWelcomeMessage()}
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Stats Grid */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
                    {isAdmin && (
                        <>
                            <StatCard
                                href={route('users.index')}
                                icon="icon-[tabler--users]"
                                value={counts.total_users}
                                label="Total Users"
                                color="primary"
                            />
                            <StatCard
                                href={route('users.index')}
                                icon="icon-[tabler--shield-check]"
                                value={counts.admin_users}
                                label="Admin Users"
                                color="error"
                            />
                            <StatCard
                                href={route('users.index')}
                                icon="icon-[tabler--user]"
                                value={counts.hr_users}
                                label="HR Personnel"
                                color="warning"
                            />
                            <StatCard
                                href={route('users.index')}
                                icon="icon-[tabler--circle-check]"
                                value={counts.active_users}
                                label="Active Accounts"
                                color="success"
                            />
                        </>
                    )}

                    {isHR && (
                        <>
                            <StatCard
                                href={route('employees.index')}
                                icon="icon-[tabler--users]"
                                value={counts.total_employees}
                                label="Total Employees"
                                color="primary"
                            />
                            <InfoCard
                                icon="icon-[tabler--calendar-check]"
                                value={counts.regular}
                                label="Regular"
                                color="success"
                            />
                            <InfoCard
                                icon="icon-[tabler--clock]"
                                value={counts.probationary}
                                label="Probationary"
                                color="warning"
                            />
                            <InfoCard
                                icon="icon-[tabler--archive]"
                                value={counts.archived}
                                label="Archived"
                                color="base-content"
                            />
                        </>
                    )}

                    {isEmployee && (
                        <>
                            <InfoCard
                                icon="icon-[tabler--building]"
                                value={user.employee?.department || '—'}
                                label="Department"
                                color="primary"
                            />
                            <InfoCard
                                icon="icon-[tabler--id-badge]"
                                value={user.employee?.position || '—'}
                                label="Position"
                                color="secondary"
                            />
                            <InfoCard
                                icon="icon-[tabler--briefcase]"
                                value={user.employee?.employment_status || '—'}
                                label="Employment Status"
                                color={
                                    user.employee?.employment_status === 'Regular'
                                        ? 'success'
                                        : user.employee?.employment_status === 'Probationary'
                                        ? 'warning'
                                        : 'base-content'
                                }
                            />
                            <InfoCard
                                icon="icon-[tabler--calendar]"
                                value={
                                    user.employee?.date_hired
                                        ? new Date(user.employee.date_hired).toLocaleDateString('en-US', {
                                              month: 'short',
                                              day: 'numeric',
                                              year: 'numeric',
                                          })
                                        : '—'
                                }
                                label="Date Hired"
                                color="accent"
                            />
                        </>
                    )}
                </div>

                {/* Quick Actions */}
                <div className="card border border-base-300 shadow-sm p-6">
                    <h2 className="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
                        <i className="icon-[ph--lightning-fill]"></i>
                        {isAdmin ? 'Administrative Actions' : isHR ? 'HR Actions' : 'Quick Actions'}
                    </h2>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                        {isAdmin && (
                            <>
                                <ActionButton
                                    href={route('employees.create')}
                                    icon="icon-[tabler--user-plus]"
                                    label="Create Users"
                                />
                                <ActionButton
                                    href={route('roles.index')}
                                    icon="icon-[tabler--lock]"
                                    label="Manage Roles"
                                />
                                <button className="btn btn-soft flex-col h-auto py-5 gap-2">
                                    <div className="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
                                        <i className="icon-[tabler--database]"></i>
                                    </div>
                                    <span className="text-base-content/80">System Backup</span>
                                </button>
                                <ActionButton
                                    href={route('audit-logs.index')}
                                    icon="icon-[tabler--history]"
                                    label="View Logs"
                                />
                            </>
                        )}

                        {isHR && (
                            <>
                                <ActionButton
                                    href={route('employees.create')}
                                    icon="icon-[tabler--user-plus]"
                                    label="Add Employee"
                                />
                                <ActionButton
                                    href={route('payroll.index')}
                                    icon="icon-[tabler--calculator]"
                                    label="Manage Payroll"
                                />
                                <ActionButton
                                    href={route('manual-payroll-attendance.index')}
                                    icon="icon-[tabler--clock-plus]"
                                    label="Attendance"
                                />
                                <ActionButton
                                    href={route('work-requests.index')}
                                    icon="icon-[tabler--briefcase]"
                                    label="Work Requests"
                                />
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

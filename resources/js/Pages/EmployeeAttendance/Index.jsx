import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import ConfirmButton from '../../Components/ConfirmButton';

const fmtDate = (value, opts = { month: 'short', day: '2-digit', year: 'numeric' }) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-US', opts);
};

const fmtNum = (value) => Number(value || 0).toFixed(2);

const timeValue = (value) => value || '-';

function AttendanceTable({ attendances, canDelete, employeeId }) {
    return (
        <div className="overflow-x-auto overflow-y-auto" style={{ maxHeight: '50vh' }}>
            <table className="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Rendered Hours</th>
                        <th>Computed Days</th>
                        <th>Remarks</th>
                        {canDelete && <th className="text-center">Actions</th>}
                    </tr>
                </thead>
                <tbody>
                    {attendances.map((attendance) => (
                        <tr key={attendance.id} className="row-hover">
                            <td className="text-base-content">{fmtDate(attendance.date)}</td>
                            <td className="text-base-content">{timeValue(attendance.time_in)}</td>
                            <td className="text-base-content">{timeValue(attendance.time_out)}</td>
                            <td className="text-base-content font-semibold">{fmtNum(attendance.rendered_hours)} hrs</td>
                            <td className="text-base-content font-semibold">{fmtNum(attendance.computed_days)} days</td>
                            <td className="text-base-content/60">{attendance.remarks || '-'}</td>
                            {canDelete && (
                                <td className="text-center">
                                    <div className="flex gap-2 justify-center">
                                        <Link href="/employee-attendance/create" className="btn btn-soft btn-info btn-sm">
                                            <Icon name="tabler--pencil" className="size-4" />
                                        </Link>
                                        <ConfirmButton
                                            title="Delete Attendance?"
                                            text="Are you sure you want to delete this attendance record?"
                                            confirmText="Yes, delete it"
                                            cancelText="Back"
                                            url={`/employee-attendance/${attendance.id}`}
                                            method="delete"
                                            className="btn btn-soft btn-error btn-sm"
                                        >
                                            <Icon name="tabler--trash" className="size-4" />
                                        </ConfirmButton>
                                    </div>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export default function EmployeeAttendanceIndex({ currentPeriod, attendances, totalHours, totalDays, recentAttendances }) {
    const { auth } = usePage().props;
    const role = auth?.user?.role;
    const canDelete = role === 'admin' || role === 'hr';
    const roleBtnClass = role === 'admin' ? 'btn-error' : role === 'hr' ? 'btn-info' : 'btn-primary';

    return (
        <AppLayout>
            <Head title="My Attendance" />
            <div className="p-2 sm:p-4">
                <div className="flex justify-between items-center flex-wrap gap-3 mb-6">
                    <div>
                        <span className="badge badge-soft badge-info mb-2">
                            <Icon name="tabler--clock" className="size-3.5" /> My Attendance
                        </span>
                        <h2 className="text-lg font-bold text-base-content mt-2 mb-1">Attendance Records</h2>
                        <p className="text-base-content/60 m-0">Track your daily time-in/time-out records</p>
                    </div>
                    <div className="flex gap-2">
                        <Link href="/employee-attendance/create?new=true" className={`btn btn-soft ${roleBtnClass}`}>
                            <Icon name="tabler--plus" className="size-4" /> Add New Attendance
                        </Link>
                        <Link href="/employee-attendance/create" className="btn btn-soft">
                            <Icon name="tabler--pencil" className="size-4" /> Edit Today's Attendance
                        </Link>
                    </div>
                </div>

                {currentPeriod && (
                    <div className="card bg-base-100 border border-base-300 p-6 mb-6">
                        <h3 className="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
                            <Icon name="tabler--calendar" className="size-4 text-base-content/60" /> Current Payroll Period
                        </h3>
                        <div className="grid gap-4" style={{ gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))' }}>
                            <div className="p-4 bg-base-200 rounded-lg border-l-4 border-info">
                                <div className="text-xs text-base-content/60 mb-1">Period</div>
                                <div className="text-sm font-semibold text-base-content">
                                    {fmtDate(currentPeriod.cutoff_start, { month: 'short', day: '2-digit' })} - {fmtDate(currentPeriod.cutoff_end)}
                                </div>
                            </div>
                            <div className="p-4 bg-base-200 rounded-lg border-l-4 border-success">
                                <div className="text-xs text-base-content/60 mb-1">Total Rendered Hours</div>
                                <div className="text-lg font-bold text-success">{fmtNum(totalHours)} hrs</div>
                            </div>
                            <div className="p-4 bg-base-200 rounded-lg border-l-4 border-accent">
                                <div className="text-xs text-base-content/60 mb-1">Total Computed Days</div>
                                <div className="text-lg font-bold text-accent">{fmtNum(totalDays)} days</div>
                            </div>
                        </div>
                    </div>
                )}

                {currentPeriod && attendances.length > 0 && (
                    <div className="card bg-base-100 border border-base-300 p-0 overflow-hidden mb-6">
                        <div className="px-6 py-5 border-b border-base-300">
                            <h3 className="text-sm font-bold text-base-content m-0">Attendance Records — Current Period</h3>
                        </div>
                        <AttendanceTable attendances={attendances} canDelete={canDelete} />
                    </div>
                )}

                {recentAttendances.length > 0 && (
                    <div className="card bg-base-100 border border-base-300 p-0 overflow-hidden">
                        <div className="px-6 py-5 border-b border-base-300">
                            <h3 className="text-sm font-bold text-base-content m-0">Recent Attendance (Last 30 Days)</h3>
                        </div>
                        <AttendanceTable attendances={recentAttendances} canDelete={false} />
                    </div>
                )}

                {!currentPeriod && recentAttendances.length === 0 && (
                    <div className="card p-12 text-center">
                        <Icon name="tabler--clock-off" className="size-10 text-base-content/30 mx-auto mb-3" />
                        <h3 className="text-base-content/60 font-semibold mb-2">No Attendance Records</h3>
                        <p className="text-base-content/40 mb-6">Clock in to start tracking your daily attendance.</p>
                        <Link href="/employee-attendance/create" className={`btn btn-soft ${roleBtnClass}`}>
                            <Icon name="tabler--plus" className="size-4" /> Record Attendance
                        </Link>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

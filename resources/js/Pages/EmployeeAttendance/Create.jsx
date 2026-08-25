import { useMemo, useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import { toast } from '../../components/toast';

const nowDate = () => {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
};

const nowTime = () => {
    const now = new Date();
    return `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
};

function computeRenderedHours(timeIn, timeOut) {
    if (!timeIn || !timeOut) return 0;
    const [inH, inM] = timeIn.split(':').map(Number);
    const [outH, outM] = timeOut.split(':').map(Number);
    const inTotal = inH * 60 + inM;
    const outTotal = outH * 60 + outM;
    let totalMinutes = outTotal - inTotal;
    if (totalMinutes < 0) totalMinutes += 24 * 60;
    let hours = totalMinutes / 60;
    if (hours > 4) hours -= 1;
    return Math.max(0, hours);
}

export default function EmployeeAttendanceCreate({ todayAttendance }) {
    const { auth } = usePage().props;
    const role = auth?.user?.role;
    const roleBtnClass = role === 'admin' ? 'btn-error' : role === 'hr' ? 'btn-info' : 'btn-primary';

    const existing = todayAttendance || null;
    const hasTimeIn = Boolean(existing?.time_in);
    const hasTimeOut = Boolean(existing?.time_out);

    const { data, setData, post, processing, errors } = useForm({
        date: existing?.date ? existing.date.slice(0, 10) : '',
        time_in: existing?.time_in || '',
        time_out: existing?.time_out || '',
        remarks: existing?.remarks || '',
    });

    const [clockedIn, setClockedIn] = useState(hasTimeIn);
    const [clockedOut, setClockedOut] = useState(hasTimeOut);

    const handleClockIn = () => {
        setData('date', nowDate());
        setData('time_in', nowTime());
        setClockedIn(true);
    };

    const handleClockOut = () => {
        setData('time_out', nowTime());
        setClockedOut(true);
    };

    const renderedHours = useMemo(
        () => computeRenderedHours(data.time_in, data.time_out),
        [data.time_in, data.time_out]
    );

    const expectedTimeOut = useMemo(() => {
        if (!data.time_in || clockedOut) return null;
        const [inH, inM] = data.time_in.split(':').map(Number);
        const total = (inH + 9) % 24;
        const period = total >= 12 ? 'PM' : 'AM';
        const displayHours = total % 12 || 12;
        return `${displayHours}:${String(inM).padStart(2, '0')} ${period}`;
    }, [data.time_in, clockedOut]);

    const submit = (e) => {
        e.preventDefault();
        if (!data.time_in) {
            toast('error', 'Please clock in before saving attendance.');
            return;
        }
        if (!/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(data.time_in)) {
            toast('error', `Invalid time format. Please use HH:MM format. Current value: ${data.time_in}`);
            return;
        }
        post('/employee-attendance');
    };

    return (
        <AppLayout>
            <Head title="Add Attendance" />
            <div className="p-2 sm:p-4">
                <div className="mb-6">
                    <Link href="/employee-attendance" className="text-dim-foreground no-underline text-sm inline-flex items-center gap-1.5 mb-2 hover:text-brand">
                        <Icon name="tabler--arrow-left" className="size-4" /> Back to Attendance
                    </Link>
                    <div>
                        <span className="inline-flex items-center gap-1 rounded-full border border-transparent bg-highlight/12 px-2.5 py-0.5 text-xs font-medium text-highlight mb-2">
                            <Icon name="tabler--plus" className="size-3.5" /> Add Attendance
                        </span>
                    </div>
                    <h2 className="text-lg font-bold text-base-content mt-2 mb-1">Record Attendance</h2>
                    <p className="text-dim-foreground m-0">Add your time-in/time-out record for a specific date</p>
                </div>

                <div className="rounded-xl border border-edge bg-card p-0 overflow-hidden max-w-[600px]">
                    <div className="px-6 py-5 border-b border-edge">
                        <h3 className="text-sm font-bold text-base-content m-0">Attendance Details</h3>
                    </div>

                    <form onSubmit={submit} className="p-6">
                        {errors.date || errors.time_in || errors.time_out ? (
                            <div className="bg-error/10 border border-error/20 rounded-lg p-3 mb-5 text-sm text-error">
                                {errors.date || errors.time_in || errors.time_out}
                            </div>
                        ) : null}

                        <div className="mb-5">
                            <label className="label text-sm font-semibold text-base-content">Date</label>
                            <input type="date" name="date" readOnly value={data.date} className="input input-bordered w-full bg-base-200" />
                            <p className="text-dim-foreground text-xs mt-1">Auto-set when you clock in</p>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-5 mb-5">
                            <div>
                                <label className="label text-sm font-semibold text-base-content">Time In</label>
                                <input type="time" name="time_in" readOnly value={data.time_in} className="input input-bordered w-full bg-base-200" />
                                <p className="text-dim-foreground text-xs mt-1">Auto-set when you clock in</p>
                            </div>
                            <div>
                                <label className="label text-sm font-semibold text-base-content">Time Out</label>
                                <input type="time" name="time_out" readOnly value={data.time_out} className="input input-bordered w-full bg-base-200" />
                                <p className="text-dim-foreground text-xs mt-1">Auto-set when you clock out</p>
                            </div>
                        </div>

                        <div className="flex gap-3 mb-6">
                            <button type="button" onClick={handleClockIn} disabled={clockedIn} className="btn btn-success flex-1 disabled:opacity-50">
                                <Icon name="tabler--login-2" className="size-4" />
                                {clockedIn ? 'Clocked In' : 'Clock In'}
                            </button>
                            <button type="button" onClick={handleClockOut} disabled={!clockedIn || clockedOut} className="btn btn-warning flex-1 disabled:opacity-50">
                                <Icon name="tabler--logout-2" className="size-4" />
                                {clockedOut ? 'Clocked Out' : 'Clock Out'}
                            </button>
                        </div>

                        {expectedTimeOut && (
                            <div className="flex items-center gap-2 mb-6">
                                <div className="tooltip [--placement:top]">
                                    <span className="tooltip-toggle text-warning text-2xl cursor-help">
                                        <Icon name="tabler--clock" className="size-6" />
                                    </span>
                                    <span className="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
                                        <span className="tooltip-body bg-neutral/95 shadow-md rounded-lg px-3 py-2.5 text-xs normal-case text-neutral-content font-medium w-64 block">
                                            <span className="block font-semibold mb-1">Expected Clock Out Time</span>
                                            <span className="block text-sm mb-1"><strong>{expectedTimeOut}</strong></span>
                                            <span className="block text-neutral-content/70">9 hours after clock in, including 1-hour lunch break</span>
                                            <span className="block mt-2 pt-2 border-t border-neutral-content/20 text-[11px] text-neutral-content/60">
                                                Click "Clock Out" to record actual time
                                            </span>
                                        </span>
                                    </span>
                                </div>
                                <span className="text-dim-foreground text-sm">Hover to see expected clock out time</span>
                            </div>
                        )}

                        <div className="mb-6">
                            <label className="label text-sm font-semibold text-base-content">Remarks</label>
                            <input
                                type="text"
                                name="remarks"
                                value={data.remarks}
                                onChange={(e) => setData('remarks', e.target.value)}
                                placeholder="Optional notes (e.g., worked from home, late arrival, etc.)"
                                className="input input-bordered w-full"
                            />
                            {errors.remarks && <p className="label text-error text-xs mt-1">{errors.remarks}</p>}
                        </div>

                        <div className="bg-success/10 border border-success/20 rounded-lg p-4 mb-6">
                            <div className="text-sm font-semibold text-success mb-2 flex items-center gap-1.5">
                                <Icon name="tabler--info-circle" className="size-4" /> Computation Rules
                            </div>
                            <ul className="m-0 pl-5 text-sm text-success/90 list-disc">
                                <li>Less than 4 hours = 0 days</li>
                                <li>4-8 hours = 0.5 days</li>
                                <li>8 hours or more = 1 day</li>
                                <li>1 hour break is automatically deducted for shifts &gt; 4 hours</li>
                            </ul>
                            {data.time_in && data.time_out && (
                                <div className="mt-3 pt-3 border-t border-success/20 text-sm font-semibold text-success">
                                    <Icon name="tabler--calculator" className="size-4 inline" /> Rendered Hours: {renderedHours.toFixed(2)} hrs
                                </div>
                            )}
                        </div>

                        <div className="bg-warning/10 border border-warning/20 rounded-lg p-4 mb-6">
                            <div className="text-sm font-semibold text-warning mb-2 flex items-center gap-1.5">
                                <Icon name="tabler--alert-triangle" className="size-4" /> Auto Clock-Out
                            </div>
                            <p className="m-0 text-sm text-warning/90">
                                Attendance will automatically clock out at 9 hours (including 1-hour break). Any time beyond 9 hours will not be recorded.
                            </p>
                        </div>

                        <div className="flex gap-3">
                            <button type="submit" disabled={processing} className={`btn ${roleBtnClass} flex-1`}>
                                <Icon name="tabler--device-floppy" className="size-4" /> Save Attendance
                            </button>
                            <Link href="/employee-attendance" className="btn btn-soft">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}

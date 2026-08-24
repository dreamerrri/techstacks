import { useEffect, useMemo } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import FormField from '../../components/FormField';

// Local date (not UTC) so min/max are correct for the user's timezone
const toLocalISO = (d) => {
    const t = new Date(d);
    t.setMinutes(t.getMinutes() - t.getTimezoneOffset());
    return t.toISOString().split('T')[0];
};
const TODAY = toLocalISO(new Date());

function calculateOvertime(start, end) {
    if (!start || !end) return null;
    const [sh, sm] = start.split(':').map(Number);
    const [eh, em] = end.split(':').map(Number);
    let totalMinutes = (eh * 60 + em) - (sh * 60 + sm);
    if (totalMinutes < 0) totalMinutes += 24 * 60;
    const overtime = Math.max(0, totalMinutes / 60 - 8);
    return Math.round(overtime * 10) / 10;
}

const toTimeInput = (value) => (value ? value.slice(0, 5) : '');

export default function WorkRequestsEdit({ workRequest, upcomingHolidays }) {
    const { data, setData, put, processing, errors } = useForm({
        request_type: workRequest.request_type || '',
        work_date: workRequest.work_date ? workRequest.work_date.slice(0, 10) : '',
        start_time: toTimeInput(workRequest.start_time),
        end_time: toTimeInput(workRequest.end_time),
        estimated_hours: workRequest.estimated_hours ?? '',
        reason: workRequest.reason ?? '',
    });

    const isOvertime = data.request_type === 'overtime';
    const overtimeHours = useMemo(
        () => (isOvertime ? calculateOvertime(data.start_time, data.end_time) : null),
        [isOvertime, data.start_time, data.end_time]
    );

    const handleOvertime = (start, end) => {
        setData('start_time', start);
        setData('end_time', end);
        if (isOvertime) {
            const ot = calculateOvertime(start, end);
            if (ot !== null && ot > 0 && (data.estimated_hours === '' || parseFloat(data.estimated_hours) === 0)) {
                setData('estimated_hours', ot.toFixed(1));
            }
        }
    };

    useEffect(() => {
        if (isOvertime && overtimeHours !== null && (data.estimated_hours === '' || parseFloat(data.estimated_hours) === 0)) {
            setData('estimated_hours', overtimeHours.toFixed(1));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isOvertime]);

    const submit = (e) => {
        e.preventDefault();
        put(`/work-requests/${workRequest.id}`);
    };

    return (
        <AppLayout>
            <Head title="Edit Work Request" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href={`/work-requests/${workRequest.id}`} className="back-link text-subtle no-underline text-sm hover:text-primary">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Request
                    </Link>
                </div>

                <div className="card bg-base-100 shadow-md p-6 max-w-3xl">
                    <div className="badge badge-info gap-1 mb-2">
                        <Icon name="ph--pencil-fill" className="size-4" /> Edit Request
                    </div>
                    <h2 className="text-lg font-bold text-base-content mb-1">Edit Work Request #{workRequest.id}</h2>
                    <p className="text-subtle text-sm mb-6">Modify your pending work request</p>

                    <form onSubmit={submit}>
                        <FormField label="Request Type" required error={errors.request_type}>
                            <select
                                name="request_type"
                                value={data.request_type}
                                onChange={(e) => setData('request_type', e.target.value)}
                                className="select select-bordered w-full"
                                required
                            >
                                <option value="">Select type...</option>
                                <option value="weekend">Weekend Work</option>
                                <option value="holiday">Holiday Work</option>
                                <option value="overtime">Overtime</option>
                                <option value="half_day">Half Day</option>
                            </select>
                        </FormField>

                        <FormField label="Work Date" required error={errors.work_date}>
                            <input
                                type="date"
                                name="work_date"
                                value={data.work_date}
                                min={TODAY}
                                onChange={(e) => setData('work_date', e.target.value)}
                                className="input input-bordered w-full"
                                required
                            />
                        </FormField>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <FormField label="Start Time">
                                <input
                                    type="time"
                                    name="start_time"
                                    value={data.start_time}
                                    onChange={(e) => handleOvertime(e.target.value, data.end_time)}
                                    className="input input-bordered w-full"
                                />
                            </FormField>
                            <FormField label="End Time">
                                <input
                                    type="time"
                                    name="end_time"
                                    value={data.end_time}
                                    onChange={(e) => handleOvertime(data.start_time, e.target.value)}
                                    className="input input-bordered w-full"
                                />
                            </FormField>
                        </div>

                        {isOvertime && overtimeHours !== null && (
                            <div className="bg-info/10 border-s-4 border-info rounded-lg p-3 mt-2 mb-4">
                                <span className="font-semibold text-xs text-info flex items-center gap-1">
                                    <Icon name="ph--clock-fill" className="size-4" />
                                    Approximate Overtime Hours: {overtimeHours.toFixed(1)}
                                </span>
                            </div>
                        )}

                        <FormField label="Estimated Hours" error={errors.estimated_hours}>
                            <input
                                type="number"
                                name="estimated_hours"
                                value={data.estimated_hours}
                                min="0"
                                max="24"
                                step="0.5"
                                onChange={(e) => setData('estimated_hours', e.target.value)}
                                className="input input-bordered w-full"
                            />
                        </FormField>

                        <FormField label="Reason" error={errors.reason}>
                            <textarea
                                name="reason"
                                rows="4"
                                maxLength="500"
                                value={data.reason}
                                onChange={(e) => setData('reason', e.target.value)}
                                className="textarea textarea-bordered w-full resize-y"
                                placeholder="Provide a reason for this work request..."
                            />
                        </FormField>

                        <div className="flex gap-3 flex-wrap pt-4 border-t border-base-300">
                            <button type="submit" className="btn btn-soft btn-primary" disabled={processing}>
                                <Icon name="ph--floppy-disk-fill" className="size-4" /> Update Request
                            </button>
                            <Link href={`/work-requests/${workRequest.id}`} className="btn btn-soft">Cancel</Link>
                        </div>
                    </form>
                </div>

                {upcomingHolidays?.length > 0 && (
                    <div className="card bg-base-100 shadow-md p-6 mt-4">
                        <h3 className="m-0 mb-4 flex items-center gap-2 text-sm font-bold text-base-content">
                            <Icon name="ph--calendar-fill" className="size-4 text-subtle" /> Upcoming Holidays
                        </h3>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            {upcomingHolidays.map((holiday) => (
                                <div key={holiday.id} className="p-3 bg-base-200 border-s-4 rounded-lg">
                                    <div className="text-sm font-semibold text-base-content">{holiday.name}</div>
                                    <div className="text-xs text-subtle mt-1">{new Date(holiday.date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}</div>
                                    <div className="mt-1">
                                        <span className="badge badge-neutral badge-sm font-semibold">
                                            {holiday.type.charAt(0).toUpperCase() + holiday.type.slice(1)}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

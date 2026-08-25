import { useMemo, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';

function toInputDate(d) {
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${mm}-${dd}`;
}

function fmtLong(d) {
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function computeDates(val) {
    if (!val) return null;
    const [year, month, day] = val.split('-').map(Number);
    const start = new Date(year, month - 1, day);
    const isP1 = day <= 15;
    let end, payroll;
    if (isP1) {
        end = new Date(year, month - 1, 15);
        payroll = new Date(year, month - 1, 20);
    } else {
        const lastDay = new Date(year, month, 0);
        end = lastDay;
        payroll = new Date(year, month, 5);
    }
    return {
        start,
        end,
        payroll,
        isP1,
        endInput: toInputDate(end),
        payrollInput: toInputDate(payroll),
    };
}

export default function CreatePayrollPeriod() {
    const { data, setData, post, errors, processing } = useForm({
        cutoff_start: '',
        cutoff_end: '',
        payroll_date: '',
    });

    const computed = useMemo(() => (data.cutoff_start ? computeDates(data.cutoff_start) : null), [data.cutoff_start]);

    const handleStartChange = (value) => {
        const c = computeDates(value);
        setData({
            cutoff_start: value,
            cutoff_end: c ? c.endInput : '',
            payroll_date: c ? c.payrollInput : '',
        });
    };

    const submit = (e) => {
        e.preventDefault();
        post('/payroll-periods', {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout title="Create Payroll Period">
            <Head title="Create Payroll Period" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/manual-payroll-attendance" className="text-dim-foreground no-underline text-sm inline-flex items-center gap-1.5 mb-2 hover:text-brand">
                        <Icon name="tabler--arrow-left" className="size-4" /> Back to Payroll Periods
                    </Link>
                </div>

                <span className="inline-flex items-center gap-1 rounded-full border border-transparent bg-highlight/12 px-2.5 py-0.5 text-xs font-medium text-highlight mb-2">
                    <Icon name="tabler--calendar-plus" className="size-3.5" /> Create Payroll Period
                </span>
                <h2 className="text-lg font-bold text-base-content mt-2 mb-1">New Payroll Period</h2>
                <p className="text-dim-foreground m-0">Pick a start date — end date and pay date are computed automatically</p>

                <div className="max-w-[600px] mt-6">
                    <div className="rounded-xl border border-edge bg-card p-0 overflow-hidden">
                        <div className="px-6 py-5 border-b border-edge flex items-center justify-between">
                            <h3 className="text-sm font-bold text-base-content m-0">Payroll Period Details</h3>
                            <span className={`badge ${computed ? (computed.isP1 ? 'badge-soft badge-info' : 'badge-soft badge-secondary') : 'hidden'}`}>
                                {computed ? (computed.isP1 ? '1st Half' : '2nd Half') : ''}
                            </span>
                        </div>

                        <form onSubmit={submit} className="p-6">
                            <div className="mb-5">
                                <label className="label label-text font-semibold text-sm text-base-content">Cutoff Start Date</label>
                                <input
                                    type="date"
                                    required
                                    value={data.cutoff_start}
                                    onChange={(e) => handleStartChange(e.target.value)}
                                    className={`input input-bordered w-full ${errors.cutoff_start ? 'border-error' : ''}`}
                                />
                                <p className="text-dim-foreground text-xs mt-1">The period will cover 15 days starting from this date.</p>
                                {errors.cutoff_start && <p className="text-error text-sm mt-1.5">{errors.cutoff_start}</p>}
                            </div>

                            <div className="mb-5">
                                <label className="label label-text font-semibold text-sm text-base-content">
                                    Cutoff End Date <span className="font-normal text-dim-foreground/70 text-xs">— auto</span>
                                </label>
                                <input type="text" disabled value={computed ? fmtLong(computed.end) : 'Computed after picking start date'} placeholder="Computed after picking start date" className="input input-bordered w-full bg-base-200" />
                            </div>

                            <div className="mb-7">
                                <label className="label label-text font-semibold text-sm text-base-content">
                                    Payroll Date <span className="font-normal text-dim-foreground/70 text-xs">— auto (5 days after end)</span>
                                </label>
                                <input type="text" disabled value={computed ? fmtLong(computed.payroll) : 'Computed after picking start date'} placeholder="Computed after picking start date" className="input input-bordered w-full bg-base-200" />
                            </div>

                            {computed && (
                                <div className="bg-info/10 border border-info/20 rounded-lg px-4 py-3.5 mb-6">
                                    <p className="m-0 mb-1.5 text-sm font-semibold text-info flex items-center gap-1.5">
                                        <Icon name="tabler--info-circle" className="size-4" /> Period Summary
                                    </p>
                                    <p className="m-0 text-sm text-info/90 leading-relaxed">
                                        <strong>Start:</strong> {fmtLong(computed.start)}
                                        <br />
                                        <strong>End:</strong> {fmtLong(computed.end)}
                                        <br />
                                        <strong>Pay Date:</strong> {fmtLong(computed.payroll)}
                                        <br />
                                        <strong>Phase:</strong> {computed.isP1 ? '1st Half' : '2nd Half'}
                                    </p>
                                </div>
                            )}

                            <div className="flex gap-3">
                                <button type="submit" disabled={!computed || processing} className="btn btn-soft btn-primary flex-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <Icon name="tabler--device-floppy" className="size-4" /> Create Payroll Period
                                </button>
                                <Link href="/manual-payroll-attendance" className="btn btn-soft btn-error">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
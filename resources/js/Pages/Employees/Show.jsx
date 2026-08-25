import { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import DetailRow from '../../components/DetailRow';
import StatusBadge from '../../components/StatusBadge';
import ConfirmButton from '../../components/ConfirmButton';
import { toast } from '../../components/toast';
import NativeSelect from '../../components/NativeSelect';

const STATUS_META = {
    Regular: 'success',
    Probationary: 'warning',
    Contractual: 'info',
    'Part-time': 'neutral',
};

const fmtDate = (value, opts = { month: 'short', day: '2-digit', year: 'numeric' }) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-US', opts);
};

const fmtMoney = (value) => `₱${Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const fmtNum = (value) => Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

export default function EmployeesShow({ employee, payrollInput }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isHR = auth?.user?.role === 'hr';
    const canManage = isAdmin || isHR;

    const [form, setForm] = useState(null);

    const allowance = useForm({ name: '', amount: '', type: 'monthly', description: '' });
    const benefit = useForm({ name: '', amount: '', type: 'monthly', description: '' });

    const saveAllowance = (e) => {
        e.preventDefault();
        allowance.post(`/employees/${employee.id}/allowances`, {
            preserveScroll: true,
            onSuccess: () => {
                allowance.reset();
                setForm(null);
                toast('success', 'Allowance added successfully.');
            },
        });
    };

    const saveBenefit = (e) => {
        e.preventDefault();
        benefit.post(`/employees/${employee.id}/benefits`, {
            preserveScroll: true,
            onSuccess: () => {
                benefit.reset();
                setForm(null);
                toast('success', 'Benefit added successfully.');
            },
        });
    };

    const period = payrollInput?.payroll_period;

    const payrollStats = [
        { label: 'Days Worked', value: `${fmtNum(payrollInput?.days_worked)} Days`, cls: 'text-base-content' },
        { label: 'Overtime Hours', value: `${fmtNum(payrollInput?.overtime_hours)} Hrs`, cls: 'text-error' },
        { label: 'Late Hours', value: `${fmtNum(payrollInput?.late_hours)} Hrs`, cls: 'text-error' },
        { label: 'Allowances', value: fmtMoney(payrollInput?.allowances), cls: 'text-success' },
        { label: 'Deductions', value: fmtMoney(payrollInput?.deductions), cls: 'text-error' },
        { label: 'Net Pay', value: fmtMoney(payrollInput?.net_pay), cls: 'text-success' },
    ];

    const allowanceForm = (
        <div className="mt-4 p-4 bg-success/10 rounded-xl border border-success/20">
            <h4 className="text-sm font-bold text-success mb-3 flex items-center gap-2">
                <Icon name="ph--gift-fill" className="size-4" /> Add Allowance
            </h4>
            <form onSubmit={saveAllowance}>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    <div className="fieldset">
                        <label className="label text-xs text-dim-foreground">Name</label>
                        <input type="text" value={allowance.data.name} onChange={(e) => allowance.setData('name', e.target.value)} required className="input input-bordered input-sm w-full" />
                    </div>
                    <div className="fieldset">
                        <label className="label text-xs text-dim-foreground">Amount</label>
                        <input type="number" step="0.01" min="0" value={allowance.data.amount} onChange={(e) => allowance.setData('amount', e.target.value)} required className="input input-bordered input-sm w-full" />
                    </div>
                    <div className="fieldset">
                        <label className="label text-xs text-dim-foreground">Type</label>
                        <NativeSelect value={allowance.data.type} onChange={(e) => allowance.setData('type', e.target.value)} className="select select-bordered select-sm w-full">
                            <option value="monthly">Monthly</option>
                            <option value="one-time">One-time</option>
                        </NativeSelect>
                    </div>
                </div>
                <div className="fieldset mb-3">
                    <label className="label text-xs text-dim-foreground">Description (optional)</label>
                    <textarea rows="2" value={allowance.data.description} onChange={(e) => allowance.setData('description', e.target.value)} className="textarea textarea-bordered textarea-sm w-full" />
                </div>
                {allowance.errors && Object.keys(allowance.errors).length > 0 && (
                    <div className="text-error text-xs mb-3">{Object.values(allowance.errors)[0]}</div>
                )}
                <div className="flex gap-2">
                    <button type="submit" className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-transparent bg-brand px-3 text-xs font-medium text-brand-foreground no-underline transition-colors hover:bg-brand/90" disabled={allowance.processing}>
                        <Icon name="ph--floppy-disk-fill" className="size-4" /> Save Allowance
                    </button>
                    <button type="button" onClick={() => setForm(null)} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">Cancel</button>
                </div>
            </form>
        </div>
    );

    const benefitForm = (
        <div className="mt-4 p-4 bg-info/10 rounded-xl border border-info/20">
            <h4 className="text-sm font-bold text-info mb-3 flex items-center gap-2">
                <Icon name="ph--gift-fill" className="size-4" /> Add Benefit
            </h4>
            <form onSubmit={saveBenefit}>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    <div className="fieldset">
                        <label className="label text-xs text-dim-foreground">Name</label>
                        <input type="text" value={benefit.data.name} onChange={(e) => benefit.setData('name', e.target.value)} required className="input input-bordered input-sm w-full" />
                    </div>
                    <div className="fieldset">
                        <label className="label text-xs text-dim-foreground">Amount</label>
                        <input type="number" step="0.01" min="0" value={benefit.data.amount} onChange={(e) => benefit.setData('amount', e.target.value)} required className="input input-bordered input-sm w-full" />
                    </div>
                    <div className="fieldset">
                        <label className="label text-xs text-dim-foreground">Type</label>
                        <NativeSelect value={benefit.data.type} onChange={(e) => benefit.setData('type', e.target.value)} className="select select-bordered select-sm w-full">
                            <option value="monthly">Monthly</option>
                            <option value="one-time">One-time</option>
                        </NativeSelect>
                    </div>
                </div>
                <div className="fieldset mb-3">
                    <label className="label text-xs text-dim-foreground">Description (optional)</label>
                    <textarea rows="2" value={benefit.data.description} onChange={(e) => benefit.setData('description', e.target.value)} className="textarea textarea-bordered textarea-sm w-full" />
                </div>
                {benefit.errors && Object.keys(benefit.errors).length > 0 && (
                    <div className="text-error text-xs mb-3">{Object.values(benefit.errors)[0]}</div>
                )}
                <div className="flex gap-2">
                    <button type="submit" className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60" disabled={benefit.processing}>
                        <Icon name="ph--floppy-disk-fill" className="size-4" /> Save Benefit
                    </button>
                    <button type="button" onClick={() => setForm(null)} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">Cancel</button>
                </div>
            </form>
        </div>
    );

    return (
        <AppLayout>
            <Head title={employee.full_name} />
            <div className="p-2 sm:p-4">
                <div className="flex justify-between items-center flex-wrap gap-3 mb-5">
                    <Link href="/employees" className="back-link text-dim-foreground no-underline text-sm hover:text-success">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Employee List
                    </Link>
                    <div className="flex gap-2 flex-wrap">
                        <Link href={`/employees/${employee.id}/edit`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-warning/40 bg-warning/10 px-3 text-xs font-medium text-warning no-underline transition-colors hover:bg-warning/20">
                            <Icon name="ph--pencil-fill" className="size-4" /> Edit
                        </Link>
                        {canManage && (
                            <ConfirmButton
                                title="Archive Employee?"
                                text="This employee will be moved to the archive."
                                confirmText="Yes, archive"
                                url={`/employees/${employee.id}/archive`}
                                method="patch"
                                className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10"
                            >
                                <Icon name="ph--archive-fill" className="size-4" /> Archive
                            </ConfirmButton>
                        )}
                    </div>
                </div>

                <div className="rounded-xl border border-edge bg-card p-5 flex items-center gap-5 flex-wrap mb-5">
                    <div className="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
                        {employee.user?.photo_url ? (
                            <img src={employee.user.photo_url} alt={employee.full_name} className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-16 h-16 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-2xl font-bold">
                                {employee.first_name.charAt(0).toUpperCase()}
                            </div>
                        )}
                    </div>
                    <div>
                        <h2 className="text-xl font-bold text-base-content m-0 mb-1">{employee.full_name}</h2>
                        <p className="text-dim-foreground m-0">{employee.position} — {employee.department}</p>
                        <div className="mt-2">
                            <StatusBadge type={STATUS_META[employee.employment_status] ?? 'neutral'}>
                                {employee.employment_status}
                            </StatusBadge>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div className="rounded-xl border border-edge bg-card p-5">
                        <h2 className="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
                            <Icon name="tabler--user" className="size-4 text-error" /> Personal Information
                        </h2>
                        <div className="flex flex-col text-sm">
                            <DetailRow label="Employee ID"><span className="font-mono">{employee.employee_id}</span></DetailRow>
                            <DetailRow label="Birthdate">{fmtDate(employee.birthdate, { month: 'long', day: '2-digit', year: 'numeric' })}</DetailRow>
                            <DetailRow label="Gender">{employee.gender}</DetailRow>
                            <DetailRow label="Civil Status">{employee.civil_status}</DetailRow>
                            <DetailRow label="Contact No.">{employee.contact_number}</DetailRow>
                            <DetailRow label="Email">{employee.email}</DetailRow>
                            <DetailRow label="Address" border={false}><span className="break-words">{employee.address}</span></DetailRow>
                        </div>
                    </div>

                    <div className="rounded-xl border border-edge bg-card p-5">
                        <h2 className="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
                            <Icon name="ph--briefcase-fill" className="size-4 text-error" /> Employment Details
                        </h2>
                        <div className="flex flex-col text-sm">
                            <DetailRow label="Department">{employee.department}</DetailRow>
                            <DetailRow label="Position">{employee.position}</DetailRow>
                            <DetailRow label="Date Hired">{fmtDate(employee.date_hired, { month: 'long', day: '2-digit', year: 'numeric' })}</DetailRow>
                            <DetailRow label="Salary Type">{employee.salary_type}</DetailRow>
                            <DetailRow label="Basic Salary" border={false}><span className="font-bold text-error text-base">{fmtMoney(employee.basic_salary)}</span></DetailRow>
                        </div>
                    </div>

                    <div className="rounded-xl border border-edge bg-card p-5 md:col-span-2">
                        <div className="flex justify-between items-center mb-4 flex-wrap gap-2">
                            <h2 className="text-sm font-bold text-base-content m-0 flex items-center gap-2">
                                <Icon name="ph--clock-fill" className="size-4 text-error" /> Payroll Input Summary
                            </h2>
                            {canManage && (
                                payrollInput ? (
                                    <Link href={`/manual-payroll-attendance/period/${period?.id}/employee/${employee.id}`} className="btn btn-soft btn-info btn-xs">
                                        <Icon name="ph--pencil-fill" className="size-4" /> Edit
                                    </Link>
                                ) : (
                                    <Link href="/manual-payroll-attendance" className="btn btn-soft btn-success btn-xs">
                                        <Icon name="ph--plus-fill" className="size-4" /> Add Payroll Input
                                    </Link>
                                )
                            )}
                        </div>
                        <div className="bg-base-200 rounded-xl p-5">
                            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-center">
                                {payrollStats.map((stat) => (
                                    <div key={stat.label}>
                                        <div className="text-xs text-dim-foreground mb-1">{stat.label}</div>
                                        <div className={`text-lg font-bold ${stat.cls}`}>{stat.value}</div>
                                    </div>
                                ))}
                            </div>
                            {payrollInput && period ? (
                                <div className="mt-4 px-4 py-2 bg-info/10 rounded-lg text-xs text-info text-center">
                                    <Icon name="ph--info-fill" className="size-3.5 inline" />
                                    Showing payroll input for period: {fmtDate(period.cutoff_start, { month: 'short', day: '2-digit' })} - {fmtDate(period.cutoff_end, { month: 'short', day: '2-digit', year: 'numeric' })}
                                </div>
                            ) : (
                                <div className="mt-4 px-4 py-2 bg-warning/10 rounded-lg text-xs text-warning text-center">
                                    <Icon name="ph--info-fill" className="size-3.5 inline" /> No payroll input data found
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="rounded-xl border border-edge bg-card p-5 md:col-span-2">
                        <div className="flex justify-between items-center">
                            <div>
                                <h2 className="text-sm font-bold text-base-content m-0 flex items-center gap-2">
                                    <Icon name="ph--identification-card-fill" className="size-4 text-error" /> Government Contributions
                                </h2>
                                <p className="text-dim-foreground text-xs mt-1 mb-0">View and manage government contribution rates for this employee.</p>
                            </div>
                            <Link href={`/government-contributions/${employee.id}`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">
                                <Icon name="ph--eye-fill" className="size-4" /> View Contributions
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-xl border border-edge bg-card p-5 md:col-span-2">
                        <div className="flex justify-between items-center">
                            <div>
                                <h2 className="text-sm font-bold text-base-content m-0 flex items-center gap-2">
                                    <Icon name="ph--clock-fill" className="size-4 text-error" /> Attendance Records
                                </h2>
                                <p className="text-dim-foreground text-xs mt-1 mb-0">View daily time-in/time-out records and attendance history for this employee.</p>
                            </div>
                            <Link href={`/employee-attendance/employee/${employee.id}`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-transparent bg-brand px-3 text-xs font-medium text-brand-foreground no-underline transition-colors hover:bg-brand/90">
                                <Icon name="ph--eye-fill" className="size-4" /> View Attendance
                            </Link>
                        </div>
                    </div>

                    {canManage && (
                        <div className="rounded-xl border border-edge bg-card p-5 md:col-span-2">
                            <div className="flex justify-between items-center mb-4 flex-wrap gap-2">
                                <h2 className="text-sm font-bold text-base-content m-0 flex items-center gap-2">
                                    <Icon name="ph--gift-fill" className="size-4 text-error" /> Allowances & Benefits
                                </h2>
                                <div className="flex gap-2">
                                    <button type="button" onClick={() => setForm(form === 'allowance' ? null : 'allowance')} className="btn btn-soft btn-success btn-xs">
                                        <Icon name="ph--plus-fill" className="size-4" /> Add Allowance
                                    </button>
                                    <button type="button" onClick={() => setForm(form === 'benefit' ? null : 'benefit')} className="btn btn-soft btn-info btn-xs">
                                        <Icon name="ph--plus-fill" className="size-4" /> Add Benefit
                                    </button>
                                </div>
                            </div>

                            <div className="mb-6">
                                <h3 className="text-sm font-bold text-dim-foreground mb-3">Allowances</h3>
                                {(employee.active_allowances || []).length > 0 ? (
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        {employee.active_allowances.map((allowanceItem) => (
                                            <div key={allowanceItem.id} className="bg-base-200 p-3 rounded-lg border-l-4 border-success">
                                                <div className="flex justify-between items-start">
                                                    <div>
                                                        <div className="font-semibold text-base-content text-sm">{allowanceItem.name}</div>
                                                        <div className="text-dim-foreground text-xs mt-0.5 capitalize">{allowanceItem.type}</div>
                                                        {allowanceItem.description && <div className="text-dim-foreground/70 text-xs mt-1">{allowanceItem.description}</div>}
                                                    </div>
                                                    <div className="text-right">
                                                        <div className="font-bold text-success text-sm">{fmtMoney(allowanceItem.amount)}</div>
                                                        <ConfirmButton
                                                            title="Delete Allowance?"
                                                            text="This allowance will be permanently deleted."
                                                            confirmText="Yes, delete it"
                                                            url={`/employees/${employee.id}/allowances/${allowanceItem.id}`}
                                                            method="delete"
                                                            className="btn btn-soft btn-error btn-xs mt-1"
                                                        >
                                                            <Icon name="ph--trash-fill" className="size-4" />
                                                        </ConfirmButton>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="py-3 bg-base-200 rounded-lg text-center text-dim-foreground/70 text-xs">No allowances added</div>
                                )}
                            </div>

                            <div className="mb-4">
                                <h3 className="text-sm font-bold text-dim-foreground mb-3">Benefits</h3>
                                {(employee.active_benefits || []).length > 0 ? (
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        {employee.active_benefits.map((benefitItem) => (
                                            <div key={benefitItem.id} className="bg-base-200 p-3 rounded-lg border-l-4 border-info">
                                                <div className="flex justify-between items-start">
                                                    <div>
                                                        <div className="font-semibold text-base-content text-sm">{benefitItem.name}</div>
                                                        <div className="text-dim-foreground text-xs mt-0.5 capitalize">{benefitItem.type}</div>
                                                        {benefitItem.description && <div className="text-dim-foreground/70 text-xs mt-1">{benefitItem.description}</div>}
                                                    </div>
                                                    <div className="text-right">
                                                        <div className="font-bold text-info text-sm">{fmtMoney(benefitItem.amount)}</div>
                                                        <ConfirmButton
                                                            title="Delete Benefit?"
                                                            text="This benefit will be permanently deleted."
                                                            confirmText="Yes, delete it"
                                                            url={`/employees/${employee.id}/benefits/${benefitItem.id}`}
                                                            method="delete"
                                                            className="btn btn-soft btn-error btn-xs mt-1"
                                                        >
                                                            <Icon name="ph--trash-fill" className="size-4" />
                                                        </ConfirmButton>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="py-3 bg-base-200 rounded-lg text-center text-dim-foreground/70 text-xs">No benefits added</div>
                                )}
                            </div>

                            {form === 'allowance' && allowanceForm}
                            {form === 'benefit' && benefitForm}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

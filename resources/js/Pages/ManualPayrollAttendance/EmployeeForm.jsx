import { useMemo, useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';

const fmtDate = (value, opts = { month: 'long', day: '2-digit', year: 'numeric' }) => {
    if (!value) return 'N/A';
    return new Date(value + 'T00:00:00').toLocaleDateString('en-US', opts);
};

const fmtMoney = (value) => '₱' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtNum = (value, digits = 2) => Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

function ApprovedRequests({ approvedRequests }) {
    if (!approvedRequests.length) return null;
    return (
        <div className="bg-success/10 border border-base-300 rounded-lg p-4 mb-4">
            <div className="flex items-center mb-4 gap-3">
                <Icon name="tabler--calendar-check" className="size-5 text-success" />
                <span className="font-semibold text-success">Approved Work Requests ({approvedRequests.length})</span>
            </div>
            <div className="text-sm text-success-content">
                {approvedRequests.map((r) => (
                    <div key={r.id} className="py-2 border-b border-success/20">
                        <span style={{ fontWeight: 600 }}>{r.request_type ? r.request_type.charAt(0).toUpperCase() + r.request_type.slice(1) : ''}</span>
                        <span className="text-base-content/60"> | </span>
                        <span>{fmtDate(r.work_date, { month: 'short', day: '2-digit', year: 'numeric' })}</span>
                        {(r.calculated_overtime_hours || r.estimated_hours) && (
                            <>
                                <span className="text-base-content/60"> | </span>
                                <span>{fmtNum(r.calculated_overtime_hours || r.estimated_hours, 1)} hrs</span>
                            </>
                        )}
                    </div>
                ))}
            </div>
            <p className="text-xs text-success-content mt-2 mb-0">These requests have been auto-populated to the payroll fields below.</p>
        </div>
    );
}

function CashAdvanceBox({ cashAdvance, isSecondHalf }) {
    const fullyPaid = cashAdvance.fully_paid || [];
    const outstanding = cashAdvance.outstanding || [];
    return (
        <>
            {fullyPaid.length > 0 && (
                <div className="bg-success/10 border border-success rounded-lg p-4 mb-4">
                    <div className="flex items-center mb-4 gap-3">
                        <Icon name="tabler--circle-check" className="size-5 text-success" />
                        <span className="font-semibold text-success">Fully Paid Cash Advances ({fullyPaid.length})</span>
                    </div>
                    <div className="text-sm text-success-content">
                        {fullyPaid.map((r, i) => (
                            <div key={i} className="py-2 border-b border-success/20">
                                <span className="font-semibold">{fmtMoney(r.amount)}</span>
                                <span className="text-base-content/60"> | </span>
                                <span>{fmtDate(r.request_date, { month: 'short', day: '2-digit', year: 'numeric' })}</span>
                                <span className="text-base-content/60"> | </span>
                                <span className="font-semibold text-success">Fully Paid: {fmtMoney(r.amount_paid)}</span>
                            </div>
                        ))}
                    </div>
                    <p className="text-xs text-success-content mt-2 mb-0">These cash advances have been fully repaid and no further deductions will be made.</p>
                </div>
            )}
            {outstanding.length > 0 && (
                <div className="bg-warning/10 border border-warning rounded-lg p-4 mb-4">
                    <div className="flex items-center mb-4 gap-3">
                        <Icon name="tabler--moneybag" className="size-5 text-warning" />
                        <span className="font-semibold text-warning">Outstanding Cash Advances ({outstanding.length})</span>
                    </div>
                    <div className="text-sm text-warning-content">
                        {outstanding.map((r, i) => (
                            <div key={i} className="py-2 border-b border-warning/20">
                                <span className="font-semibold">{fmtMoney(r.amount)}</span>
                                <span className="text-base-content/60"> | </span>
                                <span>{fmtDate(r.request_date, { month: 'short', day: '2-digit', year: 'numeric' })}</span>
                                <span className="text-base-content/60"> | </span>
                                <span>Paid: {fmtMoney(r.amount_paid)}</span>
                                <span className="text-base-content/60"> | </span>
                                <span className="font-semibold text-warning">Balance: {fmtMoney(r.amount - r.amount_paid)}</span>
                            </div>
                        ))}
                    </div>
                    <div className="flex items-center justify-between mt-3 pt-3 border-t border-warning/30">
                        <span className="text-xs text-warning-content font-semibold">Total Outstanding Balance:</span>
                        <span className="text-sm font-bold text-warning">{fmtMoney(cashAdvance.total_outstanding)}</span>
                    </div>
                    {cashAdvance.existing_payments ? (
                        <div className="mt-3 pt-3 border-t border-warning/30">
                            <div className="flex items-center gap-2 text-xs text-success">
                                <Icon name="tabler--circle-check" className="size-4" />
                                <span className="font-semibold">Payment already processed for this cutoff</span>
                            </div>
                            <p className="text-xs text-warning-content mt-1 mb-0">Cash advance payment has already been deducted for this payroll period.</p>
                        </div>
                    ) : (
                        <p className="text-xs text-warning-content mt-2 mb-0">50% of net pay will be automatically deducted for cash advance repayment.</p>
                    )}
                </div>
            )}
        </>
    );
}

function Row({ label, value, tone = '' }) {
    return (
        <div className="flex justify-between text-xs mb-3">
            <span className="text-base-content/60">{label}</span>
            <span className={`font-semibold ${tone}`}>{value}</span>
        </div>
    );
}

function AllowanceBenefitList({ employee, total }) {
    const allowances = employee.active_allowances || [];
    const benefits = employee.active_benefits || [];
    const hasAny = allowances.length > 0 || benefits.length > 0;
    return (
        <div className="bg-base-200 border border-base-300 rounded-lg p-4">
            {hasAny ? (
                <>
                    <div className="grid gap-3 mb-4">
                        {allowances.map((a, i) => (
                            <div key={`a-${i}`} className="flex items-center justify-between bg-base-100 border-s-4 border-primary rounded-lg p-3">
                                <div>
                                    <div className="font-semibold text-xs text-base-content">{a.name}</div>
                                    <div className="text-base-content/60 text-xs">{a.type}</div>
                                </div>
                                <div className="font-bold text-success text-sm">{fmtMoney(Number(a.amount || 0) / 2)}</div>
                            </div>
                        ))}
                        {benefits.map((b, i) => (
                            <div key={`b-${i}`} className="flex items-center justify-between bg-base-100 border-s-4 border-primary rounded-lg p-3">
                                <div>
                                    <div className="font-semibold text-xs text-base-content">{b.name}</div>
                                    <div className="text-base-content/60 text-xs">{b.type}</div>
                                </div>
                                <div className="font-bold text-sm">{fmtMoney(Number(b.amount || 0) / 2)}</div>
                            </div>
                        ))}
                    </div>
                    <div className="flex items-center justify-between">
                        <span className="font-semibold text-sm text-base-content">Total Allowances & Benefits (per cutoff):</span>
                        <span className="font-bold text-success">{fmtMoney(total)}</span>
                    </div>
                </>
            ) : (
                <div className="text-center text-xs text-base-content/60 p-4">No allowances or benefits configured for this employee.</div>
            )}
        </div>
    );
}

function Label({ children }) {
    return <label className="label label-text font-semibold text-sm text-base-content mb-1">{children}</label>;
}

export default function ManualPayrollEmployeeForm({
    payrollPeriod,
    employee,
    payrollInput,
    dailyRate,
    computedDaysFromAttendance,
    approvedRequests,
    weekendsWorked,
    overtimeHours,
    holidayDays,
    isEdit,
    isSecondHalfOfMonth,
    cashAdvance,
    totalAllowancesAndBenefits,
}) {
    const { auth } = usePage().props;

    const [preview, setPreview] = useState(null);
    const [previewError, setPreviewError] = useState('');
    const [previewing, setPreviewing] = useState(false);

    const { data, setData, post, errors, processing } = useForm({
        payroll_period_id: payrollPeriod.id,
        employee_id: employee.id,
        daily_rate: payrollInput?.daily_rate ?? dailyRate,
        rate_type: 'daily',
        days_worked: payrollInput?.days_worked ?? (computedDaysFromAttendance > 0 ? computedDaysFromAttendance : ''),
        weekends_worked: payrollInput?.weekends_worked ?? (weekendsWorked || 0),
        overtime_hours: payrollInput?.overtime_hours ?? (overtimeHours || 0),
        late_hours: payrollInput?.late_hours ?? 0,
        holiday_days: payrollInput?.holiday_days ?? (holidayDays || 0),
        night_differential_hours: payrollInput?.night_differential_hours ?? 0,
        allowances: payrollInput?.allowances ?? totalAllowancesAndBenefits,
        deductions: payrollInput?.deductions ?? 0,
        deductions_remarks: payrollInput?.deductions_remarks ?? '',
        reimbursements: payrollInput?.reimbursements ?? 0,
        reimbursements_remarks: payrollInput?.reimbursements_remarks ?? '',
    });

    const setNum = (field) => (e) => {
        setData(field, e.target.value);
    };

    const submit = (e) => {
        e.preventDefault();
        post('/manual-payroll-attendance/save', {
            preserveScroll: true,
        });
    };

    const runPreview = async () => {
        setPreviewing(true);
        setPreviewError('');
        try {
            const res = await fetch('/manual-payroll-attendance/preview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                body: JSON.stringify(data),
            });
            const json = await res.json();
            if (json.success) {
                setPreview(json.preview);
            } else {
                setPreviewError(json.message || 'Preview failed.');
            }
        } catch (err) {
            setPreviewError('Error previewing payroll: ' + err.message);
        } finally {
            setPreviewing(false);
        }
    };

    const inputCls = (hasErr) => `input input-bordered w-full text-sm ${hasErr ? 'input-error border-error' : ''}`;
    const periodDates = useMemo(
        () => `${payrollPeriod.cutoff_start ? fmtDate(payrollPeriod.cutoff_start, { month: 'short', day: '2-digit' }) : 'N/A'} - ${fmtDate(payrollPeriod.cutoff_end)}`,
        [payrollPeriod]
    );

    return (
        <AppLayout title={`Encode Attendance - ${employee.first_name || 'Employee'} ${employee.last_name || ''}`}>
            <Head title="Encode Attendance" />
            <div className="p-2 sm:p-4">
                <Link href={`/manual-payroll-attendance/period/${payrollPeriod.id}`} className="inline-flex items-center text-sm text-base-content/60 mb-4 gap-3 no-underline hover:text-primary">
                    <Icon name="tabler--arrow-left" className="size-4" /> Back to Period
                </Link>

                <div className="flex items-center justify-between flex-wrap gap-3 mb-4">
                    <div>
                        <span className="badge badge-soft badge-info mb-2">
                            <Icon name="tabler--user-edit" className="size-3.5" /> {isEdit ? 'Edit' : 'Encode'} Attendance
                        </span>
                        <h2 className="text-lg font-bold text-base-content mt-2 mb-1">{employee.first_name || 'Employee'} {employee.last_name || ''}</h2>
                        <p className="text-base-content/60 m-0">
                            {employee.employee_id || 'N/A'} | {employee.position || 'N/A'} | {employee.department || 'N/A'} | Period: {periodDates}
                        </p>
                    </div>
                </div>

                <form onSubmit={submit} className="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                    <div className="card bg-base-100 border border-base-300 overflow-hidden p-0">
                        <div className="px-6 py-4 border-b border-base-300">
                            <h3 className="text-sm font-bold text-base-content m-0">Attendance Details</h3>
                            <p className="text-sm text-base-content/60 m-0">Enter attendance totals for the payroll period</p>
                        </div>

                        <div className="p-4">
                            <ApprovedRequests approvedRequests={approvedRequests} />
                            <CashAdvanceBox cashAdvance={cashAdvance || { fully_paid: [], outstanding: [], total_outstanding: 0, existing_payments: false }} />

                            <div className="mb-4">
                                <Label>Rate Type</Label>
                                <div className="text-sm text-base-content bg-base-200 border border-base-300 rounded-lg p-3">
                                    <span className="font-semibold">Daily Rate</span>
                                    <span className="text-base-content/60"> (computed using BSM X 12 / 52 / 40 X 8)</span>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <Label>Daily Rate</Label>
                                    <input type="number" step="0.01" min="0" required value={data.daily_rate} onChange={setNum('daily_rate')} className={inputCls(errors.daily_rate)} />
                                    <p className="text-xs text-base-content/60 mt-1">Based on basic salary ({fmtMoney(employee.basic_salary)})</p>
                                    {errors.daily_rate && <p className="text-error text-xs mt-1">{errors.daily_rate}</p>}
                                </div>
                                <div>
                                    <Label>Days Worked</Label>
                                    <input
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        max="31"
                                        value={data.days_worked}
                                        onChange={setNum('days_worked')}
                                        className={inputCls(errors.days_worked)}
                                        placeholder={computedDaysFromAttendance ? `Leave blank to use ${fmtNum(computedDaysFromAttendance, 2)} days from attendance` : 'Enter days worked'}
                                    />
                                    <p className="text-xs text-base-content/60 mt-1">
                                        {computedDaysFromAttendance ? `Leave blank to use ${fmtNum(computedDaysFromAttendance, 2)} days from attendance records, or enter a custom value.` : 'Enter days worked for the period.'}
                                    </p>
                                    {errors.days_worked && <p className="text-error text-xs mt-1">{errors.days_worked}</p>}
                                </div>
                            </div>

                            <div className="mb-4">
                                <Label>Weekends Worked</Label>
                                <input type="number" step="0.5" min="0" value={data.weekends_worked} onChange={setNum('weekends_worked')} className={inputCls(errors.weekends_worked)} />
                                <p className="text-xs text-base-content/60 mt-1">Number of weekend days worked (paid at 30% premium of daily rate)</p>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <Label>Overtime Hours</Label>
                                    <input type="number" step="0.5" min="0" value={data.overtime_hours} onChange={setNum('overtime_hours')} className={inputCls(errors.overtime_hours)} />
                                </div>
                                <div>
                                    <Label>Late Hours</Label>
                                    <input type="number" step="0.5" min="0" value={data.late_hours} onChange={setNum('late_hours')} className={inputCls(errors.late_hours)} />
                                </div>
                            </div>

                            <div className="mb-4">
                                <Label>Holiday Days Worked</Label>
                                <input type="number" step="0.5" min="0" value={data.holiday_days} onChange={setNum('holiday_days')} className={inputCls(errors.holiday_days)} />
                                <p className="text-xs text-base-content/60 mt-1">Number of regular holidays worked (paid at 200% of daily rate)</p>
                            </div>

                            <div className="mb-4">
                                <Label>Night Differential Hours</Label>
                                <input type="number" step="0.5" min="0" value={data.night_differential_hours} onChange={setNum('night_differential_hours')} className={inputCls(errors.night_differential_hours)} />
                                <p className="text-xs text-base-content/60 mt-1">Hours worked during night shift (paid at 10% premium of hourly rate)</p>
                            </div>

                            <div className="mb-4">
                                <Label>Allowances & Benefits</Label>
                                <AllowanceBenefitList employee={employee} total={totalAllowancesAndBenefits} />
                            </div>

                            <div className="mb-4">
                                <Label>Deductions</Label>
                                <input type="number" step="0.01" min="0" value={data.deductions} onChange={setNum('deductions')} className={inputCls(errors.deductions)} />
                                <input type="text" value={data.deductions_remarks} onChange={(e) => setData('deductions_remarks', e.target.value)} placeholder="Remarks (optional)" className="input input-bordered w-full text-sm mt-2" />
                            </div>

                            <div className="mb-4">
                                <Label>Reimbursements</Label>
                                <input type="number" step="0.01" min="0" value={data.reimbursements} onChange={setNum('reimbursements')} className={inputCls(errors.reimbursements)} />
                                <input type="text" value={data.reimbursements_remarks} onChange={(e) => setData('reimbursements_remarks', e.target.value)} placeholder="Remarks (optional)" className="input input-bordered w-full text-sm mt-2" />
                                <p className="text-xs text-base-content/60 mt-1">Expense reimbursements to be added to net pay</p>
                            </div>

                            <div className="flex gap-3">
                                <button type="submit" disabled={processing} className="btn btn-primary">
                                    <Icon name="tabler--floppy-disk" className="size-4" /> {isEdit ? 'Update' : 'Save'} Attendance
                                </button>
                                <button type="button" onClick={runPreview} disabled={previewing} className="btn btn-soft btn-neutral">
                                    <Icon name="tabler--calculator" className="size-4" /> Preview
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="card bg-base-100 border border-base-300 overflow-hidden p-0">
                        <div className="bg-base-200 px-6 py-4 border-b border-base-300">
                            <h3 className="flex items-center gap-3 text-sm font-bold text-base-content m-0">
                                <Icon name="tabler--receipt-2" className="size-5 text-base-content/60" /> Payroll Preview
                            </h3>
                        </div>

                        <div className="p-4">
                            {previewError && (
                                <div className="alert alert-error mb-4">
                                    <Icon name="tabler--alert-circle" className="size-5" />
                                    <span>{previewError}</span>
                                </div>
                            )}

                            {!preview ? (
                                <div className="text-center text-base-content/60 p-8">
                                    <Icon name="tabler--calculator" className="size-8 mx-auto mb-3 text-base-content/30" />
                                    <p className="text-sm m-0">Click "Preview" to see payroll computation</p>
                                </div>
                            ) : (
                                <div>
                                    <div className="mb-4">
                                        <Row label="Basic Salary:" value={fmtMoney(preview.basic_salary)} />
                                        <Row label="Weekend Rate:" value={`+${fmtMoney(preview.weekend_pay)}`} tone="text-success" />
                                        <Row label="Overtime Rate:" value={`+${fmtMoney(preview.overtime_pay)}`} tone="text-success" />
                                        <Row label="Holiday Rate:" value={`+${fmtMoney(preview.holiday_pay)}`} tone="text-success" />
                                        <Row label="Night Differential:" value={`+${fmtMoney(preview.night_differential)}`} tone="text-success" />
                                        <Row label="Allowances:" value={`+${fmtMoney(preview.allowances)}`} tone="text-success" />
                                        <Row label="Late Deduction:" value={`-${fmtMoney(preview.late_deduction)}`} tone="text-error" />
                                    </div>
                                    <div className="bg-base-200 rounded-lg p-4 mb-4">
                                        <div className="flex justify-between font-bold text-sm text-base-content">
                                            <span>Gross Pay:</span>
                                            <span>{fmtMoney(preview.gross_pay)}</span>
                                        </div>
                                    </div>

                                    {isSecondHalfOfMonth && (Number(preview.first_cutoff_gross_pay) > 0 || Number(preview.second_cutoff_gross_pay) > 0) && (
                                        <div className="bg-info/10 border border-base-300 rounded-lg p-4 mb-4">
                                            <div className="font-semibold text-xs text-info mb-3">Monthly Cutoff Breakdown</div>
                                            <Row label="1st Cutoff Pay:" value={fmtMoney(preview.first_cutoff_gross_pay)} />
                                            <Row label="2nd Cutoff Pay:" value={fmtMoney(preview.second_cutoff_gross_pay)} />
                                            <div className="flex justify-between font-bold text-sm text-info">
                                                <span>Total Monthly Gross:</span>
                                                <span>{fmtMoney(preview.total_monthly_gross_pay)}</span>
                                            </div>
                                        </div>
                                    )}

                                    <div className="mb-4">
                                        <Row label="SSS Contribution:" value={`-${fmtMoney(preview.sss_contribution)}`} tone="text-error" />
                                        <Row label="PhilHealth Contribution:" value={`-${fmtMoney(preview.philhealth_contribution)}`} tone="text-error" />
                                        <Row label="Pag-IBIG Contribution:" value={`-${fmtMoney(preview.pagibig_contribution)}`} tone="text-error" />
                                        {isSecondHalfOfMonth && (
                                            <Row label="Withholding Tax:" value={`-${fmtMoney(preview.withholding_tax)}`} tone="text-error" />
                                        )}
                                        {isEdit && (
                                            <Row label="Manual Deductions:" value={`-${fmtMoney(preview.manual_deductions)}`} tone="text-error" />
                                        )}
                                        {Number(preview.cash_advance_deduction) > 0 && (
                                            <Row label="Cash Advance Deduction:" value={`-${fmtMoney(preview.cash_advance_deduction)}`} tone="text-error" />
                                        )}
                                        <div className="flex justify-between font-semibold text-xs text-base-content">
                                            <span>Total Deductions:</span>
                                            <span className="text-error">-{fmtMoney(preview.deductions)}</span>
                                        </div>
                                    </div>

                                    {Number(preview.cash_advance_deduction) > 0 && (
                                        <div className="bg-warning/10 border border-warning rounded-lg p-4 mb-4">
                                            <div className="flex items-center gap-2 text-xs text-warning mb-2">
                                                <Icon name="tabler--alert-triangle" className="size-4" />
                                                <span className="font-semibold">Cash Advance Payment</span>
                                            </div>
                                            <div className="text-xs text-warning-content">{fmtMoney(preview.cash_advance_deduction)} deducted from this payroll (50% of net pay)</div>
                                        </div>
                                    )}

                                    <div className="mb-4">
                                        <Row label="Reimbursements:" value={`+${fmtMoney(preview.reimbursements)}`} tone="text-success" />
                                    </div>

                                    {isSecondHalfOfMonth && (Number(preview.first_cutoff_net_pay) > 0 || Number(preview.second_cutoff_net_pay) > 0) && (
                                        <div className="bg-success/10 border border-base-300 rounded-lg p-4 mb-4">
                                            <div className="font-semibold text-xs mb-3">Monthly Net Pay Breakdown</div>
                                            <Row label="1st Cutoff Net:" value={fmtMoney(preview.first_cutoff_net_pay)} />
                                            <Row label="2nd Cutoff Net:" value={fmtMoney(preview.second_cutoff_net_pay)} />
                                            <div className="flex justify-between font-bold text-sm">
                                                <span>Total Monthly Net:</span>
                                                <span>{fmtMoney(preview.total_monthly_net_pay)}</span>
                                            </div>
                                        </div>
                                    )}

                                    <div className="rounded-lg p-4 bg-base-100 border border-base-300">
                                        <div className="flex justify-between font-bold">
                                            <span>Net Pay:</span>
                                            <span>{fmtMoney(Number(preview.net_pay) + Number(data.reimbursements || 0))}</span>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
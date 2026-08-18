import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';

const fmt = (n) => '₱' + parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtNum = (n, digits = 2) => parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });

const fmtDate = (value, opts = { month: 'short', day: '2-digit', year: 'numeric' }) => {
    if (!value) return '—';
    return new Date(value.slice ? value.slice(0, 10) + 'T00:00:00' : value).toLocaleDateString('en-US', opts);
};

const EMPLOYMENT_BADGE = {
    Regular: 'badge-soft badge-success',
    Probationary: 'badge-soft badge-warning',
    Contractual: 'badge-soft badge-info',
    'Part-time': 'badge-soft badge-neutral',
};

function StatCard({ tone, icon, title, value, sub }) {
    const ribbon = tone === 'green' ? 'payroll-stat-green' : tone === 'red' ? 'payroll-stat-red' : 'payroll-stat-net';
    return (
        <div className={`payroll-stat-card ${ribbon}`}>
            <div className="payroll-stat-card__body">
                <div className="payroll-stat-card__icon">
                    <svg height="32" width="32" stroke="currentColor" strokeWidth="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        {icon}
                    </svg>
                </div>
                <p className="payroll-stat-card__title">{title}</p>
                <p className="payroll-stat-card__paragraph">{value}</p>
                <div className="text-subtle">{sub}</div>
            </div>
            <div className="payroll-stat-card__ribbon">
                <div className="payroll-stat-card__ribbon-label">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1813 1441">
                        <path d="M0 720.5 710.6 9.9v417.8L417.8 720.5l292.8 292.8v417.8zm1813 0-719.7 719.8v-417.9l301.9-301.9-301.9-301.9V.8z" fillRule="evenodd" fill="var(--ribbon-color)"/>
                        <path d="M1266.4 674.9h-209.8l-59 451H806.3l-59-451H546.6L697 524.6h419z" fillRule="evenodd" fill="var(--ribbon-color)"/>
                    </svg>
                </div>
            </div>
        </div>
    );
}

function EarningsRow({ label, sub, value, tone = 'text-success', prefix = '+' }) {
    return (
        <div className="flex justify-between items-start py-2.5 border-b border-base-200">
            <div>
                <div className="text-subtle">{label}</div>
                <div className="text-xs text-faint">{sub}</div>
            </div>
            <span className={`font-semibold ${tone} ml-4 whitespace-nowrap`}>{prefix}{fmt(value)}</span>
        </div>
    );
}

export default function PayrollShow({ employee, payrollData, selectedPeriod, payslipUrl }) {
    const p = payrollData || {};
    const a = p.attendance_data || {};
    const isSecondHalf = selectedPeriod?.is_second_half;
    const hasGross = (p.gross_pay || 0) > 0;

    const govIds = [
        { label: 'SSS Number', value: employee.sss_number, icon: 'tabler--shield-check', color: 'text-success', bg: 'bg-success/10' },
        { label: 'PhilHealth Number', value: employee.philhealth_number, icon: 'tabler--heart', color: 'text-info', bg: 'bg-info/10' },
        { label: 'Pag-IBIG Number', value: employee.pagibig_number, icon: 'tabler--home', color: 'text-warning', bg: 'bg-warning/10' },
        { label: 'TIN Number', value: employee.tin_number, icon: 'tabler--file-text', color: 'text-secondary', bg: 'bg-secondary/10' },
    ];

    const totalGovDeductions = (p.sss_contribution || 0) + (p.philhealth_contribution || 0) + (p.pagibig_contribution || 0) + (p.late_deduction || 0) + (p.manual_deductions || 0);

    const totalEarnings = (p.base_pay || 0) + (p.overtime_pay || 0) + (p.night_differential_pay || 0) + (p.holiday_pay || 0) + (p.benefits || 0) + (p.allowances || 0);

    return (
        <AppLayout title={`Payroll Details - ${employee.full_name}`}>
            <Head title="Payroll Details" />
            <div className="p-2 sm:p-4">
                <style>{`
                    .payroll-stat-card { width: min(300px, 100%); margin: auto; background-color: var(--color-base-200); text-align: center; border-top-left-radius: 4rem; border: 2px solid var(--color-base-200); position: relative; --ribbon-color: #393e7f; --ribbon-dark-color: #191c39; }
                    .payroll-stat-card.payroll-stat-green { --ribbon-color: var(--color-success); --ribbon-dark-color: var(--color-success-content); }
                    .payroll-stat-card.payroll-stat-red { --ribbon-color: var(--color-error); --ribbon-dark-color: var(--color-error-content); }
                    .payroll-stat-card.payroll-stat-net { --ribbon-color: var(--color-success); --ribbon-dark-color: var(--color-success-content); }
                    .payroll-stat-card::before { content: ""; position: absolute; height: 30px; width: 120px; background-color: var(--ribbon-color); top: 32px; right: -2.5px; -webkit-clip-path: polygon(10% 0, 100% 0, 100% 100%, 0 100%); clip-path: polygon(10% 0, 100% 0, 100% 100%, 0 100%); }
                    .payroll-stat-card__body { padding: 2rem 1.5rem; max-width: 25ch; margin: auto; }
                    .payroll-stat-card__title { font-weight: 800; color: var(--color-base-content); font-size: 1.25rem; margin-block: 1.5rem 0.75rem; }
                    .payroll-stat-card__paragraph { color: var(--color-base-content); font-size: 1.5rem; }
                    .payroll-stat-card__ribbon { margin-top: 1.5rem; display: grid; place-items: center; height: 50px; background-color: var(--ribbon-color); position: relative; width: 110%; left: -5%; top: 10px; border-radius: 0 0 2rem 2rem; }
                    .payroll-stat-card__ribbon::after, .payroll-stat-card__ribbon::before { content: ""; position: absolute; width: 20px; aspect-ratio: 1/1; bottom: 100%; z-index: -2; background-color: var(--ribbon-dark-color); }
                    .payroll-stat-card__ribbon::before { left: 0; transform-origin: left bottom; transform: rotate(45deg); }
                    .payroll-stat-card__ribbon::after { right: 0; transform-origin: right bottom; transform: rotate(-45deg); }
                    .payroll-stat-card__ribbon-label { display: block; width: 84px; aspect-ratio: 1/1; background-color: var(--color-base-100); position: relative; transform: translateY(-50%); border-radius: 50%; border: 8px solid var(--ribbon-color); display: grid; place-items: center; padding: 12px; }
                    .payroll-stat-card__ribbon-label svg { width: 100%; height: 100%; }
                    .payroll-stat-card__ribbon-label::before, .payroll-stat-card__ribbon-label::after { content: ""; position: absolute; width: 25px; height: 25px; bottom: 50%; }
                    .payroll-stat-card__ribbon-label::before { right: calc(100% + 4px); border-bottom-right-radius: 20px; box-shadow: 5px 5px 0 var(--ribbon-color); }
                    .payroll-stat-card__ribbon-label::after { left: calc(100% + 4px); border-bottom-left-radius: 20px; box-shadow: -5px 5px 0 var(--ribbon-color); }
                `}</style>

                <div className="flex justify-between items-center flex-wrap gap-3 mb-5">
                    <Link href="/payroll" className="back-link text-subtle no-underline text-sm hover:text-primary flex items-center gap-1">
                        <Icon name="tabler--arrow-left" className="size-4" /> Back to Payroll List
                    </Link>
                    {hasGross && payslipUrl && (
                        <a href={payslipUrl} className="btn btn-soft btn-info btn-sm">
                            <Icon name="tabler--file-download" className="size-4" /> Download Payslip
                        </a>
                    )}
                </div>

                <div className="card bg-base-100 shadow-sm p-5 flex items-center gap-5 flex-wrap mb-5">
                    <div className="w-16 h-16 rounded-full bg-gradient-to-br from-error to-error/80 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                        {(employee.full_name || '?').charAt(0).toUpperCase()}
                    </div>
                    <div className="flex-1">
                        <h2 className="text-xl font-bold text-base-content m-0 mb-1">{employee.full_name}</h2>
                        <p className="text-subtle m-0">{employee.position} — {employee.department}</p>
                        <div className="flex flex-wrap gap-3 mt-1 text-xs text-subtle">
                            <span><Icon name="tabler--id-badge" className="size-3.5 inline" /> {employee.employee_id}</span>
                            <span><Icon name="tabler--calendar" className="size-3.5 inline" /> {fmtDate(employee.date_hired)}</span>
                            <span><Icon name="tabler--moneybag" className="size-3.5 inline" /> {employee.salary_type} Salary</span>
                        </div>
                    </div>
                    <span className={`badge ${EMPLOYMENT_BADGE[employee.employment_status] || 'badge-soft'}`}>{employee.employment_status}</span>
                </div>

                <div className="card bg-base-100 shadow-sm p-5 mb-5">
                    <h2 className="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
                        <Icon name="tabler--id" className="size-4 text-error" /> Government IDs
                    </h2>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        {govIds.map((g) => (
                            <div key={g.label} className="card bg-base-100 shadow-sm p-4 text-center">
                                <div className={`w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 ${g.color} ${g.bg}`}>
                                    <Icon name={g.icon} className="size-5" />
                                </div>
                                <div className="text-xs text-faint uppercase tracking-widest font-medium mb-1">{g.label}</div>
                                <div className="font-bold font-mono text-base-content text-xs break-all">{g.value || '—'}</div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                    <StatCard
                        tone="green"
                        title="Gross Pay"
                        value={fmt(p.gross_pay)}
                        sub="For this cutoff"
                        icon={<path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" strokeLinejoin="round" strokeLinecap="round" />}
                    />
                    <StatCard
                        tone="red"
                        title="Total Deductions"
                        value={`-${fmt(p.total_deductions)}`}
                        sub="Gov't & Manual Deductions"
                        icon={<path d="M18 12H6M6 12l-3 3m3-3l-3-3M18 12l3 3m-3-3l3-3" strokeLinejoin="round" strokeLinecap="round" />}
                    />
                    <StatCard
                        tone="net"
                        title="Net Pay"
                        value={fmt(p.net_pay)}
                        sub="Take-home for this cutoff"
                        icon={<path d="M21 12V7a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 7v5m18 0v5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 17v-5m18 0h-18" strokeLinejoin="round" strokeLinecap="round" />}
                    />
                </div>

                {selectedPeriod && isSecondHalf && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                        <div className="card bg-base-100 shadow-sm p-5 border-l-4 border-info bg-info/10">
                            <div className="text-xs font-bold text-info uppercase tracking-wider mb-3">Monthly Gross Breakdown</div>
                            <div className="flex flex-col gap-2 text-sm">
                                <div className="flex justify-between font-bold text-info"><span>1st Cutoff Gross:</span><span>{fmt(p.first_cutoff_gross_pay)}</span></div>
                                <div className="flex justify-between font-bold text-info"><span>2nd Cutoff Gross:</span><span>{fmt(p.gross_pay)}</span></div>
                                <div className="flex justify-between pt-2 border-t border-info/20 font-bold text-info"><span>Total Monthly Gross:</span><span>{fmt(p.total_monthly_gross_pay)}</span></div>
                            </div>
                        </div>
                        <div className="card bg-base-100 shadow-sm p-5 border-l-4 border-error bg-error/10">
                            <div className="text-xs font-bold text-error uppercase tracking-wider mb-3">Monthly Contributions</div>
                            <div className="flex flex-col gap-2 text-sm">
                                <div className="flex justify-between font-bold text-error"><span>1st Cutoff Gov't:</span><span>{fmt(p.first_cutoff_contributions)}</span></div>
                                <div className="flex justify-between font-bold text-error"><span>2nd Cutoff Gov't:</span><span>{fmt(p.current_cutoff_contributions)}</span></div>
                                <div className="flex justify-between pt-2 border-t border-error/20 font-bold text-error"><span>Total Monthly Gov't:</span><span>{fmt((p.first_cutoff_contributions || 0) + (p.current_cutoff_contributions || 0))}</span></div>
                            </div>
                        </div>
                        <div className="card bg-base-100 shadow-sm p-5 border-l-4 border-success bg-success/10">
                            <div className="text-xs font-bold text-success uppercase tracking-wider mb-3">Monthly Net Pay Breakdown</div>
                            <div className="flex flex-col gap-2 text-sm">
                                <div className="flex justify-between font-bold text-success"><span>1st Cutoff Net:</span><span>{fmt(p.first_cutoff_net_pay)}</span></div>
                                <div className="flex justify-between font-bold text-success"><span>2nd Cutoff Net:</span><span>{fmt(p.net_pay)}</span></div>
                                <div className="flex justify-between pt-2 border-t border-success/20 font-bold text-success"><span>Total Monthly Net:</span><span>{fmt(p.total_monthly_net_pay)}</span></div>
                            </div>
                        </div>
                    </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div className="card bg-base-100 shadow-sm p-5">
                        <h2 className="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
                            <span className="w-7 h-7 rounded-md bg-success/10 flex items-center justify-center text-success text-xs flex-shrink-0">
                                <Icon name="tabler--clock" className="size-4" />
                            </span>
                            Attendance-Based Earnings
                        </h2>
                        <div className="flex flex-col text-sm">
                            <EarningsRow
                                label="Base Pay"
                                sub={`${fmtNum(a.regular_hours)} hrs × ${fmt(p.hourly_rate)}/hr`}
                                value={p.base_pay}
                                tone="text-success"
                                prefix=""
                            />
                            <EarningsRow label="Weekend Pay" sub={`${fmtNum(a.weekends_worked)} weekend days × 0.30 × ${fmt(p.daily_rate)}/day`} value={p.weekend_pay} />
                            <EarningsRow label="Overtime Pay" sub={`${fmtNum(a.overtime_hours)} OT hrs × 1.25 × ${fmt(p.hourly_rate)}/hr`} value={p.overtime_pay} />
                            <EarningsRow label="Night Differential" sub={`${fmtNum(a.night_diff_hours)} ND hrs × 1.10 × ${fmt(p.hourly_rate)}/hr`} value={p.night_differential_pay} />
                            <EarningsRow label="Holiday Pay" sub={`${fmtNum(a.holiday_days)} holiday days × 2 × ${fmt(p.daily_rate)}/day`} value={p.holiday_pay} />
                            <EarningsRow label="Benefits" sub="Total active benefits" value={p.benefits} />
                            <EarningsRow label="Allowances" sub="Total active allowances" value={p.allowances} />
                        </div>
                        <div className="flex justify-between items-center mt-3 pt-3 border-t-2 border-base-300 font-bold text-sm">
                            <span className="text-base-content">Total Earnings</span>
                            <span className="text-success">{fmt(totalEarnings)}</span>
                        </div>
                    </div>

                    <div className="card bg-base-100 shadow-sm p-5">
                        <h2 className="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
                            <span className="w-7 h-7 rounded-md bg-info/10 flex items-center justify-center text-info text-xs flex-shrink-0">
                                <Icon name="tabler--building-bank" className="size-4" />
                            </span>
                            Government Contributions & Deductions
                        </h2>
                        <div className="flex flex-col text-sm">
                            <EarningsRow label="SSS Contribution" sub="4.5% of gross pay (capped at ₱900)" value={p.sss_contribution} tone="text-error" prefix="-" />
                            <EarningsRow label="PhilHealth Contribution" sub="2.25% of gross pay (capped at ₱1,500)" value={p.philhealth_contribution} tone="text-error" prefix="-" />
                            <EarningsRow label="Pag-IBIG Contribution" sub="2% of gross pay (capped at ₱100)" value={p.pagibig_contribution} tone="text-error" prefix="-" />
                            <EarningsRow label="Late Deduction" sub={`${fmtNum(a.late_hours)} late hrs × ${fmt(p.hourly_rate)}/hr`} value={p.late_deduction} tone="text-error" prefix="-" />
                            <EarningsRow label="Manual Deductions" sub="Deductions from manual payroll attendance" value={p.manual_deductions} tone="text-error" prefix="-" />
                        </div>
                        <div className="flex justify-between items-center mt-3 pt-3 border-t-2 border-base-300 font-bold text-sm">
                            <span className="text-base-content">Net Contributions & Deductions</span>
                            <span className="text-error">-{fmt(totalGovDeductions)}</span>
                        </div>
                    </div>

                    <div className="card bg-base-100 shadow-sm p-5">
                        <h2 className="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
                            <span className="w-7 h-7 rounded-md bg-error/10 flex items-center justify-center text-error text-xs flex-shrink-0">
                                <Icon name="tabler--file-text" className="size-4" />
                            </span>
                            Tax Information
                        </h2>
                        <div className="flex flex-col text-sm">
                            <div className="flex justify-between items-start py-2.5 border-b border-base-200">
                                <div>
                                    <div className="text-subtle">Taxable Income</div>
                                    <div className="text-xs text-faint">After government contributions</div>
                                </div>
                                <span className="font-semibold text-base-content ml-4">{fmt(p.taxable_income)}</span>
                            </div>
                            <div className="flex justify-between items-start py-2.5">
                                <div>
                                    <div className="text-subtle">Withholding Tax</div>
                                    <div className="text-xs text-faint">Based on Philippine tax brackets</div>
                                </div>
                                <span className="font-semibold text-error ml-4">-{fmt(p.withholding_tax)}</span>
                            </div>
                        </div>
                        <div className="mt-4 p-4 bg-base-300 rounded-xl text-xs text-subtle leading-relaxed">
                            <strong className="text-muted">Note:</strong> Allowances are included in gross pay but deducted from taxable income for withholding tax computation.<br />
                            <strong className="text-muted">Tax Bracket Reference:</strong><br />
                            • ₱0 – ₱20,832: 0%<br />
                            • ₱20,833 – ₱33,333: 20%<br />
                            • ₱33,334 – ₱66,667: 25%<br />
                            • ₱66,668 – ₱166,667: 30%<br />
                            • ₱166,668 – ₱666,667: 32%<br />
                            • Above ₱666,667: 35%
                        </div>
                    </div>

                    <div className="card bg-base-300 shadow-sm p-5 md:col-span-3">
                        <h2 className="text-sm font-bold text-base-content mb-4 flex items-center gap-2">
                            <Icon name="tabler--receipt" className="size-4 text-error" /> Pay Summary
                        </h2>
                        <div className="flex flex-col text-sm">
                            <div className="flex justify-between items-center py-3 border-b border-base-200">
                                <span className="text-base-content">Gross Pay</span>
                                <span className="font-semibold text-base-content">{fmt(p.gross_pay)}</span>
                            </div>
                            <div className="flex justify-between items-center py-3 border-b border-base-200">
                                <span className="text-error">Less: Government Contributions & Deductions</span>
                                <span className="font-semibold text-error">-{fmt(totalGovDeductions)}</span>
                            </div>
                            <div className="flex justify-between items-center py-3 border-b-2 border-base-300">
                                <span className="text-error">Less: Withholding Tax</span>
                                <span className="font-semibold text-error">-{fmt(p.withholding_tax)}</span>
                            </div>
                            <div className="flex justify-between items-center py-4">
                                <span className="text-xl font-bold text-base-content">NET PAY</span>
                                <span className="text-3xl font-extrabold text-success">{fmt(p.net_pay)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
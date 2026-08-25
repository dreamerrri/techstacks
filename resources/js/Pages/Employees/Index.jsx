import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import StatusBadge from '../../components/StatusBadge';
import DataTable from '../../components/DataTable';
import ConfirmButton from '../../components/ConfirmButton';

const STATUS_META = {
    Regular: 'success',
    Probationary: 'warning',
    Contractual: 'info',
    'Part-time': 'neutral',
};

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
};

function SortableTh({ label, sortKey, filters }) {
    const active = filters.sort === sortKey;
    const direction = active && filters.direction === 'asc' ? 'desc' : 'asc';

    const handleSort = () => {
        router.get('/employees', { ...filters, sort: sortKey, direction }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    };

    return (
        <th>
            <button type="button" onClick={handleSort} className="flex items-center gap-1 normal-case font-medium hover:text-brand cursor-pointer">
                {label}
                {active ? (
                    <Icon name={filters.direction === 'asc' ? 'tabler--arrow-up' : 'tabler--arrow-down'} className="size-3.5" />
                ) : (
                    <Icon name="tabler--arrows-sort" className="size-3.5 opacity-40" />
                )}
            </button>
        </th>
    );
}

export default function EmployeesIndex({ employees, departments, filters, stats }) {
    const { auth } = usePage().props;
    const canManage = auth?.user?.role === 'admin' || auth?.user?.role === 'hr';

    const avatar = (emp, size = 'w-8 h-8', text = 'text-xs') => (
        <div className={`${size} rounded-full overflow-hidden flex-shrink-0`}>
            {emp.user?.photo_url ? (
                <img src={emp.user.photo_url} alt={emp.full_name} className="w-full h-full object-cover" />
            ) : (
                <div className={`${size} rounded-full bg-gradient-to-br from-brand to-brand/70 flex items-center justify-center text-brand-foreground ${text} font-bold`}>
                    {emp.full_name.charAt(0).toUpperCase()}
                </div>
            )}
        </div>
    );

    return (
        <AppLayout>
            <Head title="Manage Employees" />
            <div className="p-2 sm:p-4 space-y-6">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-1">
                    <div className="rounded-xl border border-edge bg-card p-4 text-center sm:p-5">
                        <div className="mx-auto mb-2 flex size-10 items-center justify-center rounded-lg text-primary bg-primary/10">
                            <Icon name="tabler--user" className="size-5" />
                        </div>
                        <div className="mb-0.5 text-xl font-bold sm:text-2xl">{stats.total}</div>
                        <div className="text-[11px] uppercase tracking-widest text-dim-foreground">Total Employees</div>
                    </div>
                    <div className="rounded-xl border border-edge bg-card p-4 text-center sm:p-5">
                        <div className="mx-auto mb-2 flex size-10 items-center justify-center rounded-lg text-success bg-success/10">
                            <Icon name="tabler--circle-check" className="size-5" />
                        </div>
                        <div className="mb-0.5 text-xl font-bold sm:text-2xl">{stats.regular}</div>
                        <div className="text-[11px] uppercase tracking-widest text-dim-foreground">Regular</div>
                    </div>
                    <div className="rounded-xl border border-edge bg-card p-4 text-center sm:p-5">
                        <div className="mx-auto mb-2 flex size-10 items-center justify-center rounded-lg text-warning bg-warning/10">
                            <Icon name="tabler--clock" className="size-5" />
                        </div>
                        <div className="mb-0.5 text-xl font-bold sm:text-2xl">{stats.probationary}</div>
                        <div className="text-[11px] uppercase tracking-widest text-dim-foreground">Probationary</div>
                    </div>
                    <Link href="/employees/archived" className="rounded-xl border border-edge bg-card p-4 text-center sm:p-5 hover:shadow-md transition-shadow cursor-pointer">
                        <div className="mx-auto mb-2 flex size-10 items-center justify-center rounded-lg bg-dim text-dim-foreground">
                            <Icon name="tabler--archive" className="size-5" />
                        </div>
                        <div className="mb-0.5 text-xl font-bold sm:text-2xl">{stats.archived}</div>
                        <div className="text-[11px] uppercase tracking-widest text-dim-foreground">Archived</div>
                    </Link>
                </div>

                <DataTable
                    title="Employee List"
                    icon="tabler--users"
                    tooltip="Manage all employee records in the system."
                    baseUrl="/employees"
                    search
                    searchPlaceholder="Search name or email..."
                    filters={[
                        {
                            name: 'department',
                            value: filters.department || '',
                            options: [
                                { value: '', label: 'All Departments' },
                                ...departments.map((dept) => ({ value: dept, label: dept })),
                            ],
                        },
                        {
                            name: 'status',
                            value: filters.status || '',
                            options: [
                                { value: '', label: 'All Status' },
                                { value: 'Regular', label: 'Regular' },
                                { value: 'Probationary', label: 'Probationary' },
                                { value: 'Contractual', label: 'Contractual' },
                                { value: 'Part-time', label: 'Part-time' },
                            ],
                        },
                    ]}
                    paginator={employees}
                    empty="No employees found."
                    actions={
                        <Link href="/employees/create" className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10">
                            <Icon name="tabler--plus" className="size-4" /> Add Employee
                        </Link>
                    }
                >
                    <div className="overflow-x-auto overflow-y-auto hidden md:block" style={{ maxHeight: '53vh' }}>
                        <table className="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <SortableTh label="Status" sortKey="employment_status" filters={filters} />
                                    <SortableTh label="Date Hired" sortKey="date_hired" filters={filters} />
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {employees.data.map((emp) => (
                                    <tr key={emp.id} className="row-hover">
                                        <td className="font-mono text-dim-foreground">{emp.employee_id}</td>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                {avatar(emp)}
                                                <Link href={`/employees/${emp.id}`} className="text-base-content no-underline font-semibold hover:text-brand">
                                                    {emp.full_name}
                                                </Link>
                                            </div>
                                        </td>
                                        <td className="text-dim-foreground">{emp.department}</td>
                                        <td className="text-dim-foreground">{emp.position}</td>
                                        <td>
                                            <StatusBadge type={STATUS_META[emp.employment_status] ?? 'neutral'}>
                                                {emp.employment_status}
                                            </StatusBadge>
                                        </td>
                                        <td className="text-dim-foreground">{fmtDate(emp.date_hired)}</td>
                                        <td>
                                            <div className="flex gap-2">
                                                <Link href={`/employees/${emp.id}`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">
                                                    <Icon name="tabler--eye" className="size-4" />
                                                </Link>
                                                <Link href={`/employees/${emp.id}/edit`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-warning/40 bg-warning/10 px-3 text-xs font-medium text-warning no-underline transition-colors hover:bg-warning/20">
                                                    <Icon name="tabler--pencil" className="size-4" />
                                                </Link>
                                                {canManage && (
                                                    <ConfirmButton
                                                        title="Archive Employee?"
                                                        text="This employee will be moved to the archive."
                                                        confirmText="Yes, archive"
                                                        url={`/employees/${emp.id}/archive`}
                                                        method="patch"
                                                        className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10"
                                                    >
                                                        <Icon name="tabler--archive" className="size-4" />
                                                    </ConfirmButton>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="md:hidden p-4 flex flex-col gap-3">
                        {employees.data.map((emp) => (
                            <div key={emp.id} className="card bg-base-100 border border-edge p-4">
                                <div className="flex justify-between items-start mb-2">
                                    <div className="flex items-center gap-3">
                                        {avatar(emp, 'w-10 h-10', 'text-sm')}
                                        <div>
                                            <Link href={`/employees/${emp.id}`} className="text-base-content no-underline font-semibold text-sm hover:text-brand">
                                                {emp.full_name}
                                            </Link>
                                            <div className="text-xs text-dim-foreground font-mono">{emp.employee_id}</div>
                                        </div>
                                    </div>
                                    <StatusBadge type={STATUS_META[emp.employment_status] ?? 'neutral'} className="whitespace-nowrap">
                                        {emp.employment_status}
                                    </StatusBadge>
                                </div>

                                <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-dim-foreground mt-2">
                                    <span><Icon name="tabler--building" className="size-3.5 inline" /> {emp.department}</span>
                                    <span><Icon name="tabler--briefcase" className="size-3.5 inline" /> {emp.position}</span>
                                    <span><Icon name="tabler--calendar" className="size-3.5 inline" /> {fmtDate(emp.date_hired)}</span>
                                </div>

                                <div className="flex gap-2 flex-wrap mt-3 pt-3 border-t border-edge">
                                    <Link href={`/employees/${emp.id}`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-edge bg-dim px-3 text-xs font-medium no-underline transition-colors hover:bg-dim/60">
                                        <Icon name="tabler--eye" className="size-4" /> View
                                    </Link>
                                    <Link href={`/employees/${emp.id}/edit`} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-warning/40 bg-warning/10 px-3 text-xs font-medium text-warning no-underline transition-colors hover:bg-warning/20">
                                        <Icon name="tabler--pencil" className="size-4" /> Edit
                                    </Link>
                                    {canManage && (
                                        <ConfirmButton
                                            title="Archive Employee?"
                                            text="This employee will be moved to the archive."
                                            confirmText="Yes, archive"
                                            url={`/employees/${emp.id}/archive`}
                                            method="patch"
                                            className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10"
                                        >
                                            <Icon name="tabler--archive" className="size-4" /> Archive
                                        </ConfirmButton>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </DataTable>
            </div>
        </AppLayout>
    );
}

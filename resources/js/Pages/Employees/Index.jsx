import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import StatusBadge from '../../Components/StatusBadge';
import DataTable from '../../Components/DataTable';
import ConfirmButton from '../../Components/ConfirmButton';

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
            <button type="button" onClick={handleSort} className="flex items-center gap-1 normal-case font-medium hover:text-primary cursor-pointer">
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
                <div className={`${size} rounded-full bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-primary-content ${text} font-bold`}>
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
                    <div className="card bg-base-100 border border-base-300 p-5 text-center">
                        <div className="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                            <Icon name="tabler--user" className="size-5" />
                        </div>
                        <div className="text-3xl font-bold text-base-content mb-1">{stats.total}</div>
                        <div className="text-xs text-base-content/80 uppercase tracking-widest font-medium">Total Employees</div>
                    </div>
                    <div className="card bg-base-100 border border-base-300 p-5 text-center">
                        <div className="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-success bg-success/10">
                            <Icon name="tabler--circle-check" className="size-5" />
                        </div>
                        <div className="text-3xl font-bold text-base-content mb-1">{stats.regular}</div>
                        <div className="text-xs text-base-content/80 uppercase tracking-widest font-medium">Regular</div>
                    </div>
                    <div className="card bg-base-100 border border-base-300 p-5 text-center">
                        <div className="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-warning bg-warning/10">
                            <Icon name="tabler--clock" className="size-5" />
                        </div>
                        <div className="text-3xl font-bold text-base-content mb-1">{stats.probationary}</div>
                        <div className="text-xs text-base-content/80 uppercase tracking-widest font-medium">Probationary</div>
                    </div>
                    <Link href="/employees/archived" className="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
                        <div className="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-base-content/60 bg-base-200">
                            <Icon name="tabler--archive" className="size-5" />
                        </div>
                        <div className="text-3xl font-bold text-base-content mb-1">{stats.archived}</div>
                        <div className="text-xs text-base-content/80 uppercase tracking-widest font-medium">Archived</div>
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
                        <Link href="/employees/create" className="btn btn-soft btn-error btn-sm">
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
                                        <td className="font-mono text-base-content/60">{emp.employee_id}</td>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                {avatar(emp)}
                                                <Link href={`/employees/${emp.id}`} className="text-base-content no-underline font-semibold hover:text-primary">
                                                    {emp.full_name}
                                                </Link>
                                            </div>
                                        </td>
                                        <td className="text-base-content/60">{emp.department}</td>
                                        <td className="text-base-content/60">{emp.position}</td>
                                        <td>
                                            <StatusBadge type={STATUS_META[emp.employment_status] ?? 'neutral'}>
                                                {emp.employment_status}
                                            </StatusBadge>
                                        </td>
                                        <td className="text-base-content/60">{fmtDate(emp.date_hired)}</td>
                                        <td>
                                            <div className="flex gap-2">
                                                <Link href={`/employees/${emp.id}`} className="btn btn-soft btn-info btn-sm">
                                                    <Icon name="tabler--eye" className="size-4" />
                                                </Link>
                                                <Link href={`/employees/${emp.id}/edit`} className="btn btn-soft btn-warning btn-sm">
                                                    <Icon name="tabler--pencil" className="size-4" />
                                                </Link>
                                                {canManage && (
                                                    <ConfirmButton
                                                        title="Archive Employee?"
                                                        text="This employee will be moved to the archive."
                                                        confirmText="Yes, archive"
                                                        url={`/employees/${emp.id}/archive`}
                                                        method="patch"
                                                        className="btn btn-soft btn-error btn-sm"
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
                            <div key={emp.id} className="card bg-base-100 border border-base-300 p-4">
                                <div className="flex justify-between items-start mb-2">
                                    <div className="flex items-center gap-3">
                                        {avatar(emp, 'w-10 h-10', 'text-sm')}
                                        <div>
                                            <Link href={`/employees/${emp.id}`} className="text-base-content no-underline font-semibold text-sm hover:text-primary">
                                                {emp.full_name}
                                            </Link>
                                            <div className="text-xs text-base-content/60 font-mono">{emp.employee_id}</div>
                                        </div>
                                    </div>
                                    <StatusBadge type={STATUS_META[emp.employment_status] ?? 'neutral'} className="whitespace-nowrap">
                                        {emp.employment_status}
                                    </StatusBadge>
                                </div>

                                <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                                    <span><Icon name="tabler--building" className="size-3.5 inline" /> {emp.department}</span>
                                    <span><Icon name="tabler--briefcase" className="size-3.5 inline" /> {emp.position}</span>
                                    <span><Icon name="tabler--calendar" className="size-3.5 inline" /> {fmtDate(emp.date_hired)}</span>
                                </div>

                                <div className="flex gap-2 flex-wrap mt-3 pt-3 border-t border-base-300">
                                    <Link href={`/employees/${emp.id}`} className="btn btn-soft btn-info btn-sm">
                                        <Icon name="tabler--eye" className="size-4" /> View
                                    </Link>
                                    <Link href={`/employees/${emp.id}/edit`} className="btn btn-soft btn-warning btn-sm">
                                        <Icon name="tabler--pencil" className="size-4" /> Edit
                                    </Link>
                                    {canManage && (
                                        <ConfirmButton
                                            title="Archive Employee?"
                                            text="This employee will be moved to the archive."
                                            confirmText="Yes, archive"
                                            url={`/employees/${emp.id}/archive`}
                                            method="patch"
                                            className="btn btn-soft btn-error btn-sm"
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

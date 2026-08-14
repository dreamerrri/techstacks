import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import DataTable from '../../Components/DataTable';
import ConfirmButton from '../../Components/ConfirmButton';

export default function EmployeesArchived({ employees }) {
    const avatar = (emp, size = 'w-8 h-8', text = 'text-xs') => (
        <div className={`${size} rounded-full overflow-hidden flex-shrink-0`}>
            {emp.user?.photo_url ? (
                <img src={emp.user.photo_url} alt={emp.full_name} className="w-full h-full object-cover" />
            ) : (
                <div className={`${size} rounded-full bg-gradient-to-br from-neutral to-neutral/80 flex items-center justify-center text-white ${text} font-bold`}>
                    {emp.full_name.charAt(0).toUpperCase()}
                </div>
            )}
        </div>
    );

    return (
        <AppLayout>
            <Head title="Archived Employees" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/employees" className="back-link text-subtle no-underline text-sm hover:text-success">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Employee page
                    </Link>
                </div>

                <DataTable
                    title="Archived Employees"
                    icon="tabler--archive"
                    tooltip="Manage archived employees"
                    paginator={employees}
                    empty="No archived employees."
                >
                    <div className="overflow-x-auto overflow-y-auto hidden md:block" style={{ maxHeight: '53vh' }}>
                        <table className="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {employees.data.map((emp) => (
                                    <tr key={emp.id} className="row-hover">
                                        <td className="font-mono text-subtle">{emp.employee_id}</td>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                {avatar(emp)}
                                                <span className="font-semibold text-base-content">{emp.full_name}</span>
                                            </div>
                                        </td>
                                        <td className="text-subtle">{emp.department}</td>
                                        <td className="text-subtle">{emp.position}</td>
                                        <td>
                                            <ConfirmButton
                                                title="Restore Employee?"
                                                text="This employee will be restored to the active list."
                                                icon="question"
                                                confirmText="Yes, restore"
                                                url={`/employees/${emp.id}/restore`}
                                                method="patch"
                                                className="btn btn-soft btn-success btn-sm"
                                            >
                                                <Icon name="ph--arrow-counter-clockwise-fill" className="size-4" /> Restore
                                            </ConfirmButton>
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
                                            <div className="font-semibold text-base-content text-sm">{emp.full_name}</div>
                                            <div className="text-xs text-subtle font-mono">{emp.employee_id}</div>
                                        </div>
                                    </div>
                                    <span className="badge badge-soft badge-neutral whitespace-nowrap">Archived</span>
                                </div>

                                <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-subtle mt-2">
                                    <span><Icon name="ph--buildings-fill" className="size-3.5 inline" /> {emp.department}</span>
                                    <span><Icon name="ph--briefcase-fill" className="size-3.5 inline" /> {emp.position}</span>
                                </div>

                                <div className="mt-3 pt-3 border-t border-base-200">
                                    <ConfirmButton
                                        title="Restore Employee?"
                                        text="This employee will be restored to the active list."
                                        icon="question"
                                        confirmText="Yes, restore"
                                        url={`/employees/${emp.id}/restore`}
                                        method="patch"
                                        className="btn btn-success btn-sm"
                                    >
                                        <Icon name="ph--arrow-counter-clockwise-fill" className="size-4" /> Restore
                                    </ConfirmButton>
                                </div>
                            </div>
                        ))}
                    </div>
                </DataTable>
            </div>
        </AppLayout>
    );
}

import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import EmployeeForm from './EmployeeForm';

export default function EmployeesEdit({ employee }) {
    return (
        <AppLayout>
            <Head title="Edit Employee" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href={`/employees/${employee.id}`} className="back-link text-subtle no-underline text-sm hover:text-success">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Employee Profile
                    </Link>
                </div>

                <div className="card bg-base-100 shadow-sm p-6">
                    <h2 className="text-base font-bold text-base-content mb-6 flex items-center gap-2">
                        <Icon name="tabler--user-edit" className="size-4 text-error" /> Edit — {employee.full_name}
                    </h2>
                    <EmployeeForm
                        employee={employee}
                        submitLabel="Update Employee"
                        submitIcon="ph--floppy-disk-fill"
                        cancelHref={`/employees/${employee.id}`}
                        cancelLabel="Cancel"
                    />
                </div>
            </div>
        </AppLayout>
    );
}

import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import EmployeeForm from './EmployeeForm';

export default function EmployeesCreate() {
    return (
        <AppLayout>
            <Head title="Add Employee" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/employees" className="back-link text-base-content no-underline text-sm hover:text-brand">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Employee List
                    </Link>
                </div>

                <div className="rounded-xl border border-edge bg-card p-6">
                    <h2 className="text-base font-bold text-base-content mb-6 flex items-center gap-2">
                        <Icon name="tabler--user-plus" className="size-4 text-error" /> Add New Employee
                    </h2>
                    <EmployeeForm
                        submitLabel="Save Employee"
                        submitIcon="ph--floppy-disk-fill"
                        cancelHref="/employees"
                        cancelLabel="Cancel"
                    />
                </div>
            </div>
        </AppLayout>
    );
}

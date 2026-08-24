import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import ConfirmButton from '../../components/ConfirmButton';
import { toast } from '../../components/toast';

function ClaimRow({ claim }) {
    const approve = () => {
        router.patch(`/users/${claim.id}/approve`, {}, {
            preserveScroll: true,
            onSuccess: () => toast('success', 'Account approved.'),
        });
    };

    return (
        <tr className="hover:bg-base-200/60">
            <td>
                <div className="font-semibold text-base-content">{claim.name}</div>
                <div className="text-xs text-subtle">{claim.email}</div>
            </td>
            <td className="text-xs text-subtle whitespace-nowrap">{claim.registered_at}</td>
            <td>
                {claim.matched_employee ? (
                    <div className="flex flex-col gap-0.5">
                        <span className="badge badge-soft badge-success w-fit gap-1 normal-case">
                            <Icon name="tabler--user-check" className="size-3.5" />
                            Match found
                        </span>
                        <span className="text-xs text-subtle">
                            {claim.matched_employee.employee_id} — {claim.matched_employee.full_name},{' '}
                            {claim.matched_employee.department} ({claim.matched_employee.employment_status})
                        </span>
                    </div>
                ) : (
                    <span className="badge badge-soft badge-warning w-fit gap-1 normal-case">
                        <Icon name="tabler--user-question" className="size-3.5" />
                        No match — new profile will be created
                    </span>
                )}
            </td>
            <td className="text-end">
                <div className="flex justify-end gap-2">
                    <button type="button" className="btn btn-soft btn-success btn-sm" onClick={approve}>
                        <Icon name="tabler--check" className="size-4" />
                        Approve
                    </button>
                    <ConfirmButton
                        className="btn btn-soft btn-error btn-sm"
                        title="Reject registration?"
                        text={`This permanently removes ${claim.name}'s account. They can register again later.`}
                        confirmText="Yes, reject it"
                        url={`/users/${claim.id}/reject`}
                        method="delete"
                    >
                        <Icon name="tabler--trash" className="size-4" />
                        Reject
                    </ConfirmButton>
                </div>
            </td>
        </tr>
    );
}

export default function UsersPending({ claims }) {
    const { auth } = usePage().props;
    const canManage = auth?.user?.role === 'admin' || auth?.user?.role === 'hr';

    return (
        <AppLayout title="Pending Accounts">
            <Head title="Pending Accounts" />

            <div className="card w-full min-w-0 border border-base-300 p-0">
                <div className="px-4 sm:px-7 pt-5 pb-4 rounded-t-2xl">
                    <h2 className="text-sm font-semibold uppercase tracking-widest text-faint flex items-center gap-2 m-0">
                        <Icon name="tabler--user-question" className="size-4 text-primary" />
                        <span>Registration Approval Queue</span>
                    </h2>
                    <p className="text-xs text-subtle mt-1 mb-0">
                        Self-registrations stay inactive until approved. Approving links the account to a matching
                        employee record, or creates a new profile when none exists.
                    </p>
                </div>

                {claims.length === 0 ? (
                    <div className="px-4 py-10 text-center">
                        <Icon name="tabler--circle-check" className="size-10 text-success mx-auto mb-3" />
                        <p className="text-sm text-subtle">No pending registrations. You're all caught up.</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto px-4 sm:px-7 pb-5">
                        <table className="table table-sm w-full">
                            <thead>
                                <tr>
                                    <th>Registrant</th>
                                    <th>Registered</th>
                                    <th>Employee Match</th>
                                    {canManage && <th className="text-end">Actions</th>}
                                </tr>
                            </thead>
                            <tbody>
                                {claims.map((claim) => (
                                    <ClaimRow key={claim.id} claim={claim} />
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

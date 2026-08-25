import { Head, router } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import DataTable from '../../components/DataTable';
import ConfirmButton from '../../components/ConfirmButton';

function MatchBadge({ matched }) {
    return matched ? (
        <span className="inline-flex items-center gap-1 rounded-full border border-transparent bg-brand/12 px-2.5 py-0.5 text-xs font-medium text-brand w-fit gap-1 normal-case">
            <Icon name="tabler--user-check" className="size-3.5" />
            Match found
        </span>
    ) : (
        <span className="inline-flex items-center gap-1 rounded-full border border-transparent bg-warning/15 px-2.5 py-0.5 text-xs font-medium text-warning w-fit gap-1 normal-case">
            <Icon name="tabler--user-question" className="size-3.5" />
            No match — new profile will be created
        </span>
    );
}

export default function UsersPending({ claims }) {
    const approve = (claim) => {
        router.patch(`/users/${claim.id}/approve`, {}, { preserveScroll: true });
    };

    const matchCell = (claim) => (
        <div className="flex flex-col gap-0.5">
            <MatchBadge matched={claim.matched_employee} />
            {claim.matched_employee && (
                <span className="text-xs text-dim-foreground">
                    {claim.matched_employee.employee_id} — {claim.matched_employee.full_name},{' '}
                    {claim.matched_employee.department} ({claim.matched_employee.employment_status})
                </span>
            )}
        </div>
    );

    return (
        <AppLayout title="Pending Accounts">
            <Head title="Pending Accounts" />

            <DataTable
                title="Registration Approval Queue"
                icon="tabler--user-question"
                tooltip="Self-registrations stay inactive until approved. Approving links the account to a matching employee record."
                baseUrl="/users/pending"
                empty={claims.length === 0 ? 'No pending registrations. You\'re all caught up.' : ''}
            >
                <div className="overflow-x-auto hidden md:block">
                    <table className="table table-hover">
                        <thead>
                            <tr>
                                <th>Registrant</th>
                                <th>Registered</th>
                                <th>Employee Match</th>
                                <th className="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {claims.map((claim) => (
                                <tr key={claim.id} className="row-hover">
                                    <td>
                                        <div className="font-semibold text-base-content">{claim.name}</div>
                                        <div className="text-xs text-dim-foreground">{claim.email}</div>
                                    </td>
                                    <td className="text-dim-foreground text-xs whitespace-nowrap">{claim.registered_at}</td>
                                    <td>{matchCell(claim)}</td>
                                    <td className="text-right">
                                        <div className="flex gap-2 justify-end items-center">
                                            <button
                                                type="button"
                                                className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-transparent bg-brand px-3 text-xs font-medium text-brand-foreground no-underline transition-colors hover:bg-brand/90"
                                                onClick={() => approve(claim)}
                                            >
                                                <Icon name="tabler--check" className="size-4" /> Approve
                                            </button>
                                            <ConfirmButton
                                                title="Reject registration?"
                                                text={`This permanently removes ${claim.name}'s account. They can register again later.`}
                                                confirmText="Yes, reject it"
                                                url={`/users/${claim.id}/reject`}
                                                method="delete"
                                                className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-danger/40 px-3 text-xs font-medium text-danger no-underline transition-colors hover:bg-danger/10"
                                            >
                                                <Icon name="tabler--trash" className="size-4" /> Reject
                                            </ConfirmButton>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="md:hidden p-4">
                    {claims.map((claim) => (
                        <div key={claim.id} className="rounded-xl border border-edge bg-card p-4 mb-3">
                            <div className="flex justify-between items-start mb-3">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-full bg-gradient-to-br from-brand to-brand/70 flex items-center justify-center text-brand-foreground text-sm font-bold flex-shrink-0">
                                        {claim.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div className="font-semibold text-base-content text-sm">{claim.name}</div>
                                        <div className="text-xs text-dim-foreground">{claim.email}</div>
                                    </div>
                                </div>
                                <span className="text-xs text-dim-foreground/70 whitespace-nowrap">{claim.registered_at}</span>
                            </div>

                            <div className="mb-2">{matchCell(claim)}</div>

                            <div className="flex justify-between items-center flex-wrap gap-2 pt-3 border-t border-edge">
                                <button
                                    type="button"
                                    className="inline-flex h-8 items-center gap-1.5 rounded-lg bg-brand px-3 text-xs font-medium text-brand-foreground no-underline transition-colors hover:bg-brand/90"
                                    onClick={() => approve(claim)}
                                >
                                    <Icon name="tabler--check" className="size-4" /> Approve
                                </button>
                                <ConfirmButton
                                    title="Reject registration?"
                                    text={`This permanently removes ${claim.name}'s account. They can register again later.`}
                                    confirmText="Yes, reject it"
                                    url={`/users/${claim.id}/reject`}
                                    method="delete"
                                    className="btn btn-outline btn-error btn-sm"
                                >
                                    <Icon name="tabler--trash" className="size-4" /> Reject
                                </ConfirmButton>
                            </div>
                        </div>
                    ))}
                </div>
            </DataTable>
        </AppLayout>
    );
}

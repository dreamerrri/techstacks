import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import FormField from '../../components/FormField';

const MAX_REIMBURSEMENT = 15000;

export default function FinancialRequestsEdit({ financialRequest }) {
    const isCashAdvance = financialRequest.request_type === 'cash_advance';
    const basicSalary = Number(financialRequest.employee?.basic_salary || 0);

    const { data, setData, put, processing, errors } = useForm({
        amount: financialRequest.amount ?? '',
        description: financialRequest.description ?? '',
        reason: financialRequest.reason ?? '',
        receipt_image: null,
    });

    const submit = (e) => {
        e.preventDefault();
        put(`/financial-requests/${financialRequest.id}`, { forceFormData: true });
    };

    const amountHelp = isCashAdvance
        ? `Amount is automatically set to your basic salary: ₱${basicSalary.toLocaleString('en-US', { minimumFractionDigits: 2 })}`
        : `Maximum amount: ₱${MAX_REIMBURSEMENT.toLocaleString('en-US', { minimumFractionDigits: 2 })} (₱15,000 maximum)`;

    return (
        <AppLayout>
            <Head title="Edit Financial Request" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href={`/financial-requests/${financialRequest.id}`} className="back-link text-dim-foreground no-underline text-sm hover:text-brand">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Request
                    </Link>
                </div>

                <div className="rounded-xl border border-edge bg-card shadow-md p-6 max-w-3xl">
                    <div className="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg px-3 py-1.5 mb-4">
                        <Icon name="ph--cash-register-fill" className="size-4 inline" /> Edit Request
                    </div>
                    <h2 className="text-lg font-bold text-base-content mb-1">Edit Financial Request #{financialRequest.id}</h2>
                    <p className="text-dim-foreground text-sm mb-6">
                        {isCashAdvance ? 'Cash Advance' : 'Reimbursement'} Request
                    </p>

                    <form onSubmit={submit}>
                        <FormField label="Request Type">
                            <input
                                type="text"
                                value={isCashAdvance ? 'Cash Advance' : 'Reimbursement'}
                                readOnly
                                className="input input-bordered w-full bg-base-200"
                            />
                        </FormField>

                        <FormField label="Amount (₱)" required error={errors.amount} help={amountHelp}>
                            <input
                                type="number"
                                name="amount"
                                value={data.amount}
                                min="0"
                                step="0.01"
                                readOnly={isCashAdvance}
                                max={!isCashAdvance ? MAX_REIMBURSEMENT : undefined}
                                placeholder={!isCashAdvance ? `Max: ₱${MAX_REIMBURSEMENT.toFixed(2)}` : ''}
                                onChange={(e) => setData('amount', e.target.value)}
                                className="input input-bordered w-full"
                                required
                            />
                        </FormField>

                        <FormField label="Description" error={errors.description}>
                            <input
                                type="text"
                                name="description"
                                value={data.description}
                                maxLength="255"
                                onChange={(e) => setData('description', e.target.value)}
                                className="input input-bordered w-full"
                                placeholder="Brief description of the request"
                            />
                        </FormField>

                        <FormField label="Reason" error={errors.reason}>
                            <textarea
                                name="reason"
                                rows="4"
                                maxLength="1000"
                                value={data.reason}
                                onChange={(e) => setData('reason', e.target.value)}
                                className="textarea textarea-bordered w-full resize-y"
                                placeholder="Provide a detailed reason for this financial request..."
                            />
                        </FormField>

                        {!isCashAdvance && (
                            <>
                                <FormField label="Receipt Image" error={errors.receipt_image} help="Upload new receipt image (optional, max 2MB)">
                                    <input
                                        type="file"
                                        name="receipt_image"
                                        accept="image/*"
                                        onChange={(e) => setData('receipt_image', e.target.files?.[0] ?? null)}
                                        className="file-input file-input-bordered w-full"
                                    />
                                </FormField>
                                {financialRequest.receipt_image && (
                                    <div className="mb-4">
                                        <p className="text-xs text-dim-foreground mb-1">Current receipt:</p>
                                        <img
                                            src={`/storage/${financialRequest.receipt_image}`}
                                            alt="Current Receipt"
                                            className="max-w-full h-auto rounded-lg border border-edge"
                                            style={{ maxHeight: '200px' }}
                                        />
                                    </div>
                                )}
                            </>
                        )}

                        <div className="flex gap-3 flex-wrap pt-4 border-t border-edge">
                            <button type="submit" className="btn btn-soft btn-primary" disabled={processing}>
                                <Icon name="ph--floppy-disk-fill" className="size-4" /> Update Request
                            </button>
                            <Link href={`/financial-requests/${financialRequest.id}`} className="btn btn-soft">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
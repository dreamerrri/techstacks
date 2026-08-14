import { useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import FormField from '../../Components/FormField';

const MAX_REIMBURSEMENT = 15000;

export default function FinancialRequestsCreate({ employee }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        request_type: '',
        amount: '',
        description: '',
        reason: '',
        receipt_image: null,
    });

    const basicSalary = Number(employee?.basic_salary || 0);
    const isReimbursement = data.request_type === 'reimbursement';
    const isCashAdvance = data.request_type === 'cash_advance';

    useEffect(() => {
        if (isCashAdvance) {
            setData('amount', basicSalary.toFixed(2));
        } else {
            setData('amount', '');
        }
        setData('receipt_image', null);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isCashAdvance, isReimbursement]);

    const submit = (e) => {
        e.preventDefault();
        post('/financial-requests', { forceFormData: true, onSuccess: () => reset() });
    };

    const amountHelp = isCashAdvance
        ? `Amount is automatically set to your basic salary: ₱${basicSalary.toLocaleString('en-US', { minimumFractionDigits: 2 })}`
        : `Maximum amount: ₱${MAX_REIMBURSEMENT.toLocaleString('en-US', { minimumFractionDigits: 2 })} (₱15,000 maximum)`;

    return (
        <AppLayout>
            <Head title="New Financial Request" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/financial-requests" className="back-link text-subtle no-underline text-sm hover:text-primary">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Requests
                    </Link>
                </div>

                <div className="card bg-base-100 shadow-md p-6 max-w-3xl">
                    <div className="inline-block font-semibold text-xs text-info bg-info/10 rounded-lg px-3 py-1.5 mb-4">
                        <Icon name="ph--cash-fill" className="size-4 inline" /> New Request
                    </div>
                    <h2 className="text-lg font-bold text-base-content mb-1">Create Financial Request</h2>
                    <p className="text-subtle text-sm mb-6">Submit a request for cash advance or reimbursement</p>

                    <form onSubmit={submit}>
                        <FormField label="Request Type" required error={errors.request_type}>
                            <select
                                name="request_type"
                                value={data.request_type}
                                onChange={(e) => setData('request_type', e.target.value)}
                                className="select select-bordered w-full"
                                required
                            >
                                <option value="">Select type...</option>
                                <option value="cash_advance">Cash Advance</option>
                                <option value="reimbursement">Reimbursement</option>
                            </select>
                        </FormField>

                        <FormField label="Amount (₱)" required error={errors.amount} help={amountHelp}>
                            <input
                                type="number"
                                name="amount"
                                value={data.amount}
                                min="0"
                                step="0.01"
                                readOnly={isCashAdvance}
                                max={isReimbursement ? MAX_REIMBURSEMENT : undefined}
                                placeholder={isReimbursement ? `Max: ₱${MAX_REIMBURSEMENT.toFixed(2)}` : ''}
                                onChange={(e) => setData('amount', e.target.value)}
                                className="input input-bordered w-full"
                                required
                            />
                        </FormField>

                        <FormField label="Description" error={errors.description} help="Short description (optional)">
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

                        <FormField label="Reason" error={errors.reason} help="Maximum 1000 characters">
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

                        {isReimbursement && (
                            <FormField label="Receipt Image" required error={errors.receipt_image} help="Upload receipt image (required for reimbursements, max 2MB)">
                                <input
                                    type="file"
                                    name="receipt_image"
                                    accept="image/*"
                                    onChange={(e) => setData('receipt_image', e.target.files?.[0] ?? null)}
                                    className="file-input file-input-bordered w-full"
                                    required={isReimbursement}
                                />
                            </FormField>
                        )}

                        <div className="flex gap-3 flex-wrap pt-4 border-t border-base-300">
                            <button type="submit" className="btn btn-soft btn-primary" disabled={processing}>
                                <Icon name="ph--paper-plane-fill" className="size-4" /> Submit Request
                            </button>
                            <Link href="/financial-requests" className="btn btn-soft">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
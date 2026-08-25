import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import FormField from '../../components/FormField';

export default function PermissionsCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        slug: '',
        module: '',
        description: '',
        is_active: true,
    });

    const [slugTouched, setSlugTouched] = useState(false);

    const handleName = (e) => {
        const value = e.target.value;
        setData('name', value);
        if (!slugTouched) {
            setData('slug', value.toLowerCase().trim().replace(/\s+/g, '.').replace(/[^a-z0-9.]/g, ''));
        }
    };

    return (
        <AppLayout>
            <Head title="Create Permission" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/permissions" className="back-link text-dim-foreground no-underline text-sm hover:text-success">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Permissions
                    </Link>
                </div>

                <div className="rounded-xl border border-edge bg-card p-6">
                    <h2 className="text-base font-bold text-base-content mb-6 flex items-center gap-2">
                        <Icon name="ph--key-fill" className="size-5 text-error" /> Create New Permission
                    </h2>

                    <form onSubmit={(e) => { e.preventDefault(); post('/permissions'); }}>
                        <div className="mb-8">
                            <h3 className="text-xs font-semibold uppercase tracking-widest text-dim-foreground/70 border-b-2 border-error/20 pb-2 mb-4">
                                <Icon name="ph--info-fill" className="size-4 text-error inline" /> Permission Details
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <FormField label="Permission Name" required error={errors.name}>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={handleName}
                                        className="input input-bordered w-full"
                                        placeholder="e.g. View Employees"
                                        required
                                    />
                                </FormField>
                                <FormField label="Slug" required error={errors.slug} help="Dot-notation, lowercase (used in middleware)">
                                    <input
                                        type="text"
                                        value={data.slug}
                                        onChange={(e) => { setData('slug', e.target.value); setSlugTouched(e.target.value !== ''); }}
                                        className="input input-bordered w-full"
                                        placeholder="e.g. view.employees"
                                        required
                                    />
                                </FormField>
                                <FormField label="Module" required error={errors.module} help="Groups permissions on the roles page">
                                    <input
                                        type="text"
                                        value={data.module}
                                        onChange={(e) => setData('module', e.target.value)}
                                        className="input input-bordered w-full"
                                        placeholder="e.g. Employees, Payroll, Users"
                                        required
                                    />
                                </FormField>
                                <div className="fieldset md:col-span-2">
                                    <FormField label="Description" error={errors.description}>
                                        <textarea
                                            rows="2"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            className="textarea textarea-bordered w-full"
                                            placeholder="What does this permission allow?"
                                        />
                                    </FormField>
                                </div>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        checked={data.is_active}
                                        onChange={(e) => setData('is_active', e.target.checked)}
                                        className="checkbox checkbox-error"
                                    />
                                    <span className="font-semibold text-dim-foreground text-sm">Active</span>
                                </div>
                            </div>
                        </div>

                        <div className="flex gap-3 flex-wrap pt-4 border-t border-edge">
                            <button type="submit" className="inline-flex h-9 items-center gap-2 rounded-lg bg-danger px-4 text-sm font-medium text-danger-foreground no-underline transition-colors hover:bg-danger/90" disabled={processing}>
                                <Icon name="ph--floppy-disk-fill" className="size-4" /> Create Permission
                            </button>
                            <Link href="/permissions" className="btn btn-soft btn-success">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
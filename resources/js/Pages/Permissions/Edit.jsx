import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Components/AppLayout';
import Icon from '../../Components/Icon';
import FormField from '../../Components/FormField';

export default function PermissionsEdit({ permission }) {
    const { data, setData, put, processing, errors } = useForm({
        name: permission.name,
        slug: permission.slug,
        module: permission.module,
        description: permission.description || '',
        is_active: !!permission.is_active,
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
            <Head title="Edit Permission" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href={`/permissions/${permission.id}`} className="back-link text-subtle no-underline text-sm hover:text-success">
                        <Icon name="ph--arrow-left-fill" className="size-4" /> Back to Permission
                    </Link>
                </div>

                <div className="card bg-base-100 shadow-sm p-6">
                    <h2 className="text-base font-bold text-base-content mb-6 flex items-center gap-2">
                        <Icon name="ph--pencil-fill" className="size-5 text-error" /> Edit — {permission.name}
                    </h2>

                    <form onSubmit={(e) => { e.preventDefault(); put(`/permissions/${permission.id}`); }}>
                        <div className="mb-8">
                            <h3 className="text-xs font-semibold uppercase tracking-widest text-faint border-b-2 border-error/20 pb-2 mb-4">
                                <Icon name="ph--info-fill" className="size-4 text-error inline" /> Permission Details
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <FormField label="Permission Name" required error={errors.name}>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={handleName}
                                        className="input input-bordered w-full"
                                        required
                                    />
                                </FormField>
                                <FormField label="Slug" required error={errors.slug} help="Dot-notation, lowercase (used in middleware)">
                                    <input
                                        type="text"
                                        value={data.slug}
                                        onChange={(e) => { setData('slug', e.target.value); setSlugTouched(e.target.value !== ''); }}
                                        className="input input-bordered w-full"
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
                                    <span className="font-semibold text-muted text-sm">Active</span>
                                </div>
                            </div>
                        </div>

                        <div className="flex gap-3 flex-wrap pt-4 border-t border-base-300">
                            <button type="submit" className="btn btn-error" disabled={processing}>
                                <Icon name="ph--floppy-disk-fill" className="size-4" /> Update Permission
                            </button>
                            <Link href={`/permissions/${permission.id}`} className="btn btn-soft btn-success">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
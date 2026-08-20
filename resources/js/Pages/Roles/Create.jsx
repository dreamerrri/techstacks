import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import FormField from '../../components/FormField';

export default function RolesCreate({ permissions }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        slug: '',
        description: '',
        is_active: true,
        permissions: [],
    });

    const [slugTouched, setSlugTouched] = useState(false);

    const handleName = (e) => {
        const value = e.target.value;
        setData('name', value);
        if (!slugTouched) {
            setData('slug', value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, ''));
        }
    };

    const togglePermission = (id) => {
        setData('permissions', data.permissions.includes(id)
            ? data.permissions.filter((p) => p !== id)
            : [...data.permissions, id]);
    };

    return (
        <AppLayout>
            <Head title="Create Role" />
            <div className="p-2 sm:p-4">
                <div className="mb-5">
                    <Link href="/roles" className="back-link text-base-content no-underline text-sm hover:text-primary">
                        <Icon name="tabler--arrow-left" className="size-4" /> Back to Roles
                    </Link>
                </div>

                <div className="card bg-base-100 shadow-md p-6">
                    <h2 className="text-base font-bold text-base-content mb-6 flex items-center gap-2">
                        <Icon name="tabler--id-badge" className="size-5 text-error" /> Create New Role
                    </h2>

                    <form onSubmit={(e) => { e.preventDefault(); post('/roles'); }}>
                        <div className="mb-8">
                            <h3 className="text-xs font-semibold uppercase tracking-widest text-base-content border-b-2 border-error/20 pb-2 mb-4">
                                <Icon name="tabler--info-circle" className="size-4 text-error inline" /> Role Details
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <FormField label="Role Name" required error={errors.name}>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={handleName}
                                        className="input input-bordered w-full"
                                        required
                                    />
                                </FormField>
                                <FormField label="Slug" required error={errors.slug} help="Lowercase, no spaces (used in code)">
                                    <input
                                        type="text"
                                        value={data.slug}
                                        onChange={(e) => { setData('slug', e.target.value); setSlugTouched(e.target.value !== ''); }}
                                        className="input input-bordered w-full"
                                        placeholder="e.g. admin, hr, employee"
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
                                    <span className="font-semibold text-base-content text-sm">Active</span>
                                </div>
                            </div>
                        </div>

                        <div className="mb-8">
                            <h3 className="text-xs font-semibold uppercase tracking-widest text-base-content border-b-2 border-error/20 pb-2 mb-4">
                                <Icon name="tabler--key" className="size-4 text-error inline" /> Permissions
                            </h3>
                            {errors.permissions && (
                                <p className="label text-error text-xs mt-2">{typeof errors.permissions === 'string' ? errors.permissions : errors.permissions.join(', ')}</p>
                            )}
                            {Object.keys(permissions || {}).length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    {Object.entries(permissions).map(([module, modulePermissions]) => (
                                        <div key={module} className="bg-base-200 border border-base-300 rounded-xl p-4">
                                            <div className="text-xs font-bold text-base-content uppercase tracking-widest mb-3">
                                                {module.charAt(0).toUpperCase() + module.slice(1)}
                                            </div>
                                            {modulePermissions.map((permission) => (
                                                <label key={permission.id} className="flex items-center gap-2 cursor-pointer text-xs text-muted mb-2">
                                                    <input
                                                        type="checkbox"
                                                        checked={data.permissions.includes(permission.id)}
                                                        onChange={() => togglePermission(permission.id)}
                                                        className="checkbox checkbox-error checkbox-xs"
                                                    />
                                                    {permission.name}
                                                </label>
                                            ))}
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-base-content text-sm m-0">No permissions available. Create permissions first.</p>
                            )}
                        </div>

                        <div className="flex gap-3 flex-wrap pt-4 border-t border-base-300">
                            <button type="submit" className="btn btn-soft btn-error" disabled={processing}>
                                <Icon name="tabler--device-floppy" className="size-4" /> Create Role
                            </button>
                            <Link href="/roles" className="btn btn-success btn-soft">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
import { useEffect, useRef, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import Icon from '../../components/Icon';
import Panel, { PanelHeader } from '../../components/Panel';
import DetailRow from '../../components/DetailRow';
import FormField from '../../components/FormField';
import StatusBadge from '../../components/StatusBadge';
import { toast } from '../../components/toast';

const THEMES = [
    'techstacks', 'techstacks-light', 'light', 'dark', 'black', 'claude', 'corporate',
    'ghibli', 'gourmet', 'luxury', 'mintlify', 'pastel', 'perplexity', 'shadcn',
    'slack', 'soft', 'spotify', 'valorant', 'vscode',
];

function AvatarUpload({ photoUrl, initials, size = 'w-32 h-32' }) {
    const fileRef = useRef(null);

    const handleFile = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('photo', file);
        router.post('/profile/photo', formData, {
            preserveScroll: true,
            onSuccess: () => toast('success', 'Profile photo updated.'),
            onError: () => toast('error', 'Failed to upload photo.'),
        });
    };

    return (
        <div className="avatar-upload relative flex-shrink-0">
            <div className={`avatar-circle ${size} rounded-full bg-base-200 border-2 border-base-300 overflow-hidden flex items-center justify-center cursor-pointer text-3xl font-bold text-base-content`}>
                {photoUrl ? (
                    <img className="avatar-img w-full h-full object-cover" src={photoUrl} alt="" />
                ) : (
                    <span className="avatar-initials">{initials}</span>
                )}
            </div>
            <label className="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-base-100 border-2 border-base-300 flex items-center justify-center cursor-pointer shadow-sm">
                <Icon name="tabler--camera" className="size-3 text-base-content" />
                <input type="file" accept="image/*" className="hidden" ref={fileRef} onChange={handleFile} />
            </label>
        </div>
    );
}

export default function ProfileShow({ employee }) {
    const { auth } = usePage().props;
    const user = auth.user;
    const isAdmin = user?.role === 'admin';
    const isHR = user?.role === 'hr';
    const roleClass = isAdmin ? 'badge-soft badge-error' : isHR ? 'badge-soft badge-info' : 'badge-soft badge-success';
    const [activeTab, setActiveTab] = useState('account');
    const [selectedTheme, setSelectedTheme] = useState(user?.theme || 'light');

    const personal = useForm({
        first_name: employee?.first_name || '',
        middle_name: employee?.middle_name || '',
        last_name: employee?.last_name || '',
        birthdate: employee?.birthdate || '',
        gender: employee?.gender || 'Male',
        civil_status: employee?.civil_status || 'Single',
        contact_number: employee?.contact_number || '',
        address: employee?.address || '',
    });

    const account = useForm({
        name: user?.name || '',
        email: user?.email || '',
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const govIds = employee ? [
        ['SSS Number', employee.sss_number, 'tabler--shield-check', 'text-success', 'bg-success/10'],
        ['PhilHealth Number', employee.philhealth_number, 'ph--heart-fill', 'text-info', 'bg-info/10'],
        ['Pag-IBIG Number', employee.pagibig_number, 'ph--house-fill', 'text-notification', 'bg-notification/10'],
        ['TIN Number', employee.tin_number, 'ph--receipt-fill', 'text-secondary', 'bg-secondary/10'],
    ] : [];

    const tabs = [
        { id: 'account', label: 'Account Info' },
        { id: 'gov', label: 'Government Contributions' },
        { id: 'settings', label: 'Settings' },
    ];

    const selectTheme = (theme) => {
        setSelectedTheme(theme);
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        fetch('/settings/theme', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ theme }),
        }).catch(() => {});
    };

    const fmt = (value, options = {}) => {
        if (!value) return '—';
        return new Date(value).toLocaleDateString('en-US', options);
    };

    return (
        <AppLayout>
            <Head title="My Profile" />
            <div className="p-2 sm:p-4">
                <div className="card bg-base-100 border border-base-300 p-6">
                    <div className="w-full mt-16 mb-6">
                        <div className="px-6">
                            <div className="flex flex-wrap justify-center">
                                <div className="w-full flex justify-center">
                                    <AvatarUpload photoUrl={user?.photo_url} initials={user?.name?.charAt(0).toUpperCase() || '?'} />
                                </div>
                            </div>
                            <div className="text-center mt-2 pb-6">
                                <h3 className="text-2xl text-base-content font-bold leading-normal mb-1">{user?.name}</h3>
                                <p className="text-subtle text-sm m-0 mb-2">{user?.email}</p>
                                <div className="text-xs mt-0 mb-2 text-subtle font-bold uppercase flex items-center justify-center gap-1">
                                    <span className={`badge ${roleClass}`}>{user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : ''}</span>
                                    {employee && (
                                        <span className="badge badge-soft badge-neutral">{employee.position} — {employee.department}</span>
                                    )}
                                </div>
                            </div>
                        </div>

                        <nav className="tabs tabs-bordered overflow-x-auto [&_.tab:hover]:text-primary [&_.tab:hover]:border-primary [&_.tab-active]:border-primary [&_.tab-active]:text-primary" aria-label="Tabs" role="tablist" aria-orientation="horizontal">
                            {tabs.map((tab, index) => (
                                <button
                                    key={tab.id}
                                    type="button"
                                    className={`tab active-tab:tab-active whitespace-nowrap text-xs sm:text-sm sm:w-full ${activeTab === tab.id ? 'active' : ''}`}
                                    id={`tabs-${tab.id}-item`}
                                    data-tab={`#tabs-${tab.id}`}
                                    aria-controls={`tabs-${tab.id}`}
                                    role="tab"
                                    aria-selected={activeTab === tab.id}
                                    onClick={() => setActiveTab(tab.id)}
                                >
                                    {tab.label}
                                </button>
                            ))}
                        </nav>

                        <div className="mt-3 px-6 pb-6">
                            {activeTab === 'account' && (
                                employee ? (
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <Panel className="md:col-span-2">
                                            <PanelHeader icon="ph--identification-card-fill" color="text-info" bg="bg-info/10">
                                                Personal Information
                                            </PanelHeader>
                                            <form onSubmit={(e) => { e.preventDefault(); personal.put('/profile/personal'); }}>
                                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
                                                    <FormField label="First Name" error={personal.errors.first_name}>
                                                        <input type="text" value={personal.data.first_name} onChange={(e) => personal.setData('first_name', e.target.value)} className="input input-bordered w-full" required />
                                                    </FormField>
                                                    <FormField label="Middle Name">
                                                        <input type="text" value={personal.data.middle_name} onChange={(e) => personal.setData('middle_name', e.target.value)} className="input input-bordered w-full" />
                                                    </FormField>
                                                    <FormField label="Last Name" error={personal.errors.last_name}>
                                                        <input type="text" value={personal.data.last_name} onChange={(e) => personal.setData('last_name', e.target.value)} className="input input-bordered w-full" required />
                                                    </FormField>
                                                    <FormField label="Birthdate" error={personal.errors.birthdate}>
                                                        <input type="date" value={personal.data.birthdate} onChange={(e) => personal.setData('birthdate', e.target.value)} className="input input-bordered w-full" required max={(() => { const t = new Date(Date.now() - 86400000); t.setMinutes(t.getMinutes() - t.getTimezoneOffset()); return t.toISOString().split('T')[0]; })()} />
                                                    </FormField>
                                                    <FormField label="Gender" error={personal.errors.gender}>
                                                        <select value={personal.data.gender} onChange={(e) => personal.setData('gender', e.target.value)} className="select select-bordered w-full" required>
                                                            {['Male', 'Female', 'Other'].map((g) => <option key={g} value={g}>{g}</option>)}
                                                        </select>
                                                    </FormField>
                                                    <FormField label="Civil Status" error={personal.errors.civil_status}>
                                                        <select value={personal.data.civil_status} onChange={(e) => personal.setData('civil_status', e.target.value)} className="select select-bordered w-full" required>
                                                            {['Single', 'Married', 'Widowed', 'Separated'].map((cs) => <option key={cs} value={cs}>{cs}</option>)}
                                                        </select>
                                                    </FormField>
                                                    <FormField label="Contact Number" error={personal.errors.contact_number}>
                                                        <input type="text" value={personal.data.contact_number} placeholder="09XXXXXXXXX" maxLength={11} onChange={(e) => personal.setData('contact_number', e.target.value)} className="input input-bordered w-full" />
                                                    </FormField>
                                                    <div className="fieldset md:col-span-2 lg:col-span-3">
                                                        <FormField label="Address" error={personal.errors.address}>
                                                            <textarea rows="2" value={personal.data.address} onChange={(e) => personal.setData('address', e.target.value)} className="textarea textarea-bordered w-full" />
                                                        </FormField>
                                                    </div>
                                                </div>
                                                <button type="submit" className="btn btn-info" disabled={personal.processing}>
                                                    <Icon name="ph--floppy-disk-fill" className="size-4" /> Save Personal Info
                                                </button>
                                            </form>
                                        </Panel>

                                        <Panel padding="p-5">
                                            <PanelHeader icon="ph--briefcase-fill" color="text-success" bg="bg-success/10">
                                                Employment Information
                                            </PanelHeader>
                                            <div className="flex flex-col text-sm">
                                                <DetailRow label="Department">{employee.department}</DetailRow>
                                                <DetailRow label="Position">{employee.position}</DetailRow>
                                                <DetailRow label="Employment Status">{employee.employment_status}</DetailRow>
                                                <DetailRow label="Date Hired">{fmt(employee.date_hired, { month: 'short', day: '2-digit', year: 'numeric' })}</DetailRow>
                                                <DetailRow label="Salary Type" border={false}>{employee.salary_type}</DetailRow>
                                            </div>
                                            <div className="mt-3 px-3 py-2 bg-base-200 rounded-lg text-xs text-faint">
                                                <Icon name="ph--info-fill" className="size-3.5 inline" /> Employment details can only be changed by HR.
                                            </div>
                                        </Panel>

                                        <Panel padding="p-5">
                                            <PanelHeader icon="tabler--shield-check">
                                                Account Info
                                            </PanelHeader>
                                            <div className="flex flex-col text-sm">
                                                <DetailRow label="Role">
                                                    <span className={`badge ${roleClass}`}>{user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : ''}</span>
                                                </DetailRow>
                                                <DetailRow label="Account Status">
                                                    <StatusBadge type="success">Active</StatusBadge>
                                                </DetailRow>
                                                <DetailRow label="Member Since">{fmt(user?.created_at, { month: 'short', day: '2-digit', year: 'numeric' })}</DetailRow>
                                                <DetailRow label="Last Login" border={false}>
                                                    {user?.last_login_at ? fmt(user.last_login_at, { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'}
                                                </DetailRow>
                                            </div>
                                        </Panel>
                                    </div>
                                ) : (
                                    <Panel padding="p-5">
                                        <PanelHeader icon="tabler--shield-check">
                                            Account Info
                                        </PanelHeader>
                                        <div className="flex flex-col text-sm">
                                            <DetailRow label="Role">
                                                <span className={`badge ${roleClass}`}>{user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : ''}</span>
                                            </DetailRow>
                                            <DetailRow label="Account Status">
                                                <StatusBadge type="success">Active</StatusBadge>
                                            </DetailRow>
                                            <DetailRow label="Member Since">{fmt(user?.created_at, { month: 'short', day: '2-digit', year: 'numeric' })}</DetailRow>
                                            <DetailRow label="Last Login" border={false}>
                                                {user?.last_login_at ? fmt(user.last_login_at, { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'}
                                            </DetailRow>
                                        </div>
                                    </Panel>
                                )
                            )}

                            {activeTab === 'gov' && (
                                employee ? (
                                    <Panel padding="p-5">
                                        <PanelHeader icon="ph--bank-fill" color="text-error" bg="bg-error/10">
                                            Government IDs
                                        </PanelHeader>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                                            {govIds.map(([label, value, icon, color, bg]) => (
                                                <Panel key={label} padding="p-4" className="text-center">
                                                    <div className={`w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 ${color} ${bg}`}>
                                                        <Icon name={icon} className="size-5" />
                                                    </div>
                                                    <div className="text-xs text-faint uppercase tracking-widest font-medium mb-1">{label}</div>
                                                    <div className="font-bold font-mono text-base-content text-xs break-all">{value || '—'}</div>
                                                </Panel>
                                            ))}
                                        </div>
                                    </Panel>
                                ) : (
                                    <Panel padding="p-6" className="text-center text-sm text-subtle">
                                        No employee record is linked to this account yet, so government contribution details aren&apos;t available.
                                    </Panel>
                                )
                            )}

                            {activeTab === 'settings' && (
                                <>
                                    <Panel>
                                        <PanelHeader icon="ph--user-gear-fill" color="text-error" bg="bg-error/10">
                                            Account Settings
                                        </PanelHeader>
                                        <form onSubmit={(e) => { e.preventDefault(); account.put('/profile'); }}>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                                                <FormField label="Display Name" error={account.errors.name}>
                                                    <input type="text" value={account.data.name} onChange={(e) => account.setData('name', e.target.value)} className="input input-bordered w-full" required />
                                                </FormField>
                                                <FormField label="Email Address" error={account.errors.email}>
                                                    <input type="email" value={account.data.email} onChange={(e) => account.setData('email', e.target.value)} className="input input-bordered w-full" required />
                                                </FormField>
                                            </div>

                                            <div className="text-xs font-semibold text-faint uppercase tracking-widest mb-2">
                                                Change Password <span className="normal-case font-normal">(leave blank to keep current)</span>
                                            </div>
                                            <div className="border-t border-base-300 mb-4"></div>

                                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                                                <FormField label="Current Password" error={account.errors.current_password}>
                                                    <input type="password" value={account.data.current_password} placeholder="••••••••" onChange={(e) => account.setData('current_password', e.target.value)} className="input input-bordered w-full" />
                                                </FormField>
                                                <FormField label="New Password" error={account.errors.password}>
                                                    <input type="password" value={account.data.password} placeholder="••••••••" onChange={(e) => account.setData('password', e.target.value)} className="input input-bordered w-full" />
                                                </FormField>
                                                <FormField label="Confirm New Password">
                                                    <input type="password" value={account.data.password_confirmation} placeholder="••••••••" onChange={(e) => account.setData('password_confirmation', e.target.value)} className="input input-bordered w-full" />
                                                </FormField>
                                            </div>

                                            <button type="submit" className="btn btn-error" disabled={account.processing}>
                                                <Icon name="ph--floppy-disk-fill" className="size-4" /> Save Changes
                                            </button>
                                        </form>
                                    </Panel>

                                    <Panel>
                                        <PanelHeader icon="ph--palette-fill" color="text-accent" bg="bg-accent/10">
                                            Appearance
                                        </PanelHeader>
                                        <p className="text-xs text-faint mb-4">
                                            Pick a theme — it applies instantly and is saved to your account.
                                        </p>
                                        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" id="theme-picker">
                                            {THEMES.map((theme) => (
                                                <label key={theme} className="cursor-pointer border rounded-field p-3 flex items-center gap-2 has-checked:border-primary has-checked:ring-2" data-theme={theme}>
                                                    <input
                                                        type="radio"
                                                        name="theme"
                                                        value={theme}
                                                        className="theme-controller radio radio-sm"
                                                        checked={selectedTheme === theme}
                                                        onChange={() => selectTheme(theme)}
                                                    />
                                                    <span className="capitalize text-sm">{theme}</span>
                                                </label>
                                            ))}
                                        </div>
                                    </Panel>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
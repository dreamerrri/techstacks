import { useForm } from '@inertiajs/react';
import Icon from '../../components/Icon';
import FormField from '../../components/FormField';

const YESTERDAY = new Date(Date.now() - 86400000).toISOString().split('T')[0];
const TODAY = new Date().toISOString().split('T')[0];

const GENDERS = ['Male', 'Female', 'Other'];
const CIVIL_STATUSES = ['Single', 'Married', 'Widowed', 'Separated'];
const DEPARTMENTS = ['Sales', 'Marketing', 'Human Resources', 'Information Technology'];
const POSITIONS = ['Manager', 'Supervisor', 'Employee'];
const EMPLOYMENT_STATUSES = ['Regular', 'Probationary', 'Contractual', 'Part-time'];
const SALARY_TYPES = ['Monthly', 'Daily', 'Hourly'];

function autoFormat(raw, type) {
    const digits = raw.replace(/\D/g, '');
    let f = '';
    if (type === 'sss') f = digits.length > 9 ? `${digits.slice(0, 2)}-${digits.slice(2, 9)}-${digits.slice(9, 10)}` : digits.length > 2 ? `${digits.slice(0, 2)}-${digits.slice(2)}` : digits;
    if (type === 'philhealth') f = digits.length > 11 ? `${digits.slice(0, 2)}-${digits.slice(2, 11)}-${digits.slice(11, 12)}` : digits.length > 2 ? `${digits.slice(0, 2)}-${digits.slice(2)}` : digits;
    if (type === 'pagibig') f = digits.length > 8 ? `${digits.slice(0, 4)}-${digits.slice(4, 8)}-${digits.slice(8, 12)}` : digits.length > 4 ? `${digits.slice(0, 4)}-${digits.slice(4)}` : digits;
    if (type === 'tin') f = digits.length > 6 ? `${digits.slice(0, 3)}-${digits.slice(3, 6)}-${digits.slice(6, 9)}` : digits.length > 3 ? `${digits.slice(0, 3)}-${digits.slice(3)}` : digits;
    return f || digits;
}

const GOV_MAXLENGTH = { sss_number: 12, philhealth_number: 14, pagibig_number: 14, tin_number: 11 };
const GOV_FORMAT = { sss_number: 'sss', philhealth_number: 'philhealth', pagibig_number: 'pagibig', tin_number: 'tin' };
const GOV_HELP = {
    sss_number: 'Format: XX-XXXXXXX-X',
    philhealth_number: 'Format: XX-XXXXXXXXX-X',
    pagibig_number: 'Format: XXXX-XXXX-XXXX',
    tin_number: 'Format: XXX-XXX-XXX',
};

export default function EmployeeForm({ employee, submitLabel, submitIcon, cancelHref, cancelLabel = 'Cancel' }) {
    const { data, setData, post, put, processing, errors } = useForm({
        first_name: employee?.first_name || '',
        middle_name: employee?.middle_name || '',
        last_name: employee?.last_name || '',
        birthdate: employee?.birthdate ? employee.birthdate.slice(0, 10) : '',
        gender: employee?.gender || '',
        civil_status: employee?.civil_status || '',
        address: employee?.address || '',
        contact_number: employee?.contact_number || '',
        email: employee?.email || '',
        department: employee?.department || '',
        position: employee?.position || '',
        employment_status: employee?.employment_status || '',
        date_hired: employee?.date_hired ? employee.date_hired.slice(0, 10) : '',
        salary_type: employee?.salary_type || '',
        basic_salary: employee?.basic_salary || '',
        sss_number: employee?.sss_number || '',
        philhealth_number: employee?.philhealth_number || '',
        pagibig_number: employee?.pagibig_number || '',
        tin_number: employee?.tin_number || '',
    });

    const setGov = (field, value) => {
        setData(field, autoFormat(value, GOV_FORMAT[field]));
    };

    const submit = (e) => {
        e.preventDefault();
        if (employee) {
            put(`/employees/${employee.id}`);
        } else {
            post('/employees');
        }
    };

    const sectionHeader = (icon, label) => (
        <h3 className="text-xs font-semibold uppercase tracking-widest text-faint border-b-2 border-error/20 pb-2 mb-4 flex items-center gap-2">
            <Icon name={icon} className="size-4 text-error" /> {label}
        </h3>
    );

    return (
        <form onSubmit={submit}>
            <div className="mb-8">
                {sectionHeader('tabler--user', 'Personal Information')}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <FormField label="First Name" required error={errors.first_name}>
                        <input type="text" value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} className="input input-bordered w-full" required />
                    </FormField>
                    <FormField label="Middle Name">
                        <input type="text" value={data.middle_name} onChange={(e) => setData('middle_name', e.target.value)} className="input input-bordered w-full" />
                    </FormField>
                    <FormField label="Last Name" required error={errors.last_name}>
                        <input type="text" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} className="input input-bordered w-full" required />
                    </FormField>
                    <FormField label="Birthdate" required error={errors.birthdate}>
                        <input type="date" value={data.birthdate} max={YESTERDAY} onChange={(e) => setData('birthdate', e.target.value)} className="input input-bordered w-full" required />
                    </FormField>
                    <FormField label="Gender" required error={errors.gender}>
                        <select value={data.gender} onChange={(e) => setData('gender', e.target.value)} className="select select-bordered w-full" required>
                            <option value="">Select Gender</option>
                            {GENDERS.map((g) => <option key={g} value={g}>{g}</option>)}
                        </select>
                    </FormField>
                    <FormField label="Civil Status" required error={errors.civil_status}>
                        <select value={data.civil_status} onChange={(e) => setData('civil_status', e.target.value)} className="select select-bordered w-full" required>
                            <option value="">Select Civil Status</option>
                            {CIVIL_STATUSES.map((cs) => <option key={cs} value={cs}>{cs}</option>)}
                        </select>
                    </FormField>
                    <FormField label="Contact Number" required error={errors.contact_number} help="Must be 11 digits starting with 09">
                        <input type="text" value={data.contact_number} placeholder="09XXXXXXXXX" maxLength="11" onChange={(e) => setData('contact_number', e.target.value)} className="input input-bordered w-full" required />
                    </FormField>
                    <FormField label="Email Address" required error={errors.email}>
                        <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className="input input-bordered w-full" required />
                    </FormField>
                    <FormField label="Address" required error={errors.address} wrapperClass="md:col-span-2 lg:col-span-3">
                        <textarea rows="2" value={data.address} onChange={(e) => setData('address', e.target.value)} className="textarea textarea-bordered w-full" required />
                    </FormField>
                </div>
            </div>

            <div className="mb-8">
                {sectionHeader('ph--briefcase-fill', 'Employment Details')}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <FormField label="Department" required error={errors.department}>
                        <select value={data.department} onChange={(e) => setData('department', e.target.value)} className="select select-bordered w-full" required>
                            <option value="">Select Department</option>
                            {DEPARTMENTS.map((dept) => <option key={dept} value={dept}>{dept}</option>)}
                        </select>
                    </FormField>
                    <FormField label="Position" required error={errors.position}>
                        <select value={data.position} onChange={(e) => setData('position', e.target.value)} className="select select-bordered w-full" required>
                            <option value="">Select Position</option>
                            {POSITIONS.map((pos) => <option key={pos} value={pos}>{pos}</option>)}
                        </select>
                    </FormField>
                    <FormField label="Employment Status" required error={errors.employment_status}>
                        <select value={data.employment_status} onChange={(e) => setData('employment_status', e.target.value)} className="select select-bordered w-full" required>
                            <option value="">Select Status</option>
                            {EMPLOYMENT_STATUSES.map((es) => <option key={es} value={es}>{es}</option>)}
                        </select>
                    </FormField>
                    <FormField label="Date Hired" required error={errors.date_hired}>
                        <input type="date" value={data.date_hired} max={TODAY} onChange={(e) => setData('date_hired', e.target.value)} className="input input-bordered w-full" required />
                    </FormField>
                    <FormField label="Salary Type" required error={errors.salary_type}>
                        <select value={data.salary_type} onChange={(e) => setData('salary_type', e.target.value)} className="select select-bordered w-full" required>
                            <option value="">Select Salary Type</option>
                            {SALARY_TYPES.map((st) => <option key={st} value={st}>{st}</option>)}
                        </select>
                    </FormField>
                    <FormField label="Basic Salary (PHP)" required error={errors.basic_salary} help="Must be greater than 0">
                        <input type="number" step="0.01" min="0" value={data.basic_salary} onChange={(e) => setData('basic_salary', e.target.value)} className="input input-bordered w-full" required />
                    </FormField>
                </div>
            </div>

            <div className="mb-4">
                {sectionHeader('ph--identification-card-fill', 'Government Contributions')}
                <p className="text-xs text-faint mb-4">
                    <Icon name="ph--info-fill" className="size-3.5 inline" /> These fields are optional but must follow the correct format if provided.
                </p>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <FormField label="SSS Number" error={errors.sss_number} help={GOV_HELP.sss_number}>
                        <input type="text" value={data.sss_number} placeholder="XX-XXXXXXX-X" maxLength={GOV_MAXLENGTH.sss_number} onChange={(e) => setGov('sss_number', e.target.value)} className="input input-bordered w-full" />
                    </FormField>
                    <FormField label="PhilHealth Number" error={errors.philhealth_number} help={GOV_HELP.philhealth_number}>
                        <input type="text" value={data.philhealth_number} placeholder="XX-XXXXXXXXX-X" maxLength={GOV_MAXLENGTH.philhealth_number} onChange={(e) => setGov('philhealth_number', e.target.value)} className="input input-bordered w-full" />
                    </FormField>
                    <FormField label="Pag-IBIG Number" error={errors.pagibig_number} help={GOV_HELP.pagibig_number}>
                        <input type="text" value={data.pagibig_number} placeholder="XXXX-XXXX-XXXX" maxLength={GOV_MAXLENGTH.pagibig_number} onChange={(e) => setGov('pagibig_number', e.target.value)} className="input input-bordered w-full" />
                    </FormField>
                    <FormField label="TIN Number" error={errors.tin_number} help={GOV_HELP.tin_number}>
                        <input type="text" value={data.tin_number} placeholder="XXX-XXX-XXX" maxLength={GOV_MAXLENGTH.tin_number} onChange={(e) => setGov('tin_number', e.target.value)} className="input input-bordered w-full" />
                    </FormField>
                </div>
            </div>

            <div className="flex gap-3 flex-wrap mt-6">
                <button type="submit" className="btn btn-soft btn-error" disabled={processing}>
                    <Icon name={submitIcon} className="size-4" /> {submitLabel}
                </button>
                <a href={cancelHref} className="btn btn-soft btn-success">{cancelLabel}</a>
            </div>
        </form>
    );
}

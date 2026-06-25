{{-- Personal Information --}}
<div class="mb-8">
    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 border-b-2 border-red-200 pb-2 mb-4">
        <i class="fas fa-user text-red-600"></i> Personal Information
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">First Name <span class="text-red-600">*</span></label>
            <input type="text" name="first_name" id="first_name"
                   value="{{ old('first_name', $employee->first_name ?? '') }}"
                   class="input input-bordered w-full" data-validate="required|alpha">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('first_name') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name"
                   value="{{ old('middle_name', $employee->middle_name ?? '') }}"
                   class="input input-bordered w-full" data-validate="alpha">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Last Name <span class="text-red-600">*</span></label>
            <input type="text" name="last_name" id="last_name"
                   value="{{ old('last_name', $employee->last_name ?? '') }}"
                   class="input input-bordered w-full" data-validate="required|alpha">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('last_name') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Birthdate <span class="text-red-600">*</span></label>
            <input type="date" name="birthdate" id="birthdate"
                   value="{{ old('birthdate', isset($employee) ? $employee->birthdate->format('Y-m-d') : '') }}"
                   class="input input-bordered w-full" data-validate="required|past_date"
                   max="{{ date('Y-m-d', strtotime('-1 day')) }}">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('birthdate') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Gender <span class="text-red-600">*</span></label>
            <select name="gender" id="gender" class="select select-bordered w-full" data-validate="required">
                <option value="">Select Gender</option>
                @foreach(['Male','Female','Other'] as $g)
                    <option value="{{ $g }}" {{ old('gender', $employee->gender ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('gender') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Civil Status <span class="text-red-600">*</span></label>
            <select name="civil_status" id="civil_status" class="select select-bordered w-full" data-validate="required">
                <option value="">Select Civil Status</option>
                @foreach(['Single','Married','Widowed','Separated'] as $cs)
                    <option value="{{ $cs }}" {{ old('civil_status', $employee->civil_status ?? '') == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                @endforeach
            </select>
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('civil_status') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Contact Number <span class="text-red-600">*</span></label>
            <input type="text" name="contact_number" id="contact_number"
                   value="{{ old('contact_number', $employee->contact_number ?? '') }}"
                   placeholder="09XXXXXXXXX"
                   class="input input-bordered w-full" data-validate="required|ph_mobile" maxlength="11">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('contact_number') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Email Address <span class="text-red-600">*</span></label>
            <input type="email" name="email" id="email"
                   value="{{ old('email', $employee->email ?? '') }}"
                   class="input input-bordered w-full" data-validate="required|email">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('email') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset md:col-span-2 lg:col-span-3">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Address <span class="text-red-600">*</span></label>
            <textarea name="address" id="address" rows="2"
                      class="textarea textarea-bordered w-full" data-validate="required">{{ old('address', $employee->address ?? '') }}</textarea>
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('address') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

    </div>
</div>

{{-- Employment Details --}}
<div class="mb-8">
    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 border-b-2 border-red-200 pb-2 mb-4">
        <i class="fas fa-briefcase text-red-600"></i> Employment Details
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Department <span class="text-red-600">*</span></label>
            <select name="department" id="Department" class="select select-bordered w-full" data-validate="required">
                <option value="">Select Department</option>
                @foreach(['Sales','Marketing','Human Resources','Information Technology'] as $dept)
                    <option value="{{ $dept }}" {{ old('department', $employee->department ?? '') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('department') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Position <span class="text-red-600">*</span></label>
            <select name="position" id="position" class="select select-bordered w-full" data-validate="required">
                <option value="">Select Position</option>
                @foreach(['Manager','Supervisor','Employee'] as $pos)
                    <option value="{{ $pos }}" {{ old('position', $employee->position ?? '') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                @endforeach
            </select>
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('position') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Employment Status <span class="text-red-600">*</span></label>
            <select name="employment_status" id="employment_status" class="select select-bordered w-full" data-validate="required">
                <option value="">Select Status</option>
                @foreach(['Regular','Probationary','Contractual','Part-time'] as $es)
                    <option value="{{ $es }}" {{ old('employment_status', $employee->employment_status ?? '') == $es ? 'selected' : '' }}>{{ $es }}</option>
                @endforeach
            </select>
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('employment_status') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Date Hired <span class="text-red-600">*</span></label>
            <input type="date" name="date_hired" id="date_hired"
                   value="{{ old('date_hired', isset($employee) ? $employee->date_hired->format('Y-m-d') : '') }}"
                   class="input input-bordered w-full" data-validate="required" max="{{ date('Y-m-d') }}">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('date_hired') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Salary Type <span class="text-red-600">*</span></label>
            <select name="salary_type" id="salary_type" class="select select-bordered w-full" data-validate="required">
                <option value="">Select Salary Type</option>
                @foreach(['Monthly','Daily','Hourly'] as $st)
                    <option value="{{ $st }}" {{ old('salary_type', $employee->salary_type ?? '') == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('salary_type') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Basic Salary (PHP) <span class="text-red-600">*</span></label>
            <input type="number" name="basic_salary" id="basic_salary"
                   step="0.01" min="0"
                   value="{{ old('basic_salary', $employee->basic_salary ?? '') }}"
                   class="input input-bordered w-full" data-validate="required|min_salary">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            @error('basic_salary') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

    </div>
</div>

{{-- Government Contributions --}}
<div class="mb-4">
    <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 border-b-2 border-red-200 pb-2 mb-2">
        <i class="fas fa-id-card text-red-600"></i> Government Contributions
    </h3>
    <p class="text-xs text-gray-400 mb-4">
        <i class="fas fa-info-circle"></i> These fields are optional but must follow the correct format if provided.
    </p>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">SSS Number</label>
            <input type="text" name="sss_number" id="sss_number"
                   value="{{ old('sss_number', $employee->sss_number ?? '') }}"
                   placeholder="XX-XXXXXXX-X"
                   class="input input-bordered w-full" data-validate="sss" maxlength="12">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            <p class="text-gray-400 text-xs mt-1">Format: XX-XXXXXXX-X</p>
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">PhilHealth Number</label>
            <input type="text" name="philhealth_number" id="philhealth_number"
                   value="{{ old('philhealth_number', $employee->philhealth_number ?? '') }}"
                   placeholder="XX-XXXXXXXXX-X"
                   class="input input-bordered w-full" data-validate="philhealth" maxlength="14">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            <p class="text-gray-400 text-xs mt-1">Format: XX-XXXXXXXXX-X</p>
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">Pag-IBIG Number</label>
            <input type="text" name="pagibig_number" id="pagibig_number"
                   value="{{ old('pagibig_number', $employee->pagibig_number ?? '') }}"
                   placeholder="XXXX-XXXX-XXXX"
                   class="input input-bordered w-full" data-validate="pagibig" maxlength="14">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            <p class="text-gray-400 text-xs mt-1">Format: XXXX-XXXX-XXXX</p>
        </div>

        <div class="fieldset">
            <label class="label text-xs font-semibold uppercase tracking-wider text-gray-500">TIN Number</label>
            <input type="text" name="tin_number" id="tin_number"
                   value="{{ old('tin_number', $employee->tin_number ?? '') }}"
                   placeholder="XXX-XXX-XXX"
                   class="input input-bordered w-full" data-validate="tin" maxlength="11">
            <p class="field-error label text-red-600 text-xs mt-1 hidden"></p>
            <p class="text-gray-400 text-xs mt-1">Format: XXX-XXX-XXX</p>
        </div>

    </div>
</div>

{{-- Client-side Validation Script --}}
<script>
(function () {
    const rules = {
        required:   (v) => v.trim() !== ''                          || 'This field is required.',
        alpha:      (v) => v === '' || /^[a-zA-ZÀ-ÿ\s\-'\.]+$/.test(v.trim()) || 'Letters only.',
        email:      (v) => v === '' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) || 'Enter a valid email address.',
        ph_mobile:  (v) => v === '' || /^09\d{9}$/.test(v.trim())  || 'Must be 11 digits starting with 09.',
        past_date:  (v) => { if (!v) return true; return new Date(v) < new Date() || 'Birthdate must be in the past.'; },
        min_salary: (v) => v === '' || parseFloat(v) > 0            || 'Salary must be greater than 0.',
        sss:        (v) => v === '' || /^\d{2}-\d{7}-\d$/.test(v.trim())    || 'Format must be XX-XXXXXXX-X.',
        philhealth: (v) => v === '' || /^\d{2}-\d{9}-\d$/.test(v.trim())    || 'Format must be XX-XXXXXXXXX-X.',
        pagibig:    (v) => v === '' || /^\d{4}-\d{4}-\d{4}$/.test(v.trim()) || 'Format must be XXXX-XXXX-XXXX.',
        tin:        (v) => v === '' || /^\d{3}-\d{3}-\d{3}$/.test(v.trim()) || 'Format must be XXX-XXX-XXX.',
    };

    function autoFormat(input, type) {
        input.addEventListener('input', function () {
            let digits = this.value.replace(/\D/g, '');
            let f = '';
            if (type === 'sss')        f = digits.length > 9  ? digits.slice(0,2)+'-'+digits.slice(2,9)+'-'+digits.slice(9,10)   : digits.length > 2 ? digits.slice(0,2)+'-'+digits.slice(2) : digits;
            if (type === 'philhealth') f = digits.length > 11 ? digits.slice(0,2)+'-'+digits.slice(2,11)+'-'+digits.slice(11,12) : digits.length > 2 ? digits.slice(0,2)+'-'+digits.slice(2) : digits;
            if (type === 'pagibig')    f = digits.length > 8  ? digits.slice(0,4)+'-'+digits.slice(4,8)+'-'+digits.slice(8,12)   : digits.length > 4 ? digits.slice(0,4)+'-'+digits.slice(4) : digits;
            if (type === 'tin')        f = digits.length > 6  ? digits.slice(0,3)+'-'+digits.slice(3,6)+'-'+digits.slice(6,9)    : digits.length > 3 ? digits.slice(0,3)+'-'+digits.slice(3) : digits;
            this.value = f || digits;
        });
    }

    autoFormat(document.getElementById('sss_number'),        'sss');
    autoFormat(document.getElementById('philhealth_number'), 'philhealth');
    autoFormat(document.getElementById('pagibig_number'),    'pagibig');
    autoFormat(document.getElementById('tin_number'),        'tin');

    function validateField(field) {
        const ruleList = (field.dataset.validate || '').split('|').filter(Boolean);
        const errorEl  = field.parentElement.querySelector('.field-error');
        let message    = '';
        for (const rule of ruleList) {
            if (!rules[rule]) continue;
            const result = rules[rule](field.value);
            if (result !== true) { message = result; break; }
        }
        if (message) {
            field.classList.add('input-error'); field.classList.remove('input-success');
            if (errorEl) { errorEl.textContent = message; errorEl.classList.remove('hidden'); }
            return false;
        } else {
            field.classList.remove('input-error'); field.classList.add('input-success');
            if (errorEl) { errorEl.textContent = ''; errorEl.classList.add('hidden'); }
            return true;
        }
    }

    document.querySelectorAll('[data-validate]').forEach(function (field) {
        field.addEventListener('blur',  () => validateField(field));
        field.addEventListener('input', () => { if (field.classList.contains('input-error')) validateField(field); });
    });

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;
            document.querySelectorAll('[data-validate]').forEach(function (field) {
                if (!validateField(field)) valid = false;
            });
            if (!valid) {
                e.preventDefault();
                const firstError = [...document.querySelectorAll('[data-validate]')].find(f => f.classList.contains('input-error'));
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
})();
</script>
@php
    $input   = "width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px; transition: border-color 0.2s; box-sizing:border-box;";
    $label   = "display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;";
    $section = "font-size:16px; font-weight:700; color:#1f2937; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;";
@endphp

{{-- Personal Information --}}
<div style="margin-bottom:32px;">
    <h3 style="{{ $section }}"><i class="fas fa-user" style="color:#dc2626;"></i> Personal Information</h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

 

        <div>
            <label style="{{ $label }}">First Name <span style="color:#dc2626;">*</span></label>
            <input type="text" name="first_name" id="first_name"
                   value="{{ old('first_name', $employee->first_name ?? '') }}"
                   style="{{ $input }}"
                   data-validate="required|alpha">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('first_name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name"
                   value="{{ old('middle_name', $employee->middle_name ?? '') }}"
                   style="{{ $input }}"
                   data-validate="alpha">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
        </div>

        <div>
            <label style="{{ $label }}">Last Name <span style="color:#dc2626;">*</span></label>
            <input type="text" name="last_name" id="last_name"
                   value="{{ old('last_name', $employee->last_name ?? '') }}"
                   style="{{ $input }}"
                   data-validate="required|alpha">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('last_name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Birthdate <span style="color:#dc2626;">*</span></label>
            <input type="date" name="birthdate" id="birthdate"
                   value="{{ old('birthdate', isset($employee) ? $employee->birthdate->format('Y-m-d') : '') }}"
                   style="{{ $input }}"
                   data-validate="required|past_date"
                   max="{{ date('Y-m-d', strtotime('-1 day')) }}">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('birthdate') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Gender <span style="color:#dc2626;">*</span></label>
            <select name="gender" id="gender" style="{{ $input }}" data-validate="required">
                <option value="">Select Gender</option>
                @foreach(['Male','Female','Other'] as $g)
                    <option value="{{ $g }}" {{ old('gender', $employee->gender ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('gender') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Civil Status <span style="color:#dc2626;">*</span></label>
            <select name="civil_status" id="civil_status" style="{{ $input }}" data-validate="required">
                <option value="">Select Civil Status</option>
                @foreach(['Single','Married','Widowed','Separated'] as $cs)
                    <option value="{{ $cs }}" {{ old('civil_status', $employee->civil_status ?? '') == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                @endforeach
            </select>
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('civil_status') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Contact Number <span style="color:#dc2626;">*</span></label>
            <input type="text" name="contact_number" id="contact_number"
                   value="{{ old('contact_number', $employee->contact_number ?? '') }}"
                   placeholder="09XXXXXXXXX"
                   style="{{ $input }}"
                   data-validate="required|ph_mobile"
                   maxlength="11">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('contact_number') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Email Address <span style="color:#dc2626;">*</span></label>
            <input type="email" name="email" id="email"
                   value="{{ old('email', $employee->email ?? '') }}"
                   style="{{ $input }}"
                   data-validate="required|email">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('email') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div style="grid-column: 1 / -1;">
            <label style="{{ $label }}">Address <span style="color:#dc2626;">*</span></label>
            <textarea name="address" id="address" rows="2"
                      style="{{ $input }}"
                      data-validate="required">{{ old('address', $employee->address ?? '') }}</textarea>
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('address') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

    </div>
</div>

{{-- Employment Details --}}
<div style="margin-bottom:32px;">
    <h3 style="{{ $section }}"><i class="fas fa-briefcase" style="color:#dc2626;"></i> Employment Details</h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

        
         <div>
            <label style="{{ $label }}">Department <span style="color:#dc2626;">*</span></label>
            <select name="department" id="Department" style="{{ $input }}" data-validate="required">
                <option value="">Select Department</option>
                @foreach(['Sales','Marketing','Human Resources','Information Technology'] as $dept)
                    <option value="{{ $dept }}" {{ old('department', $employee->department ?? '') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('department') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

         <div>
            <label style="{{ $label }}">Position <span style="color:#dc2626;">*</span></label>
            <select name="position" id="position" style="{{ $input }}" data-validate="required">
                <option value="">Select Position</option>
                @foreach(['Manager','Supervisor','Employee'] as $pos)
                    <option value="{{ $pos }}" {{ old('position', $employee->position ?? '') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                @endforeach
            </select>
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('position') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Employment Status <span style="color:#dc2626;">*</span></label>
            <select name="employment_status" id="employment_status" style="{{ $input }}" data-validate="required">
                <option value="">Select Status</option>
                @foreach(['Regular','Probationary','Contractual','Part-time'] as $es)
                    <option value="{{ $es }}" {{ old('employment_status', $employee->employment_status ?? '') == $es ? 'selected' : '' }}>{{ $es }}</option>
                @endforeach
            </select>
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('employment_status') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>
        

        <div>
            <label style="{{ $label }}">Date Hired <span style="color:#dc2626;">*</span></label>
            <input type="date" name="date_hired" id="date_hired"
                   value="{{ old('date_hired', isset($employee) ? $employee->date_hired->format('Y-m-d') : '') }}"
                   style="{{ $input }}"
                   data-validate="required"
                   max="{{ date('Y-m-d') }}">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('date_hired') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Salary Type <span style="color:#dc2626;">*</span></label>
            <select name="salary_type" id="salary_type" style="{{ $input }}" data-validate="required">
                <option value="">Select Salary Type</option>
                @foreach(['Monthly','Daily','Hourly'] as $st)
                    <option value="{{ $st }}" {{ old('salary_type', $employee->salary_type ?? '') == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('salary_type') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Basic Salary (PHP) <span style="color:#dc2626;">*</span></label>
            <input type="number" name="basic_salary" id="basic_salary"
                   step="0.01" min="0"
                   value="{{ old('basic_salary', $employee->basic_salary ?? '') }}"
                   style="{{ $input }}"
                   data-validate="required|min_salary">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            @error('basic_salary') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

    </div>
</div>

{{-- Government Contributions --}}
<div>
    <h3 style="{{ $section }}"><i class="fas fa-id-card" style="color:#dc2626;"></i> Government Contributions</h3>
    <p style="font-size:12px; color:#6b7280; margin-bottom:16px; margin-top:-8px;">
        <i class="fas fa-info-circle"></i> These fields are optional but must follow the correct format if provided.
    </p>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

        <div>
            <label style="{{ $label }}">SSS Number</label>
            <input type="text" name="sss_number" id="sss_number"
                   value="{{ old('sss_number', $employee->sss_number ?? '') }}"
                   placeholder="XX-XXXXXXX-X"
                   style="{{ $input }}"
                   data-validate="sss"
                   maxlength="12">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Format: XX-XXXXXXX-X</p>
        </div>

        <div>
            <label style="{{ $label }}">PhilHealth Number</label>
            <input type="text" name="philhealth_number" id="philhealth_number"
                   value="{{ old('philhealth_number', $employee->philhealth_number ?? '') }}"
                   placeholder="XX-XXXXXXXXX-X"
                   style="{{ $input }}"
                   data-validate="philhealth"
                   maxlength="14">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Format: XX-XXXXXXXXX-X</p>
        </div>

        <div>
            <label style="{{ $label }}">Pag-IBIG Number</label>
            <input type="text" name="pagibig_number" id="pagibig_number"
                   value="{{ old('pagibig_number', $employee->pagibig_number ?? '') }}"
                   placeholder="XXXX-XXXX-XXXX"
                   style="{{ $input }}"
                   data-validate="pagibig"
                   maxlength="14">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Format: XXXX-XXXX-XXXX</p>
        </div>

        <div>
            <label style="{{ $label }}">TIN Number</label>
            <input type="text" name="tin_number" id="tin_number"
                   value="{{ old('tin_number', $employee->tin_number ?? '') }}"
                   placeholder="XXX-XXX-XXX"
                   style="{{ $input }}"
                   data-validate="tin"
                   maxlength="11">
            <p class="field-error" style="color:#dc2626; font-size:12px; margin-top:4px; display:none;"></p>
            <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Format: XXX-XXX-XXX</p>
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
        ph_mobile:  (v) => v === '' || /^09\d{9}$/.test(v.trim())  || 'Must be 11 digits starting with 09 (e.g. 09XXXXXXXXX).',
        past_date:  (v) => { if (!v) return true; return new Date(v) < new Date() || 'Birthdate must be in the past.'; },
        min_salary: (v) => v === '' || parseFloat(v) > 0            || 'Salary must be greater than 0.',
        sss:        (v) => v === '' || /^\d{2}-\d{7}-\d$/.test(v.trim())       || 'Format must be XX-XXXXXXX-X.',
        philhealth: (v) => v === '' || /^\d{2}-\d{9}-\d$/.test(v.trim())       || 'Format must be XX-XXXXXXXXX-X.',
        pagibig:    (v) => v === '' || /^\d{4}-\d{4}-\d{4}$/.test(v.trim())    || 'Format must be XXXX-XXXX-XXXX.',
        tin:        (v) => v === '' || /^\d{3}-\d{3}-\d{3}$/.test(v.trim())    || 'Format must be XXX-XXX-XXX.',
    };

    function autoFormat(input, type) {
        input.addEventListener('input', function () {
            let digits = this.value.replace(/\D/g, '');
            let formatted = '';
            if (type === 'sss') {
                if (digits.length > 2)  formatted = digits.slice(0,2) + '-' + digits.slice(2);
                else                    formatted = digits;
                if (digits.length > 9)  formatted = digits.slice(0,2) + '-' + digits.slice(2,9) + '-' + digits.slice(9,10);
            } else if (type === 'philhealth') {
                if (digits.length > 2)  formatted = digits.slice(0,2) + '-' + digits.slice(2);
                else                    formatted = digits;
                if (digits.length > 11) formatted = digits.slice(0,2) + '-' + digits.slice(2,11) + '-' + digits.slice(11,12);
            } else if (type === 'pagibig') {
                if (digits.length > 4)  formatted = digits.slice(0,4) + '-' + digits.slice(4);
                else                    formatted = digits;
                if (digits.length > 8)  formatted = digits.slice(0,4) + '-' + digits.slice(4,8) + '-' + digits.slice(8,12);
            } else if (type === 'tin') {
                if (digits.length > 3)  formatted = digits.slice(0,3) + '-' + digits.slice(3);
                else                    formatted = digits;
                if (digits.length > 6)  formatted = digits.slice(0,3) + '-' + digits.slice(3,6) + '-' + digits.slice(6,9);
            }
            this.value = formatted || digits;
        });
    }

    autoFormat(document.getElementById('sss_number'),        'sss');
    autoFormat(document.getElementById('philhealth_number'), 'philhealth');
    autoFormat(document.getElementById('pagibig_number'),    'pagibig');
    autoFormat(document.getElementById('tin_number'),        'tin');

    function validateField(field) {
        const ruleList  = (field.dataset.validate || '').split('|').filter(Boolean);
        const errorEl   = field.parentElement.querySelector('.field-error');
        const value     = field.value;
        let   message   = '';

        for (const rule of ruleList) {
            if (!rules[rule]) continue;
            const result = rules[rule](value);
            if (result !== true) { message = result; break; }
        }

        if (message) {
            field.style.borderColor = '#dc2626';
            if (errorEl) { errorEl.textContent = message; errorEl.style.display = 'block'; }
            return false;
        } else {
            field.style.borderColor = '#10b981';
            if (errorEl) { errorEl.textContent = ''; errorEl.style.display = 'none'; }
            return true;
        }
    }

    document.querySelectorAll('[data-validate]').forEach(function (field) {
        field.addEventListener('blur',  () => validateField(field));
        field.addEventListener('input', () => {
            if (field.style.borderColor === 'rgb(220, 38, 38)') validateField(field);
        });
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
                const firstError = [...document.querySelectorAll('[data-validate]')]
                    .find(f => f.style.borderColor === 'rgb(220, 38, 38)');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

})();
</script>
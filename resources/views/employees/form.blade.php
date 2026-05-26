@php
    $input  = "width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;";
    $label  = "display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;";
    $section = "font-size:16px; font-weight:700; color:#1f2937; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;";
@endphp

{{-- Personal Information --}}
<div style="margin-bottom:32px;">
    <h3 style="{{ $section }}"><i class="fas fa-user" style="color:#dc2626;"></i> Personal Information</h3>
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">

        <div>
            <label style="{{ $label }}">Employee ID <span style="color:#dc2626;">*</span></label>
            <input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id ?? '') }}" style="{{ $input }} {{ $errors->has('employee_id') ? 'border-color:#dc2626;' : '' }}">
            @error('employee_id') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">First Name <span style="color:#dc2626;">*</span></label>
            <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name ?? '') }}" style="{{ $input }}">
            @error('first_name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Middle Name</label>
            <input type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name ?? '') }}" style="{{ $input }}">
        </div>

        <div>
            <label style="{{ $label }}">Last Name <span style="color:#dc2626;">*</span></label>
            <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name ?? '') }}" style="{{ $input }}">
            @error('last_name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Birthdate <span style="color:#dc2626;">*</span></label>
            <input type="date" name="birthdate" value="{{ old('birthdate', isset($employee) ? $employee->birthdate->format('Y-m-d') : '') }}" style="{{ $input }}">
            @error('birthdate') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Gender <span style="color:#dc2626;">*</span></label>
            <select name="gender" style="{{ $input }}">
                <option value="">Select Gender</option>
                @foreach(['Male','Female','Other'] as $g)
                    <option value="{{ $g }}" {{ old('gender', $employee->gender ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            @error('gender') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Civil Status <span style="color:#dc2626;">*</span></label>
            <select name="civil_status" style="{{ $input }}">
                <option value="">Select Civil Status</option>
                @foreach(['Single','Married','Widowed','Separated'] as $cs)
                    <option value="{{ $cs }}" {{ old('civil_status', $employee->civil_status ?? '') == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                @endforeach
            </select>
            @error('civil_status') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Contact Number <span style="color:#dc2626;">*</span></label>
            <input type="text" name="contact_number" value="{{ old('contact_number', $employee->contact_number ?? '') }}" style="{{ $input }}">
            @error('contact_number') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Email Address <span style="color:#dc2626;">*</span></label>
            <input type="email" name="email" value="{{ old('email', $employee->email ?? '') }}" style="{{ $input }}">
            @error('email') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div style="grid-column:span 3;">
            <label style="{{ $label }}">Address <span style="color:#dc2626;">*</span></label>
            <textarea name="address" rows="2" style="{{ $input }}">{{ old('address', $employee->address ?? '') }}</textarea>
            @error('address') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

    </div>
</div>

{{-- Employment Details --}}
<div style="margin-bottom:32px;">
    <h3 style="{{ $section }}"><i class="fas fa-briefcase" style="color:#dc2626;"></i> Employment Details</h3>
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">

        <div>
            <label style="{{ $label }}">Department <span style="color:#dc2626;">*</span></label>
            <input type="text" name="department" value="{{ old('department', $employee->department ?? '') }}" style="{{ $input }}">
            @error('department') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Position <span style="color:#dc2626;">*</span></label>
            <input type="text" name="position" value="{{ old('position', $employee->position ?? '') }}" style="{{ $input }}">
            @error('position') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Employment Status <span style="color:#dc2626;">*</span></label>
            <select name="employment_status" style="{{ $input }}">
                <option value="">Select Status</option>
                @foreach(['Regular','Probationary','Contractual','Part-time'] as $es)
                    <option value="{{ $es }}" {{ old('employment_status', $employee->employment_status ?? '') == $es ? 'selected' : '' }}>{{ $es }}</option>
                @endforeach
            </select>
            @error('employment_status') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Date Hired <span style="color:#dc2626;">*</span></label>
            <input type="date" name="date_hired" value="{{ old('date_hired', isset($employee) ? $employee->date_hired->format('Y-m-d') : '') }}" style="{{ $input }}">
            @error('date_hired') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Salary Type <span style="color:#dc2626;">*</span></label>
            <select name="salary_type" style="{{ $input }}">
                <option value="">Select Salary Type</option>
                @foreach(['Monthly','Daily','Hourly'] as $st)
                    <option value="{{ $st }}" {{ old('salary_type', $employee->salary_type ?? '') == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
            @error('salary_type') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="{{ $label }}">Basic Salary (PHP) <span style="color:#dc2626;">*</span></label>
            <input type="number" name="basic_salary" step="0.01" min="0" value="{{ old('basic_salary', $employee->basic_salary ?? '') }}" style="{{ $input }}">
            @error('basic_salary') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
        </div>

    </div>
</div>

{{-- Government Contributions --}}
<div>
    <h3 style="{{ $section }}"><i class="fas fa-id-card" style="color:#dc2626;"></i> Government Contributions</h3>
    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px;">

        <div>
            <label style="{{ $label }}">SSS Number</label>
            <input type="text" name="sss_number" value="{{ old('sss_number', $employee->sss_number ?? '') }}" placeholder="XX-XXXXXXX-X" style="{{ $input }}">
        </div>

        <div>
            <label style="{{ $label }}">PhilHealth Number</label>
            <input type="text" name="philhealth_number" value="{{ old('philhealth_number', $employee->philhealth_number ?? '') }}" placeholder="XX-XXXXXXXXX-X" style="{{ $input }}">
        </div>

        <div>
            <label style="{{ $label }}">Pag-IBIG Number</label>
            <input type="text" name="pagibig_number" value="{{ old('pagibig_number', $employee->pagibig_number ?? '') }}" placeholder="XXXX-XXXX-XXXX" style="{{ $input }}">
        </div>

        <div>
            <label style="{{ $label }}">TIN Number</label>
            <input type="text" name="tin_number" value="{{ old('tin_number', $employee->tin_number ?? '') }}" placeholder="XXX-XXX-XXX" style="{{ $input }}">
        </div>

    </div>
</div>
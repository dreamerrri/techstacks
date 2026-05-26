{{-- resources/views/employees/form.blade.php --}}
{{-- Shared form for create and edit --}}

@php $isEdit = isset($employee); @endphp

<div class="space-y-8">

    {{-- Personal Information --}}
    <section>
        <h2 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Personal Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID <span class="text-red-500">*</span></label>
                <input type="text" name="employee_id"
                       value="{{ old('employee_id', $employee->employee_id ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('employee_id') border-red-500 @enderror">
                @error('employee_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                <input type="text" name="first_name"
                       value="{{ old('first_name', $employee->first_name ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('first_name') border-red-500 @enderror">
                @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                <input type="text" name="middle_name"
                       value="{{ old('middle_name', $employee->middle_name ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                <input type="text" name="last_name"
                       value="{{ old('last_name', $employee->last_name ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('last_name') border-red-500 @enderror">
                @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Birthdate <span class="text-red-500">*</span></label>
                <input type="date" name="birthdate"
                       value="{{ old('birthdate', isset($employee) ? $employee->birthdate->format('Y-m-d') : '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('birthdate') border-red-500 @enderror">
                @error('birthdate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                <select name="gender" class="w-full border rounded px-3 py-2 text-sm @error('gender') border-red-500 @enderror">
                    <option value="">Select Gender</option>
                    @foreach(['Male','Female','Other'] as $g)
                        <option value="{{ $g }}" {{ old('gender', $employee->gender ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
                @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Civil Status <span class="text-red-500">*</span></label>
                <select name="civil_status" class="w-full border rounded px-3 py-2 text-sm @error('civil_status') border-red-500 @enderror">
                    <option value="">Select Civil Status</option>
                    @foreach(['Single','Married','Widowed','Separated'] as $cs)
                        <option value="{{ $cs }}" {{ old('civil_status', $employee->civil_status ?? '') == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                    @endforeach
                </select>
                @error('civil_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number <span class="text-red-500">*</span></label>
                <input type="text" name="contact_number"
                       value="{{ old('contact_number', $employee->contact_number ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('contact_number') border-red-500 @enderror">
                @error('contact_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email"
                       value="{{ old('email', $employee->email ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('email') border-red-500 @enderror">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                <textarea name="address" rows="2"
                          class="w-full border rounded px-3 py-2 text-sm @error('address') border-red-500 @enderror">{{ old('address', $employee->address ?? '') }}</textarea>
                @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

        </div>
    </section>

    {{-- Employment Details --}}
    <section>
        <h2 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Employment Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                <input type="text" name="department"
                       value="{{ old('department', $employee->department ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('department') border-red-500 @enderror">
                @error('department') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Position <span class="text-red-500">*</span></label>
                <input type="text" name="position"
                       value="{{ old('position', $employee->position ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('position') border-red-500 @enderror">
                @error('position') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status <span class="text-red-500">*</span></label>
                <select name="employment_status" class="w-full border rounded px-3 py-2 text-sm @error('employment_status') border-red-500 @enderror">
                    <option value="">Select Status</option>
                    @foreach(['Regular','Probationary','Contractual','Part-time'] as $es)
                        <option value="{{ $es }}" {{ old('employment_status', $employee->employment_status ?? '') == $es ? 'selected' : '' }}>{{ $es }}</option>
                    @endforeach
                </select>
                @error('employment_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Hired <span class="text-red-500">*</span></label>
                <input type="date" name="date_hired"
                       value="{{ old('date_hired', isset($employee) ? $employee->date_hired->format('Y-m-d') : '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('date_hired') border-red-500 @enderror">
                @error('date_hired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Salary Type <span class="text-red-500">*</span></label>
                <select name="salary_type" class="w-full border rounded px-3 py-2 text-sm @error('salary_type') border-red-500 @enderror">
                    <option value="">Select Salary Type</option>
                    @foreach(['Monthly','Daily','Hourly'] as $st)
                        <option value="{{ $st }}" {{ old('salary_type', $employee->salary_type ?? '') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
                @error('salary_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary (PHP) <span class="text-red-500">*</span></label>
                <input type="number" name="basic_salary" step="0.01" min="0"
                       value="{{ old('basic_salary', $employee->basic_salary ?? '') }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('basic_salary') border-red-500 @enderror">
                @error('basic_salary') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

        </div>
    </section>

    {{-- Government Contributions --}}
    <section>
        <h2 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Government Contributions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SSS Number</label>
                <input type="text" name="sss_number"
                       value="{{ old('sss_number', $employee->sss_number ?? '') }}"
                       placeholder="XX-XXXXXXX-X"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PhilHealth Number</label>
                <input type="text" name="philhealth_number"
                       value="{{ old('philhealth_number', $employee->philhealth_number ?? '') }}"
                       placeholder="XX-XXXXXXXXX-X"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pag-IBIG Number</label>
                <input type="text" name="pagibig_number"
                       value="{{ old('pagibig_number', $employee->pagibig_number ?? '') }}"
                       placeholder="XXXX-XXXX-XXXX"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">TIN Number</label>
                <input type="text" name="tin_number"
                       value="{{ old('tin_number', $employee->tin_number ?? '') }}"
                       placeholder="XXX-XXX-XXX"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

        </div>
    </section>

</div>
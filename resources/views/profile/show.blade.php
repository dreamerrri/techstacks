@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

@php
use Illuminate\Support\Facades\Storage;
    $user     = auth()->user();
    $isAdmin  = $user->isAdmin();
    $isHR     = $user->isHR();
    $employee = $user->employee;
    $roleClass = match(true) {
        $isAdmin => 'badge-soft badge-error',
        $isHR    => 'badge-soft badge-info',
        default  => 'badge-soft badge-success',
    };
@endphp

<div class="card bg-base-100 shadow-lg w-full mt-16 mb-6">
    <div class="px-6">
        <div class="flex flex-wrap justify-center">
            <div class="w-full flex justify-center -mt-16">
                <x-avatar-upload
                    size="w-32 h-32"
                    :photo-url="$user->profile_photo ? Storage::disk('s3')->temporaryUrl($user->profile_photo, now()->addHours(24)) : null"
                    :initials="strtoupper(substr($user->name, 0, 1))"
                    upload-route="{{ route('profile.photo') }}"
                />
            </div>
        </div>

        <div class="text-center mt-2 pb-6">
            <h3 class="text-2xl text-base-content font-bold leading-normal mb-1">{{ $user->name }}</h3>
            <p class="text-gray-500 text-sm m-0 mb-2">{{ $user->email }}</p>
            <div class="text-xs mt-0 mb-2 text-base-content/60 font-bold uppercase flex items-center justify-center gap-1">
                <span class="badge {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
                @if($employee)
                    <span class="badge badge-soft badge-neutral">{{ $employee->position }} — {{ $employee->department }}</span>
                @endif
            </div>
        </div>
    </div>

    <x-tabs :tabs="[
        ['id' => 'account',  'label' => 'Account Info'],
        ['id' => 'gov',      'label' => 'Government Contributions'],
        ['id' => 'settings', 'label' => 'Settings'],
    ]" />

    <div class="mt-3 px-6 pb-6">

        {{-- Tab 1: Account Info --}}
        <x-tab-panel id="account" :first="true">
            @if($employee)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-panel class="md:col-span-2">
                        <x-panel-header icon="icon-[ph--identification-card-fill]" color="text-blue-600" bg="bg-blue-100">
                            Personal Information
                        </x-panel-header>
                        <form method="POST" action="{{ route('profile.personal') }}">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-gray-600">First Name</label>
                                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}"
                                           class="input input-bordered w-full" required>
                                    @error('first_name') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-gray-600">Middle Name</label>
                                    <input type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}"
                                           class="input input-bordered w-full">
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-gray-600">Last Name</label>
                                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                                           class="input input-bordered w-full" required>
                                    @error('last_name') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-gray-600">Birthdate</label>
                                    <input type="date" name="birthdate" value="{{ old('birthdate', $employee->birthdate->format('Y-m-d')) }}"
                                           class="input input-bordered w-full" required max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                                    @error('birthdate') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-gray-600">Gender</label>
                                    <select name="gender" class="select select-bordered w-full" required>
                                        @foreach(['Male','Female','Other'] as $g)
                                            <option value="{{ $g }}" {{ old('gender', $employee->gender) == $g ? 'selected' : '' }}>{{ $g }}</option>
                                        @endforeach
                                    </select>
                                    @error('gender') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-gray-600">Civil Status</label>
                                    <select name="civil_status" class="select select-bordered w-full" required>
                                        @foreach(['Single','Married','Widowed','Separated'] as $cs)
                                            <option value="{{ $cs }}" {{ old('civil_status', $employee->civil_status) == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                                        @endforeach
                                    </select>
                                    @error('civil_status') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-gray-600">Contact Number</label>
                                    <input type="text" name="contact_number" value="{{ old('contact_number', $employee->contact_number) }}"
                                           placeholder="09XXXXXXXXX" maxlength="11" class="input input-bordered w-full">
                                    @error('contact_number') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset md:col-span-2 lg:col-span-3">
                                    <label class="label text-xs font-semibold text-gray-600">Address</label>
                                    <textarea name="address" rows="2" class="textarea textarea-bordered w-full">{{ old('address', $employee->address) }}</textarea>
                                    @error('address') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-soft btn-info">
                                <i class="icon-[ph--floppy-disk-fill]"></i> Save Personal Info
                            </button>
                        </form>
                    </x-panel>

                    <x-panel padding="p-5">
                        <x-panel-header icon="icon-[ph--briefcase-fill]" color="text-emerald-600" bg="bg-emerald-100">
                            Employment Information
                        </x-panel-header>
                        <div class="flex flex-col text-sm">
                            <x-detail-row label="Department">{{ $employee->department }}</x-detail-row>
                            <x-detail-row label="Position">{{ $employee->position }}</x-detail-row>
                            <x-detail-row label="Employment Status">{{ $employee->employment_status }}</x-detail-row>
                            <x-detail-row label="Date Hired">{{ $employee->date_hired->format('M d, Y') }}</x-detail-row>
                            <x-detail-row label="Salary Type" :border="false">{{ $employee->salary_type }}</x-detail-row>
                        </div>
                        <div class="mt-3 px-3 py-2 bg-gray-50 rounded-lg text-xs text-gray-400">
                            <i class="icon-[ph--info-fill]"></i> Employment details can only be changed by HR.
                        </div>
                    </x-panel>

                    <x-panel padding="p-5">
                        <x-panel-header icon="icon-[ph--shield-check-fill]">
                            Account Info
                        </x-panel-header>
                        <div class="flex flex-col text-sm">
                            <x-detail-row label="Role">
                                <span class="badge {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
                            </x-detail-row>
                            <x-detail-row label="Account Status">
                                <span class="badge badge-soft badge-success">Active</span>
                            </x-detail-row>
                            <x-detail-row label="Member Since">{{ $user->created_at->format('M d, Y') }}</x-detail-row>
                            <x-detail-row label="Last Login" :border="false">
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : '—' }}
                            </x-detail-row>
                        </div>
                    </x-panel>

                </div>
            @else
                <x-panel padding="p-5">
                    <x-panel-header icon="icon-[ph--shield-check-fill]">
                        Account Info
                    </x-panel-header>
                    <div class="flex flex-col text-sm">
                        <x-detail-row label="Role">
                            <span class="badge {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
                        </x-detail-row>
                        <x-detail-row label="Account Status">
                            <span class="badge badge-soft badge-success">Active</span>
                        </x-detail-row>
                        <x-detail-row label="Member Since">{{ $user->created_at->format('M d, Y') }}</x-detail-row>
                        <x-detail-row label="Last Login" :border="false">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : '—' }}
                        </x-detail-row>
                    </div>
                </x-panel>
            @endif
        </x-tab-panel>

        {{-- Tab 2: Government Contributions --}}
        <x-tab-panel id="gov">
            @if($employee)
                <x-panel padding="p-5">
                    <x-panel-header icon="icon-[ph--bank-fill]" color="text-red-600" bg="bg-red-100">
                        Government IDs
                    </x-panel-header>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        @foreach([
                            ['SSS Number',        $employee->sss_number,        'icon-[ph--shield-check-fill]', 'text-emerald-600', 'bg-emerald-100'],
                            ['PhilHealth Number', $employee->philhealth_number,  'icon-[ph--heart-fill]',        'text-blue-600',    'bg-blue-100'],
                            ['Pag-IBIG Number',   $employee->pagibig_number,     'icon-[ph--house-fill]',        'text-amber-500',   'bg-amber-100'],
                            ['TIN Number',        $employee->tin_number,         'icon-[ph--receipt-fill]',      'text-violet-600',  'bg-violet-100'],
                        ] as [$label, $value, $icon, $color, $bg])
                            <x-panel padding="p-4" class="text-center">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 {{ $color }} {{ $bg }}">
                                    <i class="{{ $icon }} size-5"></i>
                                </div>
                                <div class="text-xs text-gray-400 uppercase tracking-widest font-medium mb-1">{{ $label }}</div>
                                <div class="font-bold font-mono text-gray-800 text-xs break-all">{{ $value ?? '—' }}</div>
                            </x-panel>
                        @endforeach
                    </div>
                </x-panel>
            @else
                <x-panel padding="p-6" class="text-center text-sm text-base-content/60">
                    No employee record is linked to this account yet, so government contribution details aren't available.
                </x-panel>
            @endif
        </x-tab-panel>

        {{-- Tab 3: Settings --}}
        <x-tab-panel id="settings">
            <x-panel>
                <x-panel-header icon="icon-[ph--user-gear-fill]" color="text-red-600" bg="bg-red-100">
                    Account Settings
                </x-panel-header>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-gray-600">Display Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                   class="input input-bordered w-full" required>
                            @error('name') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-gray-600">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="input input-bordered w-full" required>
                            @error('email') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">
                        Change Password <span class="normal-case font-normal">(leave blank to keep current)</span>
                    </div>
                    <div class="border-t border-gray-200 mb-4"></div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-gray-600">Current Password</label>
                            <input type="password" name="current_password"
                                   class="input input-bordered w-full" placeholder="••••••••">
                            @error('current_password') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-gray-600">New Password</label>
                            <input type="password" name="password"
                                   class="input input-bordered w-full" placeholder="••••••••">
                            @error('password') <p class="label text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-gray-600">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                   class="input input-bordered w-full" placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-soft btn-error">
                        <i class="icon-[ph--floppy-disk-fill]"></i> Save Changes
                    </button>
                </form>
            </x-panel>
        </x-tab-panel>

    </div>
</div>

@endsection
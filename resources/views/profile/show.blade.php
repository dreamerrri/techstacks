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
<div class="card bg-base-100 border border-base-300 p-6">
<div class=" w-full mt-16 mb-6">
    <div class="px-6">
        <div class="flex flex-wrap justify-center">
            <div class="w-full flex justify-center">
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
            <p class="text-subtle text-sm m-0 mb-2">{{ $user->email }}</p>
            <div class="text-xs mt-0 mb-2 text-subtle font-bold uppercase flex items-center justify-center gap-1">
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
                        <x-panel-header icon="icon-[ph--identification-card-fill]" color="text-info" bg="bg-info/10">
                            Personal Information
                        </x-panel-header>
                        <form method="POST" action="{{ route('profile.personal') }}">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-muted">First Name</label>
                                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}"
                                           class="input input-bordered w-full" required>
                                    @error('first_name') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-muted">Middle Name</label>
                                    <input type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}"
                                           class="input input-bordered w-full">
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-muted">Last Name</label>
                                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                                           class="input input-bordered w-full" required>
                                    @error('last_name') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-muted">Birthdate</label>
                                    <input type="date" name="birthdate" value="{{ old('birthdate', $employee->birthdate->format('Y-m-d')) }}"
                                           class="input input-bordered w-full" required max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                                    @error('birthdate') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-muted">Gender</label>
                                    <select name="gender" class="select select-bordered w-full" required>
                                        @foreach(['Male','Female','Other'] as $g)
                                            <option value="{{ $g }}" {{ old('gender', $employee->gender) == $g ? 'selected' : '' }}>{{ $g }}</option>
                                        @endforeach
                                    </select>
                                    @error('gender') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-muted">Civil Status</label>
                                    <select name="civil_status" class="select select-bordered w-full" required>
                                        @foreach(['Single','Married','Widowed','Separated'] as $cs)
                                            <option value="{{ $cs }}" {{ old('civil_status', $employee->civil_status) == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                                        @endforeach
                                    </select>
                                    @error('civil_status') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset">
                                    <label class="label text-xs font-semibold text-muted">Contact Number</label>
                                    <input type="text" name="contact_number" value="{{ old('contact_number', $employee->contact_number) }}"
                                           placeholder="09XXXXXXXXX" maxlength="11" class="input input-bordered w-full">
                                    @error('contact_number') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="fieldset md:col-span-2 lg:col-span-3">
                                    <label class="label text-xs font-semibold text-muted">Address</label>
                                    <textarea name="address" rows="2" class="textarea textarea-bordered w-full">{{ old('address', $employee->address) }}</textarea>
                                    @error('address') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn  btn-info">
                                <i class="icon-[ph--floppy-disk-fill]"></i> Save Personal Info
                            </button>
                        </form>
                    </x-panel>

                    <x-panel padding="p-5">
                        <x-panel-header icon="icon-[ph--briefcase-fill]" color="text-success" bg="bg-success/10">
                            Employment Information
                        </x-panel-header>
                        <div class="flex flex-col text-sm">
                            <x-detail-row label="Department">{{ $employee->department }}</x-detail-row>
                            <x-detail-row label="Position">{{ $employee->position }}</x-detail-row>
                            <x-detail-row label="Employment Status">{{ $employee->employment_status }}</x-detail-row>
                            <x-detail-row label="Date Hired">{{ $employee->date_hired->format('M d, Y') }}</x-detail-row>
                            <x-detail-row label="Salary Type" :border="false">{{ $employee->salary_type }}</x-detail-row>
                        </div>
                        <div class="mt-3 px-3 py-2 bg-base-200 rounded-lg text-xs text-faint">
                            <i class="icon-[ph--info-fill]"></i> Employment details can only be changed by HR.
                        </div>
                    </x-panel>

                    <x-panel padding="p-5">
                        <x-panel-header icon="icon-[tabler--shield-check]">
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
                    <x-panel-header icon="icon-[tabler--shield-check]">
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
                    <x-panel-header icon="icon-[ph--bank-fill]" color="text-error" bg="bg-error/10">
                        Government IDs
                    </x-panel-header>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        @foreach([
                            ['SSS Number',        $employee->sss_number,        'icon-[tabler--shield-check]', 'text-success', 'bg-success/10'],
                            ['PhilHealth Number', $employee->philhealth_number,  'icon-[ph--heart-fill]',        'text-info',    'bg-info/10'],
                            ['Pag-IBIG Number',   $employee->pagibig_number,     'icon-[ph--house-fill]',        'text-notification',   'bg-notification/10'],
                            ['TIN Number',        $employee->tin_number,         'icon-[ph--receipt-fill]',      'text-secondary',  'bg-secondary/10'],
                        ] as [$label, $value, $icon, $color, $bg])
                            <x-panel padding="p-4" class="text-center">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 {{ $color }} {{ $bg }}">
                                    <i class="{{ $icon }} size-5"></i>
                                </div>
                                <div class="text-xs text-faint uppercase tracking-widest font-medium mb-1">{{ $label }}</div>
                                <div class="font-bold font-mono text-base-content text-xs break-all">{{ $value ?? '—' }}</div>
                            </x-panel>
                        @endforeach
                    </div>
                </x-panel>
            @else
                <x-panel padding="p-6" class="text-center text-sm text-subtle">
                    No employee record is linked to this account yet, so government contribution details aren't available.
                </x-panel>
            @endif
        </x-tab-panel>

        {{-- Tab 3: Settings --}}
        <x-tab-panel id="settings">
            <x-panel>
                <x-panel-header icon="icon-[ph--user-gear-fill]" color="text-error" bg="bg-error/10">
                    Account Settings
                </x-panel-header>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-muted">Display Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                   class="input input-bordered w-full" required>
                            @error('name') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-muted">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="input input-bordered w-full" required>
                            @error('email') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="text-xs font-semibold text-faint uppercase tracking-widest mb-2">
                        Change Password <span class="normal-case font-normal">(leave blank to keep current)</span>
                    </div>
                    <div class="border-t border-base-300 mb-4"></div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-muted">Current Password</label>
                            <input type="password" name="current_password"
                                   class="input input-bordered w-full" placeholder="••••••••">
                            @error('current_password') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-muted">New Password</label>
                            <input type="password" name="password"
                                   class="input input-bordered w-full" placeholder="••••••••">
                            @error('password') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="fieldset">
                            <label class="label text-xs font-semibold text-muted">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                   class="input input-bordered w-full" placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" class="btn  btn-error">
                        <i class="icon-[ph--floppy-disk-fill]"></i> Save Changes
                    </button>
                </form>
            </x-panel>
                    {{-- New: Appearance / Theme panel --}}
<x-panel>
    <x-panel-header icon="icon-[ph--palette-fill]" color="text-accent" bg="bg-accent/10">
        Appearance
    </x-panel-header>

    <p class="text-xs text-faint mb-4">
        Pick a theme — it applies instantly and is saved to your account.
    </p>

    @php
        $themes = ['techstacks', 'techstacks-light', 'light','dark','black','claude','corporate','ghibli','gourmet','luxury','mintlify','pastel','perplexity','shadcn','slack','soft','spotify','valorant','vscode'];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" id="theme-picker">
        @foreach($themes as $theme)
            <label class="cursor-pointer border rounded-field p-3 flex items-center gap-2 has-checked:border-primary has-checked:ring-2"
                   data-theme="{{ $theme }}">
                <input type="radio"
                       name="theme"
                       value="{{ $theme }}"
                       class="theme-controller radio radio-sm"
                       data-theme-select
                       {{ $user->theme === $theme ? 'checked' : '' }}>
                <span class="capitalize text-sm">{{ $theme }}</span>
            </label>
        @endforeach
    </div>
</x-panel>
        </x-tab-panel>



</div>
</div>
</div>
<script>
document.querySelectorAll('#theme-picker input[data-theme-select]').forEach(input => {
    input.addEventListener('change', (e) => {
        const theme = e.target.value;
        document.documentElement.setAttribute('data-theme', theme);
        fetch('{{ route('settings.theme') }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ theme })
        })
        .then(res => {
            console.log('theme save response:', res.status);
            if (!res.ok) return res.text().then(t => console.error(t));
        })
        .catch(err => console.error('theme save failed:', err));
    });
});
</script>

@endsection
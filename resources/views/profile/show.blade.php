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

{{-- Banner --}}
<div class="card bg-base-100 shadow-sm p-5 mb-5 flex items-center gap-5 flex-wrap">
    <div class="flex items-center gap-5 flex-wrap flex-1">

        {{-- Avatar --}}
        <div class="relative flex-shrink-0">
            <div id="avatar-circle"
                 class="w-20 h-20 rounded-full bg-gray-100 border-2 border-gray-200 overflow-hidden flex items-center justify-center cursor-pointer text-3xl font-bold text-gray-500">
                @if($user->profile_photo)
                    <img id="avatar-img"
                         src="{{ Storage::disk('s3')->temporaryUrl($user->profile_photo, now()->addHours(24)) }}"
                         alt="{{ $user->name }}"
                         class="w-full h-full object-cover">
                @else
                    <span id="avatar-initials">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @endif
            </div>
            <label for="photo-file-input"
                   class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-white border-2 border-gray-200 flex items-center justify-center cursor-pointer shadow-sm">
                <i class="fas fa-camera text-[10px] text-gray-700"></i>
            </label>
            <input type="file" id="photo-file-input" accept="image/*" class="hidden">
        </div>

        {{-- Name / email / badges --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800 m-0 mb-1">{{ $user->name }}</h1>
            <p class="text-gray-500 text-sm m-0 mb-2">{{ $user->email }}</p>
            <div class="flex gap-2 flex-wrap">
                <span class="badge {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
                @if($employee)
                    <span class="badge badge-soft badge-neutral">{{ $employee->position }} — {{ $employee->department }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Hidden photo upload form --}}
<form id="photo-upload-form" method="POST"
      action="{{ route('profile.photo') }}"
      enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="file" name="photo" id="photo-form-file">
</form>

{{-- Body Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Account Settings --}}
    <div class="card bg-base-100 shadow-sm p-6 md:col-span-2">
        <h2 class="text-sm font-bold text-gray-800 mb-5 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-red-100 flex items-center justify-center text-red-600 text-xs flex-shrink-0">
                <i class="fas fa-user-cog"></i>
            </span>
            Account Settings
        </h2>
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
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>

    @if($employee)

    {{-- Personal Information --}}
    <div class="card bg-base-100 shadow-sm p-6 md:col-span-2">
        <h2 class="text-sm font-bold text-gray-800 mb-5 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-blue-100 flex items-center justify-center text-blue-600 text-xs flex-shrink-0">
                <i class="fas fa-id-card"></i>
            </span>
            Personal Information
        </h2>
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
                <i class="fas fa-save"></i> Save Personal Info
            </button>
        </form>
    </div>

    {{-- Employment Information --}}
    <div class="card bg-base-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-emerald-100 flex items-center justify-center text-emerald-600 text-xs flex-shrink-0">
                <i class="fas fa-briefcase"></i>
            </span>
            Employment Information
        </h2>
        <div class="flex flex-col text-sm">
            @foreach([
                ['Department',        $employee->department],
                ['Position',          $employee->position],
                ['Employment Status', $employee->employment_status],
                ['Date Hired',        $employee->date_hired->format('M d, Y')],
                ['Salary Type',       $employee->salary_type],
            ] as [$label, $value])
                <div class="flex justify-between items-center py-2.5 border-b border-gray-100">
                    <span class="text-gray-400">{{ $label }}</span>
                    <span class="font-medium text-gray-800">{{ $value ?? '—' }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-3 px-3 py-2 bg-gray-50 rounded-lg text-xs text-gray-400">
            <i class="fas fa-info-circle"></i> Employment details can only be changed by HR.
        </div>
    </div>

    {{-- Account Info --}}
    <div class="card bg-base-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-gray-100 flex items-center justify-center text-gray-500 text-xs flex-shrink-0">
                <i class="fas fa-shield-alt"></i>
            </span>
            Account Info
        </h2>
        <div class="flex flex-col text-sm">
            <div class="flex justify-between items-center py-2.5 border-b border-gray-100">
                <span class="text-gray-400">Role</span>
                <span class="badge {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
            </div>
            <div class="flex justify-between items-center py-2.5 border-b border-gray-100">
                <span class="text-gray-400">Account Status</span>
                <span class="badge badge-soft badge-success">Active</span>
            </div>
            <div class="flex justify-between items-center py-2.5 border-b border-gray-100">
                <span class="text-gray-400">Member Since</span>
                <span class="font-medium text-gray-800">{{ $user->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex justify-between items-center py-2.5">
                <span class="text-gray-400">Last Login</span>
                <span class="font-medium text-gray-800">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Government IDs --}}
    <div class="card bg-base-100 shadow-sm p-5 md:col-span-2">
        <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-red-100 flex items-center justify-center text-red-600 text-xs flex-shrink-0">
                <i class="fas fa-landmark"></i>
            </span>
            Government IDs
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['SSS Number',        $employee->sss_number,        'fa-shield-alt',   'text-emerald-600', 'bg-emerald-100'],
                ['PhilHealth Number', $employee->philhealth_number,  'fa-heart',        'text-blue-600',    'bg-blue-100'],
                ['Pag-IBIG Number',   $employee->pagibig_number,     'fa-home',         'text-amber-500',   'bg-amber-100'],
                ['TIN Number',        $employee->tin_number,         'fa-file-invoice', 'text-violet-600',  'bg-violet-100'],
            ] as [$label, $value, $icon, $color, $bg])
                <div class="card bg-base-100 shadow-sm p-4 text-center">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 {{ $color }} {{ $bg }}">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div class="text-xs text-gray-400 uppercase tracking-widest font-medium mb-1">{{ $label }}</div>
                    <div class="font-bold font-mono text-gray-800 text-xs break-all">{{ $value ?? '—' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @else

    {{-- Account Info (no employee record) --}}
    <div class="card bg-base-100 shadow-sm p-5 md:col-span-2">
        <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-md bg-gray-100 flex items-center justify-center text-gray-500 text-xs flex-shrink-0">
                <i class="fas fa-shield-alt"></i>
            </span>
            Account Info
        </h2>
        <div class="flex flex-col text-sm">
            <div class="flex justify-between items-center py-2.5 border-b border-gray-100">
                <span class="text-gray-400">Role</span>
                <span class="badge {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
            </div>
            <div class="flex justify-between items-center py-2.5 border-b border-gray-100">
                <span class="text-gray-400">Account Status</span>
                <span class="badge badge-soft badge-success">Active</span>
            </div>
            <div class="flex justify-between items-center py-2.5 border-b border-gray-100">
                <span class="text-gray-400">Member Since</span>
                <span class="font-medium text-gray-800">{{ $user->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex justify-between items-center py-2.5">
                <span class="text-gray-400">Last Login</span>
                <span class="font-medium text-gray-800">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : '—' }}</span>
            </div>
        </div>
    </div>

    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput     = document.getElementById('photo-file-input');
    const formFileInput = document.getElementById('photo-form-file');
    const uploadForm    = document.getElementById('photo-upload-form');
    const avatarCircle  = document.getElementById('avatar-circle');

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            let img = document.getElementById('avatar-img');
            const initials = document.getElementById('avatar-initials');
            if (!img) {
                img = document.createElement('img');
                img.id = 'avatar-img';
                img.className = 'w-full h-full object-cover';
                avatarCircle.innerHTML = '';
                avatarCircle.appendChild(img);
            }
            if (initials) initials.style.display = 'none';
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);

        const dt = new DataTransfer();
        dt.items.add(file);
        formFileInput.files = dt.files;
        uploadForm.submit();
    });
});
</script>

@endsection
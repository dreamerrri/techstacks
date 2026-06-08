@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

@php
use Illuminate\Support\Facades\Storage;
    $user     = auth()->user();
    $isAdmin  = $user->isAdmin();
    $isHR     = $user->isHR();

    $employee = $user->employee;
@endphp

{{-- ============================================================
     BANNER
     ============================================================ --}}
    <div id="profile-banner" class="card" style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">

    {{-- Avatar + info --}}
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">

        {{-- Avatar (click to change) --}}
        <div style="position:relative;flex-shrink:0;">
            <div id="avatar-circle"
                 style="width:80px;height:80px;border-radius:50%;
                        background:#f3f4f6;
                        border:3px solid #e5e7eb;
                        overflow:hidden;display:flex;align-items:center;
                        justify-content:center;cursor:pointer;
                        font-size:32px;font-weight:700;color:#6b7280;">
                @if($user->profile_photo)
                    <img id="avatar-img"
{{-- After --}}
src="{{ Storage::disk('s3')->temporaryUrl($user->profile_photo, now()->addHours(24)) }}"     

   alt="{{ $user->name }}"
                         style="width:100%;height:100%;object-fit:cover;">
                @else
                    <span id="avatar-initials">{{ strtoupper(substr($user->name,0,1)) }}</span>
                @endif
            </div>
            <label for="photo-file-input"
                   style="position:absolute;bottom:0;right:0;
                          width:24px;height:24px;border-radius:50%;
                          background:white;border:2px solid #e5e7eb;
                          display:flex;align-items:center;justify-content:center;
                          cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                <i class="fas fa-camera" style="font-size:10px;color:#374151;"></i>
            </label>
            <input type="file" id="photo-file-input" accept="image/*" style="display:none;">
        </div>

        {{-- Name / email / badges --}}
        <div style="flex:1;">
            <h1 style="margin:0 0 4px;color:#1f2937;font-size:24px;font-weight:700;">
                {{ $user->name }}
            </h1>
            <p style="margin:0 0 8px;color:#6b7280;font-size:14px;">
                {{ $user->email }}
            </p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <span style="padding:3px 12px;border-radius:20px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;
                    {{ $isAdmin ? 'background:#fee2e2;color:#991b1b;' : ($isHR ? 'background:#dbeafe;color:#1e40af;' : 'background:#d1fae5;color:#065f46;') }}">
                    {{ ucfirst($user->role) }}
                </span>
                @if($employee)
                <span style="background:#f3f4f6;color:#374151;
                             padding:3px 12px;border-radius:20px;font-size:11px;">
                    {{ $employee->position }} — {{ $employee->department }}
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for photo upload --}}
<form id="photo-upload-form" method="POST"
      action="{{ route('profile.photo') }}"
      enctype="multipart/form-data" style="display:none;">
    @csrf
    <input type="file" name="photo" id="photo-form-file">
</form>

{{-- ============================================================
     BODY CARDS
     ============================================================ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    {{-- Account Settings --}}
    <div class="card" style="grid-column:1 / -1;">
        <h2><i class="fas fa-user-cog" style="background:rgba(220,38,38,0.1);color:#dc2626;"></i> Account Settings</h2>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-weight:600;color:#1f2937;margin-bottom:8px;font-size:14px;">Display Name</label>
                    <input type="text" name="name" value="{{ old('name',$user->name) }}" required
                           style="width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:10px;font-size:14px;">
                    @error('name') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="display:block;font-weight:600;color:#1f2937;margin-bottom:8px;font-size:14px;">Email Address</label>
                    <input type="email" name="email" value="{{ old('email',$user->email) }}" required
                           style="width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:10px;font-size:14px;">
                    @error('email') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:4px;font-size:13px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                Change Password <span style="font-weight:400;text-transform:none;">(leave blank to keep current)</span>
            </div>
            <div style="border-top:1px solid #e5e7eb;margin-bottom:16px;"></div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-weight:600;color:#1f2937;margin-bottom:8px;font-size:14px;">Current Password</label>
                    <input type="password" name="current_password"
                           style="width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:10px;font-size:14px;" placeholder="••••••••">
                    @error('current_password') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="display:block;font-weight:600;color:#1f2937;margin-bottom:8px;font-size:14px;">New Password</label>
                    <input type="password" name="password"
                           style="width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:10px;font-size:14px;" placeholder="••••••••">
                    @error('password') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="display:block;font-weight:600;color:#1f2937;margin-bottom:8px;font-size:14px;">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                           style="width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:10px;font-size:14px;" placeholder="••••••••">
                </div>
            </div>

            <button type="submit"
                    style="padding:10px 24px;background:linear-gradient(135deg,#dc2626,#991b1b);
                           color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>

    @if($employee)

    {{-- Personal Information (editable) --}}
    <div class="card" style="grid-column:1 / -1;">
        <h2><i class="fas fa-id-card" style="background:rgba(37,99,235,0.1);color:#2563eb;"></i> Personal Information</h2>
        <form method="POST" action="{{ route('profile.personal') }}">
            @csrf @method('PUT')
            @php
                $inp = "width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:8px 12px;font-size:14px;box-sizing:border-box;";
                $lbl = "display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;";
            @endphp
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:20px;">

                <div>
                    <label style="{{ $lbl }}">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required
                           style="{{ $inp }}">
                    @error('first_name') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="{{ $lbl }}">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}"
                           style="{{ $inp }}">
                </div>

                <div>
                    <label style="{{ $lbl }}">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required
                           style="{{ $inp }}">
                    @error('last_name') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="{{ $lbl }}">Birthdate</label>
                    <input type="date" name="birthdate" value="{{ old('birthdate', $employee->birthdate->format('Y-m-d')) }}"
                           required max="{{ date('Y-m-d', strtotime('-1 day')) }}" style="{{ $inp }}">
                    @error('birthdate') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="{{ $lbl }}">Gender</label>
                    <select name="gender" required style="{{ $inp }}">
                        @foreach(['Male','Female','Other'] as $g)
                            <option value="{{ $g }}" {{ old('gender', $employee->gender) == $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                    @error('gender') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="{{ $lbl }}">Civil Status</label>
                    <select name="civil_status" required style="{{ $inp }}">
                        @foreach(['Single','Married','Widowed','Separated'] as $cs)
                            <option value="{{ $cs }}" {{ old('civil_status', $employee->civil_status) == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                        @endforeach
                    </select>
                    @error('civil_status') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="{{ $lbl }}">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $employee->contact_number) }}"
                           placeholder="09XXXXXXXXX" maxlength="11" style="{{ $inp }}">
                    @error('contact_number') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column:1 / -1;">
                    <label style="{{ $lbl }}">Address</label>
                    <textarea name="address" rows="2" style="{{ $inp }}">{{ old('address', $employee->address) }}</textarea>
                    @error('address') <div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div> @enderror
                </div>

            </div>

            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <button type="submit"
                        style="padding:10px 24px;background:linear-gradient(135deg,#2563eb,#1e40af);
                               color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                    <i class="fas fa-save"></i> Save Personal Info
                </button>
            </div>
        </form>
    </div>

    {{-- Employment Information (read-only) --}}
    <div class="card">
        <h2><i class="fas fa-briefcase" style="background:rgba(16,185,129,0.1);color:#10b981;"></i> Employment Information</h2>
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            @foreach([
                ['Department',        $employee->department],
                ['Position',          $employee->position],
                ['Employment Status', $employee->employment_status],
                ['Date Hired',        $employee->date_hired->format('M d, Y')],
                ['Salary Type',       $employee->salary_type],
            ] as [$label,$value])
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 0;color:#6b7280;font-size:13px;width:45%;">{{ $label }}</td>
                <td style="padding:10px 0;font-weight:500;color:#1f2937;font-size:13px;">{{ $value ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
        <div style="margin-top:12px;padding:10px 12px;background:#f9fafb;border-radius:8px;font-size:12px;color:#6b7280;">
            <i class="fas fa-info-circle"></i> Employment details can only be changed by HR.
        </div>
    </div>

    {{-- Account Info --}}
    <div class="card">
        <h2><i class="fas fa-shield-alt" style="background:rgba(107,114,128,0.1);color:#6b7280;"></i> Account Info</h2>
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 0;color:#6b7280;font-size:13px;width:45%;">Role</td>
                <td style="padding:10px 0;">
                    <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;
                        {{ $isAdmin ? 'background:#fee2e2;color:#991b1b;' : ($isHR ? 'background:#dbeafe;color:#1e40af;' : 'background:#d1fae5;color:#065f46;') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 0;color:#6b7280;font-size:13px;">Account Status</td>
                <td style="padding:10px 0;">
                    <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#d1fae5;color:#065f46;">Active</span>
                </td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 0;color:#6b7280;font-size:13px;">Member Since</td>
                <td style="padding:10px 0;font-weight:500;color:#1f2937;font-size:13px;">{{ $user->created_at->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:#6b7280;font-size:13px;">Last Login</td>
                <td style="padding:10px 0;font-weight:500;color:#1f2937;font-size:13px;">
                    {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : '—' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Government IDs --}}
    <div class="card" style="grid-column:1 / -1;">
        <h2><i class="fas fa-landmark" style="background:rgba(220,38,38,0.1);color:#dc2626;"></i> Government IDs</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;">
            @foreach([
                ['SSS Number',        $employee->sss_number,       'fa-shield-alt'],
                ['PhilHealth Number', $employee->philhealth_number, 'fa-heart'],
                ['Pag-IBIG Number',   $employee->pagibig_number,    'fa-home'],
                ['TIN Number',        $employee->tin_number,        'fa-file-invoice'],
            ] as [$label,$value,$icon])
            <div style="background:#f9fafb;padding:16px;border-radius:10px;text-align:center;">
                <div style="color:#dc2626;font-size:20px;margin-bottom:8px;"><i class="fas {{ $icon }}"></i></div>
                <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">{{ $label }}</div>
                <div style="font-weight:600;font-family:monospace;color:#1f2937;font-size:13px;word-break:break-all;">{{ $value ?? '—' }}</div>
            </div>
            @endforeach
        </div>
    </div>

    @else

    {{-- Account Info (no employee record) --}}
    <div class="card" style="grid-column:1 / -1;">
        <h2><i class="fas fa-shield-alt" style="background:rgba(107,114,128,0.1);color:#6b7280;"></i> Account Info</h2>
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 0;color:#6b7280;font-size:13px;width:45%;">Role</td>
                <td style="padding:10px 0;">
                    <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;
                        {{ $isAdmin ? 'background:#fee2e2;color:#991b1b;' : ($isHR ? 'background:#dbeafe;color:#1e40af;' : 'background:#d1fae5;color:#065f46;') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 0;color:#6b7280;font-size:13px;">Account Status</td>
                <td style="padding:10px 0;">
                    <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#d1fae5;color:#065f46;">Active</span>
                </td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 0;color:#6b7280;font-size:13px;">Member Since</td>
                <td style="padding:10px 0;font-weight:500;color:#1f2937;font-size:13px;">{{ $user->created_at->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:#6b7280;font-size:13px;">Last Login</td>
                <td style="padding:10px 0;font-weight:500;color:#1f2937;font-size:13px;">
                    {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : '—' }}
                </td>
            </tr>
        </table>
    </div>

    @endif

</div>

{{-- ============================================================
     JAVASCRIPT
     ============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Profile photo ────────────────────────────────────────── */
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
                img.id    = 'avatar-img';
                img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
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
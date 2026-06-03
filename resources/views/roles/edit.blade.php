@extends('layouts.app')

@section('title', 'Create Role')

@section('content')

    @php
        $input   = "width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box;";
        $label   = "display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;";
        $section = "font-size:16px; font-weight:700; color:#1f2937; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;";
    @endphp

    <div style="margin-bottom:20px;">
        <a href="{{ route('roles.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    <div class="card">
        <h2 style="margin-bottom:24px;"><i class="fas fa-user-tag" style="color:#dc2626;"></i> Create New Role</h2>

        <form method="POST" action="{{ route('roles.store') }}">
            @csrf

            {{-- Role Details --}}
            <div style="margin-bottom:32px;">
                <h3 style="{{ $section }}"><i class="fas fa-info-circle" style="color:#dc2626;"></i> Role Details</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

                    <div>
                        <label style="{{ $label }}">Role Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" style="{{ $input }}" required>
                        @error('name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="{{ $label }}">Slug <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" style="{{ $input }}" required
                               placeholder="e.g. admin, hr, employee">
                        <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Lowercase, no spaces (used in code)</p>
                        @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="{{ $label }}">Description</label>
                        <textarea name="description" rows="2" style="{{ $input }}">{{ old('description') }}</textarea>
                        @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; color:#374151;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                   style="width:16px; height:16px; accent-color:#dc2626;">
                            <span style="font-weight:600;">Active</span>
                        </label>
                    </div>

                </div>
            </div>

            {{-- Permissions --}}
            <div style="margin-bottom:32px;">
                <h3 style="{{ $section }}"><i class="fas fa-key" style="color:#dc2626;"></i> Permissions</h3>
                @if($permissions->count())
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                        @foreach($permissions as $module => $modulePermissions)
                            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:14px;">
                                <div style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; margin-bottom:10px; letter-spacing:0.05em;">
                                    {{ ucfirst($module) }}
                                </div>
                                @foreach($modulePermissions as $permission)
                                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151; margin-bottom:6px;">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                               {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                               style="width:14px; height:14px; accent-color:#dc2626;">
                                        {{ $permission->name }}
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="color:#9ca3af; font-size:14px;">No permissions available. Create permissions first.</p>
                @endif
                @error('permissions') <p style="color:#dc2626; font-size:12px; margin-top:8px;">{{ $message }}</p> @enderror
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <button type="submit"
                        style="padding:10px 24px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px;">
                    <i class="fas fa-save"></i> Create Role
                </button>
                <a href="{{ route('roles.index') }}"
                   style="padding:10px 24px; background:#f3f4f6; color:#374151; border-radius:6px; text-decoration:none; font-weight:600; font-size:14px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // Auto-generate slug from name
        document.querySelector('[name="name"]').addEventListener('input', function () {
            const slugField = document.getElementById('slug');
            if (!slugField._touched) {
                slugField.value = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '');
            }
        });
        document.getElementById('slug').addEventListener('input', function () {
            this._touched = this.value !== '';
        });
    </script>

@endsection
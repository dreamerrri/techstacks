@extends('layouts.app')

@section('title', 'Create Role')
@section('breadcrumb')
    <a href="{{ route('users.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Manage Users</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('roles.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Roles</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Create Role</span>
@endsection
@section('content')

    <div style="margin-bottom:20px;">
        <a href="{{ route('roles.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    <div class="aurora-card">
        <h2 class="aurora-card-title" style="font-size:16px; text-transform:none; color:#1f2937; margin-bottom:24px;">
            <i class="fas fa-user-tag" style="color:#dc2626;"></i> Create New Role
        </h2>

        <form method="POST" action="{{ route('roles.store') }}">
            @csrf

            {{-- Role Details --}}
            <div style="margin-bottom:32px;">
                <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#9ca3af; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;">
                    <i class="fas fa-info-circle" style="color:#dc2626;"></i> Role Details
                </h3>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; display:block; margin-bottom:6px;">
                            Role Name <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box; outline:none;"
                               required>
                        @error('name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; display:block; margin-bottom:6px;">
                            Slug <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                               style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box; outline:none;"
                               placeholder="e.g. admin, hr, employee" required>
                        <p style="color:#9ca3af; font-size:11px; margin-top:4px;">Lowercase, no spaces (used in code)</p>
                        @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; display:block; margin-bottom:6px;">
                            Description
                        </label>
                        <textarea name="description" rows="2"
                                  style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box; outline:none; resize:vertical;">{{ old('description') }}</textarea>
                        @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; color:#374151; font-weight:500;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                   style="width:16px; height:16px; accent-color:#dc2626;">
                            <span style="font-weight:600;">Active</span>
                        </label>
                    </div>

                </div>
            </div>

            {{-- Permissions --}}
            <div style="margin-bottom:32px;">
                <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#9ca3af; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;">
                    <i class="fas fa-key" style="color:#dc2626;"></i> Permissions
                </h3>

                @if($permissions->count())
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                        @foreach($permissions as $module => $modulePermissions)
                            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:14px;">
                                <div style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px;">
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
                    <p style="color:#9ca3af; font-size:14px; margin:0;">No permissions available. Create permissions first.</p>
                @endif
                @error('permissions') <p style="color:#dc2626; font-size:12px; margin-top:8px;">{{ $message }}</p> @enderror
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:12px; flex-wrap:wrap; padding-top:4px; border-top:1px solid rgba(0,0,0,0.045);">
                <button type="submit" class="btn btn btn-error">
                    <i class="fas fa-save"></i> Create Role
                </button>
                <a href="{{ route('roles.index') }}" class="btn btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
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
@extends('layouts.app')

@section('title', 'Edit Permission')
@section('breadcrumb')
    <a href="{{ route('users.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Manage Users</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('permissions.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Permissions</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Edit {{ $permission->name }}</span>
@endsection
@section('content')

    <div style="margin-bottom:20px;">
        <a href="{{ route('permissions.show', $permission) }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Permission
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <h2 class="card bg-base-100 shadow-sm-title" style="font-size:16px; text-transform:none; color:#1f2937; margin-bottom:24px;">
            <i class="fas fa-edit" style="color:#dc2626;"></i> Edit — {{ $permission->name }}
        </h2>

        <form method="POST" action="{{ route('permissions.update', $permission) }}">
            @csrf @method('PUT')

            {{-- Permission Details --}}
            <div style="margin-bottom:32px;">
                <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#9ca3af; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;">
                    <i class="fas fa-info-circle" style="color:#dc2626;"></i> Permission Details
                </h3>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; display:block; margin-bottom:6px;">
                            Permission Name <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $permission->name) }}"
                               style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box; outline:none;"
                               required>
                        @error('name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; display:block; margin-bottom:6px;">
                            Slug <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" name="slug" value="{{ old('slug', $permission->slug) }}"
                               style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box; outline:none;"
                               required>
                        <p style="color:#9ca3af; font-size:11px; margin-top:4px;">Dot-notation, lowercase (used in middleware)</p>
                        @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; display:block; margin-bottom:6px;">
                            Module <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" name="module" value="{{ old('module', $permission->module) }}"
                               style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box; outline:none;"
                               placeholder="e.g. Employees, Payroll, Users" required>
                        <p style="color:#9ca3af; font-size:11px; margin-top:4px;">Groups permissions on the roles page</p>
                        @error('module') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; display:block; margin-bottom:6px;">
                            Description
                        </label>
                        <textarea name="description" rows="2"
                                  style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box; outline:none; resize:vertical;">{{ old('description', $permission->description) }}</textarea>
                        @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; color:#374151; font-weight:500;">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $permission->is_active) ? 'checked' : '' }}
                                   style="width:16px; height:16px; accent-color:#dc2626;">
                            <span style="font-weight:600;">Active</span>
                        </label>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:12px; flex-wrap:wrap; padding-top:4px; border-top:1px solid rgba(0,0,0,0.045);">
                <button type="submit" class="btn btn btn-error">
                    <i class="fas fa-save"></i> Update Permission
                </button>
                <a href="{{ route('permissions.show', $permission) }}" class="btn btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>

@endsection
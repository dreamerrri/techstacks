@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')

    @php
        $input   = "width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box;";
        $label   = "display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;";
        $section = "font-size:16px; font-weight:700; color:#1f2937; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;";
    @endphp

    <div style="margin-bottom:20px;">
        <a href="{{ route('permissions.show', $permission) }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Permission
        </a>
    </div>

    <div class="card">
        <h2 style="margin-bottom:24px;"><i class="fas fa-edit" style="color:#dc2626;"></i> Edit — {{ $permission->name }}</h2>

        <form method="POST" action="{{ route('permissions.update', $permission) }}">
            @csrf @method('PUT')

            <div style="margin-bottom:32px;">
                <h3 style="{{ $section }}"><i class="fas fa-info-circle" style="color:#dc2626;"></i> Permission Details</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

                    <div>
                        <label style="{{ $label }}">Permission Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $permission->name) }}" style="{{ $input }}" required>
                        @error('name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="{{ $label }}">Slug <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="slug" value="{{ old('slug', $permission->slug) }}" style="{{ $input }}" required>
                        <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Dot-notation, lowercase (used in middleware)</p>
                        @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="{{ $label }}">Module <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="module" value="{{ old('module', $permission->module) }}" style="{{ $input }}" required
                               placeholder="e.g. Employees, Payroll, Users">
                        <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Groups permissions on the roles page</p>
                        @error('module') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="{{ $label }}">Description</label>
                        <textarea name="description" rows="2" style="{{ $input }}">{{ old('description', $permission->description) }}</textarea>
                        @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; color:#374151;">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $permission->is_active) ? 'checked' : '' }}
                                   style="width:16px; height:16px; accent-color:#dc2626;">
                            <span style="font-weight:600;">Active</span>
                        </label>
                    </div>

                </div>
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <button type="submit"
                        style="padding:10px 24px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px;">
                    <i class="fas fa-save"></i> Update Permission
                </button>
                <a href="{{ route('permissions.show', $permission) }}"
                   style="padding:10px 24px; background:#f3f4f6; color:#374151; border-radius:6px; text-decoration:none; font-weight:600; font-size:14px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

@endsection
@extends('layouts.app')

@section('title', 'Create Permission')

@section('content')

    @php
        $input   = "width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px; transition:border-color 0.2s; box-sizing:border-box;";
        $label   = "display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;";
        $section = "font-size:16px; font-weight:700; color:#1f2937; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;";
    @endphp

    <div style="margin-bottom:20px;">
        <a href="{{ route('permissions.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Permissions
        </a>
    </div>

    <div class="card">
        <h2 style="margin-bottom:24px;"><i class="fas fa-key" style="color:#dc2626;"></i> Create New Permission</h2>

        <form method="POST" action="{{ route('permissions.store') }}">
            @csrf

            <div style="margin-bottom:32px;">
                <h3 style="{{ $section }}"><i class="fas fa-info-circle" style="color:#dc2626;"></i> Permission Details</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

                    <div>
                        <label style="{{ $label }}">Permission Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" style="{{ $input }}" required
                               placeholder="e.g. View Employees">
                        @error('name') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="{{ $label }}">Slug <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" style="{{ $input }}" required
                               placeholder="e.g. view.employees">
                        <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Dot-notation, lowercase (used in middleware)</p>
                        @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="{{ $label }}">Module <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="module" id="module" value="{{ old('module') }}" style="{{ $input }}" required
                               placeholder="e.g. Employees, Payroll, Users">
                        <p style="color:#9ca3af; font-size:11px; margin-top:3px;">Groups permissions on the roles page</p>
                        @error('module') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="{{ $label }}">Description</label>
                        <textarea name="description" rows="2" style="{{ $input }}" placeholder="What does this permission allow?">{{ old('description') }}</textarea>
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

            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <button type="submit"
                        style="padding:10px 24px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px;">
                    <i class="fas fa-save"></i> Create Permission
                </button>
                <a href="{{ route('permissions.index') }}"
                   style="padding:10px 24px; background:#f3f4f6; color:#374151; border-radius:6px; text-decoration:none; font-weight:600; font-size:14px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // Auto-generate slug from name (dot.notation style)
        document.getElementById('name').addEventListener('input', function () {
            const slugField = document.getElementById('slug');
            if (!slugField._touched) {
                slugField.value = this.value.toLowerCase().trim().replace(/\s+/g, '.').replace(/[^a-z0-9\.]/g, '');
            }
        });
        document.getElementById('slug').addEventListener('input', function () {
            this._touched = this.value !== '';
        });
    </script>

@endsection
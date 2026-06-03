@extends('layouts.app')

@section('title', 'Permission Details')

@section('content')

    @php
        $label   = "display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;";
        $section = "font-size:16px; font-weight:700; color:#1f2937; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;";
    @endphp

    <div style="margin-bottom:20px;">
        <a href="{{ route('permissions.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Permissions
        </a>
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:#d1fae5; color:#065f46; border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:18px; flex-shrink:0;">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h2 style="margin:0; color:#1f2937; font-size:20px;">{{ $permission->name }}</h2>
                    <code style="font-size:12px; color:#6b7280; background:#f3f4f6; padding:2px 8px; border-radius:4px;">{{ $permission->slug }}</code>
                </div>
            </div>
            <a href="{{ route('permissions.edit', $permission) }}"
               style="padding:8px 16px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600;">
                <i class="fas fa-edit"></i> Edit Permission
            </a>
        </div>

        {{-- Permission Info --}}
        <div style="margin-bottom:32px;">
            <h3 style="{{ $section }}"><i class="fas fa-info-circle" style="color:#dc2626;"></i> Permission Information</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                <div>
                    <span style="{{ $label }}">Module</span>
                    <span style="font-size:14px; color:#374151;">{{ ucfirst($permission->module) }}</span>
                </div>
                <div>
                    <span style="{{ $label }}">Status</span>
                    @if($permission->is_active)
                        <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; background:#d1fae5; color:#065f46;">Active</span>
                    @else
                        <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; background:#fee2e2; color:#991b1b;">Inactive</span>
                    @endif
                </div>
                <div>
                    <span style="{{ $label }}">Assigned to Roles</span>
                    <span style="font-size:14px; color:#374151;">{{ $permission->roles->count() }}</span>
                </div>
                @if($permission->description)
                    <div style="grid-column: 1 / -1;">
                        <span style="{{ $label }}">Description</span>
                        <span style="font-size:14px; color:#374151;">{{ $permission->description }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Roles --}}
        <div>
            <h3 style="{{ $section }}"><i class="fas fa-user-tag" style="color:#dc2626;"></i> Roles with this Permission ({{ $permission->roles->count() }})</h3>
            @if($permission->roles->count() > 0)
                @foreach($permission->roles as $role)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; flex-shrink:0;">
                                {{ strtoupper(substr($role->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $role->name }}</div>
                                <code style="font-size:11px; color:#6b7280;">{{ $role->slug }}</code>
                            </div>
                        </div>
                        <a href="{{ route('roles.show', $role) }}"
                           style="padding:5px 12px; background:#eff6ff; color:#1d4ed8; border-radius:5px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View Role
                        </a>
                    </div>
                @endforeach
            @else
                <p style="color:#9ca3af; font-size:14px;">No roles have this permission.</p>
            @endif
        </div>
    </div>

@endsection
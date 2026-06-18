@extends('layouts.app')

@section('title', 'Permission Details')
@section('breadcrumb')
    <a href="{{ route('users.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Manage Users</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('permissions.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Permissions</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">{{ $permission->name }}</span>
@endsection
@section('content')

    <div style="margin-bottom:20px;">
        <a href="{{ route('permissions.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Permissions
        </a>
    </div>

    @if(session('success'))
        <div class="badge badge badge-soft badge-success" style="margin-bottom:16px; padding:12px 16px; border-radius:8px; display:block;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="aurora-card">

        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:18px; flex-shrink:0;">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h2 style="margin:0; color:#1f2937; font-size:20px; font-weight:700;">{{ $permission->name }}</h2>
                    <code style="font-size:12px; color:#6b7280; background:#f3f4f6; padding:2px 8px; border-radius:4px;">{{ $permission->slug }}</code>
                </div>
            </div>
            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn btn-error btn-sm" style="font-size:14px; padding:8px 16px;">
                <i class="fas fa-edit"></i> Edit Permission
            </a>
        </div>

        {{-- Permission Information --}}
        <div style="margin-bottom:32px;">
            <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#9ca3af; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;">
                <i class="fas fa-info-circle" style="color:#dc2626;"></i> Permission Information
            </h3>

            <div class="aurora-info-list">
                <div class="aurora-info-row">
                    <span class="aurora-info-label">Module</span>
                    <span class="aurora-info-value">{{ ucfirst($permission->module) }}</span>
                </div>
                <div class="aurora-info-row">
                    <span class="aurora-info-label">Status</span>
                    <span class="aurora-info-value">
                        @if($permission->is_active)
                            <span class="badge badge badge-soft badge-success"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge badge badge-soft badge-error"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </span>
                </div>
                <div class="aurora-info-row">
                    <span class="aurora-info-label">Assigned to Roles</span>
                    <span class="aurora-info-value">{{ $permission->roles->count() }}</span>
                </div>
                @if($permission->description)
                    <div class="aurora-info-row" style="flex-direction:column; align-items:flex-start; gap:4px;">
                        <span class="aurora-info-label">Description</span>
                        <span class="aurora-info-value" style="text-align:left; font-weight:400; color:#374151;">{{ $permission->description }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Roles with this Permission --}}
        <div>
            <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#9ca3af; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;">
                <i class="fas fa-user-tag" style="color:#dc2626;"></i> Roles with this Permission ({{ $permission->roles->count() }})
            </h3>

            @if($permission->roles->count() > 0)
                @foreach($permission->roles as $role)
                
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid #e5e7eb; border-radius:12px; margin-bottom:8px; transition:box-shadow 0.2s ease;"
                         onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.07)'"
                         onmouseout="this.style.boxShadow='none'">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; flex-shrink:0;">
                                {{ strtoupper(substr($role->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $role->name }}</div>
                                <code style="font-size:11px; color:#6b7280;">{{ $role->slug }}</code>
                            </div>
                        </div>
                        <a href="{{ route('roles.show', $role) }}" class="btn btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View Role
                        </a>
                    </div>
                @endforeach
            @else
                <p style="color:#9ca3af; font-size:14px; margin:0;">No roles have this permission.</p>
            @endif
        </div>

    </div>

@endsection
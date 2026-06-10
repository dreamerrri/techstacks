@extends('layouts.app')

@section('title', 'Role Details')
@section('breadcrumb')
    <a href="{{ route('users.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Manage Users</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('roles.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Roles</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">{{ $role->name }}</span>
@endsection
@section('content')

    <div style="margin-bottom:20px;">
        <a href="{{ route('roles.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    @if(session('success'))
        <div class="aurora-status aurora-status-active" style="margin-bottom:16px; padding:12px 16px; border-radius:8px; display:block;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="aurora-status aurora-status-inactive" style="margin-bottom:16px; padding:12px 16px; border-radius:8px; display:block;">
            <i class="fas fa-times-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="aurora-card">

        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:20px; font-weight:700; flex-shrink:0;">
                    {{ strtoupper(substr($role->name, 0, 1)) }}
                </div>
                <div>
                    <h2 style="margin:0; color:#1f2937; font-size:20px; font-weight:700;">{{ $role->name }}</h2>
                    <code style="font-size:12px; color:#6b7280; background:#f3f4f6; padding:2px 8px; border-radius:4px;">{{ $role->slug }}</code>
                </div>
            </div>
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-danger btn-sm" style="font-size:14px; padding:8px 16px;">
                <i class="fas fa-edit"></i> Edit Role
            </a>
        </div>

        {{-- Role Information --}}
        <div style="margin-bottom:32px;">
            <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#9ca3af; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;">
                <i class="fas fa-user-tag" style="color:#dc2626;"></i> Role Information
            </h3>

            <div class="aurora-info-list">
                <div class="aurora-info-row">
                    <span class="aurora-info-label">Description</span>
                    <span class="aurora-info-value" style="font-weight:400; color:#374151;">{{ $role->description ?? '—' }}</span>
                </div>
                <div class="aurora-info-row">
                    <span class="aurora-info-label">Status</span>
                    <span class="aurora-info-value">
                        @if($role->is_active)
                            <span class="aurora-status aurora-status-active"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="aurora-status aurora-status-inactive"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </span>
                </div>
                <div class="aurora-info-row">
                    <span class="aurora-info-label">Total Users</span>
                    <span class="aurora-info-value">{{ $role->users->count() }}</span>
                </div>
                <div class="aurora-info-row">
                    <span class="aurora-info-label">Total Permissions</span>
                    <span class="aurora-info-value">{{ $role->permissions->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div style="margin-bottom:32px;">
            <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#9ca3af; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;">
                <i class="fas fa-key" style="color:#dc2626;"></i> Permissions ({{ $role->permissions->count() }})
            </h3>

            @if($role->permissions->count() > 0)
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                    @foreach($role->permissions->groupBy('module') as $module => $modulePerms)
                        <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:14px;">
                            <div style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px;">
                                {{ ucfirst($module) }}
                            </div>
                            @foreach($modulePerms as $permission)
                                <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:#374151; margin-bottom:4px;">
                                    <i class="fas fa-check-circle" style="color:#10b981; font-size:11px; flex-shrink:0;"></i>
                                    {{ $permission->name }}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color:#9ca3af; font-size:14px; margin:0;">No permissions assigned to this role.</p>
            @endif
        </div>

        {{-- Assigned Users --}}
        <div>
            <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#9ca3af; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;">
                <i class="fas fa-users" style="color:#dc2626;"></i> Assigned Users ({{ $role->users->count() }})
            </h3>

            {{-- Assign User Form --}}
            @if($availableUsers->count() > 0)
                <form method="POST" action="{{ route('roles.assign.user', $role) }}" style="margin-bottom:20px;">
                    @csrf
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <select name="user_id" required
                                style="flex:1; min-width:200px; border:1px solid #e5e7eb; border-radius:8px; padding:9px 12px; font-size:14px; outline:none; background:white;">
                            <option value="">Select a user to assign...</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-user-plus"></i> Assign User
                        </button>
                    </div>
                </form>
            @endif

            @if($role->users->count() > 0)
                @foreach($role->users as $user)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid #e5e7eb; border-radius:12px; margin-bottom:8px; transition:box-shadow 0.2s ease;"
                         onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.07)'"
                         onmouseout="this.style.boxShadow='none'">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $user->name }}</div>
                                <div style="color:#6b7280; font-size:12px;">{{ $user->email }}</div>
                            </div>
                        </div>
                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('roles.remove.user', [$role, $user]) }}"
                                  data-confirm="This user will be removed from the {{ $role->name }} role."
                                  data-confirm-title="Remove User?"
                                  data-confirm-icon="warning"
                                  data-confirm-btn="Yes, remove">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="fas fa-user-minus"></i> Remove
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            @else
                <p style="color:#9ca3af; font-size:14px; margin:0;">No users assigned to this role.</p>
            @endif
        </div>

    </div>

@endsection
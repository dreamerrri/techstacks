@extends('layouts.app')

@section('title', 'Role Details')

@section('content')

    @php
        $label   = "display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;";
        $section = "font-size:16px; font-weight:700; color:#1f2937; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;";
        $input   = "width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px; box-sizing:border-box;";
    @endphp

    <div style="margin-bottom:20px;">
        <a href="{{ route('roles.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:#d1fae5; color:#065f46; border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom:16px; padding:12px 16px; background:#fee2e2; color:#991b1b; border-radius:8px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:20px; font-weight:700; flex-shrink:0;">
                    {{ strtoupper(substr($role->name, 0, 1)) }}
                </div>
                <div>
                    <h2 style="margin:0; color:#1f2937; font-size:20px;">{{ $role->name }}</h2>
                    <code style="font-size:12px; color:#6b7280; background:#f3f4f6; padding:2px 8px; border-radius:4px;">{{ $role->slug }}</code>
                </div>
            </div>
            <a href="{{ route('roles.edit', $role) }}"
               style="padding:8px 16px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600;">
                <i class="fas fa-edit"></i> Edit Role
            </a>
        </div>

        {{-- Role Info --}}
        <div style="margin-bottom:32px;">
            <h3 style="{{ $section }}"><i class="fas fa-user-tag" style="color:#dc2626;"></i> Role Information</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                <div>
                    <span style="{{ $label }}">Description</span>
                    <span style="font-size:14px; color:#374151;">{{ $role->description ?? '—' }}</span>
                </div>
                <div>
                    <span style="{{ $label }}">Status</span>
                    @if($role->is_active)
                        <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; background:#d1fae5; color:#065f46;">Active</span>
                    @else
                        <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; background:#fee2e2; color:#991b1b;">Inactive</span>
                    @endif
                </div>
                <div>
                    <span style="{{ $label }}">Total Users</span>
                    <span style="font-size:14px; color:#374151;">{{ $role->users->count() }}</span>
                </div>
                <div>
                    <span style="{{ $label }}">Total Permissions</span>
                    <span style="font-size:14px; color:#374151;">{{ $role->permissions->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div style="margin-bottom:32px;">
            <h3 style="{{ $section }}"><i class="fas fa-key" style="color:#dc2626;"></i> Permissions ({{ $role->permissions->count() }})</h3>
            @if($role->permissions->count() > 0)
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                    @foreach($role->permissions->groupBy('module') as $module => $modulePerms)
                        <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:12px;">
                            <div style="font-size:12px; font-weight:700; color:#374151; text-transform:uppercase; margin-bottom:8px; letter-spacing:0.05em;">
                                {{ ucfirst($module) }}
                            </div>
                            @foreach($modulePerms as $permission)
                                <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:#374151; margin-bottom:4px;">
                                    <i class="fas fa-check-circle" style="color:#10b981; font-size:11px;"></i>
                                    {{ $permission->name }}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color:#9ca3af; font-size:14px;">No permissions assigned to this role.</p>
            @endif
        </div>

        {{-- Users --}}
        <div>
            <h3 style="{{ $section }}"><i class="fas fa-users" style="color:#dc2626;"></i> Assigned Users ({{ $role->users->count() }})</h3>

            {{-- Assign User Form --}}
            @if($availableUsers->count() > 0)
                <form method="POST" action="{{ route('roles.assign.user', $role) }}" style="margin-bottom:20px;">
                    @csrf
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <select name="user_id" required
                                style="flex:1; min-width:200px; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
                            <option value="">Select a user to assign...</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <button type="submit"
                                style="padding:8px 16px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border:none; border-radius:6px; font-size:14px; font-weight:600; cursor:pointer;">
                            <i class="fas fa-user-plus"></i> Assign User
                        </button>
                    </div>
                </form>
            @endif

            @if($role->users->count() > 0)
                @foreach($role->users as $user)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:8px;">
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
                                <button style="padding:5px 12px; background:#fee2e2; color:#991b1b; border:none; border-radius:5px; font-size:12px; cursor:pointer;">
                                    <i class="fas fa-user-minus"></i> Remove
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            @else
                <p style="color:#9ca3af; font-size:14px;">No users assigned to this role.</p>
            @endif
        </div>
    </div>

@endsection
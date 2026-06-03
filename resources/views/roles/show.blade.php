@extends('layouts.app')

@section('title', 'Role Details')

@section('content')
<div class="page-header">
    <h1>Role Details: {{ $role->name }}</h1>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="detail-section">
            <h3>Role Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Name:</label>
                    <span>{{ $role->name }}</span>
                </div>
                <div class="detail-item">
                    <label>Slug:</label>
                    <span><code>{{ $role->slug }}</code></span>
                </div>
                <div class="detail-item">
                    <label>Description:</label>
                    <span>{{ $role->description ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <label>Status:</label>
                    <span>
                        @if($role->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Permissions ({{ $role->permissions->count() }})</h3>
            @if($role->permissions->count() > 0)
                <div class="permissions-list">
                    @foreach($role->permissions->groupBy('module') as $module => $permissions)
                        <div class="permission-module">
                            <h4>{{ ucfirst($module) }}</h4>
                            <ul>
                                @foreach($permissions as $permission)
                                    <li>{{ $permission->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No permissions assigned.</p>
            @endif
        </div>

        <div class="detail-section">
            <h3>Users ({{ $role->users->count() }})</h3>
            
            {{-- Assign User Form --}}
            @if($availableUsers->count() > 0)
                <form method="POST" action="{{ route('roles.assign.user', $role) }}" style="margin-bottom: 20px;">
                    @csrf
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select name="user_id" required style="flex: 1; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px;">
                            <option value="">Select a user to assign...</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Assign User
                        </button>
                    </div>
                </form>
            @else
                <p class="text-muted" style="margin-bottom: 20px;">All users are already assigned to this role.</p>
            @endif

            @if($role->users->count() > 0)
                <div class="users-list">
                    @foreach($role->users as $user)
                        <div class="user-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #dc2626, #991b1b); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                <div class="user-info">
                                    <div class="user-name" style="font-weight: 600;">{{ $user->name }}</div>
                                    <div class="user-email" style="color: #6b7280; font-size: 14px;">{{ $user->email }}</div>
                                </div>
                            </div>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('roles.remove.user', [$role, $user]) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove {{ $user->name }} from this role?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-user-minus"></i> Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No users assigned to this role.</p>
            @endif
        </div>

        <div class="form-actions">
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Role
            </a>
        </div>
    </div>
</div>
@endsection

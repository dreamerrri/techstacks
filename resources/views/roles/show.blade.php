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
            @if($role->users->count() > 0)
                <div class="users-list">
                    @foreach($role->users as $user)
                        <div class="user-item">
                            <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                            <div class="user-info">
                                <div class="user-name">{{ $user->name }}</div>
                                <div class="user-email">{{ $user->email }}</div>
                            </div>
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

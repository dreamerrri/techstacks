@extends('layouts.app')

@section('title', 'Permission Details')

@section('content')
<div class="page-header">
    <h1>Permission Details: {{ $permission->name }}</h1>
    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="detail-section">
            <h3>Permission Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Name:</label>
                    <span>{{ $permission->name }}</span>
                </div>
                <div class="detail-item">
                    <label>Slug:</label>
                    <span><code>{{ $permission->slug }}</code></span>
                </div>
                <div class="detail-item">
                    <label>Module:</label>
                    <span>{{ ucfirst($permission->module) }}</span>
                </div>
                <div class="detail-item">
                    <label>Description:</label>
                    <span>{{ $permission->description ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <label>Status:</label>
                    <span>
                        @if($permission->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h3>Roles with this Permission ({{ $permission->roles->count() }})</h3>
            @if($permission->roles->count() > 0)
                <div class="roles-list">
                    @foreach($permission->roles as $role)
                        <div class="role-item">
                            <div class="role-info">
                                <div class="role-name">{{ $role->name }}</div>
                                <div class="role-slug"><code>{{ $role->slug }}</code></div>
                            </div>
                            <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-info">View Role</a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No roles have this permission.</p>
            @endif
        </div>

        <div class="form-actions">
            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Permission
            </a>
        </div>
    </div>
</div>
@endsection

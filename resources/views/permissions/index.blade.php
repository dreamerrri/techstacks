@extends('layouts.app')

@section('title', 'Permissions Management')

@section('content')
<div class="page-header">
    <h1>Permissions Management</h1>
    <a href="{{ route('permissions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Permission
    </a>
</div>

<div class="card">
    <div class="card-body">
        @foreach($permissions as $module => $modulePermissions)
            <div class="permission-module-card">
                <h3>{{ ucfirst($module) }}</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Roles</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modulePermissions as $permission)
                                <tr>
                                    <td>{{ $permission->name }}</td>
                                    <td><code>{{ $permission->slug }}</code></td>
                                    <td>{{ $permission->description ?? '-' }}</td>
                                    <td>{{ $permission->roles->count() }}</td>
                                    <td>
                                        @if($permission->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('permissions.show', $permission) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($permission->roles->count() == 0)
                                                <form action="{{ route('permissions.destroy', $permission) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this permission?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

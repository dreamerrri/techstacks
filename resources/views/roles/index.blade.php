@extends('layouts.app')

@section('title', 'Roles Management')
@section('breadcrumb')
    <span>Manage Users</span>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:500;">Roles</span>
@endsection
@section('content')

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <h2 style="margin:0; color:#1f2937; font-size:20px; font-weight:700;">Roles Management</h2>
        <a href="{{ route('roles.create') }}" class="btn btn btn-error btn-sm" style="font-size:14px; padding:8px 16px;">
            <i class="fas fa-plus"></i> Create Role
        </a>
    </div>

    @if(session('success'))
        <div class="badge badge badge-soft badge-success" style="margin-bottom:16px; padding:12px 16px; border-radius:8px; display:block;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="badge badge badge-soft badge-error" style="margin-bottom:16px; padding:12px 16px; border-radius:8px; display:block;">
            <i class="fas fa-times-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="aurora-card" style="padding:0; overflow:hidden;">

        {{-- Desktop Table --}}
        <div class="user-table-wrapper">
            <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:500px;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Name</th>
                        <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Slug</th>
                        <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Description</th>
                        <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Users</th>
                        <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Permissions</th>
                        <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Status</th>
                        <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:12px 16px; font-weight:600; color:#1f2937;">{{ $role->name }}</td>
                            <td style="padding:12px 16px;"><code>{{ $role->slug }}</code></td>
                            <td style="padding:12px 16px; color:#6b7280;">{{ $role->description ?? '—' }}</td>
                            <td style="padding:12px 16px; color:#6b7280;">{{ $role->users_count }}</td>
                            <td style="padding:12px 16px; color:#6b7280;">{{ $role->permissions->count() }}</td>
                            <td style="padding:12px 16px;">
                                @if($role->is_active)
                                    <span class="badge badge badge-soft badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                @else
                                    <span class="badge badge badge-soft badge-error"><i class="fas fa-times-circle"></i> Inactive</span>
                                @endif
                            </td>
                            <td style="padding:12px 16px;">
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <a href="{{ route('roles.show', $role) }}" class="btn btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($role->users_count == 0)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                              data-confirm="This role will be permanently deleted."
                                              data-confirm-title="Delete Role?"
                                              data-confirm-icon="warning"
                                              data-confirm-btn="Yes, delete">
                                            @csrf @method('DELETE')
                                            <button class="btn btn btn-error btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:40px; text-align:center; color:#9ca3af;">
                                <i class="fas fa-user-tag" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                                No roles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="user-mobile-cards" style="padding:16px;">
            @forelse($roles as $role)
                <div class="user-card">
                    <div class="user-card-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:700; flex-shrink:0;">
                                {{ strtoupper(substr($role->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $role->name }}</div>
                                <code style="font-size:11px; color:#6b7280;">{{ $role->slug }}</code>
                            </div>
                        </div>
                        @if($role->is_active)
                            <span class="badge badge badge-soft badge-success" style="white-space:nowrap;"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="badge badge badge-soft badge-error" style="white-space:nowrap;"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </div>

                    <div style="margin-top:8px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                        <span><i class="fas fa-users" style="width:14px;"></i> {{ $role->users_count }} users</span>
                        <span><i class="fas fa-key" style="width:14px;"></i> {{ $role->permissions->count() }} permissions</span>
                    </div>

                    @if($role->description)
                        <div style="margin-top:6px; font-size:12px; color:#9ca3af;">{{ $role->description }}</div>
                    @endif

                    <div class="user-card-meta">
                        <a href="{{ route('roles.show', $role) }}" class="btn btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @if($role->users_count == 0)
                            <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                  data-confirm="This role will be permanently deleted."
                                  data-confirm-title="Delete Role?"
                                  data-confirm-icon="warning"
                                  data-confirm-btn="Yes, delete">
                                @csrf @method('DELETE')
                                <button class="btn btn btn-error btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div style="padding:40px; text-align:center; color:#9ca3af;">
                    <i class="fas fa-user-tag" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                    No roles found.
                </div>
            @endforelse
        </div>

    </div>

@endsection
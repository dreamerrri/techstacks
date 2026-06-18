@extends('layouts.app')

@section('title', 'Permissions Management')
@section('breadcrumb')
    <span>Manage Users</span>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:500;">Permissions</span>
@endsection
@section('content')

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <h2 style="margin:0; color:#1f2937; font-size:20px; font-weight:700;">Permissions Management</h2>
        <a href="{{ route('permissions.create') }}" class="btn btn btn-error btn-sm" style="font-size:14px; padding:8px 16px;">
            <i class="fas fa-plus"></i> Create Permission
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

    @foreach($permissions as $module => $modulePermissions)
        <div style="margin-bottom:24px;">
            {{-- Module header --}}
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700; flex-shrink:0;">
                    {{ strtoupper(substr($module, 0, 1)) }}
                </div>
                <h3 style="margin:0; color:#1f2937; font-size:16px; font-weight:700;">{{ ucfirst($module) }}</h3>
                <span class="badge badge badge-soft badge-success" style="text-transform:none; letter-spacing:0; font-size:11px;">
                    {{ $modulePermissions->count() }}
                </span>
            </div>

            <div class="aurora-card" style="padding:0; overflow:hidden;">

                {{-- Desktop Table --}}
                <div class="user-table-wrapper">
                    <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:500px;">
                        <thead>
                            <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                                <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Name</th>
                                <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Slug</th>
                                <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Description</th>
                                <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Roles</th>
                                <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Status</th>
                                <th style="padding:12px 16px; text-align:left; color:#6b7280; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modulePermissions as $permission)
                                <tr style="border-bottom:1px solid #f3f4f6;">
                                    <td style="padding:12px 16px; font-weight:600; color:#1f2937;">{{ $permission->name }}</td>
                                    <td style="padding:12px 16px;"><code>{{ $permission->slug }}</code></td>
                                    <td style="padding:12px 16px; color:#6b7280;">{{ $permission->description ?? '—' }}</td>
                                    <td style="padding:12px 16px; color:#6b7280;">{{ $permission->roles->count() }}</td>
                                    <td style="padding:12px 16px;">
                                        @if($permission->is_active)
                                            <span class="badge badge badge-soft badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                        @else
                                            <span class="badge badge badge-soft badge-error"><i class="fas fa-times-circle"></i> Inactive</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px 16px;">
                                        <div style="display:flex; gap:6px; align-items:center;">
                                            <a href="{{ route('permissions.show', $permission) }}" class="btn btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($permission->roles->count() == 0)
                                                <form method="POST" action="{{ route('permissions.destroy', $permission) }}"
                                                      data-confirm="This permission will be permanently deleted."
                                                      data-confirm-title="Delete Permission?"
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="user-mobile-cards" style="padding:16px;">
                    @foreach($modulePermissions as $permission)
                        <div class="user-card">
                            <div class="user-card-header">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; flex-shrink:0;">
                                        <i class="fas fa-key" style="font-size:12px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $permission->name }}</div>
                                        <code style="font-size:11px; color:#6b7280;">{{ $permission->slug }}</code>
                                    </div>
                                </div>
                                @if($permission->is_active)
                                    <span class="badge badge badge-soft badge-success" style="white-space:nowrap;"><i class="fas fa-check-circle"></i> Active</span>
                                @else
                                    <span class="badge badge badge-soft badge-error" style="white-space:nowrap;"><i class="fas fa-times-circle"></i> Inactive</span>
                                @endif
                            </div>

                            <div style="margin-top:8px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                                <span><i class="fas fa-user-tag" style="width:14px;"></i> {{ $permission->roles->count() }} roles</span>
                            </div>

                            @if($permission->description)
                                <div style="margin-top:6px; font-size:12px; color:#9ca3af;">{{ $permission->description }}</div>
                            @endif

                            <div class="user-card-meta">
                                <a href="{{ route('permissions.show', $permission) }}" class="btn btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('permissions.edit', $permission) }}" class="btn btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                @if($permission->roles->count() == 0)
                                    <form method="POST" action="{{ route('permissions.destroy', $permission) }}"
                                          data-confirm="This permission will be permanently deleted."
                                          data-confirm-title="Delete Permission?"
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
                    @endforeach
                </div>

            </div>
        </div>
    @endforeach

@endsection
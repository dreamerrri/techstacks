@extends('layouts.app')

@section('title', 'Permissions Management')

@section('content')

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <h2 style="margin:0; color:#1f2937; font-size:20px;">Permissions Management</h2>
        <a href="{{ route('permissions.create') }}"
           style="padding:8px 16px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600;">
            <i class="fas fa-plus"></i> Create Permission
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

    @foreach($permissions as $module => $modulePermissions)
        <div style="margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700; flex-shrink:0;">
                    {{ strtoupper(substr($module, 0, 1)) }}
                </div>
                <h3 style="margin:0; color:#1f2937; font-size:16px; font-weight:700;">{{ ucfirst($module) }}</h3>
                <span style="padding:2px 8px; background:#fecaca; color:#991b1b; border-radius:20px; font-size:11px; font-weight:600;">
                    {{ $modulePermissions->count() }}
                </span>
            </div>

            {{-- Desktop Table --}}
            <div class="card" style="padding:0; overflow:hidden;">
                <div class="user-table-wrapper">
                    <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:500px;">
                        <thead>
                            <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                                <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Name</th>
                                <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Slug</th>
                                <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Description</th>
                                <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Roles</th>
                                <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Status</th>
                                <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modulePermissions as $permission)
                                <tr style="border-bottom:1px solid #e5e7eb;">
                                    <td style="padding:12px; font-weight:600; color:#1f2937;">{{ $permission->name }}</td>
                                    <td style="padding:12px;"><code style="background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:12px; color:#374151;">{{ $permission->slug }}</code></td>
                                    <td style="padding:12px; color:#6b7280;">{{ $permission->description ?? '—' }}</td>
                                    <td style="padding:12px; color:#6b7280;">{{ $permission->roles->count() }}</td>
                                    <td style="padding:12px;">
                                        @if($permission->is_active)
                                            <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#d1fae5; color:#065f46;">Active</span>
                                        @else
                                            <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fee2e2; color:#991b1b;">Inactive</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px;">
                                        <div style="display:flex; gap:6px; align-items:center;">
                                            <a href="{{ route('permissions.show', $permission) }}"
                                               style="padding:5px 10px; background:#eff6ff; color:#1d4ed8; border-radius:5px; font-size:12px; text-decoration:none;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('permissions.edit', $permission) }}"
                                               style="padding:5px 10px; background:#fef3c7; color:#92400e; border-radius:5px; font-size:12px; text-decoration:none;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($permission->roles->count() == 0)
                                                <form method="POST" action="{{ route('permissions.destroy', $permission) }}"
                                                      data-confirm="This permission will be permanently deleted."
                                                      data-confirm-title="Delete Permission?"
                                                      data-confirm-icon="warning"
                                                      data-confirm-btn="Yes, delete">
                                                    @csrf @method('DELETE')
                                                    <button style="padding:5px 10px; background:#fee2e2; color:#991b1b; border:none; border-radius:5px; font-size:12px; cursor:pointer;">
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
                                    <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700; flex-shrink:0;">
                                        <i class="fas fa-key" style="font-size:12px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $permission->name }}</div>
                                        <code style="font-size:11px; color:#6b7280;">{{ $permission->slug }}</code>
                                    </div>
                                </div>
                                @if($permission->is_active)
                                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#d1fae5; color:#065f46; white-space:nowrap;">Active</span>
                                @else
                                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fee2e2; color:#991b1b; white-space:nowrap;">Inactive</span>
                                @endif
                            </div>

                            <div style="margin-top:8px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                                <span><i class="fas fa-user-tag" style="width:14px;"></i> {{ $permission->roles->count() }} roles</span>
                            </div>

                            @if($permission->description)
                                <div style="margin-top:6px; font-size:12px; color:#9ca3af;">{{ $permission->description }}</div>
                            @endif

                            <div style="margin-top:10px; padding-top:10px; border-top:1px solid #f3f4f6; display:flex; gap:8px; flex-wrap:wrap;">
                                <a href="{{ route('permissions.show', $permission) }}"
                                   style="padding:5px 12px; background:#eff6ff; color:#1d4ed8; border-radius:5px; font-size:12px; text-decoration:none;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('permissions.edit', $permission) }}"
                                   style="padding:5px 12px; background:#fef3c7; color:#92400e; border-radius:5px; font-size:12px; text-decoration:none;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                @if($permission->roles->count() == 0)
                                    <form method="POST" action="{{ route('permissions.destroy', $permission) }}"
                                          data-confirm="This permission will be permanently deleted."
                                          data-confirm-title="Delete Permission?"
                                          data-confirm-icon="warning"
                                          data-confirm-btn="Yes, delete">
                                        @csrf @method('DELETE')
                                        <button style="padding:5px 12px; background:#fee2e2; color:#991b1b; border:none; border-radius:5px; font-size:12px; cursor:pointer;">
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
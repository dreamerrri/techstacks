@extends('layouts.app')

@section('title', 'Roles Management')

@section('content')

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <h2 style="margin:0; color:#1f2937; font-size:20px;">Roles Management</h2>
        <a href="{{ route('roles.create') }}"
           style="padding:8px 16px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600;">
            <i class="fas fa-plus"></i> Create Role
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

    {{-- Desktop Table --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div class="user-table-wrapper">
            <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:500px;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Name</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Slug</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Description</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Users</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Permissions</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Status</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:12px; font-weight:600; color:#1f2937;">{{ $role->name }}</td>
                            <td style="padding:12px;"><code style="background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:12px; color:#374151;">{{ $role->slug }}</code></td>
                            <td style="padding:12px; color:#6b7280;">{{ $role->description ?? '—' }}</td>
                            <td style="padding:12px; color:#6b7280;">{{ $role->users_count }}</td>
                            <td style="padding:12px; color:#6b7280;">{{ $role->permissions->count() }}</td>
                            <td style="padding:12px;">
                                @if($role->is_active)
                                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#d1fae5; color:#065f46;">Active</span>
                                @else
                                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fee2e2; color:#991b1b;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding:12px;">
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <a href="{{ route('roles.show', $role) }}"
                                       style="padding:5px 10px; background:#eff6ff; color:#1d4ed8; border-radius:5px; font-size:12px; text-decoration:none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('roles.edit', $role) }}"
                                       style="padding:5px 10px; background:#fef3c7; color:#92400e; border-radius:5px; font-size:12px; text-decoration:none;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($role->users_count == 0)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                              data-confirm="This role will be permanently deleted."
                                              data-confirm-title="Delete Role?"
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
                            <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#d1fae5; color:#065f46; white-space:nowrap;">Active</span>
                        @else
                            <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fee2e2; color:#991b1b; white-space:nowrap;">Inactive</span>
                        @endif
                    </div>

                    <div style="margin-top:8px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                        <span><i class="fas fa-users" style="width:14px;"></i> {{ $role->users_count }} users</span>
                        <span><i class="fas fa-key" style="width:14px;"></i> {{ $role->permissions->count() }} permissions</span>
                    </div>

                    @if($role->description)
                        <div style="margin-top:6px; font-size:12px; color:#9ca3af;">{{ $role->description }}</div>
                    @endif

                    <div style="margin-top:10px; padding-top:10px; border-top:1px solid #f3f4f6; display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="{{ route('roles.show', $role) }}"
                           style="padding:5px 12px; background:#eff6ff; color:#1d4ed8; border-radius:5px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('roles.edit', $role) }}"
                           style="padding:5px 12px; background:#fef3c7; color:#92400e; border-radius:5px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @if($role->users_count == 0)
                            <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                  data-confirm="This role will be permanently deleted."
                                  data-confirm-title="Delete Role?"
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
            @empty
                <div style="padding:40px; text-align:center; color:#9ca3af;">
                    <i class="fas fa-user-tag" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                    No roles found.
                </div>
            @endforelse
        </div>
    </div>

@endsection
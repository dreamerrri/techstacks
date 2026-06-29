@extends('layouts.app')

@section('title', 'All Users')
@section('breadcrumb')
    <span>Manage Users</span>
    <i class="icon-[ph--caret-right-fill]" style="font-size:11px;"></i>
    <span style="color:white; font-weight:500;">Users</span>
@endsection
@section('content')

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div>
            <span class="badge badge badge-soft badge-success" style="margin-bottom:8px;">
                <i class="fas fa-users-cog"></i> User Management
            </span>
            <p style="color:#6b7280; margin:0;">Manage system accounts, roles, and access.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5" style="margin-bottom:24px;">
        <div class="card bg-base-100 shadow-sm">
            <div class="aurora-stat-icon" style="color:#dc2626; background:rgba(220,38,38,0.1);">
                <i class="fas fa-users"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\User::count() }}</div>
            <div class="aurora-stat-label">Total Users</div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="aurora-stat-icon" style="color:#991b1b; background:rgba(153,27,27,0.1);">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\User::where('role','admin')->count() }}</div>
            <div class="aurora-stat-label">Admins</div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="aurora-stat-icon" style="color:#f59e0b; background:rgba(245,158,11,0.1);">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\User::where('role','hr')->count() }}</div>
            <div class="aurora-stat-label">HR Personnel</div>
        </div>
        <div class="card bg-base-100 shadow-sm">
            <div class="aurora-stat-icon" style="color:#10b981; background:rgba(16,185,129,0.1);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\User::where('is_active', true)->count() }}</div>
            <div class="aurora-stat-label">Active Accounts</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card bg-base-100 shadow-sm" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">

        {{-- Sticky header: title + search --}}
        <div style="position:sticky; top:0; z-index:10; background:white; padding:20px 28px 0; border-radius:20px 20px 0 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
                <h2 class="card bg-base-100 shadow-sm-title" style="margin:0; font-size:15px;">
                    <i class="fas fa-list"></i> User Accounts
                </h2>
            </div>

            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('users.index') }}"
                  style="display:flex; flex-wrap:wrap; gap:10px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name or email..."
                       style="flex:1; min-width:160px; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                <select name="role" style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                    <option value="">All Roles</option>
                    <option value="admin"    {{ request('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                    <option value="hr"       {{ request('role') === 'hr'       ? 'selected' : '' }}>HR</option>
                    <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                </select>
                <select name="status" style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn btn-error btn-sm" style="padding:8px 20px; font-size:14px;">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request()->hasAny(['search','role','status']))
                    <a href="{{ route('users.index') }}"
                       style="padding:8px 16px; background:#f3f4f6; color:#6b7280; border-radius:8px; text-decoration:none; font-size:14px;">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="table-responsive style="overflow-y:auto; max-height:53vh; padding:0 28px;">
            <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:600px;">
                <thead style="position:sticky; top:0; z-index:5;">
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Name</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Email</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Role</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Status</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Last Login</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $roleStyles = [
                                'admin'    => 'background:#fecaca; color:#991b1b;',
                                'hr'       => 'background:#fef3c7; color:#92400e;',
                                'employee' => 'background:#dbeafe; color:#1e40af;',
                            ];
                        @endphp
                        <tr style="border-bottom:1px solid #f3f4f6;">

                            {{-- Name --}}
                            <td style="padding:12px; font-weight:600; color:#1a1a2e;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700; flex-shrink:0;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    @if($user->employee)
                                        <a href="{{ route('employees.show', $user->employee) }}"
                                           style="color:#1a1a2e; text-decoration:none; font-weight:600;">
                                            {{ $user->name }}
                                        </a>
                                    @else
                                        {{ $user->name }}
                                    @endif
                                    @if($user->id === auth()->id())
                                        <span class="badge badge badge-soft badge-success" style="font-size:10px; padding:2px 8px; margin:0; text-transform:none; letter-spacing:0;">You</span>
                                    @endif
                                </div>
                            </td>

                            <td style="padding:12px; color:#6b7280;">{{ $user->email }}</td>

                            {{-- Role selector --}}
                            <td style="padding:12px;">
                                <form method="POST" action="{{ route('users.role', $user) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <select name="role" onchange="this.form.submit()"
                                            {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                            style="border:1px solid #e5e7eb; border-radius:20px; padding:3px 10px; font-size:12px; font-weight:600; cursor:pointer; {{ $roleStyles[$user->role] ?? '' }}">
                                        <option value="admin"    {{ $user->role === 'admin'    ? 'selected' : '' }}>Admin</option>
                                        <option value="hr"       {{ $user->role === 'hr'       ? 'selected' : '' }}>HR</option>
                                        <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                                    </select>
                                </form>
                            </td>

                            {{-- Status --}}
                            <td style="padding:12px;">
                                @if($user->is_active)
                                    <span class="badge badge badge-soft badge-success">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="badge badge badge-soft badge-error">
                                        <i class="fas fa-times-circle"></i> Inactive
                                    </span>
                                @endif
                            </td>

                            <td style="padding:12px; color:#6b7280; font-size:13px;">
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}
                            </td>

                            {{-- Toggle action --}}
                            <td style="padding:12px;">
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.toggle', $user) }}"
                                          data-confirm="{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}"
                                          data-confirm-title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                          data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                          data-confirm-btn="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}">
                                        @csrf @method('PATCH')
                                        <button style="padding:5px 10px; background:{{ $user->is_active ? '#fecaca' : '#d1fae5' }}; color:{{ $user->is_active ? '#991b1b' : '#065f46' }}; border:none; border-radius:8px; font-size:12px; cursor:pointer; font-weight:600;">
                                            <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @else
                                    <span style="color:#9ca3af; font-size:12px;">—</span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:40px; text-align:center; color:#9ca3af;">
                                <i class="fas fa-users" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="user-mobile-cards" style="padding:16px;">
            @forelse($users as $user)
                @php
                    $roleStyles = [
                        'admin'    => 'background:#fecaca; color:#991b1b;',
                        'hr'       => 'background:#fef3c7; color:#92400e;',
                        'employee' => 'background:#dbeafe; color:#1e40af;',
                    ];
                @endphp
                <div class="user-card">
                    <div class="user-card-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:700; flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1a1a2e; font-size:14px;">
                                    @if($user->employee)
                                        <a href="{{ route('employees.show', $user->employee) }}"
                                           style="color:#1a1a2e; text-decoration:none; font-weight:600;">
                                            {{ $user->name }}
                                        </a>
                                    @else
                                        {{ $user->name }}
                                    @endif
                                    @if($user->id === auth()->id())
                                        <span class="badge badge badge-soft badge-success" style="font-size:10px; padding:2px 8px; margin:0 0 0 4px; text-transform:none; letter-spacing:0; vertical-align:middle;">You</span>
                                    @endif
                                </div>
                                <div style="font-size:12px; color:#6b7280;">{{ $user->email }}</div>
                            </div>
                        </div>

                        @if($user->is_active)
                            <span class="badge badge badge-soft badge-success" style="white-space:nowrap;">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        @else
                            <span class="badge badge badge-soft badge-error" style="white-space:nowrap;">
                                <i class="fas fa-times-circle"></i> Inactive
                            </span>
                        @endif
                    </div>

                    <div class="user-card-meta">
                        <form method="POST" action="{{ route('users.role', $user) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <select name="role" onchange="this.form.submit()"
                                    {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                    style="border:1px solid #e5e7eb; border-radius:20px; padding:3px 10px; font-size:12px; font-weight:600; cursor:pointer; {{ $roleStyles[$user->role] ?? '' }}">
                                <option value="admin"    {{ $user->role === 'admin'    ? 'selected' : '' }}>Admin</option>
                                <option value="hr"       {{ $user->role === 'hr'       ? 'selected' : '' }}>HR</option>
                                <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                            </select>
                        </form>

                        <span style="font-size:11px; color:#9ca3af;">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never logged in' }}
                        </span>

                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle', $user) }}"
                                  data-confirm="{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}"
                                  data-confirm-title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                  data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                  data-confirm-btn="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}">
                                @csrf @method('PATCH')
                                <button style="padding:5px 12px; background:{{ $user->is_active ? '#fecaca' : '#d1fae5' }}; color:{{ $user->is_active ? '#991b1b' : '#065f46' }}; border:none; border-radius:8px; font-size:12px; cursor:pointer; font-weight:600;">
                                    <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        @else
                            <span style="color:#9ca3af; font-size:12px;">—</span>
                        @endif
                    </div>
                </div>
            @empty
                <div style="padding:40px; text-align:center; color:#9ca3af;">
                    <i class="fas fa-users" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                    No users found.
                </div>
            @endforelse
        </div>

    </div>
    <div style="padding:16px 28px; border-top:1px solid #e5e7eb;">{{ $users->links() }}</div>
@endsection
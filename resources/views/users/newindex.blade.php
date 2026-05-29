@extends('layouts.app')

@section('title', 'All Users')

@section('content')

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div style="display:inline-block; background:#fecaca; color:#991b1b; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
                <i class="fas fa-users-cog"></i> User Management
            </div>
            <p style="color:#6b7280; margin:0;">Manage system accounts, roles, and access.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:20px; margin-bottom:30px;">
        <div class="card" style="text-align:center; margin-bottom:0;">
            <div style="font-size:28px; color:#dc2626; margin-bottom:8px;"><i class="fas fa-users"></i></div>
            <div style="font-size:24px; font-weight:700;">{{ \App\Models\User::count() }}</div>
            <div style="color:#6b7280; font-size:14px;">Total Users</div>
        </div>
        <div class="card" style="text-align:center; margin-bottom:0;">
            <div style="font-size:28px; color:#991b1b; margin-bottom:8px;"><i class="fas fa-user-shield"></i></div>
            <div style="font-size:24px; font-weight:700;">{{ \App\Models\User::where('role','admin')->count() }}</div>
            <div style="color:#6b7280; font-size:14px;">Admins</div>
        </div>
        <div class="card" style="text-align:center; margin-bottom:0;">
            <div style="font-size:28px; color:#fbbf24; margin-bottom:8px;"><i class="fas fa-user-tie"></i></div>
            <div style="font-size:24px; font-weight:700;">{{ \App\Models\User::where('role','hr')->count() }}</div>
            <div style="color:#6b7280; font-size:14px;">HR Personnel</div>
        </div>
        <div class="card" style="text-align:center; margin-bottom:0;">
            <div style="font-size:28px; color:#10b981; margin-bottom:8px;"><i class="fas fa-check-circle"></i></div>
            <div style="font-size:24px; font-weight:700;">{{ \App\Models\User::where('is_active', true)->count() }}</div>
            <div style="color:#6b7280; font-size:14px;">Active Accounts</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">

        {{-- Sticky header: title + search --}}
        <div style="position:sticky; top:0; z-index:10; background:white; padding:20px 25px 0; border-radius:10px 10px 0 0; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">User Accounts</h2>
            </div>

            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('users.index') }}"
                  style="display:flex; flex-wrap:wrap; gap:10px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name or email..."
                       style="flex:1; min-width:160px; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
                <select name="role" style="border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
                    <option value="">All Roles</option>
                    <option value="admin"    {{ request('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                    <option value="hr"       {{ request('role') === 'hr'       ? 'selected' : '' }}>HR</option>
                    <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                </select>
                <select name="status" style="border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit"
                        style="padding:8px 20px; background:#dc2626; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px;">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request()->hasAny(['search','role','status']))
                    <a href="{{ route('users.index') }}"
                       style="padding:8px 16px; background:#f3f4f6; color:#6b7280; border-radius:6px; text-decoration:none; font-size:14px;">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Desktop Table — scrollable body --}}
        <div class="user-table-wrapper" style="overflow-y:auto; max-height:62vh; padding:0 25px;">
            <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:600px;">
                <thead style="position:sticky; top:0; z-index:5;">
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Name</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Email</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Role</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Status</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Last Login</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:12px; font-weight:600; color:#1f2937;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700; flex-shrink:0;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span style="font-size:11px; background:#dbeafe; color:#1e40af; padding:2px 8px; border-radius:10px;">You</span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding:12px; color:#6b7280;">{{ $user->email }}</td>
                            <td style="padding:12px;">
                                @php
                                    $roleColors = [
                                        'admin'    => 'background:#fecaca; color:#991b1b;',
                                        'hr'       => 'background:#fef3c7; color:#92400e;',
                                        'employee' => 'background:#dbeafe; color:#1e40af;',
                                    ];
                                @endphp
                                <form method="POST" action="{{ route('users.role', $user) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <select name="role" onchange="this.form.submit()"
                                            {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                            style="border:1px solid #e5e7eb; border-radius:20px; padding:3px 10px; font-size:12px; font-weight:600; cursor:pointer; {{ $roleColors[$user->role] ?? '' }}">
                                        <option value="admin"    {{ $user->role === 'admin'    ? 'selected' : '' }}>Admin</option>
                                        <option value="hr"       {{ $user->role === 'hr'       ? 'selected' : '' }}>HR</option>
                                        <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                                    </select>
                                </form>
                            </td>
                            <td style="padding:12px;">
                                @if($user->is_active)
                                    <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; background:#d1fae5; color:#065f46;">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @else
                                    <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; background:#fecaca; color:#991b1b;">
                                        <i class="fas fa-times-circle"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td style="padding:12px; color:#6b7280; font-size:13px;">
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                            </td>
                            <td style="padding:12px;">
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.toggle', $user) }}"
                                          data-confirm="{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}"
                                          data-confirm-title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                          data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                          data-confirm-btn="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}">
                                        @csrf @method('PATCH')
                                        <button style="padding:5px 10px; background:{{ $user->is_active ? '#fecaca' : '#d1fae5' }}; color:{{ $user->is_active ? '#991b1b' : '#065f46' }}; border:none; border-radius:5px; font-size:12px; cursor:pointer;">
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
                    $roleColors = [
                        'admin'    => 'background:#fecaca; color:#991b1b;',
                        'hr'       => 'background:#fef3c7; color:#92400e;',
                        'employee' => 'background:#dbeafe; color:#1e40af;',
                    ];
                @endphp
                <div class="user-card">
                    {{-- Card header: avatar + name/email + status badge --}}
                    <div class="user-card-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:700; flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1f2937; font-size:14px;">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span style="font-size:11px; background:#dbeafe; color:#1e40af; padding:2px 8px; border-radius:10px; margin-left:4px;">You</span>
                                    @endif
                                </div>
                                <div style="font-size:12px; color:#6b7280;">{{ $user->email }}</div>
                            </div>
                        </div>
                        @if($user->is_active)
                            <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#d1fae5; color:#065f46; white-space:nowrap;">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        @else
                            <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fecaca; color:#991b1b; white-space:nowrap;">
                                <i class="fas fa-times-circle"></i> Inactive
                            </span>
                        @endif
                    </div>

                    {{-- Card footer: role selector + last login + action --}}
                    <div class="user-card-meta">
                        <form method="POST" action="{{ route('users.role', $user) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <select name="role" onchange="this.form.submit()"
                                    {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                    style="border:1px solid #e5e7eb; border-radius:20px; padding:3px 10px; font-size:12px; font-weight:600; cursor:pointer; {{ $roleColors[$user->role] ?? '' }}">
                                <option value="admin"    {{ $user->role === 'admin'    ? 'selected' : '' }}>Admin</option>
                                <option value="hr"       {{ $user->role === 'hr'       ? 'selected' : '' }}>HR</option>
                                <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                            </select>
                        </form>

                        <span style="font-size:11px; color:#9ca3af;">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never logged in' }}
                        </span>

                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle', $user) }}"
                                  data-confirm="{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}"
                                  data-confirm-title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                  data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                  data-confirm-btn="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}">
                                @csrf @method('PATCH')
                                <button style="padding:5px 12px; background:{{ $user->is_active ? '#fecaca' : '#d1fae5' }}; color:{{ $user->is_active ? '#991b1b' : '#065f46' }}; border:none; border-radius:5px; font-size:12px; cursor:pointer;">
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

        <div style="padding:16px 25px; border-top:1px solid #e5e7eb;">{{ $users->links() }}</div>
    </div>

@endsection
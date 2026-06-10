@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div>
            <span class="aurora-badge aurora-badge-admin" style="margin-bottom:8px;">
                <i class="fas fa-history"></i> Audit Logs
            </span>
            <p style="color:#6b7280; margin:0;">Track and review all system activity and changes.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="aurora-card" style="margin-bottom:18px;">
        <h2 class="aurora-card-title"><i class="fas fa-filter"></i> Filters</h2>
        <form method="GET" action="{{ route('audit-logs.index') }}">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px; align-items:flex-end;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Module</label>
                    <select name="module" style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                        <option value="">All Modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ ucfirst($module) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Action</label>
                    <select name="action" style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; box-sizing:border-box; outline:none;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           style="width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; box-sizing:border-box; outline:none;">
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-danger btn-sm" style="flex:1; padding:8px 16px; font-size:14px;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('audit-logs.index') }}"
                       style="flex:1; padding:8px 16px; background:#f3f4f6; color:#6b7280; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; text-align:center;">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table Mobile Cards --}}
    <div class="aurora-card" style="padding:0; overflow:hidden;">

        {{-- Desktop Table --}}
  
            <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:500px;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Date/Time</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">User</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Action</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Module</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Description</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">IP Address</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $actionColors = [
                                'create' => ['bg' => '#d1fae5', 'color' => '#065f46'],
                                'update' => ['bg' => '#fef3c7', 'color' => '#92400e'],
                                'delete' => ['bg' => '#fee2e2', 'color' => '#991b1b'],
                                'login'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8'],
                                'logout' => ['bg' => '#f3f4f6', 'color' => '#374151'],
                            ];
                            $ac = $actionColors[strtolower($log->action)] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
                        @endphp
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:12px; color:#6b7280; font-size:13px; white-space:nowrap;">
                                {{ $log->created_at->format('M d, Y') }}<br>
                                <span style="font-size:11px;">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td style="padding:12px; font-weight:600; color:#1a1a2e;">
                                {{ $log->user?->name ?? '—' }}
                            </td>
                            <td style="padding:12px;">
                                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:{{ $ac['bg'] }}; color:{{ $ac['color'] }};">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td style="padding:12px; color:#6b7280;">{{ ucfirst($log->module) }}</td>
                            <td style="padding:12px; color:#6b7280; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ $log->description }}
                            </td>
                            <td style="padding:12px; color:#6b7280; font-family:monospace; font-size:12px;">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td style="padding:12px;">
                                <a href="{{ route('audit-logs.show', $log) }}"
                                   style="padding:5px 10px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:40px; text-align:center; color:#9ca3af;">
                                <i class="fas fa-history" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                                No audit logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="user-mobile-cards" style="padding:16px;">
            @forelse($logs as $log)
                @php
                    $actionColors = [
                        'create' => ['bg' => '#d1fae5', 'color' => '#065f46'],
                        'update' => ['bg' => '#fef3c7', 'color' => '#92400e'],
                        'delete' => ['bg' => '#fee2e2', 'color' => '#991b1b'],
                        'login'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8'],
                        'logout' => ['bg' => '#f3f4f6', 'color' => '#374151'],
                    ];
                    $ac = $actionColors[strtolower($log->action)] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
                @endphp
                <div class="user-card">
                    <div class="user-card-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#6b7280,#374151); display:flex; align-items:center; justify-content:center; color:white; font-size:13px; font-weight:700; flex-shrink:0;">
                                {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1a1a2e; font-size:14px;">{{ $log->user?->name ?? 'System' }}</div>
                                <div style="font-size:11px; color:#9ca3af;">{{ $log->created_at->format('M d, Y H:i:s') }}</div>
                            </div>
                        </div>
                        <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:{{ $ac['bg'] }}; color:{{ $ac['color'] }}; white-space:nowrap;">
                            {{ ucfirst($log->action) }}
                        </span>
                    </div>

                    <div style="margin-top:8px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                        <span><i class="fas fa-cube" style="width:14px;"></i> {{ ucfirst($log->module) }}</span>
                        <span><i class="fas fa-network-wired" style="width:14px;"></i> {{ $log->ip_address ?? '—' }}</span>
                    </div>

                    <div style="margin-top:6px; font-size:12px; color:#9ca3af;">{{ $log->description }}</div>

                    <div class="user-card-meta">
                        <a href="{{ route('audit-logs.show', $log) }}"
                           style="padding:5px 12px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            @empty
                <div style="padding:40px; text-align:center; color:#9ca3af;">
                    <i class="fas fa-history" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                    No audit logs found.
                </div>
            @endforelse
        </div>

        <div style="padding:16px 28px; border-top:1px solid #e5e7eb;">{{ $logs->links() }}</div>
    </div>

@endsection
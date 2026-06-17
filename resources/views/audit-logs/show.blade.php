@extends('layouts.app')

@section('title', 'Audit Log Detail')

@section('breadcrumb')
    <span>Monitoring</span>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('audit-logs.index') }}" style="color:rgba(255,255,255,0.7); text-decoration:none;">Audit Logs</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:500;">Log #{{ $auditLog->id }}</span>
@endsection

@section('content')

    <div style="margin-bottom:24px;">
        <span class="aurora-badge aurora-badge-admin" style="margin-bottom:8px;">
            <i class="fas fa-history"></i> Audit Log Detail
        </span>
        <p style="color:#6b7280; margin:0;">Detailed view of a single audit log entry.</p>
    </div>

    <div class="aurora-card" style="max-width:720px;">
        @php
            $actionColors = [
                'create' => ['bg' => '#d1fae5', 'color' => '#065f46'],
                'update' => ['bg' => '#fef3c7', 'color' => '#92400e'],
                'delete' => ['bg' => '#fee2e2', 'color' => '#991b1b'],
                'login'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8'],
                'logout' => ['bg' => '#f3f4f6', 'color' => '#374151'],
            ];
            $ac = $actionColors[strtolower($auditLog->action)] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
        @endphp

        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase; width:160px;">Date / Time</td>
                <td style="padding:12px 16px; color:#1a1a2e;">{{ $auditLog->created_at->format('M d, Y h:i:s A') }}</td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase;">User</td>
                <td style="padding:12px 16px; color:#1a1a2e; font-weight:600;">{{ $auditLog->user?->name ?? '—' }}</td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase;">Action</td>
                <td style="padding:12px 16px;">
                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:{{ $ac['bg'] }}; color:{{ $ac['color'] }};">
                        {{ ucfirst($auditLog->action) }}
                    </span>
                </td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase;">Module</td>
                <td style="padding:12px 16px; color:#1a1a2e;">{{ ucfirst($auditLog->module) }}</td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase;">Description</td>
                <td style="padding:12px 16px; color:#1a1a2e;">{{ $auditLog->description }}</td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase;">IP Address</td>
                <td style="padding:12px 16px; color:#1a1a2e; font-family:monospace;">{{ $auditLog->ip_address ?? '—' }}</td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase;">User Agent</td>
                <td style="padding:12px 16px; color:#6b7280; font-size:12px; word-break:break-all;">{{ $auditLog->user_agent ?? '—' }}</td>
            </tr>
            @if($auditLog->old_values)
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase;">Old Values</td>
                <td style="padding:12px 16px;">
                    <pre style="margin:0; font-size:12px; background:#f9fafb; padding:10px; border-radius:8px; overflow-x:auto;">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                </td>
            </tr>
            @endif
            @if($auditLog->new_values)
            <tr>
                <td style="padding:12px 16px; color:#9ca3af; font-weight:600; font-size:12px; text-transform:uppercase;">New Values</td>
                <td style="padding:12px 16px;">
                    <pre style="margin:0; font-size:12px; background:#f9fafb; padding:10px; border-radius:8px; overflow-x:auto;">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                </td>
            </tr>
            @endif
        </table>

        <div style="padding:16px; border-top:1px solid #f3f4f6;">
            <a href="{{ route('audit-logs.index') }}"
               style="padding:8px 16px; background:#f3f4f6; color:#374151; border-radius:8px; text-decoration:none; font-size:13px; font-weight:600;">
                <i class="fas fa-arrow-left"></i> Back to Audit Logs
            </a>
        </div>
    </div>

@endsection 
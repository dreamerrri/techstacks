@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('content')

    @php
        $label   = "display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;";
        $section = "font-size:16px; font-weight:700; color:#1f2937; border-bottom:2px solid #fecaca; padding-bottom:8px; margin-bottom:16px;";
        $actionColors = [
            'create' => ['bg' => '#d1fae5', 'color' => '#065f46'],
            'update' => ['bg' => '#fef3c7', 'color' => '#92400e'],
            'delete' => ['bg' => '#fee2e2', 'color' => '#991b1b'],
            'login'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8'],
            'logout' => ['bg' => '#f3f4f6', 'color' => '#374151'],
        ];
        $ac = $actionColors[strtolower($auditLog->action)] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
    @endphp

    <div style="margin-bottom:20px;">
        <a href="{{ route('audit-logs.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Audit Logs
        </a>
    </div>

    <div class="card">
        {{-- Header --}}
        <div style="display:flex; align-items:center; gap:14px; margin-bottom:24px;">
            <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#6b7280,#374151); display:flex; align-items:center; justify-content:center; color:white; font-size:20px; font-weight:700; flex-shrink:0;">
                {{ $auditLog->user ? strtoupper(substr($auditLog->user->name, 0, 1)) : 'S' }}
            </div>
            <div>
                <h2 style="margin:0; color:#1f2937; font-size:20px;">
                    {{ $auditLog->user?->name ?? 'System' }}
                </h2>
                <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                    <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; background:{{ $ac['bg'] }}; color:{{ $ac['color'] }};">
                        {{ ucfirst($auditLog->action) }}
                    </span>
                    <span style="font-size:13px; color:#9ca3af;">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</span>
                </div>
            </div>
        </div>

        {{-- Log Info --}}
        <div style="margin-bottom:32px;">
            <h3 style="{{ $section }}"><i class="fas fa-info-circle" style="color:#dc2626;"></i> Log Information</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                <div>
                    <span style="{{ $label }}">User</span>
                    <span style="font-size:14px; color:#374151;">
                        @if($auditLog->user)
                            {{ $auditLog->user->name }}<br>
                            <span style="font-size:12px; color:#6b7280;">{{ $auditLog->user->email }}</span>
                        @else
                            System
                        @endif
                    </span>
                </div>
                <div>
                    <span style="{{ $label }}">Action</span>
                    <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; background:{{ $ac['bg'] }}; color:{{ $ac['color'] }};">
                        {{ ucfirst($auditLog->action) }}
                    </span>
                </div>
                <div>
                    <span style="{{ $label }}">Module</span>
                    <span style="font-size:14px; color:#374151;">{{ ucfirst($auditLog->module) }}</span>
                </div>
                <div>
                    <span style="{{ $label }}">Date / Time</span>
                    <span style="font-size:14px; color:#374151;">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</span>
                </div>
                <div>
                    <span style="{{ $label }}">IP Address</span>
                    <span style="font-size:14px; color:#374151; font-family:monospace;">{{ $auditLog->ip_address ?? '—' }}</span>
                </div>
                <div>
                    <span style="{{ $label }}">Description</span>
                    <span style="font-size:14px; color:#374151;">{{ $auditLog->description }}</span>
                </div>
                @if($auditLog->user_agent)
                    <div style="grid-column: 1 / -1;">
                        <span style="{{ $label }}">User Agent</span>
                        <span style="font-size:12px; color:#6b7280; word-break:break-all;">{{ $auditLog->user_agent }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Old Values --}}
        @if($auditLog->old_values)
            <div style="margin-bottom:32px;">
                <h3 style="{{ $section }}"><i class="fas fa-history" style="color:#dc2626;"></i> Old Values</h3>
                <pre style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px; font-size:13px; color:#374151; overflow-x:auto; margin:0;">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        {{-- New Values --}}
        @if($auditLog->new_values)
            <div>
                <h3 style="{{ $section }}"><i class="fas fa-edit" style="color:#dc2626;"></i> New Values</h3>
                <pre style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px; font-size:13px; color:#374151; overflow-x:auto; margin:0;">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>

@endsection
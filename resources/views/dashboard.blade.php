@extends('layouts.app')

@section('title')
    @if($user->role === 'admin') Admin Dashboard
    @elseif($user->role === 'hr') HR Dashboard
    @else Dashboard
    @endif
@endsection

@section('content')

@php
    $isAdmin = $user->role === 'admin';
    $isHR    = $user->role === 'hr';
    $color   = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
@endphp

{{-- Role access badge --}}
@if($isAdmin)
    <span class="role-access-badge" style="background:#fecaca; color:#991b1b;">
        <i class="fas fa-shield-alt"></i> Administrator Access
    </span>
@elseif($isHR)
    <span class="role-access-badge" style="background:#bfdbfe; color:#1e40af;">
        <i class="fas fa-user-tie"></i> HR Department Access
    </span>
@endif 

<div class="welcome-message">
    Welcome, {{ $user->name }}!
    @if($isAdmin) You have full administrative access.
    @elseif($isHR) You have HR access privileges.
    @endif
</div>

{{-- Stats --}}
<div class="stats-grid">
    @foreach($stats as $stat)
        <div class="stat-card">
            <div class="stat-icon" style="color: {{ $stat['color'] }};">
                <i class="fas {{ $stat['icon'] }}"></i>
            </div>
            <div class="stat-value">{{ $stat['value'] }}</div>
            <div class="stat-label">{{ $stat['label'] }}</div>
        </div>
    @endforeach
</div>

{{-- Quick Actions --}}
<div class="card">
    <h2>
        @if($isAdmin) Administrative Actions
        @elseif($isHR) HR Actions
        @else Quick Actions
        @endif
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
        @foreach($actions as $action)
            <button class="action-btn" style="border: 2px solid {{ $color }}; color: {{ $color }};">
                <i class="fas {{ $action['icon'] }}"></i> {{ $action['label'] }}
            </button>
        @endforeach
    </div>
</div>

{{-- System Information --}}
<div class="card">
    <h2>System Information</h2>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 10px 0; color: #6b7280; width: 200px;">Name</td>
            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">{{ $user->name }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 10px 0; color: #6b7280;">Email</td>
            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">{{ $user->email }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 10px 0; color: #6b7280;">Role</td>
            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                @if($isAdmin) Administrator
                @elseif($isHR) HR Personnel
                @else Employee
                @endif
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 10px 0; color: #6b7280;">Account Status</td>
            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                @if($user->is_active)
                    <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Active</span>
                @else
                    <span style="color: #dc2626;"><i class="fas fa-times-circle"></i> Inactive</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #6b7280;">Last Login</td>
            <td style="padding: 10px 0; font-weight: 600; color: #1f2937;">
                @if($user->last_login_at)
                    {{ $user->last_login_at->format('M d, Y H:i A') }}
                @else
                    First Login
                @endif
            </td>
        </tr>
    </table>
</div>

@endsection
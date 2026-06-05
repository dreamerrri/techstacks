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
@endphp

{{-- Role access badge --}}
@if($isAdmin)
    <span class="aurora-badge aurora-badge-admin">
        <i class="fas fa-shield-alt"></i> Administrator Access
    </span>
@elseif($isHR)
    <span class="aurora-badge aurora-badge-hr">
        <i class="fas fa-user-tie"></i> HR Department Access
    </span>
@endif

<div class="aurora-welcome">
    Welcome back, <strong>{{ $user->name }}</strong>
    @if($isAdmin) — You have full administrative access.
    @elseif($isHR) — You have HR access privileges.
    @endif
</div>

{{-- Stats --}}
<div class="aurora-stats-grid">
    @foreach($stats as $stat)
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color: {{ $stat['color'] }}; background: {{ $stat['color'] }}1a;">
                <i class="fas {{ $stat['icon'] }}"></i>
            </div>
            <div class="aurora-stat-value">{{ $stat['value'] }}</div>
            <div class="aurora-stat-label">{{ $stat['label'] }}</div>
        </div>
    @endforeach
</div>

{{-- Quick Actions --}}
<div class="aurora-card">
    <h2 class="aurora-card-title">
        <i class="fas fa-bolt"></i>
        @if($isAdmin) Administrative Actions
        @elseif($isHR) HR Actions
        @else Quick Actions
        @endif
    </h2>
    <div class="aurora-actions-grid">
        @foreach($actions as $action)
            <a href="{{ $action['route'] }}" class="aurora-action-btn">
                <i class="fas {{ $action['icon'] }}"></i>
                <span>{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>

{{-- System Information --}}
<div class="aurora-card">
    <h2 class="aurora-card-title">
        <i class="fas fa-id-badge"></i>
        System Information
    </h2>
    <div class="aurora-info-list">
        <div class="aurora-info-row">
            <span class="aurora-info-label">Name</span>
            <span class="aurora-info-value">{{ $user->name }}</span>
        </div>
        <div class="aurora-info-row">
            <span class="aurora-info-label">Email</span>
            <span class="aurora-info-value">{{ $user->email }}</span>
        </div>
        <div class="aurora-info-row">
            <span class="aurora-info-label">Role</span>
            <span class="aurora-info-value">
                @if($isAdmin) Administrator
                @elseif($isHR) HR Personnel
                @else Employee
                @endif
            </span>
        </div>
        <div class="aurora-info-row">
            <span class="aurora-info-label">Account Status</span>
            <span class="aurora-info-value">
                @if($user->is_active)
                    <span class="aurora-status aurora-status-active">
                        <i class="fas fa-check-circle"></i> Active
                    </span>
                @else
                    <span class="aurora-status aurora-status-inactive">
                        <i class="fas fa-times-circle"></i> Inactive
                    </span>
                @endif
            </span>
        </div>
        <div class="aurora-info-row">
            <span class="aurora-info-label">Last Login</span>
            <span class="aurora-info-value">
                @if($user->last_login_at)
                    {{ $user->last_login_at->format('M d, Y H:i A') }}
                @else
                    First Login
                @endif
            </span>
        </div>
    </div>
</div>

@endsection
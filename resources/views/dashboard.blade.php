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
    <span class="badge badge-soft badge-success mb-4">
        <i class="fas fa-shield-alt"></i> Administrator Access
    </span>
@elseif($isHR)
    <span class="badge badge-soft badge-success mb-4">
        <i class="fas fa-user-tie"></i> HR Department Access
    </span>
@endif

<div class="text-gray-500 text-lg mb-5">
    Welcome back, <strong>{{ $user->name }}</strong>
    @if($isAdmin) — You have full administrative access.
    @elseif($isHR) — You have HR access privileges.
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
    @foreach($stats as $stat)
        @if(!empty($stat['route']))
            <a href="{{ $stat['route'] }}" class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
        @else
            <div class="card bg-base-100 shadow-sm p-5 text-center">
        @endif
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3"
                 style="color: {{ $stat['color'] }}; background: {{ $stat['color'] }}1a;">
                <i class="fas {{ $stat['icon'] }}"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ $stat['value'] }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">{{ $stat['label'] }}</div>
        @if(!empty($stat['route']))
            </a>
        @else
            </div>
        @endif
    @endforeach
</div>

{{-- Quick Actions --}}
<div class="card bg-base-100 shadow-sm p-6 mb-5">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
        <i class="fas fa-bolt"></i>
        @if($isAdmin) Administrative Actions
        @elseif($isHR) HR Actions
        @else Quick Actions
        @endif
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($actions as $action)
            <a href="{{ $action['route'] }}" class="btn btn-soft btn-outline flex-col h-auto py-5 gap-2">
                <i class="fas {{ $action['icon'] }} text-2xl"></i>
                <span>{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>

{{-- System Information --}}
<div class="card bg-base-100 shadow-sm p-6">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
        <i class="fas fa-id-badge"></i>
        System Information
    </h2>
    <div class="flex flex-col">
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-gray-400 font-medium">Name</span>
            <span class="font-semibold text-gray-800 text-right">{{ $user->name }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-gray-400 font-medium">Email</span>
            <span class="font-semibold text-gray-800 text-right">{{ $user->email }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-gray-400 font-medium">Role</span>
            <span class="font-semibold text-gray-800 text-right">
                @if($isAdmin) Administrator
                @elseif($isHR) HR Personnel
                @else Employee
                @endif
            </span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-gray-400 font-medium">Account Status</span>
            <span class="font-semibold text-gray-800 text-right">
                @if($user->is_active)
                    <span class="badge badge-soft badge-success">
                        <i class="fas fa-check-circle"></i> Active
                    </span>
                @else
                    <span class="badge badge-soft badge-error">
                        <i class="fas fa-times-circle"></i> Inactive
                    </span>
                @endif
            </span>
        </div>
        <div class="flex justify-between items-center py-3">
            <span class="text-gray-400 font-medium">Last Login</span>
            <span class="font-semibold text-gray-800 text-right">
                @if($user->last_login_at)
                    {{ $user->last_login_at->format('M d, Y h:i A') }}
                @else
                    First Login
                @endif
            </span>
        </div>
    </div>
</div>

@endsection
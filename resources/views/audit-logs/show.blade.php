@extends('layouts.app')

@section('title', 'Audit Log Detail')

@section('content')
 <div class="mb-5">
        <a href="{{ route('audit-logs.index') }}" class="back-link text-base-content no-underline text-sm hover:text-primary">
            <i class="icon-[tabler--arrow-left]"></i> Back to Audit Logs
        </a>
    </div>
    <div class="mb-6">
        <span class="badge badge-soft badge-success mb-2">
            <i class="icon-[tabler--history]"></i> Audit Log Detail
        </span>
        <p class="text-base-content/60 m-0">Detailed view of a single audit log entry.</p>
    </div>

    <div class="card bg-base-100 shadow-sm p-0 max-w-2xl">
        @php
            $actionClass = match(strtolower($auditLog->action)) {
                'create' => 'badge-soft badge-success',
                'update' => 'badge-soft badge-warning',
                'delete' => 'badge-soft badge-error',
                'login'  => 'badge-soft badge-info',
                'logout' => 'badge-soft badge-neutral',
                default  => 'badge-soft',
            };
        @endphp

        <div class="flex flex-col text-sm px-5">
            <x-detail-row label="Date / Time">
                {{ $auditLog->created_at->format('M d, Y h:i:s A') }}
            </x-detail-row>

            <x-detail-row label="User">
                {{ $auditLog->user?->name ?? '—' }}
            </x-detail-row>

            <x-detail-row label="Action">
                <span class="badge {{ $actionClass }}">{{ ucfirst($auditLog->action) }}</span>
            </x-detail-row>

            <x-detail-row label="Module">
                {{ ucfirst($auditLog->module) }}
            </x-detail-row>

            <x-detail-row label="Description">
                {{ $auditLog->description }}
            </x-detail-row>

            <x-detail-row label="IP Address">
                <span class="font-mono">{{ $auditLog->ip_address ?? '—' }}</span>
            </x-detail-row>

            <x-detail-row label="User Agent" :border="!($auditLog->old_values || $auditLog->new_values)">
                <span class="text-base-content/60 text-xs break-all">{{ $auditLog->user_agent ?? '—' }}</span>
            </x-detail-row>

            @if($auditLog->old_values)
                <div class="flex flex-col gap-2 py-3 {{ $auditLog->new_values ? 'border-b border-base-300' : '' }}">
                    <span class="text-xs font-semibold uppercase tracking-wider text-base-content/40">Old Values</span>
                    <pre class="m-0 text-xs bg-base-200 p-3 rounded-xl overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif

            @if($auditLog->new_values)
                <div class="flex flex-col gap-2 py-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-base-content/40">New Values</span>
                    <pre class="m-0 text-xs bg-base-200 p-3 rounded-xl overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>

        
    </div>

@endsection
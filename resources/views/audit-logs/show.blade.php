@extends('layouts.app')

@section('title', 'Audit Log Detail')


@section('content')

    <div class="mb-6">
        <span class="badge badge-soft badge-success mb-2">
            <i class="icon-[ph--clock-counter-clockwise-fill]"></i> Audit Log Detail
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

        <div class="flex flex-col text-sm">
            @foreach([
                ['Date / Time', $auditLog->created_at->format('M d, Y h:i:s A'), 'text-base-content'],
                ['User',        $auditLog->user?->name ?? '—',                   'text-base-content font-semibold'],
                ['Module',      ucfirst($auditLog->module),                      'text-base-content'],
                ['Description', $auditLog->description,                          'text-base-content'],
                ['IP Address',  $auditLog->ip_address ?? '—',                    'text-base-content font-mono'],
                ['User Agent',  $auditLog->user_agent ?? '—',                    'text-base-content/60 text-xs break-all'],
            ] as [$label, $value, $cls])
                <div class="flex justify-between items-start py-3 px-5 border-b border-gray-100 gap-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-base-content/40 w-32 flex-shrink-0 pt-0.5">{{ $label }}</span>
                    <span class="{{ $cls }} text-right flex-1">{{ $value }}</span>
                </div>
            @endforeach

            {{-- Action row with badge --}}
            <div class="flex justify-between items-center py-3 px-5 border-b border-gray-100 gap-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-base-content/40 w-32 flex-shrink-0">Action</span>
                <span class="badge {{ $actionClass }}">{{ ucfirst($auditLog->action) }}</span>
            </div>

            @if($auditLog->old_values)
                <div class="flex flex-col gap-2 py-3 px-5 border-b border-gray-100">
                    <span class="text-xs font-semibold uppercase tracking-wider text-base-content/40">Old Values</span>
                    <pre class="m-0 text-xs bg-gray-50 p-3 rounded-xl overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif

            @if($auditLog->new_values)
                <div class="flex flex-col gap-2 py-3 px-5 border-b border-gray-100">
                    <span class="text-xs font-semibold uppercase tracking-wider text-base-content/40">New Values</span>
                    <pre class="m-0 text-xs bg-gray-50 p-3 rounded-xl overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>

        <div class="px-5 py-4 border-t border-gray-100">
            <a href="{{ route('audit-logs.index') }}" class="btn btn-soft btn-sm">
                <i class="icon-[ph--arrow-left-fill]"></i> Back to Audit Logs
            </a>
        </div>
    </div>

@endsection
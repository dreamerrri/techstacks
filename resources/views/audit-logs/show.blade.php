@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('content')
<div class="page-header">
    <h1>Audit Log Details</h1>
    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="detail-section">
            <h3>Log Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Date/Time:</label>
                    <span>{{ $auditLog->created_at->format('M d, Y H:i:s') }}</span>
                </div>
                <div class="detail-item">
                    <label>User:</label>
                    <span>
                        @if($auditLog->user)
                            {{ $auditLog->user->name }} ({{ $auditLog->user->email }})
                        @else
                            System
                        @endif
                    </span>
                </div>
                <div class="detail-item">
                    <label>Action:</label>
                    <span><span class="badge badge-info">{{ ucfirst($auditLog->action) }}</span></span>
                </div>
                <div class="detail-item">
                    <label>Module:</label>
                    <span>{{ ucfirst($auditLog->module) }}</span>
                </div>
                <div class="detail-item">
                    <label>Description:</label>
                    <span>{{ $auditLog->description }}</span>
                </div>
                <div class="detail-item">
                    <label>IP Address:</label>
                    <span>{{ $auditLog->ip_address ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <label>User Agent:</label>
                    <span>{{ $auditLog->user_agent ?? '-' }}</span>
                </div>
            </div>
        </div>

        @if($auditLog->old_values)
            <div class="detail-section">
                <h3>Old Values</h3>
                <pre class="json-viewer">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if($auditLog->new_values)
            <div class="detail-section">
                <h3>New Values</h3>
                <pre class="json-viewer">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>
</div>
@endsection

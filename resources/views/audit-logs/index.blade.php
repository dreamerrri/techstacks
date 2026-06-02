@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="page-header">
    <h1>Audit Logs</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('audit-logs.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Module</label>
                    <select name="module" class="form-control">
                        <option value="">All Modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                {{ ucfirst($module) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Action</label>
                    <select name="action" class="form-control">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst($action) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td>
                                @if($log->user)
                                    {{ $log->user->name }}
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td><span class="badge badge-info">{{ ucfirst($log->action) }}</span></td>
                            <td>{{ ucfirst($log->module) }}</td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                            <td>
                                <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
</div>
@endsection

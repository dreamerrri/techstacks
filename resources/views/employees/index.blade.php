@extends('layouts.app')

@section('title', 'Manage Employees')
@section('breadcrumb')
    <span>Manage Employees</span>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:500;">Employees</span>
@endsection
@section('content')

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div>
            <span class="aurora-badge aurora-badge-admin" style="margin-bottom:8px;">
                <i class="fas fa-user-tie"></i> Employee Management
            </span>
            <p style="color:#6b7280; margin:0;">Manage all employee records in the system.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="btn btn-danger" style="white-space:nowrap;">
            <i class="fas fa-user-plus"></i> Add Employee
        </a>
    </div>

    {{-- Stats --}}
    <div class="aurora-stats-grid" style="margin-bottom:24px;">
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#dc2626; background:#dc26261a;">
                <i class="fas fa-users"></i>
            </div>
            <div class="aurora-stat-value">{{ $employees->total() }}</div>
            <div class="aurora-stat-label">Total Employees</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#10b981; background:#10b9811a;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::active()->where('employment_status','Regular')->count() }}</div>
            <div class="aurora-stat-label">Regular</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#fbbf24; background:#fbbf241a;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::active()->where('employment_status','Probationary')->count() }}</div>
            <div class="aurora-stat-label">Probationary</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#6b7280; background:#6b72801a;">
                <i class="fas fa-archive"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::archived()->count() }}</div>
            <div class="aurora-stat-label">Archived</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="aurora-card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">

        {{-- Sticky header: title + search --}}
        <div style="position:sticky; top:0; z-index:10; background:white; padding:20px 25px 0; border-radius:20px 20px 0 0; ">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
                <h2 class="aurora-card-title" style="margin:0; font-size:15px;">
                    <i class="fas fa-list"></i> Employee List
                </h2>
                <a href="{{ route('employees.archived') }}" style="color:#6b7280; font-size:13px; text-decoration:none;">
                    <i class="fas fa-archive"></i> View Archived
                </a>
            </div>

            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('employees.index') }}"
                  style="display:flex; flex-wrap:wrap; gap:10px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, ID, email..."
                       style="flex:1; min-width:160px; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px;">
                <select name="department"
                        style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px;">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <select name="status"
                        style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px;">
                    <option value="">All Status</option>
                    @foreach(['Regular','Probationary','Contractual','Part-time'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-danger btn-sm" style="padding:8px 20px; font-size:14px;">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request()->hasAny(['search','department','status']))
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm" style="padding:8px 16px; font-size:14px;">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="user-table-wrapper" style="overflow-y:auto; max-height:53vh; padding:0 25px;">
            <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:700px;">
                <thead style="position:sticky; top:0; z-index:5;">
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Employee ID</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Full Name</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Department</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Position</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Status</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Date Hired</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        @php
                            $colors = [
                                'Regular'      => 'background:#d1fae5; color:#065f46;',
                                'Probationary' => 'background:#fef3c7; color:#92400e;',
                                'Contractual'  => 'background:#dbeafe; color:#1e40af;',
                                'Part-time'    => 'background:#f3f4f6; color:#374151;',
                            ];
                        @endphp
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:12px; font-family:monospace; color:#6b7280;">{{ $employee->employee_id }}</td>
                            <td style="padding:12px;">
                                <a href="{{ route('employees.show', $employee) }}"
                                   style="color:#1f2937; text-decoration:none; font-weight:600;">
                                    {{ $employee->full_name }}
                                </a>
                            </td>
                            <td style="padding:12px; color:#6b7280;">{{ $employee->department }}</td>
                            <td style="padding:12px; color:#6b7280;">{{ $employee->position }}</td>
                            <td style="padding:12px;">
                                <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; {{ $colors[$employee->employment_status] ?? '' }}">
                                    {{ $employee->employment_status }}
                                </span>
                            </td>
                            <td style="padding:12px; color:#6b7280;">{{ $employee->date_hired->format('M d, Y') }}</td>
                            <td style="padding:12px;">
                                <div style="display:flex; gap:8px;">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('employees.archive', $employee) }}"
                                          data-confirm="This employee will be moved to the archive."
                                          data-confirm-title="Archive Employee?"
                                          data-confirm-icon="warning"
                                          data-confirm-btn="Yes, archive"
                                          style="display:inline;">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:40px; text-align:center; color:#9ca3af;">
                                <i class="fas fa-users" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>{{-- end aurora-card --}}

    {{-- Mobile Cards --}}
    <div class="user-mobile-cards" style="padding:16px;">
        @forelse($employees as $employee)
            @php
                $colors = [
                    'Regular'      => 'background:#d1fae5; color:#065f46;',
                    'Probationary' => 'background:#fef3c7; color:#92400e;',
                    'Contractual'  => 'background:#dbeafe; color:#1e40af;',
                    'Part-time'    => 'background:#f3f4f6; color:#374151;',
                ];
            @endphp
            <div class="user-card">
                <div class="user-card-header">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:38px; height:38px; border-radius:50%; overflow:hidden; flex-shrink:0;">
                            @if($employee->user?->profile_photo)
                                <img src="{{ asset('storage/' . $employee->user->profile_photo) }}"
                                     alt="{{ $employee->full_name }}"
                                     style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#dc2626,#991b1b); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:700;">
                                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <div style="font-weight:600; color:#1f2937; font-size:14px;">
                                <a href="{{ route('employees.show', $employee) }}"
                                   style="color:#1f2937; text-decoration:none; font-weight:600;">
                                    {{ $employee->full_name }}
                                </a>
                            </div>
                            <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
                        </div>
                    </div>
                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; {{ $colors[$employee->employment_status] ?? '' }}">
                        {{ $employee->employment_status }}
                    </span>
                </div>

                <div style="margin-top:10px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                    <span><i class="fas fa-building" style="width:14px;"></i> {{ $employee->department }}</span>
                    <span><i class="fas fa-briefcase" style="width:14px;"></i> {{ $employee->position }}</span>
                    <span><i class="fas fa-calendar" style="width:14px;"></i> {{ $employee->date_hired->format('M d, Y') }}</span>
                </div>

                <div class="user-card-meta" style="margin-top:10px; padding-top:10px; border-top:1px solid #f3f4f6; display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('employees.archive', $employee) }}"
                          data-confirm="This employee will be moved to the archive."
                          data-confirm-title="Archive Employee?"
                          data-confirm-icon="warning"
                          data-confirm-btn="Yes, archive"
                          style="display:inline;">
                        @csrf @method('PATCH')
                        <button class="btn btn-danger btn-sm">
                            <i class="fas fa-archive"></i> Archive
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="padding:40px; text-align:center; color:#9ca3af;">
                <i class="fas fa-users" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                No employees found.
            </div>
        @endforelse
    </div>

    <div style="padding:16px 25px; border-top:1px solid #e5e7eb;">{{ $employees->links() }}</div>

@endsection
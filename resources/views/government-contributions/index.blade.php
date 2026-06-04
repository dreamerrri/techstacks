@extends('layouts.app')

@section('title', 'Government Contributions')

@section('content')

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div>
            <span class="aurora-badge aurora-badge-hr" style="margin-bottom:8px;">
                <i class="fas fa-id-card"></i> Government Contributions
            </span>
            <p style="color:#6b7280; margin:0;">View and manage employee government contribution rates.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="aurora-stats-grid" style="margin-bottom:24px;">
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#dc2626; background:rgba(220,38,38,0.1);">
                <i class="fas fa-users"></i>
            </div>
            <div class="aurora-stat-value">{{ $employees->total() }}</div>
            <div class="aurora-stat-label">Total Employees</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#10b981; background:rgba(16,185,129,0.1);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::active()->whereNotNull('sss_number')->count() }}</div>
            <div class="aurora-stat-label">With SSS</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#3b82f6; background:rgba(59,130,246,0.1);">
                <i class="fas fa-heart"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::active()->whereNotNull('philhealth_number')->count() }}</div>
            <div class="aurora-stat-label">With PhilHealth</div>
        </div>
        <div class="aurora-stat-card">
            <div class="aurora-stat-icon" style="color:#f59e0b; background:rgba(245,158,11,0.1);">
                <i class="fas fa-home"></i>
            </div>
            <div class="aurora-stat-value">{{ \App\Models\Employee::active()->whereNotNull('pagibig_number')->count() }}</div>
            <div class="aurora-stat-label">With Pag-IBIG</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="aurora-card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">

        {{-- Sticky header: title + search --}}
        <div style="position:sticky; top:0; z-index:10; background:white; padding:20px 28px 0; border-radius:20px 20px 0 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
                <h2 class="aurora-card-title" style="margin:0; font-size:15px;">
                    <i class="fas fa-list"></i> Employee List
                </h2>
            </div>

            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('government-contributions.index') }}"
                  style="display:flex; flex-wrap:wrap; gap:10px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, ID, email..."
                       style="flex:1; min-width:160px; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                <select name="department"
                        style="border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:14px; outline:none;">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-danger btn-sm" style="padding:8px 20px; font-size:14px;">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request()->hasAny(['search','department']))
                    <a href="{{ route('government-contributions.index') }}"
                       style="padding:8px 16px; background:#f3f4f6; color:#6b7280; border-radius:8px; text-decoration:none; font-size:14px;">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="user-table-wrapper" style="overflow-y:auto; max-height:53vh; padding:0 28px;">
            <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:700px;">
                <thead style="position:sticky; top:0; z-index:5;">
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Employee ID</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Full Name</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Department</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Position</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Basic Salary</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Status</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        @php
                            $statusStyles = [
                                'Regular'      => 'background:#d1fae5; color:#065f46;',
                                'Probationary' => 'background:#fef3c7; color:#92400e;',
                                'Contractual'  => 'background:#dbeafe; color:#1e40af;',
                                'Part-time'    => 'background:#f3f4f6; color:#374151;',
                            ];
                        @endphp
                        <tr style="border-bottom:1px solid #f3f4f6; transition:background 0.15s;">
                            <td style="padding:12px; font-family:monospace; color:#6b7280;">{{ $employee->employee_id }}</td>
                            <td style="padding:12px; font-weight:600; color:#1a1a2e;">
                                <a href="{{ route('government-contributions.show', $employee) }}"
                                   style="color:#1a1a2e; text-decoration:none;">
                                    {{ $employee->full_name }}
                                </a>
                            </td>
                            <td style="padding:12px; color:#6b7280;">{{ $employee->department }}</td>
                            <td style="padding:12px; color:#6b7280;">{{ $employee->position }}</td>
                            <td style="padding:12px; font-weight:600; color:#1a1a2e;">₱{{ number_format($employee->basic_salary, 2) }}</td>
                            <td style="padding:12px;">
                                <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; {{ $statusStyles[$employee->employment_status] ?? '' }}">
                                    {{ $employee->employment_status }}
                                </span>
                            </td>
                            <td style="padding:12px;">
                                <a href="{{ route('government-contributions.show', $employee) }}"
                                   class="btn btn-sm" style="background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none; padding:5px 10px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
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

        {{-- Mobile Cards --}}
        <div class="user-mobile-cards" style="padding:16px;">
            @forelse($employees as $employee)
                @php
                    $statusStyles = [
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
                                <div style="font-weight:600; color:#1a1a2e; font-size:14px;">
                                    <a href="{{ route('government-contributions.show', $employee) }}"
                                       style="color:#1a1a2e; text-decoration:none;">
                                        {{ $employee->full_name }}
                                    </a>
                                </div>
                                <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                        <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; {{ $statusStyles[$employee->employment_status] ?? '' }}">
                            {{ $employee->employment_status }}
                        </span>
                    </div>

                    <div style="margin-top:10px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                        <span><i class="fas fa-building" style="width:14px;"></i> {{ $employee->department }}</span>
                        <span><i class="fas fa-briefcase" style="width:14px;"></i> {{ $employee->position }}</span>
                        <span><i class="fas fa-money-bill-wave" style="width:14px;"></i> ₱{{ number_format($employee->basic_salary, 2) }}</span>
                    </div>

                    <div class="user-card-meta">
                        <a href="{{ route('government-contributions.show', $employee) }}"
                           style="padding:5px 12px; background:#dbeafe; color:#1e40af; border-radius:8px; font-size:12px; text-decoration:none;">
                            <i class="fas fa-eye"></i> View Contributions
                        </a>
                    </div>
                </div>
            @empty
                <div style="padding:40px; text-align:center; color:#9ca3af;">
                    <i class="fas fa-users" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                    No employees found.
                </div>
            @endforelse
        </div>

    </div>


            <div style="padding:16px 28px; border-top:1px solid #e5e7eb;">{{ $employees->links() }}</div>

@endsection
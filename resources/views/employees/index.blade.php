@extends('layouts.app')

@section('title', 'Manage Staff')

@section('content')

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div style="display:inline-block; background:#fecaca; color:#991b1b; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
                <i class="fas fa-user-tie"></i> Employee Management
            </div>
            <p style="color:#6b7280; margin:0;">Manage all employee records in the system.</p>
        </div>
        <a href="{{ route('employees.create') }}"
           style="padding:10px 20px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
            <i class="fas fa-user-plus"></i> Add Employee
        </a>
    </div>

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; margin-bottom:30px;">
        <div class="card" style="text-align:center; margin-bottom:0;">
            <div style="font-size:28px; color:#dc2626; margin-bottom:8px;"><i class="fas fa-users"></i></div>
            <div style="font-size:24px; font-weight:700; color:#1f2937;">{{ $employees->total() }}</div>
            <div style="color:#6b7280; font-size:14px;">Total Employees</div>
        </div>
        <div class="card" style="text-align:center; margin-bottom:0;">
            <div style="font-size:28px; color:#10b981; margin-bottom:8px;"><i class="fas fa-check-circle"></i></div>
            <div style="font-size:24px; font-weight:700; color:#1f2937;">{{ \App\Models\Employee::active()->where('employment_status','Regular')->count() }}</div>
            <div style="color:#6b7280; font-size:14px;">Regular</div>
        </div>
        <div class="card" style="text-align:center; margin-bottom:0;">
            <div style="font-size:28px; color:#fbbf24; margin-bottom:8px;"><i class="fas fa-clock"></i></div>
            <div style="font-size:24px; font-weight:700; color:#1f2937;">{{ \App\Models\Employee::active()->where('employment_status','Probationary')->count() }}</div>
            <div style="color:#6b7280; font-size:14px;">Probationary</div>
        </div>
        <div class="card" style="text-align:center; margin-bottom:0;">
            <div style="font-size:28px; color:#6b7280; margin-bottom:8px;"><i class="fas fa-archive"></i></div>
            <div style="font-size:24px; font-weight:700; color:#1f2937;">{{ \App\Models\Employee::archived()->count() }}</div>
            <div style="color:#6b7280; font-size:14px;">Archived</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0;">Employee List</h2>
            <a href="{{ route('employees.archived') }}" style="color:#6b7280; font-size:13px; text-decoration:none;">
                <i class="fas fa-archive"></i> View Archived
            </a>
        </div>

        {{-- Search & Filters --}}
        <form method="GET" action="{{ route('employees.index') }}"
              style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, ID, email..."
                   style="flex:1; min-width:200px; border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
            <select name="department"
                    style="border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <select name="status"
                    style="border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px;">
                <option value="">All Status</option>
                @foreach(['Regular','Probationary','Contractual','Part-time'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit"
                    style="padding:8px 20px; background:#dc2626; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px;">
                <i class="fas fa-search"></i> Search
            </button>
            @if(request()->hasAny(['search','department','status']))
                <a href="{{ route('employees.index') }}"
                   style="padding:8px 16px; background:#f3f4f6; color:#6b7280; border-radius:6px; text-decoration:none; font-size:14px;">
                    Clear
                </a>
            @endif
        </form>

        {{-- Table --}}
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
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
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:12px; font-family:monospace; color:#6b7280;">{{ $employee->employee_id }}</td>
                        <td style="padding:12px; font-weight:600; color:#1f2937;">{{ $employee->full_name }}</td>
                        <td style="padding:12px; color:#6b7280;">{{ $employee->department }}</td>
                        <td style="padding:12px; color:#6b7280;">{{ $employee->position }}</td>
                        <td style="padding:12px;">
                            @php
                                $colors = [
                                    'Regular'      => 'background:#d1fae5; color:#065f46;',
                                    'Probationary' => 'background:#fef3c7; color:#92400e;',
                                    'Contractual'  => 'background:#dbeafe; color:#1e40af;',
                                    'Part-time'    => 'background:#f3f4f6; color:#374151;',
                                ];
                            @endphp
                            <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; {{ $colors[$employee->employment_status] ?? '' }}">
                                {{ $employee->employment_status }}
                            </span>
                        </td>
                        <td style="padding:12px; color:#6b7280;">{{ $employee->date_hired->format('M d, Y') }}</td>
                        <td style="padding:12px;">
                            <div style="display:flex; gap:8px;">
                                <a href="{{ route('employees.show', $employee) }}"
                                   style="padding:5px 10px; background:#dbeafe; color:#1e40af; border-radius:5px; font-size:12px; text-decoration:none;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('employees.edit', $employee) }}"
                                   style="padding:5px 10px; background:#fef3c7; color:#92400e; border-radius:5px; font-size:12px; text-decoration:none;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('employees.archive', $employee) }}"
                                      data-confirm="This employee will be moved to the archive."
                                      data-confirm-title="Archive Employee?"
                                      data-confirm-icon="warning"
                                      data-confirm-btn="Yes, archive"
                                      style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button style="padding:5px 10px; background:#fecaca; color:#991b1b; border:none; border-radius:5px; font-size:12px; cursor:pointer;">
                                        <i class="fas fa-archive"></i> Archive
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

        <div style="margin-top:20px;">{{ $employees->links() }}</div>
    </div>

@endsection
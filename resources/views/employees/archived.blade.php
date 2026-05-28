{{-- resources/views/employees/archived.blade.php --}}
@extends('layouts.app')

@section('title', 'Archived Employees')

@section('content')

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <a href="{{ route('employees.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2 style="margin:0; color:#1f2937; font-size:20px;">Archived Employees</h2>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:#d1fae5; color:#065f46; border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Desktop Table --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div class="user-table-wrapper">
            <table style="width:100%; border-collapse:collapse; font-size:14px; min-width:500px;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Employee ID</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Full Name</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Department</th>
                        <th style="padding:12px; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase;">Position</th>
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
                                <form method="POST" action="{{ route('employees.restore', $employee) }}"
                                      data-confirm="This employee will be restored to the active list."
                                      data-confirm-title="Restore Employee?"
                                      data-confirm-icon="question"
                                      data-confirm-btn="Yes, restore">
                                    @csrf @method('PATCH')
                                    <button style="padding:5px 12px; background:#d1fae5; color:#065f46; border:none; border-radius:5px; font-size:12px; cursor:pointer;">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:40px; text-align:center; color:#9ca3af;">
                                <i class="fas fa-archive" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                                No archived employees.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="user-mobile-cards" style="padding:16px;">
            @forelse($employees as $employee)
                <div class="user-card">
                    <div class="user-card-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#6b7280,#374151); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:700; flex-shrink:0;">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#1f2937; font-size:14px;">{{ $employee->full_name }}</div>
                                <div style="font-size:12px; color:#6b7280; font-family:monospace;">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                        <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#f3f4f6; color:#374151; white-space:nowrap;">
                            Archived
                        </span>
                    </div>

                    <div style="margin-top:8px; font-size:13px; color:#6b7280; display:flex; flex-wrap:wrap; gap:6px 16px;">
                        <span><i class="fas fa-building" style="width:14px;"></i> {{ $employee->department }}</span>
                        <span><i class="fas fa-briefcase" style="width:14px;"></i> {{ $employee->position }}</span>
                    </div>

                    <div style="margin-top:10px; padding-top:10px; border-top:1px solid #f3f4f6;">
                        <form method="POST" action="{{ route('employees.restore', $employee) }}"
                              data-confirm="This employee will be restored to the active list."
                              data-confirm-title="Restore Employee?"
                              data-confirm-icon="question"
                              data-confirm-btn="Yes, restore">
                            @csrf @method('PATCH')
                            <button style="padding:5px 14px; background:#d1fae5; color:#065f46; border:none; border-radius:5px; font-size:12px; cursor:pointer;">
                                <i class="fas fa-undo"></i> Restore
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="padding:40px; text-align:center; color:#9ca3af;">
                    <i class="fas fa-archive" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                    No archived employees.
                </div>
            @endforelse
        </div>
    </div>

    <div style="margin-top:20px;">{{ $employees->links() }}</div>

@endsection
@extends('layouts.app')

@section('title', 'Manage Employees')

@section('content')

    {{-- Stat --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                <i class="icon-[tabler--user]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $employees->total() }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Total Employees</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-success bg-success/10">
                <i class="icon-[tabler--circle-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\Employee::active()->where('employment_status','Regular')->count() }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Regular</div>
        </div>
        <div class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-warning bg-warning/10">
                <i class="icon-[tabler--clock]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\Employee::active()->where('employment_status','Probationary')->count() }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Probationary</div>
        </div>
        <a href="{{ route('employees.archived') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-base-content/60 bg-base-200">
                <i class="icon-[tabler--archive]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\Employee::archived()->count() }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Archived</div>
        </a>
    </div>

    {{-- Filters + Table --}}
    <x-table-card action="{{ route('employees.index') }}">
        <x-slot:title>
            <x-dot-loader /> <p class="text-base-content">Employee List</p>
            <x-info-tooltip>
                Manage all employee records in the system.
            </x-info-tooltip>
        </x-slot:title>

        <x-slot:actions>
            <a href="{{ route('employees.create') }}" class="btn btn-soft  btn-error btn-sm">
                <i class="icon-[tabler--plus]"></i> Add Employee
            </a>
        </x-slot:actions>

        <x-slot:filters>
            {{-- Search group --}}
            <div class="join w-full sm:w-64 sm:flex-none min-w-0">
                <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                       placeholder="Search name or email..."
                       oninput="clearTimeout(this._t); this._t = setTimeout(() => this.closest('form').submit(), 400)"
                       class="input input-bordered input-sm bg-base-200  join-item w-full ">
               <button type="submit" class="btn btn-soft btn-primary btn-sm join-item">
                    <i class="icon-[tabler--search]"></i>
                </button>
            </div>

            {{-- Filters group --}}
            <div class="flex flex-wrap gap-2 md:ml-auto">
                <select name="department" onchange="this.closest('form').submit()" class="select select-bordered select-sm w-full sm:w-auto">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.closest('form').submit()" class="select select-bordered select-sm w-full sm:w-auto">
                    <option value="">All Status</option>
                    @foreach(['Regular','Probationary','Contractual','Part-time'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                @if(request()->hasAny(['search','department','status']))
                    <a href="{{ route('employees.index') }}" class="btn btn-soft  btn-sm">Clear</a>
                @endif
            </div>
        </x-slot:filters>

        {{-- Desktop Table --}}
        <x-data-table>
            <x-slot:head>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Position</th>
                <x-sortable-th sort-key="employment_status" label="Status" route="employees.index" />
                <x-sortable-th sort-key="date_hired" label="Date Hired" route="employees.index" />
                <th>Actions</th>
            </x-slot:head>

            @forelse($employees as $employee)
                @php
                    $statusClass = match($employee->employment_status) {
                        'Regular'      => 'badge-soft badge-success',
                        'Probationary' => 'badge-soft badge-warning',
                        'Contractual'  => 'badge-soft badge-info',
                        'Part-time'    => 'badge-soft badge-neutral',
                        default        => 'badge-soft',
                    };
                @endphp
                <tr class="row-hover">
                    <td class="font-mono text-base-content/60">{{ $employee->employee_id }}</td>
                    <td>
                        <a href="{{ route('employees.show', $employee) }}"
                           class="text-base-content no-underline font-semibold hover:text-primary">
                            {{ $employee->full_name }}
                        </a>
                    </td>
                    <td class="text-base-content/60">{{ $employee->department }}</td>
                    <td class="text-base-content/60">{{ $employee->position }}</td>
                    <td>
                        <span class="badge {{ $statusClass }}">{{ $employee->employment_status }}</span>
                    </td>
                    <td class="text-base-content/60">{{ $employee->date_hired->format('M d, Y') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-soft btn-info btn-sm">
                                <i class="icon-[tabler--eye]"></i>
                            </a>
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-soft btn-warning btn-sm">
                                <i class="icon-[tabler--pencil]"></i>
                            </a>
                            <form method="POST" action="{{ route('employees.archive', $employee) }}"
                                  data-confirm="This employee will be moved to the archive."
                                  data-confirm-title="Archive Employee?"
                                  data-confirm-icon="warning"
                                  data-confirm-btn="Yes, archive">
                                @csrf @method('PATCH')
                                <button class="btn btn-soft btn-error btn-sm">
                                    <i class="icon-[tabler--archive]"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-base-content/40">
                        <i class="icon-[tabler--user] text-3xl mb-2 block"></i>
                        No employees found.
                    </td>
                </tr>
            @endforelse
        </x-data-table>

        {{-- Mobile Cards --}}
        <div class="md:hidden p-4 flex flex-col gap-3">
            @forelse($employees as $employee)
                @php
                    $statusClass = match($employee->employment_status) {
                        'Regular'      => 'badge-soft badge-success',
                        'Probationary' => 'badge-soft badge-warning',
                        'Contractual'  => 'badge-soft badge-info',
                        'Part-time'    => 'badge-soft badge-neutral',
                        default        => 'badge-soft',
                    };
                @endphp
                <div class="card bg-base-100 border border-base-300 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                @if($employee->user?->profile_photo)
                                    <img src="{{ asset('storage/' . $employee->user->profile_photo) }}"
                                         alt="{{ $employee->full_name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-primary-content text-sm font-bold">
                                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('employees.show', $employee) }}"
                                   class="text-base-content no-underline font-semibold text-sm hover:text-primary">
                                    {{ $employee->full_name }}
                                </a>
                                <div class="text-xs text-base-content/60 font-mono">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                        <span class="badge {{ $statusClass }} whitespace-nowrap">{{ $employee->employment_status }}</span>
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                        <span><i class="icon-[tabler--building] w-3.5"></i> {{ $employee->department }}</span>
                        <span><i class="icon-[tabler--briefcase] w-3.5"></i> {{ $employee->position }}</span>
                        <span><i class="icon-[tabler--calendar] w-3.5"></i> {{ $employee->date_hired->format('M d, Y') }}</span>
                    </div>

                    <div class="flex gap-2 flex-wrap mt-3 pt-3 border-t border-base-300">
                        <a href="{{ route('employees.show', $employee) }}" class="btn btn-soft  btn-info btn-sm">
                            <i class="icon-[tabler--eye]"></i> View
                        </a>
                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-soft  btn-warning btn-sm">
                            <i class="icon-[tabler--pencil]"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('employees.archive', $employee) }}"
                              data-confirm="This employee will be moved to the archive."
                              data-confirm-title="Archive Employee?"
                              data-confirm-icon="warning"
                              data-confirm-btn="Yes, archive">
                            @csrf @method('PATCH')
                            <button class="btn btn-soft btn-error btn-sm">
                                <i class="icon-[tabler--archive]"></i> Archive
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-base-content/40">
                    <i class="icon-[tabler--user] text-3xl mb-2 block"></i>
                    No employees found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-base-300">
            {{ $employees->links('vendor.pagination.pagination') }}
        </div>
    </x-table-card>

@endsection
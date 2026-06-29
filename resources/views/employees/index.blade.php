@extends('layouts.app')

@section('title', 'Manage Employees')
@section('breadcrumb')
    <span>Manage Employees</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-medium">Employees</span>
@endsection

@section('content')

    {{-- Header --}}
    <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
        <div>
            <span class="badge badge-soft badge-success mb-2">
                <i class="icon-[ph--user-fill]"></i> Employee Management
            </span>
            <p class="text-gray-500 m-0">Manage all employee records in the system.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="btn btn-soft btn-error whitespace-nowrap">
            <i class="icon-[ph--user-fill]-plus"></i> Add Employee
        </a>
    </div>

    {{-- Stat --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-red-600 bg-red-100">
                <i class="icon-[ph--user-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ $employees->total() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Total Employees</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-emerald-600 bg-emerald-100">
                <i class="icon-[ph--check-circle-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\Employee::active()->where('employment_status','Regular')->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Regular</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-amber-400 bg-amber-100">
                <i class="icon-[ph--clock-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\Employee::active()->where('employment_status','Probationary')->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Probationary</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-gray-500 bg-gray-100">
                <i class="icon-[ph--archive-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ \App\Models\Employee::archived()->count() }}</div>
            <div class="text-xs text-gray-400 uppercase tracking-widest font-medium">Archived</div>
        </div>
    </div>

    {{-- Filters + Table --}}
    <div class="card bg-base-100 shadow-sm overflow-hidden flex flex-col p-0">

        {{-- Card header --}}
        <div class="sticky top-0 z-10 bg-white px-6 pt-5 rounded-t-2xl">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 flex items-center gap-2 m-0">
                    <i class="icon-[ph--list-fill]"></i> Employee List
                </h2>
                <a href="{{ route('employees.archived') }}" class="text-gray-500 text-xs no-underline hover:text-emerald-600">
                    <i class="icon-[ph--archive-fill]"></i> View Archived
                </a>
            </div>

            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('employees.index') }}"
                  class="flex flex-wrap gap-2 pb-4 border-b border-gray-200">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, ID, email..."
                       class="input input-bordered input-sm flex-1 min-w-40">
                <select name="department" class="select select-bordered select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <select name="status" class="select select-bordered select-sm">
                    <option value="">All Status</option>
                    @foreach(['Regular','Probationary','Contractual','Part-time'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-soft btn-error btn-sm">
                    <i class="icon-[ph--magnifying-glass-fill]"></i> Search
                </button>
                @if(request()->hasAny(['search','department','status']))
                    <a href="{{ route('employees.index') }}" class="btn btn-soft btn-sm">Clear</a>
                @endif
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="table-responsive overflow-y-auto max-h-[53vh] px-6 hidden md:block">
            @php
                $s    = request('sort');
                $d    = request('direction', 'asc');
                $base = request()->except(['sort', 'direction', 'page']);
                function empSortTh(string $key, string $label, array $base, ?string $s, string $d): string {
                    $active  = $s === $key;
                    $nextDir = ($active && $d === 'asc') ? 'desc' : 'asc';
                    $url     = route('employees.index', array_merge($base, ['sort' => $key, 'direction' => $nextDir]));
                    $upCol   = ($active && $d === 'asc')  ? '#dc2626' : '#d1d5db';
                    $dnCol   = ($active && $d === 'desc') ? '#dc2626' : '#d1d5db';
                    $color   = $active ? 'text-red-600 font-bold' : 'text-gray-500 font-semibold';
                    return '<th><a href="' . $url . '" class="inline-flex items-center gap-1 no-underline uppercase tracking-wider text-xs ' . $color . '">'
                         . $label
                         . '<span class="inline-flex flex-col leading-none gap-px">'
                         . '<i class="icon-[ph--caret-up-fill]" style="font-size:9px; color:' . $upCol . ';"></i>'
                         . '<i class="icon-[ph--caret-down-fill]" style="font-size:9px; color:' . $dnCol . ';"></i>'
                         . '</span></a></th>';
                }
            @endphp
            <table class="table table-hover w-full text-sm">
                <thead class="sticky top-0 z-5">
                    <tr>
                        <th>Employee ID</th>
                        <th>Full Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        {!! empSortTh('employment_status', 'Status',     $base, $s, $d) !!}
                        {!! empSortTh('date_hired',        'Date Hired', $base, $s, $d) !!}
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                        <tr>
                            <td class="font-mono text-gray-500">{{ $employee->employee_id }}</td>
                            <td>
                                <a href="{{ route('employees.show', $employee) }}"
                                   class="text-gray-800 no-underline font-semibold hover:text-emerald-600">
                                    {{ $employee->full_name }}
                                </a>
                            </td>
                            <td class="text-gray-500">{{ $employee->department }}</td>
                            <td class="text-gray-500">{{ $employee->position }}</td>
                            <td>
                                <span class="badge {{ $statusClass }}">{{ $employee->employment_status }}</span>
                            </td>
                            <td class="text-gray-500">{{ $employee->date_hired->format('M d, Y') }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-soft btn-info btn-sm">
                                        <i class="icon-[ph--eye-fill]"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-soft btn-warning btn-sm">
                                        <i class="icon-[ph--pencil-fill]"></i>
                                    </a>
                                    <form method="POST" action="{{ route('employees.archive', $employee) }}"
                                          data-confirm="This employee will be moved to the archive."
                                          data-confirm-title="Archive Employee?"
                                          data-confirm-icon="warning"
                                          data-confirm-btn="Yes, archive">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-soft btn-error btn-sm">
                                            <i class="icon-[ph--archive-fill]"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-400">
                                <i class="icon-[ph--user-fill]s text-3xl mb-2 block"></i>
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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
                <div class="card bg-base-100 border border-gray-200 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                @if($employee->user?->profile_photo)
                                    <img src="{{ asset('storage/' . $employee->user->profile_photo) }}"
                                         alt="{{ $employee->full_name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-sm font-bold">
                                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('employees.show', $employee) }}"
                                   class="text-gray-800 no-underline font-semibold text-sm hover:text-emerald-600">
                                    {{ $employee->full_name }}
                                </a>
                                <div class="text-xs text-gray-500 font-mono">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                        <span class="badge {{ $statusClass }} whitespace-nowrap">{{ $employee->employment_status }}</span>
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mt-2">
                        <span><i class="icon-[ph--buildings-fill] w-3.5"></i> {{ $employee->department }}</span>
                        <span><i class="icon-[ph--briefcase-fill] w-3.5"></i> {{ $employee->position }}</span>
                        <span><i class=" icon-[ph--calendar-fill] w-3.5"></i> {{ $employee->date_hired->format('M d, Y') }}</span>
                    </div>

                    <div class="flex gap-2 flex-wrap mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('employees.show', $employee) }}" class="btn btn-soft btn-info btn-sm">
                            <i class="icon-[ph--eye-fill]"></i> View
                        </a>
                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-soft btn-warning btn-sm">
                            <i class="icon-[ph--pencil-fill]"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('employees.archive', $employee) }}"
                              data-confirm="This employee will be moved to the archive."
                              data-confirm-title="Archive Employee?"
                              data-confirm-icon="warning"
                              data-confirm-btn="Yes, archive">
                            @csrf @method('PATCH')
                            <button class="btn btn-soft btn-error btn-sm">
                                <i class="icon-[ph--archive-fill]"></i> Archive
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-gray-400">
                    <i class="icon-[ph--user-fill]s text-3xl mb-2 block"></i>
                    No employees found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $employees->links() }}
        </div>

    </div>

@endsection
@extends('layouts.app')

@section('title', 'Manage Employees')

@section('content')


    {{-- Stat --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-red-600 bg-red-100">
                <i class="icon-[ph--user-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $employees->total() }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Total Employees</div>
        </div>


        <div class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-emerald-600 bg-emerald-100">
                <i class="icon-[ph--check-circle-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\Employee::active()->where('employment_status','Regular')->count() }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Regular</div>
        </div>
        <div class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-amber-400 bg-amber-100">
                <i class="icon-[ph--clock-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\Employee::active()->where('employment_status','Probationary')->count() }}</div>
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Probationary</div>
        </div>
         <a href="{{ route('employees.archived') }}"class="card bg-base-100 shadow-sm p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
              <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-base-content/60 bg-gray-100">
                <i class="icon-[ph--archive-fill]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ \App\Models\Employee::archived()->count() }}</div>
           
            <div class="text-xs text-base-content/40 uppercase tracking-widest font-medium">Archived</div>
         </a>
          
        </div>
    

    {{-- Filters + Table --}}
    <div class="card bg-base-100 shadow-sm overflow-hidden flex flex-col p-0">

        {{-- Card header --}}
        <div class="sticky top-0 z-10  px-6 pt-5 rounded-t-2xl">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-widest text-base-content/40 flex items-center gap-2 m-0">
                    <x-dot-loader /> Employee List
                    
                <div class="tooltip [--placement:right]">
    <span class="tooltip-toggle cursor-pointer text-base-content/40 hover:text-base-content/70" aria-label="More info">
        <i class="icon-[ph--info-fill]"></i>
    </span>
    <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
        <span class="tooltip-body  bg-success/67 shadow-md rounded-lg px-3 py-2 text-xs normal-case">
           Manage all employee records in the system.
        </span>
    </span>
           
</div>
                </h2>

         
<a href="{{ route('employees.create') }}" class="btn btn-soft btn-error btn-sm">
            <i class="icon-[ph--plus-fill]"></i> Add Employee
        </a>
       
            </div> 
            {{-- -HIDE MUNA   <a href="{{ route('employees.archived') }}" class="text-base-content/60 text-xs no-underline hover:text-emerald-600">
                    <i class="icon-[ph--archive-fill]"></i> View Archived
                </a>--}} 

            {{-- Search & Filters --}}
<form method="GET" action="{{ route('employees.index') }}"
      class="flex flex-col md:flex-row md:items-center gap-3 pb-4 ">

    {{-- Search group --}}
    <div class="join flex-none w-64 min-w-40 ">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name, ID, email..."
                oninput="clearTimeout(this._t); this._t = setTimeout(() => this.closest('form').submit(), 400)"   {{-- Could be removed for consistency, come back later --}}
               class="input input-bordered input-sm join-item w-full border-gray-300">
               
        <button type="submit" class="btn btn-outline btn-sm join-item border-gray-300">
            <i class="icon-[ph--magnifying-glass-fill]"></i>
        </button>
    </div>

    {{-- Filters group --}}
    <div class="flex flex-row gap-2 md:ml-auto">
        <select name="department" 
         onchange="this.closest('form').submit()"
        class="select select-bordered select-sm">
            
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        <select name="status"
         onchange="this.closest('form').submit()"  {{-- auto reloads form --}}
        class="select select-bordered select-sm">
            <option value="">All Status</option>
            @foreach(['Regular','Probationary','Contractual','Part-time'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','department','status']))
            <a href="{{ route('employees.index') }}" class="btn btn-soft btn-sm">Clear</a>
        @endif
    </div>
</form>
            
        </div>

        {{-- Desktop Table --}}
        <div class="table-responsive overflow-y-auto max-h-[53vh] hidden md:block">
          @php
    $s    = request('sort');
    $d    = request('direction', 'asc');
    $base = request()->except(['sort', 'direction', 'page']);
    function empSortTh(string $key, string $label, array $base, ?string $s, string $d): string {
        $active  = $s === $key;
        $nextDir = ($active && $d === 'asc') ? 'desc' : 'asc';
        $url     = route('employees.index', array_merge($base, ['sort' => $key, 'direction' => $nextDir]));
        $upCol   = ($active && $d === 'asc')  ? 'text-red-600' : 'text-white';
        $dnCol   = ($active && $d === 'desc') ? 'text-red-600' : 'text-white';
        $color   = $active ? 'text-red-600' : 'text-white';
        return '<th><a href="' . $url . '" class="inline-flex items-center gap-1 no-underline uppercase tracking-wider text-white ' . $color . '">'
             . $label
             . '<span class="inline-flex flex-col leading-none">'
             . '<i class="icon-[ph--caret-up-fill] text-[9px] ' . $upCol . '"></i>'
             . '<i class="icon-[ph--caret-down-fill] text-[9px] ' . $dnCol . '"></i>'
             . '</span></a></th>';
    }
@endphp
            <table class="table table-hover w-full text-sm table-borderless ">
               <thead class="sticky top-0 z-5" style="background: white">
                    <tr class="bg-success/67 shadow-md text-white text-xs">
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
                        <tr class="row-hover ">
                            <td class="font-mono text-base-content/60">{{ $employee->employee_id }}</td>
                            <td>
                                <a href="{{ route('employees.show', $employee) }}"
                                   class="text-base-content no-underline font-semibold hover:text-emerald-600">
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
                            <td colspan="7" class="py-10 text-center text-base-content/40">
                                <i class="icon-[ph--user-fill] text-3xl mb-2 block"></i>
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
                                   class="text-base-content no-underline font-semibold text-sm hover:text-emerald-600">
                                    {{ $employee->full_name }}
                                </a>
                                <div class="text-xs text-base-content/60 font-mono">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                        <span class="badge {{ $statusClass }} whitespace-nowrap">{{ $employee->employment_status }}</span>
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
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
                <div class="py-10 text-center text-base-content/40">
                    <i class="icon-[ph--user-fill] text-3xl mb-2 block"></i>
                    No employees found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
      <div class="px-6 py-4 border-t border-gray-200">
    {{ $employees->links('vendor.pagination.pagination') }}
</div>

    </div>

@endsection
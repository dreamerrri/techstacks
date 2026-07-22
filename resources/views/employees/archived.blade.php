@extends('layouts.app')

@section('title', 'Archived Employees')


@section('content')

 <a href="{{ route('employees.index') }}" class="back-link text-base-content/60 no-underline text-sm hover:text-emerald-600">
                <i class="icon-[ph--arrow-left-fill]"></i> Back to Employee page
            </a>
<x-table-card>
    <x-slot:title>
        <x-dot-loader /> Archived Employees
        <x-info-tooltip>
           Manage archived employees
        </x-info-tooltip>
    </x-slot:title>




<x-data-table>
    <x-slot:head>

             
                        <th>Employee ID</th>
                        <th>Full Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Actions</th>
               
    </x-slot:head>


               
          
       
                    @forelse($employees as $employee)
                        <tr>
                            <td class="font-mono text-base-content/60">{{ $employee->employee_id }}</td>
                            <td class="font-semibold text-base-content">{{ $employee->full_name }}</td>
                            <td class="text-base-content/60">{{ $employee->department }}</td>
                            <td class="text-base-content/60">{{ $employee->position }}</td>
                            <td>
                                <form method="POST" action="{{ route('employees.restore', $employee) }}"
                                      data-confirm="This employee will be restored to the active list."
                                      data-confirm-title="Restore Employee?"
                                      data-confirm-icon="question"
                                      data-confirm-btn="Yes, restore">
                                    @csrf @method('PATCH')
                                    <button class="btn  btn-success btn-sm">
                                        <i class="icon-[ph--arrow-counter-clockwise-fill]"></i> Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-base-content/40">
    <div class="flex flex-col items-center"> {{-- Remember this --}}
        <i class="icon-[ph--archive-fill] text-3xl mb-2"></i>
        <span>No archived employees.</span> {{-- And this --}}
    </div>
</td>
                        </tr>
                    @endforelse
    </x-data-table>
        {{-- Mobile Cards --}}
        <div class="md:hidden p-4 flex flex-col gap-3">
            @forelse($employees as $employee)
                <div class="card bg-base-100 border border-gray-200 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-500 to-gray-700 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-base-content text-sm">{{ $employee->full_name }}</div>
                                <div class="text-xs text-base-content/60 font-mono">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                        <span class="badge badge-soft badge-neutral whitespace-nowrap">Archived</span>
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                        <span><i class="icon-[ph--buildings-fill] w-3.5"></i> {{ $employee->department }}</span>
                        <span><i class="icon-[ph--briefcase-fill] w-3.5"></i> {{ $employee->position }}</span>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <form method="POST" action="{{ route('employees.restore', $employee) }}"
                              data-confirm="This employee will be restored to the active list."
                              data-confirm-title="Restore Employee?"
                              data-confirm-icon="question"
                              data-confirm-btn="Yes, restore">
                            @csrf @method('PATCH')
                            <button class="btn  btn-success btn-sm">
                                <i class="icon-[ph--arrow-counter-clockwise-fill]"></i> Restore
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-base-content/40">
                    <i class="icon-[ph--archive-fill] text-3xl mb-2 block"></i>
                    No archived employees.
                </div>
            @endforelse
        </div>

    
    <div class="mt-5">{{ $employees->links() }}</div>
</x-table-card>
@endsection
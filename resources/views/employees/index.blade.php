{{-- resources/views/employees/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Employee Management</h1>
        <div class="flex gap-2">
            <a href="{{ route('employees.archived') }}"
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">
                Archived Employees
            </a>
            <a href="{{ route('employees.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                + Add Employee
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('employees.index') }}"
          class="flex flex-wrap gap-3 mb-6 bg-white p-4 rounded shadow-sm">

        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name, ID, or email..."
               class="border rounded px-3 py-2 text-sm flex-1 min-w-[200px]">

        <select name="department" class="border rounded px-3 py-2 text-sm">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                    {{ $dept }}
                </option>
            @endforeach
        </select>

        <select name="status" class="border rounded px-3 py-2 text-sm">
            <option value="">All Status</option>
            @foreach(['Regular','Probationary','Contractual','Part-time'] as $status)
                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                    {{ $status }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
            Search
        </button>

        @if(request()->hasAny(['search','department','status']))
            <a href="{{ route('employees.index') }}"
               class="px-4 py-2 bg-gray-100 text-gray-600 rounded text-sm hover:bg-gray-200">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Employee ID</th>
                    <th class="px-4 py-3 text-left">Full Name</th>
                    <th class="px-4 py-3 text-left">Department</th>
                    <th class="px-4 py-3 text-left">Position</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Date Hired</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $employee->employee_id }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $employee->full_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $employee->department }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $employee->position }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                {{ $employee->employment_status === 'Regular' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $employee->employment_status === 'Probationary' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $employee->employment_status === 'Contractual' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $employee->employment_status === 'Part-time' ? 'bg-gray-100 text-gray-700' : '' }}
                            ">
                                {{ $employee->employment_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $employee->date_hired->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('employees.show', $employee) }}"
                                   class="text-blue-600 hover:underline text-xs">View</a>
                                <a href="{{ route('employees.edit', $employee) }}"
                                   class="text-yellow-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('employees.archive', $employee) }}"
                                      onsubmit="return confirm('Archive this employee?')">
                                    @csrf @method('PATCH')
                                    <button class="text-red-500 hover:underline text-xs">Archive</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            No employees found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $employees->links() }}
    </div>

</div>
@endsection
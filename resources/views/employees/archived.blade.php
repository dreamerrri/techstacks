{{-- resources/views/employees/archived.blade.php --}}
@extends('layouts.app')

@section('title', 'Archived Employees')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}" class="text-gray-500 hover:text-gray-700">← Back</a>
            <h1 class="text-2xl font-bold text-gray-800">Archived Employees</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Employee ID</th>
                    <th class="px-4 py-3 text-left">Full Name</th>
                    <th class="px-4 py-3 text-left">Department</th>
                    <th class="px-4 py-3 text-left">Position</th>
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
                            <form method="POST" action="{{ route('employees.restore', $employee) }}"
                                  data-confirm="This employee will be restored to the active list."
                                  data-confirm-title="Restore Employee?"
                                  data-confirm-icon="question"
                                  data-confirm-btn="Yes, restore">
                                @csrf @method('PATCH')
                                <button class="text-green-600 hover:underline text-xs">Restore</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">No archived employees.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $employees->links() }}</div>

</div>
@endsection
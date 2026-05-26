{{-- resources/views/employees/show.blade.php --}}
@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}" class="text-gray-500 hover:text-gray-700">← Back</a>
            <h1 class="text-2xl font-bold text-gray-800">{{ $employee->full_name }}</h1>
            <span class="px-2 py-1 rounded text-xs font-medium
                {{ $employee->employment_status === 'Regular' ? 'bg-green-100 text-green-700' : '' }}
                {{ $employee->employment_status === 'Probationary' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $employee->employment_status === 'Contractual' ? 'bg-blue-100 text-blue-700' : '' }}
                {{ $employee->employment_status === 'Part-time' ? 'bg-gray-100 text-gray-700' : '' }}
            ">
                {{ $employee->employment_status }}
            </span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('employees.edit', $employee) }}"
               class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                Edit
            </a>
            <form method="POST" action="{{ route('employees.archive', $employee) }}"
                  onsubmit="return confirm('Archive this employee?')">
                @csrf @method('PATCH')
                <button class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                    Archive
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Personal Information --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-gray-700 border-b pb-2 mb-4">Personal Information</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Employee ID</dt>
                    <dd class="font-mono font-medium">{{ $employee->employee_id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Full Name</dt>
                    <dd class="font-medium">{{ $employee->full_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Birthdate</dt>
                    <dd>{{ $employee->birthdate->format('F d, Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Gender</dt>
                    <dd>{{ $employee->gender }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Civil Status</dt>
                    <dd>{{ $employee->civil_status }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Contact No.</dt>
                    <dd>{{ $employee->contact_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Email</dt>
                    <dd>{{ $employee->email }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 mb-1">Address</dt>
                    <dd>{{ $employee->address }}</dd>
                </div>
            </dl>
        </div>

        {{-- Employment Details --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-gray-700 border-b pb-2 mb-4">Employment Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Department</dt>
                    <dd class="font-medium">{{ $employee->department }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Position</dt>
                    <dd>{{ $employee->position }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Date Hired</dt>
                    <dd>{{ $employee->date_hired->format('F d, Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Salary Type</dt>
                    <dd>{{ $employee->salary_type }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Basic Salary</dt>
                    <dd class="font-semibold text-green-700">
                        ₱{{ number_format($employee->basic_salary, 2) }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Government Contributions --}}
        <div class="bg-white rounded shadow p-5 md:col-span-2">
            <h2 class="font-semibold text-gray-700 border-b pb-2 mb-4">Government Contributions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">SSS Number</p>
                    <p class="font-mono font-medium">{{ $employee->sss_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">PhilHealth Number</p>
                    <p class="font-mono font-medium">{{ $employee->philhealth_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Pag-IBIG Number</p>
                    <p class="font-mono font-medium">{{ $employee->pagibig_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">TIN Number</p>
                    <p class="font-mono font-medium">{{ $employee->tin_number ?? '—' }}</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
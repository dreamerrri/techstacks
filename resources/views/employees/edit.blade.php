@extends('layouts.app')

@section('title', 'Edit Employee')
@section('breadcrumb')
    <span>Manage Employees</span>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <a href="{{ route('employees.index') }}" class="text-white/55 no-underline">Employees</a>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <a href="{{ route('employees.show', $employee) }}" class="text-white/55 no-underline">{{ $employee->full_name }}</a>
    <i class="icon-[ph--caret-right-fill] text-xs"></i>
    <span class="text-white font-semibold">Edit {{ $employee->full_name }}</span>
@endsection

@section('content')

    <div class="mb-5">
        <a href="{{ route('employees.show', $employee) }}" class="text-gray-500 no-underline text-sm hover:text-emerald-600">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Employee Profile
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i class="icon-[ph--user-fill]-edit text-red-600"></i> Edit — {{ $employee->full_name }}
        </h2>

        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf @method('PUT')
            @include('employees.form')
            <div class="flex gap-3 flex-wrap mt-6">
                <button type="submit" class="btn btn-soft btn-error">
                    <i class="icon-[ph--floppy-disk-fill]"></i> Update Employee
                </button>
                <a href="{{ route('employees.show', $employee) }}" class="btn btn-soft">Cancel</a>
            </div>
        </form>
    </div>

@endsection
@extends('layouts.app')

@section('title', 'Edit Employee')


@section('content')

    <div class="mb-5">
        <a href="{{ route('employees.show', $employee) }}" class="back-link text-base-content/60 no-underline text-sm hover:text-success">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Employee Profile
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm p-6">
        <h2 class="text-base font-bold text-base-content mb-6 flex items-center gap-2">
            <i class="icon-[tabler--user]-edit text-error"></i> Edit — {{ $employee->full_name }}
        </h2>

        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf @method('PUT')
            @include('employees.form')
            <div class="flex gap-3 flex-wrap mt-6">
                <button type="submit" class="btn  btn-error">
                    <i class="icon-[ph--floppy-disk-fill]"></i> Update Employee
                </button>
                <a href="{{ route('employees.show', $employee) }}" class="btn ">Cancel</a>
            </div>
        </form>
    </div>

@endsection
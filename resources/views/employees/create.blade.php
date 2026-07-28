@extends('layouts.app')

@section('title', 'Add Employee')


@section('content')

    <div class="mb-5">
        <a href="{{ route('employees.index') }}" class="back-link text-base-content no-underline text-sm hover:text-primary">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Employee List
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm p-6">
        <h2 class="text-base font-bold text-base-content mb-6 flex items-center gap-2">
            <i class="icon-[tabler--user]-plus text-error"></i> Add New Employee
        </h2>

        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            @include('employees.form')
            <div class="flex gap-3 flex-wrap mt-6">
                <button type="submit" class="btn btn-soft btn-error">
                    <i class="icon-[ph--floppy-disk-fill]"></i> Save Employee
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-success btn-soft">Cancel</a>
            </div>
        </form>
    </div>

@endsection
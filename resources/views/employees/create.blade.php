@extends('layouts.app')

@section('title', 'Add Employee')


@section('content')

    <div class="mb-5">
        <a href="{{ route('employees.index') }}" class="back-link text-base-content/60 no-underline text-sm hover:text-emerald-600">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Employee List
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm p-6">
        <h2 class="text-base font-bold text-base-content mb-6 flex items-center gap-2">
            <i class="icon-[tabler--user]-plus text-red-600"></i> Add New Employee
        </h2>

        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            @include('employees.form')
            <div class="flex gap-3 flex-wrap mt-6">
                <button type="submit" class="btn btn-soft btn-error">
                    <i class="icon-[ph--floppy-disk-fill]"></i> Save Employee
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-soft">Cancel</a>
            </div>
        </form>
    </div>

@endsection
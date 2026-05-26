{{-- resources/views/employees/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('employees.index') }}" class="text-gray-500 hover:text-gray-700">← Back</a>
        <h1 class="text-2xl font-bold text-gray-800">Add New Employee</h1>
    </div>

    <form method="POST" action="{{ route('employees.store') }}"
          class="bg-white rounded shadow p-6">
        @csrf

        @include('employees.form')

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                Save Employee
            </button>
            <a href="{{ route('employees.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">
                Cancel
            </a>
        </div>
    </form>

</div>
@endsection
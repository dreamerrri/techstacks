@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')

    <div style="margin-bottom:20px;">
        <a href="{{ route('employees.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Employee List
        </a>
    </div>

    <div class="aurora-card">
        <h2 class="aurora-card-title">
            <i class="fas fa-user-plus" style="color:#dc2626;"></i> Add New Employee
        </h2>

        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            @include('employees.form')
            <div style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save"></i> Save Employee
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>

@endsection
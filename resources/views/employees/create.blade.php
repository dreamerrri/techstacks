@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')

    <div style="margin-bottom:20px;">
        <a href="{{ route('employees.index') }}" style="color:#6b7280; text-decoration:none; font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Employee List
        </a>
    </div>

    <div class="card">
        <h2><i class="fas fa-user-plus" style="color:#dc2626;"></i> Add New Employee</h2>

        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            @include('employees.form')
            <div style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
                <button type="submit"
                        style="padding:10px 24px; background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;">
                    <i class="fas fa-save"></i> Save Employee
                </button>
                <a href="{{ route('employees.index') }}"
                   style="padding:10px 24px; background:#f3f4f6; color:#374151; border-radius:6px; text-decoration:none; font-weight:600;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

@endsection
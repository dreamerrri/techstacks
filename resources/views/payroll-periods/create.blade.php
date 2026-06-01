@extends('layouts.app')

@section('title', 'Create Payroll Period')

@section('content')

@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isHR = $user->isHR();
    $color = $isAdmin ? '#dc2626' : ($isHR ? '#2563eb' : '#667eea');
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <a href="{{ route('manual-payroll-attendance.index') }}"
           style="color:#6b7280; text-decoration:none; font-size:14px; display:inline-flex; align-items:center; gap:6px; margin-bottom:8px;">
            <i class="fas fa-arrow-left"></i> Back to Payroll Periods
        </a>
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-calendar-plus"></i> Create Payroll Period
        </div>
        <h2 style="margin:8px 0 4px 0;">New Payroll Period</h2>
        <p style="color:#6b7280; margin:0;">Define the cutoff dates for payroll processing</p>
    </div>
</div>

<div style="max-width:600px;">
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 25px; border-bottom:1px solid #e5e7eb;">
            <h3 style="margin:0;">Payroll Period Details</h3>
        </div>

        <form action="{{ route('payroll-periods.store') }}" method="POST" style="padding:25px;">
            @csrf

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Cutoff Start Date</label>
                <input type="date" name="cutoff_start" required
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">First day of the payroll period</p>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Cutoff End Date</label>
                <input type="date" name="cutoff_end" required
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">Last day of the payroll period</p>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:14px;">Payroll Date</label>
                <input type="date" name="payroll_date" required
                       style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                <p style="color:#6b7280; font-size:12px; margin-top:4px;">Date when payroll will be processed/paid</p>
            </div>

            @error('cutoff_start')
            <div style="color:#dc2626; font-size:13px; margin-bottom:16px;">{{ $message }}</div>
            @enderror

            @error('cutoff_end')
            <div style="color:#dc2626; font-size:13px; margin-bottom:16px;">{{ $message }}</div>
            @enderror

            @error('payroll_date')
            <div style="color:#dc2626; font-size:13px; margin-bottom:16px;">{{ $message }}</div>
            @enderror

            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="flex:1; padding:12px 20px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600;">
                    <i class="fas fa-save"></i> Create Payroll Period
                </button>
                <a href="{{ route('manual-payroll-attendance.index') }}"
                   style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

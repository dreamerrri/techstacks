@extends('layouts.app')

@section('title', 'Edit Work Request')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <a href="{{ route('work-requests.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Work Requests</a>
    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Edit Request</span>
@endsection

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
        <div style="display:inline-block; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:8px;">
            <i class="fas fa-edit"></i> Edit Request
        </div>
        <h2 style="margin:8px 0 4px 0;">Edit Work Request #{{ $workRequest->id }}</h2>
        <p style="color:#6b7280; margin:0;">
            Modify your pending work request
        </p>
    </div>
    <a href="{{ route('work-requests.show', $workRequest) }}"
       style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
        <i class="fas fa-arrow-left"></i> Back to Request
    </a>
</div>

{{-- Form --}}
<div class="card" style="padding:32px; max-width:800px;">
    <form id="workRequestForm">
        @csrf
        @method('PUT')
        
        {{-- Request Type --}}
        <div style="margin-bottom:24px;">
            <label style="display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
                Request Type <span style="color:#ef4444;">*</span>
            </label>
            <select name="request_type" id="request_type" required
                    style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                <option value="">Select type...</option>
                <option value="weekend" {{ $workRequest->request_type === 'weekend' ? 'selected' : '' }}>Weekend Work</option>
                <option value="holiday" {{ $workRequest->request_type === 'holiday' ? 'selected' : '' }}>Holiday Work</option>
                <option value="overtime" {{ $workRequest->request_type === 'overtime' ? 'selected' : '' }}>Overtime</option>
            </select>
        </div>

        {{-- Work Date --}}
        <div style="margin-bottom:24px;">
            <label style="display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
                Work Date <span style="color:#ef4444;">*</span>
            </label>
            <input type="date" name="work_date" id="work_date" required min="{{ now()->toDateString() }}"
                   value="{{ $workRequest->work_date->format('Y-m-d') }}"
                   style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
        </div>

        {{-- Time Range --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
            <div>
                <label style="display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
                    Start Time
                </label>
                <input type="time" name="start_time" id="start_time" value="{{ $workRequest->start_time }}"
                       style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
            </div>
            <div>
                <label style="display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
                    End Time
                </label>
                <input type="time" name="end_time" id="end_time" value="{{ $workRequest->end_time }}"
                       style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
            </div>
        </div>

        {{-- Estimated Hours --}}
        <div style="margin-bottom:24px;">
            <label style="display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
                Estimated Hours
            </label>
            <input type="number" name="estimated_hours" id="estimated_hours" min="0" max="24" step="0.5"
                   value="{{ $workRequest->estimated_hours }}"
                   style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
        </div>

        {{-- Reason --}}
        <div style="margin-bottom:32px;">
            <label style="display:block; font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
                Reason
            </label>
            <textarea name="reason" id="reason" rows="4" maxlength="500"
                      style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; resize:vertical;"
                      placeholder="Provide a reason for this work request...">{{ $workRequest->reason }}</textarea>
        </div>

        {{-- Submit Button --}}
        <div style="display:flex; gap:12px;">
            <button type="submit"
                    style="padding:12px 32px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600;">
                <i class="fas fa-save"></i> Update Request
            </button>
            <a href="{{ route('work-requests.show', $workRequest) }}"
               style="padding:12px 32px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center;">
                Cancel
            </a>
        </div>
    </form>
</div>

{{-- Upcoming Holidays Reference --}}
@if($upcomingHolidays->count() > 0)
<div class="card" style="padding:24px; margin-top:24px;">
    <h3 style="margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-calendar-alt" style="color:#6b7280;"></i> Upcoming Holidays
    </h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
        @foreach($upcomingHolidays as $holiday)
        <div style="padding:12px; background:#f9fafb; border-radius:6px; border-left:4px solid {{ $holiday->type === 'regular' ? '#f59e0b' : '#3b82f6' }};">
            <div style="font-size:14px; font-weight:600; color:#1f2937;">{{ $holiday->name }}</div>
            <div style="font-size:12px; color:#6b7280; margin-top:4px;">{{ $holiday->date->format('M d, Y') }}</div>
            <div style="font-size:11px; color:#6b7280; margin-top:2px;">
                <span style="padding:2px 6px; border-radius:4px; background:#e5e7eb; font-weight:600;">
                    {{ ucfirst($holiday->type) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
const getVisibleForm = (forms) => {
    for (let form of forms) {
        if (form.offsetParent !== null) {
            return form;
        }
    }
    return forms[0]; // Fallback to first if none visible
};

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('#workRequestForm');
    
    if (forms.length > 0) {
        const form = getVisibleForm(forms);
        
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('{{ route('work-requests.update', $workRequest) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route('work-requests.show', $workRequest) }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update request: ' + error.message
                    });
                });
            });
        }
    }
});
</script>
@endsection

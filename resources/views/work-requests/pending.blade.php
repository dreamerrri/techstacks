@extends('layouts.app')

@section('title', 'Pending Work Requests')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Dashboard</a>
    <i class="icon-[ph--caret-right-fill]" style="font-size:11px;"></i>
    <a href="{{ route('work-requests.index') }}" style="color:rgba(255,255,255,0.55); text-decoration:none;">Work Requests</a>
    <i class="icon-[ph--caret-right-fill]" style="font-size:11px;"></i>
    <span style="color:white; font-weight:600;">Pending Requests</span>
@endsection

@section('content')
@php
    $user = auth()->user();
    $admin = $user->isAdmin();
    $hr = $user->isHR();
    $color = $admin ? '#dc2626' : ($hr ? '#2563eb' : '#667eea');
    $colorDark = $admin ? '#991b1b' : ($hr ? '#1e40af' : '#764ba2');
@endphp



<x-table-card action="{{ route('work-requests.pending') }}">
    <x-slot:title>
        <x-dot-loader /> Pending Work Requests
        <x-info-tooltip>
            {{ $admin || $hr ? 'Review and manage pending work requests' : 'View and manage your pending work requests' }}
        </x-info-tooltip>

   
    </x-slot:title>

<x-slot:actions>
          <a href="{{ route('work-requests.index') }}"
           style="padding:12px 20px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="icon-[ph--list-fill]"></i> All Requests
        </a>
</x-slot:actions>


    <x-data-table>
@if($pendingRequests->count() > 0)
 <x-slot:head>

              
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Work Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Actions</th>
    </x-slot:head>
                <tbody>
                    @foreach($pendingRequests as $request)
                    <tr class="row-hover">
                        <td>
                            <div>{{ $request->employee->full_name }}</div>
                            <div>{{ $request->employee->employee_id }}</div>
                            <div>{{ $request->employee->position }}</div>
                        </td>
                        <td>
                            <span style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:600; 
                                {{ $request->request_type === 'weekend' ? 'background:#dbeafe; color:#1e40af;' : 
                                   ($request->request_type === 'holiday' ? 'background:#fef3c7; color:#92400e;' : 'background:#e0e7ff; color:#3730a3;') }}">
                                {{ ucfirst($request->request_type) }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; font-size:14px; color:#1f2937;">
                            {{ $request->work_date->format('M d, Y') }}
                            <div style="font-size:12px; color:#6b7280;">{{ $request->work_date->format('l') }}</div>
                        </td>
                        <td>
                            {{ $request->start_time ? $request->start_time : '-' }} 
                            @if($request->end_time) - {{ $request->end_time }}@endif
                            @if($request->estimated_hours)
                            <div style="font-size:12px; color:#6b7280;">{{ number_format($request->estimated_hours, 2) }} hrs</div>
                            @endif
                        </td>
                        <td>
                            {{ $request->reason ? \Illuminate\Support\Str::limit($request->reason, 50) : '-' }}
                        </td>
                        <td>
                          <a href="{{ route('work-requests.show', $request) }}" class="btn btn-soft btn-info btn-sm">
    <i class="icon-[ph--eye-fill]"></i>
</a>
<button type="button" class="btn btn-soft btn-success btn-sm" onclick="approveRequest({{ $request->id }})">
    <i class="icon-[ph--check-fill]"></i>
</button>
<button type="button" class="btn btn-soft btn-error btn-sm" onclick="showRejectModal({{ $request->id }})">
    <i class="icon-[ph--x]"></i>
</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="card" style="padding:48px; text-align:center;">
        <i class="icon-[ph--check-circle-fill]" style="font-size:48px; color:#10b981; margin-bottom:16px;"></i>
        <h3 style="margin:0 0 8px 0; color:#6b7280;">All Caught Up!</h3>
        <p style="color:#9ca3af; margin:0 0 24px 0;">
            There are no pending work requests to review.
        </p>
        <a href="{{ route('work-requests.index') }}"
           style="padding:12px 24px; background:{{ $color }}; color:white; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
            <i class="icon-[ph--list-fill]"></i> View All Requests
        </a>
    </div>
@endif


    </x-data-table>


</x-table-card>
@endsection




@section('scripts')
<script>
function approveRequest(requestId) {
    Swal.fire({
        title: 'Approve Work Request',
        text: 'Are you sure you want to approve this work request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route('work-requests.approve', ':id') }}'.replace(':id', requestId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('notyf_success', data.message);
                    window.location.reload();
                } else {
                    window.notyf.error(data.message ?? 'Something went wrong.');
                }
            })
            .catch(error => {
                window.notyf.error('Failed to approve request: ' + error.message);
            });
        }
    });
}

function showRejectModal(requestId) {
    Swal.fire({
        title: 'Reject Request',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Please provide a reason for rejection...',
        inputAttributes: {
            maxlength: 500,
            required: true,
        },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
        preConfirm: (reason) => {
            if (!reason) {
                Swal.showValidationMessage('Please provide a rejection reason');
            }
            return reason;
        },
    }).then(result => {
        if (result.isConfirmed) {
            rejectRequest(requestId, result.value);
        }
    });
}



function rejectRequest(requestId, reason) {
    const formData = new FormData();
    formData.append('rejection_reason', reason);

    fetch('{{ route('work-requests.reject', ':id') }}'.replace(':id', requestId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            sessionStorage.setItem('notyf_success', data.message);
            window.location.reload();
        } else {
            window.notyf.error(data.message ?? 'Something went wrong.');
        }
    })
    .catch(error => {
        window.notyf.error('Failed to reject request: ' + error.message);
    });
}


</script>
@endsection

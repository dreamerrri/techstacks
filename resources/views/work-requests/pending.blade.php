@extends('layouts.app')

@section('title', 'Pending Work Requests')


@section('content')
@php
    $user = auth()->user();
    $admin = $user->isAdmin();
    $hr = $user->isHR();
    $color = $admin ? '#dc2626' : ($hr ? '#2563eb' : '#667eea');
    $colorDark = $admin ? '#991b1b' : ($hr ? '#1e40af' : '#764ba2');
@endphp

{{-- -ACTUALLY THIS PAGE IS REDUNDANT SINCE MAY FILTER NAMAN SI WORK-REQUEST INDEX --}}

<x-table-card action="{{ route('work-requests.pending') }}">
    <x-slot:title>
        <x-dot-loader /> Pending Work Requests
        <x-info-tooltip>
            {{ $admin || $hr ? 'Review and manage pending work requests' : 'View and manage your pending work requests' }}
        </x-info-tooltip>

   
    </x-slot:title>

<x-slot:actions>
     
         <a href="{{ route('work-requests.index') }}" class="btn btn-soft btn-warning btn-sm">
                        <i class="icon-[tabler--clock]"></i> All Requests
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
                            <span class="badge badge-sm font-semibold
                                {{ $request->request_type === 'weekend' ? 'badge-info' :
                                   ($request->request_type === 'holiday' ? 'badge-warning' : 'badge-primary') }}">
                                {{ ucfirst($request->request_type) }}
                            </span>
                        </td>
                        <td class="text-sm text-base-content p-4">
                            {{ $request->work_date->format('M d, Y') }}
                            <div class="text-xs text-base-content/60">{{ $request->work_date->format('l') }}</div>
                        </td>
                        <td>
                            {{ $request->start_time ? $request->start_time : '-' }} 
                            @if($request->end_time) - {{ $request->end_time }}@endif
                            @if($request->estimated_hours)
                            <div class="text-xs text-base-content/60">{{ number_format($request->estimated_hours, 2) }} hrs</div>
                            @endif
                        </td>
                        <td>
                            {{ $request->reason ? \Illuminate\Support\Str::limit($request->reason, 50) : '-' }}
                        </td>
                        <td>
                          <a href="{{ route('work-requests.show', $request) }}" class="btn btn-soft  btn-info btn-sm">
    <i class="icon-[ph--eye-fill]"></i>
</a>
<button type="button" class="btn btn-soft btn-success btn-sm" onclick="approveRequest({{ $request->id }})">
    <i class="icon-[tabler--check]"></i>
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
    <div class="card text-center p-4">
        <i class="icon-[tabler--circle-check] text-success mb-4"></i>
        <h3 class="text-base-content/60">All Caught Up!</h3>
        <p class="text-base-content/60">
            There are no pending work requests to review.
        </p>
        <a href="{{ route('work-requests.index') }}"
           class="inline-flex items-center font-semibold text-sm bg-primary rounded-lg p-4 gap-3 cursor-pointer no-underline">
            <i class="icon-[ph--list-fill]"></i> View All Requests
        </a>
    </div>
@endif


    </x-data-table>


    {{-- Mobile Cards --}}
    <div class="md:hidden p-4 flex flex-col gap-3">
        @if($pendingRequests->count() > 0)
            @foreach($pendingRequests as $request)
                <div class="card bg-base-100 border border-base-300 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="text-sm text-base-content font-semibold">{{ $request->employee->full_name }}</div>
                            <div class="text-xs text-base-content/60">{{ $request->employee->employee_id }}</div>
                            <div class="text-xs text-base-content/60">{{ $request->employee->position }}</div>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $request->request_type === 'weekend' ? 'bg-blue-100 text-blue-800' : ($request->request_type === 'holiday' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800') }}">
                            {{ ucfirst($request->request_type) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-base-content/60 mt-2">
                        <span><i class="icon-[ph--calendar-fill] w-3.5"></i> {{ $request->work_date->format('M d, Y') }}</span>
                        <span><i class="icon-[ph--calendar-blank-fill] w-3.5"></i> {{ $request->work_date->format('l') }}</span>
                        <span><i class="icon-[ph--clock-fill] w-3.5"></i> {{ $request->start_time ? $request->start_time : '-' }} @if($request->end_time) - {{ $request->end_time }}@endif</span>
                        @if($request->estimated_hours)
                            <span><i class="icon-[ph--hourglass-fill] w-3.5"></i> {{ number_format($request->estimated_hours, 2) }} hrs</span>
                        @endif
                    </div>

                    @if($request->reason)
                        <div class="text-xs text-base-content/60 mt-2">
                            <i class="icon-[ph--text-align-left-fill] w-3.5"></i> {{ \Illuminate\Support\Str::limit($request->reason, 80) }}
                        </div>
                    @endif

                    <div class="flex gap-2 flex-wrap mt-3 pt-3 border-t border-base-200">
                        <a href="{{ route('work-requests.show', $request) }}" class="btn btn-soft btn-info btn-sm">
                            <i class="icon-[ph--eye-fill]"></i> View
                        </a>
                        <button type="button" class="btn btn-soft btn-success btn-sm" onclick="approveRequest({{ $request->id }})">
                            <i class="icon-[ph--check-fill]"></i> Approve
                        </button>
                        <button type="button" class="btn btn-soft btn-error btn-sm" onclick="showRejectModal({{ $request->id }})">
                            <i class="icon-[ph--x]"></i> Reject
                        </button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="py-10 text-center text-base-content/40">
                <i class="icon-[ph--check-circle-fill] text-3xl mb-2 block"></i>
                All caught up! No pending requests.
            </div>
        @endif
    </div>

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
            fetch('/work-requests/' + requestId + '/approve', {
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

    fetch('/work-requests/' + requestId + '/reject', {
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

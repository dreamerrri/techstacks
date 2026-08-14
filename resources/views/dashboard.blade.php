@extends('layouts.app')

@section('title')
    @if($user->role === 'admin') Admin Dashboard
    @elseif($user->role === 'hr') HR Dashboard
    @else Dashboard
    @endif
@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet'>
@endpush

@section('content')

@php
    $isAdmin = $user->role === 'admin';
    $isHR    = $user->role === 'hr';
@endphp

{{-- Role access badge --}}
@if($isAdmin)
    <span class="badge badge-soft badge-primary mb-4">
        <i class="icon-[tabler--shield-check]"></i> Administrator Access
    </span>
@elseif($isHR)
    <span class="badge badge-soft badge-primary mb-4">
        <i class="icon-[tabler--user]"></i> HR Department Access
    </span>
@endif

<div class="text-base-content text-lg mb-5">
    Welcome back, <strong>{{ $user->name }}</strong>
    @if($isAdmin) — You have full administrative access.
    @elseif($isHR) — You have HR access privileges.
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">

    @if($isAdmin)
        <a href="{{ route('users.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                <i class="icon-[tabler--users]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['total_users'] }}</div>
            <div class="text-xs text-muted uppercase tracking-widest">Total Users</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-error bg-error/10">
                <i class="icon-[tabler--shield-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['admin_users'] }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Admin Users</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-warning bg-warning/10">
                <i class="icon-[tabler--user]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['hr_users'] }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">HR Personnel</div>
        </a>

        <a href="{{ route('users.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-success bg-success/10">
                <i class="icon-[tabler--circle-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['active_users'] }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Active Accounts</div>
        </a>

    @elseif($isHR)
        <a href="{{ route('employees.index') }}" class="card bg-base-100 border border-base-300 p-5 text-center hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                <i class="icon-[tabler--users]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['total_employees'] }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Total Employees</div>
        </a>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-success bg-success/10">
                <i class="icon-[tabler--calendar-check]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['regular'] }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Regular</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-warning bg-warning/10">
                <i class="icon-[tabler--clock]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['probationary'] }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Probationary</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-base-content bg-base-200">
                <i class="icon-[tabler--archive]"></i>
            </div>
            <div class="text-3xl font-bold text-base-content mb-1">{{ $counts['archived'] }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Archived</div>
        </div>

    @else
        {{-- Employee stats pulled from their own record --}}
        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-primary bg-primary/10">
                <i class="icon-[tabler--building]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $user->employee?->department ?? '—' }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Department</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-secondary bg-secondary/10">
                <i class="icon-[tabler--id-badge]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $user->employee?->position ?? '—' }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Position</div>
        </div>

        @php
            $empStatus = $user->employee?->employment_status;
            $empStatusColor = match($empStatus) {
                'Regular'      => 'text-success bg-success/10',
                'Probationary' => 'text-warning bg-warning/10',
                'Contractual'  => 'text-info bg-info/10',
                'Part-time'    => 'text-base-content bg-base-200',
                default        => 'text-base-content bg-base-200',
            };
        @endphp
        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 {{ $empStatusColor }}">
                <i class="icon-[tabler--briefcase]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">{{ $empStatus ?? '—' }}</div>
            <div class="text-xs text-muted uppercase tracking-widest ">Employment Status</div>
        </div>

        <div class="card bg-base-100 border border-base-300 p-5 text-center">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 text-accent bg-accent/10">
                <i class="icon-[tabler--calendar]"></i>
            </div>
            <div class="text-2xl font-small text-base-content mb-1">
                {{ $user->employee?->date_hired ? \Carbon\Carbon::parse($user->employee->date_hired)->format('M d, Y') : '—' }}
            </div>
            <div class="text-xs text-muted uppercase tracking-widest ">Date Hired</div>
        </div>
    @endif

</div>

{{-- Quick Actions --}}
<div class="card border border-base-300 shadow-sm p-6 mb-5">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
        <i class="icon-[ph--lightning-fill]"></i>
        @if($isAdmin) Administrative Actions
        @elseif($isHR) HR Actions
        @else Quick Actions
        @endif
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

@if($isAdmin)
    <a href="{{ route('employees.create') }}" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--user-plus]"></i>
        </div>
        <span class="text-muted">Create Users</span>
    </a>
    <a href="{{ route('roles.index') }}" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--lock]"></i>
        </div>
        <span class="text-muted">Manage Roles</span>
    </a>
    <a href="#" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--database]"></i>
        </div>
        <span class="text-muted">System Backup</span>
    </a>
    <a href="{{ route('audit-logs.index') }}" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--history]"></i>
        </div>
        <span class="text-muted">View Logs</span>
    </a>

@elseif($isHR)
    <a href="{{ route('employees.create') }}" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--user-plus]"></i>
        </div>
        <span class="text-muted">Add Employee</span>
    </a>
    <a href="{{ route('payroll.index') }}" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--calculator]"></i>
        </div>
        <span class="text-muted">Payroll</span>
    </a>
    <a href="#" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--inbox]"></i>
        </div>
        <span class="text-muted">Leave Requests</span>
    </a>
    <a href="#" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--file-type-pdf]"></i>
        </div>
        <span class="text-muted">Reports</span>
    </a>

@else
    <a href="{{ route('profile.show') }}" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--user]"></i>
        </div>
        <span class="text-muted">My Profile</span>
    </a>
    <a href="{{ route('payroll.index') }}" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--receipt]"></i>
        </div>
        <span class="text-muted">Payslips</span>
    </a>
    <a href="#" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--calendar-off]"></i>
        </div>
        <span class="text-muted">Leave Request</span>
    </a>
    <a href="#" class="btn btn-soft flex-col h-auto py-5 gap-2">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl text-primary bg-primary/10">
            <i class="icon-[tabler--clock]"></i>
        </div>
        <span class="text-muted">Attendance</span>
    </a>
@endif
    </div>
</div>

{{-- Calendar --}}
<div class="card border border-base-300 shadow-sm p-6 mb-5">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
        <i class="icon-[tabler--calendar]"></i>
        Calendar
    </h2>
    <div class="card flex not-prose p-4 w-full">
        <div id="calendar-custom" style="min-height: 500px;"></div>
    </div>
</div>

<!-- Modal Button (Hidden) -->
<button type="button" class="btn hidden" id="modalTrigger" aria-haspopup="dialog" aria-expanded="false" aria-controls="calendar-event-modal" data-overlay="#calendar-event-modal"></button>

<!-- Modal -->
<div id="calendar-event-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Event</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#calendar-event-modal"><span class="icon-[tabler--x] size-4"></span></button>
            </div>
            <form id="eventForm">
                <div class="modal-body pt-0">
                    <div class="mb-4">
                        <label class="label-text" for="eventTitle">Add event title below</label>
                        <input type="text" id="eventTitle" class="input" placeholder="Event title" required />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft btn-secondary" data-overlay="#calendar-event-modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- System Information --}}
<div class="card bg-base-100 border border-base-300 p-6">
    <h2 class="text-xs font-semibold uppercase tracking-widest text-primary mb-4 flex items-center gap-2">
        <i class="icon-[tabler--id-badge]"></i>
        System Information
    </h2>
    <div class="flex flex-col">
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-muted ">Name</span>
            <span class="font-semibold text-base-content text-right">{{ $user->name }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-muted ">Email</span>
            <span class="font-semibold text-base-content text-right">{{ $user->email }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-muted ">Role</span>
            <span class="font-semibold text-base-content text-right">
                @if($isAdmin) Administrator
                @elseif($isHR) HR Personnel
                @else Employee
                @endif
            </span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-base-200">
            <span class="text-muted ">Account Status</span>
            <span class="font-semibold text-base-content text-right">
                @if($user->is_active)
                    <span class="badge badge-soft badge-primary">
                        <i class="icon-[tabler--circle-check]"></i> Active
                    </span>
                @else
                    <span class="badge badge-soft badge-error">
                        <i class="icon-[tabler--circle-x]"></i> Inactive
                    </span>
                @endif
            </span>
        </div>
        <div class="flex justify-between items-center py-3">
            <span class="text-muted ">Last Login</span>
            <span class="font-semibold text-base-content text-right">
                @if($user->last_login_at)
                    {{ $user->last_login_at->format('M d, Y h:i A') }}
                @else
                    First Login
                @endif
            </span>
        </div>
    </div>
</div>

@endsection

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
console.log('Calendar script loaded directly in body');
document.addEventListener('DOMContentLoaded', function () {
  console.log('DOMContentLoaded fired');
  console.log('FullCalendar loaded:', typeof FullCalendar !== 'undefined');
  const calendarCustomExample = document.getElementById('calendar-custom')
  console.log('Calendar element found:', calendarCustomExample);
  if (!calendarCustomExample) {
    console.error('Calendar element not found');
    return;
  }
  let selectedEvent = null
  let selectedDateInfo = null
  function addDays(date, days) {
    const result = new Date(date)
    result.setDate(result.getDate() + days)
    return result
  }
  function formatDate(date) {
    return date.toLocaleDateString('en-GB', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
  }
  const today = new Date()
  const events = [
    {
      title: 'Past Event',
      start: addDays(today, -2).toISOString().split('T')[0],
      classNames: ['fc-event-info']
    },
    {
      title: 'All Day Event',
      start: addDays(today, 2).toISOString().split('T')[0],
      classNames: ['fc-event-info']
    },
    {
      title: 'Long Event',
      start: addDays(today, 2).toISOString().split('T')[0],
      end: addDays(today, 5).toISOString().split('T')[0],
      classNames: ['fc-event-primary']
    },
    {
      title: 'Confirm tech stack',
      start: addDays(today, 0).toISOString().split('T')[0] + 'T10:00:00',
      end: addDays(today, 0).toISOString().split('T')[0] + 'T18:00:00',
      classNames: ['fc-event-success']
    },
    {
      groupId: '999',
      title: 'Coding session',
      start: addDays(today, 1).toISOString().split('T')[0] + 'T16:00:00',
      classNames: ['fc-event-secondary']
    },
    {
      groupId: '999',
      title: 'Coding session',
      start: addDays(today, 8).toISOString().split('T')[0] + 'T16:00:00',
      classNames: ['fc-event-secondary']
    },
    {
      title: 'Conference',
      start: addDays(today, 9).toISOString().split('T')[0],
      end: addDays(today, 10).toISOString().split('T')[0],
      classNames: ['fc-event-primary']
    },
    {
      title: 'Meeting',
      start: addDays(today, 9).toISOString().split('T')[0] + 'T10:30:00',
      end: addDays(today, 9).toISOString().split('T')[0] + 'T12:30:00',
      classNames: ['fc-event-error']
    },
    {
      title: 'Lunch',
      start: addDays(today, 9).toISOString().split('T')[0] + 'T12:40:00',
      classNames: ['fc-event-warning']
    },
    {
      title: 'Meeting',
      start: addDays(today, 9).toISOString().split('T')[0] + 'T14:30:00',
      classNames: ['fc-event-error']
    },
    {
      title: 'Picnic',
      start: addDays(today, 12).toISOString().split('T')[0],
      classNames: ['fc-event-success']
    },
    {
      title: 'Yoga',
      start: addDays(today, 15).toISOString().split('T')[0],
      classNames: ['fc-event-info']
    },
    {
      title: 'Credit Card Payment',
      start: addDays(today, 23).toISOString().split('T')[0],
      end: addDays(today, 24).toISOString().split('T')[0],
      classNames: ['fc-event-warning']
    },
    {
      title: 'Meeting with client',
      start: addDays(today, 27).toISOString().split('T')[0],
      classNames: ['fc-event-success']
    },
    {
      start: addDays(today, 17).toISOString().split('T')[0],
      end: addDays(today, 20).toISOString().split('T')[0],
      display: 'background',
      classNames: ['fc-event-disabled']
    }
  ]
  try {
    const calendarCustom = new FullCalendar.Calendar(calendarCustomExample, {
      initialView: 'dayGridMonth',
      initialDate: today.toISOString().split('T')[0],
      editable: true,
      dragScroll: true,
      dayMaxEvents: 2,
      direction: 'ltr',
      eventResizableFromStart: true,
      selectable: true,
      headerToolbar: {
        left: 'prev,next title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
      },
      buttonText: {
        month: 'Month',
        week: 'Week',
        day: 'Day',
        list: 'List'
      },
      events: events,
      select: function (info) {
        const blockedStart = addDays(today, 17).getTime()
        const blockedEnd = addDays(today, 20).getTime()
        const selectedStart = info.start.getTime()
        const selectedEnd = info.end ? info.end.getTime() : selectedStart
        if (
          (selectedStart < blockedEnd && selectedEnd > blockedStart) ||
          (selectedEnd > blockedStart && selectedStart < blockedEnd)
        ) {
          alert('Events cannot be added in the blocked date range.')
          calendarCustom.unselect()
          return
        }
        selectedEvent = null
        selectedDateInfo = info
        document.getElementById('modalTitle').textContent = `${formatDate(info.start)}`
        document.getElementById('eventForm').reset()
        document.getElementById('modalTrigger').click()
      },
      eventClick: function (info) {
        selectedEvent = info.event
        document.getElementById('modalTitle').textContent = `${formatDate(info.event.start)}`
        document.getElementById('eventTitle').value = info.event.title
        document.getElementById('modalTrigger').click()
      },
      eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
      },
      allDayText: 'All day'
    })
    calendarCustom.render()
    console.log('Calendar rendered successfully');
  } catch (error) {
    console.error('Error initializing calendar:', error);
  }
  document.getElementById('eventForm').addEventListener('submit', function (e) {
    e.preventDefault()
    const title = document.getElementById('eventTitle').value
    if (title) {
      if (selectedEvent) {
        selectedEvent.setProp('title', title)
      } else {
        calendarCustom.addEvent({
          title: title,
          start: selectedDateInfo.startStr,
          end: selectedDateInfo.endStr,
          allDay: true
        })
      }
      HSOverlay.close('#calendar-event-modal')
    }
  })
})
</script>
@extends('layouts.admin')

@section('title', 'Team Calendar')

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.hr.dashboard') }}">HR</a></li>
            <li class="breadcrumb-item active">Team Calendar</li>
        </ol>
        <div class="d-flex align-items-center gap-2">
            <select id="departmentFilter" class="form-select form-select-sm" style="width: auto;">
                <option value="">All Departments</option>
                @php
                    $departments = \App\Models\Hr\EmployeeHrProfile::active()
                        ->whereNotNull('department')
                        ->distinct()
                        ->pluck('department')
                        ->sort();
                @endphp
                @foreach($departments as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="teamCalendar"></div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-3 flex-wrap">
        <span><span style="display: inline-block; width: 12px; height: 12px; background: #3699ff; border-radius: 2px;"></span> Annual Leave</span>
        <span><span style="display: inline-block; width: 12px; height: 12px; background: #dc3545; border-radius: 2px;"></span> Sickness</span>
        <span><span style="display: inline-block; width: 12px; height: 12px; background: #fd7e14; border-radius: 2px;"></span> Medical</span>
        <span><span style="display: inline-block; width: 12px; height: 12px; background: #28a745; border-radius: 2px;"></span> Birthday</span>
        <span><span style="display: inline-block; width: 12px; height: 12px; background: #ffc107; border-radius: 2px;"></span> Pending</span>
        <span><span style="display: inline-block; width: 12px; height: 12px; background: #17a2b8; border-radius: 2px; opacity: 0.3;"></span> Bank Holiday</span>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<style>
#teamCalendar {
    min-height: 600px;
}
.fc .fc-toolbar-title {
    font-size: 1.2rem;
    font-weight: 600;
}
.fc .fc-event {
    cursor: pointer;
    font-size: 0.75rem;
    border: none;
    padding: 1px 4px;
}
.fc .fc-bg-event {
    opacity: 0.15;
}
.fc .fc-daygrid-day.fc-day-today {
    background: #e8f4fd !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('teamCalendar');
    const departmentFilter = document.getElementById('departmentFilter');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        firstDay: 1,
        height: 'auto',
        eventSources: [{
            url: '{{ route("admin.hr.api.calendar-events") }}',
            extraParams: function() {
                return { department: departmentFilter.value };
            },
            failure: function() {
                alert('Error loading calendar data.');
            }
        }],
        eventClick: function(info) {
            const props = info.event.extendedProps;
            if (props.employee) {
                alert(
                    props.employee + '\n' +
                    'Type: ' + (props.leave_type || 'Leave') + '\n' +
                    'Status: ' + (props.status || '') + '\n' +
                    'Duration: ' + (props.duration || '')
                );
            }
        },
        eventDidMount: function(info) {
            if (info.event.extendedProps.employee) {
                info.el.title = info.event.extendedProps.employee + ' — ' + (info.event.extendedProps.leave_type || 'Leave');
            }
        }
    });

    calendar.render();

    departmentFilter.addEventListener('change', function() {
        calendar.refetchEvents();
    });
});
</script>
@endpush

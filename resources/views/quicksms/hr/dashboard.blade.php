@extends('layouts.quicksms')

@section('title', 'HR Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">HR</li>
        </ol>
    </div>

    @if(!$profile)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                    <h5>HR Profile Not Set Up</h5>
                    <p class="text-muted">You don't have an HR profile yet. Please contact your administrator to set up your leave management profile.</p>
                </div>
            </div>
        </div>
    </div>
    @else

    <!-- Personal Summary Cards -->
    <section class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>My Leave Summary ({{ $year }})</h4>
            <div class="ms-auto">
                <a href="{{ route('hr.my-leave') }}" class="btn btn-sm btn-primary"><i class="fas fa-calendar-alt me-1"></i> My Leave</a>
                @if($isManagerOrAdmin)
                <a href="{{ route('hr.admin') }}" class="btn btn-sm btn-outline-primary ms-1"><i class="fas fa-cog me-1"></i> Admin</a>
                @endif
            </div>
        </div>
        <div class="row">
            <!-- Annual Leave Entitlement -->
            <div class="col-xl-3 col-lg-6 col-sm-6 mb-3">
                <div class="widget-stat card" style="border-left: 4px solid #886CC0;">
                    <div class="card-body p-4">
                        <div class="media ai-icon">
                            <span class="me-3" style="background: rgba(136,108,192,0.12); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-umbrella-beach" style="color: #886CC0; font-size: 24px;"></i>
                            </span>
                            <div class="media-body">
                                <p class="mb-1 text-muted">Annual Leave</p>
                                <h3 class="mb-0 fw-bold">{{ $balance['annual_leave']['remaining_days'] }} <small class="text-muted fs-14">/ {{ $balance['entitlement']['total_days'] }} days</small></h3>
                                <small class="text-muted">{{ $balance['annual_leave']['used_days'] }} used</small>
                                @if($balance['annual_leave']['pending_days'] > 0)
                                    <small class="text-warning ms-1">({{ $balance['annual_leave']['pending_days'] }} pending)</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remaining Balance -->
            <div class="col-xl-3 col-lg-6 col-sm-6 mb-3">
                <div class="widget-stat card" style="border-left: 4px solid #2BC155;">
                    <div class="card-body p-4">
                        <div class="media ai-icon">
                            <span class="me-3" style="background: rgba(43,193,85,0.12); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-check-circle" style="color: #2BC155; font-size: 24px;"></i>
                            </span>
                            <div class="media-body">
                                <p class="mb-1 text-muted">Remaining</p>
                                <h3 class="mb-0 fw-bold {{ $balance['annual_leave']['remaining_days'] <= 2 ? 'text-danger' : '' }}">{{ $balance['annual_leave']['remaining_days'] }} days</h3>
                                @if($balance['entitlement']['is_prorated'])
                                    <small class="text-info"><i class="fas fa-info-circle"></i> Pro-rated</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sickness -->
            <div class="col-xl-3 col-lg-6 col-sm-6 mb-3">
                <div class="widget-stat card" style="border-left: 4px solid #FF6746;">
                    <div class="card-body p-4">
                        <div class="media ai-icon">
                            <span class="me-3" style="background: rgba(255,103,70,0.12); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-thermometer-half" style="color: #FF6746; font-size: 24px;"></i>
                            </span>
                            <div class="media-body">
                                <p class="mb-1 text-muted">Sickness</p>
                                <h3 class="mb-0 fw-bold">{{ $balance['sickness']['total_days'] }} days</h3>
                                <small class="text-muted">This year</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical -->
            <div class="col-xl-3 col-lg-6 col-sm-6 mb-3">
                <div class="widget-stat card" style="border-left: 4px solid #3F9AE0;">
                    <div class="card-body p-4">
                        <div class="media ai-icon">
                            <span class="me-3" style="background: rgba(63,154,224,0.12); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-medkit" style="color: #3F9AE0; font-size: 24px;"></i>
                            </span>
                            <div class="media-body">
                                <p class="mb-1 text-muted">Medical</p>
                                <h3 class="mb-0 fw-bold">{{ $balance['medical']['total_days'] }} days</h3>
                                <small class="text-muted">This year</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row">
        <!-- Pending Requests / Approval Queue (Manager view) -->
        @if($isManagerOrAdmin && $teamPendingRequests->isNotEmpty())
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title"><i class="fas fa-inbox me-2 text-warning"></i>Pending Approvals</h4>
                    <a href="{{ route('hr.admin') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teamPendingRequests as $req)
                                <tr>
                                    <td>{{ $req->employee->full_name }}</td>
                                    <td><span class="badge {{ $req->leave_type === 'annual_leave' ? 'badge-primary' : ($req->leave_type === 'sickness' ? 'badge-warning' : 'badge-info') }} light">{{ $req->leave_type_label }}</span></td>
                                    <td>{{ $req->start_date->format('d M') }} - {{ $req->end_date->format('d M') }}</td>
                                    <td>{{ $req->duration_days_display }} days</td>
                                    <td>
                                        <button class="btn btn-xs btn-success" onclick="approveRequest('{{ $req->id }}')" title="Approve"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-xs btn-danger" onclick="rejectRequest('{{ $req->id }}')" title="Reject"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- My Recent Requests -->
        <div class="{{ $isManagerOrAdmin && $teamPendingRequests->isNotEmpty() ? 'col-xl-6' : 'col-xl-8' }} mb-4">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title"><i class="fas fa-history me-2 text-primary"></i>Recent Leave Requests</h4>
                    <a href="{{ route('hr.my-leave') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($myRecentRequests->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p>No leave requests yet.</p>
                            <a href="{{ route('hr.my-leave') }}" class="btn btn-sm btn-primary">Request Leave</a>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myRecentRequests as $req)
                                <tr>
                                    <td>{{ $req->leave_type_label }}</td>
                                    <td>{{ $req->start_date->format('d M Y') }} @if(!$req->start_date->isSameDay($req->end_date))- {{ $req->end_date->format('d M Y') }}@endif</td>
                                    <td>{{ $req->duration_days_display }} days</td>
                                    <td><span class="badge {{ $req->status_badge_class }} light">{{ ucfirst($req->status) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Absences -->
        <div class="{{ !$isManagerOrAdmin || $teamPendingRequests->isEmpty() ? 'col-xl-4' : 'col-xl-12' }} mb-4">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title"><i class="fas fa-calendar me-2 text-success"></i>Upcoming Absences</h4>
                </div>
                <div class="card-body">
                    @if($upcomingAbsences->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-sun fa-2x mb-2"></i>
                            <p>No upcoming absences.</p>
                        </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($upcomingAbsences as $absence)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <strong>{{ $absence->employee->full_name }}</strong>
                                <br>
                                <small class="text-muted">{{ $absence->start_date->format('d M') }} - {{ $absence->end_date->format('d M Y') }}</small>
                            </div>
                            <span class="badge badge-success light">{{ $absence->duration_days_display }}d</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]');

function approveRequest(id) {
    if (!confirm('Approve this leave request?')) return;
    fetch('/hr/admin/requests/' + id + '/approve', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            toastr.success(data.message);
            setTimeout(function() { location.reload(); }, 800);
        } else {
            toastr.error(data.message || 'Error approving request');
        }
    })
    .catch(function() { toastr.error('Network error'); });
}

function rejectRequest(id) {
    var reason = prompt('Rejection reason (optional):');
    if (reason === null) return;
    fetch('/hr/admin/requests/' + id + '/reject', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            toastr.success(data.message);
            setTimeout(function() { location.reload(); }, 800);
        } else {
            toastr.error(data.message || 'Error rejecting request');
        }
    })
    .catch(function() { toastr.error('Network error'); });
}
</script>
@endpush

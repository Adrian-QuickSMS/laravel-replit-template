@extends('layouts.admin')

@section('title', 'HR Dashboard')

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">HR Dashboard</li>
        </ol>
        <div class="d-flex align-items-center gap-2">
            <select id="yearSelector" class="form-select form-select-sm" style="width: auto;" onchange="window.location='?year='+this.value">
                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    @if(!$profile)
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            You don't have an HR profile yet. Please contact your HR administrator to set one up.
        </div>
    @else
        <div class="row">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fs-32 font-w600 mb-0">{{ $balance ? number_format($balance['annual_leave']['remaining_days'], 1) : '0' }}</h2>
                            <span class="fs-14 text-muted">Days Remaining</span>
                        </div>
                        <div class="rounded-circle p-3" style="background: rgba(54, 153, 255, 0.1);">
                            <i class="fas fa-calendar-check text-primary fs-24"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fs-32 font-w600 mb-0">{{ $balance ? number_format($balance['annual_leave']['used_days'], 1) : '0' }}</h2>
                            <span class="fs-14 text-muted">Days Used</span>
                        </div>
                        <div class="rounded-circle p-3" style="background: rgba(40, 167, 69, 0.1);">
                            <i class="fas fa-calendar-minus text-success fs-24"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fs-32 font-w600 mb-0">{{ $balance ? number_format($balance['annual_leave']['pending_days'], 1) : '0' }}</h2>
                            <span class="fs-14 text-muted">Days Pending</span>
                        </div>
                        <div class="rounded-circle p-3" style="background: rgba(255, 193, 7, 0.1);">
                            <i class="fas fa-clock text-warning fs-24"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fs-32 font-w600 mb-0">{{ $balance ? number_format($balance['sickness']['total_days'], 1) : '0' }}</h2>
                            <span class="fs-14 text-muted">Sick Days</span>
                        </div>
                        <div class="rounded-circle p-3" style="background: rgba(220, 53, 69, 0.1);">
                            <i class="fas fa-thermometer-half text-danger fs-24"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($balance)
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3">Annual Leave Balance — {{ $year }}</h6>
                        @php
                            $totalDays = $balance['entitlement']['total_days'];
                            $usedPct = $totalDays > 0 ? ($balance['annual_leave']['used_days'] / $totalDays) * 100 : 0;
                            $pendingPct = $totalDays > 0 ? ($balance['annual_leave']['pending_days'] / $totalDays) * 100 : 0;
                            $remainingPct = max(0, 100 - $usedPct - $pendingPct);
                        @endphp
                        <div class="progress mb-2" style="height: 24px;">
                            <div class="progress-bar bg-success" style="width: {{ $usedPct }}%" title="Used: {{ number_format($balance['annual_leave']['used_days'], 1) }} days">{{ number_format($balance['annual_leave']['used_days'], 1) }}d</div>
                            <div class="progress-bar bg-warning" style="width: {{ $pendingPct }}%" title="Pending: {{ number_format($balance['annual_leave']['pending_days'], 1) }} days">{{ number_format($balance['annual_leave']['pending_days'], 1) }}d</div>
                        </div>
                        <small class="text-muted">
                            Total entitlement: {{ number_format($totalDays, 1) }} days
                            @if($balance['entitlement']['is_prorated']) (prorated) @endif
                            @if($balance['birthday']['eligible'] && !$balance['birthday']['used'])
                                | <i class="fas fa-birthday-cake text-info"></i> Birthday day available
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif

    @if($isManagerOrAdmin && $teamPendingRequests->count() > 0)
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Pending Approval</h4>
                    <span class="badge bg-warning">{{ $teamPendingRequests->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Duration</th>
                                    <th>Note</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teamPendingRequests as $req)
                                <tr id="pending-row-{{ $req->id }}">
                                    <td>{{ $req->employee?->full_name ?? 'Unknown' }}</td>
                                    <td><span class="badge {{ $req->leave_type === 'sickness' ? 'bg-danger' : ($req->leave_type === 'medical' ? 'bg-info' : 'bg-primary') }} badge-sm">{{ $req->leave_type_label }}</span></td>
                                    <td>{{ $req->start_date->format('d M') }}{{ $req->start_date->ne($req->end_date) ? ' – ' . $req->end_date->format('d M') : '' }}</td>
                                    <td>{{ number_format($req->duration_days_display, 1) }}d</td>
                                    <td>{{ Str::limit($req->employee_note, 40) }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-success btn-xs" onclick="hrApproveRequest('{{ $req->id }}')"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-danger btn-xs" onclick="hrRejectRequest('{{ $req->id }}')"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Upcoming Absences</h4>
                </div>
                <div class="card-body">
                    @if($upcomingAbsences->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Who</th><th>Type</th><th>When</th><th>Duration</th></tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingAbsences as $abs)
                                <tr>
                                    <td>{{ $abs->employee?->full_name ?? 'Unknown' }}</td>
                                    <td><span class="badge {{ $abs->leave_type === 'sickness' ? 'bg-danger' : ($abs->leave_type === 'birthday' ? 'bg-info' : 'bg-primary') }} badge-sm">{{ $abs->leave_type_label }}</span></td>
                                    <td>{{ $abs->start_date->format('d M') }}{{ $abs->start_date->ne($abs->end_date) ? ' – ' . $abs->end_date->format('d M') : '' }}</td>
                                    <td>{{ number_format($abs->duration_days_display, 1) }}d</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">No upcoming absences.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">My Recent Requests</h4>
                </div>
                <div class="card-body">
                    @if($profile && $myRecentRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Type</th><th>When</th><th>Duration</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($myRecentRequests as $req)
                                <tr>
                                    <td>{{ $req->leave_type_label }}</td>
                                    <td>{{ $req->start_date->format('d M') }}{{ $req->start_date->ne($req->end_date) ? ' – ' . $req->end_date->format('d M') : '' }}</td>
                                    <td>{{ number_format($req->duration_days_display, 1) }}d</td>
                                    <td><span class="badge {{ $req->status_badge_class }} badge-sm">{{ ucfirst($req->status) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">No recent requests.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function hrApproveRequest(id) {
    if (!confirm('Approve this leave request?')) return;
    fetch(`/admin/hr/request/${id}/approve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            document.getElementById('pending-row-' + id)?.remove();
            alert('Approved.');
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}

function hrRejectRequest(id) {
    const comment = prompt('Reason for rejection:');
    if (!comment) return;
    fetch(`/admin/hr/request/${id}/reject`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ comment }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            document.getElementById('pending-row-' + id)?.remove();
            alert('Rejected.');
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}
</script>
@endpush

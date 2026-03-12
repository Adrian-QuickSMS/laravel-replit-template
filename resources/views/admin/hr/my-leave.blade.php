@extends('layouts.admin')

@section('title', 'My Leave')

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.hr.dashboard') }}">HR</a></li>
            <li class="breadcrumb-item active">My Leave</li>
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
            You don't have an HR profile yet. Please contact your HR administrator.
        </div>
    @else
        @if($balance)
        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Leave Balance — {{ $year }}</h4>
                    </div>
                    <div class="card-body">
                        @php
                            $totalDays = $balance['entitlement']['total_days'];
                            $usedPct = $totalDays > 0 ? ($balance['annual_leave']['used_days'] / $totalDays) * 100 : 0;
                            $pendingPct = $totalDays > 0 ? ($balance['annual_leave']['pending_days'] / $totalDays) * 100 : 0;
                        @endphp
                        <div class="progress mb-3" style="height: 30px;">
                            <div class="progress-bar bg-success" style="width: {{ $usedPct }}%">Used: {{ number_format($balance['annual_leave']['used_days'], 1) }}d</div>
                            <div class="progress-bar bg-warning" style="width: {{ $pendingPct }}%">Pending: {{ number_format($balance['annual_leave']['pending_days'], 1) }}d</div>
                        </div>
                        <div class="row text-center">
                            <div class="col-3">
                                <h5 class="mb-0">{{ number_format($totalDays, 1) }}</h5>
                                <small class="text-muted">Total Entitlement</small>
                            </div>
                            <div class="col-3">
                                <h5 class="mb-0 text-success">{{ number_format($balance['annual_leave']['used_days'], 1) }}</h5>
                                <small class="text-muted">Used</small>
                            </div>
                            <div class="col-3">
                                <h5 class="mb-0 text-warning">{{ number_format($balance['annual_leave']['pending_days'], 1) }}</h5>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-3">
                                <h5 class="mb-0 text-primary">{{ number_format($balance['annual_leave']['remaining_days'], 1) }}</h5>
                                <small class="text-muted">Remaining</small>
                            </div>
                        </div>
                        @if($balance['sickness']['total_days'] > 0 || $balance['medical']['total_days'] > 0)
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <span class="badge bg-danger mb-1">Sickness</span>
                                <h6>{{ number_format($balance['sickness']['total_days'], 1) }} days</h6>
                            </div>
                            <div class="col-6">
                                <span class="badge bg-info mb-1">Medical</span>
                                <h6>{{ number_format($balance['medical']['total_days'], 1) }} days</h6>
                            </div>
                        </div>
                        @endif
                        @if($balance['birthday']['eligible'] && !$balance['birthday']['used'])
                        <hr>
                        <div class="d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-birthday-cake text-info me-2"></i> Birthday day off available</span>
                            <button class="btn btn-info btn-sm" onclick="submitBirthdayLeave()">Book Birthday Leave</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">Request Leave</h4>
                    </div>
                    <div class="card-body">
                        <form id="leaveRequestForm" onsubmit="submitLeaveRequest(event)">
                            <div class="mb-3">
                                <label class="form-label">Leave Type</label>
                                <select name="leave_type" class="form-select form-select-sm" required>
                                    <option value="annual_leave">Annual Leave</option>
                                    <option value="sickness">Sickness</option>
                                    <option value="medical">Medical Appointment</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Day Portion</label>
                                <select name="day_portion" class="form-select form-select-sm">
                                    <option value="full">Full Day</option>
                                    <option value="half_am">Morning Half</option>
                                    <option value="half_pm">Afternoon Half</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Note (optional)</label>
                                <textarea name="note" class="form-control form-control-sm" rows="2" maxlength="1000"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100" id="submitBtn">Submit Request</button>
                        </form>
                        <div id="requestMessage" class="mt-2" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title">My Requests — {{ $year }}</h4>
                    </div>
                    <div class="card-body">
                        @if($requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Dates</th>
                                        <th>Duration</th>
                                        <th>Portion</th>
                                        <th>Status</th>
                                        <th>Note</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $req)
                                    <tr id="req-row-{{ $req->id }}">
                                        <td><span class="badge {{ $req->leave_type === 'sickness' ? 'bg-danger' : ($req->leave_type === 'birthday' ? 'bg-info' : ($req->leave_type === 'medical' ? 'bg-secondary' : 'bg-primary')) }} badge-sm">{{ $req->leave_type_label }}</span></td>
                                        <td>{{ $req->start_date->format('d M Y') }}{{ $req->start_date->ne($req->end_date) ? ' – ' . $req->end_date->format('d M Y') : '' }}</td>
                                        <td>{{ number_format($req->duration_days_display, 1) }}d</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $req->day_portion)) }}</td>
                                        <td><span class="badge {{ $req->status_badge_class }} badge-sm">{{ ucfirst($req->status) }}</span></td>
                                        <td>{{ Str::limit($req->employee_note, 30) }}</td>
                                        <td class="text-end">
                                            @if(in_array($req->status, ['pending', 'approved']))
                                            <button class="btn btn-outline-danger btn-xs" onclick="cancelRequest('{{ $req->id }}')">Cancel</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted mb-0">No leave requests for {{ $year }}.</p>
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
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function submitLeaveRequest(e) {
    e.preventDefault();
    const form = document.getElementById('leaveRequestForm');
    const btn = document.getElementById('submitBtn');
    const msg = document.getElementById('requestMessage');
    const data = Object.fromEntries(new FormData(form));

    btn.disabled = true;
    btn.textContent = 'Submitting...';

    fetch('{{ route("admin.hr.leave-request.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        msg.style.display = 'block';
        if (d.status === 'success') {
            msg.innerHTML = '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>';
            form.reset();
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.innerHTML = '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error submitting request.') + '</div>';
        }
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.innerHTML = '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Submit Request';
    });
}

function cancelRequest(id) {
    if (!confirm('Cancel this leave request?')) return;
    fetch(`/admin/hr/leave-request/${id}/cancel`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            location.reload();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}

function submitBirthdayLeave() {
    if (!confirm('Book your birthday as a day off?')) return;
    fetch('{{ route("admin.hr.leave-request.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
            leave_type: 'birthday',
            start_date: new Date().toISOString().split('T')[0],
            end_date: new Date().toISOString().split('T')[0],
            day_portion: 'full',
            note: 'Birthday leave',
        }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            location.reload();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}
</script>
@endpush

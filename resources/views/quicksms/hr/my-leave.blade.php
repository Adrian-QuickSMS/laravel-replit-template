@extends('layouts.quicksms')

@section('title', 'My Leave')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
            <li class="breadcrumb-item active">My Leave</li>
        </ol>
    </div>

    @if(!$profile)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                    <h5>HR Profile Not Set Up</h5>
                    <p class="text-muted">You don't have an HR profile yet. Please contact your administrator.</p>
                </div>
            </div>
        </div>
    </div>
    @else

    <!-- Balance Cards -->
    <section class="mb-4">
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-sm-6 mb-3">
                <div class="widget-stat card" style="border-left: 4px solid #886CC0;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1">Entitlement</h6>
                        <h3 class="mb-0 fw-bold">{{ $balance['entitlement']['total_days'] }} <small class="fs-14 text-muted">days</small></h3>
                        @if($balance['entitlement']['is_prorated'])
                        <small class="text-info"><i class="fas fa-info-circle"></i> Pro-rated</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-sm-6 mb-3">
                <div class="widget-stat card" style="border-left: 4px solid #2BC155;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1">Remaining</h6>
                        <h3 class="mb-0 fw-bold {{ $balance['annual_leave']['remaining_days'] <= 2 ? 'text-danger' : 'text-success' }}">{{ $balance['annual_leave']['remaining_days'] }} <small class="fs-14 text-muted">days</small></h3>
                        <small class="text-muted">{{ $balance['annual_leave']['used_days'] }} used</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-sm-6 mb-3">
                <div class="widget-stat card" style="border-left: 4px solid #FF6746;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1">Sickness</h6>
                        <h3 class="mb-0 fw-bold">{{ $balance['sickness']['total_days'] }} <small class="fs-14 text-muted">days</small></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-sm-6 mb-3">
                <div class="widget-stat card" style="border-left: 4px solid #3F9AE0;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1">Medical</h6>
                        <h3 class="mb-0 fw-bold">{{ $balance['medical']['total_days'] }} <small class="fs-14 text-muted">days</small></h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row">
        <!-- Request Form -->
        <div class="col-xl-5 mb-4">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Request Leave</h4>
                </div>
                <div class="card-body">
                    <form id="leaveRequestForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type" class="form-select" required>
                                <option value="">Select type...</option>
                                <option value="annual_leave">Annual Leave</option>
                                <option value="sickness">Sickness</option>
                                <option value="medical">Medical</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Day Portion</label>
                            <select name="day_portion" class="form-select">
                                <option value="full">Full Day</option>
                                <option value="half_am">Half Day (AM)</option>
                                <option value="half_pm">Half Day (PM)</option>
                                <option value="quarter">Quarter Day</option>
                            </select>
                            <small class="text-muted">Partial days only apply to single-day requests</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Note</label>
                            <textarea name="note" class="form-control" rows="2" maxlength="1000" placeholder="Optional note..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="submitLeaveBtn">
                            <i class="fas fa-paper-plane me-1"></i> Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Leave History -->
        <div class="col-xl-7 mb-4">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title"><i class="fas fa-list me-2 text-primary"></i>Leave History ({{ $year }})</h4>
                </div>
                <div class="card-body">
                    @if($requests->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p>No leave requests for {{ $year }}.</p>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Duration</th>
                                    <th>Portion</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $req)
                                <tr>
                                    <td>
                                        <span class="badge {{ $req->leave_type === 'annual_leave' ? 'badge-primary' : ($req->leave_type === 'sickness' ? 'badge-warning' : 'badge-info') }} light">
                                            {{ $req->leave_type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $req->start_date->format('d M Y') }}
                                        @if(!$req->start_date->isSameDay($req->end_date))
                                        <br><small class="text-muted">to {{ $req->end_date->format('d M Y') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $req->duration_days_display }} days</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $req->day_portion)) }}</td>
                                    <td><span class="badge {{ $req->status_badge_class }} light">{{ ucfirst($req->status) }}</span></td>
                                    <td>
                                        @if($req->isPending() || ($req->isApproved() && $req->start_date->gte(today())))
                                        <button class="btn btn-xs btn-outline-danger" onclick="cancelRequest('{{ $req->id }}')" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                        @if($req->isApproved())
                                        <a href="{{ route('hr.admin.requests.ics', $req->id) }}" class="btn btn-xs btn-outline-primary" title="Download ICS">
                                            <i class="fas fa-calendar-plus"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @if($req->employee_note)
                                <tr>
                                    <td colspan="6" class="pt-0 border-0"><small class="text-muted"><i class="fas fa-sticky-note me-1"></i>{{ $req->employee_note }}</small></td>
                                </tr>
                                @endif
                                @if($req->approval_comment)
                                <tr>
                                    <td colspan="6" class="pt-0 border-0"><small class="text-{{ $req->status === 'rejected' ? 'danger' : 'success' }}"><i class="fas fa-comment me-1"></i>{{ $req->approval_comment }}</small></td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
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

document.getElementById('leaveRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('submitLeaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';

    var formData = new FormData(this);
    var body = {};
    formData.forEach(function(value, key) { body[key] = value; });

    fetch('{{ route("hr.my-leave.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(body)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            toastr.success(data.message);
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            toastr.error(data.message || 'Error submitting request');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Submit Request';
        }
    })
    .catch(function() {
        toastr.error('Network error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Submit Request';
    });
});

// Sync end_date with start_date when start changes
document.querySelector('input[name="start_date"]').addEventListener('change', function() {
    var endInput = document.querySelector('input[name="end_date"]');
    if (!endInput.value || endInput.value < this.value) {
        endInput.value = this.value;
    }
    endInput.min = this.value;
});

// Disable partial day options when multi-day range
function checkPartialDayAvailability() {
    var start = document.querySelector('input[name="start_date"]').value;
    var end = document.querySelector('input[name="end_date"]').value;
    var portionSelect = document.querySelector('select[name="day_portion"]');

    if (start && end && start !== end) {
        portionSelect.value = 'full';
        portionSelect.disabled = true;
    } else {
        portionSelect.disabled = false;
    }
}

document.querySelector('input[name="start_date"]').addEventListener('change', checkPartialDayAvailability);
document.querySelector('input[name="end_date"]').addEventListener('change', checkPartialDayAvailability);

function cancelRequest(id) {
    if (!confirm('Are you sure you want to cancel this leave request?')) return;
    fetch('/hr/my-leave/' + id + '/cancel', {
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
            toastr.error(data.message || 'Error cancelling request');
        }
    })
    .catch(function() { toastr.error('Network error'); });
}
</script>
@endpush

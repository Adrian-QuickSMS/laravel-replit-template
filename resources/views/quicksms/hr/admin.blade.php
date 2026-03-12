@extends('layouts.quicksms')

@section('title', 'HR Admin')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
            <li class="breadcrumb-item active">Admin</li>
        </ol>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="hrAdminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="approvals-tab" data-bs-toggle="tab" data-bs-target="#approvals" type="button" role="tab">
                <i class="fas fa-inbox me-1"></i> Approval Queue
                @if($pendingRequests->count() > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $pendingRequests->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees" type="button" role="tab">
                <i class="fas fa-users me-1"></i> Employees
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="add-employee-tab" data-bs-toggle="tab" data-bs-target="#add-employee" type="button" role="tab">
                <i class="fas fa-user-plus me-1"></i> Add Employee
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">
                <i class="fas fa-cog me-1"></i> Settings
            </button>
        </li>
    </ul>

    <div class="tab-content" id="hrAdminTabContent">
        <!-- Approval Queue Tab -->
        <div class="tab-pane fade show active" id="approvals" role="tabpanel">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Pending Leave Requests</h4>
                </div>
                <div class="card-body">
                    @if($pendingRequests->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <h5>All caught up!</h5>
                            <p>No pending leave requests to review.</p>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Duration</th>
                                    <th>Portion</th>
                                    <th>Submitted</th>
                                    <th>Note</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $req)
                                <tr id="request-row-{{ $req->id }}">
                                    <td>
                                        <strong>{{ $req->employee->full_name }}</strong>
                                        @if($req->employee->department)
                                        <br><small class="text-muted">{{ $req->employee->department }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $req->leave_type === 'annual_leave' ? 'badge-primary' : ($req->leave_type === 'sickness' ? 'badge-warning' : 'badge-info') }} light">
                                            {{ $req->leave_type_label }}
                                        </span>
                                    </td>
                                    <td>{{ $req->start_date->format('d M Y') }}</td>
                                    <td>{{ $req->end_date->format('d M Y') }}</td>
                                    <td><strong>{{ $req->duration_days_display }}</strong> days</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $req->day_portion)) }}</td>
                                    <td>{{ $req->submitted_at->format('d M H:i') }}</td>
                                    <td>
                                        @if($req->employee_note)
                                        <span title="{{ $req->employee_note }}"><i class="fas fa-sticky-note text-muted"></i></span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-success" onclick="approveRequest('{{ $req->id }}')" title="Approve">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectRequest('{{ $req->id }}')" title="Reject">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Employees Tab -->
        <div class="tab-pane fade" id="employees" role="tabpanel">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Employee Leave Overview ({{ $year }})</h4>
                </div>
                <div class="card-body">
                    @if($employees->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <p>No employees set up yet. Use the "Add Employee" tab to add team members.</p>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Start Date</th>
                                    <th>Role</th>
                                    <th>Entitlement</th>
                                    <th>Used</th>
                                    <th>Remaining</th>
                                    <th>Sickness</th>
                                    <th>Medical</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $emp)
                                <tr>
                                    <td><strong>{{ $emp->full_name }}</strong></td>
                                    <td>{{ $emp->department ?? '-' }}</td>
                                    <td>{{ $emp->start_date->format('d M Y') }}</td>
                                    <td><span class="badge badge-light">{{ ucfirst(str_replace('_', ' ', $emp->hr_role)) }}</span></td>
                                    <td>{{ $emp->balance['entitlement']['total_days'] }}
                                        @if($emp->balance['entitlement']['is_prorated'])
                                        <i class="fas fa-info-circle text-info" title="Pro-rated"></i>
                                        @endif
                                    </td>
                                    <td>{{ $emp->balance['annual_leave']['used_days'] }}</td>
                                    <td class="{{ $emp->balance['annual_leave']['remaining_days'] <= 2 ? 'text-danger fw-bold' : '' }}">
                                        {{ $emp->balance['annual_leave']['remaining_days'] }}
                                    </td>
                                    <td>{{ $emp->balance['sickness']['total_days'] }}</td>
                                    <td>{{ $emp->balance['medical']['total_days'] }}</td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-primary" onclick="editEntitlement('{{ $emp->id }}', '{{ $emp->full_name }}', {{ $emp->balance['entitlement']['total_days'] }})" title="Edit Entitlement">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Add Employee Tab -->
        <div class="tab-pane fade" id="add-employee" role="tabpanel">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Add / Edit Employee HR Profile</h4>
                </div>
                <div class="card-body">
                    <form id="addEmployeeForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">User Account <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required id="userSelect">
                                    <option value="">Select a user...</option>
                                </select>
                                <small class="text-muted">Select from existing QuickSMS users</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Department</label>
                                <input type="text" name="department" class="form-control" placeholder="e.g. Engineering">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Job Title</label>
                                <input type="text" name="job_title" class="form-control" placeholder="e.g. Software Developer">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">HR Role <span class="text-danger">*</span></label>
                                <select name="hr_role" class="form-select" required>
                                    <option value="employee">Employee</option>
                                    <option value="manager">Manager</option>
                                    <option value="hr_admin">HR Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Annual Leave Entitlement (days)</label>
                                <input type="number" name="annual_entitlement_days" class="form-control" step="0.25" min="0" max="365" placeholder="Leave blank for default ({{ $settings->default_entitlement_days }} days)">
                                <small class="text-muted">Default: {{ $settings->default_entitlement_days }} days. Will be pro-rated if start date is mid-year.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Manager</label>
                                <select name="manager_id" class="form-select" id="managerSelect">
                                    <option value="">None</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="addEmployeeBtn">
                            <i class="fas fa-save me-1"></i> Save Employee Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade" id="settings" role="tabpanel">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Company HR Settings</h4>
                </div>
                <div class="card-body">
                    <form id="hrSettingsForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Default Annual Entitlement (days)</label>
                                <input type="number" name="default_annual_entitlement_days" class="form-control" step="0.25" min="0" max="365" value="{{ $settings->default_entitlement_days }}">
                                <small class="text-muted">This is the base annual leave for new employees before pro-rating.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Team Notification Email</label>
                                <input type="email" name="team_notification_email" class="form-control" value="{{ $settings->team_notification_email }}" placeholder="team@company.com">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="email_notifications_enabled" id="emailNotif" {{ $settings->email_notifications_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="emailNotif">Email Notifications</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ics_generation_enabled" id="icsGen" {{ $settings->ics_generation_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="icsGen">ICS Calendar Files</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_leave_type_in_notifications" id="showType" {{ $settings->show_leave_type_in_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label" for="showType">Show Leave Type in Notifications</label>
                                </div>
                                <small class="text-muted">Privacy: If disabled, notifications just say "On Leave"</small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveSettingsBtn">
                            <i class="fas fa-save me-1"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Entitlement Edit Modal -->
<div class="modal fade" id="entitlementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Entitlement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Employee: <strong id="entitlementEmployeeName"></strong></p>
                <form id="entitlementForm">
                    <input type="hidden" name="employee_id" id="entitlementEmployeeId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Year</label>
                        <input type="number" name="year" class="form-control" value="{{ $year }}" min="2020" max="2100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Entitlement (days)</label>
                        <input type="number" name="entitlement_days" class="form-control" step="0.25" min="0" max="365" id="entitlementDays">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note</label>
                        <input type="text" name="note" class="form-control" placeholder="Reason for change...">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEntitlement()">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]');
var csrfValue = csrfToken ? csrfToken.content : '';

// Load users for the add employee form
(function loadUsers() {
    fetch('/api/users', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfValue }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var users = data.data || data;
        var select = document.getElementById('userSelect');
        if (Array.isArray(users)) {
            users.forEach(function(u) {
                var opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = (u.first_name || '') + ' ' + (u.last_name || '') + ' (' + (u.email || '') + ')';
                select.appendChild(opt);
            });
        }
    })
    .catch(function() {});

    // Load managers
    fetch('/hr/admin/employees', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfValue }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var employees = data.data || [];
        var select = document.getElementById('managerSelect');
        employees.forEach(function(e) {
            if (e.hr_role === 'manager' || e.hr_role === 'hr_admin') {
                var opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = e.name;
                select.appendChild(opt);
            }
        });
    })
    .catch(function() {});
})();

// Add Employee form
document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('addEmployeeBtn');
    btn.disabled = true;

    var formData = new FormData(this);
    var body = {};
    formData.forEach(function(v, k) { if (v !== '') body[k] = v; });

    fetch('{{ route("hr.admin.employees.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfValue,
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
            toastr.error(data.message || 'Error saving profile');
            btn.disabled = false;
        }
    })
    .catch(function() { toastr.error('Network error'); btn.disabled = false; });
});

// Settings form
document.getElementById('hrSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('saveSettingsBtn');
    btn.disabled = true;

    var body = {
        default_annual_entitlement_days: parseFloat(this.default_annual_entitlement_days.value),
        email_notifications_enabled: this.email_notifications_enabled.checked,
        ics_generation_enabled: this.ics_generation_enabled.checked,
        team_notification_email: this.team_notification_email.value || null,
        show_leave_type_in_notifications: this.show_leave_type_in_notifications.checked
    };

    fetch('{{ route("hr.admin.settings.update") }}', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': csrfValue,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(body)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            toastr.success(data.message);
        } else {
            toastr.error(data.message || 'Error saving settings');
        }
        btn.disabled = false;
    })
    .catch(function() { toastr.error('Network error'); btn.disabled = false; });
});

// Approve/Reject
function approveRequest(id) {
    var comment = prompt('Approval comment (optional):');
    if (comment === null) return;

    fetch('/hr/admin/requests/' + id + '/approve', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfValue, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ comment: comment })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            toastr.success(data.message);
            var row = document.getElementById('request-row-' + id);
            if (row) row.style.display = 'none';
        } else {
            toastr.error(data.message);
        }
    })
    .catch(function() { toastr.error('Network error'); });
}

function rejectRequest(id) {
    var reason = prompt('Rejection reason:');
    if (reason === null) return;

    fetch('/hr/admin/requests/' + id + '/reject', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfValue, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ reason: reason })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            toastr.success(data.message);
            var row = document.getElementById('request-row-' + id);
            if (row) row.style.display = 'none';
        } else {
            toastr.error(data.message);
        }
    })
    .catch(function() { toastr.error('Network error'); });
}

// Entitlement Modal
function editEntitlement(empId, name, currentDays) {
    document.getElementById('entitlementEmployeeId').value = empId;
    document.getElementById('entitlementEmployeeName').textContent = name;
    document.getElementById('entitlementDays').value = currentDays;
    new bootstrap.Modal(document.getElementById('entitlementModal')).show();
}

function saveEntitlement() {
    var form = document.getElementById('entitlementForm');
    var empId = form.employee_id.value;
    var body = {
        year: parseInt(form.year.value),
        entitlement_days: parseFloat(form.entitlement_days.value),
        note: form.note.value || null
    };

    fetch('/hr/admin/employees/' + empId + '/entitlement', {
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': csrfValue, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            toastr.success(data.message);
            bootstrap.Modal.getInstance(document.getElementById('entitlementModal')).hide();
            setTimeout(function() { location.reload(); }, 800);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(function() { toastr.error('Network error'); });
}
</script>
@endpush

@extends('layouts.admin')

@section('title', 'HR Settings')

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.hr.dashboard') }}">HR</a></li>
            <li class="breadcrumb-item active">Settings</li>
        </ol>
        <div class="d-flex align-items-center gap-2">
            <select id="yearSelector" class="form-select form-select-sm" style="width: auto;" onchange="window.location='?year='+this.value">
                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-employees">Employees</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-pending">Pending Requests <span class="badge bg-warning">{{ $pendingRequests->count() }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-settings">Settings</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-holidays">Bank Holidays</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-audit">Audit Log</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-employees">
            <div class="card">
                <div class="card-header border-0 pb-0 d-flex justify-content-between">
                    <h4 class="card-title">Employee Profiles</h4>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal"><i class="fas fa-plus me-1"></i>Add Employee</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Job Title</th>
                                    <th>HR Role</th>
                                    <th>Start Date</th>
                                    <th>Entitlement</th>
                                    <th>Used</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $emp)
                                <tr>
                                    <td>{{ $emp->full_name }}</td>
                                    <td>{{ $emp->adminUser?->email }}</td>
                                    <td>{{ $emp->department ?? '—' }}</td>
                                    <td>{{ $emp->job_title ?? '—' }}</td>
                                    <td><span class="badge {{ $emp->hr_role === 'hr_admin' ? 'bg-danger' : ($emp->hr_role === 'manager' ? 'bg-warning' : 'bg-secondary') }} badge-sm">{{ ucfirst(str_replace('_', ' ', $emp->hr_role)) }}</span></td>
                                    <td>{{ $emp->start_date->format('d M Y') }}</td>
                                    <td>{{ $emp->balance ? number_format($emp->balance['entitlement']['total_days'], 1) : '—' }}d</td>
                                    <td>{{ $emp->balance ? number_format($emp->balance['annual_leave']['used_days'], 1) : '—' }}d</td>
                                    <td>{{ $emp->balance ? number_format($emp->balance['annual_leave']['remaining_days'], 1) : '—' }}d</td>
                                    <td><span class="badge {{ $emp->is_active ? 'bg-success' : 'bg-secondary' }} badge-sm">{{ $emp->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-pending">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Pending Approval</h4>
                </div>
                <div class="card-body">
                    @if($pendingRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Employee</th><th>Type</th><th>Dates</th><th>Duration</th><th>Note</th><th>Submitted</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $req)
                                <tr id="settings-pending-{{ $req->id }}">
                                    <td>{{ $req->employee?->full_name ?? 'Unknown' }}</td>
                                    <td><span class="badge bg-primary badge-sm">{{ $req->leave_type_label }}</span></td>
                                    <td>{{ $req->start_date->format('d M') }}–{{ $req->end_date->format('d M') }}</td>
                                    <td>{{ number_format($req->duration_days_display, 1) }}d</td>
                                    <td>{{ Str::limit($req->employee_note, 30) }}</td>
                                    <td>{{ $req->submitted_at->format('d M H:i') }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-success btn-xs" onclick="settingsApprove('{{ $req->id }}')"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-danger btn-xs" onclick="settingsReject('{{ $req->id }}')"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">No pending requests.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-settings">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Company HR Settings</h4>
                </div>
                <div class="card-body">
                    <form id="hrSettingsForm" onsubmit="saveSettings(event)">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Default Annual Entitlement (days)</label>
                                <input type="number" name="default_annual_entitlement_days" class="form-control form-control-sm" value="{{ $settings->default_entitlement_days }}" step="0.25" min="0" max="365">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Team Notification Email</label>
                                <input type="email" name="team_notification_email" class="form-control form-control-sm" value="{{ $settings->team_notification_email }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="birthday_leave_enabled" value="1" {{ $settings->birthday_leave_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label">Birthday Leave Enabled</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="email_notifications_enabled" value="1" {{ $settings->email_notifications_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label">Email Notifications</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_leave_type_in_notifications" value="1" {{ $settings->show_leave_type_in_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label">Show Leave Type in Notifications</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                    </form>
                    <div id="settingsMessage" class="mt-2" style="display: none;"></div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-holidays">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Bank Holidays</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Date</th><th>Name</th><th>Region</th><th>Year</th></tr>
                            </thead>
                            <tbody>
                                @foreach($bankHolidays as $hol)
                                <tr>
                                    <td>{{ $hol->holiday_date->format('D d M Y') }}</td>
                                    <td>{{ $hol->name }}</td>
                                    <td>{{ ucfirst(str_replace('-', ' & ', $hol->region)) }}</td>
                                    <td>{{ $hol->year }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-audit">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">HR Audit Log</h4>
                </div>
                <div class="card-body">
                    @if($auditLog->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>When</th><th>Action</th><th>Old Value</th><th>New Value</th><th>Note</th></tr>
                            </thead>
                            <tbody>
                                @foreach($auditLog as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                    <td><span class="badge bg-secondary badge-sm">{{ str_replace('_', ' ', $log->action) }}</span></td>
                                    <td><small>{{ Str::limit($log->old_value, 50) }}</small></td>
                                    <td><small>{{ Str::limit($log->new_value, 50) }}</small></td>
                                    <td><small>{{ Str::limit($log->note, 40) }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">No audit log entries yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Employee HR Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" onsubmit="addEmployee(event)">
                    <div class="mb-3">
                        <label class="form-label">Admin User</label>
                        <select name="admin_user_id" class="form-select form-select-sm" required>
                            <option value="">Select user...</option>
                            @foreach($adminUsers as $au)
                                <option value="{{ $au->id }}">{{ $au->full_name }} ({{ $au->email }})</option>
                            @endforeach
                        </select>
                        @if($adminUsers->count() === 0)
                            <small class="text-muted">All admin users already have HR profiles.</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control form-control-sm" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="job_title" class="form-control form-control-sm" maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">HR Role</label>
                        <select name="hr_role" class="form-select form-select-sm" required>
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
                            <option value="hr_admin">HR Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Birthday</label>
                        <input type="date" name="birthday" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Annual Entitlement (days, blank for default)</label>
                        <input type="number" name="annual_entitlement_days" class="form-control form-control-sm" step="0.25" min="0" max="365">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Create Profile</button>
                </form>
                <div id="addEmployeeMessage" class="mt-2" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function addEmployee(e) {
    e.preventDefault();
    const form = document.getElementById('addEmployeeForm');
    const msg = document.getElementById('addEmployeeMessage');
    const data = Object.fromEntries(new FormData(form));

    fetch('{{ route("admin.hr.employee.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        msg.style.display = 'block';
        if (d.status === 'success') {
            msg.innerHTML = '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.innerHTML = '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error') + '</div>';
        }
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.innerHTML = '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>';
    });
}

function saveSettings(e) {
    e.preventDefault();
    const form = document.getElementById('hrSettingsForm');
    const msg = document.getElementById('settingsMessage');
    const data = Object.fromEntries(new FormData(form));
    data.birthday_leave_enabled = form.querySelector('[name=birthday_leave_enabled]').checked ? 1 : 0;
    data.email_notifications_enabled = form.querySelector('[name=email_notifications_enabled]').checked ? 1 : 0;
    data.show_leave_type_in_notifications = form.querySelector('[name=show_leave_type_in_notifications]').checked ? 1 : 0;

    fetch('{{ route("admin.hr.settings.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        msg.style.display = 'block';
        msg.innerHTML = d.status === 'success'
            ? '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>'
            : '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error') + '</div>';
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.innerHTML = '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>';
    });
}

function settingsApprove(id) {
    if (!confirm('Approve this leave request?')) return;
    fetch(`/admin/hr/request/${id}/approve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            document.getElementById('settings-pending-' + id)?.remove();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}

function settingsReject(id) {
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
            document.getElementById('settings-pending-' + id)?.remove();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}
</script>
@endpush

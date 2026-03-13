@extends('layouts.admin-hr')

@section('title', 'HR Settings')

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.landing') }}">Admin</a></li>
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
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-pending">Pending <span class="badge bg-warning">{{ $pendingRequests->count() }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-adjustments">Adjustments</a></li>
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
                    <h4 class="card-title">Pending Leave Requests</h4>
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

            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Pending Purchase Requests</h4>
                </div>
                <div class="card-body">
                    <div id="pendingPurchasesContainer">
                        <p class="text-muted mb-0">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-adjustments">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title"><i class="fas fa-clock me-2"></i>Grant TOIL</h4>
                        </div>
                        <div class="card-body">
                            @if($settings->allow_toil)
                            <form id="toilForm" onsubmit="submitToil(event)">
                                <div class="mb-3">
                                    <label class="form-label">Employee</label>
                                    <select name="employee_id" class="form-select form-select-sm" required>
                                        <option value="">Select employee...</option>
                                        @foreach($employees->where('is_active', true) as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Days</label>
                                    <input type="number" name="days" class="form-control form-control-sm" step="0.25" min="0.25" max="5" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason</label>
                                    <textarea name="reason" class="form-control form-control-sm" rows="2" maxlength="500"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Grant TOIL</button>
                            </form>
                            <div id="toilMessage" class="mt-2 d-none"></div>
                            @else
                            <p class="text-muted mb-0">TOIL is not enabled. Enable it in the Settings tab.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title"><i class="fas fa-gift me-2"></i>Grant Gifted Holiday</h4>
                        </div>
                        <div class="card-body">
                            <form id="giftedForm" onsubmit="submitGifted(event)">
                                <div class="mb-3">
                                    <label class="form-label">Employee</label>
                                    <select name="employee_id" class="form-select form-select-sm" required>
                                        <option value="">Select employee...</option>
                                        @foreach($employees->where('is_active', true) as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Days</label>
                                    <input type="number" name="days" class="form-control form-control-sm" step="0.25" min="0.25" max="5" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason</label>
                                    <textarea name="reason" class="form-control form-control-sm" rows="2" maxlength="500"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100">Grant Gifted Holiday</button>
                            </form>
                            <div id="giftedMessage" class="mt-2 d-none"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title"><i class="fas fa-redo me-2"></i>Year-End Carry Over</h4>
                </div>
                <div class="card-body">
                    @if($settings->allow_carry_over)
                    <p class="text-muted" style="font-size: 0.85rem;">
                        Carry over unused annual leave from one year to the next. The carry-over respects the additional pool cap of {{ number_format($settings->max_additional_units / 4, 1) }} days.
                    </p>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Closing Year</label>
                            <select id="carryOverYear" class="form-select form-select-sm">
                                @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                                    <option value="{{ $y }}" {{ $y == date('Y') - 1 ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-8 d-flex align-items-end gap-2 mb-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="runCarryOver(true)"><i class="fas fa-eye me-1"></i>Dry Run</button>
                            <button class="btn btn-primary btn-sm" onclick="runCarryOver(false)"><i class="fas fa-redo me-1"></i>Run Carry Over</button>
                        </div>
                    </div>
                    <div id="carryOverOutput" class="d-none">
                        <pre id="carryOverPre" class="bg-light p-3 border rounded" style="font-size: 0.75rem; max-height: 300px; overflow-y: auto;"></pre>
                    </div>
                    @else
                    <p class="text-muted mb-0">Carry over is not enabled. Enable it in the Settings tab.</p>
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
                        <h6 class="mt-2 mb-3">Entitlements</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Default Annual Entitlement (days)</label>
                                <input type="number" name="default_annual_entitlement_days" class="form-control form-control-sm" value="{{ $settings->default_entitlement_days }}" step="0.25" min="0" max="365">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Additional Days (carry-over + purchased + gifted)</label>
                                <input type="number" name="max_additional_days" class="form-control form-control-sm" value="{{ $settings->max_additional_units / 4 }}" step="0.25" min="0" max="30">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allow_purchase" value="1" {{ $settings->allow_purchase ? 'checked' : '' }}>
                                    <label class="form-check-label">Allow Purchase</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allow_toil" value="1" {{ $settings->allow_toil ? 'checked' : '' }}>
                                    <label class="form-check-label">Allow TOIL</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allow_carry_over" value="1" {{ $settings->allow_carry_over ? 'checked' : '' }}>
                                    <label class="form-check-label">Allow Carry Over</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="birthday_leave_enabled" value="1" {{ $settings->birthday_leave_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label">Birthday Leave</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3 mb-3">Notifications</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Team Notification Email</label>
                                <input type="email" name="team_notification_email" class="form-control form-control-sm" value="{{ $settings->team_notification_email }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="email_notifications_enabled" value="1" {{ $settings->email_notifications_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label">Email Notifications</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="show_leave_type_in_notifications" value="1" {{ $settings->show_leave_type_in_notifications ? 'checked' : '' }}>
                                    <label class="form-check-label">Show Leave Type</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3 mb-3">Webhook Integrations</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Slack Webhook URL</label>
                                <input type="url" name="slack_webhook_url" class="form-control form-control-sm" value="{{ $settings->slack_webhook_url }}" placeholder="https://hooks.slack.com/services/...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Microsoft Teams Webhook URL</label>
                                <input type="url" name="teams_webhook_url" class="form-control form-control-sm" value="{{ $settings->teams_webhook_url }}" placeholder="https://outlook.office.com/webhook/...">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                    </form>
                    <div id="settingsMessage" class="mt-2 d-none"></div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-holidays">
            <div class="card">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Bank Holidays</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="importBankHolidays()"><i class="fas fa-download me-1"></i>Import from GOV.UK</button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBankHolidayModal"><i class="fas fa-plus me-1"></i>Add</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="bankHolidayMessage" class="mb-2 d-none"></div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Date</th><th>Name</th><th>Region</th><th>Year</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody id="bankHolidayTbody">
                                @foreach($bankHolidays as $hol)
                                <tr id="bh-row-{{ $hol->id }}">
                                    <td>{{ $hol->holiday_date->format('D d M Y') }}</td>
                                    <td>
                                        <span id="bh-name-{{ $hol->id }}">{{ $hol->name }}</span>
                                    </td>
                                    <td>{{ ucfirst(str_replace('-', ' & ', $hol->region)) }}</td>
                                    <td>{{ $hol->year }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-primary btn-xs" onclick="editBankHoliday('{{ $hol->id }}', '{{ $hol->holiday_date->format('Y-m-d') }}', '{{ addslashes($hol->name) }}')"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger btn-xs" onclick="deleteBankHoliday('{{ $hol->id }}')"><i class="fas fa-trash"></i></button>
                                    </td>
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
                <div id="addEmployeeMessage" class="mt-2 d-none"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addBankHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Bank Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addBankHolidayForm" onsubmit="addBankHoliday(event)">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="holiday_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Region</label>
                        <select name="region" class="form-select form-select-sm">
                            <option value="england-and-wales">England & Wales</option>
                            <option value="scotland">Scotland</option>
                            <option value="northern-ireland">Northern Ireland</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Add Bank Holiday</button>
                </form>
                <div id="addBankHolidayMessage" class="mt-2 d-none"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editBankHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bank Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBankHolidayForm" onsubmit="updateBankHoliday(event)">
                    <input type="hidden" name="id" id="editBhId">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="holiday_date" id="editBhDate" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="editBhName" class="form-control form-control-sm" maxlength="150" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
                </form>
                <div id="editBankHolidayMessage" class="mt-2 d-none"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function showMsg(el, html) {
    el.classList.remove('d-none');
    el.innerHTML = html;
}

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
        if (d.status === 'success') {
            showMsg(msg, '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>');
            setTimeout(() => location.reload(), 1500);
        } else {
            showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error') + '</div>');
        }
    })
    .catch(() => showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>'));
}

function saveSettings(e) {
    e.preventDefault();
    const form = document.getElementById('hrSettingsForm');
    const msg = document.getElementById('settingsMessage');
    const data = Object.fromEntries(new FormData(form));
    ['birthday_leave_enabled', 'email_notifications_enabled', 'show_leave_type_in_notifications', 'allow_purchase', 'allow_toil', 'allow_carry_over'].forEach(function(key) {
        data[key] = form.querySelector('[name=' + key + ']').checked ? 1 : 0;
    });

    fetch('{{ route("admin.hr.settings.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        showMsg(msg, d.status === 'success'
            ? '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>'
            : '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error') + '</div>');
    })
    .catch(() => showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>'));
}

function settingsApprove(id) {
    if (!confirm('Approve this leave request?')) return;
    fetch('/admin/hr/request/' + id + '/approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            var row = document.getElementById('settings-pending-' + id);
            if (row) row.remove();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}

function settingsReject(id) {
    const comment = prompt('Reason for rejection:');
    if (!comment) return;
    fetch('/admin/hr/request/' + id + '/reject', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ comment: comment }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            var row = document.getElementById('settings-pending-' + id);
            if (row) row.remove();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}

function submitToil(e) {
    e.preventDefault();
    const form = document.getElementById('toilForm');
    const msg = document.getElementById('toilMessage');
    const data = Object.fromEntries(new FormData(form));

    fetch('{{ route("admin.hr.grant-toil") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            showMsg(msg, '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>');
            form.reset();
        } else {
            showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error') + '</div>');
        }
    })
    .catch(() => showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>'));
}

function submitGifted(e) {
    e.preventDefault();
    const form = document.getElementById('giftedForm');
    const msg = document.getElementById('giftedMessage');
    const data = Object.fromEntries(new FormData(form));

    fetch('{{ route("admin.hr.grant-gifted") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            showMsg(msg, '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>');
            form.reset();
        } else {
            showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error') + '</div>');
        }
    })
    .catch(() => showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>'));
}

function runCarryOver(dryRun) {
    if (!dryRun && !confirm('Run carry-over? This will update employee entitlements.')) return;
    const year = document.getElementById('carryOverYear').value;
    const output = document.getElementById('carryOverOutput');
    const pre = document.getElementById('carryOverPre');

    pre.textContent = 'Running...';
    output.classList.remove('d-none');

    fetch('{{ route("admin.hr.carry-over") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ year: year, dry_run: dryRun }),
    })
    .then(r => r.json())
    .then(d => {
        pre.textContent = (d.message || '') + '\n\n' + (d.output || '');
    })
    .catch(() => {
        pre.textContent = 'Network error.';
    });
}

function addBankHoliday(e) {
    e.preventDefault();
    const form = document.getElementById('addBankHolidayForm');
    const msg = document.getElementById('addBankHolidayMessage');
    const data = Object.fromEntries(new FormData(form));

    fetch('{{ route("admin.hr.bank-holiday.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            showMsg(msg, '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>');
            setTimeout(() => location.reload(), 1500);
        } else {
            showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error') + '</div>');
        }
    })
    .catch(() => showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>'));
}

function editBankHoliday(id, date, name) {
    document.getElementById('editBhId').value = id;
    document.getElementById('editBhDate').value = date;
    document.getElementById('editBhName').value = name;
    new bootstrap.Modal(document.getElementById('editBankHolidayModal')).show();
}

function updateBankHoliday(e) {
    e.preventDefault();
    const id = document.getElementById('editBhId').value;
    const msg = document.getElementById('editBankHolidayMessage');
    const data = {
        holiday_date: document.getElementById('editBhDate').value,
        name: document.getElementById('editBhName').value,
    };

    fetch('/admin/hr/bank-holiday/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            location.reload();
        } else {
            showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Error') + '</div>');
        }
    })
    .catch(() => showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>'));
}

function deleteBankHoliday(id) {
    if (!confirm('Delete this bank holiday?')) return;
    fetch('/admin/hr/bank-holiday/' + id, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            var row = document.getElementById('bh-row-' + id);
            if (row) row.remove();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}

function importBankHolidays() {
    if (!confirm('Import bank holidays from GOV.UK? Existing entries will not be duplicated.')) return;
    const msg = document.getElementById('bankHolidayMessage');
    showMsg(msg, '<div class="alert alert-info py-1 px-2 mb-0"><i class="fas fa-spinner fa-spin me-1"></i>Importing...</div>');

    fetch('{{ route("admin.hr.bank-holidays.import") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            showMsg(msg, '<div class="alert alert-success py-1 px-2 mb-0">' + d.message + '</div>');
            setTimeout(() => location.reload(), 2000);
        } else {
            showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">' + (d.message || 'Import failed.') + '</div>');
        }
    })
    .catch(() => showMsg(msg, '<div class="alert alert-danger py-1 px-2 mb-0">Network error.</div>'));
}

function esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
}

function loadPendingPurchases() {
    fetch('{{ route("admin.hr.api.pending-adjustments") }}')
    .then(r => r.json())
    .then(d => {
        const container = document.getElementById('pendingPurchasesContainer');
        if (d.status !== 'success' || !d.data || d.data.length === 0) {
            container.innerHTML = '<p class="text-muted mb-0">No pending purchase requests.</p>';
            return;
        }
        let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Employee</th><th>Type</th><th>Days</th><th>Year</th><th>Reason</th><th>Requested By</th><th class="text-end">Actions</th></tr></thead><tbody>';
        d.data.forEach(function(r) {
            html += '<tr id="adj-row-' + esc(r.id) + '">';
            html += '<td>' + esc(r.employee_name) + '</td>';
            html += '<td><span class="badge bg-primary badge-sm">' + esc(r.type_label) + '</span></td>';
            html += '<td>' + esc(String(r.days)) + 'd</td>';
            html += '<td>' + esc(String(r.year)) + '</td>';
            html += '<td>' + esc(r.reason || '—') + '</td>';
            html += '<td>' + esc(r.requested_by) + '</td>';
            html += '<td class="text-end">';
            html += '<button class="btn btn-success btn-xs" onclick="approvePurchase(\'' + esc(r.id) + '\')"><i class="fas fa-check"></i></button> ';
            html += '<button class="btn btn-danger btn-xs" onclick="rejectPurchase(\'' + esc(r.id) + '\')"><i class="fas fa-times"></i></button>';
            html += '</td></tr>';
        });
        html += '</tbody></table></div>';
        container.innerHTML = html;
    })
    .catch(() => {
        document.getElementById('pendingPurchasesContainer').innerHTML = '<p class="text-muted mb-0">Error loading purchase requests.</p>';
    });
}

function approvePurchase(id) {
    if (!confirm('Approve this purchase request?')) return;
    const note = prompt('Note (optional):') || '';
    fetch('/admin/hr/purchase/' + id + '/approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ note: note }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            var row = document.getElementById('adj-row-' + id);
            if (row) row.remove();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}

function rejectPurchase(id) {
    const note = prompt('Reason for rejection:');
    if (!note) return;
    fetch('/admin/hr/purchase/' + id + '/reject', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ note: note }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            var row = document.getElementById('adj-row-' + id);
            if (row) row.remove();
        } else {
            alert(d.message || 'Error');
        }
    })
    .catch(() => alert('Network error'));
}

document.addEventListener('DOMContentLoaded', function() {
    var pendingTab = document.querySelector('[href="#tab-pending"]');
    if (pendingTab) {
        pendingTab.addEventListener('shown.bs.tab', loadPendingPurchases);
    }
});
</script>
@endpush

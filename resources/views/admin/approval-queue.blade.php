@extends('layouts.admin')

@section('title', 'Approval Queue')

@push('styles')
<style>
.queue-stats-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.queue-stat-card {
    background: #fff;
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    min-width: 140px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.queue-stat-card:hover {
    border-color: #1e3a5f;
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.1);
}

.queue-stat-card.active {
    border-color: #1e3a5f;
    background: rgba(30, 58, 95, 0.05);
}

.queue-stat-card .stat-count {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.2;
}

.queue-stat-card .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.25rem;
}

.queue-stat-card.awaiting .stat-count { color: #f59e0b; }
.queue-stat-card.sla-critical .stat-count { color: #dc2626; }
.queue-stat-card.high-risk .stat-count { color: #9333ea; }
.queue-stat-card.assigned .stat-count { color: #2563eb; }
.queue-stat-card.total .stat-count { color: #1e3a5f; }

.filter-panel {
    background: #f8f9fc;
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}

.filter-row {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 150px;
}

.filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-group select,
.filter-group input {
    font-size: 0.875rem;
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    margin-left: auto;
}

.btn-apply {
    background: #1e3a5f;
    color: #fff;
    border: none;
    padding: 0.5rem 1.25rem;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
}

.btn-apply:hover {
    background: #2d5a87;
    color: #fff;
}

.sort-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.5rem 0;
}

.sort-options {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.sort-options label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-right: 0.5rem;
}

.sort-btn {
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    background: #fff;
    border-radius: 6px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
}

.sort-btn.active {
    background: #1e3a5f;
    color: #fff;
    border-color: #1e3a5f;
}

.sort-btn:hover:not(.active) {
    border-color: #1e3a5f;
    color: #1e3a5f;
}

.queue-results {
    font-size: 0.875rem;
    color: #6c757d;
}

.queue-table-container {
    background: #fff;
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    overflow: hidden;
}

.queue-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.queue-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e5e9f2;
}

.queue-table th {
    padding: 0.875rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #1e3a5f;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.queue-table td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.queue-table tbody tr:hover {
    background: #f8f9fc;
}

.queue-table tbody tr.sla-critical {
    background: rgba(220, 38, 38, 0.03);
}

.queue-table tbody tr.sla-critical:hover {
    background: rgba(220, 38, 38, 0.06);
}

.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.type-badge.sender-id {
    background: #dbeafe;
    color: #1e40af;
}

.type-badge.rcs-agent {
    background: #ede9fe;
    color: #6b21a8;
}

.account-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.account-name {
    font-weight: 500;
    color: #1e3a5f;
}

.sub-account-name {
    font-size: 0.75rem;
    color: #6c757d;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
}

.status-pill.submitted {
    background: #dbeafe;
    color: #1e40af;
}

.status-pill.in-review {
    background: #e0e7ff;
    color: #3730a3;
}

.status-pill.returned-to-customer {
    background: #fef3c7;
    color: #92400e;
}

.status-pill.resubmitted {
    background: #d1fae5;
    color: #065f46;
}

.status-pill.validation-in-progress {
    background: #fce7f3;
    color: #9d174d;
}

.status-pill.validation-failed {
    background: #fee2e2;
    color: #991b1b;
}

.status-pill.approved {
    background: #d9f99d;
    color: #3f6212;
}

.status-pill.rejected {
    background: #fecaca;
    color: #7f1d1d;
}

.status-pill.provisioning-in-progress {
    background: #c7d2fe;
    color: #4338ca;
}

.status-pill.live {
    background: #bbf7d0;
    color: #15803d;
}

.sla-timer {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-weight: 500;
    font-size: 0.8rem;
}

.sla-timer.critical {
    color: #dc2626;
}

.sla-timer.warning {
    color: #f59e0b;
}

.sla-timer.normal {
    color: #059669;
}

.sla-timer i {
    font-size: 0.7rem;
}

.risk-flags {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
}

.risk-pill {
    padding: 0.125rem 0.5rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.risk-pill.high {
    background: #fecaca;
    color: #991b1b;
}

.risk-pill.medium {
    background: #fed7aa;
    color: #9a3412;
}

.risk-pill.low {
    background: #d9f99d;
    color: #3f6212;
}

.risk-pill.spam-keywords {
    background: #fce7f3;
    color: #9d174d;
}

.risk-pill.new-account {
    background: #e0e7ff;
    color: #3730a3;
}

.risk-pill.high-volume {
    background: #fef3c7;
    color: #92400e;
}

.validation-status {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8rem;
}

.validation-status.verified {
    color: #059669;
}

.validation-status.pending {
    color: #f59e0b;
}

.validation-status.failed {
    color: #dc2626;
}

.validation-status.na {
    color: #9ca3af;
}

.assigned-admin {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #1e3a5f;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 600;
}

.admin-name {
    font-size: 0.8rem;
    color: #495057;
}

.unassigned {
    font-size: 0.8rem;
    color: #9ca3af;
    font-style: italic;
}

.action-menu {
    position: relative;
}

.action-btn {
    background: none;
    border: 1px solid #e5e9f2;
    border-radius: 6px;
    padding: 0.375rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #f8f9fc;
    border-color: #1e3a5f;
    color: #1e3a5f;
}

.action-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 180px;
    z-index: 100;
    display: none;
}

.action-dropdown.show {
    display: block;
}

.action-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 1rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #495057;
    transition: background 0.15s;
}

.action-dropdown-item:hover {
    background: #f8f9fc;
}

.action-dropdown-item i {
    width: 16px;
    text-align: center;
    color: #6c757d;
}

.action-dropdown-item.approve { color: #059669; }
.action-dropdown-item.approve i { color: #059669; }
.action-dropdown-item.warn { color: #f59e0b; }
.action-dropdown-item.warn i { color: #f59e0b; }
.action-dropdown-item.reject { color: #dc2626; }
.action-dropdown-item.reject i { color: #dc2626; }

.action-divider {
    height: 1px;
    background: #e5e9f2;
    margin: 0.25rem 0;
}

.submitted-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.submitted-date {
    font-weight: 500;
    color: #1e3a5f;
}

.submitted-time {
    font-size: 0.75rem;
    color: #6c757d;
}

.submitter-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.submitter-name {
    font-weight: 500;
    color: #495057;
}

.submitter-email {
    font-size: 0.7rem;
    color: #9ca3af;
}

.pagination-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fc;
    border-top: 1px solid #e5e9f2;
}

.pagination-info {
    font-size: 0.875rem;
    color: #6c757d;
}

.pagination {
    display: flex;
    gap: 0.25rem;
    margin: 0;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem;
    border: 1px solid #e5e9f2;
    background: #fff;
    color: #495057;
    border-radius: 4px;
    font-size: 0.875rem;
    cursor: pointer;
    white-space: nowrap;
}

.pagination .page-link:hover {
    background: #f8f9fc;
    border-color: #1e3a5f;
}

.pagination .page-link.active {
    background: #1e3a5f;
    color: #fff;
    border-color: #1e3a5f;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    color: #e5e9f2;
    margin-bottom: 1rem;
}
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <span>Approval Queue</span>
    </div>

    <div class="page-header">
        <div>
            <h4><i class="fas fa-inbox me-2"></i>Approval Queue</h4>
            <p>Unified view of all pending SenderID and RCS Agent registration requests</p>
        </div>
        <div class="card-header-actions">
            <button class="export-btn" onclick="exportQueue()">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="queue-stats-row">
        <div class="queue-stat-card awaiting active" data-filter="awaiting">
            <div class="stat-count" id="stat-awaiting">8</div>
            <div class="stat-label">Awaiting Action</div>
        </div>
        <div class="queue-stat-card sla-critical" data-filter="sla-critical">
            <div class="stat-count" id="stat-sla-critical">3</div>
            <div class="stat-label">SLA Critical</div>
        </div>
        <div class="queue-stat-card high-risk" data-filter="high-risk">
            <div class="stat-count" id="stat-high-risk">4</div>
            <div class="stat-label">High Risk</div>
        </div>
        <div class="queue-stat-card assigned" data-filter="assigned-to-me">
            <div class="stat-count" id="stat-assigned">2</div>
            <div class="stat-label">Assigned to Me</div>
        </div>
        <div class="queue-stat-card total" data-filter="all">
            <div class="stat-count" id="stat-total">11</div>
            <div class="stat-label">Total In Queue</div>
        </div>
    </div>

    <div class="filter-panel">
        <div class="filter-row">
            <div class="filter-group">
                <label>Request Type</label>
                <select class="form-select form-select-sm" id="filterType">
                    <option value="">All Types</option>
                    <option value="sender-id">Sender ID</option>
                    <option value="rcs-agent">RCS Agent</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="submitted">Submitted</option>
                    <option value="in-review">In Review</option>
                    <option value="returned-to-customer">Returned to Customer</option>
                    <option value="resubmitted">Resubmitted</option>
                    <option value="validation-in-progress">Validation In Progress</option>
                    <option value="validation-failed">Validation Failed</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="provisioning-in-progress">Provisioning In Progress</option>
                    <option value="live">Live</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Risk Level</label>
                <select class="form-select form-select-sm" id="filterRisk">
                    <option value="">All Levels</option>
                    <option value="high">High Risk</option>
                    <option value="medium">Medium Risk</option>
                    <option value="low">Low Risk</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Account</label>
                <select class="form-select form-select-sm" id="filterAccount">
                    <option value="">All Accounts</option>
                    <option value="ACC-1234">Acme Corporation</option>
                    <option value="ACC-5678">Finance Ltd</option>
                    <option value="ACC-3456">MedTech Solutions</option>
                    <option value="ACC-4001">RetailMax Group</option>
                    <option value="ACC-4005">HealthPlus Care</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Age in Queue</label>
                <select class="form-select form-select-sm" id="filterAge">
                    <option value="">Any Age</option>
                    <option value="24h">Less than 24h</option>
                    <option value="1-3d">1-3 Days</option>
                    <option value="3d+">More than 3 Days</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Assigned Admin</label>
                <select class="form-select form-select-sm" id="filterAdmin">
                    <option value="">All Admins</option>
                    <option value="me">Assigned to Me</option>
                    <option value="unassigned">Unassigned</option>
                    <option value="admin-1">Sarah Johnson</option>
                    <option value="admin-2">Mike Chen</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-apply" onclick="applyFilters()">Apply</button>
                <button class="btn btn-link text-muted" onclick="clearFilters()">Clear</button>
            </div>
        </div>
    </div>

    <div class="sort-bar">
        <div class="sort-options">
            <label>Sort by:</label>
            <button class="sort-btn active" data-sort="oldest" onclick="setSort('oldest')">Oldest First</button>
            <button class="sort-btn" data-sort="sla" onclick="setSort('sla')">SLA Priority</button>
            <button class="sort-btn" data-sort="risk" onclick="setSort('risk')">Risk Level</button>
        </div>
        <div class="queue-results">
            Showing <strong id="showing-count">11</strong> of <strong id="total-count">11</strong> requests
        </div>
    </div>

    <div class="queue-table-container">
        <table class="queue-table">
            <thead>
                <tr>
                    <th>Request Type</th>
                    <th>Account Name</th>
                    <th>Sub-Account</th>
                    <th>Submitted By</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>SLA Timer</th>
                    <th>Risk Flags</th>
                    <th>External Validation</th>
                    <th>Assigned Admin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="queue-tbody">
                <tr data-id="SID-001" data-type="sender-id" data-status="submitted" data-risk="low" data-account="ACC-1234" data-sla="18" data-age="2h">
                    <td>
                        <span class="type-badge sender-id">
                            <i class="fas fa-signature"></i> Sender ID
                        </span>
                    </td>
                    <td><span class="account-name">Acme Corporation</span></td>
                    <td><span class="sub-account-name">Marketing Dept</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">John Smith</span>
                            <span class="submitter-email">j.smith@acme.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 20, 2026</span>
                            <span class="submitted-time">10:15 AM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer normal"><i class="fas fa-hourglass-half"></i> 18h remaining</span></td>
                    <td><div class="risk-flags"><span class="risk-pill low">Low</span></div></td>
                    <td><span class="validation-status verified"><i class="fas fa-check-circle"></i> Verified</span></td>
                    <td><span class="unassigned">Unassigned</span></td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('SID-001', 'sender-id')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="assignToMe('SID-001')"><i class="fas fa-user-plus"></i> Assign to Me</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item" onclick="markInReview('SID-001')"><i class="fas fa-search"></i> In Review</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('SID-001')"><i class="fas fa-times-circle"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="sla-critical" data-id="RCS-001" data-type="rcs-agent" data-status="submitted" data-risk="high" data-account="ACC-5678" data-sla="2" data-age="46h">
                    <td>
                        <span class="type-badge rcs-agent">
                            <i class="fas fa-robot"></i> RCS Agent
                        </span>
                    </td>
                    <td><span class="account-name">Finance Ltd</span></td>
                    <td><span class="sub-account-na">-</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">Emily Davis</span>
                            <span class="submitter-email">e.davis@financeltd.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 18, 2026</span>
                            <span class="submitted-time">2:30 PM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer critical"><i class="fas fa-exclamation-triangle"></i> 2h remaining</span></td>
                    <td>
                        <div class="risk-flags">
                            <span class="risk-pill high">High</span>
                            <span class="risk-pill spam-keywords">Spam Keywords</span>
                        </div>
                    </td>
                    <td><span class="validation-status pending"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="assigned-admin">
                            <span class="admin-avatar">SJ</span>
                            <span class="admin-name">Sarah Johnson</span>
                        </div>
                    </td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('RCS-001', 'rcs-agent')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="reassign('RCS-001')"><i class="fas fa-user-edit"></i> Reassign</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item" onclick="markInReview('RCS-001')"><i class="fas fa-search"></i> In Review</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('RCS-001')"><i class="fas fa-times-circle"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr data-id="SID-002" data-type="sender-id" data-status="submitted" data-risk="medium" data-account="ACC-3456" data-sla="12" data-age="12h">
                    <td>
                        <span class="type-badge sender-id">
                            <i class="fas fa-signature"></i> Sender ID
                        </span>
                    </td>
                    <td><span class="account-name">MedTech Solutions</span></td>
                    <td><span class="sub-account-name">Patient Services</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">Dr. Sarah Wilson</span>
                            <span class="submitter-email">s.wilson@medtech.nhs.uk</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 20, 2026</span>
                            <span class="submitted-time">12:45 AM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer warning"><i class="fas fa-hourglass-half"></i> 12h remaining</span></td>
                    <td>
                        <div class="risk-flags">
                            <span class="risk-pill medium">Medium</span>
                            <span class="risk-pill new-account">New Account</span>
                        </div>
                    </td>
                    <td><span class="validation-status verified"><i class="fas fa-check-circle"></i> Verified</span></td>
                    <td><span class="unassigned">Unassigned</span></td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('SID-002', 'sender-id')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="assignToMe('SID-002')"><i class="fas fa-user-plus"></i> Assign to Me</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('SID-002')"><i class="fas fa-check"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('SID-002')"><i class="fas fa-times"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr data-id="RCS-002" data-type="rcs-agent" data-status="in-review" data-risk="low" data-account="ACC-4001" data-sla="36" data-age="8h">
                    <td>
                        <span class="type-badge rcs-agent">
                            <i class="fas fa-robot"></i> RCS Agent
                        </span>
                    </td>
                    <td><span class="account-name">RetailMax Group</span></td>
                    <td><span class="sub-account-name">E-Commerce Division</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">Michael Brown</span>
                            <span class="submitter-email">m.brown@retailmax.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill in-review"><i class="fas fa-search"></i> In Review</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 20, 2026</span>
                            <span class="submitted-time">4:15 AM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer normal"><i class="fas fa-hourglass-half"></i> 36h remaining</span></td>
                    <td><div class="risk-flags"><span class="risk-pill low">Low</span></div></td>
                    <td><span class="validation-status verified"><i class="fas fa-check-circle"></i> Verified</span></td>
                    <td>
                        <div class="assigned-admin">
                            <span class="admin-avatar">MC</span>
                            <span class="admin-name">Mike Chen</span>
                        </div>
                    </td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('RCS-002', 'rcs-agent')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="reassign('RCS-002')"><i class="fas fa-user-edit"></i> Reassign</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item warn" onclick="returnToCustomer('RCS-002')"><i class="fas fa-reply"></i> Return to Customer</div>
                                <div class="action-dropdown-item" onclick="startValidation('RCS-002')"><i class="fas fa-spinner"></i> Start Validation</div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('RCS-002')"><i class="fas fa-check-circle"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('RCS-002')"><i class="fas fa-times-circle"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="sla-critical" data-id="SID-003" data-type="sender-id" data-status="submitted" data-risk="high" data-account="ACC-4005" data-sla="0" data-age="52h">
                    <td>
                        <span class="type-badge sender-id">
                            <i class="fas fa-signature"></i> Sender ID
                        </span>
                    </td>
                    <td><span class="account-name">HealthPlus Care</span></td>
                    <td><span class="sub-account-na">-</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">James Taylor</span>
                            <span class="submitter-email">j.taylor@healthplus.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 18, 2026</span>
                            <span class="submitted-time">8:00 AM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer critical"><i class="fas fa-exclamation-circle"></i> SLA Breached</span></td>
                    <td>
                        <div class="risk-flags">
                            <span class="risk-pill high">High</span>
                            <span class="risk-pill high-volume">High Volume</span>
                        </div>
                    </td>
                    <td><span class="validation-status failed"><i class="fas fa-times-circle"></i> Failed</span></td>
                    <td><span class="unassigned">Unassigned</span></td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('SID-003', 'sender-id')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="assignToMe('SID-003')"><i class="fas fa-user-plus"></i> Assign to Me</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('SID-003')"><i class="fas fa-check"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('SID-003')"><i class="fas fa-times"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr data-id="RCS-003" data-type="rcs-agent" data-status="submitted" data-risk="medium" data-account="ACC-1234" data-sla="42" data-age="6h">
                    <td>
                        <span class="type-badge rcs-agent">
                            <i class="fas fa-robot"></i> RCS Agent
                        </span>
                    </td>
                    <td><span class="account-name">Acme Corporation</span></td>
                    <td><span class="sub-account-name">Sales Team</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">Lisa Anderson</span>
                            <span class="submitter-email">l.anderson@acme.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 20, 2026</span>
                            <span class="submitted-time">6:30 AM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer normal"><i class="fas fa-hourglass-half"></i> 42h remaining</span></td>
                    <td>
                        <div class="risk-flags">
                            <span class="risk-pill medium">Medium</span>
                        </div>
                    </td>
                    <td><span class="validation-status pending"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td><span class="unassigned">Unassigned</span></td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('RCS-003', 'rcs-agent')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="assignToMe('RCS-003')"><i class="fas fa-user-plus"></i> Assign to Me</div>
                                <div class="action-dropdown-item" onclick="markInReview('RCS-003')"><i class="fas fa-search"></i> Mark In Review</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('RCS-003')"><i class="fas fa-check"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('RCS-003')"><i class="fas fa-times"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr data-id="SID-004" data-type="sender-id" data-status="submitted" data-risk="low" data-account="ACC-5678" data-sla="22" data-age="2h">
                    <td>
                        <span class="type-badge sender-id">
                            <i class="fas fa-signature"></i> Sender ID
                        </span>
                    </td>
                    <td><span class="account-name">Finance Ltd</span></td>
                    <td><span class="sub-account-name">Compliance Team</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">Robert White</span>
                            <span class="submitter-email">r.white@financeltd.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 20, 2026</span>
                            <span class="submitted-time">10:00 AM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer normal"><i class="fas fa-hourglass-half"></i> 22h remaining</span></td>
                    <td><div class="risk-flags"><span class="risk-pill low">Low</span></div></td>
                    <td><span class="validation-status verified"><i class="fas fa-check-circle"></i> Verified</span></td>
                    <td><span class="unassigned">Unassigned</span></td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('SID-004', 'sender-id')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="assignToMe('SID-004')"><i class="fas fa-user-plus"></i> Assign to Me</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('SID-004')"><i class="fas fa-check"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('SID-004')"><i class="fas fa-times"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="sla-critical" data-id="RCS-004" data-type="rcs-agent" data-status="submitted" data-risk="high" data-account="ACC-3456" data-sla="4" data-age="44h">
                    <td>
                        <span class="type-badge rcs-agent">
                            <i class="fas fa-robot"></i> RCS Agent
                        </span>
                    </td>
                    <td><span class="account-name">MedTech Solutions</span></td>
                    <td><span class="sub-account-na">-</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">Jennifer Martinez</span>
                            <span class="submitter-email">j.martinez@medtech.nhs.uk</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 18, 2026</span>
                            <span class="submitted-time">4:00 PM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer critical"><i class="fas fa-exclamation-triangle"></i> 4h remaining</span></td>
                    <td>
                        <div class="risk-flags">
                            <span class="risk-pill high">High</span>
                            <span class="risk-pill new-account">New Account</span>
                        </div>
                    </td>
                    <td><span class="validation-status na"><i class="fas fa-minus-circle"></i> N/A</span></td>
                    <td><span class="unassigned">Unassigned</span></td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('RCS-004', 'rcs-agent')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="assignToMe('RCS-004')"><i class="fas fa-user-plus"></i> Assign to Me</div>
                                <div class="action-dropdown-item" onclick="markInReview('RCS-004')"><i class="fas fa-search"></i> Mark In Review</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('RCS-004')"><i class="fas fa-check"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('RCS-004')"><i class="fas fa-times"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr data-id="SID-005" data-type="sender-id" data-status="submitted" data-risk="low" data-account="ACC-4001" data-sla="20" data-age="4h">
                    <td>
                        <span class="type-badge sender-id">
                            <i class="fas fa-signature"></i> Sender ID
                        </span>
                    </td>
                    <td><span class="account-name">RetailMax Group</span></td>
                    <td><span class="sub-account-name">Notifications Team</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">David Lee</span>
                            <span class="submitter-email">d.lee@retailmax.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 20, 2026</span>
                            <span class="submitted-time">8:15 AM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer normal"><i class="fas fa-hourglass-half"></i> 20h remaining</span></td>
                    <td><div class="risk-flags"><span class="risk-pill low">Low</span></div></td>
                    <td><span class="validation-status verified"><i class="fas fa-check-circle"></i> Verified</span></td>
                    <td><span class="unassigned">Unassigned</span></td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('SID-005', 'sender-id')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="assignToMe('SID-005')"><i class="fas fa-user-plus"></i> Assign to Me</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('SID-005')"><i class="fas fa-check"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('SID-005')"><i class="fas fa-times"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr data-id="RCS-005" data-type="rcs-agent" data-status="in-review" data-risk="medium" data-account="ACC-4005" data-sla="28" data-age="20h">
                    <td>
                        <span class="type-badge rcs-agent">
                            <i class="fas fa-robot"></i> RCS Agent
                        </span>
                    </td>
                    <td><span class="account-name">HealthPlus Care</span></td>
                    <td><span class="sub-account-name">Appointments Dept</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">Amanda Foster</span>
                            <span class="submitter-email">a.foster@healthplus.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill in-review"><i class="fas fa-search"></i> In Review</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 19, 2026</span>
                            <span class="submitted-time">4:15 PM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer normal"><i class="fas fa-hourglass-half"></i> 28h remaining</span></td>
                    <td>
                        <div class="risk-flags">
                            <span class="risk-pill medium">Medium</span>
                        </div>
                    </td>
                    <td><span class="validation-status verified"><i class="fas fa-check-circle"></i> Verified</span></td>
                    <td>
                        <div class="assigned-admin">
                            <span class="admin-avatar">SJ</span>
                            <span class="admin-name">Sarah Johnson</span>
                        </div>
                    </td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('RCS-005', 'rcs-agent')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="reassign('RCS-005')"><i class="fas fa-user-edit"></i> Reassign</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('RCS-005')"><i class="fas fa-check"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('RCS-005')"><i class="fas fa-times"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr data-id="SID-006" data-type="sender-id" data-status="submitted" data-risk="medium" data-account="ACC-1234" data-sla="16" data-age="8h">
                    <td>
                        <span class="type-badge sender-id">
                            <i class="fas fa-signature"></i> Sender ID
                        </span>
                    </td>
                    <td><span class="account-name">Acme Corporation</span></td>
                    <td><span class="sub-account-name">Support Team</span></td>
                    <td>
                        <div class="submitter-cell">
                            <span class="submitter-name">Chris Johnson</span>
                            <span class="submitter-email">c.johnson@acme.com</span>
                        </div>
                    </td>
                    <td><span class="status-pill submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                    <td>
                        <div class="submitted-cell">
                            <span class="submitted-date">Jan 20, 2026</span>
                            <span class="submitted-time">4:30 AM</span>
                        </div>
                    </td>
                    <td><span class="sla-timer warning"><i class="fas fa-hourglass-half"></i> 16h remaining</span></td>
                    <td>
                        <div class="risk-flags">
                            <span class="risk-pill medium">Medium</span>
                            <span class="risk-pill high-volume">High Volume</span>
                        </div>
                    </td>
                    <td><span class="validation-status verified"><i class="fas fa-check-circle"></i> Verified</span></td>
                    <td><span class="unassigned">Unassigned</span></td>
                    <td>
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleActionMenu(this)"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="action-dropdown">
                                <div class="action-dropdown-item" onclick="viewDetails('SID-006', 'sender-id')"><i class="fas fa-eye"></i> View Details</div>
                                <div class="action-dropdown-item" onclick="assignToMe('SID-006')"><i class="fas fa-user-plus"></i> Assign to Me</div>
                                <div class="action-divider"></div>
                                <div class="action-dropdown-item approve" onclick="quickApprove('SID-006')"><i class="fas fa-check"></i> Approve</div>
                                <div class="action-dropdown-item reject" onclick="showRejectModal('SID-006')"><i class="fas fa-times"></i> Reject</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="pagination-row">
            <div class="pagination-info">
                Showing 1-11 of 11 requests
            </div>
            <nav>
                <ul class="pagination">
                    <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item"><a class="page-link active" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reject-request-id">
                <div class="mb-3">
                    <label class="form-label">Rejection Reason</label>
                    <select class="form-select mb-2" id="rejection-template">
                        <option value="">Select a reason...</option>
                        <option value="incomplete">Incomplete documentation</option>
                        <option value="verification-failed">Business verification failed</option>
                        <option value="policy-violation">Policy violation detected</option>
                        <option value="spam-content">Suspected spam content</option>
                        <option value="duplicate">Duplicate request</option>
                        <option value="other">Other (specify below)</option>
                    </select>
                    <textarea class="form-control" id="rejection-notes" rows="3" placeholder="Additional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">Reject Request</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script>
var APPROVAL_LIFECYCLE = {
    STATUSES: {
        SUBMITTED: 'submitted',
        IN_REVIEW: 'in-review',
        RETURNED_TO_CUSTOMER: 'returned-to-customer',
        RESUBMITTED: 'resubmitted',
        VALIDATION_IN_PROGRESS: 'validation-in-progress',
        VALIDATION_FAILED: 'validation-failed',
        APPROVED: 'approved',
        REJECTED: 'rejected',
        PROVISIONING_IN_PROGRESS: 'provisioning-in-progress',
        LIVE: 'live'
    },
    
    STATUS_DISPLAY: {
        'submitted': { label: 'Submitted', icon: 'fa-paper-plane' },
        'in-review': { label: 'In Review', icon: 'fa-search' },
        'returned-to-customer': { label: 'Returned to Customer', icon: 'fa-reply' },
        'resubmitted': { label: 'Resubmitted', icon: 'fa-redo' },
        'validation-in-progress': { label: 'Validation In Progress', icon: 'fa-spinner fa-spin' },
        'validation-failed': { label: 'Validation Failed', icon: 'fa-exclamation-circle' },
        'approved': { label: 'Approved', icon: 'fa-check-circle' },
        'rejected': { label: 'Rejected', icon: 'fa-times-circle' },
        'provisioning-in-progress': { label: 'Provisioning In Progress', icon: 'fa-cog fa-spin' },
        'live': { label: 'Live', icon: 'fa-broadcast-tower' }
    },
    
    TRANSITIONS: {
        'submitted': ['in-review', 'rejected'],
        'in-review': ['returned-to-customer', 'validation-in-progress', 'approved', 'rejected'],
        'returned-to-customer': [],
        'resubmitted': ['in-review', 'rejected'],
        'validation-in-progress': ['validation-failed', 'approved'],
        'validation-failed': ['returned-to-customer', 'rejected'],
        'approved': ['provisioning-in-progress', 'live'],
        'rejected': [],
        'provisioning-in-progress': ['live', 'validation-failed'],
        'live': []
    },
    
    RCS_ONLY_STATUSES: ['provisioning-in-progress'],
    
    AWAITING_ADMIN_ACTION: ['submitted', 'resubmitted', 'in-review', 'validation-failed'],
    
    TERMINAL_STATES: ['rejected', 'live'],
    
    canTransition: function(currentStatus, targetStatus, requestType) {
        if (this.RCS_ONLY_STATUSES.includes(targetStatus) && requestType !== 'rcs-agent') {
            console.warn('[LIFECYCLE] Status', targetStatus, 'is RCS-only');
            return false;
        }
        
        var allowed = this.TRANSITIONS[currentStatus] || [];
        return allowed.includes(targetStatus);
    },
    
    getAvailableTransitions: function(currentStatus, requestType) {
        var transitions = this.TRANSITIONS[currentStatus] || [];
        var self = this;
        
        if (requestType !== 'rcs-agent') {
            transitions = transitions.filter(function(s) {
                return !self.RCS_ONLY_STATUSES.includes(s);
            });
        }
        
        return transitions;
    }
};

var currentSort = 'oldest';
var openDropdown = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('[Approval Queue] Initialized');
    console.log('[LIFECYCLE] Loaded unified lifecycle with statuses:', Object.keys(APPROVAL_LIFECYCLE.STATUSES));
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('PAGE_VIEW', 'approval-queue', { module: 'approval_queue' }, 'LOW');
    }
    
    document.querySelectorAll('.queue-stat-card').forEach(function(card) {
        card.addEventListener('click', function() {
            document.querySelectorAll('.queue-stat-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            applyStatFilter(this.dataset.filter);
        });
    });
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-menu')) {
            closeAllDropdowns();
        }
    });
});

function toggleActionMenu(btn) {
    var dropdown = btn.nextElementSibling;
    var wasOpen = dropdown.classList.contains('show');
    
    closeAllDropdowns();
    
    if (!wasOpen) {
        dropdown.classList.add('show');
        openDropdown = dropdown;
    }
}

function closeAllDropdowns() {
    document.querySelectorAll('.action-dropdown.show').forEach(function(d) {
        d.classList.remove('show');
    });
    openDropdown = null;
}

function applyStatFilter(filter) {
    var rows = document.querySelectorAll('#queue-tbody tr');
    var visibleCount = 0;
    
    rows.forEach(function(row) {
        var show = true;
        
        if (filter === 'awaiting') {
            show = APPROVAL_LIFECYCLE.AWAITING_ADMIN_ACTION.includes(row.dataset.status);
        } else if (filter === 'sla-critical') {
            show = parseInt(row.dataset.sla) <= 8;
        } else if (filter === 'high-risk') {
            show = row.dataset.risk === 'high';
        } else if (filter === 'assigned-to-me') {
            show = row.querySelector('.admin-name') !== null;
        } else if (filter === 'all') {
            show = true;
        }
        
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    
    document.getElementById('showing-count').textContent = visibleCount;
}

function applyFilters() {
    var type = document.getElementById('filterType').value;
    var status = document.getElementById('filterStatus').value;
    var risk = document.getElementById('filterRisk').value;
    var account = document.getElementById('filterAccount').value;
    var age = document.getElementById('filterAge').value;
    var admin = document.getElementById('filterAdmin').value;
    
    var rows = document.querySelectorAll('#queue-tbody tr');
    var visibleCount = 0;
    
    rows.forEach(function(row) {
        var show = true;
        
        if (type && row.dataset.type !== type) show = false;
        if (status && row.dataset.status !== status) show = false;
        if (risk && row.dataset.risk !== risk) show = false;
        if (account && row.dataset.account !== account) show = false;
        
        if (age) {
            var ageVal = row.dataset.age;
            if (age === '24h' && ageVal.includes('h') && parseInt(ageVal) >= 24) show = false;
            if (age === '1-3d' && (!ageVal.includes('h') || parseInt(ageVal) < 24 || parseInt(ageVal) > 72)) show = false;
            if (age === '3d+' && parseInt(ageVal) < 72) show = false;
        }
        
        if (admin) {
            var hasAdmin = row.querySelector('.admin-name') !== null;
            if (admin === 'me' && !hasAdmin) show = false;
            if (admin === 'unassigned' && hasAdmin) show = false;
        }
        
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    
    document.getElementById('showing-count').textContent = visibleCount;
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('FILTER_APPLIED', 'approval-queue', { type, status, risk, account, age, admin }, 'LOW');
    }
}

function clearFilters() {
    document.getElementById('filterType').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterRisk').value = '';
    document.getElementById('filterAccount').value = '';
    document.getElementById('filterAge').value = '';
    document.getElementById('filterAdmin').value = '';
    
    document.querySelectorAll('#queue-tbody tr').forEach(function(row) {
        row.style.display = '';
    });
    
    document.getElementById('showing-count').textContent = document.getElementById('total-count').textContent;
}

function setSort(sortType) {
    currentSort = sortType;
    
    document.querySelectorAll('.sort-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.sort === sortType);
    });
    
    var tbody = document.getElementById('queue-tbody');
    var rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort(function(a, b) {
        if (sortType === 'oldest') {
            return parseInt(b.dataset.age) - parseInt(a.dataset.age);
        } else if (sortType === 'sla') {
            return parseInt(a.dataset.sla) - parseInt(b.dataset.sla);
        } else if (sortType === 'risk') {
            var riskOrder = { high: 0, medium: 1, low: 2 };
            return riskOrder[a.dataset.risk] - riskOrder[b.dataset.risk];
        }
        return 0;
    });
    
    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
}

function viewDetails(id, type) {
    closeAllDropdowns();
    if (type === 'sender-id') {
        window.location.href = '/admin/assets/sender-ids/' + id;
    } else {
        window.location.href = '{{ route("admin.assets.rcs-agents") }}?highlight=' + id;
    }
}

function assignToMe(id) {
    closeAllDropdowns();
    alert('Assigned ' + id + ' to you');
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ASSIGN_TO_SELF', id, {}, 'MEDIUM');
    }
}

function reassign(id) {
    closeAllDropdowns();
    alert('Reassign modal for ' + id);
}

function transitionStatus(id, newStatus) {
    var row = document.querySelector('[data-id="' + id + '"]');
    if (!row) {
        console.error('[LIFECYCLE] Row not found:', id);
        return false;
    }
    
    var currentStatus = row.dataset.status;
    var requestType = row.dataset.type;
    
    if (!APPROVAL_LIFECYCLE.canTransition(currentStatus, newStatus, requestType)) {
        console.error('[LIFECYCLE] Invalid transition:', currentStatus, '->', newStatus);
        alert('Invalid status transition from ' + currentStatus + ' to ' + newStatus);
        return false;
    }
    
    var statusInfo = APPROVAL_LIFECYCLE.STATUS_DISPLAY[newStatus];
    row.dataset.status = newStatus;
    var statusCell = row.querySelector('.status-pill');
    statusCell.className = 'status-pill ' + newStatus;
    statusCell.innerHTML = '<i class="fas ' + statusInfo.icon + '"></i> ' + statusInfo.label;
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('STATUS_TRANSITION', id, {
            from: currentStatus,
            to: newStatus,
            requestType: requestType
        }, 'HIGH');
    }
    
    console.log('[LIFECYCLE] Transition complete:', id, currentStatus, '->', newStatus);
    return true;
}

function markInReview(id) {
    closeAllDropdowns();
    transitionStatus(id, 'in-review');
}

function startValidation(id) {
    closeAllDropdowns();
    transitionStatus(id, 'validation-in-progress');
}

function returnToCustomer(id) {
    closeAllDropdowns();
    if (confirm('Return this request to the customer for additional information?')) {
        transitionStatus(id, 'returned-to-customer');
    }
}

function quickApprove(id) {
    closeAllDropdowns();
    var row = document.querySelector('[data-id="' + id + '"]');
    if (!row) return;
    
    var currentStatus = row.dataset.status;
    var requestType = row.dataset.type;
    
    if (!APPROVAL_LIFECYCLE.canTransition(currentStatus, 'approved', requestType)) {
        alert('Cannot approve from status: ' + currentStatus + '. Must complete required steps first.');
        return;
    }
    
    if (confirm('Are you sure you want to approve this request?')) {
        transitionStatus(id, 'approved');
        
        if (requestType === 'rcs-agent') {
            setTimeout(function() {
                transitionStatus(id, 'provisioning-in-progress');
            }, 1000);
        } else {
            setTimeout(function() {
                transitionStatus(id, 'live');
                setTimeout(function() {
                    row.style.opacity = '0.5';
                    setTimeout(function() {
                        row.remove();
                        updateCounts();
                    }, 500);
                }, 1000);
            }, 1000);
        }
    }
}

function showRejectModal(id) {
    closeAllDropdowns();
    document.getElementById('reject-request-id').value = id;
    document.getElementById('rejection-template').value = '';
    document.getElementById('rejection-notes').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
    var id = document.getElementById('reject-request-id').value;
    var reason = document.getElementById('rejection-template').value;
    var notes = document.getElementById('rejection-notes').value;
    
    if (!reason) {
        alert('Please select a rejection reason');
        return;
    }
    
    var row = document.querySelector('[data-id="' + id + '"]');
    if (!row) return;
    
    var currentStatus = row.dataset.status;
    if (!APPROVAL_LIFECYCLE.canTransition(currentStatus, 'rejected', row.dataset.type)) {
        alert('Cannot reject from status: ' + currentStatus);
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        return;
    }
    
    transitionStatus(id, 'rejected');
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('REJECT', id, { 
            reason: reason, 
            notes: notes,
            fromStatus: currentStatus 
        }, 'HIGH');
    }
    
    setTimeout(function() {
        row.style.opacity = '0.5';
        setTimeout(function() {
            row.remove();
            updateCounts();
        }, 500);
    }, 1000);
    
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
}

function updateCounts() {
    var rows = document.querySelectorAll('#queue-tbody tr');
    var total = rows.length;
    var slaCritical = 0;
    var highRisk = 0;
    var assigned = 0;
    var awaitingAction = 0;
    
    rows.forEach(function(row) {
        if (parseInt(row.dataset.sla) <= 8) slaCritical++;
        if (row.dataset.risk === 'high') highRisk++;
        if (row.querySelector('.admin-name')) assigned++;
        if (APPROVAL_LIFECYCLE.AWAITING_ADMIN_ACTION.includes(row.dataset.status)) awaitingAction++;
    });
    
    document.getElementById('stat-awaiting').textContent = awaitingAction;
    document.getElementById('stat-sla-critical').textContent = slaCritical;
    document.getElementById('stat-high-risk').textContent = highRisk;
    document.getElementById('stat-assigned').textContent = assigned;
    document.getElementById('stat-total').textContent = total;
    document.getElementById('showing-count').textContent = total;
    document.getElementById('total-count').textContent = total;
}

function getActionMenuItems(id, type, status) {
    var items = [];
    
    items.push({ label: 'View Details', icon: 'fa-eye', action: "viewDetails('" + id + "', '" + type + "')" });
    
    var hasAdmin = document.querySelector('[data-id="' + id + '"] .admin-name');
    if (hasAdmin) {
        items.push({ label: 'Reassign', icon: 'fa-user-edit', action: "reassign('" + id + "')" });
    } else {
        items.push({ label: 'Assign to Me', icon: 'fa-user-plus', action: "assignToMe('" + id + "')" });
    }
    
    var transitions = APPROVAL_LIFECYCLE.getAvailableTransitions(status, type);
    
    if (transitions.length > 0) {
        items.push({ divider: true });
    }
    
    transitions.forEach(function(t) {
        var info = APPROVAL_LIFECYCLE.STATUS_DISPLAY[t];
        var actionClass = '';
        var actionFn = '';
        
        switch(t) {
            case 'in-review':
                actionClass = '';
                actionFn = "markInReview('" + id + "')";
                break;
            case 'validation-in-progress':
                actionClass = '';
                actionFn = "startValidation('" + id + "')";
                break;
            case 'returned-to-customer':
                actionClass = 'warn';
                actionFn = "returnToCustomer('" + id + "')";
                break;
            case 'approved':
                actionClass = 'approve';
                actionFn = "quickApprove('" + id + "')";
                break;
            case 'rejected':
                actionClass = 'reject';
                actionFn = "showRejectModal('" + id + "')";
                break;
            default:
                actionFn = "transitionStatus('" + id + "', '" + t + "')";
        }
        
        items.push({
            label: info.label,
            icon: info.icon,
            action: actionFn,
            className: actionClass
        });
    });
    
    return items;
}

function exportQueue() {
    alert('Exporting queue to CSV...');
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('EXPORT', 'approval-queue', { format: 'csv' }, 'MEDIUM');
    }
}
</script>
@endpush

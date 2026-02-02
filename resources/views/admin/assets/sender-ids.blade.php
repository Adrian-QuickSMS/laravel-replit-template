@extends('layouts.admin')

@section('title', 'Sender ID Approvals')

@push('styles')
<style>
.breadcrumb-item.active {
    color: #1e3a5f !important;
    font-weight: 500;
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.page-header h2 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.page-header p {
    margin: 0;
    color: #6c757d;
}
.table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: hidden;
}
.api-table {
    width: 100%;
    margin: 0;
    table-layout: fixed;
}
.api-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.api-table thead th:hover {
    background: #e9ecef;
}
.api-table thead th i.sort-icon {
    margin-left: 0.25rem;
    opacity: 0.5;
}
.api-table thead th.sorted i.sort-icon {
    opacity: 1;
    color: #1e3a5f;
}
.api-table tbody tr {
    border-bottom: 1px solid #e9ecef;
}
.api-table tbody tr:last-child {
    border-bottom: none;
}
.api-table tbody tr:hover {
    background: #f8f9fa;
}
.api-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    font-size: 0.85rem;
}
.filter-panel {
    background-color: rgba(30, 58, 95, 0.05);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
.multiselect-dropdown .dropdown-menu {
    max-height: 250px;
    overflow-y: auto;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.75rem;
}
.filter-chip .remove-chip {
    margin-left: 0.25rem;
    cursor: pointer;
    opacity: 0.7;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
}
.account-link {
    color: #1e3a5f;
    text-decoration: none;
    font-weight: 500;
}
.account-link:hover {
    text-decoration: underline;
}
.action-menu-btn {
    background: none;
    border: none;
    padding: 0.25rem 0.5rem;
    color: #6c757d;
    cursor: pointer;
}
.action-menu-btn:hover {
    color: #1e3a5f;
}
.use-case-text {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.8rem;
    color: #64748b;
}
.submitted-time {
    font-size: 0.8rem;
    color: #64748b;
}
.submitted-time .date {
    color: #1e293b;
    font-weight: 500;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Assets</a></li>
            <li class="breadcrumb-item active">Sender ID Approvals</li>
        </ol>
    </div>

    <div class="page-header">
        <div>
            <h2>Sender ID Approvals</h2>
            <p>Review and approve customer SenderID registration requests across all accounts</p>
        </div>
    </div>

    <div class="collapse mb-3" id="filtersPanel">
        <div class="filter-panel">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Account</label>
                    <select class="form-select form-select-sm" id="filterAccount">
                        <option value="">All Accounts</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Status</label>
                    <div class="dropdown multiselect-dropdown" data-filter="statuses">
                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                            <span class="dropdown-label">All Statuses</span>
                        </button>
                        <div class="dropdown-menu w-100 p-2">
                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Pending" id="statusPending"><label class="form-check-label small" for="statusPending">Pending</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Approved" id="statusApproved"><label class="form-check-label small" for="statusApproved">Approved</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Rejected" id="statusRejected"><label class="form-check-label small" for="statusRejected">Rejected</label></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Type</label>
                    <div class="dropdown multiselect-dropdown" data-filter="types">
                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                            <span class="dropdown-label">All Types</span>
                        </button>
                        <div class="dropdown-menu w-100 p-2">
                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Alphanumeric" id="typeAlphanumeric"><label class="form-check-label small" for="typeAlphanumeric">Alphanumeric</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Numeric" id="typeNumeric"><label class="form-check-label small" for="typeNumeric">Numeric</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Shortcode" id="typeShortcode"><label class="form-check-label small" for="typeShortcode">Shortcode</label></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Submitted From</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Submitted To</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateTo">
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm" id="btnApplyFilters" style="background: #1e3a5f; color: white;">
                        <i class="fas fa-check me-1"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                        <i class="fas fa-undo me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="approval-queue-stats mb-3">
        <div class="approval-stat-card pending active" data-status="pending" onclick="filterByTile('pending')" style="cursor: pointer;">
            <div class="stat-count" id="stat-pending">8</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="approval-stat-card approved" data-status="approved" onclick="filterByTile('approved')" style="cursor: pointer;">
            <div class="stat-count" id="stat-approved">1,847</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="approval-stat-card rejected" data-status="rejected" onclick="filterByTile('rejected')" style="cursor: pointer;">
            <div class="stat-count" id="stat-rejected">56</div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="approval-stat-card total" data-status="all" onclick="filterByTile('all')" style="cursor: pointer;">
            <div class="stat-count" id="stat-total">1,914</div>
            <div class="stat-label">Total</div>
        </div>
    </div>

    <div class="card mb-3" style="border: 1px solid #e0e6ed; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <div class="input-group" style="width: 320px;">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 ps-0" id="quickSearchInput" placeholder="Search by Sender ID or account...">
                    </div>
                    <div id="activeFiltersChips" class="d-flex flex-wrap gap-1"></div>
                </div>
                <button type="button" class="btn btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border: 1px solid #1e3a5f; color: #1e3a5f; background: transparent;">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>
    </div>

    <div class="approval-bulk-bar" style="display: none;">
        <span class="selected-count">0 items selected</span>
        <div class="bulk-actions">
            <button class="bulk-btn approve" onclick="bulkApprove()">
                <i class="fas fa-check me-1"></i> Approve All
            </button>
            <button class="bulk-btn reject" onclick="showBulkRejectModal()">
                <i class="fas fa-times me-1"></i> Reject All
            </button>
            <button class="bulk-btn cancel" onclick="clearSelection()">Cancel</button>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table api-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                        </th>
                        <th data-sort="senderId">Sender ID <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="account">Account <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="submitted">Submitted <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="useCase">Use Case <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="approvalQueueBody">
                    <tr data-item-id="SID-001" data-status="pending">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('SID-001')"></td>
                        <td>
                            <div class="approval-item-name">ALERTS24</div>
                            <div class="approval-item-id">SID-001</div>
                        </td>
                        <td><span class="senderid-type-badge alphanumeric">Alphanumeric</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">AC</div>
                                <div class="account-info">
                                    <div class="account-name">Acme Corporation</div>
                                    <div class="account-id">ACC-1234</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 20, 2026</span><br>
                                2 hours ago
                            </div>
                        </td>
                        <td><span class="use-case-text" title="Transactional alerts and notifications">Transactional alerts and notifications</span></td>
                        <td><span class="approval-status-badge pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="goToDetail('SID-001')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="SID-002" data-status="pending">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('SID-002')"></td>
                        <td>
                            <div class="approval-item-name">MYBANK</div>
                            <div class="approval-item-id">SID-002</div>
                        </td>
                        <td><span class="senderid-type-badge alphanumeric">Alphanumeric</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">FL</div>
                                <div class="account-info">
                                    <div class="account-name">Finance Ltd</div>
                                    <div class="account-id">ACC-5678</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 20, 2026</span><br>
                                4 hours ago
                            </div>
                        </td>
                        <td><span class="use-case-text" title="OTP and security codes">OTP and security codes</span></td>
                        <td><span class="approval-status-badge pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="goToDetail('SID-002')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="SID-003" data-status="pending">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('SID-003')"></td>
                        <td>
                            <div class="approval-item-name">PROMO</div>
                            <div class="approval-item-id">SID-003</div>
                        </td>
                        <td><span class="senderid-type-badge alphanumeric">Alphanumeric</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">RC</div>
                                <div class="account-info">
                                    <div class="account-name">RetailMax Group</div>
                                    <div class="account-id">ACC-4001</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 19, 2026</span><br>
                                1 day ago
                            </div>
                        </td>
                        <td><span class="use-case-text" title="Marketing campaigns and promotional offers">Marketing campaigns and promotional offers</span></td>
                        <td><span class="approval-status-badge pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="goToDetail('SID-003')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="SID-004" data-status="pending">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('SID-004')"></td>
                        <td>
                            <div class="approval-item-name">HEALTHNOW</div>
                            <div class="approval-item-id">SID-004</div>
                        </td>
                        <td><span class="senderid-type-badge alphanumeric">Alphanumeric</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">HP</div>
                                <div class="account-info">
                                    <div class="account-name">HealthPlus Care</div>
                                    <div class="account-id">ACC-4005</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 19, 2026</span><br>
                                1 day ago
                            </div>
                        </td>
                        <td><span class="use-case-text" title="Appointment reminders and health alerts">Appointment reminders and health alerts</span></td>
                        <td><span class="approval-status-badge pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="goToDetail('SID-004')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="SID-005" data-status="pending">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('SID-005')"></td>
                        <td>
                            <div class="approval-item-name">447700</div>
                            <div class="approval-item-id">SID-005</div>
                        </td>
                        <td><span class="senderid-type-badge numeric">Numeric</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">TS</div>
                                <div class="account-info">
                                    <div class="account-name">TechStartup Inc</div>
                                    <div class="account-id">ACC-4008</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 18, 2026</span><br>
                                2 days ago
                            </div>
                        </td>
                        <td><span class="use-case-text" title="Two-way SMS communications">Two-way SMS communications</span></td>
                        <td><span class="approval-status-badge pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="goToDetail('SID-005')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="SID-006" data-status="pending">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('SID-006')"></td>
                        <td>
                            <div class="approval-item-name">88099</div>
                            <div class="approval-item-id">SID-006</div>
                        </td>
                        <td><span class="senderid-type-badge shortcode">Shortcode</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">FD</div>
                                <div class="account-info">
                                    <div class="account-name">FoodDelivery Pro</div>
                                    <div class="account-id">ACC-4009</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 18, 2026</span><br>
                                2 days ago
                            </div>
                        </td>
                        <td><span class="use-case-text" title="Order confirmations and delivery updates">Order confirmations and delivery updates</span></td>
                        <td><span class="approval-status-badge pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="goToDetail('SID-006')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="SID-007" data-status="pending">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('SID-007')"></td>
                        <td>
                            <div class="approval-item-name">EDULEARN</div>
                            <div class="approval-item-id">SID-007</div>
                        </td>
                        <td><span class="senderid-type-badge alphanumeric">Alphanumeric</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">EA</div>
                                <div class="account-info">
                                    <div class="account-name">EduLearn Academy</div>
                                    <div class="account-id">ACC-4006</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 17, 2026</span><br>
                                3 days ago
                            </div>
                        </td>
                        <td><span class="use-case-text" title="Course notifications and schedule reminders">Course notifications and schedule reminders</span></td>
                        <td><span class="approval-status-badge pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="goToDetail('SID-007')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="SID-008" data-status="pending">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('SID-008')"></td>
                        <td>
                            <div class="approval-item-name">SECUREBK</div>
                            <div class="approval-item-id">SID-008</div>
                        </td>
                        <td><span class="senderid-type-badge alphanumeric">Alphanumeric</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">SB</div>
                                <div class="account-info">
                                    <div class="account-name">SecureBank Financial</div>
                                    <div class="account-id">ACC-4012</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 17, 2026</span><br>
                                3 days ago
                            </div>
                        </td>
                        <td><span class="use-case-text" title="Fraud alerts and transaction notifications">Fraud alerts and transaction notifications</span></td>
                        <td><span class="approval-status-badge pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="goToDetail('SID-008')">Review</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">Showing 1-8 of 8 pending items</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="approval-drawer-overlay"></div>
<div class="approval-drawer">
    <div class="approval-drawer-header">
        <h5><i class="fas fa-signature me-2"></i>Sender ID Details</h5>
        <button class="approval-drawer-close"><i class="fas fa-times"></i></button>
    </div>
    <div class="approval-drawer-body">
        <div class="asset-preview-card">
            <div class="preview-label">Sender ID</div>
            <div class="preview-value" id="drawerSenderId">ALERTS24</div>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Registration Details</div>
            <div class="approval-detail-row">
                <span class="label">Type</span>
                <span class="value" id="drawerType">Alphanumeric</span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Submitted</span>
                <span class="value" id="drawerSubmitted">Jan 20, 2026 at 10:34 AM</span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Request ID</span>
                <span class="value" id="drawerRequestId">SID-001</span>
            </div>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Account Information</div>
            <div class="approval-detail-row">
                <span class="label">Account Name</span>
                <span class="value" id="drawerAccountName">Acme Corporation</span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Account ID</span>
                <span class="value" id="drawerAccountId">ACC-1234</span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Account Status</span>
                <span class="value"><span class="admin-status-badge active">Live</span></span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Total SenderIDs</span>
                <span class="value" id="drawerTotalSenderIds">12</span>
            </div>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Use Case Declaration</div>
            <p class="text-muted" style="font-size: 0.85rem;" id="drawerUseCase">
                Transactional alerts and notifications for service status updates and system maintenance windows.
            </p>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Compliance Checklist</div>
            <ul class="compliance-checklist">
                <li class="pass"><i class="fas fa-check-circle"></i> Not a reserved keyword</li>
                <li class="pass"><i class="fas fa-check-circle"></i> Length within limits (3-11 chars)</li>
                <li class="pass"><i class="fas fa-check-circle"></i> No special characters</li>
                <li class="pass"><i class="fas fa-check-circle"></i> Account in good standing</li>
                <li class="pending"><i class="fas fa-question-circle"></i> Trademark verification pending</li>
            </ul>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Approval History</div>
            <div class="approval-timeline">
                <div class="approval-timeline-item submitted">
                    <div class="approval-timeline-event">Registration Submitted</div>
                    <div class="approval-timeline-meta">Jan 20, 2026 at 10:34 AM by user@acme.com</div>
                </div>
            </div>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Admin Notes</div>
            <textarea class="approval-notes-input" id="drawerNotes" placeholder="Add notes (optional)..."></textarea>
        </div>
    </div>
    <div class="approval-drawer-footer">
        <button class="btn btn-outline-secondary" onclick="closeDrawer()">Cancel</button>
        <button class="btn btn-outline-danger" onclick="showRejectModalFromDrawer()">
            <i class="fas fa-times me-1"></i> Reject
        </button>
        <button class="btn btn-success" onclick="approveFromDrawer()">
            <i class="fas fa-check me-1"></i> Approve
        </button>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-2"></i>Reject Sender ID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Rejecting: <strong id="rejectItemName">ALERTS24</strong></p>
                
                <div class="mb-3">
                    <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="approval-reject-reason" id="rejectReason" placeholder="Provide a reason for rejection (minimum 10 characters)..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted" style="font-size: 0.8rem;">Quick Templates</label>
                    <div class="approval-reject-templates">
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Trademark violation</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Misleading sender name</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Reserved keyword</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Invalid format</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Insufficient documentation</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Duplicate request</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="fas fa-times me-1"></i> Reject Sender ID
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Dynamic SenderID Access Modal --}}
<div class="modal fade" id="dynamicSenderIdModal" tabindex="-1" aria-labelledby="dynamicSenderIdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--admin-primary, #1e3a5f) 0%, #2d5a87 100%); color: #fff;">
                <h5 class="modal-title" id="dynamicSenderIdModalLabel">
                    <i class="fas fa-unlock-alt me-2"></i>Dynamic SenderID Access
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">Grant an account permission to use Dynamic SenderIDs. This is an admin override and is fully audited.</p>
                
                <div class="mb-3">
                    <label class="form-label">Account <span class="text-danger">*</span></label>
                    <select class="form-select" id="dynamicAccountSelect" onchange="onDynamicAccountChange()">
                        <option value="">Select an account...</option>
                        <option value="ACC-1234">Acme Corporation (ACC-1234)</option>
                        <option value="ACC-5678">Finance Ltd (ACC-5678)</option>
                        <option value="ACC-4001">RetailMax Group (ACC-4001)</option>
                        <option value="ACC-4005">HealthPlus Care (ACC-4005)</option>
                        <option value="ACC-4008">TechStartup Inc (ACC-4008)</option>
                        <option value="ACC-4009">FoodDelivery Pro (ACC-4009)</option>
                        <option value="ACC-4006">EduLearn Academy (ACC-4006)</option>
                    </select>
                    <div class="invalid-feedback" id="dynamicAccountError">Please select an account.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Scope <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="dynamicScope" id="scopeAll" value="ALL_SUBACCOUNTS" checked onchange="onScopeChange()">
                        <label class="form-check-label" for="scopeAll">All sub-accounts</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="dynamicScope" id="scopeSpecific" value="SPECIFIC_SUBACCOUNTS" onchange="onScopeChange()">
                        <label class="form-check-label" for="scopeSpecific">Specific sub-account(s)</label>
                    </div>
                </div>

                <div class="mb-3" id="subAccountsContainer" style="display: none;">
                    <label class="form-label">Sub-accounts <span class="text-danger">*</span></label>
                    <select class="form-select" id="dynamicSubAccountSelect" multiple size="4">
                        <option value="SUB-001">Marketing Dept</option>
                        <option value="SUB-002">Sales Team</option>
                        <option value="SUB-003">Support Division</option>
                        <option value="SUB-004">Operations</option>
                    </select>
                    <small class="text-muted">Hold Ctrl/Cmd to select multiple sub-accounts</small>
                    <div class="invalid-feedback" id="dynamicSubAccountError">Please select at least one sub-account.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Internal Note <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="dynamicNote" rows="3" placeholder="Reason for granting dynamic SenderID access (optional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="grantDynamicAccess()" style="background: var(--admin-primary, #1e3a5f); border-color: var(--admin-primary, #1e3a5f);">
                    <i class="fas fa-check me-1"></i> Grant Access
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[SenderID Approvals] Initializing...');
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.ApprovalFramework.init('SENDERID');
    }

    AdminControlPlane.logAdminAction('PAGE_VIEW', 'sender-id-approvals', {
        module: 'messaging_assets'
    });
});

var currentRejectItem = null;
var selectedItems = [];

function applyFilters() {
    var status = document.getElementById('filterStatus').value;
    var type = document.getElementById('filterType').value;
    var account = document.getElementById('filterAccount').value;
    var search = document.getElementById('searchInput').value.toLowerCase();

    document.querySelectorAll('.api-table tbody tr').forEach(function(row) {
        var show = true;

        if (status !== 'all' && row.dataset.status !== status) {
            show = false;
        }

        if (type && !row.querySelector('.senderid-type-badge').classList.contains(type)) {
            show = false;
        }

        if (account && row.querySelector('.account-id').textContent !== account) {
            show = false;
        }

        if (search) {
            var name = row.querySelector('.approval-item-name').textContent.toLowerCase();
            if (name.indexOf(search) === -1) {
                show = false;
            }
        }

        row.style.display = show ? '' : 'none';
    });

    updateVisibleCount();
    
    AdminControlPlane.logAdminAction('FILTER_APPLIED', 'sender-id-queue', {
        status: status,
        type: type,
        account: account,
        search: search
    });
}

function clearFilters() {
    document.getElementById('filterStatus').value = 'pending';
    document.getElementById('filterType').value = '';
    document.getElementById('filterAccount').value = '';
    document.getElementById('searchInput').value = '';
    applyFilters();
}

function updateVisibleCount() {
    var visible = document.querySelectorAll('.api-table tbody tr:not([style*="display: none"])').length;
    var total = document.querySelectorAll('.api-table tbody tr').length;
    document.querySelector('.card-footer .text-muted').textContent = 'Showing 1-' + visible + ' of ' + visible + ' items';
}

function toggleSelectAll() {
    var checked = document.getElementById('selectAllCheckbox').checked;
    selectedItems = [];
    
    document.querySelectorAll('.api-table tbody tr:not([style*="display: none"]) .item-checkbox').forEach(function(checkbox) {
        checkbox.checked = checked;
        if (checked) {
            selectedItems.push(checkbox.closest('tr').dataset.itemId);
        }
    });

    updateBulkBar();
}

function toggleItemSelect(itemId) {
    var idx = selectedItems.indexOf(itemId);
    if (idx > -1) {
        selectedItems.splice(idx, 1);
    } else {
        selectedItems.push(itemId);
    }
    updateBulkBar();
}

function updateBulkBar() {
    var bar = document.querySelector('.approval-bulk-bar');
    if (selectedItems.length > 0) {
        bar.style.display = 'flex';
        bar.querySelector('.selected-count').textContent = selectedItems.length + ' items selected';
    } else {
        bar.style.display = 'none';
    }
}

function clearSelection() {
    selectedItems = [];
    document.querySelectorAll('.item-checkbox').forEach(function(cb) { cb.checked = false; });
    document.getElementById('selectAllCheckbox').checked = false;
    updateBulkBar();
}

function filterByTile(status) {
    // Update the dropdown
    var dropdown = document.getElementById('filterStatus');
    if (status === 'all') {
        dropdown.value = 'all';
    } else {
        dropdown.value = status;
    }
    
    // Update active tile
    document.querySelectorAll('.approval-stat-card').forEach(function(card) {
        card.classList.remove('active');
    });
    var activeCard = document.querySelector('.approval-stat-card[data-status="' + status + '"]');
    if (activeCard) {
        activeCard.classList.add('active');
    }
    
    // Apply filters
    applyFilters();
}

function goToDetail(itemId) {
    window.location.href = '/admin/assets/sender-ids/' + itemId;
}

function quickApprove(itemId) {
    if (confirm('Approve Sender ID ' + itemId + '?')) {
        var result = AdminControlPlane.approveItem('SENDERID', itemId, '');
        if (result.success) {
            updateRowStatus(itemId, 'approved');
            showToast('Sender ID approved successfully', 'success');
        }
    }
}

function showRejectModal(itemId) {
    currentRejectItem = itemId;
    var row = document.querySelector('[data-item-id="' + itemId + '"]');
    var name = row.querySelector('.approval-item-name').textContent;
    document.getElementById('rejectItemName').textContent = name;
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showRejectModalFromDrawer() {
    var itemId = document.getElementById('drawerRequestId').textContent;
    showRejectModal(itemId);
}

function useRejectTemplate(el) {
    var reason = el.textContent;
    document.getElementById('rejectReason').value = reason + ': ';
    document.getElementById('rejectReason').focus();
}

function confirmReject() {
    var reason = document.getElementById('rejectReason').value.trim();
    if (reason.length < 10) {
        alert('Please provide a rejection reason (minimum 10 characters)');
        return;
    }

    var result = AdminControlPlane.rejectItem('SENDERID', currentRejectItem, reason);
    if (result.success) {
        updateRowStatus(currentRejectItem, 'rejected');
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        closeDrawer();
        showToast('Sender ID rejected', 'warning');
    } else {
        alert(result.error);
    }
}

function approveFromDrawer() {
    var itemId = document.getElementById('drawerRequestId').textContent;
    var notes = document.getElementById('drawerNotes').value;
    
    var result = AdminControlPlane.approveItem('SENDERID', itemId, notes);
    if (result.success) {
        updateRowStatus(itemId, 'approved');
        closeDrawer();
        showToast('Sender ID approved successfully', 'success');
    }
}

function updateRowStatus(itemId, newStatus) {
    var row = document.querySelector('[data-item-id="' + itemId + '"]');
    if (row) {
        row.dataset.status = newStatus;
        var badge = row.querySelector('.approval-status-badge');
        badge.className = 'approval-status-badge ' + newStatus;
        
        var icons = {
            approved: '<i class="fas fa-check-circle"></i>',
            rejected: '<i class="fas fa-times-circle"></i>',
            pending: '<i class="fas fa-clock"></i>'
        };
        var labels = {
            approved: 'Approved',
            rejected: 'Rejected',
            pending: 'Pending'
        };
        badge.innerHTML = icons[newStatus] + ' ' + labels[newStatus];

        var actions = row.querySelector('.approval-quick-actions');
        if (newStatus === 'approved' || newStatus === 'rejected') {
            actions.innerHTML = '<span class="text-muted small">Completed</span>';
        }
    }
    updateStatCounts();
}

function updateStatCounts() {
    var counts = { pending: 0, approved: 0, rejected: 0 };
    document.querySelectorAll('.api-table tbody tr').forEach(function(row) {
        var status = row.dataset.status;
        if (counts.hasOwnProperty(status)) {
            counts[status]++;
        }
    });
    
    document.getElementById('stat-pending').textContent = counts.pending;
    document.getElementById('stat-approved').textContent = (1839 + counts.approved).toLocaleString();
    document.getElementById('stat-rejected').textContent = (48 + counts.rejected);
}

function closeDrawer() {
    document.querySelector('.approval-drawer').classList.remove('open');
    document.querySelector('.approval-drawer-overlay').classList.remove('open');
    document.body.style.overflow = '';
}

function bulkApprove() {
    if (confirm('Approve ' + selectedItems.length + ' items?')) {
        selectedItems.forEach(function(id) {
            AdminControlPlane.approveItem('SENDERID', id, 'Bulk approval');
            updateRowStatus(id, 'approved');
        });
        clearSelection();
        showToast(selectedItems.length + ' items approved', 'success');
    }
}

function showBulkRejectModal() {
    currentRejectItem = 'BULK';
    document.getElementById('rejectItemName').textContent = selectedItems.length + ' items';
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function exportQueue(format) {
    AdminControlPlane.logAdminAction('EXPORT_INITIATED', 'sender-id-queue', {
        format: format,
        count: document.querySelectorAll('.api-table tbody tr').length
    });
    showToast('Export started...', 'info');
}

function showToast(message, type) {
    console.log('[Toast]', type, message);
    
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(toastContainer);
    }
    
    var colors = { success: '#10b981', error: '#ef4444', info: '#3b82f6', warning: '#f59e0b' };
    var toast = document.createElement('div');
    toast.style.cssText = 'background: ' + (colors[type] || colors.info) + '; color: #fff; padding: 12px 20px; border-radius: 6px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 0.9rem;';
    toast.textContent = message;
    toastContainer.appendChild(toast);
    
    setTimeout(function() { toast.remove(); }, 4000);
}

function showDynamicSenderIdModal() {
    resetDynamicModal();
    new bootstrap.Modal(document.getElementById('dynamicSenderIdModal')).show();
}

function resetDynamicModal() {
    document.getElementById('dynamicAccountSelect').value = '';
    document.getElementById('dynamicAccountSelect').classList.remove('is-invalid');
    document.getElementById('scopeAll').checked = true;
    document.getElementById('subAccountsContainer').style.display = 'none';
    document.getElementById('dynamicSubAccountSelect').selectedIndex = -1;
    document.getElementById('dynamicSubAccountSelect').classList.remove('is-invalid');
    document.getElementById('dynamicNote').value = '';
}

function onDynamicAccountChange() {
    var accountId = document.getElementById('dynamicAccountSelect').value;
    document.getElementById('dynamicAccountSelect').classList.remove('is-invalid');
    
    if (accountId) {
        document.getElementById('dynamicSubAccountSelect').selectedIndex = -1;
    }
}

function onScopeChange() {
    var scope = document.querySelector('input[name="dynamicScope"]:checked').value;
    var container = document.getElementById('subAccountsContainer');
    
    if (scope === 'SPECIFIC_SUBACCOUNTS') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        document.getElementById('dynamicSubAccountSelect').classList.remove('is-invalid');
    }
}

function grantDynamicAccess() {
    var accountId = document.getElementById('dynamicAccountSelect').value;
    var scope = document.querySelector('input[name="dynamicScope"]:checked').value;
    var subAccountSelect = document.getElementById('dynamicSubAccountSelect');
    var subAccountIds = Array.from(subAccountSelect.selectedOptions).map(function(opt) { return opt.value; });
    var note = document.getElementById('dynamicNote').value.trim();
    
    var valid = true;
    
    if (!accountId) {
        document.getElementById('dynamicAccountSelect').classList.add('is-invalid');
        valid = false;
    }
    
    if (scope === 'SPECIFIC_SUBACCOUNTS' && subAccountIds.length === 0) {
        subAccountSelect.classList.add('is-invalid');
        valid = false;
    }
    
    if (!valid) return;
    
    var payload = {
        scope: scope,
        subAccountIds: scope === 'SPECIFIC_SUBACCOUNTS' ? subAccountIds : [],
        note: note
    };
    
    DynamicSenderIdService.grantAccess(accountId, payload)
        .then(function(response) {
            if (response.success) {
                AdminControlPlane.logAdminAction('DYNAMIC_SENDERID_ACCESS_GRANTED', accountId, {
                    scope: scope,
                    subAccountIds: subAccountIds,
                    note: note ? note.substring(0, 100) : null
                }, 'HIGH');
                
                showToast('Dynamic SenderID access granted.', 'success');
                bootstrap.Modal.getInstance(document.getElementById('dynamicSenderIdModal')).hide();
            } else {
                showToast('Could not grant access. Please try again.', 'error');
            }
        })
        .catch(function(error) {
            showToast('Could not grant access. Please try again.', 'error');
        });
}

var DynamicSenderIdService = {
    grantAccess: function(accountId, payload) {
        console.log('[DynamicSenderIdService] POST /admin/accounts/' + accountId + '/dynamic-senderid', payload);
        
        return new Promise(function(resolve) {
            setTimeout(function() {
                resolve({ success: true, message: 'Access granted' });
            }, 500);
        });
    }
};
</script>
@endpush

@extends('layouts.admin')

@section('title', 'RCS Agent Approvals')

@push('styles')
<style>
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
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(30, 58, 95, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: #1e3a5f;
}
.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.agent-name-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.agent-logo {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 600;
}

.agent-logo.has-image {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
}

.agent-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 7px;
}

.agent-desc {
    font-size: 0.75rem;
    color: #94a3b8;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.submitted-time {
    font-size: 0.8rem;
    color: #64748b;
}

.submitted-time .date {
    color: #1e293b;
    font-weight: 500;
}

.carrier-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
    font-size: 0.65rem;
    font-weight: 500;
    background: rgba(30, 58, 95, 0.08);
    color: var(--admin-primary);
}

.branding-preview {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.branding-preview .logo-preview {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.branding-preview .logo-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.branding-preview .hero-preview {
    width: 160px;
    height: 80px;
    border-radius: 8px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.branding-preview .hero-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
    display: inline-block;
    vertical-align: middle;
    margin-right: 0.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Assets</a></li>
            <li class="breadcrumb-item active">RCS Agent Approvals</li>
        </ol>
    </div>

    <div class="page-header">
        <div>
            <h2>RCS Agent Approvals</h2>
            <p>Review and approve RCS Business Messaging agent registrations across all accounts</p>
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
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Submitted" id="statusSubmitted"><label class="form-check-label small" for="statusSubmitted">Submitted</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="In Review" id="statusInReview"><label class="form-check-label small" for="statusInReview">In Review</label></div>
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
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Conversational" id="typeConversational"><label class="form-check-label small" for="typeConversational">Conversational</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Promotional" id="typePromotional"><label class="form-check-label small" for="typePromotional">Promotional</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Transactional" id="typeTransactional"><label class="form-check-label small" for="typeTransactional">Transactional</label></div>
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
        <div class="approval-stat-card pending" data-status="submitted">
            <div class="stat-count" id="stat-submitted">4</div>
            <div class="stat-label">Submitted</div>
        </div>
        <div class="approval-stat-card in-review" data-status="in-review">
            <div class="stat-count" id="stat-in-review">4</div>
            <div class="stat-label">In Review</div>
        </div>
        <div class="approval-stat-card approved" data-status="approved">
            <div class="stat-count" id="stat-approved">226</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="approval-stat-card rejected" data-status="rejected">
            <div class="stat-count" id="stat-rejected">4</div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="approval-stat-card total" data-status="all">
            <div class="stat-count" id="stat-total">254</div>
            <div class="stat-label">Total</div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
            <div class="input-group" style="width: 320px;">
                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="quickSearchInput" placeholder="Search by agent name or account...">
            </div>
            <div id="activeFiltersChips" class="d-flex flex-wrap gap-1"></div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border-color: #1e3a5f; color: #1e3a5f;">
            <i class="fas fa-filter me-1"></i> Filters
        </button>
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
                        <th data-sort="name">Agent Name <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="account">Account <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="submitted">Submitted <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="approvalQueueBody">
                    <tr data-item-id="RCS-001" data-status="submitted" data-type="conversational">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-001')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">Acme Support Bot</div>
                                    <div class="agent-desc">Customer support and FAQ assistance</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge conversational">Conversational</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">Acme Corporation</div>
                                    <div class="account-id">ACC-1234</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 20, 2026</span><br>
                                3 hours ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="markInReview('RCS-001')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-002" data-status="in-review" data-type="transactional">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-002')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">Finance Alerts</div>
                                    <div class="agent-desc">Transaction alerts and security notifications</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge transactional">Transactional</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">Finance Ltd</div>
                                    <div class="account-id">ACC-5678</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 19, 2026</span><br>
                                1 day ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge in-review"><i class="fas fa-search"></i> In Review</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <span class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-clock me-1"></i>Awaiting RCS Provider</span>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-003" data-status="submitted" data-type="promotional">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-003')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">RetailMax Offers</div>
                                    <div class="agent-desc">Promotional campaigns and special offers</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge promotional">Promotional</span></td>
                        <td>
                            <div class="approval-item-account">
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
                        <td><span class="approval-status-badge submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="markInReview('RCS-003')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-004" data-status="in-review" data-type="conversational">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-004')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">HealthPlus Assistant</div>
                                    <div class="agent-desc">Appointment booking and health reminders</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge conversational">Conversational</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">HealthPlus Care</div>
                                    <div class="account-id">ACC-4005</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 18, 2026</span><br>
                                2 days ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge in-review"><i class="fas fa-search"></i> In Review</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <span class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-clock me-1"></i>Awaiting RCS Provider</span>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-005" data-status="submitted" data-type="transactional">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-005')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">FoodDelivery Updates</div>
                                    <div class="agent-desc">Order tracking and delivery notifications</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge transactional">Transactional</span></td>
                        <td>
                            <div class="approval-item-account">
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
                        <td><span class="approval-status-badge submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="markInReview('RCS-005')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-006" data-status="in-review" data-type="promotional">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-006')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">TravelWorld Deals</div>
                                    <div class="agent-desc">Travel packages and holiday offers</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge promotional">Promotional</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">TravelWorld Agency</div>
                                    <div class="account-id">ACC-4011</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 17, 2026</span><br>
                                3 days ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge in-review"><i class="fas fa-search"></i> In Review</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <span class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-clock me-1"></i>Awaiting RCS Provider</span>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-007" data-status="submitted" data-type="conversational">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-007')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">EduLearn Helper</div>
                                    <div class="agent-desc">Course info and student support</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge conversational">Conversational</span></td>
                        <td>
                            <div class="approval-item-account">
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
                        <td><span class="approval-status-badge submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="markInReview('RCS-007')">Review</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-008" data-status="submitted" data-type="transactional">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-008')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">SecureBank Notify</div>
                                    <div class="agent-desc">Security alerts and account notifications</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge transactional">Transactional</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">SecureBank Financial</div>
                                    <div class="account-id">ACC-4012</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 16, 2026</span><br>
                                4 days ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="markInReview('RCS-008')">Review</button>
                            </div>
                        </td>
                    </tr>
                    {{-- Sample Approved Agents (hidden by default with Awaiting Action filter) --}}
                    <tr data-item-id="RCS-101" data-status="approved" data-type="conversational" style="display: none;">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-101')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">ShopRight Assistant</div>
                                    <div class="agent-desc">E-commerce customer support</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge conversational">Conversational</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">ShopRight Ltd</div>
                                    <div class="account-id">ACC-5001</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 10, 2026</span><br>
                                10 days ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge approved"><i class="fas fa-check-circle"></i> Approved</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <span class="text-muted small">Completed</span>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-102" data-status="approved" data-type="transactional" style="display: none;">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-102')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">PayQuick Alerts</div>
                                    <div class="agent-desc">Payment notifications and receipts</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge transactional">Transactional</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">PayQuick Services</div>
                                    <div class="account-id">ACC-5002</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 8, 2026</span><br>
                                12 days ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge approved"><i class="fas fa-check-circle"></i> Approved</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <span class="text-muted small">Completed</span>
                            </div>
                        </td>
                    </tr>
                    {{-- Sample Rejected Agents (hidden by default with Awaiting Action filter) --}}
                    <tr data-item-id="RCS-201" data-status="rejected" data-type="promotional" style="display: none;">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-201')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">SpamPromo Bot</div>
                                    <div class="agent-desc">Mass promotional messaging</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge promotional">Promotional</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">Unknown Corp</div>
                                    <div class="account-id">ACC-9001</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 5, 2026</span><br>
                                15 days ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge rejected"><i class="fas fa-times-circle"></i> Rejected</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <span class="text-muted small">Completed</span>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-202" data-status="rejected" data-type="conversational" style="display: none;">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-202')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div>
                                    <div class="approval-item-name">FakeBank Helper</div>
                                    <div class="agent-desc">Unverified banking agent</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge conversational">Conversational</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-info">
                                    <div class="account-name">Suspicious LLC</div>
                                    <div class="account-id">ACC-9002</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="submitted-time">
                                <span class="date">Jan 3, 2026</span><br>
                                17 days ago
                            </div>
                        </td>
                        <td><span class="approval-status-badge rejected"><i class="fas fa-times-circle"></i> Rejected</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <span class="text-muted small">Completed</span>
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
        <h5><i class="fas fa-robot me-2"></i>RCS Agent Details</h5>
        <button class="approval-drawer-close"><i class="fas fa-times"></i></button>
    </div>
    <div class="approval-drawer-body">
        <div class="asset-preview-card">
            <div class="preview-label">Agent Name</div>
            <div class="preview-value" id="drawerAgentName">Acme Support Bot</div>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Agent Configuration</div>
            <div class="approval-detail-row">
                <span class="label">Agent Type</span>
                <span class="value" id="drawerAgentType">Conversational</span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Description</span>
                <span class="value" id="drawerDescription">Customer support and FAQ assistance</span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Request ID</span>
                <span class="value" id="drawerRequestId">RCS-001</span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Submitted</span>
                <span class="value" id="drawerSubmitted">Jan 20, 2026 at 9:15 AM</span>
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
                <span class="label">Existing RCS Agents</span>
                <span class="value" id="drawerExistingAgents">3</span>
            </div>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Branding Assets</div>
            <div class="branding-preview mb-3">
                <div>
                    <div class="text-muted mb-1" style="font-size: 0.7rem;">Logo</div>
                    <div class="logo-preview">
                        <i class="fas fa-image text-muted" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div>
                    <div class="text-muted mb-1" style="font-size: 0.7rem;">Hero Image</div>
                    <div class="hero-preview">
                        <i class="fas fa-image text-muted" style="font-size: 1.5rem; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);"></i>
                    </div>
                </div>
            </div>
            <div class="approval-detail-row">
                <span class="label">Primary Color</span>
                <span class="value"><span class="color-swatch" style="background: #667eea;"></span>#667EEA</span>
            </div>
            <div class="approval-detail-row">
                <span class="label">Secondary Color</span>
                <span class="value"><span class="color-swatch" style="background: #764ba2;"></span>#764BA2</span>
            </div>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Target Carriers</div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="carrier-badge">EE</span>
                <span class="carrier-badge">Vodafone</span>
                <span class="carrier-badge">O2</span>
            </div>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Compliance Checklist</div>
            <ul class="compliance-checklist">
                <li class="pass"><i class="fas fa-check-circle"></i> Logo meets quality requirements (224x224px)</li>
                <li class="pass"><i class="fas fa-check-circle"></i> Hero image provided (1440x448px)</li>
                <li class="pass"><i class="fas fa-check-circle"></i> Privacy policy URL valid</li>
                <li class="pass"><i class="fas fa-check-circle"></i> Terms of service URL valid</li>
                <li class="pass"><i class="fas fa-check-circle"></i> Business verified with Google</li>
                <li class="pending"><i class="fas fa-question-circle"></i> Carrier approval pending</li>
            </ul>
        </div>

        <div class="approval-detail-section">
            <div class="approval-detail-section-title">Approval History</div>
            <div class="approval-timeline">
                <div class="approval-timeline-item submitted">
                    <div class="approval-timeline-event">Agent Registration Submitted</div>
                    <div class="approval-timeline-meta">Jan 20, 2026 at 9:15 AM by user@acme.com</div>
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
                <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-2"></i>Reject RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Rejecting: <strong id="rejectItemName">Acme Support Bot</strong></p>
                
                <div class="mb-3">
                    <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="approval-reject-reason" id="rejectReason" placeholder="Provide a reason for rejection (minimum 10 characters)..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted" style="font-size: 0.8rem;">Quick Templates</label>
                    <div class="approval-reject-templates">
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Logo quality insufficient</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Business verification failed</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Prohibited content</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Missing branding assets</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Invalid privacy policy</span>
                        <span class="approval-reject-template" onclick="useRejectTemplate(this)">Use case not supported</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="fas fa-times me-1"></i> Reject Agent
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[RCS Agent Approvals] Initializing...');
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.ApprovalFramework.init('RCS_AGENT');
    }

    AdminControlPlane.logAdminAction('PAGE_VIEW', 'rcs-agent-approvals', {
        module: 'messaging_assets'
    });

    initStatCardClicks();
    applyFilters();
});

function initStatCardClicks() {
    document.querySelectorAll('.approval-stat-card').forEach(function(card) {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function() {
            var status = this.dataset.status;
            
            document.querySelectorAll('.approval-stat-card').forEach(function(c) {
                c.classList.remove('active');
            });
            this.classList.add('active');
            
            var filterSelect = document.getElementById('filterStatus');
            if (status === 'all') {
                filterSelect.value = 'all';
            } else if (status === 'submitted') {
                filterSelect.value = 'submitted';
            } else if (status === 'in-review') {
                filterSelect.value = 'in-review';
            } else if (status === 'approved') {
                filterSelect.value = 'approved';
            } else if (status === 'rejected') {
                filterSelect.value = 'rejected';
            }
            
            applyFilters();
        });
    });
}

var currentRejectItem = null;
var selectedItems = [];

function applyFilters() {
    var status = document.getElementById('filterStatus').value;
    var type = document.getElementById('filterType').value;
    var account = document.getElementById('filterAccount').value;
    var search = document.getElementById('searchInput').value.toLowerCase();

    document.querySelectorAll('.api-table tbody tr').forEach(function(row) {
        var show = true;
        var rowStatus = row.dataset.status;

        if (status === 'awaiting-action') {
            if (rowStatus !== 'submitted' && rowStatus !== 'in-review') {
                show = false;
            }
        } else if (status !== 'all' && rowStatus !== status) {
            show = false;
        }

        if (type && row.dataset.type !== type) {
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
    
    AdminControlPlane.logAdminAction('FILTER_APPLIED', 'rcs-agent-queue', {
        status: status,
        type: type,
        account: account,
        search: search
    });
}

function clearFilters() {
    document.getElementById('filterStatus').value = 'awaiting-action';
    document.getElementById('filterType').value = '';
    document.getElementById('filterAccount').value = '';
    document.getElementById('searchInput').value = '';
    applyFilters();
}

function updateVisibleCount() {
    var visible = document.querySelectorAll('.api-table tbody tr:not([style*="display: none"])').length;
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

function markInReview(itemId) {
    AdminControlPlane.ApprovalFramework.markInReview(itemId, AdminControlPlane.getCurrentAdmin().email);
    window.location.href = '/admin/assets/rcs-agents/' + itemId;
}

function quickApprove(itemId) {
    if (confirm('Approve RCS Agent ' + itemId + '?')) {
        var result = AdminControlPlane.approveItem('RCS_AGENT', itemId, '');
        if (result.success) {
            updateRowStatus(itemId, 'approved');
            showToast('RCS Agent approved successfully', 'success');
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

    var result = AdminControlPlane.rejectItem('RCS_AGENT', currentRejectItem, reason);
    if (result.success) {
        updateRowStatus(currentRejectItem, 'rejected');
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        closeDrawer();
        showToast('RCS Agent rejected', 'warning');
    } else {
        alert(result.error);
    }
}

function approveFromDrawer() {
    var itemId = document.getElementById('drawerRequestId').textContent;
    var notes = document.getElementById('drawerNotes').value;
    
    var result = AdminControlPlane.approveItem('RCS_AGENT', itemId, notes);
    if (result.success) {
        updateRowStatus(itemId, 'approved');
        closeDrawer();
        showToast('RCS Agent approved successfully', 'success');
    }
}

function updateRowStatus(itemId, newStatus) {
    var row = document.querySelector('[data-item-id="' + itemId + '"]');
    if (row) {
        row.dataset.status = newStatus;
        var badge = row.querySelector('.approval-status-badge');
        badge.className = 'approval-status-badge ' + newStatus;
        
        var icons = {
            'approved': '<i class="fas fa-check-circle"></i>',
            'rejected': '<i class="fas fa-times-circle"></i>',
            'submitted': '<i class="fas fa-paper-plane"></i>',
            'in-review': '<i class="fas fa-search"></i>'
        };
        var labels = {
            'approved': 'Approved',
            'rejected': 'Rejected',
            'submitted': 'Submitted',
            'in-review': 'In Review'
        };
        badge.innerHTML = icons[newStatus] + ' ' + labels[newStatus];

        var actions = row.querySelector('.approval-quick-actions');
        if (newStatus === 'approved' || newStatus === 'rejected') {
            actions.innerHTML = '<span class="text-muted small">Completed</span>';
        } else if (newStatus === 'in-review') {
            actions.innerHTML = '<button class="approval-action-btn approve" onclick="quickApprove(\'' + itemId + '\')">Approve</button>' +
                              '<button class="approval-action-btn reject" onclick="showRejectModal(\'' + itemId + '\')">Reject</button>';
        }
    }
    updateStatCounts();
}

function updateStatCounts() {
    var counts = { submitted: 0, 'in-review': 0, approved: 0, rejected: 0 };
    document.querySelectorAll('.api-table tbody tr').forEach(function(row) {
        var status = row.dataset.status;
        if (counts.hasOwnProperty(status)) {
            counts[status]++;
        }
    });
    
    document.getElementById('stat-submitted').textContent = counts.submitted;
    document.getElementById('stat-in-review').textContent = counts['in-review'];
    document.getElementById('stat-approved').textContent = (226 + counts.approved);
    document.getElementById('stat-rejected').textContent = (4 + counts.rejected);
}

function closeDrawer() {
    document.querySelector('.approval-drawer').classList.remove('open');
    document.querySelector('.approval-drawer-overlay').classList.remove('open');
    document.body.style.overflow = '';
}

function bulkApprove() {
    if (confirm('Approve ' + selectedItems.length + ' items?')) {
        selectedItems.forEach(function(id) {
            AdminControlPlane.approveItem('RCS_AGENT', id, 'Bulk approval');
            updateRowStatus(id, 'approved');
        });
        clearSelection();
        showToast(selectedItems.length + ' agents approved', 'success');
    }
}

function showBulkRejectModal() {
    currentRejectItem = 'BULK';
    document.getElementById('rejectItemName').textContent = selectedItems.length + ' items';
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function exportQueue(format) {
    AdminControlPlane.logAdminAction('EXPORT_INITIATED', 'rcs-agent-queue', {
        format: format,
        count: document.querySelectorAll('.api-table tbody tr').length
    });
    showToast('Export started...', 'info');
}

function showToast(message, type) {
    console.log('[Toast]', type, message);
}
</script>
@endpush

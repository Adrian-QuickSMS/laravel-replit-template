@extends('layouts.admin')

@section('title', 'RCS Agent Approvals')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.page-header h4 {
    color: var(--admin-primary);
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.page-header p {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
}

.filter-row {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.filter-group label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 600;
}

.filter-group select,
.filter-group input {
    min-width: 150px;
    font-size: 0.85rem;
}

.card-header-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.export-btn {
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
    background: transparent;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    color: #64748b;
    cursor: pointer;
}

.export-btn:hover {
    background: #f8fafc;
    border-color: var(--admin-accent);
    color: var(--admin-primary);
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
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Messaging Assets</a>
        <span class="separator">/</span>
        <span>RCS Agent Approvals</span>
    </div>

    <div class="page-header">
        <div>
            <h4><i class="fas fa-robot me-2"></i>RCS Agent Approvals</h4>
            <p>Review and approve RCS Business Messaging agent registrations</p>
        </div>
        <div class="card-header-actions">
            <button class="export-btn" onclick="exportQueue('csv')">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="approval-queue-stats">
        <div class="approval-stat-card pending" data-status="submitted">
            <div class="stat-count" id="stat-submitted">5</div>
            <div class="stat-label">Submitted</div>
        </div>
        <div class="approval-stat-card in-review active" data-status="in-review">
            <div class="stat-count" id="stat-in-review">3</div>
            <div class="stat-label">In Review</div>
        </div>
        <div class="approval-stat-card approved" data-status="approved">
            <div class="stat-count" id="stat-approved">234</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="approval-stat-card rejected" data-status="rejected">
            <div class="stat-count" id="stat-rejected">12</div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="approval-stat-card total" data-status="all">
            <div class="stat-count" id="stat-total">254</div>
            <div class="stat-label">Total</div>
        </div>
    </div>

    <div class="filter-row">
        <div class="filter-group">
            <label>Status</label>
            <select class="form-select form-select-sm" id="filterStatus">
                <option value="awaiting-action" selected>Awaiting Action</option>
                <option value="submitted">Submitted Only</option>
                <option value="in-review">In Review Only</option>
                <option value="all">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Agent Type</label>
            <select class="form-select form-select-sm" id="filterType">
                <option value="">All Types</option>
                <option value="conversational">Conversational</option>
                <option value="promotional">Promotional</option>
                <option value="transactional">Transactional</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Account</label>
            <select class="form-select form-select-sm" id="filterAccount">
                <option value="">All Accounts</option>
                <option value="ACC-1234">Acme Corporation</option>
                <option value="ACC-5678">Finance Ltd</option>
                <option value="ACC-3456">MedTech Solutions</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Search</label>
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Agent name...">
        </div>
        <button class="btn admin-btn-apply" onclick="applyFilters()">Apply</button>
        <button class="btn btn-link text-muted" onclick="clearFilters()">Clear</button>
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

    <div class="admin-card">
        <div class="card-body p-0">
            <table class="table approval-queue-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                        </th>
                        <th>Agent Name</th>
                        <th>Type</th>
                        <th>Account</th>
                        <th>Submitted</th>
                        <th>Carriers</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="approvalQueueBody">
                    <tr data-item-id="RCS-001" data-status="submitted" data-type="conversational">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-001')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div class="agent-logo">AC</div>
                                <div>
                                    <div class="approval-item-name">Acme Support Bot</div>
                                    <div class="agent-desc">Customer support and FAQ assistance</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge conversational">Conversational</span></td>
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
                                3 hours ago
                            </div>
                        </td>
                        <td>
                            <span class="carrier-badge">EE</span>
                            <span class="carrier-badge">Vodafone</span>
                            <span class="carrier-badge">O2</span>
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
                                <div class="agent-logo">FL</div>
                                <div>
                                    <div class="approval-item-name">Finance Alerts</div>
                                    <div class="agent-desc">Transaction alerts and security notifications</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge transactional">Transactional</span></td>
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
                                <span class="date">Jan 19, 2026</span><br>
                                1 day ago
                            </div>
                        </td>
                        <td>
                            <span class="carrier-badge">EE</span>
                            <span class="carrier-badge">Three</span>
                        </td>
                        <td><span class="approval-status-badge in-review"><i class="fas fa-search"></i> In Review</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn approve" onclick="quickApprove('RCS-002')">Approve</button>
                                <button class="approval-action-btn reject" onclick="showRejectModal('RCS-002')">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-003" data-status="submitted" data-type="promotional">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-003')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div class="agent-logo">RM</div>
                                <div>
                                    <div class="approval-item-name">RetailMax Offers</div>
                                    <div class="agent-desc">Promotional campaigns and special offers</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge promotional">Promotional</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">RM</div>
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
                        <td>
                            <span class="carrier-badge">Vodafone</span>
                            <span class="carrier-badge">O2</span>
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
                                <div class="agent-logo">HP</div>
                                <div>
                                    <div class="approval-item-name">HealthPlus Assistant</div>
                                    <div class="agent-desc">Appointment booking and health reminders</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge conversational">Conversational</span></td>
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
                                <span class="date">Jan 18, 2026</span><br>
                                2 days ago
                            </div>
                        </td>
                        <td>
                            <span class="carrier-badge">EE</span>
                            <span class="carrier-badge">Vodafone</span>
                            <span class="carrier-badge">O2</span>
                            <span class="carrier-badge">Three</span>
                        </td>
                        <td><span class="approval-status-badge in-review"><i class="fas fa-search"></i> In Review</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn approve" onclick="quickApprove('RCS-004')">Approve</button>
                                <button class="approval-action-btn reject" onclick="showRejectModal('RCS-004')">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-005" data-status="submitted" data-type="transactional">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-005')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div class="agent-logo">FD</div>
                                <div>
                                    <div class="approval-item-name">FoodDelivery Updates</div>
                                    <div class="agent-desc">Order tracking and delivery notifications</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge transactional">Transactional</span></td>
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
                        <td>
                            <span class="carrier-badge">EE</span>
                            <span class="carrier-badge">Vodafone</span>
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
                                <div class="agent-logo">TW</div>
                                <div>
                                    <div class="approval-item-name">TravelWorld Deals</div>
                                    <div class="agent-desc">Travel packages and holiday offers</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge promotional">Promotional</span></td>
                        <td>
                            <div class="approval-item-account">
                                <div class="account-avatar">TW</div>
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
                        <td>
                            <span class="carrier-badge">O2</span>
                            <span class="carrier-badge">Three</span>
                        </td>
                        <td><span class="approval-status-badge in-review"><i class="fas fa-search"></i> In Review</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn approve" onclick="quickApprove('RCS-006')">Approve</button>
                                <button class="approval-action-btn reject" onclick="showRejectModal('RCS-006')">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-item-id="RCS-007" data-status="submitted" data-type="conversational">
                        <td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect('RCS-007')"></td>
                        <td>
                            <div class="agent-name-cell">
                                <div class="agent-logo">EA</div>
                                <div>
                                    <div class="approval-item-name">EduLearn Helper</div>
                                    <div class="agent-desc">Course info and student support</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge conversational">Conversational</span></td>
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
                        <td>
                            <span class="carrier-badge">EE</span>
                            <span class="carrier-badge">Vodafone</span>
                            <span class="carrier-badge">O2</span>
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
                                <div class="agent-logo">SB</div>
                                <div>
                                    <div class="approval-item-name">SecureBank Notify</div>
                                    <div class="agent-desc">Security alerts and account notifications</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rcs-agent-type-badge transactional">Transactional</span></td>
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
                                <span class="date">Jan 16, 2026</span><br>
                                4 days ago
                            </div>
                        </td>
                        <td>
                            <span class="carrier-badge">EE</span>
                            <span class="carrier-badge">Vodafone</span>
                            <span class="carrier-badge">O2</span>
                            <span class="carrier-badge">Three</span>
                        </td>
                        <td><span class="approval-status-badge submitted"><i class="fas fa-paper-plane"></i> Submitted</span></td>
                        <td>
                            <div class="approval-quick-actions">
                                <button class="approval-action-btn review" onclick="markInReview('RCS-008')">Review</button>
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
});

var currentRejectItem = null;
var selectedItems = [];

function applyFilters() {
    var status = document.getElementById('filterStatus').value;
    var type = document.getElementById('filterType').value;
    var account = document.getElementById('filterAccount').value;
    var search = document.getElementById('searchInput').value.toLowerCase();

    document.querySelectorAll('.approval-queue-table tbody tr').forEach(function(row) {
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
    var visible = document.querySelectorAll('.approval-queue-table tbody tr:not([style*="display: none"])').length;
    document.querySelector('.card-footer .text-muted').textContent = 'Showing 1-' + visible + ' of ' + visible + ' items';
}

function toggleSelectAll() {
    var checked = document.getElementById('selectAllCheckbox').checked;
    selectedItems = [];
    
    document.querySelectorAll('.approval-queue-table tbody tr:not([style*="display: none"]) .item-checkbox').forEach(function(checkbox) {
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
    var result = AdminControlPlane.ApprovalFramework.markInReview(itemId, AdminControlPlane.getCurrentAdmin().email);
    if (result.success) {
        updateRowStatus(itemId, 'in-review');
        showToast('Agent marked for review', 'info');
    }
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
    document.querySelectorAll('.approval-queue-table tbody tr').forEach(function(row) {
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
        count: document.querySelectorAll('.approval-queue-table tbody tr').length
    });
    showToast('Export started...', 'info');
}

function showToast(message, type) {
    console.log('[Toast]', type, message);
}
</script>
@endpush

@extends('layouts.admin')

@section('title', 'RCS Agent Approvals')

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

.rcs-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}
.rcs-status-badge.draft { background: #f3f4f6; color: #374151; }
.rcs-status-badge.submitted { background: #dbeafe; color: #1e40af; }
.rcs-status-badge.in_review { background: #e0e7ff; color: #3730a3; }
.rcs-status-badge.pending_info { background: #fef3c7; color: #92400e; }
.rcs-status-badge.info_provided { background: #fce7f3; color: #9d174d; }
.rcs-status-badge.sent_to_supplier { background: #e0e7ff; color: #4338ca; }
.rcs-status-badge.supplier_approved { background: #ccfbf1; color: #0f766e; }
.rcs-status-badge.approved { background: #d1fae5; color: #065f46; }
.rcs-status-badge.rejected { background: #fee2e2; color: #991b1b; }
.rcs-status-badge.suspended { background: #ffedd5; color: #9a3412; }
.rcs-status-badge.revoked { background: #f3f4f6; color: #6b7280; }

.billing-cat-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
    background: rgba(30, 58, 95, 0.08);
    color: var(--admin-primary, #1e3a5f);
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
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="draft" id="statusDraft"><label class="form-check-label small" for="statusDraft">Draft</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="submitted" id="statusSubmitted"><label class="form-check-label small" for="statusSubmitted">Submitted</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="in_review" id="statusInReview"><label class="form-check-label small" for="statusInReview">In Review</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="pending_info" id="statusPendingInfo"><label class="form-check-label small" for="statusPendingInfo">Pending Info</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="info_provided" id="statusInfoProvided"><label class="form-check-label small" for="statusInfoProvided">Info Provided</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="sent_to_supplier" id="statusSentToSupplier"><label class="form-check-label small" for="statusSentToSupplier">Sent to Mobile Networks</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="supplier_approved" id="statusSupplierApproved"><label class="form-check-label small" for="statusSupplierApproved">Supplier Approved</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="approved" id="statusApproved"><label class="form-check-label small" for="statusApproved">Live</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="rejected" id="statusRejected"><label class="form-check-label small" for="statusRejected">Rejected</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="suspended" id="statusSuspended"><label class="form-check-label small" for="statusSuspended">Suspended</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="revoked" id="statusRevoked"><label class="form-check-label small" for="statusRevoked">Revoked</label></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Billing Category</label>
                    <select class="form-select form-select-sm" id="filterBillingCategory">
                        <option value="">All Categories</option>
                        <option value="conversational">Conversational</option>
                        <option value="non-conversational">Non-Conversational</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Use Case</label>
                    <select class="form-select form-select-sm" id="filterUseCase">
                        <option value="">All Use Cases</option>
                        <option value="otp">OTP</option>
                        <option value="transactional">Transactional</option>
                        <option value="promotional">Promotional</option>
                        <option value="multi-use">Multi-Use</option>
                    </select>
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
        <div class="approval-stat-card pending active" data-status="submitted" onclick="filterByTile('submitted')" style="cursor: pointer;">
            <div class="stat-count" id="stat-submitted">-</div>
            <div class="stat-label">Submitted</div>
        </div>
        <div class="approval-stat-card in-review" data-status="in_review" onclick="filterByTile('in_review')" style="cursor: pointer;">
            <div class="stat-count" id="stat-in-review">-</div>
            <div class="stat-label">In Review</div>
        </div>
        <div class="approval-stat-card approved" data-status="approved" onclick="filterByTile('approved')" style="cursor: pointer;">
            <div class="stat-count" id="stat-approved">-</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="approval-stat-card rejected" data-status="rejected" onclick="filterByTile('rejected')" style="cursor: pointer;">
            <div class="stat-count" id="stat-rejected">-</div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="approval-stat-card total" data-status="all" onclick="filterByTile('all')" style="cursor: pointer;">
            <div class="stat-count" id="stat-total">-</div>
            <div class="stat-label">Total</div>
        </div>
    </div>

    <div class="card mb-3" style="border: 1px solid #e0e6ed; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <div class="input-group" style="width: 320px;">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 ps-0" id="quickSearchInput" placeholder="Search by agent name or account...">
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
                        <th data-sort="name">Agent Name <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="billing_category">Category <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="account">Account <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="submitted">Submitted <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="approvalQueueBody">
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">Loading...</span>
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

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-2"></i>Reject RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Rejecting: <strong id="rejectItemName"></strong></p>
                
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
var csrfToken = $('meta[name="csrf-token"]').attr('content');
var currentPage = 1;
var currentFilterStatus = '';
var currentFilterBillingCategory = '';
var currentFilterUseCase = '';
var currentFilterAccount = '';
var currentSearchQuery = '';
var currentRejectItemUuid = null;
var selectedItems = [];
var allLoadedItems = [];

function ajaxHeaders() {
    return { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' };
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('[RCS Agent Approvals] Initializing...');
    
    if (typeof AdminControlPlane !== 'undefined') {
        if (AdminControlPlane.ApprovalFramework) {
            AdminControlPlane.ApprovalFramework.init('RCS_AGENT');
        }
        AdminControlPlane.logAdminAction('PAGE_VIEW', 'rcs-agent-approvals', {
            module: 'messaging_assets'
        });
    }

    loadRcsAgents();
    loadStatCounts();
});

function loadRcsAgents() {
    var params = { page: currentPage, per_page: 20 };
    if (currentFilterStatus && currentFilterStatus !== 'all') {
        params.status = currentFilterStatus;
    }
    if (currentFilterBillingCategory) {
        params.billing_category = currentFilterBillingCategory;
    }
    if (currentFilterUseCase) {
        params.use_case = currentFilterUseCase;
    }
    if (currentFilterAccount) {
        params.account_id = currentFilterAccount;
    }

    var tbody = document.getElementById('approvalQueueBody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';

    $.ajax({
        url: '/admin/api/rcs-agents',
        method: 'GET',
        data: params,
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                var paginator = response.data;
                var items = paginator.data || [];
                allLoadedItems = items;
                renderTableRows(items);
                renderPagination(paginator);
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No RCS agents found.</td></tr>';
            }
        },
        error: function(xhr) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load data. Please try again.</td></tr>';
            console.error('[RCS Agents] Load error:', xhr.responseText);
        }
    });
}

function loadStatCounts() {
    var statuses = ['submitted', 'in_review', 'approved', 'rejected'];

    statuses.forEach(function(status) {
        $.ajax({
            url: '/admin/api/rcs-agents',
            method: 'GET',
            data: { status: status, per_page: 1 },
            headers: ajaxHeaders(),
            success: function(response) {
                if (response.success && response.data) {
                    var count = response.data.total || 0;
                    if (status === 'submitted') {
                        document.getElementById('stat-submitted').textContent = count.toLocaleString();
                    } else if (status === 'in_review') {
                        document.getElementById('stat-in-review').textContent = count.toLocaleString();
                    } else if (status === 'approved') {
                        document.getElementById('stat-approved').textContent = count.toLocaleString();
                    } else if (status === 'rejected') {
                        document.getElementById('stat-rejected').textContent = count.toLocaleString();
                    }
                }
            }
        });
    });

    $.ajax({
        url: '/admin/api/rcs-agents',
        method: 'GET',
        data: { per_page: 1 },
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                document.getElementById('stat-total').textContent = (response.data.total || 0).toLocaleString();
            }
        }
    });
}

function renderTableRows(items) {
    var tbody = document.getElementById('approvalQueueBody');

    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No RCS agents found matching your filters.</td></tr>';
        return;
    }

    var html = '';
    items.forEach(function(item) {
        var uuid = item.uuid || '';
        var agentName = item.name || '';
        var description = item.description || '';
        var billingCategory = item.billing_category || '';
        var useCase = item.use_case || '';
        var workflowStatus = item.workflow_status || item.status || '';
        var submittedAt = item.submitted_at ? new Date(item.submitted_at) : (item.created_at ? new Date(item.created_at) : null);
        var accountName = (item.account && item.account.company_name) ? item.account.company_name : '';
        var accountNumber = (item.account && item.account.account_number) ? item.account.account_number : '';

        var dateStr = '';
        var timeAgo = '';
        if (submittedAt) {
            dateStr = submittedAt.toLocaleDateString('en-GB', { month: 'short', day: 'numeric', year: 'numeric' });
            timeAgo = getTimeAgo(submittedAt);
        }

        var statusBadge = getStatusBadge(workflowStatus);
        var actionButtons = getActionButtons(uuid, workflowStatus);

        html += '<tr data-item-id="' + escapeHtml(uuid) + '" data-status="' + escapeHtml(workflowStatus) + '">';
        html += '<td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect(\'' + escapeHtml(uuid) + '\')"></td>';
        html += '<td>';
        html += '<div class="agent-name-cell"><div>';
        html += '<div class="approval-item-name">' + escapeHtml(agentName) + '</div>';
        html += '<div class="agent-desc">' + escapeHtml(description) + '</div>';
        html += '</div></div>';
        html += '</td>';
        html += '<td>';
        if (billingCategory) {
            html += '<span class="billing-cat-badge">' + escapeHtml(billingCategory) + '</span>';
        }
        if (useCase) {
            html += '<div style="font-size: 0.7rem; color: #94a3b8; margin-top: 2px;">' + escapeHtml(useCase) + '</div>';
        }
        html += '</td>';
        html += '<td>';
        html += '<div class="approval-item-account"><div class="account-info">';
        html += '<div class="account-name">' + escapeHtml(accountName) + '</div>';
        html += '<div class="account-id">' + escapeHtml(accountNumber) + '</div>';
        html += '</div></div>';
        html += '</td>';
        html += '<td>';
        html += '<div class="submitted-time">';
        html += '<span class="date">' + escapeHtml(dateStr) + '</span><br>';
        html += escapeHtml(timeAgo);
        html += '</div>';
        html += '</td>';
        html += '<td>' + statusBadge + '</td>';
        html += '<td><div class="approval-quick-actions">' + actionButtons + '</div></td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
}

function getStatusBadge(status) {
    var map = {
        'draft': { icon: 'fa-pencil-alt', label: 'Draft' },
        'submitted': { icon: 'fa-paper-plane', label: 'Submitted' },
        'in_review': { icon: 'fa-search', label: 'In Review' },
        'pending_info': { icon: 'fa-undo', label: 'Pending Info' },
        'info_provided': { icon: 'fa-reply', label: 'Info Provided' },
        'sent_to_supplier': { icon: 'fa-satellite-dish', label: 'Sent to Mobile Networks' },
        'supplier_approved': { icon: 'fa-check-double', label: 'Supplier Approved' },
        'approved': { icon: 'fa-check-circle', label: 'Live' },
        'rejected': { icon: 'fa-times-circle', label: 'Rejected' },
        'suspended': { icon: 'fa-pause-circle', label: 'Suspended' },
        'revoked': { icon: 'fa-ban', label: 'Revoked' }
    };
    var info = map[status] || { icon: 'fa-question', label: status };
    return '<span class="rcs-status-badge ' + escapeHtml(status) + '"><i class="fas ' + info.icon + '"></i> ' + escapeHtml(info.label) + '</span>';
}

function getActionButtons(uuid, status) {
    if (status === 'submitted' || status === 'in_review' || status === 'pending_info' || status === 'info_provided') {
        return '<button class="approval-action-btn review" onclick="goToDetail(\'' + escapeHtml(uuid) + '\')">Review</button>';
    }
    return '<button class="approval-action-btn review" onclick="goToDetail(\'' + escapeHtml(uuid) + '\')">View</button>';
}

function renderPagination(paginator) {
    var current = paginator.current_page || 1;
    var last = paginator.last_page || 1;
    var total = paginator.total || 0;
    var perPage = paginator.per_page || 20;
    var from = ((current - 1) * perPage) + 1;
    var to = Math.min(current * perPage, total);

    document.querySelector('.card-footer .text-muted').textContent = 'Showing ' + (total > 0 ? from : 0) + '-' + to + ' of ' + total + ' items';

    var paginationHtml = '';
    paginationHtml += '<li class="page-item ' + (current <= 1 ? 'disabled' : '') + '"><a class="page-link" href="#" onclick="goToPage(' + (current - 1) + '); return false;">Previous</a></li>';
    
    var startPage = Math.max(1, current - 2);
    var endPage = Math.min(last, current + 2);
    for (var i = startPage; i <= endPage; i++) {
        paginationHtml += '<li class="page-item ' + (i === current ? 'active' : '') + '"><a class="page-link" href="#" onclick="goToPage(' + i + '); return false;">' + i + '</a></li>';
    }
    
    paginationHtml += '<li class="page-item ' + (current >= last ? 'disabled' : '') + '"><a class="page-link" href="#" onclick="goToPage(' + (current + 1) + '); return false;">Next</a></li>';

    document.querySelector('.card-footer .pagination').innerHTML = paginationHtml;
}

function goToPage(page) {
    if (page < 1) return;
    currentPage = page;
    loadRcsAgents();
}

function getTimeAgo(date) {
    var now = new Date();
    var diffMs = now - date;
    var diffMins = Math.floor(diffMs / 60000);
    var diffHours = Math.floor(diffMs / 3600000);
    var diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return diffMins + ' min ago';
    if (diffHours < 24) return diffHours + ' hours ago';
    if (diffDays === 1) return '1 day ago';
    return diffDays + ' days ago';
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function applyFilters() {
    var statusCheckboxes = document.querySelectorAll('[data-filter="statuses"] .form-check-input:checked');
    var statuses = [];
    statusCheckboxes.forEach(function(cb) { statuses.push(cb.value); });
    
    currentFilterStatus = statuses.length === 1 ? statuses[0] : '';
    currentFilterBillingCategory = document.getElementById('filterBillingCategory').value;
    currentFilterUseCase = document.getElementById('filterUseCase').value;
    currentFilterAccount = document.getElementById('filterAccount').value;
    currentSearchQuery = document.getElementById('quickSearchInput').value;
    currentPage = 1;

    loadRcsAgents();

    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('FILTER_APPLIED', 'rcs-agent-queue', {
            status: currentFilterStatus,
            billing_category: currentFilterBillingCategory,
            use_case: currentFilterUseCase,
            account: currentFilterAccount,
            search: currentSearchQuery
        });
    }
}

function clearFilters() {
    document.querySelectorAll('.multiselect-dropdown .form-check-input').forEach(function(cb) { cb.checked = false; });
    document.getElementById('filterAccount').value = '';
    document.getElementById('filterBillingCategory').value = '';
    document.getElementById('filterUseCase').value = '';
    document.getElementById('quickSearchInput').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    currentFilterStatus = '';
    currentFilterBillingCategory = '';
    currentFilterUseCase = '';
    currentFilterAccount = '';
    currentSearchQuery = '';
    currentPage = 1;
    loadRcsAgents();
}

document.addEventListener('DOMContentLoaded', function() {
    var applyBtn = document.getElementById('btnApplyFilters');
    if (applyBtn) applyBtn.addEventListener('click', applyFilters);
    
    var resetBtn = document.getElementById('btnResetFilters');
    if (resetBtn) resetBtn.addEventListener('click', clearFilters);

    var searchInput = document.getElementById('quickSearchInput');
    if (searchInput) {
        var searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                currentSearchQuery = searchInput.value;
                currentPage = 1;
                loadRcsAgents();
            }, 400);
        });
    }
});

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
    document.querySelectorAll('.approval-stat-card').forEach(function(card) {
        card.classList.remove('active');
    });
    var activeCard = document.querySelector('.approval-stat-card[data-status="' + status + '"]');
    if (activeCard) {
        activeCard.classList.add('active');
    }

    if (status === 'all') {
        currentFilterStatus = '';
    } else {
        currentFilterStatus = status;
    }
    currentPage = 1;
    loadRcsAgents();
}

function goToDetail(uuid) {
    window.location.href = '/admin/assets/rcs-agents/' + uuid;
}

function useRejectTemplate(el) {
    var reason = el.textContent;
    document.getElementById('rejectReason').value = reason + ': ';
    document.getElementById('rejectReason').focus();
}

function confirmReject() {
    var reason = document.getElementById('rejectReason').value.trim();
    if (reason.length < 10) {
        showToast('Please provide a rejection reason (minimum 10 characters)', 'warning');
        return;
    }

    var uuid = currentRejectItemUuid;
    if (uuid === 'BULK') {
        bulkRejectConfirm(reason);
        return;
    }

    $.ajax({
        url: '/admin/api/rcs-agents/' + uuid + '/reject',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({ reason: reason }),
        success: function(response) {
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                showToast('RCS Agent rejected', 'warning');
                loadRcsAgents();
                loadStatCounts();
            } else {
                showToast(response.error || 'Failed to reject', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to reject';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function bulkApprove() {
    if (selectedItems.length === 0) return;
    if (!confirm('Approve ' + selectedItems.length + ' items?')) return;

    var completed = 0;
    var errors = 0;
    selectedItems.forEach(function(uuid) {
        $.ajax({
            url: '/admin/api/rcs-agents/' + uuid + '/approve',
            method: 'POST',
            headers: ajaxHeaders(),
            data: JSON.stringify({ notes: 'Bulk approval' }),
            success: function() { completed++; checkBulkDone(); },
            error: function() { errors++; completed++; checkBulkDone(); }
        });
    });

    function checkBulkDone() {
        if (completed === selectedItems.length) {
            clearSelection();
            showToast((completed - errors) + ' items approved' + (errors > 0 ? ', ' + errors + ' failed' : ''), errors > 0 ? 'warning' : 'success');
            loadRcsAgents();
            loadStatCounts();
        }
    }
}

function bulkRejectConfirm(reason) {
    var completed = 0;
    var errors = 0;
    selectedItems.forEach(function(uuid) {
        $.ajax({
            url: '/admin/api/rcs-agents/' + uuid + '/reject',
            method: 'POST',
            headers: ajaxHeaders(),
            data: JSON.stringify({ reason: reason }),
            success: function() { completed++; checkBulkDone(); },
            error: function() { errors++; completed++; checkBulkDone(); }
        });
    });

    function checkBulkDone() {
        if (completed === selectedItems.length) {
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            clearSelection();
            showToast((completed - errors) + ' items rejected' + (errors > 0 ? ', ' + errors + ' failed' : ''), errors > 0 ? 'warning' : 'success');
            loadRcsAgents();
            loadStatCounts();
        }
    }
}

function showBulkRejectModal() {
    currentRejectItemUuid = 'BULK';
    document.getElementById('rejectItemName').textContent = selectedItems.length + ' items';
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showToast(message, type) {
    var colors = {
        success: { bg: '#22c55e', icon: 'fa-check-circle' },
        error: { bg: '#ef4444', icon: 'fa-times-circle' },
        warning: { bg: '#f59e0b', icon: 'fa-exclamation-triangle' },
        info: { bg: '#3b82f6', icon: 'fa-info-circle' }
    };
    var c = colors[type] || colors.info;
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:99999;background:' + c.bg + ';color:#fff;padding:0.75rem 1.25rem;border-radius:8px;font-size:0.85rem;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.2);display:flex;align-items:center;gap:0.5rem;animation:slideInRight 0.3s ease;max-width:400px;';
    toast.innerHTML = '<i class="fas ' + c.icon + '"></i> ' + message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; }, 4000);
    setTimeout(function() { toast.remove(); }, 4500);
}
</script>
@endpush

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
            <div class="stat-count" id="stat-pending">-</div>
            <div class="stat-label">Pending</div>
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
var csrfToken = $('meta[name="csrf-token"]').attr('content');
var currentPage = 1;
var currentFilterStatus = '';
var currentFilterType = '';
var currentFilterAccount = '';
var currentSearchQuery = '';
var currentRejectItem = null;
var currentRejectItemUuid = null;
var selectedItems = [];
var allLoadedItems = [];

function ajaxHeaders() {
    return { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' };
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('[SenderID Approvals] Initializing...');
    
    if (typeof AdminControlPlane !== 'undefined') {
        if (AdminControlPlane.ApprovalFramework) {
            AdminControlPlane.ApprovalFramework.init('SENDERID');
        }
        AdminControlPlane.logAdminAction('PAGE_VIEW', 'sender-id-approvals', {
            module: 'messaging_assets'
        });
    }

    loadSenderIds();
    loadStatCounts();
});

function loadSenderIds() {
    var params = { page: currentPage, per_page: 20 };
    if (currentFilterStatus && currentFilterStatus !== 'all') {
        params.status = currentFilterStatus;
    }
    if (currentFilterType) {
        params.sender_type = currentFilterType;
    }
    if (currentFilterAccount) {
        params.account_id = currentFilterAccount;
    }

    var tbody = document.getElementById('approvalQueueBody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';

    $.ajax({
        url: '/admin/api/sender-ids',
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
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No sender IDs found.</td></tr>';
            }
        },
        error: function(xhr) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load data. Please try again.</td></tr>';
            console.error('[SenderID] Load error:', xhr.responseText);
        }
    });
}

function loadStatCounts() {
    var statuses = ['submitted', 'approved', 'rejected'];
    var totalCount = 0;

    statuses.forEach(function(status) {
        $.ajax({
            url: '/admin/api/sender-ids',
            method: 'GET',
            data: { status: status, per_page: 1 },
            headers: ajaxHeaders(),
            success: function(response) {
                if (response.success && response.data) {
                    var count = response.data.total || 0;
                    if (status === 'submitted') {
                        document.getElementById('stat-pending').textContent = count.toLocaleString();
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
        url: '/admin/api/sender-ids',
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
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No sender IDs found matching your filters.</td></tr>';
        return;
    }

    var html = '';
    items.forEach(function(item) {
        var uuid = item.uuid || '';
        var senderIdValue = item.sender_id_value || '';
        var senderType = item.sender_type || '';
        var typeLower = senderType.toLowerCase();
        var typeLabel = senderType.charAt(0).toUpperCase() + senderType.slice(1).toLowerCase();
        var workflowStatus = item.workflow_status || '';
        var brandName = item.brand_name || '';
        var useCase = item.use_case_description || item.use_case || '';
        var submittedAt = item.submitted_at ? new Date(item.submitted_at) : (item.created_at ? new Date(item.created_at) : null);
        var accountName = (item.account && item.account.company_name) ? item.account.company_name : (item.account_id || '');
        var accountNumber = (item.account && item.account.account_number) ? item.account.account_number : '';
        var accountInitials = accountName ? accountName.split(' ').map(function(w) { return w.charAt(0); }).join('').substring(0, 2).toUpperCase() : 'NA';

        var dateStr = '';
        var timeAgo = '';
        if (submittedAt) {
            dateStr = submittedAt.toLocaleDateString('en-GB', { month: 'short', day: 'numeric', year: 'numeric' });
            timeAgo = getTimeAgo(submittedAt);
        }

        var statusBadge = getStatusBadge(workflowStatus);
        var actionButtons = getActionButtons(uuid, workflowStatus);

        html += '<tr data-item-id="' + uuid + '" data-status="' + workflowStatus + '">';
        html += '<td><input type="checkbox" class="item-checkbox" onchange="toggleItemSelect(\'' + uuid + '\')"></td>';
        html += '<td>';
        html += '<div class="approval-item-name">' + escapeHtml(senderIdValue) + '</div>';
        html += '<div class="approval-item-id">' + escapeHtml(uuid.substring(0, 8)) + '</div>';
        html += '</td>';
        html += '<td><span class="senderid-type-badge ' + escapeHtml(typeLower) + '">' + escapeHtml(typeLabel) + '</span></td>';
        html += '<td>';
        html += '<div class="approval-item-account">';
        html += '<div class="account-avatar">' + escapeHtml(accountInitials) + '</div>';
        html += '<div class="account-info">';
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
        html += '<td><span class="use-case-text" title="' + escapeHtml(useCase) + '">' + escapeHtml(useCase) + '</span></td>';
        html += '<td>' + statusBadge + '</td>';
        html += '<td><div class="approval-quick-actions">' + actionButtons + '</div></td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
}

function getStatusBadge(status) {
    var map = {
        'draft': { icon: 'fa-pencil-alt', label: 'Draft', cls: 'pending' },
        'submitted': { icon: 'fa-clock', label: 'Submitted', cls: 'pending' },
        'in_review': { icon: 'fa-search', label: 'In Review', cls: 'pending' },
        'pending_info': { icon: 'fa-undo', label: 'Returned to Customer', cls: 'pending' },
        'info_provided': { icon: 'fa-reply', label: 'Resubmitted', cls: 'approved' },
        'approved': { icon: 'fa-check-circle', label: 'Approved', cls: 'approved' },
        'rejected': { icon: 'fa-times-circle', label: 'Rejected', cls: 'rejected' },
        'suspended': { icon: 'fa-pause-circle', label: 'Suspended', cls: 'rejected' },
        'revoked': { icon: 'fa-ban', label: 'Revoked', cls: 'rejected' }
    };
    var info = map[status] || { icon: 'fa-question', label: status, cls: 'pending' };
    return '<span class="approval-status-badge ' + info.cls + '"><i class="fas ' + info.icon + '"></i> ' + info.label + '</span>';
}

function getActionButtons(uuid, status) {
    if (status === 'submitted' || status === 'in_review' || status === 'pending_info' || status === 'info_provided') {
        return '<button class="approval-action-btn review" onclick="goToDetail(\'' + uuid + '\')">Review</button>';
    }
    return '<button class="approval-action-btn review" onclick="goToDetail(\'' + uuid + '\')">View</button>';
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
    loadSenderIds();
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
    var typeCheckboxes = document.querySelectorAll('[data-filter="types"] .form-check-input:checked');
    
    var statuses = [];
    statusCheckboxes.forEach(function(cb) { statuses.push(cb.value.toLowerCase()); });
    
    var types = [];
    typeCheckboxes.forEach(function(cb) { types.push(cb.value.toUpperCase()); });
    
    currentFilterStatus = statuses.length === 1 ? statuses[0] : (statuses.length > 1 ? statuses[0] : '');
    currentFilterType = types.length === 1 ? types[0] : '';
    currentFilterAccount = document.getElementById('filterAccount').value;
    currentSearchQuery = document.getElementById('quickSearchInput').value;
    currentPage = 1;

    loadSenderIds();

    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('FILTER_APPLIED', 'sender-id-queue', {
            status: currentFilterStatus,
            type: currentFilterType,
            account: currentFilterAccount,
            search: currentSearchQuery
        });
    }
}

function clearFilters() {
    document.querySelectorAll('.multiselect-dropdown .form-check-input').forEach(function(cb) { cb.checked = false; });
    document.getElementById('filterAccount').value = '';
    document.getElementById('quickSearchInput').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    currentFilterStatus = '';
    currentFilterType = '';
    currentFilterAccount = '';
    currentSearchQuery = '';
    currentPage = 1;
    loadSenderIds();
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
                loadSenderIds();
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
    } else if (status === 'pending') {
        currentFilterStatus = 'submitted';
    } else {
        currentFilterStatus = status;
    }
    currentPage = 1;
    loadSenderIds();
}

function goToDetail(uuid) {
    window.location.href = '/admin/assets/sender-ids/' + uuid;
}

function quickApprove(uuid) {
    if (confirm('Approve this Sender ID?')) {
        $.ajax({
            url: '/admin/api/sender-ids/' + uuid + '/approve',
            method: 'POST',
            headers: ajaxHeaders(),
            data: JSON.stringify({ notes: '' }),
            success: function(response) {
                if (response.success) {
                    showToast('Sender ID approved successfully', 'success');
                    loadSenderIds();
                    loadStatCounts();
                } else {
                    showToast(response.error || 'Failed to approve', 'error');
                }
            },
            error: function(xhr) {
                var msg = 'Failed to approve';
                try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
                showToast(msg, 'error');
            }
        });
    }
}

function showRejectModal(uuid) {
    currentRejectItemUuid = uuid;
    var row = document.querySelector('[data-item-id="' + uuid + '"]');
    var name = row ? row.querySelector('.approval-item-name').textContent : uuid;
    document.getElementById('rejectItemName').textContent = name;
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showRejectModalFromDrawer() {
    var uuid = document.getElementById('drawerRequestId').textContent;
    showRejectModal(uuid);
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

    var uuid = currentRejectItemUuid;
    if (uuid === 'BULK') {
        bulkRejectConfirm(reason);
        return;
    }

    $.ajax({
        url: '/admin/api/sender-ids/' + uuid + '/reject',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({ reason: reason }),
        success: function(response) {
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                closeDrawer();
                showToast('Sender ID rejected', 'warning');
                loadSenderIds();
                loadStatCounts();
            } else {
                alert(response.error || 'Failed to reject');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to reject';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            alert(msg);
        }
    });
}

function approveFromDrawer() {
    var uuid = document.getElementById('drawerRequestId').textContent;
    var notes = document.getElementById('drawerNotes').value;
    
    $.ajax({
        url: '/admin/api/sender-ids/' + uuid + '/approve',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({ notes: notes }),
        success: function(response) {
            if (response.success) {
                closeDrawer();
                showToast('Sender ID approved successfully', 'success');
                loadSenderIds();
                loadStatCounts();
            }
        },
        error: function(xhr) {
            var msg = 'Failed to approve';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function closeDrawer() {
    var drawer = document.querySelector('.approval-drawer');
    var overlay = document.querySelector('.approval-drawer-overlay');
    if (drawer) drawer.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
}

function bulkApprove() {
    if (selectedItems.length === 0) return;
    if (!confirm('Approve ' + selectedItems.length + ' items?')) return;

    var completed = 0;
    var errors = 0;
    selectedItems.forEach(function(uuid) {
        $.ajax({
            url: '/admin/api/sender-ids/' + uuid + '/approve',
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
            loadSenderIds();
            loadStatCounts();
        }
    }
}

function bulkRejectConfirm(reason) {
    var completed = 0;
    var errors = 0;
    selectedItems.forEach(function(uuid) {
        $.ajax({
            url: '/admin/api/sender-ids/' + uuid + '/reject',
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
            loadSenderIds();
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

function exportQueue(format) {
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('EXPORT_INITIATED', 'sender-id-queue', {
            format: format,
            count: allLoadedItems.length
        });
    }
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

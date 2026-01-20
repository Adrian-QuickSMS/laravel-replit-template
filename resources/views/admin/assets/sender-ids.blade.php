@extends('layouts.admin')

@section('title', 'Sender ID Approvals')

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
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Messaging Assets</a>
        <span class="separator">/</span>
        <span>Sender ID Approvals</span>
    </div>

    <div class="page-header">
        <div>
            <h4><i class="fas fa-signature me-2"></i>Sender ID Approvals</h4>
            <p>Review and approve customer SenderID registration requests</p>
        </div>
        <div class="card-header-actions">
            <button class="export-btn" onclick="exportQueue('csv')">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="approval-queue-stats">
        <div class="approval-stat-card pending active" data-status="pending">
            <div class="stat-count" id="stat-pending">8</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="approval-stat-card approved" data-status="approved">
            <div class="stat-count" id="stat-approved">1,847</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="approval-stat-card rejected" data-status="rejected">
            <div class="stat-count" id="stat-rejected">56</div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="approval-stat-card total" data-status="all">
            <div class="stat-count" id="stat-total">1,914</div>
            <div class="stat-label">Total</div>
        </div>
    </div>

    <div class="filter-row">
        <div class="filter-group">
            <label>Status</label>
            <select class="form-select form-select-sm" id="filterStatus">
                <option value="pending" selected>Pending Review</option>
                <option value="all">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Type</label>
            <select class="form-select form-select-sm" id="filterType">
                <option value="">All Types</option>
                <option value="alphanumeric">Alphanumeric</option>
                <option value="numeric">Numeric</option>
                <option value="shortcode">Shortcode</option>
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
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Sender ID name...">
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
                        <th>Sender ID</th>
                        <th>Type</th>
                        <th>Account</th>
                        <th>Submitted</th>
                        <th>Use Case</th>
                        <th>Status</th>
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

    document.querySelectorAll('.approval-queue-table tbody tr').forEach(function(row) {
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
    var visible = document.querySelectorAll('.approval-queue-table tbody tr:not([style*="display: none"])').length;
    var total = document.querySelectorAll('.approval-queue-table tbody tr').length;
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
    document.querySelectorAll('.approval-queue-table tbody tr').forEach(function(row) {
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
        count: document.querySelectorAll('.approval-queue-table tbody tr').length
    });
    showToast('Export started...', 'info');
}

function showToast(message, type) {
    console.log('[Toast]', type, message);
}
</script>
@endpush

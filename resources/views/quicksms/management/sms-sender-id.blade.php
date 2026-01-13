@extends('layouts.quicksms')

@section('title', 'SMS SenderID Registration')

@push('styles')
<style>
.senderid-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.senderid-header h2 {
    margin: 0;
    font-weight: 600;
}
.senderid-header p {
    margin: 0;
    color: #6c757d;
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
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: var(--primary);
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
.senderid-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}
.senderid-table {
    width: 100%;
    margin: 0;
    min-width: 800px;
    table-layout: fixed;
}
.senderid-table thead th {
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
.senderid-table thead th:first-child { width: 18%; }
.senderid-table thead th:nth-child(2) { width: 20%; }
.senderid-table thead th:nth-child(3) { width: 14%; }
.senderid-table thead th:nth-child(4) { width: 13%; }
.senderid-table thead th:nth-child(5) { width: 15%; }
.senderid-table thead th:nth-child(6) { width: 12%; }
.senderid-table thead th:last-child { 
    width: 8%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.senderid-table thead th:hover {
    background: #e9ecef;
}
.senderid-table thead th:last-child:hover {
    background: #f8f9fa;
}
.senderid-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.senderid-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.senderid-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
}
.senderid-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.senderid-table tbody tr:last-child td {
    border-bottom: none;
}
.senderid-table tbody tr:hover td {
    background: #f8f9fa;
}
.senderid-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.senderid-name {
    font-weight: 600;
    color: #343a40;
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    letter-spacing: 0.5px;
}
.badge-pending {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-under-review {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-approved {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-rejected {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-suspended {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-otp {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-marketing {
    background: rgba(255, 107, 107, 0.15);
    color: #ff6b6b;
}
.badge-transactional {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-alerts {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.search-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
    flex-wrap: wrap;
}
.search-box {
    flex: 1;
    max-width: 300px;
    min-width: 200px;
}
.filters-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0 0 0.75rem 0.75rem;
}
.drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}
.drawer-backdrop.show {
    opacity: 1;
    visibility: visible;
}
.drawer {
    position: fixed;
    top: 0;
    right: -500px;
    width: 500px;
    max-width: 90vw;
    height: 100%;
    background: #fff;
    z-index: 1050;
    transition: right 0.3s ease;
    display: flex;
    flex-direction: column;
    box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
}
.drawer.show {
    right: 0;
}
.drawer-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}
.drawer-header h5 {
    margin: 0;
    font-weight: 600;
}
.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
}
.drawer-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}
.form-label-required::after {
    content: ' *';
    color: #dc3545;
}
.senderid-input {
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    letter-spacing: 1px;
    text-transform: uppercase;
}
.char-counter {
    font-size: 0.75rem;
    color: #6c757d;
}
.char-counter.warning {
    color: #ffc107;
}
.char-counter.danger {
    color: #dc3545;
}
.validation-hint {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
.detail-row {
    display: flex;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    flex: 0 0 140px;
    font-weight: 500;
    color: #6c757d;
    font-size: 0.85rem;
}
.detail-value {
    flex: 1;
    color: #343a40;
    font-size: 0.85rem;
}
.audit-timeline {
    position: relative;
    padding-left: 1.5rem;
}
.audit-timeline::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.audit-item {
    position: relative;
    padding-bottom: 1rem;
}
.audit-item:last-child {
    padding-bottom: 0;
}
.audit-item::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid var(--primary);
}
.audit-item.approved::before {
    border-color: #1cbb8c;
    background: rgba(28, 187, 140, 0.2);
}
.audit-item.rejected::before {
    border-color: #dc3545;
    background: rgba(220, 53, 69, 0.2);
}
.audit-item.submitted::before {
    border-color: #3065D0;
    background: rgba(48, 101, 208, 0.2);
}
.audit-time {
    font-size: 0.75rem;
    color: #6c757d;
}
.audit-action {
    font-weight: 500;
    color: #343a40;
    font-size: 0.85rem;
}
.audit-user {
    font-size: 0.8rem;
    color: #6c757d;
}
.info-banner {
    background: rgba(136, 108, 192, 0.08);
    border: 1px solid rgba(136, 108, 192, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.info-banner h6 {
    color: #886CC0;
    margin-bottom: 0.5rem;
    font-weight: 600;
}
.info-banner ul {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.85rem;
    color: #495057;
}
.info-banner li {
    margin-bottom: 0.25rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="senderid-header">
        <div>
            <h2>SMS SenderID Registration</h2>
            <p>Register and manage approved sender identities for SMS messaging</p>
        </div>
        <button type="button" class="btn btn-primary" id="btnRegisterSenderId">
            <i class="fas fa-plus me-2"></i>Register SenderID
        </button>
    </div>

    <div class="info-banner">
        <h6><i class="fas fa-shield-alt me-2"></i>UK Compliance Requirements</h6>
        <ul>
            <li>All SenderIDs must be registered and approved before use</li>
            <li>SenderIDs must be 3-11 alphanumeric characters (letters and numbers only)</li>
            <li>Cannot start with a number or contain spaces/special characters</li>
            <li>Must represent your brand and not impersonate others</li>
        </ul>
    </div>

    <div id="emptyState" class="empty-state" style="display: none;">
        <div class="empty-state-icon">
            <i class="fas fa-id-badge"></i>
        </div>
        <h4>No SenderIDs Registered</h4>
        <p>Register your first SenderID to start sending SMS messages with your brand identity.</p>
        <button type="button" class="btn btn-primary" id="btnRegisterSenderIdEmpty">
            <i class="fas fa-plus me-2"></i>Register SenderID
        </button>
    </div>

    <div id="senderIdLibrary" class="senderid-table-container">
        <div class="search-filter-bar">
            <div class="search-box">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search SenderIDs...">
                </div>
            </div>
            <div class="filters-group">
                <select class="form-select form-select-sm" id="filterStatus" style="width: 140px;">
                    <option value="">All Status</option>
                    <option value="pending">Pending Review</option>
                    <option value="under-review">Under Review</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="suspended">Suspended</option>
                </select>
                <select class="form-select form-select-sm" id="filterUseCase" style="width: 140px;">
                    <option value="">All Use Cases</option>
                    <option value="otp">OTP / Verification</option>
                    <option value="marketing">Marketing</option>
                    <option value="transactional">Transactional</option>
                    <option value="alerts">Alerts / Notifications</option>
                </select>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table senderid-table" id="senderIdTable">
                <thead>
                    <tr>
                        <th data-sort="senderId" onclick="sortTable('senderId')">SenderID <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="brand" onclick="sortTable('brand')">Brand / Company <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="useCase" onclick="sortTable('useCase')">Use Case <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status" onclick="sortTable('status')">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="subaccount" onclick="sortTable('subaccount')">Subaccount <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="created" onclick="sortTable('created')">Created <i class="fas fa-sort sort-icon"></i></th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="senderIdTableBody">
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <div class="text-muted small">
                Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> SenderIDs
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="drawer-backdrop" id="registerDrawerBackdrop"></div>
<div class="drawer" id="registerDrawer">
    <div class="drawer-header">
        <h5><i class="fas fa-id-badge me-2 text-primary"></i>Register SenderID</h5>
        <button type="button" class="btn-close" id="registerDrawerClose"></button>
    </div>
    <div class="drawer-body">
        <form id="registerForm">
            <div class="mb-3">
                <label class="form-label form-label-required">SenderID</label>
                <input type="text" class="form-control senderid-input" id="inputSenderId" 
                       maxlength="11" placeholder="e.g. MYCOMPANY" autocomplete="off">
                <div class="d-flex justify-content-between">
                    <div class="validation-hint" id="senderIdHint">3-11 alphanumeric characters, must start with a letter</div>
                    <div class="char-counter"><span id="senderIdCharCount">0</span>/11</div>
                </div>
                <div class="invalid-feedback" id="senderIdError"></div>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-required">Brand / Company Name</label>
                <input type="text" class="form-control" id="inputBrand" 
                       placeholder="Your company or brand name" autocomplete="off">
                <div class="validation-hint">The legal entity or brand this SenderID represents</div>
                <div class="invalid-feedback" id="brandError"></div>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-required">Use Case</label>
                <select class="form-select" id="inputUseCase">
                    <option value="">Select use case...</option>
                    <option value="otp">OTP / Verification - One-time passwords, login codes</option>
                    <option value="marketing">Marketing - Promotional messages, offers</option>
                    <option value="transactional">Transactional - Order updates, confirmations</option>
                    <option value="alerts">Alerts / Notifications - Reminders, status updates</option>
                </select>
                <div class="invalid-feedback" id="useCaseError"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="inputDescription" rows="3" 
                          placeholder="Describe how this SenderID will be used..."></textarea>
                <div class="validation-hint">Help reviewers understand your intended use</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Subaccount</label>
                <select class="form-select" id="inputSubaccount">
                    <option value="">Main Account</option>
                    <option value="marketing">Marketing Department</option>
                    <option value="support">Customer Support</option>
                    <option value="operations">Operations</option>
                </select>
            </div>

            <div class="alert alert-warning small mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Review Process:</strong> SenderID registrations are typically reviewed within 1-2 business days. You'll receive an email notification once approved.
            </div>
        </form>
    </div>
    <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary" id="btnCancelRegister">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnSubmitRegister">
            <i class="fas fa-paper-plane me-1"></i>Submit for Review
        </button>
    </div>
</div>

<div class="drawer-backdrop" id="detailDrawerBackdrop"></div>
<div class="drawer" id="detailDrawer">
    <div class="drawer-header">
        <h5><i class="fas fa-id-badge me-2 text-primary"></i>SenderID Details</h5>
        <button type="button" class="btn-close" id="detailDrawerClose"></button>
    </div>
    <div class="drawer-body">
        <div class="mb-4">
            <h6 class="text-muted mb-3">Registration Information</h6>
            <div class="detail-row">
                <div class="detail-label">SenderID</div>
                <div class="detail-value"><span id="detailSenderId" class="senderid-name"></span></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Brand / Company</div>
                <div class="detail-value" id="detailBrand"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Use Case</div>
                <div class="detail-value" id="detailUseCase"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Description</div>
                <div class="detail-value" id="detailDescription"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Subaccount</div>
                <div class="detail-value" id="detailSubaccount"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status</div>
                <div class="detail-value" id="detailStatus"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Created</div>
                <div class="detail-value" id="detailCreated"></div>
            </div>
        </div>

        <div class="mb-4" id="rejectionReasonSection" style="display: none;">
            <h6 class="text-muted mb-3">Rejection Reason</h6>
            <div class="alert alert-danger small" id="rejectionReason"></div>
        </div>

        <div>
            <h6 class="text-muted mb-3">Audit History</h6>
            <div class="audit-timeline" id="auditTimeline">
            </div>
        </div>
    </div>
    <div class="drawer-footer" id="detailDrawerActions">
    </div>
</div>

<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suspendModalTitle">Suspend SenderID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="suspendModalMessage">Are you sure you want to suspend this SenderID?</p>
                <p class="text-muted small" id="suspendModalDescription">Suspended SenderIDs cannot be used for messaging until reactivated.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="btnConfirmSuspend">
                    <i class="fas fa-pause me-1"></i>Suspend
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Delete SenderID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete <strong id="deleteSenderId"></strong>?</p>
                <p class="text-muted small">This action cannot be undone. The SenderID will need to be re-registered and re-approved if needed in the future.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                    <i class="fas fa-trash-alt me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var senderIds = [
        {
            id: 'sid_001',
            senderId: 'QUICKSMS',
            brand: 'QuickSMS Ltd',
            useCase: 'transactional',
            description: 'Order confirmations and delivery updates',
            subaccount: 'Main Account',
            status: 'approved',
            created: '2024-01-15T10:30:00Z',
            auditHistory: [
                { action: 'Approved', user: 'Compliance Team', timestamp: '2024-01-16T14:22:00Z', type: 'approved' },
                { action: 'Under Review', user: 'System', timestamp: '2024-01-15T10:35:00Z', type: 'submitted' },
                { action: 'Submitted for Review', user: 'John Smith', timestamp: '2024-01-15T10:30:00Z', type: 'submitted' }
            ]
        },
        {
            id: 'sid_002',
            senderId: 'ALERTS',
            brand: 'QuickSMS Ltd',
            useCase: 'alerts',
            description: 'System alerts and notifications',
            subaccount: 'Operations',
            status: 'approved',
            created: '2024-02-01T09:00:00Z',
            auditHistory: [
                { action: 'Approved', user: 'Compliance Team', timestamp: '2024-02-02T11:45:00Z', type: 'approved' },
                { action: 'Under Review', user: 'System', timestamp: '2024-02-01T09:05:00Z', type: 'submitted' },
                { action: 'Submitted for Review', user: 'Jane Doe', timestamp: '2024-02-01T09:00:00Z', type: 'submitted' }
            ]
        },
        {
            id: 'sid_003',
            senderId: 'PROMO',
            brand: 'QuickSMS Ltd',
            useCase: 'marketing',
            description: 'Marketing campaigns and special offers',
            subaccount: 'Marketing Department',
            status: 'pending',
            created: '2024-03-10T14:20:00Z',
            auditHistory: [
                { action: 'Submitted for Review', user: 'Marketing Team', timestamp: '2024-03-10T14:20:00Z', type: 'submitted' }
            ]
        },
        {
            id: 'sid_004',
            senderId: 'VERIFY',
            brand: 'QuickSMS Ltd',
            useCase: 'otp',
            description: 'Two-factor authentication codes',
            subaccount: 'Main Account',
            status: 'under-review',
            created: '2024-03-12T16:45:00Z',
            auditHistory: [
                { action: 'Under Review', user: 'Compliance Team', timestamp: '2024-03-13T09:00:00Z', type: 'submitted' },
                { action: 'Submitted for Review', user: 'Tech Team', timestamp: '2024-03-12T16:45:00Z', type: 'submitted' }
            ]
        },
        {
            id: 'sid_005',
            senderId: 'BANK',
            brand: 'QuickSMS Ltd',
            useCase: 'transactional',
            description: 'Banking notifications',
            subaccount: 'Main Account',
            status: 'rejected',
            created: '2024-03-05T11:00:00Z',
            rejectionReason: 'SenderID "BANK" is a reserved term and cannot be registered without additional verification of financial institution status.',
            auditHistory: [
                { action: 'Rejected', user: 'Compliance Team', timestamp: '2024-03-06T10:30:00Z', type: 'rejected', reason: 'Reserved term - requires financial verification' },
                { action: 'Under Review', user: 'System', timestamp: '2024-03-05T11:05:00Z', type: 'submitted' },
                { action: 'Submitted for Review', user: 'John Smith', timestamp: '2024-03-05T11:00:00Z', type: 'submitted' }
            ]
        }
    ];

    var currentPage = 1;
    var pageSize = 10;
    var sortColumn = 'created';
    var sortDirection = 'desc';
    var selectedSenderId = null;

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if (!dateString || dateString === '-') return '-';
        var date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + 
               ' ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }

    function getStatusBadge(status) {
        var badges = {
            'pending': '<span class="badge badge-pending">Pending Review</span>',
            'under-review': '<span class="badge badge-under-review">Under Review</span>',
            'approved': '<span class="badge badge-approved">Approved</span>',
            'rejected': '<span class="badge badge-rejected">Rejected</span>',
            'suspended': '<span class="badge badge-suspended">Suspended</span>'
        };
        return badges[status] || status;
    }

    function getUseCaseBadge(useCase) {
        var badges = {
            'otp': '<span class="badge badge-otp">OTP / Verification</span>',
            'marketing': '<span class="badge badge-marketing">Marketing</span>',
            'transactional': '<span class="badge badge-transactional">Transactional</span>',
            'alerts': '<span class="badge badge-alerts">Alerts</span>'
        };
        return badges[useCase] || useCase;
    }

    function filterSenderIds() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#filterStatus').val();
        var useCase = $('#filterUseCase').val();

        return senderIds.filter(function(item) {
            var matchSearch = !search || 
                item.senderId.toLowerCase().includes(search) ||
                item.brand.toLowerCase().includes(search) ||
                (item.description && item.description.toLowerCase().includes(search));
            var matchStatus = !status || item.status === status;
            var matchUseCase = !useCase || item.useCase === useCase;
            return matchSearch && matchStatus && matchUseCase;
        });
    }

    function sortSenderIds(data) {
        return data.sort(function(a, b) {
            var aVal = a[sortColumn] || '';
            var bVal = b[sortColumn] || '';
            if (sortColumn === 'created') {
                aVal = new Date(aVal);
                bVal = new Date(bVal);
            }
            if (sortDirection === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });
    }

    function renderTable() {
        var filtered = filterSenderIds();
        var sorted = sortSenderIds(filtered);
        var start = (currentPage - 1) * pageSize;
        var paged = sorted.slice(start, start + pageSize);

        if (sorted.length === 0) {
            $('#senderIdLibrary').hide();
            $('#emptyState').show();
            return;
        }

        $('#emptyState').hide();
        $('#senderIdLibrary').show();

        var html = '';
        paged.forEach(function(item) {
            html += '<tr data-id="' + item.id + '">';
            html += '<td><span class="senderid-name">' + escapeHtml(item.senderId) + '</span></td>';
            html += '<td>' + escapeHtml(item.brand) + '</td>';
            html += '<td>' + getUseCaseBadge(item.useCase) + '</td>';
            html += '<td>' + getStatusBadge(item.status) + '</td>';
            html += '<td>' + escapeHtml(item.subaccount) + '</td>';
            html += '<td>' + formatDate(item.created) + '</td>';
            html += '<td class="text-center">';
            html += '<div class="dropdown">';
            html += '<button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>';
            html += '<ul class="dropdown-menu dropdown-menu-end">';
            html += '<li><a class="dropdown-item btn-view-details" href="#" data-id="' + item.id + '"><i class="fas fa-eye me-2"></i>View Details</a></li>';
            if (item.status === 'approved') {
                html += '<li><a class="dropdown-item btn-suspend" href="#" data-id="' + item.id + '"><i class="fas fa-pause me-2"></i>Suspend</a></li>';
            }
            if (item.status === 'suspended') {
                html += '<li><a class="dropdown-item btn-reactivate" href="#" data-id="' + item.id + '"><i class="fas fa-play me-2"></i>Reactivate</a></li>';
            }
            if (item.status === 'rejected' || item.status === 'pending') {
                html += '<li><a class="dropdown-item text-danger btn-delete" href="#" data-id="' + item.id + '"><i class="fas fa-trash-alt me-2"></i>Delete</a></li>';
            }
            html += '</ul>';
            html += '</div>';
            html += '</td>';
            html += '</tr>';
        });

        $('#senderIdTableBody').html(html);
        $('#showingCount').text(paged.length);
        $('#totalCount').text(sorted.length);

        renderPagination(sorted.length);
        updateSortIndicators();
    }

    function renderPagination(total) {
        var totalPages = Math.ceil(total / pageSize);
        var html = '';

        if (totalPages <= 1) {
            $('#paginationContainer').html('');
            return;
        }

        html += '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">';
        html += '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '">&laquo;</a></li>';

        for (var i = 1; i <= totalPages; i++) {
            html += '<li class="page-item ' + (currentPage === i ? 'active' : '') + '">';
            html += '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
        }

        html += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">';
        html += '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '">&raquo;</a></li>';

        $('#paginationContainer').html(html);
    }

    function updateSortIndicators() {
        $('.senderid-table thead th').removeClass('sorted');
        $('.senderid-table thead th .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        var $th = $('.senderid-table thead th[data-sort="' + sortColumn + '"]');
        $th.addClass('sorted');
        $th.find('.sort-icon').removeClass('fa-sort').addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
    }

    window.sortTable = function(column) {
        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }
        currentPage = 1;
        renderTable();
    };

    function openRegisterDrawer() {
        $('#registerForm')[0].reset();
        $('#inputSenderId').removeClass('is-invalid');
        $('#inputBrand').removeClass('is-invalid');
        $('#inputUseCase').removeClass('is-invalid');
        $('#senderIdCharCount').text('0');
        $('#registerDrawerBackdrop').addClass('show');
        $('#registerDrawer').addClass('show');
    }

    function closeRegisterDrawer() {
        $('#registerDrawerBackdrop').removeClass('show');
        $('#registerDrawer').removeClass('show');
    }

    function openDetailDrawer(id) {
        var item = senderIds.find(function(s) { return s.id === id; });
        if (!item) return;

        selectedSenderId = item;

        $('#detailSenderId').text(item.senderId);
        $('#detailBrand').text(item.brand);
        $('#detailUseCase').html(getUseCaseBadge(item.useCase));
        $('#detailDescription').text(item.description || '-');
        $('#detailSubaccount').text(item.subaccount);
        $('#detailStatus').html(getStatusBadge(item.status));
        $('#detailCreated').text(formatDateTime(item.created));

        if (item.status === 'rejected' && item.rejectionReason) {
            $('#rejectionReasonSection').show();
            $('#rejectionReason').text(item.rejectionReason);
        } else {
            $('#rejectionReasonSection').hide();
        }

        var auditHtml = '';
        item.auditHistory.forEach(function(audit) {
            auditHtml += '<div class="audit-item ' + audit.type + '">';
            auditHtml += '<div class="audit-action">' + escapeHtml(audit.action) + '</div>';
            auditHtml += '<div class="audit-user">by ' + escapeHtml(audit.user) + '</div>';
            auditHtml += '<div class="audit-time">' + formatDateTime(audit.timestamp) + '</div>';
            if (audit.reason) {
                auditHtml += '<div class="small text-muted mt-1">' + escapeHtml(audit.reason) + '</div>';
            }
            auditHtml += '</div>';
        });
        $('#auditTimeline').html(auditHtml);

        var actionsHtml = '';
        if (item.status === 'approved') {
            actionsHtml += '<button type="button" class="btn btn-warning btn-sm" id="btnDetailSuspend"><i class="fas fa-pause me-1"></i>Suspend</button>';
        } else if (item.status === 'suspended') {
            actionsHtml += '<button type="button" class="btn btn-success btn-sm" id="btnDetailReactivate"><i class="fas fa-play me-1"></i>Reactivate</button>';
        }
        if (item.status === 'rejected' || item.status === 'pending') {
            actionsHtml += '<button type="button" class="btn btn-danger btn-sm" id="btnDetailDelete"><i class="fas fa-trash-alt me-1"></i>Delete</button>';
        }
        $('#detailDrawerActions').html(actionsHtml);

        $('#detailDrawerBackdrop').addClass('show');
        $('#detailDrawer').addClass('show');
    }

    function closeDetailDrawer() {
        $('#detailDrawerBackdrop').removeClass('show');
        $('#detailDrawer').removeClass('show');
        selectedSenderId = null;
    }

    function validateSenderId(value) {
        if (!value) return { valid: false, message: 'SenderID is required' };
        if (value.length < 3) return { valid: false, message: 'Minimum 3 characters required' };
        if (value.length > 11) return { valid: false, message: 'Maximum 11 characters allowed' };
        if (!/^[A-Za-z]/.test(value)) return { valid: false, message: 'Must start with a letter' };
        if (!/^[A-Za-z0-9]+$/.test(value)) return { valid: false, message: 'Only letters and numbers allowed' };

        var existing = senderIds.find(function(s) { return s.senderId.toUpperCase() === value.toUpperCase(); });
        if (existing) return { valid: false, message: 'This SenderID is already registered' };

        return { valid: true };
    }

    $('#inputSenderId').on('input', function() {
        var val = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, '');
        $(this).val(val);
        $('#senderIdCharCount').text(val.length);
        
        var result = validateSenderId(val);
        if (val && !result.valid) {
            $(this).addClass('is-invalid');
            $('#senderIdError').text(result.message);
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    $('#btnRegisterSenderId, #btnRegisterSenderIdEmpty').on('click', openRegisterDrawer);
    $('#registerDrawerClose, #registerDrawerBackdrop, #btnCancelRegister').on('click', closeRegisterDrawer);
    $('#detailDrawerClose, #detailDrawerBackdrop').on('click', closeDetailDrawer);

    $('#btnSubmitRegister').on('click', function() {
        var senderId = $('#inputSenderId').val().toUpperCase();
        var brand = $('#inputBrand').val().trim();
        var useCase = $('#inputUseCase').val();
        var description = $('#inputDescription').val().trim();
        var subaccount = $('#inputSubaccount option:selected').text();

        var isValid = true;

        var senderIdResult = validateSenderId(senderId);
        if (!senderIdResult.valid) {
            $('#inputSenderId').addClass('is-invalid');
            $('#senderIdError').text(senderIdResult.message);
            isValid = false;
        }

        if (!brand) {
            $('#inputBrand').addClass('is-invalid');
            $('#brandError').text('Brand name is required');
            isValid = false;
        } else {
            $('#inputBrand').removeClass('is-invalid');
        }

        if (!useCase) {
            $('#inputUseCase').addClass('is-invalid');
            $('#useCaseError').text('Use case is required');
            isValid = false;
        } else {
            $('#inputUseCase').removeClass('is-invalid');
        }

        if (!isValid) return;

        var newEntry = {
            id: 'sid_' + Date.now(),
            senderId: senderId,
            brand: brand,
            useCase: useCase,
            description: description,
            subaccount: subaccount || 'Main Account',
            status: 'pending',
            created: new Date().toISOString(),
            auditHistory: [
                { action: 'Submitted for Review', user: 'Current User', timestamp: new Date().toISOString(), type: 'submitted' }
            ]
        };

        senderIds.unshift(newEntry);
        closeRegisterDrawer();
        renderTable();

        if (typeof showSuccessToast === 'function') {
            showSuccessToast('SenderID "' + senderId + '" submitted for review');
        }
    });

    $(document).on('click', '.btn-view-details', function(e) {
        e.preventDefault();
        openDetailDrawer($(this).data('id'));
    });

    $(document).on('click', '.btn-suspend, #btnDetailSuspend', function(e) {
        e.preventDefault();
        var id = $(this).data('id') || (selectedSenderId && selectedSenderId.id);
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            $('#suspendModal').data('id', id);
            $('#suspendModalTitle').text('Suspend SenderID');
            $('#suspendModalMessage').html('Are you sure you want to suspend <strong>' + item.senderId + '</strong>?');
            $('#btnConfirmSuspend').removeClass('btn-success').addClass('btn-warning').html('<i class="fas fa-pause me-1"></i>Suspend');
            new bootstrap.Modal($('#suspendModal')[0]).show();
        }
    });

    $(document).on('click', '.btn-reactivate, #btnDetailReactivate', function(e) {
        e.preventDefault();
        var id = $(this).data('id') || (selectedSenderId && selectedSenderId.id);
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            $('#suspendModal').data('id', id);
            $('#suspendModalTitle').text('Reactivate SenderID');
            $('#suspendModalMessage').html('Are you sure you want to reactivate <strong>' + item.senderId + '</strong>?');
            $('#btnConfirmSuspend').removeClass('btn-warning').addClass('btn-success').html('<i class="fas fa-play me-1"></i>Reactivate');
            new bootstrap.Modal($('#suspendModal')[0]).show();
        }
    });

    $('#btnConfirmSuspend').on('click', function() {
        var id = $('#suspendModal').data('id');
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            var action = item.status === 'suspended' ? 'reactivate' : 'suspend';
            item.status = action === 'suspend' ? 'suspended' : 'approved';
            item.auditHistory.unshift({
                action: action === 'suspend' ? 'Suspended' : 'Reactivated',
                user: 'Current User',
                timestamp: new Date().toISOString(),
                type: action === 'suspend' ? 'rejected' : 'approved'
            });
            bootstrap.Modal.getInstance($('#suspendModal')[0]).hide();
            closeDetailDrawer();
            renderTable();
            if (typeof showSuccessToast === 'function') {
                showSuccessToast('SenderID ' + (action === 'suspend' ? 'suspended' : 'reactivated'));
            }
        }
    });

    $(document).on('click', '.btn-delete, #btnDetailDelete', function(e) {
        e.preventDefault();
        var id = $(this).data('id') || (selectedSenderId && selectedSenderId.id);
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            $('#deleteModal').data('id', id);
            $('#deleteSenderId').text(item.senderId);
            new bootstrap.Modal($('#deleteModal')[0]).show();
        }
    });

    $('#btnConfirmDelete').on('click', function() {
        var id = $('#deleteModal').data('id');
        senderIds = senderIds.filter(function(s) { return s.id !== id; });
        bootstrap.Modal.getInstance($('#deleteModal')[0]).hide();
        closeDetailDrawer();
        renderTable();
        if (typeof showSuccessToast === 'function') {
            showSuccessToast('SenderID deleted');
        }
    });

    $('#searchInput').on('input', function() {
        currentPage = 1;
        renderTable();
    });

    $('#filterStatus, #filterUseCase').on('change', function() {
        currentPage = 1;
        renderTable();
    });

    $('#btnResetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#filterStatus').val('');
        $('#filterUseCase').val('');
        currentPage = 1;
        renderTable();
    });

    $(document).on('click', '#paginationContainer .page-link', function(e) {
        e.preventDefault();
        var page = parseInt($(this).data('page'));
        if (page && page > 0) {
            currentPage = page;
            renderTable();
        }
    });

    renderTable();
});
</script>
@endpush

@extends('layouts.admin')

@section('page_title', 'Audit Logs - Admin')

@push('styles')
<style>
.admin-blue { color: #1e3a5f; }
.admin-blue-bg { background-color: #1e3a5f; }

.top-level-tabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 1.5rem;
}
.top-level-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    margin-bottom: -2px;
    transition: all 0.15s ease;
}
.top-level-tabs .nav-link:hover {
    border-bottom-color: rgba(30, 58, 95, 0.3);
    color: #1e3a5f;
}
.top-level-tabs .nav-link.active {
    border-bottom-color: #1e3a5f;
    color: #1e3a5f;
    background: transparent;
}
.top-level-tabs .nav-link i {
    margin-right: 0.5rem;
}

.admin-audit-badge {
    background-color: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
    font-size: 0.65rem;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
    margin-left: 0.5rem;
    font-weight: 600;
}

.btn-admin-primary {
    background-color: #1e3a5f;
    border-color: #1e3a5f;
    color: #fff;
}
.btn-admin-primary:hover {
    background-color: #152a45;
    border-color: #152a45;
    color: #fff;
}
.btn-admin-outline {
    background-color: transparent;
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.btn-admin-outline:hover {
    background-color: rgba(30, 58, 95, 0.08);
    border-color: #1e3a5f;
    color: #1e3a5f;
}

.admin-compliance-card { 
    background: linear-gradient(135deg, rgba(30, 58, 95, 0.05) 0%, rgba(30, 58, 95, 0.02) 100%); 
    border-radius: 0.5rem; 
    padding: 1rem; 
    margin-bottom: 1rem; 
}
.admin-compliance-card h6 { 
    color: #1e3a5f; 
    font-size: 0.875rem; 
    margin-bottom: 0.5rem; 
}
.admin-compliance-card .compliance-stat { 
    font-size: 1.25rem; 
    font-weight: 600; 
    color: #1e3a5f; 
}

.admin-severity-badge-low { background-color: rgba(108, 117, 125, 0.15); color: #6c757d; }
.admin-severity-badge-medium { background-color: rgba(30, 58, 95, 0.15); color: #1e3a5f; }
.admin-severity-badge-high { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.admin-severity-badge-critical { background-color: rgba(220, 53, 69, 0.25); color: #dc3545; font-weight: 600; }

.admin-category-badge-admin { background-color: rgba(30, 58, 95, 0.2); color: #1e3a5f; }
.admin-category-badge-impersonation { background-color: rgba(220, 53, 69, 0.2); color: #dc3545; }

.admin-audit-table-container { 
    max-height: 600px; 
    overflow-y: auto; 
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
}
.admin-audit-logs-table {
    width: 100%;
    border-collapse: collapse;
}
.admin-audit-logs-table thead th {
    padding: 0.5rem 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    color: #495057;
    position: sticky;
    top: 0;
    z-index: 1;
}
.admin-audit-logs-table tbody td {
    padding: 0.5rem 0.35rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
.admin-audit-logs-table tbody tr {
    cursor: pointer;
}
.admin-audit-logs-table tbody tr:hover {
    background-color: rgba(30, 58, 95, 0.03);
}
.admin-sortable-header {
    cursor: pointer;
    user-select: none;
}
.admin-sortable-header:hover {
    background-color: rgba(30, 58, 95, 0.08);
}
.admin-sortable-header .sort-icon {
    color: #ccc;
    font-size: 0.7rem;
}
.admin-sortable-header .sort-icon.active {
    color: #1e3a5f;
}

.admin-filter-panel { 
    background-color: rgba(30, 58, 95, 0.05) !important; 
    border: 1px solid #e9ecef;
}

.admin-customer-search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #fff;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 250px;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.admin-customer-search-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.875rem;
}
.admin-customer-search-item:hover {
    background-color: rgba(30, 58, 95, 0.05);
}
.admin-customer-search-item:last-child {
    border-bottom: none;
}
.admin-customer-search-item .customer-name {
    font-weight: 500;
}
.admin-customer-search-item .customer-id {
    font-size: 0.75rem;
    color: #6c757d;
}
.admin-selected-customer-badge {
    display: inline-flex;
    align-items: center;
    background-color: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}
.admin-selected-customer-badge .clear-btn {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.admin-selected-customer-badge .clear-btn:hover {
    opacity: 1;
}

.high-risk-toggle .form-check-input:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

.admin-log-detail-section { 
    padding: 1rem; 
    background-color: #fafafa; 
    border-radius: 0.5rem; 
    margin-bottom: 1rem; 
}
.admin-log-detail-section h6 { 
    color: #1e3a5f; 
    margin-bottom: 0.75rem; 
    font-size: 0.875rem; 
}
.admin-log-detail-row { 
    display: flex; 
    margin-bottom: 0.5rem; 
}
.admin-log-detail-label { 
    width: 140px; 
    font-weight: 500; 
    color: #6c757d; 
    font-size: 0.8125rem; 
}
.admin-log-detail-value { 
    flex: 1; 
    font-size: 0.8125rem; 
    color: #212529; 
}
</style>
@endpush

@section('content')
<div class="row page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Security & Compliance</a></li>
        <li class="breadcrumb-item active">Audit Logs</li>
    </ol>
</div>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h3 class="mb-1">Audit Logs</h3>
        <p class="text-muted mb-0">Immutable audit trail with 7-year retention and cryptographic integrity</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-admin-outline btn-sm" id="verifyIntegrityBtn">
            <i class="fas fa-shield-alt me-1"></i>Verify Integrity
        </button>
    </div>
</div>

<ul class="nav top-level-tabs" id="auditModeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="customer-audit-tab" data-bs-toggle="tab" data-bs-target="#customerAuditPane" type="button" role="tab">
            <i class="fas fa-users"></i>Customer Audit Viewer
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="admin-audit-tab" data-bs-toggle="tab" data-bs-target="#adminAuditPane" type="button" role="tab">
            <i class="fas fa-user-shield"></i>Internal Admin Audit
            <span class="admin-audit-badge">INTERNAL</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="auditModeTabsContent">
    <div class="tab-pane fade show active" id="customerAuditPane" role="tabpanel">
        @include('shared.partials.audit-log-component', [
            'themeColor' => '#1e3a5f',
            'themeColorRgb' => '30, 58, 95',
            'themeName' => 'admin',
            'prefix' => 'customer',
            'showCustomerSelector' => true,
            'showSubAccountFilter' => true,
            'isAdminContext' => true,
            'cardTitle' => 'Customer Activity Audit',
            'cardSubtitle' => 'View audit logs across all customer accounts'
        ])
    </div>

    <div class="tab-pane fade" id="adminAuditPane" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Internal Admin Audit Trail</h5>
                    <small class="text-muted">All admin console actions with immutable logging</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm" id="adminActorFilter" style="max-width: 200px;">
                        <option value="">All Admin Users</option>
                        <option value="admin-001">Sarah Johnson</option>
                        <option value="admin-002">James Mitchell</option>
                        <option value="admin-003">Emily Chen</option>
                    </select>
                    <button type="button" class="btn btn-admin-outline btn-sm" data-bs-toggle="collapse" data-bs-target="#adminFiltersPanel">
                        <i class="fas fa-filter me-1"></i>Filters
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-admin-outline btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Export Format</h6></li>
                            <li><a class="dropdown-item" href="#" id="adminExportCsv"><i class="fas fa-file-csv me-2 text-success"></i>CSV (.csv)</a></li>
                            <li><a class="dropdown-item" href="#" id="adminExportExcel"><i class="fas fa-file-excel me-2 text-success"></i>Excel (.xlsx)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-item-text small text-muted"><i class="fas fa-info-circle me-1"></i>Exports current filtered view</span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="admin-compliance-card">
                            <h6><i class="fas fa-user-shield me-2"></i>Admin Actions (24h)</h6>
                            <div class="compliance-stat" id="adminActions24h">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="admin-compliance-card">
                            <h6><i class="fas fa-user-secret me-2"></i>Impersonations (7d)</h6>
                            <div class="compliance-stat" id="impersonations7d">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="admin-compliance-card">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Security Events (24h)</h6>
                            <div class="compliance-stat text-danger" id="adminSecurityEvents">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="admin-compliance-card">
                            <h6><i class="fas fa-ban me-2"></i>Blocked Logins (24h)</h6>
                            <div class="compliance-stat text-warning" id="adminBlockedLogins">0</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="adminSearchInput" placeholder="Search by admin email, event type, or target...">
                    </div>
                </div>

                <div class="collapse mb-3" id="adminFiltersPanel">
                    <div class="card card-body border-0 rounded-3 admin-filter-panel">
                        <div class="row g-3 align-items-end">
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Date From</label>
                                <input type="date" class="form-control form-control-sm" id="adminDateFromFilter">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Date To</label>
                                <input type="date" class="form-control form-control-sm" id="adminDateToFilter">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Module</label>
                                <select class="form-select form-select-sm" id="adminModuleFilter">
                                    <option value="">All Modules</option>
                                    <option value="admin_users">Admin Users</option>
                                    <option value="security">Security</option>
                                    <option value="impersonation">Impersonation</option>
                                    <option value="billing">Billing</option>
                                    <option value="approvals">Approvals</option>
                                    <option value="numbers">Numbers</option>
                                    <option value="accounts">Accounts</option>
                                    <option value="data_access">Data Access</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Action</label>
                                <select class="form-select form-select-sm" id="adminEventTypeFilter">
                                    <option value="">All Actions</option>
                                    <optgroup label="Admin User Lifecycle">
                                        <option value="ADMIN_USER_INVITED">User Invited</option>
                                        <option value="ADMIN_USER_SUSPENDED">User Suspended</option>
                                        <option value="ADMIN_USER_REACTIVATED">User Reactivated</option>
                                    </optgroup>
                                    <optgroup label="Security Actions">
                                        <option value="ADMIN_USER_PASSWORD_RESET">Password Reset</option>
                                        <option value="ADMIN_USER_MFA_RESET">MFA Reset</option>
                                        <option value="ADMIN_USER_SESSIONS_REVOKED">Sessions Revoked</option>
                                        <option value="LOGIN_BLOCKED_BY_IP">Login Blocked by IP</option>
                                    </optgroup>
                                    <optgroup label="Impersonation">
                                        <option value="IMPERSONATION_STARTED">Impersonation Started</option>
                                        <option value="IMPERSONATION_ENDED">Impersonation Ended</option>
                                        <option value="SUPPORT_MODE_ENABLED">Support Mode Enabled</option>
                                    </optgroup>
                                    <optgroup label="Billing">
                                        <option value="PRICING_EDITED">Pricing Edited</option>
                                        <option value="BILLING_MODE_CHANGED">Billing Mode Changed</option>
                                        <option value="CREDIT_LIMIT_CHANGED">Credit Limit Changed</option>
                                        <option value="INVOICE_CREATED_BY_ADMIN">Invoice Created</option>
                                    </optgroup>
                                    <optgroup label="Approvals">
                                        <option value="SENDERID_APPROVED">SenderID Approved</option>
                                        <option value="SENDERID_REJECTED">SenderID Rejected</option>
                                        <option value="RCS_AGENT_APPROVED">RCS Agent Approved</option>
                                        <option value="CAMPAIGN_APPROVED_BY_ADMIN">Campaign Approved</option>
                                        <option value="TEMPLATE_SUSPENDED">Template Suspended</option>
                                    </optgroup>
                                    <optgroup label="Numbers">
                                        <option value="NUMBER_ASSIGNED">Number Assigned</option>
                                        <option value="NUMBER_UNASSIGNED">Number Unassigned</option>
                                    </optgroup>
                                    <optgroup label="Account Actions">
                                        <option value="ACCOUNT_SUSPENDED_BY_ADMIN">Account Suspended</option>
                                        <option value="ACCOUNT_REACTIVATED_BY_ADMIN">Account Reactivated</option>
                                    </optgroup>
                                    <optgroup label="Data Access">
                                        <option value="ADMIN_EXPORT_INITIATED">Export Initiated</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Admin User</label>
                                <select class="form-select form-select-sm" id="adminActorFilter">
                                    <option value="">All Admin Users</option>
                                    <option value="sarah.johnson@quicksms.co.uk">Sarah Johnson</option>
                                    <option value="james.mitchell@quicksms.co.uk">James Mitchell</option>
                                    <option value="emily.chen@quicksms.co.uk">Emily Chen</option>
                                    <option value="david.lee@quicksms.co.uk">David Lee</option>
                                    <option value="anna.williams@quicksms.co.uk">Anna Williams</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Result</label>
                                <select class="form-select form-select-sm" id="adminResultFilter">
                                    <option value="">All Results</option>
                                    <option value="success">Success</option>
                                    <option value="failure">Failure</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end mt-2">
                            <div class="col-6 col-md-3 position-relative" id="adminCustomerImpactedContainer">
                                <label class="form-label small fw-bold">Customer Impacted</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fas fa-building"></i></span>
                                    <input type="text" class="form-control" id="adminCustomerImpactedSearch" placeholder="Search customers..." autocomplete="off">
                                </div>
                                <div class="admin-customer-search-results" id="adminCustomerImpactedResults" style="display: none;"></div>
                                <input type="hidden" id="adminCustomerImpactedId" value="">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">IP Address</label>
                                <input type="text" class="form-control form-control-sm" id="adminIpFilter" placeholder="e.g. 10.0.1.50">
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="adminHighRiskOnlyFilter">
                                    <label class="form-check-label small fw-bold" for="adminHighRiskOnlyFilter">
                                        <i class="fas fa-exclamation-triangle text-danger me-1"></i>High-risk only
                                    </label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-admin-primary btn-sm" id="adminApplyFiltersBtn">
                                    <i class="fas fa-check me-1"></i>Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="adminClearFiltersBtn">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small"><span id="adminTotalFiltered">0</span> events</span>
                    <div class="btn-group" role="group" style="font-size: 0.75rem;">
                        <button type="button" class="btn btn-outline-secondary btn-sm active" id="adminPaginationModeBtn">
                            <i class="fas fa-list"></i> Paginated
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="adminInfiniteScrollModeBtn">
                            <i class="fas fa-stream"></i> Scroll
                        </button>
                    </div>
                </div>

                <div class="admin-audit-table-container" id="adminAuditTableContainer">
                    <table class="admin-audit-logs-table" id="adminAuditLogsTable">
                        <thead>
                            <tr>
                                <th style="width: 140px;" class="admin-sortable-header" data-sort="timestamp">Timestamp <i class="fas fa-sort-down ms-1 sort-icon active"></i></th>
                                <th class="admin-sortable-header" data-sort="actor">Admin User <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th class="admin-sortable-header" data-sort="customer">Customer Impacted <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th style="width: 110px;" class="admin-sortable-header" data-sort="module">Module <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th class="admin-sortable-header" data-sort="action">Action <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th style="width: 80px;" class="admin-sortable-header" data-sort="result">Result <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th style="width: 85px;" class="admin-sortable-header" data-sort="risk">Risk <i class="fas fa-sort ms-1 sort-icon"></i></th>
                            </tr>
                        </thead>
                        <tbody id="adminAuditLogsTableBody">
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4" id="adminPaginationRow">
                    <div class="text-muted small">
                        Showing <span id="adminShowingStart">0</span>-<span id="adminShowingEnd">0</span> of <span id="adminPaginationTotal">0</span> events
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="adminPaginationControls"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="logDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2 admin-blue"></i>Audit Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-admin-outline btn-sm" id="copyLogDetail">
                    <i class="fas fa-copy me-1"></i>Copy to Clipboard
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="integrityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shield-alt me-2 admin-blue"></i>Log Integrity Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4" id="integrityChecking">
                    <div class="spinner-border admin-blue mb-3" role="status"></div>
                    <p class="mb-0">Verifying log integrity...</p>
                </div>
                <div id="integrityResult" style="display: none;">
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-success">Integrity Verified</h5>
                        <p class="text-muted mb-0">All audit log entries have valid cryptographic signatures.</p>
                    </div>
                    <hr>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Records Verified</span>
                            <strong id="recordsVerified">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Hash Algorithm</span>
                            <strong>SHA-256</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Verification Time</span>
                            <strong id="verificationTime">0ms</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Chain Status</span>
                            <span class="badge" style="background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c;">Unbroken</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    console.log('[AdminAuditLogs] Module initialized');

    var allCustomers = [
        { id: 'acc-001', name: 'Acme Corp', account_number: 'ACC-001' },
        { id: 'acc-002', name: 'TechStart Ltd', account_number: 'ACC-002' },
        { id: 'acc-003', name: 'HealthFirst UK', account_number: 'ACC-003' },
        { id: 'acc-004', name: 'RetailMax', account_number: 'ACC-004' },
        { id: 'acc-005', name: 'ServicePro', account_number: 'ACC-005' },
        { id: 'acc-006', name: 'Digital Agency Co', account_number: 'ACC-006' },
        { id: 'acc-007', name: 'Finance Solutions Ltd', account_number: 'ACC-007' },
        { id: 'acc-008', name: 'Healthcare Partners', account_number: 'ACC-008' },
        { id: 'acc-009', name: 'E-Commerce Hub', account_number: 'ACC-009' },
        { id: 'acc-010', name: 'Marketing Experts', account_number: 'ACC-010' }
    ];

    var subAccountsByCustomer = {
        'acc-001': [
            { id: 'sa-001-1', name: 'Marketing Department' },
            { id: 'sa-001-2', name: 'Sales Team' },
            { id: 'sa-001-3', name: 'Customer Support' }
        ],
        'acc-002': [
            { id: 'sa-002-1', name: 'Development' },
            { id: 'sa-002-2', name: 'Operations' }
        ],
        'acc-003': [
            { id: 'sa-003-1', name: 'Patient Comms' },
            { id: 'sa-003-2', name: 'Staff Notifications' }
        ]
    };

    var customerLogs = [];
    var adminLogs = [];
    var customerPage = 1;
    var adminPage = 1;
    var itemsPerPage = 25;
    var selectedCustomerId = null;

    function getDataSeparationRules() {
        return {
            customerAuditViewer: {
                description: 'Tenant-scoped customer audit events',
                requiredFields: ['tenant_id', 'customer'],
                allowedActorTypes: ['customer_user', 'system'],
                forbiddenEventTypes: INTERNAL_ADMIN_ONLY_EVENTS,
                scopeValidation: function(log) {
                    return log.tenant_id && log.isCustomerFacing === true;
                }
            },
            internalAdminAudit: {
                description: 'Admin actor-based internal events',
                requiredFields: ['admin_actor_id', 'actor'],
                allowedActorTypes: ['admin'],
                forbiddenEventTypes: [],
                scopeValidation: function(log) {
                    return log.admin_actor_id && log.isAdminEvent === true;
                }
            }
        };
    }

    var IMMUTABILITY_CONTROLS = {
        appendOnly: true,
        allowDelete: false,
        allowModify: false,
        retentionYears: 7,
        hashAlgorithm: 'SHA-256'
    };

    function validateDataSeparation(logs, viewerType) {
        var allRules = getDataSeparationRules();
        var rules = allRules[viewerType];
        if (!rules) {
            console.error('[DataSeparation] Unknown viewer type:', viewerType);
            return { valid: false, errors: ['Unknown viewer type'] };
        }

        var errors = [];
        var validLogs = [];

        logs.forEach(function(log, index) {
            var logErrors = [];

            if (!rules.scopeValidation(log)) {
                logErrors.push('Scope validation failed');
            }

            if (viewerType === 'customerAuditViewer') {
                rules.forbiddenEventTypes.forEach(function(forbidden) {
                    if (log.action === forbidden || log.eventType === forbidden) {
                        logErrors.push('Forbidden event type in customer viewer: ' + forbidden);
                    }
                });
            }

            if (logErrors.length === 0) {
                validLogs.push(log);
            } else {
                errors.push({ logId: log.id, errors: logErrors });
            }
        });

        if (errors.length > 0) {
            console.warn('[DataSeparation] ' + viewerType + ' validation errors:', errors.length);
        }

        return { valid: errors.length === 0, validLogs: validLogs, errors: errors };
    }

    function enforceImmutability(operation, logId) {
        if (operation === 'delete' && !IMMUTABILITY_CONTROLS.allowDelete) {
            console.error('[Immutability] DELETE operation blocked for log:', logId);
            return false;
        }
        if (operation === 'modify' && !IMMUTABILITY_CONTROLS.allowModify) {
            console.error('[Immutability] MODIFY operation blocked for log:', logId);
            return false;
        }
        return true;
    }

    function appendAuditLog(log, targetStore) {
        if (!IMMUTABILITY_CONTROLS.appendOnly) {
            console.error('[Immutability] Append-only mode is disabled');
            return false;
        }

        log._immutable = true;
        log._appendedAt = new Date().toISOString();
        log._hashChain = generateLogHash(log);
        
        if (targetStore === 'customer') {
            customerLogs.unshift(log);
        } else if (targetStore === 'admin') {
            adminLogs.unshift(log);
        }
        
        console.log('[Immutability] Log appended:', log.id, 'to', targetStore);
        return true;
    }

    function generateLogHash(log) {
        var content = JSON.stringify({
            id: log.id,
            timestamp: log.timestamp,
            actor: log.actor,
            action: log.action || log.eventType
        });
        return 'SHA256:' + btoa(content).substring(0, 16);
    }

    function init() {
        console.log('[DataSeparation] Initializing with strict data separation');
        console.log('[Immutability] Append-only:', IMMUTABILITY_CONTROLS.appendOnly, 
                    '| Retention:', IMMUTABILITY_CONTROLS.retentionYears, 'years');

        customerLogs = generateMockCustomerLogs();
        adminLogs = generateMockAdminLogs();

        var customerValidation = validateDataSeparation(customerLogs, 'customerAuditViewer');
        var adminValidation = validateDataSeparation(adminLogs, 'internalAdminAudit');

        console.log('[DataSeparation] Customer logs validated:', customerValidation.validLogs.length, '/', customerLogs.length);
        console.log('[DataSeparation] Admin logs validated:', adminValidation.validLogs.length, '/', adminLogs.length);

        renderCustomerLogs();
        renderAdminLogs();
        updateAdminStats();
        bindCustomerSelectorEvents();
        bindAdminFilterEvents();
        bindEvents();
    }

    var CUSTOMER_FACING_EVENT_TYPES = [
        'USER_CREATED', 'USER_INVITED', 'USER_SUSPENDED', 'USER_REACTIVATED', 'USER_DELETED',
        'LOGIN_SUCCESS', 'LOGIN_FAILED', 'PASSWORD_CHANGED', 'PASSWORD_RESET_REQUESTED',
        'MFA_ENABLED', 'MFA_DISABLED', 'MFA_VERIFIED',
        'ROLE_CHANGED', 'PERMISSION_GRANTED', 'PERMISSION_REVOKED',
        'SUB_ACCOUNT_CREATED', 'SUB_ACCOUNT_UPDATED', 'SUB_ACCOUNT_DELETED',
        'CAMPAIGN_CREATED', 'CAMPAIGN_SUBMITTED', 'CAMPAIGN_SENT', 'CAMPAIGN_CANCELLED',
        'CONTACT_CREATED', 'CONTACT_UPDATED', 'CONTACT_DELETED', 'CONTACT_LIST_IMPORTED',
        'PURCHASE_COMPLETED', 'CREDITS_ADDED', 'PAYMENT_PROCESSED',
        'API_KEY_CREATED', 'API_KEY_REVOKED', 'WEBHOOK_CONFIGURED',
        'DATA_EXPORTED', 'REPORT_GENERATED', 'REPORT_DOWNLOADED',
        'SETTINGS_UPDATED', 'ACCOUNT_DETAILS_UPDATED'
    ];

    var INTERNAL_ADMIN_ONLY_EVENTS = [
        'IMPERSONATION_STARTED', 'IMPERSONATION_ENDED', 'SUPPORT_MODE_ENABLED',
        'PRICING_EDITED', 'BILLING_MODE_CHANGED', 'CREDIT_LIMIT_CHANGED',
        'SENDERID_APPROVED', 'SENDERID_REJECTED', 'SENDERID_SUSPENDED',
        'RCS_AGENT_APPROVED', 'RCS_AGENT_REJECTED', 'RCS_AGENT_SUSPENDED',
        'CAMPAIGN_APPROVED_BY_ADMIN', 'CAMPAIGN_REJECTED_BY_ADMIN',
        'TEMPLATE_APPROVED', 'TEMPLATE_REJECTED', 'TEMPLATE_SUSPENDED',
        'NUMBER_ASSIGNED', 'NUMBER_UNASSIGNED', 'NUMBER_PORTED',
        'ADMIN_USER_INVITED', 'ADMIN_USER_SUSPENDED', 'ADMIN_USER_REACTIVATED',
        'ADMIN_USER_PASSWORD_RESET', 'ADMIN_USER_MFA_RESET', 'ADMIN_USER_SESSIONS_REVOKED',
        'LOGIN_BLOCKED_BY_IP', 'ADMIN_EXPORT_INITIATED',
        'ACCOUNT_SUSPENDED_BY_ADMIN', 'ACCOUNT_REACTIVATED_BY_ADMIN',
        'INVOICE_CREATED_BY_ADMIN', 'CREDIT_NOTE_CREATED_BY_ADMIN'
    ];

    function generateMockCustomerLogs() {
        var customerFacingActions = [
            { type: 'USER_CREATED', category: 'user_management', severity: 'medium' },
            { type: 'USER_INVITED', category: 'user_management', severity: 'low' },
            { type: 'USER_SUSPENDED', category: 'user_management', severity: 'high' },
            { type: 'LOGIN_SUCCESS', category: 'authentication', severity: 'low' },
            { type: 'LOGIN_FAILED', category: 'authentication', severity: 'medium' },
            { type: 'PASSWORD_CHANGED', category: 'authentication', severity: 'medium' },
            { type: 'MFA_ENABLED', category: 'security', severity: 'medium' },
            { type: 'MFA_DISABLED', category: 'security', severity: 'high' },
            { type: 'ROLE_CHANGED', category: 'access_control', severity: 'high' },
            { type: 'PERMISSION_GRANTED', category: 'access_control', severity: 'medium' },
            { type: 'CAMPAIGN_CREATED', category: 'messaging', severity: 'low' },
            { type: 'CAMPAIGN_SUBMITTED', category: 'messaging', severity: 'low' },
            { type: 'CAMPAIGN_SENT', category: 'messaging', severity: 'low' },
            { type: 'CONTACT_LIST_IMPORTED', category: 'contacts', severity: 'low' },
            { type: 'PURCHASE_COMPLETED', category: 'financial', severity: 'medium' },
            { type: 'CREDITS_ADDED', category: 'financial', severity: 'medium' },
            { type: 'API_KEY_CREATED', category: 'api', severity: 'high' },
            { type: 'API_KEY_REVOKED', category: 'api', severity: 'high' },
            { type: 'DATA_EXPORTED', category: 'data_access', severity: 'high' },
            { type: 'REPORT_GENERATED', category: 'reporting', severity: 'low' },
            { type: 'SETTINGS_UPDATED', category: 'account', severity: 'medium' }
        ];

        var actorsByCustomer = {
            'acc-001': ['sarah@acmecorp.com', 'john@acmecorp.com', 'mike@acmecorp.com'],
            'acc-002': ['james@techstart.co.uk', 'anna@techstart.co.uk'],
            'acc-003': ['emily@healthfirst.nhs.uk', 'dr.jones@healthfirst.nhs.uk'],
            'acc-004': ['michael@retailmax.com', 'sales@retailmax.com'],
            'acc-005': ['lisa@servicepro.co.uk', 'support@servicepro.co.uk'],
            'acc-006': ['mark@digitalagency.co', 'creative@digitalagency.co'],
            'acc-007': ['finance@finsolutions.com', 'accounts@finsolutions.com'],
            'acc-008': ['admin@healthcare.partners', 'ops@healthcare.partners'],
            'acc-009': ['orders@ecommercehub.com', 'fulfillment@ecommercehub.com'],
            'acc-010': ['campaigns@marketingexperts.com', 'analytics@marketingexperts.com']
        };

        var targets = ['New User', 'Campaign #1234', 'Sub-Account', 'Contact List', 'API Key', 'User Settings', 'Report Export', 'Webhook Config'];
        var ips = ['192.168.1.100', '10.0.0.45', '172.16.0.22', '203.45.67.89', '81.23.45.67', '145.67.89.12', '87.123.45.67'];
        var logs = [];
        var now = new Date();

        for (var i = 0; i < 250; i++) {
            var action = customerFacingActions[Math.floor(Math.random() * customerFacingActions.length)];
            var customer = allCustomers[Math.floor(Math.random() * allCustomers.length)];
            var customerActors = actorsByCustomer[customer.id] || ['user@customer.com'];
            var timestamp = new Date(now.getTime() - Math.random() * 30 * 24 * 60 * 60 * 1000);

            logs.push({
                id: 'CLOG-' + String(i + 1).padStart(6, '0'),
                timestamp: timestamp.toISOString(),
                customer: customer,
                tenant_id: customer.id,
                action: action.type,
                actionLabel: action.type.replace(/_/g, ' '),
                category: action.category,
                severity: action.severity,
                actor: customerActors[Math.floor(Math.random() * customerActors.length)],
                actorType: 'customer_user',
                target: targets[Math.floor(Math.random() * targets.length)],
                ip: ips[Math.floor(Math.random() * ips.length)],
                result: Math.random() > 0.1 ? 'success' : 'failure',
                isCustomerFacing: true
            });
        }

        logs.sort(function(a, b) { return new Date(b.timestamp) - new Date(a.timestamp); });
        return logs;
    }

    function generateMockAdminLogs() {
        var adminOnlyEventTypes = [
            { type: 'ADMIN_USER_INVITED', category: 'admin_users', severity: 'medium', targetType: 'admin' },
            { type: 'ADMIN_USER_SUSPENDED', category: 'admin_users', severity: 'high', targetType: 'admin' },
            { type: 'ADMIN_USER_REACTIVATED', category: 'admin_users', severity: 'medium', targetType: 'admin' },
            { type: 'ADMIN_USER_PASSWORD_RESET', category: 'security', severity: 'high', targetType: 'admin' },
            { type: 'ADMIN_USER_MFA_RESET', category: 'security', severity: 'critical', targetType: 'admin' },
            { type: 'ADMIN_USER_SESSIONS_REVOKED', category: 'security', severity: 'high', targetType: 'admin' },
            { type: 'IMPERSONATION_STARTED', category: 'impersonation', severity: 'critical', targetType: 'customer' },
            { type: 'IMPERSONATION_ENDED', category: 'impersonation', severity: 'high', targetType: 'customer' },
            { type: 'SUPPORT_MODE_ENABLED', category: 'impersonation', severity: 'high', targetType: 'customer' },
            { type: 'LOGIN_BLOCKED_BY_IP', category: 'security', severity: 'critical', targetType: 'admin' },
            { type: 'PRICING_EDITED', category: 'billing', severity: 'high', targetType: 'customer' },
            { type: 'BILLING_MODE_CHANGED', category: 'billing', severity: 'high', targetType: 'customer' },
            { type: 'CREDIT_LIMIT_CHANGED', category: 'billing', severity: 'high', targetType: 'customer' },
            { type: 'SENDERID_APPROVED', category: 'approvals', severity: 'medium', targetType: 'customer' },
            { type: 'SENDERID_REJECTED', category: 'approvals', severity: 'medium', targetType: 'customer' },
            { type: 'RCS_AGENT_APPROVED', category: 'approvals', severity: 'medium', targetType: 'customer' },
            { type: 'CAMPAIGN_APPROVED_BY_ADMIN', category: 'approvals', severity: 'medium', targetType: 'customer' },
            { type: 'TEMPLATE_SUSPENDED', category: 'approvals', severity: 'high', targetType: 'customer' },
            { type: 'NUMBER_ASSIGNED', category: 'numbers', severity: 'medium', targetType: 'customer' },
            { type: 'ACCOUNT_SUSPENDED_BY_ADMIN', category: 'accounts', severity: 'critical', targetType: 'customer' },
            { type: 'INVOICE_CREATED_BY_ADMIN', category: 'billing', severity: 'medium', targetType: 'customer' },
            { type: 'ADMIN_EXPORT_INITIATED', category: 'data_access', severity: 'high', targetType: 'system' }
        ];

        var adminActors = [
            { email: 'sarah.johnson@quicksms.co.uk', name: 'Sarah Johnson', role: 'Super Admin' },
            { email: 'james.mitchell@quicksms.co.uk', name: 'James Mitchell', role: 'Super Admin' },
            { email: 'emily.chen@quicksms.co.uk', name: 'Emily Chen', role: 'Internal Support' },
            { email: 'david.lee@quicksms.co.uk', name: 'David Lee', role: 'Internal Support' },
            { email: 'anna.williams@quicksms.co.uk', name: 'Anna Williams', role: 'Super Admin' }
        ];

        var adminTargets = [
            { email: 'michael.brown@quicksms.co.uk', name: 'Michael Brown', type: 'admin' },
            { email: 'new.admin@quicksms.co.uk', name: 'New Admin User', type: 'admin' }
        ];

        var customerTargets = allCustomers.map(function(c) {
            return { name: c.name, id: c.id, account_number: c.account_number, type: 'customer' };
        });

        var ips = ['10.0.1.50', '10.0.1.51', '10.0.1.52', '192.168.100.1', '10.0.1.100'];
        var logs = [];
        var now = new Date();

        for (var i = 0; i < 150; i++) {
            var event = adminOnlyEventTypes[Math.floor(Math.random() * adminOnlyEventTypes.length)];
            var actor = adminActors[Math.floor(Math.random() * adminActors.length)];
            var target = event.targetType === 'admin' 
                ? adminTargets[Math.floor(Math.random() * adminTargets.length)]
                : event.targetType === 'customer'
                    ? customerTargets[Math.floor(Math.random() * customerTargets.length)]
                    : { name: 'System', type: 'system' };
            var timestamp = new Date(now.getTime() - Math.random() * 30 * 24 * 60 * 60 * 1000);

            var reason = null;
            if (event.type.includes('SUSPENDED') || event.type.includes('REJECTED') || event.type.includes('BLOCKED')) {
                var reasons = ['Policy violation', 'Security concern', 'Compliance requirement', 'Customer request', 'Fraud prevention'];
                reason = reasons[Math.floor(Math.random() * reasons.length)];
            }

            logs.push({
                id: 'ALOG-' + String(i + 1).padStart(6, '0'),
                timestamp: timestamp.toISOString(),
                eventType: event.type,
                eventLabel: event.type.replace(/_/g, ' '),
                category: event.category,
                severity: event.severity,
                actor: actor,
                admin_actor_id: 'ADM-' + String(adminActors.indexOf(actor) + 1).padStart(3, '0'),
                target: target,
                targetType: event.targetType,
                customer_id_impacted: event.targetType === 'customer' ? target.id : null,
                ip: ips[Math.floor(Math.random() * ips.length)],
                result: Math.random() > 0.03 ? 'success' : 'failure',
                reason: reason,
                isInternalOnly: true,
                isAdminEvent: true
            });
        }

        logs.sort(function(a, b) { return new Date(b.timestamp) - new Date(a.timestamp); });
        return logs;
    }

    function renderCustomerLogs() {
        var filteredLogs = customerLogs.filter(function(log) {
            if (!log.isCustomerFacing) return false;
            if (INTERNAL_ADMIN_ONLY_EVENTS.indexOf(log.action) !== -1) return false;
            if (selectedCustomerId) {
                return log.tenant_id === selectedCustomerId;
            }
            return true;
        });

        var showCustomerColumn = !selectedCustomerId;
        updateCustomerColumnVisibility(showCustomerColumn);

        var tbody = document.getElementById('customerAuditLogsTableBody');
        tbody.innerHTML = '';

        var start = (customerPage - 1) * itemsPerPage;
        var end = Math.min(start + itemsPerPage, filteredLogs.length);
        var pageLogs = filteredLogs.slice(start, end);

        if (pageLogs.length === 0) {
            $('#customerEmptyState').show();
            $('#customerAuditTableContainer').hide();
            $('#customerPaginationRow').hide();
        } else {
            $('#customerEmptyState').hide();
            $('#customerAuditTableContainer').show();
            $('#customerPaginationRow').show();
        }

        pageLogs.forEach(function(log) {
            var row = document.createElement('tr');
            row.className = 'customer-audit-log-row';
            
            var customerCell = showCustomerColumn 
                ? '<td class="customer-col"><span class="badge bg-light text-dark" style="font-size: 0.7rem;">' + log.customer.name + '</span></td>' 
                : '';
            
            row.innerHTML = 
                '<td>' + formatTimestamp(log.timestamp) + '</td>' +
                customerCell +
                '<td><code class="small">' + log.id + '</code></td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td><span class="badge customer-category-badge-' + log.category + '">' + formatCategory(log.category) + '</span></td>' +
                '<td><span class="badge customer-severity-badge-' + log.severity + '">' + capitalize(log.severity) + '</span></td>' +
                '<td>' + log.actor + '</td>' +
                '<td>' + log.target + '</td>' +
                '<td><code class="small">' + log.ip + '</code></td>';
            row.onclick = function() { showLogDetail(log, 'customer'); };
            tbody.appendChild(row);
        });

        $('#customerTotalFiltered').text(filteredLogs.length);
        $('#customerShowingStart').text(filteredLogs.length > 0 ? start + 1 : 0);
        $('#customerShowingEnd').text(end);
        $('#customerPaginationTotal').text(filteredLogs.length);
        renderPagination('customer', filteredLogs.length, customerPage);
    }

    function updateCustomerColumnVisibility(show) {
        var table = document.getElementById('customerAuditLogsTable');
        if (!table) return;
        
        var headerRow = table.querySelector('thead tr');
        var existingCustomerHeader = headerRow.querySelector('.customer-col-header');
        
        if (show && !existingCustomerHeader) {
            var th = document.createElement('th');
            th.className = 'customer-col-header';
            th.style.width = '140px';
            th.textContent = 'Customer';
            var timestampHeader = headerRow.querySelector('th:first-child');
            timestampHeader.insertAdjacentElement('afterend', th);
        } else if (!show && existingCustomerHeader) {
            existingCustomerHeader.remove();
        }
    }

    function renderAdminLogs() {
        var filteredLogs = applyAdminFilters(adminLogs).filter(function(log) {
            if (!log.isAdminEvent || !log.admin_actor_id) {
                console.warn('[DataSeparation] Blocked non-admin event from Internal Audit:', log.id);
                return false;
            }
            return true;
        });

        var tbody = document.getElementById('adminAuditLogsTableBody');
        tbody.innerHTML = '';

        var start = (adminPage - 1) * itemsPerPage;
        var end = Math.min(start + itemsPerPage, filteredLogs.length);
        var pageLogs = filteredLogs.slice(start, end);

        pageLogs.forEach(function(log) {
            var row = document.createElement('tr');
            
            var customerImpacted = formatCustomerImpacted(log.target, log.targetType);
            var moduleBadge = formatModuleBadge(log.category);
            var actionBadge = formatActionBadge(log.eventType, log.eventLabel, log.severity);
            var resultBadge = log.result === 'success' 
                ? '<span class="badge" style="background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; font-size: 0.75rem;">Success</span>'
                : '<span class="badge" style="background-color: rgba(220, 53, 69, 0.15); color: #dc3545; font-size: 0.75rem;">Failed</span>';
            var riskBadge = formatRiskBadge(log.eventType);
            
            row.innerHTML = 
                '<td>' + formatTimestamp(log.timestamp) + '</td>' +
                '<td>' + log.actor.email + '</td>' +
                '<td>' + customerImpacted + '</td>' +
                '<td>' + moduleBadge + '</td>' +
                '<td>' + actionBadge + '</td>' +
                '<td>' + resultBadge + '</td>' +
                '<td>' + riskBadge + '</td>';
            row.onclick = function() { showLogDetail(log, 'admin'); };
            tbody.appendChild(row);
        });

        $('#adminTotalFiltered').text(filteredLogs.length);
        $('#adminShowingStart').text(filteredLogs.length > 0 ? start + 1 : 0);
        $('#adminShowingEnd').text(end);
        $('#adminPaginationTotal').text(filteredLogs.length);
        renderPagination('admin', filteredLogs.length, adminPage);
    }

    function formatCustomerImpacted(target, targetType) {
        if (targetType === 'customer' && target) {
            return '<span class="badge" style="background-color: rgba(30, 58, 95, 0.1); color: #1e3a5f; font-size: 0.75rem;">' + 
                   target.name + '</span>';
        } else if (targetType === 'admin' && target) {
            return '<span class="text-muted small" style="font-style: italic;">N/A (Admin Target)</span>';
        } else {
            return '<span class="text-muted small" style="font-style: italic;">N/A</span>';
        }
    }

    function formatModuleBadge(category) {
        var moduleLabels = {
            'admin_users': 'Admin Users',
            'security': 'Security',
            'impersonation': 'Impersonation',
            'billing': 'Billing',
            'approvals': 'Approvals',
            'numbers': 'Numbers',
            'accounts': 'Accounts',
            'data_access': 'Data Access'
        };
        var moduleColors = {
            'admin_users': 'rgba(30, 58, 95, 0.15)',
            'security': 'rgba(220, 53, 69, 0.15)',
            'impersonation': 'rgba(220, 53, 69, 0.2)',
            'billing': 'rgba(28, 187, 140, 0.15)',
            'approvals': 'rgba(111, 66, 193, 0.15)',
            'numbers': 'rgba(48, 101, 208, 0.15)',
            'accounts': 'rgba(30, 58, 95, 0.15)',
            'data_access': 'rgba(255, 191, 0, 0.15)'
        };
        var textColors = {
            'admin_users': '#1e3a5f',
            'security': '#dc3545',
            'impersonation': '#dc3545',
            'billing': '#1cbb8c',
            'approvals': '#6f42c1',
            'numbers': '#3065D0',
            'accounts': '#1e3a5f',
            'data_access': '#cc9900'
        };
        var label = moduleLabels[category] || formatCategory(category);
        var bgColor = moduleColors[category] || 'rgba(30, 58, 95, 0.15)';
        var textColor = textColors[category] || '#1e3a5f';
        return '<span class="badge" style="background-color: ' + bgColor + '; color: ' + textColor + '; font-size: 0.75rem;">' + label + '</span>';
    }

    function formatActionBadge(eventType, eventLabel, severity) {
        var bgColor = 'rgba(30, 58, 95, 0.1)';
        var textColor = '#1e3a5f';
        
        if (severity === 'critical') {
            bgColor = 'rgba(220, 53, 69, 0.2)';
            textColor = '#dc3545';
        } else if (severity === 'high') {
            bgColor = 'rgba(220, 53, 69, 0.15)';
            textColor = '#dc3545';
        } else if (eventType.includes('IMPERSONATION')) {
            bgColor = 'rgba(220, 53, 69, 0.2)';
            textColor = '#dc3545';
        }
        
        return '<span class="badge" style="background-color: ' + bgColor + '; color: ' + textColor + '; font-size: 0.75rem;">' + eventLabel + '</span>';
    }

    var HIGH_RISK_EVENT_TYPES = [
        'ADMIN_EXPORT_INITIATED',
        'DATA_EXPORTED',
        'IMPERSONATION_STARTED',
        'IMPERSONATION_ENDED',
        'SUPPORT_MODE_ENABLED',
        'PRICING_EDITED',
        'BILLING_MODE_CHANGED',
        'CREDIT_LIMIT_CHANGED',
        'ADMIN_USER_MFA_RESET',
        'MFA_RESET',
        'IP_ALLOWLIST_ADDED',
        'IP_ALLOWLIST_REMOVED',
        'IP_ALLOWLIST_CHANGED',
        'ACCOUNT_SUSPENDED_BY_ADMIN',
        'LOGIN_BLOCKED_BY_IP'
    ];

    function isHighRiskEvent(eventType) {
        return HIGH_RISK_EVENT_TYPES.indexOf(eventType) !== -1;
    }

    function formatRiskBadge(eventType) {
        if (isHighRiskEvent(eventType)) {
            return '<span class="badge" style="background-color: rgba(220, 53, 69, 0.2); color: #dc3545; font-size: 0.7rem; font-weight: 600;">' +
                   '<i class="fas fa-exclamation-triangle me-1"></i>High Risk</span>';
        }
        return '<span class="text-muted small" style="font-size: 0.75rem;">Standard</span>';
    }

    function renderPagination(type, total, currentPage) {
        var totalPages = Math.ceil(total / itemsPerPage);
        var container = document.getElementById(type + 'PaginationControls');
        container.innerHTML = '';

        if (totalPages <= 1) return;

        var prevLi = document.createElement('li');
        prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
        prevLi.innerHTML = '<a class="page-link" href="#">&laquo;</a>';
        prevLi.onclick = function(e) { e.preventDefault(); if (currentPage > 1) goToPage(type, currentPage - 1); };
        container.appendChild(prevLi);

        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

        for (var i = startPage; i <= endPage; i++) {
            var li = document.createElement('li');
            li.className = 'page-item' + (i === currentPage ? ' active' : '');
            li.innerHTML = '<a class="page-link" href="#">' + i + '</a>';
            li.onclick = (function(page) {
                return function(e) { e.preventDefault(); goToPage(type, page); };
            })(i);
            container.appendChild(li);
        }

        var nextLi = document.createElement('li');
        nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
        nextLi.innerHTML = '<a class="page-link" href="#">&raquo;</a>';
        nextLi.onclick = function(e) { e.preventDefault(); if (currentPage < totalPages) goToPage(type, currentPage + 1); };
        container.appendChild(nextLi);
    }

    function goToPage(type, page) {
        if (type === 'customer') {
            customerPage = page;
            renderCustomerLogs();
        } else {
            adminPage = page;
            renderAdminLogs();
        }
    }

    function updateAdminStats() {
        var now = new Date();
        var last24h = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        var last7d = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

        var actions24h = adminLogs.filter(function(l) { return new Date(l.timestamp) >= last24h; }).length;
        var impersonations = adminLogs.filter(function(l) { 
            return l.eventType.includes('IMPERSONATION') && new Date(l.timestamp) >= last7d; 
        }).length;
        var securityEvents = adminLogs.filter(function(l) { 
            return (l.severity === 'high' || l.severity === 'critical') && new Date(l.timestamp) >= last24h; 
        }).length;
        var blockedLogins = adminLogs.filter(function(l) { 
            return l.eventType === 'LOGIN_BLOCKED_BY_IP' && new Date(l.timestamp) >= last24h; 
        }).length;

        $('#adminActions24h').text(actions24h);
        $('#impersonations7d').text(impersonations);
        $('#adminSecurityEvents').text(securityEvents);
        $('#adminBlockedLogins').text(blockedLogins);
    }

    function bindCustomerSelectorEvents() {
        var searchInput = $('#customerCustomerSearch');
        var resultsContainer = $('#customerCustomerSearchResults');
        var selectorInput = $('#customerCustomerSelectorInput');
        var selectedDisplay = $('#customerSelectedCustomerDisplay');
        var dropdownBtn = $('#customerCustomerDropdownBtn');

        searchInput.on('focus', function() {
            showCustomerDropdown('');
        });

        searchInput.on('input', function() {
            var query = $(this).val().toLowerCase();
            showCustomerDropdown(query);
        });

        dropdownBtn.on('click', function() {
            if (resultsContainer.is(':visible')) {
                resultsContainer.hide();
            } else {
                showCustomerDropdown('');
                searchInput.focus();
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#customerCustomerSelectorContainer').length) {
                resultsContainer.hide();
            }
        });

        $('#customerClearCustomer').on('click', function() {
            selectedCustomerId = null;
            selectorInput.show();
            selectedDisplay.hide();
            searchInput.val('');
            updateSubAccountFilter(null);
            customerPage = 1;
            renderCustomerLogs();
        });

        function showCustomerDropdown(query) {
            var html = '<div class="customer-customer-search-item" data-id="" style="font-style: italic; color: #6c757d;">' +
                '<div class="customer-name">All Customers</div>' +
                '<div class="customer-id">View logs from all customer accounts</div>' +
                '</div>';

            var filtered = allCustomers.filter(function(c) {
                return c.name.toLowerCase().includes(query) || 
                       c.account_number.toLowerCase().includes(query);
            });

            filtered.forEach(function(c) {
                html += '<div class="customer-customer-search-item" data-id="' + c.id + '">' +
                    '<div class="customer-name">' + c.name + '</div>' +
                    '<div class="customer-id">' + c.account_number + '</div>' +
                    '</div>';
            });

            resultsContainer.html(html).show();

            resultsContainer.find('.customer-customer-search-item').on('click', function() {
                var id = $(this).data('id');
                if (id) {
                    var customer = allCustomers.find(function(c) { return c.id === id; });
                    selectedCustomerId = id;
                    $('#customerSelectedCustomerName').text(customer.name + ' (' + customer.account_number + ')');
                    selectorInput.hide();
                    selectedDisplay.show();
                    updateSubAccountFilter(id);
                } else {
                    selectedCustomerId = null;
                    selectorInput.show();
                    selectedDisplay.hide();
                    searchInput.val('');
                    updateSubAccountFilter(null);
                }
                resultsContainer.hide();
                customerPage = 1;
                renderCustomerLogs();
            });
        }
    }

    function updateSubAccountFilter(customerId) {
        var select = $('#customerSubAccountFilter');
        select.html('<option value="">All Sub-Accounts</option><option value="main">Main Account</option>');

        if (customerId && subAccountsByCustomer[customerId]) {
            subAccountsByCustomer[customerId].forEach(function(sa) {
                select.append('<option value="' + sa.id + '">' + sa.name + '</option>');
            });
        }
    }

    var adminFilterState = {
        dateFrom: null,
        dateTo: null,
        module: '',
        eventType: '',
        actor: '',
        customerImpacted: null,
        result: '',
        ip: '',
        highRiskOnly: false
    };

    function bindAdminFilterEvents() {
        var searchInput = $('#adminCustomerImpactedSearch');
        var resultsContainer = $('#adminCustomerImpactedResults');
        var hiddenInput = $('#adminCustomerImpactedId');

        searchInput.on('focus', function() {
            showAdminCustomerDropdown('');
        });

        searchInput.on('input', function() {
            var query = $(this).val().toLowerCase();
            showAdminCustomerDropdown(query);
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#adminCustomerImpactedContainer').length) {
                resultsContainer.hide();
            }
        });

        function showAdminCustomerDropdown(query) {
            var html = '<div class="admin-customer-search-item" data-id="">' +
                '<div class="customer-name" style="font-style: italic; color: #6c757d;">All Customers</div>' +
                '</div>';

            var filtered = allCustomers.filter(function(c) {
                return c.name.toLowerCase().includes(query) || 
                       c.account_number.toLowerCase().includes(query);
            });

            filtered.forEach(function(c) {
                html += '<div class="admin-customer-search-item" data-id="' + c.id + '" data-name="' + c.name + '">' +
                    '<div class="customer-name">' + c.name + '</div>' +
                    '<div class="customer-id">' + c.account_number + '</div>' +
                    '</div>';
            });

            resultsContainer.html(html).show();

            resultsContainer.find('.admin-customer-search-item').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                if (id) {
                    searchInput.val(name);
                    hiddenInput.val(id);
                    adminFilterState.customerImpacted = id;
                } else {
                    searchInput.val('');
                    hiddenInput.val('');
                    adminFilterState.customerImpacted = null;
                }
                resultsContainer.hide();
            });
        }

        $('#adminApplyFiltersBtn').off('click').on('click', function() {
            adminFilterState.dateFrom = $('#adminDateFromFilter').val() || null;
            adminFilterState.dateTo = $('#adminDateToFilter').val() || null;
            adminFilterState.module = $('#adminModuleFilter').val();
            adminFilterState.eventType = $('#adminEventTypeFilter').val();
            adminFilterState.actor = $('#adminActorFilter').val();
            adminFilterState.result = $('#adminResultFilter').val();
            adminFilterState.ip = $('#adminIpFilter').val().trim();
            adminFilterState.highRiskOnly = $('#adminHighRiskOnlyFilter').is(':checked');

            adminPage = 1;
            renderAdminLogs();
            showToast('Filters applied', 'info');
        });

        $('#adminClearFiltersBtn').off('click').on('click', function() {
            $('#adminFiltersPanel').find('input, select').val('');
            $('#adminHighRiskOnlyFilter').prop('checked', false);
            adminFilterState = {
                dateFrom: null,
                dateTo: null,
                module: '',
                eventType: '',
                actor: '',
                customerImpacted: null,
                result: '',
                ip: '',
                highRiskOnly: false
            };
            adminPage = 1;
            renderAdminLogs();
            showToast('Filters cleared', 'info');
        });

        $('#adminHighRiskOnlyFilter').on('change', function() {
            adminFilterState.highRiskOnly = $(this).is(':checked');
        });
    }

    function applyAdminFilters(logs) {
        return logs.filter(function(log) {
            if (!log.isInternalOnly) return false;

            if (adminFilterState.dateFrom) {
                var fromDate = new Date(adminFilterState.dateFrom);
                if (new Date(log.timestamp) < fromDate) return false;
            }

            if (adminFilterState.dateTo) {
                var toDate = new Date(adminFilterState.dateTo);
                toDate.setHours(23, 59, 59, 999);
                if (new Date(log.timestamp) > toDate) return false;
            }

            if (adminFilterState.module && log.category !== adminFilterState.module) {
                return false;
            }

            if (adminFilterState.eventType && log.eventType !== adminFilterState.eventType) {
                return false;
            }

            if (adminFilterState.actor && log.actor.email !== adminFilterState.actor) {
                return false;
            }

            if (adminFilterState.customerImpacted) {
                if (log.targetType !== 'customer') return false;
                if (log.target.id !== adminFilterState.customerImpacted) return false;
            }

            if (adminFilterState.result && log.result !== adminFilterState.result) {
                return false;
            }

            if (adminFilterState.ip && !log.ip.includes(adminFilterState.ip)) {
                return false;
            }

            if (adminFilterState.highRiskOnly) {
                if (!isHighRiskEvent(log.eventType)) return false;
            }

            return true;
        });
    }

    function showLogDetail(log, type) {
        var content = '';
        
        if (type === 'customer') {
            content = '<div class="admin-log-detail-section">' +
                '<h6><i class="fas fa-info-circle me-2"></i>Event Information</h6>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Event ID</div><div class="admin-log-detail-value"><code>' + log.id + '</code></div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Timestamp</div><div class="admin-log-detail-value">' + log.timestamp + '</div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Action</div><div class="admin-log-detail-value">' + log.actionLabel + '</div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Category</div><div class="admin-log-detail-value"><span class="badge customer-category-badge-' + log.category + '">' + formatCategory(log.category) + '</span></div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Severity</div><div class="admin-log-detail-value"><span class="badge customer-severity-badge-' + log.severity + '">' + capitalize(log.severity) + '</span></div></div>' +
                '</div>' +
                '<div class="admin-log-detail-section">' +
                '<h6><i class="fas fa-building me-2"></i>Account Context</h6>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Customer</div><div class="admin-log-detail-value">' + log.customer.name + ' (' + log.customer.account_number + ')</div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Actor</div><div class="admin-log-detail-value">' + log.actor + '</div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Target</div><div class="admin-log-detail-value">' + log.target + '</div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">IP Address</div><div class="admin-log-detail-value"><code>' + log.ip + '</code></div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Result</div><div class="admin-log-detail-value"><span class="badge ' + (log.result === 'success' ? 'bg-success' : 'bg-danger') + '">' + capitalize(log.result) + '</span></div></div>' +
                '</div>';
        } else {
            content = '<div class="admin-log-detail-section">' +
                '<h6><i class="fas fa-user-shield me-2"></i>Admin Event Information</h6>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Event ID</div><div class="admin-log-detail-value"><code>' + log.id + '</code></div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Timestamp</div><div class="admin-log-detail-value">' + log.timestamp + '</div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Event Type</div><div class="admin-log-detail-value"><span class="badge admin-category-badge-admin">' + log.eventLabel + '</span></div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Severity</div><div class="admin-log-detail-value"><span class="badge admin-severity-badge-' + log.severity + '">' + capitalize(log.severity) + '</span></div></div>' +
                '</div>' +
                '<div class="admin-log-detail-section">' +
                '<h6><i class="fas fa-users me-2"></i>Participants</h6>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Actor Admin</div><div class="admin-log-detail-value">' + log.actor.name + ' (' + log.actor.email + ')</div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Target</div><div class="admin-log-detail-value">' + log.target.name + ' (' + log.target.email + ')</div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">IP Address</div><div class="admin-log-detail-value"><code>' + log.ip + '</code></div></div>' +
                '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Result</div><div class="admin-log-detail-value"><span class="badge ' + (log.result === 'success' ? 'bg-success' : 'bg-danger') + '">' + capitalize(log.result) + '</span></div></div>' +
                (log.reason ? '<div class="admin-log-detail-row"><div class="admin-log-detail-label">Reason</div><div class="admin-log-detail-value">' + log.reason + '</div></div>' : '') +
                '</div>';
        }

        document.getElementById('logDetailContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('logDetailModal')).show();
    }

    function formatTimestamp(isoString) {
        var d = new Date(isoString);
        var day = String(d.getDate()).padStart(2, '0');
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var year = d.getFullYear();
        var hours = String(d.getHours()).padStart(2, '0');
        var mins = String(d.getMinutes()).padStart(2, '0');
        return day + '-' + month + '-' + year + ' ' + hours + ':' + mins;
    }

    function formatCategory(cat) {
        return cat.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function bindEvents() {
        $('#verifyIntegrityBtn').on('click', function() {
            $('#integrityChecking').show();
            $('#integrityResult').hide();
            new bootstrap.Modal(document.getElementById('integrityModal')).show();

            setTimeout(function() {
                $('#integrityChecking').hide();
                $('#integrityResult').show();
                $('#recordsVerified').text(customerLogs.length + adminLogs.length);
                $('#verificationTime').text(Math.floor(Math.random() * 500 + 200) + 'ms');
            }, 1500);
        });

        $('#customerSearchInput').on('input', function() {
            var query = $(this).val().toLowerCase();
            customerPage = 1;
            renderCustomerLogs();
        });

        $('#adminSearchInput').on('input', function() {
            var query = $(this).val().toLowerCase();
            var allLogs = generateMockAdminLogs();
            var filtered = allLogs.filter(function(log) {
                return log.eventLabel.toLowerCase().includes(query) ||
                       log.actor.email.toLowerCase().includes(query) ||
                       log.target.email.toLowerCase().includes(query) ||
                       log.id.toLowerCase().includes(query);
            });
            adminLogs = filtered.length > 0 || query ? filtered : allLogs;
            adminPage = 1;
            renderAdminLogs();
        });

        $('#copyLogDetail').on('click', function() {
            var text = document.getElementById('logDetailContent').innerText;
            navigator.clipboard.writeText(text).then(function() {
                showToast('Log details copied to clipboard', 'success');
            });
        });

        $('#customerApplyFiltersBtn, #adminApplyFiltersBtn').on('click', function() {
            showToast('Filters applied', 'info');
        });

        $('#customerClearFiltersBtn, #adminClearFiltersBtn').on('click', function() {
            $(this).closest('.collapse').find('input, select').val('');
            showToast('Filters cleared', 'info');
        });

        $('#customerExportCsv, #customerExportExcel').on('click', function(e) {
            e.preventDefault();
            var format = $(this).attr('id').includes('Csv') ? 'CSV' : 'Excel';
            showToast('Exporting to ' + format + '...', 'info');
            setTimeout(function() {
                showToast('Export completed successfully', 'success');
            }, 1000);
        });

        $('#adminExportCsv, #adminExportExcel').on('click', function(e) {
            e.preventDefault();
            performAdminAuditExport($(this).attr('id').includes('Csv') ? 'csv' : 'xlsx');
        });
    }

    var EXPORT_AUTHORIZED_ROLES = ['super_admin', 'security_admin', 'compliance_officer'];

    function performAdminAuditExport(format) {
        var currentAdmin = {
            email: 'admin@quicksms.co.uk',
            role: 'super_admin',
            id: 'ADM001'
        };

        if (EXPORT_AUTHORIZED_ROLES.indexOf(currentAdmin.role) === -1) {
            showToast('Export not permitted. Your role (' + currentAdmin.role + ') is not authorized to export internal audit logs.', 'error');
            logExportAttempt(currentAdmin, format, 'DENIED', 'Unauthorized role');
            return;
        }

        var filteredData = applyAdminFilters();
        var rowCount = filteredData.length;

        if (rowCount === 0) {
            showToast('No data to export with current filters', 'warning');
            return;
        }

        var exportTimestamp = new Date().toISOString();
        var fileReference = generateExportFileReference(format, exportTimestamp);

        showToast('Authorizing export...', 'info');

        setTimeout(function() {
            logExportAuditEvent(currentAdmin, format, rowCount, exportTimestamp, fileReference, filteredData);
            
            showToast('Exporting ' + rowCount + ' records to ' + format.toUpperCase() + '...', 'info');
            
            setTimeout(function() {
                showToast('Export completed: ' + fileReference, 'success');
                console.log('[AdminAuditExport] Export completed:', {
                    file: fileReference,
                    records: rowCount,
                    format: format.toUpperCase()
                });
            }, 1500);
        }, 500);
    }

    function generateExportFileReference(format, timestamp) {
        var dateStr = timestamp.replace(/[-:T]/g, '').substring(0, 14);
        var randomSuffix = Math.random().toString(36).substring(2, 8).toUpperCase();
        return 'AUDIT_EXPORT_' + dateStr + '_' + randomSuffix + '.' + format;
    }

    function logExportAttempt(admin, format, status, reason) {
        console.log('[AdminAuditExport][SECURITY] Export attempt:', {
            timestamp: new Date().toISOString(),
            admin: admin.email,
            role: admin.role,
            format: format,
            status: status,
            reason: reason
        });
    }

    function logExportAuditEvent(admin, format, rowCount, timestamp, fileReference, filteredData) {
        var appliedFilters = {
            dateFrom: adminFilterState.dateFrom || null,
            dateTo: adminFilterState.dateTo || null,
            module: adminFilterState.module || 'All',
            action: adminFilterState.action || 'All',
            adminUser: adminFilterState.admin || 'All',
            customerImpacted: adminFilterState.customer || 'All',
            ipAddress: adminFilterState.ip || null,
            highRiskOnly: adminFilterState.highRiskOnly || false,
            searchQuery: adminFilterState.search || null
        };

        var auditEvent = {
            id: 'AEXP-' + Date.now(),
            eventType: 'ADMIN_EXPORT_INITIATED',
            eventLabel: 'Internal Audit Export',
            timestamp: timestamp,
            actor: {
                id: admin.id,
                email: admin.email,
                role: admin.role
            },
            admin_actor_id: admin.id,
            category: 'data_access',
            severity: 'high',
            result: 'success',
            details: {
                exportFormat: format.toUpperCase(),
                rowCount: rowCount,
                fileReference: fileReference,
                filtersApplied: appliedFilters,
                filterSummary: buildFilterSummary(appliedFilters),
                exportScope: 'internal_admin_audit'
            },
            target: {
                type: 'audit_data',
                name: 'Internal Admin Audit Logs'
            },
            targetType: 'system',
            customer_id_impacted: null,
            isAdminEvent: true,
            isInternalOnly: true,
            ip: '10.0.0.45',
            userAgent: navigator.userAgent
        };

        console.log('[AdminAuditExport][AUDIT_EVENT]', JSON.stringify(auditEvent, null, 2));

        adminAuditLogs.unshift(auditEvent);

        console.log('[AdminAuditExport] Audit event logged for export:', {
            eventId: auditEvent.id,
            admin: admin.email,
            format: format.toUpperCase(),
            rowCount: rowCount,
            fileReference: fileReference,
            filters: appliedFilters
        });
    }

    function buildFilterSummary(filters) {
        var parts = [];
        if (filters.dateFrom || filters.dateTo) {
            parts.push('Date: ' + (filters.dateFrom || '*') + ' to ' + (filters.dateTo || '*'));
        }
        if (filters.module !== 'All') parts.push('Module: ' + filters.module);
        if (filters.action !== 'All') parts.push('Action: ' + filters.action);
        if (filters.adminUser !== 'All') parts.push('Admin: ' + filters.adminUser);
        if (filters.customerImpacted !== 'All') parts.push('Customer: ' + filters.customerImpacted);
        if (filters.ipAddress) parts.push('IP: ' + filters.ipAddress);
        if (filters.highRiskOnly) parts.push('High-Risk Only');
        if (filters.searchQuery) parts.push('Search: "' + filters.searchQuery + '"');
        return parts.length > 0 ? parts.join('; ') : 'No filters applied';
    }

    function showToast(message, type) {
        var bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
        var toastHtml = '<div class="toast align-items-center text-white ' + bgClass + ' border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">' +
            '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
        var $toast = $(toastHtml).appendTo('body');
        var toast = new bootstrap.Toast($toast[0], { delay: 3000 });
        toast.show();
        $toast.on('hidden.bs.toast', function() { $(this).remove(); });
    }

    init();
});
</script>
@endpush

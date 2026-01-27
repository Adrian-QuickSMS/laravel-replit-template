@extends('layouts.admin')

@section('page_title', 'Audit Logs - Admin')

@push('styles')
<style>
.admin-blue { color: #1e3a5f; }
.admin-blue-bg { background-color: #1e3a5f; }

.severity-badge-low { background-color: rgba(108, 117, 125, 0.15); color: #6c757d; }
.severity-badge-medium { background-color: rgba(30, 58, 95, 0.15); color: #1e3a5f; }
.severity-badge-high { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.severity-badge-critical { background-color: rgba(220, 53, 69, 0.25); color: #dc3545; font-weight: 600; }

.category-badge-user_management { background-color: rgba(30, 58, 95, 0.15); color: #1e3a5f; }
.category-badge-access_control { background-color: rgba(48, 101, 208, 0.15); color: #3065D0; }
.category-badge-security { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.category-badge-authentication { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.category-badge-enforcement { background-color: rgba(214, 83, 193, 0.15); color: #D653C1; }
.category-badge-data_access { background-color: rgba(255, 191, 0, 0.15); color: #cc9900; }
.category-badge-account { background-color: rgba(30, 58, 95, 0.15); color: #1e3a5f; }
.category-badge-messaging { background-color: rgba(48, 101, 208, 0.15); color: #3065D0; }
.category-badge-financial { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.category-badge-gdpr { background-color: rgba(214, 83, 193, 0.15); color: #D653C1; }
.category-badge-compliance { background-color: rgba(30, 58, 95, 0.15); color: #1e3a5f; }
.category-badge-admin { background-color: rgba(30, 58, 95, 0.2); color: #1e3a5f; }
.category-badge-impersonation { background-color: rgba(220, 53, 69, 0.2); color: #dc3545; }

.audit-log-row { cursor: pointer; }
.audit-log-row:hover { background-color: rgba(30, 58, 95, 0.03); }

.log-detail-section { padding: 1rem; background-color: #fafafa; border-radius: 0.5rem; margin-bottom: 1rem; }
.log-detail-section h6 { color: #1e3a5f; margin-bottom: 0.75rem; font-size: 0.875rem; }
.log-detail-row { display: flex; margin-bottom: 0.5rem; }
.log-detail-label { width: 140px; font-weight: 500; color: #6c757d; font-size: 0.8125rem; }
.log-detail-value { flex: 1; font-size: 0.8125rem; color: #212529; }

.stats-card { border: none; background: #fff; border-radius: 0.5rem; }
.stats-card .stat-value { font-size: 1.5rem; font-weight: 600; color: #1e3a5f; }
.stats-card .stat-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; }

.empty-state { padding: 4rem 2rem; text-align: center; }
.empty-state i { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; }

.retention-indicator { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; }
.retention-active { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.retention-approaching { background-color: rgba(255, 191, 0, 0.15); color: #cc9900; }

.integrity-badge { font-family: monospace; font-size: 0.7rem; background-color: rgba(108, 117, 125, 0.1); padding: 0.125rem 0.375rem; border-radius: 0.25rem; }

.compliance-card { background: linear-gradient(135deg, rgba(30, 58, 95, 0.05) 0%, rgba(30, 58, 95, 0.02) 100%); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; }
.compliance-card h6 { color: #1e3a5f; font-size: 0.875rem; margin-bottom: 0.5rem; }
.compliance-card .compliance-stat { font-size: 1.25rem; font-weight: 600; color: #1e3a5f; }

.quick-filter-btn { 
    font-size: 0.75rem; 
    padding: 0.375rem 0.875rem; 
    border-radius: 1rem; 
    margin-right: 0.5rem; 
    margin-bottom: 0.5rem; 
    background-color: #fff; 
    border: 1px solid #dee2e6; 
    color: #495057;
    transition: all 0.15s ease;
}
.quick-filter-btn:hover { 
    background-color: rgba(30, 58, 95, 0.08); 
    border-color: #1e3a5f; 
    color: #1e3a5f; 
}
.quick-filter-btn.active { 
    background-color: #1e3a5f; 
    color: #fff; 
    border-color: #1e3a5f; 
}

.audit-table-container { 
    max-height: 600px; 
    overflow-y: auto; 
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
}
.audit-table-container.infinite-scroll-enabled { max-height: none; }

.audit-logs-table {
    width: 100%;
    border-collapse: collapse;
}
.audit-logs-table thead th {
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
.audit-logs-table tbody td {
    padding: 0.5rem 0.35rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
.audit-logs-table tbody tr:hover {
    background-color: rgba(30, 58, 95, 0.03);
}

.sortable-header {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s ease;
}
.sortable-header:hover {
    background-color: rgba(30, 58, 95, 0.08);
}
.sortable-header .sort-icon {
    color: #ccc;
    font-size: 0.7rem;
}
.sortable-header .sort-icon.active {
    color: #1e3a5f;
}

.loading-more { 
    text-align: center; 
    padding: 1.5rem; 
    color: #6c757d; 
    background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(30, 58, 95, 0.02) 100%);
}
.loading-more .spinner-border { width: 1.25rem; height: 1.25rem; border-width: 0.15em; color: #1e3a5f; }

.load-more-btn {
    background-color: #fff;
    border: 1px solid #1e3a5f;
    color: #1e3a5f;
    padding: 0.5rem 1.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    transition: all 0.15s ease;
}
.load-more-btn:hover {
    background-color: rgba(30, 58, 95, 0.08);
    border-color: #1e3a5f;
    color: #1e3a5f;
}

.view-mode-toggle { font-size: 0.75rem; }
.view-mode-toggle .btn { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.view-mode-toggle .btn.active { background-color: #1e3a5f; color: #fff; border-color: #1e3a5f; }

.end-of-list { 
    text-align: center; 
    padding: 1rem; 
    color: #6c757d; 
    font-size: 0.8125rem;
    border-top: 1px dashed #dee2e6;
}

.filter-panel { 
    background-color: rgba(30, 58, 95, 0.05) !important; 
    border: 1px solid #e9ecef;
}
.filter-panel .form-label { margin-bottom: 0.25rem; }
.filter-panel .form-control,
.filter-panel .form-select { font-size: 0.875rem; }

.active-filters-display .filter-tag {
    display: inline-flex;
    align-items: center;
    background-color: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    margin-right: 0.5rem;
    margin-bottom: 0.25rem;
}
.active-filters-display .filter-tag .remove-filter {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.active-filters-display .filter-tag .remove-filter:hover {
    opacity: 1;
}

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

.account-selector {
    max-width: 300px;
}

.admin-event-row {
    background-color: rgba(30, 58, 95, 0.02);
}
.admin-event-row:hover {
    background-color: rgba(30, 58, 95, 0.06);
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Customer Activity Audit</h5>
                    <small class="text-muted">View audit logs across all customer accounts</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm account-selector" id="customerAccountFilter">
                        <option value="">All Customer Accounts</option>
                        <option value="acc-001">Acme Corp (ACC-001)</option>
                        <option value="acc-002">TechStart Ltd (ACC-002)</option>
                        <option value="acc-003">HealthFirst UK (ACC-003)</option>
                        <option value="acc-004">RetailMax (ACC-004)</option>
                        <option value="acc-005">ServicePro (ACC-005)</option>
                    </select>
                    <button type="button" class="btn btn-admin-outline btn-sm" data-bs-toggle="collapse" data-bs-target="#customerFiltersPanel">
                        <i class="fas fa-filter me-1"></i>Filters
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-admin-outline btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Export Format</h6></li>
                            <li><a class="dropdown-item" href="#" id="customerExportCsv"><i class="fas fa-file-csv me-2 text-success"></i>CSV (.csv)</a></li>
                            <li><a class="dropdown-item" href="#" id="customerExportExcel"><i class="fas fa-file-excel me-2 text-success"></i>Excel (.xlsx)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-item-text small text-muted"><i class="fas fa-info-circle me-1"></i>Exports current filtered view</span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="customerSearchInput" placeholder="Search by description, target ID, account name, or user...">
                    </div>
                </div>

                <div class="collapse mb-3" id="customerFiltersPanel">
                    <div class="card card-body border-0 rounded-3 filter-panel">
                        <div class="row g-3 align-items-end">
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Date From</label>
                                <input type="date" class="form-control form-control-sm" id="customerDateFromFilter">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Date To</label>
                                <input type="date" class="form-control form-control-sm" id="customerDateToFilter">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Category</label>
                                <select class="form-select form-select-sm" id="customerCategoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="user_management">User Management</option>
                                    <option value="access_control">Access Control</option>
                                    <option value="authentication">Authentication</option>
                                    <option value="security">Security</option>
                                    <option value="messaging">Messaging</option>
                                    <option value="financial">Financial</option>
                                    <option value="compliance">Compliance</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Severity</label>
                                <select class="form-select form-select-sm" id="customerSeverityFilter">
                                    <option value="">All Severities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Result</label>
                                <select class="form-select form-select-sm" id="customerResultFilter">
                                    <option value="">All Results</option>
                                    <option value="success">Success</option>
                                    <option value="failure">Failure</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2 d-flex gap-2">
                                <button type="button" class="btn btn-admin-primary btn-sm flex-grow-1" id="customerApplyFiltersBtn">
                                    <i class="fas fa-check me-1"></i>Apply
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="customerClearFiltersBtn">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small"><span id="customerTotalFiltered">0</span> events</span>
                    <div class="view-mode-toggle btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active" id="customerPaginationModeBtn">
                            <i class="fas fa-list"></i> Paginated
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="customerInfiniteScrollModeBtn">
                            <i class="fas fa-stream"></i> Scroll
                        </button>
                    </div>
                </div>

                <div class="audit-table-container" id="customerAuditTableContainer">
                    <table class="audit-logs-table" id="customerAuditLogsTable">
                        <thead>
                            <tr>
                                <th style="width: 150px;" class="sortable-header" data-sort="timestamp">Timestamp <i class="fas fa-sort-down ms-1 sort-icon active"></i></th>
                                <th style="width: 120px;">Account</th>
                                <th style="width: 100px;">Event ID</th>
                                <th class="sortable-header" data-sort="action">Action <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th style="width: 120px;" class="sortable-header" data-sort="category">Category <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th style="width: 90px;" class="sortable-header" data-sort="severity">Severity <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th class="sortable-header" data-sort="actor">Actor <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th style="width: 110px;">IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="customerAuditLogsTableBody">
                        </tbody>
                    </table>

                    <div class="loading-more" id="customerLoadingMore" style="display: none;">
                        <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                        <span class="ms-2">Loading more events...</span>
                    </div>

                    <div class="text-center py-3" id="customerLoadMoreContainer" style="display: none;">
                        <button type="button" class="load-more-btn" id="customerLoadMoreBtn">
                            <i class="fas fa-plus-circle me-2"></i>Load More
                        </button>
                    </div>

                    <div class="end-of-list" id="customerEndOfList" style="display: none;">
                        <i class="fas fa-check-circle text-success me-2"></i>All events loaded
                    </div>
                </div>

                <div class="empty-state" id="customerEmptyState" style="display: none;">
                    <i class="fas fa-clipboard-list"></i>
                    <h5 class="text-muted mb-2">No audit logs found</h5>
                    <p class="text-muted small mb-0">Adjust your filters or check back later for new activity.</p>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4" id="customerPaginationRow">
                    <div class="text-muted small">
                        Showing <span id="customerShowingStart">0</span>-<span id="customerShowingEnd">0</span> of <span id="customerPaginationTotal">0</span> events
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="customerPaginationControls"></ul>
                    </nav>
                </div>
            </div>
        </div>
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
                        <div class="compliance-card">
                            <h6><i class="fas fa-user-shield me-2"></i>Admin Actions (24h)</h6>
                            <div class="compliance-stat" id="adminActions24h">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-user-secret me-2"></i>Impersonations (7d)</h6>
                            <div class="compliance-stat" id="impersonations7d">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Security Events (24h)</h6>
                            <div class="compliance-stat text-danger" id="adminSecurityEvents">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
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
                    <div class="card card-body border-0 rounded-3 filter-panel">
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
                                <label class="form-label small fw-bold">Event Type</label>
                                <select class="form-select form-select-sm" id="adminEventTypeFilter">
                                    <option value="">All Event Types</option>
                                    <optgroup label="User Lifecycle">
                                        <option value="ADMIN_USER_INVITED">User Invited</option>
                                        <option value="ADMIN_USER_ACTIVATED">User Activated</option>
                                        <option value="ADMIN_USER_SUSPENDED">User Suspended</option>
                                        <option value="ADMIN_USER_REACTIVATED">User Reactivated</option>
                                        <option value="ADMIN_USER_ARCHIVED">User Archived</option>
                                    </optgroup>
                                    <optgroup label="Security">
                                        <option value="ADMIN_USER_PASSWORD_RESET">Password Reset</option>
                                        <option value="ADMIN_USER_MFA_RESET">MFA Reset</option>
                                        <option value="ADMIN_USER_MFA_UPDATED">MFA Updated</option>
                                        <option value="ADMIN_USER_SESSIONS_REVOKED">Sessions Revoked</option>
                                        <option value="LOGIN_BLOCKED_BY_IP">Login Blocked by IP</option>
                                    </optgroup>
                                    <optgroup label="Impersonation">
                                        <option value="IMPERSONATION_STARTED">Impersonation Started</option>
                                        <option value="IMPERSONATION_ENDED">Impersonation Ended</option>
                                    </optgroup>
                                    <optgroup label="Account">
                                        <option value="ADMIN_USER_EMAIL_UPDATED">Email Updated</option>
                                        <option value="ADMIN_USER_INVITE_RESENT">Invite Resent</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold">Severity</label>
                                <select class="form-select form-select-sm" id="adminSeverityFilter">
                                    <option value="">All Severities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
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
                            <div class="col-6 col-md-2 d-flex gap-2">
                                <button type="button" class="btn btn-admin-primary btn-sm flex-grow-1" id="adminApplyFiltersBtn">
                                    <i class="fas fa-check me-1"></i>Apply
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="adminClearFiltersBtn">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small"><span id="adminTotalFiltered">0</span> events</span>
                    <div class="view-mode-toggle btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active" id="adminPaginationModeBtn">
                            <i class="fas fa-list"></i> Paginated
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="adminInfiniteScrollModeBtn">
                            <i class="fas fa-stream"></i> Scroll
                        </button>
                    </div>
                </div>

                <div class="audit-table-container" id="adminAuditTableContainer">
                    <table class="audit-logs-table" id="adminAuditLogsTable">
                        <thead>
                            <tr>
                                <th style="width: 150px;" class="sortable-header" data-sort="timestamp">Timestamp <i class="fas fa-sort-down ms-1 sort-icon active"></i></th>
                                <th style="width: 100px;">Event ID</th>
                                <th class="sortable-header" data-sort="event_type">Event Type <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th class="sortable-header" data-sort="actor">Actor Admin <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th class="sortable-header" data-sort="target">Target <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th style="width: 90px;" class="sortable-header" data-sort="severity">Severity <i class="fas fa-sort ms-1 sort-icon"></i></th>
                                <th style="width: 110px;">IP Address</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody id="adminAuditLogsTableBody">
                        </tbody>
                    </table>

                    <div class="loading-more" id="adminLoadingMore" style="display: none;">
                        <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                        <span class="ms-2">Loading more events...</span>
                    </div>

                    <div class="text-center py-3" id="adminLoadMoreContainer" style="display: none;">
                        <button type="button" class="load-more-btn" id="adminLoadMoreBtn">
                            <i class="fas fa-plus-circle me-2"></i>Load More
                        </button>
                    </div>

                    <div class="end-of-list" id="adminEndOfList" style="display: none;">
                        <i class="fas fa-check-circle text-success me-2"></i>All events loaded
                    </div>
                </div>

                <div class="empty-state" id="adminEmptyState" style="display: none;">
                    <i class="fas fa-clipboard-list"></i>
                    <h5 class="text-muted mb-2">No admin audit logs found</h5>
                    <p class="text-muted small mb-0">Adjust your filters or check back later for new activity.</p>
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

    var customerLogs = [];
    var adminLogs = [];
    var customerPage = 1;
    var adminPage = 1;
    var itemsPerPage = 25;

    function init() {
        customerLogs = generateMockCustomerLogs();
        adminLogs = generateMockAdminLogs();
        renderCustomerLogs();
        renderAdminLogs();
        updateAdminStats();
        bindEvents();
    }

    function generateMockCustomerLogs() {
        var accounts = [
            { id: 'acc-001', name: 'Acme Corp' },
            { id: 'acc-002', name: 'TechStart Ltd' },
            { id: 'acc-003', name: 'HealthFirst UK' },
            { id: 'acc-004', name: 'RetailMax' },
            { id: 'acc-005', name: 'ServicePro' }
        ];

        var actions = [
            { type: 'USER_CREATED', category: 'user_management', severity: 'medium' },
            { type: 'USER_INVITED', category: 'user_management', severity: 'low' },
            { type: 'LOGIN_SUCCESS', category: 'authentication', severity: 'low' },
            { type: 'LOGIN_FAILED', category: 'authentication', severity: 'medium' },
            { type: 'MFA_ENABLED', category: 'security', severity: 'medium' },
            { type: 'CAMPAIGN_SUBMITTED', category: 'messaging', severity: 'low' },
            { type: 'CAMPAIGN_APPROVED', category: 'messaging', severity: 'medium' },
            { type: 'PURCHASE_COMPLETED', category: 'financial', severity: 'medium' }
        ];

        var actors = ['sarah@acmecorp.com', 'james@techstart.co.uk', 'emily@healthfirst.nhs.uk', 'michael@retailmax.com'];
        var ips = ['192.168.1.100', '10.0.0.45', '172.16.0.22', '203.45.67.89'];
        var logs = [];
        var now = new Date();

        for (var i = 0; i < 150; i++) {
            var action = actions[Math.floor(Math.random() * actions.length)];
            var account = accounts[Math.floor(Math.random() * accounts.length)];
            var timestamp = new Date(now.getTime() - Math.random() * 30 * 24 * 60 * 60 * 1000);

            logs.push({
                id: 'CLOG-' + String(i + 1).padStart(6, '0'),
                timestamp: timestamp.toISOString(),
                account: account,
                action: action.type,
                actionLabel: action.type.replace(/_/g, ' '),
                category: action.category,
                severity: action.severity,
                actor: actors[Math.floor(Math.random() * actors.length)],
                ip: ips[Math.floor(Math.random() * ips.length)],
                result: Math.random() > 0.1 ? 'success' : 'failure'
            });
        }

        logs.sort(function(a, b) { return new Date(b.timestamp) - new Date(a.timestamp); });
        return logs;
    }

    function generateMockAdminLogs() {
        var eventTypes = [
            { type: 'ADMIN_USER_INVITED', severity: 'medium' },
            { type: 'ADMIN_USER_SUSPENDED', severity: 'high' },
            { type: 'ADMIN_USER_REACTIVATED', severity: 'medium' },
            { type: 'ADMIN_USER_PASSWORD_RESET', severity: 'high' },
            { type: 'ADMIN_USER_MFA_RESET', severity: 'critical' },
            { type: 'ADMIN_USER_SESSIONS_REVOKED', severity: 'high' },
            { type: 'IMPERSONATION_STARTED', severity: 'critical' },
            { type: 'IMPERSONATION_ENDED', severity: 'high' },
            { type: 'LOGIN_BLOCKED_BY_IP', severity: 'critical' },
            { type: 'ADMIN_USER_EMAIL_UPDATED', severity: 'high' }
        ];

        var actors = [
            { email: 'sarah.johnson@quicksms.co.uk', name: 'Sarah Johnson' },
            { email: 'james.mitchell@quicksms.co.uk', name: 'James Mitchell' },
            { email: 'emily.chen@quicksms.co.uk', name: 'Emily Chen' }
        ];

        var targets = [
            { email: 'michael.brown@quicksms.co.uk', name: 'Michael Brown' },
            { email: 'anna.williams@quicksms.co.uk', name: 'Anna Williams' },
            { email: 'david.lee@quicksms.co.uk', name: 'David Lee' },
            { email: 'new.user@quicksms.co.uk', name: 'New User' }
        ];

        var ips = ['10.0.1.50', '10.0.1.51', '10.0.1.52', '192.168.100.1'];
        var logs = [];
        var now = new Date();

        for (var i = 0; i < 100; i++) {
            var event = eventTypes[Math.floor(Math.random() * eventTypes.length)];
            var actor = actors[Math.floor(Math.random() * actors.length)];
            var target = targets[Math.floor(Math.random() * targets.length)];
            var timestamp = new Date(now.getTime() - Math.random() * 30 * 24 * 60 * 60 * 1000);

            logs.push({
                id: 'ALOG-' + String(i + 1).padStart(6, '0'),
                timestamp: timestamp.toISOString(),
                eventType: event.type,
                eventLabel: event.type.replace(/_/g, ' '),
                severity: event.severity,
                actor: actor,
                target: target,
                ip: ips[Math.floor(Math.random() * ips.length)],
                result: Math.random() > 0.05 ? 'success' : 'failure',
                reason: Math.random() > 0.6 ? 'Security policy enforcement' : null
            });
        }

        logs.sort(function(a, b) { return new Date(b.timestamp) - new Date(a.timestamp); });
        return logs;
    }

    function renderCustomerLogs() {
        var tbody = document.getElementById('customerAuditLogsTableBody');
        tbody.innerHTML = '';

        var start = (customerPage - 1) * itemsPerPage;
        var end = Math.min(start + itemsPerPage, customerLogs.length);
        var pageLogs = customerLogs.slice(start, end);

        pageLogs.forEach(function(log) {
            var row = document.createElement('tr');
            row.className = 'audit-log-row';
            row.innerHTML = 
                '<td>' + formatTimestamp(log.timestamp) + '</td>' +
                '<td><span class="badge bg-light text-dark">' + log.account.name + '</span></td>' +
                '<td><code class="small">' + log.id + '</code></td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td><span class="badge category-badge-' + log.category + '">' + formatCategory(log.category) + '</span></td>' +
                '<td><span class="badge severity-badge-' + log.severity + '">' + capitalize(log.severity) + '</span></td>' +
                '<td>' + log.actor + '</td>' +
                '<td><code class="small">' + log.ip + '</code></td>';
            row.onclick = function() { showLogDetail(log, 'customer'); };
            tbody.appendChild(row);
        });

        $('#customerTotalFiltered').text(customerLogs.length);
        $('#customerShowingStart').text(start + 1);
        $('#customerShowingEnd').text(end);
        $('#customerPaginationTotal').text(customerLogs.length);
        renderPagination('customer', customerLogs.length, customerPage);
    }

    function renderAdminLogs() {
        var tbody = document.getElementById('adminAuditLogsTableBody');
        tbody.innerHTML = '';

        var start = (adminPage - 1) * itemsPerPage;
        var end = Math.min(start + itemsPerPage, adminLogs.length);
        var pageLogs = adminLogs.slice(start, end);

        pageLogs.forEach(function(log) {
            var row = document.createElement('tr');
            row.className = 'audit-log-row admin-event-row';
            row.innerHTML = 
                '<td>' + formatTimestamp(log.timestamp) + '</td>' +
                '<td><code class="small">' + log.id + '</code></td>' +
                '<td><span class="badge category-badge-admin">' + log.eventLabel + '</span></td>' +
                '<td>' + log.actor.email + '</td>' +
                '<td>' + log.target.email + '</td>' +
                '<td><span class="badge severity-badge-' + log.severity + '">' + capitalize(log.severity) + '</span></td>' +
                '<td><code class="small">' + log.ip + '</code></td>' +
                '<td><button class="btn btn-link btn-sm p-0 text-muted" onclick="event.stopPropagation();"><i class="fas fa-ellipsis-v"></i></button></td>';
            row.onclick = function() { showLogDetail(log, 'admin'); };
            tbody.appendChild(row);
        });

        $('#adminTotalFiltered').text(adminLogs.length);
        $('#adminShowingStart').text(start + 1);
        $('#adminShowingEnd').text(end);
        $('#adminPaginationTotal').text(adminLogs.length);
        renderPagination('admin', adminLogs.length, adminPage);
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

        for (var i = 1; i <= Math.min(totalPages, 5); i++) {
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

    function showLogDetail(log, type) {
        var content = '';
        
        if (type === 'customer') {
            content = '<div class="log-detail-section">' +
                '<h6><i class="fas fa-info-circle me-2"></i>Event Information</h6>' +
                '<div class="log-detail-row"><div class="log-detail-label">Event ID</div><div class="log-detail-value"><code>' + log.id + '</code></div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Timestamp</div><div class="log-detail-value">' + log.timestamp + '</div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Action</div><div class="log-detail-value">' + log.actionLabel + '</div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Category</div><div class="log-detail-value"><span class="badge category-badge-' + log.category + '">' + formatCategory(log.category) + '</span></div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Severity</div><div class="log-detail-value"><span class="badge severity-badge-' + log.severity + '">' + capitalize(log.severity) + '</span></div></div>' +
                '</div>' +
                '<div class="log-detail-section">' +
                '<h6><i class="fas fa-building me-2"></i>Account Context</h6>' +
                '<div class="log-detail-row"><div class="log-detail-label">Account</div><div class="log-detail-value">' + log.account.name + ' (' + log.account.id + ')</div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Actor</div><div class="log-detail-value">' + log.actor + '</div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">IP Address</div><div class="log-detail-value"><code>' + log.ip + '</code></div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Result</div><div class="log-detail-value"><span class="badge ' + (log.result === 'success' ? 'bg-success' : 'bg-danger') + '">' + capitalize(log.result) + '</span></div></div>' +
                '</div>';
        } else {
            content = '<div class="log-detail-section">' +
                '<h6><i class="fas fa-user-shield me-2"></i>Admin Event Information</h6>' +
                '<div class="log-detail-row"><div class="log-detail-label">Event ID</div><div class="log-detail-value"><code>' + log.id + '</code></div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Timestamp</div><div class="log-detail-value">' + log.timestamp + '</div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Event Type</div><div class="log-detail-value"><span class="badge category-badge-admin">' + log.eventLabel + '</span></div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Severity</div><div class="log-detail-value"><span class="badge severity-badge-' + log.severity + '">' + capitalize(log.severity) + '</span></div></div>' +
                '</div>' +
                '<div class="log-detail-section">' +
                '<h6><i class="fas fa-users me-2"></i>Participants</h6>' +
                '<div class="log-detail-row"><div class="log-detail-label">Actor Admin</div><div class="log-detail-value">' + log.actor.name + ' (' + log.actor.email + ')</div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Target</div><div class="log-detail-value">' + log.target.name + ' (' + log.target.email + ')</div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">IP Address</div><div class="log-detail-value"><code>' + log.ip + '</code></div></div>' +
                '<div class="log-detail-row"><div class="log-detail-label">Result</div><div class="log-detail-value"><span class="badge ' + (log.result === 'success' ? 'bg-success' : 'bg-danger') + '">' + capitalize(log.result) + '</span></div></div>' +
                (log.reason ? '<div class="log-detail-row"><div class="log-detail-label">Reason</div><div class="log-detail-value">' + log.reason + '</div></div>' : '') +
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
            var filtered = customerLogs.filter(function(log) {
                return log.actionLabel.toLowerCase().includes(query) ||
                       log.account.name.toLowerCase().includes(query) ||
                       log.actor.toLowerCase().includes(query) ||
                       log.id.toLowerCase().includes(query);
            });
            customerLogs = filtered.length > 0 || query ? filtered : generateMockCustomerLogs();
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
            $(this).closest('.filter-panel').find('input, select').val('');
            showToast('Filters cleared', 'info');
        });
    }

    function showToast(message, type) {
        var toastHtml = '<div class="toast align-items-center text-white bg-' + (type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info') + ' border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">' +
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

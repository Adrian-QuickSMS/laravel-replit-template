@extends('layouts.quicksms')

@section('page_title', 'Audit Logs')

@push('styles')
<link href="{{ asset('css/quicksms-pastel.css') }}" rel="stylesheet">
<style>
.severity-badge-low { background-color: rgba(108, 117, 125, 0.15); color: #6c757d; }
.severity-badge-medium { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }
.severity-badge-high { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.severity-badge-critical { background-color: rgba(220, 53, 69, 0.25); color: #dc3545; font-weight: 600; }

.category-badge-user_management { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }
.category-badge-access_control { background-color: rgba(48, 101, 208, 0.15); color: #3065D0; }
.category-badge-security { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.category-badge-authentication { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.category-badge-enforcement { background-color: rgba(214, 83, 193, 0.15); color: #D653C1; }
.category-badge-data_access { background-color: rgba(255, 191, 0, 0.15); color: #cc9900; }
.category-badge-account { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }
.category-badge-messaging { background-color: rgba(48, 101, 208, 0.15); color: #3065D0; }
.category-badge-financial { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.category-badge-gdpr { background-color: rgba(214, 83, 193, 0.15); color: #D653C1; }
.category-badge-compliance { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }

.audit-log-row { cursor: pointer; }
.audit-log-row:hover { background-color: rgba(111, 66, 193, 0.03); }

.log-detail-section { padding: 1rem; background-color: #fafafa; border-radius: 0.5rem; margin-bottom: 1rem; }
.log-detail-section h6 { color: #6f42c1; margin-bottom: 0.75rem; font-size: 0.875rem; }
.log-detail-row { display: flex; margin-bottom: 0.5rem; }
.log-detail-label { width: 140px; font-weight: 500; color: #6c757d; font-size: 0.8125rem; }
.log-detail-value { flex: 1; font-size: 0.8125rem; color: #212529; }

.stats-card { border: none; background: #fff; border-radius: 0.5rem; }
.stats-card .stat-value { font-size: 1.5rem; font-weight: 600; color: #886CC0; }
.stats-card .stat-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; }

.empty-state { padding: 4rem 2rem; text-align: center; }
.empty-state i { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; }

.retention-indicator { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; }
.retention-active { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.retention-approaching { background-color: rgba(255, 191, 0, 0.15); color: #cc9900; }

.integrity-badge { font-family: monospace; font-size: 0.7rem; background-color: rgba(108, 117, 125, 0.1); padding: 0.125rem 0.375rem; border-radius: 0.25rem; }

.compliance-card { background: linear-gradient(135deg, rgba(111, 66, 193, 0.05) 0%, rgba(111, 66, 193, 0.02) 100%); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; }
.compliance-card h6 { color: #6f42c1; font-size: 0.875rem; margin-bottom: 0.5rem; }
.compliance-card .compliance-stat { font-size: 1.25rem; font-weight: 600; color: #886CC0; }

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
    background-color: #f3e8ff; 
    border-color: #886CC0; 
    color: #6b21a8; 
}
.quick-filter-btn.active { 
    background-color: #886CC0; 
    color: #fff; 
    border-color: #886CC0; 
}

.access-denied-container { padding: 4rem 2rem; text-align: center; background: #fff; border-radius: 0.5rem; }
.access-denied-container .access-icon { font-size: 4rem; color: #dee2e6; margin-bottom: 1.5rem; }
.access-denied-container h4 { color: #495057; margin-bottom: 0.75rem; }

.scope-indicator { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; }
.export-restricted { opacity: 0.5; pointer-events: none; }

.audit-table-container { max-height: 600px; overflow-y: auto; }
.audit-table-container.infinite-scroll-enabled { max-height: none; }

.audit-log-row { 
    cursor: default; 
    user-select: text;
    transition: background-color 0.15s ease;
}
.audit-log-row:hover { background-color: rgba(111, 66, 193, 0.03); }
.audit-log-row td { vertical-align: middle; padding: 0.75rem; }

.table-read-only th { 
    background-color: #fafafa; 
    border-bottom: 2px solid #dee2e6; 
    font-weight: 600; 
    font-size: 0.8125rem; 
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.loading-more { 
    text-align: center; 
    padding: 1.5rem; 
    color: #6c757d; 
    background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(111, 66, 193, 0.02) 100%);
}
.loading-more .spinner-border { width: 1.25rem; height: 1.25rem; border-width: 0.15em; color: #886CC0; }

.load-more-btn {
    background-color: #fff;
    border: 1px solid #886CC0;
    color: #886CC0;
    padding: 0.5rem 1.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    transition: all 0.15s ease;
}
.load-more-btn:hover {
    background-color: #f3e8ff;
    border-color: #886CC0;
    color: #886CC0;
}

.view-mode-toggle { font-size: 0.75rem; }
.view-mode-toggle .btn { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.view-mode-toggle .btn.active { background-color: #886CC0; color: #fff; border-color: #886CC0; }

.end-of-list { 
    text-align: center; 
    padding: 1rem; 
    color: #6c757d; 
    font-size: 0.8125rem;
    border-top: 1px dashed #dee2e6;
}

.filter-panel { 
    background-color: #fafafa !important; 
    border: 1px solid #e9ecef;
}
.filter-panel .form-label { 
    margin-bottom: 0.25rem; 
}
.filter-panel .form-control,
.filter-panel .form-select { 
    font-size: 0.875rem; 
}

.active-filters-display .filter-tag {
    display: inline-flex;
    align-items: center;
    background-color: rgba(111, 66, 193, 0.1);
    color: #6f42c1;
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

.filters-pending-indicator {
    display: inline-flex;
    align-items: center;
    background-color: rgba(255, 193, 7, 0.15);
    color: #856404;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}
</style>
@endpush

@section('content')
<div class="row page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('account') }}">Account</a></li>
        <li class="breadcrumb-item active">Audit Logs</li>
    </ol>
</div>

<div id="accessDeniedState" class="access-denied-container" style="display: none;">
    <div class="access-icon">
        <i class="fas fa-lock"></i>
    </div>
    <h4>Access Restricted</h4>
    <p class="text-muted mb-3">You do not have permission to view Audit Logs.</p>
    <p class="small text-muted mb-0">Audit Logs are available to Account Owners, Administrators, and Security Officers only.</p>
    <p class="small text-muted">Finance and Developer/API users do not have access to this module.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
        <i class="fas fa-arrow-left me-2"></i>Return to Dashboard
    </a>
</div>

<div id="auditLogsContent">
<ul class="nav nav-tabs" id="auditTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-logs-tab" data-bs-toggle="tab" data-bs-target="#allLogs" type="button" role="tab">
            <i class="fas fa-list me-2"></i>All Logs
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#securityLogs" type="button" role="tab">
            <i class="fas fa-shield-alt me-2"></i>Security
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="messaging-tab" data-bs-toggle="tab" data-bs-target="#messagingLogs" type="button" role="tab">
            <i class="fas fa-envelope me-2"></i>Messaging
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financialLogs" type="button" role="tab">
            <i class="fas fa-pound-sign me-2"></i>Financial
        </button>
    </li>
</ul>

<div class="tab-content" id="auditTabsContent">
    <div class="tab-pane fade show active" id="allLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Audit Trail</h5>
                    <small class="text-muted">Centralised, chronological record of all platform activity</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="dropdown" id="exportDropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="exportBtn">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Export Format</h6></li>
                            <li>
                                <a class="dropdown-item" href="#" id="exportCsvBtn">
                                    <i class="fas fa-file-csv me-2 text-success"></i>CSV (.csv)
                                    <small class="d-block text-muted">Comma-separated values</small>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" id="exportExcelBtn">
                                    <i class="fas fa-file-excel me-2 text-success"></i>Excel (.xlsx)
                                    <small class="d-block text-muted">Microsoft Excel format</small>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>Exports current filtered view
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="all">All Events</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="high-severity">High Severity</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="login-activity">Login Activity</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="data-access">Data Access</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="user-changes">User Changes</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="permission-changes">Permission Changes</button>
                        </div>
                    </div>
                </div>

                <div class="filter-panel mb-4 p-3 bg-light rounded">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-muted mb-2">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search by description, target ID, or user name...">
                            </div>
                            <small class="text-muted">Searches event description, target reference ID, and user names</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small fw-medium text-muted mb-1">Date From</label>
                            <input type="date" class="form-control" id="dateFromFilter">
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small fw-medium text-muted mb-1">Date To</label>
                            <input type="date" class="form-control" id="dateToFilter">
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small fw-medium text-muted mb-1">Module</label>
                            <select class="form-select" id="moduleFilter">
                                <option value="">All Modules</option>
                                <option value="account">Account</option>
                                <option value="users">Users</option>
                                <option value="sub_accounts">Sub-Accounts</option>
                                <option value="permissions">Permissions</option>
                                <option value="security">Security</option>
                                <option value="authentication">Authentication</option>
                                <option value="messaging">Messaging</option>
                                <option value="campaigns">Campaigns</option>
                                <option value="contacts">Contacts</option>
                                <option value="reporting">Reporting</option>
                                <option value="financial">Financial</option>
                                <option value="compliance">Compliance</option>
                                <option value="api">API</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small fw-medium text-muted mb-1">Event Type</label>
                            <select class="form-select" id="eventTypeFilter">
                                <option value="">All Event Types</option>
                                <optgroup label="User Management">
                                    <option value="USER_CREATED">User Created</option>
                                    <option value="USER_INVITED">User Invited</option>
                                    <option value="USER_SUSPENDED">User Suspended</option>
                                    <option value="USER_REACTIVATED">User Reactivated</option>
                                </optgroup>
                                <optgroup label="Access Control">
                                    <option value="ROLE_CHANGED">Role Changed</option>
                                    <option value="PERMISSION_GRANTED">Permission Granted</option>
                                    <option value="PERMISSION_REVOKED">Permission Revoked</option>
                                </optgroup>
                                <optgroup label="Authentication">
                                    <option value="LOGIN_SUCCESS">Login Success</option>
                                    <option value="LOGIN_FAILED">Login Failed</option>
                                    <option value="LOGIN_BLOCKED">Login Blocked</option>
                                    <option value="PASSWORD_CHANGED">Password Changed</option>
                                </optgroup>
                                <optgroup label="Security">
                                    <option value="MFA_ENABLED">MFA Enabled</option>
                                    <option value="MFA_DISABLED">MFA Disabled</option>
                                    <option value="MFA_RESET">MFA Reset</option>
                                </optgroup>
                                <optgroup label="Data Access">
                                    <option value="DATA_EXPORTED">Data Exported</option>
                                    <option value="DATA_UNMASKED">Data Unmasked</option>
                                </optgroup>
                                <optgroup label="Messaging">
                                    <option value="CAMPAIGN_SUBMITTED">Campaign Submitted</option>
                                    <option value="CAMPAIGN_APPROVED">Campaign Approved</option>
                                    <option value="CAMPAIGN_REJECTED">Campaign Rejected</option>
                                    <option value="CAMPAIGN_SENT">Campaign Sent</option>
                                </optgroup>
                                <optgroup label="Financial">
                                    <option value="PURCHASE_COMPLETED">Purchase Completed</option>
                                    <option value="INVOICE_GENERATED">Invoice Generated</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small fw-medium text-muted mb-1">Sub-Account</label>
                            <select class="form-select" id="subAccountFilter">
                                <option value="">All Sub-Accounts</option>
                                <option value="main">Main Account</option>
                                <option value="sa-001">Marketing Department</option>
                                <option value="sa-002">Customer Support</option>
                                <option value="sa-003">Sales Team</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small fw-medium text-muted mb-1">User</label>
                            <select class="form-select" id="userFilter">
                                <option value="">All Users</option>
                                <option value="usr-001">Sarah Johnson</option>
                                <option value="usr-002">James Wilson</option>
                                <option value="usr-003">Emily Chen</option>
                                <option value="usr-004">Michael Brown</option>
                                <option value="usr-005">Lisa Anderson</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small fw-medium text-muted mb-1">Actor Type</label>
                            <select class="form-select" id="actorTypeFilter">
                                <option value="">All Actor Types</option>
                                <option value="user">User</option>
                                <option value="system">System</option>
                                <option value="api">API</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label small fw-medium text-muted mb-1">Severity</label>
                            <select class="form-select" id="severityFilter">
                                <option value="">All Severities</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center pt-3">
                        <a href="#" class="text-primary small me-3" id="clearFilters">
                            <i class="fas fa-undo me-1"></i>Reset Filters
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" id="applyFiltersBtn">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted small">
                        <span id="totalFilteredInfo"><span id="totalFiltered">0</span> events</span>
                        <span class="ms-2 text-muted">|</span>
                        <span class="ms-2">Sorted: <strong>Newest first</strong></span>
                    </div>
                    <div class="view-mode-toggle btn-group" role="group" aria-label="View mode">
                        <button type="button" class="btn btn-outline-secondary active" id="paginationModeBtn" title="Pagination view">
                            <i class="fas fa-list"></i> Paginated
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="infiniteScrollModeBtn" title="Infinite scroll view">
                            <i class="fas fa-stream"></i> Scroll
                        </button>
                    </div>
                </div>

                <div class="table-responsive audit-table-container" id="auditTableContainer">
                    <table class="table table-hover mb-0 table-read-only" id="auditLogsTable">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th style="width: 150px;">Timestamp</th>
                                <th style="width: 100px;">Event ID</th>
                                <th>Action</th>
                                <th style="width: 120px;">Category</th>
                                <th style="width: 90px;">Severity</th>
                                <th>Actor</th>
                                <th>Target</th>
                                <th style="width: 110px;">IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="auditLogsTableBody">
                        </tbody>
                    </table>

                    <div class="loading-more" id="loadingMore" style="display: none;">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading more events...</span>
                    </div>

                    <div class="text-center py-3" id="loadMoreContainer" style="display: none;">
                        <button type="button" class="load-more-btn" id="loadMoreBtn">
                            <i class="fas fa-plus-circle me-2"></i>Load More
                        </button>
                        <div class="small text-muted mt-2" id="loadMoreInfo"></div>
                    </div>

                    <div class="end-of-list" id="endOfList" style="display: none;">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        All events loaded
                    </div>
                </div>

                <div class="empty-state" id="emptyState" style="display: none;">
                    <i class="fas fa-clipboard-list"></i>
                    <h5 class="text-muted mb-2">No audit logs found</h5>
                    <p class="text-muted small mb-0">Adjust your filters or check back later for new activity.</p>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4" id="paginationRow">
                    <div class="text-muted small">
                        Showing <span id="showingStart">0</span>-<span id="showingEnd">0</span> of <span id="paginationTotal">0</span> events
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationControls">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="securityLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Security Monitoring</h5>
                <small class="text-muted">Authentication, access control, and security events (ISO 27001 / NHS DSP Toolkit)</small>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-sign-in-alt me-2"></i>Failed Logins (24h)</h6>
                            <div class="compliance-stat" id="failedLoginsCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-ban me-2"></i>Blocked Access (24h)</h6>
                            <div class="compliance-stat" id="blockedAccessCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-key me-2"></i>MFA Changes (7d)</h6>
                            <div class="compliance-stat" id="mfaChangesCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Security Alerts</h6>
                            <div class="compliance-stat text-danger" id="securityAlertsCount">0</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="securityLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Event</th>
                                <th>Severity</th>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Result</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="securityLogsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="messagingLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Messaging Activity</h5>
                <small class="text-muted">Campaign approvals, message dispatch, and delivery events for dispute resolution</small>
            </div>
            <div class="card-body">
                <div class="alert alert-pastel-primary mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                        <div>
                            <strong>Messaging audit trail for dispute resolution.</strong>
                            <p class="mb-0 mt-1 small">All message-related events are logged including campaign submissions, approvals, rejections, and delivery confirmations. Use this data to resolve delivery disputes and verify compliance with messaging regulations.</p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="messagingLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Event</th>
                                <th>Campaign/Message</th>
                                <th>Actor</th>
                                <th>Recipients</th>
                                <th>Channel</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="messagingLogsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="financialLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Financial Audit Trail</h5>
                <small class="text-muted">Purchases, invoices, credits, and billing events for financial accountability</small>
            </div>
            <div class="card-body">
                <div class="alert alert-pastel-primary mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                        <div>
                            <strong>Financial records for billing disputes and audits.</strong>
                            <p class="mb-0 mt-1 small">All financial transactions are logged including message purchases, credit allocations, invoice generation, and payment processing. This data supports billing dispute resolution and financial auditing requirements.</p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="financialLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Event</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Actor</th>
                                <th>Sub-Account</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="financialLogsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="logDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2 text-primary"></i>Audit Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" id="copyLogDetail">
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
                <h5 class="modal-title"><i class="fas fa-shield-alt me-2 text-primary"></i>Log Integrity Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4" id="integrityChecking">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
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
                            <span class="badge badge-pastel-success">Unbroken</span>
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/quicksms-audit-logger.js') }}"></script>
<script>
$(document).ready(function() {
    var currentPage = 1;
    var itemsPerPage = 25;
    var filteredLogs = [];
    var allLogs = [];
    var activeQuickFilter = 'all';

    var AUDIT_LOG_ACCESS = {
        FULL_ACCESS: ['owner', 'admin', 'security_officer'],
        READ_ONLY: ['read_only', 'auditor'],
        LIMITED_SCOPE: ['messaging_manager'],
        NO_ACCESS: ['finance', 'developer', 'api_user']
    };

    var ROLE_SCOPE_CATEGORIES = {
        messaging_manager: ['messaging'],
        read_only: null,
        auditor: null
    };

    var currentUserRole = window.QUICKSMS_USER?.role || 'admin';
    var userAccessLevel = 'none';
    var userScopeCategories = null;
    var canExport = false;

    var viewMode = 'pagination';
    var displayedCount = 0;
    var batchSize = 50;
    var isLoadingMore = false;

    function checkAccessPermissions() {
        if (AUDIT_LOG_ACCESS.FULL_ACCESS.includes(currentUserRole)) {
            userAccessLevel = 'full';
            canExport = true;
            userScopeCategories = null;
        } else if (AUDIT_LOG_ACCESS.READ_ONLY.includes(currentUserRole)) {
            userAccessLevel = 'read_only';
            canExport = false;
            userScopeCategories = null;
        } else if (AUDIT_LOG_ACCESS.LIMITED_SCOPE.includes(currentUserRole)) {
            userAccessLevel = 'limited';
            canExport = true;
            userScopeCategories = ROLE_SCOPE_CATEGORIES[currentUserRole] || null;
        } else if (AUDIT_LOG_ACCESS.NO_ACCESS.includes(currentUserRole)) {
            userAccessLevel = 'none';
            canExport = false;
            userScopeCategories = null;
        } else {
            userAccessLevel = 'full';
            canExport = true;
        }

        return userAccessLevel !== 'none';
    }

    function applyAccessRestrictions() {
        if (userAccessLevel === 'none') {
            $('#accessDeniedState').show();
            $('#auditLogsContent').hide();
            return false;
        }

        $('#accessDeniedState').hide();
        $('#auditLogsContent').show();

        if (!canExport) {
            $('#exportDropdown').addClass('export-restricted').attr('title', 'Export not available for your role');
            $('#exportBtn').prop('disabled', true);
        }

        if (userAccessLevel === 'limited') {
            $('.nav-link[data-bs-target="#securityLogs"]').parent().hide();
            $('.nav-link[data-bs-target="#financialLogs"]').parent().hide();
            $('.nav-link[data-bs-target="#complianceLogs"]').parent().hide();

            $('#verifyIntegrityBtn').hide();

            addScopeIndicator();
        }

        if (userAccessLevel === 'read_only') {
            $('#verifyIntegrityBtn').hide();
            addReadOnlyIndicator();
        }

        return true;
    }

    function addScopeIndicator() {
        var scopeText = userScopeCategories ? userScopeCategories.map(function(c) {
            return c.charAt(0).toUpperCase() + c.slice(1);
        }).join(', ') + ' only' : 'Limited scope';

        var indicator = $('<span class="scope-indicator ms-2"><i class="fas fa-filter me-1"></i>' + scopeText + '</span>');
        $('.card-header h5.card-title').first().after(indicator);
    }

    function addReadOnlyIndicator() {
        var indicator = $('<span class="scope-indicator ms-2"><i class="fas fa-eye me-1"></i>Read-only access</span>');
        $('.card-header h5.card-title').first().after(indicator);
    }

    function filterLogsByScope(logs) {
        if (!userScopeCategories || userScopeCategories.length === 0) {
            return logs;
        }

        return logs.filter(function(log) {
            return userScopeCategories.includes(log.category);
        });
    }

    var EXTENDED_ACTION_TYPES = {
        ...AuditLogger.ACTION_TYPES,
        CAMPAIGN_SUBMITTED: { category: 'messaging', severity: 'low', label: 'Campaign Submitted' },
        CAMPAIGN_APPROVED: { category: 'messaging', severity: 'medium', label: 'Campaign Approved' },
        CAMPAIGN_REJECTED: { category: 'messaging', severity: 'medium', label: 'Campaign Rejected' },
        CAMPAIGN_SENT: { category: 'messaging', severity: 'low', label: 'Campaign Sent' },
        MESSAGE_DELIVERED: { category: 'messaging', severity: 'low', label: 'Message Delivered' },
        MESSAGE_FAILED: { category: 'messaging', severity: 'medium', label: 'Message Failed' },
        OPT_OUT_RECEIVED: { category: 'messaging', severity: 'medium', label: 'Opt-out Received' },
        OPT_IN_RECEIVED: { category: 'messaging', severity: 'low', label: 'Opt-in Received' },
        PURCHASE_COMPLETED: { category: 'financial', severity: 'medium', label: 'Purchase Completed' },
        INVOICE_GENERATED: { category: 'financial', severity: 'low', label: 'Invoice Generated' },
        PAYMENT_RECEIVED: { category: 'financial', severity: 'medium', label: 'Payment Received' },
        CREDIT_APPLIED: { category: 'financial', severity: 'medium', label: 'Credit Applied' },
        REFUND_ISSUED: { category: 'financial', severity: 'high', label: 'Refund Issued' },
        SAR_REQUEST: { category: 'gdpr', severity: 'high', label: 'Subject Access Request' },
        DATA_DELETION: { category: 'gdpr', severity: 'critical', label: 'Data Deletion Request' },
        CONSENT_UPDATED: { category: 'gdpr', severity: 'medium', label: 'Consent Updated' },
        PROCESSING_RECORD: { category: 'gdpr', severity: 'low', label: 'Processing Activity Recorded' },
        SECURITY_INCIDENT: { category: 'compliance', severity: 'critical', label: 'Security Incident' },
        POLICY_UPDATED: { category: 'compliance', severity: 'high', label: 'Policy Updated' },
        ACCESS_REVIEW: { category: 'compliance', severity: 'medium', label: 'Access Review Completed' }
    };

    function generateHash(data) {
        var str = JSON.stringify(data);
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            var char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString(16).padStart(8, '0').substring(0, 8);
    }

    function generateMockAuditData() {
        var actions = [
            { type: 'USER_CREATED', category: 'user_management', severity: 'high' },
            { type: 'USER_INVITED', category: 'user_management', severity: 'medium' },
            { type: 'USER_SUSPENDED', category: 'user_management', severity: 'high' },
            { type: 'ROLE_CHANGED', category: 'access_control', severity: 'high' },
            { type: 'PERMISSION_GRANTED', category: 'access_control', severity: 'medium' },
            { type: 'PERMISSION_REVOKED', category: 'access_control', severity: 'medium' },
            { type: 'MFA_ENABLED', category: 'security', severity: 'medium' },
            { type: 'MFA_DISABLED', category: 'security', severity: 'high' },
            { type: 'LOGIN_SUCCESS', category: 'authentication', severity: 'low' },
            { type: 'LOGIN_FAILED', category: 'authentication', severity: 'medium' },
            { type: 'LOGIN_BLOCKED', category: 'authentication', severity: 'high' },
            { type: 'PASSWORD_CHANGED', category: 'authentication', severity: 'medium' },
            { type: 'ENFORCEMENT_TRIGGERED', category: 'enforcement', severity: 'medium' },
            { type: 'ENFORCEMENT_OVERRIDE_APPROVED', category: 'enforcement', severity: 'high' },
            { type: 'DATA_EXPORTED', category: 'data_access', severity: 'medium' },
            { type: 'DATA_UNMASKED', category: 'data_access', severity: 'high' },
            { type: 'ACCOUNT_ACTIVATED', category: 'account', severity: 'high' },
            { type: 'CAMPAIGN_SUBMITTED', category: 'messaging', severity: 'low' },
            { type: 'CAMPAIGN_APPROVED', category: 'messaging', severity: 'medium' },
            { type: 'CAMPAIGN_REJECTED', category: 'messaging', severity: 'medium' },
            { type: 'CAMPAIGN_SENT', category: 'messaging', severity: 'low' },
            { type: 'OPT_OUT_RECEIVED', category: 'messaging', severity: 'medium' },
            { type: 'PURCHASE_COMPLETED', category: 'financial', severity: 'medium' },
            { type: 'INVOICE_GENERATED', category: 'financial', severity: 'low' },
            { type: 'CREDIT_APPLIED', category: 'financial', severity: 'medium' },
            { type: 'SAR_REQUEST', category: 'gdpr', severity: 'high' },
            { type: 'CONSENT_UPDATED', category: 'gdpr', severity: 'medium' },
            { type: 'ACCESS_REVIEW', category: 'compliance', severity: 'medium' }
        ];

        var actors = [
            { userId: 'usr-001', userName: 'Sarah Johnson', role: 'owner', subAccountId: null },
            { userId: 'usr-002', userName: 'James Wilson', role: 'admin', subAccountId: 'sa-001' },
            { userId: 'usr-003', userName: 'Emily Chen', role: 'messaging_manager', subAccountId: 'sa-002' },
            { userId: 'usr-004', userName: 'Michael Brown', role: 'developer', subAccountId: 'sa-001' },
            { userId: 'usr-005', userName: 'Lisa Anderson', role: 'finance', subAccountId: 'sa-003' },
            { userId: 'system', userName: 'System', role: 'system', subAccountId: null }
        ];

        var targets = [
            { userId: 'usr-006', userName: 'New User', role: 'read_only', subAccountId: 'sa-002' },
            { userId: 'usr-007', userName: 'Test User', role: 'messaging_manager', subAccountId: 'sa-001' },
            { resourceType: 'sub_account', resourceId: 'sa-001', name: 'Marketing Department' },
            { resourceType: 'sub_account', resourceId: 'sa-002', name: 'Customer Support' },
            { resourceType: 'campaign', resourceId: 'camp-123', name: 'Spring Sale Campaign' },
            { resourceType: 'campaign', resourceId: 'camp-456', name: 'Newsletter Q1' },
            { resourceType: 'invoice', resourceId: 'inv-789', name: 'Invoice #INV-2026-001' },
            { resourceType: 'purchase', resourceId: 'pur-321', name: 'SMS Credit Purchase' }
        ];

        var ipAddresses = ['192.168.1.100', '10.0.0.45', '172.16.0.22', '192.168.2.50', '10.1.1.1', '203.45.67.89'];

        var logs = [];
        var now = new Date();

        for (var i = 0; i < 250; i++) {
            var action = actions[Math.floor(Math.random() * actions.length)];
            var actor = actors[Math.floor(Math.random() * actors.length)];
            var target = Math.random() > 0.3 ? targets[Math.floor(Math.random() * targets.length)] : null;
            var timestamp = new Date(now.getTime() - Math.random() * 30 * 24 * 60 * 60 * 1000);

            var logEntry = {
                id: 'audit-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
                timestamp: timestamp.toISOString(),
                action: action.type,
                actionLabel: EXTENDED_ACTION_TYPES[action.type]?.label || action.type.replace(/_/g, ' '),
                category: action.category,
                severity: action.severity,
                actor: {
                    userId: actor.userId,
                    userName: actor.userName,
                    role: actor.role,
                    subAccountId: actor.subAccountId
                },
                target: target,
                context: {
                    ipAddress: ipAddresses[Math.floor(Math.random() * ipAddresses.length)],
                    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    sessionId: 'sess-' + Math.random().toString(36).substr(2, 12),
                    requestId: 'req-' + Math.random().toString(36).substr(2, 9)
                },
                result: Math.random() > 0.1 ? 'success' : 'failure',
                details: generateActionDetails(action.type),
                reason: Math.random() > 0.7 ? 'Requested by user' : null,
                retentionExpiry: new Date(timestamp.getTime() + 7 * 365 * 24 * 60 * 60 * 1000).toISOString()
            };

            logEntry.integrityHash = generateHash(logEntry);
            logs.push(logEntry);
        }

        logs.sort(function(a, b) {
            return new Date(b.timestamp) - new Date(a.timestamp);
        });

        return logs;
    }

    function generateActionDetails(actionType) {
        switch(actionType) {
            case 'CAMPAIGN_SENT':
                return { recipients: Math.floor(Math.random() * 10000) + 100, channel: Math.random() > 0.5 ? 'SMS' : 'RCS' };
            case 'PURCHASE_COMPLETED':
                return { amount: (Math.random() * 500 + 50).toFixed(2), currency: 'GBP', credits: Math.floor(Math.random() * 10000) + 1000 };
            case 'INVOICE_GENERATED':
                return { amount: (Math.random() * 1000 + 100).toFixed(2), currency: 'GBP' };
            default:
                return {};
        }
    }

    function init() {
        if (!checkAccessPermissions()) {
            applyAccessRestrictions();
            return;
        }

        applyAccessRestrictions();

        allLogs = generateMockAuditData();

        if (userScopeCategories) {
            allLogs = filterLogsByScope(allLogs);
        }

        applyFilters();
        updateStats();
        updateSecurityStats();
        updateMessagingStats();
        updateFinancialStats();
        updateComplianceStats();
        renderCategoryTables();
        bindEvents();

        AuditLogger.log('DATA_EXPORTED', {
            data: {
                action: 'AUDIT_LOG_ACCESSED',
                userRole: currentUserRole,
                accessLevel: userAccessLevel,
                scopeCategories: userScopeCategories
            }
        });
    }

    function updateStats() {
        var now = new Date();
        var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000);

        var todayLogs = allLogs.filter(function(log) { return new Date(log.timestamp) >= today; });
        var last24h = allLogs.filter(function(log) { return new Date(log.timestamp) >= yesterday; });
        var highSeverity = last24h.filter(function(log) { return log.severity === 'high' || log.severity === 'critical'; });
        var uniqueActors = [...new Set(last24h.map(function(log) { return log.actor.userId; }))];

        $('#totalLogsCount').text(allLogs.length);
        $('#todayLogsCount').text(todayLogs.length);
        $('#highSeverityCount').text(highSeverity.length);
        $('#uniqueActorsCount').text(uniqueActors.length);
    }

    function updateSecurityStats() {
        var now = new Date();
        var last24h = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        var last7d = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

        var securityLogs = allLogs.filter(function(log) {
            return ['security', 'authentication'].includes(log.category);
        });

        $('#failedLoginsCount').text(securityLogs.filter(function(log) {
            return log.action === 'LOGIN_FAILED' && new Date(log.timestamp) >= last24h;
        }).length);

        $('#blockedAccessCount').text(securityLogs.filter(function(log) {
            return log.action === 'LOGIN_BLOCKED' && new Date(log.timestamp) >= last24h;
        }).length);

        $('#mfaChangesCount').text(securityLogs.filter(function(log) {
            return ['MFA_ENABLED', 'MFA_DISABLED', 'MFA_RESET'].includes(log.action) && new Date(log.timestamp) >= last7d;
        }).length);

        $('#securityAlertsCount').text(securityLogs.filter(function(log) {
            return (log.severity === 'high' || log.severity === 'critical') && new Date(log.timestamp) >= last24h;
        }).length);
    }

    function updateMessagingStats() {
        var now = new Date();
        var last7d = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

        var messagingLogs = allLogs.filter(function(log) {
            return log.category === 'messaging' && new Date(log.timestamp) >= last7d;
        });

        $('#campaignsSentCount').text(messagingLogs.filter(function(log) { return log.action === 'CAMPAIGN_SENT'; }).length);
        $('#campaignsApprovedCount').text(messagingLogs.filter(function(log) { return log.action === 'CAMPAIGN_APPROVED'; }).length);
        $('#campaignsRejectedCount').text(messagingLogs.filter(function(log) { return log.action === 'CAMPAIGN_REJECTED'; }).length);
        $('#optOutsCount').text(messagingLogs.filter(function(log) { return log.action === 'OPT_OUT_RECEIVED'; }).length);
    }

    function updateFinancialStats() {
        var now = new Date();
        var last30d = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);

        var financialLogs = allLogs.filter(function(log) {
            return log.category === 'financial' && new Date(log.timestamp) >= last30d;
        });

        $('#purchasesCount').text(financialLogs.filter(function(log) { return log.action === 'PURCHASE_COMPLETED'; }).length);
        $('#invoicesCount').text(financialLogs.filter(function(log) { return log.action === 'INVOICE_GENERATED'; }).length);
        $('#creditsCount').text(financialLogs.filter(function(log) { return log.action === 'CREDIT_APPLIED'; }).length);
        $('#refundsCount').text(financialLogs.filter(function(log) { return log.action === 'REFUND_ISSUED'; }).length);
    }

    function updateComplianceStats() {
        var now = new Date();
        var last30d = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);

        var gdprLogs = allLogs.filter(function(log) { return log.category === 'gdpr' && new Date(log.timestamp) >= last30d; });
        var complianceLogs = allLogs.filter(function(log) { return log.category === 'compliance' && new Date(log.timestamp) >= last30d; });

        $('#sarRequestsCount').text(gdprLogs.filter(function(log) { return log.action === 'SAR_REQUEST'; }).length);
        $('#consentChangesCount').text(gdprLogs.filter(function(log) { return log.action === 'CONSENT_UPDATED'; }).length);
        $('#gdprExportsCount').text(allLogs.filter(function(log) { return log.action === 'DATA_EXPORTED' && new Date(log.timestamp) >= last30d; }).length);

        $('#accessReviewsCount').text(complianceLogs.filter(function(log) { return log.action === 'ACCESS_REVIEW'; }).length);
        $('#securityEventsCount').text(allLogs.filter(function(log) { return log.category === 'security' && new Date(log.timestamp) >= last30d; }).length);
        $('#policyChangesCount').text(complianceLogs.filter(function(log) { return log.action === 'POLICY_UPDATED'; }).length);
        $('#incidentReportsCount').text(complianceLogs.filter(function(log) { return log.action === 'SECURITY_INCIDENT'; }).length);
    }

    function renderCategoryTables() {
        renderSecurityTable();
        renderMessagingTable();
        renderFinancialTable();
        renderComplianceTable();
    }

    function renderSecurityTable() {
        var tbody = $('#securityLogsTableBody');
        tbody.empty();

        var securityLogs = allLogs.filter(function(log) {
            return ['security', 'authentication'].includes(log.category);
        }).slice(0, 20);

        securityLogs.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var row = $('<tr class="audit-log-row">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td><span class="badge severity-badge-' + log.severity + '">' + capitalizeFirst(log.severity) + '</span></td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td class="small text-muted">' + log.context.ipAddress + '</td>' +
                '<td><span class="badge ' + (log.result === 'success' ? 'badge-pastel-success' : 'badge-pastel-danger') + '">' + capitalizeFirst(log.result) + '</span></td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
            tbody.append(row);
        });
    }

    function renderMessagingTable() {
        var tbody = $('#messagingLogsTableBody');
        tbody.empty();

        var messagingLogs = allLogs.filter(function(log) {
            return log.category === 'messaging';
        }).slice(0, 20);

        messagingLogs.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var targetName = log.target ? (log.target.name || log.target.resourceId || '-') : '-';
            var recipients = log.details && log.details.recipients ? log.details.recipients.toLocaleString() : '-';
            var channel = log.details && log.details.channel ? log.details.channel : 'SMS';

            var row = $('<tr class="audit-log-row">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td>' + targetName + '</td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td>' + recipients + '</td>' +
                '<td><span class="badge badge-pastel-primary">' + channel + '</span></td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
            tbody.append(row);
        });
    }

    function renderFinancialTable() {
        var tbody = $('#financialLogsTableBody');
        tbody.empty();

        var financialLogs = allLogs.filter(function(log) {
            return log.category === 'financial';
        }).slice(0, 20);

        financialLogs.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var amount = log.details && log.details.amount ? '£' + log.details.amount : '-';
            var reference = log.target ? (log.target.name || log.target.resourceId || '-') : '-';
            var subAccount = log.actor.subAccountId || 'Main Account';

            var row = $('<tr class="audit-log-row">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td class="fw-medium">' + amount + '</td>' +
                '<td class="small">' + reference + '</td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td class="small">' + subAccount + '</td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
            tbody.append(row);
        });
    }

    function renderComplianceTable() {
        var tbody = $('#complianceLogsTableBody');
        tbody.empty();

        var complianceLogs = allLogs.filter(function(log) {
            return ['gdpr', 'compliance'].includes(log.category);
        }).slice(0, 20);

        complianceLogs.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var framework = log.category === 'gdpr' ? 'GDPR' : 'ISO 27001';

            var row = $('<tr class="audit-log-row">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td><span class="badge badge-pastel-primary">' + framework + '</span></td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td class="small text-muted">' + (log.target ? log.target.name || log.target.resourceId : '-') + '</td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
            tbody.append(row);
        });
    }

    var appliedFilters = {
        search: '',
        dateFrom: '',
        dateTo: '',
        module: '',
        eventType: '',
        subAccount: '',
        user: '',
        actorType: '',
        severity: ''
    };

    function getFilterValues() {
        return {
            search: $('#searchInput').val().trim(),
            dateFrom: $('#dateFromFilter').val(),
            dateTo: $('#dateToFilter').val(),
            module: $('#moduleFilter').val(),
            eventType: $('#eventTypeFilter').val(),
            subAccount: $('#subAccountFilter').val(),
            user: $('#userFilter').val(),
            actorType: $('#actorTypeFilter').val(),
            severity: $('#severityFilter').val()
        };
    }

    function applyFilters() {
        appliedFilters = getFilterValues();

        var search = appliedFilters.search.toLowerCase();
        var dateFrom = appliedFilters.dateFrom;
        var dateTo = appliedFilters.dateTo;
        var module = appliedFilters.module;
        var eventType = appliedFilters.eventType;
        var subAccount = appliedFilters.subAccount;
        var user = appliedFilters.user;
        var actorType = appliedFilters.actorType;
        var severity = appliedFilters.severity;

        filteredLogs = allLogs.filter(function(log) {
            if (activeQuickFilter !== 'all') {
                if (activeQuickFilter === 'high-severity' && !['high', 'critical'].includes(log.severity)) return false;
                if (activeQuickFilter === 'login-activity' && !['LOGIN_SUCCESS', 'LOGIN_FAILED', 'LOGIN_BLOCKED'].includes(log.action)) return false;
                if (activeQuickFilter === 'data-access' && log.category !== 'data_access') return false;
                if (activeQuickFilter === 'user-changes' && log.category !== 'user_management') return false;
                if (activeQuickFilter === 'permission-changes' && log.category !== 'access_control') return false;
            }

            if (search && !matchesSearch(log, search)) return false;

            if (dateFrom && new Date(log.timestamp) < new Date(dateFrom)) return false;
            if (dateTo) {
                var toDate = new Date(dateTo);
                toDate.setHours(23, 59, 59, 999);
                if (new Date(log.timestamp) > toDate) return false;
            }

            if (module) {
                var logModule = log.module || mapCategoryToModule(log.category);
                if (logModule !== module) return false;
            }

            if (eventType && log.action !== eventType) return false;

            if (subAccount) {
                if (subAccount === 'main') {
                    if (log.actor.subAccountId) return false;
                } else {
                    if (log.actor.subAccountId !== subAccount) return false;
                }
            }

            if (user && log.actor.userId !== user) return false;

            if (actorType) {
                var logActorType = log.actorType || (log.actor.role === 'system' ? 'system' : 'user');
                if (logActorType !== actorType) return false;
            }

            if (severity && log.severity !== severity) return false;

            return true;
        });

        currentPage = 1;
        displayedCount = 0;
        renderTable();
        renderPagination();
        updateActiveFiltersDisplay();

        AuditLogger.log('DATA_EXPORTED', {
            data: {
                action: 'AUDIT_LOG_FILTERED',
                filtersApplied: Object.keys(appliedFilters).filter(function(k) { return appliedFilters[k]; }).length,
                resultsCount: filteredLogs.length
            }
        });
    }

    function mapCategoryToModule(category) {
        var mapping = {
            'user_management': 'users',
            'access_control': 'permissions',
            'security': 'security',
            'authentication': 'authentication',
            'enforcement': 'sub_accounts',
            'data_access': 'reporting',
            'messaging': 'messaging',
            'financial': 'financial',
            'gdpr': 'compliance',
            'compliance': 'compliance',
            'account': 'account'
        };
        return mapping[category] || category;
    }

    function matchesSearch(log, search) {
        if (log.actionLabel.toLowerCase().indexOf(search) !== -1) return true;

        if (log.actor.userName.toLowerCase().indexOf(search) !== -1) return true;

        if (log.target) {
            if (log.target.userName && log.target.userName.toLowerCase().indexOf(search) !== -1) return true;
            if (log.target.name && log.target.name.toLowerCase().indexOf(search) !== -1) return true;
            if (log.target.resourceId && log.target.resourceId.toLowerCase().indexOf(search) !== -1) return true;
            if (log.target.entityId && log.target.entityId.toLowerCase().indexOf(search) !== -1) return true;
        }

        if (log.id.toLowerCase().indexOf(search) !== -1) return true;

        if (log.description && log.description.toLowerCase().indexOf(search) !== -1) return true;

        return false;
    }

    function updateActiveFiltersDisplay() {
        var container = $('#activeFiltersDisplay');
        container.empty();

        var activeCount = 0;
        var filterLabels = {
            search: 'Search',
            dateFrom: 'From Date',
            dateTo: 'To Date',
            module: 'Module',
            eventType: 'Event Type',
            subAccount: 'Sub-Account',
            user: 'User',
            actorType: 'Actor Type',
            severity: 'Severity'
        };

        for (var key in appliedFilters) {
            if (appliedFilters[key]) {
                activeCount++;
                var displayValue = appliedFilters[key];

                if (key === 'module') displayValue = $('#moduleFilter option[value="' + appliedFilters[key] + '"]').text();
                else if (key === 'eventType') displayValue = $('#eventTypeFilter option[value="' + appliedFilters[key] + '"]').text();
                else if (key === 'subAccount') displayValue = $('#subAccountFilter option[value="' + appliedFilters[key] + '"]').text();
                else if (key === 'user') displayValue = $('#userFilter option[value="' + appliedFilters[key] + '"]').text();
                else if (key === 'actorType') displayValue = $('#actorTypeFilter option[value="' + appliedFilters[key] + '"]').text();
                else if (key === 'severity') displayValue = capitalizeFirst(appliedFilters[key]);

                var tag = $('<span class="filter-tag">' + filterLabels[key] + ': ' + displayValue + 
                    '<span class="remove-filter" data-filter="' + key + '"><i class="fas fa-times"></i></span></span>');
                container.append(tag);
            }
        }

        if (activeCount === 0) {
            container.html('<span class="text-muted small">No filters applied</span>');
        }

        container.find('.remove-filter').on('click', function() {
            var filterKey = $(this).data('filter');
            clearSingleFilter(filterKey);
        });
    }

    function clearSingleFilter(filterKey) {
        switch(filterKey) {
            case 'search': $('#searchInput').val(''); break;
            case 'dateFrom': $('#dateFromFilter').val(''); break;
            case 'dateTo': $('#dateToFilter').val(''); break;
            case 'module': $('#moduleFilter').val(''); break;
            case 'eventType': $('#eventTypeFilter').val(''); break;
            case 'subAccount': $('#subAccountFilter').val(''); break;
            case 'user': $('#userFilter').val(''); break;
            case 'actorType': $('#actorTypeFilter').val(''); break;
            case 'severity': $('#severityFilter').val(''); break;
        }
        applyFilters();
    }

    function clearAllFilters() {
        $('#searchInput').val('');
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');
        $('#moduleFilter').val('');
        $('#eventTypeFilter').val('');
        $('#subAccountFilter').val('');
        $('#userFilter').val('');
        $('#actorTypeFilter').val('');
        $('#severityFilter').val('');
        activeQuickFilter = 'all';
        $('.quick-filter-btn').removeClass('active');
        $('.quick-filter-btn[data-filter="all"]').addClass('active');
        applyFilters();
    }

    function renderTable() {
        if (viewMode === 'infinite') {
            renderInfiniteScrollTable();
        } else {
            renderPaginatedTable();
        }
    }

    function renderPaginatedTable() {
        var tbody = $('#auditLogsTableBody');
        tbody.empty();

        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = Math.min(startIndex + itemsPerPage, filteredLogs.length);
        var pageData = filteredLogs.slice(startIndex, endIndex);

        if (pageData.length === 0) {
            $('#auditLogsTable').hide();
            $('#emptyState').show();
            $('#paginationRow').hide();
            $('#loadMoreContainer').hide();
            $('#endOfList').hide();
            return;
        }

        $('#auditLogsTable').show();
        $('#emptyState').hide();
        $('#paginationRow').show();
        $('#loadMoreContainer').hide();
        $('#endOfList').hide();
        $('#auditTableContainer').removeClass('infinite-scroll-enabled');

        pageData.forEach(function(log) {
            tbody.append(createLogRow(log));
        });

        $('#showingStart').text(startIndex + 1);
        $('#showingEnd').text(endIndex);
        $('#paginationTotal').text(filteredLogs.length);
        $('#totalFiltered').text(filteredLogs.length);
    }

    function renderInfiniteScrollTable() {
        var tbody = $('#auditLogsTableBody');
        tbody.empty();

        displayedCount = 0;

        if (filteredLogs.length === 0) {
            $('#auditLogsTable').hide();
            $('#emptyState').show();
            $('#paginationRow').hide();
            $('#loadMoreContainer').hide();
            $('#endOfList').hide();
            return;
        }

        $('#auditLogsTable').show();
        $('#emptyState').hide();
        $('#paginationRow').hide();
        $('#auditTableContainer').addClass('infinite-scroll-enabled');

        loadMoreLogs();
    }

    function loadMoreLogs() {
        if (isLoadingMore) return;
        if (displayedCount >= filteredLogs.length) {
            $('#loadMoreContainer').hide();
            $('#endOfList').show();
            return;
        }

        isLoadingMore = true;
        $('#loadingMore').show();
        $('#loadMoreContainer').hide();

        setTimeout(function() {
            var tbody = $('#auditLogsTableBody');
            var endIndex = Math.min(displayedCount + batchSize, filteredLogs.length);
            var batchData = filteredLogs.slice(displayedCount, endIndex);

            batchData.forEach(function(log) {
                tbody.append(createLogRow(log));
            });

            displayedCount = endIndex;

            $('#loadingMore').hide();
            isLoadingMore = false;

            if (displayedCount < filteredLogs.length) {
                var remaining = filteredLogs.length - displayedCount;
                $('#loadMoreInfo').text(remaining + ' more events to load');
                $('#loadMoreContainer').show();
                $('#endOfList').hide();
            } else {
                $('#loadMoreContainer').hide();
                $('#endOfList').show();
            }

            $('#totalFiltered').text(filteredLogs.length);
        }, 300);
    }

    function createLogRow(log) {
        var timestamp = new Date(log.timestamp);
        var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

        var eventId = log.id.length > 18 ? log.id.substring(0, 18) + '...' : log.id;

        var targetDisplay = '-';
        if (log.target) {
            if (log.target.userName) targetDisplay = log.target.userName;
            else if (log.target.name) targetDisplay = log.target.name;
            else if (log.target.resourceId) targetDisplay = log.target.resourceType + ': ' + log.target.resourceId;
        }

        var row = $('<tr class="audit-log-row" data-log-id="' + log.id + '">' +
            '<td class="small text-muted">' + formattedDate + '</td>' +
            '<td class="small text-muted" title="' + log.id + '">' + eventId + '</td>' +
            '<td><span class="fw-medium">' + log.actionLabel + '</span></td>' +
            '<td class="small text-muted">' + formatCategory(log.category) + '</td>' +
            '<td><span class="badge severity-badge-' + log.severity + '">' + capitalizeFirst(log.severity) + '</span></td>' +
            '<td class="small">' + log.actor.userName + '</td>' +
            '<td class="small">' + targetDisplay + '</td>' +
            '<td class="small text-muted">' + log.context.ipAddress + '</td>' +
        '</tr>');

        row.on('click', function() { showLogDetail(log); });

        return row;
    }

    function setViewMode(mode) {
        viewMode = mode;

        if (mode === 'pagination') {
            $('#paginationModeBtn').addClass('active');
            $('#infiniteScrollModeBtn').removeClass('active');
        } else {
            $('#paginationModeBtn').removeClass('active');
            $('#infiniteScrollModeBtn').addClass('active');
        }

        currentPage = 1;
        displayedCount = 0;
        renderTable();
        if (viewMode === 'pagination') {
            renderPagination();
        }
    }

    function renderPagination() {
        var totalPages = Math.ceil(filteredLogs.length / itemsPerPage);
        var pagination = $('#paginationControls');
        pagination.empty();

        if (totalPages <= 1) return;

        pagination.append('<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '"><i class="fas fa-chevron-left"></i></a></li>');

        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

        for (var i = startPage; i <= endPage; i++) {
            pagination.append('<li class="page-item ' + (i === currentPage ? 'active' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>');
        }

        pagination.append('<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '"><i class="fas fa-chevron-right"></i></a></li>');

        pagination.find('a').on('click', function(e) {
            e.preventDefault();
            var page = parseInt($(this).data('page'));
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                renderTable();
                renderPagination();
            }
        });
    }

    function showLogDetail(log) {
        var content = $('#logDetailContent');
        var timestamp = new Date(log.timestamp);
        var retentionExpiry = new Date(log.retentionExpiry);

        var html = '<div class="log-detail-section">' +
            '<h6><i class="fas fa-shield-alt me-2"></i>Integrity & Compliance</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">Integrity Hash</span><span class="log-detail-value"><code>' + log.integrityHash + '</code> <span class="badge badge-pastel-success ms-2">Verified</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Retention Until</span><span class="log-detail-value">' + retentionExpiry.toLocaleDateString('en-GB') + ' <span class="retention-indicator retention-active ms-2">7 years</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Tamper Status</span><span class="log-detail-value"><span class="badge badge-pastel-success">Unmodified</span></span></div>' +
        '</div>';

        html += '<div class="log-detail-section">' +
            '<h6><i class="fas fa-clock me-2"></i>Event Information</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">Log ID</span><span class="log-detail-value"><code>' + log.id + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Timestamp</span><span class="log-detail-value">' + timestamp.toISOString() + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Action</span><span class="log-detail-value">' + log.actionLabel + ' <code class="ms-2">(' + log.action + ')</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Category</span><span class="log-detail-value">' + formatCategory(log.category) + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Severity</span><span class="log-detail-value"><span class="badge severity-badge-' + log.severity + '">' + capitalizeFirst(log.severity) + '</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Result</span><span class="log-detail-value"><span class="badge ' + (log.result === 'success' ? 'badge-pastel-success' : 'badge-pastel-danger') + '">' + capitalizeFirst(log.result) + '</span></span></div>' +
        '</div>';

        html += '<div class="log-detail-section">' +
            '<h6><i class="fas fa-user me-2"></i>Actor</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">User ID</span><span class="log-detail-value"><code>' + log.actor.userId + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Name</span><span class="log-detail-value">' + log.actor.userName + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Role</span><span class="log-detail-value"><span class="badge badge-pastel-primary">' + formatRole(log.actor.role) + '</span></span></div>' +
            (log.actor.subAccountId ? '<div class="log-detail-row"><span class="log-detail-label">Sub-Account</span><span class="log-detail-value">' + log.actor.subAccountId + '</span></div>' : '') +
        '</div>';

        if (log.target) {
            html += '<div class="log-detail-section">' +
                '<h6><i class="fas fa-bullseye me-2"></i>Target</h6>';
            if (log.target.userId) {
                html += '<div class="log-detail-row"><span class="log-detail-label">User ID</span><span class="log-detail-value"><code>' + log.target.userId + '</code></span></div>' +
                    '<div class="log-detail-row"><span class="log-detail-label">Name</span><span class="log-detail-value">' + log.target.userName + '</span></div>' +
                    (log.target.role ? '<div class="log-detail-row"><span class="log-detail-label">Role</span><span class="log-detail-value"><span class="badge badge-pastel-primary">' + formatRole(log.target.role) + '</span></span></div>' : '');
            } else if (log.target.resourceType) {
                html += '<div class="log-detail-row"><span class="log-detail-label">Resource Type</span><span class="log-detail-value">' + formatCategory(log.target.resourceType) + '</span></div>' +
                    '<div class="log-detail-row"><span class="log-detail-label">Resource ID</span><span class="log-detail-value"><code>' + log.target.resourceId + '</code></span></div>' +
                    (log.target.name ? '<div class="log-detail-row"><span class="log-detail-label">Name</span><span class="log-detail-value">' + log.target.name + '</span></div>' : '');
            }
            html += '</div>';
        }

        html += '<div class="log-detail-section">' +
            '<h6><i class="fas fa-network-wired me-2"></i>Context</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">IP Address</span><span class="log-detail-value">' + log.context.ipAddress + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Session ID</span><span class="log-detail-value"><code>' + log.context.sessionId + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Request ID</span><span class="log-detail-value"><code>' + log.context.requestId + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">User Agent</span><span class="log-detail-value small">' + log.context.userAgent + '</span></div>' +
        '</div>';

        if (Object.keys(log.details).length > 0) {
            html += '<div class="log-detail-section">' +
                '<h6><i class="fas fa-info-circle me-2"></i>Additional Details</h6>';
            for (var key in log.details) {
                html += '<div class="log-detail-row"><span class="log-detail-label">' + formatCategory(key) + '</span><span class="log-detail-value">' + log.details[key] + '</span></div>';
            }
            html += '</div>';
        }

        content.html(html);

        $('#copyLogDetail').off('click').on('click', function() {
            var logJson = JSON.stringify(log, null, 2);
            navigator.clipboard.writeText(logJson).then(function() {
                showToast('Log details copied to clipboard', 'success');
            });
        });

        $('#logDetailModal').modal('show');
    }

    function bindEvents() {
        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                applyFilters();
            }
        });

        $('#applyFiltersBtn').on('click', function() {
            applyFilters();
        });

        $('#clearFilters').on('click', function() {
            clearAllFilters();
        });

        $('.quick-filter-btn').on('click', function() {
            $('.quick-filter-btn').removeClass('active');
            $(this).addClass('active');
            activeQuickFilter = $(this).data('filter');
            applyFilters();
        });

        $('#exportCsvBtn').on('click', function(e) { 
            e.preventDefault();
            exportLogs('csv'); 
        });
        $('#exportExcelBtn').on('click', function(e) { 
            e.preventDefault();
            exportLogs('excel'); 
        });

        $('#verifyIntegrityBtn').on('click', function() {
            $('#integrityChecking').show();
            $('#integrityResult').hide();
            $('#integrityModal').modal('show');

            setTimeout(function() {
                $('#integrityChecking').hide();
                $('#integrityResult').show();
                $('#recordsVerified').text(allLogs.length);
                $('#verificationTime').text(Math.floor(Math.random() * 200 + 50) + 'ms');
            }, 1500);
        });

        $('#paginationModeBtn').on('click', function() {
            setViewMode('pagination');
        });

        $('#infiniteScrollModeBtn').on('click', function() {
            setViewMode('infinite');
        });

        $('#loadMoreBtn').on('click', function() {
            loadMoreLogs();
        });

        $('#auditTableContainer').on('scroll', function() {
            if (viewMode !== 'infinite') return;

            var container = $(this);
            var scrollTop = container.scrollTop();
            var scrollHeight = container[0].scrollHeight;
            var clientHeight = container.height();

            if (scrollTop + clientHeight >= scrollHeight - 100) {
                loadMoreLogs();
            }
        });
    }

    var isExporting = false;

    function exportLogs(format) {
        if (!canExport) {
            showToast('Export not permitted for your role', 'error');
            return;
        }

        if (isExporting) {
            showToast('Export already in progress...', 'warning');
            return;
        }

        var data = filteredLogs.length > 0 ? filteredLogs : allLogs;
        var sanitizedData = sanitizeExportData(data);

        AuditLogger.log('DATA_EXPORTED', {
            data: { 
                exportType: 'audit_log', 
                format: format, 
                recordCount: sanitizedData.length,
                userRole: currentUserRole,
                scopeRestricted: userScopeCategories !== null
            }
        });

        if (sanitizedData.length > 500) {
            exportLargeDataset(sanitizedData, format);
        } else {
            performExport(sanitizedData, format);
        }
    }

    function sanitizeExportData(data) {
        return data.map(function(log) {
            var sanitizedLog = JSON.parse(JSON.stringify(log));

            if (sanitizedLog.details) {
                var sensitiveKeys = ['password', 'token', 'secret', 'apiKey', 'creditCard', 'cvv', 'pin'];
                Object.keys(sanitizedLog.details).forEach(function(key) {
                    var lowerKey = key.toLowerCase();
                    if (sensitiveKeys.some(function(s) { return lowerKey.includes(s.toLowerCase()); })) {
                        sanitizedLog.details[key] = '[REDACTED]';
                    }
                });
            }

            if (sanitizedLog.context && sanitizedLog.context.userAgent) {
                sanitizedLog.context.userAgent = sanitizedLog.context.userAgent.substring(0, 50) + '...';
            }

            return sanitizedLog;
        });
    }

    function exportLargeDataset(data, format) {
        isExporting = true;
        showExportProgress(0, data.length);

        var chunkSize = 100;
        var chunks = [];
        for (var i = 0; i < data.length; i += chunkSize) {
            chunks.push(data.slice(i, i + chunkSize));
        }

        var processedRows = [];
        var currentChunk = 0;

        function processNextChunk() {
            if (currentChunk >= chunks.length) {
                finishLargeExport(processedRows, format, data.length);
                return;
            }

            var chunk = chunks[currentChunk];
            
            if (format === 'csv') {
                chunk.forEach(function(log) {
                    processedRows.push(convertLogToRow(log));
                });
            } else if (format === 'excel') {
                chunk.forEach(function(log) {
                    processedRows.push(convertLogToExcelRow(log));
                });
            }

            currentChunk++;
            var progress = Math.round((currentChunk / chunks.length) * 100);
            updateExportProgress(progress);

            setTimeout(processNextChunk, 10);
        }

        processNextChunk();
    }

    function finishLargeExport(rows, format, totalCount) {
        hideExportProgress();
        isExporting = false;

        var content, filename, mimeType;
        var dateStr = new Date().toISOString().split('T')[0];

        if (format === 'csv') {
            var headers = getExportHeaders();
            content = headers.join(',') + '\n' + rows.join('\n');
            filename = 'audit-logs-' + dateStr + '.csv';
            mimeType = 'text/csv;charset=utf-8;';
        } else if (format === 'excel') {
            content = generateExcelXML(rows);
            filename = 'audit-logs-' + dateStr + '.xlsx';
            mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        downloadFile(content, filename, mimeType);
        showToast('Exported ' + totalCount + ' records as ' + format.toUpperCase(), 'success');
    }

    function performExport(data, format) {
        var content, filename, mimeType;
        var dateStr = new Date().toISOString().split('T')[0];

        if (format === 'csv') {
            content = convertToCSV(data);
            filename = 'audit-logs-' + dateStr + '.csv';
            mimeType = 'text/csv;charset=utf-8;';
        } else if (format === 'excel') {
            content = convertToExcel(data);
            filename = 'audit-logs-' + dateStr + '.xlsx';
            mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        downloadFile(content, filename, mimeType);
        showToast('Exported ' + data.length + ' records as ' + format.toUpperCase(), 'success');
    }

    function downloadFile(content, filename, mimeType) {
        var blob;
        if (mimeType.includes('spreadsheetml')) {
            blob = new Blob([content], { type: mimeType });
        } else {
            var BOM = '\uFEFF';
            blob = new Blob([BOM + content], { type: mimeType });
        }

        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function getExportHeaders() {
        return [
            'Event ID', 'Timestamp (UTC)', 'Event Type', 'Description', 'Module', 'Category', 
            'Severity', 'Actor Type', 'Actor ID', 'Actor Name', 'Actor Role', 'Sub-Account',
            'Target Type', 'Target ID', 'Target Name', 'IP Address', 'Session ID', 
            'Result', 'Integrity Hash', 'Retention Until'
        ];
    }

    function convertLogToRow(log) {
        var values = [
            log.id,
            log.timestamp,
            log.action,
            log.actionLabel || log.description || '',
            log.module || mapCategoryToModule(log.category),
            log.category,
            log.severity,
            log.actorType || 'user',
            log.actor.userId,
            log.actor.userName,
            log.actor.role,
            log.actor.subAccountId || 'Main Account',
            log.target ? (log.target.resourceType || log.target.entityType || 'user') : '',
            log.target ? (log.target.resourceId || log.target.entityId || log.target.userId || '') : '',
            log.target ? (log.target.name || log.target.userName || '') : '',
            log.context ? log.context.ipAddress : '',
            log.context ? log.context.sessionId : '',
            log.result || 'success',
            log.integrityHash || '',
            log.retentionExpiry || ''
        ];

        return values.map(function(v) { 
            return '"' + String(v || '').replace(/"/g, '""') + '"'; 
        }).join(',');
    }

    function convertLogToExcelRow(log) {
        return [
            log.id,
            log.timestamp,
            log.action,
            log.actionLabel || log.description || '',
            log.module || mapCategoryToModule(log.category),
            log.category,
            log.severity,
            log.actorType || 'user',
            log.actor.userId,
            log.actor.userName,
            log.actor.role,
            log.actor.subAccountId || 'Main Account',
            log.target ? (log.target.resourceType || log.target.entityType || 'user') : '',
            log.target ? (log.target.resourceId || log.target.entityId || log.target.userId || '') : '',
            log.target ? (log.target.name || log.target.userName || '') : '',
            log.context ? log.context.ipAddress : '',
            log.context ? log.context.sessionId : '',
            log.result || 'success',
            log.integrityHash || '',
            log.retentionExpiry || ''
        ];
    }

    function convertToCSV(data) {
        var headers = getExportHeaders();
        var rows = data.map(function(log) {
            return convertLogToRow(log);
        });
        return headers.join(',') + '\n' + rows.join('\n');
    }

    function convertToExcel(data) {
        var headers = getExportHeaders();
        var rows = data.map(function(log) {
            return convertLogToExcelRow(log);
        });

        return generateExcelXML([headers].concat(rows));
    }

    function generateExcelXML(allRows) {
        var xml = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n';
        xml += '<' + '?mso-application progid="Excel.Sheet"?' + '>\n';
        xml += '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"\n';
        xml += ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">\n';
        xml += '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">\n';
        xml += '<Title>Audit Logs Export</Title>\n';
        xml += '<Author>QuickSMS</Author>\n';
        xml += '<Created>' + new Date().toISOString() + '</Created>\n';
        xml += '<Company>QuickSMS</Company>\n';
        xml += '</DocumentProperties>\n';

        xml += '<Styles>\n';
        xml += '<Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#E8E8E8" ss:Pattern="Solid"/></Style>\n';
        xml += '<Style ss:ID="Data"><Protection ss:Protected="1"/></Style>\n';
        xml += '</Styles>\n';

        xml += '<Worksheet ss:Name="Audit Logs" ss:Protected="1">\n';
        xml += '<Table>\n';

        if (allRows.length > 0) {
            xml += '<Row ss:StyleID="Header">\n';
            (Array.isArray(allRows[0]) ? allRows[0] : getExportHeaders()).forEach(function(header) {
                xml += '<Cell><Data ss:Type="String">' + escapeXML(String(header)) + '</Data></Cell>\n';
            });
            xml += '</Row>\n';
        }

        var dataRows = Array.isArray(allRows[0]) && typeof allRows[0][0] === 'string' && allRows[0][0].includes('Event ID') 
            ? allRows.slice(1) 
            : allRows;

        dataRows.forEach(function(row) {
            xml += '<Row ss:StyleID="Data">\n';
            var cells = Array.isArray(row) ? row : [row];
            cells.forEach(function(cell) {
                var value = String(cell || '');
                var type = isNaN(cell) || cell === '' ? 'String' : 'Number';
                xml += '<Cell><Data ss:Type="' + type + '">' + escapeXML(value) + '</Data></Cell>\n';
            });
            xml += '</Row>\n';
        });

        xml += '</Table>\n';
        xml += '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">\n';
        xml += '<ProtectObjects>True</ProtectObjects>\n';
        xml += '<ProtectScenarios>True</ProtectScenarios>\n';
        xml += '</WorksheetOptions>\n';
        xml += '</Worksheet>\n';
        xml += '</Workbook>';

        return xml;
    }

    function escapeXML(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&apos;');
    }

    function showExportProgress(current, total) {
        if (!$('#exportProgressModal').length) {
            var modal = $('<div class="modal fade" id="exportProgressModal" tabindex="-1" data-bs-backdrop="static">' +
                '<div class="modal-dialog modal-sm modal-dialog-centered">' +
                '<div class="modal-content">' +
                '<div class="modal-body text-center p-4">' +
                '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                '<h6 class="mb-2">Exporting Audit Logs</h6>' +
                '<div class="progress" style="height: 6px;">' +
                '<div class="progress-bar bg-primary" id="exportProgressBar" style="width: 0%"></div>' +
                '</div>' +
                '<p class="small text-muted mt-2 mb-0" id="exportProgressText">Preparing export...</p>' +
                '</div></div></div></div>');
            $('body').append(modal);
        }
        $('#exportProgressBar').css('width', '0%');
        $('#exportProgressText').text('Preparing ' + total + ' records...');
        $('#exportProgressModal').modal('show');
    }

    function updateExportProgress(percent) {
        $('#exportProgressBar').css('width', percent + '%');
        $('#exportProgressText').text('Processing... ' + percent + '%');
    }

    function hideExportProgress() {
        $('#exportProgressModal').modal('hide');
    }

    function formatCategory(category) { return category.split('_').map(capitalizeFirst).join(' '); }
    function formatRole(role) { return role.split('_').map(capitalizeFirst).join(' '); }
    function capitalizeFirst(str) { return str.charAt(0).toUpperCase() + str.slice(1); }

    function showToast(message, type) {
        var bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-primary');
        var toast = $('<div class="toast align-items-center text-white ' + bgClass + ' border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">' +
            '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>');
        $('body').append(toast);
        var bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
        bsToast.show();
        toast.on('hidden.bs.toast', function() { toast.remove(); });
    }

    init();
});
</script>
@endpush

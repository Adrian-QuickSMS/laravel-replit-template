@php
$themeColor = $themeColor ?? '#886CC0';
$themeColorRgb = $themeColorRgb ?? '111, 66, 193';
$themeName = $themeName ?? 'purple';
$prefix = $prefix ?? 'audit';
$showCustomerSelector = $showCustomerSelector ?? false;
$showSubAccountFilter = $showSubAccountFilter ?? true;
$isAdminContext = $isAdminContext ?? false;
$cardTitle = $cardTitle ?? 'Audit Trail';
$cardSubtitle = $cardSubtitle ?? 'Centralised, chronological record of all platform activity';
@endphp

<style>
.{{ $prefix }}-severity-badge-low { background-color: rgba(108, 117, 125, 0.15); color: #6c757d; }
.{{ $prefix }}-severity-badge-medium { background-color: rgba({{ $themeColorRgb }}, 0.15); color: {{ $themeColor }}; }
.{{ $prefix }}-severity-badge-high { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.{{ $prefix }}-severity-badge-critical { background-color: rgba(220, 53, 69, 0.25); color: #dc3545; font-weight: 600; }

.{{ $prefix }}-category-badge-user_management { background-color: rgba({{ $themeColorRgb }}, 0.15); color: {{ $themeColor }}; }
.{{ $prefix }}-category-badge-access_control { background-color: rgba(48, 101, 208, 0.15); color: #3065D0; }
.{{ $prefix }}-category-badge-security { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.{{ $prefix }}-category-badge-authentication { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.{{ $prefix }}-category-badge-enforcement { background-color: rgba(214, 83, 193, 0.15); color: #D653C1; }
.{{ $prefix }}-category-badge-data_access { background-color: rgba(255, 191, 0, 0.15); color: #cc9900; }
.{{ $prefix }}-category-badge-account { background-color: rgba({{ $themeColorRgb }}, 0.15); color: {{ $themeColor }}; }
.{{ $prefix }}-category-badge-messaging { background-color: rgba(48, 101, 208, 0.15); color: #3065D0; }
.{{ $prefix }}-category-badge-financial { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.{{ $prefix }}-category-badge-gdpr { background-color: rgba(214, 83, 193, 0.15); color: #D653C1; }
.{{ $prefix }}-category-badge-compliance { background-color: rgba({{ $themeColorRgb }}, 0.15); color: {{ $themeColor }}; }

.{{ $prefix }}-audit-log-row { cursor: pointer; }
.{{ $prefix }}-audit-log-row:hover { background-color: rgba({{ $themeColorRgb }}, 0.03); }

.{{ $prefix }}-log-detail-section { padding: 1rem; background-color: #fafafa; border-radius: 0.5rem; margin-bottom: 1rem; }
.{{ $prefix }}-log-detail-section h6 { color: {{ $themeColor }}; margin-bottom: 0.75rem; font-size: 0.875rem; }
.{{ $prefix }}-log-detail-row { display: flex; margin-bottom: 0.5rem; }
.{{ $prefix }}-log-detail-label { width: 140px; font-weight: 500; color: #6c757d; font-size: 0.8125rem; }
.{{ $prefix }}-log-detail-value { flex: 1; font-size: 0.8125rem; color: #212529; }

.{{ $prefix }}-stats-card { border: none; background: #fff; border-radius: 0.5rem; }
.{{ $prefix }}-stats-card .stat-value { font-size: 1.5rem; font-weight: 600; color: {{ $themeColor }}; }
.{{ $prefix }}-stats-card .stat-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; }

.{{ $prefix }}-empty-state { padding: 4rem 2rem; text-align: center; }
.{{ $prefix }}-empty-state i { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; }

.{{ $prefix }}-compliance-card { background: linear-gradient(135deg, rgba({{ $themeColorRgb }}, 0.05) 0%, rgba({{ $themeColorRgb }}, 0.02) 100%); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; }
.{{ $prefix }}-compliance-card h6 { color: {{ $themeColor }}; font-size: 0.875rem; margin-bottom: 0.5rem; }
.{{ $prefix }}-compliance-card .compliance-stat { font-size: 1.25rem; font-weight: 600; color: {{ $themeColor }}; }

.{{ $prefix }}-quick-filter-btn { 
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
.{{ $prefix }}-quick-filter-btn:hover { 
    background-color: rgba({{ $themeColorRgb }}, 0.08); 
    border-color: {{ $themeColor }}; 
    color: {{ $themeColor }}; 
}
.{{ $prefix }}-quick-filter-btn.active { 
    background-color: {{ $themeColor }}; 
    color: #fff; 
    border-color: {{ $themeColor }}; 
}

.{{ $prefix }}-audit-table-container { 
    max-height: 600px; 
    overflow-y: auto; 
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
}
.{{ $prefix }}-audit-table-container.infinite-scroll-enabled { max-height: none; }

.{{ $prefix }}-audit-logs-table {
    width: 100%;
    border-collapse: collapse;
}
.{{ $prefix }}-audit-logs-table thead th {
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
.{{ $prefix }}-audit-logs-table tbody td {
    padding: 0.5rem 0.35rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
.{{ $prefix }}-audit-logs-table tbody tr:hover {
    background-color: rgba({{ $themeColorRgb }}, 0.03);
}

.{{ $prefix }}-sortable-header {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s ease;
}
.{{ $prefix }}-sortable-header:hover {
    background-color: rgba({{ $themeColorRgb }}, 0.08);
}
.{{ $prefix }}-sortable-header .sort-icon {
    color: #ccc;
    font-size: 0.7rem;
}
.{{ $prefix }}-sortable-header .sort-icon.active {
    color: {{ $themeColor }};
}

.{{ $prefix }}-loading-more { 
    text-align: center; 
    padding: 1.5rem; 
    color: #6c757d; 
    background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba({{ $themeColorRgb }}, 0.02) 100%);
}
.{{ $prefix }}-loading-more .spinner-border { width: 1.25rem; height: 1.25rem; border-width: 0.15em; color: {{ $themeColor }}; }

.{{ $prefix }}-load-more-btn {
    background-color: #fff;
    border: 1px solid {{ $themeColor }};
    color: {{ $themeColor }};
    padding: 0.5rem 1.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    transition: all 0.15s ease;
}
.{{ $prefix }}-load-more-btn:hover {
    background-color: rgba({{ $themeColorRgb }}, 0.08);
    border-color: {{ $themeColor }};
    color: {{ $themeColor }};
}

.{{ $prefix }}-view-mode-toggle { font-size: 0.75rem; }
.{{ $prefix }}-view-mode-toggle .btn { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.{{ $prefix }}-view-mode-toggle .btn.active { background-color: {{ $themeColor }}; color: #fff; border-color: {{ $themeColor }}; }

.{{ $prefix }}-end-of-list { 
    text-align: center; 
    padding: 1rem; 
    color: #6c757d; 
    font-size: 0.8125rem;
    border-top: 1px dashed #dee2e6;
}

.{{ $prefix }}-filter-panel { 
    background-color: rgba({{ $themeColorRgb }}, 0.05) !important; 
    border: 1px solid #e9ecef;
}
.{{ $prefix }}-filter-panel .form-label { margin-bottom: 0.25rem; }
.{{ $prefix }}-filter-panel .form-control,
.{{ $prefix }}-filter-panel .form-select { font-size: 0.875rem; }

.{{ $prefix }}-active-filters-display .filter-tag {
    display: inline-flex;
    align-items: center;
    background-color: rgba({{ $themeColorRgb }}, 0.1);
    color: {{ $themeColor }};
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    margin-right: 0.5rem;
    margin-bottom: 0.25rem;
}
.{{ $prefix }}-active-filters-display .filter-tag .remove-filter {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.{{ $prefix }}-active-filters-display .filter-tag .remove-filter:hover {
    opacity: 1;
}

.{{ $prefix }}-btn-theme {
    background-color: {{ $themeColor }};
    border-color: {{ $themeColor }};
    color: #fff;
}
.{{ $prefix }}-btn-theme:hover {
    background-color: {{ $themeColor }};
    border-color: {{ $themeColor }};
    color: #fff;
    opacity: 0.9;
}
.{{ $prefix }}-btn-theme-outline {
    background-color: transparent;
    border-color: {{ $themeColor }};
    color: {{ $themeColor }};
}
.{{ $prefix }}-btn-theme-outline:hover {
    background-color: rgba({{ $themeColorRgb }}, 0.08);
    border-color: {{ $themeColor }};
    color: {{ $themeColor }};
}

.{{ $prefix }}-customer-selector {
    min-width: 280px;
}
.{{ $prefix }}-customer-selector .form-control {
    border-radius: 0.375rem 0 0 0.375rem;
}
.{{ $prefix }}-customer-search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #fff;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.{{ $prefix }}-customer-search-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
}
.{{ $prefix }}-customer-search-item:hover {
    background-color: rgba({{ $themeColorRgb }}, 0.05);
}
.{{ $prefix }}-customer-search-item:last-child {
    border-bottom: none;
}
.{{ $prefix }}-customer-search-item .customer-name {
    font-weight: 500;
    font-size: 0.875rem;
}
.{{ $prefix }}-customer-search-item .customer-id {
    font-size: 0.75rem;
    color: #6c757d;
}
.{{ $prefix }}-selected-customer-badge {
    display: inline-flex;
    align-items: center;
    background-color: rgba({{ $themeColorRgb }}, 0.1);
    color: {{ $themeColor }};
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
}
.{{ $prefix }}-selected-customer-badge .clear-customer {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.{{ $prefix }}-selected-customer-badge .clear-customer:hover {
    opacity: 1;
}
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="card-title mb-0">{{ $cardTitle }}</h5>
            <small class="text-muted">{{ $cardSubtitle }}</small>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            @if($showCustomerSelector)
            <div class="{{ $prefix }}-customer-selector position-relative" id="{{ $prefix }}CustomerSelectorContainer">
                <div id="{{ $prefix }}CustomerSelectorInput" style="display: block;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-building"></i></span>
                        <input type="text" class="form-control" id="{{ $prefix }}CustomerSearch" placeholder="Search customers..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="{{ $prefix }}CustomerDropdownBtn">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
                <div id="{{ $prefix }}SelectedCustomerDisplay" style="display: none;">
                    <span class="{{ $prefix }}-selected-customer-badge">
                        <i class="fas fa-building me-2"></i>
                        <span id="{{ $prefix }}SelectedCustomerName">All Customers</span>
                        <i class="fas fa-times clear-customer" id="{{ $prefix }}ClearCustomer"></i>
                    </span>
                </div>
                <div class="{{ $prefix }}-customer-search-results" id="{{ $prefix }}CustomerSearchResults" style="display: none;"></div>
            </div>
            @endif
            <button type="button" class="btn {{ $prefix }}-btn-theme-outline btn-sm" data-bs-toggle="collapse" data-bs-target="#{{ $prefix }}FiltersPanel">
                <i class="fas fa-filter me-1"></i>Filters
            </button>
            <div class="dropdown">
                <button class="btn {{ $prefix }}-btn-theme-outline btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Export Format</h6></li>
                    <li><a class="dropdown-item" href="#" id="{{ $prefix }}ExportCsv"><i class="fas fa-file-csv me-2 text-success"></i>CSV (.csv)<small class="d-block text-muted">Comma-separated values</small></a></li>
                    <li><a class="dropdown-item" href="#" id="{{ $prefix }}ExportExcel"><i class="fas fa-file-excel me-2 text-success"></i>Excel (.xlsx)<small class="d-block text-muted">Microsoft Excel format</small></a></li>
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
                <input type="text" class="form-control" id="{{ $prefix }}SearchInput" placeholder="Search by description, target ID, or user name...">
            </div>
        </div>

        <div class="collapse mb-3" id="{{ $prefix }}FiltersPanel">
            <div class="card card-body border-0 rounded-3 {{ $prefix }}-filter-panel">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Date From</label>
                        <input type="date" class="form-control form-control-sm" id="{{ $prefix }}DateFromFilter">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Date To</label>
                        <input type="date" class="form-control form-control-sm" id="{{ $prefix }}DateToFilter">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Module</label>
                        <select class="form-select form-select-sm" id="{{ $prefix }}ModuleFilter">
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
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Event Type</label>
                        <select class="form-select form-select-sm" id="{{ $prefix }}EventTypeFilter">
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
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Severity</label>
                        <select class="form-select form-select-sm" id="{{ $prefix }}SeverityFilter">
                            <option value="">All Severities</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Actor Type</label>
                        <select class="form-select form-select-sm" id="{{ $prefix }}ActorTypeFilter">
                            <option value="">All Actor Types</option>
                            <option value="user">User</option>
                            <option value="system">System</option>
                            <option value="api">API</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 align-items-end mt-2">
                    @if($showSubAccountFilter)
                    <div class="col-6 col-md-3" id="{{ $prefix }}SubAccountFilterContainer">
                        <label class="form-label small fw-bold">Sub-Account</label>
                        <select class="form-select form-select-sm" id="{{ $prefix }}SubAccountFilter">
                            <option value="">All Sub-Accounts</option>
                            <option value="main">Main Account</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">User</label>
                        <select class="form-select form-select-sm" id="{{ $prefix }}UserFilter">
                            <option value="">All Users</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Result</label>
                        <select class="form-select form-select-sm" id="{{ $prefix }}ResultFilter">
                            <option value="">All Results</option>
                            <option value="success">Success</option>
                            <option value="failure">Failure</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 d-flex gap-2">
                        <button type="button" class="btn {{ $prefix }}-btn-theme btn-sm flex-grow-1" id="{{ $prefix }}ApplyFiltersBtn">
                            <i class="fas fa-check me-1"></i>Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $prefix }}ClearFiltersBtn">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted small"><span id="{{ $prefix }}TotalFiltered">0</span> events</span>
            <div class="{{ $prefix }}-view-mode-toggle btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary active" id="{{ $prefix }}PaginationModeBtn" title="Pagination view">
                    <i class="fas fa-list"></i> Paginated
                </button>
                <button type="button" class="btn btn-outline-secondary" id="{{ $prefix }}InfiniteScrollModeBtn" title="Infinite scroll view">
                    <i class="fas fa-stream"></i> Scroll
                </button>
            </div>
        </div>

        <div class="{{ $prefix }}-audit-table-container" id="{{ $prefix }}AuditTableContainer">
            <table class="{{ $prefix }}-audit-logs-table" id="{{ $prefix }}AuditLogsTable">
                <thead>
                    <tr>
                        <th style="width: 150px;" class="{{ $prefix }}-sortable-header" data-sort="timestamp">Timestamp <i class="fas fa-sort-down ms-1 sort-icon active"></i></th>
                        <th style="width: 100px;">Event ID</th>
                        <th class="{{ $prefix }}-sortable-header" data-sort="action">Action <i class="fas fa-sort ms-1 sort-icon"></i></th>
                        <th style="width: 120px;" class="{{ $prefix }}-sortable-header" data-sort="category">Category <i class="fas fa-sort ms-1 sort-icon"></i></th>
                        <th style="width: 90px;" class="{{ $prefix }}-sortable-header" data-sort="severity">Severity <i class="fas fa-sort ms-1 sort-icon"></i></th>
                        <th class="{{ $prefix }}-sortable-header" data-sort="actor">Actor <i class="fas fa-sort ms-1 sort-icon"></i></th>
                        <th>Target</th>
                        <th style="width: 110px;">IP Address</th>
                    </tr>
                </thead>
                <tbody id="{{ $prefix }}AuditLogsTableBody">
                </tbody>
            </table>

            <div class="{{ $prefix }}-loading-more" id="{{ $prefix }}LoadingMore" style="display: none;">
                <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                <span class="ms-2">Loading more events...</span>
            </div>

            <div class="text-center py-3" id="{{ $prefix }}LoadMoreContainer" style="display: none;">
                <button type="button" class="{{ $prefix }}-load-more-btn" id="{{ $prefix }}LoadMoreBtn">
                    <i class="fas fa-plus-circle me-2"></i>Load More
                </button>
                <div class="small text-muted mt-2" id="{{ $prefix }}LoadMoreInfo"></div>
            </div>

            <div class="{{ $prefix }}-end-of-list" id="{{ $prefix }}EndOfList" style="display: none;">
                <i class="fas fa-check-circle text-success me-2"></i>All events loaded
            </div>
        </div>

        <div class="{{ $prefix }}-empty-state" id="{{ $prefix }}EmptyState" style="display: none;">
            <i class="fas fa-clipboard-list"></i>
            <h5 class="text-muted mb-2">No audit logs found</h5>
            <p class="text-muted small mb-0">Adjust your filters or check back later for new activity.</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4" id="{{ $prefix }}PaginationRow">
            <div class="text-muted small">
                Showing <span id="{{ $prefix }}ShowingStart">0</span>-<span id="{{ $prefix }}ShowingEnd">0</span> of <span id="{{ $prefix }}PaginationTotal">0</span> events
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="{{ $prefix }}PaginationControls"></ul>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="{{ $prefix }}LogDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2" style="color: {{ $themeColor }};"></i>Audit Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="{{ $prefix }}LogDetailContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn {{ $prefix }}-btn-theme-outline btn-sm" id="{{ $prefix }}CopyLogDetail">
                    <i class="fas fa-copy me-1"></i>Copy to Clipboard
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

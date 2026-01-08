@extends('layouts.quicksms')

@section('title', 'API Connections')

@push('styles')
<style>
.api-connections-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.api-connections-header h2 {
    margin: 0;
    font-weight: 600;
}
.api-connections-header p {
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
.api-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}
.api-table {
    width: 100%;
    margin: 0;
    min-width: 1400px;
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
.api-table thead th:first-child { width: 14%; }
.api-table thead th:nth-child(2) { width: 10%; }
.api-table thead th:nth-child(3) { width: 9%; }
.api-table thead th:nth-child(4) { width: 7%; }
.api-table thead th:nth-child(5) { width: 8%; }
.api-table thead th:nth-child(6) { width: 7%; }
.api-table thead th:nth-child(7) { width: 14%; }
.api-table thead th:nth-child(8) { width: 7%; }
.api-table thead th:nth-child(9) { width: 9%; }
.api-table thead th:nth-child(10) { width: 10%; }
.api-table thead th:last-child { 
    width: 5%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.api-table thead th:hover {
    background: #e9ecef;
}
.api-table thead th:last-child:hover {
    background: #f8f9fa;
}
.api-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.api-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.api-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
}
.api-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.api-table tbody tr:last-child td {
    border-bottom: none;
}
.api-table tbody tr:hover td {
    background: #f8f9fa;
}
.api-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.api-name {
    font-weight: 500;
    color: #343a40;
}
.api-description {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.15rem;
}
.badge-bulk {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-campaign {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-integration {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-test {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-live-env {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-live-status {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-suspended {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-on {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-off {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
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
.action-menu-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}
.action-menu-btn:hover {
    color: var(--primary);
}
.archived-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}
.archived-row:hover {
    opacity: 0.8;
}
.copy-btn {
    background: transparent;
    border: none;
    padding: 0.15rem 0.35rem;
    cursor: pointer;
    color: #6c757d;
    font-size: 0.75rem;
}
.copy-btn:hover {
    color: var(--primary);
}
.base-url-cell {
    font-family: monospace;
    font-size: 0.75rem;
    color: #495057;
}
.table-footer {
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.multiselect-dropdown {
    position: relative;
}
.multiselect-dropdown .dropdown-menu {
    max-height: 200px;
    overflow-y: auto;
    min-width: 100%;
}
.multiselect-dropdown .form-check {
    padding: 0.5rem 1rem 0.5rem 2.5rem;
}
.multiselect-dropdown .form-check:hover {
    background: #f8f9fa;
}
.multiselect-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left;
    background: #fff;
}
.multiselect-toggle .selected-count {
    background: #6f42c1;
    color: #fff;
    font-size: 0.65rem;
    padding: 0.125rem 0.375rem;
    border-radius: 0.75rem;
    margin-left: 0.5rem;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background-color: rgba(136, 108, 192, 0.15);
    color: #886CC0;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    margin-right: 0.5rem;
    margin-bottom: 0.25rem;
}
.filter-chip .chip-label {
    margin-right: 0.25rem;
    color: #6c757d;
}
.filter-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
    font-size: 0.7rem;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="api-connections-header">
        <div>
            <h2>API Connections</h2>
            <p>Manage your API keys and integrations for accessing QuickSMS services.</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="createApiConnection()">
                <i class="fas fa-plus me-2"></i>Create API Connection
            </button>
        </div>
    </div>
    
    <div class="api-table-container">
        <div class="search-filter-bar">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search by API name...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>
        
        <div class="collapse" id="filtersPanel">
            <div class="card card-body border-0 rounded-0" style="background-color: #f0ebf8; border-bottom: 1px solid #e9ecef !important;">
                <div class="row g-3 align-items-end">
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
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="bulk" id="typeBulk"><label class="form-check-label small" for="typeBulk">Bulk API</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="campaign" id="typeCampaign"><label class="form-check-label small" for="typeCampaign">Campaign API</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="integration" id="typeIntegration"><label class="form-check-label small" for="typeIntegration">Integration</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-bold">Environment</label>
                        <div class="dropdown multiselect-dropdown" data-filter="environments">
                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                <span class="dropdown-label">All Environments</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="test" id="envTest"><label class="form-check-label small" for="envTest">Test</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="live" id="envLive"><label class="form-check-label small" for="envLive">Live</label></div>
                            </div>
                        </div>
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
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="live" id="statusLive"><label class="form-check-label small" for="statusLive">Live</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="suspended" id="statusSuspended"><label class="form-check-label small" for="statusSuspended">Suspended</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-bold">Sub-Account</label>
                        <div class="dropdown multiselect-dropdown" data-filter="subAccounts">
                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                <span class="dropdown-label">All Sub-Accounts</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Main Account" id="subAccMain"><label class="form-check-label small" for="subAccMain">Main Account</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Marketing" id="subAccMarketing"><label class="form-check-label small" for="subAccMarketing">Marketing</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Development" id="subAccDev"><label class="form-check-label small" for="subAccDev">Development</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-bold">Auth Type</label>
                        <div class="dropdown multiselect-dropdown" data-filter="authTypes">
                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                <span class="dropdown-label">All Auth Types</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="API Key" id="authApiKey"><label class="form-check-label small" for="authApiKey">API Key</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Basic Auth" id="authBasic"><label class="form-check-label small" for="authBasic">Basic Authentication</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="OAuth" id="authOAuth"><label class="form-check-label small" for="authOAuth">OAuth (future)</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="showArchivedToggle">
                            <label class="form-check-label small" for="showArchivedToggle">Show Archived</label>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilters">
                            <i class="fas fa-check me-1"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                            <i class="fas fa-undo me-1"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="px-3 pt-3" id="activeFiltersContainer" style="display: none;">
            <div class="d-flex flex-wrap align-items-center">
                <span class="small text-muted me-2">Active filters:</span>
                <div id="activeFiltersChips"></div>
                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" id="btnClearAllFilters">
                    Clear all
                </button>
            </div>
        </div>
        
        <table class="api-table" id="apiConnectionsTable">
            <thead>
                <tr>
                    <th data-sort="name">API Name <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="subAccount">Sub-Account <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="environment">Environment <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="authType">Auth Type <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="baseUrl">Dedicated Base URL <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="ipAllowList">IP Allow List <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="createdDate">Created Date <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="lastUsed">Last Used <i class="fas fa-sort sort-icon"></i></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="apiConnectionsBody">
            </tbody>
        </table>
        
        <div class="table-footer">
            <div class="pagination-info">
                Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> connections
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="viewDetailsDrawer" style="width: 480px;">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title text-muted mb-0"><i class="fas fa-plug me-2"></i>API Connection Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
            <h4 id="drawerApiName" class="mb-3 fw-semibold">-</h4>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span id="drawerTypeBadge" class="badge bg-white text-dark">-</span>
                <span id="drawerEnvBadge" class="badge bg-white bg-opacity-25">-</span>
                <span id="drawerStatusBadge" class="badge bg-white bg-opacity-25">-</span>
            </div>
            <div class="small opacity-75 mt-2" id="drawerDescription">-</div>
        </div>

        <div class="p-4">
            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Connection Information</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">API Name</div>
                        <div class="col-7 small fw-medium" id="drawerApiNameDetail">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Sub-Account</div>
                        <div class="col-7 small" id="drawerSubAccount">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Type</div>
                        <div class="col-7 small" id="drawerType">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Environment</div>
                        <div class="col-7 small" id="drawerEnvironment">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Status</div>
                        <div class="col-7 small" id="drawerStatus">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-key me-2"></i>Authentication</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Auth Method</div>
                        <div class="col-7 small" id="drawerAuthType">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Credentials</div>
                        <div class="col-7 small">
                            <code class="text-muted" id="drawerCredentials">••••••••••••••••</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-link me-2"></i>Endpoints</h6>
                    <div class="row mb-2">
                        <div class="col-12 text-muted small mb-1">Dedicated Base URL</div>
                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm bg-light" id="drawerBaseUrl" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyDrawerField('drawerBaseUrl')" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2 mt-3">
                        <div class="col-12 text-muted small mb-1">Delivery Report URL (Webhook)</div>
                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm bg-light" id="drawerDlrUrl" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyDrawerField('drawerDlrUrl')" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-muted small mb-1">Inbound Message URL (Webhook)</div>
                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm bg-light" id="drawerInboundUrl" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyDrawerField('drawerInboundUrl')" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-shield-alt me-2"></i>Security</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">IP Allow List</div>
                        <div class="col-7 small" id="drawerIpAllowStatus">-</div>
                    </div>
                    <div class="row" id="drawerIpListRow" style="display: none;">
                        <div class="col-12 text-muted small mb-1">Allowed IPs</div>
                        <div class="col-12">
                            <div class="bg-light rounded p-2 small" id="drawerIpList">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-clock me-2"></i>Activity</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Created</div>
                        <div class="col-7 small" id="drawerCreatedDate">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Last Used</div>
                        <div class="col-7 small" id="drawerLastUsed">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-project-diagram me-2"></i>Dependencies</h6>
                    <div id="drawerDependencies">
                        <p class="text-muted small mb-0">No dependencies configured.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmModalBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var apiConnections = [
        {
            id: 1,
            name: 'Production Bulk Sender',
            description: 'Main production API for bulk messaging',
            subAccount: 'Main Account',
            type: 'bulk',
            integrationName: null,
            environment: 'live',
            authType: 'API Key',
            status: 'live',
            baseUrl: 'https://api.quicksms.io/v1/bulk/prod-001',
            dlrUrl: 'https://yourserver.com/webhooks/dlr',
            inboundUrl: 'https://yourserver.com/webhooks/inbound',
            ipAllowList: true,
            allowedIps: ['192.168.1.100', '10.0.0.50', '203.0.113.25'],
            createdDate: '2024-08-15',
            lastUsed: '2025-01-08 14:32:45',
            archived: false,
            dependencies: [
                { type: 'Template', name: 'Welcome SMS', count: 3 },
                { type: 'Automation', name: 'New Customer Flow', count: 1 }
            ]
        },
        {
            id: 2,
            name: 'Test Bulk API',
            description: 'Testing environment for bulk operations',
            subAccount: 'Development',
            type: 'bulk',
            integrationName: null,
            environment: 'test',
            authType: 'API Key',
            status: 'live',
            baseUrl: 'https://sandbox.quicksms.io/v1/bulk/test-001',
            dlrUrl: '',
            inboundUrl: '',
            ipAllowList: false,
            allowedIps: [],
            createdDate: '2024-09-20',
            lastUsed: '2025-01-07 09:15:22',
            archived: false,
            dependencies: []
        },
        {
            id: 3,
            name: 'Campaign Manager API',
            description: 'API for campaign scheduling and management',
            subAccount: 'Marketing',
            type: 'campaign',
            integrationName: null,
            environment: 'live',
            authType: 'Basic Auth',
            status: 'live',
            baseUrl: 'https://api.quicksms.io/v1/campaigns/mkt-001',
            dlrUrl: 'https://marketing.example.com/api/dlr',
            inboundUrl: 'https://marketing.example.com/api/inbound',
            ipAllowList: true,
            allowedIps: ['10.10.10.1'],
            createdDate: '2024-06-10',
            lastUsed: '2025-01-08 11:45:18',
            archived: false,
            dependencies: [
                { type: 'Campaign', name: 'Weekly Newsletter', count: 5 }
            ]
        },
        {
            id: 4,
            name: 'Campaign Testing',
            description: null,
            subAccount: 'Development',
            type: 'campaign',
            integrationName: null,
            environment: 'test',
            authType: 'API Key',
            status: 'suspended',
            baseUrl: 'https://sandbox.quicksms.io/v1/campaigns/test-002',
            dlrUrl: '',
            inboundUrl: '',
            ipAllowList: false,
            allowedIps: [],
            createdDate: '2024-10-05',
            lastUsed: '2024-12-15 16:30:00',
            archived: false,
            dependencies: []
        },
        {
            id: 5,
            name: 'Salesforce Integration',
            description: 'CRM sync for customer messaging',
            subAccount: 'Main Account',
            type: 'integration',
            integrationName: 'Salesforce CRM',
            environment: 'live',
            authType: 'OAuth',
            status: 'live',
            baseUrl: 'https://api.quicksms.io/v1/integrations/sf-001',
            dlrUrl: '',
            inboundUrl: '',
            ipAllowList: true,
            allowedIps: ['52.88.0.0/16'],
            createdDate: '2024-03-22',
            lastUsed: '2025-01-08 15:01:33',
            archived: false,
            dependencies: [
                { type: 'Contact Sync', name: 'SF Contacts', count: 1 }
            ]
        },
        {
            id: 6,
            name: 'HubSpot Connector',
            description: 'Marketing automation integration',
            subAccount: 'Marketing',
            type: 'integration',
            integrationName: 'HubSpot',
            environment: 'test',
            authType: 'API Key',
            status: 'live',
            baseUrl: 'https://sandbox.quicksms.io/v1/integrations/hs-test',
            dlrUrl: '',
            inboundUrl: '',
            ipAllowList: false,
            allowedIps: [],
            createdDate: '2024-11-18',
            lastUsed: '2025-01-06 10:22:14',
            archived: false,
            dependencies: []
        },
        {
            id: 7,
            name: 'Legacy Bulk API',
            description: 'Deprecated - migrated to new API',
            subAccount: 'Main Account',
            type: 'bulk',
            integrationName: null,
            environment: 'live',
            authType: 'API Key',
            status: 'suspended',
            baseUrl: 'https://api.quicksms.io/v1/bulk/legacy-001',
            dlrUrl: '',
            inboundUrl: '',
            ipAllowList: true,
            allowedIps: ['192.168.1.1'],
            createdDate: '2023-05-10',
            lastUsed: '2024-06-30 08:00:00',
            archived: true,
            dependencies: []
        },
        {
            id: 8,
            name: 'Old Campaign API',
            description: 'Archived campaign connection',
            subAccount: 'Development',
            type: 'campaign',
            integrationName: null,
            environment: 'test',
            authType: 'Basic Auth',
            status: 'suspended',
            baseUrl: 'https://sandbox.quicksms.io/v1/campaigns/old-001',
            dlrUrl: '',
            inboundUrl: '',
            ipAllowList: false,
            allowedIps: [],
            createdDate: '2023-08-15',
            lastUsed: '2024-01-20 12:00:00',
            archived: true,
            dependencies: []
        }
    ];
    
    var currentSort = { column: 'name', direction: 'asc' };
    
    var appliedFilters = {
        search: '',
        types: [],
        environments: [],
        statuses: [],
        subAccounts: [],
        authTypes: [],
        showArchived: false
    };
    
    var pendingFilters = {
        search: '',
        types: [],
        environments: [],
        statuses: [],
        subAccounts: [],
        authTypes: [],
        showArchived: false
    };
    
    function getTypeBadgeClass(type) {
        switch(type) {
            case 'bulk': return 'badge-bulk';
            case 'campaign': return 'badge-campaign';
            case 'integration': return 'badge-integration';
            default: return 'badge-bulk';
        }
    }
    
    function getTypeLabel(type) {
        switch(type) {
            case 'bulk': return 'Bulk API';
            case 'campaign': return 'Campaign API';
            case 'integration': return 'Integration';
            default: return type;
        }
    }
    
    function getEnvironmentBadgeClass(env) {
        return env === 'live' ? 'badge-live-env' : 'badge-test';
    }
    
    function getStatusBadgeClass(status) {
        return status === 'live' ? 'badge-live-status' : 'badge-suspended';
    }
    
    function getIpAllowBadgeClass(enabled) {
        return enabled ? 'badge-on' : 'badge-off';
    }
    
    function formatDate(dateStr) {
        var date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    
    function formatDateTime(dateTimeStr) {
        var date = new Date(dateTimeStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + 
               ' ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            var originalHtml = $(btn).html();
            $(btn).html('<i class="fas fa-check"></i>');
            setTimeout(function() {
                $(btn).html(originalHtml);
            }, 1500);
        });
    }
    
    function updateDropdownLabel(filterName) {
        var $dropdown = $('[data-filter="' + filterName + '"]');
        var checked = $dropdown.find('input:checked');
        var $label = $dropdown.find('.dropdown-label');
        var defaultLabels = {
            'types': 'All Types',
            'environments': 'All Environments',
            'statuses': 'All Statuses',
            'subAccounts': 'All Sub-Accounts',
            'authTypes': 'All Auth Types'
        };
        
        if (checked.length === 0) {
            $label.html(defaultLabels[filterName]);
        } else if (checked.length === 1) {
            $label.html(checked.first().next('label').text());
        } else {
            $label.html(defaultLabels[filterName].replace('All ', '') + ' <span class="badge bg-primary rounded-pill ms-1">' + checked.length + '</span>');
        }
    }
    
    function getPendingFilterValues(filterName) {
        var values = [];
        $('[data-filter="' + filterName + '"] input:checked').each(function() {
            values.push($(this).val());
        });
        return values;
    }
    
    function applyFilters() {
        appliedFilters.search = $('#searchInput').val().trim();
        appliedFilters.types = getPendingFilterValues('types');
        appliedFilters.environments = getPendingFilterValues('environments');
        appliedFilters.statuses = getPendingFilterValues('statuses');
        appliedFilters.subAccounts = getPendingFilterValues('subAccounts');
        appliedFilters.authTypes = getPendingFilterValues('authTypes');
        appliedFilters.showArchived = $('#showArchivedToggle').is(':checked');
        
        renderActiveFilters();
        renderTable();
    }
    
    function resetFilters() {
        $('#searchInput').val('');
        $('#showArchivedToggle').prop('checked', false);
        
        $('.multiselect-dropdown input[type="checkbox"]').prop('checked', false);
        
        $('.multiselect-dropdown').each(function() {
            var filterName = $(this).data('filter');
            updateDropdownLabel(filterName);
        });
        
        appliedFilters = {
            search: '',
            types: [],
            environments: [],
            statuses: [],
            subAccounts: [],
            authTypes: [],
            showArchived: false
        };
        
        renderActiveFilters();
        renderTable();
    }
    
    function renderActiveFilters() {
        var $container = $('#activeFiltersContainer');
        var $chips = $('#activeFiltersChips');
        $chips.empty();
        
        var hasFilters = false;
        
        if (appliedFilters.search) {
            hasFilters = true;
            $chips.append('<span class="filter-chip"><span class="chip-label">Search:</span>' + appliedFilters.search + '<span class="remove-chip" data-filter="search"><i class="fas fa-times"></i></span></span>');
        }
        
        appliedFilters.types.forEach(function(val) {
            hasFilters = true;
            $chips.append('<span class="filter-chip"><span class="chip-label">Type:</span>' + getTypeLabel(val) + '<span class="remove-chip" data-filter="types" data-value="' + val + '"><i class="fas fa-times"></i></span></span>');
        });
        
        appliedFilters.environments.forEach(function(val) {
            hasFilters = true;
            var label = val === 'live' ? 'Live' : 'Test';
            $chips.append('<span class="filter-chip"><span class="chip-label">Environment:</span>' + label + '<span class="remove-chip" data-filter="environments" data-value="' + val + '"><i class="fas fa-times"></i></span></span>');
        });
        
        appliedFilters.statuses.forEach(function(val) {
            hasFilters = true;
            var label = val === 'live' ? 'Live' : 'Suspended';
            $chips.append('<span class="filter-chip"><span class="chip-label">Status:</span>' + label + '<span class="remove-chip" data-filter="statuses" data-value="' + val + '"><i class="fas fa-times"></i></span></span>');
        });
        
        appliedFilters.subAccounts.forEach(function(val) {
            hasFilters = true;
            $chips.append('<span class="filter-chip"><span class="chip-label">Sub-Account:</span>' + val + '<span class="remove-chip" data-filter="subAccounts" data-value="' + val + '"><i class="fas fa-times"></i></span></span>');
        });
        
        appliedFilters.authTypes.forEach(function(val) {
            hasFilters = true;
            $chips.append('<span class="filter-chip"><span class="chip-label">Auth:</span>' + val + '<span class="remove-chip" data-filter="authTypes" data-value="' + val + '"><i class="fas fa-times"></i></span></span>');
        });
        
        if (appliedFilters.showArchived) {
            hasFilters = true;
            $chips.append('<span class="filter-chip">Show Archived<span class="remove-chip" data-filter="showArchived"><i class="fas fa-times"></i></span></span>');
        }
        
        $container.toggle(hasFilters);
    }
    
    function renderTable() {
        var filtered = apiConnections.filter(function(conn) {
            if (!appliedFilters.showArchived && conn.archived) return false;
            
            if (appliedFilters.search) {
                var search = appliedFilters.search.toLowerCase();
                if (!conn.name.toLowerCase().includes(search)) return false;
            }
            
            if (appliedFilters.types.length > 0 && !appliedFilters.types.includes(conn.type)) return false;
            if (appliedFilters.environments.length > 0 && !appliedFilters.environments.includes(conn.environment)) return false;
            if (appliedFilters.statuses.length > 0 && !appliedFilters.statuses.includes(conn.status)) return false;
            if (appliedFilters.subAccounts.length > 0 && !appliedFilters.subAccounts.includes(conn.subAccount)) return false;
            if (appliedFilters.authTypes.length > 0 && !appliedFilters.authTypes.includes(conn.authType)) return false;
            
            return true;
        });
        
        filtered.sort(function(a, b) {
            var aVal = a[currentSort.column] || '';
            var bVal = b[currentSort.column] || '';
            
            if (typeof aVal === 'string') aVal = aVal.toLowerCase();
            if (typeof bVal === 'string') bVal = bVal.toLowerCase();
            
            if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
            if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
            return 0;
        });
        
        var html = '';
        filtered.forEach(function(conn) {
            var rowClass = conn.archived ? 'archived-row' : '';
            html += '<tr class="' + rowClass + '" data-id="' + conn.id + '">';
            
            html += '<td>';
            html += '<div class="api-name">' + conn.name + '</div>';
            if (conn.description) {
                html += '<div class="api-description">' + conn.description + '</div>';
            }
            html += '</td>';
            
            html += '<td>' + conn.subAccount + '</td>';
            
            html += '<td>';
            var typeHtml = '<span class="badge rounded-pill ' + getTypeBadgeClass(conn.type) + '"';
            if (conn.type === 'integration' && conn.integrationName) {
                typeHtml += ' title="' + conn.integrationName + '" data-bs-toggle="tooltip"';
            }
            typeHtml += '>' + getTypeLabel(conn.type) + '</span>';
            html += typeHtml;
            html += '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getEnvironmentBadgeClass(conn.environment) + '">' + 
                    (conn.environment === 'live' ? 'Live' : 'Test') + '</span></td>';
            
            html += '<td>' + conn.authType + '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getStatusBadgeClass(conn.status) + '">' + 
                    (conn.status === 'live' ? 'Live' : 'Suspended') + '</span></td>';
            
            html += '<td>';
            html += '<span class="base-url-cell">' + conn.baseUrl + '</span>';
            html += '<button class="copy-btn ms-1" onclick="copyToClipboard(\'' + conn.baseUrl + '\', this)" title="Copy URL">';
            html += '<i class="fas fa-copy"></i>';
            html += '</button>';
            html += '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getIpAllowBadgeClass(conn.ipAllowList) + '">' + 
                    (conn.ipAllowList ? 'On' : 'Off') + '</span></td>';
            
            html += '<td>' + formatDate(conn.createdDate) + '</td>';
            
            html += '<td>' + formatDateTime(conn.lastUsed) + '</td>';
            
            html += '<td>';
            html += '<div class="dropdown">';
            html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
            html += '<i class="fas fa-ellipsis-v"></i>';
            html += '</button>';
            html += '<ul class="dropdown-menu dropdown-menu-end">';
            
            html += '<li><a class="dropdown-item" href="#" onclick="viewConnection(' + conn.id + '); return false;"><i class="fas fa-eye me-2"></i>View Details</a></li>';
            
            if (conn.authType === 'API Key') {
                html += '<li><a class="dropdown-item" href="#" onclick="regenerateKey(' + conn.id + '); return false;"><i class="fas fa-sync-alt me-2"></i>Regenerate API Key</a></li>';
            }
            
            if (conn.authType === 'Basic Auth') {
                html += '<li><a class="dropdown-item" href="#" onclick="changePassword(' + conn.id + '); return false;"><i class="fas fa-key me-2"></i>Change Password</a></li>';
            }
            
            if (conn.status === 'live') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-warning" href="#" onclick="suspendConnection(' + conn.id + '); return false;"><i class="fas fa-pause me-2"></i>Suspend API</a></li>';
            }
            
            if (conn.status === 'suspended') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-success" href="#" onclick="reactivateConnection(' + conn.id + '); return false;"><i class="fas fa-play me-2"></i>Reactivate API</a></li>';
            }
            
            if (conn.environment === 'test' && !conn.archived) {
                html += '<li><a class="dropdown-item" href="#" onclick="convertToLive(' + conn.id + '); return false;"><i class="fas fa-rocket me-2"></i>Convert to Live</a></li>';
            }
            
            if (conn.status === 'suspended' && !conn.archived) {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-danger" href="#" onclick="archiveConnection(' + conn.id + '); return false;"><i class="fas fa-archive me-2"></i>Archive API</a></li>';
            }
            
            html += '</ul>';
            html += '</div>';
            html += '</td>';
            
            html += '</tr>';
        });
        
        $('#apiConnectionsBody').html(html);
        $('#showingCount').text(filtered.length);
        $('#totalCount').text(appliedFilters.showArchived ? apiConnections.length : apiConnections.filter(c => !c.archived).length);
        
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        $('.api-table thead th').removeClass('sorted');
        $('.api-table thead th[data-sort="' + currentSort.column + '"]').addClass('sorted');
    }
    
    window.copyToClipboard = copyToClipboard;
    
    $('.api-table thead th[data-sort]').on('click', function() {
        var column = $(this).data('sort');
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }
        renderTable();
    });
    
    $('.multiselect-dropdown').on('change', 'input[type="checkbox"]', function() {
        var filterName = $(this).closest('.multiselect-dropdown').data('filter');
        updateDropdownLabel(filterName);
    });
    
    $('.multiselect-dropdown').on('click', '.select-all-btn', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('input[type="checkbox"]').prop('checked', true);
        var filterName = $(this).closest('.multiselect-dropdown').data('filter');
        updateDropdownLabel(filterName);
    });
    
    $('.multiselect-dropdown').on('click', '.clear-all-btn', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('input[type="checkbox"]').prop('checked', false);
        var filterName = $(this).closest('.multiselect-dropdown').data('filter');
        updateDropdownLabel(filterName);
    });
    
    $('#btnApplyFilters').on('click', function() {
        applyFilters();
    });
    
    $('#btnResetFilters').on('click', function() {
        resetFilters();
    });
    
    $('#btnClearAllFilters').on('click', function() {
        resetFilters();
    });
    
    $(document).on('click', '.remove-chip', function() {
        var filterType = $(this).data('filter');
        var value = $(this).data('value');
        
        if (filterType === 'search') {
            $('#searchInput').val('');
            appliedFilters.search = '';
        } else if (filterType === 'showArchived') {
            $('#showArchivedToggle').prop('checked', false);
            appliedFilters.showArchived = false;
        } else {
            $('[data-filter="' + filterType + '"] input[value="' + value + '"]').prop('checked', false);
            updateDropdownLabel(filterType);
            var idx = appliedFilters[filterType].indexOf(value);
            if (idx > -1) appliedFilters[filterType].splice(idx, 1);
        }
        
        renderActiveFilters();
        renderTable();
    });
    
    function showConfirmModal(title, message, confirmText, confirmClass, onConfirm) {
        $('#confirmModalLabel').text(title);
        $('#confirmModalMessage').text(message);
        $('#confirmModalBtn').text(confirmText).removeClass('btn-danger btn-warning btn-primary btn-success').addClass(confirmClass);
        $('#confirmModalBtn').off('click').on('click', function() {
            $('#confirmModal').modal('hide');
            onConfirm();
        });
        $('#confirmModal').modal('show');
    }
    
    function getConnectionById(id) {
        return apiConnections.find(function(c) { return c.id === id; });
    }
    
    window.createApiConnection = function() {
        alert('Create API Connection - TODO: Implement modal/wizard');
    };
    
    window.viewConnection = function(id) {
        var conn = getConnectionById(id);
        if (!conn) return;
        
        $('#drawerApiName').text(conn.name);
        $('#drawerDescription').text(conn.description || 'No description provided');
        
        var typeLabel = getTypeLabel(conn.type);
        $('#drawerTypeBadge').text(typeLabel);
        
        var envLabel = conn.environment === 'live' ? 'Live' : 'Test';
        $('#drawerEnvBadge').text(envLabel);
        
        var statusLabel = conn.status === 'live' ? 'Active' : 'Suspended';
        $('#drawerStatusBadge').text(statusLabel);
        
        $('#drawerApiNameDetail').text(conn.name);
        $('#drawerSubAccount').text(conn.subAccount);
        $('#drawerType').text(typeLabel);
        $('#drawerEnvironment').text(envLabel);
        $('#drawerStatus').html('<span class="badge ' + getStatusBadgeClass(conn.status) + '">' + (conn.status === 'live' ? 'Live' : 'Suspended') + '</span>');
        
        $('#drawerAuthType').text(conn.authType);
        var maskedCred = conn.authType === 'API Key' ? 'sk_••••••••••••••••' : 'user:••••••••';
        $('#drawerCredentials').text(maskedCred);
        
        $('#drawerBaseUrl').val(conn.baseUrl);
        $('#drawerDlrUrl').val(conn.dlrUrl || 'Not configured');
        $('#drawerInboundUrl').val(conn.inboundUrl || 'Not configured');
        
        if (conn.ipAllowList) {
            $('#drawerIpAllowStatus').html('<span class="badge badge-on">On</span>');
            $('#drawerIpListRow').show();
            var ipHtml = (conn.allowedIps && conn.allowedIps.length > 0) 
                ? conn.allowedIps.map(function(ip) { return '<code class="me-2">' + ip + '</code>'; }).join('')
                : '<span class="text-muted">No IPs configured</span>';
            $('#drawerIpList').html(ipHtml);
        } else {
            $('#drawerIpAllowStatus').html('<span class="badge badge-off">Off</span>');
            $('#drawerIpListRow').hide();
        }
        
        $('#drawerCreatedDate').text(formatDate(conn.createdDate));
        
        var lastUsedDate = new Date(conn.lastUsed);
        var now = new Date();
        var diffSeconds = Math.floor((now - lastUsedDate) / 1000);
        var lastUsedText = formatDateTime(conn.lastUsed);
        if (diffSeconds < 60) {
            lastUsedText += ' (' + diffSeconds + ' seconds ago)';
        } else if (diffSeconds < 3600) {
            lastUsedText += ' (' + Math.floor(diffSeconds / 60) + ' minutes ago)';
        } else if (diffSeconds < 86400) {
            lastUsedText += ' (' + Math.floor(diffSeconds / 3600) + ' hours ago)';
        } else {
            lastUsedText += ' (' + Math.floor(diffSeconds / 86400) + ' days ago)';
        }
        $('#drawerLastUsed').text(lastUsedText);
        
        if (conn.dependencies && conn.dependencies.length > 0) {
            var depHtml = '<ul class="list-unstyled mb-0 small">';
            conn.dependencies.forEach(function(dep) {
                depHtml += '<li class="mb-1"><i class="fas fa-link text-muted me-2"></i><strong>' + dep.type + ':</strong> ' + dep.name;
                if (dep.count > 1) depHtml += ' <span class="text-muted">(' + dep.count + ' uses)</span>';
                depHtml += '</li>';
            });
            depHtml += '</ul>';
            $('#drawerDependencies').html(depHtml);
        } else {
            $('#drawerDependencies').html('<p class="text-muted small mb-0">No dependencies configured.</p>');
        }
        
        var drawer = new bootstrap.Offcanvas(document.getElementById('viewDetailsDrawer'));
        drawer.show();
    };
    
    window.copyDrawerField = function(fieldId) {
        var input = document.getElementById(fieldId);
        var value = input.value;
        if (value && value !== 'Not configured') {
            navigator.clipboard.writeText(value).then(function() {
                var btn = $(input).siblings('button');
                var originalHtml = btn.html();
                btn.html('<i class="fas fa-check text-success"></i>');
                setTimeout(function() {
                    btn.html(originalHtml);
                }, 1500);
            });
        }
    };
    
    window.regenerateKey = function(id) {
        var conn = getConnectionById(id);
        showConfirmModal(
            'Regenerate API Key',
            'Are you sure you want to regenerate the API key for "' + conn.name + '"? The current key will be invalidated immediately and any applications using it will stop working.',
            'Regenerate Key',
            'btn-warning',
            function() {
                alert('API Key regenerated for: ' + conn.name + '\n\nTODO: Implement API call');
            }
        );
    };
    
    window.changePassword = function(id) {
        var conn = getConnectionById(id);
        alert('Change Password for: ' + conn.name + '\n\nTODO: Implement password change modal');
    };
    
    window.suspendConnection = function(id) {
        var conn = getConnectionById(id);
        showConfirmModal(
            'Suspend API Connection',
            'Are you sure you want to suspend "' + conn.name + '"? All API requests using this connection will be rejected.',
            'Suspend API',
            'btn-warning',
            function() {
                alert('API suspended: ' + conn.name + '\n\nTODO: Implement API call');
            }
        );
    };
    
    window.reactivateConnection = function(id) {
        var conn = getConnectionById(id);
        showConfirmModal(
            'Reactivate API Connection',
            'Are you sure you want to reactivate "' + conn.name + '"? The API will immediately start accepting requests again.',
            'Reactivate API',
            'btn-success',
            function() {
                alert('API reactivated: ' + conn.name + '\n\nTODO: Implement API call');
            }
        );
    };
    
    window.convertToLive = function(id) {
        var conn = getConnectionById(id);
        showConfirmModal(
            'Convert to Live Environment',
            'Are you sure you want to convert "' + conn.name + '" to a Live environment? This action is permanent and cannot be undone. The API will be configured for production use.',
            'Convert to Live',
            'btn-primary',
            function() {
                alert('Converted to Live: ' + conn.name + '\n\nTODO: Implement API call');
            }
        );
    };
    
    window.archiveConnection = function(id) {
        var conn = getConnectionById(id);
        showConfirmModal(
            'Archive API Connection',
            'Are you sure you want to archive "' + conn.name + '"? The connection will be hidden from the main list but can be restored later.',
            'Archive API',
            'btn-danger',
            function() {
                alert('API archived: ' + conn.name + '\n\nTODO: Implement API call');
            }
        );
    };
    
    renderTable();
});
</script>
@endpush

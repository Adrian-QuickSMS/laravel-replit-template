@extends('layouts.admin')

@section('title', 'API Connections')

@push('styles')
<style>
.breadcrumb-item.active {
    color: #1e3a5f !important;
    font-weight: 500;
}
.breadcrumb-item.active a {
    color: #1e3a5f !important;
}
.integration-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}
.integration-tile:hover {
    border-color: #1e3a5f;
    background: rgba(30, 58, 95, 0.05);
}
.integration-tile.selected {
    border-color: #1e3a5f;
    background: rgba(30, 58, 95, 0.1);
}
.integration-tile .tile-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 0.75rem;
}
.integration-tile .tile-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.integration-tile .tile-desc {
    font-size: 0.8rem;
    color: #6c757d;
}
.credentials-display {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.5rem;
}
.credential-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}
.credential-row:last-child {
    border-bottom: none;
}
.credential-label {
    font-weight: 500;
    color: #495057;
}
.credential-value {
    font-family: monospace;
    background: #fff;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
}
.ip-list-input {
    min-height: 100px;
}
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
.api-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
    overflow-y: visible;
}
.api-table-container .dropdown-menu {
    z-index: 1060;
    min-width: 180px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    position: absolute !important;
}
.api-table td:last-child {
    overflow: visible;
}
.api-table {
    width: 100%;
    margin: 0;
    min-width: 1500px;
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
.api-table thead th:first-child { width: 12%; }
.api-table thead th:nth-child(2) { width: 12%; }
.api-table thead th:nth-child(3) { width: 9%; }
.api-table thead th:nth-child(4) { width: 8%; }
.api-table thead th:nth-child(5) { width: 7%; }
.api-table thead th:nth-child(6) { width: 7%; }
.api-table thead th:nth-child(7) { width: 6%; }
.api-table thead th:nth-child(8) { width: 12%; }
.api-table thead th:nth-child(9) { width: 7%; }
.api-table thead th:nth-child(10) { width: 9%; }
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
    color: #1e3a5f;
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
    color: #1e3a5f;
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
    color: #1e3a5f;
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
    max-height: 250px;
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
    background: #1e3a5f;
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
    background-color: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
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
.badge-bulk {
    background: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
}
.badge-campaign {
    background: rgba(255, 193, 7, 0.15);
    color: #d39e00;
}
.badge-integration {
    background: rgba(23, 162, 184, 0.15);
    color: #117a8b;
}
.badge-live-env {
    background: rgba(40, 167, 69, 0.15);
    color: #28a745;
}
.badge-test-env {
    background: rgba(255, 193, 7, 0.15);
    color: #d39e00;
}
.badge-live-status {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-suspended-status {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.account-cell {
    font-weight: 500;
    color: #1e3a5f;
}
.account-link {
    color: #1e3a5f;
    text-decoration: none;
}
.account-link:hover {
    text-decoration: underline;
}
.admin-filter-panel {
    background-color: rgba(30, 58, 95, 0.08);
    border-bottom: 1px solid #e9ecef !important;
}
.multiselect-search {
    border-bottom: 1px solid #e9ecef;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
}
.multiselect-search input {
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">API & Integrations</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">API Connections</a></li>
        </ol>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h5 class="card-title mb-0">API Connections</h5>
                <small class="text-muted">Global view of all customer API connections</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border: 1px solid #1e3a5f; color: #1e3a5f; background: transparent;">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
                <a href="{{ route('admin.api.connections') }}/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create API Connection
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search by API name, account...">
                </div>
            </div>
        
        <div class="collapse" id="filtersPanel">
            <div class="card card-body border-0 rounded-0 admin-filter-panel">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-bold">Account</label>
                        <div class="dropdown multiselect-dropdown" data-filter="accounts">
                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                <span class="dropdown-label">All Accounts</span>
                            </button>
                            <div class="dropdown-menu w-100 p-0">
                                <div class="multiselect-search">
                                    <input type="text" class="form-control form-control-sm" placeholder="Search accounts..." id="accountSearchInput">
                                </div>
                                <div class="d-flex justify-content-between px-3 pb-2 border-bottom">
                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                </div>
                                <div class="p-2" id="accountCheckboxList"></div>
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
        
            <div class="mb-3" id="activeFiltersContainer" style="display: none;">
                <div class="d-flex flex-wrap align-items-center">
                    <span class="small text-muted me-2">Active filters:</span>
                    <div id="activeFiltersChips"></div>
                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" id="btnClearAllFilters">
                        Clear all
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover api-table" id="apiConnectionsTable">
            <thead>
                <tr>
                    <th data-sort="account">Account <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="name">API Name <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="subAccount">Sub-Account <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="environment">Environment <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="authType">Auth Type <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="createdDate">Created Date <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="lastUsed">Last Used <i class="fas fa-sort sort-icon"></i></th>
                    <th>Actions</th>
                </tr>
            </thead>
                    <tbody id="apiConnectionsBody">
                    </tbody>
                </table>
            </div>
        
            <div class="table-footer mt-3">
                <div class="pagination-info">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> connections
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="viewDetailsDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title mb-0"><i class="fas fa-plug me-2" style="color: #1e3a5f;"></i>API Connection Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-4 border-bottom">
            <h5 id="drawerApiName" class="mb-3 fw-semibold">-</h5>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span id="drawerTypeBadge" class="badge rounded-pill">-</span>
                <span id="drawerEnvBadge" class="badge rounded-pill">-</span>
                <span id="drawerStatusBadge" class="badge rounded-pill">-</span>
            </div>
            <div class="small text-muted mt-2" id="drawerDescription">-</div>
        </div>

        <div class="p-4">
            <div class="card mb-3" style="border-left: 3px solid #1e3a5f;">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-building me-2" style="color: #1e3a5f;"></i>Account Information</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Account</div>
                        <div class="col-7 small fw-medium" id="drawerAccount">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Sub-Account</div>
                        <div class="col-7 small" id="drawerSubAccount">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-info-circle me-2" style="color: #1e3a5f;"></i>Connection Information</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">API Name</div>
                        <div class="col-7 small fw-medium" id="drawerApiNameDetail">-</div>
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
                    <h6 class="card-title mb-3"><i class="fas fa-key me-2" style="color: #1e3a5f;"></i>Authentication</h6>
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
                    <h6 class="card-title mb-3"><i class="fas fa-link me-2" style="color: #1e3a5f;"></i>Endpoints</h6>
                    <div class="row mb-2">
                        <div class="col-4 text-muted small">Base URL</div>
                        <div class="col-8 small d-flex align-items-center">
                            <code id="drawerBaseUrl" class="text-break me-2">-</code>
                            <button class="btn btn-sm btn-link p-0" onclick="copyDrawerField('drawerBaseUrl')"><i class="fas fa-copy text-muted"></i></button>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted small">DLR Webhook</div>
                        <div class="col-8 small d-flex align-items-center">
                            <code id="drawerDlrUrl" class="text-break me-2">-</code>
                            <button class="btn btn-sm btn-link p-0" onclick="copyDrawerField('drawerDlrUrl')"><i class="fas fa-copy text-muted"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 text-muted small">Inbound Webhook</div>
                        <div class="col-8 small d-flex align-items-center">
                            <code id="drawerInboundUrl" class="text-break me-2">-</code>
                            <button class="btn btn-sm btn-link p-0" onclick="copyDrawerField('drawerInboundUrl')"><i class="fas fa-copy text-muted"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-shield-alt me-2" style="color: #1e3a5f;"></i>Security</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">IP Allowlist</div>
                        <div class="col-7 small" id="drawerIpAllowStatus">-</div>
                    </div>
                    <div class="row" id="drawerIpListRow" style="display: none;">
                        <div class="col-5 text-muted small">Allowed IPs</div>
                        <div class="col-7 small" id="drawerIpList">-</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-clock me-2" style="color: #1e3a5f;"></i>Activity</h6>
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
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage">Are you sure?</p>
                <div class="alert alert-warning" id="confirmModalWarning" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i><span id="confirmModalWarningText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmModalBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="regenerateKeyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New API Key Generated</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Copy this API key now. You won't be able to see it again.
                </div>
                <div class="bg-light p-3 rounded border">
                    <div class="d-flex justify-content-between align-items-center">
                        <code id="newApiKeyDisplay" class="text-break">-</code>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyNewApiKey()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var allAccounts = [
        { id: 'ACC-001', name: 'Acme Corporation' },
        { id: 'ACC-002', name: 'Finance Ltd' },
        { id: 'ACC-003', name: 'Tech Solutions' },
        { id: 'ACC-004', name: 'Global Retail' },
        { id: 'ACC-005', name: 'Healthcare Plus' },
        { id: 'ACC-006', name: 'Media Group' },
        { id: 'ACC-007', name: 'Logistics Pro' },
        { id: 'ACC-008', name: 'Education First' },
        { id: 'ACC-009', name: 'Property Services' },
        { id: 'ACC-010', name: 'Legal Partners' }
    ];

    var apiConnections = [
        { id: 1, account: 'Acme Corporation', accountId: 'ACC-001', name: 'Campaign Manager API', description: 'Main campaign management', subAccount: 'Marketing', type: 'campaign', environment: 'live', authType: 'API Key', status: 'live', createdDate: '2024-08-15', lastUsed: '2025-01-20T14:32:15', baseUrl: 'https://api.quicksms.co.uk/v2', dlrUrl: 'https://acme.com/webhooks/dlr', inboundUrl: 'https://acme.com/webhooks/inbound', ipAllowList: true, allowedIps: ['192.168.1.100', '10.0.0.50'], archived: false },
        { id: 2, account: 'Acme Corporation', accountId: 'ACC-001', name: 'Bulk Sender', description: 'High volume transactional', subAccount: 'Main Account', type: 'bulk', environment: 'live', authType: 'Basic Auth', status: 'live', createdDate: '2024-06-22', lastUsed: '2025-01-19T09:15:00', baseUrl: 'https://api.quicksms.co.uk/v2', dlrUrl: null, inboundUrl: null, ipAllowList: false, allowedIps: [], archived: false },
        { id: 3, account: 'Finance Ltd', accountId: 'ACC-002', name: 'SystmOne Integration', description: 'NHS patient notifications', subAccount: 'Main Account', type: 'integration', integrationName: 'SystmOne', environment: 'live', authType: 'API Key', status: 'live', createdDate: '2024-05-10', lastUsed: '2025-01-20T16:45:30', baseUrl: 'https://api.quicksms.co.uk/v2/integrations/systmone', dlrUrl: null, inboundUrl: null, ipAllowList: true, allowedIps: ['203.0.113.25'], archived: false },
        { id: 4, account: 'Tech Solutions', accountId: 'ACC-003', name: 'Test API', description: 'Development testing', subAccount: 'Development', type: 'bulk', environment: 'test', authType: 'API Key', status: 'live', createdDate: '2024-11-01', lastUsed: '2025-01-18T11:22:00', baseUrl: 'https://sandbox.quicksms.co.uk/v2', dlrUrl: 'https://tech.test/dlr', inboundUrl: null, ipAllowList: false, allowedIps: [], archived: false },
        { id: 5, account: 'Global Retail', accountId: 'ACC-004', name: 'Promo Campaign API', description: 'Marketing campaigns', subAccount: 'Marketing', type: 'campaign', environment: 'live', authType: 'API Key', status: 'suspended', createdDate: '2024-03-20', lastUsed: '2024-12-15T08:00:00', baseUrl: 'https://api.quicksms.co.uk/v2', dlrUrl: 'https://retail.com/webhooks/dlr', inboundUrl: 'https://retail.com/webhooks/mo', ipAllowList: true, allowedIps: ['45.67.89.100', '45.67.89.101'], archived: false },
        { id: 6, account: 'Healthcare Plus', accountId: 'ACC-005', name: 'Patient Reminders', description: 'Appointment reminders', subAccount: 'Main Account', type: 'campaign', environment: 'live', authType: 'API Key', status: 'live', createdDate: '2024-04-05', lastUsed: '2025-01-20T10:30:00', baseUrl: 'https://api.quicksms.co.uk/v2', dlrUrl: null, inboundUrl: null, ipAllowList: false, allowedIps: [], archived: false },
        { id: 7, account: 'Media Group', accountId: 'ACC-006', name: 'Notification Service', description: 'Breaking news alerts', subAccount: 'Editorial', type: 'bulk', environment: 'live', authType: 'Basic Auth', status: 'live', createdDate: '2024-07-12', lastUsed: '2025-01-20T18:05:00', baseUrl: 'https://api.quicksms.co.uk/v2', dlrUrl: 'https://media.group/dlr', inboundUrl: null, ipAllowList: false, allowedIps: [], archived: false },
        { id: 8, account: 'Logistics Pro', accountId: 'ACC-007', name: 'Delivery Updates', description: 'Real-time tracking notifications', subAccount: 'Operations', type: 'bulk', environment: 'live', authType: 'API Key', status: 'live', createdDate: '2024-09-18', lastUsed: '2025-01-20T20:15:00', baseUrl: 'https://api.quicksms.co.uk/v2', dlrUrl: 'https://logistics.pro/webhooks/dlr', inboundUrl: 'https://logistics.pro/webhooks/reply', ipAllowList: true, allowedIps: ['78.90.12.34'], archived: false },
        { id: 9, account: 'Education First', accountId: 'ACC-008', name: 'Rio Integration', description: 'Mental health service integration', subAccount: 'Main Account', type: 'integration', integrationName: 'Rio', environment: 'live', authType: 'API Key', status: 'live', createdDate: '2024-10-25', lastUsed: '2025-01-19T14:00:00', baseUrl: 'https://api.quicksms.co.uk/v2/integrations/rio', dlrUrl: null, inboundUrl: null, ipAllowList: false, allowedIps: [], archived: false },
        { id: 10, account: 'Property Services', accountId: 'ACC-009', name: 'Archived Legacy API', description: 'Old system - deprecated', subAccount: 'Main Account', type: 'bulk', environment: 'live', authType: 'Basic Auth', status: 'suspended', createdDate: '2023-01-15', lastUsed: '2024-06-01T12:00:00', baseUrl: 'https://api.quicksms.co.uk/v1', dlrUrl: null, inboundUrl: null, ipAllowList: false, allowedIps: [], archived: true },
        { id: 11, account: 'Legal Partners', accountId: 'ACC-010', name: 'Client Notifications', description: 'Case updates', subAccount: 'Main Account', type: 'campaign', environment: 'test', authType: 'API Key', status: 'live', createdDate: '2025-01-10', lastUsed: '2025-01-20T09:00:00', baseUrl: 'https://sandbox.quicksms.co.uk/v2', dlrUrl: null, inboundUrl: null, ipAllowList: false, allowedIps: [], archived: false },
        { id: 12, account: 'Finance Ltd', accountId: 'ACC-002', name: 'Secure Alerts', description: 'Transaction alerts', subAccount: 'Security', type: 'bulk', environment: 'live', authType: 'API Key', status: 'live', createdDate: '2024-02-28', lastUsed: '2025-01-20T22:10:00', baseUrl: 'https://api.quicksms.co.uk/v2', dlrUrl: 'https://finance.ltd/secure/dlr', inboundUrl: null, ipAllowList: true, allowedIps: ['10.20.30.40', '10.20.30.41', '10.20.30.42'], archived: false }
    ];
    
    var currentSort = { column: 'createdDate', direction: 'desc' };
    var appliedFilters = {
        search: '',
        accounts: [],
        types: [],
        environments: [],
        statuses: [],
        authTypes: [],
        showArchived: false
    };
    
    initAccountFilter();
    renderTable();
    
    function initAccountFilter() {
        var checkboxList = $('#accountCheckboxList');
        var searchInput = $('#accountSearchInput');
        
        function renderAccountCheckboxes(filter) {
            checkboxList.empty();
            var filteredAccounts = allAccounts.filter(function(acc) {
                return acc.name.toLowerCase().includes(filter.toLowerCase());
            });
            
            filteredAccounts.forEach(function(acc) {
                var isChecked = appliedFilters.accounts.includes(acc.name) ? 'checked' : '';
                checkboxList.append(
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="checkbox" value="' + acc.name + '" id="acc_' + acc.id + '" ' + isChecked + '>' +
                    '<label class="form-check-label small" for="acc_' + acc.id + '">' + acc.name + '</label>' +
                    '</div>'
                );
            });
        }
        
        renderAccountCheckboxes('');
        
        searchInput.on('input', function() {
            renderAccountCheckboxes($(this).val());
        });
        
        checkboxList.on('change', 'input[type="checkbox"]', function() {
            updateDropdownLabel('accounts');
        });
    }
    
    function getTypeLabel(type) {
        switch(type) {
            case 'bulk': return 'Bulk API';
            case 'campaign': return 'Campaign API';
            case 'integration': return 'Integration';
            default: return type;
        }
    }
    
    function getTypeBadgeClass(type) {
        switch(type) {
            case 'bulk': return 'badge-bulk';
            case 'campaign': return 'badge-campaign';
            case 'integration': return 'badge-integration';
            default: return 'bg-secondary';
        }
    }
    
    function getEnvironmentBadgeClass(env) {
        return env === 'live' ? 'badge-live-env' : 'badge-test-env';
    }
    
    function getStatusBadgeClass(status) {
        return status === 'live' ? 'badge-live-status' : 'badge-suspended-status';
    }
    
    function formatDate(dateStr) {
        var date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    
    function formatDateTime(dateTimeStr) {
        var date = new Date(dateTimeStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + 
               ' ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
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
            'accounts': 'All Accounts',
            'types': 'All Types',
            'environments': 'All Environments',
            'statuses': 'All Statuses',
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
        appliedFilters.accounts = getPendingFilterValues('accounts');
        appliedFilters.types = getPendingFilterValues('types');
        appliedFilters.environments = getPendingFilterValues('environments');
        appliedFilters.statuses = getPendingFilterValues('statuses');
        appliedFilters.authTypes = getPendingFilterValues('authTypes');
        appliedFilters.showArchived = $('#showArchivedToggle').is(':checked');
        
        renderActiveFilters();
        renderTable();
    }
    
    function resetFilters() {
        $('#searchInput').val('');
        $('#showArchivedToggle').prop('checked', false);
        $('#accountSearchInput').val('');
        
        $('.multiselect-dropdown input[type="checkbox"]').prop('checked', false);
        
        $('.multiselect-dropdown').each(function() {
            var filterName = $(this).data('filter');
            updateDropdownLabel(filterName);
        });
        
        initAccountFilter();
        
        appliedFilters = {
            search: '',
            accounts: [],
            types: [],
            environments: [],
            statuses: [],
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
        
        appliedFilters.accounts.forEach(function(val) {
            hasFilters = true;
            $chips.append('<span class="filter-chip"><span class="chip-label">Account:</span>' + val + '<span class="remove-chip" data-filter="accounts" data-value="' + val + '"><i class="fas fa-times"></i></span></span>');
        });
        
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
                if (!conn.name.toLowerCase().includes(search) && !conn.account.toLowerCase().includes(search)) return false;
            }
            
            if (appliedFilters.accounts.length > 0 && !appliedFilters.accounts.includes(conn.account)) return false;
            if (appliedFilters.types.length > 0 && !appliedFilters.types.includes(conn.type)) return false;
            if (appliedFilters.environments.length > 0 && !appliedFilters.environments.includes(conn.environment)) return false;
            if (appliedFilters.statuses.length > 0 && !appliedFilters.statuses.includes(conn.status)) return false;
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
            
            html += '<td class="account-cell"><a href="#" class="account-link">' + conn.account + '</a></td>';
            
            html += '<td>';
            html += '<div class="api-name">' + conn.name + '</div>';
            if (conn.description) {
                html += '<div class="api-description">' + conn.description + '</div>';
            }
            html += '</td>';
            
            html += '<td>' + conn.subAccount + '</td>';
            
            html += '<td>';
            var typeText = getTypeLabel(conn.type);
            if (conn.type === 'integration' && conn.integrationName) {
                typeText += ' <span class="text-muted small">(' + conn.integrationName + ')</span>';
            }
            html += typeText;
            html += '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getEnvironmentBadgeClass(conn.environment) + '">' + (conn.environment === 'live' ? 'Live' : 'Test') + '</span></td>';
            
            html += '<td>' + conn.authType + '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getStatusBadgeClass(conn.status) + '">' + 
                    (conn.status === 'live' ? 'Live' : 'Suspended') + '</span></td>';
            
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
            
            html += '</ul>';
            html += '</div>';
            html += '</td>';
            
            html += '</tr>';
        });
        
        $('#apiConnectionsBody').html(html);
        $('#showingCount').text(filtered.length);
        $('#totalCount').text(appliedFilters.showArchived ? apiConnections.length : apiConnections.filter(c => !c.archived).length);
        
        // Initialize Bootstrap dropdowns for dynamically added content with proper Popper config
        $('#apiConnectionsBody [data-bs-toggle="dropdown"]').each(function() {
            new bootstrap.Dropdown(this, {
                popperConfig: {
                    strategy: 'fixed',
                    modifiers: [
                        {
                            name: 'preventOverflow',
                            options: {
                                boundary: 'viewport',
                                padding: 8
                            }
                        },
                        {
                            name: 'flip',
                            options: {
                                fallbackPlacements: ['top-end', 'bottom-end', 'left-start']
                            }
                        }
                    ]
                }
            });
        });
        
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
    
    var searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });
    
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            clearTimeout(searchTimeout);
            applyFilters();
        }
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
    
    var pendingConfirmCallback = null;
    
    function showConfirmModal(title, message, confirmText, confirmClass, onConfirm, warningText) {
        $('#confirmModalLabel').text(title);
        $('#confirmModalMessage').text(message);
        $('#confirmModalBtn').text(confirmText).removeClass('btn-danger btn-warning btn-primary btn-success').addClass(confirmClass);
        
        if (warningText) {
            $('#confirmModalWarningText').text(warningText);
            $('#confirmModalWarning').show();
        } else {
            $('#confirmModalWarning').hide();
        }
        
        $('#confirmModalBtn').off('click').on('click', function() {
            pendingConfirmCallback = onConfirm;
            $('#confirmModal').modal('hide');
        });
        
        $('#confirmModal').modal('show');
    }
    
    $('#confirmModal').on('hidden.bs.modal', function() {
        if (pendingConfirmCallback) {
            var callback = pendingConfirmCallback;
            pendingConfirmCallback = null;
            setTimeout(function() {
                callback();
            }, 100);
        }
    });
    
    function getConnectionById(id) {
        return apiConnections.find(function(c) { return c.id === id; });
    }
    
    window.viewConnection = function(id) {
        var conn = getConnectionById(id);
        if (!conn) return;
        
        $('#drawerApiName').text(conn.name);
        $('#drawerDescription').text(conn.description || 'No description provided');
        
        var typeLabel = getTypeLabel(conn.type);
        $('#drawerTypeBadge').removeClass().addClass('badge rounded-pill ' + getTypeBadgeClass(conn.type)).text(typeLabel);
        
        var envLabel = conn.environment === 'live' ? 'Live' : 'Test';
        $('#drawerEnvBadge').removeClass().addClass('badge rounded-pill ' + getEnvironmentBadgeClass(conn.environment)).text(envLabel);
        
        var statusLabel = conn.status === 'live' ? 'Live' : 'Suspended';
        $('#drawerStatusBadge').removeClass().addClass('badge rounded-pill ' + getStatusBadgeClass(conn.status)).text(statusLabel);
        
        $('#drawerAccount').text(conn.account);
        $('#drawerApiNameDetail').text(conn.name);
        $('#drawerSubAccount').text(conn.subAccount);
        $('#drawerType').text(typeLabel);
        $('#drawerEnvironment').text(envLabel);
        $('#drawerStatus').html('<span class="badge rounded-pill ' + getStatusBadgeClass(conn.status) + '">' + statusLabel + '</span>');
        
        $('#drawerAuthType').text(conn.authType);
        var maskedCred = conn.authType === 'API Key' ? 'sk_••••••••••••••••' : 'user:••••••••';
        $('#drawerCredentials').text(maskedCred);
        
        $('#drawerBaseUrl').text(conn.baseUrl);
        $('#drawerDlrUrl').text(conn.dlrUrl || 'Not configured');
        $('#drawerInboundUrl').text(conn.inboundUrl || 'Not configured');
        
        if (conn.ipAllowList) {
            $('#drawerIpAllowStatus').html('<span class="badge rounded-pill badge-on">Enabled</span>');
            $('#drawerIpListRow').show();
            var ipHtml = (conn.allowedIps && conn.allowedIps.length > 0) 
                ? conn.allowedIps.map(function(ip) { return '<span class="badge rounded-pill badge-bulk me-1 mb-1">' + ip + '</span>'; }).join('')
                : '<span class="text-muted">No IPs configured</span>';
            $('#drawerIpList').html(ipHtml);
        } else {
            $('#drawerIpAllowStatus').html('<span class="badge rounded-pill badge-off">Disabled</span>');
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
        
        var drawer = new bootstrap.Offcanvas(document.getElementById('viewDetailsDrawer'));
        drawer.show();
    };
    
    window.copyDrawerField = function(fieldId) {
        var element = document.getElementById(fieldId);
        var value = element.textContent;
        if (value && value !== 'Not configured' && value !== '-') {
            navigator.clipboard.writeText(value).then(function() {
                var btn = $(element).parent().find('button');
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
        if (!conn) return;
        
        showConfirmModal(
            'Regenerate API Key',
            'Are you sure you want to regenerate the API key for "' + conn.name + '"? The current key will stop working immediately.',
            'Regenerate Key',
            'btn-warning',
            function() {
                var newKey = 'sk_live_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                $('#newApiKeyDisplay').text(newKey);
                $('#regenerateKeyModal').modal('show');
            },
            'This action cannot be undone. Make sure to update all systems using this API key.'
        );
    };
    
    window.copyNewApiKey = function() {
        var key = $('#newApiKeyDisplay').text();
        navigator.clipboard.writeText(key).then(function() {
            var btn = $('#regenerateKeyModal .btn-outline-primary');
            var originalHtml = btn.html();
            btn.html('<i class="fas fa-check text-success"></i>');
            setTimeout(function() {
                btn.html(originalHtml);
            }, 1500);
        });
    };
    
    window.suspendConnection = function(id) {
        var conn = getConnectionById(id);
        if (!conn) return;
        
        showConfirmModal(
            'Suspend API Connection',
            'Are you sure you want to suspend "' + conn.name + '" for ' + conn.account + '?',
            'Suspend',
            'btn-warning',
            function() {
                conn.status = 'suspended';
                renderTable();
            },
            'This will immediately stop all API requests using this connection.'
        );
    };
    
    window.reactivateConnection = function(id) {
        var conn = getConnectionById(id);
        if (!conn) return;
        
        showConfirmModal(
            'Reactivate API Connection',
            'Are you sure you want to reactivate "' + conn.name + '" for ' + conn.account + '?',
            'Reactivate',
            'btn-success',
            function() {
                conn.status = 'live';
                renderTable();
            }
        );
    };
    
    window.convertToLive = function(id) {
        var conn = getConnectionById(id);
        if (!conn) return;
        
        showConfirmModal(
            'Convert to Live',
            'Are you sure you want to convert "' + conn.name + '" from Test to Live environment?',
            'Convert to Live',
            'btn-primary',
            function() {
                conn.environment = 'live';
                conn.baseUrl = conn.baseUrl.replace('sandbox.', 'api.');
                renderTable();
            },
            'This will change the API endpoint to the production server.'
        );
    };
    
    console.log('[Admin API Connections] Module loaded - Global view of all customer API connections');
});
</script>
@endpush

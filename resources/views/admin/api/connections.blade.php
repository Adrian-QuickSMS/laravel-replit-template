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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0"><i class="fas fa-link me-2" style="color: #1e3a5f;"></i>Endpoints</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="openEditEndpointsModal()" title="Edit Endpoints">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0"><i class="fas fa-shield-alt me-2" style="color: #1e3a5f;"></i>Security</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="openEditSecurityModal()" title="Edit Security">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
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

<!-- Edit Endpoints Modal -->
<div class="modal fade" id="editEndpointsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-link me-2"></i>Edit Endpoints</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Base URL</label>
                    <input type="url" class="form-control" id="editBaseUrl" placeholder="https://api.quicksms.co.uk/v2">
                    <small class="text-muted">The base URL for this API connection</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">DLR Webhook URL</label>
                    <div class="input-group">
                        <input type="url" class="form-control" id="editDlrUrl" placeholder="https://example.com/webhooks/dlr">
                        <button class="btn btn-outline-danger" type="button" onclick="clearField('editDlrUrl')" title="Clear">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <small class="text-muted">Delivery receipts will be sent to this URL</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Inbound Webhook URL</label>
                    <div class="input-group">
                        <input type="url" class="form-control" id="editInboundUrl" placeholder="https://example.com/webhooks/inbound">
                        <button class="btn btn-outline-danger" type="button" onclick="clearField('editInboundUrl')" title="Clear">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <small class="text-muted">Inbound messages will be forwarded to this URL</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn text-white" style="background: var(--admin-primary);" onclick="saveEndpointsChanges()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Security Modal -->
<div class="modal fade" id="editSecurityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i>Edit Security Settings</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editIpAllowlistEnabled" onchange="toggleIpAllowlistSection()">
                        <label class="form-check-label fw-bold" for="editIpAllowlistEnabled">Enable IP Allowlist</label>
                    </div>
                    <small class="text-muted">When enabled, only requests from allowed IP addresses will be accepted</small>
                </div>
                
                <div id="ipAllowlistSection" style="display: none;">
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Allowed IP Addresses</label>
                        <div class="alert alert-info small py-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Enter one IP address per line. Supports IPv4 addresses and CIDR notation (e.g., 192.168.1.0/24)
                        </div>
                        <textarea class="form-control font-monospace" id="editAllowedIps" rows="5" placeholder="192.168.1.1
10.0.0.0/8
203.0.113.50"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addCurrentIp()">
                            <i class="fas fa-plus me-1"></i> Add Current IP
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllIps()">
                            <i class="fas fa-trash me-1"></i> Clear All
                        </button>
                    </div>
                    
                    <div id="ipValidationErrors" class="alert alert-danger small py-2" style="display: none;">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <span id="ipValidationErrorText"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn text-white" style="background: var(--admin-primary);" onclick="saveSecurityChanges()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var apiBase = '/admin/api/api-connections';

    var allAccounts = [];
    var apiConnections = [];
    var currentConnection = null;

    function getPartnerDisplayName(partnerName) {
        if (!partnerName) return null;
        var map = {
            'systmone': 'SystmOne',
            'rio': 'Rio',
            'emis': 'EMIS',
            'accurx': 'Accurx'
        };
        return map[partnerName] || partnerName.charAt(0).toUpperCase() + partnerName.slice(1);
    }

    function mapApiToLocal(item) {
        var authTypeMap = { 'api_key': 'API Key', 'basic_auth': 'Basic Auth' };
        var statusMap = { 'active': 'live', 'suspended': 'suspended', 'archived': 'suspended' };
        return {
            id: item.id,
            account: item.account_name || 'Unknown Account',
            accountId: item.account_id,
            name: item.name,
            description: item.description,
            subAccount: item.sub_account_name || 'Main Account',
            type: item.type,
            environment: item.environment,
            authType: authTypeMap[item.auth_type] || item.auth_type,
            status: statusMap[item.status] || item.status,
            createdDate: item.created_at || null,
            lastUsed: item.last_used_at || null,
            baseUrl: 'https://api.quicksms.co.uk/v2',
            dlrUrl: item.webhook_dlr_url || null,
            inboundUrl: item.webhook_inbound_url || null,
            ipAllowList: item.ip_allowlist_enabled || false,
            allowedIps: item.ip_allowlist || [],
            archived: item.status === 'archived',
            integrationName: getPartnerDisplayName(item.partner_name),
            credentialDisplay: item.credential_display || null
        };
    }

    function apiRequest(method, url, data) {
        var options = {
            method: method,
            url: url,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            dataType: 'json'
        };
        if (data) options.data = JSON.stringify(data);
        return $.ajax(options);
    }

    function loadConnections() {
        apiRequest('GET', apiBase + '?show_archived=' + (appliedFilters.showArchived ? '1' : '0'))
            .done(function(response) {
                var items = response.data || [];
                apiConnections = items.map(mapApiToLocal);

                var accountMap = {};
                apiConnections.forEach(function(c) {
                    if (c.accountId && !accountMap[c.accountId]) {
                        accountMap[c.accountId] = { id: c.accountId, name: c.account };
                    }
                });
                allAccounts = Object.values(accountMap).sort(function(a, b) {
                    return a.name.localeCompare(b.name);
                });

                initAccountFilter();
                renderTable();
            })
            .fail(function(xhr) {
                console.error('[Admin API Connections] Failed to load connections', xhr);
                showToast('Failed to load API connections', 'error');
            });
    }
    
    function showToast(message, type) {
        type = type || 'info';
        var bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : (type === 'warning' ? 'bg-warning text-dark' : 'bg-info'));
        var toastHtml = '<div class="toast align-items-center text-white ' + bgClass + ' border-0 position-fixed" role="alert" style="z-index: 99999; top: 20px; right: 20px;">' +
            '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
        var $toast = $(toastHtml).appendTo('body');
        var toast = new bootstrap.Toast($toast[0], { delay: 4000 });
        toast.show();
        $toast.on('hidden.bs.toast', function() { $toast.remove(); });
    }

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
    
    loadConnections();
    
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
        if (status === 'live' || status === 'active') return 'badge-live-status';
        return 'badge-suspended-status';
    }
    
    function getStatusLabel(status) {
        if (status === 'live' || status === 'active') return 'Live';
        if (status === 'suspended') return 'Suspended';
        if (status === 'archived') return 'Archived';
        return status;
    }
    
    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var date = new Date(dateStr);
        if (isNaN(date.getTime())) return '-';
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    
    function formatDateTime(dateTimeStr) {
        if (!dateTimeStr) return '-';
        var date = new Date(dateTimeStr);
        if (isNaN(date.getTime())) return '-';
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
                    getStatusLabel(conn.status) + '</span></td>';
            
            html += '<td>' + formatDate(conn.createdDate) + '</td>';
            
            html += '<td>' + formatDateTime(conn.lastUsed) + '</td>';
            
            html += '<td>';
            html += '<div class="dropdown">';
            html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
            html += '<i class="fas fa-ellipsis-v"></i>';
            html += '</button>';
            html += '<ul class="dropdown-menu dropdown-menu-end">';
            
            html += '<li><a class="dropdown-item" href="#" onclick="viewConnection(\'' + conn.id + '\'); return false;"><i class="fas fa-eye me-2"></i>View Details</a></li>';
            
            if (conn.authType === 'API Key') {
                html += '<li><a class="dropdown-item" href="#" onclick="regenerateKey(\'' + conn.id + '\'); return false;"><i class="fas fa-sync-alt me-2"></i>Regenerate API Key</a></li>';
            }
            
            if (conn.status === 'live') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-warning" href="#" onclick="suspendConnection(\'' + conn.id + '\'); return false;"><i class="fas fa-pause me-2"></i>Suspend API</a></li>';
            }
            
            if (conn.status === 'suspended' && !conn.archived) {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-success" href="#" onclick="reactivateConnection(\'' + conn.id + '\'); return false;"><i class="fas fa-play me-2"></i>Reactivate API</a></li>';
            }
            
            if (conn.environment === 'test' && !conn.archived) {
                html += '<li><a class="dropdown-item" href="#" onclick="convertToLive(\'' + conn.id + '\'); return false;"><i class="fas fa-rocket me-2"></i>Convert to Live</a></li>';
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
        return apiConnections.find(function(c) { return c.id == id; });
    }
    
    window.viewConnection = function(id) {
        var conn = getConnectionById(id);
        if (!conn) return;
        
        currentConnection = conn;
        
        $('#drawerApiName').text(conn.name);
        $('#drawerDescription').text(conn.description || 'No description provided');
        
        var typeLabel = getTypeLabel(conn.type);
        $('#drawerTypeBadge').removeClass().addClass('badge rounded-pill ' + getTypeBadgeClass(conn.type)).text(typeLabel);
        
        var envLabel = conn.environment === 'live' ? 'Live' : 'Test';
        $('#drawerEnvBadge').removeClass().addClass('badge rounded-pill ' + getEnvironmentBadgeClass(conn.environment)).text(envLabel);
        
        var statusLabel = getStatusLabel(conn.status);
        $('#drawerStatusBadge').removeClass().addClass('badge rounded-pill ' + getStatusBadgeClass(conn.status)).text(statusLabel);
        
        $('#drawerAccount').text(conn.account);
        $('#drawerApiNameDetail').text(conn.name);
        $('#drawerSubAccount').text(conn.subAccount);
        $('#drawerType').text(typeLabel);
        $('#drawerEnvironment').text(envLabel);
        $('#drawerStatus').html('<span class="badge rounded-pill ' + getStatusBadgeClass(conn.status) + '">' + statusLabel + '</span>');
        
        $('#drawerAuthType').text(conn.authType);
        var maskedCred = conn.credentialDisplay || (conn.authType === 'API Key' ? 'sk_••••••••••••••••' : 'user:••••••••');
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
        
        if (conn.lastUsed) {
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
        } else {
            $('#drawerLastUsed').text('Never');
        }
        
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
                apiRequest('POST', apiBase + '/' + id + '/regenerate-key')
                    .done(function(response) {
                        var newKey = response.credentials && response.credentials.api_key ? response.credentials.api_key : 'Key generated (check response)';
                        $('#newApiKeyDisplay').text(newKey);
                        $('#regenerateKeyModal').modal('show');
                        loadConnections();
                    })
                    .fail(function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to regenerate API key';
                        showToast(msg, 'error');
                    });
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
                apiRequest('PUT', apiBase + '/' + id + '/suspend', { reason: 'Suspended by admin' })
                    .done(function() {
                        showToast('Connection suspended successfully', 'success');
                        loadConnections();
                    })
                    .fail(function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to suspend connection';
                        showToast(msg, 'error');
                    });
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
                apiRequest('PUT', apiBase + '/' + id + '/reactivate')
                    .done(function() {
                        showToast('Connection reactivated successfully', 'success');
                        loadConnections();
                    })
                    .fail(function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to reactivate connection';
                        showToast(msg, 'error');
                    });
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
                apiRequest('PUT', apiBase + '/' + id + '/convert-to-live')
                    .done(function(response) {
                        showToast('Connection converted to Live successfully', 'success');
                        if (response.data && response.data.credentials && response.data.credentials.api_key) {
                            $('#newApiKeyDisplay').text(response.data.credentials.api_key);
                            $('#regenerateKeyModal').modal('show');
                        }
                        loadConnections();
                    })
                    .fail(function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to convert to live';
                        showToast(msg, 'error');
                    });
            },
            'This will change the API endpoint to the production server.'
        );
    };
    
    // Edit Endpoints Modal Functions
    window.openEditEndpointsModal = function() {
        if (!currentConnection) return;
        
        document.getElementById('editBaseUrl').value = currentConnection.baseUrl || '';
        document.getElementById('editDlrUrl').value = currentConnection.dlrUrl || '';
        document.getElementById('editInboundUrl').value = currentConnection.inboundUrl || '';
        
        var modal = new bootstrap.Modal(document.getElementById('editEndpointsModal'));
        modal.show();
    };
    
    window.clearField = function(fieldId) {
        document.getElementById(fieldId).value = '';
    };
    
    window.saveEndpointsChanges = function() {
        if (!currentConnection) return;
        
        var dlrUrl = document.getElementById('editDlrUrl').value.trim();
        var inboundUrl = document.getElementById('editInboundUrl').value.trim();
        
        if (dlrUrl && !isValidUrl(dlrUrl)) {
            showToast('Invalid DLR Webhook URL format', 'error');
            return;
        }
        if (inboundUrl && !isValidUrl(inboundUrl)) {
            showToast('Invalid Inbound Webhook URL format', 'error');
            return;
        }
        
        apiRequest('PUT', apiBase + '/' + currentConnection.id + '/endpoints', {
            webhook_dlr_url: dlrUrl || null,
            webhook_inbound_url: inboundUrl || null
        })
        .done(function(response) {
            if (response.data) {
                currentConnection.dlrUrl = response.data.webhook_dlr_url || null;
                currentConnection.inboundUrl = response.data.webhook_inbound_url || null;
            }
            
            updateDrawerEndpoints();
            loadConnections();
            
            bootstrap.Modal.getInstance(document.getElementById('editEndpointsModal')).hide();
            showToast('Endpoints updated successfully', 'success');
        })
        .fail(function(xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update endpoints';
            showToast(msg, 'error');
        });
    };
    
    function updateDrawerEndpoints() {
        if (!currentConnection) return;
        document.getElementById('drawerBaseUrl').textContent = currentConnection.baseUrl || 'Not configured';
        document.getElementById('drawerDlrUrl').innerHTML = currentConnection.dlrUrl 
            ? currentConnection.dlrUrl 
            : '<span class="badge bg-secondary">Not configured</span>';
        document.getElementById('drawerInboundUrl').innerHTML = currentConnection.inboundUrl 
            ? currentConnection.inboundUrl 
            : '<span class="badge bg-secondary">Not configured</span>';
    }
    
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Edit Security Modal Functions
    window.openEditSecurityModal = function() {
        if (!currentConnection) return;
        
        var enabled = currentConnection.ipAllowList || false;
        document.getElementById('editIpAllowlistEnabled').checked = enabled;
        
        var ips = currentConnection.allowedIps || [];
        document.getElementById('editAllowedIps').value = ips.join('\n');
        
        toggleIpAllowlistSection();
        document.getElementById('ipValidationErrors').style.display = 'none';
        
        var modal = new bootstrap.Modal(document.getElementById('editSecurityModal'));
        modal.show();
    };
    
    window.toggleIpAllowlistSection = function() {
        var enabled = document.getElementById('editIpAllowlistEnabled').checked;
        document.getElementById('ipAllowlistSection').style.display = enabled ? 'block' : 'none';
    };
    
    window.addCurrentIp = function() {
        // In a real implementation, this would detect the current IP
        // For now, add a placeholder
        var textarea = document.getElementById('editAllowedIps');
        var currentIps = textarea.value.trim();
        var mockCurrentIp = '203.0.113.' + Math.floor(Math.random() * 255);
        textarea.value = currentIps ? currentIps + '\n' + mockCurrentIp : mockCurrentIp;
        showToast('Added IP: ' + mockCurrentIp, 'info');
    };
    
    window.clearAllIps = function() {
        document.getElementById('editAllowedIps').value = '';
    };
    
    window.saveSecurityChanges = function() {
        if (!currentConnection) return;
        
        var enabled = document.getElementById('editIpAllowlistEnabled').checked;
        var ipsText = document.getElementById('editAllowedIps').value.trim();
        
        var ips = [];
        var invalidIps = [];
        
        if (enabled && ipsText) {
            var lines = ipsText.split('\n').map(function(line) { return line.trim(); }).filter(Boolean);
            lines.forEach(function(ip) {
                if (isValidIpOrCidr(ip)) {
                    ips.push(ip);
                } else {
                    invalidIps.push(ip);
                }
            });
            
            if (invalidIps.length > 0) {
                document.getElementById('ipValidationErrors').style.display = 'block';
                document.getElementById('ipValidationErrorText').textContent = 'Invalid IP addresses: ' + invalidIps.join(', ');
                return;
            }
            
            if (ips.length === 0) {
                document.getElementById('ipValidationErrors').style.display = 'block';
                document.getElementById('ipValidationErrorText').textContent = 'Please add at least one IP address when enabling the allowlist';
                return;
            }
        }
        
        document.getElementById('ipValidationErrors').style.display = 'none';
        
        apiRequest('PUT', apiBase + '/' + currentConnection.id + '/security', {
            ip_allowlist_enabled: enabled,
            ip_allowlist: enabled ? ips : []
        })
        .done(function(response) {
            if (response.data) {
                currentConnection.ipAllowList = response.data.ip_allowlist_enabled || false;
                currentConnection.allowedIps = response.data.ip_allowlist || [];
            }
            
            updateDrawerSecurity();
            loadConnections();
            
            bootstrap.Modal.getInstance(document.getElementById('editSecurityModal')).hide();
            showToast('Security settings updated successfully', 'success');
        })
        .fail(function(xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update security settings';
            showToast(msg, 'error');
        });
    };
    
    function updateDrawerSecurity() {
        if (!currentConnection) return;
        
        var statusEl = document.getElementById('drawerIpAllowStatus');
        var listRowEl = document.getElementById('drawerIpListRow');
        var listEl = document.getElementById('drawerIpList');
        
        if (currentConnection.ipAllowList) {
            statusEl.innerHTML = '<span class="badge bg-success">Enabled</span>';
            listRowEl.style.display = 'flex';
            var ips = currentConnection.allowedIps || [];
            listEl.innerHTML = ips.length > 0 
                ? ips.map(function(ip) { return '<code class="me-1">' + ip + '</code>'; }).join(', ')
                : '<span class="text-muted">No IPs configured</span>';
        } else {
            statusEl.innerHTML = '<span class="badge bg-secondary">Disabled</span>';
            listRowEl.style.display = 'none';
        }
    }
    
    function isValidIpOrCidr(ip) {
        // IPv4 validation
        var ipv4Pattern = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        // IPv4 CIDR validation
        var cidrPattern = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/(?:[0-9]|[1-2][0-9]|3[0-2])$/;
        
        return ipv4Pattern.test(ip) || cidrPattern.test(ip);
    }
    
    console.log('[Admin API Connections] Module loaded - Global view of all customer API connections');
});
</script>
@endpush

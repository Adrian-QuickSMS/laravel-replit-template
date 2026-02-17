@extends('layouts.quicksms')

@section('title', 'API Connections')

@push('styles')
<style>
.integration-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}
.integration-tile:hover {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.05);
}
.integration-tile.selected {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.1);
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
    padding: 0.5rem 0.35rem;
    font-weight: 600;
    font-size: 0.75rem;
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
    padding: 0.5rem 0.35rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.8rem;
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
    color: var(--primary);
}
.dropdown {
    position: relative;
}
.dropdown .dropdown-menu {
    z-index: 9999 !important;
}
.table-dropdown-clone {
    position: fixed !important;
    z-index: 99999 !important;
    min-width: 160px;
    background: #fff;
    border: 1px solid rgba(0,0,0,0.15);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.175);
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
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Management</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">API Connections</a></li>
        </ol>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="card-title mb-0">API Connections</h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
                <a href="{{ route('management.api-connections.create') }}" class="btn btn-primary btn-sm">
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
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search by API name...">
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
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="live" id="statusLive"><label class="form-check-label small" for="statusLive">Active</label></div>
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
                                @if(isset($subAccounts) && count($subAccounts) > 0)
                                    @foreach($subAccounts as $sub)
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="{{ $sub->name }}" id="subAcc_{{ $sub->id }}"><label class="form-check-label small" for="subAcc_{{ $sub->id }}">{{ $sub->name }}</label></div>
                                    @endforeach
                                @else
                                    <div class="text-muted small py-1">No sub-accounts</div>
                                @endif
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
            
            <div class="api-table-container">
                <table class="api-table" id="apiConnectionsTable">
            <thead>
                <tr>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="viewDetailsDrawer" style="width: 480px;">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title mb-0"><i class="fas fa-plug me-2 text-primary"></i>API Connection Details</h6>
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
            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Connection Information</h6>
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
                    <h6 class="card-title mb-3"><i class="fas fa-key me-2 text-primary"></i>Authentication</h6>
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
                    <h6 class="card-title mb-3"><i class="fas fa-link me-2 text-primary"></i>Endpoints</h6>
                    <div class="row mb-2">
                        <div class="col-12 text-muted small mb-1">Dedicated Base URL</div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <code class="small flex-grow-1 text-break" id="drawerBaseUrl">-</code>
                                <button class="btn btn-sm btn-link text-muted p-0 ms-2" type="button" onclick="copyDrawerField('drawerBaseUrl')" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2 mt-3">
                        <div class="col-12 text-muted small mb-1">Delivery Report URL (Webhook)</div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <code class="small flex-grow-1 text-break" id="drawerDlrUrl">-</code>
                                <button class="btn btn-sm btn-link text-muted p-0 ms-2" type="button" onclick="copyDrawerField('drawerDlrUrl')" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-muted small mb-1">Inbound Message URL (Webhook)</div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <code class="small flex-grow-1 text-break" id="drawerInboundUrl">-</code>
                                <button class="btn btn-sm btn-link text-muted p-0 ms-2" type="button" onclick="copyDrawerField('drawerInboundUrl')" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-shield-alt me-2 text-primary"></i>Security</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">IP Allow List</div>
                        <div class="col-7 small" id="drawerIpAllowStatus">-</div>
                    </div>
                    <div class="row" id="drawerIpListRow" style="display: none;">
                        <div class="col-12 text-muted small mb-1">Allowed IPs</div>
                        <div class="col-12">
                            <div class="small" id="drawerIpList">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-clock me-2 text-primary"></i>Activity</h6>
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
                    <h6 class="card-title mb-3"><i class="fas fa-tasks me-2 text-primary"></i>Capabilities & Restrictions</h6>
                    <div id="drawerCapabilities"></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-project-diagram me-2 text-primary"></i>Dependencies</h6>
                    <div id="drawerDependencies">
                        <p class="text-muted small mb-0">No dependencies configured.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel"><i class="fas fa-lock me-2"></i>Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="changePasswordWarning" class="alert mb-3" style="background-color: rgba(255, 193, 7, 0.15); border: 1px solid rgba(255, 193, 7, 0.3); color: #856404; display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="changePasswordWarningText"></span>
                </div>
                
                <p class="text-muted small mb-3">Changing the password for: <strong id="changePasswordConnName"></strong></p>
                
                <form id="changePasswordForm" novalidate>
                    <input type="hidden" id="changePasswordConnId">
                    
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('currentPassword', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="currentPasswordError">Please enter your current password.</div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('newPassword', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="newPasswordError">Please enter a new password.</div>
                        <div class="form-text small mt-2">
                            <div id="pwdRuleLength" class="text-muted"><i class="fas fa-circle me-1" style="font-size: 6px;"></i> At least 12 characters</div>
                            <div id="pwdRuleUpper" class="text-muted"><i class="fas fa-circle me-1" style="font-size: 6px;"></i> At least one uppercase letter</div>
                            <div id="pwdRuleLower" class="text-muted"><i class="fas fa-circle me-1" style="font-size: 6px;"></i> At least one lowercase letter</div>
                            <div id="pwdRuleNumber" class="text-muted"><i class="fas fa-circle me-1" style="font-size: 6px;"></i> At least one number</div>
                            <div id="pwdRuleSpecial" class="text-muted"><i class="fas fa-circle me-1" style="font-size: 6px;"></i> At least one special character (!@#$%^&*)</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('confirmPassword', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="confirmPasswordError">Passwords do not match.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePasswordBtn" onclick="savePassword()">
                    <i class="fas fa-save me-1"></i> Save Password
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newKeyModal" tabindex="-1" aria-labelledby="newKeyModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="newKeyModalLabel"><i class="fas fa-key me-2 text-primary"></i>New API Key Generated</h5>
            </div>
            <div class="modal-body">
                <div class="alert" style="background-color: rgba(255, 193, 7, 0.15); border: 1px solid rgba(255, 193, 7, 0.3); color: #856404;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> This key will only be shown once. Copy it now and store it securely.
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Your New API Key</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" id="newApiKeyValue" readonly style="background-color: #f8f9fa;">
                        <button class="btn btn-outline-primary" type="button" id="copyNewKeyBtn" onclick="copyNewApiKey()">
                            <i class="fas fa-copy me-1"></i> Copy
                        </button>
                    </div>
                </div>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    The previous key has been revoked and is no longer valid.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="closeNewKeyModalBtn">
                    <i class="fas fa-check me-1"></i> I've Copied the Key
                </button>
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
                <div id="confirmModalWarning" class="alert mb-3" style="background-color: rgba(255, 193, 7, 0.15); border: 1px solid rgba(255, 193, 7, 0.3); color: #856404; display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="confirmModalWarningText"></span>
                </div>
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
<script src="{{ asset('js/table-dropdown-fix.js') }}"></script>
<script>
$(document).ready(function() {

    var apiConnections = [];
    
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
        if (status === 'live' || status === 'active') return 'badge-live-status';
        if (status === 'archived') return 'badge-suspended';
        return 'badge-suspended';
    }
    
    function getStatusLabel(status) {
        if (status === 'live' || status === 'active') return 'Active';
        if (status === 'suspended') return 'Suspended';
        if (status === 'archived') return 'Archived';
        if (status === 'draft') return 'Draft';
        return status;
    }
    
    function formatDate(dateStr) {
        var date = new Date(dateStr);
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return day + '-' + month + '-' + year;
    }
    
    function formatDateTime(dateTimeStr) {
        var date = new Date(dateTimeStr);
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');
        return day + '-' + month + '-' + year + ' ' + hours + ':' + minutes;
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
        loadConnections();
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
        loadConnections();
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
        var filtered = apiConnections.slice();
        
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
            var typeText = getTypeLabel(conn.type);
            if (conn.type === 'integration' && conn.integrationName) {
                typeText += ' <span class="text-muted small">(' + conn.integrationName + ')</span>';
            }
            html += typeText;
            html += '</td>';
            
            html += '<td>' + (conn.environment === 'live' ? 'Live' : 'Test') + '</td>';
            
            html += '<td>' + conn.authType + '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getStatusBadgeClass(conn.status) + '">' + 
                    getStatusLabel(conn.status) + '</span></td>';
            
            html += '<td>' + (conn.createdDate ? formatDate(conn.createdDate) : '-') + '</td>';
            
            html += '<td>' + (conn.lastUsed ? formatDateTime(conn.lastUsed) : 'Never') + '</td>';
            
            html += '<td>';
            html += '<div class="dropdown table-action-dropdown">';
            html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
            html += '<i class="fas fa-ellipsis-v"></i>';
            html += '</button>';
            html += '<ul class="dropdown-menu dropdown-menu-end">';
            
            html += '<li><a class="dropdown-item" href="#" onclick="viewConnection(\'' + conn.id + '\'); return false;"><i class="fas fa-eye me-2"></i>View Details</a></li>';
            
            if (conn.authType === 'API Key') {
                html += '<li><a class="dropdown-item" href="#" onclick="regenerateKey(\'' + conn.id + '\'); return false;"><i class="fas fa-sync-alt me-2"></i>Regenerate API Key</a></li>';
            }
            
            if (conn.authType === 'Basic Auth') {
                html += '<li><a class="dropdown-item" href="#" onclick="changePassword(\'' + conn.id + '\'); return false;"><i class="fas fa-key me-2"></i>Change Password</a></li>';
            }
            
            if (conn.status === 'live') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-warning" href="#" onclick="suspendConnection(\'' + conn.id + '\'); return false;"><i class="fas fa-pause me-2"></i>Suspend API</a></li>';
            }
            
            if (conn.status === 'suspended') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-success" href="#" onclick="reactivateConnection(\'' + conn.id + '\'); return false;"><i class="fas fa-play me-2"></i>Reactivate API</a></li>';
            }
            
            if (conn.environment === 'test' && !conn.archived) {
                html += '<li><a class="dropdown-item" href="#" onclick="convertToLive(\'' + conn.id + '\'); return false;"><i class="fas fa-rocket me-2"></i>Convert to Live</a></li>';
            }
            
            if (conn.status === 'suspended' && !conn.archived) {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-danger" href="#" onclick="archiveConnection(\'' + conn.id + '\'); return false;"><i class="fas fa-archive me-2"></i>Archive API</a></li>';
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
    
    // Search input - filter as user types (with debounce)
    var searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 300);
    });
    
    // Also trigger on Enter key for immediate search
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
    
    function getRecentUsageWarning(conn) {
        if (!conn.lastUsed) return null;
        
        var lastUsedDate = new Date(conn.lastUsed);
        var now = new Date();
        var diffMs = now - lastUsedDate;
        var diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        var diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        
        var threshold = 24;
        
        if (diffHours < threshold) {
            if (diffHours < 1) {
                return 'This API connection was used less than an hour ago.';
            } else if (diffHours === 1) {
                return 'This API connection was used 1 hour ago.';
            } else {
                return 'This API connection was used ' + diffHours + ' hours ago.';
            }
        } else if (diffDays <= 7) {
            if (diffDays === 1) {
                return 'This API connection was used 1 day ago.';
            } else {
                return 'This API connection was used ' + diffDays + ' days ago.';
            }
        }
        
        return null;
    }
    
    function getConnectionById(id) {
        return apiConnections.find(function(c) { return c.id == id; });
    }
    
    window.viewConnection = function(id) {
        var conn = getConnectionById(id);
        if (!conn) return;
        
        $('#drawerApiName').text(conn.name);
        $('#drawerDescription').text(conn.description || 'No description provided');
        
        // Set header badges with pastel styling matching table
        var typeLabel = getTypeLabel(conn.type);
        $('#drawerTypeBadge').removeClass().addClass('badge rounded-pill ' + getTypeBadgeClass(conn.type)).text(typeLabel);
        
        var envLabel = conn.environment === 'live' ? 'Live' : 'Test';
        $('#drawerEnvBadge').removeClass().addClass('badge rounded-pill ' + getEnvironmentBadgeClass(conn.environment)).text(envLabel);
        
        var statusLabel = getStatusLabel(conn.status);
        $('#drawerStatusBadge').removeClass().addClass('badge rounded-pill ' + getStatusBadgeClass(conn.status)).text(statusLabel);
        
        $('#drawerApiNameDetail').text(conn.name);
        $('#drawerSubAccount').text(conn.subAccount);
        $('#drawerType').text(typeLabel);
        $('#drawerEnvironment').text(envLabel);
        $('#drawerStatus').html('<span class="badge rounded-pill ' + getStatusBadgeClass(conn.status) + '">' + getStatusLabel(conn.status) + '</span>');
        
        $('#drawerAuthType').text(conn.authType);
        var maskedCred = conn.credentialDisplay || (conn.authType === 'API Key' ? 'sk_••••••••••••••••' : 'user:••••••••');
        $('#drawerCredentials').text(maskedCred);
        
        // URL fields now use <code> elements instead of inputs
        $('#drawerBaseUrl').text(conn.baseUrl);
        $('#drawerDlrUrl').text(conn.dlrUrl || 'Not configured');
        $('#drawerInboundUrl').text(conn.inboundUrl || 'Not configured');
        
        if (conn.ipAllowList) {
            $('#drawerIpAllowStatus').html('<span class="badge rounded-pill badge-on">Enabled</span>');
            $('#drawerIpListRow').show();
            // Use neutral styling for IPs (not red)
            var ipHtml = (conn.allowedIps && conn.allowedIps.length > 0) 
                ? conn.allowedIps.map(function(ip) { return '<span class="badge rounded-pill badge-bulk me-1 mb-1">' + ip + '</span>'; }).join('')
                : '<span class="text-muted">No IPs configured</span>';
            $('#drawerIpList').html(ipHtml);
        } else {
            $('#drawerIpAllowStatus').html('<span class="badge rounded-pill badge-off">Disabled</span>');
            $('#drawerIpListRow').hide();
        }
        
        $('#drawerCreatedDate').text(conn.createdDate ? formatDate(conn.createdDate) : '-');
        
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
            $('#drawerLastUsed').text('Never used');
        }
        
        var capabilitiesHtml = getCapabilitiesHtml(conn.type, conn.integrationName);
        $('#drawerCapabilities').html(capabilitiesHtml);
        
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
        var element = document.getElementById(fieldId);
        // Support both input elements and code/text elements
        var value = element.value !== undefined ? element.value : element.textContent;
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
    
    function getCapabilitiesHtml(type, integrationName) {
        var html = '';
        
        if (type === 'bulk') {
            html = '<div class="small">' +
                '<div class="mb-2"><strong>Bulk API</strong></div>' +
                '<p class="text-muted mb-2">Transport-only message submission. This API sends raw messages directly to the network with no platform intelligence. Designed for customers who handle compliance and logic externally.</p>' +
                '<div class="mb-3">' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Send individual messages</div>' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Send batch messages (up to 10,000)</div>' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Receive delivery reports</div>' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Receive inbound messages</div>' +
                '</div>' +
                '<div class="border-top pt-2">' +
                '<div class="fw-medium text-muted mb-2">Platform features not available:</div>' +
                '<div class="text-danger mb-1"><i class="fas fa-times-circle me-2"></i>No template support</div>' +
                '<div class="text-danger mb-1"><i class="fas fa-times-circle me-2"></i>No Contact Book access</div>' +
                '<div class="text-danger mb-1"><i class="fas fa-times-circle me-2"></i>No opt-out list enforcement</div>' +
                '<div class="text-danger mb-1"><i class="fas fa-times-circle me-2"></i>No personalisation placeholders</div>' +
                '<div class="text-danger mb-1"><i class="fas fa-times-circle me-2"></i>No campaign logic or scheduling</div>' +
                '</div>' +
                '</div>';
        } else if (type === 'campaign') {
            html = '<div class="small">' +
                '<div class="mb-2"><strong>Campaign API</strong></div>' +
                '<p class="text-muted mb-2">Full platform-aware messaging. This API behaves as if sending messages via the QuickSMS portal, with all platform governance enforced automatically.</p>' +
                '<div class="mb-3">' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Message templates</div>' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Contacts and lists</div>' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Opt-out list compliance (enforced)</div>' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Personalisation placeholders</div>' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Campaign scheduling</div>' +
                '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Analytics and reporting</div>' +
                '</div>' +
                '<div class="border-top pt-2">' +
                '<div class="fw-medium text-muted mb-2">Platform governance:</div>' +
                '<div class="text-info mb-1"><i class="fas fa-shield-alt me-2"></i>All compliance rules enforced</div>' +
                '<div class="text-info mb-1"><i class="fas fa-shield-alt me-2"></i>Opt-out validation required</div>' +
                '<div class="text-info mb-1"><i class="fas fa-shield-alt me-2"></i>Rate limiting applied</div>' +
                '</div>' +
                '</div>';
        } else if (type === 'integration') {
            var partnerInfo = getIntegrationPartnerInfo(integrationName);
            html = '<div class="small">' +
                '<div class="mb-2"><strong>Integration API</strong>' + (integrationName ? ' — ' + integrationName : '') + '</div>' +
                '<p class="text-muted mb-2">QuickSMS-managed integration with third-party systems. This API is opinionated, versioned, and maintained by QuickSMS. Users configure but do not design these integrations.</p>' +
                '<div class="mb-3">' +
                partnerInfo +
                '</div>' +
                '<div class="border-top pt-2">' +
                '<div class="fw-medium text-muted mb-2">Integration characteristics:</div>' +
                '<div class="text-info mb-1"><i class="fas fa-cog me-2"></i>Managed by QuickSMS</div>' +
                '<div class="text-info mb-1"><i class="fas fa-code-branch me-2"></i>Versioned API</div>' +
                '<div class="text-info mb-1"><i class="fas fa-sync-alt me-2"></i>Partner-specific payloads</div>' +
                '</div>' +
                '</div>';
        } else {
            html = '<p class="text-muted small mb-0">No capability information available.</p>';
        }
        
        return html;
    }
    
    function getIntegrationPartnerInfo(partnerName) {
        var info = {
            'SystmOne': '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Patient appointment reminders</div>' +
                        '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Prescription notifications</div>',
            'Rio': '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Mental health appointment alerts</div>' +
                   '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Care plan updates</div>',
            'EMIS': '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>GP practice notifications</div>' +
                    '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Test result alerts</div>',
            'Accurx': '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Video consultation invites</div>' +
                      '<div class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Patient messaging</div>'
        };
        return info[partnerName] || '';
    }
    
    window.regenerateKey = function(id) {
        var conn = getConnectionById(id);
        var warning = getRecentUsageWarning(conn);
        
        showConfirmModal(
            'Regenerate API Key - Step 1 of 2',
            'Regenerating this key will immediately revoke the old key. Any systems using the old key will stop working. Are you sure you want to continue?',
            'Continue',
            'btn-primary',
            function() {
                showConfirmModal(
                    'Regenerate API Key - Final Confirmation',
                    'This action cannot be undone. The current API key for "' + conn.name + '" will be permanently revoked. Please confirm you want to proceed.',
                    'Regenerate Key Now',
                    'btn-danger',
                    function() {
                        generateAndShowNewKey(conn);
                    },
                    null
                );
            },
            warning
        );
    };
    
    function generateAndShowNewKey(conn) {
        apiRequest(API_BASE + '/' + conn.id + '/regenerate-key', 'POST')
            .done(function(response) {
                var newKey = response.credentials ? response.credentials.api_key : null;
                if (!newKey) {
                    showErrorToast('Server did not return a new API key. Please contact support.');
                    return;
                }
                
                loadConnections();
                
                $('#newApiKeyValue').val(newKey);
                $('#copyNewKeyBtn').html('<i class="fas fa-copy me-1"></i> Copy');
                
                $('#closeNewKeyModalBtn').off('click').on('click', function() {
                    $('#newApiKeyValue').val('');
                    $('#newKeyModal').modal('hide');
                    showSuccessToast('API Key for "' + conn.name + '" has been regenerated. The old key is now revoked.');
                });
                
                setTimeout(function() {
                    $('#newKeyModal').modal('show');
                }, 150);
            })
            .fail(function(xhr) {
                var msg = 'Failed to regenerate API key. Please try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                showErrorToast(msg);
            });
    }
    
    window.copyNewApiKey = function() {
        var keyInput = document.getElementById('newApiKeyValue');
        var value = keyInput.value;
        if (value) {
            navigator.clipboard.writeText(value).then(function() {
                $('#copyNewKeyBtn').html('<i class="fas fa-check me-1"></i> Copied!');
                setTimeout(function() {
                    $('#copyNewKeyBtn').html('<i class="fas fa-copy me-1"></i> Copy');
                }, 2000);
            });
        }
    };
    
    window.changePassword = function(id) {
        var conn = getConnectionById(id);
        var warning = getRecentUsageWarning(conn);
        
        $('#changePasswordForm')[0].reset();
        $('#changePasswordForm .is-invalid').removeClass('is-invalid');
        resetPasswordRules();
        
        $('#changePasswordConnId').val(conn.id);
        $('#changePasswordConnName').text(conn.name);
        
        if (warning) {
            $('#changePasswordWarningText').text(warning);
            $('#changePasswordWarning').show();
        } else {
            $('#changePasswordWarning').hide();
        }
        
        $('#changePasswordModal').modal('show');
    };
    
    window.togglePasswordVisibility = function(fieldId, btn) {
        var input = document.getElementById(fieldId);
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };
    
    function resetPasswordRules() {
        $('#pwdRuleLength, #pwdRuleUpper, #pwdRuleLower, #pwdRuleNumber, #pwdRuleSpecial')
            .removeClass('text-success text-danger')
            .addClass('text-muted')
            .find('i').removeClass('fa-check fa-times').addClass('fa-circle');
    }
    
    function validatePasswordRules(password) {
        var rules = {
            length: password.length >= 12,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*]/.test(password)
        };
        
        updateRuleDisplay('pwdRuleLength', rules.length);
        updateRuleDisplay('pwdRuleUpper', rules.upper);
        updateRuleDisplay('pwdRuleLower', rules.lower);
        updateRuleDisplay('pwdRuleNumber', rules.number);
        updateRuleDisplay('pwdRuleSpecial', rules.special);
        
        return rules.length && rules.upper && rules.lower && rules.number && rules.special;
    }
    
    function updateRuleDisplay(ruleId, passed) {
        var $rule = $('#' + ruleId);
        var $icon = $rule.find('i');
        
        $rule.removeClass('text-muted text-success text-danger');
        $icon.removeClass('fa-circle fa-check fa-times');
        
        if (passed) {
            $rule.addClass('text-success');
            $icon.addClass('fa-check');
        } else {
            $rule.addClass('text-danger');
            $icon.addClass('fa-times');
        }
    }
    
    $('#newPassword').on('input', function() {
        validatePasswordRules($(this).val());
    });
    
    window.savePassword = function() {
        var currentPwd = $('#currentPassword').val().trim();
        var newPwd = $('#newPassword').val();
        var confirmPwd = $('#confirmPassword').val();
        var connId = $('#changePasswordConnId').val();
        var conn = getConnectionById(connId);
        
        var isValid = true;
        
        $('#currentPassword, #newPassword, #confirmPassword').removeClass('is-invalid');
        
        if (!currentPwd) {
            $('#currentPassword').addClass('is-invalid');
            $('#currentPasswordError').text('Please enter your current password.');
            isValid = false;
        }
        
        if (!newPwd) {
            $('#newPassword').addClass('is-invalid');
            $('#newPasswordError').text('Please enter a new password.');
            isValid = false;
        } else if (!validatePasswordRules(newPwd)) {
            $('#newPassword').addClass('is-invalid');
            $('#newPasswordError').text('Password does not meet all requirements.');
            isValid = false;
        }
        
        if (newPwd !== confirmPwd) {
            $('#confirmPassword').addClass('is-invalid');
            $('#confirmPasswordError').text('Passwords do not match.');
            isValid = false;
        }
        
        if (newPwd === currentPwd && currentPwd) {
            $('#newPassword').addClass('is-invalid');
            $('#newPasswordError').text('New password must be different from current password.');
            isValid = false;
        }
        
        if (!isValid) return;
        
        apiRequest(API_BASE + '/' + connId + '/change-password', 'POST')
            .done(function(response) {
                $('#changePasswordModal').modal('hide');
                
                if (response.credentials) {
                    var credMsg = 'New password: ' + response.credentials.password;
                    alert('Password changed! Username: ' + response.credentials.username + '\n' + credMsg + '\n\nPlease copy these credentials now. They will not be shown again.');
                }
                
                showSuccessToast('Password changed successfully for "' + conn.name + '".');
                loadConnections();
            })
            .fail(function(xhr) {
                var msg = 'Failed to change password.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                showErrorToast(msg);
            });
    };
    
    function showSuccessToast(message) {
        var toastHtml = '<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">' +
            '<div class="toast show align-items-center text-white bg-success border-0" role="alert">' +
            '<div class="d-flex">' +
            '<div class="toast-body"><i class="fas fa-check-circle me-2"></i>' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div></div></div>';
        
        var $toast = $(toastHtml).appendTo('body');
        setTimeout(function() {
            $toast.fadeOut(function() { $toast.remove(); });
        }, 3000);
    }
    
    function showErrorToast(message) {
        var toastHtml = '<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">' +
            '<div class="toast show align-items-center text-white bg-danger border-0" role="alert">' +
            '<div class="d-flex">' +
            '<div class="toast-body"><i class="fas fa-exclamation-circle me-2"></i>' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div></div></div>';
        
        var $toast = $(toastHtml).appendTo('body');
        setTimeout(function() {
            $toast.fadeOut(function() { $toast.remove(); });
        }, 5000);
    }
    
    var API_BASE = '/api/api-connections';
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    var csrfValue = csrfToken ? csrfToken.getAttribute('content') : '';

    function apiRequest(url, method, data) {
        var options = {
            url: url,
            method: method,
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfValue, 'Accept': 'application/json' }
        };
        if (data) options.data = JSON.stringify(data);
        return $.ajax(options);
    }

    function loadConnections() {
        var params = { show_archived: appliedFilters.showArchived ? 1 : 0 };
        if (appliedFilters.search) params.search = appliedFilters.search;
        if (appliedFilters.types.length) params['type[]'] = appliedFilters.types;
        if (appliedFilters.environments.length) params['environment[]'] = appliedFilters.environments;
        if (appliedFilters.statuses.length) {
            params['status[]'] = appliedFilters.statuses.map(function(s) { return s === 'live' ? 'active' : s; });
        }
        if (appliedFilters.authTypes.length) {
            params['auth_type[]'] = appliedFilters.authTypes.map(function(a) { return a === 'API Key' ? 'api_key' : (a === 'Basic Auth' ? 'basic_auth' : a); });
        }

        $.ajax({
            url: API_BASE,
            method: 'GET',
            data: params,
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                apiConnections = (response.data || []).map(mapApiToLocal);
                renderTable();
            },
            error: function() {
                apiConnections = [];
                renderTable();
                showErrorToast('Failed to load API connections.');
            }
        });
    }

    function mapApiToLocal(c) {
        return {
            id: c.id,
            name: c.name,
            description: c.description,
            subAccount: c.sub_account_name || 'Main Account',
            sub_account_id: c.sub_account_id,
            type: c.type,
            integrationName: c.partner_name,
            environment: c.environment,
            authType: c.auth_type === 'api_key' ? 'API Key' : (c.auth_type === 'basic_auth' ? 'Basic Auth' : c.auth_type),
            authTypeRaw: c.auth_type,
            status: c.status === 'active' ? 'live' : c.status,
            statusRaw: c.status,
            baseUrl: '',
            dlrUrl: c.webhook_dlr_url || '',
            inboundUrl: c.webhook_inbound_url || '',
            ipAllowList: c.ip_allowlist_enabled,
            allowedIps: c.ip_allowlist || [],
            credentialDisplay: c.credential_display,
            createdDate: c.created_at,
            lastUsed: c.last_used_at,
            archived: c.status === 'archived',
            dependencies: [],
            rate_limit_per_minute: c.rate_limit_per_minute,
            capabilities: c.capabilities,
            created_by: c.created_by,
            suspended_reason: c.suspended_reason
        };
    }
    
    window.suspendConnection = function(id) {
        var conn = getConnectionById(id);
        var warning = getRecentUsageWarning(conn);
        
        showConfirmModal(
            'Suspend API Connection - Step 1 of 2',
            'Suspending "' + conn.name + '" will immediately block all API traffic. Any systems using this connection will stop working.',
            'Continue',
            'btn-primary',
            function() {
                showConfirmModal(
                    'Suspend API Connection - Final Confirmation',
                    'Please confirm you want to suspend "' + conn.name + '". All API requests will be rejected immediately.',
                    'Suspend Now',
                    'btn-danger',
                    function() {
                        apiRequest(API_BASE + '/' + id + '/suspend', 'PUT', { reason: 'Suspended by user' })
                            .done(function(response) {
                                showSuccessToast('API Connection "' + conn.name + '" has been suspended.');
                                loadConnections();
                            })
                            .fail(function(xhr) {
                                var msg = 'Failed to suspend connection.';
                                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                                showErrorToast(msg);
                            });
                    },
                    null
                );
            },
            warning
        );
    };
    
    window.reactivateConnection = function(id) {
        var conn = getConnectionById(id);
        
        if (conn.status === 'live') {
            showErrorToast('This connection is already active.');
            return;
        }
        
        showConfirmModal(
            'Reactivate API Connection - Step 1 of 2',
            'Reactivating "' + conn.name + '" will restore API access. The connection will immediately start accepting requests again.',
            'Continue',
            'btn-primary',
            function() {
                showConfirmModal(
                    'Reactivate API Connection - Final Confirmation',
                    'Please confirm you want to reactivate "' + conn.name + '". API traffic will be allowed immediately.',
                    'Reactivate Now',
                    'btn-primary',
                    function() {
                        apiRequest(API_BASE + '/' + id + '/reactivate', 'PUT')
                            .done(function(response) {
                                showSuccessToast('API Connection "' + conn.name + '" has been reactivated.');
                                loadConnections();
                            })
                            .fail(function(xhr) {
                                var msg = 'Failed to reactivate connection.';
                                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                                showErrorToast(msg);
                            });
                    },
                    null
                );
            },
            null
        );
    };
    
    window.convertToLive = function(id) {
        var conn = getConnectionById(id);
        
        if (conn.environment === 'live') {
            showErrorToast('This connection is already in the Live environment.');
            return;
        }
        
        showConfirmModal(
            'Convert to Live Environment - Step 1 of 2',
            'You are about to convert "' + conn.name + '" from a Test (sandbox) environment to a Live (production) environment.',
            'Continue',
            'btn-primary',
            function() {
                var detailedMessage = 'Please confirm you want to convert "' + conn.name + '" to Live.\n\n' +
                    'Key differences:\n' +
                    '• Live connections process real messages and incur charges\n' +
                    '• Sandbox testing features will no longer be available\n' +
                    '• This action is permanent and cannot be reversed';
                
                showConfirmModalWithDetails(
                    'Convert to Live - Final Confirmation',
                    detailedMessage,
                    'Convert to Live Now',
                    'btn-primary',
                    function() {
                        apiRequest(API_BASE + '/' + id + '/convert-to-live', 'PUT')
                            .done(function(response) {
                                showSuccessToast('API Connection "' + conn.name + '" has been converted to Live environment.');
                                if (response.data && response.data.credentials) {
                                    $('#newApiKeyValue').val(response.data.credentials.api_key);
                                    $('#copyNewKeyBtn').html('<i class="fas fa-copy me-1"></i> Copy');
                                    $('#closeNewKeyModalBtn').off('click').on('click', function() {
                                        $('#newApiKeyValue').val('');
                                        $('#newKeyModal').modal('hide');
                                    });
                                    setTimeout(function() { $('#newKeyModal').modal('show'); }, 150);
                                }
                                loadConnections();
                            })
                            .fail(function(xhr) {
                                var msg = 'Failed to convert connection to Live.';
                                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                                showErrorToast(msg);
                            });
                    }
                );
            },
            null
        );
    };
    
    function showConfirmModalWithDetails(title, message, confirmText, confirmClass, onConfirm) {
        $('#confirmModalLabel').text(title);
        
        var formattedMessage = message.replace(/\n/g, '<br>');
        $('#confirmModalMessage').html(formattedMessage);
        
        var permanentWarning = '<div class="alert mt-3 mb-0" style="background-color: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); color: #721c24;">' +
            '<i class="fas fa-exclamation-circle me-2"></i>' +
            '<strong>This action is permanent and cannot be undone.</strong>' +
            '</div>';
        $('#confirmModalMessage').append(permanentWarning);
        
        $('#confirmModalWarning').hide();
        $('#confirmModalBtn').text(confirmText).removeClass('btn-danger btn-warning btn-primary btn-success').addClass(confirmClass);
        $('#confirmModalBtn').off('click').on('click', function() {
            pendingConfirmCallback = onConfirm;
            $('#confirmModal').modal('hide');
        });
        $('#confirmModal').modal('show');
    }
    
    window.archiveConnection = function(id) {
        var conn = getConnectionById(id);
        var warning = getRecentUsageWarning(conn);
        
        showConfirmModal(
            'Archive API Connection - Step 1 of 2',
            'You are about to archive "' + conn.name + '". Archived connections cannot be used or modified, but are retained for audit purposes.',
            'Continue',
            'btn-primary',
            function() {
                showConfirmModalWithDetails(
                    'Archive API Connection - Final Confirmation',
                    'Please confirm you want to archive "' + conn.name + '".\n\nArchived connections:\n• Cannot accept API requests\n• Cannot be modified\n• Hidden from default view\n• Retained for audit trail\n\nYou can view archived connections using the "Show Archived" toggle.',
                    'Archive Now',
                    'btn-danger',
                    function() {
                        apiRequest(API_BASE + '/' + id + '/archive', 'PUT')
                            .done(function(response) {
                                showSuccessToast('API Connection "' + conn.name + '" has been archived. Use "Show Archived" to view it.');
                                loadConnections();
                            })
                            .fail(function(xhr) {
                                var msg = 'Failed to archive connection.';
                                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                                showErrorToast(msg);
                            });
                    }
                );
            },
            warning
        );
    };
    
    loadConnections();
});
</script>
@endpush

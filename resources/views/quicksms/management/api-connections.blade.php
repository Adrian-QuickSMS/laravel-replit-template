@extends('layouts.quicksms')

@section('title', 'API Connections')

@push('styles')
<link href="{{ asset('vendor/jquery-smartwizard/dist/css/smart_wizard.min.css') }}" rel="stylesheet">
<style>
.wizard-modal-fullscreen .modal-dialog {
    max-width: 100%;
    margin: 0;
    height: 100%;
}
.wizard-modal-fullscreen .modal-content {
    height: 100%;
    border: 0;
    border-radius: 0;
}
.wizard-modal-fullscreen .modal-header {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    color: #fff;
    border-radius: 0;
    padding: 1rem 2rem;
}
.wizard-modal-fullscreen .modal-header .btn-close {
    filter: brightness(0) invert(1);
}
.wizard-modal-fullscreen .modal-body {
    padding: 2rem;
    overflow-y: auto;
    background: #f8f9fa;
}
.wizard-modal-fullscreen .wizard-container {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    border-radius: 0.75rem;
    padding: 2rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}
.api-wizard .nav-wizard {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin-bottom: 2rem;
    gap: 0;
}
.api-wizard .nav-wizard li {
    flex: 1;
    max-width: 140px;
}
.api-wizard .nav-wizard li .nav-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #6c757d;
    padding: 0;
    background: transparent !important;
    border: none !important;
    position: relative;
}
.api-wizard .nav-wizard li .nav-link .step-number {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    border: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    background: #fff;
    color: #6c757d;
    z-index: 1;
    transition: all 0.2s ease;
}
.api-wizard .nav-wizard li .nav-link .step-title {
    font-size: 0.75rem;
    margin-top: 0.5rem;
    text-align: center;
}
.api-wizard .nav-wizard li .nav-link:after {
    position: absolute;
    top: 1.25rem;
    left: 50%;
    height: 2px;
    background: #e9ecef;
    content: "";
    z-index: 0;
    width: 100%;
}
.api-wizard .nav-wizard li:last-child .nav-link:after {
    display: none;
}
.api-wizard .nav-wizard li .nav-link.active .step-number,
.api-wizard .nav-wizard li .nav-link.done .step-number {
    background: var(--primary, #886CC0);
    color: #fff;
    border-color: var(--primary, #886CC0);
}
.api-wizard .nav-wizard li .nav-link.done:after {
    background: var(--primary, #886CC0);
}
.api-wizard .step-content {
    min-height: 300px;
}
.api-wizard .wizard-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
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
                    <h6 class="text-muted mb-3"><i class="fas fa-tasks me-2"></i>Capabilities & Restrictions</h6>
                    <div id="drawerCapabilities"></div>
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

<div class="modal fade wizard-modal-fullscreen" id="createApiWizardModal" tabindex="-1" aria-labelledby="createApiWizardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="createApiWizardModalLabel"><i class="fas fa-plug me-2"></i>Create API Connection</h5>
                    <small class="opacity-75">Configure a new API connection for your account</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="wizard-container">
                    <div class="api-wizard" id="apiConnectionWizard">
                        <ul class="nav-wizard mb-4">
                            <li><a href="#step-1" class="nav-link active"><span class="step-number">1</span><span class="step-title">Configuration</span></a></li>
                            <li><a href="#step-2" class="nav-link"><span class="step-number">2</span><span class="step-title">Type</span></a></li>
                            <li><a href="#step-3" class="nav-link"><span class="step-number">3</span><span class="step-title">Authentication</span></a></li>
                            <li><a href="#step-4" class="nav-link"><span class="step-number">4</span><span class="step-title">Security</span></a></li>
                            <li><a href="#step-5" class="nav-link"><span class="step-number">5</span><span class="step-title">Callbacks</span></a></li>
                            <li><a href="#step-6" class="nav-link"><span class="step-number">6</span><span class="step-title">Complete</span></a></li>
                        </ul>
                        
                        <div class="step-content">
                            <div id="step-1" class="step-pane active">
                                <h5 class="mb-4"><i class="fas fa-cog me-2 text-primary"></i>Core Configuration</h5>
                                <div class="mb-3">
                                    <label for="wizardApiName" class="form-label">API Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="wizardApiName" placeholder="e.g., Production Messaging API" maxlength="100">
                                    <div class="form-text">A unique, descriptive name for this connection.</div>
                                    <div class="invalid-feedback">API name is required and must be unique.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="wizardDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="wizardDescription" rows="2" placeholder="Optional description of this API connection"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="wizardSubAccount" class="form-label">Sub-Account <span class="text-danger">*</span></label>
                                        <select class="form-select" id="wizardSubAccount">
                                            <option value="">Select sub-account...</option>
                                            <option value="Main Account">Main Account</option>
                                            <option value="Marketing">Marketing</option>
                                            <option value="Development">Development</option>
                                            <option value="Sales">Sales</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a sub-account.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="wizardEnvironment" class="form-label">Environment <span class="text-danger">*</span></label>
                                        <select class="form-select" id="wizardEnvironment">
                                            <option value="test">Test (Sandbox)</option>
                                            <option value="live">Live (Production)</option>
                                        </select>
                                        <div class="form-text">Test environments do not incur charges.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-2" class="step-pane" style="display: none;">
                                <h5 class="mb-4"><i class="fas fa-sitemap me-2 text-primary"></i>Connection Type</h5>
                                <div class="mb-3">
                                    <label class="form-label">Select API Type <span class="text-danger">*</span></label>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="integration-tile" data-type="bulk" onclick="selectApiType('bulk')">
                                                <div class="tile-icon bg-pastel-primary"><i class="fas fa-paper-plane"></i></div>
                                                <div class="tile-name">Bulk API</div>
                                                <div class="tile-desc">Transport-only. No platform intelligence.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="integration-tile" data-type="campaign" onclick="selectApiType('campaign')">
                                                <div class="tile-icon bg-pastel-warning"><i class="fas fa-bullhorn"></i></div>
                                                <div class="tile-name">Campaign API</div>
                                                <div class="tile-desc">Full platform-aware messaging.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="integration-tile" data-type="integration" onclick="selectApiType('integration')">
                                                <div class="tile-icon bg-pastel-info"><i class="fas fa-plug"></i></div>
                                                <div class="tile-name">Integration</div>
                                                <div class="tile-desc">QuickSMS-managed connectors.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback d-block" id="apiTypeError" style="display: none !important;">Please select an API type.</div>
                                </div>
                                <div id="integrationSelector" style="display: none;">
                                    <label class="form-label mt-3">Select Integration Partner</label>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="integration-tile integration-partner" data-partner="SystmOne" onclick="selectIntegrationPartner('SystmOne')">
                                                <div class="tile-icon bg-pastel-success"><i class="fas fa-hospital"></i></div>
                                                <div class="tile-name">SystmOne</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="integration-tile integration-partner" data-partner="Rio" onclick="selectIntegrationPartner('Rio')">
                                                <div class="tile-icon bg-pastel-info"><i class="fas fa-notes-medical"></i></div>
                                                <div class="tile-name">Rio</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="integration-tile integration-partner" data-partner="EMIS" onclick="selectIntegrationPartner('EMIS')">
                                                <div class="tile-icon bg-pastel-primary"><i class="fas fa-clinic-medical"></i></div>
                                                <div class="tile-name">EMIS</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="integration-tile integration-partner" data-partner="Accurx" onclick="selectIntegrationPartner('Accurx')">
                                                <div class="tile-icon bg-pastel-warning"><i class="fas fa-stethoscope"></i></div>
                                                <div class="tile-name">Accurx</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-3" class="step-pane" style="display: none;">
                                <h5 class="mb-4"><i class="fas fa-key me-2 text-primary"></i>Authentication</h5>
                                <div class="alert" style="background-color: rgba(136, 108, 192, 0.1); border: 1px solid rgba(136, 108, 192, 0.3); color: #5a32a3;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> Authentication method cannot be changed after creation.
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Authentication Method <span class="text-danger">*</span></label>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="integration-tile auth-tile" data-auth="API Key" onclick="selectAuthType('API Key')">
                                                <div class="tile-icon bg-pastel-primary"><i class="fas fa-key"></i></div>
                                                <div class="tile-name">API Key</div>
                                                <div class="tile-desc">Simple token-based auth</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="integration-tile auth-tile" data-auth="Basic Auth" onclick="selectAuthType('Basic Auth')">
                                                <div class="tile-icon bg-pastel-success"><i class="fas fa-user-lock"></i></div>
                                                <div class="tile-name">Basic Auth</div>
                                                <div class="tile-desc">Username & password</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="integration-tile auth-tile disabled" style="opacity: 0.5; cursor: not-allowed;">
                                                <div class="tile-icon bg-pastel-secondary"><i class="fas fa-shield-alt"></i></div>
                                                <div class="tile-name">OAuth 2.0</div>
                                                <div class="tile-desc">Coming soon</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback d-block" id="authTypeError" style="display: none !important;">Please select an authentication method.</div>
                                </div>
                            </div>
                            
                            <div id="step-4" class="step-pane" style="display: none;">
                                <h5 class="mb-4"><i class="fas fa-shield-alt me-2 text-primary"></i>Security Controls</h5>
                                <div class="mb-3">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="wizardEnableIpRestriction">
                                        <label class="form-check-label" for="wizardEnableIpRestriction">Enable IP Address Restriction</label>
                                    </div>
                                    <div id="ipRestrictionFields" style="display: none;">
                                        <label for="wizardAllowedIps" class="form-label">Allowed IP Addresses</label>
                                        <textarea class="form-control ip-list-input" id="wizardAllowedIps" placeholder="Enter one IP address or CIDR range per line&#10;e.g., 192.168.1.100&#10;10.0.0.0/24&#10;2001:db8::1"></textarea>
                                        <div class="form-text">Supports IPv4, IPv6, and CIDR notation. Leave empty to allow all IPs.</div>
                                    </div>
                                </div>
                                <div class="alert alert-light border mt-4">
                                    <i class="fas fa-lightbulb me-2 text-warning"></i>
                                    <strong>Tip:</strong> IP restrictions add an extra layer of security by only allowing requests from specified addresses.
                                </div>
                            </div>
                            
                            <div id="step-5" class="step-pane" style="display: none;">
                                <h5 class="mb-4"><i class="fas fa-exchange-alt me-2 text-primary"></i>Callback Configuration</h5>
                                <div class="mb-4">
                                    <label for="wizardDlrUrl" class="form-label">Delivery Reports URL (Webhook)</label>
                                    <input type="url" class="form-control" id="wizardDlrUrl" placeholder="https://your-server.com/webhooks/delivery">
                                    <div class="form-text">We'll send delivery status updates to this URL. Must use HTTPS.</div>
                                    <div class="invalid-feedback">URL must start with https://</div>
                                </div>
                                <div class="mb-3">
                                    <label for="wizardInboundUrl" class="form-label">Inbound Message URL (Webhook)</label>
                                    <input type="url" class="form-control" id="wizardInboundUrl" placeholder="https://your-server.com/webhooks/inbound">
                                    <div class="form-text">We'll forward incoming messages to this URL. Must use HTTPS.</div>
                                    <div class="invalid-feedback">URL must start with https://</div>
                                </div>
                                <div class="alert alert-light border">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                    Callback URLs are optional. You can configure them later from the connection settings.
                                </div>
                            </div>
                            
                            <div id="step-6" class="step-pane" style="display: none;">
                                <div class="text-center mb-4">
                                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                        <i class="fas fa-check fa-2x text-white"></i>
                                    </div>
                                    <h5 class="mt-3 mb-1">API Connection Created!</h5>
                                    <p class="text-muted">Your new connection is ready to use.</p>
                                </div>
                                
                                <div class="alert" style="background-color: rgba(255, 193, 7, 0.15); border: 1px solid rgba(255, 193, 7, 0.3); color: #856404;">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> Copy your credentials now. They will only be shown once.
                                </div>
                                
                                <div class="credentials-display mb-4">
                                    <div class="credential-row">
                                        <span class="credential-label">Base URL</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <code class="credential-value" id="createdBaseUrl">-</code>
                                            <button class="btn btn-sm btn-outline-primary" onclick="copyCreatedField('createdBaseUrl')"><i class="fas fa-copy"></i></button>
                                        </div>
                                    </div>
                                    <div class="credential-row" id="createdApiKeyRow">
                                        <span class="credential-label">API Key</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <code class="credential-value" id="createdApiKey">-</code>
                                            <button class="btn btn-sm btn-outline-primary" onclick="copyCreatedField('createdApiKey')"><i class="fas fa-copy"></i></button>
                                        </div>
                                    </div>
                                    <div class="credential-row" id="createdUsernameRow" style="display: none;">
                                        <span class="credential-label">Username</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <code class="credential-value" id="createdUsername">-</code>
                                            <button class="btn btn-sm btn-outline-primary" onclick="copyCreatedField('createdUsername')"><i class="fas fa-copy"></i></button>
                                        </div>
                                    </div>
                                    <div class="credential-row" id="createdPasswordRow" style="display: none;">
                                        <span class="credential-label">Password</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <code class="credential-value" id="createdPassword">-</code>
                                            <button class="btn btn-sm btn-outline-primary" onclick="copyCreatedField('createdPassword')"><i class="fas fa-copy"></i></button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-muted small">
                                    <i class="fas fa-history me-1"></i> Connection created and logged for audit purposes.
                                </div>
                            </div>
                        </div>
                        
                        <div class="wizard-footer">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" id="wizardSaveDraft" onclick="saveDraft()">
                                    <i class="fas fa-save me-1"></i> Save Draft
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary" id="wizardPrevBtn" onclick="wizardPrev()" style="display: none;">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary" id="wizardNextBtn" onclick="wizardNext()">
                                    Next <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                                <button type="button" class="btn btn-success" id="wizardFinishBtn" onclick="closeWizard()" style="display: none;">
                                    <i class="fas fa-check me-1"></i> Done
                                </button>
                            </div>
                        </div>
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
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="newKeyModalLabel"><i class="fas fa-key me-2"></i>New API Key Generated</h5>
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
            $('#confirmModal').modal('hide');
            onConfirm();
        });
        $('#confirmModal').modal('show');
    }
    
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
        return apiConnections.find(function(c) { return c.id === id; });
    }
    
    var wizardCurrentStep = 1;
    var wizardTotalSteps = 6;
    var wizardData = {
        name: '',
        description: '',
        subAccount: '',
        environment: 'test',
        type: '',
        integrationName: null,
        authType: '',
        ipAllowList: false,
        allowedIps: [],
        dlrUrl: '',
        inboundUrl: ''
    };
    var wizardDraft = null;
    
    window.createApiConnection = function() {
        resetWizard();
        
        if (wizardDraft) {
            if (confirm('You have a saved draft. Would you like to resume?')) {
                loadDraft();
            }
        }
        
        $('#createApiWizardModal').modal('show');
    };
    
    function resetWizard() {
        wizardCurrentStep = 1;
        wizardData = {
            name: '',
            description: '',
            subAccount: '',
            environment: 'test',
            type: '',
            integrationName: null,
            authType: '',
            ipAllowList: false,
            allowedIps: [],
            dlrUrl: '',
            inboundUrl: ''
        };
        
        $('#wizardApiName').val('').removeClass('is-invalid');
        $('#wizardDescription').val('');
        $('#wizardSubAccount').val('').removeClass('is-invalid');
        $('#wizardEnvironment').val('test');
        $('#wizardDlrUrl').val('').removeClass('is-invalid');
        $('#wizardInboundUrl').val('').removeClass('is-invalid');
        $('#wizardAllowedIps').val('');
        $('#wizardEnableIpRestriction').prop('checked', false);
        $('#ipRestrictionFields').hide();
        
        $('.integration-tile').removeClass('selected');
        $('.auth-tile').removeClass('selected');
        $('.integration-partner').removeClass('selected');
        $('#integrationSelector').hide();
        $('#apiTypeError, #authTypeError').css('display', 'none !important');
        
        updateWizardStep(1);
    }
    
    function updateWizardStep(step) {
        wizardCurrentStep = step;
        
        $('.step-pane').hide();
        $('#step-' + step).show();
        
        $('.nav-wizard .nav-link').removeClass('active done');
        $('.nav-wizard li').each(function(index) {
            var $link = $(this).find('.nav-link');
            if (index + 1 < step) {
                $link.addClass('done');
            } else if (index + 1 === step) {
                $link.addClass('active');
            }
        });
        
        $('#wizardPrevBtn').toggle(step > 1 && step < 6);
        $('#wizardNextBtn').toggle(step < 5);
        $('#wizardSaveDraft').toggle(step < 6);
        $('#wizardFinishBtn').toggle(step === 6);
        
        if (step === 5) {
            $('#wizardNextBtn').html('<i class="fas fa-check me-1"></i> Create Connection');
        } else {
            $('#wizardNextBtn').html('Next <i class="fas fa-arrow-right ms-1"></i>');
        }
    }
    
    window.wizardPrev = function() {
        if (wizardCurrentStep > 1) {
            updateWizardStep(wizardCurrentStep - 1);
        }
    };
    
    window.wizardNext = function() {
        if (!validateCurrentStep()) return;
        
        saveCurrentStepData();
        
        if (wizardCurrentStep === 5) {
            createConnection();
        } else {
            updateWizardStep(wizardCurrentStep + 1);
        }
    };
    
    function validateCurrentStep() {
        var isValid = true;
        
        if (wizardCurrentStep === 1) {
            var name = $('#wizardApiName').val().trim();
            var subAccount = $('#wizardSubAccount').val();
            
            if (!name) {
                $('#wizardApiName').addClass('is-invalid');
                isValid = false;
            } else {
                $('#wizardApiName').removeClass('is-invalid');
            }
            
            if (!subAccount) {
                $('#wizardSubAccount').addClass('is-invalid');
                isValid = false;
            } else {
                $('#wizardSubAccount').removeClass('is-invalid');
            }
        }
        
        if (wizardCurrentStep === 2) {
            if (!wizardData.type) {
                $('#apiTypeError').css('display', 'block !important');
                isValid = false;
            }
        }
        
        if (wizardCurrentStep === 3) {
            if (!wizardData.authType) {
                $('#authTypeError').css('display', 'block !important');
                isValid = false;
            }
        }
        
        if (wizardCurrentStep === 5) {
            var dlrUrl = $('#wizardDlrUrl').val().trim();
            var inboundUrl = $('#wizardInboundUrl').val().trim();
            
            if (dlrUrl && !dlrUrl.startsWith('https://')) {
                $('#wizardDlrUrl').addClass('is-invalid');
                isValid = false;
            } else {
                $('#wizardDlrUrl').removeClass('is-invalid');
            }
            
            if (inboundUrl && !inboundUrl.startsWith('https://')) {
                $('#wizardInboundUrl').addClass('is-invalid');
                isValid = false;
            } else {
                $('#wizardInboundUrl').removeClass('is-invalid');
            }
        }
        
        return isValid;
    }
    
    function saveCurrentStepData() {
        if (wizardCurrentStep === 1) {
            wizardData.name = $('#wizardApiName').val().trim();
            wizardData.description = $('#wizardDescription').val().trim();
            wizardData.subAccount = $('#wizardSubAccount').val();
            wizardData.environment = $('#wizardEnvironment').val();
        }
        
        if (wizardCurrentStep === 4) {
            wizardData.ipAllowList = $('#wizardEnableIpRestriction').is(':checked');
            if (wizardData.ipAllowList) {
                var ipText = $('#wizardAllowedIps').val().trim();
                wizardData.allowedIps = ipText ? ipText.split('\n').map(function(ip) { return ip.trim(); }).filter(function(ip) { return ip; }) : [];
            } else {
                wizardData.allowedIps = [];
            }
        }
        
        if (wizardCurrentStep === 5) {
            wizardData.dlrUrl = $('#wizardDlrUrl').val().trim();
            wizardData.inboundUrl = $('#wizardInboundUrl').val().trim();
        }
    }
    
    window.selectApiType = function(type) {
        wizardData.type = type;
        wizardData.integrationName = null;
        
        $('.integration-tile[data-type]').removeClass('selected');
        $('.integration-tile[data-type="' + type + '"]').addClass('selected');
        $('#apiTypeError').css('display', 'none !important');
        
        if (type === 'integration') {
            $('#integrationSelector').slideDown();
        } else {
            $('#integrationSelector').slideUp();
            $('.integration-partner').removeClass('selected');
        }
    };
    
    window.selectIntegrationPartner = function(partner) {
        wizardData.integrationName = partner;
        $('.integration-partner').removeClass('selected');
        $('.integration-partner[data-partner="' + partner + '"]').addClass('selected');
    };
    
    window.selectAuthType = function(authType) {
        wizardData.authType = authType;
        $('.auth-tile').removeClass('selected');
        $('.auth-tile[data-auth="' + authType + '"]').addClass('selected');
        $('#authTypeError').css('display', 'none !important');
    };
    
    $('#wizardEnableIpRestriction').on('change', function() {
        $('#ipRestrictionFields').slideToggle($(this).is(':checked'));
    });
    
    function createConnection() {
        var newId = apiConnections.length + 1;
        var envPrefix = wizardData.environment === 'live' ? 'api' : 'sandbox';
        var typePrefix = wizardData.type === 'bulk' ? 'bulk' : (wizardData.type === 'campaign' ? 'campaigns' : 'integrations');
        var connId = wizardData.environment === 'live' ? 'prod-' + String(newId).padStart(3, '0') : 'test-' + String(newId).padStart(3, '0');
        
        var baseUrl = 'https://' + envPrefix + '.quicksms.io/v1/' + typePrefix + '/' + connId;
        
        var newConnection = {
            id: newId,
            name: wizardData.name,
            description: wizardData.description || null,
            subAccount: wizardData.subAccount,
            type: wizardData.type,
            integrationName: wizardData.integrationName,
            environment: wizardData.environment,
            authType: wizardData.authType,
            status: 'live',
            baseUrl: baseUrl,
            dlrUrl: wizardData.dlrUrl,
            inboundUrl: wizardData.inboundUrl,
            ipAllowList: wizardData.ipAllowList,
            allowedIps: wizardData.allowedIps,
            createdDate: new Date().toISOString().split('T')[0],
            lastUsed: null,
            archived: false,
            dependencies: []
        };
        
        apiConnections.push(newConnection);
        
        $('#createdBaseUrl').text(baseUrl);
        
        if (wizardData.authType === 'API Key') {
            var apiKey = generateNewApiKey();
            $('#createdApiKey').text(apiKey);
            $('#createdApiKeyRow').show();
            $('#createdUsernameRow, #createdPasswordRow').hide();
        } else {
            var username = 'api_user_' + newId;
            var password = generateRandomPassword();
            $('#createdUsername').text(username);
            $('#createdPassword').text(password);
            $('#createdUsernameRow, #createdPasswordRow').show();
            $('#createdApiKeyRow').hide();
        }
        
        console.log('[AUDIT] API Connection created:', newConnection.name, 'ID:', newConnection.id, 'Type:', newConnection.type, 'at:', new Date().toISOString());
        
        wizardDraft = null;
        localStorage.removeItem('apiConnectionDraft');
        
        renderTable();
        updateWizardStep(6);
    }
    
    function generateRandomPassword() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        var password = '';
        for (var i = 0; i < 16; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return password;
    }
    
    window.copyCreatedField = function(fieldId) {
        var text = $('#' + fieldId).text();
        navigator.clipboard.writeText(text).then(function() {
            showSuccessToast('Copied to clipboard!');
        });
    };
    
    window.closeWizard = function() {
        $('#createApiWizardModal').modal('hide');
        showSuccessToast('API Connection "' + wizardData.name + '" created successfully!');
    };
    
    window.saveDraft = function() {
        saveCurrentStepData();
        wizardDraft = JSON.parse(JSON.stringify(wizardData));
        wizardDraft.currentStep = wizardCurrentStep;
        localStorage.setItem('apiConnectionDraft', JSON.stringify(wizardDraft));
        showSuccessToast('Draft saved successfully!');
    };
    
    function loadDraft() {
        if (!wizardDraft) return;
        
        wizardData = JSON.parse(JSON.stringify(wizardDraft));
        
        $('#wizardApiName').val(wizardData.name);
        $('#wizardDescription').val(wizardData.description);
        $('#wizardSubAccount').val(wizardData.subAccount);
        $('#wizardEnvironment').val(wizardData.environment);
        
        if (wizardData.type) {
            selectApiType(wizardData.type);
            if (wizardData.integrationName) {
                selectIntegrationPartner(wizardData.integrationName);
            }
        }
        
        if (wizardData.authType) {
            selectAuthType(wizardData.authType);
        }
        
        $('#wizardEnableIpRestriction').prop('checked', wizardData.ipAllowList);
        if (wizardData.ipAllowList) {
            $('#ipRestrictionFields').show();
            $('#wizardAllowedIps').val(wizardData.allowedIps.join('\n'));
        }
        
        $('#wizardDlrUrl').val(wizardData.dlrUrl);
        $('#wizardInboundUrl').val(wizardData.inboundUrl);
        
        updateWizardStep(wizardDraft.currentStep || 1);
    }
    
    var savedDraft = localStorage.getItem('apiConnectionDraft');
    if (savedDraft) {
        try {
            wizardDraft = JSON.parse(savedDraft);
        } catch(e) {
            wizardDraft = null;
        }
    }
    
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
            'btn-warning',
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
    
    function generateNewApiKey() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var key = 'sk_live_';
        for (var i = 0; i < 32; i++) {
            key += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return key;
    }
    
    function generateAndShowNewKey(conn) {
        var newKey = generateNewApiKey();
        
        console.log('[AUDIT] API Key regenerated for connection:', conn.name, 'ID:', conn.id, 'at:', new Date().toISOString());
        
        $('#newApiKeyValue').val(newKey);
        $('#copyNewKeyBtn').html('<i class="fas fa-copy me-1"></i> Copy');
        
        $('#closeNewKeyModalBtn').off('click').on('click', function() {
            $('#newApiKeyValue').val('');
            $('#newKeyModal').modal('hide');
        });
        
        $('#newKeyModal').modal('show');
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
        var conn = getConnectionById(parseInt(connId));
        
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
        
        console.log('[AUDIT] Password changed for connection:', conn.name, 'ID:', conn.id, 'at:', new Date().toISOString());
        
        $('#changePasswordModal').modal('hide');
        
        showSuccessToast('Password changed successfully for "' + conn.name + '".');
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
    
    window.suspendConnection = function(id) {
        var conn = getConnectionById(id);
        var warning = getRecentUsageWarning(conn);
        
        showConfirmModal(
            'Suspend API Connection - Step 1 of 2',
            'Suspending "' + conn.name + '" will immediately block all API traffic. Any systems using this connection will stop working.',
            'Continue',
            'btn-warning',
            function() {
                showConfirmModal(
                    'Suspend API Connection - Final Confirmation',
                    'Please confirm you want to suspend "' + conn.name + '". All API requests will be rejected immediately.',
                    'Suspend Now',
                    'btn-danger',
                    function() {
                        conn.status = 'suspended';
                        console.log('[AUDIT] API Connection suspended:', conn.name, 'ID:', conn.id, 'at:', new Date().toISOString());
                        renderTable();
                        showSuccessToast('API Connection "' + conn.name + '" has been suspended.');
                    },
                    null
                );
            },
            warning
        );
    };
    
    window.reactivateConnection = function(id) {
        var conn = getConnectionById(id);
        
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
                    'btn-success',
                    function() {
                        conn.status = 'live';
                        console.log('[AUDIT] API Connection reactivated:', conn.name, 'ID:', conn.id, 'at:', new Date().toISOString());
                        renderTable();
                        showSuccessToast('API Connection "' + conn.name + '" has been reactivated.');
                    },
                    null
                );
            },
            null
        );
    };
    
    window.convertToLive = function(id) {
        var conn = getConnectionById(id);
        
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
                    'btn-warning',
                    function() {
                        conn.environment = 'live';
                        
                        if (conn.baseUrl.includes('sandbox')) {
                            conn.baseUrl = conn.baseUrl.replace('sandbox.', 'api.').replace('/test-', '/prod-');
                        }
                        
                        console.log('[AUDIT] API Connection converted to Live:', conn.name, 'ID:', conn.id, 'at:', new Date().toISOString());
                        renderTable();
                        showSuccessToast('API Connection "' + conn.name + '" has been converted to Live environment.');
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
            $('#confirmModal').modal('hide');
            onConfirm();
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
            'btn-warning',
            function() {
                showConfirmModalWithDetails(
                    'Archive API Connection - Final Confirmation',
                    'Please confirm you want to archive "' + conn.name + '".\n\nArchived connections:\n• Cannot accept API requests\n• Cannot be modified\n• Hidden from default view\n• Retained for audit trail\n\nYou can view archived connections using the "Show Archived" toggle.',
                    'Archive Now',
                    'btn-danger',
                    function() {
                        conn.archived = true;
                        console.log('[AUDIT] API Connection archived:', conn.name, 'ID:', conn.id, 'at:', new Date().toISOString());
                        renderTable();
                        showSuccessToast('API Connection "' + conn.name + '" has been archived. Use "Show Archived" to view it.');
                    }
                );
            },
            warning
        );
    };
    
    renderTable();
});
</script>
@endpush

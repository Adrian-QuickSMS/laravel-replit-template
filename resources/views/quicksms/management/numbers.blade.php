@extends('layouts.quicksms')

@section('title', 'Numbers')

@push('styles')
<style>
.numbers-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}
.numbers-table {
    width: 100%;
    margin: 0;
    min-width: 1200px;
    table-layout: fixed;
}
.numbers-table thead th {
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
.numbers-table thead th:first-child { width: 14%; }
.numbers-table thead th:nth-child(2) { width: 8%; }
.numbers-table thead th:nth-child(3) { width: 12%; }
.numbers-table thead th:nth-child(4) { width: 9%; }
.numbers-table thead th:nth-child(5) { width: 16%; }
.numbers-table thead th:nth-child(6) { width: 7%; }
.numbers-table thead th:nth-child(7) { width: 10%; }
.numbers-table thead th:nth-child(8) { width: 8%; }
.numbers-table thead th:nth-child(9) { width: 10%; }
.numbers-table thead th:last-child { 
    width: 6%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.numbers-table thead th:hover {
    background: #e9ecef;
}
.numbers-table thead th:last-child:hover {
    background: #f8f9fa;
}
.numbers-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.numbers-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.numbers-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
}
.numbers-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.numbers-table tbody td:last-child:has(.dropdown.show),
.numbers-table tbody td:last-child.dropdown-active {
    z-index: 2000 !important;
}
.numbers-table tbody tr:last-child td {
    border-bottom: none;
}
.numbers-table tbody tr:hover td {
    background: #f8f9fa;
    cursor: pointer;
}
.numbers-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.number-value {
    font-weight: 500;
    color: #343a40;
    font-family: 'SF Mono', 'Consolas', monospace;
}
.badge-active {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-suspended {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-pending {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.capability-pill {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 500;
    border-radius: 1rem;
    margin-right: 0.25rem;
    margin-bottom: 0.15rem;
}
.capability-api {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.capability-portal {
    background: rgba(111, 66, 193, 0.15);
    color: #6f42c1;
}
.capability-inbox {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.capability-optout {
    background: rgba(214, 83, 193, 0.15);
    color: #D653C1;
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
.suspended-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}
.suspended-row:hover {
    opacity: 0.8;
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
.cost-value {
    font-weight: 500;
    color: #343a40;
}
.bulk-action-bar {
    display: none;
    background: linear-gradient(135deg, rgba(136, 108, 192, 0.1) 0%, rgba(111, 66, 193, 0.15) 100%);
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    align-items: center;
    gap: 1rem;
}
.bulk-action-bar.show {
    display: flex;
}
.bulk-action-bar .selection-info {
    font-weight: 500;
    color: #495057;
}
.bulk-action-bar .selection-count {
    color: var(--primary);
    font-weight: 600;
}
.bulk-action-bar .btn-bulk {
    padding: 0.35rem 0.75rem;
    font-size: 0.8rem;
}
.numbers-table thead th.checkbox-col,
.numbers-table tbody td.checkbox-col {
    width: 40px !important;
    min-width: 40px;
    max-width: 40px;
    text-align: center;
    cursor: default;
}
.numbers-table thead th.checkbox-col:hover {
    background: #f8f9fa;
}
.row-checkbox {
    cursor: pointer;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Management</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Numbers</a></li>
        </ol>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="card-title mb-0">Numbers Library</h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
                <a href="{{ route('purchase.numbers') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Purchase Number
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search by number or keyword...">
                </div>
            </div>
        
            <div class="collapse" id="filtersPanel">
                <div class="card card-body border-0 rounded-0" style="background-color: #f0ebf8; border-bottom: 1px solid #e9ecef !important;">
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Country</label>
                            <div class="dropdown multiselect-dropdown" data-filter="countries">
                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                    <span class="dropdown-label">All Countries</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="UK" id="countryUK"><label class="form-check-label small" for="countryUK">United Kingdom</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="US" id="countryUS"><label class="form-check-label small" for="countryUS">United States</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="DE" id="countryDE"><label class="form-check-label small" for="countryDE">Germany</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="FR" id="countryFR"><label class="form-check-label small" for="countryFR">France</label></div>
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
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="vmn" id="typeVMN"><label class="form-check-label small" for="typeVMN">VMN (Virtual Mobile Number)</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="dedicated_shortcode" id="typeDedicated"><label class="form-check-label small" for="typeDedicated">Dedicated Shortcode</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="shortcode_keyword" id="typeKeyword"><label class="form-check-label small" for="typeKeyword">Shortcode Keyword</label></div>
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
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="active" id="statusActive"><label class="form-check-label small" for="statusActive">Active</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="suspended" id="statusSuspended"><label class="form-check-label small" for="statusSuspended">Suspended</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="pending" id="statusPending"><label class="form-check-label small" for="statusPending">Pending</label></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Mode</label>
                            <div class="dropdown multiselect-dropdown" data-filter="modes">
                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                    <span class="dropdown-label">All Modes</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="portal" id="modePortal"><label class="form-check-label small" for="modePortal">Portal</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="api" id="modeAPI"><label class="form-check-label small" for="modeAPI">API</label></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Capability</label>
                            <div class="dropdown multiselect-dropdown" data-filter="capabilities">
                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                    <span class="dropdown-label">All Capabilities</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="api" id="capAPI"><label class="form-check-label small" for="capAPI">API</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="portal" id="capPortal"><label class="form-check-label small" for="capPortal">Portal SenderID</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="inbox" id="capInbox"><label class="form-check-label small" for="capInbox">Inbox</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="optout" id="capOptout"><label class="form-check-label small" for="capOptout">Opt-out</label></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end mt-1">
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
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Support" id="subAccSupport"><label class="form-check-label small" for="subAccSupport">Support</label></div>
                                </div>
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
            
            <div class="bulk-action-bar" id="bulkActionBar">
                <div class="selection-info">
                    <span class="selection-count" id="selectedCount">0</span> number(s) selected
                </div>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-warning btn-bulk" id="btnBulkSuspend" disabled>
                        <i class="fas fa-pause-circle me-1"></i> Suspend
                    </button>
                    <button type="button" class="btn btn-success btn-bulk" id="btnBulkReactivate" disabled>
                        <i class="fas fa-play-circle me-1"></i> Reactivate
                    </button>
                    <button type="button" class="btn btn-primary btn-bulk" id="btnBulkAssignSubAccounts" disabled>
                        <i class="fas fa-users me-1"></i> Assign Sub-Accounts
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-bulk" id="btnClearSelection">
                        <i class="fas fa-times me-1"></i> Clear
                    </button>
                </div>
            </div>
            
            <div class="numbers-table-container" id="numbersTableContainer">
                <div class="table-responsive">
                    <table class="table numbers-table mb-0" id="numbersTable">
                        <thead>
                            <tr>
                                <th class="checkbox-col"><input type="checkbox" class="form-check-input" id="selectAllCheckbox" title="Select all"></th>
                                <th data-sort="number">Number <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="country">Country <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                                <th>Capabilities</th>
                                <th data-sort="mode">Mode <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="subAccounts">Sub-Accounts <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="monthlyCost">Monthly Cost <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="lastUsed">Last Used <i class="fas fa-sort sort-icon"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="numbersTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="empty-state" id="emptyState" style="display: none;">
                <div class="empty-state-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h4>No Numbers Found</h4>
                <p>You don't have any numbers yet. Purchase your first number to start receiving messages.</p>
                <a href="{{ route('purchase.numbers') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Purchase Number
                </a>
            </div>
        
            <div class="table-footer mt-3">
                <div class="pagination-info">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> numbers
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Number Configuration Offcanvas --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="numberConfigDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title mb-0"><i class="fas fa-cog me-2 text-primary"></i>Number Configuration</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-4 border-bottom">
            <h5 id="drawerNumber" class="mb-3 fw-semibold font-monospace">-</h5>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span id="drawerTypeBadge" class="badge rounded-pill badge-pastel-primary">-</span>
                <span id="drawerStatusBadge" class="badge rounded-pill">-</span>
                <span id="drawerModeBadge" class="badge rounded-pill badge-pastel-secondary">-</span>
            </div>
        </div>

        <div class="p-4">
            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Number Details</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Number</div>
                        <div class="col-7 small fw-medium font-monospace" id="drawerNumberDetail">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Country</div>
                        <div class="col-7 small" id="drawerCountry">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Type</div>
                        <div class="col-7 small" id="drawerType">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Mode</div>
                        <div class="col-7 small" id="drawerMode">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Monthly Cost</div>
                        <div class="col-7 small fw-medium" id="drawerCost">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-check-circle me-2 text-primary"></i>Capabilities</h6>
                    <div id="drawerCapabilities" class="d-flex flex-wrap gap-1">
                        -
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-users me-2 text-primary"></i>Assigned Sub-Accounts</h6>
                    <div id="drawerSubAccounts" class="small">
                        -
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-history me-2 text-primary"></i>Usage</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Last Used</div>
                        <div class="col-7 small" id="drawerLastUsed">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Purchase Date</div>
                        <div class="col-7 small" id="drawerPurchaseDate">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Renewal Date</div>
                        <div class="col-7 small" id="drawerRenewalDate">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 border-top">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="btnEditNumber">
                    <i class="fas fa-edit me-1"></i> Edit Configuration
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="offcanvas">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Confirm Action Modal --}}
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

{{-- Bulk Assign Sub-Accounts Modal --}}
<div class="modal fade" id="assignSubAccountsModal" tabindex="-1" aria-labelledby="assignSubAccountsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignSubAccountsModalLabel">Assign Sub-Accounts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Assigning sub-accounts to <strong><span id="assignNumbersCount">0</span></strong> selected number(s).
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Sub-Accounts</label>
                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="assignSubAccAll">
                            <label class="form-check-label fw-bold" for="assignSubAccAll">Select All</label>
                        </div>
                        <hr class="my-2">
                        <div class="form-check"><input class="form-check-input assign-subacc-check" type="checkbox" value="Main Account" id="assignSubAccMain"><label class="form-check-label" for="assignSubAccMain">Main Account</label></div>
                        <div class="form-check"><input class="form-check-input assign-subacc-check" type="checkbox" value="Marketing" id="assignSubAccMarketing"><label class="form-check-label" for="assignSubAccMarketing">Marketing</label></div>
                        <div class="form-check"><input class="form-check-input assign-subacc-check" type="checkbox" value="Support" id="assignSubAccSupport"><label class="form-check-label" for="assignSubAccSupport">Support</label></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnConfirmAssignSubAccounts">Assign</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // TODO: Replace with backend API data
    var numbersData = [
        {
            id: 1,
            number: '+447700900123',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'vmn',
            status: 'active',
            capabilities: ['api', 'portal', 'inbox', 'optout'],
            mode: 'portal',
            subAccounts: ['Main Account'],
            monthlyCost: 5.00,
            lastUsed: '2025-01-13 14:32:45',
            purchaseDate: '2024-06-15',
            renewalDate: '2025-02-15'
        },
        {
            id: 2,
            number: '+447700900456',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'vmn',
            status: 'active',
            capabilities: ['api', 'inbox'],
            mode: 'api',
            subAccounts: ['Main Account', 'Marketing'],
            monthlyCost: 5.00,
            lastUsed: '2025-01-12 09:15:22',
            purchaseDate: '2024-08-20',
            renewalDate: '2025-02-20'
        },
        {
            id: 3,
            number: '88600',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'dedicated_shortcode',
            status: 'active',
            capabilities: ['api', 'portal', 'inbox', 'optout'],
            mode: 'portal',
            subAccounts: ['Main Account', 'Marketing', 'Support'],
            monthlyCost: 500.00,
            lastUsed: '2025-01-13 11:45:18',
            purchaseDate: '2024-01-10',
            renewalDate: '2025-02-10'
        },
        {
            id: 4,
            number: 'OFFER on 88000',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'shortcode_keyword',
            status: 'active',
            capabilities: ['portal', 'optout'],
            mode: 'portal',
            subAccounts: ['Marketing'],
            monthlyCost: 25.00,
            lastUsed: '2025-01-10 16:30:00',
            purchaseDate: '2024-09-05',
            renewalDate: '2025-02-05'
        },
        {
            id: 5,
            number: '+14155551234',
            country: 'US',
            countryName: 'United States',
            type: 'vmn',
            status: 'suspended',
            capabilities: ['api'],
            mode: 'api',
            subAccounts: ['Main Account'],
            monthlyCost: 8.00,
            lastUsed: '2024-12-15 08:00:00',
            purchaseDate: '2024-03-22',
            renewalDate: '2025-02-22'
        },
        {
            id: 6,
            number: '+447700900789',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'vmn',
            status: 'pending',
            capabilities: ['portal', 'inbox'],
            mode: 'portal',
            subAccounts: ['Support'],
            monthlyCost: 5.00,
            lastUsed: null,
            purchaseDate: '2025-01-10',
            renewalDate: '2025-02-10'
        },
        {
            id: 7,
            number: 'INFO on 88000',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'shortcode_keyword',
            status: 'active',
            capabilities: ['api', 'portal', 'inbox'],
            mode: 'portal',
            subAccounts: ['Main Account'],
            monthlyCost: 25.00,
            lastUsed: '2025-01-08 10:22:14',
            purchaseDate: '2024-11-18',
            renewalDate: '2025-02-18'
        }
    ];

    var currentSort = { column: 'number', direction: 'asc' };
    
    var appliedFilters = {
        search: '',
        countries: [],
        types: [],
        statuses: [],
        modes: [],
        capabilities: [],
        subAccounts: []
    };

    var selectedNumbers = [];

    // Add/remove dropdown-active class on parent td for z-index fix
    $(document).on('shown.bs.dropdown', '.numbers-table .dropdown', function() {
        $(this).closest('td').addClass('dropdown-active');
    });
    $(document).on('hidden.bs.dropdown', '.numbers-table .dropdown', function() {
        $(this).closest('td').removeClass('dropdown-active');
    });

    function getTypeLabel(type) {
        switch(type) {
            case 'vmn': return 'VMN';
            case 'dedicated_shortcode': return 'Dedicated Shortcode';
            case 'shortcode_keyword': return 'Shortcode Keyword';
            default: return type;
        }
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'active': return 'badge-active';
            case 'suspended': return 'badge-suspended';
            case 'pending': return 'badge-pending';
            default: return 'badge-pastel-secondary';
        }
    }

    function getStatusLabel(status) {
        switch(status) {
            case 'active': return 'Active';
            case 'suspended': return 'Suspended';
            case 'pending': return 'Pending';
            default: return status;
        }
    }

    function formatCapabilities(capabilities) {
        var html = '';
        if (capabilities.includes('api')) {
            html += '<span class="capability-pill capability-api">API</span>';
        }
        if (capabilities.includes('portal')) {
            html += '<span class="capability-pill capability-portal">Portal SenderID</span>';
        }
        if (capabilities.includes('inbox')) {
            html += '<span class="capability-pill capability-inbox">Inbox</span>';
        }
        if (capabilities.includes('optout')) {
            html += '<span class="capability-pill capability-optout">Opt-out</span>';
        }
        return html || '<span class="text-muted small">None</span>';
    }

    function formatSubAccounts(subAccounts) {
        if (!subAccounts || subAccounts.length === 0) return '<span class="text-muted">-</span>';
        if (subAccounts.length === 1) return subAccounts[0];
        return 'Multiple (' + subAccounts.length + ')';
    }

    function formatCurrency(amount) {
        return '£' + amount.toFixed(2);
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '<span class="text-muted">Never</span>';
        var date = new Date(dateStr);
        var options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
        return date.toLocaleDateString('en-GB', options);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var date = new Date(dateStr);
        var options = { day: '2-digit', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('en-GB', options);
    }

    function renderTable() {
        var filtered = numbersData.filter(function(num) {
            if (appliedFilters.search) {
                var search = appliedFilters.search.toLowerCase();
                if (!num.number.toLowerCase().includes(search)) return false;
            }
            
            if (appliedFilters.countries.length > 0 && !appliedFilters.countries.includes(num.country)) return false;
            if (appliedFilters.types.length > 0 && !appliedFilters.types.includes(num.type)) return false;
            if (appliedFilters.statuses.length > 0 && !appliedFilters.statuses.includes(num.status)) return false;
            if (appliedFilters.modes.length > 0 && !appliedFilters.modes.includes(num.mode)) return false;
            
            if (appliedFilters.capabilities.length > 0) {
                var hasCapMatch = appliedFilters.capabilities.some(function(cap) {
                    return num.capabilities.includes(cap);
                });
                if (!hasCapMatch) return false;
            }
            
            if (appliedFilters.subAccounts.length > 0) {
                var hasMatch = num.subAccounts.some(function(sa) {
                    return appliedFilters.subAccounts.includes(sa);
                });
                if (!hasMatch) return false;
            }
            
            return true;
        });
        
        filtered.sort(function(a, b) {
            var aVal = a[currentSort.column];
            var bVal = b[currentSort.column];
            
            if (currentSort.column === 'subAccounts') {
                aVal = a.subAccounts.length;
                bVal = b.subAccounts.length;
            }
            
            if (aVal === null || aVal === undefined) aVal = '';
            if (bVal === null || bVal === undefined) bVal = '';
            
            if (typeof aVal === 'string') aVal = aVal.toLowerCase();
            if (typeof bVal === 'string') bVal = bVal.toLowerCase();
            
            if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
            if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
            return 0;
        });
        
        if (filtered.length === 0) {
            $('#numbersTableContainer').hide();
            $('#emptyState').show();
            $('#showingCount').text(0);
            $('#totalCount').text(numbersData.length);
            return;
        }
        
        $('#numbersTableContainer').show();
        $('#emptyState').hide();
        
        var html = '';
        filtered.forEach(function(num) {
            var rowClass = num.status === 'suspended' ? 'suspended-row' : '';
            var isChecked = selectedNumbers.includes(num.id) ? 'checked' : '';
            html += '<tr class="' + rowClass + '" data-id="' + num.id + '" data-mode="' + num.mode + '" data-status="' + num.status + '">';
            
            html += '<td class="checkbox-col"><input type="checkbox" class="form-check-input row-checkbox" data-id="' + num.id + '" ' + isChecked + '></td>';
            html += '<td><span class="number-value">' + num.number + '</span></td>';
            html += '<td>' + num.countryName + '</td>';
            html += '<td>' + getTypeLabel(num.type) + '</td>';
            html += '<td><span class="badge rounded-pill ' + getStatusBadgeClass(num.status) + '">' + getStatusLabel(num.status) + '</span></td>';
            html += '<td>' + formatCapabilities(num.capabilities) + '</td>';
            html += '<td>' + (num.mode === 'portal' ? 'Portal' : 'API') + '</td>';
            html += '<td>' + formatSubAccounts(num.subAccounts) + '</td>';
            html += '<td><span class="cost-value">' + formatCurrency(num.monthlyCost) + '</span></td>';
            html += '<td>' + formatDateTime(num.lastUsed) + '</td>';
            
            html += '<td>';
            html += '<div class="dropdown">';
            html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
            html += '<i class="fas fa-ellipsis-v"></i>';
            html += '</button>';
            html += '<ul class="dropdown-menu dropdown-menu-end">';
            
            html += '<li><a class="dropdown-item" href="#" onclick="viewNumber(' + num.id + '); return false;">View Configuration</a></li>';
            html += '<li><a class="dropdown-item" href="#" onclick="editNumber(' + num.id + '); return false;">Edit Configuration</a></li>';
            
            if (num.status === 'active') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-warning" href="#" onclick="suspendNumber(' + num.id + '); return false;">Suspend Number</a></li>';
            }
            
            if (num.status === 'suspended') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-success" href="#" onclick="reactivateNumber(' + num.id + '); return false;">Reactivate Number</a></li>';
            }
            
            html += '<li><hr class="dropdown-divider"></li>';
            html += '<li><a class="dropdown-item text-danger" href="#" onclick="releaseNumber(' + num.id + '); return false;">Release Number</a></li>';
            
            html += '</ul>';
            html += '</div>';
            html += '</td>';
            
            html += '</tr>';
        });
        
        $('#numbersTableBody').html(html);
        $('#showingCount').text(filtered.length);
        $('#totalCount').text(numbersData.length);
    }

    // Row click to open configuration drawer
    $(document).on('click', '.numbers-table tbody tr', function(e) {
        if ($(e.target).closest('.dropdown').length) return;
        if ($(e.target).hasClass('row-checkbox') || $(e.target).hasClass('form-check-input')) return;
        var id = $(this).data('id');
        viewNumber(id);
    });

    // Checkbox selection handling
    $(document).on('change', '.row-checkbox', function() {
        var id = $(this).data('id');
        if ($(this).is(':checked')) {
            if (!selectedNumbers.includes(id)) {
                selectedNumbers.push(id);
            }
        } else {
            selectedNumbers = selectedNumbers.filter(function(n) { return n !== id; });
        }
        updateBulkActionBar();
        updateSelectAllCheckbox();
    });

    $('#selectAllCheckbox').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.row-checkbox').each(function() {
            $(this).prop('checked', isChecked);
            var id = $(this).data('id');
            if (isChecked) {
                if (!selectedNumbers.includes(id)) {
                    selectedNumbers.push(id);
                }
            } else {
                selectedNumbers = selectedNumbers.filter(function(n) { return n !== id; });
            }
        });
        updateBulkActionBar();
    });

    function updateSelectAllCheckbox() {
        var total = $('.row-checkbox').length;
        var checked = $('.row-checkbox:checked').length;
        $('#selectAllCheckbox').prop('checked', total > 0 && total === checked);
        $('#selectAllCheckbox').prop('indeterminate', checked > 0 && checked < total);
    }

    function updateBulkActionBar() {
        var count = selectedNumbers.length;
        $('#selectedCount').text(count);
        
        if (count > 0) {
            $('#bulkActionBar').addClass('show');
        } else {
            $('#bulkActionBar').removeClass('show');
        }
        
        // Get selected numbers data
        var selectedData = numbersData.filter(function(n) { 
            return selectedNumbers.includes(n.id); 
        });
        
        // Check if any active numbers are selected (can suspend)
        var hasActive = selectedData.some(function(n) { return n.status === 'active'; });
        $('#btnBulkSuspend').prop('disabled', !hasActive);
        
        // Check if any suspended numbers are selected (can reactivate)
        var hasSuspended = selectedData.some(function(n) { return n.status === 'suspended'; });
        $('#btnBulkReactivate').prop('disabled', !hasSuspended);
        
        // Assign Sub-Accounts only for Portal mode numbers
        var allPortal = selectedData.every(function(n) { return n.mode === 'portal'; });
        $('#btnBulkAssignSubAccounts').prop('disabled', !allPortal || count === 0);
    }

    $('#btnClearSelection').on('click', function() {
        selectedNumbers = [];
        $('.row-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false).prop('indeterminate', false);
        updateBulkActionBar();
    });

    // Bulk Suspend
    $('#btnBulkSuspend').on('click', function() {
        var activeNumbers = numbersData.filter(function(n) {
            return selectedNumbers.includes(n.id) && n.status === 'active';
        });
        
        if (activeNumbers.length === 0) {
            toastr.warning('No active numbers selected to suspend');
            return;
        }
        
        $('#confirmModalLabel').text('Bulk Suspend Numbers');
        $('#confirmModalMessage').text('Are you sure you want to suspend ' + activeNumbers.length + ' active number(s)?');
        $('#confirmModalWarning').show();
        $('#confirmModalWarningText').text('These numbers will be hidden from sender pickers across all modules.');
        $('#confirmModalBtn').removeClass('btn-primary btn-danger').addClass('btn-warning').text('Suspend');
        
        $('#confirmModalBtn').off('click').on('click', function() {
            // TODO: Backend API call
            activeNumbers.forEach(function(num) {
                num.status = 'suspended';
            });
            selectedNumbers = [];
            $('.row-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
            updateBulkActionBar();
            renderTable();
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
            toastr.success(activeNumbers.length + ' number(s) suspended successfully');
        });
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    });

    // Bulk Reactivate
    $('#btnBulkReactivate').on('click', function() {
        var suspendedNumbers = numbersData.filter(function(n) {
            return selectedNumbers.includes(n.id) && n.status === 'suspended';
        });
        
        if (suspendedNumbers.length === 0) {
            toastr.warning('No suspended numbers selected to reactivate');
            return;
        }
        
        $('#confirmModalLabel').text('Bulk Reactivate Numbers');
        $('#confirmModalMessage').text('Are you sure you want to reactivate ' + suspendedNumbers.length + ' suspended number(s)?');
        $('#confirmModalWarning').hide();
        $('#confirmModalBtn').removeClass('btn-warning btn-danger').addClass('btn-primary').text('Reactivate');
        
        $('#confirmModalBtn').off('click').on('click', function() {
            // TODO: Backend API call
            suspendedNumbers.forEach(function(num) {
                num.status = 'active';
            });
            selectedNumbers = [];
            $('.row-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
            updateBulkActionBar();
            renderTable();
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
            toastr.success(suspendedNumbers.length + ' number(s) reactivated successfully');
        });
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    });

    // Bulk Assign Sub-Accounts
    $('#btnBulkAssignSubAccounts').on('click', function() {
        var portalNumbers = numbersData.filter(function(n) {
            return selectedNumbers.includes(n.id) && n.mode === 'portal';
        });
        
        if (portalNumbers.length === 0) {
            toastr.warning('Assign Sub-Accounts is only available for Portal mode numbers');
            return;
        }
        
        $('#assignNumbersCount').text(portalNumbers.length);
        $('.assign-subacc-check').prop('checked', false);
        $('#assignSubAccAll').prop('checked', false);
        
        new bootstrap.Modal(document.getElementById('assignSubAccountsModal')).show();
    });

    // Select All in Assign Sub-Accounts modal
    $('#assignSubAccAll').on('change', function() {
        $('.assign-subacc-check').prop('checked', $(this).is(':checked'));
    });

    // Confirm Assign Sub-Accounts
    $('#btnConfirmAssignSubAccounts').on('click', function() {
        var selectedSubAccs = [];
        $('.assign-subacc-check:checked').each(function() {
            selectedSubAccs.push($(this).val());
        });
        
        if (selectedSubAccs.length === 0) {
            toastr.warning('Please select at least one sub-account');
            return;
        }
        
        var portalNumbers = numbersData.filter(function(n) {
            return selectedNumbers.includes(n.id) && n.mode === 'portal';
        });
        
        // TODO: Backend API call
        portalNumbers.forEach(function(num) {
            num.subAccounts = selectedSubAccs.slice();
        });
        
        selectedNumbers = [];
        $('.row-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        updateBulkActionBar();
        renderTable();
        bootstrap.Modal.getInstance(document.getElementById('assignSubAccountsModal')).hide();
        toastr.success('Sub-accounts assigned to ' + portalNumbers.length + ' number(s)');
    });

    // Sorting
    $('.numbers-table thead th[data-sort]').on('click', function() {
        var column = $(this).data('sort');
        
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }
        
        $('.numbers-table thead th').removeClass('sorted');
        $(this).addClass('sorted');
        $(this).find('.sort-icon').removeClass('fa-sort fa-sort-up fa-sort-down')
            .addClass(currentSort.direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        
        renderTable();
    });

    // Search
    $('#searchInput').on('input', function() {
        appliedFilters.search = $(this).val();
        renderTable();
    });

    // Filter handling
    $('.multiselect-dropdown .form-check-input').on('change', function() {
        updateFilterLabel($(this).closest('.multiselect-dropdown'));
    });

    $('.select-all-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('.form-check-input').prop('checked', true);
        updateFilterLabel($(this).closest('.multiselect-dropdown'));
    });

    $('.clear-all-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('.form-check-input').prop('checked', false);
        updateFilterLabel($(this).closest('.multiselect-dropdown'));
    });

    function updateFilterLabel(dropdown) {
        var checked = dropdown.find('.form-check-input:checked');
        var label = dropdown.find('.dropdown-label');
        var filterType = dropdown.data('filter');
        
        if (checked.length === 0) {
            label.text('All ' + getFilterName(filterType));
        } else if (checked.length === 1) {
            label.text(checked.first().next('label').text());
        } else {
            label.text(checked.length + ' selected');
        }
    }

    function getFilterName(filterType) {
        switch(filterType) {
            case 'countries': return 'Countries';
            case 'types': return 'Types';
            case 'statuses': return 'Statuses';
            case 'modes': return 'Modes';
            case 'capabilities': return 'Capabilities';
            case 'subAccounts': return 'Sub-Accounts';
            default: return '';
        }
    }

    function getCapabilityLabel(cap) {
        switch(cap) {
            case 'api': return 'API';
            case 'portal': return 'Portal SenderID';
            case 'inbox': return 'Inbox';
            case 'optout': return 'Opt-out';
            default: return cap;
        }
    }

    $('#btnApplyFilters').on('click', function() {
        appliedFilters.countries = [];
        appliedFilters.types = [];
        appliedFilters.statuses = [];
        appliedFilters.modes = [];
        appliedFilters.capabilities = [];
        appliedFilters.subAccounts = [];
        
        $('[data-filter="countries"] .form-check-input:checked').each(function() {
            appliedFilters.countries.push($(this).val());
        });
        $('[data-filter="types"] .form-check-input:checked').each(function() {
            appliedFilters.types.push($(this).val());
        });
        $('[data-filter="statuses"] .form-check-input:checked').each(function() {
            appliedFilters.statuses.push($(this).val());
        });
        $('[data-filter="modes"] .form-check-input:checked').each(function() {
            appliedFilters.modes.push($(this).val());
        });
        $('[data-filter="capabilities"] .form-check-input:checked').each(function() {
            appliedFilters.capabilities.push($(this).val());
        });
        $('[data-filter="subAccounts"] .form-check-input:checked').each(function() {
            appliedFilters.subAccounts.push($(this).val());
        });
        
        updateActiveFiltersDisplay();
        renderTable();
        $('#filtersPanel').collapse('hide');
    });

    $('#btnResetFilters').on('click', function() {
        $('.multiselect-dropdown .form-check-input').prop('checked', false);
        $('.multiselect-dropdown').each(function() {
            updateFilterLabel($(this));
        });
        appliedFilters.countries = [];
        appliedFilters.types = [];
        appliedFilters.statuses = [];
        appliedFilters.modes = [];
        appliedFilters.capabilities = [];
        appliedFilters.subAccounts = [];
        updateActiveFiltersDisplay();
        renderTable();
    });

    $('#btnClearAllFilters').on('click', function() {
        $('#btnResetFilters').click();
    });

    function updateActiveFiltersDisplay() {
        var hasFilters = appliedFilters.countries.length > 0 || 
                        appliedFilters.types.length > 0 || 
                        appliedFilters.statuses.length > 0 || 
                        appliedFilters.modes.length > 0 ||
                        appliedFilters.capabilities.length > 0 ||
                        appliedFilters.subAccounts.length > 0;
        
        if (hasFilters) {
            $('#activeFiltersContainer').show();
            var html = '';
            
            appliedFilters.countries.forEach(function(c) {
                html += '<span class="filter-chip"><span class="chip-label">Country:</span>' + c + '<span class="remove-chip" data-filter="countries" data-value="' + c + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.types.forEach(function(t) {
                html += '<span class="filter-chip"><span class="chip-label">Type:</span>' + getTypeLabel(t) + '<span class="remove-chip" data-filter="types" data-value="' + t + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.statuses.forEach(function(s) {
                html += '<span class="filter-chip"><span class="chip-label">Status:</span>' + getStatusLabel(s) + '<span class="remove-chip" data-filter="statuses" data-value="' + s + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.modes.forEach(function(m) {
                html += '<span class="filter-chip"><span class="chip-label">Mode:</span>' + (m === 'portal' ? 'Portal' : 'API') + '<span class="remove-chip" data-filter="modes" data-value="' + m + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.capabilities.forEach(function(cap) {
                html += '<span class="filter-chip"><span class="chip-label">Capability:</span>' + getCapabilityLabel(cap) + '<span class="remove-chip" data-filter="capabilities" data-value="' + cap + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.subAccounts.forEach(function(sa) {
                html += '<span class="filter-chip"><span class="chip-label">Sub-Account:</span>' + sa + '<span class="remove-chip" data-filter="subAccounts" data-value="' + sa + '"><i class="fas fa-times"></i></span></span>';
            });
            
            $('#activeFiltersChips').html(html);
        } else {
            $('#activeFiltersContainer').hide();
        }
    }

    $(document).on('click', '.remove-chip', function() {
        var filterType = $(this).data('filter');
        var value = $(this).data('value');
        
        var index = appliedFilters[filterType].indexOf(value);
        if (index > -1) {
            appliedFilters[filterType].splice(index, 1);
        }
        
        $('[data-filter="' + filterType + '"] .form-check-input[value="' + value + '"]').prop('checked', false);
        updateFilterLabel($('[data-filter="' + filterType + '"]'));
        updateActiveFiltersDisplay();
        renderTable();
    });

    // View number function
    window.viewNumber = function(id) {
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num) return;
        
        $('#drawerNumber').text(num.number);
        $('#drawerNumberDetail').text(num.number);
        $('#drawerCountry').text(num.countryName);
        $('#drawerType').text(getTypeLabel(num.type));
        $('#drawerMode').text(num.mode === 'portal' ? 'Portal' : 'API');
        $('#drawerCost').text(formatCurrency(num.monthlyCost));
        
        $('#drawerTypeBadge').text(getTypeLabel(num.type));
        $('#drawerStatusBadge').removeClass('badge-active badge-suspended badge-pending')
            .addClass(getStatusBadgeClass(num.status)).text(getStatusLabel(num.status));
        $('#drawerModeBadge').text(num.mode === 'portal' ? 'Portal' : 'API');
        
        $('#drawerCapabilities').html(formatCapabilities(num.capabilities));
        
        var saHtml = num.subAccounts.map(function(sa) {
            return '<span class="badge badge-pastel-secondary me-1 mb-1">' + sa + '</span>';
        }).join('');
        $('#drawerSubAccounts').html(saHtml || '<span class="text-muted">None assigned</span>');
        
        $('#drawerLastUsed').text(num.lastUsed ? formatDateTime(num.lastUsed) : 'Never');
        $('#drawerPurchaseDate').text(formatDate(num.purchaseDate));
        $('#drawerRenewalDate').text(formatDate(num.renewalDate));
        
        var offcanvas = new bootstrap.Offcanvas(document.getElementById('numberConfigDrawer'));
        offcanvas.show();
    };

    // Edit number function
    window.editNumber = function(id) {
        // TODO: Implement edit number modal/wizard
        console.log('Edit number:', id);
        toastr.info('Edit number configuration coming soon');
    };

    // Suspend number function
    window.suspendNumber = function(id) {
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num) return;
        
        $('#confirmModalLabel').text('Suspend Number');
        $('#confirmModalMessage').text('Are you sure you want to suspend ' + num.number + '? The number will be hidden from sender pickers across all modules.');
        $('#confirmModalWarning').show();
        $('#confirmModalWarningText').text('This will affect Send Message, Inbox, and Email-to-SMS functionality.');
        $('#confirmModalBtn').removeClass('btn-primary btn-danger').addClass('btn-warning').text('Suspend');
        
        $('#confirmModalBtn').off('click').on('click', function() {
            // TODO: Backend API call
            num.status = 'suspended';
            renderTable();
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
            toastr.success('Number suspended successfully');
        });
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    };

    // Reactivate number function
    window.reactivateNumber = function(id) {
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num) return;
        
        $('#confirmModalLabel').text('Reactivate Number');
        $('#confirmModalMessage').text('Are you sure you want to reactivate ' + num.number + '?');
        $('#confirmModalWarning').hide();
        $('#confirmModalBtn').removeClass('btn-warning btn-danger').addClass('btn-primary').text('Reactivate');
        
        $('#confirmModalBtn').off('click').on('click', function() {
            // TODO: Backend API call
            num.status = 'active';
            renderTable();
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
            toastr.success('Number reactivated successfully');
        });
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    };

    // Release number function
    window.releaseNumber = function(id) {
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num) return;
        
        $('#confirmModalLabel').text('Release Number');
        $('#confirmModalMessage').text('Are you sure you want to release ' + num.number + '? This action cannot be undone.');
        $('#confirmModalWarning').show();
        $('#confirmModalWarningText').text('You will stop being billed for this number at the end of the current billing period. The number will become available for other customers.');
        $('#confirmModalBtn').removeClass('btn-primary btn-warning').addClass('btn-danger').text('Release Number');
        
        $('#confirmModalBtn').off('click').on('click', function() {
            // TODO: Backend API call
            numbersData = numbersData.filter(function(n) { return n.id !== id; });
            renderTable();
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
            toastr.success('Number released successfully');
        });
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    };

    // Initial render
    renderTable();
});
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Email-to-SMS')

@push('styles')
<style>
.email-sms-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.email-sms-header h2 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.email-sms-header p {
    margin: 0;
    color: #6c757d;
}
.table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: visible;
}
.table-container .dropdown-menu {
    z-index: 1050;
}
.api-table {
    width: 100%;
    margin: 0;
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
.api-table thead th:first-child { width: 13%; }
.api-table thead th:nth-child(2) { width: 13%; }
.api-table thead th:nth-child(3) { width: 18%; }
.api-table thead th:nth-child(4) { width: 10%; }
.api-table thead th:nth-child(5) { width: 14%; }
.api-table thead th:nth-child(6) { width: 8%; }
.api-table thead th:nth-child(7) { width: 10%; }
.api-table thead th:last-child { width: 7%; text-align: right; }
.api-table thead th:hover {
    background: #e9ecef;
}
.api-table thead th i.sort-icon {
    margin-left: 0.25rem;
    opacity: 0.5;
}
.api-table thead th.sorted i.sort-icon {
    opacity: 1;
    color: #1e3a5f;
}
.api-table tbody tr {
    border-bottom: 1px solid #e9ecef;
}
.api-table tbody tr:last-child {
    border-bottom: none;
}
.api-table tbody tr:hover {
    background: #f8f9fa;
}
.api-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    font-size: 0.85rem;
}
.email-sms-name {
    font-weight: 500;
    color: #343a40;
}
.email-address-display {
    font-size: 0.75rem;
    background: rgba(30, 58, 95, 0.08);
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
    color: #1e3a5f;
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.api-table tbody td {
    overflow: hidden;
    text-overflow: ellipsis;
}
.badge-live-status {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-suspended {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-archived {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-type-standard {
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
}
.badge-type-contactlist {
    background: rgba(74, 144, 217, 0.15);
    color: #4a90d9;
}
.action-menu-btn {
    background: none;
    border: none;
    padding: 0.25rem 0.5rem;
    color: #6c757d;
    cursor: pointer;
}
.action-menu-btn:hover {
    color: #1e3a5f;
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
.filter-panel {
    background-color: rgba(30, 58, 95, 0.05);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
.multiselect-dropdown .dropdown-menu {
    max-height: 250px;
    overflow-y: auto;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.75rem;
}
.filter-chip .remove-chip {
    margin-left: 0.25rem;
    cursor: pointer;
    opacity: 0.7;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
}
.account-link {
    color: #1e3a5f;
    text-decoration: none;
    font-weight: 500;
}
.account-link:hover {
    text-decoration: underline;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Assets</a></li>
            <li class="breadcrumb-item active">Email-to-SMS</li>
        </ol>
    </div>

    <div class="email-sms-header">
        <div>
            <h2>Email-to-SMS Overview</h2>
            <p>Global view of all Email-to-SMS configurations across customer accounts</p>
        </div>
    </div>

    <div class="collapse mb-3" id="filtersPanel">
        <div class="filter-panel">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Account</label>
                    <select class="form-select form-select-sm" id="filterAccount">
                        <option value="">All Accounts</option>
                    </select>
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
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Standard" id="typeStandard"><label class="form-check-label small" for="typeStandard">Standard</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Contact List" id="typeContactList"><label class="form-check-label small" for="typeContactList">Contact List</label></div>
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
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Active" id="statusActive"><label class="form-check-label small" for="statusActive">Active</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Suspended" id="statusSuspended"><label class="form-check-label small" for="statusSuspended">Suspended</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Archived" id="statusArchived"><label class="form-check-label small" for="statusArchived">Archived</label></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Created From</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold">Created To</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateTo">
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm" id="btnApplyFilters" style="background: #1e3a5f; color: white;">
                        <i class="fas fa-check me-1"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                        <i class="fas fa-undo me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3" style="border: 1px solid #e0e6ed; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <div class="input-group" style="width: 320px;">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 ps-0" id="quickSearchInput" placeholder="Search by name, email, or account...">
                    </div>
                    <div id="activeFiltersChips" class="d-flex flex-wrap gap-1"></div>
                </div>
                <button type="button" class="btn btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border: 1px solid #6f42c1; color: #6f42c1; background: transparent;">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>
    </div>

    <div class="table-container" id="addressesTableContainer">
        <div class="table-responsive">
            <table class="table api-table mb-0">
                <thead>
                    <tr>
                        <th data-sort="account">Account <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="name">Name <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="emails">Allowed Email Addresses</th>
                        <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="reportingGroup">Reporting Group <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="created">Created <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="addressesTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <div class="empty-state" id="emptyStateAddresses" style="display: none;">
        <div class="empty-state-icon">
            <i class="fas fa-at"></i>
        </div>
        <h4>No Email-to-SMS Configurations Found</h4>
        <p>There are no Email-to-SMS configurations matching your filters.</p>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">
            Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> configurations
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0" id="addressesPagination">
            </ul>
        </nav>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="detailsDrawer" style="width: 420px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="offcanvas-title" id="drawerTitle">Configuration Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-4">
            <h6 class="text-muted mb-3">General</h6>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Account</div>
                <div class="col-7 small fw-medium" id="drawerAccount">-</div>
            </div>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Name</div>
                <div class="col-7 small" id="drawerName">-</div>
            </div>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Description</div>
                <div class="col-7 small" id="drawerDescription">-</div>
            </div>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Type</div>
                <div class="col-7 small" id="drawerType">-</div>
            </div>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Status</div>
                <div class="col-7" id="drawerStatus">-</div>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Email Settings</h6>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Allowed Senders</div>
                <div class="col-7 small" id="drawerAllowedSenders">-</div>
            </div>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Originating Emails</div>
                <div class="col-7 small" id="drawerOriginatingEmails">-</div>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Dates</h6>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Created</div>
                <div class="col-7 small" id="drawerCreated">-</div>
            </div>
            <div class="row mb-2">
                <div class="col-5 text-muted small">Last Updated</div>
                <div class="col-7 small" id="drawerLastUpdated">-</div>
            </div>
        </div>
        
        <div class="d-grid gap-2 mt-4">
            <a href="#" id="drawerEditBtn" class="btn btn-sm" style="background: #1e3a5f; color: white;">
                <i class="fas fa-edit me-1"></i> Edit Configuration
            </a>
        </div>
    </div>
</div>

<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suspendModalTitle">Suspend Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="suspendModalMessage">Are you sure you want to suspend this configuration?</p>
                <p class="text-muted small" id="suspendModalDescription">Suspended configurations will no longer process incoming emails.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="btnConfirmSuspend">
                    <i class="fas fa-pause me-1"></i> Suspend
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/services/email-to-sms-service.js') }}"></script>
<script>
var allConfigurations = [];
var filteredConfigurations = [];
var currentSort = { field: 'created', direction: 'desc' };
var currentPage = 1;
var pageSize = 20;
var activeFilters = {};

var mockAccounts = [
    { id: 'acc-001', name: 'Acme Healthcare Ltd' },
    { id: 'acc-002', name: 'TechStart Solutions' },
    { id: 'acc-003', name: 'Global Retail Inc' },
    { id: 'acc-004', name: 'City Medical Centre' },
    { id: 'acc-005', name: 'Finance Pro Services' }
];

function loadAdminEmailToSmsData() {
    EmailToSmsService.listOverviewAddresses({}).then(function(response) {
        if (response.success) {
            allConfigurations = response.data.map(function(item, index) {
                var accountIndex = index % mockAccounts.length;
                return Object.assign({}, item, {
                    accountId: mockAccounts[accountIndex].id,
                    accountName: mockAccounts[accountIndex].name
                });
            });
            
            populateAccountFilter();
            applyFiltersAndRender();
        }
    }).catch(function(err) {
        console.error('Failed to load Email-to-SMS data:', err);
    });
}

function populateAccountFilter() {
    var select = document.getElementById('filterAccount');
    select.innerHTML = '<option value="">All Accounts</option>';
    
    var uniqueAccounts = {};
    allConfigurations.forEach(function(config) {
        if (!uniqueAccounts[config.accountId]) {
            uniqueAccounts[config.accountId] = config.accountName;
        }
    });
    
    Object.keys(uniqueAccounts).forEach(function(accountId) {
        var option = document.createElement('option');
        option.value = accountId;
        option.textContent = uniqueAccounts[accountId];
        select.appendChild(option);
    });
}

function applyFiltersAndRender() {
    var searchQuery = document.getElementById('quickSearchInput').value.toLowerCase().trim();
    var accountFilter = document.getElementById('filterAccount').value;
    
    var typeCheckboxes = document.querySelectorAll('[data-filter="types"] input:checked');
    var statusCheckboxes = document.querySelectorAll('[data-filter="statuses"] input:checked');
    
    var selectedTypes = Array.from(typeCheckboxes).map(function(cb) { return cb.value; });
    var selectedStatuses = Array.from(statusCheckboxes).map(function(cb) { return cb.value; });
    
    var dateFrom = document.getElementById('filterDateFrom').value;
    var dateTo = document.getElementById('filterDateTo').value;
    
    filteredConfigurations = allConfigurations.filter(function(config) {
        if (searchQuery) {
            var searchMatch = 
                config.name.toLowerCase().includes(searchQuery) ||
                config.accountName.toLowerCase().includes(searchQuery) ||
                (config.allowedSenders && config.allowedSenders.some(function(e) { return e.toLowerCase().includes(searchQuery); })) ||
                (config.originatingEmails && config.originatingEmails.some(function(e) { return e.toLowerCase().includes(searchQuery); }));
            if (!searchMatch) return false;
        }
        
        if (accountFilter && config.accountId !== accountFilter) return false;
        if (selectedTypes.length > 0 && !selectedTypes.includes(config.type)) return false;
        if (selectedStatuses.length > 0 && !selectedStatuses.includes(config.status)) return false;
        
        if (dateFrom && config.created < dateFrom) return false;
        if (dateTo && config.created > dateTo) return false;
        
        return true;
    });
    
    sortConfigurations();
    currentPage = 1;
    renderTable();
    updateFilterChips();
}

function sortConfigurations() {
    filteredConfigurations.sort(function(a, b) {
        var aVal = a[currentSort.field] || '';
        var bVal = b[currentSort.field] || '';
        
        if (typeof aVal === 'string') aVal = aVal.toLowerCase();
        if (typeof bVal === 'string') bVal = bVal.toLowerCase();
        
        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
}

function renderTable() {
    var tbody = document.getElementById('addressesTableBody');
    tbody.innerHTML = '';
    
    var start = (currentPage - 1) * pageSize;
    var end = start + pageSize;
    var pageData = filteredConfigurations.slice(start, end);
    
    if (pageData.length === 0) {
        document.getElementById('addressesTableContainer').style.display = 'none';
        document.getElementById('emptyStateAddresses').style.display = 'block';
    } else {
        document.getElementById('addressesTableContainer').style.display = 'block';
        document.getElementById('emptyStateAddresses').style.display = 'none';
    }
    
    pageData.forEach(function(config) {
        var statusBadge = '';
        if (config.status === 'Active') {
            statusBadge = '<span class="badge badge-live-status">Active</span>';
        } else if (config.status === 'Suspended') {
            statusBadge = '<span class="badge badge-suspended">Suspended</span>';
        } else {
            statusBadge = '<span class="badge badge-archived">Archived</span>';
        }
        
        var typeBadge = config.type === 'Standard' 
            ? '<span class="badge badge-type-standard">Standard</span>'
            : '<span class="badge badge-type-contactlist">Contact List</span>';
        
        var allowedEmails = config.allowedSenders || [];
        var emailsDisplay = allowedEmails.slice(0, 2).map(function(email) {
            return '<code class="email-address-display d-block mb-1">' + escapeHtml(email) + '</code>';
        }).join('');
        if (allowedEmails.length > 2) {
            emailsDisplay += '<span class="text-muted small">+' + (allowedEmails.length - 2) + ' more</span>';
        } else if (allowedEmails.length === 0) {
            emailsDisplay = '<span class="text-muted">No allowed senders</span>';
        }
        
        var reportingGroup = config.reportingGroup ? config.reportingGroup : '<span class="text-muted">-</span>';
        
        var row = '<tr data-id="' + config.id + '">' +
            '<td><a href="#" class="account-link" data-account-id="' + config.accountId + '">' + escapeHtml(config.accountName) + '</a></td>' +
            '<td><span class="email-sms-name">' + escapeHtml(config.name) + '</span></td>' +
            '<td>' + emailsDisplay + '</td>' +
            '<td>' + typeBadge + '</td>' +
            '<td>' + reportingGroup + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + config.created + '</td>' +
            '<td class="text-end">' +
                '<div class="dropdown">' +
                    '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
                        '<i class="fas fa-ellipsis-v"></i>' +
                    '</button>' +
                    '<ul class="dropdown-menu dropdown-menu-end">' +
                        '<li><a class="dropdown-item view-config" href="#" data-id="' + config.id + '"><i class="fas fa-eye me-2"></i> View</a></li>' +
                        '<li><a class="dropdown-item edit-config" href="#" data-id="' + config.id + '"><i class="fas fa-edit me-2"></i> Edit</a></li>' +
                        (config.status === 'Active' 
                            ? '<li><a class="dropdown-item suspend-config" href="#" data-id="' + config.id + '"><i class="fas fa-pause me-2"></i> Suspend</a></li>'
                            : '<li><a class="dropdown-item reactivate-config" href="#" data-id="' + config.id + '"><i class="fas fa-play me-2"></i> Reactivate</a></li>') +
                    '</ul>' +
                '</div>' +
            '</td>' +
        '</tr>';
        
        tbody.innerHTML += row;
    });
    
    document.getElementById('showingCount').textContent = pageData.length;
    document.getElementById('totalCount').textContent = filteredConfigurations.length;
    
    renderPagination();
    bindRowActions();
}

function renderPagination() {
    var totalPages = Math.ceil(filteredConfigurations.length / pageSize);
    var pagination = document.getElementById('addressesPagination');
    pagination.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    var prevDisabled = currentPage === 1 ? 'disabled' : '';
    pagination.innerHTML += '<li class="page-item ' + prevDisabled + '"><a class="page-link" href="#" data-page="' + (currentPage - 1) + '">&laquo;</a></li>';
    
    for (var i = 1; i <= totalPages && i <= 5; i++) {
        var active = i === currentPage ? 'active' : '';
        pagination.innerHTML += '<li class="page-item ' + active + '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
    }
    
    var nextDisabled = currentPage === totalPages ? 'disabled' : '';
    pagination.innerHTML += '<li class="page-item ' + nextDisabled + '"><a class="page-link" href="#" data-page="' + (currentPage + 1) + '">&raquo;</a></li>';
    
    pagination.querySelectorAll('a[data-page]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var page = parseInt(this.dataset.page);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                renderTable();
            }
        });
    });
}

function updateFilterChips() {
    var container = document.getElementById('activeFiltersChips');
    container.innerHTML = '';
    
    var accountFilter = document.getElementById('filterAccount');
    if (accountFilter.value) {
        var accountName = accountFilter.options[accountFilter.selectedIndex].text;
        container.innerHTML += '<span class="filter-chip">Account: ' + accountName + ' <i class="fas fa-times remove-chip" data-filter="account"></i></span>';
    }
    
    var typeCheckboxes = document.querySelectorAll('[data-filter="types"] input:checked');
    typeCheckboxes.forEach(function(cb) {
        container.innerHTML += '<span class="filter-chip">Type: ' + cb.value + ' <i class="fas fa-times remove-chip" data-filter="type" data-value="' + cb.value + '"></i></span>';
    });
    
    var statusCheckboxes = document.querySelectorAll('[data-filter="statuses"] input:checked');
    statusCheckboxes.forEach(function(cb) {
        container.innerHTML += '<span class="filter-chip">Status: ' + cb.value + ' <i class="fas fa-times remove-chip" data-filter="status" data-value="' + cb.value + '"></i></span>';
    });
    
    container.querySelectorAll('.remove-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            var filterType = this.dataset.filter;
            var value = this.dataset.value;
            
            if (filterType === 'account') {
                document.getElementById('filterAccount').value = '';
            } else if (filterType === 'type') {
                document.querySelector('[data-filter="types"] input[value="' + value + '"]').checked = false;
            } else if (filterType === 'status') {
                document.querySelector('[data-filter="statuses"] input[value="' + value + '"]').checked = false;
            }
            
            applyFiltersAndRender();
        });
    });
}

function bindRowActions() {
    document.querySelectorAll('.view-config').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            openDetailsDrawer(id);
        });
    });
    
    document.querySelectorAll('.edit-config').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            var config = allConfigurations.find(function(c) { return c.id === id; });
            if (config) {
                var editUrl = config.sourceType === 'standard' 
                    ? '/admin/assets/email-to-sms/standard/' + config.sourceId + '/edit'
                    : '/admin/assets/email-to-sms/contact-list/' + config.sourceId + '/edit';
                window.location.href = editUrl;
            }
        });
    });
    
    document.querySelectorAll('.suspend-config').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            var config = allConfigurations.find(function(c) { return c.id === id; });
            if (config) {
                openSuspendModal(config, 'suspend');
            }
        });
    });
    
    document.querySelectorAll('.reactivate-config').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            var config = allConfigurations.find(function(c) { return c.id === id; });
            if (config) {
                openSuspendModal(config, 'reactivate');
            }
        });
    });
}

function openDetailsDrawer(id) {
    var config = allConfigurations.find(function(c) { return c.id === id; });
    if (!config) return;
    
    document.getElementById('drawerTitle').textContent = config.name;
    document.getElementById('drawerAccount').textContent = config.accountName;
    document.getElementById('drawerName').textContent = config.name;
    document.getElementById('drawerDescription').textContent = config.description || '-';
    document.getElementById('drawerType').textContent = config.type;
    
    var statusHtml = config.status === 'Active' 
        ? '<span class="badge badge-live-status">Active</span>'
        : config.status === 'Suspended'
            ? '<span class="badge badge-suspended">Suspended</span>'
            : '<span class="badge badge-archived">Archived</span>';
    document.getElementById('drawerStatus').innerHTML = statusHtml;
    
    var allowedSenders = config.allowedSenders || [];
    document.getElementById('drawerAllowedSenders').innerHTML = allowedSenders.length > 0 
        ? allowedSenders.map(function(e) { return '<code class="d-block mb-1">' + escapeHtml(e) + '</code>'; }).join('')
        : '<span class="text-muted">All senders allowed</span>';
    
    var originatingEmails = config.originatingEmails || [];
    document.getElementById('drawerOriginatingEmails').innerHTML = originatingEmails.length > 0 
        ? originatingEmails.map(function(e) { return '<code class="d-block mb-1">' + escapeHtml(e) + '</code>'; }).join('')
        : '<span class="text-muted">Not configured</span>';
    
    document.getElementById('drawerCreated').textContent = config.created || '-';
    document.getElementById('drawerLastUpdated').textContent = config.lastUsed || '-';
    
    var editUrl = config.sourceType === 'standard' 
        ? '/admin/assets/email-to-sms/standard/' + config.sourceId + '/edit'
        : '/admin/assets/email-to-sms/contact-list/' + config.sourceId + '/edit';
    document.getElementById('drawerEditBtn').href = editUrl;
    
    var drawer = new bootstrap.Offcanvas(document.getElementById('detailsDrawer'));
    drawer.show();
}

function openSuspendModal(config, action) {
    var modal = document.getElementById('suspendModal');
    var title = document.getElementById('suspendModalTitle');
    var message = document.getElementById('suspendModalMessage');
    var desc = document.getElementById('suspendModalDescription');
    var btn = document.getElementById('btnConfirmSuspend');
    
    if (action === 'suspend') {
        title.textContent = 'Suspend Configuration';
        message.innerHTML = 'Are you sure you want to suspend <strong>' + escapeHtml(config.name) + '</strong>?';
        desc.textContent = 'Suspended configurations will no longer process incoming emails.';
        btn.className = 'btn btn-danger btn-sm';
        btn.innerHTML = '<i class="fas fa-pause me-1"></i> Suspend';
    } else {
        title.textContent = 'Reactivate Configuration';
        message.innerHTML = 'Are you sure you want to reactivate <strong>' + escapeHtml(config.name) + '</strong>?';
        desc.textContent = 'Once reactivated, emails sent to this address will resume triggering SMS messages.';
        btn.className = 'btn btn-success btn-sm';
        btn.innerHTML = '<i class="fas fa-play me-1"></i> Reactivate';
    }
    
    modal.dataset.configId = config.id;
    modal.dataset.action = action;
    
    new bootstrap.Modal(modal).show();
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showSuccessToast(message) {
    console.log('Success:', message);
}

function showInfoToast(message) {
    console.log('Info:', message);
}

document.addEventListener('DOMContentLoaded', function() {
    loadAdminEmailToSmsData();
    
    document.getElementById('quickSearchInput').addEventListener('input', function() {
        applyFiltersAndRender();
    });
    
    document.getElementById('btnApplyFilters').addEventListener('click', function() {
        applyFiltersAndRender();
    });
    
    document.getElementById('btnResetFilters').addEventListener('click', function() {
        document.getElementById('filterAccount').value = '';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) {
            cb.checked = false;
        });
        applyFiltersAndRender();
    });
    
    document.querySelectorAll('.api-table thead th[data-sort]').forEach(function(th) {
        th.addEventListener('click', function() {
            var field = this.dataset.sort;
            if (currentSort.field === field) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.field = field;
                currentSort.direction = 'asc';
            }
            
            document.querySelectorAll('.api-table thead th').forEach(function(h) {
                h.classList.remove('sorted');
                h.querySelector('.sort-icon')?.classList.remove('fa-sort-up', 'fa-sort-down');
                h.querySelector('.sort-icon')?.classList.add('fa-sort');
            });
            
            this.classList.add('sorted');
            var icon = this.querySelector('.sort-icon');
            if (icon) {
                icon.classList.remove('fa-sort');
                icon.classList.add(currentSort.direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
            }
            
            sortConfigurations();
            renderTable();
        });
    });
    
    document.querySelectorAll('.select-all-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdown = this.closest('.multiselect-dropdown');
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                cb.checked = true;
            });
        });
    });
    
    document.querySelectorAll('.clear-all-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdown = this.closest('.multiselect-dropdown');
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                cb.checked = false;
            });
        });
    });
    
    document.getElementById('btnConfirmSuspend').addEventListener('click', function() {
        var modal = document.getElementById('suspendModal');
        var configId = modal.dataset.configId;
        var action = modal.dataset.action;
        
        var config = allConfigurations.find(function(c) { return c.id === configId; });
        if (config) {
            config.status = action === 'suspend' ? 'Suspended' : 'Active';
            applyFiltersAndRender();
            showSuccessToast('Configuration ' + (action === 'suspend' ? 'suspended' : 'reactivated') + ' successfully');
        }
        
        bootstrap.Modal.getInstance(modal).hide();
    });
});
</script>
@endpush

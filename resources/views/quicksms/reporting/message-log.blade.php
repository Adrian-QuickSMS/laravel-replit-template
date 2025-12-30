@extends('layouts.quicksms')

@section('title', 'Message Log')

@push('styles')
<style>
#messageLogTable tbody tr {
    cursor: pointer;
}
#messageLogTable tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.05);
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    background-color: #e9ecef;
    border-radius: 1rem;
    font-size: 0.75rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}
.filter-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
}
.bg-primary-light {
    background-color: rgba(111, 66, 193, 0.1);
}
.bg-info-light {
    background-color: rgba(23, 162, 184, 0.1);
}
.drag-handle {
    cursor: grab;
    opacity: 0.5;
}
.drag-handle:hover {
    opacity: 1;
}
#columnConfigMenu .form-check {
    padding-left: 1.5em;
}
#columnConfigMenu .form-check-label {
    display: flex;
    align-items: center;
}
#tableContainer thead th {
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}
#messageLogTable tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.05);
}
.content-masked {
    letter-spacing: 2px;
    font-family: monospace;
}
.date-preset-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
    background: #fff;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.date-preset-btn:hover {
    background: #f8f9fa;
    border-color: #6f42c1;
}
.date-preset-btn.active {
    background: #6f42c1;
    color: #fff;
    border-color: #6f42c1;
}
.multi-value-input {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    padding: 0.25rem;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 38px;
    background: #fff;
}
.multi-value-input:focus-within {
    border-color: #6f42c1;
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}
.multi-value-input input {
    border: none;
    outline: none;
    flex: 1;
    min-width: 100px;
    font-size: 0.875rem;
    padding: 0.25rem;
}
.multi-value-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.5rem;
    background: #e9ecef;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}
.multi-value-tag .remove-tag {
    margin-left: 0.25rem;
    cursor: pointer;
    opacity: 0.7;
}
.multi-value-tag .remove-tag:hover {
    opacity: 1;
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
.predictive-input-wrapper {
    position: relative;
}
.predictive-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 150px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}
.predictive-suggestions.show {
    display: block;
}
.predictive-suggestion {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 0.875rem;
}
.predictive-suggestion:hover {
    background: #f8f9fa;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reporting') }}">Reporting</a></li>
            <li class="breadcrumb-item active">Message Log</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="card-title mb-2 mb-md-0">Message Log</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="messageSearch" placeholder="Search by recipient, sender, message content...">
                        </div>
                    </div>

                    <div class="collapse mb-3" id="filtersPanel">
                        <div class="card card-body bg-light border">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label small fw-bold">Date Range</label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" class="form-control form-control-sm" id="filterDateFrom" style="flex: 1;">
                                        <span class="align-self-center text-muted small">to</span>
                                        <input type="date" class="form-control form-control-sm" id="filterDateTo" style="flex: 1;">
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="date-preset-btn" data-preset="today">Today</button>
                                        <button type="button" class="date-preset-btn" data-preset="yesterday">Yesterday</button>
                                        <button type="button" class="date-preset-btn" data-preset="7days">Last 7 Days</button>
                                        <button type="button" class="date-preset-btn" data-preset="30days">Last 30 Days</button>
                                        <button type="button" class="date-preset-btn" data-preset="thismonth">This Month</button>
                                        <button type="button" class="date-preset-btn" data-preset="lastmonth">Last Month</button>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Sub Account</label>
                                    <div class="multiselect-dropdown dropdown">
                                        <button class="form-select form-select-sm multiselect-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="filterSubAccountToggle">
                                            <span class="toggle-text">All Sub Accounts</span>
                                        </button>
                                        <div class="dropdown-menu p-2" id="filterSubAccountMenu">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="main" id="subaccount-main">
                                                <label class="form-check-label small" for="subaccount-main">Main Account</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="marketing" id="subaccount-marketing">
                                                <label class="form-check-label small" for="subaccount-marketing">Marketing Team</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="support" id="subaccount-support">
                                                <label class="form-check-label small" for="subaccount-support">Support Team</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="sales" id="subaccount-sales">
                                                <label class="form-check-label small" for="subaccount-sales">Sales Team</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">User</label>
                                    <div class="multiselect-dropdown dropdown">
                                        <button class="form-select form-select-sm multiselect-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="filterUserToggle">
                                            <span class="toggle-text">All Users</span>
                                        </button>
                                        <div class="dropdown-menu p-2" id="filterUserMenu">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="john" id="user-john">
                                                <label class="form-check-label small" for="user-john">John Smith</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="sarah" id="user-sarah">
                                                <label class="form-check-label small" for="user-sarah">Sarah Johnson</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="mike" id="user-mike">
                                                <label class="form-check-label small" for="user-mike">Mike Williams</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="emma" id="user-emma">
                                                <label class="form-check-label small" for="user-emma">Emma Davis</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Origin</label>
                                    <div class="multiselect-dropdown dropdown">
                                        <button class="form-select form-select-sm multiselect-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="filterOriginToggle">
                                            <span class="toggle-text">All Origins</span>
                                        </button>
                                        <div class="dropdown-menu p-2" id="filterOriginMenu">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="portal" id="origin-portal">
                                                <label class="form-check-label small" for="origin-portal">Portal</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="api" id="origin-api">
                                                <label class="form-check-label small" for="origin-api">API</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="email-to-sms" id="origin-email">
                                                <label class="form-check-label small" for="origin-email">Email-to-SMS</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="integration" id="origin-integration">
                                                <label class="form-check-label small" for="origin-integration">Integration</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label small fw-bold">Mobile Number</label>
                                    <div class="multi-value-input" id="filterMobileContainer">
                                        <input type="text" placeholder="Enter number and press Enter..." id="filterMobileInput">
                                    </div>
                                    <small class="text-muted">Enter multiple numbers separated by Enter</small>
                                </div>
                                
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">SenderID</label>
                                    <div class="predictive-input-wrapper">
                                        <input type="text" class="form-control form-control-sm" id="filterSenderId" placeholder="Type to search..." autocomplete="off">
                                        <div class="predictive-suggestions" id="senderIdSuggestions">
                                            <div class="predictive-suggestion" data-value="QuickSMS">QuickSMS</div>
                                            <div class="predictive-suggestion" data-value="ALERTS">ALERTS</div>
                                            <div class="predictive-suggestion" data-value="PROMO">PROMO</div>
                                            <div class="predictive-suggestion" data-value="INFO">INFO</div>
                                            <div class="predictive-suggestion" data-value="NOTIFY">NOTIFY</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Message Status</label>
                                    <div class="multiselect-dropdown dropdown">
                                        <button class="form-select form-select-sm multiselect-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="filterStatusToggle">
                                            <span class="toggle-text">All Statuses</span>
                                        </button>
                                        <div class="dropdown-menu p-2" id="filterStatusMenu">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="delivered" id="status-delivered">
                                                <label class="form-check-label small" for="status-delivered">Delivered</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="pending" id="status-pending">
                                                <label class="form-check-label small" for="status-pending">Pending</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="undeliverable" id="status-undeliverable">
                                                <label class="form-check-label small" for="status-undeliverable">Undeliverable</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="rejected" id="status-rejected">
                                                <label class="form-check-label small" for="status-rejected">Rejected</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Country</label>
                                    <div class="multiselect-dropdown dropdown">
                                        <button class="form-select form-select-sm multiselect-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="filterCountryToggle">
                                            <span class="toggle-text">All Countries</span>
                                        </button>
                                        <div class="dropdown-menu p-2" id="filterCountryMenu">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="uk" id="country-uk">
                                                <label class="form-check-label small" for="country-uk">United Kingdom</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="us" id="country-us">
                                                <label class="form-check-label small" for="country-us">United States</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="de" id="country-de">
                                                <label class="form-check-label small" for="country-de">Germany</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="fr" id="country-fr">
                                                <label class="form-check-label small" for="country-fr">France</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="es" id="country-es">
                                                <label class="form-check-label small" for="country-es">Spain</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="ie" id="country-ie">
                                                <label class="form-check-label small" for="country-ie">Ireland</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Message Type</label>
                                    <div class="multiselect-dropdown dropdown">
                                        <button class="form-select form-select-sm multiselect-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="filterTypeToggle">
                                            <span class="toggle-text">All Types</span>
                                        </button>
                                        <div class="dropdown-menu p-2" id="filterTypeMenu">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="sms" id="type-sms">
                                                <label class="form-check-label small" for="type-sms">SMS</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="rcs-basic" id="type-rcs-basic">
                                                <label class="form-check-label small" for="type-rcs-basic">RCS Basic</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="rcs-rich" id="type-rcs-rich">
                                                <label class="form-check-label small" for="type-rcs-rich">RCS Rich</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label small fw-bold">Message ID</label>
                                    <div class="multi-value-input" id="filterMessageIdContainer">
                                        <input type="text" placeholder="Enter Message ID and press Enter..." id="filterMessageIdInput">
                                    </div>
                                    <small class="text-muted">Enter multiple IDs separated by Enter</small>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-3 border-top d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilters">
                                    <i class="fas fa-check me-1"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                                    <i class="fas fa-undo me-1"></i> Reset Filters
                                </button>
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

                    <div class="mb-3" id="summaryBar" style="display: none;">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="card">
                                    <div class="card-body py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="bg-primary-light rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                    <i class="fas fa-envelope text-primary"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 text-muted small">Total Messages</p>
                                                <h4 class="mb-0 fw-bold" id="summaryTotal">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card">
                                    <div class="card-body py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="bg-info-light rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                    <i class="fas fa-puzzle-piece text-info"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 text-muted small">Total Parts/Fragments</p>
                                                <h4 class="mb-0 fw-bold" id="summaryParts">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted small" id="rowCountInfo">
                            <span id="renderedCount">0</span> rows loaded (max 10,000)
                        </div>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#columnSettingsModal">
                            <i class="fas fa-cog me-1"></i> Column Settings
                        </button>
                    </div>
                    
                    <div class="table-responsive" id="tableContainer" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover mb-0" id="messageLogTable">
                            <thead class="sticky-top bg-white" style="z-index: 10;">
                                <tr id="tableHeaderRow">
                                    <th data-column="mobileNumber">Mobile Number</th>
                                    <th data-column="senderId">SenderID</th>
                                    <th data-column="status">
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Status <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortTable('status', 'asc'); return false;"><i class="fas fa-check-circle me-2 text-success"></i> Delivered First</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortTable('status', 'desc'); return false;"><i class="fas fa-times-circle me-2 text-danger"></i> Failed First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th data-column="sentTime">
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Sent Time <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortTable('sentTime', 'desc'); return false;"><i class="fas fa-calendar-alt me-2"></i> Most Recent</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortTable('sentTime', 'asc'); return false;"><i class="fas fa-calendar me-2"></i> Oldest First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th data-column="deliveryTime">Delivery Time</th>
                                    <th data-column="completedTime">Completed Time</th>
                                    <th data-column="cost">Cost</th>
                                    <th data-column="messageType" class="d-none">Message Type</th>
                                    <th data-column="subAccount" class="d-none">Sub Account</th>
                                    <th data-column="user" class="d-none">User</th>
                                    <th data-column="origin" class="d-none">Origin</th>
                                    <th data-column="country" class="d-none">Country</th>
                                    <th data-column="parts" class="d-none">Fragments/Parts</th>
                                    <th data-column="encoding" class="d-none">Encoding</th>
                                    <th data-column="messageId" class="d-none">Message ID</th>
                                    <th data-column="content" class="d-none">Content</th>
                                </tr>
                            </thead>
                            <tbody id="messageLogTableBody">
                                <tr>
                                    <td class="py-2" data-column="mobileNumber"><span class="mobile-masked">+44 77** ***456</span></td>
                                    <td class="py-2" data-column="senderId">QuickSMS</td>
                                    <td class="py-2" data-column="status"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2" data-column="sentTime">30/12/2024 14:23</td>
                                    <td class="py-2" data-column="deliveryTime">30/12/2024 14:23</td>
                                    <td class="py-2" data-column="completedTime">30/12/2024 14:23</td>
                                    <td class="py-2" data-column="cost">£0.035</td>
                                    <td class="py-2 d-none" data-column="messageType"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2 d-none" data-column="subAccount">Main Account</td>
                                    <td class="py-2 d-none" data-column="user">John Smith</td>
                                    <td class="py-2 d-none" data-column="origin">Portal</td>
                                    <td class="py-2 d-none" data-column="country">UK</td>
                                    <td class="py-2 d-none" data-column="parts">1</td>
                                    <td class="py-2 d-none" data-column="encoding"><span class="badge bg-light text-dark border">GSM-7</span></td>
                                    <td class="py-2 d-none" data-column="messageId">MSG-001234567</td>
                                    <td class="py-2 d-none" data-column="content"><span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span></td>
                                </tr>
                                <tr>
                                    <td class="py-2" data-column="mobileNumber"><span class="mobile-masked">+44 78** ***789</span></td>
                                    <td class="py-2" data-column="senderId">ALERTS</td>
                                    <td class="py-2" data-column="status"><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td class="py-2" data-column="sentTime">30/12/2024 14:21</td>
                                    <td class="py-2" data-column="deliveryTime">-</td>
                                    <td class="py-2" data-column="completedTime">-</td>
                                    <td class="py-2" data-column="cost">£0.035</td>
                                    <td class="py-2 d-none" data-column="messageType"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2 d-none" data-column="subAccount">Marketing Team</td>
                                    <td class="py-2 d-none" data-column="user">Sarah Johnson</td>
                                    <td class="py-2 d-none" data-column="origin">API</td>
                                    <td class="py-2 d-none" data-column="country">UK</td>
                                    <td class="py-2 d-none" data-column="parts">1</td>
                                    <td class="py-2 d-none" data-column="encoding"><span class="badge bg-light text-dark border">GSM-7</span></td>
                                    <td class="py-2 d-none" data-column="messageId">MSG-001234568</td>
                                    <td class="py-2 d-none" data-column="content"><span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span></td>
                                </tr>
                                <tr>
                                    <td class="py-2" data-column="mobileNumber"><span class="mobile-masked">+44 79** ***123</span></td>
                                    <td class="py-2" data-column="senderId">QuickSMS Brand</td>
                                    <td class="py-2" data-column="status"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2" data-column="sentTime">30/12/2024 14:18</td>
                                    <td class="py-2" data-column="deliveryTime">30/12/2024 14:18</td>
                                    <td class="py-2" data-column="completedTime">30/12/2024 14:18</td>
                                    <td class="py-2" data-column="cost">£0.025</td>
                                    <td class="py-2 d-none" data-column="messageType"><span class="badge bg-info">RCS Rich</span></td>
                                    <td class="py-2 d-none" data-column="subAccount">Main Account</td>
                                    <td class="py-2 d-none" data-column="user">John Smith</td>
                                    <td class="py-2 d-none" data-column="origin">Portal</td>
                                    <td class="py-2 d-none" data-column="country">UK</td>
                                    <td class="py-2 d-none" data-column="parts">1</td>
                                    <td class="py-2 d-none" data-column="encoding"><span class="badge bg-primary text-white">Unicode</span></td>
                                    <td class="py-2 d-none" data-column="messageId">MSG-001234569</td>
                                    <td class="py-2 d-none" data-column="content"><span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span></td>
                                </tr>
                                <tr>
                                    <td class="py-2" data-column="mobileNumber"><span class="mobile-masked">+44 77** ***321</span></td>
                                    <td class="py-2" data-column="senderId">QuickSMS</td>
                                    <td class="py-2" data-column="status"><span class="badge bg-danger">Undeliverable</span></td>
                                    <td class="py-2" data-column="sentTime">30/12/2024 14:15</td>
                                    <td class="py-2" data-column="deliveryTime">-</td>
                                    <td class="py-2" data-column="completedTime">30/12/2024 14:16</td>
                                    <td class="py-2" data-column="cost">£0.000</td>
                                    <td class="py-2 d-none" data-column="messageType"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2 d-none" data-column="subAccount">Support Team</td>
                                    <td class="py-2 d-none" data-column="user">Mike Williams</td>
                                    <td class="py-2 d-none" data-column="origin">Portal</td>
                                    <td class="py-2 d-none" data-column="country">UK</td>
                                    <td class="py-2 d-none" data-column="parts">1</td>
                                    <td class="py-2 d-none" data-column="encoding"><span class="badge bg-light text-dark border">GSM-7</span></td>
                                    <td class="py-2 d-none" data-column="messageId">MSG-001234570</td>
                                    <td class="py-2 d-none" data-column="content"><span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span></td>
                                </tr>
                                <tr>
                                    <td class="py-2" data-column="mobileNumber"><span class="mobile-masked">+44 78** ***654</span></td>
                                    <td class="py-2" data-column="senderId">QuickSMS</td>
                                    <td class="py-2" data-column="status"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2" data-column="sentTime">30/12/2024 14:12</td>
                                    <td class="py-2" data-column="deliveryTime">30/12/2024 14:12</td>
                                    <td class="py-2" data-column="completedTime">30/12/2024 14:12</td>
                                    <td class="py-2" data-column="cost">£0.070</td>
                                    <td class="py-2 d-none" data-column="messageType"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2 d-none" data-column="subAccount">Sales Team</td>
                                    <td class="py-2 d-none" data-column="user">Emma Davis</td>
                                    <td class="py-2 d-none" data-column="origin">Integration</td>
                                    <td class="py-2 d-none" data-column="country">UK</td>
                                    <td class="py-2 d-none" data-column="parts">2</td>
                                    <td class="py-2 d-none" data-column="encoding"><span class="badge bg-light text-dark border">GSM-7</span></td>
                                    <td class="py-2 d-none" data-column="messageId">MSG-001234571</td>
                                    <td class="py-2 d-none" data-column="content"><span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span></td>
                                </tr>
                                <tr>
                                    <td class="py-2" data-column="mobileNumber"><span class="mobile-masked">+44 79** ***987</span></td>
                                    <td class="py-2" data-column="senderId">QuickSMS Brand</td>
                                    <td class="py-2" data-column="status"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2" data-column="sentTime">30/12/2024 14:08</td>
                                    <td class="py-2" data-column="deliveryTime">30/12/2024 14:08</td>
                                    <td class="py-2" data-column="completedTime">30/12/2024 14:08</td>
                                    <td class="py-2" data-column="cost">£0.025</td>
                                    <td class="py-2 d-none" data-column="messageType"><span class="badge bg-info">RCS Basic</span></td>
                                    <td class="py-2 d-none" data-column="subAccount">Main Account</td>
                                    <td class="py-2 d-none" data-column="user">John Smith</td>
                                    <td class="py-2 d-none" data-column="origin">Portal</td>
                                    <td class="py-2 d-none" data-column="country">UK</td>
                                    <td class="py-2 d-none" data-column="parts">1</td>
                                    <td class="py-2 d-none" data-column="encoding"><span class="badge bg-primary text-white">Unicode</span></td>
                                    <td class="py-2 d-none" data-column="messageId">MSG-001234572</td>
                                    <td class="py-2 d-none" data-column="content"><span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span></td>
                                </tr>
                                <tr>
                                    <td class="py-2" data-column="mobileNumber"><span class="mobile-masked">+44 77** ***147</span></td>
                                    <td class="py-2" data-column="senderId">PROMO</td>
                                    <td class="py-2" data-column="status"><span class="badge bg-secondary">Rejected</span></td>
                                    <td class="py-2" data-column="sentTime">30/12/2024 14:05</td>
                                    <td class="py-2" data-column="deliveryTime">-</td>
                                    <td class="py-2" data-column="completedTime">30/12/2024 14:05</td>
                                    <td class="py-2" data-column="cost">£0.000</td>
                                    <td class="py-2 d-none" data-column="messageType"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2 d-none" data-column="subAccount">Marketing Team</td>
                                    <td class="py-2 d-none" data-column="user">Sarah Johnson</td>
                                    <td class="py-2 d-none" data-column="origin">Email-to-SMS</td>
                                    <td class="py-2 d-none" data-column="country">UK</td>
                                    <td class="py-2 d-none" data-column="parts">1</td>
                                    <td class="py-2 d-none" data-column="encoding"><span class="badge bg-light text-dark border">GSM-7</span></td>
                                    <td class="py-2 d-none" data-column="messageId">MSG-001234573</td>
                                    <td class="py-2 d-none" data-column="content"><span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span></td>
                                </tr>
                                <tr>
                                    <td class="py-2" data-column="mobileNumber"><span class="mobile-masked">+44 78** ***258</span></td>
                                    <td class="py-2" data-column="senderId">ALERTS</td>
                                    <td class="py-2" data-column="status"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2" data-column="sentTime">30/12/2024 14:02</td>
                                    <td class="py-2" data-column="deliveryTime">30/12/2024 14:02</td>
                                    <td class="py-2" data-column="completedTime">30/12/2024 14:02</td>
                                    <td class="py-2" data-column="cost">£0.035</td>
                                    <td class="py-2 d-none" data-column="messageType"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2 d-none" data-column="subAccount">Support Team</td>
                                    <td class="py-2 d-none" data-column="user">Mike Williams</td>
                                    <td class="py-2 d-none" data-column="origin">API</td>
                                    <td class="py-2 d-none" data-column="country">UK</td>
                                    <td class="py-2 d-none" data-column="parts">1</td>
                                    <td class="py-2 d-none" data-column="encoding"><span class="badge bg-light text-dark border">GSM-7</span></td>
                                    <td class="py-2 d-none" data-column="messageId">MSG-001234574</td>
                                    <td class="py-2 d-none" data-column="content"><span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span></td>
                                </tr>
                            </tbody>
                        </table>
                        <div id="noResultsState" class="text-center py-5 text-muted d-none">
                            <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                            <p class="mb-2">No messages match your filters.</p>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearFiltersEmpty">
                                <i class="fas fa-times me-1"></i> Clear filters
                            </button>
                        </div>
                        <div id="loadingMore" class="text-center py-3 d-none">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2 text-muted small">Loading more messages...</span>
                        </div>
                    </div>

                    <div class="card card-body bg-light border mt-3" id="exportBar">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing <span id="displayedCount">8</span> of <span id="totalCount">1,247</span> messages
                            </div>
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <button type="button" class="btn btn-outline-primary btn-sm" disabled>
                                    <i class="fas fa-file-csv me-1"></i> Export CSV
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" disabled>
                                    <i class="fas fa-file-excel me-1"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="columnSettingsModal" tabindex="-1" aria-labelledby="columnSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="columnSettingsModalLabel">
                    <i class="fas fa-columns me-2 text-primary"></i>Column Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Select which columns to display in the results table. Changes are saved automatically.</p>
                
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Default Columns</h6>
                <div class="list-group list-group-flush mb-3" id="defaultColumnsList">
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-mobileNumber" data-column="mobileNumber" checked>
                        <span>Mobile Number</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-senderId" data-column="senderId" checked>
                        <span>SenderID</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-status" data-column="status" checked>
                        <span>Message Status</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-sentTime" data-column="sentTime" checked>
                        <span>Sent Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-deliveryTime" data-column="deliveryTime" checked>
                        <span>Delivery Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-completedTime" data-column="completedTime" checked>
                        <span>Completed Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-cost" data-column="cost" checked>
                        <span>Cost</span>
                    </label>
                </div>
                
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Optional Columns</h6>
                <div class="list-group list-group-flush" id="optionalColumnsList">
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-messageType" data-column="messageType">
                        <span>Message Type</span>
                        <span class="badge bg-light text-muted ms-auto small">SMS / RCS</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-subAccount" data-column="subAccount">
                        <span>Sub Account</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-user" data-column="user">
                        <span>User</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-origin" data-column="origin">
                        <span>Origin</span>
                        <span class="badge bg-light text-muted ms-auto small">Portal / API / etc.</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-country" data-column="country">
                        <span>Country</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-parts" data-column="parts">
                        <span>Fragments / Parts</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-encoding" data-column="encoding">
                        <span>Encoding</span>
                        <span class="badge bg-light text-muted ms-auto small">GSM-7 / Unicode</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-messageId" data-column="messageId">
                        <span>Message ID</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-content" data-column="content">
                        <span>Content</span>
                        <span class="badge bg-warning text-dark ms-auto small"><i class="fas fa-lock me-1"></i>Security Controlled</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnResetColumns">
                    <i class="fas fa-undo me-1"></i> Reset to Default
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // TODO: Connect to backend API: GET /api/messages?page=X&limit=Y&filters=Z
    // TODO: Implement infinite scroll
    // TODO: Implement export functionality
    
    // Applied filter state - represents what's currently filtering the table
    const appliedFilters = {
        dateFrom: '',
        dateTo: '',
        subAccounts: [],
        users: [],
        origins: [],
        mobileNumbers: [],
        senderId: '',
        statuses: [],
        countries: [],
        messageTypes: [],
        messageIds: []
    };
    
    // Pending filter state (before Apply is clicked) - represents UI state
    const pendingFilters = {
        dateFrom: '',
        dateTo: '',
        subAccounts: [],
        users: [],
        origins: [],
        mobileNumbers: [],
        senderId: '',
        statuses: [],
        countries: [],
        messageTypes: [],
        messageIds: []
    };
    
    // Label mappings for readable chip text
    const labelMappings = {
        subAccounts: { main: 'Main Account', marketing: 'Marketing Team', support: 'Support Team', sales: 'Sales Team' },
        users: { john: 'John Smith', sarah: 'Sarah Johnson', mike: 'Mike Williams', emma: 'Emma Davis' },
        origins: { portal: 'Portal', api: 'API', 'email-to-sms': 'Email-to-SMS', integration: 'Integration' },
        statuses: { delivered: 'Delivered', pending: 'Pending', undeliverable: 'Undeliverable', rejected: 'Rejected' },
        countries: { uk: 'United Kingdom', us: 'United States', de: 'Germany', fr: 'France', es: 'Spain', ie: 'Ireland' },
        messageTypes: { sms: 'SMS', 'rcs-basic': 'RCS Basic', 'rcs-rich': 'RCS Rich' }
    };
    
    // Date preset buttons
    document.querySelectorAll('.date-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const preset = this.dataset.preset;
            const today = new Date();
            let fromDate, toDate = today;
            
            switch(preset) {
                case 'today':
                    fromDate = today;
                    break;
                case 'yesterday':
                    fromDate = new Date(today);
                    fromDate.setDate(fromDate.getDate() - 1);
                    toDate = fromDate;
                    break;
                case '7days':
                    fromDate = new Date(today);
                    fromDate.setDate(fromDate.getDate() - 7);
                    break;
                case '30days':
                    fromDate = new Date(today);
                    fromDate.setDate(fromDate.getDate() - 30);
                    break;
                case 'thismonth':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    break;
                case 'lastmonth':
                    fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    toDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
            }
            
            document.getElementById('filterDateFrom').value = formatDate(fromDate);
            document.getElementById('filterDateTo').value = formatDate(toDate);
            pendingFilters.dateFrom = formatDate(fromDate);
            pendingFilters.dateTo = formatDate(toDate);
        });
    });
    
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }
    
    // Date input changes
    document.getElementById('filterDateFrom')?.addEventListener('change', function() {
        pendingFilters.dateFrom = this.value;
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
    });
    
    document.getElementById('filterDateTo')?.addEventListener('change', function() {
        pendingFilters.dateTo = this.value;
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
    });
    
    // Multi-select dropdown handlers
    function setupMultiselect(menuId, toggleId, stateKey, defaultText) {
        const menu = document.getElementById(menuId);
        const toggle = document.getElementById(toggleId);
        if (!menu || !toggle) return;
        
        menu.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selected = Array.from(menu.querySelectorAll('input:checked')).map(cb => cb.value);
                pendingFilters[stateKey] = selected;
                updateMultiselectToggle(toggle, selected.length, defaultText);
            });
        });
    }
    
    function updateMultiselectToggle(toggle, count, defaultText) {
        const textSpan = toggle.querySelector('.toggle-text');
        if (count === 0) {
            textSpan.innerHTML = defaultText;
        } else {
            textSpan.innerHTML = `${defaultText} <span class="selected-count">${count}</span>`;
        }
    }
    
    setupMultiselect('filterSubAccountMenu', 'filterSubAccountToggle', 'subAccounts', 'All Sub Accounts');
    setupMultiselect('filterUserMenu', 'filterUserToggle', 'users', 'All Users');
    setupMultiselect('filterOriginMenu', 'filterOriginToggle', 'origins', 'All Origins');
    setupMultiselect('filterStatusMenu', 'filterStatusToggle', 'statuses', 'All Statuses');
    setupMultiselect('filterCountryMenu', 'filterCountryToggle', 'countries', 'All Countries');
    setupMultiselect('filterTypeMenu', 'filterTypeToggle', 'messageTypes', 'All Types');
    
    // Multi-value input handlers (Mobile Number, Message ID)
    function setupMultiValueInput(containerId, inputId, stateKey) {
        const container = document.getElementById(containerId);
        const input = document.getElementById(inputId);
        if (!container || !input) return;
        
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                e.preventDefault();
                addTag(container, input, this.value.trim(), stateKey);
                this.value = '';
            }
        });
    }
    
    function addTag(container, input, value, stateKey) {
        if (pendingFilters[stateKey].includes(value)) return;
        
        pendingFilters[stateKey].push(value);
        
        const tag = document.createElement('span');
        tag.className = 'multi-value-tag';
        tag.innerHTML = `${value} <i class="fas fa-times remove-tag"></i>`;
        tag.dataset.value = value;
        
        tag.querySelector('.remove-tag').addEventListener('click', function() {
            pendingFilters[stateKey] = pendingFilters[stateKey].filter(v => v !== value);
            tag.remove();
        });
        
        container.insertBefore(tag, input);
    }
    
    setupMultiValueInput('filterMobileContainer', 'filterMobileInput', 'mobileNumbers');
    setupMultiValueInput('filterMessageIdContainer', 'filterMessageIdInput', 'messageIds');
    
    // Predictive suggestions for SenderID
    const senderIdInput = document.getElementById('filterSenderId');
    const senderIdSuggestions = document.getElementById('senderIdSuggestions');
    
    senderIdInput?.addEventListener('focus', function() {
        senderIdSuggestions?.classList.add('show');
    });
    
    senderIdInput?.addEventListener('blur', function() {
        setTimeout(() => senderIdSuggestions?.classList.remove('show'), 200);
    });
    
    senderIdInput?.addEventListener('input', function() {
        pendingFilters.senderId = this.value;
        const query = this.value.toLowerCase();
        senderIdSuggestions?.querySelectorAll('.predictive-suggestion').forEach(item => {
            const match = item.dataset.value.toLowerCase().includes(query);
            item.style.display = match ? 'block' : 'none';
        });
    });
    
    senderIdSuggestions?.querySelectorAll('.predictive-suggestion').forEach(item => {
        item.addEventListener('click', function() {
            senderIdInput.value = this.dataset.value;
            pendingFilters.senderId = this.dataset.value;
            senderIdSuggestions.classList.remove('show');
        });
    });
    
    // Render active filter chips based on applied filters
    function renderActiveFilterChips() {
        const container = document.getElementById('activeFiltersChips');
        const wrapper = document.getElementById('activeFiltersContainer');
        if (!container || !wrapper) return;
        
        container.innerHTML = '';
        let hasFilters = false;
        
        // Date range chip
        if (appliedFilters.dateFrom || appliedFilters.dateTo) {
            hasFilters = true;
            const dateText = appliedFilters.dateFrom && appliedFilters.dateTo 
                ? `${appliedFilters.dateFrom} to ${appliedFilters.dateTo}`
                : appliedFilters.dateFrom || appliedFilters.dateTo;
            createChip(container, 'Date', dateText, () => {
                pendingFilters.dateFrom = '';
                pendingFilters.dateTo = '';
                document.getElementById('filterDateFrom').value = '';
                document.getElementById('filterDateTo').value = '';
                document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
            });
        }
        
        // Multi-select chips
        const multiSelectConfigs = [
            { key: 'subAccounts', label: 'Sub Account', menuId: 'filterSubAccountMenu', toggleId: 'filterSubAccountToggle', defaultText: 'All Sub Accounts' },
            { key: 'users', label: 'User', menuId: 'filterUserMenu', toggleId: 'filterUserToggle', defaultText: 'All Users' },
            { key: 'origins', label: 'Origin', menuId: 'filterOriginMenu', toggleId: 'filterOriginToggle', defaultText: 'All Origins' },
            { key: 'statuses', label: 'Status', menuId: 'filterStatusMenu', toggleId: 'filterStatusToggle', defaultText: 'All Statuses' },
            { key: 'countries', label: 'Country', menuId: 'filterCountryMenu', toggleId: 'filterCountryToggle', defaultText: 'All Countries' },
            { key: 'messageTypes', label: 'Type', menuId: 'filterTypeMenu', toggleId: 'filterTypeToggle', defaultText: 'All Types' }
        ];
        
        multiSelectConfigs.forEach(config => {
            appliedFilters[config.key].forEach(value => {
                hasFilters = true;
                const displayText = labelMappings[config.key]?.[value] || value;
                createChip(container, config.label, displayText, () => {
                    // Remove from pending state
                    pendingFilters[config.key] = pendingFilters[config.key].filter(v => v !== value);
                    // Update UI checkbox
                    const checkbox = document.querySelector(`#${config.menuId} input[value="${value}"]`);
                    if (checkbox) checkbox.checked = false;
                    // Update toggle text
                    const toggle = document.getElementById(config.toggleId);
                    if (toggle) updateMultiselectToggle(toggle, pendingFilters[config.key].length, config.defaultText);
                });
            });
        });
        
        // SenderID chip
        if (appliedFilters.senderId) {
            hasFilters = true;
            createChip(container, 'SenderID', appliedFilters.senderId, () => {
                pendingFilters.senderId = '';
                document.getElementById('filterSenderId').value = '';
            });
        }
        
        // Mobile number chips
        appliedFilters.mobileNumbers.forEach(number => {
            hasFilters = true;
            createChip(container, 'Mobile', number, () => {
                pendingFilters.mobileNumbers = pendingFilters.mobileNumbers.filter(n => n !== number);
                // Remove tag from UI
                document.querySelectorAll('#filterMobileContainer .multi-value-tag').forEach(tag => {
                    if (tag.dataset.value === number) tag.remove();
                });
            });
        });
        
        // Message ID chips
        appliedFilters.messageIds.forEach(id => {
            hasFilters = true;
            createChip(container, 'Message ID', id, () => {
                pendingFilters.messageIds = pendingFilters.messageIds.filter(i => i !== id);
                // Remove tag from UI
                document.querySelectorAll('#filterMessageIdContainer .multi-value-tag').forEach(tag => {
                    if (tag.dataset.value === id) tag.remove();
                });
            });
        });
        
        wrapper.style.display = hasFilters ? 'block' : 'none';
    }
    
    // Create a single filter chip
    function createChip(container, label, value, onRemove) {
        const chip = document.createElement('span');
        chip.className = 'filter-chip';
        chip.innerHTML = `<span class="fw-medium">${label}:</span> ${value} <i class="fas fa-times remove-chip"></i>`;
        
        chip.querySelector('.remove-chip').addEventListener('click', function(e) {
            e.stopPropagation();
            onRemove();
            // Remove chip visually but DO NOT refresh table - user must click Apply
            chip.remove();
            // Check if any chips remain
            if (container.children.length === 0) {
                document.getElementById('activeFiltersContainer').style.display = 'none';
            }
            console.log('Filter chip removed. Pending state updated. Click Apply to refresh table.');
        });
        
        container.appendChild(chip);
    }
    
    // Apply Filters button - commits pending state and refreshes table
    document.getElementById('btnApplyFilters')?.addEventListener('click', function() {
        // Copy pending filters to applied filters
        Object.keys(appliedFilters).forEach(key => {
            if (Array.isArray(pendingFilters[key])) {
                appliedFilters[key] = [...pendingFilters[key]];
            } else {
                appliedFilters[key] = pendingFilters[key];
            }
        });
        
        // Render chips based on applied filters
        renderActiveFilterChips();
        
        // Show summary bar if filters are applied
        const hasFilters = Object.values(appliedFilters).some(v => 
            Array.isArray(v) ? v.length > 0 : v !== ''
        );
        
        if (hasFilters) {
            // TODO: Replace with actual API response data
            // Mock summary data for UI demonstration
            document.getElementById('summaryTotal').textContent = '1,247';
            document.getElementById('summaryParts').textContent = '1,892';
            document.getElementById('summaryBar').style.display = 'block';
        } else {
            document.getElementById('summaryBar').style.display = 'none';
        }
        
        console.log('Filters applied:', appliedFilters);
        // TODO: Call API with appliedFilters and reload table
    });
    
    // Reset Filters button - resets pending state only, does NOT refresh table
    document.getElementById('btnResetFilters')?.addEventListener('click', function() {
        // Reset pending filters
        pendingFilters.dateFrom = '';
        pendingFilters.dateTo = '';
        pendingFilters.subAccounts = [];
        pendingFilters.users = [];
        pendingFilters.origins = [];
        pendingFilters.mobileNumbers = [];
        pendingFilters.senderId = '';
        pendingFilters.statuses = [];
        pendingFilters.countries = [];
        pendingFilters.messageTypes = [];
        pendingFilters.messageIds = [];
        
        // Reset UI elements
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
        
        // Reset all multiselect checkboxes
        document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        
        // Reset multiselect toggle texts
        document.querySelectorAll('.multiselect-toggle .toggle-text').forEach(span => {
            const toggle = span.closest('.multiselect-toggle');
            if (toggle.id.includes('SubAccount')) span.textContent = 'All Sub Accounts';
            else if (toggle.id.includes('User')) span.textContent = 'All Users';
            else if (toggle.id.includes('Origin')) span.textContent = 'All Origins';
            else if (toggle.id.includes('Status')) span.textContent = 'All Statuses';
            else if (toggle.id.includes('Country')) span.textContent = 'All Countries';
            else if (toggle.id.includes('Type')) span.textContent = 'All Types';
        });
        
        // Clear multi-value tags
        document.querySelectorAll('.multi-value-tag').forEach(tag => tag.remove());
        
        // Reset SenderID
        document.getElementById('filterSenderId').value = '';
        
        console.log('Filters reset (pending state only). Click Apply Filters to update table.');
    });
    
    // Clear all filters - resets pending AND applies (refreshes table)
    document.getElementById('btnClearAllFilters')?.addEventListener('click', function() {
        document.getElementById('btnResetFilters').click();
        document.getElementById('btnApplyFilters').click();
    });
    
    document.getElementById('btnClearFiltersEmpty')?.addEventListener('click', function() {
        document.getElementById('btnResetFilters').click();
        document.getElementById('btnApplyFilters').click();
    });
    
    // ========================================
    // Column Configuration
    // ========================================
    const STORAGE_KEY = 'messageLogColumnConfig';
    const MAX_ROWS = 10000;
    
    // TODO: Backend Integration - Replace with actual user role from session/API
    // GET /api/user/permissions or use Laravel Auth::user()->role
    // Roles: 'super_admin', 'admin', 'user', 'viewer'
    const currentUserRole = 'user'; // Mock: Change to 'super_admin' to test content visibility
    
    // Check if user can view message content (Super Admin only)
    function canViewMessageContent() {
        // TODO: Backend Integration - Check actual permission from server
        // Example: return window.userPermissions?.includes('view_message_content');
        return currentUserRole === 'super_admin';
    }
    
    // Render message content based on user permissions
    // Reuses masking pattern from mobile number display in contacts module
    function renderMessageContent(plaintext) {
        if (canViewMessageContent()) {
            // Super Admin can see actual content
            const truncated = plaintext.length > 50 ? plaintext.substring(0, 50) + '...' : plaintext;
            return `<span class="text-dark" title="${plaintext.replace(/"/g, '&quot;')}">${truncated}</span>`;
        } else {
            // Non-authorized users see masked content
            return `<span class="text-muted">
                <i class="fas fa-lock me-1 small"></i>
                <span class="content-masked">••••••••</span>
            </span>`;
        }
    }
    
    const allColumnsList = [
        'mobileNumber', 'senderId', 'status', 'sentTime', 'deliveryTime', 'completedTime', 'cost',
        'messageType', 'subAccount', 'user', 'origin', 'country', 'parts', 'encoding', 'messageId', 'content'
    ];
    
    const defaultColumns = {
        visible: ['mobileNumber', 'senderId', 'status', 'sentTime', 'deliveryTime', 'completedTime', 'cost'],
        order: allColumnsList
    };
    
    let columnConfig = loadColumnConfig();
    
    function loadColumnConfig() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed.visible && Array.isArray(parsed.visible)) {
                    return parsed;
                }
            }
        } catch (e) {
            console.error('Error loading column config:', e);
        }
        return { visible: [...defaultColumns.visible], order: [...defaultColumns.order] };
    }
    
    function saveColumnConfig() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(columnConfig));
            console.log('Column config saved:', columnConfig);
        } catch (e) {
            console.error('Error saving column config:', e);
        }
    }
    
    function applyColumnVisibility() {
        const allColumns = document.querySelectorAll('[data-column]');
        allColumns.forEach(el => {
            const colName = el.getAttribute('data-column');
            if (columnConfig.visible.includes(colName)) {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });
        
        document.querySelectorAll('.column-toggle').forEach(cb => {
            const colName = cb.getAttribute('data-column') || cb.id.replace('col-', '');
            cb.checked = columnConfig.visible.includes(colName);
        });
        
        updateRenderedCount();
    }
    
    function updateRenderedCount() {
        const rowCount = document.querySelectorAll('#messageLogTableBody tr').length;
        document.getElementById('renderedCount').textContent = Math.min(rowCount, MAX_ROWS).toLocaleString();
    }
    
    document.querySelectorAll('.column-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const colName = this.getAttribute('data-column') || this.id.replace('col-', '');
            if (this.checked) {
                if (!columnConfig.visible.includes(colName)) {
                    columnConfig.visible.push(colName);
                }
            } else {
                columnConfig.visible = columnConfig.visible.filter(c => c !== colName);
            }
            saveColumnConfig();
            applyColumnVisibility();
        });
    });
    
    document.getElementById('btnResetColumns')?.addEventListener('click', function() {
        columnConfig = { 
            visible: [...defaultColumns.visible], 
            order: [...defaultColumns.order] 
        };
        saveColumnConfig();
        applyColumnVisibility();
        console.log('Columns reset to default');
    });
    
    applyColumnVisibility();
    
    // ========================================
    // Infinite Scroll
    // ========================================
    let isLoading = false;
    let currentPage = 1;
    let totalRowsRendered = 8;
    
    const tableContainer = document.getElementById('tableContainer');
    
    tableContainer?.addEventListener('scroll', function() {
        if (isLoading || totalRowsRendered >= MAX_ROWS) return;
        
        const scrollTop = this.scrollTop;
        const scrollHeight = this.scrollHeight;
        const clientHeight = this.clientHeight;
        
        if (scrollTop + clientHeight >= scrollHeight - 100) {
            loadMoreRows();
        }
    });
    
    function loadMoreRows() {
        if (isLoading || totalRowsRendered >= MAX_ROWS) return;
        
        isLoading = true;
        document.getElementById('loadingMore').classList.remove('d-none');
        
        // TODO: Replace with actual API call
        setTimeout(() => {
            const tbody = document.getElementById('messageLogTableBody');
            const mockStatuses = [
                { class: 'bg-success', text: 'Delivered' },
                { class: 'bg-warning text-dark', text: 'Pending' },
                { class: 'bg-danger', text: 'Undeliverable' },
                { class: 'bg-secondary', text: 'Rejected' }
            ];
            const mockSenders = ['QuickSMS', 'ALERTS', 'PROMO', 'QuickSMS Brand'];
            const mockOrigins = ['Portal', 'API', 'Email-to-SMS', 'Integration'];
            const mockMessageTypes = [
                { class: 'bg-secondary', text: 'SMS' },
                { class: 'bg-info', text: 'RCS Basic' },
                { class: 'bg-info', text: 'RCS Rich' }
            ];
            const mockSubAccounts = ['Main Account', 'Marketing Team', 'Support Team', 'Sales Team'];
            const mockUsers = ['John Smith', 'Sarah Johnson', 'Mike Williams', 'Emma Davis'];
            const mockEncodings = [
                { class: 'bg-light text-dark border', text: 'GSM-7' },
                { class: 'bg-primary text-white', text: 'Unicode' }
            ];
            
            const rowsToAdd = Math.min(50, MAX_ROWS - totalRowsRendered);
            
            for (let i = 0; i < rowsToAdd; i++) {
                const status = mockStatuses[Math.floor(Math.random() * mockStatuses.length)];
                const sender = mockSenders[Math.floor(Math.random() * mockSenders.length)];
                const origin = mockOrigins[Math.floor(Math.random() * mockOrigins.length)];
                const messageType = mockMessageTypes[Math.floor(Math.random() * mockMessageTypes.length)];
                const subAccount = mockSubAccounts[Math.floor(Math.random() * mockSubAccounts.length)];
                const user = mockUsers[Math.floor(Math.random() * mockUsers.length)];
                const encoding = mockEncodings[Math.floor(Math.random() * mockEncodings.length)];
                const parts = Math.floor(Math.random() * 3) + 1;
                const cost = (parts * 0.035).toFixed(3);
                const msgId = `MSG-${String(totalRowsRendered + i + 1).padStart(9, '0')}`;
                const phone = `+44 7${Math.floor(Math.random() * 3) + 7}** ***${String(Math.floor(Math.random() * 1000)).padStart(3, '0')}`;
                
                const now = new Date();
                now.setMinutes(now.getMinutes() - (totalRowsRendered + i) * 5);
                const timeStr = now.toLocaleDateString('en-GB') + ' ' + now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
                const deliveryTime = status.text === 'Delivered' ? timeStr : '-';
                const completedTime = status.text !== 'Pending' ? timeStr : '-';
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="py-2 ${!columnConfig.visible.includes('mobileNumber') ? 'd-none' : ''}" data-column="mobileNumber"><span class="mobile-masked">${phone}</span></td>
                    <td class="py-2 ${!columnConfig.visible.includes('senderId') ? 'd-none' : ''}" data-column="senderId">${sender}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('status') ? 'd-none' : ''}" data-column="status"><span class="badge ${status.class}">${status.text}</span></td>
                    <td class="py-2 ${!columnConfig.visible.includes('sentTime') ? 'd-none' : ''}" data-column="sentTime">${timeStr}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('deliveryTime') ? 'd-none' : ''}" data-column="deliveryTime">${deliveryTime}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('completedTime') ? 'd-none' : ''}" data-column="completedTime">${completedTime}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('cost') ? 'd-none' : ''}" data-column="cost">£${cost}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('messageType') ? 'd-none' : ''}" data-column="messageType"><span class="badge ${messageType.class}">${messageType.text}</span></td>
                    <td class="py-2 ${!columnConfig.visible.includes('subAccount') ? 'd-none' : ''}" data-column="subAccount">${subAccount}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('user') ? 'd-none' : ''}" data-column="user">${user}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('origin') ? 'd-none' : ''}" data-column="origin">${origin}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('country') ? 'd-none' : ''}" data-column="country">UK</td>
                    <td class="py-2 ${!columnConfig.visible.includes('parts') ? 'd-none' : ''}" data-column="parts">${parts}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('encoding') ? 'd-none' : ''}" data-column="encoding"><span class="badge ${encoding.class}">${encoding.text}</span></td>
                    <td class="py-2 ${!columnConfig.visible.includes('messageId') ? 'd-none' : ''}" data-column="messageId">${msgId}</td>
                    <td class="py-2 ${!columnConfig.visible.includes('content') ? 'd-none' : ''}" data-column="content">${renderMessageContent('Hi there! Your order #12345 has been shipped and will arrive tomorrow.')}</td>
                `;
                tbody.appendChild(row);
                totalRowsRendered++;
            }
            
            updateRenderedCount();
            isLoading = false;
            document.getElementById('loadingMore').classList.add('d-none');
            currentPage++;
            
            console.log(`Loaded ${rowsToAdd} rows. Total: ${totalRowsRendered}`);
        }, 500);
    }
    
    function sortTable(column, direction) {
        console.log(`Sort by ${column} ${direction}`);
        // TODO: Implement server-side sorting with API call
    }
});
</script>
@endpush

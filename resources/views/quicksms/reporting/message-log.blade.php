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
.summary-bar {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 1rem;
}
.summary-stat {
    text-align: center;
    padding: 0.5rem;
}
.summary-stat .stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #6f42c1;
}
.summary-stat .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
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

                    <div class="summary-bar mb-3" id="summaryBar" style="display: none;">
                        <div class="row">
                            <div class="col-6 col-md-3 summary-stat">
                                <div class="stat-value" id="summaryTotal">0</div>
                                <div class="stat-label">Total Messages</div>
                            </div>
                            <div class="col-6 col-md-3 summary-stat">
                                <div class="stat-value text-success" id="summaryDelivered">0</div>
                                <div class="stat-label">Delivered</div>
                            </div>
                            <div class="col-6 col-md-3 summary-stat">
                                <div class="stat-value text-danger" id="summaryFailed">0</div>
                                <div class="stat-label">Failed</div>
                            </div>
                            <div class="col-6 col-md-3 summary-stat">
                                <div class="stat-value text-info" id="summaryCredits">0</div>
                                <div class="stat-label">Credits Used</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" id="tableContainer" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0" id="messageLogTable">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Date/Time <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!"><i class="fas fa-calendar-alt me-2"></i> Most Recent</a></li>
                                                <li><a class="dropdown-item" href="#!"><i class="fas fa-calendar me-2"></i> Oldest First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th>Direction</th>
                                    <th>Recipient</th>
                                    <th>Sender</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Message Preview</th>
                                    <th>Credits</th>
                                </tr>
                            </thead>
                            <tbody id="messageLogTableBody">
                                <tr>
                                    <td class="py-2">30/12/2024 14:23</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 77** ***456</td>
                                    <td class="py-2">QuickSMS</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Hi @{{firstName}}, your order #@{{orderNumber}} has been shipped...</td>
                                    <td class="py-2">1</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:21</td>
                                    <td class="py-2"><span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i>In</span></td>
                                    <td class="py-2">+44 78** ***789</td>
                                    <td class="py-2">+447700900100</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-info">Received</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Yes please, I'd like to confirm my appointment</td>
                                    <td class="py-2">-</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:18</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 79** ***123</td>
                                    <td class="py-2">ALERTS</td>
                                    <td class="py-2"><span class="badge bg-info">RCS</span></td>
                                    <td class="py-2"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Your appointment reminder for tomorrow at 10:00 AM...</td>
                                    <td class="py-2">0.5</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:15</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 77** ***321</td>
                                    <td class="py-2">QuickSMS</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-danger">Failed</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Special offer: Get 20% off your next purchase...</td>
                                    <td class="py-2">0</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:12</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 78** ***654</td>
                                    <td class="py-2">QuickSMS</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Thank you for your order! Tracking: @{{trackingNumber}}</td>
                                    <td class="py-2">1</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:08</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 79** ***987</td>
                                    <td class="py-2">QuickSMS Brand</td>
                                    <td class="py-2"><span class="badge bg-info">RCS</span></td>
                                    <td class="py-2"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">[Rich Card] Check out our new arrivals!</td>
                                    <td class="py-2">0.5</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:05</td>
                                    <td class="py-2"><span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i>In</span></td>
                                    <td class="py-2">+44 77** ***147</td>
                                    <td class="py-2">+447700900100</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-info">Received</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">STOP</td>
                                    <td class="py-2">-</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:02</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 78** ***258</td>
                                    <td class="py-2">ALERTS</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-secondary">Expired</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Your verification code is: ******</td>
                                    <td class="py-2">1</td>
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
        document.getElementById('summaryBar').style.display = hasFilters ? 'block' : 'none';
        
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
});
</script>
@endpush

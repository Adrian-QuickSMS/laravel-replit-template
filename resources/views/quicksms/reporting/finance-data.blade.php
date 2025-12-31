@extends('layouts.quicksms')

@section('title', 'Finance Data')

@push('styles')
<style>
.finance-data-container {
    height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.finance-data-container .card {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    margin-bottom: 0 !important;
}
.finance-data-container .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding-bottom: 0;
}
.finance-data-fixed-header {
    flex-shrink: 0;
}
.finance-data-table-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
}
#tableContainer {
    flex: 1;
    overflow-y: auto;
    overflow-x: auto;
    min-height: 0;
}
.finance-data-footer {
    flex-shrink: 0;
    margin-top: auto;
}
#financeDataTable tbody tr {
    cursor: pointer;
}
#financeDataTable tbody tr:hover {
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
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
}
.month-preset-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
    background: #fff;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.month-preset-btn:hover {
    background: #f8f9fa;
    border-color: #6f42c1;
}
.month-preset-btn.active {
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
#tableContainer thead th {
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}
#billingTableBody tr {
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
}
#billingTableBody tr:hover td {
    filter: brightness(0.95);
}
.drill-dimension-btn.active {
    background-color: #886CC0 !important;
    border-color: #886CC0 !important;
    color: #fff !important;
}
.breadcrumb-item a {
    color: #886CC0;
    text-decoration: none;
}
.breadcrumb-item a:hover {
    text-decoration: underline;
}
.breadcrumb-item.active {
    color: #495057;
    font-weight: 500;
}
.optgroup-label {
    font-weight: 600;
    font-size: 0.75rem;
    color: #6c757d;
    padding: 0.5rem 1rem 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.optgroup-item {
    padding-left: 1.5rem !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid finance-data-container">
    <div class="row page-titles mb-2" style="flex-shrink: 0;">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reporting') }}">Reporting</a></li>
            <li class="breadcrumb-item active">Finance Data</li>
        </ol>
    </div>
    
    <div class="row flex-grow-1" style="min-height: 0;">
        <div class="col-12 d-flex flex-column" style="min-height: 0;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap finance-data-fixed-header">
                    <h5 class="card-title mb-2 mb-md-0">Finance Data</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="finance-data-fixed-header">
                        <div class="collapse mb-3" id="filtersPanel">
                            <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label small fw-bold">Billing Month</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="billingMonths">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Months</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-12" id="month202512"><label class="form-check-label small" for="month202512">December 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-11" id="month202511"><label class="form-check-label small" for="month202511">November 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-10" id="month202510"><label class="form-check-label small" for="month202510">October 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-09" id="month202509"><label class="form-check-label small" for="month202509">September 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-08" id="month202508"><label class="form-check-label small" for="month202508">August 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-07" id="month202507"><label class="form-check-label small" for="month202507">July 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-06" id="month202506"><label class="form-check-label small" for="month202506">June 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-05" id="month202505"><label class="form-check-label small" for="month202505">May 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-04" id="month202504"><label class="form-check-label small" for="month202504">April 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-03" id="month202503"><label class="form-check-label small" for="month202503">March 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-02" id="month202502"><label class="form-check-label small" for="month202502">February 2025</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="2025-01" id="month202501"><label class="form-check-label small" for="month202501">January 2025</label></div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                            <button type="button" class="btn btn-outline-primary btn-xs month-preset-btn" data-preset="current">Current Month</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs month-preset-btn" data-preset="last">Last Month</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs month-preset-btn" data-preset="last3">Last 3 Months</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs month-preset-btn" data-preset="last6">Last 6 Months</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs month-preset-btn" data-preset="ytd">Year to Date</button>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Sub Account</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="subAccounts" id="subAccountDropdown">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Sub Accounts</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Main Account" id="subAcc1"><label class="form-check-label small" for="subAcc1">Main Account</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Marketing Team" id="subAcc2"><label class="form-check-label small" for="subAcc2">Marketing Team</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Support Team" id="subAcc3"><label class="form-check-label small" for="subAcc3">Support Team</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Sales Team" id="subAcc4"><label class="form-check-label small" for="subAcc4">Sales Team</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">User</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="users" id="userDropdown">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Users</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check" data-subaccount="Main Account"><input class="form-check-input" type="checkbox" value="John Smith" id="user1"><label class="form-check-label small" for="user1">John Smith</label></div>
                                                <div class="form-check" data-subaccount="Main Account"><input class="form-check-input" type="checkbox" value="Sarah Johnson" id="user2"><label class="form-check-label small" for="user2">Sarah Johnson</label></div>
                                                <div class="form-check" data-subaccount="Marketing Team"><input class="form-check-input" type="checkbox" value="Mike Williams" id="user3"><label class="form-check-label small" for="user3">Mike Williams</label></div>
                                                <div class="form-check" data-subaccount="Marketing Team"><input class="form-check-input" type="checkbox" value="Emma Davis" id="user4"><label class="form-check-label small" for="user4">Emma Davis</label></div>
                                                <div class="form-check" data-subaccount="Support Team"><input class="form-check-input" type="checkbox" value="James Wilson" id="user5"><label class="form-check-label small" for="user5">James Wilson</label></div>
                                                <div class="form-check" data-subaccount="Support Team"><input class="form-check-input" type="checkbox" value="Lisa Brown" id="user6"><label class="form-check-label small" for="user6">Lisa Brown</label></div>
                                                <div class="form-check" data-subaccount="Sales Team"><input class="form-check-input" type="checkbox" value="David Miller" id="user7"><label class="form-check-label small" for="user7">David Miller</label></div>
                                                <div class="form-check" data-subaccount="Sales Team"><input class="form-check-input" type="checkbox" value="Amy Garcia" id="user8"><label class="form-check-label small" for="user8">Amy Garcia</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Group Name</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="groupNames" id="groupNameDropdown">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Groups</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="optgroup-label">Marketing</div>
                                                <div class="form-check optgroup-item"><input class="form-check-input" type="checkbox" value="Newsletter Subscribers" id="group1"><label class="form-check-label small" for="group1">Newsletter Subscribers</label></div>
                                                <div class="form-check optgroup-item"><input class="form-check-input" type="checkbox" value="VIP Customers" id="group2"><label class="form-check-label small" for="group2">VIP Customers</label></div>
                                                <div class="form-check optgroup-item"><input class="form-check-input" type="checkbox" value="Promotional Leads" id="group3"><label class="form-check-label small" for="group3">Promotional Leads</label></div>
                                                <div class="optgroup-label">Transactional</div>
                                                <div class="form-check optgroup-item"><input class="form-check-input" type="checkbox" value="Order Notifications" id="group4"><label class="form-check-label small" for="group4">Order Notifications</label></div>
                                                <div class="form-check optgroup-item"><input class="form-check-input" type="checkbox" value="Delivery Alerts" id="group5"><label class="form-check-label small" for="group5">Delivery Alerts</label></div>
                                                <div class="form-check optgroup-item"><input class="form-check-input" type="checkbox" value="Payment Reminders" id="group6"><label class="form-check-label small" for="group6">Payment Reminders</label></div>
                                                <div class="optgroup-label">Support</div>
                                                <div class="form-check optgroup-item"><input class="form-check-input" type="checkbox" value="Customer Support" id="group7"><label class="form-check-label small" for="group7">Customer Support</label></div>
                                                <div class="form-check optgroup-item"><input class="form-check-input" type="checkbox" value="Technical Alerts" id="group8"><label class="form-check-label small" for="group8">Technical Alerts</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Product Type</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="productTypes">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Products</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Portal" id="product1"><label class="form-check-label small" for="product1">Portal</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="API" id="product2"><label class="form-check-label small" for="product2">API</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Email-to-SMS" id="product3"><label class="form-check-label small" for="product3">Email-to-SMS</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Integration" id="product4"><label class="form-check-label small" for="product4">Integration</label></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-3 align-items-end mt-2">
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <label class="form-label small fw-bold">Sender ID</label>
                                        <div class="predictive-input-wrapper">
                                            <div class="multi-value-input" id="senderIdContainer">
                                                <input type="text" id="filterSenderId" placeholder="Type to search..." autocomplete="off">
                                            </div>
                                            <div class="predictive-suggestions" id="senderIdSuggestions">
                                                <div class="predictive-suggestion" data-value="QuickSMS">QuickSMS</div>
                                                <div class="predictive-suggestion" data-value="PROMO">PROMO</div>
                                                <div class="predictive-suggestion" data-value="ALERTS">ALERTS</div>
                                                <div class="predictive-suggestion" data-value="INFO">INFO</div>
                                                <div class="predictive-suggestion" data-value="NOTIFY">NOTIFY</div>
                                                <div class="predictive-suggestion" data-value="VERIFY">VERIFY</div>
                                                <div class="predictive-suggestion" data-value="UPDATES">UPDATES</div>
                                                <div class="predictive-suggestion" data-value="NEWS">NEWS</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Message Type</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="messageTypes">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Types</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="SMS" id="msgType1"><label class="form-check-label small" for="msgType1">SMS</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="RCS" id="msgType2"><label class="form-check-label small" for="msgType2">RCS</label></div>
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
                    </div>

                    <div class="mb-3" id="drillBreadcrumbs" style="display: none;">
                        <nav aria-label="Drill-down breadcrumb">
                            <ol class="breadcrumb mb-0" id="drillBreadcrumbList">
                                <li class="breadcrumb-item"><a href="#" onclick="resetDrillDown(); return false;">Finance Data</a></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="mb-3" id="drillDimensionTabs" style="display: none;">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small me-2">Drill by:</span>
                            <div class="btn-group" role="group" aria-label="Drill dimension selector">
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="day">
                                    <i class="fas fa-calendar-day me-1"></i>Day
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="country">
                                    <i class="fas fa-globe me-1"></i>Country
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="sender_id">
                                    <i class="fas fa-id-card me-1"></i>Sender ID
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="product_type">
                                    <i class="fas fa-cube me-1"></i>Product Type
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="group_name">
                                    <i class="fas fa-users me-1"></i>Group Name
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="finance-data-table-wrapper">
                        <div id="tableContainer">
                            <table class="table table-striped table-hover table-bordered" id="financeDataTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th scope="col" class="sortable" data-sort="billing_month">
                                            Billing Month <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable text-end" data-sort="billable_parts">
                                            Billable Parts <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable text-end" data-sort="non_billable_parts">
                                            Non-Billable Parts <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable text-end" data-sort="total_parts">
                                            Total Parts <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable text-end" data-sort="total_cost">
                                            Total Cost (ex VAT) <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable text-center" data-sort="billing_status">
                                            Billing Status <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="billingTableBody">
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">December 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">125,432</td>
                                        <td class="text-end">3,218</td>
                                        <td class="text-end fw-semibold">128,650</td>
                                        <td class="text-end fw-semibold">£4,017.82</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">November 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">118,756</td>
                                        <td class="text-end">2,891</td>
                                        <td class="text-end fw-semibold">121,647</td>
                                        <td class="text-end fw-semibold">£3,812.45</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">October 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">132,890</td>
                                        <td class="text-end">4,102</td>
                                        <td class="text-end fw-semibold">136,992</td>
                                        <td class="text-end fw-semibold">£4,278.56</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                    <tr class="table-warning" data-status="adjusted">
                                        <td>
                                            <span class="fw-semibold">September 2025</span>
                                        </td>
                                        <td class="text-end">98,234</td>
                                        <td class="text-end">1,567</td>
                                        <td class="text-end fw-semibold">99,801</td>
                                        <td class="text-end fw-semibold">£3,118.92</td>
                                        <td class="text-center">Adjusted</td>
                                    </tr>
                                    <tr class="table-info" data-status="provisional">
                                        <td>
                                            <span class="fw-semibold">August 2025</span>
                                        </td>
                                        <td class="text-end">145,678</td>
                                        <td class="text-end">5,234</td>
                                        <td class="text-end fw-semibold">150,912</td>
                                        <td class="text-end fw-semibold">£4,715.89</td>
                                        <td class="text-center">Provisional</td>
                                    </tr>
                                    <tr class="table-info" data-status="provisional">
                                        <td>
                                            <span class="fw-semibold">July 2025</span>
                                        </td>
                                        <td class="text-end">112,345</td>
                                        <td class="text-end">2,456</td>
                                        <td class="text-end fw-semibold">114,801</td>
                                        <td class="text-end fw-semibold">£3,587.23</td>
                                        <td class="text-center">Provisional</td>
                                    </tr>
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">June 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">108,923</td>
                                        <td class="text-end">3,012</td>
                                        <td class="text-end fw-semibold">111,935</td>
                                        <td class="text-end fw-semibold">£3,498.12</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">May 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">95,678</td>
                                        <td class="text-end">1,890</td>
                                        <td class="text-end fw-semibold">97,568</td>
                                        <td class="text-end fw-semibold">£3,048.67</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">April 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">87,234</td>
                                        <td class="text-end">2,134</td>
                                        <td class="text-end fw-semibold">89,368</td>
                                        <td class="text-end fw-semibold">£2,792.34</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">March 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">102,456</td>
                                        <td class="text-end">2,789</td>
                                        <td class="text-end fw-semibold">105,245</td>
                                        <td class="text-end fw-semibold">£3,289.01</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">February 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">78,912</td>
                                        <td class="text-end">1,456</td>
                                        <td class="text-end fw-semibold">80,368</td>
                                        <td class="text-end fw-semibold">£2,511.45</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                    <tr class="table-success" data-status="finalised">
                                        <td>
                                            <span class="fw-semibold">January 2025</span>
                                            <i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>
                                        </td>
                                        <td class="text-end">91,345</td>
                                        <td class="text-end">2,234</td>
                                        <td class="text-end fw-semibold">93,579</td>
                                        <td class="text-end fw-semibold">£2,924.78</td>
                                        <td class="text-center">Finalised</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2 text-muted small">Loading more data...</span>
                        </div>
                    </div>

                    <div class="finance-data-footer border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted small">Showing <strong id="rowCount">12</strong> of <strong id="totalCount">12</strong> billing periods</span>
                                <span class="text-muted small">|</span>
                                <span class="text-muted small">Max display: 10,000 rows</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnExportCsv">
                                    <i class="fas fa-file-csv me-1"></i> Export CSV
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnExportPdf">
                                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Finance Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Export functionality will be available in a future update.</p>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Export Format</label>
                    <select class="form-select form-select-sm" disabled>
                        <option>CSV</option>
                        <option>PDF</option>
                        <option>Excel</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Date Range</label>
                    <p class="text-muted small mb-0">Uses current filter settings</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" disabled>
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initMultiselectDropdowns();
    initMonthPresets();
    initFilterActions();
    initSenderIdPredictive();
    initSubAccountUserFiltering();
});

var allSenderIds = ['QuickSMS', 'PROMO', 'ALERTS', 'INFO', 'NOTIFY', 'VERIFY', 'UPDATES', 'NEWS'];
var selectedSenderIds = [];

function initMultiselectDropdowns() {
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        var selectAllBtn = dropdown.querySelector('.select-all-btn');
        var clearAllBtn = dropdown.querySelector('.clear-all-btn');
        var checkboxes = dropdown.querySelectorAll('.form-check-input');
        var labelSpan = dropdown.querySelector('.dropdown-label');
        var filterName = dropdown.getAttribute('data-filter');
        
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                var visibleCheckboxes = Array.from(checkboxes).filter(function(cb) {
                    return cb.closest('.form-check').style.display !== 'none';
                });
                visibleCheckboxes.forEach(function(cb) { cb.checked = true; });
                updateDropdownLabel(dropdown);
            });
        }
        
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                checkboxes.forEach(function(cb) { cb.checked = false; });
                updateDropdownLabel(dropdown);
            });
        }
        
        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateDropdownLabel(dropdown);
            });
        });
    });
}

function updateDropdownLabel(dropdown) {
    var checkboxes = dropdown.querySelectorAll('.form-check-input');
    var labelSpan = dropdown.querySelector('.dropdown-label');
    var filterName = dropdown.getAttribute('data-filter');
    var checked = Array.from(checkboxes).filter(function(cb) { return cb.checked; });
    
    var defaultLabels = {
        'billingMonths': 'All Months',
        'subAccounts': 'All Sub Accounts',
        'users': 'All Users',
        'groupNames': 'All Groups',
        'productTypes': 'All Products',
        'messageTypes': 'All Types'
    };
    
    if (checked.length === 0) {
        labelSpan.textContent = defaultLabels[filterName] || 'All';
    } else if (checked.length === 1) {
        labelSpan.textContent = checked[0].nextElementSibling ? checked[0].nextElementSibling.textContent : checked[0].value;
    } else {
        labelSpan.textContent = checked.length + ' selected';
    }
}

function initMonthPresets() {
    document.querySelectorAll('.month-preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.month-preset-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            
            var preset = this.getAttribute('data-preset');
            var now = new Date();
            var currentYear = now.getFullYear();
            var currentMonth = now.getMonth() + 1;
            
            var dropdown = document.querySelector('[data-filter="billingMonths"]');
            var checkboxes = dropdown.querySelectorAll('.form-check-input');
            checkboxes.forEach(function(cb) { cb.checked = false; });
            
            var monthsToSelect = [];
            
            switch(preset) {
                case 'current':
                    monthsToSelect.push(currentYear + '-' + String(currentMonth).padStart(2, '0'));
                    break;
                case 'last':
                    var lastMonth = currentMonth === 1 ? 12 : currentMonth - 1;
                    var lastYear = currentMonth === 1 ? currentYear - 1 : currentYear;
                    monthsToSelect.push(lastYear + '-' + String(lastMonth).padStart(2, '0'));
                    break;
                case 'last3':
                    for (var i = 0; i < 3; i++) {
                        var m = currentMonth - i;
                        var y = currentYear;
                        if (m <= 0) { m += 12; y--; }
                        monthsToSelect.push(y + '-' + String(m).padStart(2, '0'));
                    }
                    break;
                case 'last6':
                    for (var i = 0; i < 6; i++) {
                        var m = currentMonth - i;
                        var y = currentYear;
                        if (m <= 0) { m += 12; y--; }
                        monthsToSelect.push(y + '-' + String(m).padStart(2, '0'));
                    }
                    break;
                case 'ytd':
                    for (var m = 1; m <= currentMonth; m++) {
                        monthsToSelect.push(currentYear + '-' + String(m).padStart(2, '0'));
                    }
                    break;
            }
            
            checkboxes.forEach(function(cb) {
                if (monthsToSelect.indexOf(cb.value) !== -1) {
                    cb.checked = true;
                }
            });
            
            updateDropdownLabel(dropdown);
        });
    });
}

function initSenderIdPredictive() {
    var input = document.getElementById('filterSenderId');
    var suggestions = document.getElementById('senderIdSuggestions');
    var container = document.getElementById('senderIdContainer');
    
    if (!input || !suggestions || !container) return;
    
    input.addEventListener('focus', function() {
        filterSuggestions(this.value);
        suggestions.classList.add('show');
    });
    
    input.addEventListener('input', function() {
        filterSuggestions(this.value);
    });
    
    input.addEventListener('blur', function() {
        setTimeout(function() {
            suggestions.classList.remove('show');
        }, 200);
    });
    
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && this.value.trim()) {
            e.preventDefault();
            addSenderIdTag(this.value.trim());
            this.value = '';
            filterSuggestions('');
        }
    });
    
    suggestions.querySelectorAll('.predictive-suggestion').forEach(function(item) {
        item.addEventListener('click', function() {
            addSenderIdTag(this.getAttribute('data-value'));
            input.value = '';
            suggestions.classList.remove('show');
        });
    });
    
    function filterSuggestions(query) {
        var q = query.toLowerCase();
        suggestions.querySelectorAll('.predictive-suggestion').forEach(function(item) {
            var value = item.getAttribute('data-value').toLowerCase();
            if (selectedSenderIds.indexOf(item.getAttribute('data-value')) !== -1) {
                item.style.display = 'none';
            } else if (q === '' || value.indexOf(q) !== -1) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    function addSenderIdTag(value) {
        if (selectedSenderIds.indexOf(value) !== -1) return;
        selectedSenderIds.push(value);
        
        var tag = document.createElement('span');
        tag.className = 'multi-value-tag';
        tag.innerHTML = value + '<span class="remove-tag" data-value="' + value + '"><i class="fas fa-times"></i></span>';
        container.insertBefore(tag, input);
        
        tag.querySelector('.remove-tag').addEventListener('click', function() {
            var val = this.getAttribute('data-value');
            selectedSenderIds = selectedSenderIds.filter(function(s) { return s !== val; });
            tag.remove();
        });
    }
}

function initSubAccountUserFiltering() {
    var subAccountDropdown = document.getElementById('subAccountDropdown');
    var userDropdown = document.getElementById('userDropdown');
    
    if (!subAccountDropdown || !userDropdown) return;
    
    var subAccountCheckboxes = subAccountDropdown.querySelectorAll('.form-check-input');
    var userItems = userDropdown.querySelectorAll('.form-check[data-subaccount]');
    
    subAccountCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            var selectedSubAccounts = Array.from(subAccountCheckboxes)
                .filter(function(c) { return c.checked; })
                .map(function(c) { return c.value; });
            
            userItems.forEach(function(item) {
                var userSubAccount = item.getAttribute('data-subaccount');
                if (selectedSubAccounts.length === 0 || selectedSubAccounts.indexOf(userSubAccount) !== -1) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                    item.querySelector('.form-check-input').checked = false;
                }
            });
            
            updateDropdownLabel(userDropdown);
        });
    });
}

function initFilterActions() {
    var btnApply = document.getElementById('btnApplyFilters');
    var btnReset = document.getElementById('btnResetFilters');
    var btnClearAll = document.getElementById('btnClearAllFilters');
    
    if (btnApply) {
        btnApply.addEventListener('click', function() {
            applyFilters();
        });
    }
    
    if (btnReset) {
        btnReset.addEventListener('click', function() {
            resetFilters();
        });
    }
    
    if (btnClearAll) {
        btnClearAll.addEventListener('click', function() {
            resetFilters();
            applyFilters();
        });
    }
}

function applyFilters() {
    var activeFilters = [];
    
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        var checked = Array.from(dropdown.querySelectorAll('.form-check-input:checked'));
        var totalCheckboxes = dropdown.querySelectorAll('.form-check-input').length;
        if (checked.length > 0 && checked.length < totalCheckboxes) {
            var filterName = dropdown.getAttribute('data-filter');
            var filterLabels = {
                'billingMonths': 'Month',
                'subAccounts': 'Sub Account',
                'users': 'User',
                'groupNames': 'Group',
                'productTypes': 'Product',
                'messageTypes': 'Message Type'
            };
            checked.forEach(function(cb) {
                var label = cb.nextElementSibling ? cb.nextElementSibling.textContent : cb.value;
                activeFilters.push({ type: filterLabels[filterName] || filterName, value: label });
            });
        }
    });
    
    if (selectedSenderIds.length > 0) {
        selectedSenderIds.forEach(function(sid) {
            activeFilters.push({ type: 'Sender ID', value: sid });
        });
    }
    
    renderActiveFilters(activeFilters);
    
    console.log('[Finance Data] Applying filters:', {
        billingMonths: getSelectedValues('billingMonths'),
        subAccounts: getSelectedValues('subAccounts'),
        users: getSelectedValues('users'),
        groupNames: getSelectedValues('groupNames'),
        productTypes: getSelectedValues('productTypes'),
        senderIds: selectedSenderIds,
        messageTypes: getSelectedValues('messageTypes')
    });
    
    console.log('[Finance Data] Mock data request triggered');
}

function getSelectedValues(filterName) {
    var dropdown = document.querySelector('[data-filter="' + filterName + '"]');
    if (!dropdown) return [];
    return Array.from(dropdown.querySelectorAll('.form-check-input:checked')).map(function(cb) {
        return cb.value;
    });
}

function renderActiveFilters(filters) {
    var container = document.getElementById('activeFiltersContainer');
    var chipsDiv = document.getElementById('activeFiltersChips');
    
    if (filters.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    chipsDiv.innerHTML = '';
    
    filters.forEach(function(filter, index) {
        var chip = document.createElement('span');
        chip.className = 'filter-chip';
        chip.innerHTML = '<span class="text-muted me-1">' + filter.type + ':</span>' + filter.value + 
            '<span class="remove-chip" data-index="' + index + '" data-type="' + filter.type + '" data-value="' + filter.value + '"><i class="fas fa-times"></i></span>';
        chipsDiv.appendChild(chip);
    });
    
    chipsDiv.querySelectorAll('.remove-chip').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var chipType = this.getAttribute('data-type');
            var chipValue = this.getAttribute('data-value');
            
            if (chipType === 'Sender ID') {
                selectedSenderIds = selectedSenderIds.filter(function(s) { return s !== chipValue; });
                var tags = document.querySelectorAll('#senderIdContainer .multi-value-tag');
                tags.forEach(function(tag) {
                    if (tag.textContent.indexOf(chipValue) !== -1) {
                        tag.remove();
                    }
                });
            }
            
            this.closest('.filter-chip').remove();
            if (chipsDiv.querySelectorAll('.filter-chip').length === 0) {
                container.style.display = 'none';
            }
        });
    });
}

function resetFilters() {
    document.querySelectorAll('.month-preset-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        dropdown.querySelectorAll('.form-check-input').forEach(function(cb) {
            cb.checked = false;
        });
        dropdown.querySelectorAll('.form-check[data-subaccount]').forEach(function(item) {
            item.style.display = 'block';
        });
        updateDropdownLabel(dropdown);
    });
    
    selectedSenderIds = [];
    var senderContainer = document.getElementById('senderIdContainer');
    if (senderContainer) {
        senderContainer.querySelectorAll('.multi-value-tag').forEach(function(tag) {
            tag.remove();
        });
    }
    var senderInput = document.getElementById('filterSenderId');
    if (senderInput) {
        senderInput.value = '';
    }
    
    document.getElementById('activeFiltersContainer').style.display = 'none';
    document.getElementById('activeFiltersChips').innerHTML = '';
}

var drillState = {
    level: 0,
    billingMonth: null,
    dimension: null
};

var dimensionLabels = {
    'day': 'Day',
    'country': 'Country',
    'sender_id': 'Sender ID',
    'product_type': 'Product Type',
    'group_name': 'Group Name'
};

var mockDrillData = {
    day: [
        { label: '2025-01-01', billable: 4521, nonBillable: 89, total: 4610, cost: '£144.06', status: 'Finalised' },
        { label: '2025-01-02', billable: 4832, nonBillable: 102, total: 4934, cost: '£154.19', status: 'Finalised' },
        { label: '2025-01-03', billable: 3987, nonBillable: 78, total: 4065, cost: '£127.03', status: 'Finalised' },
        { label: '2025-01-04', billable: 2156, nonBillable: 45, total: 2201, cost: '£68.78', status: 'Finalised' },
        { label: '2025-01-05', billable: 1876, nonBillable: 34, total: 1910, cost: '£59.69', status: 'Finalised' },
        { label: '2025-01-06', billable: 5234, nonBillable: 112, total: 5346, cost: '£167.06', status: 'Finalised' },
        { label: '2025-01-07', billable: 5567, nonBillable: 98, total: 5665, cost: '£177.03', status: 'Finalised' }
    ],
    country: [
        { label: 'United Kingdom', billable: 45678, nonBillable: 890, total: 46568, cost: '£1,455.25', status: 'Finalised' },
        { label: 'United States', billable: 23456, nonBillable: 456, total: 23912, cost: '£747.25', status: 'Finalised' },
        { label: 'Germany', billable: 12345, nonBillable: 234, total: 12579, cost: '£393.09', status: 'Finalised' },
        { label: 'France', billable: 8765, nonBillable: 167, total: 8932, cost: '£279.13', status: 'Finalised' },
        { label: 'Ireland', billable: 5432, nonBillable: 98, total: 5530, cost: '£172.81', status: 'Finalised' }
    ],
    sender_id: [
        { label: 'QuickSMS', billable: 34567, nonBillable: 678, total: 35245, cost: '£1,101.41', status: 'Finalised' },
        { label: 'PROMO', billable: 28934, nonBillable: 567, total: 29501, cost: '£922.01', status: 'Finalised' },
        { label: 'ALERTS', billable: 15678, nonBillable: 312, total: 15990, cost: '£499.69', status: 'Finalised' },
        { label: 'INFO', billable: 9876, nonBillable: 189, total: 10065, cost: '£314.53', status: 'Finalised' },
        { label: 'VERIFY', billable: 6789, nonBillable: 123, total: 6912, cost: '£216.00', status: 'Finalised' }
    ],
    product_type: [
        { label: 'SMS Standard', billable: 56789, nonBillable: 1123, total: 57912, cost: '£1,809.75', status: 'Finalised' },
        { label: 'SMS Premium', billable: 23456, nonBillable: 456, total: 23912, cost: '£747.25', status: 'Finalised' },
        { label: 'RCS Basic', billable: 12345, nonBillable: 234, total: 12579, cost: '£393.09', status: 'Finalised' },
        { label: 'RCS Rich', billable: 3890, nonBillable: 78, total: 3968, cost: '£123.98', status: 'Finalised' }
    ],
    group_name: [
        { label: 'VIP Customers', billable: 34567, nonBillable: 678, total: 35245, cost: '£1,101.41', status: 'Finalised' },
        { label: 'Newsletter', billable: 28934, nonBillable: 567, total: 29501, cost: '£922.01', status: 'Finalised' },
        { label: 'Promotions', billable: 18765, nonBillable: 367, total: 19132, cost: '£597.88', status: 'Finalised' },
        { label: 'Alerts', billable: 9876, nonBillable: 189, total: 10065, cost: '£314.53', status: 'Finalised' },
        { label: 'General', billable: 4321, nonBillable: 87, total: 4408, cost: '£137.75', status: 'Finalised' }
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    initDrillDownHandlers();
});

function initDrillDownHandlers() {
    document.getElementById('billingTableBody').addEventListener('click', function(e) {
        var row = e.target.closest('tr');
        if (!row) return;
        
        if (drillState.level === 0) {
            var monthCell = row.querySelector('td:first-child .fw-semibold');
            if (monthCell) {
                drillState.billingMonth = monthCell.textContent.trim();
                drillState.level = 1;
                showDimensionTabs();
                updateBreadcrumbs();
            }
        }
    });
    
    document.querySelectorAll('.drill-dimension-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var dimension = this.getAttribute('data-dimension');
            selectDimension(dimension);
        });
    });
}

function showDimensionTabs() {
    document.getElementById('drillDimensionTabs').style.display = 'block';
    document.getElementById('drillBreadcrumbs').style.display = 'block';
}

function hideDimensionTabs() {
    document.getElementById('drillDimensionTabs').style.display = 'none';
    document.querySelectorAll('.drill-dimension-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
}

function selectDimension(dimension) {
    drillState.dimension = dimension;
    drillState.level = 2;
    
    document.querySelectorAll('.drill-dimension-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.getAttribute('data-dimension') === dimension) {
            btn.classList.add('active');
        }
    });
    
    updateBreadcrumbs();
    renderDrillTable(dimension);
}

function updateBreadcrumbs() {
    var breadcrumbList = document.getElementById('drillBreadcrumbList');
    var html = '<li class="breadcrumb-item"><a href="#" onclick="resetDrillDown(); return false;">Finance Data</a></li>';
    
    if (drillState.level >= 1 && drillState.billingMonth) {
        if (drillState.level === 1) {
            html += '<li class="breadcrumb-item active" aria-current="page">' + drillState.billingMonth + '</li>';
        } else {
            html += '<li class="breadcrumb-item"><a href="#" onclick="stepBackToBillingMonth(); return false;">' + drillState.billingMonth + '</a></li>';
        }
    }
    
    if (drillState.level >= 2 && drillState.dimension) {
        html += '<li class="breadcrumb-item active" aria-current="page">' + dimensionLabels[drillState.dimension] + '</li>';
    }
    
    breadcrumbList.innerHTML = html;
}

function resetDrillDown() {
    drillState = { level: 0, billingMonth: null, dimension: null };
    document.getElementById('drillBreadcrumbs').style.display = 'none';
    hideDimensionTabs();
    renderMonthlyTable();
}

function stepBackToBillingMonth() {
    drillState.dimension = null;
    drillState.level = 1;
    document.querySelectorAll('.drill-dimension-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    updateBreadcrumbs();
    renderMonthlyTable();
}

function renderDrillTable(dimension) {
    var tableHead = document.querySelector('#financeDataTable thead tr');
    var firstCol = tableHead.querySelector('th:first-child');
    firstCol.innerHTML = dimensionLabels[dimension] + ' <i class="fas fa-sort ms-1 text-muted"></i>';
    firstCol.setAttribute('data-sort', dimension);
    
    var tbody = document.getElementById('billingTableBody');
    var data = mockDrillData[dimension] || [];
    
    var html = '';
    data.forEach(function(row) {
        var statusClass = 'table-success';
        if (row.status === 'Adjusted') statusClass = 'table-warning';
        if (row.status === 'Provisional') statusClass = 'table-info';
        
        var lockIcon = row.status === 'Finalised' ? '<i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>' : '';
        
        html += '<tr class="' + statusClass + '" data-status="' + row.status.toLowerCase() + '">';
        html += '<td><span class="fw-semibold">' + row.label + '</span>' + lockIcon + '</td>';
        html += '<td class="text-end">' + row.billable.toLocaleString() + '</td>';
        html += '<td class="text-end">' + row.nonBillable.toLocaleString() + '</td>';
        html += '<td class="text-end fw-semibold">' + row.total.toLocaleString() + '</td>';
        html += '<td class="text-end fw-semibold">' + row.cost + '</td>';
        html += '<td class="text-center">' + row.status + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    document.getElementById('rowCount').textContent = data.length;
    document.getElementById('totalCount').textContent = data.length;
}

function renderMonthlyTable() {
    var tableHead = document.querySelector('#financeDataTable thead tr');
    var firstCol = tableHead.querySelector('th:first-child');
    firstCol.innerHTML = 'Billing Month <i class="fas fa-sort ms-1 text-muted"></i>';
    firstCol.setAttribute('data-sort', 'billing_month');
    
    var monthlyData = [
        { label: 'December 2025', billable: 125432, nonBillable: 3218, total: 128650, cost: '£4,017.82', status: 'Finalised' },
        { label: 'November 2025', billable: 118756, nonBillable: 2891, total: 121647, cost: '£3,812.45', status: 'Finalised' },
        { label: 'October 2025', billable: 132890, nonBillable: 4102, total: 136992, cost: '£4,278.56', status: 'Finalised' },
        { label: 'September 2025', billable: 98234, nonBillable: 1567, total: 99801, cost: '£3,118.92', status: 'Adjusted' },
        { label: 'August 2025', billable: 145678, nonBillable: 5234, total: 150912, cost: '£4,715.89', status: 'Provisional' },
        { label: 'July 2025', billable: 112345, nonBillable: 2456, total: 114801, cost: '£3,587.23', status: 'Provisional' },
        { label: 'June 2025', billable: 108923, nonBillable: 3012, total: 111935, cost: '£3,498.12', status: 'Finalised' },
        { label: 'May 2025', billable: 95678, nonBillable: 1890, total: 97568, cost: '£3,048.67', status: 'Finalised' },
        { label: 'April 2025', billable: 87234, nonBillable: 2134, total: 89368, cost: '£2,792.34', status: 'Finalised' },
        { label: 'March 2025', billable: 102456, nonBillable: 2789, total: 105245, cost: '£3,289.01', status: 'Finalised' },
        { label: 'February 2025', billable: 78912, nonBillable: 1456, total: 80368, cost: '£2,511.45', status: 'Finalised' },
        { label: 'January 2025', billable: 91345, nonBillable: 2234, total: 93579, cost: '£2,924.78', status: 'Finalised' }
    ];
    
    var tbody = document.getElementById('billingTableBody');
    var html = '';
    
    monthlyData.forEach(function(row) {
        var statusClass = 'table-success';
        if (row.status === 'Adjusted') statusClass = 'table-warning';
        if (row.status === 'Provisional') statusClass = 'table-info';
        
        var lockIcon = row.status === 'Finalised' ? '<i class="fas fa-lock ms-2 text-muted small" title="Finalised - Locked"></i>' : '';
        
        html += '<tr class="' + statusClass + '" data-status="' + row.status.toLowerCase() + '">';
        html += '<td><span class="fw-semibold">' + row.label + '</span>' + lockIcon + '</td>';
        html += '<td class="text-end">' + row.billable.toLocaleString() + '</td>';
        html += '<td class="text-end">' + row.nonBillable.toLocaleString() + '</td>';
        html += '<td class="text-end fw-semibold">' + row.total.toLocaleString() + '</td>';
        html += '<td class="text-end fw-semibold">' + row.cost + '</td>';
        html += '<td class="text-center">' + row.status + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    document.getElementById('rowCount').textContent = monthlyData.length;
    document.getElementById('totalCount').textContent = monthlyData.length;
}

function getDrillStateForExport() {
    return {
        level: drillState.level,
        billingMonth: drillState.billingMonth,
        dimension: drillState.dimension,
        filters: {
            billingMonths: getSelectedValues('billingMonths'),
            subAccounts: getSelectedValues('subAccounts'),
            users: getSelectedValues('users'),
            groupNames: getSelectedValues('groupNames'),
            productTypes: getSelectedValues('productTypes'),
            senderIds: selectedSenderIds,
            messageTypes: getSelectedValues('messageTypes')
        }
    };
}

document.getElementById('btnExportCsv')?.addEventListener('click', function() {
    var exportState = getDrillStateForExport();
    console.log('[Finance Data] Export CSV with drill state:', exportState);
    new bootstrap.Modal(document.getElementById('exportModal')).show();
});

document.getElementById('btnExportPdf')?.addEventListener('click', function() {
    var exportState = getDrillStateForExport();
    console.log('[Finance Data] Export PDF with drill state:', exportState);
    new bootstrap.Modal(document.getElementById('exportModal')).show();
});
</script>
@endpush

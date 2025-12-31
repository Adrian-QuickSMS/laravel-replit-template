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

                    <div class="finance-data-table-wrapper">
                        <div id="tableContainer">
                            <table class="table table-striped table-hover" id="financeDataTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th scope="col" class="sortable" data-sort="date">
                                            Date/Time <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="transaction_id">
                                            Transaction ID <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="sub_account">
                                            Sub Account <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="user">
                                            User <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="type">
                                            Type <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="channel">
                                            Channel <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="country">
                                            Country <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="quantity">
                                            Quantity <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="unit_cost">
                                            Unit Cost <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="total">
                                            Total <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col" class="sortable" data-sort="balance">
                                            Balance <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
                                        <th scope="col">Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>31/12/2025 14:32:18</td>
                                        <td><code>TXN-20251231-001</code></td>
                                        <td>Main Account</td>
                                        <td>John Smith</td>
                                        <td><span class="badge bg-danger-light text-danger">Debit</span></td>
                                        <td>SMS</td>
                                        <td>UK</td>
                                        <td class="text-end">1,250</td>
                                        <td class="text-end">£0.032</td>
                                        <td class="text-end text-danger">-£40.00</td>
                                        <td class="text-end">£4,960.00</td>
                                        <td>Campaign: Winter Sale 2025</td>
                                    </tr>
                                    <tr>
                                        <td>31/12/2025 12:15:42</td>
                                        <td><code>TXN-20251231-002</code></td>
                                        <td>Marketing Team</td>
                                        <td>Sarah Johnson</td>
                                        <td><span class="badge bg-success-light text-success">Credit</span></td>
                                        <td>—</td>
                                        <td>—</td>
                                        <td class="text-end">5,000</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end text-success">+£150.00</td>
                                        <td class="text-end">£5,000.00</td>
                                        <td>Top-up: Credit Purchase</td>
                                    </tr>
                                    <tr>
                                        <td>30/12/2025 18:45:33</td>
                                        <td><code>TXN-20251230-001</code></td>
                                        <td>Support Team</td>
                                        <td>Mike Williams</td>
                                        <td><span class="badge bg-danger-light text-danger">Debit</span></td>
                                        <td>RCS</td>
                                        <td>DE</td>
                                        <td class="text-end">500</td>
                                        <td class="text-end">£0.045</td>
                                        <td class="text-end text-danger">-£22.50</td>
                                        <td class="text-end">£4,850.00</td>
                                        <td>Campaign: DE Customer Outreach</td>
                                    </tr>
                                    <tr>
                                        <td>30/12/2025 10:22:11</td>
                                        <td><code>TXN-20251230-002</code></td>
                                        <td>Sales Team</td>
                                        <td>Emma Davis</td>
                                        <td><span class="badge bg-warning-light text-warning">Refund</span></td>
                                        <td>SMS</td>
                                        <td>FR</td>
                                        <td class="text-end">100</td>
                                        <td class="text-end">£0.038</td>
                                        <td class="text-end text-success">+£3.80</td>
                                        <td class="text-end">£4,872.50</td>
                                        <td>Refund: Undelivered Messages</td>
                                    </tr>
                                    <tr>
                                        <td>29/12/2025 16:08:55</td>
                                        <td><code>TXN-20251229-001</code></td>
                                        <td>Main Account</td>
                                        <td>John Smith</td>
                                        <td><span class="badge bg-danger-light text-danger">Debit</span></td>
                                        <td>SMS</td>
                                        <td>UK</td>
                                        <td class="text-end">2,000</td>
                                        <td class="text-end">£0.032</td>
                                        <td class="text-end text-danger">-£64.00</td>
                                        <td class="text-end">£4,868.70</td>
                                        <td>Campaign: Holiday Greetings</td>
                                    </tr>
                                    <tr>
                                        <td>29/12/2025 09:30:00</td>
                                        <td><code>TXN-20251229-002</code></td>
                                        <td>Marketing Team</td>
                                        <td>Sarah Johnson</td>
                                        <td><span class="badge bg-info-light text-info">Adjustment</span></td>
                                        <td>—</td>
                                        <td>—</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end text-success">+£25.00</td>
                                        <td class="text-end">£4,932.70</td>
                                        <td>Promotional Credit Bonus</td>
                                    </tr>
                                    <tr>
                                        <td>28/12/2025 14:55:22</td>
                                        <td><code>TXN-20251228-001</code></td>
                                        <td>Support Team</td>
                                        <td>Mike Williams</td>
                                        <td><span class="badge bg-danger-light text-danger">Debit</span></td>
                                        <td>RCS</td>
                                        <td>US</td>
                                        <td class="text-end">750</td>
                                        <td class="text-end">£0.052</td>
                                        <td class="text-end text-danger">-£39.00</td>
                                        <td class="text-end">£4,907.70</td>
                                        <td>API: Automated Alerts</td>
                                    </tr>
                                    <tr>
                                        <td>28/12/2025 08:12:45</td>
                                        <td><code>TXN-20251228-002</code></td>
                                        <td>Sales Team</td>
                                        <td>Emma Davis</td>
                                        <td><span class="badge bg-danger-light text-danger">Debit</span></td>
                                        <td>SMS</td>
                                        <td>IE</td>
                                        <td class="text-end">300</td>
                                        <td class="text-end">£0.035</td>
                                        <td class="text-end text-danger">-£10.50</td>
                                        <td class="text-end">£4,946.70</td>
                                        <td>Campaign: IE Promotions</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="finance-data-footer border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted small">Showing <strong>8</strong> of <strong>8</strong> transactions</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnExportCsv" disabled>
                                    <i class="fas fa-file-csv me-1"></i> Export CSV
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnExportPdf" disabled>
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
</script>
@endpush

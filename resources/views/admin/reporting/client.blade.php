@extends('layouts.admin')

@section('title', 'Client Reporting')

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
    overflow: visible;
}
#filtersPanel {
    overflow: visible !important;
}
#filtersPanel .card-body {
    overflow: visible !important;
}
#filtersPanel .row {
    overflow: visible;
}
#filtersPanel .dropdown-menu {
    z-index: 1050;
}
.multiselect-dropdown {
    position: relative;
}
.predictive-input-wrapper {
    position: relative;
}
.predictive-suggestions {
    z-index: 1050;
}
.finance-data-table-wrapper {
    flex: 1 1 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
    max-height: 100%;
}
#tableContainer {
    flex: 1 1 0;
    overflow-y: auto !important;
    overflow-x: auto;
    min-height: 0;
    max-height: 100%;
}
#tableContainer.table-responsive {
    overflow-y: auto !important;
    max-height: none;
}
.finance-data-footer {
    flex-shrink: 0;
    margin-top: auto;
}
#financeDataTable {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}
#financeDataTable thead th {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 0.75rem 0.5rem !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    color: #495057 !important;
    white-space: nowrap;
    text-transform: none !important;
    letter-spacing: normal !important;
}
#financeDataTable thead th:hover {
    background: #e9ecef !important;
}
#financeDataTable tbody tr {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
#financeDataTable tbody tr:hover td {
    background-color: #f8f9fa !important;
}
#financeDataTable tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
    color: #495057;
}
#financeDataTable tbody tr:last-child td {
    border-bottom: none;
}
#financeDataTable .drill-label {
    font-weight: 500;
    color: #343a40;
}
.month-total-row {
    background-color: rgba(30, 58, 95, 0.06) !important;
}
.month-total-row td {
    font-weight: 600;
}
.group-total-row {
    background-color: rgba(30, 58, 95, 0.03) !important;
}
.drill-row:hover {
    background-color: rgba(30, 58, 95, 0.08) !important;
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
    border-color: var(--admin-primary, #1e3a5f);
}
.month-preset-btn.active {
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    border-color: var(--admin-primary, #1e3a5f);
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
    border-color: var(--admin-primary, #1e3a5f);
    box-shadow: 0 0 0 0.2rem rgba(30, 58, 95, 0.25);
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
    background: var(--admin-primary, #1e3a5f);
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
    background-color: var(--admin-secondary, #2d5a87) !important;
    border-color: var(--admin-secondary, #2d5a87) !important;
    color: #fff !important;
}
.breadcrumb-item a {
    color: var(--admin-secondary, #2d5a87);
    text-decoration: none;
}
.breadcrumb-item a:hover {
    text-decoration: underline;
}
.breadcrumb-item.active {
    color: #495057;
    font-weight: 500;
}
.row-locked {
    opacity: 0.85;
}
.row-locked td {
    position: relative;
}
#billingTableBody tr.row-locked {
    cursor: default;
}
#billingTableBody tr.row-locked:hover td {
    filter: none;
}
/* Skeleton loader styles */
.skeleton-row td {
    padding: 1rem 0.75rem !important;
}
.skeleton-cell {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.5s infinite;
    border-radius: 4px;
    height: 1rem;
    display: inline-block;
}
.skeleton-cell.w-80 { width: 80%; }
.skeleton-cell.w-60 { width: 60%; }
.skeleton-cell.w-40 { width: 40%; }
.skeleton-cell.w-100 { width: 100%; }
@keyframes skeleton-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.table-loading {
    pointer-events: none;
    opacity: 0.7;
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
<div class="admin-page">
    <div class="admin-breadcrumb mb-3">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Reporting</a>
        <span class="separator">/</span>
        <span>Client Reporting</span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-1" style="color: var(--admin-primary, #1e3a5f); font-weight: 600;">Client Reporting</h4>
            <p class="text-muted mb-0 small">Finance data across all customer accounts</p>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="fas fa-download me-1"></i> Export
        </button>
    </div>

    <div class="card shadow-sm mb-4" style="border: none; border-radius: 0.5rem;">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div style="max-width: 400px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search account, invoice ref...">
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border-color: #dee2e6; color: #495057;">
                    <i class="fas fa-filter me-1" style="color: var(--admin-primary, #1e3a5f);"></i> Filters
                </button>
            </div>
        </div>
    </div>
    
    <div class="container-fluid finance-data-container p-0">
        <div class="row flex-grow-1" style="min-height: 0;">
            <div class="col-12 d-flex flex-column" style="min-height: 0;">
                <div class="card">
                    <div class="card-body">
                        <div class="finance-data-fixed-header">
                            <div class="collapse mb-3" id="filtersPanel">
                                <div class="card card-body border-0 rounded-3" style="background: linear-gradient(135deg, rgba(30, 58, 95, 0.05) 0%, rgba(74, 144, 217, 0.08) 100%);">
                                    <!-- Admin-only Account Filter -->
                                    <div class="row g-3 align-items-end mb-3">
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Account</label>
                                            <div class="position-relative" id="accountFilterWrapper">
                                                <input type="text" class="form-control form-control-sm" id="accountFilter" placeholder="All Accounts" autocomplete="off">
                                                <input type="hidden" id="selectedAccountId" value="">
                                                <div class="position-absolute w-100 bg-white border rounded-bottom shadow-sm" id="accountSuggestions" style="display: none; max-height: 200px; overflow-y: auto; z-index: 1050; top: 100%;">
                                                </div>
                                            </div>
                                            <small class="text-muted">Type to search or leave empty for all accounts</small>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <!-- Existing filters -->
                                <div class="row g-3 align-items-end">
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Billing Month</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="billingMonths">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Months</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="max-height: 280px; overflow-y: auto;">
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
                                            <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
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
                                            <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
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
                                        <label class="form-label small fw-bold">Product</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="products">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Products</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="SMS" id="product1"><label class="form-check-label small" for="product1">SMS</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="RCS Basic" id="product2"><label class="form-check-label small" for="product2">RCS Basic</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="RCS Rich" id="product3"><label class="form-check-label small" for="product3">RCS Rich</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Sender ID</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="senderIds">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Sender IDs</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="QuickSMS" id="sender1"><label class="form-check-label small" for="sender1">QuickSMS</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="PROMO" id="sender2"><label class="form-check-label small" for="sender2">PROMO</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="ALERTS" id="sender3"><label class="form-check-label small" for="sender3">ALERTS</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="INFO" id="sender4"><label class="form-check-label small" for="sender4">INFO</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="NOTIFY" id="sender5"><label class="form-check-label small" for="sender5">NOTIFY</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="VERIFY" id="sender6"><label class="form-check-label small" for="sender6">VERIFY</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="UPDATES" id="sender7"><label class="form-check-label small" for="sender7">UPDATES</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="NEWS" id="sender8"><label class="form-check-label small" for="sender8">NEWS</label></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-3 align-items-end mt-2">
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Invoice Ref</label>
                                        <input type="text" class="form-control form-control-sm" id="invoiceRefFilter" placeholder="e.g. INV-2025-0001">
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Origin</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="origins">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Origins</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Portal" id="origin1"><label class="form-check-label small" for="origin1">Portal</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="API" id="origin2"><label class="form-check-label small" for="origin2">API</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Email-to-SMS" id="origin3"><label class="form-check-label small" for="origin3">Email-to-SMS</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Integration" id="origin4"><label class="form-check-label small" for="origin4">Integration</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Country</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="countries">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Countries</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="United Kingdom" id="country1"><label class="form-check-label small" for="country1">United Kingdom</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="United States" id="country2"><label class="form-check-label small" for="country2">United States</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Germany" id="country3"><label class="form-check-label small" for="country3">Germany</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="France" id="country4"><label class="form-check-label small" for="country4">France</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Ireland" id="country5"><label class="form-check-label small" for="country5">Ireland</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Spain" id="country6"><label class="form-check-label small" for="country6">Spain</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Italy" id="country7"><label class="form-check-label small" for="country7">Italy</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Netherlands" id="country8"><label class="form-check-label small" for="country8">Netherlands</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Belgium" id="country9"><label class="form-check-label small" for="country9">Belgium</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Australia" id="country10"><label class="form-check-label small" for="country10">Australia</label></div>
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

                    <div class="mb-3" id="drillDimensionSelector">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small me-2"><i class="fas fa-layer-group me-1"></i>Drill by:</span>
                            <div class="btn-group flex-wrap" role="group" aria-label="Drill dimension selector" id="dimensionButtons">
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="day">
                                    <i class="fas fa-calendar-day me-1"></i>Day
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="product">
                                    <i class="fas fa-box me-1"></i>Product
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="sub_account">
                                    <i class="fas fa-building me-1"></i>Sub Account
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="user">
                                    <i class="fas fa-user me-1"></i>User
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="sender_id">
                                    <i class="fas fa-signature me-1"></i>Sender ID
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="origin">
                                    <i class="fas fa-paper-plane me-1"></i>Origin
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="country">
                                    <i class="fas fa-globe me-1"></i>Country
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="group_name">
                                    <i class="fas fa-users me-1"></i>Group Name
                                </button>
                            </div>
                            <span class="text-muted small ms-2" id="drillInstruction" style="display: none;">
                                <i class="fas fa-arrow-right me-1"></i>Click a row to drill down
                            </span>
                        </div>
                    </div>

                    <div class="finance-data-table-wrapper">
                        <div class="table-responsive" id="tableContainer">
                            <table class="table display" id="financeDataTable" style="min-width: 900px">
                                <thead>
                                    <tr>
                                        <th scope="col" class="sortable" data-sort="account">
                                            Account <i class="fas fa-sort ms-1 text-muted"></i>
                                        </th>
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
                                    <tr class="drill-row" data-status="finalised" data-value="December 2025" data-account="ACC-001">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-001" style="color: var(--admin-primary, #1e3a5f);">Acme Corporation</a></td>
                                        <td><span class="drill-label fw-semibold">December 2025</span></td>
                                        <td class="text-end">45,230</td>
                                        <td class="text-end">821</td>
                                        <td class="text-end fw-semibold">46,051</td>
                                        <td class="text-end fw-semibold">£1,447.60</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="December 2025" data-account="ACC-002">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-002" style="color: var(--admin-primary, #1e3a5f);">Finance Ltd</a></td>
                                        <td><span class="drill-label fw-semibold">December 2025</span></td>
                                        <td class="text-end">38,500</td>
                                        <td class="text-end">650</td>
                                        <td class="text-end fw-semibold">39,150</td>
                                        <td class="text-end fw-semibold">£1,230.72</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="December 2025" data-account="ACC-003">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-003" style="color: var(--admin-primary, #1e3a5f);">Tech Solutions</a></td>
                                        <td><span class="drill-label fw-semibold">December 2025</span></td>
                                        <td class="text-end">47,000</td>
                                        <td class="text-end">850</td>
                                        <td class="text-end fw-semibold">47,850</td>
                                        <td class="text-end fw-semibold">£1,505.04</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="November 2025" data-account="ACC-001">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-001" style="color: var(--admin-primary, #1e3a5f);">Acme Corporation</a></td>
                                        <td><span class="drill-label fw-semibold">November 2025</span></td>
                                        <td class="text-end">32,157</td>
                                        <td class="text-end">559</td>
                                        <td class="text-end fw-semibold">32,716</td>
                                        <td class="text-end fw-semibold">£1,028.44</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="November 2025" data-account="ACC-002">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-002" style="color: var(--admin-primary, #1e3a5f);">Finance Ltd</a></td>
                                        <td><span class="drill-label fw-semibold">November 2025</span></td>
                                        <td class="text-end">31,000</td>
                                        <td class="text-end">500</td>
                                        <td class="text-end fw-semibold">31,500</td>
                                        <td class="text-end fw-semibold">£990.30</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="November 2025" data-account="ACC-003">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-003" style="color: var(--admin-primary, #1e3a5f);">Tech Solutions</a></td>
                                        <td><span class="drill-label fw-semibold">November 2025</span></td>
                                        <td class="text-end">30,000</td>
                                        <td class="text-end">500</td>
                                        <td class="text-end fw-semibold">30,500</td>
                                        <td class="text-end fw-semibold">£962.28</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="adjusted" data-value="October 2025" data-account="ACC-001">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-001" style="color: var(--admin-primary, #1e3a5f);">Acme Corporation</a></td>
                                        <td><span class="drill-label fw-semibold">October 2025</span></td>
                                        <td class="text-end">35,439</td>
                                        <td class="text-end">1,829</td>
                                        <td class="text-end fw-semibold">37,268</td>
                                        <td class="text-end fw-semibold">£1,171.53</td>
                                        <td class="text-center"><span class="badge light badge-warning">Adjusted</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="October 2025" data-account="ACC-002">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-002" style="color: var(--admin-primary, #1e3a5f);">Finance Ltd</a></td>
                                        <td><span class="drill-label fw-semibold">October 2025</span></td>
                                        <td class="text-end">32,500</td>
                                        <td class="text-end">2,000</td>
                                        <td class="text-end fw-semibold">34,500</td>
                                        <td class="text-end fw-semibold">£1,084.35</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="October 2025" data-account="ACC-003">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-003" style="color: var(--admin-primary, #1e3a5f);">Tech Solutions</a></td>
                                        <td><span class="drill-label fw-semibold">October 2025</span></td>
                                        <td class="text-end">32,500</td>
                                        <td class="text-end">2,000</td>
                                        <td class="text-end fw-semibold">34,500</td>
                                        <td class="text-end fw-semibold">£958.17</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="provisional" data-value="September 2025" data-account="ACC-004">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-004" style="color: var(--admin-primary, #1e3a5f);">Retail Group</a></td>
                                        <td><span class="drill-label fw-semibold">September 2025</span></td>
                                        <td class="text-end">45,711</td>
                                        <td class="text-end">705</td>
                                        <td class="text-end fw-semibold">46,416</td>
                                        <td class="text-end fw-semibold">£1,459.68</td>
                                        <td class="text-center"><span class="badge light badge-info">Provisional</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="provisional" data-value="September 2025" data-account="ACC-005">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-005" style="color: var(--admin-primary, #1e3a5f);">Healthcare UK</a></td>
                                        <td><span class="drill-label fw-semibold">September 2025</span></td>
                                        <td class="text-end">45,000</td>
                                        <td class="text-end">700</td>
                                        <td class="text-end fw-semibold">45,700</td>
                                        <td class="text-end fw-semibold">£1,443.07</td>
                                        <td class="text-center"><span class="badge light badge-info">Provisional</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="August 2025" data-account="ACC-001">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-001" style="color: var(--admin-primary, #1e3a5f);">Acme Corporation</a></td>
                                        <td><span class="drill-label fw-semibold">August 2025</span></td>
                                        <td class="text-end">67,639</td>
                                        <td class="text-end">2,891</td>
                                        <td class="text-end fw-semibold">70,530</td>
                                        <td class="text-end fw-semibold">£2,164.45</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
                                    </tr>
                                    <tr class="drill-row" data-status="finalised" data-value="August 2025" data-account="ACC-002">
                                        <td><a href="#" class="text-decoration-none account-link" data-account-id="ACC-002" style="color: var(--admin-primary, #1e3a5f);">Finance Ltd</a></td>
                                        <td><span class="drill-label fw-semibold">August 2025</span></td>
                                        <td class="text-end">67,639</td>
                                        <td class="text-end">2,892</td>
                                        <td class="text-end fw-semibold">70,531</td>
                                        <td class="text-end fw-semibold">£2,164.45</td>
                                        <td class="text-center"><span class="badge light badge-success">Finalised</span></td>
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

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportProgressModal" tabindex="-1" aria-labelledby="exportProgressModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportProgressModalLabel"><i class="fas fa-download me-2 text-primary"></i>Exporting Data</h5>
            </div>
            <div class="modal-body text-center py-4">
                <div id="exportInProgress">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Exporting...</span>
                    </div>
                    <p class="mb-1">Preparing your export...</p>
                    <p class="small text-muted mb-0" id="exportProgressText">Processing <span id="exportRowsCount">0</span> rows</p>
                </div>
                <div id="exportComplete" style="display: none;">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <p class="mb-1 fw-bold">Export Complete!</p>
                    <p class="small text-muted mb-0" id="exportCompleteText">Your file is ready for download.</p>
                </div>
                <div id="exportQueued" style="display: none;">
                    <i class="fas fa-clock text-info fa-3x mb-3"></i>
                    <p class="mb-1 fw-bold">Export Queued</p>
                    <p class="small text-muted mb-0">Large dataset detected. Your export has been queued and will be available in the Download Centre when ready.</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary btn-sm" id="btnCloseExportModal" data-bs-dismiss="modal" style="display: none;">
                    <i class="fas fa-check me-1"></i> Done
                </button>
                <a href="/reporting/download-area" class="btn btn-outline-primary btn-sm" id="btnGoToDownloads" style="display: none;">
                    <i class="fas fa-folder-open me-1"></i> Go to Download Centre
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="saveReportModal" tabindex="-1" aria-labelledby="saveReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveReportModalLabel"><i class="fas fa-save me-2 text-primary"></i>Save Report Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="reportName" class="form-label small fw-bold">Report Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="reportName" placeholder="e.g., Monthly Finance Summary">
                </div>
                <div class="mb-3">
                    <label for="reportDescription" class="form-label small fw-bold">Description (optional)</label>
                    <textarea class="form-control form-control-sm" id="reportDescription" rows="2" placeholder="Brief description of this saved report"></textarea>
                </div>
                <div class="border rounded p-3 bg-light mb-3">
                    <p class="small fw-bold mb-2"><i class="fas fa-info-circle me-1 text-primary"></i>Configuration to be saved:</p>
                    <ul class="small text-muted mb-0" id="savedConfigPreview">
                        <li>Filters: <span id="previewFilters">None selected</span></li>
                        <li>Drill Level: <span id="previewDrillLevel">Billing Month</span></li>
                        <li>Sort: <span id="previewSort">Default</span></li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfirmSaveReport">
                    <i class="fas fa-save me-1"></i> Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="scheduleReportModal" tabindex="-1" aria-labelledby="scheduleReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleReportModalLabel"><i class="fas fa-clock me-2 text-primary"></i>Schedule Report Export</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="scheduleName" class="form-label small fw-bold">Schedule Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="scheduleName" placeholder="e.g., Weekly Finance Export">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Frequency <span class="text-danger">*</span></label>
                    <div class="btn-group w-100" role="group" aria-label="Frequency selection">
                        <input type="radio" class="btn-check" name="scheduleFrequency" id="freqMonthly" value="monthly" checked>
                        <label class="btn btn-outline-primary btn-sm" for="freqMonthly">Monthly</label>
                        <input type="radio" class="btn-check" name="scheduleFrequency" id="freqWeekly" value="weekly">
                        <label class="btn btn-outline-primary btn-sm" for="freqWeekly">Weekly</label>
                        <input type="radio" class="btn-check" name="scheduleFrequency" id="freqCustom" value="custom">
                        <label class="btn btn-outline-primary btn-sm" for="freqCustom">Custom</label>
                    </div>
                </div>
                <div class="mb-3" id="monthlyOptions">
                    <label class="form-label small fw-bold">Day of Month</label>
                    <select class="form-select form-select-sm" id="monthlyDay">
                        <option value="1">1st</option>
                        <option value="5">5th</option>
                        <option value="10">10th</option>
                        <option value="15">15th</option>
                        <option value="last">Last day</option>
                    </select>
                </div>
                <div class="mb-3" id="weeklyOptions" style="display: none;">
                    <label class="form-label small fw-bold">Day of Week</label>
                    <select class="form-select form-select-sm" id="weeklyDay">
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                    </select>
                </div>
                <div class="mb-3" id="customOptions" style="display: none;">
                    <label class="form-label small fw-bold">Cron Expression</label>
                    <input type="text" class="form-control form-control-sm" id="customCron" placeholder="0 9 * * 1">
                    <small class="text-muted">Format: minute hour day month weekday</small>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Export Format</label>
                    <select class="form-select form-select-sm" id="scheduleFormat">
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Recipients (optional)</label>
                    <input type="text" class="form-control form-control-sm" id="scheduleRecipients" placeholder="email1@example.com, email2@example.com">
                    <small class="text-muted">Separate multiple emails with commas</small>
                </div>
                <div class="border rounded p-3 bg-light">
                    <p class="small fw-bold mb-2"><i class="fas fa-info-circle me-1 text-primary"></i>Report will include:</p>
                    <ul class="small text-muted mb-0">
                        <li>Current filter configuration</li>
                        <li>Current drill-down level</li>
                        <li>Available in Download Centre after generation</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfirmSchedule">
                    <i class="fas fa-clock me-1"></i> Create Schedule
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i><span id="toastMessage">Success!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// TODO: Replace with backend session/auth data
// Role hierarchy: viewer < analyst < admin
// Viewer: Can view table only
// Analyst: Can export and save reports
// Admin/Finance: Full access (export, save, schedule)
var currentUserRole = 'admin'; // Options: 'viewer', 'analyst', 'admin'

var ROLE_HIERARCHY = {
    'viewer': 0,
    'analyst': 1,
    'admin': 2
};

document.addEventListener('DOMContentLoaded', function() {
    applyRoleBasedVisibility();
    initMultiselectDropdowns();
    initMonthPresets();
    initFilterActions();
    initSenderIdPredictive();
    handleUrlParameters();
    initSubAccountUserFiltering();
    initAccountFilter();
    initAccountLinks();
    loadInitialData();
});

// Admin-only: Mock accounts data for global view
var allAccounts = [
    { id: 'ACC-001', name: 'Acme Corporation' },
    { id: 'ACC-002', name: 'Finance Ltd' },
    { id: 'ACC-003', name: 'Tech Solutions' },
    { id: 'ACC-004', name: 'Retail Group' },
    { id: 'ACC-005', name: 'Healthcare UK' },
    { id: 'ACC-006', name: 'Media Partners' },
    { id: 'ACC-007', name: 'Logistics Pro' },
    { id: 'ACC-008', name: 'Education First' }
];

// Admin-only: Initialize Account filter with typeahead
function initAccountFilter() {
    var accountInput = document.getElementById('accountFilter');
    var suggestionsDiv = document.getElementById('accountSuggestions');
    var selectedAccountId = document.getElementById('selectedAccountId');
    
    if (!accountInput || !suggestionsDiv) return;
    
    accountInput.addEventListener('input', function() {
        var query = this.value.toLowerCase().trim();
        selectedAccountId.value = '';
        
        if (query.length === 0) {
            suggestionsDiv.style.display = 'none';
            return;
        }
        
        var matches = allAccounts.filter(function(acc) {
            return acc.name.toLowerCase().includes(query) || acc.id.toLowerCase().includes(query);
        });
        
        if (matches.length === 0) {
            suggestionsDiv.innerHTML = '<div class="p-2 text-muted small">No accounts found</div>';
        } else {
            suggestionsDiv.innerHTML = matches.map(function(acc) {
                return '<div class="account-suggestion p-2 small" data-id="' + acc.id + '" data-name="' + acc.name + '" style="cursor: pointer;">' +
                    '<strong>' + acc.name + '</strong> <span class="text-muted">(' + acc.id + ')</span></div>';
            }).join('');
        }
        suggestionsDiv.style.display = 'block';
    });
    
    suggestionsDiv.addEventListener('click', function(e) {
        var suggestion = e.target.closest('.account-suggestion');
        if (suggestion) {
            accountInput.value = suggestion.dataset.name;
            selectedAccountId.value = suggestion.dataset.id;
            suggestionsDiv.style.display = 'none';
        }
    });
    
    accountInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            suggestionsDiv.style.display = 'block';
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#accountFilterWrapper')) {
            suggestionsDiv.style.display = 'none';
        }
    });
    
    // Clear button functionality
    accountInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            selectedAccountId.value = '';
            suggestionsDiv.style.display = 'none';
        }
    });
}

// Admin-only: Handle account name clicks to navigate to Account Details
function initAccountLinks() {
    document.addEventListener('click', function(e) {
        var accountLink = e.target.closest('.account-link');
        if (accountLink) {
            e.preventDefault();
            var accountId = accountLink.dataset.accountId;
            // Navigate to Admin Account Details page (stub route if not built)
            var accountDetailsUrl = '/admin/accounts/' + accountId;
            console.log('[Client Reporting] Navigating to account details:', accountDetailsUrl);
            window.location.href = accountDetailsUrl;
        }
    });
}

function applyRoleBasedVisibility() {
    var userRoleLevel = ROLE_HIERARCHY[currentUserRole] || 0;
    
    document.querySelectorAll('[data-requires-role]').forEach(function(el) {
        var requiredRole = el.getAttribute('data-requires-role');
        var requiredLevel = ROLE_HIERARCHY[requiredRole] || 0;
        
        if (userRoleLevel < requiredLevel) {
            el.style.display = 'none';
        } else {
            el.style.display = '';
        }
    });
    
    // Handle cost column visibility (only for admin/finance)
    if (currentUserRole !== 'admin') {
        document.querySelectorAll('[data-requires-cost-view]').forEach(function(el) {
            el.style.display = 'none';
        });
    }
    
    console.log('[Finance Data] Role-based visibility applied for role:', currentUserRole);
}

function hasPermission(requiredRole) {
    var userRoleLevel = ROLE_HIERARCHY[currentUserRole] || 0;
    var requiredLevel = ROLE_HIERARCHY[requiredRole] || 0;
    return userRoleLevel >= requiredLevel;
}

// Debounce utility for text filters
function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this;
        var args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

// Skeleton loader functions
function showSkeletonLoader(rowCount) {
    var tbody = document.getElementById('billingTableBody');
    var skeletonHtml = '';
    rowCount = rowCount || 8;
    
    for (var i = 0; i < rowCount; i++) {
        skeletonHtml += '<tr class="skeleton-row">' +
            '<td><span class="skeleton-cell w-80"></span></td>' +
            '<td><span class="skeleton-cell w-80"></span></td>' +
            '<td class="text-end"><span class="skeleton-cell w-60"></span></td>' +
            '<td class="text-end"><span class="skeleton-cell w-40"></span></td>' +
            '<td class="text-end"><span class="skeleton-cell w-40"></span></td>' +
            '<td class="text-end"><span class="skeleton-cell w-60"></span></td>' +
            '<td><span class="skeleton-cell w-80"></span></td>' +
            '</tr>';
    }
    
    tbody.innerHTML = skeletonHtml;
    document.getElementById('tableContainer').classList.add('table-loading');
}

function hideSkeletonLoader() {
    document.getElementById('tableContainer').classList.remove('table-loading');
}

// Non-blocking data loading with requestAnimationFrame
function loadDataAsync(callback) {
    showSkeletonLoader(10);
    
    // Use setTimeout to prevent UI freeze
    setTimeout(function() {
        requestAnimationFrame(function() {
            callback();
            hideSkeletonLoader();
        });
    }, 50);
}

// Billing API Service Layer
var BillingService = {
    baseUrl: '/api/billing',
    
    getData: function(filters) {
        var params = new URLSearchParams();
        
        if (filters.billingMonths && filters.billingMonths.length) {
            filters.billingMonths.forEach(function(m) { params.append('billingMonth[]', m); });
        }
        if (filters.subAccounts && filters.subAccounts.length) {
            filters.subAccounts.forEach(function(s) { params.append('subAccount[]', s); });
        }
        if (filters.users && filters.users.length) {
            filters.users.forEach(function(u) { params.append('user[]', u); });
        }
        if (filters.groupNames && filters.groupNames.length) {
            filters.groupNames.forEach(function(g) { params.append('groupName[]', g); });
        }
        if (filters.products && filters.products.length) {
            filters.products.forEach(function(p) { params.append('product[]', p); });
        }
        if (filters.senderIds && filters.senderIds.length) {
            filters.senderIds.forEach(function(s) { params.append('senderID[]', s); });
        }
        if (filters.origins && filters.origins.length) {
            filters.origins.forEach(function(o) { params.append('origin[]', o); });
        }
        if (filters.countries && filters.countries.length) {
            filters.countries.forEach(function(c) { params.append('country[]', c); });
        }
        
        var url = this.baseUrl + '/data' + (params.toString() ? '?' + params.toString() : '');
        
        return fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(response) {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        });
    },
    
    export: function(format, filters, rowCount) {
        var params = new URLSearchParams();
        params.append('format', format);
        params.append('rowCount', rowCount);
        
        Object.keys(filters).forEach(function(key) {
            if (Array.isArray(filters[key])) {
                filters[key].forEach(function(v) { params.append(key + '[]', v); });
            } else if (filters[key]) {
                params.append(key, filters[key]);
            }
        });
        
        return fetch(this.baseUrl + '/export?' + params.toString(), {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(response) {
            return response.json();
        });
    },
    
    getSavedReports: function() {
        return fetch(this.baseUrl + '/saved-reports', {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(response) {
            return response.json();
        });
    },
    
    saveReport: function(name, filters) {
        return fetch(this.baseUrl + '/saved-reports', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name: name, filters: filters })
        }).then(function(response) {
            return response.json();
        });
    },
    
    schedule: function(config) {
        return fetch(this.baseUrl + '/schedule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(config)
        }).then(function(response) {
            return response.json();
        });
    }
};

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

function handleUrlParameters() {
    var urlParams = new URLSearchParams(window.location.search);
    var hasFilters = false;
    
    var billingMonth = urlParams.get('billingMonth');
    if (billingMonth) {
        var dropdown = document.querySelector('[data-filter="billingMonths"]');
        if (dropdown) {
            var checkbox = dropdown.querySelector('input[value="' + billingMonth + '"]');
            if (checkbox) {
                checkbox.checked = true;
                updateMultiselectLabel(dropdown);
                hasFilters = true;
            }
        }
    }
    
    var invoiceRef = urlParams.get('invoiceRef');
    if (invoiceRef) {
        var invoiceRefInput = document.getElementById('invoiceRefFilter');
        if (invoiceRefInput) {
            invoiceRefInput.value = invoiceRef;
            hasFilters = true;
        }
    }
    
    if (hasFilters) {
        document.getElementById('filtersPanel').classList.add('show');
        
        var fromInvoice = urlParams.get('fromInvoice');
        if (fromInvoice) {
            var alertHtml = '<div class="alert alert-pastel-primary alert-dismissible fade show mb-3" role="alert">' +
                '<i class="fas fa-link text-primary me-2"></i>' +
                '<strong>Linked from Invoice ' + fromInvoice + '</strong> - ' +
                'Showing billing data for reconciliation. Invoice totals are not recalculated here.' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
            var filterPanel = document.getElementById('filtersPanel');
            filterPanel.insertAdjacentHTML('beforebegin', alertHtml);
        }
        
        setTimeout(function() {
            document.getElementById('btnApplyFilters').click();
        }, 100);
    }
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
    
    // Debounce input to prevent UI freeze on rapid typing
    var debouncedFilter = debounce(function(value) {
        filterSuggestions(value);
    }, 300);
    
    input.addEventListener('input', function() {
        debouncedFilter(this.value);
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
    
    // Admin-only: Account filter
    var selectedAccountId = document.getElementById('selectedAccountId');
    var accountFilter = document.getElementById('accountFilter');
    if (selectedAccountId && selectedAccountId.value) {
        activeFilters.push({ type: 'Account', value: accountFilter.value, id: selectedAccountId.value });
    }
    
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
    
    var filterParams = {
        account: selectedAccountId ? selectedAccountId.value : '',
        billingMonths: getSelectedValues('billingMonths'),
        subAccounts: getSelectedValues('subAccounts'),
        users: getSelectedValues('users'),
        groupNames: getSelectedValues('groupNames'),
        productTypes: getSelectedValues('productTypes'),
        senderIds: selectedSenderIds,
        messageTypes: getSelectedValues('messageTypes')
    };
    
    console.log('[Client Reporting] Applying filters:', filterParams);
    
    // Admin-only: Filter table rows by account
    filterTableByAccount(filterParams.account);
    
    // Use async loading to prevent UI freeze
    loadDataAsync(function() {
        refreshTableData(filterParams);
        console.log('[Finance Data] Data refresh complete');
    });
}

function refreshTableData(filters) {
    BillingService.getData(filters)
        .then(function(response) {
            renderBillingTable(response.data);
        })
        .catch(function(error) {
            console.error('[Finance Data] API error:', error);
            showToast('Failed to load billing data. Please try again.', 'error');
            hideSkeletonLoader();
        });
}

function renderBillingTable(data) {
    var tbody = document.getElementById('billingTableBody');
    
    var html = '';
    data.forEach(function(row) {
        var attrs = getRowAttributes(row.billingStatus);
        var accountId = row.accountId || '';
        var accountName = row.accountName || '';
        
        // Admin-only: Include Account column and data-account attribute
        html += '<tr class="' + attrs.classes + '"' + attrs.attrs + ' data-month="' + row.billingMonth + '" data-value="' + row.billingMonthLabel + '" data-status="' + row.billingStatus.toLowerCase() + '" data-account="' + accountId + '">' +
            '<td><a href="#" class="text-decoration-none account-link" data-account-id="' + accountId + '" style="color: var(--admin-primary, #1e3a5f);">' + accountName + '</a></td>' +
            '<td>' +
                '<span class="drill-label fw-semibold">' + row.billingMonthLabel + '</span>' +
                attrs.labelIcon +
            '</td>' +
            '<td class="text-end">' + formatNumber(row.billableParts) + '</td>' +
            '<td class="text-end">' + formatNumber(row.nonBillableParts) + '</td>' +
            '<td class="text-end fw-semibold">' + formatNumber(row.totalParts) + '</td>' +
            '<td class="text-end fw-semibold">' + formatCurrency(row.totalCost) + '</td>' +
            '<td class="text-center">' + attrs.statusBadge + '</td>' +
            '</tr>';
    });
    
    tbody.innerHTML = html;
    
    var rowCountEl = document.getElementById('rowCount');
    var totalCountEl = document.getElementById('totalCount');
    if (rowCountEl) rowCountEl.textContent = data.length;
    if (totalCountEl) totalCountEl.textContent = data.length;
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatCurrency(amount) {
    return '£' + parseFloat(amount).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function showToast(message, type) {
    var toast = document.getElementById('successToast');
    var toastMessage = document.getElementById('toastMessage');
    
    toast.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    
    switch(type) {
        case 'error':
            toast.classList.add('bg-danger');
            break;
        case 'warning':
            toast.classList.add('bg-warning');
            break;
        case 'info':
            toast.classList.add('bg-info');
            break;
        default:
            toast.classList.add('bg-success');
    }
    
    toastMessage.textContent = message;
    var bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

function loadInitialData() {
    loadDataAsync(function() {
        refreshTableData({});
    });
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
    
    // Admin-only: Clear Account filter
    var accountFilter = document.getElementById('accountFilter');
    var selectedAccountId = document.getElementById('selectedAccountId');
    if (accountFilter) accountFilter.value = '';
    if (selectedAccountId) selectedAccountId.value = '';
    
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
    
    // Admin-only: Show all rows (clear account filter)
    filterTableByAccount('');
    
    document.getElementById('activeFiltersContainer').style.display = 'none';
    document.getElementById('activeFiltersChips').innerHTML = '';
}

// Admin-only: Filter table rows by account
function filterTableByAccount(accountId) {
    var rows = document.querySelectorAll('#billingTableBody tr.drill-row');
    rows.forEach(function(row) {
        if (!accountId || row.dataset.account === accountId) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

var drillState = {
    selectedMonth: null,
    drillDimensions: [],
    pendingDimension: null
};

var DRILL_DIMENSIONS = ['day', 'product', 'sub_account', 'user', 'sender_id', 'origin', 'country', 'group_name'];

function getAvailableDimensions() {
    return DRILL_DIMENSIONS.filter(function(d) {
        return drillState.drillDimensions.indexOf(d) === -1;
    });
}

function getDrillPath() {
    var path = [];
    if (drillState.selectedMonth) {
        path.push({ dimension: 'month', value: drillState.selectedMonth.value, label: drillState.selectedMonth.label });
    }
    drillState.drillDimensions.forEach(function(dim) {
        path.push({ dimension: dim, value: null, label: dimensionLabels[dim] });
    });
    return path;
}

var dimensionLabels = {
    'month': 'Billing Month',
    'day': 'Day',
    'product': 'Product',
    'sub_account': 'Sub Account',
    'user': 'User',
    'sender_id': 'Sender ID',
    'origin': 'Origin',
    'country': 'Country',
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
    product: [
        { label: 'SMS', billable: 56789, nonBillable: 1123, total: 57912, cost: '£1,809.75', status: 'Finalised' },
        { label: 'RCS Basic', billable: 23456, nonBillable: 456, total: 23912, cost: '£747.25', status: 'Finalised' },
        { label: 'RCS Rich', billable: 12345, nonBillable: 234, total: 12579, cost: '£393.09', status: 'Finalised' }
    ],
    sub_account: [
        { label: 'Main Account', billable: 45678, nonBillable: 890, total: 46568, cost: '£1,455.25', status: 'Finalised' },
        { label: 'Marketing', billable: 28934, nonBillable: 567, total: 29501, cost: '£922.01', status: 'Finalised' },
        { label: 'Operations', billable: 15678, nonBillable: 312, total: 15990, cost: '£499.69', status: 'Finalised' },
        { label: 'Sales', billable: 9876, nonBillable: 189, total: 10065, cost: '£314.53', status: 'Finalised' },
        { label: 'Support', billable: 6543, nonBillable: 123, total: 6666, cost: '£208.31', status: 'Finalised' }
    ],
    user: [
        { label: 'john.smith@company.com', billable: 34567, nonBillable: 678, total: 35245, cost: '£1,101.41', status: 'Finalised' },
        { label: 'jane.doe@company.com', billable: 28934, nonBillable: 567, total: 29501, cost: '£922.01', status: 'Finalised' },
        { label: 'admin@company.com', billable: 18765, nonBillable: 367, total: 19132, cost: '£597.88', status: 'Finalised' },
        { label: 'marketing@company.com', billable: 9876, nonBillable: 189, total: 10065, cost: '£314.53', status: 'Finalised' }
    ],
    sender_id: [
        { label: 'QuickSMS', billable: 34567, nonBillable: 678, total: 35245, cost: '£1,101.41', status: 'Finalised' },
        { label: 'PROMO', billable: 28934, nonBillable: 567, total: 29501, cost: '£922.01', status: 'Finalised' },
        { label: 'ALERTS', billable: 15678, nonBillable: 312, total: 15990, cost: '£499.69', status: 'Finalised' },
        { label: 'INFO', billable: 9876, nonBillable: 189, total: 10065, cost: '£314.53', status: 'Finalised' },
        { label: 'VERIFY', billable: 6789, nonBillable: 123, total: 6912, cost: '£216.00', status: 'Finalised' }
    ],
    origin: [
        { label: 'Portal', billable: 45678, nonBillable: 890, total: 46568, cost: '£1,455.25', status: 'Finalised' },
        { label: 'API', billable: 34567, nonBillable: 678, total: 35245, cost: '£1,101.41', status: 'Finalised' },
        { label: 'Email-to-SMS', billable: 12345, nonBillable: 234, total: 12579, cost: '£393.09', status: 'Finalised' },
        { label: 'Integration', billable: 8765, nonBillable: 167, total: 8932, cost: '£279.13', status: 'Finalised' }
    ],
    country: [
        { label: 'United Kingdom', billable: 45678, nonBillable: 890, total: 46568, cost: '£1,455.25', status: 'Finalised' },
        { label: 'United States', billable: 23456, nonBillable: 456, total: 23912, cost: '£747.25', status: 'Finalised' },
        { label: 'Germany', billable: 12345, nonBillable: 234, total: 12579, cost: '£393.09', status: 'Finalised' },
        { label: 'France', billable: 8765, nonBillable: 167, total: 8932, cost: '£279.13', status: 'Finalised' },
        { label: 'Ireland', billable: 5432, nonBillable: 98, total: 5530, cost: '£172.81', status: 'Finalised' }
    ],
    group_name: [
        { label: 'VIP Customers', billable: 34567, nonBillable: 678, total: 35245, cost: '£1,101.41', status: 'Finalised' },
        { label: 'Newsletter', billable: 28934, nonBillable: 567, total: 29501, cost: '£922.01', status: 'Finalised' },
        { label: 'Promotions', billable: 18765, nonBillable: 367, total: 19132, cost: '£597.88', status: 'Finalised' },
        { label: 'Alerts', billable: 9876, nonBillable: 189, total: 10065, cost: '£314.53', status: 'Finalised' },
        { label: 'API Integration', billable: 4321, nonBillable: 87, total: 4408, cost: '£137.75', status: 'Finalised' }
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    initDrillDownHandlers();
});

function initDrillDownHandlers() {
    document.getElementById('billingTableBody').addEventListener('click', function(e) {
        var row = e.target.closest('tr');
        if (!row) return;
        
        if (row.classList.contains('skeleton-row') || row.classList.contains('totals-row') || row.classList.contains('group-total-row')) {
            return;
        }
        
        if (!drillState.selectedMonth && !drillState.pendingDimension) {
            showDrillTooltip(e, 'Select a drill dimension first');
            return;
        }
        
        var labelCell = row.querySelector('td:first-child .drill-label, td:first-child .fw-semibold');
        if (!labelCell) return;
        
        var label = labelCell.textContent.trim();
        var value = row.getAttribute('data-value') || label;
        var status = row.getAttribute('data-status') || 'Finalised';
        
        if (!drillState.selectedMonth && drillState.pendingDimension) {
            drillState.selectedMonth = {
                label: label,
                value: value,
                status: status
            };
            drillState.drillDimensions.push(drillState.pendingDimension);
            drillState.pendingDimension = null;
            
            updateDimensionSelector();
            updateBreadcrumbs();
            renderHierarchicalTable();
        }
    });
    
    initDimensionSelector();
}

function showDrillTooltip(e, message) {
    var existing = document.querySelector('.drill-tooltip');
    if (existing) existing.remove();
    
    var tooltip = document.createElement('div');
    tooltip.className = 'drill-tooltip';
    tooltip.textContent = message;
    tooltip.style.cssText = 'position: fixed; background: #333; color: #fff; padding: 8px 12px; border-radius: 4px; font-size: 12px; z-index: 9999; pointer-events: none;';
    tooltip.style.left = e.clientX + 'px';
    tooltip.style.top = (e.clientY - 40) + 'px';
    document.body.appendChild(tooltip);
    
    setTimeout(function() {
        tooltip.remove();
    }, 2000);
}

function initDimensionSelector() {
    var container = document.getElementById('drillDimensionSelector');
    if (!container) return;
    
    container.addEventListener('click', function(e) {
        var btn = e.target.closest('.drill-dimension-btn');
        if (!btn) return;
        
        var dimension = btn.getAttribute('data-dimension');
        selectDrillDimension(dimension);
    });
}

function selectDrillDimension(dimension) {
    document.querySelectorAll('.drill-dimension-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.getAttribute('data-dimension') === dimension) {
            btn.classList.add('active');
        }
    });
    
    if (!drillState.selectedMonth) {
        drillState.pendingDimension = dimension;
        document.getElementById('drillInstruction').textContent = 
            'Click a billing month to drill down by ' + dimensionLabels[dimension];
        document.getElementById('drillInstruction').style.display = 'block';
    } else {
        drillState.drillDimensions.push(dimension);
        updateDimensionSelector();
        updateBreadcrumbs();
        renderHierarchicalTable();
    }
}

function updateDimensionSelector() {
    var container = document.getElementById('drillDimensionSelector');
    var availableDimensions = getAvailableDimensions();
    
    if (availableDimensions.length === 0) {
        container.style.display = 'none';
        document.getElementById('drillInstruction').style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    var buttonsHtml = availableDimensions.map(function(dim) {
        return '<button type="button" class="btn btn-outline-primary btn-sm drill-dimension-btn" data-dimension="' + dim + '">' +
            '<i class="' + getDimensionIcon(dim) + ' me-1"></i>' + dimensionLabels[dim] +
            '</button>';
    }).join('');
    
    document.getElementById('dimensionButtons').innerHTML = buttonsHtml;
    
    document.querySelectorAll('.drill-dimension-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    drillState.pendingDimension = null;
    
    if (drillState.selectedMonth) {
        document.getElementById('drillInstruction').textContent = 'Select a dimension to expand the breakdown';
    }
    document.getElementById('drillInstruction').style.display = 'none';
}

function getDimensionIcon(dimension) {
    var icons = {
        'day': 'fas fa-calendar-day',
        'product': 'fas fa-box',
        'sub_account': 'fas fa-building',
        'user': 'fas fa-user',
        'sender_id': 'fas fa-signature',
        'origin': 'fas fa-paper-plane',
        'country': 'fas fa-globe',
        'group_name': 'fas fa-users'
    };
    return icons[dimension] || 'fas fa-layer-group';
}

function updateBreadcrumbs() {
    var breadcrumbContainer = document.getElementById('drillBreadcrumbs');
    var breadcrumbList = document.getElementById('drillBreadcrumbList');
    
    if (!drillState.selectedMonth) {
        breadcrumbContainer.style.display = 'none';
        return;
    }
    
    breadcrumbContainer.style.display = 'block';
    
    var html = '<li class="breadcrumb-item"><a href="#" onclick="navigateToBreadcrumb(-1); return false;">Finance Data</a></li>';
    
    html += '<li class="breadcrumb-item"><a href="#" onclick="navigateToBreadcrumb(0); return false;">' + drillState.selectedMonth.label + '</a></li>';
    
    drillState.drillDimensions.forEach(function(dim, index) {
        var isLast = index === drillState.drillDimensions.length - 1;
        var label = dimensionLabels[dim];
        
        if (isLast) {
            html += '<li class="breadcrumb-item active" aria-current="page">' + label + '</li>';
        } else {
            html += '<li class="breadcrumb-item"><a href="#" onclick="navigateToBreadcrumb(' + (index + 1) + '); return false;">' + label + '</a></li>';
        }
    });
    
    breadcrumbList.innerHTML = html;
}

function navigateToBreadcrumb(index) {
    if (index < 0) {
        resetDrillDown();
    } else if (index === 0) {
        drillState.drillDimensions = [];
        updateDimensionSelector();
        updateBreadcrumbs();
        renderHierarchicalTable();
    } else {
        drillState.drillDimensions = drillState.drillDimensions.slice(0, index);
        drillState.pendingDimension = null;
        
        updateDimensionSelector();
        updateBreadcrumbs();
        renderHierarchicalTable();
    }
}

function resetDrillDown() {
    drillState = { selectedMonth: null, drillDimensions: [], pendingDimension: null };
    document.getElementById('drillBreadcrumbs').style.display = 'none';
    document.getElementById('drillInstruction').style.display = 'none';
    updateDimensionSelector();
    loadInitialData();
}

function renderHierarchicalTable() {
    if (!drillState.selectedMonth) {
        loadInitialData();
        return;
    }
    
    var tableHead = document.querySelector('#financeDataTable thead tr');
    var firstCol = tableHead.querySelector('th:first-child');
    
    var columnLabel = 'Breakdown';
    if (drillState.drillDimensions.length > 0) {
        columnLabel = dimensionLabels[drillState.drillDimensions[drillState.drillDimensions.length - 1]];
    }
    firstCol.innerHTML = columnLabel + ' <i class="fas fa-sort ms-1 text-muted"></i>';
    
    var tbody = document.getElementById('billingTableBody');
    var html = '';
    var rowCount = 0;
    
    var monthData = getMonthTotals(drillState.selectedMonth);
    var monthAttrs = getRowAttributes(monthData.status);
    
    html += '<tr class="month-total-row fw-bold">';
    html += '<td><i class="fas fa-calendar-alt me-2"></i><span class="fw-bold">' + drillState.selectedMonth.label + ' (Totals)</span></td>';
    html += '<td class="text-end fw-bold">' + formatNumber(monthData.billable) + '</td>';
    html += '<td class="text-end fw-bold">' + formatNumber(monthData.nonBillable) + '</td>';
    html += '<td class="text-end fw-bold">' + formatNumber(monthData.total) + '</td>';
    html += '<td class="text-end fw-bold">' + formatCurrency(monthData.cost) + '</td>';
    html += '<td class="text-center">' + monthAttrs.statusBadge + '</td>';
    html += '</tr>';
    rowCount++;
    
    if (drillState.drillDimensions.length === 0) {
        tbody.innerHTML = html;
        var rowCountEl = document.getElementById('rowCount');
        var totalCountEl = document.getElementById('totalCount');
        if (rowCountEl) rowCountEl.textContent = rowCount;
        if (totalCountEl) totalCountEl.textContent = rowCount;
        return;
    }
    
    var firstDimension = drillState.drillDimensions[0];
    var firstLevelData = generateDimensionData(firstDimension, drillState.selectedMonth, 0);
    
    if (drillState.drillDimensions.length === 1) {
        firstLevelData.forEach(function(item) {
            var rowAttrs = getRowAttributes(item.status);
            html += '<tr class="' + rowAttrs.classes + '" data-value="' + item.value + '" data-status="' + item.status.toLowerCase() + '"' + rowAttrs.attrs + '>';
            html += '<td style="padding-left: 24px;"><span class="drill-label fw-semibold">' + item.label + '</span>' + rowAttrs.labelIcon + '</td>';
            html += '<td class="text-end">' + formatNumber(item.billable) + '</td>';
            html += '<td class="text-end">' + formatNumber(item.nonBillable) + '</td>';
            html += '<td class="text-end fw-semibold">' + formatNumber(item.total) + '</td>';
            html += '<td class="text-end fw-semibold">' + formatCurrency(item.cost) + '</td>';
            html += '<td class="text-center">' + rowAttrs.statusBadge + '</td>';
            html += '</tr>';
            rowCount++;
        });
    } else {
        firstLevelData.forEach(function(parentItem) {
            var parentAttrs = getRowAttributes(parentItem.status);
            html += '<tr class="group-total-row fw-semibold">';
            html += '<td style="padding-left: 24px;"><i class="fas fa-caret-down me-2 text-muted"></i><span class="fw-semibold">' + parentItem.label + ' (Total)</span></td>';
            html += '<td class="text-end fw-semibold">' + formatNumber(parentItem.billable) + '</td>';
            html += '<td class="text-end fw-semibold">' + formatNumber(parentItem.nonBillable) + '</td>';
            html += '<td class="text-end fw-semibold">' + formatNumber(parentItem.total) + '</td>';
            html += '<td class="text-end fw-semibold">' + formatCurrency(parentItem.cost) + '</td>';
            html += '<td class="text-center">' + parentAttrs.statusBadge + '</td>';
            html += '</tr>';
            rowCount++;
            
            var secondDimension = drillState.drillDimensions[1];
            var secondLevelData = generateDimensionData(secondDimension, parentItem, 1);
            
            if (drillState.drillDimensions.length === 2) {
                secondLevelData.forEach(function(childItem) {
                    var childAttrs = getRowAttributes(childItem.status);
                    html += '<tr class="' + childAttrs.classes + '" data-value="' + childItem.value + '" data-status="' + childItem.status.toLowerCase() + '"' + childAttrs.attrs + '>';
                    html += '<td style="padding-left: 48px;"><span class="drill-label">' + childItem.label + '</span>' + childAttrs.labelIcon + '</td>';
                    html += '<td class="text-end">' + formatNumber(childItem.billable) + '</td>';
                    html += '<td class="text-end">' + formatNumber(childItem.nonBillable) + '</td>';
                    html += '<td class="text-end">' + formatNumber(childItem.total) + '</td>';
                    html += '<td class="text-end">' + formatCurrency(childItem.cost) + '</td>';
                    html += '<td class="text-center">' + childAttrs.statusBadge + '</td>';
                    html += '</tr>';
                    rowCount++;
                });
            } else {
                secondLevelData.forEach(function(level2Item) {
                    var level2Attrs = getRowAttributes(level2Item.status);
                    html += '<tr class="group-total-row">';
                    html += '<td style="padding-left: 48px;"><i class="fas fa-caret-down me-2 text-muted small"></i><span class="fw-medium">' + level2Item.label + ' (Total)</span></td>';
                    html += '<td class="text-end">' + formatNumber(level2Item.billable) + '</td>';
                    html += '<td class="text-end">' + formatNumber(level2Item.nonBillable) + '</td>';
                    html += '<td class="text-end">' + formatNumber(level2Item.total) + '</td>';
                    html += '<td class="text-end">' + formatCurrency(level2Item.cost) + '</td>';
                    html += '<td class="text-center">' + level2Attrs.statusBadge + '</td>';
                    html += '</tr>';
                    rowCount++;
                    
                    var thirdDimension = drillState.drillDimensions[2];
                    var thirdLevelData = generateDimensionData(thirdDimension, level2Item, 2);
                    
                    thirdLevelData.forEach(function(level3Item) {
                        var level3Attrs = getRowAttributes(level3Item.status);
                        html += '<tr class="' + level3Attrs.classes + '" data-value="' + level3Item.value + '" data-status="' + level3Item.status.toLowerCase() + '"' + level3Attrs.attrs + '>';
                        html += '<td style="padding-left: 72px;"><span class="drill-label">' + level3Item.label + '</span>' + level3Attrs.labelIcon + '</td>';
                        html += '<td class="text-end">' + formatNumber(level3Item.billable) + '</td>';
                        html += '<td class="text-end">' + formatNumber(level3Item.nonBillable) + '</td>';
                        html += '<td class="text-end">' + formatNumber(level3Item.total) + '</td>';
                        html += '<td class="text-end">' + formatCurrency(level3Item.cost) + '</td>';
                        html += '<td class="text-center">' + level3Attrs.statusBadge + '</td>';
                        html += '</tr>';
                        rowCount++;
                    });
                });
            }
        });
    }
    
    tbody.innerHTML = html;
    var rowCountEl = document.getElementById('rowCount');
    var totalCountEl = document.getElementById('totalCount');
    if (rowCountEl) rowCountEl.textContent = rowCount;
    if (totalCountEl) totalCountEl.textContent = rowCount;
}

function getMonthTotals(month) {
    var monthlyData = {
        'December 2025': { billable: 130730, nonBillable: 2321, total: 133051, cost: 4183.36, status: 'Finalised' },
        'November 2025': { billable: 93157, nonBillable: 1559, total: 94716, cost: 2981.02, status: 'Finalised' },
        'October 2025': { billable: 100439, nonBillable: 5829, total: 106268, cost: 3214.05, status: 'Finalised' },
        'September 2025': { billable: 90711, nonBillable: 1405, total: 92116, cost: 2902.75, status: 'Adjusted' },
        'August 2025': { billable: 135278, nonBillable: 5783, total: 141061, cost: 4328.90, status: 'Provisional' },
        'July 2025': { billable: 121490, nonBillable: 2622, total: 124112, cost: 3887.68, status: 'Provisional' },
        'June 2025': { billable: 116325, nonBillable: 3201, total: 119526, cost: 3722.40, status: 'Finalised' },
        'May 2025': { billable: 98234, nonBillable: 2145, total: 100379, cost: 3143.49, status: 'Finalised' },
        'April 2025': { billable: 87456, nonBillable: 1876, total: 89332, cost: 2798.59, status: 'Finalised' },
        'March 2025': { billable: 102345, nonBillable: 2567, total: 104912, cost: 3275.04, status: 'Finalised' },
        'February 2025': { billable: 78912, nonBillable: 1456, total: 80368, cost: 2525.18, status: 'Finalised' },
        'January 2025': { billable: 91345, nonBillable: 2234, total: 93579, cost: 2923.04, status: 'Finalised' }
    };
    return monthlyData[month.label] || { billable: 100000, nonBillable: 2000, total: 102000, cost: 3200.00, status: month.status || 'Finalised' };
}

function generateDimensionData(dimension, parentContext, level) {
    var seed = hashCode(JSON.stringify(drillState.selectedMonth) + dimension + JSON.stringify(parentContext) + level);
    var data = [];
    
    if (dimension === 'day') {
        var monthMatch = drillState.selectedMonth.label.match(/(\w+)\s+(\d{4})/);
        var monthName = monthMatch ? monthMatch[1] : 'January';
        var year = monthMatch ? monthMatch[2] : '2025';
        var monthNum = getMonthNumber(monthName);
        var daysInMonth = new Date(parseInt(year), parseInt(monthNum), 0).getDate();
        
        for (var d = 1; d <= daysInMonth; d++) {
            var dayStr = d < 10 ? '0' + d : '' + d;
            var billable = Math.floor(seededRandom(seed + d) * 4000) + 2000;
            var nonBillable = Math.floor(seededRandom(seed + d + 100) * 150) + 30;
            
            data.push({
                label: dayStr + '/' + monthNum + '/' + year,
                value: year + '-' + monthNum + '-' + dayStr,
                billable: billable,
                nonBillable: nonBillable,
                total: billable + nonBillable,
                cost: billable * 0.032,
                status: drillState.selectedMonth.status || 'Finalised'
            });
        }
    } else {
        var dimensionItems = mockDrillData[dimension] || [];
        dimensionItems.forEach(function(item, idx) {
            var billable = Math.floor(seededRandom(seed + idx) * 3000) + 500;
            var nonBillable = Math.floor(seededRandom(seed + idx + 50) * 100) + 10;
            
            data.push({
                label: item.label,
                value: item.label,
                billable: billable,
                nonBillable: nonBillable,
                total: billable + nonBillable,
                cost: billable * 0.032,
                status: drillState.selectedMonth.status || 'Finalised'
            });
        });
    }
    
    return data;
}

function hashCode(str) {
    var hash = 0;
    for (var i = 0; i < str.length; i++) {
        var char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    return Math.abs(hash);
}

function seededRandom(seed) {
    var x = Math.sin(seed) * 10000;
    return x - Math.floor(x);
}

function getMonthNumber(monthName) {
    var months = {
        'January': '01', 'February': '02', 'March': '03', 'April': '04',
        'May': '05', 'June': '06', 'July': '07', 'August': '08',
        'September': '09', 'October': '10', 'November': '11', 'December': '12'
    };
    return months[monthName] || '01';
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
        var rowAttrs = getRowAttributes(row.status);
        
        html += '<tr class="' + rowAttrs.classes + '" data-status="' + row.status.toLowerCase() + '"' + rowAttrs.attrs + '>';
        html += '<td><span class="fw-semibold">' + row.label + '</span>' + rowAttrs.labelIcon + '</td>';
        html += '<td class="text-end">' + row.billable.toLocaleString() + '</td>';
        html += '<td class="text-end">' + row.nonBillable.toLocaleString() + '</td>';
        html += '<td class="text-end fw-semibold">' + row.total.toLocaleString() + '</td>';
        html += '<td class="text-end fw-semibold">' + row.cost + '</td>';
        html += '<td class="text-center">' + rowAttrs.statusBadge + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    var rowCountEl = document.getElementById('rowCount');
    var totalCountEl = document.getElementById('totalCount');
    if (rowCountEl) rowCountEl.textContent = monthlyData.length;
    if (totalCountEl) totalCountEl.textContent = monthlyData.length;
}

function getRowAttributes(status) {
    var result = {
        classes: 'drill-row',
        attrs: '',
        labelIcon: '',
        statusBadge: '<span class="badge light badge-success">Finalised</span>'
    };
    
    if (status === 'Finalised') {
        result.classes = 'drill-row';
        result.attrs = '';
        result.labelIcon = '';
        result.statusBadge = '<span class="badge light badge-success">Finalised</span>';
    } else if (status === 'Adjusted') {
        result.classes = 'drill-row';
        result.attrs = '';
        result.labelIcon = '';
        result.statusBadge = '<span class="badge light badge-warning">Adjusted</span>';
    } else if (status === 'Provisional') {
        result.classes = 'drill-row';
        result.attrs = '';
        result.labelIcon = '';
        result.statusBadge = '<span class="badge light badge-info">Provisional</span>';
    }
    
    return result;
}

function getDrillStateForExport() {
    return {
        selectedMonth: drillState.selectedMonth,
        drillDimensions: drillState.drillDimensions.slice(),
        path: getDrillPath(),
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

var LARGE_DATASET_THRESHOLD = 10000;

document.getElementById('btnExportCsv')?.addEventListener('click', function() {
    triggerExport('csv');
});

document.getElementById('btnExportExcel')?.addEventListener('click', function() {
    triggerExport('xlsx');
});

function triggerExport(format) {
    var exportState = getFullExportState();
    var totalCountEl = document.getElementById('totalCount');
    var rowCount = totalCountEl ? parseInt(totalCountEl.textContent) || 0 : 0;
    
    console.log('[Finance Data] Export ' + format.toUpperCase() + ' with state:', exportState);
    
    if (rowCount > LARGE_DATASET_THRESHOLD) {
        triggerAsyncExport(format, exportState, rowCount);
    } else {
        triggerSyncExport(format, exportState, rowCount);
    }
}

function getFullExportState() {
    var columns = [];
    document.querySelectorAll('#financeDataTable thead th').forEach(function(th) {
        columns.push(th.textContent.trim().replace(/\s+/g, ' '));
    });
    
    return {
        format: null,
        columns: columns,
        filters: {
            billingMonths: getSelectedValues('billingMonths'),
            subAccounts: getSelectedValues('subAccounts'),
            users: getSelectedValues('users'),
            groupNames: getSelectedValues('groupNames'),
            productTypes: getSelectedValues('productTypes'),
            senderIds: selectedSenderIds,
            messageTypes: getSelectedValues('messageTypes')
        },
        drillState: {
            selectedMonth: drillState.selectedMonth,
            drillDimensions: drillState.drillDimensions.slice(),
            path: getDrillPath()
        },
        costFormat: 'ex_vat'
    };
}

function triggerSyncExport(format, exportState, rowCount) {
    exportState.format = format;
    
    var modal = new bootstrap.Modal(document.getElementById('exportProgressModal'));
    document.getElementById('exportInProgress').style.display = 'block';
    document.getElementById('exportComplete').style.display = 'none';
    document.getElementById('exportQueued').style.display = 'none';
    document.getElementById('btnCloseExportModal').style.display = 'none';
    document.getElementById('btnGoToDownloads').style.display = 'none';
    document.getElementById('exportRowsCount').textContent = rowCount;
    
    modal.show();
    
    // TODO: Replace with actual backend export endpoint
    // exportToBackend(exportState).then(function(response) { ... });
    
    setTimeout(function() {
        document.getElementById('exportInProgress').style.display = 'none';
        document.getElementById('exportComplete').style.display = 'block';
        document.getElementById('btnCloseExportModal').style.display = 'inline-block';
        
        var filename = 'finance_data_' + new Date().toISOString().split('T')[0] + '.' + format;
        document.getElementById('exportCompleteText').textContent = 'File: ' + filename;
        
        console.log('[Finance Data] Sync export complete:', { format: format, filename: filename, state: exportState });
        
        // TODO: Trigger actual file download
        // downloadFile(response.downloadUrl);
    }, 1500);
}

function triggerAsyncExport(format, exportState, rowCount) {
    exportState.format = format;
    
    var modal = new bootstrap.Modal(document.getElementById('exportProgressModal'));
    document.getElementById('exportInProgress').style.display = 'block';
    document.getElementById('exportComplete').style.display = 'none';
    document.getElementById('exportQueued').style.display = 'none';
    document.getElementById('btnCloseExportModal').style.display = 'none';
    document.getElementById('btnGoToDownloads').style.display = 'none';
    document.getElementById('exportRowsCount').textContent = rowCount.toLocaleString();
    
    modal.show();
    
    // TODO: Replace with backend queue/job endpoint
    // queueExportJob(exportState).then(function(response) { ... });
    
    setTimeout(function() {
        document.getElementById('exportInProgress').style.display = 'none';
        document.getElementById('exportQueued').style.display = 'block';
        document.getElementById('btnGoToDownloads').style.display = 'inline-block';
        
        console.log('[Finance Data] Async export queued:', { format: format, rowCount: rowCount, state: exportState });
        
        showToast('Export queued! You will be notified when it is ready in the Download Centre.');
    }, 1000);
}

function exportToBackend(exportState) {
    // TODO: Connect to backend export endpoint
    // POST /api/reporting/finance-data/export
    // Body: exportState
    // Returns: { downloadUrl: string, filename: string }
    console.log('[Finance Data] TODO: POST to /api/reporting/finance-data/export', exportState);
    return Promise.resolve({ downloadUrl: '#', filename: 'export.csv' });
}

function queueExportJob(exportState) {
    // TODO: Connect to backend queue/job system for large exports
    // POST /api/reporting/finance-data/export-async
    // Body: exportState
    // Returns: { jobId: string, estimatedTime: number }
    // Job delivers to Download Centre when complete
    console.log('[Finance Data] TODO: POST to /api/reporting/finance-data/export-async', exportState);
    return Promise.resolve({ jobId: 'job_' + Date.now(), estimatedTime: 60 });
}

var savedReports = JSON.parse(localStorage.getItem('financeDataSavedReports') || '[]');
var scheduledReports = JSON.parse(localStorage.getItem('financeDataScheduledReports') || '[]');

document.getElementById('btnSaveReport')?.addEventListener('click', function() {
    updateSaveReportPreview();
    new bootstrap.Modal(document.getElementById('saveReportModal')).show();
});

document.getElementById('btnScheduleReport')?.addEventListener('click', function() {
    new bootstrap.Modal(document.getElementById('scheduleReportModal')).show();
});

function updateSaveReportPreview() {
    var filters = [];
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        var checked = Array.from(dropdown.querySelectorAll('.form-check-input:checked'));
        if (checked.length > 0) {
            var filterName = dropdown.getAttribute('data-filter');
            filters.push(filterName + ': ' + checked.length + ' selected');
        }
    });
    if (selectedSenderIds.length > 0) {
        filters.push('Sender IDs: ' + selectedSenderIds.length + ' selected');
    }
    
    document.getElementById('previewFilters').textContent = filters.length > 0 ? filters.join(', ') : 'None selected';
    
    var drillLevel = 'Billing Month';
    if (drillState.selectedMonth) {
        drillLevel = drillState.selectedMonth.label;
        if (drillState.drillDimensions.length > 0) {
            drillLevel += ' → ' + drillState.drillDimensions.map(function(d) {
                return dimensionLabels[d];
            }).join(' → ');
        }
    }
    document.getElementById('previewDrillLevel').textContent = drillLevel;
    document.getElementById('previewSort').textContent = 'Default';
}

document.getElementById('btnConfirmSaveReport')?.addEventListener('click', function() {
    var reportName = document.getElementById('reportName').value.trim();
    if (!reportName) {
        alert('Please enter a report name');
        return;
    }
    
    var reportConfig = {
        id: 'report_' + Date.now(),
        name: reportName,
        description: document.getElementById('reportDescription').value.trim(),
        createdAt: new Date().toISOString(),
        filters: {
            billingMonths: getSelectedValues('billingMonths'),
            subAccounts: getSelectedValues('subAccounts'),
            users: getSelectedValues('users'),
            groupNames: getSelectedValues('groupNames'),
            productTypes: getSelectedValues('productTypes'),
            senderIds: selectedSenderIds,
            messageTypes: getSelectedValues('messageTypes')
        },
        drillState: {
            selectedMonth: drillState.selectedMonth,
            drillDimensions: drillState.drillDimensions.slice(),
            path: getDrillPath()
        },
        sortState: null
    };
    
    savedReports.push(reportConfig);
    localStorage.setItem('financeDataSavedReports', JSON.stringify(savedReports));
    
    console.log('[Finance Data] Saved report configuration:', reportConfig);
    
    bootstrap.Modal.getInstance(document.getElementById('saveReportModal')).hide();
    document.getElementById('reportName').value = '';
    document.getElementById('reportDescription').value = '';
    
    showToast('Report configuration saved successfully!');
    
    // TODO: connect to backend saved reports endpoint
    // saveReportToBackend(reportConfig);
});

function saveReportToBackend(reportConfig) {
    // TODO: connect to backend saved reports endpoint
    // return fetch('/api/reporting/saved-reports', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(reportConfig)
    // });
    console.log('[Finance Data] TODO: POST to /api/reporting/saved-reports', reportConfig);
}

document.querySelectorAll('input[name="scheduleFrequency"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('monthlyOptions').style.display = 'none';
        document.getElementById('weeklyOptions').style.display = 'none';
        document.getElementById('customOptions').style.display = 'none';
        
        if (this.value === 'monthly') {
            document.getElementById('monthlyOptions').style.display = 'block';
        } else if (this.value === 'weekly') {
            document.getElementById('weeklyOptions').style.display = 'block';
        } else if (this.value === 'custom') {
            document.getElementById('customOptions').style.display = 'block';
        }
    });
});

document.getElementById('btnConfirmSchedule')?.addEventListener('click', function() {
    var scheduleName = document.getElementById('scheduleName').value.trim();
    if (!scheduleName) {
        alert('Please enter a schedule name');
        return;
    }
    
    var frequency = document.querySelector('input[name="scheduleFrequency"]:checked').value;
    var scheduleDetails = {};
    
    if (frequency === 'monthly') {
        scheduleDetails.dayOfMonth = document.getElementById('monthlyDay').value;
    } else if (frequency === 'weekly') {
        scheduleDetails.dayOfWeek = document.getElementById('weeklyDay').value;
    } else if (frequency === 'custom') {
        scheduleDetails.cronExpression = document.getElementById('customCron').value;
    }
    
    var scheduleConfig = {
        id: 'schedule_' + Date.now(),
        name: scheduleName,
        createdAt: new Date().toISOString(),
        frequency: frequency,
        scheduleDetails: scheduleDetails,
        format: document.getElementById('scheduleFormat').value,
        recipients: document.getElementById('scheduleRecipients').value.split(',').map(function(e) { return e.trim(); }).filter(Boolean),
        filters: {
            billingMonths: getSelectedValues('billingMonths'),
            subAccounts: getSelectedValues('subAccounts'),
            users: getSelectedValues('users'),
            groupNames: getSelectedValues('groupNames'),
            productTypes: getSelectedValues('productTypes'),
            senderIds: selectedSenderIds,
            messageTypes: getSelectedValues('messageTypes')
        },
        drillState: {
            selectedMonth: drillState.selectedMonth,
            drillDimensions: drillState.drillDimensions.slice(),
            path: getDrillPath()
        },
        status: 'active'
    };
    
    scheduledReports.push(scheduleConfig);
    localStorage.setItem('financeDataScheduledReports', JSON.stringify(scheduledReports));
    
    console.log('[Finance Data] Created schedule:', scheduleConfig);
    
    bootstrap.Modal.getInstance(document.getElementById('scheduleReportModal')).hide();
    document.getElementById('scheduleName').value = '';
    document.getElementById('scheduleRecipients').value = '';
    
    showToast('Report schedule created successfully! It will appear in Download Centre.');
    
    // TODO: wire to Download Centre / automated export via backend later
    // createScheduleOnBackend(scheduleConfig);
});

function createScheduleOnBackend(scheduleConfig) {
    // TODO: wire to Download Centre / automated export via backend later
    // return fetch('/api/reporting/scheduled-exports', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(scheduleConfig)
    // });
    console.log('[Finance Data] TODO: POST to /api/reporting/scheduled-exports', scheduleConfig);
}

function showToast(message) {
    var toastEl = document.getElementById('successToast');
    document.getElementById('toastMessage').textContent = message;
    var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
}
</script>
@endpush

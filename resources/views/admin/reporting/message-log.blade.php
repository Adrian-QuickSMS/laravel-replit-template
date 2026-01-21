@extends('layouts.admin')

@section('title', 'Message Log (Global)')

@push('styles')
<style>
.message-log-container {
    min-height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
}
.message-log-container .card {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-bottom: 0 !important;
}
.message-log-container > .row > .col-12 > .card > .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding-bottom: 0;
}
#summaryBar .card-body {
    display: block !important;
    flex: none !important;
    overflow: visible !important;
    padding: 1.25rem !important;
}
.message-log-fixed-header {
    flex-shrink: 0;
    overflow: visible;
}
#filtersPanel {
    overflow: visible !important;
}
#filtersPanel .card-body {
    overflow: visible !important;
}
#filtersPanel .dropdown-menu {
    z-index: 1050;
}
#summaryBar .card {
    flex: none !important;
    overflow: visible !important;
}
.message-log-table-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}
#tableContainer {
    flex: 1;
    overflow-x: auto;
    min-height: 0;
}
.message-log-footer {
    flex-shrink: 0;
    margin-top: auto;
}
#messageLogTable tbody tr {
    cursor: pointer;
}
#messageLogTable tbody tr:hover {
    background-color: rgba(30, 58, 95, 0.05);
}

#messageLogTable {
    font-size: 0.75rem;
    line-height: 1.3;
    table-layout: fixed;
    width: 100%;
}
#messageLogTable th,
#messageLogTable td {
    padding: 0.35rem 0.4rem;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#messageLogTable thead th {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#messageLogTable th[data-column="account"] { width: 9%; }
#messageLogTable th[data-column="mobileNumber"] { width: 11%; }
#messageLogTable th[data-column="ukNetworkPrefix"] { width: 7%; }
#messageLogTable th[data-column="senderId"] { width: 10%; }
#messageLogTable th[data-column="status"] { width: 7%; }
#messageLogTable th[data-column="sentTime"] { width: 11%; }
#messageLogTable th[data-column="sentToSupplier"] { width: 11%; }
#messageLogTable th[data-column="deliveryTime"] { width: 11%; }
#messageLogTable th[data-column="completeTime"] { width: 11%; }
#messageLogTable th[data-column="margin"] { width: 6%; }
#messageLogTable th[data-column="actions"] { width: 3%; min-width: 30px; }
#messageLogTable .dropdown-toggle {
    font-size: 0.7rem;
}
#messageLogTable .dropdown-toggle i.fa-sort {
    font-size: 0.6rem;
}
#messageLogTable .badge {
    font-size: 0.65rem;
    padding: 0.2em 0.45em;
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
    background-color: rgba(30, 58, 95, 0.1);
}
.bg-info-light {
    background-color: rgba(74, 144, 217, 0.1);
}
.bg-success-light {
    background-color: rgba(28, 187, 140, 0.1);
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
    background-color: rgba(30, 58, 95, 0.05);
}
.content-masked {
    letter-spacing: 2px;
    font-family: monospace;
}
.action-dots {
    color: inherit;
    opacity: 0.7;
}
.action-dots:hover {
    opacity: 1;
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
    border-color: var(--admin-primary, #1e3a5f);
}
.date-preset-btn.active {
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    border-color: var(--admin-primary, #1e3a5f);
}
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
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
.table-style-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.table-style-toggle .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
    cursor: pointer;
}
.table-style-toggle .toggle-label {
    font-size: 0.75rem;
    color: #6c757d;
    white-space: nowrap;
}
.table-style-toggle .toggle-label.active {
    color: var(--admin-primary, #1e3a5f);
    font-weight: 500;
}
#messageLogTable.clean-style tbody tr {
    background-color: transparent !important;
}
#messageLogTable.clean-style tbody tr:hover {
    background-color: rgba(30, 58, 95, 0.05) !important;
}
#messageLogTable.clean-style tbody tr.table-success,
#messageLogTable.clean-style tbody tr.table-primary,
#messageLogTable.clean-style tbody tr.table-danger {
    background-color: transparent !important;
}
.admin-filter-panel {
    background: linear-gradient(135deg, rgba(30, 58, 95, 0.05) 0%, rgba(74, 144, 217, 0.08) 100%);
    border-radius: 0.5rem;
    padding: 1rem;
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
        <span>Message Log</span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-1" style="color: var(--admin-primary, #1e3a5f); font-weight: 600;">Global Message Log</h4>
            <p class="text-muted mb-0 small">All message traffic across the platform</p>
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
                        <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search mobile, SenderID, account...">
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border-color: #dee2e6; color: #495057;">
                    <i class="fas fa-filter me-1" style="color: var(--admin-primary, #1e3a5f);"></i> Filters
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid message-log-container p-0">
        <div class="row flex-grow-1" style="min-height: 0;">
            <div class="col-12 d-flex flex-column" style="min-height: 0;">
                <div class="card">
                    <div class="card-body">
                        <div class="message-log-fixed-header">
                            <div class="collapse mb-3" id="filtersPanel">
                            <div class="card card-body border-0 rounded-3 admin-filter-panel">
                                <div class="row g-3 align-items-start">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label small fw-bold">Date Range</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="datetime-local" class="form-control form-control-sm" id="filterDateFrom" step="1">
                                            <span class="text-muted small">to</span>
                                            <input type="datetime-local" class="form-control form-control-sm" id="filterDateTo" step="1">
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="today">Today</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="yesterday">Yesterday</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="7days">Last 7 Days</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="30days">Last 30 Days</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="thismonth">This Month</button>
                                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="lastmonth">Last Month</button>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Account</label>
                                        <div class="dropdown searchable-dropdown" data-filter="accounts">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Accounts</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="min-width: 220px;">
                                                <input type="text" class="form-control form-control-sm mb-2 sender-search-input" placeholder="Type to search...">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="sender-options" style="max-height: 180px; overflow-y: auto;">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="ACC-001" id="accAcme"><label class="form-check-label small" for="accAcme">Acme Corporation</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="ACC-002" id="accFinance"><label class="form-check-label small" for="accFinance">Finance Ltd</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="ACC-003" id="accTech"><label class="form-check-label small" for="accTech">Tech Solutions</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="ACC-004" id="accRetail"><label class="form-check-label small" for="accRetail">Retail Group</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="ACC-005" id="accHealthcare"><label class="form-check-label small" for="accHealthcare">Healthcare UK</label></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Sub Account</label>
                                        <div class="dropdown searchable-dropdown" data-filter="subAccounts">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Sub Accounts</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="min-width: 220px;">
                                                <input type="text" class="form-control form-control-sm mb-2 sender-search-input" placeholder="Type to search...">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="sender-options" style="max-height: 180px; overflow-y: auto;">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Main Account" id="subAcc1"><label class="form-check-label small" for="subAcc1">Main Account</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Marketing Team" id="subAcc2"><label class="form-check-label small" for="subAcc2">Marketing Team</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Support Team" id="subAcc3"><label class="form-check-label small" for="subAcc3">Support Team</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Sales Team" id="subAcc4"><label class="form-check-label small" for="subAcc4">Sales Team</label></div>
                                                </div>
                                            </div>
                                        </div>
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
                                </div>
                                
                                <div class="row g-3 align-items-end mt-2">
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Mobile Number</label>
                                        <input type="text" class="form-control form-control-sm" id="filterMobileNumber" placeholder="Enter number...">
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">SenderID</label>
                                        <div class="dropdown searchable-dropdown" data-filter="senderIds">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All SenderIDs</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="min-width: 220px;">
                                                <input type="text" class="form-control form-control-sm mb-2 sender-search-input" placeholder="Type to search..." id="senderIdSearchInput">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="sender-options" style="max-height: 180px; overflow-y: auto;">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="QuickSMS" id="senderId1"><label class="form-check-label small" for="senderId1">QuickSMS</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="ALERTS" id="senderId3"><label class="form-check-label small" for="senderId3">ALERTS</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="PROMO" id="senderId4"><label class="form-check-label small" for="senderId4">PROMO</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="INFO" id="senderId5"><label class="form-check-label small" for="senderId5">INFO</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="NOTIFY" id="senderId6"><label class="form-check-label small" for="senderId6">NOTIFY</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="VERIFY" id="senderId7"><label class="form-check-label small" for="senderId7">VERIFY</label></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Message Status</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="statuses">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Statuses</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Delivered" id="status1"><label class="form-check-label small" for="status1">Delivered</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Pending" id="status2"><label class="form-check-label small" for="status2">Pending</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Expired" id="status3"><label class="form-check-label small" for="status3">Expired</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Rejected" id="status4"><label class="form-check-label small" for="status4">Rejected</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Undeliverable" id="status5"><label class="form-check-label small" for="status5">Undeliverable</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Country</label>
                                        <div class="dropdown searchable-dropdown" data-filter="countries">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Countries</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="min-width: 220px;">
                                                <input type="text" class="form-control form-control-sm mb-2 sender-search-input" placeholder="Type to search...">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="sender-options" style="max-height: 180px; overflow-y: auto;">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="UK" id="countryUK"><label class="form-check-label small" for="countryUK">United Kingdom</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="US" id="countryUS"><label class="form-check-label small" for="countryUS">United States</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="DE" id="countryDE"><label class="form-check-label small" for="countryDE">Germany</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="FR" id="countryFR"><label class="form-check-label small" for="countryFR">France</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="ES" id="countryES"><label class="form-check-label small" for="countryES">Spain</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="IE" id="countryIE"><label class="form-check-label small" for="countryIE">Ireland</label></div>
                                                </div>
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
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="SMS" id="typeSMS"><label class="form-check-label small" for="typeSMS">SMS</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="RCS Basic" id="typeRCSBasic"><label class="form-check-label small" for="typeRCSBasic">RCS Basic</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="RCS Rich" id="typeRCSRich"><label class="form-check-label small" for="typeRCSRich">RCS Rich</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Message ID</label>
                                        <input type="text" class="form-control form-control-sm" id="filterMessageId" placeholder="Enter ID...">
                                    </div>
                                </div>
                                
                                <div class="row g-3 align-items-end mt-2">
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">UK Network Prefix</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="ukNetworkPrefixes">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Networks</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="EE" id="networkEE"><label class="form-check-label small" for="networkEE">EE</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Vodafone" id="networkVodafone"><label class="form-check-label small" for="networkVodafone">Vodafone</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="O2" id="networkO2"><label class="form-check-label small" for="networkO2">O2</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Three" id="networkThree"><label class="form-check-label small" for="networkThree">Three</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="MVNO/Other" id="networkMVNO"><label class="form-check-label small" for="networkMVNO">MVNO/Other</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Ported To</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="portedTo">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Networks</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="EE" id="portedEE"><label class="form-check-label small" for="portedEE">EE</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Vodafone" id="portedVodafone"><label class="form-check-label small" for="portedVodafone">Vodafone</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="O2" id="portedO2"><label class="form-check-label small" for="portedO2">O2</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Three" id="portedThree"><label class="form-check-label small" for="portedThree">Three</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="MVNO/Other" id="portedMVNO"><label class="form-check-label small" for="portedMVNO">MVNO/Other</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="NOT_PORTED" id="portedNone"><label class="form-check-label small" for="portedNone">Not Ported</label></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm" id="btnApplyFilters" style="background: var(--admin-primary, #1e3a5f); color: #fff;">
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

                            <div class="mb-4" id="summaryBar" style="display: none;">
                            <div class="row g-3">
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="bg-primary-light rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                                        <i class="fas fa-envelope fs-5" style="color: var(--admin-primary, #1e3a5f);"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-muted">Total Messages</p>
                                                    <h3 class="mb-0 fw-bold" id="summaryTotal">0</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="bg-success-light rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                                        <i class="fas fa-puzzle-piece text-success fs-5"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-muted">Total Parts/Fragments</p>
                                                    <h3 class="mb-0 fw-bold" id="summaryParts">0</h3>
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
                                <div class="d-flex align-items-center gap-3">
                                    <div class="table-style-toggle">
                                        <span class="toggle-label" id="labelColoredRows">Coloured Rows</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="tableStyleToggle">
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#columnSettingsModal">
                                        <i class="fas fa-cog me-1"></i> Column Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="message-log-table-wrapper">
                            <div class="table-responsive" id="tableContainer">
                            <table class="table table-hover mb-0" id="messageLogTable">
                                <thead class="sticky-top bg-white" style="z-index: 10;">
                                    <tr id="tableHeaderRow">
                                        <th data-column="account">Account</th>
                                        <th data-column="mobileNumber">Mobile Number</th>
                                        <th data-column="ukNetworkPrefix">UK Network Prefix</th>
                                        <th data-column="senderId">
                                            <div class="dropdown d-inline-block">
                                                <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                    SenderID / Agent Name <i class="fas fa-sort ms-1 text-muted" id="sortIconSenderId"></i>
                                                </span>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="senderId" data-direction="asc"><i class="fas fa-sort-alpha-down me-2"></i> A to Z</a></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="senderId" data-direction="desc"><i class="fas fa-sort-alpha-up me-2"></i> Z to A</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="senderId" data-direction=""><i class="fas fa-times me-2 text-muted"></i> Clear Sort</a></li>
                                                </ul>
                                            </div>
                                        </th>
                                        <th data-column="status">Status</th>
                                        <th data-column="sentTime">
                                            <div class="dropdown d-inline-block">
                                                <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                    Sent Time <i class="fas fa-sort ms-1 text-muted" id="sortIconSentTime"></i>
                                                </span>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="sentTime" data-direction="desc"><i class="fas fa-arrow-down me-2"></i> Newest First</a></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="sentTime" data-direction="asc"><i class="fas fa-arrow-up me-2"></i> Oldest First</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="sentTime" data-direction=""><i class="fas fa-times me-2 text-muted"></i> Clear Sort</a></li>
                                                </ul>
                                            </div>
                                        </th>
                                        <th data-column="sentToSupplier">
                                            <div class="dropdown d-inline-block">
                                                <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                    Sent to Supplier <i class="fas fa-sort ms-1 text-muted" id="sortIconSentToSupplier"></i>
                                                </span>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="sentToSupplier" data-direction="desc"><i class="fas fa-arrow-down me-2"></i> Newest First</a></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="sentToSupplier" data-direction="asc"><i class="fas fa-arrow-up me-2"></i> Oldest First</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="sentToSupplier" data-direction=""><i class="fas fa-times me-2 text-muted"></i> Clear Sort</a></li>
                                                </ul>
                                            </div>
                                        </th>
                                        <th data-column="deliveryTime">
                                            <div class="dropdown d-inline-block">
                                                <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                    Delivery Time <i class="fas fa-sort ms-1 text-muted" id="sortIconDeliveryTime"></i>
                                                </span>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="deliveryTime" data-direction="desc"><i class="fas fa-arrow-down me-2"></i> Newest First</a></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="deliveryTime" data-direction="asc"><i class="fas fa-arrow-up me-2"></i> Oldest First</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="deliveryTime" data-direction=""><i class="fas fa-times me-2 text-muted"></i> Clear Sort</a></li>
                                                </ul>
                                            </div>
                                        </th>
                                        <th data-column="completeTime">
                                            <div class="dropdown d-inline-block">
                                                <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                    Complete Time <i class="fas fa-sort ms-1 text-muted" id="sortIconCompleteTime"></i>
                                                </span>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="completeTime" data-direction="desc"><i class="fas fa-arrow-down me-2"></i> Newest First</a></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="completeTime" data-direction="asc"><i class="fas fa-arrow-up me-2"></i> Oldest First</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="completeTime" data-direction=""><i class="fas fa-times me-2 text-muted"></i> Clear Sort</a></li>
                                                </ul>
                                            </div>
                                        </th>
                                        <th data-column="margin">
                                            <div class="dropdown d-inline-block">
                                                <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                    Margin <i class="fas fa-sort ms-1 text-muted" id="sortIconMargin"></i>
                                                </span>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="margin" data-direction="desc"><i class="fas fa-arrow-down me-2"></i> Highest First</a></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="margin" data-direction="asc"><i class="fas fa-arrow-up me-2"></i> Lowest First</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item sort-option" href="#!" data-field="margin" data-direction=""><i class="fas fa-times me-2 text-muted"></i> Clear Sort</a></li>
                                                </ul>
                                            </div>
                                        </th>
                                        <th data-column="supplier" class="d-none">Supplier</th>
                                        <th data-column="portedTo" class="d-none">Ported To</th>
                                        <th data-column="gsmErrorCode" class="d-none">GSM Error Code</th>
                                        <th data-column="supplierCost" class="d-none">Supplier Cost</th>
                                        <th data-column="customerCost" class="d-none">Customer Cost</th>
                                        <th data-column="supplierDoneTime" class="d-none">Supplier Done Time</th>
                                        <th data-column="messageType" class="d-none">Message Type</th>
                                        <th data-column="subAccount" class="d-none">Sub-Account</th>
                                        <th data-column="user" class="d-none">User</th>
                                        <th data-column="origin" class="d-none">Origin</th>
                                        <th data-column="country" class="d-none">Country</th>
                                        <th data-column="parts" class="d-none">Fragments / Parts</th>
                                        <th data-column="encoding" class="d-none">Encoding</th>
                                        <th data-column="supplierMessageId" class="d-none">Supplier Message ID</th>
                                        <th data-column="customerMessageId" class="d-none">Customer Message ID</th>
                                        <th data-column="actions" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="messageLogTableBody">
                                    <tr id="loadingInitialRow">
                                        <td colspan="26" class="text-center py-5">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <span class="text-muted">Loading messages...</span>
                                        </td>
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
            <div class="modal-header" style="background: var(--admin-primary, #1e3a5f); color: #fff;">
                <h5 class="modal-title" id="columnSettingsModalLabel">
                    <i class="fas fa-columns me-2"></i>Column Settings
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Select which columns to display in the results table. Changes are saved automatically.</p>
                
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Default Columns</h6>
                <div class="list-group list-group-flush mb-3" id="defaultColumnsList">
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-account" data-column="account" checked>
                        <span>Account</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-mobileNumber" data-column="mobileNumber" checked>
                        <span>Mobile Number</span>
                        <span class="badge bg-light text-muted ms-auto small">Unmasked</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-ukNetworkPrefix" data-column="ukNetworkPrefix" checked>
                        <span>UK Network Prefix</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-senderId" data-column="senderId" checked>
                        <span>SenderID / Agent Name</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-status" data-column="status" checked>
                        <span>Status</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-sentTime" data-column="sentTime" checked>
                        <span>Sent Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-sentToSupplier" data-column="sentToSupplier" checked>
                        <span>Sent to Supplier</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-deliveryTime" data-column="deliveryTime" checked>
                        <span>Delivery Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-completeTime" data-column="completeTime" checked>
                        <span>Complete Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-margin" data-column="margin" checked>
                        <span>Margin</span>
                        <span class="badge bg-light text-muted ms-auto small">Customer - Supplier Cost</span>
                    </label>
                </div>
                
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Optional Columns</h6>
                <div class="list-group list-group-flush" id="optionalColumnsList">
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-supplier" data-column="supplier">
                        <span>Supplier</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-portedTo" data-column="portedTo">
                        <span>Ported To</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-gsmErrorCode" data-column="gsmErrorCode">
                        <span>GSM Error Code</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-supplierCost" data-column="supplierCost">
                        <span>Supplier Cost</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-customerCost" data-column="customerCost">
                        <span>Customer Cost</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-supplierDoneTime" data-column="supplierDoneTime">
                        <span>Supplier Done Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-messageType" data-column="messageType">
                        <span>Message Type</span>
                        <span class="badge bg-light text-muted ms-auto small">SMS / RCS</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-subAccount" data-column="subAccount">
                        <span>Sub-Account</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-user" data-column="user">
                        <span>User</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-origin" data-column="origin">
                        <span>Origin</span>
                        <span class="badge bg-light text-muted ms-auto small">API / Portal / Email-to-SMS</span>
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
                        <span class="badge bg-light text-muted ms-auto small">GSM / Unicode</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-supplierMessageId" data-column="supplierMessageId">
                        <span>Supplier Message ID</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-customerMessageId" data-column="customerMessageId">
                        <span>Customer Message ID</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnResetColumns">
                    <i class="fas fa-undo me-1"></i> Reset to Default
                </button>
                <button type="button" class="btn" style="background: var(--admin-primary, #1e3a5f); color: #fff;" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="messageDetailsModal" tabindex="-1" aria-labelledby="messageDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary, #1e3a5f); color: #fff;">
                <h5 class="modal-title" id="messageDetailsModalLabel">
                    <i class="fas fa-envelope me-2"></i>Message Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Customer Message ID</label>
                        <p class="mb-0 fw-medium" id="detailCustomerMessageId">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Supplier Message ID</label>
                        <p class="mb-0 fw-medium" id="detailSupplierMessageId">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Status</label>
                        <p class="mb-0" id="detailStatus">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Account</label>
                        <p class="mb-0" id="detailAccount">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Sub-Account</label>
                        <p class="mb-0" id="detailSubAccount">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Mobile Number</label>
                        <p class="mb-0" id="detailMobile">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">UK Network Prefix</label>
                        <p class="mb-0" id="detailUkNetworkPrefix">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Ported To</label>
                        <p class="mb-0" id="detailPortedTo">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">SenderID / Agent Name</label>
                        <p class="mb-0" id="detailSenderId">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Message Type</label>
                        <p class="mb-0" id="detailType">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">Sent Time</label>
                        <p class="mb-0" id="detailSentTime">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">Sent to Supplier</label>
                        <p class="mb-0" id="detailSentToSupplier">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">Delivery Time</label>
                        <p class="mb-0" id="detailDeliveryTime">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">Complete Time</label>
                        <p class="mb-0" id="detailCompleteTime">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Supplier</label>
                        <p class="mb-0" id="detailSupplier">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Supplier Done Time</label>
                        <p class="mb-0" id="detailSupplierDoneTime">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">GSM Error Code</label>
                        <p class="mb-0" id="detailGsmErrorCode">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Encoding</label>
                        <p class="mb-0" id="detailEncoding">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Fragments / Parts</label>
                        <p class="mb-0" id="detailParts">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Country</label>
                        <p class="mb-0" id="detailCountry">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Supplier Cost</label>
                        <p class="mb-0" id="detailSupplierCost">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Customer Cost</label>
                        <p class="mb-0" id="detailCustomerCost">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Margin</label>
                        <p class="mb-0 fw-bold" id="detailMargin">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">User</label>
                        <p class="mb-0" id="detailUser">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Origin</label>
                        <p class="mb-0" id="detailOrigin">-</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small mb-1">Content</label>
                        <div class="bg-light rounded p-3" id="detailContent">
                            <span class="text-muted fst-italic">Content not available</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard(document.getElementById('detailMessageId').textContent, 'Message ID')">
                    <i class="fas fa-copy me-1"></i> Copy Message ID
                </button>
                <button type="button" class="btn" style="background: var(--admin-primary, #1e3a5f); color: #fff;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary, #1e3a5f); color: #fff;">
                <h5 class="modal-title" id="exportModalLabel"><i class="fas fa-download me-2"></i>Export Message Log</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Choose your preferred export format:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary text-start" onclick="exportData('csv')">
                        <i class="fas fa-file-csv me-2"></i> Export as CSV
                        <small class="text-muted d-block ms-4">Comma-separated values, compatible with Excel</small>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start" onclick="exportData('xlsx')">
                        <i class="fas fa-file-excel me-2"></i> Export as XLSX
                        <small class="text-muted d-block ms-4">Microsoft Excel format</small>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start" onclick="exportData('txt')">
                        <i class="fas fa-file-alt me-2"></i> Export as TXT
                        <small class="text-muted d-block ms-4">Plain text file, tab-separated</small>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text, label) {
    navigator.clipboard.writeText(text).then(() => {
        showToast(`${label} copied to clipboard`, 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showToast('Failed to copy to clipboard', 'error');
    });
}

function showToast(message, type) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white border-0 ${type === 'success' ? 'bg-success' : 'bg-danger'}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1100';
    document.body.appendChild(container);
    return container;
}

function exportData(format) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
    if (modal) modal.hide();
    showToast(`Preparing ${format.toUpperCase()} export...`, 'success');
    setTimeout(() => {
        showToast(`${format.toUpperCase()} export ready for download`, 'success');
    }, 1500);
}

function viewMessageDetails(messageId) {
    const mockMessage = {
        customerMessageId: messageId.replace('MSG-', 'CUST-'),
        supplierMessageId: messageId.replace('MSG-', 'SUP-'),
        status: 'Delivered',
        account: 'Acme Corporation',
        subAccount: 'Main Account',
        mobile: '+447712345678',
        ukNetworkPrefix: 'EE',
        portedTo: null,
        senderId: 'QuickSMS',
        type: 'SMS',
        sentTime: '21/01/2026 14:23:45',
        sentToSupplier: '21/01/2026 14:23:46',
        deliveryTime: '21/01/2026 14:23:52',
        completeTime: '21/01/2026 14:23:55',
        supplier: 'Supplier A',
        supplierDoneTime: '21/01/2026 14:23:50',
        gsmErrorCode: null,
        encoding: 'GSM',
        parts: 1,
        country: 'UK',
        supplierCost: '0.025',
        customerCost: '0.038',
        margin: '0.013',
        user: 'John Smith',
        origin: 'Portal',
        content: 'Content masked - requires explicit reveal with audit logging'
    };
    
    document.getElementById('detailCustomerMessageId').textContent = mockMessage.customerMessageId;
    document.getElementById('detailSupplierMessageId').textContent = mockMessage.supplierMessageId;
    document.getElementById('detailStatus').innerHTML = `<span class="badge bg-success">Delivered</span>`;
    document.getElementById('detailAccount').textContent = mockMessage.account;
    document.getElementById('detailSubAccount').textContent = mockMessage.subAccount;
    document.getElementById('detailMobile').textContent = mockMessage.mobile;
    document.getElementById('detailUkNetworkPrefix').textContent = mockMessage.ukNetworkPrefix;
    document.getElementById('detailPortedTo').textContent = mockMessage.portedTo || '—';
    document.getElementById('detailSenderId').textContent = mockMessage.senderId;
    document.getElementById('detailType').innerHTML = `<span class="badge bg-secondary">${mockMessage.type}</span>`;
    document.getElementById('detailSentTime').textContent = mockMessage.sentTime;
    document.getElementById('detailSentToSupplier').textContent = mockMessage.sentToSupplier;
    document.getElementById('detailDeliveryTime').textContent = mockMessage.deliveryTime;
    document.getElementById('detailCompleteTime').textContent = mockMessage.completeTime;
    document.getElementById('detailSupplier').textContent = mockMessage.supplier;
    document.getElementById('detailSupplierDoneTime').textContent = mockMessage.supplierDoneTime;
    document.getElementById('detailGsmErrorCode').textContent = mockMessage.gsmErrorCode || '—';
    document.getElementById('detailEncoding').innerHTML = `<span class="badge bg-light text-dark border">${mockMessage.encoding}</span>`;
    document.getElementById('detailParts').textContent = mockMessage.parts;
    document.getElementById('detailCountry').textContent = mockMessage.country;
    document.getElementById('detailSupplierCost').textContent = `£${mockMessage.supplierCost}`;
    document.getElementById('detailCustomerCost').textContent = `£${mockMessage.customerCost}`;
    document.getElementById('detailMargin').textContent = `£${mockMessage.margin}`;
    document.getElementById('detailUser').textContent = mockMessage.user;
    document.getElementById('detailOrigin').textContent = mockMessage.origin;
    document.getElementById('detailContent').innerHTML = `<span class="text-muted fst-italic">${mockMessage.content}</span>`;
    
    const modal = new bootstrap.Modal(document.getElementById('messageDetailsModal'));
    modal.show();
}

const MockAPI = (function() {
    const statuses = [
        { text: 'Delivered', weight: 50 },
        { text: 'Pending', weight: 20 },
        { text: 'Expired', weight: 12 },
        { text: 'Rejected', weight: 10 },
        { text: 'Undeliverable', weight: 8 }
    ];
    const senders = ['QuickSMS', 'ALERTS', 'PROMO', 'INFO', 'NOTIFY', 'VERIFY'];
    const origins = ['Portal', 'API', 'Email-to-SMS', 'Integration'];
    const messageTypes = [
        { text: 'SMS', weight: 60 },
        { text: 'RCS Basic', weight: 25 },
        { text: 'RCS Rich', weight: 15 }
    ];
    const accounts = [
        { id: 'ACC-001', name: 'Acme Corporation' },
        { id: 'ACC-002', name: 'Finance Ltd' },
        { id: 'ACC-003', name: 'Tech Solutions' },
        { id: 'ACC-004', name: 'Retail Group' },
        { id: 'ACC-005', name: 'Healthcare UK' }
    ];
    const subAccounts = ['Main Account', 'Marketing Team', 'Support Team', 'Sales Team'];
    const users = ['John Smith', 'Sarah Johnson', 'Mike Williams', 'Emma Davis', 'James Wilson'];
    const encodings = [
        { text: 'GSM', weight: 80 },
        { text: 'Unicode', weight: 20 }
    ];
    const countries = ['UK', 'US', 'DE', 'FR', 'ES', 'IE'];
    const ukNetworkPrefixes = ['EE', 'Vodafone', 'O2', 'Three', 'MVNO/Other'];
    const suppliers = ['Supplier A', 'Supplier B', 'Supplier C', 'Supplier D'];
    const gsmErrorCodes = [null, null, null, null, null, '000', '001', '002', '003', '004', '005', '006', '007', '008'];
    const messages = [
        'Your order has been dispatched and will arrive tomorrow.',
        'Reminder: Your appointment is scheduled for tomorrow at 2pm.',
        'Your verification code is 123456. Valid for 5 minutes.',
        'Thank you for your purchase! Your receipt is attached.',
        'Flash sale! 50% off all items this weekend only.',
        'Your account balance is low. Please top up soon.',
        'Delivery update: Your package is out for delivery.',
        'Welcome to QuickSMS! Your account is now active.'
    ];

    let seed = 12345;
    function seededRandom() {
        seed = (seed * 1103515245 + 12345) & 0x7fffffff;
        return seed / 0x7fffffff;
    }
    
    function weightedRandom(items) {
        const totalWeight = items.reduce((sum, item) => sum + (item.weight || 1), 0);
        let random = seededRandom() * totalWeight;
        for (const item of items) {
            random -= (item.weight || 1);
            if (random <= 0) return item;
        }
        return items[0];
    }
    
    function pickRandom(arr) {
        return arr[Math.floor(seededRandom() * arr.length)];
    }

    const TOTAL_RECORDS = 200;
    const baseTime = new Date();
    const dataset = [];
    
    for (let i = 0; i < TOTAL_RECORDS; i++) {
        const status = weightedRandom(statuses);
        const messageType = weightedRandom(messageTypes);
        const encoding = weightedRandom(encodings);
        const parts = seededRandom() < 0.2 ? Math.floor(seededRandom() * 3) + 2 : 1;
        const account = pickRandom(accounts);
        const ukNetworkPrefix = pickRandom(ukNetworkPrefixes);
        const supplier = pickRandom(suppliers);
        
        const sentTime = new Date(baseTime);
        sentTime.setMinutes(sentTime.getMinutes() - i * 5 - Math.floor(seededRandom() * 10));
        
        const sentToSupplierTime = new Date(sentTime.getTime() + seededRandom() * 2000 + 100);
        const deliveryTime = status.text === 'Delivered' ? new Date(sentTime.getTime() + seededRandom() * 30000 + 5000) : null;
        const completeTime = status.text !== 'Pending' ? new Date(sentTime.getTime() + seededRandom() * 60000 + 10000) : null;
        const supplierDoneTime = status.text !== 'Pending' ? new Date(sentTime.getTime() + seededRandom() * 55000 + 8000) : null;
        
        const phoneDigits = String(Math.floor(seededRandom() * 10000000000)).padStart(10, '0');
        const phoneUnmasked = `+447${phoneDigits}`;
        
        const supplierCost = (parts * 0.025).toFixed(3);
        const customerCost = (parts * 0.038).toFixed(3);
        const margin = (parseFloat(customerCost) - parseFloat(supplierCost)).toFixed(3);
        
        const isPorted = seededRandom() < 0.15;
        const portedTo = isPorted ? pickRandom(ukNetworkPrefixes.filter(n => n !== ukNetworkPrefix)) : null;
        
        const gsmErrorCode = ['Rejected', 'Undeliverable', 'Expired'].includes(status.text) ? pickRandom(gsmErrorCodes.filter(c => c !== null)) : null;
        
        dataset.push({
            id: `MSG-${String(i + 1).padStart(9, '0')}`,
            customerMessageId: `CUST-${String(i + 1).padStart(9, '0')}`,
            supplierMessageId: `SUP-${String(i + 1).padStart(9, '0')}`,
            account: account.name,
            accountId: account.id,
            mobileNumber: phoneUnmasked,
            ukNetworkPrefix: ukNetworkPrefix,
            senderId: pickRandom(senders),
            status: { text: status.text },
            sentTime: sentTime,
            sentToSupplier: sentToSupplierTime,
            deliveryTime: deliveryTime,
            completeTime: completeTime,
            supplierDoneTime: supplierDoneTime,
            supplierCost: supplierCost,
            customerCost: customerCost,
            margin: margin,
            supplier: supplier,
            portedTo: portedTo,
            gsmErrorCode: gsmErrorCode,
            messageType: { text: messageType.text },
            subAccount: pickRandom(subAccounts),
            user: pickRandom(users),
            origin: pickRandom(origins),
            country: pickRandom(countries),
            parts: parts,
            encoding: { text: encoding.text },
            content: pickRandom(messages)
        });
    }
    
    console.log('[MockAPI] Generated stable dataset with', dataset.length, 'records');

    return {
        async fetchMessages(filters = {}, search = '', sort = { field: '', direction: '' }, page = 1, limit = 50) {
            await new Promise(resolve => setTimeout(resolve, 150 + Math.random() * 150));
            
            let results = [...dataset];
            
            if (filters.dateFrom) {
                const fromDate = new Date(filters.dateFrom);
                results = results.filter(msg => msg.sentTime >= fromDate);
            }
            if (filters.dateTo) {
                const toDate = new Date(filters.dateTo);
                results = results.filter(msg => msg.sentTime <= toDate);
            }
            
            if (filters.accounts && filters.accounts.length > 0) {
                results = results.filter(msg => filters.accounts.includes(msg.accountId));
            }
            
            if (filters.subAccounts && filters.subAccounts.length > 0) {
                results = results.filter(msg => filters.subAccounts.includes(msg.subAccount));
            }
            
            if (filters.users && filters.users.length > 0) {
                results = results.filter(msg => filters.users.includes(msg.user));
            }
            
            if (filters.origins && filters.origins.length > 0) {
                results = results.filter(msg => filters.origins.includes(msg.origin));
            }
            
            if (filters.mobileNumbers && filters.mobileNumbers.length > 0) {
                results = results.filter(msg => {
                    const rawNum = msg.mobileNumberRaw.toLowerCase();
                    const displayNum = msg.mobileNumber.toLowerCase();
                    return filters.mobileNumbers.some(num => {
                        const searchNum = num.toLowerCase().replace(/\s+/g, '');
                        return rawNum.includes(searchNum) || displayNum.includes(searchNum);
                    });
                });
            }
            
            if (filters.senderIds && filters.senderIds.length > 0) {
                results = results.filter(msg => filters.senderIds.includes(msg.senderId));
            }
            
            if (filters.statuses && filters.statuses.length > 0) {
                const statusLower = filters.statuses.map(s => s.toLowerCase());
                results = results.filter(msg => statusLower.includes(msg.status.text.toLowerCase()));
            }
            
            if (filters.countries && filters.countries.length > 0) {
                const countryLower = filters.countries.map(c => c.toLowerCase());
                results = results.filter(msg => countryLower.includes(msg.country.toLowerCase()));
            }
            
            if (filters.messageTypes && filters.messageTypes.length > 0) {
                const typeLower = filters.messageTypes.map(t => t.toLowerCase());
                results = results.filter(msg => typeLower.includes(msg.messageType.text.toLowerCase()));
            }
            
            if (filters.messageIds && filters.messageIds.length > 0) {
                results = results.filter(msg => {
                    return filters.messageIds.some(id => 
                        msg.id.toLowerCase().includes(id.toLowerCase())
                    );
                });
            }
            
            if (filters.ukNetworkPrefixes && filters.ukNetworkPrefixes.length > 0) {
                const networkLower = filters.ukNetworkPrefixes.map(n => n.toLowerCase());
                results = results.filter(msg => networkLower.includes(msg.ukNetworkPrefix.toLowerCase()));
            }
            
            if (filters.portedTo && filters.portedTo.length > 0) {
                results = results.filter(msg => {
                    if (filters.portedTo.includes('NOT_PORTED')) {
                        if (!msg.portedTo) return true;
                    }
                    if (msg.portedTo) {
                        const portedLower = filters.portedTo.map(p => p.toLowerCase());
                        return portedLower.includes(msg.portedTo.toLowerCase());
                    }
                    return false;
                });
            }
            
            if (search && search.trim()) {
                const searchTerm = search.toLowerCase().trim();
                results = results.filter(msg => {
                    const mobile = msg.mobileNumber.toLowerCase();
                    const sender = msg.senderId.toLowerCase();
                    const account = msg.account.toLowerCase();
                    return mobile.includes(searchTerm) || sender.includes(searchTerm) || account.includes(searchTerm);
                });
            }
            
            if (sort.field && sort.direction) {
                results.sort((a, b) => {
                    let aVal, bVal;
                    
                    switch (sort.field) {
                        case 'senderId':
                            aVal = a.senderId.toLowerCase();
                            bVal = b.senderId.toLowerCase();
                            break;
                        case 'sentTime':
                            aVal = a.sentTime ? a.sentTime.getTime() : 0;
                            bVal = b.sentTime ? b.sentTime.getTime() : 0;
                            break;
                        case 'sentToSupplier':
                            aVal = a.sentToSupplier ? a.sentToSupplier.getTime() : 0;
                            bVal = b.sentToSupplier ? b.sentToSupplier.getTime() : 0;
                            break;
                        case 'deliveryTime':
                            aVal = a.deliveryTime ? a.deliveryTime.getTime() : 0;
                            bVal = b.deliveryTime ? b.deliveryTime.getTime() : 0;
                            break;
                        case 'completeTime':
                            aVal = a.completeTime ? a.completeTime.getTime() : 0;
                            bVal = b.completeTime ? b.completeTime.getTime() : 0;
                            break;
                        case 'supplierDoneTime':
                            aVal = a.supplierDoneTime ? a.supplierDoneTime.getTime() : 0;
                            bVal = b.supplierDoneTime ? b.supplierDoneTime.getTime() : 0;
                            break;
                        case 'margin':
                            aVal = (a.supplierCost !== null && a.customerCost !== null) ? parseFloat(a.margin) : null;
                            bVal = (b.supplierCost !== null && b.customerCost !== null) ? parseFloat(b.margin) : null;
                            if (aVal === null && bVal === null) return 0;
                            if (aVal === null) return 1;
                            if (bVal === null) return -1;
                            break;
                        default:
                            return 0;
                    }
                    
                    if (aVal < bVal) return sort.direction === 'asc' ? -1 : 1;
                    if (aVal > bVal) return sort.direction === 'asc' ? 1 : -1;
                    return 0;
                });
            }
            
            const total = results.length;
            const startIndex = (page - 1) * limit;
            const paginatedResults = results.slice(startIndex, startIndex + limit);
            
            console.log(`[MockAPI] Query: page=${page}, filters=${JSON.stringify(filters)}, search="${search}", sort=${JSON.stringify(sort)} => ${total} total, returning ${paginatedResults.length}`);
            
            return {
                data: paginatedResults,
                meta: {
                    currentPage: page,
                    perPage: limit,
                    total: total,
                    totalPages: Math.ceil(total / limit),
                    hasMore: startIndex + paginatedResults.length < total
                }
            };
        },
        
        getFilterOptions() {
            return {
                statuses: statuses.map(s => s.text),
                messageTypes: messageTypes.map(t => t.text),
                accounts,
                subAccounts,
                users,
                origins,
                countries,
                senders
            };
        }
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    let filterState = {
        dateFrom: '',
        dateTo: '',
        accounts: [],
        subAccounts: [],
        origins: [],
        mobileNumbers: [],
        senderIds: [],
        statuses: [],
        countries: [],
        messageTypes: [],
        messageIds: [],
        ukNetworkPrefixes: [],
        portedTo: []
    };
    
    let pendingFilters = JSON.parse(JSON.stringify(filterState));
    let searchState = '';
    let sortState = { field: '', direction: '' };
    
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let totalMessages = 0;
    const PAGE_SIZE = 50;
    const MAX_ROWS = 10000;

    const tableBody = document.getElementById('messageLogTableBody');
    const tableContainer = document.getElementById('tableContainer');
    const loadingMore = document.getElementById('loadingMore');
    const noResultsState = document.getElementById('noResultsState');
    const displayedCount = document.getElementById('displayedCount');
    const totalCount = document.getElementById('totalCount');
    const summaryBar = document.getElementById('summaryBar');
    
    const STORAGE_KEY = 'adminMessageLogColumnConfig';
    const currentUserRole = 'admin';
    const isAdminContext = true;
    
    function canViewMessageContent() {
        return currentUserRole === 'super_admin';
    }
    
    function canViewUnmaskedMobileNumber() {
        return isAdminContext === true;
    }
    
    function renderMobileNumber(mobileNumber) {
        if (canViewUnmaskedMobileNumber()) {
            return mobileNumber;
        } else {
            const masked = mobileNumber.replace(/(\+\d{2}\d{2})\d{5}(\d{3})/, '$1*****$2');
            return `<span class="mobile-masked">${masked}</span>`;
        }
    }
    
    function renderMessageContent(plaintext) {
        if (canViewMessageContent()) {
            const truncated = plaintext.length > 50 ? plaintext.substring(0, 50) + '...' : plaintext;
            return `<span class="text-dark" title="${plaintext.replace(/"/g, '&quot;')}">${truncated}</span>`;
        } else {
            return `<span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">********</span></span>`;
        }
    }
    
    function renderMargin(supplierCost, customerCost, margin) {
        if (supplierCost === null || supplierCost === undefined || 
            customerCost === null || customerCost === undefined) {
            return '—';
        }
        return `£${margin}`;
    }
    
    function renderCost(cost) {
        if (cost === null || cost === undefined) {
            return '—';
        }
        return `£${cost}`;
    }
    
    const allColumnsList = ['account', 'mobileNumber', 'ukNetworkPrefix', 'senderId', 'status', 'sentTime', 'sentToSupplier', 'deliveryTime', 'completeTime', 'margin', 'supplier', 'portedTo', 'gsmErrorCode', 'supplierCost', 'customerCost', 'supplierDoneTime', 'messageType', 'subAccount', 'user', 'origin', 'country', 'parts', 'encoding', 'supplierMessageId', 'customerMessageId'];
    const defaultColumns = { visible: ['account', 'mobileNumber', 'ukNetworkPrefix', 'senderId', 'status', 'sentTime', 'sentToSupplier', 'deliveryTime', 'completeTime', 'margin'], order: allColumnsList };
    let columnConfig = loadColumnConfig();
    
    function loadColumnConfig() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed.visible && Array.isArray(parsed.visible)) return parsed;
            }
        } catch (e) { console.error('Error loading column config:', e); }
        return { visible: [...defaultColumns.visible], order: [...defaultColumns.order] };
    }
    
    function saveColumnConfig() {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(columnConfig)); } catch (e) { console.error('Error saving column config:', e); }
    }
    
    function applyColumnVisibility() {
        document.querySelectorAll('[data-column]').forEach(el => {
            const colName = el.getAttribute('data-column');
            if (columnConfig.visible.includes(colName) || colName === 'actions') {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });
        document.querySelectorAll('.column-toggle').forEach(cb => {
            const colName = cb.getAttribute('data-column') || cb.id.replace('col-', '');
            cb.checked = columnConfig.visible.includes(colName);
        });
    }
    
    function formatDateTime(date) {
        if (!date) return '-';
        const d = new Date(date);
        return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    
    var useCleanStyle = localStorage.getItem('adminMessageLogTableStyle') === 'clean';
    var tableStyleToggle = document.getElementById('tableStyleToggle');
    var messageLogTable = document.getElementById('messageLogTable');
    var labelColoredRows = document.getElementById('labelColoredRows');
    
    function updateToggleLabels() {
        if (useCleanStyle) {
            labelColoredRows.classList.remove('active');
            messageLogTable.classList.add('clean-style');
        } else {
            labelColoredRows.classList.add('active');
            messageLogTable.classList.remove('clean-style');
        }
    }
    
    tableStyleToggle.checked = useCleanStyle;
    updateToggleLabels();
    
    tableStyleToggle.addEventListener('change', function() {
        useCleanStyle = this.checked;
        localStorage.setItem('adminMessageLogTableStyle', useCleanStyle ? 'clean' : 'colored');
        updateToggleLabels();
        loadMessages(true);
    });
    
    function getStatusBadge(statusText) {
        let badgeClass = 'badge-success';
        if (statusText === 'Delivered') {
            badgeClass = 'badge-success';
        } else if (statusText === 'Pending') {
            badgeClass = 'badge-primary';
        } else if (['Undeliverable', 'Rejected', 'Expired', 'Failed', 'Blocked', 'Blacklisted'].includes(statusText)) {
            badgeClass = 'badge-danger';
        }
        return `<span class="badge light ${badgeClass}">${statusText}</span>`;
    }
    
    function createRow(msg) {
        const statusText = msg.status.text;
        const typeText = msg.messageType.text;
        const encodingText = msg.encoding.text;
        
        let rowClass = '';
        if (!useCleanStyle) {
            if (statusText === 'Delivered') {
                rowClass = 'table-success';
            } else if (statusText === 'Pending') {
                rowClass = 'table-primary';
            } else if (['Undeliverable', 'Rejected', 'Expired', 'Failed', 'Blocked', 'Blacklisted'].includes(statusText)) {
                rowClass = 'table-danger';
            }
        }
        
        const statusDisplay = useCleanStyle ? getStatusBadge(statusText) : statusText;
        
        return `<tr class="${rowClass}">
            <td class="py-2 ${columnConfig.visible.includes('account') ? '' : 'd-none'}" data-column="account">${msg.account}</td>
            <td class="py-2 ${columnConfig.visible.includes('mobileNumber') ? '' : 'd-none'}" data-column="mobileNumber">${renderMobileNumber(msg.mobileNumber)}</td>
            <td class="py-2 ${columnConfig.visible.includes('ukNetworkPrefix') ? '' : 'd-none'}" data-column="ukNetworkPrefix">${msg.ukNetworkPrefix || '—'}</td>
            <td class="py-2 ${columnConfig.visible.includes('senderId') ? '' : 'd-none'}" data-column="senderId">${msg.senderId}</td>
            <td class="py-2 ${columnConfig.visible.includes('status') ? '' : 'd-none'}" data-column="status">${statusDisplay}</td>
            <td class="py-2 ${columnConfig.visible.includes('sentTime') ? '' : 'd-none'}" data-column="sentTime">${formatDateTime(msg.sentTime)}</td>
            <td class="py-2 ${columnConfig.visible.includes('sentToSupplier') ? '' : 'd-none'}" data-column="sentToSupplier">${formatDateTime(msg.sentToSupplier)}</td>
            <td class="py-2 ${columnConfig.visible.includes('deliveryTime') ? '' : 'd-none'}" data-column="deliveryTime">${formatDateTime(msg.deliveryTime)}</td>
            <td class="py-2 ${columnConfig.visible.includes('completeTime') ? '' : 'd-none'}" data-column="completeTime">${formatDateTime(msg.completeTime)}</td>
            <td class="py-2 ${columnConfig.visible.includes('margin') ? '' : 'd-none'}" data-column="margin">${renderMargin(msg.supplierCost, msg.customerCost, msg.margin)}</td>
            <td class="py-2 ${columnConfig.visible.includes('supplier') ? '' : 'd-none'}" data-column="supplier">${msg.supplier || '—'}</td>
            <td class="py-2 ${columnConfig.visible.includes('portedTo') ? '' : 'd-none'}" data-column="portedTo">${msg.portedTo || '—'}</td>
            <td class="py-2 ${columnConfig.visible.includes('gsmErrorCode') ? '' : 'd-none'}" data-column="gsmErrorCode">${msg.gsmErrorCode || '—'}</td>
            <td class="py-2 ${columnConfig.visible.includes('supplierCost') ? '' : 'd-none'}" data-column="supplierCost">${renderCost(msg.supplierCost)}</td>
            <td class="py-2 ${columnConfig.visible.includes('customerCost') ? '' : 'd-none'}" data-column="customerCost">${renderCost(msg.customerCost)}</td>
            <td class="py-2 ${columnConfig.visible.includes('supplierDoneTime') ? '' : 'd-none'}" data-column="supplierDoneTime">${formatDateTime(msg.supplierDoneTime)}</td>
            <td class="py-2 ${columnConfig.visible.includes('messageType') ? '' : 'd-none'}" data-column="messageType">${typeText}</td>
            <td class="py-2 ${columnConfig.visible.includes('subAccount') ? '' : 'd-none'}" data-column="subAccount">${msg.subAccount}</td>
            <td class="py-2 ${columnConfig.visible.includes('user') ? '' : 'd-none'}" data-column="user">${msg.user}</td>
            <td class="py-2 ${columnConfig.visible.includes('origin') ? '' : 'd-none'}" data-column="origin">${msg.origin}</td>
            <td class="py-2 ${columnConfig.visible.includes('country') ? '' : 'd-none'}" data-column="country">${msg.country}</td>
            <td class="py-2 ${columnConfig.visible.includes('parts') ? '' : 'd-none'}" data-column="parts">${msg.parts}</td>
            <td class="py-2 ${columnConfig.visible.includes('encoding') ? '' : 'd-none'}" data-column="encoding">${encodingText}</td>
            <td class="py-2 ${columnConfig.visible.includes('supplierMessageId') ? '' : 'd-none'}" data-column="supplierMessageId">${msg.supplierMessageId || '—'}</td>
            <td class="py-2 ${columnConfig.visible.includes('customerMessageId') ? '' : 'd-none'}" data-column="customerMessageId">${msg.customerMessageId || '—'}</td>
            <td class="py-2 text-center" data-column="actions">
                <div class="dropdown">
                    <span class="action-dots" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <i class="fas fa-ellipsis-v"></i>
                    </span>
                    <div class="dropdown-menu dropdown-menu-end border py-0">
                        <div class="dropdown-content">
                            <a class="dropdown-item" href="#!" onclick="viewMessageDetails('${msg.id}'); return false;"><i class="fas fa-eye me-2 text-info"></i>View Details</a>
                            <a class="dropdown-item" href="#!" onclick="copyToClipboard('${msg.customerMessageId}', 'Customer Message ID'); return false;"><i class="fas fa-copy me-2 text-primary"></i>Copy Customer Message ID</a>
                            <a class="dropdown-item" href="#!" onclick="copyToClipboard('${msg.mobileNumber}', 'Mobile Number'); return false;"><i class="fas fa-phone me-2 text-success"></i>Copy Mobile Number</a>
                        </div>
                    </div>
                </div>
            </td>
        </tr>`;
    }
    
    async function loadMessages(reset = false) {
        if (isLoading) return;
        if (!reset && !hasMore) return;
        
        isLoading = true;
        
        if (reset) {
            currentPage = 1;
            tableBody.innerHTML = `<tr id="loadingInitialRow"><td colspan="26" class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div><span class="text-muted">Loading messages...</span></td></tr>`;
            noResultsState.classList.add('d-none');
        } else {
            loadingMore.classList.remove('d-none');
        }
        
        try {
            const response = await MockAPI.fetchMessages(filterState, searchState, sortState, currentPage, PAGE_SIZE);
            const { data, meta } = response;
            
            if (reset) {
                tableBody.innerHTML = '';
                totalMessages = meta.total;
                if (totalCount) totalCount.textContent = totalMessages.toLocaleString();
                
                const summaryTotalEl = document.getElementById('summaryTotal');
                const summaryPartsEl = document.getElementById('summaryParts');
                const renderedCountEl = document.getElementById('renderedCount');
                if (summaryTotalEl) summaryTotalEl.textContent = totalMessages.toLocaleString();
                if (summaryPartsEl) summaryPartsEl.textContent = Math.floor(totalMessages * 1.15).toLocaleString();
                if (summaryBar) summaryBar.style.display = 'block';
            }
            
            const currentRows = tableBody.querySelectorAll('tr').length;
            if (currentRows >= MAX_ROWS) {
                hasMore = false;
                console.log('[Message Log] Max rows limit reached');
                return;
            }
            
            data.forEach(msg => {
                tableBody.insertAdjacentHTML('beforeend', createRow(msg));
            });
            
            const rowCount = tableBody.querySelectorAll('tr').length;
            if (displayedCount) displayedCount.textContent = rowCount.toLocaleString();
            const renderedCount = document.getElementById('renderedCount');
            if (renderedCount) renderedCount.textContent = rowCount.toLocaleString();
            
            hasMore = meta.hasMore && rowCount < MAX_ROWS;
            currentPage++;
            
            if (reset && data.length === 0) {
                noResultsState.classList.remove('d-none');
            }
            
        } catch (error) {
            console.error('[Message Log] Error loading messages:', error);
            if (reset) {
                tableBody.innerHTML = `<tr><td colspan="26" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle me-2"></i>Error loading messages. Please try again.</td></tr>`;
            }
        } finally {
            isLoading = false;
            loadingMore.classList.add('d-none');
        }
    }
    
    tableContainer?.addEventListener('scroll', function() {
        if (isLoading || !hasMore) return;
        const { scrollTop, scrollHeight, clientHeight } = this;
        if (scrollTop + clientHeight >= scrollHeight - 100) {
            loadMessages(false);
        }
    });
    
    loadMessages(true);
    
    const labelMappings = {
        accounts: { 'ACC-001': 'Acme Corporation', 'ACC-002': 'Finance Ltd', 'ACC-003': 'Tech Solutions', 'ACC-004': 'Retail Group', 'ACC-005': 'Healthcare UK' },
        subAccounts: { main: 'Main Account', marketing: 'Marketing Team', support: 'Support Team', sales: 'Sales Team' },
        origins: { portal: 'Portal', api: 'API', 'email-to-sms': 'Email-to-SMS', integration: 'Integration' },
        statuses: { delivered: 'Delivered', pending: 'Pending', undeliverable: 'Undeliverable', rejected: 'Rejected' },
        countries: { uk: 'United Kingdom', us: 'United States', de: 'Germany', fr: 'France', es: 'Spain', ie: 'Ireland' },
        messageTypes: { sms: 'SMS', 'rcs-basic': 'RCS Basic', 'rcs-rich': 'RCS Rich' },
        portedTo: { 'NOT_PORTED': 'Not Ported' }
    };
    
    function formatDateInput(date, isEndOfDay = false) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const time = isEndOfDay ? '23:59:59' : '00:00:00';
        return `${year}-${month}-${day}T${time}`;
    }
    
    document.querySelectorAll('.date-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const preset = this.dataset.preset;
            const today = new Date();
            let fromDate, toDate = today;
            switch(preset) {
                case 'today': fromDate = today; break;
                case 'yesterday': fromDate = new Date(today); fromDate.setDate(fromDate.getDate() - 1); toDate = fromDate; break;
                case '7days': fromDate = new Date(today); fromDate.setDate(fromDate.getDate() - 7); break;
                case '30days': fromDate = new Date(today); fromDate.setDate(fromDate.getDate() - 30); break;
                case 'thismonth': fromDate = new Date(today.getFullYear(), today.getMonth(), 1); break;
                case 'lastmonth': fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1); toDate = new Date(today.getFullYear(), today.getMonth(), 0); break;
            }
            document.getElementById('filterDateFrom').value = formatDateInput(fromDate, false);
            document.getElementById('filterDateTo').value = formatDateInput(toDate, true);
            pendingFilters.dateFrom = formatDateInput(fromDate, false);
            pendingFilters.dateTo = formatDateInput(toDate, true);
        });
    });
    
    document.getElementById('filterDateFrom')?.addEventListener('change', function() {
        pendingFilters.dateFrom = this.value;
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
    });
    
    document.getElementById('filterDateTo')?.addEventListener('change', function() {
        pendingFilters.dateTo = this.value;
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
    });
    
    const defaultLabels = {
        accounts: 'All Accounts',
        subAccounts: 'All Sub Accounts',
        users: 'All Users',
        origins: 'All Origins',
        statuses: 'All Statuses',
        countries: 'All Countries',
        messageTypes: 'All Types',
        senderIds: 'All SenderIDs'
    };
    
    function updateMultiselectLabel(dropdown) {
        const filterKey = dropdown.dataset.filter;
        const checkboxes = dropdown.querySelectorAll('.form-check-input:checked');
        const labelSpan = dropdown.querySelector('.dropdown-label');
        const count = checkboxes.length;
        
        if (count === 0) {
            labelSpan.textContent = defaultLabels[filterKey];
        } else if (count === 1) {
            labelSpan.textContent = checkboxes[0].nextElementSibling.textContent;
        } else if (count <= 2) {
            const names = Array.from(checkboxes).map(cb => cb.nextElementSibling.textContent);
            labelSpan.textContent = names.join(', ');
        } else {
            labelSpan.textContent = `${count} selected`;
        }
    }
    
    function syncMultiselectToPending(dropdown) {
        const filterKey = dropdown.dataset.filter;
        const checkboxes = dropdown.querySelectorAll('.form-check-input:checked');
        pendingFilters[filterKey] = Array.from(checkboxes).map(cb => cb.value);
        console.log('[Filter] ' + filterKey + ' changed to:', pendingFilters[filterKey]);
        updateMultiselectLabel(dropdown);
    }
    
    document.querySelectorAll('.multiselect-dropdown').forEach(dropdown => {
        const filterKey = dropdown.dataset.filter;
        
        dropdown.querySelectorAll('.form-check-input').forEach(checkbox => {
            checkbox.addEventListener('change', () => syncMultiselectToPending(dropdown));
        });
        
        dropdown.querySelector('.select-all-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.querySelectorAll('.form-check-input').forEach(cb => cb.checked = true);
            syncMultiselectToPending(dropdown);
        });
        
        dropdown.querySelector('.clear-all-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.querySelectorAll('.form-check-input').forEach(cb => cb.checked = false);
            syncMultiselectToPending(dropdown);
        });
    });
    
    function setupMultiValueInput(inputId, stateKey) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const value = this.value.trim();
                if (value && !pendingFilters[stateKey].includes(value)) {
                    pendingFilters[stateKey].push(value);
                }
                this.value = '';
            }
        });
    }
    
    setupMultiValueInput('filterMobileNumber', 'mobileNumbers');
    setupMultiValueInput('filterMessageId', 'messageIds');
    
    document.querySelectorAll('.searchable-dropdown').forEach(dropdown => {
        const filterKey = dropdown.dataset.filter;
        const searchInput = dropdown.querySelector('.sender-search-input');
        const optionsContainer = dropdown.querySelector('.sender-options');
        
        if (searchInput && optionsContainer) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                optionsContainer.querySelectorAll('.form-check').forEach(option => {
                    const label = option.querySelector('.form-check-label').textContent.toLowerCase();
                    option.style.display = label.includes(searchTerm) ? '' : 'none';
                });
            });
        }
        
        dropdown.querySelectorAll('.form-check-input').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const checkboxes = dropdown.querySelectorAll('.form-check-input:checked');
                pendingFilters[filterKey] = Array.from(checkboxes).map(cb => cb.value);
                updateMultiselectLabel(dropdown);
            });
        });
        
        dropdown.querySelector('.select-all-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.querySelectorAll('.form-check:not([style*="display: none"]) .form-check-input').forEach(cb => cb.checked = true);
            const checkboxes = dropdown.querySelectorAll('.form-check-input:checked');
            pendingFilters[filterKey] = Array.from(checkboxes).map(cb => cb.value);
            updateMultiselectLabel(dropdown);
        });
        
        dropdown.querySelector('.clear-all-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.querySelectorAll('.form-check-input').forEach(cb => cb.checked = false);
            pendingFilters[filterKey] = [];
            updateMultiselectLabel(dropdown);
        });
    });
    
    document.getElementById('btnApplyFilters')?.addEventListener('click', function() {
        filterState = JSON.parse(JSON.stringify(pendingFilters));
        console.log('[Filter] Applied filters:', filterState);
        updateActiveFilterChips();
        hasMore = true;
        loadMessages(true);
    });
    
    document.getElementById('btnResetFilters')?.addEventListener('click', function() {
        pendingFilters = {
            dateFrom: '', dateTo: '', accounts: [], subAccounts: [], users: [], origins: [],
            mobileNumbers: [], senderIds: [], statuses: [], countries: [], messageTypes: [], messageIds: []
        };
        
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.getElementById('filterMobileNumber').value = '';
        document.getElementById('filterMessageId').value = '';
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
        
        document.querySelectorAll('.multiselect-dropdown').forEach(dropdown => {
            dropdown.querySelectorAll('.form-check-input').forEach(cb => cb.checked = false);
            updateMultiselectLabel(dropdown);
        });
        
        document.querySelectorAll('.searchable-dropdown').forEach(dropdown => {
            dropdown.querySelectorAll('.form-check-input').forEach(cb => cb.checked = false);
            const searchInput = dropdown.querySelector('.sender-search-input');
            if (searchInput) searchInput.value = '';
            dropdown.querySelectorAll('.form-check').forEach(opt => opt.style.display = '');
            updateMultiselectLabel(dropdown);
        });
        
        console.log('[Filter] Pending filters reset (not applied)');
    });
    
    function createChip(label, value, key) {
        return `<span class="badge text-white me-2 mb-1 d-inline-flex align-items-center" style="padding: 0.5em 0.75em; background: var(--admin-primary, #1e3a5f);">
            <span class="fw-bold me-1">${label}:</span> ${value}
            <button type="button" class="btn-close btn-close-white btn-close-sm ms-2" style="font-size: 0.6rem;" data-filter="${key}"></button>
        </span>`;
    }
    
    function updateActiveFilterChips() {
        const container = document.getElementById('activeFiltersChips');
        const wrapper = document.getElementById('activeFiltersContainer');
        if (!container) return;
        container.innerHTML = '';
        
        let hasFilters = false;
        
        if (filterState.dateFrom || filterState.dateTo) {
            const fromText = filterState.dateFrom ? new Date(filterState.dateFrom).toLocaleDateString('en-GB') : 'Start';
            const toText = filterState.dateTo ? new Date(filterState.dateTo).toLocaleDateString('en-GB') : 'End';
            container.innerHTML += createChip('Date', `${fromText} to ${toText}`, 'dateRange');
            hasFilters = true;
        }
        
        ['accounts', 'subAccounts', 'origins', 'statuses', 'countries', 'messageTypes', 'ukNetworkPrefixes', 'portedTo'].forEach(key => {
            if (filterState[key] && filterState[key].length > 0) {
                const labels = filterState[key].map(v => labelMappings[key]?.[v] || v);
                const displayLabel = key.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase()).trim();
                container.innerHTML += createChip(displayLabel, labels.join(', '), key);
                hasFilters = true;
            }
        });
        
        if (filterState.mobileNumbers && filterState.mobileNumbers.length > 0) {
            container.innerHTML += createChip('Mobile', `${filterState.mobileNumbers.length} number(s)`, 'mobileNumbers');
            hasFilters = true;
        }
        
        if (filterState.messageIds && filterState.messageIds.length > 0) {
            container.innerHTML += createChip('Message ID', `${filterState.messageIds.length} ID(s)`, 'messageIds');
            hasFilters = true;
        }
        
        if (searchState) {
            container.innerHTML += createChip('Search', searchState, 'search');
            hasFilters = true;
        }
        
        if (sortState.field && sortState.direction) {
            const sortLabels = {
                senderId: 'SenderID',
                sentTime: 'Sent Time',
                deliveryTime: 'Delivery Time',
                completedTime: 'Completed Time'
            };
            const dirLabels = {
                asc: sortState.field === 'senderId' ? 'A to Z' : 'Oldest',
                desc: sortState.field === 'senderId' ? 'Z to A' : 'Newest'
            };
            container.innerHTML += createChip('Sort', `${sortLabels[sortState.field]} ${dirLabels[sortState.direction]}`, 'sort');
            hasFilters = true;
        }
        
        if (wrapper) {
            wrapper.style.display = hasFilters ? 'block' : 'none';
        }
    }
    
    document.querySelectorAll('.column-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const colName = this.getAttribute('data-column') || this.id.replace('col-', '');
            if (this.checked) {
                if (!columnConfig.visible.includes(colName)) columnConfig.visible.push(colName);
            } else {
                columnConfig.visible = columnConfig.visible.filter(c => c !== colName);
            }
            saveColumnConfig();
            applyColumnVisibility();
        });
    });
    
    document.getElementById('btnResetColumns')?.addEventListener('click', function() {
        columnConfig = { visible: [...defaultColumns.visible], order: [...defaultColumns.order] };
        saveColumnConfig();
        applyColumnVisibility();
    });
    
    document.getElementById('btnClearFiltersEmpty')?.addEventListener('click', function() {
        document.getElementById('btnResetFilters').click();
        document.getElementById('btnApplyFilters').click();
    });
    
    applyColumnVisibility();
    
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    
    function performSearch() {
        const searchValue = searchInput?.value.trim() || '';
        searchState = searchValue;
        console.log('[Search] Searching for:', searchState);
        hasMore = true;
        loadMessages(true);
        updateActiveFilterChips();
    }
    
    searchInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
    
    searchBtn?.addEventListener('click', performSearch);
    
    function updateSortIcons() {
        ['sortIconSenderId', 'sortIconSentTime', 'sortIconDeliveryTime', 'sortIconCompletedTime'].forEach(id => {
            const icon = document.getElementById(id);
            if (icon) {
                icon.className = 'fas fa-sort ms-1 text-muted';
            }
        });
        
        if (sortState.field && sortState.direction) {
            const iconId = 'sortIcon' + sortState.field.charAt(0).toUpperCase() + sortState.field.slice(1);
            const icon = document.getElementById(iconId);
            if (icon) {
                if (sortState.field === 'senderId') {
                    icon.className = sortState.direction === 'asc' 
                        ? 'fas fa-sort-alpha-down ms-1 text-primary'
                        : 'fas fa-sort-alpha-up ms-1 text-primary';
                } else {
                    icon.className = sortState.direction === 'asc'
                        ? 'fas fa-sort-up ms-1 text-primary'
                        : 'fas fa-sort-down ms-1 text-primary';
                }
            }
        }
    }
    
    document.querySelectorAll('.sort-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const field = this.dataset.field;
            const direction = this.dataset.direction;
            
            if (direction === '') {
                sortState = { field: '', direction: '' };
            } else {
                sortState = { field, direction };
            }
            
            console.log('[Sort] Sort updated:', sortState);
            updateSortIcons();
            updateActiveFilterChips();
            hasMore = true;
            loadMessages(true);
        });
    });
    
    document.getElementById('activeFiltersChips')?.addEventListener('click', function(e) {
        const closeBtn = e.target.closest('.btn-close');
        if (!closeBtn) return;
        
        const filterKey = closeBtn.dataset.filter;
        if (!filterKey) return;
        
        console.log('[Chip] Removing filter:', filterKey);
        
        if (filterKey === 'dateRange') {
            filterState.dateFrom = '';
            filterState.dateTo = '';
            pendingFilters.dateFrom = '';
            pendingFilters.dateTo = '';
            document.getElementById('filterDateFrom').value = '';
            document.getElementById('filterDateTo').value = '';
        } else if (filterKey === 'mobileNumbers') {
            filterState.mobileNumbers = [];
            pendingFilters.mobileNumbers = [];
            document.getElementById('filterMobileNumber').value = '';
        } else if (filterKey === 'messageIds') {
            filterState.messageIds = [];
            pendingFilters.messageIds = [];
            document.getElementById('filterMessageId').value = '';
        } else if (filterKey === 'search') {
            searchState = '';
            if (searchInput) searchInput.value = '';
        } else if (filterKey === 'sort') {
            sortState = { field: '', direction: '' };
            updateSortIcons();
        } else if (Array.isArray(filterState[filterKey])) {
            filterState[filterKey] = [];
            pendingFilters[filterKey] = [];
            const dropdown = document.querySelector(`.multiselect-dropdown[data-filter="${filterKey}"]`);
            if (dropdown) {
                dropdown.querySelectorAll('.form-check-input').forEach(cb => cb.checked = false);
                updateMultiselectLabel(dropdown);
            }
        }
        
        updateActiveFilterChips();
        hasMore = true;
        loadMessages(true);
    });
});
</script>
@endpush

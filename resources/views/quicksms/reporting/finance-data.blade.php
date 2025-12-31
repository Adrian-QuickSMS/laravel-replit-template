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
                                    <div class="col-12 col-lg-6">
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
                                        <label class="form-label small fw-bold">Sub Account</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="subAccounts">
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
                                        <div class="dropdown multiselect-dropdown" data-filter="users">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Users</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="John Smith" id="user1"><label class="form-check-label small" for="user1">John Smith</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Sarah Johnson" id="user2"><label class="form-check-label small" for="user2">Sarah Johnson</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Mike Williams" id="user3"><label class="form-check-label small" for="user3">Mike Williams</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Emma Davis" id="user4"><label class="form-check-label small" for="user4">Emma Davis</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Transaction Type</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="transactionTypes">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Types</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Credit" id="type1"><label class="form-check-label small" for="type1">Credit</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Debit" id="type2"><label class="form-check-label small" for="type2">Debit</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Refund" id="type3"><label class="form-check-label small" for="type3">Refund</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Adjustment" id="type4"><label class="form-check-label small" for="type4">Adjustment</label></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-3 align-items-end mt-2">
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Channel</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="channels">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Channels</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="SMS" id="channel1"><label class="form-check-label small" for="channel1">SMS</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="RCS" id="channel2"><label class="form-check-label small" for="channel2">RCS</label></div>
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
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="UK" id="countryUK"><label class="form-check-label small" for="countryUK">United Kingdom</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="US" id="countryUS"><label class="form-check-label small" for="countryUS">United States</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="DE" id="countryDE"><label class="form-check-label small" for="countryDE">Germany</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="FR" id="countryFR"><label class="form-check-label small" for="countryFR">France</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="IE" id="countryIE"><label class="form-check-label small" for="countryIE">Ireland</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="ES" id="countryES"><label class="form-check-label small" for="countryES">Spain</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="IT" id="countryIT"><label class="form-check-label small" for="countryIT">Italy</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="NL" id="countryNL"><label class="form-check-label small" for="countryNL">Netherlands</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" value="AU" id="countryAU"><label class="form-check-label small" for="countryAU">Australia</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Transaction ID</label>
                                        <input type="text" class="form-control form-control-sm" id="filterTransactionId" placeholder="Enter ID...">
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
    initDatePresets();
    initFilterActions();
});

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
                checkboxes.forEach(function(cb) { cb.checked = true; });
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
    var checked = Array.from(checkboxes).filter(function(cb) { return cb.checked; });
    
    if (checked.length === 0) {
        labelSpan.textContent = 'All ' + (dropdown.getAttribute('data-filter') || 'Items');
    } else if (checked.length === 1) {
        labelSpan.textContent = checked[0].value;
    } else {
        labelSpan.textContent = checked.length + ' selected';
    }
}

function initDatePresets() {
    document.querySelectorAll('.date-preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-preset-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            
            var preset = this.getAttribute('data-preset');
            var now = new Date();
            var from, to;
            
            switch(preset) {
                case 'today':
                    from = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);
                    to = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    break;
                case 'yesterday':
                    from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1, 0, 0, 0);
                    to = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1, 23, 59, 59);
                    break;
                case '7days':
                    from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6, 0, 0, 0);
                    to = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    break;
                case '30days':
                    from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 29, 0, 0, 0);
                    to = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    break;
                case 'thismonth':
                    from = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
                    to = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    break;
                case 'lastmonth':
                    from = new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0, 0);
                    to = new Date(now.getFullYear(), now.getMonth(), 0, 23, 59, 59);
                    break;
            }
            
            if (from && to) {
                document.getElementById('filterDateFrom').value = formatDateTimeLocal(from);
                document.getElementById('filterDateTo').value = formatDateTimeLocal(to);
            }
        });
    });
}

function formatDateTimeLocal(date) {
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    var hours = String(date.getHours()).padStart(2, '0');
    var minutes = String(date.getMinutes()).padStart(2, '0');
    var seconds = String(date.getSeconds()).padStart(2, '0');
    return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes + ':' + seconds;
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
        });
    }
}

function applyFilters() {
    var activeFilters = [];
    
    var dateFrom = document.getElementById('filterDateFrom').value;
    var dateTo = document.getElementById('filterDateTo').value;
    if (dateFrom || dateTo) {
        activeFilters.push({ type: 'Date Range', value: (dateFrom || '...') + ' to ' + (dateTo || '...') });
    }
    
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        var checked = Array.from(dropdown.querySelectorAll('.form-check-input:checked'));
        if (checked.length > 0 && checked.length < dropdown.querySelectorAll('.form-check-input').length) {
            var filterName = dropdown.getAttribute('data-filter');
            checked.forEach(function(cb) {
                activeFilters.push({ type: filterName, value: cb.value });
            });
        }
    });
    
    var transactionId = document.getElementById('filterTransactionId').value.trim();
    if (transactionId) {
        activeFilters.push({ type: 'Transaction ID', value: transactionId });
    }
    
    renderActiveFilters(activeFilters);
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
            '<span class="remove-chip" data-index="' + index + '"><i class="fas fa-times"></i></span>';
        chipsDiv.appendChild(chip);
    });
    
    chipsDiv.querySelectorAll('.remove-chip').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.filter-chip').remove();
            if (chipsDiv.querySelectorAll('.filter-chip').length === 0) {
                container.style.display = 'none';
            }
        });
    });
}

function resetFilters() {
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterTransactionId').value = '';
    
    document.querySelectorAll('.date-preset-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        dropdown.querySelectorAll('.form-check-input').forEach(function(cb) {
            cb.checked = false;
        });
        updateDropdownLabel(dropdown);
    });
    
    document.getElementById('activeFiltersContainer').style.display = 'none';
    document.getElementById('activeFiltersChips').innerHTML = '';
}
</script>
@endpush

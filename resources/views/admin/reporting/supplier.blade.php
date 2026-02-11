@extends('layouts.admin')

@section('title', 'Supplier Reporting')

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
#supplierDataTable {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}
#supplierDataTable thead th {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 0.75rem 0.5rem !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    color: #495057 !important;
    white-space: nowrap;
    text-transform: none !important;
    letter-spacing: normal !important;
    cursor: pointer;
    user-select: none;
}
#supplierDataTable thead th:hover {
    background: #e9ecef !important;
}
#supplierDataTable thead th .sort-icon {
    margin-left: 4px;
    opacity: 0.3;
    font-size: 0.7rem;
}
#supplierDataTable thead th.sort-asc .sort-icon,
#supplierDataTable thead th.sort-desc .sort-icon {
    opacity: 1;
}
#supplierDataTable tbody tr {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
#supplierDataTable tbody tr:hover td {
    background-color: #f8f9fa !important;
}
#supplierDataTable tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
    color: #495057;
}
#supplierDataTable tbody tr:last-child td {
    border-bottom: none;
}
#supplierDataTable .drill-label {
    font-weight: 500;
    color: #343a40;
}
.drill-row {
    background-color: rgba(30, 58, 95, 0.02) !important;
}
.drill-row:hover {
    background-color: rgba(30, 58, 95, 0.08) !important;
}
.drill-row td:first-child {
    padding-left: 2rem;
}
.kpi-card {
    border: none;
    border-radius: 0.5rem;
    padding: 1.25rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    transition: box-shadow 0.2s ease;
}
.kpi-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.kpi-card .kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.kpi-card .kpi-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #343a40;
}
.kpi-card .kpi-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
.badge-delivery-good { background-color: #d4edda; color: #155724; }
.badge-delivery-warn { background-color: #fff3cd; color: #856404; }
.badge-delivery-bad { background-color: #f8d7da; color: #721c24; }
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
.pagination-info {
    font-size: 0.8rem;
    color: #6c757d;
}
.table-pagination .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Reporting</a></li>
            <li class="breadcrumb-item active">Supplier</li>
        </ol>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-1" style="color: var(--admin-primary, #1e3a5f); font-weight: 600;">Supplier Reporting</h4>
            <p class="text-muted mb-0 small">Supplier cost, delivery and route performance data</p>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="exportCsvBtn">
            <i class="fas fa-download me-1"></i> Export CSV
        </button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon me-3" style="background: rgba(30,58,95,0.1); color: var(--admin-primary, #1e3a5f);">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div>
                        <div class="kpi-value" id="kpiTotalMessages">2,847,562</div>
                        <div class="kpi-label">Total Messages Sent</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon me-3" style="background: rgba(40,167,69,0.1); color: #28a745;">
                        <i class="fas fa-pound-sign"></i>
                    </div>
                    <div>
                        <div class="kpi-value" id="kpiTotalCost">&pound;42,318.45</div>
                        <div class="kpi-label">Total Cost</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon me-3" style="background: rgba(253,126,20,0.1); color: #fd7e14;">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div>
                        <div class="kpi-value" id="kpiAvgCost">&pound;0.0149</div>
                        <div class="kpi-label">Avg Cost / Message</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center">
                    <div class="kpi-icon me-3" style="background: rgba(23,162,184,0.1); color: #17a2b8;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="kpi-value" id="kpiDeliveryRate">97.3%</div>
                        <div class="kpi-label">Delivery Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4" style="border: none; border-radius: 0.5rem;">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div style="max-width: 400px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search supplier, country, route...">
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
                                    <div class="row g-3 align-items-end mb-3">
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <label class="form-label small fw-bold">Date From</label>
                                            <input type="date" class="form-control form-control-sm" id="dateFrom">
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <label class="form-label small fw-bold">Date To</label>
                                            <input type="date" class="form-control form-control-sm" id="dateTo">
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-8">
                                            <label class="form-label small fw-bold d-block">Quick Range</label>
                                            <div class="d-flex flex-wrap gap-1">
                                                <button type="button" class="month-preset-btn active" data-range="this-month">This Month</button>
                                                <button type="button" class="month-preset-btn" data-range="last-month">Last Month</button>
                                                <button type="button" class="month-preset-btn" data-range="last-3-months">Last 3 Months</button>
                                                <button type="button" class="month-preset-btn" data-range="last-6-months">Last 6 Months</button>
                                                <button type="button" class="month-preset-btn" data-range="ytd">YTD</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <label class="form-label small fw-bold">Supplier</label>
                                            <div class="dropdown multiselect-dropdown" data-filter="suppliers" id="supplierDropdown">
                                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                    <span class="dropdown-label">All Suppliers</span>
                                                </button>
                                                <div class="dropdown-menu w-100 p-2" style="max-height: 280px; overflow-y: auto;">
                                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                    </div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="BT Wholesale" id="sup1"><label class="form-check-label small" for="sup1">BT Wholesale</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Vodafone" id="sup2"><label class="form-check-label small" for="sup2">Vodafone</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Three UK" id="sup3"><label class="form-check-label small" for="sup3">Three UK</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="O2/Telefonica" id="sup4"><label class="form-check-label small" for="sup4">O2/Telefonica</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="EE" id="sup5"><label class="form-check-label small" for="sup5">EE</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Sinch" id="sup6"><label class="form-check-label small" for="sup6">Sinch</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Infobip" id="sup7"><label class="form-check-label small" for="sup7">Infobip</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Twilio" id="sup8"><label class="form-check-label small" for="sup8">Twilio</label></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <label class="form-label small fw-bold">Country</label>
                                            <div class="dropdown multiselect-dropdown" data-filter="countries" id="countryDropdown">
                                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                    <span class="dropdown-label">All Countries</span>
                                                </button>
                                                <div class="dropdown-menu w-100 p-2" style="max-height: 280px; overflow-y: auto;">
                                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                    </div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="UK" id="ctry1"><label class="form-check-label small" for="ctry1">UK</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="US" id="ctry2"><label class="form-check-label small" for="ctry2">US</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="DE" id="ctry3"><label class="form-check-label small" for="ctry3">DE</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="FR" id="ctry4"><label class="form-check-label small" for="ctry4">FR</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="ES" id="ctry5"><label class="form-check-label small" for="ctry5">ES</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="IT" id="ctry6"><label class="form-check-label small" for="ctry6">IT</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="NL" id="ctry7"><label class="form-check-label small" for="ctry7">NL</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="IE" id="ctry8"><label class="form-check-label small" for="ctry8">IE</label></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="form-label small fw-bold">Message Type</label>
                                            <div class="dropdown multiselect-dropdown" data-filter="msgTypes" id="msgTypeDropdown">
                                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                    <span class="dropdown-label">All Types</span>
                                                </button>
                                                <div class="dropdown-menu w-100 p-2">
                                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                    </div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="SMS" id="mt1"><label class="form-check-label small" for="mt1">SMS</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="MMS" id="mt2"><label class="form-check-label small" for="mt2">MMS</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="RCS" id="mt3"><label class="form-check-label small" for="mt3">RCS</label></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-4 d-flex gap-2 align-items-end">
                                            <button type="button" class="btn btn-sm text-white" id="applyFiltersBtn" style="background-color: var(--admin-primary, #1e3a5f); border-color: var(--admin-primary, #1e3a5f);">
                                                <i class="fas fa-check me-1"></i> Apply
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFiltersBtn">
                                                <i class="fas fa-undo me-1"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2" id="activeFilters"></div>
                                </div>
                            </div>
                        </div>

                        <div class="finance-data-table-wrapper">
                            <div id="tableContainer" class="table-responsive">
                                <table id="supplierDataTable" class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th data-sort="supplier">Supplier <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="country">Country <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="routeType">Route Type <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="sent" class="text-end">Messages Sent <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="delivered" class="text-end">Delivered <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="failed" class="text-end">Failed <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="deliveryRate" class="text-end">Delivery % <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="cost" class="text-end">Cost (&pound;) <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="avgCost" class="text-end">Avg Cost/Msg <i class="fas fa-sort sort-icon"></i></th>
                                            <th data-sort="lastUpdated">Last Updated <i class="fas fa-sort sort-icon"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="supplierTableBody">
                                    </tbody>
                                </table>
                            </div>

                            <div class="finance-data-footer border-top p-2 d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    Showing <span id="showingFrom">1</span>-<span id="showingTo">10</span> of <span id="totalRows">0</span> rows
                                </div>
                                <div class="table-pagination d-flex gap-1">
                                    <button class="btn btn-outline-secondary btn-sm" id="prevPageBtn" disabled><i class="fas fa-chevron-left"></i></button>
                                    <span class="d-flex align-items-center px-2 small" id="pageIndicator">Page 1</span>
                                    <button class="btn btn-outline-secondary btn-sm" id="nextPageBtn"><i class="fas fa-chevron-right"></i></button>
                                </div>
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
(function() {
    const supplierData = [
        { supplier: 'BT Wholesale', country: 'UK', routeType: 'SMS', sent: 524300, delivered: 512100, failed: 12200, cost: 7340.20, lastUpdated: '2026-02-10 14:30', drillDown: [
            { country: 'UK', routeType: 'Direct', sent: 412000, delivered: 405800, failed: 6200, cost: 5768.00, lastUpdated: '2026-02-10 14:30' },
            { country: 'UK', routeType: 'Wholesale', sent: 112300, delivered: 106300, failed: 6000, cost: 1572.20, lastUpdated: '2026-02-10 14:30' }
        ]},
        { supplier: 'Vodafone', country: 'UK', routeType: 'SMS', sent: 489200, delivered: 479400, failed: 9800, cost: 6848.80, lastUpdated: '2026-02-10 13:45', drillDown: [
            { country: 'UK', routeType: 'Direct', sent: 350000, delivered: 344750, failed: 5250, cost: 4900.00, lastUpdated: '2026-02-10 13:45' },
            { country: 'IE', routeType: 'Roaming', sent: 139200, delivered: 134650, failed: 4550, cost: 1948.80, lastUpdated: '2026-02-10 13:45' }
        ]},
        { supplier: 'Three UK', country: 'UK', routeType: 'SMS', sent: 312450, delivered: 303100, failed: 9350, cost: 4061.85, lastUpdated: '2026-02-10 12:15', drillDown: [
            { country: 'UK', routeType: 'Direct', sent: 280000, delivered: 273000, failed: 7000, cost: 3640.00, lastUpdated: '2026-02-10 12:15' },
            { country: 'UK', routeType: 'MVNO', sent: 32450, delivered: 30100, failed: 2350, cost: 421.85, lastUpdated: '2026-02-10 12:15' }
        ]},
        { supplier: 'O2/Telefonica', country: 'UK', routeType: 'SMS', sent: 298700, delivered: 291200, failed: 7500, cost: 3883.10, lastUpdated: '2026-02-10 14:00', drillDown: [
            { country: 'UK', routeType: 'Direct', sent: 210000, delivered: 206850, failed: 3150, cost: 2730.00, lastUpdated: '2026-02-10 14:00' },
            { country: 'ES', routeType: 'International', sent: 88700, delivered: 84350, failed: 4350, cost: 1153.10, lastUpdated: '2026-02-10 14:00' }
        ]},
        { supplier: 'EE', country: 'UK', routeType: 'SMS', sent: 276800, delivered: 271300, failed: 5500, cost: 3598.40, lastUpdated: '2026-02-10 11:30', drillDown: [
            { country: 'UK', routeType: 'Direct', sent: 276800, delivered: 271300, failed: 5500, cost: 3598.40, lastUpdated: '2026-02-10 11:30' }
        ]},
        { supplier: 'Sinch', country: 'Multi', routeType: 'SMS', sent: 387500, delivered: 374600, failed: 12900, cost: 6200.00, lastUpdated: '2026-02-10 14:15', drillDown: [
            { country: 'US', routeType: 'Direct', sent: 180000, delivered: 175500, failed: 4500, cost: 2880.00, lastUpdated: '2026-02-10 14:15' },
            { country: 'DE', routeType: 'Direct', sent: 95000, delivered: 92150, failed: 2850, cost: 1520.00, lastUpdated: '2026-02-10 14:15' },
            { country: 'FR', routeType: 'Hub', sent: 112500, delivered: 106950, failed: 5550, cost: 1800.00, lastUpdated: '2026-02-10 14:15' }
        ]},
        { supplier: 'Sinch', country: 'Multi', routeType: 'RCS', sent: 45200, delivered: 43400, failed: 1800, cost: 1808.00, lastUpdated: '2026-02-10 14:15', drillDown: [
            { country: 'UK', routeType: 'RCS Agent', sent: 28000, delivered: 27100, failed: 900, cost: 1120.00, lastUpdated: '2026-02-10 14:15' },
            { country: 'DE', routeType: 'RCS Agent', sent: 17200, delivered: 16300, failed: 900, cost: 688.00, lastUpdated: '2026-02-10 14:15' }
        ]},
        { supplier: 'Infobip', country: 'Multi', routeType: 'SMS', sent: 298412, delivered: 289100, failed: 9312, cost: 4774.59, lastUpdated: '2026-02-10 13:00', drillDown: [
            { country: 'UK', routeType: 'Wholesale', sent: 120000, delivered: 117600, failed: 2400, cost: 1920.00, lastUpdated: '2026-02-10 13:00' },
            { country: 'FR', routeType: 'Direct', sent: 85412, delivered: 82500, failed: 2912, cost: 1366.59, lastUpdated: '2026-02-10 13:00' },
            { country: 'ES', routeType: 'Hub', sent: 93000, delivered: 89000, failed: 4000, cost: 1488.00, lastUpdated: '2026-02-10 13:00' }
        ]},
        { supplier: 'Infobip', country: 'Multi', routeType: 'MMS', sent: 52300, delivered: 49800, failed: 2500, cost: 2092.00, lastUpdated: '2026-02-10 13:00', drillDown: [
            { country: 'UK', routeType: 'MMS Direct', sent: 32000, delivered: 30800, failed: 1200, cost: 1280.00, lastUpdated: '2026-02-10 13:00' },
            { country: 'US', routeType: 'MMS Hub', sent: 20300, delivered: 19000, failed: 1300, cost: 812.00, lastUpdated: '2026-02-10 13:00' }
        ]},
        { supplier: 'Twilio', country: 'Multi', routeType: 'SMS', sent: 162700, delivered: 158250, failed: 4450, cost: 1711.51, lastUpdated: '2026-02-10 10:45', drillDown: [
            { country: 'US', routeType: 'Direct', sent: 98000, delivered: 96040, failed: 1960, cost: 1029.00, lastUpdated: '2026-02-10 10:45' },
            { country: 'UK', routeType: 'Hub', sent: 44700, delivered: 43000, failed: 1700, cost: 469.35, lastUpdated: '2026-02-10 10:45' },
            { country: 'NL', routeType: 'Hub', sent: 20000, delivered: 19210, failed: 790, cost: 213.16, lastUpdated: '2026-02-10 10:45' }
        ]}
    ];

    let filteredData = [...supplierData];
    let currentPage = 1;
    const pageSize = 10;
    let sortCol = null;
    let sortDir = 'asc';
    let expandedRows = new Set();

    function formatNumber(n) {
        return n.toLocaleString('en-GB');
    }

    function formatCurrency(n) {
        return '£' + n.toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function calcDeliveryRate(delivered, sent) {
        if (sent === 0) return 0;
        return ((delivered / sent) * 100);
    }

    function deliveryBadge(rate) {
        let cls = 'badge-delivery-good';
        if (rate < 95) cls = 'badge-delivery-warn';
        if (rate < 90) cls = 'badge-delivery-bad';
        return '<span class="badge ' + cls + '">' + rate.toFixed(1) + '%</span>';
    }

    function renderTable() {
        const tbody = document.getElementById('supplierTableBody');
        const searchVal = (document.getElementById('searchInput').value || '').toLowerCase();

        let data = filteredData.filter(function(r) {
            if (!searchVal) return true;
            return (r.supplier + ' ' + r.country + ' ' + r.routeType).toLowerCase().indexOf(searchVal) !== -1;
        });

        if (sortCol) {
            data.sort(function(a, b) {
                let va = getSortVal(a, sortCol);
                let vb = getSortVal(b, sortCol);
                if (typeof va === 'string') va = va.toLowerCase();
                if (typeof vb === 'string') vb = vb.toLowerCase();
                if (va < vb) return sortDir === 'asc' ? -1 : 1;
                if (va > vb) return sortDir === 'asc' ? 1 : -1;
                return 0;
            });
        }

        const totalCount = data.length;
        const totalPages = Math.max(1, Math.ceil(totalCount / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * pageSize;
        const pageData = data.slice(start, start + pageSize);

        let html = '';
        pageData.forEach(function(row, idx) {
            const globalIdx = start + idx;
            const rate = calcDeliveryRate(row.delivered, row.sent);
            const avgCost = row.sent > 0 ? (row.cost / row.sent) : 0;
            const isExpanded = expandedRows.has(globalIdx);
            html += '<tr data-idx="' + globalIdx + '" class="supplier-row">';
            html += '<td class="drill-label"><i class="fas fa-chevron-' + (isExpanded ? 'down' : 'right') + ' me-2 text-muted" style="font-size:0.7rem;"></i>' + row.supplier + '</td>';
            html += '<td>' + row.country + '</td>';
            html += '<td><span class="badge bg-light text-dark border">' + row.routeType + '</span></td>';
            html += '<td class="text-end">' + formatNumber(row.sent) + '</td>';
            html += '<td class="text-end">' + formatNumber(row.delivered) + '</td>';
            html += '<td class="text-end">' + formatNumber(row.failed) + '</td>';
            html += '<td class="text-end">' + deliveryBadge(rate) + '</td>';
            html += '<td class="text-end">' + formatCurrency(row.cost) + '</td>';
            html += '<td class="text-end">' + formatCurrency(avgCost) + '</td>';
            html += '<td><small class="text-muted">' + row.lastUpdated + '</small></td>';
            html += '</tr>';

            if (isExpanded && row.drillDown) {
                row.drillDown.forEach(function(dr) {
                    const drRate = calcDeliveryRate(dr.delivered, dr.sent);
                    const drAvg = dr.sent > 0 ? (dr.cost / dr.sent) : 0;
                    html += '<tr class="drill-row">';
                    html += '<td style="padding-left:2.5rem;"><i class="fas fa-level-up-alt fa-rotate-90 me-2 text-muted" style="font-size:0.65rem;"></i><small>' + row.supplier + '</small></td>';
                    html += '<td>' + dr.country + '</td>';
                    html += '<td><span class="badge bg-light text-dark border">' + dr.routeType + '</span></td>';
                    html += '<td class="text-end">' + formatNumber(dr.sent) + '</td>';
                    html += '<td class="text-end">' + formatNumber(dr.delivered) + '</td>';
                    html += '<td class="text-end">' + formatNumber(dr.failed) + '</td>';
                    html += '<td class="text-end">' + deliveryBadge(drRate) + '</td>';
                    html += '<td class="text-end">' + formatCurrency(dr.cost) + '</td>';
                    html += '<td class="text-end">' + formatCurrency(drAvg) + '</td>';
                    html += '<td><small class="text-muted">' + dr.lastUpdated + '</small></td>';
                    html += '</tr>';
                });
            }
        });

        if (!html) {
            html = '<tr><td colspan="10" class="text-center text-muted py-4">No data matching your filters</td></tr>';
        }

        tbody.innerHTML = html;

        document.getElementById('showingFrom').textContent = totalCount > 0 ? start + 1 : 0;
        document.getElementById('showingTo').textContent = Math.min(start + pageSize, totalCount);
        document.getElementById('totalRows').textContent = totalCount;
        document.getElementById('pageIndicator').textContent = 'Page ' + currentPage + ' of ' + totalPages;
        document.getElementById('prevPageBtn').disabled = currentPage <= 1;
        document.getElementById('nextPageBtn').disabled = currentPage >= totalPages;

        updateKPIs(data);
    }

    function getSortVal(row, col) {
        switch(col) {
            case 'supplier': return row.supplier;
            case 'country': return row.country;
            case 'routeType': return row.routeType;
            case 'sent': return row.sent;
            case 'delivered': return row.delivered;
            case 'failed': return row.failed;
            case 'deliveryRate': return calcDeliveryRate(row.delivered, row.sent);
            case 'cost': return row.cost;
            case 'avgCost': return row.sent > 0 ? row.cost / row.sent : 0;
            case 'lastUpdated': return row.lastUpdated;
            default: return '';
        }
    }

    function updateKPIs(data) {
        let totalSent = 0, totalCost = 0, totalDelivered = 0;
        data.forEach(function(r) {
            totalSent += r.sent;
            totalCost += r.cost;
            totalDelivered += r.delivered;
        });
        const avgCost = totalSent > 0 ? totalCost / totalSent : 0;
        const deliveryRate = totalSent > 0 ? (totalDelivered / totalSent * 100) : 0;

        document.getElementById('kpiTotalMessages').textContent = formatNumber(totalSent);
        document.getElementById('kpiTotalCost').innerHTML = formatCurrency(totalCost);
        document.getElementById('kpiAvgCost').innerHTML = '£' + avgCost.toFixed(4);
        document.getElementById('kpiDeliveryRate').textContent = deliveryRate.toFixed(1) + '%';
    }

    document.getElementById('supplierTableBody').addEventListener('click', function(e) {
        const row = e.target.closest('tr.supplier-row');
        if (!row) return;
        const idx = parseInt(row.getAttribute('data-idx'));
        if (expandedRows.has(idx)) {
            expandedRows.delete(idx);
        } else {
            expandedRows.add(idx);
        }
        renderTable();
    });

    document.querySelectorAll('#supplierDataTable thead th[data-sort]').forEach(function(th) {
        th.addEventListener('click', function() {
            const col = this.getAttribute('data-sort');
            if (sortCol === col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortCol = col;
                sortDir = 'asc';
            }
            document.querySelectorAll('#supplierDataTable thead th').forEach(function(h) {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            this.classList.add(sortDir === 'asc' ? 'sort-asc' : 'sort-desc');
            currentPage = 1;
            renderTable();
        });
    });

    document.getElementById('searchInput').addEventListener('input', function() {
        currentPage = 1;
        renderTable();
    });

    document.getElementById('prevPageBtn').addEventListener('click', function() {
        if (currentPage > 1) { currentPage--; renderTable(); }
    });

    document.getElementById('nextPageBtn').addEventListener('click', function() {
        currentPage++;
        renderTable();
    });

    document.querySelectorAll('.month-preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.month-preset-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            const now = new Date();
            let from, to;
            to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            switch(this.getAttribute('data-range')) {
                case 'this-month':
                    from = new Date(now.getFullYear(), now.getMonth(), 1);
                    break;
                case 'last-month':
                    from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    to = new Date(now.getFullYear(), now.getMonth(), 0);
                    break;
                case 'last-3-months':
                    from = new Date(now.getFullYear(), now.getMonth() - 2, 1);
                    break;
                case 'last-6-months':
                    from = new Date(now.getFullYear(), now.getMonth() - 5, 1);
                    break;
                case 'ytd':
                    from = new Date(now.getFullYear(), 0, 1);
                    break;
            }
            if (from) document.getElementById('dateFrom').value = from.toISOString().split('T')[0];
            if (to) document.getElementById('dateTo').value = to.toISOString().split('T')[0];
        });
    });

    document.querySelectorAll('.multiselect-dropdown').forEach(function(dd) {
        var selectAll = dd.querySelector('.select-all-btn');
        var clearAll = dd.querySelector('.clear-all-btn');
        if (selectAll) {
            selectAll.addEventListener('click', function(e) {
                e.preventDefault();
                dd.querySelectorAll('.form-check-input').forEach(function(cb) { cb.checked = true; });
                updateDropdownLabel(dd);
            });
        }
        if (clearAll) {
            clearAll.addEventListener('click', function(e) {
                e.preventDefault();
                dd.querySelectorAll('.form-check-input').forEach(function(cb) { cb.checked = false; });
                updateDropdownLabel(dd);
            });
        }
        dd.querySelectorAll('.form-check-input').forEach(function(cb) {
            cb.addEventListener('change', function() { updateDropdownLabel(dd); });
        });
    });

    function updateDropdownLabel(dd) {
        var checked = dd.querySelectorAll('.form-check-input:checked');
        var label = dd.querySelector('.dropdown-label');
        var total = dd.querySelectorAll('.form-check-input').length;
        if (checked.length === 0 || checked.length === total) {
            var filterName = dd.getAttribute('data-filter');
            var defaults = { suppliers: 'All Suppliers', countries: 'All Countries', msgTypes: 'All Types' };
            label.textContent = defaults[filterName] || 'All';
        } else if (checked.length === 1) {
            label.textContent = checked[0].nextElementSibling.textContent;
        } else {
            label.innerHTML = checked.length + ' selected <span class="selected-count">' + checked.length + '</span>';
        }
    }

    document.getElementById('applyFiltersBtn').addEventListener('click', function() {
        applyFilters();
    });

    document.getElementById('resetFiltersBtn').addEventListener('click', function() {
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.querySelectorAll('.month-preset-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.month-preset-btn[data-range="this-month"]').forEach(function(b) { b.classList.add('active'); });
        document.querySelectorAll('.multiselect-dropdown .form-check-input').forEach(function(cb) { cb.checked = false; });
        document.querySelectorAll('.multiselect-dropdown').forEach(function(dd) { updateDropdownLabel(dd); });
        document.getElementById('activeFilters').innerHTML = '';
        filteredData = [...supplierData];
        currentPage = 1;
        expandedRows.clear();
        renderTable();
    });

    function applyFilters() {
        var selectedSuppliers = getCheckedValues('#supplierDropdown');
        var selectedCountries = getCheckedValues('#countryDropdown');
        var selectedTypes = getCheckedValues('#msgTypeDropdown');

        filteredData = supplierData.filter(function(row) {
            if (selectedSuppliers.length && selectedSuppliers.indexOf(row.supplier) === -1) return false;
            if (selectedCountries.length) {
                var match = row.country === 'Multi' || selectedCountries.indexOf(row.country) !== -1;
                if (!match) return false;
            }
            if (selectedTypes.length && selectedTypes.indexOf(row.routeType) === -1) return false;
            return true;
        });

        var chipsHtml = '';
        selectedSuppliers.forEach(function(s) { chipsHtml += '<span class="filter-chip">' + s + ' <span class="remove-chip" data-filter="suppliers" data-val="' + s + '">&times;</span></span>'; });
        selectedCountries.forEach(function(s) { chipsHtml += '<span class="filter-chip">' + s + ' <span class="remove-chip" data-filter="countries" data-val="' + s + '">&times;</span></span>'; });
        selectedTypes.forEach(function(s) { chipsHtml += '<span class="filter-chip">' + s + ' <span class="remove-chip" data-filter="msgTypes" data-val="' + s + '">&times;</span></span>'; });
        document.getElementById('activeFilters').innerHTML = chipsHtml;

        currentPage = 1;
        expandedRows.clear();
        renderTable();
    }

    document.getElementById('activeFilters').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-chip')) {
            var val = e.target.getAttribute('data-val');
            var filterId = e.target.getAttribute('data-filter');
            var map = { suppliers: '#supplierDropdown', countries: '#countryDropdown', msgTypes: '#msgTypeDropdown' };
            var dd = document.querySelector(map[filterId]);
            if (dd) {
                dd.querySelectorAll('.form-check-input').forEach(function(cb) {
                    if (cb.value === val) cb.checked = false;
                });
                updateDropdownLabel(dd);
            }
            applyFilters();
        }
    });

    function getCheckedValues(selector) {
        var vals = [];
        document.querySelectorAll(selector + ' .form-check-input:checked').forEach(function(cb) {
            vals.push(cb.value);
        });
        return vals;
    }

    document.getElementById('exportCsvBtn').addEventListener('click', function() {
        var headers = ['Supplier','Country','Route Type','Messages Sent','Delivered','Failed','Delivery Rate %','Cost (£)','Avg Cost/Msg','Last Updated'];
        var rows = [headers.join(',')];
        filteredData.forEach(function(r) {
            var rate = calcDeliveryRate(r.delivered, r.sent);
            var avg = r.sent > 0 ? (r.cost / r.sent) : 0;
            rows.push([r.supplier, r.country, r.routeType, r.sent, r.delivered, r.failed, rate.toFixed(1), r.cost.toFixed(2), avg.toFixed(4), r.lastUpdated].join(','));
        });
        var csv = rows.join('\n');
        var blob = new Blob([csv], { type: 'text/csv' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'supplier_report_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    });

    var presetBtn = document.querySelector('.month-preset-btn[data-range="this-month"]');
    if (presetBtn) presetBtn.click();
    renderTable();
})();
</script>
@endpush

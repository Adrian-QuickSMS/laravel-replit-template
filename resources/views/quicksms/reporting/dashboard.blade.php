@extends('layouts.quicksms')

@section('title', 'Reporting Dashboard')

@push('styles')
<style>
/* Fixed viewport layout - page does not scroll */
.qs-reporting-dashboard {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 80px);
    overflow: hidden;
}

/* Ensure KPI tiles show badges without clipping */
.qs-tile .widget-stat .media-body {
    overflow: visible;
}
.qs-tile .widget-stat .media-body h4 {
    overflow: visible;
    flex-wrap: wrap;
}

.qs-reporting-dashboard .qs-filter-section {
    flex-shrink: 0;
}

.qs-reporting-dashboard .qs-dashboard-scroll-container {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 1rem;
}

.chart-placeholder {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Fillow-style Loading Skeletons */
.qs-skeleton {
    animation: qs-skeleton-pulse 1.5s ease-in-out infinite;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    border-radius: 4px;
}

@keyframes qs-skeleton-pulse {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.qs-skeleton-text {
    height: 1rem;
    margin-bottom: 0.5rem;
}

.qs-skeleton-text-sm {
    height: 0.75rem;
    width: 60%;
}

.qs-skeleton-h4 {
    height: 1.5rem;
    width: 80%;
    margin-bottom: 0.25rem;
}

.qs-skeleton-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
}

.qs-skeleton-chart {
    height: 200px;
    width: 100%;
}

.qs-skeleton-bar {
    height: 20px;
    margin-bottom: 8px;
}

.qs-skeleton-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.qs-skeleton-cell {
    height: 1rem;
    flex: 1;
}

/* Error State */
.qs-error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
    color: #dc3545;
}

.qs-error-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.qs-error-state .retry-btn {
    margin-top: 0.5rem;
}

/* Loading overlay for tiles */
.qs-tile-loading .card-body {
    position: relative;
}

.qs-tile-loading .card-body::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    z-index: 10;
}

/* Drill-through clickable elements */
.cursor-pointer {
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.cursor-pointer:hover {
    background-color: rgba(var(--primary-rgb), 0.05) !important;
}

table .cursor-pointer:hover {
    background-color: rgba(var(--primary-rgb), 0.08) !important;
}

#topSenderIdsTableBody tr:hover,
#failureReasonsTableBody tr:hover {
    background-color: rgba(114, 46, 209, 0.08);
}

/* Fillow-style Tooltips */
.qs-tooltip {
    cursor: help;
    font-size: 0.75rem;
}

.qs-tooltip:hover {
    color: var(--primary) !important;
}

/* Tooltip styling overrides for Fillow consistency */
[data-bs-toggle="tooltip"] {
    cursor: help;
}

/* Individual tile table scroll containers */
.qs-tile .table-scroll-container {
    max-height: 220px;
    overflow-y: auto;
    overflow-x: hidden;
}

.qs-tile .table-scroll-container::-webkit-scrollbar {
    width: 6px;
}

.qs-tile .table-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.qs-tile .table-scroll-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.qs-tile .table-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Dashboard scroll container styling */
.qs-dashboard-scroll-container::-webkit-scrollbar {
    width: 8px;
}

.qs-dashboard-scroll-container::-webkit-scrollbar-track {
    background: #f8f9fa;
}

.qs-dashboard-scroll-container::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 4px;
}

.qs-dashboard-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #ced4da;
}

.btn-xs {
    padding: 0.15rem 0.5rem;
    font-size: 0.7rem;
}

.date-preset-btn.active {
    background-color: var(--primary) !important;
    color: white !important;
    border-color: var(--primary) !important;
}

.multiselect-dropdown .dropdown-menu {
    max-height: 200px;
    overflow-y: auto;
}

.qs-dashboard-grid {
    display: grid !important;
    grid-template-columns: repeat(12, 1fr) !important;
    gap: 1rem !important;
    width: 100% !important;
}

.qs-dashboard-grid > .qs-tile {
    float: none !important;
    width: auto !important;
    display: block !important;
    transition: transform 0.2s, box-shadow 0.2s;
}

.qs-tile.sortable-ghost {
    opacity: 0.4;
}

.qs-tile.sortable-drag {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    transform: rotate(1deg);
}

.qs-tile.sortable-chosen {
    cursor: grabbing;
}

.qs-tile .card,
.qs-tile .widget-stat {
    height: 100%;
    margin-bottom: 0 !important;
    cursor: grab;
}

.qs-tile .card:active,
.qs-tile .widget-stat:active {
    cursor: grabbing;
}

.qs-tile.tile-small { grid-column: span 3; }
.qs-tile.tile-medium { grid-column: span 4; }
.qs-tile.tile-large { grid-column: span 6; }
.qs-tile.tile-xlarge { grid-column: span 8; }
.qs-tile.tile-full { grid-column: span 12; }

@media (max-width: 1200px) {
    .qs-tile.tile-small { grid-column: span 6; }
    .qs-tile.tile-medium { grid-column: span 6; }
    .qs-tile.tile-large { grid-column: span 12; }
    .qs-tile.tile-xlarge { grid-column: span 12; }
}

@media (max-width: 768px) {
    .qs-dashboard-grid {
        grid-template-columns: 1fr !important;
    }
    .qs-tile.tile-small, .qs-tile.tile-medium, .qs-tile.tile-large, .qs-tile.tile-xlarge, .qs-tile.tile-full {
        grid-column: span 1;
    }
}

.resize-handle {
    position: absolute;
    bottom: 0.5rem;
    right: 0.5rem;
}


.tile-drag-handle {
    cursor: grab;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    transition: background-color 0.15s;
}

.tile-drag-handle:hover {
    background-color: rgba(0,0,0,0.05);
}

.tile-drag-handle:active {
    cursor: grabbing;
}

.tile-resize-dropdown .dropdown-menu {
    min-width: auto;
}

/* Filter Panel Styles (matching Message Log) */
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
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
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
<div class="container-fluid qs-reporting-dashboard">
    <div class="qs-filter-section">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reporting') }}">Reporting</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>

        <!-- Dashboard Toolbar -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted" data-requires-admin><i class="fas fa-grip-vertical me-1"></i> Drag tiles to reposition</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel" id="btnToggleFilters">
                            <i class="fas fa-filter me-1"></i> Filters
                            <span class="badge bg-primary ms-1" id="filterCountBadge" style="display: none;">0</span>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="btnResetLayout" data-requires-admin>
                            <i class="fas fa-undo me-1"></i> Reset Layout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collapsible Filters Panel -->
        <div class="collapse mb-3" id="filtersPanel">
            <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                <!-- Row 1: Date Range (with presets) + Sub Account + User -->
                <div class="row g-3 align-items-start">
                    <div class="col-12 col-lg-6">
                        <label class="form-label small fw-bold">Date Range</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                            <span class="text-muted small">to</span>
                            <input type="date" class="form-control form-control-sm" id="filterDateTo">
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="today">Today</button>
                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="yesterday">Yesterday</button>
                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn active" data-preset="7days">Last 7 Days</button>
                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="30days">Last 30 Days</button>
                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="mtd">This Month</button>
                            <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="lastmonth">Last Month</button>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
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
                    <div class="col-6 col-lg-3">
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
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="James Wilson" id="user5"><label class="form-check-label small" for="user5">James Wilson</label></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Row 2: Origin + Group Name + SenderID + Action Buttons -->
                <div class="row g-3 align-items-end mt-2">
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
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Portal" id="originPortal"><label class="form-check-label small" for="originPortal">Portal</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="API" id="originAPI"><label class="form-check-label small" for="originAPI">API</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Email-to-SMS" id="originEmail"><label class="form-check-label small" for="originEmail">Email-to-SMS</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Integration" id="originIntegration"><label class="form-check-label small" for="originIntegration">Integration</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="form-label small fw-bold">Group Name</label>
                        <div class="dropdown multiselect-dropdown" data-filter="groupNames">
                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                <span class="dropdown-label">All Groups</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                </div>
                                <div class="small text-muted mb-1 px-2">Campaigns</div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Summer Sale 2024" id="group1"><label class="form-check-label small" for="group1">Summer Sale 2024</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Welcome Series" id="group2"><label class="form-check-label small" for="group2">Welcome Series</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Black Friday" id="group3"><label class="form-check-label small" for="group3">Black Friday</label></div>
                                <div class="small text-muted mb-1 mt-2 px-2">API Connections</div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Main API" id="group4"><label class="form-check-label small" for="group4">Main API</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Mobile App" id="group5"><label class="form-check-label small" for="group5">Mobile App</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Website Integration" id="group6"><label class="form-check-label small" for="group6">Website Integration</label></div>
                                <div class="small text-muted mb-1 mt-2 px-2">Integrations</div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Zapier Flow 1" id="group7"><label class="form-check-label small" for="group7">Zapier Flow 1</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="HubSpot Workflow" id="group8"><label class="form-check-label small" for="group8">HubSpot Workflow</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="form-label small fw-bold">SenderID</label>
                        <div class="dropdown multiselect-dropdown" data-filter="senderIds">
                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                <span class="dropdown-label">All SenderIDs</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="QuickSMS" id="sender1"><label class="form-check-label small" for="sender1">QuickSMS</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="ALERTS" id="sender2"><label class="form-check-label small" for="sender2">ALERTS</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="PROMO" id="sender3"><label class="form-check-label small" for="sender3">PROMO</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="INFO" id="sender4"><label class="form-check-label small" for="sender4">INFO</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="NOTIFY" id="sender5"><label class="form-check-label small" for="sender5">NOTIFY</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="VERIFY" id="sender6"><label class="form-check-label small" for="sender6">VERIFY</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-primary btn-sm" id="btnApplyFilters">
                                <i class="fas fa-check me-1"></i> Apply Filters
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Filters Chips -->
        <div class="mb-3" id="activeFiltersContainer" style="display: none;">
            <div class="d-flex flex-wrap align-items-center">
                <span class="small text-muted me-2">Active filters:</span>
                <div id="activeFiltersChips"></div>
                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" id="btnClearAllFilters">
                    Clear all
                </button>
            </div>
        </div>
    </div><!-- end qs-filter-section -->
    
    <div class="qs-dashboard-scroll-container">

    <!-- Section 2: Tiles & Charts Grid -->
    <div class="qs-dashboard-grid" id="dashboardGrid">
        
        <!-- ========== ROW 1: KPI Tiles (small) ========== -->
        
        <!-- 1. Total Spend (requires cost permission) -->
        <div class="qs-tile tile-small" data-tile-id="kpi-spend" data-size="small" data-api="kpis" data-requires-cost>
            <div class="widget-stat card" id="kpiSpend">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-primary text-primary">
                            <i class="fas fa-pound-sign"></i>
                        </span>
                        <div class="media-body" id="kpiSpendContent">
                            <div class="qs-skeleton qs-skeleton-text" style="width:70px"></div>
                            <div class="qs-skeleton qs-skeleton-h4" style="width:80px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 2. Messages Sent -->
        <div class="qs-tile tile-small" data-tile-id="kpi-messages-sent" data-size="small" data-api="kpis">
            <div class="widget-stat card" id="kpiMessagesSent">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-success text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </span>
                        <div class="media-body" id="kpiMessagesSentContent">
                            <div class="qs-skeleton qs-skeleton-text" style="width:90px"></div>
                            <div class="qs-skeleton qs-skeleton-h4" style="width:60px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 3. Inbound Received -->
        <div class="qs-tile tile-small" data-tile-id="kpi-inbound" data-size="small" data-api="kpis">
            <div class="widget-stat card" id="kpiInbound">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-warning text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <div class="media-body" id="kpiInboundContent">
                            <div class="qs-skeleton qs-skeleton-text" style="width:110px"></div>
                            <div class="qs-skeleton qs-skeleton-h4" style="width:50px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 4. Delivery Rate KPI -->
        <div class="qs-tile tile-small" data-tile-id="kpi-delivery-rate" data-size="small" data-api="kpis">
            <div class="widget-stat card" id="kpiDeliveryRate">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-info text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="19" y1="5" x2="5" y2="19"></line>
                                <circle cx="6.5" cy="6.5" r="2.5"></circle>
                                <circle cx="17.5" cy="17.5" r="2.5"></circle>
                            </svg>
                        </span>
                        <div class="media-body" id="kpiDeliveryRateContent">
                            <div class="qs-skeleton qs-skeleton-text" style="width:80px"></div>
                            <div class="qs-skeleton qs-skeleton-h4" style="width:60px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========== ROW 2: Secondary KPIs ========== -->
        
        <!-- 5. RCS Seen Rate (conditional) -->
        <div class="qs-tile tile-small" data-tile-id="kpi-rcs-seen" data-size="small" data-conditional="rcs" data-api="kpis">
            <div class="widget-stat card" id="kpiRcsSeen">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-success text-success">
                            <i class="fas fa-eye"></i>
                        </span>
                        <div class="media-body" id="kpiRcsSeenContent">
                            <div class="qs-skeleton qs-skeleton-text" style="width:90px"></div>
                            <div class="qs-skeleton qs-skeleton-h4" style="width:55px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 6. RCS Penetration -->
        <div class="qs-tile tile-small" data-tile-id="kpi-rcs-penetration" data-size="small" data-api="kpis">
            <div class="widget-stat card" id="kpiRcsPenetration">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-info text-info">
                            <i class="fas fa-chart-pie"></i>
                        </span>
                        <div class="media-body" id="kpiRcsPenetrationContent">
                            <div class="qs-skeleton qs-skeleton-text" style="width:100px"></div>
                            <div class="qs-skeleton qs-skeleton-h4" style="width:55px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 7. Undelivered Messages -->
        <div class="qs-tile tile-small" data-tile-id="kpi-undelivered" data-size="small" data-api="kpis">
            <div class="widget-stat card" id="kpiUndelivered">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-danger text-danger">
                            <i class="fas fa-times-circle"></i>
                        </span>
                        <div class="media-body" id="kpiUndeliveredContent">
                            <div class="qs-skeleton qs-skeleton-text" style="width:120px"></div>
                            <div class="qs-skeleton qs-skeleton-h4" style="width:50px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 8. Opt-out Rate (conditional) -->
        <div class="qs-tile tile-small" data-tile-id="kpi-optout" data-size="small" data-conditional="optout" data-api="kpis">
            <div class="widget-stat card" id="kpiOptout">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-danger text-danger">
                            <i class="fas fa-user-slash"></i>
                        </span>
                        <div class="media-body" id="kpiOptoutContent">
                            <div class="qs-skeleton qs-skeleton-text" style="width:80px"></div>
                            <div class="qs-skeleton qs-skeleton-h4" style="width:50px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========== ROW 3: Charts (medium/large) ========== -->
        
        <!-- 5. Messages Sent Chart (Line Chart with 3 series: Total, SMS, RCS) -->
        <div class="qs-tile tile-xlarge" data-tile-id="chart-volume" data-size="xlarge" data-api="volume">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-0">Messages Sent</h4>
                </div>
                <div class="card-body">
                    <div id="volumeLineChart" class="chart-placeholder">
                        <div class="qs-skeleton qs-skeleton-chart w-100"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========== ROW 3: Performance Charts & Tables ========== -->
        
        <!-- 7. Delivery Status Breakdown (Pie Chart) -->
        <div class="qs-tile tile-medium" data-tile-id="chart-delivery-status" data-size="medium" data-api="delivery-status">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Delivery Status Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="deliveryStatusPieChart" class="chart-placeholder">
                        <div class="qs-skeleton" style="height:180px;width:180px;border-radius:50%;margin:0 auto"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 8. Top 10 Countries (Vertical Bar Chart) -->
        <div class="qs-tile tile-medium" data-tile-id="chart-top-countries" data-size="medium" data-api="top-countries">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Top 10 Countries</h4>
                </div>
                <div class="card-body">
                    <div id="topCountriesBarChart" class="chart-placeholder">
                        <div class="qs-skeleton qs-skeleton-chart w-100"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 9. Top SenderIDs (About Me Style) -->
        <div class="qs-tile tile-medium" data-tile-id="table-top-senderids" data-size="medium" data-api="top-sender-ids">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Top SenderIDs</h4>
                    <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#topSenderIdsModal" title="View Top 10">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
                <div class="card-body pt-3">
                    <div id="topSenderIdsList">
                        <div class="d-flex justify-content-between py-2 border-bottom"><div class="qs-skeleton" style="width:80px;height:14px"></div><div class="qs-skeleton" style="width:50px;height:14px"></div></div>
                        <div class="d-flex justify-content-between py-2 border-bottom"><div class="qs-skeleton" style="width:100px;height:14px"></div><div class="qs-skeleton" style="width:40px;height:14px"></div></div>
                        <div class="d-flex justify-content-between py-2 border-bottom"><div class="qs-skeleton" style="width:70px;height:14px"></div><div class="qs-skeleton" style="width:55px;height:14px"></div></div>
                        <div class="d-flex justify-content-between py-2 border-bottom"><div class="qs-skeleton" style="width:90px;height:14px"></div><div class="qs-skeleton" style="width:45px;height:14px"></div></div>
                        <div class="d-flex justify-content-between py-2"><div class="qs-skeleton" style="width:85px;height:14px"></div><div class="qs-skeleton" style="width:50px;height:14px"></div></div>
                    </div>
                    <div id="topSenderIdsStats" class="border-top mt-3 pt-3 d-none">
                        <div class="d-flex justify-content-between text-center">
                            <div class="flex-fill">
                                <h4 class="mb-0 text-primary" id="senderIdStatSent">-</h4>
                                <small class="text-muted">Messages Sent</small>
                            </div>
                            <div class="flex-fill border-start border-end">
                                <h4 class="mb-0 text-primary" id="senderIdStatDelivered">-</h4>
                                <small class="text-muted">Delivered</small>
                            </div>
                            <div class="flex-fill">
                                <h4 class="mb-0 text-primary" id="senderIdStatRate">-</h4>
                                <small class="text-muted">Delivery Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========== ROW 4: Intelligence Tiles (small) ========== -->
        
        <!-- 10. Peak Sending Time Insight -->
        <div class="qs-tile tile-large" data-tile-id="tile-peak-time" data-size="large" data-api="peak-time">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title"><i class="fas fa-lightbulb text-warning me-2"></i>Peak Sending Time</h4>
                </div>
                <div class="card-body" id="peakTimeContent">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <span class="display-6 text-primary fw-bold">10:00</span>
                            <span class="text-muted">AM</span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small">Most messages sent</p>
                            <strong>Tuesday mornings</strong>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Peak Hour Volume</span>
                            <strong class="text-dark">1,234 messages</strong>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Best Delivery Rate</span>
                            <strong class="text-success">97.2%</strong>
                        </div>
                    </div>
                    <div class="alert alert-light mt-3 mb-0 py-2 px-3 small">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        Consider scheduling campaigns between 9-11 AM for optimal delivery.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 11. Failure Reasons (small table) -->
        <div class="qs-tile tile-large" data-tile-id="table-failure-reasons" data-size="large" data-api="failure-reasons">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Failure Reasons</h4>
                    <span id="failureReasonsBadge" class="badge badge-danger light"><span class="qs-skeleton" style="display:inline-block;width:30px;height:12px"></span></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive table-scroll-container">
                        <table class="table table-sm mb-0">
                            <thead class="table-light sticky-top bg-white">
                                <tr>
                                    <th>Reason</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody id="failureReasonsTableBody">
                                <tr><td colspan="3"><div class="qs-skeleton qs-skeleton-bar"></div></td></tr>
                                <tr><td colspan="3"><div class="qs-skeleton qs-skeleton-bar"></div></td></tr>
                                <tr><td colspan="3"><div class="qs-skeleton qs-skeleton-bar"></div></td></tr>
                                <tr><td colspan="3"><div class="qs-skeleton qs-skeleton-bar"></div></td></tr>
                                <tr><td colspan="3"><div class="qs-skeleton qs-skeleton-bar"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    </div><!-- end qs-dashboard-scroll-container -->

</div>

<!-- Top SenderIDs Modal -->
<div class="modal fade" id="topSenderIdsModal" tabindex="-1" aria-labelledby="topSenderIdsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="topSenderIdsModalLabel">Top 10 SenderIDs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="topSenderIdsModalList">
                    <div class="d-flex justify-content-between py-2 border-bottom"><div class="qs-skeleton" style="width:80px;height:14px"></div><div class="qs-skeleton" style="width:50px;height:14px"></div></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><div class="qs-skeleton" style="width:100px;height:14px"></div><div class="qs-skeleton" style="width:40px;height:14px"></div></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><div class="qs-skeleton" style="width:70px;height:14px"></div><div class="qs-skeleton" style="width:55px;height:14px"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ asset('vendor/apexchart/apexchart.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // Dashboard Grid Layout with SortableJS
    // ========================================
    const STORAGE_KEY = 'quicksms_dashboard_layout';
    const gridEl = document.getElementById('dashboardGrid');
    let sortable = null;
    
    // Default tile order
    const defaultOrder = [
        'kpi-delivery-rate', 'kpi-spend', 'kpi-rcs-seen', 'kpi-optout',
        'chart-volume',
        'chart-delivery-status', 'chart-top-countries', 'table-top-senderids',
        'tile-peak-time', 'table-failure-reasons'
    ];
    
    // Size options for resize
    const sizeClasses = {
        small: 'tile-small',
        medium: 'tile-medium', 
        large: 'tile-large',
        xlarge: 'tile-xlarge',
        full: 'tile-full'
    };
    
    if (gridEl && typeof Sortable !== 'undefined') {
        sortable = Sortable.create(gridEl, {
            animation: 200,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            chosenClass: 'sortable-chosen',
            handle: '.card-header, .widget-stat, .card-body',
            filter: '.no-drag',
            onEnd: function(evt) {
                saveLayout();
                setTimeout(rerenderCharts, 100);
            }
        });
        
        // Load saved layout
        loadLayout();
        
        console.log('[Dashboard] SortableJS initialized with drag & reposition');
    }
    
    function saveLayout() {
        const tiles = gridEl.querySelectorAll('.qs-tile');
        const layout = Array.from(tiles).map(tile => ({
            id: tile.dataset.tileId,
            size: tile.dataset.size
        }));
        
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(layout));
            console.log('[Dashboard] Layout saved');
        } catch (e) {
            console.warn('[Dashboard] Could not save layout:', e);
        }
    }
    
    function loadLayout() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const layout = JSON.parse(saved);
                const fragment = document.createDocumentFragment();
                
                layout.forEach(item => {
                    const tile = gridEl.querySelector(`[data-tile-id="${item.id}"]`);
                    if (tile) {
                        // Apply saved size
                        if (item.size && sizeClasses[item.size]) {
                            Object.values(sizeClasses).forEach(cls => tile.classList.remove(cls));
                            tile.classList.add(sizeClasses[item.size]);
                            tile.dataset.size = item.size;
                        }
                        fragment.appendChild(tile);
                    }
                });
                
                gridEl.appendChild(fragment);
                console.log('[Dashboard] Layout loaded from storage');
            }
        } catch (e) {
            console.warn('[Dashboard] Could not load layout:', e);
        }
    }
    
    function resetLayout() {
        // Reorder tiles to default
        defaultOrder.forEach(id => {
            const tile = gridEl.querySelector(`[data-tile-id="${id}"]`);
            if (tile) {
                gridEl.appendChild(tile);
            }
        });
        
        // Reset sizes to default
        const defaultSizes = {
            'kpi-delivery-rate': 'small', 'kpi-spend': 'small', 'kpi-rcs-seen': 'small', 'kpi-optout': 'small',
            'chart-volume': 'xlarge',
            'chart-delivery-status': 'medium', 'chart-top-countries': 'medium', 'table-top-senderids': 'medium',
            'tile-peak-time': 'large', 'table-failure-reasons': 'large'
        };
        
        Object.entries(defaultSizes).forEach(([id, size]) => {
            const tile = gridEl.querySelector(`[data-tile-id="${id}"]`);
            if (tile) {
                Object.values(sizeClasses).forEach(cls => tile.classList.remove(cls));
                tile.classList.add(sizeClasses[size]);
                tile.dataset.size = size;
            }
        });
        
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (e) {}
        
        setTimeout(rerenderCharts, 100);
        console.log('[Dashboard] Layout reset to default');
    }
    
    // Reset layout button
    document.getElementById('btnResetLayout')?.addEventListener('click', resetLayout);
    
    // Chart instances for re-rendering
    let chartInstances = {};
    
    function rerenderCharts() {
        Object.values(chartInstances).forEach(chart => {
            if (chart && typeof chart.updateOptions === 'function') {
                chart.updateOptions({}, false, true);
            }
        });
    }
    
    // ========================================
    // Role-Based Access Control (Placeholder)
    // ========================================
    // TODO: Replace with backend user role from session/API
    const USER_ROLES = {
        VIEWER: 'viewer',
        ANALYST: 'analyst',
        ADMIN: 'admin'
    };
    
    // Placeholder: Set current user role (will come from backend)
    const currentUserRole = USER_ROLES.ADMIN; // Change to test different roles
    
    const ROLE_PERMISSIONS = {
        [USER_ROLES.VIEWER]: {
            canSeeCost: false,
            canExport: false,
            canManageLayout: false,
            canDrillToExports: false
        },
        [USER_ROLES.ANALYST]: {
            canSeeCost: true,
            canExport: true,
            canManageLayout: false,
            canDrillToExports: true
        },
        [USER_ROLES.ADMIN]: {
            canSeeCost: true,
            canExport: true,
            canManageLayout: true,
            canDrillToExports: true
        }
    };
    
    function hasPermission(permission) {
        return ROLE_PERMISSIONS[currentUserRole]?.[permission] ?? false;
    }
    
    // Apply role-based visibility on page load
    function applyRoleBasedVisibility() {
        // Hide cost-related elements for viewers
        if (!hasPermission('canSeeCost')) {
            document.querySelectorAll('[data-requires-cost]').forEach(el => {
                el.style.display = 'none';
            });
        }
        
        // Hide export buttons for viewers
        if (!hasPermission('canExport')) {
            document.querySelectorAll('[data-requires-export]').forEach(el => {
                el.style.display = 'none';
            });
        }
        
        // Hide layout management for non-admins
        if (!hasPermission('canManageLayout')) {
            document.querySelectorAll('[data-requires-admin]').forEach(el => {
                el.style.display = 'none';
            });
        }
        
        console.log(`[Dashboard] Role: ${currentUserRole}, Permissions:`, ROLE_PERMISSIONS[currentUserRole]);
    }
    
    // ========================================
    // Reporting API Service Layer
    // ========================================
    // TODO: Replace mock API base with production warehouse endpoint
    // Production endpoint: /api/v1/reporting
    const API_BASE = '/api/reporting/dashboard';
    
    /**
     * Reporting Service - Stub functions for backend integration
     * 
     * Each function:
     * - Accepts standard filter parameters
     * - Returns a Promise that resolves with mock data
     * - Is isolated (tiles load independently, no blocking)
     * 
     * TODO: Wire these to real warehouse endpoints when backend is ready
     */
    const ReportingService = {
        
        /**
         * Build query string from filter parameters
         * @param {Object} filters - Filter parameters
         * @returns {string} - URL query string
         */
        buildQueryParams(filters = {}) {
            const params = new URLSearchParams();
            if (filters.dateRange?.from) params.append('date_from', filters.dateRange.from);
            if (filters.dateRange?.to) params.append('date_to', filters.dateRange.to);
            if (filters.subAccount) params.append('sub_account', filters.subAccount);
            if (filters.user) params.append('user', filters.user);
            if (filters.origin) params.append('origin', filters.origin);
            if (filters.groupName) params.append('group_name', filters.groupName);
            if (filters.senderID) params.append('sender_id', filters.senderID);
            return params.toString();
        },
        
        /**
         * GET /reporting/summary - KPI summary data
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/summary
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { deliveryRate, spend, rcsSeenRate, optOutRate }
         */
        async getSummary(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/summary?${queryString}`).then(r => r.json());
            return fetch(`${API_BASE}/kpis?${queryString}`).then(r => r.json());
        },
        
        /**
         * GET /reporting/volume - Message volume over time
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/volume
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { categories, series, totals }
         */
        async getVolume(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/volume?${queryString}`).then(r => r.json());
            return fetch(`${API_BASE}/volume?${queryString}`).then(r => r.json());
        },
        
        /**
         * GET /reporting/status - Delivery status breakdown
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/status
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { delivered, pending, failed, total }
         */
        async getDeliveryStatus(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/status?${queryString}`).then(r => r.json());
            return fetch(`${API_BASE}/delivery-status?${queryString}`).then(r => r.json());
        },
        
        /**
         * GET /reporting/countries - Top countries by volume
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/countries
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { countries, categories, values }
         */
        async getTopCountries(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/countries?${queryString}`).then(r => r.json());
            return fetch(`${API_BASE}/top-countries?${queryString}`).then(r => r.json());
        },
        
        /**
         * GET /reporting/senderids - Top sender IDs
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/senderids
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { senderIds: [{ senderId, messages, delivered, deliveryRate }] }
         */
        async getTopSenderIds(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/senderids?${queryString}`).then(r => r.json());
            return fetch(`${API_BASE}/top-sender-ids?${queryString}`).then(r => r.json());
        },
        
        /**
         * GET /reporting/cost - Cost and spend data
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/cost
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { amount, currency, creditsUsed, isEstimated, vatNote }
         */
        async getCost(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/cost?${queryString}`).then(r => r.json());
            // Currently bundled with KPIs, extract spend portion
            return fetch(`${API_BASE}/kpis?${queryString}`)
                .then(r => r.json())
                .then(data => data.spend);
        },
        
        /**
         * GET /reporting/failure-reasons - Failure breakdown
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/failure-reasons
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { totalFailed, reasons: [{ reason, count, percentage }] }
         */
        async getFailureReasons(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/failure-reasons?${queryString}`).then(r => r.json());
            return fetch(`${API_BASE}/failure-reasons?${queryString}`).then(r => r.json());
        },
        
        /**
         * GET /reporting/opt-outs - Opt-out statistics
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/opt-outs
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { value, optOutCount, hasOptOutData }
         */
        async getOptOuts(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/opt-outs?${queryString}`).then(r => r.json());
            // Currently bundled with KPIs, extract opt-out portion
            return fetch(`${API_BASE}/kpis?${queryString}`)
                .then(r => r.json())
                .then(data => data.optOutRate);
        },
        
        /**
         * GET /reporting/peak-time - Peak sending time insight
         * TODO: Wire to warehouse endpoint: GET /api/v1/reporting/peak-time
         * @param {Object} filters - { dateRange, subAccount, user, origin, groupName, senderID }
         * @returns {Promise<Object>} - { peakHour, peakDay, peakVolumeCount, recommendation }
         */
        async getPeakTime(filters = {}) {
            const queryString = this.buildQueryParams(filters);
            // TODO: Replace with: return fetch(`/api/v1/reporting/peak-time?${queryString}`).then(r => r.json());
            return fetch(`${API_BASE}/peak-time?${queryString}`).then(r => r.json());
        }
    };
    
    /**
     * Get current filter state from UI (multi-select aware)
     * @returns {Object} - Current filter parameters
     */
    function getCurrentFilters() {
        return {
            dateRange: {
                from: document.getElementById('filterDateFrom')?.value || null,
                to: document.getElementById('filterDateTo')?.value || null
            },
            subAccounts: getMultiselectValues('subAccounts'),
            users: getMultiselectValues('users'),
            origins: getMultiselectValues('origins'),
            groupNames: getMultiselectValues('groupNames'),
            senderIds: getMultiselectValues('senderIds')
        };
    }
    
    /**
     * Get selected values from a multiselect dropdown
     * @param {string} filterKey - The data-filter attribute value
     * @returns {Array} - Array of selected values
     */
    function getMultiselectValues(filterKey) {
        const dropdown = document.querySelector(`.multiselect-dropdown[data-filter="${filterKey}"]`);
        if (!dropdown) return [];
        const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }
    
    /**
     * Update multiselect dropdown label to show selected count
     * @param {HTMLElement} dropdown - The dropdown container
     */
    function updateMultiselectLabel(dropdown) {
        const filterKey = dropdown.dataset.filter;
        const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]:checked');
        const labelSpan = dropdown.querySelector('.dropdown-label');
        const defaultLabels = {
            subAccounts: 'All Sub Accounts',
            users: 'All Users',
            origins: 'All Origins',
            groupNames: 'All Groups',
            senderIds: 'All SenderIDs'
        };
        
        if (checkboxes.length === 0) {
            labelSpan.innerHTML = defaultLabels[filterKey] || 'All';
        } else if (checkboxes.length === 1) {
            labelSpan.innerHTML = checkboxes[0].value;
        } else {
            labelSpan.innerHTML = `${checkboxes.length} selected`;
        }
    }
    
    // ========================================
    // Drill-Through Navigation Helpers
    // ========================================
    const ROUTES = {
        messageLog: '{{ route("reporting.message-log") }}',
        campaignHistory: '{{ route("messages.campaign-history") }}',
        optOutList: '{{ route("contacts.opt-out") }}',
        sendMessage: '{{ route("messages.send") }}'
    };
    
    function navigateWithFilters(baseUrl, filters = {}) {
        const params = new URLSearchParams(filters);
        const url = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
        console.log('[Dashboard] Drill-through:', url);
        window.location.href = url;
    }
    
    // Helper to show error state
    function showError(elementId, message = 'Failed to load data') {
        const el = document.getElementById(elementId);
        if (el) {
            el.innerHTML = `
                <div class="qs-error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <span class="small">${message}</span>
                    <button class="btn btn-outline-primary btn-sm retry-btn" onclick="loadDashboardData()">
                        <i class="fas fa-redo me-1"></i>Retry
                    </button>
                </div>
            `;
        }
    }
    
    // Format number with commas
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // Load all dashboard data independently
    async function loadDashboardData() {
        console.log('[Dashboard] Loading data from API...');
        
        // Load each tile independently for better UX
        return Promise.all([
            loadKpis(),
            loadVolumeChart(),
            loadDeliveryStatus(),
            loadTopCountries(),
            loadTopSenderIds(),
            loadPeakTime(),
            loadFailureReasons()
        ]);
    }
    
    // 1-4. KPIs
    async function loadKpis() {
        try {
            const response = await fetch(`${API_BASE}/kpis`);
            if (!response.ok) throw new Error('API error');
            const data = await response.json();
            
            // Helper function for trend badges - always show, even when 0%
            // Colors: cyan (#17a2b8) for positive/good, magenta (#D653C1) for negative/bad
            // InvertColors: for metrics where decrease is good (e.g., Undelivered Messages)
            function getTrendBadge(trend, invertColors = false) {
                const trendValue = trend ?? 0;
                const isPositive = trendValue >= 0;
                // Cyan for good, magenta for bad - inverted for metrics where decrease is good
                const bgColor = invertColors 
                    ? (isPositive ? '#D653C1' : '#17a2b8')  // Inverted: positive=bad(magenta), negative=good(cyan)
                    : (isPositive ? '#17a2b8' : '#D653C1'); // Normal: positive=good(cyan), negative=bad(magenta)
                const sign = trendValue > 0 ? '+' : '';
                return `<span class="badge ms-2" style="font-size: 11px; font-weight: 500; background-color: ${bgColor}; color: white;">${sign}${trendValue}%</span>`;
            }
            
            // Delivery Rate with tooltip and trend pill
            const deliveryTooltip = `Formula: ${data.deliveryRate.formula}\nDelivered: ${formatNumber(data.deliveryRate.delivered)}\nUndelivered: ${formatNumber(data.deliveryRate.undelivered)}\nRejected: ${formatNumber(data.deliveryRate.rejected)}`;
            const deliveryTrendBadge = getTrendBadge(data.deliveryRate.trend);
            document.getElementById('kpiDeliveryRateContent').innerHTML = `
                <p class="mb-1">DELIVERY RATE <i class="fas fa-info-circle text-muted ms-1 qs-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="${deliveryTooltip.replace(/\n/g, '&#10;')}"></i></p>
                <h4 class="mb-0 d-flex align-items-center" style="white-space: nowrap;">${data.deliveryRate.value}%${deliveryTrendBadge}</h4>
            `;
            
            // Spend with status pill (always show - Estimated or Finalised)
            if (hasPermission('canSeeCost')) {
                const statusBadge = data.spend.isEstimated 
                    ? '<span class="badge ms-2" style="font-size: 11px; font-weight: 500; background-color: #FFBF00; color: #333;">Estimated</span>' 
                    : '<span class="badge ms-2" style="font-size: 11px; font-weight: 500; background-color: #09BD3C; color: white;">Finalised</span>';
                document.getElementById('kpiSpendContent').innerHTML = `
                    <p class="mb-1">TOTAL SPEND <i class="fas fa-info-circle text-muted ms-1" style="font-size: 10px;" title="Excludes VAT"></i></p>
                    <h4 class="mb-0 d-flex align-items-center" style="white-space: nowrap;">£${formatNumber(data.spend.amount.toFixed(2))}${statusBadge}</h4>
                `;
            } else {
                document.getElementById('kpiSpendContent').innerHTML = `
                    <p class="mb-1">Credits Used</p>
                    <h4 class="mb-0">${formatNumber(data.spend.creditsUsed)}</h4>
                `;
            }
            
            // RCS Seen Rate (conditional) - only show if read receipts available
            const rcsTile = document.querySelector('[data-conditional="rcs"]');
            if (data.rcsSeenRate.hasRcsData && data.rcsSeenRate.hasReadReceiptSupport) {
                const rcsTooltip = data.rcsSeenRate.tooltip;
                document.getElementById('kpiRcsSeenContent').innerHTML = `
                    <p class="mb-1">RCS SEEN RATE <i class="fas fa-info-circle text-muted ms-1 qs-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="${rcsTooltip}"></i></p>
                    <h4 class="mb-0 d-flex align-items-center">${data.rcsSeenRate.value}% <span class="badge badge-success ms-2" style="font-size: 11px; font-weight: 500;">${formatNumber(data.rcsSeenRate.seenCount)} Seen</span></h4>
                `;
            } else if (rcsTile) {
                rcsTile.style.display = 'none';
            }
            
            // Opt-out Rate (conditional) - clickable to opt-out list
            const optoutTile = document.querySelector('[data-conditional="optout"]');
            if (data.optOutRate.hasOptOutData) {
                document.getElementById('kpiOptoutContent').innerHTML = `
                    <p class="mb-1">OPT-OUT RATE</p>
                    <h4 class="mb-0">${data.optOutRate.value}%</h4>
                    <span class="badge badge-danger" style="font-size: 10px; font-weight: 500;">${formatNumber(data.optOutRate.optOutCount)} Opt-Outs</span>
                `;
                // Make the entire opt-out tile clickable
                const optoutCard = document.getElementById('kpiOptout');
                if (optoutCard) {
                    optoutCard.classList.add('cursor-pointer');
                    optoutCard.title = 'Click to view opt-out list';
                    optoutCard.onclick = () => navigateWithFilters(ROUTES.optOutList);
                }
            } else if (optoutTile) {
                optoutTile.style.display = 'none';
            }
            
            // Messages Sent - green theme with trend pill
            const msgTrendBadge = getTrendBadge(data.messagesSent.trend);
            document.getElementById('kpiMessagesSentContent').innerHTML = `
                <p class="mb-1">MESSAGES SENT</p>
                <h4 class="mb-0 d-flex align-items-center" style="white-space: nowrap;">${formatNumber(data.messagesSent.count)}${msgTrendBadge}</h4>
            `;
            
            // RCS Penetration - blue theme with trend pill
            const rcsPenTrendBadge = getTrendBadge(data.rcsPenetration.trend);
            document.getElementById('kpiRcsPenetrationContent').innerHTML = `
                <p class="mb-1">RCS PENETRATION</p>
                <h4 class="mb-0 d-flex align-items-center" style="white-space: nowrap;">${data.rcsPenetration.percentage}%${rcsPenTrendBadge}</h4>
            `;
            
            // Inbound Received - orange/warning theme with unread count pill
            const unreadBadge = data.inboundReceived.unreadCount > 0 
                ? `<span class="badge badge-warning ms-2" style="font-size: 11px; font-weight: 500;">${data.inboundReceived.unreadCount} Unread</span>` 
                : '';
            document.getElementById('kpiInboundContent').innerHTML = `
                <p class="mb-1">INBOUND RECEIVED</p>
                <h4 class="mb-0 d-flex align-items-center" style="white-space: nowrap;">${formatNumber(data.inboundReceived.count)}${unreadBadge}</h4>
            `;
            
            // Undelivered Messages - red theme with trend pill (inverted colors - decrease is good)
            const undelTrendBadge = getTrendBadge(data.undeliveredMessages.trend, true);
            document.getElementById('kpiUndeliveredContent').innerHTML = `
                <p class="mb-1">UNDELIVERED MESSAGES</p>
                <h4 class="mb-0 d-flex align-items-center" style="white-space: nowrap;">${formatNumber(data.undeliveredMessages.count)}${undelTrendBadge}</h4>
            `;
            
            console.log('[Dashboard] KPIs loaded');
        } catch (error) {
            console.error('[Dashboard] KPIs error:', error);
            showError('kpiDeliveryRateContent', 'Error');
        }
    }
    
    // 5. Volume Over Time Chart
    async function loadVolumeChart() {
        try {
            const response = await fetch(`${API_BASE}/volume`);
            if (!response.ok) throw new Error('API error');
            const data = await response.json();
            
            const chartEl = document.getElementById('volumeLineChart');
            chartEl.innerHTML = '';
            
            const options = {
                series: data.series,
                chart: { 
                    height: 280, 
                    type: 'line', 
                    toolbar: { show: false },
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            const dateLabel = data.categories[config.dataPointIndex];
                            navigateWithFilters(ROUTES.campaignHistory, { date: dateLabel });
                        }
                    }
                },
                colors: ['#886CC0', '#09BD3C', '#3065D0'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                legend: { position: 'top', horizontalAlign: 'right' },
                xaxis: { categories: data.categories },
                yaxis: { title: { text: 'Messages' } },
                tooltip: { shared: true, intersect: false },
                markers: { size: 5, hover: { size: 7 } }
            };
            
            chartInstances.volume = new ApexCharts(chartEl, options);
            chartInstances.volume.render();
            console.log('[Dashboard] Volume chart loaded (click points to drill-through)');
        } catch (error) {
            console.error('[Dashboard] Volume chart error:', error);
            showError('volumeLineChart', 'Failed to load chart');
        }
    }
    
    // 7. Delivery Status Chart (Pie Chart with 5 statuses - Fillow style)
    async function loadDeliveryStatus() {
        try {
            const response = await fetch(`${API_BASE}/delivery-status`);
            if (!response.ok) throw new Error('API error');
            const data = await response.json();
            
            const chartEl = document.getElementById('deliveryStatusPieChart');
            chartEl.innerHTML = '';
            
            const statusLabels = ['Delivered', 'Pending', 'Undelivered', 'Expired', 'Rejected'];
            const statusValues = ['delivered', 'pending', 'undelivered', 'expired', 'rejected'];
            const options = {
                series: [
                    data.delivered.count, 
                    data.pending.count, 
                    data.undelivered.count,
                    data.expired.count,
                    data.rejected.count
                ],
                chart: { 
                    type: 'pie', 
                    height: 280,
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            const status = statusValues[config.dataPointIndex];
                            navigateWithFilters(ROUTES.messageLog, { status: status });
                        }
                    }
                },
                labels: statusLabels,
                colors: ['#09BD3C', '#886CC0', '#FC2E53', '#D653C1', '#FFBF00'],
                stroke: { show: false, width: 0 },
                legend: { show: false },
                dataLabels: { enabled: false },
                tooltip: {
                    enabled: true,
                    y: {
                        formatter: function(val, opts) {
                            const total = opts.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            const percent = ((val / total) * 100).toFixed(1);
                            return formatNumber(val) + ' (' + percent + '%)';
                        }
                    }
                },
                states: { hover: { filter: { type: 'darken', value: 0.9 } } }
            };
            
            chartInstances.deliveryStatus = new ApexCharts(chartEl, options);
            chartInstances.deliveryStatus.render();
            console.log('[Dashboard] Delivery status loaded');
        } catch (error) {
            console.error('[Dashboard] Delivery status error:', error);
            showError('deliveryStatusPieChart', 'Failed to load');
        }
    }
    
    // 8. Top Countries Chart
    async function loadTopCountries() {
        try {
            const response = await fetch(`${API_BASE}/top-countries`);
            if (!response.ok) throw new Error('API error');
            const data = await response.json();
            
            const chartEl = document.getElementById('topCountriesBarChart');
            chartEl.innerHTML = '';
            
            const options = {
                series: [{ name: 'Messages', data: data.values }],
                chart: { 
                    type: 'bar', 
                    height: 250, 
                    toolbar: { show: false },
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            const countryCode = data.categories[config.dataPointIndex];
                            navigateWithFilters(ROUTES.messageLog, { country: countryCode });
                        }
                    }
                },
                colors: ['var(--primary)'],
                plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '60%' } },
                dataLabels: { enabled: false },
                xaxis: { categories: data.categories, labels: { style: { fontSize: '10px' } } },
                yaxis: { title: { text: 'Messages' } },
                states: { hover: { filter: { type: 'darken', value: 0.9 } } }
            };
            
            chartInstances.topCountries = new ApexCharts(chartEl, options);
            chartInstances.topCountries.render();
            console.log('[Dashboard] Top countries loaded (click bars to drill-through)');
        } catch (error) {
            console.error('[Dashboard] Top countries error:', error);
            showError('topCountriesBarChart', 'Failed to load');
        }
    }
    
    // 9. Top SenderIDs (About Me Style)
    let senderIdsData = [];
    async function loadTopSenderIds() {
        try {
            const response = await fetch(`${API_BASE}/top-sender-ids`);
            if (!response.ok) throw new Error('API error');
            const data = await response.json();
            senderIdsData = data.senderIds;
            
            // Render tile list (top 5)
            renderSenderIdList(document.getElementById('topSenderIdsList'), data.senderIds.slice(0, 5), true);
            
            // Render modal list (top 10)
            renderSenderIdList(document.getElementById('topSenderIdsModalList'), data.senderIds, false);
            
            // Auto-select the first item
            if (data.senderIds.length > 0) {
                window.selectSenderId(0);
            }
            
            console.log('[Dashboard] Top SenderIDs loaded (click rows to view stats)');
        } catch (error) {
            console.error('[Dashboard] Top SenderIDs error:', error);
            document.getElementById('topSenderIdsList').innerHTML = `<div class="text-center text-danger py-3">Failed to load</div>`;
        }
    }
    
    function renderSenderIdList(container, items, isClickable) {
        if (!container) return;
        container.innerHTML = items.map((item, index) => `
            <div class="d-flex justify-content-between py-2 ${index < items.length - 1 ? 'border-bottom' : ''} ${isClickable ? 'cursor-pointer sender-id-row' : ''}" 
                 data-index="${index}" 
                 ${isClickable ? `onclick="window.selectSenderId(${index})"` : ''}
                 style="transition: background-color 0.2s;">
                <span class="fw-bold">${item.senderId}</span>
                <span class="text-muted">${formatNumber(item.messages)}</span>
            </div>
        `).join('');
    }
    
    window.selectSenderId = function(index) {
        const item = senderIdsData[index];
        if (!item) return;
        
        // Update stats
        document.getElementById('senderIdStatSent').textContent = formatNumber(item.messages);
        document.getElementById('senderIdStatDelivered').textContent = formatNumber(item.delivered);
        document.getElementById('senderIdStatRate').textContent = item.deliveryRate + '%';
        
        // Show stats section
        document.getElementById('topSenderIdsStats').classList.remove('d-none');
        
        // Highlight selected row
        document.querySelectorAll('.sender-id-row').forEach((row, i) => {
            if (i === index) {
                row.style.backgroundColor = 'var(--rgba-primary-1)';
            } else {
                row.style.backgroundColor = '';
            }
        });
    };
    
    // 10. Peak Sending Time
    async function loadPeakTime() {
        try {
            const response = await fetch(`${API_BASE}/peak-time`);
            if (!response.ok) throw new Error('API error');
            const data = await response.json();
            
            const [hour, ampm] = data.peakHour.split(':');
            const hourNum = parseInt(hour);
            const ampmLabel = hourNum >= 12 ? 'PM' : 'AM';
            const displayHour = hourNum > 12 ? hourNum - 12 : hourNum;
            
            // Use the formatted peak hour display from API
            const peakHourDisplay = data.peakHourDisplay || `${data.peakHour}–${data.peakHour.replace(':00', ':59')}`;
            
            document.getElementById('peakTimeContent').innerHTML = `
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <span class="badge badge-primary light p-2 fs-6">Peak hour: ${peakHourDisplay}</span>
                    </div>
                    <div>
                        <p class="mb-0 text-muted small">Most messages sent on</p>
                        <strong>${data.peakDay}s</strong>
                    </div>
                </div>
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Peak Hour Volume</span>
                        <strong class="text-dark">${formatNumber(data.peakVolumeCount)} messages</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Best Delivery Rate</span>
                        <strong class="text-success">${data.bestDeliveryRate}%</strong>
                    </div>
                </div>
                <a href="${ROUTES.sendMessage}?schedule_time=${data.peakHour}&schedule_day=${data.peakDay}" class="btn btn-outline-primary btn-sm w-100 mt-3">
                    <i class="fas fa-clock me-1"></i>Schedule at Peak Time
                </a>
            `;
            console.log('[Dashboard] Peak time loaded (click button to schedule)');
        } catch (error) {
            console.error('[Dashboard] Peak time error:', error);
            showError('peakTimeContent', 'Failed to load insight');
        }
    }
    
    // 11. Failure Reasons Table
    async function loadFailureReasons() {
        try {
            const response = await fetch(`${API_BASE}/failure-reasons`);
            if (!response.ok) throw new Error('API error');
            const data = await response.json();
            
            document.getElementById('failureReasonsBadge').textContent = `${data.totalFailed} failed`;
            
            const tbody = document.getElementById('failureReasonsTableBody');
            tbody.innerHTML = data.reasons.map(item => `
                <tr class="cursor-pointer" onclick="navigateWithFilters(ROUTES.messageLog, { status: 'failed', failure_reason: '${item.reason}' })" title="Click to view failed messages: ${item.reason}">
                    <td><i class="fas ${item.icon} ${item.iconColor} me-1"></i> ${item.reason}</td>
                    <td class="text-end">${item.count}</td>
                    <td class="text-end">${item.percentage}%</td>
                </tr>
            `).join('');
            console.log('[Dashboard] Failure reasons loaded (click rows to drill-through)');
        } catch (error) {
            console.error('[Dashboard] Failure reasons error:', error);
            document.getElementById('failureReasonsTableBody').innerHTML = `<tr><td colspan="3" class="text-center text-danger">Failed to load</td></tr>`;
        }
    }
    
    // Initialize Bootstrap tooltips after content loads
    function initTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(el => {
            new bootstrap.Tooltip(el, {
                html: true,
                trigger: 'hover focus'
            });
        });
    }
    
    // Initialize dashboard data load
    if (typeof ApexCharts !== 'undefined') {
        // Apply role-based visibility first
        applyRoleBasedVisibility();
        
        // Disable drag for non-admins
        if (!hasPermission('canManageLayout') && typeof sortable !== 'undefined' && sortable) {
            sortable.option('disabled', true);
        }
        
        loadDashboardData().then(() => {
            setTimeout(initTooltips, 500); // Initialize tooltips after content renders
        });
    } else {
        console.error('[Dashboard] ApexCharts not loaded');
    }
    
    // Re-initialize tooltips periodically for dynamically loaded content
    setInterval(initTooltips, 2000);
    
    // ========================================
    // Filter State Model (multi-select aware)
    // ========================================
    const filterState = {
        dateFrom: null,
        dateTo: null,
        datePreset: '7days',
        subAccounts: [],
        users: [],
        origins: [],
        groupNames: [],
        senderIds: []
    };
    
    const pendingFilters = JSON.parse(JSON.stringify(filterState));
    
    // Default labels for multiselect dropdowns
    const defaultLabels = {
        subAccounts: 'All Sub Accounts',
        users: 'All Users',
        origins: 'All Origins',
        groupNames: 'All Groups',
        senderIds: 'All SenderIDs'
    };
    
    // ========================================
    // Date Preset Helpers
    // ========================================
    function getDateRange(preset) {
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        let from, to;
        
        switch(preset) {
            case 'today':
                from = new Date(today);
                to = new Date(today);
                to.setHours(23, 59, 59);
                break;
            case 'yesterday':
                from = new Date(today);
                from.setDate(from.getDate() - 1);
                to = new Date(from);
                to.setHours(23, 59, 59);
                break;
            case '7days':
                from = new Date(today);
                from.setDate(from.getDate() - 6);
                to = new Date(today);
                to.setHours(23, 59, 59);
                break;
            case '30days':
                from = new Date(today);
                from.setDate(from.getDate() - 29);
                to = new Date(today);
                to.setHours(23, 59, 59);
                break;
            case 'mtd':
                from = new Date(today.getFullYear(), today.getMonth(), 1);
                to = new Date(today);
                to.setHours(23, 59, 59);
                break;
            case 'custom':
            default:
                return { from: null, to: null };
        }
        return { from, to };
    }
    
    function formatDatetimeLocal(date) {
        if (!date) return '';
        const pad = n => n.toString().padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }
    
    function setDateInputs(preset) {
        const range = getDateRange(preset);
        document.getElementById('filterDateFrom').value = formatDatetimeLocal(range.from);
        document.getElementById('filterDateTo').value = formatDatetimeLocal(range.to);
        pendingFilters.dateFrom = range.from;
        pendingFilters.dateTo = range.to;
        pendingFilters.datePreset = preset;
    }
    
    // Initialize with Last 7 Days
    setDateInputs('7days');
    
    // ========================================
    // Date Preset Buttons
    // ========================================
    document.querySelectorAll('.date-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            setDateInputs(this.dataset.preset);
        });
    });
    
    // Custom date input changes
    document.getElementById('filterDateFrom')?.addEventListener('change', function() {
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('[data-preset="custom"]')?.classList.add('active');
        pendingFilters.dateFrom = this.value ? new Date(this.value) : null;
        pendingFilters.datePreset = 'custom';
    });
    
    document.getElementById('filterDateTo')?.addEventListener('change', function() {
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('[data-preset="custom"]')?.classList.add('active');
        pendingFilters.dateTo = this.value ? new Date(this.value) : null;
        pendingFilters.datePreset = 'custom';
    });
    
    // ========================================
    // (Legacy filter code removed - now using multi-select dropdowns)
    // ========================================
    
    // ========================================
    // Active Filter Chips
    // ========================================
    function renderFilterChips() {
        const container = document.getElementById('activeFiltersChips');
        const wrapper = document.getElementById('activeFiltersContainer');
        const badge = document.getElementById('filterCountBadge');
        if (!container || !wrapper) return;
        
        container.innerHTML = '';
        const chips = [];
        
        // Date range chip
        if (filterState.datePreset && filterState.datePreset !== 'custom') {
            const presetLabels = {
                'today': 'Today',
                'yesterday': 'Yesterday',
                '7days': 'Last 7 Days',
                '30days': 'Last 30 Days',
                'mtd': 'This Month',
                'lastmonth': 'Last Month'
            };
            chips.push({ key: 'dateRange', label: presetLabels[filterState.datePreset] || filterState.datePreset, type: 'date' });
        } else if (filterState.dateFrom || filterState.dateTo) {
            chips.push({ key: 'dateRange', label: 'Custom Date Range', type: 'date' });
        }
        
        // Multi-select chips
        if (filterState.subAccounts && filterState.subAccounts.length > 0) {
            if (filterState.subAccounts.length <= 2) {
                chips.push({ key: 'subAccounts', label: `Sub: ${filterState.subAccounts.join(', ')}` });
            } else {
                chips.push({ key: 'subAccounts', label: `Sub: ${filterState.subAccounts.length} selected` });
            }
        }
        if (filterState.users && filterState.users.length > 0) {
            if (filterState.users.length <= 2) {
                chips.push({ key: 'users', label: `User: ${filterState.users.join(', ')}` });
            } else {
                chips.push({ key: 'users', label: `User: ${filterState.users.length} selected` });
            }
        }
        if (filterState.origins && filterState.origins.length > 0) {
            chips.push({ key: 'origins', label: `Origin: ${filterState.origins.join(', ')}` });
        }
        if (filterState.groupNames && filterState.groupNames.length > 0) {
            if (filterState.groupNames.length <= 2) {
                chips.push({ key: 'groupNames', label: `Group: ${filterState.groupNames.join(', ')}` });
            } else {
                chips.push({ key: 'groupNames', label: `Group: ${filterState.groupNames.length} selected` });
            }
        }
        if (filterState.senderIds && filterState.senderIds.length > 0) {
            if (filterState.senderIds.length <= 2) {
                chips.push({ key: 'senderIds', label: `SenderID: ${filterState.senderIds.join(', ')}` });
            } else {
                chips.push({ key: 'senderIds', label: `SenderID: ${filterState.senderIds.length} selected` });
            }
        }
        
        // Count non-default filters
        const nonDefaultCount = (filterState.subAccounts?.length || 0) + 
                                (filterState.users?.length || 0) + 
                                (filterState.origins?.length || 0) + 
                                (filterState.groupNames?.length || 0) + 
                                (filterState.senderIds?.length || 0) +
                                (filterState.datePreset !== '7days' ? 1 : 0);
        
        // Update badge
        if (badge) {
            badge.style.display = nonDefaultCount > 0 ? '' : 'none';
            badge.textContent = nonDefaultCount;
        }
        
        // Only show chips if non-default filters are active
        wrapper.style.display = nonDefaultCount > 0 ? '' : 'none';
        
        chips.forEach(chip => {
            const el = document.createElement('span');
            el.className = 'filter-chip';
            el.innerHTML = `${chip.label} <span class="remove-chip" data-filter-key="${chip.key}">&times;</span>`;
            container.appendChild(el);
        });
        
        // Chip removal handlers
        container.querySelectorAll('.remove-chip').forEach(btn => {
            btn.addEventListener('click', function() {
                const key = this.dataset.filterKey;
                removeFilter(key);
            });
        });
    }
    
    function removeFilter(key) {
        switch(key) {
            case 'dateRange':
                setDateInputs('7days');
                document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
                document.querySelector('[data-preset="7days"]')?.classList.add('active');
                filterState.datePreset = '7days';
                pendingFilters.datePreset = '7days';
                break;
            case 'subAccounts':
                filterState.subAccounts = [];
                pendingFilters.subAccounts = [];
                clearMultiselect('subAccounts');
                break;
            case 'users':
                filterState.users = [];
                pendingFilters.users = [];
                clearMultiselect('users');
                break;
            case 'origins':
                filterState.origins = [];
                pendingFilters.origins = [];
                clearMultiselect('origins');
                break;
            case 'groupNames':
                filterState.groupNames = [];
                pendingFilters.groupNames = [];
                clearMultiselect('groupNames');
                break;
            case 'senderIds':
                filterState.senderIds = [];
                pendingFilters.senderIds = [];
                clearMultiselect('senderIds');
                break;
        }
        renderFilterChips();
        console.log('[Dashboard] Filter removed:', key);
    }
    
    function clearMultiselect(filterKey) {
        const dropdown = document.querySelector(`.multiselect-dropdown[data-filter="${filterKey}"]`);
        if (dropdown) {
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            updateMultiselectLabel(dropdown);
        }
    }
    
    // ========================================
    // Apply/Reset Handlers
    // ========================================
    function applyFilters() {
        // Collect values from all multiselect dropdowns
        pendingFilters.subAccounts = getMultiselectValues('subAccounts');
        pendingFilters.users = getMultiselectValues('users');
        pendingFilters.origins = getMultiselectValues('origins');
        pendingFilters.groupNames = getMultiselectValues('groupNames');
        pendingFilters.senderIds = getMultiselectValues('senderIds');
        
        // Copy pending to active
        Object.assign(filterState, JSON.parse(JSON.stringify(pendingFilters)));
        renderFilterChips();
        
        console.log('[Dashboard] Filters applied:', JSON.stringify(filterState, null, 2));
        // TODO: Implement API call to refresh dashboard data with filterState
        // loadDashboardData();
    }
    
    function resetFilters() {
        // Reset date to Last 7 Days
        setDateInputs('7days');
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('[data-preset="7days"]')?.classList.add('active');
        
        // Clear all multiselect dropdowns
        ['subAccounts', 'users', 'origins', 'groupNames', 'senderIds'].forEach(filterKey => {
            clearMultiselect(filterKey);
        });
        
        // Reset pending and active filters
        pendingFilters.subAccounts = [];
        pendingFilters.users = [];
        pendingFilters.origins = [];
        pendingFilters.groupNames = [];
        pendingFilters.senderIds = [];
        pendingFilters.datePreset = '7days';
        
        applyFilters();
        console.log('[Dashboard] Filters reset to defaults');
    }
    
    // ========================================
    // Multiselect Dropdown Event Handlers
    // ========================================
    document.querySelectorAll('.multiselect-dropdown').forEach(dropdown => {
        const filterKey = dropdown.dataset.filter;
        
        // Checkbox change - update label
        dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', () => {
                updateMultiselectLabel(dropdown);
            });
        });
        
        // Select All button
        dropdown.querySelector('.select-all-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
            updateMultiselectLabel(dropdown);
        });
        
        // Clear button
        dropdown.querySelector('.clear-all-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            updateMultiselectLabel(dropdown);
        });
    });
    
    document.getElementById('btnApplyFilters')?.addEventListener('click', applyFilters);
    document.getElementById('btnResetFilters')?.addEventListener('click', resetFilters);
    document.getElementById('btnClearAllFilters')?.addEventListener('click', resetFilters);
});
</script>
@endpush

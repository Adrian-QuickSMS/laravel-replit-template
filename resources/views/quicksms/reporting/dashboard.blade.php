@extends('layouts.quicksms')

@section('title', 'Reporting Dashboard')

@push('styles')
<style>
.chart-placeholder {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reporting') }}">Reporting</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>

    <!-- Section 1: Global Filters Bar -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                    <!-- Row 1: Date Range with presets -->
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
                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn active" data-preset="7days">Last 7 Days</button>
                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="30days">Last 30 Days</button>
                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="mtd">MTD</button>
                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="custom">Custom Range</button>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold">Sub Account</label>
                            <select class="form-select form-select-sm" id="filterSubAccount">
                                <option value="">All Sub Accounts</option>
                                <option value="Main Account">Main Account</option>
                                <option value="Marketing Team">Marketing Team</option>
                                <option value="Support Team">Support Team</option>
                                <option value="Sales Team">Sales Team</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold">User</label>
                            <select class="form-select form-select-sm" id="filterUser">
                                <option value="">All Users</option>
                                <option value="John Smith" data-subaccount="Main Account">John Smith</option>
                                <option value="Sarah Johnson" data-subaccount="Main Account">Sarah Johnson</option>
                                <option value="Mike Williams" data-subaccount="Marketing Team">Mike Williams</option>
                                <option value="Emma Davis" data-subaccount="Support Team">Emma Davis</option>
                                <option value="James Wilson" data-subaccount="Sales Team">James Wilson</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Row 2: Origin, Integration Type, Group Name, SenderID -->
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
                        <div class="col-6 col-md-4 col-lg-2" id="integrationTypeWrapper" style="display: none;">
                            <label class="form-label small fw-bold">Integration Type</label>
                            <select class="form-select form-select-sm" id="filterIntegrationType">
                                <option value="">All Types</option>
                                <option value="Zapier">Zapier</option>
                                <option value="HubSpot">HubSpot</option>
                                <option value="Salesforce">Salesforce</option>
                                <option value="Slack">Slack</option>
                                <option value="Microsoft Teams">Microsoft Teams</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold">Group Name</label>
                            <select class="form-select form-select-sm" id="filterGroupName">
                                <option value="">All Groups</option>
                                <optgroup label="Campaigns">
                                    <option value="Summer Sale 2024">Summer Sale 2024</option>
                                    <option value="Welcome Series">Welcome Series</option>
                                    <option value="Black Friday">Black Friday</option>
                                </optgroup>
                                <optgroup label="API Connections">
                                    <option value="Main API">Main API</option>
                                    <option value="Mobile App">Mobile App</option>
                                    <option value="Website Integration">Website Integration</option>
                                </optgroup>
                                <optgroup label="Email Groups">
                                    <option value="Support Notifications">Support Notifications</option>
                                    <option value="Order Updates">Order Updates</option>
                                </optgroup>
                                <optgroup label="Integrations">
                                    <option value="Zapier Flow 1">Zapier Flow 1</option>
                                    <option value="HubSpot Workflow">HubSpot Workflow</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold">SenderID</label>
                            <input type="text" class="form-control form-control-sm" id="filterSenderId" placeholder="Type to search..." list="senderIdSuggestions" autocomplete="off">
                            <datalist id="senderIdSuggestions">
                                <option value="QuickSMS">
                                <option value="ALERTS">
                                <option value="PROMO">
                                <option value="QuickSMS Brand">
                                <option value="INFO">
                                <option value="NOTIFY">
                                <option value="VERIFY">
                            </datalist>
                        </div>
                        <div class="col-12 col-lg-2">
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-primary btn-sm" id="btnApplyFilters">
                                    <i class="fas fa-check me-1"></i> Apply
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Filters Chips -->
    <div class="row mb-3" id="activeFiltersContainer" style="display: none;">
        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center">
                <span class="small text-muted me-2">Active filters:</span>
                <div id="activeFiltersChips"></div>
                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" id="btnClearAllFilters">
                    Clear all
                </button>
            </div>
        </div>
    </div>

    <!-- Layout Toolbar -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted"><i class="fas fa-grip-vertical me-1"></i> Drag tiles to reposition</span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" id="btnResetLayout">
                        <i class="fas fa-undo me-1"></i> Reset Layout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Tiles & Charts Grid -->
    <div class="qs-dashboard-grid" id="dashboardGrid">
        
        <!-- ========== ROW 1: KPI Tiles (small) ========== -->
        
        <!-- 1. Delivery Rate KPI -->
        <div class="qs-tile tile-small" data-tile-id="kpi-delivery-rate" data-size="small">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-success text-success">
                            <i class="fas fa-percentage"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Delivery Rate</p>
                            <h4 class="mb-0">94.7%</h4>
                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i>+2.3% vs last period</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 2. Spend KPI -->
        <div class="qs-tile tile-small" data-tile-id="kpi-spend" data-size="small">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-primary text-primary">
                            <i class="fas fa-pound-sign"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Total Spend</p>
                            <h4 class="mb-0">£1,247.50</h4>
                            <small class="text-muted">12,475 credits used</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 3. RCS Seen Rate (conditional) -->
        <div class="qs-tile tile-small" data-tile-id="kpi-rcs-seen" data-size="small" data-conditional="rcs">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-info text-info">
                            <i class="fas fa-eye"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">RCS Seen Rate</p>
                            <h4 class="mb-0">78.3%</h4>
                            <small class="text-info"><i class="fas fa-comment-dots me-1"></i>RCS messages only</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 4. Opt-out Rate (conditional) -->
        <div class="qs-tile tile-small" data-tile-id="kpi-optout" data-size="small" data-conditional="optout">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-danger text-danger">
                            <i class="fas fa-user-slash"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Opt-out Rate</p>
                            <h4 class="mb-0">0.8%</h4>
                            <small class="text-muted">42 opt-outs this period</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========== ROW 2: Charts (medium/large) ========== -->
        
        <!-- 5. Volume Over Time (Line Chart) -->
        <div class="qs-tile tile-xlarge" data-tile-id="chart-volume" data-size="xlarge">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Volume Over Time</h4>
                    <div class="card-tabs">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#daily" role="tab">Daily</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#weekly" role="tab">Weekly</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#monthly" role="tab">Monthly</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="daily" role="tabpanel">
                            <div id="volumeLineChart" class="chart-placeholder"></div>
                        </div>
                        <div class="tab-pane fade" id="weekly" role="tabpanel">
                            <div class="chart-placeholder"><div class="text-center text-muted py-5">Weekly View - Coming Soon</div></div>
                        </div>
                        <div class="tab-pane fade" id="monthly" role="tabpanel">
                            <div class="chart-placeholder"><div class="text-center text-muted py-5">Monthly View - Coming Soon</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 6. Channel Split SMS vs RCS (Horizontal Stacked Bar) -->
        <div class="qs-tile tile-medium" data-tile-id="chart-channel-split" data-size="medium">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Channel Split</h4>
                </div>
                <div class="card-body">
                    <div id="channelSplitChart" class="chart-placeholder"></div>
                    <div class="d-flex justify-content-around mt-3 small">
                        <span><i class="fa fa-circle text-secondary me-1"></i> SMS: 8,234</span>
                        <span><i class="fa fa-circle text-info me-1"></i> RCS: 4,241</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========== ROW 3: Performance Charts & Tables ========== -->
        
        <!-- 7. Delivery Status Breakdown (Pie Chart) -->
        <div class="qs-tile tile-medium" data-tile-id="chart-delivery-status" data-size="medium">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Delivery Status Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="deliveryStatusPieChart" class="chart-placeholder"></div>
                    <div class="chart-point mt-2">
                        <ul class="chart-point-list mb-0 small">
                            <li><i class="fa fa-circle text-success me-1"></i> Delivered: 11,823</li>
                            <li><i class="fa fa-circle text-warning me-1"></i> Pending: 412</li>
                            <li><i class="fa fa-circle text-danger me-1"></i> Failed: 240</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 8. Top 10 Countries (Vertical Bar Chart) -->
        <div class="qs-tile tile-medium" data-tile-id="chart-top-countries" data-size="medium">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Top 10 Countries</h4>
                </div>
                <div class="card-body">
                    <div id="topCountriesBarChart" class="chart-placeholder"></div>
                </div>
            </div>
        </div>
        
        <!-- 9. Top SenderIDs (Table) -->
        <div class="qs-tile tile-medium" data-tile-id="table-top-senderids" data-size="medium">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Top SenderIDs</h4>
                    <a href="{{ route('reporting.message-log') }}" class="btn btn-outline-primary btn-sm">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SenderID</th>
                                    <th class="text-end">Messages</th>
                                    <th class="text-end">Delivered</th>
                                    <th class="text-end">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-primary light">PROMO</span></td>
                                    <td class="text-end">3,456</td>
                                    <td class="text-end">3,298</td>
                                    <td class="text-end"><span class="text-success">95.4%</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary light">ALERTS</span></td>
                                    <td class="text-end">2,891</td>
                                    <td class="text-end">2,834</td>
                                    <td class="text-end"><span class="text-success">98.0%</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary light">QuickSMS</span></td>
                                    <td class="text-end">2,234</td>
                                    <td class="text-end">2,089</td>
                                    <td class="text-end"><span class="text-success">93.5%</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary light">INFO</span></td>
                                    <td class="text-end">1,987</td>
                                    <td class="text-end">1,876</td>
                                    <td class="text-end"><span class="text-success">94.4%</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary light">NOTIFY</span></td>
                                    <td class="text-end">1,654</td>
                                    <td class="text-end">1,548</td>
                                    <td class="text-end"><span class="text-warning">93.6%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========== ROW 4: Intelligence Tiles (small) ========== -->
        
        <!-- 10. Peak Sending Time Insight -->
        <div class="qs-tile tile-large" data-tile-id="tile-peak-time" data-size="large">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title"><i class="fas fa-lightbulb text-warning me-2"></i>Peak Sending Time</h4>
                </div>
                <div class="card-body">
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
        <div class="qs-tile tile-large" data-tile-id="table-failure-reasons" data-size="large">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Failure Reasons</h4>
                    <span class="badge badge-danger light">240 failed</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Reason</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-phone-slash text-danger me-1"></i> Invalid Number</td>
                                    <td class="text-end">98</td>
                                    <td class="text-end">40.8%</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-signal text-warning me-1"></i> Network Error</td>
                                    <td class="text-end">62</td>
                                    <td class="text-end">25.8%</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-ban text-secondary me-1"></i> Carrier Rejected</td>
                                    <td class="text-end">45</td>
                                    <td class="text-end">18.8%</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-clock text-info me-1"></i> Timeout</td>
                                    <td class="text-end">23</td>
                                    <td class="text-end">9.6%</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-question-circle text-muted me-1"></i> Other</td>
                                    <td class="text-end">12</td>
                                    <td class="text-end">5.0%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Section 3: Drill-Through Links -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Detailed Reports</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                            <a href="{{ route('reporting.message-log') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-list-alt fa-2x mb-2 d-block"></i>
                                Message Log
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                            <a href="{{ route('reporting.finance-data') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-coins fa-2x mb-2 d-block"></i>
                                Finance Data
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                            <a href="{{ route('reporting.invoices') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                Invoices
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                            <a href="{{ route('reporting.download-area') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-download fa-2x mb-2 d-block"></i>
                                Download Area
                            </a>
                        </div>
                    </div>
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
        'chart-volume', 'chart-channel-split',
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
            'chart-volume': 'xlarge', 'chart-channel-split': 'medium',
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
    
    // Initialize charts with dummy data when ApexCharts is loaded
    if (typeof ApexCharts !== 'undefined') {
        
        // 5. Volume Over Time Line Chart (dummy data)
        const volumeOptions = {
            series: [{
                name: 'SMS',
                data: [1200, 1450, 1320, 1580, 1420, 1680, 1820, 1950, 1780, 2100, 1920, 2250]
            }, {
                name: 'RCS',
                data: [320, 450, 380, 520, 480, 620, 710, 780, 690, 850, 760, 920]
            }],
            chart: {
                height: 280,
                type: 'line',
                toolbar: { show: false }
            },
            colors: ['#6c757d', '#17a2b8'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            xaxis: {
                categories: ['24 Dec', '25 Dec', '26 Dec', '27 Dec', '28 Dec', '29 Dec', '30 Dec']
            },
            yaxis: {
                title: { text: 'Messages' }
            },
            tooltip: {
                shared: true,
                intersect: false
            }
        };
        const volumeChartEl = document.querySelector("#volumeLineChart");
        if (volumeChartEl) {
            volumeChartEl.innerHTML = '';
            chartInstances.volume = new ApexCharts(volumeChartEl, volumeOptions);
            chartInstances.volume.render();
        }

        // 6. Channel Split Horizontal Stacked Bar (dummy data)
        const channelSplitOptions = {
            series: [{
                name: 'SMS',
                data: [8234]
            }, {
                name: 'RCS',
                data: [4241]
            }],
            chart: {
                type: 'bar',
                height: 120,
                stacked: true,
                stackType: '100%',
                toolbar: { show: false }
            },
            colors: ['#6c757d', '#17a2b8'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '60%'
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toFixed(0) + '%';
                }
            },
            xaxis: {
                categories: ['Messages'],
                labels: { show: false }
            },
            yaxis: { labels: { show: false } },
            legend: { show: false },
            grid: { show: false }
        };
        const channelSplitChartEl = document.querySelector("#channelSplitChart");
        if (channelSplitChartEl) {
            channelSplitChartEl.innerHTML = '';
            chartInstances.channelSplit = new ApexCharts(channelSplitChartEl, channelSplitOptions);
            chartInstances.channelSplit.render();
        }

        // 7. Delivery Status Pie Chart (dummy data)
        const deliveryStatusOptions = {
            series: [11823, 412, 240],
            chart: {
                type: 'donut',
                height: 200
            },
            labels: ['Delivered', 'Pending', 'Failed'],
            colors: ['#28a745', '#ffc107', '#dc3545'],
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: () => '12,475'
                            }
                        }
                    }
                }
            }
        };
        const deliveryStatusChartEl = document.querySelector("#deliveryStatusPieChart");
        if (deliveryStatusChartEl) {
            deliveryStatusChartEl.innerHTML = '';
            chartInstances.deliveryStatus = new ApexCharts(deliveryStatusChartEl, deliveryStatusOptions);
            chartInstances.deliveryStatus.render();
        }

        // 8. Top 10 Countries Vertical Bar Chart (dummy data)
        const topCountriesOptions = {
            series: [{
                name: 'Messages',
                data: [4520, 3280, 1820, 1240, 980, 720, 450, 320, 180, 95]
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: { show: false }
            },
            colors: ['var(--primary)'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                    columnWidth: '60%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: ['UK', 'US', 'DE', 'FR', 'IE', 'ES', 'IT', 'NL', 'BE', 'AU'],
                labels: {
                    style: { fontSize: '10px' }
                }
            },
            yaxis: {
                title: { text: 'Messages' }
            }
        };
        const topCountriesChartEl = document.querySelector("#topCountriesBarChart");
        if (topCountriesChartEl) {
            topCountriesChartEl.innerHTML = '';
            chartInstances.topCountries = new ApexCharts(topCountriesChartEl, topCountriesOptions);
            chartInstances.topCountries.render();
        }

        // Legacy charts removed - replaced with new dashboard components
        console.log('[Dashboard] Charts initialized with dummy data');
    }
    
    // ========================================
    // Conditional Tile Visibility
    // ========================================
    // TODO: Backend integration - show/hide conditional tiles based on actual data
    // For now, all conditional tiles are visible with dummy data
    const hasRcsData = true; // Replace with actual check
    const hasOptoutData = true; // Replace with actual check
    
    const rcsTile = document.querySelector('[data-conditional="rcs"]');
    const optoutTile = document.querySelector('[data-conditional="optout"]');
    
    if (rcsTile && !hasRcsData) {
        rcsTile.style.display = 'none';
    }
    if (optoutTile && !hasOptoutData) {
        optoutTile.style.display = 'none';
    }
    
    // ========================================
    // Filter State Model
    // ========================================
    const filterState = {
        dateFrom: null,
        dateTo: null,
        datePreset: '7days',
        subAccount: '',
        user: '',
        origins: [],
        integrationType: '',
        groupName: '',
        senderId: ''
    };
    
    const pendingFilters = { ...filterState };
    
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
    // Sub Account -> User Filtering
    // ========================================
    const userSelect = document.getElementById('filterUser');
    const allUserOptions = userSelect ? Array.from(userSelect.querySelectorAll('option[data-subaccount]')) : [];
    
    document.getElementById('filterSubAccount')?.addEventListener('change', function() {
        const selectedSubAccount = this.value;
        pendingFilters.subAccount = selectedSubAccount;
        
        // Reset user selection
        userSelect.value = '';
        pendingFilters.user = '';
        
        // Filter user options
        allUserOptions.forEach(opt => {
            if (!selectedSubAccount || opt.dataset.subaccount === selectedSubAccount) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    });
    
    document.getElementById('filterUser')?.addEventListener('change', function() {
        pendingFilters.user = this.value;
    });
    
    // ========================================
    // Origin Multi-select with Integration Type Toggle
    // ========================================
    const originDropdown = document.querySelector('[data-filter="origins"]');
    const integrationWrapper = document.getElementById('integrationTypeWrapper');
    
    function updateOriginLabel() {
        const checkboxes = originDropdown.querySelectorAll('input[type="checkbox"]');
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        const label = originDropdown.querySelector('.dropdown-label');
        
        pendingFilters.origins = checked.map(cb => cb.value);
        
        if (checked.length === 0 || checked.length === checkboxes.length) {
            label.textContent = 'All Origins';
        } else if (checked.length === 1) {
            label.textContent = checked[0].value;
        } else {
            label.textContent = `${checked.length} selected`;
        }
        
        // Toggle Integration Type visibility
        const hasIntegration = pendingFilters.origins.includes('Integration');
        integrationWrapper.style.display = hasIntegration ? '' : 'none';
        if (!hasIntegration) {
            document.getElementById('filterIntegrationType').value = '';
            pendingFilters.integrationType = '';
        }
    }
    
    if (originDropdown) {
        originDropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', updateOriginLabel);
        });
        
        originDropdown.querySelector('.select-all-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            originDropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
            updateOriginLabel();
        });
        
        originDropdown.querySelector('.clear-all-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            originDropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            updateOriginLabel();
        });
    }
    
    // ========================================
    // Other Filter Inputs
    // ========================================
    document.getElementById('filterIntegrationType')?.addEventListener('change', function() {
        pendingFilters.integrationType = this.value;
    });
    
    document.getElementById('filterGroupName')?.addEventListener('change', function() {
        pendingFilters.groupName = this.value;
    });
    
    document.getElementById('filterSenderId')?.addEventListener('input', function() {
        pendingFilters.senderId = this.value;
    });
    
    // ========================================
    // Active Filter Chips
    // ========================================
    function renderFilterChips() {
        const container = document.getElementById('activeFiltersChips');
        const wrapper = document.getElementById('activeFiltersContainer');
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
                'mtd': 'MTD'
            };
            chips.push({ key: 'dateRange', label: presetLabels[filterState.datePreset] || filterState.datePreset, type: 'date' });
        } else if (filterState.dateFrom || filterState.dateTo) {
            chips.push({ key: 'dateRange', label: 'Custom Date Range', type: 'date' });
        }
        
        if (filterState.subAccount) chips.push({ key: 'subAccount', label: `Sub: ${filterState.subAccount}` });
        if (filterState.user) chips.push({ key: 'user', label: `User: ${filterState.user}` });
        if (filterState.origins.length > 0 && filterState.origins.length < 4) {
            chips.push({ key: 'origins', label: `Origin: ${filterState.origins.join(', ')}` });
        }
        if (filterState.integrationType) chips.push({ key: 'integrationType', label: `Integration: ${filterState.integrationType}` });
        if (filterState.groupName) chips.push({ key: 'groupName', label: `Group: ${filterState.groupName}` });
        if (filterState.senderId) chips.push({ key: 'senderId', label: `SenderID: ${filterState.senderId}` });
        
        // Only show if non-default filters are active
        const hasNonDefaultFilters = filterState.subAccount || filterState.user || filterState.origins.length > 0 || 
                                     filterState.integrationType || filterState.groupName || filterState.senderId ||
                                     (filterState.datePreset !== '7days');
        
        wrapper.style.display = hasNonDefaultFilters ? '' : 'none';
        
        chips.forEach(chip => {
            const el = document.createElement('span');
            el.className = 'badge me-1 mb-1 d-inline-flex align-items-center';
            el.style.cssText = 'background-color: #7c3aed; color: white; font-weight: 500; padding: 0.35rem 0.65rem;';
            el.innerHTML = `${chip.label} <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.6rem;" data-filter-key="${chip.key}"></button>`;
            container.appendChild(el);
        });
        
        // Chip removal handlers
        container.querySelectorAll('.btn-close').forEach(btn => {
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
                break;
            case 'subAccount':
                document.getElementById('filterSubAccount').value = '';
                pendingFilters.subAccount = '';
                break;
            case 'user':
                document.getElementById('filterUser').value = '';
                pendingFilters.user = '';
                break;
            case 'origins':
                originDropdown?.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                updateOriginLabel();
                break;
            case 'integrationType':
                document.getElementById('filterIntegrationType').value = '';
                pendingFilters.integrationType = '';
                break;
            case 'groupName':
                document.getElementById('filterGroupName').value = '';
                pendingFilters.groupName = '';
                break;
            case 'senderId':
                document.getElementById('filterSenderId').value = '';
                pendingFilters.senderId = '';
                break;
        }
        applyFilters();
    }
    
    // ========================================
    // Apply/Reset Handlers
    // ========================================
    function applyFilters() {
        Object.assign(filterState, { ...pendingFilters });
        renderFilterChips();
        
        console.log('[Dashboard] Filters applied:', JSON.stringify(filterState, null, 2));
        // TODO: Implement API call to refresh dashboard data with filterState
    }
    
    function resetFilters() {
        // Reset to defaults: Last 7 Days + All Sub Accounts
        setDateInputs('7days');
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('[data-preset="7days"]')?.classList.add('active');
        
        document.getElementById('filterSubAccount').value = '';
        document.getElementById('filterUser').value = '';
        originDropdown?.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        updateOriginLabel();
        document.getElementById('filterIntegrationType').value = '';
        document.getElementById('filterGroupName').value = '';
        document.getElementById('filterSenderId').value = '';
        
        // Reset pending filters
        pendingFilters.subAccount = '';
        pendingFilters.user = '';
        pendingFilters.origins = [];
        pendingFilters.integrationType = '';
        pendingFilters.groupName = '';
        pendingFilters.senderId = '';
        pendingFilters.datePreset = '7days';
        
        // Show all users again
        allUserOptions.forEach(opt => opt.style.display = '');
        
        applyFilters();
        console.log('[Dashboard] Filters reset to defaults');
    }
    
    document.getElementById('btnApplyFilters')?.addEventListener('click', applyFilters);
    document.getElementById('btnResetFilters')?.addEventListener('click', resetFilters);
    document.getElementById('btnClearAllFilters')?.addEventListener('click', resetFilters);
});
</script>
@endpush

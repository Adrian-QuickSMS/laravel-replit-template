@extends('layouts.admin')

@section('title', 'Pricing Management')

@push('styles')
<style>
.breadcrumb-item.active {
    color: #1e3a5f !important;
    font-weight: 500;
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.page-header h2 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.page-header p {
    margin: 0;
    color: #6c757d;
}
.pricing-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 1.5rem;
}
.pricing-tab {
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    background: none;
    border-top: none;
    border-left: none;
    border-right: none;
    transition: all 0.2s;
}
.pricing-tab:hover {
    color: #1e3a5f;
}
.pricing-tab.active {
    color: #1e3a5f;
    border-bottom-color: #1e3a5f;
    font-weight: 600;
}
.pricing-tab i {
    margin-right: 0.5rem;
}
.tab-content-panel {
    display: none;
}
.tab-content-panel.active {
    display: block;
}
.impact-banner {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 0.875rem 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.85rem;
    color: #1e3a5f;
}
.impact-banner i {
    font-size: 1.1rem;
    color: #0284c7;
}
.impact-count {
    font-weight: 700;
    color: #1e3a5f;
}
.table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: hidden;
}
.api-table {
    width: 100%;
    margin: 0;
}
.api-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    white-space: nowrap;
}
.api-table tbody tr {
    border-bottom: 1px solid #e9ecef;
}
.api-table tbody tr:last-child {
    border-bottom: none;
}
.api-table tbody tr:hover {
    background: #f8f9fa;
}
.api-table tbody td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    font-size: 0.85rem;
}
.price-cell {
    font-family: 'SF Mono', 'Fira Code', monospace;
    font-weight: 600;
    color: #1e293b;
}
.price-cell .scheduled-indicator {
    color: #f59e0b;
    font-size: 0.7rem;
    margin-left: 0.25rem;
    cursor: help;
}
.bespoke-badge {
    display: inline-block;
    background: #f3f4f6;
    color: #6b7280;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    font-style: italic;
}
.edit-price-btn {
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    color: #64748b;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s;
}
.edit-price-btn:hover {
    border-color: #1e3a5f;
    color: #1e3a5f;
    background: #f0f9ff;
}
.preview-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}
.preview-bar label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
    margin-bottom: 0;
}
.preview-bar input[type="date"] {
    max-width: 200px;
}
.preview-bar .btn-sm {
    white-space: nowrap;
}
.event-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}
.event-status-badge.draft { background: #dbeafe; color: #1e40af; }
.event-status-badge.scheduled { background: #fef3c7; color: #92400e; }
.event-status-badge.applied { background: #d1fae5; color: #065f46; }
.event-status-badge.cancelled { background: #f3f4f6; color: #6b7280; }
.availability-icon {
    font-size: 0.85rem;
}
.availability-icon.yes { color: #059669; }
.availability-icon.no { color: #dc2626; }
.conflict-row {
    background: #fffbeb !important;
    border-left: 3px solid #f59e0b;
}
.event-detail-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.event-detail-card h5 {
    color: #1e3a5f;
    font-weight: 600;
    margin-bottom: 1rem;
}
.event-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}
.event-meta-item label {
    font-size: 0.75rem;
    color: #64748b;
    display: block;
    margin-bottom: 0.25rem;
}
.event-meta-item span {
    font-size: 0.875rem;
    color: #1e293b;
    font-weight: 500;
}
.upcoming-section {
    margin-top: 2rem;
}
.upcoming-section h5 {
    color: #1e3a5f;
    font-weight: 600;
    margin-bottom: 1rem;
}
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}
.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    display: block;
}
.empty-state p {
    font-size: 0.9rem;
    margin: 0;
}
.price-change-arrow {
    color: #94a3b8;
    margin: 0 0.5rem;
}
.card-footer {
    padding: 0.75rem 1rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Management</a></li>
            <li class="breadcrumb-item active">Pricing</li>
        </ol>
    </div>

    <div class="page-header">
        <div>
            <h2><i class="fas fa-tags me-2"></i>Pricing Management</h2>
            <p>Manage service catalogue, tier pricing, scheduled events, and change history</p>
        </div>
    </div>

    <div class="pricing-tabs">
        <button class="pricing-tab active" data-tab="grid" onclick="switchTab('grid')">
            <i class="fas fa-th"></i> Pricing Grid
        </button>
        <button class="pricing-tab" data-tab="events" onclick="switchTab('events')">
            <i class="fas fa-calendar-alt"></i> Pricing Events
        </button>
        <button class="pricing-tab" data-tab="catalogue" onclick="switchTab('catalogue')">
            <i class="fas fa-book"></i> Service Catalogue
        </button>
        <button class="pricing-tab" data-tab="history" onclick="switchTab('history')">
            <i class="fas fa-history"></i> History & Export
        </button>
    </div>

    <div id="tab-grid" class="tab-content-panel active">
        <div class="impact-banner" id="impactBanner" style="display:none;">
            <i class="fas fa-info-circle"></i>
            <span>
                Price changes affect <span class="impact-count" id="starterCount">0</span> Starter and
                <span class="impact-count" id="enterpriseCount">0</span> Enterprise accounts.
                <span class="impact-count" id="bespokeCount">0</span> bespoke accounts are unaffected.
            </span>
        </div>

        <div class="preview-bar">
            <label><i class="fas fa-eye me-1"></i> Preview pricing at date:</label>
            <input type="date" class="form-control form-control-sm" id="previewDate">
            <button class="btn btn-sm" onclick="previewAtDate()" style="background: #1e3a5f; color: white;">
                <i class="fas fa-search me-1"></i> Preview
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="resetPreview()">
                <i class="fas fa-undo me-1"></i> Today
            </button>
            <span class="text-muted small ms-2" id="previewDateLabel"></span>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table api-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Service</th>
                            <th>Unit</th>
                            <th>Format</th>
                            <th>Starter Price</th>
                            <th></th>
                            <th>Enterprise Price</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="pricingGridBody">
                        <tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading pricing grid...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-events" class="tab-content-panel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select form-select-sm" id="eventStatusFilter" onchange="loadEvents()" style="width: 180px;">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="applied">Applied</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <button class="btn btn-sm" onclick="showCreateEventModal()" style="background: #1e3a5f; color: white;">
                <i class="fas fa-plus me-1"></i> Create Pricing Event
            </button>
        </div>

        <div id="eventDetailView" style="display: none;">
            <button class="btn btn-sm btn-outline-secondary mb-3" onclick="hideEventDetail()">
                <i class="fas fa-arrow-left me-1"></i> Back to Events
            </button>
            <div id="eventDetailContent"></div>
        </div>

        <div id="eventListView">
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table api-table mb-0">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Effective Date</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="eventsBody">
                            <tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="eventsPaginationInfo">Loading...</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="eventsPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-catalogue" class="tab-content-panel">
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-sm" onclick="showAddServiceModal()" style="background: #1e3a5f; color: white;">
                <i class="fas fa-plus me-1"></i> Add Service
            </button>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table api-table mb-0">
                    <thead>
                        <tr>
                            <th>Display Name</th>
                            <th>Slug</th>
                            <th>Unit Label</th>
                            <th>Format</th>
                            <th>Starter</th>
                            <th>Enterprise</th>
                            <th>Bespoke Only</th>
                            <th>Active</th>
                            <th>Sort</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="catalogueBody">
                        <tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-history" class="tab-content-panel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <select class="form-select form-select-sm" id="historyServiceFilter" style="width: 180px;">
                    <option value="">All Services</option>
                </select>
                <select class="form-select form-select-sm" id="historyTierFilter" style="width: 150px;">
                    <option value="">All Tiers</option>
                    <option value="starter">Starter</option>
                    <option value="enterprise">Enterprise</option>
                </select>
                <select class="form-select form-select-sm" id="historySourceFilter" style="width: 180px;">
                    <option value="">All Sources</option>
                    <option value="admin">Admin</option>
                    <option value="hubspot">HubSpot</option>
                    <option value="scheduled_event">Scheduled Event</option>
                </select>
                <input type="date" class="form-control form-control-sm" id="historyFromDate" style="width: 160px;" placeholder="From">
                <input type="date" class="form-control form-control-sm" id="historyToDate" style="width: 160px;" placeholder="To">
                <button class="btn btn-sm" onclick="loadHistory()" style="background: #1e3a5f; color: white;">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="resetHistoryFilters()">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
            <a href="/admin/api/pricing/export" class="btn btn-sm btn-outline-secondary" target="_blank">
                <i class="fas fa-download me-1"></i> Export CSV
            </a>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table api-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Service</th>
                            <th>Tier</th>
                            <th>Old Price</th>
                            <th>New Price</th>
                            <th>Source</th>
                            <th>Changed By</th>
                            <th>Reason</th>
                            <th>Conflict</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                        <tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small" id="historyPaginationInfo">Loading...</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="historyPagination"></ul>
                </nav>
            </div>
        </div>

        <div class="upcoming-section">
            <h5><i class="fas fa-clock me-2"></i>Upcoming Scheduled Changes</h5>
            <div id="upcomingContent">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading upcoming changes...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editPriceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Tier Price</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Service</label>
                    <input type="text" class="form-control" id="editPriceService" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tier</label>
                    <input type="text" class="form-control" id="editPriceTier" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Price</label>
                    <input type="text" class="form-control" id="editPriceCurrent" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">New Price (raw value in £) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="editPriceNew" step="0.000001" min="0" placeholder="e.g. 0.0345">
                    <small class="text-muted">Enter the value in pounds (e.g., 0.0345 for 3.45p)</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="editPriceDate">
                    <small class="text-muted">Today = immediate. Future date = scheduled.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason</label>
                    <textarea class="form-control" id="editPriceReason" rows="2" placeholder="Optional reason for the change"></textarea>
                </div>
                <input type="hidden" id="editPriceServiceId">
                <input type="hidden" id="editPriceTierValue">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTierPrice()" style="background: #1e3a5f; border-color: #1e3a5f;">
                    <i class="fas fa-save me-1"></i> Save Price
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Create Pricing Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Event Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="eventName" placeholder="e.g. Q2 2026 Price Adjustment">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Effective Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="eventDate">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea class="form-control" id="eventDescription" rows="2" placeholder="Optional description"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason</label>
                    <textarea class="form-control" id="eventReason" rows="2" placeholder="Optional reason for the changes"></textarea>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold mb-0">Price Changes <span class="text-danger">*</span></label>
                    <button class="btn btn-sm btn-outline-secondary" onclick="addEventItemRow()">
                        <i class="fas fa-plus me-1"></i> Add Item
                    </button>
                </div>
                <div id="eventItemRows"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createEvent()" style="background: #1e3a5f; border-color: #1e3a5f;">
                    <i class="fas fa-save me-1"></i> Create as Draft
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: #fff;">
                <h5 class="modal-title" id="serviceModalTitle"><i class="fas fa-plus-circle me-2"></i>Add Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="serviceSlug" placeholder="e.g. sms_premium" pattern="[a-z0-9_]+">
                        <small class="text-muted">Lowercase letters, numbers, underscores only</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Display Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="serviceDisplayName" placeholder="e.g. Premium SMS">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea class="form-control" id="serviceDescription" rows="2" placeholder="Optional description"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Unit Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="serviceUnitLabel" placeholder="e.g. per message">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Display Format <span class="text-danger">*</span></label>
                        <select class="form-select" id="serviceDisplayFormat">
                            <option value="pence">Pence</option>
                            <option value="pounds">Pounds</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Decimal Places <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="serviceDecimalPlaces" min="0" max="6" value="3">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="servicePerMessage" checked>
                            <label class="form-check-label" for="servicePerMessage">Per Message</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="serviceRecurring">
                            <label class="form-check-label" for="serviceRecurring">Recurring</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="serviceOneOff">
                            <label class="form-check-label" for="serviceOneOff">One-Off</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="serviceStarter" checked>
                            <label class="form-check-label" for="serviceStarter">Available on Starter</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="serviceEnterprise" checked>
                            <label class="form-check-label" for="serviceEnterprise">Available on Enterprise</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="serviceBespoke">
                            <label class="form-check-label" for="serviceBespoke">Bespoke Only</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Sort Order</label>
                    <input type="number" class="form-control" id="serviceSortOrder" value="99" min="0">
                </div>
                <input type="hidden" id="serviceEditId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveService()" style="background: #1e3a5f; border-color: #1e3a5f;">
                    <i class="fas fa-save me-1"></i> Save Service
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var csrfToken = $('meta[name="csrf-token"]').attr('content');
var currentEventsPage = 1;
var currentHistoryPage = 1;
var allServices = [];

function ajaxHeaders() {
    return { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' };
}

function showToast(message, type) {
    if (typeof AdminControlPlane !== 'undefined' && AdminControlPlane.showNotification) {
        AdminControlPlane.showNotification(message, type);
    } else {
        alert(message);
    }
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function switchTab(tab) {
    document.querySelectorAll('.pricing-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.tab-content-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');

    if (tab === 'grid') loadPricingGrid();
    if (tab === 'events') loadEvents();
    if (tab === 'catalogue') loadCatalogue();
    if (tab === 'history') { loadHistory(); loadUpcoming(); loadServiceFilterOptions(); }
}

document.addEventListener('DOMContentLoaded', function() {
    loadPricingGrid();
    loadServicesCache();
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('previewDate').value = today;
    document.getElementById('editPriceDate').value = today;
});

function loadServicesCache() {
    $.ajax({
        url: '/admin/api/pricing/services',
        method: 'GET',
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success) {
                allServices = response.data || [];
            }
        }
    });
}

function formatPrice(rawPrice, displayFormat, decimalPlaces) {
    if (rawPrice === null || rawPrice === undefined) return '—';
    var val = parseFloat(rawPrice);
    if (displayFormat === 'pounds') {
        return '£' + val.toFixed(0);
    }
    var pence = val * 100;
    return pence.toFixed(decimalPlaces) + 'p';
}

function loadPricingGrid(date) {
    var params = {};
    if (date) params.date = date;

    var tbody = document.getElementById('pricingGridBody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading pricing grid...</td></tr>';

    $.ajax({
        url: '/admin/api/pricing/current',
        method: 'GET',
        data: params,
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success) {
                renderPricingGrid(response.data, response.account_counts, response.date);
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load pricing data.</td></tr>';
            }
        },
        error: function() {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load pricing data.</td></tr>';
        }
    });
}

function renderPricingGrid(data, counts, date) {
    if (counts) {
        document.getElementById('starterCount').textContent = counts.starter || 0;
        document.getElementById('enterpriseCount').textContent = counts.enterprise || 0;
        document.getElementById('bespokeCount').textContent = counts.bespoke_unaffected || 0;
        document.getElementById('impactBanner').style.display = 'flex';
    }

    if (date) {
        var d = new Date(date + 'T00:00:00');
        document.getElementById('previewDateLabel').textContent = 'Showing prices as of: ' + d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    var tbody = document.getElementById('pricingGridBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No services found in the pricing grid.</td></tr>';
        return;
    }

    var html = '';
    data.forEach(function(row) {
        var svc = row.service;
        var isBespoke = svc.bespoke_only;
        var displayFormat = svc.display_format;
        var decimalPlaces = svc.decimal_places;

        html += '<tr>';
        html += '<td><strong>' + escapeHtml(svc.display_name) + '</strong></td>';
        html += '<td><span class="text-muted small">' + escapeHtml(svc.unit_label) + '</span></td>';
        html += '<td><span class="text-muted small">' + escapeHtml(displayFormat) + '</span></td>';

        if (isBespoke) {
            html += '<td colspan="4"><span class="bespoke-badge"><i class="fas fa-lock me-1"></i>Bespoke Only</span></td>';
        } else {
            var starterPrice = row.starter;
            var enterprisePrice = row.enterprise;

            html += '<td class="price-cell">';
            if (starterPrice) {
                html += formatPrice(starterPrice.unit_price, displayFormat, decimalPlaces);
                if (starterPrice.valid_from && starterPrice.valid_from > new Date().toISOString().split('T')[0]) {
                    html += ' <i class="fas fa-calendar-alt scheduled-indicator" title="Effective from ' + escapeHtml(starterPrice.valid_from) + '"></i>';
                }
            } else {
                html += '<span class="text-muted">—</span>';
            }
            html += '</td>';
            html += '<td>';
            html += '<button class="edit-price-btn" onclick="openEditPriceModal(' + svc.id + ', \'starter\', \'' + escapeHtml(svc.display_name) + '\', ' + (starterPrice ? '\'' + starterPrice.unit_price + '\'' : 'null') + ', \'' + displayFormat + '\', ' + decimalPlaces + ')">';
            html += '<i class="fas fa-pencil-alt"></i>';
            html += '</button>';
            html += '</td>';

            html += '<td class="price-cell">';
            if (enterprisePrice) {
                html += formatPrice(enterprisePrice.unit_price, displayFormat, decimalPlaces);
                if (enterprisePrice.valid_from && enterprisePrice.valid_from > new Date().toISOString().split('T')[0]) {
                    html += ' <i class="fas fa-calendar-alt scheduled-indicator" title="Effective from ' + escapeHtml(enterprisePrice.valid_from) + '"></i>';
                }
            } else {
                html += '<span class="text-muted">—</span>';
            }
            html += '</td>';
            html += '<td>';
            html += '<button class="edit-price-btn" onclick="openEditPriceModal(' + svc.id + ', \'enterprise\', \'' + escapeHtml(svc.display_name) + '\', ' + (enterprisePrice ? '\'' + enterprisePrice.unit_price + '\'' : 'null') + ', \'' + displayFormat + '\', ' + decimalPlaces + ')">';
            html += '<i class="fas fa-pencil-alt"></i>';
            html += '</button>';
            html += '</td>';
        }

        html += '</tr>';
    });

    tbody.innerHTML = html;
}

function previewAtDate() {
    var date = document.getElementById('previewDate').value;
    if (!date) return;
    loadPricingGrid(date);
}

function resetPreview() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('previewDate').value = today;
    loadPricingGrid();
}

function openEditPriceModal(serviceId, tier, serviceName, currentPrice, displayFormat, decimalPlaces) {
    document.getElementById('editPriceService').value = serviceName;
    document.getElementById('editPriceTier').value = tier.charAt(0).toUpperCase() + tier.slice(1);
    document.getElementById('editPriceCurrent').value = currentPrice !== null ? formatPrice(currentPrice, displayFormat, decimalPlaces) + ' (raw: ' + currentPrice + ')' : 'No price set';
    document.getElementById('editPriceNew').value = '';
    document.getElementById('editPriceDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('editPriceReason').value = '';
    document.getElementById('editPriceServiceId').value = serviceId;
    document.getElementById('editPriceTierValue').value = tier;
    new bootstrap.Modal(document.getElementById('editPriceModal')).show();
}

function saveTierPrice() {
    var serviceId = document.getElementById('editPriceServiceId').value;
    var tier = document.getElementById('editPriceTierValue').value;
    var newPrice = document.getElementById('editPriceNew').value;
    var effectiveFrom = document.getElementById('editPriceDate').value;
    var reason = document.getElementById('editPriceReason').value;

    if (!newPrice || isNaN(newPrice) || parseFloat(newPrice) < 0) {
        showToast('Please enter a valid price.', 'error');
        return;
    }
    if (!effectiveFrom) {
        showToast('Please select an effective date.', 'error');
        return;
    }

    $.ajax({
        url: '/admin/api/pricing/tier-prices',
        method: 'PUT',
        headers: ajaxHeaders(),
        data: JSON.stringify({
            service_catalogue_id: parseInt(serviceId),
            tier: tier,
            unit_price: parseFloat(newPrice),
            effective_from: effectiveFrom,
            reason: reason || null
        }),
        success: function(response) {
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('editPriceModal')).hide();
                showToast(response.message || 'Price updated successfully.', 'success');
                loadPricingGrid();
            } else {
                showToast(response.error || 'Failed to update price.', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to update price.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function loadEvents() {
    var status = document.getElementById('eventStatusFilter').value;
    var params = { page: currentEventsPage, per_page: 20 };
    if (status) params.status = status;

    var tbody = document.getElementById('eventsBody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading events...</td></tr>';
    document.getElementById('eventListView').style.display = 'block';
    document.getElementById('eventDetailView').style.display = 'none';

    $.ajax({
        url: '/admin/api/pricing/events',
        method: 'GET',
        data: params,
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                renderEvents(response.data);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No events found.</td></tr>';
            }
        },
        error: function() {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load events.</td></tr>';
        }
    });
}

function renderEvents(paginator) {
    var items = paginator.data || [];
    var tbody = document.getElementById('eventsBody');

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="empty-state"><i class="fas fa-calendar-times"></i><p>No pricing events found.</p></div></td></tr>';
        document.getElementById('eventsPaginationInfo').textContent = '0 events';
        document.getElementById('eventsPagination').innerHTML = '';
        return;
    }

    var html = '';
    items.forEach(function(evt) {
        var effectiveDate = evt.effective_date ? new Date(evt.effective_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
        var itemCount = evt.items ? evt.items.length : 0;
        var createdBy = evt.created_by_user ? (evt.created_by_user.first_name + ' ' + evt.created_by_user.last_name) : (evt.created_by ? evt.created_by.email || '—' : '—');
        var statusBadge = getEventStatusBadge(evt.status);

        html += '<tr>';
        html += '<td><a href="#" onclick="viewEventDetail(\'' + evt.id + '\'); return false;" style="color: #1e3a5f; font-weight: 500; text-decoration: none;">' + escapeHtml(evt.name) + '</a></td>';
        html += '<td>' + effectiveDate + '</td>';
        html += '<td><span class="badge bg-light text-dark">' + itemCount + ' change' + (itemCount !== 1 ? 's' : '') + '</span></td>';
        html += '<td>' + statusBadge + '</td>';
        html += '<td class="small text-muted">' + escapeHtml(createdBy) + '</td>';
        html += '<td>';
        if (evt.status === 'draft') {
            html += '<button class="btn btn-sm btn-outline-primary me-1" onclick="scheduleEvent(\'' + evt.id + '\')"><i class="fas fa-calendar-check"></i></button>';
            html += '<button class="btn btn-sm btn-outline-danger" onclick="cancelEvent(\'' + evt.id + '\')"><i class="fas fa-times"></i></button>';
        } else if (evt.status === 'scheduled') {
            html += '<button class="btn btn-sm btn-outline-danger" onclick="cancelEvent(\'' + evt.id + '\')"><i class="fas fa-times"></i></button>';
        }
        html += '</td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
    renderGenericPagination(paginator, 'eventsPaginationInfo', 'eventsPagination', function(page) {
        currentEventsPage = page;
        loadEvents();
    });
}

function getEventStatusBadge(status) {
    var map = {
        'draft': { cls: 'draft', icon: 'fa-pencil-alt', label: 'Draft' },
        'scheduled': { cls: 'scheduled', icon: 'fa-clock', label: 'Scheduled' },
        'applied': { cls: 'applied', icon: 'fa-check-circle', label: 'Applied' },
        'cancelled': { cls: 'cancelled', icon: 'fa-times-circle', label: 'Cancelled' }
    };
    var info = map[status] || { cls: 'draft', icon: 'fa-question', label: status };
    return '<span class="event-status-badge ' + info.cls + '"><i class="fas ' + info.icon + '"></i> ' + info.label + '</span>';
}

function viewEventDetail(eventId) {
    document.getElementById('eventListView').style.display = 'none';
    document.getElementById('eventDetailView').style.display = 'block';
    document.getElementById('eventDetailContent').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading event details...</div>';

    $.ajax({
        url: '/admin/api/pricing/events/' + eventId,
        method: 'GET',
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success) {
                renderEventDetail(response.data, response.affected_accounts);
            } else {
                document.getElementById('eventDetailContent').innerHTML = '<div class="text-center py-4 text-danger">Failed to load event details.</div>';
            }
        },
        error: function() {
            document.getElementById('eventDetailContent').innerHTML = '<div class="text-center py-4 text-danger">Failed to load event details.</div>';
        }
    });
}

function renderEventDetail(evt, affected) {
    var effectiveDate = evt.effective_date ? new Date(evt.effective_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
    var statusBadge = getEventStatusBadge(evt.status);
    var createdBy = evt.created_by ? (evt.created_by.email || '—') : '—';

    var html = '<div class="event-detail-card">';
    html += '<div class="d-flex justify-content-between align-items-center mb-3">';
    html += '<h5 class="mb-0">' + escapeHtml(evt.name) + '</h5>';
    html += statusBadge;
    html += '</div>';

    html += '<div class="event-meta">';
    html += '<div class="event-meta-item"><label>Effective Date</label><span>' + effectiveDate + '</span></div>';
    html += '<div class="event-meta-item"><label>Created By</label><span>' + escapeHtml(createdBy) + '</span></div>';
    html += '<div class="event-meta-item"><label>Status</label><span>' + escapeHtml(evt.status) + '</span></div>';
    html += '</div>';

    if (evt.description) {
        html += '<p class="text-muted small">' + escapeHtml(evt.description) + '</p>';
    }
    if (evt.reason) {
        html += '<p class="text-muted small"><strong>Reason:</strong> ' + escapeHtml(evt.reason) + '</p>';
    }

    if (affected) {
        html += '<div class="impact-banner">';
        html += '<i class="fas fa-info-circle"></i><span>';
        if (affected.affected) {
            var parts = [];
            for (var tier in affected.affected) {
                parts.push('<strong>' + affected.affected[tier] + '</strong> ' + tier);
            }
            html += 'Affects ' + parts.join(' and ') + ' accounts.';
        }
        if (affected.bespoke_unaffected) {
            html += ' <strong>' + affected.bespoke_unaffected + '</strong> bespoke accounts are unaffected.';
        }
        html += '</span></div>';
    }

    html += '</div>';

    if (evt.items && evt.items.length > 0) {
        html += '<div class="table-container mt-3">';
        html += '<div class="table-responsive"><table class="table api-table mb-0">';
        html += '<thead><tr><th>Service</th><th>Tier</th><th>Old Price</th><th></th><th>New Price</th></tr></thead>';
        html += '<tbody>';
        evt.items.forEach(function(item) {
            var svcName = item.service ? item.service.display_name : '—';
            var displayFormat = item.service ? item.service.display_format : 'pence';
            var decimals = item.service ? item.service.decimal_places : 3;
            html += '<tr>';
            html += '<td>' + escapeHtml(svcName) + '</td>';
            html += '<td>' + escapeHtml(item.tier ? item.tier.charAt(0).toUpperCase() + item.tier.slice(1) : '') + '</td>';
            html += '<td class="price-cell">' + formatPrice(item.old_price, displayFormat, decimals) + '</td>';
            html += '<td class="price-change-arrow"><i class="fas fa-arrow-right"></i></td>';
            html += '<td class="price-cell">' + formatPrice(item.new_price, displayFormat, decimals) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div></div>';
    }

    if (evt.status === 'draft' || evt.status === 'scheduled') {
        html += '<div class="mt-3 d-flex gap-2">';
        if (evt.status === 'draft') {
            html += '<button class="btn btn-primary btn-sm" onclick="scheduleEvent(\'' + evt.id + '\')" style="background: #1e3a5f; border-color: #1e3a5f;"><i class="fas fa-calendar-check me-1"></i> Schedule</button>';
        }
        html += '<button class="btn btn-outline-danger btn-sm" onclick="cancelEvent(\'' + evt.id + '\')"><i class="fas fa-times me-1"></i> Cancel Event</button>';
        html += '</div>';
    }

    document.getElementById('eventDetailContent').innerHTML = html;
}

function hideEventDetail() {
    document.getElementById('eventDetailView').style.display = 'none';
    document.getElementById('eventListView').style.display = 'block';
}

function showCreateEventModal() {
    document.getElementById('eventName').value = '';
    document.getElementById('eventDate').value = '';
    document.getElementById('eventDescription').value = '';
    document.getElementById('eventReason').value = '';
    document.getElementById('eventItemRows').innerHTML = '';
    addEventItemRow();
    new bootstrap.Modal(document.getElementById('createEventModal')).show();
}

function addEventItemRow() {
    var container = document.getElementById('eventItemRows');
    var idx = container.children.length;

    var serviceOptions = '<option value="">Select service...</option>';
    allServices.forEach(function(svc) {
        if (!svc.bespoke_only) {
            serviceOptions += '<option value="' + svc.id + '">' + escapeHtml(svc.display_name) + '</option>';
        }
    });

    var html = '<div class="row mb-2 align-items-end event-item-row">';
    html += '<div class="col-md-4"><select class="form-select form-select-sm" name="event_service_' + idx + '">' + serviceOptions + '</select></div>';
    html += '<div class="col-md-3"><select class="form-select form-select-sm" name="event_tier_' + idx + '"><option value="starter">Starter</option><option value="enterprise">Enterprise</option></select></div>';
    html += '<div class="col-md-3"><input type="number" class="form-control form-control-sm" name="event_price_' + idx + '" step="0.000001" min="0" placeholder="New price (£)"></div>';
    html += '<div class="col-md-2"><button class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.event-item-row\').remove()"><i class="fas fa-trash"></i></button></div>';
    html += '</div>';

    container.insertAdjacentHTML('beforeend', html);
}

function createEvent() {
    var name = document.getElementById('eventName').value.trim();
    var effectiveDate = document.getElementById('eventDate').value;
    var description = document.getElementById('eventDescription').value.trim();
    var reason = document.getElementById('eventReason').value.trim();

    if (!name) { showToast('Please enter an event name.', 'error'); return; }
    if (!effectiveDate) { showToast('Please select an effective date.', 'error'); return; }

    var items = [];
    var rows = document.querySelectorAll('.event-item-row');
    rows.forEach(function(row, idx) {
        var svcSelect = row.querySelector('[name^="event_service_"]');
        var tierSelect = row.querySelector('[name^="event_tier_"]');
        var priceInput = row.querySelector('[name^="event_price_"]');
        if (svcSelect.value && priceInput.value) {
            items.push({
                service_catalogue_id: parseInt(svcSelect.value),
                tier: tierSelect.value,
                new_price: parseFloat(priceInput.value)
            });
        }
    });

    if (items.length === 0) { showToast('Please add at least one price change.', 'error'); return; }

    $.ajax({
        url: '/admin/api/pricing/events',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({
            name: name,
            effective_date: effectiveDate,
            description: description || null,
            reason: reason || null,
            items: items
        }),
        success: function(response) {
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('createEventModal')).hide();
                showToast(response.message || 'Pricing event created.', 'success');
                loadEvents();
            } else {
                showToast(response.error || 'Failed to create event.', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to create event.';
            try {
                var err = JSON.parse(xhr.responseText);
                msg = err.error || err.message || msg;
            } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function scheduleEvent(eventId) {
    if (!confirm('Schedule this pricing event? Price changes will be applied on the effective date.')) return;

    $.ajax({
        url: '/admin/api/pricing/events/' + eventId + '/schedule',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({}),
        success: function(response) {
            if (response.success) {
                showToast(response.message || 'Event scheduled.', 'success');
                loadEvents();
                hideEventDetail();
            } else {
                showToast(response.error || 'Failed to schedule event.', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to schedule event.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function cancelEvent(eventId) {
    if (!confirm('Cancel this pricing event? Any scheduled price changes will be reverted.')) return;

    $.ajax({
        url: '/admin/api/pricing/events/' + eventId + '/cancel',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({}),
        success: function(response) {
            if (response.success) {
                showToast(response.message || 'Event cancelled.', 'success');
                loadEvents();
                hideEventDetail();
            } else {
                showToast(response.error || 'Failed to cancel event.', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to cancel event.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function loadCatalogue() {
    var tbody = document.getElementById('catalogueBody');
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading service catalogue...</td></tr>';

    $.ajax({
        url: '/admin/api/pricing/services',
        method: 'GET',
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success) {
                renderCatalogue(response.data || []);
            }
        },
        error: function() {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Failed to load catalogue.</td></tr>';
        }
    });
}

function renderCatalogue(services) {
    var tbody = document.getElementById('catalogueBody');
    if (services.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><div class="empty-state"><i class="fas fa-book-open"></i><p>No services in the catalogue.</p></div></td></tr>';
        return;
    }

    var html = '';
    services.forEach(function(svc) {
        html += '<tr>';
        html += '<td><strong>' + escapeHtml(svc.display_name) + '</strong></td>';
        html += '<td><code class="small">' + escapeHtml(svc.slug) + '</code></td>';
        html += '<td class="small">' + escapeHtml(svc.unit_label) + '</td>';
        html += '<td class="small">' + escapeHtml(svc.display_format) + ' / ' + svc.decimal_places + 'dp</td>';
        html += '<td class="text-center"><i class="fas ' + (svc.available_on_starter ? 'fa-check-circle availability-icon yes' : 'fa-times-circle availability-icon no') + '"></i></td>';
        html += '<td class="text-center"><i class="fas ' + (svc.available_on_enterprise ? 'fa-check-circle availability-icon yes' : 'fa-times-circle availability-icon no') + '"></i></td>';
        html += '<td class="text-center"><i class="fas ' + (svc.bespoke_only ? 'fa-check-circle availability-icon yes' : 'fa-times-circle availability-icon no') + '"></i></td>';
        html += '<td class="text-center"><i class="fas ' + (svc.is_active ? 'fa-check-circle availability-icon yes' : 'fa-times-circle availability-icon no') + '"></i></td>';
        html += '<td class="text-center small">' + (svc.sort_order || 0) + '</td>';
        html += '<td><button class="edit-price-btn" onclick="openEditServiceModal(' + svc.id + ')"><i class="fas fa-pencil-alt"></i></button></td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
}

function showAddServiceModal() {
    document.getElementById('serviceModalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Add Service';
    document.getElementById('serviceSlug').value = '';
    document.getElementById('serviceSlug').removeAttribute('readonly');
    document.getElementById('serviceDisplayName').value = '';
    document.getElementById('serviceDescription').value = '';
    document.getElementById('serviceUnitLabel').value = '';
    document.getElementById('serviceDisplayFormat').value = 'pence';
    document.getElementById('serviceDecimalPlaces').value = '3';
    document.getElementById('servicePerMessage').checked = true;
    document.getElementById('serviceRecurring').checked = false;
    document.getElementById('serviceOneOff').checked = false;
    document.getElementById('serviceStarter').checked = true;
    document.getElementById('serviceEnterprise').checked = true;
    document.getElementById('serviceBespoke').checked = false;
    document.getElementById('serviceSortOrder').value = '99';
    document.getElementById('serviceEditId').value = '';
    new bootstrap.Modal(document.getElementById('addServiceModal')).show();
}

function openEditServiceModal(serviceId) {
    var svc = allServices.find(function(s) { return s.id === serviceId; });
    if (!svc) {
        $.ajax({
            url: '/admin/api/pricing/services',
            method: 'GET',
            headers: ajaxHeaders(),
            success: function(response) {
                if (response.success) {
                    allServices = response.data || [];
                    svc = allServices.find(function(s) { return s.id === serviceId; });
                    if (svc) populateEditServiceModal(svc);
                }
            }
        });
        return;
    }
    populateEditServiceModal(svc);
}

function populateEditServiceModal(svc) {
    document.getElementById('serviceModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Service';
    document.getElementById('serviceSlug').value = svc.slug;
    document.getElementById('serviceSlug').setAttribute('readonly', 'readonly');
    document.getElementById('serviceDisplayName').value = svc.display_name;
    document.getElementById('serviceDescription').value = svc.description || '';
    document.getElementById('serviceUnitLabel').value = svc.unit_label;
    document.getElementById('serviceDisplayFormat').value = svc.display_format;
    document.getElementById('serviceDecimalPlaces').value = svc.decimal_places;
    document.getElementById('servicePerMessage').checked = svc.is_per_message;
    document.getElementById('serviceRecurring').checked = svc.is_recurring;
    document.getElementById('serviceOneOff').checked = svc.is_one_off;
    document.getElementById('serviceStarter').checked = svc.available_on_starter;
    document.getElementById('serviceEnterprise').checked = svc.available_on_enterprise;
    document.getElementById('serviceBespoke').checked = svc.bespoke_only;
    document.getElementById('serviceSortOrder').value = svc.sort_order || 0;
    document.getElementById('serviceEditId').value = svc.id;
    new bootstrap.Modal(document.getElementById('addServiceModal')).show();
}

function saveService() {
    var editId = document.getElementById('serviceEditId').value;
    var isEdit = !!editId;

    var data = {
        display_name: document.getElementById('serviceDisplayName').value.trim(),
        description: document.getElementById('serviceDescription').value.trim() || null,
        unit_label: document.getElementById('serviceUnitLabel').value.trim(),
        display_format: document.getElementById('serviceDisplayFormat').value,
        decimal_places: parseInt(document.getElementById('serviceDecimalPlaces').value),
        is_per_message: document.getElementById('servicePerMessage').checked,
        is_recurring: document.getElementById('serviceRecurring').checked,
        is_one_off: document.getElementById('serviceOneOff').checked,
        available_on_starter: document.getElementById('serviceStarter').checked,
        available_on_enterprise: document.getElementById('serviceEnterprise').checked,
        bespoke_only: document.getElementById('serviceBespoke').checked,
        sort_order: parseInt(document.getElementById('serviceSortOrder').value)
    };

    if (!isEdit) {
        data.slug = document.getElementById('serviceSlug').value.trim();
        if (!data.slug) { showToast('Slug is required.', 'error'); return; }
    }
    if (!data.display_name) { showToast('Display name is required.', 'error'); return; }
    if (!data.unit_label) { showToast('Unit label is required.', 'error'); return; }

    var url = isEdit ? '/admin/api/pricing/services/' + editId : '/admin/api/pricing/services';
    var method = isEdit ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        headers: ajaxHeaders(),
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('addServiceModal')).hide();
                showToast(response.message || 'Service saved.', 'success');
                loadCatalogue();
                loadServicesCache();
            } else {
                showToast(response.error || 'Failed to save service.', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to save service.';
            try {
                var err = JSON.parse(xhr.responseText);
                if (err.errors) {
                    var firstKey = Object.keys(err.errors)[0];
                    msg = err.errors[firstKey][0];
                } else {
                    msg = err.error || err.message || msg;
                }
            } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function loadServiceFilterOptions() {
    var select = document.getElementById('historyServiceFilter');
    if (select.options.length > 1) return;

    allServices.forEach(function(svc) {
        var opt = document.createElement('option');
        opt.value = svc.id;
        opt.textContent = svc.display_name;
        select.appendChild(opt);
    });
}

function loadHistory() {
    var params = { page: currentHistoryPage, per_page: 50 };
    var svcId = document.getElementById('historyServiceFilter').value;
    var tier = document.getElementById('historyTierFilter').value;
    var source = document.getElementById('historySourceFilter').value;
    var fromDate = document.getElementById('historyFromDate').value;
    var toDate = document.getElementById('historyToDate').value;

    if (svcId) params.service_catalogue_id = svcId;
    if (tier) params.tier = tier;
    if (source) params.source = source;
    if (fromDate) params.from_date = fromDate;
    if (toDate) params.to_date = toDate;

    var tbody = document.getElementById('historyBody');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading history...</td></tr>';

    $.ajax({
        url: '/admin/api/pricing/history',
        method: 'GET',
        data: params,
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success && response.data) {
                renderHistory(response.data);
            }
        },
        error: function() {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load history.</td></tr>';
        }
    });
}

function renderHistory(paginator) {
    var items = paginator.data || [];
    var tbody = document.getElementById('historyBody');

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="empty-state"><i class="fas fa-history"></i><p>No price change history found.</p></div></td></tr>';
        document.getElementById('historyPaginationInfo').textContent = '0 entries';
        document.getElementById('historyPagination').innerHTML = '';
        return;
    }

    var html = '';
    items.forEach(function(log) {
        var date = log.created_at ? new Date(log.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
        var svcName = log.service ? log.service.display_name : '—';
        var displayFormat = log.service ? log.service.display_format : 'pence';
        var decimals = log.service ? log.service.decimal_places : 3;
        var changedBy = log.changed_by_user ? (log.changed_by_user.first_name + ' ' + log.changed_by_user.last_name) : (log.changed_by ? log.changed_by.email || '—' : '—');
        var isConflict = log.is_conflict;

        html += '<tr class="' + (isConflict ? 'conflict-row' : '') + '">';
        html += '<td class="small">' + date + '</td>';
        html += '<td>' + escapeHtml(svcName) + '</td>';
        html += '<td>' + escapeHtml(log.tier ? log.tier.charAt(0).toUpperCase() + log.tier.slice(1) : '—') + '</td>';
        html += '<td class="price-cell">' + formatPrice(log.old_price, displayFormat, decimals) + '</td>';
        html += '<td class="price-cell">' + formatPrice(log.new_price, displayFormat, decimals) + '</td>';
        html += '<td><span class="badge bg-light text-dark small">' + escapeHtml(log.source || '—') + '</span></td>';
        html += '<td class="small text-muted">' + escapeHtml(changedBy) + '</td>';
        html += '<td class="small text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + escapeHtml(log.reason || '') + '">' + escapeHtml(log.reason || '—') + '</td>';
        html += '<td class="text-center">';
        if (isConflict) {
            html += '<i class="fas fa-exclamation-triangle text-warning" title="HubSpot conflict detected"></i>';
        }
        html += '</td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
    renderGenericPagination(paginator, 'historyPaginationInfo', 'historyPagination', function(page) {
        currentHistoryPage = page;
        loadHistory();
    });
}

function resetHistoryFilters() {
    document.getElementById('historyServiceFilter').value = '';
    document.getElementById('historyTierFilter').value = '';
    document.getElementById('historySourceFilter').value = '';
    document.getElementById('historyFromDate').value = '';
    document.getElementById('historyToDate').value = '';
    currentHistoryPage = 1;
    loadHistory();
}

function loadUpcoming() {
    $.ajax({
        url: '/admin/api/pricing/upcoming',
        method: 'GET',
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success) {
                renderUpcoming(response.events || [], response.individual_scheduled || []);
            }
        },
        error: function() {
            document.getElementById('upcomingContent').innerHTML = '<div class="text-center py-3 text-danger small">Failed to load upcoming changes.</div>';
        }
    });
}

function renderUpcoming(events, individual) {
    var container = document.getElementById('upcomingContent');

    if (events.length === 0 && individual.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-calendar-check"></i><p>No upcoming scheduled changes.</p></div>';
        return;
    }

    var html = '';

    if (events.length > 0) {
        html += '<div class="table-container mb-3"><div class="table-responsive"><table class="table api-table mb-0">';
        html += '<thead><tr><th>Event</th><th>Effective Date</th><th>Items</th><th>Status</th></tr></thead><tbody>';
        events.forEach(function(evt) {
            var effectiveDate = evt.effective_date ? new Date(evt.effective_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
            var itemCount = evt.items ? evt.items.length : 0;
            html += '<tr>';
            html += '<td><strong>' + escapeHtml(evt.name) + '</strong></td>';
            html += '<td>' + effectiveDate + '</td>';
            html += '<td>' + itemCount + ' change' + (itemCount !== 1 ? 's' : '') + '</td>';
            html += '<td>' + getEventStatusBadge(evt.status) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div></div>';
    }

    if (individual.length > 0) {
        html += '<h6 class="text-muted mt-3 mb-2"><i class="fas fa-clock me-1"></i> Individual Scheduled Prices</h6>';
        html += '<div class="table-container"><div class="table-responsive"><table class="table api-table mb-0">';
        html += '<thead><tr><th>Service</th><th>Tier</th><th>Price</th><th>Effective From</th></tr></thead><tbody>';
        individual.forEach(function(p) {
            var svcName = p.service ? p.service.display_name : p.product_type;
            var displayFormat = p.service ? p.service.display_format : 'pence';
            var decimals = p.service ? p.service.decimal_places : 3;
            var validFrom = p.valid_from ? new Date(p.valid_from).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
            html += '<tr>';
            html += '<td>' + escapeHtml(svcName) + '</td>';
            html += '<td>' + escapeHtml(p.product_tier ? p.product_tier.charAt(0).toUpperCase() + p.product_tier.slice(1) : '') + '</td>';
            html += '<td class="price-cell">' + formatPrice(p.unit_price, displayFormat, decimals) + '</td>';
            html += '<td>' + validFrom + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div></div>';
    }

    container.innerHTML = html;
}

function renderGenericPagination(paginator, infoId, paginationId, goToPageFn) {
    var current = paginator.current_page || 1;
    var last = paginator.last_page || 1;
    var total = paginator.total || 0;
    var perPage = paginator.per_page || 20;
    var from = total > 0 ? ((current - 1) * perPage) + 1 : 0;
    var to = Math.min(current * perPage, total);

    document.getElementById(infoId).textContent = 'Showing ' + from + '-' + to + ' of ' + total + ' entries';

    var paginationHtml = '';
    paginationHtml += '<li class="page-item ' + (current <= 1 ? 'disabled' : '') + '"><a class="page-link" href="#" onclick="return false;">Previous</a></li>';

    var startPage = Math.max(1, current - 2);
    var endPage = Math.min(last, current + 2);
    for (var i = startPage; i <= endPage; i++) {
        paginationHtml += '<li class="page-item ' + (i === current ? 'active' : '') + '"><a class="page-link" href="#" onclick="return false;">' + i + '</a></li>';
    }

    paginationHtml += '<li class="page-item ' + (current >= last ? 'disabled' : '') + '"><a class="page-link" href="#" onclick="return false;">Next</a></li>';

    var paginationEl = document.getElementById(paginationId);
    paginationEl.innerHTML = paginationHtml;

    paginationEl.querySelectorAll('.page-item:not(.disabled):not(.active) .page-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var text = this.textContent.trim();
            var page;
            if (text === 'Previous') page = current - 1;
            else if (text === 'Next') page = current + 1;
            else page = parseInt(text);
            if (page >= 1 && page <= last) goToPageFn(page);
        });
    });
}
</script>
@endpush

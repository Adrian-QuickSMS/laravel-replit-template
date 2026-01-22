{{-- Admin Campaign History - Backend-Ready Architecture --}}
{{-- Data Source: Currently uses mock data. Service layer: CampaignsAdminService (public/js/campaigns-admin-service.js) --}}
{{-- To switch to real backend: Set CampaignsAdminService.config.useMockData = false --}}
{{-- Schema Extensions: accountId (String), accountName (String) - All other fields match customer schema --}}
@extends('layouts.admin')

@section('title', 'Campaign History - Admin')

@push('styles')
<style>
/* =========================================
   Admin Blue Palette (Fillow Admin Console)
   Primary: #1e3a5f | Secondary: #2d5a87 | Accent: #4a90d9
   ========================================= */
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}
.admin-page { padding: 1.5rem; }

/* =========================================
   Search & Filter Toolbar (Numbers-style)
   ========================================= */
.search-filter-toolbar {
    background: #fff;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.search-filter-toolbar .input-group {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
}
.search-filter-toolbar .input-group .input-group-text,
.search-filter-toolbar .input-group .form-control,
.search-filter-toolbar .input-group .btn {
    border: none;
}
.search-filter-toolbar .form-control:focus {
    box-shadow: none;
}
.filter-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
    border: 1.5px solid #c5d3e0;
    color: var(--admin-primary, #1e3a5f);
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    transition: all 0.2s;
    cursor: pointer;
}
.filter-pill-btn:hover {
    background: rgba(30, 58, 95, 0.05);
    border-color: var(--admin-primary, #1e3a5f);
    color: var(--admin-primary, #1e3a5f);
}
.filter-pill-btn.active {
    background: rgba(30, 58, 95, 0.08);
    border-color: var(--admin-primary, #1e3a5f);
    color: var(--admin-primary, #1e3a5f);
}
.filter-pill-btn i {
    font-size: 0.8rem;
    color: var(--admin-primary, #1e3a5f);
}
.filter-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    margin-left: 0.25rem;
    padding: 0 5px;
}

/* =========================================
   Action Dots Button (Numbers-style)
   ========================================= */
#campaignsTableBody .dropdown-menu {
    z-index: 9999 !important;
    min-width: 180px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
#campaignsTableBody .dropdown-menu.show {
    display: block !important;
    position: fixed !important;
}
#campaignsTableBody .dropdown {
    position: static;
}
.action-dots-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
    position: relative;
}
.action-dots-btn:hover {
    color: var(--admin-primary, #1e3a5f);
}
.action-dots-btn:focus {
    outline: none;
    box-shadow: none;
}

/* =========================================
   Export Button
   ========================================= */
.export-btn {
    background: transparent;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
    border-radius: 0.375rem;
}
.export-btn:hover {
    background: #f8f9fa;
    color: #495057;
}

/* =========================================
   Table Footer / Pagination
   ========================================= */
.table-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
}

/* =========================================
   Sortable Column Headers
   ========================================= */
.sortable-header {
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}
.sortable-header:hover {
    background-color: rgba(30, 58, 95, 0.05);
}
.sortable-header .sort-icon {
    margin-left: 0.5rem;
    opacity: 0.3;
    font-size: 0.75rem;
}
.sortable-header:hover .sort-icon {
    opacity: 0.6;
}
.sortable-header.sort-asc .sort-icon,
.sortable-header.sort-desc .sort-icon {
    opacity: 1;
    color: var(--admin-primary, #1e3a5f);
}
.table-footer .pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Status Pill Colors - DO NOT CHANGE (semantic) */
.badge-pastel-success { background-color: #d1e7dd; color: #0f5132; }
.badge-pastel-primary { background-color: #cfe2ff; color: #084298; }
.badge-pastel-secondary { background-color: #e9ecef; color: #6c757d; }
.badge-pastel-warning { background-color: #fff3cd; color: #856404; }
.badge-pastel-info { background-color: #cff4fc; color: #055160; }
.badge-pastel-danger { background-color: #f8d7da; color: #842029; }

/* =========================================
   Admin Blue Button Styles
   ========================================= */
.btn-admin-primary { 
    background-color: #1e3a5f; 
    color: white; 
    border: none; 
}
.btn-admin-primary:hover, 
.btn-admin-primary:focus { 
    background-color: #2d5a87; 
    color: white; 
}
.btn-admin-primary:active,
.btn-admin-primary.active { 
    background-color: #4a90d9; 
    color: white; 
}

.btn-admin-secondary { 
    background-color: #2d5a87; 
    color: white; 
    border: none; 
}
.btn-admin-secondary:hover,
.btn-admin-secondary:focus { 
    background-color: #4a90d9; 
    color: white; 
}

.btn-admin-outline {
    background-color: transparent;
    color: #1e3a5f;
    border: 1px solid #1e3a5f;
}
.btn-admin-outline:hover,
.btn-admin-outline:focus {
    background-color: #1e3a5f;
    color: white;
}

/* =========================================
   Admin Blue Text & Background Utilities
   ========================================= */
.text-admin-primary { color: #1e3a5f !important; }
.text-admin-secondary { color: #2d5a87 !important; }
.text-admin-accent { color: #4a90d9 !important; }
.bg-admin-light { background-color: rgba(30, 58, 95, 0.05); }
.bg-admin-primary { background-color: #1e3a5f; }

/* =========================================
   Section Headers - Admin Blue
   ========================================= */
.admin-section-header {
    color: #1e3a5f;
    font-weight: 600;
    border-bottom: 2px solid #1e3a5f;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

/* Page title styling */
.admin-page h4 { color: #1e3a5f; }

/* =========================================
   Form Controls - Focus States (Admin Blue)
   ========================================= */
.form-control:focus,
.form-select:focus {
    border-color: #4a90d9;
    box-shadow: 0 0 0 0.2rem rgba(74, 144, 217, 0.25);
}

.form-check-input:checked {
    background-color: #1e3a5f;
    border-color: #1e3a5f;
}

.form-check-input:focus {
    border-color: #4a90d9;
    box-shadow: 0 0 0 0.2rem rgba(74, 144, 217, 0.25);
}

/* =========================================
   Multiselect Dropdown Styling
   ========================================= */
.multiselect-dropdown .dropdown-toggle {
    background-color: #fff;
    border: 1px solid #ced4da;
    color: #495057;
    text-align: left;
}
.multiselect-dropdown .dropdown-toggle:focus {
    border-color: #4a90d9;
    box-shadow: 0 0 0 0.2rem rgba(74, 144, 217, 0.25);
}
.multiselect-dropdown .dropdown-menu {
    padding: 0.5rem;
    min-width: 200px;
}

/* =========================================
   Table Row Hover (Admin Blue tint)
   ========================================= */
.campaign-row { cursor: pointer; }
.campaign-row:hover { background-color: rgba(30, 58, 95, 0.04); }
.campaign-row:focus { 
    outline: 2px solid #4a90d9;
    outline-offset: -2px;
}

/* =========================================
   Account Link Styling
   ========================================= */
.account-link { 
    color: #1e3a5f; 
    font-weight: 500; 
    display: block;
    max-width: 130px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.account-link:hover { text-decoration: underline; color: #2d5a87; }
.account-link:focus { 
    outline: 2px solid #4a90d9;
    outline-offset: 2px;
}

/* =========================================
   Filter Panel - Admin Blue
   ========================================= */
#filterPanel .form-label { 
    color: #1e3a5f; 
    font-weight: 600; 
}
#filterPanel .select-all-btn { color: #4a90d9; }
#filterPanel .select-all-btn:hover { color: #2d5a87; }
#filterPanel .clear-all-btn:hover { color: #1e3a5f; }

/* Active filter badge */
#activeFiltersBadge { background-color: #4a90d9 !important; }

/* Filters button active state */
.btn-outline-primary:not(:disabled):not(.disabled).active,
.btn-outline-primary:not(:disabled):not(.disabled):active {
    background-color: #1e3a5f;
    border-color: #1e3a5f;
}

/* =========================================
   Drawer / Offcanvas - Admin Blue
   ========================================= */
#drawerAccountLink { color: #2d5a87; }
#drawerAccountLink:hover { color: #4a90d9; }

.offcanvas-header { border-bottom: 1px solid rgba(30, 58, 95, 0.1); }
.offcanvas .btn-outline-secondary:hover { 
    border-color: #2d5a87; 
    color: #2d5a87; 
}

/* Drawer section headers */
.offcanvas h6 { color: #1e3a5f; }

/* Progress bar accents */
.progress-bar.bg-admin { background-color: #2d5a87; }

/* =========================================
   Links - Admin Blue
   ========================================= */
a.text-primary { color: #1e3a5f !important; }
a.text-primary:hover { color: #2d5a87 !important; }

/* Dropdown items hover */
.dropdown-item:hover,
.dropdown-item:focus {
    background-color: rgba(30, 58, 95, 0.08);
    color: #1e3a5f;
}
.dropdown-item.active,
.dropdown-item:active {
    background-color: #1e3a5f;
    color: white;
}

/* =========================================
   Sort dropdown active indicator
   ========================================= */
.dropdown-menu .dropdown-item.active-sort {
    background-color: rgba(30, 58, 95, 0.1);
    font-weight: 600;
}

/* =========================================
   Informational Box - Pastel Purple with Transparent Buttons
   (Consistent across portal per design spec)
   ========================================= */
.info-box-pastel {
    background-color: #f0ebf8;
    border-radius: 8px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}

.info-box-pastel .btn-transparent {
    background-color: transparent;
    border: 1px solid rgba(255, 255, 255, 0.8);
    color: #495057;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}
.info-box-pastel .btn-transparent:hover {
    background-color: rgba(255, 255, 255, 0.5);
    border-color: rgba(255, 255, 255, 1);
}
.info-box-pastel .btn-transparent:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.5);
}

.info-box-pastel .btn-transparent-danger {
    background-color: transparent;
    border: 1px solid rgba(220, 53, 69, 0.5);
    color: #dc3545;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}
.info-box-pastel .btn-transparent-danger:hover {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
}

/* Bulk Action Bar (when items selected) */
.bulk-action-bar {
    background-color: #f0ebf8;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.bulk-action-bar .selected-count {
    font-weight: 600;
    color: #495057;
    margin-right: 0.5rem;
}

.bulk-action-bar .btn-action {
    background-color: transparent;
    border: 1px solid rgba(255, 255, 255, 0.9);
    color: #495057;
    font-size: 0.8125rem;
    padding: 0.35rem 0.65rem;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.bulk-action-bar .btn-action:hover {
    background-color: rgba(255, 255, 255, 0.6);
}
.bulk-action-bar .btn-action i {
    font-size: 0.75rem;
}

.bulk-action-bar .btn-action-danger {
    border-color: rgba(220, 53, 69, 0.4);
    color: #dc3545;
}
.bulk-action-bar .btn-action-danger:hover {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>
@endpush

@php
$campaigns = [
    ['id' => 'C-2026-001', 'name' => 'Spring Promo Campaign', 'account_id' => 'ACC-001', 'account_name' => 'Acme Corp', 'channel' => 'basic_rcs', 'status' => 'scheduled', 'sender_id' => 'ACME', 'rcs_agent' => 'Acme Business', 'recipients_total' => 3500, 'recipients_delivered' => null, 'send_date' => '2026-01-25 10:00:00', 'has_tracking' => 'yes', 'has_optout' => 'yes', 'tags' => 'promo,spring', 'template' => 'Sale Announcement'],
    ['id' => 'C-2026-002', 'name' => 'New Year Flash Sale', 'account_id' => 'ACC-002', 'account_name' => 'RetailMax', 'channel' => 'rich_rcs', 'status' => 'sending', 'sender_id' => 'RETAILMAX', 'rcs_agent' => 'RetailMax Official', 'recipients_total' => 5200, 'recipients_delivered' => 3100, 'send_date' => '2026-01-22 00:00:00', 'has_tracking' => 'yes', 'has_optout' => 'yes', 'tags' => 'flash,sale', 'template' => 'Flash Deal'],
    ['id' => 'C-2025-003', 'name' => 'Holiday Greetings', 'account_id' => 'ACC-003', 'account_name' => 'ServicePro', 'channel' => 'sms_only', 'status' => 'complete', 'sender_id' => 'SVCPRO', 'rcs_agent' => '', 'recipients_total' => 3150, 'recipients_delivered' => 3102, 'send_date' => '2025-12-24 09:00:00', 'has_tracking' => 'no', 'has_optout' => 'yes', 'tags' => 'holiday', 'template' => 'Reminder'],
    ['id' => 'C-2024-004', 'name' => 'Boxing Day Deals', 'account_id' => 'ACC-001', 'account_name' => 'Acme Corp', 'channel' => 'basic_rcs', 'status' => 'complete', 'sender_id' => 'ACME', 'rcs_agent' => 'Acme Business', 'recipients_total' => 2800, 'recipients_delivered' => 2756, 'send_date' => '2024-12-26 08:00:00', 'has_tracking' => 'yes', 'has_optout' => 'yes', 'tags' => 'boxing,deals', 'template' => 'Product Showcase'],
    ['id' => 'C-2024-005', 'name' => 'Christmas Eve Reminder', 'account_id' => 'ACC-004', 'account_name' => 'HealthFirst', 'channel' => 'sms_only', 'status' => 'complete', 'sender_id' => 'HEALTH1', 'rcs_agent' => '', 'recipients_total' => 1500, 'recipients_delivered' => 1487, 'send_date' => '2024-12-24 07:00:00', 'has_tracking' => 'no', 'has_optout' => 'yes', 'tags' => 'appointment', 'template' => 'Appointment'],
    ['id' => 'C-2024-006', 'name' => 'Winter Clearance', 'account_id' => 'ACC-002', 'account_name' => 'RetailMax', 'channel' => 'rich_rcs', 'status' => 'complete', 'sender_id' => 'RETAILMAX', 'rcs_agent' => 'RetailMax Official', 'recipients_total' => 4200, 'recipients_delivered' => 4156, 'send_date' => '2024-12-23 14:30:00', 'has_tracking' => 'yes', 'has_optout' => 'yes', 'tags' => 'clearance,winter', 'template' => 'VIP Invitation'],
    ['id' => 'C-2024-007', 'name' => 'Last Minute Gifts', 'account_id' => 'ACC-005', 'account_name' => 'GiftZone', 'channel' => 'sms_only', 'status' => 'complete', 'sender_id' => 'GIFTZONE', 'rcs_agent' => '', 'recipients_total' => 890, 'recipients_delivered' => 885, 'send_date' => '2024-12-23 10:00:00', 'has_tracking' => 'yes', 'has_optout' => 'no', 'tags' => 'gifts', 'template' => 'Weekend Deal'],
    ['id' => 'C-2024-008', 'name' => 'Flash Sale Alert', 'account_id' => 'ACC-001', 'account_name' => 'Acme Corp', 'channel' => 'sms_only', 'status' => 'cancelled', 'sender_id' => 'ACME', 'rcs_agent' => '', 'recipients_total' => 1200, 'recipients_delivered' => null, 'send_date' => '2024-12-22 15:00:00', 'has_tracking' => 'no', 'has_optout' => 'yes', 'tags' => '', 'template' => ''],
    ['id' => 'C-2024-009', 'name' => 'Seasonal Offers', 'account_id' => 'ACC-003', 'account_name' => 'ServicePro', 'channel' => 'basic_rcs', 'status' => 'complete', 'sender_id' => 'SVCPRO', 'rcs_agent' => 'ServicePro Connect', 'recipients_total' => 2100, 'recipients_delivered' => 2089, 'send_date' => '2024-12-21 11:00:00', 'has_tracking' => 'yes', 'has_optout' => 'yes', 'tags' => 'seasonal', 'template' => 'Product Launch'],
    ['id' => 'C-2024-010', 'name' => 'Early Bird Special', 'account_id' => 'ACC-004', 'account_name' => 'HealthFirst', 'channel' => 'rich_rcs', 'status' => 'complete', 'sender_id' => 'HEALTH1', 'rcs_agent' => 'HealthFirst Care', 'recipients_total' => 950, 'recipients_delivered' => 942, 'send_date' => '2024-12-20 06:00:00', 'has_tracking' => 'yes', 'has_optout' => 'yes', 'tags' => 'early,special', 'template' => 'Sale Announcement'],
];

$accounts = collect($campaigns)->map(fn($c) => ['id' => $c['account_id'], 'name' => $c['account_name']])->unique('id')->values()->toArray();
$senderIds = collect($campaigns)->pluck('sender_id')->unique()->filter()->sort()->values()->toArray();
$rcsAgents = collect($campaigns)->pluck('rcs_agent')->unique()->filter()->sort()->values()->toArray();
@endphp

@section('content')
<div class="admin-page">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none" style="color: #1e3a5f;">Admin</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none" style="color: #1e3a5f;">Management</a></li>
            <li class="breadcrumb-item active">Campaign History</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1" style="color: var(--admin-primary); font-weight: 600;">Campaign History</h4>
            <p class="text-muted mb-0">View and manage all customer campaigns across the platform</p>
        </div>
        <div>
            <button class="export-btn" onclick="exportCampaigns()">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    <!-- Search and Filter Toolbar (Numbers-style) -->
    <div class="search-filter-toolbar mb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="search-box" style="max-width: 350px; flex: 1;">
                <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control" id="campaignSearch" placeholder="Search campaigns, accounts, sender IDs..." onkeyup="handleSearch(this.value)">
                    <button class="btn btn-link text-muted" type="button" onclick="clearSearch()" id="clearSearchBtn" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">
                    Showing <span id="visibleCount">{{ count($campaigns) }}</span> of <span id="totalCount">{{ count($campaigns) }}</span> campaigns
                </span>
                <button class="filter-pill-btn" type="button" id="filterPillBtn">
                    <i class="fas fa-filter"></i>
                    <span>Filters</span>
                    <span class="filter-count-badge" id="activeFilterCount" style="display: none;">0</span>
                </button>
            </div>
        </div>
    </div>
            
            <!-- Collapsible Filter Panel -->
            <div class="collapse mt-3" id="filterPanel">
                <div class="border-top pt-3">
                    <div class="row g-3">
                        <!-- Account Filter -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Account</label>
                            <div class="dropdown multiselect-dropdown" data-filter="accounts">
                                <button class="btn btn-outline-secondary w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <span class="dropdown-label">All Accounts</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2" style="max-height: 300px; overflow-y: auto;">
                                    <input type="text" class="form-control form-control-sm mb-2" id="accountFilterSearch" placeholder="Search accounts..." oninput="filterAccountOptions(this.value)">
                                    <div class="d-flex justify-content-between mb-2 px-1">
                                        <a href="#" class="small text-primary select-all-btn">Select All</a>
                                        <a href="#" class="small text-muted clear-all-btn">Clear</a>
                                    </div>
                                    <div id="accountFilterOptions">
                                    @foreach($accounts as $account)
                                    <div class="form-check account-option" data-name="{{ strtolower($account['name']) }}">
                                        <input class="form-check-input" type="checkbox" value="{{ $account['id'] }}" id="account_{{ $account['id'] }}">
                                        <label class="form-check-label small" for="account_{{ $account['id'] }}">{{ $account['name'] }}</label>
                                    </div>
                                    @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Status</label>
                            <div class="dropdown multiselect-dropdown" data-filter="statuses">
                                <button class="btn btn-outline-secondary w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <span class="dropdown-label">All Statuses</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 px-1">
                                        <a href="#" class="small text-primary select-all-btn">Select All</a>
                                        <a href="#" class="small text-muted clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="scheduled" id="status_scheduled">
                                        <label class="form-check-label small" for="status_scheduled">Scheduled</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="sending" id="status_sending">
                                        <label class="form-check-label small" for="status_sending">Sending</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="complete" id="status_complete">
                                        <label class="form-check-label small" for="status_complete">Complete</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="cancelled" id="status_cancelled">
                                        <label class="form-check-label small" for="status_cancelled">Cancelled</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Channel Filter -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Channel</label>
                            <div class="dropdown multiselect-dropdown" data-filter="channels">
                                <button class="btn btn-outline-secondary w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <span class="dropdown-label">All Channels</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 px-1">
                                        <a href="#" class="small text-primary select-all-btn">Select All</a>
                                        <a href="#" class="small text-muted clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="sms_only" id="channel_sms">
                                        <label class="form-check-label small" for="channel_sms">SMS</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="basic_rcs" id="channel_basic_rcs">
                                        <label class="form-check-label small" for="channel_basic_rcs">Basic RCS</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="rich_rcs" id="channel_rich_rcs">
                                        <label class="form-check-label small" for="channel_rich_rcs">Rich RCS</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Date Range</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" id="filterDateFrom" placeholder="From">
                                <span class="input-group-text">-</span>
                                <input type="date" class="form-control" id="filterDateTo" placeholder="To">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end gap-2">
                            <button class="btn btn-outline-secondary" id="btnResetFilters" onclick="window.resetFilters();">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button class="btn text-white" id="btnApplyFilters" style="background-color: #1e3a5f;" onclick="window.applyFilters();">
                                <i class="fas fa-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card" style="border: 1px solid #e0e6ed;">
        <div class="card-body p-3">
            <div class="table-responsive" id="campaignsTable" style="overflow-x: auto;">
                <table class="table table-hover mb-0 align-middle" style="width: 100%; min-width: 900px;">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th class="py-3 px-3 sortable-header" data-sort="account" onclick="toggleSort('account')" style="white-space: nowrap;">
                                Account <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="py-3 px-3 sortable-header" data-sort="name" onclick="toggleSort('name')" style="white-space: nowrap;">
                                Campaign Name <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="py-3 px-3" style="white-space: nowrap;">Channel</th>
                            <th class="py-3 px-3" style="white-space: nowrap;">Status</th>
                            <th class="py-3 px-3 sortable-header" data-sort="recipients" onclick="toggleSort('recipients')" style="white-space: nowrap;">
                                Recipients <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="py-3 px-3 sortable-header" data-sort="date" onclick="toggleSort('date')" style="white-space: nowrap;">
                                Send Date <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="py-3 px-3 text-end" style="white-space: nowrap; width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="campaignsTableBody">
                        @forelse($campaigns as $campaign)
                        <tr class="campaign-row" 
                            data-id="{{ $campaign['id'] }}"
                            data-account="{{ $campaign['account_id'] }}"
                            data-account-name="{{ strtolower($campaign['account_name']) }}"
                            data-name="{{ strtolower($campaign['name']) }}"
                            data-channel="{{ $campaign['channel'] }}"
                            data-status="{{ $campaign['status'] }}"
                            data-sender-id="{{ $campaign['sender_id'] }}"
                            data-rcs-agent="{{ $campaign['rcs_agent'] ?? '' }}"
                            data-send-date="{{ $campaign['send_date'] }}"
                            data-recipients-total="{{ $campaign['recipients_total'] }}"
                            data-recipients-delivered="{{ $campaign['recipients_delivered'] ?? '' }}"
                            data-has-tracking="{{ $campaign['has_tracking'] ?? 'no' }}"
                            data-has-optout="{{ $campaign['has_optout'] ?? 'no' }}"
                            data-tags="{{ $campaign['tags'] ?? '' }}"
                            data-template="{{ $campaign['template'] ?? '' }}"
                            onclick="openCampaignDrawer('{{ $campaign['id'] }}')">
                            <td class="py-2 px-3">
                                <a href="{{ route('admin.accounts.details', ['accountId' => $campaign['account_id']]) }}" 
                                   class="account-link text-decoration-none" 
                                   onclick="event.stopPropagation();"
                                   title="{{ $campaign['account_name'] }}"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="top">
                                    {{ $campaign['account_name'] }}
                                </a>
                            </td>
                            <td class="py-2 px-3">
                                <h6 class="mb-0 fs-6">{{ $campaign['name'] }}</h6>
                                @if(!empty($campaign['tags']))
                                <small class="text-muted">
                                    @foreach(explode(',', $campaign['tags']) as $tag)
                                    <span class="badge badge-pastel-secondary me-1">{{ trim($tag) }}</span>
                                    @endforeach
                                </small>
                                @endif
                            </td>
                            <td class="py-2 px-3">
                                @if($campaign['channel'] === 'sms_only')
                                <span class="badge badge-pastel-success">SMS</span>
                                @elseif($campaign['channel'] === 'basic_rcs')
                                <span class="badge badge-pastel-primary">Basic RCS</span>
                                @else
                                <span class="badge badge-pastel-info">Rich RCS</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 status-cell">
                                @if($campaign['status'] === 'scheduled')
                                <span class="badge badge-pastel-warning"><i class="fas fa-clock me-1"></i>Scheduled</span>
                                @elseif($campaign['status'] === 'sending')
                                <span class="badge badge-pastel-primary"><i class="fas fa-paper-plane me-1"></i>Sending</span>
                                @elseif($campaign['status'] === 'complete')
                                <span class="badge badge-pastel-success"><i class="fas fa-check me-1"></i>Complete</span>
                                @elseif($campaign['status'] === 'cancelled')
                                <span class="badge badge-pastel-secondary"><i class="fas fa-ban me-1"></i>Cancelled</span>
                                @elseif($campaign['status'] === 'suspended')
                                <span class="badge badge-pastel-warning"><i class="fas fa-pause-circle me-1"></i>Suspended</span>
                                @endif
                            </td>
                            <td class="py-2 px-3">
                                <span class="fw-medium">{{ number_format($campaign['recipients_total']) }}</span>
                                @if(isset($campaign['recipients_delivered']) && $campaign['recipients_delivered'] && $campaign['status'] !== 'scheduled')
                                <br><small class="text-success">{{ number_format($campaign['recipients_delivered']) }} delivered</small>
                                @endif
                            </td>
                            <td class="py-2 px-3">
                                {{ \Carbon\Carbon::parse($campaign['send_date'])->format('d M Y') }}
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($campaign['send_date'])->format('H:i') }}</small>
                            </td>
                            <td class="py-2 px-3 text-end">
                                <div class="dropdown">
                                    <button class="action-dots-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="z-index: 1050;">
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="handleCampaignAction(event, 'view', '{{ $campaign['id'] }}')"><i class="fas fa-eye me-2"></i>View Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($campaign['status'] === 'scheduled')
                                        <li><a class="dropdown-item text-warning" href="javascript:void(0)" onclick="handleCampaignAction(event, 'suspend', '{{ $campaign['id'] }}')"><i class="fas fa-pause-circle me-2"></i>Suspend Campaign</a></li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="handleCampaignAction(event, 'cancel', '{{ $campaign['id'] }}')"><i class="fas fa-ban me-2"></i>Cancel Campaign</a></li>
                                        @elseif($campaign['status'] === 'sending')
                                        <li><a class="dropdown-item text-warning" href="javascript:void(0)" onclick="handleCampaignAction(event, 'suspend', '{{ $campaign['id'] }}')"><i class="fas fa-pause-circle me-2"></i>Suspend Campaign</a></li>
                                        @elseif($campaign['status'] === 'suspended')
                                        <li><a class="dropdown-item text-success" href="javascript:void(0)" onclick="handleCampaignAction(event, 'resume', '{{ $campaign['id'] }}')"><i class="fas fa-play-circle me-2"></i>Resume Campaign</a></li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="handleCampaignAction(event, 'cancel', '{{ $campaign['id'] }}')"><i class="fas fa-ban me-2"></i>Cancel Campaign</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    <h6>No campaigns found</h6>
                                    <p class="mb-0 small">Campaign history will appear here once customers send campaigns.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- No Results State -->
            <div id="noResultsState" class="d-none text-center py-5">
                <div class="text-muted">
                    <i class="fas fa-search fa-3x mb-3 d-block"></i>
                    <h6>No matching campaigns</h6>
                    <p class="mb-0 small">Try adjusting your search or filter criteria.</p>
                    <button class="btn btn-outline-secondary btn-sm mt-3" onclick="window.resetFilters(); clearSearch();">
                        <i class="fas fa-undo me-1"></i> Clear all filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Campaign Details Drawer -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="campaignDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom" style="background-color: #f0f4f8;">
        <div>
            <h5 class="offcanvas-title mb-1" id="drawerCampaignName">Campaign Name</h5>
            <small class="text-muted">ID: <span id="drawerCampaignId">-</span></small>
            <div class="mt-1">
                <small class="text-muted">Account: </small>
                <a href="#" id="drawerAccountLink" class="text-decoration-none small" style="color: #1e3a5f;">
                    <span id="drawerAccountName">-</span>
                </a>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- Quick Stats Row -->
        <div class="p-3 border-bottom" style="background-color: #f8f9fa;">
            <div class="row g-2 text-center">
                <div class="col-3">
                    <div class="small text-muted">Recipients</div>
                    <div class="fw-bold" id="drawerRecipientsTotal">-</div>
                </div>
                <div class="col-3">
                    <div class="small text-muted">Delivered</div>
                    <div class="fw-bold text-success" id="drawerRecipientsDelivered">-</div>
                </div>
                <div class="col-3">
                    <div class="small text-muted">Failed</div>
                    <div class="fw-bold text-danger" id="drawerRecipientsFailed">-</div>
                </div>
                <div class="col-3">
                    <div class="small text-muted">Rate</div>
                    <div class="fs-5 fw-bold" id="drawerDeliveryRate">-</div>
                </div>
            </div>
        </div>
        
        <!-- Scrollable Content -->
        <div style="overflow-y: auto; max-height: calc(100vh - 220px);">
            <!-- Campaign Details -->
            <div class="p-3 border-bottom">
                <h6 class="text-muted small mb-3">CAMPAIGN DETAILS</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Channel</small>
                        <span class="badge" id="drawerChannelBadge">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge" id="drawerStatusBadge">-</span>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted d-block">Live State</small>
                        <span class="badge" id="drawerLiveStateBadge">-</span>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted d-block">Sender ID</small>
                        <span class="fw-medium" id="drawerSenderId">-</span>
                    </div>
                    <div class="col-6 mt-2" id="drawerRcsAgentRow" style="display: none;">
                        <small class="text-muted d-block">RCS Agent</small>
                        <span class="fw-medium" id="drawerRcsAgent">-</span>
                    </div>
                    <div class="col-6 mt-2" id="drawerTemplateRow" style="display: none;">
                        <small class="text-muted d-block">Template</small>
                        <span class="fw-medium" id="drawerTemplate">-</span>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted d-block" id="drawerSendTimeLabel">Sent:</small>
                        <span class="fw-medium" id="drawerSendTime">-</span>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted d-block">Tags</small>
                        <div id="drawerTags">-</div>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Outcomes -->
            <div class="p-3 border-bottom" id="deliveryOutcomesCard">
                <h6 class="text-muted small mb-3">DELIVERY OUTCOMES</h6>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-success" id="barDelivered" style="width: 0%"></div>
                    <div class="progress-bar bg-warning" id="barPending" style="width: 0%"></div>
                    <div class="progress-bar bg-danger" id="barUndeliverable" style="width: 0%"></div>
                </div>
                <div class="row g-2 small">
                    <div class="col-4">
                        <span class="badge bg-success me-1">&nbsp;</span>
                        Delivered: <span id="outcomeDelivered">0</span>
                        <span class="text-muted">(<span id="outcomeDeliveredPct">0%</span>)</span>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-warning me-1">&nbsp;</span>
                        Pending: <span id="outcomePending">0</span>
                        <span class="text-muted">(<span id="outcomePendingPct">0%</span>)</span>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-danger me-1">&nbsp;</span>
                        Failed: <span id="outcomeUndeliverable">0</span>
                        <span class="text-muted">(<span id="outcomeUndeliverablePct">0%</span>)</span>
                    </div>
                </div>
            </div>
            
            <!-- Channel Split -->
            <div class="p-3 border-bottom" id="channelSplitCard">
                <h6 class="text-muted small mb-3">CHANNEL SPLIT</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="badge badge-pastel-success">SMS</span>
                        <span class="ms-2 fw-medium" id="channelSmsPercent">0%</span>
                    </div>
                    <small class="text-muted" id="channelSmsCount">0 msgs</small>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge badge-pastel-primary">RCS</span>
                        <span class="ms-2 fw-medium" id="channelRcsPercent">0%</span>
                    </div>
                    <small class="text-muted" id="channelRcsCount">0 msgs</small>
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" id="channelBarSms" style="width: 0%; background-color: #0f5132;"></div>
                    <div class="progress-bar" id="channelBarRcs" style="width: 0%; background-color: #084298;"></div>
                </div>
            </div>
            
            <!-- Engagement Metrics -->
            <div class="p-3 border-bottom" id="engagementMetricsCard">
                <h6 class="text-muted small mb-3">ENGAGEMENT METRICS</h6>
                <div id="trackingMetrics">
                    <div class="row g-2 mb-2">
                        <div class="col-4 text-center">
                            <div class="small text-muted">Total Clicks</div>
                            <div class="fw-bold" id="metricTotalClicks">-</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="small text-muted">Unique Clicks</div>
                            <div class="fw-bold" id="metricUniqueClicks">-</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="small text-muted">CTR</div>
                            <div class="fw-bold" id="metricCtr">-</div>
                        </div>
                    </div>
                </div>
                <div id="rcsSeenMetrics" class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">RCS Read Receipts</span>
                        <span class="fw-medium" id="metricSeenPercent">-</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" id="metricSeenBar" style="width: 0%; background-color: #1e3a5f;"></div>
                    </div>
                    <small class="text-muted" id="metricSeenCount">-</small>
                </div>
            </div>
            
            <!-- Cost Summary -->
            <div class="p-3 border-bottom" id="costSummaryCard">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted small mb-0" id="costLabel">COST SUMMARY</h6>
                    <span class="badge" id="costStatusBadge">-</span>
                </div>
                <div id="smsCostSection">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>SMS Messages</span>
                        <span id="smsCostCount">-</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Unit price</span>
                        <span id="smsCostUnit">-</span>
                    </div>
                </div>
                <div id="rcsCostSection" style="display: none;">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>SMS Fallback</span>
                        <span id="rcsFallbackCount">-</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span></span>
                        <span id="rcsFallbackCost">-</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>RCS Messages</span>
                        <span id="rcsMessageCount">-</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span></span>
                        <span id="rcsMessageCost">-</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between fw-bold border-top pt-2">
                    <span id="costTotalLabel">Total</span>
                    <span id="costTotal">-</span>
                </div>
                <div id="costDisclaimer" class="mt-2" style="display: none;">
                    <small class="text-muted" id="costDisclaimerText"></small>
                </div>
            </div>
            
            <!-- Message Preview -->
            <div class="p-3 border-bottom">
                <h6 class="text-muted small mb-3">MESSAGE PREVIEW</h6>
                <div id="campaignPreviewToggle" class="d-flex justify-content-center mb-3 d-none">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm active" id="campaignPreviewRCSBtn" onclick="toggleCampaignPreview('rcs')" style="background: #1e3a5f; color: white; border: 1px solid #1e3a5f;">
                            RCS Preview
                        </button>
                        <button type="button" class="btn btn-sm" id="campaignPreviewSMSBtn" onclick="toggleCampaignPreview('sms')" style="background: white; color: #1e3a5f; border: 1px solid #1e3a5f;">
                            SMS Fallback
                        </button>
                    </div>
                </div>
                <div id="campaignPreviewContainer" class="d-flex justify-content-center">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-mobile-alt fa-2x mb-2"></i>
                        <p class="small mb-0">Preview will appear here</p>
                    </div>
                </div>
            </div>
            
            <!-- Admin Actions -->
            <div class="p-3" id="scheduledActions" style="display: none;">
                <div class="alert mb-0" style="background-color: #f0f4f8; border: 1px solid #d0d9e3;">
                    <i class="fas fa-info-circle me-2" style="color: #1e3a5f;"></i>
                    <span class="small">This campaign is scheduled. Admin users cannot modify customer campaigns.</span>
                </div>
            </div>
            <div class="p-3" id="sendingNotice" style="display: none;">
                <div class="alert mb-0" style="background-color: #cfe2ff; border: 1px solid #b6d4fe;">
                    <i class="fas fa-paper-plane me-2 text-primary"></i>
                    <span class="small">This campaign is currently sending. Delivery statistics update in real-time.</span>
                </div>
            </div>
            
            <!-- Export Actions -->
            <div class="p-3 border-top">
                <h6 class="text-muted small mb-3">ADMIN ACTIONS</h6>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="showComingSoon('Delivery report export will generate a CSV/Excel file with delivery status for all recipients.')">
                        <i class="fas fa-file-export me-1"></i> Export Delivery
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="showComingSoon('Message log export will include per-recipient status, timestamps, and message content.')">
                        <i class="fas fa-file-alt me-1"></i> Message Log
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="showComingSoon('Audit log will show who created, edited, approved, or cancelled this campaign and when.')">
                        <i class="fas fa-history me-1"></i> Audit Log
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coming Soon Modal -->
<div class="modal fade" id="comingSoonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f0f4f8; border-bottom: 1px solid #d0d9e3;">
                <h5 class="modal-title" style="color: #1e3a5f;">
                    <i class="fas fa-info-circle me-2"></i>Coming Soon
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="comingSoonMessage" class="mb-0">This feature is coming soon.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<script src="{{ asset('js/rcs-preview-renderer.js') }}"></script>
<script src="{{ asset('js/campaigns-admin-service.js') }}"></script>
<script>
var campaignDrawer = null;
var comingSoonModal = null;
var activeFilters = {};

document.addEventListener('DOMContentLoaded', function() {
    var drawerEl = document.getElementById('campaignDrawer');
    if (drawerEl) {
        campaignDrawer = new bootstrap.Offcanvas(drawerEl);
    }
    
    var searchInput = document.getElementById('campaignSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterCampaigns);
    }
    
    document.querySelectorAll('.select-all-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdown = this.closest('.multiselect-dropdown');
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = true; });
            updateDropdownLabel(dropdown);
        });
    });
    
    document.querySelectorAll('.clear-all-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdown = this.closest('.multiselect-dropdown');
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
            updateDropdownLabel(dropdown);
        });
    });
    
    document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateDropdownLabel(this.closest('.multiselect-dropdown'));
        });
    });
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Filter account options in dropdown search
function filterAccountOptions(searchTerm) {
    var term = searchTerm.toLowerCase().trim();
    var options = document.querySelectorAll('#accountFilterOptions .account-option');
    
    options.forEach(function(option) {
        var name = option.dataset.name || '';
        if (term === '' || name.includes(term)) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
}

function updateDropdownLabel(dropdown) {
    var label = dropdown.querySelector('.dropdown-label');
    var checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
    var filter = dropdown.dataset.filter;
    
    if (checked.length === 0) {
        if (filter === 'statuses') label.textContent = 'All Statuses';
        else if (filter === 'channels') label.textContent = 'All Channels';
        else if (filter === 'accounts') label.textContent = 'All Accounts';
        else label.textContent = 'Any';
    } else if (checked.length === 1) {
        label.textContent = checked[0].nextElementSibling.textContent;
    } else {
        label.textContent = checked.length + ' selected';
    }
}

function getCheckedValues(dropdownSelector) {
    var values = [];
    var dropdown = document.querySelector(dropdownSelector);
    if (dropdown) {
        dropdown.querySelectorAll('input[type="checkbox"]:checked').forEach(function(cb) {
            values.push(cb.value);
        });
    }
    return values;
}

function filterCampaigns() {
    var searchTerm = document.getElementById('campaignSearch').value.toLowerCase().trim();
    var rows = document.querySelectorAll('#campaignsTableBody tr[data-id]');
    var visibleCount = 0;
    var hasActiveFilters = Object.values(activeFilters).some(function(v) { 
        return Array.isArray(v) ? v.length > 0 : v !== ''; 
    });
    
    rows.forEach(function(row) {
        var name = (row.dataset.name || '').toLowerCase();
        var accountName = (row.dataset.accountName || '').toLowerCase();
        var senderId = (row.dataset.senderId || '').toLowerCase();
        var channel = (row.dataset.channel || '');
        var status = (row.dataset.status || '');
        var account = (row.dataset.account || '');
        var sendDate = row.dataset.sendDate || '';
        
        var searchable = name + ' ' + accountName + ' ' + senderId + ' ' + channel.replace('_', ' ') + ' ' + status;
        var matchesSearch = searchTerm === '' || searchable.includes(searchTerm);
        
        var matchesFilters = true;
        if (hasActiveFilters) {
            if (activeFilters.accounts && activeFilters.accounts.length > 0 && !activeFilters.accounts.includes(account)) matchesFilters = false;
            if (activeFilters.statuses && activeFilters.statuses.length > 0 && !activeFilters.statuses.includes(status)) matchesFilters = false;
            if (activeFilters.channels && activeFilters.channels.length > 0 && !activeFilters.channels.includes(channel)) matchesFilters = false;
            
            if (activeFilters.dateFrom) {
                var rowDate = new Date(sendDate);
                var fromDate = new Date(activeFilters.dateFrom);
                if (rowDate < fromDate) matchesFilters = false;
            }
            if (activeFilters.dateTo) {
                var rowDate = new Date(sendDate);
                var toDate = new Date(activeFilters.dateTo);
                toDate.setHours(23, 59, 59);
                if (rowDate > toDate) matchesFilters = false;
            }
        }
        
        if (matchesSearch && matchesFilters) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('visibleCount').textContent = visibleCount;
    
    var noResultsState = document.getElementById('noResultsState');
    var table = document.getElementById('campaignsTable');
    
    if (visibleCount === 0 && (searchTerm !== '' || hasActiveFilters)) {
        noResultsState.classList.remove('d-none');
        table.classList.add('d-none');
    } else {
        noResultsState.classList.add('d-none');
        table.classList.remove('d-none');
    }
}

function clearSearch() {
    document.getElementById('campaignSearch').value = '';
    filterCampaigns();
}

window.applyFilters = function() {
    activeFilters = {
        accounts: getCheckedValues('[data-filter="accounts"]'),
        statuses: getCheckedValues('[data-filter="statuses"]'),
        channels: getCheckedValues('[data-filter="channels"]'),
        dateFrom: document.getElementById('filterDateFrom').value,
        dateTo: document.getElementById('filterDateTo').value
    };
    updateFilterBadge();
    filterCampaigns();
};

window.resetFilters = function() {
    document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
    document.querySelectorAll('.multiselect-dropdown .dropdown-label').forEach(function(label) {
        var dropdown = label.closest('.multiselect-dropdown');
        var filter = dropdown ? dropdown.dataset.filter : '';
        if (filter === 'statuses') label.textContent = 'All Statuses';
        else if (filter === 'channels') label.textContent = 'All Channels';
        else if (filter === 'accounts') label.textContent = 'All Accounts';
        else label.textContent = 'Any';
    });
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    activeFilters = {};
    updateFilterBadge();
    filterCampaigns();
};

function updateFilterBadge() {
    var count = Object.values(activeFilters).filter(function(v) { 
        return Array.isArray(v) ? v.length > 0 : v !== ''; 
    }).length;
    var badge = document.getElementById('activeFiltersBadge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

var currentSortField = null;
var currentSortDirection = 'asc';

function toggleSort(field) {
    if (currentSortField === field) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortField = field;
        currentSortDirection = 'asc';
    }
    
    document.querySelectorAll('.sortable-header').forEach(function(th) {
        th.classList.remove('sort-asc', 'sort-desc');
        var icon = th.querySelector('.sort-icon');
        if (icon) icon.className = 'fas fa-sort sort-icon';
    });
    
    var activeHeader = document.querySelector('.sortable-header[data-sort="' + field + '"]');
    if (activeHeader) {
        activeHeader.classList.add('sort-' + currentSortDirection);
        var icon = activeHeader.querySelector('.sort-icon');
        if (icon) {
            icon.className = 'fas fa-sort-' + (currentSortDirection === 'asc' ? 'up' : 'down') + ' sort-icon';
        }
    }
    
    sortCampaigns(field, currentSortDirection);
}

function sortCampaigns(field, direction) {
    var tbody = document.getElementById('campaignsTableBody');
    var rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
    
    rows.sort(function(a, b) {
        var result = 0;
        if (field === 'name') {
            result = (a.dataset.name || '').localeCompare(b.dataset.name || '');
        } else if (field === 'recipients') {
            result = (parseInt(a.dataset.recipientsTotal) || 0) - (parseInt(b.dataset.recipientsTotal) || 0);
        } else if (field === 'date') {
            result = new Date(a.dataset.sendDate) - new Date(b.dataset.sendDate);
        } else if (field === 'account') {
            result = (a.dataset.accountName || '').localeCompare(b.dataset.accountName || '');
        }
        return direction === 'desc' ? -result : result;
    });
    
    rows.forEach(function(row) { tbody.appendChild(row); });
    filterCampaigns();
}

function openCampaignDrawer(campaignId) {
    var row = document.querySelector('tr[data-id="' + campaignId + '"]');
    if (!row) return;

    var name = row.dataset.name;
    var accountName = row.querySelector('.account-link') ? row.querySelector('.account-link').textContent : 'Unknown';
    var accountId = row.dataset.account || '1';
    var channel = row.dataset.channel;
    var status = row.dataset.status;
    var recipientsTotal = parseInt(row.dataset.recipientsTotal) || 0;
    var recipientsDelivered = row.dataset.recipientsDelivered ? parseInt(row.dataset.recipientsDelivered) : null;
    var sendDate = row.dataset.sendDate;
    var senderId = row.dataset.senderId || '-';
    var rcsAgent = row.dataset.rcsAgent || '';
    var tags = row.dataset.tags || '';
    var template = row.dataset.template || '';

    document.getElementById('drawerCampaignName').textContent = name.charAt(0).toUpperCase() + name.slice(1);
    document.getElementById('drawerCampaignId').textContent = campaignId;
    document.getElementById('drawerAccountName').textContent = accountName;
    document.getElementById('drawerAccountLink').href = '/admin/accounts/details/' + accountId;
    document.getElementById('drawerRecipientsTotal').textContent = recipientsTotal.toLocaleString();
    document.getElementById('drawerSenderId').textContent = senderId;

    var sendTimeLabel = document.getElementById('drawerSendTimeLabel');
    var sendTime = document.getElementById('drawerSendTime');
    sendTimeLabel.textContent = status === 'scheduled' ? 'Scheduled:' : 'Sent:';
    sendTime.textContent = formatDate(sendDate);

    document.getElementById('drawerRcsAgentRow').style.display = rcsAgent ? '' : 'none';
    document.getElementById('drawerRcsAgent').textContent = rcsAgent;
    document.getElementById('drawerTemplateRow').style.display = template ? '' : 'none';
    document.getElementById('drawerTemplate').textContent = template;

    if (tags) {
        var tagHtml = tags.split(',').map(function(t) {
            return '<span class="badge badge-pastel-secondary me-1">' + t.trim() + '</span>';
        }).join('');
        document.getElementById('drawerTags').innerHTML = tagHtml;
    } else {
        document.getElementById('drawerTags').textContent = '-';
    }

    var channelBadge = document.getElementById('drawerChannelBadge');
    channelBadge.className = 'badge';
    channelBadge.style.background = '#d4e5f7';
    channelBadge.style.color = '#2563a8';
    channelBadge.textContent = channel === 'sms_only' ? 'SMS' : channel === 'basic_rcs' ? 'Basic RCS' : 'Rich RCS';

    var statusBadge = document.getElementById('drawerStatusBadge');
    statusBadge.className = 'badge';
    if (status === 'scheduled') {
        statusBadge.style.cssText = 'background: #fff3cd; color: #856404;';
        statusBadge.textContent = 'Scheduled';
    } else if (status === 'sending') {
        statusBadge.style.cssText = 'background: #cfe2ff; color: #084298;';
        statusBadge.textContent = 'Sending';
    } else if (status === 'cancelled') {
        statusBadge.style.cssText = 'background: #e9ecef; color: #6c757d;';
        statusBadge.textContent = 'Cancelled';
    } else {
        statusBadge.style.cssText = 'background: #d1e7dd; color: #0f5132;';
        statusBadge.textContent = 'Complete';
    }

    var liveStateBadge = document.getElementById('drawerLiveStateBadge');
    liveStateBadge.className = 'badge';
    if (status === 'scheduled') {
        liveStateBadge.style.cssText = 'background: #e9ecef; color: #6c757d;';
        liveStateBadge.textContent = 'Pending';
    } else if (status === 'sending') {
        liveStateBadge.style.cssText = 'background: #d1e7dd; color: #0d6e5a;';
        liveStateBadge.textContent = 'Live';
    } else if (status === 'cancelled') {
        liveStateBadge.style.cssText = 'background: #e9ecef; color: #6c757d;';
        liveStateBadge.textContent = 'Cancelled';
    } else {
        liveStateBadge.style.cssText = 'background: #d1e7dd; color: #0d6e5a;';
        liveStateBadge.textContent = 'Complete';
    }

    var failed = 0;
    var deliveryRate = '-';
    if (recipientsDelivered !== null) {
        failed = recipientsTotal - recipientsDelivered;
        deliveryRate = recipientsTotal > 0 ? ((recipientsDelivered / recipientsTotal) * 100).toFixed(1) + '%' : '-';
        document.getElementById('drawerRecipientsDelivered').textContent = recipientsDelivered.toLocaleString();
        document.getElementById('drawerRecipientsFailed').textContent = failed.toLocaleString();
        document.getElementById('drawerDeliveryRate').textContent = deliveryRate;
        document.getElementById('drawerDeliveryRate').className = 'fs-5 fw-bold ' + (parseFloat(deliveryRate) >= 95 ? 'text-success' : parseFloat(deliveryRate) >= 80 ? 'text-warning' : 'text-danger');
    } else {
        document.getElementById('drawerRecipientsDelivered').textContent = '-';
        document.getElementById('drawerRecipientsFailed').textContent = '-';
        document.getElementById('drawerDeliveryRate').textContent = '-';
        document.getElementById('drawerDeliveryRate').className = 'fs-5 fw-bold text-muted';
    }

    updateDeliveryOutcomes(status, recipientsTotal, recipientsDelivered);
    updateChannelSplit(channel, status, recipientsTotal, recipientsDelivered);
    updateEngagementMetrics(channel, status, recipientsTotal, recipientsDelivered, row.dataset.hasTracking === 'yes');
    updateCostSummary(channel, status, recipientsTotal, recipientsDelivered);
    updateMessagePreview(channel, senderId, rcsAgent, template);
    updateStatusActions(status);

    campaignDrawer.show();
}

function updateDeliveryOutcomes(status, total, delivered) {
    var card = document.getElementById('deliveryOutcomesCard');
    if (status === 'scheduled' || total === 0) { card.style.display = 'none'; return; }
    card.style.display = '';
    
    var deliveredCount = delivered !== null ? delivered : 0;
    var pending = status === 'sending' ? Math.floor((total - deliveredCount) * 0.7) : 0;
    var undeliverable = status === 'sending' ? Math.floor((total - deliveredCount) * 0.3) : total - deliveredCount;
    
    document.getElementById('barDelivered').style.width = (deliveredCount / total * 100) + '%';
    document.getElementById('barPending').style.width = (pending / total * 100) + '%';
    document.getElementById('barUndeliverable').style.width = (undeliverable / total * 100) + '%';
    
    document.getElementById('outcomeDelivered').textContent = deliveredCount.toLocaleString();
    document.getElementById('outcomeDeliveredPct').textContent = (deliveredCount / total * 100).toFixed(1) + '%';
    document.getElementById('outcomePending').textContent = pending.toLocaleString();
    document.getElementById('outcomePendingPct').textContent = (pending / total * 100).toFixed(1) + '%';
    document.getElementById('outcomeUndeliverable').textContent = undeliverable.toLocaleString();
    document.getElementById('outcomeUndeliverablePct').textContent = (undeliverable / total * 100).toFixed(1) + '%';
}

function updateChannelSplit(channel, status, total, delivered) {
    var card = document.getElementById('channelSplitCard');
    if (status === 'scheduled' || total === 0) { card.style.display = 'none'; return; }
    card.style.display = '';
    
    var deliveredCount = delivered !== null ? delivered : total;
    var smsCount = channel === 'sms_only' ? deliveredCount : Math.floor(deliveredCount * 0.15);
    var rcsCount = channel === 'sms_only' ? 0 : deliveredCount - smsCount;
    
    document.getElementById('channelSmsPercent').textContent = (smsCount / deliveredCount * 100).toFixed(1) + '%';
    document.getElementById('channelSmsCount').textContent = smsCount.toLocaleString() + ' msgs';
    document.getElementById('channelRcsPercent').textContent = (rcsCount / deliveredCount * 100).toFixed(1) + '%';
    document.getElementById('channelRcsCount').textContent = rcsCount.toLocaleString() + ' msgs';
    document.getElementById('channelBarSms').style.width = (smsCount / deliveredCount * 100) + '%';
    document.getElementById('channelBarRcs').style.width = (rcsCount / deliveredCount * 100) + '%';
}

function updateEngagementMetrics(channel, status, total, delivered, hasTracking) {
    var card = document.getElementById('engagementMetricsCard');
    var isRcs = channel === 'basic_rcs' || channel === 'rich_rcs';
    if ((!hasTracking && !isRcs) || status === 'scheduled') { card.style.display = 'none'; return; }
    card.style.display = '';
    
    var deliveredCount = delivered !== null ? delivered : total;
    document.getElementById('trackingMetrics').style.display = hasTracking ? '' : 'none';
    if (hasTracking) {
        var uniqueClicks = Math.floor(deliveredCount * 0.12);
        document.getElementById('metricTotalClicks').textContent = Math.floor(uniqueClicks * 1.4).toLocaleString();
        document.getElementById('metricUniqueClicks').textContent = uniqueClicks.toLocaleString();
        document.getElementById('metricCtr').textContent = (uniqueClicks / deliveredCount * 100).toFixed(1) + '%';
    }
    
    document.getElementById('rcsSeenMetrics').style.display = isRcs ? '' : 'none';
    if (isRcs) {
        var rcsDelivered = Math.floor(deliveredCount * 0.85);
        var seenCount = Math.floor(rcsDelivered * 0.72);
        document.getElementById('metricSeenPercent').textContent = (seenCount / rcsDelivered * 100).toFixed(1) + '%';
        document.getElementById('metricSeenCount').textContent = seenCount.toLocaleString() + ' of ' + rcsDelivered.toLocaleString() + ' RCS';
        document.getElementById('metricSeenBar').style.width = (seenCount / rcsDelivered * 100) + '%';
    }
}

function updateCostSummary(channel, status, total, delivered) {
    var card = document.getElementById('costSummaryCard');
    if (status === 'scheduled') { card.style.display = 'none'; return; }
    card.style.display = '';
    
    var isRcs = channel === 'basic_rcs' || channel === 'rich_rcs';
    var isComplete = status === 'complete';
    var deliveredCount = delivered !== null ? delivered : total;
    var smsUnitPrice = 0.038, rcsUnitPrice = 0.025;
    
    document.getElementById('costLabel').textContent = isComplete ? 'Final Cost' : 'Estimated Cost';
    document.getElementById('costStatusBadge').style.cssText = isComplete ? 'background-color: #d4edda; color: #155724;' : 'background-color: #fff3cd; color: #856404;';
    document.getElementById('costStatusBadge').textContent = isComplete ? 'Final' : 'Estimated';
    document.getElementById('costTotalLabel').textContent = isComplete ? 'Total' : 'Est. Total';
    document.getElementById('costDisclaimer').style.display = isComplete ? 'none' : '';
    document.getElementById('costDisclaimerText').textContent = 'Final cost will be calculated when delivery completes.';
    
    var totalCost = 0;
    if (!isRcs) {
        document.getElementById('smsCostSection').style.display = '';
        document.getElementById('rcsCostSection').style.display = 'none';
        document.getElementById('smsCostCount').textContent = deliveredCount.toLocaleString() + ' msgs';
        document.getElementById('smsCostUnit').textContent = '£' + smsUnitPrice.toFixed(3);
        totalCost = deliveredCount * smsUnitPrice;
    } else {
        document.getElementById('smsCostSection').style.display = 'none';
        document.getElementById('rcsCostSection').style.display = '';
        var smsFallbackCount = Math.floor(deliveredCount * 0.15);
        var rcsCount = deliveredCount - smsFallbackCount;
        document.getElementById('rcsFallbackCount').textContent = smsFallbackCount.toLocaleString() + ' msgs';
        document.getElementById('rcsFallbackCost').textContent = '£' + (smsFallbackCount * smsUnitPrice).toFixed(2);
        document.getElementById('rcsMessageCount').textContent = rcsCount.toLocaleString() + ' msgs';
        document.getElementById('rcsMessageCost').textContent = '£' + (rcsCount * rcsUnitPrice).toFixed(2);
        totalCost = (smsFallbackCount * smsUnitPrice) + (rcsCount * rcsUnitPrice);
    }
    document.getElementById('costTotal').textContent = '£' + totalCost.toFixed(2);
}

var campaignPreviewMode = 'rcs';
var currentCampaignChannel = 'sms_only';

function toggleCampaignPreview(mode) {
    campaignPreviewMode = mode;
    var rcsBtn = document.getElementById('campaignPreviewRCSBtn');
    var smsBtn = document.getElementById('campaignPreviewSMSBtn');
    if (mode === 'rcs') {
        rcsBtn.style.cssText = 'background: #1e3a5f; color: white; border: 1px solid #1e3a5f;';
        smsBtn.style.cssText = 'background: white; color: #1e3a5f; border: 1px solid #1e3a5f;';
    } else {
        smsBtn.style.cssText = 'background: #1e3a5f; color: white; border: 1px solid #1e3a5f;';
        rcsBtn.style.cssText = 'background: white; color: #1e3a5f; border: 1px solid #1e3a5f;';
    }
}

function updateMessagePreview(channel, senderId, rcsAgent, template) {
    var container = document.getElementById('campaignPreviewContainer');
    var toggleContainer = document.getElementById('campaignPreviewToggle');
    
    if (channel === 'basic_rcs' || channel === 'rich_rcs') {
        toggleContainer.classList.remove('d-none');
    } else {
        toggleContainer.classList.add('d-none');
    }
    
    if (typeof RcsPreviewRenderer !== 'undefined') {
        var previewConfig = {
            senderId: senderId || 'QuickSMS',
            channel: channel === 'sms_only' ? 'sms' : channel,
            agent: { name: rcsAgent || 'QuickSMS Brand', verified: true },
            message: { type: 'text', body: 'Hi @{{firstName}}, thank you for being a valued customer! Reply STOP to opt out.' }
        };
        container.innerHTML = RcsPreviewRenderer.renderPreview(previewConfig);
    } else {
        container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-mobile-alt fa-2x mb-2"></i><p class="small mb-0">Preview will appear here</p></div>';
    }
}

function updateStatusActions(status) {
    document.getElementById('scheduledActions').style.display = status === 'scheduled' ? '' : 'none';
    document.getElementById('sendingNotice').style.display = status === 'sending' ? '' : 'none';
}

function formatDate(dateStr) {
    var date = new Date(dateStr);
    return date.toLocaleDateString('en-GB') + ' ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}

function showComingSoon(message) {
    if (!comingSoonModal) {
        comingSoonModal = new bootstrap.Modal(document.getElementById('comingSoonModal'));
    }
    document.getElementById('comingSoonMessage').textContent = message;
    comingSoonModal.show();
}

function handleSearch(value) {
    var clearBtn = document.getElementById('clearSearchBtn');
    clearBtn.style.display = value.length > 0 ? 'block' : 'none';
    filterCampaigns();
}

document.getElementById('filterPillBtn').addEventListener('click', function() {
    var panel = document.getElementById('filterPanel');
    var bsCollapse = new bootstrap.Collapse(panel, { toggle: true });
});

document.getElementById('filterPanel').addEventListener('shown.bs.collapse', function() {
    document.getElementById('filterPillBtn').classList.add('active');
});

document.getElementById('filterPanel').addEventListener('hidden.bs.collapse', function() {
    document.getElementById('filterPillBtn').classList.remove('active');
});

document.getElementById('campaignActionModal').addEventListener('shown.bs.modal', function() {
    var reasonInput = document.getElementById('actionReason');
    if (reasonInput) {
        reasonInput.addEventListener('input', validateActionReason);
        validateActionReason();
    }
});

function validateActionReason() {
    var reasonInput = document.getElementById('actionReason');
    var confirmBtn = document.getElementById('actionModalConfirmBtn');
    if (!reasonInput || !confirmBtn || !pendingCampaignAction) return;
    
    var action = pendingCampaignAction.action;
    var reason = reasonInput.value.trim();
    
    if (action === 'resume') {
        confirmBtn.disabled = false;
        reasonInput.classList.remove('is-invalid');
        return;
    }
    
    if (reason.length >= 10) {
        confirmBtn.disabled = false;
        reasonInput.classList.remove('is-invalid');
        reasonInput.classList.add('is-valid');
    } else {
        confirmBtn.disabled = true;
        reasonInput.classList.remove('is-valid');
    }
}

function handleCampaignAction(event, action, campaignId) {
    console.log('[CampaignAction] Triggered:', action, campaignId);
    event.preventDefault();
    event.stopPropagation();
    
    // Close any open dropdowns first
    var openDropdowns = document.querySelectorAll('.dropdown-menu.show');
    openDropdowns.forEach(function(dropdown) {
        dropdown.classList.remove('show');
    });
    var openButtons = document.querySelectorAll('[data-bs-toggle="dropdown"][aria-expanded="true"]');
    openButtons.forEach(function(btn) {
        btn.setAttribute('aria-expanded', 'false');
    });
    
    // Small delay to ensure dropdown is closed before modal opens
    setTimeout(function() {
        console.log('[CampaignAction] Executing:', action);
        switch(action) {
            case 'view':
                openCampaignDrawer(campaignId);
                break;
            case 'suspend':
                console.log('[CampaignAction] Calling confirmSuspendCampaign');
                confirmSuspendCampaign(campaignId);
                break;
            case 'resume':
                console.log('[CampaignAction] Calling confirmResumeCampaign');
                confirmResumeCampaign(campaignId);
                break;
            case 'cancel':
                console.log('[CampaignAction] Calling confirmCancelCampaign');
                confirmCancelCampaign(campaignId);
                break;
        }
    }, 50);
}

var campaignActionModal = null;
var pendingCampaignAction = null;

function getCampaignData(campaignId) {
    console.log('[getCampaignData] Looking for row with data-id:', campaignId);
    var row = document.querySelector('tr[data-id="' + campaignId + '"]');
    console.log('[getCampaignData] Row found:', row ? 'yes' : 'no');
    if (!row) return null;
    var nameCell = row.querySelector('h6');
    var data = {
        id: campaignId,
        name: nameCell ? nameCell.textContent.trim() : row.dataset.name || 'Unknown',
        accountName: row.querySelector('.account-link')?.textContent?.trim() || 'Unknown',
        status: row.dataset.status || 'unknown',
        recipientsTotal: row.dataset.recipientsTotal || '0'
    };
    console.log('[getCampaignData] Data:', data);
    return data;
}

function confirmSuspendCampaign(campaignId) {
    console.log('[confirmSuspendCampaign] Called with:', campaignId);
    var campaign = getCampaignData(campaignId);
    console.log('[confirmSuspendCampaign] Campaign data:', campaign);
    if (!campaign) {
        console.error('[confirmSuspendCampaign] No campaign data found for:', campaignId);
        return;
    }
    
    pendingCampaignAction = { action: 'suspend', campaignId: campaignId, campaign: campaign };
    
    document.getElementById('actionModalTitle').textContent = 'Suspend Campaign';
    document.getElementById('actionModalTitle').className = 'modal-title text-warning';
    document.getElementById('actionModalIcon').className = 'fas fa-pause-circle text-warning';
    document.getElementById('actionModalBody').innerHTML = `
        <div class="alert alert-warning mb-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> Suspending this campaign will pause message delivery.
        </div>
        <p>You are about to suspend the campaign:</p>
        <div class="border rounded p-3 bg-light mb-3">
            <strong>${campaign.name}</strong>
            <br><small class="text-muted">Account: ${campaign.accountName}</small>
            <br><small class="text-muted">Recipients: ${Number(campaign.recipientsTotal).toLocaleString()}</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Reason for suspension <span class="text-danger">*</span></label>
            <textarea class="form-control" id="actionReason" rows="2" placeholder="Enter reason for suspending this campaign..." required></textarea>
        </div>
    `;
    document.getElementById('actionModalConfirmBtn').textContent = 'Suspend Campaign';
    document.getElementById('actionModalConfirmBtn').className = 'btn btn-warning';
    document.getElementById('actionModalConfirmBtn').disabled = true;
    
    try {
        var modalEl = document.getElementById('campaignActionModal');
        console.log('[confirmSuspendCampaign] Modal element found:', modalEl ? 'yes' : 'no');
        if (!campaignActionModal) {
            campaignActionModal = new bootstrap.Modal(modalEl);
        }
        console.log('[confirmSuspendCampaign] Showing modal...');
        campaignActionModal.show();
        console.log('[confirmSuspendCampaign] Modal shown successfully');
    } catch (e) {
        console.error('[confirmSuspendCampaign] Error showing modal:', e);
    }
}

function confirmResumeCampaign(campaignId) {
    var campaign = getCampaignData(campaignId);
    if (!campaign) return;
    
    pendingCampaignAction = { action: 'resume', campaignId: campaignId, campaign: campaign };
    
    document.getElementById('actionModalTitle').textContent = 'Resume Campaign';
    document.getElementById('actionModalTitle').className = 'modal-title text-success';
    document.getElementById('actionModalIcon').className = 'fas fa-play-circle text-success';
    document.getElementById('actionModalBody').innerHTML = `
        <p>You are about to resume the campaign:</p>
        <div class="border rounded p-3 bg-light mb-3">
            <strong>${campaign.name}</strong>
            <br><small class="text-muted">Account: ${campaign.accountName}</small>
            <br><small class="text-muted">Recipients: ${Number(campaign.recipientsTotal).toLocaleString()}</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Reason for resuming</label>
            <textarea class="form-control" id="actionReason" rows="2" placeholder="Optional: Enter reason for resuming this campaign..."></textarea>
        </div>
    `;
    document.getElementById('actionModalConfirmBtn').textContent = 'Resume Campaign';
    document.getElementById('actionModalConfirmBtn').className = 'btn btn-success';
    
    if (!campaignActionModal) {
        campaignActionModal = new bootstrap.Modal(document.getElementById('campaignActionModal'));
    }
    campaignActionModal.show();
}

function confirmCancelCampaign(campaignId) {
    console.log('[confirmCancelCampaign] Called with:', campaignId);
    var campaign = getCampaignData(campaignId);
    console.log('[confirmCancelCampaign] Campaign data:', campaign);
    if (!campaign) {
        console.error('[confirmCancelCampaign] No campaign data found for:', campaignId);
        return;
    }
    
    pendingCampaignAction = { action: 'cancel', campaignId: campaignId, campaign: campaign };
    
    document.getElementById('actionModalTitle').textContent = 'Cancel Campaign';
    document.getElementById('actionModalTitle').className = 'modal-title text-danger';
    document.getElementById('actionModalIcon').className = 'fas fa-ban text-danger';
    document.getElementById('actionModalBody').innerHTML = `
        <div class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> This action cannot be undone. Any pending messages will not be sent.
        </div>
        <p>You are about to cancel the campaign:</p>
        <div class="border rounded p-3 bg-light mb-3">
            <strong>${campaign.name}</strong>
            <br><small class="text-muted">Account: ${campaign.accountName}</small>
            <br><small class="text-muted">Recipients: ${Number(campaign.recipientsTotal).toLocaleString()}</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Reason for cancellation <span class="text-danger">*</span></label>
            <textarea class="form-control" id="actionReason" rows="2" placeholder="Enter reason for cancelling this campaign..." required></textarea>
        </div>
    `;
    document.getElementById('actionModalConfirmBtn').textContent = 'Cancel Campaign';
    document.getElementById('actionModalConfirmBtn').className = 'btn btn-danger';
    document.getElementById('actionModalConfirmBtn').disabled = true;
    
    try {
        var modalEl = document.getElementById('campaignActionModal');
        console.log('[confirmCancelCampaign] Modal element found:', modalEl ? 'yes' : 'no');
        if (!campaignActionModal) {
            campaignActionModal = new bootstrap.Modal(modalEl);
        }
        console.log('[confirmCancelCampaign] Showing modal...');
        campaignActionModal.show();
        console.log('[confirmCancelCampaign] Modal shown successfully');
    } catch (e) {
        console.error('[confirmCancelCampaign] Error showing modal:', e);
    }
}

function executeCampaignAction() {
    if (!pendingCampaignAction) return;
    
    var reason = document.getElementById('actionReason')?.value?.trim() || '';
    var action = pendingCampaignAction.action;
    var campaign = pendingCampaignAction.campaign;
    
    if ((action === 'suspend' || action === 'cancel') && reason.length < 10) {
        var reasonInput = document.getElementById('actionReason');
        if (reasonInput) {
            reasonInput.classList.add('is-invalid');
            var feedback = reasonInput.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                reasonInput.parentNode.appendChild(feedback);
            }
            feedback.textContent = 'Please provide a reason (minimum 10 characters). Current: ' + reason.length + ' characters.';
        }
        return;
    }
    
    var adminEmail = 'admin@quicksms.co.uk';
    var timestamp = new Date().toISOString();
    
    var auditEntry = {
        timestamp: timestamp,
        eventType: 'CAMPAIGN_' + action.toUpperCase(),
        severity: action === 'cancel' ? 'CRITICAL' : 'HIGH',
        adminEmail: adminEmail,
        campaignId: campaign.id,
        campaignName: campaign.name,
        accountName: campaign.accountName,
        previousStatus: campaign.status,
        newStatus: action === 'cancel' ? 'cancelled' : (action === 'suspend' ? 'suspended' : 'sending'),
        reason: reason,
        recipientsAffected: campaign.recipientsTotal
    };
    
    console.log('[ADMIN_AUDIT][' + auditEntry.severity + ']', JSON.stringify(auditEntry));
    
    var row = document.querySelector('tr[data-id="' + campaign.id + '"]');
    if (row) {
        var statusCell = row.querySelector('.status-cell');
        if (statusCell) {
            if (action === 'cancel') {
                row.dataset.status = 'cancelled';
                statusCell.innerHTML = '<span class="badge badge-pastel-secondary"><i class="fas fa-ban me-1"></i>Cancelled</span>';
            } else if (action === 'suspend') {
                row.dataset.status = 'suspended';
                statusCell.innerHTML = '<span class="badge badge-pastel-warning"><i class="fas fa-pause-circle me-1"></i>Suspended</span>';
            } else if (action === 'resume') {
                row.dataset.status = 'sending';
                statusCell.innerHTML = '<span class="badge badge-pastel-primary"><i class="fas fa-paper-plane me-1"></i>Sending</span>';
            }
        }
    }
    
    campaignActionModal.hide();
    pendingCampaignAction = null;
    
    var actionNames = { suspend: 'suspended', resume: 'resumed', cancel: 'cancelled' };
    showSuccessToast('Campaign ' + actionNames[action] + ' successfully.');
}

function showSuccessToast(message) {
    var toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="toast show align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.parentElement.remove()"></button>
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}

function exportCampaigns() {
    showComingSoon('Campaign export functionality will be available when backend integration is complete.');
}
</script>

<!-- Campaign Action Confirmation Modal -->
<div class="modal fade" id="campaignActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalTitle">
                    <i class="fas fa-question-circle me-2" id="actionModalIcon"></i>
                    Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="actionModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="actionModalConfirmBtn" onclick="executeCampaignAction()">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endpush

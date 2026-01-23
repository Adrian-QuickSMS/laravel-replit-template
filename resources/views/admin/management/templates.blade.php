@extends('layouts.admin')

@section('title', 'Global Templates Library')

@push('styles')
<style>
.templates-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.templates-header h2 {
    margin: 0;
    font-weight: 600;
}
.templates-header p {
    margin: 0;
    color: #6c757d;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(30, 58, 95, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.wizard-steps {
    display: flex;
    gap: 1.5rem;
}
.wizard-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    opacity: 0.5;
}
.wizard-step.active {
    opacity: 1;
}
.wizard-step.completed {
    opacity: 1;
}
.step-number {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}
.wizard-step.active .step-number {
    background: #fff;
    color: #1e3a5f;
}
.wizard-step.completed .step-number {
    background: #28a745;
    color: #fff;
}
.step-label {
    font-size: 0.875rem;
}
.tpl-builder-layout {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    min-height: 100%;
}
.tpl-builder-left {
    flex: 1;
    min-width: 0;
}
.tpl-builder-right {
    flex: 0 0 320px;
    width: 320px;
}
.loading-overlay,
.error-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.95);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
}
.trigger-option-display {
    background: #f8f9fa;
    color: #1e3a5f;
    min-width: 120px;
}
@media (max-width: 991.98px) {
    .tpl-builder-layout {
        flex-direction: column;
    }
    .tpl-builder-right {
        flex: 0 0 auto;
        width: 100%;
    }
}
.empty-state-icon i {
    font-size: 2rem;
    color: #1e3a5f;
}
.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.templates-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: visible;
}
.templates-table-container .dropdown-menu {
    z-index: 1050;
}
.templates-table {
    width: 100%;
    margin: 0;
    table-layout: fixed;
}
.templates-table thead th {
    background: #f8f9fa;
    padding: 0.5rem 0.35rem;
    font-weight: 600;
    font-size: 0.75rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
    overflow: hidden;
    text-overflow: ellipsis;
}
.templates-table thead th:first-child { width: 12%; }  /* Account */
.templates-table thead th:nth-child(2) { width: 12%; } /* Name */
.templates-table thead th:nth-child(3) { width: 9%; }  /* ID */
.templates-table thead th:nth-child(4) { width: 5%; }  /* Ver */
.templates-table thead th:nth-child(5) { width: 9%; }  /* Channel */
.templates-table thead th:nth-child(6) { width: 7%; }  /* Trigger */
.templates-table thead th:nth-child(7) { width: 14%; } /* Preview */
.templates-table thead th:nth-child(8) { width: 10%; } /* Scope */
.templates-table thead th:nth-child(9) { width: 7%; }  /* Status */
.templates-table thead th:nth-child(10) { width: 9%; } /* Updated */
.templates-table thead th:last-child { 
    width: 5%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.templates-table thead th:hover {
    background: #e9ecef;
}
.templates-table thead th:last-child:hover {
    background: #f8f9fa;
}
.templates-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.templates-table thead th.sorted .sort-icon {
    opacity: 1;
    color: #1e3a5f;
}
.templates-table tbody td {
    padding: 0.5rem 0.35rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.8rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 0;
}
.templates-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.templates-table tbody tr:last-child td {
    border-bottom: none;
}
.templates-table tbody tr:hover td {
    background: #f8f9fa;
}
.templates-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.template-name {
    font-weight: 500;
    color: #343a40;
}
.template-id {
    font-family: monospace;
    font-size: 0.8rem;
    color: #6c757d;
}
.account-name {
    font-weight: 500;
    color: #1e3a5f;
}
.account-id {
    font-size: 0.7rem;
    color: #6c757d;
    display: block;
}
.badge-sms {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-basic-rcs {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-rich-rcs {
    background: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
}
.badge-api {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-portal {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-email {
    background: rgba(214, 83, 193, 0.15);
    color: #D653C1;
}
.badge-draft {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-live {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-paused {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-archived {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-rich-card {
    background: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
}
.badge-carousel {
    background: rgba(214, 83, 193, 0.15);
    color: #D653C1;
}
.content-preview {
    color: #6c757d;
    font-size: 0.8rem;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
.channel-text {
    font-size: 0.85rem;
    color: #495057;
}
.version-text {
    font-size: 0.85rem;
    color: #1e3a5f;
    font-weight: 500;
}
.access-scope {
    font-size: 0.8rem;
    color: #495057;
}
.search-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
    flex-wrap: wrap;
}
.search-box {
    flex: 1;
    max-width: 300px;
    min-width: 200px;
}
.filters-panel {
    background-color: rgba(30, 58, 95, 0.08);
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 0 1rem 1rem;
}
.filters-panel .form-label {
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.filters-panel .form-select,
.filters-panel .form-control {
    font-size: 0.875rem;
}
.filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: flex-end;
}
.multiselect-dropdown .dropdown-menu {
    max-height: 250px;
    overflow-y: auto;
    min-width: 200px;
}
.multiselect-dropdown .form-check {
    padding: 0.25rem 0.5rem;
    margin: 0;
}
.multiselect-dropdown .form-check:hover {
    background-color: #f8f9fa;
}
.multiselect-dropdown .dropdown-toggle {
    background-color: #fff;
    border: 1px solid #ced4da;
    color: #495057;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.multiselect-dropdown .dropdown-toggle::after {
    margin-left: auto;
}
.active-filters {
    padding: 0.5rem 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}
.active-filters:empty {
    display: none;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background-color: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.filter-chip .chip-label {
    margin-right: 0.25rem;
    color: #6c757d;
}
.filter-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
    font-size: 0.7rem;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
}
.dropdown-menu {
    min-width: 120px;
}
.dropdown-item {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}
.dropdown-item i {
    width: 16px;
    margin-right: 0.5rem;
}
.action-menu-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}
.action-menu-btn:hover {
    color: #1e3a5f;
}
.archived-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}
.archived-row:hover {
    opacity: 0.8;
}
.account-search-dropdown {
    position: relative;
}
.account-search-dropdown .dropdown-menu {
    width: 100%;
    max-height: 250px;
    overflow-y: auto;
}
.account-search-dropdown .dropdown-item {
    padding: 0.5rem 1rem;
    cursor: pointer;
}
.account-search-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}
.account-search-dropdown .dropdown-item.active {
    background-color: #1e3a5f;
    color: white;
}
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-top: 1px solid #e9ecef;
}
.pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.pagination .page-link {
    color: #1e3a5f;
    border-color: #e9ecef;
}
.pagination .page-item.active .page-link {
    background-color: #1e3a5f;
    border-color: #1e3a5f;
}
.pagination .page-item.disabled .page-link {
    color: #adb5bd;
}
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}
.table-loading {
    position: relative;
    min-height: 200px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Management</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Global Templates Library</a></li>
        </ol>
    </div>
</div>
<div class="container-fluid">
    <div id="emptyState" class="empty-state" style="display: none;">
        <div class="empty-state-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <h4>No templates found</h4>
        <p>No message templates exist in the system yet. Templates are created by customers through the Customer Portal.</p>
    </div>

    <div class="card" id="templatesTableContainer">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="card-title mb-0" style="color: #1e3a5f;">Global Templates Library</h5>
            <div class="d-flex align-items-center gap-2">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="showArchivedToggle">
                    <label class="form-check-label small" for="showArchivedToggle">Show Archived</label>
                </div>
                <button type="button" class="btn btn-admin-outline btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i>Filters
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="templateSearch" placeholder="Search by name, ID or account...">
                    </div>
                </div>
            </div>

            <div class="collapse mb-3" id="filtersPanel">
                <div class="card card-body border-0 rounded-3" style="background-color: rgba(30, 58, 95, 0.08);">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-bold">Account</label>
                        <div class="account-search-dropdown dropdown">
                            <input type="text" class="form-control form-control-sm" id="accountSearchInput" placeholder="All Accounts" data-bs-toggle="dropdown" autocomplete="off">
                            <input type="hidden" id="selectedAccountId" value="">
                            <ul class="dropdown-menu w-100" id="accountDropdownMenu">
                                <li><a class="dropdown-item active" href="#" data-account-id="" data-account-name="All Accounts">All Accounts</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
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
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="sms" id="channelSms"><label class="form-check-label small" for="channelSms">SMS</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="basic_rcs" id="channelBasicRcs"><label class="form-check-label small" for="channelBasicRcs">Basic RCS + SMS</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="rich_rcs" id="channelRichRcs"><label class="form-check-label small" for="channelRichRcs">Rich RCS + SMS</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-bold">Trigger</label>
                        <div class="dropdown multiselect-dropdown" data-filter="triggers">
                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                <span class="dropdown-label">All Triggers</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="api" id="triggerApi"><label class="form-check-label small" for="triggerApi">API</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="portal" id="triggerPortal"><label class="form-check-label small" for="triggerPortal">Portal</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="email" id="triggerEmail"><label class="form-check-label small" for="triggerEmail">Email-to-SMS</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-bold">Status</label>
                        <div class="dropdown multiselect-dropdown" data-filter="statuses">
                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                <span class="dropdown-label">All Statuses</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="draft" id="statusDraft"><label class="form-check-label small" for="statusDraft">Draft</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="live" id="statusLive"><label class="form-check-label small" for="statusLive">Live</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="paused" id="statusPaused"><label class="form-check-label small" for="statusPaused">Paused</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="archived" id="statusArchived"><label class="form-check-label small" for="statusArchived">Archived</label></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-admin-primary btn-sm" id="applyFiltersBtn">
                            <i class="fas fa-check me-1"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-admin-outline btn-sm" id="resetFiltersBtn">
                            <i class="fas fa-undo me-1"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

            <div class="active-filters mb-3" id="activeFilters"></div>

            <div class="templates-table-container table-loading" id="tableContainer">
                <div class="loading-overlay d-none" id="tableLoadingOverlay">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <table class="templates-table">
                    <thead>
                        <tr>
                            <th data-sort="accountName" onclick="sortTable('accountName')">Account <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="name" onclick="sortTable('name')">Name <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="templateId" onclick="sortTable('templateId')">ID <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="version" onclick="sortTable('version')">Ver <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="channel" onclick="sortTable('channel')">Channel <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="trigger" onclick="sortTable('trigger')">Trigger <i class="fas fa-sort sort-icon"></i></th>
                            <th>Preview</th>
                            <th data-sort="accessScope" onclick="sortTable('accessScope')">Scope <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="status" onclick="sortTable('status')">Status <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="lastUpdated" onclick="sortTable('lastUpdated')">Updated <i class="fas fa-sort sort-icon"></i></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="templatesBody">
                    </tbody>
                </table>
                
                <div class="pagination-container" id="paginationContainer">
                    <div class="pagination-info" id="paginationInfo">
                        Showing 0 of 0 templates
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationNav">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e3a5f; color: white;">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Template Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Account</label>
                        <div class="fw-medium" id="viewAccountName">-</div>
                        <small class="text-muted" id="viewAccountId">-</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Template ID</label>
                        <div class="fw-medium font-monospace" id="viewTemplateId">-</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Template Name</label>
                        <div class="fw-medium" id="viewTemplateName">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Version</label>
                        <div class="fw-medium" id="viewVersion">-</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Channel</label>
                        <div id="viewChannel">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Trigger</label>
                        <div id="viewTrigger">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Status</label>
                        <div id="viewStatus">-</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Content</label>
                    <div class="border rounded p-3 bg-light" id="viewContent">-</div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Access Scope</label>
                        <div id="viewScope">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Last Updated</label>
                        <div id="viewLastUpdated">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTemplateModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="height: 100vh; display: flex; flex-direction: column;">
            <div class="modal-header py-3 flex-shrink-0" style="background-color: #1e3a5f; color: #fff;">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title mb-0" id="editModalTitle"><i class="fas fa-edit me-2"></i>Edit Template</h5>
                    <div class="wizard-steps ms-4">
                        <span class="wizard-step active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-label">Metadata</span>
                        </span>
                        <span class="wizard-step" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-label">Content</span>
                        </span>
                        <span class="wizard-step" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-label">Review</span>
                        </span>
                    </div>
                    <div class="ms-3 d-flex align-items-center">
                        <span class="badge bg-light text-dark me-2" id="editAccountBadge">-</span>
                        <small class="text-white-50">(Tenant Context)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 flex-grow-1 overflow-auto position-relative" style="background: #f5f7fa;">
                <div class="loading-overlay d-none" id="editLoadingOverlay">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="mb-0">Loading template...</p>
                    </div>
                </div>
                <div class="error-overlay d-none" id="editErrorOverlay">
                    <div class="text-center">
                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                        <h5>Failed to Load Template</h5>
                        <p class="text-muted" id="editErrorMessage">An error occurred.</p>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
                
                <div class="tpl-builder-layout">
                    <div class="tpl-builder-left">
                        <div class="wizard-step-content" data-step="1">
                            <div class="card mb-4">
                                <div class="card-header" style="background-color: rgba(30, 58, 95, 0.05);">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2" style="color: #1e3a5f;"></i>Template Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info small mb-3">
                                        <i class="fas fa-user-shield me-2"></i>
                                        <strong>Admin Edit Mode:</strong> You are editing this template on behalf of <strong id="editCustomerName">-</strong>. All changes will be applied to the customer's template.
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editTemplateName" placeholder="e.g., Order Confirmation">
                                            <div class="invalid-feedback">Please enter a template name</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Template ID</label>
                                            <input type="text" class="form-control" id="editTemplateIdField" readonly style="background-color: #e9ecef;">
                                            <small class="text-muted">Auto-generated, cannot be changed</small>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Trigger Type</label>
                                        <div class="d-flex gap-3">
                                            <div class="trigger-option-display p-3 border rounded text-center" id="editTriggerDisplay">
                                                <i class="fas fa-code fa-2x mb-2"></i>
                                                <div class="fw-medium">API</div>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Trigger type cannot be changed after template creation</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wizard-step-content d-none" data-step="2">
                            <div class="card mb-4">
                                <div class="card-header" style="background-color: rgba(30, 58, 95, 0.05);">
                                    <h6 class="mb-0"><i class="fas fa-comment-alt me-2" style="color: #1e3a5f;"></i>Message Content</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Channel</label>
                                        <div class="d-flex gap-3 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="editChannel" id="editChannelSMS" value="sms" checked>
                                                <label class="form-check-label" for="editChannelSMS">SMS</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="editChannel" id="editChannelBasicRCS" value="basic_rcs">
                                                <label class="form-check-label" for="editChannelBasicRCS">Basic RCS</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="editChannel" id="editChannelRichRCS" value="rich_rcs">
                                                <label class="form-check-label" for="editChannelRichRCS">Rich RCS</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="editTextEditorContainer">
                                        <div class="mb-3">
                                            <label class="form-label">Message Content <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="editTemplateContent" rows="8" placeholder="Enter your message content..."></textarea>
                                            <div class="d-flex justify-content-between mt-2">
                                                <div>
                                                    <span class="small text-muted">Characters: <span id="editCharCount">0</span></span>
                                                    <span class="small text-muted ms-3">Parts: <span id="editPartCount">1</span></span>
                                                    <span class="small text-muted ms-3">Encoding: <span id="editEncodingType">GSM-7</span></span>
                                                </div>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertPlaceholder('FirstName')">
                                                        <i class="fas fa-tag me-1"></i>{FirstName}
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertPlaceholder('LastName')">
                                                        {LastName}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="editRcsContentSection" class="d-none">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Rich RCS content editing is available through the full RCS wizard. Changes here will update the basic configuration.
                                        </div>
                                        <div class="border rounded p-3 bg-light" id="editRcsPreview">
                                            <p class="text-muted mb-0 text-center">Rich RCS content preview</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wizard-step-content d-none" data-step="3">
                            <div class="card mb-4">
                                <div class="card-header" style="background-color: rgba(30, 58, 95, 0.05);">
                                    <h6 class="mb-0"><i class="fas fa-check-circle me-2" style="color: #1e3a5f;"></i>Review & Save</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning mb-4">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Admin Action:</strong> Saving will update the customer's template. This action will be logged in the audit trail.
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted small mb-2">TEMPLATE DETAILS</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td class="text-muted" style="width: 40%;">Name:</td>
                                                    <td id="reviewName">-</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Template ID:</td>
                                                    <td id="reviewTemplateId" class="font-monospace">-</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Channel:</td>
                                                    <td id="reviewChannel">-</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Trigger:</td>
                                                    <td id="reviewTrigger">-</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Account:</td>
                                                    <td id="reviewAccount">-</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted small mb-2">CONTENT PREVIEW</h6>
                                            <div class="border rounded p-3 bg-light" id="reviewContentPreview" style="min-height: 100px;">
                                                -
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 mt-4">
                                        <label class="form-label">Change Note (Optional)</label>
                                        <textarea class="form-control" id="editChangeNote" rows="2" placeholder="Describe the changes made..."></textarea>
                                        <small class="text-muted">This will be recorded in the version history</small>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editSetLive">
                                        <label class="form-check-label" for="editSetLive">
                                            Publish as Live immediately
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tpl-builder-right">
                        <div class="card sticky-top" style="top: 20px;">
                            <div class="card-header" style="background-color: rgba(30, 58, 95, 0.05);">
                                <h6 class="mb-0"><i class="fas fa-mobile-alt me-2" style="color: #1e3a5f;"></i>Preview</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="phone-preview-container" style="background: #1a1a1a; border-radius: 30px; padding: 10px; max-width: 280px; margin: 0 auto;">
                                    <div class="phone-screen" style="background: #fff; border-radius: 22px; padding: 15px; min-height: 350px;">
                                        <div class="preview-message p-3 rounded" style="background: #e3f2fd; text-align: left; font-size: 14px;" id="editPreviewMessage">
                                            Your message preview will appear here...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-shrink-0" style="background: #fff; border-top: 1px solid #e9ecef;">
                <div class="d-flex justify-content-between w-100">
                    <button type="button" class="btn btn-outline-secondary" id="editPrevBtn" onclick="editWizardPrev()" style="display: none;">
                        <i class="fas fa-arrow-left me-2"></i>Previous
                    </button>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-admin-primary" id="editNextBtn" onclick="editWizardNext()">
                            Next<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        <button type="button" class="btn btn-success" id="editSaveBtn" onclick="saveTemplateChanges()" style="display: none;">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="suspendTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-pause-circle me-2"></i>Suspend Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    You are about to suspend this template. The customer will not be able to use it until reactivated.
                </div>
                <div class="mb-3">
                    <label class="form-label">Template</label>
                    <div class="fw-medium" id="suspendTemplateName">-</div>
                    <small class="text-muted" id="suspendTemplateAccount">-</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason for suspension <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="suspendReason" rows="3" placeholder="Enter reason for suspension..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmSuspendTemplate()">
                    <i class="fas fa-pause-circle me-1"></i>Suspend Template
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reactivateTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-play-circle me-2"></i>Reactivate Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    You are about to reactivate this template. The customer will be able to use it again.
                </div>
                <div class="mb-3">
                    <label class="form-label">Template</label>
                    <div class="fw-medium" id="reactivateTemplateName">-</div>
                    <small class="text-muted" id="reactivateTemplateAccount">-</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmReactivateTemplate()">
                    <i class="fas fa-play-circle me-1"></i>Reactivate Template
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-archive me-2 text-warning"></i>Archive Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to archive this template?</p>
                <p class="text-muted mb-3">Archived templates cannot be edited or used by the customer.</p>
                <div class="mb-3">
                    <label class="form-label">Template</label>
                    <div class="fw-medium" id="archiveTemplateName">-</div>
                    <small class="text-muted" id="archiveTemplateAccount">-</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason for archiving <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="archiveReason" rows="3" placeholder="Enter reason for archiving..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmArchiveTemplate()">
                    <i class="fas fa-archive me-1"></i>Archive
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-templates-service.js') }}"></script>
<script>
var sortColumn = 'lastUpdated';
var sortDirection = 'desc';
var currentPage = 1;
var pageSize = 20;
var showArchived = false;

var appliedFilters = {
    search: '',
    accountId: '',
    accountName: '',
    channels: [],
    triggers: [],
    statuses: []
};

var pendingFilters = {
    accountId: '',
    accountName: ''
};

var filterLabels = {
    channels: { 'sms': 'SMS', 'basic_rcs': 'Basic RCS', 'rich_rcs': 'Rich RCS' },
    triggers: { 'api': 'API', 'portal': 'Portal', 'email': 'Email-to-SMS' },
    statuses: { 'draft': 'Draft', 'live': 'Live', 'paused': 'Paused', 'archived': 'Archived' }
};

var currentActionTemplate = null;

var AdminPermissions = (function() {
    var currentUserPermissions = [
        'templates.admin.view',
        'templates.admin.edit',
        'templates.admin.suspend',
        'templates.admin.reactivate',
        'templates.admin.archive'
    ];
    
    function hasPermission(permission) {
        return currentUserPermissions.includes(permission);
    }
    
    function canView() {
        return hasPermission('templates.admin.view');
    }
    
    function canEdit() {
        return hasPermission('templates.admin.edit');
    }
    
    function canSuspend() {
        return hasPermission('templates.admin.suspend');
    }
    
    function canReactivate() {
        return hasPermission('templates.admin.reactivate');
    }
    
    function canArchive() {
        return hasPermission('templates.admin.archive');
    }
    
    function setPermissions(permissions) {
        currentUserPermissions = permissions;
    }
    
    return {
        hasPermission: hasPermission,
        canView: canView,
        canEdit: canEdit,
        canSuspend: canSuspend,
        canReactivate: canReactivate,
        canArchive: canArchive,
        setPermissions: setPermissions
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    loadTemplates();
    setupEventListeners();
    setupAccountSearch();
});

function setupEventListeners() {
    document.getElementById('templateSearch').addEventListener('input', function() {
        appliedFilters.search = this.value;
        currentPage = 1;
        loadTemplates();
        renderActiveFilters();
    });
    
    document.getElementById('showArchivedToggle').addEventListener('change', function() {
        showArchived = this.checked;
        currentPage = 1;
        loadTemplates();
    });
    
    document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
    document.getElementById('resetFiltersBtn').addEventListener('click', resetFilters);
    
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        var filterType = dropdown.dataset.filter;
        
        dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateDropdownLabel(dropdown, filterType);
            });
        });
        
        var selectAllBtn = dropdown.querySelector('.select-all-btn');
        var clearAllBtn = dropdown.querySelector('.clear-all-btn');
        
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                    cb.checked = true;
                });
                updateDropdownLabel(dropdown, filterType);
            });
        }
        
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                    cb.checked = false;
                });
                updateDropdownLabel(dropdown, filterType);
            });
        }
    });
}

function setupAccountSearch() {
    var input = document.getElementById('accountSearchInput');
    var dropdown = document.getElementById('accountDropdownMenu');
    var searchTimeout = null;
    
    input.addEventListener('input', function() {
        var searchTerm = this.value;
        
        if (searchTimeout) clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(async function() {
            var result = await AdminTemplatesService.searchAccounts(searchTerm);
            
            if (result.success) {
                var html = '<li><a class="dropdown-item' + (!pendingFilters.accountId ? ' active' : '') + '" href="#" data-account-id="" data-account-name="All Accounts">All Accounts</a></li>';
                
                result.data.forEach(function(account) {
                    var isActive = pendingFilters.accountId === account.id;
                    html += '<li><a class="dropdown-item' + (isActive ? ' active' : '') + '" href="#" data-account-id="' + account.id + '" data-account-name="' + account.name + '">' + 
                        account.name + ' <small class="text-muted">(' + account.id + ')</small></a></li>';
                });
                
                dropdown.innerHTML = html;
                setupAccountDropdownItems();
            }
        }, 300);
    });
    
    input.addEventListener('focus', function() {
        var bsDropdown = new bootstrap.Dropdown(this);
        bsDropdown.show();
    });
    
    setupAccountDropdownItems();
}

function setupAccountDropdownItems() {
    document.querySelectorAll('#accountDropdownMenu .dropdown-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            var accountId = this.dataset.accountId;
            var accountName = this.dataset.accountName;
            
            pendingFilters.accountId = accountId;
            pendingFilters.accountName = accountName;
            
            document.getElementById('accountSearchInput').value = accountName;
            document.getElementById('selectedAccountId').value = accountId;
            
            document.querySelectorAll('#accountDropdownMenu .dropdown-item').forEach(function(i) {
                i.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

function updateDropdownLabel(dropdown, filterType) {
    var checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
    var label = dropdown.querySelector('.dropdown-label');
    var defaultLabels = {
        channels: 'All Channels',
        triggers: 'All Triggers',
        statuses: 'All Statuses'
    };
    
    if (checked.length === 0) {
        label.textContent = defaultLabels[filterType] || 'All';
    } else if (checked.length === 1) {
        var labelMap = filterLabels[filterType] || {};
        label.textContent = labelMap[checked[0].value] || checked[0].value;
    } else {
        label.textContent = checked.length + ' selected';
    }
}

function getCheckedValues(filterType) {
    var dropdown = document.querySelector('.multiselect-dropdown[data-filter="' + filterType + '"]');
    var values = [];
    if (dropdown) {
        dropdown.querySelectorAll('input[type="checkbox"]:checked').forEach(function(cb) {
            values.push(cb.value);
        });
    }
    return values;
}

function applyFilters() {
    appliedFilters.accountId = pendingFilters.accountId;
    appliedFilters.accountName = pendingFilters.accountName;
    appliedFilters.channels = getCheckedValues('channels');
    appliedFilters.triggers = getCheckedValues('triggers');
    appliedFilters.statuses = getCheckedValues('statuses');
    
    currentPage = 1;
    loadTemplates();
    renderActiveFilters();
}

function resetFilters() {
    document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) {
        cb.checked = false;
    });
    
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        updateDropdownLabel(dropdown, dropdown.dataset.filter);
    });
    
    document.getElementById('accountSearchInput').value = 'All Accounts';
    document.getElementById('selectedAccountId').value = '';
    pendingFilters.accountId = '';
    pendingFilters.accountName = '';
    
    appliedFilters.accountId = '';
    appliedFilters.accountName = '';
    appliedFilters.channels = [];
    appliedFilters.triggers = [];
    appliedFilters.statuses = [];
    
    currentPage = 1;
    loadTemplates();
    renderActiveFilters();
}

function removeFilter(filterType, value) {
    if (filterType === 'search') {
        document.getElementById('templateSearch').value = '';
        appliedFilters.search = '';
    } else if (filterType === 'account') {
        document.getElementById('accountSearchInput').value = 'All Accounts';
        document.getElementById('selectedAccountId').value = '';
        pendingFilters.accountId = '';
        pendingFilters.accountName = '';
        appliedFilters.accountId = '';
        appliedFilters.accountName = '';
    } else {
        var dropdown = document.querySelector('.multiselect-dropdown[data-filter="' + filterType + '"]');
        if (dropdown && value) {
            var cb = dropdown.querySelector('input[value="' + value + '"]');
            if (cb) cb.checked = false;
            updateDropdownLabel(dropdown, filterType);
        }
        appliedFilters[filterType] = appliedFilters[filterType].filter(function(v) {
            return v !== value;
        });
    }
    
    currentPage = 1;
    loadTemplates();
    renderActiveFilters();
}

function renderActiveFilters() {
    var container = document.getElementById('activeFilters');
    var html = '';
    
    if (appliedFilters.search) {
        html += createChip('Search', appliedFilters.search, 'search', null);
    }
    
    if (appliedFilters.accountId) {
        html += createChip('Account', appliedFilters.accountName, 'account', null);
    }
    
    appliedFilters.channels.forEach(function(val) {
        html += createChip('Channel', filterLabels.channels[val] || val, 'channels', val);
    });
    
    appliedFilters.triggers.forEach(function(val) {
        html += createChip('Trigger', filterLabels.triggers[val] || val, 'triggers', val);
    });
    
    appliedFilters.statuses.forEach(function(val) {
        html += createChip('Status', filterLabels.statuses[val] || val, 'statuses', val);
    });
    
    container.innerHTML = html;
}

function createChip(label, value, filterType, filterValue) {
    var onclick = filterValue ? "removeFilter('" + filterType + "', '" + filterValue + "')" : "removeFilter('" + filterType + "')";
    return '<span class="filter-chip">' +
        '<span class="chip-label">' + label + ':</span>' +
        '<span class="chip-value">' + value + '</span>' +
        '<i class="fas fa-times remove-chip" onclick="' + onclick + '"></i>' +
        '</span>';
}

async function loadTemplates() {
    var loadingOverlay = document.getElementById('tableLoadingOverlay');
    loadingOverlay.classList.remove('d-none');
    
    var result = await AdminTemplatesService.listTemplates({
        accountId: appliedFilters.accountId || null,
        search: appliedFilters.search,
        channels: appliedFilters.channels,
        triggers: appliedFilters.triggers,
        statuses: appliedFilters.statuses,
        showArchived: showArchived,
        sortColumn: sortColumn,
        sortDirection: sortDirection,
        page: currentPage,
        pageSize: pageSize
    });
    
    loadingOverlay.classList.add('d-none');
    
    if (result.success) {
        renderTemplates(result.data.templates);
        renderPagination(result.data.pagination);
    } else {
        showToast('Failed to load templates: ' + result.error, 'error');
    }
}

function renderTemplates(templates) {
    if (templates.length === 0 && currentPage === 1 && !appliedFilters.search && !appliedFilters.accountId) {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('templatesTableContainer').style.display = 'none';
        return;
    }
    
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('templatesTableContainer').style.display = 'block';
    
    var tbody = document.getElementById('templatesBody');
    var html = '';
    
    templates.forEach(function(template) {
        var isArchived = template.status === 'archived';
        var rowClass = isArchived ? 'archived-row' : '';
        
        html += '<tr class="' + rowClass + '">';
        html += '<td><span class="account-name">' + template.accountName + '</span><span class="account-id">' + template.accountId + '</span></td>';
        html += '<td><span class="template-name">' + template.name + '</span></td>';
        html += '<td><span class="template-id">' + template.templateId + '</span></td>';
        html += '<td><span class="version-text">v' + template.version + '</span></td>';
        html += '<td><span class="channel-text">' + getChannelLabel(template.channel) + '</span></td>';
        html += '<td>' + getTriggerLabel(template.trigger) + '</td>';
        html += '<td><span class="content-preview">' + getContentPreviewText(template) + '</span></td>';
        html += '<td><span class="access-scope">' + template.accessScope + '</span></td>';
        html += '<td><span class="badge rounded-pill ' + getStatusBadgeClass(template.status) + '">' + getStatusLabel(template.status) + '</span></td>';
        html += '<td>' + template.lastUpdated + '</td>';
        html += '<td>';
        html += '<div class="dropdown">';
        html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">';
        html += '<i class="fas fa-ellipsis-v"></i>';
        html += '</button>';
        html += '<ul class="dropdown-menu dropdown-menu-end">';
        
        html += '<li><a class="dropdown-item" href="#" onclick="viewTemplate(\'' + template.accountId + '\', \'' + template.templateId + '\'); return false;"><i class="fas fa-eye me-2"></i>View Details</a></li>';
        
        if (!isArchived && AdminPermissions.canEdit()) {
            html += '<li><a class="dropdown-item" href="#" onclick="editTemplate(\'' + template.accountId + '\', \'' + template.templateId + '\'); return false;"><i class="fas fa-edit me-2"></i>Edit</a></li>';
        }
        
        if (template.status === 'live' && AdminPermissions.canSuspend()) {
            html += '<li><a class="dropdown-item text-warning" href="#" onclick="suspendTemplate(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\', \'' + escapeJs(template.accountName) + '\'); return false;"><i class="fas fa-pause-circle me-2"></i>Suspend</a></li>';
        }
        
        if (template.status === 'paused' && AdminPermissions.canReactivate()) {
            html += '<li><a class="dropdown-item text-success" href="#" onclick="reactivateTemplate(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\', \'' + escapeJs(template.accountName) + '\'); return false;"><i class="fas fa-play-circle me-2"></i>Reactivate</a></li>';
        }
        
        if (!isArchived && AdminPermissions.canArchive()) {
            html += '<li><hr class="dropdown-divider"></li>';
            html += '<li><a class="dropdown-item text-danger" href="#" onclick="archiveTemplate(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\', \'' + escapeJs(template.accountName) + '\', \'' + template.status + '\'); return false;"><i class="fas fa-archive me-2"></i>Archive</a></li>';
        }
        
        html += '</ul>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html || '<tr><td colspan="11" class="text-center text-muted py-4">No templates match your filters</td></tr>';
}

function renderPagination(pagination) {
    var info = document.getElementById('paginationInfo');
    var nav = document.getElementById('paginationNav');
    
    var start = (pagination.page - 1) * pagination.pageSize + 1;
    var end = Math.min(pagination.page * pagination.pageSize, pagination.totalCount);
    
    if (pagination.totalCount === 0) {
        info.textContent = 'No templates found';
    } else {
        info.textContent = 'Showing ' + start + '-' + end + ' of ' + pagination.totalCount + ' templates';
    }
    
    var html = '';
    
    html += '<li class="page-item ' + (pagination.hasPrevPage ? '' : 'disabled') + '">';
    html += '<a class="page-link" href="#" onclick="goToPage(' + (pagination.page - 1) + '); return false;"><i class="fas fa-chevron-left"></i></a>';
    html += '</li>';
    
    var startPage = Math.max(1, pagination.page - 2);
    var endPage = Math.min(pagination.totalPages, pagination.page + 2);
    
    if (startPage > 1) {
        html += '<li class="page-item"><a class="page-link" href="#" onclick="goToPage(1); return false;">1</a></li>';
        if (startPage > 2) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for (var i = startPage; i <= endPage; i++) {
        html += '<li class="page-item ' + (i === pagination.page ? 'active' : '') + '">';
        html += '<a class="page-link" href="#" onclick="goToPage(' + i + '); return false;">' + i + '</a>';
        html += '</li>';
    }
    
    if (endPage < pagination.totalPages) {
        if (endPage < pagination.totalPages - 1) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        html += '<li class="page-item"><a class="page-link" href="#" onclick="goToPage(' + pagination.totalPages + '); return false;">' + pagination.totalPages + '</a></li>';
    }
    
    html += '<li class="page-item ' + (pagination.hasNextPage ? '' : 'disabled') + '">';
    html += '<a class="page-link" href="#" onclick="goToPage(' + (pagination.page + 1) + '); return false;"><i class="fas fa-chevron-right"></i></a>';
    html += '</li>';
    
    nav.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    loadTemplates();
}

function sortTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }
    
    document.querySelectorAll('.templates-table thead th').forEach(function(th) {
        th.classList.remove('sorted');
        var icon = th.querySelector('.sort-icon');
        if (icon) {
            icon.className = 'fas fa-sort sort-icon';
        }
    });
    
    var activeTh = document.querySelector('[data-sort="' + column + '"]');
    if (activeTh) {
        activeTh.classList.add('sorted');
        var icon = activeTh.querySelector('.sort-icon');
        if (icon) {
            icon.className = 'fas fa-sort-' + (sortDirection === 'asc' ? 'up' : 'down') + ' sort-icon';
        }
    }
    
    currentPage = 1;
    loadTemplates();
}

function getChannelLabel(channel) {
    switch(channel) {
        case 'sms': return 'SMS';
        case 'basic_rcs': return 'Basic RCS';
        case 'rich_rcs': return 'Rich RCS';
        default: return channel;
    }
}

function getChannelBadgeClass(channel) {
    switch(channel) {
        case 'sms': return 'badge-sms';
        case 'basic_rcs': return 'badge-basic-rcs';
        case 'rich_rcs': return 'badge-rich-rcs';
        default: return 'badge-sms';
    }
}

function getTriggerLabel(trigger) {
    switch(trigger) {
        case 'api': return 'API';
        case 'portal': return 'Portal';
        case 'email': return 'Email-to-SMS';
        default: return trigger;
    }
}

function getStatusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'draft': return 'badge-draft';
        case 'live': return 'badge-live';
        case 'paused': return 'badge-paused';
        case 'archived': return 'badge-archived';
        default: return 'badge-draft';
    }
}

function getContentPreviewText(template) {
    if (template.contentType === 'rich_card') {
        return 'Rich Card';
    } else if (template.contentType === 'carousel') {
        return 'Carousel';
    } else {
        var preview = template.content.length > 60 ? template.content.substring(0, 60) + '...' : template.content;
        return preview || '-';
    }
}

function escapeJs(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
}

async function viewTemplate(accountId, templateId) {
    var result = await AdminTemplatesService.getTemplateDetails(accountId, templateId);
    
    if (result.success) {
        var template = result.data;
        
        logAdminAuditEvent('TEMPLATE_VIEWED', {
            accountId: accountId,
            templateId: templateId,
            templateName: template.name
        });
        
        document.getElementById('viewAccountName').textContent = template.accountName;
        document.getElementById('viewAccountId').textContent = template.accountId;
        document.getElementById('viewTemplateId').textContent = template.templateId;
        document.getElementById('viewTemplateName').textContent = template.name;
        document.getElementById('viewVersion').textContent = 'v' + template.version;
        document.getElementById('viewChannel').innerHTML = '<span class="badge rounded-pill ' + getChannelBadgeClass(template.channel) + '">' + getChannelLabel(template.channel) + '</span>';
        document.getElementById('viewTrigger').textContent = getTriggerLabel(template.trigger);
        document.getElementById('viewStatus').innerHTML = '<span class="badge rounded-pill ' + getStatusBadgeClass(template.status) + '">' + getStatusLabel(template.status) + '</span>';
        document.getElementById('viewContent').textContent = template.content || (template.contentType === 'rich_card' ? 'Rich RCS Card' : 'Carousel');
        document.getElementById('viewScope').textContent = template.accessScope;
        document.getElementById('viewLastUpdated').textContent = template.lastUpdated;
        
        new bootstrap.Modal(document.getElementById('viewTemplateModal')).show();
    } else {
        showToast('Failed to load template details', 'error');
    }
}

function suspendTemplate(accountId, templateId, name, accountName) {
    currentActionTemplate = { accountId, templateId, name, accountName };
    
    document.getElementById('suspendTemplateName').textContent = name;
    document.getElementById('suspendTemplateAccount').textContent = accountName + ' (' + accountId + ')';
    document.getElementById('suspendReason').value = '';
    
    new bootstrap.Modal(document.getElementById('suspendTemplateModal')).show();
}

async function confirmSuspendTemplate() {
    if (!currentActionTemplate) return;
    
    var reason = document.getElementById('suspendReason').value.trim();
    if (!reason) {
        showToast('Please enter a reason for suspension', 'warning');
        return;
    }
    
    var result = await AdminTemplatesService.suspendTemplate(
        currentActionTemplate.accountId,
        currentActionTemplate.templateId,
        reason
    );
    
    if (result.success) {
        logAdminAuditEvent('TEMPLATE_SUSPENDED', {
            accountId: currentActionTemplate.accountId,
            templateId: currentActionTemplate.templateId,
            templateName: currentActionTemplate.name,
            reason: reason,
            beforeSnapshot: { status: 'live' },
            afterSnapshot: { status: 'paused' }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('suspendTemplateModal')).hide();
        showToast('Template suspended successfully', 'success');
        loadTemplates();
    } else {
        showToast('Failed to suspend template: ' + result.error, 'error');
    }
}

function reactivateTemplate(accountId, templateId, name, accountName) {
    currentActionTemplate = { accountId, templateId, name, accountName };
    
    document.getElementById('reactivateTemplateName').textContent = name;
    document.getElementById('reactivateTemplateAccount').textContent = accountName + ' (' + accountId + ')';
    
    new bootstrap.Modal(document.getElementById('reactivateTemplateModal')).show();
}

async function confirmReactivateTemplate() {
    if (!currentActionTemplate) return;
    
    var beforeStatus = 'paused';
    
    var result = await AdminTemplatesService.reactivateTemplate(
        currentActionTemplate.accountId,
        currentActionTemplate.templateId
    );
    
    if (result.success) {
        logAdminAuditEvent('TEMPLATE_REACTIVATED', {
            accountId: currentActionTemplate.accountId,
            templateId: currentActionTemplate.templateId,
            templateName: currentActionTemplate.name,
            beforeSnapshot: { status: beforeStatus },
            afterSnapshot: { status: 'live' }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('reactivateTemplateModal')).hide();
        showToast('Template reactivated successfully', 'success');
        loadTemplates();
    } else {
        showToast('Failed to reactivate template: ' + result.error, 'error');
    }
}

function archiveTemplate(accountId, templateId, name, accountName, currentStatus) {
    currentActionTemplate = { accountId, templateId, name, accountName, currentStatus: currentStatus };
    
    document.getElementById('archiveTemplateName').textContent = name;
    document.getElementById('archiveTemplateAccount').textContent = accountName + ' (' + accountId + ')';
    document.getElementById('archiveReason').value = '';
    
    new bootstrap.Modal(document.getElementById('archiveTemplateModal')).show();
}

async function confirmArchiveTemplate() {
    if (!currentActionTemplate) return;
    
    var reason = document.getElementById('archiveReason').value.trim();
    if (!reason) {
        showToast('Please enter a reason for archiving', 'warning');
        return;
    }
    
    var result = await AdminTemplatesService.archiveTemplate(
        currentActionTemplate.accountId,
        currentActionTemplate.templateId,
        reason
    );
    
    if (result.success) {
        logAdminAuditEvent('TEMPLATE_ARCHIVED', {
            accountId: currentActionTemplate.accountId,
            templateId: currentActionTemplate.templateId,
            templateName: currentActionTemplate.name,
            reason: reason,
            beforeSnapshot: { status: currentActionTemplate.currentStatus || 'unknown' },
            afterSnapshot: { status: 'archived' }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('archiveTemplateModal')).hide();
        showToast('Template archived successfully', 'success');
        loadTemplates();
    } else {
        showToast('Failed to archive template: ' + result.error, 'error');
    }
}

function logAdminAuditEvent(eventType, payload) {
    var adminUser = typeof AdminControlPlane !== 'undefined' ? AdminControlPlane.getCurrentUser() : null;
    
    var auditEntry = {
        timestamp: new Date().toISOString(),
        eventType: eventType,
        adminUserId: adminUser ? adminUser.userId : 'unknown',
        adminEmail: adminUser ? adminUser.email : 'unknown',
        accountId: payload.accountId,
        templateId: payload.templateId,
        templateName: payload.templateName || null,
        beforeSnapshot: payload.beforeSnapshot || null,
        afterSnapshot: payload.afterSnapshot || null,
        reason: payload.reason || null,
        changedFields: payload.changedFields || null,
        sourceScreen: 'Admin > Management > Templates',
        ipAddress: null
    };
    
    console.log('[AdminTemplatesAudit]', JSON.stringify(auditEntry));
    
    if (typeof AdminControlPlane !== 'undefined') {
        if (AdminControlPlane.logAudit) {
            AdminControlPlane.logAudit(eventType, auditEntry);
        } else if (AdminControlPlane.logAccess) {
            AdminControlPlane.logAccess(auditEntry);
        }
    }
}

function showToast(message, type) {
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    var bgClass = 'bg-primary';
    if (type === 'success') bgClass = 'bg-success';
    if (type === 'error') bgClass = 'bg-danger';
    if (type === 'warning') bgClass = 'bg-warning text-dark';
    
    var toastId = 'toast-' + Date.now();
    var toastHtml = '<div id="' + toastId + '" class="toast ' + bgClass + ' text-white" role="alert">' +
        '<div class="toast-body d-flex justify-content-between align-items-center">' +
        '<span>' + message + '</span>' +
        '<button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>' +
        '</div></div>';
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    var toastEl = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}

var editWizardStep = 1;
var editingTemplate = null;
var editTenantContext = null;
var editReadOnlyMode = false;

async function editTemplate(accountId, templateId) {
    var loadingOverlay = document.getElementById('editLoadingOverlay');
    var errorOverlay = document.getElementById('editErrorOverlay');
    
    loadingOverlay.classList.remove('d-none');
    errorOverlay.classList.add('d-none');
    
    editWizardStep = 1;
    editTenantContext = { accountId: accountId, templateId: templateId };
    editReadOnlyMode = !AdminPermissions.canEdit();
    
    updateEditWizardUI();
    new bootstrap.Modal(document.getElementById('editTemplateModal')).show();
    
    var result = await AdminTemplatesService.getTemplateDetails(accountId, templateId);
    
    if (!result.success) {
        loadingOverlay.classList.add('d-none');
        errorOverlay.classList.remove('d-none');
        document.getElementById('editErrorMessage').textContent = result.error || 'Failed to load template details.';
        return;
    }
    
    editingTemplate = result.data;
    
    if (editingTemplate.status === 'archived') {
        loadingOverlay.classList.add('d-none');
        errorOverlay.classList.remove('d-none');
        document.getElementById('editErrorMessage').textContent = 'Archived templates cannot be edited.';
        return;
    }
    
    applyEditReadOnlyMode(editReadOnlyMode);
    
    document.getElementById('editAccountBadge').textContent = editingTemplate.accountName + ' (' + accountId + ')';
    document.getElementById('editCustomerName').textContent = editingTemplate.accountName;
    
    document.getElementById('editTemplateName').value = editingTemplate.name;
    document.getElementById('editTemplateIdField').value = editingTemplate.templateId;
    document.getElementById('editTemplateContent').value = editingTemplate.content || '';
    
    var triggerIcon = editingTemplate.trigger === 'api' ? 'fa-code' : 
                      editingTemplate.trigger === 'portal' ? 'fa-desktop' : 'fa-envelope';
    var triggerLabel = getTriggerLabel(editingTemplate.trigger);
    document.getElementById('editTriggerDisplay').innerHTML = '<i class="fas ' + triggerIcon + ' fa-2x mb-2"></i><div class="fw-medium">' + triggerLabel + '</div>';
    
    if (editingTemplate.channel === 'sms') {
        document.getElementById('editChannelSMS').checked = true;
        document.getElementById('editTextEditorContainer').classList.remove('d-none');
        document.getElementById('editRcsContentSection').classList.add('d-none');
    } else if (editingTemplate.channel === 'basic_rcs') {
        document.getElementById('editChannelBasicRCS').checked = true;
        document.getElementById('editTextEditorContainer').classList.remove('d-none');
        document.getElementById('editRcsContentSection').classList.add('d-none');
    } else if (editingTemplate.channel === 'rich_rcs') {
        document.getElementById('editChannelRichRCS').checked = true;
        document.getElementById('editTextEditorContainer').classList.add('d-none');
        document.getElementById('editRcsContentSection').classList.remove('d-none');
    }
    
    updateEditCharCount();
    updateEditPreview();
    
    loadingOverlay.classList.add('d-none');
    
    AdminControlPlane.logAccess({
        eventType: 'TEMPLATE_EDIT_STARTED',
        accountId: accountId,
        templateId: templateId,
        templateName: editingTemplate.name,
        adminAction: 'edit_template'
    });
}

function updateEditWizardUI() {
    document.querySelectorAll('#editTemplateModal .wizard-step').forEach(function(step) {
        var stepNum = parseInt(step.getAttribute('data-step'));
        step.classList.remove('active', 'completed');
        if (stepNum === editWizardStep) {
            step.classList.add('active');
        } else if (stepNum < editWizardStep) {
            step.classList.add('completed');
        }
    });
    
    document.querySelectorAll('#editTemplateModal .wizard-step-content').forEach(function(content) {
        var stepNum = parseInt(content.getAttribute('data-step'));
        content.classList.toggle('d-none', stepNum !== editWizardStep);
    });
    
    document.getElementById('editPrevBtn').style.display = editWizardStep > 1 ? 'inline-block' : 'none';
    document.getElementById('editNextBtn').style.display = editWizardStep < 3 ? 'inline-block' : 'none';
    
    if (editReadOnlyMode) {
        document.getElementById('editSaveBtn').style.display = 'none';
    } else {
        document.getElementById('editSaveBtn').style.display = editWizardStep === 3 ? 'inline-block' : 'none';
    }
}

function applyEditReadOnlyMode(isReadOnly) {
    var modalTitleIcon = document.querySelector('#editTemplateModal .modal-header .modal-title i');
    var modalTitleText = document.querySelector('#editTemplateModal .modal-header .modal-title');
    var readOnlyBadge = document.getElementById('editReadOnlyBadge');
    
    if (isReadOnly) {
        if (modalTitleIcon) {
            modalTitleIcon.className = 'fas fa-eye me-2';
        }
        
        if (!readOnlyBadge && modalTitleText) {
            var badge = document.createElement('span');
            badge.id = 'editReadOnlyBadge';
            badge.className = 'badge bg-secondary ms-2';
            badge.textContent = 'Read Only';
            badge.style.fontSize = '0.7rem';
            badge.style.verticalAlign = 'middle';
            modalTitleText.parentNode.insertBefore(badge, modalTitleText.nextSibling);
        }
    } else {
        if (modalTitleIcon) {
            modalTitleIcon.className = 'fas fa-edit me-2';
        }
        if (readOnlyBadge) {
            readOnlyBadge.remove();
        }
    }
    
    document.getElementById('editTemplateName').readOnly = isReadOnly;
    document.getElementById('editTemplateContent').readOnly = isReadOnly;
    
    document.querySelectorAll('input[name="editChannel"]').forEach(function(radio) {
        radio.disabled = isReadOnly;
    });
    
    document.querySelectorAll('#editTemplateModal .placeholder-btn').forEach(function(btn) {
        btn.style.display = isReadOnly ? 'none' : 'inline-block';
    });
    
    var setLiveCheckbox = document.getElementById('editSetLive');
    var changeNoteTextarea = document.getElementById('editChangeNote');
    if (setLiveCheckbox) setLiveCheckbox.disabled = isReadOnly;
    if (changeNoteTextarea) changeNoteTextarea.readOnly = isReadOnly;
}

function editWizardNext() {
    if (editWizardStep === 1) {
        var name = document.getElementById('editTemplateName').value.trim();
        if (!name) {
            document.getElementById('editTemplateName').classList.add('is-invalid');
            return;
        }
        document.getElementById('editTemplateName').classList.remove('is-invalid');
    }
    
    if (editWizardStep === 2) {
        var channel = document.querySelector('input[name="editChannel"]:checked').value;
        if (channel !== 'rich_rcs') {
            var content = document.getElementById('editTemplateContent').value.trim();
            if (!content) {
                showToast('Please enter message content', 'warning');
                return;
            }
        }
        
        populateReviewStep();
    }
    
    if (editWizardStep < 3) {
        editWizardStep++;
        updateEditWizardUI();
    }
}

function editWizardPrev() {
    if (editWizardStep > 1) {
        editWizardStep--;
        updateEditWizardUI();
    }
}

function populateReviewStep() {
    document.getElementById('reviewName').textContent = document.getElementById('editTemplateName').value;
    document.getElementById('reviewTemplateId').textContent = document.getElementById('editTemplateIdField').value;
    
    var channel = document.querySelector('input[name="editChannel"]:checked').value;
    document.getElementById('reviewChannel').textContent = getChannelLabel(channel);
    document.getElementById('reviewTrigger').textContent = getTriggerLabel(editingTemplate.trigger);
    document.getElementById('reviewAccount').textContent = editingTemplate.accountName + ' (' + editTenantContext.accountId + ')';
    
    var content = document.getElementById('editTemplateContent').value;
    if (channel === 'rich_rcs') {
        document.getElementById('reviewContentPreview').innerHTML = '<p class="text-muted mb-0 fst-italic">Rich RCS content</p>';
    } else {
        var highlightedContent = content.replace(/\{(\w+)\}/g, '<span class="badge bg-info text-dark">{$1}</span>');
        document.getElementById('reviewContentPreview').innerHTML = highlightedContent || '-';
    }
}

async function saveTemplateChanges() {
    if (!editingTemplate || !editTenantContext) return;
    
    if (editReadOnlyMode) {
        showToast('Cannot save changes in read-only mode', 'warning');
        return;
    }
    
    var saveBtn = document.getElementById('editSaveBtn');
    var originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    
    var updatedData = {
        name: document.getElementById('editTemplateName').value.trim(),
        channel: document.querySelector('input[name="editChannel"]:checked').value,
        content: document.getElementById('editTemplateContent').value,
        changeNote: document.getElementById('editChangeNote').value.trim() || 'Updated by Admin',
        setLive: document.getElementById('editSetLive').checked
    };
    
    var result = await AdminTemplatesService.updateTemplate(
        editTenantContext.accountId,
        editTenantContext.templateId,
        updatedData
    );
    
    saveBtn.disabled = false;
    saveBtn.innerHTML = originalText;
    
    if (result.success) {
        logAdminAuditEvent('TEMPLATE_EDITED', {
            accountId: editTenantContext.accountId,
            templateId: editTenantContext.templateId,
            templateName: updatedData.name,
            changedFields: ['name', 'channel', 'content'],
            beforeSnapshot: {
                name: editingTemplate.name,
                channel: editingTemplate.channel,
                status: editingTemplate.status
            },
            afterSnapshot: {
                name: updatedData.name,
                channel: updatedData.channel,
                status: updatedData.setLive ? 'live' : editingTemplate.status
            }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('editTemplateModal')).hide();
        showToast('Template updated successfully', 'success');
        loadTemplates();
    } else {
        showToast('Failed to save template: ' + (result.error || 'Unknown error'), 'error');
    }
}

function updateEditCharCount() {
    var content = document.getElementById('editTemplateContent').value;
    var charCount = content.length;
    
    document.getElementById('editCharCount').textContent = charCount;
    
    var hasUnicode = /[^\x00-\x7F\u00A0-\u00FF]/.test(content);
    document.getElementById('editEncodingType').textContent = hasUnicode ? 'Unicode' : 'GSM-7';
    
    var maxCharsPerSegment = hasUnicode ? 70 : 160;
    var segments = Math.ceil(charCount / maxCharsPerSegment) || 1;
    document.getElementById('editPartCount').textContent = segments;
    
    updateEditPreview();
}

function updateEditPreview() {
    var content = document.getElementById('editTemplateContent').value || 'Your message preview will appear here...';
    var highlightedContent = content.replace(/\{(\w+)\}/g, '<span class="badge bg-info text-dark small">{$1}</span>');
    document.getElementById('editPreviewMessage').innerHTML = highlightedContent;
}

function insertPlaceholder(placeholder) {
    var textarea = document.getElementById('editTemplateContent');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    var tag = '{' + placeholder + '}';
    
    textarea.value = text.substring(0, start) + tag + text.substring(end);
    textarea.setSelectionRange(start + tag.length, start + tag.length);
    textarea.focus();
    
    updateEditCharCount();
}

document.addEventListener('DOMContentLoaded', function() {
    var editContent = document.getElementById('editTemplateContent');
    if (editContent) {
        editContent.addEventListener('input', updateEditCharCount);
    }
    
    document.querySelectorAll('input[name="editChannel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'rich_rcs') {
                document.getElementById('editTextEditorContainer').classList.add('d-none');
                document.getElementById('editRcsContentSection').classList.remove('d-none');
            } else {
                document.getElementById('editTextEditorContainer').classList.remove('d-none');
                document.getElementById('editRcsContentSection').classList.add('d-none');
            }
        });
    });
    
    document.getElementById('editTemplateModal').addEventListener('hidden.bs.modal', function() {
        editingTemplate = null;
        editTenantContext = null;
        editWizardStep = 1;
        document.getElementById('editTemplateName').value = '';
        document.getElementById('editTemplateIdField').value = '';
        document.getElementById('editTemplateContent').value = '';
        document.getElementById('editChangeNote').value = '';
        document.getElementById('editSetLive').checked = false;
        updateEditWizardUI();
    });
});
</script>
@endpush

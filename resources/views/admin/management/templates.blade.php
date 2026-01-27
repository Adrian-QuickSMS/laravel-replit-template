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
.table-container {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #dde4ea;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    margin-top: 0.5rem;
}
.table-container .dropdown-menu {
    z-index: 1050;
    position: absolute !important;
}
.table-container .dropdown {
    position: static;
}
.api-table tbody tr {
    position: relative;
}
.api-table tbody td:last-child .dropdown-menu {
    position: fixed !important;
    z-index: 1060;
}
.api-table {
    width: 100%;
    margin: 0;
    table-layout: fixed;
}
.api-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
    overflow: hidden;
    text-overflow: ellipsis;
}
.api-table thead th:hover {
    background: #e9ecef;
}
.api-table thead th:last-child:hover {
    background: #f8f9fa;
}
.api-table thead th .sort-icon,
.api-table thead th i.sort-icon {
    margin-left: 0.25rem;
    opacity: 0.5;
}
.api-table thead th.sorted .sort-icon,
.api-table thead th.sorted i.sort-icon {
    opacity: 1;
    color: #1e3a5f;
}
.api-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.85rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.api-table tbody tr {
    border-bottom: 1px solid #e9ecef;
}
.api-table tbody tr:last-child {
    border-bottom: none;
}
.api-table tbody tr:last-child td {
    border-bottom: none;
}
.api-table tbody tr:hover {
    background: #f8f9fa;
}
.api-table tbody tr:hover td {
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
.placeholder-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 50rem;
    background: rgba(30, 58, 95, 0.12);
    color: #1e3a5f;
    margin-right: 0.25rem;
    margin-bottom: 0.25rem;
}
.placeholder-pill i {
    margin-right: 0.35rem;
    font-size: 0.75rem;
}
.placeholder-pill .remove-btn {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.placeholder-pill .remove-btn:hover {
    opacity: 1;
}
.placeholder-counter {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 50rem;
    background: rgba(30, 58, 95, 0.12);
    color: #1e3a5f;
}
.placeholder-counter.empty {
    background: rgba(108, 117, 125, 0.12);
    color: #6c757d;
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
            <li class="breadcrumb-item"><a href="#">Assets</a></li>
            <li class="breadcrumb-item active">Global Templates Library</li>
        </ol>
    </div>

    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="margin: 0; font-weight: 600; color: #1e3a5f;">Global Templates Library</h2>
            <p style="margin: 0; color: #6c757d;">Manage message templates across all customer accounts</p>
        </div>
    </div>

    <div id="emptyState" class="empty-state" style="display: none;">
        <div class="empty-state-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <h4>No templates found</h4>
        <p>No message templates exist in the system yet. Templates are created by customers through the Customer Portal.</p>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
            <div class="input-group" style="width: 320px;">
                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="quickSearchInput" placeholder="Search templates..." onkeyup="filterTemplates()">
            </div>
            <div class="form-check form-switch mb-0 ms-3">
                <input class="form-check-input" type="checkbox" id="showArchivedToggle">
                <label class="form-check-label small" for="showArchivedToggle">Show Archived</label>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border-color: #1e3a5f; color: #1e3a5f;">
            <i class="fas fa-filter me-1"></i> Filters
        </button>
    </div>

    <div class="card" id="templatesTableContainer">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="display: none !important;">
            <h5 class="card-title mb-0" style="color: #1e3a5f;">Global Templates Library</h5>
        </div>
        <div class="card-body">
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

            <div class="table-container table-loading" id="tableContainer">
                <div class="loading-overlay d-none" id="tableLoadingOverlay">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table api-table mb-0">
                        <thead>
                            <tr>
                                <th data-sort="accountName">Account <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="name">Name <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="templateId">ID <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="version">Ver <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="channel">Channel <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="trigger">Trigger <i class="fas fa-sort sort-icon"></i></th>
                                <th>Preview</th>
                                <th data-sort="accessScope">Scope <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="lastUpdated">Updated <i class="fas fa-sort sort-icon"></i></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="templatesBody">
                        </tbody>
                    </table>
                </div>
                
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
    document.getElementById('quickSearchInput').addEventListener('input', function() {
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
        document.getElementById('quickSearchInput').value = '';
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
            html += '<li><a class="dropdown-item" href="/admin/management/templates/' + template.accountId + '/' + template.templateId + '/edit"><i class="fas fa-edit me-2"></i>Edit</a></li>';
        }
        
        html += '<li><a class="dropdown-item" href="#" onclick="viewDuplicate(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\'); return false;"><i class="fas fa-copy me-2"></i>Duplicate</a></li>';
        
        html += '<li><a class="dropdown-item" href="#" onclick="viewVersionHistory(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\'); return false;"><i class="fas fa-history me-2"></i>Version History</a></li>';
        
        if (!isArchived) {
            html += '<li><a class="dropdown-item" href="#" onclick="viewPermissions(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\'); return false;"><i class="fas fa-lock me-2"></i>Permissions</a></li>';
        }
        
        if (template.trigger === 'api') {
            html += '<li><a class="dropdown-item" href="#" onclick="viewApiStructure(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\'); return false;"><i class="fas fa-code me-2"></i>API Structure</a></li>';
        }
        
        if (!isArchived) {
            html += '<li><hr class="dropdown-divider"></li>';
            
            if (template.status === 'live' && AdminPermissions.canSuspend()) {
                html += '<li><a class="dropdown-item text-warning" href="#" onclick="suspendTemplate(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\', \'' + escapeJs(template.accountName) + '\'); return false;"><i class="fas fa-pause-circle me-2"></i>Suspend</a></li>';
            }
            
            if (template.status === 'paused' && AdminPermissions.canReactivate()) {
                html += '<li><a class="dropdown-item text-success" href="#" onclick="reactivateTemplate(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\', \'' + escapeJs(template.accountName) + '\'); return false;"><i class="fas fa-play-circle me-2"></i>Reactivate</a></li>';
            }
            
            if (AdminPermissions.canArchive()) {
                html += '<li><a class="dropdown-item text-danger" href="#" onclick="archiveTemplate(\'' + template.accountId + '\', \'' + template.templateId + '\', \'' + escapeJs(template.name) + '\', \'' + escapeJs(template.accountName) + '\', \'' + template.status + '\'); return false;"><i class="fas fa-archive me-2"></i>Archive</a></li>';
            }
        }
        
        html += '</ul>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html || '<tr><td colspan="11" class="text-center text-muted py-4">No templates match your filters</td></tr>';
    
    // Initialize Bootstrap dropdowns for dynamically added content with proper Popper config
    var dropdownElements = tbody.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdownElements.forEach(function(element) {
        new bootstrap.Dropdown(element, {
            popperConfig: {
                strategy: 'fixed',
                modifiers: [
                    {
                        name: 'preventOverflow',
                        options: {
                            boundary: 'viewport',
                            padding: 8
                        }
                    },
                    {
                        name: 'flip',
                        options: {
                            fallbackPlacements: ['top-end', 'bottom-end', 'left-start']
                        }
                    }
                ]
            }
        });
    });
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
    
    document.querySelectorAll('.api-table thead th').forEach(function(th) {
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

function viewDuplicate(accountId, templateId, templateName) {
    showToast('Duplicate is a customer action. Use impersonation to duplicate "' + templateName + '" for account ' + accountId + '.', 'info');
    
    AdminControlPlane.logAudit({
        action: 'TEMPLATE_DUPLICATE_VIEWED',
        severity: 'LOW',
        targetAccount: accountId,
        details: { templateId: templateId, templateName: templateName, note: 'Admin viewed duplicate option - customer action' }
    });
}

function viewVersionHistory(accountId, templateId, templateName) {
    showToast('Version history for "' + templateName + '" - feature coming soon. Use impersonation to access customer portal version history.', 'info');
    
    AdminControlPlane.logAudit({
        action: 'TEMPLATE_VERSION_HISTORY_VIEWED',
        severity: 'LOW',
        targetAccount: accountId,
        details: { templateId: templateId, templateName: templateName }
    });
}

function viewPermissions(accountId, templateId, templateName) {
    showToast('Permissions for "' + templateName + '" - feature coming soon. Use impersonation to manage template permissions.', 'info');
    
    AdminControlPlane.logAudit({
        action: 'TEMPLATE_PERMISSIONS_VIEWED',
        severity: 'LOW',
        targetAccount: accountId,
        details: { templateId: templateId, templateName: templateName }
    });
}

function viewApiStructure(accountId, templateId, templateName) {
    showToast('API Structure for "' + templateName + '" - feature coming soon. Use impersonation to view API payload structure.', 'info');
    
    AdminControlPlane.logAudit({
        action: 'TEMPLATE_API_STRUCTURE_VIEWED',
        severity: 'LOW',
        targetAccount: accountId,
        details: { templateId: templateId, templateName: templateName }
    });
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

</script>
@endpush

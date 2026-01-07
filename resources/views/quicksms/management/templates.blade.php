@extends('layouts.quicksms')

@section('title', 'Message Templates')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<style>
#rcsWizardModal {
    z-index: 1060 !important;
}
#rcsWizardModal .modal-backdrop,
.modal-backdrop.show + #rcsWizardModal {
    z-index: 1055 !important;
}
#rcsUnsavedChangesModal,
#rcsDeleteCardModal {
    z-index: 1070 !important;
}
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
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: var(--primary);
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
    overflow-x: auto;
}
.templates-table {
    width: 100%;
    margin: 0;
    min-width: 900px;
    table-layout: fixed;
}
.templates-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.templates-table thead th:first-child { width: 15%; }
.templates-table thead th:nth-child(2) { width: 10%; }
.templates-table thead th:nth-child(3) { width: 6%; }
.templates-table thead th:nth-child(4) { width: 10%; }
.templates-table thead th:nth-child(5) { width: 8%; }
.templates-table thead th:nth-child(6) { width: 18%; }
.templates-table thead th:nth-child(7) { width: 10%; }
.templates-table thead th:nth-child(8) { width: 8%; }
.templates-table thead th:nth-child(9) { width: 10%; }
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
    color: var(--primary);
}
.templates-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
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
.badge-sms {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-basic-rcs {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-rich-rcs {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
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
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
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
    color: #886CC0;
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
    background: rgba(136, 108, 192, 0.12);
    color: #886CC0;
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
    background: rgba(136, 108, 192, 0.12);
    color: #886CC0;
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
    background-color: #f0ebf8;
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
    background-color: rgba(136, 108, 192, 0.15);
    color: #886CC0;
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
    color: var(--primary);
}
.version-badge {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    background-color: #f0ebf8;
    color: var(--primary);
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.archived-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}
.archived-row:hover {
    opacity: 0.8;
}
.vh-audit-timeline {
    position: relative;
    padding-left: 2rem;
}
.vh-audit-timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.vh-audit-entry {
    position: relative;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
}
.vh-audit-entry:last-child {
    border-bottom: none;
}
.vh-audit-entry::before {
    content: '';
    position: absolute;
    left: -1.55rem;
    top: 1rem;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #886CC0;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}
.vh-audit-entry.action-created::before { background: #1cbb8c; }
.vh-audit-entry.action-edited::before { background: #3065D0; }
.vh-audit-entry.action-launched::before { background: #28a745; }
.vh-audit-entry.action-archived::before { background: #dc3545; }
.vh-audit-entry.action-rolled-back::before { background: #fd7e14; }
.vh-audit-entry.action-duplicated::before { background: #6f42c1; }
.vh-audit-entry.action-permissions::before { background: #17a2b8; }
.vh-audit-action {
    font-weight: 500;
    margin-bottom: 0.25rem;
}
.vh-audit-meta {
    font-size: 0.8rem;
    color: #6c757d;
}
.vh-version-current {
    background-color: rgba(136, 108, 192, 0.08);
}
.vh-version-current td:first-child {
    border-left: 3px solid #886CC0;
}
.form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
}
.wizard-steps {
    display: flex;
    gap: 1rem;
}
.wizard-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #adb5bd;
}
.wizard-step.active {
    color: var(--primary);
}
.wizard-step.completed {
    color: var(--primary);
}
.wizard-step .step-number {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}
.wizard-step.active .step-number {
    background-color: var(--primary);
    color: white;
}
.wizard-step.completed .step-number {
    background-color: var(--primary);
    color: white;
}
.wizard-step .step-label {
    font-size: 0.8rem;
    font-weight: 500;
}
.fullscreen-steps .wizard-step {
    color: rgba(255, 255, 255, 0.6);
}
.fullscreen-steps .wizard-step.active {
    color: #fff;
}
.fullscreen-steps .wizard-step.completed {
    color: #fff;
}
.fullscreen-steps .wizard-step .step-number {
    background-color: rgba(255, 255, 255, 0.3);
    color: #fff;
}
.fullscreen-steps .wizard-step.active .step-number {
    background-color: #fff;
    color: var(--primary);
}
.fullscreen-steps .wizard-step.completed .step-number {
    background-color: #fff;
    color: var(--primary);
}
.fullscreen-wizard-container {
    min-height: 100%;
    background: #f8f9fa;
}
.wizard-step-inner {
    background: #fff;
    border-radius: 0.75rem;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.trigger-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.trigger-option {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.trigger-option:hover {
    border-color: rgba(136, 108, 192, 0.5);
    background-color: #fdfcfe;
}
.trigger-option.selected {
    border-color: var(--primary);
    background-color: #f0ebf8;
}
.trigger-option .form-check {
    margin: 0;
    padding: 0;
}
.trigger-option .form-check-input {
    position: absolute;
    opacity: 0;
}
.trigger-option .form-check-label {
    width: 100%;
    cursor: pointer;
}
.trigger-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    font-size: 1rem;
}
.trigger-icon.bg-api {
    background-color: #6c757d;
}
.trigger-icon.bg-portal {
    background-color: #886CC0;
}
.trigger-icon.bg-email {
    background-color: #17a2b8;
}
.step2-locked-info {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    border-left: 3px solid var(--primary);
}
.tpl-builder-layout {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}
.tpl-builder-left {
    flex: 1 1 66.666%;
    min-width: 0;
}
.tpl-builder-right {
    flex: 0 0 33.333%;
    position: sticky;
    top: 0;
    align-self: flex-start;
}
@media (max-width: 991.98px) {
    .tpl-builder-layout {
        flex-direction: column;
    }
    .tpl-builder-left,
    .tpl-builder-right {
        flex: 0 0 100%;
        max-width: 100%;
        position: static;
    }
}
.alert-pastel-primary {
    background-color: #f0ebf8;
    border: 1px solid rgba(136, 108, 192, 0.2);
    color: #5a4a7a;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="templates-header">
        <div>
            <h2>Message Templates</h2>
            <p>Create and manage reusable SMS and RCS message templates</p>
        </div>
        <button class="btn btn-primary" id="createTemplateBtn">
            <i class="fas fa-plus me-2"></i>Create Template
        </button>
    </div>

    <div id="emptyState" class="empty-state" style="display: none;">
        <div class="empty-state-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <h4>No templates yet</h4>
        <p>Create your first message template to save time when sending messages. Templates can include personalization tags and are available for both SMS and RCS.</p>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus me-2"></i>Create Template
        </button>
    </div>

    <div id="templatesTableContainer" class="templates-table-container">
        <div class="search-filter-bar">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="templateSearch" placeholder="Search by name or ID...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="showArchivedToggle">
                    <label class="form-check-label small" for="showArchivedToggle">Show Archived</label>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i>Filters
                </button>
            </div>
        </div>

        <div class="collapse" id="filtersPanel">
            <div class="filters-panel">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Channel</label>
                        <select class="form-select form-select-sm" id="channelFilter">
                            <option value="">All Channels</option>
                            <option value="sms">SMS</option>
                            <option value="basic_rcs">Basic RCS + SMS</option>
                            <option value="rich_rcs">Rich RCS + SMS</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Trigger</label>
                        <select class="form-select form-select-sm" id="triggerFilter">
                            <option value="">All Triggers</option>
                            <option value="api">API</option>
                            <option value="portal">Portal</option>
                            <option value="email">Email-to-SMS</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="live">Live</option>
                            <option value="paused">Paused</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Sub-account</label>
                        <div class="dropdown multiselect-dropdown" id="subAccountDropdown">
                            <button class="btn btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                <span class="dropdown-label">All Sub-accounts</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="marketing" id="subMarketing">
                                    <label class="form-check-label" for="subMarketing">Marketing Team</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="sales" id="subSales">
                                    <label class="form-check-label" for="subSales">Sales</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="support" id="subSupport">
                                    <label class="form-check-label" for="subSupport">Support Team</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="it" id="subIT">
                                    <label class="form-check-label" for="subIT">IT Security</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="all" id="subAll">
                                    <label class="form-check-label" for="subAll">All Sub-accounts</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="filter-actions">
                            <button type="button" class="btn btn-primary btn-sm" id="applyFiltersBtn">
                                <i class="fas fa-check me-1"></i>Apply Filters
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFiltersBtn">
                                <i class="fas fa-undo me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="active-filters" id="activeFilters"></div>

        <div class="table-responsive">
            <table class="templates-table">
                <thead>
                    <tr>
                        <th data-sort="name" onclick="sortTable('name')">Template Name <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="templateId" onclick="sortTable('templateId')">Template ID <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="version" onclick="sortTable('version')">Version <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="channel" onclick="sortTable('channel')">Channel <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="trigger" onclick="sortTable('trigger')">Trigger <i class="fas fa-sort sort-icon"></i></th>
                        <th>Content Preview</th>
                        <th data-sort="accessScope" onclick="sortTable('accessScope')">Access Scope <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status" onclick="sortTable('status')">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="lastUpdated" onclick="sortTable('lastUpdated')">Last Updated <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="templatesBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createTemplateModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="height: 100vh; display: flex; flex-direction: column;">
            <div class="modal-header py-3 flex-shrink-0" style="background: linear-gradient(135deg, #886CC0 0%, #a78bda 100%); color: #fff;">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title mb-0"><i class="fas fa-file-alt me-2"></i>Create Template</h5>
                    <div class="wizard-steps ms-4 fullscreen-steps">
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
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body flex-grow-1 p-0" style="overflow-y: auto; background: #f8f9fa;">
                <div id="wizardStep1" class="wizard-content p-4">
                    <div class="wizard-step-inner mx-auto" style="max-width: 800px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            <strong>Step 1: Template Metadata</strong> - Define the basic information for your template. The trigger type determines where and how the template can be used.
                        </div>
                        
                        <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="templateName" placeholder="e.g., Welcome Message, Appointment Reminder" maxlength="100">
                                <div class="invalid-feedback">Please enter a template name</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Template ID</label>
                                <input type="text" class="form-control bg-light" id="templateIdField" readonly>
                                <small class="text-muted">Auto-generated, read-only</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Trigger Type <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2">Select how this template will be triggered. This cannot be changed after creation.</p>
                        
                        <div class="trigger-options">
                            <div class="trigger-option" data-trigger="api">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="templateTrigger" id="triggerApi" value="api">
                                    <label class="form-check-label" for="triggerApi">
                                        <div class="d-flex align-items-center">
                                            <div class="trigger-icon bg-api">
                                                <i class="fas fa-code"></i>
                                            </div>
                                            <div>
                                                <strong>API</strong>
                                                <p class="mb-0 small text-muted">Template is called via API only. Assign to specific sub-accounts for access control.</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="trigger-option" data-trigger="portal">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="templateTrigger" id="triggerPortal" value="portal">
                                    <label class="form-check-label" for="triggerPortal">
                                        <div class="d-flex align-items-center">
                                            <div class="trigger-icon bg-portal">
                                                <i class="fas fa-desktop"></i>
                                            </div>
                                            <div>
                                                <strong>Portal</strong>
                                                <p class="mb-0 small text-muted">Template is visible and selectable when sending messages through the QuickSMS portal.</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="trigger-option" data-trigger="email">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="templateTrigger" id="triggerEmail" value="email">
                                    <label class="form-check-label" for="triggerEmail">
                                        <div class="d-flex align-items-center">
                                            <div class="trigger-icon bg-email">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                            <div>
                                                <strong>Email-to-SMS</strong>
                                                <p class="mb-0 small text-muted">Template is visible only in Email-to-SMS configuration for automated email conversion.</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="triggerError" style="display: none;">Please select a trigger type</div>
                    </div>
                    </div>
                </div>
                
                <div id="wizardStep2" class="wizard-content p-4" style="display: none;">
                    <div class="alert alert-pastel-primary mb-3 mx-auto" style="max-width: 1200px;">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        <strong>Step 2: Message Content</strong> - Create your message content using the same editor as Send Message. You can use personalization tags to customize messages for each recipient.
                    </div>
                    
                    <div class="step2-locked-info mb-3 mx-auto" style="max-width: 1200px;">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div>
                                <small class="text-muted">Template Name</small>
                                <div class="fw-semibold" id="step2TemplateName">-</div>
                            </div>
                            <div class="vr d-none d-md-block"></div>
                            <div>
                                <small class="text-muted">Template ID</small>
                                <div class="fw-semibold" id="step2TemplateId">-</div>
                            </div>
                            <div class="vr d-none d-md-block"></div>
                            <div>
                                <small class="text-muted">Trigger</small>
                                <div>
                                    <span class="badge rounded-pill" id="step2TriggerBadge">-</span>
                                    <i class="fas fa-lock ms-1 text-muted small" title="Locked after creation"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tpl-builder-layout mx-auto" style="max-width: 1200px;">
                        <div class="tpl-builder-left">
                            <div class="card mb-3">
                                <div class="card-body p-4">
                                    <h6 class="mb-3">Channel & Sender</h6>
                                    <div class="btn-group w-100 mb-3" role="group">
                                        <input type="radio" class="btn-check" name="templateChannel" id="tplChannelSMS" value="sms" checked>
                                        <label class="btn btn-outline-primary" for="tplChannelSMS"><i class="fas fa-sms me-1"></i>SMS only</label>
                                        <input type="radio" class="btn-check" name="templateChannel" id="tplChannelRCSBasic" value="basic_rcs">
                                        <label class="btn btn-outline-primary" for="tplChannelRCSBasic" data-bs-toggle="tooltip" title="Text-only RCS with SMS fallback"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                                        <input type="radio" class="btn-check" name="templateChannel" id="tplChannelRCSRich" value="rich_rcs">
                                        <label class="btn btn-outline-primary" for="tplChannelRCSRich" data-bs-toggle="tooltip" title="Rich cards, images & buttons with SMS fallback"><i class="fas fa-image me-1"></i>Rich RCS</label>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6" id="tplSenderIdSection">
                                            <select class="form-select" id="tplSenderId" onchange="updateTemplatePreview()">
                                                <option value="">SMS Sender ID *</option>
                                                @foreach($sender_ids as $sender)
                                                <option value="{{ $sender['id'] }}">{{ $sender['name'] }} ({{ $sender['type'] }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 d-none" id="tplRcsAgentSection">
                                            <select class="form-select" id="tplRcsAgent" onchange="updateTemplatePreview()">
                                                <option value="">RCS Agent *</option>
                                                @foreach($rcs_agents as $agent)
                                                <option value="{{ $agent['id'] }}" 
                                                    data-name="{{ $agent['name'] }}"
                                                    data-logo="{{ $agent['logo'] ?? '' }}"
                                                    data-tagline="{{ $agent['tagline'] ?? '' }}"
                                                    data-brand-color="{{ $agent['brand_color'] ?? '#886CC0' }}">{{ $agent['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Content</h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="openTemplateAiAssistant()">
                                            <i class="fas fa-magic me-1"></i>Improve with AI
                                        </button>
                                    </div>
                                    
                                    <label class="form-label mb-2" id="tplContentLabel">SMS Content</label>
                                    
                                    <div class="position-relative border rounded mb-2" id="tplTextEditorContainer">
                                        <textarea class="form-control border-0" id="templateContent" rows="5" placeholder="Type your message here..." oninput="handleTemplateContentChange()" style="padding-bottom: 40px;"></textarea>
                                        <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openTemplatePersonalisation()" title="Insert personalisation">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border" id="tplEmojiPickerBtn" onclick="openTemplateEmojiPicker()" title="Insert emoji">
                                                <i class="fas fa-smile"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="text-muted me-3">Characters: <strong id="tplCharCount">0</strong></span>
                                            <span class="text-muted me-3">Encoding: <strong id="tplEncodingType">GSM-7</strong></span>
                                            <span class="text-muted">Segments: <strong id="tplPartCount">1</strong></span>
                                        </div>
                                        <span class="badge bg-warning text-dark d-none" id="tplUnicodeWarning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Unicode
                                        </span>
                                    </div>
                                    
                                    <div class="border-top pt-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="tplIncludeTrackableLink" onchange="toggleTplTrackableLink()">
                                                    <label class="form-check-label" for="tplIncludeTrackableLink">Include trackable link</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="tplMessageExpiry" onchange="toggleTplMessageExpiry()">
                                                    <label class="form-check-label" for="tplMessageExpiry">Message expiry</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-none mb-2" id="tplTrackableLinkSummary">
                                        <div class="alert alert-secondary py-2 mb-0">
                                            <i class="fas fa-link me-2"></i>Trackable link: <strong id="tplTrackableLinkDomain">qsms.uk</strong>
                                            <a href="#" class="ms-2" onclick="openTplTrackableLinkModal(); return false;">Edit</a>
                                        </div>
                                    </div>
                                    
                                    <div class="d-none mb-2" id="tplMessageExpirySummary">
                                        <div class="alert alert-secondary py-2 mb-0">
                                            <i class="fas fa-hourglass-half me-2"></i>Message expiry: <strong id="tplMessageExpiryValue">24 Hours</strong>
                                            <a href="#" class="ms-2" onclick="openTplMessageExpiryModal(); return false;">Edit</a>
                                        </div>
                                    </div>
                                    
                                    <div class="d-none mb-2" id="tplRcsTextHelper">
                                        <div class="alert alert-info py-2 mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Messages over 160 characters will be automatically sent as a single RCS message where supported.
                                        </div>
                                    </div>
                                    
                                    <div class="d-none mt-3" id="tplRcsContentSection">
                                        <div class="border rounded p-3 text-center" style="background-color: rgba(136, 108, 192, 0.1); border-color: rgba(136, 108, 192, 0.2) !important;">
                                            <i class="fas fa-image fa-2x text-primary mb-2"></i>
                                            <h6 class="mb-2">Rich RCS Card</h6>
                                            <p class="text-muted small mb-3">Create rich media cards with images, descriptions, and interactive buttons.</p>
                                            <button type="button" class="btn btn-primary" onclick="openTemplateRcsWizard()">
                                                <i class="fas fa-magic me-1"></i>Create RCS Message
                                            </button>
                                            <div class="d-none mt-3" id="tplRcsConfiguredSummary">
                                                <div class="alert alert-primary py-2 mb-0">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    <span id="tplRcsConfiguredText">RCS content configured</span>
                                                    <a href="#" class="ms-2" onclick="openTemplateRcsWizard(); return false;">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3" id="tplPlaceholderCard">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Detected Placeholders</h6>
                                        <span class="placeholder-counter empty" id="tplPlaceholderCount">0 placeholders</span>
                                    </div>
                                    
                                    <div id="tplNoPlaceholders" class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>No placeholders detected. Add placeholders like <code>{FirstName}</code> to personalize messages.
                                    </div>
                                    
                                    <div id="tplPlaceholderList" class="d-none">
                                        <div class="d-flex flex-wrap gap-2 mb-3" id="tplPlaceholderChips"></div>
                                        
                                        <div class="border-top pt-3">
                                            <h6 class="small text-muted mb-2">Placeholder Sources</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-address-book text-primary me-2"></i>
                                                        <span class="small"><strong>Contact Book:</strong> FirstName, LastName, Email, Phone, Company</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-code text-secondary me-2"></i>
                                                        <span class="small"><strong>API Payload:</strong> Custom fields via request body</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3 d-none" id="tplApiRulesCard">
                                <div class="card-body p-4">
                                    <h6 class="mb-3"><i class="fas fa-code me-2 text-secondary"></i>API Template Rules</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 mb-3 mb-md-0" id="tplApiRuleSingle">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="placeholder-counter" style="padding: 0.35rem 0.65rem;">With Placeholders</span>
                                                </div>
                                                <ul class="small mb-0 ps-3">
                                                    <li>Only <strong>1 MSISDN</strong> per API request</li>
                                                    <li>All placeholders must be provided in payload</li>
                                                    <li>Missing placeholders block execution</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3" id="tplApiRuleMultiple">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="placeholder-counter empty" style="padding: 0.35rem 0.65rem;">Without Placeholders</span>
                                                </div>
                                                <ul class="small mb-0 ps-3">
                                                    <li>Multiple MSISDNs allowed per request</li>
                                                    <li>Same message sent to all recipients</li>
                                                    <li>Batch sending supported</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="viewPlaceholderSchema()">
                                            <i class="fas fa-file-code me-1"></i>View Placeholder Schema
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Opt-out Management</h6>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="tplEnableOptout" onchange="toggleTplOptoutManagement()">
                                            <label class="form-check-label" for="tplEnableOptout">Enable</label>
                                        </div>
                                    </div>
                                    
                                    <div class="d-none" id="tplOptoutSection">
                                        <div class="mb-3">
                                            <label class="form-label">Opt-out list <span class="text-muted">(optional)</span></label>
                                            <select class="form-select" id="tplOptoutList">
                                                <option value="" selected>No list selected</option>
                                                @foreach($opt_out_lists as $list)
                                                <option value="{{ $list['id'] }}">{{ $list['name'] }} ({{ number_format($list['count']) }})</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Select a list to exclude numbers when this template is used.</small>
                                        </div>
                                        
                                        <div class="border-top pt-3">
                                            <h6 class="mb-3">Opt-out Options</h6>
                                            
                                            @if(count($virtual_numbers) > 0)
                                            <div class="mb-3 p-3 border rounded">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" id="tplEnableReplyOptout" onchange="toggleTplReplyOptout()">
                                                    <label class="form-check-label fw-medium" for="tplEnableReplyOptout">Enable reply-to-opt-out</label>
                                                </div>
                                                <div class="d-none ps-3" id="tplReplyOptoutConfig">
                                                    <div class="mb-2">
                                                        <label class="form-label">Virtual Number</label>
                                                        <select class="form-select form-select-sm" id="tplReplyVirtualNumber">
                                                            <option value="">-- Select virtual number --</option>
                                                            @foreach($virtual_numbers as $vn)
                                                            <option value="{{ $vn['id'] }}" data-number="{{ $vn['number'] }}">{{ $vn['number'] }} ({{ $vn['label'] }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Opt-out Text</label>
                                                        <input type="text" class="form-control form-control-sm" id="tplReplyOptoutText" value="Reply STOP to @{{number}}" placeholder="e.g. Reply STOP to @{{number}}">
                                                        <small class="text-muted">Use @{{number}} to insert the virtual number.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            
                                            <div class="p-3 border rounded">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" id="tplEnableUrlOptout" onchange="toggleTplUrlOptout()">
                                                    <label class="form-check-label fw-medium" for="tplEnableUrlOptout">Enable click-to-opt-out</label>
                                                </div>
                                                <div class="d-none ps-3" id="tplUrlOptoutConfig">
                                                    <div class="mb-2">
                                                        <label class="form-label">URL Domain</label>
                                                        <select class="form-select form-select-sm" id="tplUrlOptoutDomain">
                                                            @foreach($optout_domains as $domain)
                                                            <option value="{{ $domain['id'] }}" {{ $domain['is_default'] ? 'selected' : '' }}>{{ $domain['domain'] }}{{ $domain['is_default'] ? ' (default)' : '' }}</option>
                                                            @endforeach
                                                        </select>
                                                        <small class="text-muted">A unique URL will be generated per message.</small>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Opt-out Text</label>
                                                        <input type="text" class="form-control form-control-sm" id="tplUrlOptoutText" value="Opt-out: Click @{{unique_url}}" placeholder="e.g. Click @{{unique_url}}">
                                                        <small class="text-muted">Use @{{unique_url}} to insert the tracking URL.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-secondary">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Template Mode:</strong> This editor matches Send Message but without recipients, pricing, or scheduling options. Templates can be reused across campaigns.
                            </div>
                        </div>
                        
                        <div class="tpl-builder-right">
                            <div class="card mb-3">
                                <div class="card-body p-4">
                                    <h6 class="mb-3">Message Preview</h6>
                                    <div id="tplPreviewContainer" class="d-flex justify-content-center" style="transform: scale(0.85); transform-origin: top center; margin-bottom: -70px;"></div>
                                    
                                    <div class="text-center d-none" id="tplPreviewToggleContainer">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-sm py-0 px-3 active" id="tplPreviewRCSBtn" onclick="showTemplatePreview('rcs')" style="font-size: 11px; background: #886CC0; color: white; border: 1px solid #886CC0;">RCS</button>
                                            <button type="button" class="btn btn-sm py-0 px-3" id="tplPreviewSMSBtn" onclick="showTemplatePreview('sms')" style="font-size: 11px; background: white; color: #886CC0; border: 1px solid #886CC0;">SMS</button>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center d-none" id="tplBasicRcsPreviewToggle">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-sm py-0 px-3 active" id="tplBasicPreviewRCSBtn" onclick="toggleTemplateBasicRcsPreview('rcs')" style="font-size: 11px; background: #886CC0; color: white; border: 1px solid #886CC0;">RCS</button>
                                            <button type="button" class="btn btn-sm py-0 px-3" id="tplBasicPreviewSMSBtn" onclick="toggleTemplateBasicRcsPreview('sms')" style="font-size: 11px; background: white; color: #886CC0; border: 1px solid #886CC0;">SMS</button>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 border-top pt-2">
                                        <div class="row text-center">
                                            <div class="col-6"><small class="text-muted d-block mb-1">Channel</small><strong id="tplPreviewChannel" class="small">SMS</strong></div>
                                            <div class="col-6"><small class="text-muted d-block mb-1">Segments</small><strong id="tplPreviewSegments" class="small">1</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="wizardStep3" class="wizard-content p-4" style="display: none;">
                    <div class="wizard-step-inner mx-auto" style="max-width: 900px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <i class="fas fa-shield-alt me-2 text-primary"></i>
                            <strong>Step 3: Permissions</strong> - Define who can access and use this template.
                        </div>
                        
                        <div class="step3-locked-info mb-3">
                            <div class="d-flex gap-4 flex-wrap">
                                <div><small class="text-muted d-block">Template Name</small><div class="fw-semibold" id="step3TemplateName">-</div></div>
                                <div><small class="text-muted d-block">Trigger</small><span class="badge rounded-pill" id="step3TriggerBadge">-</span></div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning py-2 d-none" id="wizardPermApiWarning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>API templates</strong> must be assigned to at least one sub-account.
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Access Mode</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="wizardAccessMode" id="wizardAccessAll" value="all" onchange="updateWizardAccessMode()">
                                            <label class="form-check-label" for="wizardAccessAll">
                                                <i class="fas fa-globe me-1 text-success"></i>All Users
                                                <small class="text-muted d-block">Everyone in your organization can use this template</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="wizardAccessMode" id="wizardAccessRestricted" value="restricted" checked onchange="updateWizardAccessMode()">
                                            <label class="form-check-label" for="wizardAccessRestricted">
                                                <i class="fas fa-lock me-1 text-warning"></i>Restricted Access
                                                <small class="text-muted d-block">Only selected sub-accounts, roles, or users</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="wizardRestrictedSection">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fas fa-building me-1 text-primary"></i>Sub-accounts
                                                <span class="placeholder-counter ms-2" id="wizardSubAccountCount">0</span>
                                            </label>
                                            <div class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-subaccount-check" type="checkbox" value="all" id="wizardSubAll" onchange="toggleWizardSubAccount('all')">
                                                    <label class="form-check-label small" for="wizardSubAll">
                                                        <i class="fas fa-globe me-1 text-muted"></i>All Sub-accounts
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-subaccount-check" type="checkbox" value="main" id="wizardSubMain" onchange="toggleWizardSubAccount('main')">
                                                    <label class="form-check-label small" for="wizardSubMain">Main Account</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-subaccount-check" type="checkbox" value="marketing" id="wizardSubMarketing" onchange="toggleWizardSubAccount('marketing')">
                                                    <label class="form-check-label small" for="wizardSubMarketing">Marketing Team</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-subaccount-check" type="checkbox" value="sales" id="wizardSubSales" onchange="toggleWizardSubAccount('sales')">
                                                    <label class="form-check-label small" for="wizardSubSales">Sales Department</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-subaccount-check" type="checkbox" value="support" id="wizardSubSupport" onchange="toggleWizardSubAccount('support')">
                                                    <label class="form-check-label small" for="wizardSubSupport">Customer Support</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fas fa-user-tag me-1 text-info"></i>Roles
                                                <span class="placeholder-counter ms-2" id="wizardRoleCount">0</span>
                                            </label>
                                            <div class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-role-check" type="checkbox" value="admin" id="wizardRoleAdmin" onchange="toggleWizardRole('admin')">
                                                    <label class="form-check-label small" for="wizardRoleAdmin">
                                                        <i class="fas fa-crown me-1 text-warning"></i>Administrator
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-role-check" type="checkbox" value="manager" id="wizardRoleManager" onchange="toggleWizardRole('manager')">
                                                    <label class="form-check-label small" for="wizardRoleManager">
                                                        <i class="fas fa-user-tie me-1 text-info"></i>Manager
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-role-check" type="checkbox" value="messaging" id="wizardRoleMessaging" onchange="toggleWizardRole('messaging')">
                                                    <label class="form-check-label small" for="wizardRoleMessaging">
                                                        <i class="fas fa-envelope me-1 text-primary"></i>Messaging User
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-role-check" type="checkbox" value="viewer" id="wizardRoleViewer" onchange="toggleWizardRole('viewer')">
                                                    <label class="form-check-label small" for="wizardRoleViewer">
                                                        <i class="fas fa-eye me-1 text-secondary"></i>Viewer
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input wizard-role-check" type="checkbox" value="api" id="wizardRoleApi" onchange="toggleWizardRole('api')">
                                                    <label class="form-check-label small" for="wizardRoleApi">
                                                        <i class="fas fa-code me-1 text-dark"></i>API User
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fas fa-user me-1 text-secondary"></i>Users <small class="text-muted">(optional)</small>
                                                <span class="placeholder-counter ms-2" id="wizardUserCount">0</span>
                                            </label>
                                            <div class="input-group input-group-sm mb-2">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" id="wizardUserSearch" placeholder="Search users..." oninput="filterWizardUsers()">
                                            </div>
                                            <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;" id="wizardUserList">
                                                <div class="form-check mb-2 wizard-user-item" data-name="john smith">
                                                    <input class="form-check-input wizard-user-check" type="checkbox" value="user1" id="wizardUser1" onchange="toggleWizardUser('user1')">
                                                    <label class="form-check-label small" for="wizardUser1">John Smith</label>
                                                </div>
                                                <div class="form-check mb-2 wizard-user-item" data-name="sarah wilson">
                                                    <input class="form-check-input wizard-user-check" type="checkbox" value="user2" id="wizardUser2" onchange="toggleWizardUser('user2')">
                                                    <label class="form-check-label small" for="wizardUser2">Sarah Wilson</label>
                                                </div>
                                                <div class="form-check mb-2 wizard-user-item" data-name="mike johnson">
                                                    <input class="form-check-input wizard-user-check" type="checkbox" value="user3" id="wizardUser3" onchange="toggleWizardUser('user3')">
                                                    <label class="form-check-label small" for="wizardUser3">Mike Johnson</label>
                                                </div>
                                                <div class="form-check mb-2 wizard-user-item" data-name="emily davis">
                                                    <input class="form-check-input wizard-user-check" type="checkbox" value="user4" id="wizardUser4" onchange="toggleWizardUser('user4')">
                                                    <label class="form-check-label small" for="wizardUser4">Emily Davis</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="wizardStep4" class="wizard-content p-4" style="display: none;">
                    <div class="wizard-step-inner mx-auto" style="max-width: 800px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <i class="fas fa-check-circle me-2 text-primary"></i>
                            <strong>Step 4: Review & Save</strong> - Review your template details before saving.
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h6 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Template Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">Template Name</label>
                                        <p class="mb-0 fw-semibold" id="reviewTemplateName">-</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">Template ID</label>
                                        <p class="mb-0"><code id="reviewTemplateId">-</code></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">Channel</label>
                                        <p class="mb-0" id="reviewChannel">-</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small text-muted">Trigger</label>
                                        <p class="mb-0" id="reviewTrigger">-</p>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Content Preview</label>
                                    <div class="border rounded p-3 bg-light" id="reviewContent">-</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Access Mode</label>
                                        <p class="mb-0" id="reviewAccessMode">-</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Permissions</label>
                                        <p class="mb-0" id="reviewPermissions">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info py-2">
                            <i class="fas fa-info-circle me-2"></i>
                            Template will be created as <strong>Draft</strong>. You can launch it to make it Live after creation.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer flex-shrink-0 py-3 border-top">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-secondary" id="wizardBackBtn" style="display: none;" onclick="wizardBack()">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </button>
                <button type="button" class="btn btn-primary" id="wizardNextBtn" onclick="wizardNext()">
                    Continue<i class="fas fa-arrow-right ms-2"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="wizardSaveDraftBtn" style="display: none;" onclick="saveTemplateAsDraft()">
                    <i class="fas fa-save me-2"></i>Save Draft
                </button>
                <button type="button" class="btn btn-success" id="wizardLaunchBtn" style="display: none;" onclick="confirmLaunchTemplate()">
                    <i class="fas fa-rocket me-2"></i>Launch
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="templatePersonalisationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-user-tag me-2 text-primary"></i>Insert Personalisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Contact Book Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertTemplatePlaceholder('firstName')">{FirstName}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertTemplatePlaceholder('lastName')">{LastName}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertTemplatePlaceholder('company')">{Company}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertTemplatePlaceholder('email')">{Email}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertTemplatePlaceholder('phone')">{Phone}</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Custom Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplatePlaceholder('custom1')">{Custom1}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplatePlaceholder('custom2')">{Custom2}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplatePlaceholder('custom3')">{Custom3}</button>
                    </div>
                </div>
                <div>
                    <h6 class="text-muted mb-2">System Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="insertTemplatePlaceholder('date')">{Date}</button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="insertTemplatePlaceholder('time')">{Time}</button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="insertTemplatePlaceholder('shortUrl')">{ShortURL}</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="templateEmojiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-smile me-2 text-primary"></i>Insert Emoji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Smileys</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('😀')">😀</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('😊')">😊</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('😂')">😂</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('🤔')">🤔</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('😍')">😍</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('🥳')">🥳</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('👋')">👋</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('👍')">👍</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Objects</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('📱')">📱</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('📧')">📧</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('💬')">💬</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('🔔')">🔔</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('⭐')">⭐</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('🎉')">🎉</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('💯')">💯</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('🔥')">🔥</button>
                    </div>
                </div>
                <div>
                    <h6 class="text-muted mb-2">Symbols</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('✅')">✅</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('❌')">❌</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('⚠️')">⚠️</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('ℹ️')">ℹ️</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('➡️')">➡️</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('🔗')">🔗</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('💰')">💰</button>
                        <button type="button" class="btn btn-light btn-sm" onclick="insertTemplateEmoji('🛒')">🛒</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="placeholderSchemaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-file-code me-2 text-primary"></i>Placeholder Schema for API Templates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>API Request Requirements:</strong> When sending messages via API, you must provide values for all placeholders used in the template.
                </div>
                
                <h6 class="mb-3">Detected Placeholders in This Template</h6>
                <div class="bg-light p-3 rounded mb-4" id="schemaPlaceholdersList">
                    <em class="text-muted">No placeholders detected</em>
                </div>
                
                <h6 class="mb-3">Example API Request</h6>
                <pre class="bg-dark text-light p-3 rounded" style="font-size: 0.85rem;"><code id="schemaExampleRequest">{
  "template_id": "12345678",
  "msisdn": "+447700900123",
  "placeholders": {
    "FirstName": "John",
    "LastName": "Doe"
  }
}</code></pre>
                
                <h6 class="mb-3">Validation Rules</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Rule</th>
                            <th>Description</th>
                            <th>Error Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>MISSING_PLACEHOLDER</code></td>
                            <td>A required placeholder was not provided in the request</td>
                            <td><span class="badge bg-danger">400</span></td>
                        </tr>
                        <tr>
                            <td><code>SINGLE_MSISDN_REQUIRED</code></td>
                            <td>Templates with placeholders only accept 1 MSISDN per request</td>
                            <td><span class="badge bg-danger">400</span></td>
                        </tr>
                        <tr>
                            <td><code>INVALID_PLACEHOLDER_VALUE</code></td>
                            <td>Placeholder value exceeds maximum length or contains invalid characters</td>
                            <td><span class="badge bg-danger">400</span></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Placeholder names are case-sensitive. <code>{FirstName}</code> and <code>{firstname}</code> are treated as different placeholders.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary" onclick="copySchemaExample()">
                    <i class="fas fa-copy me-1"></i>Copy Example
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="templateAiModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-magic me-2 text-primary"></i>AI Content Assistant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="mb-3">Current Message</h6>
                    <div class="bg-light p-3 rounded" id="tplAiCurrentContent">
                        <em class="text-muted">No content to improve</em>
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="mb-3">What would you like to do?</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="templateAiImprove('tone')"><i class="fas fa-smile me-1"></i>Improve tone</button>
                        <button type="button" class="btn btn-outline-primary" onclick="templateAiImprove('shorten')"><i class="fas fa-compress-alt me-1"></i>Shorten message</button>
                        <button type="button" class="btn btn-outline-primary" onclick="templateAiImprove('grammar')"><i class="fas fa-spell-check me-1"></i>Correct spelling & grammar</button>
                        <button type="button" class="btn btn-outline-primary" onclick="templateAiImprove('clarity')"><i class="fas fa-lightbulb me-1"></i>Rephrase for clarity</button>
                    </div>
                </div>
                <div class="d-none" id="tplAiResultSection">
                    <h6 class="mb-3">Suggested Version</h6>
                    <div class="bg-success bg-opacity-10 border border-success p-3 rounded mb-3" id="tplAiSuggestedContent"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="useTemplateAiSuggestion()"><i class="fas fa-check me-1"></i>Use this</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="discardTemplateAiSuggestion()">Discard</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="templatePermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-shield-alt me-2 text-primary"></i>Template Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <h6 class="mb-1" id="permTemplateName">Template Name</h6>
                        <small class="text-muted">ID: <span id="permTemplateId">00000000</span></small>
                    </div>
                    <div>
                        <span class="badge bg-secondary" id="permAccessBadge">Restricted</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold">Access Mode</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="permAccessMode" id="permAccessAll" value="all" onchange="updatePermAccessMode()">
                            <label class="form-check-label" for="permAccessAll">
                                <i class="fas fa-globe me-1 text-success"></i>All Users
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="permAccessMode" id="permAccessRestricted" value="restricted" checked onchange="updatePermAccessMode()">
                            <label class="form-check-label" for="permAccessRestricted">
                                <i class="fas fa-lock me-1 text-warning"></i>Restricted Access
                            </label>
                        </div>
                    </div>
                </div>
                
                <div id="permRestrictedSection">
                    <ul class="nav nav-pills mb-4" id="permissionsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="subaccounts-tab" data-bs-toggle="pill" data-bs-target="#subaccounts-pane" type="button" role="tab">
                                <i class="fas fa-building me-1"></i>Sub-accounts <span class="badge bg-primary ms-1" id="permSubAccountCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="roles-tab" data-bs-toggle="pill" data-bs-target="#roles-pane" type="button" role="tab">
                                <i class="fas fa-user-tag me-1"></i>Roles <span class="badge bg-primary ms-1" id="permRoleCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="users-tab" data-bs-toggle="pill" data-bs-target="#users-pane" type="button" role="tab">
                                <i class="fas fa-user me-1"></i>Users <span class="badge bg-primary ms-1" id="permUserCount">0</span>
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="permissionsTabContent">
                        <div class="tab-pane fade show active" id="subaccounts-pane" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Select Sub-accounts</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="permSubAccountSearch" placeholder="Search sub-accounts..." oninput="filterPermSubAccounts()">
                                </div>
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;" id="permSubAccountList">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-subaccount-check" type="checkbox" value="all" id="permSubAll" onchange="togglePermSubAccount('all')">
                                        <label class="form-check-label" for="permSubAll">
                                            <i class="fas fa-globe me-1 text-muted"></i>All Sub-accounts
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-subaccount-check" type="checkbox" value="main" id="permSubMain" onchange="togglePermSubAccount('main')">
                                        <label class="form-check-label" for="permSubMain">
                                            <i class="fas fa-building me-1 text-primary"></i>Main Account
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-subaccount-check" type="checkbox" value="marketing" id="permSubMarketing" onchange="togglePermSubAccount('marketing')">
                                        <label class="form-check-label" for="permSubMarketing">
                                            <i class="fas fa-building me-1 text-primary"></i>Marketing Team
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-subaccount-check" type="checkbox" value="sales" id="permSubSales" onchange="togglePermSubAccount('sales')">
                                        <label class="form-check-label" for="permSubSales">
                                            <i class="fas fa-building me-1 text-primary"></i>Sales Department
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-subaccount-check" type="checkbox" value="support" id="permSubSupport" onchange="togglePermSubAccount('support')">
                                        <label class="form-check-label" for="permSubSupport">
                                            <i class="fas fa-building me-1 text-primary"></i>Customer Support
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id="permSubAccountChips" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        
                        <div class="tab-pane fade" id="roles-pane" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Select Roles</label>
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;" id="permRoleList">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-role-check" type="checkbox" value="admin" id="permRoleAdmin" onchange="togglePermRole('admin')">
                                        <label class="form-check-label" for="permRoleAdmin">
                                            <i class="fas fa-crown me-1 text-warning"></i>Administrator
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-role-check" type="checkbox" value="manager" id="permRoleManager" onchange="togglePermRole('manager')">
                                        <label class="form-check-label" for="permRoleManager">
                                            <i class="fas fa-user-tie me-1 text-info"></i>Manager
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-role-check" type="checkbox" value="messaging" id="permRoleMessaging" onchange="togglePermRole('messaging')">
                                        <label class="form-check-label" for="permRoleMessaging">
                                            <i class="fas fa-envelope me-1 text-primary"></i>Messaging User
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-role-check" type="checkbox" value="viewer" id="permRoleViewer" onchange="togglePermRole('viewer')">
                                        <label class="form-check-label" for="permRoleViewer">
                                            <i class="fas fa-eye me-1 text-secondary"></i>Viewer
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-role-check" type="checkbox" value="api" id="permRoleApi" onchange="togglePermRole('api')">
                                        <label class="form-check-label" for="permRoleApi">
                                            <i class="fas fa-code me-1 text-dark"></i>API User
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id="permRoleChips" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        
                        <div class="tab-pane fade" id="users-pane" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Select Individual Users</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="permUserSearch" placeholder="Search users by name or email..." oninput="filterPermUsers()">
                                </div>
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;" id="permUserList">
                                    <div class="form-check mb-2 perm-user-item" data-name="john smith" data-email="john.smith@company.com">
                                        <input class="form-check-input perm-user-check" type="checkbox" value="user1" id="permUser1" onchange="togglePermUser('user1')">
                                        <label class="form-check-label" for="permUser1">
                                            <i class="fas fa-user-circle me-1 text-primary"></i>John Smith <small class="text-muted">(john.smith@company.com)</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2 perm-user-item" data-name="sarah jones" data-email="sarah.jones@company.com">
                                        <input class="form-check-input perm-user-check" type="checkbox" value="user2" id="permUser2" onchange="togglePermUser('user2')">
                                        <label class="form-check-label" for="permUser2">
                                            <i class="fas fa-user-circle me-1 text-primary"></i>Sarah Jones <small class="text-muted">(sarah.jones@company.com)</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2 perm-user-item" data-name="mike wilson" data-email="mike.wilson@company.com">
                                        <input class="form-check-input perm-user-check" type="checkbox" value="user3" id="permUser3" onchange="togglePermUser('user3')">
                                        <label class="form-check-label" for="permUser3">
                                            <i class="fas fa-user-circle me-1 text-primary"></i>Mike Wilson <small class="text-muted">(mike.wilson@company.com)</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2 perm-user-item" data-name="emily brown" data-email="emily.brown@company.com">
                                        <input class="form-check-input perm-user-check" type="checkbox" value="user4" id="permUser4" onchange="togglePermUser('user4')">
                                        <label class="form-check-label" for="permUser4">
                                            <i class="fas fa-user-circle me-1 text-primary"></i>Emily Brown <small class="text-muted">(emily.brown@company.com)</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2 perm-user-item" data-name="david lee" data-email="david.lee@company.com">
                                        <input class="form-check-input perm-user-check" type="checkbox" value="user5" id="permUser5" onchange="togglePermUser('user5')">
                                        <label class="form-check-label" for="permUser5">
                                            <i class="fas fa-user-circle me-1 text-primary"></i>David Lee <small class="text-muted">(david.lee@company.com)</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id="permUserChips" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 d-none" id="permNoSelectionWarning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Restricted templates require at least one sub-account, role, or user to have access.
                    </div>
                    
                    <div class="alert alert-warning mt-3 d-none" id="permApiWarning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>API Template:</strong> Sub-account permissions are validated on API calls. Ensure selected sub-accounts have API access enabled.
                    </div>
                </div>
                
                <div id="permAllUsersSection" class="d-none">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>All Users:</strong> This template is accessible to everyone in your organization. No restrictions apply.
                    </div>
                </div>
                
                <div class="border-top pt-3 mt-3">
                    <h6 class="mb-2"><i class="fas fa-clipboard-list me-2 text-muted"></i>Access Summary</h6>
                    <div class="bg-light p-3 rounded" id="permAccessSummary">
                        <span class="text-muted">No access configured</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplatePermissions()" id="permSaveBtn">
                    <i class="fas fa-save me-2"></i>Save Permissions
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="apiStructureModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-code me-2 text-primary"></i>API Structure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <h5 class="mb-1" id="apiTemplateName">Template Name</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary" id="apiTemplateIdBadge">ID: 00000000</span>
                            <span class="badge bg-primary" id="apiChannelBadge">SMS</span>
                            <span class="badge bg-info" id="apiVersionBadge">v1</span>
                        </div>
                    </div>
                    <span class="badge bg-success py-2 px-3"><i class="fas fa-check-circle me-1"></i>API Enabled</span>
                </div>
                
                <div class="row">
                    <div class="col-lg-5">
                        <div class="card mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0"><i class="fas fa-list-ul me-2 text-primary"></i>Required Payload Fields</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Type</th>
                                            <th>Required</th>
                                        </tr>
                                    </thead>
                                    <tbody id="apiRequiredFields">
                                        <tr>
                                            <td><code>template_id</code></td>
                                            <td>string</td>
                                            <td><span class="badge bg-danger">Required</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>msisdn</code></td>
                                            <td>string | string[]</td>
                                            <td><span class="badge bg-danger">Required</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>sub_account_id</code></td>
                                            <td>string</td>
                                            <td><span class="badge bg-warning text-dark">Optional</span></td>
                                        </tr>
                                        <tr>
                                            <td><code>callback_url</code></td>
                                            <td>string</td>
                                            <td><span class="badge bg-warning text-dark">Optional</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Placeholder Schema</h6>
                            </div>
                            <div class="card-body">
                                <div id="apiPlaceholderSection">
                                    <div id="apiNoPlaceholders" class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>No placeholders in this template.
                                    </div>
                                    <div id="apiPlaceholdersList" class="d-none">
                                        <div class="d-flex flex-wrap gap-2 mb-3" id="apiPlaceholderChips"></div>
                                        <div class="alert alert-warning py-2 mb-0">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <strong>With placeholders:</strong> Only 1 MSISDN per request allowed.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0"><i class="fas fa-broadcast-tower me-2 text-primary"></i>Channel Requirements</h6>
                            </div>
                            <div class="card-body" id="apiChannelRequirements">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                    <span>SMS fallback automatically enabled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-terminal me-2 text-primary"></i>Code Examples</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyApiCode()">
                                    <i class="fas fa-copy me-1"></i>Copy
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs px-3 pt-2" id="apiCodeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="curl-tab" data-bs-toggle="tab" data-bs-target="#curl-pane" type="button" role="tab">cURL</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="python-tab" data-bs-toggle="tab" data-bs-target="#python-pane" type="button" role="tab">Python</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="nodejs-tab" data-bs-toggle="tab" data-bs-target="#nodejs-pane" type="button" role="tab">Node.js</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="php-tab" data-bs-toggle="tab" data-bs-target="#php-pane" type="button" role="tab">PHP</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="java-tab" data-bs-toggle="tab" data-bs-target="#java-pane" type="button" role="tab">Java</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="csharp-tab" data-bs-toggle="tab" data-bs-target="#csharp-pane" type="button" role="tab">C#</button>
                                    </li>
                                </ul>
                                <div class="tab-content" id="apiCodeTabContent">
                                    <div class="tab-pane fade show active" id="curl-pane" role="tabpanel">
                                        <pre class="bg-dark text-light p-3 mb-0 rounded-0" style="font-size: 0.8rem; max-height: 350px; overflow-y: auto;"><code id="apiCodeCurl"></code></pre>
                                    </div>
                                    <div class="tab-pane fade" id="python-pane" role="tabpanel">
                                        <pre class="bg-dark text-light p-3 mb-0 rounded-0" style="font-size: 0.8rem; max-height: 350px; overflow-y: auto;"><code id="apiCodePython"></code></pre>
                                    </div>
                                    <div class="tab-pane fade" id="nodejs-pane" role="tabpanel">
                                        <pre class="bg-dark text-light p-3 mb-0 rounded-0" style="font-size: 0.8rem; max-height: 350px; overflow-y: auto;"><code id="apiCodeNodejs"></code></pre>
                                    </div>
                                    <div class="tab-pane fade" id="php-pane" role="tabpanel">
                                        <pre class="bg-dark text-light p-3 mb-0 rounded-0" style="font-size: 0.8rem; max-height: 350px; overflow-y: auto;"><code id="apiCodePhp"></code></pre>
                                    </div>
                                    <div class="tab-pane fade" id="java-pane" role="tabpanel">
                                        <pre class="bg-dark text-light p-3 mb-0 rounded-0" style="font-size: 0.8rem; max-height: 350px; overflow-y: auto;"><code id="apiCodeJava"></code></pre>
                                    </div>
                                    <div class="tab-pane fade" id="csharp-pane" role="tabpanel">
                                        <pre class="bg-dark text-light p-3 mb-0 rounded-0" style="font-size: 0.8rem; max-height: 350px; overflow-y: auto;"><code id="apiCodeCsharp"></code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <h6 class="alert-heading mb-2"><i class="fas fa-key me-2"></i>Authentication</h6>
                            <p class="mb-2 small">All API requests require authentication using your API key in the header:</p>
                            <code class="bg-light text-dark px-2 py-1 rounded">Authorization: Bearer YOUR_API_KEY</code>
                            <hr class="my-2">
                            <p class="mb-0 small">
                                <i class="fas fa-external-link-alt me-1"></i>
                                <a href="#" class="alert-link">View full API documentation</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Version History Modal -->
<div class="modal fade" id="versionHistoryModal" tabindex="-1" aria-labelledby="versionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title mb-1" id="versionHistoryModalLabel">
                        <i class="fas fa-history me-2 text-muted"></i>Version History
                    </h5>
                    <p class="text-muted small mb-0">Template: <span id="vhTemplateName" class="fw-medium">Template Name</span> <span class="text-muted">(ID: <span id="vhTemplateId">00000000</span>)</span></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs px-3 pt-2 border-bottom-0" id="vhTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="versions-tab" data-bs-toggle="tab" data-bs-target="#versions-pane" type="button" role="tab">
                            <i class="fas fa-code-branch me-1"></i>Versions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-pane" type="button" role="tab">
                            <i class="fas fa-clipboard-list me-1"></i>Audit Log
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-3">
                    <div class="tab-pane fade show active" id="versions-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="versionsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">Version</th>
                                        <th style="width: 140px;">Edited By</th>
                                        <th style="width: 160px;">Edited At</th>
                                        <th>Change Summary</th>
                                        <th style="width: 100px;">Status</th>
                                        <th style="width: 140px; text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="versionsTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="audit-pane" role="tabpanel">
                        <div class="vh-audit-timeline" id="auditTimeline">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Version Modal (Read-only) -->
<div class="modal fade" id="viewVersionModal" tabindex="-1" aria-labelledby="viewVersionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h5 class="modal-title" id="viewVersionModalLabel">
                    <i class="fas fa-eye me-2"></i>View Version
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Read-only view</strong> - This is a historical snapshot of the template.
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small text-muted">Template Name</label>
                        <p class="mb-0 fw-medium" id="vvTemplateName">-</p>
                    </div>
                    <div class="col-3">
                        <label class="form-label small text-muted">Version</label>
                        <p class="mb-0"><span class="badge bg-primary" id="vvVersion">v1</span></p>
                    </div>
                    <div class="col-3">
                        <label class="form-label small text-muted">Status at Save</label>
                        <p class="mb-0"><span class="badge" id="vvStatus">Draft</span></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small text-muted">Channel</label>
                        <p class="mb-0"><span class="badge" id="vvChannel">SMS</span></p>
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">Trigger</label>
                        <p class="mb-0"><span class="badge" id="vvTrigger">Portal</span></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small text-muted">Change Note</label>
                    <p class="mb-0 text-muted fst-italic" id="vvChangeNote">No change note provided</p>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <label class="form-label small text-muted">Content Preview</label>
                    <div class="border rounded p-3 bg-light" id="vvContentPreview" style="min-height: 100px;">
                        <p class="text-muted mb-0">No content</p>
                    </div>
                </div>
                
                <div class="mb-3" id="vvPlaceholdersSection">
                    <label class="form-label small text-muted">Placeholders Used</label>
                    <div id="vvPlaceholders">
                        <span class="text-muted">None</span>
                    </div>
                </div>
                
                <div class="row text-muted small">
                    <div class="col-6">
                        <i class="fas fa-user me-1"></i>Edited by: <span id="vvEditedBy">-</span>
                    </div>
                    <div class="col-6 text-end">
                        <i class="fas fa-clock me-1"></i>Edited at: <span id="vvEditedAt">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-primary" id="vvRollbackBtn" onclick="rollbackFromViewVersion()">
                    <i class="fas fa-undo me-1"></i>Roll Back to This Version
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Rollback Confirmation Modal -->
<div class="modal fade" id="rollbackConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title"><i class="fas fa-undo me-2 text-warning"></i>Roll back to <span id="rbVersionLabel">v1</span>?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">This will create a new version (<strong id="rbNewVersionLabel">v4</strong>) using <span id="rbSourceVersionLabel">v2</span> content.</p>
                
                <div class="bg-light rounded p-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Source version:</span>
                        <span class="fw-medium" id="rbSourceVersion">v2</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">New version to create:</span>
                        <span class="fw-medium text-success" id="rbNewVersion">v4</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small text-muted">Change Note (optional)</label>
                    <input type="text" class="form-control" id="rbChangeNote" placeholder="e.g., Rolled back due to error in v3">
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="rbSetLive">
                    <label class="form-check-label" for="rbSetLive">
                        Set the new version as Live immediately
                    </label>
                </div>
                
                <div class="alert alert-info py-2 small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Rolling back creates a new version. No existing versions are deleted or modified.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmRollback()">
                    <i class="fas fa-undo me-1"></i>Confirm Roll Back
                </button>
            </div>
        </div>
    </div>
</div>

@include('quicksms.partials.rcs-wizard-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/rcs-preview-renderer.js') }}"></script>
<script src="{{ asset('js/rcs-wizard.js') }}?v=20260107t"></script>
<script>
var tplBasicRcsPreviewMode = 'rcs';
var tplRichRcsPreviewMode = 'rcs';
var templateRcsPayload = null;
var isTemplateWizardContext = true;

var mockTemplates = [
    {
        id: 1,
        templateId: '10483726',
        name: 'Welcome Message',
        channel: 'sms',
        trigger: 'portal',
        content: 'Hi {FirstName}, welcome to QuickSMS! Your account is now active. Reply HELP for support or STOP to opt out.',
        contentType: 'text',
        accessScope: 'All Sub-accounts',
        subAccounts: ['all'],
        status: 'live',
        version: 3,
        lastUpdated: '2026-01-05'
    },
    {
        id: 2,
        templateId: '20957341',
        name: 'Appointment Reminder',
        channel: 'basic_rcs',
        trigger: 'api',
        content: 'Reminder: Your appointment with {Company} is scheduled for tomorrow at {Time}. Reply YES to confirm.',
        contentType: 'text',
        accessScope: 'Marketing Team',
        subAccounts: ['marketing'],
        status: 'live',
        version: 2,
        lastUpdated: '2026-01-04'
    },
    {
        id: 3,
        templateId: '38472615',
        name: 'Product Showcase',
        channel: 'rich_rcs',
        trigger: 'portal',
        content: '',
        contentType: 'rich_card',
        accessScope: 'Sales, Support',
        subAccounts: ['sales', 'support'],
        status: 'draft',
        version: 1,
        lastUpdated: '2026-01-06'
    },
    {
        id: 4,
        templateId: '47291830',
        name: 'Holiday Promotions',
        channel: 'rich_rcs',
        trigger: 'api',
        content: '',
        contentType: 'carousel',
        accessScope: 'Marketing Team',
        subAccounts: ['marketing'],
        status: 'draft',
        version: 4,
        lastUpdated: '2025-12-20'
    },
    {
        id: 5,
        templateId: '56384029',
        name: 'Order Confirmation',
        channel: 'sms',
        trigger: 'email',
        content: 'Order #{OrderID} confirmed! Your items will ship within 2 business days. Track at: {TrackingURL}',
        contentType: 'text',
        accessScope: 'All Sub-accounts',
        subAccounts: ['all'],
        status: 'live',
        version: 1,
        lastUpdated: '2026-01-03'
    },
    {
        id: 6,
        templateId: '69102847',
        name: 'Password Reset',
        channel: 'sms',
        trigger: 'api',
        content: 'Your verification code is {Code}. This code expires in 10 minutes. Do not share this code.',
        contentType: 'text',
        accessScope: 'IT Security',
        subAccounts: ['it'],
        status: 'archived',
        version: 5,
        lastUpdated: '2025-11-15'
    },
    {
        id: 7,
        templateId: '71829364',
        name: 'Flash Sale Alert',
        channel: 'basic_rcs',
        trigger: 'portal',
        content: 'Flash Sale! 50% off all items for the next 24 hours. Shop now at {ShopURL}. Limited stock available!',
        contentType: 'text',
        accessScope: 'Marketing Team',
        subAccounts: ['marketing'],
        status: 'draft',
        version: 1,
        lastUpdated: '2026-01-07'
    },
    {
        id: 8,
        templateId: '82946150',
        name: 'Customer Feedback',
        channel: 'rich_rcs',
        trigger: 'email',
        content: '',
        contentType: 'rich_card',
        accessScope: 'Support Team',
        subAccounts: ['support'],
        status: 'live',
        version: 2,
        lastUpdated: '2026-01-02'
    }
];

var mockVersionHistory = {
    1: [
        { version: 3, status: 'live', content: 'Hi {FirstName}, welcome to QuickSMS! Your account is now active. Reply HELP for support or STOP to opt out.', channel: 'sms', trigger: 'portal', changeNote: 'Added opt-out instructions', editedBy: 'John Smith', editedAt: '2026-01-05 14:32:00', userId: 'user1' },
        { version: 2, status: 'archived', content: 'Hi {FirstName}, welcome to QuickSMS! Your account is now active.', channel: 'sms', trigger: 'portal', changeNote: 'Personalized greeting', editedBy: 'Sarah Jones', editedAt: '2026-01-03 10:15:00', userId: 'user2' },
        { version: 1, status: 'archived', content: 'Welcome to QuickSMS! Your account is now active.', channel: 'sms', trigger: 'portal', changeNote: 'Initial version', editedBy: 'John Smith', editedAt: '2026-01-01 09:00:00', userId: 'user1' }
    ],
    2: [
        { version: 2, status: 'live', content: 'Reminder: Your appointment with {Company} is scheduled for tomorrow at {Time}. Reply YES to confirm.', channel: 'basic_rcs', trigger: 'api', changeNote: 'Added confirmation prompt', editedBy: 'Mike Wilson', editedAt: '2026-01-04 16:45:00', userId: 'user3' },
        { version: 1, status: 'archived', content: 'Reminder: Your appointment with {Company} is scheduled for tomorrow at {Time}.', channel: 'basic_rcs', trigger: 'api', changeNote: 'Initial version', editedBy: 'Mike Wilson', editedAt: '2026-01-02 11:30:00', userId: 'user3' }
    ],
    4: [
        { version: 4, status: 'draft', content: '', channel: 'rich_rcs', trigger: 'api', changeNote: 'Updated carousel images for winter sale', editedBy: 'Lisa Chen', editedAt: '2025-12-20 09:15:00', userId: 'user6' },
        { version: 3, status: 'archived', content: '', channel: 'rich_rcs', trigger: 'api', changeNote: 'Added third carousel card', editedBy: 'Lisa Chen', editedAt: '2025-12-15 14:30:00', userId: 'user6' },
        { version: 2, status: 'archived', content: '', channel: 'rich_rcs', trigger: 'api', changeNote: 'Changed to carousel format', editedBy: 'Mike Wilson', editedAt: '2025-12-10 11:00:00', userId: 'user3' },
        { version: 1, status: 'archived', content: '', channel: 'rich_rcs', trigger: 'api', changeNote: 'Initial rich card version', editedBy: 'John Smith', editedAt: '2025-12-01 10:00:00', userId: 'user1' }
    ],
    6: [
        { version: 5, status: 'archived', content: 'Your verification code is {Code}. This code expires in 10 minutes. Do not share this code.', channel: 'sms', trigger: 'api', changeNote: 'Archived - replaced by new auth system', editedBy: 'Emily Brown', editedAt: '2025-11-15 08:00:00', userId: 'user4' },
        { version: 4, status: 'archived', content: 'Your verification code is {Code}. This code expires in 10 minutes. Do not share this code.', channel: 'sms', trigger: 'api', changeNote: 'Added security warning', editedBy: 'Emily Brown', editedAt: '2025-10-20 14:20:00', userId: 'user4' },
        { version: 3, status: 'archived', content: 'Your verification code is {Code}. Expires in 10 minutes.', channel: 'sms', trigger: 'api', changeNote: 'Shortened expiry text', editedBy: 'David Lee', editedAt: '2025-09-15 10:00:00', userId: 'user5' },
        { version: 2, status: 'archived', content: 'Your verification code is {Code}. This code will expire in 10 minutes.', channel: 'sms', trigger: 'api', changeNote: 'Added expiry info', editedBy: 'Emily Brown', editedAt: '2025-08-01 09:30:00', userId: 'user4' },
        { version: 1, status: 'archived', content: 'Your verification code is {Code}.', channel: 'sms', trigger: 'api', changeNote: 'Initial version', editedBy: 'John Smith', editedAt: '2025-07-15 15:00:00', userId: 'user1' }
    ],
    8: [
        { version: 2, status: 'live', content: '', channel: 'rich_rcs', trigger: 'email', changeNote: 'Updated feedback survey link', editedBy: 'Sarah Jones', editedAt: '2026-01-02 15:45:00', userId: 'user2' },
        { version: 1, status: 'archived', content: '', channel: 'rich_rcs', trigger: 'email', changeNote: 'Initial customer feedback template', editedBy: 'Sarah Jones', editedAt: '2025-12-28 10:30:00', userId: 'user2' }
    ]
};

var mockAuditLog = {
    1: [
        { action: 'launched', version: 3, userId: 'user1', userName: 'John Smith', timestamp: '2026-01-05 14:35:00', details: 'Published version 3 as Live' },
        { action: 'edited', version: 3, userId: 'user1', userName: 'John Smith', timestamp: '2026-01-05 14:32:00', details: 'Added opt-out instructions' },
        { action: 'edited', version: 2, userId: 'user2', userName: 'Sarah Jones', timestamp: '2026-01-03 10:15:00', details: 'Personalized greeting' },
        { action: 'created', version: 1, userId: 'user1', userName: 'John Smith', timestamp: '2026-01-01 09:00:00', details: 'Template created' }
    ],
    2: [
        { action: 'launched', version: 2, userId: 'user3', userName: 'Mike Wilson', timestamp: '2026-01-04 16:50:00', details: 'Published version 2 as Live' },
        { action: 'edited', version: 2, userId: 'user3', userName: 'Mike Wilson', timestamp: '2026-01-04 16:45:00', details: 'Added confirmation prompt' },
        { action: 'created', version: 1, userId: 'user3', userName: 'Mike Wilson', timestamp: '2026-01-02 11:30:00', details: 'Template created' }
    ],
    6: [
        { action: 'archived', version: 5, userId: 'user4', userName: 'Emily Brown', timestamp: '2025-11-15 08:00:00', details: 'Template archived - replaced by new auth system' },
        { action: 'permissions', version: 4, userId: 'user4', userName: 'Emily Brown', timestamp: '2025-10-25 11:00:00', details: 'Restricted access to IT Security only' },
        { action: 'launched', version: 4, userId: 'user4', userName: 'Emily Brown', timestamp: '2025-10-20 14:25:00', details: 'Published version 4 as Live' },
        { action: 'edited', version: 4, userId: 'user4', userName: 'Emily Brown', timestamp: '2025-10-20 14:20:00', details: 'Added security warning' },
        { action: 'edited', version: 3, userId: 'user5', userName: 'David Lee', timestamp: '2025-09-15 10:00:00', details: 'Shortened expiry text' },
        { action: 'launched', version: 3, userId: 'user5', userName: 'David Lee', timestamp: '2025-09-15 10:05:00', details: 'Published version 3 as Live' },
        { action: 'edited', version: 2, userId: 'user4', userName: 'Emily Brown', timestamp: '2025-08-01 09:30:00', details: 'Added expiry info' },
        { action: 'launched', version: 2, userId: 'user4', userName: 'Emily Brown', timestamp: '2025-08-01 09:35:00', details: 'Published version 2 as Live' },
        { action: 'created', version: 1, userId: 'user1', userName: 'John Smith', timestamp: '2025-07-15 15:00:00', details: 'Template created' }
    ]
};

var currentVersionHistory = {
    templateId: null,
    versions: [],
    auditLog: []
};

var currentViewVersion = {
    templateId: null,
    version: null,
    data: null
};

var rollbackTarget = {
    templateId: null,
    version: null,
    data: null
};

var showArchived = false;

var sortColumn = 'lastUpdated';
var sortDirection = 'desc';

var appliedFilters = {
    search: '',
    channel: '',
    trigger: '',
    status: '',
    subAccounts: []
};

var pendingFilters = {
    channel: '',
    trigger: '',
    status: '',
    subAccounts: []
};

var subAccountLabels = {
    'marketing': 'Marketing Team',
    'sales': 'Sales',
    'support': 'Support Team',
    'it': 'IT Security',
    'all': 'All Sub-accounts'
};

document.addEventListener('DOMContentLoaded', function() {
    renderTemplates();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('createTemplateBtn').addEventListener('click', showCreateModal);
    document.getElementById('templateContent').addEventListener('input', updateCharCount);
    
    document.getElementById('templateSearch').addEventListener('input', function() {
        appliedFilters.search = this.value;
        renderTemplates();
        renderActiveFilters();
    });
    
    document.getElementById('showArchivedToggle').addEventListener('change', function() {
        showArchived = this.checked;
        renderTemplates();
    });
    
    document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
    document.getElementById('resetFiltersBtn').addEventListener('click', resetFilters);
    
    document.querySelectorAll('.subaccount-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', updateSubAccountDropdownLabel);
    });
}

function updateSubAccountDropdownLabel() {
    var checked = document.querySelectorAll('.subaccount-check:checked');
    var label = document.querySelector('#subAccountDropdown .dropdown-label');
    
    if (checked.length === 0) {
        label.textContent = 'All Sub-accounts';
    } else if (checked.length === 1) {
        label.textContent = subAccountLabels[checked[0].value] || checked[0].value;
    } else {
        label.textContent = checked.length + ' selected';
    }
}

function applyFilters() {
    appliedFilters.channel = document.getElementById('channelFilter').value;
    appliedFilters.trigger = document.getElementById('triggerFilter').value;
    appliedFilters.status = document.getElementById('statusFilter').value;
    
    var checkedSubAccounts = [];
    document.querySelectorAll('.subaccount-check:checked').forEach(function(cb) {
        checkedSubAccounts.push(cb.value);
    });
    appliedFilters.subAccounts = checkedSubAccounts;
    
    renderTemplates();
    renderActiveFilters();
}

function resetFilters() {
    document.getElementById('channelFilter').value = '';
    document.getElementById('triggerFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.querySelectorAll('.subaccount-check').forEach(function(cb) {
        cb.checked = false;
    });
    updateSubAccountDropdownLabel();
    
    appliedFilters.channel = '';
    appliedFilters.trigger = '';
    appliedFilters.status = '';
    appliedFilters.subAccounts = [];
    
    renderTemplates();
    renderActiveFilters();
}

function removeFilter(filterType) {
    if (filterType === 'search') {
        document.getElementById('templateSearch').value = '';
        appliedFilters.search = '';
    } else if (filterType === 'channel') {
        document.getElementById('channelFilter').value = '';
        appliedFilters.channel = '';
    } else if (filterType === 'trigger') {
        document.getElementById('triggerFilter').value = '';
        appliedFilters.trigger = '';
    } else if (filterType === 'status') {
        document.getElementById('statusFilter').value = '';
        appliedFilters.status = '';
    } else if (filterType === 'subAccounts') {
        document.querySelectorAll('.subaccount-check').forEach(function(cb) {
            cb.checked = false;
        });
        updateSubAccountDropdownLabel();
        appliedFilters.subAccounts = [];
    }
    
    renderTemplates();
    renderActiveFilters();
}

function renderActiveFilters() {
    var container = document.getElementById('activeFilters');
    var html = '';
    
    if (appliedFilters.search) {
        html += createChip('Search', appliedFilters.search, 'search');
    }
    
    if (appliedFilters.channel) {
        html += createChip('Channel', getChannelLabel(appliedFilters.channel), 'channel');
    }
    
    if (appliedFilters.trigger) {
        html += createChip('Trigger', getTriggerLabel(appliedFilters.trigger), 'trigger');
    }
    
    if (appliedFilters.status) {
        html += createChip('Status', getStatusLabel(appliedFilters.status), 'status');
    }
    
    if (appliedFilters.subAccounts.length > 0) {
        var labels = appliedFilters.subAccounts.map(function(v) {
            return subAccountLabels[v] || v;
        });
        html += createChip('Sub-account', labels.join(', '), 'subAccounts');
    }
    
    container.innerHTML = html;
}

function createChip(label, value, filterType) {
    return '<span class="filter-chip">' +
        '<span class="chip-label">' + label + ':</span>' +
        '<span class="chip-value">' + value + '</span>' +
        '<i class="fas fa-times remove-chip" onclick="removeFilter(\'' + filterType + '\')"></i>' +
        '</span>';
}

var currentWizardStep = 1;
var wizardData = {
    name: '',
    templateId: '',
    trigger: '',
    channel: 'sms',
    content: '',
    accessMode: 'restricted',
    subAccounts: [],
    roles: [],
    users: []
};

function generateTemplateId() {
    return Math.floor(10000000 + Math.random() * 90000000).toString();
}

function showCreateModal() {
    currentWizardStep = 1;
    wizardData = {
        name: '',
        templateId: generateTemplateId(),
        trigger: '',
        channel: 'sms',
        content: '',
        accessMode: 'restricted',
        subAccounts: [],
        roles: [],
        users: []
    };
    
    document.querySelectorAll('.wizard-subaccount-check').forEach(function(cb) {
        cb.checked = false;
        cb.disabled = false;
    });
    document.querySelectorAll('.wizard-role-check').forEach(function(cb) {
        cb.checked = false;
    });
    document.querySelectorAll('.wizard-user-check').forEach(function(cb) {
        cb.checked = false;
    });
    document.getElementById('wizardAccessRestricted').checked = true;
    document.getElementById('wizardRestrictedSection').style.display = 'block';
    updateWizardPermissionCounts();
    
    document.getElementById('templateName').value = '';
    document.getElementById('templateIdField').value = wizardData.templateId;
    document.getElementById('templateContent').value = '';
    document.getElementById('tplCharCount').textContent = '0';
    document.getElementById('tplChannelSMS').checked = true;
    
    document.getElementById('tplRcsContentSection').classList.add('d-none');
    document.getElementById('tplTextEditorContainer').classList.remove('d-none');
    
    document.querySelectorAll('input[name="templateTrigger"]').forEach(function(radio) {
        radio.checked = false;
    });
    document.querySelectorAll('.trigger-option').forEach(function(opt) {
        opt.classList.remove('selected');
    });
    
    document.getElementById('templateName').classList.remove('is-invalid');
    document.getElementById('triggerError').style.display = 'none';
    
    updateWizardUI();
    new bootstrap.Modal(document.getElementById('createTemplateModal')).show();
    
    setupTriggerOptionListeners();
}

function setupTriggerOptionListeners() {
    document.querySelectorAll('.trigger-option').forEach(function(option) {
        option.addEventListener('click', function() {
            var trigger = this.getAttribute('data-trigger');
            var radio = this.querySelector('input[type="radio"]');
            
            document.querySelectorAll('.trigger-option').forEach(function(opt) {
                opt.classList.remove('selected');
            });
            document.querySelectorAll('input[name="templateTrigger"]').forEach(function(r) {
                r.checked = false;
            });
            
            this.classList.add('selected');
            radio.checked = true;
            wizardData.trigger = trigger;
            
            document.getElementById('triggerError').style.display = 'none';
        });
    });
}

function updateWizardUI() {
    document.querySelectorAll('.wizard-step').forEach(function(step) {
        var stepNum = parseInt(step.getAttribute('data-step'));
        step.classList.remove('active', 'completed');
        
        if (stepNum < currentWizardStep) {
            step.classList.add('completed');
        } else if (stepNum === currentWizardStep) {
            step.classList.add('active');
        }
    });
    
    document.getElementById('wizardStep1').style.display = currentWizardStep === 1 ? 'block' : 'none';
    document.getElementById('wizardStep2').style.display = currentWizardStep === 2 ? 'block' : 'none';
    document.getElementById('wizardStep3').style.display = currentWizardStep === 3 ? 'block' : 'none';
    document.getElementById('wizardStep4').style.display = currentWizardStep === 4 ? 'block' : 'none';
    
    document.getElementById('wizardBackBtn').style.display = currentWizardStep > 1 ? 'inline-block' : 'none';
    document.getElementById('wizardNextBtn').style.display = currentWizardStep < 4 ? 'inline-block' : 'none';
    document.getElementById('wizardSaveDraftBtn').style.display = currentWizardStep === 4 ? 'inline-block' : 'none';
    document.getElementById('wizardLaunchBtn').style.display = currentWizardStep === 4 ? 'inline-block' : 'none';
    
    if (currentWizardStep === 2) {
        document.getElementById('step2TemplateName').textContent = wizardData.name;
        document.getElementById('step2TemplateId').textContent = wizardData.templateId;
        
        var triggerBadge = document.getElementById('step2TriggerBadge');
        triggerBadge.textContent = getTriggerLabel(wizardData.trigger);
        triggerBadge.className = 'badge rounded-pill ' + getTriggerBadgeClass(wizardData.trigger);
        
        setTimeout(function() {
            updateTemplatePreview();
        }, 100);
    }
    
    if (currentWizardStep === 3) {
        document.getElementById('step3TemplateName').textContent = wizardData.name;
        var step3TriggerBadge = document.getElementById('step3TriggerBadge');
        step3TriggerBadge.textContent = getTriggerLabel(wizardData.trigger);
        step3TriggerBadge.className = 'badge rounded-pill ' + getTriggerBadgeClass(wizardData.trigger);
        
        var apiWarning = document.getElementById('wizardPermApiWarning');
        if (wizardData.trigger === 'api') {
            apiWarning.classList.remove('d-none');
        } else {
            apiWarning.classList.add('d-none');
        }
    }
    
    if (currentWizardStep === 4) {
        populateReviewStep();
    }
}

function validateStep1() {
    var isValid = true;
    
    var nameInput = document.getElementById('templateName');
    var name = nameInput.value.trim();
    if (!name) {
        nameInput.classList.add('is-invalid');
        isValid = false;
    } else {
        nameInput.classList.remove('is-invalid');
        wizardData.name = name;
    }
    
    var selectedTrigger = document.querySelector('input[name="templateTrigger"]:checked');
    if (!selectedTrigger) {
        document.getElementById('triggerError').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('triggerError').style.display = 'none';
        wizardData.trigger = selectedTrigger.value;
    }
    
    return isValid;
}

function wizardNext() {
    if (currentWizardStep === 1) {
        if (!validateStep1()) {
            return;
        }
    }
    
    if (currentWizardStep === 3) {
        if (!validateStep3()) {
            return;
        }
    }
    
    if (currentWizardStep < 4) {
        currentWizardStep++;
        updateWizardUI();
    }
}

function validateStep3() {
    var accessMode = document.querySelector('input[name="wizardAccessMode"]:checked').value;
    wizardData.accessMode = accessMode;
    
    if (accessMode === 'all') {
        wizardData.subAccounts = ['all'];
        wizardData.roles = [];
        wizardData.users = [];
        return true;
    }
    
    var subAccounts = [];
    document.querySelectorAll('.wizard-subaccount-check:checked').forEach(function(cb) {
        subAccounts.push(cb.value);
    });
    
    var roles = [];
    document.querySelectorAll('.wizard-role-check:checked').forEach(function(cb) {
        roles.push(cb.value);
    });
    
    var users = [];
    document.querySelectorAll('.wizard-user-check:checked').forEach(function(cb) {
        users.push(cb.value);
    });
    
    wizardData.subAccounts = subAccounts;
    wizardData.roles = roles;
    wizardData.users = users;
    
    if (wizardData.trigger === 'api' && subAccounts.length === 0) {
        showToast('API templates must be assigned to at least one sub-account', 'error');
        return false;
    }
    
    if (subAccounts.length === 0 && roles.length === 0 && users.length === 0) {
        showToast('Please select at least one sub-account, role, or user', 'warning');
        return false;
    }
    
    return true;
}

function updateWizardAccessMode() {
    var accessMode = document.querySelector('input[name="wizardAccessMode"]:checked').value;
    var restrictedSection = document.getElementById('wizardRestrictedSection');
    
    if (accessMode === 'all') {
        restrictedSection.style.display = 'none';
    } else {
        restrictedSection.style.display = 'block';
    }
}

function toggleWizardSubAccount(value) {
    if (value === 'all') {
        var allChecked = document.getElementById('wizardSubAll').checked;
        document.querySelectorAll('.wizard-subaccount-check').forEach(function(cb) {
            if (cb.value !== 'all') {
                cb.checked = false;
                cb.disabled = allChecked;
            }
        });
    } else {
        document.getElementById('wizardSubAll').checked = false;
    }
    updateWizardPermissionCounts();
}

function toggleWizardRole(value) {
    updateWizardPermissionCounts();
}

function toggleWizardUser(value) {
    updateWizardPermissionCounts();
}

function updateWizardPermissionCounts() {
    var subCount = document.querySelectorAll('.wizard-subaccount-check:checked').length;
    var roleCount = document.querySelectorAll('.wizard-role-check:checked').length;
    var userCount = document.querySelectorAll('.wizard-user-check:checked').length;
    
    var subCountEl = document.getElementById('wizardSubAccountCount');
    var roleCountEl = document.getElementById('wizardRoleCount');
    var userCountEl = document.getElementById('wizardUserCount');
    
    subCountEl.textContent = subCount;
    subCountEl.className = 'placeholder-counter ms-2' + (subCount === 0 ? ' empty' : '');
    
    roleCountEl.textContent = roleCount;
    roleCountEl.className = 'placeholder-counter ms-2' + (roleCount === 0 ? ' empty' : '');
    
    userCountEl.textContent = userCount;
    userCountEl.className = 'placeholder-counter ms-2' + (userCount === 0 ? ' empty' : '');
}

function filterWizardUsers() {
    var search = document.getElementById('wizardUserSearch').value.toLowerCase();
    document.querySelectorAll('.wizard-user-item').forEach(function(item) {
        var name = item.getAttribute('data-name') || '';
        item.style.display = name.includes(search) ? 'block' : 'none';
    });
}

function populateReviewStep() {
    document.getElementById('reviewTemplateName').textContent = wizardData.name;
    document.getElementById('reviewTemplateId').textContent = wizardData.templateId;
    document.getElementById('reviewChannel').textContent = getChannelLabel(wizardData.channel);
    document.getElementById('reviewTrigger').innerHTML = getTriggerIcon(wizardData.trigger) + ' ' + getTriggerLabel(wizardData.trigger);
    
    var content = document.getElementById('templateContent').value;
    if (wizardData.channel === 'rich_rcs' && templateRcsPayload) {
        document.getElementById('reviewContent').innerHTML = '<span class="text-muted fst-italic">Rich RCS content configured</span>';
    } else {
        document.getElementById('reviewContent').textContent = content || '-';
    }
    
    if (wizardData.accessMode === 'all') {
        document.getElementById('reviewAccessMode').innerHTML = '<i class="fas fa-globe me-1 text-success"></i>All Users';
        document.getElementById('reviewPermissions').textContent = 'Everyone in your organization';
    } else {
        document.getElementById('reviewAccessMode').innerHTML = '<i class="fas fa-lock me-1 text-warning"></i>Restricted';
        var permParts = [];
        if (wizardData.subAccounts.length > 0) {
            permParts.push(wizardData.subAccounts.length + ' sub-account(s)');
        }
        if (wizardData.roles.length > 0) {
            permParts.push(wizardData.roles.length + ' role(s)');
        }
        if (wizardData.users.length > 0) {
            permParts.push(wizardData.users.length + ' user(s)');
        }
        document.getElementById('reviewPermissions').textContent = permParts.join(', ') || 'None selected';
    }
}

function wizardBack() {
    if (currentWizardStep > 1) {
        currentWizardStep--;
        updateWizardUI();
    }
}

function updateCharCount() {
    var content = document.getElementById('templateContent').value;
    document.getElementById('charCount').textContent = content.length;
}

function saveTemplateAsDraft() {
    var name = wizardData.name || document.getElementById('templateName').value.trim();
    var content = document.getElementById('templateContent').value.trim();
    var channel = document.querySelector('input[name="templateChannel"]:checked').value;
    var trigger = wizardData.trigger || 'portal';
    
    if (!name) {
        showToast('Please enter a template name.', 'error');
        return;
    }
    
    var accessScope = wizardData.accessMode === 'all' ? 'All Sub-accounts' : getAccessScopeLabel();
    
    var template = {
        id: Date.now(),
        templateId: wizardData.templateId,
        name: name,
        channel: channel,
        trigger: trigger,
        content: content,
        contentType: channel === 'rich_rcs' ? 'rich_card' : 'text',
        accessScope: accessScope,
        subAccounts: wizardData.subAccounts,
        roles: wizardData.roles,
        users: wizardData.users,
        status: 'draft',
        version: 1,
        lastUpdated: new Date().toISOString().split('T')[0]
    };
    
    if (templateRcsPayload) {
        template.rcsPayload = templateRcsPayload;
    }
    
    mockTemplates.unshift(template);
    bootstrap.Modal.getInstance(document.getElementById('createTemplateModal')).hide();
    renderTemplates();
    showToast('Template "' + name + '" saved as Draft (v1)', 'success');
}

function getAccessScopeLabel() {
    var parts = [];
    if (wizardData.subAccounts.length > 0 && wizardData.subAccounts[0] !== 'all') {
        parts.push(wizardData.subAccounts.join(', '));
    }
    if (wizardData.roles.length > 0) {
        parts.push(wizardData.roles.join(', '));
    }
    return parts.length > 0 ? parts.join(', ') : 'Restricted';
}

function confirmLaunchTemplate() {
    var name = wizardData.name || document.getElementById('templateName').value.trim();
    
    if (!name) {
        showToast('Please enter a template name.', 'error');
        return;
    }
    
    var existingLive = mockTemplates.find(function(t) {
        return t.templateId === wizardData.templateId && t.status === 'live';
    });
    
    var modalHtml = '<div class="modal fade" id="launchConfirmModal" tabindex="-1" data-bs-backdrop="static" style="z-index: 1060;">' +
        '<div class="modal-dialog modal-dialog-centered">' +
            '<div class="modal-content">' +
                '<div class="modal-header border-0 pb-0">' +
                    '<h5 class="modal-title"><i class="fas fa-rocket me-2 text-success"></i>Launch Template</h5>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<p class="mb-3">You are about to launch <strong>"' + name + '"</strong> as a Live template.</p>' +
                    (existingLive ? 
                        '<div class="alert alert-info py-2 mb-3">' +
                            '<i class="fas fa-info-circle me-2"></i>' +
                            'A Live version (v' + existingLive.version + ') already exists. Launching will create a new version and set it as Live. The previous version will be archived.' +
                        '</div>' : '') +
                    '<div class="bg-light rounded p-3">' +
                        '<div class="d-flex justify-content-between mb-2">' +
                            '<span class="text-muted">Template ID:</span>' +
                            '<span class="fw-medium">' + wizardData.templateId + '</span>' +
                        '</div>' +
                        '<div class="d-flex justify-content-between mb-2">' +
                            '<span class="text-muted">Channel:</span>' +
                            '<span class="fw-medium">' + getChannelLabel(wizardData.channel) + '</span>' +
                        '</div>' +
                        '<div class="d-flex justify-content-between">' +
                            '<span class="text-muted">New Version:</span>' +
                            '<span class="fw-medium text-success">v' + (existingLive ? existingLive.version + 1 : 1) + ' (Live)</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer border-0 pt-0">' +
                    '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn btn-success" onclick="launchTemplate()">' +
                        '<i class="fas fa-rocket me-2"></i>Confirm Launch' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('launchConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    new bootstrap.Modal(document.getElementById('launchConfirmModal')).show();
}

function launchTemplate() {
    var name = wizardData.name || document.getElementById('templateName').value.trim();
    var content = document.getElementById('templateContent').value.trim();
    var channel = document.querySelector('input[name="templateChannel"]:checked').value;
    var trigger = wizardData.trigger || 'portal';
    
    var accessScope = wizardData.accessMode === 'all' ? 'All Sub-accounts' : getAccessScopeLabel();
    
    var existingLiveIndex = mockTemplates.findIndex(function(t) {
        return t.templateId === wizardData.templateId && t.status === 'live';
    });
    
    var newVersion = 1;
    if (existingLiveIndex !== -1) {
        newVersion = mockTemplates[existingLiveIndex].version + 1;
        mockTemplates[existingLiveIndex].status = 'archived';
    }
    
    var template = {
        id: Date.now(),
        templateId: wizardData.templateId,
        name: name,
        channel: channel,
        trigger: trigger,
        content: content,
        contentType: channel === 'rich_rcs' ? 'rich_card' : 'text',
        accessScope: accessScope,
        subAccounts: wizardData.subAccounts,
        roles: wizardData.roles,
        users: wizardData.users,
        status: 'live',
        version: newVersion,
        lastUpdated: new Date().toISOString().split('T')[0]
    };
    
    if (templateRcsPayload) {
        template.rcsPayload = templateRcsPayload;
    }
    
    mockTemplates.unshift(template);
    
    bootstrap.Modal.getInstance(document.getElementById('launchConfirmModal')).hide();
    document.getElementById('launchConfirmModal').remove();
    bootstrap.Modal.getInstance(document.getElementById('createTemplateModal')).hide();
    
    renderTemplates();
    showToast('Template "' + name + '" launched as Live (v' + newVersion + ')', 'success');
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
    
    renderTemplates();
}

function getChannelLabel(channel) {
    switch(channel) {
        case 'sms': return 'SMS';
        case 'basic_rcs': return 'Basic RCS + SMS';
        case 'rich_rcs': return 'Rich RCS + SMS';
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

function getTriggerBadgeClass(trigger) {
    switch(trigger) {
        case 'api': return 'badge-api';
        case 'portal': return 'badge-portal';
        case 'email': return 'badge-email';
        default: return 'badge-api';
    }
}

function getTriggerTextClass(trigger) {
    switch(trigger) {
        case 'api': return 'trigger-api';
        case 'portal': return 'trigger-portal';
        case 'email': return 'trigger-email';
        default: return 'trigger-api';
    }
}

function getTriggerIcon(trigger) {
    return '';
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

function getContentPreview(template) {
    if (template.contentType === 'rich_card') {
        return '<span class="badge rounded-pill badge-rich-card">Rich Card</span>';
    } else if (template.contentType === 'carousel') {
        return '<span class="badge rounded-pill badge-carousel">Carousel</span>';
    } else {
        var preview = template.content.length > 100 ? template.content.substring(0, 100) + '...' : template.content;
        return '<span class="content-preview" title="' + template.content.replace(/"/g, '&quot;') + '">' + preview + '</span>';
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

function renderTemplates() {
    var search = appliedFilters.search.toLowerCase();
    
    var filtered = mockTemplates.filter(function(t) {
        if (!showArchived && t.status === 'archived') {
            return false;
        }
        
        var matchSearch = !search || t.name.toLowerCase().includes(search) || t.templateId.includes(search);
        var matchChannel = !appliedFilters.channel || t.channel === appliedFilters.channel;
        var matchTrigger = !appliedFilters.trigger || t.trigger === appliedFilters.trigger;
        var matchStatus = !appliedFilters.status || t.status === appliedFilters.status;
        
        var matchSubAccount = appliedFilters.subAccounts.length === 0 || 
            appliedFilters.subAccounts.some(function(sa) {
                return t.subAccounts.includes(sa) || t.subAccounts.includes('all');
            });
        
        return matchSearch && matchChannel && matchTrigger && matchStatus && matchSubAccount;
    });
    
    filtered.sort(function(a, b) {
        var aVal = a[sortColumn] || '';
        var bVal = b[sortColumn] || '';
        
        if (sortColumn === 'lastUpdated') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
        } else if (sortColumn === 'version') {
            aVal = parseInt(aVal) || 0;
            bVal = parseInt(bVal) || 0;
        }
        
        if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
        if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });
    
    if (mockTemplates.length === 0) {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('templatesTableContainer').style.display = 'none';
        return;
    }
    
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('templatesTableContainer').style.display = 'block';
    
    var tbody = document.getElementById('templatesBody');
    var html = '';
    
    filtered.forEach(function(template) {
        var isArchived = template.status === 'archived';
        var rowClass = isArchived ? 'archived-row' : '';
        
        html += '<tr class="' + rowClass + '">';
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
        html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
        html += '<i class="fas fa-ellipsis-v"></i>';
        html += '</button>';
        html += '<ul class="dropdown-menu dropdown-menu-end">';
        
        if (!isArchived) {
            html += '<li><a class="dropdown-item" href="#" onclick="editTemplate(' + template.id + '); return false;"><i class="fas fa-edit me-2"></i>Edit</a></li>';
        }
        html += '<li><a class="dropdown-item" href="#" onclick="duplicateTemplate(' + template.id + '); return false;"><i class="fas fa-copy me-2"></i>Duplicate</a></li>';
        html += '<li><a class="dropdown-item" href="#" onclick="viewVersionHistory(' + template.id + '); return false;"><i class="fas fa-history me-2"></i>Version History</a></li>';
        if (!isArchived) {
            html += '<li><a class="dropdown-item" href="#" onclick="managePermissions(' + template.id + '); return false;"><i class="fas fa-lock me-2"></i>Permissions</a></li>';
        }
        if (template.trigger === 'api') {
            html += '<li><a class="dropdown-item" href="#" onclick="viewApiStructure(' + template.id + '); return false;"><i class="fas fa-code me-2"></i>API Structure</a></li>';
        }
        if (!isArchived) {
            html += '<li><hr class="dropdown-divider"></li>';
            html += '<li><a class="dropdown-item text-warning" href="#" onclick="archiveTemplate(' + template.id + '); return false;"><i class="fas fa-archive me-2"></i>Archive</a></li>';
        }
        
        html += '</ul>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html || '<tr><td colspan="10" class="text-center text-muted py-4">No templates match your filters</td></tr>';
}

function editTemplate(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template || template.status === 'archived') {
        showToast('Archived templates cannot be edited', 'warning');
        return;
    }
    showToast('Opening editor for "' + template.name + '"...', 'info');
}

function duplicateTemplate(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    
    var newId = mockTemplates.length + 1;
    var newTemplateId = Math.floor(10000000 + Math.random() * 90000000).toString();
    
    var duplicate = Object.assign({}, template, {
        id: newId,
        templateId: newTemplateId,
        name: template.name + ' (Copy)',
        status: 'draft',
        version: 1,
        lastUpdated: new Date().toISOString().split('T')[0]
    });
    
    mockTemplates.push(duplicate);
    renderTemplates();
    showToast('Template duplicated as "' + duplicate.name + '"', 'success');
}

var currentPermissions = {
    templateId: null,
    templateName: '',
    accessMode: 'restricted',
    subAccounts: [],
    roles: [],
    users: [],
    trigger: ''
};

var subAccountLabels = {
    'all': 'All Sub-accounts',
    'main': 'Main Account',
    'marketing': 'Marketing Team',
    'sales': 'Sales Department',
    'support': 'Customer Support'
};

var roleLabels = {
    'admin': 'Administrator',
    'manager': 'Manager',
    'messaging': 'Messaging User',
    'viewer': 'Viewer',
    'api': 'API User'
};

var userLabels = {
    'user1': 'John Smith',
    'user2': 'Sarah Jones',
    'user3': 'Mike Wilson',
    'user4': 'Emily Brown',
    'user5': 'David Lee'
};

function managePermissions(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    
    currentPermissions = {
        templateId: template.id,
        templateName: template.name,
        accessMode: template.subAccounts.includes('all') ? 'all' : 'restricted',
        subAccounts: template.permissions ? template.permissions.subAccounts || [] : (template.subAccounts || []),
        roles: template.permissions ? template.permissions.roles || [] : [],
        users: template.permissions ? template.permissions.users || [] : [],
        trigger: template.trigger
    };
    
    document.getElementById('permTemplateName').textContent = template.name;
    document.getElementById('permTemplateId').textContent = template.templateId;
    
    if (currentPermissions.accessMode === 'all') {
        document.getElementById('permAccessAll').checked = true;
    } else {
        document.getElementById('permAccessRestricted').checked = true;
    }
    
    updatePermAccessMode();
    
    document.querySelectorAll('.perm-subaccount-check').forEach(function(cb) {
        cb.checked = currentPermissions.subAccounts.includes(cb.value);
    });
    document.querySelectorAll('.perm-role-check').forEach(function(cb) {
        cb.checked = currentPermissions.roles.includes(cb.value);
    });
    document.querySelectorAll('.perm-user-check').forEach(function(cb) {
        cb.checked = currentPermissions.users.includes(cb.value);
    });
    
    updatePermissionCounts();
    updatePermissionChips();
    updatePermAccessSummary();
    
    if (template.trigger === 'api') {
        document.getElementById('permApiWarning').classList.remove('d-none');
    } else {
        document.getElementById('permApiWarning').classList.add('d-none');
    }
    
    new bootstrap.Modal(document.getElementById('templatePermissionsModal')).show();
}

function updatePermAccessMode() {
    var isAll = document.getElementById('permAccessAll').checked;
    var restrictedSection = document.getElementById('permRestrictedSection');
    var allUsersSection = document.getElementById('permAllUsersSection');
    var accessBadge = document.getElementById('permAccessBadge');
    
    currentPermissions.accessMode = isAll ? 'all' : 'restricted';
    
    if (isAll) {
        restrictedSection.classList.add('d-none');
        allUsersSection.classList.remove('d-none');
        accessBadge.className = 'badge bg-success';
        accessBadge.textContent = 'All Users';
    } else {
        restrictedSection.classList.remove('d-none');
        allUsersSection.classList.add('d-none');
        accessBadge.className = 'badge bg-warning text-dark';
        accessBadge.textContent = 'Restricted';
    }
    
    updatePermAccessSummary();
    validatePermissions();
}

function togglePermSubAccount(value) {
    var idx = currentPermissions.subAccounts.indexOf(value);
    if (idx > -1) {
        currentPermissions.subAccounts.splice(idx, 1);
    } else {
        if (value === 'all') {
            currentPermissions.subAccounts = ['all'];
            document.querySelectorAll('.perm-subaccount-check').forEach(function(cb) {
                cb.checked = cb.value === 'all';
            });
        } else {
            var allIdx = currentPermissions.subAccounts.indexOf('all');
            if (allIdx > -1) {
                currentPermissions.subAccounts.splice(allIdx, 1);
                document.getElementById('permSubAll').checked = false;
            }
            currentPermissions.subAccounts.push(value);
        }
    }
    updatePermissionCounts();
    updatePermissionChips();
    updatePermAccessSummary();
    validatePermissions();
}

function togglePermRole(value) {
    var idx = currentPermissions.roles.indexOf(value);
    if (idx > -1) {
        currentPermissions.roles.splice(idx, 1);
    } else {
        currentPermissions.roles.push(value);
    }
    updatePermissionCounts();
    updatePermissionChips();
    updatePermAccessSummary();
    validatePermissions();
}

function togglePermUser(value) {
    var idx = currentPermissions.users.indexOf(value);
    if (idx > -1) {
        currentPermissions.users.splice(idx, 1);
    } else {
        currentPermissions.users.push(value);
    }
    updatePermissionCounts();
    updatePermissionChips();
    updatePermAccessSummary();
    validatePermissions();
}

function updatePermissionCounts() {
    document.getElementById('permSubAccountCount').textContent = currentPermissions.subAccounts.length;
    document.getElementById('permRoleCount').textContent = currentPermissions.roles.length;
    document.getElementById('permUserCount').textContent = currentPermissions.users.length;
}

function updatePermissionChips() {
    var subAccountChips = document.getElementById('permSubAccountChips');
    var roleChips = document.getElementById('permRoleChips');
    var userChips = document.getElementById('permUserChips');
    
    subAccountChips.innerHTML = currentPermissions.subAccounts.map(function(sa) {
        return '<span class="badge bg-primary py-2 px-3">' +
               '<i class="fas fa-building me-1"></i>' + (subAccountLabels[sa] || sa) +
               ' <i class="fas fa-times ms-1" style="cursor:pointer;" onclick="removePermSubAccount(\'' + sa + '\')"></i></span>';
    }).join('');
    
    roleChips.innerHTML = currentPermissions.roles.map(function(r) {
        return '<span class="badge bg-info py-2 px-3">' +
               '<i class="fas fa-user-tag me-1"></i>' + (roleLabels[r] || r) +
               ' <i class="fas fa-times ms-1" style="cursor:pointer;" onclick="removePermRole(\'' + r + '\')"></i></span>';
    }).join('');
    
    userChips.innerHTML = currentPermissions.users.map(function(u) {
        return '<span class="badge bg-secondary py-2 px-3">' +
               '<i class="fas fa-user me-1"></i>' + (userLabels[u] || u) +
               ' <i class="fas fa-times ms-1" style="cursor:pointer;" onclick="removePermUser(\'' + u + '\')"></i></span>';
    }).join('');
}

function removePermSubAccount(value) {
    document.querySelector('.perm-subaccount-check[value="' + value + '"]').checked = false;
    togglePermSubAccount(value);
}

function removePermRole(value) {
    document.querySelector('.perm-role-check[value="' + value + '"]').checked = false;
    togglePermRole(value);
}

function removePermUser(value) {
    document.querySelector('.perm-user-check[value="' + value + '"]').checked = false;
    togglePermUser(value);
}

function updatePermAccessSummary() {
    var summaryEl = document.getElementById('permAccessSummary');
    
    if (currentPermissions.accessMode === 'all') {
        summaryEl.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>' +
                              '<strong>All users</strong> in your organization can use this template.';
        return;
    }
    
    var parts = [];
    
    if (currentPermissions.subAccounts.length > 0) {
        if (currentPermissions.subAccounts.includes('all')) {
            parts.push('<span class="text-primary"><i class="fas fa-building me-1"></i>All sub-accounts</span>');
        } else {
            parts.push('<span class="text-primary"><i class="fas fa-building me-1"></i>' + 
                      currentPermissions.subAccounts.length + ' sub-account(s)</span>');
        }
    }
    
    if (currentPermissions.roles.length > 0) {
        parts.push('<span class="text-info"><i class="fas fa-user-tag me-1"></i>' + 
                  currentPermissions.roles.length + ' role(s)</span>');
    }
    
    if (currentPermissions.users.length > 0) {
        parts.push('<span class="text-secondary"><i class="fas fa-user me-1"></i>' + 
                  currentPermissions.users.length + ' user(s)</span>');
    }
    
    if (parts.length === 0) {
        summaryEl.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>No access configured - template will be unusable</span>';
    } else {
        summaryEl.innerHTML = 'Access granted to: ' + parts.join(' + ');
    }
}

function validatePermissions() {
    var warningEl = document.getElementById('permNoSelectionWarning');
    var saveBtn = document.getElementById('permSaveBtn');
    
    if (currentPermissions.accessMode === 'all') {
        warningEl.classList.add('d-none');
        saveBtn.disabled = false;
        return true;
    }
    
    var hasAccess = currentPermissions.subAccounts.length > 0 || 
                    currentPermissions.roles.length > 0 || 
                    currentPermissions.users.length > 0;
    
    if (!hasAccess) {
        warningEl.classList.remove('d-none');
        saveBtn.disabled = true;
        return false;
    } else {
        warningEl.classList.add('d-none');
        saveBtn.disabled = false;
        return true;
    }
}

function filterPermSubAccounts() {
    var search = document.getElementById('permSubAccountSearch').value.toLowerCase();
    var items = document.querySelectorAll('#permSubAccountList .form-check');
    
    items.forEach(function(item) {
        var label = item.querySelector('.form-check-label').textContent.toLowerCase();
        item.style.display = label.includes(search) ? '' : 'none';
    });
}

function filterPermUsers() {
    var search = document.getElementById('permUserSearch').value.toLowerCase();
    var items = document.querySelectorAll('.perm-user-item');
    
    items.forEach(function(item) {
        var name = item.getAttribute('data-name') || '';
        var email = item.getAttribute('data-email') || '';
        item.style.display = (name.includes(search) || email.includes(search)) ? '' : 'none';
    });
}

function saveTemplatePermissions() {
    if (!validatePermissions()) {
        showToast('Please configure access before saving', 'warning');
        return;
    }
    
    var template = mockTemplates.find(function(t) { return t.id === currentPermissions.templateId; });
    if (!template) return;
    
    template.permissions = {
        subAccounts: currentPermissions.subAccounts.slice(),
        roles: currentPermissions.roles.slice(),
        users: currentPermissions.users.slice()
    };
    
    if (currentPermissions.accessMode === 'all') {
        template.subAccounts = ['all'];
        template.accessScope = 'All Users';
    } else {
        template.subAccounts = currentPermissions.subAccounts.slice();
        
        if (currentPermissions.subAccounts.includes('all')) {
            template.accessScope = 'All Sub-accounts';
        } else if (currentPermissions.subAccounts.length > 0) {
            template.accessScope = currentPermissions.subAccounts.length + ' Sub-account(s)';
        } else if (currentPermissions.roles.length > 0) {
            template.accessScope = currentPermissions.roles.length + ' Role(s)';
        } else {
            template.accessScope = currentPermissions.users.length + ' User(s)';
        }
    }
    
    template.lastUpdated = new Date().toISOString().split('T')[0];
    
    bootstrap.Modal.getInstance(document.getElementById('templatePermissionsModal')).hide();
    renderTemplates();
    showToast('Permissions updated for "' + template.name + '"', 'success');
}

function viewApiStructure(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    
    if (template.trigger !== 'api') {
        showToast('API Structure is only available for API-triggered templates', 'warning');
        return;
    }
    
    document.getElementById('apiTemplateName').textContent = template.name;
    document.getElementById('apiTemplateIdBadge').textContent = 'ID: ' + template.templateId;
    document.getElementById('apiChannelBadge').textContent = getChannelLabel(template.channel);
    document.getElementById('apiChannelBadge').className = 'badge ' + getChannelBadgeClass(template.channel);
    document.getElementById('apiVersionBadge').textContent = 'v' + template.version;
    
    var placeholders = extractPlaceholders(template.content);
    var hasPlaceholders = placeholders.length > 0;
    
    if (hasPlaceholders) {
        document.getElementById('apiNoPlaceholders').classList.add('d-none');
        document.getElementById('apiPlaceholdersList').classList.remove('d-none');
        document.getElementById('apiPlaceholderChips').innerHTML = placeholders.map(function(ph) {
            return '<span class="placeholder-pill"><i class="fas fa-tag"></i>{' + ph + '}</span>';
        }).join('');
    } else {
        document.getElementById('apiNoPlaceholders').classList.remove('d-none');
        document.getElementById('apiPlaceholdersList').classList.add('d-none');
    }
    
    var channelReq = document.getElementById('apiChannelRequirements');
    if (template.channel === 'sms') {
        channelReq.innerHTML = '<div class="d-flex align-items-start mb-2">' +
            '<i class="fas fa-check-circle text-success me-2 mt-1"></i>' +
            '<span>Standard SMS delivery</span></div>' +
            '<div class="d-flex align-items-start">' +
            '<i class="fas fa-info-circle text-info me-2 mt-1"></i>' +
            '<span class="small text-muted">Messages over 160 chars split into segments</span></div>';
    } else if (template.channel === 'basic_rcs') {
        channelReq.innerHTML = '<div class="d-flex align-items-start mb-2">' +
            '<i class="fas fa-check-circle text-success me-2 mt-1"></i>' +
            '<span>Basic RCS with SMS fallback</span></div>' +
            '<div class="d-flex align-items-start">' +
            '<i class="fas fa-info-circle text-info me-2 mt-1"></i>' +
            '<span class="small text-muted">RCS branding enabled, falls back to SMS if unsupported</span></div>';
    } else if (template.channel === 'rich_rcs') {
        channelReq.innerHTML = '<div class="d-flex align-items-start mb-2">' +
            '<i class="fas fa-check-circle text-success me-2 mt-1"></i>' +
            '<span>Rich RCS with SMS fallback</span></div>' +
            '<div class="d-flex align-items-start mb-2">' +
            '<i class="fas fa-image text-primary me-2 mt-1"></i>' +
            '<span class="small">Rich cards, carousels, and interactive buttons</span></div>' +
            '<div class="d-flex align-items-start">' +
            '<i class="fas fa-info-circle text-info me-2 mt-1"></i>' +
            '<span class="small text-muted">Falls back to SMS with link if RCS unsupported</span></div>';
    }
    
    generateApiCodeExamples(template, placeholders);
    
    new bootstrap.Modal(document.getElementById('apiStructureModal')).show();
}

function extractPlaceholders(content) {
    var regex = /\{([A-Za-z][A-Za-z0-9_]*)\}/g;
    var matches = [];
    var match;
    while ((match = regex.exec(content)) !== null) {
        if (!matches.includes(match[1])) {
            matches.push(match[1]);
        }
    }
    return matches;
}

function generateApiCodeExamples(template, placeholders) {
    var hasPlaceholders = placeholders.length > 0;
    var msisdnType = hasPlaceholders ? '"+447700900123"' : '["+447700900123", "+447700900456"]';
    var msisdnPy = hasPlaceholders ? '"+447700900123"' : '["+447700900123", "+447700900456"]';
    
    var placeholderObj = {};
    placeholders.forEach(function(ph) {
        placeholderObj[ph] = 'Example ' + ph;
    });
    var placeholderJson = JSON.stringify(placeholderObj, null, 2);
    var placeholderJsonInline = JSON.stringify(placeholderObj);
    
    var curlCode = 'curl -X POST https://api.quicksms.co.uk/v1/messages/template \\\n' +
        '  -H "Authorization: Bearer YOUR_API_KEY" \\\n' +
        '  -H "Content-Type: application/json" \\\n' +
        '  -d \'{\n' +
        '    "template_id": "' + template.templateId + '",\n' +
        '    "msisdn": ' + msisdnType;
    if (hasPlaceholders) {
        curlCode += ',\n    "placeholders": ' + placeholderJson.replace(/\n/g, '\n    ');
    }
    curlCode += '\n  }\'';
    
    var pythonCode = 'import requests\n\n' +
        'url = "https://api.quicksms.co.uk/v1/messages/template"\n' +
        'headers = {\n' +
        '    "Authorization": "Bearer YOUR_API_KEY",\n' +
        '    "Content-Type": "application/json"\n' +
        '}\n\n' +
        'payload = {\n' +
        '    "template_id": "' + template.templateId + '",\n' +
        '    "msisdn": ' + msisdnPy;
    if (hasPlaceholders) {
        pythonCode += ',\n    "placeholders": ' + placeholderJsonInline;
    }
    pythonCode += '\n}\n\n' +
        'response = requests.post(url, json=payload, headers=headers)\n' +
        'print(response.json())';
    
    var nodejsCode = 'const axios = require(\'axios\');\n\n' +
        'const payload = {\n' +
        '  template_id: "' + template.templateId + '",\n' +
        '  msisdn: ' + msisdnType;
    if (hasPlaceholders) {
        nodejsCode += ',\n  placeholders: ' + placeholderJsonInline;
    }
    nodejsCode += '\n};\n\n' +
        'axios.post(\'https://api.quicksms.co.uk/v1/messages/template\', payload, {\n' +
        '  headers: {\n' +
        '    \'Authorization\': \'Bearer YOUR_API_KEY\',\n' +
        '    \'Content-Type\': \'application/json\'\n' +
        '  }\n' +
        '})\n' +
        '.then(response => console.log(response.data))\n' +
        '.catch(error => console.error(error));';
    
    var phpMsisdn = hasPlaceholders ? '"+447700900123"' : '["+447700900123", "+447700900456"]';
    var phpCode = '<' + '?php\n\n' +
        '$curl = curl_init();\n\n' +
        '$payload = [\n' +
        '    "template_id" => "' + template.templateId + '",\n' +
        '    "msisdn" => ' + phpMsisdn;
    if (hasPlaceholders) {
        var phpPlaceholders = placeholders.map(function(ph) {
            return '"' + ph + '" => "Example ' + ph + '"';
        }).join(', ');
        phpCode += ',\n    "placeholders" => [' + phpPlaceholders + ']';
    }
    phpCode += '\n];\n\n' +
        'curl_setopt_array($curl, [\n' +
        '    CURLOPT_URL => "https://api.quicksms.co.uk/v1/messages/template",\n' +
        '    CURLOPT_RETURNTRANSFER => true,\n' +
        '    CURLOPT_POST => true,\n' +
        '    CURLOPT_POSTFIELDS => json_encode($payload),\n' +
        '    CURLOPT_HTTPHEADER => [\n' +
        '        "Authorization: Bearer YOUR_API_KEY",\n' +
        '        "Content-Type: application/json"\n' +
        '    ]\n' +
        ']);\n\n' +
        '$response = curl_exec($curl);\n' +
        'curl_close($curl);\n\n' +
        'echo $response;';
    
    var javaCode = 'import java.net.http.*;\nimport java.net.URI;\n\n' +
        'public class QuickSMSExample {\n' +
        '    public static void main(String[] args) throws Exception {\n' +
        '        String json = """{\n' +
        '            "template_id": "' + template.templateId + '",\n' +
        '            "msisdn": ' + msisdnType;
    if (hasPlaceholders) {
        javaCode += ',\n            "placeholders": ' + placeholderJsonInline;
    }
    javaCode += '\n        }""";\n\n' +
        '        HttpClient client = HttpClient.newHttpClient();\n' +
        '        HttpRequest request = HttpRequest.newBuilder()\n' +
        '            .uri(URI.create("https://api.quicksms.co.uk/v1/messages/template"))\n' +
        '            .header("Authorization", "Bearer YOUR_API_KEY")\n' +
        '            .header("Content-Type", "application/json")\n' +
        '            .POST(HttpRequest.BodyPublishers.ofString(json))\n' +
        '            .build();\n\n' +
        '        HttpResponse<String> response = client.send(request,\n' +
        '            HttpResponse.BodyHandlers.ofString());\n' +
        '        System.out.println(response.body());\n' +
        '    }\n' +
        '}';
    
    var csharpCode = 'using System.Net.Http;\nusing System.Text;\nusing System.Text.Json;\n\n' +
        'var payload = new {\n' +
        '    template_id = "' + template.templateId + '",\n' +
        '    msisdn = ' + (hasPlaceholders ? '"+447700900123"' : 'new[] { "+447700900123", "+447700900456" }');
    if (hasPlaceholders) {
        csharpCode += ',\n    placeholders = new {\n';
        placeholders.forEach(function(ph, i) {
            csharpCode += '        ' + ph + ' = "Example ' + ph + '"' + (i < placeholders.length - 1 ? ',' : '') + '\n';
        });
        csharpCode += '    }';
    }
    csharpCode += '\n};\n\n' +
        'using var client = new HttpClient();\n' +
        'client.DefaultRequestHeaders.Add("Authorization", "Bearer YOUR_API_KEY");\n\n' +
        'var json = JsonSerializer.Serialize(payload);\n' +
        'var content = new StringContent(json, Encoding.UTF8, "application/json");\n\n' +
        'var response = await client.PostAsync(\n' +
        '    "https://api.quicksms.co.uk/v1/messages/template", content);\n\n' +
        'var result = await response.Content.ReadAsStringAsync();\n' +
        'Console.WriteLine(result);';
    
    document.getElementById('apiCodeCurl').textContent = curlCode;
    document.getElementById('apiCodePython').textContent = pythonCode;
    document.getElementById('apiCodeNodejs').textContent = nodejsCode;
    document.getElementById('apiCodePhp').textContent = phpCode;
    document.getElementById('apiCodeJava').textContent = javaCode;
    document.getElementById('apiCodeCsharp').textContent = csharpCode;
}

function copyApiCode() {
    var activePane = document.querySelector('#apiCodeTabContent .tab-pane.active code');
    if (activePane) {
        navigator.clipboard.writeText(activePane.textContent).then(function() {
            showToast('Code copied to clipboard', 'success');
        }).catch(function() {
            showToast('Failed to copy code', 'warning');
        });
    }
}

function archiveTemplate(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    
    if (confirm('Are you sure you want to archive "' + template.name + '"? Archived templates cannot be edited or used.')) {
        template.status = 'archived';
        template.lastUpdated = new Date().toISOString().split('T')[0];
        renderTemplates();
        showToast('Template "' + template.name + '" has been archived', 'success');
    }
}

function goLiveTemplate(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template || template.status === 'archived') return;
    
    template.status = 'live';
    template.version = template.version + 1;
    template.lastUpdated = new Date().toISOString().split('T')[0];
    renderTemplates();
    showToast('Template "' + template.name + '" is now Live (v' + template.version + ')', 'success');
}

function showToast(message, type) {
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    var bgClass = type === 'success' ? 'bg-success' : type === 'warning' ? 'bg-warning' : 'bg-info';
    var textClass = type === 'warning' ? 'text-dark' : 'text-white';
    
    var toastHtml = '<div class="toast align-items-center ' + bgClass + ' ' + textClass + ' border-0" role="alert">' +
        '<div class="d-flex">' +
        '<div class="toast-body">' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
        '</div></div>';
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    var toastEl = toastContainer.lastElementChild;
    var toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}

function handleTemplateContentChange() {
    var content = document.getElementById('templateContent').value;
    var charCount = content.length;
    
    document.getElementById('tplCharCount').textContent = charCount;
    
    var hasUnicode = /[^\x00-\x7F\u00A0-\u00FF]/.test(content);
    document.getElementById('tplEncodingType').textContent = hasUnicode ? 'Unicode' : 'GSM-7';
    
    var unicodeWarning = document.getElementById('tplUnicodeWarning');
    if (unicodeWarning) {
        unicodeWarning.classList.toggle('d-none', !hasUnicode);
    }
    
    var maxCharsPerSegment = hasUnicode ? 70 : 160;
    var segments = Math.ceil(charCount / maxCharsPerSegment) || 1;
    
    updateTemplatePreview();
    document.getElementById('tplPartCount').textContent = segments;
    
    wizardData.content = content;
    
    detectPlaceholders(content);
}

function toggleTplTrackableLink() {
    var isChecked = document.getElementById('tplIncludeTrackableLink').checked;
    var summary = document.getElementById('tplTrackableLinkSummary');
    
    if (isChecked) {
        summary.classList.remove('d-none');
        wizardData.trackableLink = { enabled: true, domain: 'qsms.uk' };
    } else {
        summary.classList.add('d-none');
        wizardData.trackableLink = { enabled: false };
    }
}

function openTplTrackableLinkModal() {
    showToast('Trackable link settings would open here', 'info');
}

function toggleTplMessageExpiry() {
    var isChecked = document.getElementById('tplMessageExpiry').checked;
    var summary = document.getElementById('tplMessageExpirySummary');
    
    if (isChecked) {
        summary.classList.remove('d-none');
        wizardData.messageExpiry = { enabled: true, value: '24 Hours' };
    } else {
        summary.classList.add('d-none');
        wizardData.messageExpiry = { enabled: false };
    }
}

function openTplMessageExpiryModal() {
    showToast('Message expiry settings would open here', 'info');
}

function toggleTplOptoutManagement() {
    var isChecked = document.getElementById('tplEnableOptout').checked;
    var section = document.getElementById('tplOptoutSection');
    
    if (isChecked) {
        section.classList.remove('d-none');
        wizardData.optout = { enabled: true };
    } else {
        section.classList.add('d-none');
        wizardData.optout = { enabled: false };
    }
}

function toggleTplReplyOptout() {
    var isChecked = document.getElementById('tplEnableReplyOptout').checked;
    var config = document.getElementById('tplReplyOptoutConfig');
    
    if (isChecked) {
        config.classList.remove('d-none');
    } else {
        config.classList.add('d-none');
    }
}

function toggleTplUrlOptout() {
    var isChecked = document.getElementById('tplEnableUrlOptout').checked;
    var config = document.getElementById('tplUrlOptoutConfig');
    
    if (isChecked) {
        config.classList.remove('d-none');
    } else {
        config.classList.add('d-none');
    }
}

function detectPlaceholders(content) {
    var placeholderRegex = /\{([A-Za-z][A-Za-z0-9_]*)\}/g;
    var matches = [];
    var match;
    
    while ((match = placeholderRegex.exec(content)) !== null) {
        if (!matches.includes(match[1])) {
            matches.push(match[1]);
        }
    }
    
    wizardData.placeholders = matches;
    
    var countEl = document.getElementById('tplPlaceholderCount');
    var noPlaceholdersEl = document.getElementById('tplNoPlaceholders');
    var placeholderListEl = document.getElementById('tplPlaceholderList');
    var chipsEl = document.getElementById('tplPlaceholderChips');
    
    countEl.textContent = matches.length + ' placeholder' + (matches.length !== 1 ? 's' : '');
    countEl.className = 'placeholder-counter' + (matches.length === 0 ? ' empty' : '');
    
    if (matches.length === 0) {
        noPlaceholdersEl.classList.remove('d-none');
        placeholderListEl.classList.add('d-none');
    } else {
        noPlaceholdersEl.classList.add('d-none');
        placeholderListEl.classList.remove('d-none');
        
        var contactBookFields = ['FirstName', 'LastName', 'Email', 'Phone', 'Company'];
        
        chipsEl.innerHTML = matches.map(function(ph) {
            var isContactBook = contactBookFields.includes(ph);
            var icon = isContactBook ? 'fas fa-address-book' : 'fas fa-code';
            var badgeClass = isContactBook ? 'bg-primary' : 'bg-secondary';
            var source = isContactBook ? 'Contact Book' : 'API Payload';
            
            return '<span class="badge ' + badgeClass + ' py-2 px-3" title="Source: ' + source + '">' +
                   '<i class="' + icon + ' me-1"></i>{' + ph + '}' +
                   '</span>';
        }).join('');
    }
    
    updateApiRulesDisplay();
}

function updateApiRulesDisplay() {
    var apiRulesCard = document.getElementById('tplApiRulesCard');
    var singleRule = document.getElementById('tplApiRuleSingle');
    var multipleRule = document.getElementById('tplApiRuleMultiple');
    
    if (wizardData.trigger !== 'api') {
        apiRulesCard.classList.add('d-none');
        return;
    }
    
    apiRulesCard.classList.remove('d-none');
    
    var hasPlaceholders = wizardData.placeholders && wizardData.placeholders.length > 0;
    
    if (hasPlaceholders) {
        singleRule.style.borderColor = '#ffc107';
        singleRule.style.backgroundColor = 'rgba(255, 193, 7, 0.1)';
        multipleRule.style.borderColor = '#dee2e6';
        multipleRule.style.backgroundColor = 'transparent';
    } else {
        singleRule.style.borderColor = '#dee2e6';
        singleRule.style.backgroundColor = 'transparent';
        multipleRule.style.borderColor = '#198754';
        multipleRule.style.backgroundColor = 'rgba(25, 135, 84, 0.1)';
    }
}

function viewPlaceholderSchema() {
    var schemaListEl = document.getElementById('schemaPlaceholdersList');
    var exampleEl = document.getElementById('schemaExampleRequest');
    
    var placeholders = wizardData.placeholders || [];
    
    if (placeholders.length === 0) {
        schemaListEl.innerHTML = '<em class="text-muted">No placeholders detected in this template</em>';
        
        exampleEl.textContent = JSON.stringify({
            template_id: wizardData.templateId || '12345678',
            msisdn: ['+447700900123', '+447700900456', '+447700900789'],
            placeholders: {}
        }, null, 2);
    } else {
        schemaListEl.innerHTML = placeholders.map(function(ph) {
            return '<span class="placeholder-pill"><i class="fas fa-tag"></i>{' + ph + '}</span>';
        }).join('');
        
        var examplePlaceholders = {};
        placeholders.forEach(function(ph) {
            examplePlaceholders[ph] = 'Example ' + ph;
        });
        
        exampleEl.textContent = JSON.stringify({
            template_id: wizardData.templateId || '12345678',
            msisdn: '+447700900123',
            placeholders: examplePlaceholders
        }, null, 2);
    }
    
    new bootstrap.Modal(document.getElementById('placeholderSchemaModal')).show();
}

function copySchemaExample() {
    var exampleEl = document.getElementById('schemaExampleRequest');
    navigator.clipboard.writeText(exampleEl.textContent).then(function() {
        showToast('API example copied to clipboard', 'success');
    }).catch(function() {
        showToast('Failed to copy to clipboard', 'warning');
    });
}

function validateTemplateForSave() {
    var errors = [];
    
    if (!wizardData.name || wizardData.name.trim() === '') {
        errors.push('Template name is required');
    }
    
    if (!wizardData.trigger) {
        errors.push('Trigger type is required');
    }
    
    var channel = wizardData.channel || 'sms';
    if (channel === 'rich_rcs') {
        if (!wizardData.rcsContent) {
            errors.push('RCS content is required for Rich RCS templates');
        }
    } else {
        if (!wizardData.content || wizardData.content.trim() === '') {
            errors.push('Message content is required');
        }
    }
    
    if (wizardData.trigger === 'api' && wizardData.placeholders && wizardData.placeholders.length > 0) {
        var contactBookFields = ['FirstName', 'LastName', 'Email', 'Phone', 'Company', 'Custom1', 'Custom2', 'Custom3'];
        var customPlaceholders = wizardData.placeholders.filter(function(ph) {
            return !contactBookFields.includes(ph);
        });
        
        if (customPlaceholders.length > 0) {
            console.log('API template has custom placeholders:', customPlaceholders);
        }
    }
    
    return errors;
}

function openTemplatePersonalisation() {
    new bootstrap.Modal(document.getElementById('templatePersonalisationModal')).show();
}

function insertTemplatePlaceholder(field) {
    var fieldMap = {
        'firstName': '{FirstName}',
        'lastName': '{LastName}',
        'company': '{Company}',
        'email': '{Email}',
        'phone': '{Phone}',
        'custom1': '{Custom1}',
        'custom2': '{Custom2}',
        'custom3': '{Custom3}',
        'date': '{Date}',
        'time': '{Time}',
        'shortUrl': '{ShortURL}'
    };
    
    var placeholder = fieldMap[field] || '{' + field + '}';
    var textarea = document.getElementById('templateContent');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
    
    handleTemplateContentChange();
    bootstrap.Modal.getInstance(document.getElementById('templatePersonalisationModal')).hide();
}

function openTemplateEmojiPicker() {
    new bootstrap.Modal(document.getElementById('templateEmojiModal')).show();
}

function insertTemplateEmoji(emoji) {
    var textarea = document.getElementById('templateContent');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    
    textarea.value = text.substring(0, start) + emoji + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
    textarea.focus();
    
    handleTemplateContentChange();
    bootstrap.Modal.getInstance(document.getElementById('templateEmojiModal')).hide();
}

function openTemplateAiAssistant() {
    var content = document.getElementById('templateContent').value;
    var currentContentEl = document.getElementById('tplAiCurrentContent');
    
    if (content.trim()) {
        currentContentEl.innerHTML = content;
    } else {
        currentContentEl.innerHTML = '<em class="text-muted">No content to improve</em>';
    }
    
    document.getElementById('tplAiResultSection').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('templateAiModal')).show();
}

function templateAiImprove(action) {
    var content = document.getElementById('templateContent').value;
    if (!content.trim()) {
        showToast('Please enter some content first', 'warning');
        return;
    }
    
    var suggestions = {
        'tone': 'Hi there! 👋 We\'re excited to share this update with you. ' + content.replace(/^Hi|^Hello/i, '').trim(),
        'shorten': content.substring(0, Math.min(content.length, 100)) + (content.length > 100 ? '...' : ''),
        'grammar': content.charAt(0).toUpperCase() + content.slice(1).replace(/\s+/g, ' ').trim(),
        'clarity': content.replace(/\{(\w+)\}/g, '[{$1}]').replace(/\s+/g, ' ').trim()
    };
    
    document.getElementById('tplAiSuggestedContent').textContent = suggestions[action] || content;
    document.getElementById('tplAiResultSection').classList.remove('d-none');
}

function useTemplateAiSuggestion() {
    var suggestion = document.getElementById('tplAiSuggestedContent').textContent;
    document.getElementById('templateContent').value = suggestion;
    handleTemplateContentChange();
    bootstrap.Modal.getInstance(document.getElementById('templateAiModal')).hide();
    showToast('AI suggestion applied', 'success');
}

function discardTemplateAiSuggestion() {
    document.getElementById('tplAiResultSection').classList.add('d-none');
}

function openTemplateRcsWizard() {
    if (typeof openRcsWizard === 'function') {
        if (templateRcsPayload && typeof loadRcsPayloadIntoWizard === 'function') {
            loadRcsPayloadIntoWizard(templateRcsPayload);
        }
        openRcsWizard();
    } else {
        showToast('RCS Wizard is not available', 'error');
    }
}

function updateRcsWizardPreviewInMain() {
    if (typeof rcsPersistentPayload !== 'undefined' && rcsPersistentPayload) {
        templateRcsPayload = rcsPersistentPayload;
        wizardData.rcsPayload = rcsPersistentPayload;
        
        var summaryText = templateRcsPayload.type === 'carousel' 
            ? 'RCS Carousel (' + templateRcsPayload.cardCount + ' cards) configured'
            : 'RCS Rich Card configured';
        
        var totalButtons = templateRcsPayload.cards.reduce(function(sum, c) { return sum + c.buttons.length; }, 0);
        if (totalButtons > 0) {
            summaryText += ' with ' + totalButtons + ' action button' + (totalButtons > 1 ? 's' : '');
        }
        
        var configuredText = document.getElementById('tplRcsConfiguredText');
        var configuredSummary = document.getElementById('tplRcsConfiguredSummary');
        if (configuredText) configuredText.textContent = summaryText;
        if (configuredSummary) configuredSummary.classList.remove('d-none');
        
        updateTemplatePreview();
    }
}

function setupTemplateChannelListeners() {
    document.querySelectorAll('input[name="templateChannel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var channel = this.value;
            var textEditor = document.getElementById('tplTextEditorContainer');
            var rcsSection = document.getElementById('tplRcsContentSection');
            var contentLabel = document.getElementById('tplContentLabel');
            var rcsHelper = document.getElementById('tplRcsTextHelper');
            var senderIdSection = document.getElementById('tplSenderIdSection');
            var rcsAgentSection = document.getElementById('tplRcsAgentSection');
            var previewChannel = document.getElementById('tplPreviewChannel');
            var previewToggle = document.getElementById('tplPreviewToggleContainer');
            var basicRcsToggle = document.getElementById('tplBasicRcsPreviewToggle');
            
            wizardData.channel = channel;
            
            if (channel === 'rich_rcs') {
                textEditor.classList.add('d-none');
                rcsSection.classList.remove('d-none');
                contentLabel.textContent = 'RCS Content';
                rcsAgentSection.classList.remove('d-none');
                previewChannel.textContent = 'Rich RCS';
                previewToggle.classList.remove('d-none');
                basicRcsToggle.classList.add('d-none');
                tplRichRcsPreviewMode = 'rcs';
                document.getElementById('tplPreviewRCSBtn').classList.add('active');
                document.getElementById('tplPreviewSMSBtn').classList.remove('active');
                document.getElementById('tplPreviewRCSBtn').style.background = '#886CC0';
                document.getElementById('tplPreviewRCSBtn').style.color = 'white';
                document.getElementById('tplPreviewSMSBtn').style.background = 'white';
                document.getElementById('tplPreviewSMSBtn').style.color = '#886CC0';
            } else {
                textEditor.classList.remove('d-none');
                rcsSection.classList.add('d-none');
                
                if (channel === 'basic_rcs') {
                    contentLabel.textContent = 'Basic RCS Content';
                    rcsHelper.classList.remove('d-none');
                    rcsAgentSection.classList.remove('d-none');
                    previewChannel.textContent = 'Basic RCS';
                    previewToggle.classList.add('d-none');
                    basicRcsToggle.classList.remove('d-none');
                    tplBasicRcsPreviewMode = 'rcs';
                    document.getElementById('tplBasicPreviewRCSBtn').classList.add('active');
                    document.getElementById('tplBasicPreviewSMSBtn').classList.remove('active');
                    document.getElementById('tplBasicPreviewRCSBtn').style.background = '#886CC0';
                    document.getElementById('tplBasicPreviewRCSBtn').style.color = 'white';
                    document.getElementById('tplBasicPreviewSMSBtn').style.background = 'white';
                    document.getElementById('tplBasicPreviewSMSBtn').style.color = '#886CC0';
                } else {
                    contentLabel.textContent = 'SMS Content';
                    rcsHelper.classList.add('d-none');
                    rcsAgentSection.classList.add('d-none');
                    previewChannel.textContent = 'SMS';
                    previewToggle.classList.add('d-none');
                    basicRcsToggle.classList.add('d-none');
                }
            }
            
            updateTemplatePreview();
        });
    });
}

function updateTemplatePreview() {
    var container = document.getElementById('tplPreviewContainer');
    if (!container || typeof RcsPreviewRenderer === 'undefined') return;
    
    var channel = document.querySelector('input[name="templateChannel"]:checked')?.value || 'sms';
    var content = document.getElementById('templateContent')?.value || '';
    var senderId = document.getElementById('tplSenderId');
    var rcsAgentSelect = document.getElementById('tplRcsAgent');
    var senderIdText = (senderId?.selectedOptions[0]?.text || 'Sender').replace(/\s*\(.*?\)\s*$/, '');
    
    var previewConfig = {
        channel: 'sms',
        content: content,
        senderId: senderIdText,
        rcsAgent: null,
        rcsPayload: null
    };
    
    if (channel === 'sms') {
        previewConfig.channel = 'sms';
    } else if (channel === 'basic_rcs') {
        if (tplBasicRcsPreviewMode === 'sms') {
            previewConfig.channel = 'sms';
        } else {
            previewConfig.channel = 'basic_rcs';
            var selectedOption = rcsAgentSelect?.selectedOptions[0];
            if (selectedOption && selectedOption.value) {
                previewConfig.rcsAgent = {
                    name: selectedOption.dataset?.name || 'RCS Agent',
                    logo: selectedOption.dataset?.logo || '{{ asset("images/rcs-agents/quicksms-brand.svg") }}',
                    tagline: selectedOption.dataset?.tagline || '',
                    brandColor: selectedOption.dataset?.brandColor || '#886CC0'
                };
            }
        }
    } else if (channel === 'rich_rcs') {
        if (tplRichRcsPreviewMode === 'sms') {
            previewConfig.channel = 'sms';
            previewConfig.content = templateRcsPayload?.fallback || content || 'SMS fallback content';
        } else {
            previewConfig.channel = 'rich_rcs';
            var selectedOption = rcsAgentSelect?.selectedOptions[0];
            if (selectedOption && selectedOption.value) {
                previewConfig.rcsAgent = {
                    name: selectedOption.dataset?.name || 'RCS Agent',
                    logo: selectedOption.dataset?.logo || '{{ asset("images/rcs-agents/quicksms-brand.svg") }}',
                    tagline: selectedOption.dataset?.tagline || '',
                    brandColor: selectedOption.dataset?.brandColor || '#886CC0'
                };
            }
            previewConfig.rcsPayload = templateRcsPayload;
        }
    }
    
    container.innerHTML = RcsPreviewRenderer.renderPreview(previewConfig);
    
    var segments = Math.ceil((content.length || 1) / 160);
    document.getElementById('tplPreviewSegments').textContent = segments;
}

function showTemplatePreview(mode) {
    tplRichRcsPreviewMode = mode;
    var rcsBtn = document.getElementById('tplPreviewRCSBtn');
    var smsBtn = document.getElementById('tplPreviewSMSBtn');
    
    if (mode === 'rcs') {
        rcsBtn.classList.add('active');
        smsBtn.classList.remove('active');
        rcsBtn.style.background = '#886CC0';
        rcsBtn.style.color = 'white';
        smsBtn.style.background = 'white';
        smsBtn.style.color = '#886CC0';
    } else {
        smsBtn.classList.add('active');
        rcsBtn.classList.remove('active');
        smsBtn.style.background = '#886CC0';
        smsBtn.style.color = 'white';
        rcsBtn.style.background = 'white';
        rcsBtn.style.color = '#886CC0';
    }
    
    updateTemplatePreview();
}

function toggleTemplateBasicRcsPreview(mode) {
    tplBasicRcsPreviewMode = mode;
    var rcsBtn = document.getElementById('tplBasicPreviewRCSBtn');
    var smsBtn = document.getElementById('tplBasicPreviewSMSBtn');
    
    if (mode === 'rcs') {
        rcsBtn.classList.add('active');
        smsBtn.classList.remove('active');
        rcsBtn.style.background = '#886CC0';
        rcsBtn.style.color = 'white';
        smsBtn.style.background = 'white';
        smsBtn.style.color = '#886CC0';
    } else {
        smsBtn.classList.add('active');
        rcsBtn.classList.remove('active');
        smsBtn.style.background = '#886CC0';
        smsBtn.style.color = 'white';
        rcsBtn.style.background = 'white';
        rcsBtn.style.color = '#886CC0';
    }
    
    updateTemplatePreview();
}

document.addEventListener('DOMContentLoaded', function() {
    setupTemplateChannelListeners();
});

function viewVersionHistory(templateId) {
    var template = mockTemplates.find(function(t) { return t.id === templateId; });
    if (!template) return;
    
    currentVersionHistory.templateId = templateId;
    currentVersionHistory.versions = mockVersionHistory[templateId] || generateDefaultVersionHistory(template);
    currentVersionHistory.auditLog = mockAuditLog[templateId] || generateDefaultAuditLog(template);
    
    document.getElementById('vhTemplateName').textContent = template.name;
    document.getElementById('vhTemplateId').textContent = template.templateId;
    
    renderVersionsTable();
    renderAuditTimeline();
    
    document.getElementById('versions-tab').click();
    
    new bootstrap.Modal(document.getElementById('versionHistoryModal')).show();
}

function generateDefaultVersionHistory(template) {
    return [{
        version: template.version,
        status: template.status,
        content: template.content,
        channel: template.channel,
        trigger: template.trigger,
        changeNote: 'Current version',
        editedBy: 'System',
        editedAt: template.lastUpdated + ' 12:00:00',
        userId: 'system'
    }];
}

function generateDefaultAuditLog(template) {
    return [{
        action: 'created',
        version: 1,
        userId: 'system',
        userName: 'System',
        timestamp: template.lastUpdated + ' 12:00:00',
        details: 'Template created'
    }];
}

function renderVersionsTable() {
    var tbody = document.getElementById('versionsTableBody');
    var template = mockTemplates.find(function(t) { return t.id === currentVersionHistory.templateId; });
    var currentVersion = template ? template.version : 1;
    var isArchived = template && template.status === 'archived';
    
    var html = '';
    
    currentVersionHistory.versions.forEach(function(v) {
        var isCurrent = v.version === currentVersion;
        
        html += '<tr>';
        
        html += '<td class="fw-medium">';
        html += 'v' + v.version;
        if (isCurrent) {
            html += ' <small class="text-muted">(active)</small>';
        }
        html += '</td>';
        
        html += '<td>' + v.editedBy + '</td>';
        html += '<td class="small text-muted">' + formatTimestamp(v.editedAt) + '</td>';
        html += '<td class="small">' + (v.changeNote || '<span class="text-muted fst-italic">No summary</span>') + '</td>';
        
        html += '<td>';
        html += '<span class="badge rounded-pill ' + getStatusBadgeClass(v.status) + '">' + getStatusLabel(v.status) + '</span>';
        html += '</td>';
        
        html += '<td class="text-end">';
        html += '<div class="dropdown">';
        html += '<button class="btn btn-link p-0 text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
        html += '<i class="fas fa-ellipsis-v"></i>';
        html += '</button>';
        html += '<ul class="dropdown-menu dropdown-menu-end">';
        html += '<li><a class="dropdown-item" href="#" onclick="viewVersion(' + v.version + '); return false;"><i class="fas fa-eye me-2"></i>View version</a></li>';
        
        if (!isCurrent && !isArchived) {
            html += '<li><a class="dropdown-item" href="#" onclick="initiateRollback(' + v.version + '); return false;"><i class="fas fa-undo me-2"></i>Roll back to this version</a></li>';
        }
        
        html += '</ul>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html || '<tr><td colspan="6" class="text-center text-muted py-4">No version history available</td></tr>';
}

function renderAuditTimeline() {
    var container = document.getElementById('auditTimeline');
    var html = '';
    
    currentVersionHistory.auditLog.forEach(function(entry) {
        var actionClass = 'action-' + entry.action.replace(/_/g, '-');
        var actionIcon = getAuditActionIcon(entry.action);
        var actionLabel = getAuditActionLabel(entry.action);
        
        html += '<div class="vh-audit-entry ' + actionClass + '">';
        html += '<div class="vh-audit-action">';
        html += '<i class="' + actionIcon + ' me-2"></i>' + actionLabel;
        html += ' <span class="badge bg-light text-dark">v' + entry.version + '</span>';
        html += '</div>';
        html += '<div class="vh-audit-meta">';
        html += '<span class="me-3"><i class="fas fa-user me-1"></i>' + entry.userName + '</span>';
        html += '<span><i class="fas fa-clock me-1"></i>' + formatTimestamp(entry.timestamp) + '</span>';
        html += '</div>';
        if (entry.details) {
            html += '<div class="small text-muted mt-1">' + entry.details + '</div>';
        }
        html += '</div>';
    });
    
    container.innerHTML = html || '<p class="text-muted text-center py-3">No audit entries available</p>';
}

function getAuditActionIcon(action) {
    switch(action) {
        case 'created': return 'fas fa-plus-circle text-success';
        case 'edited': return 'fas fa-edit text-primary';
        case 'launched': return 'fas fa-rocket text-success';
        case 'archived': return 'fas fa-archive text-danger';
        case 'rolled-back': return 'fas fa-undo text-warning';
        case 'duplicated': return 'fas fa-copy text-purple';
        case 'permissions': return 'fas fa-lock text-info';
        default: return 'fas fa-circle';
    }
}

function getAuditActionLabel(action) {
    switch(action) {
        case 'created': return 'Created';
        case 'edited': return 'Edited';
        case 'launched': return 'Launched';
        case 'archived': return 'Archived';
        case 'rolled-back': return 'Rolled Back';
        case 'duplicated': return 'Duplicated';
        case 'permissions': return 'Permissions Changed';
        default: return action.charAt(0).toUpperCase() + action.slice(1);
    }
}

function formatTimestamp(timestamp) {
    if (!timestamp) return '-';
    var date = new Date(timestamp.replace(' ', 'T'));
    if (isNaN(date.getTime())) return timestamp;
    
    var options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-GB', options);
}

function viewVersion(versionNum) {
    var versionData = currentVersionHistory.versions.find(function(v) { return v.version === versionNum; });
    if (!versionData) return;
    
    var template = mockTemplates.find(function(t) { return t.id === currentVersionHistory.templateId; });
    if (!template) return;
    
    currentViewVersion.templateId = currentVersionHistory.templateId;
    currentViewVersion.version = versionNum;
    currentViewVersion.data = versionData;
    
    document.getElementById('vvTemplateName').textContent = template.name;
    document.getElementById('vvVersion').textContent = 'v' + versionNum;
    
    var statusBadge = document.getElementById('vvStatus');
    statusBadge.textContent = getStatusLabel(versionData.status);
    statusBadge.className = 'badge ' + getStatusBadgeClass(versionData.status).replace('badge-', 'bg-');
    
    var channelBadge = document.getElementById('vvChannel');
    channelBadge.textContent = getChannelLabel(versionData.channel);
    channelBadge.className = 'badge ' + getChannelBadgeClass(versionData.channel);
    
    var triggerBadge = document.getElementById('vvTrigger');
    triggerBadge.textContent = getTriggerLabel(versionData.trigger);
    triggerBadge.className = 'badge ' + getTriggerBadgeClass(versionData.trigger);
    
    document.getElementById('vvChangeNote').textContent = versionData.changeNote || 'No change note provided';
    document.getElementById('vvEditedBy').textContent = versionData.editedBy;
    document.getElementById('vvEditedAt').textContent = formatTimestamp(versionData.editedAt);
    
    var contentPreview = document.getElementById('vvContentPreview');
    if (versionData.content) {
        var highlightedContent = versionData.content.replace(/\{(\w+)\}/g, '<span class="placeholder-pill" style="padding: 0.15rem 0.4rem; font-size: 0.75rem;"><i class="fas fa-tag"></i>{$1}</span>');
        contentPreview.innerHTML = '<p class="mb-0">' + highlightedContent + '</p>';
    } else {
        contentPreview.innerHTML = '<p class="text-muted mb-0 fst-italic">Rich RCS content (not displayed in text view)</p>';
    }
    
    var placeholders = extractPlaceholders(versionData.content || '');
    var placeholderContainer = document.getElementById('vvPlaceholders');
    if (placeholders.length > 0) {
        placeholderContainer.innerHTML = placeholders.map(function(p) {
            return '<span class="placeholder-pill"><i class="fas fa-tag"></i>{' + p + '}</span>';
        }).join('');
    } else {
        placeholderContainer.innerHTML = '<span class="text-muted">None</span>';
    }
    
    var isCurrent = versionNum === template.version;
    var isArchived = template.status === 'archived';
    var rollbackBtn = document.getElementById('vvRollbackBtn');
    
    if (isCurrent || isArchived) {
        rollbackBtn.style.display = 'none';
    } else {
        rollbackBtn.style.display = 'inline-block';
    }
    
    new bootstrap.Modal(document.getElementById('viewVersionModal')).show();
}

function extractPlaceholders(content) {
    var matches = content.match(/\{(\w+)\}/g) || [];
    return matches.map(function(m) { return m.replace(/[{}]/g, ''); });
}

function initiateRollback(versionNum) {
    var versionData = currentVersionHistory.versions.find(function(v) { return v.version === versionNum; });
    if (!versionData) return;
    
    var template = mockTemplates.find(function(t) { return t.id === currentVersionHistory.templateId; });
    if (!template) return;
    
    var newVersion = template.version + 1;
    
    rollbackTarget.templateId = currentVersionHistory.templateId;
    rollbackTarget.version = versionNum;
    rollbackTarget.data = versionData;
    rollbackTarget.newVersion = newVersion;
    
    document.getElementById('rbVersionLabel').textContent = 'v' + versionNum;
    document.getElementById('rbSourceVersionLabel').textContent = 'v' + versionNum;
    document.getElementById('rbNewVersionLabel').textContent = 'v' + newVersion;
    document.getElementById('rbSourceVersion').textContent = 'v' + versionNum;
    document.getElementById('rbNewVersion').textContent = 'v' + newVersion;
    document.getElementById('rbChangeNote').value = '';
    document.getElementById('rbSetLive').checked = false;
    
    new bootstrap.Modal(document.getElementById('rollbackConfirmModal')).show();
}

function rollbackFromViewVersion() {
    if (!currentViewVersion.version || !currentViewVersion.data) return;
    
    bootstrap.Modal.getInstance(document.getElementById('viewVersionModal')).hide();
    
    setTimeout(function() {
        initiateRollback(currentViewVersion.version);
    }, 300);
}

function confirmRollback() {
    if (!rollbackTarget.templateId || !rollbackTarget.version || !rollbackTarget.data) return;
    
    var template = mockTemplates.find(function(t) { return t.id === rollbackTarget.templateId; });
    if (!template) return;
    
    var changeNote = document.getElementById('rbChangeNote').value.trim() || 'Rolled back from version ' + rollbackTarget.version;
    var setLive = document.getElementById('rbSetLive').checked;
    
    var newVersion = template.version + 1;
    
    var newVersionEntry = {
        version: newVersion,
        status: setLive ? 'live' : 'draft',
        content: rollbackTarget.data.content,
        channel: rollbackTarget.data.channel,
        trigger: rollbackTarget.data.trigger,
        changeNote: changeNote,
        editedBy: 'Current User',
        editedAt: new Date().toISOString().replace('T', ' ').substring(0, 19),
        userId: 'current'
    };
    
    if (!mockVersionHistory[rollbackTarget.templateId]) {
        mockVersionHistory[rollbackTarget.templateId] = generateDefaultVersionHistory(template);
    }
    mockVersionHistory[rollbackTarget.templateId].unshift(newVersionEntry);
    
    if (setLive) {
        mockVersionHistory[rollbackTarget.templateId].forEach(function(v) {
            if (v.version !== newVersion && v.status === 'live') {
                v.status = 'draft';
            }
        });
    }
    
    var auditEntry = {
        action: 'rolled-back',
        version: newVersion,
        userId: 'current',
        userName: 'Current User',
        timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
        details: 'Rolled back to version ' + rollbackTarget.version + '. ' + changeNote
    };
    
    if (!mockAuditLog[rollbackTarget.templateId]) {
        mockAuditLog[rollbackTarget.templateId] = generateDefaultAuditLog(template);
    }
    mockAuditLog[rollbackTarget.templateId].unshift(auditEntry);
    
    if (setLive) {
        var launchEntry = {
            action: 'launched',
            version: newVersion,
            userId: 'current',
            userName: 'Current User',
            timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
            details: 'Published version ' + newVersion + ' as Live'
        };
        mockAuditLog[rollbackTarget.templateId].unshift(launchEntry);
    }
    
    template.version = newVersion;
    template.content = rollbackTarget.data.content;
    template.channel = rollbackTarget.data.channel;
    
    if (setLive) {
        template.status = 'live';
    } else {
        template.status = 'draft';
    }
    template.lastUpdated = new Date().toISOString().split('T')[0];
    
    var statusBadge = document.getElementById('vhCurrentStatus');
    statusBadge.textContent = getStatusLabel(template.status);
    statusBadge.className = 'badge ' + getStatusBadgeClass(template.status).replace('badge-', 'bg-');
    
    bootstrap.Modal.getInstance(document.getElementById('rollbackConfirmModal')).hide();
    
    currentVersionHistory.versions = mockVersionHistory[rollbackTarget.templateId];
    currentVersionHistory.auditLog = mockAuditLog[rollbackTarget.templateId];
    
    renderVersionsTable();
    renderAuditTimeline();
    
    renderTemplates();
    
    showToast('Rollback complete. Created v' + newVersion + ' from v' + rollbackTarget.version + '.', 'success');
    
    rollbackTarget = { templateId: null, version: null, data: null };
}
</script>
@endpush

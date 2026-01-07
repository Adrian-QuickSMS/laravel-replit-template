@extends('layouts.quicksms')

@section('title', 'Message Templates')

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
    overflow: hidden;
}
.templates-table {
    width: 100%;
    margin: 0;
}
.templates-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.75rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.templates-table thead th:hover {
    background: #e9ecef;
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
    padding: 0.75rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.875rem;
}
.templates-table tbody tr:last-child td {
    border-bottom: none;
}
.templates-table tbody tr:hover {
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
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    color: #28a745;
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
    background-color: #28a745;
    color: white;
}
.wizard-step .step-label {
    font-size: 0.8rem;
    font-weight: 500;
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Create Template</h5>
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
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div id="wizardStep1" class="wizard-content">
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
                
                <div id="wizardStep2" class="wizard-content" style="display: none;">
                    <div class="alert alert-pastel-primary mb-4">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        <strong>Step 2: Message Content</strong> - Create your message content using the same editor as Send Message. You can use personalization tags to customize messages for each recipient.
                    </div>
                    
                    <div class="step2-locked-info mb-3">
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
                    
                    <div class="card mb-3">
                        <div class="card-body p-4">
                            <h6 class="mb-3">Channel</h6>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="templateChannel" id="tplChannelSMS" value="sms" checked>
                                <label class="btn btn-outline-primary" for="tplChannelSMS"><i class="fas fa-sms me-1"></i>SMS only</label>
                                <input type="radio" class="btn-check" name="templateChannel" id="tplChannelRCSBasic" value="basic_rcs">
                                <label class="btn btn-outline-primary" for="tplChannelRCSBasic" data-bs-toggle="tooltip" title="Text-only RCS with SMS fallback"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                                <input type="radio" class="btn-check" name="templateChannel" id="tplChannelRCSRich" value="rich_rcs">
                                <label class="btn btn-outline-primary" for="tplChannelRCSRich" data-bs-toggle="tooltip" title="Rich cards, images & buttons with SMS fallback"><i class="fas fa-image me-1"></i>Rich RCS</label>
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
                                <span class="badge bg-secondary" id="tplPlaceholderCount">0 placeholders</span>
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
                                            <span class="badge bg-warning text-dark me-2">With Placeholders</span>
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
                                            <span class="badge bg-success me-2">Without Placeholders</span>
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
                    
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Template Mode:</strong> This editor is the same as Send Message, but without recipients, pricing, or scheduling options. Templates can be reused across campaigns.
                    </div>
                </div>
                
                <div id="wizardStep3" class="wizard-content" style="display: none;">
                    <div class="alert alert-pastel-primary mb-4">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        <strong>Step 3: Review & Save</strong> - Review your template details before saving.
                    </div>
                    <p class="text-muted">Review step coming in next iteration...</p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-secondary" id="wizardBackBtn" style="display: none;" onclick="wizardBack()">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </button>
                <button type="button" class="btn btn-primary" id="wizardNextBtn" onclick="wizardNext()">
                    Continue<i class="fas fa-arrow-right ms-2"></i>
                </button>
                <button type="button" class="btn btn-primary" id="wizardSaveBtn" style="display: none;" onclick="saveTemplate()">
                    <i class="fas fa-save me-2"></i>Save Template
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
@endsection

@push('scripts')
<script>
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
    content: ''
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
        content: ''
    };
    
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
    
    document.getElementById('wizardBackBtn').style.display = currentWizardStep > 1 ? 'inline-block' : 'none';
    document.getElementById('wizardNextBtn').style.display = currentWizardStep < 3 ? 'inline-block' : 'none';
    document.getElementById('wizardSaveBtn').style.display = currentWizardStep === 3 ? 'inline-block' : 'none';
    
    if (currentWizardStep === 2) {
        document.getElementById('step2TemplateName').textContent = wizardData.name;
        document.getElementById('step2TemplateId').textContent = wizardData.templateId;
        
        var triggerBadge = document.getElementById('step2TriggerBadge');
        triggerBadge.textContent = getTriggerLabel(wizardData.trigger);
        triggerBadge.className = 'badge rounded-pill ' + getTriggerBadgeClass(wizardData.trigger);
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
    
    if (currentWizardStep < 3) {
        currentWizardStep++;
        updateWizardUI();
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

function saveTemplate() {
    var name = wizardData.name || document.getElementById('templateName').value.trim();
    var content = document.getElementById('templateContent').value.trim();
    var channel = document.querySelector('input[name="templateChannel"]:checked').value;
    var trigger = wizardData.trigger || 'portal';
    
    if (!name) {
        alert('Please enter a template name.');
        return;
    }
    
    var template = {
        id: Date.now(),
        templateId: wizardData.templateId,
        name: name,
        channel: channel,
        trigger: trigger,
        content: content,
        contentType: channel === 'rich_rcs' ? 'rich_card' : 'text',
        accessScope: 'All Sub-accounts',
        subAccounts: ['all'],
        status: 'draft',
        version: 1,
        lastUpdated: new Date().toISOString().split('T')[0]
    };
    
    mockTemplates.unshift(template);
    bootstrap.Modal.getInstance(document.getElementById('createTemplateModal')).hide();
    renderTemplates();
    showToast('Template "' + name + '" created successfully', 'success');
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
        html += '<td><span class="version-badge">v' + template.version + '</span></td>';
        html += '<td><span class="badge rounded-pill ' + getChannelBadgeClass(template.channel) + '">' + getChannelLabel(template.channel) + '</span></td>';
        html += '<td><span class="badge rounded-pill ' + getTriggerBadgeClass(template.trigger) + '">' + getTriggerLabel(template.trigger) + '</span></td>';
        html += '<td>' + getContentPreview(template) + '</td>';
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

function managePermissions(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    showToast('Opening permissions for "' + template.name + '"...', 'info');
}

function viewApiStructure(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    showToast('Viewing API structure for "' + template.name + '"...', 'info');
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
    document.getElementById('tplPartCount').textContent = segments;
    
    wizardData.content = content;
    
    detectPlaceholders(content);
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
    countEl.className = 'badge ' + (matches.length > 0 ? 'bg-primary' : 'bg-secondary');
    
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
            return '<span class="badge bg-primary me-2 mb-2 py-2 px-3">{' + ph + '}</span>';
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
    showToast('RCS Wizard opening... (Shared wizard component)', 'info');
}

function setupTemplateChannelListeners() {
    document.querySelectorAll('input[name="templateChannel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var channel = this.value;
            var textEditor = document.getElementById('tplTextEditorContainer');
            var rcsSection = document.getElementById('tplRcsContentSection');
            var contentLabel = document.getElementById('tplContentLabel');
            var rcsHelper = document.getElementById('tplRcsTextHelper');
            
            wizardData.channel = channel;
            
            if (channel === 'rich_rcs') {
                textEditor.classList.add('d-none');
                rcsSection.classList.remove('d-none');
                contentLabel.textContent = 'RCS Content';
            } else {
                textEditor.classList.remove('d-none');
                rcsSection.classList.add('d-none');
                
                if (channel === 'rcs_basic') {
                    contentLabel.textContent = 'Basic RCS Content';
                    rcsHelper.classList.remove('d-none');
                } else {
                    contentLabel.textContent = 'SMS Content';
                    rcsHelper.classList.add('d-none');
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupTemplateChannelListeners();
});
</script>
@endpush

@extends($isAdminMode ? 'layouts.admin' : 'layouts.quicksms')

@php
    $mode = $mode ?? 'create';
    $isEditMode = $mode === 'edit';
    $isAdminMode = $isAdminMode ?? false;
    $theme = $isAdminMode ? 'blue' : 'purple';
    $showRichRcs = $showRichRcs ?? (!$isAdminMode);
    
    $themeColors = [
        'purple' => ['primary' => '#886CC0', 'primaryRgb' => '136, 108, 192', 'headerBg' => 'linear-gradient(135deg, #886CC0 0%, #a78bda 100%)'],
        'blue' => ['primary' => '#1e3a5f', 'primaryRgb' => '30, 58, 95', 'headerBg' => 'linear-gradient(135deg, #1e3a5f 0%, #2d5a8f 100%)']
    ];
    $colors = $themeColors[$theme];
@endphp

@push('styles')
<link rel="stylesheet" href="/css/rcs-preview.css">
<style>
.template-wizard-page {
    min-height: 100vh;
    background: #f5f7fa;
}
.wizard-header {
    background: {{ $colors['headerBg'] }};
    color: #fff;
    padding: 1rem 1.5rem;
    position: sticky;
    top: 0;
    z-index: 100;
}
.wizard-header .wizard-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}
.wizard-steps {
    display: flex;
    gap: 1.5rem;
}
.wizard-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    opacity: 0.6;
    cursor: pointer;
    transition: opacity 0.2s;
}
.wizard-step:hover {
    opacity: 0.8;
}
.wizard-step.active {
    opacity: 1;
}
.wizard-step.completed {
    opacity: 1;
}
.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 600;
}
.wizard-step.active .step-number {
    background: #fff;
    color: {{ $colors['primary'] }};
}
.wizard-step.completed .step-number {
    background: #28a745;
    color: #fff;
}
.step-label {
    font-size: 0.9rem;
}
.wizard-body {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}
.wizard-content {
    display: none;
}
.wizard-content.active {
    display: block;
}
.wizard-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    border-top: 1px solid #e9ecef;
    padding: 1rem 2rem;
    z-index: 99;
}
.wizard-footer-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.tpl-builder-layout {
    display: flex;
    gap: 2rem;
}
.tpl-builder-left {
    flex: 1;
    min-width: 0;
}
.tpl-builder-right {
    width: 380px;
    flex-shrink: 0;
}
.card-header-theme {
    background-color: rgba({{ $colors['primaryRgb'] }}, 0.05);
}
.card-header-theme h6 {
    color: {{ $colors['primary'] }};
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
    transition: all 0.2s;
}
.trigger-option:hover {
    border-color: {{ $colors['primary'] }};
    background: rgba({{ $colors['primaryRgb'] }}, 0.03);
}
.trigger-option.selected {
    border-color: {{ $colors['primary'] }};
    background: rgba({{ $colors['primaryRgb'] }}, 0.05);
}
.trigger-option.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.trigger-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #fff;
}
.trigger-icon.bg-api { background: #886CC0; }
.trigger-icon.bg-portal { background: #28a745; }
.trigger-icon.bg-email { background: #ffc107; color: #212529; }
.channel-btn-group .btn-check:checked + .btn {
    background-color: {{ $colors['primary'] }};
    border-color: {{ $colors['primary'] }};
    color: #fff;
}
.channel-btn-group .btn-outline-primary {
    border-color: #dee2e6;
    color: #495057;
}
.channel-btn-group .btn-outline-primary:hover {
    background-color: rgba({{ $colors['primaryRgb'] }}, 0.1);
    border-color: {{ $colors['primary'] }};
    color: {{ $colors['primary'] }};
}
.review-section {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.review-section h6 {
    color: {{ $colors['primary'] }};
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.review-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}
.review-item-label {
    color: #6c757d;
}
.review-item-value {
    font-weight: 500;
}
.btn-theme-primary {
    background-color: {{ $colors['primary'] }};
    border-color: {{ $colors['primary'] }};
    color: #fff;
}
.btn-theme-primary:hover {
    background-color: {{ $colors['primary'] }};
    border-color: {{ $colors['primary'] }};
    opacity: 0.9;
    color: #fff;
}
.btn-theme-outline {
    border-color: {{ $colors['primary'] }};
    color: {{ $colors['primary'] }};
}
.btn-theme-outline:hover {
    background-color: {{ $colors['primary'] }};
    color: #fff;
}
@if($isAdminMode)
.admin-context-banner {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border: 1px solid #ffc107;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1.5rem;
}
@endif
@media (max-width: 991.98px) {
    .tpl-builder-layout {
        flex-direction: column;
    }
    .tpl-builder-right {
        width: 100%;
    }
    .wizard-steps {
        display: none;
    }
}
</style>
@endpush

@section('content')
<div class="template-wizard-page">
    <div class="wizard-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <h1 class="wizard-title">
                    <i class="fas {{ $isEditMode ? 'fa-edit' : 'fa-plus' }} me-2"></i>
                    {{ $isEditMode ? 'Edit Template' : 'Create Template' }}
                </h1>
                <div class="wizard-steps ms-4">
                    <div class="wizard-step active" data-step="1" onclick="goToStep(1)">
                        <span class="step-number">1</span>
                        <span class="step-label">Metadata</span>
                    </div>
                    <div class="wizard-step" data-step="2" onclick="goToStep(2)">
                        <span class="step-number">2</span>
                        <span class="step-label">Content</span>
                    </div>
                    <div class="wizard-step" data-step="3" onclick="goToStep(3)">
                        <span class="step-number">3</span>
                        <span class="step-label">Review</span>
                    </div>
                </div>
                @if($isAdminMode && isset($accountId))
                <div class="ms-4">
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-user-shield me-1"></i>
                        Admin Editing: <span id="accountName">{{ $accountName ?? 'Account ' . $accountId }}</span>
                    </span>
                </div>
                @endif
            </div>
            <a href="{{ $isAdminMode ? route('admin.management.templates') : route('management.templates') }}" class="btn btn-outline-light btn-sm">
                <i class="fas fa-times me-1"></i>Cancel
            </a>
        </div>
    </div>
    
    <div class="wizard-body" style="padding-bottom: 100px;">
        @if($isAdminMode)
        <div class="admin-context-banner">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                <div>
                    <strong>Admin Edit Mode:</strong> You are editing this template on behalf of <strong id="customerNameDisplay">{{ $accountName ?? 'the customer' }}</strong>. 
                    All changes will be applied to the customer's account and logged in the audit trail.
                </div>
            </div>
        </div>
        @endif
        
        <div class="wizard-content active" data-step="1">
            <div class="card mb-4">
                <div class="card-header card-header-theme">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Template Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="templateName" placeholder="e.g., Welcome Message, Appointment Reminder" maxlength="100">
                            <div class="invalid-feedback">Please enter a template name</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Template ID</label>
                            <input type="text" class="form-control bg-light" id="templateIdField" readonly>
                            <small class="text-muted">Auto-generated, read-only</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trigger Type <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2">Select how this template will be triggered.{{ $isEditMode ? ' Trigger type cannot be changed after creation.' : '' }}</p>
                        
                        <div class="trigger-options">
                            <div class="trigger-option {{ $isEditMode ? 'disabled' : '' }}" data-trigger="api" onclick="{{ $isEditMode ? '' : 'selectTrigger(\'api\')' }}">
                                <div class="d-flex align-items-center">
                                    <div class="trigger-icon bg-api">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <div>
                                        <strong>API</strong>
                                        <p class="mb-0 small text-muted">Template is called via API only. Assign to specific sub-accounts for access control.</p>
                                    </div>
                                    <div class="ms-auto">
                                        <input type="radio" name="templateTrigger" value="api" class="form-check-input" {{ $isEditMode ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="trigger-option {{ $isEditMode ? 'disabled' : '' }}" data-trigger="portal" onclick="{{ $isEditMode ? '' : 'selectTrigger(\'portal\')' }}">
                                <div class="d-flex align-items-center">
                                    <div class="trigger-icon bg-portal">
                                        <i class="fas fa-desktop"></i>
                                    </div>
                                    <div>
                                        <strong>Portal</strong>
                                        <p class="mb-0 small text-muted">Template is visible and selectable when sending messages through the QuickSMS portal.</p>
                                    </div>
                                    <div class="ms-auto">
                                        <input type="radio" name="templateTrigger" value="portal" class="form-check-input" {{ $isEditMode ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="trigger-option {{ $isEditMode ? 'disabled' : '' }}" data-trigger="email" onclick="{{ $isEditMode ? '' : 'selectTrigger(\'email\')' }}">
                                <div class="d-flex align-items-center">
                                    <div class="trigger-icon bg-email">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <strong>Email-to-SMS</strong>
                                        <p class="mb-0 small text-muted">Template is visible only in Email-to-SMS configuration for automated email conversion.</p>
                                    </div>
                                    <div class="ms-auto">
                                        <input type="radio" name="templateTrigger" value="email" class="form-check-input" {{ $isEditMode ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="triggerError" style="display: none;">Please select a trigger type</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="wizard-content" data-step="2">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <div>
                            <small class="text-muted d-block">Template Name</small>
                            <div class="fw-semibold" id="step2TemplateName">-</div>
                        </div>
                        <div class="vr d-none d-md-block"></div>
                        <div>
                            <small class="text-muted d-block">Template ID</small>
                            <div class="fw-semibold" id="step2TemplateId">-</div>
                        </div>
                        <div class="vr d-none d-md-block"></div>
                        <div>
                            <small class="text-muted d-block">Trigger</small>
                            <span class="badge rounded-pill bg-secondary" id="step2TriggerBadge">-</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tpl-builder-layout">
                <div class="tpl-builder-left">
                    <div class="card mb-3">
                        <div class="card-header card-header-theme">
                            <h6 class="mb-0"><i class="fas fa-broadcast-tower me-2"></i>Channel & Sender</h6>
                        </div>
                        <div class="card-body">
                            <div class="btn-group channel-btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="templateChannel" id="channelSMS" value="sms" checked>
                                <label class="btn btn-outline-primary" for="channelSMS"><i class="fas fa-sms me-1"></i>SMS only</label>
                                <input type="radio" class="btn-check" name="templateChannel" id="channelRCSBasic" value="basic_rcs">
                                <label class="btn btn-outline-primary" for="channelRCSBasic"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                                @if($showRichRcs)
                                <input type="radio" class="btn-check" name="templateChannel" id="channelRCSRich" value="rich_rcs">
                                <label class="btn btn-outline-primary" for="channelRCSRich"><i class="fas fa-image me-1"></i>Rich RCS</label>
                                @else
                                <input type="radio" class="btn-check" name="templateChannel" id="channelRCSRich" value="rich_rcs" disabled>
                                <label class="btn btn-outline-primary" for="channelRCSRich" title="Rich RCS editing requires the customer portal" style="opacity: 0.5;"><i class="fas fa-image me-1"></i>Rich RCS</label>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-6" id="senderIdSection">
                                    <label class="form-label">SMS Sender ID <span class="text-danger">*</span></label>
                                    <select class="form-select" id="senderId" onchange="updatePreview()">
                                        <option value="">Select Sender ID</option>
                                        @foreach($sender_ids as $sender)
                                        <option value="{{ $sender['id'] }}">{{ $sender['name'] }} ({{ $sender['type'] }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 d-none" id="rcsAgentSection">
                                    <label class="form-label">RCS Agent <span class="text-danger">*</span></label>
                                    <select class="form-select" id="rcsAgent" onchange="updatePreview()">
                                        <option value="">Select RCS Agent</option>
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
                        <div class="card-header card-header-theme">
                            <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Message Content</h6>
                        </div>
                        <div class="card-body">
                            <div id="smsContentSection">
                                <label class="form-label" id="contentLabel">SMS Content <span class="text-danger">*</span></label>
                                <div class="position-relative border rounded mb-2">
                                    <textarea class="form-control border-0" id="templateContent" rows="6" placeholder="Type your message here..." oninput="handleContentChange()" style="padding-bottom: 45px;"></textarea>
                                    <div class="position-absolute d-flex gap-2" style="bottom: 10px; right: 12px;">
                                        <button type="button" class="btn btn-sm btn-light border" onclick="openPersonalisation()" title="Insert personalisation">
                                            <i class="fas fa-user-tag"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border" onclick="openEmojiPicker()" title="Insert emoji">
                                            <i class="fas fa-smile"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="text-muted me-3">Characters: <strong id="charCount">0</strong></span>
                                        <span class="text-muted me-3">Encoding: <strong id="encodingType">GSM-7</strong></span>
                                        <span class="text-muted">Segments: <strong id="partCount">1</strong></span>
                                    </div>
                                    <span class="badge bg-warning text-dark d-none" id="unicodeWarning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Unicode detected
                                    </span>
                                </div>
                            </div>
                            
                            @if($showRichRcs)
                            <div id="richRcsSection" class="d-none">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Use the RCS Wizard to configure rich content including images, cards, and interactive buttons.
                                </div>
                                <button type="button" class="btn btn-theme-primary" onclick="openRcsWizard()">
                                    <i class="fas fa-magic me-1"></i>Open RCS Wizard
                                </button>
                                <div class="border rounded p-3 bg-light mt-3 d-none" id="rcsConfigured">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Rich RCS content configured
                                </div>
                            </div>
                            @endif
                            
                            <div class="border-top pt-3 mt-3">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="includeTrackableLink" onchange="toggleTrackableLink()">
                                            <label class="form-check-label" for="includeTrackableLink">Include trackable link</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="includeOptOut" onchange="toggleOptOut()">
                                            <label class="form-check-label" for="includeOptOut">Include opt-out link</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tpl-builder-right">
                    <div class="card" style="position: sticky; top: 80px;">
                        <div class="card-header card-header-theme">
                            <h6 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>Preview</h6>
                        </div>
                        <div class="card-body text-center">
                            <div id="previewContainer">
                                <div class="rcs-phone-preview">
                                    <div class="phone-frame">
                                        <div class="phone-header">
                                            <span class="sender-name" id="previewSender">Sender</span>
                                        </div>
                                        <div class="phone-body">
                                            <div class="message-bubble">
                                                <span id="previewContent">Your message will appear here...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="wizard-content" data-step="3">
            <div class="card mb-4">
                <div class="card-header card-header-theme">
                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Review & Save</h6>
                </div>
                <div class="card-body">
                    @if($isAdminMode)
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Admin Action:</strong> Saving will {{ $isEditMode ? 'update' : 'create' }} the customer's template. This action will be logged in the audit trail.
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="review-section">
                                <h6><i class="fas fa-info-circle me-2"></i>Template Details</h6>
                                <div class="review-item">
                                    <span class="review-item-label">Template Name</span>
                                    <span class="review-item-value" id="reviewName">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-item-label">Template ID</span>
                                    <span class="review-item-value" id="reviewId">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-item-label">Trigger Type</span>
                                    <span class="review-item-value" id="reviewTrigger">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-item-label">Channel</span>
                                    <span class="review-item-value" id="reviewChannel">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="review-section">
                                <h6><i class="fas fa-cog me-2"></i>Configuration</h6>
                                <div class="review-item">
                                    <span class="review-item-label">Sender</span>
                                    <span class="review-item-value" id="reviewSender">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-item-label">Trackable Link</span>
                                    <span class="review-item-value" id="reviewTrackable">No</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-item-label">Opt-out Link</span>
                                    <span class="review-item-value" id="reviewOptOut">No</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="review-section">
                        <h6><i class="fas fa-comment-alt me-2"></i>Message Preview</h6>
                        <div class="border rounded p-3 bg-light">
                            <pre class="mb-0" id="reviewContent" style="white-space: pre-wrap; font-family: inherit;">-</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="wizard-footer">
        <div class="wizard-footer-inner">
            <button type="button" class="btn btn-outline-secondary" id="btnBack" onclick="previousStep()" style="display: none;">
                <i class="fas fa-arrow-left me-1"></i>Back
            </button>
            <div></div>
            <div class="d-flex gap-2">
                <a href="{{ $isAdminMode ? route('admin.management.templates') : route('management.templates') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="button" class="btn btn-theme-primary" id="btnNext" onclick="nextStep()">
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-theme-primary d-none" id="btnSave" onclick="saveTemplate()">
                    <i class="fas fa-save me-1"></i>{{ $isEditMode ? 'Save Changes' : 'Create Template' }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="personalisationModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Insert Personalisation</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action" onclick="insertPlaceholder('FirstName')">{FirstName}</button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="insertPlaceholder('LastName')">{LastName}</button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="insertPlaceholder('Company')">{Company}</button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="insertPlaceholder('Mobile')">{Mobile}</button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="insertPlaceholder('Custom1')">{Custom1}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="/js/rcs-preview-renderer.js"></script>
<script>
var currentStep = 1;
var totalSteps = 3;
var isEditMode = {{ $isEditMode ? 'true' : 'false' }};
var isAdminMode = {{ $isAdminMode ? 'true' : 'false' }};
var templateId = '{{ $templateId ?? '' }}';
var accountId = '{{ $accountId ?? '' }}';
var returnUrl = '{{ $isAdminMode ? route("admin.management.templates") : route("management.templates") }}';

var wizardData = {
    name: '',
    templateId: '',
    trigger: '',
    channel: 'sms',
    content: '',
    senderId: '',
    rcsAgent: '',
    trackableLink: false,
    optOut: false,
    rcsPayload: null
};

document.addEventListener('DOMContentLoaded', function() {
    if (isEditMode && templateId) {
        loadTemplateData();
    } else {
        wizardData.templateId = generateTemplateId();
        document.getElementById('templateIdField').value = wizardData.templateId;
    }
    
    setupChannelListeners();
    updateStepIndicators();
});

function generateTemplateId() {
    return 'TPL-' + Math.random().toString(36).substr(2, 8).toUpperCase();
}

function loadTemplateData() {
    @if($isEditMode && isset($template))
    var templateData = @json($template);
    
    wizardData.name = templateData.name || '';
    wizardData.templateId = templateData.templateId || templateData.template_id || '';
    wizardData.trigger = templateData.trigger || 'api';
    wizardData.channel = templateData.channel || 'sms';
    wizardData.content = templateData.content || '';
    wizardData.senderId = templateData.senderId || templateData.sender_id || '';
    wizardData.rcsAgent = templateData.rcsAgent || templateData.rcs_agent || '';
    wizardData.trackableLink = templateData.trackableLink || false;
    wizardData.optOut = templateData.optOut || false;
    wizardData.rcsPayload = templateData.rcsPayload || null;
    
    document.getElementById('templateName').value = wizardData.name;
    document.getElementById('templateIdField').value = wizardData.templateId;
    
    var triggerRadio = document.querySelector('input[name="templateTrigger"][value="' + wizardData.trigger + '"]');
    if (triggerRadio) {
        triggerRadio.checked = true;
        document.querySelector('.trigger-option[data-trigger="' + wizardData.trigger + '"]').classList.add('selected');
    }
    
    var channelRadio = document.querySelector('input[name="templateChannel"][value="' + wizardData.channel + '"]');
    if (channelRadio) channelRadio.checked = true;
    
    document.getElementById('templateContent').value = wizardData.content;
    document.getElementById('senderId').value = wizardData.senderId;
    if (document.getElementById('rcsAgent')) {
        document.getElementById('rcsAgent').value = wizardData.rcsAgent;
    }
    document.getElementById('includeTrackableLink').checked = wizardData.trackableLink;
    document.getElementById('includeOptOut').checked = wizardData.optOut;
    
    handleContentChange();
    updateChannelUI(wizardData.channel);
    @endif
}

function setupChannelListeners() {
    document.querySelectorAll('input[name="templateChannel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            wizardData.channel = this.value;
            updateChannelUI(this.value);
        });
    });
}

function updateChannelUI(channel) {
    var senderIdSection = document.getElementById('senderIdSection');
    var rcsAgentSection = document.getElementById('rcsAgentSection');
    var smsContentSection = document.getElementById('smsContentSection');
    var richRcsSection = document.getElementById('richRcsSection');
    var contentLabel = document.getElementById('contentLabel');
    
    if (channel === 'sms') {
        senderIdSection.classList.remove('d-none');
        rcsAgentSection.classList.add('d-none');
        if (smsContentSection) smsContentSection.classList.remove('d-none');
        if (richRcsSection) richRcsSection.classList.add('d-none');
        contentLabel.textContent = 'SMS Content';
    } else if (channel === 'basic_rcs') {
        senderIdSection.classList.remove('d-none');
        rcsAgentSection.classList.remove('d-none');
        if (smsContentSection) smsContentSection.classList.remove('d-none');
        if (richRcsSection) richRcsSection.classList.add('d-none');
        contentLabel.textContent = 'RCS Text Content (with SMS fallback)';
    } else if (channel === 'rich_rcs') {
        senderIdSection.classList.remove('d-none');
        rcsAgentSection.classList.remove('d-none');
        if (smsContentSection) smsContentSection.classList.add('d-none');
        if (richRcsSection) richRcsSection.classList.remove('d-none');
    }
    
    updatePreview();
}

function selectTrigger(trigger) {
    if (isEditMode) return;
    
    document.querySelectorAll('.trigger-option').forEach(function(opt) {
        opt.classList.remove('selected');
    });
    document.querySelector('.trigger-option[data-trigger="' + trigger + '"]').classList.add('selected');
    document.querySelector('input[name="templateTrigger"][value="' + trigger + '"]').checked = true;
    wizardData.trigger = trigger;
}

function goToStep(step) {
    if (step > currentStep && !validateCurrentStep()) return;
    if (step <= 0 || step > totalSteps) return;
    
    currentStep = step;
    showStep(step);
}

function nextStep() {
    if (!validateCurrentStep()) return;
    if (currentStep < totalSteps) {
        currentStep++;
        showStep(currentStep);
    }
}

function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
}

function showStep(step) {
    document.querySelectorAll('.wizard-content').forEach(function(content) {
        content.classList.remove('active');
    });
    document.querySelector('.wizard-content[data-step="' + step + '"]').classList.add('active');
    
    updateStepIndicators();
    
    document.getElementById('btnBack').style.display = step > 1 ? 'block' : 'none';
    document.getElementById('btnNext').classList.toggle('d-none', step === totalSteps);
    document.getElementById('btnSave').classList.toggle('d-none', step !== totalSteps);
    
    if (step === 2) {
        collectStep1Data();
        document.getElementById('step2TemplateName').textContent = wizardData.name;
        document.getElementById('step2TemplateId').textContent = wizardData.templateId;
        document.getElementById('step2TriggerBadge').textContent = getTriggerLabel(wizardData.trigger);
    }
    
    if (step === 3) {
        collectStep2Data();
        updateReviewSection();
    }
    
    window.scrollTo(0, 0);
}

function updateStepIndicators() {
    document.querySelectorAll('.wizard-step').forEach(function(stepEl) {
        var stepNum = parseInt(stepEl.dataset.step);
        stepEl.classList.remove('active', 'completed');
        
        if (stepNum === currentStep) {
            stepEl.classList.add('active');
        } else if (stepNum < currentStep) {
            stepEl.classList.add('completed');
            stepEl.querySelector('.step-number').innerHTML = '<i class="fas fa-check"></i>';
        } else {
            stepEl.querySelector('.step-number').textContent = stepNum;
        }
    });
}

function validateCurrentStep() {
    if (currentStep === 1) {
        var name = document.getElementById('templateName').value.trim();
        var trigger = document.querySelector('input[name="templateTrigger"]:checked');
        
        var valid = true;
        
        if (!name) {
            document.getElementById('templateName').classList.add('is-invalid');
            valid = false;
        } else {
            document.getElementById('templateName').classList.remove('is-invalid');
        }
        
        if (!isEditMode && !trigger) {
            document.getElementById('triggerError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('triggerError').style.display = 'none';
        }
        
        return valid;
    }
    
    if (currentStep === 2) {
        var channel = wizardData.channel;
        var content = document.getElementById('templateContent').value.trim();
        var senderId = document.getElementById('senderId').value;
        
        var valid = true;
        
        if (channel !== 'rich_rcs' && !content) {
            document.getElementById('templateContent').classList.add('is-invalid');
            valid = false;
        } else {
            document.getElementById('templateContent').classList.remove('is-invalid');
        }
        
        if (!senderId) {
            document.getElementById('senderId').classList.add('is-invalid');
            valid = false;
        } else {
            document.getElementById('senderId').classList.remove('is-invalid');
        }
        
        if ((channel === 'basic_rcs' || channel === 'rich_rcs') && document.getElementById('rcsAgent')) {
            var rcsAgent = document.getElementById('rcsAgent').value;
            if (!rcsAgent) {
                document.getElementById('rcsAgent').classList.add('is-invalid');
                valid = false;
            } else {
                document.getElementById('rcsAgent').classList.remove('is-invalid');
            }
        }
        
        return valid;
    }
    
    return true;
}

function collectStep1Data() {
    wizardData.name = document.getElementById('templateName').value.trim();
    wizardData.templateId = document.getElementById('templateIdField').value;
    var trigger = document.querySelector('input[name="templateTrigger"]:checked');
    if (trigger) wizardData.trigger = trigger.value;
}

function collectStep2Data() {
    wizardData.content = document.getElementById('templateContent').value;
    wizardData.senderId = document.getElementById('senderId').value;
    var channelRadio = document.querySelector('input[name="templateChannel"]:checked');
    if (channelRadio) wizardData.channel = channelRadio.value;
    if (document.getElementById('rcsAgent')) {
        wizardData.rcsAgent = document.getElementById('rcsAgent').value;
    }
    wizardData.trackableLink = document.getElementById('includeTrackableLink').checked;
    wizardData.optOut = document.getElementById('includeOptOut').checked;
}

function updateReviewSection() {
    document.getElementById('reviewName').textContent = wizardData.name;
    document.getElementById('reviewId').textContent = wizardData.templateId;
    document.getElementById('reviewTrigger').textContent = getTriggerLabel(wizardData.trigger);
    document.getElementById('reviewChannel').textContent = getChannelLabel(wizardData.channel);
    
    var senderSelect = document.getElementById('senderId');
    var senderText = senderSelect.options[senderSelect.selectedIndex]?.text || '-';
    document.getElementById('reviewSender').textContent = senderText;
    
    document.getElementById('reviewTrackable').textContent = wizardData.trackableLink ? 'Yes' : 'No';
    document.getElementById('reviewOptOut').textContent = wizardData.optOut ? 'Yes' : 'No';
    document.getElementById('reviewContent').textContent = wizardData.content || '(No content)';
}

function getTriggerLabel(trigger) {
    var labels = { api: 'API', portal: 'Portal', email: 'Email-to-SMS' };
    return labels[trigger] || trigger;
}

function getChannelLabel(channel) {
    var labels = { sms: 'SMS', basic_rcs: 'Basic RCS', rich_rcs: 'Rich RCS' };
    return labels[channel] || channel;
}

function handleContentChange() {
    var content = document.getElementById('templateContent').value;
    var charCount = content.length;
    var isUnicode = /[^\x00-\x7F]/.test(content);
    var maxChars = isUnicode ? 70 : 160;
    var parts = Math.ceil(charCount / maxChars) || 1;
    
    document.getElementById('charCount').textContent = charCount;
    document.getElementById('partCount').textContent = parts;
    document.getElementById('encodingType').textContent = isUnicode ? 'Unicode' : 'GSM-7';
    
    var unicodeWarning = document.getElementById('unicodeWarning');
    if (unicodeWarning) {
        unicodeWarning.classList.toggle('d-none', !isUnicode);
    }
    
    updatePreview();
}

function updatePreview() {
    var content = document.getElementById('templateContent').value || 'Your message will appear here...';
    var senderSelect = document.getElementById('senderId');
    var sender = senderSelect.options[senderSelect.selectedIndex]?.text || 'Sender';
    
    document.getElementById('previewContent').textContent = content;
    document.getElementById('previewSender').textContent = sender.split(' ')[0];
}

function openPersonalisation() {
    new bootstrap.Modal(document.getElementById('personalisationModal')).show();
}

function insertPlaceholder(tag) {
    var textarea = document.getElementById('templateContent');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    var placeholder = '{' + tag + '}';
    
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
    
    bootstrap.Modal.getInstance(document.getElementById('personalisationModal')).hide();
    handleContentChange();
}

function openEmojiPicker() {
    alert('Emoji picker would open here');
}

function toggleTrackableLink() {
    wizardData.trackableLink = document.getElementById('includeTrackableLink').checked;
}

function toggleOptOut() {
    wizardData.optOut = document.getElementById('includeOptOut').checked;
}

function openRcsWizard() {
    alert('RCS Wizard would open here');
}

function saveTemplate() {
    collectStep2Data();
    
    var payload = {
        name: wizardData.name,
        templateId: wizardData.templateId,
        trigger: wizardData.trigger,
        channel: wizardData.channel,
        content: wizardData.content,
        senderId: wizardData.senderId,
        rcsAgent: wizardData.rcsAgent,
        trackableLink: wizardData.trackableLink,
        optOut: wizardData.optOut,
        rcsPayload: wizardData.rcsPayload
    };
    
    var btnSave = document.getElementById('btnSave');
    btnSave.disabled = true;
    btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    
    setTimeout(function() {
        showToast('success', isEditMode ? 'Template updated successfully!' : 'Template created successfully!');
        setTimeout(function() {
            window.location.href = returnUrl;
        }, 1000);
    }, 1500);
}

function showToast(type, message) {
    var toastHtml = '<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">' +
        '<div class="toast show" role="alert">' +
        '<div class="toast-header bg-' + (type === 'success' ? 'success' : 'danger') + ' text-white">' +
        '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' +
        '<strong class="me-auto">' + (type === 'success' ? 'Success' : 'Error') + '</strong>' +
        '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>' +
        '</div>' +
        '<div class="toast-body">' + message + '</div>' +
        '</div></div>';
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    setTimeout(function() {
        var container = document.querySelector('.toast-container');
        if (container) container.remove();
    }, 3000);
}
</script>
@endpush

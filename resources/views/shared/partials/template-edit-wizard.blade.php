{{--
    Shared Template Edit Wizard Component
    
    Used by:
    - Customer Portal: Management > Templates (Edit via modal)
    - Admin Control Plane: Management > Global Templates Library (Cross-tenant edit)
    
    Required variables:
    - $wizardMode: 'customer' or 'admin' (default: 'customer')
    - $sender_ids: array of SMS sender IDs
    - $rcs_agents: array of RCS agents
    
    Optional variables:
    - $showRichRcs: boolean - whether to show Rich RCS option (default: true for customer, false for admin)
    - $theme: 'purple' (customer) or 'blue' (admin) - affects accent colors
--}}
@php
    $wizardMode = $wizardMode ?? 'customer';
    $isAdminMode = $wizardMode === 'admin';
    $showRichRcs = $showRichRcs ?? (!$isAdminMode);
    $theme = $theme ?? ($isAdminMode ? 'blue' : 'purple');
    
    $themeColors = [
        'purple' => ['primary' => '#886CC0', 'headerBg' => '#886CC0', 'headerText' => '#fff'],
        'blue' => ['primary' => '#1e3a5f', 'headerBg' => '#1e3a5f', 'headerText' => '#fff']
    ];
    $colors = $themeColors[$theme] ?? $themeColors['purple'];
@endphp

<style>
.template-wizard-modal .wizard-steps {
    display: flex;
    gap: 1rem;
}
.template-wizard-modal .wizard-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    opacity: 0.6;
}
.template-wizard-modal .wizard-step.active {
    opacity: 1;
}
.template-wizard-modal .wizard-step.done {
    opacity: 1;
}
.template-wizard-modal .step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 600;
}
.template-wizard-modal .wizard-step.active .step-number {
    background: #fff;
    color: {{ $colors['primary'] }};
}
.template-wizard-modal .wizard-step.done .step-number {
    background: #28a745;
    color: #fff;
}
.template-wizard-modal .step-label {
    font-size: 0.85rem;
}
.template-wizard-modal .tpl-builder-layout {
    display: flex;
    min-height: calc(100vh - 160px);
}
.template-wizard-modal .tpl-builder-left {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
}
.template-wizard-modal .tpl-builder-right {
    width: 380px;
    background: #fff;
    border-left: 1px solid #e9ecef;
    padding: 1.5rem;
    position: sticky;
    top: 0;
    align-self: flex-start;
    max-height: calc(100vh - 160px);
    overflow-y: auto;
}
.template-wizard-modal .loading-overlay,
.template-wizard-modal .error-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}
.template-wizard-modal .channel-option {
    flex: 1;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.template-wizard-modal .channel-option:hover {
    border-color: {{ $colors['primary'] }};
    background: rgba({{ $isAdminMode ? '30, 58, 95' : '136, 108, 192' }}, 0.05);
}
.template-wizard-modal .channel-option.selected {
    border-color: {{ $colors['primary'] }};
    background: rgba({{ $isAdminMode ? '30, 58, 95' : '136, 108, 192' }}, 0.1);
}
.template-wizard-modal .placeholder-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 3px;
}
.template-wizard-modal .placeholder-btn:hover {
    background-color: {{ $colors['primary'] }} !important;
    color: #fff !important;
}
@media (max-width: 991.98px) {
    .template-wizard-modal .tpl-builder-layout {
        flex-direction: column;
    }
    .template-wizard-modal .tpl-builder-right {
        width: 100%;
        max-height: none;
        position: relative;
    }
}
</style>

<div class="modal-header py-3 flex-shrink-0" style="background-color: {{ $colors['headerBg'] }}; color: {{ $colors['headerText'] }};">
    <div class="d-flex align-items-center">
        <h5 class="modal-title mb-0" id="wizardModalTitle">
            <i class="fas fa-edit me-2"></i>Edit Template
        </h5>
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
        @if($isAdminMode)
        <div class="ms-3 d-flex align-items-center">
            <span class="badge bg-light text-dark me-2" id="wizardAccountBadge">-</span>
            <small class="text-white-50">(Tenant Context)</small>
        </div>
        @endif
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body p-0 flex-grow-1 overflow-auto position-relative" style="background: #f5f7fa;">
    <div class="loading-overlay d-none" id="wizardLoadingOverlay">
        <div class="text-center">
            <div class="spinner-border mb-3" style="color: {{ $colors['primary'] }};" role="status"></div>
            <p class="mb-0">Loading template...</p>
        </div>
    </div>
    <div class="error-overlay d-none" id="wizardErrorOverlay">
        <div class="text-center">
            <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
            <h5>Failed to Load Template</h5>
            <p class="text-muted" id="wizardErrorMessage">An error occurred.</p>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
    
    <div class="tpl-builder-layout">
        <div class="tpl-builder-left">
            {{-- Step 1: Metadata --}}
            <div class="wizard-step-content" data-step="1">
                <div class="card mb-4">
                    <div class="card-header" style="background-color: rgba({{ $isAdminMode ? '30, 58, 95' : '136, 108, 192' }}, 0.05);">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2" style="color: {{ $colors['primary'] }};"></i>
                            Template Information
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($isAdminMode)
                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-user-shield me-2"></i>
                            <strong>Admin Edit Mode:</strong> You are editing this template on behalf of <strong id="wizardCustomerName">-</strong>. All changes will be applied to the customer's template.
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="wizardTemplateName" placeholder="e.g., Order Confirmation">
                                <div class="invalid-feedback">Please enter a template name</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Template ID</label>
                                <input type="text" class="form-control" id="wizardTemplateIdField" readonly style="background-color: #e9ecef;">
                                <small class="text-muted">Auto-generated, cannot be changed</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trigger Type</label>
                            <div class="d-flex gap-3">
                                <div class="border rounded p-3 text-center" id="wizardTriggerDisplay" style="min-width: 100px;">
                                    <i class="fas fa-code fa-2x mb-2" style="color: {{ $colors['primary'] }};"></i>
                                    <div class="fw-medium">API</div>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">Trigger type cannot be changed after template creation</small>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Step 2: Content --}}
            <div class="wizard-step-content d-none" data-step="2">
                <div class="card mb-4">
                    <div class="card-header" style="background-color: rgba({{ $isAdminMode ? '30, 58, 95' : '136, 108, 192' }}, 0.05);">
                        <h6 class="mb-0">
                            <i class="fas fa-comment-alt me-2" style="color: {{ $colors['primary'] }};"></i>
                            Message Content
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Channel</label>
                            <div class="d-flex gap-3 mb-3">
                                <div class="channel-option" data-channel="sms" onclick="selectWizardChannel('sms')">
                                    <i class="fas fa-sms fa-lg mb-2" style="color: {{ $colors['primary'] }};"></i>
                                    <div class="fw-medium">SMS</div>
                                    <small class="text-muted">Text only</small>
                                </div>
                                <div class="channel-option" data-channel="basic_rcs" onclick="selectWizardChannel('basic_rcs')">
                                    <i class="fas fa-comment-dots fa-lg mb-2" style="color: {{ $colors['primary'] }};"></i>
                                    <div class="fw-medium">Basic RCS</div>
                                    <small class="text-muted">Text with fallback</small>
                                </div>
                                @if($showRichRcs)
                                <div class="channel-option" data-channel="rich_rcs" onclick="selectWizardChannel('rich_rcs')">
                                    <i class="fas fa-image fa-lg mb-2" style="color: {{ $colors['primary'] }};"></i>
                                    <div class="fw-medium">Rich RCS</div>
                                    <small class="text-muted">Cards & buttons</small>
                                </div>
                                @else
                                <div class="channel-option disabled" style="opacity: 0.5; cursor: not-allowed;" title="Rich RCS editing requires the customer portal">
                                    <i class="fas fa-image fa-lg mb-2 text-muted"></i>
                                    <div class="fw-medium text-muted">Rich RCS</div>
                                    <small class="text-muted">Use customer portal</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div id="wizardSmsContentSection">
                            <div class="mb-3" id="wizardSenderIdSection">
                                <label class="form-label">SMS Sender ID</label>
                                <select class="form-select" id="wizardSenderId">
                                    <option value="">Select Sender ID</option>
                                    @if(isset($sender_ids))
                                    @foreach($sender_ids as $sender)
                                    <option value="{{ $sender['id'] }}">{{ $sender['name'] }} ({{ $sender['type'] }})</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            
                            <div class="mb-3 d-none" id="wizardRcsAgentSection">
                                <label class="form-label">RCS Agent</label>
                                <select class="form-select" id="wizardRcsAgent">
                                    <option value="">Select RCS Agent</option>
                                    @if(isset($rcs_agents))
                                    @foreach($rcs_agents as $agent)
                                    <option value="{{ $agent['id'] }}" 
                                        data-name="{{ $agent['name'] }}"
                                        data-logo="{{ $agent['logo'] ?? '' }}">{{ $agent['name'] }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" id="wizardContentLabel">Message Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="wizardTemplateContent" rows="8" placeholder="Enter your message content..." oninput="updateWizardCharCount()"></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <div>
                                        <span class="small text-muted">Characters: <span id="wizardCharCount">0</span></span>
                                        <span class="small text-muted ms-3">Parts: <span id="wizardPartCount">1</span></span>
                                        <span class="small text-muted ms-3">Encoding: <span id="wizardEncodingType">GSM-7</span></span>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary placeholder-btn" onclick="insertWizardPlaceholder('FirstName')">
                                            <i class="fas fa-tag me-1"></i>{FirstName}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary placeholder-btn" onclick="insertWizardPlaceholder('LastName')">
                                            {LastName}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary placeholder-btn" onclick="insertWizardPlaceholder('Company')">
                                            {Company}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($showRichRcs)
                        <div id="wizardRcsContentSection" class="d-none">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Use the RCS Wizard to configure rich content including images, cards, and interactive buttons.
                            </div>
                            <button type="button" class="btn btn-primary" onclick="openWizardRcsBuilder()">
                                <i class="fas fa-magic me-1"></i>Open RCS Wizard
                            </button>
                            <div class="border rounded p-3 bg-light mt-3 d-none" id="wizardRcsPreview">
                                <p class="text-muted mb-0 text-center">Rich RCS content configured</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Step 3: Review --}}
            <div class="wizard-step-content d-none" data-step="3">
                <div class="card mb-4">
                    <div class="card-header" style="background-color: rgba({{ $isAdminMode ? '30, 58, 95' : '136, 108, 192' }}, 0.05);">
                        <h6 class="mb-0">
                            <i class="fas fa-check-circle me-2" style="color: {{ $colors['primary'] }};"></i>
                            Review & Save
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($isAdminMode)
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Admin Action:</strong> Saving will update the customer's template. This action will be logged in the audit trail.
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted small mb-2">TEMPLATE DETAILS</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Name:</td>
                                        <td id="wizardReviewName">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Template ID:</td>
                                        <td id="wizardReviewTemplateId" class="font-monospace">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Channel:</td>
                                        <td id="wizardReviewChannel">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Trigger:</td>
                                        <td id="wizardReviewTrigger">-</td>
                                    </tr>
                                    @if($isAdminMode)
                                    <tr>
                                        <td class="text-muted">Account:</td>
                                        <td id="wizardReviewAccount">-</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted small mb-2">CONTENT PREVIEW</h6>
                                <div class="border rounded p-3 bg-light" id="wizardReviewContentPreview" style="min-height: 100px; white-space: pre-wrap;">
                                    -
                                </div>
                            </div>
                        </div>
                        
                        @if($isAdminMode)
                        <div class="mb-3 mt-4">
                            <label class="form-label">Change Note (Optional)</label>
                            <textarea class="form-control" id="wizardChangeNote" rows="2" placeholder="Describe the changes made..."></textarea>
                            <small class="text-muted">This will be recorded in the version history</small>
                        </div>
                        @endif
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="wizardSetLive">
                            <label class="form-check-label" for="wizardSetLive">
                                Publish as Live immediately
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Right Panel: Preview --}}
        <div class="tpl-builder-right">
            <h6 class="mb-3">
                <i class="fas fa-mobile-alt me-2"></i>Message Preview
            </h6>
            <div id="wizardPreviewContainer" class="d-flex justify-content-center" style="transform: scale(0.8); transform-origin: top center;">
                {{-- Preview will be rendered here --}}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer flex-shrink-0">
    <button type="button" class="btn btn-outline-secondary" id="wizardBackBtn" onclick="wizardPrevStep()" style="display: none;">
        <i class="fas fa-arrow-left me-1"></i>Back
    </button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" id="wizardNextBtn" onclick="wizardNextStep()" style="background-color: {{ $colors['primary'] }}; border-color: {{ $colors['primary'] }};">
        Next <i class="fas fa-arrow-right ms-1"></i>
    </button>
    <button type="button" class="btn btn-success" id="wizardSaveBtn" onclick="saveWizardTemplate()" style="display: none;">
        <i class="fas fa-save me-1"></i>Save Template
    </button>
</div>

<script>
var wizardCurrentStep = 1;
var wizardTotalSteps = 3;
var wizardMode = '{{ $wizardMode }}';
var wizardIsAdmin = {{ $isAdminMode ? 'true' : 'false' }};
var wizardEditingTemplate = null;
var wizardTenantContext = null;

function initSharedWizard(template, tenantContext) {
    wizardEditingTemplate = template;
    wizardTenantContext = tenantContext;
    wizardCurrentStep = 1;
    
    document.getElementById('wizardTemplateName').value = template.name || '';
    document.getElementById('wizardTemplateIdField').value = template.templateId || '';
    document.getElementById('wizardTemplateContent').value = template.content || '';
    
    if (wizardIsAdmin && tenantContext) {
        var accountBadge = document.getElementById('wizardAccountBadge');
        var customerName = document.getElementById('wizardCustomerName');
        if (accountBadge) accountBadge.textContent = tenantContext.accountName + ' (' + tenantContext.accountId + ')';
        if (customerName) customerName.textContent = tenantContext.accountName;
    }
    
    var triggerIcon = template.trigger === 'api' ? 'fa-code' : 
                      template.trigger === 'portal' ? 'fa-desktop' : 'fa-envelope';
    var triggerLabel = template.trigger === 'api' ? 'API' : 
                       template.trigger === 'portal' ? 'Portal' : 'Email-to-SMS';
    var triggerDisplay = document.getElementById('wizardTriggerDisplay');
    if (triggerDisplay) {
        triggerDisplay.innerHTML = '<i class="fas ' + triggerIcon + ' fa-2x mb-2"></i><div class="fw-medium">' + triggerLabel + '</div>';
    }
    
    selectWizardChannel(template.channel || 'sms');
    updateWizardCharCount();
    updateWizardStepUI();
}

function selectWizardChannel(channel) {
    document.querySelectorAll('.template-wizard-modal .channel-option').forEach(function(opt) {
        opt.classList.remove('selected');
    });
    var selected = document.querySelector('.template-wizard-modal .channel-option[data-channel="' + channel + '"]');
    if (selected) selected.classList.add('selected');
    
    var senderSection = document.getElementById('wizardSenderIdSection');
    var rcsAgentSection = document.getElementById('wizardRcsAgentSection');
    var rcsContentSection = document.getElementById('wizardRcsContentSection');
    var contentLabel = document.getElementById('wizardContentLabel');
    
    if (channel === 'sms') {
        if (senderSection) senderSection.classList.remove('d-none');
        if (rcsAgentSection) rcsAgentSection.classList.add('d-none');
        if (rcsContentSection) rcsContentSection.classList.add('d-none');
        if (contentLabel) contentLabel.innerHTML = 'SMS Content <span class="text-danger">*</span>';
    } else if (channel === 'basic_rcs') {
        if (senderSection) senderSection.classList.remove('d-none');
        if (rcsAgentSection) rcsAgentSection.classList.remove('d-none');
        if (rcsContentSection) rcsContentSection.classList.add('d-none');
        if (contentLabel) contentLabel.innerHTML = 'Message Content <span class="text-danger">*</span>';
    } else if (channel === 'rich_rcs') {
        if (senderSection) senderSection.classList.remove('d-none');
        if (rcsAgentSection) rcsAgentSection.classList.remove('d-none');
        if (rcsContentSection) rcsContentSection.classList.remove('d-none');
        if (contentLabel) contentLabel.innerHTML = 'SMS Fallback Content <span class="text-danger">*</span>';
    }
    
    if (wizardEditingTemplate) {
        wizardEditingTemplate.channel = channel;
    }
    updateWizardPreview();
}

function updateWizardCharCount() {
    var content = document.getElementById('wizardTemplateContent').value;
    var charCount = content.length;
    var hasUnicode = /[^\x00-\x7F]/.test(content);
    var partCount = hasUnicode ? Math.ceil(charCount / 70) : Math.ceil(charCount / 160);
    
    document.getElementById('wizardCharCount').textContent = charCount;
    document.getElementById('wizardPartCount').textContent = partCount || 1;
    document.getElementById('wizardEncodingType').textContent = hasUnicode ? 'Unicode' : 'GSM-7';
    
    updateWizardPreview();
}

function insertWizardPlaceholder(placeholder) {
    var textarea = document.getElementById('wizardTemplateContent');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    var placeholderText = '{' + placeholder + '}';
    
    textarea.value = text.substring(0, start) + placeholderText + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholderText.length;
    textarea.focus();
    updateWizardCharCount();
}

function updateWizardPreview() {
    var container = document.getElementById('wizardPreviewContainer');
    if (!container) return;
    
    var content = document.getElementById('wizardTemplateContent')?.value || '';
    var senderId = document.getElementById('wizardSenderId')?.selectedOptions[0]?.text || 'Sender';
    senderId = senderId.replace(/\s*\(.*?\)\s*$/, '');
    
    if (typeof RcsPreviewRenderer !== 'undefined') {
        container.innerHTML = RcsPreviewRenderer.renderPreview({
            channel: 'sms',
            senderId: senderId,
            message: { body: content }
        });
    } else {
        container.innerHTML = '<div class="border rounded p-4 text-center bg-white" style="width: 280px;"><i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i><div class="bg-light rounded p-3 text-start" style="font-size: 0.85rem;">' + (content || 'Message preview...') + '</div></div>';
    }
}

function wizardNextStep() {
    if (wizardCurrentStep === 1) {
        var name = document.getElementById('wizardTemplateName').value.trim();
        if (!name) {
            document.getElementById('wizardTemplateName').classList.add('is-invalid');
            document.getElementById('wizardTemplateName').focus();
            return;
        }
        document.getElementById('wizardTemplateName').classList.remove('is-invalid');
    }
    
    if (wizardCurrentStep === 2) {
        populateWizardReview();
    }
    
    if (wizardCurrentStep < wizardTotalSteps) {
        wizardCurrentStep++;
        updateWizardStepUI();
    }
}

function wizardPrevStep() {
    if (wizardCurrentStep > 1) {
        wizardCurrentStep--;
        updateWizardStepUI();
    }
}

function updateWizardStepUI() {
    document.querySelectorAll('.template-wizard-modal .wizard-step').forEach(function(step) {
        var stepNum = parseInt(step.dataset.step);
        step.classList.remove('active', 'done');
        if (stepNum < wizardCurrentStep) {
            step.classList.add('done');
            step.querySelector('.step-number').innerHTML = '<i class="fas fa-check"></i>';
        } else if (stepNum === wizardCurrentStep) {
            step.classList.add('active');
            step.querySelector('.step-number').textContent = stepNum;
        } else {
            step.querySelector('.step-number').textContent = stepNum;
        }
    });
    
    document.querySelectorAll('.template-wizard-modal .wizard-step-content').forEach(function(content) {
        var stepNum = parseInt(content.dataset.step);
        content.classList.toggle('d-none', stepNum !== wizardCurrentStep);
    });
    
    document.getElementById('wizardBackBtn').style.display = wizardCurrentStep > 1 ? '' : 'none';
    document.getElementById('wizardNextBtn').style.display = wizardCurrentStep < wizardTotalSteps ? '' : 'none';
    document.getElementById('wizardSaveBtn').style.display = wizardCurrentStep === wizardTotalSteps ? '' : 'none';
    
    var modalTitle = document.getElementById('wizardModalTitle');
    var stepTitles = ['Metadata', 'Content', 'Review'];
    if (modalTitle) {
        modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Edit Template - ' + stepTitles[wizardCurrentStep - 1];
    }
}

function populateWizardReview() {
    var name = document.getElementById('wizardTemplateName').value;
    var templateId = document.getElementById('wizardTemplateIdField').value;
    var content = document.getElementById('wizardTemplateContent').value;
    
    var selectedChannel = document.querySelector('.template-wizard-modal .channel-option.selected');
    var channel = selectedChannel ? selectedChannel.dataset.channel : 'sms';
    var channelLabels = { 'sms': 'SMS', 'basic_rcs': 'Basic RCS', 'rich_rcs': 'Rich RCS' };
    
    document.getElementById('wizardReviewName').textContent = name || '-';
    document.getElementById('wizardReviewTemplateId').textContent = templateId || '-';
    document.getElementById('wizardReviewChannel').textContent = channelLabels[channel] || channel;
    document.getElementById('wizardReviewTrigger').textContent = wizardEditingTemplate?.trigger === 'api' ? 'API' : 'Portal';
    document.getElementById('wizardReviewContentPreview').textContent = content || '-';
    
    if (wizardIsAdmin && wizardTenantContext) {
        var reviewAccount = document.getElementById('wizardReviewAccount');
        if (reviewAccount) reviewAccount.textContent = wizardTenantContext.accountName + ' (' + wizardTenantContext.accountId + ')';
    }
}

function saveWizardTemplate() {
    var name = document.getElementById('wizardTemplateName').value.trim();
    var content = document.getElementById('wizardTemplateContent').value.trim();
    var selectedChannel = document.querySelector('.template-wizard-modal .channel-option.selected');
    var channel = selectedChannel ? selectedChannel.dataset.channel : 'sms';
    var setLive = document.getElementById('wizardSetLive').checked;
    var changeNote = wizardIsAdmin ? (document.getElementById('wizardChangeNote')?.value || '') : '';
    
    var updateData = {
        name: name,
        content: content,
        channel: channel,
        setLive: setLive,
        changeNote: changeNote
    };
    
    if (typeof onWizardSave === 'function') {
        onWizardSave(updateData, wizardEditingTemplate, wizardTenantContext);
    } else {
        console.log('[SharedWizard] Save:', updateData);
        alert('Template saved successfully!');
        bootstrap.Modal.getInstance(document.querySelector('.template-wizard-modal')).hide();
    }
}

function openWizardRcsBuilder() {
    alert('RCS Wizard would open here for configuring rich content.');
}
</script>

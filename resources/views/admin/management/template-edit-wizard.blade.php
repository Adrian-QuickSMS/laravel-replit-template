@extends('layouts.admin')

@section('title', 'Edit Template')

@push('styles')
<link href="{{ asset('css/admin-control-plane.css') }}" rel="stylesheet">
<style>
.form-wizard {
    border: 0;
}
.form-wizard .nav-wizard {
    box-shadow: none !important;
    margin-bottom: 2rem;
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
}
.form-wizard .nav-wizard li {
    flex: 1;
    max-width: 150px;
}
.form-wizard .nav-wizard li .nav-link {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #6c757d;
    padding: 0;
    background: transparent !important;
    border: none !important;
}
.form-wizard .nav-wizard li .nav-link span {
    border-radius: 3.125rem;
    width: 3rem;
    height: 3rem;
    border: 0.125rem solid #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: #3b82f6;
    position: relative;
    z-index: 1;
}
.form-wizard .nav-wizard li .nav-link:after {
    position: absolute;
    top: 1.5rem;
    left: 50%;
    height: 0.1875rem;
    background: #e9ecef;
    content: "";
    z-index: 0;
    width: 100%;
}
.form-wizard .nav-wizard li:last-child .nav-link:after {
    content: none;
}
.form-wizard .nav-wizard li .nav-link.active span,
.form-wizard .nav-wizard li .nav-link.done span {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}
.form-wizard .nav-wizard li .nav-link.active:after,
.form-wizard .nav-wizard li .nav-link.done:after {
    background: #3b82f6 !important;
}
.form-wizard .nav-wizard li .nav-link small {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.75rem;
}
.form-wizard .nav-wizard li .nav-link.active small {
    color: #3b82f6;
    font-weight: 600;
}
.toolbar-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem 0 0 0;
    border-top: 1px solid #e9ecef;
    margin-top: 2rem;
}
.toolbar-bottom .btn-back {
    background: #6b7280 !important;
    color: #fff !important;
    border: none !important;
    font-weight: 500;
}
.toolbar-bottom .btn-back:hover {
    background: #4b5563 !important;
}
.channel-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
    background: #fff;
}
.channel-tile:hover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}
.channel-tile.selected {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.08);
}
.channel-tile.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.channel-tile.disabled:hover {
    border-color: #e9ecef;
    background: #fff;
}
.tenant-context-banner {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border: 1px solid #93c5fd;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.tenant-context-banner .account-name {
    font-weight: 600;
    color: #1e40af;
}
.tenant-context-banner .account-id {
    color: #3b82f6;
    font-size: 0.875rem;
}
.admin-audit-warning {
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}
.message-preview {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    min-height: 120px;
}
.char-counter {
    font-size: 0.875rem;
    color: #6b7280;
}
.placeholder-btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 0.5rem;
}
.wizard-step-content {
    display: none;
}
.wizard-step-content.active {
    display: block;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-edit me-2 text-primary"></i>Edit Template
                    </h4>
                    <a href="{{ route('admin.management.templates') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Templates
                    </a>
                </div>
                <div class="card-body position-relative">
                    <div id="loadingOverlay" class="loading-overlay">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p class="text-muted mb-0">Loading template details...</p>
                        </div>
                    </div>
                    
                    <div id="errorOverlay" class="loading-overlay d-none">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                            <p class="text-danger mb-2" id="errorMessage">Failed to load template.</p>
                            <a href="{{ route('admin.management.templates') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Templates
                            </a>
                        </div>
                    </div>
                    
                    <div id="wizardContent" class="d-none">
                        <div class="tenant-context-banner">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building me-3 text-primary" style="font-size: 1.5rem;"></i>
                                <div>
                                    <div class="account-name" id="tenantAccountName">Loading...</div>
                                    <div class="account-id">Account: <span id="tenantAccountId">-</span></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-wizard">
                            <ul class="nav-wizard">
                                <li>
                                    <a href="javascript:void(0)" class="nav-link active" id="step1Nav" onclick="goToStep(1)">
                                        <span>1</span>
                                        <small>Metadata</small>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" class="nav-link" id="step2Nav" onclick="goToStep(2)">
                                        <span>2</span>
                                        <small>Content</small>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" class="nav-link" id="step3Nav" onclick="goToStep(3)">
                                        <span>3</span>
                                        <small>Review</small>
                                    </a>
                                </li>
                            </ul>
                            
                            <div id="step1Content" class="wizard-step-content active">
                                <h5 class="mb-4">Template Information</h5>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Template ID</label>
                                        <input type="text" class="form-control" id="templateId" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Trigger Source</label>
                                        <div class="form-control bg-light" id="triggerDisplay">
                                            <i class="fas fa-code me-2"></i>API
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="templateName" placeholder="Enter template name">
                                    <div class="invalid-feedback">Please enter a template name.</div>
                                </div>
                                
                                <h6 class="mb-3">Channel Type</h6>
                                <p class="text-muted small mb-3">Select the message channel for this template.</p>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="channel-tile selected" id="channelSms" onclick="selectChannel('sms')">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-sms text-primary me-2" style="font-size: 1.5rem;"></i>
                                                <strong>SMS</strong>
                                            </div>
                                            <small class="text-muted">Standard text messaging</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="channel-tile" id="channelBasicRcs" onclick="selectChannel('basic_rcs')">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-comment-dots text-success me-2" style="font-size: 1.5rem;"></i>
                                                <strong>Basic RCS</strong>
                                            </div>
                                            <small class="text-muted">Rich text with fallback</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="channel-tile disabled" id="channelRichRcs" title="Rich RCS templates must be edited in the customer portal">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-mobile-alt text-info me-2" style="font-size: 1.5rem;"></i>
                                                <strong>Rich RCS</strong>
                                                <span class="badge bg-secondary ms-2" style="font-size: 0.6rem;">Portal Only</span>
                                            </div>
                                            <small class="text-muted">Cards, carousels, buttons</small>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="selectedChannel" value="sms">
                            </div>
                            
                            <div id="step2Content" class="wizard-step-content">
                                <h5 class="mb-4">Message Content</h5>
                                
                                <div class="row">
                                    <div class="col-lg-7">
                                        <div class="mb-3">
                                            <label class="form-label">Message Content <span class="text-danger">*</span></label>
                                            <div class="mb-2">
                                                <span class="text-muted small me-2">Insert:</span>
                                                <button type="button" class="btn btn-outline-primary btn-sm placeholder-btn" onclick="insertPlaceholder('FirstName')">FirstName</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm placeholder-btn" onclick="insertPlaceholder('LastName')">LastName</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm placeholder-btn" onclick="insertPlaceholder('Company')">Company</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm placeholder-btn" onclick="insertPlaceholder('OrderID')">OrderID</button>
                                            </div>
                                            <textarea class="form-control" id="templateContent" rows="6" placeholder="Enter your message content..."></textarea>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between char-counter">
                                            <span>Characters: <strong id="charCount">0</strong></span>
                                            <span>Encoding: <strong id="encodingType">GSM-7</strong></span>
                                            <span>Parts: <strong id="partCount">1</strong></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Preview</label>
                                        <div class="message-preview">
                                            <div id="previewMessage" class="text-muted fst-italic">Your message preview will appear here...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step3Content" class="wizard-step-content">
                                <h5 class="mb-4">Review & Save</h5>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card bg-light border-0">
                                            <div class="card-body">
                                                <h6 class="text-muted mb-3">Template Details</h6>
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr>
                                                        <td class="text-muted" style="width: 120px;">Name:</td>
                                                        <td><strong id="reviewName">-</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Template ID:</td>
                                                        <td id="reviewTemplateId">-</td>
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
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light border-0">
                                            <div class="card-body">
                                                <h6 class="text-muted mb-3">Message Content</h6>
                                                <div id="reviewContent" class="p-2 bg-white rounded border">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Change Note</label>
                                    <textarea class="form-control" id="changeNote" rows="2" placeholder="Describe the changes made (optional)"></textarea>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="setLive">
                                    <label class="form-check-label" for="setLive">
                                        Set template status to <strong>Live</strong> after saving
                                    </label>
                                </div>
                                
                                <div class="admin-audit-warning">
                                    <div class="d-flex">
                                        <i class="fas fa-shield-alt text-warning me-3" style="font-size: 1.25rem;"></i>
                                        <div>
                                            <strong class="text-dark">Admin Audit Notice</strong>
                                            <p class="mb-0 small text-muted">This edit will be logged with your admin credentials, timestamp, and all changes made. Cross-tenant template modifications are subject to security review.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="toolbar-bottom">
                                <div>
                                    <button type="button" class="btn btn-back" id="prevBtn" onclick="prevStep()" style="display: none;">
                                        <i class="fas fa-arrow-left me-1"></i>Previous
                                    </button>
                                </div>
                                <div>
                                    <a href="{{ route('admin.management.templates') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                                        Next<i class="fas fa-arrow-right ms-1"></i>
                                    </button>
                                    <button type="button" class="btn btn-success" id="saveBtn" onclick="saveTemplate()" style="display: none;">
                                        <i class="fas fa-save me-1"></i>Save Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script src="{{ asset('js/admin-templates-service.js') }}"></script>
<script src="{{ asset('js/quicksms-audit-logger.js') }}"></script>
<script>
var accountId = '{{ $account_id }}';
var templateId = '{{ $template_id }}';
var currentStep = 1;
var templateData = null;
var isReadOnly = false;

var mockTemplates = {
    'TPL-12345678': { templateId: 'TPL-12345678', name: 'Welcome Message', accountId: 'ACC-1234', accountName: 'Acme Corporation', channel: 'sms', trigger: 'portal', content: 'Hi {FirstName}, welcome to Acme Corporation! Your account is now active.', status: 'live' },
    'TPL-23456789': { templateId: 'TPL-23456789', name: 'Order Confirmation', accountId: 'ACC-1234', accountName: 'Acme Corporation', channel: 'basic_rcs', trigger: 'api', content: 'Thank you for your order #{OrderID}. Your items will be shipped within 2-3 business days.', status: 'live' },
    'TPL-34567890': { templateId: 'TPL-34567890', name: 'Appointment Reminder', accountId: 'ACC-5678', accountName: 'TechStart Ltd', channel: 'sms', trigger: 'api', content: 'Reminder: Your appointment is scheduled for {Date} at {Time}. Reply YES to confirm.', status: 'live' },
    'TPL-45678901': { templateId: 'TPL-45678901', name: 'Product Launch', accountId: 'ACC-5678', accountName: 'TechStart Ltd', channel: 'rich_rcs', trigger: 'portal', content: '', status: 'draft' },
    'TPL-56789012': { templateId: 'TPL-56789012', name: 'Course Enrollment', accountId: 'ACC-9012', accountName: 'EduLearn Academy', channel: 'sms', trigger: 'email-to-sms', content: 'Welcome to {CourseName}! Your enrollment is confirmed. Class starts {StartDate}.', status: 'live' },
    'TPL-67890123': { templateId: 'TPL-67890123', name: 'Prescription Ready', accountId: 'ACC-3456', accountName: 'HealthCare Plus', channel: 'sms', trigger: 'api', content: 'Your prescription is ready for pickup at {PharmacyName}. Ref: {RxNumber}', status: 'live' },
    'TPL-78901234': { templateId: 'TPL-78901234', name: 'Health Tips', accountId: 'ACC-3456', accountName: 'HealthCare Plus', channel: 'basic_rcs', trigger: 'portal', content: 'Weekly Health Tip: {TipContent}. Stay healthy with HealthCare Plus!', status: 'draft' },
    'TPL-89012345': { templateId: 'TPL-89012345', name: 'Flash Sale Alert', accountId: 'ACC-7890', accountName: 'RetailMax Group', channel: 'rich_rcs', trigger: 'portal', content: '', status: 'suspended' },
    'TPL-90123456': { templateId: 'TPL-90123456', name: 'Account Statement', accountId: 'ACC-2345', accountName: 'FinServe Solutions', channel: 'sms', trigger: 'api', content: 'Your {Month} statement is ready. Balance: {Balance}. View at {PortalURL}', status: 'live' },
    'TPL-01234567': { templateId: 'TPL-01234567', name: 'Campaign Update', accountId: 'ACC-6789', accountName: 'MediaFlow Digital', channel: 'basic_rcs', trigger: 'api', content: 'Campaign "{CampaignName}" performance update: {Impressions} impressions, {Clicks} clicks.', status: 'live' },
    'TPL-11223344': { templateId: 'TPL-11223344', name: 'Delivery Update', accountId: 'ACC-1234', accountName: 'Acme Corporation', channel: 'sms', trigger: 'api', content: 'Your order {OrderID} is now {Status}. Track at: {TrackingURL}', status: 'live' },
    'TPL-22334455': { templateId: 'TPL-22334455', name: 'Safety Alert', accountId: 'ACC-0123', accountName: 'BuildRight Construction', channel: 'sms', trigger: 'portal', content: 'SAFETY NOTICE: {AlertType} alert for site {SiteName}. Please follow safety procedures.', status: 'live' }
};

document.addEventListener('DOMContentLoaded', function() {
    loadTemplate();
    
    document.getElementById('templateContent').addEventListener('input', updateCharCount);
    document.getElementById('templateName').addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});

async function loadTemplate() {
    var loadingOverlay = document.getElementById('loadingOverlay');
    var errorOverlay = document.getElementById('errorOverlay');
    var wizardContent = document.getElementById('wizardContent');
    
    await new Promise(function(resolve) { setTimeout(resolve, 500); });
    
    templateData = mockTemplates[templateId];
    
    if (!templateData || templateData.accountId !== accountId) {
        loadingOverlay.classList.add('d-none');
        errorOverlay.classList.remove('d-none');
        document.getElementById('errorMessage').textContent = 'Template not found or access denied.';
        return;
    }
    
    if (templateData.channel === 'rich_rcs') {
        loadingOverlay.classList.add('d-none');
        errorOverlay.classList.remove('d-none');
        document.getElementById('errorMessage').textContent = 'Rich RCS templates must be edited in the customer portal.';
        return;
    }
    
    if (templateData.status === 'archived') {
        loadingOverlay.classList.add('d-none');
        errorOverlay.classList.remove('d-none');
        document.getElementById('errorMessage').textContent = 'Archived templates cannot be edited.';
        return;
    }
    
    document.getElementById('tenantAccountName').textContent = templateData.accountName;
    document.getElementById('tenantAccountId').textContent = templateData.accountId;
    document.getElementById('templateId').value = templateData.templateId;
    document.getElementById('templateName').value = templateData.name;
    document.getElementById('templateContent').value = templateData.content;
    
    var triggerIcon = templateData.trigger === 'api' ? 'fa-code' : 
                      templateData.trigger === 'portal' ? 'fa-desktop' : 'fa-envelope';
    var triggerLabel = templateData.trigger === 'api' ? 'API' : 
                       templateData.trigger === 'portal' ? 'Portal' : 'Email-to-SMS';
    document.getElementById('triggerDisplay').innerHTML = '<i class="fas ' + triggerIcon + ' me-2"></i>' + triggerLabel;
    
    selectChannel(templateData.channel);
    updateCharCount();
    
    loadingOverlay.classList.add('d-none');
    wizardContent.classList.remove('d-none');
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAccess({
            eventType: 'TEMPLATE_EDIT_STARTED',
            accountId: accountId,
            templateId: templateId,
            templateName: templateData.name,
            adminAction: 'edit_template'
        });
    }
}

function selectChannel(channel) {
    document.querySelectorAll('.channel-tile').forEach(function(tile) {
        tile.classList.remove('selected');
    });
    
    if (channel === 'sms') {
        document.getElementById('channelSms').classList.add('selected');
    } else if (channel === 'basic_rcs') {
        document.getElementById('channelBasicRcs').classList.add('selected');
    }
    
    document.getElementById('selectedChannel').value = channel;
}

function goToStep(step) {
    if (step > currentStep) {
        if (!validateCurrentStep()) return;
    }
    currentStep = step;
    updateUI();
}

function nextStep() {
    if (!validateCurrentStep()) return;
    
    if (currentStep === 2) {
        populateReview();
    }
    
    if (currentStep < 3) {
        currentStep++;
        updateUI();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateUI();
    }
}

function validateCurrentStep() {
    if (currentStep === 1) {
        var name = document.getElementById('templateName').value.trim();
        if (!name) {
            document.getElementById('templateName').classList.add('is-invalid');
            return false;
        }
    }
    
    if (currentStep === 2) {
        var content = document.getElementById('templateContent').value.trim();
        if (!content) {
            alert('Please enter message content.');
            return false;
        }
    }
    
    return true;
}

function updateUI() {
    document.querySelectorAll('.wizard-step-content').forEach(function(el) {
        el.classList.remove('active');
    });
    document.getElementById('step' + currentStep + 'Content').classList.add('active');
    
    document.querySelectorAll('.nav-wizard .nav-link').forEach(function(link, index) {
        link.classList.remove('active', 'done');
        if (index + 1 === currentStep) {
            link.classList.add('active');
        } else if (index + 1 < currentStep) {
            link.classList.add('done');
        }
    });
    
    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'inline-block' : 'none';
    document.getElementById('nextBtn').style.display = currentStep < 3 ? 'inline-block' : 'none';
    document.getElementById('saveBtn').style.display = currentStep === 3 ? 'inline-block' : 'none';
}

function populateReview() {
    document.getElementById('reviewName').textContent = document.getElementById('templateName').value;
    document.getElementById('reviewTemplateId').textContent = templateData.templateId;
    
    var channel = document.getElementById('selectedChannel').value;
    var channelLabel = channel === 'sms' ? 'SMS' : channel === 'basic_rcs' ? 'Basic RCS' : 'Rich RCS';
    document.getElementById('reviewChannel').textContent = channelLabel;
    
    var triggerLabel = templateData.trigger === 'api' ? 'API' : 
                       templateData.trigger === 'portal' ? 'Portal' : 'Email-to-SMS';
    document.getElementById('reviewTrigger').textContent = triggerLabel;
    
    document.getElementById('reviewAccount').textContent = templateData.accountName + ' (' + templateData.accountId + ')';
    
    var content = document.getElementById('templateContent').value;
    var highlightedContent = content.replace(/\{(\w+)\}/g, '<span class="badge bg-info text-dark">{$1}</span>');
    document.getElementById('reviewContent').innerHTML = highlightedContent || '-';
}

function updateCharCount() {
    var content = document.getElementById('templateContent').value;
    var charCount = content.length;
    
    document.getElementById('charCount').textContent = charCount;
    
    var hasUnicode = /[^\x00-\x7F\u00A0-\u00FF]/.test(content);
    document.getElementById('encodingType').textContent = hasUnicode ? 'Unicode' : 'GSM-7';
    
    var maxCharsPerSegment = hasUnicode ? 70 : 160;
    var segments = Math.ceil(charCount / maxCharsPerSegment) || 1;
    document.getElementById('partCount').textContent = segments;
    
    updatePreview();
}

function updatePreview() {
    var content = document.getElementById('templateContent').value || 'Your message preview will appear here...';
    var highlightedContent = content.replace(/\{(\w+)\}/g, '<span class="badge bg-info text-dark small">{$1}</span>');
    document.getElementById('previewMessage').innerHTML = highlightedContent;
    document.getElementById('previewMessage').classList.toggle('text-muted', !document.getElementById('templateContent').value);
    document.getElementById('previewMessage').classList.toggle('fst-italic', !document.getElementById('templateContent').value);
}

function insertPlaceholder(placeholder) {
    var textarea = document.getElementById('templateContent');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    var tag = '{' + placeholder + '}';
    
    textarea.value = text.substring(0, start) + tag + text.substring(end);
    textarea.setSelectionRange(start + tag.length, start + tag.length);
    textarea.focus();
    
    updateCharCount();
}

async function saveTemplate() {
    var saveBtn = document.getElementById('saveBtn');
    var originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    
    var updateData = {
        name: document.getElementById('templateName').value.trim(),
        channel: document.getElementById('selectedChannel').value,
        content: document.getElementById('templateContent').value,
        changeNote: document.getElementById('changeNote').value.trim() || 'Updated by Admin',
        setLive: document.getElementById('setLive').checked
    };
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAccess({
            eventType: 'TEMPLATE_UPDATED',
            accountId: accountId,
            templateId: templateId,
            templateName: updateData.name,
            changes: {
                channel: updateData.channel,
                content: 'updated',
                setLive: updateData.setLive
            },
            changeNote: updateData.changeNote
        });
    }
    
    await new Promise(function(resolve) { setTimeout(resolve, 800); });
    
    saveBtn.disabled = false;
    saveBtn.innerHTML = originalText;
    
    alert('Template updated successfully!');
    window.location.href = '{{ route("admin.management.templates") }}';
}
</script>
@endpush

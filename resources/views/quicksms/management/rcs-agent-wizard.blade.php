@extends('layouts.quicksms')

@section('title', 'Register RCS Agent')

@push('styles')
<link href="{{ asset('vendor/jquery-smartwizard/dist/css/smart_wizard.min.css') }}" rel="stylesheet">
<style>
/* Fillow Form Wizard Stepper Styles */
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
    border: 0.125rem solid var(--primary, #886CC0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: var(--primary, #886CC0);
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
    background: var(--primary, #886CC0);
    color: #fff;
    border-color: var(--primary, #886CC0);
}
.form-wizard .nav-wizard li .nav-link.active:after,
.form-wizard .nav-wizard li .nav-link.done:after {
    background: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link small {
    display: none;
}

/* Toolbar Button Styles */
.form-wizard .toolbar-bottom {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
.form-wizard .sw-btn-prev,
.form-wizard .sw-btn-next {
    background-color: var(--primary, #886CC0) !important;
    border: 0 !important;
    padding: 0.75rem 1.5rem !important;
    color: #fff !important;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.form-wizard .sw-btn-prev {
    background-color: #6c757d !important;
}
.form-wizard .sw-btn-prev:hover {
    background-color: #5a6268 !important;
}
.form-wizard .sw-btn-next:hover {
    background-color: #7559b3 !important;
}
.form-wizard .sw-btn-prev:disabled,
.form-wizard .sw-btn-next:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* Tab Content */
.form-wizard .tab-content .tab-pane {
    padding: 0;
}
.selectable-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
}
.selectable-tile:hover {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.05);
}
.selectable-tile.selected {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.1);
}
.selectable-tile .tile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}
.selectable-tile .tile-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.selectable-tile .tile-check {
    color: #886CC0;
    font-size: 1.25rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.selectable-tile.selected .tile-check {
    opacity: 1;
}
.selectable-tile .tile-title {
    margin-bottom: 0.25rem;
    font-weight: 600;
}
.selectable-tile .tile-desc {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}
.selectable-tile .tile-footer {
    font-size: 0.8rem;
}
.bg-pastel-primary { background: rgba(136, 108, 192, 0.15); color: #886CC0; }
.bg-pastel-secondary { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
.bg-pastel-warning { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
.bg-pastel-info { background: rgba(23, 162, 184, 0.15); color: #117a8b; }
.bg-pastel-danger { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
.bg-pastel-success { background: rgba(40, 167, 69, 0.15); color: #28a745; }
.review-section {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
.review-section h6 {
    font-weight: 600;
    color: #886CC0;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px dashed #f1f3f5;
}
.review-row:last-child {
    border-bottom: none;
}
.review-label {
    color: #6c757d;
    font-size: 0.875rem;
}
.review-value {
    font-weight: 500;
    font-size: 0.875rem;
    color: #343a40;
}
.test-number-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    margin: 0.25rem;
    font-size: 0.85rem;
}
.test-number-badge .remove-btn {
    margin-left: 0.5rem;
    cursor: pointer;
    color: #dc3545;
    opacity: 0.7;
}
.test-number-badge .remove-btn:hover {
    opacity: 1;
}
.autosave-indicator {
    font-size: 0.8rem;
    color: #6c757d;
}
.autosave-indicator.saving { color: #ffc107; }
.autosave-indicator.saved { color: #28a745; }
.autosave-indicator.error { color: #dc3545; }
.color-preview {
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
.alert-pastel-primary {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.2);
    color: #614099;
}
.badge-pastel-primary {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('management.rcs-agent') }}">RCS Agents</a></li>
            <li class="breadcrumb-item active">Register Agent</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-robot me-2 text-primary"></i>Register RCS Agent</h4>
                    <span class="autosave-indicator saved" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <div class="card-body">
                    <div id="rcsAgentWizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link" href="#step-1"><span>1</span><small>Basics</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-2"><span>2</span><small>Branding</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-3"><span>3</span><small>Handset</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-4"><span>4</span><small>Agent Type</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-5"><span>5</span><small>Messaging</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-6"><span>6</span><small>Company</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-7"><span>7</span><small>Test Numbers</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-8"><span>8</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <!-- Step 1: Agent Basics -->
                            <div id="step-1" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 1: Agent Basics</strong> - Define your RCS Agent's name, description, and brand colour.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label class="text-label form-label">RCS Agent Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="agentName" placeholder="e.g., Your Brand Name" maxlength="25">
                                                <small class="text-muted"><span id="nameCharCount">0</span>/25 characters. Displayed as sender name on devices.</small>
                                                <div class="invalid-feedback">Please enter an agent name (max 25 characters)</div>
                                            </div>
                                            
                                            <div class="col-lg-12 mb-3">
                                                <label class="text-label form-label">RCS Agent Description <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="agentDescription" rows="3" placeholder="Brief description of your business..." maxlength="100"></textarea>
                                                <small class="text-muted"><span id="descCharCount">0</span>/100 characters — Visible to customers in the agent details view on their device</small>
                                                <div class="invalid-feedback">Please enter a description (max 100 characters)</div>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Brand Colour <span class="text-danger">*</span></label>
                                                <div class="d-flex align-items-center gap-3">
                                                    <input type="color" class="form-control form-control-color color-preview" id="brandColor" value="#886CC0" style="width: 50px; height: 38px;">
                                                    <input type="text" class="form-control" id="brandColorHex" value="#886CC0" maxlength="7" style="max-width: 120px;">
                                                </div>
                                                <small class="text-muted">Colour used for selected elements in the agent details view on the device.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 2: Branding Assets -->
                            <div id="step-2" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 2: Branding Assets</strong> - Upload your agent logo and hero/banner image.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-6 mb-4">
                                                <div class="alert alert-pastel-primary py-2 px-3 mb-3" style="font-size: 0.8rem;">
                                                    <i class="fas fa-mobile-alt me-2"></i>
                                                    <strong>Device display:</strong> Logos are displayed as circles on handset devices.
                                                </div>
                                                
                                                @include('quicksms.partials.shared-image-editor', [
                                                    'editorId' => 'agentLogo',
                                                    'preset' => 'agent-logo',
                                                    'label' => 'Agent Logo',
                                                    'accept' => 'image/png,image/jpeg',
                                                    'maxSize' => 2 * 1024 * 1024,
                                                    'required' => true,
                                                    'helpText' => 'Upload a square logo. Final output: 222×222 px with circular display.',
                                                    'showUrlTab' => true
                                                ])
                                            </div>
                                            
                                            <div class="col-lg-6 mb-4">
                                                <div class="alert alert-pastel-primary py-2 px-3 mb-3" style="font-size: 0.8rem;">
                                                    <i class="fas fa-mobile-alt me-2"></i>
                                                    <strong>Device display:</strong> Hero images partially overlap the logo on handset devices.
                                                </div>
                                                
                                                @include('quicksms.partials.shared-image-editor', [
                                                    'editorId' => 'agentHero',
                                                    'preset' => 'agent-hero',
                                                    'label' => 'Hero / Banner Image',
                                                    'accept' => 'image/png,image/jpeg',
                                                    'maxSize' => 5 * 1024 * 1024,
                                                    'required' => true,
                                                    'helpText' => 'Wide banner image. Final output: 1480×448 px (45:14 aspect ratio).',
                                                    'showUrlTab' => true
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 3: Handset + Compliance -->
                            <div id="step-3" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 3: Handset + Compliance</strong> - Configure handset contact details and compliance URLs.
                                        </div>
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-mobile-alt me-2 text-primary"></i>Handset Contact Details</h6>
                                        <p class="text-muted small mb-3">These details will be shown to message recipients on their device. At least one contact method must be displayed.</p>
                                        
                                        <!-- Phone Number Row -->
                                        <div class="mb-4">
                                            <label class="text-label form-label">Phone Number</label>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-lg-3">
                                                    <input type="text" class="form-control" id="phoneLabel" value="Call" placeholder="Label e.g. Support Line">
                                                    <small class="text-muted">Button label</small>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <span class="input-group-text">+44</span>
                                                        <input type="tel" class="form-control" id="supportPhone" placeholder="20 1234 5678">
                                                    </div>
                                                    <small class="text-muted">UK numbers only. Leading 0 will be stripped automatically.</small>
                                                    <div class="invalid-feedback">Please enter a valid UK phone number</div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="showPhoneToggle" checked>
                                                        <label class="form-check-label small" for="showPhoneToggle">Display on handset</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Email Address Row -->
                                        <div class="mb-4">
                                            <label class="text-label form-label">Email Address</label>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-lg-3">
                                                    <input type="text" class="form-control" id="emailLabel" value="Email" placeholder="Label e.g. Customer Care">
                                                    <small class="text-muted">Button label</small>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="email" class="form-control" id="supportEmail" placeholder="support@example.com">
                                                    <small class="text-muted">Contact email for customer inquiries</small>
                                                    <div class="invalid-feedback">Please enter a valid email address</div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="showEmailToggle" checked>
                                                        <label class="form-check-label small" for="showEmailToggle">Display on handset</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Website URL Row (no toggle - required if others not displayed) -->
                                        <div class="mb-4">
                                            <label class="text-label form-label">Website URL <span class="text-danger" id="websiteRequired">*</span></label>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-lg-3">
                                                    <input type="text" class="form-control" id="websiteLabel" value="Website" placeholder="Label e.g. Visit Us">
                                                    <small class="text-muted">Button label</small>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="url" class="form-control" id="businessWebsite" placeholder="https://www.example.com">
                                                    <small class="text-muted">Must start with https://</small>
                                                    <div class="invalid-feedback">Please enter a valid HTTPS URL</div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <small class="text-muted fst-italic" id="websiteRequiredNote">Required if phone/email not displayed</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-shield-alt me-2 text-primary"></i>Compliance URLs</h6>
                                        <p class="text-muted small mb-3">Required for RCS agent registration. Both must use HTTPS.</p>
                                        
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Privacy Policy URL <span class="text-danger">*</span></label>
                                                <input type="url" class="form-control" id="privacyUrl" placeholder="https://www.example.com/privacy">
                                                <small class="text-muted">Link to your privacy policy page</small>
                                                <div class="invalid-feedback">Please enter a valid HTTPS URL</div>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Terms of Service URL <span class="text-danger">*</span></label>
                                                <input type="url" class="form-control" id="termsUrl" placeholder="https://www.example.com/terms">
                                                <small class="text-muted">Link to your terms of service page</small>
                                                <div class="invalid-feedback">Please enter a valid HTTPS URL</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 4: Agent Type -->
                            <div id="step-4" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 4: Agent Type</strong> - Select billing category, use case, and provide a use case description.
                                        </div>
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-credit-card me-2 text-primary"></i>Billing Category <span class="text-danger">*</span></h6>
                                        <p class="text-muted small mb-3">Select the billing model for this agent.</p>
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-6 mb-3">
                                                <div class="selectable-tile billing-tile selected" data-billing="non-conversational">
                                                    <div class="tile-header">
                                                        <div class="tile-icon bg-pastel-secondary"><i class="fas fa-paper-plane"></i></div>
                                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    </div>
                                                    <div class="tile-body">
                                                        <h6 class="tile-title">Non-conversational</h6>
                                                        <p class="tile-desc">One-way notifications and alerts only</p>
                                                    </div>
                                                    <div class="tile-footer">
                                                        <button type="button" class="btn btn-sm btn-tile-learn-more" data-tile-info="non-conversational">
                                                            <i class="fas fa-info-circle me-1"></i>Learn More
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="selectable-tile billing-tile" data-billing="conversational">
                                                    <div class="tile-header">
                                                        <div class="tile-icon bg-pastel-primary"><i class="fas fa-comments"></i></div>
                                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    </div>
                                                    <div class="tile-body">
                                                        <h6 class="tile-title">Conversational</h6>
                                                        <p class="tile-desc">Two-way messaging with customer interactions</p>
                                                    </div>
                                                    <div class="tile-footer">
                                                        <button type="button" class="btn btn-sm btn-tile-learn-more" data-tile-info="conversational">
                                                            <i class="fas fa-info-circle me-1"></i>Learn More
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback d-block" id="billingError" style="display: none;">Please select a billing category</div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-bullseye me-2 text-primary"></i>Use Case <span class="text-danger">*</span></h6>
                                        <p class="text-muted small mb-3">Select the primary use case for this agent.</p>
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <div class="selectable-tile usecase-tile" data-usecase="otp">
                                                    <div class="tile-header">
                                                        <div class="tile-icon bg-pastel-warning"><i class="fas fa-key"></i></div>
                                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    </div>
                                                    <div class="tile-body">
                                                        <h6 class="tile-title">OTP</h6>
                                                        <p class="tile-desc">Verification codes</p>
                                                    </div>
                                                    <div class="tile-footer">
                                                        <button type="button" class="btn btn-sm btn-tile-learn-more" data-tile-info="otp">
                                                            <i class="fas fa-info-circle me-1"></i>Learn More
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <div class="selectable-tile usecase-tile" data-usecase="transactional">
                                                    <div class="tile-header">
                                                        <div class="tile-icon bg-pastel-info"><i class="fas fa-receipt"></i></div>
                                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    </div>
                                                    <div class="tile-body">
                                                        <h6 class="tile-title">Transactional</h6>
                                                        <p class="tile-desc">Order updates, alerts</p>
                                                    </div>
                                                    <div class="tile-footer">
                                                        <button type="button" class="btn btn-sm btn-tile-learn-more" data-tile-info="transactional">
                                                            <i class="fas fa-info-circle me-1"></i>Learn More
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <div class="selectable-tile usecase-tile" data-usecase="promotional">
                                                    <div class="tile-header">
                                                        <div class="tile-icon bg-pastel-danger"><i class="fas fa-bullhorn"></i></div>
                                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    </div>
                                                    <div class="tile-body">
                                                        <h6 class="tile-title">Promotional</h6>
                                                        <p class="tile-desc">Marketing, offers</p>
                                                    </div>
                                                    <div class="tile-footer">
                                                        <button type="button" class="btn btn-sm btn-tile-learn-more" data-tile-info="promotional">
                                                            <i class="fas fa-info-circle me-1"></i>Learn More
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <div class="selectable-tile usecase-tile" data-usecase="multi-use">
                                                    <div class="tile-header">
                                                        <div class="tile-icon bg-pastel-success"><i class="fas fa-layer-group"></i></div>
                                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    </div>
                                                    <div class="tile-body">
                                                        <h6 class="tile-title">Multi-use</h6>
                                                        <p class="tile-desc">Multiple use cases</p>
                                                    </div>
                                                    <div class="tile-footer">
                                                        <button type="button" class="btn btn-sm btn-tile-learn-more" data-tile-info="multi-use">
                                                            <i class="fas fa-info-circle me-1"></i>Learn More
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback d-block" id="useCaseError" style="display: none;">Please select a use case</div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-file-alt me-2 text-primary"></i>Use Case Description <span class="text-danger">*</span></h6>
                                        
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label class="text-label form-label">Use Case Overview</label>
                                                <textarea class="form-control" id="useCaseOverview" rows="4" maxlength="1000" placeholder="Include example message types, target audience, and business purpose..."></textarea>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Detailed explanation of your messaging use case</small>
                                                    <small class="text-muted"><span id="useCaseCharCount">0</span>/1000</small>
                                                </div>
                                                <div class="invalid-feedback">Please provide a use case overview</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 5: Messaging Behaviour -->
                            <div id="step-5" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 5: Messaging Behaviour</strong> - Define your messaging patterns and compliance measures.
                                        </div>
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-cog me-2 text-primary"></i>Messaging Patterns</h6>
                                        
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Campaign Frequency <span class="text-danger">*</span></label>
                                                <select class="form-select" id="campaignFrequency">
                                                    <option value="">Select frequency...</option>
                                                    <option value="daily">Daily</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="monthly">Monthly</option>
                                                    <option value="on-demand">On-demand / Event-triggered</option>
                                                    <option value="continuous">Continuous (24/7)</option>
                                                </select>
                                                <div class="invalid-feedback">Please select campaign frequency</div>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Estimated Monthly Volume <span class="text-danger">*</span></label>
                                                <select class="form-select" id="monthlyVolume">
                                                    <option value="">Select volume...</option>
                                                    <option value="0-1000">Up to 1,000 messages</option>
                                                    <option value="1000-10000">1,000 - 10,000 messages</option>
                                                    <option value="10000-100000">10,000 - 100,000 messages</option>
                                                    <option value="100000-500000">100,000 - 500,000 messages</option>
                                                    <option value="500000+">500,000+ messages</option>
                                                </select>
                                                <div class="invalid-feedback">Please select estimated monthly volume</div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-check-circle me-2 text-primary"></i>Consent & Opt-out</h6>
                                        
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label class="text-label form-label">Opt-in / Legitimate Interest Description <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="optInDescription" rows="3" maxlength="500" placeholder="Describe how users opt-in to receive messages..."></textarea>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Explain your legal basis for sending messages</small>
                                                    <small class="text-muted"><span id="optInCharCount">0</span>/500</small>
                                                </div>
                                                <div class="invalid-feedback">Please describe your opt-in basis</div>
                                            </div>
                                            
                                            <div class="col-lg-12 mb-3">
                                                <label class="text-label form-label">Opt-out Mechanism Description <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="optOutDescription" rows="3" maxlength="500" placeholder="Describe how users can opt-out of receiving messages..."></textarea>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Explain how recipients can stop receiving messages</small>
                                                    <small class="text-muted"><span id="optOutCharCount">0</span>/500</small>
                                                </div>
                                                <div class="invalid-feedback">Please describe your opt-out mechanism</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 6: Company & Approver Details -->
                            <div id="step-6" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 6: Company & Approver Details</strong> - Provide your company registration and approver information.
                                        </div>
                                        
                                        <div class="alert alert-warning mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Important:</strong> Incorrect or inconsistent information may delay approval.
                                        </div>
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-building me-2 text-primary"></i>Company Information</h6>
                                        
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Company Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="companyNumber" placeholder="e.g., 12345678">
                                                <small class="text-muted">Your registered company number</small>
                                                <div class="invalid-feedback">Please enter your company number</div>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Company Website <span class="text-danger">*</span></label>
                                                <input type="url" class="form-control" id="companyWebsite" placeholder="https://www.yourcompany.com">
                                                <small class="text-muted">Your main company website</small>
                                                <div class="invalid-feedback">Please enter a valid company website URL</div>
                                            </div>
                                            
                                            <div class="col-lg-12 mb-3">
                                                <label class="text-label form-label">Registered Address <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="registeredAddress" rows="3" placeholder="Enter your full registered business address..."></textarea>
                                                <small class="text-muted">Your official registered business address</small>
                                                <div class="invalid-feedback">Please enter your registered address</div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-user-tie me-2 text-primary"></i>Approver Details</h6>
                                        <p class="text-muted small mb-3">The approver is the person authorizing this RCS Agent registration on behalf of your company.</p>
                                        
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Approver Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="approverName" placeholder="e.g., John Smith">
                                                <small class="text-muted">Full name of the authorizing person</small>
                                                <div class="invalid-feedback">Please enter the approver's name</div>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Approver Job Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="approverJobTitle" placeholder="e.g., Marketing Director">
                                                <small class="text-muted">Their role within your organization</small>
                                                <div class="invalid-feedback">Please enter the approver's job title</div>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="text-label form-label">Approver Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="approverEmail" placeholder="e.g., john.smith@yourcompany.com">
                                                <small class="text-muted">Email address for verification communications</small>
                                                <div class="invalid-feedback">Please enter a valid email address</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 7: Test Numbers -->
                            <div id="step-7" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 7: Test Numbers</strong> - Add phone numbers for testing your RCS Agent before going live.
                                        </div>
                                        
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-mobile-alt me-2 text-primary"></i>Test Numbers</h6>
                                        <p class="text-muted small mb-3">Add up to 20 phone numbers for testing. Numbers must be in international format (e.g., +447700900123).</p>
                                        
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="testNumberInput" placeholder="+447700900123">
                                                    <button class="btn btn-primary" type="button" id="addTestNumberBtn">
                                                        <i class="fas fa-plus me-1"></i> Add
                                                    </button>
                                                </div>
                                                <div class="invalid-feedback" id="testNumberError" style="display: none;">Invalid format. Use international format (e.g., +447700900123)</div>
                                                <small class="text-muted">Enter phone number in international format starting with +</small>
                                            </div>
                                        </div>
                                        
                                        <div id="testNumbersList" class="test-numbers-container mb-3"></div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><span id="testNumberCount">0</span>/20 numbers added</small>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" id="clearAllTestNumbers" style="display: none;">
                                                <i class="fas fa-trash-alt me-1"></i> Clear All
                                            </button>
                                        </div>
                                        
                                        <div class="alert alert-light border mt-4" style="font-size: 0.85rem;">
                                            <i class="fas fa-info-circle text-primary me-2"></i>
                                            <strong>Note:</strong> Test numbers are optional but recommended before submitting for approval.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 8: Review & Submit -->
                            <div id="step-8" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 8: Review & Submit</strong> - Please review all information before submitting for approval.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="review-section">
                                                    <h6>Branding & Identity</h6>
                                                    <div class="review-row">
                                                        <span class="review-label">Agent Name</span>
                                                        <span class="review-value" id="reviewAgentName">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Description</span>
                                                        <span class="review-value" id="reviewDescription">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Brand Colour</span>
                                                        <span class="review-value d-flex align-items-center gap-2">
                                                            <span class="color-preview" id="reviewColorPreview" style="width: 18px; height: 18px;"></span>
                                                            <span id="reviewColor">-</span>
                                                        </span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Logo</span>
                                                        <span class="review-value" id="reviewLogo">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Hero Image</span>
                                                        <span class="review-value" id="reviewHero">-</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="review-section">
                                                    <h6>Handset Contact Details</h6>
                                                    <div class="review-row">
                                                        <span class="review-label">Phone</span>
                                                        <span class="review-value" id="reviewPhone">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Website</span>
                                                        <span class="review-value" id="reviewWebsite">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Email</span>
                                                        <span class="review-value" id="reviewEmail">-</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="review-section">
                                                    <h6>Compliance</h6>
                                                    <div class="review-row">
                                                        <span class="review-label">Privacy Policy</span>
                                                        <span class="review-value" id="reviewPrivacy">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Terms of Service</span>
                                                        <span class="review-value" id="reviewTerms">-</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="review-section">
                                                    <h6>Billing & Use Case</h6>
                                                    <div class="review-row">
                                                        <span class="review-label">Billing Category</span>
                                                        <span class="review-value" id="reviewBilling">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Use Case</span>
                                                        <span class="review-value" id="reviewUseCase">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Frequency</span>
                                                        <span class="review-value" id="reviewFrequency">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Monthly Volume</span>
                                                        <span class="review-value" id="reviewVolume">-</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="review-section">
                                                    <h6>Company Information</h6>
                                                    <div class="review-row">
                                                        <span class="review-label">Company Number</span>
                                                        <span class="review-value" id="reviewCompanyNumber">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Approver</span>
                                                        <span class="review-value" id="reviewApproverName">-</span>
                                                    </div>
                                                    <div class="review-row">
                                                        <span class="review-label">Approver Email</span>
                                                        <span class="review-value" id="reviewApproverEmail">-</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="review-section">
                                                    <h6>Test Numbers</h6>
                                                    <div class="review-row">
                                                        <span class="review-label">Numbers Added</span>
                                                        <span class="review-value" id="reviewTestNumbers">0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-warning mt-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Once submitted, your agent will be reviewed by our team. This typically takes 2-5 business days.
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
<script src="{{ asset('js/shared-image-editor.js') }}"></script>
<script>
$(document).ready(function() {
    var wizardData = {
        id: null,
        name: '',
        description: '',
        billing: 'non-conversational',
        useCase: '',
        logoDataUrl: null,
        logoCropMetadata: null,
        heroDataUrl: null,
        heroCropMetadata: null,
        brandColor: '#886CC0',
        website: '',
        privacyUrl: '',
        termsUrl: '',
        supportEmail: '',
        supportPhone: '',
        phoneLabel: 'Call',
        emailLabel: 'Email',
        websiteLabel: 'Website',
        showPhone: true,
        showEmail: true,
        campaignFrequency: '',
        monthlyVolume: '',
        optInDescription: '',
        optOutDescription: '',
        useCaseOverview: '',
        testNumbers: [],
        companyNumber: '',
        companyWebsite: '',
        registeredAddress: '',
        approverName: '',
        approverJobTitle: '',
        approverEmail: '',
        logoValid: false,
        heroValid: false,
        visitedSteps: [0], // Step 0 is visited by default (initial step)
        validatedSteps: [], // Steps that have been validated (user left at least once)
        completedSteps: []
    };
    
    var autosaveTimeout = null;
    
    function triggerAutosave() {
        if (autosaveTimeout) clearTimeout(autosaveTimeout);
        $('#autosaveIndicator').removeClass('saved error').addClass('saving');
        $('#autosaveText').text('Saving...');
        
        autosaveTimeout = setTimeout(function() {
            $('#autosaveIndicator').removeClass('saving error').addClass('saved');
            $('#autosaveText').text('Draft saved');
        }, 1000);
    }
    
    function validateStep(stepNumber) {
        var isValid = true;
        
        $('.form-control.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
        
        if (stepNumber === 0) {
            if (!wizardData.name.trim() || wizardData.name.length > 25) {
                $('#agentName').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.description.trim() || wizardData.description.length > 100) {
                $('#agentDescription').addClass('is-invalid');
                isValid = false;
            }
        } else if (stepNumber === 1) {
            if (!wizardData.logoDataUrl || !wizardData.logoValid) {
                var logoErr = $('#agentLogoError');
                if (logoErr.length && !logoErr.text().trim()) {
                    logoErr.text('Please upload a logo image');
                }
                logoErr.removeClass('d-none');
                isValid = false;
            }
            if (!wizardData.heroDataUrl || !wizardData.heroValid) {
                var heroErr = $('#agentHeroError');
                if (heroErr.length && !heroErr.text().trim()) {
                    heroErr.text('Please upload a hero/banner image');
                }
                heroErr.removeClass('d-none');
                isValid = false;
            }
        } else if (stepNumber === 2) {
            // Check if at least one contact method is displayed
            var phoneDisplayed = wizardData.showPhone && wizardData.supportPhone.trim();
            var emailDisplayed = wizardData.showEmail && wizardData.supportEmail.trim();
            var websiteProvided = wizardData.website.trim();
            
            // If phone toggle is on, phone number is required
            if (wizardData.showPhone && !wizardData.supportPhone.trim()) {
                $('#supportPhone').addClass('is-invalid');
                isValid = false;
            }
            
            // If email toggle is on, email is required
            if (wizardData.showEmail && !wizardData.supportEmail.trim()) {
                $('#supportEmail').addClass('is-invalid');
                isValid = false;
            }
            
            // Website is required if neither phone nor email is displayed
            if (!phoneDisplayed && !emailDisplayed && !websiteProvided) {
                $('#businessWebsite').addClass('is-invalid');
                isValid = false;
            }
            
            // Compliance URLs are always required
            if (!wizardData.privacyUrl.trim()) {
                $('#privacyUrl').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.termsUrl.trim()) {
                $('#termsUrl').addClass('is-invalid');
                isValid = false;
            }
        } else if (stepNumber === 3) {
            if (!wizardData.billing) {
                $('#billingError').show();
                isValid = false;
            }
            if (!wizardData.useCase) {
                $('#useCaseError').show();
                isValid = false;
            }
            if (!wizardData.useCaseOverview.trim()) {
                $('#useCaseOverview').addClass('is-invalid');
                isValid = false;
            }
        } else if (stepNumber === 4) {
            if (!wizardData.campaignFrequency) {
                $('#campaignFrequency').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.monthlyVolume) {
                $('#monthlyVolume').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.optInDescription.trim()) {
                $('#optInDescription').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.optOutDescription.trim()) {
                $('#optOutDescription').addClass('is-invalid');
                isValid = false;
            }
        } else if (stepNumber === 5) {
            if (!wizardData.companyNumber.trim()) {
                $('#companyNumber').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.companyWebsite.trim()) {
                $('#companyWebsite').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.registeredAddress.trim()) {
                $('#registeredAddress').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.approverName.trim()) {
                $('#approverName').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.approverJobTitle.trim()) {
                $('#approverJobTitle').addClass('is-invalid');
                isValid = false;
            }
            if (!wizardData.approverEmail.trim()) {
                $('#approverEmail').addClass('is-invalid');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function populateReviewStep() {
        $('#reviewAgentName').text(wizardData.name || '-');
        $('#reviewDescription').text(wizardData.description || '-');
        $('#reviewColor').text(wizardData.brandColor);
        $('#reviewColorPreview').css('background-color', wizardData.brandColor);
        $('#reviewLogo').html(wizardData.logoValid ? '<span class="badge bg-success">Uploaded</span>' : '<span class="badge bg-secondary">Not uploaded</span>');
        $('#reviewHero').html(wizardData.heroValid ? '<span class="badge bg-success">Uploaded</span>' : '<span class="badge bg-secondary">Not uploaded</span>');
        $('#reviewPhone').text(wizardData.supportPhone ? '+44 ' + wizardData.supportPhone : '-');
        $('#reviewWebsite').text(wizardData.website || '-');
        $('#reviewEmail').text(wizardData.supportEmail || '-');
        $('#reviewPrivacy').text(wizardData.privacyUrl || '-');
        $('#reviewTerms').text(wizardData.termsUrl || '-');
        $('#reviewBilling').text(wizardData.billing ? wizardData.billing.charAt(0).toUpperCase() + wizardData.billing.slice(1) : '-');
        $('#reviewUseCase').text(wizardData.useCase ? wizardData.useCase.charAt(0).toUpperCase() + wizardData.useCase.slice(1) : '-');
        $('#reviewFrequency').text(wizardData.campaignFrequency || '-');
        $('#reviewVolume').text(wizardData.monthlyVolume || '-');
        $('#reviewCompanyNumber').text(wizardData.companyNumber || '-');
        $('#reviewApproverName').text(wizardData.approverName || '-');
        $('#reviewApproverEmail').text(wizardData.approverEmail || '-');
        $('#reviewTestNumbers').text(wizardData.testNumbers.length);
    }
    
    $('#rcsAgentWizard').smartWizard({
        selected: 0,
        theme: 'default',
        autoAdjustHeight: true,
        transition: {
            animation: 'fade'
        },
        toolbar: {
            position: 'bottom',
            showNextButton: true,
            showPreviousButton: true
        },
        anchor: {
            enableNavigation: true,
            enableDoneState: true,
            markPreviousStepsAsDone: true,
            markAllPreviousStepsAsDone: true,
            removeDoneStepOnNavigateBack: false,
            enableDoneStateNavigation: true
        },
        keyboard: {
            keyNavigation: false
        },
        lang: {
            next: 'Next',
            previous: 'Previous'
        }
    });
    
    $('#rcsAgentWizard').on('leaveStep', function(e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {
        // Mark step as validated (user has left it at least once)
        markStepValidated(currentStepIndex);
        
        if (stepDirection === 'forward') {
            // If on the Review step (step 7, index 7), handle final submission
            if (currentStepIndex === 7) {
                return handleFinalSubmission();
            }
            
            var isValid = validateStep(currentStepIndex);
            if (isValid) {
                markStepCompleted(currentStepIndex);
            } else {
                unmarkStepCompleted(currentStepIndex);
            }
            return isValid;
        } else {
            // When going backward, also check if step is valid for visual indicator
            var isValid = checkStepValidity(currentStepIndex);
            if (isValid) {
                markStepCompleted(currentStepIndex);
            } else {
                unmarkStepCompleted(currentStepIndex);
            }
        }
        return true;
    });
    
    // Handle final submission with validation summary
    function handleFinalSubmission() {
        var stepNames = [
            'Agent Basics',
            'Branding Assets',
            'Handset + Compliance',
            'Agent Type',
            'Messaging Behaviour',
            'Company Details',
            'Test Numbers'
        ];
        
        var incompleteSteps = [];
        var allValid = true;
        
        // Check each step (0-6, step 7 is Review which has no required fields)
        for (var i = 0; i <= 6; i++) {
            var isValid = checkStepValidity(i);
            
            if (!isValid) {
                allValid = false;
                
                // Check if step was already validated (user left it at least once)
                // Do NOT auto-validate steps just because submission failed
                var wasValidated = wizardData.validatedSteps.indexOf(i) > -1;
                
                incompleteSteps.push({
                    step: i + 1,
                    name: stepNames[i],
                    visited: wasValidated // Only show as "visited" if user actually left the step
                });
            }
        }
        
        // Do NOT call updateStepIndicators() here - stepper state should not change due to submission
        
        if (!allValid) {
            showSubmissionValidationSummary(incompleteSteps);
            return false;
        }
        
        // All valid - proceed with submission
        submitWizard();
        return false; // Prevent default navigation, we handle it in submitWizard
    }
    
    // Show validation summary modal
    function showSubmissionValidationSummary(incompleteSteps) {
        var visitedIncomplete = incompleteSteps.filter(function(s) { return s.visited; });
        var notVisited = incompleteSteps.filter(function(s) { return !s.visited; });
        
        var summaryHtml = '<div class="submission-validation-summary">';
        
        if (visitedIncomplete.length > 0) {
            summaryHtml += '<div class="mb-3">';
            summaryHtml += '<h6 class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>Incomplete Steps (Please Fix)</h6>';
            summaryHtml += '<ul class="list-unstyled mb-0">';
            visitedIncomplete.forEach(function(s) {
                summaryHtml += '<li class="py-1"><span class="badge bg-danger me-2">Step ' + s.step + '</span>' + s.name + '</li>';
            });
            summaryHtml += '</ul></div>';
        }
        
        if (notVisited.length > 0) {
            summaryHtml += '<div class="mb-3">';
            summaryHtml += '<h6 class="text-muted"><i class="fas fa-info-circle me-2"></i>Steps Not Yet Visited</h6>';
            summaryHtml += '<ul class="list-unstyled mb-0">';
            notVisited.forEach(function(s) {
                summaryHtml += '<li class="py-1 text-muted"><span class="badge bg-secondary me-2">Step ' + s.step + '</span>' + s.name + '</li>';
            });
            summaryHtml += '</ul></div>';
        }
        
        summaryHtml += '</div>';
        
        // Show in existing modal or create alert
        if ($('#validationSummaryModal').length) {
            $('#validationSummaryModal .modal-body').html(summaryHtml);
            $('#validationSummaryModal').modal('show');
        } else {
            // Create a simple modal dynamically
            var modalHtml = '<div class="modal fade" id="validationSummaryModal" tabindex="-1">';
            modalHtml += '<div class="modal-dialog modal-dialog-centered">';
            modalHtml += '<div class="modal-content">';
            modalHtml += '<div class="modal-header bg-light">';
            modalHtml += '<h5 class="modal-title"><i class="fas fa-clipboard-check me-2 text-primary"></i>Submission Blocked</h5>';
            modalHtml += '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
            modalHtml += '</div>';
            modalHtml += '<div class="modal-body">' + summaryHtml + '</div>';
            modalHtml += '<div class="modal-footer">';
            modalHtml += '<button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK, I\'ll Fix These</button>';
            modalHtml += '</div></div></div></div>';
            
            $('body').append(modalHtml);
            $('#validationSummaryModal').modal('show');
        }
    }
    
    // Submit wizard (placeholder for actual submission logic)
    function submitWizard() {
        // TODO: Implement actual submission to backend
        console.log('Wizard submission:', wizardData);
        
        // Show success message for now
        var successHtml = '<div class="text-center py-4">';
        successHtml += '<i class="fas fa-check-circle text-success fa-4x mb-3"></i>';
        successHtml += '<h4>Agent Submitted Successfully!</h4>';
        successHtml += '<p class="text-muted">Your RCS Agent registration has been submitted for review.</p>';
        successHtml += '<p class="text-muted">You will receive an email notification once the review is complete.</p>';
        successHtml += '</div>';
        
        if ($('#validationSummaryModal').length) {
            $('#validationSummaryModal .modal-title').html('<i class="fas fa-check-circle me-2 text-success"></i>Submission Complete');
            $('#validationSummaryModal .modal-body').html(successHtml);
            $('#validationSummaryModal .modal-footer').html('<button type="button" class="btn btn-success" onclick="window.location.href=\'/management/rcs-agents\'">View My Agents</button>');
            $('#validationSummaryModal').modal('show');
        } else {
            var modalHtml = '<div class="modal fade" id="validationSummaryModal" tabindex="-1">';
            modalHtml += '<div class="modal-dialog modal-dialog-centered">';
            modalHtml += '<div class="modal-content">';
            modalHtml += '<div class="modal-header bg-light">';
            modalHtml += '<h5 class="modal-title"><i class="fas fa-check-circle me-2 text-success"></i>Submission Complete</h5>';
            modalHtml += '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
            modalHtml += '</div>';
            modalHtml += '<div class="modal-body">' + successHtml + '</div>';
            modalHtml += '<div class="modal-footer">';
            modalHtml += '<button type="button" class="btn btn-success" onclick="window.location.href=\'/management/rcs-agents\'">View My Agents</button>';
            modalHtml += '</div></div></div></div>';
            
            $('body').append(modalHtml);
            $('#validationSummaryModal').modal('show');
        }
    }
    
    // Mark a step as visited (user entered it)
    function markStepVisited(stepIndex) {
        if (wizardData.visitedSteps.indexOf(stepIndex) === -1) {
            wizardData.visitedSteps.push(stepIndex);
            triggerAutosave();
        }
    }
    
    // Mark a step as validated (user left it at least once)
    function markStepValidated(stepIndex) {
        if (wizardData.validatedSteps.indexOf(stepIndex) === -1) {
            wizardData.validatedSteps.push(stepIndex);
            updateStepIndicators();
            triggerAutosave();
        }
    }
    
    // Mark a step as completed
    function markStepCompleted(stepIndex) {
        if (wizardData.completedSteps.indexOf(stepIndex) === -1) {
            wizardData.completedSteps.push(stepIndex);
            updateStepIndicators();
        }
    }
    
    // Remove step from completed list
    function unmarkStepCompleted(stepIndex) {
        var idx = wizardData.completedSteps.indexOf(stepIndex);
        if (idx > -1) {
            wizardData.completedSteps.splice(idx, 1);
            updateStepIndicators();
        }
    }
    
    // Check step validity without showing error indicators (for visual state only)
    function checkStepValidity(stepNumber) {
        if (stepNumber === 0) {
            return wizardData.name.trim() && wizardData.name.length <= 25 && 
                   wizardData.description.trim() && wizardData.description.length <= 100;
        } else if (stepNumber === 1) {
            return wizardData.logoDataUrl && wizardData.logoValid && 
                   wizardData.heroDataUrl && wizardData.heroValid;
        } else if (stepNumber === 2) {
            var phoneDisplayed = wizardData.showPhone && wizardData.supportPhone.trim();
            var emailDisplayed = wizardData.showEmail && wizardData.supportEmail.trim();
            var websiteProvided = wizardData.website.trim();
            var hasContact = phoneDisplayed || emailDisplayed || websiteProvided;
            var phoneValid = !wizardData.showPhone || wizardData.supportPhone.trim();
            var emailValid = !wizardData.showEmail || wizardData.supportEmail.trim();
            return hasContact && phoneValid && emailValid && 
                   wizardData.privacyUrl.trim() && wizardData.termsUrl.trim();
        } else if (stepNumber === 3) {
            return wizardData.billing && wizardData.useCase && 
                   wizardData.useCaseOverview.trim() && wizardData.useCaseOverview.length <= 500;
        } else if (stepNumber === 4) {
            return wizardData.campaignFrequency && wizardData.optInDescription.trim() && 
                   wizardData.optOutDescription.trim() && wizardData.monthlyVolume;
        } else if (stepNumber === 5) {
            return wizardData.companyNumber.trim() && wizardData.registeredAddress.trim() && 
                   wizardData.approverName.trim() && wizardData.approverJobTitle.trim() && 
                   wizardData.approverEmail.trim();
        } else if (stepNumber === 6) {
            return true; // Test numbers step - no required fields
        }
        return true;
    }
    
    // Get step state: 'not-visited', 'visited-incomplete', 'completed'
    function getStepState(stepIndex) {
        var isValidated = wizardData.validatedSteps.indexOf(stepIndex) > -1;
        var isCompleted = wizardData.completedSteps.indexOf(stepIndex) > -1;
        
        if (isCompleted) return 'completed';
        // Only show incomplete (red) if user has left the step at least once AND it's invalid
        if (isValidated) return 'visited-incomplete';
        return 'not-visited';
    }
    
    // Update visual indicators for all steps
    function updateStepIndicators() {
        $('#rcsAgentWizard .nav-wizard li').each(function(index) {
            var $step = $(this);
            var state = getStepState(index);
            
            $step.removeClass('step-not-visited step-visited-incomplete step-completed');
            $step.addClass('step-' + state);
            
            // Update the nav-link with state class
            $step.find('.nav-link').removeClass('step-not-visited step-visited-incomplete step-completed');
            $step.find('.nav-link').addClass('step-' + state);
        });
    }
    
    // Re-validate a specific step and update its indicator in real-time
    function revalidateStep(stepIndex) {
        // Only revalidate if the step has been validated (user left it at least once)
        if (wizardData.validatedSteps.indexOf(stepIndex) === -1) {
            return;
        }
        
        var isValid = checkStepValidity(stepIndex);
        if (isValid) {
            markStepCompleted(stepIndex);
        } else {
            unmarkStepCompleted(stepIndex);
        }
    }
    
    // Get current step index
    function getCurrentStepIndex() {
        return $('#rcsAgentWizard').smartWizard('getStepIndex');
    }
    
    $('#rcsAgentWizard').on('showStep', function(e, anchorObject, stepIndex, stepDirection) {
        // Mark step as visited when entering
        markStepVisited(stepIndex);
        
        if (stepIndex === 7) {
            populateReviewStep();
        }
        
        var $toolbar = $(this).find('.toolbar');
        if (stepIndex === 7) {
            $toolbar.find('.sw-btn-next').text('Submit');
        } else {
            $toolbar.find('.sw-btn-next').text('Next');
        }
    });
    
    // Initialize step indicators on page load
    updateStepIndicators();
    
    $('#agentName').on('input', function() {
        wizardData.name = this.value;
        $('#nameCharCount').text(this.value.length);
        revalidateStep(0); // Step 1: Agent Basics
        triggerAutosave();
    });
    
    $('#agentDescription').on('input', function() {
        wizardData.description = this.value;
        $('#descCharCount').text(this.value.length);
        revalidateStep(0); // Step 1: Agent Basics
        triggerAutosave();
    });
    
    $('#brandColor').on('input', function() {
        wizardData.brandColor = this.value;
        $('#brandColorHex').val(this.value);
        triggerAutosave();
    });
    
    $('#brandColorHex').on('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            wizardData.brandColor = this.value;
            $('#brandColor').val(this.value);
            triggerAutosave();
        }
    });
    
    window.onagentLogoChange = function(data) {
        if (typeof agentLogoGenerateCroppedImage === 'function') {
            agentLogoGenerateCroppedImage(function(err, dataUrl) {
                if (!err && dataUrl) {
                    var cropData = typeof agentLogoGetCropData === 'function' ? agentLogoGetCropData() : null;
                    wizardData.logoDataUrl = dataUrl;
                    wizardData.logoValid = true;
                    wizardData.logoCropMetadata = {
                        originalSrc: data.originalSrc || null,
                        crop: cropData ? cropData.crop : null,
                        zoom: cropData ? cropData.zoom : 1,
                        outputWidth: 222,
                        outputHeight: 222,
                        aspectRatio: '1:1',
                        frameShape: 'circle',
                        timestamp: new Date().toISOString()
                    };
                    $('#agentLogoError').addClass('d-none');
                    revalidateStep(1); // Step 2: Branding Assets
                    triggerAutosave();
                }
            });
        }
    };
    
    window.onagentLogoRemove = function() {
        wizardData.logoDataUrl = null;
        revalidateStep(1); // Step 2: Branding Assets
        wizardData.logoValid = false;
        wizardData.logoCropMetadata = null;
        triggerAutosave();
    };
    
    window.onagentHeroChange = function(data) {
        if (typeof agentHeroGenerateCroppedImage === 'function') {
            agentHeroGenerateCroppedImage(function(err, dataUrl) {
                if (!err && dataUrl) {
                    var cropData = typeof agentHeroGetCropData === 'function' ? agentHeroGetCropData() : null;
                    wizardData.heroDataUrl = dataUrl;
                    wizardData.heroValid = true;
                    wizardData.heroCropMetadata = {
                        originalSrc: data.originalSrc || null,
                        crop: cropData ? cropData.crop : null,
                        zoom: cropData ? cropData.zoom : 1,
                        outputWidth: 1480,
                        outputHeight: 448,
                        aspectRatio: '45:14',
                        frameShape: 'rectangle',
                        timestamp: new Date().toISOString()
                    };
                    $('#agentHeroError').addClass('d-none');
                    revalidateStep(1); // Step 2: Branding Assets
                    triggerAutosave();
                }
            });
        }
    };
    
    window.onagentHeroRemove = function() {
        wizardData.heroDataUrl = null;
        revalidateStep(1); // Step 2: Branding Assets
        wizardData.heroValid = false;
        wizardData.heroCropMetadata = null;
        triggerAutosave();
    };
    
    $('#supportPhone, #businessWebsite, #supportEmail, #privacyUrl, #termsUrl').on('input', function() {
        var id = this.id;
        var key = id === 'businessWebsite' ? 'website' : id;
        wizardData[key] = this.value;
        revalidateStep(2); // Step 3: Handset + Compliance
        triggerAutosave();
    });
    
    // Label fields
    $('#phoneLabel, #emailLabel, #websiteLabel').on('input', function() {
        wizardData[this.id] = this.value;
        triggerAutosave();
    });
    
    // Update website required status based on phone/email toggles
    function updateWebsiteRequired() {
        var phoneDisplayed = $('#showPhoneToggle').is(':checked') && $('#supportPhone').val().trim();
        var emailDisplayed = $('#showEmailToggle').is(':checked') && $('#supportEmail').val().trim();
        var isRequired = !phoneDisplayed && !emailDisplayed;
        
        $('#websiteRequired').toggle(isRequired);
        $('#websiteRequiredNote').text(isRequired ? 'Required - no other contact displayed' : 'Optional - other contacts are displayed');
    }
    
    $('#showPhoneToggle, #showEmailToggle').on('change', function() {
        var key = this.id.replace('Toggle', '').replace('show', 'show');
        wizardData[key] = this.checked;
        updateWebsiteRequired();
        revalidateStep(2); // Step 3: Handset + Compliance
        triggerAutosave();
    });
    
    // Also update when phone/email values change
    $('#supportPhone, #supportEmail').on('input', function() {
        updateWebsiteRequired();
    });
    
    // Tile info descriptions for Learn More modals
    var tileDescriptions = {
        'non-conversational': {
            title: 'Non-conversational',
            description: 'For agents who send messages without expecting frequent replies.'
        },
        'conversational': {
            title: 'Conversational',
            description: 'For agents that support multi-turn conversations with users. To be cost-effective, a conversation should include at least three outbound messages within a 24-hour period, a user must reply.'
        },
        'otp': {
            title: 'OTP',
            description: 'One-time passwords required to securely authenticate an account or confirm a transaction.'
        },
        'transactional': {
            title: 'Transactional',
            description: 'Notifications, updates, or alerts that share information directly relevant to a customer\'s existing services or products, such as alerts for suspicious account activities, purchase confirmations, and shipping notifications.'
        },
        'promotional': {
            title: 'Promotional',
            description: 'Sales, marketing, and promotional messages to new or existing customers, with the goal of increasing awareness, engagement, and sales.'
        },
        'multi-use': {
            title: 'Multi-use',
            description: 'Conversations that combine transactional and promotional messages, such as sending an account notification followed by a discount offer or upgrading to a new product or service.'
        }
    };
    
    // Learn More button handler - show popover modal
    $(document).on('click', '.btn-tile-learn-more', function(e) {
        e.stopPropagation(); // Prevent tile selection
        
        var infoKey = $(this).data('tile-info');
        var info = tileDescriptions[infoKey];
        
        if (!info) return;
        
        // Create or update modal
        if (!$('#tileInfoModal').length) {
            var modalHtml = '<div class="modal fade" id="tileInfoModal" tabindex="-1">';
            modalHtml += '<div class="modal-dialog modal-dialog-centered modal-sm">';
            modalHtml += '<div class="modal-content">';
            modalHtml += '<div class="modal-header bg-light py-2">';
            modalHtml += '<h6 class="modal-title" id="tileInfoModalTitle"></h6>';
            modalHtml += '<button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>';
            modalHtml += '</div>';
            modalHtml += '<div class="modal-body" id="tileInfoModalBody"></div>';
            modalHtml += '</div></div></div>';
            $('body').append(modalHtml);
        }
        
        $('#tileInfoModalTitle').text(info.title);
        $('#tileInfoModalBody').html('<p class="mb-0">' + info.description + '</p>');
        $('#tileInfoModal').modal('show');
    });
    
    $('.billing-tile').on('click', function(e) {
        // Don't trigger if clicking on Learn More button
        if ($(e.target).closest('.btn-tile-learn-more').length) return;
        
        $('.billing-tile').removeClass('selected');
        $(this).addClass('selected');
        wizardData.billing = $(this).data('billing');
        $('#billingError').hide();
        revalidateStep(3); // Step 4: Agent Type
        triggerAutosave();
    });
    
    $('.usecase-tile').on('click', function(e) {
        // Don't trigger if clicking on Learn More button
        if ($(e.target).closest('.btn-tile-learn-more').length) return;
        
        $('.usecase-tile').removeClass('selected');
        $(this).addClass('selected');
        wizardData.useCase = $(this).data('usecase');
        $('#useCaseError').hide();
        revalidateStep(3); // Step 4: Agent Type
        triggerAutosave();
    });
    
    $('#useCaseOverview').on('input', function() {
        wizardData.useCaseOverview = this.value;
        $('#useCaseCharCount').text(this.value.length);
        revalidateStep(3); // Step 4: Agent Type
        triggerAutosave();
    });
    
    $('#campaignFrequency, #monthlyVolume').on('change', function() {
        wizardData[this.id] = this.value;
        revalidateStep(4); // Step 5: Messaging Behaviour
        triggerAutosave();
    });
    
    $('#optInDescription').on('input', function() {
        wizardData.optInDescription = this.value;
        $('#optInCharCount').text(this.value.length);
        revalidateStep(4); // Step 5: Messaging Behaviour
        triggerAutosave();
    });
    
    $('#optOutDescription').on('input', function() {
        wizardData.optOutDescription = this.value;
        $('#optOutCharCount').text(this.value.length);
        revalidateStep(4); // Step 5: Messaging Behaviour
        triggerAutosave();
    });
    
    $('#companyNumber, #companyWebsite, #registeredAddress, #approverName, #approverJobTitle, #approverEmail').on('input', function() {
        wizardData[this.id] = this.value;
        revalidateStep(5); // Step 6: Company Details
        triggerAutosave();
    });
    
    function renderTestNumbers() {
        var html = '';
        wizardData.testNumbers.forEach(function(num, index) {
            html += '<span class="test-number-badge">' + num + 
                    '<span class="remove-btn" data-index="' + index + '"><i class="fas fa-times"></i></span></span>';
        });
        $('#testNumbersList').html(html);
        $('#testNumberCount').text(wizardData.testNumbers.length);
        $('#clearAllTestNumbers').toggle(wizardData.testNumbers.length > 0);
    }
    
    $('#addTestNumberBtn').on('click', function() {
        var input = $('#testNumberInput');
        var number = input.val().trim();
        
        if (!number) return;
        
        if (!/^\+[0-9]{10,15}$/.test(number)) {
            $('#testNumberError').show();
            input.addClass('is-invalid');
            return;
        }
        
        if (wizardData.testNumbers.length >= 20) {
            return;
        }
        
        if (wizardData.testNumbers.includes(number)) {
            return;
        }
        
        wizardData.testNumbers.push(number);
        input.val('').removeClass('is-invalid');
        $('#testNumberError').hide();
        renderTestNumbers();
        triggerAutosave();
    });
    
    $('#testNumberInput').on('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#addTestNumberBtn').click();
        }
    });
    
    $(document).on('click', '.test-number-badge .remove-btn', function() {
        var index = $(this).data('index');
        wizardData.testNumbers.splice(index, 1);
        renderTestNumbers();
        triggerAutosave();
    });
    
    $('#clearAllTestNumbers').on('click', function() {
        wizardData.testNumbers = [];
        renderTestNumbers();
        triggerAutosave();
    });
});
</script>
@endpush

@extends('layouts.quicksms')

@section('title', 'Account Details')

@push('styles')
<style>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}
.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}
.breadcrumb-item.active {
    font-weight: 500;
}
.highlight-box {
    background: rgba(136, 108, 192, 0.08);
    border: 1px solid rgba(136, 108, 192, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.highlight-box h6 {
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 600;
}
.highlight-box p {
    color: #6c757d;
    margin-bottom: 0;
    font-size: 0.875rem;
}
.accordion-primary .accordion-button {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    padding: 1rem 1.25rem;
}
.accordion-primary .accordion-button:not(.collapsed) {
    background: rgba(136, 108, 192, 0.08);
    color: var(--primary);
    box-shadow: none;
}
.accordion-primary .accordion-button:focus {
    box-shadow: none;
    border-color: rgba(136, 108, 192, 0.3);
}
.accordion-primary .accordion-button::after {
    background-size: 1rem;
}
.accordion-primary .accordion-item {
    border: 1px solid #e9ecef;
    margin-bottom: 0.75rem;
    border-radius: 0.5rem !important;
    overflow: hidden;
}
.accordion-primary .accordion-body {
    padding: 1.25rem;
    background: #fff;
}
.section-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.7rem;
    font-weight: 500;
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    margin-left: 0.75rem;
}
.section-indicator.required {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}
.section-indicator.optional {
    background: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}
.section-indicator.complete {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}
.form-label {
    font-weight: 500;
    color: #495057;
    font-size: 0.875rem;
    margin-bottom: 0.375rem;
}
.form-label .required-indicator {
    color: #dc3545;
    margin-left: 0.125rem;
}
.form-label .optional-indicator {
    color: #6c757d;
    font-weight: 400;
    font-size: 0.75rem;
    margin-left: 0.25rem;
}
.form-control, .form-select {
    font-size: 0.875rem;
}
.form-control:focus, .form-select:focus {
    border-color: rgba(136, 108, 192, 0.5);
    box-shadow: 0 0 0 0.2rem rgba(136, 108, 192, 0.15);
}
.field-group {
    margin-bottom: 0.5rem;
}
.field-group:last-child {
    margin-bottom: 0;
}
.card-body > .row,
.accordion-body > .row {
    margin-bottom: 2rem !important;
}
.card-body > .row:last-child,
.accordion-body > .row:last-child {
    margin-bottom: 0 !important;
}
.field-hint {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
    margin-bottom: 0.5rem;
}
.col-md-6 {
    margin-bottom: 1.25rem;
}
.section-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
    margin-top: 1.5rem;
}
.validation-error {
    font-size: 0.75rem;
    color: #dc3545;
    margin-top: 0.25rem;
    display: none;
}
.is-invalid ~ .validation-error {
    display: block;
}
.readonly-value {
    padding: 0.5rem 0.75rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #495057;
}
.auto-save-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #6c757d;
}
.auto-save-indicator.saving {
    color: var(--primary);
}
.auto-save-indicator.saved {
    color: #28a745;
}
.usage-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    margin-top: 0.5rem;
}
.usage-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: rgba(136, 108, 192, 0.08);
    color: var(--primary);
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 500;
}
.selectable-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    background: #fff;
    text-align: center;
}
.selectable-tile:hover {
    border-color: rgba(136, 108, 192, 0.4);
    background: rgba(136, 108, 192, 0.03);
}
.selectable-tile.selected {
    border-color: var(--primary);
    background: rgba(136, 108, 192, 0.08);
}
.selectable-tile .tile-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.25rem;
}
.selectable-tile .tile-check {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    color: var(--primary);
    font-size: 1rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.selectable-tile.selected .tile-check {
    opacity: 1;
}
.selectable-tile .tile-title {
    font-weight: 600;
    font-size: 0.875rem;
    color: #333;
    margin-bottom: 0.25rem;
}
.selectable-tile .tile-desc {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0;
    line-height: 1.3;
}
.company-lookup-row {
    display: flex;
    gap: 0.5rem;
    align-items: flex-start;
}
.company-lookup-row .form-control {
    flex: 1;
}
.lookup-status {
    font-size: 0.75rem;
    margin-top: 0.25rem;
}
.lookup-status.success {
    color: #28a745;
}
.lookup-status.error {
    color: #dc3545;
}
.lookup-status.loading {
    color: var(--primary);
}
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 0.875rem 1.25rem;
    border-radius: 0.5rem;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 9999;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
    max-width: 350px;
    font-size: 0.875rem;
}
.toast-notification.show {
    opacity: 1;
    transform: translateX(0);
}
.toast-notification.toast-error {
    border-left: 4px solid #dc3545;
    color: #dc3545;
}
.toast-notification.toast-warning {
    border-left: 4px solid #ffc107;
    color: #856404;
}
.toast-notification.toast-success {
    border-left: 4px solid #28a745;
    color: #28a745;
}
#companyNumber.is-valid {
    border-color: #28a745;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    padding-right: calc(1.5em + 0.75rem);
}
/* Pricing Tab Styles */
.pricing-display-card {
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
    overflow: hidden;
    transition: all 0.2s ease;
}
.pricing-display-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.pricing-display-card.pricing-active {
    border: 2px solid var(--primary);
    position: relative;
}
.pricing-display-card .active-badge {
    position: absolute;
    top: -1px;
    right: 1rem;
    background: var(--primary);
    color: #fff;
    padding: 0.25rem 0.75rem;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 0 0 0.375rem 0.375rem;
    z-index: 10;
}
.pricing-header-starter {
    background: linear-gradient(135deg, #1cbb8c 0%, #17a673 100%);
}
.pricing-header-enterprise {
    background: linear-gradient(135deg, #6f42c1 0%, #886ab5 100%);
}
.pricing-header-bespoke {
    background: linear-gradient(135deg, #D653C1 0%, #886ab5 100%);
}
.pricing-rates {
    padding: 0;
}
.rate-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.rate-row:last-child {
    border-bottom: none;
}
.pricing-section {
    margin-bottom: 1rem;
}
.pricing-section:last-of-type {
    margin-bottom: 0;
}
.pricing-section-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}
.rate-info {
    display: flex;
    flex-direction: column;
}
.rate-label {
    font-size: 0.875rem;
    color: #333;
    font-weight: 500;
}
.rate-basis {
    font-size: 0.7rem;
    color: #6c757d;
    font-style: italic;
}
.rate-value {
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary);
}
.pricing-features li {
    padding: 0.375rem 0;
    font-size: 0.875rem;
    color: #495057;
}
.pricing-badges {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}
.pricing-badge {
    background: #f8f9fa;
    border: none;
    border-radius: 0.5rem;
    padding: 0.625rem 0.375rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.pricing-badge .badge-price {
    font-size: 0.875rem;
    font-weight: 700;
    color: #2c2c2c;
    white-space: nowrap;
    margin-bottom: 0.125rem;
}
.pricing-badge .badge-label {
    font-size: 0.65rem;
    color: #6c757d;
    font-weight: 400;
    white-space: nowrap;
}
.pricing-error-state,
.pricing-access-denied {
    background: #f8f9fa;
    border-radius: 0.5rem;
    border: 1px dashed #dee2e6;
}
.pricing-error-state .error-icon,
.pricing-access-denied .access-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #fff3cd;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.pricing-error-state .error-icon i {
    font-size: 1.5rem;
    color: #856404;
}
.pricing-access-denied .access-icon {
    background: #e9ecef;
}
.pricing-access-denied .access-icon i {
    font-size: 1.5rem;
    color: #6c757d;
}
.pricing-loading-state {
    background: #f8f9fa;
    border-radius: 0.5rem;
    border: 1px dashed #dee2e6;
}
#refreshPricingBtn.loading i {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
}
.nav-tabs .nav-link:hover {
    color: var(--primary);
    border-color: transparent;
}
.nav-tabs .nav-link.active {
    color: var(--primary);
    background: transparent;
    border-color: transparent transparent var(--primary) transparent;
}
.usage-chip i {
    font-size: 0.6rem;
}
</style>
@endpush

@section('content')
<div class="row page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('account') }}">Account</a></li>
        <li class="breadcrumb-item active"><a href="javascript:void(0)">Details</a></li>
    </ol>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs" id="accountTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#detailsContent" type="button" role="tab" aria-controls="detailsContent" aria-selected="true">
            <i class="fas fa-building me-2"></i>Details
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricingContent" type="button" role="tab" aria-controls="pricingContent" aria-selected="false">
            <i class="fas fa-tags me-2"></i>Pricing
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="accountTabsContent">
    <!-- Details Tab -->
    <div class="tab-pane fade show active" id="detailsContent" role="tabpanel" aria-labelledby="details-tab">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-body">
                <div class="alert alert-pastel-primary mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                        <div>
                            <strong>Account Information Centre</strong>
                            <p class="mb-0 mt-1 small">This is the authoritative source for your company information. Data entered here is automatically shared with RCS Agent Registration, SMS SenderID Registration, Billing, VAT handling, Support tickets, and Compliance records.</p>
                        </div>
                    </div>
                </div>
                
                <div class="accordion accordion-primary" id="accountDetailsAccordion">
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#signUpDetails" aria-expanded="true">
                        <i class="fas fa-user-plus me-2 text-primary"></i>Sign Up Details
                        <span class="section-indicator complete" id="signUpStatusBadge"><i class="fas fa-check-circle"></i> Complete</span>
                    </button>
                </h2>
                <div id="signUpDetails" class="accordion-collapse collapse show" data-bs-parent="#accountDetailsAccordion">
                    <div class="accordion-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted small mb-0">All fields are mandatory. Editable by Account Owner or Admin only.</p>
                            <span class="badge badge-pastel-primary"><i class="fas fa-user-shield me-1"></i>Admin / Owner Only</span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">First Name<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control signup-field" id="signupFirstName" value="Sarah" data-field="firstName">
                                    <div class="validation-error">First name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Last Name<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control signup-field" id="signupLastName" value="Johnson" data-field="lastName">
                                    <div class="validation-error">Last name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Job Title<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control signup-field" id="signupJobTitle" value="Account Director" data-field="jobTitle">
                                    <div class="validation-error">Job title is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Business Name<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control signup-field" id="signupBusinessName" value="Acme Communications Ltd" data-field="businessName">
                                    <div class="field-hint">Legal registered company name</div>
                                    <div class="validation-error">Business name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Business Email Address<span class="required-indicator">*</span></label>
                                    <input type="email" class="form-control signup-field" id="signupEmail" value="sarah.johnson@acmecomms.co.uk" data-field="email">
                                    <div class="field-hint">Must be unique across the platform</div>
                                    <div class="validation-error" id="emailError">Please enter a valid email address</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Mobile Number<span class="required-indicator">*</span></label>
                                    <input type="tel" class="form-control signup-field" id="signupMobile" value="+44 7700 900123" placeholder="+44 7XXX XXXXXX" data-field="mobile">
                                    <div class="field-hint">E.164 format preferred (e.g., +447700900123)</div>
                                    <div class="validation-error" id="mobileError">Please enter a valid mobile number</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-actions">
                            <span class="auto-save-indicator" id="signUpAutoSave"></span>
                            <button type="button" class="btn btn-primary btn-sm" id="saveSignUpDetails">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#companyInfo" aria-expanded="false">
                        <i class="fas fa-building me-2 text-primary"></i>Company Information
                        <span class="section-indicator required" id="companyStatusBadge"><i class="fas fa-exclamation-circle"></i> Required to go live</span>
                    </button>
                </h2>
                <div id="companyInfo" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                    <div class="accordion-body">
                        <p class="text-muted small mb-3">Complete all required fields to enable go-live. This information is used across RCS, SMS SenderID, and billing systems.</p>
                        
                        <!-- Company Type Tile Selector -->
                        <div class="field-group mb-4">
                            <label class="form-label">Company Type<span class="required-indicator">*</span></label>
                            <div class="row g-3" id="companyTypeSelector">
                                <div class="col-md-4">
                                    <div class="selectable-tile company-type-tile" data-type="uk_limited">
                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                        <div class="tile-icon bg-pastel-primary"><i class="fas fa-building"></i></div>
                                        <h6 class="tile-title">UK Limited</h6>
                                        <p class="tile-desc">Private or public limited company registered with Companies House</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="selectable-tile company-type-tile" data-type="sole_trader">
                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                        <div class="tile-icon bg-pastel-warning"><i class="fas fa-user-tie"></i></div>
                                        <h6 class="tile-title">Sole Trader</h6>
                                        <p class="tile-desc">Self-employed individual trading under their own name</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="selectable-tile company-type-tile" data-type="government">
                                        <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                        <div class="tile-icon bg-pastel-info"><i class="fas fa-landmark"></i></div>
                                        <h6 class="tile-title">Local, Central Government and NHS</h6>
                                        <p class="tile-desc">Public sector organisations and health services</p>
                                    </div>
                                </div>
                            </div>
                            <div class="validation-error" id="companyTypeError">Please select a company type</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Company Name<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control company-field" id="companyName" value="">
                                    <div class="field-hint">Legal registered company name</div>
                                    <div class="validation-error">Company name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Trading Name<span class="optional-indicator">(Optional)</span></label>
                                    <input type="text" class="form-control" id="tradingName" value="" placeholder="If different from legal name">
                                    <div class="field-hint">Only if trading under a different name</div>
                                </div>
                            </div>
                            <div class="col-md-6" id="companyNumberGroup">
                                <div class="field-group">
                                    <label class="form-label" id="companyNumberLabel">Company Number<span class="required-indicator" id="companyNumberRequired">*</span></label>
                                    <div class="company-lookup-row" id="companyLookupRow">
                                        <input type="text" class="form-control company-field" id="companyNumber" value="" placeholder="e.g., 12345678">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="lookupCompanyBtn" style="display: none;">
                                            <i class="fas fa-search me-1"></i>Lookup
                                        </button>
                                    </div>
                                    <div class="field-hint" id="companyNumberHint">Companies House registration number</div>
                                    <div class="lookup-status" id="lookupStatus"></div>
                                    <div class="validation-error" id="companyNumberError">Company number is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Sector<span class="required-indicator">*</span></label>
                                    <select class="form-select company-field" id="companySector">
                                        <option value="">Select sector...</option>
                                        <option value="telecommunications" selected>Telecommunications & Media</option>
                                        <option value="financial">Financial Services</option>
                                        <option value="healthcare">Healthcare</option>
                                        <option value="retail">Retail & E-commerce</option>
                                        <option value="travel">Travel & Hospitality</option>
                                        <option value="education">Education</option>
                                        <option value="government">Government & Public Sector</option>
                                        <option value="technology">Technology</option>
                                        <option value="manufacturing">Manufacturing</option>
                                        <option value="professional">Professional Services</option>
                                        <option value="utilities">Utilities & Energy</option>
                                        <option value="logistics">Logistics & Transport</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="validation-error">Please select a sector</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Primary Website<span class="required-indicator">*</span></label>
                                    <input type="url" class="form-control company-field" id="companyWebsite" value="https://www.acmecomms.co.uk" placeholder="https://www.example.com">
                                    <div class="field-hint">Must start with https://</div>
                                    <div class="validation-error" id="websiteError">Please enter a valid website URL starting with https://</div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Registered Address</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Address Line 1<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control company-field" id="regAddress1" value="123 Business Park">
                                    <div class="validation-error">Address is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Address Line 2<span class="optional-indicator">(Optional)</span></label>
                                    <input type="text" class="form-control" id="regAddress2" value="Tech Quarter, Floor 5">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="form-label">City<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control company-field" id="regCity" value="London">
                                    <div class="validation-error">City is required</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="form-label">County / Region<span class="optional-indicator">(Optional)</span></label>
                                    <input type="text" class="form-control" id="regCounty" value="Greater London">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="form-label">Postcode<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control company-field" id="regPostcode" value="EC1A 1BB">
                                    <div class="validation-error">Postcode is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Country<span class="required-indicator">*</span></label>
                                    <select class="form-select company-field" id="regCountry">
                                        <option value="">Select country...</option>
                                        <option value="UK" selected>United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                        <option value="IE">Ireland</option>
                                        <option value="NL">Netherlands</option>
                                        <option value="ES">Spain</option>
                                        <option value="IT">Italy</option>
                                    </select>
                                    <div class="validation-error">Country is required</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mt-4 mb-3">
                            <input class="form-check-input" type="checkbox" id="operatingSameAsRegistered" checked>
                            <label class="form-check-label" for="operatingSameAsRegistered">
                                Operating address same as registered address
                            </label>
                        </div>
                        
                        <div id="operatingAddressSection" style="display: none;">
                            <h6 class="fw-bold mb-3"><i class="fas fa-building me-2 text-primary"></i>Operating Address</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label class="form-label">Address Line 1<span class="required-indicator">*</span></label>
                                        <input type="text" class="form-control operating-field" id="opAddress1" value="">
                                        <div class="validation-error">Address is required</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label class="form-label">Address Line 2<span class="optional-indicator">(Optional)</span></label>
                                        <input type="text" class="form-control" id="opAddress2" value="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-group">
                                        <label class="form-label">City<span class="required-indicator">*</span></label>
                                        <input type="text" class="form-control operating-field" id="opCity" value="">
                                        <div class="validation-error">City is required</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-group">
                                        <label class="form-label">County / Region<span class="optional-indicator">(Optional)</span></label>
                                        <input type="text" class="form-control" id="opCounty" value="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-group">
                                        <label class="form-label">Postcode<span class="required-indicator">*</span></label>
                                        <input type="text" class="form-control operating-field" id="opPostcode" value="">
                                        <div class="validation-error">Postcode is required</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label class="form-label">Country<span class="required-indicator">*</span></label>
                                        <select class="form-select operating-field" id="opCountry">
                                            <option value="">Select country...</option>
                                            <option value="UK">United Kingdom</option>
                                            <option value="US">United States</option>
                                            <option value="DE">Germany</option>
                                            <option value="FR">France</option>
                                            <option value="IE">Ireland</option>
                                            <option value="NL">Netherlands</option>
                                            <option value="ES">Spain</option>
                                            <option value="IT">Italy</option>
                                        </select>
                                        <div class="validation-error">Country is required</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-actions">
                            <span class="auto-save-indicator" id="companyAutoSave"></span>
                            <button type="button" class="btn btn-primary btn-sm" id="saveCompanyInfo">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#supportOperations" aria-expanded="false">
                        <i class="fas fa-headset me-2 text-primary"></i>Support & Operations
                        <span class="section-indicator required" id="supportStatusBadge"><i class="fas fa-exclamation-circle"></i> Required to go live</span>
                    </button>
                </h2>
                <div id="supportOperations" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                    <div class="accordion-body">
                        <p class="text-muted small mb-4">Configure email addresses for billing notifications, support communications, and incident alerts. Shared or group inboxes are accepted.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Accounts & Billing Email<span class="required-indicator">*</span></label>
                                    <input type="email" class="form-control support-field" id="billingEmail" value="accounts@acmecomms.co.uk" placeholder="e.g., accounts@company.com">
                                    <div class="field-hint">Receives invoices, payment confirmations, and billing alerts</div>
                                    <div class="validation-error">Please enter a valid email address</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Support Email Address<span class="required-indicator">*</span></label>
                                    <input type="email" class="form-control support-field" id="supportEmail" value="support@acmecomms.co.uk" placeholder="e.g., support@company.com">
                                    <div class="field-hint">Receives support ticket updates and general communications</div>
                                    <div class="validation-error">Please enter a valid email address</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Incident Email Address<span class="required-indicator">*</span></label>
                                    <input type="email" class="form-control support-field" id="incidentEmail" value="incidents@acmecomms.co.uk" placeholder="e.g., incidents@company.com">
                                    <div class="field-hint">Receives urgent incident alerts and service disruption notices</div>
                                    <div class="validation-error">Please enter a valid email address</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-actions">
                            <span class="auto-save-indicator" id="supportAutoSave"></span>
                            <button type="button" class="btn btn-primary btn-sm" id="saveSupportOps">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contractSignatory" aria-expanded="false">
                        <i class="fas fa-signature me-2 text-primary"></i>Contract Signatory
                        <span class="section-indicator required" id="signatoryStatusBadge"><i class="fas fa-exclamation-circle"></i> Required to go live</span>
                    </button>
                </h2>
                <div id="contractSignatory" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                    <div class="accordion-body">
                        <p class="text-muted small mb-4">The contract signatory is the individual authorised to enter contracts on behalf of your company. This person will receive legal notices and approval requests.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Full Name<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control signatory-field" id="signatoryName" value="James Wilson" placeholder="e.g., John Smith">
                                    <div class="validation-error">Full name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Job Title<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control signatory-field" id="signatoryTitle" value="Managing Director" placeholder="e.g., CEO, Managing Director">
                                    <div class="validation-error">Job title is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Email Address<span class="required-indicator">*</span></label>
                                    <input type="email" class="form-control signatory-field" id="signatoryEmail" value="j.wilson@acmecomms.co.uk" placeholder="e.g., signatory@company.com">
                                    <div class="field-hint">Used for contract signing and legal communications</div>
                                    <div class="alert alert-warning domain-warning py-2 px-3 mt-2" id="signatoryDomainWarning" style="display: none;">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span>Email domain does not match your company website domain. Please verify this is correct.</span>
                                    </div>
                                    <div class="validation-error">Please enter a valid email address</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-actions">
                            <span class="auto-save-indicator" id="signatoryAutoSave"></span>
                            <button type="button" class="btn btn-primary btn-sm" id="saveSignatory">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#vatTaxInfo" aria-expanded="false">
                        <i class="fas fa-receipt me-2 text-primary"></i>Billing, VAT and Tax Information
                        <span class="section-indicator required" id="vatStatusBadge"><i class="fas fa-exclamation-circle"></i> Required to go live</span>
                    </button>
                </h2>
                <div id="vatTaxInfo" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                    <div class="accordion-body">
                        <p class="text-muted small mb-4">Billing and VAT settings are used for invoice generation. Changes to these details are audit-logged.</p>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Purchase Order Number</label>
                                    <input type="text" class="form-control" id="purchaseOrderNumber" value="" placeholder="e.g., PO-12345">
                                    <div class="field-hint">Optional: This number will be included on all invoices</div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">VAT Registered<span class="required-indicator">*</span></label>
                                    <select class="form-select vat-field" id="vatRegistered">
                                        <option value="">Select...</option>
                                        <option value="yes" selected>Yes - VAT registered</option>
                                        <option value="no">No - Not VAT registered</option>
                                    </select>
                                    <div class="validation-error">Please select VAT registration status</div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="vatDetailsSection">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label class="form-label">VAT Number<span class="required-indicator">*</span></label>
                                        <input type="text" class="form-control vat-detail-field" id="vatNumber" value="" placeholder="e.g., GB123456789">
                                        <div class="field-hint" id="vatFormatHint">Format: GB followed by 9 digits</div>
                                        <div class="vat-verification-status small mt-1" id="vatVerificationStatus" style="display: none;"></div>
                                        <div class="validation-error" id="vatNumberError">VAT number is required</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label class="form-label">VAT Country<span class="required-indicator">*</span></label>
                                        <select class="form-select vat-detail-field" id="vatCountry">
                                            <option value="">Select country...</option>
                                            <option value="GB" selected>United Kingdom (GB)</option>
                                            <option value="DE">Germany (DE)</option>
                                            <option value="FR">France (FR)</option>
                                            <option value="IE">Ireland (IE)</option>
                                            <option value="NL">Netherlands (NL)</option>
                                            <option value="ES">Spain (ES)</option>
                                            <option value="IT">Italy (IT)</option>
                                            <option value="BE">Belgium (BE)</option>
                                            <option value="AT">Austria (AT)</option>
                                            <option value="PL">Poland (PL)</option>
                                        </select>
                                        <div class="validation-error">Please select a VAT country</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label class="form-label">
                                            Reverse Charges<span class="required-indicator">*</span>
                                            <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<strong>No</strong> = You are sending messages to your own customers<br><br><strong>Yes</strong> = You are providing messaging as a service to third parties (reverse charge applies)"></i>
                                        </label>
                                        <select class="form-select vat-detail-field" id="reverseCharges">
                                            <option value="">Select...</option>
                                            <option value="no" selected>No - Messaging to own customers</option>
                                            <option value="yes">Yes - Messaging provided as a service</option>
                                        </select>
                                        <div class="field-hint">Determines how VAT is applied on invoices</div>
                                        <div class="validation-error">Please select reverse charges option</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-actions">
                            <span class="auto-save-indicator" id="vatAutoSave"></span>
                            <button type="button" class="btn btn-primary btn-sm" id="saveVatInfo">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pricing Tab -->
    <div class="tab-pane fade" id="pricingContent" role="tabpanel" aria-labelledby="pricing-tab">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-body">
        @php
            // Permission check - only Admin/Owner can view pricing
            $canViewPricing = true; // TODO: Replace with auth check: auth()->user()->hasRole(['admin', 'owner'])
            $currentUserRole = 'admin'; // Mock: admin, owner, user
        @endphp
        
        @if(!$canViewPricing)
            <!-- Access Denied State -->
            <div class="pricing-access-denied text-center py-5">
                <div class="access-icon mb-3">
                    <i class="fas fa-lock"></i>
                </div>
                <h5 class="mb-2">Access Restricted</h5>
                <p class="text-muted mb-0">Pricing information is only available to Account Owners and Administrators.</p>
            </div>
        @else
            <!-- Loading State -->
            <div id="pricingLoadingState" class="pricing-loading-state text-center py-5" style="display: none;">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mb-0">Loading pricing from HubSpot...</p>
            </div>
            
            <!-- Error State -->
            <div id="pricingErrorState" class="pricing-error-state text-center py-5" style="display: none;">
                <div class="error-icon mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="mb-2">Unable to Load Pricing</h5>
                <p class="text-muted mb-3" id="pricingErrorMessage">Failed to fetch pricing data. Please try again.</p>
                <button class="btn btn-primary" id="retryPricingBtn">
                    <i class="fas fa-sync-alt me-2"></i>Retry
                </button>
            </div>
            
            <!-- Pricing Content -->
            <div id="pricingContentLoaded">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1">Your Pricing Plan</h5>
                                <p class="text-muted mb-0 small">Pricing is managed by your account manager. Contact support to discuss changes.</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-outline-secondary btn-sm" id="refreshPricingBtn" title="Refresh pricing">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <span class="badge bg-light text-dark px-3 py-2">
                                    <i class="fas fa-eye me-1"></i>Read Only
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
        
        <div class="row g-4" id="pricingTiersDisplay">
            <!-- Starter Tier -->
            <div class="col-md-4">
                <div class="card pricing-display-card h-100">
                    <div class="card-header pricing-header-starter text-center py-4">
                        <h4 class="text-white mb-2">Starter</h4>
                        <p class="text-white-50 mb-0 small">0 – 50,000 messages</p>
                    </div>
                    <div class="card-body">
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">SMS Rates</h6>
                            <div class="pricing-rates">
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">SMS UK</span>
                                        <span class="rate-basis">per SMS submitted</span>
                                    </div>
                                    <span class="rate-value">£0.034</span>
                                </div>
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">SMS International</span>
                                        <span class="rate-basis">per SMS submitted</span>
                                    </div>
                                    <span class="rate-value">£0.045</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">RCS Rates</h6>
                            <div class="pricing-rates">
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">RCS Basic</span>
                                        <span class="rate-basis">per RCS submitted</span>
                                    </div>
                                    <span class="rate-value">£0.042</span>
                                </div>
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">RCS Rich</span>
                                        <span class="rate-basis">per RCS submitted</span>
                                    </div>
                                    <span class="rate-value">£0.065</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">Other Services</h6>
                            <div class="pricing-badges">
                                <div class="pricing-badge">
                                    <span class="badge-price">£0.002</span>
                                    <span class="badge-label">AI Token</span>
                                </div>
                                <div class="pricing-badge">
                                    <span class="badge-price">£5.00</span>
                                    <span class="badge-label">VMN /mo</span>
                                </div>
                                <div class="pricing-badge">
                                    <span class="badge-price">£25.00</span>
                                    <span class="badge-label">Keyword /mo</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <ul class="pricing-features list-unstyled mb-0">
                            <li><i class="fas fa-check text-success me-2"></i>Portal access</li>
                            <li><i class="fas fa-check text-success me-2"></i>API access</li>
                            <li><i class="fas fa-check text-success me-2"></i>Email support</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Enterprise Tier -->
            <div class="col-md-4">
                <div class="card pricing-display-card h-100 pricing-active">
                    <div class="active-badge">Your Plan</div>
                    <div class="card-header pricing-header-enterprise text-center py-4">
                        <h4 class="text-white mb-2">Enterprise</h4>
                        <p class="text-white-50 mb-0 small">50,000 – 1,000,000 messages</p>
                    </div>
                    <div class="card-body">
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">SMS Rates</h6>
                            <div class="pricing-rates">
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">SMS UK</span>
                                        <span class="rate-basis">per delivered SMS</span>
                                    </div>
                                    <span class="rate-value">£0.028</span>
                                </div>
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">SMS International</span>
                                        <span class="rate-basis">per delivered SMS</span>
                                    </div>
                                    <span class="rate-value">£0.038</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">RCS Rates</h6>
                            <div class="pricing-rates">
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">RCS Basic</span>
                                        <span class="rate-basis">per delivered RCS</span>
                                    </div>
                                    <span class="rate-value">£0.035</span>
                                </div>
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">RCS Rich</span>
                                        <span class="rate-basis">per delivered RCS</span>
                                    </div>
                                    <span class="rate-value">£0.052</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">Other Services</h6>
                            <div class="pricing-badges">
                                <div class="pricing-badge">
                                    <span class="badge-price">£0.0015</span>
                                    <span class="badge-label">AI Token</span>
                                </div>
                                <div class="pricing-badge">
                                    <span class="badge-price">£4.00</span>
                                    <span class="badge-label">VMN /mo</span>
                                </div>
                                <div class="pricing-badge">
                                    <span class="badge-price">£20.00</span>
                                    <span class="badge-label">Keyword /mo</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <ul class="pricing-features list-unstyled mb-0">
                            <li><i class="fas fa-check text-success me-2"></i>Portal access</li>
                            <li><i class="fas fa-check text-success me-2"></i>API access</li>
                            <li><i class="fas fa-check text-success me-2"></i>Priority support</li>
                            <li><i class="fas fa-check text-success me-2"></i>Dedicated account manager</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Bespoke Tier -->
            <div class="col-md-4">
                <div class="card pricing-display-card h-100">
                    <div class="card-header pricing-header-bespoke text-center py-4">
                        <span class="badge bg-white text-dark mb-2"><i class="fas fa-gem me-1"></i>Custom</span>
                        <h4 class="text-white mb-2">Bespoke</h4>
                        <p class="text-white-50 mb-0 small">1,000,000+ messages</p>
                    </div>
                    <div class="card-body">
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">SMS Rates</h6>
                            <div class="pricing-rates">
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">SMS UK</span>
                                        <span class="rate-basis">per delivered SMS</span>
                                    </div>
                                    <span class="rate-value">Custom</span>
                                </div>
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">SMS International</span>
                                        <span class="rate-basis">per delivered SMS</span>
                                    </div>
                                    <span class="rate-value">Custom</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">RCS Rates</h6>
                            <div class="pricing-rates">
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">RCS Basic</span>
                                        <span class="rate-basis">per delivered RCS</span>
                                    </div>
                                    <span class="rate-value">Custom</span>
                                </div>
                                <div class="rate-row">
                                    <div class="rate-info">
                                        <span class="rate-label">RCS Rich</span>
                                        <span class="rate-basis">per delivered RCS</span>
                                    </div>
                                    <span class="rate-value">Custom</span>
                                </div>
                            </div>
                        </div>
                        <div class="pricing-section">
                            <h6 class="pricing-section-title">Other Services</h6>
                            <div class="pricing-badges">
                                <div class="pricing-badge">
                                    <span class="badge-price">Custom</span>
                                    <span class="badge-label">AI Token</span>
                                </div>
                                <div class="pricing-badge">
                                    <span class="badge-price">Custom</span>
                                    <span class="badge-label">VMN /mo</span>
                                </div>
                                <div class="pricing-badge">
                                    <span class="badge-price">Custom</span>
                                    <span class="badge-label">Keyword /mo</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <ul class="pricing-features list-unstyled mb-0">
                            <li><i class="fas fa-check text-success me-2"></i>All Enterprise features</li>
                            <li><i class="fas fa-check text-success me-2"></i>Volume discounts</li>
                            <li><i class="fas fa-check text-success me-2"></i>Custom SLAs</li>
                            <li><i class="fas fa-check text-success me-2"></i>24/7 support</li>
                        </ul>
                    </div>
                    <div class="card-footer bg-transparent text-center py-3">
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Contact Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-pastel-primary small">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-primary me-2 mt-1"></i>
                        <div>
                            <strong>Note:</strong> Pricing shown is from HubSpot and reflects your current agreement. VAT is applied separately on invoices where applicable. For pricing changes, please contact your account manager.
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
        @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Pricing Service - Mock HubSpot Integration
    var PricingService = {
        simulateError: false, // Set to true to test error state
        
        fetchPricing: function() {
            var self = this;
            return new Promise(function(resolve, reject) {
                // Show loading state
                $('#pricingLoadingState').show();
                $('#pricingErrorState').hide();
                $('#pricingContentLoaded').hide();
                
                // Simulate API delay
                setTimeout(function() {
                    $('#pricingLoadingState').hide();
                    
                    if (self.simulateError) {
                        // Simulate HubSpot API failure
                        reject({ 
                            message: 'Unable to connect to HubSpot. Please check your connection and try again.',
                            code: 'HUBSPOT_UNAVAILABLE'
                        });
                    } else {
                        // Simulate successful fetch
                        resolve({
                            currentPlan: 'enterprise',
                            tiers: {
                                starter: { sms: 0.065, rcs_rich: 0.085, rcs_basic: 0.068, ai_token: 0.002, vmn: 5.00, keyword: 25.00 },
                                enterprise: { sms: 0.052, rcs_rich: 0.072, rcs_basic: 0.056, ai_token: 0.0015, vmn: 4.00, keyword: 20.00 },
                                bespoke: { sms: 'custom', rcs_rich: 'custom', rcs_basic: 'custom', ai_token: 'custom', vmn: 'custom', keyword: 'custom' }
                            },
                            lastUpdated: new Date().toISOString()
                        });
                    }
                }, 800);
            });
        },
        
        showError: function(error) {
            $('#pricingLoadingState').hide();
            $('#pricingContentLoaded').hide();
            $('#pricingErrorMessage').text(error.message || 'Failed to fetch pricing data. Please try again.');
            $('#pricingErrorState').show();
        },
        
        showContent: function(data) {
            $('#pricingLoadingState').hide();
            $('#pricingErrorState').hide();
            $('#pricingContentLoaded').show();
            console.log('[PricingService] Pricing loaded:', data);
        }
    };
    
    // Load pricing when tab is clicked
    $('#pricing-tab').on('shown.bs.tab', function() {
        // Only load if not already loaded
        if ($('#pricingContentLoaded').is(':visible')) return;
        
        PricingService.fetchPricing()
            .then(function(data) {
                PricingService.showContent(data);
            })
            .catch(function(error) {
                PricingService.showError(error);
            });
    });
    
    // Retry button
    $('#retryPricingBtn').on('click', function() {
        PricingService.fetchPricing()
            .then(function(data) {
                PricingService.showContent(data);
            })
            .catch(function(error) {
                PricingService.showError(error);
            });
    });
    
    // Refresh button
    $('#refreshPricingBtn').on('click', function() {
        var $btn = $(this);
        $btn.addClass('loading').prop('disabled', true);
        
        PricingService.fetchPricing()
            .then(function(data) {
                PricingService.showContent(data);
            })
            .catch(function(error) {
                PricingService.showError(error);
            })
            .finally(function() {
                $btn.removeClass('loading').prop('disabled', false);
            });
    });
    
    // Expose for testing error state: PricingService.simulateError = true
    window.PricingService = PricingService;
    
    var originalValues = {};
    var highImpactFields = ['companyName', 'companyNumber', 'vatRegistered'];
    var currentUser = {
        id: 1,
        name: 'Sarah Johnson',
        email: 'sarah.johnson@acmecomms.co.uk',
        role: 'admin'
    };
    
    function captureOriginalValues() {
        $('input, select, textarea').each(function() {
            var id = $(this).attr('id');
            if (id) {
                originalValues[id] = $(this).val();
            }
        });
    }
    
    captureOriginalValues();
    
    function createAuditEntry(fieldId, fieldLabel, oldValue, newValue) {
        var isHighImpact = highImpactFields.includes(fieldId);
        return {
            field_id: fieldId,
            field_name: fieldLabel,
            old_value: oldValue,
            new_value: newValue,
            user_id: currentUser.id,
            user_name: currentUser.name,
            user_email: currentUser.email,
            timestamp: new Date().toISOString(),
            is_high_impact: isHighImpact,
            section: getFieldSection(fieldId)
        };
    }
    
    function getFieldSection(fieldId) {
        var sectionMap = {
            'signupFirstName': 'sign_up_details',
            'signupLastName': 'sign_up_details',
            'signupJobTitle': 'sign_up_details',
            'signupBusinessName': 'sign_up_details',
            'signupEmail': 'sign_up_details',
            'signupMobile': 'sign_up_details',
            'companyName': 'company_information',
            'tradingName': 'company_information',
            'companyNumber': 'company_information',
            'companySector': 'company_information',
            'companyWebsite': 'company_information',
            'billingEmail': 'support_operations',
            'supportEmail': 'support_operations',
            'incidentEmail': 'support_operations',
            'signatoryName': 'contract_signatory',
            'signatoryTitle': 'contract_signatory',
            'signatoryEmail': 'contract_signatory',
            'vatRegistered': 'vat_tax_information',
            'vatNumber': 'vat_tax_information',
            'vatCountry': 'vat_tax_information',
            'reverseCharges': 'vat_tax_information'
        };
        return sectionMap[fieldId] || 'unknown';
    }
    
    function getFieldLabel(fieldId) {
        var $field = $('#' + fieldId);
        var $label = $field.closest('.field-group').find('label.form-label').first();
        return $label.text().replace('*', '').replace('(Optional)', '').trim();
    }
    
    function collectChanges(section) {
        var changes = [];
        var sectionFields = [];
        
        switch(section) {
            case 'signUpDetails':
                sectionFields = ['signupFirstName', 'signupLastName', 'signupJobTitle', 'signupBusinessName', 'signupEmail', 'signupMobile'];
                break;
            case 'companyInfo':
                sectionFields = ['companyName', 'tradingName', 'companyNumber', 'companySector', 'companyWebsite', 'regAddress1', 'regAddress2', 'regCity', 'regCounty', 'regPostcode', 'regCountry'];
                break;
            case 'supportOperations':
                sectionFields = ['billingEmail', 'supportEmail', 'incidentEmail'];
                break;
            case 'contractSignatory':
                sectionFields = ['signatoryName', 'signatoryTitle', 'signatoryEmail'];
                break;
            case 'vatTaxInfo':
                sectionFields = ['vatRegistered', 'vatNumber', 'vatCountry', 'reverseCharges'];
                break;
        }
        
        sectionFields.forEach(function(fieldId) {
            var $field = $('#' + fieldId);
            if ($field.length) {
                var currentValue = $field.val();
                var originalValue = originalValues[fieldId] || '';
                
                if (currentValue !== originalValue) {
                    changes.push(createAuditEntry(
                        fieldId,
                        getFieldLabel(fieldId),
                        originalValue,
                        currentValue
                    ));
                }
            }
        });
        
        return changes;
    }
    
    function submitAuditLog(changes) {
        if (changes.length === 0) return;
        
        var hasHighImpact = changes.some(function(c) { return c.is_high_impact; });
        
        var auditPayload = {
            changes: changes,
            has_high_impact_changes: hasHighImpact,
            submitted_at: new Date().toISOString(),
            user: currentUser
        };
        
        console.log('Audit Log Payload (Backend Ready):', JSON.stringify(auditPayload, null, 2));
        
        changes.forEach(function(change) {
            originalValues[change.field_id] = change.new_value;
        });
    }
    
    window.AccountDetailsAudit = {
        collectChanges: collectChanges,
        submitAuditLog: submitAuditLog,
        getOriginalValues: function() { return originalValues; },
        isHighImpactField: function(fieldId) { return highImpactFields.includes(fieldId); }
    };
    
    window.AccountDetailsData = {
        getSignUpDetails: function() {
            return {
                first_name: $('#signupFirstName').val(),
                last_name: $('#signupLastName').val(),
                job_title: $('#signupJobTitle').val(),
                business_name: $('#signupBusinessName').val(),
                email: $('#signupEmail').val(),
                mobile: $('#signupMobile').val()
            };
        },
        getCompanyInformation: function() {
            return {
                company_type: selectedCompanyType,
                company_name: $('#companyName').val(),
                trading_name: $('#tradingName').val(),
                company_number: selectedCompanyType !== 'sole_trader' ? $('#companyNumber').val() : null,
                sector: $('#companySector').val(),
                website: $('#companyWebsite').val(),
                registered_address: {
                    line1: $('#regAddress1').val(),
                    line2: $('#regAddress2').val(),
                    city: $('#regCity').val(),
                    county: $('#regCounty').val(),
                    postcode: $('#regPostcode').val(),
                    country: $('#regCountry').val()
                },
                operating_address_same: $('#operatingSameAsRegistered').is(':checked'),
                operating_address: $('#operatingSameAsRegistered').is(':checked') ? null : {
                    line1: $('#opAddress1').val(),
                    line2: $('#opAddress2').val(),
                    city: $('#opCity').val(),
                    county: $('#opCounty').val(),
                    postcode: $('#opPostcode').val(),
                    country: $('#opCountry').val()
                }
            };
        },
        getSupportContacts: function() {
            return {
                billing_email: $('#billingEmail').val(),
                support_email: $('#supportEmail').val(),
                incident_email: $('#incidentEmail').val()
            };
        },
        getContractSignatory: function() {
            return {
                name: $('#signatoryName').val(),
                title: $('#signatoryTitle').val(),
                email: $('#signatoryEmail').val()
            };
        },
        getVatInformation: function() {
            var isRegistered = $('#vatRegistered').val() === 'yes';
            return {
                vat_registered: isRegistered,
                vat_number: isRegistered ? $('#vatNumber').val() : null,
                vat_country: isRegistered ? $('#vatCountry').val() : null,
                reverse_charges: isRegistered ? $('#reverseCharges').val() === 'yes' : null
            };
        },
        getAll: function() {
            return {
                sign_up: this.getSignUpDetails(),
                company: this.getCompanyInformation(),
                support_contacts: this.getSupportContacts(),
                signatory: this.getContractSignatory(),
                vat: this.getVatInformation(),
                retrieved_at: new Date().toISOString()
            };
        },
        getForModule: function(moduleName) {
            var data = this.getAll();
            var moduleData = {
                source: 'account_details',
                read_only: true,
                module: moduleName,
                retrieved_at: data.retrieved_at
            };
            
            switch(moduleName) {
                case 'rcs_agent_registration':
                    moduleData.data = {
                        company_name: data.company.company_name,
                        company_number: data.company.company_number,
                        website: data.company.website,
                        registered_address: data.company.registered_address,
                        signatory: data.signatory
                    };
                    break;
                case 'sms_senderid_registration':
                    moduleData.data = {
                        company_name: data.company.company_name,
                        company_number: data.company.company_number,
                        registered_address: data.company.registered_address
                    };
                    break;
                case 'billing_invoicing':
                    moduleData.data = {
                        company_name: data.company.company_name,
                        billing_email: data.support_contacts.billing_email,
                        vat: data.vat,
                        registered_address: data.company.registered_address
                    };
                    break;
                case 'finance_reporting':
                    moduleData.data = {
                        company_name: data.company.company_name,
                        vat: data.vat,
                        billing_email: data.support_contacts.billing_email
                    };
                    break;
                case 'support_incidents':
                    moduleData.data = {
                        company_name: data.company.company_name,
                        support_email: data.support_contacts.support_email,
                        incident_email: data.support_contacts.incident_email,
                        primary_contact: data.sign_up
                    };
                    break;
                case 'compliance_audit':
                    moduleData.data = {
                        company_name: data.company.company_name,
                        company_number: data.company.company_number,
                        vat: data.vat,
                        signatory: data.signatory,
                        registered_address: data.company.registered_address
                    };
                    break;
                default:
                    moduleData.data = data;
            }
            
            return moduleData;
        },
        
        getDataVersion: function() {
            return '1.0.0';
        },
        
        getMetadata: function() {
            return {
                version: this.getDataVersion(),
                schema: 'account_details_v1',
                cache_key: 'account_details_' + Date.now(),
                cache_ttl: 300,
                supports: {
                    international_addresses: true,
                    eu_vat_validation: true,
                    gdpr_right_to_rectify: true,
                    api_accessible: true
                },
                supported_countries: ['GB', 'DE', 'FR', 'IE', 'NL', 'ES', 'IT', 'BE', 'AT', 'PL', 'US'],
                supported_vat_formats: Object.keys(vatFormats),
                last_modified: new Date().toISOString()
            };
        },
        
        exportForApi: function() {
            return {
                data: this.getAll(),
                metadata: this.getMetadata(),
                _links: {
                    self: '/api/v1/account/details',
                    audit: '/api/v1/account/details/audit',
                    gdpr_export: '/api/v1/account/details/gdpr-export',
                    gdpr_rectify: '/api/v1/account/details/gdpr-rectify'
                }
            };
        },
        
        getGdprExport: function() {
            var data = this.getAll();
            return {
                subject_type: 'account',
                export_date: new Date().toISOString(),
                data_categories: {
                    identity: {
                        first_name: data.sign_up.first_name,
                        last_name: data.sign_up.last_name,
                        job_title: data.sign_up.job_title
                    },
                    contact: {
                        email: data.sign_up.email,
                        mobile: data.sign_up.mobile,
                        billing_email: data.support_contacts.billing_email,
                        support_email: data.support_contacts.support_email,
                        incident_email: data.support_contacts.incident_email
                    },
                    company: {
                        business_name: data.sign_up.business_name,
                        company_name: data.company.company_name,
                        trading_name: data.company.trading_name,
                        company_number: data.company.company_number,
                        website: data.company.website,
                        sector: data.company.sector
                    },
                    financial: {
                        vat_registered: data.vat.vat_registered,
                        vat_number: data.vat.vat_number,
                        vat_country: data.vat.vat_country
                    },
                    addresses: {
                        registered: data.company.registered_address,
                        operating: data.company.operating_address
                    }
                },
                rectification_available: true,
                rectification_url: '/account/details'
            };
        }
    };
    
    // Company Type Tile Selector
    var selectedCompanyType = null;
    
    $('.company-type-tile').on('click', function() {
        $('.company-type-tile').removeClass('selected');
        $(this).addClass('selected');
        selectedCompanyType = $(this).data('type');
        $('#companyTypeError').hide();
        
        handleCompanyTypeChange(selectedCompanyType);
    });
    
    function handleCompanyTypeChange(type) {
        var $numberGroup = $('#companyNumberGroup');
        var $numberField = $('#companyNumber');
        var $lookupBtn = $('#lookupCompanyBtn');
        var $requiredIndicator = $('#companyNumberRequired');
        var $hint = $('#companyNumberHint');
        var $chips = $('#companyNumberChips');
        
        // Reset lookup status
        $('#lookupStatus').text('').removeClass('success error loading');
        
        switch(type) {
            case 'uk_limited':
                // Company Number is mandatory, show lookup button
                $numberGroup.show();
                $numberField.prop('required', true).removeClass('is-invalid');
                $requiredIndicator.show();
                $lookupBtn.show();
                $hint.text('Companies House registration number (8 digits)');
                $chips.show();
                break;
                
            case 'sole_trader':
                // Company Number is hidden/not required
                $numberGroup.hide();
                $numberField.prop('required', false).val('').removeClass('is-invalid');
                $requiredIndicator.hide();
                $lookupBtn.hide();
                break;
                
            case 'government':
                // Company Number is optional
                $numberGroup.show();
                $numberField.prop('required', false).removeClass('is-invalid');
                $requiredIndicator.hide();
                $lookupBtn.hide();
                $hint.text('Optional - enter if applicable');
                $chips.show();
                break;
        }
        
        updateCompanyStatusBadge();
    }
    
    // Companies House Lookup Service (backend-ready)
    var CompaniesHouseLookup = {
        // Mock data for testing different scenarios
        mockDatabase: {
            '12345678': {
                success: true,
                data: {
                    company_name: 'Acme Communications Ltd',
                    company_status: 'active',
                    registered_address: {
                        line1: '123 Business Park',
                        line2: 'Tech Quarter',
                        city: 'London',
                        county: 'Greater London',
                        postcode: 'EC1A 1BB',
                        country: 'UK'
                    }
                }
            },
            '87654321': {
                success: true,
                data: {
                    company_name: 'Global Tech Solutions PLC',
                    company_status: 'active',
                    registered_address: {
                        line1: '45 Innovation Way',
                        line2: 'Science Park',
                        city: 'Cambridge',
                        county: 'Cambridgeshire',
                        postcode: 'CB1 2AB',
                        country: 'UK'
                    }
                }
            },
            '00000000': {
                success: false,
                error: 'not_found',
                message: 'Could not find a company with that number.'
            },
            '99999999': {
                success: false,
                error: 'service_unavailable',
                message: 'Service unavailable, try again later.'
            }
        },
        
        // Validate company number format
        validateFormat: function(number) {
            var pattern = /^([0-9]{8}|[A-Z]{2}[0-9]{6})$/i;
            return pattern.test(number.trim());
        },
        
        // Show toast notification
        showToast: function(message, type) {
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                // Fallback toast using Fillow style
                var $toast = $('<div class="toast-notification toast-' + type + '">' + 
                    '<i class="fas fa-' + (type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle') + ' me-2"></i>' + 
                    message + '</div>');
                $('body').append($toast);
                setTimeout(function() { $toast.addClass('show'); }, 100);
                setTimeout(function() { $toast.removeClass('show'); setTimeout(function() { $toast.remove(); }, 300); }, 4000);
            }
        },
        
        // Main lookup function
        lookup: function(companyNumber, callbacks) {
            var self = this;
            var $status = $('#lookupStatus');
            var $lookupBtn = $('#lookupCompanyBtn');
            var $numberField = $('#companyNumber');
            
            // Clear previous states
            $numberField.removeClass('is-invalid is-valid');
            $status.removeClass('success error');
            
            // Validate format
            if (!this.validateFormat(companyNumber)) {
                $status.html('<i class="fas fa-exclamation-triangle me-1"></i>Invalid format. Use 8 digits or 2 letters + 6 digits.')
                    .addClass('error');
                $numberField.addClass('is-invalid');
                return;
            }
            
            // Set loading state
            $status.html('<i class="fas fa-spinner fa-spin me-1"></i>Searching Companies House...')
                .addClass('loading').removeClass('success error');
            $lookupBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $numberField.prop('readonly', true);
            
            // TODO: Replace mock with actual API call
            // Backend endpoint: POST /api/companies-house/lookup
            // Request: { company_number: companyNumber }
            // Response: { success: bool, data?: {...}, error?: string, message?: string }
            
            // Simulate API call with mock data
            setTimeout(function() {
                var response;
                
                // Check mock database first, otherwise generate response
                if (self.mockDatabase[companyNumber]) {
                    response = self.mockDatabase[companyNumber];
                } else {
                    // Default: generate mock success response for any valid format
                    response = {
                        success: true,
                        data: {
                            company_name: 'Company ' + companyNumber + ' Ltd',
                            company_status: 'active',
                            registered_address: {
                                line1: Math.floor(Math.random() * 200) + 1 + ' High Street',
                                line2: '',
                                city: 'London',
                                county: '',
                                postcode: 'SW1A 1AA',
                                country: 'UK'
                            }
                        }
                    };
                }
                
                // Reset button state
                $lookupBtn.prop('disabled', false).html('<i class="fas fa-search me-1"></i>Lookup');
                $numberField.prop('readonly', false);
                
                if (response.success) {
                    // Success - populate fields
                    self.populateFields(response.data);
                    $status.html('<i class="fas fa-check-circle me-1"></i>Company details populated from Companies House')
                        .addClass('success').removeClass('error loading');
                    $numberField.addClass('is-valid');
                    
                    if (callbacks && callbacks.onSuccess) {
                        callbacks.onSuccess(response.data);
                    }
                } else {
                    // Error handling
                    $status.html('<i class="fas fa-exclamation-circle me-1"></i>' + response.message)
                        .addClass('error').removeClass('success loading');
                    
                    if (response.error === 'not_found') {
                        $numberField.addClass('is-invalid');
                        self.showToast(response.message, 'error');
                    } else if (response.error === 'service_unavailable') {
                        self.showToast(response.message, 'warning');
                    }
                    
                    if (callbacks && callbacks.onError) {
                        callbacks.onError(response);
                    }
                }
                
                updateCompanyStatusBadge();
                
            }, 1500); // Simulate network delay
        },
        
        // Populate form fields with response data
        populateFields: function(data) {
            var oldCompanyName = $('#companyName').val();
            
            $('#companyName').val(data.company_name);
            $('#regAddress1').val(data.registered_address.line1);
            $('#regAddress2').val(data.registered_address.line2 || '');
            $('#regCity').val(data.registered_address.city);
            $('#regCounty').val(data.registered_address.county || '');
            $('#regPostcode').val(data.registered_address.postcode);
            $('#regCountry').val(data.registered_address.country);
            
            // Log audit for autofill
            if (oldCompanyName !== data.company_name) {
                logFieldChange('companyName', oldCompanyName, data.company_name, 'Companies House Lookup');
            }
            logFieldChange('registered_address', '', JSON.stringify(data.registered_address), 'Companies House Lookup');
        }
    };
    
    // Legacy wrapper for backward compatibility
    function lookupCompaniesHouse(companyNumber) {
        CompaniesHouseLookup.lookup(companyNumber);
    }
    
    // Lookup button click
    $('#lookupCompanyBtn').on('click', function() {
        var companyNumber = $('#companyNumber').val().trim();
        if (companyNumber) {
            lookupCompaniesHouse(companyNumber);
        } else {
            $('#lookupStatus').text('Please enter a company number first').addClass('error').removeClass('success loading');
        }
    });
    
    // Auto-lookup on blur for UK Limited
    $('#companyNumber').on('blur', function() {
        if (selectedCompanyType === 'uk_limited') {
            var value = $(this).val().trim();
            if (value && value.length >= 8) {
                // Only auto-lookup if company name is empty
                if (!$('#companyName').val().trim()) {
                    lookupCompaniesHouse(value);
                }
            }
        }
    });
    
    $('#vatRegistered').on('change', function() {
        if ($(this).val() === 'yes') {
            $('#vatDetailsSection').slideDown();
        } else {
            $('#vatDetailsSection').slideUp();
        }
        updateVatStatusBadge();
    });
    
    var vatFormats = {
        'GB': { pattern: /^GB\d{9}$|^GB\d{12}$|^GBGD\d{3}$|^GBHA\d{3}$/, hint: 'Format: GB followed by 9 or 12 digits' },
        'DE': { pattern: /^DE\d{9}$/, hint: 'Format: DE followed by 9 digits' },
        'FR': { pattern: /^FR[A-Z0-9]{2}\d{9}$/, hint: 'Format: FR followed by 2 characters and 9 digits' },
        'IE': { pattern: /^IE\d{7}[A-Z]{1,2}$|^IE\d[A-Z]\d{5}[A-Z]$/, hint: 'Format: IE followed by 7 digits and 1-2 letters' },
        'NL': { pattern: /^NL\d{9}B\d{2}$/, hint: 'Format: NL followed by 9 digits, B, and 2 digits' },
        'ES': { pattern: /^ES[A-Z0-9]\d{7}[A-Z0-9]$/, hint: 'Format: ES followed by 9 alphanumeric characters' },
        'IT': { pattern: /^IT\d{11}$/, hint: 'Format: IT followed by 11 digits' },
        'BE': { pattern: /^BE0\d{9}$/, hint: 'Format: BE0 followed by 9 digits' },
        'AT': { pattern: /^ATU\d{8}$/, hint: 'Format: ATU followed by 8 digits' },
        'PL': { pattern: /^PL\d{10}$/, hint: 'Format: PL followed by 10 digits' }
    };
    
    $('#vatCountry').on('change', function() {
        var country = $(this).val();
        if (vatFormats[country]) {
            $('#vatFormatHint').text(vatFormats[country].hint);
        } else {
            $('#vatFormatHint').text('Enter your VAT number');
        }
    });
    
    // VAT Validation Service (backend-ready)
    var VatValidationService = {
        // Mock database for testing scenarios
        mockDatabase: {
            'GB123456789': { valid: true, verified: true, company_name: 'Acme Communications Ltd' },
            'GB987654321': { valid: true, verified: true, company_name: 'Global Tech Solutions PLC' },
            'GB000000000': { valid: true, verified: false, reason: 'not_found' },
            'GB999999999': { valid: true, verified: null, reason: 'service_unavailable' },
            'DE123456789': { valid: true, verified: true, company_name: 'Deutsche Firma GmbH' },
            'FR12345678901': { valid: true, verified: true, company_name: 'Entreprise Francaise SARL' }
        },
        
        isValidating: false,
        
        // Validate format locally first
        validateFormat: function(vatNumber, country) {
            if (!vatNumber || !country) return false;
            if (vatFormats[country]) {
                return vatFormats[country].pattern.test(vatNumber.toUpperCase());
            }
            return vatNumber.length >= 8;
        },
        
        // Main verification function
        verify: function(vatNumber, country, callbacks) {
            var self = this;
            var $field = $('#vatNumber');
            var $status = $('#vatVerificationStatus');
            
            if (this.isValidating) return;
            
            vatNumber = vatNumber.trim().toUpperCase();
            
            // Clear previous states
            $field.removeClass('is-invalid is-valid');
            $status.removeClass('text-success text-warning text-danger').hide();
            
            // First validate format
            if (!this.validateFormat(vatNumber, country)) {
                $field.addClass('is-invalid');
                $('#vatNumberError').text('Invalid VAT number format for ' + country);
                return;
            }
            
            // Set loading state
            this.isValidating = true;
            $status.html('<i class="fas fa-spinner fa-spin me-1"></i>Verifying with HMRC/VIES...')
                .removeClass('text-success text-warning text-danger')
                .addClass('text-muted').show();
            
            // TODO: Replace mock with actual API call
            // Backend endpoint: POST /api/vat/verify
            // Request: { vat_number: string, country: string }
            // Response: { valid: bool, verified: bool|null, company_name?: string, reason?: string }
            
            setTimeout(function() {
                self.isValidating = false;
                var response;
                
                // Check mock database
                if (self.mockDatabase[vatNumber]) {
                    response = self.mockDatabase[vatNumber];
                } else {
                    // Default: generate mock verified response
                    response = { valid: true, verified: true, company_name: 'Verified Business' };
                }
                
                if (response.verified === true) {
                    // Verified successfully
                    $field.addClass('is-valid');
                    $status.html('<i class="fas fa-check-circle me-1"></i>Verified' + 
                        (response.company_name ? ' - ' + response.company_name : ''))
                        .removeClass('text-muted text-warning text-danger')
                        .addClass('text-success').show();
                    
                    if (callbacks && callbacks.onVerified) {
                        callbacks.onVerified(response);
                    }
                } else if (response.verified === false) {
                    // Not valid / not found
                    $status.html('<i class="fas fa-exclamation-triangle me-1"></i>Not valid or cannot be verified')
                        .removeClass('text-muted text-success text-danger')
                        .addClass('text-warning').show();
                    
                    if (callbacks && callbacks.onNotVerified) {
                        callbacks.onNotVerified(response);
                    }
                } else {
                    // Service unavailable - warn but don't block
                    $status.html('<i class="fas fa-info-circle me-1"></i>Verification service unavailable. You can still save.')
                        .removeClass('text-muted text-success text-danger')
                        .addClass('text-warning').show();
                    
                    if (callbacks && callbacks.onServiceError) {
                        callbacks.onServiceError(response);
                    }
                }
                
                updateVatStatusBadge();
                
            }, 1200);
        }
    };
    
    function validateVatNumber() {
        var country = $('#vatCountry').val();
        var vatNum = $('#vatNumber').val().trim().toUpperCase();
        
        if (!vatNum) {
            $('#vatNumber').addClass('is-invalid');
            $('#vatNumberError').text('VAT number is required');
            $('#vatVerificationStatus').hide();
            return false;
        }
        
        if (vatFormats[country]) {
            if (!vatFormats[country].pattern.test(vatNum)) {
                $('#vatNumber').addClass('is-invalid');
                $('#vatNumberError').text('Invalid VAT number format for ' + country);
                $('#vatVerificationStatus').hide();
                return false;
            }
        }
        
        $('#vatNumber').removeClass('is-invalid');
        return true;
    }
    
    function updateVatStatusBadge() {
        var isRegistered = $('#vatRegistered').val();
        var allValid = true;
        
        if (!isRegistered) {
            allValid = false;
        } else if (isRegistered === 'yes') {
            if (!$('#vatNumber').val().trim() || !$('#vatCountry').val() || !$('#reverseCharges').val()) {
                allValid = false;
            }
        }
        
        var $badge = $('#vatStatusBadge');
        if (allValid) {
            $badge.removeClass('required').addClass('complete')
                .html('<i class="fas fa-check-circle"></i> Complete');
        } else {
            $badge.removeClass('complete').addClass('required')
                .html('<i class="fas fa-exclamation-circle"></i> Required to go live');
        }
    }
    
    $('.vat-field, .vat-detail-field').on('input blur change', function() {
        updateVatStatusBadge();
    });
    
    $('#vatNumber').on('blur', function() {
        if ($('#vatRegistered').val() === 'yes') {
            var vatNum = $(this).val().trim();
            var country = $('#vatCountry').val();
            
            if (vatNum && country) {
                // Trigger verification (includes format validation)
                VatValidationService.verify(vatNum, country);
            } else if (vatNum && !country) {
                $('#vatNumber').addClass('is-invalid');
                $('#vatNumberError').text('Please select a VAT country first');
            }
        }
    });
    
    $('#operatingSameAsRegistered').on('change', function() {
        if ($(this).is(':checked')) {
            $('#operatingAddressSection').slideUp();
        } else {
            $('#operatingAddressSection').slideDown();
        }
    });
    
    $('#companyWebsite').on('blur', function() {
        var value = $(this).val().trim();
        if (value && !value.startsWith('http://') && !value.startsWith('https://')) {
            $(this).val('https://' + value);
        }
    });
    
    function updateCompanyStatusBadge() {
        var allValid = true;
        
        // Company Type is always required
        if (!selectedCompanyType) {
            allValid = false;
        }
        
        // Build required fields based on company type
        var requiredFields = ['#companyName', '#companySector', '#companyWebsite', 
                              '#regAddress1', '#regCity', '#regPostcode', '#regCountry'];
        
        // Company Number is only required for UK Limited
        if (selectedCompanyType === 'uk_limited') {
            requiredFields.push('#companyNumber');
        }
        
        requiredFields.forEach(function(selector) {
            var value = $(selector).val();
            if (!value || value.trim() === '') {
                allValid = false;
            }
        });
        
        if (!$('#operatingSameAsRegistered').is(':checked')) {
            var opFields = ['#opAddress1', '#opCity', '#opPostcode', '#opCountry'];
            opFields.forEach(function(selector) {
                var value = $(selector).val();
                if (!value || value.trim() === '') {
                    allValid = false;
                }
            });
        }
        
        var $badge = $('#companyStatusBadge');
        if (allValid) {
            $badge.removeClass('required').addClass('complete')
                .html('<i class="fas fa-check-circle"></i> Complete');
        } else {
            $badge.removeClass('complete').addClass('required')
                .html('<i class="fas fa-exclamation-circle"></i> Required to go live');
        }
    }
    
    $('.company-field, .operating-field').on('input blur change', function() {
        validateField($(this));
        updateCompanyStatusBadge();
    });
    
    function showAutoSave($indicator, state) {
        if (state === 'saving') {
            $indicator.removeClass('saved').addClass('saving')
                .html('<i class="fas fa-circle-notch fa-spin"></i> Saving...');
        } else if (state === 'saved') {
            $indicator.removeClass('saving').addClass('saved')
                .html('<i class="fas fa-check-circle"></i> All changes saved');
        }
    }
    
    var autoSaveTimeout;
    function triggerAutoSave($indicator, sectionName) {
        clearTimeout(autoSaveTimeout);
        showAutoSave($indicator, 'saving');
        
        autoSaveTimeout = setTimeout(function() {
            showAutoSave($indicator, 'saved');
            console.log('Auto-saved: ' + sectionName);
        }, 1000);
    }
    
    function updateSupportStatusBadge() {
        var allValid = true;
        $('.support-field').each(function() {
            var value = $(this).val().trim();
            if (!value) {
                allValid = false;
                return false;
            }
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                allValid = false;
                return false;
            }
        });
        
        var $badge = $('#supportStatusBadge');
        if (allValid) {
            $badge.removeClass('required').addClass('complete')
                .html('<i class="fas fa-check-circle"></i> Complete');
        } else {
            $badge.removeClass('complete').addClass('required')
                .html('<i class="fas fa-exclamation-circle"></i> Required to go live');
        }
    }
    
    $('.support-field').on('input blur', function() {
        validateField($(this));
        updateSupportStatusBadge();
    });
    
    $('#saveSupportOps').on('click', function() {
        var $saveBtn = $(this);
        var $autoSave = $('#supportAutoSave');
        var isValid = true;
        
        $('.support-field').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            toastr.error('Please enter valid email addresses for all fields.');
            return;
        }
        
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        showAutoSave($autoSave, 'saving');
        
        setTimeout(function() {
            var changes = collectChanges('supportOperations');
            submitAuditLog(changes);
            
            $saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Changes');
            showAutoSave($autoSave, 'saved');
            updateSupportStatusBadge();
            toastr.success('Support & operations contacts saved successfully.');
        }, 800);
    });
    
    function validatePhoneNumber(phone) {
        var cleaned = phone.replace(/[\s\-\(\)]/g, '');
        var e164Regex = /^\+[1-9]\d{6,14}$/;
        var ukRegex = /^(\+44|0044|44)?[1-9]\d{9,10}$/;
        return e164Regex.test(cleaned) || ukRegex.test(cleaned);
    }
    
    function validateField($field) {
        var value = $field.val().trim();
        var isRequired = $field.closest('.field-group').find('.required-indicator').length > 0;
        
        $field.val(value);
        
        if (isRequired && !value) {
            $field.addClass('is-invalid');
            return false;
        }
        
        if ($field.attr('type') === 'email' && value) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                $field.addClass('is-invalid');
                $('#emailError').text('Please enter a valid email address');
                return false;
            }
        }
        
        if ($field.attr('type') === 'tel' && value) {
            if (!validatePhoneNumber(value)) {
                $field.addClass('is-invalid');
                $('#mobileError').text('Please enter a valid mobile number (E.164 format preferred)');
                return false;
            }
        }
        
        if ($field.attr('type') === 'url' && value) {
            try {
                new URL(value);
            } catch (e) {
                $field.addClass('is-invalid');
                return false;
            }
        }
        
        $field.removeClass('is-invalid');
        return true;
    }
    
    function updateSignUpStatusBadge() {
        var allValid = true;
        $('.signup-field').each(function() {
            var value = $(this).val().trim();
            if (!value) {
                allValid = false;
                return false;
            }
        });
        
        var $badge = $('#signUpStatusBadge');
        if (allValid) {
            $badge.removeClass('required').addClass('complete')
                .html('<i class="fas fa-check-circle"></i> Complete');
        } else {
            $badge.removeClass('complete').addClass('required')
                .html('<i class="fas fa-exclamation-circle"></i> Incomplete');
        }
    }
    
    $('.signup-field').on('input blur', function() {
        validateField($(this));
        updateSignUpStatusBadge();
    });
    
    $('input, select').on('blur', function() {
        validateField($(this));
    });
    
    $('input').on('input', function() {
        if ($(this).hasClass('is-invalid')) {
            validateField($(this));
        }
    });
    
    function saveSection(sectionId, $saveBtn, $autoSave) {
        var $section = $('#' + sectionId);
        var isValid = true;
        
        $section.find('input[required], input').each(function() {
            var $field = $(this);
            var isRequired = $field.closest('.field-group').find('.required-indicator').length > 0;
            if (isRequired) {
                if (!validateField($field)) {
                    isValid = false;
                }
            }
        });
        
        if (!isValid) {
            toastr.error('Please fix the validation errors before saving.');
            return;
        }
        
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        showAutoSave($autoSave, 'saving');
        
        setTimeout(function() {
            $saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Changes');
            showAutoSave($autoSave, 'saved');
            toastr.success('Changes saved successfully. Audit log updated.');
        }, 800);
    }
    
    $('#saveSignUpDetails').on('click', function() {
        var $section = $('#signUpDetails');
        var $saveBtn = $(this);
        var $autoSave = $('#signUpAutoSave');
        var isValid = true;
        
        $('.signup-field').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            toastr.error('Please complete all mandatory fields before saving.');
            return;
        }
        
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        showAutoSave($autoSave, 'saving');
        
        setTimeout(function() {
            var changes = collectChanges('signUpDetails');
            submitAuditLog(changes);
            
            $saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Changes');
            showAutoSave($autoSave, 'saved');
            updateSignUpStatusBadge();
            toastr.success('Sign up details saved successfully.');
        }, 800);
    });
    
    $('#saveCompanyInfo').on('click', function() {
        var $saveBtn = $(this);
        var $autoSave = $('#companyAutoSave');
        var isValid = true;
        
        var website = $('#companyWebsite').val().trim();
        if (website && !website.startsWith('https://')) {
            $('#companyWebsite').addClass('is-invalid');
            $('#websiteError').text('Website must start with https://');
            isValid = false;
        }
        
        $('.company-field').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        
        if (!$('#operatingSameAsRegistered').is(':checked')) {
            $('.operating-field').each(function() {
                if (!validateField($(this))) {
                    isValid = false;
                }
            });
        }
        
        if (!isValid) {
            toastr.error('Please complete all required fields before saving.');
            return;
        }
        
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        showAutoSave($autoSave, 'saving');
        
        setTimeout(function() {
            var changes = collectChanges('companyInfo');
            submitAuditLog(changes);
            
            $saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Changes');
            showAutoSave($autoSave, 'saved');
            updateCompanyStatusBadge();
            toastr.success('Company information saved successfully.');
        }, 800);
    });
    
    function getCompanyDomain() {
        var website = $('#companyWebsite').val().trim();
        if (!website) return null;
        try {
            var url = new URL(website);
            return url.hostname.replace('www.', '');
        } catch (e) {
            return null;
        }
    }
    
    function checkSignatoryDomainMatch() {
        var email = $('#signatoryEmail').val().trim();
        var companyDomain = getCompanyDomain();
        
        if (!email || !companyDomain) {
            $('#signatoryDomainWarning').hide();
            return;
        }
        
        var emailDomain = email.split('@')[1];
        if (emailDomain && emailDomain.replace('www.', '') !== companyDomain) {
            $('#signatoryDomainWarning').show();
        } else {
            $('#signatoryDomainWarning').hide();
        }
    }
    
    function updateSignatoryStatusBadge() {
        var allValid = true;
        $('.signatory-field').each(function() {
            var value = $(this).val().trim();
            if (!value) {
                allValid = false;
                return false;
            }
        });
        
        var email = $('#signatoryEmail').val().trim();
        if (email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                allValid = false;
            }
        }
        
        var $badge = $('#signatoryStatusBadge');
        if (allValid) {
            $badge.removeClass('required').addClass('complete')
                .html('<i class="fas fa-check-circle"></i> Complete');
        } else {
            $badge.removeClass('complete').addClass('required')
                .html('<i class="fas fa-exclamation-circle"></i> Required to go live');
        }
    }
    
    $('.signatory-field').on('input blur', function() {
        validateField($(this));
        updateSignatoryStatusBadge();
    });
    
    $('#signatoryEmail').on('blur', function() {
        checkSignatoryDomainMatch();
    });
    
    $('#saveSignatory').on('click', function() {
        var $saveBtn = $(this);
        var $autoSave = $('#signatoryAutoSave');
        var isValid = true;
        
        $('.signatory-field').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            toastr.error('Please complete all required fields before saving.');
            return;
        }
        
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        showAutoSave($autoSave, 'saving');
        
        setTimeout(function() {
            var changes = collectChanges('contractSignatory');
            submitAuditLog(changes);
            
            $saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Changes');
            showAutoSave($autoSave, 'saved');
            updateSignatoryStatusBadge();
            checkSignatoryDomainMatch();
            toastr.success('Contract signatory details saved successfully.');
        }, 800);
    });
    
    $('#saveVatInfo').on('click', function() {
        var $saveBtn = $(this);
        var $autoSave = $('#vatAutoSave');
        var isValid = true;
        var isRegistered = $('#vatRegistered').val();
        
        if (!isRegistered) {
            $('#vatRegistered').addClass('is-invalid');
            isValid = false;
        }
        
        if (isRegistered === 'yes') {
            if (!validateVatNumber()) {
                isValid = false;
            }
            if (!$('#vatCountry').val()) {
                $('#vatCountry').addClass('is-invalid');
                isValid = false;
            }
            if (!$('#reverseCharges').val()) {
                $('#reverseCharges').addClass('is-invalid');
                isValid = false;
            }
        }
        
        if (!isValid) {
            toastr.error('Please complete all required VAT fields before saving.');
            return;
        }
        
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        showAutoSave($autoSave, 'saving');
        
        setTimeout(function() {
            var changes = collectChanges('vatTaxInfo');
            submitAuditLog(changes);
            
            $saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Changes');
            showAutoSave($autoSave, 'saved');
            updateVatStatusBadge();
            toastr.success('VAT & tax information saved successfully. Audit log updated.');
        }, 800);
    });
    
});
</script>
@endpush

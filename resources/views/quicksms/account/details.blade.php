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
    margin-bottom: 1.25rem;
}
.field-group:last-child {
    margin-bottom: 0;
}
.field-hint {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
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

<div class="row">
    <div class="col-12">
        <div class="highlight-box">
            <h6><i class="fas fa-database me-2"></i>Account Information Centre</h6>
            <p>This is the authoritative source for your company information. Data entered here is automatically shared with RCS Agent Registration, SMS SenderID Registration, Billing, VAT handling, Support tickets, and Compliance records.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Company Name<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control company-field" id="companyName" value="Acme Communications Ltd">
                                    <div class="field-hint">Legal registered company name</div>
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-robot"></i> RCS Registration</span>
                                        <span class="usage-chip"><i class="fas fa-id-badge"></i> SMS SenderID</span>
                                        <span class="usage-chip"><i class="fas fa-file-invoice"></i> Invoices</span>
                                    </div>
                                    <div class="validation-error">Company name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Trading Name<span class="optional-indicator">(Optional)</span></label>
                                    <input type="text" class="form-control" id="tradingName" value="Acme Comms" placeholder="If different from legal name">
                                    <div class="field-hint">Only if trading under a different name</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Company Number<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control company-field" id="companyNumber" value="12345678" placeholder="e.g., 12345678">
                                    <div class="field-hint">Companies House registration number</div>
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-robot"></i> RCS Registration</span>
                                        <span class="usage-chip"><i class="fas fa-id-badge"></i> SMS SenderID</span>
                                    </div>
                                    <div class="validation-error">Company number is required</div>
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
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-robot"></i> RCS Registration</span>
                                    </div>
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
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-file-invoice"></i> Invoices</span>
                                        <span class="usage-chip"><i class="fas fa-credit-card"></i> Payments</span>
                                    </div>
                                    <div class="validation-error">Please enter a valid email address</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Support Email Address<span class="required-indicator">*</span></label>
                                    <input type="email" class="form-control support-field" id="supportEmail" value="support@acmecomms.co.uk" placeholder="e.g., support@company.com">
                                    <div class="field-hint">Receives support ticket updates and general communications</div>
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-headset"></i> Support Tickets</span>
                                        <span class="usage-chip"><i class="fas fa-bell"></i> Notifications</span>
                                    </div>
                                    <div class="validation-error">Please enter a valid email address</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Incident Email Address<span class="required-indicator">*</span></label>
                                    <input type="email" class="form-control support-field" id="incidentEmail" value="incidents@acmecomms.co.uk" placeholder="e.g., incidents@company.com">
                                    <div class="field-hint">Receives urgent incident alerts and service disruption notices</div>
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-exclamation-triangle"></i> Incidents</span>
                                        <span class="usage-chip"><i class="fas fa-server"></i> Service Alerts</span>
                                    </div>
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
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-file-contract"></i> Contracts</span>
                                        <span class="usage-chip"><i class="fas fa-gavel"></i> Legal Notices</span>
                                    </div>
                                    <div class="validation-error">Full name is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Job Title<span class="required-indicator">*</span></label>
                                    <input type="text" class="form-control signatory-field" id="signatoryTitle" value="Managing Director" placeholder="e.g., CEO, Managing Director">
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-check-double"></i> Approvals</span>
                                    </div>
                                    <div class="validation-error">Job title is required</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">Email Address<span class="required-indicator">*</span></label>
                                    <input type="email" class="form-control signatory-field" id="signatoryEmail" value="j.wilson@acmecomms.co.uk" placeholder="e.g., signatory@company.com">
                                    <div class="field-hint">Used for contract signing and legal communications</div>
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-file-signature"></i> Contract Signing</span>
                                        <span class="usage-chip"><i class="fas fa-envelope-open-text"></i> Legal Comms</span>
                                    </div>
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
                        <i class="fas fa-receipt me-2 text-primary"></i>VAT & Tax Information
                        <span class="section-indicator required" id="vatStatusBadge"><i class="fas fa-exclamation-circle"></i> Required to go live</span>
                    </button>
                </h2>
                <div id="vatTaxInfo" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                    <div class="accordion-body">
                        <p class="text-muted small mb-4">VAT settings are used for billing and invoice generation. Changes to VAT details are audit-logged.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-group">
                                    <label class="form-label">VAT Registered<span class="required-indicator">*</span></label>
                                    <select class="form-select vat-field" id="vatRegistered">
                                        <option value="">Select...</option>
                                        <option value="yes" selected>Yes - VAT registered</option>
                                        <option value="no">No - Not VAT registered</option>
                                    </select>
                                    <div class="usage-chips">
                                        <span class="usage-chip"><i class="fas fa-file-invoice-dollar"></i> Billing</span>
                                        <span class="usage-chip"><i class="fas fa-calculator"></i> Invoices</span>
                                    </div>
                                    <div class="validation-error">Please select VAT registration status</div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="vatDetailsSection">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field-group">
                                        <label class="form-label">VAT Number<span class="required-indicator">*</span></label>
                                        <input type="text" class="form-control vat-detail-field" id="vatNumber" value="GB123456789" placeholder="e.g., GB123456789">
                                        <div class="field-hint" id="vatFormatHint">Format: GB followed by 9 digits</div>
                                        <div class="usage-chips">
                                            <span class="usage-chip"><i class="fas fa-file-invoice-dollar"></i> Invoicing</span>
                                            <span class="usage-chip"><i class="fas fa-percentage"></i> VAT Returns</span>
                                        </div>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    $('[data-bs-toggle="tooltip"]').tooltip();
    
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
                company_name: $('#companyName').val(),
                trading_name: $('#tradingName').val(),
                company_number: $('#companyNumber').val(),
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
    
    function validateVatNumber() {
        var country = $('#vatCountry').val();
        var vatNum = $('#vatNumber').val().trim().toUpperCase();
        
        if (!vatNum) {
            $('#vatNumber').addClass('is-invalid');
            $('#vatNumberError').text('VAT number is required');
            return false;
        }
        
        if (vatFormats[country]) {
            if (!vatFormats[country].pattern.test(vatNum)) {
                $('#vatNumber').addClass('is-invalid');
                $('#vatNumberError').text('Invalid VAT number format for ' + country);
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
            validateVatNumber();
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
        var requiredFields = ['#companyName', '#companyNumber', '#companySector', '#companyWebsite', 
                              '#regAddress1', '#regCity', '#regPostcode', '#regCountry'];
        
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

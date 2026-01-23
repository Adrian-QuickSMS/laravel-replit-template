@extends('layouts.admin')

@section('title', 'Account Details - ' . $account_id)

@push('styles')
<style>
.admin-page { padding: 1.5rem; }
.admin-breadcrumb { margin-bottom: 1rem; }
.admin-breadcrumb a { color: #6c757d; text-decoration: none; }
.admin-breadcrumb a:hover { color: #1e3a5f; }
.admin-breadcrumb .separator { margin: 0 0.5rem; color: #adb5bd; }

.account-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}
.account-title h4 { color: #1e3a5f; font-weight: 600; margin-bottom: 0.25rem; }
.account-title .account-id { font-size: 0.875rem; color: #6c757d; }
.account-actions { display: flex; gap: 0.5rem; }

.highlight-box {
    background: rgba(30, 58, 95, 0.08);
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.highlight-box h6 { color: #333; margin-bottom: 0.5rem; font-weight: 600; }
.highlight-box p { color: #6c757d; margin-bottom: 0; font-size: 0.875rem; }

.accordion-admin .accordion-button {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    padding: 1rem 1.25rem;
}
.accordion-admin .accordion-button:not(.collapsed) {
    background: rgba(30, 58, 95, 0.08);
    color: #1e3a5f;
    box-shadow: none;
}
.accordion-admin .accordion-button:focus {
    box-shadow: none;
    border-color: rgba(30, 58, 95, 0.3);
}
.accordion-admin .accordion-item {
    border: 1px solid #e9ecef;
    margin-bottom: 0.75rem;
    border-radius: 0.5rem !important;
    overflow: hidden;
}
.accordion-admin .accordion-body {
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
.section-indicator.required { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
.section-indicator.optional { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
.section-indicator.complete { background: rgba(40, 167, 69, 0.1); color: #28a745; }

.form-label { font-weight: 500; color: #495057; font-size: 0.875rem; margin-bottom: 0.375rem; }
.form-label .required-indicator { color: #dc3545; margin-left: 0.125rem; }
.form-label .optional-indicator { color: #6c757d; font-weight: 400; font-size: 0.75rem; margin-left: 0.25rem; }
.form-control, .form-select { font-size: 0.875rem; }
.form-control:focus, .form-select:focus {
    border-color: rgba(30, 58, 95, 0.5);
    box-shadow: 0 0 0 0.2rem rgba(30, 58, 95, 0.15);
}
.field-group { margin-bottom: 0.5rem; }
.field-hint { font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem; margin-bottom: 0.5rem; }
.col-md-6 { margin-bottom: 1.25rem; }

.section-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
    margin-top: 1.5rem;
}
.validation-error { font-size: 0.75rem; color: #dc3545; margin-top: 0.25rem; display: none; }
.is-invalid ~ .validation-error { display: block; }

.readonly-value {
    padding: 0.5rem 0.75rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #495057;
}

.btn-admin-primary { background: #1e3a5f; border-color: #1e3a5f; color: #fff; }
.btn-admin-primary:hover { background: #2d5a87; border-color: #2d5a87; color: #fff; }
.btn-admin-outline { border-color: #1e3a5f; color: #1e3a5f; }
.btn-admin-outline:hover { background: #1e3a5f; color: #fff; }

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
.selectable-tile:hover { border-color: rgba(30, 58, 95, 0.4); background: rgba(30, 58, 95, 0.03); }
.selectable-tile.selected { border-color: #1e3a5f; background: rgba(30, 58, 95, 0.08); }
.selectable-tile .tile-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.25rem;
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
}
.selectable-tile .tile-check {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    color: #1e3a5f;
    font-size: 1rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.selectable-tile.selected .tile-check { opacity: 1; }
.selectable-tile .tile-title { font-weight: 600; font-size: 0.875rem; color: #333; margin-bottom: 0.25rem; }
.selectable-tile .tile-desc { font-size: 0.75rem; color: #6c757d; margin-bottom: 0; line-height: 1.3; }

.nav-tabs .nav-link { color: #6c757d; border: none; border-bottom: 2px solid transparent; padding: 0.75rem 1.25rem; font-weight: 500; }
.nav-tabs .nav-link:hover { color: #1e3a5f; border-color: transparent; }
.nav-tabs .nav-link.active { color: #1e3a5f; background: transparent; border-color: transparent transparent #1e3a5f transparent; }

.pricing-display-card { border: 1px solid #e9ecef; border-radius: 0.75rem; overflow: hidden; }
.pricing-display-card.pricing-active { border: 2px solid #1e3a5f; position: relative; }
.pricing-display-card .active-badge {
    position: absolute;
    top: -1px;
    right: 1rem;
    background: #1e3a5f;
    color: #fff;
    padding: 0.25rem 0.75rem;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 0 0 0.375rem 0.375rem;
}
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="{{ route('admin.accounts.overview') }}">Accounts</a>
        <span class="separator">/</span>
        <span>{{ $account_id }}</span>
    </div>

    <div class="account-header">
        <div class="account-title">
            <h4 id="accountName">Loading...</h4>
            <div class="account-id">{{ $account_id }}</div>
        </div>
        <div class="account-actions">
            <button class="btn btn-admin-outline btn-sm" onclick="openAccountStructureModal()">
                <i class="fas fa-sitemap me-1"></i>View Account Structure
            </button>
            <a href="{{ route('admin.accounts.overview') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Accounts
            </a>
        </div>
    </div>

    <ul class="nav nav-tabs" id="accountTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#detailsContent" type="button" role="tab">
                <i class="fas fa-building me-2"></i>Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricingContent" type="button" role="tab">
                <i class="fas fa-tags me-2"></i>Pricing
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('admin.accounts.billing', ['accountId' => $account_id]) }}">
                <i class="fas fa-file-invoice-dollar me-2"></i>Billing
            </a>
        </li>
    </ul>

    <div class="tab-content" id="accountTabsContent">
        <div class="tab-pane fade show active" id="detailsContent" role="tabpanel">
            <div class="card border-top-0 rounded-top-0">
                <div class="card-body">
                    <div class="highlight-box">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle me-3 mt-1" style="color: #1e3a5f;"></i>
                            <div>
                                <strong>Account Information Centre</strong>
                                <p class="mb-0 mt-1 small">This is the authoritative source for company information. Data entered here is automatically shared with RCS Agent Registration, SMS SenderID Registration, Billing, VAT handling, Support tickets, and Compliance records.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion accordion-admin" id="accountDetailsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#signUpDetails" aria-expanded="true">
                                    <i class="fas fa-user-plus me-2" style="color: #1e3a5f;"></i>Sign Up Details
                                    <span class="section-indicator complete"><i class="fas fa-check-circle"></i> Complete</span>
                                </button>
                            </h2>
                            <div id="signUpDetails" class="accordion-collapse collapse show" data-bs-parent="#accountDetailsAccordion">
                                <div class="accordion-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <p class="text-muted small mb-0">All fields are mandatory. Editable by Account Owner or Admin only.</p>
                                        <span class="badge bg-light text-dark"><i class="fas fa-user-shield me-1"></i>Admin / Owner Only</span>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">First Name<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signupFirstName" value="Sarah">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Last Name<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signupLastName" value="Johnson">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Job Title<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signupJobTitle" value="Account Director">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Business Name<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signupBusinessName" value="">
                                                <div class="field-hint">Legal registered company name</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Business Email Address<span class="required-indicator">*</span></label>
                                                <input type="email" class="form-control" id="signupEmail" value="">
                                                <div class="field-hint">Must be unique across the platform</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Mobile Number<span class="required-indicator">*</span></label>
                                                <input type="tel" class="form-control" id="signupMobile" value="">
                                                <div class="field-hint">E.164 format preferred</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#companyInfo" aria-expanded="false">
                                    <i class="fas fa-building me-2" style="color: #1e3a5f;"></i>Company Information
                                    <span class="section-indicator complete"><i class="fas fa-check-circle"></i> Complete</span>
                                </button>
                            </h2>
                            <div id="companyInfo" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted small mb-3">Company information used across RCS, SMS SenderID, and billing systems.</p>
                                    
                                    <div class="field-group mb-4">
                                        <label class="form-label">Company Type<span class="required-indicator">*</span></label>
                                        <div class="row g-3" id="companyTypeSelector">
                                            <div class="col-md-4">
                                                <div class="selectable-tile selected" data-type="uk_limited">
                                                    <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    <div class="tile-icon"><i class="fas fa-building"></i></div>
                                                    <h6 class="tile-title">UK Limited</h6>
                                                    <p class="tile-desc">Private or public limited company</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selectable-tile" data-type="sole_trader">
                                                    <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    <div class="tile-icon"><i class="fas fa-user-tie"></i></div>
                                                    <h6 class="tile-title">Sole Trader</h6>
                                                    <p class="tile-desc">Self-employed individual</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selectable-tile" data-type="government">
                                                    <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                                    <div class="tile-icon"><i class="fas fa-landmark"></i></div>
                                                    <h6 class="tile-title">Government / NHS</h6>
                                                    <p class="tile-desc">Public sector organisations</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Company Name<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="companyName" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Trading Name<span class="optional-indicator">(Optional)</span></label>
                                                <input type="text" class="form-control" id="tradingName" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Company Number<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="companyNumber" value="">
                                                <div class="field-hint">Companies House registration number</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Sector<span class="required-indicator">*</span></label>
                                                <select class="form-select" id="companySector">
                                                    <option value="">Select sector...</option>
                                                    <option value="telecommunications">Telecommunications & Media</option>
                                                    <option value="financial">Financial Services</option>
                                                    <option value="healthcare">Healthcare</option>
                                                    <option value="retail">Retail & E-commerce</option>
                                                    <option value="technology">Technology</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Primary Website<span class="required-indicator">*</span></label>
                                                <input type="url" class="form-control" id="companyWebsite" value="">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-map-marker-alt me-2" style="color: #1e3a5f;"></i>Registered Address</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Address Line 1<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="regAddress1" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Address Line 2<span class="optional-indicator">(Optional)</span></label>
                                                <input type="text" class="form-control" id="regAddress2" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label class="form-label">City<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="regCity" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label class="form-label">Postcode<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="regPostcode" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label class="form-label">Country<span class="required-indicator">*</span></label>
                                                <select class="form-select" id="regCountry">
                                                    <option value="UK" selected>United Kingdom</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billingInfo" aria-expanded="false">
                                    <i class="fas fa-file-invoice me-2" style="color: #1e3a5f;"></i>Billing & Invoicing
                                    <span class="section-indicator complete"><i class="fas fa-check-circle"></i> Complete</span>
                                </button>
                            </h2>
                            <div id="billingInfo" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">VAT Number<span class="optional-indicator">(Optional)</span></label>
                                                <input type="text" class="form-control" id="vatNumber" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Billing Email<span class="required-indicator">*</span></label>
                                                <input type="email" class="form-control" id="billingEmail" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Purchase Order Required</label>
                                                <select class="form-select" id="poRequired">
                                                    <option value="no">No</option>
                                                    <option value="yes">Yes</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contactsSection" aria-expanded="false">
                                    <i class="fas fa-users me-2" style="color: #1e3a5f;"></i>Account Contacts
                                    <span class="section-indicator complete"><i class="fas fa-check-circle"></i> Complete</span>
                                </button>
                            </h2>
                            <div id="contactsSection" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted small mb-3">Primary and secondary contacts for account communications.</p>
                                    
                                    <h6 class="fw-bold mb-3">Primary Contact</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label class="form-label">Name<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label class="form-label">Email<span class="required-indicator">*</span></label>
                                                <input type="email" class="form-control" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label class="form-label">Phone<span class="required-indicator">*</span></label>
                                                <input type="tel" class="form-control" value="">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dataGovernance" aria-expanded="false">
                                    <i class="fas fa-shield-alt me-2" style="color: #1e3a5f;"></i>Data Governance & Compliance
                                    <span class="section-indicator complete"><i class="fas fa-check-circle"></i> Complete</span>
                                </button>
                            </h2>
                            <div id="dataGovernance" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Data Protection Officer<span class="optional-indicator">(Optional)</span></label>
                                                <input type="text" class="form-control" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">DPO Email<span class="optional-indicator">(Optional)</span></label>
                                                <input type="email" class="form-control" value="">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm">
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

        <div class="tab-pane fade" id="pricingContent" role="tabpanel">
            <div class="card border-top-0 rounded-top-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-1" style="color: #1e3a5f;">Current Pricing Model</h5>
                            <p class="text-muted small mb-0">Pricing plan applied to this account</p>
                        </div>
                        <button class="btn btn-admin-outline btn-sm" onclick="editPricingModal()">
                            <i class="fas fa-edit me-1"></i>Edit Pricing
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="pricing-display-card pricing-active">
                                <span class="active-badge">ACTIVE</span>
                                <div class="card-body">
                                    <h5 class="mb-3">Enterprise Plan</h5>
                                    <div class="mb-3">
                                        <strong>Pricing Basis:</strong> <span class="badge bg-primary">Submitted</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>SMS UK:</strong> £0.0285 per message
                                    </div>
                                    <div class="mb-2">
                                        <strong>RCS UK:</strong> £0.0350 per message
                                    </div>
                                    <div class="text-muted small mt-3">
                                        <i class="fas fa-sync-alt me-1"></i>Synced from HubSpot
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

@include('admin.accounts.partials.account-structure-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var accountId = '{{ $account_id }}';
    
    var accountData = {
        'ACC-1234': { name: 'Acme Corporation', email: 'sarah@acme.com', mobile: '+44 7700 900123', businessName: 'Acme Corporation Ltd' },
        'ACC-5678': { name: 'Finance Ltd', email: 'admin@finance.co.uk', mobile: '+44 7700 900456', businessName: 'Finance Ltd' },
        'ACC-7890': { name: 'NewClient', email: 'info@newclient.com', mobile: '+44 7700 900789', businessName: 'NewClient Inc' },
        'ACC-4567': { name: 'TestCo', email: 'test@testco.com', mobile: '+44 7700 900111', businessName: 'TestCo Ltd' },
        'ACC-9012': { name: 'HighRisk Corp', email: 'risk@highrisk.com', mobile: '+44 7700 900222', businessName: 'HighRisk Corp' },
        'ACC-3456': { name: 'MedTech Solutions', email: 'info@medtech.com', mobile: '+44 7700 900333', businessName: 'MedTech Solutions Ltd' }
    };
    
    var data = accountData[accountId] || { name: 'Unknown Account', email: '', mobile: '', businessName: '' };
    document.getElementById('accountName').textContent = data.name;
    document.getElementById('signupBusinessName').value = data.businessName;
    document.getElementById('signupEmail').value = data.email;
    document.getElementById('signupMobile').value = data.mobile;
    document.getElementById('companyName').value = data.businessName;
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ACCOUNT_DETAILS_VIEWED', accountId, { accountName: data.name });
    }
});

var accountStructureModal = null;
var currentHierarchyData = null;

function openAccountStructureModal() {
    var accountId = '{{ $account_id }}';
    var accountName = document.getElementById('accountName').textContent;
    
    if (!accountStructureModal) {
        accountStructureModal = new bootstrap.Modal(document.getElementById('accountStructureModal'));
    }
    
    document.getElementById('accountStructureModalLabel').textContent = 'Account Structure — ' + accountName;
    
    var hierarchyData = {
        'ACC-1234': {
            main: { name: 'Acme Corporation', id: 'ACC-1234', status: 'Active', type: 'Enterprise' },
            subAccounts: [
                { name: 'Acme Marketing', id: 'SUB-001', status: 'Active', users: [
                    { name: 'Sarah Wilson', email: 's.wilson@acme.com', role: 'Admin', status: 'Active' }
                ]},
                { name: 'Acme Sales', id: 'SUB-002', status: 'Active', users: [] }
            ],
            mainUsers: [
                { name: 'James Thompson', email: 'j.thompson@acme.com', role: 'Account Owner', status: 'Active' }
            ]
        }
    };
    
    currentHierarchyData = hierarchyData[accountId] || {
        main: { name: accountName, id: accountId, status: 'Active', type: 'Standard' },
        subAccounts: [],
        mainUsers: [{ name: 'Primary User', email: 'user@example.com', role: 'Account Owner', status: 'Active' }]
    };
    
    renderHierarchyTree();
    accountStructureModal.show();
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ACCOUNT_STRUCTURE_VIEWED', 'ACCOUNTS', { accountId: accountId });
    }
}

function renderHierarchyTree() {
    var data = currentHierarchyData;
    var html = '<div class="tree-item main-account" onclick="selectNode(\'main\')"><span class="tree-node-name">' + data.main.name + '</span></div>';
    
    if (data.mainUsers) {
        data.mainUsers.forEach(function(u, i) {
            html += '<div class="tree-node"><div class="tree-item" onclick="selectNode(\'main-user\', ' + i + ')"><span class="tree-node-name">' + u.name + '</span><span class="badge light badge-primary ms-2">' + u.role + '</span></div></div>';
        });
    }
    
    if (data.subAccounts) {
        data.subAccounts.forEach(function(s, i) {
            html += '<div class="tree-node"><div class="tree-item" onclick="selectNode(\'sub\', ' + i + ')"><span class="tree-node-name">' + s.name + '</span><span class="badge light badge-success ms-2">' + s.status + '</span></div></div>';
        });
    }
    
    document.getElementById('hierarchyTree').innerHTML = html;
}

function selectNode(type, index) {
    var data = currentHierarchyData;
    var html = '';
    if (type === 'main') {
        html = '<h6>Main Account</h6><table class="table table-sm"><tr><th>Status</th><td><span class="badge light badge-success">' + data.main.status + '</span></td></tr><tr><th>Type</th><td>' + data.main.type + '</td></tr></table>';
    } else if (type === 'main-user') {
        var u = data.mainUsers[index];
        html = '<h6>User</h6><table class="table table-sm"><tr><th>Name</th><td>' + u.name + '</td></tr><tr><th>Email</th><td>' + u.email + '</td></tr><tr><th>Role</th><td><span class="badge light badge-primary">' + u.role + '</span></td></tr></table>';
    } else if (type === 'sub') {
        var s = data.subAccounts[index];
        html = '<h6>Sub-Account</h6><table class="table table-sm"><tr><th>Name</th><td>' + s.name + '</td></tr><tr><th>Status</th><td><span class="badge light badge-success">' + s.status + '</span></td></tr></table>';
    }
    document.getElementById('nodeDetailsPanel').innerHTML = html;
}

function addSubAccount() { alert('Add Sub-account'); }
function inviteUser() { alert('Invite User'); }

function editPricingModal() {
    alert('Edit Pricing modal would open here');
}

document.querySelectorAll('.selectable-tile').forEach(function(tile) {
    tile.addEventListener('click', function() {
        document.querySelectorAll('.selectable-tile').forEach(function(t) {
            t.classList.remove('selected');
        });
        this.classList.add('selected');
    });
});
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Account Details - ' . $account_id)

@push('styles')
<style>
.admin-page { padding: 1.5rem; }

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
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accounts.overview') }}">Accounts</a></li>
            <li class="breadcrumb-item active">{{ $account_id }}</li>
        </ol>
    </div>

    <div class="account-header">
        <div class="account-title">
            <h4 id="accountName">{{ $account->company_name ?? 'Unknown Account' }}</h4>
            <div class="account-id">{{ $account->account_number ?? $account_id }}</div>
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
                                                <input type="text" class="form-control" id="signupFirstName" value="{{ $owner->first_name ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Last Name<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signupLastName" value="{{ $owner->last_name ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Job Title<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signupJobTitle" value="{{ $owner->job_title ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Business Name<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signupBusinessName" value="{{ $account->company_name ?? '' }}">
                                                <div class="field-hint">Legal registered company name</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Business Email Address<span class="required-indicator">*</span></label>
                                                <input type="email" class="form-control" id="signupEmail" value="{{ $owner->email ?? $account->email ?? '' }}">
                                                <div class="field-hint">Must be unique across the platform</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Mobile Number<span class="required-indicator">*</span></label>
                                                <input type="tel" class="form-control" id="signupMobile" value="{{ $owner->mobile_number ?? '' }}">
                                                <div class="field-hint">E.164 format preferred</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm" onclick="showSaveConfirmModal('Sign Up Details')">
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
                                                <input type="text" class="form-control" id="companyName" value="{{ $account->company_name ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Trading Name<span class="optional-indicator">(Optional)</span></label>
                                                <input type="text" class="form-control" id="tradingName" value="{{ $account->trading_name ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Company Number<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="companyNumber" value="{{ $account->company_number ?? '' }}">
                                                <div class="field-hint">Companies House registration number</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Sector<span class="required-indicator">*</span></label>
                                                <select class="form-select" id="companySector">
                                                    <option value="">Select sector...</option>
                                                    <option value="it-telecoms">IT and Telecoms</option>
                                                    <option value="government">Government</option>
                                                    <option value="health">Health</option>
                                                    <option value="logistics">Logistics</option>
                                                    <option value="travel-transport">Travel and Transport</option>
                                                    <option value="finance">Finance</option>
                                                    <option value="retail-hospitality">Retail and Hospitality</option>
                                                    <option value="media-leisure">Media and Leisure</option>
                                                    <option value="utilities">Utilities</option>
                                                    <option value="marketing-advertising">Marketing/Advertising Agency</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Primary Website<span class="required-indicator">*</span></label>
                                                <input type="url" class="form-control" id="companyWebsite" value="{{ $account->website ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-map-marker-alt me-2" style="color: #1e3a5f;"></i>Registered Address</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Address Line 1<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="regAddress1" value="{{ $account->address_line1 ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Address Line 2<span class="optional-indicator">(Optional)</span></label>
                                                <input type="text" class="form-control" id="regAddress2" value="{{ $account->address_line2 ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label class="form-label">City<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="regCity" value="{{ $account->city ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label class="form-label">Postcode<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="regPostcode" value="{{ $account->postcode ?? '' }}">
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
                                        <button type="button" class="btn btn-admin-primary btn-sm" onclick="showSaveConfirmModal('Company Information')">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#supportOperations" aria-expanded="false">
                                    <i class="fas fa-headset me-2" style="color: #1e3a5f;"></i>Support & Operations
                                    <span class="section-indicator complete"><i class="fas fa-check-circle"></i> Complete</span>
                                </button>
                            </h2>
                            <div id="supportOperations" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted small mb-4">Configure email addresses for billing notifications, support communications, and incident alerts. Shared or group inboxes are accepted.</p>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Accounts & Billing Email<span class="required-indicator">*</span></label>
                                                <input type="email" class="form-control" id="billingEmail" value="{{ $account->accounts_billing_email ?? '' }}" placeholder="e.g., accounts@company.com">
                                                <div class="field-hint">Receives invoices, payment confirmations, and billing alerts</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Support Email Address<span class="required-indicator">*</span></label>
                                                <input type="email" class="form-control" id="supportEmail" value="{{ $account->support_contact_email ?? '' }}" placeholder="e.g., support@company.com">
                                                <div class="field-hint">Receives support ticket updates and general communications</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Incident Email Address<span class="required-indicator">*</span></label>
                                                <input type="email" class="form-control" id="incidentEmail" value="{{ $account->incident_email ?? '' }}" placeholder="e.g., incidents@company.com">
                                                <div class="field-hint">Receives urgent incident alerts and service disruption notices</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm" onclick="showSaveConfirmModal('Support & Operations')">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contractSignatory" aria-expanded="false">
                                    <i class="fas fa-signature me-2" style="color: #1e3a5f;"></i>Contract Signatory
                                    <span class="section-indicator complete"><i class="fas fa-check-circle"></i> Complete</span>
                                </button>
                            </h2>
                            <div id="contractSignatory" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted small mb-4">The contract signatory is the individual authorised to enter contracts on behalf of the company. This person will receive legal notices and approval requests.</p>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Full Name<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signatoryName" value="{{ $account->signatory_name ?? '' }}" placeholder="e.g., John Smith">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Job Title<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="signatoryTitle" value="{{ $account->signatory_title ?? '' }}" placeholder="e.g., CEO, Managing Director">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Email Address<span class="required-indicator">*</span></label>
                                                <input type="email" class="form-control" id="signatoryEmail" value="{{ $account->signatory_email ?? '' }}" placeholder="e.g., signatory@company.com">
                                                <div class="field-hint">Used for contract signing and legal communications</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm" onclick="showSaveConfirmModal('Contract Signatory')">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#vatTaxInfo" aria-expanded="false">
                                    <i class="fas fa-receipt me-2" style="color: #1e3a5f;"></i>Billing, VAT and Tax Information
                                    <span class="section-indicator complete"><i class="fas fa-check-circle"></i> Complete</span>
                                </button>
                            </h2>
                            <div id="vatTaxInfo" class="accordion-collapse collapse" data-bs-parent="#accountDetailsAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted small mb-4">Billing and VAT settings are used for invoice generation. Changes to these details are audit-logged.</p>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Purchase Order Number</label>
                                                <input type="text" class="form-control" id="purchaseOrderNumber" value="{{ $account->purchase_order_number ?? '' }}" placeholder="e.g., PO-12345">
                                                <div class="field-hint">Optional: This number will be included on all invoices</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">VAT Registered<span class="required-indicator">*</span></label>
                                                <select class="form-select" id="vatRegistered">
                                                    <option value="">Select...</option>
                                                    <option value="yes" {{ ($account->vat_registered ?? false) ? 'selected' : '' }}>Yes - VAT registered</option>
                                                    <option value="no" {{ !($account->vat_registered ?? false) && $account->vat_registered !== null ? 'selected' : '' }}>No - Not VAT registered</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row" id="vatDetailsSection">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">VAT Number<span class="required-indicator">*</span></label>
                                                <input type="text" class="form-control" id="vatNumber" value="{{ $account->vat_number ?? '' }}" placeholder="e.g., GB123456789">
                                                <div class="field-hint">Format: GB followed by 9 digits</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">VAT Country<span class="required-indicator">*</span></label>
                                                <select class="form-select" id="vatCountry">
                                                    <option value="">Select country...</option>
                                                    <option value="GB" {{ ($account->tax_country ?? '') === 'GB' ? 'selected' : '' }}>United Kingdom (GB)</option>
                                                    <option value="DE" {{ ($account->tax_country ?? '') === 'DE' ? 'selected' : '' }}>Germany (DE)</option>
                                                    <option value="FR">France (FR)</option>
                                                    <option value="IE">Ireland (IE)</option>
                                                    <option value="NL">Netherlands (NL)</option>
                                                    <option value="ES">Spain (ES)</option>
                                                    <option value="IT">Italy (IT)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label class="form-label">Reverse Charge Applicable</label>
                                                <select class="form-select" id="reverseCharge">
                                                    <option value="no" {{ !($account->vat_reverse_charges ?? false) ? 'selected' : '' }}>No</option>
                                                    <option value="yes" {{ ($account->vat_reverse_charges ?? false) ? 'selected' : '' }}>Yes - EU Reverse Charge applies</option>
                                                </select>
                                                <div class="field-hint">Applies to B2B transactions with EU businesses</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="section-actions">
                                        <button type="button" class="btn btn-admin-primary btn-sm" onclick="showSaveConfirmModal('Billing, VAT and Tax Information')">
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
                        <div class="col-md-6 mb-4">
                            <div class="pricing-display-card pricing-active">
                                <span class="active-badge">{{ strtoupper($productTier) }}</span>
                                <div class="card-body">
                                    <h5 class="mb-3">{{ ucfirst($productTier) }} Plan</h5>
                                    @if($customerPrices->isNotEmpty())
                                        <div class="mb-3">
                                            <strong>Pricing Basis:</strong> <span class="badge bg-primary">Bespoke</span>
                                        </div>
                                        @foreach($customerPrices as $price)
                                            <div class="mb-2">
                                                <strong>{{ strtoupper(str_replace('_', ' ', $price->product_type)) }}@if($price->country_iso) {{ strtoupper($price->country_iso) }}@endif:</strong>
                                                £{{ number_format($price->unit_price, 4) }} {{ str_replace('_', ' ', $price->billing_type ?? 'per submitted') }}
                                                @if($price->source === 'hubspot')
                                                    <span class="text-muted small"><i class="fas fa-sync-alt ms-1"></i></span>
                                                @endif
                                            </div>
                                        @endforeach
                                        <div class="text-muted small mt-3">
                                            <i class="fas fa-user-tag me-1"></i>Customer-specific pricing applied
                                        </div>
                                    @elseif($tierPrices->isNotEmpty())
                                        <div class="mb-3">
                                            <strong>Pricing Basis:</strong> <span class="badge bg-info">Tier Standard</span>
                                        </div>
                                        @foreach($tierPrices as $price)
                                            <div class="mb-2">
                                                <strong>{{ strtoupper(str_replace('_', ' ', $price->product_type)) }}@if($price->country_iso) {{ strtoupper($price->country_iso) }}@endif:</strong>
                                                £{{ number_format($price->unit_price, 4) }} {{ str_replace('_', ' ', $price->billing_type ?? 'per submitted') }}
                                            </div>
                                        @endforeach
                                        <div class="text-muted small mt-3">
                                            <i class="fas fa-layer-group me-1"></i>Standard tier pricing
                                        </div>
                                    @else
                                        <div class="mb-3">
                                            <strong>Pricing Basis:</strong> <span class="badge bg-warning text-dark">Not Set</span>
                                        </div>
                                        <div class="text-muted">
                                            <i class="fas fa-exclamation-triangle me-1"></i>No pricing has been configured for this account. 
                                            Add tier prices or customer-specific prices to enable billing.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card border" style="border-color: #e6e6e6 !important;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>All Billable Product Types</h6>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-3">Product Type</th>
                                                <th>Category</th>
                                                <th>Pricing Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $allProducts = [
                                                    'sms' => 'Messaging',
                                                    'rcs_basic' => 'Messaging',
                                                    'rcs_single' => 'Messaging',
                                                    'inbound_sms' => 'Messaging',
                                                    'ai_query' => 'Value-Added',
                                                    'virtual_number_monthly' => 'Recurring',
                                                    'shortcode_monthly' => 'Recurring',
                                                    'support' => 'Service',
                                                ];
                                                $customerPriceTypes = $customerPrices->pluck('product_type')->toArray();
                                                $tierPriceTypes = $tierPrices->pluck('product_type')->toArray();
                                            @endphp
                                            @foreach($allProducts as $type => $category)
                                                <tr>
                                                    <td class="ps-3">{{ strtoupper(str_replace('_', ' ', $type)) }}</td>
                                                    <td><span class="badge bg-light text-dark">{{ $category }}</span></td>
                                                    <td>
                                                        @if(in_array($type, $customerPriceTypes))
                                                            <span class="badge bg-success">Bespoke</span>
                                                        @elseif(in_array($type, $tierPriceTypes))
                                                            <span class="badge bg-info">Tier</span>
                                                        @else
                                                            <span class="badge bg-secondary">Not Set</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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

<!-- Edit Pricing Modal -->
<div class="modal fade" id="editPricingModal" tabindex="-1" aria-labelledby="editPricingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; color: white;">
                <h5 class="modal-title" id="editPricingModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Account Pricing
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editPricingLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading pricing data...</p>
                </div>
                <div id="editPricingContent" class="d-none">
                    <div class="alert alert-warning d-flex align-items-start mb-3" id="editPricingBespokeWarning" style="background: rgba(255, 193, 7, 0.1); border-color: rgba(255, 193, 7, 0.3);">
                        <i class="fas fa-exclamation-triangle me-2 mt-1 text-warning"></i>
                        <div style="font-size: 0.85rem;">
                            <strong>Important:</strong> Editing any price will change this account to <strong>Bespoke</strong> pricing. Custom prices override standard tier prices.
                        </div>
                    </div>
                    <div class="alert alert-info d-flex align-items-start mb-3 d-none" id="editPricingTierChangeInfo" style="background: rgba(30, 58, 95, 0.06); border-color: rgba(30, 58, 95, 0.2);">
                        <i class="fas fa-info-circle me-2 mt-1" style="color: #1e3a5f;"></i>
                        <div style="font-size: 0.85rem;">
                            <strong>Tier Change:</strong> Switching to <strong id="tierChangeTarget">Starter</strong> will deactivate all bespoke prices. The account will use standard tier pricing.
                        </div>
                    </div>
                    <div class="mb-3 d-flex align-items-center gap-3">
                        <div>
                            <span class="text-muted small">Current Tier:</span>
                            <span class="badge bg-info ms-1" id="editPricingCurrentTier"></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">Change to:</span>
                            <select class="form-select form-select-sm" id="editPricingTierSelect" onchange="onTierSelectChange()" style="width: 160px;">
                                <option value="starter">Starter</option>
                                <option value="enterprise">Enterprise</option>
                                <option value="bespoke">Bespoke</option>
                            </select>
                        </div>
                    </div>
                    <table class="table table-sm table-hover mb-3" id="editPricingTable">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th class="ps-3">Service</th>
                                <th>Tier Price</th>
                                <th>Bespoke Price</th>
                                <th style="width: 180px;">New Price (£)</th>
                                <th style="width: 150px;">Billing</th>
                            </tr>
                        </thead>
                        <tbody id="editPricingTableBody">
                        </tbody>
                    </table>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Change Reason</label>
                        <textarea class="form-control form-control-sm" id="editPricingReason" rows="2" placeholder="Reason for pricing change (optional)"></textarea>
                    </div>
                </div>
                <div id="editPricingError" class="d-none">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span id="editPricingErrorMsg">Failed to load pricing data.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="editPricingFooter">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-admin-primary" id="savePricingBtn" onclick="saveAccountPricing()" disabled>
                    <i class="fas fa-save me-1"></i>Save Pricing
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tier Change Confirmation Modal -->
<div class="modal fade" id="tierChangeConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Confirm Tier Change
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="mb-2">Change this account to <strong id="tierChangeConfirmName"></strong> tier?</h6>
                    <p class="text-muted mb-0">All bespoke pricing will be deactivated and the account will use standard <strong id="tierChangeConfirmName2"></strong> prices.</p>
                </div>
                <div class="alert alert-warning d-flex align-items-start mt-3" style="background: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.3);">
                    <i class="fas fa-info-circle me-2 mt-1 text-warning"></i>
                    <div style="font-size: 0.85rem;">
                        This action cannot be undone. Any custom bespoke prices for this account will be permanently deactivated.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-admin-primary" id="confirmTierChangeBtn" onclick="executeTierChange()">
                    <i class="fas fa-check me-1"></i>Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Save Changes Confirmation Modal -->
<div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-labelledby="saveConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; color: white;">
                <h5 class="modal-title" id="saveConfirmModalLabel">
                    <i class="fas fa-save me-2"></i>Confirm Save Changes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="mb-2">Are you sure you want to save these changes?</h6>
                    <p class="text-muted mb-0" id="saveConfirmSectionName">Changes to this section will be applied immediately.</p>
                </div>
                
                <div class="alert alert-info d-flex align-items-start mt-3" style="background: rgba(30, 58, 95, 0.08); border-color: rgba(30, 58, 95, 0.2);">
                    <i class="fas fa-info-circle me-2 mt-1" style="color: #1e3a5f;"></i>
                    <div style="font-size: 0.85rem;">
                        <strong>Note:</strong> Data entered here is automatically shared with RCS Agent Registration, SMS SenderID Registration, Billing, VAT handling, Support tickets, and Compliance records.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-admin-primary" id="confirmSaveBtn" onclick="confirmSaveChanges()">
                    <i class="fas fa-check me-1"></i>Confirm Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="saveSuccessToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>Changes saved successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var accountId = @json($account_id);
    
    var data = {
        name: @json($account->company_name ?? 'Unknown Account'),
        email: @json($owner->email ?? $account->email ?? ''),
        mobile: @json($owner->mobile_number ?? ''),
        businessName: @json($account->company_name ?? ''),
        companyType: @json($account->company_type ?? ''),
        sector: @json($account->business_sector ?? '')
    };
    document.getElementById('accountName').textContent = data.name;
    if (document.getElementById('signupBusinessName')) document.getElementById('signupBusinessName').value = data.businessName;
    if (document.getElementById('signupEmail')) document.getElementById('signupEmail').value = data.email;
    if (document.getElementById('signupMobile')) document.getElementById('signupMobile').value = data.mobile;
    
    if (data.companyType) {
        document.querySelectorAll('#companyTypeSelector .selectable-tile').forEach(function(tile) {
            tile.classList.remove('selected');
            if (tile.getAttribute('data-type') === data.companyType) {
                tile.classList.add('selected');
            }
        });
    }
    
    if (data.sector && document.getElementById('companySector')) {
        document.getElementById('companySector').value = data.sector;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ACCOUNT_DETAILS_VIEWED', accountId, { accountName: data.name });
    }
});

var accountStructureModal = null;
var currentHierarchyData = null;

function openAccountStructureModal() {
    var accountId = @json($account_id);
    var accountName = document.getElementById('accountName').textContent;
    
    if (!accountStructureModal) {
        accountStructureModal = new bootstrap.Modal(document.getElementById('accountStructureModal'));
    }
    
    document.getElementById('accountStructureModalLabel').textContent = 'Account Structure — ' + accountName;
    
    currentHierarchyData = {
        main: { name: @json($account->company_name ?? 'Unknown'), id: @json($account->account_number ?? $account_id), status: @json(ucfirst($account->status ?? 'active')), type: @json(ucfirst($account->account_type ?? 'standard')) },
        subAccounts: [],
        mainUsers: [
            @if($owner)
            { name: @json(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')), email: @json($owner->email ?? ''), role: @json(ucfirst($owner->role ?? 'owner')), status: @json(ucfirst($owner->status ?? 'active')) }
            @endif
        ]
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

var editPricingModalInstance = null;
var editPricingData = [];

function editPricingModal() {
    var accountId = @json($account_id);
    if (!editPricingModalInstance) {
        editPricingModalInstance = new bootstrap.Modal(document.getElementById('editPricingModal'));
    }

    document.getElementById('editPricingLoading').classList.remove('d-none');
    document.getElementById('editPricingContent').classList.add('d-none');
    document.getElementById('editPricingError').classList.add('d-none');
    document.getElementById('savePricingBtn').disabled = true;
    document.getElementById('editPricingReason').value = '';

    editPricingModalInstance.show();

    fetch('/admin/api/accounts/' + accountId + '/pricing', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function(res) {
        if (!res.ok) throw new Error('Server returned ' + res.status);
        return res.json();
    })
    .then(function(data) {
        if (!data.success) throw new Error(data.error || 'Failed to load pricing');

        editPricingData = data.items;
        var currentTier = data.product_tier || 'starter';
        document.getElementById('editPricingCurrentTier').textContent = currentTier.charAt(0).toUpperCase() + currentTier.slice(1);
        document.getElementById('editPricingTierSelect').value = currentTier;
        document.getElementById('editPricingTierSelect').setAttribute('data-original-tier', currentTier);

        var tbody = document.getElementById('editPricingTableBody');
        var html = '';
        intlCountriesList = data.countries || [];

        data.items.forEach(function(item, idx) {
            var currentPrice = item.has_bespoke ? item.bespoke_price : (item.tier_price !== null ? item.tier_price : '');
            var billingType = item.billing_type || 'per_submitted';
            var billingCol = '';

            if (item.supports_billing_type) {
                billingCol = '<select class="form-select form-select-sm billing-type-select" data-slug="' + item.slug + '" data-original-bt="' + billingType + '">' +
                    '<option value="per_submitted"' + (billingType === 'per_submitted' ? ' selected' : '') + '>Per Submitted</option>' +
                    '<option value="per_delivered"' + (billingType === 'per_delivered' ? ' selected' : '') + '>Per Delivered</option>' +
                '</select>';
            } else {
                billingCol = '<span class="text-muted small">—</span>';
            }

            var expandBtn = '';
            if (item.supports_country_pricing) {
                expandBtn = ' <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1 ms-1" onclick="toggleCountryPricing(\'' + item.slug + '\')" title="Per-country pricing">' +
                    '<i class="fas fa-globe fa-xs"></i> <i class="fas fa-chevron-down fa-xs" id="countryChevron_' + item.slug + '"></i></button>';
            }

            var tierPriceDisplay = item.tier_price_formatted;
            if (item.enterprise_price_formatted !== undefined) {
                tierPriceDisplay = '<span class="text-muted small" title="Enterprise">E:</span> ' + item.enterprise_price_formatted;
                if (item.starter_price_formatted !== undefined && item.starter_price_formatted !== 'N/A') {
                    tierPriceDisplay += '<br><span class="text-muted small" title="Starter">S:</span> ' + item.starter_price_formatted;
                }
            }

            html += '<tr data-row-slug="' + item.slug + '">' +
                '<td class="ps-3"><strong>' + item.display_name + '</strong>' + expandBtn + '<br><span class="text-muted small">' + item.unit_label + '</span></td>' +
                '<td>' + tierPriceDisplay + '</td>' +
                '<td>' + (item.has_bespoke ? '<span class="badge bg-success">Bespoke</span> ' + item.bespoke_price_formatted : '<span class="text-muted small">—</span>') + '</td>' +
                '<td><div class="input-group input-group-sm">' +
                    '<span class="input-group-text">£</span>' +
                    '<input type="number" class="form-control form-control-sm pricing-input" ' +
                        'data-slug="' + item.slug + '" ' +
                        'data-original="' + currentPrice + '" ' +
                        'value="' + currentPrice + '" ' +
                        'step="0.000001" min="0" placeholder="0.00">' +
                '</div></td>' +
                '<td>' + billingCol + '</td>' +
                '</tr>';

            if (item.supports_country_pricing) {
                var existingCountryMap = {};
                (item.country_prices || []).forEach(function(cp) {
                    existingCountryMap[cp.country_iso] = cp;
                });

                html += '<tr class="country-pricing-rows d-none" id="countryRows_' + item.slug + '">' +
                    '<td colspan="5" class="ps-4 pe-3 py-2" style="background: #f8f9fa;">' +
                    '<div class="d-flex justify-content-between align-items-center mb-2">' +
                        '<strong class="small"><i class="fas fa-globe me-1"></i>Per-Country Pricing</strong>' +
                        '<button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" onclick="addCountryRow(\'' + item.slug + '\')">' +
                            '<i class="fas fa-plus fa-xs me-1"></i>Add Country</button>' +
                    '</div>' +
                    '<div id="countryList_' + item.slug + '">';

                (item.country_prices || []).forEach(function(cp) {
                    html += buildCountryRowHtml(item.slug, cp.country_iso, cp.unit_price, cp.billing_type, intlCountriesList, true);
                });

                html += '</div></td></tr>';
            }
        });
        tbody.innerHTML = html;

        document.querySelectorAll('.pricing-input, .billing-type-select').forEach(function(el) {
            el.addEventListener('input', function() { checkPricingChanges(); });
            el.addEventListener('change', function() { checkPricingChanges(); });
        });

        document.getElementById('editPricingLoading').classList.add('d-none');
        document.getElementById('editPricingContent').classList.remove('d-none');
    })
    .catch(function(err) {
        document.getElementById('editPricingLoading').classList.add('d-none');
        document.getElementById('editPricingErrorMsg').textContent = err.message || 'Failed to load pricing data.';
        document.getElementById('editPricingError').classList.remove('d-none');
    });
}

var intlCountriesList = [];

function buildCountryRowHtml(slug, countryIso, unitPrice, billingType, countries, isExisting) {
    var opts = '<option value="">Select country...</option>';
    countries.forEach(function(c) {
        opts += '<option value="' + c.iso + '"' + (c.iso === countryIso ? ' selected' : '') + '>' + c.name + ' (' + c.iso + ')</option>';
    });
    var origPrice = isExisting ? unitPrice : '';
    var origBt = isExisting ? (billingType || 'per_submitted') : '';
    return '<div class="d-flex align-items-center gap-2 mb-2 country-price-row" data-slug="' + slug + '" data-country-original="' + (countryIso || '') + '" data-price-original="' + origPrice + '" data-bt-original="' + origBt + '">' +
        '<select class="form-select form-select-sm country-iso-select" style="width:180px;" ' + (isExisting ? 'disabled' : '') + '>' + opts + '</select>' +
        '<div class="input-group input-group-sm" style="width:140px;">' +
            '<span class="input-group-text">£</span>' +
            '<input type="number" class="form-control form-control-sm country-price-input" value="' + (unitPrice || '') + '" step="0.000001" min="0" placeholder="0.00">' +
        '</div>' +
        '<select class="form-select form-select-sm country-bt-select" style="width:130px;">' +
            '<option value="per_submitted"' + ((billingType || 'per_submitted') === 'per_submitted' ? ' selected' : '') + '>Per Submitted</option>' +
            '<option value="per_delivered"' + (billingType === 'per_delivered' ? ' selected' : '') + '>Per Delivered</option>' +
        '</select>' +
        '<button type="button" class="btn btn-outline-danger btn-sm py-0 px-1" onclick="removeCountryRow(this)" title="Remove"><i class="fas fa-times fa-xs"></i></button>' +
    '</div>';
}

function toggleCountryPricing(slug) {
    var rows = document.getElementById('countryRows_' + slug);
    var chevron = document.getElementById('countryChevron_' + slug);
    if (rows) {
        rows.classList.toggle('d-none');
        if (chevron) {
            chevron.classList.toggle('fa-chevron-down');
            chevron.classList.toggle('fa-chevron-up');
        }
    }
}

function addCountryRow(slug) {
    var container = document.getElementById('countryList_' + slug);
    if (container) {
        container.insertAdjacentHTML('beforeend', buildCountryRowHtml(slug, '', '', 'per_submitted', intlCountriesList, false));
        container.querySelectorAll('input, select').forEach(function(el) {
            el.addEventListener('input', function() { checkPricingChanges(); });
            el.addEventListener('change', function() { checkPricingChanges(); });
        });
        checkPricingChanges();
    }
}

function removeCountryRow(btn) {
    btn.closest('.country-price-row').remove();
    checkPricingChanges();
}

function onTierSelectChange() {
    var tierSelect = document.getElementById('editPricingTierSelect');
    var selectedTier = tierSelect.value;
    var originalTier = tierSelect.getAttribute('data-original-tier');
    var isBespoke = selectedTier === 'bespoke';
    var pricingTable = document.getElementById('editPricingTable');
    var bespokeWarning = document.getElementById('editPricingBespokeWarning');
    var tierChangeInfo = document.getElementById('editPricingTierChangeInfo');

    if (!isBespoke) {
        pricingTable.style.opacity = '0.5';
        pricingTable.style.pointerEvents = 'none';
        bespokeWarning.classList.add('d-none');
        tierChangeInfo.classList.remove('d-none');
        document.getElementById('tierChangeTarget').textContent = selectedTier.charAt(0).toUpperCase() + selectedTier.slice(1);
    } else {
        pricingTable.style.opacity = '1';
        pricingTable.style.pointerEvents = '';
        bespokeWarning.classList.remove('d-none');
        tierChangeInfo.classList.add('d-none');
    }

    checkPricingChanges();
}

function checkPricingChanges() {
    var hasChanges = false;

    var tierSelect = document.getElementById('editPricingTierSelect');
    var selectedTier = tierSelect.value;
    var originalTier = tierSelect.getAttribute('data-original-tier');
    if (selectedTier !== originalTier) {
        hasChanges = true;
    }

    if (selectedTier === 'bespoke') {
        document.querySelectorAll('.pricing-input').forEach(function(input) {
            var original = parseFloat(input.getAttribute('data-original')) || 0;
            var current = parseFloat(input.value) || 0;
            if (Math.abs(original - current) > 0.0000001) {
                hasChanges = true;
                input.closest('tr').style.background = 'rgba(25, 135, 84, 0.06)';
            } else {
                input.closest('tr').style.background = '';
            }
        });
        document.querySelectorAll('.billing-type-select').forEach(function(sel) {
            var original = sel.getAttribute('data-original-bt');
            if (sel.value !== original) {
                hasChanges = true;
                sel.closest('tr').style.background = 'rgba(25, 135, 84, 0.06)';
            }
        });
        document.querySelectorAll('.country-price-row').forEach(function(row) {
            var origCountry = row.getAttribute('data-country-original');
            var origPrice = row.getAttribute('data-price-original');
            var origBt = row.getAttribute('data-bt-original');
            var currentIso = row.querySelector('.country-iso-select').value;
            var currentPrice = row.querySelector('.country-price-input').value;
            var currentBt = row.querySelector('.country-bt-select').value;
            if (!origCountry && currentIso && currentPrice) {
                hasChanges = true;
            } else if (origCountry) {
                if (Math.abs((parseFloat(origPrice) || 0) - (parseFloat(currentPrice) || 0)) > 0.0000001 || origBt !== currentBt) {
                    hasChanges = true;
                }
            }
        });
    }

    document.getElementById('savePricingBtn').disabled = !hasChanges;
}

var tierChangeConfirmModalInstance = null;

function saveAccountPricing() {
    var accountId = @json($account_id);
    var selectedTier = document.getElementById('editPricingTierSelect').value;
    var originalTier = document.getElementById('editPricingTierSelect').getAttribute('data-original-tier');
    var changedPrices = [];

    if (selectedTier !== 'bespoke' && selectedTier !== originalTier) {
        var tierLabel = selectedTier.charAt(0).toUpperCase() + selectedTier.slice(1);
        document.getElementById('tierChangeConfirmName').textContent = tierLabel;
        document.getElementById('tierChangeConfirmName2').textContent = tierLabel;
        if (!tierChangeConfirmModalInstance) {
            tierChangeConfirmModalInstance = new bootstrap.Modal(document.getElementById('tierChangeConfirmModal'));
        }
        tierChangeConfirmModalInstance.show();
        return;
    }

    if (selectedTier === 'bespoke') {
        document.querySelectorAll('.pricing-input').forEach(function(input) {
            var slug = input.getAttribute('data-slug');
            var original = parseFloat(input.getAttribute('data-original')) || 0;
            var current = parseFloat(input.value) || 0;
            var btSelect = document.querySelector('.billing-type-select[data-slug="' + slug + '"]');
            var billingType = btSelect ? btSelect.value : 'per_submitted';
            var originalBt = btSelect ? btSelect.getAttribute('data-original-bt') : 'per_submitted';

            if (Math.abs(original - current) > 0.0000001 || (btSelect && billingType !== originalBt)) {
                changedPrices.push({ slug: slug, unit_price: current, billing_type: billingType });
            }
        });

        document.querySelectorAll('.country-price-row').forEach(function(row) {
            var slug = row.getAttribute('data-slug');
            var countryIso = row.querySelector('.country-iso-select').value;
            var unitPrice = parseFloat(row.querySelector('.country-price-input').value) || 0;
            var billingType = row.querySelector('.country-bt-select').value;
            var origCountry = row.getAttribute('data-country-original');
            var origPrice = row.getAttribute('data-price-original');
            var origBt = row.getAttribute('data-bt-original');

            if (!countryIso) return;

            if (!origCountry || Math.abs((parseFloat(origPrice) || 0) - unitPrice) > 0.0000001 || origBt !== billingType) {
                changedPrices.push({ slug: slug, unit_price: unitPrice, billing_type: billingType, country_iso: countryIso });
            }
        });

        if (changedPrices.length === 0 && selectedTier === originalTier) return;
    }

    var btn = document.getElementById('savePricingBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    var payload = {
        product_tier: selectedTier,
        change_reason: document.getElementById('editPricingReason').value || null
    };
    if (changedPrices.length > 0) {
        payload.prices = changedPrices;
    }

    fetch('/admin/api/accounts/' + accountId + '/pricing', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    })
    .then(function(res) {
        if (!res.ok) throw new Error('Server returned ' + res.status);
        return res.json();
    })
    .then(function(data) {
        if (!data.success) throw new Error(data.error || 'Failed to save pricing');

        if (editPricingModalInstance) editPricingModalInstance.hide();

        var toastEl = document.getElementById('saveSuccessToast');
        var tierLabel = selectedTier.charAt(0).toUpperCase() + selectedTier.slice(1);
        var msg;
        if (selectedTier !== 'bespoke') {
            msg = '<i class="fas fa-check-circle me-2"></i>Account changed to ' + tierLabel + ' tier. All bespoke prices deactivated.';
        } else {
            msg = '<i class="fas fa-check-circle me-2"></i>' + changedPrices.length + ' price(s) updated. Account set to Bespoke pricing.';
        }
        toastEl.querySelector('.toast-body').innerHTML = msg;
        var toast = new bootstrap.Toast(toastEl);
        toast.show();

        setTimeout(function() { location.reload(); }, 1200);
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Pricing';
        alert('Error: ' + (err.message || 'Failed to save pricing'));
    });
}

function executeTierChange() {
    if (tierChangeConfirmModalInstance) tierChangeConfirmModalInstance.hide();

    var accountId = @json($account_id);
    var selectedTier = document.getElementById('editPricingTierSelect').value;
    var tierLabel = selectedTier.charAt(0).toUpperCase() + selectedTier.slice(1);

    var btn = document.getElementById('savePricingBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    fetch('/admin/api/accounts/' + accountId + '/pricing', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            product_tier: selectedTier,
            change_reason: document.getElementById('editPricingReason').value || 'Tier changed to ' + tierLabel
        })
    })
    .then(function(res) {
        if (!res.ok) throw new Error('Server returned ' + res.status);
        return res.json();
    })
    .then(function(data) {
        if (!data.success) throw new Error(data.error || 'Failed to change tier');

        if (editPricingModalInstance) editPricingModalInstance.hide();

        var toastEl = document.getElementById('saveSuccessToast');
        toastEl.querySelector('.toast-body').innerHTML =
            '<i class="fas fa-check-circle me-2"></i>Account changed to ' + tierLabel + ' tier. All bespoke prices deactivated.';
        var toast = new bootstrap.Toast(toastEl);
        toast.show();

        setTimeout(function() { location.reload(); }, 1200);
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Pricing';
        alert('Error: ' + (err.message || 'Failed to change tier'));
    });
}

document.querySelectorAll('.selectable-tile').forEach(function(tile) {
    tile.addEventListener('click', function() {
        document.querySelectorAll('.selectable-tile').forEach(function(t) {
            t.classList.remove('selected');
        });
        this.classList.add('selected');
    });
});

// Save Changes Confirmation Modal Functions
var currentSectionName = '';
var saveConfirmModal = null;

function showSaveConfirmModal(sectionName) {
    currentSectionName = sectionName;
    document.getElementById('saveConfirmSectionName').innerHTML = 
        'Changes to <strong>' + sectionName + '</strong> will be applied immediately.';
    
    if (!saveConfirmModal) {
        saveConfirmModal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
    }
    saveConfirmModal.show();
}

function confirmSaveChanges() {
    // Close the modal
    if (saveConfirmModal) {
        saveConfirmModal.hide();
    }
    
    // TODO: Backend integration - Save changes to database
    // This is where the actual save API call would go
    console.log('[AccountDetails] Saving changes for section:', currentSectionName);
    
    // Show success toast
    var toastEl = document.getElementById('saveSuccessToast');
    toastEl.querySelector('.toast-body').innerHTML = 
        '<i class="fas fa-check-circle me-2"></i>' + currentSectionName + ' saved successfully!';
    var toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    // Log audit event
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAction({
            eventType: 'ACCOUNT_DETAILS_UPDATED',
            section: currentSectionName,
            accountId: accountId
        });
    }
}
</script>
@endpush

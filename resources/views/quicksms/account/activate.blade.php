@extends('layouts.quicksms')

@section('title', $page_title ?? 'Activate Your Account')

@push('styles')
<style>
    .activate-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .activate-header {
        text-align: center;
        padding: 2rem 0;
    }
    .activate-header .icon-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
    .activate-header .icon-circle i {
        font-size: 2rem;
        color: #886cc0;
    }
    .step-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    .step-card.completed {
        border-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    }
    .step-card.current {
        border-color: #886cc0;
        box-shadow: 0 0 0 3px rgba(136, 108, 192, 0.1);
    }
    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .step-card.completed .step-number {
        background: #10b981;
        color: white;
    }
    .step-card.current .step-number {
        background: #886cc0;
        color: white;
    }
    .step-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    .step-description {
        color: #6b7280;
        font-size: 0.875rem;
    }
    .requirement-list {
        list-style: none;
        padding: 0;
        margin: 1rem 0 0;
    }
    .requirement-list li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
        font-size: 0.875rem;
        color: #4b5563;
    }
    .requirement-list li i.complete {
        color: #10b981;
    }
    .requirement-list li i.pending {
        color: #d1d5db;
    }
    .help-card {
        background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
    }
    
    /* Modal Styles */
    #completeDetailsModal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    #completeDetailsModal .modal-header {
        background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%);
        border-bottom: 1px solid #e9d5ff;
        border-radius: 16px 16px 0 0;
        padding: 1.25rem 1.5rem;
    }
    #completeDetailsModal .modal-title {
        font-weight: 600;
        color: #1f2937;
    }
    #completeDetailsModal .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    #completeDetailsModal .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
    }
    .form-section {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .form-section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .form-section-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #374151;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-title i {
        color: #886cc0;
    }
    .form-label .required {
        color: #ef4444;
    }
    .progress-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        background: #f9fafb;
        border-radius: 8px;
    }
    .progress-indicator .progress {
        flex-grow: 1;
        height: 8px;
        border-radius: 4px;
    }
    .progress-indicator .progress-bar {
        background: linear-gradient(90deg, #886cc0 0%, #a78bfa 100%);
        border-radius: 4px;
    }
    .progress-text {
        font-size: 0.75rem;
        color: #6b7280;
        min-width: 60px;
        text-align: right;
    }
    
    /* Company Type Tiles */
    .company-type-tiles {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .company-type-tile {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        position: relative;
        background: #fff;
    }
    .company-type-tile:hover {
        border-color: rgba(136, 108, 192, 0.4);
        background: rgba(136, 108, 192, 0.03);
    }
    .company-type-tile.selected {
        border-color: #886cc0;
        background: rgba(136, 108, 192, 0.08);
    }
    .company-type-tile .tile-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-size: 1rem;
    }
    .company-type-tile .tile-icon.bg-purple { background: rgba(136, 108, 192, 0.15); color: #886cc0; }
    .company-type-tile .tile-icon.bg-amber { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .company-type-tile .tile-icon.bg-blue { background: rgba(147, 197, 253, 0.3); color: #60a5fa; }
    .company-type-tile .tile-check {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        color: #886cc0;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .company-type-tile.selected .tile-check {
        opacity: 1;
    }
    .company-type-tile .tile-title {
        font-weight: 600;
        font-size: 0.8rem;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    .company-type-tile .tile-desc {
        font-size: 0.7rem;
        color: #6b7280;
        line-height: 1.3;
        margin: 0;
    }
    
    /* Lookup Row */
    .lookup-row {
        display: flex;
        gap: 0.5rem;
    }
    .lookup-row .form-control {
        flex: 1;
    }
    .lookup-status {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    .lookup-status.success { color: #10b981; }
    .lookup-status.error { color: #ef4444; }
    .lookup-status.loading { color: #886cc0; }
    
    .field-hint {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    .validation-error {
        font-size: 0.75rem;
        color: #ef4444;
        margin-top: 0.25rem;
        display: none;
    }
    .is-invalid ~ .validation-error {
        display: block;
    }
    
    @media (max-width: 576px) {
        .company-type-tiles {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="activate-container">
    <div class="activate-header">
        <div class="icon-circle">
            <i class="fas fa-rocket"></i>
        </div>
        <h2 class="mb-2">Activate Your Account</h2>
        <p class="text-muted">Complete the steps below to unlock full messaging capabilities</p>
    </div>

    <div id="activation-steps">
        <div class="step-card" id="step-details" data-step="1">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">1</div>
                <div class="flex-grow-1">
                    <div class="step-title">Complete Account Details</div>
                    <div class="step-description">Provide your company information for compliance and billing</div>
                    <ul class="requirement-list" id="details-requirements">
                        <li><i class="fas fa-circle pending" id="req-company-type"></i> Company type</li>
                        <li><i class="fas fa-circle pending" id="req-company"></i> Company information</li>
                        <li><i class="fas fa-circle pending" id="req-address"></i> Registered address</li>
                        <li><i class="fas fa-circle pending" id="req-support"></i> Support & operations</li>
                        <li><i class="fas fa-circle pending" id="req-signatory"></i> Contract signatory</li>
                        <li><i class="fas fa-circle pending" id="req-vat"></i> VAT & tax information</li>
                    </ul>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="btn-complete-details" data-bs-toggle="modal" data-bs-target="#completeDetailsModal">
                        <i class="fas fa-edit me-1"></i> Complete Details
                    </button>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step1-status">Pending</span>
                </div>
            </div>
        </div>

        <div class="step-card" id="step-payment" data-step="2">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">2</div>
                <div class="flex-grow-1">
                    <div class="step-title">Make Your First Payment</div>
                    <div class="step-description">Purchase message credits to activate your account</div>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Once you complete your account details, you can purchase message credits to start sending live messages.
                    </p>
                    <button class="btn btn-sm btn-primary mt-3" id="btn-purchase" disabled>
                        <i class="fas fa-credit-card me-1"></i> Purchase Credits
                    </button>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step2-status">Waiting</span>
                </div>
            </div>
        </div>

        <div class="step-card" id="step-activated" data-step="3">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">3</div>
                <div class="flex-grow-1">
                    <div class="step-title">Account Activated</div>
                    <div class="step-description">Your account is fully activated and ready for live messaging</div>
                    <div class="mt-3" id="activated-actions" style="display: none;">
                        <a href="{{ route('messages.send') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-paper-plane me-1"></i> Send Your First Message
                        </a>
                    </div>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step3-status">Pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="help-card">
        <div class="d-flex align-items-start gap-3">
            <div>
                <i class="fas fa-question-circle" style="font-size: 1.5rem; color: #886cc0;"></i>
            </div>
            <div>
                <h6 class="mb-1">Need Help?</h6>
                <p class="text-muted small mb-2">
                    If you have questions about activation or need assistance, our support team is here to help.
                </p>
                <a href="{{ route('support.create-ticket') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-headset me-1"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Complete Details Modal -->
<div class="modal fade" id="completeDetailsModal" tabindex="-1" aria-labelledby="completeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="completeDetailsModalLabel">
                        <i class="fas fa-building me-2"></i>Complete Account Details
                    </h5>
                    <p class="text-muted small mb-0 mt-1">This information is required to activate your account</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="progress-indicator">
                    <span class="small text-muted">Completion:</span>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" id="form-progress"></div>
                    </div>
                    <span class="progress-text" id="form-progress-text">0 of 6</span>
                </div>

                <form id="accountDetailsForm">
                    <!-- Company Type Selector -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-building"></i> Company Type
                        </div>
                        <div class="company-type-tiles" id="companyTypeSelector">
                            <div class="company-type-tile" data-type="uk_limited">
                                <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                <div class="tile-icon bg-purple"><i class="fas fa-building"></i></div>
                                <div class="tile-title">UK Limited</div>
                                <p class="tile-desc">Private or public limited company registered with Companies House</p>
                            </div>
                            <div class="company-type-tile" data-type="sole_trader">
                                <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                <div class="tile-icon bg-amber"><i class="fas fa-user-tie"></i></div>
                                <div class="tile-title">Sole Trader</div>
                                <p class="tile-desc">Self-employed individual trading under their own name</p>
                            </div>
                            <div class="company-type-tile" data-type="government">
                                <div class="tile-check"><i class="fas fa-check-circle"></i></div>
                                <div class="tile-icon bg-blue"><i class="fas fa-landmark"></i></div>
                                <div class="tile-title">Government & NHS</div>
                                <p class="tile-desc">Public sector organisations and health services</p>
                            </div>
                        </div>
                        <input type="hidden" id="company_type" name="company_type" value="{{ ($account->company_type ?? '') === 'government_nhs' ? 'government' : ($account->company_type ?? '') }}">
                        <div class="validation-error" id="companyTypeError">Please select a company type</div>
                    </div>

                    <!-- Company Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-info-circle"></i> Company Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">Company Name <span class="required"></span></label>
                                <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Legal registered company name" value="{{ $account->company_name ?? '' }}" required>
                                <div class="validation-error">Company name is required</div>
                            </div>
                            <div class="col-md-6">
                                <label for="trading_name" class="form-label">Trading Name <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="trading_name" name="trading_name" placeholder="If different from legal name" value="{{ $account->trading_name ?? '' }}">
                            </div>
                            <div class="col-md-6" id="companyNumberGroup">
                                <label for="company_number" class="form-label" id="companyNumberLabel">Company Number <span class="required" id="companyNumberRequired">*</span></label>
                                <div class="lookup-row">
                                    <input type="text" class="form-control" id="company_number" name="company_number" placeholder="e.g., 12345678" value="{{ $account->company_number ?? '' }}">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="lookupCompanyBtn" style="display: none;">
                                        <i class="fas fa-search me-1"></i>Lookup
                                    </button>
                                </div>
                                <div class="field-hint" id="companyNumberHint">Companies House registration number (8 digits)</div>
                                <div class="lookup-status" id="companyLookupStatus"></div>
                                <div class="validation-error" id="companyNumberError">Company number is required</div>
                            </div>
                            <div class="col-md-6">
                                <label for="sector" class="form-label">Business Sector <span class="required"></span></label>
                                @php $savedSector = $account->business_sector ?? ''; @endphp
                                <select class="form-select" id="sector" name="sector" required>
                                    <option value="">Select sector...</option>
                                    <option value="telecommunications" @if($savedSector === 'telecommunications') selected @endif>Telecommunications & Media</option>
                                    <option value="financial" @if($savedSector === 'financial') selected @endif>Financial Services</option>
                                    <option value="healthcare" @if($savedSector === 'healthcare') selected @endif>Healthcare</option>
                                    <option value="retail" @if($savedSector === 'retail') selected @endif>Retail & E-commerce</option>
                                    <option value="travel" @if($savedSector === 'travel') selected @endif>Travel & Hospitality</option>
                                    <option value="education" @if($savedSector === 'education') selected @endif>Education</option>
                                    <option value="government" @if($savedSector === 'government') selected @endif>Government & Public Sector</option>
                                    <option value="technology" @if($savedSector === 'technology') selected @endif>Technology</option>
                                    <option value="manufacturing" @if($savedSector === 'manufacturing') selected @endif>Manufacturing</option>
                                    <option value="professional" @if($savedSector === 'professional') selected @endif>Professional Services</option>
                                    <option value="utilities" @if($savedSector === 'utilities') selected @endif>Utilities & Energy</option>
                                    <option value="logistics" @if($savedSector === 'logistics') selected @endif>Logistics & Transport</option>
                                    <option value="other" @if($savedSector === 'other') selected @endif>Other</option>
                                </select>
                                <div class="validation-error">Please select a sector</div>
                            </div>
                            <div class="col-md-6">
                                <label for="website" class="form-label">Website <span class="required"></span></label>
                                <input type="url" class="form-control" id="website" name="website" value="{{ $account->website ?? 'https://' }}" placeholder="https://www.example.com" required>
                                <div class="field-hint">Must start with https://</div>
                                <div class="validation-error" id="websiteError">Please enter a valid website URL</div>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Address -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-map-marker-alt"></i> Registered Address
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="address_line1" class="form-label">Address Line 1 <span class="required"></span></label>
                                <input type="text" class="form-control" id="address_line1" name="address_line1" placeholder="Street address" value="{{ $account->address_line1 ?? '' }}" required>
                                <div class="validation-error">Address is required</div>
                            </div>
                            <div class="col-md-6">
                                <label for="address_line2" class="form-label">Address Line 2 <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="address_line2" name="address_line2" placeholder="Apartment, suite, etc." value="{{ $account->address_line2 ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">City <span class="required"></span></label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="City" value="{{ $account->city ?? '' }}" required>
                                <div class="validation-error">City is required</div>
                            </div>
                            <div class="col-md-4">
                                <label for="county" class="form-label">County / Region <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="county" name="county" placeholder="County" value="{{ $account->county ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label for="postcode" class="form-label">Postcode <span class="required"></span></label>
                                <input type="text" class="form-control" id="postcode" name="postcode" placeholder="Postcode" value="{{ $account->postcode ?? '' }}" required>
                                <div class="validation-error">Postcode is required</div>
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country <span class="required"></span></label>
                                @php $savedCountry = $account->country ?? 'GB'; @endphp
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select country...</option>
                                    <option value="GB" @if($savedCountry === 'GB') selected @endif>United Kingdom</option>
                                    <option value="IE" @if($savedCountry === 'IE') selected @endif>Ireland</option>
                                    <option value="DE" @if($savedCountry === 'DE') selected @endif>Germany</option>
                                    <option value="FR" @if($savedCountry === 'FR') selected @endif>France</option>
                                    <option value="NL" @if($savedCountry === 'NL') selected @endif>Netherlands</option>
                                    <option value="BE" @if($savedCountry === 'BE') selected @endif>Belgium</option>
                                    <option value="ES" @if($savedCountry === 'ES') selected @endif>Spain</option>
                                    <option value="IT" @if($savedCountry === 'IT') selected @endif>Italy</option>
                                    <option value="US" @if($savedCountry === 'US') selected @endif>United States</option>
                                    <option value="CA" @if($savedCountry === 'CA') selected @endif>Canada</option>
                                    <option value="AU" @if($savedCountry === 'AU') selected @endif>Australia</option>
                                </select>
                                <div class="validation-error">Country is required</div>
                            </div>
                        </div>
                    </div>

                    <!-- Support & Operations -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-headset"></i> Support & Operations
                        </div>
                        <p class="text-muted small mb-3">Configure email addresses for billing notifications, support communications, and incident alerts. Shared or group inboxes are accepted.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="billing_email" class="form-label">Accounts & Billing Email <span class="required"></span></label>
                                <input type="email" class="form-control" id="billing_email" name="billing_email" placeholder="e.g., accounts@company.com" value="{{ $account->billing_email ?? '' }}" required>
                                <div class="field-hint">Receives invoices, payment confirmations, and billing alerts</div>
                                <div class="validation-error">Please enter a valid email address</div>
                            </div>
                            <div class="col-md-6">
                                <label for="support_email" class="form-label">Support Email Address <span class="required"></span></label>
                                <input type="email" class="form-control" id="support_email" name="support_email" placeholder="e.g., support@company.com" value="{{ $account->support_contact_email ?? '' }}" required>
                                <div class="field-hint">Receives support ticket updates and general communications</div>
                                <div class="validation-error">Please enter a valid email address</div>
                            </div>
                            <div class="col-md-6">
                                <label for="incident_email" class="form-label">Incident Email Address <span class="required"></span></label>
                                <input type="email" class="form-control" id="incident_email" name="incident_email" placeholder="e.g., incidents@company.com" value="{{ $account->incident_email ?? '' }}" required>
                                <div class="field-hint">Receives urgent incident alerts and service disruption notices</div>
                                <div class="validation-error">Please enter a valid email address</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contract Signatory -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-signature"></i> Contract Signatory
                        </div>
                        <p class="text-muted small mb-3">The contract signatory is the individual authorised to enter contracts on behalf of your company. This person will receive legal notices and approval requests.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="signatory_name" class="form-label">Full Name <span class="required"></span></label>
                                <input type="text" class="form-control" id="signatory_name" name="signatory_name" placeholder="e.g., John Smith" value="{{ $account->signatory_name ?? '' }}" required>
                                <div class="validation-error">Full name is required</div>
                            </div>
                            <div class="col-md-6">
                                <label for="signatory_title" class="form-label">Job Title <span class="required"></span></label>
                                <input type="text" class="form-control" id="signatory_title" name="signatory_title" placeholder="e.g., CEO, Managing Director" value="{{ $account->signatory_title ?? '' }}" required>
                                <div class="validation-error">Job title is required</div>
                            </div>
                            <div class="col-md-6">
                                <label for="signatory_email" class="form-label">Email Address <span class="required"></span></label>
                                <input type="email" class="form-control" id="signatory_email" name="signatory_email" placeholder="e.g., signatory@company.com" value="{{ $account->signatory_email ?? '' }}" required>
                                <div class="field-hint">Used for contract signing and legal communications</div>
                                <div class="validation-error">Please enter a valid email address</div>
                            </div>
                        </div>
                    </div>

                    <!-- VAT & Tax Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-receipt"></i> VAT & Tax Information
                        </div>
                        <p class="text-muted small mb-3">VAT settings are used for billing and invoice generation. Changes to VAT details are audit-logged.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="vat_registered" class="form-label">VAT Registered <span class="required"></span></label>
                                @php $savedVatReg = ($account->vat_registered ?? false) ? 'yes' : (($account->vat_registered === false) ? 'no' : ''); @endphp
                                <select class="form-select" id="vat_registered" name="vat_registered" required>
                                    <option value="">Select...</option>
                                    <option value="yes" @if($savedVatReg === 'yes') selected @endif>Yes - VAT registered</option>
                                    <option value="no" @if($savedVatReg === 'no') selected @endif>No - Not VAT registered</option>
                                </select>
                                <div class="validation-error">Please select VAT registration status</div>
                            </div>
                            <div class="col-md-6" id="vatCountryGroup" style="display: none;">
                                <label for="vat_country" class="form-label">VAT Country <span class="required"></span></label>
                                @php $savedTaxCountry = $account->tax_country ?? 'GB'; @endphp
                                <select class="form-select" id="vat_country" name="vat_country">
                                    <option value="GB" @if($savedTaxCountry === 'GB') selected @endif>United Kingdom (GB)</option>
                                    <option value="IE" @if($savedTaxCountry === 'IE') selected @endif>Ireland (IE)</option>
                                    <option value="DE" @if($savedTaxCountry === 'DE') selected @endif>Germany (DE)</option>
                                    <option value="FR" @if($savedTaxCountry === 'FR') selected @endif>France (FR)</option>
                                    <option value="NL" @if($savedTaxCountry === 'NL') selected @endif>Netherlands (NL)</option>
                                    <option value="BE" @if($savedTaxCountry === 'BE') selected @endif>Belgium (BE)</option>
                                    <option value="ES" @if($savedTaxCountry === 'ES') selected @endif>Spain (ES)</option>
                                    <option value="IT" @if($savedTaxCountry === 'IT') selected @endif>Italy (IT)</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="vatNumberGroup" style="display: none;">
                                <label for="vat_number" class="form-label">VAT Number <span class="required"></span></label>
                                <div class="lookup-row">
                                    <input type="text" class="form-control" id="vat_number" name="vat_number" placeholder="e.g., GB123456789" value="{{ $account->vat_number ?? '' }}">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="lookupVatBtn">
                                        <i class="fas fa-search me-1"></i>Verify
                                    </button>
                                </div>
                                <div class="field-hint">Include country prefix (e.g., GB, IE, DE)</div>
                                <div class="lookup-status" id="vatLookupStatus"></div>
                                <div class="validation-error" id="vatNumberError">VAT number is required</div>
                            </div>
                            <div class="col-md-6" id="reverseChargesGroup" style="display: none;">
                                <label for="reverse_charges" class="form-label">
                                    Reverse Charges <span class="required"></span>
                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<strong>No</strong> = You are sending messages to your own customers<br><br><strong>Yes</strong> = You are providing messaging as a service to third parties (reverse charge applies)"></i>
                                </label>
                                @php $savedReverseCharges = ($account->vat_reverse_charges ?? false) ? 'yes' : (($account->vat_reverse_charges === false) ? 'no' : ''); @endphp
                                <select class="form-select" id="reverse_charges" name="reverse_charges">
                                    <option value="">Select...</option>
                                    <option value="no" @if($savedReverseCharges === 'no') selected @endif>No</option>
                                    <option value="yes" @if($savedReverseCharges === 'yes') selected @endif>Yes</option>
                                </select>
                                <div class="validation-error">Please select reverse charges status</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-details" disabled>
                    <i class="fas fa-save me-1"></i> Save & Continue
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/quicksms-account-lifecycle.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var lifecycle = window.AccountLifecycle;
    
    // Form elements
    var form = document.getElementById('accountDetailsForm');
    var saveBtn = document.getElementById('btn-save-details');
    var progressBar = document.getElementById('form-progress');
    var progressText = document.getElementById('form-progress-text');
    
    // State
    var selectedCompanyType = document.getElementById('company_type').value || '';
    
    // Required field groups (matches Account > Details sections)
    var requiredGroups = {
        companyType: { complete: false, reqId: 'req-company-type' },
        company: { complete: false, reqId: 'req-company' },
        address: { complete: false, reqId: 'req-address' },
        support: { complete: false, reqId: 'req-support' },
        signatory: { complete: false, reqId: 'req-signatory' },
        vat: { complete: false, reqId: 'req-vat' }
    };
    
    // =====================================================
    // COMPANY TYPE SELECTION
    // =====================================================
    document.querySelectorAll('.company-type-tile').forEach(function(tile) {
        tile.addEventListener('click', function() {
            document.querySelectorAll('.company-type-tile').forEach(function(t) {
                t.classList.remove('selected');
            });
            this.classList.add('selected');
            selectedCompanyType = this.getAttribute('data-type');
            document.getElementById('company_type').value = selectedCompanyType;
            document.getElementById('companyTypeError').style.display = 'none';
            
            updateCompanyTypeFields();
            validateForm();
        });
    });
    
    function updateCompanyTypeFields() {
        var companyNumberGroup = document.getElementById('companyNumberGroup');
        var companyNumberRequired = document.getElementById('companyNumberRequired');
        var companyNumberInput = document.getElementById('company_number');
        var lookupBtn = document.getElementById('lookupCompanyBtn');
        var hint = document.getElementById('companyNumberHint');
        
        switch (selectedCompanyType) {
            case 'uk_limited':
                companyNumberGroup.style.display = 'block';
                companyNumberRequired.style.display = 'inline';
                companyNumberInput.setAttribute('required', 'required');
                lookupBtn.style.display = 'inline-block';
                hint.textContent = 'Companies House registration number (8 digits)';
                break;
            case 'sole_trader':
                companyNumberGroup.style.display = 'none';
                companyNumberRequired.style.display = 'none';
                companyNumberInput.removeAttribute('required');
                companyNumberInput.value = '';
                lookupBtn.style.display = 'none';
                break;
            case 'government':
                companyNumberGroup.style.display = 'block';
                companyNumberRequired.style.display = 'none';
                companyNumberInput.removeAttribute('required');
                lookupBtn.style.display = 'none';
                hint.textContent = 'Organisation reference number (optional)';
                break;
        }
    }
    
    // =====================================================
    // COMPANIES HOUSE LOOKUP
    // =====================================================
    document.getElementById('lookupCompanyBtn').addEventListener('click', function() {
        var companyNumber = document.getElementById('company_number').value.trim();
        var status = document.getElementById('companyLookupStatus');
        
        if (!companyNumber || companyNumber.length < 8) {
            status.className = 'lookup-status error';
            status.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Please enter a valid 8-digit company number';
            return;
        }
        
        status.className = 'lookup-status loading';
        status.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Searching Companies House...';
        
        // TODO: Backend - GET /api/companies-house/lookup/{companyNumber}
        // Simulated lookup for UI demonstration
        setTimeout(function() {
            // Simulate API response
            var mockData = {
                success: true,
                company_name: 'Example Ltd',
                registered_address: {
                    line1: '123 Business Street',
                    city: 'London',
                    postcode: 'EC1A 1BB',
                    country: 'United Kingdom'
                }
            };
            
            if (mockData.success) {
                status.className = 'lookup-status success';
                status.innerHTML = '<i class="fas fa-check-circle me-1"></i>Company found - details populated';
                
                // Auto-fill fields
                document.getElementById('company_name').value = mockData.company_name;
                document.getElementById('address_line1').value = mockData.registered_address.line1;
                document.getElementById('city').value = mockData.registered_address.city;
                document.getElementById('postcode').value = mockData.registered_address.postcode;
                
                validateForm();
            } else {
                status.className = 'lookup-status error';
                status.innerHTML = '<i class="fas fa-times-circle me-1"></i>Company not found';
            }
        }, 1500);
    });
    
    // =====================================================
    // VAT TOGGLE & LOOKUP
    // =====================================================
    document.getElementById('vat_registered').addEventListener('change', function() {
        var isVatRegistered = this.value === 'yes';
        document.getElementById('vatCountryGroup').style.display = isVatRegistered ? 'block' : 'none';
        document.getElementById('vatNumberGroup').style.display = isVatRegistered ? 'block' : 'none';
        document.getElementById('reverseChargesGroup').style.display = isVatRegistered ? 'block' : 'none';
        
        var vatInput = document.getElementById('vat_number');
        var reverseChargesInput = document.getElementById('reverse_charges');
        if (isVatRegistered) {
            vatInput.setAttribute('required', 'required');
            reverseChargesInput.setAttribute('required', 'required');
        } else {
            vatInput.removeAttribute('required');
            vatInput.value = '';
            reverseChargesInput.removeAttribute('required');
            reverseChargesInput.value = '';
        }
        validateForm();
    });
    
    document.getElementById('lookupVatBtn').addEventListener('click', function() {
        var vatNumber = document.getElementById('vat_number').value.trim();
        var status = document.getElementById('vatLookupStatus');
        
        if (!vatNumber || vatNumber.length < 9) {
            status.className = 'lookup-status error';
            status.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Please enter a valid VAT number';
            return;
        }
        
        status.className = 'lookup-status loading';
        status.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Verifying VAT number...';
        
        // TODO: Backend - GET /api/vat/validate/{vatNumber}
        // Simulated lookup for UI demonstration
        setTimeout(function() {
            var isValid = vatNumber.length >= 9;
            
            if (isValid) {
                status.className = 'lookup-status success';
                status.innerHTML = '<i class="fas fa-check-circle me-1"></i>VAT number verified';
            } else {
                status.className = 'lookup-status error';
                status.innerHTML = '<i class="fas fa-times-circle me-1"></i>Invalid VAT number';
            }
        }, 1200);
    });
    
    // =====================================================
    // FORM VALIDATION
    // =====================================================
    function validateForm() {
        // Company Type
        requiredGroups.companyType.complete = selectedCompanyType !== '';
        
        // Company Information (name, number if UK Limited, sector, website)
        var companyName = document.getElementById('company_name').value.trim();
        var sectorVal = document.getElementById('sector').value;
        var websiteVal = document.getElementById('website').value.trim();
        var websiteValid = false;
        try {
            if (websiteVal) new URL(websiteVal);
            websiteValid = websiteVal !== '' && websiteVal.startsWith('https://');
        } catch (e) {
            websiteValid = false;
        }
        
        requiredGroups.company.complete = companyName !== '' && sectorVal !== '' && websiteValid;
        
        // Company Number (conditional for UK Limited)
        if (selectedCompanyType === 'uk_limited') {
            var companyNumber = document.getElementById('company_number').value.trim();
            requiredGroups.company.complete = requiredGroups.company.complete && companyNumber.length >= 8;
        }
        
        // Registered Address
        var addr1 = document.getElementById('address_line1').value.trim();
        var cityVal = document.getElementById('city').value.trim();
        var postcodeVal = document.getElementById('postcode').value.trim();
        var countryVal = document.getElementById('country').value;
        requiredGroups.address.complete = addr1 !== '' && cityVal !== '' && postcodeVal !== '' && countryVal !== '';
        
        // Support & Operations (3 email fields)
        var billingEmail = document.getElementById('billing_email').value.trim();
        var supportEmail = document.getElementById('support_email').value.trim();
        var incidentEmail = document.getElementById('incident_email').value.trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        requiredGroups.support.complete = emailRegex.test(billingEmail) && 
                                          emailRegex.test(supportEmail) && 
                                          emailRegex.test(incidentEmail);
        
        // Contract Signatory
        var signatoryName = document.getElementById('signatory_name').value.trim();
        var signatoryTitle = document.getElementById('signatory_title').value.trim();
        var signatoryEmail = document.getElementById('signatory_email').value.trim();
        requiredGroups.signatory.complete = signatoryName !== '' && 
                                            signatoryTitle !== '' && 
                                            emailRegex.test(signatoryEmail);
        
        // VAT & Tax Information
        var vatRegistered = document.getElementById('vat_registered').value;
        if (vatRegistered === 'no') {
            requiredGroups.vat.complete = true;
        } else if (vatRegistered === 'yes') {
            var vatNum = document.getElementById('vat_number').value.trim();
            var reverseCharges = document.getElementById('reverse_charges').value;
            requiredGroups.vat.complete = vatNum.length >= 9 && reverseCharges !== '';
        } else {
            requiredGroups.vat.complete = false;
        }
        
        // Update requirement list icons
        var completedCount = 0;
        var totalCount = Object.keys(requiredGroups).length;
        
        Object.keys(requiredGroups).forEach(function(key) {
            var group = requiredGroups[key];
            var reqEl = document.getElementById(group.reqId);
            if (reqEl) {
                reqEl.classList.remove('fa-circle', 'fa-check-circle', 'pending', 'complete');
                if (group.complete) {
                    reqEl.classList.add('fa-check-circle', 'complete');
                    completedCount++;
                } else {
                    reqEl.classList.add('fa-circle', 'pending');
                }
            }
        });
        
        // Update progress bar
        var percent = Math.round((completedCount / totalCount) * 100);
        progressBar.style.width = percent + '%';
        progressText.textContent = completedCount + ' of ' + totalCount;
        
        // Enable/disable save button
        saveBtn.disabled = completedCount < totalCount;
        
        return completedCount === totalCount;
    }
    
    // Add validation listeners
    form.querySelectorAll('input, select').forEach(function(input) {
        input.addEventListener('input', validateForm);
        input.addEventListener('change', validateForm);
    });
    
    // =====================================================
    // CLEAR STALE DATA
    // =====================================================
    localStorage.removeItem('account_details');

    function loadSavedData() {
        console.log('[Activate] Form starts empty - fill in details to complete activation');
    }
    
    // =====================================================
    // SAVE DETAILS
    // =====================================================
    saveBtn.addEventListener('click', function() {
        if (!validateForm()) return;
        
        var formData = new FormData(form);
        var data = {};
        formData.forEach(function(value, key) {
            data[key] = value;
        });
        data.company_type = selectedCompanyType;
        
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        
        fetch('{{ route("account.activate.save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.status === 'success') {
                if (lifecycle) {
                    lifecycle.setActivationStatus('account_details_complete', true);
                    lifecycle.logAccountDetailsUpdate(Object.keys(data));
                }
                
                var modal = bootstrap.Modal.getInstance(document.getElementById('completeDetailsModal'));
                modal.hide();
                
                detailsComplete = true;
                updateUI(true);
                showToast('Account details saved successfully!', 'success');
            } else {
                var msg = result.message || 'Failed to save details';
                if (result.errors) {
                    var errorList = Object.values(result.errors).flat();
                    msg = errorList.join(', ');
                }
                showToast(msg, 'error');
            }
        })
        .catch(function(err) {
            console.error('[Activate] Save error:', err);
            showToast('An error occurred while saving. Please try again.', 'error');
        })
        .finally(function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save & Continue';
        });
    });
    
    function showToast(message, type) {
        var toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '1100';
        toast.innerHTML = '<div class="toast show align-items-center text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0" role="alert">' +
            '<div class="d-flex">' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div></div>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }
    
    // Track if details are complete (local state)
    var detailsComplete = false;
    
    // =====================================================
    // UPDATE UI
    // =====================================================
    function updateUI(forceComplete) {
        // Check activation status from lifecycle OR local state OR force flag
        var activationStatus = lifecycle ? lifecycle.getActivationStatus() : {};
        var isDetailsComplete = forceComplete || detailsComplete || activationStatus.account_details_complete;
        var isLive = lifecycle ? lifecycle.isLive() : false;
        
        var step1 = document.getElementById('step-details');
        var step2 = document.getElementById('step-payment');
        var step3 = document.getElementById('step-activated');
        
        if (isDetailsComplete) {
            detailsComplete = true; // Cache locally
            step1.classList.add('completed');
            step1.classList.remove('current');
            document.getElementById('step1-status').className = 'badge bg-success';
            document.getElementById('step1-status').textContent = 'Complete';
            
            document.querySelectorAll('#details-requirements i').forEach(function(icon) {
                icon.classList.remove('pending', 'fa-circle');
                icon.classList.add('complete', 'fa-check-circle');
            });
            
            document.getElementById('btn-complete-details').innerHTML = '<i class="fas fa-check me-1"></i> Edit Details';
            document.getElementById('btn-purchase').disabled = false;
            step2.classList.add('current');
            document.getElementById('step2-status').className = 'badge bg-warning text-dark';
            document.getElementById('step2-status').textContent = 'Ready';
        } else {
            step1.classList.add('current');
        }
        
        if (activationStatus.first_payment_made || isLive) {
            step2.classList.add('completed');
            step2.classList.remove('current');
            document.getElementById('step2-status').className = 'badge bg-success';
            document.getElementById('step2-status').textContent = 'Complete';
        }
        
        if (isLive) {
            step3.classList.add('completed');
            document.getElementById('step3-status').className = 'badge bg-success';
            document.getElementById('step3-status').textContent = 'Active';
            document.getElementById('activated-actions').style.display = 'block';
        }
    }
    
    document.getElementById('btn-purchase').addEventListener('click', function() {
        window.location.href = '{{ route("purchase.messages") }}';
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
    
    // Auto-select company type tile from saved data
    if (selectedCompanyType) {
        var mappedType = selectedCompanyType;
        var tile = document.querySelector('.company-type-tile[data-type="' + mappedType + '"]');
        if (tile) {
            tile.classList.add('selected');
            updateCompanyTypeFields();
        }
    }

    // Auto-show VAT fields if VAT is registered
    var vatSelect = document.getElementById('vat_registered');
    if (vatSelect.value === 'yes') {
        document.getElementById('vatCountryGroup').style.display = 'block';
        document.getElementById('vatNumberGroup').style.display = 'block';
        document.getElementById('reverseChargesGroup').style.display = 'block';
        document.getElementById('vat_number').setAttribute('required', 'required');
        document.getElementById('reverse_charges').setAttribute('required', 'required');
    }

    // Check activation status from server-side data only
    function checkActivationStatus() {
        var accountStatus = @json($account->status ?? 'pending_activation');
        var hasCompanyType = @json(!empty($account->company_type ?? ''));
        var hasRegisteredAddress = @json(!empty($account->address_line1 ?? ''));
        var hasSignatory = @json(!empty($account->signatory_name ?? ''));

        if (accountStatus === 'live') {
            detailsComplete = true;
            return true;
        }

        if (hasCompanyType && hasRegisteredAddress && hasSignatory) {
            detailsComplete = true;
            return true;
        }

        return false;
    }

    // Initialize - load any saved draft values into form but don't mark complete
    var hasSavedData = checkActivationStatus();
    loadSavedData();
    updateUI(hasSavedData);
    
    document.addEventListener('lifecycle:state_changed', function() { updateUI(); });
});
</script>
@endpush

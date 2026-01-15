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
.info-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    margin-bottom: 1.5rem;
}
.info-card .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem 0.75rem 0 0;
}
.info-card .card-header h6 {
    margin: 0;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.info-card .card-header h6 i {
    color: var(--primary);
}
.info-card .card-body {
    padding: 1.25rem;
}
.info-row {
    display: flex;
    padding: 0.625rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.info-row:last-child {
    border-bottom: none;
}
.info-label {
    flex: 0 0 180px;
    font-weight: 500;
    color: #6c757d;
    font-size: 0.875rem;
}
.info-value {
    flex: 1;
    color: #333;
    font-size: 0.875rem;
}
.info-value.text-muted {
    font-style: italic;
}
.edit-field {
    display: none !important;
}
.edit-mode .view-field {
    display: none !important;
}
.edit-mode .edit-field {
    display: block !important;
}
.edit-mode .edit-field.d-flex {
    display: flex !important;
}
.btn-edit-section {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
}
.source-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: rgba(136, 108, 192, 0.1);
    color: var(--primary);
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 500;
}
.source-badge i {
    font-size: 0.6rem;
}
.usage-indicator {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.usage-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #f8f9fa;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
}
.usage-chip i {
    font-size: 0.65rem;
}
.usage-chip.active {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}
.audit-trail-container {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    max-height: 300px;
    overflow-y: auto;
}
.audit-entry {
    padding: 0.75rem;
    background: #fff;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
    border: 1px solid #e9ecef;
}
.audit-entry:last-child {
    margin-bottom: 0;
}
.audit-entry .audit-time {
    font-size: 0.75rem;
    color: #6c757d;
}
.audit-entry .audit-user {
    font-weight: 500;
    color: #333;
    font-size: 0.8rem;
}
.audit-entry .audit-action {
    font-size: 0.8rem;
    color: #495057;
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
.address-block {
    line-height: 1.6;
}
.contact-card {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    height: 100%;
}
.contact-card .contact-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}
.contact-card .contact-role {
    color: var(--primary);
    font-size: 0.8rem;
    margin-bottom: 0.75rem;
}
.contact-card .contact-detail {
    font-size: 0.85rem;
    color: #495057;
    margin-bottom: 0.25rem;
}
.contact-card .contact-detail i {
    width: 18px;
    color: #6c757d;
}
.verification-status {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.verification-status.verified {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}
.verification-status.pending {
    background: rgba(255, 193, 7, 0.15);
    color: #856404;
}
.verification-status.unverified {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
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
            <p>This is the authoritative source for your company information. Data entered here is automatically shared with RCS Agent Registration, SMS SenderID Registration, Billing, VAT handling, Support tickets, and Compliance records. Keeping this information accurate ensures consistency across all services.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="info-card" id="companyInfoCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-building"></i> Company Information</h6>
                <button class="btn btn-outline-primary btn-sm btn-edit-section" data-section="company">
                    <i class="fas fa-pencil-alt me-1"></i>Edit
                </button>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Legal Company Name</div>
                    <div class="info-value">
                        <span class="view-field">Acme Communications Ltd</span>
                        <input type="text" class="form-control form-control-sm edit-field" id="companyName" value="Acme Communications Ltd">
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Trading Name</div>
                    <div class="info-value">
                        <span class="view-field">Acme Comms</span>
                        <input type="text" class="form-control form-control-sm edit-field" id="tradingName" value="Acme Comms">
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Company Registration No.</div>
                    <div class="info-value">
                        <span class="view-field">12345678</span>
                        <input type="text" class="form-control form-control-sm edit-field" id="companyRegNo" value="12345678">
                        <div class="usage-indicator view-field">
                            <span class="usage-chip active"><i class="fas fa-check-circle"></i> RCS Registration</span>
                            <span class="usage-chip active"><i class="fas fa-check-circle"></i> SMS SenderID</span>
                            <span class="usage-chip active"><i class="fas fa-check-circle"></i> Billing</span>
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">VAT Number</div>
                    <div class="info-value">
                        <span class="view-field">GB123456789</span>
                        <input type="text" class="form-control form-control-sm edit-field" id="vatNumber" value="GB123456789">
                        <div class="usage-indicator view-field">
                            <span class="usage-chip active"><i class="fas fa-check-circle"></i> Invoicing</span>
                            <span class="usage-chip active"><i class="fas fa-check-circle"></i> VAT Returns</span>
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Industry / Sector</div>
                    <div class="info-value">
                        <span class="view-field">Telecommunications & Media</span>
                        <select class="form-select form-select-sm edit-field" id="industrySector">
                            <option value="">Select industry...</option>
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
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Company Website</div>
                    <div class="info-value">
                        <span class="view-field"><a href="https://www.acmecomms.co.uk" target="_blank">www.acmecomms.co.uk</a></span>
                        <input type="url" class="form-control form-control-sm edit-field" id="companyWebsite" value="https://www.acmecomms.co.uk">
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-3 edit-field">
                    <button class="btn btn-primary btn-sm btn-save-section" data-section="company">
                        <i class="fas fa-check me-1"></i>Save Changes
                    </button>
                    <button class="btn btn-outline-secondary btn-sm btn-cancel-section" data-section="company">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
        
        <div class="info-card" id="registeredAddressCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-map-marker-alt"></i> Registered Address</h6>
                <button class="btn btn-outline-primary btn-sm btn-edit-section" data-section="registered">
                    <i class="fas fa-pencil-alt me-1"></i>Edit
                </button>
            </div>
            <div class="card-body">
                <div class="view-field">
                    <div class="address-block">
                        <strong>Acme Communications Ltd</strong><br>
                        123 Business Park<br>
                        Tech Quarter, Floor 5<br>
                        London<br>
                        Greater London<br>
                        EC1A 1BB<br>
                        United Kingdom
                    </div>
                    <div class="usage-indicator mt-2">
                        <span class="usage-chip active"><i class="fas fa-check-circle"></i> Legal Documents</span>
                        <span class="usage-chip active"><i class="fas fa-check-circle"></i> Compliance</span>
                    </div>
                </div>
                <div class="edit-field">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="regAddress1" value="123 Business Park">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Address Line 2</label>
                            <input type="text" class="form-control form-control-sm" id="regAddress2" value="Tech Quarter, Floor 5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="regCity" value="London">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">County / State</label>
                            <input type="text" class="form-control form-control-sm" id="regCounty" value="Greater London">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Postcode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="regPostcode" value="EC1A 1BB">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Country <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="regCountry">
                                <option value="UK" selected>United Kingdom</option>
                                <option value="US">United States</option>
                                <option value="DE">Germany</option>
                                <option value="FR">France</option>
                                <option value="IE">Ireland</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary btn-sm btn-save-section" data-section="registered">
                            <i class="fas fa-check me-1"></i>Save Changes
                        </button>
                        <button class="btn btn-outline-secondary btn-sm btn-cancel-section" data-section="registered">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="info-card" id="billingAddressCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-file-invoice-dollar"></i> Billing Address</h6>
                <button class="btn btn-outline-primary btn-sm btn-edit-section" data-section="billing">
                    <i class="fas fa-pencil-alt me-1"></i>Edit
                </button>
            </div>
            <div class="card-body">
                <div class="view-field">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="source-badge"><i class="fas fa-link"></i> Same as Registered Address</span>
                    </div>
                    <div class="address-block text-muted">
                        123 Business Park<br>
                        Tech Quarter, Floor 5<br>
                        London, EC1A 1BB<br>
                        United Kingdom
                    </div>
                    <div class="usage-indicator mt-2">
                        <span class="usage-chip active"><i class="fas fa-check-circle"></i> Invoices</span>
                        <span class="usage-chip active"><i class="fas fa-check-circle"></i> Statements</span>
                    </div>
                </div>
                <div class="edit-field">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sameAsRegistered" checked>
                        <label class="form-check-label small" for="sameAsRegistered">
                            Same as registered address
                        </label>
                    </div>
                    <div id="billingAddressFields" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small">Address Line 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="billAddress1" value="">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Address Line 2</label>
                                <input type="text" class="form-control form-control-sm" id="billAddress2" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="billCity" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">County / State</label>
                                <input type="text" class="form-control form-control-sm" id="billCounty" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Postcode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="billPostcode" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Country <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="billCountry">
                                    <option value="UK" selected>United Kingdom</option>
                                    <option value="US">United States</option>
                                    <option value="DE">Germany</option>
                                    <option value="FR">France</option>
                                    <option value="IE">Ireland</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary btn-sm btn-save-section" data-section="billing">
                            <i class="fas fa-check me-1"></i>Save Changes
                        </button>
                        <button class="btn btn-outline-secondary btn-sm btn-cancel-section" data-section="billing">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="info-card" id="contactsCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-users"></i> Key Contacts</h6>
                <button class="btn btn-outline-primary btn-sm btn-edit-section" data-section="contacts">
                    <i class="fas fa-pencil-alt me-1"></i>Edit
                </button>
            </div>
            <div class="card-body">
                <div class="view-field">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="contact-card">
                                <div class="contact-name">Sarah Johnson</div>
                                <div class="contact-role">Primary Contact</div>
                                <div class="contact-detail"><i class="fas fa-envelope"></i> sarah.johnson@acmecomms.co.uk</div>
                                <div class="contact-detail"><i class="fas fa-phone"></i> +44 20 7946 0958</div>
                                <div class="contact-detail"><i class="fas fa-briefcase"></i> Account Director</div>
                                <div class="mt-2">
                                    <span class="usage-chip active"><i class="fas fa-check-circle"></i> Support</span>
                                    <span class="usage-chip active"><i class="fas fa-check-circle"></i> Escalations</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="contact-card">
                                <div class="contact-name">Michael Chen</div>
                                <div class="contact-role">Billing Contact</div>
                                <div class="contact-detail"><i class="fas fa-envelope"></i> m.chen@acmecomms.co.uk</div>
                                <div class="contact-detail"><i class="fas fa-phone"></i> +44 20 7946 0959</div>
                                <div class="contact-detail"><i class="fas fa-briefcase"></i> Finance Manager</div>
                                <div class="mt-2">
                                    <span class="usage-chip active"><i class="fas fa-check-circle"></i> Invoices</span>
                                    <span class="usage-chip active"><i class="fas fa-check-circle"></i> Payments</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="contact-card">
                                <div class="contact-name">David Park</div>
                                <div class="contact-role">Technical Contact</div>
                                <div class="contact-detail"><i class="fas fa-envelope"></i> d.park@acmecomms.co.uk</div>
                                <div class="contact-detail"><i class="fas fa-phone"></i> +44 20 7946 0960</div>
                                <div class="contact-detail"><i class="fas fa-briefcase"></i> CTO</div>
                                <div class="mt-2">
                                    <span class="usage-chip active"><i class="fas fa-check-circle"></i> API Issues</span>
                                    <span class="usage-chip active"><i class="fas fa-check-circle"></i> Technical</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="edit-field">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <h6 class="small fw-bold text-primary mb-3">Primary Contact</h6>
                            <div class="mb-2">
                                <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="primaryName" value="Sarah Johnson">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm" id="primaryEmail" value="sarah.johnson@acmecomms.co.uk">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control form-control-sm" id="primaryPhone" value="+44 20 7946 0958">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Job Title</label>
                                <input type="text" class="form-control form-control-sm" id="primaryTitle" value="Account Director">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="small fw-bold text-primary mb-3">Billing Contact</h6>
                            <div class="mb-2">
                                <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="billingName" value="Michael Chen">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm" id="billingEmail" value="m.chen@acmecomms.co.uk">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control form-control-sm" id="billingPhone" value="+44 20 7946 0959">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Job Title</label>
                                <input type="text" class="form-control form-control-sm" id="billingTitle" value="Finance Manager">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="small fw-bold text-primary mb-3">Technical Contact</h6>
                            <div class="mb-2">
                                <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="technicalName" value="David Park">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm" id="technicalEmail" value="d.park@acmecomms.co.uk">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control form-control-sm" id="technicalPhone" value="+44 20 7946 0960">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Job Title</label>
                                <input type="text" class="form-control form-control-sm" id="technicalTitle" value="CTO">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary btn-sm btn-save-section" data-section="contacts">
                            <i class="fas fa-check me-1"></i>Save Changes
                        </button>
                        <button class="btn btn-outline-secondary btn-sm btn-cancel-section" data-section="contacts">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="info-card">
            <div class="card-header">
                <h6><i class="fas fa-shield-alt"></i> Account Status</h6>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Account ID</div>
                    <div class="info-value"><code>ACC-2024-00847</code></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="badge badge-pastel-success">Active</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Created</div>
                    <div class="info-value">15 Mar 2023</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Account Type</div>
                    <div class="info-value">Enterprise</div>
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="card-header">
                <h6><i class="fas fa-check-double"></i> Verification Status</h6>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Company</div>
                    <div class="info-value">
                        <span class="verification-status verified"><i class="fas fa-check-circle"></i> Verified</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">VAT Number</div>
                    <div class="info-value">
                        <span class="verification-status verified"><i class="fas fa-check-circle"></i> Verified</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Primary Email</div>
                    <div class="info-value">
                        <span class="verification-status verified"><i class="fas fa-check-circle"></i> Verified</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Phone</div>
                    <div class="info-value">
                        <span class="verification-status pending"><i class="fas fa-clock"></i> Pending</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="card-header">
                <h6><i class="fas fa-link"></i> Data Usage</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">This account data is used by the following modules:</p>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('management.rcs-agent') }}" class="d-flex align-items-center text-decoration-none">
                        <span class="usage-chip active me-2"><i class="fas fa-robot"></i></span>
                        <span class="small text-dark">RCS Agent Registration</span>
                    </a>
                    <a href="{{ route('management.sms-sender-id') }}" class="d-flex align-items-center text-decoration-none">
                        <span class="usage-chip active me-2"><i class="fas fa-id-badge"></i></span>
                        <span class="small text-dark">SMS SenderID Registration</span>
                    </a>
                    <a href="{{ route('purchase.messages') }}" class="d-flex align-items-center text-decoration-none">
                        <span class="usage-chip active me-2"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span class="small text-dark">Billing & Invoicing</span>
                    </a>
                    <a href="{{ route('support.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                        <span class="usage-chip active me-2"><i class="fas fa-headset"></i></span>
                        <span class="small text-dark">Support Tickets</span>
                    </a>
                    <a href="{{ route('reporting.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                        <span class="usage-chip active me-2"><i class="fas fa-chart-bar"></i></span>
                        <span class="small text-dark">Reporting & Compliance</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-history"></i> Recent Changes</h6>
                <a href="{{ route('account.audit-logs') }}" class="small text-decoration-none">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="audit-trail-container" style="border-radius: 0 0 0.75rem 0.75rem;">
                    <div class="audit-entry">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="audit-user">Sarah Johnson</span>
                            <span class="audit-time">2 hours ago</span>
                        </div>
                        <div class="audit-action">Updated primary contact phone number</div>
                    </div>
                    <div class="audit-entry">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="audit-user">System</span>
                            <span class="audit-time">Yesterday</span>
                        </div>
                        <div class="audit-action">VAT number verified by HMRC</div>
                    </div>
                    <div class="audit-entry">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="audit-user">Michael Chen</span>
                            <span class="audit-time">3 days ago</span>
                        </div>
                        <div class="audit-action">Updated billing contact email</div>
                    </div>
                    <div class="audit-entry">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="audit-user">Admin</span>
                            <span class="audit-time">1 week ago</span>
                        </div>
                        <div class="audit-action">Account upgraded to Enterprise tier</div>
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
    
    $('.btn-edit-section').on('click', function() {
        var section = $(this).data('section');
        var $card = $(this).closest('.info-card');
        $card.addClass('edit-mode');
        $(this).hide();
    });
    
    $('.btn-cancel-section').on('click', function() {
        var section = $(this).data('section');
        var $card = $(this).closest('.info-card');
        $card.removeClass('edit-mode');
        $card.find('.btn-edit-section').show();
    });
    
    $('.btn-save-section').on('click', function() {
        var section = $(this).data('section');
        var $card = $(this).closest('.info-card');
        var $btn = $(this);
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        
        setTimeout(function() {
            $card.removeClass('edit-mode');
            $card.find('.btn-edit-section').show();
            $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Save Changes');
            
            toastr.success('Changes saved successfully. All linked modules have been updated.');
        }, 800);
    });
    
    $('#sameAsRegistered').on('change', function() {
        if ($(this).is(':checked')) {
            $('#billingAddressFields').slideUp();
        } else {
            $('#billingAddressFields').slideDown();
        }
    });
    
});
</script>
@endpush

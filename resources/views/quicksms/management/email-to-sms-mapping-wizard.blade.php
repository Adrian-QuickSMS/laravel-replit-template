@extends('layouts.quicksms')

@section('title', 'Create Email-to-SMS – Contact List')

@push('styles')
<link href="{{ asset('vendor/jquery-smartwizard/dist/css/smart_wizard.min.css') }}" rel="stylesheet">
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
    border: 0.125rem solid var(--primary, #886CC0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: var(--primary, #886CC0);
    position: relative;
    z-index: 10;
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
.form-wizard .tab-content .tab-pane {
    padding: 0;
    overflow: visible !important;
}
.contact-list-option {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 0.75rem;
}
.contact-list-option:hover {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.05);
}
.contact-list-option.selected {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.1);
}
.contact-list-option .list-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.contact-list-option .list-count {
    font-size: 0.85rem;
    color: #6c757d;
}
.contact-list-option .list-check {
    color: #886CC0;
    font-size: 1.25rem;
    opacity: 0;
}
.contact-list-option.selected .list-check {
    opacity: 1;
}
.generated-email {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.5rem;
    text-align: center;
}
.generated-email code {
    font-size: 1.25rem;
    color: var(--primary, #886CC0);
    background: #fff;
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    display: inline-block;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
}
.summary-card {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}
.summary-row:last-child {
    border-bottom: none;
}
.summary-label {
    color: #6c757d;
    font-weight: 500;
}
.summary-value {
    font-weight: 600;
    text-align: right;
}
.rules-box {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-top: 1.5rem;
}
.rules-box h6 {
    color: var(--primary, #886CC0);
    margin-bottom: 1rem;
}
.rules-box ul {
    margin-bottom: 0;
    padding-left: 1.25rem;
}
.rules-box li {
    margin-bottom: 0.5rem;
    color: #495057;
}
.rules-box li:last-child {
    margin-bottom: 0;
}
.email-address-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 1rem;
    padding: 0.35rem 0.75rem;
    margin: 0.25rem;
    font-size: 0.875rem;
    font-family: monospace;
}
.email-address-badge .remove-btn {
    background: none;
    border: none;
    color: #dc3545;
    padding: 0 0 0 0.5rem;
    cursor: pointer;
    font-size: 0.75rem;
}
.email-address-badge .remove-btn:hover {
    color: #a71d2a;
}
.email-addresses-container {
    min-height: 40px;
}
.alert-pastel-warning {
    background-color: rgba(255, 193, 7, 0.15);
    border-color: rgba(255, 193, 7, 0.3);
    color: #856404;
}
.autosave-indicator {
    font-size: 0.85rem;
    color: #6c757d;
    display: flex;
    align-items: center;
}
.autosave-indicator.saving {
    color: #ffc107;
}
.autosave-indicator.saved {
    color: #28a745;
}
.alert-pastel-primary {
    background-color: rgba(136, 108, 192, 0.1);
    border-color: rgba(136, 108, 192, 0.2);
    color: #5a4a7a;
}
.btn-save-draft {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: #fff !important;
}
.btn-save-draft:hover {
    background-color: #5a6268 !important;
    border-color: #5a6268 !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('management.email-to-sms') }}">Email-to-SMS</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management.email-to-sms') }}?tab=contact-lists">Contact List</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-link me-2 text-primary"></i>Create Email-to-SMS – Contact List</h4>
                    <span class="autosave-indicator saved" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <div class="card-body">
                    <div id="mappingWizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link" href="#step-general"><span>1</span><small>General</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-email"><span>2</span><small>Email</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-recipients"><span>3</span><small>Recipients</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-message"><span>4</span><small>Message</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-review"><span>5</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <div id="step-general" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 1: General</strong> – Define the basic information for this Email-to-SMS mapping.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label class="form-label">Mapping Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="mappingName" placeholder="e.g., Pharmacy Appointments" maxlength="50">
                                                <small class="text-muted">A unique, descriptive name for this mapping.</small>
                                                <div class="invalid-feedback">Please enter a mapping name.</div>
                                            </div>
                                            
                                            <div class="col-lg-12 mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" id="mappingDescription" rows="2" placeholder="Brief description of this mapping..." maxlength="200"></textarea>
                                                <small class="text-muted"><span id="descCharCount">0</span>/200 characters</small>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="form-label">Sub-Account <span class="text-danger">*</span></label>
                                                <select class="form-select" id="subAccount">
                                                    <option value="">Select sub-account...</option>
                                                    <option value="main">Main Account</option>
                                                    <option value="marketing">Marketing</option>
                                                    <option value="operations">Operations</option>
                                                    <option value="support">Support</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a sub-account.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-email" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 2: Email Settings</strong> – Configure which email addresses are allowed to trigger this mapping.
                                        </div>
                                        
                                        <div class="alert alert-pastel-secondary mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Email Address:</strong> A unique inbound email address will be generated when you complete and create this mapping. You'll be able to copy it from the confirmation screen.
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Allowed Sender Emails (Optional)</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="newSenderEmail" placeholder="e.g., user@domain.com or *@domain.com">
                                                <button class="btn btn-outline-primary" type="button" id="btnAddSender">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="emailError"></div>
                                            <small class="text-muted">Restrict who can trigger this mapping. Supports wildcards like *@domain.com</small>
                                        </div>
                                        
                                        <div id="wildcardWarning" class="alert alert-pastel-warning mb-3" style="display: none;">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Warning:</strong> Wildcard domains are less secure and may result in unintended messages being sent.
                                        </div>
                                        
                                        <div id="senderEmailsList" class="email-addresses-container mb-3"></div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><span id="emailAddressCount">0</span> email addresses added</small>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" id="clearAllEmails" style="display: none;">
                                                <i class="fas fa-trash-alt me-1"></i> Clear All
                                            </button>
                                        </div>
                                        
                                        <div class="alert alert-pastel-primary small mt-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Tip:</strong> If you leave this empty, any sender can trigger SMS messages to this Contact List.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-recipients" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 3: Recipients</strong> – Select recipients from your Contact Book. Combine contacts, lists, and tags.
                                        </div>
                                        
                                        {{-- Contact Book Selection Tabs (reusing Send Message pattern) --}}
                                        <div class="card mb-3">
                                            <div class="card-body p-3">
                                                <ul class="nav nav-tabs mb-3" id="recipientTabs">
                                                    <li class="nav-item">
                                                        <button class="nav-link active py-2 px-3" data-bs-toggle="tab" data-bs-target="#cbContactsTab">
                                                            <i class="fas fa-user me-1"></i> Contacts
                                                        </button>
                                                    </li>
                                                    <li class="nav-item">
                                                        <button class="nav-link py-2 px-3" data-bs-toggle="tab" data-bs-target="#cbListsTab">
                                                            <i class="fas fa-list me-1"></i> Lists
                                                        </button>
                                                    </li>
                                                    <li class="nav-item">
                                                        <button class="nav-link py-2 px-3" data-bs-toggle="tab" data-bs-target="#cbDynamicListsTab">
                                                            <i class="fas fa-sync-alt me-1"></i> Dynamic Lists
                                                        </button>
                                                    </li>
                                                    <li class="nav-item">
                                                        <button class="nav-link py-2 px-3" data-bs-toggle="tab" data-bs-target="#cbTagsTab">
                                                            <i class="fas fa-tags me-1"></i> Tags
                                                        </button>
                                                    </li>
                                                </ul>
                                                
                                                <div class="tab-content">
                                                    {{-- Contacts Tab --}}
                                                    <div class="tab-pane fade show active" id="cbContactsTab">
                                                        <div class="row mb-2">
                                                            <div class="col-md-6">
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                                    <input type="text" class="form-control" id="cbContactSearch" placeholder="Search names, numbers, tags...">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <select class="form-select form-select-sm" id="cbContactSort">
                                                                        <option value="recent">Most recently contacted</option>
                                                                        <option value="added">Most recently added</option>
                                                                        <option value="name_asc">Name A-Z</option>
                                                                        <option value="name_desc">Name Z-A</option>
                                                                    </select>
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnToggleContactFilters">
                                                                        <i class="fas fa-filter"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="d-none mb-2 p-2 bg-light rounded" id="cbContactFilters" style="font-size: 12px;">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label class="form-label mb-1">Tags</label>
                                                                    <select class="form-select form-select-sm" id="cbFilterTags">
                                                                        <option value="">All tags</option>
                                                                        <option value="vip">VIP</option>
                                                                        <option value="asthma">Asthma</option>
                                                                        <option value="diabetes">Diabetes</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label mb-1">Has Mobile</label>
                                                                    <select class="form-select form-select-sm" id="cbFilterMobile">
                                                                        <option value="">Any</option>
                                                                        <option value="yes">Yes</option>
                                                                        <option value="no">No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4 d-flex align-items-end">
                                                                    <button class="btn btn-link btn-sm" id="btnClearContactFilters">Clear filters</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                                                            <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                                                                <thead class="table-light sticky-top">
                                                                    <tr>
                                                                        <th style="width: 30px;"><input type="checkbox" class="form-check-input" id="cbSelectAllContacts"></th>
                                                                        <th>Name</th>
                                                                        <th>Mobile</th>
                                                                        <th>Tags</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="cbContactsTable">
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="1" data-name="John Smith"></td><td>John Smith</td><td>+44 7700***123</td><td><span class="badge bg-info">VIP</span></td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="2" data-name="Jane Doe"></td><td>Jane Doe</td><td>+44 7700***456</td><td><span class="badge bg-success">Asthma</span></td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="3" data-name="Robert Brown"></td><td>Robert Brown</td><td>+44 7700***789</td><td></td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="4" data-name="Sarah Wilson"></td><td>Sarah Wilson</td><td>+44 7700***012</td><td><span class="badge bg-warning">Diabetes</span></td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="5" data-name="Michael Johnson"></td><td>Michael Johnson</td><td>+44 7700***345</td><td><span class="badge bg-info">VIP</span></td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="6" data-name="Emily Davis"></td><td>Emily Davis</td><td>+44 7700***678</td><td><span class="badge bg-success">Asthma</span></td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="7" data-name="David Miller"></td><td>David Miller</td><td>+44 7700***901</td><td></td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="8" data-name="Lisa Anderson"></td><td>Lisa Anderson</td><td>+44 7700***234</td><td><span class="badge bg-warning">Diabetes</span> <span class="badge bg-danger">Hypertension</span></td></tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Static Lists Tab --}}
                                                    <div class="tab-pane fade" id="cbListsTab">
                                                        <div class="input-group input-group-sm mb-2">
                                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                            <input type="text" class="form-control" id="cbListSearch" placeholder="Search lists...">
                                                        </div>
                                                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                                                            <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                                                                <thead class="table-light sticky-top">
                                                                    <tr><th style="width: 30px;"></th><th>List Name</th><th>Contacts</th><th>Last Updated</th></tr>
                                                                </thead>
                                                                <tbody id="cbListsTable">
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-list" value="1" data-name="VIP Patients" data-count="1234"></td><td>VIP Patients</td><td>1,234</td><td>22-Dec-2025</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-list" value="2" data-name="Newsletter Subscribers" data-count="5678"></td><td>Newsletter Subscribers</td><td>5,678</td><td>21-Dec-2025</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-list" value="3" data-name="Flu Campaign 2025" data-count="3456"></td><td>Flu Campaign 2025</td><td>3,456</td><td>20-Dec-2025</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-list" value="4" data-name="Repeat Prescriptions" data-count="2100"></td><td>Repeat Prescriptions</td><td>2,100</td><td>19-Dec-2025</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-list" value="5" data-name="NHS Reminders" data-count="8900"></td><td>NHS Reminders</td><td>8,900</td><td>18-Dec-2025</td></tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Dynamic Lists Tab --}}
                                                    <div class="tab-pane fade" id="cbDynamicListsTab">
                                                        <div class="input-group input-group-sm mb-2">
                                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                            <input type="text" class="form-control" id="cbDynamicSearch" placeholder="Search dynamic lists...">
                                                        </div>
                                                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                                                            <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                                                                <thead class="table-light sticky-top">
                                                                    <tr><th style="width: 30px;"></th><th>List Name</th><th>Rules</th><th>Contacts</th><th>Last Evaluated</th></tr>
                                                                </thead>
                                                                <tbody id="cbDynamicListsTable">
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-dynamic" value="1" data-name="Over 65s" data-count="2345"></td><td>Over 65s</td><td>Age > 65</td><td>2,345</td><td>22-Dec-2025</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-dynamic" value="2" data-name="Local Postcodes" data-count="1890"></td><td>Local Postcodes</td><td>Postcode starts with SW</td><td>1,890</td><td>22-Dec-2025</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-dynamic" value="3" data-name="Active Last 30 Days" data-count="4500"></td><td>Active Last 30 Days</td><td>Last contact < 30 days</td><td>4,500</td><td>22-Dec-2025</td></tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Tags Tab --}}
                                                    <div class="tab-pane fade" id="cbTagsTab">
                                                        <div class="input-group input-group-sm mb-2">
                                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                            <input type="text" class="form-control" id="cbTagSearch" placeholder="Search tags...">
                                                        </div>
                                                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                                                            <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                                                                <thead class="table-light sticky-top">
                                                                    <tr><th style="width: 30px;"></th><th>Tag</th><th>Contacts</th></tr>
                                                                </thead>
                                                                <tbody id="cbTagsTable">
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="1" data-name="VIP" data-count="456"></td><td><span class="badge" style="background-color: #0d6efd;">VIP</span></td><td>456</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="2" data-name="Asthma" data-count="1234"></td><td><span class="badge" style="background-color: #198754;">Asthma</span></td><td>1,234</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="3" data-name="Diabetes" data-count="890"></td><td><span class="badge" style="background-color: #ffc107;">Diabetes</span></td><td>890</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="4" data-name="Hypertension" data-count="567"></td><td><span class="badge" style="background-color: #dc3545;">Hypertension</span></td><td>567</td></tr>
                                                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="5" data-name="Chronic Care" data-count="1023"></td><td><span class="badge" style="background-color: #6f42c1;">Chronic Care</span></td><td>1,023</td></tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Recipient Summary - Compact Horizontal Bar --}}
                                        <div class="bg-light border rounded p-2 mb-3">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 13px;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="fw-medium"><i class="fas fa-users me-1"></i> Recipient Summary:</span>
                                                    <span id="recipientChipsInline" class="text-muted">No recipients selected</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span><span class="text-muted">Contacts:</span> <strong id="summaryContactsCount">0</strong></span>
                                                    <span><span class="text-muted">Lists:</span> <strong id="summaryListsCount">0</strong></span>
                                                    <span><span class="text-muted">Total:</span> <strong id="summaryTotalCount" class="text-primary">0</strong></span>
                                                    <span><span class="text-muted">Deduped:</span> <strong id="summaryDedupedCount" class="text-success">0</strong></span>
                                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" id="btnClearAllRecipients" style="display: none;">
                                                        <i class="fas fa-times"></i> Clear
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="alert alert-pastel-warning small mt-2 mb-0 d-none" id="invalidRecipientsWarning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <span id="invalidRecipientsText">0 recipients excluded (no valid mobile)</span>
                                            </div>
                                        </div>
                                        
                                        {{-- Opt-out List Selection --}}
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <h6 class="mb-3"><i class="fas fa-ban me-2"></i>Opt-out Lists</h6>
                                                <p class="text-muted small mb-2">Select opt-out lists to exclude. Recipients on these lists will not receive SMS.</p>
                                                
                                                <select class="form-select" id="optOutLists" multiple size="4">
                                                    <option value="NO" selected>NO - Do not apply any opt-out list</option>
                                                    <option value="1">Global Opt-out List (2,345 contacts)</option>
                                                    <option value="2">Marketing Opt-out (1,234 contacts)</option>
                                                    <option value="3">NHS Do Not Contact (567 contacts)</option>
                                                    <option value="4">Temporary Opt-out (89 contacts)</option>
                                                </select>
                                                <small class="text-muted">Hold Ctrl/Cmd to select multiple. Select "NO" to include all recipients.</small>
                                            </div>
                                        </div>
                                        
                                        <div class="invalid-feedback" id="recipientError" style="display: none;">
                                            Please select at least one recipient to continue.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-message" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 4: Message Settings</strong> – Configure how incoming emails are processed into SMS messages.
                                        </div>
                                        
                                        {{-- SenderID Section --}}
                                        <div class="mb-4">
                                            <label class="form-label">SenderID <span class="text-danger">*</span></label>
                                            <select class="form-select" id="senderId">
                                                <option value="">Select SenderID...</option>
                                                <option value="QuickSMS">QuickSMS</option>
                                                <option value="NHSTrust">NHSTrust</option>
                                                <option value="Pharmacy">Pharmacy</option>
                                                <option value="Clinic">Clinic</option>
                                                <option value="Appointments">Appointments</option>
                                                <option value="Reminders">Reminders</option>
                                            </select>
                                            <small class="text-muted">The approved SenderID that will appear on SMS messages.</small>
                                            <div class="invalid-feedback">Please select a SenderID.</div>
                                        </div>
                                        
                                        {{-- Dynamic SenderID Toggle (visible if account flag allows) --}}
                                        <div class="mb-4" id="dynamicSenderIdSection">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="subjectAsSenderId">
                                                <label class="form-check-label" for="subjectAsSenderId">
                                                    <strong>Use Email Subject as SenderID</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Override the selected SenderID with content from the email subject line. Subject must be 3-11 alphanumeric characters.</small>
                                            
                                            <div class="alert alert-pastel-warning small mt-2 d-none" id="dynamicSenderIdWarning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <strong>Warning:</strong> When enabled, the SenderID dropdown above becomes a fallback. Invalid subjects will use the fallback SenderID.
                                            </div>
                                        </div>
                                        
                                        <hr class="my-3">
                                        
                                        {{-- Multiple SMS Toggle --}}
                                        <div class="mb-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="multipleSms" checked>
                                                <label class="form-check-label" for="multipleSms">
                                                    <strong>Enable Multiple SMS</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Allow messages longer than 160 characters to be split into multiple SMS segments. If disabled, messages will be truncated.</small>
                                        </div>
                                        
                                        {{-- Delivery Reports Toggle with Email Input --}}
                                        <div class="mb-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="deliveryReports" checked>
                                                <label class="form-check-label" for="deliveryReports">
                                                    <strong>Send Delivery Reports</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Receive delivery status notifications for sent messages.</small>
                                            
                                            <div class="mt-2" id="deliveryReportsEmailSection">
                                                <label class="form-label">Delivery Report Email</label>
                                                <input type="email" class="form-control" id="deliveryReportEmail" placeholder="reports@yourcompany.com">
                                                <small class="text-muted">Email address to receive delivery status reports.</small>
                                                <div class="invalid-feedback">Please enter a valid email address.</div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-3">
                                        
                                        {{-- Content Filter Textarea --}}
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <strong>Filter Content</strong> <span class="badge bg-light text-dark">Optional</span>
                                            </label>
                                            <textarea class="form-control" id="contentFilter" rows="4" placeholder="Enter regex patterns to remove from email content (one per line)&#10;&#10;Example:&#10;^--[\s\S]*$&#10;Sent from my iPhone&#10;\[image:.*?\]"></textarea>
                                            <small class="text-muted">
                                                Enter regex patterns (one per line) to filter out signatures, footers, or unwanted content from emails before converting to SMS.
                                            </small>
                                            
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnTestFilter">
                                                    <i class="fas fa-vial me-1"></i> Test Patterns
                                                </button>
                                                <button type="button" class="btn btn-link btn-sm" id="btnAddCommonPatterns">
                                                    <i class="fas fa-plus me-1"></i> Add Common Patterns
                                                </button>
                                            </div>
                                            
                                            <div class="alert alert-pastel-primary small mt-2 d-none" id="filterTestResult">
                                                <strong>Test Result:</strong> <span id="filterTestOutput"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-review" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 5: Review & Confirm</strong> – Please review your mapping configuration before creating.
                                        </div>
                                        
                                        {{-- Generated Email Address - Prominent Display --}}
                                        <div class="card mb-4 border-success">
                                            <div class="card-header py-2 bg-success text-white">
                                                <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Your Generated Email Address</h6>
                                            </div>
                                            <div class="card-body text-center py-4">
                                                <code class="d-block fs-5 mb-3" id="generatedEmailDisplay" style="font-size: 1.25rem !important;">Generating...</code>
                                                <button class="btn btn-outline-success" id="btnCopyEmail">
                                                    <i class="fas fa-copy me-2"></i> Copy to Clipboard
                                                </button>
                                                <p class="text-muted small mt-3 mb-0">
                                                    Send emails to this address to trigger SMS messages to your selected recipients.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            {{-- Left Column: Configuration Summary --}}
                                            <div class="col-lg-7">
                                                <div class="card mb-3">
                                                    <div class="card-header py-2">
                                                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Configuration Summary</h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <table class="table table-sm mb-0" style="font-size: 13px;">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-muted" style="width: 40%;">Mapping Name</td>
                                                                    <td class="fw-medium" id="summaryName">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Description</td>
                                                                    <td id="summaryDescription">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Sub-Account</td>
                                                                    <td id="summarySubAccount">-</td>
                                                                </tr>
                                                                <tr class="table-light">
                                                                    <td class="text-muted">Email-to-SMS Address</td>
                                                                    <td class="fw-medium text-primary" id="summaryEmail">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Allowed Senders</td>
                                                                    <td id="summarySenders">-</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                
                                                <div class="card mb-3">
                                                    <div class="card-header py-2">
                                                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Recipients</h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <table class="table table-sm mb-0" style="font-size: 13px;">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-muted" style="width: 40%;">Selected From</td>
                                                                    <td id="summaryContactList">-</td>
                                                                </tr>
                                                                <tr class="table-success">
                                                                    <td class="text-muted">Total Recipients (Deduped)</td>
                                                                    <td class="fw-bold" id="summaryRecipients">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Opt-out Lists Applied</td>
                                                                    <td id="summaryOptOut">-</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                
                                                <div class="card mb-3">
                                                    <div class="card-header py-2">
                                                        <h6 class="mb-0"><i class="fas fa-sms me-2"></i>Message Settings</h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <table class="table table-sm mb-0" style="font-size: 13px;">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-muted" style="width: 40%;">SenderID</td>
                                                                    <td class="fw-medium" id="summarySenderId">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Settings</td>
                                                                    <td id="summaryMessageSettings">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Content Filters</td>
                                                                    <td id="summaryContentFilter">-</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- Right Column: How It Works + Actions --}}
                                            <div class="col-lg-5">
                                                <div class="card mb-3 border-primary">
                                                    <div class="card-header py-2 bg-primary text-white">
                                                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>How It Works</h6>
                                                    </div>
                                                    <div class="card-body" style="font-size: 13px;">
                                                        <ol class="mb-0 ps-3">
                                                            <li class="mb-2">Email sent to <strong id="summaryEmailInline">-</strong></li>
                                                            <li class="mb-2"><strong>SenderID</strong> extracted from email subject (if enabled)</li>
                                                            <li class="mb-2"><strong>SMS content</strong> extracted from email body</li>
                                                            <li class="mb-2">Content filters applied (signatures removed)</li>
                                                            <li class="mb-0">SMS sent to <strong id="summaryRecipientCountInline">0</strong> recipients</li>
                                                        </ol>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-pastel-warning small">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    <strong>Note:</strong> Once created, the email address cannot be changed. You can edit other settings later.
                                                </div>
                                                
                                                <div class="d-grid gap-2">
                                                    <button type="button" class="btn btn-primary btn-lg" id="btnCreateMapping">
                                                        <i class="fas fa-check-circle me-2"></i> Create Mapping
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" id="btnSaveDraftReview">
                                                        <i class="fas fa-save me-2"></i> Save as Draft
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="toolbar-bottom" id="wizardToolbar">
                            <button type="button" class="btn btn-save-draft" id="btnSaveDraft">
                                <i class="fas fa-save me-1"></i> Save Draft
                            </button>
                            <button type="button" class="btn sw-btn-prev" id="btnPrev" style="display: none;">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </button>
                            <button type="button" class="btn sw-btn-next" id="btnNext">
                                Next <i class="fas fa-arrow-right ms-1"></i>
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
<script src="{{ asset('vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
<script src="{{ asset('js/services/email-to-sms-service.js') }}"></script>
<script>
$(document).ready(function() {
    var contactBookData = {
        contacts: [],
        lists: [],
        dynamicLists: [],
        tags: [],
        optOutLists: []
    };
    
    var senderIdTemplates = [];
    
    var wizardData = {
        name: '',
        description: '',
        subAccount: '',
        allowedSenders: [],
        generatedEmail: '',
        selectedContacts: [],
        selectedLists: [],
        selectedDynamicLists: [],
        selectedTags: [],
        optOutLists: ['NO'],
        senderId: '',
        subjectAsSenderId: false,
        multipleSms: true,
        deliveryReports: true,
        deliveryReportEmail: '',
        contentFilterPatterns: ''
    };
    
    var accountFlags = {
        dynamic_senderid_allowed: true
    };
    
    function loadContactBookData(subaccountId) {
        EmailToSmsService.getContactBookData(subaccountId).then(function(response) {
            if (response.success) {
                contactBookData = response.data;
                renderContactsTab();
                renderListsTab();
                renderDynamicListsTab();
                renderTagsTab();
                renderOptOutLists();
            } else {
                showErrorToast('Failed to load contact book data');
            }
        }).catch(function(err) {
            console.error('Error loading contact book data:', err);
            showErrorToast('Failed to load contact book data');
        });
    }
    
    function loadSenderIdTemplates(subaccountId) {
        EmailToSmsService.getApprovedSmsTemplates(subaccountId).then(function(response) {
            if (response.success) {
                senderIdTemplates = response.data;
                renderSenderIdDropdown();
            }
        }).catch(function(err) {
            console.error('Error loading SenderID templates:', err);
        });
    }
    
    function loadAccountFlags() {
        EmailToSmsService.getAccountFlags().then(function(response) {
            if (response.success) {
                accountFlags = response.data;
                updateDynamicSenderIdVisibility();
            }
        }).catch(function(err) {
            console.error('Error loading account flags:', err);
        });
    }
    
    function renderSenderIdDropdown() {
        var html = '<option value="">Select SenderID...</option>';
        senderIdTemplates.forEach(function(tpl) {
            html += '<option value="' + tpl.id + '">' + tpl.senderId + '</option>';
        });
        $('#senderId').html(html);
    }
    
    function updateDynamicSenderIdVisibility() {
        if (accountFlags.dynamic_senderid_allowed) {
            $('#dynamicSenderIdGroup').show();
        } else {
            $('#dynamicSenderIdGroup').hide();
            $('#subjectAsSenderId').prop('checked', false);
            wizardData.subjectAsSenderId = false;
        }
    }
    
    function renderContactsTab() {
        var html = '';
        if (contactBookData.contacts.length === 0) {
            html = '<div class="text-muted text-center py-3">No contacts available</div>';
        } else {
            html = '<div class="form-check mb-2"><input type="checkbox" class="form-check-input" id="cbSelectAllContacts"> <label class="form-check-label small" for="cbSelectAllContacts">Select All</label></div>';
            contactBookData.contacts.forEach(function(contact) {
                var isChecked = wizardData.selectedContacts.some(function(c) { return c.id === contact.id; });
                html += '<div class="form-check"><input type="checkbox" class="form-check-input cb-contact" value="' + contact.id + '" data-name="' + contact.name + '"' + (isChecked ? ' checked' : '') + '>' +
                    '<label class="form-check-label small">' + contact.name + ' <span class="text-muted">(' + contact.mobile + ')</span></label></div>';
            });
        }
        $('#recipientContactsTab').html(html);
    }
    
    function renderListsTab() {
        var html = '';
        if (contactBookData.lists.length === 0) {
            html = '<div class="text-muted text-center py-3">No contact lists available</div>';
        } else {
            contactBookData.lists.forEach(function(list) {
                var isChecked = wizardData.selectedLists.some(function(l) { return l.id === list.id; });
                html += '<div class="form-check"><input type="checkbox" class="form-check-input cb-list" value="' + list.id + '" data-name="' + list.name + '" data-count="' + list.recipientCount + '"' + (isChecked ? ' checked' : '') + '>' +
                    '<label class="form-check-label small">' + list.name + ' <span class="text-muted">(' + list.recipientCount.toLocaleString() + ')</span></label></div>';
            });
        }
        $('#recipientListsTab').html(html);
    }
    
    function renderDynamicListsTab() {
        var html = '';
        if (contactBookData.dynamicLists.length === 0) {
            html = '<div class="text-muted text-center py-3">No dynamic lists available</div>';
        } else {
            contactBookData.dynamicLists.forEach(function(list) {
                var isChecked = wizardData.selectedDynamicLists.some(function(l) { return l.id === list.id; });
                html += '<div class="form-check"><input type="checkbox" class="form-check-input cb-dynamic" value="' + list.id + '" data-name="' + list.name + '" data-count="' + list.recipientCount + '"' + (isChecked ? ' checked' : '') + '>' +
                    '<label class="form-check-label small">' + list.name + ' <span class="badge bg-info ms-1">Dynamic</span> <span class="text-muted">(' + list.recipientCount.toLocaleString() + ')</span></label></div>';
            });
        }
        $('#recipientDynamicTab').html(html);
    }
    
    function renderTagsTab() {
        var html = '';
        if (contactBookData.tags.length === 0) {
            html = '<div class="text-muted text-center py-3">No tags available</div>';
        } else {
            contactBookData.tags.forEach(function(tag) {
                var isChecked = wizardData.selectedTags.some(function(t) { return t.id === tag.id; });
                html += '<div class="form-check"><input type="checkbox" class="form-check-input cb-tag" value="' + tag.id + '" data-name="' + tag.name + '" data-count="' + tag.recipientCount + '"' + (isChecked ? ' checked' : '') + '>' +
                    '<label class="form-check-label small"><span class="badge me-1" style="background-color: ' + (tag.color || '#6c757d') + '">' + tag.name + '</span> <span class="text-muted">(' + tag.recipientCount.toLocaleString() + ')</span></label></div>';
            });
        }
        $('#recipientTagsTab').html(html);
    }
    
    function renderOptOutLists() {
        var html = '<option value="NO">Do not apply opt-out lists</option>';
        contactBookData.optOutLists.forEach(function(list) {
            html += '<option value="' + list.id + '">' + list.name + ' (' + list.recipientCount.toLocaleString() + ')</option>';
        });
        $('#optOutList').html(html);
    }
    
    var currentStep = 0;
    var totalSteps = 5;
    
    function renderSenderEmails() {
        var html = '';
        var hasWildcard = false;
        
        wizardData.allowedSenders.forEach(function(email, index) {
            if (email.startsWith('*@')) {
                hasWildcard = true;
            }
            html += '<span class="email-address-badge">' + email + 
                    '<button class="remove-btn" data-index="' + index + '"><i class="fas fa-times"></i></button></span>';
        });
        
        $('#senderEmailsList').html(html);
        $('#emailAddressCount').text(wizardData.allowedSenders.length);
        $('#clearAllEmails').toggle(wizardData.allowedSenders.length > 0);
        $('#wildcardWarning').toggle(hasWildcard);
    }
    
    function isValidEmailOrWildcard(email) {
        var standardEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var wildcardPattern = /^\*@[^\s@]+\.[^\s@]+$/;
        
        return standardEmailPattern.test(email) || wildcardPattern.test(email);
    }
    
    function generateEmailAddress() {
        var name = wizardData.name || 'mapping';
        var slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        var random = Math.floor(1000 + Math.random() * 9000);
        wizardData.generatedEmail = slug + '-' + random + '@sms.quicksms.com';
        $('#generatedEmailDisplay').text(wizardData.generatedEmail);
        $('#btnCopyEmail').prop('disabled', false);
    }
    
    function updateSummary() {
        $('#summaryName').text(wizardData.name || '-');
        $('#summaryDescription').text(wizardData.description || 'No description');
        $('#summarySubAccount').text(wizardData.subAccount ? $('#subAccount option:selected').text() : '-');
        $('#summaryEmail').text(wizardData.generatedEmail || '-');
        $('#summaryEmailInline').text(wizardData.generatedEmail || '-');
        $('#summarySenders').text(wizardData.allowedSenders.length > 0 ? wizardData.allowedSenders.length + ' whitelisted' : 'Any sender allowed');
        
        var recipientParts = [];
        if (wizardData.selectedContacts.length > 0) recipientParts.push(wizardData.selectedContacts.length + ' contacts');
        if (wizardData.selectedLists.length > 0) recipientParts.push(wizardData.selectedLists.length + ' lists');
        if (wizardData.selectedDynamicLists.length > 0) recipientParts.push(wizardData.selectedDynamicLists.length + ' dynamic lists');
        if (wizardData.selectedTags.length > 0) recipientParts.push(wizardData.selectedTags.length + ' tags');
        $('#summaryContactList').text(recipientParts.length > 0 ? recipientParts.join(', ') : '-');
        
        var stats = calculateRecipientStats();
        $('#summaryRecipients').text(stats.deduped > 0 ? stats.deduped.toLocaleString() + ' recipients' : '-');
        $('#summaryRecipientCountInline').text(stats.deduped.toLocaleString());
        $('#summarySenderId').text(wizardData.senderId ? $('#senderId option:selected').text() : '-');
        
        var optOutText = wizardData.optOutLists.includes('NO') ? 'None applied' : wizardData.optOutLists.length + ' opt-out list(s)';
        $('#summaryOptOut').text(optOutText);
        
        var settings = [];
        if (wizardData.multipleSms) settings.push('Multiple SMS');
        if (wizardData.deliveryReports) {
            var reportStr = 'Delivery Reports';
            if (wizardData.deliveryReportEmail) {
                reportStr += ' (' + wizardData.deliveryReportEmail + ')';
            }
            settings.push(reportStr);
        }
        if (wizardData.subjectAsSenderId) settings.push('Subject as SenderID');
        $('#summaryMessageSettings').text(settings.length > 0 ? settings.join(', ') : 'Default');
        
        var filterCount = wizardData.contentFilterPatterns.trim() ? wizardData.contentFilterPatterns.split('\n').filter(p => p.trim()).length : 0;
        $('#summaryContentFilter').text(filterCount > 0 ? filterCount + ' pattern(s) configured' : 'None');
    }
    
    function calculateRecipientStats() {
        var contactCount = wizardData.selectedContacts.length;
        var listCount = 0;
        var dynamicCount = 0;
        var tagCount = 0;
        
        wizardData.selectedLists.forEach(function(list) {
            listCount += parseInt(list.count) || 0;
        });
        wizardData.selectedDynamicLists.forEach(function(list) {
            dynamicCount += parseInt(list.count) || 0;
        });
        wizardData.selectedTags.forEach(function(tag) {
            tagCount += parseInt(tag.count) || 0;
        });
        
        var total = contactCount + listCount + dynamicCount + tagCount;
        var deduped = Math.floor(total * 0.95);
        var invalid = Math.floor(total * 0.02);
        
        return {
            contacts: contactCount,
            lists: listCount,
            dynamic: dynamicCount,
            tags: tagCount,
            total: total,
            deduped: deduped,
            invalid: invalid
        };
    }
    
    function updateRecipientSummary() {
        var stats = calculateRecipientStats();
        
        $('#summaryContactsCount').text(wizardData.selectedContacts.length);
        $('#summaryListsCount').text(wizardData.selectedLists.length + wizardData.selectedDynamicLists.length + wizardData.selectedTags.length);
        $('#summaryTotalCount').text(stats.total.toLocaleString());
        $('#summaryDedupedCount').text(stats.deduped.toLocaleString());
        
        if (stats.invalid > 0) {
            $('#invalidRecipientsWarning').removeClass('d-none');
            $('#invalidRecipientsText').text(stats.invalid + ' recipients excluded (no valid mobile)');
        } else {
            $('#invalidRecipientsWarning').addClass('d-none');
        }
        
        renderRecipientChips();
        
        var hasRecipients = wizardData.selectedContacts.length > 0 || 
                           wizardData.selectedLists.length > 0 || 
                           wizardData.selectedDynamicLists.length > 0 || 
                           wizardData.selectedTags.length > 0;
        $('#btnClearAllRecipients').toggle(hasRecipients);
        
        updateNextButtonState();
    }
    
    function renderRecipientChips() {
        var html = '';
        
        wizardData.selectedContacts.forEach(function(contact) {
            html += '<span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-user me-1"></i>' + contact.name + 
                    '<button type="button" class="btn-close btn-close-sm ms-1" data-type="contact" data-id="' + contact.id + '"></button></span>';
        });
        
        wizardData.selectedLists.forEach(function(list) {
            html += '<span class="badge bg-primary me-1 mb-1"><i class="fas fa-list me-1"></i>' + list.name + ' (' + parseInt(list.count).toLocaleString() + ')' +
                    '<button type="button" class="btn-close btn-close-white btn-close-sm ms-1" data-type="list" data-id="' + list.id + '"></button></span>';
        });
        
        wizardData.selectedDynamicLists.forEach(function(list) {
            html += '<span class="badge bg-info me-1 mb-1"><i class="fas fa-sync-alt me-1"></i>' + list.name + ' (' + parseInt(list.count).toLocaleString() + ')' +
                    '<button type="button" class="btn-close btn-close-white btn-close-sm ms-1" data-type="dynamic" data-id="' + list.id + '"></button></span>';
        });
        
        wizardData.selectedTags.forEach(function(tag) {
            html += '<span class="badge bg-success me-1 mb-1"><i class="fas fa-tag me-1"></i>' + tag.name + ' (' + parseInt(tag.count).toLocaleString() + ')' +
                    '<button type="button" class="btn-close btn-close-white btn-close-sm ms-1" data-type="tag" data-id="' + tag.id + '"></button></span>';
        });
        
        if (html === '') {
            html = '<span class="text-muted small">No recipients selected</span>';
        }
        
        $('#recipientChipsInline').html(html);
    }
    
    function validateStep(stepIndex) {
        var isValid = true;
        
        switch (stepIndex) {
            case 0:
                if (!wizardData.name.trim()) {
                    $('#mappingName').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#mappingName').removeClass('is-invalid');
                }
                if (!wizardData.subAccount) {
                    $('#subAccount').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#subAccount').removeClass('is-invalid');
                }
                break;
            case 2:
                var hasRecipients = wizardData.selectedContacts.length > 0 || 
                                   wizardData.selectedLists.length > 0 || 
                                   wizardData.selectedDynamicLists.length > 0 || 
                                   wizardData.selectedTags.length > 0;
                if (!hasRecipients) {
                    $('#recipientError').show();
                    isValid = false;
                } else {
                    $('#recipientError').hide();
                }
                break;
            case 3:
                if (!wizardData.senderId) {
                    $('#senderId').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#senderId').removeClass('is-invalid');
                }
                break;
        }
        
        return isValid;
    }
    
    function goToStep(stepIndex) {
        if (stepIndex < 0 || stepIndex >= totalSteps) return;
        
        $('.tab-pane').removeClass('active show');
        $('.nav-wizard .nav-link').removeClass('active done');
        
        var stepIds = ['step-general', 'step-email', 'step-recipients', 'step-message', 'step-review'];
        $('#' + stepIds[stepIndex]).addClass('active show');
        
        $('.nav-wizard .nav-link').each(function(index) {
            if (index < stepIndex) {
                $(this).addClass('done');
            } else if (index === stepIndex) {
                $(this).addClass('active');
            }
        });
        
        currentStep = stepIndex;
        updateNavButtons();
        
        if (stepIndex === totalSteps - 1) {
            if (wizardData.name && !wizardData.generatedEmail) {
                generateEmailAddress();
            }
            updateSummary();
        }
    }
    
    function updateNavButtons() {
        if (currentStep === 0) {
            $('#btnPrev').hide();
        } else {
            $('#btnPrev').show();
        }
        
        if (currentStep === totalSteps - 1) {
            $('#btnNext').hide();
            $('#btnSaveDraft').hide();
        } else {
            $('#btnNext').show().html('Next <i class="fas fa-arrow-right ms-1"></i>');
            $('#btnSaveDraft').show();
        }
        
        updateNextButtonState();
    }
    
    function updateNextButtonState() {
        var canProceed = false;
        
        switch (currentStep) {
            case 0:
                canProceed = wizardData.name.trim() !== '' && wizardData.subAccount !== '';
                break;
            case 1:
                canProceed = true;
                break;
            case 2:
                canProceed = wizardData.selectedContacts.length > 0 || 
                             wizardData.selectedLists.length > 0 || 
                             wizardData.selectedDynamicLists.length > 0 || 
                             wizardData.selectedTags.length > 0;
                break;
            case 3:
                canProceed = wizardData.senderId !== '';
                break;
            case 4:
                canProceed = true;
                break;
            default:
                canProceed = true;
        }
        
        $('#btnNext').prop('disabled', !canProceed);
    }
    
    function saveDraft() {
        $('#autosaveIndicator').removeClass('saved').addClass('saving');
        $('#autosaveText').text('Saving...');
        
        setTimeout(function() {
            $('#autosaveIndicator').removeClass('saving').addClass('saved');
            $('#autosaveText').text('Draft saved');
        }, 500);
    }
    
    function showSuccessToast(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    }
    
    function showErrorToast(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }
    
    loadAccountFlags();
    loadContactBookData();
    loadSenderIdTemplates();
    renderSenderEmails();
    goToStep(0);
    
    $('#mappingName').on('input', function() {
        wizardData.name = $(this).val().trim();
        $(this).removeClass('is-invalid');
        if (wizardData.name) {
            generateEmailAddress();
        }
        updateNextButtonState();
    });
    
    $('#mappingDescription').on('input', function() {
        wizardData.description = $(this).val();
        $('#descCharCount').text($(this).val().length);
    });
    
    $('#subAccount').on('change', function() {
        wizardData.subAccount = $(this).val();
        $(this).removeClass('is-invalid');
        
        wizardData.selectedContacts = [];
        wizardData.selectedLists = [];
        wizardData.selectedDynamicLists = [];
        wizardData.selectedTags = [];
        wizardData.optOutLists = ['NO'];
        wizardData.senderId = '';
        
        if (wizardData.subAccount) {
            loadContactBookData(wizardData.subAccount);
            loadSenderIdTemplates(wizardData.subAccount);
        }
        
        updateRecipientSummary();
        updateNextButtonState();
    });
    
    $('#senderId').on('change', function() {
        wizardData.senderId = $(this).val();
        $(this).removeClass('is-invalid');
        updateNextButtonState();
    });
    
    $('#multipleSms').on('change', function() {
        wizardData.multipleSms = $(this).is(':checked');
    });
    
    $('#deliveryReports').on('change', function() {
        wizardData.deliveryReports = $(this).is(':checked');
        if (wizardData.deliveryReports) {
            $('#deliveryReportsEmailSection').slideDown();
        } else {
            $('#deliveryReportsEmailSection').slideUp();
            wizardData.deliveryReportEmail = '';
            $('#deliveryReportEmail').val('').removeClass('is-invalid');
        }
    });
    
    $('#deliveryReportEmail').on('input', function() {
        var email = $(this).val().trim();
        wizardData.deliveryReportEmail = email;
        
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#contentFilter').on('input', function() {
        wizardData.contentFilterPatterns = $(this).val();
    });
    
    $('#subjectAsSenderId').on('change', function() {
        wizardData.subjectAsSenderId = $(this).is(':checked');
        if (wizardData.subjectAsSenderId) {
            $('#dynamicSenderIdWarning').removeClass('d-none');
        } else {
            $('#dynamicSenderIdWarning').addClass('d-none');
        }
    });
    
    $('#btnTestFilter').on('click', function() {
        var patterns = wizardData.contentFilterPatterns.split('\n').filter(p => p.trim());
        if (patterns.length === 0) {
            $('#filterTestResult').removeClass('d-none');
            $('#filterTestOutput').text('No patterns defined. Add patterns to test.');
            return;
        }
        
        var testContent = "Hello, this is a test message.\n\n--\nSent from my iPhone\nJohn Smith\nEmail: john@example.com";
        var result = testContent;
        
        patterns.forEach(function(pattern) {
            try {
                var regex = new RegExp(pattern, 'gim');
                result = result.replace(regex, '');
            } catch (e) {
                result = '[Invalid regex: ' + pattern + ']';
            }
        });
        
        $('#filterTestResult').removeClass('d-none');
        $('#filterTestOutput').html('<br><strong>Before:</strong><pre class="mb-1 small bg-light p-2">' + testContent.replace(/</g, '&lt;') + '</pre><strong>After:</strong><pre class="mb-0 small bg-light p-2">' + result.trim().replace(/</g, '&lt;') + '</pre>');
    });
    
    $('#btnAddCommonPatterns').on('click', function() {
        var commonPatterns = [
            '^--[\\s\\S]*$',
            'Sent from my iPhone',
            'Sent from my Android',
            '\\[image:.*?\\]',
            'Get Outlook for iOS',
            'Confidentiality Notice:.*'
        ];
        
        var current = $('#contentFilter').val();
        var existing = current.split('\n').filter(p => p.trim());
        var toAdd = commonPatterns.filter(p => !existing.includes(p));
        
        if (toAdd.length > 0) {
            var newValue = current ? current + '\n' + toAdd.join('\n') : toAdd.join('\n');
            $('#contentFilter').val(newValue);
            wizardData.contentFilterPatterns = newValue;
        }
    });
    
    if (!accountFlags.dynamic_senderid_allowed) {
        $('#dynamicSenderIdSection').hide();
    }
    
    $('#searchContactList').on('input', function() {
        var term = $(this).val().toLowerCase();
        renderListsTab();
    });
    
    function addSenderEmail() {
        var email = $('#newSenderEmail').val().trim().toLowerCase();
        if (!email) return;
        
        $('#emailError').hide();
        $('#newSenderEmail').removeClass('is-invalid');
        
        if (!isValidEmailOrWildcard(email)) {
            $('#emailError').text('Invalid format. Use user@domain.com or *@domain.com').show();
            $('#newSenderEmail').addClass('is-invalid');
            return;
        }
        
        if (wizardData.allowedSenders.indexOf(email) !== -1) {
            $('#emailError').text('This email address is already added').show();
            $('#newSenderEmail').addClass('is-invalid');
            return;
        }
        
        wizardData.allowedSenders.push(email);
        $('#newSenderEmail').val('').removeClass('is-invalid');
        $('#emailError').hide();
        renderSenderEmails();
        saveDraft();
    }
    
    $('#btnAddSender').on('click', addSenderEmail);
    $('#newSenderEmail').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            addSenderEmail();
        }
    });
    
    $(document).on('click', '#senderEmailsList .remove-btn', function() {
        var index = $(this).data('index');
        wizardData.allowedSenders.splice(index, 1);
        renderSenderEmails();
        saveDraft();
    });
    
    $('#clearAllEmails').on('click', function() {
        wizardData.allowedSenders = [];
        renderSenderEmails();
        saveDraft();
    });
    
    $('#btnCopyEmail').on('click', function() {
        navigator.clipboard.writeText(wizardData.generatedEmail).then(function() {
            var btn = $('#btnCopyEmail');
            btn.html('<i class="fas fa-check me-1"></i> Copied!');
            setTimeout(function() {
                btn.html('<i class="fas fa-copy me-1"></i> Copy to Clipboard');
            }, 2000);
        });
    });
    
    $('#btnNext').on('click', function() {
        if (currentStep === totalSteps - 1) {
            return;
        }
        
        if (!validateStep(currentStep)) {
            return;
        }
        
        goToStep(currentStep + 1);
        saveDraft();
    });
    
    $('#btnPrev').on('click', function() {
        goToStep(currentStep - 1);
    });
    
    $('#btnSaveDraft').on('click', function() {
        var payload = {
            name: wizardData.name,
            description: wizardData.description,
            subAccount: wizardData.subAccount,
            emailAddress: wizardData.generatedEmail,
            allowedSenders: wizardData.allowedSenders,
            selectedContacts: wizardData.selectedContacts,
            selectedLists: wizardData.selectedLists,
            selectedDynamicLists: wizardData.selectedDynamicLists,
            selectedTags: wizardData.selectedTags,
            optOutLists: wizardData.optOutLists,
            senderId: wizardData.senderId,
            subjectAsSenderId: wizardData.subjectAsSenderId,
            multipleSms: wizardData.multipleSms,
            deliveryReports: wizardData.deliveryReports,
            deliveryReportEmail: wizardData.deliveryReportEmail,
            contentFilterPatterns: wizardData.contentFilterPatterns,
            status: 'draft'
        };
        
        EmailToSmsService.createEmailToSmsContactListSetup(payload).then(function(response) {
            if (response.success) {
                showSuccessToast('Draft saved successfully');
                window.location.href = '{{ route("management.email-to-sms") }}?tab=contact-lists';
            } else {
                showErrorToast(response.error || 'Failed to save draft');
            }
        }).catch(function(err) {
            console.error('Save draft error:', err);
            showErrorToast('An error occurred. Please try again.');
        });
    });
    
    function createMappingPayload(status) {
        var listNames = [];
        var listIds = [];
        
        wizardData.selectedLists.forEach(function(l) {
            listNames.push(l.name);
            listIds.push(l.id);
        });
        wizardData.selectedDynamicLists.forEach(function(l) {
            listNames.push(l.name + ' (Dynamic)');
            listIds.push(l.id);
        });
        wizardData.selectedContacts.forEach(function(c) {
            listNames.push(c.name);
            listIds.push(c.id);
        });
        wizardData.selectedTags.forEach(function(t) {
            listNames.push('Tag: ' + t.name);
            listIds.push(t.id);
        });
        
        var optOutListNames = wizardData.optOutLists.includes('NO') ? [] : wizardData.optOutLists.map(function(ol) {
            return ol.name || ol;
        });
        var optOutListIds = wizardData.optOutLists.includes('NO') ? [] : wizardData.optOutLists.map(function(ol) {
            return ol.id || ol;
        });
        
        return {
            name: wizardData.name,
            description: wizardData.description,
            subaccountId: wizardData.subAccount,
            emailAddress: wizardData.generatedEmail,
            allowedSenderEmails: wizardData.allowedSenders,
            contactBookListIds: listIds,
            contactBookListNames: listNames,
            optOutMode: wizardData.optOutLists.includes('NO') ? 'NONE' : 'SELECTED',
            optOutListIds: optOutListIds,
            optOutListNames: optOutListNames,
            senderIdTemplateId: wizardData.senderId,
            senderId: wizardData.senderId ? $('#senderId option:selected').text() : 'QuickSMS',
            subjectOverridesSenderId: wizardData.subjectAsSenderId,
            multipleSmsEnabled: wizardData.multipleSms,
            deliveryReportsEnabled: wizardData.deliveryReports,
            deliveryReportsEmail: wizardData.deliveryReportEmail,
            contentFilter: wizardData.contentFilterPatterns,
            status: status
        };
    }
    
    $('#btnCreateMapping').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Creating...');
        
        var payload = createMappingPayload('active');
        
        EmailToSmsService.createEmailToSmsContactListSetup(payload).then(function(response) {
            if (response.success) {
                showSuccessToast('Contact List mapping created successfully!');
                window.location.href = '{{ route("management.email-to-sms") }}?tab=contact-lists&created=1&name=' + encodeURIComponent(wizardData.name);
            } else {
                btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Create Mapping');
                showErrorToast(response.error || 'Failed to create mapping');
            }
        }).catch(function(err) {
            console.error('Create error:', err);
            btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Create Mapping');
            showErrorToast('An error occurred. Please try again.');
        });
    });
    
    $('#btnSaveDraftReview').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...');
        
        var payload = createMappingPayload('draft');
        
        EmailToSmsService.createEmailToSmsContactListSetup(payload).then(function(response) {
            if (response.success) {
                showSuccessToast('Draft saved successfully');
                window.location.href = '{{ route("management.email-to-sms") }}?tab=contact-lists';
            } else {
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save as Draft');
                showErrorToast(response.error || 'Failed to save draft');
            }
        }).catch(function(err) {
            console.error('Save draft error:', err);
            btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save as Draft');
            showErrorToast('An error occurred. Please try again.');
        });
    });
    
    $(document).on('change', '.cb-contact', function() {
        var id = $(this).val();
        var name = $(this).data('name');
        
        if ($(this).is(':checked')) {
            if (!wizardData.selectedContacts.find(c => c.id === id)) {
                wizardData.selectedContacts.push({ id: id, name: name });
            }
        } else {
            wizardData.selectedContacts = wizardData.selectedContacts.filter(c => c.id !== id);
        }
        updateRecipientSummary();
    });
    
    $(document).on('change', '.cb-list', function() {
        var id = $(this).val();
        var name = $(this).data('name');
        var count = $(this).data('count');
        
        if ($(this).is(':checked')) {
            if (!wizardData.selectedLists.find(l => l.id === id)) {
                wizardData.selectedLists.push({ id: id, name: name, count: count });
            }
        } else {
            wizardData.selectedLists = wizardData.selectedLists.filter(l => l.id !== id);
        }
        updateRecipientSummary();
    });
    
    $(document).on('change', '.cb-dynamic', function() {
        var id = $(this).val();
        var name = $(this).data('name');
        var count = $(this).data('count');
        
        if ($(this).is(':checked')) {
            if (!wizardData.selectedDynamicLists.find(l => l.id === id)) {
                wizardData.selectedDynamicLists.push({ id: id, name: name, count: count });
            }
        } else {
            wizardData.selectedDynamicLists = wizardData.selectedDynamicLists.filter(l => l.id !== id);
        }
        updateRecipientSummary();
    });
    
    $(document).on('change', '.cb-tag', function() {
        var id = $(this).val();
        var name = $(this).data('name');
        var count = $(this).data('count');
        
        if ($(this).is(':checked')) {
            if (!wizardData.selectedTags.find(t => t.id === id)) {
                wizardData.selectedTags.push({ id: id, name: name, count: count });
            }
        } else {
            wizardData.selectedTags = wizardData.selectedTags.filter(t => t.id !== id);
        }
        updateRecipientSummary();
    });
    
    $('#cbSelectAllContacts').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.cb-contact').each(function() {
            $(this).prop('checked', isChecked).trigger('change');
        });
    });
    
    $(document).on('click', '#recipientChipsInline .btn-close', function() {
        var type = $(this).data('type');
        var id = $(this).data('id');
        
        switch(type) {
            case 'contact':
                wizardData.selectedContacts = wizardData.selectedContacts.filter(c => c.id != id);
                $('.cb-contact[value="' + id + '"]').prop('checked', false);
                break;
            case 'list':
                wizardData.selectedLists = wizardData.selectedLists.filter(l => l.id != id);
                $('.cb-list[value="' + id + '"]').prop('checked', false);
                break;
            case 'dynamic':
                wizardData.selectedDynamicLists = wizardData.selectedDynamicLists.filter(l => l.id != id);
                $('.cb-dynamic[value="' + id + '"]').prop('checked', false);
                break;
            case 'tag':
                wizardData.selectedTags = wizardData.selectedTags.filter(t => t.id != id);
                $('.cb-tag[value="' + id + '"]').prop('checked', false);
                break;
        }
        updateRecipientSummary();
    });
    
    $('#btnClearAllRecipients').on('click', function() {
        wizardData.selectedContacts = [];
        wizardData.selectedLists = [];
        wizardData.selectedDynamicLists = [];
        wizardData.selectedTags = [];
        $('.cb-contact, .cb-list, .cb-dynamic, .cb-tag').prop('checked', false);
        $('#cbSelectAllContacts').prop('checked', false);
        updateRecipientSummary();
    });
    
    $('#btnToggleContactFilters').on('click', function() {
        $('#cbContactFilters').toggleClass('d-none');
    });
    
    $('#btnClearContactFilters').on('click', function() {
        $('#cbFilterTags').val('');
        $('#cbFilterMobile').val('');
        $('#cbContactFilters').addClass('d-none');
    });
    
    $('#optOutLists').on('change', function() {
        var selected = $(this).val() || [];
        
        if (selected.includes('NO') && selected.length > 1) {
            selected = selected.filter(v => v !== 'NO');
            $(this).val(selected);
        }
        
        if (selected.length === 0) {
            selected = ['NO'];
            $(this).val(selected);
        }
        
        wizardData.optOutLists = selected;
    });
    
    updateRecipientSummary();
});
</script>
@endpush

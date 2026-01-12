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
/* Pastel badge styles */
.badge-pastel-primary { background: rgba(136, 108, 192, 0.15); color: #886CC0; }
.badge-pastel-success { background: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.badge-pastel-info { background: rgba(23, 162, 184, 0.15); color: #17a2b8; }
.badge-pastel-warning { background: rgba(255, 193, 7, 0.2); color: #b38600; }
.badge-pastel-danger { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
.badge-pastel-secondary { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
/* Modal table styling */
.modal-api-table { width: 100%; margin: 0; }
.modal-api-table thead th { background: #f8f9fa; padding: 0.5rem; font-weight: 600; font-size: 0.75rem; color: #495057; border-bottom: 1px solid #e9ecef; }
.modal-api-table tbody td { padding: 0.5rem; vertical-align: middle; border-bottom: 1px solid #f1f3f5; font-size: 0.8rem; }
.modal-api-table tbody td:nth-child(2) { font-weight: 500; color: #343a40; }
.modal-api-table tbody tr:last-child td { border-bottom: none; }
.modal-api-table tbody tr:hover td { background: #f8f9fa; }
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
                                            <label class="form-label">Allowed Sender Emails <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="newSenderEmail" placeholder="e.g., user@domain.com or *@domain.com">
                                                <button class="btn btn-outline-primary" type="button" id="btnAddSender">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="emailError">At least one sender email address is required.</div>
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
                                        
                                        <div class="alert alert-danger small mt-3" id="emailRequiredError" style="display: none;">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            At least one sender email address is required.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-recipients" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-3">
                                            <strong>Step 3: Recipients</strong> – Select recipients from your Contact Book and configure opt-out rules.
                                        </div>
                                        
                                        {{-- Sticky Recipient Summary Bar --}}
                                        <div class="card shadow-sm mb-3" style="position: sticky; top: 0; z-index: 10;">
                                            <div class="card-body py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 13px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-users text-primary"></i>
                                                    <strong>Selected:</strong>
                                                    <span id="recipientChipsInline" class="text-muted">None</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span>Contacts: <strong id="summaryContactsCount">0</strong></span>
                                                    <span>Lists: <strong id="summaryListsCount">0</strong></span>
                                                    <span class="text-muted">Total: <span id="summaryTotalCount">0</span></span>
                                                    <span class="text-warning fw-bold">Removed: <span id="summaryDedupedCount">0</span></span>
                                                    <span class="text-success fw-bold">Unique: <span id="summaryUniqueCount">0</span></span>
                                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" id="btnClearAllRecipients" style="display: none;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            {{-- Left: Contact Book Trigger + Selection Display --}}
                                            <div class="col-lg-8 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-body text-center py-4">
                                                        <i class="fas fa-address-book fa-3x text-primary mb-3"></i>
                                                        <h5 class="mb-2">Contact Book</h5>
                                                        <p class="text-muted small mb-3">Select individual contacts, lists, dynamic lists, or tags</p>
                                                        <button type="button" class="btn btn-outline-primary" id="btnOpenContactBookModal">
                                                            <i class="fas fa-plus me-1"></i> Select from Contact Book
                                                        </button>
                                                        
                                                        {{-- Selection Preview --}}
                                                        <div id="selectionPreview" class="mt-3 text-start d-none">
                                                            <hr>
                                                            <div class="small">
                                                                <div id="selectedContactsPreview" class="mb-2 d-none">
                                                                    <span class="text-muted">Contacts:</span> <span id="selectedContactsList"></span>
                                                                </div>
                                                                <div id="selectedListsPreview" class="mb-2 d-none">
                                                                    <span class="text-muted">Lists:</span> <span id="selectedListsList"></span>
                                                                </div>
                                                                <div id="selectedDynamicPreview" class="mb-2 d-none">
                                                                    <span class="text-muted">Dynamic:</span> <span id="selectedDynamicList"></span>
                                                                </div>
                                                                <div id="selectedTagsPreview" class="d-none">
                                                                    <span class="text-muted">Tags:</span> <span id="selectedTagsList"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- Right: Opt-out Lists Compact Card --}}
                                            <div class="col-lg-4 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-header py-2">
                                                        <h6 class="mb-0 small"><i class="fas fa-ban me-1"></i> Opt-out Lists</h6>
                                                    </div>
                                                    <div class="card-body p-2" style="font-size: 12px;">
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="checkbox" id="optOutNone" checked>
                                                            <label class="form-check-label" for="optOutNone">No opt-out (include all)</label>
                                                        </div>
                                                        <hr class="my-1">
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input opt-out-item" type="checkbox" value="1" id="optOut1">
                                                            <label class="form-check-label" for="optOut1">Global Opt-out <span class="badge badge-pastel-danger">2,345</span></label>
                                                        </div>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input opt-out-item" type="checkbox" value="2" id="optOut2">
                                                            <label class="form-check-label" for="optOut2">Marketing <span class="badge badge-pastel-warning">1,234</span></label>
                                                        </div>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input opt-out-item" type="checkbox" value="3" id="optOut3">
                                                            <label class="form-check-label" for="optOut3">NHS DNC <span class="badge badge-pastel-info">567</span></label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input opt-out-item" type="checkbox" value="4" id="optOut4">
                                                            <label class="form-check-label" for="optOut4">Temporary <span class="badge badge-pastel-secondary">89</span></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-pastel-warning small mt-2 mb-0 d-none" id="invalidRecipientsWarning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <span id="invalidRecipientsText">0 recipients excluded (no valid mobile)</span>
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
                                        <div class="alert alert-pastel-primary mb-3">
                                            <strong>Step 5: Review & Confirm</strong> – Please review your mapping configuration before creating.
                                        </div>
                                        
                                        {{-- Generated Email Address - Compact Display --}}
                                        <div class="bg-success bg-opacity-10 border border-success rounded p-3 mb-3 text-center">
                                            <div class="small text-success fw-medium mb-1"><i class="fas fa-envelope me-1"></i> Your Generated Email Address</div>
                                            <code class="d-block fs-5 mb-2" id="generatedEmailDisplay">Generating...</code>
                                            <button class="btn btn-outline-success btn-sm" id="btnCopyEmail">
                                                <i class="fas fa-copy me-1"></i> Copy
                                            </button>
                                        </div>
                                        
                                        <div class="row">
                                            {{-- Left Column: All Summary Tables --}}
                                            <div class="col-lg-8">
                                                <div class="card mb-3">
                                                    <div class="card-body p-0">
                                                        <table class="table table-sm mb-0" style="font-size: 13px;">
                                                            <thead class="table-light">
                                                                <tr><th colspan="2"><i class="fas fa-cog me-2"></i>Configuration</th></tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-muted" style="width: 35%;">Mapping Name</td>
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
                                                                <tr>
                                                                    <td class="text-muted">Email Address</td>
                                                                    <td class="text-primary" id="summaryEmail">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Allowed Senders</td>
                                                                    <td id="summarySenders">-</td>
                                                                </tr>
                                                            </tbody>
                                                            <thead class="table-light">
                                                                <tr><th colspan="2"><i class="fas fa-users me-2"></i>Recipients</th></tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-muted">Selected From</td>
                                                                    <td id="summaryContactList">-</td>
                                                                </tr>
                                                                <tr class="table-success">
                                                                    <td class="text-muted">Total Recipients</td>
                                                                    <td class="fw-bold" id="summaryRecipients">-</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Opt-out Lists</td>
                                                                    <td id="summaryOptOut">-</td>
                                                                </tr>
                                                            </tbody>
                                                            <thead class="table-light">
                                                                <tr><th colspan="2"><i class="fas fa-sms me-2"></i>Message Settings</th></tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-muted">SenderID</td>
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
                                            
                                            {{-- Right Column: Actions + How It Works (collapsible) --}}
                                            <div class="col-lg-4">
                                                <div class="d-grid gap-2 mb-3">
                                                    <button type="button" class="btn btn-primary" id="btnCreateMapping">
                                                        <i class="fas fa-check-circle me-1"></i> Create Mapping
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSaveDraftReview">
                                                        <i class="fas fa-save me-1"></i> Save as Draft
                                                    </button>
                                                </div>
                                                
                                                <div class="alert alert-pastel-warning small py-2 mb-3">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Email address cannot be changed after creation.
                                                </div>
                                                
                                                <div class="card mb-3 border-primary">
                                                    <div class="card-header py-2 bg-primary text-white" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#howItWorksCollapse" aria-expanded="false" aria-controls="howItWorksCollapse">
                                                        <h6 class="mb-0 small d-flex justify-content-between align-items-center">
                                                            <span><i class="fas fa-info-circle me-1"></i>How It Works</span>
                                                            <i class="fas fa-chevron-down small" id="howItWorksIcon"></i>
                                                        </h6>
                                                    </div>
                                                    <div class="collapse" id="howItWorksCollapse">
                                                        <div class="card-body py-2 px-3" style="font-size: 12px;">
                                                            <ol class="mb-0 ps-3">
                                                                <li class="mb-1">Email to <strong id="summaryEmailInline" class="text-break">-</strong></li>
                                                                <li class="mb-1">SenderID from subject</li>
                                                                <li class="mb-1">SMS from email body</li>
                                                                <li class="mb-0">Sent to <strong id="summaryRecipientCountInline">0</strong> recipients</li>
                                                            </ol>
                                                        </div>
                                                    </div>
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

{{-- Contact Book Selection Modal --}}
<div class="modal fade" id="contactBookModal" tabindex="-1" aria-labelledby="contactBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 bg-primary text-white">
                <h5 class="modal-title" id="contactBookModalLabel">
                    <i class="fas fa-address-book me-2"></i>Select from Contact Book
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                {{-- Modal Selection Summary --}}
                <div class="bg-light rounded p-2 mb-3" style="font-size: 13px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><strong>Current Selection:</strong> <span id="modalSelectionSummary">None</span></span>
                        <button type="button" class="btn btn-link btn-sm text-danger p-0" id="btnModalClearAll">
                            <i class="fas fa-times me-1"></i> Clear All
                        </button>
                    </div>
                </div>
                
                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" id="modalRecipientTabs">
                    <li class="nav-item">
                        <button class="nav-link active py-2 px-3" data-bs-toggle="tab" data-bs-target="#modalContactsTab">
                            <i class="fas fa-user me-1"></i> Contacts <span class="badge bg-secondary ms-1" id="modalContactsBadge">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2 px-3" data-bs-toggle="tab" data-bs-target="#modalListsTab">
                            <i class="fas fa-list me-1"></i> Lists <span class="badge bg-secondary ms-1" id="modalListsBadge">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2 px-3" data-bs-toggle="tab" data-bs-target="#modalDynamicListsTab">
                            <i class="fas fa-sync-alt me-1"></i> Dynamic <span class="badge bg-secondary ms-1" id="modalDynamicBadge">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2 px-3" data-bs-toggle="tab" data-bs-target="#modalTagsTab">
                            <i class="fas fa-tags me-1"></i> Tags <span class="badge bg-secondary ms-1" id="modalTagsBadge">0</span>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    {{-- Contacts Tab --}}
                    <div class="tab-pane fade show active" id="modalContactsTab">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control modal-cb-search" placeholder="Search names, numbers, tags...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select form-select-sm modal-cb-sort">
                                        <option value="recent">Most recently contacted</option>
                                        <option value="added">Most recently added</option>
                                        <option value="name_asc">Name A-Z</option>
                                        <option value="name_desc">Name Z-A</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="modalBtnToggleFilters">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-none mb-2 p-2 bg-light rounded" id="modalContactFilters" style="font-size: 12px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Tags</label>
                                    <select class="form-select form-select-sm modal-filter-tags">
                                        <option value="">All tags</option>
                                        <option value="vip">VIP</option>
                                        <option value="asthma">Asthma</option>
                                        <option value="diabetes">Diabetes</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Has Mobile</label>
                                    <select class="form-select form-select-sm modal-filter-mobile">
                                        <option value="">Any</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-link btn-sm" id="modalBtnClearFilters">Clear filters</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table modal-api-table mb-0">
                                <thead class="sticky-top">
                                    <tr>
                                        <th style="width: 30px;"><input type="checkbox" class="form-check-input" id="modalSelectAllContacts"></th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Tags</th>
                                    </tr>
                                </thead>
                                <tbody id="modalContactsTable">
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="1" data-name="John Smith"></td><td>John Smith</td><td>+44 7700***123</td><td><span class="badge badge-pastel-info">VIP</span></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="2" data-name="Jane Doe"></td><td>Jane Doe</td><td>+44 7700***456</td><td><span class="badge badge-pastel-success">Asthma</span></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="3" data-name="Robert Brown"></td><td>Robert Brown</td><td>+44 7700***789</td><td></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="4" data-name="Sarah Wilson"></td><td>Sarah Wilson</td><td>+44 7700***012</td><td><span class="badge badge-pastel-warning">Diabetes</span></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="5" data-name="Michael Johnson"></td><td>Michael Johnson</td><td>+44 7700***345</td><td><span class="badge badge-pastel-info">VIP</span></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="6" data-name="Emily Davis"></td><td>Emily Davis</td><td>+44 7700***678</td><td><span class="badge badge-pastel-success">Asthma</span></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="7" data-name="David Miller"></td><td>David Miller</td><td>+44 7700***901</td><td></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="8" data-name="Lisa Anderson"></td><td>Lisa Anderson</td><td>+44 7700***234</td><td><span class="badge badge-pastel-warning">Diabetes</span> <span class="badge badge-pastel-danger">Hypertension</span></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    {{-- Static Lists Tab --}}
                    <div class="tab-pane fade" id="modalListsTab">
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control modal-list-search" placeholder="Search lists...">
                        </div>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table modal-api-table mb-0">
                                <thead class="sticky-top">
                                    <tr><th style="width: 30px;"></th><th>List Name</th><th>Contacts</th><th>Last Updated</th></tr>
                                </thead>
                                <tbody id="modalListsTable">
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
                    <div class="tab-pane fade" id="modalDynamicListsTab">
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control modal-dynamic-search" placeholder="Search dynamic lists...">
                        </div>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table modal-api-table mb-0">
                                <thead class="sticky-top">
                                    <tr><th style="width: 30px;"></th><th>List Name</th><th>Rules</th><th>Contacts</th><th>Last Evaluated</th></tr>
                                </thead>
                                <tbody id="modalDynamicListsTable">
                                    <tr><td><input type="checkbox" class="form-check-input cb-dynamic" value="1" data-name="Over 65s" data-count="2345"></td><td>Over 65s</td><td>Age > 65</td><td>2,345</td><td>22-Dec-2025</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-dynamic" value="2" data-name="Local Postcodes" data-count="1890"></td><td>Local Postcodes</td><td>Postcode starts with SW</td><td>1,890</td><td>22-Dec-2025</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-dynamic" value="3" data-name="Active Last 30 Days" data-count="4500"></td><td>Active Last 30 Days</td><td>Last contact < 30 days</td><td>4,500</td><td>22-Dec-2025</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    {{-- Tags Tab --}}
                    <div class="tab-pane fade" id="modalTagsTab">
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control modal-tag-search" placeholder="Search tags...">
                        </div>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table modal-api-table mb-0">
                                <thead class="sticky-top">
                                    <tr><th style="width: 30px;"></th><th>Tag</th><th>Contacts</th></tr>
                                </thead>
                                <tbody id="modalTagsTable">
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="1" data-name="VIP" data-count="456"></td><td><span class="badge badge-pastel-info">VIP</span></td><td>456</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="2" data-name="Asthma" data-count="1234"></td><td><span class="badge badge-pastel-success">Asthma</span></td><td>1,234</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="3" data-name="Diabetes" data-count="890"></td><td><span class="badge badge-pastel-warning">Diabetes</span></td><td>890</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="4" data-name="Hypertension" data-count="567"></td><td><span class="badge badge-pastel-danger">Hypertension</span></td><td>567</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="5" data-name="Chronic Care" data-count="1023"></td><td><span class="badge badge-pastel-primary">Chronic Care</span></td><td>1,023</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnApplyContactSelection">
                    <i class="fas fa-check me-1"></i> Apply Selection
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="fas fa-check-circle me-2"></i> Email-to-SMS Address Created
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-envelope-open-text text-success" style="font-size: 3rem;"></i>
                </div>
                <p class="mb-3">Your Email-to-SMS mapping has been created successfully. Send emails to the address below to trigger SMS messages:</p>
                <div class="bg-light border rounded p-3 mb-3">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <code class="fs-5 text-primary" id="successEmailAddress">-</code>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnCopySuccessEmail" title="Copy to clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-info-circle me-1"></i> 
                    <strong>SenderID:</strong> Extracted from email subject<br>
                    <strong>SMS Content:</strong> Extracted from email body
                </div>
            </div>
            <div class="modal-footer justify-content-center py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('management.email-to-sms') }}?tab=contact-lists" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Contact List Library
                </a>
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
        $('#summaryRecipients').text(stats.uniqueAfterDedup > 0 ? stats.uniqueAfterDedup.toLocaleString() + ' recipients' : '-');
        $('#summaryRecipientCountInline').text(stats.uniqueAfterDedup.toLocaleString());
        $('#summarySenderId').text(wizardData.senderId ? $('#senderId option:selected').text() : '-');
        
        if (wizardData.optOutLists.includes('NO')) {
            $('#summaryOptOut').html('<span class="text-muted">None applied</span>');
        } else {
            var optOutChips = '';
            var optOutColors = ['badge-pastel-danger', 'badge-pastel-warning', 'badge-pastel-info', 'badge-pastel-secondary'];
            wizardData.optOutLists.forEach(function(listId, index) {
                var listName = $('#optOut' + listId).next('label').text().split(' ')[0] || 'List ' + listId;
                var colorClass = optOutColors[index % optOutColors.length];
                optOutChips += '<span class="badge ' + colorClass + ' me-1">' + listName + '</span>';
            });
            $('#summaryOptOut').html(optOutChips);
        }
        
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
        // Individual contacts are already unique, so no deduplication needed for them
        // Only lists/dynamic lists/tags might have overlaps - apply 5% dedup estimate only to list-based counts
        var listBasedCount = listCount + dynamicCount + tagCount;
        var listDedupedRemoved = listBasedCount > 0 ? Math.floor(listBasedCount * 0.05) : 0;
        var uniqueAfterDedup = total - listDedupedRemoved;
        var invalid = listBasedCount > 0 ? Math.floor(listBasedCount * 0.02) : 0;
        
        return {
            contacts: contactCount,
            lists: listCount,
            dynamic: dynamicCount,
            tags: tagCount,
            total: total,
            dedupedRemoved: listDedupedRemoved,
            uniqueAfterDedup: uniqueAfterDedup,
            invalid: invalid
        };
    }
    
    function updateRecipientSummary() {
        var stats = calculateRecipientStats();
        
        $('#summaryContactsCount').text(wizardData.selectedContacts.length);
        $('#summaryListsCount').text(wizardData.selectedLists.length + wizardData.selectedDynamicLists.length + wizardData.selectedTags.length);
        $('#summaryTotalCount').text(stats.total.toLocaleString());
        $('#summaryDedupedCount').text(stats.dedupedRemoved.toLocaleString());
        $('#summaryUniqueCount').text(stats.uniqueAfterDedup.toLocaleString());
        
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
            html += '<span class="badge badge-pastel-secondary me-1 mb-1"><i class="fas fa-user me-1"></i>' + contact.name + 
                    '<button type="button" class="btn-close btn-close-sm ms-1" style="font-size: 0.5rem;" data-type="contact" data-id="' + contact.id + '"></button></span>';
        });
        
        wizardData.selectedLists.forEach(function(list) {
            html += '<span class="badge badge-pastel-primary me-1 mb-1"><i class="fas fa-list me-1"></i>' + list.name + ' (' + parseInt(list.count).toLocaleString() + ')' +
                    '<button type="button" class="btn-close btn-close-sm ms-1" style="font-size: 0.5rem;" data-type="list" data-id="' + list.id + '"></button></span>';
        });
        
        wizardData.selectedDynamicLists.forEach(function(list) {
            html += '<span class="badge badge-pastel-info me-1 mb-1"><i class="fas fa-sync-alt me-1"></i>' + list.name + ' (' + parseInt(list.count).toLocaleString() + ')' +
                    '<button type="button" class="btn-close btn-close-sm ms-1" style="font-size: 0.5rem;" data-type="dynamic" data-id="' + list.id + '"></button></span>';
        });
        
        wizardData.selectedTags.forEach(function(tag) {
            html += '<span class="badge badge-pastel-success me-1 mb-1"><i class="fas fa-tag me-1"></i>' + tag.name + ' (' + parseInt(tag.count).toLocaleString() + ')' +
                    '<button type="button" class="btn-close btn-close-sm ms-1" style="font-size: 0.5rem;" data-type="tag" data-id="' + tag.id + '"></button></span>';
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
            case 1:
                if (wizardData.allowedSenders.length === 0) {
                    $('#emailRequiredError').show();
                    $('#newSenderEmail').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#emailRequiredError').hide();
                    $('#newSenderEmail').removeClass('is-invalid');
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
        $('#emailRequiredError').hide();
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
                $('#successEmailAddress').text(wizardData.generatedEmail);
                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Create Mapping');
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
    
    $('#btnCopySuccessEmail').on('click', function() {
        var email = $('#successEmailAddress').text();
        navigator.clipboard.writeText(email).then(function() {
            showSuccessToast('Email address copied to clipboard!');
            $('#btnCopySuccessEmail').html('<i class="fas fa-check"></i>');
            setTimeout(function() {
                $('#btnCopySuccessEmail').html('<i class="fas fa-copy"></i>');
            }, 2000);
        }).catch(function() {
            showErrorToast('Failed to copy. Please select and copy manually.');
        });
    });
    
    $('#howItWorksCollapse').on('show.bs.collapse', function() {
        $('#howItWorksIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    }).on('hide.bs.collapse', function() {
        $('#howItWorksIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
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
    
    // Old select all handler removed - replaced by modal handler below
    
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
        $('#modalSelectAllContacts').prop('checked', false);
        updateRecipientSummary();
        updateSelectionPreview();
    });
    
    $('#btnToggleContactFilters, #modalBtnToggleFilters').on('click', function() {
        $('#modalContactFilters').toggleClass('d-none');
    });
    
    $('#btnClearContactFilters, #modalBtnClearFilters').on('click', function() {
        $('.modal-filter-tags').val('');
        $('.modal-filter-mobile').val('');
        $('#modalContactFilters').addClass('d-none');
    });
    
    // Modal select all contacts
    $('#modalSelectAllContacts').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#modalContactsTable .cb-contact').each(function() {
            $(this).prop('checked', isChecked).trigger('change');
        });
    });
    
    // Contact Book Modal handlers
    $('#btnOpenContactBookModal').on('click', function() {
        updateModalSelectionBadges();
        var modal = new bootstrap.Modal(document.getElementById('contactBookModal'));
        modal.show();
    });
    
    $('#btnApplyContactSelection').on('click', function() {
        updateRecipientSummary();
        updateSelectionPreview();
        bootstrap.Modal.getInstance(document.getElementById('contactBookModal')).hide();
    });
    
    $('#btnModalClearAll').on('click', function() {
        wizardData.selectedContacts = [];
        wizardData.selectedLists = [];
        wizardData.selectedDynamicLists = [];
        wizardData.selectedTags = [];
        $('.cb-contact, .cb-list, .cb-dynamic, .cb-tag').prop('checked', false);
        $('#modalSelectAllContacts').prop('checked', false);
        updateModalSelectionBadges();
    });
    
    function updateModalSelectionBadges() {
        var contactCount = wizardData.selectedContacts.length;
        var listCount = wizardData.selectedLists.length;
        var dynamicCount = wizardData.selectedDynamicLists.length;
        var tagCount = wizardData.selectedTags.length;
        
        $('#modalContactsBadge').text(contactCount).toggleClass('bg-primary', contactCount > 0).toggleClass('bg-secondary', contactCount === 0);
        $('#modalListsBadge').text(listCount).toggleClass('bg-primary', listCount > 0).toggleClass('bg-secondary', listCount === 0);
        $('#modalDynamicBadge').text(dynamicCount).toggleClass('bg-primary', dynamicCount > 0).toggleClass('bg-secondary', dynamicCount === 0);
        $('#modalTagsBadge').text(tagCount).toggleClass('bg-primary', tagCount > 0).toggleClass('bg-secondary', tagCount === 0);
        
        var total = contactCount + listCount + dynamicCount + tagCount;
        $('#modalSelectionSummary').text(total > 0 ? total + ' items selected' : 'None');
    }
    
    function updateSelectionPreview() {
        var hasContacts = wizardData.selectedContacts.length > 0;
        var hasLists = wizardData.selectedLists.length > 0;
        var hasDynamic = wizardData.selectedDynamicLists.length > 0;
        var hasTags = wizardData.selectedTags.length > 0;
        var hasAny = hasContacts || hasLists || hasDynamic || hasTags;
        
        $('#selectionPreview').toggleClass('d-none', !hasAny);
        
        $('#selectedContactsPreview').toggleClass('d-none', !hasContacts);
        if (hasContacts) {
            var names = wizardData.selectedContacts.slice(0, 3).map(c => c.name).join(', ');
            if (wizardData.selectedContacts.length > 3) names += ' +' + (wizardData.selectedContacts.length - 3) + ' more';
            $('#selectedContactsList').text(names);
        }
        
        $('#selectedListsPreview').toggleClass('d-none', !hasLists);
        if (hasLists) {
            var names = wizardData.selectedLists.slice(0, 2).map(l => l.name).join(', ');
            if (wizardData.selectedLists.length > 2) names += ' +' + (wizardData.selectedLists.length - 2) + ' more';
            $('#selectedListsList').text(names);
        }
        
        $('#selectedDynamicPreview').toggleClass('d-none', !hasDynamic);
        if (hasDynamic) {
            var names = wizardData.selectedDynamicLists.slice(0, 2).map(l => l.name).join(', ');
            if (wizardData.selectedDynamicLists.length > 2) names += ' +' + (wizardData.selectedDynamicLists.length - 2) + ' more';
            $('#selectedDynamicList').text(names);
        }
        
        $('#selectedTagsPreview').toggleClass('d-none', !hasTags);
        if (hasTags) {
            var names = wizardData.selectedTags.slice(0, 3).map(t => t.name).join(', ');
            if (wizardData.selectedTags.length > 3) names += ' +' + (wizardData.selectedTags.length - 3) + ' more';
            $('#selectedTagsList').text(names);
        }
        
        // Update button text based on selection
        var btnText = hasAny ? '<i class="fas fa-edit me-1"></i> Edit Selection' : '<i class="fas fa-plus me-1"></i> Select from Contact Book';
        $('#btnOpenContactBookModal').html(btnText);
    }
    
    // Opt-out list checkbox handlers
    $('#optOutNone').on('change', function() {
        if ($(this).is(':checked')) {
            $('.opt-out-item').prop('checked', false);
            wizardData.optOutLists = ['NO'];
        }
    });
    
    $('.opt-out-item').on('change', function() {
        var anyChecked = $('.opt-out-item:checked').length > 0;
        if (anyChecked) {
            $('#optOutNone').prop('checked', false);
            wizardData.optOutLists = $('.opt-out-item:checked').map(function() { return $(this).val(); }).get();
        } else {
            $('#optOutNone').prop('checked', true);
            wizardData.optOutLists = ['NO'];
        }
    });
    
    // Update badges when selections change in modal
    $(document).on('change', '.cb-contact, .cb-list, .cb-dynamic, .cb-tag', function() {
        updateModalSelectionBadges();
    });
    
    updateRecipientSummary();
    updateSelectionPreview();
});
</script>
@endpush

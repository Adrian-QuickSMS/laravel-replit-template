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
.sender-email-tag {
    display: inline-flex;
    align-items: center;
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    margin: 0.25rem;
    font-size: 0.85rem;
}
.sender-email-tag .remove-tag {
    margin-left: 0.5rem;
    cursor: pointer;
    color: #6c757d;
}
.sender-email-tag .remove-tag:hover {
    color: #dc3545;
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
                                            <strong>Step 2: Email Settings</strong> – Configure allowed sender emails and view your generated inbound address.
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label">Generated Email Address</label>
                                            <div class="generated-email">
                                                <code id="generatedEmailDisplay">Enter mapping name to generate...</code>
                                                <div>
                                                    <button class="btn btn-outline-primary btn-sm" id="btnCopyEmail" disabled>
                                                        <i class="fas fa-copy me-1"></i> Copy to Clipboard
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-muted">This unique address is auto-generated based on your mapping name.</small>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Allowed Sender Emails (Optional)</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control" id="newSenderEmail" placeholder="Enter email address...">
                                                <button class="btn btn-outline-primary" type="button" id="btnAddSender">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </div>
                                            <small class="text-muted">Restrict which email addresses can trigger this mapping. Leave empty to allow any sender.</small>
                                        </div>
                                        
                                        <div id="senderEmailTags" class="mb-3">
                                            <span class="text-muted">No sender restrictions - any email can trigger this mapping</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-recipients" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 3: Recipients (Contact Book Access)</strong> – Select the Contact Book List that will receive SMS messages.
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" id="searchContactList" placeholder="Search Contact Lists...">
                                            </div>
                                        </div>
                                        
                                        <div id="contactListOptions">
                                        </div>
                                        
                                        <input type="hidden" id="selectedContactListId" value="">
                                        <input type="hidden" id="selectedContactListName" value="">
                                        <input type="hidden" id="selectedContactListCount" value="">
                                        
                                        <div class="invalid-feedback" id="contactListError" style="display: none;">
                                            Please select a Contact List to continue.
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
                                        
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label class="form-label">SenderID <span class="text-danger">*</span></label>
                                                <select class="form-select" id="senderId">
                                                    <option value="">Select SenderID...</option>
                                                    <option value="QuickSMS">QuickSMS</option>
                                                    <option value="NHSTrust">NHSTrust</option>
                                                    <option value="Pharmacy">Pharmacy</option>
                                                    <option value="Clinic">Clinic</option>
                                                </select>
                                                <small class="text-muted">The SenderID that will appear on SMS messages.</small>
                                                <div class="invalid-feedback">Please select a SenderID.</div>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="subjectAsSenderId">
                                                    <label class="form-check-label" for="subjectAsSenderId">Use Email Subject as SenderID</label>
                                                </div>
                                                <small class="text-muted">Override SenderID with email subject line content.</small>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-3">
                                        
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="multipleSms" checked>
                                                    <label class="form-check-label" for="multipleSms">Allow Multiple SMS</label>
                                                </div>
                                                <small class="text-muted">Allow messages longer than 160 characters to be split.</small>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="deliveryReports" checked>
                                                    <label class="form-check-label" for="deliveryReports">Delivery Reports</label>
                                                </div>
                                                <small class="text-muted">Receive delivery status notifications.</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="contentFilter" checked>
                                                    <label class="form-check-label" for="contentFilter">Content Filter</label>
                                                </div>
                                                <small class="text-muted">Apply content filtering and signature removal.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-review" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 5: Review & Confirm</strong> – Please review your mapping configuration before creating.
                                        </div>
                                        
                                        <div class="summary-card">
                                            <div class="summary-row">
                                                <span class="summary-label">Mapping Name</span>
                                                <span class="summary-value" id="summaryName">-</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Sub-Account</span>
                                                <span class="summary-value" id="summarySubAccount">-</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Email-to-SMS Address</span>
                                                <span class="summary-value" id="summaryEmail">-</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Allowed Senders</span>
                                                <span class="summary-value" id="summarySenders">-</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Contact List</span>
                                                <span class="summary-value" id="summaryContactList">-</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Recipients</span>
                                                <span class="summary-value" id="summaryRecipients">-</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">SenderID</span>
                                                <span class="summary-value" id="summarySenderId">-</span>
                                            </div>
                                            <div class="summary-row">
                                                <span class="summary-label">Message Settings</span>
                                                <span class="summary-value" id="summaryMessageSettings">-</span>
                                            </div>
                                        </div>
                                        
                                        <div class="rules-box">
                                            <h6><i class="fas fa-info-circle me-2"></i>How It Works</h6>
                                            <ul>
                                                <li><strong>SenderID</strong> is extracted from the <strong>EMAIL SUBJECT</strong> line (if enabled)</li>
                                                <li><strong>SMS content</strong> is extracted from the <strong>EMAIL BODY</strong></li>
                                                <li>All recipients in the selected Contact List will receive the SMS</li>
                                                <li>Only whitelisted sender emails can trigger this mapping (if configured)</li>
                                            </ul>
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
    var mockContactLists = [
        { id: 'cl-001', name: 'NHS Patients', count: 4521 },
        { id: 'cl-002', name: 'Pharmacy Patients', count: 1892 },
        { id: 'cl-003', name: 'Appointment List', count: 3267 },
        { id: 'cl-004', name: 'Newsletter Subscribers', count: 8934 },
        { id: 'cl-005', name: 'Emergency Contacts', count: 156 },
        { id: 'cl-006', name: 'VIP Customers', count: 342 },
        { id: 'cl-007', name: 'Staff Members', count: 89 },
        { id: 'cl-008', name: 'Suppliers', count: 45 }
    ];
    
    var wizardData = {
        name: '',
        description: '',
        subAccount: '',
        allowedSenders: [],
        generatedEmail: '',
        contactListId: '',
        contactListName: '',
        contactListCount: 0,
        senderId: '',
        subjectAsSenderId: false,
        multipleSms: true,
        deliveryReports: true,
        contentFilter: true
    };
    
    var currentStep = 0;
    var totalSteps = 5;
    
    function renderContactLists(lists) {
        var html = '';
        lists.forEach(function(list) {
            var isSelected = wizardData.contactListId === list.id;
            html += '<div class="contact-list-option d-flex justify-content-between align-items-center' + (isSelected ? ' selected' : '') + '" data-id="' + list.id + '" data-name="' + list.name + '" data-count="' + list.count + '">' +
                '<div>' +
                    '<div class="list-name">' + list.name + '</div>' +
                    '<div class="list-count"><i class="fas fa-users me-1"></i> ' + list.count.toLocaleString() + ' recipients</div>' +
                '</div>' +
                '<div class="list-check"><i class="fas fa-check-circle"></i></div>' +
            '</div>';
        });
        $('#contactListOptions').html(html);
    }
    
    function renderSenderTags() {
        if (wizardData.allowedSenders.length === 0) {
            $('#senderEmailTags').html('<span class="text-muted">No sender restrictions - any email can trigger this mapping</span>');
            return;
        }
        var html = '';
        wizardData.allowedSenders.forEach(function(email, index) {
            html += '<span class="sender-email-tag">' + email + '<span class="remove-tag" data-index="' + index + '">&times;</span></span>';
        });
        $('#senderEmailTags').html(html);
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
        $('#summarySubAccount').text(wizardData.subAccount ? $('#subAccount option:selected').text() : '-');
        $('#summaryEmail').text(wizardData.generatedEmail || '-');
        $('#summarySenders').text(wizardData.allowedSenders.length > 0 ? wizardData.allowedSenders.length + ' whitelisted' : 'Any sender allowed');
        $('#summaryContactList').text(wizardData.contactListName || '-');
        $('#summaryRecipients').text(wizardData.contactListCount > 0 ? wizardData.contactListCount.toLocaleString() + ' contacts' : '-');
        $('#summarySenderId').text(wizardData.senderId || '-');
        
        var settings = [];
        if (wizardData.multipleSms) settings.push('Multiple SMS');
        if (wizardData.deliveryReports) settings.push('Delivery Reports');
        if (wizardData.contentFilter) settings.push('Content Filter');
        if (wizardData.subjectAsSenderId) settings.push('Subject as SenderID');
        $('#summaryMessageSettings').text(settings.length > 0 ? settings.join(', ') : 'Default');
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
                if (!wizardData.contactListId) {
                    $('#contactListError').show();
                    isValid = false;
                } else {
                    $('#contactListError').hide();
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
        
        if (stepIndex === 1 && wizardData.name && !wizardData.generatedEmail) {
            generateEmailAddress();
        }
        
        if (stepIndex === totalSteps - 1) {
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
            $('#btnNext').html('<i class="fas fa-check me-1"></i> Create');
        } else {
            $('#btnNext').html('Next <i class="fas fa-arrow-right ms-1"></i>');
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
                canProceed = wizardData.contactListId !== '';
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
    
    renderContactLists(mockContactLists);
    renderSenderTags();
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
        updateNextButtonState();
    });
    
    $('#senderId').on('change', function() {
        wizardData.senderId = $(this).val();
        $(this).removeClass('is-invalid');
        updateNextButtonState();
    });
    
    $('#subjectAsSenderId').on('change', function() {
        wizardData.subjectAsSenderId = $(this).is(':checked');
    });
    
    $('#multipleSms').on('change', function() {
        wizardData.multipleSms = $(this).is(':checked');
    });
    
    $('#deliveryReports').on('change', function() {
        wizardData.deliveryReports = $(this).is(':checked');
    });
    
    $('#contentFilter').on('change', function() {
        wizardData.contentFilter = $(this).is(':checked');
    });
    
    $('#searchContactList').on('input', function() {
        var term = $(this).val().toLowerCase();
        var filtered = mockContactLists.filter(function(list) {
            return list.name.toLowerCase().indexOf(term) !== -1;
        });
        renderContactLists(filtered);
    });
    
    $(document).on('click', '.contact-list-option', function() {
        $('.contact-list-option').removeClass('selected');
        $(this).addClass('selected');
        wizardData.contactListId = $(this).data('id');
        wizardData.contactListName = $(this).data('name');
        wizardData.contactListCount = $(this).data('count');
        $('#contactListError').hide();
        updateNextButtonState();
    });
    
    function addSenderEmail() {
        var email = $('#newSenderEmail').val().trim();
        if (!email) return;
        
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $('#newSenderEmail').addClass('is-invalid');
            return;
        }
        
        if (wizardData.allowedSenders.indexOf(email) !== -1) {
            return;
        }
        
        wizardData.allowedSenders.push(email);
        $('#newSenderEmail').val('').removeClass('is-invalid');
        renderSenderTags();
    }
    
    $('#btnAddSender').on('click', addSenderEmail);
    $('#newSenderEmail').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            addSenderEmail();
        }
    });
    
    $(document).on('click', '.remove-tag', function() {
        var index = $(this).data('index');
        wizardData.allowedSenders.splice(index, 1);
        renderSenderTags();
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
            var payload = {
                name: wizardData.name,
                description: wizardData.description,
                subAccount: wizardData.subAccount,
                emailAddress: wizardData.generatedEmail,
                allowedSenders: wizardData.allowedSenders,
                contactListId: wizardData.contactListId,
                contactListName: wizardData.contactListName,
                senderId: wizardData.senderId,
                subjectAsSenderId: wizardData.subjectAsSenderId,
                multipleSms: wizardData.multipleSms,
                deliveryReports: wizardData.deliveryReports,
                contentFilter: wizardData.contentFilter,
                status: 'active'
            };
            
            EmailToSmsService.createEmailToSmsContactListSetup(payload).then(function(response) {
                if (response.success) {
                    showSuccessToast('Contact List mapping created successfully');
                    window.location.href = '{{ route("management.email-to-sms") }}?tab=contact-lists&created=1';
                } else {
                    showErrorToast(response.error || 'Failed to create mapping');
                }
            }).catch(function(err) {
                console.error('Create error:', err);
                showErrorToast('An error occurred. Please try again.');
            });
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
            contactListId: wizardData.contactListId,
            contactListName: wizardData.contactListName,
            senderId: wizardData.senderId,
            subjectAsSenderId: wizardData.subjectAsSenderId,
            multipleSms: wizardData.multipleSms,
            deliveryReports: wizardData.deliveryReports,
            contentFilter: wizardData.contentFilter,
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
});
</script>
@endpush

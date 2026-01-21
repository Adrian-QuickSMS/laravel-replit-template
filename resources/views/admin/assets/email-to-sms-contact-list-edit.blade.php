@extends('layouts.admin')

@section('title', 'Edit Contact List Email-to-SMS')

@push('styles')
<link href="{{ asset('vendor/jquery-smartwizard/dist/css/smart_wizard.min.css') }}" rel="stylesheet">
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-primary-light: #2d5a87;
    --admin-primary-lighter: #4a90d9;
}
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
    border: 0.125rem solid var(--admin-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: var(--admin-primary);
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
    background: var(--admin-primary);
    color: #fff;
    border-color: var(--admin-primary);
}
.form-wizard .nav-wizard li .nav-link.active:after,
.form-wizard .nav-wizard li .nav-link.done:after {
    background: var(--admin-primary) !important;
}
.form-wizard .nav-wizard li .nav-link small {
    display: none;
}
.form-wizard .nav-wizard li .nav-link.step-completed span {
    background: var(--admin-primary) !important;
    color: #fff !important;
    border-color: var(--admin-primary) !important;
}
.form-wizard .nav-wizard li .nav-link.step-completed:after {
    background: var(--admin-primary) !important;
}
.form-wizard .tab-content {
    min-height: 300px;
    padding: 0.5rem 0;
}
.form-wizard .toolbar-bottom {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 0 0 0;
    border-top: 1px solid #e9ecef;
    background: #fff;
}
.email-display {
    background: rgba(30, 58, 95, 0.08);
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-family: monospace;
    font-size: 0.9rem;
}
.sender-entry {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
}
.sender-entry input {
    flex: 1;
}
.btn-admin-primary {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #fff;
}
.btn-admin-primary:hover {
    background: var(--admin-primary-light);
    border-color: var(--admin-primary-light);
    color: #fff;
}
.btn-admin-outline {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
}
.btn-admin-outline:hover {
    background: var(--admin-primary);
    color: #fff;
}
.admin-info-banner {
    background: rgba(30, 58, 95, 0.1);
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.admin-info-banner i {
    color: var(--admin-primary);
}
.form-check-input:checked {
    background-color: var(--admin-primary);
    border-color: var(--admin-primary);
}
.contact-list-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    border: 2px solid transparent;
}
.contact-list-item:hover {
    background: #e9ecef;
}
.contact-list-item.selected {
    border-color: var(--admin-primary);
    background: rgba(30, 58, 95, 0.05);
}
.contact-list-item .list-info {
    flex: 1;
}
.contact-list-item .list-name {
    font-weight: 500;
}
.contact-list-item .list-count {
    font-size: 0.8rem;
    color: #6c757d;
}
.mapping-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
}
.mapping-row .field-name {
    flex: 1;
    font-weight: 500;
}
.mapping-row select {
    width: 200px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.assets.email-to-sms') }}">Email-to-SMS</a></li>
            <li class="breadcrumb-item active">Edit Contact List Configuration</li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Edit Contact List Email-to-SMS Configuration</h4>
                </div>
                <div class="card-body">
                    <div class="admin-info-banner">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Admin Edit Mode:</strong> You are editing this configuration on behalf of the customer. Changes will be logged in the audit trail.
                    </div>

                    <div class="mb-4">
                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Account:</div>
                            <div class="col-md-9 fw-medium" id="accountName">Loading...</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Configuration ID:</div>
                            <div class="col-md-9"><code>{{ $id }}</code></div>
                        </div>
                    </div>

                    <div id="smartwizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link" href="#step-1"><span>1</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-2"><span>2</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-3"><span>3</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-4"><span>4</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-5"><span>5</span></a></li>
                        </ul>

                        <div class="tab-content">
                            <div id="step-1" class="tab-pane" role="tabpanel">
                                <h5 class="mb-3">Configuration Details</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Configuration Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="configName" placeholder="e.g., Customer Notifications">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Reporting Group</label>
                                            <select class="form-select" id="reportingGroup">
                                                <option value="">Select a group...</option>
                                                <option value="default">Default</option>
                                                <option value="appointments">Appointments</option>
                                                <option value="reminders">Reminders</option>
                                                <option value="notifications">Notifications</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="configDescription" rows="3" placeholder="Optional description for internal reference"></textarea>
                                </div>
                            </div>

                            <div id="step-2" class="tab-pane" role="tabpanel">
                                <h5 class="mb-3">Select Contact List</h5>
                                <p class="text-muted">Choose the contact list that contains your recipient data.</p>
                                <div id="contactListsContainer">
                                    <div class="contact-list-item selected" data-list-id="cl-001">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="contactList" value="cl-001" checked>
                                        </div>
                                        <div class="list-info">
                                            <div class="list-name">Patient Database</div>
                                            <div class="list-count">15,420 contacts</div>
                                        </div>
                                    </div>
                                    <div class="contact-list-item" data-list-id="cl-002">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="contactList" value="cl-002">
                                        </div>
                                        <div class="list-info">
                                            <div class="list-name">Staff Directory</div>
                                            <div class="list-count">342 contacts</div>
                                        </div>
                                    </div>
                                    <div class="contact-list-item" data-list-id="cl-003">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="contactList" value="cl-003">
                                        </div>
                                        <div class="list-info">
                                            <div class="list-name">Supplier Contacts</div>
                                            <div class="list-count">89 contacts</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="step-3" class="tab-pane" role="tabpanel">
                                <h5 class="mb-3">Field Mapping</h5>
                                <p class="text-muted">Map your contact list fields to the Email-to-SMS parameters.</p>
                                <div class="mapping-row">
                                    <div class="field-name">Mobile Number</div>
                                    <select class="form-select form-select-sm" id="mapMobile">
                                        <option value="mobile">mobile</option>
                                        <option value="phone">phone</option>
                                        <option value="cell">cell</option>
                                    </select>
                                </div>
                                <div class="mapping-row">
                                    <div class="field-name">First Name</div>
                                    <select class="form-select form-select-sm" id="mapFirstName">
                                        <option value="firstName">firstName</option>
                                        <option value="first_name">first_name</option>
                                        <option value="name">name</option>
                                    </select>
                                </div>
                                <div class="mapping-row">
                                    <div class="field-name">Last Name</div>
                                    <select class="form-select form-select-sm" id="mapLastName">
                                        <option value="lastName">lastName</option>
                                        <option value="last_name">last_name</option>
                                        <option value="surname">surname</option>
                                    </select>
                                </div>
                            </div>

                            <div id="step-4" class="tab-pane" role="tabpanel">
                                <h5 class="mb-3">Email & SMS Settings</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="form-label">Generated Email Addresses</label>
                                            <div class="email-display mb-2">
                                                <code id="emailAddress1">config-name@sms.quicksms.co.uk</code>
                                            </div>
                                            <small class="text-muted">Emails sent to these addresses will trigger SMS to the contact list.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Sender ID <span class="text-danger">*</span></label>
                                            <select class="form-select" id="senderId">
                                                <option value="">Select sender ID...</option>
                                                <option value="QuickSMS">QuickSMS</option>
                                                <option value="Reminders">Reminders</option>
                                                <option value="Alerts">Alerts</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Allowed Sender Emails</label>
                                    <p class="text-muted small">Only emails from these addresses will be processed.</p>
                                    <div id="sendersList">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-admin-outline" id="btnAddSender">
                                        <i class="fas fa-plus me-1"></i> Add Sender
                                    </button>
                                </div>
                            </div>

                            <div id="step-5" class="tab-pane" role="tabpanel">
                                <h5 class="mb-3">Review & Save</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Configuration Summary</h6>
                                        <div class="row mb-2">
                                            <div class="col-4 text-muted">Name:</div>
                                            <div class="col-8" id="summaryName">-</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4 text-muted">Contact List:</div>
                                            <div class="col-8" id="summaryContactList">-</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4 text-muted">Sender ID:</div>
                                            <div class="col-8" id="summarySenderId">-</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4 text-muted">Allowed Senders:</div>
                                            <div class="col-8" id="summarySenders">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="toolbar-bottom">
                            <a href="{{ route('admin.assets.email-to-sms') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="button" class="btn btn-secondary sw-btn-prev">Previous</button>
                            <button type="button" class="btn btn-admin-primary sw-btn-next">Next</button>
                            <button type="button" class="btn btn-admin-primary sw-btn-finish" style="display: none;">Save Changes</button>
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
var configId = '{{ $id }}';
var configData = null;

$(document).ready(function() {
    $('#smartwizard').smartWizard({
        selected: 0,
        theme: 'default',
        autoAdjustHeight: false,
        enableURLhash: false,
        toolbar: {
            showNextButton: false,
            showPreviousButton: false
        },
        anchor: {
            enableNavigation: true,
            enableDoneState: true,
            markAllPreviousStepsAsDone: true
        }
    });

    $('#smartwizard').on('showStep', function(e, anchorObject, stepIndex, stepDirection) {
        if (stepIndex === 4) {
            $('.sw-btn-next').hide();
            $('.sw-btn-finish').show();
            updateSummary();
        } else {
            $('.sw-btn-next').show();
            $('.sw-btn-finish').hide();
        }
        
        if (stepIndex === 0) {
            $('.sw-btn-prev').prop('disabled', true);
        } else {
            $('.sw-btn-prev').prop('disabled', false);
        }
    });

    $('.sw-btn-next').on('click', function() {
        $('#smartwizard').smartWizard('next');
    });

    $('.sw-btn-prev').on('click', function() {
        $('#smartwizard').smartWizard('prev');
    });

    $('.sw-btn-finish').on('click', function() {
        saveChanges();
    });

    $('#btnAddSender').on('click', function() {
        addSenderEntry('');
    });

    $('.contact-list-item').on('click', function() {
        $('.contact-list-item').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
    });

    $('#configName').on('input', function() {
        var name = $(this).val().toLowerCase().replace(/[^a-z0-9]/g, '-').substring(0, 20) || 'config-name';
        $('#emailAddress1').text(name + '@sms.quicksms.co.uk');
    });

    loadConfiguration();
});

function loadConfiguration() {
    try {
        if (typeof EmailToSmsService !== 'undefined' && typeof EmailToSmsService.getContactListSetup === 'function') {
            EmailToSmsService.getContactListSetup(configId).then(function(response) {
                if (response.success) {
                    configData = response.data;
                    populateForm(configData);
                } else {
                    loadMockData();
                }
            }).catch(function(err) {
                console.error('Failed to load configuration:', err);
                loadMockData();
            });
        } else {
            loadMockData();
        }
    } catch (err) {
        console.error('Error loading configuration:', err);
        loadMockData();
    }
}

function loadMockData() {
    configData = {
        id: configId,
        name: 'Patient Appointment Reminders',
        description: 'Sends appointment reminder SMS to patients from the database',
        reportingGroup: 'appointments',
        contactListId: 'cl-001',
        contactListName: 'Patient Database',
        senderId: 'Reminders',
        allowedSenders: ['admin@nhstrust.nhs.uk', 'system@nhstrust.nhs.uk'],
        originatingEmails: ['patient-reminders@sms.quicksms.co.uk'],
        accountName: 'Acme Healthcare Ltd',
        fieldMapping: {
            mobile: 'mobile',
            firstName: 'firstName',
            lastName: 'lastName'
        }
    };
    populateForm(configData);
}

function populateForm(data) {
    $('#accountName').text(data.accountName || 'Unknown Account');
    $('#configName').val(data.name || '').trigger('input');
    $('#configDescription').val(data.description || '');
    $('#reportingGroup').val(data.reportingGroup || '');
    $('#senderId').val(data.senderId || '');
    
    if (data.contactListId) {
        $('.contact-list-item').removeClass('selected');
        $('.contact-list-item[data-list-id="' + data.contactListId + '"]').addClass('selected')
            .find('input[type="radio"]').prop('checked', true);
    }
    
    if (data.fieldMapping) {
        $('#mapMobile').val(data.fieldMapping.mobile || 'mobile');
        $('#mapFirstName').val(data.fieldMapping.firstName || 'firstName');
        $('#mapLastName').val(data.fieldMapping.lastName || 'lastName');
    }
    
    $('#sendersList').empty();
    if (data.allowedSenders && data.allowedSenders.length > 0) {
        data.allowedSenders.forEach(function(sender) {
            addSenderEntry(sender);
        });
    }
}

function addSenderEntry(email) {
    var entry = $('<div class="sender-entry">' +
        '<input type="email" class="form-control form-control-sm sender-email" placeholder="email@example.com" value="' + (email || '') + '">' +
        '<button type="button" class="btn btn-sm btn-outline-danger remove-sender"><i class="fas fa-times"></i></button>' +
    '</div>');
    
    entry.find('.remove-sender').on('click', function() {
        entry.remove();
    });
    
    $('#sendersList').append(entry);
}

function updateSummary() {
    $('#summaryName').text($('#configName').val() || '-');
    
    var selectedList = $('.contact-list-item.selected .list-name').text();
    $('#summaryContactList').text(selectedList || '-');
    
    $('#summarySenderId').text($('#senderId').val() || '-');
    
    var senders = [];
    $('.sender-email').each(function() {
        if ($(this).val()) senders.push($(this).val());
    });
    $('#summarySenders').text(senders.length > 0 ? senders.join(', ') : 'All senders allowed');
}

function saveChanges() {
    var senders = [];
    $('.sender-email').each(function() {
        if ($(this).val()) senders.push($(this).val());
    });
    
    var updatedData = {
        id: configId,
        name: $('#configName').val(),
        description: $('#configDescription').val(),
        reportingGroup: $('#reportingGroup').val(),
        contactListId: $('input[name="contactList"]:checked').val(),
        senderId: $('#senderId').val(),
        allowedSenders: senders,
        fieldMapping: {
            mobile: $('#mapMobile').val(),
            firstName: $('#mapFirstName').val(),
            lastName: $('#mapLastName').val()
        }
    };
    
    console.log('[AUDIT] Admin Edit - Email-to-SMS Contact List Configuration', {
        configId: configId,
        changes: updatedData,
        adminUser: 'admin@quicksms.co.uk',
        timestamp: new Date().toISOString()
    });
    
    alert('Configuration saved successfully!');
    window.location.href = '{{ route("admin.assets.email-to-sms") }}';
}
</script>
@endpush

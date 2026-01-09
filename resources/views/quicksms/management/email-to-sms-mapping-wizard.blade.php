@extends('layouts.quicksms')

@section('title', 'Create Email-to-SMS Mapping')

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
    max-width: 180px;
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
.form-wizard .nav-wizard li .nav-link span.step-number {
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
    z-index: 1;
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
.form-wizard .nav-wizard li .nav-link.active span.step-number,
.form-wizard .nav-wizard li .nav-link.done span.step-number {
    background: var(--primary, #886CC0);
    color: #fff;
    border-color: var(--primary, #886CC0);
}
.form-wizard .nav-wizard li .nav-link.active:after,
.form-wizard .nav-wizard li .nav-link.done:after {
    background: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link .step-label {
    margin-top: 0.5rem;
    font-size: 0.7rem;
    text-align: center;
    max-width: 80px;
    line-height: 1.2;
    color: #6c757d;
}
.form-wizard .toolbar-bottom {
    display: flex;
    justify-content: space-between;
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
    padding: 1rem 0;
}
.step-content-header {
    text-align: center;
    margin-bottom: 2rem;
}
.step-content-header h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.step-content-header p {
    color: #6c757d;
    margin-bottom: 0;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management') }}">Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management.email-to-sms') }}">Email-to-SMS</a></li>
            <li class="breadcrumb-item active">Create Mapping</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Create Email-to-SMS Mapping</h4>
                </div>
                <div class="card-body">
                    <div id="mappingWizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li>
                                <a class="nav-link" href="#step-contact-list">
                                    <span class="step-number">1</span>
                                    <span class="step-label">Contact List</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link" href="#step-allowed-senders">
                                    <span class="step-number">2</span>
                                    <span class="step-label">Allowed Senders</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link" href="#step-email-generation">
                                    <span class="step-number">3</span>
                                    <span class="step-label">Email Address</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link" href="#step-confirmation">
                                    <span class="step-number">4</span>
                                    <span class="step-label">Confirmation</span>
                                </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <div id="step-contact-list" class="tab-pane" role="tabpanel">
                                <div class="step-content-header">
                                    <h4>Select Contact List</h4>
                                    <p>Choose the Contact Book List that will receive SMS messages when an email is sent to the generated address.</p>
                                </div>
                                
                                <div class="row justify-content-center">
                                    <div class="col-lg-8">
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
                            
                            <div id="step-allowed-senders" class="tab-pane" role="tabpanel">
                                <div class="step-content-header">
                                    <h4>Allowed Sender Emails (Optional)</h4>
                                    <p>Specify which email addresses are permitted to trigger this mapping. Leave empty to allow any sender.</p>
                                </div>
                                
                                <div class="row justify-content-center">
                                    <div class="col-lg-8">
                                        <div class="mb-3">
                                            <label class="form-label">Add Email Addresses</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control" id="newSenderEmail" placeholder="Enter email address...">
                                                <button class="btn btn-outline-primary" type="button" id="btnAddSender">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </div>
                                            <div class="form-text">Press Enter or click Add to add an email address to the whitelist.</div>
                                        </div>
                                        
                                        <div id="senderEmailTags" class="mb-3">
                                        </div>
                                        
                                        <div class="alert alert-info small" style="background-color: rgba(48, 101, 208, 0.1); border: none;">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Tip:</strong> If you leave this empty, any sender can trigger SMS messages to this Contact List. Add specific email addresses to restrict who can send.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-email-generation" class="tab-pane" role="tabpanel">
                                <div class="step-content-header">
                                    <h4>Generated Email Address</h4>
                                    <p>Your unique inbound email address has been generated. Emails sent to this address will trigger SMS to the selected Contact List.</p>
                                </div>
                                
                                <div class="row justify-content-center">
                                    <div class="col-lg-8">
                                        <div class="generated-email">
                                            <code id="generatedEmailDisplay">loading...</code>
                                            <div>
                                                <button class="btn btn-outline-primary btn-sm" id="btnCopyEmail">
                                                    <i class="fas fa-copy me-1"></i> Copy to Clipboard
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-warning mt-3" style="background-color: rgba(255, 193, 7, 0.1); border: none;">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Important:</strong> Each Email-to-SMS Address can only be mapped to one Contact List. This address is unique and cannot be changed after creation.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-confirmation" class="tab-pane" role="tabpanel">
                                <div class="step-content-header">
                                    <h4>Review & Confirm</h4>
                                    <p>Please review your mapping configuration before creating.</p>
                                </div>
                                
                                <div class="row justify-content-center">
                                    <div class="col-lg-8">
                                        <div class="summary-card">
                                            <div class="summary-row">
                                                <span class="summary-label">Email-to-SMS Address</span>
                                                <span class="summary-value" id="summaryEmail">-</span>
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
                                                <span class="summary-label">Allowed Senders</span>
                                                <span class="summary-value" id="summarySenders">-</span>
                                            </div>
                                        </div>
                                        
                                        <div class="rules-box">
                                            <h6><i class="fas fa-info-circle me-2"></i>How It Works</h6>
                                            <ul>
                                                <li><strong>SenderID</strong> is extracted from the <strong>EMAIL SUBJECT</strong> line</li>
                                                <li><strong>SMS content</strong> is extracted from the <strong>EMAIL BODY</strong></li>
                                                <li>All recipients in the selected Contact List will receive the SMS</li>
                                                <li>Only whitelisted sender emails can trigger this mapping (if configured)</li>
                                            </ul>
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
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
    
    var allowedSenders = [];
    var generatedEmail = '';
    
    function renderContactLists(lists) {
        var html = '';
        lists.forEach(function(list) {
            var isSelected = $('#selectedContactListId').val() === list.id;
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
        if (allowedSenders.length === 0) {
            $('#senderEmailTags').html('<span class="text-muted">No sender restrictions - any email can trigger this mapping</span>');
            return;
        }
        var html = '';
        allowedSenders.forEach(function(email, index) {
            html += '<span class="sender-email-tag">' + email + '<span class="remove-tag" data-index="' + index + '">&times;</span></span>';
        });
        $('#senderEmailTags').html(html);
    }
    
    function generateEmailAddress() {
        var listName = $('#selectedContactListName').val() || 'mapping';
        var slug = listName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        var random = Math.floor(1000 + Math.random() * 9000);
        generatedEmail = slug + '-' + random + '@sms.quicksms.com';
        $('#generatedEmailDisplay').text(generatedEmail);
    }
    
    function updateSummary() {
        $('#summaryEmail').text(generatedEmail);
        $('#summaryContactList').text($('#selectedContactListName').val() || '-');
        $('#summaryRecipients').text(($('#selectedContactListCount').val() || '0') + ' contacts');
        $('#summarySenders').text(allowedSenders.length > 0 ? allowedSenders.length + ' whitelisted' : 'Any sender allowed');
    }
    
    renderContactLists(mockContactLists);
    renderSenderTags();
    
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
        $('#selectedContactListId').val($(this).data('id'));
        $('#selectedContactListName').val($(this).data('name'));
        $('#selectedContactListCount').val($(this).data('count'));
        $('#contactListError').hide();
    });
    
    function addSenderEmail() {
        var email = $('#newSenderEmail').val().trim();
        if (!email) return;
        
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $('#newSenderEmail').addClass('is-invalid');
            return;
        }
        
        if (allowedSenders.indexOf(email) !== -1) {
            return;
        }
        
        allowedSenders.push(email);
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
        allowedSenders.splice(index, 1);
        renderSenderTags();
    });
    
    $('#btnCopyEmail').on('click', function() {
        navigator.clipboard.writeText(generatedEmail).then(function() {
            var btn = $('#btnCopyEmail');
            btn.html('<i class="fas fa-check me-1"></i> Copied!');
            setTimeout(function() {
                btn.html('<i class="fas fa-copy me-1"></i> Copy to Clipboard');
            }, 2000);
        });
    });
    
    $('#mappingWizard').smartWizard({
        selected: 0,
        theme: 'default',
        autoAdjustHeight: true,
        transition: {
            animation: 'fade'
        },
        toolbar: {
            showNextButton: true,
            showPreviousButton: true,
            position: 'bottom'
        },
        anchor: {
            enableNavigation: false,
            enableNavigationAlways: false,
            enableDoneState: true,
            markPreviousStepsAsDone: true,
            unDoneOnBackNavigation: true,
            enableClickNavigation: false
        },
        keyboard: {
            keyNavigation: false
        },
        lang: {
            next: 'Next <i class="fas fa-arrow-right ms-1"></i>',
            previous: '<i class="fas fa-arrow-left me-1"></i> Back'
        }
    });
    
    $('#mappingWizard').on('leaveStep', function(e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {
        if (stepDirection === 'forward') {
            if (currentStepIndex === 0) {
                if (!$('#selectedContactListId').val()) {
                    $('#contactListError').show();
                    return false;
                }
            }
            
            if (currentStepIndex === 1) {
                generateEmailAddress();
            }
            
            if (currentStepIndex === 2) {
                updateSummary();
            }
        }
        return true;
    });
    
    $('#mappingWizard').on('showStep', function(e, anchorObject, stepIndex, stepDirection) {
        var totalSteps = 4;
        var $nextBtn = $('.sw-btn-next');
        
        if (stepIndex === totalSteps - 1) {
            $nextBtn.html('<i class="fas fa-check me-1"></i> Create Mapping');
        } else {
            $nextBtn.html('Next <i class="fas fa-arrow-right ms-1"></i>');
        }
    });
    
    $('#mappingWizard').on('leaveStep', function(e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {
        if (currentStepIndex === 3 && stepDirection === 'forward') {
            var newMapping = {
                id: 'clm-' + Date.now(),
                emailAddress: generatedEmail,
                contactListName: $('#selectedContactListName').val(),
                contactListId: $('#selectedContactListId').val(),
                recipientsCount: parseInt($('#selectedContactListCount').val()),
                allowedSenders: allowedSenders.slice(),
                lastUsed: '-',
                created: new Date().toISOString().split('T')[0],
                status: 'Active'
            };
            
            sessionStorage.setItem('newMapping', JSON.stringify(newMapping));
            
            window.location.href = '{{ route("management.email-to-sms") }}?tab=contact-lists&created=1';
            return false;
        }
        return true;
    });
});
</script>
@endpush

@extends('layouts.quicksms')

@section('title', isset($id) ? 'Edit Standard Email-to-SMS' : 'Create Standard Email-to-SMS')

@push('styles')
<style>
.create-form-section {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}
.create-form-section-header {
    background: #f8f9fa;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    border-radius: 0.5rem 0.5rem 0 0;
}
.create-form-section-header h6 {
    margin: 0;
    font-weight: 600;
    color: #495057;
}
.create-form-section-body {
    padding: 1.25rem;
}
.email-tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    min-height: 2.5rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
}
.email-tag {
    display: inline-flex;
    align-items: center;
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}
.email-tag.email-tag-wildcard {
    background: #fff3cd;
    border: 1px solid #ffc107;
}
.email-tag .remove-email {
    margin-left: 0.5rem;
    cursor: pointer;
    color: #dc3545;
    font-weight: bold;
}
.email-tag .remove-email:hover {
    color: #b02a37;
}
</style>
@endpush

@section('content')
<div class="row page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('management.email-to-sms') }}?tab=standard">Email-to-SMS</a></li>
        <li class="breadcrumb-item active">{{ isset($id) ? 'Edit' : 'Create' }} Standard Email-to-SMS</li>
    </ol>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        {{ isset($id) ? 'Edit' : 'Create' }} Standard Email-to-SMS
                    </h4>
                </div>
                <div class="card-body">
                    
                    <div class="create-form-section">
                        <div class="create-form-section-header">
                            <h6><i class="fas fa-info-circle me-2 text-primary"></i>General</h6>
                        </div>
                        <div class="create-form-section-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="stdFormName" placeholder="e.g., Appointment Reminders">
                                    <div class="invalid-feedback">Name is required.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description</label>
                                    <input type="text" class="form-control" id="stdFormDescription" placeholder="Optional description...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Subaccount <span class="text-danger">*</span></label>
                                    <select class="form-select" id="stdFormSubaccount">
                                        <option value="">Select subaccount...</option>
                                        <option value="main">Main Account</option>
                                        <option value="marketing">Marketing Team</option>
                                        <option value="support">Support Team</option>
                                    </select>
                                    <div class="invalid-feedback">Subaccount is required.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="create-form-section">
                        <div class="create-form-section-header">
                            <h6><i class="fas fa-envelope-open-text me-2 text-primary"></i>Email Settings (Sender Allowlist)</h6>
                        </div>
                        <div class="create-form-section-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Allowed Sender Emails</label>
                                <p class="text-muted small mb-2">Only emails from these addresses will trigger SMS. Leave empty to allow all senders. Supports wildcard domains (e.g., *@company.com).</p>
                                <div class="input-group mb-2">
                                    <input type="email" class="form-control" id="stdFormEmailInput" placeholder="email@example.com or *@domain.com">
                                    <button class="btn btn-primary" type="button" id="stdFormAddEmailBtn">
                                        <i class="fas fa-plus me-1"></i> Add
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="stdFormEmailError" style="display: none;">Invalid email format.</div>
                                <div id="stdFormEmailTagsContainer" class="email-tags-container"></div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted"><span id="stdFormEmailCount">0</span> email(s) added</small>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" id="stdFormClearAllEmails" style="display: none;">
                                        <i class="fas fa-trash-alt me-1"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            
                            <div id="stdFormWildcardWarning" class="alert alert-warning d-none" style="font-size: 0.85rem;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> Wildcard domains are less secure and may result in unintended messages being sent.
                            </div>
                        </div>
                    </div>
                    
                    <div class="create-form-section">
                        <div class="create-form-section-header">
                            <h6><i class="fas fa-sms me-2 text-primary"></i>Message Settings</h6>
                        </div>
                        <div class="create-form-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">SenderID <span class="text-danger">*</span></label>
                                    <select class="form-select" id="stdFormSenderId">
                                        <option value="">Select SenderID...</option>
                                        <option value="QuickSMS">QuickSMS</option>
                                        <option value="ALERTS">ALERTS</option>
                                        <option value="NHS">NHS</option>
                                        <option value="INFO">INFO</option>
                                        <option value="Pharmacy">Pharmacy</option>
                                    </select>
                                    <small class="text-muted">Only approved/live SenderIDs are shown.</small>
                                    <div class="invalid-feedback">SenderID is required.</div>
                                </div>
                                
                                <div class="col-md-6" id="stdFormSubjectAsSenderIdGroup">
                                    <label class="form-label fw-semibold">Subject as SenderID</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="stdFormSubjectAsSenderId">
                                        <label class="form-check-label" for="stdFormSubjectAsSenderId">
                                            Extract SenderID from email subject
                                        </label>
                                    </div>
                                    <small class="text-muted">When enabled, the SenderID is extracted from the email subject line.</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Enable Multiple SMS</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="stdFormMultipleSms">
                                        <label class="form-check-label" for="stdFormMultipleSms">
                                            Allow multipart SMS messages
                                        </label>
                                    </div>
                                    <small class="text-muted">Messages over 160 characters will be sent as multiple parts.</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Send Delivery Reports</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="stdFormDeliveryReports">
                                        <label class="form-check-label" for="stdFormDeliveryReports">
                                            Enable delivery report notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6" id="stdFormDeliveryEmailGroup" style="display: none;">
                                    <label class="form-label fw-semibold">Delivery Reports Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="stdFormDeliveryEmail" placeholder="reports@yourcompany.com">
                                    <small class="text-muted">Email address to receive delivery status reports.</small>
                                    <div class="invalid-feedback" id="stdFormDeliveryEmailError">Valid email is required for delivery reports.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Filter Content (Signature Removal)</label>
                                    <textarea class="form-control" id="stdFormSignatureFilter" rows="3" placeholder="e.g., --\n.*\nSent from.*"></textarea>
                                    <div class="invalid-feedback" id="stdFormSignatureFilterError">Invalid regex pattern</div>
                                    <small class="text-muted">Remove matching content from inbound emails (e.g., signatures). Regex supported. One pattern per line.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('management.email-to-sms') }}?tab=standard" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="button" class="btn btn-primary" id="btnSaveStandardForm">
                        <i class="fas fa-check me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="stdFormEditingId" value="{{ $id ?? '' }}">
@endsection

@push('scripts')
<script src="{{ asset('js/services/email-to-sms-service.js') }}"></script>
<script>
$(document).ready(function() {
    var stdFormAllowedEmails = [];
    var editingId = $('#stdFormEditingId').val() || null;
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
    
    function stdFormAddAllowedEmail() {
        var input = $('#stdFormEmailInput');
        var email = input.val().trim().toLowerCase();
        var errorEl = $('#stdFormEmailError');
        
        if (!email) return;
        
        var validation = EmailToSmsService.validateEmail(email);
        if (!validation.valid) {
            errorEl.text('Invalid email format. Use email@domain.com or *@domain.com for wildcards.').show();
            input.addClass('is-invalid');
            return;
        }
        
        if (stdFormAllowedEmails.includes(email)) {
            errorEl.text('This email has already been added.').show();
            input.addClass('is-invalid');
            return;
        }
        
        errorEl.hide();
        input.removeClass('is-invalid');
        
        stdFormAllowedEmails.push(email);
        input.val('');
        stdFormRenderEmailTags();
        stdFormUpdateWildcardWarning();
    }
    
    function stdFormRemoveAllowedEmail(email) {
        var index = stdFormAllowedEmails.indexOf(email);
        if (index > -1) {
            stdFormAllowedEmails.splice(index, 1);
            stdFormRenderEmailTags();
            stdFormUpdateWildcardWarning();
        }
    }
    
    function stdFormRenderEmailTags() {
        var container = $('#stdFormEmailTagsContainer');
        container.empty();
        
        stdFormAllowedEmails.forEach(function(email) {
            var isWildcard = email.startsWith('*@');
            var tag = $('<span class="email-tag' + (isWildcard ? ' email-tag-wildcard' : '') + '">' +
                        '<span class="email-text">' + escapeHtml(email) + '</span>' +
                        '<span class="remove-email" data-email="' + escapeHtml(email) + '">&times;</span>' +
                        '</span>');
            container.append(tag);
        });
        
        $('#stdFormEmailCount').text(stdFormAllowedEmails.length);
        
        if (stdFormAllowedEmails.length > 0) {
            $('#stdFormClearAllEmails').show();
        } else {
            $('#stdFormClearAllEmails').hide();
        }
    }
    
    function stdFormUpdateWildcardWarning() {
        var hasWildcard = stdFormAllowedEmails.some(function(email) {
            return email.startsWith('*@');
        });
        
        if (hasWildcard) {
            $('#stdFormWildcardWarning').removeClass('d-none');
        } else {
            $('#stdFormWildcardWarning').addClass('d-none');
        }
    }
    
    $('#stdFormAddEmailBtn').on('click', stdFormAddAllowedEmail);
    $('#stdFormEmailInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            stdFormAddAllowedEmail();
        }
    });
    
    $(document).on('click', '.remove-email', function() {
        var email = $(this).data('email');
        stdFormRemoveAllowedEmail(email);
    });
    
    $('#stdFormClearAllEmails').on('click', function() {
        stdFormAllowedEmails = [];
        stdFormRenderEmailTags();
        stdFormUpdateWildcardWarning();
    });
    
    $('#stdFormDeliveryReports').on('change', function() {
        if ($(this).is(':checked')) {
            $('#stdFormDeliveryEmailGroup').slideDown(200);
        } else {
            $('#stdFormDeliveryEmailGroup').slideUp(200);
            $('#stdFormDeliveryEmail').val('').removeClass('is-invalid');
        }
    });
    
    if (editingId) {
        EmailToSmsService.listStandardEmailToSmsSetups().then(function(response) {
            if (response.success) {
                var item = response.data.find(function(s) { return s.id === editingId; });
                if (item) {
                    $('#stdFormName').val(item.name);
                    $('#stdFormDescription').val(item.description || '');
                    $('#stdFormSubaccount').val(item.subaccount);
                    $('#stdFormSenderId').val(item.senderId);
                    $('#stdFormSubjectAsSenderId').prop('checked', item.subjectAsSenderId);
                    $('#stdFormMultipleSms').prop('checked', item.multipleSms);
                    $('#stdFormDeliveryReports').prop('checked', item.deliveryReports);
                    if (item.deliveryReports) {
                        $('#stdFormDeliveryEmailGroup').show();
                        $('#stdFormDeliveryEmail').val(item.deliveryEmail || '');
                    }
                    $('#stdFormSignatureFilter').val(item.signatureFilter || '');
                    
                    stdFormAllowedEmails = (item.allowedSenders || []).slice();
                    stdFormRenderEmailTags();
                    stdFormUpdateWildcardWarning();
                }
            }
        });
    }
    
    $('#btnSaveStandardForm').on('click', function() {
        var isValid = true;
        var hasWildcard = false;
        
        var name = $('#stdFormName').val().trim();
        if (!name) {
            $('#stdFormName').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdFormName').removeClass('is-invalid');
        }
        
        var subaccount = $('#stdFormSubaccount').val();
        if (!subaccount) {
            $('#stdFormSubaccount').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdFormSubaccount').removeClass('is-invalid');
        }
        
        var hasInvalidEmail = false;
        stdFormAllowedEmails.forEach(function(email) {
            var validation = EmailToSmsService.validateEmail(email);
            if (!validation.valid) {
                hasInvalidEmail = true;
            }
            if (validation.isWildcard) {
                hasWildcard = true;
            }
        });
        
        if (hasInvalidEmail) {
            $('#stdFormEmailError').text('One or more allowed sender emails are invalid.').show();
            isValid = false;
        } else {
            $('#stdFormEmailError').hide();
        }
        
        if (hasWildcard) {
            $('#stdFormWildcardWarning').removeClass('d-none');
        }
        
        var senderId = $('#stdFormSenderId').val();
        if (!senderId) {
            $('#stdFormSenderId').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdFormSenderId').removeClass('is-invalid');
        }
        
        if ($('#stdFormDeliveryReports').is(':checked')) {
            var deliveryEmail = $('#stdFormDeliveryEmail').val().trim();
            var emailValidation = EmailToSmsService.validateEmail(deliveryEmail);
            if (!deliveryEmail || !emailValidation.valid || emailValidation.isWildcard) {
                $('#stdFormDeliveryEmail').addClass('is-invalid');
                $('#stdFormDeliveryEmailError').text(deliveryEmail ? 'Please enter a valid email address (wildcards not allowed).' : 'Delivery reports email is required.').show();
                isValid = false;
            } else {
                $('#stdFormDeliveryEmail').removeClass('is-invalid');
                $('#stdFormDeliveryEmailError').hide();
            }
        }
        
        var contentFilter = $('#stdFormSignatureFilter').val().trim();
        var regexValidation = EmailToSmsService.validateContentFilterRegex(contentFilter);
        if (!regexValidation.valid) {
            $('#stdFormSignatureFilter').addClass('is-invalid');
            $('#stdFormSignatureFilterError').text(regexValidation.error).show();
            isValid = false;
        } else {
            $('#stdFormSignatureFilter').removeClass('is-invalid');
            $('#stdFormSignatureFilterError').hide();
        }
        
        if (!isValid) {
            return;
        }
        
        var payload = {
            name: name,
            description: $('#stdFormDescription').val().trim(),
            subaccount: subaccount,
            subaccountName: $('#stdFormSubaccount option:selected').text(),
            allowedSenders: stdFormAllowedEmails.slice(),
            senderId: senderId,
            subjectAsSenderId: $('#stdFormSubjectAsSenderId').is(':checked'),
            multipleSms: $('#stdFormMultipleSms').is(':checked'),
            deliveryReports: $('#stdFormDeliveryReports').is(':checked'),
            deliveryEmail: $('#stdFormDeliveryEmail').val().trim(),
            signatureFilter: contentFilter
        };
        
        var savePromise;
        if (editingId) {
            savePromise = EmailToSmsService.updateStandardEmailToSmsSetup(editingId, payload);
        } else {
            savePromise = EmailToSmsService.createStandardEmailToSmsSetup(payload);
        }
        
        savePromise.then(function(response) {
            if (response.success) {
                window.location.href = '{{ route("management.email-to-sms") }}?tab=standard';
            } else {
                alert('Error: ' + (response.error || 'Failed to save setup'));
            }
        }).catch(function(error) {
            console.error('Save failed:', error);
            alert('Error saving setup. Please try again.');
        });
    });
});
</script>
@endpush

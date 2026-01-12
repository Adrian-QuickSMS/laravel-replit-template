@extends('layouts.quicksms')

@section('title', isset($id) ? 'Edit Standard Email-to-SMS' : 'Create Standard Email-to-SMS')

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

.form-wizard .nav-wizard li .nav-link.step-not-visited span {
    background: #fff !important;
    color: var(--primary, #886CC0) !important;
    border-color: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link.step-not-visited:after {
    background: #e9ecef !important;
}
.form-wizard .nav-wizard li .nav-link.step-visited-incomplete span {
    background: rgba(220, 53, 69, 0.15) !important;
    color: #dc3545 !important;
    border-color: #dc3545 !important;
}
.form-wizard .nav-wizard li .nav-link.step-visited-incomplete:after {
    background: #e9ecef !important;
}
.form-wizard .nav-wizard li .nav-link.step-completed span {
    background: var(--primary, #886CC0) !important;
    color: #fff !important;
    border-color: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link.step-completed:after {
    background: var(--primary, #886CC0) !important;
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

.alert-pastel-primary {
    background-color: rgba(136, 108, 192, 0.1);
    border-color: rgba(136, 108, 192, 0.2);
    color: #5a4a7a;
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

.review-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.review-section h6 {
    margin-bottom: 0.75rem;
    color: #495057;
    font-weight: 600;
}
.review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.review-row:last-child {
    border-bottom: none;
}
.review-label {
    color: #6c757d;
    font-size: 0.875rem;
}
.review-value {
    font-weight: 500;
    color: #212529;
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}

.autosave-indicator {
    font-size: 0.85rem;
    color: #6c757d;
}
.autosave-indicator.saving {
    color: #ffc107;
}
.autosave-indicator.saved {
    color: #28a745;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('management.email-to-sms') }}?tab=standard">Email-to-SMS</a></li>
            <li class="breadcrumb-item active">{{ isset($id) ? 'Edit' : 'Create' }} Standard</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-envelope me-2 text-primary"></i>{{ isset($id) ? 'Edit' : 'Create' }} Standard Email-to-SMS</h4>
                    <span class="autosave-indicator saved" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <div class="card-body">
                    <div id="stdEmailSmsWizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link" href="#step-1"><span>1</span><small>Basics</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-2"><span>2</span><small>Senders</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-3"><span>3</span><small>Settings</small></a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <div id="step-1" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 1: Core Configuration</strong> - Define the setup name, description, and assign to a subaccount.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="stdFormName" placeholder="e.g., Appointment Reminders" maxlength="50">
                                                <small class="text-muted">A descriptive name for this Email-to-SMS configuration.</small>
                                                <div class="invalid-feedback">Please enter a name.</div>
                                            </div>
                                            
                                            <div class="col-lg-12 mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" id="stdFormDescription" rows="2" placeholder="Brief description of this Email-to-SMS setup..." maxlength="200"></textarea>
                                                <small class="text-muted"><span id="descCharCount">0</span>/200 characters</small>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="form-label">Subaccount <span class="text-danger">*</span></label>
                                                <select class="form-select" id="stdFormSubaccount">
                                                    <option value="">Select subaccount...</option>
                                                    <option value="main">Main Account</option>
                                                    <option value="marketing">Marketing Team</option>
                                                    <option value="support">Support Team</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a subaccount.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-2" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 2: Sender Allowlist</strong> <span class="badge bg-pastel-primary ms-2">Optional</span>
                                            <p class="mb-0 mt-2 small">Only emails from these addresses will trigger SMS. Leave empty to allow all senders.</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-medium">Allowed Sender Emails</label>
                                            <p class="text-muted small mb-2">Add email addresses that are allowed to send SMS via this configuration. Supports wildcard domains (e.g., *@company.com).</p>
                                            <div class="input-group mb-2">
                                                <input type="email" class="form-control" id="stdFormEmailInput" placeholder="email@example.com or *@domain.com">
                                                <button class="btn btn-primary" type="button" id="stdFormAddEmailBtn">
                                                    <i class="fas fa-plus me-1"></i> Add
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="stdFormEmailError" style="display: none;">Invalid email format.</div>
                                        </div>
                                        
                                        <div id="stdFormEmailTagsContainer" class="email-tags-container mb-3"></div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><span id="stdFormEmailCount">0</span> email(s) added</small>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" id="stdFormClearAllEmails" style="display: none;">
                                                <i class="fas fa-trash-alt me-1"></i> Clear All
                                            </button>
                                        </div>
                                        
                                        <div id="stdFormWildcardWarning" class="alert alert-warning d-none mt-3" style="font-size: 0.85rem;">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Warning:</strong> Wildcard domains are less secure and may result in unintended messages being sent.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-3" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 3: Message Settings</strong> - Configure SenderID, delivery options, and content processing.
                                        </div>
                                        
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label">SenderID <span class="text-danger">*</span></label>
                                                <select class="form-select" id="stdFormSenderId">
                                                    <option value="">Select SenderID...</option>
                                                    <option value="QuickSMS">QuickSMS</option>
                                                    <option value="ALERTS">ALERTS</option>
                                                    <option value="NHS">NHS</option>
                                                    <option value="INFO">INFO</option>
                                                    <option value="Pharmacy">Pharmacy</option>
                                                </select>
                                                <small class="text-muted">Only approved/live SenderIDs are shown.</small>
                                                <div class="invalid-feedback">Please select a SenderID.</div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Subject as SenderID</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="stdFormSubjectAsSenderId">
                                                    <label class="form-check-label" for="stdFormSubjectAsSenderId">
                                                        Extract SenderID from email subject
                                                    </label>
                                                </div>
                                                <small class="text-muted">Overrides selected SenderID with subject line content.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Enable Multiple SMS</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="stdFormMultipleSms">
                                                    <label class="form-check-label" for="stdFormMultipleSms">
                                                        Allow multipart SMS messages
                                                    </label>
                                                </div>
                                                <small class="text-muted">Messages over 160 characters sent as multiple parts.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Delivery Reports</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="stdFormDeliveryReports">
                                                    <label class="form-check-label" for="stdFormDeliveryReports">
                                                        Enable delivery report notifications
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6" id="stdFormDeliveryEmailGroup" style="display: none;">
                                                <label class="form-label">Delivery Reports Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="stdFormDeliveryEmail" placeholder="reports@yourcompany.com">
                                                <small class="text-muted">Receive delivery status notifications.</small>
                                                <div class="invalid-feedback" id="stdFormDeliveryEmailError">Valid email required.</div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label class="form-label">Content Filter (Signature Removal)</label>
                                                <textarea class="form-control" id="stdFormSignatureFilter" rows="2" placeholder="e.g., --\n.*\nSent from.*"></textarea>
                                                <div class="invalid-feedback" id="stdFormSignatureFilterError">Invalid regex pattern</div>
                                                <small class="text-muted">Remove matching content from emails. Regex supported, one pattern per line.</small>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h5 class="mb-3"><i class="fas fa-check-circle me-2 text-primary"></i>Review Configuration</h5>
                                        
                                        <div class="review-section">
                                            <h6><i class="fas fa-info-circle me-2"></i>Basics</h6>
                                            <div class="review-row">
                                                <span class="review-label">Name</span>
                                                <span class="review-value" id="reviewName">-</span>
                                            </div>
                                            <div class="review-row">
                                                <span class="review-label">Description</span>
                                                <span class="review-value" id="reviewDescription">-</span>
                                            </div>
                                            <div class="review-row">
                                                <span class="review-label">Subaccount</span>
                                                <span class="review-value" id="reviewSubaccount">-</span>
                                            </div>
                                        </div>
                                        
                                        <div class="review-section">
                                            <h6><i class="fas fa-envelope me-2"></i>Sender Allowlist</h6>
                                            <div class="review-row">
                                                <span class="review-label">Allowed Senders</span>
                                                <span class="review-value" id="reviewAllowedSenders">All senders allowed</span>
                                            </div>
                                        </div>
                                        
                                        <div class="review-section">
                                            <h6><i class="fas fa-sms me-2"></i>Message Settings</h6>
                                            <div class="review-row">
                                                <span class="review-label">SenderID</span>
                                                <span class="review-value" id="reviewSenderId">-</span>
                                            </div>
                                            <div class="review-row">
                                                <span class="review-label">Subject as SenderID</span>
                                                <span class="review-value" id="reviewSubjectAsSenderId">No</span>
                                            </div>
                                            <div class="review-row">
                                                <span class="review-label">Multiple SMS</span>
                                                <span class="review-value" id="reviewMultipleSms">No</span>
                                            </div>
                                            <div class="review-row">
                                                <span class="review-label">Delivery Reports</span>
                                                <span class="review-value" id="reviewDeliveryReports">No</span>
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
</div>

<input type="hidden" id="stdFormEditingId" value="{{ $id ?? '' }}">
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
<script src="{{ asset('js/services/email-to-sms-service.js') }}"></script>
<script>
$(document).ready(function() {
    var stdFormAllowedEmails = [];
    var editingId = $('#stdFormEditingId').val() || null;
    var setupCreated = false;
    var stepValidated = [false, false, false];
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
    
    $('#stdEmailSmsWizard').smartWizard({
        selected: 0,
        theme: 'default',
        autoAdjustHeight: true,
        contentCache: true,
        transition: {
            animation: 'fade',
            speed: '200'
        },
        toolbar: {
            position: 'bottom',
            showNextButton: true,
            showPreviousButton: true
        },
        anchor: {
            enableNavigation: true,
            enableAllAnchors: true,
            enableDoneState: false,
            markPreviousStepsAsDone: false,
            markAllPreviousStepsAsDone: false,
            removeDoneStepOnNavigateBack: false,
            enableDoneStateNavigation: true
        },
        keyboard: {
            keyNavigation: false
        },
        lang: {
            next: 'Next',
            previous: 'Previous'
        }
    });
    
    function markStepValidated(stepIndex) {
        stepValidated[stepIndex] = true;
    }
    
    function checkStepValidity(stepIndex) {
        if (stepIndex === 0) {
            var name = $('#stdFormName').val().trim();
            var subaccount = $('#stdFormSubaccount').val();
            return name.length > 0 && subaccount.length > 0;
        } else if (stepIndex === 1) {
            return true;
        } else if (stepIndex === 2) {
            var senderId = $('#stdFormSenderId').val();
            if (!senderId) return false;
            
            if ($('#stdFormDeliveryReports').is(':checked')) {
                var deliveryEmail = $('#stdFormDeliveryEmail').val().trim();
                var validation = EmailToSmsService.validateEmail(deliveryEmail);
                if (!validation.valid || validation.isWildcard) return false;
            }
            
            var contentFilter = $('#stdFormSignatureFilter').val().trim();
            var regexValidation = EmailToSmsService.validateContentFilterRegex(contentFilter);
            if (!regexValidation.valid) return false;
            
            return true;
        }
        return true;
    }
    
    function markStepCompleted(stepIndex) {
        var $link = $('.nav-wizard li:eq(' + stepIndex + ') .nav-link');
        $link.removeClass('step-not-visited step-visited-incomplete').addClass('step-completed');
    }
    
    function unmarkStepCompleted(stepIndex) {
        var $link = $('.nav-wizard li:eq(' + stepIndex + ') .nav-link');
        if (stepValidated[stepIndex]) {
            $link.removeClass('step-not-visited step-completed').addClass('step-visited-incomplete');
        }
    }
    
    function updateStepIndicators() {
        for (var i = 0; i < 3; i++) {
            var $link = $('.nav-wizard li:eq(' + i + ') .nav-link');
            if (!stepValidated[i]) {
                $link.removeClass('step-completed step-visited-incomplete').addClass('step-not-visited');
            } else if (checkStepValidity(i)) {
                $link.removeClass('step-not-visited step-visited-incomplete').addClass('step-completed');
            } else {
                $link.removeClass('step-not-visited step-completed').addClass('step-visited-incomplete');
            }
        }
    }
    
    function populateReview() {
        $('#reviewName').text($('#stdFormName').val().trim() || '-');
        $('#reviewDescription').text($('#stdFormDescription').val().trim() || 'None');
        $('#reviewSubaccount').text($('#stdFormSubaccount option:selected').text() || '-');
        
        if (stdFormAllowedEmails.length > 0) {
            $('#reviewAllowedSenders').text(stdFormAllowedEmails.join(', '));
        } else {
            $('#reviewAllowedSenders').text('All senders allowed');
        }
        
        $('#reviewSenderId').text($('#stdFormSenderId').val() || '-');
        $('#reviewSubjectAsSenderId').text($('#stdFormSubjectAsSenderId').is(':checked') ? 'Yes' : 'No');
        $('#reviewMultipleSms').text($('#stdFormMultipleSms').is(':checked') ? 'Yes' : 'No');
        
        if ($('#stdFormDeliveryReports').is(':checked')) {
            var email = $('#stdFormDeliveryEmail').val().trim();
            $('#reviewDeliveryReports').text('Yes (' + (email || 'email not set') + ')');
        } else {
            $('#reviewDeliveryReports').text('No');
        }
    }
    
    function handleCreateSetupClick(e) {
        if (setupCreated) return;
        e.preventDefault();
        e.stopPropagation();
        
        if (!validateAllSteps()) {
            return;
        }
        
        saveSetup();
    }
    
    function validateAllSteps() {
        var isValid = true;
        
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
                isValid = false;
            } else {
                $('#stdFormDeliveryEmail').removeClass('is-invalid');
            }
        }
        
        var contentFilter = $('#stdFormSignatureFilter').val().trim();
        var regexValidation = EmailToSmsService.validateContentFilterRegex(contentFilter);
        if (!regexValidation.valid) {
            $('#stdFormSignatureFilter').addClass('is-invalid');
            $('#stdFormSignatureFilterError').text(regexValidation.error);
            isValid = false;
        } else {
            $('#stdFormSignatureFilter').removeClass('is-invalid');
        }
        
        return isValid;
    }
    
    function saveSetup() {
        var $btn = $('.sw-btn-next');
        $btn.prop('disabled', true).text('Saving...');
        
        var payload = {
            name: $('#stdFormName').val().trim(),
            description: $('#stdFormDescription').val().trim(),
            subaccountId: $('#stdFormSubaccount').val(),
            allowedEmails: stdFormAllowedEmails.slice(),
            senderIdTemplateId: 'tpl-' + $('#stdFormSenderId').val().toLowerCase().replace(/\s+/g, '-'),
            subjectOverridesSenderId: $('#stdFormSubjectAsSenderId').is(':checked'),
            multipleSmsEnabled: $('#stdFormMultipleSms').is(':checked'),
            deliveryReportsEnabled: $('#stdFormDeliveryReports').is(':checked'),
            deliveryReportsEmail: $('#stdFormDeliveryEmail').val().trim(),
            contentFilterRegex: $('#stdFormSignatureFilter').val().trim()
        };
        
        var savePromise;
        if (editingId) {
            savePromise = EmailToSmsService.updateEmailToSmsSetup(editingId, payload);
        } else {
            savePromise = EmailToSmsService.createEmailToSmsSetup(payload);
        }
        
        savePromise.then(function(response) {
            if (response.success) {
                setupCreated = true;
                showSuccessToast(editingId ? 'Setup updated successfully' : 'Setup created successfully');
                setTimeout(function() {
                    window.location.href = '{{ route("management.email-to-sms") }}?tab=standard';
                }, 1000);
            } else {
                showErrorToast(response.error || 'Failed to save setup');
                $btn.prop('disabled', false).text(editingId ? 'Save Changes' : 'Create Setup');
            }
        }).catch(function(error) {
            console.error('Save failed:', error);
            showErrorToast('An error occurred while saving');
            $btn.prop('disabled', false).text(editingId ? 'Save Changes' : 'Create Setup');
        });
    }
    
    function showSuccessToast(message) {
        var toastHtml = '<div class="toast align-items-center text-bg-success border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">' +
            '<div class="d-flex"><div class="toast-body"><i class="fas fa-check-circle me-2"></i>' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
        var toastEl = $(toastHtml).appendTo('body');
        var toast = new bootstrap.Toast(toastEl[0], { delay: 3000 });
        toast.show();
        toastEl.on('hidden.bs.toast', function() { $(this).remove(); });
    }
    
    function showErrorToast(message) {
        var toastHtml = '<div class="toast align-items-center text-bg-danger border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">' +
            '<div class="d-flex"><div class="toast-body"><i class="fas fa-exclamation-circle me-2"></i>' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
        var toastEl = $(toastHtml).appendTo('body');
        var toast = new bootstrap.Toast(toastEl[0], { delay: 5000 });
        toast.show();
        toastEl.on('hidden.bs.toast', function() { $(this).remove(); });
    }
    
    $('#stdEmailSmsWizard').on('leaveStep', function(e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {
        if (setupCreated) return false;
        
        markStepValidated(currentStepIndex);
        
        var isValid = checkStepValidity(currentStepIndex);
        if (isValid) {
            markStepCompleted(currentStepIndex);
        } else {
            unmarkStepCompleted(currentStepIndex);
        }
        updateStepIndicators();
        
        return true;
    });
    
    $('#stdEmailSmsWizard').on('showStep', function(e, anchorObject, stepIndex, stepDirection) {
        updateStepIndicators();
        
        var $nextBtn = $('.toolbar-bottom .sw-btn-next');
        
        if (stepIndex === 2) {
            populateReview();
            $nextBtn.text(editingId ? 'Save Changes' : 'Create Setup');
            $nextBtn.prop('disabled', false).removeClass('disabled').css('pointer-events', 'auto');
            $nextBtn.off('click.createSetup').on('click.createSetup', handleCreateSetupClick);
        } else {
            $nextBtn.text('Next');
            $nextBtn.off('click.createSetup');
        }
    });
    
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
    
    $('#stdFormDescription').on('input', function() {
        var len = $(this).val().length;
        $('#descCharCount').text(len);
    });
    
    if (editingId) {
        EmailToSmsService.getEmailToSmsSetup(editingId).then(function(response) {
            if (response.success && response.data) {
                var item = response.data;
                $('#stdFormName').val(item.name);
                $('#stdFormDescription').val(item.description || '').trigger('input');
                $('#stdFormSubaccount').val(item.subaccountId);
                $('#stdFormSenderId').val(item.senderId);
                $('#stdFormSubjectAsSenderId').prop('checked', item.subjectOverridesSenderId);
                $('#stdFormMultipleSms').prop('checked', item.multipleSmsEnabled);
                $('#stdFormDeliveryReports').prop('checked', item.deliveryReportsEnabled);
                if (item.deliveryReportsEnabled) {
                    $('#stdFormDeliveryEmailGroup').show();
                    $('#stdFormDeliveryEmail').val(item.deliveryReportsEmail || '');
                }
                $('#stdFormSignatureFilter').val(item.contentFilterRegex || '');
                
                stdFormAllowedEmails = (item.allowedEmails || []).slice();
                stdFormRenderEmailTags();
                stdFormUpdateWildcardWarning();
            }
        });
    }
    
    updateStepIndicators();
});
</script>
@endpush

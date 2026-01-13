@extends('layouts.quicksms')

@section('title', 'Create Email-to-SMS – Standard')

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
.form-wizard .tab-content {
    min-height: 300px;
    max-height: calc(100vh - 350px);
    overflow-y: auto;
    padding-bottom: 1rem;
}
.form-wizard .tab-content .tab-pane {
    padding-bottom: 1rem;
}
.form-wizard .toolbar-bottom {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 0 0 0;
    border-top: 1px solid #e9ecef;
    background: #fff;
    position: sticky;
    bottom: 0;
    z-index: 20;
    margin-top: auto;
}
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
    background: #a894d4 !important;
    color: #fff !important;
    border: none !important;
    padding: 0.75rem 1.5rem !important;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.form-wizard .sw-btn-prev:hover {
    background: #9783c7 !important;
}
.form-wizard .sw-btn-next:hover {
    background-color: #7559b3 !important;
}
.form-wizard .sw-btn-prev:disabled,
.form-wizard .sw-btn-next:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.email-tag {
    display: inline-flex;
    align-items: center;
    background: rgba(136, 108, 192, 0.1);
    color: #5a4a7a;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    margin: 0.25rem;
}
.email-tag .remove-email {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.email-tag .remove-email:hover {
    opacity: 1;
    color: #dc3545;
}
.email-tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    min-height: 40px;
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: #f8f9fa;
}
.email-tags-container:empty::before {
    content: 'No emails added';
    color: #adb5bd;
    font-style: italic;
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
.toolbar-bottom .btn.btn-save-draft,
button.btn-save-draft {
    background-color: #fff !important;
    color: #D653C1 !important;
    border: 1px solid #D653C1 !important;
    border-color: #D653C1 !important;
    font-weight: 500;
}
.toolbar-bottom .btn.btn-save-draft:hover,
button.btn-save-draft:hover {
    background-color: rgba(214, 83, 193, 0.08) !important;
    color: #D653C1 !important;
    border-color: #D653C1 !important;
}
.success-email-box {
    background: rgba(136, 108, 192, 0.12);
    border: none;
}
.success-email-box code {
    color: #886CC0;
}
.api-table {
    font-size: 0.9rem;
}
.api-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}
.api-table td {
    vertical-align: middle;
    padding: 0.75rem;
}
.api-table tbody tr:hover {
    background-color: rgba(136, 108, 192, 0.04);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('management.email-to-sms') }}">Email-to-SMS</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management.email-to-sms') }}?tab=standard">Standard</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Create Email-to-SMS – Standard</h4>
                    <span class="autosave-indicator saved" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <div class="card-body">
                    <div id="standardWizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link" href="#step-general"><span>1</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-email"><span>2</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-message"><span>3</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-review"><span>4</span></a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <div class="tab-pane" id="step-general" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 1: General</strong> – Define the basic information for this Email-to-SMS setup.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="stdName" placeholder="e.g., Appointment Reminders" maxlength="50">
                                                <small class="text-muted">A unique, descriptive name for this setup.</small>
                                                <div class="invalid-feedback">Please enter a name.</div>
                                            </div>
                                            
                                            <div class="col-lg-12 mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" id="stdDescription" rows="2" placeholder="Brief description of this Email-to-SMS setup..." maxlength="200"></textarea>
                                                <small class="text-muted"><span id="stdDescCharCount">0</span>/200 characters</small>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="form-label">Sub-Account <span class="text-danger">*</span></label>
                                                <select class="form-select" id="stdSubaccount">
                                                    <option value="">Select sub-account...</option>
                                                    <option value="main">Main Account</option>
                                                    <option value="marketing">Marketing Team</option>
                                                    <option value="support">Support Team</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a sub-account.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="step-email" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 2: Email Settings</strong> – Configure which email addresses are allowed to trigger this setup. <span class="badge bg-secondary">Optional</span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Allowed Sender Emails</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="stdEmailInput" placeholder="e.g., user@domain.com or *@domain.com">
                                                <button class="btn btn-outline-primary" type="button" id="stdAddEmailBtn">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="stdEmailError" style="display: none;">Invalid email format.</div>
                                            <small class="text-muted">Restrict who can trigger this setup. Supports wildcards like *@domain.com. Leave empty to allow all senders.</small>
                                        </div>
                                        
                                        <div id="stdWildcardWarning" class="alert alert-pastel-warning mb-3 d-none">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Warning:</strong> Wildcard domains are less secure and may result in unintended messages being sent.
                                        </div>
                                        
                                        <div id="stdEmailTagsContainer" class="email-tags-container mb-3"></div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><span id="stdEmailCount">0</span> email addresses added</small>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" id="stdClearAllEmails" style="display: none;">
                                                <i class="fas fa-trash-alt me-1"></i> Clear All
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="step-message" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 3: Message Settings</strong> – Configure SenderID, delivery options, and content processing.
                                        </div>
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">SenderID <span class="text-danger">*</span></label>
                                                <select class="form-select" id="stdSenderId">
                                                    <option value="">Select SenderID...</option>
                                                    <option value="QuickSMS">QuickSMS</option>
                                                    <option value="MyBrand">MyBrand</option>
                                                    <option value="Notify">Notify</option>
                                                </select>
                                                <small class="text-muted">Only approved/live SenderIDs are shown.</small>
                                                <div class="invalid-feedback">Please select a SenderID.</div>
                                            </div>
                                            
                                            <div class="col-md-6" id="stdSubjectAsSenderIdGroup">
                                                <label class="form-label">Subject as SenderID</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="stdSubjectAsSenderId">
                                                    <label class="form-check-label" for="stdSubjectAsSenderId">
                                                        Extract SenderID from email subject
                                                    </label>
                                                </div>
                                                <small class="text-muted">Overrides selected SenderID with subject line content.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Enable Multiple SMS</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="stdMultipleSms">
                                                    <label class="form-check-label" for="stdMultipleSms">
                                                        Allow multipart SMS messages
                                                    </label>
                                                </div>
                                                <small class="text-muted">Messages over 160 characters sent as multiple parts.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Send Delivery Reports</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="stdDeliveryReports">
                                                    <label class="form-check-label" for="stdDeliveryReports">
                                                        Enable delivery report notifications
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6" id="stdDeliveryEmailGroup" style="display: none;">
                                                <label class="form-label">Delivery Reports Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="stdDeliveryEmail" placeholder="reports@yourcompany.com">
                                                <small class="text-muted">Receive delivery status notifications.</small>
                                                <div class="invalid-feedback">Valid email required.</div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label class="form-label">Filter Content (Signature Removal)</label>
                                                <textarea class="form-control" id="stdSignatureFilter" rows="3" placeholder="e.g., --&#10;.*&#10;Sent from.*"></textarea>
                                                <small class="text-muted">Remove matching content from emails. Regex patterns supported, one pattern per line.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="step-review" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 4: Review & Confirm</strong> – Please review your configuration before creating.
                                        </div>
                                        
                                        <div class="alert alert-pastel-warning mb-4">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Note:</strong> Email address will be generated and cannot be changed after creation.
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table api-table">
                                                <thead>
                                                    <tr><th colspan="2"><i class="fas fa-cog me-2"></i>Configuration</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" style="width: 200px;">Setup Name</td>
                                                        <td id="summaryName">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Description</td>
                                                        <td id="summaryDescription">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Sub-Account</td>
                                                        <td id="summarySubaccount">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Allowed Senders</td>
                                                        <td id="summaryAllowedSenders">All senders allowed</td>
                                                    </tr>
                                                </tbody>
                                                <thead>
                                                    <tr><th colspan="2"><i class="fas fa-sms me-2"></i>Message Settings</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted">SenderID</td>
                                                        <td id="summarySenderId">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Settings</td>
                                                        <td id="summaryMessageSettings">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Content Filters</td>
                                                        <td id="summaryContentFilter">None</td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
                            <button type="button" class="btn btn-primary" id="btnCreate" style="display: none;">
                                <i class="fas fa-check-circle me-1"></i> Create
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title text-dark" id="successModalLabel">
                    <i class="fas fa-check-circle me-2 text-primary"></i> Email-to-SMS Setup Created
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-envelope-open-text text-primary" style="font-size: 3rem;"></i>
                </div>
                <p class="mb-3">Your Standard Email-to-SMS setup has been created successfully. Send emails to the address below to trigger SMS messages:</p>
                <div class="success-email-box rounded p-3 mb-3">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <code class="fs-5" id="successEmailAddress">-</code>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopySuccessEmail" title="Copy to clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="alert alert-pastel-primary small mb-0">
                    <i class="fas fa-info-circle me-1"></i> 
                    <strong>SenderID:</strong> Extracted from email subject<br>
                    <strong>SMS Content:</strong> Extracted from email body
                </div>
            </div>
            <div class="modal-footer justify-content-center py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('management.email-to-sms') }}?tab=standard" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Standard Library
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
<script>
$(document).ready(function() {
    var currentStep = 0;
    var totalSteps = 4;
    var allowedEmails = [];
    
    $('#standardWizard').smartWizard({
        selected: 0,
        theme: 'dots',
        transition: {
            animation: 'fade'
        },
        toolbar: {
            showNextButton: false,
            showPreviousButton: false
        },
        anchor: {
            enableNavigation: false,
            enableDoneStateNavigation: false
        }
    });
    
    function goToStep(stepIndex) {
        $('#standardWizard').smartWizard('goToStep', stepIndex);
        currentStep = stepIndex;
        updateButtons();
        if (stepIndex === totalSteps - 1) {
            updateReviewSummary();
        }
    }
    
    function updateButtons() {
        if (currentStep === 0) {
            $('#btnPrev').hide();
        } else {
            $('#btnPrev').show();
        }
        
        if (currentStep === totalSteps - 1) {
            $('#btnNext').hide();
            $('#btnCreate').show();
        } else {
            $('#btnNext').show();
            $('#btnCreate').hide();
        }
    }
    
    function validateStep(step) {
        var isValid = true;
        
        if (step === 0) {
            var name = $('#stdName').val().trim();
            var subaccount = $('#stdSubaccount').val();
            
            if (!name) {
                $('#stdName').addClass('is-invalid');
                isValid = false;
            } else {
                $('#stdName').removeClass('is-invalid');
            }
            
            if (!subaccount) {
                $('#stdSubaccount').addClass('is-invalid');
                isValid = false;
            } else {
                $('#stdSubaccount').removeClass('is-invalid');
            }
        } else if (step === 2) {
            var senderId = $('#stdSenderId').val();
            if (!senderId) {
                $('#stdSenderId').addClass('is-invalid');
                isValid = false;
            } else {
                $('#stdSenderId').removeClass('is-invalid');
            }
            
            if ($('#stdDeliveryReports').is(':checked')) {
                var email = $('#stdDeliveryEmail').val().trim();
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    $('#stdDeliveryEmail').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#stdDeliveryEmail').removeClass('is-invalid');
                }
            }
        }
        
        return isValid;
    }
    
    function updateReviewSummary() {
        $('#summaryName').text($('#stdName').val() || '-');
        $('#summaryDescription').text($('#stdDescription').val() || 'None');
        $('#summarySubaccount').text($('#stdSubaccount option:selected').text() || '-');
        
        if (allowedEmails.length > 0) {
            var emailBadges = allowedEmails.map(function(email) {
                return '<span class="badge bg-pastel-primary me-1">' + email + '</span>';
            }).join('');
            $('#summaryAllowedSenders').html(emailBadges);
        } else {
            $('#summaryAllowedSenders').text('All senders allowed');
        }
        
        $('#summarySenderId').text($('#stdSenderId option:selected').text() || '-');
        
        var settings = [];
        if ($('#stdSubjectAsSenderId').is(':checked')) settings.push('Subject as SenderID');
        if ($('#stdMultipleSms').is(':checked')) settings.push('Multiple SMS');
        if ($('#stdDeliveryReports').is(':checked')) settings.push('Delivery Reports');
        $('#summaryMessageSettings').text(settings.length > 0 ? settings.join(', ') : 'None');
        
        var filter = $('#stdSignatureFilter').val().trim();
        $('#summaryContentFilter').text(filter ? 'Custom patterns applied' : 'None');
    }
    
    $('#btnNext').on('click', function() {
        if (validateStep(currentStep)) {
            goToStep(currentStep + 1);
        }
    });
    
    $('#btnPrev').on('click', function() {
        goToStep(currentStep - 1);
    });
    
    $('#stdDescription').on('input', function() {
        $('#stdDescCharCount').text($(this).val().length);
    });
    
    $('#stdDeliveryReports').on('change', function() {
        if ($(this).is(':checked')) {
            $('#stdDeliveryEmailGroup').show();
        } else {
            $('#stdDeliveryEmailGroup').hide();
        }
    });
    
    function addEmail(email) {
        if (allowedEmails.indexOf(email) === -1) {
            allowedEmails.push(email);
            renderEmailTags();
        }
    }
    
    function removeEmail(email) {
        allowedEmails = allowedEmails.filter(function(e) { return e !== email; });
        renderEmailTags();
    }
    
    function renderEmailTags() {
        var container = $('#stdEmailTagsContainer');
        container.empty();
        
        allowedEmails.forEach(function(email) {
            var tag = $('<span class="email-tag">' + email + 
                '<span class="remove-email" data-email="' + email + '">&times;</span></span>');
            container.append(tag);
        });
        
        $('#stdEmailCount').text(allowedEmails.length);
        
        if (allowedEmails.length > 0) {
            $('#stdClearAllEmails').show();
        } else {
            $('#stdClearAllEmails').hide();
        }
        
        var hasWildcard = allowedEmails.some(function(e) { return e.startsWith('*@'); });
        if (hasWildcard) {
            $('#stdWildcardWarning').removeClass('d-none');
        } else {
            $('#stdWildcardWarning').addClass('d-none');
        }
    }
    
    $('#stdAddEmailBtn').on('click', function() {
        var email = $('#stdEmailInput').val().trim();
        var emailRegex = /^(\*@[\w.-]+|[^\s@]+@[^\s@]+\.[^\s@]+)$/;
        
        if (email && emailRegex.test(email)) {
            addEmail(email);
            $('#stdEmailInput').val('');
            $('#stdEmailError').hide();
        } else {
            $('#stdEmailError').show();
        }
    });
    
    $('#stdEmailInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#stdAddEmailBtn').click();
        }
    });
    
    $(document).on('click', '.remove-email', function() {
        var email = $(this).data('email');
        removeEmail(email);
    });
    
    $('#stdClearAllEmails').on('click', function() {
        allowedEmails = [];
        renderEmailTags();
    });
    
    $('#btnCreate').on('click', function() {
        if (!validateStep(2)) return;
        
        var generatedEmail = $('#stdName').val().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '') + 
            '-' + Math.random().toString(36).substring(2, 6) + '@sms.quicksms.com';
        
        $('#successEmailAddress').text(generatedEmail);
        
        var modal = new bootstrap.Modal($('#successModal')[0]);
        modal.show();
    });
    
    $('#btnCopySuccessEmail').on('click', function() {
        var email = $('#successEmailAddress').text();
        navigator.clipboard.writeText(email).then(function() {
            var btn = $('#btnCopySuccessEmail');
            btn.html('<i class="fas fa-check"></i>');
            setTimeout(function() {
                btn.html('<i class="fas fa-copy"></i>');
            }, 2000);
        });
    });
    
    $('#btnSaveDraft').on('click', function() {
        $('#autosaveIndicator').removeClass('saved').addClass('saving');
        $('#autosaveText').text('Saving...');
        
        setTimeout(function() {
            $('#autosaveIndicator').removeClass('saving').addClass('saved');
            $('#autosaveText').text('Draft saved');
        }, 1000);
    });
    
    updateButtons();
});
</script>
@endpush

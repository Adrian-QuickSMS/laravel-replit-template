@extends('layouts.quicksms')

@section('title', 'Register SenderID')

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
    font-weight: 500;
}
.toolbar-bottom .btn.btn-save-draft:hover,
button.btn-save-draft:hover {
    background-color: rgba(214, 83, 193, 0.08) !important;
}
.autosave-indicator {
    font-size: 0.85rem;
    color: #6c757d;
    display: flex;
    align-items: center;
}
.autosave-indicator.saved {
    color: #28a745;
}
.type-selector {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.type-card {
    flex: 1;
    min-width: 140px;
    max-width: 180px;
    padding: 1.25rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
}
.type-card:hover {
    border-color: #a78bda;
    background: rgba(136, 108, 192, 0.02);
}
.type-card.selected {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.08);
}
.type-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
}
.type-card-icon i {
    font-size: 1.25rem;
    color: #886CC0;
}
.type-card.selected .type-card-icon {
    background: #886CC0;
}
.type-card.selected .type-card-icon i {
    color: #fff;
}
.type-card-title {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 0.25rem;
}
.type-card-desc {
    font-size: 0.75rem;
    color: #6c757d;
    line-height: 1.3;
}
.senderid-input {
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: 1px;
}
.permission-blocked-alert {
    border-left: 4px solid #dc3545;
}
.review-summary {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
}
.review-section {
    margin-bottom: 1.5rem;
}
.review-section:last-child {
    margin-bottom: 0;
}
.review-section-title {
    font-size: 0.9rem;
    color: #495057;
    margin-bottom: 0.75rem;
    font-weight: 600;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.4rem 0;
}
.review-label {
    color: #6c757d;
    font-size: 0.85rem;
}
.review-value {
    font-weight: 500;
    color: #343a40;
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('management.sms-sender-id') }}">SMS SenderID</a></li>
            <li class="breadcrumb-item active">Register</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-id-badge me-2 text-primary"></i>Register SenderID</h4>
                    <span class="autosave-indicator saved" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <div class="card-body">
                    <div id="senderIdWizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link" href="#step-senderid"><span>1</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-business"><span>2</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-permission"><span>3</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-usecase"><span>4</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-review"><span>5</span></a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <div class="tab-pane" id="step-senderid" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 1: SenderID Type & Value</strong> – Choose the type and enter the value you wish to register.
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">SenderID Type <span class="text-danger">*</span></label>
                                            <div class="type-selector">
                                                <div class="type-card selected" data-type="alphanumeric">
                                                    <div class="type-card-icon"><i class="fas fa-font"></i></div>
                                                    <div class="type-card-title">Alphanumeric</div>
                                                    <div class="type-card-desc">Text-based ID<br>e.g. MYBRAND</div>
                                                </div>
                                                <div class="type-card" data-type="numeric">
                                                    <div class="type-card-icon"><i class="fas fa-phone"></i></div>
                                                    <div class="type-card-title">Numeric</div>
                                                    <div class="type-card-desc">UK Virtual Mobile<br>e.g. +447700...</div>
                                                </div>
                                                <div class="type-card" data-type="shortcode">
                                                    <div class="type-card-icon"><i class="fas fa-hashtag"></i></div>
                                                    <div class="type-card-title">Shortcode</div>
                                                    <div class="type-card-desc">Short number<br>e.g. 60123</div>
                                                </div>
                                            </div>
                                            <input type="hidden" id="inputType" value="alphanumeric">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">SenderID Value <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control senderid-input" id="inputSenderId" 
                                                   maxlength="11" placeholder="e.g. MyBrand" autocomplete="off">
                                            <div class="d-flex justify-content-between mt-1">
                                                <small class="text-muted" id="senderIdHint">Max 11 characters: A-Z a-z 0-9 . - _ & space</small>
                                                <small class="text-muted"><span id="senderIdCharCount">0</span>/11</small>
                                            </div>
                                            <div class="invalid-feedback" id="senderIdError"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="step-business" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 2: Business Association</strong> – Associate this SenderID with your business entity.
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Brand / Business Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="inputBrand" 
                                                   placeholder="Your company or brand name" autocomplete="off">
                                            <small class="text-muted">The legal entity or brand this SenderID represents</small>
                                            <div class="invalid-feedback" id="brandError"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Country</label>
                                            <input type="text" class="form-control" value="United Kingdom" readonly disabled>
                                            <small class="text-muted">SenderID registrations are currently available for UK only</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Subaccount</label>
                                            <select class="form-select" id="inputSubaccount">
                                                <option value="">Main Account</option>
                                                <option value="marketing">Marketing Department</option>
                                                <option value="support">Customer Support</option>
                                                <option value="operations">Operations</option>
                                            </select>
                                            <small class="text-muted">Optionally assign to a subaccount for billing/reporting</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="step-permission" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 3: Permission Confirmation</strong> – Confirm your authorisation to use this SenderID.
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Do you have permission to use this SenderID? <span class="text-danger">*</span></label>
                                            <select class="form-select" id="inputPermission">
                                                <option value="">Select...</option>
                                                <option value="yes">Yes - I am authorised to use this SenderID</option>
                                                <option value="no">No - I do not have permission</option>
                                            </select>
                                            <div class="invalid-feedback" id="permissionError"></div>
                                        </div>

                                        <div class="permission-blocked-alert alert alert-danger" id="permissionBlockedAlert" style="display: none;">
                                            <div class="d-flex">
                                                <i class="fas fa-ban me-3 mt-1 fa-lg"></i>
                                                <div>
                                                    <strong class="d-block">Registration Cannot Continue</strong>
                                                    <p class="mb-2 small">You have indicated that you do not have permission to use this SenderID. UK regulations require explicit authorisation from the brand owner before a SenderID can be registered.</p>
                                                    <p class="mb-0 small text-muted"><i class="fas fa-arrow-right me-1"></i>Please obtain written authorisation from the brand owner, then return to complete registration.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3" id="confirmationSection" style="display: none;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="inputConfirmAuthorised">
                                                <label class="form-check-label" for="inputConfirmAuthorised">
                                                    I confirm I am authorised to use this SenderID and understand that misuse may result in suspension
                                                </label>
                                            </div>
                                            <div class="invalid-feedback" id="confirmError"></div>
                                        </div>

                                        <div class="mb-3" id="explanationSection" style="display: none;">
                                            <label class="form-label fw-semibold">Additional Explanation (Optional)</label>
                                            <textarea class="form-control" id="inputExplanation" rows="3" 
                                                      placeholder="Provide any additional context about your authorisation..."></textarea>
                                            <small class="text-muted">e.g. "Brand registered under company X" or "Subsidiary of parent company Y"</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="step-usecase" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 4: Intended Use Case</strong> – How will this SenderID be used for messaging?
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Primary Use Case <span class="text-danger">*</span></label>
                                            <select class="form-select" id="inputUseCase">
                                                <option value="">Select use case...</option>
                                                <option value="transactional">Transactional - Order updates, confirmations, receipts</option>
                                                <option value="marketing">Promotional - Marketing messages, offers, campaigns</option>
                                                <option value="otp">OTP - One-time passwords, verification codes, 2FA</option>
                                                <option value="mixed">Mixed - Combination of above use cases</option>
                                            </select>
                                            <div class="invalid-feedback" id="useCaseError"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" id="inputDescription" rows="3" 
                                                      placeholder="Describe how this SenderID will be used..."></textarea>
                                            <small class="text-muted">Help reviewers understand your intended messaging use</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="step-review" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 5: Review & Submit</strong> – Please review your registration details before submitting.
                                        </div>
                                        
                                        <div class="review-summary">
                                            <div class="review-section">
                                                <div class="review-section-title"><i class="fas fa-id-badge me-2"></i>SenderID Details</div>
                                                <div class="review-row">
                                                    <span class="review-label">Type:</span>
                                                    <span class="review-value" id="reviewType"></span>
                                                </div>
                                                <div class="review-row">
                                                    <span class="review-label">SenderID Value:</span>
                                                    <span class="review-value" id="reviewSenderId"></span>
                                                </div>
                                            </div>
                                            
                                            <div class="review-section">
                                                <div class="review-section-title"><i class="fas fa-building me-2"></i>Business</div>
                                                <div class="review-row">
                                                    <span class="review-label">Brand:</span>
                                                    <span class="review-value" id="reviewBrand"></span>
                                                </div>
                                                <div class="review-row">
                                                    <span class="review-label">Country:</span>
                                                    <span class="review-value">United Kingdom</span>
                                                </div>
                                                <div class="review-row">
                                                    <span class="review-label">Subaccount:</span>
                                                    <span class="review-value" id="reviewSubaccount"></span>
                                                </div>
                                            </div>
                                            
                                            <div class="review-section">
                                                <div class="review-section-title"><i class="fas fa-envelope me-2"></i>Use Case</div>
                                                <div class="review-row">
                                                    <span class="review-label">Primary Use:</span>
                                                    <span class="review-value" id="reviewUseCase"></span>
                                                </div>
                                                <div class="review-row">
                                                    <span class="review-label">Description:</span>
                                                    <span class="review-value" id="reviewDescription"></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info mt-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            After submission, your SenderID will be reviewed by our compliance team. This typically takes 1-2 business days.
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
    var selectedType = 'alphanumeric';
    
    $('#senderIdWizard').smartWizard({
        selected: 0,
        theme: 'arrows',
        transition: {
            animation: 'fade'
        },
        toolbar: {
            position: 'bottom',
            showNextButton: true,
            showPreviousButton: true,
            extraHtml: '<button class="btn btn-save-draft me-2" type="button" id="btnSaveDraft"><i class="fas fa-save me-1"></i>Save Draft</button>' +
                       '<button class="btn btn-success" type="button" id="btnSubmit" style="display:none;"><i class="fas fa-paper-plane me-1"></i>Submit for Approval</button>'
        },
        anchor: {
            enableNavigation: true,
            enableNavigationAlways: false,
            enableDoneState: true,
            markPreviousStepsAsDone: true,
            markAllPreviousStepsAsDone: true,
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

    $('.sw-btn-next').html('Next <i class="fas fa-arrow-right ms-1"></i>');
    $('.sw-btn-prev').html('<i class="fas fa-arrow-left me-1"></i> Previous');

    $('#senderIdWizard').on('showStep', function(e, anchorObject, stepIndex, stepDirection) {
        if (stepIndex === 4) {
            updateReviewSummary();
            $('.sw-btn-next').hide();
            $('#btnSubmit').show();
        } else {
            $('.sw-btn-next').show();
            $('#btnSubmit').hide();
        }
    });

    $('#senderIdWizard').on('leaveStep', function(e, anchorObject, stepIndex, stepDirection) {
        if (stepDirection === 'forward') {
            return validateStep(stepIndex);
        }
        return true;
    });

    $('.type-card').on('click', function() {
        $('.type-card').removeClass('selected');
        $(this).addClass('selected');
        selectedType = $(this).data('type');
        $('#inputType').val(selectedType);
        updateSenderIdInput();
    });

    function updateSenderIdInput() {
        var $input = $('#inputSenderId');
        var $hint = $('#senderIdHint');
        var $charWrapper = $('#senderIdCharCount').parent();
        
        if (selectedType === 'alphanumeric') {
            $input.attr('maxlength', '11').attr('placeholder', 'e.g. MyBrand');
            $hint.text('Max 11 characters: A-Z a-z 0-9 . - _ & space');
            $charWrapper.show();
        } else if (selectedType === 'numeric') {
            $input.attr('maxlength', '15').attr('placeholder', 'e.g. 447700900123');
            $hint.text('UK Virtual Mobile Number format: 447xxxxxxxxx');
            $charWrapper.hide();
        } else if (selectedType === 'shortcode') {
            $input.attr('maxlength', '5').attr('placeholder', 'e.g. 60123');
            $hint.text('Exactly 5 digits, starting with 6, 7, or 8');
            $charWrapper.hide();
        }
        $input.val('');
        $('#senderIdCharCount').text('0');
    }

    $('#inputSenderId').on('input', function() {
        var val = $(this).val();
        $('#senderIdCharCount').text(val.length);
    });

    $('#inputPermission').on('change', function() {
        var val = $(this).val();
        if (val === 'yes') {
            $('#permissionBlockedAlert').hide();
            $('#confirmationSection, #explanationSection').show();
        } else if (val === 'no') {
            $('#permissionBlockedAlert').show();
            $('#confirmationSection, #explanationSection').hide();
        } else {
            $('#permissionBlockedAlert, #confirmationSection, #explanationSection').hide();
        }
    });

    function validateStep(stepIndex) {
        var isValid = true;
        
        if (stepIndex === 0) {
            var senderId = $('#inputSenderId').val().trim();
            if (!senderId) {
                $('#inputSenderId').addClass('is-invalid');
                $('#senderIdError').text('SenderID value is required').show();
                isValid = false;
            } else {
                $('#inputSenderId').removeClass('is-invalid');
                $('#senderIdError').hide();
            }
        } else if (stepIndex === 1) {
            var brand = $('#inputBrand').val().trim();
            if (!brand) {
                $('#inputBrand').addClass('is-invalid');
                $('#brandError').text('Brand name is required').show();
                isValid = false;
            } else {
                $('#inputBrand').removeClass('is-invalid');
                $('#brandError').hide();
            }
        } else if (stepIndex === 2) {
            var permission = $('#inputPermission').val();
            if (!permission) {
                $('#inputPermission').addClass('is-invalid');
                $('#permissionError').text('Please select an option').show();
                isValid = false;
            } else if (permission === 'no') {
                isValid = false;
            } else if (permission === 'yes' && !$('#inputConfirmAuthorised').is(':checked')) {
                $('#confirmError').text('You must confirm authorisation').show();
                isValid = false;
            } else {
                $('#inputPermission').removeClass('is-invalid');
                $('#permissionError, #confirmError').hide();
            }
        } else if (stepIndex === 3) {
            var useCase = $('#inputUseCase').val();
            if (!useCase) {
                $('#inputUseCase').addClass('is-invalid');
                $('#useCaseError').text('Please select a use case').show();
                isValid = false;
            } else {
                $('#inputUseCase').removeClass('is-invalid');
                $('#useCaseError').hide();
            }
        }
        
        return isValid;
    }

    function updateReviewSummary() {
        var typeLabels = {
            'alphanumeric': 'Alphanumeric',
            'numeric': 'Numeric (UK VMN)',
            'shortcode': 'Shortcode'
        };
        var useCaseLabels = {
            'transactional': 'Transactional',
            'marketing': 'Promotional',
            'otp': 'OTP / Verification',
            'mixed': 'Mixed'
        };
        
        $('#reviewType').text(typeLabels[selectedType] || selectedType);
        $('#reviewSenderId').text($('#inputSenderId').val());
        $('#reviewBrand').text($('#inputBrand').val());
        $('#reviewSubaccount').text($('#inputSubaccount option:selected').text() || 'Main Account');
        $('#reviewUseCase').text(useCaseLabels[$('#inputUseCase').val()] || '-');
        $('#reviewDescription').text($('#inputDescription').val() || '-');
    }

    $('#btnSaveDraft').on('click', function() {
        $('#autosaveIndicator').removeClass('saved').addClass('saving');
        $('#autosaveText').text('Saving...');
        
        setTimeout(function() {
            $('#autosaveIndicator').removeClass('saving').addClass('saved');
            $('#autosaveText').text('Draft saved');
        }, 1000);
    });

    $('#btnSubmit').on('click', function() {
        if (confirm('Are you sure you want to submit this SenderID for approval?')) {
            alert('SenderID submitted for approval!');
            window.location.href = '{{ route("management.sms-sender-id") }}';
        }
    });
});
</script>
@endpush

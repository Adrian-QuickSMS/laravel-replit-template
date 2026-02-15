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
.modal-backdrop {
    z-index: 1040 !important;
}
#submitConfirmModal {
    z-index: 1050 !important;
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
                                                <small class="text-muted" id="senderIdHint">Max 11 characters: A-Z a-z 0-9 - _ & space</small>
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
                                            <label class="form-label fw-semibold">Subaccount(s)</label>
                                            <div class="multiselect-wrapper">
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input" type="checkbox" id="selectAllSubaccounts">
                                                    <label class="form-check-label small text-muted" for="selectAllSubaccounts">Select All</label>
                                                </div>
                                                <select class="form-select" id="inputSubaccount" multiple size="4">
                                                    @forelse($sub_accounts as $sa)
                                                        <option value="{{ $sa->id }}">{{ $sa->name }}</option>
                                                    @empty
                                                        <option value="main" selected>Main Account (no sub-accounts configured)</option>
                                                    @endforelse
                                                </select>
                                            </div>
                                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                        </div>

                                        <div class="mb-3" id="usersSection" style="display: none;">
                                            <label class="form-label fw-semibold">Users</label>
                                            <div class="multiselect-wrapper">
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                                    <label class="form-check-label small text-muted" for="selectAllUsers">Select All</label>
                                                </div>
                                                <select class="form-select" id="inputUsers" multiple size="4">
                                                </select>
                                            </div>
                                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
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
                                                    <span class="review-label">Subaccount(s):</span>
                                                    <span class="review-value" id="reviewSubaccount"></span>
                                                </div>
                                                <div class="review-row" id="reviewUsersRow" style="display: none;">
                                                    <span class="review-label">Users:</span>
                                                    <span class="review-value" id="reviewUsers"></span>
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

<div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-labelledby="submitConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitConfirmModalLabel">Submit for Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit this SenderID for approval?</p>
                <p class="text-muted small mb-0">Once submitted, your registration will be reviewed by our compliance team. This typically takes 1-2 business days.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSubmitBtn">Submit</button>
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
    var serverValidationTimer = null;

    var typeMap = {
        'alphanumeric': 'ALPHA',
        'numeric': 'NUMERIC',
        'shortcode': 'SHORTCODE'
    };

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
                       '<button class="btn btn-success text-white" type="button" id="btnSubmit" style="display:none;"><i class="fas fa-paper-plane me-1"></i>Submit for Approval</button>'
        },
        anchor: {
            enableNavigation: false,
            enableNavigationAlways: false,
            enableDoneState: true,
            markPreviousStepsAsDone: true,
            markAllPreviousStepsAsDone: true,
            removeDoneStepOnNavigateBack: false,
            enableDoneStateNavigation: false
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
    
    $('#btnSaveDraft').insertBefore('.sw-btn-prev');

    $('#selectAllSubaccounts').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#inputSubaccount option').prop('selected', isChecked);
        $('#inputSubaccount').trigger('change');
    });

    $('#inputSubaccount').on('change', function() {
        var selectedSubaccounts = $(this).val() || [];
        var totalOptions = $('#inputSubaccount option').length;
        $('#selectAllSubaccounts').prop('checked', selectedSubaccounts.length === totalOptions);

        if (selectedSubaccounts.length > 0) {
            $('#inputUsers').html('');
            $('#selectAllUsers').prop('checked', false);
            $('#usersSection').show();

            $.ajax({
                url: '/api/sub-accounts/users',
                method: 'POST',
                data: JSON.stringify({ sub_account_ids: selectedSubaccounts }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    var usersHtml = '';
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(user) {
                            usersHtml += '<option value="' + user.id + '">' + user.name + '</option>';
                        });
                        $('#inputUsers').html(usersHtml);
                    } else {
                        $('#usersSection').hide();
                    }
                },
                error: function() {
                    $('#usersSection').hide();
                }
            });
        } else {
            $('#inputUsers').html('');
            $('#selectAllUsers').prop('checked', false);
            $('#usersSection').hide();
        }
    });

    $('#selectAllUsers').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#inputUsers option').prop('selected', isChecked);
    });

    $('#inputUsers').on('change', function() {
        var selectedUsers = $(this).val() || [];
        var totalOptions = $('#inputUsers option').length;
        $('#selectAllUsers').prop('checked', selectedUsers.length === totalOptions && totalOptions > 0);
    });

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
            var isValid = validateStep(stepIndex);
            if (!isValid) {
                e.preventDefault();
                return false;
            }
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
            $hint.text('Max 11 characters: A-Z a-z 0-9 - _ & space');
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
        $('#inputSenderId').removeClass('is-invalid');
        $('#senderIdError').hide();
    }

    $('#inputSenderId').on('input', function() {
        var val = $(this).val();
        $('#senderIdCharCount').text(val.length);
        
        // Real-time validation feedback
        var $input = $(this);
        var $error = $('#senderIdError');
        
        $input.removeClass('is-invalid');
        $error.removeClass('d-block').hide();
        
        if (!val) {
            // Empty is OK while typing, only show error on Next
            return;
        }
        
        var hasError = false;
        var errorMsg = '';
        
        if (selectedType === 'alphanumeric') {
            var alphaPattern = /^[A-Za-z0-9\-_& ]+$/;
            if (!alphaPattern.test(val)) {
                hasError = true;
                errorMsg = 'Only A-Z, a-z, 0-9, -, _, &, and space are allowed';
            } else if (val.length > 11) {
                hasError = true;
                errorMsg = 'Maximum 11 characters allowed';
            }
        } else if (selectedType === 'numeric') {
            // Check if input contains only digits
            if (!/^\d*$/.test(val)) {
                hasError = true;
                errorMsg = 'Only numbers are allowed';
            } else if (val.length > 0) {
                // Check if it starts with 447
                if (!val.startsWith('447') && val.length >= 3) {
                    hasError = true;
                    errorMsg = 'UK mobile number must start with 447';
                } else if (val.length > 12) {
                    hasError = true;
                    errorMsg = 'UK mobile number must be exactly 12 digits';
                }
            }
        } else if (selectedType === 'shortcode') {
            if (!/^\d*$/.test(val)) {
                hasError = true;
                errorMsg = 'Only numbers are allowed';
            } else if (val.length >= 1) {
                var firstDigit = val.charAt(0);
                if (firstDigit !== '6' && firstDigit !== '7' && firstDigit !== '8') {
                    hasError = true;
                    errorMsg = 'Shortcode must start with 6, 7, or 8';
                }
            }
            if (!hasError && val.length > 5) {
                hasError = true;
                errorMsg = 'Shortcode must be exactly 5 digits';
            }
        }
        
        if (hasError) {
            $input.addClass('is-invalid');
            $error.text(errorMsg).addClass('d-block').show();
        }
    });

    $('#inputSenderId').on('blur', function() {
        var inputValue = $(this).val().trim();
        if (!inputValue) return;
        if ($(this).hasClass('is-invalid')) return;

        if (serverValidationTimer) clearTimeout(serverValidationTimer);
        serverValidationTimer = setTimeout(function() {
            $.ajax({
                url: '/api/sender-ids/validate',
                method: 'POST',
                data: JSON.stringify({
                    sender_id_value: inputValue,
                    sender_type: typeMap[selectedType]
                }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (!response.valid) {
                        $('#inputSenderId').addClass('is-invalid');
                        var msgs = (response.errors || []).join('. ');
                        $('#senderIdError').text(msgs).addClass('d-block').show();
                    }
                    if (response.spoofing && !response.spoofing.passed) {
                        var warningHtml = '<div class="alert alert-warning mt-2" id="spoofingWarning">' +
                            '<i class="fas fa-exclamation-triangle me-2"></i>' +
                            '<strong>Anti-Spoofing Warning:</strong> This SenderID may be flagged. ' +
                            'Normalised form: <code>' + (response.spoofing.normalised || '') + '</code>. ' +
                            'Action: ' + (response.spoofing.action || 'review') +
                            '</div>';
                        $('#spoofingWarning').remove();
                        $('#inputSenderId').closest('.mb-3').append(warningHtml);
                    } else {
                        $('#spoofingWarning').remove();
                    }
                }
            });
        }, 500);
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
            $('#inputSenderId').removeClass('is-invalid');
            $('#senderIdError').removeClass('d-block').hide();
            
            if (!senderId) {
                $('#inputSenderId').addClass('is-invalid');
                $('#senderIdError').text('SenderID value is required').addClass('d-block').show();
                isValid = false;
            } else if (selectedType === 'alphanumeric') {
                var alphaPattern = /^[A-Za-z0-9\-_& ]+$/;
                if (!alphaPattern.test(senderId)) {
                    $('#inputSenderId').addClass('is-invalid');
                    $('#senderIdError').text('Only A-Z, a-z, 0-9, -, _, &, and space are allowed').addClass('d-block').show();
                    isValid = false;
                } else if (senderId.length > 11) {
                    $('#inputSenderId').addClass('is-invalid');
                    $('#senderIdError').text('Maximum 11 characters allowed').addClass('d-block').show();
                    isValid = false;
                }
            } else if (selectedType === 'numeric') {
                var numericPattern = /^447\d{9}$/;
                if (!numericPattern.test(senderId)) {
                    $('#inputSenderId').addClass('is-invalid');
                    $('#senderIdError').text('Must be a valid UK mobile number starting with 447 (12 digits total)').addClass('d-block').show();
                    isValid = false;
                }
            } else if (selectedType === 'shortcode') {
                var shortcodePattern = /^[678]\d{4}$/;
                if (!shortcodePattern.test(senderId)) {
                    $('#inputSenderId').addClass('is-invalid');
                    $('#senderIdError').text('Must be exactly 5 digits starting with 6, 7, or 8').addClass('d-block').show();
                    isValid = false;
                }
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
        
        var selectedSubaccounts = $('#inputSubaccount option:selected').map(function() {
            return $(this).text();
        }).get();
        $('#reviewSubaccount').text(selectedSubaccounts.length > 0 ? selectedSubaccounts.join(', ') : 'None selected');
        
        var selectedUsers = $('#inputUsers option:selected').map(function() {
            return $(this).text();
        }).get();
        if (selectedUsers.length > 0) {
            $('#reviewUsers').text(selectedUsers.join(', '));
            $('#reviewUsersRow').show();
        } else {
            $('#reviewUsersRow').hide();
        }
        
        $('#reviewUseCase').text(useCaseLabels[$('#inputUseCase').val()] || '-');
        $('#reviewDescription').text($('#inputDescription').val() || '-');
    }

    $('#btnSaveDraft').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);
        $('#autosaveIndicator').removeClass('saved').addClass('saving');
        $('#autosaveText').text('Saving...');

        var payload = {
            sender_id_value: $('#inputSenderId').val().trim(),
            sender_type: typeMap[selectedType],
            brand_name: $('#inputBrand').val().trim() || 'Draft',
            country_code: 'GB',
            use_case: $('#inputUseCase').val() || 'transactional',
            use_case_description: $('#inputDescription').val().trim(),
            permission_confirmed: $('#inputConfirmAuthorised').is(':checked'),
            permission_explanation: $('#inputExplanation').val().trim(),
            sub_account_ids: $('#inputSubaccount').val() || [],
            user_ids: $('#inputUsers').val() || [],
            submit: false
        };

        $.ajax({
            url: '/api/sender-ids',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function() {
                $('#autosaveIndicator').removeClass('saving').addClass('saved');
                $('#autosaveText').text('Draft saved');
                $btn.prop('disabled', false);
            },
            error: function(xhr) {
                $('#autosaveIndicator').removeClass('saving saved');
                $('#autosaveText').text('Save failed');
                $btn.prop('disabled', false);
            }
        });
    });

    $('#btnSubmit').on('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('submitConfirmModal'));
        modal.show();
    });

    $('#confirmSubmitBtn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Submitting...');

        var payload = {
            sender_id_value: $('#inputSenderId').val().trim(),
            sender_type: typeMap[selectedType],
            brand_name: $('#inputBrand').val().trim(),
            country_code: 'GB',
            use_case: $('#inputUseCase').val(),
            use_case_description: $('#inputDescription').val().trim(),
            permission_confirmed: $('#inputConfirmAuthorised').is(':checked'),
            permission_explanation: $('#inputExplanation').val().trim(),
            sub_account_ids: $('#inputSubaccount').val() || [],
            user_ids: $('#inputUsers').val() || [],
            submit: true
        };

        $.ajax({
            url: '/api/sender-ids',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function() {
                var modal = bootstrap.Modal.getInstance(document.getElementById('submitConfirmModal'));
                modal.hide();
                window.location.href = '{{ route("management.sms-sender-id") }}?created=1';
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Submit');
                var resp = xhr.responseJSON;
                var modal = bootstrap.Modal.getInstance(document.getElementById('submitConfirmModal'));
                modal.hide();

                var errorMsg = 'Failed to submit SenderID. Please try again.';
                if (resp && resp.errors && resp.errors.length > 0) {
                    errorMsg = resp.errors.join('<br>');
                } else if (resp && resp.error) {
                    errorMsg = resp.error;
                }

                var alertHtml = '<div class="alert alert-danger alert-dismissible fade show mt-3" id="submitErrorAlert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>' + errorMsg +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                $('#submitErrorAlert').remove();
                $('#step-review .col-lg-8').prepend(alertHtml);
            }
        });
    });
});
</script>
@endpush

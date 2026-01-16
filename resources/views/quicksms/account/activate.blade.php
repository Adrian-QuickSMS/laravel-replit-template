@extends('layouts.quicksms')

@section('title', $page_title ?? 'Activate Your Account')

@push('styles')
<style>
    .activate-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .activate-header {
        text-align: center;
        padding: 2rem 0;
    }
    .activate-header .icon-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
    .activate-header .icon-circle i {
        font-size: 2rem;
        color: #886cc0;
    }
    .step-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    .step-card.completed {
        border-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    }
    .step-card.current {
        border-color: #886cc0;
        box-shadow: 0 0 0 3px rgba(136, 108, 192, 0.1);
    }
    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .step-card.completed .step-number {
        background: #10b981;
        color: white;
    }
    .step-card.current .step-number {
        background: #886cc0;
        color: white;
    }
    .step-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    .step-description {
        color: #6b7280;
        font-size: 0.875rem;
    }
    .requirement-list {
        list-style: none;
        padding: 0;
        margin: 1rem 0 0;
    }
    .requirement-list li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
        font-size: 0.875rem;
        color: #4b5563;
    }
    .requirement-list li i.complete {
        color: #10b981;
    }
    .requirement-list li i.pending {
        color: #d1d5db;
    }
    .help-card {
        background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
    }
    
    /* Modal Styles */
    #completeDetailsModal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    #completeDetailsModal .modal-header {
        background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%);
        border-bottom: 1px solid #e9d5ff;
        border-radius: 16px 16px 0 0;
        padding: 1.25rem 1.5rem;
    }
    #completeDetailsModal .modal-title {
        font-weight: 600;
        color: #1f2937;
    }
    #completeDetailsModal .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    #completeDetailsModal .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
    }
    .form-section {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .form-section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .form-section-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #374151;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-title i {
        color: #886cc0;
    }
    .form-label .required {
        color: #ef4444;
    }
    .field-status {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.875rem;
    }
    .field-status.valid {
        color: #10b981;
    }
    .field-status.invalid {
        color: #ef4444;
    }
    .input-with-status {
        position: relative;
    }
    .input-with-status input,
    .input-with-status select {
        padding-right: 2.5rem;
    }
    .progress-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        background: #f9fafb;
        border-radius: 8px;
    }
    .progress-indicator .progress {
        flex-grow: 1;
        height: 8px;
        border-radius: 4px;
    }
    .progress-indicator .progress-bar {
        background: linear-gradient(90deg, #886cc0 0%, #a78bfa 100%);
        border-radius: 4px;
    }
    .progress-text {
        font-size: 0.75rem;
        color: #6b7280;
        min-width: 60px;
        text-align: right;
    }
</style>
@endpush

@section('content')
<div class="activate-container">
    <div class="activate-header">
        <div class="icon-circle">
            <i class="fas fa-rocket"></i>
        </div>
        <h2 class="mb-2">Activate Your Account</h2>
        <p class="text-muted">Complete the steps below to unlock full messaging capabilities</p>
    </div>

    <div id="activation-steps">
        <div class="step-card" id="step-details" data-step="1">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">1</div>
                <div class="flex-grow-1">
                    <div class="step-title">Complete Account Details</div>
                    <div class="step-description">Provide your company information for compliance and billing</div>
                    <ul class="requirement-list" id="details-requirements">
                        <li><i class="fas fa-circle pending" id="req-company"></i> Company name</li>
                        <li><i class="fas fa-circle pending" id="req-address"></i> Business address</li>
                        <li><i class="fas fa-circle pending" id="req-website"></i> Website</li>
                        <li><i class="fas fa-circle pending" id="req-sector"></i> Business sector</li>
                        <li><i class="fas fa-circle pending" id="req-vat"></i> VAT information</li>
                    </ul>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="btn-complete-details" data-bs-toggle="modal" data-bs-target="#completeDetailsModal">
                        <i class="fas fa-edit me-1"></i> Complete Details
                    </button>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step1-status">Pending</span>
                </div>
            </div>
        </div>

        <div class="step-card" id="step-payment" data-step="2">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">2</div>
                <div class="flex-grow-1">
                    <div class="step-title">Make Your First Payment</div>
                    <div class="step-description">Purchase message credits to activate your account</div>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Once you complete your account details, you can purchase message credits to start sending live messages.
                    </p>
                    <button class="btn btn-sm btn-primary mt-3" id="btn-purchase" disabled>
                        <i class="fas fa-credit-card me-1"></i> Purchase Credits
                    </button>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step2-status">Waiting</span>
                </div>
            </div>
        </div>

        <div class="step-card" id="step-activated" data-step="3">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">3</div>
                <div class="flex-grow-1">
                    <div class="step-title">Account Activated</div>
                    <div class="step-description">Your account is fully activated and ready for live messaging</div>
                    <div class="mt-3" id="activated-actions" style="display: none;">
                        <a href="{{ route('messages.send') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-paper-plane me-1"></i> Send Your First Message
                        </a>
                    </div>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step3-status">Pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="help-card">
        <div class="d-flex align-items-start gap-3">
            <div>
                <i class="fas fa-question-circle" style="font-size: 1.5rem; color: #886cc0;"></i>
            </div>
            <div>
                <h6 class="mb-1">Need Help?</h6>
                <p class="text-muted small mb-2">
                    If you have questions about activation or need assistance, our support team is here to help.
                </p>
                <a href="{{ route('support.create-ticket') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-headset me-1"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Complete Details Modal -->
<div class="modal fade" id="completeDetailsModal" tabindex="-1" aria-labelledby="completeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="completeDetailsModalLabel">
                        <i class="fas fa-building me-2"></i>Complete Account Details
                    </h5>
                    <p class="text-muted small mb-0 mt-1">This information is required to activate your account</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="progress-indicator">
                    <span class="small text-muted">Completion:</span>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" id="form-progress"></div>
                    </div>
                    <span class="progress-text" id="form-progress-text">0 of 5</span>
                </div>

                <form id="accountDetailsForm">
                    <!-- Company Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-building"></i> Company Information
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="company_name" class="form-label">Company Name <span class="required">*</span></label>
                                <div class="input-with-status">
                                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter your legal company name" required>
                                    <span class="field-status" id="status-company"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="trading_name" class="form-label">Trading Name <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="trading_name" name="trading_name" placeholder="If different from company name">
                            </div>
                            <div class="col-md-6">
                                <label for="company_number" class="form-label">Company Number <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="company_number" name="company_number" placeholder="e.g. 12345678">
                            </div>
                        </div>
                    </div>

                    <!-- Business Address -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-map-marker-alt"></i> Business Address
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="address_line1" class="form-label">Address Line 1 <span class="required">*</span></label>
                                <div class="input-with-status">
                                    <input type="text" class="form-control" id="address_line1" name="address_line1" placeholder="Street address" required>
                                    <span class="field-status" id="status-address"></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="address_line2" class="form-label">Address Line 2 <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="address_line2" name="address_line2" placeholder="Apartment, suite, etc.">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">City <span class="required">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="City" required>
                            </div>
                            <div class="col-md-3">
                                <label for="postcode" class="form-label">Postcode <span class="required">*</span></label>
                                <input type="text" class="form-control" id="postcode" name="postcode" placeholder="Postcode" required>
                            </div>
                            <div class="col-md-3">
                                <label for="country" class="form-label">Country <span class="required">*</span></label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select...</option>
                                    <option value="GB" selected>United Kingdom</option>
                                    <option value="IE">Ireland</option>
                                    <option value="DE">Germany</option>
                                    <option value="FR">France</option>
                                    <option value="NL">Netherlands</option>
                                    <option value="BE">Belgium</option>
                                    <option value="ES">Spain</option>
                                    <option value="IT">Italy</option>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="AU">Australia</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Website & Sector -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-globe"></i> Business Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="website" class="form-label">Website <span class="required">*</span></label>
                                <div class="input-with-status">
                                    <input type="url" class="form-control" id="website" name="website" placeholder="https://www.example.com" required>
                                    <span class="field-status" id="status-website"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="sector" class="form-label">Business Sector <span class="required">*</span></label>
                                <div class="input-with-status">
                                    <select class="form-select" id="sector" name="sector" required>
                                        <option value="">Select sector...</option>
                                        <option value="retail">Retail & E-commerce</option>
                                        <option value="healthcare">Healthcare</option>
                                        <option value="finance">Finance & Banking</option>
                                        <option value="technology">Technology</option>
                                        <option value="education">Education</option>
                                        <option value="hospitality">Hospitality & Travel</option>
                                        <option value="manufacturing">Manufacturing</option>
                                        <option value="logistics">Logistics & Transport</option>
                                        <option value="professional_services">Professional Services</option>
                                        <option value="government">Government & Public Sector</option>
                                        <option value="nhs">NHS & Health Services</option>
                                        <option value="charity">Charity & Non-profit</option>
                                        <option value="real_estate">Real Estate</option>
                                        <option value="utilities">Utilities</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <span class="field-status" id="status-sector"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VAT Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-receipt"></i> VAT Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="vat_registered" class="form-label">VAT Registered? <span class="required">*</span></label>
                                <div class="input-with-status">
                                    <select class="form-select" id="vat_registered" name="vat_registered" required>
                                        <option value="">Select...</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                    <span class="field-status" id="status-vat"></span>
                                </div>
                            </div>
                            <div class="col-md-6" id="vat-number-group" style="display: none;">
                                <label for="vat_number" class="form-label">VAT Number <span class="required">*</span></label>
                                <input type="text" class="form-control" id="vat_number" name="vat_number" placeholder="e.g. GB123456789">
                                <div class="form-text">Include country prefix (e.g. GB, IE, DE)</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-details" disabled>
                    <i class="fas fa-save me-1"></i> Save & Continue
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/quicksms-account-lifecycle.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var lifecycle = window.AccountLifecycle;
    
    // Form elements
    var form = document.getElementById('accountDetailsForm');
    var saveBtn = document.getElementById('btn-save-details');
    var progressBar = document.getElementById('form-progress');
    var progressText = document.getElementById('form-progress-text');
    
    // Required fields for validation
    var requiredFields = {
        company: { field: 'company_name', status: 'status-company', req: 'req-company' },
        address: { fields: ['address_line1', 'city', 'postcode', 'country'], status: 'status-address', req: 'req-address' },
        website: { field: 'website', status: 'status-website', req: 'req-website' },
        sector: { field: 'sector', status: 'status-sector', req: 'req-sector' },
        vat: { field: 'vat_registered', status: 'status-vat', req: 'req-vat' }
    };
    
    // Load existing data from localStorage/sessionStorage
    function loadSavedData() {
        var saved = localStorage.getItem('account_details');
        if (saved) {
            try {
                var data = JSON.parse(saved);
                Object.keys(data).forEach(function(key) {
                    var el = document.getElementById(key);
                    if (el) el.value = data[key] || '';
                });
                
                // Handle VAT number visibility
                if (data.vat_registered === 'yes') {
                    document.getElementById('vat-number-group').style.display = 'block';
                }
                
                validateForm();
            } catch (e) {
                console.error('Error loading saved data:', e);
            }
        }
    }
    
    // Validate a single field group
    function validateFieldGroup(groupKey) {
        var group = requiredFields[groupKey];
        var isValid = false;
        
        if (group.fields) {
            // Multiple fields (address)
            isValid = group.fields.every(function(fieldId) {
                var el = document.getElementById(fieldId);
                return el && el.value.trim() !== '';
            });
        } else {
            // Single field
            var el = document.getElementById(group.field);
            isValid = el && el.value.trim() !== '';
            
            // Special validation for website
            if (groupKey === 'website' && isValid) {
                try {
                    new URL(el.value);
                } catch (e) {
                    isValid = false;
                }
            }
        }
        
        // Update status icon in form
        var statusEl = document.getElementById(group.status);
        if (statusEl) {
            statusEl.innerHTML = isValid ? '<i class="fas fa-check-circle valid"></i>' : '';
        }
        
        // Update requirement list icon
        var reqEl = document.getElementById(group.req);
        if (reqEl) {
            reqEl.classList.remove('fa-circle', 'fa-check-circle', 'pending', 'complete');
            if (isValid) {
                reqEl.classList.add('fa-check-circle', 'complete');
            } else {
                reqEl.classList.add('fa-circle', 'pending');
            }
        }
        
        return isValid;
    }
    
    // Validate entire form
    function validateForm() {
        var validCount = 0;
        var totalCount = Object.keys(requiredFields).length;
        
        Object.keys(requiredFields).forEach(function(key) {
            if (validateFieldGroup(key)) {
                validCount++;
            }
        });
        
        // Update progress
        var percent = Math.round((validCount / totalCount) * 100);
        progressBar.style.width = percent + '%';
        progressText.textContent = validCount + ' of ' + totalCount;
        
        // Enable/disable save button
        saveBtn.disabled = validCount < totalCount;
        
        return validCount === totalCount;
    }
    
    // VAT registered toggle
    document.getElementById('vat_registered').addEventListener('change', function() {
        var vatGroup = document.getElementById('vat-number-group');
        vatGroup.style.display = this.value === 'yes' ? 'block' : 'none';
        
        var vatInput = document.getElementById('vat_number');
        if (this.value === 'yes') {
            vatInput.setAttribute('required', 'required');
        } else {
            vatInput.removeAttribute('required');
            vatInput.value = '';
        }
        validateForm();
    });
    
    // Add validation listeners to all form inputs
    form.querySelectorAll('input, select').forEach(function(input) {
        input.addEventListener('input', validateForm);
        input.addEventListener('change', validateForm);
    });
    
    // Save details
    saveBtn.addEventListener('click', function() {
        if (!validateForm()) return;
        
        var formData = new FormData(form);
        var data = {};
        formData.forEach(function(value, key) {
            data[key] = value;
        });
        
        // Save to localStorage (syncs with Account Details page)
        localStorage.setItem('account_details', JSON.stringify(data));
        
        // Mark account details as complete in lifecycle
        if (lifecycle) {
            lifecycle.setActivationStatus('account_details_complete', true);
            lifecycle.onAccountDetailsComplete(function(result) {
                console.log('Account details saved:', result);
            });
            
            // Log audit
            lifecycle.logAccountDetailsUpdate(Object.keys(data));
        }
        
        // Close modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('completeDetailsModal'));
        modal.hide();
        
        // Update UI
        updateUI();
        
        // Show success toast
        showToast('Account details saved successfully!', 'success');
    });
    
    function showToast(message, type) {
        var toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '1100';
        toast.innerHTML = '<div class="toast show align-items-center text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0" role="alert">' +
            '<div class="d-flex">' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div></div>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }
    
    function updateUI() {
        var activationStatus = lifecycle ? lifecycle.getActivationStatus() : { account_details_complete: false };
        var isLive = lifecycle ? lifecycle.isLive() : false;
        
        var step1 = document.getElementById('step-details');
        var step2 = document.getElementById('step-payment');
        var step3 = document.getElementById('step-activated');
        
        if (activationStatus.account_details_complete) {
            step1.classList.add('completed');
            step1.classList.remove('current');
            document.getElementById('step1-status').className = 'badge bg-success';
            document.getElementById('step1-status').textContent = 'Complete';
            
            document.querySelectorAll('#details-requirements i').forEach(function(icon) {
                icon.classList.remove('pending', 'fa-circle');
                icon.classList.add('complete', 'fa-check-circle');
            });
            
            document.getElementById('btn-complete-details').innerHTML = '<i class="fas fa-check me-1"></i> Edit Details';
            document.getElementById('btn-purchase').disabled = false;
            step2.classList.add('current');
            document.getElementById('step2-status').className = 'badge bg-warning text-dark';
            document.getElementById('step2-status').textContent = 'Ready';
        } else {
            step1.classList.add('current');
        }
        
        if (activationStatus.first_payment_made || isLive) {
            step2.classList.add('completed');
            step2.classList.remove('current');
            document.getElementById('step2-status').className = 'badge bg-success';
            document.getElementById('step2-status').textContent = 'Complete';
        }
        
        if (isLive) {
            step3.classList.add('completed');
            document.getElementById('step3-status').className = 'badge bg-success';
            document.getElementById('step3-status').textContent = 'Active';
            document.getElementById('activated-actions').style.display = 'block';
        }
    }
    
    document.getElementById('btn-purchase').addEventListener('click', function() {
        window.location.href = '{{ route("purchase.messages") }}';
    });
    
    // Initialize
    loadSavedData();
    updateUI();
    
    // Listen for lifecycle changes
    document.addEventListener('lifecycle:state_changed', function(e) {
        updateUI();
    });
});
</script>
@endpush

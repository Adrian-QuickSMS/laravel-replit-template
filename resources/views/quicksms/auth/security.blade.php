@extends('layouts.fullwidth')
@section('title', 'Security & Consent')
@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card mb-0 h-auto">
        <div class="card-body">
            <div class="text-center mb-3">
                <a href="{{ url('/') }}"><img class="logo-auth" src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 48px;"></a>
            </div>
            <h4 class="text-center mb-2">Set Up Your Account</h4>
            <p class="text-center text-muted mb-4">Step 2 of 3: Security & Consent</p>
            
            <div class="alert alert-success mb-4" id="verifiedBadge">
                <i class="fas fa-check-circle me-2"></i>
                Email verified: <strong id="verifiedEmail"></strong>
            </div>
            
            <form id="securityForm" novalidate>
                
                <div class="section-card mb-4">
                    <h6 class="section-title"><i class="fas fa-lock me-2"></i>A. Password Setup</h6>
                    <p class="section-helper">Create a strong password to secure your account.</p>
                    
                    <div class="form-group mb-3">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="password" placeholder="Minimum 8 characters" required>
                            <span class="show-pass eye">
                                <i class="fa fa-eye-slash"></i>
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback">Password must be at least 8 characters</div>
                        <div class="password-strength mt-2" id="passwordStrength"></div>
                        <small class="form-text text-muted">Use a mix of letters, numbers, and symbols for best security.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Re-enter password" required>
                        <div class="invalid-feedback" id="confirmError">Passwords do not match</div>
                    </div>
                </div>
                
                <div class="section-card mb-4">
                    <h6 class="section-title"><i class="fas fa-mobile-alt me-2"></i>B. Mobile Number & MFA</h6>
                    <p class="section-helper">Your mobile number is used for account recovery and two-factor authentication (MFA).</p>
                    
                    <div class="form-group mb-3">
                        <label class="form-label" for="mobileNumber">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="mobileNumber" placeholder="+44 7700 900123" required>
                        <div class="invalid-feedback">Please enter a valid mobile number</div>
                        <small class="form-text text-muted">Include country code (e.g., +44 for UK)</small>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="enableMfa">
                        <label class="form-check-label" for="enableMfa">
                            Enable Two-Factor Authentication (recommended)
                        </label>
                        <small class="d-block text-muted mt-1">You'll receive a verification code via SMS when signing in from a new device.</small>
                    </div>
                </div>
                
                <div class="section-card mb-4">
                    <h6 class="section-title"><i class="fas fa-shield-alt me-2"></i>C. Fraud Prevention & Validation Consent</h6>
                    <p class="section-helper">These agreements are required to protect you and ensure message delivery.</p>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="termsConsent" required>
                        <label class="form-check-label" for="termsConsent">
                            I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Acceptable Use Policy</a> <span class="text-danger">*</span>
                        </label>
                        <div class="invalid-feedback">You must agree to the terms to continue</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="privacyConsent" required>
                        <label class="form-check-label" for="privacyConsent">
                            I have read and accept the <a href="#" class="text-primary">Privacy Policy</a> <span class="text-danger">*</span>
                        </label>
                        <div class="invalid-feedback">You must accept the privacy policy to continue</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="fraudConsent" required>
                        <label class="form-check-label" for="fraudConsent">
                            I consent to fraud prevention checks and identity validation <span class="text-danger">*</span>
                        </label>
                        <div class="invalid-feedback">You must consent to fraud prevention to continue</div>
                        <small class="d-block text-muted mt-1">We may verify your identity and business details to prevent misuse of our platform.</small>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="contentConsent" required>
                        <label class="form-check-label" for="contentConsent">
                            I agree that all messages sent will comply with <a href="#" class="text-primary">UK messaging regulations</a> <span class="text-danger">*</span>
                        </label>
                        <div class="invalid-feedback">You must agree to messaging compliance</div>
                    </div>
                </div>
                
                <div class="section-card mb-4">
                    <h6 class="section-title"><i class="fas fa-envelope me-2"></i>D. Marketing Preferences</h6>
                    <p class="section-helper">Optional: Stay informed about product updates and offers.</p>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="marketingEmail">
                        <label class="form-check-label" for="marketingEmail">
                            I would like to receive product updates via email
                        </label>
                    </div>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="marketingSms">
                        <label class="form-check-label" for="marketingSms">
                            I would like to receive promotional offers via SMS
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="partnerOffers">
                        <label class="form-check-label" for="partnerOffers">
                            I'm interested in partner offers and integrations
                        </label>
                    </div>
                </div>
                
                <div class="section-card section-card-highlight mb-4">
                    <h6 class="section-title"><i class="fas fa-gift me-2 text-success"></i>E. Test Credit Eligibility</h6>
                    <div class="d-flex align-items-start">
                        <div class="test-credit-icon me-3">
                            <i class="fas fa-coins text-warning"></i>
                        </div>
                        <div>
                            <p class="mb-2"><strong>You're eligible for free test credits!</strong></p>
                            <p class="text-muted mb-0 small">Once your account is verified, you'll receive <strong>10 free SMS credits</strong> to test our platform. No payment required to get started.</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-block" id="continueBtn">
                        <span class="btn-text">Continue to Step 3 <i class="fas fa-arrow-right ms-2"></i></span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Processing...
                        </span>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">Need help? <a class="text-primary" href="#">Contact Support</a></small>
            </div>
        </div>
    </div>
</div>

<style>
.logo-auth {
    max-height: 48px;
}
.form-control:focus, .form-select:focus {
    border-color: rgba(136, 108, 192, 0.5);
    box-shadow: 0 0 0 0.2rem rgba(136, 108, 192, 0.15);
}
.btn-primary {
    background-color: #886CC0;
    border-color: #886CC0;
}
.btn-primary:hover, .btn-primary:focus {
    background-color: #7358a8;
    border-color: #7358a8;
}
.text-primary {
    color: #886CC0 !important;
}
.card {
    border: none;
    box-shadow: 0 0 35px 0 rgba(154, 161, 171, 0.15);
}
.section-card {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    border-left: 3px solid #886CC0;
}
.section-card-highlight {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-left-color: #22c55e;
}
.section-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c2c2c;
    margin-bottom: 0.5rem;
}
.section-helper {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 1rem;
}
.password-strength {
    font-size: 0.75rem;
}
.password-strength .strength-bar {
    height: 4px;
    border-radius: 2px;
    margin-bottom: 4px;
    background: #e9ecef;
}
.password-strength .strength-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease;
}
.password-strength.weak .strength-fill { background: #dc3545; width: 33%; }
.password-strength.medium .strength-fill { background: #ffc107; width: 66%; }
.password-strength.strong .strength-fill { background: #28a745; width: 100%; }
.show-pass {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
}
.show-pass .fa-eye { display: none; }
.show-pass.active .fa-eye { display: inline; }
.show-pass.active .fa-eye-slash { display: none; }
.test-credit-icon {
    width: 48px;
    height: 48px;
    background: #fef3c7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.form-check-input:checked {
    background-color: #886CC0;
    border-color: #886CC0;
}
</style>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var email = urlParams.get('email');
    var verified = urlParams.get('verified');
    
    if (!verified || verified !== 'true') {
        window.location.href = '/signup';
        return;
    }
    
    if (email) {
        $('#verifiedEmail').text(email);
    } else {
        $('#verifiedBadge').hide();
    }
    
    $('.show-pass').on('click', function() {
        var $input = $(this).siblings('input');
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('active');
    });
    
    $('#password').on('input', function() {
        var password = $(this).val();
        var $strength = $('#passwordStrength');
        
        if (password.length === 0) {
            $strength.html('').removeClass('weak medium strong');
            return;
        }
        
        var strength = calculateStrength(password);
        $strength.html('<div class="strength-bar"><div class="strength-fill"></div></div><span>' + strength.label + '</span>');
        $strength.removeClass('weak medium strong').addClass(strength.class);
        
        if (password.length >= 8) {
            $(this).removeClass('is-invalid');
        }
    });
    
    function calculateStrength(password) {
        var score = 0;
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;
        
        if (score <= 2) return { class: 'weak', label: 'Weak password' };
        if (score <= 3) return { class: 'medium', label: 'Medium strength' };
        return { class: 'strong', label: 'Strong password' };
    }
    
    $('#confirmPassword').on('input', function() {
        var confirm = $(this).val();
        var password = $('#password').val();
        
        if (confirm && confirm !== password) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#securityForm').on('submit', function(e) {
        e.preventDefault();
        
        var isValid = true;
        var password = $('#password').val();
        var confirm = $('#confirmPassword').val();
        
        if (password.length < 8) {
            $('#password').addClass('is-invalid');
            isValid = false;
        }
        
        if (confirm !== password) {
            $('#confirmPassword').addClass('is-invalid');
            isValid = false;
        }
        
        if (!$('#mobileNumber').val().trim()) {
            $('#mobileNumber').addClass('is-invalid');
            isValid = false;
        }
        
        var requiredCheckboxes = ['termsConsent', 'privacyConsent', 'fraudConsent', 'contentConsent'];
        requiredCheckboxes.forEach(function(id) {
            var $checkbox = $('#' + id);
            if (!$checkbox.is(':checked')) {
                $checkbox.addClass('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 300);
            return;
        }
        
        var $btn = $('#continueBtn');
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        
        // Mock save security settings
        // In production: POST /api/auth/complete-security
        var formData = {
            email: email,
            password: password,
            mobile_number: $('#mobileNumber').val().trim(),
            enable_mfa: $('#enableMfa').is(':checked'),
            consents: {
                terms: true,
                privacy: true,
                fraud_prevention: true,
                content_compliance: true
            },
            marketing: {
                email: $('#marketingEmail').is(':checked'),
                sms: $('#marketingSms').is(':checked'),
                partners: $('#partnerOffers').is(':checked')
            }
        };
        
        console.log('[Security] Saving security settings:', formData);
        
        setTimeout(function() {
            window.location.href = '/signup/complete?email=' + encodeURIComponent(email);
        }, 1500);
    });
    
    $('input[required]').on('input change', function() {
        if ($(this).attr('type') === 'checkbox') {
            if ($(this).is(':checked')) $(this).removeClass('is-invalid');
        } else {
            if ($(this).val().trim()) $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush
@endsection

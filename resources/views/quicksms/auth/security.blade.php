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
                    <p class="section-helper">Create a strong password to secure your account. Password is hashed using Argon2id before storage.</p>
                    
                    <div class="form-group mb-3">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="password" placeholder="12-128 characters" required autocomplete="new-password" minlength="12" maxlength="128">
                            <span class="show-pass eye">
                                <i class="fa fa-eye-slash"></i>
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback" id="passwordError">Password does not meet requirements</div>
                        
                        <div class="password-rules mt-2" id="passwordRules">
                            <div class="rule-item" id="rule-length">
                                <i class="fas fa-circle rule-icon"></i>
                                <span>12-128 characters</span>
                            </div>
                            <div class="rule-item" id="rule-uppercase">
                                <i class="fas fa-circle rule-icon"></i>
                                <span>At least 1 uppercase letter (A-Z)</span>
                            </div>
                            <div class="rule-item" id="rule-lowercase">
                                <i class="fas fa-circle rule-icon"></i>
                                <span>At least 1 lowercase letter (a-z)</span>
                            </div>
                            <div class="rule-item" id="rule-number">
                                <i class="fas fa-circle rule-icon"></i>
                                <span>At least 1 number (0-9)</span>
                            </div>
                            <div class="rule-item" id="rule-special">
                                <i class="fas fa-circle rule-icon"></i>
                                <span>At least 1 special character</span>
                            </div>
                        </div>
                        <small class="form-text text-muted mt-2">Allowed special characters: ! @ £ $ % ^ & * ( ) _ - = + [ ] { } ; : ' " , . &lt; &gt; ? / \ | ~</small>
                        
                        <div class="password-check-status mt-2 d-none" id="passwordCheckStatus"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Re-enter password" required autocomplete="new-password">
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
.password-rules {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 0.75rem;
}
.rule-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #6c757d;
    padding: 0.25rem 0;
}
.rule-item .rule-icon {
    font-size: 0.5rem;
    color: #dee2e6;
    transition: color 0.2s ease;
}
.rule-item.valid .rule-icon {
    color: #28a745;
}
.rule-item.valid {
    color: #28a745;
}
.rule-item.invalid .rule-icon {
    color: #dc3545;
}
.rule-item.invalid {
    color: #dc3545;
}
.password-check-status {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
}
.password-check-status.checking {
    background: #e7f1ff;
    color: #0d6efd;
}
.password-check-status.error {
    background: #f8d7da;
    color: #842029;
}
.password-check-status.success {
    background: #d1e7dd;
    color: #0f5132;
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
    
    // Allowed special characters for password
    var SPECIAL_CHARS = '!@£$%^&*()_\\-=+\\[\\]{};\':\",.\\<\\>?/\\\\|~';
    var SPECIAL_CHARS_REGEX = new RegExp('[' + SPECIAL_CHARS + ']');
    
    // Mock breached passwords (backend would check against HaveIBeenPwned API)
    var BREACHED_PASSWORDS = ['password123!', 'Password1234!', 'Qwerty123456!', 'Welcome123!'];
    
    // Mock previous passwords (backend would check against user's history)
    var PREVIOUS_PASSWORDS = ['OldPassword1!', 'MyOldPass123!'];
    
    var passwordCheckTimeout;
    var passwordIsValid = false;
    var passwordChecksComplete = false;
    
    $('.show-pass').on('click', function() {
        var $input = $(this).siblings('input');
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('active');
    });
    
    $('#password').on('input', function() {
        var password = $(this).val();
        
        // Reset checks
        passwordChecksComplete = false;
        $('#passwordCheckStatus').addClass('d-none');
        
        // Validate password rules in real-time
        var rules = validatePasswordRules(password);
        updateRuleIndicators(rules);
        
        // Check if all rules pass
        passwordIsValid = rules.length && rules.uppercase && rules.lowercase && rules.number && rules.special;
        
        if (passwordIsValid) {
            $(this).removeClass('is-invalid');
            // Debounce backend checks
            clearTimeout(passwordCheckTimeout);
            passwordCheckTimeout = setTimeout(function() {
                checkPasswordBackend(password);
            }, 500);
        }
    });
    
    function validatePasswordRules(password) {
        return {
            length: password.length >= 12 && password.length <= 128,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: SPECIAL_CHARS_REGEX.test(password)
        };
    }
    
    function updateRuleIndicators(rules) {
        updateRule('rule-length', rules.length);
        updateRule('rule-uppercase', rules.uppercase);
        updateRule('rule-lowercase', rules.lowercase);
        updateRule('rule-number', rules.number);
        updateRule('rule-special', rules.special);
    }
    
    function updateRule(ruleId, isValid) {
        var $rule = $('#' + ruleId);
        $rule.removeClass('valid invalid');
        if ($('#password').val().length > 0) {
            $rule.addClass(isValid ? 'valid' : 'invalid');
        }
    }
    
    function checkPasswordBackend(password) {
        var $status = $('#passwordCheckStatus');
        
        // Show checking status
        $status.removeClass('d-none error success').addClass('checking');
        $status.html('<i class="fas fa-spinner fa-spin me-2"></i>Checking password security...');
        
        // Mock backend checks
        // In production: POST /api/auth/check-password (hashed or using k-anonymity for breach check)
        setTimeout(function() {
            var isBreached = BREACHED_PASSWORDS.includes(password);
            var isPreviouslyUsed = PREVIOUS_PASSWORDS.includes(password);
            
            if (isBreached) {
                $status.removeClass('checking success').addClass('error');
                $status.html('<i class="fas fa-exclamation-triangle me-2"></i>This password has been found in a data breach. Please choose a different password.');
                passwordChecksComplete = false;
            } else if (isPreviouslyUsed) {
                $status.removeClass('checking success').addClass('error');
                $status.html('<i class="fas fa-exclamation-triangle me-2"></i>You cannot reuse one of your last 10 passwords. Please choose a different password.');
                passwordChecksComplete = false;
            } else {
                $status.removeClass('checking error').addClass('success');
                $status.html('<i class="fas fa-check-circle me-2"></i>Password meets all security requirements.');
                passwordChecksComplete = true;
            }
            
            console.log('[Password] Backend check result:', { isBreached, isPreviouslyUsed, checksComplete: passwordChecksComplete });
        }, 800);
    }
    
    $('#confirmPassword').on('input', function() {
        var confirm = $(this).val();
        var password = $('#password').val();
        
        if (confirm && confirm !== password) {
            $(this).addClass('is-invalid');
            $('#confirmError').text('Passwords do not match');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#securityForm').on('submit', function(e) {
        e.preventDefault();
        
        var isValid = true;
        var password = $('#password').val();
        var confirm = $('#confirmPassword').val();
        
        // Validate password rules
        var rules = validatePasswordRules(password);
        if (!rules.length || !rules.uppercase || !rules.lowercase || !rules.number || !rules.special) {
            $('#password').addClass('is-invalid');
            $('#passwordError').text('Password does not meet all requirements');
            isValid = false;
        } else if (!passwordChecksComplete) {
            $('#password').addClass('is-invalid');
            $('#passwordError').text('Please wait for security checks to complete');
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
        // Password should be hashed using Argon2id on the server
        // Never log plaintext password
        var formData = {
            email: email,
            password_hash: 'ARGON2ID_HASH_PLACEHOLDER', // Backend hashes with per-user salt
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

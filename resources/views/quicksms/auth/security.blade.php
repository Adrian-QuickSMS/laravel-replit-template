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
                    <h6 class="section-title"><i class="fas fa-mobile-alt me-2"></i>B. Mobile Number Verification</h6>
                    <p class="section-helper">Verify your mobile number for account security and two-factor authentication (MFA is mandatory for all accounts).</p>
                    
                    <div class="form-group mb-3">
                        <label class="form-label" for="mobileNumber">Mobile Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="tel" class="form-control" id="mobileNumber" placeholder="+44 7700 900123" required>
                            <button class="btn btn-outline-primary" type="button" id="sendOtpBtn">
                                <span class="btn-text">Send Code</span>
                                <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm"></span></span>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="mobileError">Please enter a valid mobile number</div>
                        <small class="form-text text-muted">E.164 format preferred (e.g., +447700900123)</small>
                        <div class="otp-status mt-2 d-none" id="otpStatus"></div>
                    </div>
                    
                    <div class="form-group mb-3 d-none" id="otpInputGroup">
                        <label class="form-label" for="otpCode">Verification Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="otpCode" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                            <button class="btn btn-primary" type="button" id="verifyOtpBtn">
                                <span class="btn-text">Verify</span>
                                <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm"></span></span>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="otpError">Invalid verification code</div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">Code expires in <span id="otpCountdown">5:00</span></small>
                            <button type="button" class="btn btn-link btn-sm p-0" id="resendOtpBtn" disabled>Resend Code</button>
                        </div>
                    </div>
                    
                    <div class="verified-badge d-none" id="mobileVerifiedBadge">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <span>Mobile number verified</span>
                    </div>
                    
                    <div class="mfa-notice mt-3">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        <small class="text-muted">Two-factor authentication (MFA) is enabled by default for all accounts. You'll receive a verification code via SMS when signing in.</small>
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
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="thirdPartyConsent" required>
                        <label class="form-check-label" for="thirdPartyConsent">
                            I agree that QuickSMS may share my information with trusted third-party fraud prevention, validation, and messaging partners to protect against abuse <span class="text-danger">*</span>
                        </label>
                        <div class="invalid-feedback">You must agree to third-party data sharing to continue</div>
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
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="marketingConsent">
                        <label class="form-check-label" for="marketingConsent">
                            I agree to receive product updates, tips, and offers from QuickSMS via email, SMS, and RCS. I can opt out at any time.
                        </label>
                        <small class="d-block text-muted mt-1">Email updates are sent to your business email. SMS/RCS updates are sent to your verified mobile number.</small>
                    </div>
                </div>
                
                <div class="section-card mb-4" id="testCreditSection">
                    <h6 class="section-title"><i class="fas fa-gift me-2"></i>E. Test Credit Eligibility</h6>
                    
                    <div id="creditEligibilityStatus">
                        <div class="credit-status-pending" id="creditPending">
                            <div class="d-flex align-items-start">
                                <div class="test-credit-icon me-3">
                                    <i class="fas fa-coins text-muted"></i>
                                </div>
                                <div>
                                    <p class="mb-2"><strong>Unlock 100 free test SMS credits</strong></p>
                                    <p class="text-muted mb-0 small">Opt in to marketing above to receive 100 free test SMS credits when your account is created.</p>
                                    <ul class="eligibility-checklist mt-2 mb-0">
                                        <li id="checkMobile"><i class="fas fa-circle"></i> Mobile number verified</li>
                                        <li id="checkMarketing"><i class="fas fa-circle"></i> Marketing consent accepted</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="credit-status-eligible d-none" id="creditEligible">
                            <div class="d-flex align-items-start">
                                <div class="test-credit-icon test-credit-icon-success me-3">
                                    <i class="fas fa-coins text-warning"></i>
                                </div>
                                <div>
                                    <p class="mb-2 text-success"><strong><i class="fas fa-check-circle me-1"></i>You're eligible for 100 free test credits!</strong></p>
                                    <p class="text-muted mb-0 small">Your 100 free SMS credits will be applied when your account is created. No payment required to get started.</p>
                                </div>
                            </div>
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
.otp-status {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
}
.otp-status.sending {
    background: #e7f1ff;
    color: #0d6efd;
}
.otp-status.sent {
    background: #d1e7dd;
    color: #0f5132;
}
.otp-status.error {
    background: #f8d7da;
    color: #842029;
}
.verified-badge {
    display: flex;
    align-items: center;
    background: #d1e7dd;
    color: #0f5132;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
}
.mfa-notice {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    border-left: 3px solid #886CC0;
}
#otpCode {
    letter-spacing: 0.5rem;
    font-weight: 600;
    font-size: 1.1rem;
    text-align: center;
}
.btn-outline-primary {
    color: #886CC0;
    border-color: #886CC0;
}
.btn-outline-primary:hover {
    background-color: #886CC0;
    border-color: #886CC0;
    color: #fff;
}
.eligibility-checklist {
    list-style: none;
    padding-left: 0;
    font-size: 0.8rem;
}
.eligibility-checklist li {
    padding: 0.25rem 0;
    color: #6c757d;
}
.eligibility-checklist li i {
    font-size: 0.5rem;
    margin-right: 0.5rem;
    color: #dee2e6;
}
.eligibility-checklist li.checked {
    color: #28a745;
}
.eligibility-checklist li.checked i {
    color: #28a745;
}
.test-credit-icon-success {
    background: #fef3c7 !important;
}
.credit-status-pending {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}
.credit-status-eligible {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 3px solid #22c55e;
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
    
    // OTP verification state
    var mobileVerified = false;
    var currentOtp = null;
    var otpExpiry = null;
    var countdownInterval = null;
    var resendCooldown = 30; // seconds before resend is enabled
    
    // E.164 format validation (international format)
    function isValidE164(number) {
        var cleaned = number.replace(/[\s\-\(\)]/g, '');
        return /^\+[1-9]\d{6,14}$/.test(cleaned);
    }
    
    // Send OTP button handler
    $('#sendOtpBtn').on('click', function() {
        var mobile = $('#mobileNumber').val().trim();
        
        if (!mobile || !isValidE164(mobile)) {
            $('#mobileNumber').addClass('is-invalid');
            $('#mobileError').text('Please enter a valid mobile number in E.164 format (e.g., +447700900123)');
            return;
        }
        
        $('#mobileNumber').removeClass('is-invalid');
        
        var $btn = $(this);
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        
        var $status = $('#otpStatus');
        $status.removeClass('d-none sent error').addClass('sending');
        $status.html('<i class="fas fa-spinner fa-spin me-2"></i>Sending verification code...');
        
        // Mock SMS OTP send
        // In production: POST /api/auth/send-otp { mobile: mobile }
        setTimeout(function() {
            // Generate mock 6-digit OTP
            currentOtp = String(Math.floor(100000 + Math.random() * 900000));
            otpExpiry = Date.now() + (5 * 60 * 1000); // 5 minutes
            
            console.log('[OTP] Mock code sent to ' + mobile + ': ' + currentOtp);
            
            $status.removeClass('sending').addClass('sent');
            $status.html('<i class="fas fa-check-circle me-2"></i>Verification code sent to ' + mobile);
            
            // Show OTP input
            $('#otpInputGroup').removeClass('d-none');
            $('#otpCode').focus();
            
            // Reset button
            $btn.prop('disabled', false);
            $btn.find('.btn-text').removeClass('d-none').text('Resend');
            $btn.find('.btn-loading').addClass('d-none');
            
            // Lock mobile number field
            $('#mobileNumber').prop('readonly', true);
            
            // Start countdown
            startCountdown();
            
            // Enable resend after cooldown
            var $resend = $('#resendOtpBtn');
            $resend.prop('disabled', true);
            setTimeout(function() {
                $resend.prop('disabled', false);
            }, resendCooldown * 1000);
            
        }, 1500);
    });
    
    function startCountdown() {
        var remaining = 5 * 60; // 5 minutes
        
        if (countdownInterval) clearInterval(countdownInterval);
        
        countdownInterval = setInterval(function() {
            remaining--;
            var minutes = Math.floor(remaining / 60);
            var seconds = remaining % 60;
            $('#otpCountdown').text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);
            
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                $('#otpCountdown').text('Expired');
                currentOtp = null;
                $('#otpStatus').removeClass('sent sending').addClass('error');
                $('#otpStatus').html('<i class="fas fa-exclamation-triangle me-2"></i>Code expired. Please request a new code.');
            }
        }, 1000);
    }
    
    // Resend OTP
    $('#resendOtpBtn').on('click', function() {
        $('#sendOtpBtn').click();
    });
    
    // Verify OTP button handler
    $('#verifyOtpBtn').on('click', function() {
        var enteredOtp = $('#otpCode').val().trim();
        
        if (!enteredOtp || enteredOtp.length !== 6) {
            $('#otpCode').addClass('is-invalid');
            $('#otpError').text('Please enter the 6-digit verification code');
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        
        // Mock OTP verification
        // In production: POST /api/auth/verify-otp { mobile: mobile, otp: enteredOtp }
        setTimeout(function() {
            if (!currentOtp || Date.now() > otpExpiry) {
                $('#otpCode').addClass('is-invalid');
                $('#otpError').text('Code has expired. Please request a new code.');
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
                return;
            }
            
            if (enteredOtp !== currentOtp) {
                $('#otpCode').addClass('is-invalid');
                $('#otpError').text('Invalid verification code. Please try again.');
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
                console.log('[OTP] Verification failed. Entered: ' + enteredOtp + ', Expected: ' + currentOtp);
                return;
            }
            
            // Success!
            mobileVerified = true;
            clearInterval(countdownInterval);
            
            // Hide OTP input, show verified badge
            $('#otpInputGroup').addClass('d-none');
            $('#otpStatus').addClass('d-none');
            
            // Update credit eligibility
            updateCreditEligibility();
            $('#sendOtpBtn').addClass('d-none');
            $('#mobileVerifiedBadge').removeClass('d-none');
            
            console.log('[OTP] Mobile verified successfully');
            
        }, 800);
    });
    
    // OTP input - only allow numbers
    $('#otpCode').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        $(this).removeClass('is-invalid');
    });
    
    // Mobile number change detection
    $('#mobileNumber').on('input', function() {
        $(this).removeClass('is-invalid');
        // Reset verification if number changes
        if (mobileVerified) {
            mobileVerified = false;
            $('#mobileVerifiedBadge').addClass('d-none');
            $('#sendOtpBtn').removeClass('d-none').find('.btn-text').text('Send Code');
        }
        updateCreditEligibility();
    });
    
    // Marketing consent change handler
    $('#marketingConsent').on('change', function() {
        updateCreditEligibility();
    });
    
    // Update credit eligibility status
    function updateCreditEligibility() {
        var marketingChecked = $('#marketingConsent').is(':checked');
        
        // Update checklist items
        if (mobileVerified) {
            $('#checkMobile').addClass('checked');
        } else {
            $('#checkMobile').removeClass('checked');
        }
        
        if (marketingChecked) {
            $('#checkMarketing').addClass('checked');
        } else {
            $('#checkMarketing').removeClass('checked');
        }
        
        // Show eligible or pending state
        if (mobileVerified && marketingChecked) {
            $('#creditPending').addClass('d-none');
            $('#creditEligible').removeClass('d-none');
        } else {
            $('#creditPending').removeClass('d-none');
            $('#creditEligible').addClass('d-none');
        }
    }
    
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
        
        // Validate mobile number is provided
        if (!$('#mobileNumber').val().trim()) {
            $('#mobileNumber').addClass('is-invalid');
            $('#mobileError').text('Mobile number is required');
            isValid = false;
        } else if (!mobileVerified) {
            // Mobile must be verified via OTP
            $('#mobileNumber').addClass('is-invalid');
            $('#mobileError').text('Please verify your mobile number with SMS code');
            isValid = false;
        }
        
        var requiredCheckboxes = ['termsConsent', 'privacyConsent', 'fraudConsent', 'thirdPartyConsent', 'contentConsent'];
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
            mobile_verified: true, // Verified via SMS OTP
            mfa_enabled: true, // MFA is mandatory for all accounts
            consents: {
                terms: true,
                privacy: true,
                fraud_prevention: true,
                third_party_sharing: true,
                content_compliance: true
            },
            consent_audit: {
                third_party_sharing: {
                    consent_given: true,
                    consent_text: 'I agree that QuickSMS may share my information with trusted third-party fraud prevention, validation, and messaging partners to protect against abuse',
                    timestamp: new Date().toISOString(),
                    user_email: email,
                    ip_address: 'CAPTURED_BY_SERVER', // Backend captures IP
                    user_agent: navigator.userAgent
                }
            },
            marketing: {
                consent: $('#marketingConsent').is(':checked'),
                consent_timestamp: $('#marketingConsent').is(':checked') ? new Date().toISOString() : null,
                channels: {
                    email: $('#marketingConsent').is(':checked'),
                    sms: $('#marketingConsent').is(':checked') && mobileVerified,
                    rcs: $('#marketingConsent').is(':checked') && mobileVerified
                }
            },
            test_credits: {
                eligible: mobileVerified && $('#marketingConsent').is(':checked'),
                amount: (mobileVerified && $('#marketingConsent').is(':checked')) ? 100 : 0,
                reason: (mobileVerified && $('#marketingConsent').is(':checked')) ? 'marketing_opt_in' : null
            },
            hubspot_sync: {
                marketing_consent: $('#marketingConsent').is(':checked'),
                marketing_consent_timestamp: $('#marketingConsent').is(':checked') ? new Date().toISOString() : null
            }
        };
        
        console.log('[Security] Saving security settings:', formData);
        
        // Mock account provisioning
        // In production: POST /api/auth/provision-account
        setTimeout(function() {
            // Step 1: Create QuickSMS Account
            var accountId = 'ACC-' + Date.now();
            var userId = 'USR-' + Date.now();
            
            var provisioningResult = {
                account: {
                    id: accountId,
                    created_at: new Date().toISOString(),
                    status: 'active',
                    type: 'standard'
                },
                user: {
                    id: userId,
                    email: email,
                    role: 'account_owner',
                    email_verified: true,
                    mfa_enabled: true,
                    mobile_verified: true,
                    created_at: new Date().toISOString()
                },
                security: {
                    password_policy_applied: true,
                    password_hash_algorithm: 'argon2id',
                    password_history_count: 10
                },
                credits: formData.test_credits.eligible ? {
                    applied: true,
                    amount: 100,
                    reason: 'marketing_opt_in',
                    applied_at: new Date().toISOString()
                } : {
                    applied: false,
                    amount: 0
                },
                account_details: {
                    first_name: urlParams.get('first_name') || 'New',
                    last_name: urlParams.get('last_name') || 'User',
                    job_title: urlParams.get('job_title') || '',
                    business_name: urlParams.get('business_name') || '',
                    business_email: email,
                    mobile_number: formData.mobile_number,
                    country: urlParams.get('country') || 'United Kingdom'
                },
                hubspot_sync: {
                    triggered: true,
                    contact_id: 'HS-CONTACT-' + Date.now(),
                    company_id: 'HS-COMPANY-' + Date.now(),
                    synced_at: new Date().toISOString(),
                    properties: {
                        email: email,
                        phone: formData.mobile_number,
                        marketing_consent: formData.marketing.consent,
                        marketing_consent_timestamp: formData.marketing.consent_timestamp,
                        mfa_enabled: true,
                        email_verified: true
                    }
                },
                audit: {
                    event: 'account_provisioned',
                    timestamp: new Date().toISOString(),
                    user_agent: navigator.userAgent,
                    ip_address: 'CAPTURED_BY_SERVER',
                    details: {
                        email_verified: true,
                        mobile_verified: true,
                        mfa_enabled: true,
                        test_credits_applied: formData.test_credits.eligible,
                        marketing_consent: formData.marketing.consent,
                        consents_accepted: ['terms', 'privacy', 'fraud_prevention', 'third_party_sharing', 'content_compliance']
                    }
                }
            };
            
            console.log('[Provisioning] Account created:', provisioningResult);
            console.log('[HubSpot] Contact synced:', provisioningResult.hubspot_sync);
            console.log('[Audit] Event logged:', provisioningResult.audit);
            
            if (formData.test_credits.eligible) {
                console.log('[Credits] 100 test SMS credits applied to account');
            }
            
            // Store provisioning result for dashboard
            sessionStorage.setItem('quicksms_onboarding', JSON.stringify({
                completed: true,
                account_id: accountId,
                user_id: userId,
                email: email,
                test_credits_applied: formData.test_credits.eligible,
                test_credits_amount: formData.test_credits.eligible ? 100 : 0
            }));
            
            // Redirect to Dashboard
            window.location.href = '/dashboard?onboarding=complete&credits=' + (formData.test_credits.eligible ? '100' : '0');
        }, 2000);
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

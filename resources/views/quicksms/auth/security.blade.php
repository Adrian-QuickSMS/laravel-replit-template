@extends('layouts.fullwidth')
@section('title', 'Security & Consent')
@section('content')
<div class="col-xl-10 col-lg-11 col-md-12">
    <div class="card mb-0 h-auto">
        <div class="card-body py-4 px-4">
            <div class="text-center mb-3">
                <a href="{{ url('/') }}"><img class="logo-auth" src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 40px;"></a>
            </div>
            <h4 class="text-center mb-1">Set Up Your Account</h4>
            <p class="text-center text-muted mb-3">Step 2 of 3: Security & Consent</p>
            
            <div class="alert alert-success mb-3 py-2" id="verifiedBadge">
                <i class="fas fa-check-circle me-2"></i>
                Email verified: <strong id="verifiedEmail"></strong>
            </div>
            
            <form id="securityForm" novalidate>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section-card mb-3">
                            <h6 class="section-title"><i class="fas fa-lock me-2"></i>Password Setup</h6>
                            <p class="section-helper mb-2">Create a strong password. Hashed with Argon2id.</p>
                            
                            <div class="form-group mb-2">
                                <label class="form-label form-label-sm" for="password">Password <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="form-control form-control-sm" id="password" placeholder="12-128 characters" required autocomplete="new-password" minlength="12" maxlength="128">
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
                                        <span>1 uppercase (A-Z)</span>
                                    </div>
                                    <div class="rule-item" id="rule-lowercase">
                                        <i class="fas fa-circle rule-icon"></i>
                                        <span>1 lowercase (a-z)</span>
                                    </div>
                                    <div class="rule-item" id="rule-number">
                                        <i class="fas fa-circle rule-icon"></i>
                                        <span>1 number (0-9)</span>
                                    </div>
                                    <div class="rule-item" id="rule-special">
                                        <i class="fas fa-circle rule-icon"></i>
                                        <span>1 special character</span>
                                    </div>
                                </div>
                                
                                <div class="password-check-status mt-2 d-none" id="passwordCheckStatus"></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label form-label-sm" for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-sm" id="confirmPassword" placeholder="Re-enter password" required autocomplete="new-password">
                                <div class="invalid-feedback" id="confirmError">Passwords do not match</div>
                            </div>
                        </div>
                        
                        <div class="section-card mb-3">
                            <h6 class="section-title"><i class="fas fa-mobile-alt me-2"></i>Mobile Verification</h6>
                            <p class="section-helper mb-2">Verify your mobile for MFA (mandatory for all accounts).</p>
                            
                            <div class="form-group mb-2">
                                <label class="form-label form-label-sm" for="mobileNumber">Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="tel" class="form-control" id="mobileNumber" placeholder="+44 7700 900123" required>
                                    <button class="btn btn-outline-primary" type="button" id="sendOtpBtn">
                                        <span class="btn-text">Send Code</span>
                                        <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="mobileError">Please enter a valid mobile number</div>
                                <div class="otp-status mt-2 d-none" id="otpStatus"></div>
                            </div>
                            
                            <div class="form-group mb-2 d-none" id="otpInputGroup">
                                <label class="form-label form-label-sm" for="otpCode">Verification Code <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="otpCode" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                                    <button class="btn btn-primary" type="button" id="verifyOtpBtn">
                                        <span class="btn-text">Verify</span>
                                        <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="otpError">Invalid verification code</div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted">Expires in <span id="otpCountdown">5:00</span></small>
                                    <button type="button" class="btn btn-link btn-sm p-0" id="resendOtpBtn" disabled>Resend</button>
                                </div>
                                <div class="alert alert-info small mt-2 py-1 d-none" id="testOtpCode">
                                    <strong>Test Mode:</strong> Your code is <span class="fw-bold" id="displayOtp"></span>
                                </div>
                            </div>
                            
                            <div class="verified-badge d-none" id="mobileVerifiedBadge">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Mobile verified</span>
                            </div>
                            
                            <div class="mfa-notice mt-2">
                                <i class="fas fa-shield-alt text-primary me-1"></i>
                                <small class="text-muted">MFA is enabled by default. You'll receive a code via SMS when signing in.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="section-card mb-3">
                            <h6 class="section-title"><i class="fas fa-shield-alt me-2"></i>Required Agreements</h6>
                            <p class="section-helper mb-2">Required to protect you and ensure message delivery.</p>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="termsConsent" required>
                                <label class="form-check-label small" for="termsConsent">
                                    I agree to the <a href="#" class="text-primary">Terms</a> and <a href="#" class="text-primary">Acceptable Use Policy</a> <span class="text-danger">*</span>
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="privacyConsent" required>
                                <label class="form-check-label small" for="privacyConsent">
                                    I accept the <a href="#" class="text-primary">Privacy Policy</a> <span class="text-danger">*</span>
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="fraudConsent" required>
                                <label class="form-check-label small" for="fraudConsent">
                                    I consent to fraud prevention checks <span class="text-danger">*</span>
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="thirdPartyConsent" required>
                                <label class="form-check-label small" for="thirdPartyConsent">
                                    I agree to third-party fraud prevention data sharing <span class="text-danger">*</span>
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="contentConsent" required>
                                <label class="form-check-label small" for="contentConsent">
                                    I agree to comply with <a href="#" class="text-primary">UK messaging regulations</a> <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="section-card mb-3">
                            <h6 class="section-title"><i class="fas fa-envelope me-2"></i>Marketing Preferences</h6>
                            <p class="section-helper mb-2">Optional: Stay informed about updates and offers.</p>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="marketingConsent">
                                <label class="form-check-label small" for="marketingConsent">
                                    I agree to receive product updates, tips, and offers via email, SMS, and RCS. I can opt out at any time.
                                </label>
                            </div>
                        </div>
                        
                        <div class="section-card mb-3" id="testCreditSection">
                            <h6 class="section-title"><i class="fas fa-gift me-2"></i>Test Credit Eligibility</h6>
                            
                            <div id="creditEligibilityStatus">
                                <div class="credit-status-pending" id="creditPending">
                                    <div class="d-flex align-items-start">
                                        <div class="test-credit-icon me-2">
                                            <i class="fas fa-coins text-muted"></i>
                                        </div>
                                        <div>
                                            <p class="mb-1 small"><strong>Unlock 100 free test SMS credits</strong></p>
                                            <ul class="eligibility-checklist mb-0 small">
                                                <li id="checkMobile"><i class="fas fa-circle"></i> Mobile verified</li>
                                                <li id="checkMarketing"><i class="fas fa-circle"></i> Marketing consent</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="credit-status-eligible d-none" id="creditEligible">
                                    <div class="d-flex align-items-start">
                                        <div class="test-credit-icon test-credit-icon-success me-2">
                                            <i class="fas fa-coins text-warning"></i>
                                        </div>
                                        <div>
                                            <p class="mb-1 text-success small"><strong><i class="fas fa-check-circle me-1"></i>You're eligible for 100 free credits!</strong></p>
                                            <p class="text-muted mb-0 small">Credits applied on account creation.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary px-5" id="continueBtn">
                        <span class="btn-text">Complete Setup <i class="fas fa-arrow-right ms-2"></i></span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Processing...
                        </span>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-2">
                <small class="text-muted">Need help? <a class="text-primary" href="#">Contact Support</a></small>
            </div>
        </div>
    </div>
</div>

<style>
.logo-auth {
    max-height: 40px;
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
    border-radius: 0.375rem;
    padding: 0.875rem;
    border-left: 3px solid #886CC0;
}
.section-card-highlight {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-left-color: #22c55e;
}
.section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #2c2c2c;
    margin-bottom: 0.5rem;
}
.section-helper {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
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
    width: 36px;
    height: 36px;
    background: #fef3c7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.form-check-input:checked {
    background-color: #886CC0;
    border-color: #886CC0;
}
.password-rules {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    padding: 0.5rem 0.75rem;
}
.rule-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.7rem;
    color: #6c757d;
    padding: 0.125rem 0;
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
    
    // =====================================================
    // SECURITY CONTROLS - Backend-ready service wrappers
    // =====================================================
    
    // Security Configuration
    var SecurityConfig = {
        https_only: true,
        rate_limits: {
            signup_per_ip: { max: 5, window_minutes: 15 },
            login_per_ip: { max: 10, window_minutes: 15 },
            login_per_email: { max: 5, window_minutes: 15 },
            otp_send_per_mobile: { max: 3, window_minutes: 10 },
            otp_verify_attempts: { max: 5, window_minutes: 5 }
        },
        account_lockout: {
            max_failed_attempts: 5,
            lockout_duration_minutes: 30,
            progressive_delay: true
        }
    };
    
    // Rate Limiting Service (Mock - Backend would use Redis/DB)
    var RateLimitService = {
        attempts: {},
        
        checkLimit: function(key, limitConfig) {
            var now = Date.now();
            var windowMs = limitConfig.window_minutes * 60 * 1000;
            
            if (!this.attempts[key]) {
                this.attempts[key] = [];
            }
            
            // Clean old attempts
            this.attempts[key] = this.attempts[key].filter(function(t) {
                return now - t < windowMs;
            });
            
            if (this.attempts[key].length >= limitConfig.max) {
                var oldestAttempt = this.attempts[key][0];
                var retryAfter = Math.ceil((windowMs - (now - oldestAttempt)) / 1000);
                return { allowed: false, retryAfter: retryAfter };
            }
            
            return { allowed: true, remaining: limitConfig.max - this.attempts[key].length };
        },
        
        recordAttempt: function(key) {
            if (!this.attempts[key]) {
                this.attempts[key] = [];
            }
            this.attempts[key].push(Date.now());
        },
        
        checkSignupRate: function(ipAddress) {
            return this.checkLimit('signup:' + ipAddress, SecurityConfig.rate_limits.signup_per_ip);
        },
        
        checkLoginRate: function(ipAddress, email) {
            var ipCheck = this.checkLimit('login_ip:' + ipAddress, SecurityConfig.rate_limits.login_per_ip);
            var emailCheck = this.checkLimit('login_email:' + email, SecurityConfig.rate_limits.login_per_email);
            
            if (!ipCheck.allowed) return ipCheck;
            if (!emailCheck.allowed) return emailCheck;
            return { allowed: true };
        },
        
        checkOtpSendRate: function(mobile) {
            return this.checkLimit('otp_send:' + mobile, SecurityConfig.rate_limits.otp_send_per_mobile);
        },
        
        checkOtpVerifyRate: function(mobile) {
            return this.checkLimit('otp_verify:' + mobile, SecurityConfig.rate_limits.otp_verify_attempts);
        }
    };
    
    // Account Lockout Service (Mock - Backend would use DB)
    var LockoutService = {
        failedAttempts: {},
        lockedAccounts: {},
        
        recordFailedAttempt: function(email) {
            if (!this.failedAttempts[email]) {
                this.failedAttempts[email] = { count: 0, lastAttempt: null };
            }
            this.failedAttempts[email].count++;
            this.failedAttempts[email].lastAttempt = Date.now();
            
            if (this.failedAttempts[email].count >= SecurityConfig.account_lockout.max_failed_attempts) {
                this.lockAccount(email);
            }
            
            AuditService.log('login_failed', { email: email, attempt_count: this.failedAttempts[email].count });
            
            return this.failedAttempts[email].count;
        },
        
        lockAccount: function(email) {
            var unlockTime = Date.now() + (SecurityConfig.account_lockout.lockout_duration_minutes * 60 * 1000);
            this.lockedAccounts[email] = unlockTime;
            AuditService.log('account_locked', { email: email, unlock_at: new Date(unlockTime).toISOString() });
        },
        
        isLocked: function(email) {
            if (!this.lockedAccounts[email]) return false;
            if (Date.now() > this.lockedAccounts[email]) {
                delete this.lockedAccounts[email];
                delete this.failedAttempts[email];
                return false;
            }
            return true;
        },
        
        getUnlockTime: function(email) {
            return this.lockedAccounts[email] ? new Date(this.lockedAccounts[email]).toISOString() : null;
        },
        
        resetOnSuccess: function(email) {
            delete this.failedAttempts[email];
            delete this.lockedAccounts[email];
        }
    };
    
    // Audit Logging Service (Mock - Backend would persist to DB)
    var AuditService = {
        logs: [],
        
        log: function(event, details) {
            var entry = {
                id: 'AUDIT-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
                event: event,
                timestamp: new Date().toISOString(),
                actor: {
                    email: email || 'anonymous',
                    ip_address: 'CAPTURED_BY_SERVER',
                    user_agent: navigator.userAgent
                },
                details: details || {},
                session_id: sessionStorage.getItem('session_id') || 'unknown'
            };
            
            this.logs.push(entry);
            console.log('[AuditService]', entry.event, entry);
            
            // In production: POST /api/audit/log
            return entry;
        },
        
        logSignupStart: function(email) {
            return this.log('signup_started', { email: email });
        },
        
        logSignupComplete: function(email, accountId) {
            return this.log('signup_completed', { email: email, account_id: accountId });
        },
        
        logEmailVerification: function(email, success, method) {
            return this.log('email_verification', { email: email, success: success, method: method || 'token' });
        },
        
        logConsentCapture: function(email, consents) {
            return this.log('consent_captured', { email: email, consents: consents });
        },
        
        logPasswordSet: function(email) {
            return this.log('password_set', { email: email, policy_applied: true, algorithm: 'argon2id' });
        },
        
        logPasswordChange: function(email) {
            return this.log('password_changed', { email: email, policy_applied: true });
        },
        
        logMfaOtpSent: function(mobile, success) {
            return this.log('mfa_otp_sent', { mobile_masked: mobile.slice(0, 4) + '****' + mobile.slice(-2), success: success });
        },
        
        logMfaOtpVerified: function(mobile, success) {
            return this.log('mfa_otp_verified', { mobile_masked: mobile.slice(0, 4) + '****' + mobile.slice(-2), success: success });
        },
        
        getRecentLogs: function(count) {
            return this.logs.slice(-count);
        }
    };
    
    // Initialize session ID for audit tracking
    if (!sessionStorage.getItem('session_id')) {
        sessionStorage.setItem('session_id', 'SES-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9));
    }
    
    // Log signup security step start
    AuditService.log('security_step_started', { step: 2, email: email });
    
    // =====================================================
    // PASSWORD VALIDATION
    // =====================================================
    
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
    
    // =====================================================
    // OTP VERIFICATION STATE
    // =====================================================
    
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
        
        // Check rate limit for OTP sending
        var rateCheck = RateLimitService.checkOtpSendRate(mobile);
        if (!rateCheck.allowed) {
            var $status = $('#otpStatus');
            $status.removeClass('d-none sent sending').addClass('error');
            $status.html('<i class="fas fa-exclamation-triangle me-2"></i>Too many attempts. Please try again in ' + Math.ceil(rateCheck.retryAfter / 60) + ' minutes.');
            AuditService.log('otp_send_rate_limited', { mobile_masked: mobile.slice(0, 4) + '****' + mobile.slice(-2) });
            return;
        }
        
        $('#mobileNumber').removeClass('is-invalid');
        RateLimitService.recordAttempt('otp_send:' + mobile);
        
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
            
            // Audit log OTP sent
            AuditService.logMfaOtpSent(mobile, true);
            otpExpiry = Date.now() + (5 * 60 * 1000); // 5 minutes
            
            console.log('[OTP] Mock code sent to ' + mobile + ': ' + currentOtp);
            
            $status.removeClass('sending').addClass('sent');
            $status.html('<i class="fas fa-check-circle me-2"></i>Verification code sent to ' + mobile);
            
            // Show OTP input
            $('#otpInputGroup').removeClass('d-none');
            $('#otpCode').focus();
            
            // Show test mode OTP code on page
            $('#displayOtp').text(currentOtp);
            $('#testOtpCode').removeClass('d-none');
            
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
        var mobile = $('#mobileNumber').val().trim();
        
        if (!enteredOtp || enteredOtp.length !== 6) {
            $('#otpCode').addClass('is-invalid');
            $('#otpError').text('Please enter the 6-digit verification code');
            return;
        }
        
        // Check rate limit for OTP verification attempts
        var rateCheck = RateLimitService.checkOtpVerifyRate(mobile);
        if (!rateCheck.allowed) {
            $('#otpCode').addClass('is-invalid');
            $('#otpError').text('Too many attempts. Please try again in ' + Math.ceil(rateCheck.retryAfter / 60) + ' minutes.');
            AuditService.log('otp_verify_rate_limited', { mobile_masked: mobile.slice(0, 4) + '****' + mobile.slice(-2) });
            return;
        }
        
        RateLimitService.recordAttempt('otp_verify:' + mobile);
        
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
                AuditService.logMfaOtpVerified(mobile, false);
                return;
            }
            
            if (enteredOtp !== currentOtp) {
                $('#otpCode').addClass('is-invalid');
                $('#otpError').text('Invalid verification code. Please try again.');
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
                AuditService.logMfaOtpVerified(mobile, false);
                console.log('[OTP] Verification failed. Entered: ' + enteredOtp + ', Expected: ' + currentOtp);
                return;
            }
            
            // Audit log successful verification
            AuditService.logMfaOtpVerified(mobile, true);
            
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
        
        // Audit log: Password set
        AuditService.logPasswordSet(email);
        
        // Audit log: Consent capture
        AuditService.logConsentCapture(email, {
            terms: true,
            privacy: true,
            fraud_prevention: true,
            third_party_sharing: true,
            content_compliance: true,
            marketing: formData.marketing.consent
        });
        
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
            
            // HubSpot Service - Backend-ready wrapper
            // In production: Replace with actual HubSpot API calls via backend
            var HubSpotService = {
                createContact: function(contactData) {
                    // POST /api/hubspot/contacts
                    var payload = {
                        properties: {
                            firstname: contactData.first_name,
                            lastname: contactData.last_name,
                            email: contactData.email,
                            jobtitle: contactData.job_title,
                            company: contactData.business_name,
                            phone: contactData.mobile_number,
                            country: contactData.country,
                            email_verified: String(contactData.email_verified),
                            marketing_consent: String(contactData.marketing_consent),
                            marketing_consent_timestamp: contactData.marketing_consent_timestamp || '',
                            test_credit_eligible: String(contactData.test_credit_eligible)
                        }
                    };
                    console.log('[HubSpotService] createContact payload:', payload);
                    return {
                        success: true,
                        id: 'HS-CONTACT-' + Date.now(),
                        payload: payload
                    };
                },
                createCompany: function(companyData) {
                    // POST /api/hubspot/companies
                    var payload = {
                        properties: {
                            name: companyData.company_name,
                            country: companyData.country,
                            domain: companyData.email ? companyData.email.split('@')[1] : ''
                        }
                    };
                    console.log('[HubSpotService] createCompany payload:', payload);
                    return {
                        success: true,
                        id: 'HS-COMPANY-' + Date.now(),
                        payload: payload
                    };
                },
                associateContactToCompany: function(contactId, companyId) {
                    // PUT /api/hubspot/associations
                    console.log('[HubSpotService] associateContactToCompany:', { contactId, companyId });
                    return { success: true };
                },
                syncOnboarding: function(accountDetails, formData) {
                    // Combined sync method for onboarding
                    var contactResult = this.createContact({
                        first_name: accountDetails.first_name,
                        last_name: accountDetails.last_name,
                        email: accountDetails.business_email,
                        job_title: accountDetails.job_title,
                        business_name: accountDetails.business_name,
                        mobile_number: accountDetails.mobile_number,
                        country: accountDetails.country,
                        email_verified: true,
                        marketing_consent: formData.marketing.consent,
                        marketing_consent_timestamp: formData.marketing.consent_timestamp,
                        test_credit_eligible: formData.test_credits.eligible
                    });
                    
                    var companyResult = this.createCompany({
                        company_name: accountDetails.business_name,
                        country: accountDetails.country,
                        email: accountDetails.business_email
                    });
                    
                    if (contactResult.success && companyResult.success) {
                        this.associateContactToCompany(contactResult.id, companyResult.id);
                    }
                    
                    return {
                        success: true,
                        contact: contactResult,
                        company: companyResult,
                        synced_at: new Date().toISOString()
                    };
                }
            };
            
            // Execute HubSpot sync
            var hubspotResult = HubSpotService.syncOnboarding(provisioningResult.account_details, formData);
            provisioningResult.hubspot_sync = hubspotResult;
            
            console.log('[Provisioning] Account created:', provisioningResult);
            console.log('[HubSpot] Sync completed:', hubspotResult);
            console.log('[Audit] Event logged:', provisioningResult.audit);
            
            // Audit log: Signup completed
            AuditService.logSignupComplete(email, accountId);
            
            if (formData.test_credits.eligible) {
                console.log('[Credits] 100 test SMS credits applied to account');
                AuditService.log('test_credits_applied', { email: email, amount: 100, reason: 'marketing_opt_in' });
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

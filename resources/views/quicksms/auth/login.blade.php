@extends('layouts.fullwidth')
@section('title', 'Login')
@section('content')
<style>
:root {
    --qs-primary: #886CC0;
    --qs-primary-light: #a78bda;
    --qs-primary-dark: #6b5b95;
}

body {
    background-color: #f0ebf8 !important;
}

.auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.auth-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 0 40px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 440px;
    padding: 2.5rem;
}

.auth-logo {
    text-align: center;
    margin-bottom: 1.5rem;
}

.auth-logo img {
    height: 45px;
}

.auth-title {
    text-align: center;
    font-weight: 600;
    color: #333;
    margin-bottom: 2rem;
    font-size: 1.25rem;
}

.form-label {
    font-weight: 500;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e0e6ed;
    font-size: 0.95rem;
}

.form-control:focus {
    border-color: var(--qs-primary);
    box-shadow: 0 0 0 0.15rem rgba(136, 108, 192, 0.15);
}

.form-control::placeholder {
    color: #adb5bd;
}

.password-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    z-index: 5;
}

.password-toggle:hover {
    color: var(--qs-primary);
}

.form-check-input:checked {
    background-color: var(--qs-primary);
    border-color: var(--qs-primary);
}

.forgot-link {
    color: var(--qs-primary);
    text-decoration: none;
    font-size: 0.875rem;
}

.forgot-link:hover {
    color: var(--qs-primary-dark);
    text-decoration: underline;
}

.btn-signin {
    background: var(--qs-primary);
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 1rem;
    color: #fff;
    width: 100%;
    margin-top: 1.5rem;
    transition: all 0.2s ease;
}

.btn-signin:hover {
    background: var(--qs-primary-dark);
    color: #fff;
}

.btn-signin:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.signup-link {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.signup-link a {
    color: var(--qs-primary);
    text-decoration: none;
    font-weight: 500;
}

.signup-link a:hover {
    text-decoration: underline;
}

.login-status {
    padding: 0.875rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.mfa-section {
    text-align: center;
}

.mfa-method-card {
    border: 2px solid #e0e6ed;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
    text-align: left;
}

.mfa-method-card:hover {
    border-color: var(--qs-primary-light);
    background: #faf9fc;
}

.mfa-method-card.active {
    border-color: var(--qs-primary);
    background: #f8f5ff;
}

.mfa-method-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f0e6ff 0%, #e6d9ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    color: var(--qs-primary);
    font-size: 1rem;
}

.mfa-method-check {
    font-size: 1.25rem;
    opacity: 0;
    transition: opacity 0.2s ease;
    color: var(--qs-primary);
}

.mfa-method-card.active .mfa-method-check {
    opacity: 1;
}

.otp-input {
    letter-spacing: 0.5rem;
    font-weight: 600;
    font-size: 1.25rem;
    text-align: center;
}

.back-link {
    display: inline-flex;
    align-items: center;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.875rem;
    margin-top: 1rem;
}

.back-link:hover {
    color: var(--qs-primary);
}
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" onerror="this.onerror=null; this.innerHTML='<span style=\'font-size: 1.75rem; font-weight: 700; color: #886CC0;\'>QuickSMS</span>';">
        </div>
        
        <div id="loginStep1">
            <h4 class="auth-title">Sign in your account</h4>
            
            @if ($errors->any())
            <div class="alert alert-danger mb-3">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif
            
            <div class="mb-3 p-3 rounded" style="background: rgba(136, 108, 192, 0.1); border: 1px solid rgba(136, 108, 192, 0.2); font-size: 0.85rem;">
                <strong style="color: #1a1a2e;">Sign in with your account credentials</strong><br>
                <small style="color: #333;">Don't have an account? <a href="{{ url('/signup') }}" style="color: #886cc0; font-weight: 500;">Sign up here</a></small>
            </div>
            
            <form id="loginForm" method="POST" action="{{ route('auth.login.submit') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required autocomplete="email" value="{{ old('email') }}">
                    <div class="invalid-feedback" id="emailError">Please enter a valid email address</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword" tabindex="-1">
                            <i class="fa fa-eye-slash" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" id="passwordError">Please enter your password</div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label small" for="rememberMe">Remember my preference</label>
                    </div>
                    <a href="{{ url('/forgot-password') }}" class="forgot-link">Forgot Password?</a>
                </div>
                
                <div class="login-status d-none" id="loginStatus"></div>
                
                <button type="submit" class="btn btn-signin" id="loginBtn">
                    <span class="btn-text">Sign In</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Signing in...
                    </span>
                </button>
            </form>
            
            <div class="signup-link">
                Don't have an account? <a href="{{ url('/signup') }}">Sign up</a>
            </div>
        </div>
        
        <div id="loginStep2" class="d-none">
            <h4 class="auth-title" id="mfaStepTitle">Verify it's you</h4>
            <p class="text-muted text-center mb-4" id="mfaStepSubtitle">Choose how you want to verify.</p>
            
            <div id="policyChangedAlert" class="alert d-none mb-4" style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); border-radius: 0.5rem;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-shield-alt me-3 mt-1" style="color: #dc3545;"></i>
                    <div>
                        <strong style="color: #dc3545;">Security Policy Changed</strong>
                        <p class="mb-0 small" style="color: #495057;">Your account security policy has changed. Please set up an approved MFA method to continue.</p>
                    </div>
                </div>
            </div>
            
            <div id="mfaMethodSelection">
                <div class="mfa-method-card mb-2" id="totpMethodCard" data-method="totp">
                    <div class="d-flex align-items-center">
                        <div class="mfa-method-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">Authenticator App</div>
                            <small class="text-muted">Use Google Authenticator, Microsoft Authenticator, etc.</small>
                        </div>
                        <i class="fas fa-check-circle mfa-method-check"></i>
                    </div>
                </div>
                
                <div class="mfa-method-card active mb-3" id="smsMethodCard" data-method="sms">
                    <div class="d-flex align-items-center">
                        <div class="mfa-method-icon">
                            <i class="fas fa-comment-sms"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">One-time passcode via SMS or RCS</div>
                            <small class="text-muted">Send code to ****<span id="maskedMobile"></span></small>
                        </div>
                        <i class="fas fa-check-circle mfa-method-check"></i>
                    </div>
                </div>
            </div>
            
            <div id="smsChannelOptions" class="mb-3 d-none">
                <label class="form-label text-muted small mb-2">Delivery Channel</label>
                <div class="d-flex gap-2">
                    <div class="form-check form-check-inline flex-grow-1">
                        <input class="form-check-input" type="radio" name="smsChannel" id="channelSms" value="sms" checked>
                        <label class="form-check-label" for="channelSms">
                            <i class="fas fa-sms me-1"></i> SMS
                        </label>
                    </div>
                    <div class="form-check form-check-inline flex-grow-1" id="rcsChannelOption">
                        <input class="form-check-input" type="radio" name="smsChannel" id="channelRcs" value="rcs">
                        <label class="form-check-label" for="channelRcs">
                            <i class="fas fa-message me-1"></i> RCS
                            <span class="badge bg-success-light text-success ms-1" style="font-size: 0.65rem;">Enhanced</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div id="noMobileWarning" class="alert alert-warning d-none mb-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                    <div>
                        <strong>Mobile number required</strong>
                        <p class="mb-2 small">You need a mobile number on your account to receive a code.</p>
                        <a href="/account/details" class="btn btn-sm btn-outline-primary" id="updateAccountLink">
                            <i class="fas fa-user-edit me-1"></i>Update account details
                        </a>
                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary d-none" id="contactAdminLink">
                            <i class="fas fa-headset me-1"></i>Contact admin
                        </a>
                    </div>
                </div>
            </div>
            
            <div id="forcedEnrollmentSection" class="d-none mb-4">
                <div class="text-center mb-3">
                    <div style="width: 60px; height: 60px; background: rgba(111, 66, 193, 0.1); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-mobile-alt" style="font-size: 1.5rem; color: var(--qs-primary);"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">Set Up Authenticator App</h6>
                    <p class="text-muted small mb-3">Scan the QR code or enter the secret key in your authenticator app.</p>
                </div>
                <div class="text-center mb-3 p-3" style="background: #f8f9fa; border-radius: 0.5rem;">
                    <i class="fas fa-qrcode fa-3x text-muted mb-2"></i>
                    <p class="text-muted small mb-2">QR code placeholder (TODO: Backend integration)</p>
                    <div style="font-family: monospace; background: #fff; padding: 0.5rem; border-radius: 0.25rem; font-size: 0.9rem; letter-spacing: 0.1em;">
                        JBSWY3DPEHPK3PXP
                    </div>
                    <small class="text-muted">Copy this secret key into your authenticator app</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Verification Code</label>
                    <input type="text" class="form-control otp-input" id="enrollmentCode" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                    <small class="text-muted">Enter the code from your authenticator app to complete setup</small>
                </div>
                <button type="button" class="btn btn-signin" id="completeEnrollmentBtn">
                    <i class="fas fa-check-circle me-2"></i>Complete Setup & Continue
                </button>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-link back-link" id="backToLoginFromEnrollment">
                        <i class="fas fa-arrow-left me-1"></i> Back to login
                    </button>
                </div>
            </div>
            
            <div id="sendCodeSection" class="mb-4">
                <button type="button" class="btn btn-signin" id="sendCodeBtn">
                    <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send Code</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Sending...
                    </span>
                </button>
                <div class="text-center mt-2 d-none" id="resendInfo">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        You can resend in <span id="resendCountdown">15:00</span>
                    </small>
                </div>
            </div>
            
            <form id="mfaForm" novalidate>
                <div class="mb-4 d-none" id="otpInputSection">
                    <label class="form-label" for="otpCode">
                        <span id="codeLabel">Verification Code</span> <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control otp-input" id="otpCode" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code">
                    <div class="invalid-feedback" id="otpError">Please enter the verification code</div>
                    <div class="d-flex justify-content-between align-items-center mt-2" id="smsCodeHelpers">
                        <small class="text-muted">Code expires in <span id="otpCountdown">5:00</span></small>
                        <button type="button" class="btn btn-link btn-sm p-0" id="resendOtpBtn" disabled style="color: var(--qs-primary);">Resend Code</button>
                    </div>
                    <div class="mt-2 d-none" id="totpCodeHelpers">
                        <small class="text-muted">Enter the code from your authenticator app</small>
                    </div>
                    <div class="alert alert-info small mt-3" id="testOtpCode">
                        <i class="fas fa-info-circle me-2"></i><strong>Test Mode:</strong> Your code is <span class="fw-bold fs-5" id="displayOtp"></span>
                    </div>
                </div>
                
                <div class="login-status d-none" id="mfaStatus"></div>
                
                <button type="submit" class="btn btn-signin" id="verifyBtn">
                    <span class="btn-text">Verify & Continue</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Verifying...
                    </span>
                </button>
                
                <div class="text-center mt-3">
                    <a href="javascript:void(0)" class="forgot-link" id="tryAnotherMethod">Try another method</a>
                </div>
                
                <div class="text-center">
                    <button type="button" class="btn btn-link back-link" id="backToLogin">
                        <i class="fas fa-arrow-left me-1"></i> Back to login
                    </button>
                </div>
                
                <div class="text-center mt-3 d-none" id="devBypassSection">
                    <div class="border-top pt-3">
                        <span class="badge bg-warning text-dark mb-2">
                            <i class="fas fa-flask me-1"></i>Development Mode
                        </span>
                        <br>
                        <a href="javascript:void(0)" class="text-warning small" id="skipMfaDev">
                            <i class="fas fa-forward me-1"></i>Skip MFA (Dev)
                        </a>
                        <div class="small text-muted mt-1">Test OTP: <code>000000</code></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/account-policy-service.js') }}"></script>
<script>
$(document).ready(function() {
    var currentEmail = null;
    var currentMobile = null;
    var currentOtp = null;
    var otpExpiry = null;
    var countdownInterval = null;
    var resendCooldown = 30;
    
    var EnvironmentConfig = {
        APP_ENV: '{{ config("app.env", "local") }}',
        isProduction: function() {
            return this.APP_ENV === 'production';
        },
        isDevelopment: function() {
            return !this.isProduction();
        }
    };
    
    var SecurityConfig = {
        rate_limits: {
            login_per_ip: { max: 10, window_minutes: 15 },
            login_per_email: { max: 5, window_minutes: 15 },
            otp_verify_attempts: { max: 5, window_minutes: 5 },
            otp_send_cooldown: { cooldown_minutes: 15 },
            otp_send_daily: { max: 4, window_minutes: 1440 }
        },
        account_lockout: {
            max_failed_attempts: 5,
            lockout_duration_minutes: 30
        },
        session: {
            idle_timeout_minutes: 30,
            absolute_timeout_hours: 12,
            cookie_secure: true,
            cookie_httponly: true,
            cookie_samesite: 'Lax'
        },
        dev_bypass: {
            enabled: true,
            test_otp: '000000',
            allowed_groups: ['internal_testers', 'developers', 'qa_team']
        }
    };
    
    var SessionManager = {
        lastActivity: Date.now(),
        sessionStart: Date.now(),
        timeoutWarningShown: false,
        
        init: function() {
            var self = this;
            $(document).on('mousemove keypress click scroll', function() {
                self.updateActivity();
            });
            setInterval(function() {
                self.checkTimeout();
            }, 60000);
            this.setSecureCookieDefaults();
        },
        
        updateActivity: function() {
            this.lastActivity = Date.now();
            this.timeoutWarningShown = false;
        },
        
        checkTimeout: function() {
            var idleMs = Date.now() - this.lastActivity;
            var idleMinutes = idleMs / 60000;
            var timeoutMinutes = SecurityConfig.session.idle_timeout_minutes;
            if (idleMinutes >= timeoutMinutes) {
                this.handleTimeout();
            } else if (idleMinutes >= timeoutMinutes - 5 && !this.timeoutWarningShown) {
                this.showTimeoutWarning(Math.ceil(timeoutMinutes - idleMinutes));
                this.timeoutWarningShown = true;
            }
        },
        
        handleTimeout: function() {
            AuditService.log('session_timeout', { email: currentEmail, idle_minutes: SecurityConfig.session.idle_timeout_minutes });
            alert('Your session has expired due to inactivity. Please log in again.');
            window.location.href = '/login';
        },
        
        showTimeoutWarning: function(minutesRemaining) {
            console.log('[Session] Warning: Session will expire in ' + minutesRemaining + ' minutes due to inactivity');
        },
        
        setSecureCookieDefaults: function() {
            console.log('[Session] Cookie settings applied');
        }
    };
    
    var IPService = {
        currentIP: null,
        init: function() {
            this.currentIP = this.getClientIP();
        },
        getClientIP: function() {
            return '{{ request()->ip() ?? "127.0.0.1" }}';
        },
        logLoginAttempt: function(email, success, details) {
            var logEntry = {
                ip: this.currentIP,
                email: email,
                success: success,
                timestamp: new Date().toISOString(),
                user_agent: navigator.userAgent,
                details: details || {}
            };
            console.log('[IPService] Login attempt:', logEntry);
            return logEntry;
        }
    };
    
    var DevBypassService = {
        canBypass: function(user) {
            if (EnvironmentConfig.isProduction()) {
                return { allowed: false, reason: 'production_environment' };
            }
            if (!SecurityConfig.dev_bypass.enabled) {
                return { allowed: false, reason: 'bypass_disabled' };
            }
            var userGroups = user.groups || [];
            var allowedGroups = SecurityConfig.dev_bypass.allowed_groups;
            var hasAllowedGroup = userGroups.some(function(g) {
                return allowedGroups.includes(g);
            });
            if (user.dev_bypass_enabled === true || hasAllowedGroup) {
                return { allowed: true };
            }
            return { allowed: false, reason: 'user_not_authorized' };
        },
        isTestOtp: function(code) {
            if (EnvironmentConfig.isProduction()) {
                return false;
            }
            return code === SecurityConfig.dev_bypass.test_otp;
        }
    };
    
    var AuditService = {
        log: function(event, data) {
            var entry = {
                event: event,
                timestamp: new Date().toISOString(),
                ip: IPService.currentIP,
                user_agent: navigator.userAgent,
                data: data
            };
            console.log('[AuditService]', event, entry);
            return entry;
        }
    };
    
    var MockUsers = {
        'john@techstart.io': { password: 'Demo123!', name: 'John Smith', company: 'TechStart Solutions', mobile: '7911123456', mfa_enabled: true, totp_enabled: true, rcs_enabled: true, groups: ['developers'], dev_bypass_enabled: true },
        'sarah@edulearn.ac.uk': { password: 'Demo123!', name: 'Sarah Johnson', company: 'EduLearn Institute', mobile: '7922234567', mfa_enabled: true, totp_enabled: false, rcs_enabled: false, groups: [], dev_bypass_enabled: false },
        'mike@greenenergy.com': { password: 'Demo123!', name: 'Mike Wilson', company: 'GreenEnergy Co', mobile: null, mfa_enabled: true, totp_enabled: true, rcs_enabled: false, groups: ['qa_team'], dev_bypass_enabled: true },
        'demo@quicksms.io': { password: 'demo123', name: 'Demo User', company: 'QuickSMS Demo', mobile: '7900000000', mfa_enabled: true, totp_enabled: true, rcs_enabled: true, groups: ['internal_testers'], dev_bypass_enabled: true },
        // Test user for policy enforcement - enrolled only in SMS (no TOTP), to test when authenticator-only policy is enforced
        'policy-test@example.com': { password: 'test123', name: 'Policy Test User', company: 'Test Corp', mobile: null, mfa_enabled: true, totp_enabled: false, rcs_enabled: false, groups: [], dev_bypass_enabled: false }
    };
    
    // Account MFA Policy - wraps centralized AccountPolicyService
    var AccountMfaPolicy = {
        get mfa_required() { return AccountPolicyService.isMfaRequired(); },
        get allowed_methods() { return AccountPolicyService.getMfaMethods(); },
        checkPolicyCompliance: function(user) {
            return AccountPolicyService.checkMfaCompliance(user);
        },
        getAvailableMethods: function(user) {
            var methods = [];
            var allowed = this.allowed_methods;
            if (allowed.authenticator) {
                methods.push('authenticator');
            }
            if (allowed.sms_rcs && user.mobile) {
                methods.push('sms_rcs');
            }
            return methods;
        }
    };
    
    // Account IP Policy - wraps centralized AccountPolicyService
    var AccountIPPolicy = {
        isIPAllowed: function(clientIP) {
            return AccountPolicyService.isIpAllowed(clientIP);
        },
        generateRequestId: function() {
            return AccountPolicyService.generateRequestId();
        }
    };
    
    var forcedEnrollmentMode = false;
    
    $('#togglePassword').on('click', function() {
        var passwordField = $('#password');
        var icon = $('#togglePasswordIcon');
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });
    
    $('#loginForm').on('submit', function(e) {
        var email = $('#email').val().trim().toLowerCase();
        var password = $('#password').val();
        
        $('.is-invalid').removeClass('is-invalid');
        $('#loginStatus').addClass('d-none');
        
        var isValid = true;
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $('#email').addClass('is-invalid');
            isValid = false;
        }
        if (!password) {
            $('#password').addClass('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            return;
        }
        
        var $btn = $('#loginBtn');
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        $btn.prop('disabled', true);
    });
    
    $('.mfa-method-card').on('click', function() {
        // Block if in forced enrollment mode
        if (forcedEnrollmentMode) {
            console.log('[Security] Blocked: User in forced enrollment mode');
            return;
        }
        
        $('.mfa-method-card').removeClass('active');
        $(this).addClass('active');
        
        var method = $(this).data('method');
        if (method === 'sms') {
            $('#smsChannelOptions').removeClass('d-none');
            $('#smsCodeHelpers').removeClass('d-none');
            $('#totpCodeHelpers').addClass('d-none');
            $('#codeLabel').text('Verification Code');
            $('#sendCodeSection').removeClass('d-none');
            $('#otpInputSection').addClass('d-none');
        } else {
            $('#smsChannelOptions').addClass('d-none');
            $('#smsCodeHelpers').addClass('d-none');
            $('#totpCodeHelpers').removeClass('d-none');
            $('#codeLabel').text('Authenticator Code');
            $('#sendCodeSection').addClass('d-none');
            $('#otpInputSection').removeClass('d-none');
            $('#testOtpCode').addClass('d-none');
        }
    });
    
    function generateOtp() {
        return String(Math.floor(100000 + Math.random() * 900000));
    }
    
    function startOtpCountdown(seconds) {
        if (countdownInterval) clearInterval(countdownInterval);
        var remaining = seconds;
        
        function updateDisplay() {
            var mins = Math.floor(remaining / 60);
            var secs = remaining % 60;
            $('#otpCountdown').text(mins + ':' + (secs < 10 ? '0' : '') + secs);
        }
        
        updateDisplay();
        countdownInterval = setInterval(function() {
            remaining--;
            updateDisplay();
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                $('#otpCode').addClass('is-invalid');
                $('#otpError').text('Code has expired. Please request a new one.');
                currentOtp = null;
            }
        }, 1000);
    }
    
    $('#sendCodeBtn').on('click', function() {
        // Block if in forced enrollment mode
        if (forcedEnrollmentMode) {
            console.log('[Security] Blocked: User in forced enrollment mode');
            return;
        }
        
        var $btn = $(this);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        $btn.prop('disabled', true);
        
        var channel = $('input[name="smsChannel"]:checked').val() || 'sms';
        
        setTimeout(function() {
            currentOtp = generateOtp();
            otpExpiry = Date.now() + (5 * 60 * 1000);
            
            AuditService.log('otp_sent', { email: currentEmail, channel: channel, mobile_masked: '****' + (currentMobile ? currentMobile.slice(-4) : '') });
            
            $('#displayOtp').text(currentOtp);
            $('#testOtpCode').removeClass('d-none');
            $('#otpInputSection').removeClass('d-none');
            $('#sendCodeSection').addClass('d-none');
            
            startOtpCountdown(300);
            
            $btn.find('.btn-text').removeClass('d-none');
            $btn.find('.btn-loading').addClass('d-none');
            $btn.prop('disabled', false);
        }, 1500);
    });
    
    $('#resendOtpBtn').on('click', function() {
        if (!$(this).prop('disabled')) {
            $('#sendCodeBtn').click();
        }
    });
    
    $('#mfaForm').on('submit', function(e) {
        e.preventDefault();
        
        // Block if in forced enrollment mode
        if (forcedEnrollmentMode) {
            console.log('[Security] Blocked: User in forced enrollment mode');
            return;
        }
        
        var code = $('#otpCode').val().trim();
        
        $('#otpCode').removeClass('is-invalid');
        $('#mfaStatus').addClass('d-none');
        
        if (!code || code.length !== 6 || !/^\d{6}$/.test(code)) {
            $('#otpCode').addClass('is-invalid');
            $('#otpError').text('Please enter a valid 6-digit code');
            return;
        }
        
        var $btn = $('#verifyBtn');
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        $btn.prop('disabled', true);
        
        setTimeout(function() {
            var isValid = false;
            var method = $('.mfa-method-card.active').data('method');
            
            if (method === 'totp') {
                isValid = DevBypassService.isTestOtp(code) || code === '123456';
            } else {
                isValid = code === currentOtp || DevBypassService.isTestOtp(code);
            }
            
            if (isValid) {
                AuditService.log('mfa_success', { email: currentEmail, method: method });
                if (countdownInterval) clearInterval(countdownInterval);
                window.location.href = '/dashboard';
            } else {
                AuditService.log('mfa_failed', { email: currentEmail, method: method });
                $('#mfaStatus').html('<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>Invalid verification code. Please try again.</div>').removeClass('d-none');
            }
            
            $btn.find('.btn-text').removeClass('d-none');
            $btn.find('.btn-loading').addClass('d-none');
            $btn.prop('disabled', false);
        }, 1000);
    });
    
    $('#tryAnotherMethod').on('click', function() {
        $('#otpInputSection').addClass('d-none');
        $('#sendCodeSection').removeClass('d-none');
        $('#otpCode').val('');
        $('#mfaStatus').addClass('d-none');
        if (countdownInterval) clearInterval(countdownInterval);
    });
    
    $('#backToLogin').on('click', function() {
        $('#loginStep2').addClass('d-none');
        $('#loginStep1').removeClass('d-none');
        $('#password').val('');
        $('#otpCode').val('');
        $('#loginStatus').addClass('d-none');
        $('#mfaStatus').addClass('d-none');
        currentEmail = null;
        currentMobile = null;
        currentOtp = null;
        forcedEnrollmentMode = false;
        if (countdownInterval) clearInterval(countdownInterval);
    });
    
    // Back to login from forced enrollment
    $('#backToLoginFromEnrollment').on('click', function() {
        $('#loginStep2').addClass('d-none');
        $('#loginStep1').removeClass('d-none');
        $('#password').val('');
        $('#enrollmentCode').val('');
        $('#loginStatus').addClass('d-none');
        
        // Hide enrollment, reset visibility
        $('#forcedEnrollmentSection').addClass('d-none');
        $('#policyChangedAlert').addClass('d-none');
        
        currentEmail = null;
        currentMobile = null;
        forcedEnrollmentMode = false;
    });
    
    // Complete forced enrollment
    $('#completeEnrollmentBtn').on('click', function() {
        var code = $('#enrollmentCode').val().trim();
        
        if (!code || code.length !== 6) {
            $('#enrollmentCode').addClass('is-invalid');
            return;
        }
        $('#enrollmentCode').removeClass('is-invalid');
        
        // TODO: Validate TOTP code with backend
        // For now, accept any 6-digit code or test code
        if (code === '000000' || code.length === 6) {
            // Update mock user state to enable TOTP
            var user = mockUsers.find(function(u) { return u.email === currentEmail; });
            if (user) {
                user.totp_enabled = true;
            }
            
            AuditService.log('mfa_enrollment_completed', {
                user: currentEmail,
                method: 'authenticator',
                reason: 'POLICY_CHANGED',
                timestamp: new Date().toISOString(),
                source_ip: IPService.currentIP
            });
            
            forcedEnrollmentMode = false;
            
            // Enrollment successful - proceed to dashboard
            window.location.href = '/dashboard';
        } else {
            $('#enrollmentCode').addClass('is-invalid');
        }
    });
    
    // Allow Enter key to submit enrollment
    $('#enrollmentCode').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#completeEnrollmentBtn').click();
        }
    });
    
    $('#skipMfaDev').on('click', function() {
        AuditService.log('mfa_bypassed_dev', { email: currentEmail });
        window.location.href = '/dashboard';
    });
    
    SessionManager.init();
    IPService.init();
});
</script>
@endpush
@endsection

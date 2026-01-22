@extends('layouts.fullwidth')
@section('title', 'Login')
@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card overflow-hidden">
        <div class="row g-0">
            <div class="col-xl-6 d-none d-xl-block">
                <div class="auth-img-wrap">
                    <div class="auth-img-content">
                        <img src="{{ asset('images/quicksms-logo-white.png') }}" alt="QuickSMS" class="auth-logo mb-4" onerror="this.onerror=null; this.src='{{ asset('images/quicksms-logo.png') }}';">
                        <h3 class="text-white mb-3">Welcome to QuickSMS</h3>
                        <p class="text-white-50">Your trusted SMS & RCS messaging platform for business communication.</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card-body auth-form p-4 p-lg-5">
                    <div class="d-xl-none text-center mb-4">
                        <a href="{{ url('/') }}"><img src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 40px;"></a>
                    </div>
                    <h4 class="mb-1">Sign In</h4>
                    <p class="text-muted mb-4">Enter your credentials to access your account</p>
                    
                    <div id="loginStep1">
                        <form id="loginForm" novalidate>
                            <div class="mb-4">
                                <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" placeholder="name@company.com" required autocomplete="email">
                                <div class="invalid-feedback" id="emailError">Please enter a valid email address</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="form-control pe-5" id="password" placeholder="Enter your password" required autocomplete="current-password">
                                    <button type="button" class="show-pass-btn" id="togglePassword" tabindex="-1">
                                        <i class="fa fa-eye-slash" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="passwordError">Please enter your password</div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>
                                <a href="{{ url('/forgot-password') }}" class="text-primary small">Forgot password?</a>
                            </div>
                            
                            <div class="login-status mb-3 d-none" id="loginStatus"></div>
                            
                            <button type="submit" class="btn btn-primary d-block w-100" id="loginBtn">
                                <span class="btn-text">Sign In</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Signing in...
                                </span>
                            </button>
                        </form>
                    </div>
                    
                    <div id="loginStep2" class="d-none">
                        <h4 class="mb-1">Verify it's you</h4>
                        <p class="text-muted mb-4">Choose how you want to verify.</p>
                        
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
                                    <i class="fas fa-check-circle text-primary mfa-method-check"></i>
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
                                    <i class="fas fa-check-circle text-primary mfa-method-check"></i>
                                </div>
                            </div>
                        </div>
                        
                        <form id="mfaForm" novalidate>
                            <div class="mb-4">
                                <label class="form-label" for="otpCode">
                                    <span id="codeLabel">Verification Code</span> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control otp-input" id="otpCode" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code">
                                <div class="invalid-feedback" id="otpError">Please enter the verification code</div>
                                <div class="d-flex justify-content-between align-items-center mt-2" id="smsCodeHelpers">
                                    <small class="text-muted">Code expires in <span id="otpCountdown">5:00</span></small>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-primary" id="resendOtpBtn" disabled>Resend Code</button>
                                </div>
                                <div class="mt-2 d-none" id="totpCodeHelpers">
                                    <small class="text-muted">Enter the code from your authenticator app</small>
                                </div>
                                <div class="alert alert-info small mt-3" id="testOtpCode">
                                    <i class="fas fa-info-circle me-2"></i><strong>Test Mode:</strong> Your code is <span class="fw-bold fs-5" id="displayOtp"></span>
                                </div>
                            </div>
                            
                            <div class="mfa-status mb-3 d-none" id="mfaStatus"></div>
                            
                            <button type="submit" class="btn btn-primary d-block w-100" id="verifyBtn">
                                <span class="btn-text">Verify & Continue</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Verifying...
                                </span>
                            </button>
                            
                            <div class="text-center mt-3">
                                <a href="javascript:void(0)" class="text-primary small" id="tryAnotherMethod">Try another method</a>
                            </div>
                            
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-link text-muted btn-sm" id="backToLogin">
                                    <i class="fas fa-arrow-left me-1"></i> Back to login
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <span class="text-muted">Don't have an account?</span>
                        <a class="text-primary ms-1 fw-medium" href="{{ url('/signup') }}">Sign up</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --qs-primary: #6b5b95;
    --qs-primary-light: #886CC0;
    --qs-primary-dark: #5a4a80;
    --qs-gradient: linear-gradient(135deg, #6b5b95 0%, #886CC0 100%);
}

.auth-img-wrap {
    background: var(--qs-gradient);
    height: 100%;
    min-height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.auth-img-wrap::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.auth-img-wrap::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -30%;
    width: 60%;
    height: 60%;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    border-radius: 50%;
}

.auth-img-content {
    position: relative;
    z-index: 1;
    text-align: center;
    max-width: 280px;
}

.auth-logo {
    height: 50px;
    filter: brightness(0) invert(1);
}

.auth-form {
    background: #fff;
}

.card {
    border: none;
    box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
    border-radius: 1rem;
}

.form-control {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e0e6ed;
}

.form-control:focus {
    border-color: var(--qs-primary-light);
    box-shadow: 0 0 0 0.2rem rgba(136, 108, 192, 0.15);
}

.btn-primary {
    background: var(--qs-gradient);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

.btn-primary:hover, .btn-primary:focus {
    background: linear-gradient(135deg, var(--qs-primary-dark) 0%, var(--qs-primary) 100%);
    box-shadow: 0 4px 15px rgba(107, 91, 149, 0.3);
}

.text-primary {
    color: var(--qs-primary) !important;
}

a.text-primary:hover {
    color: var(--qs-primary-dark) !important;
}

.show-pass-btn {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 45px;
    background: transparent;
    border: none;
    color: #6c757d;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5;
    transition: color 0.2s ease;
}

.show-pass-btn:hover {
    color: var(--qs-primary);
}

.show-pass-btn:focus {
    outline: none;
}

.form-check-input:checked {
    background-color: var(--qs-primary);
    border-color: var(--qs-primary);
}

.mfa-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #f0e6ff 0%, #e6d9ff 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 1.5rem;
    color: var(--qs-primary);
}

.otp-input {
    letter-spacing: 0.5rem;
    font-weight: 600;
    font-size: 1.25rem;
    text-align: center;
}

.mfa-method-card {
    border: 2px solid #e0e6ed;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
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
}

.mfa-method-card.active .mfa-method-check {
    opacity: 1;
}

.login-status, .mfa-status {
    padding: 0.875rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.login-status.alert, .mfa-status.alert {
    display: flex;
    align-items: center;
}

.login-status.alert i, .mfa-status.alert i {
    flex-shrink: 0;
}

.form-label {
    font-weight: 500;
    color: #344054;
    margin-bottom: 0.5rem;
}

@media (max-width: 1199px) {
    .auth-form {
        border-radius: 1rem;
    }
}
</style>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var currentEmail = null;
    var currentMobile = null;
    var currentOtp = null;
    var otpExpiry = null;
    var countdownInterval = null;
    var resendCooldown = 30;
    
    var SecurityConfig = {
        rate_limits: {
            login_per_ip: { max: 10, window_minutes: 15 },
            login_per_email: { max: 5, window_minutes: 15 },
            otp_verify_attempts: { max: 5, window_minutes: 5 }
        },
        account_lockout: {
            max_failed_attempts: 5,
            lockout_duration_minutes: 30
        }
    };
    
    var RateLimitService = {
        attempts: {},
        checkLimit: function(key, limitConfig) {
            var now = Date.now();
            var windowMs = limitConfig.window_minutes * 60 * 1000;
            if (!this.attempts[key]) this.attempts[key] = [];
            this.attempts[key] = this.attempts[key].filter(function(t) { return now - t < windowMs; });
            if (this.attempts[key].length >= limitConfig.max) {
                var retryAfter = Math.ceil((windowMs - (now - this.attempts[key][0])) / 1000);
                return { allowed: false, retryAfter: retryAfter };
            }
            return { allowed: true };
        },
        recordAttempt: function(key) {
            if (!this.attempts[key]) this.attempts[key] = [];
            this.attempts[key].push(Date.now());
        }
    };
    
    var LockoutService = {
        failedAttempts: {},
        lockedAccounts: {},
        recordFailedAttempt: function(email) {
            if (!this.failedAttempts[email]) this.failedAttempts[email] = { count: 0 };
            this.failedAttempts[email].count++;
            if (this.failedAttempts[email].count >= SecurityConfig.account_lockout.max_failed_attempts) {
                this.lockedAccounts[email] = Date.now() + (SecurityConfig.account_lockout.lockout_duration_minutes * 60 * 1000);
            }
            return this.failedAttempts[email].count;
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
        getUnlockMinutes: function(email) {
            if (!this.lockedAccounts[email]) return 0;
            return Math.ceil((this.lockedAccounts[email] - Date.now()) / 60000);
        },
        resetOnSuccess: function(email) {
            delete this.failedAttempts[email];
            delete this.lockedAccounts[email];
        }
    };
    
    var AuditService = {
        log: function(event, details) {
            var entry = { event: event, timestamp: new Date().toISOString(), details: details };
            console.log('[AuditService]', event, entry);
            return entry;
        }
    };
    
    var MockUsers = {
        'test@example.com': { password: 'Password123!', status: 'active', mfa_enabled: true, mobile: '+447700900123', email_verified: true },
        'suspended@example.com': { password: 'Password123!', status: 'suspended', mfa_enabled: true, mobile: '+447700900456', email_verified: true },
        'pending@example.com': { password: 'Password123!', status: 'pending', mfa_enabled: true, mobile: '+447700900789', email_verified: false },
        'demo@quicksms.com': { password: 'Demo2026!', status: 'active', mfa_enabled: true, mobile: '+447700900999', email_verified: true }
    };
    
    function showLoginError(message, type) {
        type = type || 'danger';
        var iconClass = type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle';
        $('#loginStatus')
            .removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert alert-' + type)
            .html('<i class="fas ' + iconClass + ' me-2"></i>' + message);
    }
    
    function showLoginSuccess(message) {
        $('#loginStatus')
            .removeClass('d-none alert-danger alert-warning')
            .addClass('alert alert-success')
            .html('<i class="fas fa-check-circle me-2"></i>' + message);
    }
    
    function resetButton($btn) {
        $btn.prop('disabled', false);
        $btn.find('.btn-text').removeClass('d-none');
        $btn.find('.btn-loading').addClass('d-none');
    }
    
    $('#togglePassword').on('click', function() {
        var $input = $('#password');
        var $icon = $('#togglePasswordIcon');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });
    
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        var email = $('#email').val().trim();
        var password = $('#password').val();
        
        $('#email, #password').removeClass('is-invalid');
        $('#emailError').text('Please enter a valid email address');
        $('#passwordError').text('Please enter your password');
        $('#loginStatus').addClass('d-none');
        
        var isValid = true;
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!email) {
            $('#emailError').text('Please enter your email address');
            $('#email').addClass('is-invalid');
            isValid = false;
        } else if (!emailRegex.test(email)) {
            $('#emailError').text('Please enter a valid email address');
            $('#email').addClass('is-invalid');
            isValid = false;
        }
        
        if (!password) {
            $('#password').addClass('is-invalid');
            isValid = false;
        }
        
        if (!isValid) return;
        
        if (LockoutService.isLocked(email)) {
            showLoginError('Too many failed attempts. Try again later.');
            AuditService.log('login_blocked_locked', { email: email });
            return;
        }
        
        var rateCheck = RateLimitService.checkLimit('login:' + email, SecurityConfig.rate_limits.login_per_email);
        if (!rateCheck.allowed) {
            showLoginError('Too many failed attempts. Try again later.');
            AuditService.log('login_rate_limited', { email: email });
            return;
        }
        
        RateLimitService.recordAttempt('login:' + email);
        
        var $btn = $('#loginBtn');
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        
        AuditService.log('login_attempt', { email: email });
        
        setTimeout(function() {
            var user = MockUsers[email];
            var validCredentials = false;
            
            if (user && user.password === password) {
                validCredentials = true;
            }
            
            if (!validCredentials) {
                var attempts = LockoutService.recordFailedAttempt(email);
                
                if (attempts >= SecurityConfig.account_lockout.max_failed_attempts) {
                    showLoginError('Too many failed attempts. Try again later.');
                } else {
                    showLoginError('Incorrect email or password.');
                }
                
                resetButton($btn);
                AuditService.log('login_failed', { email: email, attempts: attempts });
                return;
            }
            
            if (user.status === 'suspended') {
                showLoginError('Your account is suspended. Contact support.', 'warning');
                resetButton($btn);
                AuditService.log('login_blocked_suspended', { email: email });
                return;
            }
            
            if (user.status === 'pending') {
                showLoginError('Your account is pending activation. Please check your email.', 'warning');
                resetButton($btn);
                AuditService.log('login_blocked_pending', { email: email });
                return;
            }
            
            LockoutService.resetOnSuccess(email);
            currentEmail = email;
            currentMobile = user.mobile || '+447700900123';
            
            AuditService.log('login_password_verified', { email: email });
            
            showLoginSuccess('Password verified. Sending verification code...');
            
            setTimeout(function() {
                sendMfaOtp();
                
                $('#loginStep1').addClass('d-none');
                $('#loginStep2').removeClass('d-none');
                $('#maskedMobile').text(currentMobile.slice(-4));
                $('#otpCode').focus();
                
                resetButton($btn);
            }, 500);
            
        }, 1000);
    });
    
    function sendMfaOtp() {
        currentOtp = String(Math.floor(100000 + Math.random() * 900000));
        otpExpiry = Date.now() + (5 * 60 * 1000);
        
        console.log('[MFA] OTP sent to ' + currentMobile + ': ' + currentOtp);
        AuditService.log('mfa_otp_sent', { mobile_masked: '****' + currentMobile.slice(-4) });
        
        $('#displayOtp').text(currentOtp);
        startCountdown();
        
        var $resend = $('#resendOtpBtn');
        $resend.prop('disabled', true);
        setTimeout(function() { $resend.prop('disabled', false); }, resendCooldown * 1000);
    }
    
    function startCountdown() {
        var remaining = 5 * 60;
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
            }
        }, 1000);
    }
    
    $('#resendOtpBtn').on('click', function() {
        sendMfaOtp();
        $('#mfaStatus').removeClass('d-none error').addClass('success');
        $('#mfaStatus').html('<i class="fas fa-check-circle me-2"></i>New verification code sent.');
    });
    
    $('#otpCode').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        $(this).removeClass('is-invalid');
    });
    
    $('#mfaForm').on('submit', function(e) {
        e.preventDefault();
        
        var enteredOtp = $('#otpCode').val().trim();
        
        if (!enteredOtp || enteredOtp.length !== 6) {
            $('#otpCode').addClass('is-invalid');
            return;
        }
        
        var rateCheck = RateLimitService.checkLimit('otp_verify:' + currentEmail, SecurityConfig.rate_limits.otp_verify_attempts);
        if (!rateCheck.allowed) {
            $('#mfaStatus').removeClass('d-none success').addClass('error');
            $('#mfaStatus').html('<i class="fas fa-clock me-2"></i>Too many attempts. Try again in ' + Math.ceil(rateCheck.retryAfter / 60) + ' minutes.');
            return;
        }
        
        RateLimitService.recordAttempt('otp_verify:' + currentEmail);
        
        var $btn = $('#verifyBtn');
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        
        setTimeout(function() {
            if (!currentOtp || Date.now() > otpExpiry) {
                $('#mfaStatus').removeClass('d-none success').addClass('error');
                $('#mfaStatus').html('<i class="fas fa-exclamation-circle me-2"></i>Code expired. Please request a new code.');
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
                AuditService.log('mfa_otp_expired', { email: currentEmail });
                return;
            }
            
            if (enteredOtp !== currentOtp) {
                $('#otpCode').addClass('is-invalid');
                $('#otpError').text('Invalid verification code');
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
                AuditService.log('mfa_otp_failed', { email: currentEmail });
                return;
            }
            
            clearInterval(countdownInterval);
            LockoutService.resetOnSuccess(currentEmail);
            
            AuditService.log('mfa_otp_verified', { email: currentEmail });
            AuditService.log('login_success', { email: currentEmail, mfa_verified: true });
            
            $('#mfaStatus').removeClass('d-none error').addClass('success');
            $('#mfaStatus').html('<i class="fas fa-check-circle me-2"></i>Verification successful. Redirecting...');
            
            setTimeout(function() { window.location.href = '/dashboard'; }, 1000);
            
        }, 800);
    });
    
    var currentMfaMethod = 'sms';
    
    $('.mfa-method-card').on('click', function() {
        var method = $(this).data('method');
        selectMfaMethod(method);
    });
    
    $('#tryAnotherMethod').on('click', function() {
        var newMethod = currentMfaMethod === 'sms' ? 'totp' : 'sms';
        selectMfaMethod(newMethod);
    });
    
    function selectMfaMethod(method) {
        currentMfaMethod = method;
        
        $('.mfa-method-card').removeClass('active');
        $('.mfa-method-card[data-method="' + method + '"]').addClass('active');
        
        $('#otpCode').val('').removeClass('is-invalid');
        $('#mfaStatus').addClass('d-none');
        
        if (method === 'sms') {
            $('#smsCodeHelpers').removeClass('d-none');
            $('#totpCodeHelpers').addClass('d-none');
            $('#testOtpCode').removeClass('d-none');
            $('#codeLabel').text('Verification Code');
            sendMfaOtp();
        } else {
            $('#smsCodeHelpers').addClass('d-none');
            $('#totpCodeHelpers').removeClass('d-none');
            $('#testOtpCode').addClass('d-none');
            $('#codeLabel').text('Authenticator Code');
            currentOtp = '123456';
            if (countdownInterval) clearInterval(countdownInterval);
        }
        
        $('#otpCode').focus();
        AuditService.log('mfa_method_changed', { method: method });
    }
    
    $('#backToLogin').on('click', function() {
        $('#loginStep2').addClass('d-none');
        $('#loginStep1').removeClass('d-none');
        $('#loginStatus').addClass('d-none');
        clearInterval(countdownInterval);
        currentOtp = null;
        currentMfaMethod = 'sms';
        selectMfaMethod('sms');
    });
    
    $('#email, #password').on('input', function() {
        $(this).removeClass('is-invalid');
        $('#loginStatus').addClass('d-none');
    });
});
</script>
@endpush
@endsection

@extends('layouts.fullwidth')
@section('title', 'Login')
@section('content')
<div class="col-lg-5 col-md-7">
    <div class="card mb-0 h-auto">
        <div class="card-body">
            <div class="text-center mb-3">
                <a href="{{ url('/') }}"><img class="logo-auth" src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 48px;"></a>
            </div>
            <h4 class="text-center mb-2">Welcome back</h4>
            <p class="text-center text-muted mb-4">Sign in to your QuickSMS account</p>
            
            <div id="loginStep1">
                <form id="loginForm" novalidate>
                    <div class="form-group mb-4">
                        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" placeholder="you@company.com" required autocomplete="email">
                        <div class="invalid-feedback" id="emailError">Please enter your email address</div>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="password" placeholder="Enter your password" required autocomplete="current-password">
                            <span class="show-pass eye">
                                <i class="fa fa-eye-slash"></i>
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback" id="passwordError">Please enter your password</div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="text-primary small">Forgot password?</a>
                    </div>
                    
                    <div class="login-status mb-3 d-none" id="loginStatus"></div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Signing in...
                        </span>
                    </button>
                </form>
            </div>
            
            <div id="loginStep2" class="d-none">
                <div class="text-center mb-4">
                    <div class="mfa-icon mb-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="mb-2">Two-Factor Authentication</h5>
                    <p class="text-muted small">We've sent a verification code to your mobile number ending in <strong id="maskedMobile"></strong></p>
                </div>
                
                <form id="mfaForm" novalidate>
                    <div class="form-group mb-4">
                        <label class="form-label" for="otpCode">Verification Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control otp-input" id="otpCode" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code">
                        <div class="invalid-feedback" id="otpError">Please enter the verification code</div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">Code expires in <span id="otpCountdown">5:00</span></small>
                            <button type="button" class="btn btn-link btn-sm p-0" id="resendOtpBtn" disabled>Resend Code</button>
                        </div>
                        <div class="alert alert-info small mt-2" id="testOtpCode">
                            <strong>Test Mode:</strong> Your code is <span class="fw-bold fs-5" id="displayOtp"></span>
                        </div>
                    </div>
                    
                    <div class="mfa-status mb-3 d-none" id="mfaStatus"></div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="verifyBtn">
                        <span class="btn-text">Verify & Continue</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Verifying...
                        </span>
                    </button>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-link text-muted" id="backToLogin">
                            <i class="fas fa-arrow-left me-1"></i> Back to login
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-4">
                <span class="text-muted">Don't have an account?</span>
                <a class="text-primary ms-1" href="{{ url('/signup') }}">Sign up</a>
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
.form-check-input:checked {
    background-color: #886CC0;
    border-color: #886CC0;
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
    color: #886CC0;
}
.otp-input {
    letter-spacing: 0.5rem;
    font-weight: 600;
    font-size: 1.25rem;
    text-align: center;
}
.login-status, .mfa-status {
    padding: 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}
.login-status.error, .mfa-status.error {
    background: #f8d7da;
    color: #842029;
}
.login-status.success, .mfa-status.success {
    background: #d1e7dd;
    color: #0f5132;
}
.login-status.warning {
    background: #fff3cd;
    color: #664d03;
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
    
    // Security Configuration
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
    
    // Rate Limiting Service
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
    
    // Account Lockout Service
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
    
    // Audit Service
    var AuditService = {
        log: function(event, details) {
            var entry = {
                event: event,
                timestamp: new Date().toISOString(),
                details: details
            };
            console.log('[AuditService]', event, entry);
            return entry;
        }
    };
    
    // Mock user database
    var MockUsers = {
        'test@example.com': {
            password_hash: 'hashed_password',
            mfa_enabled: true,
            mobile: '+447700900123',
            email_verified: true
        }
    };
    
    // Show/hide password toggle
    $('.show-pass').on('click', function() {
        var $input = $(this).siblings('input');
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('active');
    });
    
    // Login form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        var email = $('#email').val().trim();
        var password = $('#password').val();
        
        // Clear previous errors
        $('#email, #password').removeClass('is-invalid');
        $('#loginStatus').addClass('d-none');
        
        // Validate
        var isValid = true;
        if (!email) {
            $('#email').addClass('is-invalid');
            isValid = false;
        }
        if (!password) {
            $('#password').addClass('is-invalid');
            isValid = false;
        }
        
        if (!isValid) return;
        
        // Check account lockout
        if (LockoutService.isLocked(email)) {
            $('#loginStatus').removeClass('d-none success warning').addClass('error');
            $('#loginStatus').html('<i class="fas fa-lock me-2"></i>Account locked. Try again in ' + LockoutService.getUnlockMinutes(email) + ' minutes.');
            AuditService.log('login_blocked_locked', { email: email });
            return;
        }
        
        // Check rate limit
        var rateCheck = RateLimitService.checkLimit('login:' + email, SecurityConfig.rate_limits.login_per_email);
        if (!rateCheck.allowed) {
            $('#loginStatus').removeClass('d-none success warning').addClass('error');
            $('#loginStatus').html('<i class="fas fa-clock me-2"></i>Too many attempts. Try again in ' + Math.ceil(rateCheck.retryAfter / 60) + ' minutes.');
            AuditService.log('login_rate_limited', { email: email });
            return;
        }
        
        RateLimitService.recordAttempt('login:' + email);
        
        var $btn = $('#loginBtn');
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        
        AuditService.log('login_attempt', { email: email });
        
        // Mock login validation
        setTimeout(function() {
            // For demo: accept any email with password "Password123!"
            var validPassword = (password === 'Password123!') || MockUsers[email];
            
            if (!validPassword) {
                var attempts = LockoutService.recordFailedAttempt(email);
                var remaining = SecurityConfig.account_lockout.max_failed_attempts - attempts;
                
                $('#loginStatus').removeClass('d-none success').addClass('error');
                if (remaining > 0) {
                    $('#loginStatus').html('<i class="fas fa-exclamation-circle me-2"></i>Invalid email or password. ' + remaining + ' attempts remaining.');
                } else {
                    $('#loginStatus').html('<i class="fas fa-lock me-2"></i>Account locked due to too many failed attempts.');
                }
                
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
                
                AuditService.log('login_failed', { email: email, attempts: attempts });
                return;
            }
            
            // Login successful - check MFA
            currentEmail = email;
            currentMobile = MockUsers[email] ? MockUsers[email].mobile : '+447700900123';
            
            AuditService.log('login_password_verified', { email: email });
            
            // All accounts have MFA enabled
            $('#loginStatus').removeClass('d-none error').addClass('success');
            $('#loginStatus').html('<i class="fas fa-check-circle me-2"></i>Password verified. Sending verification code...');
            
            // Send OTP
            setTimeout(function() {
                sendMfaOtp();
                
                // Show MFA step
                $('#loginStep1').addClass('d-none');
                $('#loginStep2').removeClass('d-none');
                $('#maskedMobile').text(currentMobile.slice(-4));
                $('#otpCode').focus();
                
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
            }, 500);
            
        }, 1000);
    });
    
    function sendMfaOtp() {
        currentOtp = String(Math.floor(100000 + Math.random() * 900000));
        otpExpiry = Date.now() + (5 * 60 * 1000);
        
        console.log('[MFA] OTP sent to ' + currentMobile + ': ' + currentOtp);
        AuditService.log('mfa_otp_sent', { mobile_masked: '****' + currentMobile.slice(-4) });
        
        // Show test mode OTP code on page
        $('#displayOtp').text(currentOtp);
        
        startCountdown();
        
        var $resend = $('#resendOtpBtn');
        $resend.prop('disabled', true);
        setTimeout(function() {
            $resend.prop('disabled', false);
        }, resendCooldown * 1000);
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
    
    // Resend OTP
    $('#resendOtpBtn').on('click', function() {
        sendMfaOtp();
        $('#mfaStatus').removeClass('d-none error').addClass('success');
        $('#mfaStatus').html('<i class="fas fa-check-circle me-2"></i>New verification code sent.');
    });
    
    // OTP input - numbers only
    $('#otpCode').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        $(this).removeClass('is-invalid');
    });
    
    // MFA form submission
    $('#mfaForm').on('submit', function(e) {
        e.preventDefault();
        
        var enteredOtp = $('#otpCode').val().trim();
        
        if (!enteredOtp || enteredOtp.length !== 6) {
            $('#otpCode').addClass('is-invalid');
            return;
        }
        
        // Check rate limit
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
            
            // MFA verified - complete login
            clearInterval(countdownInterval);
            LockoutService.resetOnSuccess(currentEmail);
            
            AuditService.log('mfa_otp_verified', { email: currentEmail });
            AuditService.log('login_success', { email: currentEmail, mfa_verified: true });
            
            $('#mfaStatus').removeClass('d-none error').addClass('success');
            $('#mfaStatus').html('<i class="fas fa-check-circle me-2"></i>Verification successful. Redirecting...');
            
            // Redirect to dashboard
            setTimeout(function() {
                window.location.href = '/dashboard';
            }, 1000);
            
        }, 800);
    });
    
    // Back to login
    $('#backToLogin').on('click', function() {
        $('#loginStep2').addClass('d-none');
        $('#loginStep1').removeClass('d-none');
        $('#loginStatus').addClass('d-none');
        clearInterval(countdownInterval);
        currentOtp = null;
    });
    
    // Clear validation on input
    $('#email, #password').on('input', function() {
        $(this).removeClass('is-invalid');
        $('#loginStatus').addClass('d-none');
    });
});
</script>
@endpush
@endsection

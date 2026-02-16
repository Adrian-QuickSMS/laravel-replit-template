@extends('layouts.fullwidth')
@section('title', 'Admin Login')
@section('content')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-primary-light: #2d5a87;
    --admin-primary-dark: #152a45;
}

body {
    background-color: #e8eef4 !important;
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

.admin-badge {
    display: inline-block;
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-light));
    color: #fff;
    padding: 0.35rem 0.85rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}

.auth-title {
    text-align: center;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
}

.auth-subtitle {
    text-align: center;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 2rem;
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
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 0.15rem rgba(30, 58, 95, 0.15);
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
    color: var(--admin-primary);
}

.form-check-input:checked {
    background-color: var(--admin-primary);
    border-color: var(--admin-primary);
}

.forgot-link {
    color: var(--admin-primary);
    text-decoration: none;
    font-size: 0.875rem;
}

.forgot-link:hover {
    color: var(--admin-primary-dark);
    text-decoration: underline;
}

.btn-signin {
    background: var(--admin-primary);
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
    background: var(--admin-primary-dark);
    color: #fff;
}

.btn-signin:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.login-status {
    padding: 0.875rem 1rem;
    border-radius: 0.5rem;
    margin-top: 1rem;
    font-size: 0.9rem;
}

.security-notice {
    background: rgba(30, 58, 95, 0.08);
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1.5rem;
    font-size: 0.8rem;
    color: #495057;
}

.security-notice i {
    color: var(--admin-primary);
}

.mfa-step {
    text-align: center;
}

.mfa-icon {
    font-size: 3rem;
    color: var(--admin-primary);
    margin-bottom: 1rem;
}

.mfa-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.mfa-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.otp-input-group {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.otp-input {
    width: 48px;
    height: 56px;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    border: 2px solid #e0e6ed;
    border-radius: 0.5rem;
}

.otp-input:focus {
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 0.15rem rgba(30, 58, 95, 0.15);
    outline: none;
}

.back-link {
    color: #6c757d;
    text-decoration: none;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
}

.back-link:hover {
    color: var(--admin-primary);
}

.mfa-method-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.mfa-method-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.25rem 1rem;
    background: #f8f9fa;
    border: 2px solid #e0e6ed;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.mfa-method-btn:hover {
    border-color: var(--admin-primary);
    background: rgba(30, 58, 95, 0.05);
}

.mfa-method-btn i {
    font-size: 1.75rem;
    color: var(--admin-primary);
    margin-bottom: 0.5rem;
}

.mfa-method-btn .method-title {
    font-weight: 600;
    font-size: 1rem;
    color: #333;
    display: block;
}

.mfa-method-btn .method-desc {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
    display: block;
}

.mfa-code-instruction {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1.25rem;
    text-align: center;
}

.resend-link {
    color: var(--admin-primary);
    text-decoration: none;
    font-size: 0.875rem;
}

.resend-link:hover {
    text-decoration: underline;
}

.copyright-text {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.8rem;
    color: #6c757d;
}
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 50px;">
        </div>
        
        <div class="text-center mb-3">
            <span class="admin-badge">Admin Control Plane</span>
        </div>
        
        <h4 class="auth-title">Admin Sign In</h4>
        <p class="auth-subtitle">Internal use only - All access is monitored</p>
        
        <div id="loginStep1">
            <div class="login-status d-none" id="loginStatus"></div>
            
            <form id="loginForm" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" placeholder="admin@quicksms.com" required autocomplete="email">
                    <div class="invalid-feedback" id="emailError">Please enter a valid email address</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" placeholder="Enter password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" id="passwordError">Please enter your password</div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label small" for="rememberMe">Remember me</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-signin" id="loginBtn">
                    <i class="fas fa-shield-alt me-2"></i>Sign In
                </button>
            </form>
            
            <div class="security-notice">
                <i class="fas fa-lock me-2"></i>
                <strong>Security Notice:</strong> This is a restricted administrative interface. 
                All access attempts are logged and monitored. Unauthorized access is prohibited.
            </div>
        </div>
        
        <div id="loginStep2" class="d-none">
            <div class="mfa-step">
                <div class="mfa-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h5 class="mfa-title">Two-Factor Authentication</h5>
                <p class="mfa-subtitle" id="mfaSubtitle">Choose your verification method</p>
                
                <div id="mfaMethodChoice" class="mb-3">
                    <div class="mfa-method-options">
                        <button type="button" class="mfa-method-btn" id="chooseAuthenticator">
                            <i class="fas fa-mobile-alt"></i>
                            <span class="method-title">Authenticator App</span>
                            <span class="method-desc">Use Google Authenticator or similar</span>
                        </button>
                        <button type="button" class="mfa-method-btn" id="chooseSmsRcs">
                            <i class="fas fa-comment-dots"></i>
                            <span class="method-title">SMS / RCS</span>
                            <span class="method-desc">Receive code via text message</span>
                        </button>
                    </div>
                </div>
                
                <div id="mfaCodeEntry" class="d-none">
                    <p class="mfa-code-instruction" id="mfaCodeInstruction">Enter the 6-digit code from your authenticator app</p>
                    
                    <div class="otp-input-group">
                        <input type="text" class="otp-input" maxlength="1" data-index="0" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" data-index="4" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" data-index="5" inputmode="numeric">
                    </div>
                    
                    <div id="smsResendSection" class="d-none text-center mb-3">
                        <span class="text-muted small">Didn't receive the code? </span>
                        <a href="#" class="resend-link" id="resendCode">Resend</a>
                    </div>
                    
                    <div class="login-status d-none" id="mfaStatus"></div>
                    
                    <button type="button" class="btn btn-signin" id="verifyMfaBtn">
                        <i class="fas fa-check-circle me-2"></i>Verify & Sign In
                    </button>
                    
                    <div class="mt-3">
                        <a href="#" class="back-link" id="backToMethodChoice">
                            <i class="fas fa-arrow-left me-1"></i> Choose different method
                        </a>
                    </div>
                </div>
                
                <div class="mt-3" id="backToLoginSection">
                    <a href="#" class="back-link" id="backToLogin">
                        <i class="fas fa-arrow-left me-1"></i> Back to login
                    </a>
                </div>
            </div>
        </div>
        
        <p class="copyright-text">&copy; {{ date('Y') }} QuickSMS Ltd. All rights reserved.</p>
    </div>
</div>

<script>
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    var currentMfaMethod = null;
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    document.getElementById('togglePassword').addEventListener('click', function() {
        var passwordInput = document.getElementById('password');
        var toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    });

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var loginBtn = document.getElementById('loginBtn');
        var loginStatus = document.getElementById('loginStatus');

        document.getElementById('email').classList.remove('is-invalid');
        document.getElementById('password').classList.remove('is-invalid');
        loginStatus.classList.add('d-none');

        var isValid = true;
        if (!email || !email.includes('@')) {
            document.getElementById('email').classList.add('is-invalid');
            isValid = false;
        }
        if (!password) {
            document.getElementById('password').classList.add('is-invalid');
            isValid = false;
        }
        if (!isValid) return;

        loginBtn.disabled = true;
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Authenticating...';

        fetch('{{ route("admin.login.submit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email, password: password })
        })
        .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, status: res.status, data: data }; }); })
        .then(function(result) {
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="fas fa-shield-alt me-2"></i>Sign In';

            if (!result.ok) {
                var msg = result.data.error || result.data.errors?.email?.[0] || 'Invalid email or password.';
                loginStatus.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>' + escapeHtml(msg) + ' All login attempts are logged.</div>';
                loginStatus.classList.remove('d-none');
                return;
            }

            var data = result.data;

            if (data.mfa_required) {
                currentMfaMethod = data.mfa_method;

                if (data.mfa_setup_required) {
                    window.location.href = data.redirect || '/admin/mfa/setup';
                    return;
                }

                document.getElementById('loginStep1').classList.add('d-none');
                document.getElementById('loginStep2').classList.remove('d-none');

                if (currentMfaMethod === 'sms') {
                    selectMfaMethod('sms');
                } else if (currentMfaMethod === 'both') {
                    resetMfaStep();
                } else {
                    selectMfaMethod('authenticator');
                }
            } else if (data.redirect) {
                loginStatus.innerHTML = '<div class="alert alert-success mb-0"><i class="fas fa-check-circle me-2"></i>Login successful! Redirecting...</div>';
                loginStatus.classList.remove('d-none');
                setTimeout(function() { window.location.href = data.redirect; }, 500);
            }
        })
        .catch(function(err) {
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="fas fa-shield-alt me-2"></i>Sign In';
            loginStatus.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>Connection error. Please try again.</div>';
            loginStatus.classList.remove('d-none');
        });
    });

    var otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach(function(input, index) {
        input.addEventListener('input', function(e) {
            var value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;

            if (value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }

            var fullCode = Array.from(otpInputs).map(function(i) { return i.value; }).join('');
            if (fullCode.length === 6) {
                document.getElementById('verifyMfaBtn').click();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            var pastedData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            for (var i = 0; i < pastedData.length && i < otpInputs.length; i++) {
                otpInputs[i].value = pastedData[i];
            }
            if (pastedData.length === 6) {
                document.getElementById('verifyMfaBtn').click();
            }
        });
    });

    document.getElementById('verifyMfaBtn').addEventListener('click', function() {
        var code = Array.from(otpInputs).map(function(i) { return i.value; }).join('');
        var mfaStatus = document.getElementById('mfaStatus');
        var verifyBtn = document.getElementById('verifyMfaBtn');

        if (code.length !== 6) {
            mfaStatus.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>Please enter all 6 digits</div>';
            mfaStatus.classList.remove('d-none');
            return;
        }

        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';

        var verifyUrl = currentMfaMethod === 'sms'
            ? '{{ route("admin.mfa.sms.verify") }}'
            : '{{ route("admin.mfa.verify.submit") }}';

        fetch(verifyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ code: code })
        })
        .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
        .then(function(result) {
            if (result.ok && result.data.redirect) {
                mfaStatus.innerHTML = '<div class="alert alert-success mb-0"><i class="fas fa-check-circle me-2"></i>Verification successful! Redirecting...</div>';
                mfaStatus.classList.remove('d-none');
                setTimeout(function() { window.location.href = result.data.redirect; }, 500);
            } else {
                var msg = result.data.error || 'Invalid verification code';
                mfaStatus.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>' + escapeHtml(msg) + '</div>';
                mfaStatus.classList.remove('d-none');
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Verify';
                otpInputs.forEach(function(input) { input.value = ''; });
                otpInputs[0].focus();
            }
        })
        .catch(function() {
            mfaStatus.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>Connection error. Please try again.</div>';
            mfaStatus.classList.remove('d-none');
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Verify';
        });
    });

    document.getElementById('backToLogin').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('loginStep2').classList.add('d-none');
        document.getElementById('loginStep1').classList.remove('d-none');
        resetMfaStep();
    });

    document.getElementById('chooseAuthenticator').addEventListener('click', function() {
        selectMfaMethod('authenticator');
    });

    document.getElementById('chooseSmsRcs').addEventListener('click', function() {
        selectMfaMethod('sms');
    });

    document.getElementById('backToMethodChoice').addEventListener('click', function(e) {
        e.preventDefault();
        resetMfaStep();
    });

    document.getElementById('resendCode').addEventListener('click', function(e) {
        e.preventDefault();
        var link = this;
        link.textContent = 'Sending...';
        link.style.pointerEvents = 'none';

        fetch('{{ route("admin.mfa.sms.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            link.textContent = 'Resend';
            link.style.pointerEvents = 'auto';

            if (data.success) {
                document.getElementById('mfaStatus').innerHTML = '<div class="alert alert-info mb-0"><i class="fas fa-check me-2"></i>New code sent to ' + escapeHtml(data.masked_phone || 'your phone') + '</div>';
            } else {
                document.getElementById('mfaStatus').innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>' + escapeHtml(data.error || 'Failed to send code') + '</div>';
            }
            document.getElementById('mfaStatus').classList.remove('d-none');
            otpInputs.forEach(function(input) { input.value = ''; });
            otpInputs[0].focus();
        })
        .catch(function() {
            link.textContent = 'Resend';
            link.style.pointerEvents = 'auto';
        });
    });

    function resetMfaStep() {
        document.getElementById('mfaMethodChoice').classList.remove('d-none');
        document.getElementById('mfaCodeEntry').classList.add('d-none');
        document.getElementById('mfaSubtitle').textContent = 'Choose your verification method';
        document.getElementById('smsResendSection').classList.add('d-none');
        document.getElementById('mfaStatus').classList.add('d-none');
        document.getElementById('backToLoginSection').classList.remove('d-none');
        otpInputs.forEach(function(input) { input.value = ''; });
    }

    function selectMfaMethod(method) {
        currentMfaMethod = method;
        document.getElementById('mfaMethodChoice').classList.add('d-none');
        document.getElementById('backToLoginSection').classList.add('d-none');

        if (method === 'authenticator') {
            document.getElementById('mfaSubtitle').textContent = 'Authenticator App';
            document.getElementById('mfaCodeInstruction').textContent = 'Enter the 6-digit code from your authenticator app';
            document.getElementById('smsResendSection').classList.add('d-none');
            document.getElementById('mfaCodeEntry').classList.remove('d-none');
            otpInputs[0].focus();
        } else {
            document.getElementById('mfaSubtitle').textContent = 'SMS / RCS Verification';
            document.getElementById('mfaCodeInstruction').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending code to your registered phone...';
            document.getElementById('mfaCodeEntry').classList.remove('d-none');

            fetch('{{ route("admin.mfa.sms.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    document.getElementById('mfaCodeInstruction').textContent = 'Enter the 6-digit code sent to ' + (data.masked_phone || 'your phone');
                } else {
                    document.getElementById('mfaCodeInstruction').textContent = data.error || 'Failed to send SMS code';
                }
                document.getElementById('smsResendSection').classList.remove('d-none');
                otpInputs[0].focus();
            })
            .catch(function() {
                document.getElementById('mfaCodeInstruction').textContent = 'Failed to send code. Please try again.';
                document.getElementById('smsResendSection').classList.remove('d-none');
            });
        }
    }
});
</script>
@endsection

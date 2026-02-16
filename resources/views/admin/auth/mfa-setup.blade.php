@extends('layouts.fullwidth')
@section('title', 'Setup MFA - QuickSMS Admin')
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

.mfa-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.mfa-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 0 40px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 520px;
    padding: 2.5rem;
}

.mfa-logo {
    text-align: center;
    margin-bottom: 1.5rem;
}

.mfa-logo img {
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

.mfa-title {
    text-align: center;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
    font-size: 1.25rem;
}

.mfa-subtitle {
    text-align: center;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 2rem;
}

.setup-step {
    margin-bottom: 1.75rem;
}

.step-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.step-number {
    background: var(--admin-primary);
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.step-label {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.step-content {
    margin-left: 2.75rem;
}

.step-content p {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0;
}

.qr-section {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.qr-placeholder {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 0.75rem;
    padding: 1.25rem;
    text-align: center;
    flex-shrink: 0;
    width: 140px;
}

.qr-placeholder i {
    color: #adb5bd;
}

.qr-placeholder p {
    font-size: 0.75rem;
    color: #adb5bd;
    margin-top: 0.5rem;
    margin-bottom: 0;
}

.secret-section {
    flex: 1;
}

.secret-section .label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.secret-code {
    font-family: 'Courier New', monospace;
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 0.625rem 0.875rem;
    border-radius: 0.5rem;
    word-break: break-all;
    color: #333;
}

.btn-copy {
    background: none;
    border: 1px solid #dee2e6;
    color: var(--admin-primary);
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    margin-top: 0.5rem;
    transition: all 0.2s ease;
}

.btn-copy:hover {
    background: var(--admin-primary);
    color: #fff;
    border-color: var(--admin-primary);
}

.otp-input-group {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.otp-input {
    width: 48px;
    height: 56px;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    border: 2px solid #e0e6ed;
    border-radius: 0.5rem;
    outline: none;
    transition: border-color 0.2s;
}

.otp-input:focus {
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 0.15rem rgba(30, 58, 95, 0.15);
}

.btn-verify {
    background: var(--admin-primary);
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 1rem;
    color: #fff;
    width: 100%;
    margin-top: 1.25rem;
    transition: all 0.2s ease;
}

.btn-verify:hover {
    background: var(--admin-primary-dark);
    color: #fff;
}

.btn-verify:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.info-notice {
    background: rgba(30, 58, 95, 0.06);
    border: 1px solid rgba(30, 58, 95, 0.15);
    border-radius: 0.5rem;
    padding: 0.875rem 1rem;
    margin-top: 1.5rem;
    font-size: 0.8rem;
    color: #495057;
}

.info-notice i {
    color: var(--admin-primary);
}

.dev-notice {
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: 0.5rem;
    padding: 0.875rem 1rem;
    margin-top: 1rem;
    font-size: 0.8rem;
    color: #6d4c00;
}

.btn-skip {
    background: none;
    border: 1px solid #dee2e6;
    padding: 0.625rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: #6c757d;
    width: 100%;
    margin-top: 0.75rem;
    transition: all 0.2s ease;
}

.btn-skip:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    color: #495057;
}

.otp-hint {
    text-align: center;
    font-size: 0.8rem;
    color: #adb5bd;
    margin-top: 0.5rem;
}

.copyright-text {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.8rem;
    color: #6c757d;
}

.divider {
    height: 1px;
    background: #e9ecef;
    margin: 1.5rem 0;
}
</style>

<div class="mfa-wrapper">
    <div class="mfa-card">
        <div class="mfa-logo">
            <img src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 50px;">
        </div>

        <div class="text-center mb-3">
            <span class="admin-badge">Security Setup</span>
        </div>

        <h4 class="mfa-title">Setup Two-Factor Authentication</h4>
        <p class="mfa-subtitle">MFA is mandatory for all admin accounts</p>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 0.5rem; font-size: 0.9rem;">
                <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="setup-step">
            <div class="step-header">
                <span class="step-number">1</span>
                <span class="step-label">Install an authenticator app</span>
            </div>
            <div class="step-content">
                <p>Download Google Authenticator, Microsoft Authenticator, or Authy on your mobile device.</p>
            </div>
        </div>

        <div class="setup-step">
            <div class="step-header">
                <span class="step-number">2</span>
                <span class="step-label">Scan the QR code or enter the secret key</span>
            </div>
            <div class="step-content">
                <div class="qr-section">
                    <div class="qr-placeholder">
                        <i class="fas fa-qrcode fa-3x"></i>
                        <p>Scan with your authenticator app</p>
                    </div>
                    <div class="secret-section">
                        <div class="label">Or enter this secret key manually:</div>
                        <div class="secret-code" id="secretCode">{{ $secret }}</div>
                        <button type="button" class="btn-copy" id="copyBtn" onclick="copySecret()">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="setup-step" style="margin-bottom: 0;">
            <div class="step-header">
                <span class="step-number">3</span>
                <span class="step-label">Enter the verification code</span>
            </div>
            <div class="step-content">
                <form action="{{ route('admin.mfa.setup.complete') }}" method="POST" id="mfaForm">
                    @csrf
                    <input type="hidden" name="code" id="hiddenCode">
                    <div class="otp-input-group">
                        <input type="text" class="otp-input" maxlength="1" data-index="0" inputmode="numeric" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="4" inputmode="numeric" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="5" inputmode="numeric" autocomplete="off">
                    </div>
                    <div class="otp-hint">Enter the 6-digit code from your authenticator app</div>
                    <button type="submit" class="btn-verify" id="verifyBtn">
                        <i class="fas fa-check-circle me-2"></i>Verify and Enable MFA
                    </button>
                </form>
            </div>
        </div>

        <div class="info-notice">
            <i class="fas fa-lock me-2"></i>
            <strong>Important:</strong> Save your secret key in a secure location. You will need it to recover your account if you lose access to your authenticator app.
        </div>

        @if(config('app.env') !== 'production')
        <div class="divider"></div>
        <div class="dev-notice">
            <i class="fas fa-flask me-2"></i>
            <strong>Development Mode</strong> — MFA can be skipped for testing purposes only.
        </div>
        <form action="{{ route('admin.mfa.setup.skip') }}" method="POST">
            @csrf
            <button type="submit" class="btn-skip">
                <i class="fas fa-forward me-2"></i>Skip MFA Setup (Development Only)
            </button>
        </form>
        @endif

        <p class="copyright-text">&copy; {{ date('Y') }} QuickSMS Ltd. All rights reserved.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var otpInputs = document.querySelectorAll('.otp-input');

    otpInputs.forEach(function(input, index) {
        input.addEventListener('input', function(e) {
            var value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;
            if (value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
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
        });
    });

    document.getElementById('mfaForm').addEventListener('submit', function(e) {
        var code = Array.from(otpInputs).map(function(i) { return i.value; }).join('');
        if (code.length !== 6) {
            e.preventDefault();
            otpInputs[0].focus();
            return;
        }
        document.getElementById('hiddenCode').value = code;
        var btn = document.getElementById('verifyBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
    });
});

function copySecret() {
    var secretCode = document.getElementById('secretCode').textContent;
    var btn = document.getElementById('copyBtn');
    navigator.clipboard.writeText(secretCode).then(function() {
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        setTimeout(function() {
            btn.innerHTML = '<i class="fas fa-copy me-1"></i>Copy';
        }, 2000);
    });
}
</script>
@endsection

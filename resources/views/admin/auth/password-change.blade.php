@extends('layouts.fullwidth')
@section('title', 'Change Password')
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

.btn-submit {
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

.btn-submit:hover {
    background: var(--admin-primary-dark);
    color: #fff;
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.password-requirements {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.5rem;
}

.password-requirements li {
    margin-bottom: 0.2rem;
}

.password-requirements li.valid {
    color: #198754;
}

.password-requirements li.invalid {
    color: #dc3545;
}

.strength-bar {
    height: 4px;
    border-radius: 2px;
    background: #e9ecef;
    margin-top: 0.5rem;
}

.strength-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease, background-color 0.3s ease;
}
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <span class="admin-badge">ADMIN CONTROL PLANE</span>
        </div>
        <h4 class="auth-title">Change Your Password</h4>
        <p class="auth-subtitle">Your password must be changed before continuing</p>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.change.submit') }}" id="passwordChangeForm">
            @csrf

            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="current_password" name="current_password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="12" autocomplete="new-password">
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill" style="width: 0%"></div>
                </div>
                <ul class="password-requirements list-unstyled mt-2" id="requirements">
                    <li id="req-length"><i class="fas fa-circle fa-xs me-1"></i> At least 12 characters</li>
                    <li id="req-upper"><i class="fas fa-circle fa-xs me-1"></i> One uppercase letter</li>
                    <li id="req-lower"><i class="fas fa-circle fa-xs me-1"></i> One lowercase letter</li>
                    <li id="req-number"><i class="fas fa-circle fa-xs me-1"></i> One number</li>
                    <li id="req-special"><i class="fas fa-circle fa-xs me-1"></i> One special character</li>
                </ul>
            </div>

            <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="12" autocomplete="new-password">
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password_confirmation', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="text-danger d-none" id="matchError">Passwords do not match</small>
            </div>

            <button type="submit" class="btn btn-submit" id="submitBtn" disabled>
                <i class="fas fa-lock me-2"></i>Change Password
            </button>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const newPass = document.getElementById('new_password');
    const confirmPass = document.getElementById('new_password_confirmation');
    const submitBtn = document.getElementById('submitBtn');
    const strengthFill = document.getElementById('strengthFill');
    const matchError = document.getElementById('matchError');

    const requirements = {
        length: { el: document.getElementById('req-length'), test: v => v.length >= 12 },
        upper: { el: document.getElementById('req-upper'), test: v => /[A-Z]/.test(v) },
        lower: { el: document.getElementById('req-lower'), test: v => /[a-z]/.test(v) },
        number: { el: document.getElementById('req-number'), test: v => /[0-9]/.test(v) },
        special: { el: document.getElementById('req-special'), test: v => /[^A-Za-z0-9]/.test(v) }
    };

    function validate() {
        const val = newPass.value;
        let met = 0;
        const total = Object.keys(requirements).length;

        for (const [key, req] of Object.entries(requirements)) {
            const passed = req.test(val);
            req.el.className = passed ? 'valid' : 'invalid';
            if (passed) met++;
        }

        const pct = (met / total) * 100;
        strengthFill.style.width = pct + '%';
        if (pct <= 20) strengthFill.style.backgroundColor = '#dc3545';
        else if (pct <= 60) strengthFill.style.backgroundColor = '#ffc107';
        else if (pct < 100) strengthFill.style.backgroundColor = '#0dcaf0';
        else strengthFill.style.backgroundColor = '#198754';

        const matching = confirmPass.value.length > 0 && newPass.value === confirmPass.value;
        matchError.classList.toggle('d-none', matching || confirmPass.value.length === 0);

        submitBtn.disabled = !(met === total && matching && document.getElementById('current_password').value.length > 0);
    }

    newPass.addEventListener('input', validate);
    confirmPass.addEventListener('input', validate);
    document.getElementById('current_password').addEventListener('input', validate);
});
</script>
@endsection

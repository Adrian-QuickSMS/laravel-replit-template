@extends('layouts.quicksms')

@section('title', 'My Profile')

@push('styles')
<style>
.profile-avatar-large {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background-color: #886cc0;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
}
.form-label .text-muted {
    font-weight: 400;
}
.is-invalid + .invalid-feedback {
    display: block;
}
.email-pending-banner {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    display: none;
}
.email-pending-banner.show {
    display: flex;
}
.email-pending-banner i {
    color: #856404;
    margin-right: 0.5rem;
}
.email-pending-banner span {
    color: #856404;
    font-size: 0.875rem;
}
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
.toast-success {
    background: #fff;
    border-left: 4px solid #1cbb8c;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 1rem 1.25rem;
    border-radius: 0.375rem;
    display: none;
    align-items: center;
    gap: 0.75rem;
}
.toast-success.show {
    display: flex;
    animation: slideIn 0.3s ease;
}
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.toast-success i {
    color: #1cbb8c;
    font-size: 1.25rem;
}
.toast-success .toast-message {
    font-size: 0.875rem;
    color: #333;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">My Profile</li>
        </ol>
    </div>
    
    @php
        $username = 'sarah.mitchell';
        $firstName = 'Sarah';
        $lastName = 'Mitchell';
        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        $email = 'sarah.mitchell@example.com';
        $emailVerified = true;
        $mobile = '+447700900123';
        $role = 'Account Administrator';
        $senderCapabilityLevel = 'Full Access';
        $subAccount = 'Marketing Department';
        $campaignApprovalResponsibility = 'Can approve campaigns';
        $accountName = 'Acme Corporation Ltd';
        $lastLogin = '26 Jan 2026, 09:15';
        $accountCreated = '15 Mar 2024';
        $lastPasswordChange = '10 Jan 2026';
        $twoFactorEnabled = true;
        $mfaMethod = 'sms';
        $loginCount = 247;
    @endphp
    
    <div class="toast-container">
        <div class="toast-success" id="successToast">
            <i class="fas fa-check-circle"></i>
            <span class="toast-message">Profile updated successfully</span>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Profile Information</h4>
                </div>
                <div class="card-body">
                    <div class="email-pending-banner" id="emailPendingBanner">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Email change pending verification. Please check your inbox to confirm.</span>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="profile-avatar-large me-3" id="profileAvatar">{{ $initials }}</div>
                        <div>
                            <h5 class="mb-1" id="displayName">{{ $firstName }} {{ $lastName }}</h5>
                            <span class="text-muted" id="displayEmail">{{ $email }}</span>
                            <span class="badge badge-warning light ms-2" id="emailUnverifiedBadge" style="display: none;">Unverified</span>
                        </div>
                    </div>
                    
                    <form id="profileForm">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="{{ $username }}" readonly disabled style="background-color: #f8f9fa;">
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="firstName" value="{{ $firstName }}" data-original="{{ $firstName }}" required>
                                <div class="invalid-feedback">First name is required</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="lastName" value="{{ $lastName }}" data-original="{{ $lastName }}" required>
                                <div class="invalid-feedback">Last name is required</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="emailAddress" name="email" value="{{ $email }}" data-original="{{ $email }}" required>
                            <div class="invalid-feedback" id="emailError">Please enter a valid email address</div>
                            <small class="text-muted">Changing your email will require verification</small>
                        </div>
                        
                        @php
                            $mfaRequiresMobile = $twoFactorEnabled && in_array($mfaMethod, ['sms', 'rcs']);
                        @endphp
                        <div class="mb-4">
                            <label class="form-label">Mobile Number @if($mfaRequiresMobile)<span class="text-danger">*</span>@endif</label>
                            <input type="tel" class="form-control" id="mobileNumber" name="mobile" value="{{ $mobile }}" data-original="{{ $mobile }}" placeholder="+44XXXXXXXXXX" {{ $mfaRequiresMobile ? 'required' : '' }}>
                            <div class="invalid-feedback" id="mobileError">Please enter a valid mobile number in E.164 format (e.g., +447700900123)</div>
                            <small class="text-muted">E.164 format required (e.g., +447700900123)
                                @if($mfaRequiresMobile) - Required for SMS/RCS MFA
                                @endif
                            </small>
                        </div>
                        
                        <div>
                            <button type="submit" class="btn btn-primary" id="saveBtn" disabled>Save Changes</button>
                            <button type="button" class="btn btn-light ms-2" id="cancelBtn" disabled>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Security</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-1">Password</h6>
                                <span class="text-muted" style="font-size: 0.85rem;">Last changed: {{ $lastPasswordChange }}</span>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Two-Factor Authentication</h6>
                            <span class="text-muted" style="font-size: 0.85rem;">Add an extra layer of security to your account</span>
                        </div>
                        <div>
                            @if($twoFactorEnabled)
                                <span class="badge badge-success light">Enabled</span>
                            @else
                                <span class="badge badge-warning light">Disabled</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Account & Permissions</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Role</label>
                        <span class="badge badge-primary light">{{ $role }}</span>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Sender Capability Level</label>
                        <span>{{ $senderCapabilityLevel }}</span>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Sub-Account Membership</label>
                        <span>{{ $subAccount }}</span>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Campaign Approval Responsibility</label>
                        <span class="badge badge-success light">{{ $campaignApprovalResponsibility }}</span>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">If you need changes to your role or permissions, contact your account administrator.</p>
                        <a href="{{ url('/support/knowledge-base') }}" class="text-primary" style="font-size: 0.85rem;">
                            <i class="fas fa-external-link-alt me-1"></i>Learn more about roles & permissions
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Audit & Metadata</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Account Created</label>
                            <span>{{ $accountCreated }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Last Login</label>
                            <span>{{ $lastLogin }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Total Logins</label>
                            <span>{{ $loginCount }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">User ID</label>
                            <span class="text-muted" style="font-family: monospace; font-size: 0.85rem;">usr_8f4a2b1c</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="currentPasswordError">Please enter your current password</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="newPasswordError">Password does not meet requirements</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="confirmPasswordError">Passwords do not match</div>
                    </div>
                    
                    <!-- Password Rules (collapsed by default) -->
                    <div class="card bg-light border-0 mb-0" id="passwordRulesCard">
                        <div class="card-header bg-transparent border-0 p-2" style="cursor: pointer;" id="passwordRulesToggle">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 0.85rem;">
                                    <i class="fas fa-info-circle me-1"></i>Password requirements
                                </span>
                                <i class="fas fa-chevron-down text-muted" id="passwordRulesIcon" style="font-size: 0.75rem; transition: transform 0.2s;"></i>
                            </div>
                        </div>
                        <div class="collapse" id="passwordRulesCollapse">
                            <div class="card-body pt-0 px-2 pb-2">
                                <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                    <li class="mb-1" id="rule-length">
                                        <i class="fas fa-circle me-2" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        Minimum 12 characters
                                    </li>
                                    <li class="mb-1" id="rule-upper">
                                        <i class="fas fa-circle me-2" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        At least one uppercase letter
                                    </li>
                                    <li class="mb-1" id="rule-lower">
                                        <i class="fas fa-circle me-2" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        At least one lowercase letter
                                    </li>
                                    <li class="mb-1" id="rule-number">
                                        <i class="fas fa-circle me-2" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        At least one number
                                    </li>
                                    <li class="mb-1" id="rule-special">
                                        <i class="fas fa-circle me-2" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        At least one special character (!@#$%^&*...)
                                    </li>
                                    <li id="rule-reuse">
                                        <i class="fas fa-circle me-2" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        Cannot reuse last 5 passwords
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="changePasswordBtn">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[MyProfile] Page loaded');
    
    var form = document.getElementById('profileForm');
    var saveBtn = document.getElementById('saveBtn');
    var cancelBtn = document.getElementById('cancelBtn');
    var successToast = document.getElementById('successToast');
    var emailPendingBanner = document.getElementById('emailPendingBanner');
    var emailUnverifiedBadge = document.getElementById('emailUnverifiedBadge');
    
    var fields = ['firstName', 'lastName', 'emailAddress', 'mobileNumber'];
    var originalValues = {};
    
    fields.forEach(function(field) {
        var input = document.getElementById(field);
        if (input) {
            originalValues[field] = input.dataset.original || input.value;
        }
    });
    
    function checkForChanges() {
        var hasChanges = false;
        fields.forEach(function(field) {
            var input = document.getElementById(field);
            if (input && input.value !== originalValues[field]) {
                hasChanges = true;
            }
        });
        
        saveBtn.disabled = !hasChanges;
        cancelBtn.disabled = !hasChanges;
    }
    
    function validateE164(phone) {
        var e164Regex = /^\+[1-9]\d{6,14}$/;
        return e164Regex.test(phone.replace(/\s/g, ''));
    }
    
    function validateEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function validateForm() {
        var isValid = true;
        
        var firstName = document.getElementById('firstName');
        if (!firstName.value.trim()) {
            firstName.classList.add('is-invalid');
            isValid = false;
        } else {
            firstName.classList.remove('is-invalid');
        }
        
        var lastName = document.getElementById('lastName');
        if (!lastName.value.trim()) {
            lastName.classList.add('is-invalid');
            isValid = false;
        } else {
            lastName.classList.remove('is-invalid');
        }
        
        var email = document.getElementById('emailAddress');
        if (!email.value.trim() || !validateEmail(email.value)) {
            email.classList.add('is-invalid');
            document.getElementById('emailError').textContent = !email.value.trim() 
                ? 'Email address is required' 
                : 'Please enter a valid email address';
            isValid = false;
        } else {
            email.classList.remove('is-invalid');
        }
        
        var mobile = document.getElementById('mobileNumber');
        if (mobile.required && !mobile.value.trim()) {
            mobile.classList.add('is-invalid');
            document.getElementById('mobileError').textContent = 'Mobile number is required for SMS/RCS MFA';
            isValid = false;
        } else if (mobile.value.trim() && !validateE164(mobile.value)) {
            mobile.classList.add('is-invalid');
            document.getElementById('mobileError').textContent = 'Please enter a valid mobile number in E.164 format (e.g., +447700900123)';
            isValid = false;
        } else {
            mobile.classList.remove('is-invalid');
        }
        
        return isValid;
    }
    
    fields.forEach(function(field) {
        var input = document.getElementById(field);
        if (input) {
            input.addEventListener('input', function() {
                checkForChanges();
                this.classList.remove('is-invalid');
            });
        }
    });
    
    cancelBtn.addEventListener('click', function() {
        fields.forEach(function(field) {
            var input = document.getElementById(field);
            if (input) {
                input.value = originalValues[field];
                input.classList.remove('is-invalid');
            }
        });
        checkForChanges();
    });
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        var emailInput = document.getElementById('emailAddress');
        var emailChanged = emailInput.value !== originalValues['emailAddress'];
        
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        
        setTimeout(function() {
            if (emailChanged) {
                emailPendingBanner.classList.add('show');
                emailUnverifiedBadge.style.display = 'inline-block';
                document.getElementById('displayEmail').textContent = emailInput.value;
            }
            
            var firstName = document.getElementById('firstName').value;
            var lastName = document.getElementById('lastName').value;
            document.getElementById('displayName').textContent = firstName + ' ' + lastName;
            document.getElementById('profileAvatar').textContent = firstName.charAt(0).toUpperCase() + lastName.charAt(0).toUpperCase();
            
            fields.forEach(function(field) {
                var input = document.getElementById(field);
                if (input) {
                    originalValues[field] = input.value;
                }
            });
            
            saveBtn.innerHTML = 'Save Changes';
            saveBtn.disabled = true;
            cancelBtn.disabled = true;
            
            successToast.classList.add('show');
            setTimeout(function() {
                successToast.classList.remove('show');
            }, 3000);
            
        }, 800);
    });
    
    // Change Password Modal functionality
    var changePasswordModal = document.getElementById('changePasswordModal');
    var changePasswordForm = document.getElementById('changePasswordForm');
    var currentPasswordInput = document.getElementById('currentPassword');
    var newPasswordInput = document.getElementById('newPassword');
    var confirmPasswordInput = document.getElementById('confirmPassword');
    var passwordRulesToggle = document.getElementById('passwordRulesToggle');
    var passwordRulesCollapse = document.getElementById('passwordRulesCollapse');
    var passwordRulesIcon = document.getElementById('passwordRulesIcon');
    var changePasswordBtn = document.getElementById('changePasswordBtn');
    
    // Toggle password visibility
    function setupPasswordToggle(toggleBtnId, inputId) {
        var btn = document.getElementById(toggleBtnId);
        var input = document.getElementById(inputId);
        if (btn && input) {
            btn.addEventListener('click', function() {
                var type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                var icon = btn.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    }
    
    setupPasswordToggle('toggleCurrentPassword', 'currentPassword');
    setupPasswordToggle('toggleNewPassword', 'newPassword');
    setupPasswordToggle('toggleConfirmPassword', 'confirmPassword');
    
    // Toggle password rules collapse
    var rulesExpanded = false;
    passwordRulesToggle.addEventListener('click', function() {
        rulesExpanded = !rulesExpanded;
        if (rulesExpanded) {
            passwordRulesCollapse.classList.add('show');
            passwordRulesIcon.style.transform = 'rotate(180deg)';
        } else {
            passwordRulesCollapse.classList.remove('show');
            passwordRulesIcon.style.transform = 'rotate(0deg)';
        }
    });
    
    // Password validation rules
    function validatePasswordRules(password) {
        var rules = {
            length: password.length >= 12,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };
        
        // Update rule indicators
        Object.keys(rules).forEach(function(rule) {
            var ruleEl = document.getElementById('rule-' + rule);
            if (ruleEl) {
                var icon = ruleEl.querySelector('i');
                if (rules[rule]) {
                    ruleEl.classList.add('text-success');
                    ruleEl.classList.remove('text-danger');
                    icon.classList.remove('fa-circle');
                    icon.classList.add('fa-check-circle');
                } else {
                    ruleEl.classList.remove('text-success');
                    if (password.length > 0) {
                        ruleEl.classList.add('text-danger');
                    } else {
                        ruleEl.classList.remove('text-danger');
                    }
                    icon.classList.add('fa-circle');
                    icon.classList.remove('fa-check-circle');
                }
            }
        });
        
        return rules.length && rules.upper && rules.lower && rules.number && rules.special;
    }
    
    // Real-time password validation
    newPasswordInput.addEventListener('input', function() {
        validatePasswordRules(this.value);
    });
    
    // Reset modal on close
    changePasswordModal.addEventListener('hidden.bs.modal', function() {
        changePasswordForm.reset();
        currentPasswordInput.classList.remove('is-invalid');
        newPasswordInput.classList.remove('is-invalid');
        confirmPasswordInput.classList.remove('is-invalid');
        
        // Reset password rule indicators
        ['length', 'upper', 'lower', 'number', 'special'].forEach(function(rule) {
            var ruleEl = document.getElementById('rule-' + rule);
            if (ruleEl) {
                ruleEl.classList.remove('text-success', 'text-danger');
                var icon = ruleEl.querySelector('i');
                icon.classList.add('fa-circle');
                icon.classList.remove('fa-check-circle');
            }
        });
        
        // Collapse rules
        rulesExpanded = false;
        passwordRulesCollapse.classList.remove('show');
        passwordRulesIcon.style.transform = 'rotate(0deg)';
        
        // Reset button
        changePasswordBtn.innerHTML = 'Change Password';
        changePasswordBtn.disabled = false;
    });
    
    // Form submission
    changePasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        var isValid = true;
        var expandRules = false;
        
        // Validate current password
        if (!currentPasswordInput.value) {
            currentPasswordInput.classList.add('is-invalid');
            isValid = false;
        } else {
            currentPasswordInput.classList.remove('is-invalid');
        }
        
        // Validate new password
        if (!validatePasswordRules(newPasswordInput.value)) {
            newPasswordInput.classList.add('is-invalid');
            isValid = false;
            expandRules = true;
        } else {
            newPasswordInput.classList.remove('is-invalid');
        }
        
        // Validate confirm password
        if (confirmPasswordInput.value !== newPasswordInput.value || !confirmPasswordInput.value) {
            confirmPasswordInput.classList.add('is-invalid');
            if (confirmPasswordInput.value && confirmPasswordInput.value !== newPasswordInput.value) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
            } else {
                document.getElementById('confirmPasswordError').textContent = 'Please confirm your new password';
            }
            isValid = false;
        } else {
            confirmPasswordInput.classList.remove('is-invalid');
        }
        
        // Auto-expand rules if validation failed
        if (expandRules && !rulesExpanded) {
            rulesExpanded = true;
            passwordRulesCollapse.classList.add('show');
            passwordRulesIcon.style.transform = 'rotate(180deg)';
        }
        
        if (!isValid) return;
        
        // Simulate API call
        changePasswordBtn.disabled = true;
        changePasswordBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Changing...';
        
        setTimeout(function() {
            // Close modal
            var modal = bootstrap.Modal.getInstance(changePasswordModal);
            modal.hide();
            
            // Show success toast
            successToast.classList.add('show');
            setTimeout(function() {
                successToast.classList.remove('show');
            }, 3000);
            
            console.log('[MyProfile] Password changed successfully');
        }, 1000);
    });
});
</script>
@endpush

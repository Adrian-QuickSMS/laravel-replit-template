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
        $mfaMethods = ['sms'];
        $mfaEnforced = true;
        $mfaPhone = '+447700900123';
        $backupCodesRemaining = 6;
        $loginCount = 247;
        $lastLoginIp = '192.168.1.45';
        $mfaLastVerified = '26 Jan 2026, 08:30';
        
        $securityEvents = [
            ['date' => '26 Jan 2026, 09:15', 'event' => 'Successful login', 'ip' => '192.168.1.45', 'icon' => 'fa-sign-in-alt', 'color' => 'success'],
            ['date' => '25 Jan 2026, 14:22', 'event' => 'Password changed', 'ip' => '192.168.1.45', 'icon' => 'fa-key', 'color' => 'info'],
            ['date' => '25 Jan 2026, 10:05', 'event' => 'MFA verified', 'ip' => '192.168.1.45', 'icon' => 'fa-shield-alt', 'color' => 'primary'],
            ['date' => '24 Jan 2026, 16:30', 'event' => 'Successful login', 'ip' => '10.0.0.12', 'icon' => 'fa-sign-in-alt', 'color' => 'success'],
            ['date' => '23 Jan 2026, 09:45', 'event' => 'Failed login attempt', 'ip' => '203.45.67.89', 'icon' => 'fa-exclamation-triangle', 'color' => 'warning'],
        ];
    @endphp
    
    <div class="toast-container">
        <div class="toast-success" id="successToast">
            <i class="fas fa-check-circle"></i>
            <span class="toast-message">Profile updated successfully</span>
        </div>
    </div>
    
    <div class="row align-items-start">
        <div class="col-xl-6 col-lg-12">
            <div class="card h-auto">
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
            
            <div class="card h-auto">
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
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
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
                        
                        @if($twoFactorEnabled)
                        <div class="mb-3 p-3 rounded" style="background: #f3e8ff;">
                            <div class="mb-2">
                                <label class="mb-1 d-block" style="font-size: 0.8rem; color: #6b21a8;">Active Method(s)</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($mfaMethods as $method)
                                        @if($method === 'authenticator')
                                            <span class="badge badge-primary light"><i class="fas fa-mobile-alt me-1"></i>Authenticator App</span>
                                        @elseif($method === 'sms')
                                            <span class="badge badge-info light"><i class="fas fa-sms me-1"></i>SMS</span>
                                        @elseif($method === 'rcs')
                                            <span class="badge badge-purple light"><i class="fas fa-comment-dots me-1"></i>RCS</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @if(in_array('sms', $mfaMethods) || in_array('rcs', $mfaMethods))
                            <div class="mt-2">
                                <label class="mb-1 d-block" style="font-size: 0.8rem; color: #6b21a8;">Registered Number</label>
                                <span style="font-size: 0.9rem; color: #6b21a8;">{{ $mfaPhone }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        <div class="d-flex flex-wrap gap-2">
                            @if($twoFactorEnabled)
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#mfaMethodModal">
                                    <i class="fas fa-exchange-alt me-1"></i>Change Method
                                </button>
                                @if(in_array('authenticator', $mfaMethods))
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#reenrolAuthenticatorModal">
                                    <i class="fas fa-sync-alt me-1"></i>Re-enrol Authenticator
                                </button>
                                @endif
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#backupCodesModal">
                                    <i class="fas fa-key me-1"></i>Backup Codes
                                    <span class="badge bg-secondary ms-1">{{ $backupCodesRemaining }}</span>
                                </button>
                                @if(!$mfaEnforced)
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#disableMfaModal">
                                    <i class="fas fa-times me-1"></i>Disable
                                </button>
                                @endif
                            @else
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#enableMfaModal">
                                    <i class="fas fa-shield-alt me-1"></i>Enable MFA
                                </button>
                            @endif
                        </div>
                        
                        @if($mfaEnforced && $twoFactorEnabled)
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>MFA is enforced by your account administrator and cannot be disabled.
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 col-lg-12">
            <div class="card h-auto">
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
            
            <div class="card h-auto">
                <div class="card-header">
                    <h4 class="card-title">Audit & Metadata</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Last Login</label>
                            <span>{{ $lastLogin }}</span>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">IP: {{ $lastLoginIp }}</small>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Last Password Change</label>
                            <span>{{ $lastPasswordChange }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">MFA Last Verified</label>
                            <span>{{ $mfaLastVerified }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Account Created</label>
                            <span>{{ $accountCreated }}</span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div>
                        <h6 class="mb-3" style="font-size: 0.9rem;">Recent Security Events</h6>
                        <div class="security-events-list">
                            @foreach($securityEvents as $event)
                            <div class="d-flex align-items-start mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="font-size: 0.85rem;">
                                <div class="me-3">
                                    <span class="badge badge-{{ $event['color'] }} light" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="fas {{ $event['icon'] }}" style="font-size: 0.7rem;"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $event['event'] }}</span>
                                        <small class="text-muted">{{ $event['date'] }}</small>
                                    </div>
                                    <small class="text-muted">IP: {{ $event['ip'] }}</small>
                                </div>
                            </div>
                            @endforeach
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
                    <div class="card border-0 mb-0" id="passwordRulesCard" style="background: #f3e8ff;">
                        <div class="card-header border-0 p-2" style="cursor: pointer; background: transparent;" id="passwordRulesToggle">
                            <div class="d-flex justify-content-between align-items-center">
                                <span style="font-size: 0.85rem; color: #6b21a8;">
                                    <i class="fas fa-info-circle me-1" style="color: #886cc0;"></i>Password requirements
                                </span>
                                <i class="fas fa-chevron-down" id="passwordRulesIcon" style="font-size: 0.75rem; transition: transform 0.2s; color: #886cc0;"></i>
                            </div>
                        </div>
                        <div class="collapse" id="passwordRulesCollapse">
                            <div class="card-body pt-0 px-2 pb-2" style="color: #6b21a8;">
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

<!-- Enable MFA Modal -->
<div class="modal fade" id="enableMfaModal" tabindex="-1" aria-labelledby="enableMfaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enableMfaModalLabel">Enable Two-Factor Authentication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Choose your preferred authentication method:</p>
                
                <div class="list-group" id="mfaMethodSelection">
                    <label class="list-group-item list-group-item-action d-flex align-items-center" style="cursor: pointer;">
                        <input type="radio" name="mfaMethodChoice" value="authenticator" class="me-3">
                        <div>
                            <h6 class="mb-1"><i class="fas fa-mobile-alt me-2"></i>Authenticator App</h6>
                            <small class="text-muted">Use Google Authenticator, Authy, or similar apps</small>
                        </div>
                    </label>
                    <label class="list-group-item list-group-item-action d-flex align-items-center" style="cursor: pointer;">
                        <input type="radio" name="mfaMethodChoice" value="sms" class="me-3">
                        <div>
                            <h6 class="mb-1"><i class="fas fa-sms me-2"></i>SMS</h6>
                            <small class="text-muted">Receive codes via text message (UK numbers only)</small>
                        </div>
                    </label>
                    <label class="list-group-item list-group-item-action d-flex align-items-center" style="cursor: pointer;">
                        <input type="radio" name="mfaMethodChoice" value="rcs" class="me-3">
                        <div>
                            <h6 class="mb-1"><i class="fas fa-comment-dots me-2"></i>RCS</h6>
                            <small class="text-muted">Receive codes via RCS messaging (UK numbers only)</small>
                        </div>
                    </label>
                </div>
                
                <!-- SMS/RCS Phone Setup (hidden by default) -->
                <div id="phoneSetupSection" class="mt-4" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">UK Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="mfaPhoneInput" placeholder="07700 900123">
                        <div class="invalid-feedback" id="mfaPhoneError">Please enter a valid UK mobile number</div>
                        <small class="text-muted">Enter a UK mobile number starting with 07, +447, or 447</small>
                    </div>
                    <div id="otpSection" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Verification Code <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control" id="mfaOtpInput" placeholder="Enter 6-digit code" maxlength="6" style="width: 150px;">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="resendOtpBtn">Resend Code</button>
                            </div>
                            <div class="invalid-feedback" id="mfaOtpError">Invalid verification code</div>
                            <small class="text-muted" id="otpCooldownText"></small>
                        </div>
                    </div>
                </div>
                
                <!-- Authenticator Setup (hidden by default) -->
                <div id="authenticatorSetupSection" class="mt-4" style="display: none;">
                    <div class="text-center mb-3">
                        <div class="p-4 bg-light rounded d-inline-block">
                            <i class="fas fa-qrcode" style="font-size: 120px; color: #333;"></i>
                        </div>
                        <p class="text-muted mt-2 mb-0" style="font-size: 0.85rem;">Scan this QR code with your authenticator app</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Or enter this code manually:</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light" value="JBSWY3DPEHPK3PXP" readonly id="mfaSecretKey">
                            <button class="btn btn-outline-secondary" type="button" id="copySecretBtn">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Verification Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="authenticatorOtpInput" placeholder="Enter 6-digit code" maxlength="6" style="width: 150px;">
                        <div class="invalid-feedback" id="authenticatorOtpError">Invalid verification code</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="enableMfaBtn" disabled>Continue</button>
            </div>
        </div>
    </div>
</div>

<!-- Change MFA Method Modal -->
<div class="modal fade" id="mfaMethodModal" tabindex="-1" aria-labelledby="mfaMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mfaMethodModalLabel">Change MFA Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Select your new authentication method:</p>
                
                <div class="list-group" id="changeMfaMethodSelection">
                    <label class="list-group-item list-group-item-action d-flex align-items-center" style="cursor: pointer;">
                        <input type="radio" name="changeMfaMethod" value="authenticator" class="me-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><i class="fas fa-mobile-alt me-2"></i>Authenticator App</h6>
                            <small class="text-muted">Use Google Authenticator, Authy, or similar apps</small>
                        </div>
                    </label>
                    <label class="list-group-item list-group-item-action d-flex align-items-center" style="cursor: pointer;">
                        <input type="radio" name="changeMfaMethod" value="sms" class="me-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><i class="fas fa-sms me-2"></i>SMS</h6>
                            <small class="text-muted">Receive codes via text message (UK numbers only)</small>
                        </div>
                    </label>
                    <label class="list-group-item list-group-item-action d-flex align-items-center" style="cursor: pointer;">
                        <input type="radio" name="changeMfaMethod" value="rcs" class="me-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><i class="fas fa-comment-dots me-2"></i>RCS</h6>
                            <small class="text-muted">Receive codes via RCS messaging (UK numbers only)</small>
                        </div>
                    </label>
                </div>
                
                <div class="alert alert-warning mt-3 mb-0" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Changing your MFA method will require you to verify your identity.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="changeMfaMethodBtn" disabled>Continue</button>
            </div>
        </div>
    </div>
</div>

<!-- Re-enrol Authenticator Modal -->
<div class="modal fade" id="reenrolAuthenticatorModal" tabindex="-1" aria-labelledby="reenrolAuthenticatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reenrolAuthenticatorModalLabel">Re-enrol Authenticator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-4" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle me-2"></i>
                    Re-enrolling will generate a new secret key. Your old authenticator setup will no longer work.
                </div>
                
                <div class="text-center mb-3">
                    <div class="p-4 bg-light rounded d-inline-block">
                        <i class="fas fa-qrcode" style="font-size: 120px; color: #333;"></i>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.85rem;">Scan this new QR code with your authenticator app</p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Or enter this code manually:</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light" value="NEWKY3DPEHPK3ABC" readonly id="reenrolSecretKey">
                        <button class="btn btn-outline-secondary" type="button" id="copyReenrolSecretBtn">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Verification Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="reenrolOtpInput" placeholder="Enter 6-digit code" maxlength="6" style="width: 150px;">
                    <div class="invalid-feedback" id="reenrolOtpError">Invalid verification code</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="reenrolAuthenticatorBtn">Verify & Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Backup Codes Modal -->
<div class="modal fade" id="backupCodesModal" tabindex="-1" aria-labelledby="backupCodesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="backupCodesModalLabel">Backup Codes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Store these codes securely. Each code can only be used once.
                </div>
                
                <div class="mb-3">
                    <label class="text-muted mb-2 d-block" style="font-size: 0.8rem;">Remaining Codes: <strong id="remainingCodesCount">{{ $backupCodesRemaining }}</strong> of 10</label>
                    <div class="row g-2" id="backupCodesList">
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center">ABC12-DEF34</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center">GHI56-JKL78</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center">MNO90-PQR12</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center">STU34-VWX56</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center">YZA78-BCD90</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center">EFG12-HIJ34</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center text-muted text-decoration-line-through">KLM56-NOP78</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center text-muted text-decoration-line-through">QRS90-TUV12</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center text-muted text-decoration-line-through">WXY34-ZAB56</code></div>
                        <div class="col-6"><code class="d-block p-2 bg-light rounded text-center text-muted text-decoration-line-through">CDE78-FGH90</code></div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="copyBackupCodesBtn">
                        <i class="fas fa-copy me-1"></i>Copy All
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadBackupCodesBtn">
                        <i class="fas fa-download me-1"></i>Download
                    </button>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Regenerate Codes</h6>
                        <small class="text-muted">This will invalidate all existing codes</small>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="regenerateCodesBtn">
                        <i class="fas fa-sync-alt me-1"></i>Regenerate
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- Disable MFA Modal -->
<div class="modal fade" id="disableMfaModal" tabindex="-1" aria-labelledby="disableMfaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="disableMfaModalLabel">Disable Two-Factor Authentication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Disabling MFA will make your account less secure.
                </div>
                
                <p class="mb-3">To confirm, please enter your password:</p>
                
                <div class="mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="disableMfaPassword" autocomplete="current-password">
                    <div class="invalid-feedback" id="disableMfaPasswordError">Incorrect password</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="disableMfaBtn">Disable MFA</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[MyProfile] Page loaded');
    
    // ========== AUDIT EVENT UTILITY ==========
    var currentUserId = 'usr_8f4a2b1c'; // TODO: Get from backend session
    
    function emitAuditEvent(action, details) {
        var event = {
            userId: currentUserId,
            action: action,
            timestamp: new Date().toISOString(),
            ipAddress: '{{ request()->ip() ?? "127.0.0.1" }}',
            details: details || {}
        };
        
        console.log('[AuditEvent]', JSON.stringify(event, null, 2));
        
        // TODO: Send to backend API
        // fetch('/api/audit-events', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify(event)
        // });
        
        return event;
    }
    
    // ========================================
    
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
            // Emit audit events for changes
            var changedFields = [];
            fields.forEach(function(field) {
                var input = document.getElementById(field);
                if (input && input.value !== originalValues[field]) {
                    changedFields.push(field);
                }
            });
            
            if (emailChanged) {
                emitAuditEvent('EMAIL_CHANGED', {
                    oldEmail: originalValues['emailAddress'],
                    newEmail: emailInput.value
                });
                emailPendingBanner.classList.add('show');
                emailUnverifiedBadge.style.display = 'inline-block';
                document.getElementById('displayEmail').textContent = emailInput.value;
            }
            
            var mobileInput = document.getElementById('mobileNumber');
            if (mobileInput && mobileInput.value !== originalValues['mobileNumber']) {
                emitAuditEvent('MOBILE_NUMBER_CHANGED', {
                    oldMobile: originalValues['mobileNumber'],
                    newMobile: mobileInput.value
                });
            }
            
            // Emit general profile update event
            emitAuditEvent('PROFILE_UPDATED', {
                fieldsChanged: changedFields
            });
            
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
            
            emitAuditEvent('PASSWORD_CHANGED', {});
            console.log('[MyProfile] Password changed successfully');
        }, 1000);
    });
    
    // ========== MFA FUNCTIONALITY ==========
    
    // OTP rate limiting state (mock - would be server-side in production)
    var otpState = {
        lastRequestTime: null,
        requestCount: 0,
        cooldownMinutes: 15,
        maxRequestsPerDay: 4
    };
    
    // UK phone number validation and normalization
    function validateUKMobile(phone) {
        // Remove all non-digit characters except +
        var cleaned = phone.replace(/[^\d+]/g, '');
        
        // Valid formats: 07xxxxxxxxx, +447xxxxxxxxx, 447xxxxxxxxx
        var patterns = [
            /^07\d{9}$/,           // 07700900123
            /^\+447\d{9}$/,        // +447700900123
            /^447\d{9}$/           // 447700900123
        ];
        
        return patterns.some(function(pattern) {
            return pattern.test(cleaned);
        });
    }
    
    function normalizeUKMobile(phone) {
        var cleaned = phone.replace(/[^\d+]/g, '');
        
        // Convert to 447xxxxxxxxx format
        if (cleaned.startsWith('07')) {
            return '44' + cleaned.substring(1);
        } else if (cleaned.startsWith('+447')) {
            return cleaned.substring(1);
        } else if (cleaned.startsWith('447')) {
            return cleaned;
        }
        return cleaned;
    }
    
    function checkOTPCooldown() {
        if (!otpState.lastRequestTime) return { allowed: true };
        
        var now = new Date();
        var timeSinceLastRequest = (now - otpState.lastRequestTime) / 1000 / 60; // minutes
        
        if (otpState.requestCount >= otpState.maxRequestsPerDay) {
            return { 
                allowed: false, 
                message: 'Maximum OTP requests reached. Try again tomorrow.' 
            };
        }
        
        if (timeSinceLastRequest < otpState.cooldownMinutes) {
            var remainingMinutes = Math.ceil(otpState.cooldownMinutes - timeSinceLastRequest);
            return { 
                allowed: false, 
                message: 'Try again in ' + remainingMinutes + ' minute' + (remainingMinutes > 1 ? 's' : '') 
            };
        }
        
        return { allowed: true };
    }
    
    function recordOTPRequest() {
        otpState.lastRequestTime = new Date();
        otpState.requestCount++;
    }
    
    // Enable MFA Modal
    var enableMfaModal = document.getElementById('enableMfaModal');
    var enableMfaBtn = document.getElementById('enableMfaBtn');
    var mfaMethodRadios = document.querySelectorAll('input[name="mfaMethodChoice"]');
    var phoneSetupSection = document.getElementById('phoneSetupSection');
    var authenticatorSetupSection = document.getElementById('authenticatorSetupSection');
    var mfaPhoneInput = document.getElementById('mfaPhoneInput');
    var otpSection = document.getElementById('otpSection');
    var mfaOtpInput = document.getElementById('mfaOtpInput');
    var resendOtpBtn = document.getElementById('resendOtpBtn');
    var otpCooldownText = document.getElementById('otpCooldownText');
    
    var currentMfaStep = 'select'; // select, phone, otp, authenticator
    var selectedMfaMethod = null;
    
    mfaMethodRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            selectedMfaMethod = this.value;
            enableMfaBtn.disabled = false;
            
            // Reset sections
            phoneSetupSection.style.display = 'none';
            authenticatorSetupSection.style.display = 'none';
            otpSection.style.display = 'none';
            
            currentMfaStep = 'select';
            enableMfaBtn.textContent = 'Continue';
        });
    });
    
    enableMfaBtn.addEventListener('click', function() {
        if (currentMfaStep === 'select') {
            if (selectedMfaMethod === 'sms' || selectedMfaMethod === 'rcs') {
                phoneSetupSection.style.display = 'block';
                authenticatorSetupSection.style.display = 'none';
                currentMfaStep = 'phone';
                enableMfaBtn.textContent = 'Send Code';
            } else if (selectedMfaMethod === 'authenticator') {
                authenticatorSetupSection.style.display = 'block';
                phoneSetupSection.style.display = 'none';
                currentMfaStep = 'authenticator';
                enableMfaBtn.textContent = 'Verify & Enable';
            }
        } else if (currentMfaStep === 'phone') {
            // Validate phone
            if (!validateUKMobile(mfaPhoneInput.value)) {
                mfaPhoneInput.classList.add('is-invalid');
                return;
            }
            mfaPhoneInput.classList.remove('is-invalid');
            
            // Check cooldown
            var cooldownCheck = checkOTPCooldown();
            if (!cooldownCheck.allowed) {
                otpCooldownText.textContent = cooldownCheck.message;
                otpCooldownText.classList.add('text-danger');
                return;
            }
            
            // Send OTP
            recordOTPRequest();
            var normalizedPhone = normalizeUKMobile(mfaPhoneInput.value);
            console.log('[MFA] Sending OTP to: ' + normalizedPhone);
            
            otpSection.style.display = 'block';
            otpCooldownText.textContent = 'Code sent to ' + mfaPhoneInput.value;
            otpCooldownText.classList.remove('text-danger');
            currentMfaStep = 'otp';
            enableMfaBtn.textContent = 'Verify & Enable';
        } else if (currentMfaStep === 'otp') {
            // Validate OTP
            if (!mfaOtpInput.value || mfaOtpInput.value.length !== 6) {
                mfaOtpInput.classList.add('is-invalid');
                return;
            }
            mfaOtpInput.classList.remove('is-invalid');
            
            // Simulate verification
            enableMfaBtn.disabled = true;
            enableMfaBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
            
            setTimeout(function() {
                var modal = bootstrap.Modal.getInstance(enableMfaModal);
                modal.hide();
                successToast.classList.add('show');
                setTimeout(function() {
                    successToast.classList.remove('show');
                    location.reload();
                }, 2000);
                emitAuditEvent('MFA_ENABLED', { method: selectedMfaMethod });
                console.log('[MFA] SMS/RCS MFA enabled successfully');
            }, 1000);
        } else if (currentMfaStep === 'authenticator') {
            var authenticatorOtp = document.getElementById('authenticatorOtpInput');
            if (!authenticatorOtp.value || authenticatorOtp.value.length !== 6) {
                authenticatorOtp.classList.add('is-invalid');
                return;
            }
            authenticatorOtp.classList.remove('is-invalid');
            
            enableMfaBtn.disabled = true;
            enableMfaBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
            
            setTimeout(function() {
                var modal = bootstrap.Modal.getInstance(enableMfaModal);
                modal.hide();
                successToast.classList.add('show');
                setTimeout(function() {
                    successToast.classList.remove('show');
                    location.reload();
                }, 2000);
                emitAuditEvent('MFA_ENABLED', { method: 'authenticator' });
                console.log('[MFA] Authenticator MFA enabled successfully');
            }, 1000);
        }
    });
    
    // Resend OTP button
    if (resendOtpBtn) {
        resendOtpBtn.addEventListener('click', function() {
            var cooldownCheck = checkOTPCooldown();
            if (!cooldownCheck.allowed) {
                otpCooldownText.textContent = cooldownCheck.message;
                otpCooldownText.classList.add('text-danger');
                return;
            }
            
            recordOTPRequest();
            otpCooldownText.textContent = 'Code resent to ' + mfaPhoneInput.value;
            otpCooldownText.classList.remove('text-danger');
            console.log('[MFA] OTP resent');
        });
    }
    
    // Reset Enable MFA modal on close
    if (enableMfaModal) {
        enableMfaModal.addEventListener('hidden.bs.modal', function() {
            mfaMethodRadios.forEach(function(radio) { radio.checked = false; });
            phoneSetupSection.style.display = 'none';
            authenticatorSetupSection.style.display = 'none';
            otpSection.style.display = 'none';
            mfaPhoneInput.value = '';
            mfaPhoneInput.classList.remove('is-invalid');
            mfaOtpInput.value = '';
            mfaOtpInput.classList.remove('is-invalid');
            var authOtp = document.getElementById('authenticatorOtpInput');
            if (authOtp) { authOtp.value = ''; authOtp.classList.remove('is-invalid'); }
            currentMfaStep = 'select';
            selectedMfaMethod = null;
            enableMfaBtn.disabled = true;
            enableMfaBtn.textContent = 'Continue';
        });
    }
    
    // Change MFA Method Modal
    var changeMfaMethodBtn = document.getElementById('changeMfaMethodBtn');
    var changeMfaRadios = document.querySelectorAll('input[name="changeMfaMethod"]');
    
    changeMfaRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            changeMfaMethodBtn.disabled = false;
        });
    });
    
    if (changeMfaMethodBtn) {
        changeMfaMethodBtn.addEventListener('click', function() {
            var selectedMethod = document.querySelector('input[name="changeMfaMethod"]:checked');
            if (selectedMethod) {
                changeMfaMethodBtn.disabled = true;
                changeMfaMethodBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                
                setTimeout(function() {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('mfaMethodModal'));
                    modal.hide();
                    successToast.classList.add('show');
                    setTimeout(function() {
                        successToast.classList.remove('show');
                    }, 3000);
                    emitAuditEvent('MFA_METHOD_CHANGED', { newMethod: selectedMethod.value });
                    console.log('[MFA] Method changed to: ' + selectedMethod.value);
                }, 1000);
            }
        });
    }
    
    // Backup Codes functionality
    var copyBackupCodesBtn = document.getElementById('copyBackupCodesBtn');
    var downloadBackupCodesBtn = document.getElementById('downloadBackupCodesBtn');
    var regenerateCodesBtn = document.getElementById('regenerateCodesBtn');
    
    if (copyBackupCodesBtn) {
        copyBackupCodesBtn.addEventListener('click', function() {
            var codes = [];
            document.querySelectorAll('#backupCodesList code:not(.text-decoration-line-through)').forEach(function(el) {
                codes.push(el.textContent);
            });
            navigator.clipboard.writeText(codes.join('\n'));
            copyBackupCodesBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
            setTimeout(function() {
                copyBackupCodesBtn.innerHTML = '<i class="fas fa-copy me-1"></i>Copy All';
            }, 2000);
        });
    }
    
    if (downloadBackupCodesBtn) {
        downloadBackupCodesBtn.addEventListener('click', function() {
            var codes = [];
            document.querySelectorAll('#backupCodesList code:not(.text-decoration-line-through)').forEach(function(el) {
                codes.push(el.textContent);
            });
            var blob = new Blob(['QuickSMS Backup Codes\n\n' + codes.join('\n') + '\n\nKeep these codes safe!'], { type: 'text/plain' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'quicksms-backup-codes.txt';
            a.click();
            URL.revokeObjectURL(url);
        });
    }
    
    if (regenerateCodesBtn) {
        regenerateCodesBtn.addEventListener('click', function() {
            if (confirm('Are you sure? All existing backup codes will be invalidated.')) {
                regenerateCodesBtn.disabled = true;
                regenerateCodesBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Regenerating...';
                
                setTimeout(function() {
                    regenerateCodesBtn.disabled = false;
                    regenerateCodesBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Regenerate';
                    document.getElementById('remainingCodesCount').textContent = '10';
                    console.log('[MFA] Backup codes regenerated');
                }, 1000);
            }
        });
    }
    
    // Copy secret key buttons
    var copySecretBtn = document.getElementById('copySecretBtn');
    if (copySecretBtn) {
        copySecretBtn.addEventListener('click', function() {
            var secret = document.getElementById('mfaSecretKey').value;
            navigator.clipboard.writeText(secret);
            copySecretBtn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(function() {
                copySecretBtn.innerHTML = '<i class="fas fa-copy"></i>';
            }, 2000);
        });
    }
    
    var copyReenrolSecretBtn = document.getElementById('copyReenrolSecretBtn');
    if (copyReenrolSecretBtn) {
        copyReenrolSecretBtn.addEventListener('click', function() {
            var secret = document.getElementById('reenrolSecretKey').value;
            navigator.clipboard.writeText(secret);
            copyReenrolSecretBtn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(function() {
                copyReenrolSecretBtn.innerHTML = '<i class="fas fa-copy"></i>';
            }, 2000);
        });
    }
    
    // Re-enrol Authenticator
    var reenrolAuthenticatorBtn = document.getElementById('reenrolAuthenticatorBtn');
    if (reenrolAuthenticatorBtn) {
        reenrolAuthenticatorBtn.addEventListener('click', function() {
            var otpInput = document.getElementById('reenrolOtpInput');
            if (!otpInput.value || otpInput.value.length !== 6) {
                otpInput.classList.add('is-invalid');
                return;
            }
            otpInput.classList.remove('is-invalid');
            
            reenrolAuthenticatorBtn.disabled = true;
            reenrolAuthenticatorBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
            
            setTimeout(function() {
                var modal = bootstrap.Modal.getInstance(document.getElementById('reenrolAuthenticatorModal'));
                modal.hide();
                successToast.classList.add('show');
                setTimeout(function() {
                    successToast.classList.remove('show');
                }, 3000);
                reenrolAuthenticatorBtn.disabled = false;
                reenrolAuthenticatorBtn.textContent = 'Verify & Save';
                console.log('[MFA] Authenticator re-enrolled');
            }, 1000);
        });
    }
    
    // Disable MFA
    var disableMfaBtn = document.getElementById('disableMfaBtn');
    if (disableMfaBtn) {
        disableMfaBtn.addEventListener('click', function() {
            var passwordInput = document.getElementById('disableMfaPassword');
            if (!passwordInput.value) {
                passwordInput.classList.add('is-invalid');
                return;
            }
            passwordInput.classList.remove('is-invalid');
            
            disableMfaBtn.disabled = true;
            disableMfaBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Disabling...';
            
            setTimeout(function() {
                var modal = bootstrap.Modal.getInstance(document.getElementById('disableMfaModal'));
                modal.hide();
                successToast.classList.add('show');
                setTimeout(function() {
                    successToast.classList.remove('show');
                    location.reload();
                }, 2000);
                emitAuditEvent('MFA_DISABLED', {});
                console.log('[MFA] MFA disabled');
            }, 1000);
        });
    }
});
</script>
@endpush

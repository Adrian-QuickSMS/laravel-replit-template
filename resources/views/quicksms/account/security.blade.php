@extends('layouts.quicksms')

@section('title', 'Security Settings')

@push('styles')
<style>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}
.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}
.breadcrumb-item.active {
    font-weight: 500;
}
.security-card {
    background: #fff;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.security-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.security-card-header i {
    color: #886cc0;
    font-size: 1.1rem;
}
.security-card-header h6 {
    margin: 0;
    font-weight: 600;
    color: #374151;
    font-size: 0.95rem;
}
.security-card-body {
    padding: 1.25rem;
}
.setting-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}
.setting-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.setting-row:first-child {
    padding-top: 0;
}
.setting-info {
    flex: 1;
    padding-right: 1.5rem;
}
.setting-label {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}
.setting-description {
    font-size: 0.8rem;
    color: #6b7280;
    line-height: 1.5;
}
.setting-control {
    flex-shrink: 0;
}
.form-switch .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
    cursor: pointer;
}
.form-switch .form-check-input:checked {
    background-color: #886cc0;
    border-color: #886cc0;
}
.form-switch .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(136, 108, 192, 0.25);
    border-color: #886cc0;
}
.warning-banner {
    background: #f3e8ff;
    border: none;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    margin-top: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #6b21a8;
}
.warning-banner i {
    color: #886cc0;
}
.form-select-sm {
    font-size: 0.85rem;
    padding: 0.35rem 2rem 0.35rem 0.75rem;
}
.sub-setting {
    margin-top: 0.75rem;
    padding-left: 0;
}
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0;
}
.toggle-row:first-child {
    padding-top: 0;
}
.toggle-label {
    font-size: 0.85rem;
    color: #374151;
}
.country-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
.country-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.country-pill.approved {
    background: #dcfce7;
    color: #166534;
}
.country-pill.pending {
    background: #f3e8ff;
    color: #6b21a8;
}
.country-pill.rejected {
    background: #fee2e2;
    color: #991b1b;
}
.country-pill .remove-btn {
    background: none;
    border: none;
    padding: 0;
    margin-left: 0.25rem;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.15s;
}
.country-pill .remove-btn:hover {
    opacity: 1;
}
.add-country-btn {
    background: #f3e8ff;
    color: #886cc0;
    border: 1px dashed #886cc0;
    padding: 0.375rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}
.add-country-btn:hover {
    background: #886cc0;
    color: white;
    border-style: solid;
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}
.status-dot.approved { background: #16a34a; }
.status-dot.pending { background: #886cc0; }
.status-dot.rejected { background: #dc2626; }
.save-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #16a34a;
    opacity: 0;
    transition: opacity 0.3s;
}
.save-indicator.show {
    opacity: 1;
}
.save-indicator i {
    color: #16a34a;
}
.mfa-method-toggles {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.mfa-method-option {
    display: inline-block;
    cursor: pointer;
}
.mfa-method-option input[type="checkbox"] {
    display: none;
}
.mfa-method-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: 1px solid #e9ecef;
    border-radius: 2rem;
    font-size: 0.85rem;
    color: #6b7280;
    background: #f9fafb;
    transition: all 0.15s;
}
.mfa-method-pill i {
    font-size: 0.9rem;
}
.mfa-method-option:hover .mfa-method-pill {
    border-color: #886cc0;
    background: #faf8ff;
}
.mfa-method-option input[type="checkbox"]:checked + .mfa-method-pill {
    border-color: #886cc0;
    background: rgba(111, 66, 193, 0.08);
    color: #374151;
}
.mfa-method-option input[type="checkbox"]:checked + .mfa-method-pill i {
    color: #886cc0;
}
.add-ip-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    background: rgba(111, 66, 193, 0.08);
    color: #886cc0;
    border: 1px dashed #886cc0;
    border-radius: 0.375rem;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    margin-top: 0.75rem;
}
.add-ip-btn:hover {
    background: #886cc0;
    color: white;
    border-style: solid;
}
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #9ca3af;
}
.empty-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}
.empty-state p {
    font-size: 0.8rem;
    margin: 0;
}
.security-loading-overlay {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    color: #886cc0;
    font-size: 0.9rem;
    gap: 0.5rem;
}
.mode-selector {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.mode-option {
    flex: 1;
    padding: 0.6rem 0.75rem;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
}
.mode-option:hover {
    border-color: #886cc0;
    background: #faf8ff;
}
.mode-option.active {
    border-color: #886cc0;
    background: rgba(111, 66, 193, 0.08);
}
.mode-option .mode-title {
    font-weight: 600;
    font-size: 0.8rem;
    color: #374151;
    margin-bottom: 0.15rem;
}
.mode-option .mode-desc {
    font-size: 0.7rem;
    color: #6b7280;
    line-height: 1.3;
}
.toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
}
.qs-toast {
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
    min-width: 280px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.qs-toast.show {
    opacity: 1;
    transform: translateX(0);
}
.qs-toast.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}
.qs-toast.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>
@endpush

@section('content')
<div class="toast-container" id="toastContainer"></div>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('account.details') }}">Account</a></li>
                    <li class="breadcrumb-item active">Security Settings</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <h4 class="mb-0" style="font-weight: 600; color: #374151;">Security Settings</h4>
            <div class="save-indicator" id="saveIndicator">
                <i class="fas fa-check-circle"></i>
                <span>Changes saved</span>
            </div>
        </div>
    </div>

    <div id="securityLoadingState" class="security-loading-overlay">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Loading security settings...</span>
    </div>

    <div id="securityContent" class="d-none">
    <div class="row">
        <div class="col-lg-8">
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-shield-alt"></i>
                    <h6>MFA Policy</h6>
                </div>
                <div class="security-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Require MFA for all users</div>
                            <div class="setting-description">When enabled, all users must configure multi-factor authentication to access their account.</div>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="mfaRequiredToggle">
                            </div>
                        </div>
                    </div>
                    
                    <div class="warning-banner d-none" id="mfaWarning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Disabling MFA reduces account security. This is not recommended.</span>
                    </div>
                    
                    <div class="mfa-methods-section" id="mfaMethodsSection">
                        <div class="setting-info mb-2" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e9ecef;">
                            <div class="setting-label">Allowed MFA Methods</div>
                            <div class="setting-description">Select which authentication methods users can use. At least one method must be enabled.</div>
                        </div>
                        <div class="mfa-method-toggles">
                            <label class="mfa-method-option" id="methodAuthenticatorOption">
                                <input type="checkbox" id="mfaMethodAuthenticator" value="authenticator">
                                <span class="mfa-method-pill">
                                    <i class="fas fa-mobile-alt"></i>
                                    Authenticator App (TOTP)
                                </span>
                            </label>
                            <label class="mfa-method-option" id="methodSmsRcsOption">
                                <input type="checkbox" id="mfaMethodSmsRcs" value="sms_rcs">
                                <span class="mfa-method-pill">
                                    <i class="fas fa-sms"></i>
                                    SMS/RCS OTP
                                </span>
                            </label>
                        </div>
                        <div class="warning-banner d-none" id="mfaMethodsWarning" style="margin-top: 0.75rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>At least one MFA method must be enabled.</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-database"></i>
                    <h6>Message Data Retention</h6>
                </div>
                <div class="security-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Message Log Retention Period</div>
                            <div class="setting-description">How long message logs and delivery receipts are stored. Aggregated reporting and billing records are not affected by this setting.</div>
                        </div>
                        <div class="setting-control">
                            <select class="form-select form-select-sm" id="retentionPeriod" style="width: 140px;">
                                <option value="30">30 days</option>
                                <option value="60">60 days</option>
                                <option value="90">90 days</option>
                                <option value="120">120 days</option>
                                <option value="150">150 days</option>
                                <option value="180">180 days</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-eye-slash"></i>
                    <h6>Data Visibility & Masking</h6>
                </div>
                <div class="security-card-body">
                    <p class="setting-description mb-3">Control which data fields are masked in Message Logs, Reporting, and Exports. When enabled, data is masked or hidden from view.</p>
                    <div class="toggle-row">
                        <div>
                            <span class="toggle-label">Mobile Number</span>
                            <div style="font-size: 0.7rem; color: #9ca3af;">07700900123 &rarr; 077****0123</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="maskMobile">
                        </div>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <span class="toggle-label">Message Content</span>
                            <div style="font-size: 0.7rem; color: #9ca3af;">Full text &rarr; [REDACTED]</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="maskContent">
                        </div>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <span class="toggle-label">Date/Time Sent</span>
                            <div style="font-size: 0.7rem; color: #9ca3af;">18/03/2026 14:30 &rarr; 18/03/2026 --:--</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="maskSentTime">
                        </div>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <span class="toggle-label">Date/Time Delivered</span>
                            <div style="font-size: 0.7rem; color: #9ca3af;">18/03/2026 14:30 &rarr; 18/03/2026 --:--</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="maskDeliveredTime">
                        </div>
                    </div>
                    <div class="toggle-row" style="border-top: 1px solid #e9ecef; margin-top: 0.5rem; padding-top: 0.75rem;">
                        <div>
                            <span class="toggle-label" style="font-weight: 600;">Owner/Admin Bypass</span>
                            <div style="font-size: 0.7rem; color: #9ca3af;">Owners and Admins see unmasked data even when masking is enabled</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="ownerBypassMasking">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-ban"></i>
                    <h6>Anti-Flood Protection</h6>
                </div>
                <div class="security-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable Anti-Flood Protection</div>
                            <div class="setting-description">Prevents sending identical message content to the same recipient within a time window. Applies to Portal, API, and Email-to-SMS.</div>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="antiFloodToggle">
                            </div>
                        </div>
                    </div>
                    <div id="antiFloodOptions" class="d-none" style="padding-top: 0.75rem;">
                        <label class="form-label" style="font-size: 0.8rem; color: #374151; font-weight: 600;">Mode</label>
                        <div class="mode-selector" id="antiFloodModeSelector">
                            <div class="mode-option" data-mode="enforce">
                                <div class="mode-title"><i class="fas fa-shield-alt me-1"></i>Enforce</div>
                                <div class="mode-desc">Block duplicate messages</div>
                            </div>
                            <div class="mode-option" data-mode="monitor">
                                <div class="mode-title"><i class="fas fa-eye me-1"></i>Monitor</div>
                                <div class="mode-desc">Log duplicates but allow sending</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" style="font-size: 0.8rem; color: #374151; font-weight: 600;">Protection Window</label>
                            <select class="form-select form-select-sm" id="antiFloodWindow" style="width: 140px;">
                                <option value="2">2 hours</option>
                                <option value="4">4 hours</option>
                                <option value="8">8 hours</option>
                                <option value="12">12 hours</option>
                                <option value="24">24 hours</option>
                                <option value="48">48 hours</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-clock"></i>
                    <h6>Out-of-Hours Sending Restriction</h6>
                </div>
                <div class="security-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Restrict Out-of-Hours Sending</div>
                            <div class="setting-description">When enabled, blocks or holds outbound messages during the configured time window. Applies to Portal, API, and Email-to-SMS.</div>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="outOfHoursToggle">
                            </div>
                        </div>
                    </div>
                    <div id="outOfHoursOptions" class="d-none" style="padding-top: 0.75rem;">
                        <div class="row g-3 mb-3">
                            <div class="col-auto">
                                <label class="form-label" style="font-size: 0.8rem; color: #374151; font-weight: 600;">Start Time</label>
                                <input type="time" class="form-control form-control-sm" id="oohStartTime" value="21:00" style="width: 130px;">
                            </div>
                            <div class="col-auto">
                                <label class="form-label" style="font-size: 0.8rem; color: #374151; font-weight: 600;">End Time</label>
                                <input type="time" class="form-control form-control-sm" id="oohEndTime" value="08:00" style="width: 130px;">
                            </div>
                            <div class="col-auto d-flex align-items-end">
                                <span id="oohTimezone" class="text-muted" style="font-size: 0.75rem; padding-bottom: 0.35rem;"></span>
                            </div>
                        </div>
                        <label class="form-label" style="font-size: 0.8rem; color: #374151; font-weight: 600;">Action for blocked messages</label>
                        <div class="mode-selector" id="oohActionSelector">
                            <div class="mode-option" data-action="reject">
                                <div class="mode-title"><i class="fas fa-times-circle me-1"></i>Reject</div>
                                <div class="mode-desc">Return error — caller retries later</div>
                            </div>
                            <div class="mode-option" data-action="hold">
                                <div class="mode-title"><i class="fas fa-pause-circle me-1"></i>Hold</div>
                                <div class="mode-desc">Queue and auto-send when window opens</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-globe"></i>
                    <h6>Allowed Destination Countries</h6>
                </div>
                <div class="security-card-body">
                    <p class="setting-description mb-3">Messages can only be sent to approved countries. New country requests require approval before sending is permitted.</p>
                    <div class="country-list" id="countryList">
                        @if(isset($availableCountries))
                        @foreach($availableCountries->where('default_status', 'allowed') as $country)
                        <span class="country-pill approved">
                            <span class="status-dot approved"></span>
                            {{ $country->country_name }}
                        </span>
                        @endforeach
                        @endif
                        @if(isset($approvedOverrides))
                        @foreach($approvedOverrides as $override)
                        @if(!isset($availableCountries) || !$availableCountries->where('default_status', 'allowed')->where('country_iso', $override->country_iso)->count())
                        <span class="country-pill approved">
                            <span class="status-dot approved"></span>
                            {{ $override->country_name }}
                        </span>
                        @endif
                        @endforeach
                        @endif
                        @if(isset($pendingRequests))
                        @foreach($pendingRequests as $pending)
                        <span class="country-pill pending">
                            <span class="status-dot pending"></span>
                            {{ $pending->country_name }}
                        </span>
                        @endforeach
                        @endif
                        <button type="button" class="add-country-btn" data-bs-toggle="modal" data-bs-target="#addCountryModal">
                            <i class="fas fa-plus me-1"></i>Add Country
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-network-wired"></i>
                    <h6>Login IP Allowlist</h6>
                    <span class="ms-auto" id="ipCounter" style="font-size: 0.75rem; color: #6b7280;"></span>
                </div>
                <div class="security-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Restrict portal login by IP allowlist</div>
                            <div class="setting-description">When enabled, users can only log in from IP addresses in the allowlist. Use this to restrict access to specific office locations or VPNs.</div>
                            
                            <div class="alert d-none mt-2" id="ipAllowlistError" style="background: #fee2e2; border: 1px solid #fecaca; font-size: 0.8rem; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: 0.375rem;">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Add at least one IP before enabling to prevent lockout.
                            </div>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ipAllowlistToggle">
                            </div>
                        </div>
                    </div>
                    
                    <div id="ipAllowlistContainer">
                        <div class="d-flex align-items-center justify-content-between mb-3 mt-3" style="padding: 0.75rem; background: rgba(111, 66, 193, 0.08); border-radius: 0.375rem;">
                            <div style="font-size: 0.85rem; color: #495057;">
                                <i class="fas fa-info-circle me-1" style="color: #886cc0;"></i>
                                Your current IP: <strong id="currentIPDisplay">...</strong>
                            </div>
                            <button type="button" class="btn btn-sm" id="addCurrentIPBtn" style="background: #886cc0; color: white; font-size: 0.8rem;">
                                <i class="fas fa-plus me-1"></i>Add Current IP
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="ipAllowlistTable">
                                <thead>
                                    <tr>
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">Label</th>
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">IP / CIDR</th>
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">Status</th>
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">Added</th>
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef; width: 60px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="ipAllowlistTableBody"></tbody>
                            </table>
                        </div>
                        
                        <div class="text-center py-3 d-none" id="ipAllowlistEmpty" style="color: #9ca3af; font-size: 0.85rem;">
                            <i class="fas fa-shield-alt mb-2" style="font-size: 1.5rem;"></i>
                            <div>No IP addresses in allowlist</div>
                        </div>
                        
                        <button type="button" class="add-ip-btn mt-2" data-bs-toggle="modal" data-bs-target="#addIPModal">
                            <i class="fas fa-plus"></i>Add IP Address
                        </button>
                    </div>
                    
                    <div class="warning-banner d-none" id="ipAllowlistWarning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Ensure your current IP is in the allowlist before enabling, or you may lock yourself out.</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="security-card" style="background: #f9fafb;">
                <div class="security-card-body">
                    <h6 style="font-weight: 600; color: #374151; margin-bottom: 0.75rem;"><i class="fas fa-info-circle me-2" style="color: #886cc0;"></i>About Security Settings</h6>
                    <p style="font-size: 0.8rem; color: #6b7280; line-height: 1.6; margin-bottom: 0.75rem;">
                        These settings apply to your entire account. Only Account Owners and Admins can modify security settings.
                    </p>
                    <p style="font-size: 0.8rem; color: #6b7280; line-height: 1.6; margin-bottom: 0;">
                        All changes are automatically logged for compliance purposes.
                    </p>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="addCountryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-globe me-2" style="color: #886cc0;"></i>Add Destination Country</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Country</label>
                    @php
                        $excludedIsos = collect();
                        if(isset($availableCountries)) {
                            $excludedIsos = $excludedIsos->merge($availableCountries->where('default_status', 'allowed')->pluck('country_iso'));
                        }
                        if(isset($pendingRequests)) {
                            $excludedIsos = $excludedIsos->merge($pendingRequests->pluck('country_code'));
                        }
                        if(isset($approvedOverrides)) {
                            $excludedIsos = $excludedIsos->merge($approvedOverrides->pluck('country_iso'));
                        }
                        $excludedIsos = $excludedIsos->unique()->toArray();
                    @endphp
                    <select class="form-select" id="newCountrySelect">
                        <option value="">Choose a country...</option>
                        @if(isset($availableCountries))
                        @foreach($availableCountries as $country)
                        @if(!in_array($country->country_iso, $excludedIsos))
                        <option value="{{ $country->country_iso }}">{{ $country->country_name }} (+{{ $country->country_prefix }})</option>
                        @endif
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="alert" style="background: rgba(111, 66, 193, 0.08); border: none; font-size: 0.8rem; color: #495057;">
                    <i class="fas fa-info-circle me-1" style="color: #886cc0;"></i>
                    New countries require approval before messages can be sent. This typically takes 1-2 business days.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmAddCountry" style="background: #886cc0; color: white;">
                    <i class="fas fa-plus me-1"></i>Request Country
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addIPModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-network-wired me-2" style="color: #886cc0;"></i>Add IP Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">IP Address or CIDR Range <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newIPAddress" placeholder="e.g., 192.168.1.1 or 10.0.0.0/24">
                    <div class="form-text">IPv4 address or CIDR range</div>
                    <div class="invalid-feedback" id="newIPAddressError">Please enter a valid IP address</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Label</label>
                    <input type="text" class="form-control" id="newIPLabel" placeholder="e.g., Office Network, VPN Gateway">
                </div>
                <div class="alert" style="background: rgba(111, 66, 193, 0.08); border: none; font-size: 0.8rem; color: #495057;">
                    <i class="fas fa-info-circle me-1" style="color: #886cc0;"></i>
                    Your current IP address is <strong id="currentUserIP">...</strong>. Ensure it is included in the allowlist.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmAddIP" style="background: #886cc0; color: white;">
                    <i class="fas fa-plus me-1"></i>Add IP
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="removeIPModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash-alt me-2" style="color: #dc2626;"></i>Remove IP Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="removeIPId">
                <p style="font-size: 0.9rem; color: #374151;">
                    Are you sure you want to remove this IP entry?
                </p>
                <div class="p-2 mb-3" style="background: #f9fafb; border-radius: 0.375rem; font-family: monospace;">
                    <span id="removeIPDisplay"></span>
                    <span class="ms-2 text-muted" id="removeIPLabelDisplay"></span>
                </div>
                <div class="alert" style="background: #fef3c7; border: none; font-size: 0.8rem; color: #92400e;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    If this is your only IP entry and the allowlist is enabled, you may need to contact support to regain access.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveIP">
                    <i class="fas fa-trash-alt me-1"></i>Remove
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmIPAllowlistModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2" style="color: #f59e0b;"></i>Enable IP Allowlist?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.9rem; color: #374151; margin-bottom: 1rem;">
                    Are you sure? If you haven't added your office/VPN IPs you may lock yourself out.
                </p>
                <div class="alert" style="background: rgba(111, 66, 193, 0.08); border: none; font-size: 0.8rem; color: #495057;">
                    <i class="fas fa-info-circle me-1" style="color: #886cc0;"></i>
                    Your current IP will be automatically added if not already in the allowlist.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelIPAllowlist">Cancel</button>
                <button type="button" class="btn" id="confirmEnableIPAllowlist" style="background: #886cc0; color: white;">
                    <i class="fas fa-check me-1"></i>Yes, Enable
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var ipEntries = [];
    var ipLimit = 50;
    var currentIp = '';
    var savingFlags = {};

    function apiCall(url, method, body) {
        var opts = {
            method: method || 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        };
        if (body) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function(response) {
            if (!response.ok) {
                return response.json().catch(function() { return { message: 'Request failed' }; }).then(function(err) {
                    throw new Error(err.message || 'HTTP ' + response.status);
                });
            }
            return response.json();
        });
    }

    function showToast(type, message) {
        var container = document.getElementById('toastContainer');
        var toast = document.createElement('div');
        toast.className = 'qs-toast ' + type;
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        toast.innerHTML = '<i class="fas ' + icon + '"></i><span>' + escapeHtml(message) + '</span>';
        container.appendChild(toast);
        requestAnimationFrame(function() {
            toast.classList.add('show');
        });
        setTimeout(function() {
            toast.classList.remove('show');
            setTimeout(function() { toast.remove(); }, 300);
        }, 3500);
    }

    function showSaveIndicator() {
        var indicator = document.getElementById('saveIndicator');
        indicator.classList.add('show');
        setTimeout(function() { indicator.classList.remove('show'); }, 2000);
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function setButtonLoading(btn, loading) {
        if (!btn) return;
        if (loading) {
            btn.disabled = true;
            btn._originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        } else {
            btn.disabled = false;
            if (btn._originalHtml) btn.innerHTML = btn._originalHtml;
        }
    }

    apiCall('/api/account/security/settings').then(function(result) {
        var data = result.data;
        populateRetention(data.retention);
        populateMasking(data.masking);
        populateAntiFlood(data.anti_flood);
        populateOutOfHours(data.out_of_hours);
        populateIpAllowlist(data.ip_allowlist);

        document.getElementById('securityLoadingState').classList.add('d-none');
        document.getElementById('securityContent').classList.remove('d-none');
    }).catch(function(err) {
        document.getElementById('securityLoadingState').innerHTML =
            '<i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i>' +
            '<span style="color: #dc2626;">Failed to load security settings. Please refresh the page.</span>';
        console.error('Failed to load security settings:', err);
    });

    apiCall('/api/account/security/ip-allowlist/current-ip').then(function(result) {
        currentIp = result.data.ip_address;
        var displays = document.querySelectorAll('#currentIPDisplay, #currentUserIP');
        displays.forEach(function(el) { el.textContent = currentIp; });
    }).catch(function() {
        currentIp = '';
    });

    function populateRetention(data) {
        var select = document.getElementById('retentionPeriod');
        if (!select) return;
        select.value = String(data.message_retention_days);

        select.addEventListener('change', function() {
            var val = parseInt(this.value, 10);
            apiCall('/api/account/security/retention', 'PUT', { message_retention_days: val })
                .then(function() {
                    showToast('success', 'Retention period updated');
                    showSaveIndicator();
                })
                .catch(function(err) {
                    showToast('error', err.message);
                });
        });
    }

    function populateMasking(data) {
        var maskMobile = document.getElementById('maskMobile');
        var maskContent = document.getElementById('maskContent');
        var maskSentTime = document.getElementById('maskSentTime');
        var maskDeliveredTime = document.getElementById('maskDeliveredTime');
        var ownerBypass = document.getElementById('ownerBypassMasking');

        if (maskMobile) maskMobile.checked = data.config.mask_mobile;
        if (maskContent) maskContent.checked = data.config.mask_content;
        if (maskSentTime) maskSentTime.checked = data.config.mask_sent_time;
        if (maskDeliveredTime) maskDeliveredTime.checked = data.config.mask_delivered_time;
        if (ownerBypass) ownerBypass.checked = data.owner_bypass_masking;

        function saveMasking() {
            if (savingFlags.masking) return;
            savingFlags.masking = true;

            apiCall('/api/account/security/masking', 'PUT', {
                mask_mobile: maskMobile ? maskMobile.checked : false,
                mask_content: maskContent ? maskContent.checked : false,
                mask_sent_time: maskSentTime ? maskSentTime.checked : false,
                mask_delivered_time: maskDeliveredTime ? maskDeliveredTime.checked : false,
                owner_bypass_masking: ownerBypass ? ownerBypass.checked : true,
            }).then(function() {
                showToast('success', 'Masking settings updated');
                showSaveIndicator();
            }).catch(function(err) {
                showToast('error', err.message);
            }).finally(function() {
                savingFlags.masking = false;
            });
        }

        [maskMobile, maskContent, maskSentTime, maskDeliveredTime, ownerBypass].forEach(function(el) {
            if (el) el.addEventListener('change', saveMasking);
        });
    }

    function populateAntiFlood(data) {
        var toggle = document.getElementById('antiFloodToggle');
        var options = document.getElementById('antiFloodOptions');
        var windowSelect = document.getElementById('antiFloodWindow');
        var modeSelector = document.getElementById('antiFloodModeSelector');
        var currentMode = data.mode || 'enforce';

        if (!toggle) return;
        toggle.checked = data.enabled;
        if (data.enabled) options.classList.remove('d-none');
        if (windowSelect) windowSelect.value = String(data.window_hours);

        function setActiveMode(mode) {
            currentMode = mode;
            modeSelector.querySelectorAll('.mode-option').forEach(function(opt) {
                if (opt.dataset.mode === mode) {
                    opt.classList.add('active');
                } else {
                    opt.classList.remove('active');
                }
            });
        }
        setActiveMode(data.enabled ? currentMode : 'enforce');

        function saveAntiFlood() {
            if (savingFlags.antiFlood) return;
            savingFlags.antiFlood = true;

            var enabled = toggle.checked;
            apiCall('/api/account/security/anti-flood', 'PUT', {
                enabled: enabled,
                mode: enabled ? currentMode : 'off',
                window_hours: parseInt(windowSelect.value, 10),
            }).then(function() {
                showToast('success', 'Anti-flood settings updated');
                showSaveIndicator();
            }).catch(function(err) {
                showToast('error', err.message);
            }).finally(function() {
                savingFlags.antiFlood = false;
            });
        }

        toggle.addEventListener('change', function() {
            if (this.checked) {
                options.classList.remove('d-none');
            } else {
                options.classList.add('d-none');
            }
            saveAntiFlood();
        });

        modeSelector.querySelectorAll('.mode-option').forEach(function(opt) {
            opt.addEventListener('click', function() {
                setActiveMode(this.dataset.mode);
                saveAntiFlood();
            });
        });

        windowSelect.addEventListener('change', saveAntiFlood);
    }

    function populateOutOfHours(data) {
        var toggle = document.getElementById('outOfHoursToggle');
        var options = document.getElementById('outOfHoursOptions');
        var startInput = document.getElementById('oohStartTime');
        var endInput = document.getElementById('oohEndTime');
        var timezoneEl = document.getElementById('oohTimezone');
        var actionSelector = document.getElementById('oohActionSelector');
        var currentAction = data.action || 'reject';

        if (!toggle) return;
        toggle.checked = data.enabled;
        if (data.enabled) options.classList.remove('d-none');
        if (startInput) startInput.value = data.start || '21:00';
        if (endInput) endInput.value = data.end || '08:00';
        if (timezoneEl) timezoneEl.textContent = data.timezone || 'Europe/London';

        function setActiveAction(action) {
            currentAction = action;
            actionSelector.querySelectorAll('.mode-option').forEach(function(opt) {
                if (opt.dataset.action === action) {
                    opt.classList.add('active');
                } else {
                    opt.classList.remove('active');
                }
            });
        }
        setActiveAction(currentAction);

        function saveOutOfHours() {
            if (savingFlags.outOfHours) return;
            savingFlags.outOfHours = true;

            var enabled = toggle.checked;
            var body = { enabled: enabled };
            if (enabled) {
                body.start = startInput.value;
                body.end = endInput.value;
                body.action = currentAction;
            }

            apiCall('/api/account/security/out-of-hours', 'PUT', body)
                .then(function() {
                    showToast('success', 'Out-of-hours settings updated');
                    showSaveIndicator();
                })
                .catch(function(err) {
                    showToast('error', err.message);
                })
                .finally(function() {
                    savingFlags.outOfHours = false;
                });
        }

        toggle.addEventListener('change', function() {
            if (this.checked) {
                options.classList.remove('d-none');
            } else {
                options.classList.add('d-none');
            }
            saveOutOfHours();
        });

        actionSelector.querySelectorAll('.mode-option').forEach(function(opt) {
            opt.addEventListener('click', function() {
                setActiveAction(this.dataset.action);
                saveOutOfHours();
            });
        });

        startInput.addEventListener('change', saveOutOfHours);
        endInput.addEventListener('change', saveOutOfHours);
    }

    function populateIpAllowlist(data) {
        ipEntries = data.entries || [];
        ipLimit = data.limit || 50;
        var toggle = document.getElementById('ipAllowlistToggle');

        if (toggle) {
            toggle.checked = data.enabled;
            updateIpWarning(data.enabled);
        }

        updateIpCounter();
        renderIPList();
        bindIpToggle();
        bindAddIp();
        bindAddCurrentIp();
    }

    function updateIpCounter() {
        var counter = document.getElementById('ipCounter');
        if (counter) counter.textContent = ipEntries.length + ' / ' + ipLimit + ' IPs';
    }

    function updateIpWarning(enabled) {
        var warning = document.getElementById('ipAllowlistWarning');
        if (warning) {
            if (enabled) {
                warning.classList.remove('d-none');
            } else {
                warning.classList.add('d-none');
            }
        }
    }

    function renderIPList() {
        var tableBody = document.getElementById('ipAllowlistTableBody');
        var emptyState = document.getElementById('ipAllowlistEmpty');
        var table = document.getElementById('ipAllowlistTable');

        if (!tableBody) return;
        tableBody.innerHTML = '';

        if (ipEntries.length === 0) {
            if (table) table.classList.add('d-none');
            if (emptyState) emptyState.classList.remove('d-none');
            return;
        }

        if (table) table.classList.remove('d-none');
        if (emptyState) emptyState.classList.add('d-none');

        var cellStyle = 'padding: 0.5rem 0.35rem; font-size: 0.8rem; border-bottom: 1px solid #f1f3f5; vertical-align: middle;';

        ipEntries.forEach(function(item) {
            var row = document.createElement('tr');
            var statusBg = item.status === 'active' ? '#dcfce7' : '#fee2e2';
            var statusColor = item.status === 'active' ? '#166534' : '#991b1b';
            var createdDate = item.created_at ? new Date(item.created_at).toLocaleDateString('en-GB') : '-';

            row.innerHTML =
                '<td style="' + cellStyle + '">' + escapeHtml(item.label || '-') + '</td>' +
                '<td style="' + cellStyle + ' font-family: monospace;">' + escapeHtml(item.ip_address) + '</td>' +
                '<td style="' + cellStyle + '"><span class="badge" style="background: ' + statusBg + '; color: ' + statusColor + '; font-weight: 500;">' + escapeHtml(item.status) + '</span></td>' +
                '<td style="' + cellStyle + '">' + escapeHtml(createdDate) + '</td>' +
                '<td style="' + cellStyle + '">' +
                    '<button type="button" class="btn btn-sm p-1 remove-ip-btn" data-id="' + escapeHtml(item.id) + '" data-ip="' + escapeHtml(item.ip_address) + '" data-label="' + escapeHtml(item.label || '') + '" title="Remove" style="color: #6c757d;"><i class="fas fa-trash-alt"></i></button>' +
                '</td>';
            tableBody.appendChild(row);
        });

        tableBody.querySelectorAll('.remove-ip-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openRemoveIPModal(this.dataset.id, this.dataset.ip, this.dataset.label);
            });
        });

        updateIpCounter();
    }

    function openRemoveIPModal(id, ip, label) {
        document.getElementById('removeIPId').value = id;
        document.getElementById('removeIPDisplay').textContent = ip;
        document.getElementById('removeIPLabelDisplay').textContent = label ? '(' + label + ')' : '';

        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('removeIPModal'));
        modal.show();
    }

    function bindIpToggle() {
        var toggle = document.getElementById('ipAllowlistToggle');
        if (!toggle) return;

        toggle.addEventListener('change', function() {
            var self = this;
            var ipAllowlistError = document.getElementById('ipAllowlistError');

            if (self.checked) {
                if (ipEntries.length === 0) {
                    self.checked = false;
                    if (ipAllowlistError) ipAllowlistError.classList.remove('d-none');
                    return;
                }
                if (ipAllowlistError) ipAllowlistError.classList.add('d-none');

                self.checked = false;
                var confirmModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmIPAllowlistModal'));
                confirmModal.show();
            } else {
                apiCall('/api/account/security/ip-allowlist/toggle', 'PUT', { enabled: false })
                    .then(function() {
                        updateIpWarning(false);
                        showToast('success', 'IP allowlist disabled');
                        showSaveIndicator();
                    })
                    .catch(function(err) {
                        self.checked = true;
                        showToast('error', err.message);
                    });
            }
        });

        var confirmBtn = document.getElementById('confirmEnableIPAllowlist');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                setButtonLoading(this, true);
                var self = this;

                apiCall('/api/account/security/ip-allowlist/toggle', 'PUT', { enabled: true })
                    .then(function() {
                        var toggle = document.getElementById('ipAllowlistToggle');
                        if (toggle) toggle.checked = true;
                        updateIpWarning(true);
                        showToast('success', 'IP allowlist enabled');
                        showSaveIndicator();

                        return apiCall('/api/account/security/ip-allowlist');
                    })
                    .then(function(result) {
                        ipEntries = result.data.entries || [];
                        renderIPList();
                    })
                    .catch(function(err) {
                        showToast('error', err.message);
                    })
                    .finally(function() {
                        setButtonLoading(self, false);
                        var modal = bootstrap.Modal.getInstance(document.getElementById('confirmIPAllowlistModal'));
                        if (modal) modal.hide();
                    });
            });
        }

        var cancelBtn = document.getElementById('cancelIPAllowlist');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                var toggle = document.getElementById('ipAllowlistToggle');
                if (toggle) toggle.checked = false;
            });
        }
    }

    function bindAddIp() {
        var confirmBtn = document.getElementById('confirmAddIP');
        if (!confirmBtn) return;

        confirmBtn.addEventListener('click', function() {
            var ipInput = document.getElementById('newIPAddress');
            var labelInput = document.getElementById('newIPLabel');
            var errorEl = document.getElementById('newIPAddressError');
            var ip = ipInput.value.trim();
            var label = labelInput.value.trim() || null;

            if (!ip) {
                ipInput.classList.add('is-invalid');
                errorEl.textContent = 'Please enter an IP address';
                return;
            }
            ipInput.classList.remove('is-invalid');

            setButtonLoading(this, true);
            var self = this;

            apiCall('/api/account/security/ip-allowlist', 'POST', { ip_address: ip, label: label })
                .then(function(result) {
                    ipEntries.unshift(result.data);
                    renderIPList();

                    var ipAllowlistError = document.getElementById('ipAllowlistError');
                    if (ipAllowlistError) ipAllowlistError.classList.add('d-none');

                    showToast('success', 'IP address added');
                    showSaveIndicator();
                    ipInput.value = '';
                    labelInput.value = '';

                    var modal = bootstrap.Modal.getInstance(document.getElementById('addIPModal'));
                    if (modal) modal.hide();
                })
                .catch(function(err) {
                    ipInput.classList.add('is-invalid');
                    errorEl.textContent = err.message;
                })
                .finally(function() {
                    setButtonLoading(self, false);
                });
        });
    }

    function bindAddCurrentIp() {
        var btn = document.getElementById('addCurrentIPBtn');
        if (!btn) return;

        btn.addEventListener('click', function() {
            if (!currentIp) {
                showToast('error', 'Could not detect your current IP');
                return;
            }

            setButtonLoading(this, true);
            var self = this;

            apiCall('/api/account/security/ip-allowlist', 'POST', { ip_address: currentIp, label: 'My Current IP' })
                .then(function(result) {
                    ipEntries.unshift(result.data);
                    renderIPList();

                    var ipAllowlistError = document.getElementById('ipAllowlistError');
                    if (ipAllowlistError) ipAllowlistError.classList.add('d-none');

                    showToast('success', 'Current IP added');
                    showSaveIndicator();
                })
                .catch(function(err) {
                    showToast('error', err.message);
                })
                .finally(function() {
                    setButtonLoading(self, false);
                });
        });
    }

    var confirmRemoveIPBtn = document.getElementById('confirmRemoveIP');
    if (confirmRemoveIPBtn) {
        confirmRemoveIPBtn.addEventListener('click', function() {
            var id = document.getElementById('removeIPId').value;
            setButtonLoading(this, true);
            var self = this;

            apiCall('/api/account/security/ip-allowlist/' + id, 'DELETE')
                .then(function() {
                    ipEntries = ipEntries.filter(function(e) { return e.id !== id; });
                    renderIPList();
                    showToast('success', 'IP address removed');
                    showSaveIndicator();
                })
                .catch(function(err) {
                    showToast('error', err.message);
                })
                .finally(function() {
                    setButtonLoading(self, false);
                    var modal = bootstrap.Modal.getInstance(document.getElementById('removeIPModal'));
                    if (modal) modal.hide();
                });
        });
    }

    var mfaRequiredToggle = document.getElementById('mfaRequiredToggle');
    var mfaWarning = document.getElementById('mfaWarning');

    if (mfaRequiredToggle) {
        mfaRequiredToggle.checked = AccountPolicyService.isMfaRequired();
        if (mfaRequiredToggle.checked) { mfaWarning.classList.add('d-none'); } else { mfaWarning.classList.remove('d-none'); }

        mfaRequiredToggle.addEventListener('change', function() {
            AccountPolicyService.setMfaRequired(this.checked);
            if (this.checked) { mfaWarning.classList.add('d-none'); } else { mfaWarning.classList.remove('d-none'); }
            showSaveIndicator();
        });
    }

    var mfaMethodAuthenticator = document.getElementById('mfaMethodAuthenticator');
    var mfaMethodSmsRcs = document.getElementById('mfaMethodSmsRcs');
    var mfaMethodsWarning = document.getElementById('mfaMethodsWarning');

    function getEnabledMethods() {
        return {
            authenticator: mfaMethodAuthenticator ? mfaMethodAuthenticator.checked : false,
            sms_rcs: mfaMethodSmsRcs ? mfaMethodSmsRcs.checked : false
        };
    }

    function validateMfaMethods() {
        var methods = getEnabledMethods();
        var hasAtLeastOne = methods.authenticator || methods.sms_rcs;
        if (mfaMethodsWarning) {
            if (hasAtLeastOne) { mfaMethodsWarning.classList.add('d-none'); } else { mfaMethodsWarning.classList.remove('d-none'); }
        }
        return hasAtLeastOne;
    }

    function handleMfaMethodChange() {
        var oldMethods = AccountPolicyService.getMfaMethods();
        var newMethods = getEnabledMethods();

        if (!newMethods.authenticator && !newMethods.sms_rcs) {
            if (mfaMethodAuthenticator) mfaMethodAuthenticator.checked = oldMethods.authenticator;
            if (mfaMethodSmsRcs) mfaMethodSmsRcs.checked = oldMethods.sms_rcs;
            validateMfaMethods();
            return;
        }

        if (newMethods.authenticator !== oldMethods.authenticator) {
            AccountPolicyService.setMfaMethod('authenticator', newMethods.authenticator);
        }
        if (newMethods.sms_rcs !== oldMethods.sms_rcs) {
            AccountPolicyService.setMfaMethod('sms_rcs', newMethods.sms_rcs);
        }
        validateMfaMethods();
        showSaveIndicator();
    }

    if (mfaMethodAuthenticator) {
        mfaMethodAuthenticator.checked = AccountPolicyService.getMfaMethods().authenticator;
        mfaMethodAuthenticator.addEventListener('change', handleMfaMethodChange);
    }
    if (mfaMethodSmsRcs) {
        mfaMethodSmsRcs.checked = AccountPolicyService.getMfaMethods().sms_rcs;
        mfaMethodSmsRcs.addEventListener('change', handleMfaMethodChange);
    }
    validateMfaMethods();

    var confirmAddCountryBtn = document.getElementById('confirmAddCountry');
    if (confirmAddCountryBtn) {
        confirmAddCountryBtn.addEventListener('click', function() {
            var select = document.getElementById('newCountrySelect');
            var code = select.value;
            if (!code) {
                alert('Please select a country');
                return;
            }

            var selectedOption = select.options[select.selectedIndex];
            var name = selectedOption.textContent.replace(/\s*\(\+\d+\)\s*$/, '').trim();

            confirmAddCountryBtn.disabled = true;
            confirmAddCountryBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';

            fetch('/account/security/country-request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ country_code: code, country_name: name })
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.json().catch(function() { return { message: 'Request failed' }; }).then(function(err) {
                        throw new Error(err.message || 'HTTP ' + response.status);
                    });
                }
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    var countryList = document.getElementById('countryList');
                    if (countryList) {
                        var addBtn = countryList.querySelector('.add-country-btn');
                        var newPill = document.createElement('span');
                        newPill.className = 'country-pill pending';
                        newPill.innerHTML = '<span class="status-dot pending"></span>' + escapeHtml(name);
                        countryList.insertBefore(newPill, addBtn);
                    }
                    showToast('success', 'Country access request submitted for ' + name);
                } else {
                    showToast('error', data.message || 'Could not submit request');
                }
            })
            .catch(function(err) {
                showToast('error', err.message || 'Failed to submit country request');
            })
            .finally(function() {
                confirmAddCountryBtn.disabled = false;
                confirmAddCountryBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Request Country';
                select.value = '';
                var modal = bootstrap.Modal.getInstance(document.getElementById('addCountryModal'));
                if (modal) modal.hide();
            });
        });
    }
});
</script>
@endpush

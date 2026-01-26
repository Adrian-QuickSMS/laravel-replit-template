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
.ip-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
.ip-entry {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.75rem;
    background: #f9fafb;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
}
.ip-entry-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.ip-address {
    font-family: monospace;
    font-size: 0.85rem;
    color: #374151;
}
.ip-label {
    font-size: 0.75rem;
    color: #6b7280;
}
.ip-entry .remove-ip-btn {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.15s;
}
.ip-entry .remove-ip-btn:hover {
    color: #dc2626;
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
.audit-timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.audit-entry {
    display: flex;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
    position: relative;
}
.audit-entry:last-child {
    border-bottom: none;
}
.audit-entry-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 0.75rem;
}
.audit-entry-icon.security { background: rgba(111, 66, 193, 0.1); color: #886cc0; }
.audit-entry-icon.mfa { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.audit-entry-icon.ip { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.audit-entry-icon.retention { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.audit-entry-content {
    flex: 1;
}
.audit-entry-action {
    font-size: 0.8rem;
    color: #374151;
    font-weight: 500;
    margin-bottom: 0.125rem;
}
.audit-entry-details {
    font-size: 0.75rem;
    color: #6b7280;
}
.audit-entry-meta {
    text-align: right;
    flex-shrink: 0;
}
.audit-entry-time {
    font-size: 0.75rem;
    color: #9ca3af;
}
.audit-entry-user {
    font-size: 0.7rem;
    color: #6b7280;
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
</style>
@endpush

@section('content')
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
                    
                    <div class="warning-banner" id="mfaWarning" style="display: none;">
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
                        <div class="warning-banner" id="mfaMethodsWarning" style="display: none; margin-top: 0.75rem;">
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
                    <p class="setting-description mb-3">Control which data fields are visible in Message Logs, Reporting, and Exports. When disabled, data is masked or hidden from view.</p>
                    <div class="toggle-row">
                        <span class="toggle-label">Mobile Number</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="visibilityMobile">
                        </div>
                    </div>
                    <div class="toggle-row">
                        <span class="toggle-label">Message Content</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="visibilityContent">
                        </div>
                    </div>
                    <div class="toggle-row">
                        <span class="toggle-label">Date/Time Sent</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="visibilitySentTime">
                        </div>
                    </div>
                    <div class="toggle-row">
                        <span class="toggle-label">Date/Time Delivered</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="visibilityDeliveredTime">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-ban"></i>
                    <h6>Anti-Spam Protection</h6>
                </div>
                <div class="security-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable Anti-Spam Protection</div>
                            <div class="setting-description">Blocks sending identical message content to the same recipient within the specified time window. Applies to Portal, API, and Email-to-SMS.</div>
                            <div class="sub-setting" id="antiSpamWindowContainer" style="display: none;">
                                <label class="form-label" style="font-size: 0.8rem; color: #6b7280;">Protection Window</label>
                                <select class="form-select form-select-sm" id="antiSpamWindow" style="width: 120px;">
                                    <option value="2">2 hours</option>
                                    <option value="4">4 hours</option>
                                    <option value="12">12 hours</option>
                                    <option value="24">24 hours</option>
                                    <option value="48">48 hours</option>
                                </select>
                            </div>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="antiSpamToggle">
                            </div>
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
                            <div class="setting-description">When enabled, blocks all message sending between 21:00 and 08:00 (account timezone). Applies to Portal, API, and Email-to-SMS. Messages are rejected immediately, not queued.</div>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="outOfHoursToggle">
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
                        <span class="country-pill approved">
                            <span class="status-dot approved"></span>
                            United Kingdom
                        </span>
                        <span class="country-pill pending">
                            <span class="status-dot pending"></span>
                            Ireland
                            <button type="button" class="remove-btn" title="Remove"><i class="fas fa-times" style="font-size: 10px;"></i></button>
                        </span>
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
                                Your current IP: <strong id="currentIPDisplay">192.168.1.100</strong>
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
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">Created by</th>
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">Created date</th>
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">Status</th>
                                        <th style="padding: 0.5rem 0.35rem; font-size: 0.75rem; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e9ecef; width: 80px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="ipAllowlistTableBody">
                                    <!-- Entries rendered from service state -->
                                </tbody>
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
                    
                    <div class="warning-banner" id="ipAllowlistWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Ensure your current IP is in the allowlist before enabling, or you may lock yourself out.</span>
                    </div>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-header">
                    <i class="fas fa-history"></i>
                    <h6>Audit & Change History</h6>
                </div>
                <div class="security-card-body">
                    <p class="setting-description mb-3">Security-related changes to your account. This log is read-only and retained for 7 years for compliance purposes.</p>
                    
                    <div class="audit-timeline" id="auditTimeline">
                        <div class="audit-entry">
                            <div class="audit-entry-icon mfa">
                                <i class="fas fa-shield-alt" style="font-size: 0.7rem;"></i>
                            </div>
                            <div class="audit-entry-content">
                                <div class="audit-entry-action">MFA enabled for all users</div>
                                <div class="audit-entry-details">Previous: Disabled</div>
                            </div>
                            <div class="audit-entry-meta">
                                <div class="audit-entry-time">Jan 20, 16:45</div>
                                <div class="audit-entry-user">Sarah Mitchell</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-entry-icon ip">
                                <i class="fas fa-network-wired" style="font-size: 0.7rem;"></i>
                            </div>
                            <div class="audit-entry-content">
                                <div class="audit-entry-action">IP added to allowlist</div>
                                <div class="audit-entry-details">192.168.1.0/24 (Office Network)</div>
                            </div>
                            <div class="audit-entry-meta">
                                <div class="audit-entry-time">Jan 18, 10:22</div>
                                <div class="audit-entry-user">Sarah Mitchell</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-entry-icon retention">
                                <i class="fas fa-database" style="font-size: 0.7rem;"></i>
                            </div>
                            <div class="audit-entry-content">
                                <div class="audit-entry-action">Retention period changed</div>
                                <div class="audit-entry-details">30 days → 60 days</div>
                            </div>
                            <div class="audit-entry-meta">
                                <div class="audit-entry-time">Jan 10, 09:15</div>
                                <div class="audit-entry-user">Sarah Mitchell</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-entry-icon security">
                                <i class="fas fa-globe" style="font-size: 0.7rem;"></i>
                            </div>
                            <div class="audit-entry-content">
                                <div class="audit-entry-action">Country request submitted</div>
                                <div class="audit-entry-details">Ireland - Pending approval</div>
                            </div>
                            <div class="audit-entry-meta">
                                <div class="audit-entry-time">Jan 8, 11:40</div>
                                <div class="audit-entry-user">Sarah Mitchell</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="#" class="text-muted" style="font-size: 0.8rem;"><i class="fas fa-external-link-alt me-1"></i>View Full Audit Log</a>
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
                        All changes are logged in the Audit Trail for compliance purposes.
                    </p>
                </div>
            </div>
            
            <div class="security-card">
                <div class="security-card-body">
                    <h6 style="font-weight: 600; color: #374151; margin-bottom: 0.75rem;"><i class="fas fa-history me-2" style="color: #886cc0;"></i>Recent Changes</h6>
                    <div style="font-size: 0.8rem;">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">MFA enabled</span>
                            <span class="text-muted">Jan 15, 2026</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Retention set to 60 days</span>
                            <span class="text-muted">Jan 10, 2026</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Ireland country requested</span>
                            <span class="text-muted">Jan 8, 2026</span>
                        </div>
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
                    <select class="form-select" id="newCountrySelect">
                        <option value="">Choose a country...</option>
                        <option value="FR">France</option>
                        <option value="DE">Germany</option>
                        <option value="ES">Spain</option>
                        <option value="IT">Italy</option>
                        <option value="NL">Netherlands</option>
                        <option value="BE">Belgium</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="AU">Australia</option>
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
                    <div class="form-text">IPv4 address or CIDR range (/8 to /32)</div>
                    <div class="invalid-feedback" id="newIPAddressError">Please enter a valid IP address</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Label</label>
                    <input type="text" class="form-control" id="newIPLabel" placeholder="e.g., Office Network, VPN Gateway">
                </div>
                <div class="alert" style="background: rgba(111, 66, 193, 0.08); border: none; font-size: 0.8rem; color: #495057;">
                    <i class="fas fa-info-circle me-1" style="color: #886cc0;"></i>
                    Your current IP address is <strong id="currentUserIP">192.168.1.100</strong>. Ensure it is included in the allowlist.
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

<div class="modal fade" id="editIPModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2" style="color: #886cc0;"></i>Edit IP Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIPIndex">
                <div class="mb-3">
                    <label class="form-label">IP Address or CIDR Range <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editIPAddress" placeholder="e.g., 192.168.1.1 or 10.0.0.0/24">
                    <div class="form-text">IPv4 address or CIDR range (/8 to /32)</div>
                    <div class="invalid-feedback" id="editIPAddressError">Please enter a valid IP address</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Label</label>
                    <input type="text" class="form-control" id="editIPLabel" placeholder="e.g., Office Network, VPN Gateway">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmEditIP" style="background: #886cc0; color: white;">
                    <i class="fas fa-save me-1"></i>Save Changes
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
                <input type="hidden" id="removeIPIndex">
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
                    Make sure your current IP address is in the allowlist before proceeding.
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
    // Security Settings Service - wraps centralized AccountPolicyService for MFA/IP policy
    // Other settings remain local until backend integration
    var SecuritySettingsService = {
        settings: {
            // MFA and IP policies now use centralized AccountPolicyService
            get mfa_required() { return AccountPolicyService.isMfaRequired(); },
            set mfa_required(v) { AccountPolicyService.setMfaRequired(v); },
            get mfa_methods() { return AccountPolicyService.getMfaMethods(); },
            get ip_allowlist_enabled() { return AccountPolicyService.isIpAllowlistEnabled(); },
            set ip_allowlist_enabled(v) { AccountPolicyService.setIpAllowlistEnabled(v); },
            get ip_allowlist() { return AccountPolicyService.getIpAllowlist(); },
            set ip_allowlist(v) { /* handled by AccountPolicyService methods */ },
            current_ip: '192.168.1.100',
            retention_days: 60,
            visibility_mobile: false,
            visibility_content: true,
            visibility_sent_time: false,
            visibility_delivered_time: false,
            anti_spam_enabled: false,
            anti_spam_window: 4,
            out_of_hours_enabled: false,
            countries: [
                { code: 'GB', name: 'United Kingdom', status: 'approved' },
                { code: 'IE', name: 'Ireland', status: 'pending' }
            ]
        },
        auditLog: [
            { action: 'MFA enabled for all users', details: 'Previous: Disabled', user: 'Sarah Mitchell', time: 'Jan 20, 16:45', type: 'mfa' },
            { action: 'IP added to allowlist', details: '192.168.1.0/24 (Office Network)', user: 'Sarah Mitchell', time: 'Jan 18, 10:22', type: 'ip' },
            { action: 'Retention period changed', details: '30 days → 60 days', user: 'Sarah Mitchell', time: 'Jan 10, 09:15', type: 'retention' },
            { action: 'Country request submitted', details: 'Ireland - Pending approval', user: 'Sarah Mitchell', time: 'Jan 8, 11:40', type: 'security' }
        ],
        save: function(key, value) {
            // Route MFA/IP policy saves through centralized service
            if (key === 'mfa_required') {
                return Promise.resolve(AccountPolicyService.setMfaRequired(value));
            }
            if (key === 'ip_allowlist_enabled') {
                return Promise.resolve(AccountPolicyService.setIpAllowlistEnabled(value));
            }
            this.settings[key] = value;
            console.log('[SecuritySettingsService] Saved:', key, value);
            return Promise.resolve({ success: true });
        },
        addAuditEntry: function(action, details, type) {
            var entry = {
                action: action,
                details: details,
                user: 'Sarah Mitchell',
                time: new Date().toLocaleString('en-GB', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }),
                type: type || 'security'
            };
            this.auditLog.unshift(entry);
            console.log('[AUDIT]', entry);
            return entry;
        }
    };
    
    function showSaveIndicator() {
        var indicator = document.getElementById('saveIndicator');
        indicator.classList.add('show');
        setTimeout(function() {
            indicator.classList.remove('show');
        }, 2000);
    }
    
    function emitAuditEvent(action, details, type) {
        // TODO: Replace with actual API call
        // details is a structured object containing: actor, timestamp, source_ip, old_value, new_value
        SecuritySettingsService.addAuditEntry(action, details, type);
    }
    
    // MFA Required Toggle
    var mfaRequiredToggle = document.getElementById('mfaRequiredToggle');
    var mfaWarning = document.getElementById('mfaWarning');
    
    if (mfaRequiredToggle) {
        // Initialize from service state (default: ON)
        mfaRequiredToggle.checked = SecuritySettingsService.settings.mfa_required;
        mfaWarning.style.display = SecuritySettingsService.settings.mfa_required ? 'none' : 'flex';
        
        mfaRequiredToggle.addEventListener('change', function() {
            var oldValue = SecuritySettingsService.settings.mfa_required;
            var newValue = this.checked;
            
            SecuritySettingsService.save('mfa_required', newValue);
            
            // Show warning when MFA is disabled
            mfaWarning.style.display = newValue ? 'none' : 'flex';
            
            // Emit audit event: MFA_REQUIRED_CHANGED
            emitAuditEvent('MFA_REQUIRED_CHANGED', {
                actor: 'Sarah Mitchell', // TODO: Get from session
                timestamp: new Date().toISOString(),
                source_ip: '192.168.1.100', // TODO: Get from request
                old_value: oldValue,
                new_value: newValue
            }, 'mfa');
            showSaveIndicator();
        });
    }
    
    // MFA Allowed Methods
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
            mfaMethodsWarning.style.display = hasAtLeastOne ? 'none' : 'flex';
        }
        return hasAtLeastOne;
    }
    
    function handleMfaMethodChange() {
        var oldMethods = JSON.parse(JSON.stringify(SecuritySettingsService.settings.mfa_methods));
        var newMethods = getEnabledMethods();
        
        // Prevent unchecking if it would leave no methods enabled
        if (!newMethods.authenticator && !newMethods.sms_rcs) {
            // Revert the change
            if (mfaMethodAuthenticator) mfaMethodAuthenticator.checked = oldMethods.authenticator;
            if (mfaMethodSmsRcs) mfaMethodSmsRcs.checked = oldMethods.sms_rcs;
            validateMfaMethods();
            return;
        }
        
        SecuritySettingsService.save('mfa_methods', newMethods);
        validateMfaMethods();
        
        // Emit audit event: MFA_ALLOWED_METHODS_CHANGED
        emitAuditEvent('MFA_ALLOWED_METHODS_CHANGED', {
            actor: 'Sarah Mitchell', // TODO: Get from session
            timestamp: new Date().toISOString(),
            source_ip: '192.168.1.100', // TODO: Get from request
            old_value: oldMethods,
            new_value: newMethods
        }, 'mfa');
        showSaveIndicator();
    }
    
    // Initialize MFA methods from service state
    if (mfaMethodAuthenticator) {
        mfaMethodAuthenticator.checked = SecuritySettingsService.settings.mfa_methods.authenticator;
        mfaMethodAuthenticator.addEventListener('change', handleMfaMethodChange);
    }
    if (mfaMethodSmsRcs) {
        mfaMethodSmsRcs.checked = SecuritySettingsService.settings.mfa_methods.sms_rcs;
        mfaMethodSmsRcs.addEventListener('change', handleMfaMethodChange);
    }
    validateMfaMethods();
    
    // IP Allowlist Toggle
    var ipAllowlistToggle = document.getElementById('ipAllowlistToggle');
    var ipAllowlistContainer = document.getElementById('ipAllowlistContainer');
    var ipAllowlistWarning = document.getElementById('ipAllowlistWarning');
    var ipAllowlistEl = document.getElementById('ipAllowlist');
    
    // IP Validation Functions
    function validateIPv4(ip) {
        var parts = ip.split('.');
        if (parts.length !== 4) return false;
        for (var i = 0; i < 4; i++) {
            var num = parseInt(parts[i], 10);
            if (isNaN(num) || num < 0 || num > 255 || parts[i] !== String(num)) return false;
        }
        return true;
    }
    
    function validateIPEntry(ipStr, excludeIndex) {
        ipStr = ipStr.trim();
        
        // Check for 0.0.0.0/0 explicitly
        if (ipStr === '0.0.0.0/0') {
            return { valid: false, error: 'Cannot add 0.0.0.0/0 - this would allow all IPs' };
        }
        
        var hasCidr = ipStr.includes('/');
        var ip, prefix;
        
        if (hasCidr) {
            var parts = ipStr.split('/');
            if (parts.length !== 2) {
                return { valid: false, error: 'Invalid CIDR format' };
            }
            ip = parts[0];
            prefix = parseInt(parts[1], 10);
            
            // Validate prefix range /8 to /32
            if (isNaN(prefix) || prefix < 8 || prefix > 32) {
                return { valid: false, error: 'CIDR prefix must be between /8 and /32' };
            }
        } else {
            ip = ipStr;
        }
        
        // Validate IPv4
        if (!validateIPv4(ip)) {
            return { valid: false, error: 'Invalid IPv4 address' };
        }
        
        // Check for duplicates
        var isDuplicate = SecuritySettingsService.settings.ip_allowlist.some(function(item, idx) {
            if (excludeIndex !== undefined && idx === excludeIndex) return false;
            return item.ip === ipStr;
        });
        
        if (isDuplicate) {
            return { valid: false, error: 'This IP address already exists in the allowlist' };
        }
        
        return { valid: true };
    }
    
    // Render IP list as table from service state
    function renderIPList() {
        var tableBody = document.getElementById('ipAllowlistTableBody');
        var emptyState = document.getElementById('ipAllowlistEmpty');
        var table = document.getElementById('ipAllowlistTable');
        
        if (!tableBody) return;
        tableBody.innerHTML = '';
        
        var entries = SecuritySettingsService.settings.ip_allowlist;
        
        if (entries.length === 0) {
            if (table) table.classList.add('d-none');
            if (emptyState) emptyState.classList.remove('d-none');
            return;
        }
        
        if (table) table.classList.remove('d-none');
        if (emptyState) emptyState.classList.add('d-none');
        
        entries.forEach(function(item, index) {
            var row = document.createElement('tr');
            row.innerHTML = 
                '<td style="padding: 0.5rem 0.35rem; font-size: 0.8rem; border-bottom: 1px solid #f1f3f5;">' + (item.label || '-') + '</td>' +
                '<td style="padding: 0.5rem 0.35rem; font-size: 0.8rem; border-bottom: 1px solid #f1f3f5; font-family: monospace;">' + item.ip + '</td>' +
                '<td style="padding: 0.5rem 0.35rem; font-size: 0.8rem; border-bottom: 1px solid #f1f3f5;">' + (item.created_by || 'System') + '</td>' +
                '<td style="padding: 0.5rem 0.35rem; font-size: 0.8rem; border-bottom: 1px solid #f1f3f5;">' + (item.created_date || '-') + '</td>' +
                '<td style="padding: 0.5rem 0.35rem; font-size: 0.8rem; border-bottom: 1px solid #f1f3f5;"><span class="badge" style="background: #dcfce7; color: #166534; font-weight: 500;">Active</span></td>' +
                '<td style="padding: 0.5rem 0.35rem; font-size: 0.8rem; border-bottom: 1px solid #f1f3f5;">' +
                    '<button type="button" class="btn btn-sm p-1 edit-ip-btn" data-index="' + index + '" title="Edit" style="color: #6c757d;"><i class="fas fa-edit"></i></button>' +
                    '<button type="button" class="btn btn-sm p-1 remove-ip-btn" data-index="' + index + '" title="Remove" style="color: #6c757d;"><i class="fas fa-trash-alt"></i></button>' +
                '</td>';
            tableBody.appendChild(row);
        });
        
        // Bind edit/remove handlers
        tableBody.querySelectorAll('.edit-ip-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(this.dataset.index, 10);
                openEditIPModal(idx);
            });
        });
        
        tableBody.querySelectorAll('.remove-ip-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(this.dataset.index, 10);
                openRemoveIPModal(idx);
            });
        });
    }
    
    function openEditIPModal(index) {
        var ipList = AccountPolicyService.getIpAllowlist();
        var entry = ipList[index];
        if (!entry) return;
        
        document.getElementById('editIPIndex').value = index;
        document.getElementById('editIPAddress').value = entry.ip;
        document.getElementById('editIPLabel').value = entry.label || '';
        document.getElementById('editIPAddress').classList.remove('is-invalid');
        
        var modal = new bootstrap.Modal(document.getElementById('editIPModal'));
        modal.show();
    }
    
    function openRemoveIPModal(index) {
        var ipList = AccountPolicyService.getIpAllowlist();
        var entry = ipList[index];
        if (!entry) return;
        
        document.getElementById('removeIPIndex').value = index;
        document.getElementById('removeIPDisplay').textContent = entry.ip;
        document.getElementById('removeIPLabelDisplay').textContent = entry.label ? '(' + entry.label + ')' : '';
        
        var modal = new bootstrap.Modal(document.getElementById('removeIPModal'));
        modal.show();
    }
    
    function getCurrentDate() {
        var d = new Date();
        var day = String(d.getDate()).padStart(2, '0');
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var year = d.getFullYear();
        return day + '-' + month + '-' + year;
    }
    
    if (ipAllowlistToggle) {
        ipAllowlistToggle.checked = SecuritySettingsService.settings.ip_allowlist_enabled;
        if (ipAllowlistContainer) {
            ipAllowlistContainer.style.display = SecuritySettingsService.settings.ip_allowlist_enabled ? 'block' : 'none';
        }
        if (ipAllowlistWarning) {
            ipAllowlistWarning.style.display = SecuritySettingsService.settings.ip_allowlist_enabled ? 'flex' : 'none';
        }
        
        // Render initial IP list
        renderIPList();
        
        ipAllowlistToggle.addEventListener('change', function() {
            var toggle = this;
            var ipAllowlistError = document.getElementById('ipAllowlistError');
            
            if (toggle.checked) {
                // Hard guardrail: Cannot enable with 0 entries
                if (SecuritySettingsService.settings.ip_allowlist.length === 0) {
                    toggle.checked = false;
                    if (ipAllowlistError) {
                        ipAllowlistError.classList.remove('d-none');
                    }
                    return;
                }
                
                // Hide error if previously shown
                if (ipAllowlistError) {
                    ipAllowlistError.classList.add('d-none');
                }
                
                // Show confirmation modal before enabling
                toggle.checked = false; // Reset until confirmed
                var confirmModal = new bootstrap.Modal(document.getElementById('confirmIPAllowlistModal'));
                confirmModal.show();
            } else {
                // Disabling - no confirmation needed
                var oldValue = SecuritySettingsService.settings.ip_allowlist_enabled;
                SecuritySettingsService.save('ip_allowlist_enabled', false);
                if (ipAllowlistWarning) {
                    ipAllowlistWarning.style.display = 'none';
                }
                emitAuditEvent('IP_ALLOWLIST_ENABLED_CHANGED', { 
                    old_value: oldValue, 
                    new_value: false,
                    actor: 'Sarah Mitchell',
                    source_ip: '192.168.1.100'
                }, 'ip');
                showSaveIndicator();
            }
        });
    }
    
    // Confirm enable IP allowlist
    var confirmEnableIPAllowlistBtn = document.getElementById('confirmEnableIPAllowlist');
    if (confirmEnableIPAllowlistBtn) {
        confirmEnableIPAllowlistBtn.addEventListener('click', function() {
            var oldValue = SecuritySettingsService.settings.ip_allowlist_enabled;
            SecuritySettingsService.save('ip_allowlist_enabled', true);
            
            var ipAllowlistToggle = document.getElementById('ipAllowlistToggle');
            if (ipAllowlistToggle) {
                ipAllowlistToggle.checked = true;
            }
            
            var ipAllowlistWarning = document.getElementById('ipAllowlistWarning');
            if (ipAllowlistWarning) {
                ipAllowlistWarning.style.display = 'flex';
            }
            
            emitAuditEvent('IP_ALLOWLIST_ENABLED_CHANGED', { 
                old_value: oldValue, 
                new_value: true,
                actor: 'Sarah Mitchell',
                source_ip: '192.168.1.100'
            }, 'ip');
            showSaveIndicator();
            
            var modal = bootstrap.Modal.getInstance(document.getElementById('confirmIPAllowlistModal'));
            if (modal) modal.hide();
        });
    }
    
    // Cancel IP allowlist enable
    var cancelIPAllowlistBtn = document.getElementById('cancelIPAllowlist');
    if (cancelIPAllowlistBtn) {
        cancelIPAllowlistBtn.addEventListener('click', function() {
            var ipAllowlistToggle = document.getElementById('ipAllowlistToggle');
            if (ipAllowlistToggle) {
                ipAllowlistToggle.checked = false;
            }
        });
    
    // Add Current IP Button
    var addCurrentIPBtn = document.getElementById('addCurrentIPBtn');
    if (addCurrentIPBtn) {
        addCurrentIPBtn.addEventListener('click', function() {
            var currentIP = SecuritySettingsService.settings.current_ip;
            var validation = validateIPEntry(currentIP);
            
            if (!validation.valid) {
                alert(validation.error);
                return;
            }
            
            var newEntry = {
                ip: currentIP,
                label: 'My Current IP',
                created_by: 'Sarah Mitchell',
                created_date: getCurrentDate(),
                status: 'active'
            };
            
            AccountPolicyService.addIpEntry(newEntry);
            
            // Hide error message now that there's at least one IP
            var ipAllowlistError = document.getElementById('ipAllowlistError');
            if (ipAllowlistError) {
                ipAllowlistError.classList.add('d-none');
            }
            
            renderIPList();
            
            emitAuditEvent('IP_ALLOWLIST_ENTRY_ADDED', { 
                ip: currentIP, 
                label: newEntry.label,
                actor: 'Sarah Mitchell',
                timestamp: new Date().toISOString(),
                source_ip: currentIP
            }, 'ip');
            showSaveIndicator();
        });
    }
    
    // Add IP
    var confirmAddIPBtn = document.getElementById('confirmAddIP');
    if (confirmAddIPBtn) {
        confirmAddIPBtn.addEventListener('click', function() {
            var ipInput = document.getElementById('newIPAddress');
            var labelInput = document.getElementById('newIPLabel');
            var errorEl = document.getElementById('newIPAddressError');
            var ip = ipInput.value.trim();
            var label = labelInput.value.trim() || 'Custom';
            
            if (!ip) {
                ipInput.classList.add('is-invalid');
                errorEl.textContent = 'Please enter an IP address';
                return;
            }
            
            var validation = AccountPolicyService.validateIpEntry(ip);
            if (!validation.valid) {
                ipInput.classList.add('is-invalid');
                errorEl.textContent = validation.error;
                return;
            }
            
            ipInput.classList.remove('is-invalid');
            
            var newEntry = {
                ip: ip,
                label: label,
                created_by: 'Sarah Mitchell',
                created_date: getCurrentDate(),
                status: 'active'
            };
            
            AccountPolicyService.addIpEntry(newEntry);
            
            // Hide error message now that there's at least one IP
            var ipAllowlistError = document.getElementById('ipAllowlistError');
            if (ipAllowlistError) {
                ipAllowlistError.classList.add('d-none');
            }
            
            renderIPList();
            
            emitAuditEvent('IP_ALLOWLIST_ENTRY_ADDED', { 
                ip: ip, 
                label: label,
                actor: 'Sarah Mitchell',
                timestamp: new Date().toISOString(),
                source_ip: SecuritySettingsService.settings.current_ip
            }, 'ip');
            showSaveIndicator();
            
            ipInput.value = '';
            labelInput.value = '';
            var modal = bootstrap.Modal.getInstance(document.getElementById('addIPModal'));
            if (modal) modal.hide();
        });
    }
    
    // Edit IP
    var confirmEditIPBtn = document.getElementById('confirmEditIP');
    if (confirmEditIPBtn) {
        confirmEditIPBtn.addEventListener('click', function() {
            var indexInput = document.getElementById('editIPIndex');
            var ipInput = document.getElementById('editIPAddress');
            var labelInput = document.getElementById('editIPLabel');
            var errorEl = document.getElementById('editIPAddressError');
            var index = parseInt(indexInput.value, 10);
            var ip = ipInput.value.trim();
            var label = labelInput.value.trim() || 'Custom';
            
            if (!ip) {
                ipInput.classList.add('is-invalid');
                errorEl.textContent = 'Please enter an IP address';
                return;
            }
            
            var validation = validateIPEntry(ip, index);
            if (!validation.valid) {
                ipInput.classList.add('is-invalid');
                errorEl.textContent = validation.error;
                return;
            }
            
            ipInput.classList.remove('is-invalid');
            
            var ipList = AccountPolicyService.getIpAllowlist();
            var oldEntry = ipList[index];
            var oldIP = oldEntry.ip;
            var oldLabel = oldEntry.label;
            
            var updatedEntry = Object.assign({}, oldEntry, { ip: ip, label: label });
            AccountPolicyService.updateIpEntry(index, updatedEntry);
            
            renderIPList();
            
            emitAuditEvent('IP_ALLOWLIST_ENTRY_EDITED', { 
                old_ip: oldIP,
                old_label: oldLabel,
                new_ip: ip, 
                new_label: label,
                actor: 'Sarah Mitchell',
                timestamp: new Date().toISOString(),
                source_ip: SecuritySettingsService.settings.current_ip
            }, 'ip');
            showSaveIndicator();
            
            var modal = bootstrap.Modal.getInstance(document.getElementById('editIPModal'));
            if (modal) modal.hide();
        });
    }
    
    // Remove IP
    var confirmRemoveIPBtn = document.getElementById('confirmRemoveIP');
    if (confirmRemoveIPBtn) {
        confirmRemoveIPBtn.addEventListener('click', function() {
            var indexInput = document.getElementById('removeIPIndex');
            var index = parseInt(indexInput.value, 10);
            
            var ipList = AccountPolicyService.getIpAllowlist();
            var entry = ipList[index];
            var removedIP = entry.ip;
            var removedLabel = entry.label;
            
            AccountPolicyService.removeIpEntry(index);
            
            renderIPList();
            
            emitAuditEvent('IP_ALLOWLIST_ENTRY_REMOVED', { 
                ip: removedIP, 
                label: removedLabel,
                actor: 'Sarah Mitchell',
                timestamp: new Date().toISOString(),
                source_ip: SecuritySettingsService.settings.current_ip
            }, 'ip');
            showSaveIndicator();
            
            var modal = bootstrap.Modal.getInstance(document.getElementById('removeIPModal'));
            if (modal) modal.hide();
        });
    }
    
    // Retention Period
    var retentionSelect = document.getElementById('retentionPeriod');
    if (retentionSelect) {
        retentionSelect.value = SecuritySettingsService.settings.retention_days;
        retentionSelect.addEventListener('change', function() {
            var oldValue = SecuritySettingsService.settings.retention_days;
            SecuritySettingsService.save('retention_days', parseInt(this.value));
            emitAuditEvent('RETENTION_PERIOD_CHANGED', { old_value: oldValue, new_value: parseInt(this.value) }, 'retention');
            showSaveIndicator();
        });
    }
    
    // Data Visibility Toggles
    ['Mobile', 'Content', 'SentTime', 'DeliveredTime'].forEach(function(field) {
        var toggle = document.getElementById('visibility' + field);
        if (toggle) {
            var key = 'visibility_' + field.toLowerCase().replace('time', '_time');
            toggle.checked = SecuritySettingsService.settings[key] || false;
            toggle.addEventListener('change', function() {
                SecuritySettingsService.save(key, this.checked);
                emitAuditEvent('DATA_VISIBILITY_CHANGED', { field: field, visible: this.checked }, 'security');
                showSaveIndicator();
            });
        }
    });
    
    // Anti-Spam
    var antiSpamToggle = document.getElementById('antiSpamToggle');
    var antiSpamWindowContainer = document.getElementById('antiSpamWindowContainer');
    if (antiSpamToggle) {
        antiSpamToggle.checked = SecuritySettingsService.settings.anti_spam_enabled;
        if (antiSpamWindowContainer) {
            antiSpamWindowContainer.style.display = SecuritySettingsService.settings.anti_spam_enabled ? 'block' : 'none';
        }
        antiSpamToggle.addEventListener('change', function() {
            SecuritySettingsService.save('anti_spam_enabled', this.checked);
            if (antiSpamWindowContainer) {
                antiSpamWindowContainer.style.display = this.checked ? 'block' : 'none';
            }
            emitAuditEvent(this.checked ? 'ANTI_SPAM_ENABLED' : 'ANTI_SPAM_DISABLED', { enabled: this.checked }, 'security');
            showSaveIndicator();
        });
    }
    
    var antiSpamWindow = document.getElementById('antiSpamWindow');
    if (antiSpamWindow) {
        antiSpamWindow.value = SecuritySettingsService.settings.anti_spam_window;
        antiSpamWindow.addEventListener('change', function() {
            var oldValue = SecuritySettingsService.settings.anti_spam_window;
            SecuritySettingsService.save('anti_spam_window', parseInt(this.value));
            emitAuditEvent('ANTI_SPAM_WINDOW_CHANGED', { old_value: oldValue, new_value: parseInt(this.value) }, 'security');
            showSaveIndicator();
        });
    }
    
    // Out of Hours
    var outOfHoursToggle = document.getElementById('outOfHoursToggle');
    if (outOfHoursToggle) {
        outOfHoursToggle.checked = SecuritySettingsService.settings.out_of_hours_enabled;
        outOfHoursToggle.addEventListener('change', function() {
            SecuritySettingsService.save('out_of_hours_enabled', this.checked);
            emitAuditEvent(this.checked ? 'OUT_OF_HOURS_RESTRICTION_ENABLED' : 'OUT_OF_HOURS_RESTRICTION_DISABLED', { enabled: this.checked }, 'security');
            showSaveIndicator();
        });
    }
    
    // Country List
    var countryNames = {
        'FR': 'France', 'DE': 'Germany', 'ES': 'Spain', 'IT': 'Italy',
        'NL': 'Netherlands', 'BE': 'Belgium', 'US': 'United States',
        'CA': 'Canada', 'AU': 'Australia'
    };
    
    var confirmAddCountryBtn = document.getElementById('confirmAddCountry');
    if (confirmAddCountryBtn) {
        confirmAddCountryBtn.addEventListener('click', function() {
            var select = document.getElementById('newCountrySelect');
            var code = select.value;
            if (!code) {
                alert('Please select a country');
                return;
            }
            
            var name = countryNames[code];
            SecuritySettingsService.settings.countries.push({ code: code, name: name, status: 'pending' });
            
            var countryList = document.getElementById('countryList');
            if (countryList) {
                var addBtn = countryList.querySelector('.add-country-btn');
                var newPill = document.createElement('span');
                newPill.className = 'country-pill pending';
                newPill.innerHTML = '<span class="status-dot pending"></span>' + name +
                    '<button type="button" class="remove-btn" title="Remove"><i class="fas fa-times" style="font-size: 10px;"></i></button>';
                countryList.insertBefore(newPill, addBtn);
            }
            
            emitAuditEvent('COUNTRY_REQUESTED', { country: name }, 'security');
            showSaveIndicator();
            
            select.value = '';
            var modal = bootstrap.Modal.getInstance(document.getElementById('addCountryModal'));
            if (modal) modal.hide();
        });
    }
    
    var countryListEl = document.getElementById('countryList');
    if (countryListEl) {
        countryListEl.addEventListener('click', function(e) {
            if (e.target.closest('.remove-btn')) {
                var pill = e.target.closest('.country-pill');
                var countryName = pill.textContent.trim();
                pill.remove();
                
                SecuritySettingsService.settings.countries = SecuritySettingsService.settings.countries.filter(function(c) {
                    return c.name !== countryName;
                });
                
                emitAuditEvent('COUNTRY_REMOVED', { country: countryName }, 'security');
                showSaveIndicator();
            }
        });
    }
});
</script>
@endpush

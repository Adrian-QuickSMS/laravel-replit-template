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
.policy-select-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.policy-option {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.15s;
}
.policy-option:hover {
    border-color: #886cc0;
    background: #faf8ff;
}
.policy-option.selected {
    border-color: #886cc0;
    background: rgba(111, 66, 193, 0.08);
}
.policy-option input[type="radio"] {
    margin-right: 0.75rem;
    margin-top: 0.15rem;
    accent-color: #886cc0;
}
.policy-option-content {
    flex: 1;
}
.policy-option-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #374151;
    margin-bottom: 0.125rem;
}
.policy-option-desc {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1.4;
}
.method-toggles {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
.method-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0;
}
.method-toggle-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.method-toggle-info i {
    width: 20px;
    color: #886cc0;
    font-size: 0.9rem;
}
.method-toggle-label {
    font-size: 0.85rem;
    color: #374151;
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
                    <div class="setting-info mb-3">
                        <div class="setting-label">Enforcement Level</div>
                        <div class="setting-description">Choose how multi-factor authentication is enforced for all users in your account.</div>
                    </div>
                    
                    <div class="policy-select-group" id="mfaPolicyGroup">
                        <label class="policy-option" data-policy="disabled">
                            <input type="radio" name="mfaPolicy" value="disabled">
                            <div class="policy-option-content">
                                <div class="policy-option-label">Disabled</div>
                                <div class="policy-option-desc">MFA is not available for users. Not recommended for production accounts.</div>
                            </div>
                        </label>
                        <label class="policy-option" data-policy="optional">
                            <input type="radio" name="mfaPolicy" value="optional">
                            <div class="policy-option-content">
                                <div class="policy-option-label">Optional</div>
                                <div class="policy-option-desc">Users can choose to enable MFA but it is not required.</div>
                            </div>
                        </label>
                        <label class="policy-option" data-policy="recommended">
                            <input type="radio" name="mfaPolicy" value="recommended">
                            <div class="policy-option-content">
                                <div class="policy-option-label">Recommended</div>
                                <div class="policy-option-desc">Users are prompted to enable MFA at login but can skip.</div>
                            </div>
                        </label>
                        <label class="policy-option" data-policy="required">
                            <input type="radio" name="mfaPolicy" value="required">
                            <div class="policy-option-content">
                                <div class="policy-option-label">Required</div>
                                <div class="policy-option-desc">All users must configure MFA. Users without MFA cannot access the account.</div>
                            </div>
                        </label>
                        <label class="policy-option" data-policy="required_grace">
                            <input type="radio" name="mfaPolicy" value="required_grace">
                            <div class="policy-option-content">
                                <div class="policy-option-label">Required (7-Day Grace Period)</div>
                                <div class="policy-option-desc">MFA becomes mandatory after a 7-day grace period for new users.</div>
                            </div>
                        </label>
                    </div>
                    
                    <div class="warning-banner" id="mfaWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Disabling or making MFA optional significantly reduces account security. This is not recommended for production accounts.</span>
                    </div>
                    
                    <div class="method-toggles">
                        <div class="setting-label mb-2">Allowed MFA Methods</div>
                        <div class="setting-description mb-3">Select which authentication methods users can use. At least one method must be enabled.</div>
                        
                        <div class="method-toggle-row">
                            <div class="method-toggle-info">
                                <i class="fas fa-mobile-alt"></i>
                                <span class="method-toggle-label">Authenticator App (TOTP)</span>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="mfaMethodAuthenticator">
                            </div>
                        </div>
                        <div class="method-toggle-row">
                            <div class="method-toggle-info">
                                <i class="fas fa-sms"></i>
                                <span class="method-toggle-label">SMS One-Time Password</span>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="mfaMethodSMS">
                            </div>
                        </div>
                        <div class="method-toggle-row">
                            <div class="method-toggle-info">
                                <i class="fas fa-comment-dots"></i>
                                <span class="method-toggle-label">RCS Messaging</span>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="mfaMethodRCS">
                            </div>
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
                            <div class="setting-label">Enable IP Allowlist</div>
                            <div class="setting-description">When enabled, users can only log in from IP addresses in the allowlist. Use this to restrict access to specific office locations or VPNs.</div>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ipAllowlistToggle">
                            </div>
                        </div>
                    </div>
                    
                    <div id="ipAllowlistContainer" style="display: none;">
                        <div class="ip-list" id="ipAllowlist">
                            <!-- IP entries rendered from service state -->
                        </div>
                        <button type="button" class="add-ip-btn" data-bs-toggle="modal" data-bs-target="#addIPModal">
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
                                <div class="audit-entry-action">MFA Policy changed to Required</div>
                                <div class="audit-entry-details">Previous: Recommended</div>
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
                            <div class="audit-entry-icon mfa">
                                <i class="fas fa-mobile-alt" style="font-size: 0.7rem;"></i>
                            </div>
                            <div class="audit-entry-content">
                                <div class="audit-entry-action">SMS MFA method enabled</div>
                                <div class="audit-entry-details">Added to allowed methods</div>
                            </div>
                            <div class="audit-entry-meta">
                                <div class="audit-entry-time">Jan 15, 14:30</div>
                                <div class="audit-entry-user">John Thompson</div>
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
                    <label class="form-label">IP Address or CIDR Range</label>
                    <input type="text" class="form-control" id="newIPAddress" placeholder="e.g., 192.168.1.1 or 10.0.0.0/24">
                    <div class="form-text">Enter a single IP address or CIDR range (e.g., 192.168.1.0/24)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Label (Optional)</label>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mock Security Settings Service - TODO: Replace with actual API calls
    var SecuritySettingsService = {
        settings: {
            mfa_policy: 'required',
            mfa_methods: {
                authenticator: true,
                sms: true,
                rcs: false
            },
            ip_allowlist_enabled: false,
            ip_allowlist: [
                { ip: '192.168.1.0/24', label: 'Office Network' },
                { ip: '10.0.0.1', label: 'VPN Gateway' }
            ],
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
            { action: 'MFA Policy changed to Required', details: 'Previous: Recommended', user: 'Sarah Mitchell', time: 'Jan 20, 16:45', type: 'mfa' },
            { action: 'IP added to allowlist', details: '192.168.1.0/24 (Office Network)', user: 'Sarah Mitchell', time: 'Jan 18, 10:22', type: 'ip' },
            { action: 'SMS MFA method enabled', details: 'Added to allowed methods', user: 'John Thompson', time: 'Jan 15, 14:30', type: 'mfa' },
            { action: 'Retention period changed', details: '30 days → 60 days', user: 'Sarah Mitchell', time: 'Jan 10, 09:15', type: 'retention' },
            { action: 'Country request submitted', details: 'Ireland - Pending approval', user: 'Sarah Mitchell', time: 'Jan 8, 11:40', type: 'security' }
        ],
        save: function(key, value) {
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
        SecuritySettingsService.addAuditEntry(action, JSON.stringify(details), type);
    }
    
    // MFA Policy Selection
    var mfaPolicyGroup = document.getElementById('mfaPolicyGroup');
    var mfaWarning = document.getElementById('mfaWarning');
    
    if (mfaPolicyGroup) {
        var policyOptions = mfaPolicyGroup.querySelectorAll('.policy-option');
        var currentPolicy = SecuritySettingsService.settings.mfa_policy;
        
        // Initialize from service state
        policyOptions.forEach(function(option) {
            var policy = option.dataset.policy;
            var radio = option.querySelector('input[type="radio"]');
            if (policy === currentPolicy) {
                option.classList.add('selected');
                radio.checked = true;
            } else {
                option.classList.remove('selected');
                radio.checked = false;
            }
        });
        mfaWarning.style.display = (currentPolicy === 'disabled' || currentPolicy === 'optional') ? 'flex' : 'none';
        
        policyOptions.forEach(function(option) {
            option.addEventListener('click', function() {
                var policy = this.dataset.policy;
                var radio = this.querySelector('input[type="radio"]');
                
                // Update selection state
                policyOptions.forEach(function(opt) { opt.classList.remove('selected'); });
                this.classList.add('selected');
                radio.checked = true;
                
                // Show warning for weak policies
                mfaWarning.style.display = (policy === 'disabled' || policy === 'optional') ? 'flex' : 'none';
                
                SecuritySettingsService.save('mfa_policy', policy);
                emitAuditEvent('MFA_POLICY_CHANGED', { policy: policy }, 'mfa');
                showSaveIndicator();
            });
        });
    }
    
    // MFA Methods
    ['Authenticator', 'SMS', 'RCS'].forEach(function(method) {
        var toggle = document.getElementById('mfaMethod' + method);
        if (toggle) {
            var methodKey = method.toLowerCase();
            toggle.checked = SecuritySettingsService.settings.mfa_methods[methodKey] || false;
            
            toggle.addEventListener('change', function() {
                SecuritySettingsService.settings.mfa_methods[methodKey] = this.checked;
                
                // Ensure at least one method is enabled
                var methods = SecuritySettingsService.settings.mfa_methods;
                var enabledCount = Object.values(methods).filter(function(v) { return v; }).length;
                if (enabledCount === 0) {
                    this.checked = true;
                    methods[methodKey] = true;
                    alert('At least one MFA method must be enabled.');
                    return;
                }
                
                SecuritySettingsService.save('mfa_methods', methods);
                emitAuditEvent(this.checked ? method.toUpperCase() + '_MFA_ENABLED' : method.toUpperCase() + '_MFA_DISABLED', { method: method }, 'mfa');
                showSaveIndicator();
            });
        }
    });
    
    // IP Allowlist Toggle
    var ipAllowlistToggle = document.getElementById('ipAllowlistToggle');
    var ipAllowlistContainer = document.getElementById('ipAllowlistContainer');
    var ipAllowlistWarning = document.getElementById('ipAllowlistWarning');
    var ipAllowlistEl = document.getElementById('ipAllowlist');
    
    // Render IP list from service state
    function renderIPList() {
        if (!ipAllowlistEl) return;
        ipAllowlistEl.innerHTML = '';
        SecuritySettingsService.settings.ip_allowlist.forEach(function(item) {
            var entry = document.createElement('div');
            entry.className = 'ip-entry';
            entry.innerHTML = '<div class="ip-entry-info"><span class="ip-address">' + item.ip + '</span><span class="ip-label">' + item.label + '</span></div><button type="button" class="remove-ip-btn" title="Remove"><i class="fas fa-trash-alt"></i></button>';
            ipAllowlistEl.appendChild(entry);
        });
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
            SecuritySettingsService.save('ip_allowlist_enabled', this.checked);
            if (ipAllowlistContainer) {
                ipAllowlistContainer.style.display = this.checked ? 'block' : 'none';
            }
            if (ipAllowlistWarning) {
                ipAllowlistWarning.style.display = this.checked ? 'flex' : 'none';
            }
            emitAuditEvent(this.checked ? 'IP_ALLOWLIST_ENABLED' : 'IP_ALLOWLIST_DISABLED', { enabled: this.checked }, 'ip');
            showSaveIndicator();
        });
    }
    
    // Add IP
    var confirmAddIPBtn = document.getElementById('confirmAddIP');
    if (confirmAddIPBtn) {
        confirmAddIPBtn.addEventListener('click', function() {
            var ipInput = document.getElementById('newIPAddress');
            var labelInput = document.getElementById('newIPLabel');
            var ip = ipInput.value.trim();
            var label = labelInput.value.trim() || 'Custom';
            
            if (!ip) {
                alert('Please enter an IP address');
                return;
            }
            
            // Basic IP validation
            var ipRegex = /^(\d{1,3}\.){3}\d{1,3}(\/\d{1,2})?$/;
            if (!ipRegex.test(ip)) {
                alert('Please enter a valid IP address or CIDR range');
                return;
            }
            
            SecuritySettingsService.settings.ip_allowlist.push({ ip: ip, label: label });
            
            var ipList = document.getElementById('ipAllowlist');
            if (ipList) {
                var newEntry = document.createElement('div');
                newEntry.className = 'ip-entry';
                newEntry.innerHTML = '<div class="ip-entry-info"><span class="ip-address">' + ip + '</span><span class="ip-label">' + label + '</span></div><button type="button" class="remove-ip-btn" title="Remove"><i class="fas fa-trash-alt"></i></button>';
                ipList.appendChild(newEntry);
            }
            
            emitAuditEvent('IP_ADDED', { ip: ip, label: label }, 'ip');
            showSaveIndicator();
            
            ipInput.value = '';
            labelInput.value = '';
            var modal = bootstrap.Modal.getInstance(document.getElementById('addIPModal'));
            if (modal) modal.hide();
        });
    }
    
    // Remove IP (uses ipAllowlistEl declared earlier)
    if (ipAllowlistEl) {
        ipAllowlistEl.addEventListener('click', function(e) {
            if (e.target.closest('.remove-ip-btn')) {
                var entry = e.target.closest('.ip-entry');
                var ip = entry.querySelector('.ip-address').textContent;
                entry.remove();
                
                SecuritySettingsService.settings.ip_allowlist = SecuritySettingsService.settings.ip_allowlist.filter(function(item) {
                    return item.ip !== ip;
                });
                
                emitAuditEvent('IP_REMOVED', { ip: ip }, 'ip');
                showSaveIndicator();
            }
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

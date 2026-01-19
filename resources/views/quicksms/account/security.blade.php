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
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    margin-top: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #92400e;
}
.warning-banner i {
    color: #d97706;
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
    background: #fef3c7;
    color: #92400e;
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
.status-dot.pending { background: #f59e0b; }
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
                    <h6>Multi-Factor Authentication (MFA)</h6>
                </div>
                <div class="security-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Require MFA for All Users</div>
                            <div class="setting-description">When enabled, all users must complete multi-factor authentication at login. This significantly improves account security.</div>
                            <div class="warning-banner" id="mfaWarning" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Disabling MFA reduces account security and is not recommended.</span>
                            </div>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="mfaToggle" checked>
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
                                <option value="60" selected>60 days</option>
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
                            <input class="form-check-input" type="checkbox" id="visibilityContent" checked>
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
                                    <option value="4" selected>4 hours</option>
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
                <div class="alert" style="background: #f3e8ff; border: none; font-size: 0.8rem; color: #6b21a8;">
                    <i class="fas fa-info-circle me-1"></i>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var securitySettings = {
        mfa_enabled: true,
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
    };
    
    function showSaveIndicator() {
        var indicator = document.getElementById('saveIndicator');
        indicator.classList.add('show');
        setTimeout(function() {
            indicator.classList.remove('show');
        }, 2000);
    }
    
    function emitAuditEvent(action, details) {
        console.log('[AUDIT]', {
            action: action,
            category: 'security',
            severity: 'medium',
            actor: { userId: 'user-001', userName: 'Sarah Mitchell', role: 'admin' },
            details: details,
            timestamp: new Date().toISOString(),
            ip: '192.168.1.100'
        });
    }
    
    var mfaToggle = document.getElementById('mfaToggle');
    var mfaWarning = document.getElementById('mfaWarning');
    mfaToggle.addEventListener('change', function() {
        securitySettings.mfa_enabled = this.checked;
        mfaWarning.style.display = this.checked ? 'none' : 'flex';
        emitAuditEvent(this.checked ? 'MFA_ENABLED' : 'MFA_DISABLED', { enabled: this.checked });
        showSaveIndicator();
    });
    
    var retentionSelect = document.getElementById('retentionPeriod');
    retentionSelect.addEventListener('change', function() {
        var oldValue = securitySettings.retention_days;
        securitySettings.retention_days = parseInt(this.value);
        emitAuditEvent('RETENTION_PERIOD_CHANGED', { old_value: oldValue, new_value: securitySettings.retention_days });
        showSaveIndicator();
    });
    
    ['Mobile', 'Content', 'SentTime', 'DeliveredTime'].forEach(function(field) {
        var toggle = document.getElementById('visibility' + field);
        toggle.addEventListener('change', function() {
            var key = 'visibility_' + field.toLowerCase().replace('time', '_time');
            securitySettings[key] = this.checked;
            emitAuditEvent('DATA_VISIBILITY_CHANGED', { field: field, visible: this.checked });
            showSaveIndicator();
        });
    });
    
    var antiSpamToggle = document.getElementById('antiSpamToggle');
    var antiSpamWindowContainer = document.getElementById('antiSpamWindowContainer');
    antiSpamToggle.addEventListener('change', function() {
        securitySettings.anti_spam_enabled = this.checked;
        antiSpamWindowContainer.style.display = this.checked ? 'block' : 'none';
        emitAuditEvent(this.checked ? 'ANTI_SPAM_ENABLED' : 'ANTI_SPAM_DISABLED', { enabled: this.checked });
        showSaveIndicator();
    });
    
    var antiSpamWindow = document.getElementById('antiSpamWindow');
    antiSpamWindow.addEventListener('change', function() {
        var oldValue = securitySettings.anti_spam_window;
        securitySettings.anti_spam_window = parseInt(this.value);
        emitAuditEvent('ANTI_SPAM_WINDOW_CHANGED', { old_value: oldValue, new_value: securitySettings.anti_spam_window });
        showSaveIndicator();
    });
    
    var outOfHoursToggle = document.getElementById('outOfHoursToggle');
    outOfHoursToggle.addEventListener('change', function() {
        securitySettings.out_of_hours_enabled = this.checked;
        emitAuditEvent(this.checked ? 'OUT_OF_HOURS_RESTRICTION_ENABLED' : 'OUT_OF_HOURS_RESTRICTION_DISABLED', { enabled: this.checked });
        showSaveIndicator();
    });
    
    var countryNames = {
        'FR': 'France', 'DE': 'Germany', 'ES': 'Spain', 'IT': 'Italy',
        'NL': 'Netherlands', 'BE': 'Belgium', 'US': 'United States',
        'CA': 'Canada', 'AU': 'Australia'
    };
    
    document.getElementById('confirmAddCountry').addEventListener('click', function() {
        var select = document.getElementById('newCountrySelect');
        var code = select.value;
        if (!code) {
            alert('Please select a country');
            return;
        }
        
        var name = countryNames[code];
        securitySettings.countries.push({ code: code, name: name, status: 'pending' });
        
        var countryList = document.getElementById('countryList');
        var addBtn = countryList.querySelector('.add-country-btn');
        var newPill = document.createElement('span');
        newPill.className = 'country-pill pending';
        newPill.innerHTML = '<span class="status-dot pending"></span>' + name +
            '<button type="button" class="remove-btn" title="Remove"><i class="fas fa-times" style="font-size: 10px;"></i></button>';
        countryList.insertBefore(newPill, addBtn);
        
        emitAuditEvent('COUNTRY_LIST_UPDATED', { country: name, action: 'requested' });
        showSaveIndicator();
        
        select.value = '';
        bootstrap.Modal.getInstance(document.getElementById('addCountryModal')).hide();
    });
    
    document.getElementById('countryList').addEventListener('click', function(e) {
        if (e.target.closest('.remove-btn')) {
            var pill = e.target.closest('.country-pill');
            var countryName = pill.textContent.trim();
            pill.remove();
            emitAuditEvent('COUNTRY_LIST_UPDATED', { country: countryName, action: 'removed' });
            showSaveIndicator();
        }
    });
});
</script>
@endpush

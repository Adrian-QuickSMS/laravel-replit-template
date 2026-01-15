@extends('layouts.quicksms')

@section('title', 'Configure Numbers')

@push('styles')
<style>
.selection-summary {
    background: linear-gradient(135deg, rgba(136, 108, 192, 0.08) 0%, rgba(111, 66, 193, 0.12) 100%);
    border: 1px solid rgba(136, 108, 192, 0.25);
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
}
.selection-summary .count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: var(--primary);
    color: #fff;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.85rem;
    margin-right: 0.75rem;
}
.selected-numbers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
.selected-number-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 2rem;
    font-size: 0.8rem;
    font-weight: 500;
}
.selected-number-chip .chip-remove {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.5;
    font-size: 0.7rem;
}
.selected-number-chip .chip-remove:hover {
    opacity: 1;
    color: #dc3545;
}
.mode-warning-banner {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
}
.mode-warning-banner strong {
    color: #333 !important;
}
.mode-warning-banner .fa-exclamation-triangle {
    color: var(--primary) !important;
}
.config-section {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}
.config-section-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0.5rem 0.5rem 0 0;
}
.config-section-header h6 {
    margin: 0;
    font-weight: 600;
    color: #333;
}
.config-section-body {
    padding: 1.25rem;
}
.mode-selector-card {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
}
.mode-selector-card:hover {
    border-color: var(--primary);
    background: rgba(136, 108, 192, 0.03);
}
.mode-selector-card.active {
    border-color: var(--primary);
    background: rgba(136, 108, 192, 0.08);
    box-shadow: 0 0 0 3px rgba(136, 108, 192, 0.15);
}
.mode-selector-card .mode-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
}
.mode-selector-card .mode-icon i {
    font-size: 1.25rem;
    color: var(--primary);
}
.mode-selector-card.active .mode-icon {
    background: var(--primary);
}
.mode-selector-card.active .mode-icon i {
    color: #fff;
}
.mode-features-list {
    list-style: none;
    padding: 0;
    margin: 0.75rem 0 0;
    font-size: 0.8rem;
}
.mode-features-list li {
    padding: 0.25rem 0;
}
.mode-features-list li i {
    width: 16px;
    margin-right: 0.5rem;
}
.capability-toggle-card {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
}
.capability-toggle-card:hover {
    background: #f8f9fa;
}
.capability-toggle-card.disabled {
    opacity: 0.5;
    pointer-events: none;
}
.subaccount-select-list {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    max-height: 200px;
    overflow-y: auto;
}
.subaccount-select-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f3f5;
    display: flex;
    align-items: center;
}
.subaccount-select-item:last-child {
    border-bottom: none;
}
.subaccount-select-item:hover {
    background: #f8f9fa;
}
.defaults-grid {
    display: grid;
    gap: 0.75rem;
}
.default-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}
.default-option.is-default {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.3);
}
.api-webhook-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}
.action-buttons-bar {
    background: #fff;
    border-top: 1px solid #e9ecef;
    padding: 1rem 0;
    position: sticky;
    bottom: 0;
}
.form-switch .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
    cursor: pointer;
}
.form-switch .form-check-input:checked {
    background-color: #886CC0;
    border-color: #886CC0;
}
.api-restrictions-box {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 0.5rem;
    padding: 1rem;
}
.mode-selector-card h6 {
    color: #333;
}
.mode-selector-card .mode-features-list li.text-success {
    color: #6c757d !important;
}
.mode-selector-card .mode-features-list li.text-success i {
    color: var(--primary) !important;
}
.mode-selector-card .mode-features-list li.text-danger {
    color: #6c757d !important;
}
.mode-selector-card .mode-features-list li.text-danger i {
    color: #adb5bd !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management.numbers') }}">Numbers</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Configure</a></li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="selection-summary mb-4" id="selectionSummary">
                <div class="d-flex align-items-center">
                    <span class="count-badge" id="selectedCountBadge">0</span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Selected Numbers</h6>
                        <small class="text-muted">Configure settings for the selected numbers below</small>
                    </div>
                    <a href="{{ route('management.numbers') }}" class="btn btn-outline-secondary btn-sm ms-auto">
                        <i class="fas fa-arrow-left me-1"></i> Back to Library
                    </a>
                </div>
                <div class="selected-numbers-list" id="selectedNumbersList">
                </div>
            </div>
            
            <div class="mode-warning-banner" id="mixedModeWarning" style="display: none;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle me-3 mt-1" style="color: var(--primary);"></i>
                    <div>
                        <strong style="color: #333;">Mixed Mode Warning</strong>
                        <p class="mb-0 small mt-1" style="color: #6c757d;">The selected numbers have different operating modes. Some configuration options may not apply to all numbers.</p>
                    </div>
                </div>
            </div>
            
            <div id="noSelectionState" style="display: none;">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-phone-alt fa-3x text-muted"></i>
                        </div>
                        <h5>No Numbers Selected</h5>
                        <p class="text-muted mb-4">Please go back to the Numbers Library and select one or more numbers to configure.</p>
                        <a href="{{ route('management.numbers') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i> Go to Numbers Library
                        </a>
                    </div>
                </div>
            </div>
            
            <div id="configurationPanel">
                <div class="config-section">
                    <div class="config-section-header">
                        <h6><i class="fas fa-exchange-alt me-2 text-primary"></i>Operating Mode</h6>
                    </div>
                    <div class="config-section-body">
                        <p class="text-muted small mb-3">Each number must operate in exactly one mode. Switching mode will affect feature availability across all modules.</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mode-selector-card" id="modeCardPortal" data-mode="portal">
                                    <div class="mode-icon">
                                        <i class="fas fa-desktop"></i>
                                    </div>
                                    <h6 class="mb-1 fw-semibold">Portal Mode</h6>
                                    <small class="text-muted">For campaigns, inbox, and opt-out handling</small>
                                    <ul class="mode-features-list">
                                        <li class="text-success"><i class="fas fa-check"></i>Campaign Composer access</li>
                                        <li class="text-success"><i class="fas fa-check"></i>Inbox visibility</li>
                                        <li class="text-success"><i class="fas fa-check"></i>Opt-out management</li>
                                        <li class="text-danger"><i class="fas fa-times"></i>Not available via API</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mode-selector-card" id="modeCardAPI" data-mode="api">
                                    <div class="mode-icon">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <h6 class="mb-1 fw-semibold">API Mode</h6>
                                    <small class="text-muted">For REST API integration only</small>
                                    <ul class="mode-features-list">
                                        <li class="text-success"><i class="fas fa-check"></i>REST API access</li>
                                        <li class="text-success"><i class="fas fa-check"></i>Webhook forwarding</li>
                                        <li class="text-danger"><i class="fas fa-times"></i>Limited Portal Features</li>
                                        <li class="text-danger"><i class="fas fa-times"></i>Not visible in campaigns</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="config-section" id="portalConfigSection" style="display: none;">
                    <div class="config-section-header">
                        <h6><i class="fas fa-cogs me-2 text-primary"></i>Portal Configuration</h6>
                    </div>
                    <div class="config-section-body">
                        <div id="portalShortcodeNotice" class="alert alert-info small mb-4" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Shortcode Keyword:</strong> This number type can only be used for opt-out handling. SenderID and Inbox options are not available.
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <label class="form-label fw-bold">Sub-Account Assignment</label>
                                <p class="text-muted small mb-2">Select which sub-accounts can use this number.</p>
                                <div class="subaccount-select-list">
                                    <div class="subaccount-select-item">
                                        <input class="form-check-input me-2" type="checkbox" id="subAccMain" value="Main Account">
                                        <label class="form-check-label" for="subAccMain">Main Account</label>
                                    </div>
                                    <div class="subaccount-select-item">
                                        <input class="form-check-input me-2" type="checkbox" id="subAccMarketing" value="Marketing">
                                        <label class="form-check-label" for="subAccMarketing">Marketing</label>
                                    </div>
                                    <div class="subaccount-select-item">
                                        <input class="form-check-input me-2" type="checkbox" id="subAccSupport" value="Support">
                                        <label class="form-check-label" for="subAccSupport">Support</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 mb-4">
                                <label class="form-label fw-bold">Portal Capabilities</label>
                                <p class="text-muted small mb-2">Enable or disable features for this number.</p>
                                
                                <div class="capability-toggle-card" id="capSenderIDCard">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small">Allow as SenderID</div>
                                            <div class="text-muted small">Selectable in Campaign Builder</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="toggleSenderID" checked>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="capability-toggle-card" id="capInboxCard">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small">Enable Inbox Replies</div>
                                            <div class="text-muted small">Replies appear in Inbox</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="toggleInbox" checked>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="capability-toggle-card" id="capOptoutCard">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small">Enable Opt-out Handling</div>
                                            <div class="text-muted small">STOP messages update opt-out lists</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="toggleOptout" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="defaultsSection">
                            <label class="form-label fw-bold">Defaults Per Sub-Account</label>
                            <p class="text-muted small mb-3">Set this number as default for specific functions. Only one default per capability per sub-account.</p>
                            
                            <div id="subAccountDefaultsContainer">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="config-section" id="apiConfigSection" style="display: none;">
                    <div class="config-section-header">
                        <h6><i class="fas fa-plug me-2 text-primary"></i>API Configuration</h6>
                    </div>
                    <div class="config-section-body">
                        <div id="apiShortcodeNotice" class="alert small mb-4" style="display: none; background: rgba(136, 108, 192, 0.1); border: 1px solid rgba(136, 108, 192, 0.3); color: #333;">
                            <i class="fas fa-info-circle me-2" style="color: var(--primary);"></i>
                            <strong>Shortcode Keyword:</strong> This number can only be used for opt-out keywords or API inbound triggers.
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <label class="form-label fw-bold">Sub-Account Assignment</label>
                                <p class="text-muted small mb-2">Select which sub-accounts can use this number.</p>
                                
                                <select class="selectpicker form-control" id="apiSubAccountSelect" multiple data-live-search="true" data-actions-box="true" data-selected-text-format="count > 2" title="Select sub-accounts...">
                                    <option value="main">Main Account</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="support">Support</option>
                                    <option value="sales">Sales</option>
                                    <option value="operations">Operations</option>
                                    <option value="finance">Finance</option>
                                    <option value="hr">Human Resources</option>
                                    <option value="it">IT Department</option>
                                    <option value="legal">Legal</option>
                                    <option value="customer-success">Customer Success</option>
                                </select>
                                
                                <div class="mt-3">
                                    <label class="form-label fw-bold">User Assignment <span class="badge badge-pastel-pink ms-1">Optional</span></label>
                                    <p class="text-muted small mb-2">Optionally limit to specific users within selected sub-accounts.</p>
                                    
                                    <select class="selectpicker form-control" id="apiUserSelect" multiple data-live-search="true" data-actions-box="true" data-selected-text-format="count > 2" title="All users (default)" disabled>
                                    </select>
                                    <small class="text-muted d-block mt-1">Leave empty to allow all users in selected sub-accounts.</small>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 mb-4">
                                <label class="form-label fw-bold">API Capabilities</label>
                                <p class="text-muted small mb-2">Configure features available for this API number.</p>
                                
                                <div class="capability-toggle-card" id="apiCapSenderIDCard">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small">Allow as SenderID in Send Message</div>
                                            <div class="text-muted small">Selectable when composing messages</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="toggleApiSenderID">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="capability-toggle-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small">Enable Inbound Forwarding</div>
                                            <div class="text-muted small">Forward messages to webhook</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="toggleInboundForwarding">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="api-webhook-section" id="webhookSection" style="display: none;">
                                    <label class="form-label small fw-medium">Inbound Webhook URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                                        <input type="url" class="form-control" id="apiInboundUrl" placeholder="https://your-domain.com/webhook/inbound">
                                    </div>
                                    <div class="form-text small">
                                        <i class="fas fa-lock me-1 text-success"></i>HTTPS URLs only
                                    </div>
                                    <div id="webhookUrlError" class="text-danger small mt-1" style="display: none;">
                                        <i class="fas fa-exclamation-circle me-1"></i>URL must start with https://
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="api-restrictions-box small mb-0">
                            <label class="form-label fw-bold small mb-2" style="color: #333;">API Mode Restrictions</label>
                            <ul class="mb-0 ps-3" style="color: #6c757d;">
                                <li><i class="fas fa-times me-1" style="color: var(--primary);"></i>Cannot receive messages in Inbox</li>
                                <li><i class="fas fa-times me-1" style="color: var(--primary);"></i>Cannot be used in Campaign Builder</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons-bar">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('management.numbers') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" id="btnResetConfig">
                                <i class="fas fa-undo me-1"></i> Reset Changes
                            </button>
                            <button type="button" class="btn btn-primary" id="btnSaveConfig">
                                <i class="fas fa-save me-1"></i> Save Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModeChangeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2" style="color: var(--primary);"></i>Confirm Mode Change
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert mb-3" style="background: rgba(136, 108, 192, 0.1); border: 1px solid rgba(136, 108, 192, 0.3); color: #333;">
                    <i class="fas fa-info-circle me-2" style="color: var(--primary);"></i>
                    Changing the operating mode will immediately affect how this number can be used.
                </div>
                <p class="mb-0">Are you sure you want to switch to <strong id="newModeLabel">API Mode</strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnConfirmModeChange">
                    <i class="fas fa-exchange-alt me-1"></i> Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="saveSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-3x text-success"></i>
                </div>
                <h5>Configuration Saved</h5>
                <p class="text-muted small mb-0">Changes have been applied successfully.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="{{ route('management.numbers') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Library
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var numbersData = [
        { id: 1, number: '+447700900123', type: 'vmn', mode: 'portal', status: 'active', subAccounts: ['Main Account'] },
        { id: 2, number: '+447700900456', type: 'vmn', mode: 'api', status: 'active', subAccounts: ['Main Account'] },
        { id: 3, number: '88600', type: 'dedicated_shortcode', mode: 'portal', status: 'active', subAccounts: ['Main Account', 'Marketing', 'Support'] },
        { id: 4, number: 'OFFER on 88000', type: 'shortcode_keyword', mode: 'portal', status: 'active', subAccounts: ['Marketing'] },
        { id: 5, number: '+14155551234', type: 'vmn', mode: 'api', status: 'suspended', subAccounts: ['Main Account'] },
        { id: 6, number: '+447700900789', type: 'vmn', mode: 'portal', status: 'pending', subAccounts: ['Support'] },
        { id: 7, number: 'INFO on 88000', type: 'shortcode_keyword', mode: 'portal', status: 'active', subAccounts: ['Main Account'] }
    ];
    
    var selectedIds = '{{ $selectedIds }}'.split(',').filter(function(id) { return id.trim() !== ''; }).map(function(id) { return parseInt(id); });
    var selectedNumbers = numbersData.filter(function(n) { return selectedIds.includes(n.id); });
    
    var currentMode = null;
    var pendingModeChange = null;
    
    function init() {
        if (selectedNumbers.length === 0) {
            $('#noSelectionState').show();
            $('#configurationPanel').hide();
            $('#selectionSummary').hide();
            return;
        }
        
        $('#noSelectionState').hide();
        $('#configurationPanel').show();
        $('#selectionSummary').show();
        
        renderSelectedNumbers();
        checkMixedModes();
        setInitialMode();
        checkShortcodeKeywords();
    }
    
    function renderSelectedNumbers() {
        $('#selectedCountBadge').text(selectedNumbers.length);
        
        var html = '';
        selectedNumbers.forEach(function(num) {
            html += '<span class="selected-number-chip">';
            html += '<span>' + num.number + '</span>';
            html += '<span class="chip-remove" data-id="' + num.id + '"><i class="fas fa-times"></i></span>';
            html += '</span>';
        });
        $('#selectedNumbersList').html(html);
    }
    
    function checkMixedModes() {
        var modes = [...new Set(selectedNumbers.map(function(n) { return n.mode; }))];
        if (modes.length > 1) {
            $('#mixedModeWarning').show();
        } else {
            $('#mixedModeWarning').hide();
        }
    }
    
    function setInitialMode() {
        if (selectedNumbers.length === 0) return;
        
        var firstMode = selectedNumbers[0].mode;
        currentMode = firstMode;
        
        if (firstMode === 'portal') {
            selectMode('portal', false);
        } else {
            selectMode('api', false);
        }
    }
    
    function checkShortcodeKeywords() {
        var hasShortcodeKeyword = selectedNumbers.some(function(n) { return n.type === 'shortcode_keyword'; });
        
        if (hasShortcodeKeyword) {
            if (currentMode === 'portal') {
                $('#portalShortcodeNotice').show();
                $('#capSenderIDCard').hide();
                $('#capInboxCard').hide();
            } else {
                $('#apiShortcodeNotice').show();
            }
        } else {
            $('#portalShortcodeNotice').hide();
            $('#apiShortcodeNotice').hide();
            $('#capSenderIDCard').show();
            $('#capInboxCard').show();
        }
    }
    
    function selectMode(mode, showConfirmation) {
        if (showConfirmation && mode !== currentMode) {
            pendingModeChange = mode;
            $('#newModeLabel').text(mode === 'portal' ? 'Portal Mode' : 'API Mode');
            $('#confirmModeChangeModal').modal('show');
            return;
        }
        
        currentMode = mode;
        
        $('.mode-selector-card').removeClass('active');
        $('#modeCard' + (mode === 'portal' ? 'Portal' : 'API')).addClass('active');
        
        if (mode === 'portal') {
            $('#portalConfigSection').show();
            $('#apiConfigSection').hide();
        } else {
            $('#portalConfigSection').hide();
            $('#apiConfigSection').show();
        }
        
        checkShortcodeKeywords();
        updateSubAccountDefaults();
    }
    
    function updateSubAccountDefaults() {
        var checkedSubAccounts = [];
        $('.subaccount-select-list input:checked').each(function() {
            checkedSubAccounts.push($(this).val());
        });
        
        if (checkedSubAccounts.length === 0) {
            $('#subAccountDefaultsContainer').html('<p class="text-muted small">Select sub-accounts above to configure defaults.</p>');
            return;
        }
        
        var hasShortcodeKeyword = selectedNumbers.some(function(n) { return n.type === 'shortcode_keyword'; });
        
        var html = '';
        checkedSubAccounts.forEach(function(sa) {
            html += '<div class="card mb-2">';
            html += '<div class="card-body p-3">';
            html += '<h6 class="small fw-bold mb-2">' + sa + '</h6>';
            html += '<div class="defaults-grid">';
            
            if (!hasShortcodeKeyword) {
                html += '<div class="default-option">';
                html += '<span class="small">Default Sender Number</span>';
                html += '<div class="form-check form-switch mb-0">';
                html += '<input class="form-check-input default-toggle" type="checkbox" data-sa="' + sa + '" data-type="sender">';
                html += '</div>';
                html += '</div>';
                
                html += '<div class="default-option">';
                html += '<span class="small">Default Inbox Number</span>';
                html += '<div class="form-check form-switch mb-0">';
                html += '<input class="form-check-input default-toggle" type="checkbox" data-sa="' + sa + '" data-type="inbox">';
                html += '</div>';
                html += '</div>';
            }
            
            html += '<div class="default-option">';
            html += '<span class="small">Default Opt-out Number</span>';
            html += '<div class="form-check form-switch mb-0">';
            html += '<input class="form-check-input default-toggle" type="checkbox" data-sa="' + sa + '" data-type="optout">';
            html += '</div>';
            html += '</div>';
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });
        
        $('#subAccountDefaultsContainer').html(html);
    }
    
    $('.mode-selector-card').on('click', function() {
        var mode = $(this).data('mode');
        selectMode(mode, true);
    });
    
    $('#btnConfirmModeChange').on('click', function() {
        $('#confirmModeChangeModal').modal('hide');
        if (pendingModeChange) {
            selectMode(pendingModeChange, false);
            pendingModeChange = null;
        }
    });
    
    $('.subaccount-select-list input').on('change', function() {
        updateSubAccountDefaults();
    });
    
    $('#toggleInboundForwarding').on('change', function() {
        if ($(this).is(':checked')) {
            $('#webhookSection').slideDown();
        } else {
            $('#webhookSection').slideUp();
        }
    });
    
    $('#apiInboundUrl').on('input', function() {
        var url = $(this).val().trim();
        if (url && !url.startsWith('https://')) {
            $('#webhookUrlError').show();
        } else {
            $('#webhookUrlError').hide();
        }
    });
    
    $(document).on('click', '.chip-remove', function() {
        var id = $(this).data('id');
        selectedNumbers = selectedNumbers.filter(function(n) { return n.id !== id; });
        selectedIds = selectedIds.filter(function(i) { return i !== id; });
        
        if (selectedNumbers.length === 0) {
            window.location.href = '{{ route("management.numbers") }}';
            return;
        }
        
        renderSelectedNumbers();
        checkMixedModes();
        checkShortcodeKeywords();
    });
    
    $('#btnResetConfig').on('click', function() {
        setInitialMode();
        $('.subaccount-select-list input').prop('checked', false);
        $('#toggleSenderID, #toggleInbox, #toggleOptout').prop('checked', true);
        $('#toggleInboundForwarding').prop('checked', false);
        $('#toggleApiSenderID').prop('checked', false);
        $('#webhookSection').hide();
        $('#apiInboundUrl').val('');
        $('#apiSubAccountSelect').val([]).selectpicker('refresh');
        $('#apiUserSelect').val([]).prop('disabled', true).selectpicker('refresh');
        updateSubAccountDefaults();
        toastr.info('Configuration reset to defaults');
    });
    
    $('#btnSaveConfig').on('click', function() {
        var webhookUrl = $('#apiInboundUrl').val().trim();
        if (currentMode === 'api' && $('#toggleInboundForwarding').is(':checked') && webhookUrl && !webhookUrl.startsWith('https://')) {
            toastr.error('Webhook URL must start with https://');
            return;
        }
        
        $('#saveSuccessModal').modal('show');
    });
    
    // Mock user data by sub-account (TODO: Replace with backend data)
    var usersBySubAccount = {
        'main': [
            { value: 'main-admin', name: 'Admin User' },
            { value: 'main-john', name: 'John Smith' },
            { value: 'main-sarah', name: 'Sarah Johnson' }
        ],
        'marketing': [
            { value: 'mkt-mike', name: 'Mike Wilson' },
            { value: 'mkt-emma', name: 'Emma Davis' }
        ],
        'support': [
            { value: 'sup-tom', name: 'Tom Brown' },
            { value: 'sup-lisa', name: 'Lisa Chen' }
        ],
        'sales': [
            { value: 'sales-alex', name: 'Alex Turner' },
            { value: 'sales-rachel', name: 'Rachel Green' }
        ],
        'operations': [
            { value: 'ops-david', name: 'David Miller' }
        ],
        'finance': [
            { value: 'fin-kate', name: 'Kate Wilson' },
            { value: 'fin-james', name: 'James Brown' }
        ],
        'hr': [
            { value: 'hr-susan', name: 'Susan Lee' }
        ],
        'it': [
            { value: 'it-paul', name: 'Paul Zhang' },
            { value: 'it-maria', name: 'Maria Garcia' }
        ],
        'legal': [
            { value: 'legal-robert', name: 'Robert King' }
        ],
        'customer-success': [
            { value: 'cs-amy', name: 'Amy Roberts' },
            { value: 'cs-brian', name: 'Brian Scott' }
        ]
    };
    
    var subAccountNames = {
        'main': 'Main Account',
        'marketing': 'Marketing',
        'support': 'Support',
        'sales': 'Sales',
        'operations': 'Operations',
        'finance': 'Finance',
        'hr': 'Human Resources',
        'it': 'IT Department',
        'legal': 'Legal',
        'customer-success': 'Customer Success'
    };
    
    // Initialize selectpickers for API configuration
    $('#apiSubAccountSelect, #apiUserSelect').selectpicker();
    
    // Dynamically populate user dropdown based on selected sub-accounts
    $('#apiSubAccountSelect').on('changed.bs.select', function() {
        var selected = $(this).val() || [];
        var $userSelect = $('#apiUserSelect');
        
        // Clear and rebuild user options
        $userSelect.empty();
        
        if (selected.length > 0) {
            selected.forEach(function(subAccId) {
                var users = usersBySubAccount[subAccId] || [];
                if (users.length > 0) {
                    var $optgroup = $('<optgroup>').attr('label', subAccountNames[subAccId]);
                    users.forEach(function(user) {
                        $optgroup.append($('<option>').val(user.value).text(user.name));
                    });
                    $userSelect.append($optgroup);
                }
            });
            $userSelect.prop('disabled', false);
        } else {
            $userSelect.prop('disabled', true);
        }
        
        $userSelect.selectpicker('refresh');
    });
    
    init();
});
</script>
@endpush

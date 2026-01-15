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
                                
                                <div class="dropdown filter-dropdown w-100" id="portalSubAccountDropdown" data-filter="portalSubAccounts">
                                    <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                        <span class="dropdown-label">Select sub-accounts...</span>
                                    </button>
                                    <div class="dropdown-menu w-100 p-2">
                                        <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                            <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                            <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                        </div>
                                        <div class="dropdown-options" style="max-height: 200px; overflow-y: auto;">
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="main" id="portalSubAccMain"><label class="form-check-label small" for="portalSubAccMain">Main Account</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="marketing" id="portalSubAccMarketing"><label class="form-check-label small" for="portalSubAccMarketing">Marketing</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="support" id="portalSubAccSupport"><label class="form-check-label small" for="portalSubAccSupport">Support</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="sales" id="portalSubAccSales"><label class="form-check-label small" for="portalSubAccSales">Sales</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="operations" id="portalSubAccOps"><label class="form-check-label small" for="portalSubAccOps">Operations</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="finance" id="portalSubAccFinance"><label class="form-check-label small" for="portalSubAccFinance">Finance</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="hr" id="portalSubAccHR"><label class="form-check-label small" for="portalSubAccHR">Human Resources</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="it" id="portalSubAccIT"><label class="form-check-label small" for="portalSubAccIT">IT Department</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="legal" id="portalSubAccLegal"><label class="form-check-label small" for="portalSubAccLegal">Legal</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="customer-success" id="portalSubAccCS"><label class="form-check-label small" for="portalSubAccCS">Customer Success</label></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label fw-bold">User Assignment <span class="badge badge-pastel-pink ms-1">Optional</span></label>
                                    <p class="text-muted small mb-2">Optionally limit to specific users within selected sub-accounts.</p>
                                    
                                    <div class="dropdown filter-dropdown w-100" id="portalUserDropdown" data-filter="portalUsers">
                                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" disabled>
                                            <span class="dropdown-label">All users (default)</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            <div class="dropdown-options" id="portalUserOptions" style="max-height: 200px; overflow-y: auto;">
                                                <p class="text-muted small mb-0">Select sub-accounts first</p>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">Leave empty to allow all users in selected sub-accounts.</small>
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
                                
                                <div class="dropdown filter-dropdown w-100" id="apiSubAccountDropdown" data-filter="apiSubAccounts">
                                    <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                        <span class="dropdown-label">Select sub-accounts...</span>
                                    </button>
                                    <div class="dropdown-menu w-100 p-2">
                                        <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                            <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                            <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                        </div>
                                        <div class="dropdown-options" style="max-height: 200px; overflow-y: auto;">
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="main" id="apiSubAccMain"><label class="form-check-label small" for="apiSubAccMain">Main Account</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="marketing" id="apiSubAccMarketing"><label class="form-check-label small" for="apiSubAccMarketing">Marketing</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="support" id="apiSubAccSupport"><label class="form-check-label small" for="apiSubAccSupport">Support</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="sales" id="apiSubAccSales"><label class="form-check-label small" for="apiSubAccSales">Sales</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="operations" id="apiSubAccOps"><label class="form-check-label small" for="apiSubAccOps">Operations</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="finance" id="apiSubAccFinance"><label class="form-check-label small" for="apiSubAccFinance">Finance</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="hr" id="apiSubAccHR"><label class="form-check-label small" for="apiSubAccHR">Human Resources</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="it" id="apiSubAccIT"><label class="form-check-label small" for="apiSubAccIT">IT Department</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="legal" id="apiSubAccLegal"><label class="form-check-label small" for="apiSubAccLegal">Legal</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="customer-success" id="apiSubAccCS"><label class="form-check-label small" for="apiSubAccCS">Customer Success</label></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label fw-bold">User Assignment <span class="badge badge-pastel-pink ms-1">Optional</span></label>
                                    <p class="text-muted small mb-2">Optionally limit to specific users within selected sub-accounts.</p>
                                    
                                    <div class="dropdown filter-dropdown w-100" id="apiUserDropdown" data-filter="apiUsers">
                                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" disabled>
                                            <span class="dropdown-label">All users (default)</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            <div class="dropdown-options" id="apiUserOptions" style="max-height: 200px; overflow-y: auto;">
                                                <p class="text-muted small mb-0">Select sub-accounts first</p>
                                            </div>
                                        </div>
                                    </div>
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
        var selectedSubAccounts = getSelectedValues('portalSubAccountDropdown');
        var checkedSubAccounts = selectedSubAccounts.map(function(id) {
            return subAccountNames[id] || id;
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
        // Reset Portal dropdowns
        $('#portalSubAccountDropdown .form-check-input').prop('checked', false);
        $('#portalSubAccountDropdown .dropdown-label').text('Select sub-accounts...');
        $('#portalUserOptions').html('<p class="text-muted small mb-0">Select sub-accounts first</p>');
        $('#portalUserDropdown button').prop('disabled', true);
        $('#portalUserDropdown .dropdown-label').text('All users (default)');
        // Reset API dropdowns
        $('#apiSubAccountDropdown .form-check-input').prop('checked', false);
        $('#apiSubAccountDropdown .dropdown-label').text('Select sub-accounts...');
        $('#apiUserOptions').html('<p class="text-muted small mb-0">Select sub-accounts first</p>');
        $('#apiUserDropdown button').prop('disabled', true);
        $('#apiUserDropdown .dropdown-label').text('All users (default)');
        // Reset toggles
        $('#toggleSenderID, #toggleInbox, #toggleOptout').prop('checked', true);
        $('#toggleInboundForwarding').prop('checked', false);
        $('#toggleApiSenderID').prop('checked', false);
        $('#webhookSection').hide();
        $('#apiInboundUrl').val('');
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
    
    // Filter dropdown handlers - Select All / Clear
    $('.filter-dropdown .select-all-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('.form-check-input').prop('checked', true).trigger('change');
    });
    
    $('.filter-dropdown .clear-all-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('.form-check-input').prop('checked', false).trigger('change');
    });
    
    // Helper function to get selected values from a filter dropdown
    function getSelectedValues(dropdownId) {
        var values = [];
        $('#' + dropdownId + ' .form-check-input:checked').each(function() {
            values.push($(this).val());
        });
        return values;
    }
    
    // Helper function to update dropdown label
    function updateDropdownLabel(dropdownId, defaultLabel) {
        var selected = getSelectedValues(dropdownId);
        var $label = $('#' + dropdownId + ' .dropdown-label');
        
        if (selected.length === 0) {
            $label.text(defaultLabel);
        } else if (selected.length <= 2) {
            var names = selected.map(function(id) { return subAccountNames[id] || id; });
            $label.text(names.join(', '));
        } else {
            $label.text(selected.length + ' selected');
        }
    }
    
    // Helper function to populate user dropdown based on selected sub-accounts
    function populateUserDropdown(subAccountDropdownId, userOptionsId, userDropdownId) {
        var selected = getSelectedValues(subAccountDropdownId);
        var $userOptions = $('#' + userOptionsId);
        var $userBtn = $('#' + userDropdownId + ' button');
        
        $userOptions.empty();
        
        if (selected.length > 0) {
            selected.forEach(function(subAccId) {
                var users = usersBySubAccount[subAccId] || [];
                if (users.length > 0) {
                    $userOptions.append('<div class="small fw-bold text-muted mt-2 mb-1">' + subAccountNames[subAccId] + '</div>');
                    users.forEach(function(user) {
                        var checkId = userDropdownId + '_' + user.value.replace(/[^a-zA-Z0-9]/g, '');
                        $userOptions.append('<div class="form-check"><input class="form-check-input" type="checkbox" value="' + user.value + '" id="' + checkId + '"><label class="form-check-label small" for="' + checkId + '">' + user.name + '</label></div>');
                    });
                }
            });
            $userBtn.prop('disabled', false);
        } else {
            $userOptions.html('<p class="text-muted small mb-0">Select sub-accounts first</p>');
            $userBtn.prop('disabled', true);
        }
        
        $('#' + userDropdownId + ' .dropdown-label').text('All users (default)');
    }
    
    // Portal sub-account change handler
    $('#portalSubAccountDropdown .form-check-input').on('change', function() {
        updateDropdownLabel('portalSubAccountDropdown', 'Select sub-accounts...');
        populateUserDropdown('portalSubAccountDropdown', 'portalUserOptions', 'portalUserDropdown');
        updateSubAccountDefaults();
    });
    
    // API sub-account change handler
    $('#apiSubAccountDropdown .form-check-input').on('change', function() {
        updateDropdownLabel('apiSubAccountDropdown', 'Select sub-accounts...');
        populateUserDropdown('apiSubAccountDropdown', 'apiUserOptions', 'apiUserDropdown');
    });
    
    // User selection change handlers
    $(document).on('change', '#portalUserOptions .form-check-input, #apiUserOptions .form-check-input', function() {
        var dropdownId = $(this).closest('.filter-dropdown').attr('id');
        var $label = $('#' + dropdownId + ' .dropdown-label');
        var checked = $('#' + dropdownId + ' .dropdown-options .form-check-input:checked');
        
        if (checked.length === 0) {
            $label.text('All users (default)');
        } else if (checked.length <= 2) {
            var names = [];
            checked.each(function() {
                names.push($(this).next('label').text());
            });
            $label.text(names.join(', '));
        } else {
            $label.text(checked.length + ' users selected');
        }
    });
    
    init();
});
</script>
@endpush

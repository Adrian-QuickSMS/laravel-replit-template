@extends('layouts.admin')

@section('title', 'Configure Number')

@push('styles')
<style>
.selection-summary {
    background: linear-gradient(135deg, rgba(30, 58, 95, 0.08) 0%, rgba(45, 90, 135, 0.12) 100%);
    border: 1px solid rgba(30, 58, 95, 0.25);
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
}
.selection-summary .count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.85rem;
    margin-right: 0.75rem;
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
.config-section-header h6 i {
    color: var(--admin-primary, #1e3a5f);
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
    border-color: var(--admin-primary, #1e3a5f);
    background: rgba(30, 58, 95, 0.03);
}
.mode-selector-card.active {
    border-color: var(--admin-primary, #1e3a5f);
    background: rgba(30, 58, 95, 0.08);
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.15);
}
.mode-selector-card .mode-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(30, 58, 95, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
}
.mode-selector-card .mode-icon i {
    font-size: 1.25rem;
    color: var(--admin-primary, #1e3a5f);
}
.mode-selector-card.active .mode-icon {
    background: var(--admin-primary, #1e3a5f);
}
.mode-selector-card.active .mode-icon i {
    color: #fff;
}
.mode-selector-card h6 {
    color: #333;
}
.mode-features-list {
    list-style: none;
    padding: 0;
    margin: 0.75rem 0 0;
    font-size: 0.8rem;
}
.mode-features-list li {
    padding: 0.25rem 0;
    color: #6c757d;
}
.mode-features-list li i {
    width: 16px;
    margin-right: 0.5rem;
}
.mode-features-list li.feature-yes i {
    color: var(--admin-primary, #1e3a5f);
}
.mode-features-list li.feature-no i {
    color: #adb5bd;
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
.form-switch .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
    cursor: pointer;
}
.form-switch .form-check-input:checked {
    background-color: var(--admin-primary, #1e3a5f);
    border-color: var(--admin-primary, #1e3a5f);
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}
.info-item {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}
.info-item .info-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}
.info-item .info-value {
    font-weight: 600;
    color: #333;
}
.type-label {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.type-vmn { background: #e3f2fd; color: #1565c0; }
.type-keyword { background: #e8f5e9; color: #2e7d32; }
.type-dedicated { background: #fce4ec; color: #c2185b; }
.badge-admin-active {
    background: rgba(30, 58, 95, 0.1);
    color: var(--admin-primary, #1e3a5f);
    padding: 0.35rem 0.65rem;
    border-radius: 0.25rem;
    font-weight: 500;
    font-size: 0.75rem;
}
.badge-admin-suspended {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    padding: 0.35rem 0.65rem;
    border-radius: 0.25rem;
    font-weight: 500;
    font-size: 0.75rem;
}
.badge-admin-pending {
    background: rgba(255, 193, 7, 0.15);
    color: #b38600;
    padding: 0.35rem 0.65rem;
    border-radius: 0.25rem;
    font-weight: 500;
    font-size: 0.75rem;
}
.action-buttons-bar {
    background: #fff;
    border-top: 1px solid #e9ecef;
    padding: 1rem 0;
    position: sticky;
    bottom: 0;
}
.btn-admin-primary {
    background: var(--admin-primary, #1e3a5f);
    border-color: var(--admin-primary, #1e3a5f);
    color: #fff;
}
.btn-admin-primary:hover {
    background: var(--admin-secondary, #2d5a87);
    border-color: var(--admin-secondary, #2d5a87);
    color: #fff;
}
.keyword-restriction-alert {
    background: rgba(30, 58, 95, 0.1);
    border: 1px solid rgba(30, 58, 95, 0.3);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
}
.keyword-restriction-alert i {
    color: var(--admin-primary, #1e3a5f);
}
.api-webhook-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}
.network-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 0.75rem;
}
.network-info-item {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 0.75rem;
}
.network-info-item .label {
    font-size: 0.7rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.network-info-item .value {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Management</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.assets.numbers') }}">Numbers</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Configure</a></li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="selection-summary mb-4" id="selectionSummary">
                <div class="d-flex align-items-center">
                    <span class="count-badge">1</span>
                    <div>
                        <h6 class="mb-0 fw-semibold" id="numberTitle">Loading...</h6>
                        <small class="text-muted">Configure settings for this number</small>
                    </div>
                    <a href="{{ route('admin.assets.numbers') }}" class="btn btn-outline-secondary btn-sm ms-auto">
                        <i class="fas fa-arrow-left me-1"></i> Back to Library
                    </a>
                </div>
            </div>
            
            <div id="loadingState" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3">Loading number details...</p>
            </div>
            
            <div id="notFoundState" style="display: none;">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-circle fa-3x text-danger"></i>
                        </div>
                        <h5>Number Not Found</h5>
                        <p class="text-muted mb-4">The requested number could not be found in the system.</p>
                        <a href="{{ route('admin.assets.numbers') }}" class="btn btn-admin-primary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Numbers Library
                        </a>
                    </div>
                </div>
            </div>
            
            <div id="configurationPanel" style="display: none;">
                <div class="config-section">
                    <div class="config-section-header">
                        <h6><i class="fas fa-info-circle me-2"></i>Number Details</h6>
                    </div>
                    <div class="config-section-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Number / Keyword</div>
                                <div class="info-value" id="cfg_number">—</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Type</div>
                                <div class="info-value" id="cfg_type">—</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value" id="cfg_status">—</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Customer Account</div>
                                <div class="info-value" id="cfg_account">—</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Sub-Account</div>
                                <div class="info-value" id="cfg_subaccount">—</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Monthly Cost</div>
                                <div class="info-value" id="cfg_cost">—</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Supplier</div>
                                <div class="info-value" id="cfg_supplier">—</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Country</div>
                                <div class="info-value" id="cfg_country">—</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="config-section">
                    <div class="config-section-header">
                        <h6><i class="fas fa-network-wired me-2"></i>Network Information</h6>
                    </div>
                    <div class="config-section-body">
                        <div class="network-info-grid">
                            <div class="network-info-item">
                                <div class="label">Supplier</div>
                                <div class="value" id="cfg_network_supplier">—</div>
                            </div>
                            <div class="network-info-item">
                                <div class="label">Route</div>
                                <div class="value" id="cfg_network_route">—</div>
                            </div>
                            <div class="network-info-item">
                                <div class="label">Network</div>
                                <div class="value" id="cfg_network_destination">—</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="keyword-restriction-alert" id="keywordRestrictionAlert" style="display: none;">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle me-3 mt-1"></i>
                        <div>
                            <strong style="color: #333;">Shortcode Keyword Restrictions</strong>
                            <p class="mb-0 small mt-1" style="color: #6c757d;">This keyword can only be used for opt-out handling. SenderID and Inbox capabilities are not available for shortcode keywords.</p>
                        </div>
                    </div>
                </div>
                
                <div class="config-section" id="sectionModeSelection">
                    <div class="config-section-header">
                        <h6><i class="fas fa-exchange-alt me-2"></i>Operating Mode</h6>
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
                                        <li class="feature-yes"><i class="fas fa-check"></i>Campaign Composer access</li>
                                        <li class="feature-yes"><i class="fas fa-check"></i>Inbox visibility</li>
                                        <li class="feature-yes"><i class="fas fa-check"></i>Opt-out management</li>
                                        <li class="feature-no"><i class="fas fa-times"></i>Not available via API</li>
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
                                        <li class="feature-yes"><i class="fas fa-check"></i>REST API access</li>
                                        <li class="feature-yes"><i class="fas fa-check"></i>Webhook forwarding</li>
                                        <li class="feature-no"><i class="fas fa-times"></i>Limited Portal Features</li>
                                        <li class="feature-no"><i class="fas fa-times"></i>Not visible in campaigns</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="config-section" id="sectionPortalConfig" style="display: none;">
                    <div class="config-section-header">
                        <h6><i class="fas fa-cogs me-2"></i>Portal Configuration</h6>
                    </div>
                    <div class="config-section-body">
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <label class="form-label fw-bold">Sub-Account Assignment</label>
                                <p class="text-muted small mb-2">Select which sub-accounts can use this number.</p>
                                
                                <select class="form-select" id="portalSubAccountSelect">
                                    <option value="">Select sub-account...</option>
                                </select>
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
                    </div>
                </div>
                
                <div class="config-section" id="sectionAPIConfig" style="display: none;">
                    <div class="config-section-header">
                        <h6><i class="fas fa-plug me-2"></i>API Configuration</h6>
                    </div>
                    <div class="config-section-body">
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <label class="form-label fw-bold">Sub-Account Assignment</label>
                                <p class="text-muted small mb-2">Select which sub-accounts can use this number via API.</p>
                                
                                <select class="form-select" id="apiSubAccountSelect">
                                    <option value="">Select sub-account...</option>
                                </select>
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
                                            <div class="fw-semibold small">Enable Inbound Webhook</div>
                                            <div class="text-muted small">Forward inbound messages to webhook</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="toggleApiInbound" checked>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="capability-toggle-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small">Enable Opt-out Webhook</div>
                                            <div class="text-muted small">Notify webhook on opt-out events</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="toggleApiOptout" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="api-webhook-section">
                            <label class="form-label fw-bold">Webhook URL</label>
                            <p class="text-muted small mb-2">Configure the endpoint for inbound message and opt-out notifications.</p>
                            <input type="url" class="form-control" id="webhookUrl" placeholder="https://api.example.com/webhooks/sms">
                            <small class="text-muted d-block mt-1">Messages will be POSTed to this URL in JSON format.</small>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons-bar">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.assets.numbers') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="button" class="btn btn-admin-primary" id="saveConfigBtn">
                            <i class="fas fa-save me-1"></i> Save Configuration
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/numbers-admin-service.js') }}"></script>
<script>
const numberId = '{{ $number_id }}';
let currentNumber = null;

document.addEventListener('DOMContentLoaded', function() {
    loadNumberDetails();
    initModeSelectors();
    initSaveButton();
});

function loadNumberDetails() {
    let num = null;
    if (typeof NumbersAdminService !== 'undefined' && NumbersAdminService._mockDb) {
        num = NumbersAdminService._mockDb.numbers.find(n => n.id === numberId);
    }
    
    document.getElementById('loadingState').style.display = 'none';
    
    if (!num) {
        document.getElementById('notFoundState').style.display = 'block';
        return;
    }
    
    currentNumber = num;
    document.getElementById('configurationPanel').style.display = 'block';
    document.getElementById('numberTitle').textContent = num.number;
    
    document.getElementById('cfg_number').textContent = num.number;
    document.getElementById('cfg_type').innerHTML = getTypeLabel(num.type);
    document.getElementById('cfg_status').innerHTML = getStatusBadge(num.status);
    document.getElementById('cfg_account').textContent = num.account || '—';
    document.getElementById('cfg_subaccount').textContent = num.subAccount || '—';
    document.getElementById('cfg_cost').textContent = num.cost ? `£${num.cost.toFixed(2)}` : '—';
    document.getElementById('cfg_supplier').textContent = num.supplier || '—';
    document.getElementById('cfg_country').textContent = getCountryName(num.country);
    
    document.getElementById('cfg_network_supplier').textContent = num.supplier || '—';
    document.getElementById('cfg_network_route').textContent = num.route || 'Direct';
    document.getElementById('cfg_network_destination').textContent = num.network || '—';
    
    const isKeyword = num.type === 'shortcode_keyword';
    document.getElementById('keywordRestrictionAlert').style.display = isKeyword ? 'block' : 'none';
    
    if (isKeyword) {
        document.getElementById('sectionModeSelection').style.display = 'none';
        document.getElementById('sectionPortalConfig').style.display = 'none';
        document.getElementById('sectionAPIConfig').style.display = 'none';
        
        document.getElementById('capSenderIDCard').classList.add('disabled');
        document.getElementById('toggleSenderID').disabled = true;
        document.getElementById('toggleSenderID').checked = false;
        
        document.getElementById('capInboxCard').classList.add('disabled');
        document.getElementById('toggleInbox').disabled = true;
        document.getElementById('toggleInbox').checked = false;
    } else {
        document.getElementById('sectionModeSelection').style.display = 'block';
        
        if (num.mode === 'portal') {
            document.getElementById('modeCardPortal').classList.add('active');
            document.getElementById('modeCardAPI').classList.remove('active');
            document.getElementById('sectionPortalConfig').style.display = 'block';
            document.getElementById('sectionAPIConfig').style.display = 'none';
        } else if (num.mode === 'api') {
            document.getElementById('modeCardAPI').classList.add('active');
            document.getElementById('modeCardPortal').classList.remove('active');
            document.getElementById('sectionPortalConfig').style.display = 'none';
            document.getElementById('sectionAPIConfig').style.display = 'block';
        }
    }
    
    populateSubAccountDropdowns();
    
    if (num.capabilities) {
        document.getElementById('toggleSenderID').checked = num.capabilities.senderid !== false;
        document.getElementById('toggleInbox').checked = num.capabilities.inbox !== false;
        document.getElementById('toggleOptout').checked = num.capabilities.optout !== false;
    }
}

function getTypeLabel(type) {
    const types = {
        'vmn': '<span class="type-label type-vmn">VMN</span>',
        'shortcode_keyword': '<span class="type-label type-keyword">Shortcode Keyword</span>',
        'dedicated_shortcode': '<span class="type-label type-dedicated">Dedicated Shortcode</span>'
    };
    return types[type] || type;
}

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-admin-active">Active</span>',
        'suspended': '<span class="badge badge-admin-suspended">Suspended</span>',
        'pending': '<span class="badge badge-admin-pending">Pending</span>'
    };
    return badges[status] || status;
}

function getCountryName(code) {
    const countries = {
        'UK': '🇬🇧 United Kingdom',
        'US': '🇺🇸 United States',
        'DE': '🇩🇪 Germany',
        'FR': '🇫🇷 France',
        'ES': '🇪🇸 Spain'
    };
    return countries[code] || code;
}

function initModeSelectors() {
    const portalCard = document.getElementById('modeCardPortal');
    const apiCard = document.getElementById('modeCardAPI');
    
    portalCard.addEventListener('click', function() {
        portalCard.classList.add('active');
        apiCard.classList.remove('active');
        document.getElementById('sectionPortalConfig').style.display = 'block';
        document.getElementById('sectionAPIConfig').style.display = 'none';
    });
    
    apiCard.addEventListener('click', function() {
        apiCard.classList.add('active');
        portalCard.classList.remove('active');
        document.getElementById('sectionPortalConfig').style.display = 'none';
        document.getElementById('sectionAPIConfig').style.display = 'block';
    });
}

function populateSubAccountDropdowns() {
    if (!currentNumber || typeof NumbersAdminService === 'undefined') return;
    
    const subAccounts = NumbersAdminService._mockDb.subAccounts.filter(sa => 
        sa.accountId === currentNumber.accountId
    );
    
    const portalSelect = document.getElementById('portalSubAccountSelect');
    const apiSelect = document.getElementById('apiSubAccountSelect');
    
    subAccounts.forEach(sa => {
        const option1 = document.createElement('option');
        option1.value = sa.id;
        option1.textContent = sa.name;
        portalSelect.appendChild(option1);
        
        const option2 = document.createElement('option');
        option2.value = sa.id;
        option2.textContent = sa.name;
        apiSelect.appendChild(option2);
    });
    
    if (currentNumber.subAccountId) {
        portalSelect.value = currentNumber.subAccountId;
        apiSelect.value = currentNumber.subAccountId;
    }
}

function initSaveButton() {
    document.getElementById('saveConfigBtn').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        
        setTimeout(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Saved!';
            btn.classList.remove('btn-admin-primary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Configuration';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-admin-primary');
            }, 2000);
        }, 1000);
    });
}
</script>
@endpush

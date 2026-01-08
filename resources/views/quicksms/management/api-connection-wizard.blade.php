@extends('layouts.quicksms')

@section('title', 'Create API Connection')

@push('styles')
<link href="{{ asset('vendor/jquery-smartwizard/dist/css/smart_wizard.min.css') }}" rel="stylesheet">
<style>
.form-wizard {
    border: 0;
}
.form-wizard .nav-wizard {
    box-shadow: none !important;
    margin-bottom: 2rem;
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
}
.form-wizard .nav-wizard li {
    flex: 1;
    max-width: 150px;
}
.form-wizard .nav-wizard li .nav-link {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #6c757d;
    padding: 0;
    background: transparent !important;
    border: none !important;
}
.form-wizard .nav-wizard li .nav-link span {
    border-radius: 3.125rem;
    width: 3rem;
    height: 3rem;
    border: 0.125rem solid var(--primary, #886CC0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: var(--primary, #886CC0);
    position: relative;
    z-index: 1;
}
.form-wizard .nav-wizard li .nav-link:after {
    position: absolute;
    top: 1.5rem;
    left: 50%;
    height: 0.1875rem;
    background: #e9ecef;
    content: "";
    z-index: 0;
    width: 100%;
}
.form-wizard .nav-wizard li:last-child .nav-link:after {
    content: none;
}
.form-wizard .nav-wizard li .nav-link.active span,
.form-wizard .nav-wizard li .nav-link.done span {
    background: var(--primary, #886CC0);
    color: #fff;
    border-color: var(--primary, #886CC0);
}
.form-wizard .nav-wizard li .nav-link.active:after,
.form-wizard .nav-wizard li .nav-link.done:after {
    background: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link small {
    display: none;
}
.form-wizard .nav-wizard li .nav-link.skipped span {
    background: #e9ecef;
    color: #6c757d;
    border-color: #dee2e6;
}
.form-wizard .nav-wizard li .nav-link.skipped:after {
    background: #e9ecef !important;
}

/* Step state colors - matches RCS Agent wizard */
.form-wizard .nav-wizard li .nav-link.step-not-visited span {
    background: #fff !important;
    color: var(--primary, #886CC0) !important;
    border-color: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link.step-not-visited:after {
    background: #e9ecef !important;
}
.form-wizard .nav-wizard li .nav-link.step-visited-incomplete span {
    background: rgba(220, 53, 69, 0.15) !important;
    color: #dc3545 !important;
    border-color: #dc3545 !important;
}
.form-wizard .nav-wizard li .nav-link.step-visited-incomplete:after {
    background: rgba(220, 53, 69, 0.3) !important;
}
.form-wizard .nav-wizard li .nav-link.step-completed span {
    background: var(--primary, #886CC0) !important;
    color: #fff !important;
    border-color: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link.step-completed:after {
    background: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link.step-skipped span {
    background: #e9ecef !important;
    color: #6c757d !important;
    border-color: #dee2e6 !important;
}
.form-wizard .nav-wizard li .nav-link.step-skipped:after {
    background: #e9ecef !important;
}

.form-wizard .toolbar-bottom {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
.form-wizard .sw-btn-prev,
.form-wizard .sw-btn-next {
    background-color: var(--primary, #886CC0) !important;
    border: 0 !important;
    padding: 0.75rem 1.5rem !important;
    color: #fff !important;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.form-wizard .sw-btn-prev {
    background-color: #6c757d !important;
}
.form-wizard .sw-btn-prev:hover {
    background-color: #5a6268 !important;
}
.form-wizard .sw-btn-next:hover {
    background-color: #7559b3 !important;
}
.form-wizard .sw-btn-prev:disabled,
.form-wizard .sw-btn-next:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.form-wizard .tab-content .tab-pane {
    padding: 0;
    overflow: visible !important;
}

#integrationPartnerSection {
    margin-top: 1rem;
    display: none;
}
#integrationPartnerSection.show {
    display: block !important;
}

.selectable-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
    text-align: center;
}
.selectable-tile:hover {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.05);
}
.selectable-tile.selected {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.1);
}
.selectable-tile .tile-icon {
    width: 60px;
    height: 60px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 0.75rem;
}
.selectable-tile .tile-title {
    margin-bottom: 0.25rem;
    font-weight: 600;
}
.selectable-tile .tile-desc {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0;
}
.selectable-tile.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.bg-pastel-primary { background: rgba(136, 108, 192, 0.15); color: #886CC0; }
.bg-pastel-warning { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
.bg-pastel-info { background: rgba(23, 162, 184, 0.15); color: #117a8b; }
.bg-pastel-success { background: rgba(40, 167, 69, 0.15); color: #28a745; }
.bg-pastel-secondary { background: rgba(108, 117, 125, 0.15); color: #6c757d; }

.alert-pastel-primary {
    background-color: rgba(136, 108, 192, 0.1);
    border-color: rgba(136, 108, 192, 0.2);
    color: #5a4a7a;
}

.partner-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}
.partner-tile:hover {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.05);
}
.partner-tile.selected {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.1);
}
.partner-tile .partner-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #886CC0;
}

.autosave-indicator {
    font-size: 0.85rem;
    color: #6c757d;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    background: #f8f9fa;
}
.autosave-indicator.saving {
    color: #ffc107;
}
.autosave-indicator.saved {
    color: #28a745;
}

.completion-card {
    text-align: center;
    padding: 2rem;
}
.completion-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(40, 167, 69, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.completion-icon i {
    font-size: 2.5rem;
    color: #28a745;
}
.credential-box {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}
.credential-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.credential-row:last-child {
    border-bottom: none;
}
.credential-label {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.85rem;
}
.credential-value {
    font-family: monospace;
    font-size: 0.9rem;
    word-break: break-all;
}

.skip-section-btn {
    font-size: 0.85rem;
    color: #6c757d;
    cursor: pointer;
    text-decoration: underline;
}
.skip-section-btn:hover {
    color: #886CC0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('management.api-connections') }}">API Connections</a></li>
            <li class="breadcrumb-item active">Create Connection</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-plug me-2 text-primary"></i>Create API Connection</h4>
                    <span class="autosave-indicator saved" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <div class="card-body">
                    <div id="apiConnectionWizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link" href="#step-1"><span>1</span><small>Basics</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-2"><span>2</span><small>Type</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-3"><span>3</span><small>Auth</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-4"><span>4</span><small>Security</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-5"><span>5</span><small>Webhooks</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-6"><span>6</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <div id="step-1" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 1: Core Configuration</strong> - Define your API connection's name, description, and environment.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label class="form-label">API Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="apiName" placeholder="e.g., Campaign Manager API" maxlength="50">
                                                <small class="text-muted">A unique, descriptive name for this API connection.</small>
                                                <div class="invalid-feedback">Please enter an API name.</div>
                                            </div>
                                            
                                            <div class="col-lg-12 mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" id="apiDescription" rows="2" placeholder="Brief description of this API connection..." maxlength="200"></textarea>
                                                <small class="text-muted"><span id="descCharCount">0</span>/200 characters</small>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="form-label">Sub-Account <span class="text-danger">*</span></label>
                                                <select class="form-select" id="subAccount">
                                                    <option value="">Select sub-account...</option>
                                                    <option value="Main Account">Main Account</option>
                                                    <option value="Marketing">Marketing</option>
                                                    <option value="Development">Development</option>
                                                    <option value="Operations">Operations</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a sub-account.</div>
                                            </div>
                                            
                                            <div class="col-lg-6 mb-3">
                                                <label class="form-label">Environment <span class="text-danger">*</span></label>
                                                <select class="form-select" id="environment">
                                                    <option value="test">Test (Sandbox)</option>
                                                    <option value="live">Live (Production)</option>
                                                </select>
                                                <small class="text-muted">Test environments use sandbox endpoints.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-2" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 2: Connection Type</strong> - Select the API type based on your integration needs.
                                        </div>
                                        
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-4">
                                                <div class="selectable-tile" data-type="bulk" onclick="selectApiType('bulk')">
                                                    <div class="tile-icon bg-pastel-primary"><i class="fas fa-paper-plane"></i></div>
                                                    <div class="tile-title">Bulk API</div>
                                                    <div class="tile-desc">Transport-only. No platform intelligence.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selectable-tile" data-type="campaign" onclick="selectApiType('campaign')">
                                                    <div class="tile-icon bg-pastel-warning"><i class="fas fa-bullhorn"></i></div>
                                                    <div class="tile-title">Campaign API</div>
                                                    <div class="tile-desc">Full platform-aware messaging.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selectable-tile" data-type="integration" onclick="selectApiType('integration')">
                                                    <div class="tile-icon bg-pastel-info"><i class="fas fa-plug"></i></div>
                                                    <div class="tile-title">Integration</div>
                                                    <div class="tile-desc">QuickSMS-managed connectors.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback d-block" id="apiTypeError" style="display: none;">Please select an API type.</div>
                                        
                                        <div id="integrationPartnerSection">
                                            <hr class="my-4">
                                            <h6 class="mb-3">Select Integration Partner</h6>
                                            <div class="row g-3">
                                                <div class="col-6 col-md-3">
                                                    <div class="partner-tile" data-partner="SystmOne" onclick="selectPartner('SystmOne')">
                                                        <div class="partner-icon"><i class="fas fa-hospital"></i></div>
                                                        <div class="fw-medium">SystmOne</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="partner-tile" data-partner="Rio" onclick="selectPartner('Rio')">
                                                        <div class="partner-icon"><i class="fas fa-brain"></i></div>
                                                        <div class="fw-medium">Rio</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="partner-tile" data-partner="EMIS" onclick="selectPartner('EMIS')">
                                                        <div class="partner-icon"><i class="fas fa-stethoscope"></i></div>
                                                        <div class="fw-medium">EMIS</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="partner-tile" data-partner="Accurx" onclick="selectPartner('Accurx')">
                                                        <div class="partner-icon"><i class="fas fa-video"></i></div>
                                                        <div class="fw-medium">Accurx</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-3" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 3: Authentication</strong> - Choose how this API connection will authenticate requests.
                                        </div>
                                        
                                        <div class="alert alert-pastel-primary mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Note:</strong> Authentication method cannot be changed after creation.
                                        </div>
                                        
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <div class="selectable-tile" data-auth="API Key" onclick="selectAuthType('API Key')">
                                                    <div class="tile-icon bg-pastel-success"><i class="fas fa-key"></i></div>
                                                    <div class="tile-title">API Key</div>
                                                    <div class="tile-desc">Simple token-based authentication.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selectable-tile" data-auth="Basic Auth" onclick="selectAuthType('Basic Auth')">
                                                    <div class="tile-icon bg-pastel-primary"><i class="fas fa-user-lock"></i></div>
                                                    <div class="tile-title">Basic Auth</div>
                                                    <div class="tile-desc">Username and password authentication.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selectable-tile disabled" data-auth="OAuth" title="Coming soon">
                                                    <div class="tile-icon bg-pastel-secondary"><i class="fas fa-shield-alt"></i></div>
                                                    <div class="tile-title">OAuth 2.0</div>
                                                    <div class="tile-desc">Coming soon</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback d-block" id="authTypeError" style="display: none;">Please select an authentication method.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-4" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 4: Security Controls</strong> - Configure additional security settings.
                                            <span class="skip-section-btn float-end" onclick="skipStep(4)">Skip this section</span>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="enableIpRestriction">
                                                <label class="form-check-label fw-medium" for="enableIpRestriction">Enable IP Address Restriction</label>
                                            </div>
                                            <p class="text-muted small">When enabled, API requests will only be accepted from specified IP addresses.</p>
                                        </div>
                                        
                                        <div id="ipRestrictionFields" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label">Allowed IP Addresses</label>
                                                <textarea class="form-control" id="allowedIps" rows="4" placeholder="Enter IP addresses, one per line&#10;e.g., 192.168.1.1&#10;10.0.0.0/24"></textarea>
                                                <small class="text-muted">Supports IPv4, IPv6, and CIDR notation.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-5" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 5: Webhook Configuration</strong> - Configure callback URLs for delivery reports and inbound messages.
                                            <span class="skip-section-btn float-end" onclick="skipStep(5)">Skip this section</span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Delivery Reports Webhook URL</label>
                                            <input type="url" class="form-control" id="dlrUrl" placeholder="https://your-domain.com/webhooks/dlr">
                                            <small class="text-muted">We'll POST delivery status updates to this URL. Must be HTTPS.</small>
                                            <div class="invalid-feedback">URL must start with https://</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Inbound Messages Webhook URL</label>
                                            <input type="url" class="form-control" id="inboundUrl" placeholder="https://your-domain.com/webhooks/inbound">
                                            <small class="text-muted">We'll POST inbound messages to this URL. Must be HTTPS.</small>
                                            <div class="invalid-feedback">URL must start with https://</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-6" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div id="reviewSection">
                                            <div class="alert alert-pastel-primary mb-4">
                                                <strong>Step 6: Review & Create</strong> - Review your configuration before creating the connection.
                                            </div>
                                            
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h6 class="text-muted mb-3">Configuration Summary</h6>
                                                    <table class="table table-borderless mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <td class="text-muted" style="width: 150px;">Name</td>
                                                                <td id="reviewName">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Description</td>
                                                                <td id="reviewDescription">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Sub-Account</td>
                                                                <td id="reviewSubAccount">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Environment</td>
                                                                <td id="reviewEnvironment">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Type</td>
                                                                <td id="reviewType">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Authentication</td>
                                                                <td id="reviewAuth">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">IP Restriction</td>
                                                                <td id="reviewIpRestriction">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Webhooks</td>
                                                                <td id="reviewWebhooks">-</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="completionSection" style="display: none;">
                                            <div class="completion-card">
                                                <div class="completion-icon">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                                <h4 class="mb-2">API Connection Created!</h4>
                                                <p class="text-muted mb-4">Your API connection has been created successfully. Save your credentials below - they will only be shown once.</p>
                                                
                                                <div class="credential-box text-start">
                                                    <div class="credential-row">
                                                        <span class="credential-label">Base URL</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="credential-value" id="createdBaseUrl">-</span>
                                                            <button class="btn btn-sm btn-outline-secondary" onclick="copyCredential('createdBaseUrl')"><i class="fas fa-copy"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="credential-row" id="createdApiKeyRow">
                                                        <span class="credential-label">API Key</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="credential-value" id="createdApiKey">-</span>
                                                            <button class="btn btn-sm btn-outline-secondary" onclick="copyCredential('createdApiKey')"><i class="fas fa-copy"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="credential-row" id="createdUsernameRow" style="display: none;">
                                                        <span class="credential-label">Username</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="credential-value" id="createdUsername">-</span>
                                                            <button class="btn btn-sm btn-outline-secondary" onclick="copyCredential('createdUsername')"><i class="fas fa-copy"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="credential-row" id="createdPasswordRow" style="display: none;">
                                                        <span class="credential-label">Password</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="credential-value" id="createdPassword">-</span>
                                                            <button class="btn btn-sm btn-outline-secondary" onclick="copyCredential('createdPassword')"><i class="fas fa-copy"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-warning mt-3 text-start">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <strong>Important:</strong> These credentials will only be shown once. Please save them securely.
                                                </div>
                                                
                                                <div class="mt-4">
                                                    <a href="{{ route('management.api-connections') }}" class="btn btn-primary">
                                                        <i class="fas fa-arrow-left me-2"></i>Back to API Connections
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resumeDraftModal" tabindex="-1" aria-labelledby="resumeDraftModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="resumeDraftModalLabel">
                    <i class="fas fa-file-alt text-primary me-2"></i>Resume Draft?
                </h5>
            </div>
            <div class="modal-body">
                <p class="mb-3">You have an unsaved draft from a previous session. Would you like to continue where you left off?</p>
                <div class="d-flex align-items-center p-3 rounded" style="background-color: rgba(136, 108, 192, 0.1);">
                    <div class="me-3">
                        <i class="fas fa-clock text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" id="draftApiName">Draft Connection</div>
                        <div class="small text-muted">Last saved: <span id="draftTimestamp">-</span></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" id="discardDraftBtn">
                    <i class="fas fa-trash-alt me-1"></i> Discard & Start Fresh
                </button>
                <button type="button" class="btn btn-primary" id="resumeDraftBtn">
                    <i class="fas fa-play me-1"></i> Continue Draft
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="validationSummaryModal" tabindex="-1" aria-labelledby="validationSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="validationSummaryModalLabel">
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Incomplete Steps
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Please complete the following steps before creating the connection:</p>
                <div id="validationSummaryContent"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i> Go Back and Complete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
<script>
$(document).ready(function() {
    var wizardData = {
        name: '',
        description: '',
        subAccount: '',
        environment: 'test',
        type: '',
        integrationName: null,
        authType: '',
        ipAllowList: false,
        allowedIps: [],
        dlrUrl: '',
        inboundUrl: '',
        validatedSteps: [],
        completedSteps: []
    };
    
    var skippedSteps = [];
    var connectionCreated = false;
    
    function checkStepValidity(stepIndex) {
        if (skippedSteps.indexOf(stepIndex) > -1) return true;
        
        if (stepIndex === 0) {
            return wizardData.name.trim() && wizardData.subAccount;
        } else if (stepIndex === 1) {
            return !!wizardData.type;
        } else if (stepIndex === 2) {
            return !!wizardData.authType;
        } else if (stepIndex === 3) {
            if (!wizardData.ipAllowList) return true;
            return wizardData.allowedIps.length > 0;
        } else if (stepIndex === 4) {
            var dlrValid = !wizardData.dlrUrl || wizardData.dlrUrl.startsWith('https://');
            var inboundValid = !wizardData.inboundUrl || wizardData.inboundUrl.startsWith('https://');
            return dlrValid && inboundValid;
        }
        return true;
    }
    
    function getStepState(stepIndex) {
        if (skippedSteps.indexOf(stepIndex) > -1) return 'skipped';
        var isValidated = wizardData.validatedSteps.indexOf(stepIndex) > -1;
        var isCompleted = wizardData.completedSteps.indexOf(stepIndex) > -1;
        
        if (isCompleted) return 'completed';
        if (isValidated) return 'visited-incomplete';
        return 'not-visited';
    }
    
    function updateStepIndicators() {
        $('#apiConnectionWizard .nav-wizard li').each(function(index) {
            var $step = $(this);
            var $link = $step.find('.nav-link');
            var state = getStepState(index);
            
            $link.removeClass('step-not-visited step-visited-incomplete step-completed step-skipped done active');
            $link.addClass('step-' + state);
        });
    }
    
    function markStepValidated(stepIndex) {
        if (wizardData.validatedSteps.indexOf(stepIndex) === -1) {
            wizardData.validatedSteps.push(stepIndex);
        }
    }
    
    function markStepCompleted(stepIndex) {
        if (wizardData.completedSteps.indexOf(stepIndex) === -1) {
            wizardData.completedSteps.push(stepIndex);
        }
        var idx = wizardData.validatedSteps.indexOf(stepIndex);
        if (idx > -1) {
            wizardData.validatedSteps.splice(idx, 1);
        }
    }
    
    function unmarkStepCompleted(stepIndex) {
        var idx = wizardData.completedSteps.indexOf(stepIndex);
        if (idx > -1) {
            wizardData.completedSteps.splice(idx, 1);
        }
        if (wizardData.validatedSteps.indexOf(stepIndex) === -1) {
            wizardData.validatedSteps.push(stepIndex);
        }
    }
    
    function revalidateStep(stepIndex) {
        if (wizardData.validatedSteps.indexOf(stepIndex) === -1 && 
            wizardData.completedSteps.indexOf(stepIndex) === -1) {
            return;
        }
        
        var isValid = checkStepValidity(stepIndex);
        if (isValid) {
            markStepCompleted(stepIndex);
        } else {
            unmarkStepCompleted(stepIndex);
        }
        updateStepIndicators();
    }
    
    var savedDraft = localStorage.getItem('apiConnectionWizardDraft');
    var pendingDraft = null;
    
    $('#apiConnectionWizard').smartWizard({
        selected: 0,
        theme: 'default',
        autoAdjustHeight: true,
        transition: {
            animation: 'fade',
            speed: '200'
        },
        toolbar: {
            position: 'bottom',
            showNextButton: true,
            showPreviousButton: true
        },
        anchor: {
            enableNavigation: true,
            enableAllAnchors: true,
            enableDoneState: false,
            markPreviousStepsAsDone: false,
            markAllPreviousStepsAsDone: false,
            removeDoneStepOnNavigateBack: false,
            enableDoneStateNavigation: true
        },
        keyboard: {
            keyNavigation: false
        },
        lang: {
            next: 'Next',
            previous: 'Previous'
        }
    });
    
    $('#apiConnectionWizard').on('leaveStep', function(e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {
        if (connectionCreated) return false;
        
        saveFormData();
        
        // Mark step as validated (user has left it at least once)
        markStepValidated(currentStepIndex);
        
        // Check validity and update step indicators (but don't block navigation)
        var isValid = checkStepValidity(currentStepIndex);
        if (isValid) {
            markStepCompleted(currentStepIndex);
        } else {
            unmarkStepCompleted(currentStepIndex);
        }
        updateStepIndicators();
        
        saveDraft();
        
        // Always allow navigation - users can skip steps freely
        return true;
    });
    
    $('#apiConnectionWizard').on('showStep', function(e, anchorObject, stepIndex, stepDirection) {
        updateStepIndicators();
        
        if (stepIndex === 5) {
            populateReview();
            
            var $toolbar = $('.toolbar-bottom');
            $toolbar.find('.sw-btn-next').text('Create Connection');
        } else {
            var $toolbar = $('.toolbar-bottom');
            $toolbar.find('.sw-btn-next').text('Next');
        }
        
        if (connectionCreated) {
            $('.toolbar-bottom').hide();
        }
    });
    
    $('#apiConnectionWizard').on('stepContent', function(e, anchorObject, stepIndex, stepDirection) {
        if (stepIndex === 5 && stepDirection === 'forward' && !connectionCreated) {
            if (validateStep(4)) {
                createConnection();
            }
        }
    });
    
    $('.sw-btn-next').on('click', function(e) {
        var currentStep = $('#apiConnectionWizard').smartWizard('getStepIndex');
        if (currentStep === 5 && !connectionCreated) {
            e.preventDefault();
            createConnection();
        }
    });
    
    function validateStep(stepIndex) {
        var isValid = true;
        
        if (stepIndex === 0) {
            var name = $('#apiName').val().trim();
            var subAccount = $('#subAccount').val();
            
            if (!name) {
                $('#apiName').addClass('is-invalid');
                isValid = false;
            } else {
                $('#apiName').removeClass('is-invalid');
            }
            
            if (!subAccount) {
                $('#subAccount').addClass('is-invalid');
                isValid = false;
            } else {
                $('#subAccount').removeClass('is-invalid');
            }
        }
        
        if (stepIndex === 1) {
            if (!wizardData.type) {
                $('#apiTypeError').show();
                isValid = false;
            } else {
                $('#apiTypeError').hide();
            }
        }
        
        if (stepIndex === 2) {
            if (!wizardData.authType) {
                $('#authTypeError').show();
                isValid = false;
            } else {
                $('#authTypeError').hide();
            }
        }
        
        if (stepIndex === 4) {
            var dlrUrl = $('#dlrUrl').val().trim();
            var inboundUrl = $('#inboundUrl').val().trim();
            
            if (dlrUrl && !dlrUrl.startsWith('https://')) {
                $('#dlrUrl').addClass('is-invalid');
                isValid = false;
            } else {
                $('#dlrUrl').removeClass('is-invalid');
            }
            
            if (inboundUrl && !inboundUrl.startsWith('https://')) {
                $('#inboundUrl').addClass('is-invalid');
                isValid = false;
            } else {
                $('#inboundUrl').removeClass('is-invalid');
            }
        }
        
        return isValid;
    }
    
    function saveFormData() {
        wizardData.name = $('#apiName').val().trim();
        wizardData.description = $('#apiDescription').val().trim();
        wizardData.subAccount = $('#subAccount').val();
        wizardData.environment = $('#environment').val();
        wizardData.ipAllowList = $('#enableIpRestriction').is(':checked');
        
        if (wizardData.ipAllowList) {
            var ipText = $('#allowedIps').val().trim();
            wizardData.allowedIps = ipText ? ipText.split('\n').map(function(ip) { return ip.trim(); }).filter(function(ip) { return ip; }) : [];
        } else {
            wizardData.allowedIps = [];
        }
        
        wizardData.dlrUrl = $('#dlrUrl').val().trim();
        wizardData.inboundUrl = $('#inboundUrl').val().trim();
    }
    
    function loadDraftToForm() {
        $('#apiName').val(wizardData.name);
        $('#apiDescription').val(wizardData.description);
        updateDescCharCount();
        $('#subAccount').val(wizardData.subAccount);
        $('#environment').val(wizardData.environment);
        
        if (wizardData.type) {
            selectApiType(wizardData.type);
            if (wizardData.integrationName) {
                selectPartner(wizardData.integrationName);
            }
        }
        
        if (wizardData.authType) {
            selectAuthType(wizardData.authType);
        }
        
        $('#enableIpRestriction').prop('checked', wizardData.ipAllowList);
        if (wizardData.ipAllowList) {
            $('#ipRestrictionFields').show();
            $('#allowedIps').val(wizardData.allowedIps.join('\n'));
        }
        
        $('#dlrUrl').val(wizardData.dlrUrl);
        $('#inboundUrl').val(wizardData.inboundUrl);
        
        updateStepIndicators();
    }
    
    function saveDraft() {
        var draft = {
            data: wizardData,
            skippedSteps: skippedSteps,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem('apiConnectionWizardDraft', JSON.stringify(draft));
        
        $('#autosaveIndicator').addClass('saving').removeClass('saved');
        $('#autosaveText').text('Saving...');
        
        setTimeout(function() {
            $('#autosaveIndicator').removeClass('saving').addClass('saved');
            $('#autosaveText').text('Draft saved');
        }, 500);
    }
    
    window.selectApiType = function(type) {
        console.log('[API Wizard] selectApiType called with:', type);
        wizardData.type = type;
        wizardData.integrationName = null;
        
        $('.selectable-tile[data-type]').removeClass('selected');
        $('.selectable-tile[data-type="' + type + '"]').addClass('selected');
        $('#apiTypeError').hide();
        
        if (type === 'integration') {
            console.log('[API Wizard] Showing integration partner section');
            $('#integrationPartnerSection').addClass('show');
        } else {
            $('#integrationPartnerSection').removeClass('show');
            $('.partner-tile').removeClass('selected');
        }
        revalidateStep(1);
    };
    
    window.selectPartner = function(partner) {
        wizardData.integrationName = partner;
        $('.partner-tile').removeClass('selected');
        $('.partner-tile[data-partner="' + partner + '"]').addClass('selected');
    };
    
    window.selectAuthType = function(authType) {
        wizardData.authType = authType;
        $('.selectable-tile[data-auth]').removeClass('selected');
        $('.selectable-tile[data-auth="' + authType + '"]').addClass('selected');
        $('#authTypeError').hide();
        revalidateStep(2);
    };
    
    window.skipStep = function(stepIndex) {
        if (skippedSteps.indexOf(stepIndex) === -1) {
            skippedSteps.push(stepIndex);
        }
        markStepCompleted(stepIndex);
        updateStepIndicators();
        saveDraft();
        $('#apiConnectionWizard').smartWizard('next');
    };
    
    $('#enableIpRestriction').on('change', function() {
        $('#ipRestrictionFields').slideToggle($(this).is(':checked'));
        saveFormData();
        revalidateStep(3);
    });
    
    $('#allowedIps').on('input', function() {
        saveFormData();
        revalidateStep(3);
    });
    
    $('#apiName').on('input', function() {
        wizardData.name = this.value.trim();
        revalidateStep(0);
    });
    
    $('#subAccount').on('change', function() {
        wizardData.subAccount = this.value;
        revalidateStep(0);
    });
    
    $('#dlrUrl, #inboundUrl').on('input', function() {
        saveFormData();
        revalidateStep(4);
    });
    
    $('#apiDescription').on('input', updateDescCharCount);
    
    function updateDescCharCount() {
        $('#descCharCount').text($('#apiDescription').val().length);
    }
    
    function populateReview() {
        saveFormData();
        
        $('#reviewName').text(wizardData.name || '-');
        $('#reviewDescription').text(wizardData.description || 'Not provided');
        $('#reviewSubAccount').text(wizardData.subAccount || '-');
        $('#reviewEnvironment').text(wizardData.environment === 'live' ? 'Live (Production)' : 'Test (Sandbox)');
        
        var typeText = getTypeLabel(wizardData.type);
        if (wizardData.integrationName) {
            typeText += ' - ' + wizardData.integrationName;
        }
        $('#reviewType').text(typeText);
        
        $('#reviewAuth').text(wizardData.authType || '-');
        
        if (wizardData.ipAllowList && wizardData.allowedIps.length > 0) {
            $('#reviewIpRestriction').text('Enabled (' + wizardData.allowedIps.length + ' IPs)');
        } else {
            $('#reviewIpRestriction').text('Not configured');
        }
        
        var webhooks = [];
        if (wizardData.dlrUrl) webhooks.push('Delivery Reports');
        if (wizardData.inboundUrl) webhooks.push('Inbound Messages');
        $('#reviewWebhooks').text(webhooks.length > 0 ? webhooks.join(', ') : 'Not configured');
    }
    
    function getTypeLabel(type) {
        switch(type) {
            case 'bulk': return 'Bulk API';
            case 'campaign': return 'Campaign API';
            case 'integration': return 'Integration';
            default: return type;
        }
    }
    
    function showSubmissionValidationSummary(incompleteSteps) {
        var visitedIncomplete = incompleteSteps.filter(function(s) { return s.visited; });
        var notVisited = incompleteSteps.filter(function(s) { return !s.visited; });
        
        var summaryHtml = '<div class="submission-validation-summary">';
        
        if (visitedIncomplete.length > 0) {
            summaryHtml += '<div class="mb-3">';
            summaryHtml += '<h6 class="text-danger mb-2"><i class="fas fa-times-circle me-1"></i> Steps with missing information:</h6>';
            summaryHtml += '<ul class="list-unstyled mb-0">';
            visitedIncomplete.forEach(function(s) {
                summaryHtml += '<li class="mb-1"><a href="#" class="validation-step-link text-decoration-none" data-step="' + (s.step - 1) + '"><i class="fas fa-arrow-right me-1"></i> Step ' + s.step + ': ' + s.name + '</a></li>';
            });
            summaryHtml += '</ul></div>';
        }
        
        if (notVisited.length > 0) {
            summaryHtml += '<div class="mb-3">';
            summaryHtml += '<h6 class="text-muted mb-2"><i class="fas fa-eye-slash me-1"></i> Steps not yet visited:</h6>';
            summaryHtml += '<ul class="list-unstyled mb-0">';
            notVisited.forEach(function(s) {
                summaryHtml += '<li class="mb-1"><a href="#" class="validation-step-link text-decoration-none" data-step="' + (s.step - 1) + '"><i class="fas fa-arrow-right me-1"></i> Step ' + s.step + ': ' + s.name + '</a></li>';
            });
            summaryHtml += '</ul></div>';
        }
        
        summaryHtml += '</div>';
        
        $('#validationSummaryContent').html(summaryHtml);
        
        // Add click handlers for step links
        $('.validation-step-link').on('click', function(e) {
            e.preventDefault();
            var stepIndex = parseInt($(this).data('step'));
            $('#validationSummaryModal').modal('hide');
            setTimeout(function() {
                $('#apiConnectionWizard').smartWizard('goToStep', stepIndex);
            }, 300);
        });
        
        $('#validationSummaryModal').modal('show');
    }
    
    function createConnection() {
        saveFormData();
        
        // Validate all steps before creating connection
        var stepNames = [
            'Core Configuration',
            'API Type',
            'Authentication',
            'Security Settings',
            'Webhooks'
        ];
        
        var incompleteSteps = [];
        var allValid = true;
        
        // Check each step (0-4, step 5 is Review)
        for (var i = 0; i <= 4; i++) {
            var isValid = checkStepValidity(i);
            
            if (!isValid) {
                allValid = false;
                var wasValidated = wizardData.validatedSteps.indexOf(i) > -1;
                
                incompleteSteps.push({
                    step: i + 1,
                    name: stepNames[i],
                    visited: wasValidated
                });
            }
        }
        
        if (!allValid) {
            showSubmissionValidationSummary(incompleteSteps);
            return false;
        }
        
        // All valid - proceed with connection creation
        var envPrefix = wizardData.environment === 'live' ? 'api' : 'sandbox';
        var typePrefix = wizardData.type === 'bulk' ? 'bulk' : (wizardData.type === 'campaign' ? 'campaigns' : 'integrations');
        var connId = wizardData.environment === 'live' ? 'prod-' + Math.floor(Math.random() * 1000).toString().padStart(3, '0') : 'test-' + Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        
        var baseUrl = 'https://' + envPrefix + '.quicksms.io/v1/' + typePrefix + '/' + connId;
        
        $('#createdBaseUrl').text(baseUrl);
        
        if (wizardData.authType === 'API Key') {
            var apiKey = generateApiKey();
            $('#createdApiKey').text(apiKey);
            $('#createdApiKeyRow').show();
            $('#createdUsernameRow, #createdPasswordRow').hide();
        } else {
            var username = 'api_user_' + Math.floor(Math.random() * 10000);
            var password = generatePassword();
            $('#createdUsername').text(username);
            $('#createdPassword').text(password);
            $('#createdUsernameRow, #createdPasswordRow').show();
            $('#createdApiKeyRow').hide();
        }
        
        console.log('[AUDIT] API Connection created:', wizardData.name, 'Type:', wizardData.type, 'at:', new Date().toISOString());
        
        localStorage.removeItem('apiConnectionWizardDraft');
        
        connectionCreated = true;
        $('#reviewSection').hide();
        $('#completionSection').show();
        $('.toolbar-bottom').hide();
    }
    
    function generateApiKey() {
        var prefix = wizardData.environment === 'live' ? 'sk_live_' : 'sk_test_';
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var key = '';
        for (var i = 0; i < 32; i++) {
            key += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return prefix + key;
    }
    
    function generatePassword() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        var password = '';
        for (var i = 0; i < 16; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return password;
    }
    
    window.copyCredential = function(elementId) {
        var text = $('#' + elementId).text();
        navigator.clipboard.writeText(text).then(function() {
            var $btn = $('#' + elementId).siblings('button');
            var originalHtml = $btn.html();
            $btn.html('<i class="fas fa-check text-success"></i>');
            setTimeout(function() {
                $btn.html(originalHtml);
            }, 1500);
        });
    };
    
    $('#resumeDraftBtn').on('click', function() {
        if (pendingDraft) {
            wizardData = pendingDraft.data || wizardData;
            skippedSteps = pendingDraft.skippedSteps || [];
            loadDraftToForm();
        }
        $('#resumeDraftModal').modal('hide');
    });
    
    $('#discardDraftBtn').on('click', function() {
        localStorage.removeItem('apiConnectionWizardDraft');
        pendingDraft = null;
        $('#resumeDraftModal').modal('hide');
    });
    
    if (savedDraft) {
        try {
            pendingDraft = JSON.parse(savedDraft);
            if (pendingDraft && pendingDraft.data) {
                $('#draftApiName').text(pendingDraft.data.name || 'Untitled Connection');
                if (pendingDraft.timestamp) {
                    var draftDate = new Date(pendingDraft.timestamp);
                    $('#draftTimestamp').text(draftDate.toLocaleString());
                }
                $('#resumeDraftModal').modal('show');
            }
        } catch(e) {
            localStorage.removeItem('apiConnectionWizardDraft');
        }
    }
});
</script>
@endpush

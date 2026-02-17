@extends('layouts.admin')

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
    border: 0.125rem solid #1e3a5f;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: #1e3a5f;
    position: relative;
    z-index: 10;
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
    background: #1e3a5f;
    color: #fff;
    border-color: #1e3a5f;
}
.form-wizard .nav-wizard li .nav-link.active:after,
.form-wizard .nav-wizard li .nav-link.done:after {
    background: #1e3a5f !important;
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
.form-wizard .nav-wizard li .nav-link.step-not-visited span {
    background: #fff !important;
    color: #1e3a5f !important;
    border-color: #1e3a5f !important;
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
    background: #e9ecef !important;
}
.form-wizard .nav-wizard li .nav-link.step-completed span {
    background: #1e3a5f !important;
    color: #fff !important;
    border-color: #1e3a5f !important;
}
.form-wizard .nav-wizard li .nav-link.step-completed:after {
    background: #1e3a5f !important;
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
    background-color: #1e3a5f !important;
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
    background-color: #2d5a87 !important;
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
#apiConnectionWizard > .tab-content {
    height: auto !important;
    overflow: visible !important;
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
    border-color: #1e3a5f;
    background: rgba(30, 58, 95, 0.05);
}
.selectable-tile.selected {
    border-color: #1e3a5f;
    background: rgba(30, 58, 95, 0.1);
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
.bg-pastel-primary { background: rgba(30, 58, 95, 0.15); color: #1e3a5f; }
.bg-pastel-warning { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
.bg-pastel-info { background: rgba(23, 162, 184, 0.15); color: #117a8b; }
.bg-pastel-success { background: rgba(40, 167, 69, 0.15); color: #28a745; }
.bg-pastel-secondary { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
.alert-pastel-primary {
    background-color: rgba(30, 58, 95, 0.1);
    border-color: rgba(30, 58, 95, 0.2);
    color: #1e3a5f;
}
.ip-address-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(30, 58, 95, 0.1);
    border: 1px solid rgba(30, 58, 95, 0.3);
    border-radius: 1rem;
    padding: 0.35rem 0.75rem;
    margin: 0.25rem;
    font-size: 0.875rem;
    font-family: monospace;
}
.ip-address-badge .remove-btn {
    background: none;
    border: none;
    color: #dc3545;
    padding: 0 0 0 0.5rem;
    cursor: pointer;
    font-size: 0.75rem;
}
.ip-address-badge .remove-btn:hover {
    color: #a71d2a;
}
.ip-addresses-container {
    min-height: 40px;
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
    border-color: #1e3a5f;
    background: rgba(30, 58, 95, 0.05);
}
.partner-tile.selected {
    border-color: #1e3a5f;
    background: rgba(30, 58, 95, 0.1);
}
.partner-tile .partner-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #1e3a5f;
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
.account-selector-section {
    background: rgba(30, 58, 95, 0.08);
    border: 2px solid rgba(30, 58, 95, 0.2);
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.account-selector-section .section-label {
    font-weight: 600;
    color: #1e3a5f;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}
.account-selector-section .section-label i {
    margin-right: 0.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.api.connections') }}">API Connections</a></li>
            <li class="breadcrumb-item active">Create Connection</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-plug me-2" style="color: #1e3a5f;"></i>Create API Connection</h4>
                    <span class="autosave-indicator saved" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <div class="card-body">
                    <div id="apiConnectionWizard" class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link" href="#step-1"><span>1</span><small>Account</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-2"><span>2</span><small>Basics</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-3"><span>3</span><small>Type</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-4"><span>4</span><small>Auth</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-5"><span>5</span><small>Security</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-6"><span>6</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <div id="step-1" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 1: Select Account</strong> - Choose the customer account for this API connection.
                                        </div>
                                        
                                        <div class="account-selector-section">
                                            <div class="section-label"><i class="fas fa-building"></i>Customer Account</div>
                                            <p class="text-muted small mb-3">Select the customer account that will own this API connection.</p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Account <span class="text-danger">*</span></label>
                                                <select class="form-select" id="accountSelect">
                                                    <option value="">Search and select an account...</option>
                                                </select>
                                                <div class="invalid-feedback">Please select an account.</div>
                                            </div>
                                            
                                            <div id="selectedAccountInfo" style="display: none;">
                                                <div class="card border-0 bg-white">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-pastel-primary rounded-circle p-2 me-3">
                                                                <i class="fas fa-building"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0" id="selectedAccountName">-</h6>
                                                                <small class="text-muted" id="selectedAccountId">-</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-2" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 2: Core Configuration</strong> - Define your API connection's name, description, and environment.
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
                                            
                                            <div class="col-lg-6 mb-3" id="subAccountWrapper" style="display: none;">
                                                <label class="form-label">Sub-Account</label>
                                                <select class="form-select" id="subAccount">
                                                    <option value="">None (main account)</option>
                                                </select>
                                                <small class="text-muted">Sub-accounts are filtered by the selected customer account.</small>
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
                            
                            <div id="step-3" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 3: Connection Type</strong> - Select the API type based on your integration needs.
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
                                        
                                        <div id="integrationPartnerSection" style="display: none;">
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
                            
                            <div id="step-4" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-10 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 4: Authentication</strong> - Choose how this API connection will authenticate requests.
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
                            
                            <div id="step-5" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 5: Security Controls</strong> <span class="badge bg-pastel-primary ms-2">Optional</span>
                                            <p class="mb-0 mt-2 small">Restrict API access to specific IP addresses. You can skip this step if you don't need IP restrictions.</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-medium">Allowed IP Addresses</label>
                                            <p class="text-muted small mb-2">Add IP addresses that are allowed to make API requests. Leave empty to allow all IPs.</p>
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" id="ipAddressInput" placeholder="e.g., 192.168.1.100">
                                                <button class="btn btn-outline-primary" type="button" id="addIpBtn">Add IP</button>
                                            </div>
                                            <div class="ip-addresses-container" id="ipAddressesContainer"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="step-6" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-8 mx-auto">
                                        <div class="alert alert-pastel-primary mb-4">
                                            <strong>Step 6: Review & Create</strong> - Review your configuration before creating the API connection.
                                        </div>
                                        
                                        <div class="card mb-3" style="border-left: 3px solid #1e3a5f;">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-building me-2" style="color: #1e3a5f;"></i>Account</h6>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted">Customer Account</small>
                                                        <p class="mb-0 fw-medium" id="reviewAccount">-</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Sub-Account</small>
                                                        <p class="mb-0" id="reviewSubAccount">-</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-info-circle me-2" style="color: #1e3a5f;"></i>Configuration</h6>
                                                <div class="row">
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">API Name</small>
                                                        <p class="mb-0 fw-medium" id="reviewApiName">-</p>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Environment</small>
                                                        <p class="mb-0" id="reviewEnvironment">-</p>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Type</small>
                                                        <p class="mb-0" id="reviewType">-</p>
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <small class="text-muted">Authentication</small>
                                                        <p class="mb-0" id="reviewAuth">-</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-shield-alt me-2" style="color: #1e3a5f;"></i>Security</h6>
                                                <small class="text-muted">IP Restrictions</small>
                                                <p class="mb-0" id="reviewIpRestrictions">None (all IPs allowed)</p>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-primary btn-lg" id="createConnectionBtn" style="background-color: #1e3a5f; border-color: #1e3a5f;">
                                                <i class="fas fa-check me-2"></i>Create API Connection
                                            </button>
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

<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="completion-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h4 class="mb-3">API Connection Created!</h4>
                <p class="text-muted mb-4">Your API connection has been successfully created.</p>
                
                <div class="credential-box text-start">
                    <div class="credential-row">
                        <span class="credential-label">Base URL</span>
                        <div class="d-flex align-items-center">
                            <code class="credential-value me-2" id="generatedBaseUrl">-</code>
                            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('generatedBaseUrl')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="credential-row" id="generatedApiKeyRow">
                        <span class="credential-label">API Key</span>
                        <div class="d-flex align-items-center">
                            <code class="credential-value me-2" id="generatedApiKey">-</code>
                            <button class="btn btn-sm btn-outline-primary" onclick="copyGeneratedKey()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="credential-row" id="generatedUsernameRow" style="display: none;">
                        <span class="credential-label">Username</span>
                        <div class="d-flex align-items-center">
                            <code class="credential-value me-2" id="generatedUsername">-</code>
                            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('generatedUsername')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="credential-row" id="generatedPasswordRow" style="display: none;">
                        <span class="credential-label">Password</span>
                        <div class="d-flex align-items-center">
                            <code class="credential-value me-2" id="generatedPassword">-</code>
                            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('generatedPassword')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3 text-start">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Copy this API key now. You won't be able to see it again.
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('admin.api.connections') }}" class="btn btn-primary" style="background-color: #1e3a5f; border-color: #1e3a5f;">
                    <i class="fas fa-arrow-left me-2"></i>Back to API Connections
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
<script>
$(document).ready(function() {
    var allAccounts = @json($accounts ?? []);
    
    var subAccountsByAccountRaw = @json($subAccountsByAccount ?? (object)[]);
    var subAccountsByAccount = {};
    Object.keys(subAccountsByAccountRaw).forEach(function(accId) {
        subAccountsByAccount[accId] = subAccountsByAccountRaw[accId];
    });
    
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    var csrfValue = csrfToken ? csrfToken.getAttribute('content') : '';
    
    var formData = {
        account: null,
        accountId: null,
        apiName: '',
        description: '',
        subAccount: '',
        environment: 'test',
        apiType: null,
        integrationPartner: null,
        authType: null,
        ipAddresses: []
    };
    
    allAccounts.forEach(function(acc) {
        var label = acc.name + (acc.account_number ? ' (' + acc.account_number + ')' : '');
        $('#accountSelect').append('<option value="' + acc.id + '">' + label + '</option>');
    });
    
    $('#accountSelect').on('change', function() {
        var selectedId = $(this).val();
        if (selectedId) {
            var account = allAccounts.find(function(a) { return a.id === selectedId; });
            if (account) {
                formData.accountId = selectedId;
                formData.account = account.name;
                $('#selectedAccountName').text(account.name);
                $('#selectedAccountId').text(account.account_number || selectedId);
                $('#selectedAccountInfo').show();
                
                $('#subAccount').empty().append('<option value="">None (main account)</option>');
                var subs = subAccountsByAccount[selectedId];
                if (subs && subs.length > 0) {
                    subs.forEach(function(sub) {
                        $('#subAccount').append('<option value="' + sub.id + '">' + sub.name + '</option>');
                    });
                    $('#subAccountWrapper').show();
                } else {
                    $('#subAccountWrapper').hide();
                }
            }
        } else {
            formData.accountId = null;
            formData.account = null;
            $('#selectedAccountInfo').hide();
            $('#subAccount').empty().append('<option value="">None (main account)</option>');
            $('#subAccountWrapper').hide();
        }
    });
    
    $('#apiDescription').on('input', function() {
        $('#descCharCount').text($(this).val().length);
    });
    
    window.selectApiType = function(type) {
        formData.apiType = type;
        $('.selectable-tile[data-type]').removeClass('selected');
        $('.selectable-tile[data-type="' + type + '"]').addClass('selected');
        $('#apiTypeError').hide();
        
        if (type === 'integration') {
            $('#integrationPartnerSection').show();
        } else {
            $('#integrationPartnerSection').hide();
            formData.integrationPartner = null;
            $('.partner-tile').removeClass('selected');
        }
    };
    
    window.selectPartner = function(partner) {
        formData.integrationPartner = partner;
        $('.partner-tile').removeClass('selected');
        $('.partner-tile[data-partner="' + partner + '"]').addClass('selected');
    };
    
    window.selectAuthType = function(type) {
        formData.authType = type;
        $('.selectable-tile[data-auth]').removeClass('selected');
        $('.selectable-tile[data-auth="' + type + '"]').addClass('selected');
        $('#authTypeError').hide();
    };
    
    $('#addIpBtn').on('click', function() {
        var ip = $('#ipAddressInput').val().trim();
        if (ip && isValidIp(ip) && !formData.ipAddresses.includes(ip)) {
            formData.ipAddresses.push(ip);
            renderIpAddresses();
            $('#ipAddressInput').val('');
        }
    });
    
    function isValidIp(ip) {
        var pattern = /^(\d{1,3}\.){3}\d{1,3}$/;
        return pattern.test(ip);
    }
    
    function renderIpAddresses() {
        var html = '';
        formData.ipAddresses.forEach(function(ip, idx) {
            html += '<span class="ip-address-badge">' + ip + 
                    '<button class="remove-btn" onclick="removeIp(' + idx + ')"><i class="fas fa-times"></i></button></span>';
        });
        $('#ipAddressesContainer').html(html);
    }
    
    window.removeIp = function(idx) {
        formData.ipAddresses.splice(idx, 1);
        renderIpAddresses();
    };
    
    function updateReviewStep() {
        $('#reviewAccount').text(formData.account || '-');
        $('#reviewSubAccount').text($('#subAccount').val() || '-');
        $('#reviewApiName').text($('#apiName').val() || '-');
        $('#reviewEnvironment').text($('#environment').val() === 'live' ? 'Live (Production)' : 'Test (Sandbox)');
        
        var typeLabel = '';
        if (formData.apiType === 'bulk') typeLabel = 'Bulk API';
        else if (formData.apiType === 'campaign') typeLabel = 'Campaign API';
        else if (formData.apiType === 'integration') typeLabel = 'Integration' + (formData.integrationPartner ? ' (' + formData.integrationPartner + ')' : '');
        $('#reviewType').text(typeLabel || '-');
        
        $('#reviewAuth').text(formData.authType || '-');
        
        if (formData.ipAddresses.length > 0) {
            $('#reviewIpRestrictions').text(formData.ipAddresses.join(', '));
        } else {
            $('#reviewIpRestrictions').text('None (all IPs allowed)');
        }
    }
    
    $('#apiConnectionWizard').smartWizard({
        selected: 0,
        theme: 'default',
        autoAdjustHeight: false,
        transition: {
            animation: 'none'
        },
        toolbar: {
            position: 'bottom'
        },
        anchor: {
            enableNavigation: true,
            enableNavigationAlways: true,
            enableDoneState: true,
            markPreviousStepsAsDone: true,
            enableDoneStateNavigation: true
        }
    });

    function resetWizardHeight() {
        var tc = document.querySelector('#apiConnectionWizard > .tab-content');
        if (tc) tc.style.setProperty('height', 'auto', 'important');
    }
    setTimeout(function() {
        var sw = $('#apiConnectionWizard').data('smartWizard');
        if (sw) sw._fixHeight = function() {};
        resetWizardHeight();
    }, 0);

    $('#apiConnectionWizard').on('showStep', function() {
        resetWizardHeight();
        setTimeout(resetWizardHeight, 50);
    });

    $('#apiConnectionWizard').on('leaveStep', function(e, anchorObject, currentStepIndex, nextStepIndex) {
        if (nextStepIndex > currentStepIndex) {
            if (currentStepIndex === 0) {
                if (!$('#accountSelect').val()) {
                    $('#accountSelect').addClass('is-invalid');
                    return false;
                }
                $('#accountSelect').removeClass('is-invalid');
            }
            if (currentStepIndex === 1) {
                var valid = true;
                if (!$('#apiName').val().trim()) {
                    $('#apiName').addClass('is-invalid');
                    valid = false;
                } else {
                    $('#apiName').removeClass('is-invalid');
                }
                return valid;
            }
            if (currentStepIndex === 2 && !formData.apiType) {
                $('#apiTypeError').show();
                return false;
            }
            if (currentStepIndex === 3 && !formData.authType) {
                $('#authTypeError').show();
                return false;
            }
        }
        return true;
    });
    
    $('#apiConnectionWizard').on('showStep', function(e, anchorObject, stepIndex) {
        if (stepIndex === 5) {
            updateReviewStep();
        }
    });
    
    $('#createConnectionBtn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Creating...');
        
        var authTypeMap = { 'API Key': 'api_key', 'Basic Auth': 'basic_auth' };
        var partnerMap = { 'SystmOne': 'systmone', 'Rio': 'rio', 'EMIS': 'emis', 'Accurx': 'accurx' };
        
        var payload = {
            account_id: formData.accountId,
            name: $('#apiName').val().trim(),
            description: $('#apiDescription').val().trim() || null,
            sub_account_id: $('#subAccount').val() || null,
            type: formData.apiType,
            auth_type: authTypeMap[formData.authType] || formData.authType,
            environment: $('#environment').val() || 'test',
            ip_allowlist_enabled: formData.ipAddresses.length > 0,
            ip_allowlist: formData.ipAddresses.length > 0 ? formData.ipAddresses : [],
            partner_name: formData.integrationPartner ? (partnerMap[formData.integrationPartner] || formData.integrationPartner.toLowerCase()) : null,
            partner_config: null
        };
        
        $.ajax({
            url: '/admin/api/api-connections',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfValue, 'Accept': 'application/json' },
            data: JSON.stringify(payload),
            success: function(response) {
                var data = response.data;
                var credentials = data.credentials || {};
                
                $('#generatedBaseUrl').text(data.base_url || (data.id + '.api.quicksms.com'));
                
                if (data.auth_type === 'api_key' && credentials.api_key) {
                    $('#generatedApiKey').text(credentials.api_key);
                    $('#generatedApiKeyRow').show();
                    $('#generatedUsernameRow, #generatedPasswordRow').hide();
                } else if (credentials.username && credentials.password) {
                    $('#generatedUsername').text(credentials.username);
                    $('#generatedPassword').text(credentials.password);
                    $('#generatedUsernameRow, #generatedPasswordRow').show();
                    $('#generatedApiKeyRow').hide();
                }
                
                $('#successModal').modal('show');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Create API Connection');
                var msg = 'Failed to create API connection.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.errors) {
                        var errorMessages = [];
                        Object.keys(resp.errors).forEach(function(key) {
                            errorMessages.push(resp.errors[key].join(', '));
                        });
                        msg = errorMessages.join('; ');
                    } else if (resp.message) {
                        msg = resp.message;
                    }
                } catch(e) {}
                alert(msg);
            }
        });
    });
    
    window.copyToClipboard = function(elementId) {
        var text = $('#' + elementId).text();
        navigator.clipboard.writeText(text).then(function() {
            var btn = $('#' + elementId).closest('.d-flex').find('.btn-outline-primary');
            var originalHtml = btn.html();
            btn.html('<i class="fas fa-check text-success"></i>');
            setTimeout(function() {
                btn.html(originalHtml);
            }, 1500);
        });
    };

    window.copyGeneratedKey = function() {
        copyToClipboard('generatedApiKey');
    };
    
    console.log('[Admin API Connection Wizard] Loaded - Create connections for any customer account');
});
</script>
@endpush

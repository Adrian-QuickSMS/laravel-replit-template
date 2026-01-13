@extends('layouts.quicksms')

@section('title', 'SMS SenderID Registration')

@push('styles')
<style>
.senderid-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.senderid-header h2 {
    margin: 0;
    font-weight: 600;
}
.senderid-header p {
    margin: 0;
    color: #6c757d;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: var(--primary);
}
.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.senderid-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}
.senderid-table {
    width: 100%;
    margin: 0;
    min-width: 800px;
    table-layout: fixed;
}
.senderid-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.senderid-table thead th:first-child { width: 16%; }
.senderid-table thead th:nth-child(2) { width: 11%; }
.senderid-table thead th:nth-child(3) { width: 16%; }
.senderid-table thead th:nth-child(4) { width: 12%; }
.senderid-table thead th:nth-child(5) { width: 11%; }
.senderid-table thead th:nth-child(6) { width: 13%; }
.senderid-table thead th:nth-child(7) { width: 11%; }
.senderid-table thead th:last-child { 
    width: 7%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.senderid-table thead th:hover {
    background: #e9ecef;
}
.senderid-table thead th:last-child:hover {
    background: #f8f9fa;
}
.senderid-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.senderid-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.senderid-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
}
.senderid-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.senderid-table tbody tr:last-child td {
    border-bottom: none;
}
.senderid-table tbody tr:hover td {
    background: #f8f9fa;
}
.senderid-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.senderid-name {
    font-weight: 600;
    color: #343a40;
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    letter-spacing: 0.5px;
}
.badge-draft {
    background: rgba(173, 181, 189, 0.15);
    color: #868e96;
}
.badge-pending {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-approved {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-rejected {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-suspended {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-archived {
    background: rgba(52, 58, 64, 0.15);
    color: #495057;
}
.badge-otp {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-marketing {
    background: rgba(255, 107, 107, 0.15);
    color: #ff6b6b;
}
.badge-transactional {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-alerts {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-alphanumeric {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-numeric {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-shortcode {
    background: rgba(255, 107, 107, 0.15);
    color: #ff6b6b;
}
.drawer-wide {
    width: 500px;
}
.wizard-steps {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding: 0 0.5rem;
}
.wizard-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.wizard-step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}
.wizard-step.active .wizard-step-number {
    background: var(--primary);
    color: #fff;
}
.wizard-step.completed .wizard-step-number {
    background: #1cbb8c;
    color: #fff;
}
.wizard-step.completed .wizard-step-number::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}
.wizard-step-label {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 0.25rem;
    text-align: center;
}
.wizard-step.active .wizard-step-label {
    color: var(--primary);
    font-weight: 600;
}
.wizard-step-connector {
    flex: 1;
    height: 2px;
    background: #e9ecef;
    margin: 0 0.25rem;
    margin-bottom: 1rem;
}
.wizard-step-connector.completed {
    background: #1cbb8c;
}
.wizard-panel-title {
    color: #343a40;
    margin-bottom: 0.25rem;
    font-weight: 600;
}
.review-summary {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
}
.review-section {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}
.review-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.review-section-title {
    font-weight: 600;
    font-size: 0.85rem;
    color: #495057;
    margin-bottom: 0.5rem;
}
.review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.25rem 0;
    font-size: 0.85rem;
}
.review-label {
    color: #6c757d;
}
.review-value {
    color: #343a40;
    font-weight: 500;
    text-align: right;
}
.wizard-nav-buttons {
    display: flex;
    gap: 0.5rem;
}
.type-selector {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.type-card {
    flex: 1;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
    text-align: center;
}
.type-card:hover {
    border-color: rgba(136, 108, 192, 0.5);
    background: rgba(136, 108, 192, 0.02);
}
.type-card.selected {
    border-color: var(--primary);
    background: rgba(136, 108, 192, 0.08);
}
.type-card-icon {
    font-size: 1.25rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}
.type-card.selected .type-card-icon {
    color: var(--primary);
}
.type-card-title {
    font-weight: 600;
    font-size: 0.8rem;
    color: #343a40;
    margin-bottom: 0.15rem;
}
.type-card-desc {
    font-size: 0.7rem;
    color: #6c757d;
    line-height: 1.3;
}
.search-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
    flex-wrap: wrap;
}
.search-box {
    flex: 1;
    max-width: 300px;
    min-width: 200px;
}
.filters-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0 0 0.75rem 0.75rem;
}
.drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}
.drawer-backdrop.show {
    opacity: 1;
    visibility: visible;
}
.drawer {
    position: fixed;
    top: 0;
    right: -500px;
    width: 500px;
    max-width: 90vw;
    height: 100%;
    background: #fff;
    z-index: 1050;
    transition: right 0.3s ease;
    display: flex;
    flex-direction: column;
    box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
}
.drawer.show {
    right: 0;
}
.drawer-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}
.drawer-header h5 {
    margin: 0;
    font-weight: 600;
}
.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
}
.drawer-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}
.form-label-required::after {
    content: ' *';
    color: #dc3545;
}
.senderid-input {
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    letter-spacing: 0.5px;
}
.char-counter {
    font-size: 0.75rem;
    color: #6c757d;
}
.char-counter.warning {
    color: #ffc107;
}
.char-counter.danger {
    color: #dc3545;
}
.normalisation-preview {
    font-size: 0.8rem;
    color: #1cbb8c;
    background: rgba(28, 187, 140, 0.1);
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    margin-top: 0.5rem;
}
.normalisation-preview i {
    color: #1cbb8c;
}
.validation-hint {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
.detail-row {
    display: flex;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    flex: 0 0 140px;
    font-weight: 500;
    color: #6c757d;
    font-size: 0.85rem;
}
.detail-value {
    flex: 1;
    color: #343a40;
    font-size: 0.85rem;
}
.audit-timeline {
    position: relative;
    padding-left: 1.5rem;
}
.audit-timeline::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.audit-item {
    position: relative;
    padding-bottom: 1rem;
}
.audit-item:last-child {
    padding-bottom: 0;
}
.audit-item::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid var(--primary);
}
.audit-item.approved::before {
    border-color: #1cbb8c;
    background: rgba(28, 187, 140, 0.2);
}
.audit-item.rejected::before {
    border-color: #dc3545;
    background: rgba(220, 53, 69, 0.2);
}
.audit-item.submitted::before {
    border-color: #3065D0;
    background: rgba(48, 101, 208, 0.2);
}
.audit-time {
    font-size: 0.75rem;
    color: #6c757d;
}
.audit-action {
    font-weight: 500;
    color: #343a40;
    font-size: 0.85rem;
}
.audit-user {
    font-size: 0.8rem;
    color: #6c757d;
}
.info-banner {
    background: rgba(136, 108, 192, 0.08);
    border: 1px solid rgba(136, 108, 192, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.info-banner h6 {
    color: #886CC0;
    margin-bottom: 0.5rem;
    font-weight: 600;
}
.info-banner ul {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.85rem;
    color: #495057;
}
.info-banner li {
    margin-bottom: 0.25rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="senderid-header">
        <div>
            <h2>SMS SenderID Registration</h2>
            <p>Register and manage approved sender identities for SMS messaging</p>
        </div>
        <button type="button" class="btn btn-primary" id="btnRegisterSenderId">
            <i class="fas fa-plus me-2"></i>Register SenderID
        </button>
    </div>

    <div class="info-banner">
        <h6><i class="fas fa-shield-alt me-2"></i>UK Compliance Requirements</h6>
        <ul>
            <li>All SenderIDs must be registered and approved before use</li>
            <li><strong>Alphanumeric:</strong> Max 11 characters (A-Z, a-z, 0-9, . - _ & space)</li>
            <li><strong>Numeric:</strong> UK Virtual Mobile Number (+447xxxxxxxxx)</li>
            <li><strong>Shortcode:</strong> Exactly 5 digits, starting with 6, 7, or 8</li>
            <li>Must represent your brand and not impersonate others</li>
        </ul>
    </div>

    <div id="emptyState" class="empty-state" style="display: none;">
        <div class="empty-state-icon">
            <i class="fas fa-id-badge"></i>
        </div>
        <h4>No SenderIDs Registered</h4>
        <p>Register your first SenderID to start sending SMS messages with your brand identity.</p>
        <button type="button" class="btn btn-primary" id="btnRegisterSenderIdEmpty">
            <i class="fas fa-plus me-2"></i>Register SenderID
        </button>
    </div>

    <div id="senderIdLibrary" class="senderid-table-container">
        <div class="search-filter-bar">
            <div class="search-box">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search SenderIDs...">
                </div>
            </div>
            <div class="filters-group">
                <select class="form-select form-select-sm" id="filterType" style="width: 130px;">
                    <option value="">All Types</option>
                    <option value="alphanumeric">Alphanumeric</option>
                    <option value="numeric">Numeric</option>
                    <option value="shortcode">Shortcode</option>
                </select>
                <select class="form-select form-select-sm" id="filterStatus" style="width: 140px;">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="suspended">Suspended</option>
                    <option value="archived">Archived</option>
                </select>
                <select class="form-select form-select-sm" id="filterUseCase" style="width: 130px;">
                    <option value="">All Use Cases</option>
                    <option value="otp">OTP / Verification</option>
                    <option value="marketing">Marketing</option>
                    <option value="transactional">Transactional</option>
                    <option value="alerts">Alerts / Notifications</option>
                </select>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table senderid-table" id="senderIdTable">
                <thead>
                    <tr>
                        <th data-sort="senderId" onclick="sortTable('senderId')">SenderID <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="type" onclick="sortTable('type')">Type <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="brand" onclick="sortTable('brand')">Brand / Business <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="useCase" onclick="sortTable('useCase')">Use Case <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status" onclick="sortTable('status')">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="created" onclick="sortTable('created')">Created <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="lastUsed" onclick="sortTable('lastUsed')">Last Used <i class="fas fa-sort sort-icon"></i></th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="senderIdTableBody">
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <div class="text-muted small">
                Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> SenderIDs
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="drawer-backdrop" id="registerDrawerBackdrop"></div>
<div class="drawer drawer-wide" id="registerDrawer">
    <div class="drawer-header">
        <h5><i class="fas fa-id-badge me-2 text-primary"></i>Register SenderID</h5>
        <button type="button" class="btn-close" id="registerDrawerClose"></button>
    </div>
    <div class="drawer-body">
        <div class="wizard-steps">
            <div class="wizard-step active" data-step="1">
                <div class="wizard-step-number">1</div>
                <div class="wizard-step-label">SenderID</div>
            </div>
            <div class="wizard-step-connector"></div>
            <div class="wizard-step" data-step="2">
                <div class="wizard-step-number">2</div>
                <div class="wizard-step-label">Business</div>
            </div>
            <div class="wizard-step-connector"></div>
            <div class="wizard-step" data-step="3">
                <div class="wizard-step-number">3</div>
                <div class="wizard-step-label">Permission</div>
            </div>
            <div class="wizard-step-connector"></div>
            <div class="wizard-step" data-step="4">
                <div class="wizard-step-number">4</div>
                <div class="wizard-step-label">Use Case</div>
            </div>
            <div class="wizard-step-connector"></div>
            <div class="wizard-step" data-step="5">
                <div class="wizard-step-number">5</div>
                <div class="wizard-step-label">Review</div>
            </div>
        </div>

        <form id="registerForm">
            <div class="wizard-panel" data-panel="1">
                <h6 class="wizard-panel-title">SenderID Type & Value</h6>
                <p class="text-muted small mb-3">Choose the type of SenderID and enter the value you wish to register.</p>
                
                <div class="mb-3">
                    <label class="form-label form-label-required">SenderID Type</label>
                    <div class="type-selector">
                        <div class="type-card selected" data-type="alphanumeric">
                            <div class="type-card-icon"><i class="fas fa-font"></i></div>
                            <div class="type-card-title">Alphanumeric</div>
                            <div class="type-card-desc">Text-based ID<br>e.g. MYBRAND</div>
                        </div>
                        <div class="type-card" data-type="numeric">
                            <div class="type-card-icon"><i class="fas fa-phone"></i></div>
                            <div class="type-card-title">Numeric</div>
                            <div class="type-card-desc">UK Virtual Mobile<br>e.g. +447700...</div>
                        </div>
                        <div class="type-card" data-type="shortcode">
                            <div class="type-card-icon"><i class="fas fa-hashtag"></i></div>
                            <div class="type-card-title">Shortcode</div>
                            <div class="type-card-desc">Short number<br>e.g. 60123</div>
                        </div>
                    </div>
                    <input type="hidden" id="inputType" value="alphanumeric">
                </div>

                <div class="mb-3">
                    <label class="form-label form-label-required">SenderID Value</label>
                    <div id="senderIdInputWrapper">
                        <input type="text" class="form-control senderid-input" id="inputSenderId" 
                               maxlength="11" placeholder="e.g. MyBrand" autocomplete="off">
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="validation-hint" id="senderIdHint">Max 11 characters: A-Z a-z 0-9 . - _ & space</div>
                        <div class="char-counter" id="charCounterWrapper"><span id="senderIdCharCount">0</span>/11</div>
                    </div>
                    <div class="normalisation-preview" id="normalisationPreview" style="display: none;">
                        <i class="fas fa-arrow-right me-1"></i>Will be registered as: <strong id="normalisedValue"></strong>
                    </div>
                    <div class="invalid-feedback" id="senderIdError"></div>
                </div>
            </div>

            <div class="wizard-panel" data-panel="2" style="display: none;">
                <h6 class="wizard-panel-title">Brand / Business Association</h6>
                <p class="text-muted small mb-3">Associate this SenderID with your business entity.</p>
                
                <div class="mb-3">
                    <label class="form-label form-label-required">Brand / Business Name</label>
                    <input type="text" class="form-control" id="inputBrand" 
                           placeholder="Your company or brand name" autocomplete="off">
                    <div class="validation-hint">The legal entity or brand this SenderID represents</div>
                    <div class="invalid-feedback" id="brandError"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Country</label>
                    <input type="text" class="form-control" value="United Kingdom" readonly disabled>
                    <div class="validation-hint">SenderID registrations are currently available for UK only</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subaccount</label>
                    <select class="form-select" id="inputSubaccount">
                        <option value="">Main Account</option>
                        <option value="marketing">Marketing Department</option>
                        <option value="support">Customer Support</option>
                        <option value="operations">Operations</option>
                    </select>
                    <div class="validation-hint">Optionally assign to a subaccount for billing/reporting</div>
                </div>
            </div>

            <div class="wizard-panel" data-panel="3" style="display: none;">
                <h6 class="wizard-panel-title">Permission Confirmation</h6>
                <p class="text-muted small mb-3">Confirm your authorisation to use this SenderID.</p>
                
                <div class="mb-3">
                    <label class="form-label form-label-required">Do you have permission to use this SenderID?</label>
                    <select class="form-select" id="inputPermission">
                        <option value="">Select...</option>
                        <option value="yes">Yes - I am authorised to use this SenderID</option>
                        <option value="no">No - I do not have permission</option>
                    </select>
                    <div class="invalid-feedback" id="permissionError"></div>
                </div>

                <div class="permission-blocked-alert alert alert-danger" id="permissionBlockedAlert" style="display: none;">
                    <div class="d-flex">
                        <i class="fas fa-ban me-3 mt-1 fa-lg"></i>
                        <div>
                            <strong class="d-block">Registration Cannot Continue</strong>
                            <p class="mb-2 small">You have indicated that you do not have permission to use this SenderID. UK regulations require explicit authorisation from the brand owner before a SenderID can be registered.</p>
                            <p class="mb-0 small text-muted"><i class="fas fa-arrow-right me-1"></i>Please obtain written authorisation from the brand owner, then return to complete registration.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="confirmationSection" style="display: none;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="inputConfirmAuthorised">
                        <label class="form-check-label" for="inputConfirmAuthorised">
                            I confirm I am authorised to use this SenderID and understand that misuse may result in suspension
                        </label>
                    </div>
                    <div class="invalid-feedback" id="confirmError"></div>
                </div>

                <div class="mb-3" id="explanationSection" style="display: none;">
                    <label class="form-label">Additional Explanation (Optional)</label>
                    <textarea class="form-control" id="inputExplanation" rows="3" 
                              placeholder="Provide any additional context about your authorisation..."></textarea>
                    <div class="validation-hint">e.g. "Brand registered under company X" or "Subsidiary of parent company Y"</div>
                </div>
            </div>

            <div class="wizard-panel" data-panel="4" style="display: none;">
                <h6 class="wizard-panel-title">Intended Use Case</h6>
                <p class="text-muted small mb-3">How will this SenderID be used for messaging?</p>
                
                <div class="mb-3">
                    <label class="form-label form-label-required">Primary Use Case</label>
                    <select class="form-select" id="inputUseCase">
                        <option value="">Select use case...</option>
                        <option value="transactional">Transactional - Order updates, confirmations, receipts</option>
                        <option value="marketing">Promotional - Marketing messages, offers, campaigns</option>
                        <option value="otp">OTP - One-time passwords, verification codes, 2FA</option>
                        <option value="mixed">Mixed - Combination of above use cases</option>
                    </select>
                    <div class="invalid-feedback" id="useCaseError"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="inputDescription" rows="3" 
                              placeholder="Describe how this SenderID will be used..."></textarea>
                    <div class="validation-hint">Help reviewers understand your intended messaging use</div>
                </div>
            </div>

            <div class="wizard-panel" data-panel="5" style="display: none;">
                <h6 class="wizard-panel-title">Review & Submit</h6>
                <p class="text-muted small mb-3">Please review your registration details before submitting.</p>
                
                <div class="review-summary">
                    <div class="review-section">
                        <div class="review-section-title"><i class="fas fa-id-badge me-2"></i>SenderID Details</div>
                        <div class="review-row">
                            <span class="review-label">Type:</span>
                            <span class="review-value" id="reviewType"></span>
                        </div>
                        <div class="review-row">
                            <span class="review-label">SenderID:</span>
                            <span class="review-value senderid-name" id="reviewSenderId"></span>
                        </div>
                    </div>

                    <div class="review-section">
                        <div class="review-section-title"><i class="fas fa-building me-2"></i>Business Association</div>
                        <div class="review-row">
                            <span class="review-label">Brand / Business:</span>
                            <span class="review-value" id="reviewBrand"></span>
                        </div>
                        <div class="review-row">
                            <span class="review-label">Country:</span>
                            <span class="review-value">United Kingdom</span>
                        </div>
                        <div class="review-row">
                            <span class="review-label">Subaccount:</span>
                            <span class="review-value" id="reviewSubaccount"></span>
                        </div>
                    </div>

                    <div class="review-section">
                        <div class="review-section-title"><i class="fas fa-check-circle me-2"></i>Permission</div>
                        <div class="review-row">
                            <span class="review-label">Authorised:</span>
                            <span class="review-value text-success"><i class="fas fa-check me-1"></i>Confirmed</span>
                        </div>
                    </div>

                    <div class="review-section">
                        <div class="review-section-title"><i class="fas fa-bullseye me-2"></i>Use Case</div>
                        <div class="review-row">
                            <span class="review-label">Primary Use:</span>
                            <span class="review-value" id="reviewUseCase"></span>
                        </div>
                        <div class="review-row" id="reviewDescriptionRow">
                            <span class="review-label">Description:</span>
                            <span class="review-value" id="reviewDescription"></span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info small mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Review Process:</strong> SenderID registrations are typically reviewed within 1-2 business days. You'll receive an email notification once approved.
                </div>
            </div>
        </form>
    </div>
    <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary" id="btnCancelRegister">Cancel</button>
        <div class="wizard-nav-buttons">
            <button type="button" class="btn btn-outline-secondary" id="btnWizardBack" style="display: none;">
                <i class="fas fa-arrow-left me-1"></i>Back
            </button>
            <button type="button" class="btn btn-primary" id="btnWizardNext">
                Next<i class="fas fa-arrow-right ms-1"></i>
            </button>
            <button type="button" class="btn btn-primary" id="btnSubmitRegister" style="display: none;">
                <i class="fas fa-paper-plane me-1"></i>Submit for Approval
            </button>
        </div>
    </div>
</div>

<div class="drawer-backdrop" id="detailDrawerBackdrop"></div>
<div class="drawer" id="detailDrawer">
    <div class="drawer-header">
        <h5><i class="fas fa-id-badge me-2 text-primary"></i>SenderID Details</h5>
        <button type="button" class="btn-close" id="detailDrawerClose"></button>
    </div>
    <div class="drawer-body">
        <div class="mb-4">
            <h6 class="text-muted mb-3">Registration Information</h6>
            <div class="detail-row">
                <div class="detail-label">SenderID</div>
                <div class="detail-value"><span id="detailSenderId" class="senderid-name"></span></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Type</div>
                <div class="detail-value" id="detailType"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Brand / Company</div>
                <div class="detail-value" id="detailBrand"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Use Case</div>
                <div class="detail-value" id="detailUseCase"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Description</div>
                <div class="detail-value" id="detailDescription"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Subaccount</div>
                <div class="detail-value" id="detailSubaccount"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status</div>
                <div class="detail-value" id="detailStatus"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Created</div>
                <div class="detail-value" id="detailCreated"></div>
            </div>
        </div>

        <div class="mb-4" id="rejectionReasonSection" style="display: none;">
            <div class="alert alert-danger mb-0">
                <div class="d-flex align-items-start">
                    <i class="fas fa-times-circle me-2 mt-1"></i>
                    <div>
                        <strong class="d-block mb-1">Registration Rejected</strong>
                        <p class="mb-2 small" id="rejectionReason"></p>
                        <hr class="my-2">
                        <p class="mb-0 small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            To resubmit, you must register a new SenderID with the required changes addressed.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4" id="suspensionReasonSection" style="display: none;">
            <div class="alert alert-warning mb-0">
                <div class="d-flex align-items-start">
                    <i class="fas fa-pause-circle me-2 mt-1"></i>
                    <div>
                        <strong class="d-block mb-1">SenderID Suspended</strong>
                        <p class="mb-0 small" id="suspensionReason"></p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h6 class="text-muted mb-3">Audit History</h6>
            <div class="audit-timeline" id="auditTimeline">
            </div>
        </div>
    </div>
    <div class="drawer-footer" id="detailDrawerActions">
    </div>
</div>

<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suspendModalTitle">Suspend SenderID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="suspendModalMessage">Are you sure you want to suspend this SenderID?</p>
                <p class="text-muted small" id="suspendModalDescription">Suspended SenderIDs cannot be used for messaging until reactivated.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="btnConfirmSuspend">
                    <i class="fas fa-pause me-1"></i>Suspend
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-warning"><i class="fas fa-archive me-2"></i>Archive SenderID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to archive <strong id="archiveSenderId"></strong>?</p>
                <p class="text-muted small">Archived SenderIDs cannot be used for sending messages. This action is logged for audit purposes and can be reviewed by administrators.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="btnConfirmArchive">
                    <i class="fas fa-archive me-1"></i>Archive
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="submissionConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title text-success"><i class="fas fa-check-circle me-2"></i>Registration Submitted</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Your SenderID <strong id="submissionSenderId"></strong> has been submitted for approval.</p>
                
                <div class="alert alert-info small mb-3">
                    <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-1"></i>What happens next?</h6>
                    <ol class="mb-0 ps-3">
                        <li class="mb-1">Your registration will be reviewed by our compliance team</li>
                        <li class="mb-1">We may perform third-party validation checks</li>
                        <li class="mb-1">Mobile operators may also verify compliance</li>
                        <li>You'll receive an email notification with the outcome</li>
                    </ol>
                </div>
                
                <p class="text-muted small mb-0">
                    <i class="fas fa-clock me-1"></i>
                    Review typically takes <strong>1-2 business days</strong>. You can track the status in your SenderID library.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Available usage scopes for SenderIDs (all enabled by default when approved)
    var SENDERID_SCOPES = {
        SEND_MESSAGE: 'send_message',      // Send Message / Campaigns
        INBOX_REPLIES: 'inbox_replies',    // Inbox replies
        EMAIL_TO_SMS: 'email_to_sms',      // Email-to-SMS
        BULK_API: 'bulk_api',              // Bulk API
        CAMPAIGN_API: 'campaign_api'       // Campaign API
    };

    // Default scopes - all enabled
    function getDefaultScopes() {
        return {
            send_message: true,
            inbox_replies: true,
            email_to_sms: true,
            bulk_api: true,
            campaign_api: true
        };
    }

    var senderIds = [
        {
            id: 'sid_001',
            senderId: 'QUICKSMS',
            type: 'alphanumeric',
            brand: 'QuickSMS Ltd',
            useCase: 'transactional',
            description: 'Order confirmations and delivery updates',
            subaccount: 'Main Account',
            status: 'approved',
            created: '2024-01-15T10:30:00Z',
            lastUsed: '2024-03-14T16:45:00Z',
            scopes: { send_message: true, inbox_replies: true, email_to_sms: true, bulk_api: true, campaign_api: true },
            approvalDetails: { decision: 'approved', timestamp: '2024-01-16T14:22:00Z', reviewer: 'Compliance Team', reviewerType: 'manual' },
            auditHistory: [
                { action: 'Approved', user: 'Compliance Team', timestamp: '2024-01-16T14:22:00Z', auditType: 'approved' },
                { action: 'Under Review', user: 'System', timestamp: '2024-01-15T10:35:00Z', auditType: 'submitted' },
                { action: 'Submitted for Approval', user: 'John Smith', timestamp: '2024-01-15T10:30:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_002',
            senderId: 'ALERTS',
            type: 'alphanumeric',
            brand: 'QuickSMS Ltd',
            useCase: 'alerts',
            description: 'System alerts and notifications',
            subaccount: 'Operations',
            status: 'approved',
            created: '2024-02-01T09:00:00Z',
            lastUsed: '2024-03-13T09:30:00Z',
            scopes: { send_message: true, inbox_replies: true, email_to_sms: true, bulk_api: true, campaign_api: true },
            approvalDetails: { decision: 'approved', timestamp: '2024-02-02T11:45:00Z', reviewer: 'Compliance Team', reviewerType: 'manual' },
            auditHistory: [
                { action: 'Approved', user: 'Compliance Team', timestamp: '2024-02-02T11:45:00Z', auditType: 'approved' },
                { action: 'Under Review', user: 'System', timestamp: '2024-02-01T09:05:00Z', auditType: 'submitted' },
                { action: 'Submitted for Approval', user: 'Jane Doe', timestamp: '2024-02-01T09:00:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_003',
            senderId: '+447700900123',
            type: 'numeric',
            brand: 'QuickSMS Ltd',
            useCase: 'otp',
            description: 'Two-way messaging for customer support',
            subaccount: 'Customer Support',
            status: 'approved',
            created: '2024-02-15T11:30:00Z',
            lastUsed: '2024-03-14T11:20:00Z',
            scopes: { send_message: true, inbox_replies: true, email_to_sms: true, bulk_api: true, campaign_api: true },
            approvalDetails: { decision: 'approved', timestamp: '2024-02-16T09:00:00Z', reviewer: 'Third-Party Validator', reviewerType: 'third_party' },
            auditHistory: [
                { action: 'Approved', user: 'Third-Party Validator', timestamp: '2024-02-16T09:00:00Z', auditType: 'approved' },
                { action: 'Under Review', user: 'System', timestamp: '2024-02-15T11:35:00Z', auditType: 'submitted' },
                { action: 'Submitted for Approval', user: 'Support Team', timestamp: '2024-02-15T11:30:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_004',
            senderId: '60123',
            type: 'shortcode',
            brand: 'QuickSMS Ltd',
            useCase: 'marketing',
            description: 'Marketing campaigns and promotional offers',
            subaccount: 'Marketing Department',
            status: 'approved',
            created: '2024-01-20T14:00:00Z',
            lastUsed: '2024-03-10T15:00:00Z',
            scopes: { send_message: true, inbox_replies: true, email_to_sms: true, bulk_api: true, campaign_api: true },
            approvalDetails: { decision: 'approved', timestamp: '2024-01-22T10:00:00Z', reviewer: 'UK Operator Check', reviewerType: 'operator' },
            auditHistory: [
                { action: 'Approved', user: 'UK Operator Check', timestamp: '2024-01-22T10:00:00Z', auditType: 'approved' },
                { action: 'Under Review', user: 'System', timestamp: '2024-01-20T14:05:00Z', auditType: 'submitted' },
                { action: 'Submitted for Approval', user: 'Marketing Team', timestamp: '2024-01-20T14:00:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_005',
            senderId: 'PROMO',
            type: 'alphanumeric',
            brand: 'QuickSMS Ltd',
            useCase: 'marketing',
            description: 'Marketing campaigns and special offers',
            subaccount: 'Marketing Department',
            status: 'pending',
            created: '2024-03-10T14:20:00Z',
            auditHistory: [
                { action: 'Submitted for Approval', user: 'Marketing Team', timestamp: '2024-03-10T14:20:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_006',
            senderId: 'VERIFY',
            type: 'alphanumeric',
            brand: 'QuickSMS Ltd',
            useCase: 'otp',
            description: 'Two-factor authentication codes',
            subaccount: 'Main Account',
            status: 'pending',
            created: '2024-03-12T16:45:00Z',
            auditHistory: [
                { action: 'Submitted for Approval', user: 'Tech Team', timestamp: '2024-03-12T16:45:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_008',
            senderId: 'NEWBRAND',
            type: 'alphanumeric',
            brand: 'QuickSMS Ltd',
            useCase: 'transactional',
            description: 'New brand identity - work in progress',
            subaccount: 'Main Account',
            status: 'draft',
            created: '2024-03-15T10:00:00Z',
            auditHistory: [
                { action: 'Draft Created', user: 'John Smith', timestamp: '2024-03-15T10:00:00Z', auditType: 'created' }
            ]
        },
        {
            id: 'sid_009',
            senderId: 'OLDPROMO',
            type: 'alphanumeric',
            brand: 'QuickSMS Ltd',
            useCase: 'marketing',
            description: 'Legacy promotional sender - no longer in use',
            subaccount: 'Marketing Department',
            status: 'archived',
            created: '2023-06-01T09:00:00Z',
            auditHistory: [
                { action: 'Archived', user: 'Admin User', timestamp: '2024-02-28T15:30:00Z', auditType: 'archived', reason: 'No longer needed - replaced by PROMO' },
                { action: 'Approved', user: 'Compliance Team', timestamp: '2023-06-02T11:00:00Z', auditType: 'approved' },
                { action: 'Submitted for Approval', user: 'Marketing Team', timestamp: '2023-06-01T09:00:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_010',
            senderId: '70123',
            type: 'shortcode',
            brand: 'QuickSMS Ltd',
            useCase: 'marketing',
            description: 'Campaign shortcode - suspended for compliance review',
            subaccount: 'Marketing Department',
            status: 'suspended',
            created: '2023-09-15T14:00:00Z',
            suspensionReason: 'Compliance review required following high complaint rate.',
            auditHistory: [
                { action: 'Suspended', user: 'QuickSMS Compliance', timestamp: '2024-03-01T10:00:00Z', auditType: 'suspended', reason: 'Compliance review required following high complaint rate.' },
                { action: 'Approved', user: 'Compliance Team', timestamp: '2023-09-16T10:00:00Z', auditType: 'approved' },
                { action: 'Submitted for Approval', user: 'Marketing Team', timestamp: '2023-09-15T14:00:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_007',
            senderId: 'BANK',
            type: 'alphanumeric',
            brand: 'QuickSMS Ltd',
            useCase: 'transactional',
            description: 'Banking notifications',
            subaccount: 'Main Account',
            status: 'rejected',
            created: '2024-03-05T11:00:00Z',
            rejectionReason: 'SenderID "BANK" is a reserved term and cannot be registered without additional verification of financial institution status. Please provide proof of authorisation from a registered financial institution.',
            approvalDetails: { decision: 'rejected', timestamp: '2024-03-06T10:30:00Z', reviewer: 'Compliance Team', reviewerType: 'manual', rejectionReason: 'Reserved term - requires financial verification' },
            auditHistory: [
                { action: 'Rejected', user: 'Compliance Team', timestamp: '2024-03-06T10:30:00Z', auditType: 'rejected', reason: 'Reserved term - requires financial verification' },
                { action: 'Under Review', user: 'System', timestamp: '2024-03-05T11:05:00Z', auditType: 'submitted' },
                { action: 'Submitted for Approval', user: 'John Smith', timestamp: '2024-03-05T11:00:00Z', auditType: 'submitted' }
            ]
        },
        {
            id: 'sid_011',
            senderId: 'HMRC',
            type: 'alphanumeric',
            brand: 'QuickSMS Ltd',
            useCase: 'transactional',
            description: 'Tax notifications',
            subaccount: 'Main Account',
            status: 'rejected',
            created: '2024-02-20T09:00:00Z',
            rejectionReason: 'SenderID "HMRC" impersonates a UK government agency. Only the official HM Revenue & Customs organisation may use this identifier. Impersonation of government bodies is strictly prohibited.',
            approvalDetails: { decision: 'rejected', timestamp: '2024-02-21T14:00:00Z', reviewer: 'UK Operator Check', reviewerType: 'operator', rejectionReason: 'Government impersonation' },
            auditHistory: [
                { action: 'Rejected', user: 'UK Operator Check', timestamp: '2024-02-21T14:00:00Z', auditType: 'rejected', reason: 'Government impersonation' },
                { action: 'Under Review', user: 'System', timestamp: '2024-02-20T09:05:00Z', auditType: 'submitted' },
                { action: 'Submitted for Approval', user: 'John Smith', timestamp: '2024-02-20T09:00:00Z', auditType: 'submitted' }
            ]
        }
    ];

    var currentPage = 1;
    var pageSize = 10;
    var sortColumn = 'created';
    var sortDirection = 'desc';
    var selectedSenderId = null;

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if (!dateString || dateString === '-') return '-';
        var date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + 
               ' ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }

    function getStatusBadge(status) {
        var badges = {
            'draft': '<span class="badge badge-draft">Draft</span>',
            'pending': '<span class="badge badge-pending">Pending Approval</span>',
            'approved': '<span class="badge badge-approved">Approved</span>',
            'rejected': '<span class="badge badge-rejected">Rejected</span>',
            'suspended': '<span class="badge badge-suspended">Suspended</span>',
            'archived': '<span class="badge badge-archived">Archived</span>'
        };
        return badges[status] || status;
    }

    function getUseCaseBadge(useCase) {
        var badges = {
            'otp': '<span class="badge badge-otp">OTP / Verification</span>',
            'marketing': '<span class="badge badge-marketing">Marketing</span>',
            'transactional': '<span class="badge badge-transactional">Transactional</span>',
            'alerts': '<span class="badge badge-alerts">Alerts</span>'
        };
        return badges[useCase] || useCase;
    }

    function getTypeBadge(senderIdType) {
        var badges = {
            'alphanumeric': '<span class="badge badge-alphanumeric">Alphanumeric</span>',
            'numeric': '<span class="badge badge-numeric">Numeric</span>',
            'shortcode': '<span class="badge badge-shortcode">Shortcode</span>'
        };
        return badges[senderIdType] || senderIdType;
    }

    function getTypeLabel(senderIdType) {
        var labels = {
            'alphanumeric': 'Alphanumeric',
            'numeric': 'UK Virtual Mobile Number',
            'shortcode': 'Shortcode'
        };
        return labels[senderIdType] || senderIdType;
    }

    function filterSenderIds() {
        var search = $('#searchInput').val().toLowerCase();
        var filterType = $('#filterType').val();
        var status = $('#filterStatus').val();
        var useCase = $('#filterUseCase').val();

        return senderIds.filter(function(item) {
            var matchSearch = !search || 
                item.senderId.toLowerCase().includes(search) ||
                item.brand.toLowerCase().includes(search) ||
                (item.description && item.description.toLowerCase().includes(search));
            var matchType = !filterType || item.type === filterType;
            var matchStatus = !status || item.status === status;
            var matchUseCase = !useCase || item.useCase === useCase;
            return matchSearch && matchType && matchStatus && matchUseCase;
        });
    }

    function sortSenderIds(data) {
        return data.sort(function(a, b) {
            var aVal = a[sortColumn] || '';
            var bVal = b[sortColumn] || '';
            if (sortColumn === 'created') {
                aVal = new Date(aVal);
                bVal = new Date(bVal);
            }
            if (sortDirection === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });
    }

    function renderTable() {
        var filtered = filterSenderIds();
        var sorted = sortSenderIds(filtered);
        var start = (currentPage - 1) * pageSize;
        var paged = sorted.slice(start, start + pageSize);

        if (sorted.length === 0) {
            $('#senderIdLibrary').hide();
            $('#emptyState').show();
            return;
        }

        $('#emptyState').hide();
        $('#senderIdLibrary').show();

        var html = '';
        paged.forEach(function(item) {
            html += '<tr data-id="' + item.id + '">';
            html += '<td><span class="senderid-name">' + escapeHtml(item.senderId) + '</span></td>';
            html += '<td>' + getTypeBadge(item.type) + '</td>';
            html += '<td>' + escapeHtml(item.brand) + '</td>';
            html += '<td>' + getUseCaseBadge(item.useCase) + '</td>';
            html += '<td>' + getStatusBadge(item.status) + '</td>';
            html += '<td>' + formatDate(item.created) + '</td>';
            html += '<td>' + (item.lastUsed ? formatDate(item.lastUsed) : '<span class="text-muted">Never</span>') + '</td>';
            html += '<td class="text-center">';
            html += '<div class="dropdown">';
            html += '<button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>';
            html += '<ul class="dropdown-menu dropdown-menu-end">';
            html += '<li><a class="dropdown-item btn-view-details" href="#" data-id="' + item.id + '"><i class="fas fa-eye me-2"></i>View Details</a></li>';
            html += '<li><a class="dropdown-item btn-view-audit" href="#" data-id="' + item.id + '"><i class="fas fa-history me-2"></i>View Audit History</a></li>';
            if (item.status === 'approved') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item btn-suspend" href="#" data-id="' + item.id + '"><i class="fas fa-pause me-2"></i>Suspend</a></li>';
                html += '<li><a class="dropdown-item btn-archive-row" href="#" data-id="' + item.id + '"><i class="fas fa-archive me-2"></i>Archive</a></li>';
            }
            if (item.status === 'suspended') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item btn-reactivate" href="#" data-id="' + item.id + '"><i class="fas fa-play me-2"></i>Reactivate</a></li>';
                html += '<li><a class="dropdown-item btn-archive-row" href="#" data-id="' + item.id + '"><i class="fas fa-archive me-2"></i>Archive</a></li>';
            }
            if (item.status === 'rejected') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item btn-archive-row" href="#" data-id="' + item.id + '"><i class="fas fa-archive me-2"></i>Archive</a></li>';
            }
            html += '</ul>';
            html += '</div>';
            html += '</td>';
            html += '</tr>';
        });

        $('#senderIdTableBody').html(html);
        $('#showingCount').text(paged.length);
        $('#totalCount').text(sorted.length);

        renderPagination(sorted.length);
        updateSortIndicators();
    }

    function renderPagination(total) {
        var totalPages = Math.ceil(total / pageSize);
        var html = '';

        if (totalPages <= 1) {
            $('#paginationContainer').html('');
            return;
        }

        html += '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">';
        html += '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '">&laquo;</a></li>';

        for (var i = 1; i <= totalPages; i++) {
            html += '<li class="page-item ' + (currentPage === i ? 'active' : '') + '">';
            html += '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
        }

        html += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">';
        html += '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '">&raquo;</a></li>';

        $('#paginationContainer').html(html);
    }

    function updateSortIndicators() {
        $('.senderid-table thead th').removeClass('sorted');
        $('.senderid-table thead th .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        var $th = $('.senderid-table thead th[data-sort="' + sortColumn + '"]');
        $th.addClass('sorted');
        $th.find('.sort-icon').removeClass('fa-sort').addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
    }

    window.sortTable = function(column) {
        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }
        currentPage = 1;
        renderTable();
    };

    var currentWizardStep = 1;
    var totalWizardSteps = 5;

    function openRegisterDrawer() {
        $('#registerForm')[0].reset();
        $('#inputSenderId').removeClass('is-invalid');
        $('#inputBrand').removeClass('is-invalid');
        $('#inputUseCase').removeClass('is-invalid');
        $('#inputPermission').removeClass('is-invalid');
        $('#inputConfirmAuthorised').prop('checked', false);
        $('#senderIdCharCount').text('0');
        $('.type-card').removeClass('selected');
        $('.type-card[data-type="alphanumeric"]').addClass('selected');
        $('#inputType').val('alphanumeric');
        updateSenderIdInputForType('alphanumeric');
        
        currentWizardStep = 1;
        updateWizardUI();
        
        $('#registerDrawerBackdrop').addClass('show');
        $('#registerDrawer').addClass('show');
    }

    function updateWizardUI() {
        $('.wizard-panel').hide();
        $('.wizard-panel[data-panel="' + currentWizardStep + '"]').show();
        
        $('.wizard-step').removeClass('active completed');
        $('.wizard-step-connector').removeClass('completed');
        
        for (var i = 1; i <= totalWizardSteps; i++) {
            var $step = $('.wizard-step[data-step="' + i + '"]');
            if (i < currentWizardStep) {
                $step.addClass('completed');
                $step.find('.wizard-step-number').text('');
            } else if (i === currentWizardStep) {
                $step.addClass('active');
                $step.find('.wizard-step-number').text(i);
            } else {
                $step.find('.wizard-step-number').text(i);
            }
        }
        
        $('.wizard-step-connector').each(function(index) {
            if (index < currentWizardStep - 1) {
                $(this).addClass('completed');
            }
        });
        
        $('#btnWizardBack').toggle(currentWizardStep > 1);
        $('#btnWizardNext').toggle(currentWizardStep < totalWizardSteps);
        $('#btnSubmitRegister').toggle(currentWizardStep === totalWizardSteps);
        
        if (currentWizardStep === totalWizardSteps) {
            populateReviewSummary();
        }
    }

    function populateReviewSummary() {
        var senderIdType = $('#inputType').val();
        var senderId = $('#inputSenderId').val();
        var brand = $('#inputBrand').val();
        var subaccount = $('#inputSubaccount option:selected').text() || 'Main Account';
        var useCase = $('#inputUseCase option:selected').text();
        var description = $('#inputDescription').val();
        
        var typeLabels = {
            'alphanumeric': 'Alphanumeric',
            'numeric': 'UK Virtual Mobile Number',
            'shortcode': 'Shortcode'
        };
        
        $('#reviewType').text(typeLabels[senderIdType] || senderIdType);
        $('#reviewSenderId').text(senderId);
        $('#reviewBrand').text(brand);
        $('#reviewSubaccount').text(subaccount);
        $('#reviewUseCase').text(useCase);
        
        if (description) {
            $('#reviewDescriptionRow').show();
            $('#reviewDescription').text(description);
        } else {
            $('#reviewDescriptionRow').hide();
        }
    }

    function validateWizardStep(step) {
        var isValid = true;
        
        if (step === 1) {
            var senderIdType = $('#inputType').val();
            var senderId = $('#inputSenderId').val().trim();
            var result = validateSenderId(senderId, senderIdType);
            
            if (!senderId) {
                $('#inputSenderId').addClass('is-invalid');
                $('#senderIdError').text('SenderID is required');
                isValid = false;
            } else if (!result.valid) {
                $('#inputSenderId').addClass('is-invalid');
                $('#senderIdError').text(result.message);
                isValid = false;
            } else {
                $('#inputSenderId').removeClass('is-invalid');
            }
        } else if (step === 2) {
            var brand = $('#inputBrand').val().trim();
            if (!brand) {
                $('#inputBrand').addClass('is-invalid');
                $('#brandError').text('Brand / Business name is required');
                isValid = false;
            } else {
                $('#inputBrand').removeClass('is-invalid');
            }
        } else if (step === 3) {
            var permission = $('#inputPermission').val();
            var confirmed = $('#inputConfirmAuthorised').is(':checked');
            
            if (!permission) {
                $('#inputPermission').addClass('is-invalid');
                $('#permissionError').text('Please select a permission option');
                isValid = false;
            } else if (permission === 'no') {
                isValid = false;
            } else {
                $('#inputPermission').removeClass('is-invalid');
                
                if (!confirmed) {
                    $('#confirmError').text('You must confirm authorisation to proceed');
                    $('#confirmError').show();
                    isValid = false;
                } else {
                    $('#confirmError').hide();
                }
            }
        } else if (step === 4) {
            var useCase = $('#inputUseCase').val();
            if (!useCase) {
                $('#inputUseCase').addClass('is-invalid');
                $('#useCaseError').text('Use case is required');
                isValid = false;
            } else {
                $('#inputUseCase').removeClass('is-invalid');
            }
        }
        
        return isValid;
    }

    $('#btnWizardNext').on('click', function() {
        if (validateWizardStep(currentWizardStep)) {
            currentWizardStep++;
            updateWizardUI();
        }
    });

    $('#btnWizardBack').on('click', function() {
        if (currentWizardStep > 1) {
            currentWizardStep--;
            updateWizardUI();
        }
    });

    $('#inputPermission').on('change', function() {
        var val = $(this).val();
        $(this).removeClass('is-invalid');
        
        if (val === 'no') {
            $('#permissionBlockedAlert').show();
            $('#confirmationSection').hide();
            $('#explanationSection').hide();
        } else if (val === 'yes') {
            $('#permissionBlockedAlert').hide();
            $('#confirmationSection').show();
            $('#explanationSection').show();
        } else {
            $('#permissionBlockedAlert').hide();
            $('#confirmationSection').hide();
            $('#explanationSection').hide();
        }
    });

    function updateSenderIdInputForType(senderIdType) {
        var $input = $('#inputSenderId');
        var $hint = $('#senderIdHint');
        var $counter = $('#charCounterWrapper');
        
        $input.val('').removeClass('is-invalid');
        $('#senderIdCharCount').text('0');
        $('#normalisationPreview').hide();
        
        if (senderIdType === 'alphanumeric') {
            $input.attr('maxlength', '11').attr('placeholder', 'e.g. MyBrand').removeClass('form-control-lg');
            $hint.text('Max 11 characters: A-Z a-z 0-9 . - _ & space');
            $counter.show().find('#senderIdCharCount').next().remove();
            $counter.html('<span id="senderIdCharCount">0</span>/11');
        } else if (senderIdType === 'numeric') {
            $input.attr('maxlength', '14').attr('placeholder', '07xxxxxxxxx or +447xxxxxxxxx').removeClass('form-control-lg');
            $hint.text('UK mobile: 07xxxxxxxxx, +447xxxxxxxxx, or 447xxxxxxxxx');
            $counter.hide();
        } else if (senderIdType === 'shortcode') {
            $input.attr('maxlength', '5').attr('placeholder', 'e.g. 60123').removeClass('form-control-lg');
            $hint.text('Exactly 5 digits, must start with 6, 7, or 8');
            $counter.show().html('<span id="senderIdCharCount">0</span>/5');
        }
    }

    function closeRegisterDrawer() {
        $('#registerDrawerBackdrop').removeClass('show');
        $('#registerDrawer').removeClass('show');
    }

    function openDetailDrawer(id) {
        var item = senderIds.find(function(s) { return s.id === id; });
        if (!item) return;

        selectedSenderId = item;

        $('#detailSenderId').text(item.senderId);
        $('#detailType').html(getTypeBadge(item.type));
        $('#detailBrand').text(item.brand);
        $('#detailUseCase').html(getUseCaseBadge(item.useCase));
        $('#detailDescription').text(item.description || '-');
        $('#detailSubaccount').text(item.subaccount);
        $('#detailStatus').html(getStatusBadge(item.status));
        $('#detailCreated').text(formatDateTime(item.created));

        if (item.status === 'rejected' && item.rejectionReason) {
            $('#rejectionReasonSection').show();
            $('#rejectionReason').text(item.rejectionReason);
        } else {
            $('#rejectionReasonSection').hide();
        }

        if (item.status === 'suspended' && item.suspensionReason) {
            $('#suspensionReasonSection').show();
            $('#suspensionReason').text(item.suspensionReason);
        } else {
            $('#suspensionReasonSection').hide();
        }

        var auditHtml = '';
        item.auditHistory.forEach(function(audit) {
            auditHtml += '<div class="audit-item ' + (audit.auditType || '') + '">';
            auditHtml += '<div class="audit-action">' + escapeHtml(audit.action) + '</div>';
            auditHtml += '<div class="audit-user">by ' + escapeHtml(audit.user) + '</div>';
            auditHtml += '<div class="audit-time">' + formatDateTime(audit.timestamp) + '</div>';
            if (audit.reason) {
                auditHtml += '<div class="small text-muted mt-1">' + escapeHtml(audit.reason) + '</div>';
            }
            auditHtml += '</div>';
        });
        $('#auditTimeline').html(auditHtml);

        var actionsHtml = '';
        if (item.status === 'draft') {
            actionsHtml += '<button type="button" class="btn btn-primary btn-sm me-2" id="btnDetailSubmit"><i class="fas fa-paper-plane me-1"></i>Submit for Approval</button>';
            actionsHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" id="btnDetailArchive"><i class="fas fa-archive me-1"></i>Archive</button>';
        } else if (item.status === 'pending') {
            actionsHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" id="btnDetailArchive"><i class="fas fa-archive me-1"></i>Archive</button>';
        } else if (item.status === 'approved') {
            actionsHtml += '<button type="button" class="btn btn-warning btn-sm me-2" id="btnDetailSuspend"><i class="fas fa-pause me-1"></i>Suspend</button>';
            actionsHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" id="btnDetailArchive"><i class="fas fa-archive me-1"></i>Archive</button>';
        } else if (item.status === 'suspended') {
            actionsHtml += '<button type="button" class="btn btn-success btn-sm me-2" id="btnDetailReactivate"><i class="fas fa-play me-1"></i>Reactivate</button>';
            actionsHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" id="btnDetailArchive"><i class="fas fa-archive me-1"></i>Archive</button>';
        } else if (item.status === 'rejected') {
            actionsHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" id="btnDetailArchive"><i class="fas fa-archive me-1"></i>Archive</button>';
        }
        $('#detailDrawerActions').html(actionsHtml);

        $('#detailDrawerBackdrop').addClass('show');
        $('#detailDrawer').addClass('show');
    }

    function closeDetailDrawer() {
        $('#detailDrawerBackdrop').removeClass('show');
        $('#detailDrawer').removeClass('show');
        selectedSenderId = null;
    }

    function normaliseUkMobile(value) {
        if (!value) return { normalised: null, valid: false, message: 'Phone number is required' };
        
        var cleaned = value.replace(/[\s\-\(\)]/g, '');
        var normalised = null;
        
        if (/^07\d{9}$/.test(cleaned)) {
            normalised = '447' + cleaned.substring(2);
        } else if (/^\+447\d{9}$/.test(cleaned)) {
            normalised = cleaned.substring(1);
        } else if (/^447\d{9}$/.test(cleaned)) {
            normalised = cleaned;
        } else if (/^\+440\d{10}$/.test(cleaned) || /^440\d{10}$/.test(cleaned)) {
            return { normalised: null, valid: false, message: 'Invalid format: do not include leading 0 after +44' };
        } else if (/^0[1-9]\d{8,9}$/.test(cleaned) && !/^07/.test(cleaned)) {
            return { normalised: null, valid: false, message: 'UK landlines are not permitted, only mobile numbers (07...)' };
        } else if (/^\+[^4]/.test(cleaned) || /^\+4[^4]/.test(cleaned)) {
            return { normalised: null, valid: false, message: 'Only UK numbers are permitted (+44...)' };
        } else {
            return { normalised: null, valid: false, message: 'Enter a valid UK mobile: 07xxxxxxxxx, +447xxxxxxxxx, or 447xxxxxxxxx' };
        }
        
        if (!/^447[0-9]\d{8}$/.test(normalised)) {
            return { normalised: null, valid: false, message: 'Not a valid UK mobile range' };
        }
        
        return { normalised: normalised, valid: true };
    }

    function validateSenderId(value, senderIdType) {
        if (!value) return { valid: false, message: 'SenderID is required' };
        
        if (senderIdType === 'alphanumeric') {
            if (value.length > 11) return { valid: false, message: 'Maximum 11 characters allowed' };
            
            var allowedPattern = /^[A-Za-z0-9.\-_& ]+$/;
            if (!allowedPattern.test(value)) {
                var invalidChar = value.match(/[^A-Za-z0-9.\-_& ]/);
                if (invalidChar) {
                    return { valid: false, message: "Character '" + invalidChar[0] + "' is not permitted in SenderIDs" };
                }
            }
        } else if (senderIdType === 'numeric') {
            var result = normaliseUkMobile(value);
            if (!result.valid) {
                return { valid: false, message: result.message };
            }
            var normalised = result.normalised;
            var existingNormalised = senderIds.find(function(s) { 
                if (s.type !== 'numeric') return false;
                var n = normaliseUkMobile(s.senderId);
                return n.valid && n.normalised === normalised;
            });
            if (existingNormalised) return { valid: false, message: 'This number is already registered' };
            return { valid: true, normalised: normalised };
        } else if (senderIdType === 'shortcode') {
            if (!/^\d+$/.test(value)) {
                return { valid: false, message: 'Shortcode must contain digits only' };
            }
            if (value.length !== 5) {
                return { valid: false, message: 'UK shortcodes must be exactly 5 digits' };
            }
            if (!/^[678]/.test(value)) {
                return { valid: false, message: 'UK shortcodes must start with 6, 7, or 8' };
            }
        }

        var existing = senderIds.find(function(s) { return s.senderId.toUpperCase() === value.toUpperCase(); });
        if (existing) {
            if (existing.status === 'rejected') {
                return { valid: false, message: 'This SenderID was previously rejected. Please review the rejection reason and register with a different identifier.' };
            } else if (existing.status === 'pending') {
                return { valid: false, message: 'This SenderID is already pending approval' };
            } else if (existing.status === 'approved') {
                return { valid: false, message: 'This SenderID is already registered and approved' };
            } else if (existing.status === 'suspended') {
                return { valid: false, message: 'This SenderID is registered but currently suspended' };
            } else if (existing.status === 'archived') {
                return { valid: false, message: 'This SenderID is archived. Contact support to restore it.' };
            }
            return { valid: false, message: 'This SenderID is already registered' };
        }

        return { valid: true };
    }

    $('.type-card').on('click', function() {
        $('.type-card').removeClass('selected');
        $(this).addClass('selected');
        var selectedType = $(this).data('type');
        $('#inputType').val(selectedType);
        updateSenderIdInputForType(selectedType);
    });

    $('#inputSenderId').on('input', function() {
        var senderIdType = $('#inputType').val();
        var val = $(this).val();
        
        if (senderIdType === 'numeric') {
            val = val.replace(/[^\d+]/g, '');
            $(this).val(val);
            
            var normalResult = normaliseUkMobile(val);
            if (val && normalResult.valid && normalResult.normalised) {
                $('#normalisedValue').text('+' + normalResult.normalised);
                $('#normalisationPreview').show();
            } else {
                $('#normalisationPreview').hide();
            }
        } else if (senderIdType === 'shortcode') {
            val = val.replace(/[^\d]/g, '');
            $(this).val(val);
            $('#normalisationPreview').hide();
        } else {
            $('#normalisationPreview').hide();
        }
        
        if (senderIdType === 'alphanumeric') {
            $('#senderIdCharCount').text(val.length);
        } else if (senderIdType === 'numeric') {
            $('#senderIdCharCount').text(val.replace(/\+/g, '').length);
        } else {
            $('#senderIdCharCount').text(val.length);
        }
        
        var result = validateSenderId(val, senderIdType);
        if (val && !result.valid) {
            $(this).addClass('is-invalid');
            $('#senderIdError').text(result.message);
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    $('#btnRegisterSenderId, #btnRegisterSenderIdEmpty').on('click', openRegisterDrawer);
    $('#registerDrawerClose, #registerDrawerBackdrop, #btnCancelRegister').on('click', closeRegisterDrawer);
    $('#detailDrawerClose, #detailDrawerBackdrop').on('click', closeDetailDrawer);

    $('#btnSubmitRegister').on('click', function() {
        var senderIdType = $('#inputType').val();
        var senderId = $('#inputSenderId').val().trim();
        var brand = $('#inputBrand').val().trim();
        var useCase = $('#inputUseCase').val();
        var description = $('#inputDescription').val().trim();
        var subaccount = $('#inputSubaccount option:selected').text();
        var explanation = $('#inputExplanation').val().trim();

        var senderIdResult = validateSenderId(senderId, senderIdType);
        var finalSenderId = senderId;
        if (senderIdType === 'numeric' && senderIdResult.normalised) {
            finalSenderId = '+' + senderIdResult.normalised;
        }

        var newEntry = {
            id: 'sid_' + Date.now(),
            senderId: finalSenderId,
            type: senderIdType,
            brand: brand,
            useCase: useCase,
            description: description,
            explanation: explanation,
            subaccount: subaccount || 'Main Account',
            country: 'United Kingdom',
            status: 'pending',
            created: new Date().toISOString(),
            scopes: getDefaultScopes(),
            auditHistory: [
                { action: 'Submitted for Approval', user: 'Current User', timestamp: new Date().toISOString(), auditType: 'submitted' }
            ]
        };

        senderIds.unshift(newEntry);
        closeRegisterDrawer();
        currentWizardStep = 1;
        renderTable();

        showSubmissionConfirmation(finalSenderId);
    });

    function showSubmissionConfirmation(senderId) {
        $('#submissionSenderId').text(senderId);
        new bootstrap.Modal($('#submissionConfirmModal')[0]).show();
    }

    $(document).on('click', '.btn-view-details', function(e) {
        e.preventDefault();
        openDetailDrawer($(this).data('id'));
    });

    $(document).on('click', '.btn-suspend, #btnDetailSuspend', function(e) {
        e.preventDefault();
        var id = $(this).data('id') || (selectedSenderId && selectedSenderId.id);
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            $('#suspendModal').data('id', id);
            $('#suspendModalTitle').text('Suspend SenderID');
            $('#suspendModalMessage').html('Are you sure you want to suspend <strong>' + item.senderId + '</strong>?');
            $('#btnConfirmSuspend').removeClass('btn-success').addClass('btn-warning').html('<i class="fas fa-pause me-1"></i>Suspend');
            new bootstrap.Modal($('#suspendModal')[0]).show();
        }
    });

    $(document).on('click', '.btn-reactivate, #btnDetailReactivate', function(e) {
        e.preventDefault();
        var id = $(this).data('id') || (selectedSenderId && selectedSenderId.id);
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            $('#suspendModal').data('id', id);
            $('#suspendModalTitle').text('Reactivate SenderID');
            $('#suspendModalMessage').html('Are you sure you want to reactivate <strong>' + item.senderId + '</strong>?');
            $('#btnConfirmSuspend').removeClass('btn-warning').addClass('btn-success').html('<i class="fas fa-play me-1"></i>Reactivate');
            new bootstrap.Modal($('#suspendModal')[0]).show();
        }
    });

    $('#btnConfirmSuspend').on('click', function() {
        var id = $('#suspendModal').data('id');
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            var action = item.status === 'suspended' ? 'reactivate' : 'suspend';
            item.status = action === 'suspend' ? 'suspended' : 'approved';
            item.auditHistory.unshift({
                action: action === 'suspend' ? 'Suspended' : 'Reactivated',
                user: 'Current User',
                timestamp: new Date().toISOString(),
                auditType: action === 'suspend' ? 'rejected' : 'approved'
            });
            bootstrap.Modal.getInstance($('#suspendModal')[0]).hide();
            closeDetailDrawer();
            renderTable();
            if (typeof showSuccessToast === 'function') {
                showSuccessToast('SenderID ' + (action === 'suspend' ? 'suspended' : 'reactivated'));
            }
        }
    });

    $(document).on('click', '.btn-view-audit', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            selectedSenderId = item;
            openDetailDrawer(id);
            setTimeout(function() {
                $('a[data-bs-target="#detailAudit"]').tab('show');
            }, 100);
        }
    });

    $(document).on('click', '.btn-archive-row', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item && (item.status === 'approved' || item.status === 'rejected' || item.status === 'suspended')) {
            $('#archiveModal').data('id', id);
            $('#archiveSenderId').text(item.senderId);
            new bootstrap.Modal($('#archiveModal')[0]).show();
        }
    });

    $(document).on('click', '#btnDetailArchive', function(e) {
        e.preventDefault();
        var id = selectedSenderId && selectedSenderId.id;
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            $('#archiveModal').data('id', id);
            $('#archiveSenderId').text(item.senderId);
            new bootstrap.Modal($('#archiveModal')[0]).show();
        }
    });

    $('#btnConfirmArchive').on('click', function() {
        var id = $('#archiveModal').data('id');
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item) {
            item.status = 'archived';
            item.auditHistory.unshift({
                action: 'Archived',
                user: 'Current User',
                timestamp: new Date().toISOString(),
                auditType: 'archived'
            });
            bootstrap.Modal.getInstance($('#archiveModal')[0]).hide();
            closeDetailDrawer();
            renderTable();
            if (typeof showSuccessToast === 'function') {
                showSuccessToast('SenderID archived');
            }
        }
    });

    $(document).on('click', '#btnDetailSubmit', function(e) {
        e.preventDefault();
        var id = selectedSenderId && selectedSenderId.id;
        var item = senderIds.find(function(s) { return s.id === id; });
        if (item && item.status === 'draft') {
            item.status = 'pending';
            item.auditHistory.unshift({
                action: 'Submitted for Approval',
                user: 'Current User',
                timestamp: new Date().toISOString(),
                auditType: 'submitted'
            });
            closeDetailDrawer();
            renderTable();
            if (typeof showSuccessToast === 'function') {
                showSuccessToast('SenderID submitted for approval');
            }
        }
    });

    $('#searchInput').on('input', function() {
        currentPage = 1;
        renderTable();
    });

    $('#filterType, #filterStatus, #filterUseCase').on('change', function() {
        currentPage = 1;
        renderTable();
    });

    $('#btnResetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#filterType').val('');
        $('#filterStatus').val('');
        $('#filterUseCase').val('');
        currentPage = 1;
        renderTable();
    });

    $(document).on('click', '#paginationContainer .page-link', function(e) {
        e.preventDefault();
        var page = parseInt($(this).data('page'));
        if (page && page > 0) {
            currentPage = page;
            renderTable();
        }
    });

    renderTable();
});
</script>
@endpush

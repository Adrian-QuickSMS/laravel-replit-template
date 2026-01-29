@extends('layouts.admin')

@section('title', 'Country Controls')

@push('styles')
<style>
.country-controls-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.country-controls-title h4 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.country-controls-title p {
    margin: 0.25rem 0 0 0;
    font-size: 0.85rem;
    color: #6c757d;
}
.admin-tabs {
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 1.5rem;
}
.admin-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.15s;
}
.admin-tabs .nav-link:hover {
    color: #1e3a5f;
    border-bottom-color: rgba(30, 58, 95, 0.3);
}
.admin-tabs .nav-link.active {
    color: #1e3a5f;
    border-bottom-color: #1e3a5f;
    background: transparent;
}
.admin-tabs .nav-link .badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
    margin-left: 0.5rem;
    vertical-align: middle;
}
.admin-tabs .nav-link .badge.pending-badge {
    background: #ecc94b;
    color: #744210;
}
.admin-internal-badge {
    font-size: 0.6rem;
    padding: 0.15rem 0.4rem;
    background: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
    border-radius: 0.2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 0.5rem;
}
.enforcement-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.enforcement-banner h6 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.enforcement-banner p {
    margin: 0;
    font-size: 0.8rem;
    opacity: 0.9;
}
.enforcement-points {
    display: flex;
    gap: 2rem;
    margin-top: 0.75rem;
    flex-wrap: wrap;
}
.enforcement-point {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}
.enforcement-point i {
    color: #48bb78;
}
.country-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.country-stat-card {
    flex: 1;
    background: #fff;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-left: 3px solid;
}
.country-stat-card.allowed {
    border-left-color: #48bb78;
}
.country-stat-card.blocked {
    border-left-color: #e53e3e;
}
.country-stat-card.pending {
    border-left-color: #ecc94b;
}
.country-stat-card.restricted {
    border-left-color: #ed8936;
}
.country-stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e3a5f;
}
.country-stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.country-table-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}
.country-table-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.country-table-header h6 {
    margin: 0;
    font-weight: 600;
    color: #374151;
}
.country-search-box {
    position: relative;
    width: 280px;
}
.country-search-box input {
    padding-left: 2.25rem;
    font-size: 0.85rem;
}
.country-search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}
.country-table {
    width: 100%;
    margin: 0;
}
.country-table th {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}
.country-table td {
    padding: 0.65rem 0.75rem;
    font-size: 0.85rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
.country-table tr:hover {
    background: #f8fafc;
}
.country-flag {
    width: 24px;
    height: 16px;
    border-radius: 2px;
    margin-right: 0.5rem;
    object-fit: cover;
    border: 1px solid #e9ecef;
}
.country-name {
    font-weight: 500;
    color: #374151;
}
.country-code {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-left: 0.5rem;
}
.status-badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
}
.status-badge.allowed {
    background: rgba(72, 187, 120, 0.15);
    color: #22543d;
}
.status-badge.blocked {
    background: rgba(229, 62, 62, 0.15);
    color: #c53030;
}
.status-badge.restricted {
    background: rgba(237, 137, 54, 0.15);
    color: #c05621;
}
.status-badge.pending {
    background: rgba(236, 201, 75, 0.15);
    color: #975a16;
}
.risk-indicator {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.risk-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.risk-dot.low { background: #48bb78; }
.risk-dot.medium { background: #ecc94b; }
.risk-dot.high { background: #ed8936; }
.risk-dot.critical { background: #e53e3e; }
.action-btn-group {
    display: flex;
    gap: 0.25rem;
}
.action-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.7rem;
    border-radius: 0.25rem;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.action-btn.allow {
    background: rgba(72, 187, 120, 0.15);
    color: #22543d;
}
.action-btn.allow:hover {
    background: #48bb78;
    color: #fff;
}
.action-btn.block {
    background: rgba(229, 62, 62, 0.15);
    color: #c53030;
}
.action-btn.block:hover {
    background: #e53e3e;
    color: #fff;
}
.action-btn.restrict {
    background: rgba(237, 137, 54, 0.15);
    color: #c05621;
}
.action-btn.restrict:hover {
    background: #ed8936;
    color: #fff;
}
.customer-override-badge {
    font-size: 0.65rem;
    padding: 0.15rem 0.35rem;
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
    border-radius: 0.2rem;
    margin-left: 0.5rem;
}
.enforcement-sync-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #48bb78;
}
.enforcement-sync-indicator.syncing {
    color: #ecc94b;
}
.enforcement-sync-indicator i {
    animation: none;
}
.enforcement-sync-indicator.syncing i {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.bulk-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
.bulk-actions select {
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
}
.audit-preview {
    background: #f8f9fa;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    margin-top: 1rem;
    border: 1px dashed #dee2e6;
}
.audit-preview-title {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.audit-preview-content {
    font-family: monospace;
    font-size: 0.7rem;
    color: #495057;
    white-space: pre-wrap;
}
.admin-btn-primary {
    background: #1e3a5f;
    border-color: #1e3a5f;
    color: #fff;
}
.admin-btn-primary:hover {
    background: #2c5282;
    border-color: #2c5282;
    color: #fff;
}
.request-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 1rem;
    border-left: 3px solid #ecc94b;
}
.request-card.approved {
    border-left-color: #48bb78;
}
.request-card.rejected {
    border-left-color: #e53e3e;
}
.request-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f3f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.request-card-body {
    padding: 1rem 1.25rem;
}
.request-customer {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}
.request-customer-id {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-left: 0.5rem;
}
.request-meta {
    font-size: 0.75rem;
    color: #6c757d;
}
.request-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.request-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.request-detail-label {
    font-size: 0.7rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.request-detail-value {
    font-size: 0.85rem;
    color: #374151;
    font-weight: 500;
}
.request-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f3f5;
}
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #9ca3af;
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.empty-state h6 {
    color: #6c757d;
    margin-bottom: 0.5rem;
}
.review-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.review-filters select {
    font-size: 0.85rem;
    min-width: 150px;
}
.review-stat-cards {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.review-stat-card {
    flex: 1;
    background: #fff;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    text-align: center;
}
.review-stat-card.pending {
    border-top: 3px solid #ecc94b;
}
.review-stat-card.approved-today {
    border-top: 3px solid #48bb78;
}
.review-stat-card.rejected-today {
    border-top: 3px solid #e53e3e;
}
.review-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e3a5f;
}
.review-stat-label {
    font-size: 0.7rem;
    color: #6c757d;
    text-transform: uppercase;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
            <li class="breadcrumb-item"><a href="/admin/security">Security & Compliance</a></li>
            <li class="breadcrumb-item active">Country Controls</li>
        </ol>
    </nav>

    <div class="country-controls-header">
        <div class="country-controls-title">
            <h4><i class="fas fa-globe me-2"></i>Country Controls<span class="admin-internal-badge">Admin Only</span></h4>
            <p>Manage allowed destination countries for SMS messaging across all customer accounts</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="enforcement-sync-indicator" id="syncIndicator">
                <i class="fas fa-check-circle"></i>
                <span>All systems synchronized</span>
            </div>
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshCountryData()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>

    <ul class="nav admin-tabs" id="countryControlsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="review-tab" data-bs-toggle="tab" data-bs-target="#reviewPane" type="button" role="tab">
                <i class="fas fa-inbox me-1"></i>Review
                <span class="badge pending-badge" id="pendingRequestsBadge">3</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="countries-tab" data-bs-toggle="tab" data-bs-target="#countriesPane" type="button" role="tab">
                <i class="fas fa-globe-americas me-1"></i>Countries
            </button>
        </li>
    </ul>

    <div class="tab-content" id="countryControlsTabContent">
        <div class="tab-pane fade show active" id="reviewPane" role="tabpanel">
            <div class="review-stat-cards">
                <div class="review-stat-card pending">
                    <div class="review-stat-value" id="reviewPendingCount">3</div>
                    <div class="review-stat-label">Pending Requests</div>
                </div>
                <div class="review-stat-card approved-today">
                    <div class="review-stat-value" id="reviewApprovedToday">5</div>
                    <div class="review-stat-label">Approved Today</div>
                </div>
                <div class="review-stat-card rejected-today">
                    <div class="review-stat-value" id="reviewRejectedToday">1</div>
                    <div class="review-stat-label">Rejected Today</div>
                </div>
            </div>

            <div class="review-filters">
                <select class="form-select form-select-sm" id="reviewStatusFilter">
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="">All Requests</option>
                </select>
                <select class="form-select form-select-sm" id="reviewCustomerFilter">
                    <option value="">All Customers</option>
                </select>
                <select class="form-select form-select-sm" id="reviewCountryFilter">
                    <option value="">All Countries</option>
                </select>
            </div>

            <div id="requestsList"></div>
        </div>

        <div class="tab-pane fade" id="countriesPane" role="tabpanel">
            <div class="enforcement-banner">
                <h6><i class="fas fa-shield-alt"></i>Shared Enforcement Configuration</h6>
                <p>Changes here immediately apply across all enforcement points. No restart required.</p>
                <div class="enforcement-points">
                    <div class="enforcement-point">
                        <i class="fas fa-check-circle"></i>
                        <span>Customer Portal Security Settings</span>
                    </div>
                    <div class="enforcement-point">
                        <i class="fas fa-check-circle"></i>
                        <span>Send Message Validation</span>
                    </div>
                    <div class="enforcement-point">
                        <i class="fas fa-check-circle"></i>
                        <span>API Submission Validation</span>
                    </div>
                    <div class="enforcement-point">
                        <i class="fas fa-check-circle"></i>
                        <span>Bulk Campaign Processing</span>
                    </div>
                </div>
            </div>

            <div class="country-stats">
                <div class="country-stat-card allowed">
                    <div class="country-stat-value" id="allowedCount">142</div>
                    <div class="country-stat-label">Allowed Countries</div>
                </div>
                <div class="country-stat-card blocked">
                    <div class="country-stat-value" id="blockedCount">23</div>
                    <div class="country-stat-label">Blocked Countries</div>
                </div>
                <div class="country-stat-card restricted">
                    <div class="country-stat-value" id="restrictedCount">12</div>
                    <div class="country-stat-label">Restricted (Approval Required)</div>
                </div>
                <div class="country-stat-card pending">
                    <div class="country-stat-value" id="pendingCount">5</div>
                    <div class="country-stat-label">Pending Review</div>
                </div>
            </div>

            <div class="country-table-card">
                <div class="country-table-header">
                    <div class="d-flex align-items-center gap-3">
                        <h6><i class="fas fa-list me-2"></i>Global Policy & Overrides</h6>
                        <div class="bulk-actions">
                            <select class="form-select form-select-sm" id="bulkStatusFilter">
                                <option value="">All Statuses</option>
                                <option value="allowed">Allowed</option>
                                <option value="blocked">Blocked</option>
                                <option value="restricted">Restricted</option>
                                <option value="pending">Pending</option>
                            </select>
                            <select class="form-select form-select-sm" id="bulkRiskFilter">
                                <option value="">All Risk Levels</option>
                                <option value="low">Low Risk</option>
                                <option value="medium">Medium Risk</option>
                                <option value="high">High Risk</option>
                                <option value="critical">Critical Risk</option>
                            </select>
                        </div>
                    </div>
                    <div class="country-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control form-control-sm" id="countrySearch" placeholder="Search countries...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="country-table" id="countryTable">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="selectAllCountries"></th>
                                <th>Country</th>
                                <th>ISO Code</th>
                                <th>Dial Code</th>
                                <th>Status</th>
                        <th>Risk Level</th>
                        <th>Customer Overrides</th>
                        <th>Last Updated</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="countryTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <div class="audit-preview" id="auditPreview" style="display: none;">
        <div class="audit-preview-title">
            <i class="fas fa-history"></i>Pending Audit Record (Preview)
        </div>
        <div class="audit-preview-content" id="auditPreviewContent"></div>
    </div>
        </div>
    </div>
</div>

<div class="modal fade" id="countryActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="countryActionModalTitle">Update Country Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Country</label>
                    <div id="modalCountryName" class="form-control-plaintext"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Status</label>
                    <div id="modalCurrentStatus"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">New Status</label>
                    <select class="form-select" id="modalNewStatus">
                        <option value="allowed">Allowed</option>
                        <option value="blocked">Blocked</option>
                        <option value="restricted">Restricted (Approval Required)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason for Change <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="modalChangeReason" rows="3" placeholder="Enter reason for this change (required for audit)..."></textarea>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    This change will immediately affect all customers and enforcement points.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn admin-btn-primary" id="confirmStatusChange">
                    <i class="fas fa-save me-1"></i>Apply Change
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initCountryControls();
});

var CountryControlsService = (function() {
    var CONFIG_KEY = 'QUICKSMS_ALLOWED_DESTINATION_COUNTRIES';
    var VERSION_KEY = 'QUICKSMS_COUNTRY_CONFIG_VERSION';
    
    var sharedConfig = {
        version: 0,
        lastUpdated: null,
        updatedBy: null,
        countries: {}
    };

    function getSharedConfig() {
        return JSON.parse(JSON.stringify(sharedConfig));
    }

    function updateCountryStatus(countryCode, newStatus, reason, adminUser) {
        var beforeState = sharedConfig.countries[countryCode] ? 
            JSON.parse(JSON.stringify(sharedConfig.countries[countryCode])) : null;

        sharedConfig.countries[countryCode] = sharedConfig.countries[countryCode] || {};
        sharedConfig.countries[countryCode].status = newStatus;
        sharedConfig.countries[countryCode].lastUpdated = new Date().toISOString();
        sharedConfig.countries[countryCode].updatedBy = adminUser.email;

        sharedConfig.version++;
        sharedConfig.lastUpdated = new Date().toISOString();
        sharedConfig.updatedBy = adminUser.email;

        var auditEvent = createAuditEvent(countryCode, beforeState, sharedConfig.countries[countryCode], reason, adminUser);
        
        broadcastConfigUpdate();

        return {
            success: true,
            configVersion: sharedConfig.version,
            auditEvent: auditEvent
        };
    }

    function createAuditEvent(countryCode, beforeState, afterState, reason, adminUser) {
        return {
            id: 'CCNTRL-' + Date.now(),
            eventType: 'COUNTRY_CONTROL_UPDATED',
            eventLabel: 'Country Control Updated',
            timestamp: new Date().toISOString(),
            actor: {
                id: adminUser.id,
                email: adminUser.email,
                role: adminUser.role
            },
            admin_actor_id: adminUser.id,
            category: 'security',
            severity: 'high',
            result: 'success',
            isInternalOnly: true,
            isAdminEvent: true,
            target: {
                type: 'country_control',
                countryCode: countryCode
            },
            details: {
                countryCode: countryCode,
                reason: reason,
                beforeState: beforeState,
                afterState: afterState,
                configVersion: sharedConfig.version,
                enforcementPoints: [
                    'customer_portal_security_settings',
                    'send_message_validation',
                    'api_submission_validation',
                    'bulk_campaign_processing'
                ]
            },
            ip: '10.0.1.50'
        };
    }

    function broadcastConfigUpdate() {
        console.log('[CountryControls] Broadcasting config update v' + sharedConfig.version);
        
        window.dispatchEvent(new CustomEvent('countryConfigUpdated', {
            detail: {
                version: sharedConfig.version,
                timestamp: sharedConfig.lastUpdated
            }
        }));
    }

    function isCountryAllowed(countryCode, customerId) {
        var globalStatus = sharedConfig.countries[countryCode]?.status || 'allowed';
        
        if (globalStatus === 'blocked') {
            return { allowed: false, reason: 'Blocked globally by administrator' };
        }
        
        if (globalStatus === 'restricted') {
            return { allowed: false, reason: 'Requires approval - country is restricted', requiresApproval: true };
        }
        
        return { allowed: true };
    }

    function validateDestination(phoneNumber, customerId) {
        var countryCode = extractCountryCode(phoneNumber);
        return isCountryAllowed(countryCode, customerId);
    }

    function extractCountryCode(phoneNumber) {
        var dialCodeMap = {
            '1': 'US', '44': 'GB', '33': 'FR', '49': 'DE', '39': 'IT',
            '34': 'ES', '81': 'JP', '86': 'CN', '91': 'IN', '7': 'RU'
        };
        var cleaned = phoneNumber.replace(/[^0-9]/g, '');
        for (var code in dialCodeMap) {
            if (cleaned.startsWith(code)) {
                return dialCodeMap[code];
            }
        }
        return 'UNKNOWN';
    }

    return {
        getSharedConfig: getSharedConfig,
        updateCountryStatus: updateCountryStatus,
        isCountryAllowed: isCountryAllowed,
        validateDestination: validateDestination,
        CONFIG_KEY: CONFIG_KEY
    };
})();

window.CountryControlsService = CountryControlsService;

var countries = [];
var countryRequests = [];
var currentAdmin = {
    id: 'ADM001',
    email: 'admin@quicksms.co.uk',
    role: 'super_admin'
};
var selectedCountry = null;
var selectedRequest = null;

function initCountryControls() {
    countries = generateMockCountries();
    countryRequests = generateMockRequests();
    renderCountryTable();
    renderRequestsList();
    bindEvents();
    updateStats();
    updateReviewStats();
    
    console.log('[CountryControls] Initialized with shared enforcement service');
    console.log('[CountryControls] Config version:', CountryControlsService.getSharedConfig().version);
}

function generateMockRequests() {
    return [
        {
            id: 'REQ-001',
            customer: { id: 'CUST-001', name: 'TechStart Ltd', accountNumber: 'ACC-10045' },
            country: { code: 'NG', name: 'Nigeria', dialCode: '+234' },
            requestType: 'enable',
            reason: 'We have legitimate business operations in Nigeria and need to send SMS to our local customers.',
            submittedBy: 'james@techstart.co.uk',
            submittedAt: '2026-01-28 14:30',
            status: 'pending',
            risk: 'high',
            estimatedVolume: '5,000 messages/month'
        },
        {
            id: 'REQ-002',
            customer: { id: 'CUST-002', name: 'HealthFirst UK', accountNumber: 'ACC-10089' },
            country: { code: 'IN', name: 'India', dialCode: '+91' },
            requestType: 'enable',
            reason: 'Need to send appointment reminders to patients in our Indian branch.',
            submittedBy: 'dr.jones@healthfirst.nhs.uk',
            submittedAt: '2026-01-28 10:15',
            status: 'pending',
            risk: 'medium',
            estimatedVolume: '2,000 messages/month'
        },
        {
            id: 'REQ-003',
            customer: { id: 'CUST-003', name: 'E-Commerce Hub', accountNumber: 'ACC-10112' },
            country: { code: 'PH', name: 'Philippines', dialCode: '+63' },
            requestType: 'enable',
            reason: 'Expanding e-commerce operations to Philippines, need order confirmation SMS.',
            submittedBy: 'ops@ecommercehub.com',
            submittedAt: '2026-01-27 16:45',
            status: 'pending',
            risk: 'high',
            estimatedVolume: '10,000 messages/month'
        },
        {
            id: 'REQ-004',
            customer: { id: 'CUST-004', name: 'RetailMax', accountNumber: 'ACC-10078' },
            country: { code: 'BR', name: 'Brazil', dialCode: '+55' },
            requestType: 'enable',
            reason: 'Opening new retail stores in Brazil.',
            submittedBy: 'admin@retailmax.com',
            submittedAt: '2026-01-27 09:00',
            status: 'approved',
            risk: 'medium',
            estimatedVolume: '8,000 messages/month',
            reviewedBy: 'sarah.johnson@quicksms.co.uk',
            reviewedAt: '2026-01-27 11:30'
        },
        {
            id: 'REQ-005',
            customer: { id: 'CUST-005', name: 'Unknown Corp', accountNumber: 'ACC-10099' },
            country: { code: 'RU', name: 'Russia', dialCode: '+7' },
            requestType: 'enable',
            reason: 'Business expansion.',
            submittedBy: 'info@unknowncorp.com',
            submittedAt: '2026-01-26 14:00',
            status: 'rejected',
            risk: 'critical',
            estimatedVolume: '50,000 messages/month',
            reviewedBy: 'emily.chen@quicksms.co.uk',
            reviewedAt: '2026-01-26 15:30',
            rejectionReason: 'Russia is on the blocked countries list due to regulatory compliance.'
        }
    ];
}

function renderRequestsList() {
    var container = document.getElementById('requestsList');
    var statusFilter = document.getElementById('reviewStatusFilter').value;
    var customerFilter = document.getElementById('reviewCustomerFilter').value;
    var countryFilter = document.getElementById('reviewCountryFilter').value;

    var filtered = countryRequests.filter(function(r) {
        var matchesStatus = !statusFilter || r.status === statusFilter;
        var matchesCustomer = !customerFilter || r.customer.id === customerFilter;
        var matchesCountry = !countryFilter || r.country.code === countryFilter;
        return matchesStatus && matchesCustomer && matchesCountry;
    });

    if (filtered.length === 0) {
        container.innerHTML = 
            '<div class="empty-state">' +
                '<i class="fas fa-inbox"></i>' +
                '<h6>No requests found</h6>' +
                '<p class="small">There are no country access requests matching your filters.</p>' +
            '</div>';
        return;
    }

    container.innerHTML = '';
    filtered.forEach(function(request) {
        var card = document.createElement('div');
        card.className = 'request-card ' + request.status;
        
        var statusBadge = '<span class="status-badge ' + request.status + '">' + capitalize(request.status) + '</span>';
        var riskBadge = '<span class="status-badge ' + request.risk + '" style="margin-left: 0.5rem;">' + 
            capitalize(request.risk) + ' Risk</span>';

        var actionsHtml = '';
        if (request.status === 'pending') {
            actionsHtml = 
                '<div class="request-actions">' +
                    '<button class="btn btn-sm" style="background: #48bb78; color: #fff;" onclick="approveRequest(\'' + request.id + '\')">' +
                        '<i class="fas fa-check me-1"></i>Approve' +
                    '</button>' +
                    '<button class="btn btn-sm" style="background: #e53e3e; color: #fff;" onclick="rejectRequest(\'' + request.id + '\')">' +
                        '<i class="fas fa-times me-1"></i>Reject' +
                    '</button>' +
                    '<button class="btn btn-sm btn-outline-secondary" onclick="viewRequestDetails(\'' + request.id + '\')">' +
                        '<i class="fas fa-eye me-1"></i>Details' +
                    '</button>' +
                '</div>';
        } else {
            var reviewInfo = request.status === 'approved' ? 
                '<span class="text-success small"><i class="fas fa-check-circle me-1"></i>Approved by ' + request.reviewedBy + ' on ' + request.reviewedAt + '</span>' :
                '<span class="text-danger small"><i class="fas fa-times-circle me-1"></i>Rejected by ' + request.reviewedBy + ' on ' + request.reviewedAt + '</span>';
            actionsHtml = '<div class="request-actions">' + reviewInfo + '</div>';
        }

        card.innerHTML = 
            '<div class="request-card-header">' +
                '<div>' +
                    '<span class="request-customer">' + request.customer.name + '</span>' +
                    '<span class="request-customer-id">' + request.customer.accountNumber + '</span>' +
                '</div>' +
                '<div class="request-meta">' +
                    statusBadge + riskBadge +
                '</div>' +
            '</div>' +
            '<div class="request-card-body">' +
                '<div class="request-details">' +
                    '<div class="request-detail-item">' +
                        '<div class="request-detail-label">Country Requested</div>' +
                        '<div class="request-detail-value">' + request.country.name + ' (' + request.country.dialCode + ')</div>' +
                    '</div>' +
                    '<div class="request-detail-item">' +
                        '<div class="request-detail-label">Estimated Volume</div>' +
                        '<div class="request-detail-value">' + request.estimatedVolume + '</div>' +
                    '</div>' +
                    '<div class="request-detail-item">' +
                        '<div class="request-detail-label">Submitted By</div>' +
                        '<div class="request-detail-value">' + request.submittedBy + '</div>' +
                    '</div>' +
                    '<div class="request-detail-item">' +
                        '<div class="request-detail-label">Submitted</div>' +
                        '<div class="request-detail-value">' + request.submittedAt + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="mt-3 small text-muted">' +
                    '<strong>Reason:</strong> ' + request.reason +
                '</div>' +
                actionsHtml +
            '</div>';
        container.appendChild(card);
    });
}

function updateReviewStats() {
    var pending = countryRequests.filter(function(r) { return r.status === 'pending'; }).length;
    var approvedToday = countryRequests.filter(function(r) { 
        return r.status === 'approved' && r.reviewedAt && r.reviewedAt.startsWith('2026-01-28'); 
    }).length;
    var rejectedToday = countryRequests.filter(function(r) { 
        return r.status === 'rejected' && r.reviewedAt && r.reviewedAt.startsWith('2026-01-28'); 
    }).length;

    document.getElementById('reviewPendingCount').textContent = pending;
    document.getElementById('reviewApprovedToday').textContent = approvedToday;
    document.getElementById('reviewRejectedToday').textContent = rejectedToday;
    document.getElementById('pendingRequestsBadge').textContent = pending;
    
    if (pending === 0) {
        document.getElementById('pendingRequestsBadge').style.display = 'none';
    } else {
        document.getElementById('pendingRequestsBadge').style.display = 'inline';
    }
}

function approveRequest(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;

    if (!confirm('Approve country access for ' + request.customer.name + ' to ' + request.country.name + '?')) {
        return;
    }

    request.status = 'approved';
    request.reviewedBy = currentAdmin.email;
    request.reviewedAt = new Date().toISOString().replace('T', ' ').substring(0, 16);

    renderRequestsList();
    updateReviewStats();
    showToast('Request approved. ' + request.customer.name + ' can now send to ' + request.country.name + '.', 'success');
}

function rejectRequest(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;

    var reason = prompt('Enter rejection reason:');
    if (!reason) return;

    request.status = 'rejected';
    request.reviewedBy = currentAdmin.email;
    request.reviewedAt = new Date().toISOString().replace('T', ' ').substring(0, 16);
    request.rejectionReason = reason;

    renderRequestsList();
    updateReviewStats();
    showToast('Request rejected.', 'info');
}

function viewRequestDetails(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;
    alert('Request Details:\n\nCustomer: ' + request.customer.name + '\nCountry: ' + request.country.name + '\nReason: ' + request.reason);
}

function generateMockCountries() {
    var countryData = [
        { code: 'GB', name: 'United Kingdom', dialCode: '+44', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'US', name: 'United States', dialCode: '+1', status: 'allowed', risk: 'low', overrides: 3 },
        { code: 'DE', name: 'Germany', dialCode: '+49', status: 'allowed', risk: 'low', overrides: 1 },
        { code: 'FR', name: 'France', dialCode: '+33', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'ES', name: 'Spain', dialCode: '+34', status: 'allowed', risk: 'low', overrides: 2 },
        { code: 'IT', name: 'Italy', dialCode: '+39', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'NL', name: 'Netherlands', dialCode: '+31', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'BE', name: 'Belgium', dialCode: '+32', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'CH', name: 'Switzerland', dialCode: '+41', status: 'allowed', risk: 'low', overrides: 1 },
        { code: 'AT', name: 'Austria', dialCode: '+43', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'PL', name: 'Poland', dialCode: '+48', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'RU', name: 'Russia', dialCode: '+7', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'BY', name: 'Belarus', dialCode: '+375', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'IR', name: 'Iran', dialCode: '+98', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'KP', name: 'North Korea', dialCode: '+850', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'SY', name: 'Syria', dialCode: '+963', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'CU', name: 'Cuba', dialCode: '+53', status: 'blocked', risk: 'high', overrides: 0 },
        { code: 'NG', name: 'Nigeria', dialCode: '+234', status: 'restricted', risk: 'high', overrides: 5 },
        { code: 'PH', name: 'Philippines', dialCode: '+63', status: 'restricted', risk: 'high', overrides: 3 },
        { code: 'IN', name: 'India', dialCode: '+91', status: 'allowed', risk: 'medium', overrides: 8 },
        { code: 'PK', name: 'Pakistan', dialCode: '+92', status: 'restricted', risk: 'high', overrides: 2 },
        { code: 'BD', name: 'Bangladesh', dialCode: '+880', status: 'restricted', risk: 'medium', overrides: 1 },
        { code: 'VN', name: 'Vietnam', dialCode: '+84', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'TH', name: 'Thailand', dialCode: '+66', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'MY', name: 'Malaysia', dialCode: '+60', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'SG', name: 'Singapore', dialCode: '+65', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'AU', name: 'Australia', dialCode: '+61', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'NZ', name: 'New Zealand', dialCode: '+64', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'JP', name: 'Japan', dialCode: '+81', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'KR', name: 'South Korea', dialCode: '+82', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'CN', name: 'China', dialCode: '+86', status: 'restricted', risk: 'high', overrides: 4 },
        { code: 'HK', name: 'Hong Kong', dialCode: '+852', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'TW', name: 'Taiwan', dialCode: '+886', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'BR', name: 'Brazil', dialCode: '+55', status: 'allowed', risk: 'medium', overrides: 2 },
        { code: 'MX', name: 'Mexico', dialCode: '+52', status: 'allowed', risk: 'medium', overrides: 1 },
        { code: 'ZA', name: 'South Africa', dialCode: '+27', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'AE', name: 'United Arab Emirates', dialCode: '+971', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'SA', name: 'Saudi Arabia', dialCode: '+966', status: 'restricted', risk: 'medium', overrides: 1 },
        { code: 'IL', name: 'Israel', dialCode: '+972', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'VE', name: 'Venezuela', dialCode: '+58', status: 'blocked', risk: 'high', overrides: 0 }
    ];

    return countryData.map(function(c, index) {
        c.id = index + 1;
        c.lastUpdated = getRandomDate();
        return c;
    });
}

function getRandomDate() {
    var dates = [
        '2026-01-28 14:30', '2026-01-27 10:15', '2026-01-26 16:45',
        '2026-01-25 09:00', '2026-01-24 11:30', '2026-01-20 08:00'
    ];
    return dates[Math.floor(Math.random() * dates.length)];
}

function renderCountryTable() {
    var tbody = document.getElementById('countryTableBody');
    var searchTerm = document.getElementById('countrySearch').value.toLowerCase();
    var statusFilter = document.getElementById('bulkStatusFilter').value;
    var riskFilter = document.getElementById('bulkRiskFilter').value;

    var filtered = countries.filter(function(c) {
        var matchesSearch = c.name.toLowerCase().includes(searchTerm) || 
                           c.code.toLowerCase().includes(searchTerm) ||
                           c.dialCode.includes(searchTerm);
        var matchesStatus = !statusFilter || c.status === statusFilter;
        var matchesRisk = !riskFilter || c.risk === riskFilter;
        return matchesSearch && matchesStatus && matchesRisk;
    });

    tbody.innerHTML = '';

    filtered.forEach(function(country) {
        var row = document.createElement('tr');
        row.innerHTML = 
            '<td><input type="checkbox" class="country-checkbox" data-code="' + country.code + '"></td>' +
            '<td>' +
                '<span class="country-name">' + country.name + '</span>' +
            '</td>' +
            '<td><code>' + country.code + '</code></td>' +
            '<td>' + country.dialCode + '</td>' +
            '<td><span class="status-badge ' + country.status + '">' + capitalize(country.status) + '</span></td>' +
            '<td>' +
                '<div class="risk-indicator">' +
                    '<span class="risk-dot ' + country.risk + '"></span>' +
                    '<span>' + capitalize(country.risk) + '</span>' +
                '</div>' +
            '</td>' +
            '<td>' + (country.overrides > 0 ? 
                '<span class="customer-override-badge">' + country.overrides + ' customers</span>' : 
                '<span class="text-muted">None</span>') + 
            '</td>' +
            '<td class="small text-muted">' + country.lastUpdated + '</td>' +
            '<td>' +
                '<div class="action-btn-group">' +
                    (country.status !== 'allowed' ? 
                        '<button class="action-btn allow" onclick="openActionModal(\'' + country.code + '\', \'allowed\')"><i class="fas fa-check"></i> Allow</button>' : '') +
                    (country.status !== 'blocked' ? 
                        '<button class="action-btn block" onclick="openActionModal(\'' + country.code + '\', \'blocked\')"><i class="fas fa-ban"></i> Block</button>' : '') +
                    (country.status !== 'restricted' ? 
                        '<button class="action-btn restrict" onclick="openActionModal(\'' + country.code + '\', \'restricted\')"><i class="fas fa-exclamation-triangle"></i></button>' : '') +
                '</div>' +
            '</td>';
        tbody.appendChild(row);
    });
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function updateStats() {
    document.getElementById('allowedCount').textContent = countries.filter(function(c) { return c.status === 'allowed'; }).length;
    document.getElementById('blockedCount').textContent = countries.filter(function(c) { return c.status === 'blocked'; }).length;
    document.getElementById('restrictedCount').textContent = countries.filter(function(c) { return c.status === 'restricted'; }).length;
    document.getElementById('pendingCount').textContent = countries.filter(function(c) { return c.status === 'pending'; }).length;
}

function bindEvents() {
    document.getElementById('countrySearch').addEventListener('input', renderCountryTable);
    document.getElementById('bulkStatusFilter').addEventListener('change', renderCountryTable);
    document.getElementById('bulkRiskFilter').addEventListener('change', renderCountryTable);

    document.getElementById('reviewStatusFilter').addEventListener('change', renderRequestsList);
    document.getElementById('reviewCustomerFilter').addEventListener('change', renderRequestsList);
    document.getElementById('reviewCountryFilter').addEventListener('change', renderRequestsList);

    document.getElementById('confirmStatusChange').addEventListener('click', function() {
        applyStatusChange();
    });

    document.getElementById('selectAllCountries').addEventListener('change', function() {
        var checked = this.checked;
        document.querySelectorAll('.country-checkbox').forEach(function(cb) {
            cb.checked = checked;
        });
    });

    $('button[data-bs-target="#countriesPane"]').on('shown.bs.tab', function() {
        console.log('[CountryControls] Countries tab activated');
        renderCountryTable();
    });
}

function openActionModal(countryCode, newStatus) {
    selectedCountry = countries.find(function(c) { return c.code === countryCode; });
    if (!selectedCountry) return;

    document.getElementById('modalCountryName').textContent = selectedCountry.name + ' (' + selectedCountry.code + ')';
    document.getElementById('modalCurrentStatus').innerHTML = '<span class="status-badge ' + selectedCountry.status + '">' + capitalize(selectedCountry.status) + '</span>';
    document.getElementById('modalNewStatus').value = newStatus;
    document.getElementById('modalChangeReason').value = '';

    var modal = new bootstrap.Modal(document.getElementById('countryActionModal'));
    modal.show();
}

function applyStatusChange() {
    var newStatus = document.getElementById('modalNewStatus').value;
    var reason = document.getElementById('modalChangeReason').value.trim();

    if (!reason) {
        alert('Please provide a reason for this change.');
        return;
    }

    var syncIndicator = document.getElementById('syncIndicator');
    syncIndicator.classList.add('syncing');
    syncIndicator.innerHTML = '<i class="fas fa-sync-alt"></i><span>Synchronizing...</span>';

    var result = CountryControlsService.updateCountryStatus(
        selectedCountry.code,
        newStatus,
        reason,
        currentAdmin
    );

    selectedCountry.status = newStatus;
    selectedCountry.lastUpdated = new Date().toISOString().replace('T', ' ').substring(0, 16);

    console.log('[CountryControls] Audit event created:', result.auditEvent);

    setTimeout(function() {
        syncIndicator.classList.remove('syncing');
        syncIndicator.innerHTML = '<i class="fas fa-check-circle"></i><span>All systems synchronized</span>';

        renderCountryTable();
        updateStats();

        bootstrap.Modal.getInstance(document.getElementById('countryActionModal')).hide();

        showToast('Country status updated and synchronized across all enforcement points', 'success');
    }, 1000);
}

function refreshCountryData() {
    var syncIndicator = document.getElementById('syncIndicator');
    syncIndicator.classList.add('syncing');
    syncIndicator.innerHTML = '<i class="fas fa-sync-alt"></i><span>Refreshing...</span>';

    setTimeout(function() {
        syncIndicator.classList.remove('syncing');
        syncIndicator.innerHTML = '<i class="fas fa-check-circle"></i><span>All systems synchronized</span>';
        showToast('Country data refreshed', 'info');
    }, 800);
}

function showToast(message, type) {
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(toastContainer);
    }

    var bgColor = type === 'success' ? '#48bb78' : type === 'error' ? '#e53e3e' : type === 'warning' ? '#ecc94b' : '#1e3a5f';
    var toast = document.createElement('div');
    toast.style.cssText = 'background: ' + bgColor + '; color: #fff; padding: 0.75rem 1.25rem; border-radius: 0.375rem; margin-bottom: 0.5rem; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    toast.textContent = message;
    toastContainer.appendChild(toast);

    setTimeout(function() {
        toast.remove();
    }, 4000);
}
</script>
@endpush

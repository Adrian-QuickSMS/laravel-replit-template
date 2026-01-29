@extends('layouts.admin')

@section('title', 'Security & Compliance Controls')

@push('styles')
<style>
.sec-controls-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.sec-controls-title h4 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.sec-controls-title p {
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
.sec-enforcement-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.sec-enforcement-banner h6 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.sec-enforcement-banner p {
    margin: 0;
    font-size: 0.8rem;
    opacity: 0.9;
}
.sec-enforcement-points {
    display: flex;
    gap: 2rem;
    margin-top: 0.75rem;
    flex-wrap: wrap;
}
.sec-enforcement-point {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}
.sec-enforcement-point i {
    color: #48bb78;
}
.sec-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.sec-stat-card {
    flex: 1;
    background: #fff;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-left: 3px solid;
}
.sec-stat-card.active {
    border-left-color: #48bb78;
}
.sec-stat-card.blocked {
    border-left-color: #e53e3e;
}
.sec-stat-card.pending {
    border-left-color: #ecc94b;
}
.sec-stat-card.total {
    border-left-color: #1e3a5f;
}
.sec-stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e3a5f;
}
.sec-stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sec-table-card {
    background: #fff;
    border-radius: 0.5rem;
    border: 1px solid #e5e9f2;
    margin-bottom: 1.5rem;
    overflow: hidden;
}
.sec-table-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fc;
}
.sec-table-header h6 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.sec-search-box {
    position: relative;
    width: 280px;
}
.sec-search-box input {
    padding-left: 2.25rem;
    font-size: 0.85rem;
    border: 1px solid #ced4da;
    border-radius: 6px;
}
.sec-search-box input:focus {
    border-color: #1e3a5f;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1);
}
.sec-search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}
.sec-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}
.sec-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e5e9f2;
}
.sec-table th {
    padding: 0.5rem 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #1e3a5f;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
.sec-table th:hover {
    background: #e9ecef;
}
.sec-table th i.fa-sort {
    margin-left: 0.35rem;
    opacity: 0.4;
    font-size: 0.65rem;
}
.sec-table th:hover i.fa-sort {
    opacity: 0.7;
}
.sec-table td {
    padding: 0.5rem 0.35rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
.sec-table tbody tr:hover {
    background: #f8f9fc;
}
.sec-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}
.sec-status-badge.active {
    background: #c6f6d5;
    color: #276749;
}
.sec-status-badge.blocked {
    background: #fed7d7;
    color: #9b2c2c;
}
.sec-status-badge.pending {
    background: #fefcbf;
    color: #744210;
}
.sec-status-badge.draft {
    background: #e2e8f0;
    color: #4a5568;
}
.sec-status-badge.disabled {
    background: #f3f4f6;
    color: #6b7280;
}
.mapping-chip {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 2px 6px;
    margin: 1px 2px;
    font-size: 0.75rem;
}
.mapping-chip code {
    background: #e8f4fd;
    padding: 1px 3px;
    border-radius: 2px;
    font-family: 'Courier New', monospace;
    color: #1e3a5f;
}
.sec-filter-row {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    flex-wrap: wrap;
    align-items: flex-end;
}
.sec-filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.sec-filter-group label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sec-filter-group select,
.sec-filter-group input {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 6px;
    min-width: 150px;
}
.sec-filter-group select:focus,
.sec-filter-group input:focus {
    border-color: #1e3a5f;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1);
    outline: none;
}
.sec-filter-actions {
    display: flex;
    gap: 0.5rem;
    margin-left: auto;
}
.sec-btn-primary {
    background: #1e3a5f;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.15s;
}
.sec-btn-primary:hover {
    background: #2c5282;
}
.sec-btn-outline {
    background: transparent;
    color: #6c757d;
    border: 1px solid #ced4da;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.15s;
}
.sec-btn-outline:hover {
    background: #f8f9fa;
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.action-menu-btn {
    background: transparent;
    border: none;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.15s;
}
.action-menu-btn:hover {
    color: #1e3a5f;
    background: rgba(30, 58, 95, 0.08);
}
.sec-empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}
.sec-empty-state i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}
.sec-empty-state h6 {
    color: #1e3a5f;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.sec-empty-state p {
    font-size: 0.85rem;
    margin: 0;
}
.sec-sync-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #48bb78;
}
.sec-sync-status i {
    font-size: 0.7rem;
}
.sec-refresh-btn {
    background: transparent;
    border: 1px solid #ced4da;
    color: #6c757d;
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.15s;
}
.sec-refresh-btn:hover {
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.tab-description {
    background: #f8f9fc;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.tab-description h6 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    color: #1e3a5f;
}
.tab-description p {
    margin: 0;
    font-size: 0.85rem;
    color: #6c757d;
}
</style>
@endpush

@section('content')
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)">Security & Compliance</a></li>
                <li class="breadcrumb-item active">Security & Compliance Controls</li>
            </ol>
        </div>

        <div class="sec-controls-header">
            <div class="sec-controls-title">
                <h4><i class="fas fa-shield-alt me-2" style="color: #1e3a5f;"></i> Security & Compliance Controls <span class="admin-internal-badge">ADMIN ONLY</span></h4>
                <p>Manage security rules, content policies, and compliance controls across the platform</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="sec-sync-status">
                    <i class="fas fa-check-circle"></i>
                    All systems synchronized
                </span>
                <button class="sec-btn sec-btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#featureFlagsModal" title="Manage Feature Flags">
                    <i class="fas fa-toggle-on me-1"></i> Feature Flags
                </button>
                <button class="sec-refresh-btn" onclick="refreshAllControls()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <ul class="nav admin-tabs" id="securityControlsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="senderid-controls-tab" data-bs-toggle="tab" data-bs-target="#senderid-controls" type="button" role="tab">
                    <i class="fas fa-id-badge me-1"></i> SenderID Controls
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="message-content-tab" data-bs-toggle="tab" data-bs-target="#message-content" type="button" role="tab">
                    <i class="fas fa-comment-alt me-1"></i> Message Content Controls
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="url-controls-tab" data-bs-toggle="tab" data-bs-target="#url-controls" type="button" role="tab">
                    <i class="fas fa-link me-1"></i> URL Controls
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="normalisation-rules-tab" data-bs-toggle="tab" data-bs-target="#normalisation-rules" type="button" role="tab">
                    <i class="fas fa-globe me-1"></i> Normalisation Rules (Global)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="quarantine-review-tab" data-bs-toggle="tab" data-bs-target="#quarantine-review" type="button" role="tab">
                    <i class="fas fa-exclamation-triangle me-1"></i> Quarantine & Review
                    <span class="badge pending-badge">12</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="securityControlsTabContent">
            <div class="tab-pane fade show active" id="senderid-controls" role="tabpanel">
                <div class="tab-description">
                    <h6><i class="fas fa-id-badge me-2"></i>SenderID Controls</h6>
                    <p>Manage blocked and restricted SenderIDs, configure approval requirements, and set up keyword filters for sender identification.</p>
                </div>

                <div class="sec-stats">
                    <div class="sec-stat-card active">
                        <div class="sec-stat-value" id="senderid-active-count">0</div>
                        <div class="sec-stat-label">Active Rules</div>
                    </div>
                    <div class="sec-stat-card blocked">
                        <div class="sec-stat-value" id="senderid-blocked-count">0</div>
                        <div class="sec-stat-label">Blocked SenderIDs</div>
                    </div>
                    <div class="sec-stat-card pending">
                        <div class="sec-stat-value" id="senderid-pending-count">0</div>
                        <div class="sec-stat-label">Pending Review</div>
                    </div>
                    <div class="sec-stat-card total">
                        <div class="sec-stat-value" id="senderid-total-count">0</div>
                        <div class="sec-stat-label">Total</div>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-filter-row">
                        <div class="sec-filter-group">
                            <label>Status</label>
                            <select id="senderid-filter-status">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Rule Type</label>
                            <select id="senderid-filter-type">
                                <option value="">All Types</option>
                                <option value="block">Block</option>
                                <option value="flag">Flag (Quarantine)</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Category</label>
                            <select id="senderid-filter-category">
                                <option value="">All Categories</option>
                                <option value="bank_impersonation">Bank Impersonation</option>
                                <option value="government">Government</option>
                                <option value="lottery_prize">Lottery/Prize</option>
                                <option value="brand_abuse">Brand Abuse</option>
                                <option value="premium_rate">Premium Rate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="sec-filter-actions">
                            <button class="sec-btn-primary" onclick="showAddSenderIdRuleModal()">
                                <i class="fas fa-plus"></i> Add Rule
                            </button>
                            <button class="sec-btn-outline" onclick="resetSenderIdFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>SenderID Rule Library</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search rules..." id="senderid-search">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="sec-table" id="senderid-rules-table">
                            <thead>
                                <tr>
                                    <th>Rule Name <i class="fas fa-sort"></i></th>
                                    <th>Base SenderID <i class="fas fa-sort"></i></th>
                                    <th>Rule Type <i class="fas fa-sort"></i></th>
                                    <th>Category <i class="fas fa-sort"></i></th>
                                    <th>Normalisation <i class="fas fa-sort"></i></th>
                                    <th>Status <i class="fas fa-sort"></i></th>
                                    <th>Created By <i class="fas fa-sort"></i></th>
                                    <th>Last Updated <i class="fas fa-sort"></i></th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="senderid-rules-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="senderid-empty-state" style="display: none;">
                        <i class="fas fa-id-badge"></i>
                        <h6>No SenderID Rules</h6>
                        <p>Create rules to control which SenderIDs can be used on the platform.</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="message-content" role="tabpanel">
                <div class="tab-description">
                    <h6><i class="fas fa-comment-alt me-2"></i>Message Content Controls</h6>
                    <p>Configure content filtering rules, banned keywords, and message scanning policies to ensure compliance.</p>
                </div>

                <div class="sec-stats">
                    <div class="sec-stat-card active">
                        <div class="sec-stat-value" id="content-active-count">0</div>
                        <div class="sec-stat-label">Active Filters</div>
                    </div>
                    <div class="sec-stat-card blocked">
                        <div class="sec-stat-value" id="content-blocked-count">0</div>
                        <div class="sec-stat-label">Blocked Keywords</div>
                    </div>
                    <div class="sec-stat-card pending">
                        <div class="sec-stat-value" id="content-pending-count">0</div>
                        <div class="sec-stat-label">Pending Review</div>
                    </div>
                    <div class="sec-stat-card total">
                        <div class="sec-stat-value" id="content-total-count">0</div>
                        <div class="sec-stat-label">Total Rules</div>
                    </div>
                </div>

                <div class="card mb-3" style="border: 1px solid #e9ecef; border-left: 3px solid #1e3a5f;">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background: #f8f9fa;">
                        <h6 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
                            <i class="fas fa-shield-virus me-2" style="color: #1e3a5f;"></i>Anti-Spam Controls
                        </h6>
                        <span class="badge text-white" style="background: #1e3a5f; font-size: 0.65rem;">SUPPLEMENTARY</span>
                    </div>
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="antispam-repeat-toggle" onchange="toggleAntiSpamRepeat()">
                                    <label class="form-check-label" for="antispam-repeat-toggle" style="font-size: 0.85rem;">
                                        <strong>Prevent identical content to same MSISDN within window</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    When enabled, blocks duplicate messages sent to the same recipient within the configured time window.
                                </small>
                            </div>
                            <div class="col-md-3">
                                <label for="antispam-window" class="form-label mb-1" style="font-size: 0.8rem; font-weight: 600;">Time Window</label>
                                <select class="form-select form-select-sm" id="antispam-window" onchange="updateAntiSpamWindow()" disabled>
                                    <option value="2">2 hours</option>
                                    <option value="4">4 hours</option>
                                    <option value="12">12 hours</option>
                                    <option value="24" selected>24 hours</option>
                                    <option value="48">48 hours</option>
                                </select>
                            </div>
                            <div class="col-md-3 text-end">
                                <div id="antispam-status" class="d-inline-block">
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">
                                        <i class="fas fa-toggle-off me-1"></i> Disabled
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 p-2 bg-light rounded" style="font-size: 0.75rem; border: 1px solid #e9ecef;" id="antispam-info">
                            <i class="fas fa-info-circle me-1 text-muted"></i>
                            <span class="text-muted">Enforcement is handled globally via the shared Message Enforcement Service. Blocked events will include reason: "Repeated content within window".</span>
                        </div>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-filter-row">
                        <div class="sec-filter-group">
                            <label>Status</label>
                            <select id="content-filter-status">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Match Type</label>
                            <select id="content-filter-matchtype">
                                <option value="">All Types</option>
                                <option value="keyword">Keyword</option>
                                <option value="regex">Regex</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Rule Type</label>
                            <select id="content-filter-ruletype">
                                <option value="">All Types</option>
                                <option value="block">Block</option>
                                <option value="flag">Flag (Quarantine)</option>
                            </select>
                        </div>
                        <div class="sec-filter-actions">
                            <button class="sec-btn-primary" onclick="showAddContentRuleModal()">
                                <i class="fas fa-plus"></i> Add Rule
                            </button>
                            <button class="sec-btn-outline" onclick="resetContentFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>Content Rule Library</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search rules..." id="content-search">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="sec-table" id="content-rules-table">
                            <thead>
                                <tr>
                                    <th>Rule Name <i class="fas fa-sort"></i></th>
                                    <th>Match Type <i class="fas fa-sort"></i></th>
                                    <th>Rule Type <i class="fas fa-sort"></i></th>
                                    <th>Normalisation <i class="fas fa-sort"></i></th>
                                    <th>Status <i class="fas fa-sort"></i></th>
                                    <th>Last Updated <i class="fas fa-sort"></i></th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="content-rules-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="content-empty-state" style="display: none;">
                        <i class="fas fa-comment-alt"></i>
                        <h6>No Content Rules</h6>
                        <p>Create content filtering rules to manage message compliance.</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="url-controls" role="tabpanel">
                <div class="tab-description">
                    <h6><i class="fas fa-link me-2"></i>URL Controls</h6>
                    <p>Manage URL domain/pattern rules, domain age controls, and per-account exceptions for link enforcement.</p>
                </div>

                <div class="sec-stats">
                    <div class="sec-stat-card active">
                        <div class="sec-stat-value" id="url-active-count">0</div>
                        <div class="sec-stat-label">Active Rules</div>
                    </div>
                    <div class="sec-stat-card blocked">
                        <div class="sec-stat-value" id="url-block-count">0</div>
                        <div class="sec-stat-label">Block Rules</div>
                    </div>
                    <div class="sec-stat-card pending">
                        <div class="sec-stat-value" id="url-flag-count">0</div>
                        <div class="sec-stat-label">Flag Rules</div>
                    </div>
                    <div class="sec-stat-card total">
                        <div class="sec-stat-value" id="url-total-count">0</div>
                        <div class="sec-stat-label">Total Rules</div>
                    </div>
                </div>

                <div class="sec-table-card" style="margin-bottom: 1.5rem;">
                    <div class="sec-table-header" style="border-bottom: 1px solid #e9ecef; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                        <h6 style="margin: 0;"><i class="fas fa-clock me-2" style="color: #1e3a5f;"></i>Domain Age Control</h6>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="domain-age-enabled" style="width: 2.5rem; height: 1.25rem;">
                                    <label class="form-check-label" for="domain-age-enabled" style="font-weight: 600; margin-left: 0.5rem;">
                                        Enable Domain Age Check
                                    </label>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">When enabled, newly registered domains will be blocked or flagged based on their age.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Block domains younger than</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="domain-age-hours" value="72" min="1" max="8760" disabled>
                                <span class="input-group-text">hours</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Action</label>
                            <select class="form-select" id="domain-age-action" disabled>
                                <option value="block">Block</option>
                                <option value="flag">Flag (Quarantine)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveDomainAgeSettings()">
                            <i class="fas fa-save me-1"></i> Save Settings
                        </button>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-filter-row">
                        <div class="sec-filter-group">
                            <label>Status</label>
                            <select id="url-filter-status">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Match Type</label>
                            <select id="url-filter-matchtype">
                                <option value="">All Types</option>
                                <option value="exact">Exact Domain</option>
                                <option value="wildcard">Wildcard</option>
                                <option value="regex">Regex</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Rule Type</label>
                            <select id="url-filter-ruletype">
                                <option value="">All Types</option>
                                <option value="block">Block</option>
                                <option value="flag">Flag</option>
                            </select>
                        </div>
                        <div class="sec-filter-actions">
                            <button class="sec-btn-primary" onclick="showAddUrlRuleModal()">
                                <i class="fas fa-plus"></i> Add Rule
                            </button>
                            <button class="sec-btn-outline" onclick="resetUrlFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>URL Rule Library</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search domains/patterns..." id="url-search">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="sec-table" id="url-rules-table">
                            <thead>
                                <tr>
                                    <th>Domain/Pattern <i class="fas fa-sort"></i></th>
                                    <th>Match Type <i class="fas fa-sort"></i></th>
                                    <th>Rule Type <i class="fas fa-sort"></i></th>
                                    <th>Domain Age <i class="fas fa-sort"></i></th>
                                    <th>Status <i class="fas fa-sort"></i></th>
                                    <th>Last Updated <i class="fas fa-sort"></i></th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="url-rules-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="url-empty-state" style="display: none;">
                        <i class="fas fa-link"></i>
                        <h6>No URL Rules</h6>
                        <p>Add domain/pattern rules to control URL usage in messages.</p>
                    </div>
                </div>

                <div class="sec-table-card" style="margin-top: 1.5rem;">
                    <div class="sec-table-header" style="border-bottom: 1px solid #e9ecef; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                        <h6 style="margin: 0;"><i class="fas fa-user-shield me-2" style="color: #1e3a5f;"></i>Per-Account Domain Age Exceptions</h6>
                        <button class="sec-btn-primary" onclick="showAddDomainAgeExceptionModal()">
                            <i class="fas fa-plus"></i> Add Exception
                        </button>
                    </div>
                    <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;">
                        Accounts listed below are exempt from domain age checks. All exceptions are logged in the audit trail.
                    </p>
                    <div class="table-responsive">
                        <table class="sec-table" id="domain-age-exceptions-table">
                            <thead>
                                <tr>
                                    <th>Account ID <i class="fas fa-sort"></i></th>
                                    <th>Account Name <i class="fas fa-sort"></i></th>
                                    <th>Reason <i class="fas fa-sort"></i></th>
                                    <th>Added By <i class="fas fa-sort"></i></th>
                                    <th>Added On <i class="fas fa-sort"></i></th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="domain-age-exceptions-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="domain-exceptions-empty-state" style="display: none;">
                        <i class="fas fa-user-check"></i>
                        <h6>No Exceptions</h6>
                        <p>All accounts are subject to domain age checks when enabled.</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="normalisation-rules" role="tabpanel">
                <div class="tab-description">
                    <h6><i class="fas fa-globe me-2"></i>Normalisation Rules (Global)</h6>
                    <p>Single source of truth for character equivalence ("bastardisation") rules. These rules define how visually similar characters are treated as equivalent during security checks.</p>
                </div>

                <div class="alert alert-info d-flex align-items-start mb-3" style="background: #e8f4fd; border: 1px solid #1e3a5f; border-radius: 6px;">
                    <i class="fas fa-info-circle me-2 mt-1" style="color: #1e3a5f;"></i>
                    <div>
                        <strong>Consuming Engines:</strong> These normalisation rules are consumed by the enforcement engines below. Changes apply globally.
                        <div class="d-flex gap-2 mt-2">
                            <span class="badge" style="background: #d97706; color: white;"><i class="fas fa-id-badge me-1"></i>SenderID Matching</span>
                            <span class="badge" style="background: #2563eb; color: white;"><i class="fas fa-comment-alt me-1"></i>Content Matching</span>
                            <span class="badge" style="background: #7c3aed; color: white;" id="url-engine-badge"><i class="fas fa-link me-1"></i>URL Matching <small>(guarded)</small></span>
                        </div>
                    </div>
                </div>

                <div class="sec-stats">
                    <div class="sec-stat-card active">
                        <div class="sec-stat-value" id="norm-active-count">0</div>
                        <div class="sec-stat-label">Active Rules</div>
                    </div>
                    <div class="sec-stat-card blocked">
                        <div class="sec-stat-value" id="norm-disabled-count">0</div>
                        <div class="sec-stat-label">Disabled</div>
                    </div>
                    <div class="sec-stat-card pending">
                        <div class="sec-stat-value" id="norm-mappings-count">0</div>
                        <div class="sec-stat-label">Total Mappings</div>
                    </div>
                    <div class="sec-stat-card total">
                        <div class="sec-stat-value" id="norm-total-count">0</div>
                        <div class="sec-stat-label">Total Rules</div>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-filter-row">
                        <div class="sec-filter-group">
                            <label>Status</label>
                            <select id="norm-filter-status">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Rule Category</label>
                            <select id="norm-filter-category">
                                <option value="">All Categories</option>
                                <option value="substitution">Character Substitution</option>
                                <option value="homoglyph">Homoglyph Detection</option>
                                <option value="unicode">Unicode Normalisation</option>
                                <option value="case">Case Folding</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Scope</label>
                            <select id="norm-filter-scope">
                                <option value="">All Scopes</option>
                                <option value="senderid">SenderID Only</option>
                                <option value="content">Content Only</option>
                                <option value="url">URL Only</option>
                                <option value="all">All Engines</option>
                            </select>
                        </div>
                        <div class="sec-filter-actions">
                            <button class="sec-btn-primary" onclick="showAddNormRuleModal()">
                                <i class="fas fa-plus"></i> Add Rule
                            </button>
                            <button class="sec-btn-outline" onclick="resetNormFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>Character Equivalence Rules</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search rules or characters..." id="norm-search">
                        </div>
                    </div>
                    <table class="sec-table" id="norm-rules-table">
                        <thead>
                            <tr>
                                <th>Rule Name <i class="fas fa-sort"></i></th>
                                <th>Category <i class="fas fa-sort"></i></th>
                                <th>Character Mappings</th>
                                <th>Scope <i class="fas fa-sort"></i></th>
                                <th>Priority <i class="fas fa-sort"></i></th>
                                <th>Status <i class="fas fa-sort"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="norm-rules-body">
                        </tbody>
                    </table>
                    <div class="sec-empty-state" id="norm-empty-state" style="display: none;">
                        <i class="fas fa-globe"></i>
                        <h6>No Normalisation Rules</h6>
                        <p>Create character equivalence rules for consistent security matching.</p>
                    </div>
                </div>

                <div class="card mt-3" style="border: 1px solid #e9ecef; border-radius: 8px;">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; border-radius: 8px 8px 0 0;">
                        <h6 class="mb-0"><i class="fas fa-code me-2"></i>Quick Reference: Built-in Substitution Map</h6>
                    </div>
                    <div class="card-body" style="padding: 1rem;">
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">The following character equivalences are currently active across all engines:</p>
                        <div class="row" id="substitution-map-display">
                        </div>
                        <div class="mt-2 text-end">
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportSubstitutionMap()">
                                <i class="fas fa-download me-1"></i>Export Map
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="testNormalisationRule()">
                                <i class="fas fa-flask me-1"></i>Test Rule
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="quarantine-review" role="tabpanel">
                <div class="tab-description">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Quarantine & Review</h6>
                    <p>Review flagged messages, suspicious content, and items held for manual review before delivery.</p>
                </div>

                <div class="sec-stats">
                    <div class="sec-stat-card pending">
                        <div class="sec-stat-value" id="quarantine-pending-count">0</div>
                        <div class="sec-stat-label">Awaiting Review</div>
                    </div>
                    <div class="sec-stat-card active">
                        <div class="sec-stat-value" id="quarantine-released-count">0</div>
                        <div class="sec-stat-label">Released Today</div>
                    </div>
                    <div class="sec-stat-card blocked">
                        <div class="sec-stat-value" id="quarantine-blocked-count">0</div>
                        <div class="sec-stat-label">Permanently Blocked</div>
                    </div>
                    <div class="sec-stat-card total">
                        <div class="sec-stat-value" id="quarantine-total-count">0</div>
                        <div class="sec-stat-label">Total in Queue</div>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-filter-row">
                        <div class="sec-filter-group">
                            <label>Status</label>
                            <select id="quarantine-filter-status">
                                <option value="">All Statuses</option>
                                <option value="pending" selected>Pending</option>
                                <option value="released">Released</option>
                                <option value="blocked">Permanently Blocked</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Rule Triggered</label>
                            <select id="quarantine-filter-rule">
                                <option value="">All Rules</option>
                                <option value="senderid">SenderID Rule</option>
                                <option value="content">Content Rule</option>
                                <option value="url">URL Rule</option>
                                <option value="domain_age">Domain Age</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>URL Present</label>
                            <select id="quarantine-filter-url">
                                <option value="">All</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Account</label>
                            <select id="quarantine-filter-account">
                                <option value="">All Accounts</option>
                            </select>
                        </div>
                        <div class="sec-filter-actions">
                            <button class="sec-btn-primary" onclick="bulkReleaseQuarantine()">
                                <i class="fas fa-check"></i> Release Selected
                            </button>
                            <button class="sec-btn-outline text-danger" style="border-color: #dc3545;" onclick="bulkBlockQuarantine()">
                                <i class="fas fa-ban"></i> Block Selected
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>Quarantine Inbox</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search messages, accounts, SenderIDs..." id="quarantine-search">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="sec-table" id="quarantine-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="quarantine-select-all"></th>
                                    <th>Timestamp <i class="fas fa-sort"></i></th>
                                    <th>Account <i class="fas fa-sort"></i></th>
                                    <th>Sub-Account <i class="fas fa-sort"></i></th>
                                    <th>SenderID <i class="fas fa-sort"></i></th>
                                    <th>Message Snippet <i class="fas fa-sort"></i></th>
                                    <th>URL <i class="fas fa-sort"></i></th>
                                    <th>Rule Triggered <i class="fas fa-sort"></i></th>
                                    <th>Status <i class="fas fa-sort"></i></th>
                                    <th>Reviewer <i class="fas fa-sort"></i></th>
                                    <th>Decision At <i class="fas fa-sort"></i></th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="quarantine-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="quarantine-empty-state" style="display: none;">
                        <i class="fas fa-check-circle" style="color: #1e3a5f;"></i>
                        <h6>No Messages in Quarantine</h6>
                        <p>All messages have been reviewed. Check back later for new items.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="senderIdRuleModal" tabindex="-1" aria-labelledby="senderIdRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" id="senderIdRuleModalLabel" style="color: #1e3a5f; font-weight: 600;">
                    <i class="fas fa-id-badge me-2"></i><span id="senderid-modal-title">Add SenderID Rule</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="senderid-rule-id" value="">
                
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Rule Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="senderid-rule-name" placeholder="e.g., Block HSBC Impersonation" required>
                    <small class="text-muted">A descriptive name for this rule</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Base SenderID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="senderid-base-value" placeholder="e.g., HSBC" style="text-transform: uppercase;" required>
                    <small class="text-muted">The canonical SenderID to match (case-insensitive, variants auto-detected)</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Rule Type <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="senderid-rule-type" id="senderid-type-block" value="block" checked>
                            <label class="form-check-label" for="senderid-type-block">
                                <span class="badge bg-danger">Block</span>
                                <small class="d-block text-muted">Reject message outright</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="senderid-rule-type" id="senderid-type-flag" value="flag">
                            <label class="form-check-label" for="senderid-type-flag">
                                <span class="badge bg-warning text-dark">Flag</span>
                                <small class="d-block text-muted">Send to quarantine for review</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="senderid-category" required>
                        <option value="">Select a category...</option>
                        <option value="bank_impersonation">Bank Impersonation</option>
                        <option value="government">Government Impersonation</option>
                        <option value="lottery_prize">Lottery/Prize Scam</option>
                        <option value="brand_abuse">Brand Abuse</option>
                        <option value="premium_rate">Premium Rate Services</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="senderid-apply-normalisation" checked>
                        <label class="form-check-label" for="senderid-apply-normalisation" style="font-weight: 500; color: #1e3a5f;">
                            Apply Normalisation Rules
                        </label>
                    </div>
                    <small class="text-muted">When enabled, global normalisation rules will be applied before matching</small>
                </div>

                <div class="p-3 rounded" style="background: #f8f9fc; border: 1px solid #e9ecef;">
                    <h6 style="font-size: 0.8rem; font-weight: 600; color: #1e3a5f; margin-bottom: 0.5rem;">
                        <i class="fas fa-info-circle me-1"></i> Matching Behaviour
                    </h6>
                    <ul style="font-size: 0.75rem; color: #6c757d; margin: 0; padding-left: 1.25rem;">
                        <li>Case-insensitive matching (HSBC = hsbc = HsBc)</li>
                        <li>Character substitution variants detected (0→O, 1→I/L, 5→S)</li>
                        <li>Whitespace and special characters normalized</li>
                        <li id="normalisation-note">Global normalisation rules applied first</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8f9fc; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: #fff;" onclick="saveSenderIdRule()">
                    <i class="fas fa-save me-1"></i> <span id="senderid-save-btn-text">Save Rule</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="senderIdViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #1e3a5f; font-weight: 600;">
                    <i class="fas fa-eye me-2"></i>View SenderID Rule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="senderid-view-content">
            </div>
            <div class="modal-footer" style="background: #f8f9fc; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background: #fee2e2; border-bottom: 1px solid #fecaca;">
                <h5 class="modal-title" style="color: #991b1b; font-weight: 600;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirm-delete-message">Are you sure you want to delete this rule?</p>
                <p class="text-muted" style="font-size: 0.8rem;">This action cannot be undone.</p>
                <input type="hidden" id="delete-rule-id" value="">
                <input type="hidden" id="delete-rule-type" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteRule()">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contentRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white" id="content-rule-modal-title">
                    <i class="fas fa-comment-alt me-2"></i>Add Content Rule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="content-rule-form">
                    <input type="hidden" id="content-rule-id" value="">
                    
                    <div class="mb-3">
                        <label for="content-rule-name" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="content-rule-name" placeholder="e.g., Phishing Keywords" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content-match-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Match Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="content-match-type" onchange="updateContentMatchInputLabel()">
                            <option value="keyword">Keyword(s)</option>
                            <option value="regex">Regex Pattern</option>
                        </select>
                        <small class="text-muted">Keyword matching is case-insensitive. Regex allows advanced pattern matching.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content-match-value" class="form-label" style="font-weight: 600; font-size: 0.85rem;" id="content-match-value-label">Keywords (comma-separated) <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content-match-value" rows="3" placeholder="verify your account, click here, suspended" required></textarea>
                        <small class="text-muted" id="content-match-value-help">Enter keywords separated by commas. Matching is case-insensitive.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content-rule-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Rule Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="content-rule-type">
                            <option value="block">Block (Immediate Rejection)</option>
                            <option value="flag">Flag (Quarantine for Review)</option>
                        </select>
                        <small class="text-muted">Block immediately rejects the message. Flag sends it to the quarantine queue for manual review.</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="content-apply-normalisation" checked style="width: 2.5rem; height: 1.25rem;">
                            <label class="form-check-label" for="content-apply-normalisation" style="font-weight: 600; font-size: 0.85rem; margin-left: 0.5rem;">
                                Apply Normalisation
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">When enabled, message content is normalised (character substitution, case conversion) before matching.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveContentRule()">
                    <i class="fas fa-save me-1"></i> Save Rule
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="urlRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white" id="url-rule-modal-title">
                    <i class="fas fa-link me-2"></i>Add URL Rule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="url-rule-form">
                    <input type="hidden" id="url-rule-id" value="">
                    
                    <div class="mb-3">
                        <label for="url-match-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Match Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="url-match-type" onchange="updateUrlPatternLabel()">
                            <option value="exact">Exact Domain</option>
                            <option value="wildcard">Wildcard Pattern</option>
                            <option value="regex">Regex Pattern</option>
                        </select>
                        <small class="text-muted">Choose how the domain/URL pattern should be matched.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url-pattern" class="form-label" style="font-weight: 600; font-size: 0.85rem;" id="url-pattern-label">Domain <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="url-pattern" placeholder="example.com" required>
                        <small class="text-muted" id="url-pattern-help">Enter the exact domain to match (e.g., example.com)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url-rule-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Rule Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="url-rule-type">
                            <option value="block">Block (Immediate Rejection)</option>
                            <option value="flag">Flag (Quarantine for Review)</option>
                        </select>
                        <small class="text-muted">Block immediately rejects messages containing this URL. Flag sends them to quarantine.</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="url-apply-domain-age" checked style="width: 2.5rem; height: 1.25rem;">
                            <label class="form-check-label" for="url-apply-domain-age" style="font-weight: 600; font-size: 0.85rem; margin-left: 0.5rem;">
                                Apply Domain Age Check
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">When enabled, the global domain age rule will also apply to URLs matching this pattern.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveUrlRule()">
                    <i class="fas fa-save me-1"></i> Save Rule
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="domainAgeExceptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-shield me-2"></i>Add Domain Age Exception
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="exception-form">
                    <div class="mb-3">
                        <label for="exception-account-id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Account ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="exception-account-id" placeholder="ACC-XXXXX" required>
                        <small class="text-muted">Enter the account ID to exempt from domain age checks.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="exception-account-name" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="exception-account-name" placeholder="Company Name" required>
                        <small class="text-muted">Enter the account/company name for reference.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="exception-reason" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Reason for Exception <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="exception-reason" rows="3" placeholder="Explain why this account needs an exception..." required></textarea>
                        <small class="text-muted">This will be recorded in the audit trail.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveException()">
                    <i class="fas fa-save me-1"></i> Add Exception
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quarantineViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-shield-alt me-2"></i>Quarantine Review: <span id="qrn-view-id-header"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3" style="border: 1px solid #e9ecef;">
                            <div class="card-header py-2" style="background: #f8f9fa; font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-info-circle me-1" style="color: #1e3a5f;"></i> Message Details
                            </div>
                            <div class="card-body py-2">
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.8rem;">
                                    <tr><td style="font-weight: 600; width: 120px;">Quarantine ID:</td><td id="qrn-view-id"></td></tr>
                                    <tr><td style="font-weight: 600;">Timestamp:</td><td id="qrn-view-timestamp"></td></tr>
                                    <tr><td style="font-weight: 600;">Account:</td><td id="qrn-view-account"></td></tr>
                                    <tr><td style="font-weight: 600;">Sub-Account:</td><td id="qrn-view-subaccount"></td></tr>
                                    <tr><td style="font-weight: 600;">SenderID:</td><td><code id="qrn-view-senderid" style="background: #f8f9fa; padding: 0.15rem 0.35rem; border-radius: 3px;"></code></td></tr>
                                    <tr><td style="font-weight: 600;">Recipient:</td><td id="qrn-view-recipient"></td></tr>
                                    <tr><td style="font-weight: 600;">URL Present:</td><td id="qrn-view-hasurl"></td></tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card mb-3" style="border: 1px solid #e9ecef;">
                            <div class="card-header py-2" style="background: #f8f9fa; font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-exclamation-triangle me-1" style="color: #dc3545;"></i> Triggered Rules
                            </div>
                            <div class="card-body py-2" id="qrn-view-triggered-rules">
                            </div>
                        </div>
                        
                        <div class="card" style="border: 1px solid #e9ecef;">
                            <div class="card-header py-2" style="background: #f8f9fa; font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-magic me-1" style="color: #6b21a8;"></i> Normalised Values
                            </div>
                            <div class="card-body py-2" id="qrn-view-normalised">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-3" style="border: 1px solid #e9ecef;">
                            <div class="card-header py-2" style="background: #f8f9fa; font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-envelope me-1" style="color: #1e3a5f;"></i> Message Content
                                <span class="badge bg-warning text-dark float-end" style="font-size: 0.65rem;">PII GATED</span>
                            </div>
                            <div class="card-body py-2">
                                <div class="p-2 bg-light rounded" style="font-size: 0.85rem; border: 1px solid #dee2e6; min-height: 80px;">
                                    <span id="qrn-view-message"></span>
                                </div>
                                <div class="mt-2 text-muted" style="font-size: 0.7rem;">
                                    <i class="fas fa-lock me-1"></i> Phone numbers and sensitive data are masked for compliance.
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3" style="border: 1px solid #e9ecef;">
                            <div class="card-header py-2" style="background: #f8f9fa; font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-clipboard-check me-1" style="color: #1e3a5f;"></i> Review Status
                            </div>
                            <div class="card-body py-2">
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.8rem;">
                                    <tr><td style="font-weight: 600; width: 100px;">Status:</td><td id="qrn-view-status"></td></tr>
                                    <tr><td style="font-weight: 600;">Reviewer:</td><td id="qrn-view-reviewer"></td></tr>
                                    <tr><td style="font-weight: 600;">Decision At:</td><td id="qrn-view-decisionat"></td></tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card" style="border: 1px solid #e9ecef;">
                            <div class="card-header py-2" style="background: #f8f9fa; font-weight: 600; font-size: 0.85rem;">
                                <i class="fas fa-sticky-note me-1" style="color: #1e3a5f;"></i> Internal Notes
                                <span class="badge text-white float-end" style="font-size: 0.65rem; background: #1e3a5f;">ADMIN ONLY</span>
                            </div>
                            <div class="card-body py-2">
                                <div id="qrn-view-notes-list" style="max-height: 100px; overflow-y: auto; font-size: 0.8rem;"></div>
                                <div class="mt-2" id="qrn-add-note-section">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="qrn-new-note" placeholder="Add internal note...">
                                        <button class="btn btn-outline-secondary" type="button" onclick="addQuarantineNote()">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem; background: #f8f9fa;">
                <div id="qrn-view-actions" class="d-flex gap-2 flex-wrap">
                </div>
                <div class="ms-auto d-flex gap-2">
                    <div class="form-check form-switch" id="qrn-notify-customer-section" style="display: none;">
                        <input class="form-check-input" type="checkbox" id="qrn-notify-customer">
                        <label class="form-check-label" for="qrn-notify-customer" style="font-size: 0.8rem;">Notify Customer Admin</label>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feature Flags Modal -->
<div class="modal fade" id="featureFlagsModal" tabindex="-1" aria-labelledby="featureFlagsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: white;">
                <h5 class="modal-title" id="featureFlagsModalLabel">
                    <i class="fas fa-toggle-on me-2"></i> Feature Flags Management
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Admin Only:</strong> Feature flags control which enforcement engines are active. Changes take effect immediately and are audited.
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light" style="font-size: 0.85rem; font-weight: 600;">
                                <i class="fas fa-cogs me-2"></i> Cache Statistics
                            </div>
                            <div class="card-body p-3" id="cacheStatsPanel">
                                <div class="row g-2" style="font-size: 0.8rem;">
                                    <div class="col-6"><strong>Version:</strong> <span id="cache-version">-</span></div>
                                    <div class="col-6"><strong>Rules:</strong> <span id="cache-total-rules">-</span></div>
                                    <div class="col-6"><strong>Tenants:</strong> <span id="cache-tenant-count">-</span></div>
                                    <div class="col-6"><strong>Last Load:</strong> <span id="cache-last-loaded">-</span></div>
                                </div>
                                <button class="btn btn-outline-primary btn-sm mt-2 w-100" onclick="hotReloadRules()">
                                    <i class="fas fa-sync-alt me-1"></i> Hot Reload Rules
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light" style="font-size: 0.85rem; font-weight: 600;">
                                <i class="fas fa-shield-alt me-2"></i> Tenant Isolation
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2" style="font-size: 0.8rem;">
                                    <span><i class="fas fa-lock text-success me-1"></i> Isolation Enforced</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.75rem;">
                                    Rules are tenant-scoped. Cross-tenant reads are blocked. Global rules apply to all tenants.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6 class="mb-3" style="font-size: 0.9rem; font-weight: 600; color: #1e3a5f;">
                    <i class="fas fa-flag me-2"></i> Engine Feature Flags
                </h6>
                
                <div class="table-responsive">
                    <table class="table table-sm" style="font-size: 0.85rem;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 0.5rem;">Flag</th>
                                <th style="padding: 0.5rem;">Description</th>
                                <th style="padding: 0.5rem; text-align: center;">Status</th>
                                <th style="padding: 0.5rem; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="featureFlagsTableBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-warning mt-3 mb-0" style="font-size: 0.8rem;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Caution:</strong> Disabling engines will skip their checks. Messages that would normally be blocked may be allowed through.
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto" style="font-size: 0.75rem;">
                    <i class="fas fa-history me-1"></i> All changes are audit logged
                </span>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Access Enforcement Script -->
<script>
(function() {
    var AdminAccessControl = {
        currentAdmin: {
            id: 'admin-001',
            email: 'admin@quicksms.co.uk',
            role: 'super_admin',
            isAdmin: true
        },
        
        accessRules: {
            internalRoutingOnly: { enforced: true, description: 'Admin routes are internal only' },
            noSharedRoutes: { enforced: true, description: 'No shared routes with customer portal' },
            noDeepLinksFromCustomer: { enforced: true, description: 'Customer portal cannot deep-link to admin' },
            mandatoryMfa: { enforced: true, description: 'MFA required for admin access' },
            adminUsersOnly: { enforced: true, description: 'Only admin users can access' }
        },
        
        validateAccess: function() {
            if (!this.currentAdmin || !this.currentAdmin.isAdmin) {
                console.error('[AdminAccessControl] Access denied - not an admin user');
                window.location.href = '/login';
                return false;
            }
            
            var referrer = document.referrer;
            if (referrer && referrer.includes('/customer/')) {
                console.error('[AdminAccessControl] Access denied - referrer from customer portal');
                this.logAccessViolation('CUSTOMER_PORTAL_REFERRER', referrer);
                return false;
            }
            
            console.log('[AdminControlPlane] Initialized for:', this.currentAdmin.email);
            console.log('[AdminControlPlane] Access Rules:', this.accessRules);
            console.log('[AdminControlPlane] Global Rules:', {
                singleSourceOfTruth: { enforced: true },
                filtering: { enforced: true },
                audit: { enforced: true },
                piiProtection: { enforced: true }
            });
            
            this.logSessionStart();
            return true;
        },
        
        logSessionStart: function() {
            var logEntry = {
                timestamp: new Date().toISOString(),
                eventType: 'ADMIN_SESSION_START',
                adminEmail: this.currentAdmin.email,
                adminRole: this.currentAdmin.role,
                path: window.location.pathname,
                referrer: document.referrer || 'direct',
                userAgent: navigator.userAgent,
                accessRulesEnforced: Object.keys(this.accessRules)
            };
            console.log('[AdminControlPlane][ACCESS]', JSON.stringify(logEntry));
        },
        
        logAccessViolation: function(type, details) {
            console.error('[AdminControlPlane][VIOLATION]', {
                timestamp: new Date().toISOString(),
                type: type,
                details: details,
                path: window.location.pathname
            });
        }
    };
    
    AdminAccessControl.validateAccess();
    window.AdminAccessControl = AdminAccessControl;
})();

function loadFeatureFlagsModal() {
    if (typeof MessageEnforcementService === 'undefined') return;
    
    var flags = MessageEnforcementService.getFeatureFlags();
    var cacheStats = MessageEnforcementService.getCacheStats();
    
    document.getElementById('cache-version').textContent = cacheStats.version || '-';
    document.getElementById('cache-total-rules').textContent = cacheStats.totalRules || '-';
    document.getElementById('cache-tenant-count').textContent = cacheStats.tenantCount || '-';
    document.getElementById('cache-last-loaded').textContent = cacheStats.lastLoaded ? 
        new Date(cacheStats.lastLoaded).toLocaleTimeString() : '-';
    
    var flagDescriptions = {
        normalisation_enabled: { name: 'Normalisation Engine', desc: 'Apply text normalisation before matching' },
        senderid_controls_enabled: { name: 'SenderID Controls', desc: 'Block/flag based on sender ID patterns' },
        content_controls_enabled: { name: 'Content Controls', desc: 'Block/flag based on message content' },
        url_controls_enabled: { name: 'URL Controls', desc: 'Block/flag based on URLs in messages' },
        quarantine_enabled: { name: 'Quarantine', desc: 'Send flagged messages to quarantine queue' },
        anti_spam_enabled: { name: 'Anti-Spam', desc: 'Prevent duplicate messages to same recipient' },
        domain_age_check_enabled: { name: 'Domain Age Check', desc: 'Flag URLs with newly registered domains' }
    };
    
    var tbody = document.getElementById('featureFlagsTableBody');
    tbody.innerHTML = '';
    
    Object.keys(flags).forEach(function(flagKey) {
        var flagInfo = flagDescriptions[flagKey] || { name: flagKey, desc: '' };
        var isEnabled = flags[flagKey];
        
        var row = document.createElement('tr');
        row.innerHTML = 
            '<td><code style="font-size: 0.75rem; background: #f1f3f5; padding: 0.2rem 0.4rem; border-radius: 3px;">' + flagKey + '</code><br><small class="text-muted">' + flagInfo.name + '</small></td>' +
            '<td style="color: #666;">' + flagInfo.desc + '</td>' +
            '<td style="text-align: center;">' +
                '<span class="badge ' + (isEnabled ? 'bg-success' : 'bg-secondary') + '">' + (isEnabled ? 'Enabled' : 'Disabled') + '</span>' +
            '</td>' +
            '<td style="text-align: center;">' +
                '<div class="form-check form-switch d-inline-block">' +
                    '<input class="form-check-input feature-flag-toggle" type="checkbox" data-flag="' + flagKey + '" ' + (isEnabled ? 'checked' : '') + ' onchange="toggleFeatureFlag(\'' + flagKey + '\', this.checked)">' +
                '</div>' +
            '</td>';
        tbody.appendChild(row);
    });
}

function toggleFeatureFlag(flagKey, enabled) {
    if (typeof MessageEnforcementService === 'undefined') return;
    
    var result = MessageEnforcementService.setFeatureFlag(flagKey, enabled, {
        isAdmin: true,
        adminId: 'admin-001',
        adminEmail: 'admin@quicksms.co.uk'
    });
    
    if (result.success) {
        logAuditEvent('FEATURE_FLAG_TOGGLED', {
            flag: flagKey,
            newValue: enabled,
            previousValue: !enabled
        });
        
        loadFeatureFlagsModal();
        
        var toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        toast.innerHTML = '<i class="fas fa-check-circle me-2"></i><strong>' + flagKey + '</strong> ' + (enabled ? 'enabled' : 'disabled') + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    } else {
        console.error('[FeatureFlags] Toggle failed:', result.error);
    }
}

function hotReloadRules() {
    if (typeof MessageEnforcementService === 'undefined') return;
    
    var result = MessageEnforcementService.hotReloadRules();
    
    if (result.success) {
        logAuditEvent('RULES_HOT_RELOADED', { newVersion: result.version });
        loadFeatureFlagsModal();
        
        var toast = document.createElement('div');
        toast.className = 'alert alert-info alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        toast.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Rules reloaded (v' + result.version + ')<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }
}

document.getElementById('featureFlagsModal').addEventListener('show.bs.modal', function() {
    loadFeatureFlagsModal();
});

console.log('[SecurityComplianceControls] Initialized');
</script>
@endsection

@push('scripts')
@include('shared.services.message-enforcement-service')
<script>
var currentAdmin = {
    id: 'admin-001',
    email: 'admin@quicksms.co.uk',
    role: 'super_admin'
};

function showEnforcementErrorBanner(result, isAdmin) {
    if (!result || result.decision === 'ALLOW') return;
    
    var container = document.getElementById('enforcement-error-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'enforcement-error-container';
        container.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(container);
    }
    
    var alertClass = result.decision === 'BLOCK' ? 'alert-danger' : 'alert-warning';
    var iconClass = result.decision === 'BLOCK' ? 'fa-ban' : 'fa-exclamation-triangle';
    
    var explanation = result.explainability || {};
    var adminDetail = explanation.adminDetail || {};
    var customerSummary = explanation.customerSummary || {};
    
    var bannerHtml = '';
    if (isAdmin) {
        bannerHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert" style="border-left: 4px solid; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">' +
            '<div class="d-flex align-items-start">' +
                '<i class="fas ' + iconClass + ' me-3 mt-1" style="font-size: 1.2rem;"></i>' +
                '<div class="flex-grow-1">' +
                    '<h6 class="alert-heading mb-1" style="font-size: 0.95rem;">' + (adminDetail.summary || 'Message ' + result.decision.toLowerCase()) + '</h6>' +
                    '<p class="mb-2" style="font-size: 0.85rem;">' + (adminDetail.fullReason || result.reason) + '</p>' +
                    '<div class="d-flex flex-wrap gap-2" style="font-size: 0.75rem;">' +
                        '<span class="badge bg-light text-dark"><i class="fas fa-cog me-1"></i>' + (adminDetail.engine || 'Policy') + '</span>' +
                        (adminDetail.ruleId ? '<span class="badge bg-light text-dark"><i class="fas fa-hashtag me-1"></i>' + adminDetail.ruleId + '</span>' : '') +
                        (adminDetail.ruleName ? '<span class="badge bg-light text-dark"><i class="fas fa-tag me-1"></i>' + adminDetail.ruleName + '</span>' : '') +
                        (adminDetail.matchedToken ? '<span class="badge bg-light text-dark" title="Matched: ' + adminDetail.matchedToken + '"><i class="fas fa-search me-1"></i>Token matched</span>' : '') +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>';
    } else {
        bannerHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert" style="border-left: 4px solid; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">' +
            '<div class="d-flex align-items-start">' +
                '<i class="fas ' + iconClass + ' me-3 mt-1" style="font-size: 1.2rem;"></i>' +
                '<div class="flex-grow-1">' +
                    '<h6 class="alert-heading mb-1" style="font-size: 0.95rem;">' + (customerSummary.headline || 'Message could not be sent') + '</h6>' +
                    '<p class="mb-2" style="font-size: 0.85rem;">' + (customerSummary.reason || 'Your message was flagged by our security policy.') + '</p>' +
                    '<p class="mb-0" style="font-size: 0.8rem; opacity: 0.9;">' + (customerSummary.actionRequired || '') + '</p>' +
                    (customerSummary.supportCode ? '<small class="text-muted d-block mt-2">Reference: ' + customerSummary.supportCode + '</small>' : '') +
                '</div>' +
            '</div>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>';
    }
    
    container.innerHTML = bannerHtml;
    
    setTimeout(function() {
        var alert = container.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(function() { container.innerHTML = ''; }, 150);
        }
    }, 10000);
}

window.showEnforcementErrorBanner = showEnforcementErrorBanner;

var auditConfig = {
    enableCustomerAudit: true,
    customerAuditEvents: [
        'MESSAGE_BLOCKED_BY_POLICY',
        'MESSAGE_QUARANTINED_BY_POLICY',
        'MESSAGE_RELEASED_FROM_QUARANTINE'
    ],
    internalOnlyEvents: [
        'SENDERID_RULE_CREATED', 'SENDERID_RULE_UPDATED', 'SENDERID_RULE_DELETED', 'SENDERID_RULE_STATUS_CHANGED',
        'CONTENT_RULE_CREATED', 'CONTENT_RULE_UPDATED', 'CONTENT_RULE_DELETED', 'CONTENT_RULE_STATUS_CHANGED',
        'URL_RULE_CREATED', 'URL_RULE_UPDATED', 'URL_RULE_DELETED', 'URL_RULE_STATUS_CHANGED',
        'NORMALISATION_RULE_CHANGED',
        'DOMAIN_AGE_SETTINGS_UPDATED', 'DOMAIN_AGE_EXCEPTION_ADDED', 'DOMAIN_AGE_EXCEPTION_REMOVED',
        'QUARANTINE_NOTE_ADDED', 'QUARANTINE_EXCEPTION_STARTED', 'QUARANTINE_RULE_CREATE_STARTED',
        'ANTISPAM_REPEAT_CONTENT_TOGGLED', 'ANTISPAM_WINDOW_UPDATED',
        'OVERRIDE_APPLIED'
    ],
    retentionYears: 7
};

function getClientIP() {
    return '192.168.1.' + Math.floor(Math.random() * 255);
}

function logAuditEvent(eventType, details) {
    var auditEntry = {
        eventId: 'AUD-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
        eventType: eventType,
        timestamp: new Date().toISOString(),
        timestampUtc: new Date().toUTCString(),
        actor: {
            adminId: currentAdmin.id,
            adminEmail: currentAdmin.email,
            adminRole: currentAdmin.role
        },
        sourceIp: getClientIP(),
        affectedEntities: details.affectedEntities || [],
        entityId: details.ruleId || details.messageId || details.accountId || null,
        entityType: details.entityType || inferEntityType(eventType),
        action: eventType,
        before: details.before || details.beforeStatus || null,
        after: details.after || details.afterStatus || null,
        details: sanitizeAuditDetails(details),
        module: 'security_compliance_controls',
        retentionExpiry: new Date(Date.now() + (auditConfig.retentionYears * 365 * 24 * 60 * 60 * 1000)).toISOString()
    };
    
    console.log('[SecurityComplianceAudit][INTERNAL]', JSON.stringify(auditEntry));
    
    if (auditConfig.enableCustomerAudit && shouldLogToCustomerAudit(eventType, details)) {
        var customerAuditEntry = buildCustomerAuditEntry(eventType, details, auditEntry);
        console.log('[SecurityComplianceAudit][CUSTOMER]', JSON.stringify(customerAuditEntry));
    }
    
    return auditEntry;
}

function inferEntityType(eventType) {
    if (eventType.indexOf('SENDERID') !== -1) return 'senderid_rule';
    if (eventType.indexOf('CONTENT') !== -1) return 'content_rule';
    if (eventType.indexOf('URL') !== -1) return 'url_rule';
    if (eventType.indexOf('DOMAIN_AGE') !== -1) return 'domain_age_setting';
    if (eventType.indexOf('QUARANTINE') !== -1) return 'quarantine_message';
    if (eventType.indexOf('ANTISPAM') !== -1) return 'antispam_setting';
    if (eventType.indexOf('NORMALISATION') !== -1) return 'normalisation_rule';
    return 'unknown';
}

function sanitizeAuditDetails(details) {
    var sanitized = JSON.parse(JSON.stringify(details));
    var sensitiveFields = ['password', 'token', 'secret', 'apiKey', 'recipientFull'];
    sensitiveFields.forEach(function(field) {
        if (sanitized[field]) {
            sanitized[field] = '[REDACTED]';
        }
    });
    return sanitized;
}

function shouldLogToCustomerAudit(eventType, details) {
    if (auditConfig.customerAuditEvents.indexOf(eventType) !== -1) {
        return true;
    }
    if (eventType === 'QUARANTINE_MESSAGE_RELEASED' || eventType === 'QUARANTINE_MESSAGE_BLOCKED') {
        return true;
    }
    return false;
}

function buildCustomerAuditEntry(eventType, details, internalEntry) {
    var customerEvent = {
        eventId: internalEntry.eventId,
        timestamp: internalEntry.timestamp,
        accountId: details.accountId || null,
        accountName: details.accountName || null,
        eventType: mapToCustomerEventType(eventType),
        summary: buildCustomerSummary(eventType, details),
        affectedMessageId: details.messageId || null
    };
    return customerEvent;
}

function mapToCustomerEventType(internalEventType) {
    var mapping = {
        'QUARANTINE_MESSAGE_RELEASED': 'MESSAGE_RELEASED_FROM_QUARANTINE',
        'QUARANTINE_MESSAGE_BLOCKED': 'MESSAGE_BLOCKED_BY_POLICY',
        'MESSAGE_QUARANTINED': 'MESSAGE_QUARANTINED_BY_POLICY'
    };
    return mapping[internalEventType] || 'POLICY_ACTION';
}

function buildCustomerSummary(eventType, details) {
    if (eventType === 'QUARANTINE_MESSAGE_RELEASED') {
        return 'A message to ' + (details.recipient || 'recipient') + ' was reviewed and released for delivery.';
    }
    if (eventType === 'QUARANTINE_MESSAGE_BLOCKED') {
        return 'A message to ' + (details.recipient || 'recipient') + ' was reviewed and blocked due to policy violation.';
    }
    return 'A policy action was applied to your message.';
}

var SecurityComplianceControlsService = (function() {
    var mockData = {
        senderIdRules: [],
        contentRules: [],
        urlRules: [],
        normalisationRules: [],
        quarantinedMessages: []
    };

    function initialize() {
        loadMockData();
        renderAllTabs();
        setupEventListeners();
        console.log('[SecurityComplianceControls] Initialized');
    }

    function loadMockData() {
        mockData.senderIdRules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];

        mockData.contentRules = [
            { id: 'CNT-001', name: 'Phishing Keywords', matchType: 'keyword', matchValue: 'verify your account, click here immediately, suspended account, urgent action', ruleType: 'block', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'CNT-002', name: 'Adult Content Filter', matchType: 'regex', matchValue: '(18\\+|xxx|adult\\s?content)', ruleType: 'flag', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '12-01-2026 14:00', updatedAt: '20-01-2026 11:45' },
            { id: 'CNT-003', name: 'Gambling Promotion', matchType: 'keyword', matchValue: 'bet now, free spins, casino bonus, jackpot winner', ruleType: 'flag', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'CNT-004', name: 'Cryptocurrency Scam', matchType: 'regex', matchValue: '(bitcoin|crypto|eth)\\s*(giveaway|airdrop|double)', ruleType: 'block', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '08-01-2026 10:20', updatedAt: '08-01-2026 10:20' },
            { id: 'CNT-005', name: 'Premium Rate Numbers', matchType: 'regex', matchValue: '(call|text|dial)\\s*(09\\d{8,}|118\\d+)', ruleType: 'flag', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];

        mockData.urlRules = [
            { id: 'URL-001', pattern: 'bit.ly', matchType: 'exact', ruleType: 'flag', applyDomainAge: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'URL-002', pattern: 'malicious-site.com', matchType: 'exact', ruleType: 'block', applyDomainAge: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 14:00', updatedAt: '20-01-2026 11:45' },
            { id: 'URL-003', pattern: '*.tinyurl.com', matchType: 'wildcard', ruleType: 'flag', applyDomainAge: false, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '08-01-2026 10:20', updatedAt: '25-01-2026 16:30' },
            { id: 'URL-004', pattern: 'phish\\d+\\.com', matchType: 'regex', ruleType: 'block', applyDomainAge: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 08:45', updatedAt: '05-01-2026 08:45' },
            { id: 'URL-005', pattern: 'suspicious-domain.net', matchType: 'exact', ruleType: 'block', applyDomainAge: true, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '01-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
        
        mockData.domainAgeSettings = {
            enabled: false,
            minAgeHours: 72,
            action: 'block'
        };
        
        mockData.domainAgeExceptions = [
            { id: 'EXC-001', accountId: 'ACC-10045', accountName: 'TechStart Ltd', reason: 'Approved marketing partner - verified shortlinks', addedBy: 'admin@quicksms.co.uk', addedAt: '15-01-2026 10:30' },
            { id: 'EXC-002', accountId: 'ACC-10089', accountName: 'HealthFirst UK', reason: 'Enterprise customer - internal domain rotation', addedBy: 'compliance@quicksms.co.uk', addedAt: '20-01-2026 14:15' }
        ];

        mockData.normalisationRules = [
            { 
                id: 1, 
                name: 'Numeric-Alpha Substitution', 
                category: 'substitution',
                description: 'Maps numeric digits to visually similar letters',
                mappings: [
                    { base: '0', equivalents: ['O', 'o', 'Ο', 'ο'] },
                    { base: '1', equivalents: ['I', 'i', 'l', 'L', '|', 'Ι', 'ι'] },
                    { base: '3', equivalents: ['E', 'e', 'Ε', 'ε'] },
                    { base: '4', equivalents: ['A', 'a', 'Α', 'α'] },
                    { base: '5', equivalents: ['S', 's', 'Ѕ', 'ѕ'] },
                    { base: '8', equivalents: ['B', 'b', 'Β', 'β'] }
                ],
                scope: 'all',
                scopeLabel: 'All Engines',
                priority: 1, 
                status: 'active',
                createdAt: '15-01-2026',
                updatedAt: '28-01-2026',
                createdBy: 'admin@quicksms.co.uk'
            },
            { 
                id: 2, 
                name: 'Greek Homoglyphs', 
                category: 'homoglyph',
                description: 'Detects Greek letters that appear identical to Latin characters',
                mappings: [
                    { base: 'A', equivalents: ['Α', 'α', 'Ά'] },
                    { base: 'B', equivalents: ['Β', 'β', 'ϐ'] },
                    { base: 'E', equivalents: ['Ε', 'ε', 'Έ'] },
                    { base: 'H', equivalents: ['Η', 'η', 'Ή'] },
                    { base: 'I', equivalents: ['Ι', 'ι', 'Ί'] },
                    { base: 'K', equivalents: ['Κ', 'κ'] },
                    { base: 'M', equivalents: ['Μ', 'μ'] },
                    { base: 'N', equivalents: ['Ν', 'ν'] },
                    { base: 'O', equivalents: ['Ο', 'ο', 'Ό'] },
                    { base: 'P', equivalents: ['Ρ', 'ρ'] },
                    { base: 'T', equivalents: ['Τ', 'τ'] },
                    { base: 'X', equivalents: ['Χ', 'χ'] },
                    { base: 'Y', equivalents: ['Υ', 'υ', 'Ύ'] },
                    { base: 'Z', equivalents: ['Ζ', 'ζ'] }
                ],
                scope: 'senderid',
                scopeLabel: 'SenderID Only',
                priority: 2, 
                status: 'active',
                createdAt: '10-01-2026',
                updatedAt: '25-01-2026',
                createdBy: 'admin@quicksms.co.uk'
            },
            { 
                id: 3, 
                name: 'Cyrillic Homoglyphs', 
                category: 'homoglyph',
                description: 'Detects Cyrillic letters that appear identical to Latin characters',
                mappings: [
                    { base: 'A', equivalents: ['А', 'а'] },
                    { base: 'B', equivalents: ['В', 'в'] },
                    { base: 'C', equivalents: ['С', 'с'] },
                    { base: 'E', equivalents: ['Е', 'е', 'Ё', 'ё'] },
                    { base: 'H', equivalents: ['Н', 'н'] },
                    { base: 'K', equivalents: ['К', 'к'] },
                    { base: 'M', equivalents: ['М', 'м'] },
                    { base: 'O', equivalents: ['О', 'о'] },
                    { base: 'P', equivalents: ['Р', 'р'] },
                    { base: 'T', equivalents: ['Т', 'т'] },
                    { base: 'X', equivalents: ['Х', 'х'] },
                    { base: 'Y', equivalents: ['У', 'у'] }
                ],
                scope: 'all',
                scopeLabel: 'All Engines',
                priority: 3, 
                status: 'active',
                createdAt: '10-01-2026',
                updatedAt: '25-01-2026',
                createdBy: 'admin@quicksms.co.uk'
            },
            { 
                id: 4, 
                name: 'Case Folding (Standard)', 
                category: 'case',
                description: 'Normalises upper and lower case for comparison',
                mappings: [
                    { base: 'A-Z', equivalents: ['a-z'] }
                ],
                scope: 'all',
                scopeLabel: 'All Engines',
                priority: 0, 
                status: 'active',
                createdAt: '01-01-2026',
                updatedAt: '01-01-2026',
                createdBy: 'system'
            },
            { 
                id: 5, 
                name: 'Unicode Normalisation (NFKC)', 
                category: 'unicode',
                description: 'Applies NFKC normalisation to decompose and recompose characters',
                mappings: [
                    { base: 'fi', equivalents: ['ﬁ'] },
                    { base: 'fl', equivalents: ['ﬂ'] },
                    { base: 'ff', equivalents: ['ﬀ'] },
                    { base: '1/2', equivalents: ['½'] },
                    { base: '(c)', equivalents: ['©'] },
                    { base: '(r)', equivalents: ['®'] },
                    { base: 'TM', equivalents: ['™'] }
                ],
                scope: 'content',
                scopeLabel: 'Content Only',
                priority: 4, 
                status: 'active',
                createdAt: '05-01-2026',
                updatedAt: '20-01-2026',
                createdBy: 'admin@quicksms.co.uk'
            },
            { 
                id: 6, 
                name: 'URL Character Substitution', 
                category: 'substitution',
                description: 'Detects character substitutions commonly used in phishing URLs',
                mappings: [
                    { base: 'l', equivalents: ['1', 'I', '|'] },
                    { base: 'o', equivalents: ['0', 'O'] },
                    { base: 'a', equivalents: ['@', '4'] },
                    { base: 's', equivalents: ['$', '5'] },
                    { base: 'e', equivalents: ['3'] },
                    { base: 'g', equivalents: ['9'] },
                    { base: 't', equivalents: ['7'] }
                ],
                scope: 'url',
                scopeLabel: 'URL Only',
                priority: 5, 
                status: 'disabled',
                createdAt: '12-01-2026',
                updatedAt: '28-01-2026',
                createdBy: 'admin@quicksms.co.uk'
            }
        ];

        mockData.quarantinedMessages = [
            { 
                id: 'QRN-001', timestamp: '29-01-2026 10:15:32', accountId: 'ACC-10045', accountName: 'TechStart Ltd', 
                subAccountId: 'SUB-001', subAccountName: 'Marketing Dept', senderId: 'TECHPROMO', 
                recipient: '+44****7890', recipientFull: '+447700900890',
                messageSnippet: 'Congratulations! You have won a prize...', 
                fullMessage: 'Congratulations! You have won a prize of £1000! Click here to claim: http://win-prize-now.xyz/claim?ref=123',
                hasUrl: true, extractedUrls: ['http://win-prize-now.xyz/claim?ref=123'],
                ruleTriggered: 'content', ruleName: 'Lottery Keywords', ruleId: 'CONT-002',
                triggeredRules: [
                    { engine: 'MessageContentEngine', ruleId: 'CONT-002', ruleName: 'Lottery Keywords', matchType: 'Keyword', matchedValue: 'won a prize' }
                ],
                normalisedValues: { senderId: 'TECHPROMO', senderIdNormalised: 'techpromo', messageNormalised: 'congratulations! you have won a prize of £1000! click here to claim: http://win-prize-now.xyz/claim?ref=123' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [], idempotencyKey: 'idem-001-abc', releaseAttempts: 0
            },
            { 
                id: 'QRN-002', timestamp: '29-01-2026 09:45:18', accountId: 'ACC-10089', accountName: 'HealthFirst UK', 
                subAccountId: null, subAccountName: null, senderId: 'HEALTH', 
                recipient: '+44****1234', recipientFull: '+447700901234',
                messageSnippet: 'Click here to verify your account immediately...', 
                fullMessage: 'URGENT: Click here to verify your account immediately or it will be suspended: http://verify-now.tk/urgent',
                hasUrl: true, extractedUrls: ['http://verify-now.tk/urgent'],
                ruleTriggered: 'url', ruleName: 'Suspicious URL Pattern', ruleId: 'URL-003',
                triggeredRules: [
                    { engine: 'UrlEnforcementEngine', ruleId: 'URL-003', ruleName: 'Suspicious URL Pattern', matchType: 'Wildcard', matchedValue: '*.tk/*' }
                ],
                normalisedValues: { senderId: 'HEALTH', senderIdNormalised: 'health', messageNormalised: 'urgent: click here to verify your account immediately or it will be suspended: http://verify-now.tk/urgent' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [{ author: 'admin@quicksms.co.uk', timestamp: '29-01-2026 10:00:00', text: 'Appears to be phishing attempt - investigate account' }],
                idempotencyKey: 'idem-002-def', releaseAttempts: 0
            },
            { 
                id: 'QRN-003', timestamp: '29-01-2026 08:30:45', accountId: 'ACC-10112', accountName: 'E-Commerce Hub', 
                subAccountId: 'SUB-005', subAccountName: 'Promotions', senderId: 'ECOMDEALS', 
                recipient: '+44****5678', recipientFull: '+447700905678',
                messageSnippet: 'Limited time offer! Free casino bonus...', 
                fullMessage: 'Limited time offer! Free casino bonus when you sign up. Visit our site for more gaming deals!',
                hasUrl: false, extractedUrls: [],
                ruleTriggered: 'content', ruleName: 'Gambling Keywords', ruleId: 'CONT-003',
                triggeredRules: [
                    { engine: 'MessageContentEngine', ruleId: 'CONT-003', ruleName: 'Gambling Keywords', matchType: 'Keyword', matchedValue: 'casino bonus' }
                ],
                normalisedValues: { senderId: 'ECOMDEALS', senderIdNormalised: 'ecomdeals', messageNormalised: 'limited time offer! free casino bonus when you sign up. visit our site for more gaming deals!' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [], idempotencyKey: 'idem-003-ghi', releaseAttempts: 0
            },
            { 
                id: 'QRN-004', timestamp: '29-01-2026 07:22:11', accountId: 'ACC-10045', accountName: 'TechStart Ltd', 
                subAccountId: null, subAccountName: null, senderId: 'HMRC', 
                recipient: '+44****9999', recipientFull: '+447700909999',
                messageSnippet: 'Your tax refund is ready. Click to claim...', 
                fullMessage: 'HMRC: Your tax refund of £450.32 is ready. Click to claim within 24 hours: http://hmrc-refund.net/claim',
                hasUrl: true, extractedUrls: ['http://hmrc-refund.net/claim'],
                ruleTriggered: 'senderid', ruleName: 'Block HMRC Impersonation', ruleId: 'SID-001',
                triggeredRules: [
                    { engine: 'SenderIdEnforcementEngine', ruleId: 'SID-001', ruleName: 'Block HMRC Impersonation', matchType: 'Exact', matchedValue: 'HMRC' },
                    { engine: 'UrlEnforcementEngine', ruleId: 'URL-005', ruleName: 'Suspicious Domain', matchType: 'Exact', matchedValue: 'hmrc-refund.net' }
                ],
                normalisedValues: { senderId: 'HMRC', senderIdNormalised: 'hmrc', messageNormalised: 'hmrc: your tax refund of £450.32 is ready. click to claim within 24 hours: http://hmrc-refund.net/claim' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [], idempotencyKey: 'idem-004-jkl', releaseAttempts: 0
            },
            { 
                id: 'QRN-005', timestamp: '28-01-2026 16:45:00', accountId: 'ACC-10200', accountName: 'FastLoans Ltd', 
                subAccountId: null, subAccountName: null, senderId: 'LOANS', 
                recipient: '+44****4321', recipientFull: '+447700904321',
                messageSnippet: 'Instant approval! Get cash now at bit.ly/xxx', 
                fullMessage: 'Instant approval! Get cash now. No credit check needed. Apply at bit.ly/fastcash-now',
                hasUrl: true, extractedUrls: ['bit.ly/fastcash-now'],
                ruleTriggered: 'domain_age', ruleName: 'Domain Age Check', ruleId: 'DAGE-001',
                triggeredRules: [
                    { engine: 'DomainAgeEngine', ruleId: 'DAGE-001', ruleName: 'Domain Age Check', matchType: 'Age', matchedValue: 'Domain registered 2 hours ago (threshold: 72 hours)' }
                ],
                normalisedValues: { senderId: 'LOANS', senderIdNormalised: 'loans', messageNormalised: 'instant approval! get cash now. no credit check needed. apply at bit.ly/fastcash-now' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [], idempotencyKey: 'idem-005-mno', releaseAttempts: 0
            },
            { 
                id: 'QRN-006', timestamp: '28-01-2026 14:30:22', accountId: 'ACC-10089', accountName: 'HealthFirst UK', 
                subAccountId: 'SUB-003', subAccountName: 'Patient Comms', senderId: 'NHSALERT', 
                recipient: '+44****8765', recipientFull: '+447700908765',
                messageSnippet: 'Important health notice regarding...', 
                fullMessage: 'Important health notice regarding your upcoming appointment on 30th January. Please confirm attendance.',
                hasUrl: false, extractedUrls: [],
                ruleTriggered: 'senderid', ruleName: 'Block NHS Impersonation', ruleId: 'SID-002',
                triggeredRules: [
                    { engine: 'SenderIdEnforcementEngine', ruleId: 'SID-002', ruleName: 'Block NHS Impersonation', matchType: 'Fuzzy', matchedValue: 'NHSALERT (variant of NHS)' }
                ],
                normalisedValues: { senderId: 'NHSALERT', senderIdNormalised: 'nhsalert', messageNormalised: 'important health notice regarding your upcoming appointment on 30th january. please confirm attendance.' },
                status: 'released', reviewer: 'admin@quicksms.co.uk', decisionAt: '28-01-2026 15:10:00',
                notes: [{ author: 'admin@quicksms.co.uk', timestamp: '28-01-2026 15:08:00', text: 'Verified with HealthFirst - legitimate NHS partnership communication' }],
                idempotencyKey: 'idem-006-pqr', releaseAttempts: 1
            },
            { 
                id: 'QRN-007', timestamp: '28-01-2026 11:15:33', accountId: 'ACC-10150', accountName: 'CryptoTraders', 
                subAccountId: null, subAccountName: null, senderId: 'CRYPTO', 
                recipient: '+44****2222', recipientFull: '+447700902222',
                messageSnippet: 'Bitcoin giveaway! Double your crypto...', 
                fullMessage: 'Bitcoin giveaway! Double your crypto instantly. Send 0.1 BTC to wallet xyz and receive 0.2 BTC back!',
                hasUrl: true, extractedUrls: [],
                ruleTriggered: 'content', ruleName: 'Cryptocurrency Scam', ruleId: 'CONT-005',
                triggeredRules: [
                    { engine: 'MessageContentEngine', ruleId: 'CONT-005', ruleName: 'Cryptocurrency Scam', matchType: 'Regex', matchedValue: 'double.*crypto|send.*btc.*receive' }
                ],
                normalisedValues: { senderId: 'CRYPTO', senderIdNormalised: 'crypto', messageNormalised: 'bitcoin giveaway! double your crypto instantly. send 0.1 btc to wallet xyz and receive 0.2 btc back!' },
                status: 'blocked', reviewer: 'compliance@quicksms.co.uk', decisionAt: '28-01-2026 12:00:00',
                notes: [
                    { author: 'compliance@quicksms.co.uk', timestamp: '28-01-2026 11:45:00', text: 'Classic crypto doubling scam pattern' },
                    { author: 'compliance@quicksms.co.uk', timestamp: '28-01-2026 12:00:00', text: 'Blocked permanently - account flagged for review' }
                ],
                idempotencyKey: 'idem-007-stu', releaseAttempts: 0
            },
            { 
                id: 'QRN-008', timestamp: '27-01-2026 09:00:15', accountId: 'ACC-10112', accountName: 'E-Commerce Hub', 
                subAccountId: 'SUB-005', subAccountName: 'Promotions', senderId: 'SHOP', 
                recipient: '+44****3333', recipientFull: '+447700903333',
                messageSnippet: 'Flash sale! 50% off everything...', 
                fullMessage: 'Flash sale! 50% off everything this weekend only. Shop now at bit.ly/shop-sale',
                hasUrl: true, extractedUrls: ['bit.ly/shop-sale'],
                ruleTriggered: 'url', ruleName: 'URL Shortener Flag', ruleId: 'URL-002',
                triggeredRules: [
                    { engine: 'UrlEnforcementEngine', ruleId: 'URL-002', ruleName: 'URL Shortener Flag', matchType: 'Wildcard', matchedValue: 'bit.ly/*' }
                ],
                normalisedValues: { senderId: 'SHOP', senderIdNormalised: 'shop', messageNormalised: 'flash sale! 50% off everything this weekend only. shop now at bit.ly/shop-sale' },
                status: 'released', reviewer: 'admin@quicksms.co.uk', decisionAt: '27-01-2026 09:30:00',
                notes: [{ author: 'admin@quicksms.co.uk', timestamp: '27-01-2026 09:28:00', text: 'Verified shortened URL points to legitimate e-commerce site' }],
                idempotencyKey: 'idem-008-vwx', releaseAttempts: 1
            }
        ];
        
        mockData.quarantineFeatureFlags = {
            notifyCustomerAdminOnRelease: true,
            requireNoteOnBlock: false,
            allowAddExceptionFromQuarantine: true,
            allowCreateRuleFromQuarantine: true
        };
        
        mockData.antiSpamSettings = {
            preventRepeatContent: false,
            windowHours: 24,
            lastUpdated: null,
            updatedBy: null
        };
    }

    function renderAllTabs() {
        renderSenderIdTab();
        renderContentTab();
        renderUrlTab();
        renderNormTab();
        renderQuarantineTab();
    }

    function renderSenderIdTab() {
        var tbody = document.getElementById('senderid-rules-body');
        var emptyState = document.getElementById('senderid-empty-state');
        var rules = mockData.senderIdRules;

        var categoryLabels = {
            'bank_impersonation': 'Bank Impersonation',
            'government': 'Government',
            'lottery_prize': 'Lottery/Prize',
            'brand_abuse': 'Brand Abuse',
            'premium_rate': 'Premium Rate',
            'other': 'Other'
        };

        document.getElementById('senderid-active-count').textContent = rules.filter(r => r.status === 'active').length;
        document.getElementById('senderid-blocked-count').textContent = rules.filter(r => r.ruleType === 'block').length;
        document.getElementById('senderid-pending-count').textContent = rules.filter(r => r.ruleType === 'flag').length;
        document.getElementById('senderid-total-count').textContent = rules.length;

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            var ruleTypeBadge = rule.ruleType === 'block' 
                ? '<span class="sec-status-badge blocked">Block</span>'
                : '<span class="sec-status-badge pending">Flag</span>';
            var statusBadge = rule.status === 'active'
                ? '<span class="sec-status-badge active">Active</span>'
                : '<span class="sec-status-badge draft">Disabled</span>';
            var normBadge = rule.applyNormalisation
                ? '<span class="badge bg-success" style="font-size: 0.65rem;">Y</span>'
                : '<span class="badge bg-secondary" style="font-size: 0.65rem;">N</span>';
            var isSuperAdmin = currentAdmin.role === 'super_admin';
            
            return '<tr data-rule-id="' + rule.id + '">' +
                '<td><strong>' + rule.name + '</strong><br><small class="text-muted">' + rule.id + '</small></td>' +
                '<td><code style="background: #e9ecef; padding: 0.15rem 0.4rem; border-radius: 3px;">' + rule.baseSenderId + '</code></td>' +
                '<td>' + ruleTypeBadge + '</td>' +
                '<td>' + (categoryLabels[rule.category] || rule.category) + '</td>' +
                '<td class="text-center">' + normBadge + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td><small>' + rule.createdBy.split('@')[0] + '</small></td>' +
                '<td><small>' + rule.updatedAt + '</small></td>' +
                '<td>' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewSenderIdRule(\'' + rule.id + '\')"><i class="fas fa-eye me-2 text-muted"></i>View</a></li>' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="editSenderIdRule(\'' + rule.id + '\')"><i class="fas fa-edit me-2 text-muted"></i>Edit</a></li>' +
                            '<li><hr class="dropdown-divider"></li>' +
                            (rule.status === 'active' 
                                ? '<li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleSenderIdRuleStatus(\'' + rule.id + '\', \'disabled\')"><i class="fas fa-ban me-2 text-warning"></i>Disable</a></li>'
                                : '<li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleSenderIdRuleStatus(\'' + rule.id + '\', \'active\')"><i class="fas fa-check me-2 text-success"></i>Enable</a></li>') +
                            (isSuperAdmin ? '<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="showDeleteConfirmation(\'' + rule.id + '\', \'senderid\')"><i class="fas fa-trash me-2"></i>Delete</a></li>' : '') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        }).join('');
    }

    function renderContentTab() {
        var tbody = document.getElementById('content-rules-body');
        var emptyState = document.getElementById('content-empty-state');
        
        var statusFilter = document.getElementById('content-filter-status').value;
        var matchTypeFilter = document.getElementById('content-filter-matchtype').value;
        var ruleTypeFilter = document.getElementById('content-filter-ruletype').value;
        var searchTerm = document.getElementById('content-search').value.toLowerCase();
        
        var rules = mockData.contentRules.filter(function(rule) {
            if (statusFilter && rule.status !== statusFilter) return false;
            if (matchTypeFilter && rule.matchType !== matchTypeFilter) return false;
            if (ruleTypeFilter && rule.ruleType !== ruleTypeFilter) return false;
            if (searchTerm && rule.name.toLowerCase().indexOf(searchTerm) === -1 && 
                rule.matchValue.toLowerCase().indexOf(searchTerm) === -1) return false;
            return true;
        });

        document.getElementById('content-active-count').textContent = mockData.contentRules.filter(r => r.status === 'active').length;
        document.getElementById('content-blocked-count').textContent = mockData.contentRules.filter(r => r.ruleType === 'block').length;
        document.getElementById('content-pending-count').textContent = mockData.contentRules.filter(r => r.ruleType === 'flag').length;
        document.getElementById('content-total-count').textContent = mockData.contentRules.length;

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            var matchTypeBadge = rule.matchType === 'keyword' 
                ? '<span class="sec-status-badge" style="background: #e0e7ff; color: #3730a3;"><i class="fas fa-key me-1"></i>Keyword</span>'
                : '<span class="sec-status-badge" style="background: #fef3c7; color: #92400e;"><i class="fas fa-code me-1"></i>Regex</span>';
            
            var ruleTypeBadge = rule.ruleType === 'block'
                ? '<span class="sec-status-badge blocked"><i class="fas fa-ban me-1"></i>Block</span>'
                : '<span class="sec-status-badge pending"><i class="fas fa-flag me-1"></i>Flag</span>';
            
            var normBadge = rule.applyNormalisation
                ? '<span class="sec-status-badge active"><i class="fas fa-check me-1"></i>Yes</span>'
                : '<span class="sec-status-badge disabled"><i class="fas fa-times me-1"></i>No</span>';
            
            var statusBadge = '<span class="sec-status-badge ' + rule.status + '">' + 
                (rule.status === 'active' ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="fas fa-pause-circle me-1"></i>') +
                rule.status.charAt(0).toUpperCase() + rule.status.slice(1) + '</span>';
            
            var dateOnly = rule.updatedAt.split(' ')[0];
            
            return '<tr data-rule-id="' + rule.id + '">' +
                '<td><strong>' + rule.name + '</strong><br><small class="text-muted" style="font-size: 0.7rem;">' + rule.id + '</small></td>' +
                '<td>' + matchTypeBadge + '</td>' +
                '<td>' + ruleTypeBadge + '</td>' +
                '<td>' + normBadge + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td><span style="font-size: 0.8rem;">' + dateOnly + '</span></td>' +
                '<td>' +
                    '<div class="action-menu-container">' +
                        '<button class="action-menu-btn" onclick="toggleContentActionMenu(this, \'' + rule.id + '\')"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<div class="action-menu-dropdown" id="content-menu-' + rule.id + '">' +
                            '<a href="#" onclick="viewContentRule(\'' + rule.id + '\'); return false;"><i class="fas fa-eye"></i> View Details</a>' +
                            '<a href="#" onclick="editContentRule(\'' + rule.id + '\'); return false;"><i class="fas fa-edit"></i> Edit Rule</a>' +
                            '<a href="#" onclick="toggleContentRuleStatus(\'' + rule.id + '\'); return false;"><i class="fas fa-toggle-on"></i> ' + (rule.status === 'active' ? 'Disable' : 'Enable') + '</a>' +
                            '<div class="dropdown-divider"></div>' +
                            '<a href="#" class="text-danger" onclick="deleteContentRule(\'' + rule.id + '\'); return false;"><i class="fas fa-trash"></i> Delete</a>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        }).join('');
    }
    
    function toggleContentActionMenu(btn, ruleId) {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            if (menu.id !== 'content-menu-' + ruleId) {
                menu.classList.remove('show');
            }
        });
        var menu = document.getElementById('content-menu-' + ruleId);
        menu.classList.toggle('show');
    }
    
    function showAddContentRuleModal() {
        document.getElementById('content-rule-modal-title').textContent = 'Add Content Rule';
        document.getElementById('content-rule-form').reset();
        document.getElementById('content-rule-id').value = '';
        document.getElementById('content-match-type').value = 'keyword';
        document.getElementById('content-apply-normalisation').checked = true;
        updateContentMatchInputLabel();
        clearContentRuleErrors();
        var modal = new bootstrap.Modal(document.getElementById('contentRuleModal'));
        modal.show();
    }
    
    function editContentRule(ruleId) {
        var rule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('content-rule-modal-title').textContent = 'Edit Content Rule';
        document.getElementById('content-rule-id').value = rule.id;
        document.getElementById('content-rule-name').value = rule.name;
        document.getElementById('content-match-type').value = rule.matchType;
        document.getElementById('content-match-value').value = rule.matchValue;
        document.getElementById('content-rule-type').value = rule.ruleType;
        document.getElementById('content-apply-normalisation').checked = rule.applyNormalisation;
        updateContentMatchInputLabel();
        clearContentRuleErrors();
        
        closeAllContentMenus();
        var modal = new bootstrap.Modal(document.getElementById('contentRuleModal'));
        modal.show();
    }
    
    function viewContentRule(ruleId) {
        editContentRule(ruleId);
    }
    
    function updateContentMatchInputLabel() {
        var matchType = document.getElementById('content-match-type').value;
        var label = document.getElementById('content-match-value-label');
        var input = document.getElementById('content-match-value');
        var helpText = document.getElementById('content-match-value-help');
        
        if (matchType === 'keyword') {
            label.textContent = 'Keywords (comma-separated)';
            input.placeholder = 'verify your account, click here, suspended';
            helpText.textContent = 'Enter keywords separated by commas. Matching is case-insensitive.';
        } else {
            label.textContent = 'Regex Pattern';
            input.placeholder = '(verify|confirm)\\s+your\\s+(account|details)';
            helpText.textContent = 'Enter a valid regular expression. Will be validated before saving.';
        }
    }
    
    function validateContentRuleForm() {
        clearContentRuleErrors();
        var isValid = true;
        
        var name = document.getElementById('content-rule-name').value.trim();
        if (!name) {
            showContentFieldError('content-rule-name', 'Rule name is required');
            isValid = false;
        }
        
        var matchValue = document.getElementById('content-match-value').value.trim();
        if (!matchValue) {
            showContentFieldError('content-match-value', 'Match value is required');
            isValid = false;
        }
        
        var matchType = document.getElementById('content-match-type').value;
        if (matchType === 'regex' && matchValue) {
            var regexError = validateRegexPattern(matchValue);
            if (regexError) {
                showContentFieldError('content-match-value', regexError);
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function validateRegexPattern(pattern) {
        try {
            new RegExp(pattern);
            return null;
        } catch (e) {
            return 'Invalid regex: ' + e.message.replace('Invalid regular expression: ', '');
        }
    }
    
    function showContentFieldError(fieldId, message) {
        var field = document.getElementById(fieldId);
        field.classList.add('is-invalid');
        var errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearContentRuleErrors() {
        document.querySelectorAll('#content-rule-form .is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('#content-rule-form .invalid-feedback').forEach(function(el) {
            el.remove();
        });
    }
    
    function saveContentRule() {
        if (!validateContentRuleForm()) return;
        
        var ruleId = document.getElementById('content-rule-id').value;
        var ruleData = {
            name: document.getElementById('content-rule-name').value.trim(),
            matchType: document.getElementById('content-match-type').value,
            matchValue: document.getElementById('content-match-value').value.trim(),
            ruleType: document.getElementById('content-rule-type').value,
            applyNormalisation: document.getElementById('content-apply-normalisation').checked,
            status: 'active',
            updatedAt: formatDateTime(new Date())
        };
        
        var eventType, beforeState = null;
        
        if (ruleId) {
            var existingRule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
            if (existingRule) {
                beforeState = JSON.parse(JSON.stringify(existingRule));
                Object.assign(existingRule, ruleData);
                eventType = 'CONTENT_RULE_UPDATED';
            }
        } else {
            ruleData.id = 'CNT-' + String(mockData.contentRules.length + 1).padStart(3, '0');
            ruleData.createdBy = currentAdmin.email;
            ruleData.createdAt = ruleData.updatedAt;
            mockData.contentRules.push(ruleData);
            eventType = 'CONTENT_RULE_CREATED';
        }
        
        logAuditEvent(eventType, {
            ruleId: ruleId || ruleData.id,
            ruleName: ruleData.name,
            matchType: ruleData.matchType,
            ruleType: ruleData.ruleType,
            before: beforeState,
            after: ruleData
        });
        
        bootstrap.Modal.getInstance(document.getElementById('contentRuleModal')).hide();
        renderContentTab();
        showToast(ruleId ? 'Content rule updated successfully' : 'Content rule created successfully', 'success');
    }
    
    function toggleContentRuleStatus(ruleId) {
        var rule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        var beforeStatus = rule.status;
        rule.status = rule.status === 'active' ? 'disabled' : 'active';
        rule.updatedAt = formatDateTime(new Date());
        
        logAuditEvent('CONTENT_RULE_STATUS_CHANGED', {
            ruleId: ruleId,
            ruleName: rule.name,
            beforeStatus: beforeStatus,
            afterStatus: rule.status
        });
        
        closeAllContentMenus();
        renderContentTab();
        showToast('Content rule ' + (rule.status === 'active' ? 'enabled' : 'disabled'), 'success');
    }
    
    function deleteContentRule(ruleId) {
        var rule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('confirm-delete-message').textContent = 'Are you sure you want to delete the content rule "' + rule.name + '"?';
        document.getElementById('delete-rule-id').value = ruleId;
        document.getElementById('delete-rule-type').value = 'content';
        
        closeAllContentMenus();
        var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        modal.show();
    }
    
    function closeAllContentMenus() {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            menu.classList.remove('show');
        });
    }
    
    function resetContentFilters() {
        document.getElementById('content-filter-status').value = '';
        document.getElementById('content-filter-matchtype').value = '';
        document.getElementById('content-filter-ruletype').value = '';
        document.getElementById('content-search').value = '';
        renderContentTab();
    }

    function renderUrlTab() {
        var tbody = document.getElementById('url-rules-body');
        var emptyState = document.getElementById('url-empty-state');
        
        var statusFilter = document.getElementById('url-filter-status').value;
        var matchTypeFilter = document.getElementById('url-filter-matchtype').value;
        var ruleTypeFilter = document.getElementById('url-filter-ruletype').value;
        var searchTerm = document.getElementById('url-search').value.toLowerCase();
        
        var rules = mockData.urlRules.filter(function(rule) {
            if (statusFilter && rule.status !== statusFilter) return false;
            if (matchTypeFilter && rule.matchType !== matchTypeFilter) return false;
            if (ruleTypeFilter && rule.ruleType !== ruleTypeFilter) return false;
            if (searchTerm && rule.pattern.toLowerCase().indexOf(searchTerm) === -1) return false;
            return true;
        });

        document.getElementById('url-active-count').textContent = mockData.urlRules.filter(r => r.status === 'active').length;
        document.getElementById('url-block-count').textContent = mockData.urlRules.filter(r => r.ruleType === 'block').length;
        document.getElementById('url-flag-count').textContent = mockData.urlRules.filter(r => r.ruleType === 'flag').length;
        document.getElementById('url-total-count').textContent = mockData.urlRules.length;
        
        document.getElementById('domain-age-enabled').checked = mockData.domainAgeSettings.enabled;
        document.getElementById('domain-age-hours').value = mockData.domainAgeSettings.minAgeHours;
        document.getElementById('domain-age-hours').disabled = !mockData.domainAgeSettings.enabled;
        document.getElementById('domain-age-action').value = mockData.domainAgeSettings.action;
        document.getElementById('domain-age-action').disabled = !mockData.domainAgeSettings.enabled;

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
            tbody.innerHTML = rules.map(function(rule) {
                var matchTypeBadges = {
                    'exact': '<span class="sec-status-badge" style="background: #dbeafe; color: #1e40af;"><i class="fas fa-bullseye me-1"></i>Exact</span>',
                    'wildcard': '<span class="sec-status-badge" style="background: #fef3c7; color: #92400e;"><i class="fas fa-asterisk me-1"></i>Wildcard</span>',
                    'regex': '<span class="sec-status-badge" style="background: #f3e8ff; color: #6b21a8;"><i class="fas fa-code me-1"></i>Regex</span>'
                };
                
                var ruleTypeBadge = rule.ruleType === 'block'
                    ? '<span class="sec-status-badge blocked"><i class="fas fa-ban me-1"></i>Block</span>'
                    : '<span class="sec-status-badge pending"><i class="fas fa-flag me-1"></i>Flag</span>';
                
                var domainAgeBadge = rule.applyDomainAge
                    ? '<span class="sec-status-badge active"><i class="fas fa-check me-1"></i>Yes</span>'
                    : '<span class="sec-status-badge disabled"><i class="fas fa-times me-1"></i>No</span>';
                
                var statusBadge = '<span class="sec-status-badge ' + rule.status + '">' + 
                    (rule.status === 'active' ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="fas fa-pause-circle me-1"></i>') +
                    rule.status.charAt(0).toUpperCase() + rule.status.slice(1) + '</span>';
                
                var dateOnly = rule.updatedAt.split(' ')[0];
                
                return '<tr data-rule-id="' + rule.id + '">' +
                    '<td><code style="background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 4px;">' + rule.pattern + '</code><br><small class="text-muted" style="font-size: 0.7rem;">' + rule.id + '</small></td>' +
                    '<td>' + matchTypeBadges[rule.matchType] + '</td>' +
                    '<td>' + ruleTypeBadge + '</td>' +
                    '<td>' + domainAgeBadge + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td><span style="font-size: 0.8rem;">' + dateOnly + '</span></td>' +
                    '<td>' +
                        '<div class="action-menu-container">' +
                            '<button class="action-menu-btn" onclick="toggleUrlActionMenu(this, \'' + rule.id + '\')"><i class="fas fa-ellipsis-v"></i></button>' +
                            '<div class="action-menu-dropdown" id="url-menu-' + rule.id + '">' +
                                '<a href="#" onclick="viewUrlRule(\'' + rule.id + '\'); return false;"><i class="fas fa-eye"></i> View Details</a>' +
                                '<a href="#" onclick="editUrlRule(\'' + rule.id + '\'); return false;"><i class="fas fa-edit"></i> Edit Rule</a>' +
                                '<a href="#" onclick="toggleUrlRuleStatus(\'' + rule.id + '\'); return false;"><i class="fas fa-toggle-on"></i> ' + (rule.status === 'active' ? 'Disable' : 'Enable') + '</a>' +
                                '<div class="dropdown-divider"></div>' +
                                '<a href="#" class="text-danger" onclick="deleteUrlRule(\'' + rule.id + '\'); return false;"><i class="fas fa-trash"></i> Delete</a>' +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '</tr>';
            }).join('');
        }
        
        renderDomainAgeExceptions();
    }
    
    function renderDomainAgeExceptions() {
        var tbody = document.getElementById('domain-age-exceptions-body');
        var emptyState = document.getElementById('domain-exceptions-empty-state');
        var exceptions = mockData.domainAgeExceptions;
        
        if (exceptions.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }
        
        emptyState.style.display = 'none';
        tbody.innerHTML = exceptions.map(function(exc) {
            var dateOnly = exc.addedAt.split(' ')[0];
            return '<tr data-exception-id="' + exc.id + '">' +
                '<td><code>' + exc.accountId + '</code></td>' +
                '<td><strong>' + exc.accountName + '</strong></td>' +
                '<td><span style="font-size: 0.85rem;">' + exc.reason + '</span></td>' +
                '<td><span style="font-size: 0.8rem;">' + exc.addedBy + '</span></td>' +
                '<td><span style="font-size: 0.8rem;">' + dateOnly + '</span></td>' +
                '<td>' +
                    '<button class="action-menu-btn text-danger" onclick="removeDomainAgeException(\'' + exc.id + '\')" title="Remove Exception">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>' +
                '</td>' +
                '</tr>';
        }).join('');
    }
    
    function toggleUrlActionMenu(btn, ruleId) {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            if (menu.id !== 'url-menu-' + ruleId) {
                menu.classList.remove('show');
            }
        });
        var menu = document.getElementById('url-menu-' + ruleId);
        menu.classList.toggle('show');
    }
    
    function showAddUrlRuleModal() {
        document.getElementById('url-rule-modal-title').textContent = 'Add URL Rule';
        document.getElementById('url-rule-form').reset();
        document.getElementById('url-rule-id').value = '';
        document.getElementById('url-match-type').value = 'exact';
        document.getElementById('url-apply-domain-age').checked = true;
        updateUrlPatternLabel();
        clearUrlRuleErrors();
        var modal = new bootstrap.Modal(document.getElementById('urlRuleModal'));
        modal.show();
    }
    
    function editUrlRule(ruleId) {
        var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('url-rule-modal-title').textContent = 'Edit URL Rule';
        document.getElementById('url-rule-id').value = rule.id;
        document.getElementById('url-pattern').value = rule.pattern;
        document.getElementById('url-match-type').value = rule.matchType;
        document.getElementById('url-rule-type').value = rule.ruleType;
        document.getElementById('url-apply-domain-age').checked = rule.applyDomainAge;
        updateUrlPatternLabel();
        clearUrlRuleErrors();
        
        closeAllUrlMenus();
        var modal = new bootstrap.Modal(document.getElementById('urlRuleModal'));
        modal.show();
    }
    
    function viewUrlRule(ruleId) {
        editUrlRule(ruleId);
    }
    
    function updateUrlPatternLabel() {
        var matchType = document.getElementById('url-match-type').value;
        var label = document.getElementById('url-pattern-label');
        var input = document.getElementById('url-pattern');
        var helpText = document.getElementById('url-pattern-help');
        
        var config = {
            'exact': { label: 'Domain', placeholder: 'example.com', help: 'Enter the exact domain to match (e.g., example.com)' },
            'wildcard': { label: 'Wildcard Pattern', placeholder: '*.example.com', help: 'Use * for wildcard matching (e.g., *.example.com matches all subdomains)' },
            'regex': { label: 'Regex Pattern', placeholder: 'phish\\d+\\.com', help: 'Enter a valid regular expression pattern' }
        };
        
        label.textContent = config[matchType].label;
        input.placeholder = config[matchType].placeholder;
        helpText.textContent = config[matchType].help;
    }
    
    function validateUrlRuleForm() {
        clearUrlRuleErrors();
        var isValid = true;
        
        var pattern = document.getElementById('url-pattern').value.trim();
        if (!pattern) {
            showUrlFieldError('url-pattern', 'Domain/pattern is required');
            isValid = false;
        }
        
        var matchType = document.getElementById('url-match-type').value;
        if (matchType === 'regex' && pattern) {
            try {
                new RegExp(pattern);
            } catch (e) {
                showUrlFieldError('url-pattern', 'Invalid regex: ' + e.message.replace('Invalid regular expression: ', ''));
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function showUrlFieldError(fieldId, message) {
        var field = document.getElementById(fieldId);
        field.classList.add('is-invalid');
        var errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearUrlRuleErrors() {
        document.querySelectorAll('#url-rule-form .is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('#url-rule-form .invalid-feedback').forEach(function(el) {
            el.remove();
        });
    }
    
    function saveUrlRule() {
        if (!validateUrlRuleForm()) return;
        
        var ruleId = document.getElementById('url-rule-id').value;
        var ruleData = {
            pattern: document.getElementById('url-pattern').value.trim(),
            matchType: document.getElementById('url-match-type').value,
            ruleType: document.getElementById('url-rule-type').value,
            applyDomainAge: document.getElementById('url-apply-domain-age').checked,
            status: 'active',
            updatedAt: formatDateTime(new Date())
        };
        
        var eventType, beforeState = null;
        
        if (ruleId) {
            var existingRule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
            if (existingRule) {
                beforeState = JSON.parse(JSON.stringify(existingRule));
                Object.assign(existingRule, ruleData);
                eventType = 'URL_RULE_UPDATED';
            }
        } else {
            ruleData.id = 'URL-' + String(mockData.urlRules.length + 1).padStart(3, '0');
            ruleData.createdBy = currentAdmin.email;
            ruleData.createdAt = ruleData.updatedAt;
            mockData.urlRules.push(ruleData);
            eventType = 'URL_RULE_CREATED';
        }
        
        logAuditEvent(eventType, {
            ruleId: ruleId || ruleData.id,
            pattern: ruleData.pattern,
            matchType: ruleData.matchType,
            ruleType: ruleData.ruleType,
            before: beforeState,
            after: ruleData
        });
        
        bootstrap.Modal.getInstance(document.getElementById('urlRuleModal')).hide();
        renderUrlTab();
        showToast(ruleId ? 'URL rule updated successfully' : 'URL rule created successfully', 'success');
    }
    
    function toggleUrlRuleStatus(ruleId) {
        var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        var beforeStatus = rule.status;
        rule.status = rule.status === 'active' ? 'disabled' : 'active';
        rule.updatedAt = formatDateTime(new Date());
        
        logAuditEvent('URL_RULE_STATUS_CHANGED', {
            ruleId: ruleId,
            pattern: rule.pattern,
            beforeStatus: beforeStatus,
            afterStatus: rule.status
        });
        
        closeAllUrlMenus();
        renderUrlTab();
        showToast('URL rule ' + (rule.status === 'active' ? 'enabled' : 'disabled'), 'success');
    }
    
    function deleteUrlRule(ruleId) {
        var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('confirm-delete-message').textContent = 'Are you sure you want to delete the URL rule "' + rule.pattern + '"?';
        document.getElementById('delete-rule-id').value = ruleId;
        document.getElementById('delete-rule-type').value = 'url';
        
        closeAllUrlMenus();
        var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        modal.show();
    }
    
    function deleteUrlRuleById(ruleId) {
        var ruleIndex = mockData.urlRules.findIndex(function(r) { return r.id === ruleId; });
        if (ruleIndex === -1) return;
        
        var deletedRule = mockData.urlRules[ruleIndex];
        mockData.urlRules.splice(ruleIndex, 1);
        
        logAuditEvent('URL_RULE_DELETED', {
            ruleId: ruleId,
            pattern: deletedRule.pattern,
            deletedRule: deletedRule
        });
        
        showToast('URL rule deleted successfully', 'success');
    }
    
    function closeAllUrlMenus() {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            menu.classList.remove('show');
        });
    }
    
    function resetUrlFilters() {
        document.getElementById('url-filter-status').value = '';
        document.getElementById('url-filter-matchtype').value = '';
        document.getElementById('url-filter-ruletype').value = '';
        document.getElementById('url-search').value = '';
        renderUrlTab();
    }
    
    function saveDomainAgeSettings() {
        var enabled = document.getElementById('domain-age-enabled').checked;
        var hours = parseInt(document.getElementById('domain-age-hours').value) || 72;
        var action = document.getElementById('domain-age-action').value;
        
        var beforeSettings = JSON.parse(JSON.stringify(mockData.domainAgeSettings));
        mockData.domainAgeSettings.enabled = enabled;
        mockData.domainAgeSettings.minAgeHours = hours;
        mockData.domainAgeSettings.action = action;
        
        logAuditEvent('DOMAIN_AGE_SETTINGS_UPDATED', {
            before: beforeSettings,
            after: mockData.domainAgeSettings
        });
        
        showToast('Domain age settings saved successfully', 'success');
    }
    
    function showAddDomainAgeExceptionModal() {
        document.getElementById('exception-form').reset();
        clearExceptionErrors();
        var modal = new bootstrap.Modal(document.getElementById('domainAgeExceptionModal'));
        modal.show();
    }
    
    function saveException() {
        clearExceptionErrors();
        var accountId = document.getElementById('exception-account-id').value.trim();
        var accountName = document.getElementById('exception-account-name').value.trim();
        var reason = document.getElementById('exception-reason').value.trim();
        
        var isValid = true;
        if (!accountId) {
            document.getElementById('exception-account-id').classList.add('is-invalid');
            isValid = false;
        }
        if (!accountName) {
            document.getElementById('exception-account-name').classList.add('is-invalid');
            isValid = false;
        }
        if (!reason) {
            document.getElementById('exception-reason').classList.add('is-invalid');
            isValid = false;
        }
        
        if (!isValid) return;
        
        var exception = {
            id: 'EXC-' + String(mockData.domainAgeExceptions.length + 1).padStart(3, '0'),
            accountId: accountId,
            accountName: accountName,
            reason: reason,
            addedBy: currentAdmin.email,
            addedAt: formatDateTime(new Date())
        };
        
        mockData.domainAgeExceptions.push(exception);
        
        logAuditEvent('DOMAIN_AGE_EXCEPTION_ADDED', {
            exceptionId: exception.id,
            accountId: accountId,
            accountName: accountName,
            reason: reason
        });
        
        bootstrap.Modal.getInstance(document.getElementById('domainAgeExceptionModal')).hide();
        renderDomainAgeExceptions();
        showToast('Domain age exception added successfully', 'success');
    }
    
    function removeDomainAgeException(exceptionId) {
        var excIndex = mockData.domainAgeExceptions.findIndex(function(e) { return e.id === exceptionId; });
        if (excIndex === -1) return;
        
        var removedExc = mockData.domainAgeExceptions[excIndex];
        mockData.domainAgeExceptions.splice(excIndex, 1);
        
        logAuditEvent('DOMAIN_AGE_EXCEPTION_REMOVED', {
            exceptionId: exceptionId,
            accountId: removedExc.accountId,
            accountName: removedExc.accountName
        });
        
        renderDomainAgeExceptions();
        showToast('Exception removed successfully', 'success');
    }
    
    function clearExceptionErrors() {
        document.querySelectorAll('#exception-form .is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
    }
    
    function setupUrlTabListeners() {
        document.getElementById('url-filter-status').addEventListener('change', renderUrlTab);
        document.getElementById('url-filter-matchtype').addEventListener('change', renderUrlTab);
        document.getElementById('url-filter-ruletype').addEventListener('change', renderUrlTab);
        document.getElementById('url-search').addEventListener('input', renderUrlTab);
        
        document.getElementById('domain-age-enabled').addEventListener('change', function() {
            document.getElementById('domain-age-hours').disabled = !this.checked;
            document.getElementById('domain-age-action').disabled = !this.checked;
        });
    }

    function renderNormTab() {
        var tbody = document.getElementById('norm-rules-body');
        var emptyState = document.getElementById('norm-empty-state');
        var rules = mockData.normalisationRules;

        var categoryLabels = {
            'substitution': 'Character Substitution',
            'homoglyph': 'Homoglyph Detection',
            'unicode': 'Unicode Normalisation',
            'case': 'Case Folding'
        };

        var categoryColors = {
            'substitution': '#d97706',
            'homoglyph': '#7c3aed',
            'unicode': '#2563eb',
            'case': '#059669'
        };

        var scopeIcons = {
            'all': 'fa-globe',
            'senderid': 'fa-id-badge',
            'content': 'fa-comment-alt',
            'url': 'fa-link'
        };

        var totalMappings = rules.reduce(function(sum, rule) {
            return sum + (rule.mappings ? rule.mappings.length : 0);
        }, 0);

        document.getElementById('norm-active-count').textContent = rules.filter(r => r.status === 'active').length;
        document.getElementById('norm-disabled-count').textContent = rules.filter(r => r.status === 'disabled').length;
        document.getElementById('norm-mappings-count').textContent = totalMappings;
        document.getElementById('norm-total-count').textContent = rules.length;

        renderSubstitutionMapDisplay();

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            var categoryLabel = categoryLabels[rule.category] || rule.category;
            var categoryColor = categoryColors[rule.category] || '#6c757d';
            var scopeIcon = scopeIcons[rule.scope] || 'fa-globe';
            
            var mappingsPreview = '';
            if (rule.mappings && rule.mappings.length > 0) {
                var previewMappings = rule.mappings.slice(0, 3);
                mappingsPreview = previewMappings.map(function(m) {
                    return '<span class="mapping-chip">' + 
                        '<code>' + m.base + '</code> → <code>' + m.equivalents.slice(0, 2).join(', ') + '</code>' +
                        (m.equivalents.length > 2 ? '...' : '') +
                    '</span>';
                }).join(' ');
                if (rule.mappings.length > 3) {
                    mappingsPreview += '<span class="text-muted" style="font-size: 0.75rem;"> +' + (rule.mappings.length - 3) + ' more</span>';
                }
            }

            return '<tr data-rule-id="' + rule.id + '">' +
                '<td><strong>' + rule.name + '</strong><br><small class="text-muted">' + (rule.description || '') + '</small></td>' +
                '<td><span class="badge" style="background: ' + categoryColor + '; color: white;">' + categoryLabel + '</span></td>' +
                '<td style="max-width: 250px;">' + mappingsPreview + '</td>' +
                '<td><i class="fas ' + scopeIcon + ' me-1" style="color: #1e3a5f;"></i>' + rule.scopeLabel + '</td>' +
                '<td><span class="badge bg-light text-dark">' + rule.priority + '</span></td>' +
                '<td><span class="sec-status-badge ' + rule.status + '">' + rule.status.charAt(0).toUpperCase() + rule.status.slice(1) + '</span></td>' +
                '<td>' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewNormalisationRule(' + rule.id + ')"><i class="fas fa-eye me-2 text-muted"></i>View Details</a></li>' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="editNormalisationRule(' + rule.id + ')"><i class="fas fa-edit me-2 text-muted"></i>Edit Rule</a></li>' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="testNormalisationRuleById(' + rule.id + ')"><i class="fas fa-flask me-2 text-muted"></i>Test Rule</a></li>' +
                            '<li><hr class="dropdown-divider"></li>' +
                            (rule.status === 'active' 
                                ? '<li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleNormalisationRuleStatus(' + rule.id + ', \'disabled\')"><i class="fas fa-ban me-2 text-warning"></i>Disable</a></li>'
                                : '<li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleNormalisationRuleStatus(' + rule.id + ', \'active\')"><i class="fas fa-check me-2 text-success"></i>Enable</a></li>') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        }).join('');
    }

    function renderQuarantineTab() {
        var tbody = document.getElementById('quarantine-body');
        var emptyState = document.getElementById('quarantine-empty-state');
        
        var statusFilter = document.getElementById('quarantine-filter-status').value;
        var ruleFilter = document.getElementById('quarantine-filter-rule').value;
        var urlFilter = document.getElementById('quarantine-filter-url').value;
        var accountFilter = document.getElementById('quarantine-filter-account').value;
        var searchTerm = document.getElementById('quarantine-search').value.toLowerCase();
        
        var messages = mockData.quarantinedMessages.filter(function(msg) {
            if (statusFilter && msg.status !== statusFilter) return false;
            if (ruleFilter && msg.ruleTriggered !== ruleFilter) return false;
            if (urlFilter === 'yes' && !msg.hasUrl) return false;
            if (urlFilter === 'no' && msg.hasUrl) return false;
            if (accountFilter && msg.accountId !== accountFilter) return false;
            if (searchTerm) {
                var searchFields = [msg.accountName, msg.senderId, msg.messageSnippet, msg.ruleName].join(' ').toLowerCase();
                if (searchFields.indexOf(searchTerm) === -1) return false;
            }
            return true;
        });

        document.getElementById('quarantine-pending-count').textContent = mockData.quarantinedMessages.filter(m => m.status === 'pending').length;
        document.getElementById('quarantine-released-count').textContent = mockData.quarantinedMessages.filter(m => m.status === 'released').length;
        document.getElementById('quarantine-blocked-count').textContent = mockData.quarantinedMessages.filter(m => m.status === 'blocked').length;
        document.getElementById('quarantine-total-count').textContent = mockData.quarantinedMessages.length;
        
        populateAccountFilter();

        if (messages.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = messages.map(function(msg) {
            var ruleTypeBadges = {
                'senderid': '<span class="sec-status-badge" style="background: #fef3c7; color: #92400e;"><i class="fas fa-id-badge me-1"></i>SenderID</span>',
                'content': '<span class="sec-status-badge" style="background: #dbeafe; color: #1e40af;"><i class="fas fa-comment me-1"></i>Content</span>',
                'url': '<span class="sec-status-badge" style="background: #f3e8ff; color: #6b21a8;"><i class="fas fa-link me-1"></i>URL</span>',
                'domain_age': '<span class="sec-status-badge" style="background: #fee2e2; color: #991b1b;"><i class="fas fa-clock me-1"></i>Domain Age</span>',
                'antispam': '<span class="sec-status-badge" style="background: #fce7f3; color: #9d174d;"><i class="fas fa-shield-virus me-1"></i>Anti-Spam</span>'
            };
            
            var ruleTriggeredHtml = (ruleTypeBadges[msg.ruleTriggered] || '<span class="sec-status-badge disabled">Unknown</span>') +
                '<br><small class="text-muted" title="Rule: ' + msg.ruleId + '">' + msg.ruleName + '</small>';
            
            if (msg.triggeredRules && msg.triggeredRules.length > 1) {
                ruleTriggeredHtml += '<br><small class="text-primary" style="cursor: pointer;" onclick="viewQuarantinedMessage(\'' + msg.id + '\')">+' + (msg.triggeredRules.length - 1) + ' more</small>';
            }
            
            var statusBadges = {
                'pending': '<span class="sec-status-badge pending"><i class="fas fa-clock me-1"></i>Pending</span>',
                'released': '<span class="sec-status-badge active"><i class="fas fa-check-circle me-1"></i>Released</span>',
                'blocked': '<span class="sec-status-badge blocked"><i class="fas fa-ban me-1"></i>Blocked</span>'
            };
            
            var urlBadge = msg.hasUrl 
                ? '<span class="sec-status-badge" style="background: #dcfce7; color: #166534;"><i class="fas fa-check me-1"></i>Yes</span>'
                : '<span class="sec-status-badge disabled"><i class="fas fa-times me-1"></i>No</span>';
            
            var subAccountDisplay = msg.subAccountName 
                ? '<span style="font-size: 0.75rem;">' + msg.subAccountName + '</span><br><small class="text-muted">' + msg.subAccountId + '</small>'
                : '<span class="text-muted">—</span>';
            
            var reviewerDisplay = msg.reviewer 
                ? '<span style="font-size: 0.75rem;">' + msg.reviewer.split('@')[0] + '</span>'
                : '<span class="text-muted">—</span>';
            
            var decisionDisplay = msg.decisionAt 
                ? '<span style="font-size: 0.75rem;">' + msg.decisionAt.split(' ')[0] + '</span>'
                : '<span class="text-muted">—</span>';
            
            var actionButtons = '';
            if (msg.status === 'pending') {
                actionButtons = '<div class="dropdown">' +
                    '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end">' +
                        '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-eye me-2 text-muted"></i>View Details</a></li>' +
                        '<li><hr class="dropdown-divider"></li>' +
                        '<li><a class="dropdown-item text-success" href="javascript:void(0)" onclick="releaseQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-check-circle me-2"></i>Release Message</a></li>' +
                        '<li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="blockQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-ban me-2"></i>Block Message</a></li>' +
                    '</ul>' +
                '</div>';
            } else {
                actionButtons = '<div class="dropdown">' +
                    '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end">' +
                        '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-eye me-2 text-muted"></i>View Details</a></li>' +
                    '</ul>' +
                '</div>';
            }
            
            return '<tr data-msg-id="' + msg.id + '">' +
                '<td><input type="checkbox" class="quarantine-checkbox" data-id="' + msg.id + '"' + (msg.status !== 'pending' ? ' disabled' : '') + '></td>' +
                '<td><span style="font-size: 0.75rem;">' + msg.timestamp + '</span></td>' +
                '<td><strong style="font-size: 0.8rem;">' + msg.accountName + '</strong><br><small class="text-muted">' + msg.accountId + '</small></td>' +
                '<td>' + subAccountDisplay + '</td>' +
                '<td><code style="font-size: 0.8rem; background: #f8f9fa; padding: 0.15rem 0.35rem; border-radius: 3px;">' + msg.senderId + '</code></td>' +
                '<td><span style="font-size: 0.8rem; max-width: 200px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + msg.messageSnippet + '">' + msg.messageSnippet + '</span></td>' +
                '<td>' + urlBadge + '</td>' +
                '<td>' + ruleTriggeredHtml + '</td>' +
                '<td>' + statusBadges[msg.status] + '</td>' +
                '<td>' + reviewerDisplay + '</td>' +
                '<td>' + decisionDisplay + '</td>' +
                '<td>' + actionButtons + '</td>' +
                '</tr>';
        }).join('');
    }
    
    function populateAccountFilter() {
        var select = document.getElementById('quarantine-filter-account');
        var currentValue = select.value;
        var accounts = [];
        mockData.quarantinedMessages.forEach(function(msg) {
            if (accounts.indexOf(msg.accountId) === -1) {
                accounts.push(msg.accountId);
            }
        });
        
        var options = '<option value="">All Accounts</option>';
        accounts.forEach(function(accId) {
            var msg = mockData.quarantinedMessages.find(function(m) { return m.accountId === accId; });
            options += '<option value="' + accId + '">' + msg.accountName + '</option>';
        });
        select.innerHTML = options;
        select.value = currentValue;
    }
    
    var currentQuarantineMessageId = null;
    
    function viewQuarantinedMessage(msgId) {
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === msgId; });
        if (!msg) return;
        
        currentQuarantineMessageId = msgId;
        
        document.getElementById('qrn-view-id-header').textContent = msg.id;
        document.getElementById('qrn-view-id').textContent = msg.id;
        document.getElementById('qrn-view-timestamp').textContent = msg.timestamp;
        document.getElementById('qrn-view-account').textContent = msg.accountName + ' (' + msg.accountId + ')';
        document.getElementById('qrn-view-subaccount').textContent = msg.subAccountName ? msg.subAccountName + ' (' + msg.subAccountId + ')' : '—';
        document.getElementById('qrn-view-senderid').textContent = msg.senderId;
        document.getElementById('qrn-view-recipient').textContent = msg.recipient || '—';
        document.getElementById('qrn-view-hasurl').innerHTML = msg.hasUrl 
            ? '<span class="badge bg-success" style="font-size: 0.7rem;">Yes</span>' 
            : '<span class="badge bg-secondary" style="font-size: 0.7rem;">No</span>';
        
        document.getElementById('qrn-view-message').innerHTML = escapeHtml(msg.fullMessage || msg.messageSnippet);
        
        var statusBadge = msg.status === 'pending' 
            ? '<span class="badge bg-warning text-dark">Pending</span>'
            : msg.status === 'released'
                ? '<span class="badge bg-success">Released</span>'
                : '<span class="badge bg-danger">Blocked</span>';
        document.getElementById('qrn-view-status').innerHTML = statusBadge;
        document.getElementById('qrn-view-reviewer').textContent = msg.reviewer || '—';
        document.getElementById('qrn-view-decisionat').textContent = msg.decisionAt || '—';
        
        var triggeredRulesHtml = '';
        if (msg.triggeredRules && msg.triggeredRules.length > 0) {
            triggeredRulesHtml = '<div class="list-group list-group-flush" style="font-size: 0.8rem;">';
            msg.triggeredRules.forEach(function(rule) {
                var engineColor = rule.engine === 'SenderIdEnforcementEngine' ? '#6b21a8' 
                    : rule.engine === 'MessageContentEngine' ? '#1e3a5f'
                    : rule.engine === 'UrlEnforcementEngine' ? '#0d6efd'
                    : '#dc3545';
                triggeredRulesHtml += '<div class="list-group-item px-2 py-2" style="border-left: 3px solid ' + engineColor + ';">' +
                    '<div><strong>' + rule.ruleName + '</strong> <code style="font-size: 0.7rem; background: #f8f9fa; padding: 0.1rem 0.3rem;">' + rule.ruleId + '</code></div>' +
                    '<small class="text-muted">Engine: ' + rule.engine + '</small><br>' +
                    '<small>Match: <span class="badge bg-light text-dark">' + rule.matchType + '</span> ' + escapeHtml(rule.matchedValue) + '</small>' +
                '</div>';
            });
            triggeredRulesHtml += '</div>';
        } else {
            triggeredRulesHtml = '<span class="text-muted" style="font-size: 0.8rem;">No rule details available</span>';
        }
        document.getElementById('qrn-view-triggered-rules').innerHTML = triggeredRulesHtml;
        
        var normHtml = '';
        if (msg.normalisedValues) {
            normHtml = '<table class="table table-sm table-borderless mb-0" style="font-size: 0.75rem;">';
            if (msg.normalisedValues.senderId !== msg.normalisedValues.senderIdNormalised) {
                normHtml += '<tr><td style="width: 90px;"><strong>SenderID:</strong></td><td><code>' + msg.normalisedValues.senderId + '</code> → <code>' + msg.normalisedValues.senderIdNormalised + '</code></td></tr>';
            } else {
                normHtml += '<tr><td style="width: 90px;"><strong>SenderID:</strong></td><td><code>' + msg.normalisedValues.senderIdNormalised + '</code> <span class="text-muted">(unchanged)</span></td></tr>';
            }
            normHtml += '<tr><td colspan="2"><strong>Message (normalised):</strong></td></tr>';
            normHtml += '<tr><td colspan="2" style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px; word-break: break-word;">' + escapeHtml(msg.normalisedValues.messageNormalised) + '</td></tr>';
            normHtml += '</table>';
        } else {
            normHtml = '<span class="text-muted" style="font-size: 0.8rem;">Normalisation not applied</span>';
        }
        document.getElementById('qrn-view-normalised').innerHTML = normHtml;
        
        var notesHtml = '';
        if (msg.notes && msg.notes.length > 0) {
            msg.notes.forEach(function(note) {
                notesHtml += '<div class="mb-2 p-2 bg-light rounded" style="border-left: 3px solid #1e3a5f;">' +
                    '<small class="text-muted">' + note.timestamp + ' - ' + note.author.split('@')[0] + '</small><br>' +
                    '<span>' + escapeHtml(note.text) + '</span></div>';
            });
        } else {
            notesHtml = '<span class="text-muted" style="font-size: 0.8rem;">No notes yet</span>';
        }
        document.getElementById('qrn-view-notes-list').innerHTML = notesHtml;
        document.getElementById('qrn-new-note').value = '';
        
        var addNoteSection = document.getElementById('qrn-add-note-section');
        addNoteSection.style.display = 'block';
        
        var notifySection = document.getElementById('qrn-notify-customer-section');
        var notifyCheckbox = document.getElementById('qrn-notify-customer');
        if (msg.status === 'pending' && mockData.quarantineFeatureFlags.notifyCustomerAdminOnRelease) {
            notifySection.style.display = 'block';
            notifyCheckbox.checked = false;
        } else {
            notifySection.style.display = 'none';
        }
        
        var actionsDiv = document.getElementById('qrn-view-actions');
        if (msg.status === 'pending') {
            var actionsHtml = '<button class="btn btn-success btn-sm" onclick="releaseQuarantinedMessageFromModal()">' +
                '<i class="fas fa-paper-plane me-1"></i> Release (Send)</button> ' +
                '<button class="btn btn-danger btn-sm" onclick="blockQuarantinedMessageFromModal()">' +
                '<i class="fas fa-ban me-1"></i> Block Permanently</button> ';
            
            if (mockData.quarantineFeatureFlags.allowAddExceptionFromQuarantine) {
                actionsHtml += '<button class="btn btn-outline-primary btn-sm" onclick="addExceptionFromQuarantine()">' +
                    '<i class="fas fa-shield-alt me-1"></i> Add Exception</button> ';
            }
            if (mockData.quarantineFeatureFlags.allowCreateRuleFromQuarantine) {
                actionsHtml += '<button class="btn btn-outline-secondary btn-sm" onclick="createRuleFromQuarantine()">' +
                    '<i class="fas fa-plus me-1"></i> Create Rule</button>';
            }
            actionsDiv.innerHTML = actionsHtml;
        } else {
            actionsDiv.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-check-circle me-1"></i> Reviewed</span> ' +
                '<span class="text-muted" style="font-size: 0.85rem;">Decision made on ' + msg.decisionAt + ' by ' + (msg.reviewer ? msg.reviewer.split('@')[0] : 'System') + '</span>';
        }
        
        var modal = new bootstrap.Modal(document.getElementById('quarantineViewModal'));
        modal.show();
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function releaseQuarantinedMessageFromModal() {
        if (!currentQuarantineMessageId) return;
        var notifyCustomer = document.getElementById('qrn-notify-customer').checked;
        releaseQuarantinedMessage(currentQuarantineMessageId, notifyCustomer);
        bootstrap.Modal.getInstance(document.getElementById('quarantineViewModal')).hide();
    }
    
    function blockQuarantinedMessageFromModal() {
        if (!currentQuarantineMessageId) return;
        blockQuarantinedMessage(currentQuarantineMessageId);
        bootstrap.Modal.getInstance(document.getElementById('quarantineViewModal')).hide();
    }
    
    function addQuarantineNote() {
        if (!currentQuarantineMessageId) return;
        var noteText = document.getElementById('qrn-new-note').value.trim();
        if (!noteText) {
            showToast('Please enter a note', 'warning');
            return;
        }
        
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === currentQuarantineMessageId; });
        if (!msg) return;
        
        if (!msg.notes) msg.notes = [];
        msg.notes.push({
            author: currentAdmin.email,
            timestamp: formatDateTime(new Date()),
            text: noteText
        });
        
        logAuditEvent('QUARANTINE_NOTE_ADDED', {
            messageId: currentQuarantineMessageId,
            accountId: msg.accountId,
            note: noteText,
            admin: currentAdmin.email
        });
        
        viewQuarantinedMessage(currentQuarantineMessageId);
        showToast('Note added', 'success');
    }
    
    function addExceptionFromQuarantine() {
        if (!currentQuarantineMessageId) return;
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === currentQuarantineMessageId; });
        if (!msg) return;
        
        bootstrap.Modal.getInstance(document.getElementById('quarantineViewModal')).hide();
        
        var exceptionType = msg.ruleTriggered;
        if (exceptionType === 'senderid') {
            showToast('TODO: Deep-link to SenderID exceptions with account=' + msg.accountId + ', senderId=' + msg.senderId, 'info');
        } else if (exceptionType === 'content') {
            showToast('TODO: Deep-link to Content exceptions with account=' + msg.accountId, 'info');
        } else if (exceptionType === 'url' || exceptionType === 'domain_age') {
            document.getElementById('exception-account').value = msg.accountId;
            var modal = new bootstrap.Modal(document.getElementById('domainAgeExceptionModal'));
            modal.show();
        }
        
        logAuditEvent('QUARANTINE_EXCEPTION_STARTED', {
            messageId: currentQuarantineMessageId,
            accountId: msg.accountId,
            ruleType: exceptionType,
            admin: currentAdmin.email
        });
    }
    
    function createRuleFromQuarantine() {
        if (!currentQuarantineMessageId) return;
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === currentQuarantineMessageId; });
        if (!msg) return;
        
        bootstrap.Modal.getInstance(document.getElementById('quarantineViewModal')).hide();
        
        if (msg.ruleTriggered === 'content' || msg.extractedUrls.length === 0) {
            document.querySelector('#contentRuleModal [data-content-type="keyword"]').click();
            document.getElementById('content-rule-value').value = msg.fullMessage ? msg.fullMessage.substring(0, 50) : '';
            var modal = new bootstrap.Modal(document.getElementById('contentRuleModal'));
            modal.show();
        } else {
            document.getElementById('url-pattern').value = msg.extractedUrls[0] || '';
            var modal = new bootstrap.Modal(document.getElementById('urlRuleModal'));
            modal.show();
        }
        
        logAuditEvent('QUARANTINE_RULE_CREATE_STARTED', {
            messageId: currentQuarantineMessageId,
            accountId: msg.accountId,
            prefilledFrom: msg.ruleTriggered,
            admin: currentAdmin.email
        });
        
        showToast('Rule form prefilled from quarantine data', 'info');
    }
    
    function releaseQuarantinedMessage(msgId, notifyCustomer) {
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === msgId; });
        if (!msg) return;
        
        if (msg.status !== 'pending') {
            console.log('[Quarantine] Idempotency check: Message ' + msgId + ' already processed (status: ' + msg.status + ')');
            showToast('This message has already been reviewed', 'warning');
            return;
        }
        
        if (msg.releaseAttempts > 0) {
            console.log('[Quarantine] Idempotency warning: Release already attempted for ' + msgId + ' (attempts: ' + msg.releaseAttempts + ')');
        }
        msg.releaseAttempts = (msg.releaseAttempts || 0) + 1;
        
        msg.status = 'released';
        msg.reviewer = currentAdmin.email;
        msg.decisionAt = formatDateTime(new Date());
        
        logAuditEvent('QUARANTINE_MESSAGE_RELEASED', {
            messageId: msgId,
            idempotencyKey: msg.idempotencyKey,
            accountId: msg.accountId,
            accountName: msg.accountName,
            senderId: msg.senderId,
            recipient: msg.recipient,
            ruleTriggered: msg.ruleTriggered,
            triggeredRules: msg.triggeredRules.map(function(r) { return r.ruleId; }),
            reviewer: currentAdmin.email,
            notifyCustomer: notifyCustomer || false,
            releaseAttempts: msg.releaseAttempts
        });
        
        if (notifyCustomer && mockData.quarantineFeatureFlags.notifyCustomerAdminOnRelease) {
            console.log('[Quarantine] TODO: Send notification to customer admin for account ' + msg.accountId);
            logAuditEvent('QUARANTINE_CUSTOMER_NOTIFIED', {
                messageId: msgId,
                accountId: msg.accountId,
                notificationType: 'message_released'
            });
        }
        
        console.log('[Quarantine] Message ' + msgId + ' released - resuming delivery pipeline');
        
        renderQuarantineTab();
        showToast('Message released for delivery' + (notifyCustomer ? ' (customer notified)' : ''), 'success');
    }
    
    function blockQuarantinedMessage(msgId) {
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === msgId; });
        if (!msg) return;
        
        if (msg.status !== 'pending') {
            console.log('[Quarantine] Idempotency check: Message ' + msgId + ' already processed (status: ' + msg.status + ')');
            showToast('This message has already been reviewed', 'warning');
            return;
        }
        
        msg.status = 'blocked';
        msg.reviewer = currentAdmin.email;
        msg.decisionAt = formatDateTime(new Date());
        
        logAuditEvent('QUARANTINE_MESSAGE_BLOCKED', {
            messageId: msgId,
            idempotencyKey: msg.idempotencyKey,
            accountId: msg.accountId,
            accountName: msg.accountName,
            senderId: msg.senderId,
            recipient: msg.recipient,
            ruleTriggered: msg.ruleTriggered,
            triggeredRules: msg.triggeredRules ? msg.triggeredRules.map(function(r) { return r.ruleId; }) : [],
            reviewer: currentAdmin.email,
            permanent: true
        });
        
        console.log('[Quarantine] Message ' + msgId + ' permanently blocked');
        
        renderQuarantineTab();
        showToast('Message permanently blocked', 'success');
    }
    
    function bulkReleaseQuarantine() {
        var selectedIds = getSelectedQuarantineIds();
        if (selectedIds.length === 0) {
            showToast('Please select messages to release', 'warning');
            return;
        }
        
        selectedIds.forEach(function(msgId) {
            releaseQuarantinedMessage(msgId);
        });
        
        showToast(selectedIds.length + ' message(s) released', 'success');
    }
    
    function bulkBlockQuarantine() {
        var selectedIds = getSelectedQuarantineIds();
        if (selectedIds.length === 0) {
            showToast('Please select messages to block', 'warning');
            return;
        }
        
        selectedIds.forEach(function(msgId) {
            blockQuarantinedMessage(msgId);
        });
        
        showToast(selectedIds.length + ' message(s) blocked', 'success');
    }
    
    function getSelectedQuarantineIds() {
        var checkboxes = document.querySelectorAll('.quarantine-checkbox:checked');
        var ids = [];
        checkboxes.forEach(function(cb) {
            ids.push(cb.dataset.id);
        });
        return ids;
    }
    
    function setupQuarantineTabListeners() {
        document.getElementById('quarantine-filter-status').addEventListener('change', renderQuarantineTab);
        document.getElementById('quarantine-filter-rule').addEventListener('change', renderQuarantineTab);
        document.getElementById('quarantine-filter-url').addEventListener('change', renderQuarantineTab);
        document.getElementById('quarantine-filter-account').addEventListener('change', renderQuarantineTab);
        document.getElementById('quarantine-search').addEventListener('input', renderQuarantineTab);
    }

    function setupEventListeners() {
        document.getElementById('quarantine-select-all').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.quarantine-checkbox');
            checkboxes.forEach(function(cb) {
                cb.checked = this.checked;
            }.bind(this));
        });
        
        setupContentTabListeners();
        setupUrlTabListeners();
        setupQuarantineTabListeners();
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.action-menu-container')) {
                document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
                    menu.classList.remove('show');
                });
            }
        });
    }

    function deleteContentRuleById(ruleId) {
        var ruleIndex = mockData.contentRules.findIndex(function(r) { return r.id === ruleId; });
        if (ruleIndex === -1) return;
        
        var deletedRule = mockData.contentRules[ruleIndex];
        mockData.contentRules.splice(ruleIndex, 1);
        
        logAuditEvent('CONTENT_RULE_DELETED', {
            ruleId: ruleId,
            ruleName: deletedRule.name,
            deletedRule: deletedRule
        });
        
        showToast('Content rule deleted successfully', 'success');
    }
    
    function setupContentTabListeners() {
        document.getElementById('content-filter-status').addEventListener('change', renderContentTab);
        document.getElementById('content-filter-matchtype').addEventListener('change', renderContentTab);
        document.getElementById('content-filter-ruletype').addEventListener('change', renderContentTab);
        document.getElementById('content-search').addEventListener('input', renderContentTab);
        
        renderAntiSpamControls();
    }
    
    function renderAntiSpamControls() {
        var settings = mockData.antiSpamSettings;
        document.getElementById('antispam-repeat-toggle').checked = settings.preventRepeatContent;
        document.getElementById('antispam-window').value = settings.windowHours;
        document.getElementById('antispam-window').disabled = !settings.preventRepeatContent;
        
        var statusEl = document.getElementById('antispam-status');
        if (settings.preventRepeatContent) {
            statusEl.innerHTML = '<span class="badge bg-success" style="font-size: 0.75rem;"><i class="fas fa-toggle-on me-1"></i> Enabled (' + settings.windowHours + 'h window)</span>';
        } else {
            statusEl.innerHTML = '<span class="badge bg-secondary" style="font-size: 0.75rem;"><i class="fas fa-toggle-off me-1"></i> Disabled</span>';
        }
    }
    
    function toggleAntiSpamRepeat() {
        var enabled = document.getElementById('antispam-repeat-toggle').checked;
        mockData.antiSpamSettings.preventRepeatContent = enabled;
        mockData.antiSpamSettings.lastUpdated = formatDateTime(new Date());
        mockData.antiSpamSettings.updatedBy = currentAdmin.email;
        
        document.getElementById('antispam-window').disabled = !enabled;
        
        logAuditEvent('ANTISPAM_REPEAT_CONTENT_TOGGLED', {
            enabled: enabled,
            windowHours: mockData.antiSpamSettings.windowHours,
            admin: currentAdmin.email
        });
        
        if (window.MessageEnforcementService) {
            window.MessageEnforcementService.updateAntiSpamSettings({
                preventRepeatContent: enabled,
                windowHours: mockData.antiSpamSettings.windowHours
            });
        }
        
        renderAntiSpamControls();
        showToast(enabled ? 'Anti-spam repeat content protection enabled' : 'Anti-spam repeat content protection disabled', enabled ? 'success' : 'info');
    }
    
    function updateAntiSpamWindow() {
        var windowHours = parseInt(document.getElementById('antispam-window').value, 10);
        mockData.antiSpamSettings.windowHours = windowHours;
        mockData.antiSpamSettings.lastUpdated = formatDateTime(new Date());
        mockData.antiSpamSettings.updatedBy = currentAdmin.email;
        
        logAuditEvent('ANTISPAM_WINDOW_UPDATED', {
            windowHours: windowHours,
            admin: currentAdmin.email
        });
        
        if (window.MessageEnforcementService) {
            window.MessageEnforcementService.updateAntiSpamSettings({
                preventRepeatContent: mockData.antiSpamSettings.preventRepeatContent,
                windowHours: windowHours
            });
        }
        
        renderAntiSpamControls();
        showToast('Anti-spam window updated to ' + windowHours + ' hours', 'success');
    }

    return {
        initialize: initialize,
        renderAllTabs: renderAllTabs,
        showAddContentRuleModal: showAddContentRuleModal,
        editContentRule: editContentRule,
        viewContentRule: viewContentRule,
        toggleContentRuleStatus: toggleContentRuleStatus,
        deleteContentRule: deleteContentRule,
        deleteContentRuleById: deleteContentRuleById,
        saveContentRule: saveContentRule,
        updateContentMatchInputLabel: updateContentMatchInputLabel,
        resetContentFilters: resetContentFilters,
        toggleContentActionMenu: toggleContentActionMenu,
        setupContentTabListeners: setupContentTabListeners,
        toggleAntiSpamRepeat: toggleAntiSpamRepeat,
        updateAntiSpamWindow: updateAntiSpamWindow,
        renderAntiSpamControls: renderAntiSpamControls,
        showAddUrlRuleModal: showAddUrlRuleModal,
        editUrlRule: editUrlRule,
        viewUrlRule: viewUrlRule,
        toggleUrlRuleStatus: toggleUrlRuleStatus,
        deleteUrlRule: deleteUrlRule,
        deleteUrlRuleById: deleteUrlRuleById,
        saveUrlRule: saveUrlRule,
        updateUrlPatternLabel: updateUrlPatternLabel,
        resetUrlFilters: resetUrlFilters,
        toggleUrlActionMenu: toggleUrlActionMenu,
        setupUrlTabListeners: setupUrlTabListeners,
        saveDomainAgeSettings: saveDomainAgeSettings,
        showAddDomainAgeExceptionModal: showAddDomainAgeExceptionModal,
        saveException: saveException,
        removeDomainAgeException: removeDomainAgeException,
        viewQuarantinedMessage: viewQuarantinedMessage,
        releaseQuarantinedMessage: releaseQuarantinedMessage,
        blockQuarantinedMessage: blockQuarantinedMessage,
        bulkReleaseQuarantine: bulkReleaseQuarantine,
        bulkBlockQuarantine: bulkBlockQuarantine,
        setupQuarantineTabListeners: setupQuarantineTabListeners,
        addQuarantineNote: addQuarantineNote,
        addExceptionFromQuarantine: addExceptionFromQuarantine,
        createRuleFromQuarantine: createRuleFromQuarantine,
        releaseQuarantinedMessageFromModal: releaseQuarantinedMessageFromModal,
        blockQuarantinedMessageFromModal: blockQuarantinedMessageFromModal
    };
})();

function refreshAllControls() {
    console.log('[SecurityComplianceControls] Refreshing all controls...');
    SecurityComplianceControlsService.renderAllTabs();
}

function viewNormalisationRule(ruleId) {
    console.log('[NormalisationRules] View rule:', ruleId);
    // TODO: Implement view normalisation rule modal
    alert('View Normalisation Rule: ' + ruleId);
}

function editNormalisationRule(ruleId) {
    console.log('[NormalisationRules] Edit rule:', ruleId);
    // TODO: Implement edit normalisation rule modal
    alert('Edit Normalisation Rule: ' + ruleId);
}

function toggleNormalisationRuleStatus(ruleId, newStatus) {
    console.log('[NormalisationRules] Toggle rule status:', ruleId, '->', newStatus);
    logAuditEvent('NORMALISATION_RULE_STATUS_CHANGED', {
        ruleId: ruleId,
        newStatus: newStatus
    });
    // TODO: Implement API call to update status
    var rule = mockData.normalisationRules.find(function(r) { return r.id === ruleId; });
    if (rule) {
        rule.status = newStatus;
        MessageEnforcementService.hotReloadRules();
    }
    SecurityComplianceControlsService.renderAllTabs();
}

function testNormalisationRuleById(ruleId) {
    var rule = mockData.normalisationRules.find(function(r) { return r.id === ruleId; });
    if (rule) {
        showTestNormalisationModal(rule);
    }
}

function renderSubstitutionMapDisplay() {
    var container = document.getElementById('substitution-map-display');
    if (!container) return;
    
    var activeRules = mockData.normalisationRules.filter(function(r) { return r.status === 'active'; });
    var allMappings = {};
    
    activeRules.forEach(function(rule) {
        if (rule.mappings) {
            rule.mappings.forEach(function(m) {
                if (!allMappings[m.base]) {
                    allMappings[m.base] = { equivalents: [], sources: [] };
                }
                m.equivalents.forEach(function(eq) {
                    if (allMappings[m.base].equivalents.indexOf(eq) === -1) {
                        allMappings[m.base].equivalents.push(eq);
                    }
                });
                if (allMappings[m.base].sources.indexOf(rule.name) === -1) {
                    allMappings[m.base].sources.push(rule.name);
                }
            });
        }
    });
    
    var baseChars = Object.keys(allMappings).sort();
    var html = '';
    
    baseChars.slice(0, 12).forEach(function(base) {
        var mapping = allMappings[base];
        html += '<div class="col-md-3 col-sm-4 col-6 mb-2">' +
            '<div class="p-2 border rounded" style="background: #f8fafc; font-size: 0.8rem;">' +
                '<code style="background: #1e3a5f; color: white; padding: 2px 6px; border-radius: 3px;">' + base + '</code>' +
                ' <i class="fas fa-arrow-right text-muted mx-1" style="font-size: 0.7rem;"></i> ' +
                '<span>' + mapping.equivalents.slice(0, 4).join(', ') + (mapping.equivalents.length > 4 ? '...' : '') + '</span>' +
            '</div>' +
        '</div>';
    });
    
    if (baseChars.length > 12) {
        html += '<div class="col-12 text-muted" style="font-size: 0.75rem;"><i class="fas fa-info-circle me-1"></i>' + (baseChars.length - 12) + ' more base characters configured</div>';
    }
    
    container.innerHTML = html;
}

function exportSubstitutionMap() {
    var activeRules = mockData.normalisationRules.filter(function(r) { return r.status === 'active'; });
    var exportData = {
        exportedAt: new Date().toISOString(),
        version: '1.0',
        rules: activeRules.map(function(rule) {
            return {
                id: rule.id,
                name: rule.name,
                category: rule.category,
                scope: rule.scope,
                priority: rule.priority,
                mappings: rule.mappings
            };
        })
    };
    
    var blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'normalisation-rules-export-' + new Date().toISOString().split('T')[0] + '.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    logAuditEvent('NORMALISATION_RULES_EXPORTED', { ruleCount: activeRules.length });
}

function testNormalisationRule() {
    showTestNormalisationModal(null);
}

function showTestNormalisationModal(rule) {
    var modalHtml = '<div class="modal fade" id="testNormalisationModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-flask me-2"></i>Test Normalisation Rules</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Input Text</label>' +
                        '<input type="text" class="form-control" id="normTestInput" placeholder="Enter text to test normalisation..." value="' + (rule ? '' : 'BАNK0FENGLAND') + '">' +
                        '<small class="text-muted">Try typing text with homoglyphs or substitutions (e.g., "BАNK0FENGLAND" with Cyrillic A and zero)</small>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Test Against</label>' +
                        '<select class="form-control" id="normTestScope">' +
                            '<option value="all"' + (!rule ? ' selected' : '') + '>All Active Rules</option>' +
                            '<option value="senderid">SenderID Rules Only</option>' +
                            '<option value="content">Content Rules Only</option>' +
                            '<option value="url">URL Rules Only</option>' +
                            (rule ? '<option value="single" selected>This Rule Only (' + rule.name + ')</option>' : '') +
                        '</select>' +
                    '</div>' +
                    '<button class="btn btn-primary mb-3" onclick="runNormalisationTest(' + (rule ? rule.id : 'null') + ')" style="background: #1e3a5f; border-color: #1e3a5f;">' +
                        '<i class="fas fa-play me-1"></i>Run Test' +
                    '</button>' +
                    '<div id="normTestResults" style="display: none;">' +
                        '<hr>' +
                        '<h6><i class="fas fa-chart-bar me-2"></i>Results</h6>' +
                        '<div class="row">' +
                            '<div class="col-md-6">' +
                                '<div class="card border-0" style="background: #f8fafc;">' +
                                    '<div class="card-body">' +
                                        '<strong>Original:</strong><br>' +
                                        '<code id="normTestOriginal" style="font-size: 1.1rem;"></code>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-6">' +
                                '<div class="card border-0" style="background: #e8f4fd;">' +
                                    '<div class="card-body">' +
                                        '<strong>Normalised:</strong><br>' +
                                        '<code id="normTestNormalised" style="font-size: 1.1rem; color: #1e3a5f;"></code>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="mt-3">' +
                            '<strong>Transformations Applied:</strong>' +
                            '<ul id="normTestTransformations" class="mt-2 mb-0" style="font-size: 0.85rem;"></ul>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<small class="text-muted me-auto"><i class="fas fa-info-circle me-1"></i>Tests run against the active normalisation library</small>' +
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('testNormalisationModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('testNormalisationModal'));
    modal.show();
}

function runNormalisationTest(specificRuleId) {
    var input = document.getElementById('normTestInput').value;
    var scope = document.getElementById('normTestScope').value;
    
    if (!input) {
        alert('Please enter text to test');
        return;
    }
    
    var result = NormalisationLibrary.normalise(input, scope === 'single' ? { ruleId: specificRuleId } : { scope: scope });
    
    document.getElementById('normTestOriginal').textContent = input;
    document.getElementById('normTestNormalised').textContent = result.normalised;
    
    var transformationsHtml = '';
    if (result.transformations && result.transformations.length > 0) {
        result.transformations.forEach(function(t) {
            transformationsHtml += '<li><code>' + t.original + '</code> → <code>' + t.replacement + '</code> <span class="text-muted">(' + t.ruleName + ')</span></li>';
        });
    } else {
        transformationsHtml = '<li class="text-muted">No transformations applied</li>';
    }
    document.getElementById('normTestTransformations').innerHTML = transformationsHtml;
    
    document.getElementById('normTestResults').style.display = 'block';
    
    logAuditEvent('NORMALISATION_TEST_RUN', { input: input, output: result.normalised, scope: scope });
}

function showAddNormRuleModal() {
    var modalHtml = '<div class="modal fade" id="addNormRuleModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Normalisation Rule</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div class="row">' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="form-label fw-bold">Rule Name <span class="text-danger">*</span></label>' +
                            '<input type="text" class="form-control" id="newNormRuleName" placeholder="e.g., Custom Homoglyphs">' +
                        '</div>' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="form-label fw-bold">Category <span class="text-danger">*</span></label>' +
                            '<select class="form-control" id="newNormRuleCategory">' +
                                '<option value="">Select category...</option>' +
                                '<option value="substitution">Character Substitution</option>' +
                                '<option value="homoglyph">Homoglyph Detection</option>' +
                                '<option value="unicode">Unicode Normalisation</option>' +
                                '<option value="case">Case Folding</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Description</label>' +
                        '<textarea class="form-control" id="newNormRuleDescription" rows="2" placeholder="Describe what this rule does..."></textarea>' +
                    '</div>' +
                    '<div class="row">' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="form-label fw-bold">Scope <span class="text-danger">*</span></label>' +
                            '<select class="form-control" id="newNormRuleScope">' +
                                '<option value="all">All Engines</option>' +
                                '<option value="senderid">SenderID Only</option>' +
                                '<option value="content">Content Only</option>' +
                                '<option value="url">URL Only (Guarded)</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="form-label fw-bold">Priority</label>' +
                            '<input type="number" class="form-control" id="newNormRulePriority" value="10" min="0" max="100">' +
                            '<small class="text-muted">Lower = higher priority (0-100)</small>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Character Mappings <span class="text-danger">*</span></label>' +
                        '<div id="normMappingsContainer">' +
                            '<div class="mapping-row d-flex gap-2 mb-2">' +
                                '<input type="text" class="form-control" placeholder="Base char" style="width: 100px;">' +
                                '<span class="align-self-center">→</span>' +
                                '<input type="text" class="form-control" placeholder="Equivalents (comma-separated)">' +
                                '<button class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>' +
                            '</div>' +
                        '</div>' +
                        '<button class="btn btn-outline-secondary btn-sm mt-1" onclick="addMappingRow()">' +
                            '<i class="fas fa-plus me-1"></i>Add Mapping' +
                        '</button>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<small class="text-muted me-auto"><i class="fas fa-shield-alt me-1"></i>Changes require admin approval</small>' +
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn btn-primary" onclick="saveNewNormRule()" style="background: #1e3a5f; border-color: #1e3a5f;">' +
                        '<i class="fas fa-save me-1"></i>Save Rule' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('addNormRuleModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('addNormRuleModal'));
    modal.show();
}

function addMappingRow() {
    var container = document.getElementById('normMappingsContainer');
    var rowHtml = '<div class="mapping-row d-flex gap-2 mb-2">' +
        '<input type="text" class="form-control" placeholder="Base char" style="width: 100px;">' +
        '<span class="align-self-center">→</span>' +
        '<input type="text" class="form-control" placeholder="Equivalents (comma-separated)">' +
        '<button class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>' +
    '</div>';
    container.insertAdjacentHTML('beforeend', rowHtml);
}

function saveNewNormRule() {
    var name = document.getElementById('newNormRuleName').value.trim();
    var category = document.getElementById('newNormRuleCategory').value;
    var description = document.getElementById('newNormRuleDescription').value.trim();
    var scope = document.getElementById('newNormRuleScope').value;
    var priority = parseInt(document.getElementById('newNormRulePriority').value) || 10;
    
    if (!name || !category) {
        alert('Please fill in required fields');
        return;
    }
    
    var mappings = [];
    document.querySelectorAll('#normMappingsContainer .mapping-row').forEach(function(row) {
        var inputs = row.querySelectorAll('input');
        var base = inputs[0].value.trim();
        var equivalentsStr = inputs[1].value.trim();
        if (base && equivalentsStr) {
            mappings.push({
                base: base,
                equivalents: equivalentsStr.split(',').map(function(e) { return e.trim(); }).filter(function(e) { return e; })
            });
        }
    });
    
    if (mappings.length === 0) {
        alert('Please add at least one character mapping');
        return;
    }
    
    var scopeLabels = {
        'all': 'All Engines',
        'senderid': 'SenderID Only',
        'content': 'Content Only',
        'url': 'URL Only'
    };
    
    var newRule = {
        id: mockData.normalisationRules.length + 1,
        name: name,
        category: category,
        description: description,
        mappings: mappings,
        scope: scope,
        scopeLabel: scopeLabels[scope] || scope,
        priority: priority,
        status: 'active',
        createdAt: new Date().toLocaleDateString('en-GB').replace(/\//g, '-'),
        updatedAt: new Date().toLocaleDateString('en-GB').replace(/\//g, '-'),
        createdBy: 'admin@quicksms.co.uk'
    };
    
    mockData.normalisationRules.push(newRule);
    MessageEnforcementService.hotReloadRules();
    
    logAuditEvent('NORMALISATION_RULE_CREATED', { ruleId: newRule.id, ruleName: newRule.name });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('addNormRuleModal'));
    modal.hide();
    
    SecurityComplianceControlsService.renderAllTabs();
}

function resetNormFilters() {
    document.getElementById('norm-filter-status').value = '';
    document.getElementById('norm-filter-category').value = '';
    document.getElementById('norm-filter-scope').value = '';
    document.getElementById('norm-search').value = '';
    SecurityComplianceControlsService.renderAllTabs();
}

var NormalisationLibrary = (function() {
    function normalise(input, options) {
        options = options || {};
        var scope = options.scope || 'all';
        var ruleId = options.ruleId || null;
        
        var rules = mockData.normalisationRules.filter(function(r) {
            if (r.status !== 'active') return false;
            if (ruleId !== null) return r.id === ruleId;
            if (scope === 'all') return true;
            return r.scope === scope || r.scope === 'all';
        });
        
        rules.sort(function(a, b) {
            return a.priority - b.priority;
        });
        
        var result = input;
        var transformations = [];
        
        rules.forEach(function(rule) {
            if (rule.mappings) {
                rule.mappings.forEach(function(mapping) {
                    mapping.equivalents.forEach(function(eq) {
                        if (result.indexOf(eq) !== -1) {
                            var regex = new RegExp(escapeRegex(eq), 'g');
                            var beforeLen = result.length;
                            result = result.replace(regex, mapping.base);
                            if (result.length !== beforeLen || result !== input) {
                                transformations.push({
                                    original: eq,
                                    replacement: mapping.base,
                                    ruleName: rule.name,
                                    ruleId: rule.id
                                });
                            }
                        }
                    });
                });
            }
        });
        
        return {
            original: input,
            normalised: result,
            transformations: transformations,
            rulesApplied: rules.map(function(r) { return r.id; })
        };
    }
    
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    function getActiveRules(scope) {
        return mockData.normalisationRules.filter(function(r) {
            if (r.status !== 'active') return false;
            if (!scope || scope === 'all') return true;
            return r.scope === scope || r.scope === 'all';
        });
    }
    
    function getMappingCount() {
        return mockData.normalisationRules.reduce(function(sum, rule) {
            return sum + (rule.mappings ? rule.mappings.length : 0);
        }, 0);
    }
    
    return {
        normalise: normalise,
        getActiveRules: getActiveRules,
        getMappingCount: getMappingCount
    };
})();

var SenderIdMatchingService = (function() {
    var SUBSTITUTION_MAP = {
        '0': ['O', 'o'],
        'O': ['0'],
        'o': ['0'],
        '1': ['I', 'i', 'L', 'l', '|'],
        'I': ['1', 'l', '|'],
        'i': ['1', 'L', 'l', '|'],
        'L': ['1', 'I', 'i', '|'],
        'l': ['1', 'I', 'i', '|'],
        '5': ['S', 's'],
        'S': ['5'],
        's': ['5'],
        '3': ['E', 'e'],
        'E': ['3'],
        'e': ['3'],
        '4': ['A', 'a'],
        'A': ['4'],
        'a': ['4'],
        '8': ['B', 'b'],
        'B': ['8'],
        'b': ['8'],
        '6': ['G', 'g'],
        'G': ['6'],
        'g': ['6'],
        '7': ['T', 't'],
        'T': ['7'],
        't': ['7']
    };

    function normalise(senderId) {
        if (!senderId) return '';
        return senderId.toUpperCase().replace(/[\s\-_\.]/g, '');
    }

    function generateVariants(baseSenderId) {
        var normalised = normalise(baseSenderId);
        var variants = [normalised];
        
        for (var i = 0; i < normalised.length; i++) {
            var char = normalised[i];
            if (SUBSTITUTION_MAP[char]) {
                var subs = SUBSTITUTION_MAP[char];
                var newVariants = [];
                variants.forEach(function(v) {
                    subs.forEach(function(sub) {
                        var newVariant = v.substring(0, i) + sub.toUpperCase() + v.substring(i + 1);
                        if (newVariants.indexOf(newVariant) === -1) {
                            newVariants.push(newVariant);
                        }
                    });
                });
                variants = variants.concat(newVariants);
            }
        }
        
        return [...new Set(variants)];
    }

    function matches(inputSenderId, baseSenderId, applyNormalisation) {
        var normalisedInput = normalise(inputSenderId);
        var normalisedBase = normalise(baseSenderId);
        
        if (normalisedInput === normalisedBase) {
            return { matched: true, reason: 'exact_match', variant: normalisedBase };
        }
        
        if (applyNormalisation) {
            var variants = generateVariants(normalisedBase);
            for (var i = 0; i < variants.length; i++) {
                if (normalisedInput === variants[i]) {
                    return { matched: true, reason: 'variant_match', variant: variants[i] };
                }
            }
        }
        
        return { matched: false, reason: null, variant: null };
    }

    function buildRegexPattern(baseSenderId) {
        var normalised = normalise(baseSenderId);
        var pattern = '';
        
        for (var i = 0; i < normalised.length; i++) {
            var char = normalised[i];
            if (SUBSTITUTION_MAP[char]) {
                var allChars = [char].concat(SUBSTITUTION_MAP[char]);
                pattern += '[' + allChars.join('') + ']';
            } else {
                pattern += char;
            }
        }
        
        return new RegExp('^' + pattern + '$', 'i');
    }

    return {
        normalise: normalise,
        generateVariants: generateVariants,
        matches: matches,
        buildRegexPattern: buildRegexPattern
    };
})();

window.SenderIdMatchingService = SenderIdMatchingService;

var senderIdRulesStore = [];

function showAddSenderIdRuleModal() {
    document.getElementById('senderid-modal-title').textContent = 'Add SenderID Rule';
    document.getElementById('senderid-save-btn-text').textContent = 'Save Rule';
    document.getElementById('senderid-rule-id').value = '';
    document.getElementById('senderid-rule-name').value = '';
    document.getElementById('senderid-base-value').value = '';
    document.getElementById('senderid-type-block').checked = true;
    document.getElementById('senderid-category').value = '';
    document.getElementById('senderid-apply-normalisation').checked = true;
    
    var modal = new bootstrap.Modal(document.getElementById('senderIdRuleModal'));
    modal.show();
}

function editSenderIdRule(ruleId) {
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var rule = rules.find(r => r.id === ruleId);
    if (!rule) {
        console.error('Rule not found:', ruleId);
        return;
    }
    
    document.getElementById('senderid-modal-title').textContent = 'Edit SenderID Rule';
    document.getElementById('senderid-save-btn-text').textContent = 'Update Rule';
    document.getElementById('senderid-rule-id').value = rule.id;
    document.getElementById('senderid-rule-name').value = rule.name;
    document.getElementById('senderid-base-value').value = rule.baseSenderId;
    document.getElementById('senderid-type-' + rule.ruleType).checked = true;
    document.getElementById('senderid-category').value = rule.category;
    document.getElementById('senderid-apply-normalisation').checked = rule.applyNormalisation;
    
    var modal = new bootstrap.Modal(document.getElementById('senderIdRuleModal'));
    modal.show();
}

function viewSenderIdRule(ruleId) {
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var rule = rules.find(r => r.id === ruleId);
    if (!rule) return;
    
    var categoryLabels = {
        'bank_impersonation': 'Bank Impersonation',
        'government': 'Government',
        'lottery_prize': 'Lottery/Prize',
        'brand_abuse': 'Brand Abuse',
        'premium_rate': 'Premium Rate',
        'other': 'Other'
    };
    
    var variants = SenderIdMatchingService.generateVariants(rule.baseSenderId);
    
    var html = '<div class="mb-3"><strong style="color: #1e3a5f;">Rule Details</strong></div>' +
        '<table class="table table-sm">' +
        '<tr><td class="text-muted" style="width: 40%;">Rule ID</td><td>' + rule.id + '</td></tr>' +
        '<tr><td class="text-muted">Rule Name</td><td>' + rule.name + '</td></tr>' +
        '<tr><td class="text-muted">Base SenderID</td><td><code>' + rule.baseSenderId + '</code></td></tr>' +
        '<tr><td class="text-muted">Rule Type</td><td>' + (rule.ruleType === 'block' ? '<span class="badge bg-danger">Block</span>' : '<span class="badge bg-warning text-dark">Flag</span>') + '</td></tr>' +
        '<tr><td class="text-muted">Category</td><td>' + (categoryLabels[rule.category] || rule.category) + '</td></tr>' +
        '<tr><td class="text-muted">Normalisation</td><td>' + (rule.applyNormalisation ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>') + '</td></tr>' +
        '<tr><td class="text-muted">Status</td><td>' + (rule.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Disabled</span>') + '</td></tr>' +
        '<tr><td class="text-muted">Created By</td><td>' + rule.createdBy + '</td></tr>' +
        '<tr><td class="text-muted">Created At</td><td>' + rule.createdAt + '</td></tr>' +
        '<tr><td class="text-muted">Last Updated</td><td>' + rule.updatedAt + '</td></tr>' +
        '</table>' +
        '<div class="mt-3 p-2 rounded" style="background: #f8f9fc; border: 1px solid #e9ecef;">' +
        '<small class="text-muted d-block mb-1"><strong>Detected Variants (' + variants.length + ')</strong></small>' +
        '<div style="font-size: 0.75rem; max-height: 80px; overflow-y: auto;">' +
        variants.slice(0, 20).map(function(v) { return '<code class="me-1 mb-1 d-inline-block" style="background: #e9ecef; padding: 0.1rem 0.3rem; border-radius: 3px;">' + v + '</code>'; }).join('') +
        (variants.length > 20 ? '<span class="text-muted">... and ' + (variants.length - 20) + ' more</span>' : '') +
        '</div></div>';
    
    document.getElementById('senderid-view-content').innerHTML = html;
    var modal = new bootstrap.Modal(document.getElementById('senderIdViewModal'));
    modal.show();
}

function saveSenderIdRule() {
    var ruleId = document.getElementById('senderid-rule-id').value;
    var name = document.getElementById('senderid-rule-name').value.trim();
    var baseSenderId = document.getElementById('senderid-base-value').value.trim().toUpperCase();
    var ruleType = document.querySelector('input[name="senderid-rule-type"]:checked').value;
    var category = document.getElementById('senderid-category').value;
    var applyNormalisation = document.getElementById('senderid-apply-normalisation').checked;
    
    if (!name || !baseSenderId || !category) {
        alert('Please fill in all required fields.');
        return;
    }
    
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var now = new Date();
    var timestamp = now.toLocaleDateString('en-GB').replace(/\//g, '-') + ' ' + now.toTimeString().slice(0, 5);
    var beforeState = null;
    var isEdit = !!ruleId;
    
    if (isEdit) {
        var existingIndex = rules.findIndex(r => r.id === ruleId);
        if (existingIndex !== -1) {
            beforeState = JSON.parse(JSON.stringify(rules[existingIndex]));
            rules[existingIndex] = {
                ...rules[existingIndex],
                name: name,
                baseSenderId: baseSenderId,
                ruleType: ruleType,
                category: category,
                applyNormalisation: applyNormalisation,
                updatedAt: timestamp
            };
        }
    } else {
        var newId = 'SID-' + String(rules.length + 1).padStart(3, '0');
        rules.push({
            id: newId,
            name: name,
            baseSenderId: baseSenderId,
            ruleType: ruleType,
            category: category,
            applyNormalisation: applyNormalisation,
            status: 'active',
            createdBy: currentAdmin.email,
            createdAt: timestamp,
            updatedAt: timestamp
        });
        ruleId = newId;
    }
    
    localStorage.setItem('senderIdRules', JSON.stringify(rules));
    
    logAuditEvent(isEdit ? 'SENDERID_RULE_UPDATED' : 'SENDERID_RULE_CREATED', {
        ruleId: ruleId,
        ruleName: name,
        baseSenderId: baseSenderId,
        ruleType: ruleType,
        category: category,
        applyNormalisation: applyNormalisation,
        before: beforeState,
        after: { name: name, baseSenderId: baseSenderId, ruleType: ruleType, category: category, applyNormalisation: applyNormalisation },
        entityType: 'senderid_rule'
    });
    
    bootstrap.Modal.getInstance(document.getElementById('senderIdRuleModal')).hide();
    SecurityComplianceControlsService.renderAllTabs();
    
    console.log('[SenderIdControls] Rule saved:', ruleId);
}

function toggleSenderIdRuleStatus(ruleId, newStatus) {
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var ruleIndex = rules.findIndex(r => r.id === ruleId);
    if (ruleIndex !== -1) {
        var beforeStatus = rules[ruleIndex].status;
        rules[ruleIndex].status = newStatus;
        rules[ruleIndex].updatedAt = new Date().toLocaleDateString('en-GB').replace(/\//g, '-') + ' ' + new Date().toTimeString().slice(0, 5);
        localStorage.setItem('senderIdRules', JSON.stringify(rules));
        
        logAuditEvent('SENDERID_RULE_STATUS_CHANGED', {
            ruleId: ruleId,
            ruleName: rules[ruleIndex].name,
            beforeStatus: beforeStatus,
            afterStatus: newStatus,
            entityType: 'senderid_rule'
        });
        
        SecurityComplianceControlsService.renderAllTabs();
    }
}

function showDeleteConfirmation(ruleId, ruleType) {
    document.getElementById('delete-rule-id').value = ruleId;
    document.getElementById('delete-rule-type').value = ruleType;
    document.getElementById('confirm-delete-message').textContent = 'Are you sure you want to delete rule ' + ruleId + '?';
    var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
}

function confirmDeleteRule() {
    var ruleId = document.getElementById('delete-rule-id').value;
    var ruleType = document.getElementById('delete-rule-type').value;
    
    if (currentAdmin.role !== 'super_admin') {
        alert('Only Super Admins can delete rules.');
        return;
    }
    
    if (ruleType === 'senderid') {
        var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
        var deletedRule = rules.find(r => r.id === ruleId);
        rules = rules.filter(r => r.id !== ruleId);
        localStorage.setItem('senderIdRules', JSON.stringify(rules));
        
        logAuditEvent('SENDERID_RULE_DELETED', {
            ruleId: ruleId,
            ruleName: deletedRule ? deletedRule.name : null,
            before: deletedRule,
            after: null,
            entityType: 'senderid_rule'
        });
    } else if (ruleType === 'content') {
        SecurityComplianceControlsService.deleteContentRuleById(ruleId);
    } else if (ruleType === 'url') {
        SecurityComplianceControlsService.deleteUrlRuleById(ruleId);
    }
    
    bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
    SecurityComplianceControlsService.renderAllTabs();
}

function showAddContentRuleModal() {
    SecurityComplianceControlsService.showAddContentRuleModal();
}

function editContentRule(ruleId) {
    SecurityComplianceControlsService.editContentRule(ruleId);
}

function viewContentRule(ruleId) {
    SecurityComplianceControlsService.viewContentRule(ruleId);
}

function toggleContentRuleStatus(ruleId) {
    SecurityComplianceControlsService.toggleContentRuleStatus(ruleId);
}

function deleteContentRule(ruleId) {
    SecurityComplianceControlsService.deleteContentRule(ruleId);
}

function saveContentRule() {
    SecurityComplianceControlsService.saveContentRule();
}

function updateContentMatchInputLabel() {
    SecurityComplianceControlsService.updateContentMatchInputLabel();
}

function resetContentFilters() {
    SecurityComplianceControlsService.resetContentFilters();
}

function toggleContentActionMenu(btn, ruleId) {
    SecurityComplianceControlsService.toggleContentActionMenu(btn, ruleId);
}

function showAddUrlRuleModal() {
    SecurityComplianceControlsService.showAddUrlRuleModal();
}

function editUrlRule(ruleId) {
    SecurityComplianceControlsService.editUrlRule(ruleId);
}

function viewUrlRule(ruleId) {
    SecurityComplianceControlsService.viewUrlRule(ruleId);
}

function toggleUrlRuleStatus(ruleId) {
    SecurityComplianceControlsService.toggleUrlRuleStatus(ruleId);
}

function deleteUrlRule(ruleId) {
    SecurityComplianceControlsService.deleteUrlRule(ruleId);
}

function saveUrlRule() {
    SecurityComplianceControlsService.saveUrlRule();
}

function updateUrlPatternLabel() {
    SecurityComplianceControlsService.updateUrlPatternLabel();
}

function resetUrlFilters() {
    SecurityComplianceControlsService.resetUrlFilters();
}

function toggleUrlActionMenu(btn, ruleId) {
    SecurityComplianceControlsService.toggleUrlActionMenu(btn, ruleId);
}

function saveDomainAgeSettings() {
    SecurityComplianceControlsService.saveDomainAgeSettings();
}

function showAddDomainAgeExceptionModal() {
    SecurityComplianceControlsService.showAddDomainAgeExceptionModal();
}

function saveException() {
    SecurityComplianceControlsService.saveException();
}

function removeDomainAgeException(exceptionId) {
    SecurityComplianceControlsService.removeDomainAgeException(exceptionId);
}

function showAddNormRuleModal() {
    console.log('[SecurityComplianceControls] TODO: Implement Add Normalisation Rule modal');
    alert('Add Normalisation Rule - Coming Soon');
}

function resetSenderIdFilters() {
    document.getElementById('senderid-filter-status').value = '';
    document.getElementById('senderid-filter-type').value = '';
    document.getElementById('senderid-filter-category').value = '';
    document.getElementById('senderid-search').value = '';
}

function resetContentFilters() {
    document.getElementById('content-filter-status').value = '';
    document.getElementById('content-filter-category').value = '';
    document.getElementById('content-search').value = '';
}

function resetUrlFilters() {
    document.getElementById('url-filter-type').value = '';
    document.getElementById('url-filter-category').value = '';
    document.getElementById('url-search').value = '';
}

function resetNormFilters() {
    document.getElementById('norm-filter-status').value = '';
    document.getElementById('norm-filter-type').value = '';
    document.getElementById('norm-search').value = '';
}

function bulkReleaseQuarantine() {
    var selected = document.querySelectorAll('.quarantine-checkbox:checked');
    if (selected.length === 0) {
        alert('Please select messages to release.');
        return;
    }
    console.log('[SecurityComplianceControls] TODO: Implement bulk release');
    alert('Bulk release ' + selected.length + ' messages - Coming Soon');
}

function viewQuarantinedMessage(msgId) {
    SecurityComplianceControlsService.viewQuarantinedMessage(msgId);
}

function releaseQuarantinedMessage(msgId) {
    SecurityComplianceControlsService.releaseQuarantinedMessage(msgId);
}

function blockQuarantinedMessage(msgId) {
    SecurityComplianceControlsService.blockQuarantinedMessage(msgId);
}

function bulkReleaseQuarantine() {
    SecurityComplianceControlsService.bulkReleaseQuarantine();
}

function bulkBlockQuarantine() {
    SecurityComplianceControlsService.bulkBlockQuarantine();
}

function bulkRejectQuarantine() {
    SecurityComplianceControlsService.bulkBlockQuarantine();
}

function addQuarantineNote() {
    SecurityComplianceControlsService.addQuarantineNote();
}

function addExceptionFromQuarantine() {
    SecurityComplianceControlsService.addExceptionFromQuarantine();
}

function createRuleFromQuarantine() {
    SecurityComplianceControlsService.createRuleFromQuarantine();
}

function releaseQuarantinedMessageFromModal() {
    SecurityComplianceControlsService.releaseQuarantinedMessageFromModal();
}

function blockQuarantinedMessageFromModal() {
    SecurityComplianceControlsService.blockQuarantinedMessageFromModal();
}

function toggleAntiSpamRepeat() {
    SecurityComplianceControlsService.toggleAntiSpamRepeat();
}

function updateAntiSpamWindow() {
    SecurityComplianceControlsService.updateAntiSpamWindow();
}

document.addEventListener('DOMContentLoaded', function() {
    SecurityComplianceControlsService.initialize();
});
</script>
@endpush

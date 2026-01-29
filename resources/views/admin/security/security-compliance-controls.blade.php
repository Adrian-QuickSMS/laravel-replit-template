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
                    <p>Configure global rules for phone number normalisation, character encoding, and message formatting across all accounts.</p>
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
                        <div class="sec-stat-value" id="norm-draft-count">0</div>
                        <div class="sec-stat-label">Draft</div>
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
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Rule Type</label>
                            <select id="norm-filter-type">
                                <option value="">All Types</option>
                                <option value="phone">Phone Number</option>
                                <option value="encoding">Encoding</option>
                                <option value="format">Format</option>
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
                        <h6>Normalisation Rules</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search rules..." id="norm-search">
                        </div>
                    </div>
                    <table class="sec-table" id="norm-rules-table">
                        <thead>
                            <tr>
                                <th>Rule Name <i class="fas fa-sort"></i></th>
                                <th>Type <i class="fas fa-sort"></i></th>
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
                        <p>Create global normalisation rules for consistent message handling.</p>
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
                        <div class="sec-stat-value" id="quarantine-pending-count">12</div>
                        <div class="sec-stat-label">Awaiting Review</div>
                    </div>
                    <div class="sec-stat-card active">
                        <div class="sec-stat-value" id="quarantine-released-count">0</div>
                        <div class="sec-stat-label">Released Today</div>
                    </div>
                    <div class="sec-stat-card blocked">
                        <div class="sec-stat-value" id="quarantine-rejected-count">0</div>
                        <div class="sec-stat-label">Rejected Today</div>
                    </div>
                    <div class="sec-stat-card total">
                        <div class="sec-stat-value" id="quarantine-total-count">0</div>
                        <div class="sec-stat-label">Total Reviewed</div>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-filter-row">
                        <div class="sec-filter-group">
                            <label>Status</label>
                            <select id="quarantine-filter-status">
                                <option value="pending">Pending Review</option>
                                <option value="">All Statuses</option>
                                <option value="released">Released</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Reason</label>
                            <select id="quarantine-filter-reason">
                                <option value="">All Reasons</option>
                                <option value="keyword">Keyword Match</option>
                                <option value="url">Suspicious URL</option>
                                <option value="pattern">Pattern Match</option>
                                <option value="manual">Manual Hold</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Customer</label>
                            <select id="quarantine-filter-customer">
                                <option value="">All Customers</option>
                            </select>
                        </div>
                        <div class="sec-filter-actions">
                            <button class="sec-btn-primary" onclick="bulkReleaseQuarantine()">
                                <i class="fas fa-check"></i> Release Selected
                            </button>
                            <button class="sec-btn-outline" onclick="bulkRejectQuarantine()">
                                <i class="fas fa-times"></i> Reject Selected
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>Quarantined Messages</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search messages..." id="quarantine-search">
                        </div>
                    </div>
                    <table class="sec-table" id="quarantine-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="quarantine-select-all"></th>
                                <th>Customer <i class="fas fa-sort"></i></th>
                                <th>Message Preview <i class="fas fa-sort"></i></th>
                                <th>Reason <i class="fas fa-sort"></i></th>
                                <th>Flagged At <i class="fas fa-sort"></i></th>
                                <th>Status <i class="fas fa-sort"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="quarantine-body">
                        </tbody>
                    </table>
                    <div class="sec-empty-state" id="quarantine-empty-state" style="display: none;">
                        <i class="fas fa-check-circle"></i>
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
@endsection

@push('scripts')
@include('shared.services.message-enforcement-service')
<script>
var currentAdmin = {
    id: 'admin-001',
    email: 'admin@quicksms.co.uk',
    role: 'super_admin'
};

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
            { id: 1, name: 'UK Number Format', type: 'phone', scope: 'Global', priority: 1, status: 'active' },
            { id: 2, name: 'UTF-8 Encoding', type: 'encoding', scope: 'Global', priority: 2, status: 'active' },
            { id: 3, name: 'GSM Character Set', type: 'format', scope: 'Global', priority: 3, status: 'active' }
        ];

        mockData.quarantinedMessages = [
            { id: 1, customer: 'TechStart Ltd', accountId: 'ACC-10045', preview: 'Congratulations! You have won...', reason: 'keyword', flaggedAt: '29-01-2026 10:15', status: 'pending' },
            { id: 2, customer: 'HealthFirst UK', accountId: 'ACC-10089', preview: 'Click here to verify your account...', reason: 'url', flaggedAt: '29-01-2026 09:45', status: 'pending' },
            { id: 3, customer: 'E-Commerce Hub', accountId: 'ACC-10112', preview: 'Limited time offer! Free casino...', reason: 'pattern', flaggedAt: '29-01-2026 08:30', status: 'pending' }
        ];
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

        document.getElementById('norm-active-count').textContent = rules.filter(r => r.status === 'active').length;
        document.getElementById('norm-disabled-count').textContent = rules.filter(r => r.status === 'disabled').length;
        document.getElementById('norm-draft-count').textContent = rules.filter(r => r.status === 'draft').length;
        document.getElementById('norm-total-count').textContent = rules.length;

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            return '<tr>' +
                '<td><strong>' + rule.name + '</strong></td>' +
                '<td>' + rule.type.charAt(0).toUpperCase() + rule.type.slice(1) + '</td>' +
                '<td>' + rule.scope + '</td>' +
                '<td>' + rule.priority + '</td>' +
                '<td><span class="sec-status-badge ' + rule.status + '">' + rule.status.charAt(0).toUpperCase() + rule.status.slice(1) + '</span></td>' +
                '<td><button class="action-menu-btn"><i class="fas fa-ellipsis-v"></i></button></td>' +
                '</tr>';
        }).join('');
    }

    function renderQuarantineTab() {
        var tbody = document.getElementById('quarantine-body');
        var emptyState = document.getElementById('quarantine-empty-state');
        var messages = mockData.quarantinedMessages;

        document.getElementById('quarantine-pending-count').textContent = messages.filter(m => m.status === 'pending').length;
        document.getElementById('quarantine-released-count').textContent = messages.filter(m => m.status === 'released').length;
        document.getElementById('quarantine-rejected-count').textContent = messages.filter(m => m.status === 'rejected').length;
        document.getElementById('quarantine-total-count').textContent = messages.length;

        if (messages.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = messages.map(function(msg) {
            var reasonLabels = { keyword: 'Keyword Match', url: 'Suspicious URL', pattern: 'Pattern Match', manual: 'Manual Hold' };
            return '<tr>' +
                '<td><input type="checkbox" class="quarantine-checkbox" data-id="' + msg.id + '"></td>' +
                '<td><strong>' + msg.customer + '</strong><br><small class="text-muted">' + msg.accountId + '</small></td>' +
                '<td>' + msg.preview + '</td>' +
                '<td><span class="badge bg-warning text-dark">' + (reasonLabels[msg.reason] || msg.reason) + '</span></td>' +
                '<td>' + msg.flaggedAt + '</td>' +
                '<td><span class="sec-status-badge ' + msg.status + '">' + msg.status.charAt(0).toUpperCase() + msg.status.slice(1) + '</span></td>' +
                '<td>' +
                    '<button class="sec-btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; margin-right: 0.25rem;" onclick="reviewQuarantinedMessage(' + msg.id + ')"><i class="fas fa-eye"></i></button>' +
                    '<button class="action-menu-btn"><i class="fas fa-ellipsis-v"></i></button>' +
                '</td>' +
                '</tr>';
        }).join('');
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
        removeDomainAgeException: removeDomainAgeException
    };
})();

function refreshAllControls() {
    console.log('[SecurityComplianceControls] Refreshing all controls...');
    SecurityComplianceControlsService.renderAllTabs();
}

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
    
    var auditEvent = {
        eventType: isEdit ? 'SENDERID_RULE_UPDATED' : 'SENDERID_RULE_CREATED',
        timestamp: new Date().toISOString(),
        adminActor: { id: currentAdmin.id, email: currentAdmin.email, role: currentAdmin.role },
        ruleId: ruleId,
        beforeState: beforeState,
        afterState: { name, baseSenderId, ruleType, category, applyNormalisation }
    };
    console.log('[SecurityComplianceAudit]', JSON.stringify(auditEvent));
    
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
        
        console.log('[SecurityComplianceAudit]', JSON.stringify({
            eventType: 'SENDERID_RULE_STATUS_CHANGED',
            timestamp: new Date().toISOString(),
            adminActor: { id: currentAdmin.id, email: currentAdmin.email },
            ruleId: ruleId,
            beforeStatus: beforeStatus,
            afterStatus: newStatus
        }));
        
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
        
        console.log('[SecurityComplianceAudit]', JSON.stringify({
            eventType: 'SENDERID_RULE_DELETED',
            timestamp: new Date().toISOString(),
            adminActor: { id: currentAdmin.id, email: currentAdmin.email, role: currentAdmin.role },
            ruleId: ruleId,
            deletedRule: deletedRule
        }));
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

function bulkRejectQuarantine() {
    var selected = document.querySelectorAll('.quarantine-checkbox:checked');
    if (selected.length === 0) {
        alert('Please select messages to reject.');
        return;
    }
    console.log('[SecurityComplianceControls] TODO: Implement bulk reject');
    alert('Bulk reject ' + selected.length + ' messages - Coming Soon');
}

function reviewQuarantinedMessage(id) {
    console.log('[SecurityComplianceControls] TODO: Implement review modal for message', id);
    alert('Review message #' + id + ' - Coming Soon');
}

document.addEventListener('DOMContentLoaded', function() {
    SecurityComplianceControlsService.initialize();
});
</script>
@endpush

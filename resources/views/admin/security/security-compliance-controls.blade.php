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
                                <option value="blocked">Blocked</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Rule Type</label>
                            <select id="senderid-filter-type">
                                <option value="">All Types</option>
                                <option value="exact">Exact Match</option>
                                <option value="pattern">Pattern</option>
                                <option value="keyword">Keyword</option>
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
                        <h6>SenderID Rules</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search SenderIDs..." id="senderid-search">
                        </div>
                    </div>
                    <table class="sec-table" id="senderid-rules-table">
                        <thead>
                            <tr>
                                <th>Rule Name <i class="fas fa-sort"></i></th>
                                <th>Pattern / Value <i class="fas fa-sort"></i></th>
                                <th>Type <i class="fas fa-sort"></i></th>
                                <th>Status <i class="fas fa-sort"></i></th>
                                <th>Created <i class="fas fa-sort"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="senderid-rules-body">
                        </tbody>
                    </table>
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
                            <label>Category</label>
                            <select id="content-filter-category">
                                <option value="">All Categories</option>
                                <option value="spam">Spam</option>
                                <option value="fraud">Fraud</option>
                                <option value="adult">Adult Content</option>
                                <option value="gambling">Gambling</option>
                                <option value="regulated">Regulated Industry</option>
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
                        <h6>Content Filtering Rules</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search rules..." id="content-search">
                        </div>
                    </div>
                    <table class="sec-table" id="content-rules-table">
                        <thead>
                            <tr>
                                <th>Rule Name <i class="fas fa-sort"></i></th>
                                <th>Pattern <i class="fas fa-sort"></i></th>
                                <th>Category <i class="fas fa-sort"></i></th>
                                <th>Action <i class="fas fa-sort"></i></th>
                                <th>Status <i class="fas fa-sort"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="content-rules-body">
                        </tbody>
                    </table>
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
                    <p>Manage URL blacklists, whitelists, and link scanning policies for messages containing URLs.</p>
                </div>

                <div class="sec-stats">
                    <div class="sec-stat-card active">
                        <div class="sec-stat-value" id="url-whitelist-count">0</div>
                        <div class="sec-stat-label">Whitelisted</div>
                    </div>
                    <div class="sec-stat-card blocked">
                        <div class="sec-stat-value" id="url-blacklist-count">0</div>
                        <div class="sec-stat-label">Blacklisted</div>
                    </div>
                    <div class="sec-stat-card pending">
                        <div class="sec-stat-value" id="url-pending-count">0</div>
                        <div class="sec-stat-label">Pending Scan</div>
                    </div>
                    <div class="sec-stat-card total">
                        <div class="sec-stat-value" id="url-total-count">0</div>
                        <div class="sec-stat-label">Total URLs</div>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-filter-row">
                        <div class="sec-filter-group">
                            <label>List Type</label>
                            <select id="url-filter-type">
                                <option value="">All Types</option>
                                <option value="whitelist">Whitelist</option>
                                <option value="blacklist">Blacklist</option>
                            </select>
                        </div>
                        <div class="sec-filter-group">
                            <label>Category</label>
                            <select id="url-filter-category">
                                <option value="">All Categories</option>
                                <option value="phishing">Phishing</option>
                                <option value="malware">Malware</option>
                                <option value="spam">Spam</option>
                                <option value="trusted">Trusted</option>
                            </select>
                        </div>
                        <div class="sec-filter-actions">
                            <button class="sec-btn-primary" onclick="showAddUrlRuleModal()">
                                <i class="fas fa-plus"></i> Add URL
                            </button>
                            <button class="sec-btn-outline" onclick="resetUrlFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>URL Rules</h6>
                        <div class="sec-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search URLs..." id="url-search">
                        </div>
                    </div>
                    <table class="sec-table" id="url-rules-table">
                        <thead>
                            <tr>
                                <th>Domain / URL <i class="fas fa-sort"></i></th>
                                <th>List Type <i class="fas fa-sort"></i></th>
                                <th>Category <i class="fas fa-sort"></i></th>
                                <th>Added By <i class="fas fa-sort"></i></th>
                                <th>Added On <i class="fas fa-sort"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="url-rules-body">
                        </tbody>
                    </table>
                    <div class="sec-empty-state" id="url-empty-state" style="display: none;">
                        <i class="fas fa-link"></i>
                        <h6>No URL Rules</h6>
                        <p>Add URLs to the whitelist or blacklist to control link usage.</p>
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
            { id: 1, name: 'Block Bank Impersonation', pattern: '*HSBC*', type: 'pattern', status: 'active', created: '15-01-2026' },
            { id: 2, name: 'Block Lottery Keywords', pattern: 'WINNER|PRIZE|LOTTERY', type: 'keyword', status: 'active', created: '12-01-2026' },
            { id: 3, name: 'Restrict Premium SenderIDs', pattern: 'PREMIUM', type: 'exact', status: 'pending', created: '20-01-2026' }
        ];

        mockData.contentRules = [
            { id: 1, name: 'Phishing Keywords', pattern: 'verify your account|click here immediately', category: 'fraud', action: 'block', status: 'active' },
            { id: 2, name: 'Adult Content Filter', pattern: '(adult keywords)', category: 'adult', action: 'quarantine', status: 'active' },
            { id: 3, name: 'Gambling Promotion', pattern: 'bet now|free spins|casino bonus', category: 'gambling', action: 'quarantine', status: 'active' }
        ];

        mockData.urlRules = [
            { id: 1, domain: 'bit.ly', type: 'whitelist', category: 'trusted', addedBy: 'admin@quicksms.co.uk', addedOn: '01-01-2026' },
            { id: 2, domain: 'malicious-site.com', type: 'blacklist', category: 'phishing', addedBy: 'admin@quicksms.co.uk', addedOn: '10-01-2026' },
            { id: 3, domain: 'tinyurl.com', type: 'whitelist', category: 'trusted', addedBy: 'admin@quicksms.co.uk', addedOn: '01-01-2026' }
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

        document.getElementById('senderid-active-count').textContent = rules.filter(r => r.status === 'active').length;
        document.getElementById('senderid-blocked-count').textContent = rules.filter(r => r.status === 'blocked').length;
        document.getElementById('senderid-pending-count').textContent = rules.filter(r => r.status === 'pending').length;
        document.getElementById('senderid-total-count').textContent = rules.length;

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            return '<tr>' +
                '<td><strong>' + rule.name + '</strong></td>' +
                '<td><code>' + rule.pattern + '</code></td>' +
                '<td>' + rule.type.charAt(0).toUpperCase() + rule.type.slice(1) + '</td>' +
                '<td><span class="sec-status-badge ' + rule.status + '">' + rule.status.charAt(0).toUpperCase() + rule.status.slice(1) + '</span></td>' +
                '<td>' + rule.created + '</td>' +
                '<td><button class="action-menu-btn"><i class="fas fa-ellipsis-v"></i></button></td>' +
                '</tr>';
        }).join('');
    }

    function renderContentTab() {
        var tbody = document.getElementById('content-rules-body');
        var emptyState = document.getElementById('content-empty-state');
        var rules = mockData.contentRules;

        document.getElementById('content-active-count').textContent = rules.filter(r => r.status === 'active').length;
        document.getElementById('content-blocked-count').textContent = rules.filter(r => r.action === 'block').length;
        document.getElementById('content-pending-count').textContent = 0;
        document.getElementById('content-total-count').textContent = rules.length;

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            return '<tr>' +
                '<td><strong>' + rule.name + '</strong></td>' +
                '<td><code>' + (rule.pattern.length > 30 ? rule.pattern.substring(0, 30) + '...' : rule.pattern) + '</code></td>' +
                '<td>' + rule.category.charAt(0).toUpperCase() + rule.category.slice(1) + '</td>' +
                '<td>' + rule.action.charAt(0).toUpperCase() + rule.action.slice(1) + '</td>' +
                '<td><span class="sec-status-badge ' + rule.status + '">' + rule.status.charAt(0).toUpperCase() + rule.status.slice(1) + '</span></td>' +
                '<td><button class="action-menu-btn"><i class="fas fa-ellipsis-v"></i></button></td>' +
                '</tr>';
        }).join('');
    }

    function renderUrlTab() {
        var tbody = document.getElementById('url-rules-body');
        var emptyState = document.getElementById('url-empty-state');
        var rules = mockData.urlRules;

        document.getElementById('url-whitelist-count').textContent = rules.filter(r => r.type === 'whitelist').length;
        document.getElementById('url-blacklist-count').textContent = rules.filter(r => r.type === 'blacklist').length;
        document.getElementById('url-pending-count').textContent = 0;
        document.getElementById('url-total-count').textContent = rules.length;

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            var typeClass = rule.type === 'whitelist' ? 'active' : 'blocked';
            return '<tr>' +
                '<td><code>' + rule.domain + '</code></td>' +
                '<td><span class="sec-status-badge ' + typeClass + '">' + rule.type.charAt(0).toUpperCase() + rule.type.slice(1) + '</span></td>' +
                '<td>' + rule.category.charAt(0).toUpperCase() + rule.category.slice(1) + '</td>' +
                '<td>' + rule.addedBy + '</td>' +
                '<td>' + rule.addedOn + '</td>' +
                '<td><button class="action-menu-btn"><i class="fas fa-ellipsis-v"></i></button></td>' +
                '</tr>';
        }).join('');
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
    }

    return {
        initialize: initialize,
        renderAllTabs: renderAllTabs
    };
})();

function refreshAllControls() {
    console.log('[SecurityComplianceControls] Refreshing all controls...');
    SecurityComplianceControlsService.renderAllTabs();
}

function showAddSenderIdRuleModal() {
    console.log('[SecurityComplianceControls] TODO: Implement Add SenderID Rule modal');
    alert('Add SenderID Rule - Coming Soon');
}

function showAddContentRuleModal() {
    console.log('[SecurityComplianceControls] TODO: Implement Add Content Rule modal');
    alert('Add Content Rule - Coming Soon');
}

function showAddUrlRuleModal() {
    console.log('[SecurityComplianceControls] TODO: Implement Add URL Rule modal');
    alert('Add URL Rule - Coming Soon');
}

function showAddNormRuleModal() {
    console.log('[SecurityComplianceControls] TODO: Implement Add Normalisation Rule modal');
    alert('Add Normalisation Rule - Coming Soon');
}

function resetSenderIdFilters() {
    document.getElementById('senderid-filter-status').value = '';
    document.getElementById('senderid-filter-type').value = '';
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

@extends('layouts.admin')

@section('title', 'SenderID Approval Detail')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-approval-workflow.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-external-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-notifications.css') }}">
<style>
.detail-page { 
    padding: 1.5rem; 
    padding-bottom: 5rem;
    min-height: auto;
    overflow: visible !important;
    height: auto !important;
}

.content-body {
    overflow: visible !important;
    overflow-y: auto !important;
    height: auto !important;
    max-height: none !important;
}

.content-body.default-height {
    min-height: auto !important;
    height: auto !important;
    overflow: visible !important;
    overflow-y: auto !important;
}

html, body {
    overflow-y: auto !important;
    height: auto !important;
}

#main-wrapper {
    overflow: visible !important;
    height: auto !important;
}

.qsms-main, .qsms-content-wrap {
    overflow: visible !important;
    height: auto !important;
}

.header-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.header-action-btn.primary {
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    border: 1px solid var(--admin-primary, #1e3a5f);
}

.header-action-btn.primary:hover {
    background: var(--admin-secondary, #2d5a87);
    border-color: var(--admin-secondary, #2d5a87);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.page-header h4 {
    color: var(--admin-primary);
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.page-header p {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    text-decoration: none;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.back-link:hover { color: var(--admin-primary); }

.status-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.sender-id-display {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--admin-primary);
    font-family: 'SF Mono', monospace;
    background: #e0e7ff;
    padding: 0.5rem 1rem;
    border-radius: 6px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pill.submitted { background: #dbeafe; color: #1e40af; }
.status-pill.in-review { background: #e0e7ff; color: #3730a3; }
.status-pill.returned-to-customer { background: #fef3c7; color: #92400e; }
.status-pill.validation-in-progress { background: #fce7f3; color: #9d174d; }
.status-pill.validation-failed { background: #fee2e2; color: #991b1b; }
.status-pill.approved { background: #d9f99d; color: #3f6212; }
.status-pill.rejected { background: #fecaca; color: #7f1d1d; }
.status-pill.live { background: #bbf7d0; color: #15803d; }

.detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}

.detail-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.detail-card-header {
    background: #f8fafc;
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: var(--admin-primary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-card-body { padding: 1.25rem; }

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child { border-bottom: none; }

.detail-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

.detail-value {
    font-size: 0.875rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
}

.detail-value.mono {
    font-family: 'SF Mono', monospace;
    background: #f1f5f9;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.yes-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #d9f99d;
    color: #3f6212;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.no-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #fecaca;
    color: #991b1b;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.channels-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.channel-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.8rem;
}

.channel-item.enabled {
    background: #f0fdf4;
    border-color: #86efac;
}

.channel-item.disabled {
    background: #fef2f2;
    border-color: #fecaca;
    color: #9ca3af;
}

.channel-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-size: 0.7rem;
}

.channel-item.enabled .channel-icon { background: #bbf7d0; color: #15803d; }
.channel-item.disabled .channel-icon { background: #fee2e2; color: #dc2626; }

.validation-section { margin-top: 1.5rem; }

.validation-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.validation-item.pass { background: #f0fdf4; border: 1px solid #bbf7d0; }
.validation-item.fail { background: #fef2f2; border: 1px solid #fecaca; }
.validation-item.warn { background: #fffbeb; border: 1px solid #fde68a; }

.validation-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.validation-item.pass .validation-icon { background: #22c55e; color: #fff; }
.validation-item.fail .validation-icon { background: #ef4444; color: #fff; }
.validation-item.warn .validation-icon { background: #f59e0b; color: #fff; }

.validation-content { flex: 1; }

.validation-title {
    font-weight: 600;
    font-size: 0.8rem;
    color: #1e293b;
    margin-bottom: 0.125rem;
}

.validation-detail {
    font-size: 0.75rem;
    color: #64748b;
}

.explanation-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 1rem;
    font-size: 0.875rem;
    color: #475569;
    font-style: italic;
    margin-top: 0.5rem;
}

.action-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
    margin-top: 1.5rem;
}

.action-panel-title {
    font-weight: 600;
    color: var(--admin-primary);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
}

.action-btn.primary {
    background: var(--admin-primary);
    color: #fff;
}

.action-btn.primary:hover { background: var(--admin-accent); }

.action-btn.success {
    background: #22c55e;
    color: #fff;
}

.action-btn.success:hover { background: #16a34a; }

.action-btn.warning {
    background: #f59e0b;
    color: #fff;
}

.action-btn.warning:hover { background: #d97706; }

.action-btn.danger {
    background: #ef4444;
    color: #fff;
}

.action-btn.danger:hover { background: #dc2626; }

.action-btn.outline {
    background: #fff;
    border-color: #e2e8f0;
    color: #475569;
}

.action-btn.outline:hover {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
}

.action-btn.enterprise {
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: #fff;
}

.action-btn.enterprise:hover { opacity: 0.9; }

.notes-section { margin-top: 1.5rem; }

.notes-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 1rem;
}

.notes-tab {
    padding: 0.75rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    background: none;
    border: none;
}

.notes-tab.active {
    color: var(--admin-primary);
    border-bottom-color: var(--admin-primary);
}

.notes-content { display: none; }
.notes-content.active { display: block; }

.notes-textarea {
    width: 100%;
    min-height: 120px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.875rem;
    font-size: 0.875rem;
    resize: vertical;
}

.notes-textarea:focus {
    outline: none;
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

.note-entry {
    padding: 0.875rem;
    background: #f8fafc;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.note-author {
    font-weight: 600;
    font-size: 0.8rem;
    color: var(--admin-primary);
}

.note-time {
    font-size: 0.7rem;
    color: #94a3b8;
}

.note-text {
    font-size: 0.85rem;
    color: #475569;
}

.audit-trail {
    max-height: 300px;
    overflow-y: auto;
}

.audit-entry {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.audit-entry:last-child { border-bottom: none; }

.audit-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    background: #e0e7ff;
    color: #3730a3;
    flex-shrink: 0;
}

.audit-content { flex: 1; }

.audit-action {
    font-size: 0.8rem;
    color: #1e293b;
    font-weight: 500;
}

.audit-meta {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 0.125rem;
}

.context-info {
    display: flex;
    gap: 1.5rem;
    padding: 0.75rem 1rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.context-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}

.context-label { color: #64748b; }
.context-value { color: var(--admin-primary); font-weight: 600; }

.sidebar-card { margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="detail-page">
    <a href="{{ route('admin.approval-queue') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Approval Queue
    </a>

    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.approval-queue') }}">Approval Queue</a></li>
            <li class="breadcrumb-item active">SenderID Detail</li>
        </ol>
    </div>

    <div class="status-header">
        <span class="sender-id-display" id="senderIdValue">ACMEBANK</span>
        <span class="status-pill submitted" id="currentStatus"><i class="fas fa-paper-plane"></i> Submitted</span>
        <div style="margin-left: auto; display: flex; gap: 1rem; align-items: center;">
            <div style="font-size: 0.8rem; color: #64748b; display: flex; gap: 1rem;">
                <span><i class="fas fa-hashtag me-1"></i>Request ID: <strong>SID-001</strong></span>
                <span><i class="fas fa-clock me-1"></i>Submitted: <strong>Jan 20, 2026, 10:15 AM</strong></span>
            </div>
            @php
                $senderIdVersions = [
                    ['id' => 'v2', 'label' => 'Version 2 (Current)', 'date' => '20 Jan 2026, 10:15', 'status' => 'submitted'],
                    ['id' => 'v1', 'label' => 'Version 1', 'date' => '15 Jan 2026, 14:30', 'status' => 'returned'],
                ];
            @endphp
            @include('partials.admin.version-history-dropdown', [
                'currentVersion' => 'v2',
                'submissionId' => 'SID-001',
                'submissionType' => 'sender-id',
                'versions' => $senderIdVersions
            ])
            @include('partials.admin.compare-versions', [
                'submissionId' => 'SID-001',
                'submissionType' => 'sender-id',
                'versions' => $senderIdVersions
            ])
            <button class="header-action-btn primary" onclick="showAdminActionsModal()">
                <i class="fas fa-gavel"></i>
                Admin Actions
            </button>
        </div>
    </div>

    <div class="context-info">
        <div class="context-item">
            <i class="fas fa-building"></i>
            <span class="context-label">Account:</span>
            <span class="context-value">Acme Corporation</span>
        </div>
        <div class="context-item">
            <i class="fas fa-sitemap"></i>
            <span class="context-label">Sub-Account:</span>
            <span class="context-value">Marketing Dept</span>
        </div>
        <div class="context-item">
            <i class="fas fa-user"></i>
            <span class="context-label">Submitted By:</span>
            <span class="context-value">John Smith (j.smith@acme.com)</span>
        </div>
    </div>

    <div class="detail-grid">
        <div class="main-content">
            {{-- CANONICAL REVIEW UI - EXACT SAME as customer registration wizard Step 5 --}}
            {{-- Sections A-D: Matches customer final review exactly --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-id-card"></i> SenderID Overview
                    <span class="badge bg-info ms-2" style="font-size: 0.65rem;">Matches Customer Final Review</span>
                </div>
                <div class="detail-card-body">
                    <div id="sender-id-overview-loading" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2 text-muted">Loading SenderID details...</span>
                    </div>
                    @php
                    $senderIdData = [
                        'senderId' => '',
                        'type' => '',
                        'normalisedValue' => null,
                        'brand' => '',
                        'hasPermission' => false,
                        'explanation' => '',
                        'channels' => [
                            'portal' => false,
                            'inbox' => false,
                            'emailToSms' => false,
                            'api' => false
                        ],
                        'useCase' => '',
                        'description' => '',
                        'validation' => [
                            'characterCompliance' => false,
                            'lengthCompliance' => false,
                            'restrictedChars' => false,
                            'ukRules' => false
                        ]
                    ];
                    
                    $senderIdMetadata = [
                        'versionId' => '',
                        'submittedBy' => '',
                        'account' => '',
                        'subAccount' => '',
                        'createdAt' => '',
                        'submittedAt' => '',
                        'lastUpdatedAt' => '',
                        'externalValidationStatus' => '',
                        'externalReferenceIds' => []
                    ];
                    @endphp
                    <div id="sender-id-overview-content" style="display: none;">
                        @include('partials.review.sender-id-review-summary', [
                            'isAdmin' => true,
                            'data' => $senderIdData
                        ])
                        
                        {{-- Section E: Admin-only Submission Metadata --}}
                        @include('partials.admin.sender-id-admin-extras', ['metadata' => $senderIdMetadata])
                    </div>
                </div>
            </div>

            <div class="detail-card validation-section">
                <div class="detail-card-header">
                    <i class="fas fa-shield-alt"></i> Automated Validation Results
                </div>
                <div class="detail-card-body">
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Character Rules</div>
                            <div class="validation-detail">Only alphanumeric characters (A-Z, 0-9) detected. No special characters or spaces.</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Length Rules</div>
                            <div class="validation-detail">8 characters - within the 3-11 character limit for alphanumeric SenderIDs.</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">UK Shortcode Rules</div>
                            <div class="validation-detail">Not applicable - this is an alphanumeric SenderID, not a shortcode.</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Numeric Normalisation</div>
                            <div class="validation-detail">No leading zeros or numeric-only patterns detected.</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Restricted Keyword Detection</div>
                            <div class="validation-detail">No restricted keywords (HMRC, NHS, GOV, Police, etc.) detected.</div>
                        </div>
                    </div>
                    <div class="validation-item warn">
                        <div class="validation-icon"><i class="fas fa-exclamation"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Brand Mismatch Warning</div>
                            <div class="validation-detail">SenderID contains "BANK" - verify business is authorised to use banking-related terminology. Recommend BrandAssure verification.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="external-validation-card">
                <div class="external-validation-header">
                    <i class="fas fa-shield-alt"></i> BrandAssure Validation Tracking
                </div>
                <div class="external-validation-body" id="brandAssureTracking">
                    <div class="validation-empty">
                        <i class="fas fa-shield-alt"></i>
                        <p>No BrandAssure validation requests yet</p>
                        <small>Click "Submit to BrandAssure" to initiate external brand verification</small>
                    </div>
                </div>
            </div>

        </div>

        <div class="sidebar">
            <div class="detail-card sidebar-card">
                <div class="detail-card-header">
                    <i class="fas fa-history"></i> Audit Trail
                </div>
                <div class="detail-card-body">
                    <div class="audit-trail">
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-paper-plane"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Request Submitted</div>
                                <div class="audit-meta">John Smith | Jan 20, 2026, 10:15 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-robot"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Automated Validation Complete</div>
                                <div class="audit-meta">System | Jan 20, 2026, 10:15 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Brand Warning Flagged</div>
                                <div class="audit-meta">System | Jan 20, 2026, 10:15 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-eye"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Viewed by Admin</div>
                                <div class="audit-meta">Sarah Johnson | Jan 20, 2026, 11:28 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-comment"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Internal Note Added</div>
                                <div class="audit-meta">Sarah Johnson | Jan 20, 2026, 11:30 AM</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card sidebar-card">
                <div class="detail-card-header">
                    <i class="fas fa-info-circle"></i> Request Details
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Request ID</span>
                        <span class="detail-value mono">SID-001</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SLA Timer</span>
                        <span class="detail-value" style="color: #22c55e;"><i class="fas fa-hourglass-half me-1"></i>18h remaining</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Risk Level</span>
                        <span class="detail-value"><span class="yes-badge" style="background: #fef3c7; color: #92400e;">Medium</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Assigned Admin</span>
                        <span class="detail-value">Unassigned</span>
                    </div>
                </div>
            </div>

            <div class="detail-card sidebar-card">
                <div class="detail-card-header">
                    <i class="fas fa-user-circle"></i> Customer Account
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Account Status</span>
                        <span class="detail-value"><span class="yes-badge">Active</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Account Age</span>
                        <span class="detail-value">2 years, 4 months</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Existing SenderIDs</span>
                        <span class="detail-value">3 approved</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Previous Rejections</span>
                        <span class="detail-value">0</span>
                    </div>
                    <a href="{{ route('admin.accounts.details', ['accountId' => 'ACC-1234']) }}" class="action-btn outline" style="width: 100%; justify-content: center; margin-top: 0.75rem;">
                        <i class="fas fa-external-link-alt"></i> View Full Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject SenderID Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Rejection Reason</label>
                    <select class="form-select" id="rejectReason">
                        <option value="">Select a reason...</option>
                        <option value="brand-unauthorized">Brand/trademark not authorized</option>
                        <option value="restricted-keyword">Contains restricted keyword</option>
                        <option value="verification-failed">Business verification failed</option>
                        <option value="policy-violation">Policy violation</option>
                        <option value="duplicate">Duplicate of existing SenderID</option>
                        <option value="other">Other (specify below)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Customer-Facing Message</label>
                    <textarea class="form-control" id="rejectMessage" rows="4" placeholder="Explain the reason for rejection (will be sent to customer)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">Reject Request</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script src="{{ asset('js/unified-approval-framework.js') }}"></script>
<script src="{{ asset('js/admin-notifications.js') }}"></script>
<script src="{{ asset('js/admin-audit-log.js') }}"></script>
<script>
var csrfToken = $('meta[name="csrf-token"]').attr('content');
var senderIdUuid = @json($sender_id ?? '');
var currentSenderIdData = null;

function ajaxHeaders() {
    return { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' };
}

var SENDER_ID_VALIDATION = {
    characterRules: function(value) {
        var alphanumericOnly = /^[A-Za-z0-9]+$/.test(value);
        var startsWithLetter = /^[A-Za-z]/.test(value);
        return {
            pass: alphanumericOnly && startsWithLetter,
            message: alphanumericOnly ? 'Only alphanumeric characters detected' : 'Contains invalid characters'
        };
    },
    
    lengthRules: function(value, type) {
        var limits = {
            'alpha': { min: 3, max: 11 },
            'alphanumeric': { min: 3, max: 11 },
            'numeric': { min: 10, max: 15 },
            'shortcode': { min: 5, max: 6 }
        };
        var limit = limits[type.toLowerCase()] || limits.alphanumeric;
        var pass = value.length >= limit.min && value.length <= limit.max;
        return {
            pass: pass,
            message: value.length + ' characters - ' + (pass ? 'within' : 'outside') + ' the ' + limit.min + '-' + limit.max + ' character limit'
        };
    },
    
    ukShortcodeRules: function(value, type) {
        if (type.toLowerCase() !== 'shortcode') {
            return { pass: true, message: 'Not applicable - not a shortcode' };
        }
        var validShortcode = /^[0-9]{5,6}$/.test(value);
        return {
            pass: validShortcode,
            message: validShortcode ? 'Valid UK shortcode format' : 'Invalid UK shortcode format'
        };
    },
    
    numericNormalisation: function(value) {
        var hasLeadingZero = /^0/.test(value);
        return {
            pass: !hasLeadingZero,
            message: hasLeadingZero ? 'Leading zeros detected - normalisation required' : 'No leading zeros detected'
        };
    },
    
    restrictedKeywords: function(value) {
        var restricted = ['HMRC', 'NHS', 'GOV', 'POLICE', 'DVLA', 'UKGOV', 'GOVT', 'ROYAL'];
        var upper = value.toUpperCase();
        var found = restricted.filter(function(k) { return upper.includes(k); });
        return {
            pass: found.length === 0,
            message: found.length === 0 ? 'No restricted keywords detected' : 'Restricted keywords found: ' + found.join(', ')
        };
    },
    
    brandMismatch: function(value, brandName) {
        var sensitiveTerms = ['BANK', 'FINANCE', 'INSURANCE', 'CREDIT', 'LOAN'];
        var upper = value.toUpperCase();
        var found = sensitiveTerms.filter(function(t) { return upper.includes(t); });
        return {
            warn: found.length > 0,
            message: found.length > 0 
                ? 'Contains "' + found.join(', ') + '" - verify business authorisation'
                : 'No sensitive industry terms detected'
        };
    },
    
    runAllValidations: function(value, type, brandName) {
        return {
            characterRules: this.characterRules(value),
            lengthRules: this.lengthRules(value, type),
            ukShortcodeRules: this.ukShortcodeRules(value, type),
            numericNormalisation: this.numericNormalisation(value),
            restrictedKeywords: this.restrictedKeywords(value),
            brandMismatch: this.brandMismatch(value, brandName)
        };
    }
};

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('[SenderID Detail] Initialized with UUID:', senderIdUuid);
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('PAGE_VIEW', 'sender-id-detail', { requestId: senderIdUuid }, 'LOW');
    }

    if (senderIdUuid) {
        loadSenderIdDetail();
    }
});

function loadSenderIdDetail() {
    $.ajax({
        url: '/admin/api/sender-ids/' + senderIdUuid,
        method: 'GET',
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success) {
                currentSenderIdData = response.data;
                populateDetailPage(response.data, response.spoofing_check, response.status_history, response.account);
                $('#sender-id-overview-loading').hide();
                $('#sender-id-overview-content').show();

                if (typeof UNIFIED_APPROVAL !== 'undefined') {
                    UNIFIED_APPROVAL.init({
                        entityType: 'SENDER_ID',
                        entityId: senderIdUuid,
                        currentVersion: response.data.version || 1,
                        currentStatus: response.data.workflow_status,
                        accountId: response.data.account_id || '',
                        accountName: (response.account && response.account.company_name) || '',
                        submittedBy: response.data.created_by || '',
                        submittedAt: response.data.submitted_at || response.data.created_at || '',
                        entityData: {
                            value: response.data.sender_id_value,
                            type: response.data.sender_type,
                            brand: response.data.brand_name,
                            permissionConfirmed: response.data.permission_confirmed,
                            explanation: response.data.use_case_description || '',
                            vertical: response.data.use_case || ''
                        }
                    });
                }

                if (typeof ADMIN_NOTIFICATIONS !== 'undefined') {
                    ADMIN_NOTIFICATIONS.init();
                    checkSlaStatus();
                }
            }
        },
        error: function(xhr) {
            console.error('[SenderID Detail] Load error:', xhr.responseText);
            alert('Failed to load SenderID details.');
        }
    });
}

function populateDetailPage(data, spoofingCheck, statusHistory, account) {
    document.getElementById('senderIdValue').textContent = data.sender_id_value || '';

    var statusMap = {
        'draft': { cls: 'submitted', icon: 'fa-pencil-alt', label: 'Draft' },
        'submitted': { cls: 'submitted', icon: 'fa-paper-plane', label: 'Submitted' },
        'in_review': { cls: 'in-review', icon: 'fa-search', label: 'In Review' },
        'pending_info': { cls: 'returned-to-customer', icon: 'fa-question-circle', label: 'Info Requested' },
        'info_provided': { cls: 'submitted', icon: 'fa-reply', label: 'Info Provided' },
        'approved': { cls: 'approved', icon: 'fa-check-circle', label: 'Approved' },
        'rejected': { cls: 'rejected', icon: 'fa-times-circle', label: 'Rejected' },
        'suspended': { cls: 'rejected', icon: 'fa-pause-circle', label: 'Suspended' },
        'revoked': { cls: 'rejected', icon: 'fa-ban', label: 'Revoked' }
    };
    var statusInfo = statusMap[data.workflow_status] || { cls: 'submitted', icon: 'fa-question', label: data.workflow_status };
    updateStatus(statusInfo.cls, statusInfo.label, statusInfo.icon);

    var headerMeta = document.querySelector('.status-header div[style*="font-size: 0.8rem"]');
    if (headerMeta) {
        var submittedDate = data.submitted_at ? new Date(data.submitted_at).toLocaleString('en-GB', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
        headerMeta.innerHTML = '<span><i class="fas fa-hashtag me-1"></i>Request ID: <strong>' + escapeHtml(data.uuid) + '</strong></span>' +
            '<span><i class="fas fa-clock me-1"></i>Submitted: <strong>' + escapeHtml(submittedDate) + '</strong></span>';
    }

    var contextInfo = document.querySelector('.context-info');
    if (contextInfo && account) {
        var accountName = account.company_name || '';
        var accountNum = account.account_number || '';
        contextInfo.innerHTML = '<div class="context-item"><i class="fas fa-building"></i><span class="context-label">Account:</span><span class="context-value">' + escapeHtml(accountName) + ' (' + escapeHtml(accountNum) + ')</span></div>' +
            '<div class="context-item"><i class="fas fa-user"></i><span class="context-label">Created By:</span><span class="context-value">' + escapeHtml(data.created_by || 'N/A') + '</span></div>';
    }

    if (spoofingCheck) {
        renderSpoofingCheck(spoofingCheck);
    }

    if (statusHistory && statusHistory.length > 0) {
        renderAuditTrail(statusHistory);
    }

    var sidebarRequestId = document.querySelector('.sidebar-card .detail-value.mono');
    if (sidebarRequestId) {
        sidebarRequestId.textContent = data.uuid || '';
    }

    if (account) {
        var accountStatusEl = document.querySelector('.sidebar .detail-card:last-child .detail-card-body');
        if (accountStatusEl) {
            var statusBadge = (account.status === 'active' || account.status === 'Active') ? '<span class="yes-badge">Active</span>' : '<span class="no-badge">' + escapeHtml(account.status || 'Unknown') + '</span>';
            var rows = accountStatusEl.querySelectorAll('.detail-row');
            if (rows[0]) {
                rows[0].querySelector('.detail-value').innerHTML = statusBadge;
            }
        }

        var viewAccountLink = accountStatusEl ? accountStatusEl.querySelector('a.action-btn') : null;
        if (viewAccountLink && account.id) {
            viewAccountLink.href = '/admin/accounts/' + account.id;
        }
    }

    updateActionButtonVisibility(data.workflow_status);
}

function renderSpoofingCheck(spoofingCheck) {
    var container = document.querySelector('.validation-section .detail-card-body');
    if (!container) return;

    var html = '';
    if (spoofingCheck.results && Array.isArray(spoofingCheck.results)) {
        spoofingCheck.results.forEach(function(check) {
            var cls = check.pass ? 'pass' : (check.warn ? 'warn' : 'fail');
            var icon = check.pass ? 'fa-check' : (check.warn ? 'fa-exclamation' : 'fa-times');
            html += '<div class="validation-item ' + cls + '">';
            html += '<div class="validation-icon"><i class="fas ' + icon + '"></i></div>';
            html += '<div class="validation-content">';
            html += '<div class="validation-title">' + escapeHtml(check.name || check.title || '') + '</div>';
            html += '<div class="validation-detail">' + escapeHtml(check.message || check.detail || '') + '</div>';
            html += '</div></div>';
        });
    }

    if (html) {
        container.innerHTML = html;
    }
}

function renderAuditTrail(statusHistory) {
    var container = document.querySelector('.audit-trail');
    if (!container) return;

    var iconMap = {
        'submitted': 'fa-paper-plane',
        'review_started': 'fa-eye',
        'approved': 'fa-check-circle',
        'rejected': 'fa-times-circle',
        'info_requested': 'fa-question-circle',
        'info_provided': 'fa-reply',
        'review_resumed': 'fa-eye',
        'suspended': 'fa-pause-circle',
        'reactivated': 'fa-play-circle',
        'revoked': 'fa-ban',
        'status_changed': 'fa-exchange-alt',
        'resubmission_started': 'fa-redo'
    };

    var html = '';
    statusHistory.forEach(function(entry) {
        var icon = iconMap[entry.action] || 'fa-circle';
        var actionLabel = (entry.action || '').replace(/_/g, ' ');
        actionLabel = actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1);
        var meta = (entry.user_name || entry.user_email || 'System');
        if (entry.created_at) {
            meta += ' | ' + new Date(entry.created_at).toLocaleString('en-GB', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        html += '<div class="audit-entry">';
        html += '<div class="audit-icon"><i class="fas ' + icon + '"></i></div>';
        html += '<div class="audit-content">';
        html += '<div class="audit-action">' + escapeHtml(actionLabel) + '</div>';
        html += '<div class="audit-meta">' + escapeHtml(meta) + '</div>';
        if (entry.reason) {
            html += '<div class="audit-meta" style="color: #64748b; margin-top: 0.25rem;">' + escapeHtml(entry.reason) + '</div>';
        }
        if (entry.notes) {
            html += '<div class="audit-meta" style="color: #475569; margin-top: 0.25rem; font-style: italic;">' + escapeHtml(entry.notes) + '</div>';
        }
        html += '</div></div>';
    });

    container.innerHTML = html;
}

function updateActionButtonVisibility(status) {
    var adminActionsModal = document.getElementById('adminActionsModal');
    if (!adminActionsModal) return;

    var approveBtn = adminActionsModal.querySelector('[onclick*="approveSenderId"]');
    var rejectBtn = adminActionsModal.querySelector('[onclick*="showRejectModal"]');
    var requestInfoBtn = adminActionsModal.querySelector('[onclick*="returnToCustomer"]');
    var suspendBtn = adminActionsModal.querySelector('[onclick*="suspendSenderId"]');
    var reactivateBtn = adminActionsModal.querySelector('[onclick*="reactivateSenderId"]');
    var revokeBtn = adminActionsModal.querySelector('[onclick*="revokeSenderId"]');

    var allBtns = [approveBtn, rejectBtn, requestInfoBtn, suspendBtn, reactivateBtn, revokeBtn];
    allBtns.forEach(function(btn) { if (btn) btn.style.display = 'none'; });

    switch (status) {
        case 'submitted':
            break;
        case 'in_review':
            if (approveBtn) approveBtn.style.display = '';
            if (rejectBtn) rejectBtn.style.display = '';
            if (requestInfoBtn) requestInfoBtn.style.display = '';
            break;
        case 'approved':
            if (suspendBtn) suspendBtn.style.display = '';
            break;
        case 'suspended':
            if (reactivateBtn) reactivateBtn.style.display = '';
            if (revokeBtn) revokeBtn.style.display = '';
            break;
    }
}

function checkHighRiskFlags() {
    var brandWarning = document.querySelector('.validation-item.warn');
    if (brandWarning) {
        var warningText = brandWarning.textContent;
        if (warningText.includes('BANK') || warningText.includes('NHS') || warningText.includes('HMRC')) {
            if (typeof ADMIN_NOTIFICATIONS !== 'undefined') {
                ADMIN_NOTIFICATIONS.triggerInternalAlert('HIGH_RISK', senderIdUuid, 'SenderID contains regulated term - requires enhanced verification');
            }
        }
    }
}

function checkSlaStatus() {
    if (!currentSenderIdData) return;
    var submittedAt = currentSenderIdData.submitted_at || currentSenderIdData.created_at;
    if (submittedAt && typeof ADMIN_NOTIFICATIONS !== 'undefined') {
        ADMIN_NOTIFICATIONS.checkSlaBreach(senderIdUuid, submittedAt, 'SenderID');
    }
}

function switchNotesTab(tab) {
    document.querySelectorAll('.notes-tab').forEach(function(t) {
        t.classList.remove('active');
    });
    document.querySelectorAll('.notes-content').forEach(function(c) {
        c.classList.remove('active');
    });
    
    event.target.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

function performAction(action, body, successMsg) {
    $.ajax({
        url: '/admin/api/sender-ids/' + senderIdUuid + '/' + action,
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify(body || {}),
        success: function(response) {
            if (response.success) {
                alert(successMsg || response.message || 'Action completed.');
                loadSenderIdDetail();
            } else {
                alert(response.error || 'Action failed.');
            }
        },
        error: function(xhr) {
            var msg = 'Action failed.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            alert(msg);
        }
    });
}

function startReview() {
    if (confirm('Start review for this SenderID?')) {
        performAction('review', {}, 'SenderID is now in review.');
    }
}

function returnToCustomer() {
    var notes = prompt('What information do you need from the customer?');
    if (!notes || notes.trim().length < 5) {
        alert('Please provide details about what information is needed (min 5 characters).');
        return;
    }
    performAction('request-info', { notes: notes.trim() }, 'Information request sent to customer.');
}

function showRejectModal() {
    var modal = document.getElementById('rejectModal');
    if (modal) {
        document.getElementById('rejectReason').value = '';
        document.getElementById('rejectMessage').value = '';
        new bootstrap.Modal(modal).show();
    }
}

function confirmReject() {
    var reason = document.getElementById('rejectMessage').value.trim();
    if (!reason || reason.length < 10) {
        alert('Please provide a rejection reason (minimum 10 characters).');
        return;
    }

    $.ajax({
        url: '/admin/api/sender-ids/' + senderIdUuid + '/reject',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({ reason: reason }),
        success: function(response) {
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                alert('SenderID rejected.');
                loadSenderIdDetail();
            } else {
                alert(response.error || 'Failed to reject.');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to reject';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            alert(msg);
        }
    });
}

function approveSenderId() {
    if (confirm('Approve this SenderID request?')) {
        performAction('approve', { notes: 'Manual approval by admin' }, 'SenderID approved successfully.');
    }
}

function suspendSenderId() {
    var reason = prompt('Reason for suspending this SenderID (required):');
    if (!reason || reason.trim().length < 5) {
        alert('Suspension reason is required (min 5 characters).');
        return;
    }
    performAction('suspend', { reason: reason.trim() }, 'SenderID suspended.');
}

function reactivateSenderId() {
    if (confirm('Reactivate this suspended SenderID?')) {
        performAction('reactivate', { notes: 'Reactivated by admin' }, 'SenderID reactivated.');
    }
}

function revokeSenderId() {
    var reason = prompt('Reason for permanently revoking this SenderID (required):');
    if (!reason || reason.trim().length < 5) {
        alert('Revocation reason is required (min 5 characters).');
        return;
    }
    if (confirm('This action is PERMANENT. Revoke this SenderID?')) {
        performAction('revoke', { reason: reason.trim() }, 'SenderID revoked permanently.');
    }
}

function submitToExternalProvider() {
    if (confirm('Submit this SenderID to BrandAssure for external validation?')) {
        if (typeof UNIFIED_APPROVAL !== 'undefined') {
            var entity = UNIFIED_APPROVAL.getCurrentEntity();
            UNIFIED_APPROVAL.submitToExternalProvider(entity ? entity.data : {});
        }
    }
}

function forceApprove() {
    var reason = prompt('ENTERPRISE OVERRIDE: Enter reason for force approve (required for audit):');
    if (!reason) {
        alert('Reason is required for force approve.');
        return;
    }
    
    if (confirm('Force approve this SenderID bypassing validation? This action is logged with CRITICAL severity.')) {
        performAction('approve', { notes: 'FORCE APPROVE: ' + reason }, 'SenderID force approved (enterprise override).');
    }
}

function updateStatus(status, label, icon) {
    var pill = document.getElementById('currentStatus');
    pill.className = 'status-pill ' + status;
    pill.innerHTML = '<i class="fas ' + icon + '"></i> ' + label;
}

function addInternalNote() {
    var textarea = document.querySelector('#tab-internal .notes-textarea');
    if (!textarea) return;
    var note = textarea.value.trim();
    if (!note) {
        alert('Please enter a note');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ADD_INTERNAL_NOTE', senderIdUuid, { note: note.substring(0, 100) }, 'LOW');
    }
    
    textarea.value = '';
    alert('Internal note added.');
}

function previewCustomerMessage() {
    var textarea = document.querySelector('#tab-customer .notes-textarea');
    var message = textarea ? textarea.value : '';
    alert('Preview:\n\n' + (message || '(No message entered)'));
}

function sendCustomerMessage() {
    var textarea = document.querySelector('#tab-customer .notes-textarea');
    if (!textarea) return;
    var message = textarea.value.trim();
    if (!message) {
        alert('Please enter a message');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('SEND_CUSTOMER_MESSAGE', senderIdUuid, {}, 'MEDIUM');
    }
    
    textarea.value = '';
    alert('Message sent to customer.');
}

function showAdminActionsModal() {
    var modal = document.getElementById('adminActionsModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    }
}
</script>
@endpush

{{-- Admin Actions Modal --}}
<style>
#adminActionsModal:not(.show) {
    display: none !important;
}
</style>
<div class="modal fade" id="adminActionsModal" tabindex="-1" aria-labelledby="adminActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--admin-primary, #1e3a5f) 0%, #2d5a87 100%); color: #fff;">
                <h5 class="modal-title" id="adminActionsModalLabel">
                    <i class="fas fa-gavel me-2"></i>Admin Actions
                    <span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem;"><i class="fas fa-lock me-1"></i>INTERNAL ONLY</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 0;">
                @include('partials.admin.approval-action-panel', [
                    'entityType' => 'sender_id',
                    'entityId' => 'SID-001',
                    'validationProvider' => 'BrandAssure',
                    'isModal' => true
                ])
            </div>
        </div>
    </div>
</div>

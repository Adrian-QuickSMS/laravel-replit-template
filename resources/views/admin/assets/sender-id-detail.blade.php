@extends('layouts.admin')

@section('title', 'SenderID Approval Detail')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-approval-workflow.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-external-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-notifications.css') }}">
<style>
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

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
                        <span class="detail-value mono" id="sidebarRequestId">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SLA Timer</span>
                        <span class="detail-value" id="sidebarSlaTimer">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Risk Level</span>
                        <span class="detail-value" id="sidebarRiskLevel">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Assigned Admin</span>
                        <span class="detail-value" id="sidebarAssignedAdmin">Unassigned</span>
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
                        <span class="detail-value" id="sidebarAccountStatus">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Account Age</span>
                        <span class="detail-value" id="sidebarAccountAge">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Existing SenderIDs</span>
                        <span class="detail-value" id="sidebarExistingSenderIds">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Previous Rejections</span>
                        <span class="detail-value" id="sidebarPrevRejections">-</span>
                    </div>
                    <a href="#" id="sidebarViewAccountLink" class="action-btn outline" style="width: 100%; justify-content: center; margin-top: 0.75rem;">
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
                renderCommentThread(response.comments || []);
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
            showToast('Failed to load SenderID details.', 'error');
        }
    });
}

function populateDetailPage(data, spoofingCheck, statusHistory, account) {
    document.getElementById('senderIdValue').textContent = data.sender_id_value || '';

    var statusMap = {
        'draft': { cls: 'submitted', icon: 'fa-pencil-alt', label: 'Draft' },
        'submitted': { cls: 'submitted', icon: 'fa-paper-plane', label: 'Submitted' },
        'in_review': { cls: 'in-review', icon: 'fa-search', label: 'In Review' },
        'pending_info': { cls: 'returned-to-customer', icon: 'fa-undo', label: 'Returned to Customer' },
        'info_provided': { cls: 'in-review', icon: 'fa-reply', label: 'Resubmitted' },
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

    var sidebarRequestId = document.getElementById('sidebarRequestId');
    if (sidebarRequestId) {
        sidebarRequestId.textContent = data.uuid || '-';
    }

    var sidebarSlaTimer = document.getElementById('sidebarSlaTimer');
    if (sidebarSlaTimer && data.submitted_at) {
        var submitted = new Date(data.submitted_at);
        var now = new Date();
        var hoursElapsed = (now - submitted) / (1000 * 60 * 60);
        var slaHours = 24;
        var remaining = Math.max(0, slaHours - hoursElapsed);
        if (remaining <= 0) {
            sidebarSlaTimer.innerHTML = '<span style="color: #dc2626;"><i class="fas fa-exclamation-triangle me-1"></i>SLA Breached</span>';
        } else if (remaining <= 4) {
            sidebarSlaTimer.innerHTML = '<span style="color: #f59e0b;"><i class="fas fa-hourglass-half me-1"></i>' + Math.round(remaining) + 'h remaining</span>';
        } else {
            sidebarSlaTimer.innerHTML = '<span style="color: #22c55e;"><i class="fas fa-hourglass-half me-1"></i>' + Math.round(remaining) + 'h remaining</span>';
        }
    } else if (sidebarSlaTimer) {
        sidebarSlaTimer.innerHTML = '<span style="color: #64748b;">N/A</span>';
    }

    var sidebarRiskLevel = document.getElementById('sidebarRiskLevel');
    if (sidebarRiskLevel && spoofingCheck) {
        var riskLevel = 'Low';
        var riskColor = '#dcfce7';
        var riskTextColor = '#166534';
        if (spoofingCheck.results) {
            var failCount = spoofingCheck.results.filter(function(r) { return r.pass === false || r.result === 'fail'; }).length;
            var warnCount = spoofingCheck.results.filter(function(r) { return r.warn === true || r.result === 'warn'; }).length;
            if (failCount > 0) { riskLevel = 'High'; riskColor = '#fee2e2'; riskTextColor = '#991b1b'; }
            else if (warnCount > 0) { riskLevel = 'Medium'; riskColor = '#fef3c7'; riskTextColor = '#92400e'; }
        }
        sidebarRiskLevel.innerHTML = '<span class="yes-badge" style="background: ' + riskColor + '; color: ' + riskTextColor + ';">' + riskLevel + '</span>';
    } else if (sidebarRiskLevel) {
        sidebarRiskLevel.innerHTML = '<span class="yes-badge" style="background: #dcfce7; color: #166534;">Low</span>';
    }

    var sidebarAssignedAdmin = document.getElementById('sidebarAssignedAdmin');
    if (sidebarAssignedAdmin) {
        if (data.reviewed_by) {
            sidebarAssignedAdmin.textContent = data.reviewed_by;
        } else {
            sidebarAssignedAdmin.textContent = 'Unassigned';
        }
    }

    if (account) {
        var sidebarAccountStatus = document.getElementById('sidebarAccountStatus');
        if (sidebarAccountStatus) {
            var isActive = (account.status === 'active' || account.status === 'Active');
            sidebarAccountStatus.innerHTML = isActive
                ? '<span class="yes-badge">Active</span>'
                : '<span class="no-badge">' + escapeHtml(account.status || 'Unknown') + '</span>';
        }

        var sidebarAccountAge = document.getElementById('sidebarAccountAge');
        if (sidebarAccountAge && account.created_at) {
            var created = new Date(account.created_at);
            var nowDate = new Date();
            var diffMs = nowDate - created;
            var totalMonths = Math.floor(diffMs / (1000 * 60 * 60 * 24 * 30.44));
            var years = Math.floor(totalMonths / 12);
            var months = totalMonths % 12;
            var ageStr = '';
            if (years > 0) ageStr += years + (years === 1 ? ' year' : ' years');
            if (months > 0) ageStr += (ageStr ? ', ' : '') + months + (months === 1 ? ' month' : ' months');
            sidebarAccountAge.textContent = ageStr || 'Less than a month';
        } else if (sidebarAccountAge) {
            sidebarAccountAge.textContent = '-';
        }

        var sidebarExisting = document.getElementById('sidebarExistingSenderIds');
        if (sidebarExisting) {
            var approvedCount = account.approved_sender_ids !== undefined ? account.approved_sender_ids : 0;
            sidebarExisting.textContent = approvedCount + ' approved';
        }

        var sidebarRejections = document.getElementById('sidebarPrevRejections');
        if (sidebarRejections) {
            sidebarRejections.textContent = account.rejected_sender_ids !== undefined ? account.rejected_sender_ids : 0;
        }

        var viewAccountLink = document.getElementById('sidebarViewAccountLink');
        if (viewAccountLink && account.id) {
            viewAccountLink.href = '/admin/accounts/' + account.id;
        }
    }

    updateActionButtonVisibility(data.workflow_status);

    // =====================================================
    // Section A: SenderID Details
    // =====================================================
    var reviewSenderIdEl = document.getElementById('reviewSenderId');
    if (reviewSenderIdEl) {
        reviewSenderIdEl.textContent = data.sender_id_value || '-';
    }

    var reviewTypeEl = document.getElementById('reviewType');
    if (reviewTypeEl) {
        var typeMap = {
            'ALPHA': { label: 'Alphanumeric', cls: 'alphanumeric' },
            'NUMERIC': { label: 'VMN', cls: 'vmn' },
            'SHORTCODE': { label: 'Shortcode', cls: 'shortcode' }
        };
        var typeInfo = typeMap[data.sender_type] || { label: escapeHtml(data.sender_type || '-'), cls: 'alphanumeric' };
        reviewTypeEl.className = 'senderid-type-badge ' + typeInfo.cls;
        reviewTypeEl.textContent = typeInfo.label;
    }

    // =====================================================
    // Section B: Brand Representation
    // =====================================================
    var reviewBrandEl = document.getElementById('reviewBrand');
    if (reviewBrandEl) {
        reviewBrandEl.textContent = data.brand_name || '-';
    }

    var reviewPermissionEl = document.getElementById('reviewPermission');
    if (reviewPermissionEl) {
        if (data.permission_confirmed) {
            reviewPermissionEl.innerHTML = '<span class="senderid-yes-no yes"><i class="fas fa-check"></i> Yes</span>';
        } else {
            reviewPermissionEl.innerHTML = '<span class="senderid-yes-no no"><i class="fas fa-times"></i> No</span>';
        }
    }

    var explanationText = data.permission_explanation || data.use_case_description || '';
    var reviewExplanationEl = document.getElementById('reviewExplanation');
    if (reviewExplanationEl) {
        reviewExplanationEl.textContent = '"' + explanationText + '"';
    }
    if (explanationText) {
        var sectionBRows = document.querySelectorAll('#senderIdReviewSummary .senderid-review-section:nth-child(2) .senderid-review-row');
        if (sectionBRows.length < 3) {
            var explanationRow = document.createElement('div');
            explanationRow.className = 'senderid-review-row';
            explanationRow.innerHTML = '<span class="senderid-review-label">Explanation</span>' +
                '<span class="senderid-review-value" style="max-width: 70%;">' +
                '<div class="senderid-explanation" id="reviewExplanation">"' + escapeHtml(explanationText) + '"</div>' +
                '</span>';
            var sectionB = document.querySelectorAll('#senderIdReviewSummary .senderid-review-section')[1];
            if (sectionB) {
                sectionB.appendChild(explanationRow);
            }
        }
    }

    // =====================================================
    // Section C: Intended Usage
    // =====================================================
    var reviewChannelsEl = document.getElementById('reviewChannels');
    if (reviewChannelsEl) {
        var channels = ['Portal', 'Inbox', 'Email-to-SMS', 'API'];
        var channelHtml = '';
        channels.forEach(function(ch) {
            channelHtml += '<span class="senderid-channel-pill enabled"><i class="fas fa-check"></i> ' + escapeHtml(ch) + '</span>';
        });
        reviewChannelsEl.innerHTML = channelHtml;
    }

    var useCaseValue = data.use_case || '';
    var useCaseFormatted = useCaseValue ? useCaseValue.charAt(0).toUpperCase() + useCaseValue.slice(1) : '-';
    var reviewUseCaseEl = document.getElementById('reviewUseCase');
    if (reviewUseCaseEl) {
        reviewUseCaseEl.textContent = useCaseFormatted;
    } else if (useCaseValue) {
        var sectionC = document.querySelectorAll('#senderIdReviewSummary .senderid-review-section')[2];
        if (sectionC) {
            var useCaseRow = document.createElement('div');
            useCaseRow.className = 'senderid-review-row';
            useCaseRow.innerHTML = '<span class="senderid-review-label">Primary Use Case</span>' +
                '<span class="senderid-review-value" id="reviewUseCase">' + escapeHtml(useCaseFormatted) + '</span>';
            sectionC.appendChild(useCaseRow);
        }
    }

    var descriptionText = data.use_case_description || '';
    var reviewDescriptionEl = document.getElementById('reviewDescription');
    if (reviewDescriptionEl) {
        reviewDescriptionEl.textContent = descriptionText;
    } else if (descriptionText) {
        var sectionC2 = document.querySelectorAll('#senderIdReviewSummary .senderid-review-section')[2];
        if (sectionC2) {
            var descRow = document.createElement('div');
            descRow.className = 'senderid-review-row';
            descRow.innerHTML = '<span class="senderid-review-label">Description</span>' +
                '<span class="senderid-review-value" id="reviewDescription">' + escapeHtml(descriptionText) + '</span>';
            sectionC2.appendChild(descRow);
        }
    }

    // =====================================================
    // Section D: Validation Summary (inline in overview)
    // =====================================================
    var sectionD = document.querySelectorAll('#senderIdReviewSummary .senderid-review-section')[3];
    if (sectionD) {
        var sid = data.sender_id_value || '';
        var sType = (data.sender_type || 'ALPHA').toUpperCase();

        var charPass = sType === 'ALPHA' ? /^[A-Za-z0-9]+$/.test(sid) : /^[0-9+]+$/.test(sid);
        var maxLen = sType === 'ALPHA' ? 11 : 15;
        var minLen = 3;
        var lenPass = sid.length >= minLen && sid.length <= maxLen;
        var restrictedPass = !/[\s!@#$%^&*()_=\[\]{};':"\\|,.<>\/?]/.test(sid);
        var ukPass = charPass && lenPass;

        var validationChecks = [
            { label: 'Character Compliance', pass: charPass, passMsg: 'Only allowed characters used (A-Z, a-z, 0-9)', failMsg: 'Contains invalid characters' },
            { label: 'Length Compliance', pass: lenPass, passMsg: 'Within ' + minLen + '-' + maxLen + ' character limit (' + sid.length + ' chars)', failMsg: 'Outside allowed length (' + sid.length + ' chars, limit ' + minLen + '-' + maxLen + ')' },
            { label: 'Restricted Characters', pass: restrictedPass, passMsg: 'No restricted characters detected', failMsg: 'Contains restricted characters' },
            { label: 'UK Rules', pass: ukPass, passMsg: 'Complies with UK carrier requirements', failMsg: 'May require additional validation' }
        ];

        var validationItems = sectionD.querySelectorAll('.senderid-validation-item');
        validationChecks.forEach(function(check, i) {
            if (validationItems[i]) {
                var iconEl = validationItems[i].querySelector('.senderid-validation-icon');
                var textEl = validationItems[i].querySelector('.senderid-validation-text');
                if (iconEl) {
                    iconEl.className = 'senderid-validation-icon ' + (check.pass ? 'pass' : 'fail');
                    iconEl.innerHTML = '<i class="fas ' + (check.pass ? 'fa-check' : 'fa-times') + '"></i>';
                }
                if (textEl) {
                    textEl.innerHTML = '<strong>' + escapeHtml(check.label) + ':</strong> ' + escapeHtml(check.pass ? check.passMsg : check.failMsg);
                }
            }
        });
    }

    // =====================================================
    // Section E: Submission Metadata
    // =====================================================
    var metadataValues = document.querySelectorAll('.senderid-metadata-grid .senderid-metadata-value');
    if (metadataValues.length >= 8) {
        var createdYear = data.created_at ? new Date(data.created_at).getFullYear() : new Date().getFullYear();
        var versionId = 'SID-' + createdYear + '-' + String(data.id || 0).padStart(5, '0') + '-v' + (data.version || 1);
        metadataValues[0].textContent = versionId;

        metadataValues[1].textContent = data.created_by || '-';

        metadataValues[2].textContent = (account && account.company_name) ? account.company_name : '-';

        metadataValues[3].textContent = '-';

        var formatDate = function(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleString('en-GB', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        };
        metadataValues[4].textContent = formatDate(data.created_at);
        metadataValues[5].textContent = formatDate(data.submitted_at);
        metadataValues[6].textContent = formatDate(data.reviewed_at || data.created_at);

        metadataValues[7].innerHTML = '<span class="senderid-validation-status not-started"><i class="fas fa-minus-circle"></i> Not Started</span>';
    }
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
    var btnIds = ['btnStartReview', 'btnApprove', 'btnReject', 'btnRequestInfo', 'btnSuspend', 'btnReactivate', 'btnRevoke'];
    var noActionsMsg = document.getElementById('noActionsMsg');

    btnIds.forEach(function(id) {
        var btn = document.getElementById(id);
        if (btn) btn.style.display = 'none';
    });
    if (noActionsMsg) noActionsMsg.style.display = 'none';

    var shownCount = 0;
    function showBtn(id) {
        var btn = document.getElementById(id);
        if (btn) { btn.style.display = ''; shownCount++; }
    }

    switch (status) {
        case 'submitted':
            showBtn('btnStartReview');
            break;
        case 'in_review':
            showBtn('btnApprove');
            showBtn('btnReject');
            showBtn('btnRequestInfo');
            break;
        case 'pending_info':
        case 'info_provided':
            showBtn('btnApprove');
            showBtn('btnReject');
            showBtn('btnRequestInfo');
            break;
        case 'approved':
            showBtn('btnSuspend');
            showBtn('btnRevoke');
            break;
        case 'suspended':
            showBtn('btnReactivate');
            showBtn('btnRevoke');
            break;
    }

    if (shownCount === 0 && noActionsMsg) {
        noActionsMsg.style.display = '';
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

function switchNotesTab(tab, evt) {
    var el = evt ? (evt.target || evt.srcElement) : this;
    var modal = document.getElementById('adminActionsModal');
    if (!modal) return;
    modal.querySelectorAll('.notes-tab').forEach(function(t) {
        t.classList.remove('active');
        t.style.color = '#64748b';
        t.style.background = '#fff';
        t.style.borderBottom = 'none';
    });
    modal.querySelectorAll('.notes-tab-pane').forEach(function(c) {
        c.style.display = 'none';
        c.classList.remove('active');
    });

    if (el) {
        el.classList.add('active');
        el.style.color = 'var(--admin-primary, #1e3a5f)';
        el.style.background = '#f8fafc';
        el.style.borderBottom = '2px solid var(--admin-primary, #1e3a5f)';
    }
    var pane = document.getElementById('tab-' + tab);
    if (pane) { pane.style.display = 'block'; pane.classList.add('active'); }
}

function showToast(message, type) {
    type = type || 'success';
    var colors = {
        success: { bg: '#059669', icon: 'fa-check-circle' },
        error: { bg: '#dc2626', icon: 'fa-times-circle' },
        warning: { bg: '#d97706', icon: 'fa-exclamation-triangle' },
        info: { bg: '#1e3a5f', icon: 'fa-info-circle' }
    };
    var c = colors[type] || colors.info;
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:99999;background:' + c.bg + ';color:#fff;padding:0.75rem 1.25rem;border-radius:8px;font-size:0.85rem;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.2);display:flex;align-items:center;gap:0.5rem;animation:slideInRight 0.3s ease;max-width:400px;';
    toast.innerHTML = '<i class="fas ' + c.icon + '"></i> ' + message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; }, 4000);
    setTimeout(function() { toast.remove(); }, 4500);
}

function performAction(action, body, successMsg) {
    $.ajax({
        url: '/admin/api/sender-ids/' + senderIdUuid + '/' + action,
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify(body || {}),
        success: function(response) {
            if (response.success) {
                var modal = document.getElementById('adminActionsModal');
                if (modal) {
                    var instance = bootstrap.Modal.getInstance(modal);
                    if (instance) instance.hide();
                }
                showToast(successMsg || response.message || 'Action completed.', 'success');
                loadSenderIdDetail();
            } else {
                showToast(response.error || 'Action failed.', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Action failed.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function showConfirmModal(options) {
    var modal = document.getElementById('confirmActionModal');
    document.getElementById('confirmModalIcon').className = 'fas ' + (options.icon || 'fa-question-circle') + ' me-2';
    document.getElementById('confirmModalTitle').textContent = options.title || 'Confirm Action';
    document.getElementById('confirmModalMessage').textContent = options.message || 'Are you sure?';
    
    var headerEl = document.getElementById('confirmModalHeader');
    headerEl.style.background = options.headerBg || 'linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%)';
    
    var confirmBtn = document.getElementById('confirmModalBtn');
    confirmBtn.className = 'btn btn-sm ' + (options.btnClass || 'btn-primary');
    confirmBtn.innerHTML = '<i class="fas ' + (options.btnIcon || 'fa-check') + ' me-1"></i> ' + (options.btnText || 'Confirm');
    confirmBtn.onclick = function() {
        var bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
        if (options.onConfirm) options.onConfirm();
    };

    var hasInput = !!options.inputRequired;
    var inputGroup = document.getElementById('confirmModalInputGroup');
    var inputEl = document.getElementById('confirmModalInput');
    var inputLabel = document.getElementById('confirmModalInputLabel');
    inputGroup.style.display = hasInput ? '' : 'none';
    if (hasInput) {
        inputEl.value = '';
        inputEl.placeholder = options.inputPlaceholder || '';
        inputLabel.textContent = options.inputLabel || 'Reason';
        confirmBtn.disabled = true;
        inputEl.oninput = function() {
            confirmBtn.disabled = inputEl.value.trim().length < (options.inputMinLength || 5);
        };
        confirmBtn.onclick = function() {
            if (inputEl.value.trim().length < (options.inputMinLength || 5)) return;
            var bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
            if (options.onConfirm) options.onConfirm(inputEl.value.trim());
        };
    }

    var warningEl = document.getElementById('confirmModalWarning');
    if (options.warning) {
        warningEl.style.display = '';
        warningEl.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> ' + options.warning;
    } else {
        warningEl.style.display = 'none';
    }

    new bootstrap.Modal(modal).show();
}

function startReview() {
    showConfirmModal({
        title: 'Start Review',
        message: 'Begin reviewing this SenderID registration request?',
        icon: 'fa-search',
        headerBg: 'linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%)',
        btnClass: 'btn-primary',
        btnIcon: 'fa-search',
        btnText: 'Start Review',
        onConfirm: function() {
            performAction('review', {}, 'SenderID is now in review.');
        }
    });
}

function renderCommentThread(comments) {
    var section = document.getElementById('commentThreadSection');
    var body = document.getElementById('commentThreadBody');
    var countEl = document.getElementById('commentCount');

    if (!comments || comments.length === 0) {
        if (section) section.style.display = 'none';
        return;
    }

    if (section) section.style.display = '';
    if (countEl) countEl.textContent = comments.length;

    var html = '';
    comments.forEach(function(comment) {
        var isAdmin = comment.created_by_actor_type === 'admin';
        var isCustomer = comment.created_by_actor_type === 'customer';
        var bgColor = isAdmin ? '#f0f4ff' : (isCustomer ? '#f0fdf4' : '#f8fafc');
        var borderColor = isAdmin ? '#dbeafe' : (isCustomer ? '#bbf7d0' : '#e2e8f0');
        var icon = isAdmin ? 'fa-shield-alt' : (isCustomer ? 'fa-user' : 'fa-robot');
        var iconColor = isAdmin ? '#3b82f6' : (isCustomer ? '#22c55e' : '#94a3b8');
        var label = isAdmin ? 'Admin' : (isCustomer ? 'Customer' : 'System');
        var date = comment.created_at ? new Date(comment.created_at).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';

        html += '<div style="background: ' + bgColor + '; border: 1px solid ' + borderColor + '; border-radius: 6px; padding: 0.75rem; margin-bottom: 0.75rem;">';
        html += '<div style="display: flex; align-items: center; margin-bottom: 0.5rem;">';
        html += '<i class="fas ' + icon + ' me-2" style="color: ' + iconColor + ';"></i>';
        html += '<strong style="font-size: 0.8rem;">' + label + '</strong>';
        if (comment.created_by_name) html += '<span class="text-muted small ms-2">(' + escapeHtml(comment.created_by_name) + ')</span>';
        html += '<span class="text-muted small ms-auto">' + date + '</span>';
        html += '</div>';
        html += '<div style="font-size: 0.85rem; line-height: 1.6; white-space: pre-wrap;">' + escapeHtml(comment.comment_text) + '</div>';
        if (comment.comment_type === 'customer' && isCustomer) {
            html += '<div style="margin-top: 0.5rem;"><span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.7rem;">Customer Response</span></div>';
        }
        html += '</div>';
    });

    if (body) body.innerHTML = html;
}

function returnToCustomer() {
    var rejectPanel = document.getElementById('rejectModalInline');
    if (rejectPanel) rejectPanel.style.display = 'none';

    var panel = document.getElementById('returnToCustomerPanel');
    if (panel) {
        var textarea = document.getElementById('returnInfoText');
        if (textarea) {
            textarea.value = '';
            textarea.classList.remove('is-invalid');
        }
        var charCount = document.getElementById('returnInfoCharCount');
        if (charCount) charCount.textContent = '0';
        var validation = document.getElementById('returnInfoValidation');
        if (validation) validation.style.display = 'none';

        panel.style.display = '';
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        if (textarea) {
            textarea.addEventListener('input', function() {
                var count = this.value.length;
                var charEl = document.getElementById('returnInfoCharCount');
                if (charEl) charEl.textContent = count.toLocaleString();
                if (count >= 5) {
                    this.classList.remove('is-invalid');
                }
            });
        }
    }
}

function closeReturnPanel() {
    var panel = document.getElementById('returnToCustomerPanel');
    if (panel) panel.style.display = 'none';
}

function confirmReturnToCustomer() {
    var textarea = document.getElementById('returnInfoText');
    var notes = textarea ? textarea.value.trim() : '';

    if (!notes || notes.length < 5) {
        if (textarea) textarea.classList.add('is-invalid');
        var validation = document.getElementById('returnInfoValidation');
        if (validation) validation.style.display = 'block';
        return;
    }

    var btn = document.getElementById('btnConfirmReturn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    }

    $.ajax({
        url: '/admin/api/sender-ids/' + senderIdUuid + '/request-info',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({ notes: notes }),
        success: function(response) {
            if (response.success) {
                closeReturnPanel();
                var modal = document.getElementById('adminActionsModal');
                if (modal) {
                    var instance = bootstrap.Modal.getInstance(modal);
                    if (instance) instance.hide();
                }
                loadSenderIdDetail();

                var toast = document.createElement('div');
                toast.className = 'alert alert-success alert-dismissible fade show';
                toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: none; border-left: 4px solid #22c55e;';
                toast.innerHTML = '<div class="d-flex align-items-center"><i class="fas fa-check-circle text-success me-2" style="font-size: 1.2rem;"></i><div><strong>Returned to Customer</strong><br><small class="text-muted">Customer has been notified and your comments are visible to them.</small></div></div><button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.body.appendChild(toast);
                setTimeout(function() { toast.remove(); }, 5000);
            } else {
                showToast(response.error || 'Failed to return to customer.', 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send & Return';
                }
            }
        },
        error: function(xhr) {
            var msg = 'Failed to return to customer.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send & Return';
            }
        }
    });
}

function showRejectModal() {
    var returnPanel = document.getElementById('returnToCustomerPanel');
    if (returnPanel) returnPanel.style.display = 'none';

    var panel = document.getElementById('rejectModalInline');
    if (panel) {
        var textarea = document.getElementById('rejectReasonText');
        if (textarea) textarea.value = '';
        panel.style.display = '';
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function confirmRejectInline() {
    var reason = document.getElementById('rejectReasonText').value.trim();
    if (!reason || reason.length < 10) {
        showToast('Please provide a rejection reason (minimum 10 characters).', 'warning');
        return;
    }

    $.ajax({
        url: '/admin/api/sender-ids/' + senderIdUuid + '/reject',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({ reason: reason }),
        success: function(response) {
            if (response.success) {
                document.getElementById('rejectModalInline').style.display = 'none';
                bootstrap.Modal.getInstance(document.getElementById('adminActionsModal')).hide();
                showToast('SenderID rejected.', 'success');
                loadSenderIdDetail();
            } else {
                showToast(response.error || 'Failed to reject.', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to reject';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function approveSenderId() {
    showConfirmModal({
        title: 'Approve SenderID',
        message: 'This will approve the SenderID registration request and make it available for use. The customer will be notified.',
        icon: 'fa-check-circle',
        headerBg: 'linear-gradient(135deg, #059669 0%, #10b981 100%)',
        btnClass: 'btn-success',
        btnIcon: 'fa-check-circle',
        btnText: 'Approve SenderID',
        onConfirm: function() {
            performAction('approve', { notes: 'Manual approval by admin' }, 'SenderID approved successfully.');
        }
    });
}

function suspendSenderId() {
    showConfirmModal({
        title: 'Suspend SenderID',
        message: 'This will suspend the SenderID and prevent it from being used for sending messages until reactivated.',
        icon: 'fa-pause-circle',
        headerBg: 'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)',
        btnClass: 'btn-warning',
        btnIcon: 'fa-pause-circle',
        btnText: 'Suspend SenderID',
        inputRequired: true,
        inputLabel: 'Suspension Reason *',
        inputPlaceholder: 'Explain why this SenderID is being suspended (min 5 characters)...',
        inputMinLength: 5,
        onConfirm: function(reason) {
            performAction('suspend', { reason: reason }, 'SenderID suspended.');
        }
    });
}

function reactivateSenderId() {
    showConfirmModal({
        title: 'Reactivate SenderID',
        message: 'This will reactivate the suspended SenderID, making it available for sending messages again.',
        icon: 'fa-play-circle',
        headerBg: 'linear-gradient(135deg, #059669 0%, #10b981 100%)',
        btnClass: 'btn-success',
        btnIcon: 'fa-play-circle',
        btnText: 'Reactivate',
        onConfirm: function() {
            performAction('reactivate', { notes: 'Reactivated by admin' }, 'SenderID reactivated.');
        }
    });
}

function revokeSenderId() {
    showConfirmModal({
        title: 'Revoke SenderID',
        message: 'This will permanently revoke the SenderID. This action cannot be undone.',
        icon: 'fa-ban',
        headerBg: 'linear-gradient(135deg, #dc2626 0%, #ef4444 100%)',
        btnClass: 'btn-danger',
        btnIcon: 'fa-ban',
        btnText: 'Revoke Permanently',
        warning: 'WARNING: This action is permanent and cannot be reversed.',
        inputRequired: true,
        inputLabel: 'Revocation Reason *',
        inputPlaceholder: 'Explain why this SenderID is being permanently revoked (min 5 characters)...',
        inputMinLength: 5,
        onConfirm: function(reason) {
            performAction('revoke', { reason: reason }, 'SenderID revoked permanently.');
        }
    });
}

function submitToExternalProvider() {
    showConfirmModal({
        title: 'Submit to BrandAssure',
        message: 'This will submit the SenderID to BrandAssure for external validation. The process may take up to 48 hours.',
        icon: 'fa-external-link-alt',
        headerBg: 'linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%)',
        btnClass: 'btn-primary',
        btnIcon: 'fa-paper-plane',
        btnText: 'Submit for Validation',
        onConfirm: function() {
            if (typeof UNIFIED_APPROVAL !== 'undefined') {
                var entity = UNIFIED_APPROVAL.getCurrentEntity();
                UNIFIED_APPROVAL.submitToExternalProvider(entity ? entity.data : {});
            }
        }
    });
}

function forceApprove() {
    showConfirmModal({
        title: 'Enterprise Override — Force Approve',
        message: 'This will approve the SenderID bypassing all validation checks. This action is logged with CRITICAL severity for audit compliance.',
        icon: 'fa-exclamation-triangle',
        headerBg: 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)',
        btnClass: 'btn-danger',
        btnIcon: 'fa-bolt',
        btnText: 'Force Approve',
        warning: 'ENTERPRISE OVERRIDE: This bypasses all validation and is logged with CRITICAL severity.',
        inputRequired: true,
        inputLabel: 'Override Reason (required for audit) *',
        inputPlaceholder: 'Enter the business justification for this override...',
        inputMinLength: 5,
        onConfirm: function(reason) {
            performAction('approve', { notes: 'FORCE APPROVE: ' + reason }, 'SenderID force approved (enterprise override).');
        }
    });
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
        showToast('Please enter a note.', 'warning');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ADD_INTERNAL_NOTE', senderIdUuid, { note: note.substring(0, 100) }, 'LOW');
    }
    
    textarea.value = '';
    showToast('Internal note added.', 'success');
}

function previewCustomerMessage() {
    var textarea = document.querySelector('#tab-customer .notes-textarea');
    var message = textarea ? textarea.value : '';
    showConfirmModal({
        title: 'Message Preview',
        message: message || '(No message entered)',
        icon: 'fa-eye',
        headerBg: 'linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%)',
        btnClass: 'btn-secondary',
        btnIcon: 'fa-times',
        btnText: 'Close',
        onConfirm: function() {}
    });
}

function sendCustomerMessage() {
    var textarea = document.querySelector('#tab-customer .notes-textarea');
    if (!textarea) return;
    var message = textarea.value.trim();
    if (!message) {
        showToast('Please enter a message.', 'warning');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('SEND_CUSTOMER_MESSAGE', senderIdUuid, {}, 'MEDIUM');
    }
    
    textarea.value = '';
    showToast('Message sent to customer.', 'success');
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
.admin-action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
    width: 100%;
    text-align: left;
}
.admin-action-btn:hover {
    transform: translateX(2px);
}
.admin-action-btn.approve {
    background: #d9f99d;
    color: #3f6212;
    border-color: #a3e635;
}
.admin-action-btn.approve:hover {
    background: #bef264;
}
.admin-action-btn.reject {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}
.admin-action-btn.reject:hover {
    background: #fecaca;
}
.admin-action-btn.return {
    background: #fef3c7;
    color: #92400e;
    border-color: #fcd34d;
}
.admin-action-btn.return:hover {
    background: #fde68a;
}
.admin-action-btn.submit-provider {
    background: #dbeafe;
    color: #1e40af;
    border-color: #93c5fd;
}
.admin-action-btn.submit-provider:hover {
    background: #bfdbfe;
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
            <div class="modal-body" style="padding: 1.25rem;">
                <div class="admin-action-group">
                    <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.75rem;">ACTIONS</div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;" id="senderIdActionButtons">
                        <button id="btnStartReview" class="admin-action-btn approve" onclick="startReview()" style="display:none;">
                            <i class="fas fa-search"></i>
                            <span>Start Review</span>
                        </button>
                        <button id="btnApprove" class="admin-action-btn approve" onclick="approveSenderId()" style="display:none;">
                            <i class="fas fa-check-circle"></i>
                            <span>Approve</span>
                        </button>
                        <button id="btnReject" class="admin-action-btn reject" onclick="showRejectModal()" style="display:none;">
                            <i class="fas fa-times-circle"></i>
                            <span>Reject</span>
                        </button>
                        <button id="btnRequestInfo" class="admin-action-btn return" onclick="returnToCustomer()" style="display:none;">
                            <i class="fas fa-reply"></i>
                            <span>Request Info</span>
                        </button>
                        <button id="btnSuspend" class="admin-action-btn submit-provider" onclick="suspendSenderId()" style="display:none;">
                            <i class="fas fa-pause-circle"></i>
                            <span>Suspend</span>
                        </button>
                        <button id="btnReactivate" class="admin-action-btn approve" onclick="reactivateSenderId()" style="display:none;">
                            <i class="fas fa-play-circle"></i>
                            <span>Reactivate</span>
                        </button>
                        <button id="btnRevoke" class="admin-action-btn reject" onclick="revokeSenderId()" style="display:none;">
                            <i class="fas fa-ban"></i>
                            <span>Revoke (Permanent)</span>
                        </button>
                        <div id="noActionsMsg" style="display:none; padding: 0.75rem 1rem; background: #f1f5f9; border-radius: 6px; font-size: 0.8rem; color: #64748b;">
                            <i class="fas fa-info-circle me-2"></i>No actions available for the current status.
                        </div>
                    </div>
                </div>

                <div id="commentThreadSection" style="display: none; margin-top: 1rem;">
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                            <h6 style="margin: 0; font-size: 0.85rem; color: #1e3a5f;"><i class="fas fa-comments me-2"></i>Comment Thread</h6>
                            <span id="commentCount" class="badge" style="background: var(--admin-primary, #1e3a5f); color: #fff; font-size: 0.7rem;">0</span>
                        </div>
                        <div id="commentThreadBody" style="max-height: 400px; overflow-y: auto; padding: 1rem;">
                            <div class="text-muted text-center py-2 small">No comments yet.</div>
                        </div>
                    </div>
                </div>

                <div style="height: 1px; background: #e2e8f0; margin: 1rem 0;"></div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                    <div style="display: flex; background: #fff; border-bottom: 1px solid #e2e8f0;">
                        <button class="notes-tab active" onclick="switchNotesTab('internal', event)" style="flex: 1; padding: 0.75rem; font-size: 0.8rem; font-weight: 500; color: var(--admin-primary, #1e3a5f); background: #f8fafc; border: none; cursor: pointer; border-bottom: 2px solid var(--admin-primary, #1e3a5f);">
                            <i class="fas fa-lock me-1"></i> Internal Notes
                        </button>
                        <button class="notes-tab" onclick="switchNotesTab('customer', event)" style="flex: 1; padding: 0.75rem; font-size: 0.8rem; font-weight: 500; color: #64748b; background: #fff; border: none; cursor: pointer;">
                            <i class="fas fa-envelope me-1"></i> Customer Message
                        </button>
                    </div>
                    <div style="padding: 1rem;">
                        <div class="notes-tab-pane active" id="tab-internal">
                            <textarea class="notes-textarea" id="internalNoteText" placeholder="Add internal note (admin-only, never visible to customer)..." style="width: 100%; min-height: 100px; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8rem; resize: vertical;"></textarea>
                            <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                                <button class="btn btn-sm" onclick="addInternalNote()" style="background: var(--admin-primary, #1e3a5f); color: #fff; padding: 0.5rem 1rem; font-size: 0.75rem; border-radius: 4px; border: none;">
                                    <i class="fas fa-plus me-1"></i> Add Note
                                </button>
                            </div>
                        </div>
                        <div class="notes-tab-pane" id="tab-customer" style="display: none;">
                            <textarea class="notes-textarea" id="customerMessageText" placeholder="Message to customer (shown when returned/rejected)..." style="width: 100%; min-height: 100px; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8rem; resize: vertical;"></textarea>
                            <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                                <button class="btn btn-sm" onclick="previewCustomerMessage()" style="background: #fff; color: var(--admin-primary, #1e3a5f); border: 1px solid var(--admin-primary, #1e3a5f); padding: 0.5rem 1rem; font-size: 0.75rem; border-radius: 4px;">
                                    <i class="fas fa-eye me-1"></i> Preview
                                </button>
                                <button class="btn btn-sm" onclick="sendCustomerMessage()" style="background: var(--admin-primary, #1e3a5f); color: #fff; padding: 0.5rem 1rem; font-size: 0.75rem; border-radius: 4px; border: none;">
                                    <i class="fas fa-paper-plane me-1"></i> Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="returnToCustomerPanel" style="display:none; margin-top: 1rem; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #f59e0b; border-radius: 6px; padding: 1rem;">
                    <h6 style="color: #92400e; margin-bottom: 0.75rem;"><i class="fas fa-reply me-2"></i>Return to Customer</h6>
                    <p style="font-size: 0.8rem; color: #78350f; margin-bottom: 0.75rem;">
                        This will change the status to <strong>Returned to Customer</strong> and notify them to provide additional information. Your comments below will be visible to the customer.
                    </p>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem;">What information do you need from the customer? <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="returnInfoText" rows="4" placeholder="e.g. Please provide a letter of authorisation from the brand owner, or proof of business registration under this name..." style="font-size: 0.8rem;"></textarea>
                        <div class="invalid-feedback" id="returnInfoValidation">Please provide details about what information is needed (minimum 5 characters).</div>
                        <div class="d-flex justify-content-end mt-1">
                            <small class="text-muted"><span id="returnInfoCharCount">0</span> / 2,000 characters</small>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button class="btn btn-sm" id="btnConfirmReturn" onclick="confirmReturnToCustomer()" style="background: #f59e0b; color: #fff; border: none; padding: 0.5rem 1rem; font-size: 0.75rem; border-radius: 4px; font-weight: 600;">
                            <i class="fas fa-paper-plane me-1"></i> Send & Return
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="closeReturnPanel()" style="font-size: 0.75rem;">Cancel</button>
                    </div>
                </div>

                <div id="rejectModalInline" style="display:none; margin-top: 1rem; background: #fff5f5; border: 1px solid #fca5a5; border-radius: 6px; padding: 1rem;">
                    <h6 style="color: #991b1b; margin-bottom: 0.75rem;"><i class="fas fa-times-circle me-2"></i>Reject SenderID</h6>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem;">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="rejectReasonText" rows="3" placeholder="Explain why this SenderID is being rejected (min 10 characters)..." style="font-size: 0.8rem;"></textarea>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-sm btn-danger" onclick="confirmRejectInline()"><i class="fas fa-times me-1"></i>Confirm Rejection</button>
                        <button class="btn btn-sm btn-secondary" onclick="document.getElementById('rejectModalInline').style.display='none'">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 8px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-header" id="confirmModalHeader" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: #fff; padding: 1rem 1.25rem; border: none;">
                <h6 class="modal-title" style="margin: 0; font-weight: 600;">
                    <i id="confirmModalIcon" class="fas fa-question-circle me-2"></i>
                    <span id="confirmModalTitle">Confirm Action</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.25rem;">
                <p id="confirmModalMessage" style="font-size: 0.85rem; color: #334155; line-height: 1.6; margin-bottom: 0.75rem;"></p>
                <div id="confirmModalWarning" style="display: none; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; padding: 0.75rem; font-size: 0.8rem; color: #991b1b; font-weight: 500; margin-bottom: 0.75rem;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                </div>
                <div id="confirmModalInputGroup" style="display: none; margin-top: 0.75rem;">
                    <label id="confirmModalInputLabel" class="form-label fw-semibold" style="font-size: 0.8rem;">Reason</label>
                    <textarea id="confirmModalInput" class="form-control form-control-sm" rows="3" style="font-size: 0.8rem;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 0.75rem 1.25rem; background: #f8fafc;">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" style="font-size: 0.8rem;">Cancel</button>
                <button type="button" id="confirmModalBtn" class="btn btn-sm btn-primary" style="font-size: 0.8rem;">
                    <i class="fas fa-check me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

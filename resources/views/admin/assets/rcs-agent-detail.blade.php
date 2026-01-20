@extends('layouts.admin')

@section('title', 'RCS Agent Approval Detail')

@push('styles')
<style>
.detail-page { padding: 1.5rem; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
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

.agent-name-display {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--admin-primary);
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
.status-pill.provisioning-in-progress { background: #c7d2fe; color: #4338ca; }
.status-pill.live { background: #bbf7d0; color: #15803d; }

.context-info {
    display: flex;
    gap: 1.5rem;
    padding: 0.75rem 1rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.context-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}

.context-label { color: #64748b; }
.context-value { color: var(--admin-primary); font-weight: 600; }

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
    margin-bottom: 1rem;
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
    align-items: flex-start;
}

.detail-row:last-child { border-bottom: none; }

.detail-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
    flex-shrink: 0;
}

.detail-value {
    font-size: 0.875rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
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

.brand-color-preview {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 2px solid #e2e8f0;
}

.asset-preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.asset-preview-box {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.asset-preview-header {
    background: #f8fafc;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
}

.asset-preview-content {
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    background: #fafafa;
}

.logo-preview {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 1.5rem;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.hero-preview {
    width: 100%;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    position: relative;
}

.hero-preview .logo-overlay {
    position: absolute;
    bottom: -20px;
    left: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.asset-specs {
    margin-top: 0.5rem;
    font-size: 0.7rem;
    color: #64748b;
    text-align: center;
}

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

.compliance-flag {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.compliance-flag.critical { background: #fef2f2; border: 1px solid #fecaca; }
.compliance-flag.warning { background: #fffbeb; border: 1px solid #fde68a; }
.compliance-flag.info { background: #eff6ff; border: 1px solid #bfdbfe; }
.compliance-flag.clear { background: #f0fdf4; border: 1px solid #bbf7d0; }

.compliance-flag-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
}

.compliance-flag.critical .compliance-flag-icon { background: #ef4444; color: #fff; }
.compliance-flag.warning .compliance-flag-icon { background: #f59e0b; color: #fff; }
.compliance-flag.info .compliance-flag-icon { background: #3b82f6; color: #fff; }
.compliance-flag.clear .compliance-flag-icon { background: #22c55e; color: #fff; }

.compliance-flag-content { flex: 1; }

.compliance-flag-title {
    font-weight: 600;
    font-size: 0.85rem;
    color: #1e293b;
}

.compliance-flag-detail {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.125rem;
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

.test-numbers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.test-number-pill {
    background: #f1f5f9;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-family: 'SF Mono', monospace;
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

.action-btn.primary { background: var(--admin-primary); color: #fff; }
.action-btn.primary:hover { background: var(--admin-accent); }
.action-btn.success { background: #22c55e; color: #fff; }
.action-btn.success:hover { background: #16a34a; }
.action-btn.warning { background: #f59e0b; color: #fff; }
.action-btn.warning:hover { background: #d97706; }
.action-btn.danger { background: #ef4444; color: #fff; }
.action-btn.danger:hover { background: #dc2626; }
.action-btn.outline { background: #fff; border-color: #e2e8f0; color: #475569; }
.action-btn.outline:hover { border-color: var(--admin-primary); color: var(--admin-primary); }
.action-btn.provision { background: linear-gradient(135deg, #059669, #10b981); color: #fff; }
.action-btn.provision:hover { opacity: 0.9; }

.audit-trail { max-height: 300px; overflow-y: auto; }

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

.url-link {
    color: var(--admin-primary);
    text-decoration: none;
    word-break: break-all;
}

.url-link:hover { text-decoration: underline; }

.sidebar-card { margin-bottom: 1rem; }

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
    min-height: 100px;
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
</style>
@endpush

@section('content')
<div class="detail-page">
    <a href="{{ route('admin.approval-queue') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Approval Queue
    </a>

    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="{{ route('admin.approval-queue') }}">Approval Queue</a>
        <span class="separator">/</span>
        <span>RCS Agent Detail</span>
    </div>

    <div class="status-header">
        <span class="agent-name-display" id="agentNameDisplay">Acme Bank Notifications</span>
        <span class="status-pill submitted" id="currentStatus"><i class="fas fa-paper-plane"></i> Submitted</span>
        <div style="margin-left: auto; display: flex; gap: 1rem; font-size: 0.8rem; color: #64748b;">
            <span><i class="fas fa-hashtag me-1"></i>Request ID: <strong>RCS-001</strong></span>
            <span><i class="fas fa-clock me-1"></i>Submitted: <strong>Jan 18, 2026, 2:30 PM</strong></span>
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
        <div class="context-item">
            <i class="fas fa-tag"></i>
            <span class="context-label">Type:</span>
            <span class="context-value">RCS Agent</span>
        </div>
    </div>

    <div class="detail-grid">
        <div class="main-content">
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-robot"></i> Agent Identity
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Agent Name</span>
                        <span class="detail-value">Acme Bank Notifications</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Agent Description</span>
                        <span class="detail-value" style="text-align: left; max-width: 70%;">Official notification service for Acme Bank customers. Receive balance alerts, transaction confirmations, and security notifications.</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Brand Colour</span>
                        <span class="detail-value">
                            <div class="brand-color-preview">
                                <div class="color-swatch" style="background: #1e40af;"></div>
                                <span class="mono">#1e40af</span>
                            </div>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Billing Category</span>
                        <span class="detail-value">Financial Services</span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-images"></i> Brand Assets
                </div>
                <div class="detail-card-body">
                    <div class="asset-preview-grid">
                        <div class="asset-preview-box">
                            <div class="asset-preview-header">Logo (Circular Crop Preview)</div>
                            <div class="asset-preview-content">
                                <div class="logo-preview">AB</div>
                            </div>
                            <div class="asset-specs">224x224px | 42 KB | 1:1 aspect</div>
                        </div>
                        <div class="asset-preview-box">
                            <div class="asset-preview-header">Hero/Banner (Overlap Preview)</div>
                            <div class="asset-preview-content" style="flex-direction: column;">
                                <div class="hero-preview">
                                    <div class="logo-overlay"></div>
                                </div>
                            </div>
                            <div class="asset-specs">1440x448px | 128 KB | 45:14 aspect</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-address-card"></i> Handset Contact Details
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Phone Number</span>
                        <span class="detail-value mono">+44 800 123 4567</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">support@acmebank.com</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Website</span>
                        <span class="detail-value"><a href="#" class="url-link">https://www.acmebank.com</a></span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-file-contract"></i> Legal & Compliance
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Privacy Policy URL</span>
                        <span class="detail-value"><a href="#" class="url-link">https://acmebank.com/privacy</a></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Terms of Service URL</span>
                        <span class="detail-value"><a href="#" class="url-link">https://acmebank.com/terms</a></span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-bullhorn"></i> Use Case & Messaging
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Use Case</span>
                        <span class="detail-value">Transactional Notifications</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Messaging Behaviour</span>
                        <span class="detail-value">One-way notifications only</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Monthly Volume Estimate</span>
                        <span class="detail-value">50,000 - 100,000 messages</span>
                    </div>

                    <div style="margin-top: 1rem;">
                        <div class="detail-label" style="margin-bottom: 0.5rem;">Opt-in Explanation</div>
                        <div class="explanation-box">
                            "Customers opt-in to receive RCS notifications during their online banking registration process. They confirm their preference to receive transaction alerts and security notifications via SMS/RCS on their mobile device."
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <div class="detail-label" style="margin-bottom: 0.5rem;">Opt-out Explanation</div>
                        <div class="explanation-box">
                            "Customers can opt-out by replying STOP to any message, through their online banking settings, or by calling our customer service line."
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-mobile-alt"></i> Test Numbers
                </div>
                <div class="detail-card-body">
                    <div class="test-numbers-list">
                        <span class="test-number-pill">+44 7700 900111</span>
                        <span class="test-number-pill">+44 7700 900222</span>
                        <span class="test-number-pill">+44 7700 900333</span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-building"></i> Company Details (from Account)
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Registered Company Name</span>
                        <span class="detail-value">Acme Corporation Ltd</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Company Number</span>
                        <span class="detail-value mono">12345678</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">VAT Number</span>
                        <span class="detail-value mono">GB123456789</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registered Address</span>
                        <span class="detail-value" style="text-align: right;">123 Business Park<br>London EC1A 1BB<br>United Kingdom</span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-check-double"></i> Asset Validation
                </div>
                <div class="detail-card-body">
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Logo Dimensions</div>
                            <div class="validation-detail">224x224px - meets minimum 224x224px requirement</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Logo File Size</div>
                            <div class="validation-detail">42 KB - under 50 KB limit</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Logo Aspect Ratio</div>
                            <div class="validation-detail">1:1 (square) - correct for circular crop</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Hero Image Dimensions</div>
                            <div class="validation-detail">1440x448px - meets 1440x448px requirement</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Hero File Size</div>
                            <div class="validation-detail">128 KB - under 200 KB limit</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Hero Aspect Ratio</div>
                            <div class="validation-detail">45:14 - correct RCS banner ratio</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-shield-alt"></i> Compliance Flags
                </div>
                <div class="detail-card-body">
                    <div class="compliance-flag warning">
                        <div class="compliance-flag-icon"><i class="fas fa-exclamation"></i></div>
                        <div class="compliance-flag-content">
                            <div class="compliance-flag-title">High-Risk Vertical Detected</div>
                            <div class="compliance-flag-detail">Financial Services category requires additional verification. Confirm FCA authorisation status.</div>
                        </div>
                    </div>
                    <div class="compliance-flag clear">
                        <div class="compliance-flag-icon"><i class="fas fa-check"></i></div>
                        <div class="compliance-flag-content">
                            <div class="compliance-flag-title">Use-Case Alignment</div>
                            <div class="compliance-flag-detail">Transactional notifications aligns with Financial Services billing category.</div>
                        </div>
                    </div>
                    <div class="compliance-flag clear">
                        <div class="compliance-flag-icon"><i class="fas fa-check"></i></div>
                        <div class="compliance-flag-content">
                            <div class="compliance-flag-title">Opt-in/Opt-out Explanation</div>
                            <div class="compliance-flag-detail">Both opt-in and opt-out mechanisms are clearly documented.</div>
                        </div>
                    </div>
                    <div class="compliance-flag clear">
                        <div class="compliance-flag-icon"><i class="fas fa-check"></i></div>
                        <div class="compliance-flag-content">
                            <div class="compliance-flag-title">Volume Assessment</div>
                            <div class="compliance-flag-detail">Estimated volume (50-100k/month) is consistent with account history and use case.</div>
                        </div>
                    </div>
                    <div class="compliance-flag clear">
                        <div class="compliance-flag-icon"><i class="fas fa-check"></i></div>
                        <div class="compliance-flag-content">
                            <div class="compliance-flag-title">Legal Information</div>
                            <div class="compliance-flag-detail">Privacy Policy and Terms of Service URLs provided and accessible.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-panel">
                <div class="action-panel-title"><i class="fas fa-gavel me-2"></i>Admin Actions</div>
                <div class="action-buttons">
                    <button class="action-btn warning" onclick="returnToCustomer()">
                        <i class="fas fa-reply"></i> Return to Customer
                    </button>
                    <button class="action-btn danger" onclick="showRejectModal()">
                        <i class="fas fa-times-circle"></i> Reject
                    </button>
                    <button class="action-btn success" onclick="approveAgent()">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                    <button class="action-btn primary" onclick="submitToProvider()">
                        <i class="fas fa-cloud-upload-alt"></i> Submit to RCS Provider
                    </button>
                    <button class="action-btn outline" onclick="markValidationFailed()">
                        <i class="fas fa-exclamation-triangle"></i> Mark Validation Failed
                    </button>
                    <button class="action-btn provision" onclick="provisionAgent()">
                        <i class="fas fa-rocket"></i> Provision Agent
                    </button>
                </div>
            </div>

            <div class="detail-card notes-section">
                <div class="detail-card-header">
                    <i class="fas fa-sticky-note"></i> Notes & Communication
                </div>
                <div class="detail-card-body">
                    <div class="notes-tabs">
                        <button class="notes-tab active" onclick="switchNotesTab('internal')">Internal Notes</button>
                        <button class="notes-tab" onclick="switchNotesTab('customer')">Customer Message</button>
                    </div>

                    <div class="notes-content active" id="tab-internal">
                        <div class="note-entry">
                            <div class="note-header">
                                <span class="note-author">Michael Chen</span>
                                <span class="note-time">Jan 19, 2026 9:15 AM</span>
                            </div>
                            <div class="note-text">Financial Services vertical - verified FCA register, company is authorised. Proceeding with standard review.</div>
                        </div>
                        <textarea class="notes-textarea" placeholder="Add internal note (admin-only, not visible to customer)..."></textarea>
                        <button class="action-btn primary" style="margin-top: 0.75rem;" onclick="addInternalNote()">
                            <i class="fas fa-plus"></i> Add Note
                        </button>
                    </div>

                    <div class="notes-content" id="tab-customer">
                        <textarea class="notes-textarea" placeholder="Compose message to customer (will be included in status notification email)..."></textarea>
                        <div style="margin-top: 0.75rem; display: flex; gap: 0.75rem;">
                            <button class="action-btn outline" onclick="previewCustomerMessage()">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button class="action-btn primary" onclick="sendCustomerMessage()">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
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
                                <div class="audit-meta">John Smith | Jan 18, 2026, 2:30 PM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-robot"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Asset Validation Complete</div>
                                <div class="audit-meta">System | Jan 18, 2026, 2:31 PM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-flag"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">High-Risk Vertical Flag</div>
                                <div class="audit-meta">System | Jan 18, 2026, 2:31 PM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-eye"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Viewed by Admin</div>
                                <div class="audit-meta">Michael Chen | Jan 19, 2026, 9:10 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-comment"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Internal Note Added</div>
                                <div class="audit-meta">Michael Chen | Jan 19, 2026, 9:15 AM</div>
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
                        <span class="detail-value mono">RCS-001</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SLA Timer</span>
                        <span class="detail-value" style="color: #f59e0b;"><i class="fas fa-hourglass-half me-1"></i>8h remaining</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Risk Level</span>
                        <span class="detail-value"><span class="yes-badge" style="background: #fef3c7; color: #92400e;">Medium</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Assigned Admin</span>
                        <span class="detail-value">Michael Chen</span>
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
                        <span class="detail-label">Existing RCS Agents</span>
                        <span class="detail-value">1 active</span>
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
                <h5 class="modal-title">Reject RCS Agent Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Rejection Reason</label>
                    <select class="form-select" id="rejectReason">
                        <option value="">Select a reason...</option>
                        <option value="brand-guidelines">Brand guidelines violation</option>
                        <option value="asset-quality">Asset quality issues</option>
                        <option value="use-case-mismatch">Use case mismatch</option>
                        <option value="compliance-failure">Compliance requirements not met</option>
                        <option value="verification-failed">Business verification failed</option>
                        <option value="provider-rejected">Rejected by RCS provider</option>
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
<script>
var RCS_ASSET_VALIDATION = {
    logoRequirements: {
        minWidth: 224,
        minHeight: 224,
        maxFileSize: 50 * 1024,
        aspectRatio: '1:1',
        formats: ['PNG', 'JPG', 'JPEG']
    },
    heroRequirements: {
        width: 1440,
        height: 448,
        maxFileSize: 200 * 1024,
        aspectRatio: '45:14',
        formats: ['PNG', 'JPG', 'JPEG']
    },
    
    validateLogo: function(width, height, fileSize) {
        var results = [];
        results.push({
            rule: 'dimensions',
            pass: width >= this.logoRequirements.minWidth && height >= this.logoRequirements.minHeight,
            message: width + 'x' + height + 'px - ' + (width >= this.logoRequirements.minWidth ? 'meets' : 'below') + ' minimum ' + this.logoRequirements.minWidth + 'x' + this.logoRequirements.minHeight + 'px requirement'
        });
        results.push({
            rule: 'fileSize',
            pass: fileSize <= this.logoRequirements.maxFileSize,
            message: Math.round(fileSize / 1024) + ' KB - ' + (fileSize <= this.logoRequirements.maxFileSize ? 'under' : 'over') + ' ' + Math.round(this.logoRequirements.maxFileSize / 1024) + ' KB limit'
        });
        results.push({
            rule: 'aspectRatio',
            pass: width === height,
            message: (width === height ? '1:1 (square)' : width + ':' + height) + ' - ' + (width === height ? 'correct' : 'incorrect') + ' for circular crop'
        });
        return results;
    },
    
    validateHero: function(width, height, fileSize) {
        var results = [];
        results.push({
            rule: 'dimensions',
            pass: width === this.heroRequirements.width && height === this.heroRequirements.height,
            message: width + 'x' + height + 'px - ' + (width === this.heroRequirements.width && height === this.heroRequirements.height ? 'meets' : 'does not meet') + ' ' + this.heroRequirements.width + 'x' + this.heroRequirements.height + 'px requirement'
        });
        results.push({
            rule: 'fileSize',
            pass: fileSize <= this.heroRequirements.maxFileSize,
            message: Math.round(fileSize / 1024) + ' KB - ' + (fileSize <= this.heroRequirements.maxFileSize ? 'under' : 'over') + ' ' + Math.round(this.heroRequirements.maxFileSize / 1024) + ' KB limit'
        });
        var expectedRatio = 45 / 14;
        var actualRatio = width / height;
        var ratioMatch = Math.abs(actualRatio - expectedRatio) < 0.01;
        results.push({
            rule: 'aspectRatio',
            pass: ratioMatch,
            message: this.heroRequirements.aspectRatio + ' - ' + (ratioMatch ? 'correct' : 'incorrect') + ' RCS banner ratio'
        });
        return results;
    }
};

var RCS_COMPLIANCE_FLAGS = {
    highRiskVerticals: ['Financial Services', 'Healthcare', 'Government', 'Pharmaceuticals', 'Insurance', 'Gambling'],
    
    checkUseCaseMismatch: function(useCase, billingCategory) {
        var categoryUseCases = {
            'Financial Services': ['Transactional Notifications', 'Account Alerts', 'Security Notifications'],
            'Retail': ['Promotional', 'Order Updates', 'Delivery Notifications'],
            'Healthcare': ['Appointment Reminders', 'Health Alerts', 'Prescription Notifications']
        };
        var valid = categoryUseCases[billingCategory] && categoryUseCases[billingCategory].includes(useCase);
        return {
            flag: !valid,
            severity: valid ? 'clear' : 'warning',
            message: valid ? 'Use case aligns with billing category' : 'Use case may not align with billing category'
        };
    },
    
    checkOptInExplanation: function(optInText, optOutText) {
        var hasOptIn = optInText && optInText.length > 20;
        var hasOptOut = optOutText && optOutText.length > 20;
        return {
            flag: !(hasOptIn && hasOptOut),
            severity: (hasOptIn && hasOptOut) ? 'clear' : 'critical',
            message: (hasOptIn && hasOptOut) ? 'Opt-in and opt-out mechanisms documented' : 'Missing or incomplete opt-in/opt-out explanation'
        };
    },
    
    checkHighRiskVertical: function(billingCategory) {
        var isHighRisk = this.highRiskVerticals.includes(billingCategory);
        return {
            flag: isHighRisk,
            severity: isHighRisk ? 'warning' : 'clear',
            message: isHighRisk ? 'High-risk vertical requires additional verification' : 'Standard risk vertical'
        };
    },
    
    checkVolumeAnomaly: function(estimatedVolume, accountHistory) {
        var anomaly = estimatedVolume > (accountHistory.avgMonthlyVolume * 5);
        return {
            flag: anomaly,
            severity: anomaly ? 'warning' : 'clear',
            message: anomaly ? 'Estimated volume significantly exceeds historical average' : 'Volume estimate is consistent with account history'
        };
    },
    
    checkLegalInformation: function(privacyUrl, tosUrl) {
        var hasPrivacy = privacyUrl && privacyUrl.startsWith('http');
        var hasTos = tosUrl && tosUrl.startsWith('http');
        return {
            flag: !(hasPrivacy && hasTos),
            severity: (hasPrivacy && hasTos) ? 'clear' : 'critical',
            message: (hasPrivacy && hasTos) ? 'Legal URLs provided and accessible' : 'Missing or invalid legal information URLs'
        };
    }
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('[RCS Agent Detail] Initialized');
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('PAGE_VIEW', 'rcs-agent-detail', { requestId: 'RCS-001' }, 'LOW');
    }
});

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

function returnToCustomer() {
    if (confirm('Return this request to the customer for additional information?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('STATUS_TRANSITION', 'RCS-001', { 
                from: 'submitted', 
                to: 'returned-to-customer' 
            }, 'HIGH');
        }
        alert('Request returned to customer. They will receive a notification email.');
        updateStatus('returned-to-customer', 'Returned to Customer', 'fa-reply');
    }
}

function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
    var reason = document.getElementById('rejectReason').value;
    var message = document.getElementById('rejectMessage').value;
    
    if (!reason) {
        alert('Please select a rejection reason');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('REJECT', 'RCS-001', { reason: reason, message: message }, 'HIGH');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
    updateStatus('rejected', 'Rejected', 'fa-times-circle');
    alert('RCS Agent request rejected. Customer will be notified.');
}

function approveAgent() {
    if (confirm('Approve this RCS Agent request?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('APPROVE', 'RCS-001', {}, 'HIGH');
        }
        updateStatus('approved', 'Approved', 'fa-check-circle');
        alert('RCS Agent approved. Ready for provisioning.');
    }
}

function submitToProvider() {
    if (confirm('Submit this RCS Agent to the RCS Provider for validation?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('SUBMIT_TO_PROVIDER', 'RCS-001', {}, 'MEDIUM');
        }
        updateStatus('validation-in-progress', 'Validation In Progress', 'fa-spinner fa-spin');
        alert('Submitted to RCS Provider. You will be notified when validation completes.');
    }
}

function markValidationFailed() {
    if (confirm('Mark provider validation as failed?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('VALIDATION_FAILED', 'RCS-001', {}, 'HIGH');
        }
        updateStatus('validation-failed', 'Validation Failed', 'fa-exclamation-circle');
    }
}

function provisionAgent() {
    if (confirm('Provision this RCS Agent? This will make it live on the network.')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('PROVISION_AGENT', 'RCS-001', {}, 'HIGH');
        }
        updateStatus('provisioning-in-progress', 'Provisioning In Progress', 'fa-cog fa-spin');
        
        setTimeout(function() {
            updateStatus('live', 'Live', 'fa-broadcast-tower');
            if (typeof AdminControlPlane !== 'undefined') {
                AdminControlPlane.logAdminAction('AGENT_LIVE', 'RCS-001', {}, 'HIGH');
            }
            alert('RCS Agent is now live on the network.');
        }, 2000);
    }
}

function updateStatus(status, label, icon) {
    var pill = document.getElementById('currentStatus');
    pill.className = 'status-pill ' + status;
    pill.innerHTML = '<i class="fas ' + icon + '"></i> ' + label;
}

function addInternalNote() {
    var textarea = document.querySelector('#tab-internal .notes-textarea');
    var note = textarea.value.trim();
    if (!note) {
        alert('Please enter a note');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ADD_INTERNAL_NOTE', 'RCS-001', { note: note.substring(0, 100) }, 'LOW');
    }
    
    textarea.value = '';
    alert('Internal note added.');
}

function previewCustomerMessage() {
    var message = document.querySelector('#tab-customer .notes-textarea').value;
    alert('Preview:\n\n' + (message || '(No message entered)'));
}

function sendCustomerMessage() {
    var message = document.querySelector('#tab-customer .notes-textarea').value.trim();
    if (!message) {
        alert('Please enter a message');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('SEND_CUSTOMER_MESSAGE', 'RCS-001', {}, 'MEDIUM');
    }
    
    document.querySelector('#tab-customer .notes-textarea').value = '';
    alert('Message sent to customer.');
}
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Settings - ' . $account_id)

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-primary-hover: #2d5a87;
    --admin-primary-light: rgba(30, 58, 95, 0.08);
}

.admin-page { padding: 1.5rem; }

.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}
.settings-header-info h4 {
    color: var(--admin-primary);
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.settings-header-info .account-id-text {
    font-size: 0.875rem;
    color: #6c757d;
}
.settings-header-pills {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.settings-header-actions {
    display: flex;
    gap: 0.5rem;
}

.pill-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.pill-live { background: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.pill-test { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
.pill-suspended { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
.pill-closed { background: rgba(108, 117, 125, 0.25); color: #495057; }
.pill-pending { background: rgba(255, 193, 7, 0.15); color: #856404; }

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
}
.nav-tabs .nav-link:hover { color: var(--admin-primary); border-color: transparent; }
.nav-tabs .nav-link.active {
    color: var(--admin-primary);
    background: transparent;
    border-color: transparent transparent var(--admin-primary) transparent;
}

.settings-card {
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.5rem;
}
.settings-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0.75rem 0.75rem 0 0;
}
.settings-card-header h6 {
    margin: 0;
    font-weight: 600;
    color: var(--admin-primary);
}
.settings-card-body {
    padding: 1.25rem;
}

.settings-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
}
.settings-row:last-child { border-bottom: none; }
.settings-label {
    font-weight: 500;
    color: #495057;
}
.settings-value {
    font-weight: 600;
    color: #2c2c2c;
}

.btn-admin-primary {
    background-color: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #fff;
}
.btn-admin-primary:hover {
    background-color: var(--admin-primary-hover);
    border-color: var(--admin-primary-hover);
    color: #fff;
}
.btn-admin-outline {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
}
.btn-admin-outline:hover {
    background-color: var(--admin-primary);
    color: #fff;
}

.spam-filter-option {
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
}
.spam-filter-option:hover {
    border-color: rgba(30, 58, 95, 0.4);
    background: rgba(30, 58, 95, 0.03);
}
.spam-filter-option.selected {
    border-color: var(--admin-primary);
    background: rgba(30, 58, 95, 0.06);
}
.spam-filter-option .option-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.375rem;
}
.spam-filter-option .option-header .option-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}
.spam-filter-option .option-title {
    font-weight: 600;
    font-size: 0.875rem;
    color: #333;
}
.spam-filter-option .option-desc {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
    padding-left: 2.5rem;
}
.spam-filter-option .option-check {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    color: var(--admin-primary);
    font-size: 1rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.spam-filter-option.selected .option-check { opacity: 1; }

.icon-enforced { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
.icon-monitoring { background: rgba(255, 193, 7, 0.15); color: #856404; }
.icon-off { background: rgba(108, 117, 125, 0.15); color: #6c757d; }

.credit-wallet-table {
    width: 100%;
    font-size: 0.8rem;
}
.credit-wallet-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    padding: 0.5rem 0.75rem;
    border-bottom: 2px solid #e9ecef;
}
.credit-wallet-table td {
    padding: 0.5rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
}

.credit-summary-box {
    background: linear-gradient(135deg, #fff 0%, #f0f4f8 100%);
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
.credit-summary-metric {
    text-align: center;
}
.credit-summary-metric .metric-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    margin-bottom: 0.25rem;
}
.credit-summary-metric .metric-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c2c2c;
}

.toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
}

.status-change-warning {
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.4);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-top: 0.75rem;
    font-size: 0.8rem;
    color: #856404;
    display: none;
}

@media (max-width: 768px) {
    .settings-header {
        flex-direction: column;
        gap: 1rem;
    }
    .settings-header-actions {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
@endpush

@section('content')
<div class="admin-page" id="settingsPageContent">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accounts.overview') }}">Accounts</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accounts.details', ['accountId' => $account_id]) }}">{{ $account_id }}</a></li>
            <li class="breadcrumb-item active">Settings</li>
        </ol>
    </div>

    <div class="settings-header">
        <div>
            <div class="settings-header-info">
                <h4>{{ $account->company_name ?? 'Unknown Account' }}</h4>
                <div class="account-id-text">{{ $account->account_number ?? $account_id }}</div>
                <div class="settings-header-pills">
                    @php
                        $statusClass = 'pill-test';
                        $statusIcon = 'fas fa-flask';
                        $statusLabel = ucwords(str_replace('_', ' ', $account->status));
                        if (in_array($account->status, \App\Models\Account::LIVE_STATUSES)) {
                            $statusClass = 'pill-live';
                            $statusIcon = 'fas fa-check-circle';
                        } elseif ($account->status === 'suspended') {
                            $statusClass = 'pill-suspended';
                            $statusIcon = 'fas fa-ban';
                        } elseif ($account->status === 'closed') {
                            $statusClass = 'pill-closed';
                            $statusIcon = 'fas fa-times-circle';
                        } elseif ($account->status === 'pending_verification') {
                            $statusClass = 'pill-pending';
                            $statusIcon = 'fas fa-clock';
                        }
                    @endphp
                    <span class="pill-status {{ $statusClass }}" id="headerStatusPill"><i class="{{ $statusIcon }} me-1" style="font-size: 0.5rem;"></i>{{ $statusLabel }}</span>
                </div>
            </div>
        </div>
        <div class="settings-header-actions">
            <a href="{{ route('admin.accounts.structure', $account_id) }}" class="btn btn-admin-outline btn-sm">
                <i class="fas fa-sitemap me-1"></i>View Account Structure
            </a>
            <a href="{{ route('admin.accounts.overview') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Accounts
            </a>
        </div>
    </div>

    <ul class="nav nav-tabs" id="accountTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('admin.accounts.details', ['accountId' => $account_id]) }}">
                <i class="fas fa-building me-2"></i>Details
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('admin.accounts.details', ['accountId' => $account_id]) }}#pricing">
                <i class="fas fa-tags me-2"></i>Pricing
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('admin.accounts.billing', ['accountId' => $account_id]) }}">
                <i class="fas fa-file-invoice-dollar me-2"></i>Billing
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" href="{{ route('admin.accounts.settings', ['accountId' => $account_id]) }}">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
        </li>
    </ul>

    <div class="row mt-3">
        <div class="col-lg-6">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-exchange-alt me-2"></i>Account Status</h6>
                </div>
                <div class="settings-card-body">
                    <div class="settings-row">
                        <span class="settings-label">Current Status</span>
                        <span class="settings-value" id="currentStatusBadge">
                            <span class="pill-status {{ $statusClass }}"><i class="{{ $statusIcon }} me-1" style="font-size: 0.5rem;"></i>{{ $statusLabel }}</span>
                        </span>
                    </div>
                    <div class="settings-row">
                        <span class="settings-label">Change To</span>
                        <div>
                            <select class="form-select form-select-sm" id="statusSelect" style="max-width: 220px;">
                                <option value="">Select new status...</option>
                                <option value="pending_verification" {{ $account->status === 'pending_verification' ? 'disabled' : '' }}>Pending Verification</option>
                                <option value="test_standard" {{ $account->status === 'test_standard' ? 'disabled' : '' }}>Test Standard</option>
                                <option value="test_dynamic" {{ $account->status === 'test_dynamic' ? 'disabled' : '' }}>Test Dynamic</option>
                                <option value="active_standard" {{ $account->status === 'active_standard' ? 'disabled' : '' }}>Active Standard (Live)</option>
                                <option value="active_dynamic" {{ $account->status === 'active_dynamic' ? 'disabled' : '' }}>Active Dynamic (Live)</option>
                                <option value="suspended" {{ $account->status === 'suspended' ? 'disabled' : '' }}>Suspended</option>
                                <option value="closed" {{ $account->status === 'closed' ? 'disabled' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>
                    <div id="statusReasonRow" class="settings-row" style="display: none;">
                        <span class="settings-label">Reason</span>
                        <div style="flex: 1; margin-left: 1rem;">
                            <input type="text" class="form-control form-control-sm" id="statusReason" placeholder="Reason for status change" maxlength="500">
                        </div>
                    </div>
                    <div class="status-change-warning" id="statusWarning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Warning:</strong> <span id="statusWarningText"></span>
                    </div>
                    <div id="statusActions" style="display: none; margin-top: 0.75rem;">
                        <button type="button" class="btn btn-sm btn-admin-primary" id="applyStatusBtn" onclick="applyStatusChange()">
                            <i class="fas fa-check me-1"></i>Apply Status Change
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="cancelStatusChange()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-shield-alt me-2"></i>Spam Filter / Message Enforcement</h6>
                </div>
                <div class="settings-card-body">
                    <p class="text-muted small mb-3">Controls how the spam filter engine processes messages for this account.</p>
                    
                    <div class="d-flex flex-column gap-3" id="spamFilterOptions">
                        <div class="spam-filter-option position-relative {{ ($account->spam_filter_mode ?? 'enforced') === 'enforced' ? 'selected' : '' }}" data-mode="enforced" onclick="selectSpamFilter('enforced')">
                            <span class="option-check"><i class="fas fa-check-circle"></i></span>
                            <div class="option-header">
                                <div class="option-icon icon-enforced"><i class="fas fa-ban"></i></div>
                                <span class="option-title">Spam Filter On</span>
                            </div>
                            <p class="option-desc">Messages that trigger the spam filter are <strong>blocked</strong>, unless an exemption is in place for this account.</p>
                        </div>
                        <div class="spam-filter-option position-relative {{ ($account->spam_filter_mode ?? 'enforced') === 'monitoring' ? 'selected' : '' }}" data-mode="monitoring" onclick="selectSpamFilter('monitoring')">
                            <span class="option-check"><i class="fas fa-check-circle"></i></span>
                            <div class="option-header">
                                <div class="option-icon icon-monitoring"><i class="fas fa-eye"></i></div>
                                <span class="option-title">Spam Filter Monitoring</span>
                            </div>
                            <p class="option-desc">Messages are <strong>sent normally</strong>, but admins are alerted if the spam filter would have blocked them.</p>
                        </div>
                        <div class="spam-filter-option position-relative {{ ($account->spam_filter_mode ?? 'enforced') === 'off' ? 'selected' : '' }}" data-mode="off" onclick="selectSpamFilter('off')">
                            <span class="option-check"><i class="fas fa-check-circle"></i></span>
                            <div class="option-header">
                                <div class="option-icon icon-off"><i class="fas fa-times"></i></div>
                                <span class="option-title">Spam Filter Off</span>
                            </div>
                            <p class="option-desc">Messages are <strong>not checked</strong> against the spam filter. Use with caution.</p>
                        </div>
                    </div>
                    <div id="spamFilterActions" style="display: none; margin-top: 1rem;">
                        <button type="button" class="btn btn-sm btn-admin-primary" id="applySpamFilterBtn" onclick="applySpamFilter()">
                            <i class="fas fa-check me-1"></i>Save Spam Filter Setting
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="cancelSpamFilter()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-coins me-2"></i>Test Credits</h6>
                </div>
                <div class="settings-card-body">
                    <div class="credit-summary-box">
                        <div class="row">
                            <div class="col-4 credit-summary-metric">
                                <div class="metric-label">Total Awarded</div>
                                <div class="metric-value" id="totalAwarded">{{ number_format($totalCreditsAwarded) }}</div>
                            </div>
                            <div class="col-4 credit-summary-metric">
                                <div class="metric-label">Used</div>
                                <div class="metric-value" style="color: #dc3545;" id="totalUsed">{{ number_format($totalCreditsUsed) }}</div>
                            </div>
                            <div class="col-4 credit-summary-metric">
                                <div class="metric-label">Remaining</div>
                                <div class="metric-value" style="color: #1cbb8c;" id="totalRemaining">{{ number_format($totalCreditsRemaining) }}</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3" style="font-size: 0.875rem; color: var(--admin-primary);">
                        <i class="fas fa-plus-circle me-1"></i>Add Test Credits
                    </h6>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-bold">Credits</label>
                            <input type="number" class="form-control form-control-sm" id="addCreditsAmount" min="1" max="100000" placeholder="e.g. 500">
                        </div>
                        <div class="col-8">
                            <label class="form-label small fw-bold">Reason</label>
                            <input type="text" class="form-control form-control-sm" id="addCreditsReason" placeholder="e.g. Extended trial, sales demo" maxlength="500">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-admin-primary" id="addCreditsBtn" onclick="addTestCredits()">
                        <i class="fas fa-plus me-1"></i>Add Credits
                    </button>

                    @if($testCreditWallets->count() > 0)
                    <hr class="my-3">
                    <h6 class="fw-bold mb-2" style="font-size: 0.875rem; color: var(--admin-primary);">
                        <i class="fas fa-history me-1"></i>Credit Wallet History
                    </h6>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="credit-wallet-table">
                            <thead>
                                <tr>
                                    <th>Awarded</th>
                                    <th>Total</th>
                                    <th>Used</th>
                                    <th>Remaining</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="walletHistoryBody">
                                @foreach($testCreditWallets as $wallet)
                                <tr>
                                    <td>{{ $wallet->created_at?->format('d M Y, H:i') ?? '-' }}</td>
                                    <td>{{ number_format($wallet->credits_total) }}</td>
                                    <td>{{ number_format($wallet->credits_used) }}</td>
                                    <td>{{ number_format($wallet->credits_remaining) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($wallet->awarded_reason, 30) }}</td>
                                    <td>
                                        @if($wallet->expired)
                                            <span class="badge" style="background: rgba(220,53,69,0.15); color: #dc3545;">Expired</span>
                                        @elseif($wallet->credits_remaining <= 0)
                                            <span class="badge" style="background: rgba(108,117,125,0.15); color: #6c757d;">Depleted</span>
                                        @else
                                            <span class="badge" style="background: rgba(28,187,140,0.15); color: #1cbb8c;">Active</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-coins" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mb-0 mt-2 small">No test credit wallets found for this account.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h5 style="font-weight: 600; color: var(--admin-primary); margin-bottom: 1rem;">
                <i class="fas fa-lock me-2"></i>Customer Security Settings
            </h5>
        </div>
    </div>

    <div id="securityLoadingState" class="text-center py-4">
        <i class="fas fa-spinner fa-spin" style="color: var(--admin-primary);"></i>
        <span class="ms-2 text-muted">Loading security settings...</span>
    </div>

    <div id="securityContent" class="d-none">
    <div class="row">
        <div class="col-lg-6">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-database me-2"></i>Message Data Retention</h6>
                    <span class="d-none" id="secRetentionSaving" style="font-size: 0.75rem; color: var(--admin-primary);"><i class="fas fa-spinner fa-spin"></i> Saving</span>
                </div>
                <div class="settings-card-body">
                    <div class="settings-row">
                        <span class="settings-label">Message Log Retention Period</span>
                        <select class="form-select form-select-sm" id="secRetentionPeriod" style="width: 140px;" onchange="saveSecRetention()">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                            <option value="120">120 days</option>
                            <option value="150">150 days</option>
                            <option value="180">180 days</option>
                        </select>
                    </div>
                    <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.5rem;">
                        How long message logs and delivery receipts are stored. Billing records are not affected.
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-eye-slash me-2"></i>Data Visibility & Masking</h6>
                    <span class="d-none" id="secMaskingSaving" style="font-size: 0.75rem; color: var(--admin-primary);"><i class="fas fa-spinner fa-spin"></i> Saving</span>
                </div>
                <div class="settings-card-body">
                    <div style="font-size: 0.8rem; color: #6c757d; margin-bottom: 0.75rem;">Controls which data fields are masked in Message Logs, Reporting, and Exports.</div>
                    <div class="settings-row">
                        <span class="settings-label" style="font-size: 0.85rem;">Mobile Number</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="secMaskMobile" onchange="saveSecMasking()">
                        </div>
                    </div>
                    <div class="settings-row">
                        <span class="settings-label" style="font-size: 0.85rem;">Message Content</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="secMaskContent" onchange="saveSecMasking()">
                        </div>
                    </div>
                    <div class="settings-row">
                        <span class="settings-label" style="font-size: 0.85rem;">Sent Timestamp</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="secMaskSentTime" onchange="saveSecMasking()">
                        </div>
                    </div>
                    <div class="settings-row">
                        <span class="settings-label" style="font-size: 0.85rem;">Delivered Timestamp</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="secMaskDeliveredTime" onchange="saveSecMasking()">
                        </div>
                    </div>
                    <div class="settings-row">
                        <span class="settings-label" style="font-size: 0.85rem;">Owner Bypass Masking</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="secOwnerBypass" onchange="saveSecMasking()">
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-shield-alt me-2"></i>MFA Policy</h6>
                </div>
                <div class="settings-card-body">
                    <div class="settings-row">
                        <span class="settings-label">Require MFA for all users</span>
                        <div>
                            <span class="badge" id="secMfaBadge" style="font-size: 0.8rem;">-</span>
                        </div>
                    </div>
                    <div style="font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem;">
                        <i class="fas fa-info-circle me-1"></i>MFA configuration is read-only from the admin console. The customer manages this setting.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-water me-2"></i>Anti-Flood Protection</h6>
                    <span class="d-none" id="secAntiFloodSaving" style="font-size: 0.75rem; color: var(--admin-primary);"><i class="fas fa-spinner fa-spin"></i> Saving</span>
                </div>
                <div class="settings-card-body">
                    <div class="settings-row">
                        <span class="settings-label">Enabled</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="secAntiFloodEnabled" onchange="saveSecAntiFlood()">
                        </div>
                    </div>
                    <div class="settings-row" id="secAntiFloodModeRow">
                        <span class="settings-label">Mode</span>
                        <select class="form-select form-select-sm" id="secAntiFloodMode" style="width: 140px;" onchange="saveSecAntiFlood()">
                            <option value="enforce">Enforce</option>
                            <option value="monitor">Monitor</option>
                            <option value="off">Off</option>
                        </select>
                    </div>
                    <div class="settings-row" id="secAntiFloodWindowRow">
                        <span class="settings-label">Window (hours)</span>
                        <select class="form-select form-select-sm" id="secAntiFloodWindow" style="width: 100px;" onchange="saveSecAntiFlood()">
                            <option value="2">2</option>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8">8</option>
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="48">48</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-clock me-2"></i>Out-of-Hours Restriction</h6>
                    <span class="d-none" id="secOohSaving" style="font-size: 0.75rem; color: var(--admin-primary);"><i class="fas fa-spinner fa-spin"></i> Saving</span>
                </div>
                <div class="settings-card-body">
                    <div class="settings-row">
                        <span class="settings-label">Enabled</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="secOohEnabled" onchange="saveSecOutOfHours()">
                        </div>
                    </div>
                    <div id="secOohDetailsRows">
                        <div class="settings-row">
                            <span class="settings-label">Start Time</span>
                            <input type="time" class="form-control form-control-sm" id="secOohStart" style="width: 120px;" onchange="saveSecOutOfHours()">
                        </div>
                        <div class="settings-row">
                            <span class="settings-label">End Time</span>
                            <input type="time" class="form-control form-control-sm" id="secOohEnd" style="width: 120px;" onchange="saveSecOutOfHours()">
                        </div>
                        <div class="settings-row">
                            <span class="settings-label">Action</span>
                            <select class="form-select form-select-sm" id="secOohAction" style="width: 120px;" onchange="saveSecOutOfHours()">
                                <option value="reject">Reject</option>
                                <option value="hold">Hold</option>
                            </select>
                        </div>
                        <div class="settings-row">
                            <span class="settings-label">Timezone</span>
                            <span class="settings-value" id="secOohTimezone" style="font-size: 0.85rem;">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-header">
                    <h6><i class="fas fa-network-wired me-2"></i>IP Allowlist</h6>
                    <span class="d-none" id="secIpSaving" style="font-size: 0.75rem; color: var(--admin-primary);"><i class="fas fa-spinner fa-spin"></i> Saving</span>
                </div>
                <div class="settings-card-body">
                    <div class="settings-row">
                        <span class="settings-label">Allowlist Enabled</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="secIpEnabled" onchange="confirmToggleIpAllowlist()">
                        </div>
                    </div>
                    <div class="settings-row">
                        <span class="settings-label">Your Current IP</span>
                        <span class="settings-value" id="secAdminCurrentIp" style="font-size: 0.85rem;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 0.75rem;"></i>
                        </span>
                    </div>
                    <div id="secIpListContainer" style="margin-top: 0.75rem;">
                        <div id="secIpEntries"></div>
                        <button type="button" class="btn btn-sm btn-admin-outline mt-2" onclick="openAddIpModal()">
                            <i class="fas fa-plus me-1"></i>Add IP
                        </button>
                        <div style="font-size: 0.7rem; color: #6c757d; margin-top: 0.5rem;" id="secIpCount">0 / 50 entries</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="addIpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                <h6 class="modal-title" style="color: var(--admin-primary); font-weight: 600;">
                    <i class="fas fa-plus-circle me-2"></i>Add IP to Allowlist
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">IP Address or CIDR</label>
                    <input type="text" class="form-control" id="modalIpAddress" placeholder="e.g. 192.168.1.1 or 10.0.0.0/24">
                    <div class="form-text">Supports IPv4, IPv6, and CIDR notation.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Label (optional)</label>
                    <input type="text" class="form-control" id="modalIpLabel" placeholder="e.g. Office VPN, Developer laptop" maxlength="100">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-admin-primary" id="modalAddIpBtn" onclick="submitAddIp()">
                    <i class="fas fa-plus me-1"></i>Add IP
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmToggleIpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                <h6 class="modal-title" style="color: var(--admin-primary); font-weight: 600;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm IP Allowlist Change
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmToggleIpText"></p>
                <div class="alert alert-warning small" id="confirmToggleIpWarning" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <span id="confirmToggleIpWarningText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" onclick="revertIpToggle()">Cancel</button>
                <button type="button" class="btn btn-sm btn-admin-primary" onclick="executeToggleIpAllowlist()">
                    <i class="fas fa-check me-1"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmRemoveIpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                <h6 class="modal-title" style="color: var(--admin-primary); font-weight: 600;">
                    <i class="fas fa-trash me-2"></i>Remove IP Entry
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this IP entry?</p>
                <div class="p-2 rounded" style="background: #f8f9fa;">
                    <strong>IP:</strong> <code id="confirmRemoveIpAddress"></code>
                    <span class="ms-2" id="confirmRemoveIpLabel"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" id="confirmRemoveIpBtn" onclick="executeRemoveIp()">
                    <i class="fas fa-trash me-1"></i>Remove
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>
@endsection

@push('scripts')
<script>
const ACCOUNT_ID = @json($account_id);
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
let currentSpamFilter = @json($account->spam_filter_mode ?? 'enforced');
let selectedSpamFilter = currentSpamFilter;

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const html = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body"><i class="fas ${icon} me-2"></i>${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(toastId);
    const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

document.getElementById('statusSelect').addEventListener('change', function() {
    const val = this.value;
    const reasonRow = document.getElementById('statusReasonRow');
    const actions = document.getElementById('statusActions');
    const warning = document.getElementById('statusWarning');
    
    if (val) {
        reasonRow.style.display = 'flex';
        actions.style.display = 'block';
        
        if (val === 'closed') {
            warning.style.display = 'block';
            document.getElementById('statusWarningText').textContent = 'Closing an account is a terminal action. The account cannot be reopened.';
        } else if (val === 'suspended') {
            warning.style.display = 'block';
            document.getElementById('statusWarningText').textContent = 'Suspending will immediately prevent the account from sending messages.';
        } else {
            warning.style.display = 'none';
        }
    } else {
        reasonRow.style.display = 'none';
        actions.style.display = 'none';
        warning.style.display = 'none';
    }
});

async function applyStatusChange() {
    const newStatus = document.getElementById('statusSelect').value;
    const reason = document.getElementById('statusReason').value;
    
    if (!newStatus) return;
    
    const btn = document.getElementById('applyStatusBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/status-override`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: newStatus, reason: reason }),
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => null);
            showToast(errData?.error || `Request failed (${res.status})`, 'error');
            return;
        }
        const data = await res.json();

        if (data.success) {
            showToast(`Status changed to ${newStatus.replace(/_/g, ' ')}`);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to update status', 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Apply Status Change';
    }
}

function cancelStatusChange() {
    document.getElementById('statusSelect').value = '';
    document.getElementById('statusReason').value = '';
    document.getElementById('statusReasonRow').style.display = 'none';
    document.getElementById('statusActions').style.display = 'none';
    document.getElementById('statusWarning').style.display = 'none';
}

function selectSpamFilter(mode) {
    selectedSpamFilter = mode;
    document.querySelectorAll('.spam-filter-option').forEach(el => {
        el.classList.toggle('selected', el.dataset.mode === mode);
    });
    
    const actions = document.getElementById('spamFilterActions');
    if (mode !== currentSpamFilter) {
        actions.style.display = 'block';
    } else {
        actions.style.display = 'none';
    }
}

async function applySpamFilter() {
    if (selectedSpamFilter === currentSpamFilter) return;
    
    const btn = document.getElementById('applySpamFilterBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/spam-filter-mode`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ spam_filter_mode: selectedSpamFilter }),
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => null);
            showToast(errData?.error || `Request failed (${res.status})`, 'error');
            return;
        }
        const data = await res.json();

        if (data.success) {
            currentSpamFilter = selectedSpamFilter;
            document.getElementById('spamFilterActions').style.display = 'none';
            const modeLabels = { enforced: 'Spam Filter On', monitoring: 'Spam Filter Monitoring', off: 'Spam Filter Off' };
            showToast(`Spam filter set to: ${modeLabels[selectedSpamFilter]}`);
        } else {
            showToast(data.error || 'Failed to update spam filter mode', 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Save Spam Filter Setting';
    }
}

function cancelSpamFilter() {
    selectSpamFilter(currentSpamFilter);
    document.getElementById('spamFilterActions').style.display = 'none';
}

let securityData = null;
let secSaveTimers = {};

async function loadSecuritySettings() {
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/settings`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        });
        if (!res.ok) throw new Error('Failed to load');
        const json = await res.json();
        if (!json.success) throw new Error(json.error || 'Failed');
        securityData = json.data;
        populateSecurityCards(securityData);
        document.getElementById('securityLoadingState').classList.add('d-none');
        document.getElementById('securityContent').classList.remove('d-none');
    } catch (err) {
        document.getElementById('securityLoadingState').innerHTML =
            '<i class="fas fa-exclamation-triangle text-danger"></i> <span class="ms-2 text-danger">Failed to load security settings.</span>';
    }
}

function populateSecurityCards(d) {
    document.getElementById('secRetentionPeriod').value = d.retention.message_retention_days;

    const mc = d.masking.config || {};
    document.getElementById('secMaskMobile').checked = !!mc.mask_mobile;
    document.getElementById('secMaskContent').checked = !!mc.mask_content;
    document.getElementById('secMaskSentTime').checked = !!mc.mask_sent_time;
    document.getElementById('secMaskDeliveredTime').checked = !!mc.mask_delivered_time;
    document.getElementById('secOwnerBypass').checked = d.masking.owner_bypass_masking !== false;

    document.getElementById('secAntiFloodEnabled').checked = !!d.anti_flood.enabled;
    document.getElementById('secAntiFloodMode').value = d.anti_flood.mode || 'off';
    document.getElementById('secAntiFloodWindow').value = d.anti_flood.window_hours || 2;
    updateAntiFloodVisibility();

    document.getElementById('secOohEnabled').checked = !!d.out_of_hours.enabled;
    document.getElementById('secOohStart').value = d.out_of_hours.start || '21:00';
    document.getElementById('secOohEnd').value = d.out_of_hours.end || '08:00';
    document.getElementById('secOohAction').value = d.out_of_hours.action || 'reject';
    document.getElementById('secOohTimezone').textContent = d.out_of_hours.timezone || 'Europe/London';
    updateOohVisibility();

    const mfaEnabled = d.mfa && d.mfa.require_mfa;
    const mfaBadge = document.getElementById('secMfaBadge');
    if (mfaEnabled) {
        mfaBadge.textContent = 'Enabled';
        mfaBadge.style.background = 'rgba(28,187,140,0.15)';
        mfaBadge.style.color = '#1cbb8c';
    } else {
        mfaBadge.textContent = 'Disabled';
        mfaBadge.style.background = 'rgba(108,117,125,0.15)';
        mfaBadge.style.color = '#6c757d';
    }

    document.getElementById('secIpEnabled').checked = !!d.ip_allowlist.enabled;
    renderIpEntries(d.ip_allowlist.entries || []);
}

function updateAntiFloodVisibility() {
    const enabled = document.getElementById('secAntiFloodEnabled').checked;
    document.getElementById('secAntiFloodModeRow').style.display = enabled ? 'flex' : 'none';
    document.getElementById('secAntiFloodWindowRow').style.display = enabled ? 'flex' : 'none';
}

function updateOohVisibility() {
    const enabled = document.getElementById('secOohEnabled').checked;
    document.getElementById('secOohDetailsRows').style.display = enabled ? 'block' : 'none';
}

function showSecSaving(id) {
    document.getElementById(id).classList.remove('d-none');
}
function hideSecSaving(id) {
    document.getElementById(id).classList.add('d-none');
}

async function saveSecRetention() {
    showSecSaving('secRetentionSaving');
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/retention`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ message_retention_days: parseInt(document.getElementById('secRetentionPeriod').value) }),
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.error || 'Failed');
        showToast('Retention updated');
    } catch (err) {
        showToast(err.message || 'Failed to update retention', 'error');
    } finally {
        hideSecSaving('secRetentionSaving');
    }
}

async function saveSecMasking() {
    clearTimeout(secSaveTimers.masking);
    secSaveTimers.masking = setTimeout(async () => {
        showSecSaving('secMaskingSaving');
        try {
            const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/masking`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify({
                    mask_mobile: document.getElementById('secMaskMobile').checked,
                    mask_content: document.getElementById('secMaskContent').checked,
                    mask_sent_time: document.getElementById('secMaskSentTime').checked,
                    mask_delivered_time: document.getElementById('secMaskDeliveredTime').checked,
                    owner_bypass_masking: document.getElementById('secOwnerBypass').checked,
                }),
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.error || 'Failed');
            showToast('Masking updated');
        } catch (err) {
            showToast(err.message || 'Failed to update masking', 'error');
        } finally {
            hideSecSaving('secMaskingSaving');
        }
    }, 300);
}

async function saveSecAntiFlood() {
    updateAntiFloodVisibility();
    showSecSaving('secAntiFloodSaving');
    try {
        const enabled = document.getElementById('secAntiFloodEnabled').checked;
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/anti-flood`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({
                enabled: enabled,
                mode: enabled ? document.getElementById('secAntiFloodMode').value : 'off',
                window_hours: parseInt(document.getElementById('secAntiFloodWindow').value),
            }),
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.error || 'Failed');
        showToast('Anti-flood updated');
    } catch (err) {
        showToast(err.message || 'Failed to update anti-flood', 'error');
    } finally {
        hideSecSaving('secAntiFloodSaving');
    }
}

async function saveSecOutOfHours() {
    updateOohVisibility();
    showSecSaving('secOohSaving');
    try {
        const enabled = document.getElementById('secOohEnabled').checked;
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/out-of-hours`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({
                enabled: enabled,
                start: document.getElementById('secOohStart').value,
                end: document.getElementById('secOohEnd').value,
                action: document.getElementById('secOohAction').value,
            }),
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.error || 'Failed');
        showToast('Out-of-hours updated');
    } catch (err) {
        showToast(err.message || 'Failed to update out-of-hours', 'error');
    } finally {
        hideSecSaving('secOohSaving');
    }
}

let pendingIpToggle = null;
let pendingRemoveIpId = null;
let ipEntriesCache = [];

function confirmToggleIpAllowlist() {
    const enabled = document.getElementById('secIpEnabled').checked;
    pendingIpToggle = enabled;
    const text = document.getElementById('confirmToggleIpText');
    const warning = document.getElementById('confirmToggleIpWarning');
    const warningText = document.getElementById('confirmToggleIpWarningText');

    if (enabled) {
        text.textContent = 'Are you sure you want to enable the IP allowlist for this account?';
        warning.style.display = 'block';
        warningText.textContent = 'Enabling the allowlist restricts portal access to listed IPs only. Ensure valid IPs are added to prevent customer lockout.';
    } else {
        text.textContent = 'Are you sure you want to disable the IP allowlist for this account? All users will be able to log in from any IP.';
        warning.style.display = 'none';
    }

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmToggleIpModal'));
    modal.show();
}

function revertIpToggle() {
    if (pendingIpToggle !== null) {
        document.getElementById('secIpEnabled').checked = !pendingIpToggle;
        pendingIpToggle = null;
    }
}

async function executeToggleIpAllowlist() {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmToggleIpModal'));
    modal.hide();

    if (pendingIpToggle === null) return;
    const enabled = pendingIpToggle;
    pendingIpToggle = null;

    showSecSaving('secIpSaving');
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/ip-allowlist/toggle`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ enabled: enabled }),
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.error || 'Failed');
        showToast(enabled ? 'IP allowlist enabled' : 'IP allowlist disabled');
    } catch (err) {
        document.getElementById('secIpEnabled').checked = !enabled;
        showToast(err.message || 'Failed to toggle IP allowlist', 'error');
    } finally {
        hideSecSaving('secIpSaving');
    }
}

async function loadAdminCurrentIp() {
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/ip-allowlist/current-ip`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        });
        const json = await res.json();
        if (json.success && json.data) {
            document.getElementById('secAdminCurrentIp').innerHTML = '<code style="font-size: 0.85rem;">' + escHtml(json.data.ip_address) + '</code>';
        } else {
            document.getElementById('secAdminCurrentIp').textContent = 'Unknown';
        }
    } catch (err) {
        document.getElementById('secAdminCurrentIp').textContent = 'Unknown';
    }
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function renderIpEntries(entries) {
    ipEntriesCache = entries || [];
    const container = document.getElementById('secIpEntries');
    if (!entries || entries.length === 0) {
        container.innerHTML = '<div class="text-muted small py-2">No IP entries configured.</div>';
    } else {
        let html = '<table class="credit-wallet-table"><thead><tr><th>IP Address</th><th>Label</th><th>Status</th><th>Added</th><th></th></tr></thead><tbody>';
        entries.forEach(e => {
            const statusBg = e.status === 'active' ? 'rgba(28,187,140,0.15)' : 'rgba(108,117,125,0.15)';
            const statusColor = e.status === 'active' ? '#1cbb8c' : '#6c757d';
            const dateStr = e.created_at ? new Date(e.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
            const safeIp = escHtml(e.ip_address);
            const safeLabel = e.label ? escHtml(e.label) : '-';
            const safeStatus = escHtml(e.status);
            const safeId = escHtml(e.id);
            html += `<tr>
                <td><code style="font-size: 0.8rem;">${safeIp}</code></td>
                <td>${safeLabel}</td>
                <td><span class="badge" style="background:${statusBg};color:${statusColor};">${safeStatus}</span></td>
                <td>${dateStr}</td>
                <td><button class="btn btn-sm btn-outline-danger" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;" onclick="openRemoveIpModal('${safeId}')"><i class="fas fa-trash"></i></button></td>
            </tr>`;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    document.getElementById('secIpCount').textContent = `${entries.length} / 50 entries`;
}

function openAddIpModal() {
    document.getElementById('modalIpAddress').value = '';
    document.getElementById('modalIpLabel').value = '';
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addIpModal'));
    modal.show();
}

async function submitAddIp() {
    const ip = document.getElementById('modalIpAddress').value.trim();
    const label = document.getElementById('modalIpLabel').value.trim();
    if (!ip) {
        showToast('Please enter an IP address.', 'error');
        return;
    }
    const btn = document.getElementById('modalAddIpBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/ip-allowlist`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ ip_address: ip, label: label || null }),
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.error || 'Failed');
        showToast('IP added successfully');
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addIpModal'));
        modal.hide();
        loadSecuritySettings();
    } catch (err) {
        showToast(err.message || 'Failed to add IP', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus me-1"></i>Add IP';
    }
}

function openRemoveIpModal(entryId) {
    pendingRemoveIpId = entryId;
    const entry = ipEntriesCache.find(e => e.id === entryId);
    document.getElementById('confirmRemoveIpAddress').textContent = entry ? entry.ip_address : entryId;
    document.getElementById('confirmRemoveIpLabel').textContent = entry && entry.label ? '(' + entry.label + ')' : '';
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmRemoveIpModal'));
    modal.show();
}

async function executeRemoveIp() {
    if (!pendingRemoveIpId) return;
    const entryId = pendingRemoveIpId;
    pendingRemoveIpId = null;

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmRemoveIpModal'));
    modal.hide();

    showSecSaving('secIpSaving');
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/security/ip-allowlist/${entryId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.error || 'Failed');
        showToast('IP removed');
        loadSecuritySettings();
    } catch (err) {
        showToast(err.message || 'Failed to remove IP', 'error');
    } finally {
        hideSecSaving('secIpSaving');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadSecuritySettings();
    loadAdminCurrentIp();

    document.getElementById('confirmToggleIpModal').addEventListener('hidden.bs.modal', function() {
        revertIpToggle();
    });
});

async function addTestCredits() {
    const amount = parseInt(document.getElementById('addCreditsAmount').value);
    const reason = document.getElementById('addCreditsReason').value.trim();
    
    if (!amount || amount < 1) {
        showToast('Please enter a valid credit amount.', 'error');
        return;
    }
    if (!reason) {
        showToast('Please provide a reason for adding credits.', 'error');
        return;
    }
    
    const btn = document.getElementById('addCreditsBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';
    
    try {
        const res = await fetch(`/admin/api/accounts/${ACCOUNT_ID}/test-credits`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ credits: amount, reason: reason }),
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => null);
            showToast(errData?.error || `Request failed (${res.status})`, 'error');
            return;
        }
        const data = await res.json();

        if (data.success) {
            showToast(`Added ${amount.toLocaleString()} test credits successfully`);
            document.getElementById('addCreditsAmount').value = '';
            document.getElementById('addCreditsReason').value = '';
            document.getElementById('totalRemaining').textContent = Number(data.data.total_remaining).toLocaleString();

            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.error || 'Failed to add test credits', 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus me-1"></i>Add Credits';
    }
}
</script>
@endpush

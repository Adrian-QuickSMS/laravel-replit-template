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

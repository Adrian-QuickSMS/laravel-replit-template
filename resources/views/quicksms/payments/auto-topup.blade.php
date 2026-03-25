@extends('layouts.quicksms')

@section('title', $page_title ?? 'Auto Top-Up')

@push('styles')
<style>
.auto-topup-container { min-height: calc(100vh - 200px); }
.alert-pastel-primary { background-color: #ede8f5; border: 1px solid #d5c8e8; color: #4a3570; }
.section-card { background: #fff; border-radius: 0.75rem; border: none; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.section-card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; gap: 0.75rem; }
.section-card-header i { color: #886cc0; font-size: 1.1rem; }
.section-card-header h6 { margin: 0; font-weight: 600; color: #374151; }
.section-card-body { padding: 1.5rem; }
.form-label { font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.form-text { color: #6b7280; font-size: 0.8rem; }
.pm-display { display: flex; align-items: center; gap: 12px; padding: 1rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; }
.pm-display .brand-icon { font-size: 1.5rem; }
.pm-display .details { flex: 1; }
.pm-display .details .card-number { font-weight: 600; }
.pm-display .details .card-expiry { font-size: 0.85rem; color: #6b7280; }
.activity-table th { font-size: 0.8rem; text-transform: uppercase; color: #6b7280; font-weight: 600; }
.badge-succeeded { background: #dcfce7; color: #166534; }
.badge-failed { background: #fee2e2; color: #991b1b; }
.badge-requires_action { background: #fef3c7; color: #92400e; }
.badge-pending, .badge-processing { background: #e5e7eb; color: #374151; }
.badge-expired, .badge-cancelled { background: #f3f4f6; color: #6b7280; }
.vat-preview { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.5rem; padding: 0.75rem 1rem; margin-top: 0.5rem; font-size: 0.9rem; }
.locked-banner { background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px; }
.locked-banner i { color: #dc2626; font-size: 1.25rem; }
.confirm-modal .modal-body { padding: 1.5rem; }
.confirm-summary { background: #f9fafb; border-radius: 0.5rem; padding: 1rem; margin: 1rem 0; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Purchase</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Auto Top-Up</a></li>
        </ol>
    </div>
</div>
<div class="container-fluid auto-topup-container">

    @if(!($is_prepay ?? true))
    <div class="alert alert-warning">
        <i class="fas fa-info-circle me-2"></i>
        Auto Top-Up is only available for prepay accounts. Your account uses postpay billing.
    </div>
    @else

    <div id="lockedBanner" class="locked-banner d-none">
        <i class="fas fa-lock"></i>
        <div>
            <strong>Auto Top-Up has been locked by support.</strong>
            <span id="lockedReason"></span>
            Please contact us for assistance.
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-6 col-sm-6 mb-4">
            <div class="widget-stat card" id="statusCard">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-primary text-primary" id="statusIcon">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Status</p>
                            <h4 class="mb-0" id="statusBadge">Not Configured</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-sm-6 mb-4">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-warning text-warning">
                            <i class="fas fa-sterling-sign"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Trigger Below</p>
                            <h4 class="mb-0" id="statThreshold">—</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-sm-6 mb-4">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-success text-success">
                            <i class="fas fa-arrow-up"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Top-Up Amount</p>
                            <h4 class="mb-0" id="statAmount">—</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-sm-6 mb-4">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-info text-info">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Today's Top-Ups</p>
                            <h4 class="mb-0" id="statDailyCount">—</h4>
                            <small class="text-muted" id="statDailyValue"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-card-header">
                    <i class="fas fa-cog"></i>
                    <h6>Configuration</h6>
                </div>
                <div class="section-card-body">
                    <form id="autoTopUpForm">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enableToggle" name="enabled">
                                <label class="form-check-label fw-semibold" for="enableToggle">Enable Auto Top-Up</label>
                            </div>
                        </div>

                        <div id="configFields">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="thresholdAmount">Trigger Balance Threshold</label>
                                    <div class="input-group">
                                        <span class="input-group-text">&pound;</span>
                                        <input type="number" class="form-control" id="thresholdAmount" name="threshold_amount" min="1" step="1" placeholder="50">
                                    </div>
                                    <div class="form-text">Auto Top-Up will trigger when your balance falls below this amount.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="topupAmount">Top-Up Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">&pound;</span>
                                        <input type="number" class="form-control" id="topupAmount" name="topup_amount" min="5" step="1" placeholder="250">
                                    </div>
                                    <div class="form-text">Net amount to add to your balance each time.</div>
                                    <div class="vat-preview" id="vatPreview" style="display:none;">
                                        <span id="vatBreakdown"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="maxPerDay">Max Top-Ups Per Day</label>
                                    <select class="form-select" id="maxPerDay" name="max_topups_per_day">
                                        @for($i = 1; $i <= config('billing.auto_topup.max_per_day', 3); $i++)
                                        <option value="{{ $i }}" {{ $i === 3 ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <div class="form-text">Maximum number of auto top-ups in a single day.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="dailyCap">Daily Top-Up Cap</label>
                                    <div class="input-group">
                                        <span class="input-group-text">&pound;</span>
                                        <input type="number" class="form-control" id="dailyCap" name="daily_topup_cap" min="0" step="1" placeholder="750">
                                    </div>
                                    <div class="form-text">Maximum total auto top-up value per day.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="cooldown">Cooldown (minutes)</label>
                                    <input type="number" class="form-control" id="cooldown" name="min_minutes_between_topups" min="0" step="1" value="0" placeholder="0">
                                    <div class="form-text">Minimum time between auto top-ups.</div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3"><i class="fas fa-bell me-2"></i>Notification Preferences</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="notifyEmailSuccess" name="notify_email_success" checked>
                                        <label class="form-check-label" for="notifyEmailSuccess">Email on successful top-up</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="notifyEmailFailure" name="notify_email_failure" checked>
                                        <label class="form-check-label" for="notifyEmailFailure">Email on failed top-up</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="notifyInappSuccess" name="notify_inapp_success" checked>
                                        <label class="form-check-label" for="notifyInappSuccess">In-app notification on success</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="notifyInappFailure" name="notify_inapp_failure" checked>
                                        <label class="form-check-label" for="notifyInappFailure">In-app notification on failure</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="notifyRequiresAction" name="notify_requires_action" checked>
                                        <label class="form-check-label" for="notifyRequiresAction">Notify when action is required</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="fas fa-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-history"></i>
                        <h6>Recent Activity</h6>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="refreshActivity"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div class="section-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 activity-table">
                            <thead>
                                <tr>
                                    <th>Date / Time</th>
                                    <th>Event</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody id="activityBody">
                                <tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="section-card">
                <div class="section-card-header">
                    <i class="fas fa-credit-card"></i>
                    <h6>Payment Method</h6>
                </div>
                <div class="section-card-body">
                    <div id="pmSection">
                        <div id="pmDisplay" style="display:none;">
                            <div class="pm-display mb-3">
                                <div class="brand-icon"><i class="fas fa-credit-card"></i></div>
                                <div class="details">
                                    <div class="card-number" id="pmCardInfo">—</div>
                                    <div class="card-expiry" id="pmCardExpiry"></div>
                                </div>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm w-100 mb-2" id="changePmBtn">
                                <i class="fas fa-exchange-alt me-1"></i> Change Payment Method
                            </button>
                            <button class="btn btn-outline-danger btn-sm w-100" id="removePmBtn">
                                <i class="fas fa-trash me-1"></i> Remove Payment Method
                            </button>
                        </div>
                        <div id="pmEmpty">
                            <div class="text-center py-3">
                                <i class="fas fa-credit-card fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-3">No payment method on file.<br>Add one to enable Auto Top-Up.</p>
                                <button class="btn btn-primary" id="addPmBtn">
                                    <i class="fas fa-plus me-1"></i> Add Payment Method
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <i class="fas fa-info-circle"></i>
                    <h6>How It Works</h6>
                </div>
                <div class="section-card-body">
                    <ol class="small text-muted mb-0" style="padding-left: 1.25rem;">
                        <li class="mb-2">Set your balance threshold and top-up amount</li>
                        <li class="mb-2">When your balance drops below the threshold, we automatically charge your card</li>
                        <li class="mb-2">Credit is added to your account once payment is confirmed</li>
                        <li class="mb-2">VAT is added where applicable based on your account's tax status</li>
                        <li>Daily limits and cooldowns prevent excessive charges</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Confirmation Modal -->
<div class="modal fade confirm-modal" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Auto Top-Up Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Please review your Auto Top-Up settings before saving:</p>
                <div class="confirm-summary" id="confirmSummary"></div>
                <div class="alert alert-pastel-primary mt-3 mb-0">
                    <small><i class="fas fa-info-circle me-1"></i> By enabling Auto Top-Up, you authorise QuickSMS to automatically charge your saved payment method when your balance falls below the configured threshold.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSaveBtn">Confirm &amp; Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Payment Method Modal -->
<div class="modal fade" id="removePmModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Removing your payment method will disable Auto Top-Up. Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemovePm">Remove</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const API_BASE = '/api/v1/topup/auto-topup';
    let currentConfig = null;
    let dailyStats = null;
    let vatInfo = { vat_applicable: false, vat_rate: '0.00' };

    // HTML escape helper to prevent XSS
    function esc(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // Helpers
    function fmt(amount) {
        if (amount === null || amount === undefined) return '—';
        return '£' + parseFloat(amount).toFixed(2);
    }

    function fmtDate(iso) {
        if (!iso) return '—';
        const d = new Date(iso);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
            + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }

    function eventLabel(type) {
        const labels = {
            'triggered': 'Top-up triggered',
            'payment_initiated': 'Payment initiated',
            'payment_succeeded': 'Payment succeeded',
            'payment_failed': 'Payment failed',
            'requires_action': 'Authentication required',
            'action_completed': 'Authentication completed',
            'action_expired': 'Authentication expired',
            'retry_scheduled': 'Retry scheduled',
            'retry_attempted': 'Retry attempted',
            'auto_disabled': 'Auto-disabled (failures)',
            'admin_disabled': 'Disabled by support',
            'admin_unlocked': 'Unlocked by support',
            'config_updated': 'Settings updated',
            'payment_method_added': 'Payment method added',
            'payment_method_removed': 'Payment method removed',
        };
        return labels[type] || type;
    }

    async function apiFetch(url, options = {}) {
        const res = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...options.headers,
            },
            ...options,
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: 'Request failed' }));
            throw err;
        }
        return res.json();
    }

    // Load config
    async function loadConfig() {
        try {
            const resp = await apiFetch(API_BASE);
            currentConfig = resp.data;
            dailyStats = resp.daily_stats;
            vatInfo = resp.vat || vatInfo;
            renderStatus();
            renderForm();
            renderPaymentMethod();
        } catch (e) {
            console.error('[AutoTopUp] Failed to load config:', e);
        }
    }

    function renderStatus() {
        const badge = document.getElementById('statusBadge');
        const icon = document.getElementById('statusIcon');
        const lockedBanner = document.getElementById('lockedBanner');

        if (currentConfig?.admin_locked) {
            badge.textContent = 'Locked';
            icon.className = 'me-3 bgl-danger text-danger';
            lockedBanner.classList.remove('d-none');
            document.getElementById('lockedReason').textContent = currentConfig.admin_locked_reason || '';
        } else if (currentConfig?.enabled) {
            badge.textContent = 'Enabled';
            icon.className = 'me-3 bgl-success text-success';
            lockedBanner.classList.add('d-none');
        } else {
            badge.textContent = currentConfig ? 'Disabled' : 'Not Configured';
            icon.className = 'me-3 bgl-primary text-primary';
            lockedBanner.classList.add('d-none');
        }

        document.getElementById('statThreshold').textContent = currentConfig ? fmt(currentConfig.threshold_amount) : '—';
        document.getElementById('statAmount').textContent = currentConfig ? fmt(currentConfig.topup_amount) : '—';
        document.getElementById('statDailyCount').textContent = dailyStats
            ? `${dailyStats.count} of ${currentConfig?.max_topups_per_day || '—'}`
            : '—';
        const dailyValueEl = document.getElementById('statDailyValue');
        dailyValueEl.textContent = dailyStats
            ? `${fmt(dailyStats.value)} of ${currentConfig?.daily_topup_cap ? fmt(currentConfig.daily_topup_cap) : '—'}`
            : '';
    }

    function renderForm() {
        if (!currentConfig) return;

        document.getElementById('enableToggle').checked = currentConfig.enabled;
        document.getElementById('thresholdAmount').value = currentConfig.threshold_amount || '';
        document.getElementById('topupAmount').value = currentConfig.topup_amount || '';
        document.getElementById('maxPerDay').value = currentConfig.max_topups_per_day || 3;
        document.getElementById('dailyCap').value = currentConfig.daily_topup_cap || '';
        document.getElementById('cooldown').value = currentConfig.min_minutes_between_topups || 0;

        document.getElementById('notifyEmailSuccess').checked = currentConfig.notify_email_success !== false;
        document.getElementById('notifyEmailFailure').checked = currentConfig.notify_email_failure !== false;
        document.getElementById('notifyInappSuccess').checked = currentConfig.notify_inapp_success !== false;
        document.getElementById('notifyInappFailure').checked = currentConfig.notify_inapp_failure !== false;
        document.getElementById('notifyRequiresAction').checked = currentConfig.notify_requires_action !== false;

        updateVatPreview();

        // Disable form if locked
        if (currentConfig.admin_locked) {
            document.querySelectorAll('#autoTopUpForm input, #autoTopUpForm select, #autoTopUpForm button').forEach(el => {
                el.disabled = true;
            });
        }
    }

    function renderPaymentMethod() {
        const hasMethod = currentConfig?.has_payment_method;
        document.getElementById('pmDisplay').style.display = hasMethod ? 'block' : 'none';
        document.getElementById('pmEmpty').style.display = hasMethod ? 'none' : 'block';

        if (hasMethod) {
            const brand = (currentConfig.card_brand || 'card').charAt(0).toUpperCase() + (currentConfig.card_brand || 'card').slice(1);
            document.getElementById('pmCardInfo').textContent = `${brand} ending ${currentConfig.card_last4 || '****'}`;
            if (currentConfig.card_exp_month && currentConfig.card_exp_year) {
                document.getElementById('pmCardExpiry').textContent = `Expires ${String(currentConfig.card_exp_month).padStart(2, '0')}/${currentConfig.card_exp_year}`;
            }
        }
    }

    function updateVatPreview() {
        const amount = parseFloat(document.getElementById('topupAmount').value);
        const preview = document.getElementById('vatPreview');
        if (isNaN(amount) || amount <= 0) {
            preview.style.display = 'none';
            return;
        }
        const rate = parseFloat(vatInfo.vat_rate) / 100;
        const vat = amount * rate;
        const total = amount + vat;
        if (vatInfo.vat_applicable) {
            document.getElementById('vatBreakdown').innerHTML =
                `£${amount.toFixed(2)} + £${vat.toFixed(2)} VAT (${parseFloat(vatInfo.vat_rate).toFixed(0)}%) = <strong>£${total.toFixed(2)} total charge</strong>`;
        } else {
            document.getElementById('vatBreakdown').innerHTML =
                `<strong>£${amount.toFixed(2)} total charge</strong> (VAT not applicable)`;
        }
        preview.style.display = 'block';
    }

    // Load activity
    async function loadActivity() {
        try {
            const resp = await apiFetch(API_BASE + '/events?limit=25');
            const tbody = document.getElementById('activityBody');

            if (!resp.data || resp.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No activity yet.</td></tr>';
                return;
            }

            tbody.innerHTML = resp.data.map(e => `
                <tr>
                    <td class="small">${esc(fmtDate(e.created_at))}</td>
                    <td>${esc(eventLabel(e.event_type))}</td>
                    <td>${e.topup_amount ? esc(fmt(e.topup_amount)) : '—'}</td>
                    <td><span class="badge badge-${esc(e.status)}">${esc(e.status)}</span></td>
                    <td class="small text-muted">${esc(e.failure_message) || (e.requires_action_url ? '<a href="' + encodeURI(e.requires_action_url) + '">Complete payment</a>' : '—')}</td>
                </tr>
            `).join('');
        } catch (e) {
            console.error('[AutoTopUp] Failed to load activity:', e);
            document.getElementById('activityBody').innerHTML =
                '<tr><td colspan="5" class="text-center text-danger py-4">Failed to load activity.</td></tr>';
        }
    }

    // Save settings
    document.getElementById('autoTopUpForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const enabled = document.getElementById('enableToggle').checked;

        if (enabled) {
            // Show confirmation modal
            const summary = `
                <table class="w-100">
                    <tr><td class="text-muted">Status</td><td class="text-end fw-semibold">Enabled</td></tr>
                    <tr><td class="text-muted">Trigger below</td><td class="text-end fw-semibold">${fmt(document.getElementById('thresholdAmount').value)}</td></tr>
                    <tr><td class="text-muted">Top-up amount</td><td class="text-end fw-semibold">${fmt(document.getElementById('topupAmount').value)}</td></tr>
                    <tr><td class="text-muted">Max per day</td><td class="text-end fw-semibold">${document.getElementById('maxPerDay').value}</td></tr>
                    <tr><td class="text-muted">Daily cap</td><td class="text-end fw-semibold">${document.getElementById('dailyCap').value ? fmt(document.getElementById('dailyCap').value) : 'No limit'}</td></tr>
                </table>`;
            document.getElementById('confirmSummary').innerHTML = summary;
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        } else {
            submitSettings();
        }
    });

    document.getElementById('confirmSaveBtn').addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
        submitSettings();
    });

    async function submitSettings() {
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        const data = {
            enabled: document.getElementById('enableToggle').checked,
            threshold_amount: parseFloat(document.getElementById('thresholdAmount').value) || null,
            topup_amount: parseFloat(document.getElementById('topupAmount').value) || null,
            max_topups_per_day: parseInt(document.getElementById('maxPerDay').value),
            daily_topup_cap: parseFloat(document.getElementById('dailyCap').value) || null,
            min_minutes_between_topups: parseInt(document.getElementById('cooldown').value) || 0,
            notify_email_success: document.getElementById('notifyEmailSuccess').checked,
            notify_email_failure: document.getElementById('notifyEmailFailure').checked,
            notify_inapp_success: document.getElementById('notifyInappSuccess').checked,
            notify_inapp_failure: document.getElementById('notifyInappFailure').checked,
            notify_requires_action: document.getElementById('notifyRequiresAction').checked,
        };

        try {
            const resp = await apiFetch(API_BASE, {
                method: 'PUT',
                body: JSON.stringify(data),
            });

            if (resp.success) {
                currentConfig = resp.data;
                renderStatus();
                renderForm();
                showToast('Settings saved successfully.', 'success');
            }
        } catch (e) {
            const msg = e.message || (e.errors ? Object.values(e.errors).flat().join(', ') : 'Failed to save settings.');
            showToast(msg, 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Settings';
        }
    }

    // Payment method setup
    function setupPaymentMethod() {
        apiFetch(API_BASE + '/setup-payment-method', { method: 'POST' })
            .then(resp => {
                if (resp.data?.url) {
                    window.location.href = resp.data.url;
                }
            })
            .catch(e => showToast(e.message || 'Failed to start payment setup.', 'danger'));
    }

    document.getElementById('addPmBtn')?.addEventListener('click', setupPaymentMethod);
    document.getElementById('changePmBtn')?.addEventListener('click', setupPaymentMethod);

    // Remove payment method
    document.getElementById('removePmBtn')?.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('removePmModal')).show();
    });

    document.getElementById('confirmRemovePm')?.addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('removePmModal')).hide();
        apiFetch(API_BASE + '/payment-method/remove', { method: 'POST' })
            .then(() => {
                showToast('Payment method removed.', 'success');
                loadConfig();
                loadActivity();
            })
            .catch(e => showToast(e.message || 'Failed to remove payment method.', 'danger'));
    });

    // VAT preview on input
    document.getElementById('topupAmount')?.addEventListener('input', updateVatPreview);

    // Refresh activity
    document.getElementById('refreshActivity')?.addEventListener('click', loadActivity);

    // Toast helper
    function showToast(message, type) {
        const container = document.querySelector('.auto-topup-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `${esc(message)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        container.insertBefore(alert, container.firstChild);
        setTimeout(() => alert.remove(), 5000);
    }

    // Check for setup success from redirect
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('setup') === 'success') {
        showToast('Payment method added successfully.', 'success');
        window.history.replaceState({}, '', window.location.pathname);
    } else if (urlParams.get('setup') === 'cancelled') {
        showToast('Payment method setup was cancelled.', 'warning');
        window.history.replaceState({}, '', window.location.pathname);
    }

    // Init
    loadConfig();
    loadActivity();

    console.log('[AutoTopUp] Initialized');
})();
</script>
@endpush

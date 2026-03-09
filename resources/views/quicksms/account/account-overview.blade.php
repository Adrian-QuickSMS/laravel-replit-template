@extends('layouts.quicksms')

@section('title', $account['name'])

@push('styles')
<style>
.page-header {
    margin-bottom: 1.5rem;
}
.page-header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.section-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}
.section-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.section-title i {
    color: #886cc0;
}
.section-body {
    padding: 1.25rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 500;
}
.status-pill.active,
.status-pill.active_standard,
.status-pill.active_dynamic {
    background: #dcfce7;
    color: #166534;
}
.status-pill.test_standard,
.status-pill.test_dynamic {
    background: #fef3c7;
    color: #92400e;
}
.status-pill.pending_verification {
    background: #e0e7ff;
    color: #3730a3;
}
.status-pill.suspended {
    background: #fef3c7;
    color: #92400e;
}
.status-pill.closed {
    background: #f3f4f6;
    color: #6b7280;
}
.status-pill i {
    font-size: 0.65rem;
}

.status-note {
    font-size: 0.8rem;
    color: #6b7280;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}
.status-note i {
    color: #9ca3af;
    margin-top: 0.125rem;
}

.overview-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    text-align: center;
}
.stat-card .stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
    letter-spacing: 0.025em;
    margin-bottom: 0.25rem;
}
.stat-card .stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #374151;
}

.current-values {
    background: #f9fafb;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
}
.current-values .label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
    letter-spacing: 0.025em;
}
.current-values .value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #374151;
}

.usage-metric {
    margin-bottom: 1.5rem;
}
.usage-metric:last-child {
    margin-bottom: 0;
}
.usage-metric .metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}
.usage-metric .metric-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
}
.usage-metric .metric-value {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}
.usage-metric .progress {
    height: 10px;
    border-radius: 5px;
    background: #f3f4f6;
}
.usage-metric .progress-bar {
    border-radius: 5px;
    transition: width 0.5s ease;
}
.usage-metric .progress-bar.normal { background: #886cc0; }
.usage-metric .progress-bar.warning { background: #f59e0b; }
.usage-metric .progress-bar.critical { background: #ef4444; }

.enforcement-state {
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.enforcement-state.normal {
    background: #f0fdf4;
    border: none;
}
.enforcement-state.warning {
    background: #fefce8;
    border: none;
}
.enforcement-state.blocked {
    background: #fef2f2;
    border: none;
}
.enforcement-state .state-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.enforcement-state.normal .state-icon { background: #dcfce7; color: #16a34a; }
.enforcement-state.warning .state-icon { background: #fef3c7; color: #d97706; }
.enforcement-state.blocked .state-icon { background: #fee2e2; color: #dc2626; }
.enforcement-state .state-title {
    font-size: 0.9rem;
    font-weight: 600;
}
.enforcement-state.normal .state-title { color: #166534; }
.enforcement-state.warning .state-title { color: #92400e; }
.enforcement-state.blocked .state-title { color: #991b1b; }
.enforcement-state .state-desc {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.125rem;
}

.live-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.7rem;
    color: #16a34a;
    font-weight: 500;
}
.live-indicator .pulse {
    width: 8px;
    height: 8px;
    background: #16a34a;
    border-radius: 50%;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.8); }
}

.assets-tabs .nav-tabs {
    border-bottom: 1px solid #e5e7eb;
}
.assets-tabs .nav-link {
    font-size: 0.8rem;
    color: #6b7280;
    border: none;
    padding: 0.625rem 1rem;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
}
.assets-tabs .nav-link:hover {
    color: #886cc0;
    border-color: transparent;
}
.assets-tabs .nav-link.active {
    color: #886cc0;
    font-weight: 500;
    border-bottom-color: #886cc0;
    background: transparent;
}
.assets-tabs .nav-link .badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
    margin-left: 0.375rem;
    background: #f3e8ff;
    color: #886cc0;
}

.btn-manage {
    font-size: 0.75rem;
    padding: 0.25rem 0.625rem;
    color: #886cc0;
    border: 1px solid #886cc0;
    background: transparent;
    border-radius: 4px;
}
.btn-manage:hover {
    background: #f3e8ff;
    color: #7c3aed;
}

.empty-assets {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}
.empty-assets i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}
.empty-assets p {
    font-size: 0.85rem;
    margin: 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mb-0" style="width: 100%;">
                <div class="card-body py-2 px-3" style="width: 100%;">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                            <li class="breadcrumb-item"><a href="{{ route('account') }}" style="color: #886cc0;">Account</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('account.sub-accounts') }}" style="color: #886cc0;">Sub-Accounts, Users & Permissions</a></li>
                            <li class="breadcrumb-item active" style="color: #6b7280;">{{ $account['name'] }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <div class="page-header">
        <h1>{{ $account['name'] }}</h1>
    </div>
    
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-circle-check"></i>
                Account Status
            </h2>
        </div>
        <div class="section-body">
            <div class="d-flex align-items-center justify-content-between mb-3" style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem;">
                <div>
                    <div style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0.25rem;">Current Status</div>
                    @php
                        $statusLabels = [
                            'pending_verification' => 'Pending Verification',
                            'test_standard' => 'Test Mode',
                            'test_dynamic' => 'Test Mode',
                            'active_standard' => 'Active',
                            'active_dynamic' => 'Active',
                            'suspended' => 'Suspended',
                            'closed' => 'Closed',
                        ];
                        $statusLabel = $statusLabels[$account['status']] ?? ucfirst(str_replace('_', ' ', $account['status']));
                    @endphp
                    <span class="status-pill {{ $account['status'] }}">
                        <i class="fas fa-circle"></i> {{ $statusLabel }}
                    </span>
                </div>
                @if($account['account_number'])
                    <div style="font-size: 0.8rem; color: #6b7280;">
                        Account # <strong style="color: #374151;">{{ $account['account_number'] }}</strong>
                    </div>
                @endif
            </div>
            
            <div class="status-note">
                <i class="fas fa-info-circle"></i>
                <div>
                    @if(in_array($account['status'], ['active_standard', 'active_dynamic']))
                        This account is active. All sub-accounts and users can send messages according to their permissions.
                    @elseif(in_array($account['status'], ['test_standard', 'test_dynamic']))
                        This account is in test mode. Messages can only be sent to approved test numbers.
                    @elseif($account['status'] === 'suspended')
                        This account is suspended. All messaging is disabled until the account is reactivated.
                    @elseif($account['status'] === 'pending_verification')
                        This account is pending verification. Complete the activation steps to start sending messages.
                    @else
                        This account is {{ str_replace('_', ' ', $account['status']) }}.
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-chart-pie"></i>
                Account Summary
            </h2>
        </div>
        <div class="section-body">
            <div class="overview-stats">
                <div class="stat-card">
                    <div class="stat-label">Sub-Accounts</div>
                    <div class="stat-value">{{ $account['total_sub_accounts'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value">{{ $account['total_users'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Credit Limit</div>
                    <div class="stat-value">&pound;{{ number_format($account['limits']['credit_limit'], 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Combined Message Cap</div>
                    <div class="stat-value">{{ number_format($account['limits']['message_cap']) }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-sliders"></i>
                Aggregated Limits
            </h2>
        </div>
        <div class="section-body">
            <div class="current-values">
                <div class="row">
                    <div class="col-md-3">
                        <div class="label">Current Spend</div>
                        <div class="value">&pound;{{ number_format($account['monthly_spend'], 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="label">Current Messages</div>
                        <div class="value">{{ number_format($account['monthly_messages']) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="label">Combined Spend Cap</div>
                        <div class="value">&pound;{{ number_format($account['limits']['spend_cap'], 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="label">Combined Message Cap</div>
                        <div class="value">{{ number_format($account['limits']['message_cap']) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="status-note mt-3">
                <i class="fas fa-info-circle"></i>
                <div>
                    These values are aggregated across all sub-accounts. To adjust individual limits, visit each sub-account's detail page.
                </div>
            </div>
        </div>
    </div>
    
    @php
        $spendPercent = $account['limits']['spend_cap'] > 0 
            ? min(100, ($account['monthly_spend'] / $account['limits']['spend_cap']) * 100) 
            : 0;
        $msgPercent = $account['limits']['message_cap'] > 0 
            ? min(100, ($account['monthly_messages'] / $account['limits']['message_cap']) * 100) 
            : 0;
        
        $spendClass = $spendPercent >= 90 ? 'critical' : ($spendPercent >= 75 ? 'warning' : 'normal');
        $msgClass = $msgPercent >= 90 ? 'critical' : ($msgPercent >= 75 ? 'warning' : 'normal');
        
        $enforcementState = 'normal';
        if ($spendPercent >= 100 || $msgPercent >= 100) {
            $enforcementState = 'blocked';
        } elseif ($spendPercent >= 75 || $msgPercent >= 75) {
            $enforcementState = 'warning';
        }
    @endphp
    
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Live Usage & Telemetry
            </h2>
            <span class="live-indicator">
                <span class="pulse"></span>
                Live
            </span>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="usage-metric">
                        <div class="metric-header">
                            <span class="metric-label"><i class="fas fa-pound-sign me-1"></i>Spend vs Cap</span>
                            <span class="metric-value" id="spend-value">&pound;{{ number_format($account['monthly_spend'], 2) }} / &pound;{{ number_format($account['limits']['spend_cap'], 2) }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $spendClass }}" id="spend-bar" role="progressbar" style="width: {{ $spendPercent }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="usage-metric">
                        <div class="metric-header">
                            <span class="metric-label"><i class="fas fa-envelope me-1"></i>Messages vs Limit</span>
                            <span class="metric-value" id="msgs-value">{{ number_format($account['monthly_messages']) }} / {{ number_format($account['limits']['message_cap']) }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $msgClass }}" id="msgs-bar" role="progressbar" style="width: {{ $msgPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="enforcement-state {{ $enforcementState }}" id="enforcement-state-display">
                <div class="state-icon">
                    @if($enforcementState === 'normal')
                        <i class="fas fa-shield-check"></i>
                    @elseif($enforcementState === 'warning')
                        <i class="fas fa-exclamation-triangle"></i>
                    @else
                        <i class="fas fa-ban"></i>
                    @endif
                </div>
                <div>
                    <div class="state-title">
                        @if($enforcementState === 'normal')
                            Normal
                        @elseif($enforcementState === 'warning')
                            Warning
                        @else
                            Blocked
                        @endif
                    </div>
                    <div class="state-desc">
                        @if($enforcementState === 'normal')
                            All systems operational. Usage is within acceptable limits.
                        @elseif($enforcementState === 'warning')
                            Approaching limit threshold. Consider reviewing usage patterns.
                        @else
                            Limit exceeded. Some sub-accounts may be blocked from sending.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-card" id="users-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-users"></i>
                Main Account Users
            </h2>
            @if($can_manage_users)
            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#addUserModal" style="background: #886cc0; color: white; font-size: 0.8rem; padding: 0.4rem 0.875rem; border-radius: 0.375rem;">
                <i class="fas fa-plus me-1"></i>Add User
            </button>
            @endif
        </div>
        <div class="section-body p-0">
            @if(count($main_account_users) > 0)
                <table class="table mb-0" style="font-size: 0.85rem;">
                    <thead>
                        <tr style="background: #f9fafb;">
                            <th style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #6b7280; padding: 0.625rem 1rem; border-bottom: 1px solid #e5e7eb;">Name</th>
                            <th style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #6b7280; padding: 0.625rem 1rem; border-bottom: 1px solid #e5e7eb;">Email</th>
                            <th style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #6b7280; padding: 0.625rem 1rem; border-bottom: 1px solid #e5e7eb;">Role</th>
                            <th style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #6b7280; padding: 0.625rem 1rem; border-bottom: 1px solid #e5e7eb;">Status</th>
                            <th style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #6b7280; padding: 0.625rem 1rem; border-bottom: 1px solid #e5e7eb;">Sender Capability</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($main_account_users as $user)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 0.75rem 1rem; vertical-align: middle;">
                                    <div style="font-weight: 500; color: #374151;">{{ $user['name'] }}</div>
                                    @if($user['is_account_owner'])
                                        <span style="font-size: 0.65rem; background: #f3e8ff; color: #7c3aed; padding: 2px 6px; border-radius: 4px;">Account Owner</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem 1rem; vertical-align: middle; color: #6b7280;">{{ $user['email'] }}</td>
                                <td style="padding: 0.75rem 1rem; vertical-align: middle;">
                                    <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; background: #f3e8ff; color: #886cc0;">{{ $user['role_label'] }}</span>
                                </td>
                                <td style="padding: 0.75rem 1rem; vertical-align: middle;">
                                    @php
                                        $userStatusBg = $user['status'] === 'active' ? '#dcfce7' : ($user['status'] === 'suspended' ? '#fef3c7' : '#f3f4f6');
                                        $userStatusColor = $user['status'] === 'active' ? '#166534' : ($user['status'] === 'suspended' ? '#92400e' : '#6b7280');
                                    @endphp
                                    <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; background: {{ $userStatusBg }}; color: {{ $userStatusColor }};">{{ ucfirst($user['status']) }}</span>
                                </td>
                                <td style="padding: 0.75rem 1rem; vertical-align: middle;">
                                    @if($user['sender_capability'] !== 'none')
                                        <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; background: #e0e7ff; color: #3730a3;">{{ ucfirst($user['sender_capability']) }}</span>
                                    @else
                                        <span style="font-size: 0.75rem; color: #9ca3af;">None</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-4 text-muted" style="font-size: 0.9rem;">
                    <i class="fas fa-user-plus mb-2" style="font-size: 1.5rem; color: #886cc0;"></i>
                    <p class="mb-2">No users assigned directly to the main account yet.</p>
                    @if($can_manage_users)
                    <button type="button" class="btn btn-manage" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-cube"></i>
                Assigned Assets
            </h2>
        </div>
        <div class="section-body p-0">
            <div class="assets-tabs">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-senderids">
                            SMS SenderIDs <span class="badge">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-numbers">
                            Numbers <span class="badge">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rcs">
                            RCS Agents <span class="badge">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-templates">
                            Templates <span class="badge">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-email">
                            Email-to-SMS <span class="badge">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-api">
                            API Connections <span class="badge">0</span>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-senderids">
                        <div class="text-center py-4 text-muted" style="font-size: 0.9rem;">
                            <i class="fas fa-id-card-alt mb-2" style="font-size: 1.5rem; color: #886cc0;"></i>
                            <p class="mb-2">No Sender IDs assigned to this account yet.</p>
                            <a href="{{ route('management.sms-sender-id') }}" class="btn btn-manage">Manage Sender IDs</a>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-numbers">
                        <div class="text-center py-4 text-muted" style="font-size: 0.9rem;">
                            <i class="fas fa-phone mb-2" style="font-size: 1.5rem; color: #886cc0;"></i>
                            <p class="mb-2">No numbers assigned to this account yet.</p>
                            <a href="{{ route('management.numbers') }}" class="btn btn-manage">Manage Numbers</a>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-rcs">
                        <div class="text-center py-4 text-muted" style="font-size: 0.9rem;">
                            <i class="fas fa-comment-dots mb-2" style="font-size: 1.5rem; color: #886cc0;"></i>
                            <p class="mb-2">No RCS agents assigned to this account yet.</p>
                            <a href="{{ route('management.rcs-agent') }}" class="btn btn-manage">Manage RCS Agents</a>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-templates">
                        <div class="text-center py-4 text-muted" style="font-size: 0.9rem;">
                            <i class="fas fa-file-alt mb-2" style="font-size: 1.5rem; color: #886cc0;"></i>
                            <p class="mb-2">No templates assigned to this account yet.</p>
                            <a href="{{ route('management.templates') }}" class="btn btn-manage">Manage Templates</a>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-email">
                        <div class="text-center py-4 text-muted" style="font-size: 0.9rem;">
                            <i class="fas fa-envelope mb-2" style="font-size: 1.5rem; color: #886cc0;"></i>
                            <p class="mb-2">No Email-to-SMS setups assigned to this account yet.</p>
                            <a href="{{ route('management.email-to-sms') }}" class="btn btn-manage">Manage Email-to-SMS</a>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-api">
                        <div class="text-center py-4 text-muted" style="font-size: 0.9rem;">
                            <i class="fas fa-plug mb-2" style="font-size: 1.5rem; color: #886cc0;"></i>
                            <p class="mb-2">No API connections assigned to this account yet.</p>
                            <a href="{{ route('management.api-connections') }}" class="btn btn-manage">Manage API Connections</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($can_manage_users)
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User to Main Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="addUserTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="invite-tab" data-bs-toggle="tab" data-bs-target="#invite-pane" type="button" role="tab">
                            Send Invitation
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="direct-tab" data-bs-toggle="tab" data-bs-target="#direct-pane" type="button" role="tab">
                            Direct Creation
                            <span class="badge ms-1" style="font-size: 0.65rem; background: #f3e8ff; color: #6b21a8;">Admin Only</span>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="addUserTabContent">
                    <div class="tab-pane fade show active" id="invite-pane" role="tabpanel">
                        <div class="alert mb-4" style="background: #f3e8ff; border: none; color: #6b21a8; font-size: 0.85rem;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle me-3 mt-1"></i>
                                <div>
                                    <strong>Invitation Flow:</strong> The user will receive an email to set their password and enrol MFA. Once completed, they become Active.
                                </div>
                            </div>
                        </div>
                        
                        <form id="invite-user-form">
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="invite-email" placeholder="user@company.com" required>
                                <div class="form-text">Invitation will be sent to this email address</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign to</label>
                                <select class="form-select" id="invite-sub-account">
                                    <option value="" selected>Main Account (no sub-account)</option>
                                    @foreach($sub_accounts_list as $sa)
                                        <option value="{{ $sa['id'] }}">{{ $sa['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Defaults to Main Account. Optionally assign to a sub-account.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="invite-role" required>
                                    <option value="">Select Role...</option>
                                    <option value="admin">Admin</option>
                                    <option value="messaging_manager">Messaging Manager</option>
                                    <option value="finance">Finance / Billing</option>
                                    <option value="developer">Developer / API User</option>
                                    <option value="user">User</option>
                                    <option value="readonly">Read-Only / Auditor</option>
                                </select>
                                <div class="form-text">Determines navigation and feature access</div>
                            </div>
                            <div class="mb-3" id="sender-capability-group">
                                <label class="form-label">Sender Capability Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="invite-sender-capability" required>
                                    <option value="">Select Capability...</option>
                                    <option value="advanced">Advanced Sender - Full content creation, Contact Book, CSV uploads</option>
                                    <option value="restricted">Restricted Sender - Templates only, predefined lists only</option>
                                    <option value="none">None - No sending capability</option>
                                </select>
                                <div class="form-text">Controls how messages can be composed and sent</div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="direct-pane" role="tabpanel">
                        <div class="alert mb-4" style="background: #f3e8ff; border: none; color: #6b21a8; font-size: 0.85rem;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle me-3 mt-1"></i>
                                <div>
                                    <strong>Elevated Risk Action</strong><br>
                                    Direct user creation bypasses the standard invitation flow. The user will be required to:
                                    <ul class="mb-0 mt-2">
                                        <li>Reset their password on first login</li>
                                        <li>Enrol MFA immediately before accessing the platform</li>
                                    </ul>
                                    This action is logged as a high-risk audit event.
                                </div>
                            </div>
                        </div>
                        
                        <form id="direct-create-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="direct-first-name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="direct-last-name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="direct-email" placeholder="user@company.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Temporary Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="direct-temp-password" required minlength="12">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-generate-password">Generate</button>
                                </div>
                                <div class="form-text">Minimum 12 characters. User must change this on first login.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign to</label>
                                <select class="form-select" id="direct-sub-account">
                                    <option value="" selected>Main Account (no sub-account)</option>
                                    @foreach($sub_accounts_list as $sa)
                                        <option value="{{ $sa['id'] }}">{{ $sa['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="direct-role" required>
                                        <option value="">Select Role...</option>
                                        <option value="admin">Admin</option>
                                        <option value="messaging_manager">Messaging Manager</option>
                                        <option value="finance">Finance / Billing</option>
                                        <option value="developer">Developer / API User</option>
                                        <option value="user">User</option>
                                        <option value="readonly">Read-Only / Auditor</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3" id="direct-sender-capability-group">
                                    <label class="form-label">Sender Capability Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="direct-sender-capability" required>
                                        <option value="">Select Capability...</option>
                                        <option value="advanced">Advanced Sender</option>
                                        <option value="restricted">Restricted Sender</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason for Direct Creation <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="direct-reason" rows="2" placeholder="e.g., Urgent onboarding required, user has no email access" required></textarea>
                                <div class="form-text">This will be recorded in the audit log</div>
                            </div>
                        </form>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="direct-confirm-risk">
                            <label class="form-check-label" for="direct-confirm-risk">
                                I understand this is a high-risk action and accept responsibility for this user account
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="btn-send-invite" style="background: #886cc0; color: white;">Send Invitation</button>
                <button type="button" class="btn" id="btn-direct-create" style="display: none; background: #886cc0; color: white;">Create User Directly</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var usageData = {
        spend: {{ $account['monthly_spend'] }},
        messages: {{ $account['monthly_messages'] }},
        spendCap: {{ $account['limits']['spend_cap'] }},
        messageCap: {{ $account['limits']['message_cap'] }}
    };
    
    function updateUsageDisplay() {
        var spendPercent = usageData.spendCap > 0 ? Math.min(100, (usageData.spend / usageData.spendCap) * 100) : 0;
        var msgPercent = usageData.messageCap > 0 ? Math.min(100, (usageData.messages / usageData.messageCap) * 100) : 0;
        
        document.getElementById('spend-value').textContent = '\u00A3' + usageData.spend.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / \u00A3' + usageData.spendCap.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('msgs-value').textContent = usageData.messages.toLocaleString() + ' / ' + usageData.messageCap.toLocaleString();
        
        var spendBar = document.getElementById('spend-bar');
        var msgsBar = document.getElementById('msgs-bar');
        
        spendBar.style.width = spendPercent + '%';
        msgsBar.style.width = msgPercent + '%';
        
        spendBar.className = 'progress-bar ' + (spendPercent >= 90 ? 'critical' : (spendPercent >= 75 ? 'warning' : 'normal'));
        msgsBar.className = 'progress-bar ' + (msgPercent >= 90 ? 'critical' : (msgPercent >= 75 ? 'warning' : 'normal'));
        
        updateEnforcementState(spendPercent, msgPercent);
    }
    
    function updateEnforcementState(spendPercent, msgPercent) {
        var stateDisplay = document.getElementById('enforcement-state-display');
        var stateTitle = stateDisplay.querySelector('.state-title');
        var stateDesc = stateDisplay.querySelector('.state-desc');
        var stateIcon = stateDisplay.querySelector('.state-icon i');
        
        var state = 'normal';
        if (spendPercent >= 100 || msgPercent >= 100) {
            state = 'blocked';
        } else if (spendPercent >= 75 || msgPercent >= 75) {
            state = 'warning';
        }
        
        stateDisplay.className = 'enforcement-state ' + state;
        
        var states = {
            'normal': { title: 'Normal', desc: 'All systems operational. Usage is within acceptable limits.', icon: 'fa-shield-check' },
            'warning': { title: 'Warning', desc: 'Approaching limit threshold. Consider reviewing usage patterns.', icon: 'fa-exclamation-triangle' },
            'blocked': { title: 'Blocked', desc: 'Limit exceeded. Some sub-accounts may be blocked from sending.', icon: 'fa-ban' }
        };
        
        stateTitle.textContent = states[state].title;
        stateDesc.textContent = states[state].desc;
        stateIcon.className = 'fas ' + states[state].icon;
    }
    
    function apiRequest(url, method, data) {
        return new Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.getAttribute('content'));
            }
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try { resolve(JSON.parse(xhr.responseText)); } catch(e) { resolve({}); }
                } else {
                    var errMsg = 'Request failed';
                    try {
                        var errBody = JSON.parse(xhr.responseText);
                        if (errBody.errors) {
                            var msgs = [];
                            Object.keys(errBody.errors).forEach(function(k) {
                                msgs = msgs.concat(errBody.errors[k]);
                            });
                            errMsg = msgs.join(', ');
                        } else if (errBody.message) {
                            errMsg = errBody.message;
                        }
                    } catch(e) {}
                    reject(new Error(errMsg));
                }
            };
            xhr.onerror = function() { reject(new Error('Network error')); };
            xhr.send(data ? JSON.stringify(data) : null);
        });
    }
    
    function showToast(type, title, message) {
        var bgColors = { success: '#dcfce7', error: '#fee2e2', warning: '#fef3c7', info: '#f3e8ff' };
        var textColors = { success: '#166534', error: '#991b1b', warning: '#92400e', info: '#6b21a8' };
        var icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        var toast = document.createElement('div');
        toast.className = 'alert position-fixed';
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px; background: ' + (bgColors[type] || bgColors.info) + '; border: none; color: ' + (textColors[type] || textColors.info) + ';';
        toast.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + ' me-2"></i><strong>' + title + '</strong> ' + message;
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 4000);
    }
    
    var roleSelect = document.getElementById('invite-role');
    if (roleSelect) {
    var senderCapabilityGroup = document.getElementById('sender-capability-group');
    var btnSendInvite = document.getElementById('btn-send-invite');
    var btnDirectCreate = document.getElementById('btn-direct-create');
    var directSenderCapabilityGroup = document.getElementById('direct-sender-capability-group');
    
    roleSelect.addEventListener('change', function() {
        var nonMessagingRoles = ['finance', 'readonly'];
        if (nonMessagingRoles.includes(this.value)) {
            senderCapabilityGroup.style.display = 'none';
            document.getElementById('invite-sender-capability').removeAttribute('required');
        } else {
            senderCapabilityGroup.style.display = 'block';
            document.getElementById('invite-sender-capability').setAttribute('required', 'required');
        }
    });
    
    document.getElementById('direct-role').addEventListener('change', function() {
        var nonMessagingRoles = ['finance', 'readonly'];
        if (nonMessagingRoles.includes(this.value)) {
            directSenderCapabilityGroup.style.display = 'none';
            document.getElementById('direct-sender-capability').removeAttribute('required');
        } else {
            directSenderCapabilityGroup.style.display = 'block';
            document.getElementById('direct-sender-capability').setAttribute('required', 'required');
        }
    });
    
    document.querySelectorAll('#addUserTabs button').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function(e) {
            if (e.target.id === 'direct-tab') {
                btnSendInvite.style.display = 'none';
                btnDirectCreate.style.display = 'inline-block';
            } else {
                btnSendInvite.style.display = 'inline-block';
                btnDirectCreate.style.display = 'none';
            }
        });
    });
    
    document.getElementById('btn-generate-password').addEventListener('click', function() {
        var chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
        var password = '';
        for (var i = 0; i < 16; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        var input = document.getElementById('direct-temp-password');
        input.value = password;
        input.type = 'text';
        setTimeout(function() { input.type = 'password'; }, 3000);
    });
    
    btnSendInvite.addEventListener('click', function() {
        var email = document.getElementById('invite-email').value.trim();
        var subAccountId = document.getElementById('invite-sub-account').value;
        var role = document.getElementById('invite-role').value;
        var senderCapability = document.getElementById('invite-sender-capability').value;
        
        var nonMessagingRoles = ['finance', 'readonly'];
        var requiresCapability = !nonMessagingRoles.includes(role);
        
        if (!email || !role) {
            showToast('warning', 'Missing Fields', 'Please fill in all required fields');
            return;
        }
        if (requiresCapability && !senderCapability) {
            showToast('warning', 'Missing Fields', 'Please select a Sender Capability Level');
            return;
        }
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('warning', 'Invalid Email', 'Please enter a valid email address');
            return;
        }
        
        var payload = { email: email, role: role };
        if (subAccountId) payload.sub_account_id = subAccountId;
        if (requiresCapability && senderCapability) payload.sender_capability = senderCapability;
        
        btnSendInvite.disabled = true;
        btnSendInvite.textContent = 'Sending...';
        
        apiRequest('/api/invitations', 'POST', payload).then(function() {
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            document.getElementById('invite-user-form').reset();
            showToast('success', 'Invitation Sent', 'The user will receive an email invitation shortly.');
            setTimeout(function() { window.location.reload(); }, 1500);
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btnSendInvite.disabled = false;
            btnSendInvite.textContent = 'Send Invitation';
        });
    });
    
    btnDirectCreate.addEventListener('click', function() {
        var firstName = document.getElementById('direct-first-name').value.trim();
        var lastName = document.getElementById('direct-last-name').value.trim();
        var email = document.getElementById('direct-email').value.trim();
        var tempPassword = document.getElementById('direct-temp-password').value;
        var subAccountId = document.getElementById('direct-sub-account').value;
        var role = document.getElementById('direct-role').value;
        var senderCapability = document.getElementById('direct-sender-capability').value;
        var reason = document.getElementById('direct-reason').value.trim();
        var confirmRisk = document.getElementById('direct-confirm-risk').checked;
        
        var nonMessagingRoles = ['finance', 'readonly'];
        var requiresCapability = !nonMessagingRoles.includes(role);
        
        if (!firstName || !lastName || !email || !tempPassword || !role || !reason) {
            showToast('warning', 'Missing Fields', 'Please fill in all required fields');
            return;
        }
        if (requiresCapability && !senderCapability) {
            showToast('warning', 'Missing Fields', 'Please select a Sender Capability Level');
            return;
        }
        if (tempPassword.length < 12) {
            showToast('warning', 'Invalid Password', 'Password must be at least 12 characters');
            return;
        }
        if (!confirmRisk) {
            showToast('warning', 'Confirmation Required', 'You must acknowledge the risk before creating a user directly');
            return;
        }
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('warning', 'Invalid Email', 'Please enter a valid email address');
            return;
        }
        
        var payload = { email: email, role: role, first_name: firstName, last_name: lastName };
        if (subAccountId) payload.sub_account_id = subAccountId;
        if (requiresCapability && senderCapability) payload.sender_capability = senderCapability;
        
        btnDirectCreate.disabled = true;
        btnDirectCreate.textContent = 'Creating...';
        
        apiRequest('/api/invitations', 'POST', payload).then(function() {
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            document.getElementById('direct-create-form').reset();
            document.getElementById('direct-confirm-risk').checked = false;
            showToast('success', 'Invitation Created', 'The user account has been created. They will need to reset their password on first login.');
            setTimeout(function() { window.location.reload(); }, 1500);
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btnDirectCreate.disabled = false;
            btnDirectCreate.textContent = 'Create User Directly';
        });
    });
    } // end if (roleSelect)
    
    setInterval(function() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/sub-accounts', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    var subs = resp.data || resp;
                    var totalSpend = 0, totalMsgs = 0, totalSpendCap = 0, totalMsgCap = 0;
                    subs.forEach(function(s) {
                        totalSpend += parseFloat((s.usage && s.usage.monthly_spend_used) || 0);
                        totalMsgs += parseInt((s.usage && s.usage.monthly_messages_used) || 0);
                        totalSpendCap += parseFloat((s.limits && s.limits.monthly_spending_cap) || 0);
                        totalMsgCap += parseInt((s.limits && s.limits.monthly_message_cap) || 0);
                    });
                    usageData.spend = totalSpend;
                    usageData.messages = totalMsgs;
                    usageData.spendCap = totalSpendCap;
                    usageData.messageCap = totalMsgCap;
                    updateUsageDisplay();
                } catch(e) {}
            }
        };
        xhr.send();
    }, 30000);
});
</script>
@endpush
@extends('layouts.admin')

@section('title', 'Account Overview')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }
.account-filters { margin-bottom: 1.5rem; }
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Accounts</a>
        <span class="separator">/</span>
        <span>Overview</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: #1e3a5f; font-weight: 600;">Account Overview</h4>
            <p class="text-muted mb-0">Manage all client accounts across the platform</p>
        </div>
    </div>

    <div class="admin-filter-bar">
        <div class="filter-group">
            <label>Status</label>
            <select class="form-select form-select-sm">
                <option>All Statuses</option>
                <option>Live</option>
                <option>Test</option>
                <option>Suspended</option>
                <option>Archived</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Account Type</label>
            <select class="form-select form-select-sm">
                <option>All Types</option>
                <option>Enterprise</option>
                <option>SMB</option>
                <option>Startup</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Search</label>
            <input type="text" class="form-control form-control-sm" placeholder="Account ID or name...">
        </div>
        <button class="btn admin-btn-apply">Apply</button>
    </div>

    <div class="admin-card">
        <div class="card-body p-0">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th class="text-end">MTD Volume</th>
                        <th class="text-end">MTD Revenue</th>
                        <th class="text-end">Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">AC</div>
                                <div>
                                    <div class="account-name">Acme Corporation</div>
                                    <div class="account-id">ACC-1234</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status-badge live">Live</span></td>
                        <td>Enterprise</td>
                        <td>Jan 15, 2024</td>
                        <td class="text-end">1,247,832</td>
                        <td class="text-end">£24,892</td>
                        <td class="text-end">£5,420</td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-secondary btn-sm">Impersonate</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">FL</div>
                                <div>
                                    <div class="account-name">Finance Ltd</div>
                                    <div class="account-id">ACC-5678</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status-badge live">Live</span></td>
                        <td>Enterprise</td>
                        <td>Mar 02, 2024</td>
                        <td class="text-end">892,156</td>
                        <td class="text-end">£18,432</td>
                        <td class="text-end">£12,100</td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-secondary btn-sm">Impersonate</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">NC</div>
                                <div>
                                    <div class="account-name">NewClient</div>
                                    <div class="account-id">ACC-7890</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status-badge test">Test</span></td>
                        <td>Startup</td>
                        <td>Jan 18, 2026</td>
                        <td class="text-end">47</td>
                        <td class="text-end">£0</td>
                        <td class="text-end">£0</td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-success btn-sm">Activate</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">TC</div>
                                <div>
                                    <div class="account-name">TestCo</div>
                                    <div class="account-id">ACC-4567</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status-badge suspended">Suspended</span></td>
                        <td>SMB</td>
                        <td>Nov 10, 2025</td>
                        <td class="text-end">0</td>
                        <td class="text-end">£0</td>
                        <td class="text-end">-£240</td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-warning btn-sm">Reactivate</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

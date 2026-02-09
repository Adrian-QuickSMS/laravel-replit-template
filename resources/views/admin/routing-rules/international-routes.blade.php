@extends('layouts.admin')

@section('title', 'International Routes - Routing Rules')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.product-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 1.5rem;
}

.product-tab {
    padding: 0.75rem 1.5rem;
    border: none;
    background: transparent;
    color: #6c757d;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}

.product-tab:hover { color: var(--admin-primary); }
.product-tab.active { color: var(--admin-primary); border-bottom-color: var(--admin-primary); font-weight: 600; }

.route-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #dde4ea;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.route-table th {
    padding: 0.5rem 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    color: #495057;
    white-space: nowrap;
}

.route-table td {
    padding: 0.5rem 0.35rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}

.route-row { cursor: pointer; transition: background 0.15s; }
.route-row:hover { background: #f8f9fa; }
.route-row.expanded { background: #f0f4f8; }

.expand-icon { transition: transform 0.2s; color: #6c757d; font-size: 0.7rem; }
.route-row.expanded .expand-icon { transform: rotate(90deg); }

.expand-panel { display: none; background: #f8f9fb; border-top: 1px solid #e9ecef; }
.expand-panel.show { display: table-row; }

.gateway-cards-container { display: flex; flex-wrap: wrap; gap: 1rem; padding: 1.25rem; }

.gateway-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #dde4ea;
    padding: 1rem;
    min-width: 280px;
    flex: 1;
    max-width: 340px;
    position: relative;
}

.gateway-card.primary-gw { border-color: var(--admin-primary); border-width: 2px; }

.gateway-card .gw-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; }
.gateway-card .gw-name { font-weight: 600; font-size: 0.875rem; color: #212529; }
.gateway-card .gw-supplier { font-size: 0.75rem; color: #6c757d; margin-top: 0.15rem; }

.gw-primary-badge {
    background: var(--admin-primary);
    color: #fff;
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.weight-display { text-align: center; padding: 0.75rem 0; margin-bottom: 0.75rem; border-top: 1px solid #f1f3f5; border-bottom: 1px solid #f1f3f5; }
.weight-value { font-size: 1.75rem; font-weight: 700; color: var(--admin-primary); line-height: 1; }
.weight-label { font-size: 0.65rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.25rem; }

.telemetry-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.75rem; }
.telemetry-stat { background: #f8f9fa; padding: 0.4rem 0.5rem; border-radius: 6px; }
.telemetry-stat .stat-label { font-size: 0.6rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.3px; }
.telemetry-stat .stat-value { font-size: 0.8rem; font-weight: 600; color: #212529; }

.gw-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; padding-top: 0.5rem; border-top: 1px solid #f1f3f5; }
.gw-actions .btn { font-size: 0.7rem; padding: 0.25rem 0.5rem; border-radius: 6px; }

.status-badge { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.7rem; font-weight: 500; }
.status-badge.active { background: #d4f4dd; color: #198754; }
.status-badge.blocked { background: #fde2e2; color: #dc3545; }
.status-badge.online { background: #d4f4dd; color: #198754; }
.status-badge.offline { background: #e0e0e0; color: #6c757d; }

.gateway-count-badge { background: #e8edf3; color: var(--admin-primary); padding: 0.2rem 0.5rem; border-radius: 8px; font-size: 0.7rem; font-weight: 600; }
.rate-snapshot { font-family: 'SFMono-Regular', monospace; font-size: 0.8rem; color: #212529; }

.filter-toolbar { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }

.alpha-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 1rem;
}

.alpha-nav-btn {
    width: 28px;
    height: 28px;
    border: 1px solid #dde4ea;
    border-radius: 6px;
    background: #fff;
    color: #6c757d;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.alpha-nav-btn:hover { background: #e8edf3; color: var(--admin-primary); }
.alpha-nav-btn.active { background: var(--admin-primary); color: #fff; border-color: var(--admin-primary); }
.alpha-nav-btn.disabled { opacity: 0.3; cursor: default; }

.country-iso {
    display: inline-block;
    background: #e8edf3;
    color: var(--admin-primary);
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    font-family: monospace;
    margin-left: 0.35rem;
}
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Routing</a></li>
        <li class="breadcrumb-item active">International Routes</li>
    </ol>
</div>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="margin: 0; color: var(--admin-primary);">Routing Rules</h2>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage route priorities, gateway weights, and customer overrides</p>
    </div>
    <div>
        <button class="btn btn-sm" style="background: var(--admin-primary); color: #fff;" onclick="openAddGatewayToRouteModal()">
            <i class="fas fa-plus me-1"></i>Add Gateway to Route
        </button>
    </div>
</div>

{{-- Tab Navigation --}}
<ul class="nav nav-tabs mb-0" style="border-bottom: 2px solid #e9ecef;">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.system.routing') }}" style="color: #6c757d;">
            <i class="fas fa-flag me-1"></i>UK Routes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.system.routing') }}?tab=international" style="color: var(--admin-primary); font-weight: 600; border-bottom: 2px solid var(--admin-primary); margin-bottom: -2px;">
            <i class="fas fa-globe me-1"></i>International Routes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.system.routing') }}?tab=overrides" style="color: #6c757d;">
            <i class="fas fa-user-cog me-1"></i>Customer Overrides
        </a>
    </li>
</ul>

{{-- Product Selector Tabs --}}
<div class="product-tabs mt-3">
    <button class="product-tab active" data-product="sms" onclick="switchProduct('sms', this)">SMS</button>
    <button class="product-tab" data-product="rcs_basic" onclick="switchProduct('rcs_basic', this)">RCS Basic</button>
    <button class="product-tab" data-product="rcs_single" onclick="switchProduct('rcs_single', this)">RCS Single</button>
</div>

{{-- A-Z Navigation --}}
<div class="alpha-nav" id="alphaNav">
    @php
    $activeLetters = ['A','B','C','D','F','G','I','J','K','N','S','U'];
    @endphp
    @foreach(range('A','Z') as $letter)
    <button class="alpha-nav-btn {{ in_array($letter, $activeLetters) ? '' : 'disabled' }} {{ $letter === 'A' ? 'active' : '' }}"
            onclick="filterByLetter('{{ $letter }}', this)"
            {{ !in_array($letter, $activeLetters) ? 'disabled' : '' }}>
        {{ $letter }}
    </button>
    @endforeach
    <button class="alpha-nav-btn" onclick="filterByLetter('ALL', this)" style="width: auto; padding: 0 0.5rem;">ALL</button>
</div>

{{-- Filter Toolbar --}}
<div class="filter-toolbar">
    <div class="row g-3 align-items-center">
        <div class="col-md-4">
            <input type="text" class="form-control form-control-sm" id="searchRoutes" placeholder="Search by country..." onkeyup="filterRoutes()">
        </div>
        <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterStatus" onchange="filterRoutes()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="blocked">Blocked</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterGateway" onchange="filterRoutes()">
                <option value="">All Gateways</option>
                <option value="gw_bics_intl">BICS International</option>
                <option value="gw_sinch_global">Sinch Global</option>
                <option value="gw_telnyx_global">Telnyx Global</option>
            </select>
        </div>
        <div class="col-md-2 text-end">
            <span class="text-muted" style="font-size: 0.75rem;" id="routeCount">Showing 12 routes</span>
        </div>
    </div>
</div>

{{-- Routes Table --}}
<div class="route-card">
    <div class="table-responsive">
        <table class="table route-table mb-0">
            <thead>
                <tr>
                    <th style="width: 30px;"></th>
                    <th>Country</th>
                    <th>ISO</th>
                    <th>Gateways</th>
                    <th>Primary Gateway</th>
                    <th>Billing</th>
                    <th>Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="routesTableBody">
                @php
                $intlRoutes = [
                    ['id' => 101, 'country' => 'Australia', 'iso' => 'AU', 'letter' => 'A', 'gateway_count' => 2, 'primary_gw' => 'BICS International', 'billing' => 'Per Message', 'rate' => '0.0520', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 70, 'primary' => true, 'status' => 'online', 'delivery_rate' => '97.3%', 'response_time' => '245ms', 'median_dlr' => '2.1s', 'rate' => '0.0520'],
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 30, 'primary' => false, 'status' => 'online', 'delivery_rate' => '96.1%', 'response_time' => '280ms', 'median_dlr' => '2.8s', 'rate' => '0.0545'],
                        ]],
                    ['id' => 102, 'country' => 'Austria', 'iso' => 'AT', 'letter' => 'A', 'gateway_count' => 2, 'primary_gw' => 'Sinch Global', 'billing' => 'Per Message', 'rate' => '0.0680', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 60, 'primary' => true, 'status' => 'online', 'delivery_rate' => '98.1%', 'response_time' => '165ms', 'median_dlr' => '1.4s', 'rate' => '0.0680'],
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 40, 'primary' => false, 'status' => 'online', 'delivery_rate' => '97.4%', 'response_time' => '182ms', 'median_dlr' => '1.7s', 'rate' => '0.0695'],
                        ]],
                    ['id' => 103, 'country' => 'Brazil', 'iso' => 'BR', 'letter' => 'B', 'gateway_count' => 3, 'primary_gw' => 'Telnyx Global', 'billing' => 'Per Segment', 'rate' => '0.0420', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'Telnyx Global', 'supplier' => 'Telnyx', 'code' => 'gw_telnyx_global', 'weight' => 50, 'primary' => true, 'status' => 'online', 'delivery_rate' => '94.8%', 'response_time' => '310ms', 'median_dlr' => '3.2s', 'rate' => '0.0420'],
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 30, 'primary' => false, 'status' => 'online', 'delivery_rate' => '93.5%', 'response_time' => '340ms', 'median_dlr' => '3.8s', 'rate' => '0.0445'],
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 20, 'primary' => false, 'status' => 'offline', 'delivery_rate' => '92.1%', 'response_time' => '380ms', 'median_dlr' => '4.5s', 'rate' => '0.0460'],
                        ]],
                    ['id' => 104, 'country' => 'Canada', 'iso' => 'CA', 'letter' => 'C', 'gateway_count' => 2, 'primary_gw' => 'BICS International', 'billing' => 'Per Message', 'rate' => '0.0180', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 55, 'primary' => true, 'status' => 'online', 'delivery_rate' => '98.9%', 'response_time' => '125ms', 'median_dlr' => '0.8s', 'rate' => '0.0180'],
                            ['name' => 'Telnyx Global', 'supplier' => 'Telnyx', 'code' => 'gw_telnyx_global', 'weight' => 45, 'primary' => false, 'status' => 'online', 'delivery_rate' => '98.2%', 'response_time' => '142ms', 'median_dlr' => '1.0s', 'rate' => '0.0195'],
                        ]],
                    ['id' => 105, 'country' => 'Denmark', 'iso' => 'DK', 'letter' => 'D', 'gateway_count' => 1, 'primary_gw' => 'Sinch Global', 'billing' => 'Per Message', 'rate' => '0.0380', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 100, 'primary' => true, 'status' => 'online', 'delivery_rate' => '99.2%', 'response_time' => '112ms', 'median_dlr' => '0.7s', 'rate' => '0.0380'],
                        ]],
                    ['id' => 106, 'country' => 'France', 'iso' => 'FR', 'letter' => 'F', 'gateway_count' => 3, 'primary_gw' => 'BICS International', 'billing' => 'Per Message', 'rate' => '0.0620', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 50, 'primary' => true, 'status' => 'online', 'delivery_rate' => '97.8%', 'response_time' => '155ms', 'median_dlr' => '1.3s', 'rate' => '0.0620'],
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 35, 'primary' => false, 'status' => 'online', 'delivery_rate' => '97.1%', 'response_time' => '172ms', 'median_dlr' => '1.6s', 'rate' => '0.0640'],
                            ['name' => 'Telnyx Global', 'supplier' => 'Telnyx', 'code' => 'gw_telnyx_global', 'weight' => 15, 'primary' => false, 'status' => 'online', 'delivery_rate' => '96.3%', 'response_time' => '198ms', 'median_dlr' => '2.0s', 'rate' => '0.0665'],
                        ]],
                    ['id' => 107, 'country' => 'Germany', 'iso' => 'DE', 'letter' => 'G', 'gateway_count' => 2, 'primary_gw' => 'Sinch Global', 'billing' => 'Per Message', 'rate' => '0.0750', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 65, 'primary' => true, 'status' => 'online', 'delivery_rate' => '98.5%', 'response_time' => '138ms', 'median_dlr' => '1.0s', 'rate' => '0.0750'],
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 35, 'primary' => false, 'status' => 'online', 'delivery_rate' => '97.9%', 'response_time' => '152ms', 'median_dlr' => '1.3s', 'rate' => '0.0770'],
                        ]],
                    ['id' => 108, 'country' => 'India', 'iso' => 'IN', 'letter' => 'I', 'gateway_count' => 2, 'primary_gw' => 'Telnyx Global', 'billing' => 'Per Segment', 'rate' => '0.0085', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'Telnyx Global', 'supplier' => 'Telnyx', 'code' => 'gw_telnyx_global', 'weight' => 60, 'primary' => true, 'status' => 'online', 'delivery_rate' => '93.2%', 'response_time' => '420ms', 'median_dlr' => '5.2s', 'rate' => '0.0085'],
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 40, 'primary' => false, 'status' => 'online', 'delivery_rate' => '92.8%', 'response_time' => '445ms', 'median_dlr' => '5.8s', 'rate' => '0.0092'],
                        ]],
                    ['id' => 109, 'country' => 'Japan', 'iso' => 'JP', 'letter' => 'J', 'gateway_count' => 1, 'primary_gw' => 'BICS International', 'billing' => 'Per Message', 'rate' => '0.0820', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 100, 'primary' => true, 'status' => 'online', 'delivery_rate' => '99.4%', 'response_time' => '195ms', 'median_dlr' => '1.5s', 'rate' => '0.0820'],
                        ]],
                    ['id' => 110, 'country' => 'Kenya', 'iso' => 'KE', 'letter' => 'K', 'gateway_count' => 1, 'primary_gw' => 'Sinch Global', 'billing' => 'Per Message', 'rate' => '0.0280', 'currency' => 'GBP', 'status' => 'blocked',
                        'gateways' => [
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 100, 'primary' => true, 'status' => 'offline', 'delivery_rate' => '88.5%', 'response_time' => '520ms', 'median_dlr' => '6.8s', 'rate' => '0.0280'],
                        ]],
                    ['id' => 111, 'country' => 'Nigeria', 'iso' => 'NG', 'letter' => 'N', 'gateway_count' => 2, 'primary_gw' => 'BICS International', 'billing' => 'Per Message', 'rate' => '0.0350', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 75, 'primary' => true, 'status' => 'online', 'delivery_rate' => '91.8%', 'response_time' => '380ms', 'median_dlr' => '4.2s', 'rate' => '0.0350'],
                            ['name' => 'Telnyx Global', 'supplier' => 'Telnyx', 'code' => 'gw_telnyx_global', 'weight' => 25, 'primary' => false, 'status' => 'online', 'delivery_rate' => '90.2%', 'response_time' => '410ms', 'median_dlr' => '4.9s', 'rate' => '0.0375'],
                        ]],
                    ['id' => 112, 'country' => 'South Africa', 'iso' => 'ZA', 'letter' => 'S', 'gateway_count' => 2, 'primary_gw' => 'Sinch Global', 'billing' => 'Per Message', 'rate' => '0.0220', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 55, 'primary' => true, 'status' => 'online', 'delivery_rate' => '95.6%', 'response_time' => '290ms', 'median_dlr' => '3.0s', 'rate' => '0.0220'],
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 45, 'primary' => false, 'status' => 'online', 'delivery_rate' => '94.8%', 'response_time' => '315ms', 'median_dlr' => '3.5s', 'rate' => '0.0240'],
                        ]],
                    ['id' => 113, 'country' => 'United States', 'iso' => 'US', 'letter' => 'U', 'gateway_count' => 3, 'primary_gw' => 'Telnyx Global', 'billing' => 'Per Segment', 'rate' => '0.0150', 'currency' => 'GBP', 'status' => 'active',
                        'gateways' => [
                            ['name' => 'Telnyx Global', 'supplier' => 'Telnyx', 'code' => 'gw_telnyx_global', 'weight' => 45, 'primary' => true, 'status' => 'online', 'delivery_rate' => '98.5%', 'response_time' => '118ms', 'median_dlr' => '0.6s', 'rate' => '0.0150'],
                            ['name' => 'BICS International', 'supplier' => 'BICS', 'code' => 'gw_bics_intl', 'weight' => 35, 'primary' => false, 'status' => 'online', 'delivery_rate' => '98.1%', 'response_time' => '132ms', 'median_dlr' => '0.9s', 'rate' => '0.0165'],
                            ['name' => 'Sinch Global', 'supplier' => 'Sinch', 'code' => 'gw_sinch_global', 'weight' => 20, 'primary' => false, 'status' => 'online', 'delivery_rate' => '97.8%', 'response_time' => '145ms', 'median_dlr' => '1.1s', 'rate' => '0.0175'],
                        ]],
                ];
                @endphp

                @foreach($intlRoutes as $route)
                <tr class="route-row" data-route-id="{{ $route['id'] }}" data-status="{{ $route['status'] }}" data-letter="{{ $route['letter'] }}" data-search="{{ strtolower($route['country'] . ' ' . $route['iso']) }}" onclick="toggleRoutePanel({{ $route['id'] }})">
                    <td><i class="fas fa-chevron-right expand-icon"></i></td>
                    <td><strong>{{ $route['country'] }}</strong> <span class="country-iso">{{ $route['iso'] }}</span></td>
                    <td><code style="font-size: 0.75rem;">{{ $route['iso'] }}</code></td>
                    <td><span class="gateway-count-badge">{{ $route['gateway_count'] }} {{ $route['gateway_count'] === 1 ? 'gateway' : 'gateways' }}</span></td>
                    <td style="font-size: 0.8rem;">{{ $route['primary_gw'] }}</td>
                    <td style="font-size: 0.75rem;">{{ $route['billing'] }}</td>
                    <td><span class="rate-snapshot">£{{ $route['rate'] }}</span></td>
                    <td>
                        <span class="status-badge {{ $route['status'] }}">
                            <i class="fas fa-circle" style="font-size: 5px;"></i>
                            {{ ucfirst($route['status']) }}
                        </span>
                    </td>
                </tr>
                <tr class="expand-panel" id="panel-{{ $route['id'] }}">
                    <td colspan="8" style="padding: 0;">
                        <div class="gateway-cards-container">
                            @foreach($route['gateways'] as $gw)
                            <div class="gateway-card {{ $gw['primary'] ? 'primary-gw' : '' }}">
                                <div class="gw-header">
                                    <div>
                                        <div class="gw-name">{{ $gw['name'] }}</div>
                                        <div class="gw-supplier">{{ $gw['supplier'] }} &middot; <code style="font-size: 0.65rem;">{{ $gw['code'] }}</code></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($gw['primary'])
                                        <span class="gw-primary-badge">Primary</span>
                                        @endif
                                        <span class="status-badge {{ $gw['status'] }}">
                                            <i class="fas fa-circle" style="font-size: 5px;"></i>
                                            {{ ucfirst($gw['status']) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="weight-display">
                                    <div class="weight-value">{{ $gw['weight'] }}%</div>
                                    <div class="weight-label">Traffic Weight</div>
                                </div>
                                <div class="telemetry-grid">
                                    <div class="telemetry-stat">
                                        <div class="stat-label">Delivery Rate</div>
                                        <div class="stat-value">{{ $gw['delivery_rate'] }}</div>
                                    </div>
                                    <div class="telemetry-stat">
                                        <div class="stat-label">Response Time</div>
                                        <div class="stat-value">{{ $gw['response_time'] }}</div>
                                    </div>
                                    <div class="telemetry-stat">
                                        <div class="stat-label">Median DLR</div>
                                        <div class="stat-value">{{ $gw['median_dlr'] }}</div>
                                    </div>
                                    <div class="telemetry-stat">
                                        <div class="stat-label">Rate</div>
                                        <div class="stat-value">£{{ $gw['rate'] }}</div>
                                    </div>
                                </div>
                                <div class="gw-actions">
                                    @if(!$gw['primary'])
                                    <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); setPrimaryGateway({{ $route['id'] }}, '{{ $gw['code'] }}')">
                                        <i class="fas fa-star me-1"></i>Set Primary
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-secondary btn-sm" onclick="event.stopPropagation(); openChangeWeightModal({{ $route['id'] }}, '{{ $gw['code'] }}', '{{ $gw['name'] }}', {{ $gw['weight'] }})">
                                        <i class="fas fa-balance-scale me-1"></i>Weight
                                    </button>
                                    <button class="btn btn-outline-{{ $gw['status'] === 'online' ? 'warning' : 'success' }} btn-sm" onclick="event.stopPropagation(); toggleGatewayBlock({{ $route['id'] }}, '{{ $gw['code'] }}')">
                                        <i class="fas fa-{{ $gw['status'] === 'online' ? 'ban' : 'check' }} me-1"></i>{{ $gw['status'] === 'online' ? 'Block' : 'Allow' }}
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="event.stopPropagation(); removeGateway({{ $route['id'] }}, '{{ $gw['code'] }}', '{{ $gw['name'] }}')">
                                        <i class="fas fa-times me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                            @endforeach

                            <div class="gateway-card" style="border-style: dashed; display: flex; align-items: center; justify-content: center; min-height: 200px; cursor: pointer;" onclick="event.stopPropagation(); openAddGatewayToRouteModal({{ $route['id'] }})">
                                <div class="text-center text-muted">
                                    <i class="fas fa-plus-circle fa-2x mb-2" style="opacity: 0.4;"></i>
                                    <div style="font-size: 0.8rem;">Add Gateway</div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Add Gateway to Route Modal --}}
<div class="modal fade" id="addGatewayRouteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Gateway to Route</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="addGwRouteId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Gateway <span class="text-danger">*</span></label>
                    <select class="form-select" id="addGwSelect">
                        <option value="">Choose a gateway...</option>
                        <option value="gw_bics_intl">BICS International (BICS)</option>
                        <option value="gw_sinch_global">Sinch Global (Sinch)</option>
                        <option value="gw_telnyx_global">Telnyx Global (Telnyx)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Initial Weight (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="addGwWeight" min="1" max="100" value="10">
                    <small class="text-muted">Weights will be rebalanced automatically.</small>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="addGwPrimary">
                    <label class="form-check-label" for="addGwPrimary">Set as Primary Gateway</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: var(--admin-primary); color: #fff;" onclick="confirmAddGateway()">
                    <i class="fas fa-plus me-1"></i>Add Gateway
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Change Weight Modal --}}
<div class="modal fade" id="changeWeightModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-balance-scale me-2"></i>Change Weight</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cwRouteId">
                <input type="hidden" id="cwGatewayCode">
                <p class="mb-2" style="font-size: 0.85rem;">Gateway: <strong id="cwGatewayName"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Weight (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="cwNewWeight" min="1" max="100">
                    <small class="text-muted">Current: <span id="cwCurrentWeight"></span>%</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: var(--admin-primary); color: #fff;" onclick="confirmChangeWeight()">
                    <i class="fas fa-check me-1"></i>Update Weight
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentLetter = 'A';

document.addEventListener('DOMContentLoaded', function() {
    filterByLetter('A', document.querySelector('.alpha-nav-btn.active'));
});

function switchProduct(product, btn) {
    document.querySelectorAll('.product-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    showToast('Switched to ' + btn.textContent.trim() + ' routes', 'info');
}

function filterByLetter(letter, btn) {
    currentLetter = letter;
    document.querySelectorAll('.alpha-nav-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterRoutes();
}

function toggleRoutePanel(routeId) {
    const row = document.querySelector(`tr.route-row[data-route-id="${routeId}"]`);
    const panel = document.getElementById('panel-' + routeId);

    if (row.classList.contains('expanded')) {
        row.classList.remove('expanded');
        panel.classList.remove('show');
    } else {
        document.querySelectorAll('.route-row.expanded').forEach(r => {
            r.classList.remove('expanded');
            document.getElementById('panel-' + r.dataset.routeId).classList.remove('show');
        });
        row.classList.add('expanded');
        panel.classList.add('show');
    }
}

function filterRoutes() {
    const search = document.getElementById('searchRoutes').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    let visible = 0;

    document.querySelectorAll('.route-row').forEach(row => {
        const matchSearch = !search || row.dataset.search.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        const matchLetter = currentLetter === 'ALL' || row.dataset.letter === currentLetter;
        const show = matchSearch && matchStatus && matchLetter;
        row.style.display = show ? '' : 'none';
        const panel = document.getElementById('panel-' + row.dataset.routeId);
        if (!show && panel) {
            panel.classList.remove('show');
            row.classList.remove('expanded');
        }
        if (show) visible++;
    });

    document.getElementById('routeCount').textContent = 'Showing ' + visible + ' route' + (visible !== 1 ? 's' : '');
}

function openAddGatewayToRouteModal(routeId) {
    document.getElementById('addGwRouteId').value = routeId || '';
    document.getElementById('addGwSelect').value = '';
    document.getElementById('addGwWeight').value = 10;
    document.getElementById('addGwPrimary').checked = false;
    new bootstrap.Modal(document.getElementById('addGatewayRouteModal')).show();
}

function confirmAddGateway() {
    const gw = document.getElementById('addGwSelect').value;
    const weight = document.getElementById('addGwWeight').value;
    if (!gw) { showToast('Please select a gateway', 'warning'); return; }
    if (!weight || weight < 1 || weight > 100) { showToast('Weight must be between 1 and 100', 'warning'); return; }
    bootstrap.Modal.getInstance(document.getElementById('addGatewayRouteModal')).hide();
    showToast('Gateway added successfully. Weights rebalanced.', 'success');
}

function openChangeWeightModal(routeId, gwCode, gwName, currentWeight) {
    document.getElementById('cwRouteId').value = routeId;
    document.getElementById('cwGatewayCode').value = gwCode;
    document.getElementById('cwGatewayName').textContent = gwName;
    document.getElementById('cwCurrentWeight').textContent = currentWeight;
    document.getElementById('cwNewWeight').value = currentWeight;
    new bootstrap.Modal(document.getElementById('changeWeightModal')).show();
}

function confirmChangeWeight() {
    const newWeight = document.getElementById('cwNewWeight').value;
    if (!newWeight || newWeight < 1 || newWeight > 100) { showToast('Weight must be between 1 and 100', 'warning'); return; }
    bootstrap.Modal.getInstance(document.getElementById('changeWeightModal')).hide();
    showToast('Weight updated successfully', 'success');
}

function setPrimaryGateway(routeId, gwCode) {
    if (confirm('Set this gateway as primary? The current primary will be demoted.')) {
        showToast('Primary gateway updated', 'success');
    }
}

function toggleGatewayBlock(routeId, gwCode) {
    if (confirm('Are you sure you want to change the status of this gateway?')) {
        showToast('Gateway status updated', 'success');
    }
}

function removeGateway(routeId, gwCode, gwName) {
    if (confirm('Remove "' + gwName + '" from this route? This cannot be undone.')) {
        showToast('Gateway removed from route', 'success');
    }
}

function showToast(message, type) {
    type = type || 'info';
    const colors = { success: '#198754', warning: '#ffc107', info: '#0dcaf0', danger: '#dc3545' };
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;padding:0.75rem 1.25rem;border-radius:8px;color:#fff;font-size:0.85rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);background:' + (colors[type] || colors.info);
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>
@endpush

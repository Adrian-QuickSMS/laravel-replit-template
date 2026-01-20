@extends('layouts.admin')

@section('title', 'Global Numbers Library')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }

.admin-filter-panel {
    background: #fff;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.admin-filter-panel .filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
}
.admin-filter-panel .filter-header h6 {
    margin: 0;
    font-weight: 600;
    color: var(--admin-primary);
    font-size: 0.9rem;
}
.admin-filter-panel .filter-header .toggle-icon {
    transition: transform 0.2s;
}
.admin-filter-panel .filter-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}
.admin-filter-panel .filter-body {
    padding: 1rem 1.25rem;
    background: #f8fafc;
}
.admin-filter-panel .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}
.admin-filter-panel .filter-group {
    display: flex;
    flex-direction: column;
    min-width: 160px;
    flex: 1;
    max-width: 200px;
}
.admin-filter-panel .filter-group.wide {
    min-width: 200px;
    max-width: 250px;
}
.admin-filter-panel .filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.375rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.admin-filter-panel .filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.multiselect-dropdown {
    position: relative;
}
.multiselect-dropdown .dropdown-toggle {
    background: #fff;
    border: 1px solid #ced4da;
    color: #495057;
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
    width: 100%;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.multiselect-dropdown .dropdown-toggle:hover {
    border-color: var(--admin-accent);
}
.multiselect-dropdown .dropdown-toggle::after {
    margin-left: 0.5rem;
}
.multiselect-dropdown .dropdown-menu {
    max-height: 250px;
    overflow-y: auto;
    min-width: 100%;
    padding: 0.5rem;
}
.multiselect-dropdown .dropdown-menu .search-box {
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.multiselect-dropdown .dropdown-menu .search-box input {
    font-size: 0.8rem;
    padding: 0.35rem 0.5rem;
}
.multiselect-dropdown .dropdown-menu .select-actions {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.multiselect-dropdown .dropdown-menu .select-actions a {
    font-size: 0.75rem;
    color: var(--admin-accent);
    text-decoration: none;
}
.multiselect-dropdown .dropdown-menu .select-actions a:hover {
    text-decoration: underline;
}
.multiselect-dropdown .form-check {
    padding: 0.25rem 0 0.25rem 1.75rem;
}
.multiselect-dropdown .form-check:hover {
    background: #f8f9fa;
    border-radius: 0.25rem;
}
.multiselect-dropdown .form-check-label {
    font-size: 0.8rem;
    cursor: pointer;
}
.multiselect-dropdown .selected-count {
    background: var(--admin-accent);
    color: #fff;
    font-size: 0.65rem;
    padding: 0.1rem 0.4rem;
    border-radius: 0.75rem;
    margin-left: 0.5rem;
}

.admin-btn-apply {
    background: var(--admin-primary);
    color: #fff;
    border: none;
    padding: 0.5rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 0.375rem;
}
.admin-btn-apply:hover {
    background: var(--admin-secondary);
    color: #fff;
}
.admin-btn-reset {
    background: transparent;
    color: #6c757d;
    border: 1px solid #dee2e6;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 0.375rem;
}
.admin-btn-reset:hover {
    background: #f8f9fa;
    color: #495057;
}

#numbersTable tbody tr td { padding: 0.5rem 0.75rem; vertical-align: middle; }
#numbersTable thead th { padding: 0.5rem 0.75rem; font-size: 0.8rem; font-weight: 600; color: #495057; background: #f8f9fa; }
#numbersTable tbody tr:hover { background: #f8f9fa; }

.number-value { 
    font-weight: 600; 
    color: var(--admin-primary); 
    white-space: nowrap;
}
.account-cell .account-name { 
    font-weight: 500; 
    color: #333; 
}
.account-cell .sub-account { 
    font-size: 0.75rem; 
    color: #6c757d; 
}

.sortable { cursor: pointer; user-select: none; position: relative; white-space: nowrap; }
.sortable:hover { background: #e9ecef; }
.sortable::after {
    content: '\f0dc';
    font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: 0.5rem;
    color: #adb5bd;
    font-size: 0.7rem;
}
.sortable.sort-asc::after { content: '\f0de'; color: var(--admin-primary); }
.sortable.sort-desc::after { content: '\f0dd'; color: var(--admin-primary); }

.badge-admin-active { background: rgba(5, 150, 105, 0.15); color: #059669; }
.badge-admin-suspended { background: rgba(220, 38, 38, 0.15); color: #dc2626; }
.badge-admin-pending { background: rgba(245, 158, 11, 0.15); color: #d97706; }
.badge-admin-portal { background: rgba(74, 144, 217, 0.15); color: var(--admin-accent); }
.badge-admin-api { background: rgba(30, 58, 95, 0.15); color: var(--admin-primary); }

.type-vmn { color: var(--admin-primary); font-weight: 500; }
.type-shortcode-keyword { color: #7c3aed; font-weight: 500; }
.type-dedicated { color: #059669; font-weight: 500; }

.capability-pill {
    display: inline-block;
    padding: 0.15rem 0.4rem;
    font-size: 0.65rem;
    font-weight: 500;
    border-radius: 0.75rem;
    margin-right: 0.2rem;
}
.capability-senderid { background: rgba(74, 144, 217, 0.15); color: var(--admin-accent); }
.capability-inbox { background: rgba(5, 150, 105, 0.15); color: #059669; }
.capability-optout { background: rgba(124, 58, 237, 0.15); color: #7c3aed; }
.capability-api { background: rgba(30, 58, 95, 0.15); color: var(--admin-primary); }

.cost-value { font-weight: 500; color: #333; }
.supplier-value { color: #6c757d; font-size: 0.85rem; }
.date-value { color: #6c757d; font-size: 0.85rem; }

.action-dots-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    color: #6c757d;
    cursor: pointer;
}
.action-dots-btn:hover { color: var(--admin-primary); }

.table-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
}
.table-footer .pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.table-footer .pagination {
    margin: 0;
}
.table-footer .page-link {
    padding: 0.35rem 0.65rem;
    font-size: 0.85rem;
    color: var(--admin-primary);
    border-color: #dee2e6;
}
.table-footer .page-item.active .page-link {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #fff;
}
.table-footer .page-link:hover {
    background: #f8f9fa;
    color: var(--admin-primary);
}

.filter-chips-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: rgba(74, 144, 217, 0.08);
    border-radius: 0.5rem;
    border: 1px solid rgba(74, 144, 217, 0.15);
}
.filter-chips-row .chips-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-right: 0.5rem;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background: #fff;
    border: 1px solid rgba(74, 144, 217, 0.3);
    color: var(--admin-primary);
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.filter-chip .chip-label {
    margin-right: 0.25rem;
    color: #6c757d;
}
.filter-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
    font-size: 0.7rem;
}
.filter-chip .remove-chip:hover { opacity: 1; color: #dc2626; }
.filter-chips-row .clear-all-link {
    margin-left: auto;
    font-size: 0.75rem;
    color: var(--admin-accent);
    text-decoration: none;
}
.filter-chips-row .clear-all-link:hover { text-decoration: underline; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}
.empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; }

.export-btn {
    background: transparent;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
    border-radius: 0.375rem;
}
.export-btn:hover {
    background: #f8f9fa;
    color: #495057;
}
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Messaging Assets</a>
        <span class="separator">/</span>
        <span>Numbers</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: var(--admin-primary); font-weight: 600;">Global Numbers Library</h4>
            <p class="text-muted mb-0">All numbers and keywords across the platform</p>
        </div>
        <div>
            <button class="export-btn" onclick="exportNumbers()">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="admin-filter-panel">
        <div class="filter-header" onclick="toggleFilterPanel()">
            <h6><i class="fas fa-filter me-2"></i>Filters</h6>
            <i class="fas fa-chevron-down toggle-icon"></i>
        </div>
        <div class="filter-body" id="filterBody">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Country</label>
                    <div class="dropdown multiselect-dropdown" id="countryDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Countries</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('countryDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('countryDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="UK" id="country_UK"><label class="form-check-label" for="country_UK">United Kingdom</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="US" id="country_US"><label class="form-check-label" for="country_US">United States</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="DE" id="country_DE"><label class="form-check-label" for="country_DE">Germany</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="FR" id="country_FR"><label class="form-check-label" for="country_FR">France</label></div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Number Type</label>
                    <div class="dropdown multiselect-dropdown" id="typeDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Types</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('typeDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('typeDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="vmn" id="type_vmn"><label class="form-check-label" for="type_vmn">VMN</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="shortcode_keyword" id="type_shortcode"><label class="form-check-label" for="type_shortcode">Shared Shortcode Keyword</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="dedicated" id="type_dedicated"><label class="form-check-label" for="type_dedicated">Dedicated Shortcode</label></div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <div class="dropdown multiselect-dropdown" id="statusDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Statuses</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('statusDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('statusDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="active" id="status_active"><label class="form-check-label" for="status_active">Active</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="suspended" id="status_suspended"><label class="form-check-label" for="status_suspended">Suspended</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="pending" id="status_pending"><label class="form-check-label" for="status_pending">Pending</label></div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Mode</label>
                    <div class="dropdown multiselect-dropdown" id="modeDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Modes</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('modeDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('modeDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="portal" id="mode_portal"><label class="form-check-label" for="mode_portal">Portal</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="api" id="mode_api"><label class="form-check-label" for="mode_api">API</label></div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Capability</label>
                    <div class="dropdown multiselect-dropdown" id="capabilityDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Capabilities</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('capabilityDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('capabilityDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="senderid" id="cap_senderid"><label class="form-check-label" for="cap_senderid">SenderID</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="inbox" id="cap_inbox"><label class="form-check-label" for="cap_inbox">Inbox</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="optout" id="cap_optout"><label class="form-check-label" for="cap_optout">Opt-out</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="api" id="cap_api"><label class="form-check-label" for="cap_api">API</label></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group wide">
                    <label>Customer Account</label>
                    <div class="dropdown multiselect-dropdown" id="accountDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Accounts</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="search-box">
                                <input type="text" class="form-control form-control-sm" placeholder="Search accounts..." onkeyup="filterDropdownOptions('accountDropdown', this.value)">
                            </div>
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('accountDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('accountDropdown'); return false;">Clear</a>
                            </div>
                            <div class="dropdown-options">
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Acme Corporation" id="acc_acme"><label class="form-check-label" for="acc_acme">Acme Corporation</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Finance Ltd" id="acc_finance"><label class="form-check-label" for="acc_finance">Finance Ltd</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="TechStart Inc" id="acc_techstart"><label class="form-check-label" for="acc_techstart">TechStart Inc</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Big Enterprise" id="acc_big"><label class="form-check-label" for="acc_big">Big Enterprise</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="NewClient" id="acc_new"><label class="form-check-label" for="acc_new">NewClient</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Retail Corp" id="acc_retail"><label class="form-check-label" for="acc_retail">Retail Corp</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Healthcare Plus" id="acc_health"><label class="form-check-label" for="acc_health">Healthcare Plus</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="US Branch Corp" id="acc_usbranch"><label class="form-check-label" for="acc_usbranch">US Branch Corp</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Support Services" id="acc_support"><label class="form-check-label" for="acc_support">Support Services</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Logistics Ltd" id="acc_logistics"><label class="form-check-label" for="acc_logistics">Logistics Ltd</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Old Account" id="acc_old"><label class="form-check-label" for="acc_old">Old Account</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Media Group" id="acc_media"><label class="form-check-label" for="acc_media">Media Group</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Banking Secure" id="acc_banking"><label class="form-check-label" for="acc_banking">Banking Secure</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Euro Expansion" id="acc_euro"><label class="form-check-label" for="acc_euro">Euro Expansion</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Tech Solutions" id="acc_techsol"><label class="form-check-label" for="acc_techsol">Tech Solutions</label></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="filter-group wide">
                    <label>Sub-Account</label>
                    <div class="dropdown multiselect-dropdown" id="subAccountDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Sub-Accounts</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="search-box">
                                <input type="text" class="form-control form-control-sm" placeholder="Search sub-accounts..." onkeyup="filterDropdownOptions('subAccountDropdown', this.value)">
                            </div>
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('subAccountDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('subAccountDropdown'); return false;">Clear</a>
                            </div>
                            <div class="dropdown-options" id="subAccountOptions">
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Marketing" id="sub_marketing"><label class="form-check-label" for="sub_marketing">Marketing</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Sales" id="sub_sales"><label class="form-check-label" for="sub_sales">Sales</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Retail" id="sub_retail"><label class="form-check-label" for="sub_retail">Retail</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Main" id="sub_main"><label class="form-check-label" for="sub_main">Main</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Operations" id="sub_ops"><label class="form-check-label" for="sub_ops">Operations</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Notifications" id="sub_notif"><label class="form-check-label" for="sub_notif">Notifications</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Support" id="sub_support"><label class="form-check-label" for="sub_support">Support</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Customer Care" id="sub_care"><label class="form-check-label" for="sub_care">Customer Care</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Dispatch" id="sub_dispatch"><label class="form-check-label" for="sub_dispatch">Dispatch</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Legacy" id="sub_legacy"><label class="form-check-label" for="sub_legacy">Legacy</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="News" id="sub_news"><label class="form-check-label" for="sub_news">News</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Alerts" id="sub_alerts"><label class="form-check-label" for="sub_alerts">Alerts</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Germany" id="sub_germany"><label class="form-check-label" for="sub_germany">Germany</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="France" id="sub_france"><label class="form-check-label" for="sub_france">France</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Promotions" id="sub_promo"><label class="form-check-label" for="sub_promo">Promotions</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="API Team" id="sub_api"><label class="form-check-label" for="sub_api">API Team</label></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Supplier</label>
                    <div class="dropdown multiselect-dropdown" id="supplierDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Suppliers</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('supplierDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('supplierDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Sinch" id="sup_sinch"><label class="form-check-label" for="sup_sinch">Sinch</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Twilio" id="sup_twilio"><label class="form-check-label" for="sup_twilio">Twilio</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Vonage" id="sup_vonage"><label class="form-check-label" for="sup_vonage">Vonage</label></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn admin-btn-reset" onclick="resetFilters()"><i class="fas fa-undo me-1"></i> Reset</button>
                <button class="btn admin-btn-apply" onclick="applyFilters()"><i class="fas fa-check me-1"></i> Apply Filters</button>
            </div>
        </div>
    </div>

    <div class="filter-chips-row" id="activeFiltersRow" style="display: none;">
        <span class="chips-label">Active filters:</span>
        <div id="filterChipsContainer"></div>
        <a href="#" class="clear-all-link" onclick="resetFilters(); return false;">Clear all</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="numbersTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="number">Number / Keyword</th>
                            <th class="sortable" data-sort="country">Country</th>
                            <th class="sortable" data-sort="type">Number Type</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th class="sortable" data-sort="mode">Mode</th>
                            <th class="sortable" data-sort="account">Customer Account</th>
                            <th class="sortable text-end" data-sort="cost">Monthly Cost</th>
                            <th class="sortable" data-sort="supplier">Supplier</th>
                            <th class="sortable" data-sort="created">Created Date</th>
                            <th class="text-center" style="width: 50px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="numbersTableBody">
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <div class="pagination-info">
                    Showing <span id="showingStart">1</span>-<span id="showingEnd">20</span> of <span id="totalCount">156</span> numbers
                </div>
                <nav>
                    <ul class="pagination pagination-sm" id="tablePagination">
                        <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="numberDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-phone-alt me-2"></i>Number Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="numberDetailsContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="confirmModalHeader">
                <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmModalBtn" onclick="executeConfirmedAction()">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>Reassign Number</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Reassigning a number will transfer ownership and billing to the new account.
                </div>
                <div id="reassignCurrentInfo" class="mb-3"></div>
                <div class="mb-3">
                    <label class="form-label fw-bold">New Customer Account</label>
                    <select class="form-select" id="reassignAccount">
                        <option value="">Select account...</option>
                        <option value="Acme Corporation">Acme Corporation</option>
                        <option value="Finance Ltd">Finance Ltd</option>
                        <option value="TechStart Inc">TechStart Inc</option>
                        <option value="Big Enterprise">Big Enterprise</option>
                        <option value="Retail Corp">Retail Corp</option>
                        <option value="Healthcare Plus">Healthcare Plus</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">New Sub-Account</label>
                    <select class="form-select" id="reassignSubAccount">
                        <option value="">Select sub-account...</option>
                        <option value="Main">Main</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Sales">Sales</option>
                        <option value="Support">Support</option>
                        <option value="Operations">Operations</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason for Reassignment</label>
                    <textarea class="form-control" id="reassignReason" rows="2" placeholder="Enter reason (required for audit)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeReassign()">Reassign Number</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCapabilitiesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-cogs me-2"></i>Edit Capabilities</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="capabilitiesNumberInfo" class="mb-3"></div>
                <div id="capabilitiesRulesAlert" class="alert alert-warning mb-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="capabilitiesRulesText"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Available Capabilities</label>
                    <div id="capabilityToggles"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCapabilities()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="optoutRoutingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-route me-2"></i>Edit Opt-out Routing</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="optoutKeywordInfo" class="mb-3"></div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Opt-out Keywords</label>
                    <input type="text" class="form-control" id="optoutKeywords" value="STOP, UNSUBSCRIBE, QUIT, END">
                    <small class="text-muted">Comma-separated list of keywords that trigger opt-out</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Auto-Reply Message</label>
                    <textarea class="form-control" id="optoutReply" rows="2">You have been unsubscribed. Reply START to resubscribe.</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Forward Opt-outs To</label>
                    <select class="form-select" id="optoutForward">
                        <option value="none">Do not forward</option>
                        <option value="email">Email notification</option>
                        <option value="webhook">Webhook URL</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveOptoutRouting()">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Admin Numbers] Initializing Global Numbers Library');
    
    initializeSorting();
    initializeMultiSelectDropdowns();
    loadNumbersData();
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBERS_LIBRARY_VIEWED', {
            module: 'numbers',
            action: 'view_list'
        }, 'LOW');
    }
});

const mockNumbersData = [
    { id: 'NUM-001', number: '+447700900123', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Acme Corporation', subAccount: 'Marketing', capabilities: ['senderid', 'inbox', 'optout'], cost: 2.00, supplier: 'Sinch', created: '2025-10-15' },
    { id: 'NUM-002', number: '+447700900456', country: 'UK', type: 'vmn', status: 'active', mode: 'api', account: 'Finance Ltd', subAccount: 'Retail', capabilities: ['api'], cost: 2.00, supplier: 'Sinch', created: '2025-09-20' },
    { id: 'NUM-003', number: 'PROMO', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Acme Corporation', subAccount: 'Sales', capabilities: ['optout'], cost: 2.00, supplier: 'Sinch', created: '2025-11-01' },
    { id: 'NUM-004', number: '+447700900789', country: 'UK', type: 'vmn', status: 'suspended', mode: 'portal', account: 'TechStart Inc', subAccount: 'Main', capabilities: ['senderid', 'inbox'], cost: 2.00, supplier: 'Twilio', created: '2025-08-10' },
    { id: 'NUM-005', number: '82228', country: 'UK', type: 'dedicated', status: 'active', mode: 'portal', account: 'Big Enterprise', subAccount: 'Operations', capabilities: ['senderid', 'inbox', 'optout'], cost: 500.00, supplier: 'Vonage', created: '2024-06-15' },
    { id: 'NUM-006', number: '+447700900111', country: 'UK', type: 'vmn', status: 'pending', mode: 'api', account: 'NewClient', subAccount: 'Main', capabilities: ['api'], cost: 2.00, supplier: 'Sinch', created: '2026-01-18' },
    { id: 'NUM-007', number: 'SALE', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Retail Corp', subAccount: 'Marketing', capabilities: ['optout'], cost: 2.00, supplier: 'Sinch', created: '2025-12-05' },
    { id: 'NUM-008', number: '+447700900222', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Healthcare Plus', subAccount: 'Notifications', capabilities: ['senderid', 'inbox', 'optout'], cost: 2.00, supplier: 'Twilio', created: '2025-07-22' },
    { id: 'NUM-009', number: '+14155551234', country: 'US', type: 'vmn', status: 'active', mode: 'api', account: 'US Branch Corp', subAccount: 'Sales', capabilities: ['api'], cost: 3.50, supplier: 'Twilio', created: '2025-11-10' },
    { id: 'NUM-010', number: 'HELP', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Support Services', subAccount: 'Customer Care', capabilities: ['inbox', 'optout'], cost: 2.00, supplier: 'Sinch', created: '2025-10-01' },
    { id: 'NUM-011', number: '+447700900333', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Logistics Ltd', subAccount: 'Dispatch', capabilities: ['senderid', 'inbox'], cost: 2.00, supplier: 'Vonage', created: '2025-09-15' },
    { id: 'NUM-012', number: '+447700900444', country: 'UK', type: 'vmn', status: 'suspended', mode: 'api', account: 'Old Account', subAccount: 'Legacy', capabilities: ['api'], cost: 2.00, supplier: 'Sinch', created: '2024-03-20' },
    { id: 'NUM-013', number: 'INFO', country: 'UK', type: 'shortcode_keyword', status: 'pending', mode: 'portal', account: 'Media Group', subAccount: 'News', capabilities: ['optout'], cost: 2.00, supplier: 'Sinch', created: '2026-01-15' },
    { id: 'NUM-014', number: '+447700900555', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Banking Secure', subAccount: 'Alerts', capabilities: ['senderid', 'inbox', 'optout'], cost: 2.00, supplier: 'Twilio', created: '2025-08-30' },
    { id: 'NUM-015', number: '+49170123456', country: 'DE', type: 'vmn', status: 'active', mode: 'api', account: 'Euro Expansion', subAccount: 'Germany', capabilities: ['api'], cost: 4.00, supplier: 'Vonage', created: '2025-11-20' },
    { id: 'NUM-016', number: '+447700900666', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Acme Corporation', subAccount: 'Support', capabilities: ['senderid', 'inbox'], cost: 2.00, supplier: 'Sinch', created: '2025-10-25' },
    { id: 'NUM-017', number: 'DEAL', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Retail Corp', subAccount: 'Promotions', capabilities: ['optout'], cost: 2.00, supplier: 'Sinch', created: '2025-12-10' },
    { id: 'NUM-018', number: '+447700900777', country: 'UK', type: 'vmn', status: 'active', mode: 'api', account: 'Tech Solutions', subAccount: 'API Team', capabilities: ['api'], cost: 2.00, supplier: 'Twilio', created: '2025-09-05' },
    { id: 'NUM-019', number: '+33612345678', country: 'FR', type: 'vmn', status: 'pending', mode: 'portal', account: 'Euro Expansion', subAccount: 'France', capabilities: ['senderid'], cost: 3.50, supplier: 'Vonage', created: '2026-01-10' },
    { id: 'NUM-020', number: '+447700900888', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Finance Ltd', subAccount: 'Alerts', capabilities: ['senderid', 'inbox', 'optout'], cost: 2.00, supplier: 'Sinch', created: '2025-07-15' }
];

let currentPage = 1;
const rowsPerPage = 20;
let filteredData = [...mockNumbersData];
let sortColumn = 'created';
let sortDirection = 'desc';
let appliedFilters = {};

function toggleFilterPanel() {
    const body = document.getElementById('filterBody');
    const header = document.querySelector('.filter-header');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        header.classList.remove('collapsed');
    } else {
        body.style.display = 'none';
        header.classList.add('collapsed');
    }
}

function initializeMultiSelectDropdowns() {
    document.querySelectorAll('.multiselect-dropdown').forEach(dropdown => {
        dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', () => updateDropdownLabel(dropdown.id));
        });
    });
}

function updateDropdownLabel(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
    const label = dropdown.querySelector('.dropdown-label');
    const allLabels = {
        'countryDropdown': 'All Countries',
        'typeDropdown': 'All Types',
        'statusDropdown': 'All Statuses',
        'modeDropdown': 'All Modes',
        'capabilityDropdown': 'All Capabilities',
        'accountDropdown': 'All Accounts',
        'subAccountDropdown': 'All Sub-Accounts',
        'supplierDropdown': 'All Suppliers'
    };
    
    if (checked.length === 0) {
        label.innerHTML = allLabels[dropdownId] || 'All';
    } else if (checked.length === 1) {
        label.innerHTML = checked[0].nextElementSibling.textContent;
    } else {
        label.innerHTML = `${checked.length} selected <span class="selected-count">${checked.length}</span>`;
    }
}

function selectAll(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.querySelectorAll('.dropdown-options input[type="checkbox"], .dropdown-menu > .form-check input[type="checkbox"]').forEach(cb => {
        if (cb.closest('.form-check').style.display !== 'none') {
            cb.checked = true;
        }
    });
    updateDropdownLabel(dropdownId);
}

function clearAll(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    updateDropdownLabel(dropdownId);
}

function filterDropdownOptions(dropdownId, searchTerm) {
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.dropdown-options .form-check, .dropdown-menu > .form-check');
    const term = searchTerm.toLowerCase();
    
    options.forEach(option => {
        const label = option.querySelector('label').textContent.toLowerCase();
        option.style.display = label.includes(term) ? '' : 'none';
    });
}

function getSelectedValues(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
    return Array.from(checked).map(cb => cb.value);
}

function loadNumbersData() {
    renderTable(filteredData);
    updatePaginationInfo();
}

function renderTable(data) {
    const tbody = document.getElementById('numbersTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="empty-state">
                    <i class="fas fa-phone-slash d-block"></i>
                    <p>No numbers found matching your criteria</p>
                </td>
            </tr>
        `;
        return;
    }
    
    const start = (currentPage - 1) * rowsPerPage;
    const end = Math.min(start + rowsPerPage, data.length);
    const pageData = data.slice(start, end);
    
    tbody.innerHTML = pageData.map(num => `
        <tr data-id="${num.id}">
            <td><span class="number-value">${num.number}</span></td>
            <td>${getCountryFlag(num.country)} ${num.country}</td>
            <td>${getTypeLabel(num.type)}</td>
            <td>${getStatusBadge(num.status)}</td>
            <td>${getModeBadge(num.mode)}</td>
            <td class="account-cell">
                <div class="account-name">${num.account}</div>
                <div class="sub-account">${num.subAccount}</div>
            </td>
            <td class="text-end"><span class="cost-value">£${num.cost.toFixed(2)}</span></td>
            <td><span class="supplier-value">${num.supplier}</span></td>
            <td><span class="date-value">${formatDate(num.created)}</span></td>
            <td class="text-center">
                <div class="dropdown">
                    <button class="action-dots-btn" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    ${buildContextMenu(num)}
                </div>
            </td>
        </tr>
    `).join('');
}

function buildContextMenu(num) {
    let menuItems = [];
    
    menuItems.push(`<li><a class="dropdown-item" href="#" onclick="viewNumberDetails('${num.id}'); return false;"><i class="fas fa-cog me-2 text-muted"></i>View Configuration</a></li>`);
    menuItems.push(`<li><a class="dropdown-item" href="#" onclick="viewAuditTrail('${num.id}'); return false;"><i class="fas fa-history me-2 text-muted"></i>View Audit History</a></li>`);
    menuItems.push('<li><hr class="dropdown-divider"></li>');
    
    if (num.status === 'active') {
        menuItems.push(`<li><a class="dropdown-item text-warning" href="#" onclick="confirmSuspend('${num.id}'); return false;"><i class="fas fa-pause-circle me-2"></i>Suspend Number</a></li>`);
    } else if (num.status === 'suspended') {
        menuItems.push(`<li><a class="dropdown-item text-success" href="#" onclick="confirmReactivate('${num.id}'); return false;"><i class="fas fa-play-circle me-2"></i>Reactivate Number</a></li>`);
    }
    
    menuItems.push(`<li><a class="dropdown-item" href="#" onclick="openReassignModal('${num.id}'); return false;"><i class="fas fa-exchange-alt me-2 text-muted"></i>Reassign Customer / Sub-Account</a></li>`);
    
    if (num.type === 'vmn' || num.type === 'dedicated') {
        menuItems.push('<li><hr class="dropdown-divider"></li>');
        
        const targetMode = num.mode === 'portal' ? 'API' : 'Portal';
        menuItems.push(`<li><a class="dropdown-item" href="#" onclick="confirmChangeMode('${num.id}', '${targetMode}'); return false;"><i class="fas fa-sync-alt me-2 text-muted"></i>Change Mode to ${targetMode}</a></li>`);
        
        menuItems.push(`<li><a class="dropdown-item" href="#" onclick="openEditCapabilities('${num.id}'); return false;"><i class="fas fa-cogs me-2 text-muted"></i>Edit Capabilities</a></li>`);
        
        if (num.mode === 'portal') {
            menuItems.push(`<li><a class="dropdown-item" href="#" onclick="openSubAccountAssign('${num.id}'); return false;"><i class="fas fa-sitemap me-2 text-muted"></i>Assign / Remove Sub-Accounts</a></li>`);
            menuItems.push(`<li><a class="dropdown-item" href="#" onclick="openOverrideUsage('${num.id}'); return false;"><i class="fas fa-sliders-h me-2 text-muted"></i>Override Default Usage</a></li>`);
        }
    }
    
    if (num.type === 'shortcode_keyword') {
        menuItems.push('<li><hr class="dropdown-divider"></li>');
        menuItems.push(`<li><a class="dropdown-item" href="#" onclick="openReassignSubAccountOnly('${num.id}'); return false;"><i class="fas fa-sitemap me-2 text-muted"></i>Reassign Sub-Account</a></li>`);
        menuItems.push(`<li><a class="dropdown-item" href="#" onclick="openOptoutRouting('${num.id}'); return false;"><i class="fas fa-route me-2 text-muted"></i>Edit Opt-out Routing</a></li>`);
        
        if (num.status === 'active') {
            menuItems.push(`<li><a class="dropdown-item text-danger" href="#" onclick="confirmDisableKeyword('${num.id}'); return false;"><i class="fas fa-ban me-2"></i>Disable Keyword</a></li>`);
        }
    }
    
    return `<ul class="dropdown-menu dropdown-menu-end">${menuItems.join('')}</ul>`;
}

function getCountryFlag(country) {
    const flags = { 'UK': '🇬🇧', 'US': '🇺🇸', 'DE': '🇩🇪', 'FR': '🇫🇷' };
    return flags[country] || '🌍';
}

function getTypeLabel(type) {
    const types = {
        'vmn': '<span class="type-vmn">VMN</span>',
        'shortcode_keyword': '<span class="type-shortcode-keyword">Shared Shortcode Keyword</span>',
        'dedicated': '<span class="type-dedicated">Dedicated Shortcode</span>'
    };
    return types[type] || type;
}

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-admin-active">Active</span>',
        'suspended': '<span class="badge badge-admin-suspended">Suspended</span>',
        'pending': '<span class="badge badge-admin-pending">Pending</span>'
    };
    return badges[status] || status;
}

function getModeBadge(mode) {
    const badges = {
        'portal': '<span class="badge badge-admin-portal">Portal</span>',
        'api': '<span class="badge badge-admin-api">API</span>'
    };
    return badges[mode] || mode;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function applyFilters() {
    const countries = getSelectedValues('countryDropdown');
    const types = getSelectedValues('typeDropdown');
    const statuses = getSelectedValues('statusDropdown');
    const modes = getSelectedValues('modeDropdown');
    const capabilities = getSelectedValues('capabilityDropdown');
    const accounts = getSelectedValues('accountDropdown');
    const subAccounts = getSelectedValues('subAccountDropdown');
    const suppliers = getSelectedValues('supplierDropdown');
    
    appliedFilters = { countries, types, statuses, modes, capabilities, accounts, subAccounts, suppliers };
    
    filteredData = mockNumbersData.filter(num => {
        if (countries.length > 0 && !countries.includes(num.country)) return false;
        if (types.length > 0 && !types.includes(num.type)) return false;
        if (statuses.length > 0 && !statuses.includes(num.status)) return false;
        if (modes.length > 0 && !modes.includes(num.mode)) return false;
        if (capabilities.length > 0 && !capabilities.some(cap => num.capabilities.includes(cap))) return false;
        if (accounts.length > 0 && !accounts.includes(num.account)) return false;
        if (subAccounts.length > 0 && !subAccounts.includes(num.subAccount)) return false;
        if (suppliers.length > 0 && !suppliers.includes(num.supplier)) return false;
        return true;
    });
    
    currentPage = 1;
    renderTable(filteredData);
    updatePaginationInfo();
    updateFilterChips();
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBERS_FILTERED', {
            module: 'numbers',
            filters: appliedFilters,
            resultCount: filteredData.length
        }, 'LOW');
    }
}

function resetFilters() {
    ['countryDropdown', 'typeDropdown', 'statusDropdown', 'modeDropdown', 'capabilityDropdown', 'accountDropdown', 'subAccountDropdown', 'supplierDropdown'].forEach(id => {
        clearAll(id);
    });
    
    appliedFilters = {};
    filteredData = [...mockNumbersData];
    currentPage = 1;
    renderTable(filteredData);
    updatePaginationInfo();
    updateFilterChips();
}

function updateFilterChips() {
    const container = document.getElementById('filterChipsContainer');
    const row = document.getElementById('activeFiltersRow');
    const chips = [];
    
    const filterLabels = {
        countries: 'Country',
        types: 'Type',
        statuses: 'Status',
        modes: 'Mode',
        capabilities: 'Capability',
        accounts: 'Account',
        subAccounts: 'Sub-Account',
        suppliers: 'Supplier'
    };
    
    Object.entries(appliedFilters).forEach(([key, values]) => {
        if (values && values.length > 0) {
            values.forEach(val => {
                chips.push({ filterKey: key, value: val, label: filterLabels[key] });
            });
        }
    });
    
    if (chips.length === 0) {
        row.style.display = 'none';
        return;
    }
    
    row.style.display = 'flex';
    container.innerHTML = chips.map(chip => `
        <span class="filter-chip">
            <span class="chip-label">${chip.label}:</span> ${chip.value}
            <i class="fas fa-times remove-chip" onclick="removeFilterChip('${chip.filterKey}', '${chip.value}')"></i>
        </span>
    `).join('');
}

function removeFilterChip(filterKey, value) {
    const dropdownMap = {
        countries: 'countryDropdown',
        types: 'typeDropdown',
        statuses: 'statusDropdown',
        modes: 'modeDropdown',
        capabilities: 'capabilityDropdown',
        accounts: 'accountDropdown',
        subAccounts: 'subAccountDropdown',
        suppliers: 'supplierDropdown'
    };
    
    const dropdown = document.getElementById(dropdownMap[filterKey]);
    const checkbox = dropdown.querySelector(`input[value="${value}"]`);
    if (checkbox) {
        checkbox.checked = false;
        updateDropdownLabel(dropdownMap[filterKey]);
    }
    
    applyFilters();
}

function updatePaginationInfo() {
    const total = filteredData.length;
    const start = total === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
    const end = Math.min(currentPage * rowsPerPage, total);
    
    document.getElementById('showingStart').textContent = start;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalCount').textContent = total;
}

function initializeSorting() {
    document.querySelectorAll('#numbersTable th.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            
            document.querySelectorAll('#numbersTable th.sortable').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }
            
            this.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
            
            sortTable();
        });
    });
}

function sortTable() {
    filteredData.sort((a, b) => {
        let aVal, bVal;
        
        switch (sortColumn) {
            case 'number': aVal = a.number; bVal = b.number; break;
            case 'country': aVal = a.country; bVal = b.country; break;
            case 'type': aVal = a.type; bVal = b.type; break;
            case 'status': aVal = a.status; bVal = b.status; break;
            case 'mode': aVal = a.mode; bVal = b.mode; break;
            case 'account': aVal = a.account; bVal = b.account; break;
            case 'cost': aVal = a.cost; bVal = b.cost; break;
            case 'supplier': aVal = a.supplier; bVal = b.supplier; break;
            case 'created': aVal = new Date(a.created); bVal = new Date(b.created); break;
            default: aVal = a.number; bVal = b.number;
        }
        
        if (typeof aVal === 'string') {
            return sortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        }
        return sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
    });
    
    renderTable(filteredData);
}

function viewNumberDetails(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    const capBadges = (num.capabilities || []).map(cap => {
        const classes = {
            'senderid': 'capability-senderid',
            'inbox': 'capability-inbox',
            'optout': 'capability-optout',
            'api': 'capability-api'
        };
        const labels = { 'senderid': 'SenderID', 'inbox': 'Inbox', 'optout': 'Opt-out', 'api': 'API' };
        return `<span class="capability-pill ${classes[cap]}">${labels[cap]}</span>`;
    }).join('');
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Number Information</h6>
                <table class="table table-sm">
                    <tr><td class="text-muted">Number/Keyword</td><td class="fw-bold">${num.number}</td></tr>
                    <tr><td class="text-muted">Country</td><td>${getCountryFlag(num.country)} ${num.country}</td></tr>
                    <tr><td class="text-muted">Type</td><td>${getTypeLabel(num.type)}</td></tr>
                    <tr><td class="text-muted">Status</td><td>${getStatusBadge(num.status)}</td></tr>
                    <tr><td class="text-muted">Mode</td><td>${getModeBadge(num.mode)}</td></tr>
                    <tr><td class="text-muted">Capabilities</td><td>${capBadges}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Assignment & Billing</h6>
                <table class="table table-sm">
                    <tr><td class="text-muted">Customer Account</td><td class="fw-bold">${num.account}</td></tr>
                    <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
                    <tr><td class="text-muted">Monthly Cost</td><td>£${num.cost.toFixed(2)}</td></tr>
                    <tr><td class="text-muted">Supplier</td><td>${num.supplier}</td></tr>
                    <tr><td class="text-muted">Created</td><td>${formatDate(num.created)}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    document.getElementById('numberDetailsContent').innerHTML = content;
    document.querySelector('#numberDetailsModal .modal-title').innerHTML = '<i class="fas fa-phone-alt me-2"></i>Number Details';
    new bootstrap.Modal(document.getElementById('numberDetailsModal')).show();
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBER_DETAILS_VIEWED', {
            module: 'numbers',
            numberId: numberId,
            number: num.number,
            account: num.account
        }, 'LOW');
    }
}

function viewAuditTrail(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    const content = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Audit trail for <strong>${num.number}</strong> (${num.account})
        </div>
        <div class="list-group">
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Number Provisioned</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Number added to platform via supplier ${num.supplier}</small>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Assigned to Account</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Assigned to ${num.account} / ${num.subAccount}</small>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Mode Set to ${num.mode.charAt(0).toUpperCase() + num.mode.slice(1)}</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Operating mode configured</small>
            </div>
        </div>
    `;
    
    document.getElementById('numberDetailsContent').innerHTML = content;
    document.querySelector('#numberDetailsModal .modal-title').innerHTML = '<i class="fas fa-history me-2"></i>Audit Trail';
    new bootstrap.Modal(document.getElementById('numberDetailsModal')).show();
}

function exportNumbers() {
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBERS_EXPORTED', {
            module: 'numbers',
            recordCount: filteredData.length,
            format: 'CSV'
        }, 'MEDIUM');
    }
    
    alert('Export functionality: ' + filteredData.length + ' records would be exported to CSV.');
}

let pendingAction = null;

function confirmSuspend(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    pendingAction = { type: 'suspend', numberId, num };
    
    document.getElementById('confirmModalHeader').style.background = '#d97706';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Suspend Number';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> Suspending this number will immediately stop all messaging.
        </div>
        <p>Are you sure you want to suspend <strong>${num.number}</strong>?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
            <tr><td class="text-muted">Type</td><td>${getTypeLabel(num.type)}</td></tr>
        </table>
        <div class="mb-3">
            <label class="form-label fw-bold">Reason for Suspension</label>
            <textarea class="form-control" id="actionReason" rows="2" placeholder="Enter reason (required)..."></textarea>
        </div>
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-warning';
    document.getElementById('confirmModalBtn').textContent = 'Suspend Number';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function confirmReactivate(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    pendingAction = { type: 'reactivate', numberId, num };
    
    document.getElementById('confirmModalHeader').style.background = '#059669';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Reactivate Number';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            Reactivating this number will restore messaging capabilities.
        </div>
        <p>Are you sure you want to reactivate <strong>${num.number}</strong>?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
        </table>
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-success';
    document.getElementById('confirmModalBtn').textContent = 'Reactivate Number';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function confirmChangeMode(numberId, targetMode) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    pendingAction = { type: 'changeMode', numberId, num, targetMode };
    
    const isBillingImpact = true;
    
    document.getElementById('confirmModalHeader').style.background = 'var(--admin-primary)';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Change Operating Mode';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Changing mode will affect how this number can be used.
        </div>
        <p>Change <strong>${num.number}</strong> from <strong>${num.mode.charAt(0).toUpperCase() + num.mode.slice(1)}</strong> to <strong>${targetMode}</strong> mode?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Current Mode</td><td>${getModeBadge(num.mode)}</td></tr>
            <tr><td class="text-muted">New Mode</td><td><span class="badge badge-admin-${targetMode.toLowerCase()}">${targetMode}</span></td></tr>
        </table>
        ${isBillingImpact ? '<div class="alert alert-warning mt-3"><i class="fas fa-pound-sign me-2"></i>This change may affect billing.</div>' : ''}
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-primary';
    document.getElementById('confirmModalBtn').textContent = 'Change Mode';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function confirmDisableKeyword(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    pendingAction = { type: 'disableKeyword', numberId, num };
    
    document.getElementById('confirmModalHeader').style.background = '#dc2626';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Disable Keyword';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Destructive Action:</strong> Disabling this keyword will stop all opt-out processing.
        </div>
        <p>Are you sure you want to disable keyword <strong>${num.number}</strong>?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
        </table>
        <div class="mb-3">
            <label class="form-label fw-bold">Reason for Disabling</label>
            <textarea class="form-control" id="actionReason" rows="2" placeholder="Enter reason (required)..."></textarea>
        </div>
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-danger';
    document.getElementById('confirmModalBtn').textContent = 'Disable Keyword';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function executeConfirmedAction() {
    if (!pendingAction) return;
    
    const reason = document.getElementById('actionReason')?.value || '';
    
    if (['suspend', 'disableKeyword'].includes(pendingAction.type) && !reason.trim()) {
        alert('Please provide a reason for this action.');
        return;
    }
    
    const num = pendingAction.num;
    const dataNum = mockNumbersData.find(n => n.id === pendingAction.numberId);
    
    switch (pendingAction.type) {
        case 'suspend':
            if (dataNum) dataNum.status = 'suspended';
            break;
        case 'reactivate':
            if (dataNum) dataNum.status = 'active';
            break;
        case 'changeMode':
            if (dataNum) dataNum.mode = pendingAction.targetMode.toLowerCase();
            break;
        case 'disableKeyword':
            if (dataNum) dataNum.status = 'suspended';
            break;
    }
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBER_ACTION_EXECUTED', {
            module: 'numbers',
            action: pendingAction.type,
            numberId: pendingAction.numberId,
            number: num.number,
            reason: reason
        }, pendingAction.type === 'disableKeyword' ? 'HIGH' : 'MEDIUM');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('confirmActionModal')).hide();
    pendingAction = null;
    
    applyFilters();
    
    showToast('Action completed successfully', 'success');
}

let currentReassignNumberId = null;

function openReassignModal(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    currentReassignNumberId = numberId;
    
    document.getElementById('reassignCurrentInfo').innerHTML = `
        <div class="bg-light p-3 rounded">
            <strong>Current Assignment:</strong><br>
            <span class="text-muted">Number:</span> ${num.number}<br>
            <span class="text-muted">Account:</span> ${num.account}<br>
            <span class="text-muted">Sub-Account:</span> ${num.subAccount}
        </div>
    `;
    
    document.getElementById('reassignAccount').value = '';
    document.getElementById('reassignSubAccount').value = '';
    document.getElementById('reassignReason').value = '';
    
    new bootstrap.Modal(document.getElementById('reassignModal')).show();
}

function executeReassign() {
    const newAccount = document.getElementById('reassignAccount').value;
    const newSubAccount = document.getElementById('reassignSubAccount').value;
    const reason = document.getElementById('reassignReason').value;
    
    if (!newAccount || !newSubAccount) {
        alert('Please select both a customer account and sub-account.');
        return;
    }
    if (!reason.trim()) {
        alert('Please provide a reason for the reassignment.');
        return;
    }
    
    const num = mockNumbersData.find(n => n.id === currentReassignNumberId);
    if (num) {
        num.account = newAccount;
        num.subAccount = newSubAccount;
    }
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBER_REASSIGNED', {
            module: 'numbers',
            numberId: currentReassignNumberId,
            newAccount: newAccount,
            newSubAccount: newSubAccount,
            reason: reason
        }, 'MEDIUM');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('reassignModal')).hide();
    currentReassignNumberId = null;
    
    applyFilters();
    showToast('Number reassigned successfully', 'success');
}

let currentCapabilitiesNumberId = null;

function openEditCapabilities(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    currentCapabilitiesNumberId = numberId;
    
    document.getElementById('capabilitiesNumberInfo').innerHTML = `
        <div class="bg-light p-3 rounded">
            <strong>${num.number}</strong> (${num.account})
        </div>
    `;
    
    const isKeyword = num.type === 'shortcode_keyword';
    const rulesAlert = document.getElementById('capabilitiesRulesAlert');
    const rulesText = document.getElementById('capabilitiesRulesText');
    
    if (isKeyword) {
        rulesAlert.style.display = 'block';
        rulesText.textContent = 'Shared Shortcode Keywords cannot have SenderID or Inbox capabilities. Only Opt-out and API are allowed.';
    } else {
        rulesAlert.style.display = 'none';
    }
    
    const allCapabilities = [
        { id: 'senderid', label: 'SenderID', desc: 'Use as sender ID for outbound messages', disabled: isKeyword },
        { id: 'inbox', label: 'Inbox', desc: 'Receive inbound messages', disabled: isKeyword },
        { id: 'optout', label: 'Opt-out', desc: 'Handle opt-out/unsubscribe requests', disabled: false },
        { id: 'api', label: 'API', desc: 'Available via API integration', disabled: false }
    ];
    
    document.getElementById('capabilityToggles').innerHTML = allCapabilities.map(cap => `
        <div class="form-check form-switch mb-2 ${cap.disabled ? 'opacity-50' : ''}">
            <input class="form-check-input" type="checkbox" id="cap_toggle_${cap.id}" 
                   ${num.capabilities.includes(cap.id) ? 'checked' : ''} 
                   ${cap.disabled ? 'disabled' : ''}>
            <label class="form-check-label" for="cap_toggle_${cap.id}">
                <strong>${cap.label}</strong>
                <small class="d-block text-muted">${cap.desc}</small>
            </label>
        </div>
    `).join('');
    
    new bootstrap.Modal(document.getElementById('editCapabilitiesModal')).show();
}

function saveCapabilities() {
    const num = mockNumbersData.find(n => n.id === currentCapabilitiesNumberId);
    if (!num) return;
    
    const newCaps = [];
    ['senderid', 'inbox', 'optout', 'api'].forEach(cap => {
        const toggle = document.getElementById(`cap_toggle_${cap}`);
        if (toggle && toggle.checked && !toggle.disabled) {
            newCaps.push(cap);
        }
    });
    
    num.capabilities = newCaps;
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBER_CAPABILITIES_CHANGED', {
            module: 'numbers',
            numberId: currentCapabilitiesNumberId,
            capabilities: newCaps
        }, 'MEDIUM');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('editCapabilitiesModal')).hide();
    currentCapabilitiesNumberId = null;
    
    showToast('Capabilities updated successfully', 'success');
}

function openReassignSubAccountOnly(numberId) {
    openReassignModal(numberId);
}

let currentOptoutNumberId = null;

function openOptoutRouting(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    currentOptoutNumberId = numberId;
    
    document.getElementById('optoutKeywordInfo').innerHTML = `
        <div class="bg-light p-3 rounded">
            <strong>Keyword:</strong> ${num.number}<br>
            <span class="text-muted">Account:</span> ${num.account} / ${num.subAccount}
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('optoutRoutingModal')).show();
}

function saveOptoutRouting() {
    const keywords = document.getElementById('optoutKeywords').value;
    const reply = document.getElementById('optoutReply').value;
    const forward = document.getElementById('optoutForward').value;
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('OPTOUT_ROUTING_UPDATED', {
            module: 'numbers',
            numberId: currentOptoutNumberId,
            keywords: keywords,
            forward: forward
        }, 'MEDIUM');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('optoutRoutingModal')).hide();
    currentOptoutNumberId = null;
    
    showToast('Opt-out routing updated successfully', 'success');
}

function openSubAccountAssign(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    alert(`TODO: Open Sub-Account assignment modal for ${num.number}\nThis would allow assigning/removing sub-accounts in Portal mode.`);
}

function openOverrideUsage(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    alert(`TODO: Open Override Default Usage modal for ${num.number}\nThis would allow overriding default usage settings in Portal mode.`);
}

function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0 position-fixed" 
             role="alert" style="top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const container = document.createElement('div');
    container.innerHTML = toastHtml;
    document.body.appendChild(container.firstElementChild);
    
    const toast = document.body.lastElementChild;
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}
</script>
@endpush

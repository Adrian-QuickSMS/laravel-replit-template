@extends('layouts.admin')

@section('title', 'MCC/MNC Reference - Supplier Management')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.mcc-mnc-table-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #dde4ea;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d4f4dd;
    color: #198754;
}

.status-badge.inactive {
    background: #e0e0e0;
    color: #6c757d;
}

.network-type-badge {
    padding: 0.15rem 0.5rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 500;
}

.network-type-badge.mobile {
    background: #e3f2fd;
    color: #1976d2;
}

.network-type-badge.fixed {
    background: #f3e5f5;
    color: #7b1fa2;
}

.network-type-badge.virtual {
    background: #fff3e0;
    color: #e65100;
}

.filter-toolbar {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.action-menu-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}

.action-menu-btn:hover {
    color: #1e3a5f;
}

.import-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.mcc-pagination-wrap {
    padding: 0.75rem 1rem;
}

.mcc-pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 4px;
}

.mcc-page-item .mcc-page-link,
.mcc-page-item span.mcc-page-link {
    display: inline-block;
    padding: 0.35rem 0.65rem;
    font-size: 0.8rem;
    color: var(--admin-primary);
    background: #fff;
    border: 1px solid #dde4ea;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    line-height: 1.4;
}

.mcc-page-item .mcc-page-link:hover {
    background: #f0f4f8;
    border-color: var(--admin-accent);
}

.mcc-page-item.active .mcc-page-link {
    background: var(--admin-primary);
    color: #fff;
    border-color: var(--admin-primary);
}

.mcc-page-item.disabled .mcc-page-link,
.mcc-page-item.disabled span.mcc-page-link {
    color: #adb5bd;
    cursor: not-allowed;
    background: #f8f9fa;
    border-color: #e9ecef;
}

.ref-tabs {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 1.5rem 0;
    border-bottom: 2px solid #e9ecef;
    gap: 0;
}

.ref-tab-item {
    margin-bottom: -2px;
}

.ref-tab-link {
    display: inline-flex;
    align-items: center;
    padding: 0.65rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: #6c757d;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.ref-tab-link:hover {
    color: var(--admin-primary);
}

.ref-tab-link.active {
    color: var(--admin-primary);
    border-bottom-color: var(--admin-primary);
    font-weight: 600;
}

.ref-tab-badge {
    display: inline-block;
    padding: 0.1rem 0.45rem;
    font-size: 0.65rem;
    font-weight: 600;
    border-radius: 10px;
    background: #e9ecef;
    color: #6c757d;
    margin-left: 0.4rem;
}

.ref-tab-link.active .ref-tab-badge {
    background: var(--admin-accent);
    color: #fff;
}

.uk-stat-card {
    background: #fff;
    border: 1px solid #dde4ea;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    text-align: center;
}

.uk-stat-card.matched { border-left: 3px solid #198754; }
.uk-stat-card.predicted { border-left: 3px solid #0d6efd; }
.uk-stat-card.unmatched { border-left: 3px solid #dc3545; }

.uk-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--admin-primary);
}

.uk-stat-label {
    font-size: 0.75rem;
    color: #6c757d;
}

.match-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.2rem 0.6rem;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: 500;
}

.match-badge.confirmed { background: #d4f4dd; color: #198754; }
.match-badge.predicted { background: #cfe2ff; color: #0d6efd; }
.match-badge.unmatched { background: #f8d7da; color: #dc3545; }

.wizard-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.wizard-step {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    color: #adb5bd;
    font-weight: 500;
}

.wizard-step.active {
    color: var(--admin-primary);
    font-weight: 600;
}

.wizard-step.done {
    color: #198754;
}

.wizard-step-num {
    display: inline-flex;
    width: 24px;
    height: 24px;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #e9ecef;
    font-size: 0.7rem;
    font-weight: 700;
}

.wizard-step.active .wizard-step-num {
    background: var(--admin-primary);
    color: #fff;
}

.wizard-step.done .wizard-step-num {
    background: #198754;
    color: #fff;
}

.drop-zone {
    border: 2px dashed #ced4da;
    border-radius: 12px;
    padding: 2.5rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s;
}

.drop-zone:hover {
    border-color: var(--admin-accent);
}
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Supplier Management</a></li>
        <li class="breadcrumb-item active">MCC/MNC Reference</li>
    </ol>
</div>

<div class="page-header">
    <div>
        <h2>Network Reference Database</h2>
        <p>Mobile network codes and UK number prefix management</p>
    </div>
    <div class="d-flex gap-2" id="tabActions">
        <button class="btn btn-admin-primary" onclick="openAddMccMncModal()" id="btnAddNetwork">
            <i class="fas fa-plus me-2"></i>Add Network
        </button>
        <button class="btn btn-outline-primary" onclick="openImportModal()" id="btnMccImport">
            <i class="fas fa-file-import me-2"></i>Bulk Import
        </button>
        <button class="btn btn-outline-primary d-none" onclick="openUkPrefixImportModal()" id="btnUkImport">
            <i class="fas fa-file-import me-2"></i>Import Ofcom Data
        </button>
    </div>
</div>

<ul class="ref-tabs" role="tablist">
    <li class="ref-tab-item">
        <a class="ref-tab-link active" data-tab="mcc-mnc-tab" onclick="switchTab('mcc-mnc-tab', this)">
            <i class="fas fa-globe me-1"></i> Global MCC/MNC
        </a>
    </li>
    <li class="ref-tab-item">
        <a class="ref-tab-link" data-tab="uk-prefixes-tab" onclick="switchTab('uk-prefixes-tab', this)">
            <i class="fas fa-phone-alt me-1"></i> UK Prefixes
            <span class="ref-tab-badge" id="ukPrefixCount">0</span>
        </a>
    </li>
</ul>

<div id="mcc-mnc-tab" class="ref-tab-content">

<div class="filter-toolbar">
    <div class="row g-3">
        <div class="col-md-3">
            <select class="form-select" id="filterCountry" onchange="filterNetworks()">
                <option value="">All Countries</option>
                @foreach($countries as $country)
                <option value="{{ $country->country_iso }}">{{ $country->country_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterType" onchange="filterNetworks()">
                <option value="">All Network Types</option>
                <option value="mobile">Mobile</option>
                <option value="fixed">Fixed</option>
                <option value="virtual">Virtual</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterStatus" onchange="filterNetworks()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" id="searchNetwork" placeholder="Search MCC/MNC or network..." onkeyup="filterNetworks()">
        </div>
    </div>
</div>

<div class="mcc-mnc-table-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>MCC</th>
                    <th>MNC</th>
                    <th>Country</th>
                    <th>Network Name</th>
                    <th>Network Type</th>
                    <th>Status</th>
                    <th>Rate Cards</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody id="mccMncTableBody">
                @forelse($mccMncList as $network)
                <tr data-country="{{ $network->country_iso }}"
                    data-type="{{ $network->network_type }}"
                    data-status="{{ $network->active ? 'active' : 'inactive' }}"
                    data-search="{{ strtolower($network->mcc . $network->mnc . $network->network_name . $network->country_name) }}">
                    <td><code>{{ $network->mcc }}</code></td>
                    <td><code>{{ $network->mnc }}</code></td>
                    <td>
                        <strong>{{ $network->country_name }}</strong>
                        <br><small class="text-muted">{{ $network->country_iso }}</small>
                    </td>
                    <td>{{ $network->network_name }}</td>
                    <td>
                        <span class="network-type-badge {{ $network->network_type }}">
                            {{ ucfirst($network->network_type) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge {{ $network->active ? 'active' : 'inactive' }}">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            {{ $network->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $network->rate_cards_count ?? 0 }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="action-menu-btn" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editNetwork({{ $network->id }})"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" onclick="toggleNetworkStatus({{ $network->id }})"><i class="fas fa-toggle-{{ $network->active ? 'off' : 'on' }} me-2"></i>{{ $network->active ? 'Deactivate' : 'Activate' }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.rate-cards.index', ['mcc' => $network->mcc, 'mnc' => $network->mnc]) }}"><i class="fas fa-dollar-sign me-2"></i>View Rates</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteNetwork({{ $network->id }})"><i class="fas fa-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No MCC/MNC records found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($mccMncList->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3 mcc-pagination-wrap">
    <div class="text-muted" style="font-size: 0.8rem;">
        Showing {{ $mccMncList->firstItem() }} to {{ $mccMncList->lastItem() }} of {{ $mccMncList->total() }} networks
    </div>
    <nav>
        <ul class="mcc-pagination">
            @if($mccMncList->onFirstPage())
                <li class="mcc-page-item disabled"><span class="mcc-page-link">&laquo; Previous</span></li>
            @else
                <li class="mcc-page-item"><a class="mcc-page-link" href="{{ $mccMncList->previousPageUrl() }}">&laquo; Previous</a></li>
            @endif

            @foreach($mccMncList->getUrlRange(1, $mccMncList->lastPage()) as $page => $url)
                <li class="mcc-page-item {{ $page == $mccMncList->currentPage() ? 'active' : '' }}">
                    <a class="mcc-page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
            @endforeach

            @if($mccMncList->hasMorePages())
                <li class="mcc-page-item"><a class="mcc-page-link" href="{{ $mccMncList->nextPageUrl() }}">Next &raquo;</a></li>
            @else
                <li class="mcc-page-item disabled"><span class="mcc-page-link">Next &raquo;</span></li>
            @endif
        </ul>
    </nav>
</div>
@endif

</div><!-- end mcc-mnc-tab -->

<div id="uk-prefixes-tab" class="ref-tab-content d-none">

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="uk-stat-card">
            <div class="uk-stat-value" id="statTotal">0</div>
            <div class="uk-stat-label">Total Prefixes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="uk-stat-card matched">
            <div class="uk-stat-value" id="statMatched">0</div>
            <div class="uk-stat-label">Confirmed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="uk-stat-card predicted">
            <div class="uk-stat-value" id="statPredicted">0</div>
            <div class="uk-stat-label">Predicted</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="uk-stat-card unmatched">
            <div class="uk-stat-value" id="statUnmatched">0</div>
            <div class="uk-stat-label">Unmatched</div>
        </div>
    </div>
</div>

<div class="filter-toolbar">
    <div class="row g-3">
        <div class="col-md-3">
            <select class="form-select" id="ukFilterMatch" onchange="loadUkPrefixes()">
                <option value="">All Match Status</option>
                <option value="confirmed">Confirmed</option>
                <option value="predicted">Predicted</option>
                <option value="unmatched">Unmatched</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="ukFilterCp" onchange="loadUkPrefixes()">
                <option value="">All Operators</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="ukSearchPrefix" placeholder="Search prefix or operator..." onkeyup="debounceUkSearch()">
        </div>
        <div class="col-md-2 d-flex align-items-center">
            <button class="btn btn-sm btn-outline-secondary w-100" onclick="loadUkPrefixes()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>
</div>

<div class="mcc-mnc-table-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Prefix</th>
                    <th>Raw Block</th>
                    <th>Operator (CP Name)</th>
                    <th>Mapped Network</th>
                    <th>Match Status</th>
                    <th>Number Length</th>
                    <th>Allocation Date</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody id="ukPrefixTableBody">
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No UK prefix data loaded. Import Ofcom data to get started.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="ukPaginationWrap" class="d-none d-flex justify-content-between align-items-center mt-3 mcc-pagination-wrap">
    <div class="text-muted" style="font-size: 0.8rem;" id="ukPaginationInfo"></div>
    <nav>
        <ul class="mcc-pagination" id="ukPaginationLinks"></ul>
    </nav>
</div>

</div><!-- end uk-prefixes-tab -->

<!-- UK Prefix Import Modal -->
<div class="modal fade" id="ukImportModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import UK Prefix Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="wizard-steps mb-4">
                    <div class="wizard-step active" data-step="1"><span class="wizard-step-num">1</span> Upload File</div>
                    <div class="wizard-step" data-step="2"><span class="wizard-step-num">2</span> Map Columns</div>
                    <div class="wizard-step" data-step="3"><span class="wizard-step-num">3</span> Import & Match</div>
                    <div class="wizard-step" data-step="4"><span class="wizard-step-num">4</span> Review Matches</div>
                </div>

                <!-- Step 1: Upload -->
                <div class="uk-wizard-step" id="ukStep1">
                    <p class="text-muted mb-3">Upload an Ofcom number allocation file (CSV or Excel). Prefixes starting with 7 will be padded with 44 automatically, and spaces will be removed.</p>
                    <div class="drop-zone" id="ukDropZone" onclick="document.getElementById('ukFileInput').click()">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                        <p>Drag & drop or click to select file</p>
                        <small class="text-muted">CSV, XLSX, XLS (max 10MB)</small>
                        <input type="file" id="ukFileInput" accept=".csv,.xlsx,.xls" style="display:none" onchange="handleUkFile(this)">
                    </div>
                    <div id="ukFileInfo" class="d-none mt-3 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-file-excel text-success me-2"></i><span id="ukFileName"></span></div>
                            <button class="btn btn-sm btn-outline-danger" onclick="resetUkImport()"><i class="fas fa-times"></i></button>
                        </div>
                        <small class="text-muted" id="ukRowCount"></small>
                    </div>
                    <div id="ukUploadSpinner" class="text-center d-none py-3"><div class="spinner-border text-primary"></div><p class="mt-2">Reading file...</p></div>
                    <div id="ukUploadError" class="alert alert-danger d-none mt-3"></div>
                </div>

                <!-- Step 2: Map Columns -->
                <div class="uk-wizard-step d-none" id="ukStep2">
                    <p class="text-muted mb-3">Map the columns from your file to the required fields.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Number Block <span class="text-danger">*</span></label>
                            <select class="form-select" id="ukMapNumberBlock"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Operator / CP Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="ukMapCpName"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Block Status</label>
                            <select class="form-select" id="ukMapStatus"><option value="">-- Skip --</option></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Number Length</label>
                            <select class="form-select" id="ukMapNumberLength"><option value="">-- Skip --</option></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Allocation Date</label>
                            <select class="form-select" id="ukMapAllocationDate"><option value="">-- Skip --</option></select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Preview (first 3 rows):</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="ukPreviewTable"></table>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Import Progress -->
                <div class="uk-wizard-step d-none" id="ukStep3">
                    <div id="ukImportProgress" class="text-center py-4">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                        <p class="mt-3">Importing and matching prefixes to networks...</p>
                        <small class="text-muted">This may take a moment for large files</small>
                    </div>
                    <div id="ukImportResults" class="d-none">
                        <div class="text-center mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-2">Import Complete</h5>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="text-center p-2 rounded" style="background: #d4f4dd;">
                                    <div class="fw-bold text-success" id="ukResultCreated">0</div>
                                    <small>Created</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 rounded" style="background: #fff3cd;">
                                    <div class="fw-bold text-warning" id="ukResultUpdated">0</div>
                                    <small>Updated</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 rounded" style="background: #f8d7da;">
                                    <div class="fw-bold text-danger" id="ukResultErrors">0</div>
                                    <small>Errors</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review Matches -->
                <div class="uk-wizard-step d-none" id="ukStep4">
                    <p class="text-muted mb-3">Review predicted network matches. Confirm correct predictions or assign networks to unmatched operators.</p>

                    <div id="predictedMatchesSection" class="d-none mb-4">
                        <h6><i class="fas fa-magic me-1 text-primary"></i> Predicted Matches</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Operator</th><th>Predicted Network</th><th>Prefixes</th><th>Actions</th></tr></thead>
                                <tbody id="predictedMatchesBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="unmatchedSection" class="d-none mb-4">
                        <h6><i class="fas fa-unlink me-1 text-warning"></i> Unmatched Operators</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Operator</th><th>Prefixes</th><th>Actions</th></tr></thead>
                                <tbody id="unmatchedBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="allMatchedMsg" class="text-center py-3 d-none">
                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                        <p class="text-success">All operators have been matched to networks.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" id="ukBtnNext" onclick="ukWizardNext()" disabled>
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Map Network Modal -->
<div class="modal fade" id="mapNetworkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Map Operator to Network</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mapCpName">
                <p class="mb-2">Map all prefixes for operator: <strong id="mapCpNameDisplay"></strong></p>
                <p class="text-muted small mb-3">This will update all prefixes belonging to this operator.</p>
                <div class="mb-3">
                    <label class="form-label">Select Network</label>
                    <select class="form-select" id="mapNetworkSelect">
                        <option value="">-- Choose a network --</option>
                    </select>
                </div>
                <hr>
                <p class="text-muted mb-2"><small>Or create a new network:</small></p>
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm" id="newNetworkName" placeholder="Network name">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" id="newNetworkMcc" value="234" maxlength="3" placeholder="MCC">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" id="newNetworkMnc" maxlength="3" placeholder="MNC">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-sm btn-outline-primary" onclick="createAndMapNetwork()">
                            <i class="fas fa-plus me-1"></i>Create & Map
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary btn-sm" onclick="confirmBulkMap()">
                    <i class="fas fa-check me-1"></i>Map Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add MCC/MNC Modal -->
<div class="modal fade" id="addMccMncModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Network</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMccMncForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MCC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mcc" maxlength="3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MNC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mnc" maxlength="3" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="country_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country ISO Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="country_iso" maxlength="2" required>
                        <small class="text-muted">2-letter code (e.g., GB, US)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Network Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="network_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Network Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="network_type" required>
                            <option value="mobile">Mobile</option>
                            <option value="fixed">Fixed</option>
                            <option value="virtual">Virtual</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitAddMccMnc()">Add Network</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit MCC/MNC Modal -->
<div class="modal fade" id="editMccMncModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Network</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editMccMncForm">
                    <input type="hidden" name="network_id" id="editNetworkId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MCC</label>
                            <input type="text" class="form-control" id="editMcc" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MNC</label>
                            <input type="text" class="form-control" id="editMnc" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Network Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="network_name" id="editNetworkName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Network Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="network_type" id="editNetworkType" required>
                            <option value="mobile">Mobile</option>
                            <option value="fixed">Fixed</option>
                            <option value="virtual">Virtual</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitEditMccMnc()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Import Wizard Modal -->
<div class="modal fade" id="importModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Bulk Import MCC/MNC Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="resetImportWizard()"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Step indicators -->
                <div class="import-steps d-flex border-bottom">
                    <div class="import-step active" id="stepIndicator1" style="flex:1; text-align:center; padding:0.75rem; font-size:0.85rem; font-weight:600; border-bottom:3px solid var(--admin-primary); color: var(--admin-primary);">
                        <span class="step-number" style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:var(--admin-primary);color:#fff;align-items:center;justify-content:center;font-size:0.7rem;margin-right:0.4rem;">1</span>Upload File
                    </div>
                    <div class="import-step" id="stepIndicator2" style="flex:1; text-align:center; padding:0.75rem; font-size:0.85rem; font-weight:600; border-bottom:3px solid #dee2e6; color: #adb5bd;">
                        <span class="step-number" style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:#adb5bd;color:#fff;align-items:center;justify-content:center;font-size:0.7rem;margin-right:0.4rem;">2</span>Map Columns
                    </div>
                    <div class="import-step" id="stepIndicator3" style="flex:1; text-align:center; padding:0.75rem; font-size:0.85rem; font-weight:600; border-bottom:3px solid #dee2e6; color: #adb5bd;">
                        <span class="step-number" style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:#adb5bd;color:#fff;align-items:center;justify-content:center;font-size:0.7rem;margin-right:0.4rem;">3</span>Preview & Import
                    </div>
                </div>

                <!-- Step 1: Upload File -->
                <div class="import-step-content p-4" id="step1Content">
                    <p class="text-muted mb-3">Upload a CSV or Excel file containing MCC/MNC network data. Your file can have any column layout — you'll map columns in the next step.</p>
                    <div class="upload-zone text-center p-4 rounded mb-3" id="dropZone" style="border:2px dashed #ccc; cursor:pointer; background:#fafbfc; transition: all 0.2s;">
                        <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: var(--admin-accent);"></i>
                        <p class="mb-1 fw-semibold">Drag & drop your file here</p>
                        <p class="text-muted mb-2" style="font-size:0.85rem;">or click to browse</p>
                        <span class="badge bg-light text-dark border">.csv</span>
                        <span class="badge bg-light text-dark border">.xlsx</span>
                        <span class="badge bg-light text-dark border">.xls</span>
                        <input type="file" id="importFile" accept=".csv,.xlsx,.xls" style="display:none;">
                    </div>
                    <div id="fileInfo" class="d-none">
                        <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:#f0f7ff; border:1px solid #c5ddf7;">
                            <i class="fas fa-file-alt fa-2x" style="color: var(--admin-accent);"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" id="fileName"></div>
                                <small class="text-muted" id="fileSize"></small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="clearFile()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div id="uploadError" class="alert alert-danger mt-3 d-none"></div>
                    <div id="uploadSpinner" class="text-center py-3 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Reading file...</p>
                    </div>
                </div>

                <!-- Step 2: Map Columns -->
                <div class="import-step-content p-4 d-none" id="step2Content">
                    <p class="text-muted mb-3">Map your file's columns to the required MCC/MNC fields. We've auto-detected likely matches where possible.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">MCC <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapMcc"></select>
                            <small class="text-muted">Mobile Country Code (3 digits)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">MNC <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapMnc"></select>
                            <small class="text-muted">Mobile Network Code (2-3 digits)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Country Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapCountryName"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Country ISO <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapCountryIso"></select>
                            <small class="text-muted">2-letter code (e.g., GB, US)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Network Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapNetworkName"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Network Type <span class="text-muted">(optional)</span></label>
                            <select class="form-select" id="mapNetworkType"></select>
                            <small class="text-muted">Defaults to "mobile" if not mapped</small>
                        </div>
                    </div>
                    <div id="mappingError" class="alert alert-danger mt-3 d-none"></div>
                </div>

                <!-- Step 3: Preview & Import -->
                <div class="import-step-content p-4 d-none" id="step3Content">
                    <div id="previewSection">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">Data Preview</h6>
                                <small class="text-muted">Showing first 5 rows of <span id="totalRowCount"></span> total</small>
                            </div>
                            <span class="badge" style="background: var(--admin-primary); font-size:0.8rem;" id="readyBadge"></span>
                        </div>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0" id="previewTable">
                                <thead style="background:#f8f9fa; position:sticky; top:0;">
                                    <tr id="previewHeader"></tr>
                                </thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3 mb-0" style="font-size:0.85rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            Duplicate MCC/MNC pairs will be <strong>updated</strong> with the new data. New entries will be <strong>created</strong>.
                        </div>
                    </div>
                    <div id="importProgress" class="d-none text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status" style="width:3rem;height:3rem;"></div>
                        <h6>Importing data...</h6>
                        <p class="text-muted" id="importProgressText">Please wait</p>
                    </div>
                    <div id="importResults" class="d-none">
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-3x mb-3" style="color:#198754;"></i>
                            <h5>Import Complete</h5>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-4 text-center">
                                <div class="p-3 rounded" style="background:#d4f4dd;">
                                    <div class="fs-4 fw-bold" style="color:#198754;" id="resultCreated">0</div>
                                    <small class="text-muted">Created</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-3 rounded" style="background:#fff3cd;">
                                    <div class="fs-4 fw-bold" style="color:#856404;" id="resultUpdated">0</div>
                                    <small class="text-muted">Updated</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-3 rounded" style="background:#f8d7da;">
                                    <div class="fs-4 fw-bold" style="color:#842029;" id="resultErrors">0</div>
                                    <small class="text-muted">Errors</small>
                                </div>
                            </div>
                        </div>
                        <div id="errorDetails" class="d-none">
                            <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Error Details</h6>
                            <div class="table-responsive" style="max-height:150px; overflow-y:auto;">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Row</th><th>Error</th></tr></thead>
                                    <tbody id="errorTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnBack" onclick="wizardBack()" style="display:none;">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </button>
                <div class="flex-grow-1"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetImportWizard()">Cancel</button>
                <button type="button" class="btn btn-admin-primary" id="btnNext" onclick="wizardNext()" disabled>
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddMccMncModal() {
    new bootstrap.Modal(document.getElementById('addMccMncModal')).show();
}

function submitAddMccMnc() {
    const form = document.getElementById('addMccMncForm');
    const formData = new FormData(form);

    fetch('{{ route('admin.mcc-mnc.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function editNetwork(networkId) {
    fetch(`/admin/supplier-management/mcc-mnc/${networkId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editNetworkId').value = data.id;
            document.getElementById('editMcc').value = data.mcc;
            document.getElementById('editMnc').value = data.mnc;
            document.getElementById('editNetworkName').value = data.network_name;
            document.getElementById('editNetworkType').value = data.network_type;
            new bootstrap.Modal(document.getElementById('editMccMncModal')).show();
        });
}

function submitEditMccMnc() {
    const networkId = document.getElementById('editNetworkId').value;
    const form = document.getElementById('editMccMncForm');
    const formData = new FormData(form);

    fetch(`/admin/supplier-management/mcc-mnc/${networkId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function toggleNetworkStatus(networkId) {
    if (confirm('Are you sure you want to change this network status?')) {
        fetch(`/admin/supplier-management/mcc-mnc/${networkId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function deleteNetwork(networkId) {
    if (confirm('Are you sure you want to delete this network? This may affect existing rate cards.')) {
        fetch(`/admin/supplier-management/mcc-mnc/${networkId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

let importState = { step: 1, headers: [], preview: [], totalRows: 0, importId: '' };

function openImportModal() {
    resetImportWizard();
    new bootstrap.Modal(document.getElementById('importModal')).show();
}

function resetImportWizard() {
    importState = { step: 1, headers: [], preview: [], totalRows: 0, importId: '' };
    showStep(1);
    document.getElementById('fileInfo').classList.add('d-none');
    document.getElementById('dropZone').classList.remove('d-none');
    document.getElementById('uploadError').classList.add('d-none');
    document.getElementById('uploadSpinner').classList.add('d-none');
    document.getElementById('importResults').classList.add('d-none');
    document.getElementById('importProgress').classList.add('d-none');
    document.getElementById('previewSection').classList.remove('d-none');
    document.getElementById('importFile').value = '';
    document.getElementById('btnNext').disabled = true;
    document.getElementById('btnNext').innerHTML = 'Next <i class="fas fa-arrow-right ms-1"></i>';
}

function showStep(step) {
    importState.step = step;
    [1,2,3].forEach(s => {
        document.getElementById('step' + s + 'Content').classList.toggle('d-none', s !== step);
        const ind = document.getElementById('stepIndicator' + s);
        if (s <= step) {
            ind.style.borderBottomColor = 'var(--admin-primary)';
            ind.style.color = 'var(--admin-primary)';
            ind.querySelector('.step-number').style.background = 'var(--admin-primary)';
        } else {
            ind.style.borderBottomColor = '#dee2e6';
            ind.style.color = '#adb5bd';
            ind.querySelector('.step-number').style.background = '#adb5bd';
        }
    });
    document.getElementById('btnBack').style.display = step > 1 ? '' : 'none';
    if (step === 3) {
        document.getElementById('btnNext').innerHTML = '<i class="fas fa-upload me-1"></i>Import Data';
    } else {
        document.getElementById('btnNext').innerHTML = 'Next <i class="fas fa-arrow-right ms-1"></i>';
    }
}

const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('importFile');

dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = 'var(--admin-accent)'; dropZone.style.background = '#e8f0fe'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#ccc'; dropZone.style.background = '#fafbfc'; });
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.style.borderColor = '#ccc'; dropZone.style.background = '#fafbfc';
    if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; handleFileSelected(); }
});
fileInput.addEventListener('change', handleFileSelected);

function handleFileSelected() {
    const file = fileInput.files[0];
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['csv','xlsx','xls'].includes(ext)) {
        document.getElementById('uploadError').textContent = 'Please upload a .csv, .xlsx, or .xls file.';
        document.getElementById('uploadError').classList.remove('d-none');
        return;
    }
    document.getElementById('uploadError').classList.add('d-none');
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatBytes(file.size);
    document.getElementById('fileInfo').classList.remove('d-none');
    document.getElementById('dropZone').classList.add('d-none');
    uploadFile(file);
}

function clearFile() {
    fileInput.value = '';
    document.getElementById('fileInfo').classList.add('d-none');
    document.getElementById('dropZone').classList.remove('d-none');
    document.getElementById('btnNext').disabled = true;
    importState.headers = [];
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function uploadFile(file) {
    document.getElementById('uploadSpinner').classList.remove('d-none');
    document.getElementById('btnNext').disabled = true;
    const formData = new FormData();
    formData.append('file', file);

    fetch('{{ route('admin.mcc-mnc.parse-file') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('uploadSpinner').classList.add('d-none');
        if (!data.success) {
            document.getElementById('uploadError').textContent = data.message;
            document.getElementById('uploadError').classList.remove('d-none');
            return;
        }
        importState.headers = data.headers;
        importState.preview = data.preview;
        importState.totalRows = data.totalRows;
        importState.importId = data.importId;
        document.getElementById('btnNext').disabled = false;
    })
    .catch(err => {
        document.getElementById('uploadSpinner').classList.add('d-none');
        document.getElementById('uploadError').textContent = 'Failed to upload file. Please try again.';
        document.getElementById('uploadError').classList.remove('d-none');
    });
}

function populateMappingDropdowns() {
    const fields = [
        { id: 'mapMcc', keywords: ['mcc'] },
        { id: 'mapMnc', keywords: ['mnc'] },
        { id: 'mapCountryName', keywords: ['country_name','country name','country','countryname'] },
        { id: 'mapCountryIso', keywords: ['country_iso','iso','country_code','countryiso','country iso','cc'] },
        { id: 'mapNetworkName', keywords: ['network_name','network name','operator','network','networkname','carrier'] },
        { id: 'mapNetworkType', keywords: ['network_type','type','network type','networktype'], optional: true },
    ];
    fields.forEach(field => {
        const sel = document.getElementById(field.id);
        sel.innerHTML = field.optional
            ? '<option value="">— Not mapped (defaults to mobile) —</option>'
            : '<option value="">— Select column —</option>';
        importState.headers.forEach((h, i) => {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = h;
            const lower = h.toLowerCase().trim();
            if (field.keywords.some(k => lower === k || lower.replace(/[\s_-]/g,'') === k.replace(/[\s_-]/g,''))) {
                opt.selected = true;
            }
            sel.appendChild(opt);
        });
    });
}

function buildPreviewTable() {
    const mapping = getMappingValues();
    const headerRow = document.getElementById('previewHeader');
    const tbody = document.getElementById('previewBody');
    headerRow.innerHTML = '<th>MCC</th><th>MNC</th><th>Country</th><th>ISO</th><th>Network</th><th>Type</th>';
    tbody.innerHTML = '';
    importState.preview.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><code>${row[mapping.mcc] || ''}</code></td>
            <td><code>${row[mapping.mnc] || ''}</code></td>
            <td>${row[mapping.country_name] || ''}</td>
            <td>${row[mapping.country_iso] || ''}</td>
            <td>${row[mapping.network_name] || ''}</td>
            <td>${mapping.network_type !== '' ? (row[mapping.network_type] || 'mobile') : 'mobile'}</td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('totalRowCount').textContent = importState.totalRows;
    document.getElementById('readyBadge').textContent = importState.totalRows + ' rows ready';
}

function getMappingValues() {
    return {
        mcc: parseInt(document.getElementById('mapMcc').value),
        mnc: parseInt(document.getElementById('mapMnc').value),
        country_name: parseInt(document.getElementById('mapCountryName').value),
        country_iso: parseInt(document.getElementById('mapCountryIso').value),
        network_name: parseInt(document.getElementById('mapNetworkName').value),
        network_type: document.getElementById('mapNetworkType').value,
    };
}

function wizardNext() {
    if (importState.step === 1) {
        if (!importState.headers.length) return;
        populateMappingDropdowns();
        showStep(2);
        document.getElementById('btnNext').disabled = false;
    } else if (importState.step === 2) {
        const required = ['mapMcc','mapMnc','mapCountryName','mapCountryIso','mapNetworkName'];
        const missing = required.filter(id => document.getElementById(id).value === '');
        if (missing.length) {
            document.getElementById('mappingError').textContent = 'Please map all required fields (marked with *).';
            document.getElementById('mappingError').classList.remove('d-none');
            return;
        }
        document.getElementById('mappingError').classList.add('d-none');
        buildPreviewTable();
        showStep(3);
    } else if (importState.step === 3) {
        runImport();
    }
}

function wizardBack() {
    if (importState.step === 2) showStep(1);
    else if (importState.step === 3) showStep(2);
}

function runImport() {
    const mapping = getMappingValues();
    document.getElementById('previewSection').classList.add('d-none');
    document.getElementById('importProgress').classList.remove('d-none');
    document.getElementById('btnNext').disabled = true;
    document.getElementById('btnBack').style.display = 'none';

    fetch('{{ route('admin.mcc-mnc.import') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ importId: importState.importId, mapping })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('importResults').classList.remove('d-none');
        if (data.success) {
            document.getElementById('resultCreated').textContent = data.imported;
            document.getElementById('resultUpdated').textContent = data.updated;
            document.getElementById('resultErrors').textContent = data.errors.length;
            if (data.errors.length) {
                document.getElementById('errorDetails').classList.remove('d-none');
                const errBody = document.getElementById('errorTableBody');
                errBody.innerHTML = '';
                data.errors.slice(0, 20).forEach(e => {
                    errBody.innerHTML += `<tr><td>${e.row}</td><td>${e.error}</td></tr>`;
                });
            }
            document.getElementById('btnNext').innerHTML = '<i class="fas fa-check me-1"></i>Done';
            document.getElementById('btnNext').disabled = false;
            document.getElementById('btnNext').onclick = () => location.reload();
        } else {
            document.getElementById('resultCreated').textContent = '0';
            document.getElementById('resultUpdated').textContent = '0';
            document.getElementById('resultErrors').textContent = '1';
            document.getElementById('errorDetails').classList.remove('d-none');
            document.getElementById('errorTableBody').innerHTML = `<tr><td>-</td><td>${data.message || 'Unknown error occurred'}</td></tr>`;
            document.getElementById('btnNext').innerHTML = '<i class="fas fa-redo me-1"></i>Try Again';
            document.getElementById('btnNext').disabled = false;
            document.getElementById('btnNext').onclick = () => { resetImportWizard(); };
        }
    })
    .catch(err => {
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('importResults').classList.remove('d-none');
        document.getElementById('resultCreated').textContent = '0';
        document.getElementById('resultUpdated').textContent = '0';
        document.getElementById('resultErrors').textContent = '1';
        document.getElementById('errorDetails').classList.remove('d-none');
        document.getElementById('errorTableBody').innerHTML = `<tr><td>-</td><td>Network error. Please check your connection and try again.</td></tr>`;
        document.getElementById('btnNext').innerHTML = '<i class="fas fa-redo me-1"></i>Try Again';
        document.getElementById('btnNext').disabled = false;
        document.getElementById('btnNext').onclick = () => { resetImportWizard(); };
    });
}

function filterNetworks() {
    const countryFilter = document.getElementById('filterCountry').value;
    const typeFilter = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const searchText = document.getElementById('searchNetwork').value.toLowerCase();
    const rows = document.querySelectorAll('#mccMncTableBody tr[data-country]');

    rows.forEach(row => {
        let show = true;

        if (countryFilter && row.dataset.country !== countryFilter) show = false;
        if (typeFilter && row.dataset.type !== typeFilter) show = false;
        if (statusFilter && row.dataset.status !== statusFilter) show = false;
        if (searchText && !row.dataset.search.includes(searchText)) show = false;

        row.style.display = show ? '' : 'none';
    });
}

function switchTab(tabId, el) {
    document.querySelectorAll('.ref-tab-content').forEach(t => t.classList.add('d-none'));
    document.getElementById(tabId).classList.remove('d-none');
    document.querySelectorAll('.ref-tab-link').forEach(l => l.classList.remove('active'));
    el.classList.add('active');

    if (tabId === 'uk-prefixes-tab') {
        document.getElementById('btnAddNetwork').classList.add('d-none');
        document.getElementById('btnMccImport').classList.add('d-none');
        document.getElementById('btnUkImport').classList.remove('d-none');
        loadUkPrefixes();
    } else {
        document.getElementById('btnAddNetwork').classList.remove('d-none');
        document.getElementById('btnMccImport').classList.remove('d-none');
        document.getElementById('btnUkImport').classList.add('d-none');
    }
}

let ukCurrentPage = 1;
let ukNetworksList = [];
let ukDebounceTimer = null;
let ukImportState = { step: 1, headers: [], preview: [], totalRows: 0, importId: '' };

function debounceUkSearch() {
    clearTimeout(ukDebounceTimer);
    ukDebounceTimer = setTimeout(() => loadUkPrefixes(), 300);
}

function loadUkPrefixes(page) {
    if (page) ukCurrentPage = page;
    const match = document.getElementById('ukFilterMatch').value;
    const cp = document.getElementById('ukFilterCp').value;
    const search = document.getElementById('ukSearchPrefix').value;

    let url = `{{ route('admin.uk-prefixes.index') }}?page=${ukCurrentPage}`;
    if (match) url += `&match_status=${encodeURIComponent(match)}`;
    if (cp) url += `&cp_name=${encodeURIComponent(cp)}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    fetch(url, { headers: { 'Accept': 'application/json' }})
    .then(r => r.json())
    .then(data => {
        renderUkStats(data.stats);
        renderUkTable(data.prefixes);
        renderUkPagination(data.prefixes);
        renderCpFilter(data.cpNames);
        ukNetworksList = data.ukNetworks;
        document.getElementById('ukPrefixCount').textContent = data.stats.total;
    })
    .catch(err => console.error('Failed to load UK prefixes:', err));
}

function renderUkStats(stats) {
    document.getElementById('statTotal').textContent = stats.total.toLocaleString();
    document.getElementById('statMatched').textContent = stats.matched.toLocaleString();
    document.getElementById('statPredicted').textContent = stats.predicted.toLocaleString();
    document.getElementById('statUnmatched').textContent = stats.unmatched.toLocaleString();
}

function renderUkTable(paginated) {
    const tbody = document.getElementById('ukPrefixTableBody');
    if (!paginated.data || paginated.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2"></i><p>No UK prefix data found. Import Ofcom data to get started.</p></td></tr>`;
        return;
    }

    tbody.innerHTML = paginated.data.map(p => {
        const networkName = p.mcc_mnc ? p.mcc_mnc.network_name : '<span class="text-muted">-</span>';
        const matchClass = p.match_status;
        const matchLabel = p.match_status.charAt(0).toUpperCase() + p.match_status.slice(1);
        const allocDate = p.allocation_date ? new Date(p.allocation_date).toLocaleDateString('en-GB') : '-';

        let actions = '';
        if (p.match_status === 'predicted') {
            actions = `<button class="btn btn-sm btn-outline-success me-1" onclick="confirmPrefix(${p.id})" title="Confirm match"><i class="fas fa-check"></i></button><button class="btn btn-sm btn-outline-danger" onclick="rejectPrefix(${p.id})" title="Reject match"><i class="fas fa-times"></i></button>`;
        } else if (p.match_status === 'unmatched') {
            actions = `<button class="btn btn-sm btn-outline-primary" onclick="openMapModal('${p.cp_name.replace(/'/g, "\\'")}', ${p.id})" title="Map all '${p.cp_name}' prefixes"><i class="fas fa-link"></i></button>`;
        } else {
            actions = `<button class="btn btn-sm btn-outline-secondary" onclick="openMapModal('${p.cp_name.replace(/'/g, "\\'")}', ${p.id})" title="Re-map all '${p.cp_name}' prefixes"><i class="fas fa-edit"></i></button>`;
        }

        return `<tr>
            <td><code>${p.prefix}</code></td>
            <td><small class="text-muted">${p.number_block_raw || '-'}</small></td>
            <td>${p.cp_name}</td>
            <td>${networkName}</td>
            <td><span class="match-badge ${matchClass}"><i class="fas fa-circle" style="font-size:5px;"></i> ${matchLabel}</span></td>
            <td><small>${p.number_length || '-'}</small></td>
            <td><small>${allocDate}</small></td>
            <td>${actions}</td>
        </tr>`;
    }).join('');
}

function renderUkPagination(paginated) {
    const wrap = document.getElementById('ukPaginationWrap');
    if (paginated.last_page <= 1) { wrap.classList.add('d-none'); return; }
    wrap.classList.remove('d-none');

    document.getElementById('ukPaginationInfo').textContent = `Showing ${paginated.from} to ${paginated.to} of ${paginated.total} prefixes`;

    let links = '';
    if (paginated.current_page > 1) {
        links += `<li class="mcc-page-item"><a class="mcc-page-link" onclick="loadUkPrefixes(${paginated.current_page - 1})">&laquo;</a></li>`;
    }
    const start = Math.max(1, paginated.current_page - 3);
    const end = Math.min(paginated.last_page, paginated.current_page + 3);
    for (let i = start; i <= end; i++) {
        links += `<li class="mcc-page-item ${i === paginated.current_page ? 'active' : ''}"><a class="mcc-page-link" onclick="loadUkPrefixes(${i})">${i}</a></li>`;
    }
    if (paginated.current_page < paginated.last_page) {
        links += `<li class="mcc-page-item"><a class="mcc-page-link" onclick="loadUkPrefixes(${paginated.current_page + 1})">&raquo;</a></li>`;
    }
    document.getElementById('ukPaginationLinks').innerHTML = links;
}

function renderCpFilter(cpNames) {
    const sel = document.getElementById('ukFilterCp');
    const current = sel.value;
    sel.innerHTML = '<option value="">All Operators</option>';
    cpNames.forEach(name => {
        sel.innerHTML += `<option value="${name}" ${name === current ? 'selected' : ''}>${name}</option>`;
    });
}

function confirmPrefix(id) {
    fetch(`/admin/supplier-management/uk-prefixes/${id}/confirm`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(() => loadUkPrefixes())
    .catch(err => alert('Error confirming prediction'));
}

function rejectPrefix(id) {
    fetch(`/admin/supplier-management/uk-prefixes/${id}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(() => loadUkPrefixes())
    .catch(err => alert('Error rejecting prediction'));
}

function openMapModal(cpName, prefixId) {
    document.getElementById('mapCpName').value = cpName;
    document.getElementById('mapCpNameDisplay').textContent = cpName;

    const sel = document.getElementById('mapNetworkSelect');
    sel.innerHTML = '<option value="">-- Choose a network --</option>';
    ukNetworksList.forEach(n => {
        sel.innerHTML += `<option value="${n.id}">${n.network_name} (${n.mcc}/${n.mnc})</option>`;
    });

    document.getElementById('newNetworkName').value = '';
    document.getElementById('newNetworkMnc').value = '';
    new bootstrap.Modal(document.getElementById('mapNetworkModal')).show();
}

function confirmBulkMap() {
    const cpName = document.getElementById('mapCpName').value;
    const mccMncId = document.getElementById('mapNetworkSelect').value;
    if (!mccMncId) { alert('Please select a network'); return; }

    fetch('{{ route('admin.uk-prefixes.bulk-confirm') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ cp_name: cpName, mcc_mnc_id: parseInt(mccMncId) })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('mapNetworkModal')).hide();
            loadUkPrefixes();
            alert(data.message);
        }
    })
    .catch(err => alert('Error mapping network'));
}

function createAndMapNetwork() {
    const cpName = document.getElementById('mapCpName').value;
    const name = document.getElementById('newNetworkName').value;
    const mcc = document.getElementById('newNetworkMcc').value;
    const mnc = document.getElementById('newNetworkMnc').value;

    if (!name || !mcc || !mnc) { alert('Please fill in all fields'); return; }

    fetch('{{ route('admin.uk-prefixes.create-and-map') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ network_name: name, mcc: mcc, mnc: mnc, network_type: 'mobile', cp_name: cpName })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('mapNetworkModal')).hide();
            loadUkPrefixes();
            alert(data.message);
        } else {
            alert(data.message || 'Error creating network');
        }
    })
    .catch(err => alert('Error creating network'));
}

function openUkPrefixImportModal() {
    resetUkImport();
    new bootstrap.Modal(document.getElementById('ukImportModal')).show();
}

function resetUkImport() {
    ukImportState = { step: 1, headers: [], preview: [], totalRows: 0, importId: '' };
    showUkStep(1);
    document.getElementById('ukFileInfo').classList.add('d-none');
    document.getElementById('ukDropZone').classList.remove('d-none');
    document.getElementById('ukUploadError').classList.add('d-none');
    document.getElementById('ukBtnNext').disabled = true;
    document.getElementById('ukBtnNext').innerHTML = 'Next <i class="fas fa-arrow-right ms-1"></i>';
    document.getElementById('ukBtnNext').onclick = ukWizardNext;
    if (document.getElementById('ukFileInput')) document.getElementById('ukFileInput').value = '';
}

function showUkStep(n) {
    document.querySelectorAll('.uk-wizard-step').forEach(s => s.classList.add('d-none'));
    document.getElementById('ukStep' + n).classList.remove('d-none');
    document.querySelectorAll('.wizard-step').forEach(s => {
        const step = parseInt(s.dataset.step);
        s.classList.remove('active', 'done');
        if (step < n) s.classList.add('done');
        if (step === n) s.classList.add('active');
    });
    ukImportState.step = n;
}

function handleUkFile(input) {
    const file = input.files[0];
    if (!file) return;

    document.getElementById('ukDropZone').classList.add('d-none');
    document.getElementById('ukUploadSpinner').classList.remove('d-none');
    document.getElementById('ukUploadError').classList.add('d-none');

    const formData = new FormData();
    formData.append('file', file);

    fetch('{{ route('admin.uk-prefixes.parse-file') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('ukUploadSpinner').classList.add('d-none');
        if (!data.success) {
            document.getElementById('ukUploadError').textContent = data.message;
            document.getElementById('ukUploadError').classList.remove('d-none');
            document.getElementById('ukDropZone').classList.remove('d-none');
            return;
        }
        ukImportState.headers = data.headers;
        ukImportState.preview = data.preview;
        ukImportState.totalRows = data.totalRows;
        ukImportState.importId = data.importId;

        document.getElementById('ukFileName').textContent = data.fileName;
        document.getElementById('ukRowCount').textContent = `${data.totalRows} data rows`;
        document.getElementById('ukFileInfo').classList.remove('d-none');
        document.getElementById('ukBtnNext').disabled = false;
    })
    .catch(err => {
        document.getElementById('ukUploadSpinner').classList.add('d-none');
        document.getElementById('ukUploadError').textContent = 'Error uploading file';
        document.getElementById('ukUploadError').classList.remove('d-none');
        document.getElementById('ukDropZone').classList.remove('d-none');
    });
}

function ukWizardNext() {
    if (ukImportState.step === 1) {
        showUkStep(2);
        populateUkColumnMapping();
        document.getElementById('ukBtnNext').innerHTML = '<i class="fas fa-upload me-1"></i>Import & Match';
    } else if (ukImportState.step === 2) {
        runUkImport();
    } else if (ukImportState.step === 3) {
        showUkStep(4);
        showReviewStep();
        document.getElementById('ukBtnNext').innerHTML = '<i class="fas fa-check me-1"></i>Done';
        document.getElementById('ukBtnNext').disabled = false;
        document.getElementById('ukBtnNext').onclick = () => {
            bootstrap.Modal.getInstance(document.getElementById('ukImportModal')).hide();
            loadUkPrefixes();
        };
    } else if (ukImportState.step === 4) {
        bootstrap.Modal.getInstance(document.getElementById('ukImportModal')).hide();
        loadUkPrefixes();
    }
}

function populateUkColumnMapping() {
    const headers = ukImportState.headers;
    const selectors = ['ukMapNumberBlock', 'ukMapCpName', 'ukMapStatus', 'ukMapNumberLength', 'ukMapAllocationDate'];
    const autoMatchKeys = {
        'ukMapNumberBlock': ['number block', 'number', 'block', 'prefix', 'nms'],
        'ukMapCpName': ['cp name', 'cp', 'operator', 'provider', 'name'],
        'ukMapStatus': ['status', 'block status'],
        'ukMapNumberLength': ['length', 'number length', 'non geo'],
        'ukMapAllocationDate': ['date', 'allocation', 'allocated']
    };

    selectors.forEach(selId => {
        const sel = document.getElementById(selId);
        const isRequired = selId === 'ukMapNumberBlock' || selId === 'ukMapCpName';
        const existingOpts = isRequired ? '' : '<option value="">-- Skip --</option>';
        sel.innerHTML = existingOpts;

        headers.forEach((h, i) => {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = h;
            sel.appendChild(opt);
        });

        const keys = autoMatchKeys[selId] || [];
        for (let i = 0; i < headers.length; i++) {
            const hLower = headers[i].toLowerCase();
            if (keys.some(k => hLower.includes(k))) {
                sel.value = i;
                break;
            }
        }
    });

    let previewHtml = '<thead><tr>';
    headers.forEach(h => previewHtml += `<th style="font-size:0.75rem;">${h}</th>`);
    previewHtml += '</tr></thead><tbody>';
    ukImportState.preview.slice(0, 3).forEach(row => {
        previewHtml += '<tr>';
        row.forEach(cell => previewHtml += `<td style="font-size:0.75rem;">${cell}</td>`);
        previewHtml += '</tr>';
    });
    previewHtml += '</tbody>';
    document.getElementById('ukPreviewTable').innerHTML = previewHtml;
}

function runUkImport() {
    showUkStep(3);
    document.getElementById('ukImportProgress').classList.remove('d-none');
    document.getElementById('ukImportResults').classList.add('d-none');
    document.getElementById('ukBtnNext').disabled = true;

    const mapping = {
        number_block: parseInt(document.getElementById('ukMapNumberBlock').value),
        cp_name: parseInt(document.getElementById('ukMapCpName').value),
    };

    const statusVal = document.getElementById('ukMapStatus').value;
    if (statusVal !== '') mapping.status = parseInt(statusVal);
    const lenVal = document.getElementById('ukMapNumberLength').value;
    if (lenVal !== '') mapping.number_length = parseInt(lenVal);
    const dateVal = document.getElementById('ukMapAllocationDate').value;
    if (dateVal !== '') mapping.allocation_date = parseInt(dateVal);

    fetch('{{ route('admin.uk-prefixes.import') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ importId: ukImportState.importId, mapping })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('ukImportProgress').classList.add('d-none');
        document.getElementById('ukImportResults').classList.remove('d-none');

        if (data.success) {
            document.getElementById('ukResultCreated').textContent = data.imported;
            document.getElementById('ukResultUpdated').textContent = data.updated;
            document.getElementById('ukResultErrors').textContent = data.totalErrors || data.errors.length;

            ukImportState.unmatchedCps = data.unmatchedCps || [];
            ukImportState.predictedCps = data.predictedCps || [];

            document.getElementById('ukBtnNext').disabled = false;
            document.getElementById('ukBtnNext').innerHTML = 'Review Matches <i class="fas fa-arrow-right ms-1"></i>';
        } else {
            document.getElementById('ukResultCreated').textContent = '0';
            document.getElementById('ukResultUpdated').textContent = '0';
            document.getElementById('ukResultErrors').textContent = data.message || 'Error';
        }
    })
    .catch(err => {
        document.getElementById('ukImportProgress').classList.add('d-none');
        document.getElementById('ukImportResults').classList.remove('d-none');
        document.getElementById('ukResultErrors').textContent = 'Network error';
    });
}

function showReviewStep() {
    const predicted = ukImportState.predictedCps || [];
    const unmatched = ukImportState.unmatchedCps || [];

    if (predicted.length > 0) {
        document.getElementById('predictedMatchesSection').classList.remove('d-none');
        const tbody = document.getElementById('predictedMatchesBody');
        tbody.innerHTML = predicted.map(p => `
            <tr>
                <td>${p.cp_name}</td>
                <td><span class="match-badge predicted"><i class="fas fa-magic" style="font-size:8px;"></i> ${p.network_name}</span></td>
                <td><span class="badge bg-light text-dark">${p.prefix_count}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-success me-1" onclick="bulkConfirmCp('${p.cp_name.replace(/'/g, "\\'")}', ${p.mcc_mnc_id})"><i class="fas fa-check me-1"></i>Confirm All</button>
                    <button class="btn btn-sm btn-outline-warning" onclick="openMapModal('${p.cp_name.replace(/'/g, "\\'")}')"><i class="fas fa-exchange-alt me-1"></i>Change</button>
                </td>
            </tr>
        `).join('');
    } else {
        document.getElementById('predictedMatchesSection').classList.add('d-none');
    }

    if (unmatched.length > 0) {
        document.getElementById('unmatchedSection').classList.remove('d-none');
        const tbody = document.getElementById('unmatchedBody');
        tbody.innerHTML = unmatched.map(u => `
            <tr>
                <td>${u.cp_name}</td>
                <td><span class="badge bg-light text-dark">${u.prefix_count}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="openMapModal('${u.cp_name.replace(/'/g, "\\'")}')"><i class="fas fa-link me-1"></i>Map to Network</button>
                </td>
            </tr>
        `).join('');
    } else {
        document.getElementById('unmatchedSection').classList.add('d-none');
    }

    if (predicted.length === 0 && unmatched.length === 0) {
        document.getElementById('allMatchedMsg').classList.remove('d-none');
    }
}

function bulkConfirmCp(cpName, mccMncId) {
    fetch('{{ route('admin.uk-prefixes.bulk-confirm') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ cp_name: cpName, mcc_mnc_id: mccMncId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            event.target.closest('tr').remove();
            loadUkPrefixes();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'uk-prefixes') {
        const ukTab = document.querySelector('[data-tab="uk-prefixes-tab"]');
        if (ukTab) switchTab('uk-prefixes-tab', ukTab);
    }
});
</script>
@endpush

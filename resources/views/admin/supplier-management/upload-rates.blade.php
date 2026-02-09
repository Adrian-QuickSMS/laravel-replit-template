@extends('layouts.admin')

@section('title', 'Upload Rate Cards - Supplier Management')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.upload-wizard {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #dde4ea;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.wizard-step {
    display: none;
}

.wizard-step.active {
    display: block;
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    position: relative;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #dde4ea;
    z-index: 0;
}

.step-item {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 2px solid #dde4ea;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 0.5rem;
    transition: all 0.3s;
}

.step-item.completed .step-circle {
    background: #198754;
    border-color: #198754;
    color: #fff;
}

.step-item.active .step-circle {
    background: #1e3a5f;
    border-color: #1e3a5f;
    color: #fff;
}

.step-label {
    font-size: 0.8rem;
    color: #6c757d;
}

.step-item.active .step-label {
    color: #1e3a5f;
    font-weight: 600;
}

.upload-dropzone {
    border: 2px dashed #dde4ea;
    border-radius: 12px;
    padding: 3rem 2rem;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
}

.upload-dropzone:hover,
.upload-dropzone.dragover {
    border-color: #4a90d9;
    background: #f8f9fa;
}

.upload-icon {
    font-size: 3rem;
    color: #4a90d9;
    margin-bottom: 1rem;
}

.preview-table-wrap {
    max-height: 400px;
    overflow: auto;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.preview-table-wrap table {
    font-size: 0.78rem;
    margin: 0;
}

.preview-table-wrap th {
    background: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 2;
    font-size: 0.72rem;
    padding: 0.4rem 0.5rem;
}

.preview-table-wrap td {
    padding: 0.35rem 0.5rem;
    white-space: nowrap;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.row-selector {
    cursor: pointer;
    transition: background 0.15s;
}

.row-selector:hover {
    background: #eef3f9 !important;
}

.row-selector.selected-header {
    background: #d4e6f9 !important;
    font-weight: 600;
}

.row-selector.skipped-row {
    background: #f8f9fa;
    color: #adb5bd;
}

.header-badge {
    display: inline-block;
    background: var(--admin-primary);
    color: #fff;
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 4px;
}

.mapping-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #e9ecef;
}

.mapping-field {
    margin-bottom: 0.75rem;
}

.mapping-field label {
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.mapping-field .required-star {
    color: #dc3545;
}

.mapping-field select {
    font-size: 0.82rem;
}

.validation-error {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    font-size: 0.82rem;
    border-radius: 0 6px 6px 0;
}

.validation-success {
    background: #d1e7dd;
    border-left: 4px solid #198754;
    padding: 0.75rem;
    border-radius: 0 6px 6px 0;
}

.summary-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
}

.summary-stat {
    text-align: center;
    padding: 0.75rem;
}

.summary-stat .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--admin-primary);
}

.summary-stat .stat-label {
    font-size: 0.78rem;
    color: #6c757d;
}

.btn-admin-primary {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #fff;
}

.btn-admin-primary:hover {
    background: var(--admin-secondary);
    border-color: var(--admin-secondary);
    color: #fff;
}

.error-list-wrap {
    max-height: 200px;
    overflow-y: auto;
}
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Supplier Management</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.rate-cards.index') }}">Rate Cards</a></li>
        <li class="breadcrumb-item active">Upload Rates</li>
    </ol>
</div>

<div class="page-header">
    <div>
        <h2>Upload Rate Cards</h2>
        <p>Import supplier rates via CSV or Excel file</p>
    </div>
    <div>
        <a href="{{ route('admin.rate-cards.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Rate Cards
        </a>
    </div>
</div>

<div class="upload-wizard p-4">
    <div class="step-indicator">
        <div class="step-item active" id="stepIndicator1">
            <div class="step-circle">1</div>
            <div class="step-label">Select Gateway</div>
        </div>
        <div class="step-item" id="stepIndicator2">
            <div class="step-circle">2</div>
            <div class="step-label">Upload File</div>
        </div>
        <div class="step-item" id="stepIndicator3">
            <div class="step-circle">3</div>
            <div class="step-label">Select Header Row</div>
        </div>
        <div class="step-item" id="stepIndicator4">
            <div class="step-circle">4</div>
            <div class="step-label">Map Columns</div>
        </div>
        <div class="step-item" id="stepIndicator5">
            <div class="step-circle">5</div>
            <div class="step-label">Confirm Import</div>
        </div>
    </div>

    <!-- Step 1: Select Gateway -->
    <div class="wizard-step active" id="step1">
        <h4 class="mb-4">Step 1: Select Gateway</h4>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="mb-3">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg" id="selectSupplier" onchange="loadGateways()">
                        <option value="">Choose supplier...</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gateway <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg" id="selectGateway" disabled>
                        <option value="">Select a supplier first...</option>
                    </select>
                    <small class="text-muted">Select which gateway/route these rates apply to</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Valid From Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control form-control-lg" id="validFromDate" value="{{ date('Y-m-d') }}">
                    <small class="text-muted">When should these rates become active?</small>
                </div>
                <button class="btn btn-admin-primary btn-lg mt-3" onclick="goToStep(2)" disabled id="step1NextBtn">
                    Continue to Upload <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 2: Upload File -->
    <div class="wizard-step" id="step2">
        <h4 class="mb-4">Step 2: Upload Rate Card File</h4>
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="upload-dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h5>Drop your file here or click to browse</h5>
                    <p class="text-muted mb-0">Supports CSV, Excel (.xlsx, .xls) files up to 10MB</p>
                    <input type="file" id="fileInput" accept=".csv,.xlsx,.xls" style="display: none" onchange="handleFileSelect(event)">
                </div>

                <div id="fileInfo" class="mt-3" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                            <div>
                                <strong id="fileName"></strong>
                                <div class="text-muted" style="font-size: 0.8rem;" id="fileSize"></div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div id="uploadProgress" class="mt-3" style="display: none;">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                        <span class="ms-2">Parsing file...</span>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-secondary" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </button>
                    <button class="btn btn-admin-primary ms-2" onclick="uploadFile()" disabled id="step2NextBtn">
                        Parse File <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Select Header Row -->
    <div class="wizard-step" id="step3">
        <h4 class="mb-3">Step 3: Select Header Row</h4>
        <p class="text-muted mb-3">Click the row that contains the column headings. Rows above it (filler rows) will be skipped during import.</p>
        <div class="d-flex align-items-center mb-3 gap-2">
            <span class="badge bg-primary" style="background: var(--admin-primary) !important;">
                <i class="fas fa-file me-1"></i><span id="parsedFileName"></span>
            </span>
            <span class="badge bg-secondary"><span id="parsedRowCount"></span> rows</span>
        </div>
        <div class="preview-table-wrap" id="headerSelectionTable"></div>
        <div class="mt-4">
            <button class="btn btn-secondary" onclick="goToStep(2)">
                <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <button class="btn btn-admin-primary ms-2" onclick="goToStep(4)" disabled id="step3NextBtn">
                Continue to Map Columns <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

    <!-- Step 4: Map Columns -->
    <div class="wizard-step" id="step4">
        <h4 class="mb-3">Step 4: Map Columns</h4>
        <p class="text-muted mb-3">Map the columns from your file to the required rate card fields.</p>
        <div class="row">
            <div class="col-md-5">
                <div class="mapping-card">
                    <h6 class="mb-3"><i class="fas fa-link me-2"></i>Column Mapping</h6>
                    <div class="mapping-field">
                        <label>MCC <span class="required-star">*</span></label>
                        <select class="form-select form-select-sm" id="mapMcc">
                            <option value="">-- Select column --</option>
                        </select>
                    </div>
                    <div class="mapping-field">
                        <label>MNC <span class="required-star">*</span></label>
                        <select class="form-select form-select-sm" id="mapMnc">
                            <option value="">-- Select column --</option>
                        </select>
                    </div>
                    <div class="mapping-field">
                        <label>Rate <span class="required-star">*</span></label>
                        <select class="form-select form-select-sm" id="mapRate">
                            <option value="">-- Select column --</option>
                        </select>
                    </div>
                    <div class="mapping-field">
                        <label>Currency <span class="text-muted" style="font-weight:400; font-size:0.75rem;">(optional)</span></label>
                        <select class="form-select form-select-sm" id="mapCurrency">
                            <option value="">-- Not in file (use gateway default) --</option>
                        </select>
                    </div>
                    <div class="mapping-field">
                        <label>Product Type <span class="text-muted" style="font-weight:400; font-size:0.75rem;">(optional)</span></label>
                        <select class="form-select form-select-sm" id="mapProductType">
                            <option value="">-- Not in file (default: SMS) --</option>
                        </select>
                    </div>
                    <div class="mapping-field">
                        <label>Country <span class="text-muted" style="font-weight:400; font-size:0.75rem;">(optional)</span></label>
                        <select class="form-select form-select-sm" id="mapCountry">
                            <option value="">-- Not in file (lookup from MCC/MNC) --</option>
                        </select>
                    </div>
                    <div class="mapping-field mb-0">
                        <label>Network <span class="text-muted" style="font-weight:400; font-size:0.75rem;">(optional)</span></label>
                        <select class="form-select form-select-sm" id="mapNetwork">
                            <option value="">-- Not in file (lookup from MCC/MNC) --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <h6 class="mb-2"><i class="fas fa-table me-2"></i>Data Preview</h6>
                <div class="preview-table-wrap" id="mappingPreviewTable"></div>
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-secondary" onclick="goToStep(3)">
                <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <button class="btn btn-admin-primary ms-2" onclick="validateAndPreview()" disabled id="step4NextBtn">
                <i class="fas fa-check-circle me-1"></i>Validate & Preview
            </button>
        </div>
    </div>

    <!-- Step 5: Confirm Import -->
    <div class="wizard-step" id="step5">
        <h4 class="mb-3">Step 5: Review & Import</h4>
        <div id="validationResults"></div>
        <div id="importActions" style="display: none;">
            <div class="mt-4">
                <button class="btn btn-secondary" onclick="goToStep(4)">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </button>
                <button class="btn btn-success btn-lg ms-2" onclick="confirmImport()" id="confirmImportBtn">
                    <i class="fas fa-check me-2"></i>Import Rate Cards
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let uploadedFile = null;
let selectedGatewayId = null;
let importId = null;
let parsedRows = [];
let selectedHeaderRow = -1;
let headerColumns = [];
let validatedRates = null;

function loadGateways() {
    const supplierId = document.getElementById('selectSupplier').value;
    const gatewaySelect = document.getElementById('selectGateway');

    if (!supplierId) {
        gatewaySelect.innerHTML = '<option value="">Select a supplier first...</option>';
        gatewaySelect.disabled = true;
        document.getElementById('step1NextBtn').disabled = true;
        return;
    }

    fetch(`/admin/supplier-management/suppliers/${supplierId}/gateways`)
        .then(r => r.json())
        .then(gateways => {
            gatewaySelect.innerHTML = '<option value="">Choose gateway...</option>';
            gateways.forEach(gw => {
                gatewaySelect.innerHTML += `<option value="${gw.id}">${gw.name} (${gw.gateway_code})</option>`;
            });
            gatewaySelect.disabled = false;
        });
}

document.getElementById('selectGateway').addEventListener('change', function() {
    document.getElementById('step1NextBtn').disabled = !this.value;
    selectedGatewayId = this.value;
});

function handleFileSelect(event) {
    uploadedFile = event.target.files[0];
    if (!uploadedFile) return;
    document.getElementById('fileName').textContent = uploadedFile.name;
    const sizeKB = (uploadedFile.size / 1024).toFixed(1);
    document.getElementById('fileSize').textContent = sizeKB > 1024 ? (sizeKB / 1024).toFixed(1) + ' MB' : sizeKB + ' KB';
    document.getElementById('fileInfo').style.display = 'block';
    document.getElementById('step2NextBtn').disabled = false;
}

function clearFile() {
    uploadedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('step2NextBtn').disabled = true;
}

function uploadFile() {
    if (!uploadedFile) return;

    document.getElementById('uploadProgress').style.display = 'block';
    document.getElementById('step2NextBtn').disabled = true;

    const formData = new FormData();
    formData.append('file', uploadedFile);

    fetch('{{ route("admin.rate-cards.parse-file") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('uploadProgress').style.display = 'none';
        document.getElementById('step2NextBtn').disabled = false;

        if (!data.success) {
            alert(data.message || 'Failed to parse file.');
            return;
        }

        importId = data.importId;
        parsedRows = data.preview;
        document.getElementById('parsedFileName').textContent = data.fileName;
        document.getElementById('parsedRowCount').textContent = data.totalRows;

        selectedHeaderRow = -1;
        document.getElementById('step3NextBtn').disabled = true;
        renderHeaderSelectionTable();
        goToStep(3);
    })
    .catch(err => {
        document.getElementById('uploadProgress').style.display = 'none';
        document.getElementById('step2NextBtn').disabled = false;
        alert('Error parsing file. Please check the file format.');
        console.error(err);
    });
}

function renderHeaderSelectionTable() {
    const wrap = document.getElementById('headerSelectionTable');
    if (!parsedRows.length) {
        wrap.innerHTML = '<p class="p-3 text-muted">No data found in file.</p>';
        return;
    }

    const maxCols = Math.max(...parsedRows.map(r => r.length));
    let html = '<table class="table table-bordered mb-0">';
    html += '<thead><tr><th style="width:60px">#</th>';
    for (let c = 0; c < maxCols; c++) {
        html += `<th>Col ${String.fromCharCode(65 + (c % 26))}${c >= 26 ? Math.floor(c / 26) : ''}</th>`;
    }
    html += '</tr></thead><tbody>';

    parsedRows.forEach((row, idx) => {
        const isHeader = idx === selectedHeaderRow;
        const isSkipped = selectedHeaderRow >= 0 && idx < selectedHeaderRow;
        let cls = 'row-selector';
        if (isHeader) cls += ' selected-header';
        else if (isSkipped) cls += ' skipped-row';

        html += `<tr class="${cls}" onclick="selectHeaderRow(${idx})" title="Click to set row ${idx + 1} as header">`;
        html += `<td><strong>${idx + 1}</strong>`;
        if (isHeader) html += ' <span class="header-badge">HEADER</span>';
        if (isSkipped) html += ' <span class="badge bg-secondary" style="font-size:0.6rem">skip</span>';
        html += '</td>';
        for (let c = 0; c < maxCols; c++) {
            const val = row[c] !== undefined && row[c] !== null ? row[c] : '';
            html += `<td title="${escapeHtml(String(val))}">${escapeHtml(String(val).substring(0, 40))}</td>`;
        }
        html += '</tr>';
    });

    html += '</tbody></table>';
    wrap.innerHTML = html;
}

function selectHeaderRow(idx) {
    selectedHeaderRow = idx;
    headerColumns = parsedRows[idx] || [];
    document.getElementById('step3NextBtn').disabled = false;
    renderHeaderSelectionTable();
}

function populateMappingDropdowns() {
    const selects = ['mapMcc', 'mapMnc', 'mapRate', 'mapCurrency', 'mapProductType', 'mapCountry', 'mapNetwork'];
    const requiredSelects = ['mapMcc', 'mapMnc', 'mapRate'];

    selects.forEach(id => {
        const sel = document.getElementById(id);
        const isRequired = requiredSelects.includes(id);
        const defaultLabel = isRequired ? '-- Select column --' : sel.options[0].text;
        sel.innerHTML = `<option value="">${defaultLabel}</option>`;

        headerColumns.forEach((col, idx) => {
            const label = col ? String(col).trim() : `Column ${idx + 1}`;
            sel.innerHTML += `<option value="${idx}">${label}</option>`;
        });
    });

    autoMapColumns();
    updateMappingNextBtn();
    renderMappingPreview();

    selects.forEach(id => {
        document.getElementById(id).addEventListener('change', () => {
            updateMappingNextBtn();
            renderMappingPreview();
        });
    });
}

function autoMapColumns() {
    const patterns = {
        mapMcc: [/^mcc$/i, /mcc/i, /mobile.country/i],
        mapMnc: [/^mnc$/i, /mnc/i, /mobile.network/i],
        mapRate: [/^rate$/i, /price/i, /cost/i, /charge/i],
        mapCurrency: [/^currency$/i, /ccy/i, /curr/i],
        mapProductType: [/^product.type$/i, /product/i, /type/i, /service/i],
        mapCountry: [/^country$/i, /country.name/i, /destination/i],
        mapNetwork: [/^network$/i, /operator/i, /carrier/i, /network.name/i],
    };

    Object.entries(patterns).forEach(([selectId, regexes]) => {
        const sel = document.getElementById(selectId);
        if (sel.value) return;

        for (const regex of regexes) {
            const matchIdx = headerColumns.findIndex(col => col && regex.test(String(col).trim()));
            if (matchIdx >= 0) {
                sel.value = String(matchIdx);
                break;
            }
        }
    });
}

function updateMappingNextBtn() {
    const mcc = document.getElementById('mapMcc').value;
    const mnc = document.getElementById('mapMnc').value;
    const rate = document.getElementById('mapRate').value;
    document.getElementById('step4NextBtn').disabled = !(mcc !== '' && mnc !== '' && rate !== '');
}

function renderMappingPreview() {
    const mccCol = document.getElementById('mapMcc').value;
    const mncCol = document.getElementById('mapMnc').value;
    const rateCol = document.getElementById('mapRate').value;
    const currCol = document.getElementById('mapCurrency').value;
    const prodCol = document.getElementById('mapProductType').value;

    if (mccCol === '' || mncCol === '' || rateCol === '') {
        document.getElementById('mappingPreviewTable').innerHTML = '<p class="p-3 text-muted">Map the required columns to see a preview.</p>';
        return;
    }

    const dataRows = parsedRows.slice(selectedHeaderRow + 1, selectedHeaderRow + 6);
    let html = '<table class="table table-sm table-bordered mb-0">';
    html += '<thead><tr><th>MCC</th><th>MNC</th><th>Rate</th>';
    if (currCol !== '') html += '<th>Currency</th>';
    if (prodCol !== '') html += '<th>Product</th>';
    html += '</tr></thead><tbody>';

    dataRows.forEach(row => {
        html += '<tr>';
        html += `<td>${escapeHtml(String(row[parseInt(mccCol)] || ''))}</td>`;
        html += `<td>${escapeHtml(String(row[parseInt(mncCol)] || ''))}</td>`;
        html += `<td>${escapeHtml(String(row[parseInt(rateCol)] || ''))}</td>`;
        if (currCol !== '') html += `<td>${escapeHtml(String(row[parseInt(currCol)] || ''))}</td>`;
        if (prodCol !== '') html += `<td>${escapeHtml(String(row[parseInt(prodCol)] || ''))}</td>`;
        html += '</tr>';
    });

    html += '</tbody></table>';
    document.getElementById('mappingPreviewTable').innerHTML = html;
}

function validateAndPreview() {
    const mapping = {
        mcc: parseInt(document.getElementById('mapMcc').value),
        mnc: parseInt(document.getElementById('mapMnc').value),
        rate: parseInt(document.getElementById('mapRate').value),
    };

    const currVal = document.getElementById('mapCurrency').value;
    if (currVal !== '') mapping.currency = parseInt(currVal);

    const prodVal = document.getElementById('mapProductType').value;
    if (prodVal !== '') mapping.product_type = parseInt(prodVal);

    const countryVal = document.getElementById('mapCountry').value;
    if (countryVal !== '') mapping.country_name = parseInt(countryVal);

    const networkVal = document.getElementById('mapNetwork').value;
    if (networkVal !== '') mapping.network_name = parseInt(networkVal);

    goToStep(5);

    document.getElementById('validationResults').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Validating rate data against MCC/MNC master reference...</p>
        </div>`;
    document.getElementById('importActions').style.display = 'none';

    fetch('{{ route("admin.rate-cards.validate-mapping") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            importId: importId,
            headerRow: selectedHeaderRow,
            mapping: mapping,
            gateway_id: selectedGatewayId,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('validationResults').innerHTML = `
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>${data.message}</div>`;
            document.getElementById('importActions').style.display = 'block';
            document.getElementById('confirmImportBtn').style.display = 'none';
            return;
        }

        validatedRates = data.rates;
        displayValidationResults(data);
    })
    .catch(err => {
        console.error(err);
        document.getElementById('validationResults').innerHTML = `
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Validation failed. Please try again.</div>`;
        document.getElementById('importActions').style.display = 'block';
        document.getElementById('confirmImportBtn').style.display = 'none';
    });
}

function displayValidationResults(data) {
    let html = '';

    html += '<div class="row mb-3">';
    html += `<div class="col-md-3"><div class="summary-card summary-stat"><div class="stat-value">${data.totalRows}</div><div class="stat-label">Total Data Rows</div></div></div>`;
    html += `<div class="col-md-3"><div class="summary-card summary-stat"><div class="stat-value text-success">${data.validRows}</div><div class="stat-label">Valid Rows</div></div></div>`;
    html += `<div class="col-md-3"><div class="summary-card summary-stat"><div class="stat-value" style="color:#4a90d9">${data.newRates}</div><div class="stat-label">New Rates</div></div></div>`;
    html += `<div class="col-md-3"><div class="summary-card summary-stat"><div class="stat-value" style="color:#e67e22">${data.updateRates}</div><div class="stat-label">Rate Updates</div></div></div>`;
    html += '</div>';

    if (data.errors && data.errors.length > 0) {
        html += `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i><strong>${data.errors.length} row(s) with errors</strong> (these will be skipped during import)</div>`;
        html += '<div class="error-list-wrap">';
        data.errors.slice(0, 50).forEach(err => {
            html += `<div class="validation-error"><strong>Row ${err.row}:</strong> ${err.errors.join(', ')}`;
            if (err.data) html += ` <span class="text-muted">(MCC: ${err.data.mcc || '-'}, MNC: ${err.data.mnc || '-'})</span>`;
            html += '</div>';
        });
        if (data.errors.length > 50) {
            html += `<div class="text-muted p-2">... and ${data.errors.length - 50} more errors</div>`;
        }
        html += '</div>';
    }

    if (data.validRows > 0) {
        html += '<div class="validation-success mb-3"><i class="fas fa-check-circle me-2"></i>';
        html += `<strong>${data.validRows} rate(s)</strong> ready to import`;
        html += '</div>';

        html += '<h6 class="mt-3 mb-2">Preview (first 20 rows)</h6>';
        html += '<div class="preview-table-wrap"><table class="table table-sm table-bordered mb-0">';
        html += '<thead><tr><th>MCC</th><th>MNC</th><th>Country</th><th>Network</th><th>Rate</th><th>Currency</th><th>Product</th></tr></thead>';
        html += '<tbody>';
        data.preview.forEach(row => {
            html += `<tr>
                <td><code>${row.mcc}</code></td>
                <td><code>${row.mnc}</code></td>
                <td>${escapeHtml(row.country_name || '')}</td>
                <td>${escapeHtml(row.network_name || '')}</td>
                <td>${row.rate}</td>
                <td>${row.currency}</td>
                <td><span class="badge bg-secondary">${row.product_type}</span></td>
            </tr>`;
        });
        html += '</tbody></table></div>';

        document.getElementById('confirmImportBtn').style.display = '';
    } else {
        html += '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>No valid rows to import. Please fix the errors and try again.</div>';
        document.getElementById('confirmImportBtn').style.display = 'none';
    }

    document.getElementById('validationResults').innerHTML = html;
    document.getElementById('importActions').style.display = 'block';
}

function confirmImport() {
    if (!validatedRates || !validatedRates.length) return;

    const btn = document.getElementById('confirmImportBtn');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importing...';

    fetch('{{ route("admin.rate-cards.process-upload") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            gateway_id: selectedGatewayId,
            valid_from: document.getElementById('validFromDate').value,
            rates: validatedRates
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('validationResults').innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4 class="text-success">Import Complete</h4>
                    <p class="text-muted">${data.message}</p>
                    <div class="row justify-content-center mt-3">
                        <div class="col-auto"><div class="summary-card summary-stat"><div class="stat-value text-success">${data.imported}</div><div class="stat-label">New Rates</div></div></div>
                        <div class="col-auto"><div class="summary-card summary-stat"><div class="stat-value" style="color:#e67e22">${data.updated}</div><div class="stat-label">Updated</div></div></div>
                    </div>
                    <a href="{{ route('admin.rate-cards.index') }}" class="btn btn-admin-primary mt-3"><i class="fas fa-arrow-left me-2"></i>Back to Rate Cards</a>
                </div>`;
            document.getElementById('importActions').style.display = 'none';
        } else {
            alert(data.message || 'Import failed.');
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Import failed. Please try again.');
        btn.disabled = false;
        btn.innerHTML = origText;
    });
}

function goToStep(step) {
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.step-item').forEach(el => {
        el.classList.remove('active');
        el.classList.remove('completed');
    });

    for (let i = 1; i < step; i++) {
        document.getElementById(`stepIndicator${i}`).classList.add('completed');
    }

    document.getElementById(`step${step}`).classList.add('active');
    document.getElementById(`stepIndicator${step}`).classList.add('active');

    if (step === 4) {
        populateMappingDropdowns();
    }
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

const dropzone = document.getElementById('dropzone');

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
});

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('fileInput').files = files;
        handleFileSelect({ target: { files: files } });
    }
});
</script>
@endpush
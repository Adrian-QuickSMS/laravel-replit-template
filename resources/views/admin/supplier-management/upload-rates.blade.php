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
    font-size: 0.875rem;
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

.preview-table {
    max-height: 400px;
    overflow-y: auto;
}

.validation-error {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}

.validation-success {
    background: #d1e7dd;
    border-left: 4px solid #198754;
    padding: 0.75rem;
}

.summary-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
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
    <!-- Step Indicator -->
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
            <div class="step-label">Validate Data</div>
        </div>
        <div class="step-item" id="stepIndicator4">
            <div class="step-circle">4</div>
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
                <button class="btn btn-admin-primary btn-lg mt-3" onclick="nextStep(2)" disabled id="step1NextBtn">
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
                <div class="alert alert-info">
                    <strong>Required CSV Format:</strong>
                    <code>mcc,mnc,rate,currency,product_type</code>
                    <br><small>Example: 234,10,0.0350,GBP,SMS</small>
                </div>

                <div class="upload-dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h5>Drop your file here or click to browse</h5>
                    <p class="text-muted mb-0">Supports CSV and Excel (.xlsx) files up to 10MB</p>
                    <input type="file" id="fileInput" accept=".csv,.xlsx" style="display: none" onchange="handleFileSelect(event)">
                </div>

                <div id="fileInfo" class="mt-3" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div>
                            <i class="fas fa-file-csv fa-2x text-success me-3"></i>
                            <span id="fileName"></span>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-secondary" onclick="previousStep(1)">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </button>
                    <button class="btn btn-admin-primary ms-2" onclick="uploadAndValidate()" disabled id="step2NextBtn">
                        Validate File <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Validate Data -->
    <div class="wizard-step" id="step3">
        <h4 class="mb-4">Step 3: Validation Results</h4>
        <div id="validationResults">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Validating...</span>
                </div>
                <p class="mt-3 text-muted">Validating rate data...</p>
            </div>
        </div>
        <div id="validationActions" style="display: none;">
            <button class="btn btn-secondary" onclick="previousStep(2)">
                <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <button class="btn btn-admin-primary ms-2" onclick="nextStep(4)" id="step3NextBtn">
                Continue to Import <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

    <!-- Step 4: Confirm Import -->
    <div class="wizard-step" id="step4">
        <h4 class="mb-4">Step 4: Confirm Import</h4>
        <div id="importSummary"></div>
        <div class="mt-4">
            <button class="btn btn-secondary" onclick="previousStep(3)">
                <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <button class="btn btn-success btn-lg ms-2" onclick="confirmImport()">
                <i class="fas fa-check me-2"></i>Import Rate Cards
            </button>
        </div>
    </div>
</div>

<!-- Download Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download CSV Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Download a pre-formatted CSV template to ensure your data is correctly structured:</p>
                <a href="/downloads/rate-card-template.csv" class="btn btn-admin-primary" download>
                    <i class="fas fa-download me-2"></i>Download Template
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let uploadedFile = null;
let validatedData = null;
let selectedGatewayId = null;

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
        .then(response => response.json())
        .then(gateways => {
            gatewaySelect.innerHTML = '<option value="">Choose gateway...</option>';
            gateways.forEach(gateway => {
                gatewaySelect.innerHTML += `<option value="${gateway.id}">${gateway.name} (${gateway.gateway_code})</option>`;
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
    document.getElementById('fileName').textContent = uploadedFile.name;
    document.getElementById('fileInfo').style.display = 'block';
    document.getElementById('step2NextBtn').disabled = false;
}

function clearFile() {
    uploadedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('step2NextBtn').disabled = true;
}

function uploadAndValidate() {
    if (!uploadedFile) return;

    const formData = new FormData();
    formData.append('file', uploadedFile);
    formData.append('gateway_id', selectedGatewayId);
    formData.append('valid_from', document.getElementById('validFromDate').value);

    nextStep(3);

    fetch('{{ route('admin.rate-cards.validate') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        validatedData = data;
        displayValidationResults(data);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Validation failed. Please check your file format.');
    });
}

function displayValidationResults(data) {
    const resultsDiv = document.getElementById('validationResults');
    let html = '';

    if (data.errors && data.errors.length > 0) {
        html += '<div class="alert alert-danger"><strong>Validation Errors Found:</strong></div>';
        data.errors.forEach(error => {
            html += `<div class="validation-error">${error}</div>`;
        });
        document.getElementById('step3NextBtn').disabled = true;
    } else {
        html += '<div class="validation-success mb-3">';
        html += '<i class="fas fa-check-circle me-2"></i>';
        html += `<strong>Validation Successful!</strong> ${data.total_rows} rate cards ready to import`;
        html += '</div>';

        html += '<div class="summary-card">';
        html += '<h6>Import Summary:</h6>';
        html += `<p class="mb-1">Total Rows: <strong>${data.total_rows}</strong></p>`;
        html += `<p class="mb-1">New Rates: <strong>${data.new_rates}</strong></p>`;
        html += `<p class="mb-0">Rate Updates: <strong>${data.updated_rates}</strong></p>`;
        html += '</div>';

        html += '<div class="preview-table">';
        html += '<table class="table table-sm">';
        html += '<thead><tr><th>MCC/MNC</th><th>Network</th><th>Rate</th><th>Currency</th><th>Product</th></tr></thead>';
        html += '<tbody>';
        data.preview.forEach(row => {
            html += `<tr>
                <td><code>${row.mcc}/${row.mnc}</code></td>
                <td>${row.network_name}</td>
                <td>${row.rate}</td>
                <td>${row.currency}</td>
                <td>${row.product_type}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        html += '</div>';

        document.getElementById('step3NextBtn').disabled = false;
    }

    resultsDiv.innerHTML = html;
    document.getElementById('validationActions').style.display = 'block';
}

function confirmImport() {
    if (!validatedData) return;

    if (confirm(`Are you sure you want to import ${validatedData.total_rows} rate cards?`)) {
        fetch('{{ route('admin.rate-cards.process') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                gateway_id: selectedGatewayId,
                valid_from: document.getElementById('validFromDate').value,
                rates: validatedData.rates
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rate cards imported successfully!');
                window.location.href = '{{ route('admin.rate-cards.index') }}';
            }
        });
    }
}

function nextStep(step) {
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.step-item').forEach(el => el.classList.remove('active'));

    for (let i = 1; i < step; i++) {
        document.getElementById(`stepIndicator${i}`).classList.add('completed');
    }

    document.getElementById(`step${step}`).classList.add('active');
    document.getElementById(`stepIndicator${step}`).classList.add('active');
}

function previousStep(step) {
    nextStep(step);
}

// Drag and drop handlers
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
        uploadedFile = files[0];
        document.getElementById('fileInput').files = files;
        handleFileSelect({ target: { files: files } });
    }
});
</script>
@endpush

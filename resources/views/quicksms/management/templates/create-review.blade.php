@extends('layouts.quicksms')

@section('title', 'Create Template - Review')

@push('styles')
<style>
.form-wizard {
    border: 0;
}
.form-wizard .nav-wizard {
    box-shadow: none !important;
    margin-bottom: 2rem;
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
}
.form-wizard .nav-wizard li {
    flex: 1;
    max-width: 150px;
}
.form-wizard .nav-wizard li .nav-link {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #6c757d;
    padding: 0;
    background: transparent !important;
    border: none !important;
}
.form-wizard .nav-wizard li .nav-link span {
    border-radius: 3.125rem;
    width: 3rem;
    height: 3rem;
    border: 0.125rem solid var(--primary, #886CC0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: var(--primary, #886CC0);
    position: relative;
    z-index: 1;
}
.form-wizard .nav-wizard li .nav-link:after {
    position: absolute;
    top: 1.5rem;
    left: 50%;
    height: 0.1875rem;
    background: #e9ecef;
    content: "";
    z-index: 0;
    width: 100%;
}
.form-wizard .nav-wizard li:last-child .nav-link:after {
    content: none;
}
.form-wizard .nav-wizard li .nav-link.active span,
.form-wizard .nav-wizard li .nav-link.done span {
    background: var(--primary, #886CC0);
    color: #fff;
    border-color: var(--primary, #886CC0);
}
.form-wizard .nav-wizard li .nav-link.active:after,
.form-wizard .nav-wizard li .nav-link.done:after {
    background: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link small {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.75rem;
}
.form-wizard .nav-wizard li .nav-link.active small {
    color: var(--primary, #886CC0);
    font-weight: 600;
}
.toolbar-bottom {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 1.5rem 0 0 0;
    border-top: 1px solid #e9ecef;
    margin-top: 2rem;
}
.form-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.alert-pastel-primary {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.2);
    color: #614099;
}
.review-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.review-section h6 {
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.review-section h6 a {
    font-size: 0.8rem;
    font-weight: 400;
}
.review-row {
    display: flex;
    margin-bottom: 0.5rem;
}
.review-label {
    width: 140px;
    color: #6c757d;
    font-size: 0.85rem;
}
.review-value {
    flex: 1;
    font-size: 0.85rem;
}
.message-preview {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    white-space: pre-wrap;
    font-family: inherit;
    font-size: 0.9rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management.templates') }}">Templates</a></li>
            <li class="breadcrumb-item active">Create Template</li>
        </ol>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Create Message Template</h4>
                </div>
                <div class="card-body">
                    <div class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link done" href="#step-1"><span><i class="fas fa-check"></i></span><small>Metadata</small></a></li>
                            <li class="nav-item"><a class="nav-link done" href="#step-2"><span><i class="fas fa-check"></i></span><small>Content</small></a></li>
                            <li class="nav-item"><a class="nav-link done" href="#step-3"><span><i class="fas fa-check"></i></span><small>Settings</small></a></li>
                            <li class="nav-item"><a class="nav-link active" href="#step-4"><span>4</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="row">
                            <div class="col-lg-10 mx-auto">
                                <div class="alert alert-pastel-primary mb-4">
                                    <strong>Step 4: Review</strong> - Review your template before saving. Click "Edit" to make changes.
                                </div>

                                <div class="review-section">
                                    <h6>
                                        <span><i class="fas fa-info-circle me-2"></i>Metadata</span>
                                        <a href="{{ route('management.templates.create.step1') }}"><i class="fas fa-edit me-1"></i>Edit</a>
                                    </h6>
                                    <div class="review-row">
                                        <span class="review-label">Name:</span>
                                        <span class="review-value" id="reviewName">-</span>
                                    </div>
                                    <div class="review-row">
                                        <span class="review-label">Type:</span>
                                        <span class="review-value" id="reviewType">-</span>
                                    </div>
                                    <div class="review-row">
                                        <span class="review-label">Description:</span>
                                        <span class="review-value" id="reviewDescription">-</span>
                                    </div>
                                </div>

                                <div class="review-section">
                                    <h6>
                                        <span><i class="fas fa-envelope me-2"></i>Content</span>
                                        <a href="{{ route('management.templates.create.step2') }}"><i class="fas fa-edit me-1"></i>Edit</a>
                                    </h6>
                                    <div class="review-row">
                                        <span class="review-label">Channel:</span>
                                        <span class="review-value" id="reviewChannel">-</span>
                                    </div>
                                    <div class="mt-2">
                                        <div class="review-label mb-1">Message:</div>
                                        <div class="message-preview" id="reviewMessage">-</div>
                                    </div>
                                </div>

                                <div class="review-section">
                                    <h6>
                                        <span><i class="fas fa-cog me-2"></i>Settings</span>
                                        <a href="{{ route('management.templates.create.step3') }}"><i class="fas fa-edit me-1"></i>Edit</a>
                                    </h6>
                                    <div class="review-row">
                                        <span class="review-label">Assigned Sub-Accounts:</span>
                                        <span class="review-value" id="reviewSubAccounts">-</span>
                                    </div>
                                    <div class="review-row">
                                        <span class="review-label">Assigned Users:</span>
                                        <span class="review-value" id="reviewUsers">-</span>
                                    </div>
                                </div>

                                <div class="toolbar-bottom">
                                    <a href="{{ route('management.templates.create.step3') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Back
                                    </a>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary me-2" id="saveDraftBtn">
                                            <i class="fas fa-save me-1"></i>Save as Draft
                                        </button>
                                        <button type="button" class="btn btn-primary" id="createTemplateBtn">
                                            <i class="fas fa-check me-1"></i>Create Template
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Created Success Modal -->
<div class="modal fade" id="templateSuccessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><i class="fas fa-check-circle text-success me-2"></i>Template Created Successfully</h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="success-icon-circle mb-3">
                        <i class="fas fa-file-alt fa-3x text-primary"></i>
                    </div>
                    <p class="text-muted mb-0">Your template has been created and is ready to use.</p>
                </div>
                
                <div class="template-id-box p-3 rounded mb-3" style="background: #f8f9fa; border: 1px dashed #886CC0;">
                    <label class="form-label small fw-bold mb-2">Template ID (for API use)</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="generatedTemplateId" readonly style="font-family: monospace; font-size: 0.9rem;">
                        <button class="btn btn-outline-primary" type="button" id="copyTemplateIdBtn" title="Copy to clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">Use this ID when sending messages via the API with <code>template_id</code> parameter.</small>
                </div>
                
                <div class="alert small mb-0" style="background: rgba(136, 108, 192, 0.1); border: 1px solid rgba(136, 108, 192, 0.3); color: #614099;">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>API Usage Example:</strong>
                    <pre class="mb-0 mt-2" style="font-size: 0.75rem; background: #fff; padding: 0.5rem; border-radius: 4px; overflow-x: auto; color: #333;">{
  "template_id": "<span id="templateIdExample">TPL-XXXXXXXX</span>",
  "recipients": ["+447123456789"],
  "variables": { "name": "John" }
}</pre>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="{{ route('management.templates') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i>View All Templates
                </a>
                <button type="button" class="btn btn-primary" id="createAnotherBtn">
                    <i class="fas fa-plus me-1"></i>Create Another
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
#templateSuccessModal .modal-content {
    border: none;
    border-radius: 0.75rem;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var step1 = sessionStorage.getItem('templateWizardStep1');
    var step2 = sessionStorage.getItem('templateWizardStep2');
    var step3 = sessionStorage.getItem('templateWizardStep3');
    
    if (step1) {
        var data1 = JSON.parse(step1);
        document.getElementById('reviewName').textContent = data1.name || '-';
        document.getElementById('reviewType').textContent = data1.type === 'api' ? 'API Template' : 'Portal Template';
        document.getElementById('reviewDescription').textContent = data1.description || 'No description';
    }
    
    if (step2) {
        var data2 = JSON.parse(step2);
        var channelMap = { 'sms': 'SMS only', 'rcs_basic': 'Basic RCS + SMS Fallback', 'rcs_rich': 'Rich RCS + SMS Fallback' };
        document.getElementById('reviewChannel').textContent = channelMap[data2.channel] || data2.channel;
        document.getElementById('reviewMessage').textContent = data2.smsText || 'No content';
    }
    
    if (step3) {
        var data3 = JSON.parse(step3);
        
        // Display sub-accounts
        if (data3.subAccounts && data3.subAccounts.length > 0) {
            document.getElementById('reviewSubAccounts').textContent = data3.subAccounts.length + ' sub-account(s) selected';
        } else {
            document.getElementById('reviewSubAccounts').textContent = 'None selected';
        }
        
        // Display users
        if (data3.users && data3.users.length > 0) {
            var userNames = data3.users.map(function(u) { return u.name; }).join(', ');
            document.getElementById('reviewUsers').textContent = userNames;
        } else {
            document.getElementById('reviewUsers').textContent = 'None selected';
        }
    }
    
    // Generate unique template ID
    function generateTemplateId() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var id = 'TPL-';
        for (var i = 0; i < 8; i++) {
            id += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return id;
    }
    
    document.getElementById('createTemplateBtn').addEventListener('click', function() {
        // Generate template ID
        var templateId = generateTemplateId();
        
        // Clear session storage
        sessionStorage.removeItem('templateWizardStep1');
        sessionStorage.removeItem('templateWizardStep2');
        sessionStorage.removeItem('templateWizardStep3');
        sessionStorage.removeItem('templateWizardChannel');
        
        // Display the template ID in the modal
        document.getElementById('generatedTemplateId').value = templateId;
        document.getElementById('templateIdExample').textContent = templateId;
        
        // Show the success modal
        var successModal = new bootstrap.Modal(document.getElementById('templateSuccessModal'));
        successModal.show();
    });
    
    // Copy template ID to clipboard
    document.getElementById('copyTemplateIdBtn').addEventListener('click', function() {
        var templateIdInput = document.getElementById('generatedTemplateId');
        templateIdInput.select();
        templateIdInput.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(templateIdInput.value).then(function() {
            var btn = document.getElementById('copyTemplateIdBtn');
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        });
    });
    
    // Create another template
    document.getElementById('createAnotherBtn').addEventListener('click', function() {
        window.location.href = '{{ route("management.templates.create.step1") }}';
    });
    
    document.getElementById('saveDraftBtn').addEventListener('click', function() {
        alert('Template saved as draft.');
        window.location.href = '{{ route("management.templates") }}';
    });
});
</script>
@endpush

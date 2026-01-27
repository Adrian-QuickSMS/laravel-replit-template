@extends(isset($isAdminMode) && $isAdminMode ? 'layouts.admin' : 'layouts.quicksms')

@section('title', 'Create Template - Metadata')

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
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1.5rem 0 0 0;
    border-top: 1px solid #e9ecef;
    margin-top: 2rem;
}
.toolbar-bottom .btn-back {
    background: #a894d4 !important;
    color: #fff !important;
    border: none !important;
    font-weight: 500;
}
.toolbar-bottom .btn-back:hover {
    background: #9783c7 !important;
}
.toolbar-bottom .btn-save-draft {
    background-color: #fff !important;
    color: #D653C1 !important;
    border: 1px solid #D653C1 !important;
    font-weight: 500;
}
.toolbar-bottom .btn-save-draft:hover {
    background-color: rgba(214, 83, 193, 0.08) !important;
}
.selectable-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
    background: #fff;
}
.selectable-tile:hover {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.05);
}
.selectable-tile.selected {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.1);
}
.selectable-tile .tile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}
.selectable-tile .tile-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.selectable-tile .tile-check {
    color: #886CC0;
    font-size: 1.25rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.selectable-tile.selected .tile-check {
    opacity: 1;
}
.selectable-tile .tile-title {
    margin-bottom: 0.25rem;
    font-weight: 600;
    font-size: 1rem;
}
.selectable-tile .tile-desc {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0;
}
.bg-pastel-primary { background: rgba(136, 108, 192, 0.15); color: #886CC0; }
.bg-pastel-info { background: rgba(23, 162, 184, 0.15); color: #117a8b; }
.alert-pastel-primary {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.2);
    color: #614099;
}
.selectable-tile.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}
.selectable-tile.disabled:hover {
    transform: none;
    box-shadow: none;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management.templates') }}">Templates</a></li>
            <li class="breadcrumb-item active">{{ $isEditMode ? 'Edit Template' : 'Create Template' }}</li>
        </ol>
    </div>

    @if(isset($isAdminMode) && $isAdminMode)
    <div class="alert alert-warning mb-3">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Admin Mode:</strong> You are editing a template belonging to <strong>{{ $account['name'] ?? 'Unknown Account' }}</strong>. Changes will affect the customer's account.
    </div>
    @endif

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fas fa-{{ $isEditMode ? 'edit' : 'file-alt' }} me-2 text-primary"></i>{{ $isEditMode ? 'Edit Message Template' : 'Create Message Template' }}</h4>
                </div>
                <div class="card-body">
                    <div class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link active" href="#step-1"><span>1</span><small>Metadata</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-2"><span>2</span><small>Content</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-3"><span>3</span><small>Settings</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-4"><span>4</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="row">
                            <div class="col-lg-10 mx-auto">
                                <div class="alert alert-pastel-primary mb-4">
                                    <strong>Step 1: Metadata</strong> - Define your template's name, type, and basic details.
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold mb-3">Template Type <span class="text-danger">*</span></label>
                                    @if($isEditMode)
                                    <small class="text-muted d-block mb-2"><i class="fas fa-lock me-1"></i>Template type cannot be changed after creation.</small>
                                    @endif
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="selectable-tile {{ ($isEditMode && $template['trigger'] == 'portal') || (!$isEditMode) ? 'selected' : '' }} {{ $isEditMode ? 'disabled' : '' }}" data-value="portal" {{ $isEditMode ? '' : 'onclick=selectTemplateType(\'portal\')' }}>
                                                <div class="tile-header">
                                                    <div class="tile-icon bg-pastel-primary">
                                                        <i class="fas fa-desktop"></i>
                                                    </div>
                                                    <i class="fas fa-check-circle tile-check"></i>
                                                </div>
                                                <div class="tile-title">Portal Template</div>
                                                <p class="tile-desc">Use from the QuickSMS portal. Ideal for manual sending by your team.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="selectable-tile {{ ($isEditMode && $template['trigger'] == 'api') ? 'selected' : '' }} {{ $isEditMode ? 'disabled' : '' }}" data-value="api" {{ $isEditMode ? '' : 'onclick=selectTemplateType(\'api\')' }}>
                                                <div class="tile-header">
                                                    <div class="tile-icon bg-pastel-info">
                                                        <i class="fas fa-code"></i>
                                                    </div>
                                                    <i class="fas fa-check-circle tile-check"></i>
                                                </div>
                                                <div class="tile-title">API Template</div>
                                                <p class="tile-desc">Triggered via API integration. Ideal for automated workflows.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="templateType" value="{{ $isEditMode ? $template['trigger'] : 'portal' }}">
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="templateName" placeholder="e.g., Welcome Message" maxlength="100" value="{{ $isEditMode && $template ? $template['name'] : '' }}">
                                        <small class="text-muted">A descriptive name to identify this template</small>
                                        <div class="invalid-feedback">Please enter a template name</div>
                                    </div>
                                    
                                    <div class="col-lg-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" id="templateDescription" rows="2" placeholder="Optional description of what this template is for..." maxlength="255">{{ $isEditMode && $template ? ($template['description'] ?? '') : '' }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="toolbar-bottom">
                                    <a href="{{ route('management.templates') }}" class="btn btn-back">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </a>
                                    <a href="@if($isEditMode){{ isset($isAdminMode) && $isAdminMode ? route('admin.management.templates.edit.step2', ['accountId' => $accountId, 'templateId' => $templateId]) : route('management.templates.edit.step2', ['templateId' => $templateId]) }}@else{{ route('management.templates.create.step2') }}@endif" class="btn btn-primary" id="nextBtn">
                                        Next: Content <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectTemplateType(type) {
    document.querySelectorAll('.selectable-tile').forEach(function(tile) {
        tile.classList.remove('selected');
    });
    document.querySelector('.selectable-tile[data-value="' + type + '"]').classList.add('selected');
    document.getElementById('templateType').value = type;
}

document.addEventListener('DOMContentLoaded', function() {
    var isEditMode = {{ $isEditMode ? 'true' : 'false' }};
    
    if (isEditMode) {
        // In Edit mode, clear any cached Create data and load from template
        sessionStorage.removeItem('templateWizardStep1');
        sessionStorage.removeItem('templateWizardStep2');
        sessionStorage.removeItem('templateWizardStep3');
        sessionStorage.removeItem('templateWizardChannel');
        
        // Pre-populate from template data
        @if($isEditMode && $template)
        document.getElementById('templateName').value = '{{ $template['name'] ?? '' }}';
        document.getElementById('templateDescription').value = '{{ $template['description'] ?? '' }}';
        var templateType = '{{ $template['trigger'] ?? 'portal' }}';
        selectTemplateType(templateType);
        @endif
    } else {
        // In Create mode, restore from sessionStorage
        var savedData = sessionStorage.getItem('templateWizardStep1');
        if (savedData) {
            var data = JSON.parse(savedData);
            document.getElementById('templateName').value = data.name || '';
            document.getElementById('templateDescription').value = data.description || '';
            if (data.type) {
                selectTemplateType(data.type);
            }
        }
    }

    document.getElementById('nextBtn').addEventListener('click', function(e) {
        var name = document.getElementById('templateName').value.trim();
        if (!name) {
            e.preventDefault();
            document.getElementById('templateName').classList.add('is-invalid');
            document.getElementById('templateName').focus();
            return;
        }

        sessionStorage.setItem('templateWizardStep1', JSON.stringify({
            name: name,
            description: document.getElementById('templateDescription').value.trim(),
            type: document.getElementById('templateType').value
        }));
    });

    document.getElementById('templateName').addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>
@endpush

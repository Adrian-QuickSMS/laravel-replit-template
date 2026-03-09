@extends(isset($isAdminMode) && $isAdminMode ? 'layouts.admin' : 'layouts.quicksms')

@section('title', 'Create Template - Settings')

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
.settings-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.settings-section h6 {
    margin-bottom: 1rem;
    font-weight: 600;
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
                            <li class="nav-item"><a class="nav-link done" href="#step-1"><span><i class="fas fa-check"></i></span><small>Metadata</small></a></li>
                            <li class="nav-item"><a class="nav-link done" href="#step-2"><span><i class="fas fa-check"></i></span><small>Content</small></a></li>
                            <li class="nav-item"><a class="nav-link active" href="#step-3"><span>3</span><small>Settings</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-4"><span>4</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="row">
                            <div class="col-lg-10 mx-auto">
                                <div class="alert alert-pastel-primary mb-4">
                                    <strong>Step 3: Settings</strong> - Configure access control for this template.
                                </div>
                                
                                <div class="settings-section">
                                    <h6><i class="fas fa-sitemap me-2"></i>Sub-Account Assignment</h6>
                                    <p class="small text-muted mb-3">Assign this template to a specific sub-account, or keep it available at the main account level.</p>

                                    <div class="mb-3">
                                        <label class="form-label">Assign to Sub-Account</label>
                                        <select class="form-select" id="templateSubAccount">
                                            <option value="">All sub-accounts (main account level)</option>
                                            @foreach($sub_accounts ?? [] as $sa)
                                                <option value="{{ $sa['id'] }}">{{ $sa['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted mt-1 d-block">Templates assigned to a sub-account will only be visible to users within that sub-account. Leave unassigned for account-wide availability.</small>
                                    </div>
                                </div>

                                <div class="settings-section">
                                    <h6><i class="fas fa-user-shield me-2"></i>Access Control</h6>
                                    <p class="small text-muted mb-3">Control who can use this template within your account.</p>

                                    <div class="mb-3">
                                        <label class="form-label">Visibility</label>
                                        <select class="form-select" id="templateVisibility">
                                            <option value="all_users" selected>All users on this account</option>
                                            <option value="owner_only">Only me (template owner)</option>
                                        </select>
                                        <small class="text-muted mt-1 d-block">Choose who can see and use this template when composing messages.</small>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="allowEditing">
                                        <label class="form-check-label" for="allowEditing">
                                            Allow other users to edit this template
                                        </label>
                                    </div>
                                </div>

                                <div class="toolbar-bottom">
                                    <a href="@if($isEditMode){{ isset($isAdminMode) && $isAdminMode ? route('admin.management.templates.edit.step2', ['accountId' => $accountId, 'templateId' => $templateId]) : route('management.templates.edit.step2', ['templateId' => $templateId]) }}@else{{ route('management.templates.create.step2') }}@endif" class="btn btn-back">
                                        <i class="fas fa-arrow-left me-1"></i>Back
                                    </a>
                                    <button type="button" class="btn btn-save-draft" id="saveDraftBtn">
                                        <i class="fas fa-save me-1"></i>Save Draft
                                    </button>
                                    <a href="@if($isEditMode){{ isset($isAdminMode) && $isAdminMode ? route('admin.management.templates.edit.review', ['accountId' => $accountId, 'templateId' => $templateId]) : route('management.templates.edit.review', ['templateId' => $templateId]) }}@else{{ route('management.templates.create.review') }}@endif" class="btn btn-primary" id="nextBtn">
                                        Next: Review <i class="fas fa-arrow-right ms-1"></i>
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
document.addEventListener('DOMContentLoaded', function() {
    var isEditMode = {{ $isEditMode ? 'true' : 'false' }};

    if (isEditMode) {
        @if($isEditMode && $template)
        document.getElementById('templateVisibility').value = '{{ $template['visibility'] ?? 'all_users' }}';
        document.getElementById('allowEditing').checked = {{ ($template['allowEditing'] ?? false) ? 'true' : 'false' }};
        document.getElementById('templateSubAccount').value = '{{ $template['sub_account_id'] ?? '' }}';
        @endif
    } else {
        var savedData = sessionStorage.getItem('templateWizardStep3');
        if (savedData) {
            try {
                var data = JSON.parse(savedData);
                if (data.visibility) {
                    document.getElementById('templateVisibility').value = data.visibility;
                }
                if (data.sub_account_id) {
                    document.getElementById('templateSubAccount').value = data.sub_account_id;
                }
                document.getElementById('allowEditing').checked = data.allowEditing || false;
            } catch(e) {}
        }
    }

    document.getElementById('nextBtn').addEventListener('click', function() {
        sessionStorage.setItem('templateWizardStep3', JSON.stringify({
            visibility: document.getElementById('templateVisibility').value,
            allowEditing: document.getElementById('allowEditing').checked,
            sub_account_id: document.getElementById('templateSubAccount').value || null
        }));
    });
});
</script>
@endpush

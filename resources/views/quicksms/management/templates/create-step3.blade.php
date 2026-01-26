@extends('layouts.quicksms')

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
.multiselect-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 42px;
    background-color: #ffffff !important;
    cursor: pointer;
    align-items: center;
}
.multiselect-tags:focus-within {
    border-color: var(--primary, #886CC0);
    box-shadow: 0 0 0 0.2rem rgba(136, 108, 192, 0.15);
}
.multiselect-tags .tag {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    background: rgba(136, 108, 192, 0.15);
    color: var(--primary, #886CC0);
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}
.multiselect-tags .tag .remove-tag {
    cursor: pointer;
    opacity: 0.7;
    font-size: 0.7rem;
}
.multiselect-tags .tag .remove-tag:hover {
    opacity: 1;
}
.multiselect-tags .multiselect-placeholder {
    color: #6c757d;
    font-size: 0.875rem;
    background: transparent !important;
    opacity: 1 !important;
}
.multiselect-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1050;
    display: none;
}
.multiselect-dropdown-menu.show {
    display: block;
}
.multiselect-dropdown-menu .search-box {
    padding: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.multiselect-dropdown-menu .search-box input {
    width: 100%;
    padding: 0.35rem 0.5rem;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    font-size: 0.85rem;
}
.multiselect-dropdown-menu .options-list {
    padding: 0.25rem 0;
}
.multiselect-dropdown-menu .option-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.multiselect-dropdown-menu .option-item:hover {
    background: #f8f9fa;
}
.multiselect-dropdown-menu .option-item.selected {
    background: rgba(136, 108, 192, 0.1);
}
.multiselect-dropdown-menu .option-item .check-icon {
    width: 16px;
    height: 16px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    color: #fff;
}
.multiselect-dropdown-menu .option-item.selected .check-icon {
    background: var(--primary, #886CC0);
    border-color: var(--primary, #886CC0);
}
.multiselect-dropdown-menu .no-results {
    padding: 0.75rem;
    text-align: center;
    color: #6c757d;
    font-size: 0.85rem;
}
.multiselect-wrapper {
    position: relative;
}
.user-option-detail {
    display: flex;
    flex-direction: column;
}
.user-option-detail .user-name {
    font-weight: 500;
}
.user-option-detail .user-email {
    font-size: 0.75rem;
    color: #6c757d;
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
                                    <h6><i class="fas fa-user-shield me-2"></i>Access Control</h6>
                                    <p class="small text-muted mb-3">Assign this template to sub-accounts and select which users can access it.</p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Assign to Sub-Account(s)</label>
                                        <div class="multiselect-wrapper" id="subAccountsWrapper">
                                            <div class="multiselect-tags" id="subAccountsTags" tabindex="0" style="background-color: #fff !important;">
                                                <span class="multiselect-placeholder">Select sub-accounts...</span>
                                            </div>
                                            <div class="multiselect-dropdown-menu" id="subAccountsDropdown">
                                                <div class="search-box">
                                                    <input type="text" id="subAccountsSearch" placeholder="Search sub-accounts...">
                                                </div>
                                                <div class="options-list" id="subAccountsList"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="selectedSubAccounts" name="selectedSubAccounts">
                                    </div>
                                    
                                    <div class="mb-3" id="usersSelectionContainer" style="display: none;">
                                        <label class="form-label">Assign to User(s)</label>
                                        <div class="multiselect-wrapper" id="usersWrapper">
                                            <div class="multiselect-tags" id="usersTags" tabindex="0" style="background-color: #fff !important;">
                                                <span class="multiselect-placeholder">Select users from sub-accounts...</span>
                                            </div>
                                            <div class="multiselect-dropdown-menu" id="usersDropdown">
                                                <div class="search-box">
                                                    <input type="text" id="usersSearch" placeholder="Search users...">
                                                </div>
                                                <div class="options-list" id="usersList"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="selectedUsers" name="selectedUsers">
                                        <small class="text-muted">Users from selected sub-accounts will appear here.</small>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="allowEditing">
                                        <label class="form-check-label" for="allowEditing">
                                            Allow assigned users to edit this template
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
    // Mock data for sub-accounts - TODO: Replace with API call
    const subAccountsData = [
        { id: 'sa-001', name: 'Marketing Department' },
        { id: 'sa-002', name: 'Customer Support' },
        { id: 'sa-003', name: 'Sales Team' },
        { id: 'sa-004', name: 'HR & Recruitment' },
        { id: 'sa-005', name: 'Operations' }
    ];

    // Mock data for users per sub-account - TODO: Replace with API call
    const usersData = {
        'sa-001': [
            { id: 'u-001', name: 'John Smith', email: 'john.smith@marketing.com' },
            { id: 'u-002', name: 'Sarah Johnson', email: 'sarah.j@marketing.com' },
            { id: 'u-003', name: 'Mike Williams', email: 'm.williams@marketing.com' }
        ],
        'sa-002': [
            { id: 'u-004', name: 'Emma Davis', email: 'emma.d@support.com' },
            { id: 'u-005', name: 'James Brown', email: 'j.brown@support.com' }
        ],
        'sa-003': [
            { id: 'u-006', name: 'Lisa Anderson', email: 'l.anderson@sales.com' },
            { id: 'u-007', name: 'Robert Taylor', email: 'r.taylor@sales.com' },
            { id: 'u-008', name: 'Jennifer White', email: 'j.white@sales.com' },
            { id: 'u-009', name: 'David Miller', email: 'd.miller@sales.com' }
        ],
        'sa-004': [
            { id: 'u-010', name: 'Amanda Clark', email: 'a.clark@hr.com' }
        ],
        'sa-005': [
            { id: 'u-011', name: 'Chris Lee', email: 'c.lee@ops.com' },
            { id: 'u-012', name: 'Patricia Moore', email: 'p.moore@ops.com' }
        ]
    };

    let selectedSubAccounts = [];
    let selectedUsers = [];

    // Initialize sub-accounts dropdown
    function renderSubAccountsOptions(filter = '') {
        const list = document.getElementById('subAccountsList');
        const filtered = subAccountsData.filter(sa => 
            sa.name.toLowerCase().includes(filter.toLowerCase())
        );

        if (filtered.length === 0) {
            list.innerHTML = '<div class="no-results">No sub-accounts found</div>';
            return;
        }

        list.innerHTML = filtered.map(sa => `
            <div class="option-item ${selectedSubAccounts.includes(sa.id) ? 'selected' : ''}" data-id="${sa.id}" data-name="${sa.name}">
                <div class="check-icon">${selectedSubAccounts.includes(sa.id) ? '<i class="fas fa-check"></i>' : ''}</div>
                <span>${sa.name}</span>
            </div>
        `).join('');

        list.querySelectorAll('.option-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSubAccount(this.dataset.id, this.dataset.name);
            });
        });
    }

    function toggleSubAccount(id, name) {
        const idx = selectedSubAccounts.indexOf(id);
        if (idx > -1) {
            selectedSubAccounts.splice(idx, 1);
            // Remove users from this sub-account
            const subAccountUsers = usersData[id] || [];
            subAccountUsers.forEach(u => {
                const userIdx = selectedUsers.findIndex(su => su.id === u.id);
                if (userIdx > -1) selectedUsers.splice(userIdx, 1);
            });
        } else {
            selectedSubAccounts.push(id);
        }
        updateSubAccountsTags();
        updateUsersOptions();
        renderSubAccountsOptions(document.getElementById('subAccountsSearch').value);
    }

    function updateSubAccountsTags() {
        const tagsContainer = document.getElementById('subAccountsTags');
        if (selectedSubAccounts.length === 0) {
            tagsContainer.innerHTML = '<span class="multiselect-placeholder">Select sub-accounts...</span>';
        } else {
            tagsContainer.innerHTML = selectedSubAccounts.map(id => {
                const sa = subAccountsData.find(s => s.id === id);
                return `<span class="tag">${sa ? sa.name : id} <i class="fas fa-times remove-tag" data-id="${id}"></i></span>`;
            }).join('');

            tagsContainer.querySelectorAll('.remove-tag').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const sa = subAccountsData.find(s => s.id === this.dataset.id);
                    toggleSubAccount(this.dataset.id, sa ? sa.name : '');
                });
            });
        }
        document.getElementById('selectedSubAccounts').value = JSON.stringify(selectedSubAccounts);
    }

    // Users dropdown logic
    function updateUsersOptions() {
        const container = document.getElementById('usersSelectionContainer');
        if (selectedSubAccounts.length === 0) {
            container.style.display = 'none';
            selectedUsers = [];
            updateUsersTags();
            return;
        }
        container.style.display = 'block';
        renderUsersOptions('');
    }

    function renderUsersOptions(filter = '') {
        const list = document.getElementById('usersList');
        let allUsers = [];
        selectedSubAccounts.forEach(saId => {
            const users = usersData[saId] || [];
            const sa = subAccountsData.find(s => s.id === saId);
            users.forEach(u => {
                allUsers.push({ ...u, subAccountName: sa ? sa.name : saId });
            });
        });

        const filtered = allUsers.filter(u => 
            u.name.toLowerCase().includes(filter.toLowerCase()) ||
            u.email.toLowerCase().includes(filter.toLowerCase())
        );

        if (filtered.length === 0) {
            list.innerHTML = '<div class="no-results">No users found</div>';
            return;
        }

        list.innerHTML = filtered.map(u => `
            <div class="option-item ${selectedUsers.find(su => su.id === u.id) ? 'selected' : ''}" data-id="${u.id}" data-name="${u.name}" data-email="${u.email}">
                <div class="check-icon">${selectedUsers.find(su => su.id === u.id) ? '<i class="fas fa-check"></i>' : ''}</div>
                <div class="user-option-detail">
                    <span class="user-name">${u.name}</span>
                    <span class="user-email">${u.email} (${u.subAccountName})</span>
                </div>
            </div>
        `).join('');

        list.querySelectorAll('.option-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleUser(this.dataset.id, this.dataset.name, this.dataset.email);
            });
        });
    }

    function toggleUser(id, name, email) {
        const idx = selectedUsers.findIndex(u => u.id === id);
        if (idx > -1) {
            selectedUsers.splice(idx, 1);
        } else {
            selectedUsers.push({ id, name, email });
        }
        updateUsersTags();
        renderUsersOptions(document.getElementById('usersSearch').value);
    }

    function updateUsersTags() {
        const tagsContainer = document.getElementById('usersTags');
        if (selectedUsers.length === 0) {
            tagsContainer.innerHTML = '<span class="multiselect-placeholder">Select users from sub-accounts...</span>';
        } else {
            tagsContainer.innerHTML = selectedUsers.map(u => 
                `<span class="tag">${u.name} <i class="fas fa-times remove-tag" data-id="${u.id}"></i></span>`
            ).join('');

            tagsContainer.querySelectorAll('.remove-tag').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const user = selectedUsers.find(u => u.id === this.dataset.id);
                    if (user) toggleUser(user.id, user.name, user.email);
                });
            });
        }
        document.getElementById('selectedUsers').value = JSON.stringify(selectedUsers);
    }

    // Dropdown toggle handlers
    document.getElementById('subAccountsTags').addEventListener('click', function() {
        document.getElementById('subAccountsDropdown').classList.toggle('show');
        document.getElementById('usersDropdown').classList.remove('show');
    });

    document.getElementById('usersTags').addEventListener('click', function() {
        document.getElementById('usersDropdown').classList.toggle('show');
        document.getElementById('subAccountsDropdown').classList.remove('show');
    });

    document.getElementById('subAccountsSearch').addEventListener('input', function() {
        renderSubAccountsOptions(this.value);
    });

    document.getElementById('usersSearch').addEventListener('input', function() {
        renderUsersOptions(this.value);
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!document.getElementById('subAccountsWrapper').contains(e.target)) {
            document.getElementById('subAccountsDropdown').classList.remove('show');
        }
        if (!document.getElementById('usersWrapper').contains(e.target)) {
            document.getElementById('usersDropdown').classList.remove('show');
        }
    });

    // Initialize
    renderSubAccountsOptions();

    // Restore saved data
    var savedData = sessionStorage.getItem('templateWizardStep3');
    if (savedData) {
        var data = JSON.parse(savedData);
        if (data.subAccounts && Array.isArray(data.subAccounts)) {
            selectedSubAccounts = data.subAccounts;
            updateSubAccountsTags();
            updateUsersOptions();
        }
        if (data.users && Array.isArray(data.users)) {
            selectedUsers = data.users;
            updateUsersTags();
        }
        document.getElementById('allowEditing').checked = data.allowEditing || false;
    }

    document.getElementById('nextBtn').addEventListener('click', function() {
        sessionStorage.setItem('templateWizardStep3', JSON.stringify({
            subAccounts: selectedSubAccounts,
            users: selectedUsers,
            allowEditing: document.getElementById('allowEditing').checked
        }));
    });
});
</script>
@endpush

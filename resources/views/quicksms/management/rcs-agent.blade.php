@extends('layouts.quicksms')

@section('title', 'RCS Agent Library')

@push('styles')
<style>
.rcs-agents-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.rcs-agents-header h2 {
    margin: 0;
    font-weight: 600;
}
.rcs-agents-header p {
    margin: 0;
    color: #6c757d;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: var(--primary);
}
.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.agents-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}
.agents-table {
    width: 100%;
    margin: 0;
    min-width: 900px;
    table-layout: fixed;
}
.agents-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.agents-table thead th:first-child { width: 22%; }
.agents-table thead th:nth-child(2) { width: 12%; }
.agents-table thead th:nth-child(3) { width: 16%; }
.agents-table thead th:nth-child(4) { width: 14%; }
.agents-table thead th:nth-child(5) { width: 12%; }
.agents-table thead th:nth-child(6) { width: 12%; }
.agents-table thead th:last-child { 
    width: 7%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.agents-table thead th:hover {
    background: #e9ecef;
}
.agents-table thead th:last-child:hover {
    background: #f8f9fa;
}
.agents-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.agents-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.agents-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
}
.agents-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.agents-table tbody tr:last-child td {
    border-bottom: none;
}
.agents-table tbody tr:hover td {
    background: #f8f9fa;
}
.agents-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.agent-name {
    font-weight: 500;
    color: #343a40;
}
.badge-draft {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-submitted {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-in-review {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-approved {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-rejected {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-conversational {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-non-conversational {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.search-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
    flex-wrap: wrap;
}
.search-box {
    flex: 1;
    max-width: 300px;
    min-width: 200px;
}
.filters-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}
.action-menu .dropdown-item {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
}
.action-menu .dropdown-item i {
    width: 18px;
    margin-right: 0.5rem;
}
.action-menu .dropdown-item.disabled {
    color: #adb5bd;
    pointer-events: none;
}
.date-text {
    font-size: 0.85rem;
    color: #495057;
}
.use-case-text {
    font-size: 0.85rem;
    color: #495057;
}
.pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 1rem;
}
.pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.pagination-controls {
    display: flex;
    gap: 0.25rem;
}
.pagination-controls .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
}
.wizard-steps {
    display: flex;
    gap: 1rem;
}
.wizard-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.6);
}
.wizard-step.active {
    color: #fff;
}
.wizard-step.completed {
    color: #fff;
}
.wizard-step .step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.3);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
}
.wizard-step.active .step-number {
    background-color: #fff;
    color: var(--primary);
}
.wizard-step.completed .step-number {
    background-color: #fff;
    color: var(--primary);
}
.wizard-step .step-label {
    font-size: 0.85rem;
    font-weight: 500;
}
.wizard-step-inner {
    background: #fff;
    border-radius: 0.75rem;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.billing-option, .usecase-option {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.billing-option:hover, .usecase-option:hover {
    border-color: rgba(136, 108, 192, 0.5);
    background-color: #fdfcfe;
}
.billing-option.selected, .usecase-option.selected {
    border-color: var(--primary);
    background-color: #f0ebf8;
}
.billing-option .form-check, .usecase-option .form-check {
    margin: 0;
    padding: 0;
}
.billing-option .form-check-input, .usecase-option .form-check-input {
    position: absolute;
    opacity: 0;
}
.billing-option .form-check-label, .usecase-option .form-check-label {
    width: 100%;
    cursor: pointer;
}
.option-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}
.option-icon.bg-conversational {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.option-icon.bg-non-conversational {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.option-icon.bg-otp {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.option-icon.bg-transactional {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.option-icon.bg-promotional {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.option-icon.bg-multiuse {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.logo-upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fafafa;
}
.logo-upload-zone:hover {
    border-color: var(--primary);
    background: #f8f5fd;
}
.logo-upload-zone.has-logo {
    border-style: solid;
    border-color: var(--primary);
}
.logo-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.color-preview {
    width: 40px;
    height: 40px;
    border-radius: 0.375rem;
    border: 2px solid #dee2e6;
}
.autosave-indicator {
    font-size: 0.8rem;
    color: #6c757d;
}
.autosave-indicator.saving {
    color: #886CC0;
}
.autosave-indicator.saved {
    color: #1cbb8c;
}
.review-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.review-section h6 {
    color: #495057;
    margin-bottom: 1rem;
    font-weight: 600;
}
.review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.review-row:last-child {
    border-bottom: none;
}
.review-label {
    color: #6c757d;
    font-size: 0.85rem;
}
.review-value {
    font-weight: 500;
    color: #343a40;
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="rcs-agents-header">
        <div>
            <h2>RCS Agent Library</h2>
            <p>View, manage, and track all RCS Agents for your account</p>
        </div>
        <button class="btn btn-primary" id="createAgentBtn">
            <i class="fas fa-plus me-2"></i>Create RCS Agent
        </button>
    </div>

    <div class="agents-table-container" id="agentsTableContainer">
        <div class="search-filter-bar">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search agents...">
                </div>
            </div>
            <div class="filters-group">
                <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="in-review">In Review</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select class="form-select form-select-sm" id="billingFilter" style="width: auto;">
                    <option value="">All Billing Types</option>
                    <option value="conversational">Conversational</option>
                    <option value="non-conversational">Non-conversational</option>
                </select>
                <select class="form-select form-select-sm" id="useCaseFilter" style="width: auto;">
                    <option value="">All Use Cases</option>
                    <option value="otp">OTP</option>
                    <option value="transactional">Transactional</option>
                    <option value="promotional">Promotional</option>
                    <option value="multi-use">Multi-use</option>
                </select>
            </div>
        </div>

        <table class="agents-table" id="agentsTable">
            <thead>
                <tr>
                    <th data-sort="name" onclick="sortTable('name')">Agent Name <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="status" onclick="sortTable('status')">Status <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="billing" onclick="sortTable('billing')">Billing Category <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="useCase" onclick="sortTable('useCase')">Use Case <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="created" onclick="sortTable('created')">Created <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="updated" onclick="sortTable('updated')">Last Updated <i class="fas fa-sort sort-icon"></i></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="agentsTableBody">
            </tbody>
        </table>

        <div class="pagination-bar">
            <div class="pagination-info">
                Showing <span id="showingStart">1</span>-<span id="showingEnd">10</span> of <span id="totalCount">0</span> agents
            </div>
            <div class="pagination-controls">
                <button class="btn btn-outline-secondary btn-sm" id="prevPageBtn" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="nextPageBtn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="empty-state d-none" id="emptyState">
        <div class="empty-state-icon">
            <i class="fas fa-robot"></i>
        </div>
        <h4>No RCS Agents Yet</h4>
        <p>Create your first RCS Agent to enable rich messaging experiences for your customers.</p>
        <button class="btn btn-primary" id="createAgentEmptyBtn">
            <i class="fas fa-plus me-2"></i>Create RCS Agent
        </button>
    </div>
</div>

<div class="modal fade" id="viewAgentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Agent Name</label>
                        <p class="fw-semibold mb-0" id="viewAgentName"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Status</label>
                        <p class="mb-0" id="viewAgentStatus"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Billing Category</label>
                        <p class="mb-0" id="viewAgentBilling"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Use Case</label>
                        <p class="mb-0" id="viewAgentUseCase"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Created Date</label>
                        <p class="mb-0" id="viewAgentCreated"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Last Updated</label>
                        <p class="mb-0" id="viewAgentUpdated"></p>
                    </div>
                </div>
                <div class="mb-3" id="viewRejectionReasonContainer" style="display: none;">
                    <label class="form-label small text-muted">Rejection Reason</label>
                    <div class="border rounded p-3 bg-white">
                        <p class="mb-0 text-danger" id="viewAgentRejectionReason"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resubmitAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resubmit RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-pastel-primary mb-3">
                    You are about to resubmit <strong id="resubmitAgentName"></strong> for review.
                </div>
                <p class="text-muted">The agent will be placed back in the review queue. You will be notified once a decision has been made.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmResubmitBtn">Resubmit for Review</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="agentWizardModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="height: 100vh; display: flex; flex-direction: column;">
            <div class="modal-header py-3 flex-shrink-0" style="background: linear-gradient(135deg, #886CC0 0%, #a78bda 100%); color: #fff;">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title mb-0" id="agentWizardTitle"><i class="fas fa-robot me-2"></i>Create RCS Agent</h5>
                    <div class="wizard-steps ms-4">
                        <span class="wizard-step active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-label">Business Info</span>
                        </span>
                        <span class="wizard-step" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-label">Branding</span>
                        </span>
                        <span class="wizard-step" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-label">Contact</span>
                        </span>
                        <span class="wizard-step" data-step="4">
                            <span class="step-number">4</span>
                            <span class="step-label">Review</span>
                        </span>
                    </div>
                    <span class="autosave-indicator ms-auto me-3" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <button type="button" class="btn-close btn-close-white" id="wizardCloseBtn"></button>
            </div>
            
            <div class="modal-body flex-grow-1 p-0" style="overflow-y: auto; background: #f8f9fa;">
                <div id="agentWizardStep1" class="wizard-content p-4">
                    <div class="wizard-step-inner mx-auto" style="max-width: 800px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 1: Business Information</strong> - Define the basic details for your RCS Agent. This information will be displayed to recipients.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Agent Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="agentName" placeholder="e.g., Your Brand Name" maxlength="100">
                            <small class="text-muted">This will be displayed as the sender name in RCS messages</small>
                            <div class="invalid-feedback">Please enter an agent name</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Business Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="agentDescription" rows="3" placeholder="Describe your business and how you'll use RCS messaging..." maxlength="500"></textarea>
                            <small class="text-muted"><span id="descCharCount">0</span>/500 characters</small>
                            <div class="invalid-feedback">Please enter a business description</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Billing Category <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Select the billing model for this agent.</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="billing-option" data-billing="conversational">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="agentBilling" id="billingConv" value="conversational">
                                            <label class="form-check-label" for="billingConv">
                                                <div class="d-flex align-items-center">
                                                    <div class="option-icon bg-conversational">
                                                        <i class="fas fa-comments"></i>
                                                    </div>
                                                    <div>
                                                        <strong>Conversational</strong>
                                                        <p class="mb-0 small text-muted">Two-way messaging with customer interactions</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="billing-option" data-billing="non-conversational">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="agentBilling" id="billingNonConv" value="non-conversational">
                                            <label class="form-check-label" for="billingNonConv">
                                                <div class="d-flex align-items-center">
                                                    <div class="option-icon bg-non-conversational">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </div>
                                                    <div>
                                                        <strong>Non-conversational</strong>
                                                        <p class="mb-0 small text-muted">One-way notifications and alerts</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Use Case <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Select the primary use case for this agent.</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="usecase-option" data-usecase="otp">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="agentUseCase" id="useCaseOtp" value="otp">
                                            <label class="form-check-label" for="useCaseOtp">
                                                <div class="d-flex align-items-center">
                                                    <div class="option-icon bg-otp">
                                                        <i class="fas fa-key"></i>
                                                    </div>
                                                    <div>
                                                        <strong>OTP</strong>
                                                        <p class="mb-0 small text-muted">One-time passwords and verification codes</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="usecase-option" data-usecase="transactional">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="agentUseCase" id="useCaseTrans" value="transactional">
                                            <label class="form-check-label" for="useCaseTrans">
                                                <div class="d-flex align-items-center">
                                                    <div class="option-icon bg-transactional">
                                                        <i class="fas fa-receipt"></i>
                                                    </div>
                                                    <div>
                                                        <strong>Transactional</strong>
                                                        <p class="mb-0 small text-muted">Order updates, confirmations, alerts</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="usecase-option" data-usecase="promotional">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="agentUseCase" id="useCasePromo" value="promotional">
                                            <label class="form-check-label" for="useCasePromo">
                                                <div class="d-flex align-items-center">
                                                    <div class="option-icon bg-promotional">
                                                        <i class="fas fa-bullhorn"></i>
                                                    </div>
                                                    <div>
                                                        <strong>Promotional</strong>
                                                        <p class="mb-0 small text-muted">Marketing, offers, and campaigns</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="usecase-option" data-usecase="multi-use">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="agentUseCase" id="useCaseMulti" value="multi-use">
                                            <label class="form-check-label" for="useCaseMulti">
                                                <div class="d-flex align-items-center">
                                                    <div class="option-icon bg-multiuse">
                                                        <i class="fas fa-layer-group"></i>
                                                    </div>
                                                    <div>
                                                        <strong>Multi-use</strong>
                                                        <p class="mb-0 small text-muted">Combination of multiple use cases</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="agentWizardStep2" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 800px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 2: Branding</strong> - Upload your brand assets and configure visual identity for the RCS agent.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Agent Logo <span class="text-danger">*</span></label>
                                <div class="logo-upload-zone" id="logoUploadZone">
                                    <div id="logoPlaceholder">
                                        <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 2rem;"></i>
                                        <p class="mb-1 text-muted">Click to upload logo</p>
                                        <small class="text-muted">PNG or JPG, min 224x224px</small>
                                    </div>
                                    <div id="logoPreviewContainer" class="d-none">
                                        <img src="" alt="Logo preview" class="logo-preview" id="logoPreviewImg">
                                        <p class="mt-2 mb-0 text-primary small">Click to change</p>
                                    </div>
                                </div>
                                <input type="file" id="logoFileInput" accept="image/png,image/jpeg" class="d-none">
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Brand Color <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" class="form-control form-control-color color-preview" id="brandColor" value="#886CC0">
                                    <input type="text" class="form-control" id="brandColorHex" value="#886CC0" maxlength="7" style="max-width: 120px;">
                                </div>
                                <small class="text-muted">This color will be used for buttons and accents</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Business Website <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="businessWebsite" placeholder="https://www.example.com">
                            <small class="text-muted">Your official business website URL</small>
                            <div class="invalid-feedback">Please enter a valid website URL</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Privacy Policy URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="privacyUrl" placeholder="https://www.example.com/privacy">
                            <small class="text-muted">Link to your privacy policy</small>
                            <div class="invalid-feedback">Please enter a valid privacy policy URL</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Terms of Service URL</label>
                            <input type="url" class="form-control" id="termsUrl" placeholder="https://www.example.com/terms">
                            <small class="text-muted">Link to your terms of service (optional)</small>
                        </div>
                    </div>
                </div>
                
                <div id="agentWizardStep3" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 800px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 3: Contact Information</strong> - Provide contact details for customer support and verification purposes.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Support Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="supportEmail" placeholder="support@example.com">
                            <small class="text-muted">Email address for customer inquiries</small>
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Support Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="supportPhone" placeholder="+44 20 1234 5678">
                            <small class="text-muted">Phone number for customer support</small>
                            <div class="invalid-feedback">Please enter a valid phone number</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Business Address</label>
                            <textarea class="form-control" id="businessAddress" rows="2" placeholder="123 Business Street, City, Country"></textarea>
                            <small class="text-muted">Physical business address (optional)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Additional Notes</label>
                            <textarea class="form-control" id="additionalNotes" rows="3" placeholder="Any additional information for the review team..."></textarea>
                            <small class="text-muted">Include any relevant context that may help with approval</small>
                        </div>
                    </div>
                </div>
                
                <div id="agentWizardStep4" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 800px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 4: Review & Submit</strong> - Please review all information before submitting for approval.
                        </div>
                        
                        <div class="review-section">
                            <h6>Business Information</h6>
                            <div class="review-row">
                                <span class="review-label">Agent Name</span>
                                <span class="review-value" id="reviewAgentName">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Description</span>
                                <span class="review-value" id="reviewDescription" style="max-width: 60%; text-align: right;">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Billing Category</span>
                                <span class="review-value" id="reviewBilling">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Use Case</span>
                                <span class="review-value" id="reviewUseCase">-</span>
                            </div>
                        </div>
                        
                        <div class="review-section">
                            <h6>Branding</h6>
                            <div class="review-row">
                                <span class="review-label">Logo</span>
                                <span class="review-value" id="reviewLogo">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Brand Color</span>
                                <span class="review-value d-flex align-items-center justify-content-end gap-2">
                                    <span class="color-preview" id="reviewColorPreview" style="width: 24px; height: 24px;"></span>
                                    <span id="reviewColor">-</span>
                                </span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Website</span>
                                <span class="review-value" id="reviewWebsite">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Privacy Policy</span>
                                <span class="review-value" id="reviewPrivacy">-</span>
                            </div>
                        </div>
                        
                        <div class="review-section">
                            <h6>Contact Information</h6>
                            <div class="review-row">
                                <span class="review-label">Support Email</span>
                                <span class="review-value" id="reviewEmail">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Support Phone</span>
                                <span class="review-value" id="reviewPhone">-</span>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Once submitted, your agent will be reviewed by our team. This typically takes 2-5 business days. You will be notified of the outcome via email.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer flex-shrink-0 bg-white border-top">
                <button type="button" class="btn btn-outline-info" id="wizardCancelBtn">Cancel</button>
                <button type="button" class="btn btn-outline-secondary" id="wizardPrevBtn" style="display: none;">
                    <i class="fas fa-arrow-left me-1"></i>Previous
                </button>
                <button type="button" class="btn btn-primary" id="wizardNextBtn">
                    Next<i class="fas fa-arrow-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-primary" id="wizardSubmitBtn" style="display: none;">
                    <i class="fas fa-paper-plane me-1"></i>Submit for Review
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exitWizardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save as Draft?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Your progress has been automatically saved as a draft. You can resume editing this agent at any time from the RCS Agent Library.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="discardDraftBtn">Discard Draft</button>
                <button type="button" class="btn btn-primary" id="keepDraftBtn">Keep Draft & Exit</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var mockAgents = [
    {
        id: 'agent-001',
        name: 'QuickSMS Notifications',
        status: 'approved',
        billing: 'conversational',
        useCase: 'transactional',
        created: '2025-09-15',
        updated: '2025-10-02',
        rejectionReason: null
    },
    {
        id: 'agent-002',
        name: 'Marketing Campaigns',
        status: 'approved',
        billing: 'non-conversational',
        useCase: 'promotional',
        created: '2025-08-20',
        updated: '2025-09-10',
        rejectionReason: null
    },
    {
        id: 'agent-003',
        name: 'OTP Verification',
        status: 'in-review',
        billing: 'non-conversational',
        useCase: 'otp',
        created: '2025-12-01',
        updated: '2025-12-01',
        rejectionReason: null
    },
    {
        id: 'agent-004',
        name: 'Customer Support Bot',
        status: 'submitted',
        billing: 'conversational',
        useCase: 'multi-use',
        created: '2025-12-28',
        updated: '2025-12-28',
        rejectionReason: null
    },
    {
        id: 'agent-005',
        name: 'Holiday Promotions',
        status: 'rejected',
        billing: 'non-conversational',
        useCase: 'promotional',
        created: '2025-11-15',
        updated: '2025-11-20',
        rejectionReason: 'Brand logo does not meet minimum resolution requirements. Please upload a logo with at least 224x224 pixels.'
    },
    {
        id: 'agent-006',
        name: 'Appointment Reminders',
        status: 'draft',
        billing: 'non-conversational',
        useCase: 'transactional',
        created: '2026-01-05',
        updated: '2026-01-05',
        rejectionReason: null
    },
    {
        id: 'agent-007',
        name: 'Order Updates',
        status: 'approved',
        billing: 'non-conversational',
        useCase: 'transactional',
        created: '2025-07-10',
        updated: '2025-08-15',
        rejectionReason: null
    },
    {
        id: 'agent-008',
        name: 'Loyalty Program',
        status: 'draft',
        billing: 'conversational',
        useCase: 'promotional',
        created: '2026-01-02',
        updated: '2026-01-06',
        rejectionReason: null
    }
];

var filteredAgents = [...mockAgents];
var currentSort = { field: 'updated', direction: 'desc' };
var currentPage = 1;
var pageSize = 10;

document.addEventListener('DOMContentLoaded', function() {
    renderTable();
    
    document.getElementById('searchInput').addEventListener('input', debounce(applyFilters, 300));
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('billingFilter').addEventListener('change', applyFilters);
    document.getElementById('useCaseFilter').addEventListener('change', applyFilters);
    
    document.getElementById('createAgentBtn').addEventListener('click', function() {
        openAgentWizard();
    });
    
    document.getElementById('createAgentEmptyBtn').addEventListener('click', function() {
        openAgentWizard();
    });
    
    initializeWizard();
    
    document.getElementById('confirmResubmitBtn').addEventListener('click', function() {
        alert('TODO: Resubmit agent via API');
        bootstrap.Modal.getInstance(document.getElementById('resubmitAgentModal')).hide();
    });
    
    document.getElementById('prevPageBtn').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    
    document.getElementById('nextPageBtn').addEventListener('click', function() {
        var maxPages = Math.ceil(filteredAgents.length / pageSize);
        if (currentPage < maxPages) {
            currentPage++;
            renderTable();
        }
    });
});

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

function applyFilters() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var status = document.getElementById('statusFilter').value;
    var billing = document.getElementById('billingFilter').value;
    var useCase = document.getElementById('useCaseFilter').value;
    
    filteredAgents = mockAgents.filter(function(agent) {
        var matchesSearch = !search || agent.name.toLowerCase().includes(search);
        var matchesStatus = !status || agent.status === status;
        var matchesBilling = !billing || agent.billing === billing;
        var matchesUseCase = !useCase || agent.useCase === useCase;
        
        return matchesSearch && matchesStatus && matchesBilling && matchesUseCase;
    });
    
    currentPage = 1;
    sortAgents();
    renderTable();
}

function sortTable(field) {
    var headers = document.querySelectorAll('.agents-table thead th');
    headers.forEach(function(th) { th.classList.remove('sorted'); });
    
    if (currentSort.field === field) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.field = field;
        currentSort.direction = 'asc';
    }
    
    var sortedHeader = document.querySelector('[data-sort="' + field + '"]');
    if (sortedHeader) {
        sortedHeader.classList.add('sorted');
        var icon = sortedHeader.querySelector('.sort-icon');
        icon.className = 'fas fa-sort-' + (currentSort.direction === 'asc' ? 'up' : 'down') + ' sort-icon';
    }
    
    sortAgents();
    renderTable();
}

function sortAgents() {
    filteredAgents.sort(function(a, b) {
        var aVal = a[currentSort.field];
        var bVal = b[currentSort.field];
        
        if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }
        
        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
}

function renderTable() {
    var tbody = document.getElementById('agentsTableBody');
    var tableContainer = document.getElementById('agentsTableContainer');
    var emptyState = document.getElementById('emptyState');
    
    if (filteredAgents.length === 0) {
        tableContainer.classList.add('d-none');
        emptyState.classList.remove('d-none');
        return;
    }
    
    tableContainer.classList.remove('d-none');
    emptyState.classList.add('d-none');
    
    var start = (currentPage - 1) * pageSize;
    var end = Math.min(start + pageSize, filteredAgents.length);
    var pageAgents = filteredAgents.slice(start, end);
    
    tbody.innerHTML = pageAgents.map(function(agent) {
        return '<tr>' +
            '<td><span class="agent-name">' + escapeHtml(agent.name) + '</span></td>' +
            '<td>' + getStatusBadge(agent.status) + '</td>' +
            '<td>' + getBillingBadge(agent.billing) + '</td>' +
            '<td><span class="use-case-text">' + formatUseCase(agent.useCase) + '</span></td>' +
            '<td><span class="date-text">' + formatDate(agent.created) + '</span></td>' +
            '<td><span class="date-text">' + formatDate(agent.updated) + '</span></td>' +
            '<td>' + getActionsMenu(agent) + '</td>' +
        '</tr>';
    }).join('');
    
    document.getElementById('showingStart').textContent = start + 1;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalCount').textContent = filteredAgents.length;
    
    document.getElementById('prevPageBtn').disabled = currentPage === 1;
    document.getElementById('nextPageBtn').disabled = end >= filteredAgents.length;
}

function getStatusBadge(status) {
    var labels = {
        'draft': 'Draft',
        'submitted': 'Submitted',
        'in-review': 'In Review',
        'approved': 'Approved',
        'rejected': 'Rejected'
    };
    var classes = {
        'draft': 'badge-draft',
        'submitted': 'badge-submitted',
        'in-review': 'badge-in-review',
        'approved': 'badge-approved',
        'rejected': 'badge-rejected'
    };
    return '<span class="badge ' + classes[status] + '">' + labels[status] + '</span>';
}

function getBillingBadge(billing) {
    var label = billing === 'conversational' ? 'Conversational' : 'Non-conversational';
    var cls = billing === 'conversational' ? 'badge-conversational' : 'badge-non-conversational';
    return '<span class="badge ' + cls + '">' + label + '</span>';
}

function formatUseCase(useCase) {
    var labels = {
        'otp': 'OTP',
        'transactional': 'Transactional',
        'promotional': 'Promotional',
        'multi-use': 'Multi-use'
    };
    return labels[useCase] || useCase;
}

function formatDate(dateStr) {
    var date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function getActionsMenu(agent) {
    var canEdit = agent.status === 'draft' || agent.status === 'rejected';
    var canResubmit = agent.status === 'rejected';
    
    return '<div class="dropdown action-menu">' +
        '<button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">' +
            '<i class="fas fa-ellipsis-v"></i>' +
        '</button>' +
        '<ul class="dropdown-menu dropdown-menu-end">' +
            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewAgent(\'' + agent.id + '\')">' +
                '<i class="fas fa-eye"></i>View</a></li>' +
            '<li><a class="dropdown-item' + (canEdit ? '' : ' disabled') + '" href="javascript:void(0)"' + (canEdit ? ' onclick="editAgent(\'' + agent.id + '\')"' : '') + '>' +
                '<i class="fas fa-edit"></i>Edit</a></li>' +
            (canResubmit ? '<li><a class="dropdown-item" href="javascript:void(0)" onclick="resubmitAgent(\'' + agent.id + '\')">' +
                '<i class="fas fa-redo"></i>Resubmit</a></li>' : '') +
        '</ul>' +
    '</div>';
}

function viewAgent(agentId) {
    var agent = mockAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;
    
    document.getElementById('viewAgentName').textContent = agent.name;
    document.getElementById('viewAgentStatus').innerHTML = getStatusBadge(agent.status);
    document.getElementById('viewAgentBilling').innerHTML = getBillingBadge(agent.billing);
    document.getElementById('viewAgentUseCase').textContent = formatUseCase(agent.useCase);
    document.getElementById('viewAgentCreated').textContent = formatDate(agent.created);
    document.getElementById('viewAgentUpdated').textContent = formatDate(agent.updated);
    
    var rejectionContainer = document.getElementById('viewRejectionReasonContainer');
    if (agent.rejectionReason) {
        rejectionContainer.style.display = 'block';
        document.getElementById('viewAgentRejectionReason').textContent = agent.rejectionReason;
    } else {
        rejectionContainer.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('viewAgentModal')).show();
}

function editAgent(agentId) {
    var agent = mockAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;
    openAgentWizard(agent);
}

function resubmitAgent(agentId) {
    var agent = mockAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;
    
    document.getElementById('resubmitAgentName').textContent = agent.name;
    new bootstrap.Modal(document.getElementById('resubmitAgentModal')).show();
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

var wizardData = {
    id: null,
    name: '',
    description: '',
    billing: '',
    useCase: '',
    logoFile: null,
    logoDataUrl: null,
    brandColor: '#886CC0',
    website: '',
    privacyUrl: '',
    termsUrl: '',
    supportEmail: '',
    supportPhone: '',
    businessAddress: '',
    additionalNotes: '',
    currentStep: 1,
    isEditing: false,
    isDirty: false
};

var wizardModal = null;
var exitModal = null;
var autosaveTimeout = null;

function initializeWizard() {
    wizardModal = new bootstrap.Modal(document.getElementById('agentWizardModal'), { backdrop: 'static' });
    exitModal = new bootstrap.Modal(document.getElementById('exitWizardModal'));
    
    document.getElementById('wizardNextBtn').addEventListener('click', nextStep);
    document.getElementById('wizardPrevBtn').addEventListener('click', prevStep);
    document.getElementById('wizardCancelBtn').addEventListener('click', handleWizardCancel);
    document.getElementById('wizardCloseBtn').addEventListener('click', handleWizardCancel);
    document.getElementById('wizardSubmitBtn').addEventListener('click', submitAgent);
    document.getElementById('keepDraftBtn').addEventListener('click', keepDraftAndExit);
    document.getElementById('discardDraftBtn').addEventListener('click', discardDraftAndExit);
    
    document.querySelectorAll('.billing-option').forEach(function(opt) {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.billing-option').forEach(function(o) { o.classList.remove('selected'); });
            opt.classList.add('selected');
            opt.querySelector('input').checked = true;
            wizardData.billing = opt.dataset.billing;
            triggerAutosave();
        });
    });
    
    document.querySelectorAll('.usecase-option').forEach(function(opt) {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.usecase-option').forEach(function(o) { o.classList.remove('selected'); });
            opt.classList.add('selected');
            opt.querySelector('input').checked = true;
            wizardData.useCase = opt.dataset.usecase;
            triggerAutosave();
        });
    });
    
    document.getElementById('agentName').addEventListener('input', function() {
        wizardData.name = this.value;
        triggerAutosave();
    });
    
    document.getElementById('agentDescription').addEventListener('input', function() {
        wizardData.description = this.value;
        document.getElementById('descCharCount').textContent = this.value.length;
        triggerAutosave();
    });
    
    document.getElementById('logoUploadZone').addEventListener('click', function() {
        document.getElementById('logoFileInput').click();
    });
    
    document.getElementById('logoFileInput').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            var file = e.target.files[0];
            wizardData.logoFile = file;
            var reader = new FileReader();
            reader.onload = function(evt) {
                wizardData.logoDataUrl = evt.target.result;
                document.getElementById('logoPreviewImg').src = evt.target.result;
                document.getElementById('logoPlaceholder').classList.add('d-none');
                document.getElementById('logoPreviewContainer').classList.remove('d-none');
                document.getElementById('logoUploadZone').classList.add('has-logo');
                triggerAutosave();
            };
            reader.readAsDataURL(file);
        }
    });
    
    document.getElementById('brandColor').addEventListener('input', function() {
        wizardData.brandColor = this.value;
        document.getElementById('brandColorHex').value = this.value;
        triggerAutosave();
    });
    
    document.getElementById('brandColorHex').addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            wizardData.brandColor = this.value;
            document.getElementById('brandColor').value = this.value;
            triggerAutosave();
        }
    });
    
    ['businessWebsite', 'privacyUrl', 'termsUrl', 'supportEmail', 'supportPhone', 'businessAddress', 'additionalNotes'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            var key = id === 'businessWebsite' ? 'website' : id;
            wizardData[key] = this.value;
            triggerAutosave();
        });
    });
}

function openAgentWizard(existingAgent) {
    resetWizardData();
    
    if (existingAgent) {
        wizardData.id = existingAgent.id;
        wizardData.name = existingAgent.name || '';
        wizardData.billing = existingAgent.billing || '';
        wizardData.useCase = existingAgent.useCase || '';
        wizardData.isEditing = true;
        document.getElementById('agentWizardTitle').innerHTML = '<i class="fas fa-robot me-2"></i>Edit RCS Agent';
        
        document.getElementById('agentName').value = wizardData.name;
        if (wizardData.billing) {
            var billingOpt = document.querySelector('.billing-option[data-billing="' + wizardData.billing + '"]');
            if (billingOpt) {
                billingOpt.classList.add('selected');
                billingOpt.querySelector('input').checked = true;
            }
        }
        if (wizardData.useCase) {
            var useCaseOpt = document.querySelector('.usecase-option[data-usecase="' + wizardData.useCase + '"]');
            if (useCaseOpt) {
                useCaseOpt.classList.add('selected');
                useCaseOpt.querySelector('input').checked = true;
            }
        }
    } else {
        wizardData.id = 'agent-' + Date.now();
        document.getElementById('agentWizardTitle').innerHTML = '<i class="fas fa-robot me-2"></i>Create RCS Agent';
    }
    
    goToStep(1);
    wizardModal.show();
}

function resetWizardData() {
    wizardData = {
        id: null,
        name: '',
        description: '',
        billing: '',
        useCase: '',
        logoFile: null,
        logoDataUrl: null,
        brandColor: '#886CC0',
        website: '',
        privacyUrl: '',
        termsUrl: '',
        supportEmail: '',
        supportPhone: '',
        businessAddress: '',
        additionalNotes: '',
        currentStep: 1,
        isEditing: false,
        isDirty: false
    };
    
    document.getElementById('agentName').value = '';
    document.getElementById('agentDescription').value = '';
    document.getElementById('descCharCount').textContent = '0';
    document.querySelectorAll('.billing-option, .usecase-option').forEach(function(o) {
        o.classList.remove('selected');
        o.querySelector('input').checked = false;
    });
    document.getElementById('logoPlaceholder').classList.remove('d-none');
    document.getElementById('logoPreviewContainer').classList.add('d-none');
    document.getElementById('logoUploadZone').classList.remove('has-logo');
    document.getElementById('brandColor').value = '#886CC0';
    document.getElementById('brandColorHex').value = '#886CC0';
    document.getElementById('businessWebsite').value = '';
    document.getElementById('privacyUrl').value = '';
    document.getElementById('termsUrl').value = '';
    document.getElementById('supportEmail').value = '';
    document.getElementById('supportPhone').value = '';
    document.getElementById('businessAddress').value = '';
    document.getElementById('additionalNotes').value = '';
    
    document.querySelectorAll('.form-control.is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
    
    updateAutosaveIndicator('saved');
}

function goToStep(step) {
    wizardData.currentStep = step;
    
    for (var i = 1; i <= 4; i++) {
        document.getElementById('agentWizardStep' + i).classList.toggle('d-none', i !== step);
    }
    
    document.querySelectorAll('.wizard-step').forEach(function(el) {
        var stepNum = parseInt(el.dataset.step);
        el.classList.remove('active', 'completed');
        if (stepNum < step) {
            el.classList.add('completed');
        } else if (stepNum === step) {
            el.classList.add('active');
        }
    });
    
    document.getElementById('wizardPrevBtn').style.display = step > 1 ? '' : 'none';
    document.getElementById('wizardNextBtn').style.display = step < 4 ? '' : 'none';
    document.getElementById('wizardSubmitBtn').style.display = step === 4 ? '' : 'none';
    
    if (step === 4) {
        populateReviewStep();
    }
}

function nextStep() {
    if (!validateCurrentStep()) return;
    
    if (wizardData.currentStep < 4) {
        goToStep(wizardData.currentStep + 1);
    }
}

function prevStep() {
    if (wizardData.currentStep > 1) {
        goToStep(wizardData.currentStep - 1);
    }
}

function validateCurrentStep() {
    var isValid = true;
    
    document.querySelectorAll('.form-control.is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
    
    if (wizardData.currentStep === 1) {
        if (!wizardData.name.trim()) {
            document.getElementById('agentName').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.description.trim()) {
            document.getElementById('agentDescription').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.billing) {
            isValid = false;
        }
        if (!wizardData.useCase) {
            isValid = false;
        }
    } else if (wizardData.currentStep === 2) {
        if (!wizardData.logoDataUrl) {
            isValid = false;
        }
        if (!wizardData.website.trim() || !isValidUrl(wizardData.website)) {
            document.getElementById('businessWebsite').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.privacyUrl.trim() || !isValidUrl(wizardData.privacyUrl)) {
            document.getElementById('privacyUrl').classList.add('is-invalid');
            isValid = false;
        }
    } else if (wizardData.currentStep === 3) {
        if (!wizardData.supportEmail.trim() || !isValidEmail(wizardData.supportEmail)) {
            document.getElementById('supportEmail').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.supportPhone.trim()) {
            document.getElementById('supportPhone').classList.add('is-invalid');
            isValid = false;
        }
    }
    
    return isValid;
}

function isValidUrl(str) {
    try {
        new URL(str);
        return true;
    } catch (e) {
        return false;
    }
}

function isValidEmail(str) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(str);
}

function populateReviewStep() {
    document.getElementById('reviewAgentName').textContent = wizardData.name || '-';
    document.getElementById('reviewDescription').textContent = wizardData.description || '-';
    document.getElementById('reviewBilling').textContent = wizardData.billing === 'conversational' ? 'Conversational' : 'Non-conversational';
    document.getElementById('reviewUseCase').textContent = formatUseCase(wizardData.useCase);
    document.getElementById('reviewLogo').textContent = wizardData.logoDataUrl ? 'Uploaded' : 'Not uploaded';
    document.getElementById('reviewColorPreview').style.backgroundColor = wizardData.brandColor;
    document.getElementById('reviewColor').textContent = wizardData.brandColor;
    document.getElementById('reviewWebsite').textContent = wizardData.website || '-';
    document.getElementById('reviewPrivacy').textContent = wizardData.privacyUrl || '-';
    document.getElementById('reviewEmail').textContent = wizardData.supportEmail || '-';
    document.getElementById('reviewPhone').textContent = wizardData.supportPhone || '-';
}

function handleWizardCancel() {
    if (wizardData.isDirty) {
        exitModal.show();
    } else {
        wizardModal.hide();
    }
}

function keepDraftAndExit() {
    autosaveDraft();
    exitModal.hide();
    wizardModal.hide();
}

function discardDraftAndExit() {
    exitModal.hide();
    wizardModal.hide();
}

function triggerAutosave() {
    wizardData.isDirty = true;
    updateAutosaveIndicator('saving');
    
    if (autosaveTimeout) {
        clearTimeout(autosaveTimeout);
    }
    
    autosaveTimeout = setTimeout(function() {
        autosaveDraft();
    }, 1500);
}

function autosaveDraft() {
    updateAutosaveIndicator('saved');
    wizardData.isDirty = false;
}

function updateAutosaveIndicator(status) {
    var indicator = document.getElementById('autosaveIndicator');
    var text = document.getElementById('autosaveText');
    
    indicator.classList.remove('saving', 'saved');
    
    if (status === 'saving') {
        indicator.classList.add('saving');
        text.textContent = 'Saving...';
    } else {
        indicator.classList.add('saved');
        text.textContent = 'Draft saved';
    }
}

function submitAgent() {
    if (!validateCurrentStep()) return;
    
    updateAutosaveIndicator('saving');
    
    setTimeout(function() {
        updateAutosaveIndicator('saved');
        
        var newAgent = {
            id: wizardData.id,
            name: wizardData.name,
            status: 'submitted',
            billing: wizardData.billing,
            useCase: wizardData.useCase,
            created: new Date().toISOString().split('T')[0],
            updated: new Date().toISOString().split('T')[0],
            rejectionReason: null
        };
        
        var existingIdx = mockAgents.findIndex(function(a) { return a.id === wizardData.id; });
        if (existingIdx >= 0) {
            mockAgents[existingIdx] = newAgent;
        } else {
            mockAgents.unshift(newAgent);
        }
        
        applyFilters();
        wizardModal.hide();
        
        alert('RCS Agent submitted for review successfully! You will be notified once a decision has been made.');
    }, 500);
}
</script>
@endpush

<style>
.btn-admin-primary {
    background-color: #1e3a5f;
    border-color: #1e3a5f;
    color: #fff;
    font-weight: 500;
}
.btn-admin-primary:hover {
    background-color: #152a45;
    border-color: #152a45;
    color: #fff;
}
.btn-admin-primary:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
    opacity: 0.65;
}
.customer-typeahead-wrapper {
    position: relative;
}
.customer-select-input {
    cursor: pointer;
    background-color: #fff !important;
    padding-right: 2.5rem;
}
.customer-select-input::placeholder {
    color: #6c757d;
}
.customer-select-caret {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #6c757d;
}
.customer-typeahead-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1060;
    display: none;
}
.customer-typeahead-dropdown.show {
    display: block;
}
.customer-typeahead-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
}
.customer-typeahead-item:last-child {
    border-bottom: none;
}
.customer-typeahead-item:hover {
    background-color: #f8f9fa;
}
.customer-typeahead-item .customer-name {
    font-weight: 600;
    color: #343a40;
}
.customer-typeahead-item .customer-account-id {
    font-size: 0.8rem;
    color: #6c757d;
}
.customer-typeahead-no-results {
    padding: 0.75rem 1rem;
    color: #6c757d;
    font-style: italic;
}
.locked-customer-display {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
}
.locked-customer-display .lock-icon {
    color: #6c757d;
    font-size: 0.9rem;
}
</style>

<div class="modal fade" id="createInvoiceCreditModal" tabindex="-1" aria-labelledby="createInvoiceCreditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary, #1e3a5f); color: #fff;">
                <h5 class="modal-title" id="createInvoiceCreditModalLabel">
                    <i class="fas fa-file-invoice me-2" id="modalTitleIcon"></i>
                    <span id="modalTitleText">Create Customer Invoice</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalErrorAlert" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-circle me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong id="modalErrorTitle">Unable to create document</strong>
                            <p class="mb-2 small" id="modalErrorMessage"></p>
                            <small class="text-muted" id="modalErrorRef"></small>
                            <div class="mt-2">
                                <a href="#" class="btn btn-outline-danger btn-sm" id="viewCustomerBillingBtn" target="_blank">
                                    <i class="fas fa-external-link-alt me-1"></i>View customer billing details
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn-close" onclick="InvoiceCreditModal.hideError()" aria-label="Close"></button>
                    </div>
                </div>
                
                <p class="text-muted mb-4" id="modalDescription">Complete the form below to create a new customer invoice.</p>
                
                <form id="createInvoiceCreditForm" novalidate>
                    <input type="hidden" id="formMode" name="mode" value="invoice">
                    <input type="hidden" id="selectedCustomerId" name="customerId" value="">
                    <input type="hidden" id="customerLocked" name="customerLocked" value="false">
                    
                    <div class="mb-4" id="customerSelectionSection">
                        <label for="customerSearchInput" class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                        <div class="customer-typeahead-wrapper position-relative" id="customerTypeaheadWrapper">
                            <input type="text" class="form-control customer-select-input" id="customerSearchInput" placeholder="Select customer..." autocomplete="off">
                            <span class="customer-select-caret"><i class="fas fa-chevron-down"></i></span>
                            <div class="customer-typeahead-dropdown" id="customerTypeaheadDropdown"></div>
                            <div class="selected-customer-display d-none" id="selectedCustomerDisplay">
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded bg-light">
                                    <div>
                                        <span class="fw-semibold" id="selectedCustomerName"></span>
                                        <small class="text-muted ms-2" id="selectedCustomerAccountId"></small>
                                        <span class="badge ms-2" id="selectedCustomerStatus"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearCustomerBtn" title="Clear selection">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="customerError">Please select a customer</div>
                        </div>
                        
                        <div class="locked-customer-display d-none" id="lockedCustomerDisplay">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-semibold" id="lockedCustomerName"></span>
                                    <small class="text-muted ms-2" id="lockedCustomerAccountId"></small>
                                    <span class="badge ms-2" id="lockedCustomerStatus"></span>
                                </div>
                                <span class="lock-icon" title="Customer is pre-selected and cannot be changed from this screen">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="text-muted text-uppercase small mb-3">Line Item</h6>
                    
                    <div class="mb-3">
                        <label for="itemDescription" class="form-label fw-semibold">Item description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="itemDescription" maxlength="255" placeholder="e.g. Setup fee, Price correction, Professional services" required>
                        <div class="d-flex justify-content-between">
                            <div class="invalid-feedback" id="itemDescriptionError">Please enter an item description</div>
                            <small class="text-muted mt-1"><span id="descCharCount">0</span>/255</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="itemQuantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="itemQuantity" value="1" min="0.01" step="0.01" required>
                            <div class="invalid-feedback" id="itemQuantityError">Minimum 0.01</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="itemUnitPrice" class="form-label fw-semibold">Unit price (&pound;) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="itemUnitPrice" placeholder="0.0000" required>
                            <div class="invalid-feedback" id="itemUnitPriceError">Enter a valid price (max 4 decimal places)</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lineTotal" class="form-label fw-semibold">Line total (&pound;)</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" id="lineTotal" value="0.00" readonly>
                                <span class="input-group-text" id="lineTotalTooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="Calculated: qty × unit price">
                                    <i class="fas fa-info-circle text-muted"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label for="overrideEmail" class="form-label fw-semibold">Send invoice to different email <small class="text-muted fw-normal">(optional)</small></label>
                        <input type="email" class="form-control" id="overrideEmail" placeholder="email@example.com">
                        <div class="invalid-feedback" id="overrideEmailError">Please enter a valid email address</div>
                    </div>
                    
                    <div class="card mt-4" id="invoiceSummaryCard">
                        <div class="card-header py-2">
                            <h6 class="mb-0 small text-uppercase fw-bold">Invoice Summary</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold" id="summarySubtotal">&pound;0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">
                                    VAT <small id="vatRateDisplay" class="text-muted">(0%)</small>
                                </span>
                                <span class="fw-semibold" id="summaryVat">&pound;0.00</span>
                            </div>
                            <div id="vatNoteRow" class="d-none mb-2">
                                <small class="text-info" id="vatNote"></small>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold fs-5" id="summaryTotal">&pound;0.00</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" id="modalSubmitBtn" disabled>
                    <i class="fas fa-plus me-1"></i>
                    <span id="modalSubmitBtnText">Create invoice</span>
                </button>
            </div>
        </div>
    </div>
</div>

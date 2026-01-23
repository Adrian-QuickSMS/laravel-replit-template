var InvoiceCreditModal = (function() {
    var modal = null;
    var selectedCustomer = null;
    var customerSearchTimeout = null;
    var isCustomerLocked = false;
    var lockedCustomerData = null;
    var onSuccessCallback = null;
    
    var mockCustomers = [
        { id: 'ACC-001', name: 'TechStart Solutions', status: 'Live', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-002', name: 'EduLearn Institute', status: 'Live', vatRegistered: false, vatRate: 0, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-003', name: 'GreenEnergy Co', status: 'Test', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-004', name: 'HealthCare Plus', status: 'Live', vatRegistered: true, vatRate: 0, reverseCharge: true, vatCountry: 'DE' },
        { id: 'ACC-005', name: 'FoodService Network', status: 'Suspended', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-5678', name: 'Finance Ltd', status: 'Live', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-1234', name: 'Acme Corp', status: 'Live', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-006', name: 'RetailMax Ltd', status: 'Live', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-007', name: 'LogiTrans Systems', status: 'Test', vatRegistered: false, vatRate: 0, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-008', name: 'MediaWorks Agency', status: 'Live', vatRegistered: true, vatRate: 0, reverseCharge: true, vatCountry: 'FR' },
        { id: 'ACC-009', name: 'FinanceFirst Group', status: 'Live', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-010', name: 'BuildRight Construction', status: 'Test', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' }
    ];
    
    function searchCustomers(query) {
        var lowerQuery = query.toLowerCase();
        return mockCustomers.filter(function(c) {
            return c.name.toLowerCase().indexOf(lowerQuery) !== -1 || 
                   c.id.toLowerCase().indexOf(lowerQuery) !== -1;
        });
    }
    
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'Live': return 'bg-success';
            case 'Test': return 'bg-warning text-dark';
            case 'Suspended': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
    
    function formatCurrency(amount) {
        return '£' + parseFloat(amount).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    
    function renderCustomerDropdown(customers) {
        var dropdown = document.getElementById('customerTypeaheadDropdown');
        if (customers.length === 0) {
            dropdown.innerHTML = '<div class="customer-typeahead-no-results">No customers found</div>';
        } else {
            dropdown.innerHTML = customers.map(function(c) {
                return '<div class="customer-typeahead-item" data-id="' + c.id + '" data-name="' + c.name + '" data-status="' + c.status + '">' +
                    '<div class="d-flex align-items-center justify-content-between">' +
                        '<div>' +
                            '<span class="customer-name">' + c.name + '</span>' +
                            '<span class="customer-account-id ms-2">' + c.id + '</span>' +
                        '</div>' +
                        '<span class="badge ' + getStatusBadgeClass(c.status) + ' badge-sm">' + c.status + '</span>' +
                    '</div>' +
                '</div>';
            }).join('');
        }
        dropdown.classList.add('show');
    }
    
    function selectCustomer(id, name, status) {
        var customer = mockCustomers.find(function(c) { return c.id === id; });
        selectedCustomer = customer || { id: id, name: name, status: status, vatRegistered: false, vatRate: 0, reverseCharge: false, vatCountry: 'GB' };
        document.getElementById('selectedCustomerId').value = id;
        document.getElementById('customerSearchInput').classList.add('d-none');
        document.getElementById('selectedCustomerDisplay').classList.remove('d-none');
        document.getElementById('selectedCustomerName').textContent = name;
        document.getElementById('selectedCustomerAccountId').textContent = id;
        var statusBadge = document.getElementById('selectedCustomerStatus');
        statusBadge.textContent = status;
        statusBadge.className = 'badge ms-2 ' + getStatusBadgeClass(status);
        document.getElementById('customerTypeaheadDropdown').classList.remove('show');
        document.getElementById('customerSearchInput').classList.remove('is-invalid');
        updateInvoiceSummary();
        validateForm();
    }
    
    function clearCustomerSelection() {
        if (isCustomerLocked) return;
        selectedCustomer = null;
        document.getElementById('selectedCustomerId').value = '';
        document.getElementById('customerSearchInput').value = '';
        document.getElementById('customerSearchInput').classList.remove('d-none');
        document.getElementById('selectedCustomerDisplay').classList.add('d-none');
        updateInvoiceSummary();
        validateForm();
    }
    
    function updateInvoiceSummary() {
        var qty = parseFloat(document.getElementById('itemQuantity').value) || 0;
        var unitPrice = parseFloat(document.getElementById('itemUnitPrice').value) || 0;
        var subtotal = qty * unitPrice;
        
        document.getElementById('lineTotal').value = subtotal.toFixed(2);
        document.getElementById('summarySubtotal').textContent = formatCurrency(subtotal);
        
        var vatRate = 0;
        var vatAmount = 0;
        var vatNoteText = '';
        
        if (selectedCustomer) {
            if (selectedCustomer.reverseCharge) {
                vatRate = 0;
                vatNoteText = 'Reverse charge applies - customer is VAT registered in ' + selectedCustomer.vatCountry;
            } else if (selectedCustomer.vatRegistered) {
                vatRate = selectedCustomer.vatRate;
            }
        }
        
        vatAmount = subtotal * (vatRate / 100);
        
        document.getElementById('vatRateDisplay').textContent = '(' + vatRate + '%)';
        document.getElementById('summaryVat').textContent = formatCurrency(vatAmount);
        document.getElementById('summaryTotal').textContent = formatCurrency(subtotal + vatAmount);
        
        var vatNoteRow = document.getElementById('vatNoteRow');
        if (vatNoteText) {
            document.getElementById('vatNote').textContent = vatNoteText;
            vatNoteRow.classList.remove('d-none');
        } else {
            vatNoteRow.classList.add('d-none');
        }
    }
    
    function validateForm() {
        var isValid = true;
        
        if (!selectedCustomer) {
            isValid = false;
        }
        
        var description = document.getElementById('itemDescription').value.trim();
        if (!description) {
            isValid = false;
        }
        
        var qty = parseFloat(document.getElementById('itemQuantity').value);
        if (isNaN(qty) || qty < 0.01) {
            isValid = false;
        }
        
        var unitPrice = document.getElementById('itemUnitPrice').value.trim();
        var priceRegex = /^\d+(\.\d{1,4})?$/;
        if (!unitPrice || !priceRegex.test(unitPrice) || parseFloat(unitPrice) <= 0) {
            isValid = false;
        }
        
        var overrideEmail = document.getElementById('overrideEmail').value.trim();
        if (overrideEmail) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(overrideEmail)) {
                isValid = false;
            }
        }
        
        document.getElementById('modalSubmitBtn').disabled = !isValid;
        return isValid;
    }
    
    function resetForm() {
        selectedCustomer = isCustomerLocked ? lockedCustomerData : null;
        
        document.getElementById('createInvoiceCreditForm').reset();
        document.getElementById('selectedCustomerId').value = isCustomerLocked ? lockedCustomerData.id : '';
        document.getElementById('itemQuantity').value = '1';
        document.getElementById('lineTotal').value = '0.00';
        document.getElementById('summarySubtotal').textContent = '£0.00';
        document.getElementById('summaryVat').textContent = '£0.00';
        document.getElementById('summaryTotal').textContent = '£0.00';
        document.getElementById('vatRateDisplay').textContent = '(0%)';
        document.getElementById('vatNoteRow').classList.add('d-none');
        document.getElementById('descCharCount').textContent = '0';
        document.getElementById('modalErrorAlert').classList.add('d-none');
        
        if (!isCustomerLocked) {
            document.getElementById('customerSearchInput').classList.remove('d-none', 'is-invalid');
            document.getElementById('selectedCustomerDisplay').classList.add('d-none');
            document.getElementById('customerTypeaheadDropdown').classList.remove('show');
        }
        
        ['itemDescription', 'itemQuantity', 'itemUnitPrice', 'overrideEmail'].forEach(function(id) {
            document.getElementById(id).classList.remove('is-invalid');
        });
        
        document.getElementById('modalSubmitBtn').disabled = true;
        
        if (isCustomerLocked && lockedCustomerData) {
            updateInvoiceSummary();
        }
    }
    
    function showError(message, referenceId, customerId) {
        var errorAlert = document.getElementById('modalErrorAlert');
        document.getElementById('modalErrorMessage').textContent = message;
        document.getElementById('modalErrorRef').textContent = 'Error reference: ' + referenceId;
        document.getElementById('viewCustomerBillingBtn').href = '/admin/accounts/' + (customerId || 'unknown') + '/billing';
        errorAlert.classList.remove('d-none');
        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    function createDocument(payload) {
        return new Promise(function(resolve, reject) {
            setTimeout(function() {
                var shouldFail = Math.random() < 0.1;
                
                if (shouldFail) {
                    reject({
                        status: 'failed',
                        message: 'Unable to connect to Xero API. Please try again.',
                        referenceId: 'ERR-' + Date.now().toString(36).toUpperCase()
                    });
                    return;
                }
                
                var customer = mockCustomers.find(function(c) { return c.id === payload.customerAccountId; });
                var subtotal = payload.quantity * payload.unitPrice;
                var vatRate = 0;
                if (customer && !customer.reverseCharge && customer.vatRegistered) {
                    vatRate = customer.vatRate;
                }
                var vat = subtotal * (vatRate / 100);
                var total = subtotal + vat;
                
                var docType = payload.mode === 'invoice' ? 'invoice' : 'credit_note';
                var docPrefix = payload.mode === 'invoice' ? 'INV' : 'CN';
                var xeroDocId = 'xero-' + Math.random().toString(36).substr(2, 9);
                var xeroDocNumber = docPrefix + '-' + new Date().getFullYear() + '-' + String(Math.floor(Math.random() * 9999)).padStart(4, '0');
                
                var emailSent = Math.random() > 0.2;
                var sentToEmail = payload.overrideEmail || (customer ? customer.name.toLowerCase().replace(/\s+/g, '.') + '@example.com' : 'customer@example.com');
                
                resolve({
                    status: 'success',
                    xeroDocumentType: docType,
                    xeroDocumentId: xeroDocId,
                    xeroDocumentNumber: xeroDocNumber,
                    sentToEmail: emailSent ? sentToEmail : null,
                    emailSent: emailSent,
                    subtotal: subtotal.toFixed(2),
                    vat: vat.toFixed(2),
                    total: total.toFixed(2),
                    createdAt: new Date().toISOString()
                });
            }, 1500);
        });
    }
    
    function init(options) {
        options = options || {};
        
        modal = new bootstrap.Modal(document.getElementById('createInvoiceCreditModal'));
        onSuccessCallback = options.onSuccess || null;
        
        if (options.lockedCustomer) {
            isCustomerLocked = true;
            lockedCustomerData = options.lockedCustomer;
            selectedCustomer = lockedCustomerData;
            
            document.getElementById('customerTypeaheadWrapper').classList.add('d-none');
            document.getElementById('lockedCustomerDisplay').classList.remove('d-none');
            document.getElementById('lockedCustomerName').textContent = lockedCustomerData.name;
            document.getElementById('lockedCustomerAccountId').textContent = lockedCustomerData.id;
            var statusBadge = document.getElementById('lockedCustomerStatus');
            statusBadge.textContent = lockedCustomerData.status;
            statusBadge.className = 'badge ms-2 ' + getStatusBadgeClass(lockedCustomerData.status);
            document.getElementById('selectedCustomerId').value = lockedCustomerData.id;
            document.getElementById('customerLocked').value = 'true';
        }
        
        document.getElementById('customerSearchInput').addEventListener('click', function(e) {
            if (isCustomerLocked) return;
            renderCustomerDropdown(mockCustomers);
        });
        
        document.getElementById('customerSearchInput').addEventListener('input', function(e) {
            var query = e.target.value.trim();
            clearTimeout(customerSearchTimeout);
            
            if (query.length < 1) {
                renderCustomerDropdown(mockCustomers);
                return;
            }
            
            customerSearchTimeout = setTimeout(function() {
                var results = searchCustomers(query);
                renderCustomerDropdown(results);
            }, 200);
        });
        
        document.getElementById('customerTypeaheadDropdown').addEventListener('click', function(e) {
            var item = e.target.closest('.customer-typeahead-item');
            if (item) {
                selectCustomer(item.dataset.id, item.dataset.name, item.dataset.status);
            }
        });
        
        document.getElementById('clearCustomerBtn').addEventListener('click', clearCustomerSelection);
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.customer-typeahead-wrapper')) {
                document.getElementById('customerTypeaheadDropdown').classList.remove('show');
            }
        });
        
        document.getElementById('itemDescription').addEventListener('input', function(e) {
            document.getElementById('descCharCount').textContent = e.target.value.length;
            validateForm();
        });
        
        ['itemQuantity', 'itemUnitPrice'].forEach(function(id) {
            document.getElementById(id).addEventListener('input', function() {
                updateInvoiceSummary();
                validateForm();
            });
        });
        
        document.getElementById('overrideEmail').addEventListener('input', validateForm);
        
        document.getElementById('createInvoiceCreditModal').addEventListener('hidden.bs.modal', resetForm);
        
        document.getElementById('modalSubmitBtn').addEventListener('click', function() {
            var submitBtn = this;
            var originalBtnHtml = submitBtn.innerHTML;
            
            document.getElementById('modalErrorAlert').classList.add('d-none');
            
            var lineTotal = parseFloat(document.getElementById('itemQuantity').value) * parseFloat(document.getElementById('itemUnitPrice').value);
            var vatRate = 0;
            var vatApplied = 0;
            if (selectedCustomer && !selectedCustomer.reverseCharge && selectedCustomer.vatRegistered) {
                vatRate = selectedCustomer.vatRate;
                vatApplied = lineTotal * (vatRate / 100);
            }
            
            var payload = {
                customerAccountId: selectedCustomer ? selectedCustomer.id : null,
                customerName: selectedCustomer ? selectedCustomer.name : null,
                mode: document.getElementById('formMode').value,
                itemDescription: document.getElementById('itemDescription').value.trim(),
                quantity: parseFloat(document.getElementById('itemQuantity').value),
                unitPrice: parseFloat(document.getElementById('itemUnitPrice').value),
                overrideEmail: document.getElementById('overrideEmail').value.trim() || null,
                lineTotal: lineTotal,
                vatRate: vatRate,
                vatApplied: vatApplied,
                isLockedCustomer: isCustomerLocked
            };
            
            if (typeof AdminControlPlane !== 'undefined') {
                AdminControlPlane.logAdminAction('INVOICE_CREATE_ATTEMPT', payload.customerAccountId, {
                    mode: payload.mode,
                    lineTotal: payload.lineTotal,
                    sourceScreen: isCustomerLocked ? 'Admin > Accounts > Billing' : 'Admin > Invoices'
                });
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            
            createDocument(payload)
                .then(function(response) {
                    if (typeof AdminControlPlane !== 'undefined') {
                        var eventType = payload.mode === 'invoice' ? 'INVOICE_CREATED' : 'CREDIT_CREATED';
                        AdminControlPlane.logAdminAction(eventType, payload.customerAccountId, {
                            xeroDocumentId: response.xeroDocumentId,
                            xeroDocumentNumber: response.xeroDocumentNumber,
                            total: response.total,
                            emailSent: response.emailSent,
                            sourceScreen: isCustomerLocked ? 'Admin > Accounts > Billing' : 'Admin > Invoices'
                        });
                    }
                    
                    modal.hide();
                    
                    if (onSuccessCallback) {
                        onSuccessCallback(response, payload);
                    }
                })
                .catch(function(error) {
                    if (typeof AdminControlPlane !== 'undefined') {
                        AdminControlPlane.logAdminAction('INVOICE_CREATE_FAILED', payload.customerAccountId, {
                            mode: payload.mode,
                            error: error.message,
                            referenceId: error.referenceId,
                            sourceScreen: isCustomerLocked ? 'Admin > Accounts > Billing' : 'Admin > Invoices'
                        });
                    }
                    
                    showError(
                        error.message || 'An unexpected error occurred. Please try again.',
                        error.referenceId || 'ERR-UNKNOWN',
                        selectedCustomer ? selectedCustomer.id : null
                    );
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                });
        });
        
        new bootstrap.Tooltip(document.getElementById('lineTotalTooltip'));
    }
    
    function open(mode) {
        var isInvoice = mode === 'invoice';
        
        document.getElementById('formMode').value = mode;
        document.getElementById('modalTitleText').textContent = isInvoice ? 'Create Customer Invoice' : 'Create Customer Credit';
        document.getElementById('modalTitleIcon').className = isInvoice ? 'fas fa-file-invoice me-2' : 'fas fa-credit-card me-2';
        document.getElementById('modalDescription').textContent = isInvoice 
            ? 'Complete the form below to create a new customer invoice.' 
            : 'Complete the form below to create a new customer credit.';
        document.getElementById('modalSubmitBtnText').textContent = isInvoice ? 'Create invoice' : 'Create credit';
        
        resetForm();
        
        if (isCustomerLocked && lockedCustomerData) {
            selectedCustomer = lockedCustomerData;
            document.getElementById('selectedCustomerId').value = lockedCustomerData.id;
            updateInvoiceSummary();
        }
        
        modal.show();
    }
    
    return {
        init: init,
        open: open,
        hideError: function() {
            document.getElementById('modalErrorAlert').classList.add('d-none');
        },
        getSelectedCustomer: function() {
            return selectedCustomer;
        }
    };
})();

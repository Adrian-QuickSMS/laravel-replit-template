/**
 * Billing Services Layer
 * 
 * This module provides a clean service layer for billing operations.
 * All services return typed objects and are backend-ready without refactor.
 * 
 * Services:
 * - HubSpotBillingService: Source of truth for billingMode + creditLimit
 * - InternalBillingLedgerService: Internal balance tracking
 * - InvoicesService: Invoice management (list, create invoice/credit)
 * 
 * @version 1.0.0
 * @requires Promise support
 */

(function(global) {
    'use strict';

    /**
     * Configuration for service layer
     * Set useMockData to false to use real API endpoints
     */
    var ServiceConfig = {
        useMockData: true,
        apiBaseUrl: '/api/v1',
        hubspotApiUrl: '/api/hubspot',
        xeroApiUrl: '/api/xero',
        requestTimeout: 30000,
        retryAttempts: 3
    };

    /**
     * @typedef {Object} BillingProfile
     * @property {string} accountId - Internal account identifier
     * @property {string} billingMode - 'prepaid' or 'postpaid'
     * @property {number} creditLimit - Credit limit in account currency
     * @property {string|null} hubspotContactId - HubSpot contact/company ID
     * @property {string|null} hubspotUrl - Direct link to HubSpot record
     * @property {string} paymentTerms - Payment terms (e.g., 'Net 30', 'Immediate')
     * @property {string} currency - Currency code (e.g., 'GBP')
     * @property {boolean} vatRegistered - Whether account is VAT registered
     * @property {number} vatRate - VAT rate percentage
     * @property {boolean} reverseCharge - Whether reverse charge applies
     * @property {string} vatCountry - VAT country code
     * @property {string} lastUpdated - ISO timestamp of last update
     */

    /**
     * @typedef {Object} LedgerBalance
     * @property {string} accountId - Internal account identifier
     * @property {number} currentBalance - Current balance (positive = credit, negative = owed)
     * @property {string} lastUpdatedTimestamp - ISO timestamp of last balance update
     * @property {string} currency - Currency code
     */

    /**
     * @typedef {Object} Invoice
     * @property {string} number - Invoice number (e.g., 'INV-2024-0001')
     * @property {string} period - Billing period (e.g., 'Jan 2025')
     * @property {string} date - Invoice date (ISO format)
     * @property {string} dueDate - Due date (ISO format)
     * @property {string} status - Invoice status ('draft', 'issued', 'paid', 'overdue', 'cancelled')
     * @property {number} amountExVat - Amount excluding VAT
     * @property {number} vat - VAT amount
     * @property {number} total - Total amount including VAT
     * @property {number} outstanding - Outstanding amount
     * @property {string|null} xeroInvoiceId - Xero invoice ID if synced
     */

    /**
     * @typedef {Object} InvoiceFilters
     * @property {string} [customerAccountId] - Filter by customer account
     * @property {string} [status] - Filter by status
     * @property {string} [year] - Filter by year
     * @property {string} [invoiceNumber] - Search by invoice number
     * @property {number} [page] - Page number for pagination
     * @property {number} [pageSize] - Number of items per page
     */

    /**
     * @typedef {Object} CreateInvoiceRequest
     * @property {string} customerAccountId - Customer account ID
     * @property {string} type - 'invoice' or 'credit'
     * @property {Array<{description: string, quantity: number, unitPrice: number}>} lineItems - Invoice line items
     * @property {string} [overrideEmail] - Override email for invoice delivery
     * @property {string} [notes] - Additional notes
     */

    /**
     * @typedef {Object} ServiceResponse
     * @property {boolean} success - Whether operation succeeded
     * @property {*} data - Response data
     * @property {string} [error] - Error message if failed
     * @property {string} [errorCode] - Error code for programmatic handling
     */

    // ============================================================
    // Mock Data Store (will be replaced by real API calls)
    // ============================================================
    var MockDataStore = {
        hubspotProfiles: {
            'ACC-1234': {
                billingMode: 'prepaid',
                creditLimit: 0,
                hubspotContactId: 'HS-12345',
                hubspotUrl: 'https://app.hubspot.com/contacts/123456/company/12345',
                paymentTerms: 'Immediate',
                currency: 'GBP',
                vatRegistered: true,
                vatRate: 20,
                reverseCharge: false,
                vatCountry: 'GB',
                lastUpdated: '2026-01-23T10:30:00Z'
            },
            'ACC-5678': {
                billingMode: 'postpaid',
                creditLimit: 5000.00,
                hubspotContactId: 'HS-67890',
                hubspotUrl: 'https://app.hubspot.com/contacts/123456/company/67890',
                paymentTerms: 'Net 30',
                currency: 'GBP',
                vatRegistered: true,
                vatRate: 20,
                reverseCharge: false,
                vatCountry: 'GB',
                lastUpdated: '2026-01-22T14:15:00Z'
            },
            'ACC-7890': {
                billingMode: 'prepaid',
                creditLimit: 0,
                hubspotContactId: 'HS-78901',
                hubspotUrl: 'https://app.hubspot.com/contacts/123456/company/78901',
                paymentTerms: 'Immediate',
                currency: 'GBP',
                vatRegistered: false,
                vatRate: 0,
                reverseCharge: false,
                vatCountry: 'GB',
                lastUpdated: '2026-01-20T09:00:00Z'
            },
            'ACC-4567': {
                billingMode: 'postpaid',
                creditLimit: 2000.00,
                hubspotContactId: 'HS-45678',
                hubspotUrl: 'https://app.hubspot.com/contacts/123456/company/45678',
                paymentTerms: 'Net 14',
                currency: 'GBP',
                vatRegistered: true,
                vatRate: 20,
                reverseCharge: false,
                vatCountry: 'GB',
                lastUpdated: '2026-01-15T16:45:00Z'
            }
        },
        
        ledgerBalances: {
            'ACC-1234': { currentBalance: 2450.00, lastUpdatedTimestamp: '2026-01-23T10:30:00Z', currency: 'GBP' },
            'ACC-5678': { currentBalance: -1250.00, lastUpdatedTimestamp: '2026-01-22T14:15:00Z', currency: 'GBP' },
            'ACC-7890': { currentBalance: 100.00, lastUpdatedTimestamp: '2026-01-20T09:00:00Z', currency: 'GBP' },
            'ACC-4567': { currentBalance: -3500.00, lastUpdatedTimestamp: '2026-01-15T16:45:00Z', currency: 'GBP' }
        },
        
        accountDetails: {
            'ACC-1234': { name: 'Acme Corporation', status: 'live' },
            'ACC-5678': { name: 'Finance Ltd', status: 'live' },
            'ACC-7890': { name: 'NewClient Inc', status: 'test' },
            'ACC-4567': { name: 'TestCo Ltd', status: 'suspended' }
        },
        
        invoices: {
            'ACC-1234': [
                { number: 'INV-2024-0074', period: 'Dec 2024', date: '2024-12-25', dueDate: '2025-01-08', status: 'paid', amountExVat: 1815.00, vat: 363.00, total: 2178.00, outstanding: 0, xeroInvoiceId: 'XERO-74' },
                { number: 'INV-2024-0065', period: 'Nov 2024', date: '2024-11-25', dueDate: '2024-12-09', status: 'paid', amountExVat: 1420.00, vat: 284.00, total: 1704.00, outstanding: 0, xeroInvoiceId: 'XERO-65' },
                { number: 'INV-2024-0052', period: 'Oct 2024', date: '2024-10-25', dueDate: '2024-11-08', status: 'paid', amountExVat: 1680.00, vat: 336.00, total: 2016.00, outstanding: 0, xeroInvoiceId: 'XERO-52' }
            ],
            'ACC-5678': [
                { number: 'INV-2025-0003', period: 'Jan 2025', date: '2025-01-05', dueDate: '2025-02-04', status: 'draft', amountExVat: 1850.00, vat: 370.00, total: 2220.00, outstanding: 2220.00, xeroInvoiceId: null },
                { number: 'INV-2024-0026', period: 'Dec 2024', date: '2024-12-10', dueDate: '2025-01-09', status: 'issued', amountExVat: 2509.00, vat: 501.80, total: 3010.80, outstanding: 3010.80, xeroInvoiceId: 'XERO-26' },
                { number: 'INV-2024-0018', period: 'Nov 2024', date: '2024-11-10', dueDate: '2024-12-10', status: 'paid', amountExVat: 2150.00, vat: 430.00, total: 2580.00, outstanding: 0, xeroInvoiceId: 'XERO-18' },
                { number: 'INV-2023-0098', period: 'Dec 2023', date: '2023-12-15', dueDate: '2024-01-14', status: 'paid', amountExVat: 1920.00, vat: 384.00, total: 2304.00, outstanding: 0, xeroInvoiceId: 'XERO-98' },
                { number: 'INV-2023-0085', period: 'Nov 2023', date: '2023-11-10', dueDate: '2023-12-10', status: 'paid', amountExVat: 2050.00, vat: 410.00, total: 2460.00, outstanding: 0, xeroInvoiceId: 'XERO-85' }
            ],
            'ACC-7890': [],
            'ACC-4567': [
                { number: 'INV-2024-0045', period: 'Nov 2024', date: '2024-11-15', dueDate: '2024-11-29', status: 'overdue', amountExVat: 3500.00, vat: 700.00, total: 4200.00, outstanding: 4200.00, xeroInvoiceId: 'XERO-45' }
            ]
        }
    };

    // ============================================================
    // Utility Functions
    // ============================================================
    function simulateNetworkDelay(minMs, maxMs) {
        var delay = Math.floor(Math.random() * (maxMs - minMs + 1)) + minMs;
        return new Promise(function(resolve) {
            setTimeout(resolve, delay);
        });
    }

    function generateInvoiceNumber(type) {
        var prefix = type === 'credit' ? 'CRN' : 'INV';
        var year = new Date().getFullYear();
        var seq = String(Math.floor(Math.random() * 9000) + 1000);
        return prefix + '-' + year + '-' + seq;
    }

    function deepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    // ============================================================
    // HubSpot Billing Service
    // Source of truth for billingMode + creditLimit
    // ============================================================
    var HubSpotBillingService = (function() {
        
        /**
         * Get billing profile from HubSpot
         * @param {string} accountId - Internal account identifier
         * @returns {Promise<BillingProfile>}
         */
        function getBillingProfile(accountId) {
            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(200, 400).then(function() {
                    var profile = MockDataStore.hubspotProfiles[accountId];
                    if (!profile) {
                        return Promise.reject({
                            success: false,
                            error: 'Account not found in HubSpot',
                            errorCode: 'HUBSPOT_ACCOUNT_NOT_FOUND'
                        });
                    }
                    return {
                        accountId: accountId,
                        billingMode: profile.billingMode,
                        creditLimit: profile.creditLimit,
                        hubspotContactId: profile.hubspotContactId,
                        hubspotUrl: profile.hubspotUrl,
                        paymentTerms: profile.paymentTerms,
                        currency: profile.currency,
                        vatRegistered: profile.vatRegistered,
                        vatRate: profile.vatRate,
                        reverseCharge: profile.reverseCharge,
                        vatCountry: profile.vatCountry,
                        lastUpdated: profile.lastUpdated
                    };
                });
            }
            
            // Real API implementation placeholder
            return fetch(ServiceConfig.hubspotApiUrl + '/billing-profile/' + accountId, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('HubSpot API error: ' + response.status);
                }
                return response.json();
            });
        }

        /**
         * Update billing mode in HubSpot
         * @param {string} accountId - Internal account identifier
         * @param {'prepaid'|'postpaid'} mode - New billing mode
         * @returns {Promise<ServiceResponse>}
         */
        function updateBillingMode(accountId, mode) {
            if (!['prepaid', 'postpaid'].includes(mode)) {
                return Promise.reject({
                    success: false,
                    error: 'Invalid billing mode. Must be "prepaid" or "postpaid"',
                    errorCode: 'INVALID_BILLING_MODE'
                });
            }

            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(300, 600).then(function() {
                    var profile = MockDataStore.hubspotProfiles[accountId];
                    if (!profile) {
                        return Promise.reject({
                            success: false,
                            error: 'Account not found in HubSpot',
                            errorCode: 'HUBSPOT_ACCOUNT_NOT_FOUND'
                        });
                    }
                    
                    var previousMode = profile.billingMode;
                    profile.billingMode = mode;
                    profile.lastUpdated = new Date().toISOString();
                    
                    if (mode === 'prepaid') {
                        profile.creditLimit = 0;
                    }
                    
                    console.log('[HubSpotBillingService] Billing mode updated', {
                        accountId: accountId,
                        previousMode: previousMode,
                        newMode: mode,
                        timestamp: profile.lastUpdated
                    });
                    
                    return {
                        success: true,
                        data: {
                            accountId: accountId,
                            billingMode: mode,
                            previousMode: previousMode,
                            hubspotSynced: true,
                            syncTimestamp: profile.lastUpdated
                        }
                    };
                });
            }
            
            // Real API implementation placeholder
            return fetch(ServiceConfig.hubspotApiUrl + '/billing-mode/' + accountId, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ billingMode: mode })
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('HubSpot API error: ' + response.status);
                }
                return response.json();
            });
        }

        /**
         * Update credit limit in HubSpot
         * @param {string} accountId - Internal account identifier
         * @param {number} creditLimit - New credit limit (must be >= 0 and <= 1,000,000)
         * @returns {Promise<ServiceResponse>}
         */
        function updateCreditLimit(accountId, creditLimit) {
            if (typeof creditLimit !== 'number' || creditLimit < 0 || creditLimit > 1000000) {
                return Promise.reject({
                    success: false,
                    error: 'Invalid credit limit. Must be between 0 and 1,000,000',
                    errorCode: 'INVALID_CREDIT_LIMIT'
                });
            }

            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(300, 600).then(function() {
                    var profile = MockDataStore.hubspotProfiles[accountId];
                    if (!profile) {
                        return Promise.reject({
                            success: false,
                            error: 'Account not found in HubSpot',
                            errorCode: 'HUBSPOT_ACCOUNT_NOT_FOUND'
                        });
                    }
                    
                    if (profile.billingMode === 'prepaid') {
                        return Promise.reject({
                            success: false,
                            error: 'Cannot set credit limit for prepaid accounts',
                            errorCode: 'CREDIT_LIMIT_NOT_ALLOWED'
                        });
                    }
                    
                    var previousLimit = profile.creditLimit;
                    profile.creditLimit = creditLimit;
                    profile.lastUpdated = new Date().toISOString();
                    
                    console.log('[HubSpotBillingService] Credit limit updated', {
                        accountId: accountId,
                        previousLimit: previousLimit,
                        newLimit: creditLimit,
                        timestamp: profile.lastUpdated
                    });
                    
                    return {
                        success: true,
                        data: {
                            accountId: accountId,
                            creditLimit: creditLimit,
                            previousLimit: previousLimit,
                            hubspotSynced: true,
                            syncTimestamp: profile.lastUpdated
                        }
                    };
                });
            }
            
            // Real API implementation placeholder
            return fetch(ServiceConfig.hubspotApiUrl + '/credit-limit/' + accountId, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ creditLimit: creditLimit })
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('HubSpot API error: ' + response.status);
                }
                return response.json();
            });
        }

        return {
            getBillingProfile: getBillingProfile,
            updateBillingMode: updateBillingMode,
            updateCreditLimit: updateCreditLimit
        };
    })();

    // ============================================================
    // Internal Billing Ledger Service
    // Tracks account balances internally
    // ============================================================
    var InternalBillingLedgerService = (function() {
        
        /**
         * Get current balance for an account
         * @param {string} accountId - Internal account identifier
         * @returns {Promise<LedgerBalance>}
         */
        function getBalance(accountId) {
            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(100, 200).then(function() {
                    var balance = MockDataStore.ledgerBalances[accountId];
                    if (!balance) {
                        return {
                            accountId: accountId,
                            currentBalance: 0,
                            lastUpdatedTimestamp: new Date().toISOString(),
                            currency: 'GBP'
                        };
                    }
                    return {
                        accountId: accountId,
                        currentBalance: balance.currentBalance,
                        lastUpdatedTimestamp: balance.lastUpdatedTimestamp,
                        currency: balance.currency
                    };
                });
            }
            
            // Real API implementation placeholder
            return fetch(ServiceConfig.apiBaseUrl + '/ledger/balance/' + accountId, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('Ledger API error: ' + response.status);
                }
                return response.json();
            });
        }

        /**
         * Calculate available credit based on billing mode and balance
         * @param {string} billingMode - 'prepaid' or 'postpaid'
         * @param {number} currentBalance - Current balance
         * @param {number} creditLimit - Credit limit (postpaid only)
         * @returns {number} Available credit
         */
        function calculateAvailableCredit(billingMode, currentBalance, creditLimit) {
            if (billingMode === 'prepaid') {
                return Math.max(0, currentBalance);
            }
            return currentBalance + creditLimit;
        }

        return {
            getBalance: getBalance,
            calculateAvailableCredit: calculateAvailableCredit
        };
    })();

    // ============================================================
    // Invoices Service
    // Invoice and credit note management with Xero integration
    // ============================================================
    var InvoicesService = (function() {
        
        /**
         * List invoices with optional filters
         * @param {InvoiceFilters} [filters] - Optional filters
         * @returns {Promise<{invoices: Invoice[], total: number, page: number, pageSize: number}>}
         */
        function listInvoices(filters) {
            filters = filters || {};
            
            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(200, 400).then(function() {
                    var allInvoices = [];
                    
                    if (filters.customerAccountId) {
                        allInvoices = deepClone(MockDataStore.invoices[filters.customerAccountId] || []);
                    } else {
                        Object.keys(MockDataStore.invoices).forEach(function(accountId) {
                            var accountInvoices = MockDataStore.invoices[accountId].map(function(inv) {
                                var invoiceCopy = deepClone(inv);
                                invoiceCopy.accountId = accountId;
                                invoiceCopy.accountName = MockDataStore.accountDetails[accountId]?.name || 'Unknown';
                                return invoiceCopy;
                            });
                            allInvoices = allInvoices.concat(accountInvoices);
                        });
                    }
                    
                    if (filters.status) {
                        allInvoices = allInvoices.filter(function(inv) {
                            return inv.status === filters.status;
                        });
                    }
                    
                    if (filters.year) {
                        allInvoices = allInvoices.filter(function(inv) {
                            return inv.date.indexOf(filters.year) !== -1;
                        });
                    }
                    
                    if (filters.invoiceNumber) {
                        var searchTerm = filters.invoiceNumber.toLowerCase();
                        allInvoices = allInvoices.filter(function(inv) {
                            return inv.number.toLowerCase().indexOf(searchTerm) !== -1;
                        });
                    }
                    
                    allInvoices.sort(function(a, b) {
                        return new Date(b.date).getTime() - new Date(a.date).getTime();
                    });
                    
                    var page = filters.page || 1;
                    var pageSize = filters.pageSize || 25;
                    var start = (page - 1) * pageSize;
                    var paginatedInvoices = allInvoices.slice(start, start + pageSize);
                    
                    return {
                        invoices: paginatedInvoices,
                        total: allInvoices.length,
                        page: page,
                        pageSize: pageSize
                    };
                });
            }
            
            // Real API implementation placeholder
            var queryParams = new URLSearchParams(filters).toString();
            return fetch(ServiceConfig.apiBaseUrl + '/invoices?' + queryParams, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('Invoices API error: ' + response.status);
                }
                return response.json();
            });
        }

        /**
         * Create a new invoice
         * @param {CreateInvoiceRequest} request - Invoice creation request
         * @returns {Promise<ServiceResponse>}
         */
        function createInvoice(request) {
            return createDocument(request, 'invoice');
        }

        /**
         * Create a new credit note
         * @param {CreateInvoiceRequest} request - Credit note creation request
         * @returns {Promise<ServiceResponse>}
         */
        function createCredit(request) {
            return createDocument(request, 'credit');
        }

        /**
         * Internal function to create invoice or credit
         * @param {CreateInvoiceRequest} request - Document creation request
         * @param {'invoice'|'credit'} type - Document type
         * @returns {Promise<ServiceResponse>}
         */
        function createDocument(request, type) {
            if (!request.customerAccountId) {
                return Promise.reject({
                    success: false,
                    error: 'Customer account ID is required',
                    errorCode: 'MISSING_ACCOUNT_ID'
                });
            }

            if (!request.lineItems || request.lineItems.length === 0) {
                return Promise.reject({
                    success: false,
                    error: 'At least one line item is required',
                    errorCode: 'MISSING_LINE_ITEMS'
                });
            }

            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(500, 1000).then(function() {
                    var hubspotProfile = MockDataStore.hubspotProfiles[request.customerAccountId];
                    if (!hubspotProfile) {
                        return Promise.reject({
                            success: false,
                            error: 'Customer account not found',
                            errorCode: 'ACCOUNT_NOT_FOUND'
                        });
                    }
                    
                    var amountExVat = 0;
                    request.lineItems.forEach(function(item) {
                        amountExVat += item.quantity * item.unitPrice;
                    });
                    
                    var vatRate = hubspotProfile.vatRegistered && !hubspotProfile.reverseCharge ? hubspotProfile.vatRate : 0;
                    var vat = amountExVat * (vatRate / 100);
                    var total = amountExVat + vat;
                    
                    var documentNumber = generateInvoiceNumber(type);
                    var now = new Date();
                    var dueDate = new Date(now);
                    
                    if (hubspotProfile.paymentTerms === 'Immediate') {
                        dueDate = now;
                    } else if (hubspotProfile.paymentTerms === 'Net 14') {
                        dueDate.setDate(dueDate.getDate() + 14);
                    } else if (hubspotProfile.paymentTerms === 'Net 30') {
                        dueDate.setDate(dueDate.getDate() + 30);
                    } else {
                        dueDate.setDate(dueDate.getDate() + 30);
                    }
                    
                    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    var period = monthNames[now.getMonth()] + ' ' + now.getFullYear();
                    
                    var newDocument = {
                        number: documentNumber,
                        period: period,
                        date: now.toISOString().split('T')[0],
                        dueDate: dueDate.toISOString().split('T')[0],
                        status: 'draft',
                        amountExVat: type === 'credit' ? -Math.abs(amountExVat) : amountExVat,
                        vat: type === 'credit' ? -Math.abs(vat) : vat,
                        total: type === 'credit' ? -Math.abs(total) : total,
                        outstanding: type === 'credit' ? 0 : total,
                        xeroInvoiceId: null
                    };
                    
                    if (!MockDataStore.invoices[request.customerAccountId]) {
                        MockDataStore.invoices[request.customerAccountId] = [];
                    }
                    MockDataStore.invoices[request.customerAccountId].unshift(newDocument);
                    
                    console.log('[InvoicesService] Document created', {
                        type: type,
                        documentNumber: documentNumber,
                        accountId: request.customerAccountId,
                        total: total,
                        timestamp: now.toISOString()
                    });
                    
                    return {
                        success: true,
                        data: {
                            documentNumber: documentNumber,
                            type: type,
                            accountId: request.customerAccountId,
                            amountExVat: newDocument.amountExVat,
                            vat: newDocument.vat,
                            total: newDocument.total,
                            xeroSynced: false,
                            xeroInvoiceId: null,
                            createdAt: now.toISOString()
                        }
                    };
                });
            }
            
            // Real API implementation placeholder
            return fetch(ServiceConfig.apiBaseUrl + '/invoices', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.assign({}, request, { type: type }))
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('Invoices API error: ' + response.status);
                }
                return response.json();
            });
        }

        /**
         * Get invoice details by number
         * @param {string} invoiceNumber - Invoice number
         * @returns {Promise<Invoice>}
         */
        function getInvoice(invoiceNumber) {
            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(100, 200).then(function() {
                    var found = null;
                    Object.keys(MockDataStore.invoices).forEach(function(accountId) {
                        MockDataStore.invoices[accountId].forEach(function(inv) {
                            if (inv.number === invoiceNumber) {
                                found = deepClone(inv);
                                found.accountId = accountId;
                                found.accountName = MockDataStore.accountDetails[accountId]?.name || 'Unknown';
                            }
                        });
                    });
                    
                    if (!found) {
                        return Promise.reject({
                            success: false,
                            error: 'Invoice not found',
                            errorCode: 'INVOICE_NOT_FOUND'
                        });
                    }
                    
                    return found;
                });
            }
            
            // Real API implementation placeholder
            return fetch(ServiceConfig.apiBaseUrl + '/invoices/' + invoiceNumber, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('Invoices API error: ' + response.status);
                }
                return response.json();
            });
        }

        /**
         * Sync invoice to Xero
         * @param {string} invoiceNumber - Invoice number to sync
         * @returns {Promise<ServiceResponse>}
         */
        function syncToXero(invoiceNumber) {
            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(500, 1500).then(function() {
                    var found = null;
                    var foundAccountId = null;
                    
                    Object.keys(MockDataStore.invoices).forEach(function(accountId) {
                        MockDataStore.invoices[accountId].forEach(function(inv) {
                            if (inv.number === invoiceNumber) {
                                found = inv;
                                foundAccountId = accountId;
                            }
                        });
                    });
                    
                    if (!found) {
                        return Promise.reject({
                            success: false,
                            error: 'Invoice not found',
                            errorCode: 'INVOICE_NOT_FOUND'
                        });
                    }
                    
                    var xeroId = 'XERO-' + invoiceNumber.split('-').pop();
                    found.xeroInvoiceId = xeroId;
                    
                    console.log('[InvoicesService] Synced to Xero', {
                        invoiceNumber: invoiceNumber,
                        xeroInvoiceId: xeroId,
                        timestamp: new Date().toISOString()
                    });
                    
                    return {
                        success: true,
                        data: {
                            invoiceNumber: invoiceNumber,
                            xeroInvoiceId: xeroId,
                            syncedAt: new Date().toISOString()
                        }
                    };
                });
            }
            
            // Real API implementation placeholder
            return fetch(ServiceConfig.xeroApiUrl + '/sync/' + invoiceNumber, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('Xero API error: ' + response.status);
                }
                return response.json();
            });
        }

        return {
            listInvoices: listInvoices,
            createInvoice: createInvoice,
            createCredit: createCredit,
            getInvoice: getInvoice,
            syncToXero: syncToXero
        };
    })();

    // ============================================================
    // Account Details Service
    // Basic account information (name, status)
    // ============================================================
    var AccountDetailsService = (function() {
        
        /**
         * Get basic account details
         * @param {string} accountId - Internal account identifier
         * @returns {Promise<{accountId: string, name: string, status: string}>}
         */
        function getAccountDetails(accountId) {
            if (ServiceConfig.useMockData) {
                return simulateNetworkDelay(50, 100).then(function() {
                    var details = MockDataStore.accountDetails[accountId];
                    if (!details) {
                        return {
                            accountId: accountId,
                            name: 'Unknown Account',
                            status: 'unknown'
                        };
                    }
                    return {
                        accountId: accountId,
                        name: details.name,
                        status: details.status
                    };
                });
            }
            
            // Real API implementation placeholder
            return fetch(ServiceConfig.apiBaseUrl + '/accounts/' + accountId, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error('Accounts API error: ' + response.status);
                }
                return response.json();
            });
        }

        return {
            getAccountDetails: getAccountDetails
        };
    })();

    // ============================================================
    // Unified Billing Facade
    // Combines all services for convenient access
    // ============================================================
    var BillingFacade = (function() {
        
        /**
         * Load complete billing data for an account
         * Combines HubSpot profile, ledger balance, and account details
         * @param {string} accountId - Internal account identifier
         * @returns {Promise<Object>}
         */
        function loadCompleteBillingData(accountId) {
            return Promise.all([
                HubSpotBillingService.getBillingProfile(accountId),
                InternalBillingLedgerService.getBalance(accountId),
                AccountDetailsService.getAccountDetails(accountId)
            ]).then(function(results) {
                var profile = results[0];
                var balance = results[1];
                var details = results[2];
                
                var availableCredit = InternalBillingLedgerService.calculateAvailableCredit(
                    profile.billingMode,
                    balance.currentBalance,
                    profile.creditLimit
                );
                
                return {
                    accountId: accountId,
                    name: details.name,
                    status: details.status,
                    hubspotId: profile.hubspotContactId,
                    hubspotUrl: profile.hubspotUrl,
                    billingMode: profile.billingMode,
                    currentBalance: balance.currentBalance,
                    creditLimit: profile.creditLimit,
                    availableCredit: availableCredit,
                    paymentTerms: profile.paymentTerms,
                    currency: profile.currency,
                    vatRegistered: profile.vatRegistered,
                    vatRate: profile.vatRate,
                    reverseCharge: profile.reverseCharge,
                    vatCountry: profile.vatCountry,
                    lastUpdated: profile.lastUpdated
                };
            });
        }

        /**
         * Check if account has outstanding invoices
         * @param {string} accountId - Internal account identifier
         * @returns {Promise<{hasOutstanding: boolean, totalOutstanding: number, count: number}>}
         */
        function checkOutstandingInvoices(accountId) {
            return InvoicesService.listInvoices({ customerAccountId: accountId }).then(function(result) {
                var outstanding = result.invoices.filter(function(inv) {
                    return inv.outstanding > 0;
                });
                var totalOutstanding = outstanding.reduce(function(sum, inv) {
                    return sum + inv.outstanding;
                }, 0);
                
                return {
                    hasOutstanding: outstanding.length > 0,
                    totalOutstanding: totalOutstanding,
                    count: outstanding.length
                };
            });
        }

        return {
            loadCompleteBillingData: loadCompleteBillingData,
            checkOutstandingInvoices: checkOutstandingInvoices
        };
    })();

    // ============================================================
    // Export Services to Global Scope
    // ============================================================
    global.BillingServices = {
        config: ServiceConfig,
        HubSpotBillingService: HubSpotBillingService,
        InternalBillingLedgerService: InternalBillingLedgerService,
        InvoicesService: InvoicesService,
        AccountDetailsService: AccountDetailsService,
        BillingFacade: BillingFacade
    };

    console.log('[BillingServices] Initialized with mock data:', ServiceConfig.useMockData);

})(typeof window !== 'undefined' ? window : global);

/**
 * NumbersAdminService - Backend-Ready Service Layer for Admin Numbers Management
 * 
 * This service provides a clean abstraction between the UI and backend.
 * All methods return Promises, making it trivial to swap mock implementations
 * for real API calls when backend endpoints become available.
 * 
 * To switch to real endpoints:
 * 1. Set NumbersAdminService.useMockData = false
 * 2. Configure NumbersAdminService.apiBaseUrl
 * 3. Each method will then call the real endpoint
 */

const NumbersAdminService = (function() {
    'use strict';

    const config = {
        useMockData: true,
        apiBaseUrl: '/api/admin/numbers',
        defaultDelay: { min: 200, max: 600 },
        pageSize: 20
    };

    const mockDatabase = {
        numbers: [
            { id: 'NUM-001', number: '+447700900123', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Acme Corporation', accountId: 'ACC-001', subAccount: 'Marketing', subAccountId: 'SUB-001', capabilities: ['senderid', 'inbox', 'optout'], cost: 2.00, supplier: 'Sinch', route: 'UK-Direct-Premium', network: 'Vodafone UK', portedTo: null, apiWebhookUrl: null, optoutConfig: { keywords: 'STOP, UNSUBSCRIBE, QUIT, END', reply: 'You have been unsubscribed.', forward: null }, created: '2025-10-15', modified: '2025-12-01' },
            { id: 'NUM-002', number: '+447700900456', country: 'UK', type: 'vmn', status: 'active', mode: 'api', account: 'Finance Ltd', accountId: 'ACC-002', subAccount: 'Retail', subAccountId: 'SUB-002', capabilities: ['api'], cost: 2.00, supplier: 'Sinch', route: 'UK-Direct-Standard', network: 'EE', portedTo: 'Three UK', apiWebhookUrl: 'https://api.finance-ltd.com/sms/webhook', optoutConfig: null, created: '2025-09-20', modified: '2025-11-15' },
            { id: 'NUM-003', number: '+447700900789', country: 'UK', type: 'vmn', status: 'suspended', mode: 'portal', account: 'Tech Solutions', accountId: 'ACC-003', subAccount: 'Support', subAccountId: 'SUB-003', capabilities: ['senderid', 'optout'], cost: 2.00, supplier: 'Vonage', route: 'UK-Hybrid', network: 'O2', portedTo: null, apiWebhookUrl: null, optoutConfig: { keywords: 'STOP', reply: 'Unsubscribed', forward: null }, created: '2025-08-10', modified: '2025-10-20' },
            { id: 'NUM-004', number: 'OFFER', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Retail Group', accountId: 'ACC-004', subAccount: 'Promotions', subAccountId: 'SUB-004', capabilities: ['optout', 'api'], cost: 5.00, supplier: 'OpenMarket', route: 'UK-Shortcode', network: 'All UK', portedTo: null, apiWebhookUrl: null, optoutConfig: { keywords: 'STOP, END', reply: 'You have opted out of OFFER messages.', forward: 'ops@retailgroup.com' }, created: '2025-07-01', modified: '2025-09-15' },
            { id: 'NUM-005', number: '60123', country: 'UK', type: 'dedicated_shortcode', status: 'active', mode: 'api', account: 'Healthcare UK', accountId: 'ACC-005', subAccount: 'Appointments', subAccountId: 'SUB-005', capabilities: ['senderid', 'inbox', 'optout', 'api'], cost: 250.00, supplier: 'Twilio', route: 'UK-Premium-Shortcode', network: 'All UK', portedTo: null, apiWebhookUrl: 'https://healthcare-uk.nhs.net/sms/inbound', optoutConfig: { keywords: 'STOP, UNSUBSCRIBE', reply: 'You have been removed from NHS appointment reminders.', forward: null }, created: '2025-05-20', modified: '2025-08-30' },
            { id: 'NUM-006', number: '+447700901111', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Acme Corporation', accountId: 'ACC-001', subAccount: 'Sales', subAccountId: 'SUB-006', capabilities: ['senderid', 'inbox'], cost: 2.00, supplier: 'Sinch', route: 'UK-Direct-Premium', network: 'Vodafone UK', portedTo: null, apiWebhookUrl: null, optoutConfig: null, created: '2025-11-01', modified: '2025-12-10' },
            { id: 'NUM-007', number: '+447700902222', country: 'UK', type: 'vmn', status: 'pending', mode: 'portal', account: 'StartupCo', accountId: 'ACC-006', subAccount: 'Main', subAccountId: 'SUB-007', capabilities: [], cost: 2.00, supplier: 'Sinch', route: 'UK-Direct-Standard', network: 'Three', portedTo: null, apiWebhookUrl: null, optoutConfig: null, created: '2026-01-10', modified: '2026-01-10' },
            { id: 'NUM-008', number: 'DEALS', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'api', account: 'Retail Group', accountId: 'ACC-004', subAccount: 'Marketing', subAccountId: 'SUB-008', capabilities: ['optout', 'api'], cost: 5.00, supplier: 'OpenMarket', route: 'UK-Shortcode', network: 'All UK', portedTo: null, apiWebhookUrl: 'https://api.retailgroup.com/deals/inbound', optoutConfig: { keywords: 'STOP', reply: 'Unsubscribed from DEALS.', forward: null }, created: '2025-06-15', modified: '2025-11-20' },
            { id: 'NUM-009', number: '+447700903333', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Finance Ltd', accountId: 'ACC-002', subAccount: 'Corporate', subAccountId: 'SUB-009', capabilities: ['senderid', 'optout'], cost: 2.00, supplier: 'Vonage', route: 'UK-Hybrid', network: 'EE', portedTo: null, apiWebhookUrl: null, optoutConfig: { keywords: 'STOP, QUIT', reply: 'You have been removed.', forward: null }, created: '2025-10-05', modified: '2025-12-05' },
            { id: 'NUM-010', number: '+447700904444', country: 'UK', type: 'vmn', status: 'suspended', mode: 'portal', account: 'Old Client Ltd', accountId: 'ACC-007', subAccount: 'Legacy', subAccountId: 'SUB-010', capabilities: [], cost: 2.00, supplier: 'Sinch', route: 'UK-Direct-Standard', network: 'O2', portedTo: null, apiWebhookUrl: null, optoutConfig: null, created: '2024-01-15', modified: '2025-06-01' },
            { id: 'NUM-011', number: '+353871234567', country: 'IE', type: 'vmn', status: 'active', mode: 'portal', account: 'Dublin Retail', accountId: 'ACC-008', subAccount: 'Main', subAccountId: 'SUB-011', capabilities: ['senderid', 'inbox', 'optout'], cost: 3.50, supplier: 'Sinch', route: 'IE-Direct', network: 'Vodafone IE', portedTo: null, apiWebhookUrl: null, optoutConfig: { keywords: 'STOP', reply: 'Unsubscribed', forward: null }, created: '2025-09-01', modified: '2025-11-10' },
            { id: 'NUM-012', number: '+33612345678', country: 'FR', type: 'vmn', status: 'active', mode: 'api', account: 'Euro Corp', accountId: 'ACC-009', subAccount: 'France', subAccountId: 'SUB-012', capabilities: ['api'], cost: 4.00, supplier: 'Vonage', route: 'FR-Direct', network: 'Orange FR', portedTo: null, apiWebhookUrl: 'https://api.eurocorp.eu/sms/fr', optoutConfig: null, created: '2025-08-20', modified: '2025-10-15' }
        ],
        accounts: [
            { id: 'ACC-001', name: 'Acme Corporation', status: 'active' },
            { id: 'ACC-002', name: 'Finance Ltd', status: 'active' },
            { id: 'ACC-003', name: 'Tech Solutions', status: 'active' },
            { id: 'ACC-004', name: 'Retail Group', status: 'active' },
            { id: 'ACC-005', name: 'Healthcare UK', status: 'active' },
            { id: 'ACC-006', name: 'StartupCo', status: 'active' },
            { id: 'ACC-007', name: 'Old Client Ltd', status: 'suspended' },
            { id: 'ACC-008', name: 'Dublin Retail', status: 'active' },
            { id: 'ACC-009', name: 'Euro Corp', status: 'active' }
        ],
        subAccounts: [
            { id: 'SUB-001', accountId: 'ACC-001', name: 'Marketing', status: 'active' },
            { id: 'SUB-002', accountId: 'ACC-002', name: 'Retail', status: 'active' },
            { id: 'SUB-003', accountId: 'ACC-003', name: 'Support', status: 'active' },
            { id: 'SUB-004', accountId: 'ACC-004', name: 'Promotions', status: 'active' },
            { id: 'SUB-005', accountId: 'ACC-005', name: 'Appointments', status: 'active' },
            { id: 'SUB-006', accountId: 'ACC-001', name: 'Sales', status: 'active' },
            { id: 'SUB-007', accountId: 'ACC-006', name: 'Main', status: 'active' },
            { id: 'SUB-008', accountId: 'ACC-004', name: 'Marketing', status: 'active' },
            { id: 'SUB-009', accountId: 'ACC-002', name: 'Corporate', status: 'active' },
            { id: 'SUB-010', accountId: 'ACC-007', name: 'Legacy', status: 'suspended' },
            { id: 'SUB-011', accountId: 'ACC-008', name: 'Main', status: 'active' },
            { id: 'SUB-012', accountId: 'ACC-009', name: 'France', status: 'active' },
            { id: 'SUB-013', accountId: 'ACC-001', name: 'Operations', status: 'active' },
            { id: 'SUB-014', accountId: 'ACC-002', name: 'Treasury', status: 'active' }
        ],
        auditHistory: {}
    };

    function generateAuditId() {
        return 'AUD-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9).toUpperCase();
    }

    function addAuditEntry(entityId, eventType, details) {
        if (!mockDatabase.auditHistory[entityId]) {
            mockDatabase.auditHistory[entityId] = [];
        }
        const entry = {
            id: generateAuditId(),
            timestamp: new Date().toISOString(),
            eventType: eventType,
            adminUser: getCurrentAdminUser(),
            details: details
        };
        mockDatabase.auditHistory[entityId].unshift(entry);
        return entry;
    }

    function getCurrentAdminUser() {
        if (typeof ADMIN_AUDIT !== 'undefined' && ADMIN_AUDIT.adminUser) {
            return ADMIN_AUDIT.adminUser;
        }
        return {
            id: 'ADMIN-001',
            email: 'admin@quicksms.co.uk',
            name: 'Admin User',
            role: 'super_admin'
        };
    }

    function simulateDelay() {
        const delay = Math.floor(Math.random() * (config.defaultDelay.max - config.defaultDelay.min + 1)) + config.defaultDelay.min;
        return new Promise(resolve => setTimeout(resolve, delay));
    }

    function deepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    async function listNumbers(filters = {}, paging = {}, sorting = {}) {
        if (!config.useMockData) {
            const params = new URLSearchParams();
            Object.entries(filters).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    value.forEach(v => params.append(key, v));
                } else if (value) {
                    params.append(key, value);
                }
            });
            params.append('page', paging.page || 1);
            params.append('pageSize', paging.pageSize || config.pageSize);
            if (sorting.field) {
                params.append('sortBy', sorting.field);
                params.append('sortDir', sorting.direction || 'asc');
            }
            const response = await fetch(`${config.apiBaseUrl}?${params.toString()}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        let result = deepClone(mockDatabase.numbers);

        if (filters.country && filters.country.length > 0) {
            result = result.filter(n => filters.country.includes(n.country));
        }
        if (filters.type && filters.type.length > 0) {
            result = result.filter(n => filters.type.includes(n.type));
        }
        if (filters.status && filters.status.length > 0) {
            result = result.filter(n => filters.status.includes(n.status));
        }
        if (filters.mode && filters.mode.length > 0) {
            result = result.filter(n => filters.mode.includes(n.mode));
        }
        if (filters.capability && filters.capability.length > 0) {
            result = result.filter(n => 
                filters.capability.some(cap => n.capabilities.includes(cap))
            );
        }
        if (filters.account && filters.account.length > 0) {
            result = result.filter(n => filters.account.includes(n.account));
        }
        if (filters.subAccount && filters.subAccount.length > 0) {
            result = result.filter(n => filters.subAccount.includes(n.subAccount));
        }
        if (filters.supplier && filters.supplier.length > 0) {
            result = result.filter(n => filters.supplier.includes(n.supplier));
        }
        if (filters.search) {
            const searchLower = filters.search.toLowerCase();
            result = result.filter(n => 
                n.number.toLowerCase().includes(searchLower) ||
                n.account.toLowerCase().includes(searchLower) ||
                n.id.toLowerCase().includes(searchLower)
            );
        }

        if (sorting.field) {
            result.sort((a, b) => {
                let aVal = a[sorting.field];
                let bVal = b[sorting.field];
                if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                if (typeof bVal === 'string') bVal = bVal.toLowerCase();
                if (aVal < bVal) return sorting.direction === 'asc' ? -1 : 1;
                if (aVal > bVal) return sorting.direction === 'asc' ? 1 : -1;
                return 0;
            });
        }

        const totalCount = result.length;
        const page = paging.page || 1;
        const pageSize = paging.pageSize || config.pageSize;
        const startIndex = (page - 1) * pageSize;
        const paginatedData = result.slice(startIndex, startIndex + pageSize);

        return {
            success: true,
            data: paginatedData,
            pagination: {
                page: page,
                pageSize: pageSize,
                totalCount: totalCount,
                totalPages: Math.ceil(totalCount / pageSize)
            },
            meta: {
                filters: filters,
                sorting: sorting
            }
        };
    }

    async function getNumber(id) {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }

        return { success: true, data: deepClone(number) };
    }

    async function suspendNumber(id, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}/suspend`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }
        if (number.status === 'suspended') {
            return { success: false, error: 'Number is already suspended', code: 'ALREADY_SUSPENDED' };
        }

        const previousStatus = number.status;
        number.status = 'suspended';
        number.modified = new Date().toISOString().split('T')[0];

        addAuditEntry(id, 'NUMBER_SUSPENDED', {
            before: { status: previousStatus },
            after: { status: 'suspended' },
            reason: reason
        });

        return { 
            success: true, 
            data: deepClone(number),
            changes: {
                before: { status: previousStatus },
                after: { status: 'suspended' }
            }
        };
    }

    async function reactivateNumber(id, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}/reactivate`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }
        if (number.status !== 'suspended') {
            return { success: false, error: 'Number is not suspended', code: 'NOT_SUSPENDED' };
        }

        const previousStatus = number.status;
        number.status = 'active';
        number.modified = new Date().toISOString().split('T')[0];

        addAuditEntry(id, 'NUMBER_REACTIVATED', {
            before: { status: previousStatus },
            after: { status: 'active' },
            reason: reason
        });

        return { 
            success: true, 
            data: deepClone(number),
            changes: {
                before: { status: previousStatus },
                after: { status: 'active' }
            }
        };
    }

    async function reassignNumber(id, accountId, subAccountId, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}/reassign`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ accountId, subAccountId, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }

        const newAccount = mockDatabase.accounts.find(a => a.id === accountId);
        if (!newAccount) {
            return { success: false, error: 'Target account not found', code: 'ACCOUNT_NOT_FOUND' };
        }

        const newSubAccount = mockDatabase.subAccounts.find(s => s.id === subAccountId && s.accountId === accountId);
        if (!newSubAccount) {
            return { success: false, error: 'Target sub-account not found or does not belong to account', code: 'SUBACCOUNT_NOT_FOUND' };
        }

        const previousAccount = number.account;
        const previousAccountId = number.accountId;
        const previousSubAccount = number.subAccount;
        const previousSubAccountId = number.subAccountId;

        number.account = newAccount.name;
        number.accountId = accountId;
        number.subAccount = newSubAccount.name;
        number.subAccountId = subAccountId;
        number.modified = new Date().toISOString().split('T')[0];

        addAuditEntry(id, 'NUMBER_REASSIGNED', {
            before: { 
                account: previousAccount, 
                accountId: previousAccountId,
                subAccount: previousSubAccount, 
                subAccountId: previousSubAccountId 
            },
            after: { 
                account: newAccount.name, 
                accountId: accountId,
                subAccount: newSubAccount.name, 
                subAccountId: subAccountId 
            },
            reason: reason
        });

        return { 
            success: true, 
            data: deepClone(number),
            changes: {
                before: { account: previousAccount, subAccount: previousSubAccount },
                after: { account: newAccount.name, subAccount: newSubAccount.name }
            }
        };
    }

    async function changeMode(id, newMode, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}/mode`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ mode: newMode, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }

        const validModes = ['portal', 'api'];
        if (!validModes.includes(newMode.toLowerCase())) {
            return { success: false, error: 'Invalid mode. Must be "portal" or "api"', code: 'INVALID_MODE' };
        }

        const previousMode = number.mode;
        number.mode = newMode.toLowerCase();
        number.modified = new Date().toISOString().split('T')[0];

        if (newMode.toLowerCase() === 'api' && !number.apiWebhookUrl) {
            number.capabilities = number.capabilities.filter(c => c !== 'senderid' && c !== 'inbox');
            if (!number.capabilities.includes('api')) {
                number.capabilities.push('api');
            }
        }

        addAuditEntry(id, 'NUMBER_MODE_CHANGED', {
            before: { mode: previousMode },
            after: { mode: newMode.toLowerCase() },
            reason: reason
        });

        return { 
            success: true, 
            data: deepClone(number),
            changes: {
                before: { mode: previousMode },
                after: { mode: newMode.toLowerCase() }
            }
        };
    }

    async function updateCapabilities(id, capabilities, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}/capabilities`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ capabilities, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }

        const validCapabilities = ['senderid', 'inbox', 'optout', 'api'];
        const invalidCaps = capabilities.filter(c => !validCapabilities.includes(c));
        if (invalidCaps.length > 0) {
            return { success: false, error: `Invalid capabilities: ${invalidCaps.join(', ')}`, code: 'INVALID_CAPABILITIES' };
        }

        if (number.type === 'shortcode_keyword') {
            const restrictedCaps = capabilities.filter(c => c === 'senderid' || c === 'inbox');
            if (restrictedCaps.length > 0) {
                return { 
                    success: false, 
                    error: 'Shared Shortcode Keywords cannot have SenderID or Inbox capabilities', 
                    code: 'KEYWORD_CAPABILITY_RESTRICTION' 
                };
            }
        }

        const previousCapabilities = [...number.capabilities];
        number.capabilities = [...capabilities];
        number.modified = new Date().toISOString().split('T')[0];

        addAuditEntry(id, 'NUMBER_CAPABILITY_CHANGED', {
            before: { capabilities: previousCapabilities },
            after: { capabilities: capabilities },
            reason: reason
        });

        return { 
            success: true, 
            data: deepClone(number),
            changes: {
                before: { capabilities: previousCapabilities },
                after: { capabilities: capabilities }
            }
        };
    }

    async function updateApiWebhook(id, webhookUrl, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}/webhook`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ webhookUrl, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }

        if (number.mode !== 'api') {
            return { success: false, error: 'Webhook URL can only be set for numbers in API mode', code: 'MODE_MISMATCH' };
        }

        if (webhookUrl && !webhookUrl.match(/^https?:\/\/.+/)) {
            return { success: false, error: 'Invalid webhook URL format', code: 'INVALID_URL' };
        }

        const previousUrl = number.apiWebhookUrl;
        number.apiWebhookUrl = webhookUrl || null;
        number.modified = new Date().toISOString().split('T')[0];

        addAuditEntry(id, 'NUMBER_WEBHOOK_UPDATED', {
            before: { apiWebhookUrl: previousUrl },
            after: { apiWebhookUrl: webhookUrl },
            reason: reason
        });

        return { 
            success: true, 
            data: deepClone(number),
            changes: {
                before: { apiWebhookUrl: previousUrl },
                after: { apiWebhookUrl: webhookUrl }
            }
        };
    }

    async function disableKeyword(id, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}/disable-keyword`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }

        if (number.type !== 'shortcode_keyword') {
            return { success: false, error: 'This action is only available for shortcode keywords', code: 'NOT_KEYWORD' };
        }

        const previousStatus = number.status;
        number.status = 'suspended';
        number.modified = new Date().toISOString().split('T')[0];

        addAuditEntry(id, 'KEYWORD_DISABLED', {
            before: { status: previousStatus },
            after: { status: 'suspended' },
            reason: reason
        });

        return { 
            success: true, 
            data: deepClone(number),
            changes: {
                before: { status: previousStatus },
                after: { status: 'suspended' }
            }
        };
    }

    async function updateOptoutRouting(id, routingConfig, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/${id}/optout-routing`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ routingConfig, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === id);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }

        if (!number.capabilities.includes('optout')) {
            return { success: false, error: 'Number does not have opt-out capability enabled', code: 'NO_OPTOUT_CAPABILITY' };
        }

        const previousConfig = number.optoutConfig ? deepClone(number.optoutConfig) : null;
        number.optoutConfig = {
            keywords: routingConfig.keywords || 'STOP, UNSUBSCRIBE',
            reply: routingConfig.reply || 'You have been unsubscribed.',
            forward: routingConfig.forward || null
        };
        number.modified = new Date().toISOString().split('T')[0];

        addAuditEntry(id, 'OPTOUT_ROUTING_CHANGED', {
            before: { optoutConfig: previousConfig },
            after: { optoutConfig: number.optoutConfig },
            reason: reason
        });

        return { 
            success: true, 
            data: deepClone(number),
            changes: {
                before: { optoutConfig: previousConfig },
                after: { optoutConfig: number.optoutConfig }
            }
        };
    }

    async function getAuditHistory(entityId, paging = {}) {
        if (!config.useMockData) {
            const params = new URLSearchParams();
            params.append('page', paging.page || 1);
            params.append('pageSize', paging.pageSize || 50);
            const response = await fetch(`${config.apiBaseUrl}/${entityId}/audit?${params.toString()}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const number = mockDatabase.numbers.find(n => n.id === entityId);
        if (!number) {
            return { success: false, error: 'Number not found', code: 'NOT_FOUND' };
        }

        let history = mockDatabase.auditHistory[entityId] || [];
        
        if (history.length === 0) {
            history = [
                {
                    id: generateAuditId(),
                    timestamp: number.created + 'T10:00:00.000Z',
                    eventType: 'NUMBER_ASSIGNED',
                    adminUser: { id: 'ADMIN-001', email: 'admin@quicksms.co.uk', name: 'Admin User', role: 'super_admin' },
                    details: {
                        after: { account: number.account, subAccount: number.subAccount },
                        reason: 'Initial assignment'
                    }
                }
            ];
        }

        const page = paging.page || 1;
        const pageSize = paging.pageSize || 50;
        const startIndex = (page - 1) * pageSize;
        const paginatedHistory = history.slice(startIndex, startIndex + pageSize);

        return {
            success: true,
            data: paginatedHistory,
            pagination: {
                page: page,
                pageSize: pageSize,
                totalCount: history.length,
                totalPages: Math.ceil(history.length / pageSize)
            }
        };
    }

    async function getAccounts() {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/lookup/accounts`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        return {
            success: true,
            data: deepClone(mockDatabase.accounts.filter(a => a.status === 'active'))
        };
    }

    async function getSubAccounts(accountId) {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/lookup/accounts/${accountId}/sub-accounts`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const subAccounts = mockDatabase.subAccounts.filter(
            s => s.accountId === accountId && s.status === 'active'
        );

        return {
            success: true,
            data: deepClone(subAccounts)
        };
    }

    async function bulkSuspend(ids, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/bulk/suspend`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ ids, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const results = [];
        for (const id of ids) {
            const result = await suspendNumber(id, reason);
            results.push({ id, ...result });
        }

        const successCount = results.filter(r => r.success).length;
        const failedIds = results.filter(r => !r.success).map(r => r.id);

        return {
            success: failedIds.length === 0,
            successCount: successCount,
            failedCount: failedIds.length,
            failedIds: failedIds,
            results: results
        };
    }

    async function bulkReactivate(ids, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/bulk/reactivate`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ ids, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const results = [];
        for (const id of ids) {
            const result = await reactivateNumber(id, reason);
            results.push({ id, ...result });
        }

        const successCount = results.filter(r => r.success).length;
        const failedIds = results.filter(r => !r.success).map(r => r.id);

        return {
            success: failedIds.length === 0,
            successCount: successCount,
            failedCount: failedIds.length,
            failedIds: failedIds,
            results: results
        };
    }

    async function bulkReassign(ids, accountId, subAccountId, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/bulk/reassign`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ ids, accountId, subAccountId, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const results = [];
        for (const id of ids) {
            const result = await reassignNumber(id, accountId, subAccountId, reason);
            results.push({ id, ...result });
        }

        const successCount = results.filter(r => r.success).length;
        const failedIds = results.filter(r => !r.success).map(r => r.id);

        return {
            success: failedIds.length === 0,
            successCount: successCount,
            failedCount: failedIds.length,
            failedIds: failedIds,
            results: results
        };
    }

    async function bulkChangeMode(ids, mode, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/bulk/mode`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ ids, mode, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const results = [];
        for (const id of ids) {
            const result = await changeMode(id, mode, reason);
            results.push({ id, ...result });
        }

        const successCount = results.filter(r => r.success).length;
        const failedIds = results.filter(r => !r.success).map(r => r.id);

        return {
            success: failedIds.length === 0,
            successCount: successCount,
            failedCount: failedIds.length,
            failedIds: failedIds,
            results: results
        };
    }

    async function bulkUpdateCapabilities(ids, capabilities, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/bulk/capabilities`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ ids, capabilities, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const results = [];
        for (const id of ids) {
            const result = await updateCapabilities(id, capabilities, reason);
            results.push({ id, ...result });
        }

        const successCount = results.filter(r => r.success).length;
        const failedIds = results.filter(r => !r.success).map(r => r.id);

        return {
            success: failedIds.length === 0,
            successCount: successCount,
            failedCount: failedIds.length,
            failedIds: failedIds,
            results: results
        };
    }

    async function exportNumbers(filters = {}, format = 'csv') {
        if (!config.useMockData) {
            const params = new URLSearchParams();
            Object.entries(filters).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    value.forEach(v => params.append(key, v));
                } else if (value) {
                    params.append(key, value);
                }
            });
            params.append('format', format);
            const response = await fetch(`${config.apiBaseUrl}/export?${params.toString()}`, {
                method: 'GET',
                headers: { 'Accept': format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.blob();
        }

        await simulateDelay();

        const listResult = await listNumbers(filters, { pageSize: 10000 }, {});
        
        return {
            success: true,
            data: listResult.data,
            format: format,
            recordCount: listResult.data.length,
            generatedAt: new Date().toISOString()
        };
    }

    async function bulkReturnToPool(ids, reason = '') {
        if (!config.useMockData) {
            const response = await fetch(`${config.apiBaseUrl}/bulk/return-to-pool`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ ids, reason })
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        }

        await simulateDelay();

        const results = [];
        for (const id of ids) {
            const number = mockDatabase.numbers.find(n => n.id === id);
            if (number) {
                const previousAccount = number.account;
                const previousSubAccount = number.subAccount;
                
                number.account = 'Unassigned Pool';
                number.subAccount = 'Available';
                number.accountId = null;
                number.subAccountId = null;
                
                addAuditEntry(id, 'NUMBER_RETURNED_TO_POOL', {
                    previousAccount: previousAccount,
                    previousSubAccount: previousSubAccount,
                    newAccount: 'Unassigned Pool',
                    reason: reason || 'Bulk return to pool action'
                });
                
                results.push({ id, success: true });
            } else {
                results.push({ id, success: false, error: 'Number not found' });
            }
        }

        const successCount = results.filter(r => r.success).length;
        const failedIds = results.filter(r => !r.success).map(r => r.id);

        return {
            success: failedIds.length === 0,
            successCount: successCount,
            failedCount: failedIds.length,
            failedIds: failedIds,
            results: results
        };
    }

    return {
        config: config,
        _mockDb: mockDatabase,
        listNumbers: listNumbers,
        getNumber: getNumber,
        suspendNumber: suspendNumber,
        reactivateNumber: reactivateNumber,
        reassignNumber: reassignNumber,
        changeMode: changeMode,
        updateCapabilities: updateCapabilities,
        updateApiWebhook: updateApiWebhook,
        disableKeyword: disableKeyword,
        updateOptoutRouting: updateOptoutRouting,
        getAuditHistory: getAuditHistory,
        getAccounts: getAccounts,
        getSubAccounts: getSubAccounts,
        bulkSuspend: bulkSuspend,
        bulkReactivate: bulkReactivate,
        bulkReassign: bulkReassign,
        bulkChangeMode: bulkChangeMode,
        bulkUpdateCapabilities: bulkUpdateCapabilities,
        bulkReturnToPool: bulkReturnToPool,
        exportNumbers: exportNumbers
    };
})();

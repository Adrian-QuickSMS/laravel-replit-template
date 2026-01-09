/**
 * Email-to-SMS Service Layer
 * 
 * This service provides a clean abstraction for Standard Email-to-SMS CRUD operations.
 * Currently uses mock data but is designed to be easily swapped for real API endpoints.
 * 
 * To connect to real backend:
 * 1. Set EmailToSmsService.config.useMockData = false
 * 2. Configure EmailToSmsService.config.baseUrl to your API endpoint
 * 3. Implement authentication headers in _makeRequest()
 */

var EmailToSmsService = (function() {
    'use strict';
    
    // Configuration - easily swappable for real endpoints
    var config = {
        useMockData: true,
        baseUrl: '/api/email-to-sms',
        endpoints: {
            list: '/setups',
            create: '/setups',
            update: '/setups/{id}',
            archive: '/setups/{id}/archive',
            templates: '/templates/senderids'
        }
    };
    
    // Mock data store
    var mockSetups = [
        {
            id: 'std-001',
            name: 'General Notifications',
            description: 'General purpose notification emails converted to SMS',
            subaccountId: 'main',
            subaccountName: 'Main Account',
            allowedEmails: ['admin@company.com', 'system@company.com', 'notifications@company.com'],
            senderIdTemplateId: 'tpl-quicksms-001',
            senderId: 'QuickSMS',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: true,
            deliveryReportsEmail: 'reports@company.com',
            contentFilterRegex: '--\n.*\nSent from my iPhone',
            status: 'active',
            createdAt: '2024-10-20T10:00:00Z',
            updatedAt: '2025-01-09T10:15:00Z'
        },
        {
            id: 'std-002',
            name: 'Urgent Alerts',
            description: 'High priority alerts requiring immediate attention',
            subaccountId: 'marketing',
            subaccountName: 'Marketing Team',
            allowedEmails: ['alerts@marketing.com'],
            senderIdTemplateId: 'tpl-alerts-002',
            senderId: 'ALERTS',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: false,
            deliveryReportsEnabled: false,
            deliveryReportsEmail: '',
            contentFilterRegex: '',
            status: 'active',
            createdAt: '2024-11-05T09:00:00Z',
            updatedAt: '2025-01-08T16:42:00Z'
        },
        {
            id: 'std-003',
            name: 'Patient Communications',
            description: 'NHS Trust patient communication system',
            subaccountId: 'support',
            subaccountName: 'Support Team',
            allowedEmails: ['*@nhstrust.nhs.uk'],
            senderIdTemplateId: 'tpl-nhs-003',
            senderId: 'NHS',
            subjectOverridesSenderId: true,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: true,
            deliveryReportsEmail: 'nhs-reports@support.com',
            contentFilterRegex: 'Kind regards,\n.*',
            status: 'active',
            createdAt: '2024-11-18T14:00:00Z',
            updatedAt: '2025-01-07T11:30:00Z'
        },
        {
            id: 'std-004',
            name: 'Appointment Reminders',
            description: 'Clinic appointment reminder system',
            subaccountId: 'main',
            subaccountName: 'Main Account',
            allowedEmails: ['bookings@clinic.com', 'reception@clinic.com'],
            senderIdTemplateId: 'tpl-pharmacy-004',
            senderId: 'Pharmacy',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: false,
            deliveryReportsEnabled: false,
            deliveryReportsEmail: '',
            contentFilterRegex: '',
            status: 'active',
            createdAt: '2024-12-01T08:00:00Z',
            updatedAt: '2025-01-06T09:00:00Z'
        },
        {
            id: 'std-005',
            name: 'Delivery Updates',
            description: 'Shipping and delivery notification emails',
            subaccountId: 'marketing',
            subaccountName: 'Marketing Team',
            allowedEmails: [],
            senderIdTemplateId: 'tpl-info-005',
            senderId: 'INFO',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: false,
            deliveryReportsEmail: '',
            contentFilterRegex: '',
            status: 'active',
            createdAt: '2024-12-10T12:00:00Z',
            updatedAt: '2025-01-05T15:00:00Z'
        },
        {
            id: 'std-006',
            name: 'Internal Testing',
            description: 'Development and QA testing setup',
            subaccountId: 'support',
            subaccountName: 'Support Team',
            allowedEmails: ['dev@quicksms.io', 'qa@quicksms.io', 'test@quicksms.io', 'staging@quicksms.io'],
            senderIdTemplateId: 'tpl-quicksms-001',
            senderId: 'QuickSMS',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: true,
            deliveryReportsEmail: 'dev-team@quicksms.io',
            contentFilterRegex: '',
            status: 'active',
            createdAt: '2024-12-15T10:00:00Z',
            updatedAt: '2025-01-04T14:00:00Z'
        },
        {
            id: 'std-007',
            name: 'Legacy Alerts',
            description: 'Old alerting system - archived',
            subaccountId: 'main',
            subaccountName: 'Main Account',
            allowedEmails: ['old-system@company.com'],
            senderIdTemplateId: 'tpl-alerts-002',
            senderId: 'ALERTS',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: false,
            deliveryReportsEnabled: false,
            deliveryReportsEmail: '',
            contentFilterRegex: '',
            status: 'archived',
            createdAt: '2024-06-01T10:00:00Z',
            updatedAt: '2024-09-15T12:00:00Z'
        }
    ];
    
    // Mock SenderID templates from SMS Templates (approved/live only)
    var mockSenderIdTemplates = [
        { id: 'tpl-quicksms-001', senderId: 'QuickSMS', status: 'live' },
        { id: 'tpl-alerts-002', senderId: 'ALERTS', status: 'live' },
        { id: 'tpl-nhs-003', senderId: 'NHS', status: 'live' },
        { id: 'tpl-pharmacy-004', senderId: 'Pharmacy', status: 'live' },
        { id: 'tpl-info-005', senderId: 'INFO', status: 'live' }
    ];
    
    // Mock subaccounts
    var mockSubaccounts = [
        { id: 'main', name: 'Main Account' },
        { id: 'marketing', name: 'Marketing Team' },
        { id: 'support', name: 'Support Team' }
    ];
    
    /**
     * Simulate network delay for mock requests
     */
    function simulateDelay(ms) {
        ms = ms || 200;
        return new Promise(function(resolve) {
            setTimeout(resolve, ms);
        });
    }
    
    /**
     * Make HTTP request (for real API integration)
     * @param {string} method - HTTP method
     * @param {string} endpoint - API endpoint
     * @param {object} data - Request body
     * @returns {Promise}
     */
    function _makeRequest(method, endpoint, data) {
        var url = config.baseUrl + endpoint;
        
        return $.ajax({
            url: url,
            method: method,
            contentType: 'application/json',
            data: data ? JSON.stringify(data) : undefined,
            headers: {
                'Accept': 'application/json'
                // TODO: Add authentication headers
                // 'Authorization': 'Bearer ' + getAuthToken()
            }
        });
    }
    
    /**
     * Transform frontend payload to API payload format
     * @param {object} formData - Frontend form data
     * @returns {object} - API-ready payload
     */
    function _transformToApiPayload(formData) {
        return {
            name: formData.name,
            description: formData.description || '',
            subaccountId: formData.subaccountId,
            allowedEmails: formData.allowedEmails || [],
            senderIdTemplateId: formData.senderIdTemplateId,
            subjectOverridesSenderId: formData.subjectOverridesSenderId || false,
            multipleSmsEnabled: formData.multipleSmsEnabled || false,
            deliveryReportsEnabled: formData.deliveryReportsEnabled || false,
            deliveryReportsEmail: formData.deliveryReportsEmail || '',
            contentFilterRegex: formData.contentFilterRegex || ''
        };
    }
    
    /**
     * Transform API response to frontend format
     * @param {object} apiData - API response data
     * @returns {object} - Frontend-ready data
     */
    function _transformFromApiResponse(apiData) {
        return {
            id: apiData.id,
            name: apiData.name,
            description: apiData.description,
            subaccountId: apiData.subaccountId,
            subaccountName: apiData.subaccountName,
            allowedEmails: apiData.allowedEmails,
            senderIdTemplateId: apiData.senderIdTemplateId,
            senderId: apiData.senderId,
            subjectOverridesSenderId: apiData.subjectOverridesSenderId,
            multipleSmsEnabled: apiData.multipleSmsEnabled,
            deliveryReportsEnabled: apiData.deliveryReportsEnabled,
            deliveryReportsEmail: apiData.deliveryReportsEmail,
            contentFilterRegex: apiData.contentFilterRegex,
            status: apiData.status,
            createdAt: apiData.createdAt,
            updatedAt: apiData.updatedAt,
            // Computed fields for display
            created: apiData.createdAt ? apiData.createdAt.split('T')[0] : '',
            lastUpdated: apiData.updatedAt ? apiData.updatedAt.split('T')[0] : '',
            archived: apiData.status === 'archived'
        };
    }
    
    /**
     * List all Email-to-SMS setups
     * @param {object} options - Filter options (includeArchived, search, etc.)
     * @returns {Promise<Array>}
     */
    function listEmailToSmsSetups(options) {
        options = options || {};
        
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var results = mockSetups.map(_transformFromApiResponse);
                
                // Apply filters
                if (!options.includeArchived) {
                    results = results.filter(function(item) {
                        return item.status !== 'archived';
                    });
                }
                
                if (options.search) {
                    var searchTerm = options.search.toLowerCase();
                    results = results.filter(function(item) {
                        return item.name.toLowerCase().indexOf(searchTerm) !== -1 ||
                               item.subaccountName.toLowerCase().indexOf(searchTerm) !== -1 ||
                               item.allowedEmails.some(function(email) {
                                   return email.toLowerCase().indexOf(searchTerm) !== -1;
                               });
                    });
                }
                
                return {
                    success: true,
                    data: results,
                    total: results.length
                };
            });
        }
        
        var queryParams = new URLSearchParams(options).toString();
        var endpoint = config.endpoints.list + (queryParams ? '?' + queryParams : '');
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Create a new Email-to-SMS setup
     * @param {object} payload - Setup data
     * @returns {Promise<object>}
     */
    function createEmailToSmsSetup(payload) {
        var apiPayload = _transformToApiPayload(payload);
        
        if (config.useMockData) {
            return simulateDelay(300).then(function() {
                var now = new Date().toISOString();
                var subaccount = mockSubaccounts.find(function(s) { return s.id === apiPayload.subaccountId; });
                var template = mockSenderIdTemplates.find(function(t) { return t.id === apiPayload.senderIdTemplateId; });
                
                var newSetup = {
                    id: 'std-' + Date.now(),
                    name: apiPayload.name,
                    description: apiPayload.description,
                    subaccountId: apiPayload.subaccountId,
                    subaccountName: subaccount ? subaccount.name : 'Unknown',
                    allowedEmails: apiPayload.allowedEmails,
                    senderIdTemplateId: apiPayload.senderIdTemplateId,
                    senderId: template ? template.senderId : 'Unknown',
                    subjectOverridesSenderId: apiPayload.subjectOverridesSenderId,
                    multipleSmsEnabled: apiPayload.multipleSmsEnabled,
                    deliveryReportsEnabled: apiPayload.deliveryReportsEnabled,
                    deliveryReportsEmail: apiPayload.deliveryReportsEmail,
                    contentFilterRegex: apiPayload.contentFilterRegex,
                    status: 'active',
                    createdAt: now,
                    updatedAt: now
                };
                
                mockSetups.unshift(newSetup);
                
                return {
                    success: true,
                    data: _transformFromApiResponse(newSetup),
                    message: 'Setup created successfully'
                };
            });
        }
        
        return _makeRequest('POST', config.endpoints.create, apiPayload);
    }
    
    /**
     * Update an existing Email-to-SMS setup
     * @param {string} id - Setup ID
     * @param {object} payload - Updated data
     * @returns {Promise<object>}
     */
    function updateEmailToSmsSetup(id, payload) {
        var apiPayload = _transformToApiPayload(payload);
        
        if (config.useMockData) {
            return simulateDelay(300).then(function() {
                var index = mockSetups.findIndex(function(s) { return s.id === id; });
                if (index === -1) {
                    return {
                        success: false,
                        error: 'Setup not found'
                    };
                }
                
                var subaccount = mockSubaccounts.find(function(s) { return s.id === apiPayload.subaccountId; });
                var template = mockSenderIdTemplates.find(function(t) { return t.id === apiPayload.senderIdTemplateId; });
                
                mockSetups[index] = Object.assign({}, mockSetups[index], {
                    name: apiPayload.name,
                    description: apiPayload.description,
                    subaccountId: apiPayload.subaccountId,
                    subaccountName: subaccount ? subaccount.name : mockSetups[index].subaccountName,
                    allowedEmails: apiPayload.allowedEmails,
                    senderIdTemplateId: apiPayload.senderIdTemplateId,
                    senderId: template ? template.senderId : mockSetups[index].senderId,
                    subjectOverridesSenderId: apiPayload.subjectOverridesSenderId,
                    multipleSmsEnabled: apiPayload.multipleSmsEnabled,
                    deliveryReportsEnabled: apiPayload.deliveryReportsEnabled,
                    deliveryReportsEmail: apiPayload.deliveryReportsEmail,
                    contentFilterRegex: apiPayload.contentFilterRegex,
                    updatedAt: new Date().toISOString()
                });
                
                return {
                    success: true,
                    data: _transformFromApiResponse(mockSetups[index]),
                    message: 'Setup updated successfully'
                };
            });
        }
        
        var endpoint = config.endpoints.update.replace('{id}', id);
        return _makeRequest('PUT', endpoint, apiPayload);
    }
    
    /**
     * Archive an Email-to-SMS setup
     * @param {string} id - Setup ID
     * @returns {Promise<object>}
     */
    function archiveEmailToSmsSetup(id) {
        if (config.useMockData) {
            return simulateDelay(200).then(function() {
                var index = mockSetups.findIndex(function(s) { return s.id === id; });
                if (index === -1) {
                    return {
                        success: false,
                        error: 'Setup not found'
                    };
                }
                
                mockSetups[index].status = 'archived';
                mockSetups[index].updatedAt = new Date().toISOString();
                
                return {
                    success: true,
                    data: _transformFromApiResponse(mockSetups[index]),
                    message: 'Setup archived successfully'
                };
            });
        }
        
        var endpoint = config.endpoints.archive.replace('{id}', id);
        return _makeRequest('POST', endpoint);
    }
    
    /**
     * Unarchive an Email-to-SMS setup
     * @param {string} id - Setup ID
     * @returns {Promise<object>}
     */
    function unarchiveEmailToSmsSetup(id) {
        if (config.useMockData) {
            return simulateDelay(200).then(function() {
                var index = mockSetups.findIndex(function(s) { return s.id === id; });
                if (index === -1) {
                    return {
                        success: false,
                        error: 'Setup not found'
                    };
                }
                
                mockSetups[index].status = 'active';
                mockSetups[index].updatedAt = new Date().toISOString();
                
                return {
                    success: true,
                    data: _transformFromApiResponse(mockSetups[index]),
                    message: 'Setup unarchived successfully'
                };
            });
        }
        
        var endpoint = config.endpoints.archive.replace('{id}', id) + '/unarchive';
        return _makeRequest('POST', endpoint);
    }
    
    /**
     * Get a single Email-to-SMS setup by ID
     * @param {string} id - Setup ID
     * @returns {Promise<object>}
     */
    function getEmailToSmsSetup(id) {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var setup = mockSetups.find(function(s) { return s.id === id; });
                if (!setup) {
                    return {
                        success: false,
                        error: 'Setup not found'
                    };
                }
                
                return {
                    success: true,
                    data: _transformFromApiResponse(setup)
                };
            });
        }
        
        var endpoint = config.endpoints.update.replace('{id}', id);
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Get available SenderID templates for dropdown
     * @returns {Promise<Array>}
     */
    function getTemplatesForSenderIdDropdown() {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                return {
                    success: true,
                    data: mockSenderIdTemplates.filter(function(t) {
                        return t.status === 'live';
                    })
                };
            });
        }
        
        return _makeRequest('GET', config.endpoints.templates);
    }
    
    /**
     * Get available subaccounts for dropdown
     * @returns {Promise<Array>}
     */
    function getSubaccounts() {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                return {
                    success: true,
                    data: mockSubaccounts
                };
            });
        }
        
        return _makeRequest('GET', '/subaccounts');
    }
    
    /**
     * Validate content filter regex
     * @param {string} pattern - Regex pattern to validate
     * @returns {object} - { valid: boolean, error?: string }
     */
    function validateContentFilterRegex(pattern) {
        if (!pattern || pattern.trim() === '') {
            return { valid: true };
        }
        
        try {
            // Split by newlines and validate each line as a potential regex
            var lines = pattern.split('\n');
            for (var i = 0; i < lines.length; i++) {
                var line = lines[i].trim();
                if (line) {
                    new RegExp(line);
                }
            }
            return { valid: true };
        } catch (e) {
            return { 
                valid: false, 
                error: 'Invalid regex pattern: ' + e.message 
            };
        }
    }
    
    /**
     * Validate email format (including wildcard domains)
     * @param {string} email - Email to validate
     * @returns {object} - { valid: boolean, isWildcard: boolean }
     */
    function validateEmail(email) {
        if (!email) {
            return { valid: false, isWildcard: false };
        }
        
        // Wildcard domain format: *@domain.tld
        if (/^\*@[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/.test(email)) {
            return { valid: true, isWildcard: true };
        }
        
        // Standard email format
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return { valid: emailRegex.test(email), isWildcard: false };
    }
    
    // Public API
    return {
        config: config,
        listEmailToSmsSetups: listEmailToSmsSetups,
        createEmailToSmsSetup: createEmailToSmsSetup,
        updateEmailToSmsSetup: updateEmailToSmsSetup,
        archiveEmailToSmsSetup: archiveEmailToSmsSetup,
        unarchiveEmailToSmsSetup: unarchiveEmailToSmsSetup,
        getEmailToSmsSetup: getEmailToSmsSetup,
        getTemplatesForSenderIdDropdown: getTemplatesForSenderIdDropdown,
        getSubaccounts: getSubaccounts,
        validateContentFilterRegex: validateContentFilterRegex,
        validateEmail: validateEmail
    };
})();

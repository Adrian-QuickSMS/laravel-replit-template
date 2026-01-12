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
            get: '/setups/{id}',
            archive: '/setups/{id}/archive',
            unarchive: '/setups/{id}/unarchive',
            suspend: '/setups/{id}/suspend',
            reactivate: '/setups/{id}/reactivate',
            templates: '/templates/senderids',
            subaccounts: '/subaccounts',
            accountFlags: '/account/flags'
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
        
        var endpoint = config.endpoints.unarchive.replace('{id}', id);
        return _makeRequest('POST', endpoint);
    }
    
    /**
     * Suspend an Email-to-SMS setup
     * @param {string} id - Setup ID
     * @returns {Promise<object>}
     */
    function suspendEmailToSmsSetup(id) {
        if (config.useMockData) {
            return simulateDelay(200).then(function() {
                var index = mockSetups.findIndex(function(s) { return s.id === id; });
                if (index === -1) {
                    return {
                        success: false,
                        error: 'Setup not found'
                    };
                }
                
                mockSetups[index].status = 'suspended';
                mockSetups[index].updatedAt = new Date().toISOString();
                
                return {
                    success: true,
                    data: _transformFromApiResponse(mockSetups[index]),
                    message: 'Setup suspended successfully'
                };
            });
        }
        
        var endpoint = config.endpoints.suspend.replace('{id}', id);
        return _makeRequest('POST', endpoint);
    }
    
    /**
     * Reactivate a suspended Email-to-SMS setup
     * @param {string} id - Setup ID
     * @returns {Promise<object>}
     */
    function reactivateEmailToSmsSetup(id) {
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
                    message: 'Setup reactivated successfully'
                };
            });
        }
        
        var endpoint = config.endpoints.reactivate.replace('{id}', id);
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
    
    /**
     * Check if a setup name already exists (for uniqueness validation)
     * @param {string} name - The name to check
     * @param {string} [excludeId] - Optional ID to exclude (for editing existing setup)
     * @returns {boolean} - true if name exists, false otherwise
     */
    function checkNameExists(name, excludeId) {
        if (!name) return false;
        
        var normalizedName = name.trim().toLowerCase();
        
        return mockSetups.some(function(setup) {
            if (excludeId && setup.id === excludeId) return false;
            return setup.name.toLowerCase() === normalizedName;
        });
    }
    
    // =========================================================================
    // CONTACT LIST SETUPS - Email-to-SMS Contact List Module
    // =========================================================================
    
    var contactListConfig = {
        endpoints: {
            list: '/contact-list-setups',
            create: '/contact-list-setups',
            update: '/contact-list-setups/{id}',
            archive: '/contact-list-setups/{id}/archive',
            contactBooks: '/contact-books',
            contacts: '/contacts',
            tags: '/tags',
            contactBookData: '/contact-book-data',
            optOutLists: '/opt-out-lists',
            smsTemplates: '/sms-templates/approved',
            accountFlags: '/account/flags'
        }
    };
    
    // Mock Contact List Setups
    var mockContactListSetups = [
        {
            id: 'cls-001',
            name: 'NHS Patient Notifications',
            description: 'Automated notifications to NHS patients',
            subaccountId: 'main',
            subaccountName: 'Main Account',
            allowedSenderEmails: ['admin@nhstrust.nhs.uk', 'appointments@nhstrust.nhs.uk', 'system@nhstrust.nhs.uk'],
            contactBookListIds: ['cb-001', 'cb-003'],
            contactBookListNames: ['NHS Patients', 'Appointment List'],
            optOutMode: 'SELECTED',
            optOutListIds: ['opt-001'],
            optOutListNames: ['Global Opt-Out'],
            senderIdTemplateId: 'tpl-nhs-003',
            senderId: 'NHS',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: true,
            deliveryReportsEmail: 'nhs-reports@nhstrust.nhs.uk',
            contentFilter: '',
            status: 'active',
            createdAt: '2024-10-15T09:00:00Z',
            updatedAt: '2025-01-09T10:30:00Z'
        },
        {
            id: 'cls-002',
            name: 'Pharmacy Reminders',
            description: 'Prescription ready and refill reminders',
            subaccountId: 'support',
            subaccountName: 'Support Team',
            allowedSenderEmails: ['pharmacy@clinic.com'],
            contactBookListIds: ['cb-002'],
            contactBookListNames: ['Pharmacy Patients'],
            optOutMode: 'NONE',
            optOutListIds: [],
            optOutListNames: [],
            senderIdTemplateId: 'tpl-pharmacy-004',
            senderId: 'Pharmacy',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: false,
            deliveryReportsEnabled: false,
            deliveryReportsEmail: '',
            contentFilter: '',
            status: 'active',
            createdAt: '2024-11-01T14:00:00Z',
            updatedAt: '2025-01-08T16:20:00Z'
        },
        {
            id: 'cls-003',
            name: 'Appointment Confirmations',
            description: 'Automated appointment confirmation messages',
            subaccountId: 'main',
            subaccountName: 'Main Account',
            allowedSenderEmails: [],
            contactBookListIds: ['cb-003', 'cb-001', 'cb-002'],
            contactBookListNames: ['Appointment List', 'NHS Patients', 'Pharmacy Patients'],
            optOutMode: 'SELECTED',
            optOutListIds: ['opt-002', 'opt-003'],
            optOutListNames: ['Marketing Opt-Out', 'SMS Opt-Out'],
            senderIdTemplateId: 'tpl-quicksms-001',
            senderId: 'QuickSMS',
            subjectOverridesSenderId: true,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: true,
            deliveryReportsEmail: 'appointments@company.com',
            contentFilter: '--\n.*\nSent from.*',
            status: 'active',
            createdAt: '2024-11-20T10:00:00Z',
            updatedAt: '2025-01-07T11:30:00Z'
        },
        {
            id: 'cls-004',
            name: 'Newsletter Distribution',
            description: 'Weekly newsletter SMS notifications',
            subaccountId: 'marketing',
            subaccountName: 'Marketing Team',
            allowedSenderEmails: ['marketing@company.com', 'newsletter@company.com'],
            contactBookListIds: ['cb-004'],
            contactBookListNames: ['Newsletter Subscribers'],
            optOutMode: 'SELECTED',
            optOutListIds: ['opt-002'],
            optOutListNames: ['Marketing Opt-Out'],
            senderIdTemplateId: 'tpl-info-005',
            senderId: 'INFO',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: false,
            deliveryReportsEmail: '',
            contentFilter: '',
            status: 'archived',
            createdAt: '2024-08-05T08:00:00Z',
            updatedAt: '2024-12-20T10:00:00Z'
        },
        {
            id: 'cls-005',
            name: 'Emergency Alerts',
            description: 'Critical emergency notifications',
            subaccountId: 'main',
            subaccountName: 'Main Account',
            allowedSenderEmails: ['system@quicksms.io', 'alerts@quicksms.io', 'admin@quicksms.io', 'emergency@quicksms.io'],
            contactBookListIds: ['cb-005'],
            contactBookListNames: ['Emergency Contacts'],
            optOutMode: 'NONE',
            optOutListIds: [],
            optOutListNames: [],
            senderIdTemplateId: 'tpl-alerts-002',
            senderId: 'ALERTS',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: false,
            deliveryReportsEnabled: true,
            deliveryReportsEmail: 'alerts@quicksms.io',
            contentFilter: '',
            status: 'active',
            createdAt: '2024-12-01T12:00:00Z',
            updatedAt: '2025-01-09T09:15:00Z'
        },
        {
            id: 'cls-006',
            name: 'Daily Reminders',
            description: 'Daily reminder messages for patients',
            subaccountId: 'support',
            subaccountName: 'Support Team',
            allowedSenderEmails: ['reminders@nhstrust.nhs.uk'],
            contactBookListIds: ['cb-001', 'cb-006'],
            contactBookListNames: ['NHS Patients', 'Active Patients'],
            optOutMode: 'SELECTED',
            optOutListIds: ['opt-001'],
            optOutListNames: ['Global Opt-Out'],
            senderIdTemplateId: 'tpl-nhs-003',
            senderId: 'NHS',
            subjectOverridesSenderId: false,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: false,
            deliveryReportsEmail: '',
            contentFilter: 'Kind regards,\n.*',
            status: 'active',
            createdAt: '2024-12-15T09:00:00Z',
            updatedAt: '2025-01-06T14:00:00Z'
        },
        {
            id: 'cls-007',
            name: 'Billing Notifications',
            description: 'Invoice and payment reminder notifications',
            subaccountId: 'marketing',
            subaccountName: 'Marketing Team',
            allowedSenderEmails: ['billing@company.com', '*@finance.company.com'],
            contactBookListIds: ['cb-004', 'cb-007'],
            contactBookListNames: ['Newsletter Subscribers', 'Recent Orders'],
            optOutMode: 'NONE',
            optOutListIds: [],
            optOutListNames: [],
            senderIdTemplateId: 'tpl-info-005',
            senderId: 'INFO',
            subjectOverridesSenderId: true,
            multipleSmsEnabled: true,
            deliveryReportsEnabled: true,
            deliveryReportsEmail: 'billing-reports@company.com',
            contentFilter: '',
            status: 'active',
            createdAt: '2025-01-02T10:00:00Z',
            updatedAt: '2025-01-05T15:00:00Z'
        }
    ];
    
    // Mock Contact Book Lists (Static + Dynamic)
    var mockContactBookLists = {
        main: [
            { id: 'cb-001', name: 'NHS Patients', type: 'static', recipientCount: 4521, status: 'active' },
            { id: 'cb-003', name: 'Appointment List', type: 'static', recipientCount: 3267, status: 'active' },
            { id: 'cb-005', name: 'Emergency Contacts', type: 'static', recipientCount: 156, status: 'active' },
            { id: 'dyn-001', name: 'Active Subscribers', type: 'dynamic', recipientCount: 2890, status: 'active', criteria: 'last_activity < 30 days' },
            { id: 'dyn-002', name: 'New Patients (30 days)', type: 'dynamic', recipientCount: 342, status: 'active', criteria: 'created_at > 30 days ago' }
        ],
        marketing: [
            { id: 'cb-004', name: 'Newsletter Subscribers', type: 'static', recipientCount: 8934, status: 'active' },
            { id: 'cb-007', name: 'Recent Orders', type: 'static', recipientCount: 1456, status: 'active' },
            { id: 'dyn-003', name: 'High Value Customers', type: 'dynamic', recipientCount: 567, status: 'active', criteria: 'total_spend > 500' },
            { id: 'dyn-004', name: 'Inactive Users', type: 'dynamic', recipientCount: 1234, status: 'active', criteria: 'last_activity > 90 days' }
        ],
        support: [
            { id: 'cb-002', name: 'Pharmacy Patients', type: 'static', recipientCount: 1892, status: 'active' },
            { id: 'cb-006', name: 'Active Patients', type: 'static', recipientCount: 2145, status: 'active' },
            { id: 'dyn-005', name: 'Pending Follow-ups', type: 'dynamic', recipientCount: 89, status: 'active', criteria: 'follow_up_date <= today' }
        ]
    };
    
    // Mock Opt-Out Lists
    var mockOptOutLists = {
        main: [
            { id: 'opt-001', name: 'Global Opt-Out', description: 'Master opt-out list for all communications', recipientCount: 1245 },
            { id: 'opt-002', name: 'Marketing Opt-Out', description: 'Users who opted out of marketing messages', recipientCount: 3456 },
            { id: 'opt-003', name: 'SMS Opt-Out', description: 'Users who prefer no SMS', recipientCount: 892 }
        ],
        marketing: [
            { id: 'opt-002', name: 'Marketing Opt-Out', description: 'Users who opted out of marketing messages', recipientCount: 3456 },
            { id: 'opt-004', name: 'Promotional Opt-Out', description: 'No promotional content', recipientCount: 567 }
        ],
        support: [
            { id: 'opt-001', name: 'Global Opt-Out', description: 'Master opt-out list for all communications', recipientCount: 1245 },
            { id: 'opt-005', name: 'Healthcare Opt-Out', description: 'Healthcare communication opt-outs', recipientCount: 234 }
        ]
    };
    
    // Mock Approved SMS Templates (for SenderID selection)
    var mockApprovedSmsTemplates = {
        main: [
            { id: 'tpl-quicksms-001', senderId: 'QuickSMS', name: 'Default Sender', status: 'live', version: 'v1.2' },
            { id: 'tpl-nhs-003', senderId: 'NHS', name: 'NHS Trust Sender', status: 'live', version: 'v2.0' },
            { id: 'tpl-alerts-002', senderId: 'ALERTS', name: 'Alert Notifications', status: 'live', version: 'v1.0' }
        ],
        marketing: [
            { id: 'tpl-info-005', senderId: 'INFO', name: 'Info Messages', status: 'live', version: 'v1.1' },
            { id: 'tpl-promo-006', senderId: 'PROMO', name: 'Promotional', status: 'live', version: 'v1.0' }
        ],
        support: [
            { id: 'tpl-pharmacy-004', senderId: 'Pharmacy', name: 'Pharmacy Sender', status: 'live', version: 'v1.3' },
            { id: 'tpl-nhs-003', senderId: 'NHS', name: 'NHS Trust Sender', status: 'live', version: 'v2.0' }
        ]
    };
    
    // Mock Account Flags
    var mockAccountFlags = {
        dynamic_senderid_allowed: true,
        wildcard_email_allowed: true,
        max_contact_lists_per_setup: 10,
        max_allowed_sender_emails: 20,
        delivery_reports_enabled: true
    };
    
    // Mock Individual Contacts
    var mockContacts = {
        main: [
            { id: 'con-001', name: 'John Smith', mobile: '+447700900001', email: 'john.smith@example.com', status: 'active' },
            { id: 'con-002', name: 'Sarah Johnson', mobile: '+447700900002', email: 'sarah.j@example.com', status: 'active' },
            { id: 'con-003', name: 'Michael Brown', mobile: '+447700900003', email: 'michael.b@example.com', status: 'active' },
            { id: 'con-004', name: 'Emma Wilson', mobile: '+447700900004', email: 'emma.w@example.com', status: 'active' },
            { id: 'con-005', name: 'James Taylor', mobile: '+447700900005', email: 'james.t@example.com', status: 'active' }
        ],
        marketing: [
            { id: 'con-006', name: 'David Lee', mobile: '+447700900006', email: 'david.lee@example.com', status: 'active' },
            { id: 'con-007', name: 'Lisa Anderson', mobile: '+447700900007', email: 'lisa.a@example.com', status: 'active' },
            { id: 'con-008', name: 'Robert Clark', mobile: '+447700900008', email: 'robert.c@example.com', status: 'active' }
        ],
        support: [
            { id: 'con-009', name: 'Jennifer White', mobile: '+447700900009', email: 'jennifer.w@example.com', status: 'active' },
            { id: 'con-010', name: 'Thomas Martin', mobile: '+447700900010', email: 'thomas.m@example.com', status: 'active' }
        ]
    };
    
    // Mock Tags
    var mockTags = {
        main: [
            { id: 'tag-001', name: 'VIP', recipientCount: 234, color: '#7c3aed' },
            { id: 'tag-002', name: 'Priority', recipientCount: 567, color: '#dc2626' },
            { id: 'tag-003', name: 'New Patient', recipientCount: 189, color: '#16a34a' }
        ],
        marketing: [
            { id: 'tag-004', name: 'Newsletter', recipientCount: 4521, color: '#2563eb' },
            { id: 'tag-005', name: 'Promotional', recipientCount: 2345, color: '#ea580c' },
            { id: 'tag-006', name: 'Loyalty', recipientCount: 890, color: '#7c3aed' }
        ],
        support: [
            { id: 'tag-007', name: 'Follow-up', recipientCount: 123, color: '#0891b2' },
            { id: 'tag-008', name: 'Urgent', recipientCount: 45, color: '#dc2626' }
        ]
    };
    
    /**
     * Transform Contact List setup to frontend format
     */
    function _transformContactListSetup(apiData) {
        return {
            id: apiData.id,
            name: apiData.name,
            description: apiData.description,
            subaccountId: apiData.subaccountId,
            subaccountName: apiData.subaccountName,
            allowedSenderEmails: apiData.allowedSenderEmails || [],
            contactBookListIds: apiData.contactBookListIds || [],
            contactBookListNames: apiData.contactBookListNames || [],
            optOutMode: apiData.optOutMode,
            optOutListIds: apiData.optOutListIds || [],
            optOutListNames: apiData.optOutListNames || [],
            senderIdTemplateId: apiData.senderIdTemplateId,
            senderId: apiData.senderId,
            subjectOverridesSenderId: apiData.subjectOverridesSenderId,
            multipleSmsEnabled: apiData.multipleSmsEnabled,
            deliveryReportsEnabled: apiData.deliveryReportsEnabled,
            deliveryReportsEmail: apiData.deliveryReportsEmail,
            contentFilter: apiData.contentFilter,
            status: apiData.status,
            createdAt: apiData.createdAt,
            updatedAt: apiData.updatedAt,
            // Display-friendly fields
            created: apiData.createdAt ? apiData.createdAt.split('T')[0] : '',
            lastUpdated: apiData.updatedAt ? apiData.updatedAt.split('T')[0] : '',
            targetLists: apiData.contactBookListNames || [],
            optOutLists: apiData.optOutListNames || []
        };
    }
    
    /**
     * List all Email-to-SMS Contact List setups
     * @param {object} options - Filter options (includeArchived, search, subaccountId)
     * @returns {Promise<object>}
     */
    function listEmailToSmsContactListSetups(options) {
        options = options || {};
        
        if (config.useMockData) {
            return simulateDelay(150).then(function() {
                var results = mockContactListSetups.map(_transformContactListSetup);
                
                if (!options.includeArchived) {
                    results = results.filter(function(item) {
                        return item.status !== 'archived';
                    });
                }
                
                if (options.subaccountId) {
                    results = results.filter(function(item) {
                        return item.subaccountId === options.subaccountId;
                    });
                }
                
                if (options.search) {
                    var searchTerm = options.search.toLowerCase();
                    results = results.filter(function(item) {
                        return item.name.toLowerCase().indexOf(searchTerm) !== -1 ||
                               item.subaccountName.toLowerCase().indexOf(searchTerm) !== -1 ||
                               item.targetLists.some(function(list) {
                                   return list.toLowerCase().indexOf(searchTerm) !== -1;
                               }) ||
                               item.allowedSenderEmails.some(function(email) {
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
        var endpoint = contactListConfig.endpoints.list + (queryParams ? '?' + queryParams : '');
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Get a single Contact List setup by ID
     * @param {string} id - Setup ID
     * @returns {Promise<object>}
     */
    function getEmailToSmsContactListSetup(id) {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var setup = mockContactListSetups.find(function(s) { return s.id === id; });
                if (!setup) {
                    return { success: false, error: 'Setup not found' };
                }
                return { success: true, data: _transformContactListSetup(setup) };
            });
        }
        
        var endpoint = contactListConfig.endpoints.update.replace('{id}', id);
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Create a new Contact List setup
     * @param {object} payload - Setup data
     * @returns {Promise<object>}
     */
    function createEmailToSmsContactListSetup(payload) {
        if (config.useMockData) {
            return simulateDelay(300).then(function() {
                var now = new Date().toISOString();
                var subaccount = mockSubaccounts.find(function(s) { return s.id === payload.subaccountId; });
                var template = mockApprovedSmsTemplates[payload.subaccountId] ? 
                    mockApprovedSmsTemplates[payload.subaccountId].find(function(t) { return t.id === payload.senderIdTemplateId; }) : null;
                
                var newSetup = {
                    id: 'cls-' + Date.now(),
                    name: payload.name,
                    description: payload.description || '',
                    subaccountId: payload.subaccountId,
                    subaccountName: subaccount ? subaccount.name : 'Unknown',
                    allowedSenderEmails: payload.allowedSenderEmails || [],
                    contactBookListIds: payload.contactBookListIds || [],
                    contactBookListNames: payload.contactBookListNames || [],
                    optOutMode: payload.optOutMode || 'NONE',
                    optOutListIds: payload.optOutListIds || [],
                    optOutListNames: payload.optOutListNames || [],
                    senderIdTemplateId: payload.senderIdTemplateId,
                    senderId: template ? template.senderId : payload.senderId || 'QuickSMS',
                    subjectOverridesSenderId: payload.subjectOverridesSenderId || false,
                    multipleSmsEnabled: payload.multipleSmsEnabled || false,
                    deliveryReportsEnabled: payload.deliveryReportsEnabled || false,
                    deliveryReportsEmail: payload.deliveryReportsEmail || '',
                    contentFilter: payload.contentFilter || '',
                    status: 'active',
                    createdAt: now,
                    updatedAt: now
                };
                
                mockContactListSetups.unshift(newSetup);
                
                return {
                    success: true,
                    data: _transformContactListSetup(newSetup),
                    message: 'Contact List setup created successfully'
                };
            });
        }
        
        return _makeRequest('POST', contactListConfig.endpoints.create, payload);
    }
    
    /**
     * Update an existing Contact List setup
     * @param {string} id - Setup ID
     * @param {object} payload - Updated data
     * @returns {Promise<object>}
     */
    function updateEmailToSmsContactListSetup(id, payload) {
        if (config.useMockData) {
            return simulateDelay(300).then(function() {
                var index = mockContactListSetups.findIndex(function(s) { return s.id === id; });
                if (index === -1) {
                    return { success: false, error: 'Setup not found' };
                }
                
                var subaccount = mockSubaccounts.find(function(s) { return s.id === payload.subaccountId; });
                var template = mockApprovedSmsTemplates[payload.subaccountId] ? 
                    mockApprovedSmsTemplates[payload.subaccountId].find(function(t) { return t.id === payload.senderIdTemplateId; }) : null;
                
                mockContactListSetups[index] = Object.assign({}, mockContactListSetups[index], {
                    name: payload.name,
                    description: payload.description || '',
                    subaccountId: payload.subaccountId,
                    subaccountName: subaccount ? subaccount.name : mockContactListSetups[index].subaccountName,
                    allowedSenderEmails: payload.allowedSenderEmails || [],
                    contactBookListIds: payload.contactBookListIds || [],
                    contactBookListNames: payload.contactBookListNames || [],
                    optOutMode: payload.optOutMode || 'NONE',
                    optOutListIds: payload.optOutListIds || [],
                    optOutListNames: payload.optOutListNames || [],
                    senderIdTemplateId: payload.senderIdTemplateId,
                    senderId: template ? template.senderId : mockContactListSetups[index].senderId,
                    subjectOverridesSenderId: payload.subjectOverridesSenderId || false,
                    multipleSmsEnabled: payload.multipleSmsEnabled || false,
                    deliveryReportsEnabled: payload.deliveryReportsEnabled || false,
                    deliveryReportsEmail: payload.deliveryReportsEmail || '',
                    contentFilter: payload.contentFilter || '',
                    updatedAt: new Date().toISOString()
                });
                
                return {
                    success: true,
                    data: _transformContactListSetup(mockContactListSetups[index]),
                    message: 'Contact List setup updated successfully'
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.update.replace('{id}', id);
        return _makeRequest('PUT', endpoint, payload);
    }
    
    /**
     * Archive a Contact List setup
     * @param {string} id - Setup ID
     * @returns {Promise<object>}
     */
    function archiveEmailToSmsContactListSetup(id) {
        if (config.useMockData) {
            return simulateDelay(200).then(function() {
                var index = mockContactListSetups.findIndex(function(s) { return s.id === id; });
                if (index === -1) {
                    return { success: false, error: 'Setup not found' };
                }
                
                mockContactListSetups[index].status = 'archived';
                mockContactListSetups[index].updatedAt = new Date().toISOString();
                
                return {
                    success: true,
                    data: _transformContactListSetup(mockContactListSetups[index]),
                    message: 'Contact List setup archived successfully'
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.archive.replace('{id}', id);
        return _makeRequest('POST', endpoint);
    }
    
    /**
     * Unarchive a Contact List setup
     * @param {string} id - Setup ID
     * @returns {Promise<object>}
     */
    function unarchiveEmailToSmsContactListSetup(id) {
        if (config.useMockData) {
            return simulateDelay(200).then(function() {
                var index = mockContactListSetups.findIndex(function(s) { return s.id === id; });
                if (index === -1) {
                    return { success: false, error: 'Setup not found' };
                }
                
                mockContactListSetups[index].status = 'active';
                mockContactListSetups[index].updatedAt = new Date().toISOString();
                
                return {
                    success: true,
                    data: _transformContactListSetup(mockContactListSetups[index]),
                    message: 'Contact List setup unarchived successfully'
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.archive.replace('{id}', id) + '/unarchive';
        return _makeRequest('POST', endpoint);
    }
    
    /**
     * Get Contact Book lists (static + dynamic) for a subaccount
     * @param {string} subaccountId - Subaccount ID (optional, returns all if not provided)
     * @returns {Promise<object>}
     */
    function getContactBookListsAndDynamicLists(subaccountId) {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var lists = [];
                
                if (subaccountId && mockContactBookLists[subaccountId]) {
                    lists = mockContactBookLists[subaccountId];
                } else {
                    // Return all lists across all subaccounts
                    Object.keys(mockContactBookLists).forEach(function(key) {
                        lists = lists.concat(mockContactBookLists[key]);
                    });
                    // Remove duplicates by ID
                    lists = lists.filter(function(item, index, self) {
                        return index === self.findIndex(function(t) { return t.id === item.id; });
                    });
                }
                
                return {
                    success: true,
                    data: {
                        static: lists.filter(function(l) { return l.type === 'static'; }),
                        dynamic: lists.filter(function(l) { return l.type === 'dynamic'; }),
                        all: lists
                    },
                    total: lists.length
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.contactBooks;
        if (subaccountId) {
            endpoint += '?subaccountId=' + encodeURIComponent(subaccountId);
        }
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Get Opt-out lists for a subaccount
     * @param {string} subaccountId - Subaccount ID (optional)
     * @returns {Promise<object>}
     */
    function getOptOutLists(subaccountId) {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var lists = [];
                
                if (subaccountId && mockOptOutLists[subaccountId]) {
                    lists = mockOptOutLists[subaccountId];
                } else {
                    Object.keys(mockOptOutLists).forEach(function(key) {
                        lists = lists.concat(mockOptOutLists[key]);
                    });
                    lists = lists.filter(function(item, index, self) {
                        return index === self.findIndex(function(t) { return t.id === item.id; });
                    });
                }
                
                return {
                    success: true,
                    data: lists,
                    total: lists.length
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.optOutLists;
        if (subaccountId) {
            endpoint += '?subaccountId=' + encodeURIComponent(subaccountId);
        }
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Get approved SMS templates for SenderID selection
     * @param {string} subaccountId - Subaccount ID (optional)
     * @returns {Promise<object>}
     */
    function getApprovedSmsTemplates(subaccountId) {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var templates = [];
                
                if (subaccountId && mockApprovedSmsTemplates[subaccountId]) {
                    templates = mockApprovedSmsTemplates[subaccountId];
                } else {
                    Object.keys(mockApprovedSmsTemplates).forEach(function(key) {
                        templates = templates.concat(mockApprovedSmsTemplates[key]);
                    });
                    templates = templates.filter(function(item, index, self) {
                        return index === self.findIndex(function(t) { return t.id === item.id; });
                    });
                }
                
                return {
                    success: true,
                    data: templates.filter(function(t) { return t.status === 'live'; }),
                    total: templates.length
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.smsTemplates;
        if (subaccountId) {
            endpoint += '?subaccountId=' + encodeURIComponent(subaccountId);
        }
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Get account-level feature flags
     * @returns {Promise<object>}
     */
    function getAccountFlags() {
        if (config.useMockData) {
            return simulateDelay(50).then(function() {
                return {
                    success: true,
                    data: mockAccountFlags
                };
            });
        }
        
        return _makeRequest('GET', contactListConfig.endpoints.accountFlags);
    }
    
    /**
     * Get all Contact Book data for a subaccount (unified method)
     * Returns contacts, lists, dynamic lists, tags, and opt-out lists
     * @param {string} subaccountId - Subaccount ID (optional, returns all if not provided)
     * @returns {Promise<object>}
     */
    function getContactBookData(subaccountId) {
        if (config.useMockData) {
            return simulateDelay(150).then(function() {
                var contacts = [];
                var lists = [];
                var tags = [];
                var optOutLists = [];
                
                function collectData(mockObj, targetArr) {
                    if (subaccountId && mockObj[subaccountId]) {
                        return mockObj[subaccountId].slice();
                    }
                    var all = [];
                    Object.keys(mockObj).forEach(function(key) {
                        all = all.concat(mockObj[key]);
                    });
                    return all.filter(function(item, index, self) {
                        return index === self.findIndex(function(t) { return t.id === item.id; });
                    });
                }
                
                contacts = collectData(mockContacts, contacts);
                lists = collectData(mockContactBookLists, lists);
                tags = collectData(mockTags, tags);
                optOutLists = collectData(mockOptOutLists, optOutLists);
                
                return {
                    success: true,
                    data: {
                        contacts: contacts,
                        lists: lists.filter(function(l) { return l.type === 'static'; }),
                        dynamicLists: lists.filter(function(l) { return l.type === 'dynamic'; }),
                        tags: tags,
                        optOutLists: optOutLists
                    }
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.contactBookData;
        if (subaccountId) {
            endpoint += '?subaccountId=' + encodeURIComponent(subaccountId);
        }
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Get individual contacts for a subaccount
     * @param {string} subaccountId - Subaccount ID (optional)
     * @returns {Promise<object>}
     */
    function getContacts(subaccountId) {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var contacts = [];
                
                if (subaccountId && mockContacts[subaccountId]) {
                    contacts = mockContacts[subaccountId];
                } else {
                    Object.keys(mockContacts).forEach(function(key) {
                        contacts = contacts.concat(mockContacts[key]);
                    });
                    contacts = contacts.filter(function(item, index, self) {
                        return index === self.findIndex(function(t) { return t.id === item.id; });
                    });
                }
                
                return {
                    success: true,
                    data: contacts,
                    total: contacts.length
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.contacts;
        if (subaccountId) {
            endpoint += '?subaccountId=' + encodeURIComponent(subaccountId);
        }
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Get tags for a subaccount
     * @param {string} subaccountId - Subaccount ID (optional)
     * @returns {Promise<object>}
     */
    function getTags(subaccountId) {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var tags = [];
                
                if (subaccountId && mockTags[subaccountId]) {
                    tags = mockTags[subaccountId];
                } else {
                    Object.keys(mockTags).forEach(function(key) {
                        tags = tags.concat(mockTags[key]);
                    });
                    tags = tags.filter(function(item, index, self) {
                        return index === self.findIndex(function(t) { return t.id === item.id; });
                    });
                }
                
                return {
                    success: true,
                    data: tags,
                    total: tags.length
                };
            });
        }
        
        var endpoint = contactListConfig.endpoints.tags;
        if (subaccountId) {
            endpoint += '?subaccountId=' + encodeURIComponent(subaccountId);
        }
        return _makeRequest('GET', endpoint);
    }
    
    // =====================================================================
    // Overview Tab Service Methods (Email-to-SMS Addresses)
    // =====================================================================
    
    var overviewConfig = {
        endpoints: {
            list: '/addresses',
            get: '/addresses/{id}',
            create: '/addresses',
            update: '/addresses/{id}',
            suspend: '/addresses/{id}/suspend',
            reactivate: '/addresses/{id}/reactivate',
            delete: '/addresses/{id}'
        }
    };
    
    var EMAIL_DOMAIN = '@sms.quicksms.io';
    
    var mockOverviewAddresses = [
        {
            id: 'addr-001',
            name: 'Appointment Reminders',
            originatingEmails: ['appointments.12abc' + EMAIL_DOMAIN, 'appts.nhs' + EMAIL_DOMAIN],
            description: 'Automated appointment reminder notifications',
            type: 'Contact List',
            senderId: 'NHS Trust',
            optOut: 'Global Opt-Out',
            subAccount: 'Main Account',
            reportingGroup: 'Appointments',
            allowedSenders: ['admin@nhstrust.nhs.uk', 'system@nhstrust.nhs.uk'],
            dailyLimit: 5000,
            status: 'Active',
            created: '2024-11-15',
            lastUsed: '2025-01-09 08:45',
            messagesSent: 12847
        },
        {
            id: 'addr-002',
            name: 'Prescription Ready',
            originatingEmails: ['prescriptions.45def' + EMAIL_DOMAIN],
            description: 'Notify patients when prescriptions are ready',
            type: 'Standard',
            senderId: 'Pharmacy',
            optOut: 'Marketing Opt-Out',
            subAccount: 'Marketing Team',
            reportingGroup: 'Reminders',
            allowedSenders: [],
            dailyLimit: 2000,
            status: 'Active',
            created: '2024-12-01',
            lastUsed: '2025-01-08 16:20',
            messagesSent: 3421
        },
        {
            id: 'addr-003',
            name: 'Test Notifications',
            originatingEmails: ['test.78ghi' + EMAIL_DOMAIN, 'test.dev' + EMAIL_DOMAIN, 'test.qa' + EMAIL_DOMAIN],
            description: 'Test address for development',
            type: 'Standard',
            senderId: 'QuickSMS',
            optOut: null,
            subAccount: 'Support Team',
            reportingGroup: 'Default',
            allowedSenders: ['developer@company.com'],
            dailyLimit: 100,
            status: 'Suspended',
            created: '2025-01-02',
            lastUsed: '2025-01-05 11:30',
            messagesSent: 156
        }
    ];
    
    var mockReportingGroups = [
        { 
            id: 'rg-001', 
            name: 'Default', 
            description: 'Default reporting group for uncategorized messages', 
            linkedAddresses: ['Test Notifications'],
            messagesSent: 156,
            lastActivity: '2025-01-05 11:30',
            created: '2024-10-01',
            status: 'Active'
        },
        { 
            id: 'rg-002', 
            name: 'Appointments', 
            description: 'All appointment-related SMS communications', 
            linkedAddresses: ['Appointment Reminders'],
            messagesSent: 12847,
            lastActivity: '2025-01-09 08:45',
            created: '2024-11-10',
            status: 'Active'
        },
        { 
            id: 'rg-003', 
            name: 'Reminders', 
            description: 'General reminder and notification messages', 
            linkedAddresses: ['Prescription Ready'],
            messagesSent: 3421,
            lastActivity: '2025-01-08 16:20',
            created: '2024-11-25',
            status: 'Active'
        },
        { 
            id: 'rg-004', 
            name: 'Marketing Campaigns', 
            description: 'Promotional and marketing SMS campaigns', 
            linkedAddresses: [],
            messagesSent: 45892,
            lastActivity: '2024-12-20 14:00',
            created: '2024-08-15',
            status: 'Archived'
        },
        { 
            id: 'rg-005', 
            name: 'Urgent Alerts', 
            description: 'High-priority urgent notifications', 
            linkedAddresses: ['Emergency Alerts', 'System Notifications'],
            messagesSent: 892,
            lastActivity: '2025-01-07 09:15',
            created: '2024-12-01',
            status: 'Active'
        }
    ];
    
    /**
     * List all Overview Email-to-SMS addresses
     * @param {object} options - Filter options
     * @returns {Promise<object>}
     */
    function listOverviewAddresses(options) {
        options = options || {};
        
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var results = mockOverviewAddresses.slice();
                
                if (options.search) {
                    var searchTerm = options.search.toLowerCase();
                    results = results.filter(function(item) {
                        var emailMatch = item.originatingEmails.some(function(email) {
                            return email.toLowerCase().indexOf(searchTerm) !== -1;
                        });
                        return item.name.toLowerCase().indexOf(searchTerm) !== -1 || emailMatch;
                    });
                }
                
                if (options.status && options.status.length > 0) {
                    results = results.filter(function(item) {
                        return options.status.indexOf(item.status) !== -1;
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
        var endpoint = overviewConfig.endpoints.list + (queryParams ? '?' + queryParams : '');
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Get a single Overview Email-to-SMS address
     * @param {string} id - Address ID
     * @returns {Promise<object>}
     */
    function getOverviewAddress(id) {
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var address = mockOverviewAddresses.find(function(a) { return a.id === id; });
                if (!address) {
                    return { success: false, error: 'Address not found' };
                }
                return { success: true, data: address };
            });
        }
        
        var endpoint = overviewConfig.endpoints.get.replace('{id}', id);
        return _makeRequest('GET', endpoint);
    }
    
    /**
     * Suspend an Overview Email-to-SMS address
     * @param {string} id - Address ID
     * @returns {Promise<object>}
     */
    function suspendOverviewAddress(id) {
        if (config.useMockData) {
            return simulateDelay(300).then(function() {
                var index = mockOverviewAddresses.findIndex(function(a) { return a.id === id; });
                if (index === -1) {
                    return { success: false, error: 'Address not found' };
                }
                
                if (mockOverviewAddresses[index].status === 'Suspended') {
                    return { success: false, error: 'Address is already suspended' };
                }
                
                mockOverviewAddresses[index].status = 'Suspended';
                
                return {
                    success: true,
                    data: mockOverviewAddresses[index],
                    message: 'Email-to-SMS address suspended successfully'
                };
            });
        }
        
        var endpoint = overviewConfig.endpoints.suspend.replace('{id}', id);
        return _makeRequest('POST', endpoint);
    }
    
    /**
     * Reactivate an Overview Email-to-SMS address
     * @param {string} id - Address ID
     * @returns {Promise<object>}
     */
    function reactivateOverviewAddress(id) {
        if (config.useMockData) {
            return simulateDelay(300).then(function() {
                var index = mockOverviewAddresses.findIndex(function(a) { return a.id === id; });
                if (index === -1) {
                    return { success: false, error: 'Address not found' };
                }
                
                if (mockOverviewAddresses[index].status === 'Active') {
                    return { success: false, error: 'Address is already active' };
                }
                
                mockOverviewAddresses[index].status = 'Active';
                
                return {
                    success: true,
                    data: mockOverviewAddresses[index],
                    message: 'Email-to-SMS address reactivated successfully'
                };
            });
        }
        
        var endpoint = overviewConfig.endpoints.reactivate.replace('{id}', id);
        return _makeRequest('POST', endpoint);
    }
    
    /**
     * Delete an Overview Email-to-SMS address
     * @param {string} id - Address ID
     * @returns {Promise<object>}
     */
    function deleteOverviewAddress(id) {
        if (config.useMockData) {
            return simulateDelay(300).then(function() {
                var index = mockOverviewAddresses.findIndex(function(a) { return a.id === id; });
                if (index === -1) {
                    return { success: false, error: 'Address not found' };
                }
                
                mockOverviewAddresses.splice(index, 1);
                
                return {
                    success: true,
                    message: 'Email-to-SMS address deleted successfully'
                };
            });
        }
        
        var endpoint = overviewConfig.endpoints.delete.replace('{id}', id);
        return _makeRequest('DELETE', endpoint);
    }
    
    /**
     * List all reporting groups
     * @param {object} options - Filter options
     * @returns {Promise<object>}
     */
    function listReportingGroups(options) {
        options = options || {};
        
        if (config.useMockData) {
            return simulateDelay(100).then(function() {
                var results = mockReportingGroups.slice();
                
                if (!options.includeArchived) {
                    results = results.filter(function(g) { return g.status !== 'Archived'; });
                }
                
                if (options.search) {
                    var searchTerm = options.search.toLowerCase();
                    results = results.filter(function(g) {
                        return g.name.toLowerCase().indexOf(searchTerm) !== -1;
                    });
                }
                
                return {
                    success: true,
                    data: results,
                    total: results.length
                };
            });
        }
        
        return _makeRequest('GET', '/reporting-groups');
    }
    
    /**
     * Archive a reporting group
     * @param {string} id - Group ID
     * @returns {Promise<object>}
     */
    function archiveReportingGroup(id) {
        if (config.useMockData) {
            return simulateDelay(200).then(function() {
                var index = mockReportingGroups.findIndex(function(g) { return g.id === id; });
                if (index === -1) {
                    return { success: false, error: 'Group not found' };
                }
                
                mockReportingGroups[index].status = 'Archived';
                
                return {
                    success: true,
                    data: mockReportingGroups[index],
                    message: 'Reporting group archived successfully'
                };
            });
        }
        
        return _makeRequest('POST', '/reporting-groups/' + id + '/archive');
    }
    
    /**
     * Unarchive a reporting group
     * @param {string} id - Group ID
     * @returns {Promise<object>}
     */
    function unarchiveReportingGroup(id) {
        if (config.useMockData) {
            return simulateDelay(200).then(function() {
                var index = mockReportingGroups.findIndex(function(g) { return g.id === id; });
                if (index === -1) {
                    return { success: false, error: 'Group not found' };
                }
                
                mockReportingGroups[index].status = 'Active';
                
                return {
                    success: true,
                    data: mockReportingGroups[index],
                    message: 'Reporting group unarchived successfully'
                };
            });
        }
        
        return _makeRequest('POST', '/reporting-groups/' + id + '/unarchive');
    }
    
    /**
     * Get mock addresses data (for direct access during transition)
     * @returns {Array}
     */
    function getMockOverviewAddresses() {
        return mockOverviewAddresses;
    }
    
    /**
     * Get mock reporting groups data (for direct access during transition)
     * @returns {Array}
     */
    function getMockReportingGroups() {
        return mockReportingGroups;
    }
    
    // Public API
    return {
        config: config,
        
        // Standard Email-to-SMS (existing)
        listEmailToSmsSetups: listEmailToSmsSetups,
        createEmailToSmsSetup: createEmailToSmsSetup,
        updateEmailToSmsSetup: updateEmailToSmsSetup,
        archiveEmailToSmsSetup: archiveEmailToSmsSetup,
        unarchiveEmailToSmsSetup: unarchiveEmailToSmsSetup,
        suspendEmailToSmsSetup: suspendEmailToSmsSetup,
        reactivateEmailToSmsSetup: reactivateEmailToSmsSetup,
        getEmailToSmsSetup: getEmailToSmsSetup,
        getTemplatesForSenderIdDropdown: getTemplatesForSenderIdDropdown,
        getSubaccounts: getSubaccounts,
        
        // Contact List Email-to-SMS
        listEmailToSmsContactListSetups: listEmailToSmsContactListSetups,
        getEmailToSmsContactListSetup: getEmailToSmsContactListSetup,
        createEmailToSmsContactListSetup: createEmailToSmsContactListSetup,
        updateEmailToSmsContactListSetup: updateEmailToSmsContactListSetup,
        archiveEmailToSmsContactListSetup: archiveEmailToSmsContactListSetup,
        unarchiveEmailToSmsContactListSetup: unarchiveEmailToSmsContactListSetup,
        getContactBookListsAndDynamicLists: getContactBookListsAndDynamicLists,
        getContacts: getContacts,
        getTags: getTags,
        getContactBookData: getContactBookData,
        getOptOutLists: getOptOutLists,
        getApprovedSmsTemplates: getApprovedSmsTemplates,
        getAccountFlags: getAccountFlags,
        
        // Overview Tab (Email-to-SMS Addresses)
        listOverviewAddresses: listOverviewAddresses,
        getOverviewAddress: getOverviewAddress,
        suspendOverviewAddress: suspendOverviewAddress,
        reactivateOverviewAddress: reactivateOverviewAddress,
        deleteOverviewAddress: deleteOverviewAddress,
        getMockOverviewAddresses: getMockOverviewAddresses,
        
        // Reporting Groups
        listReportingGroups: listReportingGroups,
        archiveReportingGroup: archiveReportingGroup,
        unarchiveReportingGroup: unarchiveReportingGroup,
        getMockReportingGroups: getMockReportingGroups,
        
        // Utilities
        validateContentFilterRegex: validateContentFilterRegex,
        validateEmail: validateEmail,
        checkNameExists: checkNameExists
    };
})();

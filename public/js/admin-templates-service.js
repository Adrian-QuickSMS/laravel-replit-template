/**
 * Admin Templates Service
 * Backend-ready abstraction layer for Admin Templates Management
 * 
 * This service provides a clean separation between UI and backend API.
 * When connecting to real backend, swap out the mock implementations
 * with actual API calls. The interface remains the same.
 * 
 * Configuration:
 * - Set AdminTemplatesService.config.useMockData = false to use real API
 * - Configure apiBaseUrl for your backend endpoint
 */

const AdminTemplatesService = (function() {
    'use strict';

    const config = {
        useMockData: true,
        apiBaseUrl: '/api/admin/templates',
        accountsApiUrl: '/api/admin/accounts',
        defaultPageSize: 20,
        maxPageSize: 100
    };

    const mockAccounts = [
        { id: 'ACC-1234', name: 'Acme Corporation', status: 'active' },
        { id: 'ACC-5678', name: 'TechStart Ltd', status: 'active' },
        { id: 'ACC-9012', name: 'EduLearn Academy', status: 'active' },
        { id: 'ACC-3456', name: 'HealthCare Plus', status: 'active' },
        { id: 'ACC-7890', name: 'RetailMax Group', status: 'suspended' },
        { id: 'ACC-2345', name: 'FinServe Solutions', status: 'active' },
        { id: 'ACC-6789', name: 'MediaFlow Digital', status: 'active' },
        { id: 'ACC-0123', name: 'BuildRight Construction', status: 'active' }
    ];

    const mockTemplates = [
        {
            id: 1,
            accountId: 'ACC-1234',
            accountName: 'Acme Corporation',
            templateId: 'TPL-12345678',
            name: 'Welcome Message',
            channel: 'sms',
            trigger: 'portal',
            content: 'Hi {FirstName}, welcome to QuickSMS! Your account is now active. Reply HELP for support or STOP to opt out.',
            contentType: 'text',
            accessScope: 'All Sub-accounts',
            subAccounts: ['all'],
            status: 'live',
            version: 3,
            lastUpdated: '2026-01-05',
            createdBy: 'John Smith',
            createdAt: '2026-01-01'
        },
        {
            id: 2,
            accountId: 'ACC-1234',
            accountName: 'Acme Corporation',
            templateId: 'TPL-23456789',
            name: 'Appointment Reminder',
            channel: 'basic_rcs',
            trigger: 'api',
            content: 'Reminder: Your appointment with {Company} is scheduled for tomorrow at {Time}. Reply YES to confirm.',
            contentType: 'text',
            accessScope: 'Marketing Team, Sales',
            subAccounts: ['marketing', 'sales'],
            status: 'live',
            version: 2,
            lastUpdated: '2026-01-04',
            createdBy: 'Mike Wilson',
            createdAt: '2026-01-02'
        },
        {
            id: 3,
            accountId: 'ACC-5678',
            accountName: 'TechStart Ltd',
            templateId: 'TPL-34567890',
            name: 'Product Launch RCS',
            channel: 'rich_rcs',
            trigger: 'portal',
            content: '',
            contentType: 'rich_card',
            accessScope: 'Sales, Support',
            subAccounts: ['sales', 'support'],
            status: 'draft',
            version: 1,
            lastUpdated: '2026-01-06',
            createdBy: 'Sarah Jones',
            createdAt: '2026-01-06'
        },
        {
            id: 4,
            accountId: 'ACC-5678',
            accountName: 'TechStart Ltd',
            templateId: 'TPL-45678901',
            name: 'Holiday Promotions',
            channel: 'rich_rcs',
            trigger: 'api',
            content: '',
            contentType: 'carousel',
            accessScope: 'Marketing Team',
            subAccounts: ['marketing'],
            status: 'draft',
            version: 4,
            lastUpdated: '2025-12-20',
            createdBy: 'Lisa Chen',
            createdAt: '2025-12-01'
        },
        {
            id: 5,
            accountId: 'ACC-9012',
            accountName: 'EduLearn Academy',
            templateId: 'TPL-56789012',
            name: 'Order Confirmation',
            channel: 'sms',
            trigger: 'email',
            content: 'Order #{OrderID} confirmed! Your items will ship within 2 business days. Track at: {TrackingURL}',
            contentType: 'text',
            accessScope: 'All Sub-accounts',
            subAccounts: ['all'],
            status: 'live',
            version: 1,
            lastUpdated: '2026-01-03',
            createdBy: 'Admin User',
            createdAt: '2026-01-03'
        },
        {
            id: 6,
            accountId: 'ACC-3456',
            accountName: 'HealthCare Plus',
            templateId: 'TPL-67890123',
            name: 'Password Reset',
            channel: 'sms',
            trigger: 'api',
            content: 'Your verification code is {Code}. This code expires in 10 minutes. Do not share this code.',
            contentType: 'text',
            accessScope: 'IT Security',
            subAccounts: ['it'],
            status: 'archived',
            version: 5,
            lastUpdated: '2025-11-15',
            createdBy: 'Emily Brown',
            createdAt: '2025-07-15'
        },
        {
            id: 7,
            accountId: 'ACC-3456',
            accountName: 'HealthCare Plus',
            templateId: 'TPL-78901234',
            name: 'Flash Sale Alert',
            channel: 'basic_rcs',
            trigger: 'portal',
            content: 'Flash Sale! 50% off all items for the next 24 hours. Shop now at {ShopURL}. Limited stock available!',
            contentType: 'text',
            accessScope: 'Marketing Team',
            subAccounts: ['marketing'],
            status: 'draft',
            version: 1,
            lastUpdated: '2026-01-07',
            createdBy: 'David Lee',
            createdAt: '2026-01-07'
        },
        {
            id: 8,
            accountId: 'ACC-7890',
            accountName: 'RetailMax Group',
            templateId: 'TPL-89012345',
            name: 'Customer Feedback',
            channel: 'rich_rcs',
            trigger: 'email',
            content: '',
            contentType: 'rich_card',
            accessScope: 'Support Team',
            subAccounts: ['support'],
            status: 'live',
            version: 2,
            lastUpdated: '2026-01-02',
            createdBy: 'Sarah Jones',
            createdAt: '2025-12-28'
        },
        {
            id: 9,
            accountId: 'ACC-2345',
            accountName: 'FinServe Solutions',
            templateId: 'TPL-90123456',
            name: 'Account Statement',
            channel: 'sms',
            trigger: 'api',
            content: 'Your {Month} statement is ready. Balance: {Balance}. View details at {StatementURL}',
            contentType: 'text',
            accessScope: 'All Sub-accounts',
            subAccounts: ['all'],
            status: 'live',
            version: 1,
            lastUpdated: '2026-01-08',
            createdBy: 'Finance Admin',
            createdAt: '2026-01-08'
        },
        {
            id: 10,
            accountId: 'ACC-6789',
            accountName: 'MediaFlow Digital',
            templateId: 'TPL-01234567',
            name: 'Event Invitation',
            channel: 'rich_rcs',
            trigger: 'portal',
            content: '',
            contentType: 'rich_card',
            accessScope: 'Marketing Team',
            subAccounts: ['marketing'],
            status: 'suspended',
            version: 2,
            lastUpdated: '2026-01-01',
            createdBy: 'Marketing Lead',
            createdAt: '2025-12-15'
        },
        {
            id: 11,
            accountId: 'ACC-1234',
            accountName: 'Acme Corporation',
            templateId: 'TPL-11223344',
            name: 'Delivery Update',
            channel: 'sms',
            trigger: 'api',
            content: 'Your order {OrderID} is out for delivery! Expected arrival: {DeliveryTime}. Track: {TrackingLink}',
            contentType: 'text',
            accessScope: 'Operations',
            subAccounts: ['operations'],
            status: 'live',
            version: 3,
            lastUpdated: '2026-01-09',
            createdBy: 'Ops Manager',
            createdAt: '2025-11-01'
        },
        {
            id: 12,
            accountId: 'ACC-0123',
            accountName: 'BuildRight Construction',
            templateId: 'TPL-22334455',
            name: 'Safety Alert',
            channel: 'sms',
            trigger: 'portal',
            content: 'SAFETY NOTICE: {AlertMessage}. All staff must acknowledge. Reply YES to confirm receipt.',
            contentType: 'text',
            accessScope: 'All Sub-accounts',
            subAccounts: ['all'],
            status: 'live',
            version: 1,
            lastUpdated: '2026-01-10',
            createdBy: 'Safety Officer',
            createdAt: '2026-01-10'
        }
    ];

    async function searchAccounts(searchTerm) {
        if (config.useMockData) {
            await simulateDelay(200);
            const term = (searchTerm || '').toLowerCase();
            const filtered = mockAccounts.filter(acc => 
                acc.name.toLowerCase().includes(term) || 
                acc.id.toLowerCase().includes(term)
            );
            return { success: true, data: filtered };
        }

        try {
            const response = await fetch(`${config.accountsApiUrl}?search=${encodeURIComponent(searchTerm)}`);
            if (!response.ok) throw new Error('Failed to search accounts');
            return await response.json();
        } catch (error) {
            console.error('[AdminTemplatesService] searchAccounts error:', error);
            return { success: false, error: error.message };
        }
    }

    async function listTemplates(params = {}) {
        const {
            accountId = null,
            search = '',
            channels = [],
            triggers = [],
            statuses = [],
            subAccounts = [],
            showArchived = false,
            sortColumn = 'lastUpdated',
            sortDirection = 'desc',
            page = 1,
            pageSize = config.defaultPageSize
        } = params;

        if (config.useMockData) {
            await simulateDelay(300);
            
            let filtered = [...mockTemplates];

            if (accountId) {
                filtered = filtered.filter(t => t.accountId === accountId);
            }

            if (!showArchived) {
                filtered = filtered.filter(t => t.status !== 'archived');
            }

            if (search) {
                const term = search.toLowerCase();
                filtered = filtered.filter(t => 
                    t.name.toLowerCase().includes(term) || 
                    t.templateId.toLowerCase().includes(term) ||
                    t.accountName.toLowerCase().includes(term)
                );
            }

            if (channels.length > 0) {
                filtered = filtered.filter(t => channels.includes(t.channel));
            }

            if (triggers.length > 0) {
                filtered = filtered.filter(t => triggers.includes(t.trigger));
            }

            if (statuses.length > 0) {
                filtered = filtered.filter(t => statuses.includes(t.status));
            }

            if (subAccounts.length > 0) {
                filtered = filtered.filter(t => 
                    subAccounts.some(sa => t.subAccounts.includes(sa) || t.subAccounts.includes('all'))
                );
            }

            filtered.sort((a, b) => {
                let aVal = a[sortColumn] || '';
                let bVal = b[sortColumn] || '';

                if (sortColumn === 'lastUpdated' || sortColumn === 'createdAt') {
                    aVal = new Date(aVal);
                    bVal = new Date(bVal);
                } else if (sortColumn === 'version') {
                    aVal = parseInt(aVal) || 0;
                    bVal = parseInt(bVal) || 0;
                } else if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }

                if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            const totalCount = filtered.length;
            const totalPages = Math.ceil(totalCount / pageSize);
            const startIndex = (page - 1) * pageSize;
            const paginatedData = filtered.slice(startIndex, startIndex + pageSize);

            return {
                success: true,
                data: {
                    templates: paginatedData,
                    pagination: {
                        page,
                        pageSize,
                        totalCount,
                        totalPages,
                        hasNextPage: page < totalPages,
                        hasPrevPage: page > 1
                    }
                }
            };
        }

        try {
            const queryParams = new URLSearchParams({
                page: page.toString(),
                pageSize: pageSize.toString(),
                sortColumn,
                sortDirection,
                showArchived: showArchived.toString()
            });

            if (accountId) queryParams.append('accountId', accountId);
            if (search) queryParams.append('search', search);
            if (channels.length) queryParams.append('channels', channels.join(','));
            if (triggers.length) queryParams.append('triggers', triggers.join(','));
            if (statuses.length) queryParams.append('statuses', statuses.join(','));
            if (subAccounts.length) queryParams.append('subAccounts', subAccounts.join(','));

            const response = await fetch(`${config.apiBaseUrl}?${queryParams}`);
            if (!response.ok) throw new Error('Failed to fetch templates');
            return await response.json();
        } catch (error) {
            console.error('[AdminTemplatesService] listTemplates error:', error);
            return { success: false, error: error.message };
        }
    }

    async function getTemplateDetails(accountId, templateId) {
        if (config.useMockData) {
            await simulateDelay(200);
            const template = mockTemplates.find(t => 
                t.accountId === accountId && (t.templateId === templateId || t.id === parseInt(templateId))
            );
            
            if (!template) {
                return { success: false, error: 'Template not found' };
            }

            return { success: true, data: template };
        }

        try {
            const response = await fetch(`${config.apiBaseUrl}/${accountId}/${templateId}`);
            if (!response.ok) throw new Error('Failed to fetch template details');
            return await response.json();
        } catch (error) {
            console.error('[AdminTemplatesService] getTemplateDetails error:', error);
            return { success: false, error: error.message };
        }
    }

    async function updateTemplate(accountId, templateId, payload) {
        if (config.useMockData) {
            await simulateDelay(400);
            const templateIndex = mockTemplates.findIndex(t => 
                t.accountId === accountId && (t.templateId === templateId || t.id === parseInt(templateId))
            );

            if (templateIndex === -1) {
                return { success: false, error: 'Template not found' };
            }

            mockTemplates[templateIndex] = {
                ...mockTemplates[templateIndex],
                ...payload,
                lastUpdated: new Date().toISOString().split('T')[0]
            };

            logAudit('TEMPLATE_UPDATED', { accountId, templateId, changes: Object.keys(payload) });

            return { success: true, data: mockTemplates[templateIndex] };
        }

        try {
            const response = await fetch(`${config.apiBaseUrl}/${accountId}/${templateId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (!response.ok) throw new Error('Failed to update template');
            return await response.json();
        } catch (error) {
            console.error('[AdminTemplatesService] updateTemplate error:', error);
            return { success: false, error: error.message };
        }
    }

    async function suspendTemplate(accountId, templateId, reason) {
        if (config.useMockData) {
            await simulateDelay(300);
            const template = mockTemplates.find(t => 
                t.accountId === accountId && (t.templateId === templateId || t.id === parseInt(templateId))
            );

            if (!template) {
                return { success: false, error: 'Template not found' };
            }

            template.status = 'suspended';
            template.suspendReason = reason;
            template.suspendedAt = new Date().toISOString();
            template.suspendedBy = 'Admin User';

            logAudit('TEMPLATE_SUSPENDED', { accountId, templateId, reason });

            return { success: true, data: template };
        }

        try {
            const response = await fetch(`${config.apiBaseUrl}/${accountId}/${templateId}/suspend`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason })
            });
            if (!response.ok) throw new Error('Failed to suspend template');
            return await response.json();
        } catch (error) {
            console.error('[AdminTemplatesService] suspendTemplate error:', error);
            return { success: false, error: error.message };
        }
    }

    async function reactivateTemplate(accountId, templateId) {
        if (config.useMockData) {
            await simulateDelay(300);
            const template = mockTemplates.find(t => 
                t.accountId === accountId && (t.templateId === templateId || t.id === parseInt(templateId))
            );

            if (!template) {
                return { success: false, error: 'Template not found' };
            }

            template.status = 'live';
            delete template.suspendReason;
            delete template.suspendedAt;
            delete template.suspendedBy;

            logAudit('TEMPLATE_REACTIVATED', { accountId, templateId });

            return { success: true, data: template };
        }

        try {
            const response = await fetch(`${config.apiBaseUrl}/${accountId}/${templateId}/reactivate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            if (!response.ok) throw new Error('Failed to reactivate template');
            return await response.json();
        } catch (error) {
            console.error('[AdminTemplatesService] reactivateTemplate error:', error);
            return { success: false, error: error.message };
        }
    }

    async function archiveTemplate(accountId, templateId, reason) {
        if (config.useMockData) {
            await simulateDelay(300);
            const template = mockTemplates.find(t => 
                t.accountId === accountId && (t.templateId === templateId || t.id === parseInt(templateId))
            );

            if (!template) {
                return { success: false, error: 'Template not found' };
            }

            template.status = 'archived';
            template.archiveReason = reason;
            template.archivedAt = new Date().toISOString();
            template.archivedBy = 'Admin User';

            logAudit('TEMPLATE_ARCHIVED', { accountId, templateId, reason });

            return { success: true, data: template };
        }

        try {
            const response = await fetch(`${config.apiBaseUrl}/${accountId}/${templateId}/archive`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason })
            });
            if (!response.ok) throw new Error('Failed to archive template');
            return await response.json();
        } catch (error) {
            console.error('[AdminTemplatesService] archiveTemplate error:', error);
            return { success: false, error: error.message };
        }
    }

    async function getVersionHistory(accountId, templateId) {
        if (config.useMockData) {
            await simulateDelay(200);
            
            const template = mockTemplates.find(t => 
                t.accountId === accountId && (t.templateId === templateId || t.id === parseInt(templateId))
            );

            if (!template) {
                return { success: false, error: 'Template not found' };
            }

            const versions = [];
            for (let v = template.version; v >= 1; v--) {
                versions.push({
                    version: v,
                    status: v === template.version ? template.status : 'archived',
                    content: template.content,
                    channel: template.channel,
                    trigger: template.trigger,
                    changeNote: v === 1 ? 'Initial version' : `Updated to version ${v}`,
                    editedBy: template.createdBy,
                    editedAt: template.lastUpdated + ' 12:00:00'
                });
            }

            return { success: true, data: { versions } };
        }

        try {
            const response = await fetch(`${config.apiBaseUrl}/${accountId}/${templateId}/versions`);
            if (!response.ok) throw new Error('Failed to fetch version history');
            return await response.json();
        } catch (error) {
            console.error('[AdminTemplatesService] getVersionHistory error:', error);
            return { success: false, error: error.message };
        }
    }

    function simulateDelay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function logAudit(action, details) {
        const auditEntry = {
            timestamp: new Date().toISOString(),
            action,
            details,
            adminUser: typeof AdminControlPlane !== 'undefined' ? AdminControlPlane.getCurrentUser() : { email: 'admin@quicksms.co.uk' }
        };
        console.log('[AdminTemplatesService][AUDIT]', JSON.stringify(auditEntry));
    }

    return {
        config,
        searchAccounts,
        listTemplates,
        getTemplateDetails,
        updateTemplate,
        suspendTemplate,
        reactivateTemplate,
        archiveTemplate,
        getVersionHistory
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminTemplatesService;
}

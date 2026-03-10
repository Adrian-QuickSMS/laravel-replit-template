/**
 * CampaignsAdminService
 * Backend-ready abstraction layer for Admin Campaign History
 * 
 * Provides clean separation between UI and backend API.
 * All methods return Promises for async operation.
 * Mock data mode for development (configurable via CampaignsAdminService.config.useMockData)
 * Easy swap to real endpoints by changing config and service layer only.
 * 
 * Methods:
 * - listCampaigns(filters): Get paginated campaigns with optional filters
 * - getCampaign(id): Get single campaign details
 * - getAccounts(): Get list of all accounts for filtering
 * - exportCampaigns(filters, format): Export campaigns to CSV/Excel
 * - getCampaignStats(id): Get detailed campaign statistics
 * - getCampaignDeliveryReport(id): Get delivery breakdown
 * - getCampaignMessageLog(id, pagination): Get message-level logs
 * - getCampaignAuditHistory(id): Get audit trail for campaign
 */

(function(window) {
    'use strict';

    var CampaignsAdminService = {
        config: {
            useMockData: true,
            baseUrl: '/api/admin/campaigns',
            mockDelay: { min: 200, max: 600 }
        },

        _mockDelay: function() {
            var delay = Math.random() * (this.config.mockDelay.max - this.config.mockDelay.min) + this.config.mockDelay.min;
            return new Promise(function(resolve) { setTimeout(resolve, delay); });
        },

        _mockAccounts: [
            { id: 'ACC-001', name: 'Acme Corp', status: 'active' },
            { id: 'ACC-002', name: 'RetailMax', status: 'active' },
            { id: 'ACC-003', name: 'ServicePro', status: 'active' },
            { id: 'ACC-004', name: 'HealthFirst', status: 'active' },
            { id: 'ACC-005', name: 'GiftZone', status: 'active' },
            { id: 'ACC-006', name: 'TechStart', status: 'active' },
            { id: 'ACC-007', name: 'FoodDelivery Plus', status: 'active' },
            { id: 'ACC-008', name: 'PropertyHub', status: 'suspended' }
        ],

        _mockCampaigns: [
            {
                id: 'C-2026-001',
                name: 'Spring Promo Campaign',
                accountId: 'ACC-001',
                accountName: 'Acme Corp',
                channel: 'basic_rcs',
                status: 'scheduled',
                senderId: 'ACME',
                rcsAgent: 'Acme Business',
                recipientsTotal: 3500,
                recipientsDelivered: null,
                recipientsFailed: null,
                recipientsPending: 3500,
                sendDate: '2026-01-25T10:00:00Z',
                createdAt: '2026-01-20T14:30:00Z',
                createdBy: 'john.smith@acme.com',
                hasTracking: true,
                hasOptout: true,
                tags: ['promo', 'spring'],
                template: 'Sale Announcement',
                messageContent: 'Spring sale is here! Get 20% off all products this weekend.',
                costPerMessage: 0.035,
                totalCost: 122.50
            },
            {
                id: 'C-2026-002',
                name: 'New Year Flash Sale',
                accountId: 'ACC-002',
                accountName: 'RetailMax',
                channel: 'rich_rcs',
                status: 'sending',
                senderId: 'RETAILMAX',
                rcsAgent: 'RetailMax Official',
                recipientsTotal: 5200,
                recipientsDelivered: 3100,
                recipientsFailed: 45,
                recipientsPending: 2055,
                sendDate: '2026-01-22T00:00:00Z',
                createdAt: '2026-01-18T09:15:00Z',
                createdBy: 'marketing@retailmax.com',
                hasTracking: true,
                hasOptout: true,
                tags: ['flash', 'sale'],
                template: 'Flash Sale Template',
                messageContent: 'Flash Sale! 50% off everything for the next 24 hours only!',
                costPerMessage: 0.045,
                totalCost: 234.00
            },
            {
                id: 'C-2025-003',
                name: 'Holiday Greetings',
                accountId: 'ACC-003',
                accountName: 'ServicePro',
                channel: 'sms_only',
                status: 'complete',
                senderId: 'SVCPRO',
                rcsAgent: null,
                recipientsTotal: 3150,
                recipientsDelivered: 3102,
                recipientsFailed: 48,
                recipientsPending: 0,
                sendDate: '2025-12-24T09:00:00Z',
                createdAt: '2025-12-20T11:00:00Z',
                createdBy: 'admin@servicepro.com',
                hasTracking: true,
                hasOptout: true,
                tags: ['holiday'],
                template: 'Holiday Greeting',
                messageContent: 'Season\'s greetings from ServicePro! Thank you for your business.',
                costPerMessage: 0.025,
                totalCost: 78.75
            },
            {
                id: 'C-2024-004',
                name: 'Boxing Day Deals',
                accountId: 'ACC-001',
                accountName: 'Acme Corp',
                channel: 'basic_rcs',
                status: 'complete',
                senderId: 'ACME',
                rcsAgent: 'Acme Business',
                recipientsTotal: 2800,
                recipientsDelivered: 2756,
                recipientsFailed: 44,
                recipientsPending: 0,
                sendDate: '2024-12-26T08:00:00Z',
                createdAt: '2024-12-23T15:45:00Z',
                createdBy: 'john.smith@acme.com',
                hasTracking: true,
                hasOptout: true,
                tags: ['boxing', 'deals'],
                template: 'Boxing Day Sale',
                messageContent: 'Boxing Day deals are live! Up to 70% off selected items.',
                costPerMessage: 0.035,
                totalCost: 98.00
            },
            {
                id: 'C-2024-005',
                name: 'Christmas Eve Reminder',
                accountId: 'ACC-004',
                accountName: 'HealthFirst',
                channel: 'sms_only',
                status: 'complete',
                senderId: 'HLTHFST',
                rcsAgent: null,
                recipientsTotal: 1500,
                recipientsDelivered: 1487,
                recipientsFailed: 13,
                recipientsPending: 0,
                sendDate: '2024-12-24T07:00:00Z',
                createdAt: '2024-12-22T10:30:00Z',
                createdBy: 'comms@healthfirst.co.uk',
                hasTracking: true,
                hasOptout: true,
                tags: ['appointment'],
                template: 'Appointment Reminder',
                messageContent: 'Reminder: Our clinics will be closed Dec 25-26. Happy holidays!',
                costPerMessage: 0.025,
                totalCost: 37.50
            },
            {
                id: 'C-2024-006',
                name: 'Winter Clearance',
                accountId: 'ACC-002',
                accountName: 'RetailMax',
                channel: 'rich_rcs',
                status: 'complete',
                senderId: 'RETAILMAX',
                rcsAgent: 'RetailMax Official',
                recipientsTotal: 4200,
                recipientsDelivered: 4156,
                recipientsFailed: 44,
                recipientsPending: 0,
                sendDate: '2024-12-23T14:30:00Z',
                createdAt: '2024-12-21T09:00:00Z',
                createdBy: 'marketing@retailmax.com',
                hasTracking: true,
                hasOptout: true,
                tags: ['clearance', 'winter'],
                template: 'Clearance Sale',
                messageContent: 'Winter clearance event! All winter items reduced to clear.',
                costPerMessage: 0.045,
                totalCost: 189.00
            },
            {
                id: 'C-2024-007',
                name: 'Last Minute Gifts',
                accountId: 'ACC-005',
                accountName: 'GiftZone',
                channel: 'sms_only',
                status: 'complete',
                senderId: 'GIFTZONE',
                rcsAgent: null,
                recipientsTotal: 890,
                recipientsDelivered: 885,
                recipientsFailed: 5,
                recipientsPending: 0,
                sendDate: '2024-12-23T10:00:00Z',
                createdAt: '2024-12-22T16:00:00Z',
                createdBy: 'ops@giftzone.com',
                hasTracking: true,
                hasOptout: true,
                tags: ['gifts'],
                template: 'Last Minute Gifts',
                messageContent: 'Still need gifts? Order by 2pm for guaranteed Christmas delivery!',
                costPerMessage: 0.025,
                totalCost: 22.25
            },
            {
                id: 'C-2024-008',
                name: 'Year End Summary',
                accountId: 'ACC-006',
                accountName: 'TechStart',
                channel: 'basic_rcs',
                status: 'complete',
                senderId: 'TECHSTART',
                rcsAgent: 'TechStart App',
                recipientsTotal: 1200,
                recipientsDelivered: 1189,
                recipientsFailed: 11,
                recipientsPending: 0,
                sendDate: '2024-12-20T15:00:00Z',
                createdAt: '2024-12-18T14:00:00Z',
                createdBy: 'comms@techstart.io',
                hasTracking: true,
                hasOptout: true,
                tags: ['summary', 'yearly'],
                template: 'Year End Report',
                messageContent: 'Your 2024 with TechStart: Check out your year in review!',
                costPerMessage: 0.035,
                totalCost: 42.00
            },
            {
                id: 'C-2024-009',
                name: 'Delivery Update',
                accountId: 'ACC-007',
                accountName: 'FoodDelivery Plus',
                channel: 'sms_only',
                status: 'complete',
                senderId: 'FDPLUS',
                rcsAgent: null,
                recipientsTotal: 2500,
                recipientsDelivered: 2478,
                recipientsFailed: 22,
                recipientsPending: 0,
                sendDate: '2024-12-19T18:00:00Z',
                createdAt: '2024-12-19T12:00:00Z',
                createdBy: 'system@fooddeliveryplus.com',
                hasTracking: false,
                hasOptout: true,
                tags: ['transactional'],
                template: 'Delivery Notification',
                messageContent: 'Holiday delivery times may vary. Track your order in our app.',
                costPerMessage: 0.025,
                totalCost: 62.50
            },
            {
                id: 'C-2024-010',
                name: 'Property Alert',
                accountId: 'ACC-008',
                accountName: 'PropertyHub',
                channel: 'basic_rcs',
                status: 'cancelled',
                senderId: 'PROPHUB',
                rcsAgent: 'PropertyHub Alerts',
                recipientsTotal: 800,
                recipientsDelivered: 0,
                recipientsFailed: 0,
                recipientsPending: 0,
                sendDate: '2024-12-18T09:00:00Z',
                createdAt: '2024-12-16T11:30:00Z',
                createdBy: 'alerts@propertyhub.co.uk',
                hasTracking: true,
                hasOptout: true,
                tags: ['alert', 'property'],
                template: 'New Listing Alert',
                messageContent: 'New properties matching your criteria are now available!',
                costPerMessage: 0.035,
                totalCost: 0,
                cancelledAt: '2024-12-17T14:00:00Z',
                cancelledBy: 'admin@propertyhub.co.uk',
                cancellationReason: 'Account suspended'
            }
        ],

        listCampaigns: function(filters) {
            var self = this;
            filters = filters || {};

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    var results = self._mockCampaigns.slice();

                    if (filters.accountIds && filters.accountIds.length > 0) {
                        results = results.filter(function(c) {
                            return filters.accountIds.includes(c.accountId);
                        });
                    }

                    if (filters.statuses && filters.statuses.length > 0) {
                        results = results.filter(function(c) {
                            return filters.statuses.includes(c.status);
                        });
                    }

                    if (filters.channels && filters.channels.length > 0) {
                        results = results.filter(function(c) {
                            return filters.channels.includes(c.channel);
                        });
                    }

                    if (filters.dateFrom) {
                        var fromDate = new Date(filters.dateFrom);
                        results = results.filter(function(c) {
                            return new Date(c.sendDate) >= fromDate;
                        });
                    }

                    if (filters.dateTo) {
                        var toDate = new Date(filters.dateTo);
                        toDate.setHours(23, 59, 59);
                        results = results.filter(function(c) {
                            return new Date(c.sendDate) <= toDate;
                        });
                    }

                    if (filters.search) {
                        var term = filters.search.toLowerCase();
                        results = results.filter(function(c) {
                            return c.name.toLowerCase().includes(term) ||
                                   c.accountName.toLowerCase().includes(term) ||
                                   c.senderId.toLowerCase().includes(term);
                        });
                    }

                    if (filters.sortBy) {
                        results.sort(function(a, b) {
                            var result = 0;
                            switch (filters.sortBy) {
                                case 'name':
                                    result = a.name.localeCompare(b.name);
                                    break;
                                case 'account':
                                    result = a.accountName.localeCompare(b.accountName);
                                    break;
                                case 'date':
                                    result = new Date(a.sendDate) - new Date(b.sendDate);
                                    break;
                                case 'recipients':
                                    result = a.recipientsTotal - b.recipientsTotal;
                                    break;
                            }
                            return filters.sortDirection === 'desc' ? -result : result;
                        });
                    }

                    var page = filters.page || 1;
                    var perPage = filters.perPage || 50;
                    var total = results.length;
                    var start = (page - 1) * perPage;
                    var paginatedResults = results.slice(start, start + perPage);

                    return {
                        success: true,
                        data: paginatedResults,
                        pagination: {
                            page: page,
                            perPage: perPage,
                            total: total,
                            totalPages: Math.ceil(total / perPage)
                        }
                    };
                });
            }

            return fetch(this.config.baseUrl + '?' + new URLSearchParams(filters))
                .then(function(response) { return response.json(); });
        },

        getCampaign: function(id) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    var campaign = self._mockCampaigns.find(function(c) { return c.id === id; });
                    if (!campaign) {
                        return { success: false, error: 'Campaign not found' };
                    }
                    return { success: true, data: campaign };
                });
            }

            return fetch(this.config.baseUrl + '/' + id)
                .then(function(response) { return response.json(); });
        },

        getAccounts: function() {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    return {
                        success: true,
                        data: self._mockAccounts
                    };
                });
            }

            return fetch('/api/admin/accounts')
                .then(function(response) { return response.json(); });
        },

        getCampaignStats: function(id) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    var campaign = self._mockCampaigns.find(function(c) { return c.id === id; });
                    if (!campaign) {
                        return { success: false, error: 'Campaign not found' };
                    }

                    var delivered = campaign.recipientsDelivered || 0;
                    var total = campaign.recipientsTotal || 1;

                    return {
                        success: true,
                        data: {
                            campaignId: id,
                            recipientsTotal: campaign.recipientsTotal,
                            recipientsDelivered: delivered,
                            recipientsFailed: campaign.recipientsFailed || 0,
                            recipientsPending: campaign.recipientsPending || 0,
                            deliveryRate: total > 0 ? ((delivered / total) * 100).toFixed(1) : 0,
                            clickRate: campaign.hasTracking ? (Math.random() * 15 + 5).toFixed(1) : null,
                            optoutRate: (Math.random() * 0.5).toFixed(2),
                            totalCost: campaign.totalCost,
                            costPerDelivered: delivered > 0 ? (campaign.totalCost / delivered).toFixed(4) : 0
                        }
                    };
                });
            }

            return fetch(this.config.baseUrl + '/' + id + '/stats')
                .then(function(response) { return response.json(); });
        },

        getCampaignDeliveryReport: function(id) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    var campaign = self._mockCampaigns.find(function(c) { return c.id === id; });
                    if (!campaign) {
                        return { success: false, error: 'Campaign not found' };
                    }

                    var delivered = campaign.recipientsDelivered || 0;
                    var failed = campaign.recipientsFailed || 0;

                    return {
                        success: true,
                        data: {
                            campaignId: id,
                            outcomes: {
                                delivered: delivered,
                                failed: failed,
                                pending: campaign.recipientsPending || 0,
                                expired: Math.floor(failed * 0.3),
                                rejected: Math.floor(failed * 0.5),
                                undeliverable: Math.floor(failed * 0.2)
                            },
                            carriers: [
                                { name: 'Vodafone', delivered: Math.floor(delivered * 0.35), failed: Math.floor(failed * 0.3) },
                                { name: 'EE', delivered: Math.floor(delivered * 0.28), failed: Math.floor(failed * 0.25) },
                                { name: 'O2', delivered: Math.floor(delivered * 0.22), failed: Math.floor(failed * 0.25) },
                                { name: 'Three', delivered: Math.floor(delivered * 0.15), failed: Math.floor(failed * 0.2) }
                            ]
                        }
                    };
                });
            }

            return fetch(this.config.baseUrl + '/' + id + '/delivery-report')
                .then(function(response) { return response.json(); });
        },

        exportCampaigns: function(filters, format) {
            var self = this;
            format = format || 'csv';

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    console.log('[CampaignsAdminService] Export requested:', { filters: filters, format: format });
                    return {
                        success: true,
                        message: 'Export initiated',
                        downloadUrl: '/downloads/campaigns-export-' + Date.now() + '.' + format
                    };
                });
            }

            return fetch(this.config.baseUrl + '/export', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ filters: filters, format: format })
            }).then(function(response) { return response.json(); });
        },

        getCampaignAuditHistory: function(id) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    var campaign = self._mockCampaigns.find(function(c) { return c.id === id; });
                    if (!campaign) {
                        return { success: false, error: 'Campaign not found' };
                    }

                    var events = [
                        {
                            id: 'AUD-001',
                            timestamp: campaign.createdAt,
                            eventType: 'CAMPAIGN_CREATED',
                            actor: campaign.createdBy,
                            actorType: 'user',
                            details: 'Campaign created'
                        }
                    ];

                    if (campaign.status === 'complete' || campaign.status === 'sending') {
                        events.push({
                            id: 'AUD-002',
                            timestamp: campaign.sendDate,
                            eventType: 'CAMPAIGN_STARTED',
                            actor: 'system',
                            actorType: 'system',
                            details: 'Campaign sending started'
                        });
                    }

                    if (campaign.status === 'complete') {
                        var completedAt = new Date(new Date(campaign.sendDate).getTime() + 3600000);
                        events.push({
                            id: 'AUD-003',
                            timestamp: completedAt.toISOString(),
                            eventType: 'CAMPAIGN_COMPLETED',
                            actor: 'system',
                            actorType: 'system',
                            details: 'Campaign completed'
                        });
                    }

                    if (campaign.status === 'cancelled') {
                        events.push({
                            id: 'AUD-004',
                            timestamp: campaign.cancelledAt,
                            eventType: 'CAMPAIGN_CANCELLED',
                            actor: campaign.cancelledBy,
                            actorType: 'user',
                            details: 'Reason: ' + campaign.cancellationReason
                        });
                    }

                    return {
                        success: true,
                        data: events.sort(function(a, b) {
                            return new Date(b.timestamp) - new Date(a.timestamp);
                        })
                    };
                });
            }

            return fetch(this.config.baseUrl + '/' + id + '/audit')
                .then(function(response) { return response.json(); });
        }
    };

    window.CampaignsAdminService = CampaignsAdminService;

})(window);

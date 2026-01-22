/**
 * ContactTimelineService - Backend-ready service layer for Contact Activity Timeline
 * 
 * Provides a clean abstraction between UI and backend API.
 * Currently uses mock data; swap config.useMockData = false and update endpoints for production.
 */
var ContactTimelineService = (function() {
    'use strict';

    var config = {
        useMockData: true,
        baseUrl: '/api/v1',
        endpoints: {
            timeline: '/contacts/{contactId}/timeline',
            revealMsisdn: '/contacts/{contactId}/reveal-msisdn'
        },
        defaultPageSize: 50,
        maxPageSize: 100
    };

    /**
     * Timeline Event Data Model
     * @typedef {Object} TimelineEvent
     * @property {string} event_id - Unique event identifier (UUID)
     * @property {string} tenant_id - Account/tenant identifier
     * @property {string} contact_id - Contact identifier
     * @property {string} msisdn_hash - SHA-256 hash of MSISDN (no raw PII)
     * @property {string} timestamp - ISO 8601 timestamp
     * @property {string} event_type - Event type code (outbound|inbound|delivery|tags|lists|optout|notes)
     * @property {string} source_module - Source module (campaign|inbox|api|email-to-sms|system)
     * @property {string} actor_type - Actor type (User|System|API)
     * @property {string|null} actor_id - Actor identifier (user ID, API key ID, or null for system)
     * @property {string|null} actor_name - Display name of actor (sanitized, no email addresses)
     * @property {Object} metadata - Event-specific metadata (JSON)
     */

    /**
     * Timeline Filter Parameters
     * @typedef {Object} TimelineFilters
     * @property {string} dateFrom - Start date (ISO 8601)
     * @property {string} dateTo - End date (ISO 8601)
     * @property {string[]} eventTypes - Array of event type codes
     * @property {string[]} channels - Array of channel types (sms|rcs)
     * @property {string[]} sources - Array of source modules
     */

    /**
     * Paginated Timeline Response
     * @typedef {Object} TimelineResponse
     * @property {TimelineEvent[]} events - Array of timeline events
     * @property {number} total - Total count of matching events
     * @property {number} returned - Number of events in current page
     * @property {string|null} cursor - Cursor for next page (null if no more pages)
     * @property {boolean} hasMore - Whether more events are available
     */

    var EVENT_TYPES = {
        MESSAGE_SENT: 'message_sent',
        MESSAGE_DELIVERED: 'message_delivered',
        MESSAGE_FAILED: 'message_failed',
        MESSAGE_SEEN: 'message_seen',
        REPLY_RECEIVED: 'reply_received',
        TAG_ADDED: 'tag_added',
        TAG_REMOVED: 'tag_removed',
        LIST_ADDED: 'list_added',
        LIST_REMOVED: 'list_removed',
        OPTOUT: 'optout',
        OPTIN: 'optin',
        CONTACT_CREATED: 'contact_created',
        CONTACT_UPDATED: 'contact_updated',
        NOTE_ADDED: 'note_added'
    };

    var SOURCE_MODULES = {
        CAMPAIGN: 'campaign',
        INBOX: 'inbox',
        API: 'api',
        EMAIL_TO_SMS: 'email-to-sms',
        SYSTEM: 'system'
    };

    var ACTOR_TYPES = {
        USER: 'User',
        SYSTEM: 'System',
        API: 'API'
    };

    var EVENT_METADATA = {
        message_sent: { icon: 'fa-paper-plane', color: 'success', category: 'outbound', title: 'Message Sent' },
        message_delivered: { icon: 'fa-check-double', color: 'success', category: 'delivery', title: 'Delivered' },
        message_failed: { icon: 'fa-times-circle', color: 'danger', category: 'delivery', title: 'Delivery Failed' },
        message_seen: { icon: 'fa-eye', color: 'info', category: 'delivery', title: 'Message Seen' },
        reply_received: { icon: 'fa-reply', color: 'info', category: 'inbound', title: 'Reply Received' },
        tag_added: { icon: 'fa-tag', color: 'primary', category: 'tags', title: 'Tag Added' },
        tag_removed: { icon: 'fa-tag', color: 'secondary', category: 'tags', title: 'Tag Removed' },
        list_added: { icon: 'fa-list', color: 'primary', category: 'lists', title: 'Added to List' },
        list_removed: { icon: 'fa-list', color: 'secondary', category: 'lists', title: 'Removed from List' },
        optout: { icon: 'fa-ban', color: 'danger', category: 'optout', title: 'Opted Out' },
        optin: { icon: 'fa-check', color: 'success', category: 'optout', title: 'Opted In' },
        contact_created: { icon: 'fa-user-plus', color: 'primary', category: 'notes', title: 'Contact Created' },
        contact_updated: { icon: 'fa-edit', color: 'secondary', category: 'notes', title: 'Contact Updated' },
        note_added: { icon: 'fa-sticky-note', color: 'warning', category: 'notes', title: 'Note Added' }
    };

    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0;
            var v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function hashString(str) {
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            var char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return 'sha256_' + Math.abs(hash).toString(16).padStart(16, '0');
    }

    function generateMockEvent(contactId, tenantId, index, totalDays) {
        var eventTypeKeys = Object.keys(EVENT_TYPES);
        var sourceKeys = Object.keys(SOURCE_MODULES);
        var actorTypeKeys = Object.keys(ACTOR_TYPES);
        
        var eventTypeKey = eventTypeKeys[Math.floor(Math.random() * eventTypeKeys.length)];
        var eventType = EVENT_TYPES[eventTypeKey];
        var eventMeta = EVENT_METADATA[eventType];
        
        var sourceKey = sourceKeys[Math.floor(Math.random() * sourceKeys.length)];
        var sourceModule = SOURCE_MODULES[sourceKey];
        
        if (['tag_added', 'tag_removed', 'list_added', 'list_removed', 'contact_created', 'contact_updated', 'note_added'].includes(eventType)) {
            sourceModule = SOURCE_MODULES.SYSTEM;
        }
        
        var actorType;
        var actorId = null;
        var actorName = null;
        
        if (sourceModule === SOURCE_MODULES.API) {
            actorType = ACTOR_TYPES.API;
            actorId = 'api_key_' + Math.floor(Math.random() * 1000);
            actorName = 'API Integration';
        } else if (sourceModule === SOURCE_MODULES.SYSTEM) {
            actorType = ACTOR_TYPES.SYSTEM;
            actorId = null;
            actorName = 'System';
        } else {
            actorType = ACTOR_TYPES.USER;
            actorId = 'user_' + Math.floor(Math.random() * 100);
            var userNames = ['Admin User', 'Marketing Team', 'Support Agent', 'Campaign Manager'];
            actorName = userNames[Math.floor(Math.random() * userNames.length)];
        }
        
        var now = new Date();
        var daysAgo = Math.floor(Math.random() * totalDays);
        var hoursAgo = Math.floor(Math.random() * 24);
        var minutesAgo = Math.floor(Math.random() * 60);
        var timestamp = new Date(now.getTime() - (daysAgo * 24 * 60 * 60 * 1000) - (hoursAgo * 60 * 60 * 1000) - (minutesAgo * 60 * 1000));
        
        var metadata = generateEventMetadata(eventType, sourceModule);
        
        return {
            event_id: generateUUID(),
            tenant_id: tenantId,
            contact_id: contactId,
            msisdn_hash: hashString('+447700' + Math.floor(Math.random() * 900000 + 100000)),
            timestamp: timestamp.toISOString(),
            event_type: eventType,
            source_module: sourceModule,
            actor_type: actorType,
            actor_id: actorId,
            actor_name: actorName,
            metadata: metadata,
            _ui: {
                icon: eventMeta.icon,
                color: eventMeta.color,
                category: eventMeta.category,
                title: eventMeta.title,
                formattedDate: formatEventDate(timestamp),
                summary: metadata.summary || '',
                details: metadata.details || ''
            }
        };
    }

    function generateEventMetadata(eventType, sourceModule) {
        var campaigns = ['Winter Sale 2026', 'New Year Promo', 'Holiday Greetings', 'Boxing Day Deals', 'January Clearance'];
        var tags = ['VIP Customer', 'Newsletter', 'Promo Subscriber', 'High Value', 'New Lead'];
        var lists = ['Marketing Contacts', 'Newsletter Subscribers', 'Active Customers', 'Leads', 'Premium Members'];
        var channels = ['sms', 'rcs'];
        var channel = channels[Math.floor(Math.random() * channels.length)];
        
        switch(eventType) {
            case EVENT_TYPES.MESSAGE_SENT:
                var campaign = campaigns[Math.floor(Math.random() * campaigns.length)];
                return {
                    channel: channel,
                    campaign_id: 'camp_' + Math.floor(Math.random() * 10000),
                    campaign_name: campaign,
                    sender_id: 'QuickSMS',
                    message_id: 'msg_' + generateUUID().substring(0, 8),
                    summary: 'Sent via ' + sourceModule.replace('-', ' ').toUpperCase(),
                    details: '<strong>Campaign:</strong> ' + campaign + '<br><strong>Channel:</strong> ' + channel.toUpperCase() + '<br><strong>Sender ID:</strong> QuickSMS'
                };
            
            case EVENT_TYPES.MESSAGE_DELIVERED:
                return {
                    channel: channel,
                    message_id: 'msg_' + generateUUID().substring(0, 8),
                    network: ['Vodafone UK', 'EE', 'O2', 'Three'][Math.floor(Math.random() * 4)],
                    summary: 'Delivery confirmed',
                    details: '<strong>Status:</strong> Delivered<br><strong>Network:</strong> ' + ['Vodafone UK', 'EE', 'O2', 'Three'][Math.floor(Math.random() * 4)]
                };
            
            case EVENT_TYPES.MESSAGE_FAILED:
                var reasons = ['Number not in service', 'Carrier rejected', 'Invalid number format', 'Temporary network failure'];
                var reason = reasons[Math.floor(Math.random() * reasons.length)];
                return {
                    channel: channel,
                    message_id: 'msg_' + generateUUID().substring(0, 8),
                    error_code: 'ERR_' + Math.floor(Math.random() * 100),
                    error_message: reason,
                    summary: reason,
                    details: '<strong>Status:</strong> Failed<br><strong>Reason:</strong> ' + reason
                };
            
            case EVENT_TYPES.MESSAGE_SEEN:
                return {
                    channel: 'rcs',
                    message_id: 'msg_' + generateUUID().substring(0, 8),
                    summary: 'Read receipt received',
                    details: '<strong>Channel:</strong> RCS<br><strong>Status:</strong> Seen'
                };
            
            case EVENT_TYPES.REPLY_RECEIVED:
                var replies = ['Thanks!', 'Yes please', 'Not interested', 'More info?', 'Great offer'];
                return {
                    channel: channel,
                    message_id: 'msg_' + generateUUID().substring(0, 8),
                    reply_preview: replies[Math.floor(Math.random() * replies.length)],
                    summary: '"' + replies[Math.floor(Math.random() * replies.length)] + '"',
                    details: '<strong>Reply:</strong> "' + replies[Math.floor(Math.random() * replies.length)] + '"<br><strong>Channel:</strong> ' + channel.toUpperCase()
                };
            
            case EVENT_TYPES.TAG_ADDED:
                var tag = tags[Math.floor(Math.random() * tags.length)];
                return {
                    tag_id: 'tag_' + Math.floor(Math.random() * 100),
                    tag_name: tag,
                    summary: 'Added: ' + tag,
                    details: '<strong>Tag:</strong> ' + tag + '<br><strong>Action:</strong> Added'
                };
            
            case EVENT_TYPES.TAG_REMOVED:
                var tagRemoved = tags[Math.floor(Math.random() * tags.length)];
                return {
                    tag_id: 'tag_' + Math.floor(Math.random() * 100),
                    tag_name: tagRemoved,
                    summary: 'Removed: ' + tagRemoved,
                    details: '<strong>Tag:</strong> ' + tagRemoved + '<br><strong>Action:</strong> Removed'
                };
            
            case EVENT_TYPES.LIST_ADDED:
                var list = lists[Math.floor(Math.random() * lists.length)];
                return {
                    list_id: 'list_' + Math.floor(Math.random() * 100),
                    list_name: list,
                    summary: 'Added to: ' + list,
                    details: '<strong>List:</strong> ' + list + '<br><strong>Action:</strong> Added'
                };
            
            case EVENT_TYPES.LIST_REMOVED:
                var listRemoved = lists[Math.floor(Math.random() * lists.length)];
                return {
                    list_id: 'list_' + Math.floor(Math.random() * 100),
                    list_name: listRemoved,
                    summary: 'Removed from: ' + listRemoved,
                    details: '<strong>List:</strong> ' + listRemoved + '<br><strong>Action:</strong> Removed'
                };
            
            case EVENT_TYPES.OPTOUT:
                return {
                    keyword: 'STOP',
                    scope: 'All Lists',
                    summary: 'STOP received',
                    details: '<strong>Keyword:</strong> STOP<br><strong>Scope:</strong> All Lists<br><strong>Processed:</strong> Automatic'
                };
            
            case EVENT_TYPES.OPTIN:
                return {
                    method: 'Portal Re-subscription',
                    summary: 'Resubscribed',
                    details: '<strong>Method:</strong> Portal Re-subscription<br><strong>Confirmed:</strong> Yes'
                };
            
            case EVENT_TYPES.CONTACT_CREATED:
                var methods = ['CSV Import', 'API', 'Manual Entry', 'Form Submission'];
                var method = methods[Math.floor(Math.random() * methods.length)];
                return {
                    creation_method: method,
                    summary: 'Created via ' + method,
                    details: '<strong>Method:</strong> ' + method
                };
            
            case EVENT_TYPES.CONTACT_UPDATED:
                var fields = ['First Name', 'Last Name', 'Email', 'Custom Field'];
                var field = fields[Math.floor(Math.random() * fields.length)];
                return {
                    fields_changed: [field],
                    summary: field + ' updated',
                    details: '<strong>Changed:</strong> ' + field
                };
            
            case EVENT_TYPES.NOTE_ADDED:
                var notes = ['Customer requested callback', 'Interested in premium plan', 'Follow up next week', 'Confirmed order details'];
                var note = notes[Math.floor(Math.random() * notes.length)];
                return {
                    note_preview: note,
                    summary: 'New note added',
                    details: '<strong>Note:</strong> "' + note + '"'
                };
            
            default:
                return {
                    summary: 'Activity recorded',
                    details: 'Event details recorded.'
                };
        }
    }

    function formatEventDate(date) {
        var now = new Date();
        var diff = now - date;
        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) {
            return 'Today ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        } else if (days === 1) {
            return 'Yesterday ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        } else if (days < 7) {
            return days + ' days ago';
        } else {
            return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        }
    }

    function filterEvents(events, filters) {
        return events.filter(function(event) {
            if (filters.dateFrom) {
                var fromDate = new Date(filters.dateFrom);
                fromDate.setHours(0, 0, 0, 0);
                if (new Date(event.timestamp) < fromDate) return false;
            }
            
            if (filters.dateTo) {
                var toDate = new Date(filters.dateTo);
                toDate.setHours(23, 59, 59, 999);
                if (new Date(event.timestamp) > toDate) return false;
            }
            
            if (filters.eventTypes && filters.eventTypes.length > 0) {
                if (!filters.eventTypes.includes(event._ui.category)) return false;
            }
            
            if (filters.channels && filters.channels.length > 0) {
                if (event.metadata.channel && !filters.channels.includes(event.metadata.channel)) return false;
            }
            
            if (filters.sources && filters.sources.length > 0) {
                if (!filters.sources.includes(event.source_module)) return false;
            }
            
            return true;
        });
    }

    var mockEventCache = {};

    function getMockEvents(contactId, tenantId) {
        var cacheKey = tenantId + '_' + contactId;
        if (!mockEventCache[cacheKey]) {
            var events = [];
            var eventCount = 100 + Math.floor(Math.random() * 50);
            for (var i = 0; i < eventCount; i++) {
                events.push(generateMockEvent(contactId, tenantId, i, 90));
            }
            events.sort(function(a, b) {
                return new Date(b.timestamp) - new Date(a.timestamp);
            });
            mockEventCache[cacheKey] = events;
        }
        return mockEventCache[cacheKey];
    }

    function simulateDelay(min, max) {
        var delay = min + Math.floor(Math.random() * (max - min));
        return new Promise(function(resolve) {
            setTimeout(resolve, delay);
        });
    }

    /**
     * Get timeline events for a contact
     * @param {string} contactId - Contact identifier
     * @param {TimelineFilters} filters - Filter parameters
     * @param {Object} pagination - Pagination options
     * @param {string|null} pagination.cursor - Cursor for pagination (event_id of last event)
     * @param {number} pagination.limit - Number of events to return (default: 50, max: 100)
     * @returns {Promise<TimelineResponse>}
     */
    function getContactTimeline(contactId, filters, pagination) {
        filters = filters || {};
        pagination = pagination || {};
        
        var limit = Math.min(pagination.limit || config.defaultPageSize, config.maxPageSize);
        var cursor = pagination.cursor || null;
        
        if (config.useMockData) {
            return simulateDelay(200, 500).then(function() {
                var tenantId = 'tenant_' + (window.currentTenantId || 'default');
                var allEvents = getMockEvents(contactId, tenantId);
                var filteredEvents = filterEvents(allEvents, filters);
                
                var startIndex = 0;
                if (cursor) {
                    for (var i = 0; i < filteredEvents.length; i++) {
                        if (filteredEvents[i].event_id === cursor) {
                            startIndex = i + 1;
                            break;
                        }
                    }
                }
                
                var pageEvents = filteredEvents.slice(startIndex, startIndex + limit);
                var hasMore = (startIndex + limit) < filteredEvents.length;
                var nextCursor = hasMore ? pageEvents[pageEvents.length - 1].event_id : null;
                
                return {
                    events: pageEvents,
                    total: filteredEvents.length,
                    returned: pageEvents.length,
                    cursor: nextCursor,
                    hasMore: hasMore
                };
            });
        }
        
        var url = config.baseUrl + config.endpoints.timeline.replace('{contactId}', contactId);
        var params = new URLSearchParams();
        
        if (filters.dateFrom) params.append('date_from', filters.dateFrom);
        if (filters.dateTo) params.append('date_to', filters.dateTo);
        if (filters.eventTypes) params.append('event_types', filters.eventTypes.join(','));
        if (filters.channels) params.append('channels', filters.channels.join(','));
        if (filters.sources) params.append('sources', filters.sources.join(','));
        if (cursor) params.append('cursor', cursor);
        params.append('limit', limit.toString());
        
        return fetch(url + '?' + params.toString(), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }).then(function(response) {
            if (!response.ok) {
                throw new Error('Failed to fetch timeline: ' + response.status);
            }
            return response.json();
        });
    }

    /**
     * Request MSISDN reveal for a contact (audit logged)
     * @param {string} contactId - Contact identifier
     * @param {string} reason - Business reason for reveal
     * @returns {Promise<{msisdn: string, revealed_at: string}>}
     */
    function revealMsisdn(contactId, reason) {
        if (config.useMockData) {
            return simulateDelay(100, 300).then(function() {
                console.log('[Audit] MSISDN revealed for contact ' + contactId + ', reason: ' + reason);
                return {
                    msisdn: '+447700' + Math.floor(Math.random() * 900000 + 100000),
                    revealed_at: new Date().toISOString()
                };
            });
        }
        
        var url = config.baseUrl + config.endpoints.revealMsisdn.replace('{contactId}', contactId);
        
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ reason: reason })
        }).then(function(response) {
            if (!response.ok) {
                throw new Error('Failed to reveal MSISDN: ' + response.status);
            }
            return response.json();
        });
    }

    /**
     * Clear the mock event cache (for testing)
     */
    function clearCache() {
        mockEventCache = {};
    }

    return {
        config: config,
        EVENT_TYPES: EVENT_TYPES,
        SOURCE_MODULES: SOURCE_MODULES,
        ACTOR_TYPES: ACTOR_TYPES,
        EVENT_METADATA: EVENT_METADATA,
        getContactTimeline: getContactTimeline,
        revealMsisdn: revealMsisdn,
        clearCache: clearCache,
        formatEventDate: formatEventDate
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ContactTimelineService;
}

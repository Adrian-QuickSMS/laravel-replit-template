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
        INBOUND_SMS: 'inbound_sms',
        INBOUND_RCS: 'inbound_rcs',
        RCS_BUTTON_CLICK: 'rcs_button_click',
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
        inbound_sms: { icon: 'fa-inbox', color: 'info', category: 'inbound', title: 'Inbound SMS received' },
        inbound_rcs: { icon: 'fa-inbox', color: 'info', category: 'inbound', title: 'Inbound RCS received' },
        rcs_button_click: { icon: 'fa-hand-pointer', color: 'primary', category: 'inbound', title: 'RCS button clicked' },
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

    var DELIVERY_STATUSES = {
        PENDING: { status: 'Pending', color: 'warning', icon: 'fa-clock' },
        DELIVERED: { status: 'Delivered', color: 'success', icon: 'fa-check-double' },
        UNDELIVERABLE: { status: 'Undeliverable', color: 'danger', icon: 'fa-times-circle' },
        REJECTED: { status: 'Rejected', color: 'danger', icon: 'fa-ban' },
        SEEN: { status: 'Seen', color: 'info', icon: 'fa-eye' }
    };

    var DELIVERY_ERROR_CODES = {
        'DLR_001': 'Number not in service',
        'DLR_002': 'Carrier rejected message',
        'DLR_003': 'Invalid number format',
        'DLR_004': 'Temporary network failure',
        'DLR_005': 'Message expired before delivery',
        'DLR_006': 'Handset not reachable',
        'DLR_007': 'Subscriber barred',
        'DLR_008': 'Content blocked by carrier',
        'DLR_009': 'Insufficient credit',
        'DLR_010': 'Unknown error'
    };

    var LIST_CHANGE_METHODS = {
        MANUAL: { method: 'manual', label: 'Manual', icon: 'fa-hand-pointer', description: 'Manually added by user' },
        IMPORT: { method: 'import', label: 'Import', icon: 'fa-file-import', description: 'Added via contact import' },
        API: { method: 'api', label: 'API', icon: 'fa-code', description: 'Added via API integration' },
        RULE: { method: 'rule', label: 'Automation Rule', icon: 'fa-robot', description: 'Added by automation rule' },
        CAMPAIGN: { method: 'campaign', label: 'Campaign', icon: 'fa-bullhorn', description: 'Added from campaign targeting' }
    };

    var CONTACT_BOOK_LISTS = [
        { id: 'list_001', name: 'Marketing Contacts', description: 'General marketing list', contact_count: 2450 },
        { id: 'list_002', name: 'Newsletter Subscribers', description: 'Weekly newsletter recipients', contact_count: 8920 },
        { id: 'list_003', name: 'Active Customers', description: 'Customers with recent orders', contact_count: 1580 },
        { id: 'list_004', name: 'Leads', description: 'Prospective customers', contact_count: 3200 },
        { id: 'list_005', name: 'Premium Members', description: 'VIP subscription holders', contact_count: 420 },
        { id: 'list_006', name: 'Event Attendees', description: 'Registered for upcoming events', contact_count: 890 },
        { id: 'list_007', name: 'Product Updates', description: 'Opted-in for product news', contact_count: 5670 },
        { id: 'list_008', name: 'Seasonal Promos', description: 'Holiday and seasonal offers', contact_count: 4100 }
    ];

    var TAG_CHANGE_METHODS = {
        MANUAL: { method: 'manual', label: 'Manual', icon: 'fa-hand-pointer', description: 'Manually applied by user' },
        IMPORT: { method: 'import', label: 'Import', icon: 'fa-file-import', description: 'Applied via contact import' },
        API: { method: 'api', label: 'API', icon: 'fa-code', description: 'Applied via API integration' },
        AUTOMATION: { method: 'automation', label: 'Automation', icon: 'fa-robot', description: 'Applied by automation rule' }
    };

    var CONTACT_BOOK_TAGS = [
        { id: 'tag_001', name: 'VIP', color: 'warning' },
        { id: 'tag_002', name: 'Newsletter', color: 'info' },
        { id: 'tag_003', name: 'Customer', color: 'success' },
        { id: 'tag_004', name: 'Promotions', color: 'primary' },
        { id: 'tag_005', name: 'High Value', color: 'danger' },
        { id: 'tag_006', name: 'New Lead', color: 'secondary' },
        { id: 'tag_007', name: 'Partner', color: 'info' },
        { id: 'tag_008', name: 'Inactive', color: 'secondary' },
        { id: 'tag_009', name: 'Priority', color: 'warning' },
        { id: 'tag_010', name: 'Verified', color: 'success' }
    ];

    var OPTOUT_SCOPES = {
        MASTER: { scope: 'master', label: 'Master Opt-Out', description: 'Blocks all messaging across entire account', blocks_all: true },
        LIST: { scope: 'list', label: 'List-Specific', description: 'Blocks messaging for specific list only', blocks_all: false }
    };

    var OPTOUT_SOURCES = {
        INBOUND_KEYWORD: { source: 'inbound_keyword', label: 'Inbound Keyword', icon: 'fa-sms', description: 'Reply-based opt-out' },
        URL: { source: 'url', label: 'Opt-Out URL', icon: 'fa-link', description: 'Clicked opt-out link' },
        MANUAL: { source: 'manual', label: 'Manual', icon: 'fa-hand-pointer', description: 'Manually added by user' },
        API: { source: 'api', label: 'API', icon: 'fa-code', description: 'Added via API' },
        IMPORT: { source: 'import', label: 'Import', icon: 'fa-file-import', description: 'Imported from file' }
    };

    var OPTOUT_KEYWORDS = ['STOP', 'UNSUBSCRIBE', 'CANCEL', 'END', 'QUIT', 'OPTOUT'];

    var OPTIN_SOURCES = {
        ADMIN: { source: 'admin', label: 'Admin Removal', icon: 'fa-user-shield', description: 'Removed by administrator' },
        RESUBSCRIBE: { source: 'resubscribe', label: 'Portal Re-subscription', icon: 'fa-redo', description: 'Contact re-subscribed via portal' },
        API: { source: 'api', label: 'API', icon: 'fa-code', description: 'Removed via API' },
        EXPIRY: { source: 'expiry', label: 'Opt-Out Expired', icon: 'fa-clock', description: 'Automatic expiry after retention period' }
    };

    var CONTACT_CREATION_SOURCES = {
        CSV_IMPORT: { source: 'csv_import', label: 'CSV Import', icon: 'fa-file-csv', description: 'Imported from CSV file' },
        API: { source: 'api', label: 'API', icon: 'fa-code', description: 'Created via API' },
        MANUAL: { source: 'manual', label: 'Manual Entry', icon: 'fa-keyboard', description: 'Manually entered by user' },
        FORM: { source: 'form', label: 'Form Submission', icon: 'fa-wpforms', description: 'Submitted via web form' },
        INBOUND: { source: 'inbound', label: 'Inbound Message', icon: 'fa-inbox', description: 'Auto-created from inbound message' }
    };

    var CONTACT_FIELDS = [
        { field: 'first_name', label: 'First Name', sensitive: false },
        { field: 'last_name', label: 'Last Name', sensitive: false },
        { field: 'email', label: 'Email', sensitive: true },
        { field: 'mobile', label: 'Mobile Number', sensitive: true },
        { field: 'company', label: 'Company', sensitive: false },
        { field: 'notes', label: 'Notes', sensitive: false },
        { field: 'custom_field_1', label: 'Custom Field 1', sensitive: false },
        { field: 'custom_field_2', label: 'Custom Field 2', sensitive: false },
        { field: 'date_of_birth', label: 'Date of Birth', sensitive: true },
        { field: 'address', label: 'Address', sensitive: true }
    ];

    var SAMPLE_FIELD_VALUES = {
        first_name: ['John', 'Jane', 'Michael', 'Sarah', 'David'],
        last_name: ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones'],
        email: ['john@example.com', 'jane@company.co.uk', 'info@business.com'],
        mobile: ['+44 7700 900123', '+44 7700 900456', '+44 7700 900789'],
        company: ['Acme Ltd', 'Tech Corp', 'Global Industries', 'Local Business'],
        notes: ['VIP customer', 'Prefers email contact', 'Follow up required'],
        custom_field_1: ['Value A', 'Value B', 'Value C'],
        custom_field_2: ['Option 1', 'Option 2', 'Option 3'],
        date_of_birth: ['1985-03-15', '1990-07-22', '1978-11-08'],
        address: ['123 Main St, London', '456 High St, Manchester', '789 Oak Ave, Bristol']
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
        
        if (['inbound_sms', 'inbound_rcs', 'rcs_button_click'].includes(eventType)) {
            sourceModule = SOURCE_MODULES.INBOX;
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

    var CHANNEL_TYPES = {
        SMS: 'sms',
        RCS_BASIC: 'rcs_basic',
        RCS_SINGLE: 'rcs_single',
        RCS_RICH: 'rcs_rich'
    };

    var CHANNEL_LABELS = {
        'sms': 'SMS',
        'rcs_basic': 'RCS Basic',
        'rcs_single': 'RCS Single',
        'rcs_rich': 'Rich RCS'
    };

    var SENDER_TYPES = {
        SENDER_ID: 'sender_id',
        VMN: 'vmn',
        RCS_AGENT: 'rcs_agent'
    };

    var ORIGIN_TYPES = {
        PORTAL: 'Portal',
        API: 'API',
        EMAIL_TO_SMS: 'Email-to-SMS',
        INTEGRATION: 'Integration'
    };

    function generateOutboundMetadata(sourceModule) {
        var campaigns = ['Winter Sale 2026', 'New Year Promo', 'Holiday Greetings', 'Boxing Day Deals', 'January Clearance'];
        var senderIds = ['QuickSMS', 'MyBrand', 'AlertSvc', 'NotifySys'];
        var vmns = ['+447700900001', '+447700900002', '+447700900003'];
        var rcsAgents = ['QuickSMS Business', 'My Brand Agent', 'Support Bot'];
        
        var channelKeys = Object.keys(CHANNEL_TYPES);
        var channelKey = channelKeys[Math.floor(Math.random() * channelKeys.length)];
        var channel = CHANNEL_TYPES[channelKey];
        var channelLabel = CHANNEL_LABELS[channel];
        
        var isRcs = channel.startsWith('rcs');
        var senderType, senderValue;
        if (isRcs) {
            senderType = SENDER_TYPES.RCS_AGENT;
            senderValue = rcsAgents[Math.floor(Math.random() * rcsAgents.length)];
        } else {
            senderType = Math.random() > 0.7 ? SENDER_TYPES.VMN : SENDER_TYPES.SENDER_ID;
            senderValue = senderType === SENDER_TYPES.VMN 
                ? vmns[Math.floor(Math.random() * vmns.length)]
                : senderIds[Math.floor(Math.random() * senderIds.length)];
        }
        
        var origin;
        switch(sourceModule) {
            case SOURCE_MODULES.CAMPAIGN: origin = ORIGIN_TYPES.PORTAL; break;
            case SOURCE_MODULES.INBOX: origin = ORIGIN_TYPES.PORTAL; break;
            case SOURCE_MODULES.API: origin = ORIGIN_TYPES.API; break;
            case SOURCE_MODULES.EMAIL_TO_SMS: origin = ORIGIN_TYPES.EMAIL_TO_SMS; break;
            default: origin = ORIGIN_TYPES.INTEGRATION;
        }
        
        var hasCampaign = sourceModule === SOURCE_MODULES.CAMPAIGN;
        var campaignId = hasCampaign ? 'camp_' + Math.floor(Math.random() * 10000) : null;
        var campaignName = hasCampaign ? campaigns[Math.floor(Math.random() * campaigns.length)] : null;
        
        var messageIdWarehouse = 'wh_' + generateUUID().substring(0, 12);
        var messageIdProvider = 'prov_' + generateUUID().substring(0, 8);
        var threadId = 'thread_' + generateUUID().substring(0, 8);
        
        var parts = channel === CHANNEL_TYPES.SMS ? (Math.random() > 0.7 ? Math.floor(Math.random() * 3) + 2 : 1) : 1;
        
        var costCredits = channel === CHANNEL_TYPES.SMS 
            ? (parts * 0.035).toFixed(3)
            : (channel === CHANNEL_TYPES.RCS_RICH ? '0.12' : '0.08');
        
        var snippetRaw = hasCampaign 
            ? 'Hi {{firstName}}, check out our exclusive {{campaignName}} deals!'
            : (sourceModule === SOURCE_MODULES.INBOX 
                ? 'Thank you for your message. We will respond shortly.'
                : 'Your verification code is: 123456');
        
        return {
            channel: channel,
            channel_label: channelLabel,
            sender_type: senderType,
            sender_value: senderValue,
            origin: origin,
            campaign_id: campaignId,
            campaign_name: campaignName,
            message_id_warehouse: messageIdWarehouse,
            message_id_provider: messageIdProvider,
            thread_id: threadId,
            parts: parts,
            cost_credits: costCredits,
            snippet_raw: snippetRaw,
            has_personalisation: snippetRaw.includes('{{'),
            is_blocked: false
        };
    }

    function generateBlockedOutboundMetadata() {
        var blockReasons = ['Opted out', 'Blacklisted number', 'Rate limit exceeded', 'Account suspended'];
        var reason = blockReasons[Math.floor(Math.random() * blockReasons.length)];
        
        return {
            channel: CHANNEL_TYPES.SMS,
            channel_label: 'SMS',
            is_blocked: true,
            block_reason: reason,
            origin: ORIGIN_TYPES.PORTAL
        };
    }

    function buildOutboundSummary(metadata, sourceModule) {
        if (metadata.is_blocked) {
            return 'Message blocked: ' + metadata.block_reason;
        }
        
        var channelLabel = metadata.channel_label || 'SMS';
        var sourceLabel = '';
        
        switch(sourceModule) {
            case SOURCE_MODULES.CAMPAIGN:
                sourceLabel = 'Campaign: ' + (metadata.campaign_name || 'Unknown');
                break;
            case SOURCE_MODULES.INBOX:
                sourceLabel = 'Inbox reply';
                break;
            case SOURCE_MODULES.API:
                sourceLabel = 'API';
                break;
            case SOURCE_MODULES.EMAIL_TO_SMS:
                sourceLabel = 'Email-to-SMS';
                break;
            default:
                sourceLabel = 'Integration';
        }
        
        return channelLabel + ' sent via ' + sourceLabel;
    }

    function buildOutboundDetails(metadata, permissions) {
        permissions = permissions || { viewCost: true, viewSnippet: true, viewPersonalised: false };
        
        if (metadata.is_blocked) {
            return '<div class="text-danger"><strong>Status:</strong> Blocked</div>' +
                '<div><strong>Reason:</strong> ' + metadata.block_reason + '</div>';
        }
        
        var html = '';
        
        html += '<div class="mb-1"><strong>Channel:</strong> ' + metadata.channel_label + '</div>';
        
        var senderLabel = metadata.sender_type === SENDER_TYPES.VMN ? 'VMN' 
            : (metadata.sender_type === SENDER_TYPES.RCS_AGENT ? 'RCS Agent' : 'Sender ID');
        html += '<div class="mb-1"><strong>' + senderLabel + ':</strong> ' + metadata.sender_value + '</div>';
        
        html += '<div class="mb-1"><strong>Origin:</strong> ' + metadata.origin + '</div>';
        
        if (metadata.campaign_id) {
            html += '<div class="mb-1"><strong>Campaign:</strong> ' + metadata.campaign_name + ' <span class="text-muted small">(' + metadata.campaign_id + ')</span></div>';
        }
        
        html += '<div class="mb-1"><strong>Message ID:</strong> ' + metadata.message_id_warehouse + '</div>';
        html += '<div class="mb-1"><strong>Provider Ref:</strong> ' + metadata.message_id_provider + '</div>';
        
        if (metadata.parts > 1) {
            html += '<div class="mb-1"><strong>Parts/Fragments:</strong> ' + metadata.parts + '</div>';
        }
        
        if (permissions.viewCost && metadata.cost_credits) {
            html += '<div class="mb-1"><strong>Cost:</strong> ' + metadata.cost_credits + ' credits</div>';
        }
        
        if (permissions.viewSnippet && metadata.snippet_raw) {
            var snippet = metadata.snippet_raw;
            if (metadata.has_personalisation && !permissions.viewPersonalised) {
                snippet = snippet.replace(/\{\{[^}]+\}\}/g, '<span class="badge badge-pastel-secondary">{{...}}</span>');
            }
            html += '<div class="mb-2"><strong>Message:</strong><div class="bg-white border rounded p-2 mt-1 small">' + snippet + '</div></div>';
        }
        
        return html;
    }

    function buildOutboundActions(metadata, sourceModule) {
        var actions = [];
        var permissions = getUserPermissions();
        
        if (metadata.is_blocked) {
            return actions;
        }
        
        if (sourceModule === SOURCE_MODULES.CAMPAIGN && metadata.campaign_id) {
            actions.push({
                type: 'link',
                label: 'View Campaign',
                icon: 'fa-bullhorn',
                url: '/messages/campaign-history/' + metadata.campaign_id,
                target: '_self'
            });
        }
        
        if (sourceModule === SOURCE_MODULES.INBOX && metadata.thread_id) {
            actions.push({
                type: 'link',
                label: 'Open Conversation',
                icon: 'fa-comments',
                url: '/messages/inbox?thread=' + metadata.thread_id,
                target: '_self'
            });
        }
        
        if (metadata.message_id_warehouse && permissions.viewMessageLog) {
            actions.push({
                type: 'link',
                label: 'View Message Log',
                icon: 'fa-file-alt',
                url: '/reporting/message-log/' + metadata.message_id_warehouse,
                target: '_self'
            });
        }
        
        return actions;
    }

    var AuditLogger = {
        emit: function(eventType, payload) {
            var auditEvent = {
                event_type: eventType,
                timestamp: new Date().toISOString(),
                user_id: window.currentUserId || null,
                user_name: window.currentUserName || 'Unknown',
                tenant_id: window.currentTenantId || null,
                session_id: window.sessionId || null,
                ip_address: null,
                user_agent: navigator.userAgent,
                payload: payload
            };
            
            if (config.useMockData) {
                console.log('[AuditLogger] Event emitted:', eventType, auditEvent);
                return Promise.resolve({ success: true, event_id: generateUUID() });
            }
            
            return fetch('/api/audit/emit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(auditEvent)
            }).then(function(response) {
                return response.json();
            }).catch(function(error) {
                console.error('[AuditLogger] Failed to emit event:', error);
                return { success: false, error: error.message };
            });
        },
        
        EVENTS: {
            TIMELINE_VIEWED: 'contact.timeline.viewed',
            MSISDN_REVEALED: 'contact.msisdn.revealed',
            MESSAGE_CONTENT_REVEALED: 'message.content.revealed',
            TIMELINE_EXPORTED: 'contact.timeline.exported'
        }
    };

    function generateDeliveryTimestamps(baseTimestamp) {
        var base = new Date(baseTimestamp);
        var submitOffset = Math.floor(Math.random() * 5) * 1000;
        var deliveryOffset = (5 + Math.floor(Math.random() * 30)) * 1000;
        
        return {
            submitted_at: new Date(base.getTime() - deliveryOffset - submitOffset).toISOString(),
            sent_at: new Date(base.getTime() - deliveryOffset).toISOString(),
            delivered_at: base.toISOString(),
            latency_ms: deliveryOffset
        };
    }

    function formatTimestamp(isoString) {
        if (!isoString) return 'N/A';
        var d = new Date(isoString);
        return d.toLocaleString('en-GB', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric',
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit'
        });
    }

    function buildStatusPill(statusInfo) {
        var pastelColors = {
            'success': 'badge-pastel-success',
            'danger': 'badge-pastel-danger',
            'warning': 'badge-pastel-warning',
            'info': 'badge-pastel-info',
            'secondary': 'badge-pastel-secondary',
            'primary': 'badge-pastel-primary'
        };
        var badgeClass = pastelColors[statusInfo.color] || 'badge-pastel-secondary';
        return '<span class="badge ' + badgeClass + '">' +
            '<i class="fas ' + statusInfo.icon + ' me-1"></i>' + statusInfo.status +
        '</span>';
    }

    function buildDeliveryDetails(metadata, statusInfo, timestamps, errorInfo) {
        var html = '';
        
        html += '<div class="mb-2">' +
            '<strong>Status:</strong> ' + buildStatusPill(statusInfo) +
        '</div>';
        
        if (metadata.related_message_id) {
            html += '<div class="mb-1 p-2 bg-white border-start border-3 border-primary">' +
                '<small class="text-muted"><i class="fas fa-link me-1"></i>Related to Message ID:</small> ' +
                '<code class="small">' + metadata.related_message_id + '</code>' +
            '</div>';
        }
        
        html += '<div class="mb-1"><strong>Channel:</strong> ' + metadata.channel_label + '</div>';
        
        if (timestamps) {
            if (timestamps.submitted_at) {
                html += '<div class="mb-1"><strong>Submitted:</strong> ' + formatTimestamp(timestamps.submitted_at) + '</div>';
            }
            if (timestamps.sent_at) {
                html += '<div class="mb-1"><strong>Sent:</strong> ' + formatTimestamp(timestamps.sent_at) + '</div>';
            }
            if (timestamps.delivered_at && (statusInfo.status === 'Delivered' || statusInfo.status === 'Seen')) {
                html += '<div class="mb-1"><strong>Delivered:</strong> ' + formatTimestamp(timestamps.delivered_at) + '</div>';
            }
            if (timestamps.latency_ms && (statusInfo.status === 'Delivered' || statusInfo.status === 'Seen')) {
                var latencySec = (timestamps.latency_ms / 1000).toFixed(1);
                html += '<div class="mb-1"><strong>Delivery Time:</strong> ' + latencySec + 's</div>';
            }
        }
        
        if (errorInfo && errorInfo.code) {
            html += '<div class="mb-1 text-danger">' +
                '<strong>Error Code:</strong> ' + errorInfo.code +
            '</div>';
            html += '<div class="mb-1 text-danger">' +
                '<strong>Error Description:</strong> ' + errorInfo.description +
            '</div>';
        }
        
        html += '<div class="mb-1"><strong>Message ID:</strong> ' + metadata.message_id_warehouse + '</div>';
        
        if (metadata.campaign_id) {
            html += '<div class="mb-1"><strong>Campaign:</strong> ' + metadata.campaign_name + '</div>';
        }
        
        html += '<div class="mb-1"><strong>Sender:</strong> ' + metadata.sender_value + '</div>';
        
        return html;
    }

    function getUserPermissions() {
        return window.timelinePermissions || {
            viewCost: false,
            viewSnippet: false,
            viewPersonalised: false,
            viewSensitiveData: false,
            viewMessageLog: false,
            revealMsisdn: false,
            revealContent: false,
            exportTimeline: false
        };
    }

    function maskMsisdn(msisdn) {
        if (!msisdn) return '***';
        var cleaned = msisdn.replace(/\s/g, '');
        if (cleaned.length <= 6) return '***' + cleaned.slice(-2);
        return cleaned.slice(0, 4) + ' **** ' + cleaned.slice(-3);
    }

    function maskContent(content, maxLength) {
        maxLength = maxLength || 20;
        if (!content) return '[Content hidden]';
        return '[Message content hidden - ' + content.length + ' characters]';
    }

    function logTimelineViewed(contactId) {
        return AuditLogger.emit(AuditLogger.EVENTS.TIMELINE_VIEWED, {
            contact_id: contactId,
            action: 'view'
        });
    }

    function revealMsisdnWithAudit(contactId, reason) {
        var permissions = getUserPermissions();
        if (!permissions.revealMsisdn) {
            return Promise.reject(new Error('Permission denied: revealMsisdn'));
        }
        
        return AuditLogger.emit(AuditLogger.EVENTS.MSISDN_REVEALED, {
            contact_id: contactId,
            reason: reason || 'User requested reveal'
        }).then(function() {
            if (config.useMockData) {
                return simulateDelay(200, 400).then(function() {
                    return {
                        success: true,
                        msisdn: '+44 7700 900' + Math.floor(Math.random() * 1000).toString().padStart(3, '0')
                    };
                });
            }
            
            return fetch('/api/contacts/' + contactId + '/reveal-msisdn', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ reason: reason })
            }).then(function(response) {
                return response.json();
            });
        });
    }

    function revealMessageContentWithAudit(messageId, contactId, reason) {
        var permissions = getUserPermissions();
        if (!permissions.revealContent) {
            return Promise.reject(new Error('Permission denied: revealContent'));
        }
        
        return AuditLogger.emit(AuditLogger.EVENTS.MESSAGE_CONTENT_REVEALED, {
            message_id: messageId,
            contact_id: contactId,
            reason: reason || 'User requested reveal'
        }).then(function() {
            if (config.useMockData) {
                return simulateDelay(200, 400).then(function() {
                    var sampleMessages = [
                        'Hi {{firstName}}, your order #12345 has been dispatched!',
                        'Reminder: Your appointment is tomorrow at 2pm.',
                        'Thank you for your purchase! Use code SAVE10 for 10% off.',
                        'Your verification code is 847291. Valid for 5 minutes.'
                    ];
                    return {
                        success: true,
                        content: sampleMessages[Math.floor(Math.random() * sampleMessages.length)]
                    };
                });
            }
            
            return fetch('/api/messages/' + messageId + '/reveal-content', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ reason: reason, contact_id: contactId })
            }).then(function(response) {
                return response.json();
            });
        });
    }

    function exportTimelineWithAudit(contactId, filters, format) {
        var permissions = getUserPermissions();
        if (!permissions.exportTimeline) {
            return Promise.reject(new Error('Permission denied: exportTimeline'));
        }
        
        format = format || 'csv';
        
        return AuditLogger.emit(AuditLogger.EVENTS.TIMELINE_EXPORTED, {
            contact_id: contactId,
            format: format,
            filters: filters,
            masked: true
        }).then(function() {
            if (config.useMockData) {
                console.log('[Timeline] Export requested for contact:', contactId, 'Format:', format);
                return Promise.resolve({
                    success: true,
                    download_url: '/api/contacts/' + contactId + '/timeline/export?format=' + format + '&token=mock_' + generateUUID()
                });
            }
            
            return fetch('/api/contacts/' + contactId + '/timeline/export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ format: format, filters: filters })
            }).then(function(response) {
                return response.json();
            });
        });
    }

    function simulateDelay(min, max) {
        var delay = min + Math.floor(Math.random() * (max - min));
        return new Promise(function(resolve) {
            setTimeout(resolve, delay);
        });
    }

    function getRandomList() {
        return CONTACT_BOOK_LISTS[Math.floor(Math.random() * CONTACT_BOOK_LISTS.length)];
    }

    function getRandomListMethod() {
        var methods = Object.keys(LIST_CHANGE_METHODS);
        var methodKey = methods[Math.floor(Math.random() * methods.length)];
        return LIST_CHANGE_METHODS[methodKey];
    }

    function buildListChangeDetails(listInfo, methodInfo, operation) {
        var html = '';
        
        html += '<div class="mb-2">' +
            '<strong>Operation:</strong> ' +
            '<span class="badge ' + (operation === 'add' ? 'badge-pastel-success' : 'badge-pastel-secondary') + '">' +
            '<i class="fas ' + (operation === 'add' ? 'fa-plus' : 'fa-minus') + ' me-1"></i>' +
            (operation === 'add' ? 'Added' : 'Removed') +
            '</span>' +
        '</div>';
        
        html += '<div class="mb-1"><strong>List ID:</strong> <code class="small">' + listInfo.id + '</code></div>';
        html += '<div class="mb-1"><strong>List Name:</strong> ' + listInfo.name + '</div>';
        
        if (listInfo.description) {
            html += '<div class="mb-1"><strong>Description:</strong> ' + listInfo.description + '</div>';
        }
        
        html += '<div class="mb-2">' +
            '<strong>Method:</strong> ' +
            '<span class="badge badge-pastel-primary">' +
            '<i class="fas ' + methodInfo.icon + ' me-1"></i>' + methodInfo.label +
            '</span>' +
        '</div>';
        
        html += '<div class="mb-1 text-muted small">' +
            '<i class="fas fa-info-circle me-1"></i>' + methodInfo.description +
        '</div>';
        
        return html;
    }

    function getRandomTag() {
        return CONTACT_BOOK_TAGS[Math.floor(Math.random() * CONTACT_BOOK_TAGS.length)];
    }

    function getRandomTagMethod() {
        var methods = Object.keys(TAG_CHANGE_METHODS);
        var methodKey = methods[Math.floor(Math.random() * methods.length)];
        return TAG_CHANGE_METHODS[methodKey];
    }

    function buildTagPill(tagInfo) {
        return '<span class="badge badge-pastel-' + tagInfo.color + '">' +
            '<i class="fas fa-tag me-1"></i>' + tagInfo.name +
        '</span>';
    }

    function buildTagChangeDetails(tagInfo, methodInfo, operation) {
        var html = '';
        
        html += '<div class="mb-2">' +
            '<strong>Operation:</strong> ' +
            '<span class="badge ' + (operation === 'add' ? 'badge-pastel-success' : 'badge-pastel-secondary') + '">' +
            '<i class="fas ' + (operation === 'add' ? 'fa-plus' : 'fa-minus') + ' me-1"></i>' +
            (operation === 'add' ? 'Added' : 'Removed') +
            '</span>' +
        '</div>';
        
        html += '<div class="mb-2">' +
            '<strong>Tag:</strong> ' + buildTagPill(tagInfo) +
        '</div>';
        
        html += '<div class="mb-1"><strong>Tag ID:</strong> <code class="small">' + tagInfo.id + '</code></div>';
        
        html += '<div class="mb-2">' +
            '<strong>Method:</strong> ' +
            '<span class="badge badge-pastel-primary">' +
            '<i class="fas ' + methodInfo.icon + ' me-1"></i>' + methodInfo.label +
            '</span>' +
        '</div>';
        
        html += '<div class="mb-1 text-muted small">' +
            '<i class="fas fa-info-circle me-1"></i>' + methodInfo.description +
        '</div>';
        
        return html;
    }

    function getRandomOptoutSource() {
        var sources = Object.keys(OPTOUT_SOURCES);
        var sourceKey = sources[Math.floor(Math.random() * sources.length)];
        return OPTOUT_SOURCES[sourceKey];
    }

    function getRandomOptinSource() {
        var sources = Object.keys(OPTIN_SOURCES);
        var sourceKey = sources[Math.floor(Math.random() * sources.length)];
        return OPTIN_SOURCES[sourceKey];
    }

    function getRandomOptoutScope(lists) {
        var isMaster = Math.random() < 0.4;
        if (isMaster) {
            return {
                scope_type: OPTOUT_SCOPES.MASTER,
                list: null
            };
        } else {
            var list = lists[Math.floor(Math.random() * lists.length)];
            return {
                scope_type: OPTOUT_SCOPES.LIST,
                list: list
            };
        }
    }

    function buildOptoutDetails(scopeInfo, sourceInfo, keyword, actorName) {
        var html = '';
        
        html += '<div class="mb-2 p-2 bg-warning bg-opacity-10 border border-warning rounded">' +
            '<div class="d-flex align-items-center">' +
                '<i class="fas fa-exclamation-triangle text-warning me-2"></i>' +
                '<strong class="text-warning">Compliance Notice</strong>' +
            '</div>' +
            '<div class="mt-1 small">' +
                (scopeInfo.scope_type.blocks_all 
                    ? '<i class="fas fa-ban text-danger me-1"></i>This contact is blocked from receiving <strong>all messages</strong>.'
                    : '<i class="fas fa-list text-warning me-1"></i>This contact is blocked from list: <strong>' + scopeInfo.list.name + '</strong> only.') +
            '</div>' +
        '</div>';
        
        html += '<div class="mb-2">' +
            '<strong>Scope:</strong> ' +
            '<span class="badge badge-pastel-warning">' +
                '<i class="fas ' + (scopeInfo.scope_type.blocks_all ? 'fa-globe' : 'fa-list') + ' me-1"></i>' +
                scopeInfo.scope_type.label +
            '</span>' +
        '</div>';
        
        if (scopeInfo.list) {
            html += '<div class="mb-1"><strong>List:</strong> ' + scopeInfo.list.name + ' (<code class="small">' + scopeInfo.list.id + '</code>)</div>';
        }
        
        html += '<div class="mb-2">' +
            '<strong>Source:</strong> ' +
            '<span class="badge badge-pastel-secondary">' +
                '<i class="fas ' + sourceInfo.icon + ' me-1"></i>' + sourceInfo.label +
            '</span>' +
        '</div>';
        
        if (keyword) {
            html += '<div class="mb-1"><strong>Keyword:</strong> <code class="bg-light px-2 py-1 rounded">' + keyword + '</code></div>';
        }
        
        if (actorName && sourceInfo.source === 'manual') {
            html += '<div class="mb-1"><strong>Added by:</strong> ' + actorName + '</div>';
        }
        
        html += '<div class="mb-1 text-muted small">' +
            '<i class="fas fa-info-circle me-1"></i>' + sourceInfo.description +
        '</div>';
        
        return html;
    }

    function buildOptinDetails(sourceInfo, scopeInfo, actorName) {
        var html = '';
        
        html += '<div class="mb-2 p-2 bg-success bg-opacity-10 border border-success rounded">' +
            '<div class="d-flex align-items-center">' +
                '<i class="fas fa-check-circle text-success me-2"></i>' +
                '<strong class="text-success">Opt-Out Removed</strong>' +
            '</div>' +
            '<div class="mt-1 small">' +
                (scopeInfo && scopeInfo.scope_type && !scopeInfo.scope_type.blocks_all
                    ? '<i class="fas fa-list text-success me-1"></i>Contact can now receive messages from list: <strong>' + scopeInfo.list.name + '</strong>.'
                    : '<i class="fas fa-envelope text-success me-1"></i>Contact can now receive <strong>all messages</strong>.') +
            '</div>' +
        '</div>';
        
        html += '<div class="mb-2">' +
            '<strong>Source:</strong> ' +
            '<span class="badge badge-pastel-success">' +
                '<i class="fas ' + sourceInfo.icon + ' me-1"></i>' + sourceInfo.label +
            '</span>' +
        '</div>';
        
        if (scopeInfo && scopeInfo.list) {
            html += '<div class="mb-1"><strong>List:</strong> ' + scopeInfo.list.name + '</div>';
        }
        
        if (actorName && (sourceInfo.source === 'admin')) {
            html += '<div class="mb-1"><strong>Removed by:</strong> ' + actorName + '</div>';
        }
        
        html += '<div class="mb-1 text-muted small">' +
            '<i class="fas fa-info-circle me-1"></i>' + sourceInfo.description +
        '</div>';
        
        return html;
    }

    function getRandomCreationSource() {
        var sources = Object.keys(CONTACT_CREATION_SOURCES);
        var sourceKey = sources[Math.floor(Math.random() * sources.length)];
        return CONTACT_CREATION_SOURCES[sourceKey];
    }

    function getRandomFieldChange() {
        var field = CONTACT_FIELDS[Math.floor(Math.random() * CONTACT_FIELDS.length)];
        var values = SAMPLE_FIELD_VALUES[field.field] || ['Value 1', 'Value 2', 'Value 3'];
        var oldValue = values[Math.floor(Math.random() * values.length)];
        var newValue = values[Math.floor(Math.random() * values.length)];
        while (newValue === oldValue && values.length > 1) {
            newValue = values[Math.floor(Math.random() * values.length)];
        }
        return {
            field: field.field,
            label: field.label,
            sensitive: field.sensitive,
            old_value: oldValue,
            new_value: newValue
        };
    }

    function maskSensitiveValue(value) {
        if (!value) return '***';
        if (value.length <= 4) return '***';
        return value.substring(0, 2) + '***' + value.substring(value.length - 2);
    }

    function buildContactCreatedDetails(sourceInfo, actorName, initialFields, canViewSensitive) {
        var html = '';
        
        html += '<div class="mb-2">' +
            '<strong>Source:</strong> ' +
            '<span class="badge badge-pastel-primary">' +
                '<i class="fas ' + sourceInfo.icon + ' me-1"></i>' + sourceInfo.label +
            '</span>' +
        '</div>';
        
        if (actorName) {
            html += '<div class="mb-1"><strong>Created by:</strong> ' + actorName + '</div>';
        }
        
        if (initialFields && initialFields.length > 0) {
            html += '<div class="mb-2"><strong>Initial Fields:</strong></div>';
            html += '<table class="table table-sm table-bordered mb-0 small">';
            html += '<thead class="table-light"><tr><th>Field</th><th>Value</th></tr></thead>';
            html += '<tbody>';
            initialFields.forEach(function(f) {
                var displayValue = f.value;
                if (f.sensitive && !canViewSensitive) {
                    displayValue = '<span class="text-muted"><i class="fas fa-lock me-1"></i>' + maskSensitiveValue(f.value) + '</span>';
                }
                html += '<tr><td>' + f.label + '</td><td>' + displayValue + '</td></tr>';
            });
            html += '</tbody></table>';
        }
        
        html += '<div class="mt-2 text-muted small">' +
            '<i class="fas fa-info-circle me-1"></i>' + sourceInfo.description +
        '</div>';
        
        return html;
    }

    function buildContactUpdatedDetails(changes, sourceInfo, actorName, canViewSensitive) {
        var html = '';
        
        html += '<div class="mb-2">' +
            '<strong>Source:</strong> ' +
            '<span class="badge badge-pastel-secondary">' +
                '<i class="fas ' + sourceInfo.icon + ' me-1"></i>' + sourceInfo.label +
            '</span>' +
        '</div>';
        
        if (actorName) {
            html += '<div class="mb-1"><strong>Updated by:</strong> ' + actorName + '</div>';
        }
        
        html += '<div class="mb-2"><strong>Changes:</strong></div>';
        html += '<table class="table table-sm table-bordered mb-0 small">';
        html += '<thead class="table-light"><tr><th>Field</th><th>Old Value</th><th>New Value</th></tr></thead>';
        html += '<tbody>';
        
        changes.forEach(function(change) {
            var oldDisplay = change.old_value;
            var newDisplay = change.new_value;
            
            if (change.sensitive && !canViewSensitive) {
                oldDisplay = '<span class="text-muted"><i class="fas fa-lock me-1"></i>' + maskSensitiveValue(change.old_value) + '</span>';
                newDisplay = '<span class="text-muted"><i class="fas fa-lock me-1"></i>' + maskSensitiveValue(change.new_value) + '</span>';
            }
            
            html += '<tr>' +
                '<td>' + change.label + (change.sensitive ? ' <i class="fas fa-shield-alt text-warning" title="Sensitive field"></i>' : '') + '</td>' +
                '<td class="text-danger"><del>' + oldDisplay + '</del></td>' +
                '<td class="text-success">' + newDisplay + '</td>' +
            '</tr>';
        });
        
        html += '</tbody></table>';
        
        if (!canViewSensitive) {
            html += '<div class="mt-2 small text-muted">' +
                '<i class="fas fa-lock me-1"></i>Some values are masked. You need additional permissions to view sensitive data.' +
            '</div>';
        }
        
        return html;
    }

    function generateInboundMetadata(channel) {
        var messageId = 'inb_' + generateUUID().substring(0, 12);
        var conversationId = 'conv_' + generateUUID().substring(0, 8);
        var threadId = 'thread_' + generateUUID().substring(0, 8);
        
        var inboundMessages = [
            'Yes, I would like more information',
            'Thanks for the update!',
            'Can you call me back?',
            'What are your opening hours?',
            'Please remove me from this list',
            'I have a question about my order'
        ];
        
        var channelLabel = channel === 'sms' ? 'SMS' : (CHANNEL_LABELS[channel] || 'RCS');
        
        return {
            channel: channel,
            channel_label: channelLabel,
            message_id: messageId,
            conversation_id: conversationId,
            thread_id: threadId,
            message_preview: inboundMessages[Math.floor(Math.random() * inboundMessages.length)]
        };
    }

    function generateRcsButtonClickMetadata() {
        var buttonLabels = [
            { label: 'View Details', action: 'openUrl', payload: 'https://example.com/product/123' },
            { label: 'Call Us', action: 'dialPhone', payload: '+441onal234567' },
            { label: 'Get Directions', action: 'openMap', payload: 'geo:51.5074,-0.1278' },
            { label: 'Yes, I am interested', action: 'reply', payload: 'INTERESTED_YES' },
            { label: 'No thanks', action: 'reply', payload: 'INTERESTED_NO' },
            { label: 'Book Now', action: 'openUrl', payload: 'https://example.com/book' },
            { label: 'Learn More', action: 'openUrl', payload: 'https://example.com/info' }
        ];
        
        var button = buttonLabels[Math.floor(Math.random() * buttonLabels.length)];
        var messageId = 'rcs_' + generateUUID().substring(0, 12);
        var conversationId = 'conv_' + generateUUID().substring(0, 8);
        var threadId = 'thread_' + generateUUID().substring(0, 8);
        
        return {
            channel: 'rcs_rich',
            channel_label: 'Rich RCS',
            message_id: messageId,
            conversation_id: conversationId,
            thread_id: threadId,
            button_label: button.label,
            button_action: button.action,
            button_payload: button.payload
        };
    }

    function buildInboundSummary(metadata, eventType) {
        if (eventType === EVENT_TYPES.RCS_BUTTON_CLICK) {
            return "'" + metadata.button_label + "'";
        }
        
        var channelLabel = metadata.channel_label || 'SMS';
        return 'Inbound ' + channelLabel + ' received';
    }

    function buildInboundDetails(metadata, eventType) {
        var html = '';
        
        html += '<div class="mb-1"><strong>Channel:</strong> ' + metadata.channel_label + '</div>';
        html += '<div class="mb-1"><strong>Message ID:</strong> ' + metadata.message_id + '</div>';
        html += '<div class="mb-1"><strong>Conversation ID:</strong> <a href="/messages/inbox?thread=' + metadata.thread_id + '" class="text-primary">' + metadata.conversation_id + '</a></div>';
        
        if (eventType === EVENT_TYPES.RCS_BUTTON_CLICK) {
            html += '<div class="mb-1"><strong>Button Label:</strong> ' + metadata.button_label + '</div>';
            html += '<div class="mb-1"><strong>Action:</strong> ' + formatButtonAction(metadata.button_action) + '</div>';
            html += '<div class="mb-1"><strong>Payload:</strong> <code class="small">' + metadata.button_payload + '</code></div>';
        } else if (metadata.message_preview) {
            html += '<div class="mb-2"><strong>Message:</strong><div class="bg-white border rounded p-2 mt-1 small">' + metadata.message_preview + '</div></div>';
        }
        
        return html;
    }

    function formatButtonAction(action) {
        var actionLabels = {
            'openUrl': 'Open URL',
            'dialPhone': 'Dial Phone',
            'openMap': 'Open Map',
            'reply': 'Quick Reply',
            'calendar': 'Add to Calendar'
        };
        return actionLabels[action] || action;
    }

    function buildInboundActions(metadata) {
        return [{
            type: 'link',
            label: 'Open Conversation',
            icon: 'fa-comments',
            url: '/messages/inbox?thread=' + metadata.thread_id,
            target: '_self'
        }];
    }

    function generateEventMetadata(eventType, sourceModule) {
        var campaigns = ['Winter Sale 2026', 'New Year Promo', 'Holiday Greetings', 'Boxing Day Deals', 'January Clearance'];
        var tags = ['VIP Customer', 'Newsletter', 'Promo Subscriber', 'High Value', 'New Lead'];
        var lists = ['Marketing Contacts', 'Newsletter Subscribers', 'Active Customers', 'Leads', 'Premium Members'];
        var channels = ['sms', 'rcs_basic', 'rcs_single', 'rcs_rich'];
        var channel = channels[Math.floor(Math.random() * channels.length)];
        
        switch(eventType) {
            case EVENT_TYPES.MESSAGE_SENT:
                var isBlocked = Math.random() < 0.1;
                var outboundMeta = isBlocked ? generateBlockedOutboundMetadata() : generateOutboundMetadata(sourceModule);
                var permissions = getUserPermissions();
                var summary = buildOutboundSummary(outboundMeta, sourceModule);
                var details = buildOutboundDetails(outboundMeta, permissions);
                var actions = buildOutboundActions(outboundMeta, sourceModule);
                
                return Object.assign({}, outboundMeta, {
                    summary: summary,
                    details: details,
                    actions: actions
                });
            
            case EVENT_TYPES.MESSAGE_DELIVERED:
                var deliveredMeta = generateOutboundMetadata(sourceModule);
                deliveredMeta.related_message_id = deliveredMeta.message_id_warehouse;
                var deliveredTimestamps = generateDeliveryTimestamps(new Date().toISOString());
                var deliveredStatus = DELIVERY_STATUSES.DELIVERED;
                var deliveredSummary = 'Delivery confirmed - ' + deliveredMeta.channel_label;
                var deliveredDetails = buildDeliveryDetails(deliveredMeta, deliveredStatus, deliveredTimestamps, null);
                return Object.assign({}, deliveredMeta, {
                    delivery_status: deliveredStatus.status,
                    timestamps: deliveredTimestamps,
                    summary: deliveredSummary,
                    details: deliveredDetails,
                    actions: buildOutboundActions(deliveredMeta, sourceModule)
                });
            
            case EVENT_TYPES.MESSAGE_FAILED:
                var failedMeta = generateOutboundMetadata(sourceModule);
                failedMeta.related_message_id = failedMeta.message_id_warehouse;
                var failedTimestamps = generateDeliveryTimestamps(new Date().toISOString());
                var isRejected = Math.random() < 0.3;
                var failedStatus = isRejected ? DELIVERY_STATUSES.REJECTED : DELIVERY_STATUSES.UNDELIVERABLE;
                var errorCodes = Object.keys(DELIVERY_ERROR_CODES);
                var randomErrorCode = errorCodes[Math.floor(Math.random() * errorCodes.length)];
                var errorInfo = {
                    code: randomErrorCode,
                    description: DELIVERY_ERROR_CODES[randomErrorCode]
                };
                var failedSummary = failedStatus.status + ' - ' + errorInfo.description;
                var failedDetails = buildDeliveryDetails(failedMeta, failedStatus, failedTimestamps, errorInfo);
                return Object.assign({}, failedMeta, {
                    delivery_status: failedStatus.status,
                    timestamps: failedTimestamps,
                    error_code: errorInfo.code,
                    error_message: errorInfo.description,
                    summary: failedSummary,
                    details: failedDetails,
                    actions: buildOutboundActions(failedMeta, sourceModule)
                });
            
            case EVENT_TYPES.MESSAGE_SEEN:
                var seenMeta = generateOutboundMetadata(sourceModule);
                seenMeta.channel = 'rcs_rich';
                seenMeta.channel_label = 'Rich RCS';
                seenMeta.related_message_id = seenMeta.message_id_warehouse;
                var seenTimestamps = generateDeliveryTimestamps(new Date().toISOString());
                var seenStatus = DELIVERY_STATUSES.SEEN;
                var seenSummary = 'Read receipt received - Rich RCS';
                var seenDetails = buildDeliveryDetails(seenMeta, seenStatus, seenTimestamps, null);
                return Object.assign({}, seenMeta, {
                    delivery_status: seenStatus.status,
                    timestamps: seenTimestamps,
                    summary: seenSummary,
                    details: seenDetails,
                    actions: buildOutboundActions(seenMeta, sourceModule)
                });
            
            case EVENT_TYPES.REPLY_RECEIVED:
                var replyMeta = generateOutboundMetadata(sourceModule);
                var replies = ['Thanks!', 'Yes please', 'Not interested', 'More info?', 'Great offer'];
                var reply = replies[Math.floor(Math.random() * replies.length)];
                return Object.assign({}, replyMeta, {
                    reply_preview: reply,
                    summary: '"' + reply + '"',
                    details: '<strong>Reply:</strong> "' + reply + '"<br>' +
                        '<strong>Channel:</strong> ' + replyMeta.channel_label + '<br>' +
                        '<strong>Message ID:</strong> ' + replyMeta.message_id_warehouse,
                    actions: [{
                        type: 'link',
                        label: 'Open Conversation',
                        icon: 'fa-comments',
                        url: '/messages/inbox?thread=' + replyMeta.thread_id,
                        target: '_self'
                    }]
                });
            
            case EVENT_TYPES.INBOUND_SMS:
                var inboundSmsMeta = generateInboundMetadata('sms');
                return Object.assign({}, inboundSmsMeta, {
                    summary: buildInboundSummary(inboundSmsMeta, eventType),
                    details: buildInboundDetails(inboundSmsMeta, eventType),
                    actions: buildInboundActions(inboundSmsMeta)
                });
            
            case EVENT_TYPES.INBOUND_RCS:
                var inboundRcsMeta = generateInboundMetadata(channels[Math.floor(Math.random() * 3) + 1]);
                return Object.assign({}, inboundRcsMeta, {
                    summary: buildInboundSummary(inboundRcsMeta, eventType),
                    details: buildInboundDetails(inboundRcsMeta, eventType),
                    actions: buildInboundActions(inboundRcsMeta)
                });
            
            case EVENT_TYPES.RCS_BUTTON_CLICK:
                var buttonClickMeta = generateRcsButtonClickMetadata();
                return Object.assign({}, buttonClickMeta, {
                    summary: buildInboundSummary(buttonClickMeta, eventType),
                    details: buildInboundDetails(buttonClickMeta, eventType),
                    actions: buildInboundActions(buttonClickMeta)
                });
            
            case EVENT_TYPES.TAG_ADDED:
                var addedTag = getRandomTag();
                var addTagMethod = getRandomTagMethod();
                return {
                    tag_id: addedTag.id,
                    tag_name: addedTag.name,
                    tag_color: addedTag.color,
                    operation: 'add',
                    method: addTagMethod.method,
                    method_label: addTagMethod.label,
                    summary: addedTag.name,
                    details: buildTagChangeDetails(addedTag, addTagMethod, 'add')
                };
            
            case EVENT_TYPES.TAG_REMOVED:
                var removedTag = getRandomTag();
                var removeTagMethod = getRandomTagMethod();
                return {
                    tag_id: removedTag.id,
                    tag_name: removedTag.name,
                    tag_color: removedTag.color,
                    operation: 'remove',
                    method: removeTagMethod.method,
                    method_label: removeTagMethod.label,
                    summary: removedTag.name,
                    details: buildTagChangeDetails(removedTag, removeTagMethod, 'remove')
                };
            
            case EVENT_TYPES.LIST_ADDED:
                var addedList = getRandomList();
                var addMethod = getRandomListMethod();
                return {
                    list_id: addedList.id,
                    list_name: addedList.name,
                    list_description: addedList.description,
                    operation: 'add',
                    method: addMethod.method,
                    method_label: addMethod.label,
                    summary: addedList.name,
                    details: buildListChangeDetails(addedList, addMethod, 'add')
                };
            
            case EVENT_TYPES.LIST_REMOVED:
                var removedList = getRandomList();
                var removeMethod = getRandomListMethod();
                return {
                    list_id: removedList.id,
                    list_name: removedList.name,
                    list_description: removedList.description,
                    operation: 'remove',
                    method: removeMethod.method,
                    method_label: removeMethod.label,
                    summary: removedList.name,
                    details: buildListChangeDetails(removedList, removeMethod, 'remove')
                };
            
            case EVENT_TYPES.OPTOUT:
                var optoutSource = getRandomOptoutSource();
                var optoutScope = getRandomOptoutScope(CONTACT_BOOK_LISTS);
                var optoutKeyword = optoutSource.source === 'inbound_keyword' 
                    ? OPTOUT_KEYWORDS[Math.floor(Math.random() * OPTOUT_KEYWORDS.length)] 
                    : null;
                var optoutToken = optoutSource.source === 'url' ? 'tok_' + generateUUID().substring(0, 8) : null;
                var optoutActorNames = ['Admin User', 'Support Agent', 'Compliance Team'];
                var optoutActor = optoutSource.source === 'manual' 
                    ? optoutActorNames[Math.floor(Math.random() * optoutActorNames.length)] 
                    : null;
                
                var optoutSummary = optoutScope.scope_type.blocks_all 
                    ? (optoutKeyword ? optoutKeyword + ' received' : 'Opted out')
                    : (optoutScope.list ? 'List: ' + optoutScope.list.name : 'Opted out') + 
                      (optoutKeyword ? ' — via ' + optoutKeyword : '');
                
                return {
                    keyword: optoutKeyword,
                    token: optoutToken,
                    scope: optoutScope.scope_type.scope,
                    scope_label: optoutScope.scope_type.label,
                    list_id: optoutScope.list ? optoutScope.list.id : null,
                    list_name: optoutScope.list ? optoutScope.list.name : null,
                    source: optoutSource.source,
                    source_label: optoutSource.label,
                    blocks_all: optoutScope.scope_type.blocks_all,
                    summary: optoutSummary,
                    details: buildOptoutDetails(optoutScope, optoutSource, optoutKeyword || optoutToken, optoutActor)
                };
            
            case EVENT_TYPES.OPTIN:
                var optinSource = getRandomOptinSource();
                var optinScope = getRandomOptoutScope(CONTACT_BOOK_LISTS);
                var optinActorNames = ['Admin User', 'Support Manager', 'Compliance Officer'];
                var optinActor = optinSource.source === 'admin' 
                    ? optinActorNames[Math.floor(Math.random() * optinActorNames.length)] 
                    : null;
                
                var optinSummary = optinSource.source === 'admin' 
                    ? 'Removed by ' + optinActor
                    : optinSource.label;
                
                return {
                    source: optinSource.source,
                    source_label: optinSource.label,
                    list_id: optinScope.list ? optinScope.list.id : null,
                    list_name: optinScope.list ? optinScope.list.name : null,
                    removed_by: optinActor,
                    summary: optinSummary,
                    details: buildOptinDetails(optinSource, optinScope, optinActor)
                };
            
            case EVENT_TYPES.CONTACT_CREATED:
                var creationSource = getRandomCreationSource();
                var creationActorNames = ['Admin User', 'Marketing Team', 'Support Agent', 'System'];
                var creationActor = creationSource.source === 'manual' 
                    ? creationActorNames[Math.floor(Math.random() * creationActorNames.length)]
                    : (creationSource.source === 'api' ? 'API Integration' : null);
                
                var initialFieldCount = 2 + Math.floor(Math.random() * 3);
                var shuffledFields = CONTACT_FIELDS.slice().sort(function() { return 0.5 - Math.random(); });
                var initialFields = shuffledFields.slice(0, initialFieldCount).map(function(f) {
                    var values = SAMPLE_FIELD_VALUES[f.field] || ['Sample Value'];
                    return {
                        field: f.field,
                        label: f.label,
                        sensitive: f.sensitive,
                        value: values[Math.floor(Math.random() * values.length)]
                    };
                });
                
                var contactPermissions = getUserPermissions();
                var canViewSensitiveData = contactPermissions.viewSensitiveData === true;
                
                return {
                    source: creationSource.source,
                    source_label: creationSource.label,
                    created_by: creationActor,
                    initial_fields: initialFields,
                    summary: creationSource.label,
                    details: buildContactCreatedDetails(creationSource, creationActor, initialFields, canViewSensitiveData)
                };
            
            case EVENT_TYPES.CONTACT_UPDATED:
                var updateSource = getRandomCreationSource();
                var updateActorNames = ['Admin User', 'Marketing Team', 'Support Agent', 'Compliance Team'];
                var updateActor = (updateSource.source === 'manual' || updateSource.source === 'csv_import')
                    ? updateActorNames[Math.floor(Math.random() * updateActorNames.length)]
                    : (updateSource.source === 'api' ? 'API Integration' : null);
                
                var changeCount = 1 + Math.floor(Math.random() * 3);
                var fieldChanges = [];
                for (var i = 0; i < changeCount; i++) {
                    fieldChanges.push(getRandomFieldChange());
                }
                
                var updatePermissions = getUserPermissions();
                var canViewSensitive = updatePermissions.viewSensitiveData === true;
                
                var changedFieldLabels = fieldChanges.map(function(c) { return c.label; });
                var updateSummary = changedFieldLabels.length === 1 
                    ? changedFieldLabels[0] + ' changed'
                    : changedFieldLabels.length + ' fields changed';
                
                return {
                    source: updateSource.source,
                    source_label: updateSource.label,
                    updated_by: updateActor,
                    changes: fieldChanges,
                    fields_changed: changedFieldLabels,
                    summary: updateSummary,
                    details: buildContactUpdatedDetails(fieldChanges, updateSource, updateActor, canViewSensitive)
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
        
        if (!cursor) {
            logTimelineViewed(contactId);
        }
        
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
        revealMsisdn: revealMsisdnWithAudit,
        revealMessageContent: revealMessageContentWithAudit,
        exportTimeline: exportTimelineWithAudit,
        clearCache: clearCache,
        formatEventDate: formatEventDate,
        maskMsisdn: maskMsisdn,
        maskContent: maskContent,
        AuditLogger: AuditLogger,
        getPermissions: getUserPermissions
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ContactTimelineService;
}

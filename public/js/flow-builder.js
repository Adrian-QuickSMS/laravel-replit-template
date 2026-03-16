/**
 * QuickSMS Flow Builder - Canvas Engine
 * Drag-and-drop visual flow builder for messaging automation.
 */

(function(window) {
    'use strict';

    // ========================================
    // Node type definitions
    // ========================================
    var NODE_TYPES = {
        // === TRIGGERS ===
        trigger_api: {
            label: 'API Trigger',
            icon: 'fa-plug',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            configFields: [
                { key: 'endpoint_note', type: 'info', text: 'This flow will be triggered via POST /flows/{id}/start' },
                { key: 'variables', type: 'textarea', label: 'Expected Variables (JSON)', placeholder: '{"phone": "", "name": ""}' }
            ]
        },
        trigger_webhook: {
            label: 'External Webhook',
            icon: 'fa-satellite-dish',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            description: 'Triggered when an external system sends a webhook POST',
            configFields: [
                { key: 'webhook_url_note', type: 'info', text: 'Webhook URL will be generated when the flow is activated.' },
                { key: 'payload_schema', type: 'textarea', label: 'Expected Payload (JSON)', placeholder: '{"order_id": "", "customer_phone": ""}' },
                { key: 'auth_method', type: 'select', label: 'Authentication', options: ['none', 'hmac'] },
                { key: 'hmac_secret', type: 'text', label: 'HMAC Secret', placeholder: 'Auto-generated on save', showWhen: { key: 'auth_method', values: ['hmac'] } }
            ]
        },
        trigger_sms_inbound: {
            label: 'SMS Inbound',
            icon: 'fa-comment-dots',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            description: 'Triggered when an SMS is received',
            configFields: [
                { key: 'sender_id', type: 'select', label: 'Listen on Number', options: [], dynamic: 'senderIds' },
                { key: 'match_type', type: 'select', label: 'Match Type', options: ['any', 'keyword', 'contains', 'regex'] },
                { key: 'keywords', type: 'text', label: 'Keywords (comma separated)', placeholder: 'HELP, INFO', showWhen: { key: 'match_type', values: ['keyword', 'contains'] } }
            ]
        },
        trigger_rcs_inbound: {
            label: 'RCS Inbound',
            icon: 'fa-hand-pointer',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            description: 'Triggered when an RCS reply or button tap is received',
            configFields: [
                { key: 'rcs_agent_id', type: 'select', label: 'RCS Agent', options: [], dynamic: 'rcsAgents' },
                { key: 'match_type', type: 'select', label: 'Match Type', options: ['any', 'postback', 'text'] },
                { key: 'postback_data', type: 'text', label: 'Postback Data', placeholder: 'track_delivery', showWhen: { key: 'match_type', values: ['postback'] } },
                { key: 'text_match', type: 'text', label: 'Text Match', placeholder: 'YES', showWhen: { key: 'match_type', values: ['text'] } }
            ]
        },
        trigger_campaign: {
            label: 'Campaign Event',
            icon: 'fa-bullhorn',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            description: 'Triggered by campaign activity',
            configFields: [
                { key: 'campaign_event', type: 'select', label: 'Event', options: ['campaign_completed', 'message_delivered', 'message_failed', 'link_clicked', 'reply_received'] },
                { key: 'campaign_id', type: 'text', label: 'Campaign ID (or "any")', placeholder: 'any' }
            ]
        },
        trigger_contact_event: {
            label: 'Contact Event',
            icon: 'fa-address-book',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            description: 'Triggered by contact book changes',
            configFields: [
                { key: 'event_type', type: 'select', label: 'Event Type', options: ['contact_created', 'contact_updated', 'added_to_list', 'removed_from_list', 'tag_added', 'tag_removed', 'opted_out', 'opted_in'] },
                { key: 'filter_list_id', type: 'select', label: 'Filter by List', options: [], dynamic: 'contactLists', showWhen: { key: 'event_type', values: ['added_to_list', 'removed_from_list'] } },
                { key: 'filter_tag_id', type: 'select', label: 'Filter by Tag', options: [], dynamic: 'tags', showWhen: { key: 'event_type', values: ['tag_added', 'tag_removed'] } }
            ]
        },
        trigger_schedule: {
            label: 'Schedule',
            icon: 'fa-clock',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            configFields: [
                { key: 'schedule_type', type: 'select', label: 'Type', options: ['once', 'daily', 'weekly', 'monthly'] },
                { key: 'time', type: 'text', label: 'Time', placeholder: '09:00' },
                { key: 'date', type: 'text', label: 'Start Date', placeholder: '2026-03-15' }
            ]
        },

        // === ACTIONS ===
        send_message: {
            label: 'Send Message',
            icon: 'fa-paper-plane',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            configFields: [],
            customProperties: true,
            dynamicOutputs: true
        },
        contact: {
            label: 'Contact',
            icon: 'fa-user-plus',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            description: 'Create, update or delete a contact',
            configFields: [
                { key: 'action', type: 'select', label: 'Action', options: ['create', 'update', 'delete'] },
                { key: 'phone_number', type: 'text', label: 'Phone Number', placeholder: '{{phone}}' },
                { key: 'first_name', type: 'text', label: 'First Name', placeholder: '{{first_name}}' },
                { key: 'last_name', type: 'text', label: 'Last Name', placeholder: '{{last_name}}' },
                { key: 'email', type: 'text', label: 'Email', placeholder: '{{email}}' }
            ]
        },
        tag_action: {
            label: 'Tag',
            icon: 'fa-tag',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            description: 'Add or remove a tag',
            configFields: [
                { key: 'action', type: 'select', label: 'Action', options: ['add', 'remove'] },
                { key: 'tag_name', type: 'text', label: 'Tag Name', placeholder: 'VIP' }
            ]
        },
        list_action: {
            label: 'List',
            icon: 'fa-list',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            description: 'Add or remove from a contact list',
            configFields: [
                { key: 'action', type: 'select', label: 'Action', options: ['add', 'remove'] },
                { key: 'list_id', type: 'select', label: 'Contact List', options: [], dynamic: 'contactLists' }
            ]
        },
        optout_action: {
            label: 'Opt-Out',
            icon: 'fa-ban',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            description: 'Manage opt-out status',
            configFields: [
                { key: 'action', type: 'select', label: 'Action', options: ['add', 'remove'] },
                { key: 'opt_out_list_id', type: 'select', label: 'Opt-Out List', options: [], dynamic: 'optOutLists' },
                { key: 'reason', type: 'text', label: 'Reason (optional)', placeholder: 'User requested' }
            ]
        },
        webhook: {
            label: 'Webhook',
            icon: 'fa-globe',
            category: 'action',
            outputs: ['success', 'error'],
            inputs: true,
            configFields: [],
            customProperties: true
        },
        action_group: {
            label: 'Quick Steps',
            icon: 'fa-layer-group',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            description: 'Execute multiple quick actions in sequence',
            configFields: [],
            customProperties: true
        },

        // === LOGIC ===
        wait: {
            label: 'Wait / Delay',
            icon: 'fa-hourglass-half',
            category: 'logic',
            outputs: ['default'],
            inputs: true,
            configFields: [
                { key: 'wait_type', type: 'select', label: 'Wait Type', options: ['duration', 'until_date', 'until_event'] },
                { key: 'duration_value', type: 'text', label: 'Duration', placeholder: '24' },
                { key: 'duration_unit', type: 'select', label: 'Unit', options: ['minutes', 'hours', 'days'] },
                { key: 'quiet_hours', type: 'checkbox', label: 'Respect quiet hours (20:00-09:00)' }
            ]
        },
        decision: {
            label: 'Decision',
            icon: 'fa-random',
            category: 'logic',
            outputs: ['yes', 'no'],
            inputs: true,
            configFields: [
                { key: 'condition_type', type: 'select', label: 'Condition', options: ['field_equals', 'field_contains', 'tag_exists', 'rcs_capable', 'message_length', 'time_check', 'reply_received'] },
                { key: 'field', type: 'text', label: 'Field / Tag', placeholder: 'customer_type' },
                { key: 'operator', type: 'select', label: 'Operator', options: ['equals', 'not_equals', 'contains', 'greater_than', 'less_than'] },
                { key: 'compare_value', type: 'text', label: 'Value', placeholder: 'premium' },
                { key: 'timeout', type: 'text', label: 'Timeout (hours, for reply_received)', placeholder: '48' }
            ]
        },
        decision_contact: {
            label: 'Contact Decision',
            icon: 'fa-address-card',
            category: 'logic',
            outputs: ['yes', 'no'],
            inputs: true,
            description: 'Branch based on contact book data',
            configFields: [],
            customProperties: true
        },
        decision_webhook: {
            label: 'Webhook Decision',
            icon: 'fa-code-branch',
            category: 'logic',
            outputs: ['yes', 'no'],
            inputs: true,
            description: 'Branch based on webhook/API response',
            configFields: [],
            customProperties: true
        },

        // === END ===
        inbox_handoff: {
            label: 'Inbox Handoff',
            icon: 'fa-headset',
            category: 'end',
            outputs: [],
            inputs: true,
            configFields: [
                { key: 'assign_to', type: 'select', label: 'Assign To', options: ['support_team', 'sales_team', 'unassigned'] },
                { key: 'priority', type: 'select', label: 'Priority', options: ['normal', 'high', 'urgent'] },
                { key: 'note', type: 'textarea', label: 'Internal Note', placeholder: 'Customer needs help with...' }
            ]
        },
        flow_handoff: {
            label: 'Flow Handoff',
            icon: 'fa-exchange-alt',
            category: 'end',
            outputs: [],
            inputs: true,
            description: 'Hand off to another flow',
            configFields: [
                { key: 'target_flow_id', type: 'select', label: 'Target Flow', options: [], dynamic: 'activeFlows' },
                { key: 'pass_context', type: 'checkbox', label: 'Pass flow variables to target flow' }
            ]
        },
        end: {
            label: 'End Flow',
            icon: 'fa-stop-circle',
            category: 'end',
            outputs: [],
            inputs: true,
            configFields: []
        }
    };

    // ========================================
    // Flow Templates
    // ========================================
    var TEMPLATES = {
        welcome: {
            nodes: [
                { node_uid: 'n1', type: 'trigger_api', label: 'New Customer Signup', position_x: 400, position_y: 80, config: {} },
                { node_uid: 'n2', type: 'send_message', label: 'Welcome Message', position_x: 400, position_y: 220, config: { channel: 'sms', sms_content: 'Welcome to {{company}}, {{first_name}}! We\'re glad to have you.' } },
                { node_uid: 'n3', type: 'wait', label: 'Wait 2 Days', position_x: 400, position_y: 380, config: { wait_type: 'duration', duration_value: '2', duration_unit: 'days' } },
                { node_uid: 'n4', type: 'send_message', label: 'Tips & Getting Started', position_x: 400, position_y: 540, config: { channel: 'sms', sms_content: 'Hi {{first_name}}, here are some tips to get started...' } },
                { node_uid: 'n5', type: 'tag_action', label: 'Tag: Onboarded', position_x: 400, position_y: 680, config: { action: 'add', tag_name: 'onboarded' } },
                { node_uid: 'n6', type: 'end', label: 'End', position_x: 400, position_y: 820, config: {} }
            ],
            connections: [
                { source_node_uid: 'n1', target_node_uid: 'n2', source_handle: 'default' },
                { source_node_uid: 'n2', target_node_uid: 'n3', source_handle: 'default' },
                { source_node_uid: 'n3', target_node_uid: 'n4', source_handle: 'default' },
                { source_node_uid: 'n4', target_node_uid: 'n5', source_handle: 'default' },
                { source_node_uid: 'n5', target_node_uid: 'n6', source_handle: 'default' }
            ]
        },
        reminder: {
            nodes: [
                { node_uid: 'n1', type: 'trigger_api', label: 'Appointment Created', position_x: 400, position_y: 80, config: {} },
                { node_uid: 'n2', type: 'wait', label: 'Wait Until 24h Before', position_x: 400, position_y: 220, config: { wait_type: 'duration', duration_value: '24', duration_unit: 'hours' } },
                { node_uid: 'n3', type: 'send_message', label: 'Reminder with Buttons', position_x: 400, position_y: 380, config: { channel: 'rich_rcs', sms_content: 'Reminder: Your appointment is tomorrow at {{time}}. Reply CONFIRM or RESCHEDULE.', rcs_payload: { type: 'standalone', card: { title: 'Appointment Reminder', description: 'Your appointment is tomorrow at {{time}}.', suggestions: [{ type: 'reply', text: 'Confirm', postbackData: 'confirm' }, { type: 'reply', text: 'Reschedule', postbackData: 'reschedule' }] } } } },
                { node_uid: 'n4', type: 'decision', label: 'Confirmed?', position_x: 400, position_y: 540, config: { condition_type: 'reply_received', timeout: '4' } },
                { node_uid: 'n5', type: 'tag_action', label: 'Tag: Confirmed', position_x: 250, position_y: 700, config: { action: 'add', tag_name: 'appointment_confirmed' } },
                { node_uid: 'n6', type: 'send_message', label: 'Fallback SMS Reminder', position_x: 550, position_y: 700, config: { channel: 'sms', sms_content: 'Reminder: You have an appointment tomorrow. Reply CONFIRM or call us.' } },
                { node_uid: 'n7', type: 'end', label: 'End', position_x: 400, position_y: 860, config: {} }
            ],
            connections: [
                { source_node_uid: 'n1', target_node_uid: 'n2', source_handle: 'default' },
                { source_node_uid: 'n2', target_node_uid: 'n3', source_handle: 'default' },
                { source_node_uid: 'n3', target_node_uid: 'n4', source_handle: 'default' },
                { source_node_uid: 'n4', target_node_uid: 'n5', source_handle: 'yes', label: 'Confirmed' },
                { source_node_uid: 'n4', target_node_uid: 'n6', source_handle: 'no', label: 'No Response' },
                { source_node_uid: 'n5', target_node_uid: 'n7', source_handle: 'default' },
                { source_node_uid: 'n6', target_node_uid: 'n7', source_handle: 'default' }
            ]
        },
        delivery: {
            nodes: [
                { node_uid: 'n1', type: 'trigger_api', label: 'Order Shipped', position_x: 400, position_y: 80, config: {} },
                { node_uid: 'n2', type: 'send_message', label: 'Shipping Notification', position_x: 400, position_y: 220, config: { channel: 'rich_rcs', sms_content: 'Your order #{{order_id}} has shipped! Track at {{tracking_url}}', rcs_payload: { type: 'standalone', card: { title: 'Order Shipped!', description: 'Your order #{{order_id}} is on its way.', suggestions: [{ type: 'reply', text: 'Track', postbackData: 'track' }, { type: 'reply', text: 'Support', postbackData: 'support' }] } } } },
                { node_uid: 'n3', type: 'decision', label: 'Button Clicked?', position_x: 400, position_y: 400, config: { condition_type: 'reply_received', timeout: '24' } },
                { node_uid: 'n4', type: 'webhook', label: 'Get Tracking Info', position_x: 250, position_y: 580, config: { url: 'https://api.courier.com/track', method: 'GET' } },
                { node_uid: 'n5', type: 'inbox_handoff', label: 'Support Handoff', position_x: 550, position_y: 580, config: { assign_to: 'support_team' } },
                { node_uid: 'n6', type: 'send_message', label: 'SMS Reminder', position_x: 400, position_y: 580, config: { channel: 'sms', sms_content: 'Your order #{{order_id}} is on its way. Track at {{tracking_url}}' } },
                { node_uid: 'n7', type: 'end', label: 'End', position_x: 350, position_y: 740, config: {} }
            ],
            connections: [
                { source_node_uid: 'n1', target_node_uid: 'n2', source_handle: 'default' },
                { source_node_uid: 'n2', target_node_uid: 'n3', source_handle: 'default' },
                { source_node_uid: 'n3', target_node_uid: 'n4', source_handle: 'yes', label: 'Track' },
                { source_node_uid: 'n3', target_node_uid: 'n5', source_handle: 'no', label: 'Support' },
                { source_node_uid: 'n4', target_node_uid: 'n7', source_handle: 'default' },
                { source_node_uid: 'n6', target_node_uid: 'n7', source_handle: 'default' }
            ]
        }
    };

    // ========================================
    // Utility functions
    // ========================================
    function uid() {
        return 'node_' + Math.random().toString(36).substr(2, 9);
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ========================================
    // FlowBuilder class
    // ========================================
    function FlowBuilder(canvasId, options) {
        this.options = options || {};
        this.nodes = {};
        this.connections = [];
        this.selectedNodeId = null;
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.isDraggingNode = false;
        this.isDraggingCanvas = false;
        this.isConnecting = false;
        this.connectFrom = null;
        this.undoStack = [];
        this.redoStack = [];
        this.isDirty = false;

        this.canvas = document.getElementById(canvasId);
        this.wrapper = document.getElementById('flow-canvas-wrapper');
        this.svg = document.getElementById('flow-connections-svg');
        this.nodesLayer = document.getElementById('flow-nodes-layer');
        this.emptyState = document.getElementById('canvas-empty-state');

        this._init();
    }

    FlowBuilder.prototype._init = function() {
        var self = this;

        // Load initial data or template
        if (this.options.initialData && this.options.initialData.nodes.length > 0) {
            this._loadData(this.options.initialData);
        } else if (this.options.template && TEMPLATES[this.options.template]) {
            this._loadData(TEMPLATES[this.options.template]);
        }

        // Set up event listeners
        this._setupDragDrop();
        this._setupCanvasPan();
        this._setupZoom();
        this._setupToolbar();
        this._setupPaletteSearch();
        this._setupKeyboard();

        // Apply initial canvas transform
        if (this.options.initialData && this.options.initialData.canvas_meta) {
            var meta = this.options.initialData.canvas_meta;
            this.zoom = meta.zoom || 1;
            this.panX = meta.panX || 0;
            this.panY = meta.panY || 0;
        }
        this._applyTransform();
        this._updateEmptyState();
        this._renderAllConnections();
    };

    // ========================================
    // Data loading
    // ========================================
    FlowBuilder.prototype._loadData = function(data) {
        var self = this;
        // Clear existing
        this.nodesLayer.innerHTML = '';
        this.nodes = {};
        this.connections = [];

        if (data.nodes) {
            data.nodes.forEach(function(n) {
                self.addNode(n.type, n.position_x, n.position_y, n.config || {}, n.label, n.node_uid);
            });
        }
        if (data.connections) {
            data.connections.forEach(function(c) {
                self.connections.push({
                    source: c.source_node_uid,
                    target: c.target_node_uid,
                    handle: c.source_handle || 'default',
                    label: c.label || null
                });
            });
        }
    };

    // ========================================
    // Node management
    // ========================================
    FlowBuilder.prototype.addNode = function(type, x, y, config, label, nodeUid) {
        var typeDef = NODE_TYPES[type];
        if (!typeDef) return null;

        var id = nodeUid || uid();
        var node = {
            id: id,
            type: type,
            label: label || typeDef.label,
            config: config || {},
            x: x,
            y: y
        };

        this.nodes[id] = node;
        this._renderNode(node);
        this._updateEmptyState();
        return node;
    };

    // ========================================
    // Dynamic Interaction Outputs for send_message
    // Computes output ports based on message config
    // ========================================
    FlowBuilder.prototype._getInteractionOutputs = function(node) {
        var c = node.config || {};
        if (!c.interaction_enabled) return null; // not in interaction mode

        var outputs = [];
        var channel = c.channel || 'sms';
        var isRichRcs = (channel === 'rcs_rich' || channel === 'rich_rcs');
        var isAnyRcs = isRichRcs || channel === 'rcs_basic' || channel === 'basic_rcs';

        // RCS button interactions (from rcs_payload)
        if (isRichRcs && c.rcs_payload) {
            var rcsCards = [];
            if (c.rcs_payload.cards && c.rcs_payload.cards.length > 0) {
                rcsCards = c.rcs_payload.cards;
            } else if (c.rcs_payload.card) {
                rcsCards = [c.rcs_payload.card];
            }
            var btnIndex = 0;
            rcsCards.forEach(function(card) {
                var buttons = card.buttons || card.suggestions || [];
                buttons.forEach(function(btn) {
                    var btnLabel = btn.label || btn.text || 'Button ' + (btnIndex + 1);
                    outputs.push({
                        handle: 'rcs_btn_' + btnIndex,
                        label: btnLabel,
                        group: 'rcs',
                        type: 'rcs_button'
                    });
                    btnIndex++;
                });
            });
        }

        // SMS interactions (also applies as fallback for RCS)
        // Trackable link click
        if (c.trackable_link) {
            outputs.push({
                handle: 'sms_link',
                label: 'Link Clicked',
                group: 'sms',
                type: 'link_click'
            });
        }

        // Opt-out URL click
        var oc = c.optout_config;
        if (oc && (oc.opt_out_url_enabled || oc.url_optout)) {
            outputs.push({
                handle: 'sms_optout_url',
                label: 'Opt-out Link',
                group: 'sms',
                type: 'optout_url'
            });
        }

        // Opt-out keyword reply
        if (oc && (oc.opt_out_keyword || oc.reply_optout)) {
            var kwLabel = oc.opt_out_keyword || 'STOP';
            outputs.push({
                handle: 'sms_optout_reply',
                label: 'Opt-out: ' + kwLabel,
                group: 'sms',
                type: 'optout_reply'
            });
        }

        // User-defined keyword branches
        var keywords = c.interaction_keywords || [];
        keywords.forEach(function(kw, idx) {
            outputs.push({
                handle: 'sms_kw_' + idx,
                label: 'Keyword: ' + kw.keyword,
                group: 'sms',
                type: 'keyword'
            });
        });

        // Catch-all reply
        if (c.interaction_catch_all) {
            outputs.push({
                handle: 'sms_catch_all',
                label: 'Any Other Reply',
                group: 'sms',
                type: 'catch_all'
            });
        }

        // Timeout / no response (always present when interaction is enabled)
        var timeout = c.interaction_timeout || { value: 24, unit: 'hours' };
        outputs.push({
            handle: 'timeout',
            label: 'No Response (' + timeout.value + timeout.unit.charAt(0) + ')',
            group: 'timeout',
            type: 'timeout'
        });

        return outputs;
    };

    // Get effective outputs for any node (static or dynamic)
    FlowBuilder.prototype._getNodeOutputs = function(node) {
        var typeDef = NODE_TYPES[node.type];
        if (typeDef.dynamicOutputs) {
            var dynamic = this._getInteractionOutputs(node);
            if (dynamic) return dynamic;
        }
        // Fallback to static outputs
        return typeDef.outputs.map(function(h) {
            return { handle: h, label: h === 'default' ? '' : h, group: 'default', type: 'static' };
        });
    };

    FlowBuilder.prototype._renderNode = function(node) {
        var typeDef = NODE_TYPES[node.type];
        var self = this;
        var el = document.createElement('div');
        el.className = 'flow-node';
        el.id = 'node-' + node.id;
        el.setAttribute('data-node-id', node.id);
        el.style.left = node.x + 'px';
        el.style.top = node.y + 'px';

        var configPreview = this._getConfigPreview(node);

        var bodyHtml = '';
        if (configPreview) {
            bodyHtml = '<div class="config-preview">' + escapeHtml(configPreview) + '</div>';
        } else {
            bodyHtml = '<span style="color:#ccc; font-style:italic;">Click to configure</span>';
        }

        // Action buttons for send_message nodes (View / Interaction)
        var actionBtnsHtml = '';
        if (node.type === 'send_message' && (node.config.channel || node.config.sms_content || node.config.rcs_payload)) {
            var interactionActive = node.config.interaction_enabled;
            actionBtnsHtml = '<div class="node-action-btns">' +
                '<button class="node-action-btn node-action-view" data-action="view" title="Preview message"><i class="fas fa-eye"></i> View</button>' +
                '<button class="node-action-btn node-action-interact' + (interactionActive ? ' active' : '') + '" data-action="interaction" title="Configure interactions"><i class="fas fa-code-branch"></i> Interaction</button>' +
            '</div>';
        }

        var descText = node.config && node.config.description ? node.config.description : '';
        var descHtml = descText
            ? '<div class="node-description">' + escapeHtml(descText) + '</div>'
            : '<div class="node-description node-description-empty">Add a description...</div>';

        el.innerHTML =
            '<div class="flow-node-header">' +
                '<div class="node-icon ' + typeDef.category + '"><i class="fas ' + typeDef.icon + '"></i></div>' +
                '<div style="flex:1; min-width:0;">' +
                    '<div class="node-type-label">' + escapeHtml(typeDef.label) + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="flow-node-body">' +
                '<div class="node-label-editable" title="Double-click to rename">' + escapeHtml(node.label) + '</div>' +
                descHtml +
            '</div>' +
            actionBtnsHtml;

        // Add input port
        if (typeDef.inputs) {
            var inputPort = document.createElement('div');
            inputPort.className = 'node-port port-input';
            inputPort.setAttribute('data-port', 'input');
            inputPort.setAttribute('data-node-id', node.id);
            el.appendChild(inputPort);
        }

        // Add output ports
        this._renderOutputPorts(el, node);

        // Event listeners
        this._setupNodeDrag(el, node);
        this._setupNodeClick(el, node);
        this._setupPortEvents(el, node);

        // Action button listeners for send_message
        if (node.type === 'send_message') {
            el.querySelectorAll('.node-action-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var action = btn.getAttribute('data-action');
                    if (action === 'view') {
                        self._openPreviewModal(node.id);
                    } else if (action === 'interaction') {
                        self._toggleInteraction(node.id);
                    }
                });
                btn.addEventListener('mousedown', function(e) { e.stopPropagation(); });
            });
        }

        var labelEl = el.querySelector('.node-label-editable');
        if (labelEl) {
            labelEl.addEventListener('dblclick', function(e) {
                e.stopPropagation();
                self._startInlineEdit(node.id, labelEl, 'label');
            });
            labelEl.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        }

        var descEl = el.querySelector('.node-description');
        if (descEl) {
            descEl.addEventListener('click', function(e) {
                e.stopPropagation();
                self._startInlineEdit(node.id, descEl, 'description');
            });
            descEl.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        }

        this.nodesLayer.appendChild(el);
    };

    FlowBuilder.prototype._getPortShades = function(groupKey, count) {
        var baseColors = {
            rcs:     { h: 286, s: 65, lBase: 35, lStep: 12 },
            sms:     { h: 210, s: 70, lBase: 35, lStep: 14 },
            timeout: { h: 30,  s: 85, lBase: 40, lStep: 0  }
        };
        var cfg = baseColors[groupKey] || { h: 0, s: 0, lBase: 50, lStep: 8 };
        var shades = [];
        for (var i = 0; i < count; i++) {
            var l = Math.min(cfg.lBase + (i * cfg.lStep), 72);
            shades.push('hsl(' + cfg.h + ', ' + cfg.s + '%, ' + l + '%)');
        }
        return shades;
    };

    // Renders output ports on a node element (handles static and dynamic outputs)
    FlowBuilder.prototype._renderOutputPorts = function(el, node) {
        var self = this;
        var typeDef = NODE_TYPES[node.type];
        var outputs = this._getNodeOutputs(node);

        // Check if this is dynamic interaction mode
        var isDynamic = typeDef.dynamicOutputs && node.config && node.config.interaction_enabled;

        if (isDynamic && outputs.length > 0) {
            el.classList.add('interaction-active');

            var portsContainer = document.createElement('div');
            portsContainer.className = 'interaction-ports-container';

            var groups = {};
            var groupOrder = [];
            outputs.forEach(function(out, idx) {
                if (!groups[out.group]) {
                    groups[out.group] = [];
                    groupOrder.push(out.group);
                }
                groups[out.group].push({ out: out, idx: idx });
            });

            var groupLabels = { rcs: 'RCS', sms: 'SMS', timeout: 'Response' };

            groupOrder.forEach(function(groupKey) {
                var items = groups[groupKey];
                var shades = self._getPortShades(groupKey, items.length);

                var groupDiv = document.createElement('div');
                groupDiv.className = 'interaction-group group-' + groupKey;

                var groupText = groupLabels[groupKey] !== undefined ? groupLabels[groupKey] : groupKey;
                var groupLabel = document.createElement('div');
                groupLabel.className = 'interaction-group-label group-' + groupKey;
                groupLabel.textContent = groupText;
                groupDiv.appendChild(groupLabel);

                items.forEach(function(item, i) {
                    var color = shades[i];
                    var row = document.createElement('div');
                    row.className = 'interaction-port-row';

                    var label = document.createElement('span');
                    label.className = 'interaction-port-label';
                    label.textContent = item.out.label;
                    label.style.color = color;
                    label.style.fontWeight = '600';
                    row.appendChild(label);

                    groupDiv.appendChild(row);
                });

                var groupPortsRow = document.createElement('div');
                groupPortsRow.className = 'interaction-group-ports';
                items.forEach(function(item, i) {
                    var color = shades[i];
                    var port = document.createElement('div');
                    port.className = 'node-port port-output-dynamic';
                    port.style.borderColor = color;
                    port.style.setProperty('--port-color', color);
                    port.setAttribute('data-port', 'output');
                    port.setAttribute('data-handle', item.out.handle);
                    port.setAttribute('data-node-id', node.id);
                    port.setAttribute('data-port-index', item.idx);
                    groupPortsRow.appendChild(port);
                });
                groupDiv.appendChild(groupPortsRow);

                portsContainer.appendChild(groupDiv);
            });

            el.appendChild(portsContainer);
        } else if (!isDynamic) {
            // Static outputs (default single port, or yes/no for decision)
            if (typeDef.outputs.length === 1) {
                var outputPort = document.createElement('div');
                outputPort.className = 'node-port port-output';
                outputPort.setAttribute('data-port', 'output');
                outputPort.setAttribute('data-handle', 'default');
                outputPort.setAttribute('data-node-id', node.id);
                el.appendChild(outputPort);
            } else if (typeDef.outputs.length === 2) {
                var handles = typeDef.outputs; // ['yes','no'] or ['success','error']
                var portClasses = {
                    yes: 'port-output-yes', no: 'port-output-no',
                    success: 'port-output-yes port-output-success', error: 'port-output-no port-output-error'
                };
                var labelClasses = {
                    yes: 'branch-label yes', no: 'branch-label no',
                    success: 'branch-label success', error: 'branch-label error'
                };
                handles.forEach(function(handle) {
                    var port = document.createElement('div');
                    port.className = 'node-port ' + (portClasses[handle] || 'port-output');
                    port.setAttribute('data-port', 'output');
                    port.setAttribute('data-handle', handle);
                    port.setAttribute('data-node-id', node.id);
                    el.appendChild(port);

                    var lbl = document.createElement('div');
                    lbl.className = labelClasses[handle] || 'branch-label';
                    lbl.textContent = handle.charAt(0).toUpperCase() + handle.slice(1);
                    el.appendChild(lbl);
                });
            }
        }
    };

    FlowBuilder.prototype._getConfigPreview = function(node) {
        var c = node.config;
        if (!c) return '';
        switch (node.type) {
            case 'send_message':
                var rawCh = c.channel || 'sms';
                var chLabels = { 'sms': 'SMS', 'rcs_basic': 'Basic RCS', 'rcs_rich': 'Rich RCS', 'basic_rcs': 'Basic RCS', 'rich_rcs': 'Rich RCS' };
                var chDisplay = chLabels[rawCh] || rawCh.toUpperCase();
                var isRichRcs = (rawCh === 'rcs_rich' || rawCh === 'rich_rcs');
                if (isRichRcs && c.rcs_payload) {
                    var card = c.rcs_payload.card || (c.rcs_payload.cards && c.rcs_payload.cards.length > 0 ? c.rcs_payload.cards[0] : null);
                    var label = 'RCS';
                    if (card && (card.title || card.description)) label += ': ' + (card.title || card.description);
                    if (c.rcs_payload.type === 'carousel') label = 'RCS Carousel';
                    return label.length > 60 ? label.substr(0, 57) + '...' : label;
                }
                var txt = c.sms_content || '';
                if (!txt) return chDisplay + ': (no content)';
                var preview = chDisplay + ': ' + txt;
                return preview.length > 60 ? preview.substr(0, 57) + '...' : preview;
            case 'trigger_webhook':
                return c.auth_method ? 'Auth: ' + c.auth_method : 'Webhook endpoint';
            case 'trigger_sms_inbound':
                if (c.match_type === 'keyword' || c.match_type === 'contains') return c.keywords ? 'Match: ' + c.keywords : '';
                return c.match_type ? 'Match: ' + c.match_type : '';
            case 'trigger_rcs_inbound':
                if (c.match_type === 'postback') return c.postback_data ? 'Postback: ' + c.postback_data : '';
                return c.match_type ? 'Match: ' + c.match_type : '';
            case 'trigger_campaign':
                return c.campaign_event ? c.campaign_event.replace(/_/g, ' ') : '';
            case 'trigger_contact_event':
                return c.event_type ? c.event_type.replace(/_/g, ' ') : '';
            case 'trigger_schedule':
                return c.schedule_type ? c.schedule_type + ' at ' + (c.time || '') : '';
            case 'contact':
                return c.action ? c.action + ': ' + (c.phone_number || '') : '';
            case 'tag_action':
                return c.action ? c.action + ' tag: ' + (c.tag_name || '') : '';
            case 'list_action':
                return c.action ? c.action + ' list' : '';
            case 'optout_action':
                return c.action ? c.action + ' opt-out' : '';
            case 'webhook':
                return c.url ? (c.method || 'POST') + ' ' + c.url : '';
            case 'action_group':
                var steps = c.steps || [];
                if (steps.length === 0) return '';
                return steps.map(function(s, i) {
                    var stepType = ACTION_GROUP_STEP_TYPES[s.type];
                    var label = stepType ? stepType.label : s.type;
                    return (i + 1) + '. ' + label;
                }).join('\n');
            case 'wait':
                return c.duration_value ? 'Wait ' + c.duration_value + ' ' + (c.duration_unit || 'hours') : '';
            case 'decision':
                return c.condition_type ? 'If ' + c.condition_type + (c.field ? ' (' + c.field + ')' : '') : '';
            case 'decision_contact':
                return c.condition ? c.condition.replace(/_/g, ' ') : '';
            case 'decision_webhook':
                return c.condition_type ? c.condition_type.replace(/_/g, ' ') : '';
            case 'inbox_handoff':
                return c.assign_to ? 'Assign: ' + c.assign_to : '';
            case 'flow_handoff':
                return c.target_flow_id ? 'Hand off to flow' : '';
            default:
                return '';
        }
    };

    FlowBuilder.prototype._refreshNode = function(nodeId) {
        var el = document.getElementById('node-' + nodeId);
        if (!el) return;
        var node = this.nodes[nodeId];

        // For send_message with dynamic outputs, do a full re-render
        var typeDef = NODE_TYPES[node.type];
        if (typeDef.dynamicOutputs) {
            this._fullRebuildNode(nodeId);
            return;
        }

        var labelEditable = el.querySelector('.node-label-editable');
        if (labelEditable) labelEditable.textContent = node.label;
        var descEl = el.querySelector('.node-description');
        if (descEl) {
            var descText = node.config && node.config.description ? node.config.description : '';
            descEl.textContent = descText || 'Add a description...';
            descEl.classList.toggle('node-description-empty', !descText);
        }
    };

    // Full re-render of a node (remove + re-create), preserving connections
    FlowBuilder.prototype._fullRebuildNode = function(nodeId) {
        var oldEl = document.getElementById('node-' + nodeId);
        if (oldEl) oldEl.remove();

        var node = this.nodes[nodeId];
        if (!node) return;

        // Clean up connections to handles that no longer exist
        var currentOutputs = this._getNodeOutputs(node);
        var validHandles = {};
        currentOutputs.forEach(function(o) { validHandles[o.handle] = true; });

        this.connections = this.connections.filter(function(c) {
            if (c.source === nodeId && !validHandles[c.handle]) {
                return false; // Remove connection from deleted handle
            }
            return true;
        });

        // Re-render the node
        this._renderNode(node);
        this._renderAllConnections();
    };

    FlowBuilder.prototype.deleteNode = function(nodeId) {
        this._saveUndo();
        var el = document.getElementById('node-' + nodeId);
        if (el) el.remove();

        // Remove connections
        this.connections = this.connections.filter(function(c) {
            return c.source !== nodeId && c.target !== nodeId;
        });

        delete this.nodes[nodeId];

        if (this.selectedNodeId === nodeId) {
            this.selectedNodeId = null;
            this._hideProperties();
        }

        this._renderAllConnections();
        this._updateEmptyState();
        this.isDirty = true;
    };

    // ========================================
    // Node dragging
    // ========================================
    FlowBuilder.prototype._setupNodeDrag = function(el, node) {
        var self = this;
        var startX, startY, origX, origY;

        el.addEventListener('mousedown', function(e) {
            if (e.target.classList.contains('node-port')) return;
            if (e.button !== 0) return;
            e.stopPropagation();

            self._saveUndo();
            self.isDraggingNode = true;
            el.classList.add('dragging');

            startX = e.clientX;
            startY = e.clientY;
            origX = node.x;
            origY = node.y;

            function onMove(e2) {
                var dx = (e2.clientX - startX) / self.zoom;
                var dy = (e2.clientY - startY) / self.zoom;
                node.x = Math.round(origX + dx);
                node.y = Math.round(origY + dy);
                el.style.left = node.x + 'px';
                el.style.top = node.y + 'px';
                self._renderAllConnections();
            }

            function onUp() {
                self.isDraggingNode = false;
                el.classList.remove('dragging');
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                self.isDirty = true;
            }

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    };

    // ========================================
    // Node selection & properties
    // ========================================
    FlowBuilder.prototype._setupNodeClick = function(el, node) {
        var self = this;
        el.addEventListener('click', function(e) {
            if (e.target.classList.contains('node-port')) return;
            e.stopPropagation();
            self._selectNode(node.id);
        });
    };

    FlowBuilder.prototype._selectNode = function(nodeId) {
        // Deselect previous
        if (this.selectedNodeId) {
            var prev = document.getElementById('node-' + this.selectedNodeId);
            if (prev) prev.classList.remove('selected');
        }

        this.selectedNodeId = nodeId;
        var el = document.getElementById('node-' + nodeId);
        if (el) el.classList.add('selected');

        this._showProperties(nodeId);
    };

    FlowBuilder.prototype._startInlineEdit = function(nodeId, targetEl, field) {
        var self = this;
        var node = this.nodes[nodeId];
        if (!node) return;
        if (!node.config) node.config = {};

        var isDesc = (field === 'description');
        var currentVal = isDesc ? (node.config.description || '') : node.label;

        var input = document.createElement(isDesc ? 'textarea' : 'input');
        if (!isDesc) input.type = 'text';
        input.value = currentVal;
        input.className = isDesc ? 'node-description-input' : 'node-label-input';
        input.maxLength = isDesc ? 200 : 60;
        if (isDesc) { input.rows = 2; input.placeholder = 'Describe this step...'; }

        targetEl.classList.add('d-none');
        targetEl.parentNode.insertBefore(input, targetEl);
        input.focus();
        if (!isDesc) input.select();

        var commit = function() {
            var val = input.value.trim();
            if (isDesc) {
                if (val !== (node.config.description || '')) {
                    self._saveUndo();
                    node.config.description = val;
                    self.isDirty = true;
                    var propDesc = document.getElementById('prop-description');
                    if (propDesc) propDesc.value = val;
                }
                targetEl.textContent = val || 'Add a description...';
                targetEl.classList.toggle('node-description-empty', !val);
            } else {
                if (val && val !== node.label) {
                    self._saveUndo();
                    node.label = val;
                    self.isDirty = true;
                    var propInput = document.getElementById('prop-label');
                    if (propInput) propInput.value = val;
                }
                targetEl.textContent = node.label;
            }
            targetEl.classList.remove('d-none');
            if (input.parentNode) input.parentNode.removeChild(input);
        };

        input.addEventListener('blur', commit);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !isDesc) { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') { input.value = currentVal; input.blur(); }
        });
        input.addEventListener('mousedown', function(e) { e.stopPropagation(); });
    };

    FlowBuilder.prototype._deselectAll = function() {
        if (this.selectedNodeId) {
            var prev = document.getElementById('node-' + this.selectedNodeId);
            if (prev) prev.classList.remove('selected');
        }
        this.selectedNodeId = null;
        this._hideProperties();
    };

    FlowBuilder.prototype._showProperties = function(nodeId) {
        var node = this.nodes[nodeId];
        if (!node) return;

        var typeDef = NODE_TYPES[node.type];
        var panel = document.getElementById('flow-properties');
        var body = document.getElementById('properties-body');
        var title = document.getElementById('properties-title');

        title.textContent = typeDef.label + ' Settings';
        panel.classList.remove('d-none');
        panel.classList.add('d-flex');

        var html = '';
        html += '<div class="mb-3">';
        html += '<label class="form-label">Node Label</label>';
        html += '<input type="text" class="form-control" id="prop-label" value="' + escapeHtml(node.label) + '">';
        html += '</div>';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Description</label>';
        html += '<textarea class="form-control" id="prop-description" rows="2" maxlength="200" placeholder="Describe what this step does...">' + escapeHtml(node.config.description || '') + '</textarea>';
        html += '</div>';

        if (typeDef.customProperties) {
            if (node.type === 'send_message') {
                html += this._renderSendMessageProperties(node);
                body.innerHTML = html;
                this._bindSendMessageEvents(node, nodeId);
            } else if (node.type === 'action_group') {
                html += this._renderActionGroupProperties(node);
                body.innerHTML = html;
                this._bindActionGroupEvents(node, nodeId);
            } else if (node.type === 'webhook') {
                html += this._renderWebhookProperties(node);
                body.innerHTML = html;
                this._bindWebhookEvents(node, nodeId);
            } else if (node.type === 'decision_contact') {
                html += this._renderDecisionContactProperties(node);
                body.innerHTML = html;
                this._bindDecisionContactEvents(node, nodeId);
            } else if (node.type === 'decision_webhook') {
                html += this._renderDecisionWebhookProperties(node);
                body.innerHTML = html;
                this._bindDecisionWebhookEvents(node, nodeId);
            }
        } else {
            // Generic config fields with dynamic selects and showWhen support
            var self2 = this;
            typeDef.configFields.forEach(function(field) {
                var showWhenStyle = '';
                if (field.showWhen) {
                    var curVal = node.config[field.showWhen.key] || '';
                    if (field.showWhen.values.indexOf(curVal) === -1) showWhenStyle = ' style="display:none;"';
                }
                html += '<div class="mb-3 config-field-wrap" data-field-key="' + field.key + '"' + showWhenStyle + '>';
                if (field.type === 'info') {
                    html += '<div class="alert alert-light p-2" style="font-size:0.78rem; border:1px solid #e0e0e0;">' + escapeHtml(field.text) + '</div>';
                } else if (field.type === 'text') {
                    html += '<label class="form-label">' + escapeHtml(field.label) + '</label>';
                    html += '<input type="text" class="form-control" data-config="' + field.key + '" value="' + escapeHtml(node.config[field.key] || '') + '" placeholder="' + escapeHtml(field.placeholder || '') + '">';
                } else if (field.type === 'textarea') {
                    html += '<label class="form-label">' + escapeHtml(field.label) + '</label>';
                    html += '<textarea class="form-control" data-config="' + field.key + '" rows="3" placeholder="' + escapeHtml(field.placeholder || '') + '">' + escapeHtml(node.config[field.key] || '') + '</textarea>';
                } else if (field.type === 'select') {
                    html += '<label class="form-label">' + escapeHtml(field.label) + '</label>';
                    html += '<select class="form-select" data-config="' + field.key + '">';
                    // Dynamic options from __flowBuilderData
                    var options = field.options;
                    if (field.dynamic && window.__flowBuilderData && window.__flowBuilderData[field.dynamic]) {
                        var dynamicData = window.__flowBuilderData[field.dynamic];
                        html += '<option value="">-- Select --</option>';
                        dynamicData.forEach(function(item) {
                            var val = item.id || item.name;
                            var label = item.name || item.id;
                            var selected = (node.config[field.key] == val) ? ' selected' : '';
                            html += '<option value="' + escapeHtml(String(val)) + '"' + selected + '>' + escapeHtml(label) + '</option>';
                        });
                    } else {
                        options.forEach(function(opt) {
                            var selected = (node.config[field.key] === opt) ? ' selected' : '';
                            html += '<option value="' + escapeHtml(opt) + '"' + selected + '>' + escapeHtml(opt.replace(/_/g, ' ')) + '</option>';
                        });
                    }
                    html += '</select>';
                } else if (field.type === 'checkbox') {
                    html += '<div class="form-check">';
                    var checked = node.config[field.key] ? ' checked' : '';
                    html += '<input type="checkbox" class="form-check-input" data-config="' + field.key + '"' + checked + '>';
                    html += '<label class="form-check-label" style="font-size:0.82rem;">' + escapeHtml(field.label) + '</label>';
                    html += '</div>';
                }
                html += '</div>';
            });

            body.innerHTML = html;

            // showWhen toggle logic
            typeDef.configFields.forEach(function(field) {
                if (!field.showWhen) return;
                var triggerInput = body.querySelector('[data-config="' + field.showWhen.key + '"]');
                if (triggerInput) {
                    triggerInput.addEventListener('change', function() {
                        var wrap = body.querySelector('.config-field-wrap[data-field-key="' + field.key + '"]');
                        if (wrap) {
                            if (field.showWhen.values.indexOf(triggerInput.value) >= 0) {
                                wrap.classList.remove('d-none');
                            } else {
                                wrap.classList.add('d-none');
                            }
                        }
                    });
                }
            });
        }

        // Bind change events
        var self = this;
        var labelInput = document.getElementById('prop-label');
        if (labelInput) {
            labelInput.addEventListener('change', function() {
                node.label = this.value;
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }

        var descInput = document.getElementById('prop-description');
        if (descInput) {
            descInput.addEventListener('change', function() {
                node.config.description = this.value.trim();
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }

        body.querySelectorAll('[data-config]').forEach(function(input) {
            var key = input.getAttribute('data-config');
            var eventType = (input.type === 'checkbox') ? 'change' : 'change';
            input.addEventListener(eventType, function() {
                if (input.type === 'checkbox') {
                    node.config[key] = input.checked;
                } else {
                    node.config[key] = input.value;
                }
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        });

        // Delete button
        var deleteBtn = document.getElementById('btn-delete-node');
        deleteBtn.onclick = function() {
            if (confirm('Delete this node?')) {
                self.deleteNode(nodeId);
            }
        };

        // Close button
        document.getElementById('btn-close-properties').onclick = function() {
            self._deselectAll();
        };
    };

    FlowBuilder.prototype._hideProperties = function() {
        var propertiesPanel = document.getElementById('flow-properties');
        propertiesPanel.classList.add('d-none');
        propertiesPanel.classList.remove('d-flex');
    };

    // ========================================
    // Connection ports & drawing
    // ========================================
    FlowBuilder.prototype._setupPortEvents = function(el, node) {
        var self = this;
        var ports = el.querySelectorAll('.node-port');

        ports.forEach(function(port) {
            port.addEventListener('mousedown', function(e) {
                e.stopPropagation();
                e.preventDefault();

                var portType = port.getAttribute('data-port');
                if (portType !== 'output') return;

                self.isConnecting = true;
                self.connectFrom = {
                    nodeId: node.id,
                    handle: port.getAttribute('data-handle') || 'default'
                };

                // Create temp line
                var tempLine = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                tempLine.setAttribute('class', 'connection-line-temp');
                tempLine.id = 'temp-connection';
                self.svg.appendChild(tempLine);

                function onMove(e2) {
                    var rect = self.wrapper.getBoundingClientRect();
                    var mx = (e2.clientX - rect.left - self.panX) / self.zoom;
                    var my = (e2.clientY - rect.top - self.panY) / self.zoom;

                    var portRect = port.getBoundingClientRect();
                    var canvasRect = self.canvas.getBoundingClientRect();
                    var sx = (portRect.left + portRect.width / 2 - canvasRect.left) / self.zoom;
                    var sy = (portRect.top + portRect.height / 2 - canvasRect.top) / self.zoom;

                    var path = self._bezierPath(sx, sy, mx, my);
                    tempLine.setAttribute('d', path);
                }

                function onUp(e2) {
                    self.isConnecting = false;
                    var tempEl = document.getElementById('temp-connection');
                    if (tempEl) tempEl.remove();

                    // Check if dropped on an input port
                    var target = document.elementFromPoint(e2.clientX, e2.clientY);
                    if (target && target.classList.contains('node-port') && target.getAttribute('data-port') === 'input') {
                        var targetNodeId = target.getAttribute('data-node-id');
                        if (targetNodeId !== self.connectFrom.nodeId) {
                            // Check for duplicate
                            var exists = self.connections.some(function(c) {
                                return c.source === self.connectFrom.nodeId &&
                                       c.target === targetNodeId &&
                                       c.handle === self.connectFrom.handle;
                            });
                            if (!exists) {
                                self._saveUndo();
                                self.connections.push({
                                    source: self.connectFrom.nodeId,
                                    target: targetNodeId,
                                    handle: self.connectFrom.handle,
                                    label: null
                                });
                                self._renderAllConnections();
                                self.isDirty = true;
                            }
                        }
                    }

                    self.connectFrom = null;
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                }

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });
        });
    };

    FlowBuilder.prototype._getPortPosition = function(nodeId, portType, handle) {
        var node = this.nodes[nodeId];
        if (!node) return { x: 0, y: 0 };

        var el = document.getElementById('node-' + nodeId);
        if (!el) return { x: node.x, y: node.y };

        var w = el.offsetWidth;
        var h = el.offsetHeight;

        if (portType === 'input') {
            return { x: node.x + w / 2, y: node.y };
        }

        // Dynamic interaction ports - find actual port element position
        var typeDef = NODE_TYPES[node.type];
        if (typeDef.dynamicOutputs && node.config && node.config.interaction_enabled) {
            var portEl = el.querySelector('.node-port[data-handle="' + handle + '"]');
            if (portEl) {
                var portRect = portEl.getBoundingClientRect();
                var elRect = el.getBoundingClientRect();
                var zoom = self.zoom || 1;
                var portX = node.x + (portRect.left - elRect.left + portRect.width / 2) / zoom;
                var portY = node.y + (portRect.top - elRect.top + portRect.height) / zoom;
                return { x: portX, y: portY };
            }
        }

        // Static output ports (yes/no or success/error)
        if (typeDef.outputs.length === 2) {
            if (handle === typeDef.outputs[0]) return { x: node.x + w * 0.3, y: node.y + h };
            if (handle === typeDef.outputs[1]) return { x: node.x + w * 0.7, y: node.y + h };
        }
        return { x: node.x + w / 2, y: node.y + h };
    };

    FlowBuilder.prototype._bezierPath = function(sx, sy, tx, ty) {
        var dy = ty - sy;
        var cy = Math.max(Math.abs(dy) * 0.5, 50);
        return 'M ' + sx + ' ' + sy +
               ' C ' + sx + ' ' + (sy + cy) +
               ', ' + tx + ' ' + (ty - cy) +
               ', ' + tx + ' ' + ty;
    };

    FlowBuilder.prototype._renderAllConnections = function() {
        var self = this;
        // Clear existing
        this.svg.innerHTML = '';

        this.connections.forEach(function(conn, idx) {
            var sp = self._getPortPosition(conn.source, 'output', conn.handle);
            var tp = self._getPortPosition(conn.target, 'input', 'input');

            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', self._bezierPath(sp.x, sp.y, tp.x, tp.y));
            path.setAttribute('class', 'connection-line');
            path.setAttribute('data-conn-idx', idx);

            var srcEl = document.getElementById('node-' + conn.source);
            if (srcEl) {
                var portEl = srcEl.querySelector('.node-port[data-handle="' + conn.handle + '"]');
                if (portEl && portEl.style.borderColor) {
                    path.style.stroke = portEl.style.borderColor;
                    path.style.strokeOpacity = '0.7';
                }
            }

            // Click to delete connection
            path.addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('Remove this connection?')) {
                    self._saveUndo();
                    self.connections.splice(idx, 1);
                    self._renderAllConnections();
                    self.isDirty = true;
                }
            });

            self.svg.appendChild(path);

            // Connection label
            if (conn.label) {
                var midX = (sp.x + tp.x) / 2;
                var midY = (sp.y + tp.y) / 2;
                var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', midX);
                text.setAttribute('y', midY - 8);
                text.setAttribute('class', 'connection-label');
                text.textContent = conn.label;
                self.svg.appendChild(text);
            }
        });

        this._updateConnectedPorts();
    };

    FlowBuilder.prototype._updateConnectedPorts = function() {
        var allDynamic = document.querySelectorAll('.port-output-dynamic');
        allDynamic.forEach(function(p) { p.classList.remove('port-connected'); });

        var connectedInputs = document.querySelectorAll('.node-port.port-input');
        connectedInputs.forEach(function(p) { p.classList.remove('port-connected'); });

        this.connections.forEach(function(conn) {
            var srcEl = document.getElementById('node-' + conn.source);
            if (srcEl) {
                var portEl = srcEl.querySelector('.node-port[data-handle="' + conn.handle + '"]');
                if (portEl) portEl.classList.add('port-connected');
            }
            var tgtEl = document.getElementById('node-' + conn.target);
            if (tgtEl) {
                var inputEl = tgtEl.querySelector('.node-port.port-input');
                if (inputEl) inputEl.classList.add('port-connected');
            }
        });
    };

    // ========================================
    // Canvas panning
    // ========================================
    FlowBuilder.prototype._setupCanvasPan = function() {
        var self = this;
        var isPanning = false;
        var startX, startY, origPanX, origPanY;

        this.wrapper.addEventListener('mousedown', function(e) {
            if (self.isDraggingNode || self.isConnecting) return;
            if (e.target !== self.wrapper && !e.target.classList.contains('flow-canvas') &&
                !e.target.classList.contains('canvas-empty-state') &&
                e.target.tagName !== 'I' && e.target.tagName !== 'H5' && e.target.tagName !== 'P') return;
            e.preventDefault();

            isPanning = true;
            self.isDraggingCanvas = true;
            startX = e.clientX;
            startY = e.clientY;
            origPanX = self.panX;
            origPanY = self.panY;

            function onMove(e2) {
                self.panX = origPanX + (e2.clientX - startX);
                self.panY = origPanY + (e2.clientY - startY);
                self._applyTransform();
            }

            function onUp() {
                isPanning = false;
                self.isDraggingCanvas = false;
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            }

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });

        // Click on canvas background to deselect
        this.wrapper.addEventListener('click', function(e) {
            if (e.target === self.wrapper || e.target.classList.contains('flow-canvas') ||
                e.target.classList.contains('canvas-empty-state')) {
                self._deselectAll();
            }
        });
    };

    // ========================================
    // Zoom
    // ========================================
    FlowBuilder.prototype._setupZoom = function() {
        var self = this;

        this.wrapper.addEventListener('wheel', function(e) {
            e.preventDefault();
            var delta = e.deltaY > 0 ? -0.05 : 0.05;
            self._setZoom(self.zoom + delta, e.clientX, e.clientY);
        });

        document.getElementById('btn-zoom-in').addEventListener('click', function() {
            self._setZoom(self.zoom + 0.1);
        });
        document.getElementById('btn-zoom-out').addEventListener('click', function() {
            self._setZoom(self.zoom - 0.1);
        });
        document.getElementById('btn-zoom-fit').addEventListener('click', function() {
            self._fitToScreen();
        });
    };

    FlowBuilder.prototype._setZoom = function(newZoom, cx, cy) {
        newZoom = Math.max(0.2, Math.min(2, newZoom));

        if (cx !== undefined && cy !== undefined) {
            var rect = this.wrapper.getBoundingClientRect();
            var mx = cx - rect.left;
            var my = cy - rect.top;
            this.panX = mx - (mx - this.panX) * (newZoom / this.zoom);
            this.panY = my - (my - this.panY) * (newZoom / this.zoom);
        }

        this.zoom = newZoom;
        this._applyTransform();
        document.getElementById('zoom-level').textContent = Math.round(this.zoom * 100) + '%';
    };

    FlowBuilder.prototype._fitToScreen = function() {
        var nodeIds = Object.keys(this.nodes);
        if (nodeIds.length === 0) {
            this.zoom = 1;
            this.panX = 0;
            this.panY = 0;
            this._applyTransform();
            document.getElementById('zoom-level').textContent = '100%';
            return;
        }

        var minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        nodeIds.forEach(function(id) {
            var n = this.nodes[id];
            minX = Math.min(minX, n.x);
            minY = Math.min(minY, n.y);
            maxX = Math.max(maxX, n.x + 260);
            maxY = Math.max(maxY, n.y + 120);
        }.bind(this));

        var wrapperRect = this.wrapper.getBoundingClientRect();
        var contentW = maxX - minX + 100;
        var contentH = maxY - minY + 100;
        var scaleX = wrapperRect.width / contentW;
        var scaleY = wrapperRect.height / contentH;
        this.zoom = Math.min(scaleX, scaleY, 1);
        this.panX = (wrapperRect.width - contentW * this.zoom) / 2 - minX * this.zoom + 50;
        this.panY = (wrapperRect.height - contentH * this.zoom) / 2 - minY * this.zoom + 50;
        this._applyTransform();
        document.getElementById('zoom-level').textContent = Math.round(this.zoom * 100) + '%';
    };

    FlowBuilder.prototype._applyTransform = function() {
        this.canvas.style.transform = 'translate(' + this.panX + 'px, ' + this.panY + 'px) scale(' + this.zoom + ')';
    };

    // ========================================
    // Drag & drop from palette
    // ========================================
    FlowBuilder.prototype._setupDragDrop = function() {
        var self = this;

        // Palette drag start
        document.querySelectorAll('.palette-node[draggable]').forEach(function(paletteNode) {
            paletteNode.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('text/plain', paletteNode.getAttribute('data-type'));
                e.dataTransfer.effectAllowed = 'copy';
            });
        });

        // Canvas drop zone
        this.wrapper.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            self.wrapper.classList.add('drag-over');
        });

        this.wrapper.addEventListener('dragleave', function() {
            self.wrapper.classList.remove('drag-over');
        });

        this.wrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            self.wrapper.classList.remove('drag-over');

            var type = e.dataTransfer.getData('text/plain');
            if (!NODE_TYPES[type]) return;

            var rect = self.wrapper.getBoundingClientRect();
            var x = (e.clientX - rect.left - self.panX) / self.zoom;
            var y = (e.clientY - rect.top - self.panY) / self.zoom;

            // Snap to grid (20px)
            x = Math.round(x / 20) * 20;
            y = Math.round(y / 20) * 20;

            self._saveUndo();
            var node = self.addNode(type, x, y);
            self._renderAllConnections();
            self._selectNode(node.id);
            self.isDirty = true;
        });
    };

    // ========================================
    // Palette search
    // ========================================
    FlowBuilder.prototype._setupPaletteSearch = function() {
        var searchInput = document.getElementById('palette-search');
        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            document.querySelectorAll('.palette-node').forEach(function(el) {
                var name = el.querySelector('.palette-node-name').textContent.toLowerCase();
                var desc = el.querySelector('.palette-node-desc').textContent.toLowerCase();
                if (name.indexOf(query) >= 0 || desc.indexOf(query) >= 0 || !query) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });
        });
    };

    // ========================================
    // Keyboard shortcuts
    // ========================================
    FlowBuilder.prototype._setupKeyboard = function() {
        var self = this;

        document.addEventListener('keydown', function(e) {
            // Delete key
            if ((e.key === 'Delete' || e.key === 'Backspace') && self.selectedNodeId) {
                var activeEl = document.activeElement;
                if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA')) return;
                e.preventDefault();
                self.deleteNode(self.selectedNodeId);
            }

            // Ctrl+S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                self._save();
            }

            // Ctrl+Z undo
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') {
                e.preventDefault();
                self._undo();
            }

            // Ctrl+Shift+Z redo
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'z') {
                e.preventDefault();
                self._redo();
            }

            // Escape deselect
            if (e.key === 'Escape') {
                self._deselectAll();
            }
        });
    };

    // ========================================
    // Undo/Redo
    // ========================================
    FlowBuilder.prototype._saveUndo = function() {
        this.undoStack.push(this._getState());
        this.redoStack = [];
        if (this.undoStack.length > 30) this.undoStack.shift();
        this._updateUndoButtons();
    };

    FlowBuilder.prototype._getState = function() {
        return JSON.stringify({ nodes: this.nodes, connections: this.connections });
    };

    FlowBuilder.prototype._restoreState = function(stateStr) {
        var state = JSON.parse(stateStr);
        this.nodesLayer.innerHTML = '';
        this.nodes = {};
        this.connections = state.connections;

        var self = this;
        Object.keys(state.nodes).forEach(function(id) {
            var n = state.nodes[id];
            self.nodes[id] = n;
            self._renderNode(n);
        });

        this._renderAllConnections();
        this._updateEmptyState();
        this._deselectAll();
    };

    FlowBuilder.prototype._undo = function() {
        if (this.undoStack.length === 0) return;
        this.redoStack.push(this._getState());
        this._restoreState(this.undoStack.pop());
        this._updateUndoButtons();
        this.isDirty = true;
    };

    FlowBuilder.prototype._redo = function() {
        if (this.redoStack.length === 0) return;
        this.undoStack.push(this._getState());
        this._restoreState(this.redoStack.pop());
        this._updateUndoButtons();
        this.isDirty = true;
    };

    FlowBuilder.prototype._updateUndoButtons = function() {
        var undoBtn = document.getElementById('btn-undo');
        var redoBtn = document.getElementById('btn-redo');
        if (undoBtn) undoBtn.disabled = this.undoStack.length === 0;
        if (redoBtn) redoBtn.disabled = this.redoStack.length === 0;
    };

    // ========================================
    // Toolbar
    // ========================================
    FlowBuilder.prototype._setupToolbar = function() {
        var self = this;

        document.getElementById('btn-save-flow').addEventListener('click', function() {
            self._save();
        });

        document.getElementById('btn-undo').addEventListener('click', function() {
            self._undo();
        });

        document.getElementById('btn-redo').addEventListener('click', function() {
            self._redo();
        });

        document.getElementById('btn-test-flow').addEventListener('click', function() {
            self._testFlow();
        });
    };

    // ========================================
    // Save
    // ========================================
    FlowBuilder.prototype._save = function() {
        var self = this;
        if (!this.options.flowId || !this.options.saveUrl) {
            this._showToast('Please create the flow first.', 'error');
            return;
        }

        var nodesArr = [];
        Object.keys(this.nodes).forEach(function(id) {
            var n = self.nodes[id];
            nodesArr.push({
                node_uid: n.id,
                type: n.type,
                label: n.label,
                config: n.config,
                position_x: n.x,
                position_y: n.y
            });
        });

        var connsArr = this.connections.map(function(c) {
            return {
                source_node_uid: c.source,
                target_node_uid: c.target,
                source_handle: c.handle,
                label: c.label
            };
        });

        var data = {
            name: document.getElementById('flow-name-input').value,
            nodes: nodesArr,
            connections: connsArr,
            canvas_meta: {
                zoom: this.zoom,
                panX: this.panX,
                panY: this.panY
            }
        };

        var saveBtn = document.getElementById('btn-save-flow');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        fetch(this.options.saveUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.options.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (result.success) {
                self.isDirty = false;
                self._showToast('Flow saved successfully!');
            } else {
                self._showToast('Error saving flow.', 'error');
            }
        })
        .catch(function(err) {
            console.error('Save error:', err);
            self._showToast('Error saving flow.', 'error');
        })
        .finally(function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
        });
    };

    // ========================================
    // Test flow (simulation)
    // ========================================
    FlowBuilder.prototype._testFlow = function() {
        var nodeIds = Object.keys(this.nodes);
        if (nodeIds.length === 0) {
            this._showToast('Add nodes to test the flow.', 'error');
            return;
        }

        // Find trigger node
        var triggerNode = null;
        for (var i = 0; i < nodeIds.length; i++) {
            if (this.nodes[nodeIds[i]].type.indexOf('trigger') === 0) {
                triggerNode = this.nodes[nodeIds[i]];
                break;
            }
        }

        if (!triggerNode) {
            this._showToast('Flow needs a trigger node to test.', 'error');
            return;
        }

        // Visual simulation - highlight nodes in sequence
        var self = this;
        var visited = [];
        var queue = [triggerNode.id];

        function processNext() {
            if (queue.length === 0) {
                self._showToast('Test complete! ' + visited.length + ' nodes traversed.');
                // Remove highlights after a delay
                setTimeout(function() {
                    visited.forEach(function(id) {
                        var el = document.getElementById('node-' + id);
                        if (el) el.style.borderColor = '';
                    });
                }, 3000);
                return;
            }

            var currentId = queue.shift();
            if (visited.indexOf(currentId) >= 0) {
                processNext();
                return;
            }
            visited.push(currentId);

            var el = document.getElementById('node-' + currentId);
            if (el) {
                el.style.borderColor = '#2e7d32';
                el.style.transition = 'border-color 0.3s';
            }

            // Find outgoing connections
            self.connections.forEach(function(c) {
                if (c.source === currentId) {
                    queue.push(c.target);
                }
            });

            setTimeout(processNext, 600);
        }

        this._showToast('Testing flow...');
        processNext();
    };

    // ========================================
    // Toast notifications
    // ========================================
    FlowBuilder.prototype._showToast = function(message, type) {
        var existing = document.querySelector('.save-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.className = 'save-toast' + (type === 'error' ? ' error' : '');
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(function() { toast.classList.add('show'); }, 10);
        setTimeout(function() {
            toast.classList.remove('show');
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    };

    // ========================================
    // Empty state
    // ========================================
    FlowBuilder.prototype._updateEmptyState = function() {
        var hasNodes = Object.keys(this.nodes).length > 0;
        if (this.emptyState) {
            if (hasNodes) {
                this.emptyState.classList.add('d-none');
            } else {
                this.emptyState.classList.remove('d-none');
            }
        }
    };

    // ========================================
    // Unsaved changes warning
    // ========================================
    window.addEventListener('beforeunload', function(e) {
        if (window.flowBuilder && window.flowBuilder.isDirty) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    // ========================================
    // GSM-7 Character Detection (for summary display)
    // ========================================
    var GSM_CHARS = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ ÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
    var GSM_EXTENDED = '^{}\\[~]|€';

    function isGSM7(text) {
        for (var i = 0; i < text.length; i++) {
            var ch = text.charAt(i);
            if (GSM_CHARS.indexOf(ch) === -1 && GSM_EXTENDED.indexOf(ch) === -1) {
                return false;
            }
        }
        return true;
    }

    function countGSM7Chars(text) {
        var count = 0;
        for (var i = 0; i < text.length; i++) {
            count += (GSM_EXTENDED.indexOf(text.charAt(i)) >= 0) ? 2 : 1;
        }
        return count;
    }

    function calculateSegments(text) {
        if (!text || text.length === 0) return { chars: 0, encoding: 'GSM-7', segments: 0 };
        var gsm = isGSM7(text);
        var encoding = gsm ? 'GSM-7' : 'Unicode';
        var charCount = gsm ? countGSM7Chars(text) : text.length;
        var segments;
        if (gsm) {
            segments = charCount <= 160 ? 1 : Math.ceil(charCount / 153);
        } else {
            segments = charCount <= 70 ? 1 : Math.ceil(charCount / 67);
        }
        return { chars: charCount, encoding: encoding, segments: segments };
    }

    // ========================================
    // Send Message - Custom Properties Panel
    // Shows summary of configured message + "Configure Message" button
    // that opens full send-message page in iframe modal
    // ========================================
    FlowBuilder.prototype._renderSendMessageProperties = function(node) {
        var c = node.config || {};
        var html = '';

        // Configured summary
        html += '<div class="mb-3">';
        html += '<label class="form-label">Message Configuration</label>';
        html += '<div class="send-msg-summary">';

        if (c.channel || c.sms_content || c.rcs_payload) {
            var channel = c.channel || 'sms';
            var channelLabels = { 'sms': 'SMS', 'rcs_basic': 'Basic RCS', 'rcs_rich': 'Rich RCS', 'basic_rcs': 'Basic RCS', 'rich_rcs': 'Rich RCS' };
            var channelLabel = channelLabels[channel] || 'SMS';
            var isRichRcs = (channel === 'rcs_rich' || channel === 'rich_rcs');
            var isAnyRcs = isRichRcs || channel === 'rcs_basic' || channel === 'basic_rcs';
            var badgeClass = isRichRcs ? 'rcs' : 'sms';
            html += '<div class="summary-badge ' + badgeClass + '"><i class="fas fa-' + (isRichRcs ? 'palette' : 'sms') + ' me-1"></i>' + channelLabel + '</div>';

            if (c.sender_id_text) {
                html += '<div class="summary-meta"><i class="fas fa-id-badge me-1"></i>' + escapeHtml(c.sender_id_text) + '</div>';
            }
            if (isAnyRcs && c.rcs_agent_name) {
                html += '<div class="summary-meta"><i class="fas fa-robot me-1"></i>' + escapeHtml(c.rcs_agent_name) + '</div>';
            }

            if (isRichRcs && c.rcs_payload) {
                var rcsCards = [];
                if (c.rcs_payload.cards && c.rcs_payload.cards.length > 0) {
                    rcsCards = c.rcs_payload.cards;
                } else if (c.rcs_payload.card) {
                    rcsCards = [c.rcs_payload.card];
                }
                if (rcsCards.length > 0) {
                    var firstCard = rcsCards[0];
                    if (firstCard.description) html += '<div class="summary-text">' + escapeHtml(firstCard.description) + '</div>';
                    var totalBtns = rcsCards.reduce(function(acc, card) { return acc + (card.buttons ? card.buttons.length : card.suggestions ? card.suggestions.length : 0); }, 0);
                    if (totalBtns > 0) html += '<div class="summary-meta">' + totalBtns + ' button(s)</div>';
                }
                if (c.rcs_payload.type === 'carousel' && rcsCards.length > 1) {
                    html += '<div class="summary-meta">Carousel · ' + rcsCards.length + ' cards</div>';
                }
            } else if (c.sms_content) {
                var info = calculateSegments(c.sms_content);
                var preview = c.sms_content.length > 80 ? c.sms_content.substr(0, 77) + '...' : c.sms_content;
                html += '<div class="summary-text">' + escapeHtml(preview) + '</div>';
                html += '<div class="summary-meta">' + info.chars + ' chars · ' + info.encoding + ' · ' + info.segments + ' segment(s)</div>';
            }
        } else {
            html += '<div class="summary-empty"><i class="fas fa-file-alt me-2"></i>No message configured yet</div>';
        }
        html += '</div>';
        html += '</div>';

        // Configure Message button
        html += '<button type="button" class="btn btn-primary w-100 mb-3" id="btn-configure-message" style="background: #886CC0; border-color: #886CC0;">';
        html += '<i class="fas fa-edit me-1"></i> Configure Message';
        html += '</button>';

        // --- Interaction Branching Section ---
        if (c.channel || c.sms_content || c.rcs_payload) {
            html += '<hr class="my-3" style="border-color:#eee;">';
            html += '<label class="form-label">Interaction Branching</label>';

            if (c.interaction_enabled) {
                // Show active interaction summary
                var outputs = this._getInteractionOutputs(node);
                if (outputs) {
                    html += '<div class="interaction-summary">';
                    html += '<div class="mb-2" style="font-size:0.78rem; color:#666;"><i class="fas fa-code-branch me-1"></i>' + outputs.length + ' branch(es) active</div>';

                    // List current branches grouped
                    var lastGroup = '';
                    outputs.forEach(function(out) {
                        if (out.group !== lastGroup) {
                            var gLabels = { rcs: 'RCS', sms: 'SMS', timeout: 'Timeout' };
                            var gText = gLabels[out.group] || out.group;
                            html += '<div class="interaction-summary-group group-' + out.group + '">' + gText + '</div>';
                            lastGroup = out.group;
                        }
                        html += '<div class="interaction-summary-item item-' + out.group + '">';
                        html += '<i class="fas fa-circle me-1" style="font-size:0.45rem;vertical-align:middle;"></i> ' + escapeHtml(out.label);
                        html += '</div>';
                    });
                    html += '</div>';

                    // Keyword management button
                    html += '<button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" id="btn-manage-keywords">';
                    html += '<i class="fas fa-keyboard me-1"></i> Manage Keywords';
                    html += '</button>';

                    // Timeout configuration
                    var timeout = c.interaction_timeout || { value: 24, unit: 'hours' };
                    html += '<div class="mb-2">';
                    html += '<label class="form-label" style="font-size:0.78rem;">No-Response Timeout</label>';
                    html += '<div class="d-flex gap-2">';
                    html += '<input type="number" class="form-control form-control-sm" id="prop-timeout-value" value="' + timeout.value + '" min="1" style="width:70px;">';
                    html += '<select class="form-select form-select-sm" id="prop-timeout-unit" style="width:90px;">';
                    html += '<option value="hours"' + (timeout.unit === 'hours' ? ' selected' : '') + '>Hours</option>';
                    html += '<option value="days"' + (timeout.unit === 'days' ? ' selected' : '') + '>Days</option>';
                    html += '</select>';
                    html += '</div>';
                    html += '</div>';

                    // Disable interaction button
                    html += '<button type="button" class="btn btn-outline-danger btn-sm w-100" id="btn-disable-interaction">';
                    html += '<i class="fas fa-times me-1"></i> Disable Branching';
                    html += '</button>';
                }
            } else {
                html += '<p class="text-muted" style="font-size:0.78rem;">Click "Interaction" on the node to enable response-based branching (RCS buttons, SMS keywords, link clicks).</p>';
            }
        }

        return html;
    };

    FlowBuilder.prototype._bindSendMessageEvents = function(node, nodeId) {
        var self = this;

        var labelInput = document.getElementById('prop-label');
        if (labelInput) {
            labelInput.addEventListener('change', function() {
                node.label = this.value;
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }

        var descInput = document.getElementById('prop-description');
        if (descInput) {
            descInput.addEventListener('change', function() {
                node.config.description = this.value.trim();
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }

        // Configure Message button
        var configBtn = document.getElementById('btn-configure-message');
        if (configBtn) {
            configBtn.addEventListener('click', function() {
                self._openMessageComposer(nodeId);
            });
        }

        // Manage Keywords button
        var keywordsBtn = document.getElementById('btn-manage-keywords');
        if (keywordsBtn) {
            keywordsBtn.addEventListener('click', function() {
                self._openKeywordModal(nodeId);
            });
        }

        // Timeout configuration
        var timeoutValue = document.getElementById('prop-timeout-value');
        var timeoutUnit = document.getElementById('prop-timeout-unit');
        if (timeoutValue) {
            timeoutValue.addEventListener('change', function() {
                if (!node.config.interaction_timeout) node.config.interaction_timeout = { value: 24, unit: 'hours' };
                node.config.interaction_timeout.value = parseInt(this.value, 10) || 24;
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }
        if (timeoutUnit) {
            timeoutUnit.addEventListener('change', function() {
                if (!node.config.interaction_timeout) node.config.interaction_timeout = { value: 24, unit: 'hours' };
                node.config.interaction_timeout.unit = this.value;
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }

        // Disable interaction button
        var disableBtn = document.getElementById('btn-disable-interaction');
        if (disableBtn) {
            disableBtn.addEventListener('click', function() {
                node.config.interaction_enabled = false;
                self._refreshNode(nodeId);
                self._showProperties(nodeId);
                self.isDirty = true;
            });
        }

        // Delete button
        var deleteBtn = document.getElementById('btn-delete-node');
        if (deleteBtn) {
            deleteBtn.onclick = function() {
                if (confirm('Delete this node?')) {
                    self.deleteNode(nodeId);
                }
            };
        }

        // Close button
        var closeBtn = document.getElementById('btn-close-properties');
        if (closeBtn) {
            closeBtn.onclick = function() {
                self._deselectAll();
            };
        }
    };

    // ========================================
    // Toggle interaction branching on/off
    // ========================================
    FlowBuilder.prototype._toggleInteraction = function(nodeId) {
        var node = this.nodes[nodeId];
        if (!node) return;

        this._saveUndo();
        node.config.interaction_enabled = !node.config.interaction_enabled;

        // Set defaults when enabling
        if (node.config.interaction_enabled) {
            if (!node.config.interaction_timeout) {
                node.config.interaction_timeout = { value: 24, unit: 'hours' };
            }
            if (!node.config.interaction_keywords) {
                node.config.interaction_keywords = [];
            }
        }

        this._refreshNode(nodeId);
        if (this.selectedNodeId === nodeId) {
            this._showProperties(nodeId);
        }
        this.isDirty = true;
    };

    // ========================================
    // Preview Modal - shows phone mockup
    // ========================================
    FlowBuilder.prototype._openPreviewModal = function(nodeId) {
        var node = this.nodes[nodeId];
        if (!node) return;
        var c = node.config || {};

        var modalEl = document.getElementById('flowPreviewModal');
        var container = document.getElementById('flowPreviewContainer');
        var toggleContainer = document.getElementById('flowPreviewToggle');
        if (!modalEl || !container) return;

        container.innerHTML = '';
        toggleContainer.innerHTML = '';
        toggleContainer.classList.add('d-none');

        var channel = c.channel || 'sms';
        var isRichRcs = (channel === 'rcs_rich' || channel === 'rich_rcs');
        var isAnyRcs = isRichRcs || channel === 'rcs_basic' || channel === 'basic_rcs';

        var agent = null;
        if (isAnyRcs && c.rcs_agent_name) {
            agent = { name: c.rcs_agent_name, logo: c.rcs_agent_logo || '', verified: true, tagline: 'Business messaging' };
        }

        var smsFallbackText = c.sms_content || '';
        if (!smsFallbackText && isRichRcs && c.rcs_payload) {
            var fallbackCards = c.rcs_payload.cards || (c.rcs_payload.card ? [c.rcs_payload.card] : []);
            if (fallbackCards.length > 0) {
                var fc = fallbackCards[0];
                smsFallbackText = fc.description || fc.title || fc.textBody || '';
            }
        }
        if (!smsFallbackText) smsFallbackText = '(no content)';

        var renderForChannel = function(previewChannel) {
            if (typeof RcsPreviewRenderer === 'undefined') {
                container.innerHTML = '<p class="text-muted text-center p-4">Preview renderer not available.</p>';
                return;
            }
            if (previewChannel === 'sms' || previewChannel === 'basic_rcs' || previewChannel === 'rcs_basic') {
                var ch = (previewChannel === 'sms') ? 'sms' : 'basic_rcs';
                container.innerHTML = RcsPreviewRenderer.renderPreview({
                    channel: ch,
                    message: { type: 'text', body: smsFallbackText },
                    senderId: c.sender_id_text || 'Sender',
                    agent: agent
                });
            } else if (isRichRcs && c.rcs_payload) {
                container.innerHTML = RcsPreviewRenderer.renderRichRcsPreview(c.rcs_payload, agent);
                RcsPreviewRenderer.initCarouselBehavior('#flowPreviewContainer');
            } else {
                var fallbackCh = isAnyRcs ? 'basic_rcs' : 'sms';
                container.innerHTML = RcsPreviewRenderer.renderPreview({
                    channel: fallbackCh,
                    message: { type: 'text', body: smsFallbackText },
                    senderId: c.sender_id_text || 'Sender',
                    agent: agent
                });
            }
        };

        if (isAnyRcs) {
            toggleContainer.innerHTML =
                '<div class="btn-group btn-group-sm" role="group">' +
                    '<button type="button" class="btn btn-sm active" id="previewToggleRcs" style="background:#886CC0;color:#fff;border:1px solid #886CC0;">RCS</button>' +
                    '<button type="button" class="btn btn-sm" id="previewToggleSms" style="background:#fff;color:#886CC0;border:1px solid #886CC0;">SMS</button>' +
                '</div>';
            toggleContainer.classList.remove('d-none');

            renderForChannel(isRichRcs ? 'rcs_rich' : 'basic_rcs');

            setTimeout(function() {
                var rcsBtn = document.getElementById('previewToggleRcs');
                var smsBtn = document.getElementById('previewToggleSms');
                if (rcsBtn) rcsBtn.addEventListener('click', function() {
                    rcsBtn.classList.add('active'); rcsBtn.style.background = '#886CC0'; rcsBtn.style.color = '#fff';
                    smsBtn.classList.remove('active'); smsBtn.style.background = '#fff'; smsBtn.style.color = '#886CC0';
                    renderForChannel(isRichRcs ? 'rcs_rich' : 'basic_rcs');
                });
                if (smsBtn) smsBtn.addEventListener('click', function() {
                    smsBtn.classList.add('active'); smsBtn.style.background = '#886CC0'; smsBtn.style.color = '#fff';
                    rcsBtn.classList.remove('active'); rcsBtn.style.background = '#fff'; rcsBtn.style.color = '#886CC0';
                    renderForChannel('sms');
                });
            }, 0);
        } else {
            renderForChannel('sms');
        }

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    // ========================================
    // Keyword Modal - manage SMS keyword branches
    // ========================================
    FlowBuilder.prototype._openKeywordModal = function(nodeId) {
        var node = this.nodes[nodeId];
        if (!node) return;
        var self = this;

        var modalEl = document.getElementById('flowKeywordModal');
        var listEl = document.getElementById('flowKeywordList');
        var inputEl = document.getElementById('flowKeywordInput');
        var addBtn = document.getElementById('flowKeywordAddBtn');
        var catchAllEl = document.getElementById('flowKeywordCatchAll');
        var warningEl = document.getElementById('flowKeywordWarning');
        if (!modalEl) return;

        var keywords = node.config.interaction_keywords || [];

        // Show warning if sender ID likely can't receive replies
        if (warningEl) {
            var senderText = (node.config.sender_id_text || '').toLowerCase();
            var isVmn = /^\d{10,}$/.test(senderText.replace(/\D/g, ''));
            var isShortcode = /^\d{4,6}$/.test(senderText.replace(/\D/g, ''));
            if (!isVmn && !isShortcode && senderText) {
                warningEl.classList.remove('d-none');
            } else {
                warningEl.classList.add('d-none');
            }
        }

        // Render keyword list
        var renderKeywordList = function() {
            listEl.innerHTML = '';
            keywords.forEach(function(kw, idx) {
                var row = document.createElement('div');
                row.className = 'd-flex align-items-center gap-2 mb-2';
                row.innerHTML =
                    '<span class="badge" style="background:#e3f2fd;color:#1565c0;font-size:0.82rem;padding:6px 10px;">' + escapeHtml(kw.keyword) + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" data-remove="' + idx + '" style="padding:2px 8px;font-size:0.7rem;"><i class="fas fa-times"></i></button>';
                listEl.appendChild(row);
            });

            // Bind remove buttons
            listEl.querySelectorAll('[data-remove]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var removeIdx = parseInt(btn.getAttribute('data-remove'), 10);
                    keywords.splice(removeIdx, 1);
                    renderKeywordList();
                });
            });
        };

        // Set catch-all checkbox
        if (catchAllEl) {
            catchAllEl.checked = !!node.config.interaction_catch_all;
        }

        renderKeywordList();
        if (inputEl) inputEl.value = '';

        // Clone and replace to remove old listeners
        var newAddBtn = addBtn.cloneNode(true);
        addBtn.parentNode.replaceChild(newAddBtn, addBtn);

        var newInputEl = inputEl.cloneNode(true);
        inputEl.parentNode.replaceChild(newInputEl, inputEl);

        var addKeyword = function() {
            var val = (newInputEl.value || '').trim().toUpperCase();
            if (!val) return;
            var exists = keywords.some(function(kw) { return kw.keyword === val; });
            if (exists) { newInputEl.value = ''; return; }
            keywords.push({ keyword: val, handle: 'sms_kw_' + keywords.length });
            newInputEl.value = '';
            renderKeywordList();
            newInputEl.focus();
        };

        newAddBtn.addEventListener('click', addKeyword);
        newInputEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addKeyword(); }
        });

        // Apply button
        var applyBtn = document.getElementById('flowKeywordApplyBtn');
        if (applyBtn) {
            var newApplyBtn = applyBtn.cloneNode(true);
            applyBtn.parentNode.replaceChild(newApplyBtn, applyBtn);
            newApplyBtn.addEventListener('click', function() {
                // Save keywords back to config
                node.config.interaction_keywords = keywords.map(function(kw, idx) {
                    return { keyword: kw.keyword, handle: 'sms_kw_' + idx };
                });
                var catchAllCheckbox = document.getElementById('flowKeywordCatchAll');
                node.config.interaction_catch_all = catchAllCheckbox ? catchAllCheckbox.checked : false;

                self._refreshNode(nodeId);
                if (self.selectedNodeId === nodeId) self._showProperties(nodeId);
                self.isDirty = true;

                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
        }

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    // ========================================
    // Full-Screen Message Composer (iframe embed)
    // ========================================
    FlowBuilder.prototype._openMessageComposer = function(nodeId) {
        var node = this.nodes[nodeId];
        if (!node) return;

        var self = this;
        this._composerNodeId = nodeId;

        var modalEl = document.getElementById('flowMessageComposerModal');
        var iframe = document.getElementById('flowMessageComposerIframe');
        var loading = document.getElementById('flowMessageComposerLoading');

        // Reset state
        iframe.classList.add('d-none');
        loading.classList.remove('d-none');
        iframe.src = '';

        // Show the modal
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        // Load the send-message page with flow context
        iframe.src = '/messages/send?context=flow';

        // Wait for iframe to signal ready, then send config for restoration
        var onMessage = function(e) {
            if (!e.data || !e.data.type) return;

            if (e.data.type === 'flowEmbedReady') {
                // Iframe is loaded — hide loading, show iframe
                loading.classList.add('d-none');
                iframe.classList.remove('d-none');

                // Send existing config to the iframe for restoration
                var config = node.config || {};
                if (config.channel || config.sms_content || config.rcs_payload || config.sender_id) {
                    iframe.contentWindow.postMessage({
                        type: 'flowRestoreConfig',
                        config: config
                    }, '*');
                }
            }

            if (e.data.type === 'flowConfigApplied') {
                var cfg = e.data.config;
                if (cfg) {
                    // Map the iframe's config back to node.config
                    node.config.channel = cfg.channel || 'sms';
                    node.config.sender_id = cfg.sender_id || '';
                    node.config.sender_id_text = cfg.sender_id_text || '';
                    node.config.rcs_agent_id = cfg.rcs_agent_id || '';
                    node.config.rcs_agent_name = cfg.rcs_agent_name || '';
                    node.config.rcs_agent_logo = cfg.rcs_agent_logo || '';
                    node.config.sms_content = cfg.sms_content || '';
                    node.config.rcs_payload = cfg.rcs_payload || null;
                    node.config.rcs_cards_data = cfg.rcs_cards_data || null;
                    node.config.char_count = cfg.char_count || 0;
                    node.config.encoding = cfg.encoding || 'GSM-7';
                    node.config.segments = cfg.segments || 0;
                    node.config.optout_config = cfg.optout_config || null;
                    node.config.trackable_link = cfg.trackable_link || false;
                    node.config.message_expiry = cfg.message_expiry || null;

                    self._refreshNode(nodeId);
                    self._showProperties(nodeId);
                    self.isDirty = true;
                }

                // Clean up and close
                window.removeEventListener('message', onMessage);
                modal.hide();
            }

            if (e.data.type === 'flowConfigCancelled') {
                window.removeEventListener('message', onMessage);
                modal.hide();
            }
        };

        window.addEventListener('message', onMessage);

        // Also clean up listener when modal is hidden via X button or backdrop
        modalEl.addEventListener('hidden.bs.modal', function handler() {
            window.removeEventListener('message', onMessage);
            // Unload iframe to free memory
            iframe.src = 'about:blank';
            iframe.classList.add('d-none');
            modalEl.removeEventListener('hidden.bs.modal', handler);
        });
    };

    // ========================================
    // Initialize message composer modal handlers
    // ========================================
    var _origInit = FlowBuilder.prototype._init;
    FlowBuilder.prototype._init = function() {
        _origInit.call(this);
        var self = this;

        // Close button on the modal header
        var closeBtn = document.getElementById('flowMessageComposerClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                var modalEl = document.getElementById('flowMessageComposerModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
        }
    };

    // ========================================
    // Action Group Step Types
    // ========================================
    var ACTION_GROUP_STEP_TYPES = {
        add_tag: { label: 'Add Tag', icon: 'fa-tag', fields: [{ key: 'tag_name', type: 'text', label: 'Tag Name', placeholder: 'VIP' }] },
        remove_tag: { label: 'Remove Tag', icon: 'fa-tag', fields: [{ key: 'tag_name', type: 'text', label: 'Tag Name', placeholder: 'temporary' }] },
        add_to_list: { label: 'Add to List', icon: 'fa-list', fields: [{ key: 'list_id', type: 'select', label: 'List', dynamic: 'contactLists' }] },
        remove_from_list: { label: 'Remove from List', icon: 'fa-list', fields: [{ key: 'list_id', type: 'select', label: 'List', dynamic: 'contactLists' }] },
        add_optout: { label: 'Add Opt-Out', icon: 'fa-ban', fields: [{ key: 'opt_out_list_id', type: 'select', label: 'Opt-Out List', dynamic: 'optOutLists' }] },
        remove_optout: { label: 'Remove Opt-Out', icon: 'fa-ban', fields: [{ key: 'opt_out_list_id', type: 'select', label: 'Opt-Out List', dynamic: 'optOutLists' }] },
        update_contact: { label: 'Update Contact', icon: 'fa-user-edit', fields: [{ key: 'field_name', type: 'text', label: 'Field', placeholder: 'first_name' }, { key: 'field_value', type: 'text', label: 'Value', placeholder: '{{value}}' }] },
        wait: { label: 'Wait', icon: 'fa-hourglass-half', fields: [{ key: 'value', type: 'text', label: 'Duration', placeholder: '24' }, { key: 'unit', type: 'select', label: 'Unit', options: ['minutes', 'hours', 'days'] }] }
    };

    // ========================================
    // Action Group Properties
    // ========================================
    FlowBuilder.prototype._renderActionGroupProperties = function(node) {
        var c = node.config || {};
        var steps = c.steps || [];
        var html = '';

        html += '<label class="form-label">Steps</label>';

        steps.forEach(function(step, idx) {
            var stepDef = ACTION_GROUP_STEP_TYPES[step.type] || {};
            html += '<div class="action-group-step" data-step-index="' + idx + '">';
            html += '<div class="step-header">';
            html += '<span class="step-number">' + (idx + 1) + '</span>';
            html += '<span class="step-drag-handle"><i class="fas fa-grip-vertical"></i></span>';
            html += '<select class="form-select form-select-sm step-type-select" data-step-index="' + idx + '" style="flex:1;">';
            Object.keys(ACTION_GROUP_STEP_TYPES).forEach(function(key) {
                var selected = (step.type === key) ? ' selected' : '';
                html += '<option value="' + key + '"' + selected + '>' + ACTION_GROUP_STEP_TYPES[key].label + '</option>';
            });
            html += '</select>';
            html += '<button class="step-remove" data-step-index="' + idx + '" title="Remove step"><i class="fas fa-times"></i></button>';
            html += '</div>';

            // Step-specific fields
            html += '<div class="step-fields">';
            if (stepDef.fields) {
                stepDef.fields.forEach(function(field) {
                    var params = step.params || {};
                    if (field.type === 'text') {
                        html += '<input type="text" class="form-control form-control-sm" data-step-index="' + idx + '" data-step-field="' + field.key + '" value="' + escapeHtml(params[field.key] || '') + '" placeholder="' + escapeHtml(field.placeholder || field.label) + '">';
                    } else if (field.type === 'select') {
                        html += '<select class="form-select form-select-sm" data-step-index="' + idx + '" data-step-field="' + field.key + '">';
                        if (field.dynamic && window.__flowBuilderData && window.__flowBuilderData[field.dynamic]) {
                            html += '<option value="">-- Select --</option>';
                            window.__flowBuilderData[field.dynamic].forEach(function(item) {
                                var val = item.id || item.name;
                                var selected = (params[field.key] == val) ? ' selected' : '';
                                html += '<option value="' + escapeHtml(String(val)) + '"' + selected + '>' + escapeHtml(item.name || item.id) + '</option>';
                            });
                        } else if (field.options) {
                            field.options.forEach(function(opt) {
                                var selected = (params[field.key] === opt) ? ' selected' : '';
                                html += '<option value="' + opt + '"' + selected + '>' + opt + '</option>';
                            });
                        }
                        html += '</select>';
                    }
                });
            }
            html += '</div>';
            html += '</div>';
        });

        html += '<button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" id="btn-add-step" style="border-color:#886CC0; color:#886CC0;">';
        html += '<i class="fas fa-plus me-1"></i> Add Step';
        html += '</button>';

        return html;
    };

    FlowBuilder.prototype._bindActionGroupEvents = function(node, nodeId) {
        var self = this;
        var body = document.getElementById('properties-body');

        // Common label/description binding
        this._bindCommonPropertyEvents(node, nodeId);

        // Add step
        var addBtn = document.getElementById('btn-add-step');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                if (!node.config.steps) node.config.steps = [];
                node.config.steps.push({ type: 'add_tag', params: {} });
                self._refreshNode(nodeId);
                self._showProperties(nodeId);
                self.isDirty = true;
            });
        }

        // Remove step
        body.querySelectorAll('.step-remove').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(btn.getAttribute('data-step-index'), 10);
                node.config.steps.splice(idx, 1);
                self._refreshNode(nodeId);
                self._showProperties(nodeId);
                self.isDirty = true;
            });
        });

        // Step type change
        body.querySelectorAll('.step-type-select').forEach(function(sel) {
            sel.addEventListener('change', function() {
                var idx = parseInt(sel.getAttribute('data-step-index'), 10);
                node.config.steps[idx].type = sel.value;
                node.config.steps[idx].params = {};
                self._refreshNode(nodeId);
                self._showProperties(nodeId);
                self.isDirty = true;
            });
        });

        // Step field changes
        body.querySelectorAll('[data-step-field]').forEach(function(input) {
            input.addEventListener('change', function() {
                var idx = parseInt(input.getAttribute('data-step-index'), 10);
                var fieldKey = input.getAttribute('data-step-field');
                if (!node.config.steps[idx].params) node.config.steps[idx].params = {};
                node.config.steps[idx].params[fieldKey] = input.value;
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        });
    };

    // ========================================
    // Webhook Properties (enhanced with credentials)
    // ========================================
    FlowBuilder.prototype._renderWebhookProperties = function(node) {
        var c = node.config || {};
        var html = '';

        html += '<div class="mb-3">';
        html += '<label class="form-label">URL</label>';
        html += '<input type="text" class="form-control" id="prop-webhook-url" value="' + escapeHtml(c.url || '') + '" placeholder="https://api.example.com/webhook">';
        html += '</div>';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Method</label>';
        html += '<select class="form-select" id="prop-webhook-method">';
        ['POST', 'GET', 'PUT', 'PATCH', 'DELETE'].forEach(function(m) {
            var sel = (c.method === m) ? ' selected' : '';
            html += '<option value="' + m + '"' + sel + '>' + m + '</option>';
        });
        html += '</select>';
        html += '</div>';

        // Credential selector
        html += '<div class="mb-3">';
        html += '<label class="form-label">API Credential</label>';
        html += '<div class="d-flex gap-2">';
        html += '<select class="form-select" id="prop-webhook-credential" style="flex:1;">';
        html += '<option value="">None</option>';
        var creds = (window.__flowBuilderData && window.__flowBuilderData.apiCredentials) || [];
        creds.forEach(function(cred) {
            var sel = (c.credential_id == cred.id) ? ' selected' : '';
            html += '<option value="' + escapeHtml(String(cred.id)) + '"' + sel + '>' + escapeHtml(cred.name) + ' (' + cred.auth_type + ')</option>';
        });
        html += '</select>';
        html += '<button type="button" class="btn btn-sm btn-outline-primary" id="btn-new-credential" style="border-color:#886CC0;color:#886CC0;" title="New Credential"><i class="fas fa-plus"></i></button>';
        html += '</div>';
        html += '</div>';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Headers (JSON)</label>';
        html += '<textarea class="form-control" id="prop-webhook-headers" rows="2" placeholder=\'{"Content-Type": "application/json"}\'>' + escapeHtml(c.headers || '') + '</textarea>';
        html += '</div>';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Body Template (JSON)</label>';
        html += '<textarea class="form-control" id="prop-webhook-body" rows="3" placeholder=\'{"phone": "{{phone}}"}\'>' + escapeHtml(c.body || '') + '</textarea>';
        html += '</div>';

        html += '<div class="row mb-3">';
        html += '<div class="col-6">';
        html += '<label class="form-label">Timeout (s)</label>';
        html += '<input type="number" class="form-control" id="prop-webhook-timeout" value="' + (c.timeout || 30) + '" min="1" max="60">';
        html += '</div>';
        html += '<div class="col-6">';
        html += '<label class="form-label">Retries</label>';
        html += '<select class="form-select" id="prop-webhook-retries">';
        ['0', '1', '2', '3'].forEach(function(r) {
            var sel = (String(c.retry_count) === r) ? ' selected' : '';
            html += '<option value="' + r + '"' + sel + '>' + r + '</option>';
        });
        html += '</select>';
        html += '</div>';
        html += '</div>';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Response Variable</label>';
        html += '<input type="text" class="form-control" id="prop-webhook-response-var" value="' + escapeHtml(c.response_variable || '') + '" placeholder="webhook_response">';
        html += '<div class="form-text">Store response for use in downstream Webhook Decision nodes.</div>';
        html += '</div>';

        html += '<div class="alert alert-light p-2 mb-0" style="font-size:0.75rem; border:1px solid #e0e0e0;">';
        html += '<i class="fas fa-info-circle me-1" style="color:#886CC0;"></i>';
        html += '<strong>Success</strong> fires on 2xx responses. <strong>Error</strong> fires on 4xx/5xx or timeout.';
        html += '</div>';

        return html;
    };

    FlowBuilder.prototype._bindWebhookEvents = function(node, nodeId) {
        var self = this;
        this._bindCommonPropertyEvents(node, nodeId);

        var fields = {
            'prop-webhook-url': 'url',
            'prop-webhook-method': 'method',
            'prop-webhook-credential': 'credential_id',
            'prop-webhook-headers': 'headers',
            'prop-webhook-body': 'body',
            'prop-webhook-timeout': 'timeout',
            'prop-webhook-retries': 'retry_count',
            'prop-webhook-response-var': 'response_variable'
        };

        Object.keys(fields).forEach(function(elId) {
            var el = document.getElementById(elId);
            if (el) {
                el.addEventListener('change', function() {
                    node.config[fields[elId]] = el.value;
                    self._refreshNode(nodeId);
                    self.isDirty = true;
                });
            }
        });

        // New credential button
        var newCredBtn = document.getElementById('btn-new-credential');
        if (newCredBtn) {
            newCredBtn.addEventListener('click', function() {
                self._openCredentialModal(nodeId);
            });
        }
    };

    // ========================================
    // Decision Contact Properties
    // ========================================
    FlowBuilder.prototype._renderDecisionContactProperties = function(node) {
        var c = node.config || {};
        var html = '';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Condition</label>';
        html += '<select class="form-select" id="prop-dc-condition">';
        var conditions = ['is_contact', 'not_contact', 'in_list', 'not_in_list', 'has_tag', 'not_has_tag', 'is_opted_out', 'not_opted_out'];
        conditions.forEach(function(cond) {
            var sel = (c.condition === cond) ? ' selected' : '';
            html += '<option value="' + cond + '"' + sel + '>' + cond.replace(/_/g, ' ') + '</option>';
        });
        html += '</select>';
        html += '</div>';

        // List selector (shown for list conditions)
        var showList = (c.condition === 'in_list' || c.condition === 'not_in_list') ? '' : ' style="display:none;"';
        html += '<div class="mb-3" id="dc-list-wrap"' + showList + '>';
        html += '<label class="form-label">Contact List</label>';
        html += '<select class="form-select" id="prop-dc-list-id">';
        html += '<option value="">-- Select --</option>';
        var lists = (window.__flowBuilderData && window.__flowBuilderData.contactLists) || [];
        lists.forEach(function(l) {
            var sel = (c.list_id == l.id) ? ' selected' : '';
            html += '<option value="' + escapeHtml(String(l.id)) + '"' + sel + '>' + escapeHtml(l.name) + '</option>';
        });
        html += '</select>';
        html += '</div>';

        // Tag selector (shown for tag conditions)
        var showTag = (c.condition === 'has_tag' || c.condition === 'not_has_tag') ? '' : ' style="display:none;"';
        html += '<div class="mb-3" id="dc-tag-wrap"' + showTag + '>';
        html += '<label class="form-label">Tag Name</label>';
        html += '<input type="text" class="form-control" id="prop-dc-tag-name" value="' + escapeHtml(c.tag_name || '') + '" placeholder="VIP">';
        html += '</div>';

        // Opt-out list (shown for opt-out conditions)
        var showOpt = (c.condition === 'is_opted_out' || c.condition === 'not_opted_out') ? '' : ' style="display:none;"';
        html += '<div class="mb-3" id="dc-optout-wrap"' + showOpt + '>';
        html += '<label class="form-label">Opt-Out List</label>';
        html += '<select class="form-select" id="prop-dc-optout-id">';
        html += '<option value="">-- Any --</option>';
        var optLists = (window.__flowBuilderData && window.__flowBuilderData.optOutLists) || [];
        optLists.forEach(function(o) {
            var sel = (c.opt_out_list_id == o.id) ? ' selected' : '';
            html += '<option value="' + escapeHtml(String(o.id)) + '"' + sel + '>' + escapeHtml(o.name) + '</option>';
        });
        html += '</select>';
        html += '</div>';

        return html;
    };

    FlowBuilder.prototype._bindDecisionContactEvents = function(node, nodeId) {
        var self = this;
        this._bindCommonPropertyEvents(node, nodeId);

        var condSel = document.getElementById('prop-dc-condition');
        if (condSel) {
            condSel.addEventListener('change', function() {
                node.config.condition = condSel.value;
                // Toggle visibility
                var listWrap = document.getElementById('dc-list-wrap');
                var tagWrap = document.getElementById('dc-tag-wrap');
                var optWrap = document.getElementById('dc-optout-wrap');
                if (listWrap) { (condSel.value === 'in_list' || condSel.value === 'not_in_list') ? listWrap.classList.remove('d-none') : listWrap.classList.add('d-none'); }
                if (tagWrap) { (condSel.value === 'has_tag' || condSel.value === 'not_has_tag') ? tagWrap.classList.remove('d-none') : tagWrap.classList.add('d-none'); }
                if (optWrap) { (condSel.value === 'is_opted_out' || condSel.value === 'not_opted_out') ? optWrap.classList.remove('d-none') : optWrap.classList.add('d-none'); }
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }

        var listSel = document.getElementById('prop-dc-list-id');
        if (listSel) listSel.addEventListener('change', function() { node.config.list_id = listSel.value; self._refreshNode(nodeId); self.isDirty = true; });

        var tagInput = document.getElementById('prop-dc-tag-name');
        if (tagInput) tagInput.addEventListener('change', function() { node.config.tag_name = tagInput.value; self._refreshNode(nodeId); self.isDirty = true; });

        var optSel = document.getElementById('prop-dc-optout-id');
        if (optSel) optSel.addEventListener('change', function() { node.config.opt_out_list_id = optSel.value; self._refreshNode(nodeId); self.isDirty = true; });
    };

    // ========================================
    // Decision Webhook Properties
    // ========================================
    FlowBuilder.prototype._renderDecisionWebhookProperties = function(node) {
        var c = node.config || {};
        var html = '';

        // Find upstream webhook nodes
        html += '<div class="mb-3">';
        html += '<label class="form-label">Source Webhook Node</label>';
        html += '<select class="form-select" id="prop-dw-source">';
        html += '<option value="">-- Select webhook node --</option>';
        var self = this;
        Object.keys(this.nodes).forEach(function(nid) {
            var n = self.nodes[nid];
            if (n.type === 'webhook') {
                var sel = (c.source_node_uid === n.id) ? ' selected' : '';
                html += '<option value="' + n.id + '"' + sel + '>' + escapeHtml(n.label) + '</option>';
            }
        });
        html += '</select>';
        html += '</div>';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Condition Type</label>';
        html += '<select class="form-select" id="prop-dw-condition-type">';
        var condTypes = ['status_code', 'json_path_equals', 'json_path_contains', 'json_path_exists', 'response_empty'];
        condTypes.forEach(function(ct) {
            var sel = (c.condition_type === ct) ? ' selected' : '';
            html += '<option value="' + ct + '"' + sel + '>' + ct.replace(/_/g, ' ') + '</option>';
        });
        html += '</select>';
        html += '</div>';

        var showJsonPath = (c.condition_type && c.condition_type.indexOf('json_path') === 0) ? '' : ' style="display:none;"';
        html += '<div class="mb-3" id="dw-json-path-wrap"' + showJsonPath + '>';
        html += '<label class="form-label">JSON Path</label>';
        html += '<input type="text" class="form-control" id="prop-dw-json-path" value="' + escapeHtml(c.json_path || '') + '" placeholder="data.status">';
        html += '</div>';

        var showCompare = (c.condition_type && c.condition_type !== 'json_path_exists' && c.condition_type !== 'response_empty') ? '' : ' style="display:none;"';
        html += '<div id="dw-compare-wrap"' + showCompare + '>';
        html += '<div class="mb-3">';
        html += '<label class="form-label">Operator</label>';
        html += '<select class="form-select" id="prop-dw-operator">';
        ['equals', 'not_equals', 'contains', 'greater_than', 'less_than'].forEach(function(op) {
            var sel = (c.operator === op) ? ' selected' : '';
            html += '<option value="' + op + '"' + sel + '>' + op.replace(/_/g, ' ') + '</option>';
        });
        html += '</select>';
        html += '</div>';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Compare Value</label>';
        html += '<input type="text" class="form-control" id="prop-dw-compare-value" value="' + escapeHtml(c.compare_value || '') + '" placeholder="success">';
        html += '</div>';
        html += '</div>';

        return html;
    };

    FlowBuilder.prototype._bindDecisionWebhookEvents = function(node, nodeId) {
        var self = this;
        this._bindCommonPropertyEvents(node, nodeId);

        var fields = {
            'prop-dw-source': 'source_node_uid',
            'prop-dw-condition-type': 'condition_type',
            'prop-dw-json-path': 'json_path',
            'prop-dw-operator': 'operator',
            'prop-dw-compare-value': 'compare_value'
        };

        Object.keys(fields).forEach(function(elId) {
            var el = document.getElementById(elId);
            if (el) {
                el.addEventListener('change', function() {
                    node.config[fields[elId]] = el.value;

                    // Toggle visibility for condition type changes
                    if (elId === 'prop-dw-condition-type') {
                        var jsonWrap = document.getElementById('dw-json-path-wrap');
                        var compareWrap = document.getElementById('dw-compare-wrap');
                        if (jsonWrap) { el.value.indexOf('json_path') === 0 ? jsonWrap.classList.remove('d-none') : jsonWrap.classList.add('d-none'); }
                        if (compareWrap) { (el.value !== 'json_path_exists' && el.value !== 'response_empty') ? compareWrap.classList.remove('d-none') : compareWrap.classList.add('d-none'); }
                    }

                    self._refreshNode(nodeId);
                    self.isDirty = true;
                });
            }
        });
    };

    // ========================================
    // Common property event binding helper
    // ========================================
    FlowBuilder.prototype._bindCommonPropertyEvents = function(node, nodeId) {
        var self = this;

        var labelInput = document.getElementById('prop-label');
        if (labelInput) {
            labelInput.addEventListener('change', function() {
                node.label = this.value;
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }

        var descInput = document.getElementById('prop-description');
        if (descInput) {
            descInput.addEventListener('change', function() {
                node.config.description = this.value.trim();
                self._refreshNode(nodeId);
                self.isDirty = true;
            });
        }

        var deleteBtn = document.getElementById('btn-delete-node');
        if (deleteBtn) {
            deleteBtn.onclick = function() {
                if (confirm('Delete this node?')) {
                    self.deleteNode(nodeId);
                }
            };
        }

        var closeBtn = document.getElementById('btn-close-properties');
        if (closeBtn) {
            closeBtn.onclick = function() {
                self._deselectAll();
            };
        }
    };

    // ========================================
    // Credential Modal
    // ========================================
    FlowBuilder.prototype._openCredentialModal = function(nodeId) {
        var self = this;
        var modalEl = document.getElementById('flowCredentialModal');
        if (!modalEl) return;

        // Reset fields
        document.getElementById('credentialName').value = '';
        document.getElementById('credentialAuthType').value = 'bearer';
        self._toggleCredentialFields('bearer');

        var errorEl = document.getElementById('credentialError');
        if (errorEl) errorEl.classList.add('d-none');

        // Auth type toggle
        var authSelect = document.getElementById('credentialAuthType');
        authSelect.onchange = function() { self._toggleCredentialFields(authSelect.value); };

        // Save button
        var saveBtn = document.getElementById('flowCredentialSaveBtn');
        var newSaveBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

        newSaveBtn.addEventListener('click', function() {
            self._saveCredential(nodeId);
        });

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    FlowBuilder.prototype._toggleCredentialFields = function(authType) {
        document.querySelectorAll('.credential-fields').forEach(function(el) {
            el.classList.add('d-none');
        });
        var target = document.getElementById('credentialFields-' + authType);
        if (target) target.classList.remove('d-none');
    };

    FlowBuilder.prototype._saveCredential = function(nodeId) {
        var self = this;
        var name = document.getElementById('credentialName').value.trim();
        var authType = document.getElementById('credentialAuthType').value;
        var errorEl = document.getElementById('credentialError');

        if (!name) {
            errorEl.textContent = 'Name is required.';
            errorEl.classList.remove('d-none');
            return;
        }

        var credentials = {};
        if (authType === 'basic') {
            credentials = {
                username: document.getElementById('credentialBasicUser').value,
                password: document.getElementById('credentialBasicPass').value
            };
        } else if (authType === 'bearer') {
            credentials = { token: document.getElementById('credentialBearerToken').value };
        } else if (authType === 'api_key') {
            credentials = {
                header_name: document.getElementById('credentialApiKeyHeader').value || 'X-API-Key',
                key: document.getElementById('credentialApiKeyValue').value
            };
        } else if (authType === 'custom_header') {
            var headerName = document.getElementById('credentialCustomHeaderName').value;
            var headerValue = document.getElementById('credentialCustomHeaderValue').value;
            credentials = { headers: {} };
            if (headerName) credentials.headers[headerName] = headerValue;
        }

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            errorEl.textContent = 'Session expired. Please refresh the page.';
            errorEl.classList.remove('d-none');
            return;
        }
        fetch('/api-credentials', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name, auth_type: authType, credentials: credentials })
        })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (result.success) {
                // Add to local data
                if (!window.__flowBuilderData.apiCredentials) window.__flowBuilderData.apiCredentials = [];
                window.__flowBuilderData.apiCredentials.push(result.credential);

                // Auto-select in webhook node
                if (nodeId) {
                    var node = self.nodes[nodeId];
                    if (node) {
                        node.config.credential_id = result.credential.id;
                        self._refreshNode(nodeId);
                        self._showProperties(nodeId);
                        self.isDirty = true;
                    }
                }

                var modalEl = document.getElementById('flowCredentialModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                self._showToast('Credential saved!');
            } else {
                errorEl.textContent = 'Failed to save credential.';
                errorEl.classList.remove('d-none');
            }
        })
        .catch(function(err) {
            console.error('Credential save error:', err);
            errorEl.textContent = 'Network error saving credential.';
            errorEl.classList.remove('d-none');
        });
    };

    // Export
    window.FlowBuilder = FlowBuilder;

})(window);

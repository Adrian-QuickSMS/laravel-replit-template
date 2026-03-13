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
        trigger_sms_keyword: {
            label: 'SMS Keyword',
            icon: 'fa-comment-dots',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            configFields: [
                { key: 'keywords', type: 'text', label: 'Keywords (comma separated)', placeholder: 'HELP, INFO, BALANCE' },
                { key: 'match_type', type: 'select', label: 'Match Type', options: ['exact', 'contains', 'starts_with'] }
            ]
        },
        trigger_rcs_button: {
            label: 'RCS Button',
            icon: 'fa-hand-pointer',
            category: 'trigger',
            outputs: ['default'],
            inputs: false,
            configFields: [
                { key: 'button_text', type: 'text', label: 'Button Text', placeholder: 'Track Delivery' },
                { key: 'postback_data', type: 'text', label: 'Postback Data', placeholder: 'track_delivery' }
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
        send_message: {
            label: 'Send Message',
            icon: 'fa-paper-plane',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            configFields: [],
            customProperties: true,
            dynamicOutputs: true  // outputs determined by node.config at runtime
        },
        webhook: {
            label: 'Webhook',
            icon: 'fa-globe',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            configFields: [
                { key: 'url', type: 'text', label: 'URL', placeholder: 'https://api.example.com/webhook' },
                { key: 'method', type: 'select', label: 'Method', options: ['POST', 'GET', 'PUT'] },
                { key: 'headers', type: 'textarea', label: 'Headers (JSON)', placeholder: '{"Authorization": "Bearer ..."}' },
                { key: 'body', type: 'textarea', label: 'Body Template (JSON)', placeholder: '{"phone": "{{phone}}"}' },
                { key: 'retry_count', type: 'select', label: 'Retries', options: ['0', '1', '2', '3'] }
            ]
        },
        tag: {
            label: 'Tag / List',
            icon: 'fa-tag',
            category: 'action',
            outputs: ['default'],
            inputs: true,
            configFields: [
                { key: 'action', type: 'select', label: 'Action', options: ['add_tag', 'remove_tag', 'add_to_list', 'remove_from_list', 'update_field'] },
                { key: 'value', type: 'text', label: 'Tag / List / Field Name', placeholder: 'interested' },
                { key: 'field_value', type: 'text', label: 'Field Value (for update)', placeholder: 'gold' }
            ]
        },
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
                { node_uid: 'n5', type: 'tag', label: 'Tag: Onboarded', position_x: 400, position_y: 680, config: { action: 'add_tag', value: 'onboarded' } },
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
                { node_uid: 'n5', type: 'tag', label: 'Tag: Confirmed', position_x: 250, position_y: 700, config: { action: 'add_tag', value: 'appointment_confirmed' } },
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

    // Renders output ports on a node element (handles static and dynamic outputs)
    FlowBuilder.prototype._renderOutputPorts = function(el, node) {
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
                var groupDiv = document.createElement('div');
                groupDiv.className = 'interaction-group group-' + groupKey;

                var groupText = groupLabels[groupKey] !== undefined ? groupLabels[groupKey] : groupKey;
                var groupLabel = document.createElement('div');
                groupLabel.className = 'interaction-group-label group-' + groupKey;
                groupLabel.textContent = groupText;
                groupDiv.appendChild(groupLabel);

                groups[groupKey].forEach(function(item) {
                    var row = document.createElement('div');
                    row.className = 'interaction-port-row';

                    var label = document.createElement('span');
                    label.className = 'interaction-port-label port-label-' + item.out.group;
                    label.textContent = item.out.label;
                    row.appendChild(label);

                    groupDiv.appendChild(row);
                });

                var groupPortsRow = document.createElement('div');
                groupPortsRow.className = 'interaction-group-ports';
                groups[groupKey].forEach(function(item) {
                    var port = document.createElement('div');
                    port.className = 'node-port port-output-dynamic port-' + item.out.group;
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
                var yesPort = document.createElement('div');
                yesPort.className = 'node-port port-output-yes';
                yesPort.setAttribute('data-port', 'output');
                yesPort.setAttribute('data-handle', 'yes');
                yesPort.setAttribute('data-node-id', node.id);
                el.appendChild(yesPort);

                var yesLabel = document.createElement('div');
                yesLabel.className = 'branch-label yes';
                yesLabel.textContent = 'Yes';
                el.appendChild(yesLabel);

                var noPort = document.createElement('div');
                noPort.className = 'node-port port-output-no';
                noPort.setAttribute('data-port', 'output');
                noPort.setAttribute('data-handle', 'no');
                noPort.setAttribute('data-node-id', node.id);
                el.appendChild(noPort);

                var noLabel = document.createElement('div');
                noLabel.className = 'branch-label no';
                noLabel.textContent = 'No';
                el.appendChild(noLabel);
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
            case 'trigger_sms_keyword':
                return c.keywords ? 'Keywords: ' + c.keywords : '';
            case 'webhook':
                return c.url ? c.method + ' ' + c.url : '';
            case 'wait':
                return c.duration_value ? 'Wait ' + c.duration_value + ' ' + (c.duration_unit || 'hours') : '';
            case 'decision':
                return c.condition_type ? 'If ' + c.condition_type + (c.field ? ' (' + c.field + ')' : '') : '';
            case 'tag':
                return c.action ? c.action + ': ' + (c.value || '') : '';
            case 'trigger_schedule':
                return c.schedule_type ? c.schedule_type + ' at ' + (c.time || '') : '';
            case 'inbox_handoff':
                return c.assign_to ? 'Assign: ' + c.assign_to : '';
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

        targetEl.style.display = 'none';
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
            targetEl.style.display = '';
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
        panel.style.display = 'flex';

        var html = '';
        html += '<div class="mb-3">';
        html += '<label class="form-label">Node Label</label>';
        html += '<input type="text" class="form-control" id="prop-label" value="' + escapeHtml(node.label) + '">';
        html += '</div>';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Description</label>';
        html += '<textarea class="form-control" id="prop-description" rows="2" maxlength="200" placeholder="Describe what this step does...">' + escapeHtml(node.config.description || '') + '</textarea>';
        html += '</div>';

        if (typeDef.customProperties && node.type === 'send_message') {
            html += this._renderSendMessageProperties(node);
            body.innerHTML = html;
            this._bindSendMessageEvents(node, nodeId);
        } else {
            // Config fields
            typeDef.configFields.forEach(function(field) {
                html += '<div class="mb-3">';
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
                    field.options.forEach(function(opt) {
                        var selected = (node.config[field.key] === opt) ? ' selected' : '';
                        html += '<option value="' + escapeHtml(opt) + '"' + selected + '>' + escapeHtml(opt.replace(/_/g, ' ')) + '</option>';
                    });
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
        document.getElementById('flow-properties').style.display = 'none';
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
                // Get position relative to node element
                var portRect = portEl.getBoundingClientRect();
                var elRect = el.getBoundingClientRect();
                var portX = node.x + (portRect.left - elRect.left + portRect.width / 2);
                var portY = node.y + (portRect.top - elRect.top + portRect.height / 2);
                return { x: portX, y: portY };
            }
        }

        // Static output ports
        if (typeDef.outputs.length === 2) {
            if (handle === 'yes') return { x: node.x + w * 0.3, y: node.y + h };
            if (handle === 'no') return { x: node.x + w * 0.7, y: node.y + h };
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
                el.style.display = (name.indexOf(query) >= 0 || desc.indexOf(query) >= 0 || !query) ? 'flex' : 'none';
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
            this.emptyState.style.display = hasNodes ? 'none' : 'block';
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

        var modal = new bootstrap.Modal(modalEl);
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

        var modal = new bootstrap.Modal(modalEl);
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
        var modal = new bootstrap.Modal(modalEl);
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

    // Export
    window.FlowBuilder = FlowBuilder;

})(window);

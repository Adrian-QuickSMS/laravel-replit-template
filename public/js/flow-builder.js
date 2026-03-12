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
            customProperties: true
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
                var type = n.type;
                var config = n.config || {};
                if (type === 'send_sms') {
                    type = 'send_message';
                    config = { channel: 'sms', sms_content: config.message || '', sender_id: config.sender_id || '' };
                } else if (type === 'send_rcs') {
                    type = 'send_message';
                    config = { channel: config.buttons ? 'rich_rcs' : 'basic_rcs', sms_content: config.fallback_sms ? (config.message || '') : '', rcs_payload: config.buttons ? { type: 'standalone', card: { title: '', description: config.message || '', suggestions: [] } } : null };
                    if (!config.rcs_payload) config.sms_content = (n.config || {}).message || '';
                }
                self.addNode(type, n.position_x, n.position_y, config, n.label, n.node_uid);
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

    FlowBuilder.prototype._renderNode = function(node) {
        var typeDef = NODE_TYPES[node.type];
        var el = document.createElement('div');
        el.className = 'flow-node';
        el.id = 'node-' + node.id;
        el.setAttribute('data-node-id', node.id);
        el.style.left = node.x + 'px';
        el.style.top = node.y + 'px';

        var configPreview = this._getConfigPreview(node);

        el.innerHTML =
            '<div class="flow-node-header">' +
                '<div class="node-icon ' + typeDef.category + '"><i class="fas ' + typeDef.icon + '"></i></div>' +
                '<div style="flex:1; min-width:0;">' +
                    '<div class="node-label">' + escapeHtml(node.label) + '</div>' +
                    '<div class="node-type-label">' + escapeHtml(typeDef.label) + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="flow-node-body">' +
                (configPreview ? '<div class="config-preview">' + escapeHtml(configPreview) + '</div>' : '<span style="color:#ccc; font-style:italic;">Click to configure</span>') +
            '</div>';

        // Add ports
        if (typeDef.inputs) {
            var inputPort = document.createElement('div');
            inputPort.className = 'node-port port-input';
            inputPort.setAttribute('data-port', 'input');
            inputPort.setAttribute('data-node-id', node.id);
            el.appendChild(inputPort);
        }

        if (typeDef.outputs.length === 1) {
            var outputPort = document.createElement('div');
            outputPort.className = 'node-port port-output';
            outputPort.setAttribute('data-port', 'output');
            outputPort.setAttribute('data-handle', 'default');
            outputPort.setAttribute('data-node-id', node.id);
            el.appendChild(outputPort);
        } else if (typeDef.outputs.length === 2) {
            // Yes/No ports for decision nodes
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

        // Event listeners
        this._setupNodeDrag(el, node);
        this._setupNodeClick(el, node);
        this._setupPortEvents(el, node);

        this.nodesLayer.appendChild(el);
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
                    var card = c.rcs_payload.card;
                    var label = 'RCS';
                    if (card && card.title) label += ': ' + card.title;
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
        var typeDef = NODE_TYPES[node.type];
        var configPreview = this._getConfigPreview(node);

        el.querySelector('.node-label').textContent = node.label;
        var bodyEl = el.querySelector('.flow-node-body');
        if (configPreview) {
            bodyEl.innerHTML = '<div class="config-preview">' + escapeHtml(configPreview) + '</div>';
        } else {
            bodyEl.innerHTML = '<span style="color:#ccc; font-style:italic;">Click to configure</span>';
        }
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
        // Node label field
        html += '<div class="mb-3">';
        html += '<label class="form-label">Node Label</label>';
        html += '<input type="text" class="form-control" id="prop-label" value="' + escapeHtml(node.label) + '">';
        html += '</div>';

        // Custom properties for send_message
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

        // Output
        var typeDef = NODE_TYPES[node.type];
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
    // GSM-7 Character Detection
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
    // that opens the full send-message page in an iframe modal
    // ========================================
    FlowBuilder.prototype._renderSendMessageProperties = function(node) {
        var c = node.config || {};
        var html = '';

        html += '<div class="mb-3">';
        html += '<label class="form-label">Message Configuration</label>';
        html += '<div class="send-msg-summary">';

        if (c.channel) {
            var chLabels = { 'sms': 'SMS', 'rcs_basic': 'Basic RCS', 'rcs_rich': 'Rich RCS', 'basic_rcs': 'Basic RCS', 'rich_rcs': 'Rich RCS' };
            var chDisplay = chLabels[c.channel] || c.channel.toUpperCase();
            var isRichRcs = (c.channel === 'rcs_rich' || c.channel === 'rich_rcs');

            if (isRichRcs && c.rcs_payload) {
                html += '<div class="summary-badge rcs"><i class="fas fa-palette me-1"></i>Rich RCS</div>';
                if (c.rcs_payload.card && c.rcs_payload.card.title) {
                    html += '<div class="summary-text">' + escapeHtml(c.rcs_payload.card.title) + '</div>';
                }
                if (c.rcs_payload.type === 'carousel') {
                    html += '<div class="summary-meta">Carousel</div>';
                }
            } else if (c.sms_content) {
                var info = calculateSegments(c.sms_content);
                html += '<div class="summary-badge sms"><i class="fas fa-sms me-1"></i>' + chDisplay + '</div>';
                var preview = c.sms_content.length > 80 ? c.sms_content.substr(0, 77) + '...' : c.sms_content;
                html += '<div class="summary-text">' + escapeHtml(preview) + '</div>';
                html += '<div class="summary-meta">' + info.chars + ' chars · ' + info.encoding + ' · ' + info.segments + ' segment(s)</div>';
            } else {
                html += '<div class="summary-badge sms"><i class="fas fa-sms me-1"></i>' + chDisplay + '</div>';
                html += '<div class="summary-empty"><i class="fas fa-file-alt me-2"></i>No content yet</div>';
            }

            if (c.sender_name) {
                html += '<div class="summary-meta">Sender: ' + escapeHtml(c.sender_name) + '</div>';
            }
            if (c.optout_config && c.optout_config.enabled) {
                html += '<div class="summary-meta"><i class="fas fa-ban me-1"></i>Opt-out enabled</div>';
            }
        } else {
            html += '<div class="summary-empty"><i class="fas fa-file-alt me-2"></i>No message configured yet</div>';
        }
        html += '</div>';
        html += '</div>';

        html += '<button type="button" class="btn btn-primary w-100 mb-3" id="btn-configure-message" style="background: #886CC0; border-color: #886CC0;">';
        html += '<i class="fas fa-edit me-1"></i> Configure Message';
        html += '</button>';

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

        var configBtn = document.getElementById('btn-configure-message');
        if (configBtn) {
            configBtn.addEventListener('click', function() {
                self._openMessageComposerIframe(nodeId);
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
    // Full-Screen Message Composer (iframe embed)
    // ========================================
    FlowBuilder.prototype._openMessageComposerIframe = function(nodeId) {
        var self = this;
        var node = this.nodes[nodeId];
        if (!node) return;

        this._iframeComposerNodeId = nodeId;

        var modalEl = document.getElementById('flowMessageComposerModal');
        var iframe = document.getElementById('flowMessageComposerIframe');
        var loading = document.getElementById('flowMessageComposerLoading');
        var loadTimeout = null;
        var cleaned = false;

        loading.classList.remove('d-none');
        iframe.classList.add('d-none');

        iframe.onload = function() {
            clearTimeout(loadTimeout);
            loading.classList.add('d-none');
            iframe.classList.remove('d-none');

            setTimeout(function() {
                var existingConfig = node.config || {};
                if (existingConfig.channel || existingConfig.sms_content || existingConfig.sender_id || existingConfig.rcs_payload) {
                    try {
                        iframe.contentWindow.postMessage({
                            type: 'flowRestoreConfig',
                            config: existingConfig
                        }, '*');
                    } catch(err) {
                        console.warn('[FlowBuilder] postMessage to iframe failed:', err);
                    }
                }
            }, 500);
        };

        iframe.src = '/messages/send?context=flow';

        loadTimeout = setTimeout(function() {
            if (!cleaned) {
                loading.innerHTML = '<div class="text-center"><i class="fas fa-exclamation-triangle mb-2 text-warning" style="font-size:2rem;"></i><p class="text-muted mb-2">Failed to load message composer.</p><button class="btn btn-sm btn-outline-secondary" onclick="bootstrap.Modal.getInstance(document.getElementById(\'flowMessageComposerModal\')).hide()">Close</button></div>';
            }
        }, 15000);

        function onMessage(e) {
            if (!e.data || !e.data.type) return;
            var validTypes = ['flowEmbedReady', 'flowConfigApplied', 'flowConfigCancelled', 'flowRestoreComplete'];
            if (validTypes.indexOf(e.data.type) === -1) return;

            if (e.data.type === 'flowEmbedReady') {
                clearTimeout(loadTimeout);
                loading.classList.add('d-none');
                iframe.classList.remove('d-none');

                var existingConfig = node.config || {};
                if (existingConfig.channel || existingConfig.sms_content || existingConfig.sender_id || existingConfig.rcs_payload) {
                    iframe.contentWindow.postMessage({
                        type: 'flowRestoreConfig',
                        config: existingConfig
                    }, '*');
                }
            }

            if (e.data.type === 'flowConfigApplied') {
                var config = e.data.config || {};
                node.config = config;
                self._refreshNode(nodeId);
                self._showProperties(nodeId);
                self.isDirty = true;

                closeModal();
            }

            if (e.data.type === 'flowConfigCancelled') {
                closeModal();
            }
        }

        function closeModal() {
            if (cleaned) return;
            cleaned = true;
            clearTimeout(loadTimeout);
            window.removeEventListener('message', onMessage);
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            iframe.src = 'about:blank';
            iframe.classList.add('d-none');
            self._iframeComposerNodeId = null;
        }

        window.addEventListener('message', onMessage);

        var closeBtn = document.getElementById('flowMessageComposerClose');
        if (closeBtn) {
            closeBtn.onclick = function() { closeModal(); };
        }

        modalEl.addEventListener('hidden.bs.modal', function handler() {
            closeModal();
            modalEl.removeEventListener('hidden.bs.modal', handler);
        });

        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    // ========================================
    // Initialize (no modal handlers needed — iframe handles everything)
    // ========================================
    var _origInit = FlowBuilder.prototype._init;
    FlowBuilder.prototype._init = function() {
        _origInit.call(this);
    };

    // Export
    window.FlowBuilder = FlowBuilder;

})(window);

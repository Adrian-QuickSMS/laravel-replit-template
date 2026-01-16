/**
 * QuickSMS Account Lifecycle State Management
 * 
 * Core state model for account lifecycle management.
 * This service is authoritative - state is NOT inferred from balance or activity.
 * 
 * States:
 * - TEST: New accounts, evaluation period, limited features
 * - LIVE_SELF_SERVICE: Verified accounts, full self-service access
 * - LIVE_SALES_OVERRIDE: Enterprise accounts, sales-managed
 * - SUSPENDED: Temporarily blocked (fraud, billing, compliance)
 * - CLOSED: Permanently closed, read-only access
 * 
 * Backend Integration:
 * - State stored in accounts table: lifecycle_state VARCHAR(30) NOT NULL
 * - All transitions logged to account_lifecycle_audit table
 * - API: GET /api/account/lifecycle, POST /api/account/lifecycle/transition
 */

(function(global) {
    'use strict';

    var AccountLifecycle = {
        
        // =====================================================
        // STATE DEFINITIONS
        // =====================================================
        
        STATES: {
            TEST: 'TEST',
            LIVE_SELF_SERVICE: 'LIVE_SELF_SERVICE',
            LIVE_SALES_OVERRIDE: 'LIVE_SALES_OVERRIDE',
            SUSPENDED: 'SUSPENDED',
            CLOSED: 'CLOSED'
        },
        
        STATE_META: {
            TEST: {
                label: 'Test Account',
                description: 'Evaluation period with limited features',
                badge_class: 'badge-warning',
                icon: 'fas fa-flask',
                color: '#f0ad4e',
                can_send_messages: true,
                can_use_api: true,
                can_purchase: false,
                can_access_inbox: true,
                max_daily_messages: 100,
                requires_approval_for: ['bulk_send', 'api_production']
            },
            LIVE_SELF_SERVICE: {
                label: 'Live - Self Service',
                description: 'Full self-service access',
                badge_class: 'badge-success',
                icon: 'fas fa-check-circle',
                color: '#5cb85c',
                can_send_messages: true,
                can_use_api: true,
                can_purchase: true,
                can_access_inbox: true,
                max_daily_messages: null,
                requires_approval_for: []
            },
            LIVE_SALES_OVERRIDE: {
                label: 'Live - Enterprise',
                description: 'Sales-managed enterprise account',
                badge_class: 'badge-primary',
                icon: 'fas fa-building',
                color: '#7c3aed',
                can_send_messages: true,
                can_use_api: true,
                can_purchase: true,
                can_access_inbox: true,
                max_daily_messages: null,
                requires_approval_for: [],
                sales_managed: true
            },
            SUSPENDED: {
                label: 'Suspended',
                description: 'Account temporarily suspended',
                badge_class: 'badge-danger',
                icon: 'fas fa-ban',
                color: '#d9534f',
                can_send_messages: false,
                can_use_api: false,
                can_purchase: false,
                can_access_inbox: true,
                max_daily_messages: 0,
                requires_approval_for: ['all'],
                blocked_reason_required: true
            },
            CLOSED: {
                label: 'Closed',
                description: 'Account permanently closed',
                badge_class: 'badge-secondary',
                icon: 'fas fa-times-circle',
                color: '#6c757d',
                can_send_messages: false,
                can_use_api: false,
                can_purchase: false,
                can_access_inbox: false,
                max_daily_messages: 0,
                requires_approval_for: ['all'],
                read_only: true
            }
        },
        
        // Valid state transitions (from -> [allowed_to_states])
        VALID_TRANSITIONS: {
            TEST: ['LIVE_SELF_SERVICE', 'LIVE_SALES_OVERRIDE', 'SUSPENDED', 'CLOSED'],
            LIVE_SELF_SERVICE: ['LIVE_SALES_OVERRIDE', 'SUSPENDED', 'CLOSED'],
            LIVE_SALES_OVERRIDE: ['LIVE_SELF_SERVICE', 'SUSPENDED', 'CLOSED'],
            SUSPENDED: ['TEST', 'LIVE_SELF_SERVICE', 'LIVE_SALES_OVERRIDE', 'CLOSED'],
            CLOSED: [] // No transitions from CLOSED
        },
        
        // =====================================================
        // CURRENT STATE (loaded from backend/session)
        // =====================================================
        
        _currentState: null,
        _stateLoadedAt: null,
        _accountId: null,
        _suspensionReason: null,
        _stateChangedAt: null,
        _stateChangeCallbacks: [],
        
        // =====================================================
        // INITIALIZATION
        // =====================================================
        
        init: function(accountData) {
            if (!accountData || !accountData.lifecycle_state) {
                console.error('[AccountLifecycle] Invalid account data provided');
                return false;
            }
            
            if (!this.isValidState(accountData.lifecycle_state)) {
                console.error('[AccountLifecycle] Invalid state:', accountData.lifecycle_state);
                return false;
            }
            
            this._currentState = accountData.lifecycle_state;
            this._accountId = accountData.account_id || null;
            this._suspensionReason = accountData.suspension_reason || null;
            this._stateChangedAt = accountData.state_changed_at || null;
            this._stateLoadedAt = new Date().toISOString();
            
            console.log('[AccountLifecycle] Initialized:', this._currentState, 'for account:', this._accountId);
            
            // Emit event for UI components to react
            this._emitStateEvent('lifecycle:initialized', {
                state: this._currentState,
                meta: this.getStateMeta()
            });
            
            return true;
        },
        
        // Initialize with TEST state for new signups
        initNewAccount: function(accountId) {
            return this.init({
                account_id: accountId,
                lifecycle_state: this.STATES.TEST,
                state_changed_at: new Date().toISOString()
            });
        },
        
        // =====================================================
        // STATE QUERIES
        // =====================================================
        
        getCurrentState: function() {
            return this._currentState;
        },
        
        getStateMeta: function(state) {
            state = state || this._currentState;
            return this.STATE_META[state] || null;
        },
        
        // Register a callback to be notified of state changes
        onStateChange: function(callback) {
            if (typeof callback === 'function') {
                this._stateChangeCallbacks.push(callback);
            }
            return this; // Allow chaining
        },
        
        // Remove a state change callback
        offStateChange: function(callback) {
            var index = this._stateChangeCallbacks.indexOf(callback);
            if (index > -1) {
                this._stateChangeCallbacks.splice(index, 1);
            }
            return this;
        },
        
        // Internal: notify all state change listeners
        _notifyStateChange: function(newState, oldState, transitionData) {
            var self = this;
            this._stateChangeCallbacks.forEach(function(callback) {
                try {
                    callback.call(self, newState, oldState, transitionData);
                } catch (e) {
                    console.error('[AccountLifecycle] State change callback error:', e);
                }
            });
        },
        
        isValidState: function(state) {
            return Object.prototype.hasOwnProperty.call(this.STATES, state);
        },
        
        isState: function(state) {
            return this._currentState === state;
        },
        
        isTest: function() {
            return this.isState(this.STATES.TEST);
        },
        
        isLive: function() {
            return this.isState(this.STATES.LIVE_SELF_SERVICE) || 
                   this.isState(this.STATES.LIVE_SALES_OVERRIDE);
        },
        
        isSuspended: function() {
            return this.isState(this.STATES.SUSPENDED);
        },
        
        isClosed: function() {
            return this.isState(this.STATES.CLOSED);
        },
        
        isEnterprise: function() {
            return this.isState(this.STATES.LIVE_SALES_OVERRIDE);
        },
        
        getSuspensionReason: function() {
            return this._suspensionReason;
        },
        
        // =====================================================
        // CAPABILITY CHECKS
        // =====================================================
        
        canSendMessages: function() {
            var meta = this.getStateMeta();
            return meta ? meta.can_send_messages : false;
        },
        
        canUseApi: function() {
            var meta = this.getStateMeta();
            return meta ? meta.can_use_api : false;
        },
        
        canPurchase: function() {
            var meta = this.getStateMeta();
            return meta ? meta.can_purchase : false;
        },
        
        canAccessInbox: function() {
            var meta = this.getStateMeta();
            return meta ? meta.can_access_inbox : false;
        },
        
        getMaxDailyMessages: function() {
            var meta = this.getStateMeta();
            return meta ? meta.max_daily_messages : 0;
        },
        
        requiresApprovalFor: function(feature) {
            var meta = this.getStateMeta();
            if (!meta) return true;
            if (meta.requires_approval_for.indexOf('all') !== -1) return true;
            return meta.requires_approval_for.indexOf(feature) !== -1;
        },
        
        isReadOnly: function() {
            var meta = this.getStateMeta();
            return meta ? !!meta.read_only : false;
        },
        
        // =====================================================
        // STATE TRANSITIONS
        // =====================================================
        
        canTransitionTo: function(newState) {
            if (!this._currentState) return false;
            if (!this.isValidState(newState)) return false;
            
            var allowed = this.VALID_TRANSITIONS[this._currentState] || [];
            return allowed.indexOf(newState) !== -1;
        },
        
        getAvailableTransitions: function() {
            if (!this._currentState) return [];
            return this.VALID_TRANSITIONS[this._currentState] || [];
        },
        
        // Request state transition (mock - backend handles actual transition)
        requestTransition: function(newState, reason, callback) {
            var self = this;
            
            if (!this.canTransitionTo(newState)) {
                var error = {
                    success: false,
                    error: 'INVALID_TRANSITION',
                    message: 'Cannot transition from ' + this._currentState + ' to ' + newState
                };
                console.error('[AccountLifecycle] Invalid transition:', error.message);
                if (callback) callback(error);
                return;
            }
            
            var transitionData = {
                account_id: this._accountId,
                from_state: this._currentState,
                to_state: newState,
                reason: reason || 'No reason provided',
                requested_at: new Date().toISOString(),
                requested_by: 'current_user' // Backend fills actual user
            };
            
            console.log('[AccountLifecycle] Requesting transition:', transitionData);
            
            // Mock backend call
            // In production: POST /api/account/lifecycle/transition
            setTimeout(function() {
                var previousState = self._currentState;
                self._currentState = newState;
                self._stateChangedAt = new Date().toISOString();
                
                if (newState === self.STATES.SUSPENDED) {
                    self._suspensionReason = reason;
                } else {
                    self._suspensionReason = null;
                }
                
                // Log audit entry
                self._logTransition(previousState, newState, reason);
                
                // Emit event
                self._emitStateEvent('lifecycle:transitioned', {
                    from: previousState,
                    to: newState,
                    reason: reason,
                    meta: self.getStateMeta()
                });
                
                // Notify registered state change callbacks
                self._notifyStateChange(newState, previousState, {
                    reason: reason,
                    transitioned_at: self._stateChangedAt
                });
                
                var result = {
                    success: true,
                    from_state: previousState,
                    to_state: newState,
                    transitioned_at: self._stateChangedAt
                };
                
                console.log('[AccountLifecycle] Transition complete:', result);
                if (callback) callback(result);
            }, 500);
        },
        
        // =====================================================
        // ACCOUNT ACTIVATION (SELF-SERVICE)
        // =====================================================
        // Trigger: TEST → LIVE_SELF_SERVICE when:
        //   1. Required account details are complete
        //   2. A payment is successfully made
        // System Actions:
        //   - Remove Test Mode banner (via onStateChange)
        //   - Remove message disclaimer
        //   - Enable live SenderIDs
        //   - Lift fragment limits
        //   - Enable live API endpoints
        // =====================================================
        
        ACTIVATION_REQUIREMENTS: {
            account_details_complete: {
                label: 'Account Details Complete',
                description: 'Company name, address, website, sector, and VAT information'
            },
            payment_made: {
                label: 'First Payment Completed',
                description: 'Successfully processed a payment for messaging credits'
            }
        },
        
        _activationStatus: {
            account_details_complete: false,
            payment_made: false
        },
        
        // Set activation status (called by payment flow, account details page)
        setActivationStatus: function(key, value) {
            if (this.ACTIVATION_REQUIREMENTS.hasOwnProperty(key)) {
                this._activationStatus[key] = !!value;
                console.log('[AccountLifecycle] Activation status updated:', key, '=', value);
                
                // Check if ready for activation
                if (this.canActivate()) {
                    console.log('[AccountLifecycle] All activation requirements met - ready for activation');
                }
            }
        },
        
        // Check if all activation requirements are met
        canActivate: function() {
            if (!this.isTest()) return false; // Only TEST accounts can activate
            
            return this._activationStatus.account_details_complete && 
                   this._activationStatus.payment_made;
        },
        
        // Get activation readiness status
        getActivationStatus: function() {
            var self = this;
            var requirements = [];
            var complete = 0;
            
            Object.keys(this.ACTIVATION_REQUIREMENTS).forEach(function(key) {
                var req = self.ACTIVATION_REQUIREMENTS[key];
                var met = self._activationStatus[key];
                requirements.push({
                    key: key,
                    label: req.label,
                    description: req.description,
                    met: met
                });
                if (met) complete++;
            });
            
            return {
                ready: this.canActivate(),
                complete: complete,
                total: requirements.length,
                requirements: requirements
            };
        },
        
        // Activate account (TEST → LIVE_SELF_SERVICE)
        // Called after successful payment when all requirements are met
        activateAccount: function(paymentReference, callback) {
            var self = this;
            
            if (!this.isTest()) {
                var error = { 
                    success: false, 
                    error: 'NOT_TEST_ACCOUNT',
                    message: 'Only TEST accounts can be activated' 
                };
                if (callback) callback(error);
                return;
            }
            
            if (!this.canActivate()) {
                var status = this.getActivationStatus();
                var missingReqs = status.requirements.filter(function(r) { return !r.met; });
                var error = { 
                    success: false, 
                    error: 'REQUIREMENTS_NOT_MET',
                    message: 'Activation requirements not met',
                    missing: missingReqs
                };
                if (callback) callback(error);
                return;
            }
            
            var activationData = {
                account_id: this._accountId,
                from_state: this.STATES.TEST,
                to_state: this.STATES.LIVE_SELF_SERVICE,
                trigger: 'self_service_activation',
                payment_reference: paymentReference || null,
                activation_requirements: this._activationStatus,
                activated_at: new Date().toISOString()
            };
            
            console.log('[AccountLifecycle] Activating account:', activationData);
            
            // Atomic transition with logging
            // In production: POST /api/account/activate
            this.requestTransition(
                this.STATES.LIVE_SELF_SERVICE, 
                'Self-service activation after payment (Ref: ' + (paymentReference || 'N/A') + ')',
                function(result) {
                    if (result.success) {
                        // Log activation event
                        self._logActivation(activationData);
                        
                        // Update sessionStorage
                        sessionStorage.setItem('lifecycle_state', self.STATES.LIVE_SELF_SERVICE);
                        sessionStorage.removeItem('test_mode_fragments_used');
                        
                        // Emit activation event
                        self._emitStateEvent('lifecycle:activated', {
                            previous_state: self.STATES.TEST,
                            new_state: self.STATES.LIVE_SELF_SERVICE,
                            payment_reference: paymentReference,
                            activated_at: activationData.activated_at
                        });
                        
                        console.log('[AccountLifecycle] Account activated successfully');
                    }
                    
                    if (callback) callback(result);
                }
            );
        },
        
        // Log activation event (separate from transition log)
        _logActivation: function(activationData) {
            var entry = {
                id: 'ACTIVATION-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
                type: 'account_activation',
                account_id: activationData.account_id,
                from_state: activationData.from_state,
                to_state: activationData.to_state,
                trigger: activationData.trigger,
                payment_reference: activationData.payment_reference,
                requirements_met: activationData.activation_requirements,
                timestamp: activationData.activated_at,
                actor: 'self_service',
                ip_address: 'CAPTURED_BY_SERVER'
            };
            
            this._auditLog.push(entry);
            console.log('[AccountLifecycle] Activation audit:', entry);
            
            // TODO: Backend - POST /api/audit/account-activation
            return entry;
        },
        
        // Called after successful payment to trigger activation
        onPaymentSuccess: function(paymentReference, callback) {
            // Mark payment requirement as met
            this.setActivationStatus('payment_made', true);
            
            // If account details are also complete, activate immediately
            if (this.canActivate()) {
                this.activateAccount(paymentReference, callback);
            } else {
                // Payment made but still waiting for account details
                console.log('[AccountLifecycle] Payment recorded, awaiting account details completion');
                if (callback) callback({ 
                    success: true, 
                    activated: false,
                    message: 'Payment recorded. Complete account details to activate.'
                });
            }
        },
        
        // Called when account details are completed
        onAccountDetailsComplete: function(callback) {
            // Mark account details requirement as met
            this.setActivationStatus('account_details_complete', true);
            
            // If payment is also made, activate immediately
            if (this.canActivate()) {
                this.activateAccount(null, callback);
            } else {
                // Details complete but still waiting for payment
                console.log('[AccountLifecycle] Account details complete, awaiting first payment');
                if (callback) callback({
                    success: true,
                    activated: false,
                    message: 'Account details saved. Make a payment to activate.'
                });
            }
        },
        
        // =====================================================
        // AUDIT LOGGING
        // =====================================================
        
        _auditLog: [],
        
        _logTransition: function(fromState, toState, reason) {
            var entry = {
                id: 'LIFECYCLE-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
                account_id: this._accountId,
                event: 'state_transition',
                from_state: fromState,
                to_state: toState,
                reason: reason,
                timestamp: new Date().toISOString(),
                actor: 'current_user', // Backend fills actual user
                ip_address: 'CAPTURED_BY_SERVER'
            };
            
            this._auditLog.push(entry);
            console.log('[AccountLifecycle] Audit:', entry);
            
            // In production: POST /api/audit/account-lifecycle
            return entry;
        },
        
        getAuditLog: function() {
            return this._auditLog.slice();
        },
        
        // =====================================================
        // EVENT SYSTEM
        // =====================================================
        
        _listeners: {},
        
        on: function(event, callback) {
            if (!this._listeners[event]) {
                this._listeners[event] = [];
            }
            this._listeners[event].push(callback);
        },
        
        off: function(event, callback) {
            if (!this._listeners[event]) return;
            var idx = this._listeners[event].indexOf(callback);
            if (idx !== -1) {
                this._listeners[event].splice(idx, 1);
            }
        },
        
        _emitStateEvent: function(event, data) {
            var listeners = this._listeners[event] || [];
            listeners.forEach(function(cb) {
                try {
                    cb(data);
                } catch (e) {
                    console.error('[AccountLifecycle] Event handler error:', e);
                }
            });
            
            // Also dispatch DOM event for decoupled components
            if (typeof CustomEvent !== 'undefined') {
                document.dispatchEvent(new CustomEvent(event, { detail: data }));
            }
        },
        
        // =====================================================
        // UI HELPERS
        // =====================================================
        
        getBadgeHtml: function(state) {
            state = state || this._currentState;
            var meta = this.STATE_META[state];
            if (!meta) return '';
            
            return '<span class="badge ' + meta.badge_class + '">' +
                   '<i class="' + meta.icon + ' me-1"></i>' + meta.label +
                   '</span>';
        },
        
        getStatusIndicator: function() {
            var meta = this.getStateMeta();
            if (!meta) return '';
            
            return '<div class="lifecycle-status" style="display: inline-flex; align-items: center; gap: 8px;">' +
                   '<span class="status-dot" style="width: 10px; height: 10px; border-radius: 50%; background: ' + meta.color + ';"></span>' +
                   '<span class="status-label">' + meta.label + '</span>' +
                   '</div>';
        },
        
        // =====================================================
        // EXPORT FOR API/DEBUGGING
        // =====================================================
        
        exportState: function() {
            return {
                account_id: this._accountId,
                lifecycle_state: this._currentState,
                state_meta: this.getStateMeta(),
                suspension_reason: this._suspensionReason,
                state_changed_at: this._stateChangedAt,
                state_loaded_at: this._stateLoadedAt,
                capabilities: {
                    can_send_messages: this.canSendMessages(),
                    can_use_api: this.canUseApi(),
                    can_purchase: this.canPurchase(),
                    can_access_inbox: this.canAccessInbox(),
                    max_daily_messages: this.getMaxDailyMessages(),
                    is_read_only: this.isReadOnly()
                },
                available_transitions: this.getAvailableTransitions()
            };
        }
    };
    
    // Export to global scope
    global.AccountLifecycle = AccountLifecycle;
    
    // Also export STATES as constants for easy access
    global.ACCOUNT_STATES = AccountLifecycle.STATES;
    
})(typeof window !== 'undefined' ? window : this);

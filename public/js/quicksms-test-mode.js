/**
 * QuickSMS Test Mode Restrictions
 * 
 * Enforces messaging restrictions when account is in TEST state.
 * These restrictions apply to BOTH Portal sends and API sends.
 * 
 * Backend Integration:
 * - Server MUST enforce these same rules (frontend is UI guidance only)
 * - API returns structured errors: { error: 'TEST_MODE_RESTRICTION', code: 'TM_xxx', message: '...' }
 */

(function(global) {
    'use strict';

    var TestModeRestrictions = {
        
        // =====================================================
        // CONFIGURATION (NOT customer-configurable)
        // =====================================================
        
        CONFIG: {
            max_fragments: 100,              // Total fragments per account
            sender_id: 'QuickSMS Test',      // Fixed sender for TEST accounts
            sender_display: 'QuickSMS Test Sender',
            disclaimer_text: '[TEST] ',      // Prepended to all messages
            disclaimer_fragments: 1,         // Fragments consumed by disclaimer
            max_recipients_per_send: 1,      // No bulk sends
            allow_rcs_rich: false,           // No live RCS rich content
            allow_url_tracking: false,       // No URL tracking
            allow_bulk_upload: false,        // No bulk contact uploads
            api_mode: 'sandbox'              // Sandbox API only
        },
        
        // Error codes for structured responses
        ERROR_CODES: {
            TM_001: 'Recipient not in approved test list',
            TM_002: 'Multiple recipients not allowed in TEST mode',
            TM_003: 'Bulk upload not allowed in TEST mode',
            TM_004: 'Custom SenderID not allowed in TEST mode',
            TM_005: 'Rich RCS content not allowed in TEST mode',
            TM_006: 'URL tracking not allowed in TEST mode',
            TM_007: 'Message fragment limit exceeded',
            TM_008: 'Live API not available in TEST mode',
            TM_009: 'Account is in TEST mode'
        },
        
        // =====================================================
        // STATE
        // =====================================================
        
        _verifiedMobile: null,        // MFA verified number (447xxxxxxxxx)
        _approvedTestNumbers: [],     // Admin-approved test numbers
        _fragmentsUsed: 0,            // Fragments consumed so far
        _fragmentsRemaining: 100,     // Fragments remaining
        
        // =====================================================
        // INITIALIZATION
        // =====================================================
        
        init: function(options) {
            options = options || {};
            
            this._verifiedMobile = options.verified_mobile || null;
            this._approvedTestNumbers = options.approved_test_numbers || [];
            this._fragmentsUsed = options.fragments_used || 0;
            this._fragmentsRemaining = this.CONFIG.max_fragments - this._fragmentsUsed;
            
            console.log('[TestMode] Initialized:', {
                verified_mobile: this._maskNumber(this._verifiedMobile),
                approved_numbers: this._approvedTestNumbers.length,
                fragments_remaining: this._fragmentsRemaining
            });
            
            return this;
        },
        
        // =====================================================
        // VALIDATION CHECKS
        // =====================================================
        
        isTestMode: function() {
            if (typeof AccountLifecycle !== 'undefined') {
                return AccountLifecycle.isTest();
            }
            // Fallback: check session storage
            return sessionStorage.getItem('lifecycle_state') === 'TEST';
        },
        
        // Check if a recipient number is allowed
        isRecipientAllowed: function(number) {
            if (!this.isTestMode()) return { allowed: true };
            
            var normalized = this._normalizeNumber(number);
            
            // Check against verified MFA mobile
            if (normalized === this._verifiedMobile) {
                return { allowed: true, reason: 'verified_mfa_mobile' };
            }
            
            // Check against admin-approved test numbers
            if (this._approvedTestNumbers.indexOf(normalized) !== -1) {
                return { allowed: true, reason: 'admin_approved_test_number' };
            }
            
            return {
                allowed: false,
                error_code: 'TM_001',
                message: 'In TEST mode, you can only send to your verified mobile number or admin-approved test numbers.'
            };
        },
        
        // Validate a send request
        validateSendRequest: function(request) {
            if (!this.isTestMode()) {
                return { valid: true };
            }
            
            var errors = [];
            
            // Check recipient count
            var recipients = request.recipients || [];
            if (recipients.length > this.CONFIG.max_recipients_per_send) {
                errors.push({
                    code: 'TM_002',
                    message: this.ERROR_CODES.TM_002,
                    field: 'recipients'
                });
            }
            
            // Check each recipient is allowed
            for (var i = 0; i < recipients.length; i++) {
                var check = this.isRecipientAllowed(recipients[i]);
                if (!check.allowed) {
                    errors.push({
                        code: 'TM_001',
                        message: check.message,
                        field: 'recipients[' + i + ']',
                        value: this._maskNumber(recipients[i])
                    });
                }
            }
            
            // Check sender ID (must use TEST sender)
            if (request.sender_id && request.sender_id !== this.CONFIG.sender_id) {
                errors.push({
                    code: 'TM_004',
                    message: this.ERROR_CODES.TM_004 + ' Using: ' + this.CONFIG.sender_display,
                    field: 'sender_id'
                });
            }
            
            // Check RCS rich content
            if (request.rcs_rich_content && this.CONFIG.allow_rcs_rich === false) {
                errors.push({
                    code: 'TM_005',
                    message: this.ERROR_CODES.TM_005,
                    field: 'rcs_rich_content'
                });
            }
            
            // Check URL tracking
            if (request.track_urls && this.CONFIG.allow_url_tracking === false) {
                errors.push({
                    code: 'TM_006',
                    message: this.ERROR_CODES.TM_006,
                    field: 'track_urls'
                });
            }
            
            // Check fragment limit
            var fragmentsNeeded = this._calculateFragments(request.message);
            if (fragmentsNeeded > this._fragmentsRemaining) {
                errors.push({
                    code: 'TM_007',
                    message: 'Message requires ' + fragmentsNeeded + ' fragments, but only ' + 
                             this._fragmentsRemaining + ' remaining. Upgrade to LIVE to remove limits.',
                    field: 'message',
                    fragments_needed: fragmentsNeeded,
                    fragments_remaining: this._fragmentsRemaining
                });
            }
            
            if (errors.length > 0) {
                return {
                    valid: false,
                    error: 'TEST_MODE_RESTRICTION',
                    errors: errors
                };
            }
            
            return { 
                valid: true,
                applied_restrictions: {
                    sender_id: this.CONFIG.sender_id,
                    disclaimer_prepended: true,
                    fragments_will_use: fragmentsNeeded
                }
            };
        },
        
        // Validate bulk upload
        validateBulkUpload: function() {
            if (!this.isTestMode()) {
                return { allowed: true };
            }
            
            return {
                allowed: false,
                error_code: 'TM_003',
                message: this.ERROR_CODES.TM_003 + ' Upgrade to LIVE to enable bulk uploads.'
            };
        },
        
        // Validate API access
        validateApiAccess: function(endpoint) {
            if (!this.isTestMode()) {
                return { allowed: true, mode: 'live' };
            }
            
            // In TEST mode, only sandbox endpoints allowed
            var isSandbox = endpoint && endpoint.indexOf('/sandbox/') !== -1;
            
            if (!isSandbox) {
                return {
                    allowed: false,
                    error_code: 'TM_008',
                    message: this.ERROR_CODES.TM_008 + ' Use /api/sandbox/* endpoints instead.',
                    sandbox_equivalent: endpoint ? endpoint.replace('/api/', '/api/sandbox/') : null
                };
            }
            
            return { allowed: true, mode: 'sandbox' };
        },
        
        // =====================================================
        // MESSAGE TRANSFORMATION
        // =====================================================
        
        // Transform message for TEST mode sending
        transformMessage: function(message) {
            if (!this.isTestMode()) {
                return { message: message, transformed: false };
            }
            
            // Prepend disclaimer
            var transformedMessage = this.CONFIG.disclaimer_text + message;
            
            return {
                message: transformedMessage,
                transformed: true,
                disclaimer_added: this.CONFIG.disclaimer_text,
                original_message: message
            };
        },
        
        // Get the fixed sender ID for TEST mode
        getTestSenderId: function() {
            return this.CONFIG.sender_id;
        },
        
        // =====================================================
        // FRAGMENT TRACKING
        // =====================================================
        
        _calculateFragments: function(message) {
            if (!message) return 0;
            
            // Add disclaimer for TEST mode
            var fullMessage = this.CONFIG.disclaimer_text + message;
            var length = fullMessage.length;
            
            // Standard SMS fragment calculation (GSM-7)
            // 160 chars for single SMS, 153 chars per segment for concatenated
            if (length <= 160) {
                return 1;
            }
            return Math.ceil(length / 153);
        },
        
        getFragmentsRemaining: function() {
            return this._fragmentsRemaining;
        },
        
        getFragmentsUsed: function() {
            return this._fragmentsUsed;
        },
        
        recordFragmentsUsed: function(count) {
            this._fragmentsUsed += count;
            this._fragmentsRemaining = Math.max(0, this.CONFIG.max_fragments - this._fragmentsUsed);
            
            console.log('[TestMode] Fragments used:', count, 'Remaining:', this._fragmentsRemaining);
            
            // Persist to session
            sessionStorage.setItem('test_mode_fragments_used', this._fragmentsUsed);
            
            return {
                used: count,
                total_used: this._fragmentsUsed,
                remaining: this._fragmentsRemaining
            };
        },
        
        // =====================================================
        // TEST NUMBER MANAGEMENT
        // =====================================================
        
        setVerifiedMobile: function(number) {
            this._verifiedMobile = this._normalizeNumber(number);
            sessionStorage.setItem('test_mode_verified_mobile', this._verifiedMobile);
        },
        
        addApprovedTestNumber: function(number) {
            var normalized = this._normalizeNumber(number);
            if (this._approvedTestNumbers.indexOf(normalized) === -1) {
                this._approvedTestNumbers.push(normalized);
            }
        },
        
        getApprovedNumbers: function() {
            var all = this._approvedTestNumbers.slice();
            if (this._verifiedMobile) {
                all.unshift(this._verifiedMobile);
            }
            return all;
        },
        
        // =====================================================
        // UI HELPERS
        // =====================================================
        
        getRestrictionsBanner: function() {
            if (!this.isTestMode()) return '';
            
            return '<div class="alert alert-warning test-mode-banner mb-3">' +
                   '<div class="d-flex align-items-center">' +
                   '<i class="fas fa-flask me-3" style="font-size: 24px;"></i>' +
                   '<div>' +
                   '<strong>TEST MODE</strong>' +
                   '<p class="mb-0 small">Messages can only be sent to your verified mobile. ' +
                   'Sender shows as "' + this.CONFIG.sender_display + '". ' +
                   this._fragmentsRemaining + ' of ' + this.CONFIG.max_fragments + ' fragments remaining.</p>' +
                   '</div>' +
                   '</div>' +
                   '</div>';
        },
        
        getFragmentCounter: function() {
            if (!this.isTestMode()) return '';
            
            var percentage = (this._fragmentsRemaining / this.CONFIG.max_fragments) * 100;
            var colorClass = percentage > 50 ? 'bg-success' : (percentage > 20 ? 'bg-warning' : 'bg-danger');
            
            return '<div class="test-mode-fragments">' +
                   '<small class="text-muted">Test Fragments: ' + 
                   this._fragmentsRemaining + '/' + this.CONFIG.max_fragments + '</small>' +
                   '<div class="progress" style="height: 4px;">' +
                   '<div class="progress-bar ' + colorClass + '" style="width: ' + percentage + '%"></div>' +
                   '</div>' +
                   '</div>';
        },
        
        // Format error for display
        formatError: function(error) {
            if (!error || !error.errors) return '';
            
            var html = '<div class="alert alert-danger"><strong>TEST Mode Restrictions:</strong><ul class="mb-0 mt-2">';
            error.errors.forEach(function(err) {
                html += '<li>' + err.message + '</li>';
            });
            html += '</ul></div>';
            
            return html;
        },
        
        // =====================================================
        // UTILITIES
        // =====================================================
        
        _normalizeNumber: function(number) {
            if (!number) return null;
            var cleaned = String(number).replace(/[^0-9]/g, '');
            
            // Normalize UK mobile formats
            if (cleaned.startsWith('07') && cleaned.length === 11) {
                cleaned = '44' + cleaned.substring(1);
            } else if (cleaned.startsWith('447') && cleaned.length === 12) {
                // Already normalized
            } else if (cleaned.startsWith('44') && cleaned.length === 12) {
                // Already in format
            }
            
            return cleaned;
        },
        
        _maskNumber: function(number) {
            if (!number) return 'unknown';
            var str = String(number);
            if (str.length < 6) return str;
            return str.slice(0, 4) + '****' + str.slice(-2);
        },
        
        // =====================================================
        // EXPORT / DEBUG
        // =====================================================
        
        exportState: function() {
            return {
                is_test_mode: this.isTestMode(),
                config: this.CONFIG,
                verified_mobile: this._maskNumber(this._verifiedMobile),
                approved_test_numbers: this._approvedTestNumbers.map(this._maskNumber),
                fragments_used: this._fragmentsUsed,
                fragments_remaining: this._fragmentsRemaining,
                restrictions: {
                    max_recipients: this.CONFIG.max_recipients_per_send,
                    sender_id: this.CONFIG.sender_id,
                    rcs_rich_allowed: this.CONFIG.allow_rcs_rich,
                    url_tracking_allowed: this.CONFIG.allow_url_tracking,
                    bulk_upload_allowed: this.CONFIG.allow_bulk_upload,
                    api_mode: this.CONFIG.api_mode
                }
            };
        }
    };
    
    // Export to global scope
    global.TestModeRestrictions = TestModeRestrictions;
    
})(typeof window !== 'undefined' ? window : this);

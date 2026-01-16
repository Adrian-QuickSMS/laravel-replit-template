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
            // Full disclaimer appended to ALL test messages (cannot be edited/removed)
            disclaimer_text: '\n\nThis is a test message sent using QuickSMS.\nIf you were not expecting this message, please ignore it and do not click on any links or call any numbers.',
            disclaimer_char_count: 168,      // Pre-calculated for fragment math
            max_recipients_per_send: 1,      // No bulk sends
            allow_rcs_rich: false,           // No live RCS rich content
            allow_url_tracking: false,       // No URL tracking
            allow_bulk_upload: false,        // No bulk contact uploads
            api_mode: 'sandbox'              // Sandbox API only
        },
        
        // Error codes for structured responses
        ERROR_CODES: {
            TM_001: 'Test mode restricts sending to approved test numbers only.',
            TM_002: 'Multiple recipients not allowed in TEST mode',
            TM_003: 'Bulk upload not allowed in TEST mode',
            TM_004: 'Custom SenderID not allowed in TEST mode',
            TM_005: 'Rich RCS content not allowed in TEST mode',
            TM_006: 'URL tracking not allowed in TEST mode',
            TM_007: 'Message would exceed remaining fragment allowance',
            TM_008: 'Live API not available in TEST mode',
            TM_009: 'Account is in TEST mode',
            TM_010: "You've reached the test sending limit. Activate your account to continue."
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
        
        // Check if fragment limit is completely exhausted (hard block)
        // Server-side enforcement is authoritative - UI is informational only
        isLimitExhausted: function() {
            if (!this.isTestMode()) return false;
            return this._fragmentsRemaining <= 0;
        },
        
        // Check if a send would exceed the limit
        wouldExceedLimit: function(message) {
            if (!this.isTestMode()) return { exceeds: false };
            
            var fragmentsNeeded = this._calculateFragments(message);
            
            if (this._fragmentsRemaining <= 0) {
                return {
                    exceeds: true,
                    exhausted: true,
                    error_code: 'TM_010',
                    message: this.ERROR_CODES.TM_010,
                    fragments_needed: fragmentsNeeded,
                    fragments_remaining: 0
                };
            }
            
            if (fragmentsNeeded > this._fragmentsRemaining) {
                return {
                    exceeds: true,
                    exhausted: false,
                    error_code: 'TM_007',
                    message: this.ERROR_CODES.TM_007 + ' (Need: ' + fragmentsNeeded + ', Have: ' + this._fragmentsRemaining + ')',
                    fragments_needed: fragmentsNeeded,
                    fragments_remaining: this._fragmentsRemaining
                };
            }
            
            return {
                exceeds: false,
                fragments_needed: fragmentsNeeded,
                fragments_remaining: this._fragmentsRemaining - fragmentsNeeded
            };
        },
        
        // =====================================================
        // SENDERID RULES (TEST MODE)
        // =====================================================
        // Allowed:
        //   - Users may submit SenderID registration requests
        //   - Sales/Admin may assign SenderIDs for evaluation
        // Restricted:
        //   - SenderIDs are NOT presented to handsets in TEST mode
        //   - All sends use "QuickSMS Test Sender" + mandatory disclaimer
        // Status Handling:
        //   - SenderIDs requested in TEST appear as: Pending, Test Approved
        // Activation:
        //   - SenderIDs become usable ONLY once account is LIVE
        // Hard Rule:
        //   - No test message ever displays a customer SenderID
        // =====================================================
        
        // SenderID status constants for TEST mode
        SENDERID_STATUS: {
            PENDING: 'pending',             // Submitted, awaiting review
            TEST_APPROVED: 'test_approved', // Approved for evaluation only
            LIVE_APPROVED: 'live_approved', // Available for LIVE accounts
            REJECTED: 'rejected'
        },
        
        // Check if a SenderID can be used (always false in TEST mode for actual sending)
        canUseSenderId: function(senderId) {
            // In TEST mode, no customer SenderID is ever displayed to handsets
            if (this.isTestMode()) {
                return {
                    allowed: false,
                    reason: 'test_mode_restriction',
                    message: 'SenderIDs are not displayed to handsets in TEST mode. All messages use "' + 
                             this.CONFIG.sender_display + '".',
                    actual_sender: this.CONFIG.sender_display
                };
            }
            
            // In LIVE mode, check if SenderID is approved
            // TODO: Backend integration - check SenderID status
            return { allowed: true, reason: 'live_mode' };
        },
        
        // Get the effective sender for a message
        getEffectiveSender: function(requestedSenderId) {
            if (this.isTestMode()) {
                // Always override to test sender in TEST mode
                return {
                    sender: this.CONFIG.sender_display,
                    original_requested: requestedSenderId || null,
                    overridden: true,
                    reason: 'test_mode_enforced'
                };
            }
            
            // In LIVE mode, use requested SenderID (if approved)
            return {
                sender: requestedSenderId,
                original_requested: requestedSenderId,
                overridden: false,
                reason: 'live_mode'
            };
        },
        
        // Check if SenderID registration is allowed (yes, even in TEST mode)
        canRegisterSenderId: function() {
            // SenderID registration requests are allowed in all states
            // They just won't be usable until LIVE
            return {
                allowed: true,
                note: this.isTestMode() ? 
                    'SenderID will be available once account is activated to LIVE status.' :
                    'SenderID will be available once approved.'
            };
        },
        
        // Get SenderID display info for UI
        getSenderIdInfo: function() {
            if (this.isTestMode()) {
                return {
                    display_sender: this.CONFIG.sender_display,
                    custom_allowed: false,
                    registration_allowed: true,
                    info_message: 'In TEST mode, all messages display "' + this.CONFIG.sender_display + 
                                  '". Register SenderIDs now - they become active when your account goes LIVE.'
                };
            }
            
            return {
                display_sender: null, // User's choice
                custom_allowed: true,
                registration_allowed: true,
                info_message: null
            };
        },
        
        // Get message preview info strip HTML for SMS/RCS preview panels
        // Non-dismissible, pastel purple info style
        getMessagePreviewInfoStrip: function() {
            if (!this.isTestMode()) return '';
            
            return '<div class="test-mode-preview-strip" style="' +
                   'background: linear-gradient(135deg, #f3e8ff 0%, #ede9fe 100%); ' +
                   'border: 1px solid #c4b5fd; ' +
                   'border-radius: 8px; ' +
                   'padding: 12px 16px; ' +
                   'margin-bottom: 12px; ' +
                   'font-size: 13px;">' +
                   '<div style="display: flex; align-items: flex-start; gap: 10px;">' +
                   '<i class="fas fa-info-circle" style="color: #7c3aed; margin-top: 2px;"></i>' +
                   '<div>' +
                   '<strong style="color: #5b21b6; display: block; margin-bottom: 4px;">Test Mode</strong>' +
                   '<span style="color: #6b7280;">A safety disclaimer will be automatically appended to this message. ' +
                   'This may increase the number of message parts used.</span>' +
                   '</div>' +
                   '</div>' +
                   '</div>';
        },
        
        // Get info strip data for programmatic use
        getMessagePreviewInfo: function() {
            if (!this.isTestMode()) return null;
            
            return {
                visible: true,
                title: 'Test Mode',
                message: 'A safety disclaimer will be automatically appended to this message. This may increase the number of message parts used.',
                disclaimer_chars: this.CONFIG.disclaimer_char_count,
                style: 'info' // Not warning - informational
            };
        },
        
        // =====================================================
        // RECIPIENT VALIDATION
        // =====================================================
        
        // Check if a recipient number is allowed
        // Approved numbers: MFA verified mobile OR admin-approved test numbers
        // Test numbers are NOT self-service - managed via QuickSMS Admin Console only
        isRecipientAllowed: function(number) {
            if (!this.isTestMode()) return { allowed: true };
            
            var normalized = this._normalizeNumber(number);
            
            // Check against verified MFA mobile
            if (normalized === this._verifiedMobile) {
                return { allowed: true, reason: 'verified_mfa_mobile' };
            }
            
            // Check against admin-approved test numbers (managed via Admin Console)
            if (this._approvedTestNumbers.indexOf(normalized) !== -1) {
                return { allowed: true, reason: 'admin_approved_test_number' };
            }
            
            return {
                allowed: false,
                error_code: 'TM_001',
                message: this.ERROR_CODES.TM_001
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
            
            // Check fragment limit (hard block when exhausted)
            // Server-side is authoritative - cannot be bypassed by refresh, API, or retries
            var limitCheck = this.wouldExceedLimit(request.message);
            if (limitCheck.exceeds) {
                errors.push({
                    code: limitCheck.error_code,
                    message: limitCheck.exhausted ? 
                        this.ERROR_CODES.TM_010 :
                        'Message requires ' + limitCheck.fragments_needed + ' fragments, but only ' + 
                        limitCheck.fragments_remaining + ' remaining.',
                    field: 'message',
                    fragments_needed: limitCheck.fragments_needed,
                    fragments_remaining: limitCheck.fragments_remaining,
                    limit_exhausted: limitCheck.exhausted
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
        // Disclaimer is APPENDED at send-time, never stored in user content
        transformMessage: function(message) {
            if (!this.isTestMode()) {
                return { message: message, transformed: false };
            }
            
            // Append disclaimer (cannot be edited or removed)
            var transformedMessage = message + this.CONFIG.disclaimer_text;
            
            return {
                message: transformedMessage,
                transformed: true,
                disclaimer_appended: true,
                disclaimer_text: this.CONFIG.disclaimer_text,
                disclaimer_char_count: this.CONFIG.disclaimer_char_count,
                original_message: message,
                original_length: message.length,
                total_length: transformedMessage.length
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
            
            // Add disclaimer for TEST mode (appended at send-time)
            var fullMessage = message + this.CONFIG.disclaimer_text;
            var length = fullMessage.length;
            
            // Standard SMS fragment calculation (GSM-7)
            // 160 chars for single SMS, 153 chars per segment for concatenated
            if (length <= 160) {
                return 1;
            }
            return Math.ceil(length / 153);
        },
        
        // Calculate fragments with breakdown for UI display
        calculateFragmentsWithBreakdown: function(message) {
            var userLength = message ? message.length : 0;
            var disclaimerLength = this.CONFIG.disclaimer_char_count;
            var totalLength = userLength + disclaimerLength;
            var fragments = this._calculateFragments(message);
            
            return {
                user_content_length: userLength,
                disclaimer_length: disclaimerLength,
                total_length: totalLength,
                fragments: fragments,
                chars_remaining_in_fragment: fragments === 1 ? 
                    (160 - totalLength) : 
                    ((fragments * 153) - totalLength)
            };
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
            
            // Show exhausted banner if limit reached
            if (this.isLimitExhausted()) {
                return '<div class="alert alert-danger test-mode-banner mb-3">' +
                       '<div class="d-flex align-items-center">' +
                       '<i class="fas fa-ban me-3" style="font-size: 24px;"></i>' +
                       '<div>' +
                       '<strong>TEST LIMIT REACHED</strong>' +
                       '<p class="mb-0 small">' + this.ERROR_CODES.TM_010 + '</p>' +
                       '</div>' +
                       '</div>' +
                       '</div>';
            }
            
            return '<div class="alert alert-warning test-mode-banner mb-3">' +
                   '<div class="d-flex align-items-center">' +
                   '<i class="fas fa-flask me-3" style="font-size: 24px;"></i>' +
                   '<div>' +
                   '<strong>TEST MODE</strong>' +
                   '<p class="mb-1 small">Messages can only be sent to your verified mobile. ' +
                   'Sender shows as "' + this.CONFIG.sender_display + '". ' +
                   this._fragmentsRemaining + ' of ' + this.CONFIG.max_fragments + ' fragments remaining.</p>' +
                   '<p class="mb-0 small text-muted"><i class="fas fa-info-circle me-1"></i>' +
                   'A disclaimer (' + this.CONFIG.disclaimer_char_count + ' chars) will be appended to all messages.</p>' +
                   '</div>' +
                   '</div>' +
                   '</div>';
        },
        
        // Get disclaimer preview for message compose UI
        getDisclaimerPreview: function() {
            if (!this.isTestMode()) return '';
            
            return '<div class="disclaimer-preview mt-2 p-2 bg-light border rounded small">' +
                   '<strong class="text-muted">Disclaimer (auto-appended):</strong>' +
                   '<div class="text-muted" style="white-space: pre-wrap;">' + 
                   this.CONFIG.disclaimer_text.trim() + '</div>' +
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
        // PAYMENT GATING (TEST MODE)
        // =====================================================
        // Users cannot make purchases until required account details are complete.
        // Required fields: Company name, Registered address, Primary website, Sector, VAT details
        // =====================================================
        
        REQUIRED_ACCOUNT_FIELDS: {
            company_name: {
                label: 'Company Name',
                section: 'Company Information',
                placeholder: 'Enter your legal company name',
                type: 'text',
                required: true
            },
            registered_address: {
                label: 'Registered Address',
                section: 'Company Information',
                placeholder: 'Enter your registered business address',
                type: 'textarea',
                required: true
            },
            primary_website: {
                label: 'Primary Website',
                section: 'Company Information',
                placeholder: 'https://www.example.com',
                type: 'url',
                required: true
            },
            sector: {
                label: 'Business Sector',
                section: 'Company Information',
                placeholder: 'Select your sector',
                type: 'select',
                options: [
                    'Financial Services',
                    'Healthcare',
                    'Retail & E-commerce',
                    'Technology',
                    'Education',
                    'Government & Public Sector',
                    'Hospitality & Travel',
                    'Manufacturing',
                    'Professional Services',
                    'Telecommunications',
                    'Other'
                ],
                required: true
            },
            vat_registered: {
                label: 'VAT Registered',
                section: 'VAT & Tax Information',
                type: 'boolean',
                required: true
            },
            vat_number: {
                label: 'VAT Number',
                section: 'VAT & Tax Information',
                placeholder: 'GB123456789',
                type: 'text',
                required: false, // Required only if vat_registered = true
                conditional: 'vat_registered'
            }
        },
        
        // Account details storage (mock - in production from backend)
        _accountDetails: {},
        
        // Set account details (called on page load from backend data)
        setAccountDetails: function(details) {
            this._accountDetails = details || {};
        },
        
        // Check if all required fields are complete
        arePaymentRequirementsMet: function() {
            var missing = this.getMissingPaymentFields();
            return missing.length === 0;
        },
        
        // Get list of missing required fields
        getMissingPaymentFields: function() {
            var self = this;
            var missing = [];
            
            Object.keys(this.REQUIRED_ACCOUNT_FIELDS).forEach(function(fieldKey) {
                var field = self.REQUIRED_ACCOUNT_FIELDS[fieldKey];
                
                // Check conditional fields
                if (field.conditional) {
                    var conditionMet = self._accountDetails[field.conditional] === true;
                    if (!conditionMet) return; // Skip if condition not met
                }
                
                if (field.required || field.conditional) {
                    var value = self._accountDetails[fieldKey];
                    if (!value || (typeof value === 'string' && value.trim() === '')) {
                        missing.push({
                            key: fieldKey,
                            label: field.label,
                            section: field.section,
                            type: field.type,
                            placeholder: field.placeholder,
                            options: field.options
                        });
                    }
                }
            });
            
            return missing;
        },
        
        // Get completion status summary
        getPaymentReadinessStatus: function() {
            var total = Object.keys(this.REQUIRED_ACCOUNT_FIELDS).filter(function(k) {
                return this.REQUIRED_ACCOUNT_FIELDS[k].required;
            }.bind(this)).length;
            
            var missing = this.getMissingPaymentFields();
            var complete = total - missing.length;
            
            return {
                complete: complete,
                total: total,
                percentage: Math.round((complete / total) * 100),
                missing: missing,
                ready: missing.length === 0
            };
        },
        
        // Handle purchase attempt - returns true if allowed, false if blocked
        attemptPurchase: function(callback) {
            if (this.arePaymentRequirementsMet()) {
                // All requirements met, proceed with purchase
                if (callback) callback({ allowed: true });
                return true;
            }
            
            // Show modal with missing fields
            this.showPaymentGatingModal(callback);
            return false;
        },
        
        // Show payment gating modal with missing fields
        showPaymentGatingModal: function(onComplete) {
            var self = this;
            var missing = this.getMissingPaymentFields();
            var status = this.getPaymentReadinessStatus();
            
            // Build modal HTML
            var modalHtml = 
                '<div class="modal fade" id="paymentGatingModal" tabindex="-1" data-bs-backdrop="static">' +
                '<div class="modal-dialog modal-lg">' +
                '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #f3e8ff 0%, #ede9fe 100%); border-bottom: 1px solid #c4b5fd;">' +
                '<div class="d-flex align-items-center">' +
                '<i class="fas fa-clipboard-check me-3" style="font-size: 24px; color: #7c3aed;"></i>' +
                '<div>' +
                '<h5 class="modal-title mb-0" style="color: #5b21b6;">Complete Your Account Details</h5>' +
                '<small class="text-muted">Required before you can make purchases</small>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="modal-body">' +
                '<div class="mb-4">' +
                '<div class="d-flex justify-content-between align-items-center mb-2">' +
                '<span class="text-muted">Completion Progress</span>' +
                '<span class="badge bg-primary">' + status.complete + ' of ' + status.total + ' complete</span>' +
                '</div>' +
                '<div class="progress" style="height: 8px;">' +
                '<div class="progress-bar bg-primary" style="width: ' + status.percentage + '%"></div>' +
                '</div>' +
                '</div>' +
                '<form id="paymentGatingForm">' +
                this._buildMissingFieldsForm(missing) +
                '</form>' +
                '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>' +
                '<button type="button" class="btn btn-primary" id="savePaymentDetailsBtn" disabled>' +
                '<i class="fas fa-save me-1"></i> Save & Continue to Payment' +
                '</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            // Remove existing modal if present
            var existingModal = document.getElementById('paymentGatingModal');
            if (existingModal) existingModal.remove();
            
            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            var modalEl = document.getElementById('paymentGatingModal');
            var modal = new bootstrap.Modal(modalEl);
            
            // Form validation and save handling
            var form = document.getElementById('paymentGatingForm');
            var saveBtn = document.getElementById('savePaymentDetailsBtn');
            
            // Validate on input
            form.addEventListener('input', function() {
                var isValid = self._validatePaymentForm(form, missing);
                saveBtn.disabled = !isValid;
            });
            
            // Save handler
            saveBtn.addEventListener('click', function() {
                var formData = self._collectFormData(form, missing);
                
                // Update local account details
                Object.assign(self._accountDetails, formData);
                
                // Save to sessionStorage for persistence
                sessionStorage.setItem('account_details', JSON.stringify(self._accountDetails));
                
                // Log for backend integration
                console.log('[PaymentGating] Account details updated:', formData);
                
                // TODO: Backend integration - POST /api/account/details
                
                modal.hide();
                
                // Check if now complete
                if (self.arePaymentRequirementsMet()) {
                    if (onComplete) onComplete({ allowed: true, details_updated: true });
                } else {
                    // Still missing fields (shouldn't happen)
                    if (onComplete) onComplete({ allowed: false, missing: self.getMissingPaymentFields() });
                }
            });
            
            // Clean up on hide
            modalEl.addEventListener('hidden.bs.modal', function() {
                modalEl.remove();
            });
            
            modal.show();
        },
        
        // Build form fields for missing items
        _buildMissingFieldsForm: function(missingFields) {
            var self = this;
            var html = '<div class="row g-3">';
            
            // Group by section
            var sections = {};
            missingFields.forEach(function(field) {
                if (!sections[field.section]) sections[field.section] = [];
                sections[field.section].push(field);
            });
            
            Object.keys(sections).forEach(function(sectionName) {
                html += '<div class="col-12"><h6 class="text-muted border-bottom pb-2 mb-3">' + 
                        '<i class="fas fa-folder-open me-2"></i>' + sectionName + '</h6></div>';
                
                sections[sectionName].forEach(function(field) {
                    html += '<div class="col-md-6">';
                    html += '<label class="form-label">' + field.label + ' <span class="text-danger">*</span></label>';
                    
                    if (field.type === 'select') {
                        html += '<select class="form-select" name="' + field.key + '" required>';
                        html += '<option value="">Select...</option>';
                        (field.options || []).forEach(function(opt) {
                            html += '<option value="' + opt + '">' + opt + '</option>';
                        });
                        html += '</select>';
                    } else if (field.type === 'textarea') {
                        html += '<textarea class="form-control" name="' + field.key + '" rows="2" ' +
                                'placeholder="' + (field.placeholder || '') + '" required></textarea>';
                    } else if (field.type === 'boolean') {
                        html += '<div class="form-check form-switch mt-2">' +
                                '<input class="form-check-input" type="checkbox" name="' + field.key + '" id="field_' + field.key + '">' +
                                '<label class="form-check-label" for="field_' + field.key + '">Yes</label>' +
                                '</div>';
                    } else {
                        html += '<input type="' + (field.type || 'text') + '" class="form-control" name="' + field.key + '" ' +
                                'placeholder="' + (field.placeholder || '') + '" required>';
                    }
                    
                    html += '</div>';
                });
            });
            
            html += '</div>';
            return html;
        },
        
        // Validate payment form
        _validatePaymentForm: function(form, missingFields) {
            var isValid = true;
            
            missingFields.forEach(function(field) {
                var input = form.querySelector('[name="' + field.key + '"]');
                if (!input) return;
                
                if (field.type === 'boolean') {
                    // Booleans are always valid (can be true or false)
                    return;
                }
                
                var value = input.value.trim();
                if (!value) {
                    isValid = false;
                }
                
                // URL validation
                if (field.type === 'url' && value) {
                    try {
                        new URL(value);
                    } catch (e) {
                        isValid = false;
                    }
                }
            });
            
            return isValid;
        },
        
        // Collect form data
        _collectFormData: function(form, missingFields) {
            var data = {};
            
            missingFields.forEach(function(field) {
                var input = form.querySelector('[name="' + field.key + '"]');
                if (!input) return;
                
                if (field.type === 'boolean') {
                    data[field.key] = input.checked;
                } else {
                    data[field.key] = input.value.trim();
                }
            });
            
            return data;
        },
        
        // Get purchase button state (for disabling buttons)
        getPurchaseButtonState: function() {
            if (this.arePaymentRequirementsMet()) {
                return {
                    enabled: true,
                    tooltip: null
                };
            }
            
            var missing = this.getMissingPaymentFields();
            return {
                enabled: false,
                tooltip: 'Complete ' + missing.length + ' required field(s) in Account Details before purchasing',
                missing_count: missing.length
            };
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

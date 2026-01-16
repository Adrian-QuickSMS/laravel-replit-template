var SenderCapability = (function() {
    'use strict';

    var CAPABILITY_LEVELS = {
        'advanced': {
            label: 'Advanced Sender',
            description: 'Full content creation capabilities',
            permissions: {
                freeFormSms: true,
                freeFormRcs: true,
                fullContactBook: true,
                csvUpload: true,
                adHocNumbers: true,
                richRcsMedia: true,
                templateCreation: true,
                freeTextEditing: true,
                useTemplates: true,
                usePredefinedLists: true
            }
        },
        'restricted': {
            label: 'Restricted Sender',
            description: 'Templates and predefined lists only',
            permissions: {
                freeFormSms: false,
                freeFormRcs: false,
                fullContactBook: false,
                csvUpload: false,
                adHocNumbers: false,
                richRcsMedia: false,
                templateCreation: false,
                freeTextEditing: false,
                useTemplates: true,
                usePredefinedLists: true
            }
        }
    };

    var MESSAGING_ROLES = ['owner', 'admin', 'messaging-manager', 'campaign-approver'];

    var currentCapability = null;
    var currentRole = null;

    function init(capability, role) {
        currentCapability = capability || 'restricted';
        currentRole = role || 'auditor';
        
        if (hasMessagingAccess()) {
            applyCapabilityRestrictions();
        }
        
        console.log('[SenderCapability] Initialized:', { capability: currentCapability, role: currentRole });
    }

    function hasMessagingAccess() {
        return MESSAGING_ROLES.includes(currentRole);
    }

    function getCapabilityLevel() {
        return currentCapability;
    }

    function getCapabilityDefinition(level) {
        return CAPABILITY_LEVELS[level] || null;
    }

    function can(permission) {
        if (!hasMessagingAccess()) {
            return false;
        }
        
        var capDef = CAPABILITY_LEVELS[currentCapability];
        if (!capDef) {
            return false;
        }
        
        return capDef.permissions[permission] === true;
    }

    function applyCapabilityRestrictions() {
        var isRestricted = currentCapability === 'restricted';
        
        document.querySelectorAll('[data-capability="advanced"]').forEach(function(el) {
            if (isRestricted) {
                el.classList.add('capability-hidden');
                el.style.display = 'none';
            } else {
                el.classList.remove('capability-hidden');
                el.style.display = '';
            }
        });

        document.querySelectorAll('[data-capability-disable="advanced"]').forEach(function(el) {
            if (isRestricted) {
                el.classList.add('capability-disabled');
                el.disabled = true;
                el.setAttribute('title', 'This feature requires Advanced Sender capability');
            } else {
                el.classList.remove('capability-disabled');
                el.disabled = false;
                el.removeAttribute('title');
            }
        });

        if (isRestricted) {
            document.querySelectorAll('[data-capability="free-text"]').forEach(function(el) {
                el.setAttribute('readonly', 'readonly');
                el.classList.add('capability-readonly');
            });

            document.querySelectorAll('[data-capability="csv-upload"]').forEach(function(el) {
                el.style.display = 'none';
            });

            document.querySelectorAll('[data-capability="contact-book-full"]').forEach(function(el) {
                el.style.display = 'none';
            });

            document.querySelectorAll('[data-capability="template-create"]').forEach(function(el) {
                el.style.display = 'none';
            });

            document.querySelectorAll('[data-capability="rich-media"]').forEach(function(el) {
                el.style.display = 'none';
            });
        } else {
            document.querySelectorAll('[data-capability="free-text"]').forEach(function(el) {
                el.removeAttribute('readonly');
                el.classList.remove('capability-readonly');
            });

            document.querySelectorAll('[data-capability="csv-upload"], [data-capability="contact-book-full"], [data-capability="template-create"], [data-capability="rich-media"]').forEach(function(el) {
                el.style.display = '';
            });
        }

        showCapabilityIndicator();
    }

    function showCapabilityIndicator() {
        var existingIndicator = document.getElementById('sender-capability-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }

        if (!hasMessagingAccess()) {
            return;
        }

        var capDef = CAPABILITY_LEVELS[currentCapability];
        if (!capDef) {
            return;
        }

        var indicator = document.createElement('div');
        indicator.id = 'sender-capability-indicator';
        indicator.className = 'sender-capability-badge';
        indicator.style.cssText = 'position: fixed; bottom: 20px; right: 20px; padding: 8px 12px; border-radius: 6px; font-size: 0.75rem; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.15);';
        
        if (currentCapability === 'advanced') {
            indicator.style.background = 'linear-gradient(135deg, #886cc0 0%, #a78bfa 100%)';
            indicator.style.color = '#fff';
            indicator.innerHTML = '<i class="fas fa-star me-1"></i> Advanced Sender';
        } else {
            indicator.style.background = '#f3f4f6';
            indicator.style.color = '#6b7280';
            indicator.style.border = '1px solid #e5e7eb';
            indicator.innerHTML = '<i class="fas fa-lock me-1"></i> Restricted Sender';
        }

        var messagingPages = ['/messages', '/contacts', '/management/email-to-sms'];
        var isMessagingPage = messagingPages.some(function(page) {
            return window.location.pathname.includes(page);
        });

        if (isMessagingPage) {
            document.body.appendChild(indicator);
        }
    }

    function enforceOnSendMessage() {
        if (!hasMessagingAccess()) {
            return { allowed: false, reason: 'No messaging access' };
        }

        if (currentCapability === 'restricted') {
            var messageContent = document.getElementById('message-content');
            var selectedTemplate = document.getElementById('selected-template');
            
            if (messageContent && !selectedTemplate) {
                return { 
                    allowed: false, 
                    reason: 'Restricted Senders must use templates. Please select a template.',
                    enforcement: 'template_required'
                };
            }

            var recipientSource = document.getElementById('recipient-source');
            if (recipientSource && recipientSource.value === 'csv') {
                return { 
                    allowed: false, 
                    reason: 'Restricted Senders cannot use CSV uploads. Please use a predefined list.',
                    enforcement: 'no_csv'
                };
            }

            if (recipientSource && recipientSource.value === 'ad-hoc') {
                return { 
                    allowed: false, 
                    reason: 'Restricted Senders cannot use ad-hoc numbers. Please use a predefined list.',
                    enforcement: 'no_adhoc'
                };
            }
        }

        return { allowed: true };
    }

    function getCapabilityComparison() {
        return {
            headers: ['Feature', 'Advanced', 'Restricted'],
            rows: [
                ['Free-form SMS composition', true, false],
                ['Free-form RCS composition', true, false],
                ['Full Contact Book access', true, false],
                ['CSV recipient upload', true, false],
                ['Ad-hoc number entry', true, false],
                ['Rich RCS media upload', true, false],
                ['Template creation', true, false],
                ['Free-text editing', true, false],
                ['Use message templates', true, true],
                ['Use predefined lists', true, true]
            ]
        };
    }

    function changeCapability(userId, newCapability, changedBy, reason) {
        var oldCapability = currentCapability;
        
        if (!CAPABILITY_LEVELS[newCapability]) {
            return { success: false, error: 'Invalid capability level' };
        }

        var auditEntry = {
            action: 'SENDER_CAPABILITY_CHANGED',
            userId: userId,
            previousCapability: oldCapability,
            newCapability: newCapability,
            changedBy: changedBy,
            reason: reason || null,
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100'
        };

        console.log('[AUDIT] Sender capability changed:', auditEntry);

        return {
            success: true,
            auditEntry: auditEntry,
            previousCapability: oldCapability,
            newCapability: newCapability
        };
    }

    return {
        init: init,
        can: can,
        hasMessagingAccess: hasMessagingAccess,
        getCapabilityLevel: getCapabilityLevel,
        getCapabilityDefinition: getCapabilityDefinition,
        enforceOnSendMessage: enforceOnSendMessage,
        getCapabilityComparison: getCapabilityComparison,
        changeCapability: changeCapability,
        applyCapabilityRestrictions: applyCapabilityRestrictions,
        LEVELS: CAPABILITY_LEVELS,
        MESSAGING_ROLES: MESSAGING_ROLES
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = SenderCapability;
}

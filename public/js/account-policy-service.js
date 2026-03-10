/**
 * Account Policy Service
 * Centralized source of truth for MFA Policy and IP Allowlist
 * Applies account-wide to all users across all sub-accounts (v1 - no per-sub-account overrides)
 * 
 * TODO: Replace mock data with actual API calls to backend
 */
var AccountPolicyService = (function() {
    'use strict';
    
    // Centralized policy state - single source of truth
    var _policy = {
        // MFA Policy
        mfa_required: true,
        mfa_methods: {
            authenticator: true,
            sms_rcs: true
        },
        
        // IP Allowlist Policy
        ip_allowlist_enabled: false,
        ip_allowlist: [
            { ip: '192.168.1.0/24', label: 'Office Network', created_by: 'Sarah Mitchell', created_date: '15-01-2026', status: 'active' },
            { ip: '10.0.0.1', label: 'VPN Gateway', created_by: 'John Smith', created_date: '10-01-2026', status: 'active' }
        ],
        
        // Metadata
        last_updated: new Date().toISOString(),
        updated_by: 'Sarah Mitchell'
    };
    
    // Event listeners for policy changes
    var _listeners = [];
    
    function notifyListeners(changeType, oldValue, newValue) {
        _listeners.forEach(function(listener) {
            try {
                listener(changeType, oldValue, newValue);
            } catch (e) {
                console.error('[AccountPolicyService] Listener error:', e);
            }
        });
    }
    
    // IP matching utilities
    function validateIPv4(ip) {
        var parts = ip.split('.');
        if (parts.length !== 4) return false;
        for (var i = 0; i < 4; i++) {
            var num = parseInt(parts[i], 10);
            if (isNaN(num) || num < 0 || num > 255 || parts[i] !== String(num)) return false;
        }
        return true;
    }
    
    function ipToNum(ip) {
        var parts = ip.split('.');
        return ((parseInt(parts[0]) << 24) | 
                (parseInt(parts[1]) << 16) | 
                (parseInt(parts[2]) << 8) | 
                parseInt(parts[3])) >>> 0;
    }
    
    function matchCIDR(ip, cidr) {
        var parts = cidr.split('/');
        var baseIP = parts[0];
        var prefix = parseInt(parts[1], 10);
        
        var ipNum = ipToNum(ip);
        var baseNum = ipToNum(baseIP);
        var mask = ~((1 << (32 - prefix)) - 1);
        
        return (ipNum & mask) === (baseNum & mask);
    }
    
    function matchIP(clientIP, allowedIP) {
        if (allowedIP.includes('/')) {
            return matchCIDR(clientIP, allowedIP);
        }
        return clientIP === allowedIP;
    }
    
    return {
        // Get current policy state (read-only copy)
        getPolicy: function() {
            return JSON.parse(JSON.stringify(_policy));
        },
        
        // MFA Policy getters
        isMfaRequired: function() {
            return _policy.mfa_required;
        },
        
        getMfaMethods: function() {
            return {
                authenticator: _policy.mfa_methods.authenticator,
                sms_rcs: _policy.mfa_methods.sms_rcs
            };
        },
        
        // MFA Policy setters
        setMfaRequired: function(value) {
            var oldValue = _policy.mfa_required;
            _policy.mfa_required = value;
            _policy.last_updated = new Date().toISOString();
            notifyListeners('mfa_required', oldValue, value);
            console.log('[AccountPolicyService] MFA required changed:', oldValue, '->', value);
            return { success: true, old_value: oldValue, new_value: value };
        },
        
        setMfaMethod: function(method, enabled) {
            if (!_policy.mfa_methods.hasOwnProperty(method)) {
                return { success: false, error: 'Invalid method' };
            }
            var oldValue = _policy.mfa_methods[method];
            _policy.mfa_methods[method] = enabled;
            _policy.last_updated = new Date().toISOString();
            notifyListeners('mfa_method_' + method, oldValue, enabled);
            console.log('[AccountPolicyService] MFA method', method, 'changed:', oldValue, '->', enabled);
            return { success: true, old_value: oldValue, new_value: enabled };
        },
        
        // Check MFA policy compliance for a user
        checkMfaCompliance: function(user) {
            var userHasAuthenticator = user.totp_enabled;
            var userHasSmsRcs = user.mobile && user.mfa_enabled;
            
            var hasAllowedMethod = false;
            if (_policy.mfa_methods.authenticator && userHasAuthenticator) {
                hasAllowedMethod = true;
            }
            if (_policy.mfa_methods.sms_rcs && userHasSmsRcs) {
                hasAllowedMethod = true;
            }
            
            return {
                compliant: hasAllowedMethod,
                has_authenticator: userHasAuthenticator,
                has_sms_rcs: userHasSmsRcs,
                allowed_authenticator: _policy.mfa_methods.authenticator,
                allowed_sms_rcs: _policy.mfa_methods.sms_rcs
            };
        },
        
        // IP Allowlist getters
        isIpAllowlistEnabled: function() {
            return _policy.ip_allowlist_enabled;
        },
        
        getIpAllowlist: function() {
            return JSON.parse(JSON.stringify(_policy.ip_allowlist));
        },
        
        // IP Allowlist setters
        setIpAllowlistEnabled: function(value) {
            if (value && _policy.ip_allowlist.length === 0) {
                return { success: false, error: 'Cannot enable with empty allowlist' };
            }
            var oldValue = _policy.ip_allowlist_enabled;
            _policy.ip_allowlist_enabled = value;
            _policy.last_updated = new Date().toISOString();
            notifyListeners('ip_allowlist_enabled', oldValue, value);
            console.log('[AccountPolicyService] IP allowlist enabled changed:', oldValue, '->', value);
            return { success: true, old_value: oldValue, new_value: value };
        },
        
        addIpEntry: function(entry) {
            _policy.ip_allowlist.push(entry);
            _policy.last_updated = new Date().toISOString();
            notifyListeners('ip_entry_added', null, entry);
            console.log('[AccountPolicyService] IP entry added:', entry);
            return { success: true, entry: entry };
        },
        
        updateIpEntry: function(index, entry) {
            if (index < 0 || index >= _policy.ip_allowlist.length) {
                return { success: false, error: 'Invalid index' };
            }
            var oldEntry = _policy.ip_allowlist[index];
            _policy.ip_allowlist[index] = entry;
            _policy.last_updated = new Date().toISOString();
            notifyListeners('ip_entry_updated', oldEntry, entry);
            console.log('[AccountPolicyService] IP entry updated:', oldEntry, '->', entry);
            return { success: true, old_entry: oldEntry, new_entry: entry };
        },
        
        removeIpEntry: function(index) {
            if (index < 0 || index >= _policy.ip_allowlist.length) {
                return { success: false, error: 'Invalid index' };
            }
            var removedEntry = _policy.ip_allowlist.splice(index, 1)[0];
            _policy.last_updated = new Date().toISOString();
            notifyListeners('ip_entry_removed', removedEntry, null);
            console.log('[AccountPolicyService] IP entry removed:', removedEntry);
            return { success: true, removed_entry: removedEntry };
        },
        
        // Check if IP is allowed
        isIpAllowed: function(clientIP) {
            if (!_policy.ip_allowlist_enabled) {
                return { allowed: true, reason: 'policy_disabled' };
            }
            
            if (_policy.ip_allowlist.length === 0) {
                return { allowed: true, reason: 'empty_allowlist' };
            }
            
            var allowed = _policy.ip_allowlist.some(function(entry) {
                return matchIP(clientIP, entry.ip);
            });
            
            return {
                allowed: allowed,
                reason: allowed ? 'ip_in_allowlist' : 'IP_BLOCKED'
            };
        },
        
        // IP Validation
        validateIpEntry: function(ipStr, excludeIndex) {
            ipStr = ipStr.trim();
            
            if (ipStr === '0.0.0.0/0') {
                return { valid: false, error: 'Cannot add 0.0.0.0/0 - this would allow all IPs' };
            }
            
            var hasCidr = ipStr.includes('/');
            var ip, prefix;
            
            if (hasCidr) {
                var parts = ipStr.split('/');
                if (parts.length !== 2) {
                    return { valid: false, error: 'Invalid CIDR format' };
                }
                ip = parts[0];
                prefix = parseInt(parts[1], 10);
                
                if (isNaN(prefix) || prefix < 8 || prefix > 32) {
                    return { valid: false, error: 'CIDR prefix must be between /8 and /32' };
                }
            } else {
                ip = ipStr;
            }
            
            if (!validateIPv4(ip)) {
                return { valid: false, error: 'Invalid IPv4 address' };
            }
            
            var isDuplicate = _policy.ip_allowlist.some(function(item, idx) {
                if (excludeIndex !== undefined && idx === excludeIndex) return false;
                return item.ip === ipStr;
            });
            
            if (isDuplicate) {
                return { valid: false, error: 'This IP address already exists in the allowlist' };
            }
            
            return { valid: true };
        },
        
        // Utility
        generateRequestId: function() {
            return 'REQ-' + Date.now().toString(36).toUpperCase() + '-' + Math.random().toString(36).substring(2, 8).toUpperCase();
        },
        
        // Subscribe to policy changes
        subscribe: function(listener) {
            _listeners.push(listener);
            return function unsubscribe() {
                var idx = _listeners.indexOf(listener);
                if (idx > -1) _listeners.splice(idx, 1);
            };
        },
        
        // Initialize (TODO: fetch from backend API)
        init: function() {
            console.log('[AccountPolicyService] Initialized - account-wide policy active');
            return Promise.resolve(_policy);
        }
    };
})();

// Auto-initialize on load
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function() {
        AccountPolicyService.init();
    });
}

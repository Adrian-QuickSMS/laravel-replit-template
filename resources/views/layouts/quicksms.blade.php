@extends('layouts.default')

@push('styles')
<link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/quicksms-pastel.css') }}" rel="stylesheet" type="text/css"/>
@endpush

@push('scripts')
<script src="{{ asset('js/security-helpers.js') }}"></script>
<script src="{{ asset('js/account-policy-service.js') }}"></script>
<script src="{{ asset('js/quicksms-account-lifecycle.js') }}"></script>
<script src="{{ asset('js/quicksms-test-mode.js') }}"></script>
<script src="{{ asset('js/quicksms-role-navigation.js') }}"></script>
<script src="{{ asset('js/quicksms-sender-capability.js') }}"></script>
<script src="{{ asset('js/quicksms-permissions.js') }}"></script>
<script src="{{ asset('js/quicksms-permission-evaluator.js') }}"></script>
<script src="{{ asset('js/quicksms-reporting-access.js') }}"></script>
<script src="{{ asset('js/quicksms-enforcement-rules.js') }}"></script>
<script src="{{ asset('js/quicksms-audit-logger.js') }}"></script>
<script src="{{ asset('js/quicksms-security-controls.js') }}"></script>
<script src="{{ asset('js/quicksms-hierarchy-enforcement.js') }}"></script>
<script src="{{ asset('js/quicksms-permission-engine.js') }}"></script>
<script>
// Initialize account lifecycle and test mode from session/backend data
(function() {
    // Mock account data - in production, this comes from backend session
    var accountData = {
        account_id: sessionStorage.getItem('account_id') || null,
        lifecycle_state: sessionStorage.getItem('lifecycle_state') || 'TEST',
        state_changed_at: sessionStorage.getItem('state_changed_at') || null,
        suspension_reason: sessionStorage.getItem('suspension_reason') || null
    };
    
    if (accountData.account_id && typeof AccountLifecycle !== 'undefined') {
        AccountLifecycle.init(accountData);
    }
    
    // Initialize Test Mode Restrictions
    if (typeof TestModeRestrictions !== 'undefined') {
        TestModeRestrictions.init({
            verified_mobile: sessionStorage.getItem('test_mode_verified_mobile') || null,
            approved_test_numbers: JSON.parse(sessionStorage.getItem('test_mode_approved_numbers') || '[]'),
            fragments_used: parseInt(sessionStorage.getItem('test_mode_fragments_used') || '0', 10)
        });
    }
    
    // SenderID Returned Banner Logic
    (function() {
        fetch('/api/notifications?type=SENDERID_RETURNED')
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (result.success && result.data && result.data.length > 0) {
                    var notification = result.data[0];
                    var banner = document.getElementById('senderid-returned-banner');
                    if (banner) {
                        var nameEl = document.getElementById('senderid-returned-name');
                        var linkEl = document.getElementById('senderid-returned-link');
                        if (nameEl && notification.meta) nameEl.textContent = notification.meta.sender_id_value || '';
                        if (linkEl) {
                            linkEl.href = notification.deep_link || '#';
                            linkEl.setAttribute('data-notification-uuid', notification.uuid);
                            linkEl.addEventListener('click', function(e) {
                                var uuid = this.getAttribute('data-notification-uuid');
                                if (uuid) {
                                    var csrfToken = document.querySelector('meta[name="csrf-token"]');
                                    fetch('/api/notifications/' + uuid + '/read', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                                            'Content-Type': 'application/json'
                                        }
                                    });
                                }
                            });
                        }
                        banner.setAttribute('data-notification-uuid', notification.uuid);
                        banner.style.display = 'block';
                    }
                }
            })
            .catch(function() {});
    })();

    window.dismissSenderIdBanner = function(btn) {
        var banner = document.getElementById('senderid-returned-banner');
        var uuid = banner ? banner.getAttribute('data-notification-uuid') : null;
        if (uuid) {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            fetch('/api/notifications/' + uuid + '/dismiss', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Content-Type': 'application/json'
                }
            });
        }
    };

    // Show/hide TEST mode activation banner based on account state
    // Default: Show banner if no lifecycle state is set (new accounts default to TEST)
    var testModeBanner = document.getElementById('test-mode-activation-banner');
    var collapsedTab = document.getElementById('test-mode-collapsed-tab');
    var BANNER_STORAGE_KEY = 'quicksms_test_banner_collapsed';
    
    if (testModeBanner) {
        var lifecycleState = sessionStorage.getItem('lifecycle_state');
        var isTestMode = false;
        var isCollapsed = localStorage.getItem(BANNER_STORAGE_KEY) === 'true';
        
        // Check AccountLifecycle if initialized
        if (typeof AccountLifecycle !== 'undefined' && AccountLifecycle.getCurrentState()) {
            isTestMode = AccountLifecycle.isTest();
        } else if (lifecycleState) {
            // Fallback to sessionStorage
            isTestMode = lifecycleState === 'TEST';
        } else {
            // Default: New accounts are TEST mode, show banner
            isTestMode = true;
        }
        
        // Respect user's collapse preference when showing banner
        if (isTestMode) {
            if (isCollapsed) {
                testModeBanner.style.display = 'none';
                if (collapsedTab) collapsedTab.style.display = 'block';
            } else {
                testModeBanner.style.display = 'block';
                if (collapsedTab) collapsedTab.style.display = 'none';
            }
        } else {
            testModeBanner.style.display = 'none';
            if (collapsedTab) collapsedTab.style.display = 'none';
        }
        
        // Listen for state changes to update banner visibility
        if (typeof AccountLifecycle !== 'undefined') {
            AccountLifecycle.onStateChange(function(newState, oldState) {
                var stillCollapsed = localStorage.getItem(BANNER_STORAGE_KEY) === 'true';
                if (newState === 'TEST') {
                    if (stillCollapsed) {
                        testModeBanner.style.display = 'none';
                        if (collapsedTab) collapsedTab.style.display = 'block';
                    } else {
                        testModeBanner.style.display = 'block';
                        if (collapsedTab) collapsedTab.style.display = 'none';
                    }
                } else {
                    testModeBanner.style.display = 'none';
                    if (collapsedTab) collapsedTab.style.display = 'none';
                }
            });
        }
    }
})();
</script>
@endpush

@section('sidebar')
    @include('elements.quicksms-sidebar')
@endsection

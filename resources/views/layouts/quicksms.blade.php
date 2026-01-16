@extends('layouts.default')

@push('styles')
<link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/quicksms-pastel.css') }}" rel="stylesheet" type="text/css"/>
@endpush

@push('scripts')
<script src="{{ asset('js/quicksms-account-lifecycle.js') }}"></script>
<script>
// Initialize account lifecycle from session/backend data
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
})();
</script>
@endpush

@section('sidebar')
    @include('elements.quicksms-sidebar')
@endsection

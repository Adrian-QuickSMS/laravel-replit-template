@extends('layouts.quicksms')

@section('title', 'Purchase Messages')

@push('styles')
<style>
.purchase-messages-container {
    min-height: calc(100vh - 200px);
}
.access-denied-card {
    max-width: 500px;
    margin: 100px auto;
    text-align: center;
}
.access-denied-card .icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.access-denied-card .icon-wrapper i {
    font-size: 2rem;
    color: #dc3545;
}
.purchase-header {
    margin-bottom: 1.5rem;
}
.purchase-header h2 {
    margin-bottom: 0.25rem;
}
.purchase-header p {
    color: #6c757d;
    margin-bottom: 0;
}
.placeholder-card {
    border: 2px dashed #dee2e6;
    background: #f8f9fa;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.placeholder-card .placeholder-content {
    text-align: center;
    color: #6c757d;
}
.placeholder-card .placeholder-content i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.placeholder-card .placeholder-content p {
    margin-bottom: 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid purchase-messages-container">
    @php
        $currentUserRole = 'admin';
    @endphp
    
    @if(!in_array($currentUserRole, ['admin', 'finance']))
        <div class="card access-denied-card">
            <div class="card-body py-5">
                <div class="icon-wrapper">
                    <i class="fas fa-lock"></i>
                </div>
                <h4 class="mb-3">Access Restricted</h4>
                <p class="text-muted mb-4">This page is only accessible to Admin and Finance users. Please contact your administrator if you need access to purchasing features.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Return to Dashboard
                </a>
            </div>
        </div>
    @else
        <div class="purchase-header">
            <h2>Purchase Messages</h2>
            <p>Buy message credits and packages for your account</p>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card placeholder-card">
                    <div class="card-body">
                        <div class="placeholder-content">
                            <i class="fas fa-shopping-cart"></i>
                            <h5>Pricing & Packages Coming Soon</h5>
                            <p>Message credit packages and pricing options will be displayed here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Purchase Messages] Page initialized');
});
</script>
@endpush

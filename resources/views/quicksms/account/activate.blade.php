@extends('layouts.quicksms')

@section('title', $page_title ?? 'Activate Your Account')

@push('styles')
<style>
    .activate-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .activate-header {
        text-align: center;
        padding: 2rem 0;
    }
    .activate-header .icon-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
    .activate-header .icon-circle i {
        font-size: 2rem;
        color: #886cc0;
    }
    .step-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    .step-card.completed {
        border-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    }
    .step-card.current {
        border-color: #886cc0;
        box-shadow: 0 0 0 3px rgba(136, 108, 192, 0.1);
    }
    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .step-card.completed .step-number {
        background: #10b981;
        color: white;
    }
    .step-card.current .step-number {
        background: #886cc0;
        color: white;
    }
    .step-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    .step-description {
        color: #6b7280;
        font-size: 0.875rem;
    }
    .requirement-list {
        list-style: none;
        padding: 0;
        margin: 1rem 0 0;
    }
    .requirement-list li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
        font-size: 0.875rem;
        color: #4b5563;
    }
    .requirement-list li i.complete {
        color: #10b981;
    }
    .requirement-list li i.pending {
        color: #d1d5db;
    }
    .help-card {
        background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
    }
</style>
@endpush

@section('content')
<div class="activate-container">
    <div class="activate-header">
        <div class="icon-circle">
            <i class="fas fa-rocket"></i>
        </div>
        <h2 class="mb-2">Activate Your Account</h2>
        <p class="text-muted">Complete the steps below to unlock full messaging capabilities</p>
    </div>

    <div id="activation-steps">
        <div class="step-card" id="step-details" data-step="1">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">1</div>
                <div class="flex-grow-1">
                    <div class="step-title">Complete Account Details</div>
                    <div class="step-description">Provide your company information for compliance and billing</div>
                    <ul class="requirement-list" id="details-requirements">
                        <li><i class="fas fa-circle pending" id="req-company"></i> Company name</li>
                        <li><i class="fas fa-circle pending" id="req-address"></i> Business address</li>
                        <li><i class="fas fa-circle pending" id="req-website"></i> Website</li>
                        <li><i class="fas fa-circle pending" id="req-sector"></i> Business sector</li>
                        <li><i class="fas fa-circle pending" id="req-vat"></i> VAT information</li>
                    </ul>
                    <a href="{{ route('account.details') }}" class="btn btn-sm btn-outline-primary mt-3" id="btn-complete-details">
                        <i class="fas fa-edit me-1"></i> Complete Details
                    </a>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step1-status">Pending</span>
                </div>
            </div>
        </div>

        <div class="step-card" id="step-payment" data-step="2">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">2</div>
                <div class="flex-grow-1">
                    <div class="step-title">Make Your First Payment</div>
                    <div class="step-description">Purchase message credits to activate your account</div>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Once you complete your account details, you can purchase message credits to start sending live messages.
                    </p>
                    <button class="btn btn-sm btn-primary mt-3" id="btn-purchase" disabled>
                        <i class="fas fa-credit-card me-1"></i> Purchase Credits
                    </button>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step2-status">Waiting</span>
                </div>
            </div>
        </div>

        <div class="step-card" id="step-activated" data-step="3">
            <div class="d-flex align-items-start gap-3">
                <div class="step-number">3</div>
                <div class="flex-grow-1">
                    <div class="step-title">Account Activated</div>
                    <div class="step-description">Your account is fully activated and ready for live messaging</div>
                    <div class="mt-3" id="activated-actions" style="display: none;">
                        <a href="{{ route('messages.send') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-paper-plane me-1"></i> Send Your First Message
                        </a>
                    </div>
                </div>
                <div class="step-status">
                    <span class="badge bg-light text-muted" id="step3-status">Pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="help-card">
        <div class="d-flex align-items-start gap-3">
            <div>
                <i class="fas fa-question-circle" style="font-size: 1.5rem; color: #886cc0;"></i>
            </div>
            <div>
                <h6 class="mb-1">Need Help?</h6>
                <p class="text-muted small mb-2">
                    If you have questions about activation or need assistance, our support team is here to help.
                </p>
                <a href="{{ route('support.create-ticket') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-headset me-1"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/quicksms-account-lifecycle.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var lifecycle = window.AccountLifecycle;
    if (!lifecycle) return;
    
    function updateUI() {
        var state = lifecycle.getState();
        var activationStatus = lifecycle.getActivationStatus();
        var isLive = lifecycle.isLive();
        
        var step1 = document.getElementById('step-details');
        var step2 = document.getElementById('step-payment');
        var step3 = document.getElementById('step-activated');
        
        if (activationStatus.account_details_complete) {
            step1.classList.add('completed');
            step1.classList.remove('current');
            document.getElementById('step1-status').className = 'badge bg-success';
            document.getElementById('step1-status').textContent = 'Complete';
            
            document.querySelectorAll('#details-requirements i').forEach(function(icon) {
                icon.classList.remove('pending');
                icon.classList.add('complete');
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
            });
            
            document.getElementById('btn-purchase').disabled = false;
            step2.classList.add('current');
            document.getElementById('step2-status').className = 'badge bg-warning text-dark';
            document.getElementById('step2-status').textContent = 'Ready';
        } else {
            step1.classList.add('current');
        }
        
        if (activationStatus.first_payment_made || isLive) {
            step2.classList.add('completed');
            step2.classList.remove('current');
            document.getElementById('step2-status').className = 'badge bg-success';
            document.getElementById('step2-status').textContent = 'Complete';
        }
        
        if (isLive) {
            step3.classList.add('completed');
            document.getElementById('step3-status').className = 'badge bg-success';
            document.getElementById('step3-status').textContent = 'Active';
            document.getElementById('activated-actions').style.display = 'block';
        }
    }
    
    document.getElementById('btn-purchase').addEventListener('click', function() {
        window.location.href = '{{ route("purchase.messages") }}';
    });
    
    updateUI();
    
    document.addEventListener('lifecycle:state_changed', function(e) {
        updateUI();
    });
});
</script>
@endpush

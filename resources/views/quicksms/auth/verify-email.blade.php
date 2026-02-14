@extends('layouts.fullwidth')
@section('title', 'Verify Email')
@section('content')
<div class="col-lg-5 col-md-6">
    <div class="card mb-0 h-auto">
        <div class="card-body">
            <div class="text-center mb-3">
                <a href="{{ url('/') }}"><img class="logo-auth" src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 48px;"></a>
            </div>
            
            <h4 class="text-center mb-1">Verify Your Email</h4>
            <p class="text-center text-muted mb-4">Step 2 of 3: Verify Email</p>
            
            <div id="verifyingState" class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Verifying...</span>
                </div>
                <h5>Verifying your email...</h5>
                <p class="text-muted mb-0">Please wait while we verify your email address.</p>
            </div>
            
            <div id="successState" class="text-center py-4 d-none">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                </div>
                <h4 class="text-success mb-2">Email Verified!</h4>
                <p class="text-muted mb-4">Your email address has been successfully verified.</p>
                <p class="mb-4">Redirecting to complete your registration...</p>
                <a href="#" class="btn btn-primary" id="continueToStep2Btn">
                    Continue to Step 3 <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            
            <div id="errorState" class="text-center py-4 d-none">
                <div class="mb-3">
                    <i class="fas fa-exclamation-circle text-danger" style="font-size: 64px;"></i>
                </div>
                <h4 class="text-danger mb-2" id="errorTitle">Verification Failed</h4>
                <p class="text-muted mb-4" id="errorMessage">The verification link is invalid or has expired.</p>
                
                <div id="resendSection">
                    <p class="mb-3">Enter your email to receive a new verification link:</p>
                    <div class="form-group mb-3">
                        <input type="email" class="form-control" id="resendEmail" placeholder="your@email.com">
                        <div class="invalid-feedback">Please enter a valid email address</div>
                    </div>
                    <button class="btn btn-primary" id="resendBtn">
                        <span class="btn-text"><i class="fas fa-envelope me-2"></i>Resend Verification Email</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Sending...
                        </span>
                    </button>
                </div>
                
                <div id="resendSuccess" class="d-none">
                    <div class="alert alert-success">
                        <i class="fas fa-check me-2"></i>
                        A new verification email has been sent to <strong id="resendEmailConfirm"></strong>
                    </div>
                </div>
            </div>
            
            <div class="new-account mt-3 text-center">
                <p class="mb-0"><a class="text-primary" href="{{ url('/signup') }}">Back to Sign Up</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.logo-auth {
    max-height: 48px;
}
.form-control:focus {
    border-color: rgba(136, 108, 192, 0.5);
    box-shadow: 0 0 0 0.2rem rgba(136, 108, 192, 0.15);
}
.btn-primary {
    background-color: #886CC0;
    border-color: #886CC0;
}
.btn-primary:hover, .btn-primary:focus {
    background-color: #7358a8;
    border-color: #7358a8;
}
.text-primary {
    color: #886CC0 !important;
}
.card {
    border: none;
    box-shadow: 0 0 35px 0 rgba(154, 161, 171, 0.15);
}
</style>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var TOKEN_EXPIRY_HOURS = 24;
    
    var urlParams = new URLSearchParams(window.location.search);
    var token = urlParams.get('token');
    var email = urlParams.get('email');
    
    if (!token) {
        showError('Missing Token', 'No verification token was provided. Please check your email for the correct link.');
        return;
    }
    
    verifyToken(token, email);
    
    function verifyToken(token, email) {
        console.log('[Verify] Verifying token');
        
        $.ajax({
            url: '/api/auth/verify-email',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ token: token }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                var verifiedEmail = (response.data && response.data.email) || email;
                var verifiedToken = (response.data && response.data.token) || token;
                onVerificationSuccess(verifiedEmail, verifiedToken);
            },
            error: function(xhr) {
                var msg = 'This verification link is invalid. Please check your email or request a new link.';
                var title = 'Verification Failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (xhr.status === 400 && msg.toLowerCase().indexOf('expired') !== -1) {
                    title = 'Link Expired';
                }
                showError(title, msg);
            }
        });
    }
    
    function onVerificationSuccess(email, verifiedToken) {
        $('#verifyingState').addClass('d-none');
        $('#successState').removeClass('d-none');
        
        var pendingReg = JSON.parse(sessionStorage.getItem('pendingRegistration') || '{}');
        var redirectUrl = '/signup/security?email=' + encodeURIComponent(email || '') + '&verified=true&token=' + encodeURIComponent(verifiedToken || '');
        if (pendingReg.first_name) redirectUrl += '&first_name=' + encodeURIComponent(pendingReg.first_name);
        if (pendingReg.last_name) redirectUrl += '&last_name=' + encodeURIComponent(pendingReg.last_name);
        if (pendingReg.business_name) redirectUrl += '&business_name=' + encodeURIComponent(pendingReg.business_name);
        if (pendingReg.job_title) redirectUrl += '&job_title=' + encodeURIComponent(pendingReg.job_title);
        if (pendingReg.country) redirectUrl += '&country=' + encodeURIComponent(pendingReg.country);
        
        // Set the button href so clicking it also works
        $('#continueToStep2Btn').attr('href', redirectUrl);
        
        // Mock HubSpot update
        // In production: POST /api/hubspot/contact/update { email, properties: { email_verified: true } }
        console.log('[HubSpot] Updating contact:', email, '{ email_verified: true }');
        
        // Auto-redirect after 3 seconds
        setTimeout(function() {
            window.location.href = redirectUrl;
        }, 3000);
    }
    
    function showError(title, message) {
        $('#verifyingState').addClass('d-none');
        $('#errorState').removeClass('d-none');
        $('#errorTitle').text(title);
        $('#errorMessage').text(message);
        
        if (email) {
            $('#resendEmail').val(email);
        }
    }
    
    $('#resendBtn').on('click', function() {
        var emailVal = $('#resendEmail').val().trim();
        
        if (!emailVal || !isValidEmail(emailVal)) {
            $('#resendEmail').addClass('is-invalid');
            return;
        }
        
        $('#resendEmail').removeClass('is-invalid');
        
        var $btn = $(this);
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        
        $.ajax({
            url: '/api/auth/resend-verification',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: emailVal }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function() {
                $('#resendSection').addClass('d-none');
                $('#resendEmailConfirm').text(emailVal);
                $('#resendSuccess').removeClass('d-none');
            },
            error: function() {
                $('#resendSection').addClass('d-none');
                $('#resendEmailConfirm').text(emailVal);
                $('#resendSuccess').removeClass('d-none');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
            }
        });
    });
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    $('#resendEmail').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
@endpush
@endsection

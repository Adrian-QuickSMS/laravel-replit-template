@extends('layouts.fullwidth')
@section('title', 'Security & Consent')
@section('content')
<div class="col-lg-5 col-md-6">
    <div class="card mb-0 h-auto">
        <div class="card-body">
            <div class="text-center mb-3">
                <a href="{{ url('/') }}"><img class="logo-auth" src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 48px;"></a>
            </div>
            <h4 class="text-center mb-2">Set Up Your Account</h4>
            <p class="text-center text-muted mb-4">Step 2 of 3: Security & Consent</p>
            
            <div class="alert alert-success mb-4" id="verifiedBadge">
                <i class="fas fa-check-circle me-2"></i>
                Email verified: <strong id="verifiedEmail"></strong>
            </div>
            
            <form id="securityForm" novalidate>
                <div class="form-group mb-4">
                    <label class="form-label" for="password">Create Password <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="password" placeholder="Minimum 8 characters" required>
                        <span class="show-pass eye" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                            <i class="fa fa-eye-slash"></i>
                        </span>
                    </div>
                    <div class="invalid-feedback">Password must be at least 8 characters</div>
                    <div class="password-strength mt-2" id="passwordStrength"></div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Re-enter password" required>
                    <div class="invalid-feedback" id="confirmError">Passwords do not match</div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="mobileNumber">Mobile Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="mobileNumber" placeholder="+44 7700 900123" required>
                    <div class="invalid-feedback">Please enter a valid mobile number</div>
                    <small class="form-text text-muted">Used for account recovery and 2FA</small>
                </div>
                
                <hr class="my-4">
                
                <h6 class="mb-3">Consent & Agreements</h6>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="termsConsent" required>
                    <label class="form-check-label" for="termsConsent">
                        I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Acceptable Use Policy</a> <span class="text-danger">*</span>
                    </label>
                    <div class="invalid-feedback">You must agree to the terms to continue</div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="privacyConsent" required>
                    <label class="form-check-label" for="privacyConsent">
                        I have read and accept the <a href="#" class="text-primary">Privacy Policy</a> <span class="text-danger">*</span>
                    </label>
                    <div class="invalid-feedback">You must accept the privacy policy to continue</div>
                </div>
                
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="marketingConsent">
                    <label class="form-check-label" for="marketingConsent">
                        I would like to receive product updates and marketing communications (optional)
                    </label>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-block" id="continueBtn">
                        <span class="btn-text">Continue to Step 3</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Processing...
                        </span>
                    </button>
                </div>
            </form>
            
            <div class="new-account mt-3 text-center">
                <p class="mb-0">Need help? <a class="text-primary" href="#">Contact Support</a></p>
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
.password-strength {
    font-size: 0.75rem;
}
.password-strength .strength-bar {
    height: 4px;
    border-radius: 2px;
    margin-bottom: 4px;
}
.password-strength.weak .strength-bar { background: #dc3545; width: 33%; }
.password-strength.medium .strength-bar { background: #ffc107; width: 66%; }
.password-strength.strong .strength-bar { background: #28a745; width: 100%; }
.show-pass .fa-eye { display: none; }
.show-pass.active .fa-eye { display: inline; }
.show-pass.active .fa-eye-slash { display: none; }
</style>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var email = urlParams.get('email');
    var verified = urlParams.get('verified');
    
    if (!verified || verified !== 'true') {
        window.location.href = '/signup';
        return;
    }
    
    if (email) {
        $('#verifiedEmail').text(email);
    } else {
        $('#verifiedBadge').hide();
    }
    
    $('.show-pass').on('click', function() {
        var $input = $(this).siblings('input');
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('active');
    });
    
    $('#password').on('input', function() {
        var password = $(this).val();
        var $strength = $('#passwordStrength');
        
        if (password.length === 0) {
            $strength.html('').removeClass('weak medium strong');
            return;
        }
        
        var strength = calculateStrength(password);
        $strength.html('<div class="strength-bar"></div><span>' + strength.label + '</span>');
        $strength.removeClass('weak medium strong').addClass(strength.class);
        
        if (password.length >= 8) {
            $(this).removeClass('is-invalid');
        }
    });
    
    function calculateStrength(password) {
        var score = 0;
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;
        
        if (score <= 2) return { class: 'weak', label: 'Weak password' };
        if (score <= 3) return { class: 'medium', label: 'Medium strength' };
        return { class: 'strong', label: 'Strong password' };
    }
    
    $('#confirmPassword').on('input', function() {
        var confirm = $(this).val();
        var password = $('#password').val();
        
        if (confirm && confirm !== password) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#securityForm').on('submit', function(e) {
        e.preventDefault();
        
        var isValid = true;
        var password = $('#password').val();
        var confirm = $('#confirmPassword').val();
        
        if (password.length < 8) {
            $('#password').addClass('is-invalid');
            isValid = false;
        }
        
        if (confirm !== password) {
            $('#confirmPassword').addClass('is-invalid');
            isValid = false;
        }
        
        if (!$('#mobileNumber').val().trim()) {
            $('#mobileNumber').addClass('is-invalid');
            isValid = false;
        }
        
        if (!$('#termsConsent').is(':checked')) {
            $('#termsConsent').addClass('is-invalid');
            isValid = false;
        }
        
        if (!$('#privacyConsent').is(':checked')) {
            $('#privacyConsent').addClass('is-invalid');
            isValid = false;
        }
        
        if (!isValid) return;
        
        var $btn = $('#continueBtn');
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        
        // Mock save security settings
        // In production: POST /api/auth/complete-registration
        var formData = {
            email: email,
            password: password,
            mobile_number: $('#mobileNumber').val().trim(),
            terms_consent: true,
            privacy_consent: true,
            marketing_consent: $('#marketingConsent').is(':checked')
        };
        
        console.log('[Security] Saving security settings:', formData);
        
        setTimeout(function() {
            // Redirect to Step 3 (Company Details or Dashboard)
            window.location.href = '/signup/complete?email=' + encodeURIComponent(email);
        }, 1500);
    });
    
    $('input[required]').on('input change', function() {
        if ($(this).attr('type') === 'checkbox') {
            if ($(this).is(':checked')) $(this).removeClass('is-invalid');
        } else {
            if ($(this).val().trim()) $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush
@endsection

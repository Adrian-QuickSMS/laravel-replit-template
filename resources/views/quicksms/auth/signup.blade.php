@extends('layouts.fullwidth')
@section('title', 'Sign Up')
@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card mb-0 h-auto">
        <div class="card-body">
            <div class="text-center mb-3">
                <a href="{{ url('/') }}"><img class="logo-auth" src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 48px;"></a>
            </div>
            <h4 class="text-center mb-2">Create your account</h4>
            <p class="text-center text-muted mb-4">Step 1 of 3: Account Basics</p>
            
            <div class="alert alert-danger d-none" id="signupError"></div>
            
            <form id="signupForm" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label" for="firstName">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="firstName" placeholder="Enter first name" required>
                            <div class="invalid-feedback">Please enter your first name</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label" for="lastName">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lastName" placeholder="Enter last name" required>
                            <div class="invalid-feedback">Please enter your last name</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label" for="jobTitle">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="jobTitle" placeholder="e.g., Marketing Manager" required>
                            <div class="invalid-feedback">Please enter your job title</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label" for="businessName">Business Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="businessName" placeholder="Your company name" required>
                            <div class="invalid-feedback">Please enter your business name</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="businessEmail">Business Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="businessEmail" placeholder="you@company.com" required>
                    <div class="invalid-feedback" id="emailError">Please enter a valid business email address</div>
                    <small class="form-text text-muted">We'll send a verification link to this address</small>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="country">Country <span class="text-danger">*</span></label>
                    <select class="form-select" id="country" required>
                        <option value="GB" selected>United Kingdom</option>
                        <option value="IE">Ireland</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="AU">Australia</option>
                        <option value="DE">Germany</option>
                        <option value="FR">France</option>
                        <option value="NL">Netherlands</option>
                        <option value="ES">Spain</option>
                        <option value="IT">Italy</option>
                    </select>
                    <div class="invalid-feedback">Please select your country</div>
                </div>
                
                <div class="form-group mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="termsAgree" required>
                        <label class="form-check-label" for="termsAgree">
                            I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>
                        </label>
                        <div class="invalid-feedback">You must agree to the terms to continue</div>
                    </div>
                </div>
                
                <div id="recaptchaContainer" class="mb-4">
                    <div class="recaptcha-mock border rounded p-3 bg-light d-flex align-items-center">
                        <input class="form-check-input me-2" type="checkbox" id="recaptchaCheck">
                        <label class="form-check-label mb-0" for="recaptchaCheck">I'm not a robot</label>
                        <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="reCAPTCHA" class="ms-auto" style="height: 32px; opacity: 0.7;">
                    </div>
                    <div class="invalid-feedback d-block" id="recaptchaError" style="display: none !important;">Please complete the reCAPTCHA verification</div>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
                        <span class="btn-text">Register</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Sending verification...
                        </span>
                    </button>
                </div>
            </form>
            
            <div id="successMessage" class="text-center mt-4 d-none">
                <div class="mb-3">
                    <i class="fas fa-envelope-open-text text-success" style="font-size: 48px;"></i>
                </div>
                <h5 class="text-success">Verification Email Sent!</h5>
                <p class="text-muted mb-3">We've sent a verification link to <strong id="sentToEmail"></strong></p>
                <p class="small text-muted">Please check your inbox and click the link to continue. The link expires in 24 hours.</p>
                
                <div class="alert alert-info small mt-3" id="testModeLink">
                    <strong>Test Mode:</strong> Click the link below to continue<br>
                    <a href="#" id="verifyLink" class="text-primary fw-bold"></a>
                </div>
                
                <button class="btn btn-outline-primary btn-sm mt-2" id="resendBtn">
                    <i class="fas fa-redo me-1"></i>Resend Email
                </button>
            </div>
            
            <div class="new-account mt-3 text-center">
                <p class="mb-0">Already have an account? <a class="text-primary" href="{{ url('/') }}">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.logo-auth {
    max-height: 48px;
}
.form-control:focus, .form-select:focus {
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
.form-control.is-invalid, .form-select.is-invalid {
    border-color: #dc3545;
}
.form-control.is-valid, .form-select.is-valid {
    border-color: #28a745;
}
.recaptcha-mock {
    background-color: #f9f9f9 !important;
    border-color: #d3d3d3 !important;
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
    var emailCheckTimeout;
    
    $('#businessEmail').on('blur', function() {
        var email = $(this).val().trim();
        if (email && isValidEmail(email)) {
            checkEmailUniqueness(email);
        }
    });
    
    $('#businessEmail').on('input', function() {
        clearTimeout(emailCheckTimeout);
        var $field = $(this);
        var email = $field.val().trim();
        
        $field.removeClass('is-invalid is-valid');
        
        if (email.length > 5) {
            emailCheckTimeout = setTimeout(function() {
                if (isValidEmail(email)) {
                    checkEmailUniqueness(email);
                }
            }, 500);
        }
    });
    
    function isValidEmail(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    function checkEmailUniqueness(email) {
        var $field = $('#businessEmail');
        var $error = $('#emailError');
        
        $.ajax({
            url: '/api/auth/check-email',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: email }),
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                if (response.available) {
                    $field.addClass('is-valid').removeClass('is-invalid');
                } else {
                    $field.addClass('is-invalid').removeClass('is-valid');
                    $error.text('This email is already registered. Please sign in or use a different email.');
                }
            },
            error: function() {
                if (!isValidEmail(email)) {
                    $field.addClass('is-invalid').removeClass('is-valid');
                    $error.text('Please enter a valid business email address');
                } else {
                    $field.addClass('is-valid').removeClass('is-invalid');
                }
            }
        });
    }
    
    $('input[required], select[required]').on('input change', function() {
        var $field = $(this);
        if ($field.val() && $field.val().trim()) {
            $field.removeClass('is-invalid');
        }
    });
    
    $('#signupForm').on('submit', function(e) {
        e.preventDefault();
        
        var isValid = true;
        var $form = $(this);
        
        $form.find('input[required], select[required]').each(function() {
            var $field = $(this);
            if ($field.attr('type') === 'checkbox') {
                if (!$field.is(':checked')) {
                    $field.addClass('is-invalid');
                    isValid = false;
                } else {
                    $field.removeClass('is-invalid');
                }
            } else if (!$field.val() || !$field.val().trim()) {
                $field.addClass('is-invalid');
                isValid = false;
            } else {
                $field.removeClass('is-invalid');
            }
        });
        
        var email = $('#businessEmail').val().trim();
        if (email && !isValidEmail(email)) {
            $('#businessEmail').addClass('is-invalid');
            $('#emailError').text('Please enter a valid business email address');
            isValid = false;
        }
        
        if (!$('#recaptchaCheck').is(':checked')) {
            $('#recaptchaError').css('display', 'block !important').show();
            isValid = false;
        } else {
            $('#recaptchaError').hide();
        }
        
        if (!isValid) {
            return;
        }
        
        var $btn = $('#registerBtn');
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        $('#signupError').addClass('d-none');
        
        var formData = {
            first_name: $('#firstName').val().trim(),
            last_name: $('#lastName').val().trim(),
            job_title: $('#jobTitle').val().trim(),
            company_name: $('#businessName').val().trim(),
            email: email,
            country: $('#country').val(),
            accept_terms: $('#termsAgree').is(':checked') ? 1 : 0,
            accept_privacy: 1,
            accept_fraud_prevention: 1,
            recaptcha_token: 'mock_token_' + Date.now()
        };
        
        sessionStorage.setItem('pendingRegistration', JSON.stringify(formData));
        
        $.ajax({
            url: '/api/auth/signup',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
            },
            success: function(response) {
                var testToken = 'test_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                var verifyUrl = '/signup/verify?token=' + testToken + '&email=' + encodeURIComponent(email);
                
                sessionStorage.setItem('verificationToken', testToken);
                
                $('#signupForm').addClass('d-none');
                $('#sentToEmail').text(email);
                $('#verifyLink').attr('href', verifyUrl).text(verifyUrl);
                $('#successMessage').removeClass('d-none');
            },
            error: function(xhr) {
                var msg = 'An error occurred. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var errorList = [];
                        for (var field in errors) {
                            errorList.push(errors[field][0]);
                        }
                        msg = errorList.join('<br>');
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                $('#signupError').html(msg).removeClass('d-none');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btn.find('.btn-text').removeClass('d-none');
                $btn.find('.btn-loading').addClass('d-none');
            }
        });
    });
    
    $('#resendBtn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Sending...');
        
        setTimeout(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Email Resent!');
            
            setTimeout(function() {
                $btn.html('<i class="fas fa-redo me-1"></i>Resend Email');
            }, 3000);
        }, 1000);
    });
});
</script>
@endpush
@endsection

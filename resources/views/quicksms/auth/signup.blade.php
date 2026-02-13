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
            <p class="text-center text-muted mb-4">Enter your details to get started</p>
            
            <div class="alert alert-danger d-none" id="signupError"></div>
            
            <form id="signupForm" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label" for="firstName">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Enter first name" required>
                            <div class="invalid-feedback">Please enter your first name</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label" for="lastName">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Enter last name" required>
                            <div class="invalid-feedback">Please enter your last name</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="businessName">Business Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="businessName" name="company_name" placeholder="Your company name" required>
                    <div class="invalid-feedback">Please enter your business name</div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="businessEmail">Business Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="businessEmail" name="email" placeholder="you@company.com" required>
                    <div class="invalid-feedback" id="emailError">Please enter a valid business email address</div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="mobileNumber">Mobile Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="mobileNumber" name="mobile_number" placeholder="+44 7911 123456" required>
                    <div class="invalid-feedback">Please enter your mobile number</div>
                    <small class="form-text text-muted">Used for two-factor authentication</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Min 12 characters" required minlength="12">
                            <div class="invalid-feedback" id="passwordError">Password must be at least 12 characters</div>
                            <small class="form-text text-muted">Min 12 chars, mixed case, number &amp; symbol</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label" for="passwordConfirm">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="passwordConfirm" name="password_confirmation" placeholder="Confirm password" required>
                            <div class="invalid-feedback" id="confirmError">Passwords do not match</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="country">Country <span class="text-danger">*</span></label>
                    <select class="form-select" id="country" name="country" required>
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
                
                <div class="form-group mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="termsAgree" name="accept_terms" required>
                        <label class="form-check-label" for="termsAgree">
                            I agree to the <a href="#" class="text-primary">Terms of Service</a>
                        </label>
                        <div class="invalid-feedback">You must agree to the terms</div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="privacyAgree" name="accept_privacy" required>
                        <label class="form-check-label" for="privacyAgree">
                            I agree to the <a href="#" class="text-primary">Privacy Policy</a>
                        </label>
                        <div class="invalid-feedback">You must agree to the privacy policy</div>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="fraudAgree" name="accept_fraud_prevention" required>
                        <label class="form-check-label" for="fraudAgree">
                            I consent to <a href="#" class="text-primary">fraud prevention checks</a>
                        </label>
                        <div class="invalid-feedback">You must consent to fraud prevention</div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
                        <span class="btn-text">Create Account</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Creating account...
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
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    $('input[required], select[required]').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
    
    $('#signupForm').on('submit', function(e) {
        e.preventDefault();
        
        var isValid = true;
        var $form = $(this);
        
        $form.find('input[required], select[required]').each(function() {
            var $field = $(this);
            if ($field.attr('type') === 'checkbox') {
                if (!$field.is(':checked')) { $field.addClass('is-invalid'); isValid = false; }
                else { $field.removeClass('is-invalid'); }
            } else if (!$field.val().trim()) {
                $field.addClass('is-invalid'); isValid = false;
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
        
        var password = $('#password').val();
        var confirm = $('#passwordConfirm').val();
        if (password.length < 12) {
            $('#password').addClass('is-invalid');
            $('#passwordError').text('Password must be at least 12 characters');
            isValid = false;
        }
        if (password !== confirm) {
            $('#passwordConfirm').addClass('is-invalid');
            $('#confirmError').text('Passwords do not match');
            isValid = false;
        }
        
        if (!isValid) return;
        
        var $btn = $('#registerBtn');
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('d-none');
        $btn.find('.btn-loading').removeClass('d-none');
        $('#signupError').addClass('d-none');
        
        var formData = {
            company_name: $('#businessName').val().trim(),
            first_name: $('#firstName').val().trim(),
            last_name: $('#lastName').val().trim(),
            email: email,
            password: password,
            password_confirmation: confirm,
            mobile_number: $('#mobileNumber').val().trim(),
            country: $('#country').val(),
            accept_terms: $('#termsAgree').is(':checked') ? 1 : 0,
            accept_privacy: $('#privacyAgree').is(':checked') ? 1 : 0,
            accept_fraud_prevention: $('#fraudAgree').is(':checked') ? 1 : 0
        };
        
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
                $('#signupForm').addClass('d-none');
                $('#sentToEmail').text(email);
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
                            var $field = $('[name="' + field + '"]');
                            if ($field.length) {
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(errors[field][0]);
                            }
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
});
</script>
@endpush
@endsection

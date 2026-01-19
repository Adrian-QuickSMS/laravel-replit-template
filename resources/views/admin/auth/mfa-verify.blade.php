<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify MFA - QuickSMS Admin</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon.png') }}">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #1e3a5f 100%);
        }
        .admin-mfa-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .admin-badge {
            background: linear-gradient(135deg, #1e3a5f, #2d5a87);
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .admin-mfa-header {
            background: linear-gradient(135deg, #1e3a5f, #2d5a87);
            color: #fff;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
            text-align: center;
        }
        .admin-mfa-body {
            padding: 2rem;
        }
        .btn-admin-primary {
            background: linear-gradient(135deg, #1e3a5f, #2d5a87);
            border: none;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            width: 100%;
        }
        .btn-admin-primary:hover {
            background: linear-gradient(135deg, #2d5a87, #4a90d9);
            color: #fff;
        }
        .form-control:focus {
            border-color: #2d5a87;
            box-shadow: 0 0 0 0.2rem rgba(45, 90, 135, 0.25);
        }
        .verification-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.5em;
            font-family: monospace;
            padding: 1rem;
        }
        .mfa-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .mfa-icon i {
            font-size: 2.5rem;
        }
    </style>
</head>
<body class="vh-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="admin-mfa-card">
                        <div class="admin-mfa-header">
                            <div class="mfa-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="mb-1">Two-Factor Authentication</h3>
                            <p class="mb-0 opacity-75">Enter your verification code to continue</p>
                        </div>
                        <div class="admin-mfa-body">
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $errors->first() }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            <form action="{{ route('admin.mfa.verify.submit') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-center d-block">Verification Code</label>
                                    <input type="text" 
                                           class="form-control verification-input" 
                                           name="code" 
                                           maxlength="6"
                                           placeholder="000000"
                                           pattern="[0-9]{6}"
                                           inputmode="numeric"
                                           autocomplete="one-time-code"
                                           required
                                           autofocus>
                                    <div class="form-text text-center mt-2">
                                        Open your authenticator app and enter the 6-digit code
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-admin-primary">
                                    <i class="fas fa-unlock-alt me-2"></i>Verify
                                </button>
                            </form>
                            
                            <div class="text-center mt-4 pt-3 border-top">
                                <a href="{{ route('admin.logout') }}" class="text-muted">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script>
        document.querySelector('input[name="code"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>

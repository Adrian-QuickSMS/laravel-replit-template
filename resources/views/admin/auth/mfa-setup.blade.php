<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup MFA - QuickSMS Admin</title>
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
        .qr-placeholder {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
        }
        .secret-code {
            font-family: monospace;
            font-size: 1.25rem;
            letter-spacing: 0.1em;
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            word-break: break-all;
        }
        .step-badge {
            background: #2d5a87;
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        .verification-input {
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 0.5em;
            font-family: monospace;
        }
    </style>
</head>
<body class="vh-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-8 col-lg-6">
                    <div class="admin-mfa-card">
                        <div class="admin-mfa-header">
                            <div class="mb-3">
                                <span class="admin-badge">SECURITY SETUP</span>
                            </div>
                            <h3 class="mb-1">
                                <i class="fas fa-mobile-alt me-2"></i>Setup Two-Factor Authentication
                            </h3>
                            <p class="mb-0 opacity-75">MFA is mandatory for all admin accounts</p>
                        </div>
                        <div class="admin-mfa-body">
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $errors->first() }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            <div class="mb-4">
                                <h6><span class="step-badge">1</span>Install an authenticator app</h6>
                                <p class="text-muted ms-4 mb-0">
                                    Download Google Authenticator, Microsoft Authenticator, or Authy on your mobile device.
                                </p>
                            </div>
                            
                            <div class="mb-4">
                                <h6><span class="step-badge">2</span>Scan the QR code or enter the secret key</h6>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="qr-placeholder">
                                            <i class="fas fa-qrcode fa-4x text-muted mb-3"></i>
                                            <p class="text-muted mb-2 small">Scan with your authenticator app</p>
                                            <small class="text-muted">QR code generation requires backend integration</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-2">Or enter this secret key manually:</p>
                                        <div class="secret-code" id="secretCode">{{ $secret }}</div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="copySecret()">
                                            <i class="fas fa-copy me-1"></i>Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6><span class="step-badge">3</span>Enter the verification code</h6>
                                <form action="{{ route('admin.mfa.setup.complete') }}" method="POST" class="mt-3">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="text" 
                                               class="form-control form-control-lg verification-input" 
                                               name="code" 
                                               maxlength="6"
                                               placeholder="000000"
                                               pattern="[0-9]{6}"
                                               inputmode="numeric"
                                               autocomplete="one-time-code"
                                               required>
                                        <div class="form-text text-center">Enter the 6-digit code from your authenticator app</div>
                                    </div>
                                    <button type="submit" class="btn btn-admin-primary">
                                        <i class="fas fa-check-circle me-2"></i>Verify and Enable MFA
                                    </button>
                                </form>
                            </div>
                            
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Important:</strong> Save your secret key in a secure location. 
                                You will need it to recover your account if you lose access to your authenticator app.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script>
        function copySecret() {
            const secretCode = document.getElementById('secretCode').textContent;
            navigator.clipboard.writeText(secretCode).then(() => {
                alert('Secret key copied to clipboard');
            });
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - QuickSMS</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon.png') }}">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #1e3a5f 100%);
        }
        .admin-login-card {
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
        .admin-login-header {
            background: linear-gradient(135deg, #1e3a5f, #2d5a87);
            color: #fff;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
            text-align: center;
        }
        .admin-login-body {
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
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.875rem;
        }
        .security-notice i {
            color: #856404;
        }
    </style>
</head>
<body class="vh-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="admin-login-card">
                        <div class="admin-login-header">
                            <div class="mb-3">
                                <span class="admin-badge">ADMIN CONTROL PLANE</span>
                            </div>
                            <h3 class="mb-1">QuickSMS Admin</h3>
                            <p class="mb-0 opacity-75">Internal Use Only</p>
                        </div>
                        <div class="admin-login-body">
                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $errors->first() }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            <form action="{{ route('admin.login.submit') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" 
                                           class="form-control form-control-lg" 
                                           name="email" 
                                           value="{{ old('email') }}"
                                           placeholder="admin@quicksms.co.uk"
                                           required 
                                           autofocus>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Password</label>
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           name="password" 
                                           placeholder="Enter your password"
                                           required>
                                </div>
                                <button type="submit" class="btn btn-admin-primary">
                                    <i class="fas fa-shield-alt me-2"></i>Sign In
                                </button>
                            </form>
                            
                            <div class="security-notice">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Security Notice:</strong> This is a restricted administrative interface. 
                                All access attempts are logged and monitored. Unauthorized access is prohibited.
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <p class="text-white-50 mb-0">
                            <small>&copy; {{ date('Y') }} QuickSMS Ltd. All rights reserved.</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
</body>
</html>

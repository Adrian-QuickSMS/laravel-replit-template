@php
    $action = 'admin_dashboard';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="QuickSMS Admin Control Plane" />
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" type="image/png" href="{{asset('images/favicon.png') }}">
    
    <title>QuickSMS Admin | @yield('title', $page_title ?? '')</title> 

    @if(!empty(config('dz.public.global.css'))) 
        @foreach(config('dz.public.global.css') as $style)
            <link href="{{ asset($style) }}" rel="stylesheet" type="text/css"/>
        @endforeach
    @endif

    <link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/admin-control-plane.css') }}" rel="stylesheet" type="text/css"/>
    
    @stack('styles')
</head>
<body class="admin-control-plane">

    <div id="preloader" style="display: none;">
        <div class="lds-ripple">
            <div></div>
            <div></div>
        </div>
    </div>

    <div id="main-wrapper" class="show">
        <div class="nav-header admin-nav-header">
            <a href="{{ url('/admin') }}" class="brand-logo">
                <img src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS Admin" style="height: 35px; width: auto;">
                <span class="admin-badge">ADMIN</span>
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        
        @include('elements.admin-header')
        
        @yield('sidebar', View::make('elements.admin-sidebar'))
        
        <div class="content-body default-height qsms-density-compact @yield('body_class')">
            <div class="admin-mode-banner">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shield-alt me-2"></i>
                        <span><strong>Admin Control Plane</strong> — Internal Use Only. All actions are logged.</span>
                        <span class="ms-auto admin-user-info">
                            <i class="fas fa-user-shield me-1"></i>
                            <span id="admin-user-name">Admin User</span>
                            <span class="admin-role-badge">Super Admin</span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="qsms-main">
                <div class="qsms-content-wrap">
                    @yield('content')
                </div>
            </div>
        </div>
        
        @stack('modal')
        
        @include('elements.footer')

    </div>

    @if(!empty(config('dz.public.global.js.top')))
        @foreach(config('dz.public.global.js.top') as $script)
            <script src="{{ asset($script) }}" type="text/javascript"></script>
        @endforeach
    @endif
    
    @if(!empty(config('dz.public.global.js.bottom')))
        @foreach(config('dz.public.global.js.bottom') as $script)
            <script src="{{ asset($script) }}" type="text/javascript"></script>
        @endforeach
    @endif
    
    <script src="{{ asset('js/admin-control-plane.js') }}"></script>
    <script src="{{ asset('js/quicksms-audit-logger.js') }}"></script>
    
    @stack('scripts')

</body>
</html>

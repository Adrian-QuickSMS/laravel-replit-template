@php
    $action = 'admin_dashboard';
    $adminSession = session('admin_auth', []);
    $adminName = $adminSession['name'] ?? 'Admin User';
    $adminEmail = $adminSession['email'] ?? '';
    $adminRole = $adminSession['role'] ?? 'super_admin';
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'support' => 'Support',
        'finance' => 'Finance',
        'compliance' => 'Compliance',
        'sales' => 'Sales'
    ];
    $adminRoleLabel = $roleLabels[$adminRole] ?? ucfirst($adminRole);
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
    <meta name="admin-user" content="{{ json_encode(['id' => $adminSession['admin_id'] ?? '', 'name' => $adminName, 'email' => $adminEmail, 'role' => $adminRole]) }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="QuickSMS Admin Control Plane" />
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" type="image/png" href="{{asset('images/favicon.png') }}?v={{ time() }}">
    
    <title>QuickSMS Admin | @yield('title', $page_title ?? '')</title> 

    @if(!empty(config('dz.public.global.css'))) 
        @foreach(config('dz.public.global.css') as $style)
            <link href="{{ asset($style) }}" rel="stylesheet" type="text/css"/>
        @endforeach
    @endif

    <link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/admin-control-plane.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/admin-modal-design-system.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/admin-breadcrumb-system.css') }}" rel="stylesheet" type="text/css"/>
    
    @stack('styles')

    <style>
    .nav-header .brand-logo .logo-abbr { display: none; }
    .nav-header .brand-logo .brand-title { margin-left: 0; }
    .menu-toggle .nav-header .brand-logo .logo-abbr,
    [data-sidebar-style="mini"] .nav-header .brand-logo .logo-abbr,
    [data-sidebar-style="icon-hover"] .nav-header .brand-logo .logo-abbr,
    [data-sidebar-style="overlay"] .nav-header .brand-logo .logo-abbr { display: block; }
    .menu-toggle .nav-header .brand-logo .brand-title,
    [data-sidebar-style="mini"] .nav-header .brand-logo .brand-title,
    [data-sidebar-style="icon-hover"] .nav-header .brand-logo .brand-title,
    [data-sidebar-style="overlay"] .nav-header .brand-logo .brand-title { display: none; }
    </style>
</head>
<body class="admin-control-plane admin-console">

    <div id="preloader" style="display: none;">
        <div class="lds-ripple">
            <div></div>
            <div></div>
        </div>
    </div>

    <div id="main-wrapper" class="show">
        <div class="nav-header admin-nav-header">
            <a href="{{ url('/admin') }}" class="brand-logo">
                <img class="logo-abbr" src="{{ asset('images/favicon.png') }}" alt="Q">
                <span class="brand-title">
                    <img src="{{ asset('images/quicksms-logo-white.png') }}" alt="QuickSMS Admin" style="height: 35px; width: auto;">
                </span>
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
            <!-- Admin Control Plane banner hidden per user request -->
            
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
    
    <script src="{{ asset('vendor/apexchart/apexchart.js') }}"></script>
    <script src="{{ asset('js/admin-control-plane.js') }}"></script>
    <script src="{{ asset('js/quicksms-audit-logger.js') }}"></script>
    
    @stack('scripts')

</body>
</html>

@php
    $controller = DzHelper::controller();
    $page = $action = DzHelper::action();
    $action = $controller.'_'.$action;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="keywords" content="" />
        <meta name="author" content="" />
        <meta name="robots" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@yield('page_description', $page_description ?? '')" />
        <meta property="og:title" content="@yield('page_description', $page_description ?? '')" />
        <meta property="og:description" content="@yield('page_description', $page_description ?? '')" />
        <meta property="og:image" content="https:/fillow.dexignlab.com/laravel/social-image.png" />
        <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" type="image/png" href="{{asset('images/favicon.png') }}">
        
        <!-- PAGE TITLE HERE -->
        <title>{{ config('dz.name') }} | @yield('title', $page_title ?? '')</title> 
        
        @if(!empty(config('dz.public.pagelevel.css.'.$action))) 
        @foreach(config('dz.public.pagelevel.css.'.$action) as $style)
            <link href="{{ asset($style) }}" rel="stylesheet" type="text/css"/>
        @endforeach
    @endif

    {{-- Global Theme Styles (used by all pages) --}}
    @if(!empty(config('dz.public.global.css'))) 
        @foreach(config('dz.public.global.css') as $style)
            <link href="{{ asset($style) }}" rel="stylesheet" type="text/css"/>
        @endforeach
    @endif

    {{-- Page-specific styles --}}
    @stack('styles')
        
</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader" style="display: none;">
                <div class="lds-ripple">
                        <div></div>
                        <div></div>
                </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper" class="show">
        <!--**********************************
            Nav header start
        ***********************************-->
                <div class="nav-header">
            <a href="{{ url('/') }}" class="brand-logo">
                <img src="{{ asset('images/quicksms-logo.png') }}" alt="QuickSMS" style="height: 35px; width: auto;">
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->
                
                <!--**********************************
            Chat box start
        ***********************************-->
                @include('elements.header')
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        @yield('sidebar', View::make('elements.sidebar'))
        <!--**********************************
            Sidebar end
        ***********************************-->
                
                <!--**********************************
            Content body start
        ***********************************-->
        @php
            $body_class = ''; 
            if($page == 'ui_button'){ $body_class = 'btn-page';} 
            if($page == 'ui_badge'){ $body_class = 'badge-demo';}
        @endphp
        <div class="content-body default-height qsms-density-compact {{$body_class}} @yield('body_class')">
            <!-- TEST MODE BANNER - Non-dismissible, visible on all pages -->
            <div id="test-mode-activation-banner" class="alert alert-warning alert-dismissible fade show mb-0" role="alert" style="display: none; border-radius: 0; border-left: none; border-right: none; border-top: none;">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lock me-3" style="font-size: 20px; color: #886cc0;"></i>
                            <div>
                                <strong class="d-block">Your account is in Test Mode</strong>
                                <span class="text-muted small">You can send test messages to approved numbers using QuickSMS test settings. To send live messages, complete your account details and activate your account.</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ url('/account/activate') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-rocket me-1"></i> Activate Account
                            </a>
                            <a href="{{ url('/support/knowledge-base/test-mode') }}" class="btn btn-outline-secondary btn-sm">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TEST MODE BANNER -->
            
            <div class="qsms-main">
                <div class="qsms-content-wrap">
                    @yield('content')
                </div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->
        <!--**********************************
            Content body end
        ***********************************-->
                <!-- Modal -->
                @stack('modal')
        <!--**********************************
            Footer start
        ***********************************-->
         @include('elements.footer')
        <!--**********************************
            Footer end
        ***********************************-->

                <!--**********************************
           Support ticket button start
        ***********************************-->
                
        <!--**********************************
           Support ticket button end
        ***********************************-->


        </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
   <!-- Required vendors -->
    @if(!empty(config('dz.public.global.js.top')))
        @foreach(config('dz.public.global.js.top') as $script)
            <script src="{{ asset($script) }}" type="text/javascript"></script>
        @endforeach
    @endif
    @if(!empty(config('dz.public.pagelevel.js.'.$action)))
        @foreach(config('dz.public.pagelevel.js.'.$action) as $script)
            <script src="{{ asset($script) }}" type="text/javascript"></script>
        @endforeach
    @endif
    @if(!empty(config('dz.public.global.js.bottom')))
        @foreach(config('dz.public.global.js.bottom') as $script)
            <script src="{{ asset($script) }}" type="text/javascript"></script>
        @endforeach
    @endif
        
        @stack('scripts')

</body>
</html>
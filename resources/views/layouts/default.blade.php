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
            <!-- TEST MODE BANNER - Collapsible overlay -->
            <div id="test-mode-activation-banner" class="fade show mb-0" role="alert" style="display: none; position: fixed; top: 0; left: 0; right: 0; z-index: 1050; border-radius: 0; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); background: #f0eaf8;">
                <div class="container-fluid" style="padding: 12px 20px;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lock me-3" style="font-size: 20px; color: #6f42c1;"></i>
                            <div>
                                <strong class="d-block" style="color: #000; font-size: 0.95rem;">Your account is in Test Mode</strong>
                                <span class="small" style="color: #000;">You can send test messages to approved numbers using QuickSMS test settings. To send live messages, complete your account details and activate your account.</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ url('/account/activate') }}" class="btn btn-sm" style="background: #886CC0; color: #fff; font-weight: 600; border: none;">
                                <i class="fas fa-rocket me-1"></i> Activate Account
                            </a>
                            <a href="{{ url('/support/knowledge-base/test-mode') }}" class="btn btn-sm" style="background: transparent; color: #000; border: 1px solid rgba(0,0,0,0.25); font-weight: 500;">
                                Learn More
                            </a>
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2" id="test-mode-banner-close" title="Collapse banner" style="color: #6f42c1;">
                                <i class="fas fa-chevron-up" style="font-size: 14px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Collapsed Test Mode Tab -->
            <div id="test-mode-collapsed-tab" style="display: none; position: fixed; top: 0; left: 50%; transform: translateX(-50%); z-index: 1050; cursor: pointer;">
                <div style="background: linear-gradient(135deg, #886cc0, #6f42c1); color: white; padding: 6px 16px; border-radius: 0 0 8px 8px; font-size: 0.8rem; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-lock" style="font-size: 12px;"></i>
                    <span>Test Mode</span>
                    <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
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
    
    <script src="{{ asset('js/quicksms-enforcement-notifications.js') }}" type="text/javascript"></script>
    
    <!-- Test Mode Banner Toggle Handler -->
    <script>
    (function() {
        var STORAGE_KEY = 'quicksms_test_banner_collapsed';
        var testModeBanner = document.getElementById('test-mode-activation-banner');
        var collapsedTab = document.getElementById('test-mode-collapsed-tab');
        var closeBtn = document.getElementById('test-mode-banner-close');
        var contentBody = document.querySelector('.content-body');
        
        function adjustContentPadding() {
            if (contentBody) {
                if (testModeBanner && testModeBanner.style.display !== 'none' && testModeBanner.offsetHeight > 0) {
                    contentBody.style.paddingTop = testModeBanner.offsetHeight + 'px';
                } else if (collapsedTab && collapsedTab.style.display !== 'none') {
                    contentBody.style.paddingTop = '32px';
                } else {
                    contentBody.style.paddingTop = '';
                }
            }
        }
        
        function collapseBanner() {
            if (testModeBanner && collapsedTab) {
                testModeBanner.style.display = 'none';
                collapsedTab.style.display = 'block';
                localStorage.setItem(STORAGE_KEY, 'true');
                adjustContentPadding();
            }
        }
        
        function expandBanner() {
            if (testModeBanner && collapsedTab) {
                collapsedTab.style.display = 'none';
                testModeBanner.style.display = 'block';
                localStorage.setItem(STORAGE_KEY, 'false');
                adjustContentPadding();
            }
        }
        
        // Restore saved state on page load
        function restoreBannerState() {
            var isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
            if (testModeBanner && collapsedTab) {
                if (isCollapsed) {
                    testModeBanner.style.display = 'none';
                    collapsedTab.style.display = 'block';
                } else {
                    collapsedTab.style.display = 'none';
                    testModeBanner.style.display = 'block';
                }
                adjustContentPadding();
            }
        }
        
        // Close button collapses the banner
        if (closeBtn) {
            closeBtn.addEventListener('click', collapseBanner);
        }
        
        // Collapsed tab expands the banner
        if (collapsedTab) {
            collapsedTab.addEventListener('click', expandBanner);
        }
        
        // Observe banner visibility changes to adjust padding
        if (testModeBanner) {
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'style') {
                        setTimeout(adjustContentPadding, 10);
                    }
                });
            });
            observer.observe(testModeBanner, { attributes: true });
            
            // Restore state and adjust padding
            restoreBannerState();
            setTimeout(adjustContentPadding, 100);
        }
    })();
    </script>
        
        @stack('scripts')

</body>
</html>
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('dz.name') }} | @yield('title', $page_title ?? '')</title>

    @if(!empty(config('dz.public.global.css')))
        @foreach(config('dz.public.global.css') as $style)
            <link href="{{ asset($style) }}" rel="stylesheet" type="text/css"/>
        @endforeach
    @endif

    <link href="{{ asset('vendor/toastr/css/toastr.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/quicksms-pastel.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/badge-chip-editor.css') }}" rel="stylesheet" type="text/css"/>

    @stack('styles')

    <style>
        body {
            background: #fff !important;
            overflow-y: auto !important;
            margin: 0;
            padding: 0;
        }
        #main-wrapper,
        .content-body {
            margin: 0 !important;
            padding: 0 !important;
        }
        .container-fluid {
            padding: 1rem 1.5rem !important;
        }
        .page-titles { display: none !important; }
        .nav-header,
        .chatbox,
        .header,
        .deznav,
        .footer { display: none !important; }
    </style>
</head>
<body>
    <div id="main-wrapper" class="show">
        <div class="content-body" style="margin-left: 0 !important; min-height: auto !important;">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    @if(!empty(config('dz.public.global.js')))
        @foreach(config('dz.public.global.js') as $script)
            <script src="{{ asset($script) }}" type="text/javascript"></script>
        @endforeach
    @endif

    <script src="{{ asset('js/badge-chip-editor.js') }}"></script>

    @stack('scripts')
</body>
</html>

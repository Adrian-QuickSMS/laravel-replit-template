<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('dz.name') }} | @yield('title', $page_title ?? '')</title>

    {{-- Core theme CSS (Bootstrap + theme) --}}
    <link href="{{ asset('vendor/bootstrap-select/css/bootstrap-select.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('vendor/toastr/css/toastr.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/quicksms-pastel.css') }}" rel="stylesheet" type="text/css"/>

    @stack('styles')

    <style>
        /* Remove all sidebar/header chrome for embedded context */
        body {
            background: #fff !important;
            overflow-y: auto !important;
        }
        .container-fluid {
            padding: 1rem 1.5rem !important;
        }
        /* Hide breadcrumbs in embed */
        .page-titles { display: none !important; }
    </style>
</head>
<body>
    @yield('content')

    {{-- Core JS (jQuery + Bootstrap) --}}
    <script src="{{ asset('vendor/global/global.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('vendor/bootstrap-select/js/bootstrap-select.min.js') }}" type="text/javascript"></script>

    @stack('scripts')
</body>
</html>

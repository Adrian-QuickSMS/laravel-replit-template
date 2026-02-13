@php
    $controller = DzHelper::controller();
    $page = $action = DzHelper::action();
    $action = $controller.'_'.$action;
@endphp

<!DOCTYPE html>
<html lang="en" class="h-100">

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
    
</head>

<body class="vh-100">
    <div class="authincation h-100">
		<div class="container ">
			<div class="row justify-content-center h-100 align-items-center">
				@yield('content')
			</div>
		</div>
    </div>
	
<!--**********************************
	Scripts
***********************************-->
<!-- Required vendors -->
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
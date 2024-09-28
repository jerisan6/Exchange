<!DOCTYPE html>
<html lang="{{ get_default_language_code() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $current_url = URL::current();
    @endphp

    @if($current_url == setRoute('index'))
        <title>{{$basic_settings->site_name ?? ''}}  - {{ $basic_settings->site_title ?? "" }}</title>
    @else
        <title>{{$basic_settings->site_name ?? ''}}  {{ $page_title ?? '' }}</title>
    @endif
    @include('partials.header-asset')
    @stack('css')
</head>
<body class="{{ get_default_language_dir() }}">
    
@include('frontend.partials.preloader')


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Body Overlay
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div id="body-overlay" class="body-overlay"></div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Body Overlay
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@yield('content')


@include('partials.footer-asset')
@stack('script')

</body>
</html>
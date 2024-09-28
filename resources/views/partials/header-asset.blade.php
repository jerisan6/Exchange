<link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"> 
<!-- fontawesome css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/css/fontawesome-all.css') }}">
<!-- bootstrap css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/css/bootstrap.css') }}">
<!-- favicon -->
<link rel="shortcut icon" href="{{ get_fav($basic_settings) ?? "" }}" type="image/x-icon">
<!-- odometer css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/css/odometer.css') }}">
<!-- lightcase css links -->
<link rel="stylesheet" href="{{ asset('public/frontend/css/lightcase.css') }}">
<!-- line-awesome-icon css -->
<link rel="stylesheet" href="{{ asset('public/frontend/css/line-awesome.css') }}">
<!-- animate.css -->
<link rel="stylesheet" href="{{ asset('public/frontend/css/animate.css') }}">
<!-- nice select css -->
<link rel="stylesheet" href="{{ asset('public/frontend/css/nice-select.css') }}">
 <!-- Popup  -->
 <link rel="stylesheet" href="{{ asset('public/backend/library/popup/magnific-popup.css') }}">
  <!-- select2 css -->
  <link rel="stylesheet" href="{{ asset('public/frontend/css/select2.css') }}">
<!-- main style css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/css/style.css') }}">
<!-- file holder css -->
<link rel="stylesheet" href="https://appdevs.cloud/cdn/fileholder/v1.0/css/fileholder-style.css" type="text/css">

@php
    $base_color = $basic_settings->base_color;
@endphp
<style>
    :root {
        --primary-color: {{ $base_color }};
    }
</style>


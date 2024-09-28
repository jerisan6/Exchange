
@extends('layouts.master')

@push('css')
    
@endpush

@section('content')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Start Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div class="account-section">
    <div class="account-inner">
        <div class="account-area change-form">
            <div class="account-thumb">
                <img src="{{ asset('public/frontend/images/element/account.png') }}" alt="element">
            </div>
            <div class="account-form-area">
                <div class="account-logo">
                    <a class="site-logo site-title" href="{{ setRoute('index') }}"><img src="{{ get_logo($basic_settings) }}" alt="site-logo"></a>
                </div>
                <h4 class="title">{{ __("Reset Your Forgotten Password?") }}</h4>
                <p>{{ __("Take control of your account by resetting your password. Our password recovery page guides you through the necessary steps to securely reset your password.") }}</p>
                <form action="{{ setRoute('user.password.forgot.send.code') }}" class="account-form" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 form-group">
                            <input type="email" class="form-control form--control" name="credentials" placeholder="{{ __("Enter Email") }}..." required>
                        </div>
                        <div class="col-lg-12 form-group text-center">
                            <button type="submit" class="btn--base w-100">{{ __("Send OTP") }}</button>
                        </div>
                        <div class="col-lg-12 text-center">
                            <div class="account-item">
                                <label>{{ __("Back To") }} <a href="{{ setRoute('user.login') }}">{{ __("Login") }}</a></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection

@push('script')
    
@endpush
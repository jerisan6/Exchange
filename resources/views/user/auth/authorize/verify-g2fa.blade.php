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
                <img src="{{ asset("public/frontend/images/element/account.png") }}" alt="element">
            </div>
            <div class="account-form-area">
                <div class="account-logo">
                    <a class="site-logo site-title" href="{{ setRoute('index') }}"><img src="{{ get_logo($basic_settings) }}" alt="site-logo"></a>
                </div>
                <h4 class="title">{{ __("Two Factor Authorization") }}</h4>
                <p>{{ __("Please enter your authorization code to access dashboard") }}</p>
                <form action="{{ setRoute('user.authorize.google.2fa.submit') }}" method="POST" class="account-form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 form-group otp-form">
                            <input class="otp" type="text"  name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(1)'
                                        maxlength=1 required>
                            <input class="otp" type="text"  name="code[]"  oninput='digitValidate(this)' onkeyup='tabChange(2)'
                                maxlength=1 required>
                            <input class="otp" type="text"  name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(3)'
                                maxlength=1 required>
                            <input class="otp" type="text"  name="code[]"  oninput='digitValidate(this)' onkeyup='tabChange(4)'
                                maxlength=1 required>
                            <input class="otp" type="text"  name="code[]"  oninput='digitValidate(this)' onkeyup='tabChange(5)'
                                maxlength=1 required>
                            <input class="otp" type="text"  name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(6)'
                                maxlength=1 required>
                        </div>
                       
                        <div class="col-lg-12 form-group text-center">
                            <button type="submit" class="btn--base w-100">{{ __("Submit") }}</button>
                        </div>
                        <div class="col-lg-12 text-center">
                            <div class="account-item">
                                <label>{{ __("Back To") }} <a href="{{ setRoute('index') }}">{{ __("Login") }}</a></label>
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
<script>
    let digitValidate = function (ele) {
      ele.value = ele.value.replace(/[^0-9]/g, '');
  }

  let tabChange = function (val) {
      let ele = document.querySelectorAll('.otp');
      if (ele[val - 1].value != '') {
          ele[val].focus()
      } else if (ele[val - 1].value == '') {
          ele[val - 2].focus()
      }
  }
</script>
@endpush
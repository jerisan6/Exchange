@php
    $app_local      = get_default_language_code();
    $slug           = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::REGISTER_SECTION);
    $register       = App\Models\Admin\SiteSections::getData($slug)->first();
@endphp
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
                <img src="{{ get_image($register->value->image , 'site-section') }}" alt="element">
            </div>
            <div class="account-form-area">
                <div class="account-logo">
                    <a class="site-logo site-title" href="{{ setRoute('index') }}"><img src="{{ get_logo($basic_settings) }}" alt="site-logo"></a>
                </div>
                <h4 class="title">{{ @$register->value->language->$app_local->title ?? '' }}</h4>
                <p>{{ @$register->value->language->$app_local->heading ?? '' }}</p>
                <form action="{{ setRoute('user.register.submit') }}" class="account-form" method="POST" autocomplete="on">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 col-md-12 form-group">
                            <input type="text" class="form-control form--control" name="firstname" value="{{ old('firstname') }}" placeholder="{{ __("First Name") }}" required>
                        </div>
                        <div class="col-lg-6 col-md-12 form-group">
                            <input type="text" class="form-control form--control" name="lastname" value="{{ old('lastname') }}" placeholder="{{ __("Last Name") }}" required>
                        </div>
                        <div class="col-lg-12 form-group">
                            <input type="email" class="form-control form--control" name="email" value="{{ old('email') }}" placeholder="{{ __("Email") }}" required>
                        </div>
                        <div class="col-lg-12 form-group show_hide_password">
                            <input type="password" class="form-control form--control" name="password" value="{{ old('password') }}" placeholder="{{ __("Password") }}" required>
                            <span class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></span>
                        </div>
                        <div class="col-lg-12 form-group">
                            <div class="custom-check-group">
                                <input type="checkbox" name="agree" id="level-1">
                                @php
                                    $data = App\Models\Admin\UsefulLink::where('type',global_const()::USEFUL_LINK_PRIVACY_POLICY)->first();
                                @endphp
                                <label for="level-1">{{ __("I have agreed with") }} <a class="text--base" href="{{ setRoute('link',$data->slug) }}" target="_blank">{{ __("Terms Of Use & Privacy Policy") }}</a></label>
                            </div>
                        </div>
                        <div class="col-lg-12 form-group text-center">
                            <button type="submit" class="btn--base w-100"><span class="w-100">{{ __("Register Now") }}</span></button>
                        </div>
                        <div class="col-lg-12 text-center">
                            <div class="account-item">
                                <label>{{ __("Already Have An Account?") }} <a href="{{ setRoute('user.login') }}" class="account-control-btn">{{ __("Login Now") }}</a></label>
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
    function deRequireCb(elClass) {
  el = document.getElementsByClassName(elClass);

  var atLeastOneChecked = false; //at least one cb is checked
  for (i = 0; i < el.length; i++) {
    if (el[i].checked === true) {
      atLeastOneChecked = true;
    }
  }

  if (atLeastOneChecked === true) {
    for (i = 0; i < el.length; i++) {
      el[i].required = false;
    }
  } else {
    for (i = 0; i < el.length; i++) {
      el[i].required = true;
    }
  }
}
</script>
@endpush
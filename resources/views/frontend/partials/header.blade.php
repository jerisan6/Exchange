@php
    $menues = App\Models\Admin\SetupPage::where('status',true)->get();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<header class="header-section {{ $class ?? "two" }}">
    <div class="header">
        <div class="header-bottom-area">
            <div class="container">
                <div class="header-menu-content">
                    <nav class="navbar navbar-expand-lg p-0">
                        <a class="site-logo site-title" href="{{ setRoute('index') }}"><img src="{{ get_logo($basic_settings) }}" alt="site-logo"></a>
                        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="las la-bars"></span>
                        </button>
                        @php
                            $current_url = URL::current();
                        @endphp
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav main-menu ms-auto me-auto">
                                @foreach ($menues as $item)
                                    @php
                                        $title          = $item->title ?? "";
                                        $menu_active    = $item->menu_active ?? [];
                                    @endphp
                                    <li><a href="{{ url($item->url) }}" class=" @if(in_array(Route::currentRouteName(),$menu_active)) active @endif ">{{ __($title) }}</a></li>
                                @endforeach
                            </ul>
                            <div class="header-language">
                                @php
                                    $__current_local = session("local") ?? get_default_language_code();
                                @endphp
                                <select class="form--control nice-select" name="lang_switcher" id="">
                                    @foreach ($__languages as $__item)
                                        <option value="{{ $__item->code }}" @if ($__current_local == $__item->code)
                                            @selected(true)
                                        @endif>{{ $__item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="header-action">
                                @auth
                                    <a href="{{ setRoute('user.dashboard')}}" class="btn--base">{{ __("Dashboard") }}</a>
                                @else
                                    <a href="{{ setRoute('user.login')}}" class="btn--base active">
                                        <svg enable-background="new 0 0 26 26" id="Слой_1" version="1.1" viewBox="0 0 26 26" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M16.8035889,14.4605103c2.0820923-1.2811279,3.4776611-3.5723267,3.4776611-6.1885376  C20.28125,4.262207,17.0146484,1,13,1S5.71875,4.262207,5.71875,8.2719727c0,2.6162109,1.3955688,4.9074097,3.4776611,6.1885376  c-4.4957886,1.0071411-7.6505127,3.7583618-7.6505127,7.0878296C1.5458984,24.2729492,8.7460938,25,13,25  s11.4541016-0.7270508,11.4541016-3.4516602C24.4541016,18.2188721,21.2993774,15.4676514,16.8035889,14.4605103z   M7.21875,8.2719727C7.21875,5.0893555,9.8125,2.5,13,2.5s5.78125,2.5893555,5.78125,5.7719727S16.1875,14.043457,13,14.043457  S7.21875,11.4545898,7.21875,8.2719727z M13,23.5c-6.1149902,0-9.7753906-1.289978-9.9536743-1.9567261  C3.0509644,18.2345581,7.5145874,15.543457,13,15.543457c5.4848633,0,9.9481201,2.6906128,9.9536133,5.9988403  C22.7797852,22.2085571,19.1190186,23.5,13,23.5z" fill="#ffffff"/></svg>
                                        {{ __("Join Now") }}
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@push('script')
<script>
    $("select[name=lang_switcher]").change(function(){
        var selected_value = $(this).val();
        var submitForm = `<form action="{{ setRoute('languages.switch') }}" id="local_submit" method="POST"> @csrf <input type="hidden" name="target" value="${$(this).val()}" ></form>`;
        $("body").append(submitForm);
        $("#local_submit").submit();
    });
</script>
@endpush
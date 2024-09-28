@php
    $app_local  = get_default_language_code();
    $slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::FOOTER_SECTION);
    $footer = App\Models\Admin\SiteSections::getData($slug)->first();
    $newsletter_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::NEWSLETTER_SECTION);
    $news_letter = App\Models\Admin\SiteSections::getData($newsletter_slug)->first();
    $menues = DB::table('setup_pages')->where('status', 1)->get();
    $useful_links       = App\Models\Admin\UsefulLink::where('status',true)->get()
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Footer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<footer class="footer-section pt-80">
    <div class="footer-top-area">
        <div class="container">
            <div class="row justify-content-center mb-30-none">
                <div class="col-xl-4 col-lg-4 col-md-6 mb-30">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <a class="site-logo site-title" href="{{ setRoute('index') }}"><img src="{{ @$footer->value->footer->image ? get_image(@$footer->value->footer->image,'site-section') : get_logo($basic_settings) }}" alt="site-logo"></a>
                        </div>
                        <p>{{ @$footer->value->footer->language->$app_local->description ?? '' }}</p>
                        <ul class="footer-social">
                            @foreach (@$footer->value->social_links ?? [] as $item)
                                <li><a href="{{ $item->link ?? '' }}"><i class="{{ $item->icon ?? '' }}"></i></a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 mb-30">
                    <div class="footer-widget">
                        <h4 class="widget-title">{{ __("Menus") }}</h4>
                        
                        <ul class="footer-list">
                            @foreach ($menues as $item)
                            @php
                                $title = $item->title ?? "";
                            @endphp
                                <li><a href="{{ url($item->url) }}">{{ __($title) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 mb-30">
                    <div class="footer-widget">
                        <h4 class="widget-title">{{ @$news_letter->value->language->$app_local->title }}</h4>
                        <p>{{ @$news_letter->value->language->$app_local->description }}</p>
                        <form id="subscribe-form" class="subscribe-form" action="{{ setRoute('subscribe') }} " method="POST">
                            @csrf
                            <div class="form-group">
                                <input type="email" name="email" class="form--control" placeholder="{{ __("Email Address") }}...">
                                <button type="submit" class="btn--base subscribe-btn">{{ __("Subscribe") }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom-area">
        <div class="container">
            <div class="footer-bottom-wrapper">
                <ul class="footer-list">
                    @foreach (@$useful_links ?? [] as $item)
                        <li><a href="{{ setRoute('link',$item->slug) }}">{{ @$item->title->language->$app_local->title }}</a></li>
                    @endforeach
                </ul>
                <div class="copyright-area">
                    <p>© 2024 <a href="{{ setRoute('index') }}">{{ $basic_settings->site_name }}</a> {{ __("is Proudly Powered by AppDevs") }}</p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Footer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
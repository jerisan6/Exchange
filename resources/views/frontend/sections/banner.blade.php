@php
    $app_local  = get_default_language_code();
    $slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::BANNER_SECTION);
    $banner = App\Models\Admin\SiteSections::getData($slug)->first();
@endphp


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="banner-section">
    <div class="container">
        <div class="row justify-content-center align-items-center mb-30-none">
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="banner-content-wrapper">
                    <div class="banner-content">
                        <span class="title-badge"></span>
                        @php
                            $heading        = explode('[',@$banner->value->language->$app_local->heading ?? '' );
                            $middle_text    = explode(']',@$heading[1] ?? '');
                            
                        @endphp
                        <h5 class="sub-title">{{ @$banner->value->language->$app_local->title ?? '' }}</h5>
                        <h1 class="title">{{ isset($heading[0]) ? $heading[0] : ''  }} <span>{{ isset($middle_text[0]) ? $middle_text[0] : '' }}</span>{{ isset($middle_text[1]) ? $middle_text[1] : '' }}</h1>
                        <p>{{ @$banner->value->language->$app_local->sub_heading ?? '' }}</p>
                        <div class="banner-btn">
                            <a href="{{ setRoute('user.register') }}" class="btn--base">{{ @$banner->value->language->$app_local->button_name ?? '' }}</a>
                            <a class="video-icon" data-rel="lightcase:myCollection" href="{{ @$banner->value->button_link ?? '' }}">
                                <img src="{{ get_image(@$banner->value->button_image , 'site-section') }}" alt="element">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="banner-thumb">
                    <img src="{{ get_image(@$banner->value->image , 'site-section') }}" alt="banner">
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
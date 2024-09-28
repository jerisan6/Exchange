@php
    $app_local      = get_default_language_code();
    $slug           = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::DOWNLOAD_APP_SECTION);
    $download_app   = App\Models\Admin\SiteSections::getData($slug)->first();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start App
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="app-section pt-120">
    <div class="container">
        <div class="row justify-content-center align-items-center mb-30-none">
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="app-thumb">
                    <img src="{{ get_image(@$download_app->value->image , 'site-section') }}" alt="element">
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="app-content">
                    <div class="section-header">
                        <span class="title-badge">$</span>
                        <h5 class="section-sub-title">{{ @$download_app->value->language->$app_local->title }}</h5>
                        @php
                            $heading   = explode('|',@$download_app->value->language->$app_local->heading);
                        @endphp
                        <h2 class="section-title">{{ isset($heading[0]) ? $heading[0] : '' }} <span>{{ isset($heading[1]) ? $heading[1] : '' }}</span></h2>
                    </div>
                    <p>{{ @$download_app->value->language->$app_local->sub_heading }}</p>
                    <div class="app-btn-wrapper">
                        @foreach (@$download_app->value->items ?? [] as $item)
                            <a href="{{ $item->link }}" class="app-btn">
                                <div class="content">
                                    <h5 class="title">{{ $item->language->$app_local->item_title ?? ''}}</h5>
                                </div>
                                <div class="icon">
                                    <img src="{{ get_image($item->image , 'site-section') }}" alt="element">
                                </div>
                                <div class="app-qr">
                                    <img src="{{ get_image($item->image , 'site-section') }}" alt="element">
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End App
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
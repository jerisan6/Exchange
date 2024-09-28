@php
    $app_local      = get_default_language_code();
    $slug           = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::HOW_ITS_WORK_SECTION);
    $how_its_work   = App\Models\Admin\SiteSections::getData($slug)->first();
@endphp

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start How it works
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="how-it-works-section section--bg ptb-120">
    <div class="container">
        <div class="row justify-content-center align-items-center mb-30-none">
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="how-it-works-thumb">
                    <img src="{{ get_image(@$how_its_work->value->image , 'site-section') }}" alt="element">
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="section-header">
                    <span class="title-badge">$</span>
                    <h5 class="section-sub-title">{{ @$how_its_work->value->language->$app_local->title ?? '' }}</h5>
                    @php
                        $heading   = explode('|' , @$how_its_work->value->language->$app_local->heading);
                    @endphp
                    <h2 class="section-title">{{ isset($heading[0]) ? $heading[0] : '' }} <span>{{ isset($heading[1]) ? $heading[1] : '' }}</span></h2>
                    <p>{{ @$how_its_work->value->language->$app_local->sub_heading ?? '' }}</p>
                </div>
                <div class="how-it-works-item-wrapper">
                    @foreach ($how_its_work->value->items ?? [] as $item)
                        <div class="how-it-works-item">
                            <div class="how-it-works-icon">
                                <i class="{{ $item->icon }}"></i>
                            </div>
                            <div class="how-it-works-content">
                                <h3 class="title">{{ $item->language->$app_local->item_title ?? '' }}</h3>
                                <p>{{ $item->language->$app_local->item_heading ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End How it works
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
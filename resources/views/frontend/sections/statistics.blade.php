@php
    $app_local  = get_default_language_code();
    $slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::STATISTIC_SECTION);
    $statistic = App\Models\Admin\SiteSections::getData($slug)->first();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Roadmap
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="statistics-section ptb-120">
    <div class="container">
        <div class="statistics-area bg_img" data-background="{{ asset('public/frontend/images/element/overview-bg.png') }}">
            <div class="row justify-content-center align-items-center mb-30-none">
                <div class="col-xl-6 col-lg-6 mb-30">
                    <div class="section-header">
                        <span class="title-badge">$</span>
                        <h5 class="section-sub-title">{{ @$statistic->value->language->$app_local->title }}</h5>
                        @php
                            $heading    = explode('|', @$statistic->value->language->$app_local->heading);
                        @endphp
                        <h2 class="section-title">{{ isset($heading[0]) ? $heading[0] : '' }} <span>{{ isset($heading[1]) ? $heading[1] : '' }}</span></h2>
                    </div>
                    <p>{{ @$statistic->value->language->$app_local->sub_heading }}</p>
                    <div class="statistics-wrapper">
                        <div class="row mb-30-none">
                            @foreach (@$statistic->value->items ?? [] as $item)
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 mb-30">
                                    <div class="statistics-item">
                                        <div class="statistics-content">
                                            <div class="odo-area">
                                                @php
                                                    $counter_value  = numeric_unit_converter($item->counter_value);
                                                @endphp
                                                <h3 class="odo-title odometer" data-odometer-final="{{ $counter_value->number }}">0</h3>
                                                <h3 class="title">{{ $counter_value->unit }}</h3>
                                            </div>
                                            <p>{{ @$item->language->$app_local->title }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 mb-30">
                    <div class="statistics-thumb">
                        <img src="{{ get_image(@$statistic->value->image , 'site-section') }}" alt="element">
                        <img src="{{ get_image(@$statistic->value->image , 'site-section') }}" alt="element">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Roadmap
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
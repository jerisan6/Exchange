@php
    $app_local      = get_default_language_code();
    $slug           = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::ABOUT_SECTION);
    $about          = App\Models\Admin\SiteSections::getData($slug)->first();
    $faq_slug       = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::FAQ_SECTION);
    $faq            = App\Models\Admin\SiteSections::getData($faq_slug)->first();
@endphp

@extends('frontend.layouts.master')

@push("css")
    
@endpush

@section('content') 

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start About
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="about-section ptb-120">
    <div class="container">
        <div class="row justify-content-center align-items-center mb-30-none">
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="about-thumb">
                    <img src="{{ get_image(@$about->value->image , 'site-section') }}" alt="element">
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="about-content">
                    <div class="section-header">
                        <span class="title-badge">$</span>
                        <h5 class="section-sub-title">{{ @$about->value->language->$app_local->title }}</h5>
                        @php
                            $heading    = explode('|',@$about->value->language->$app_local->heading);
                        @endphp
                        <h2 class="section-title">{{ isset($heading[0]) ? $heading[0] : '' }} <span>{{ isset($heading[1]) ? $heading[1] : '' }}</span></h2>
                    </div>
                    <p>{{ @$about->value->language->$app_local->sub_heading }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End About
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Faq
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="faq-section section--bg ptb-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 text-center">
                <div class="section-header">
                    <span class="title-badge">$</span>
                    <h5 class="section-sub-title">{{ @$faq->value->language->$app_local->title }}</h5>
                    @php
                        $heading   = explode('|', @$faq->value->language->$app_local->heading);
                    @endphp
                    <h2 class="section-title">{{ isset($heading[0]) ? $heading[0] : '' }} <span>{{ isset($heading[1]) ? $heading[1] : '' }}</span></h2>
                </div>
            </div>
        </div>
        @php
            $items      = @$faq->value->items;
            $itemData   = (array) $items;
            if ($itemData != []) {
                $data = array_chunk($itemData, ceil(count($itemData) / 2));
                $part1 = $data[0];
                $part2 = $data[1];
            }
        @endphp
        <div class="row justify-content-center mb-30-none">
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="faq-wrapper">
                    @foreach ($part1 ?? [] as $item)
                        @if ($item->status == 1)
                        <div class="faq-item">
                            <h6 class="faq-title"><span class="title">{{ @$item->language->$app_local->question }}</span><span class="right-icon"></span></h6>
                            <div class="faq-content">
                                <p>{{ @$item->language->$app_local->answer }}</p>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="faq-wrapper">
                    @foreach (@$part2 ?? [] as $item)
                        @if (@$item->status ==  1)
                        <div class="faq-item">
                            <h6 class="faq-title"><span class="title">{{ @$item->language->$app_local->question }}</span><span class="right-icon"></span></h6>
                            <div class="faq-content">
                                <p>{{ @$item->language->$app_local->answer }}</p>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Faq
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection
@php
    $app_local      = get_default_language_code();
    $slug           = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::SERVICE_SECTION);
    $service        = App\Models\Admin\SiteSections::getData($slug)->first();
@endphp

@extends('frontend.layouts.master')

@push("css")
    
@endpush

@section('content') 


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="service-section ptb-120 section--bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 text-center">
                <div class="section-header">
                    <span class="title-badge">$</span>
                    <h5 class="section-sub-title">{{ @$service->value->language->$app_local->title ?? '' }}</h5>
                    @php
                        $heading    = explode('|' , @$service->value->language->$app_local->heading);
                    @endphp
                    <h2 class="section-title">{{ isset($heading[0]) ? $heading[0] : '' }} <span>{{ isset($heading[1]) ? $heading[1] : '' }}</span></h2>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mb-30-none">
            @foreach (@$service->value->items ?? [] as $item)
                @if ($item->status == 1)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-30">
                        <div class="service-item">
                            <div class="service-icon">
                                <i class="{{ $item->icon }}"></i>
                            </div>
                            <div class="service-content">
                                <h3 class="title">{{ $item->language->$app_local->item_title }}</h3>
                                <p>{{ $item->language->$app_local->item_heading }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@endsection
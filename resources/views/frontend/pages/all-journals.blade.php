@php
    $app_local      = get_default_language_code();
@endphp
@extends('frontend.layouts.master')

@push("css")
    
@endpush

@section('content') 


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Blog
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="blog-section section--bg ptb-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 text-center">
                <div class="section-header">
                    <h5 class="section-sub-title">{{ __("All Journals") }}</h5>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mb-30-none">
            @foreach ($blogs ?? [] as $item)
                <div class="col-xl-4 col-lg-6 col-md-6 mb-30">
                    <div class="blog-item">
                        <div class="blog-thumb">
                            <img src="{{ get_image($item->data->image ,'site-section') }}" alt="blog">
                        </div>
                        <div class="blog-content">
                           
                            <span class="date"><i class="las la-calendar"></i> {{ \Carbon\Carbon::parse($item->created_at)->format('F j, Y') }}</span>
                            <h5 class="title"><a href="{{ setRoute('journal.details',$item->slug) }}">{{ Str::words($item->data->language->$app_local->title ?? "","5","...") }}</a></h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{ get_paginate($blogs) }}
        
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Blog
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@endsection
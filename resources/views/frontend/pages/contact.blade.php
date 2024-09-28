@php
    $app_local  = get_default_language_code();
    $slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::CONTACT_SECTION);
    $contact = App\Models\Admin\SiteSections::getData($slug)->first();
@endphp

@extends('frontend.layouts.master')

@push("css")
    
@endpush

@section('content') 


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Contact
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div class="contact-section section--bg ptb-120">
    <div class="container">
        <div class="contact-area">
            <div class="contact-wrapper">
                <div class="row justify-content-center align-items-center mb-30-none">
                    <div class="col-xl-6 col-lg-6 mb-30">
                        <div class="contact-thumb">
                            <img src="{{ get_image(@$contact->value->image , 'site-section') }}" alt="element">
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 mb-30">
                        <div class="contact-form-area">
                            <div class="section-header">
                                <span class="title-badge">$</span>
                                <h5 class="section-sub-title">{{ @$contact->value->language->$app_local->title ?? '' }}</h5>
                                @php
                                    $heading    = explode('|' , @$contact->value->language->$app_local->heading); 
                                @endphp
                                <h2 class="section-title">{{ isset($heading[0]) ? $heading[0] : '' }} <span>{{ isset($heading[1]) ? $heading[1] : '' }}</span></h2>
                            </div>
                            <form class="contact-form" action="{{ setRoute('contact.request') }}" method="POST">
                                @csrf
                                <div class="row justify-content-center mb-10-none">
                                    <div class="col-xl-6 col-lg-6 col-md-12 form-group">
                                        <label>{{ __("Name") }}<span>*</span></label>
                                        <input type="text" name="name" class="form--control" placeholder="{{ __("Enter Name") }}...">
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-12 form-group">
                                        <label>{{ __("Email") }}<span>*</span></label>
                                        <input type="email" name="email" class="form--control" placeholder="{{ __("Enter Email") }}...">
                                    </div>
                                    <div class="col-xl-12 col-lg-12 form-group">
                                        <label>{{ __("Message") }}<span>*</span></label>
                                        <textarea class="form--control" name="message" placeholder="{{ __("Write Here") }}..."></textarea>
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <button type="submit" class="btn--base mt-10"><span>{{ __("Send Message") }}</span></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Contact
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@endsection
@php
    $app_local      = get_default_language_code();
    $slug           = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::CALL_TO_ACTION_SECTION);
    $call_to_action = App\Models\Admin\SiteSections::getData($slug)->first();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start CallToAction
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="call-to-action-section section--bg ptb-120">
    <div class="call-to-action-element">
        <img src="{{ asset('public/frontend/images/element/wel-map.png') }}" alt="element">
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 text-center">
                <div class="call-to-action-wrapper">
                    <div class="call-to-action-marker">
                        <img src="{{ get_image(@$call_to_action->value->image , 'site-section') }}" alt="element">
                    </div>
                    <div class="call-to-action-content">
                        <h2 class="title">{{ @$call_to_action->value->language->$app_local->heading }}</h2>
                        <p>{{ @$call_to_action->value->language->$app_local->sub_heading }}</p>
                        <div class="call-to-action-btn">
                            <a href="{{ setRoute('contact') }}" class="btn--base">{{ @$call_to_action->value->language->$app_local->button_name }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End CallToAction
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
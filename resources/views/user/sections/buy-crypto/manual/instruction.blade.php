@extends('user.layouts.master')

@push('css')
    
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Buy Crypto Manual")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row justify-content-center mt-30">
        <div class="col-xxl-6 col-xl-8 col-lg-8">
            <div class="custom-card">
                <div class="dashboard-header-wrapper">
                    <h5 class="title">{{ __("Buy Crypto") }}</h5>
                </div>

                <form class="row g-4 submit-form" method="POST" action="{{ setRoute('user.buy.crypto.manual.submit',$token) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <p>{!! $gateway->desc !!}</p>
                        @include('user.components.payment-gateway.generate-dy-input',['input_fields' => array_reverse($gateway->input_fields)])
                    </div>

                    <div class="col-12 mt-5">
                        <button type="submit" class="btn--base w-100 text-center">{{ __("Submit") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')

@endpush
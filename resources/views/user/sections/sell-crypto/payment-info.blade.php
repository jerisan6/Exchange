@extends('user.layouts.master')

@push('css')
    
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Sell Crypto Payment Info")])
@endsection

@section('content')
<div class="body-wrapper">
    @if ($data->data->sender_wallet->type  == global_const()::INSIDE_WALLET)
    <form action="{{ setRoute('user.sell.crypto.payment.info.store',$data->identifier) }}" class="card-form-area" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row justify-content-center mt-30 mb-20-none">
            <div class="col-xl-6 col-lg-6 mb-20">
                <div class="custom-card">
                    <div class="dashboard-header-wrapper">
                        <h5 class="title">{{ __("Receiving Method Information") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-form">
                            <div class="row">
                                <p>{!! $gateway->desc !!}</p>
                                @include('user.components.payment-gateway.generate-dy-input',['input_fields' => array_reverse($gateway->input_fields)])
                            </div>
                            <div class="col-xl-12 col-lg-12">
                                <button type="submit" class="btn--base mt-10 w-100"><span class="w-100">{{ __("Continue") }}</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @else
    <form action="{{ setRoute('user.sell.crypto.payment.info.store',$data->identifier) }}" class="card-form-area" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row justify-content-center mt-30 mb-20-none">
            <div class="col-xl-6 col-lg-6 mb-20">
                <div class="custom-card">
                    <div class="dashboard-header-wrapper">
                        <h5 class="title">{{ __("Payment Proof") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-form">
                            <div class="row mb-20-none">
                                <p>{!! $outside_wallet->desc !!}</p>
                                @include('user.components.payment-gateway.generate-dy-input',['input_fields' => array_reverse($outside_wallet->input_fields)])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-20">
                <div class="custom-card">
                    <div class="dashboard-header-wrapper">
                        <h5 class="title">{{ __("Receiving Method Information") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-form">
                            <div class="row">
                                <p>{!! $gateway->desc !!}</p>
                                @include('user.components.payment-gateway.generate-dy-input',['input_fields' => array_reverse($gateway->input_fields)])
                            </div>
                            <div class="col-xl-12 col-lg-12">
                                <button type="submit" class="btn--base mt-10 w-100"><span class="w-100">{{ __("Continue") }}</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif
    
</div>
@endsection
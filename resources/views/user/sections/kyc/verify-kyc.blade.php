@php
    $default = get_default_language_code();
@endphp
@extends('user.layouts.master')
@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("KYC Verification")])
@endsection
@section('content')
<div class="body-wrapper">
    <div class="row mb-30-none justify-content-center">
        <div class="col-lg-6 mb-30">
            <div class="custom-card mt-20">
                <div class="card-body">
                    <div class="dash-payment-item-wrapper">
                        <div class="dash-payment-item active">
                            <div class="dash-payment-title-area">
                                <h5 class="title">{{ __("Proof Of Identity") }}</h5>
                            </div>
                            <div class="dash-payment-body">
                                @include('user.components.profile.kyc', compact('user_kyc'))
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')

@endpush

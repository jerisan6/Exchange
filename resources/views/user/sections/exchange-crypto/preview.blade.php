@extends('user.layouts.master')

@push('css')
    
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Exchange Crypto Preview")])
@endsection

@section('content')

<div class="body-wrapper">
    <div class="row justify-content-center mt-30">
        <div class="col-xxl-6 col-xl-8 col-lg-8">
            <div class="custom-card">
                <div class="dashboard-header-wrapper">
                    <h4 class="title">{{ __("Summary") }}</h4>
                </div>
                <form action="{{ setRoute('user.exchange.crypto.confirm',$data->identifier) }}" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-keyboard"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("From Wallet") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ $data->data->sender_wallet->name ?? '' }} ({{ $data->data->sender_wallet->code ?? '' }})</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hockey-puck"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("To Wallet") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ $data->data->receiver_wallet->name ?? '' }} ({{ $data->data->receiver_wallet->code ?? '' }})</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-wallet"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Enter Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--success">{{ $data->data->sending_amount ?? '' }} {{ $data->data->sender_wallet->code ?? '' }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-exchange-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Exchange Rate") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--warning">1 {{ $data->data->sender_wallet->code ?? '' }} = {{ get_amount($data->data->exchange_rate) ?? '' }} {{ $data->data->receiver_wallet->code ?? '' }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Network Fees") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--danger">{{ $data->data->total_charge ?? '' }} {{ $data->data->sender_wallet->code ?? '' }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="last">{{ __("Total Payable Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="last">{{ $data->data->payable_amount ?? '' }} {{ $data->data->sender_wallet->code ?? '' }}</span>
                                </div>
                            </div>
                            <button type="submit" class="btn--base mt-20 w-100"><span class="w-100">{{ __("Confirm") }}</span></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
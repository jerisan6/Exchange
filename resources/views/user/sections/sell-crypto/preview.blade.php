@extends('user.layouts.master')

@push('css')
    <style>
        .image-resize{
            width: 20px;
            height: 25px;
        }
    </style>
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Sell Crypto Preview")])
@endsection

@section('content')

<div class="body-wrapper">
    <form action="{{ setRoute('user.sell.crypto.confirm',$data->identifier) }}" method="POST">
        @csrf
        <div class="row justify-content-center mt-30 mb-30-none">
            <div class="col-xl-6 col-lg-6 mb-30">
                <div class="custom-card">
                    <div class="dashboard-header-wrapper">
                        <h4 class="title">{{ __("Transactions Summary") }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-keyboard"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Wallet Type") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$data->data->sender_wallet->type }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-coins"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Coin") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$data->data->sender_wallet->name }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-network-wired"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Network") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$data->data->network->name }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Payment Method") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$data->data->payment_method->name }}</span>
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
                                    <span class="text--success">{{ get_amount(@$data->data->amount,@$data->data->sender_wallet->code) }}</span>
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
                                    <span class="text--warning">1 {{ @$data->data->sender_wallet->code }}  = {{ get_amount(@$data->data->exchange_rate,@$data->data->payment_method->code) }}</span>
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
                                    <span class="text--danger">{{ get_amount(@$data->data->total_charge,@$data->data->sender_wallet->code) }}</span>
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
                                    <span class="last">{{ get_amount(@$data->data->total_payable,@$data->data->sender_wallet->code) }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="last">{{ __("Will Get Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="last">{{ get_amount(@$data->data->will_get,@$data->data->payment_method->code) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="col-xl-6 col-lg-6 mb-30">
                @if ($data->data->sender_wallet->type == global_const()::OUTSIDE_WALLET)
                    <div class="custom-card mb-10">
                        <div class="dashboard-header-wrapper">
                            <h4 class="title">{{ __("Payment Proof Summary") }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="preview-list-wrapper">
                                @php
                                    $details   = json_decode($data->data->details);
                                @endphp
                                @foreach ($details->outside_address_input_values ?? [] as $item)
                                    <div class="preview-list-item">
                                        <div class="preview-list-left">
                                            <div class="preview-list-user-wrapper">
                                                <div class="preview-list-user-icon">
                                                    <i class="las la-compact-disc"></i>
                                                </div>
                                                <div class="preview-list-user-content">
                                                    <span>{{ __(@$item->label) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="preview-list-right">
                                            @if (@$item->type == "text" || @$item->type == "textarea")
                                                <span>{{ @$item->value }}</span>
                                            @elseif (@$item->type == "file")
                                                <img class="image-resize" src="{{ get_image(@$item->value , 'kyc-files') }}" alt="" srcset="">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                <div class="custom-card">
                    <div class="dashboard-header-wrapper">
                        <h4 class="title">{{ __("Receiving Method Summary") }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="preview-list-wrapper">
                            @php
                                $details   = json_decode($data->data->details);
                            @endphp
                            @foreach ($details->gateway_input_values ?? [] as $item)
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-university"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __(@$item->label) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    @if (@$item->type == "text" || @$item->type == "textarea")
                                        <span>{{ @$item->value }}</span>
                                    @elseif (@$item->type == "file")
                                        <img class="image-resize" src="{{ get_image(@$item->value , 'kyc-files') }}" alt="" srcset="">
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="submit-btn-wrapper">
            <button type="submit" class="btn--base w-100"><span class="w-100">{{ __("Confirm") }}</span></button>
        </div>
    </form>
</div>
@endsection
@extends('user.layouts.master')

@push('css')
    
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Sell Crypto Payment")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row justify-content-center mt-30">
        <div class="col-xxl-6 col-xl-6 col-lg-6">
            <div class="custom-card">
                <div class="dashboard-header-wrapper">
                    <h5 class="title">{{ __("Sell Crypto Payment") }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ setRoute('user.sell.crypto.sell.payment.store',$data->identifier) }}" class="card-form" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 form-group">
                                <div class="qr-code-thumb text-center">
                                    {{ $qr_code }}
                                </div>
                            </div>
                            <input type="hidden" name="slug" value="{{ $outside_wallet_address->slug }}">
                            <div class="col-xl-12 col-lg-12 form-group paste-form text-center">
                                <label id="public-address">{{ $outside_wallet_address->public_address }}</label>
                                <div class="paste-text" id="copy-address"><i class="las la-paste"></i></div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group paste-form text-center">
                                <label id="payable-amount">{{ $data->data->total_payable }}</label>
                                <div class="paste-text" id="copy-amount"><i class="las la-paste"></i></div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12">
                            <button type="submit" class="btn--base w-100"><span class="w-100">{{ __("Continue") }}</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-xl-6 col-lg-6 mb-30">
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
    </div>
</div>
@endsection
@push('script')
<script>
    $('#copy-address').on('click',function(){
        var copyText = document.getElementById("public-address").textContent;

        var tempTextarea = document.createElement('textarea');
        tempTextarea.value = copyText;
        document.body.appendChild(tempTextarea);

        tempTextarea.select();
        tempTextarea.setSelectionRange(0, 99999);
        document.execCommand('copy');
        document.body.removeChild(tempTextarea);

        throwMessage('success', ["Copied: " + copyText]);
    });

    //copy amount
    $('#copy-amount').on('click',function(){
        var copyText = document.getElementById("payable-amount").textContent;

        var tempTextarea = document.createElement('textarea');
        tempTextarea.value = copyText;
        document.body.appendChild(tempTextarea);

        tempTextarea.select();
        tempTextarea.setSelectionRange(0, 99999);
        document.execCommand('copy');
        document.body.removeChild(tempTextarea);

        throwMessage('success', ["Copied: " + copyText]);
    });
</script>
@endpush
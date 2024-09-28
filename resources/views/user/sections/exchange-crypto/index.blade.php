@extends('user.layouts.master')

@push('css')
    
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Exchange Crypto")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row justify-content-center mt-30">
        <div class="col-xxl-8 col-xl-8 col-lg-8">
            <div class="custom-card">
                <div class="dashboard-header-wrapper">
                    <h5 class="title">{{ __("Exchange Crypto") }}</h5>
                </div>
                <div class="card-body">
                    <form class="card-form" action="{{ setRoute('user.exchange.crypto.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 form-group text-center">
                                <div class="exchange-area">
                                    <code class="d-block text-center"><span>{{ __("Exchange Rate") }}</span><span class="exchange-rate"></span> </code>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 form-group">
                                <label>{{ __("Exchange From") }}<span>*</span></label>
                                <div class="input-group max">
                                    <input type="text" class="form--control send-amount number-input" name="send_amount" placeholder="{{ __("Enter Amount") }}...">
                                    <div class="input-group-text two max-amount">{{ __("Max") }}</div>
                                    <select class="form--control nice-select" name="sender_wallet">
                                        @foreach ($currencies as $item)
                                            <option 
                                            value="{{ $item->id }}"
                                                data-balance="{{ $item->balance }}"
                                                data-rate="{{ $item->currency->rate }}"
                                                data-code="{{ $item->currency->code }}"
                                                >{{ $item->currency->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="exist text-start"></label>
                                <code class="d-block mt-10 available-balance"></code>
                            </div>
                            <div class="col-xl-6 col-lg-6 form-group">
                                <label>{{ __("Exchange To") }}<span>*</span></label>
                                <div class="input-group max">
                                    <input type="text" class="form--control receive-money" name="receive_money" placeholder="{{ __("Enter Amount") }}...">
                                    <select class="form--control nice-select" name="receiver_currency">
                                        @foreach ($reciever_currencies as $item)
                                            <option 
                                            value="{{ $item->id }}"
                                            data-code="{{ $item->currency->code }}"
                                            data-rate="{{ $item->currency->rate }}"
                                            data-balance="{{ $item->balance }}"
                                            >{{ $item->currency->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <div class="note-area">
                                    <code class="d-block limit"></code>
                                    <code class="d-block charges"></code>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12">
                            <button type="submit" class="btn--base w-100 exchange-button"><span class="w-100">{{ __("Exchange Crypto") }}</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        $(document).ready(function(){
            var amount      = $("input[name=send_amount]").val();
            getExchangePreview();
            chargeCalculation(amount);
        });
    </script>
    <script>
        var minAmountText           = "{{ __('Min Amount') }}";
        var maxAmountText           = "{{ __('Max Amount') }}";
        var limitText               = "{{ __('Limit') }}";
        var rateText                = "{{ __('Rate') }}";
        var networkFeesText         = "{{ __('Network Fees') }}";
        var availableBalanceText    = "{{ __('Available Balance') }}";
        var ExchangeRateText        = "{{ __('Exchange Rate') }}";
        var insufficientBalanceText        = "{{ __('Sorry! Insufficient Balance.') }}";
        $("select[name=sender_wallet]").change(function(){
            var amount      = $("input[name=send_amount]").val();
            getExchangePreview();
            amountCalculation(amount);
            chargeCalculation(amount);
        });
        $("select[name=receiver_currency]").change(function(){
            var amount      = $("input[name=send_amount]").val();
            getExchangePreview();
            amountCalculation(amount);
        });
        $(document).on('click','.max-amount',function(){
            $(".send-amount").val('');
            var walletMaxBalance    = selectedVariable().senderWalletBalance;
            if(walletMaxBalance <= 0){
                $('.exist').text(insufficientBalanceText).addClass('text--danger');
            }else{
                var senderCurrency      = selectedVariable().senderCurrency;
                var senderRate          = selectedVariable().senderRate;
                var receiverRate        = selectedVariable().receiverRate;
                var fixedCharge         = '{{ $transaction_fees->fixed_charge }}';
                var percentCharge       = '{{ $transaction_fees->percent_charge }}';
                var exchangeRate        = parseFloat(receiverRate) / parseFloat(senderRate);
                var fixedChargeCalc     = parseFloat(fixedCharge) * senderRate;
                var percentChargeCalc   = (walletMaxBalance / 100) * percentCharge;
                var totalCharge         = parseFloat(fixedChargeCalc) + parseFloat(percentChargeCalc);
                if(walletMaxBalance <= totalCharge){
                    $('.exist').text(insufficientBalanceText).addClass('text--danger');
                }else{
                    var deductAmount        = parseFloat(walletMaxBalance) - parseFloat(totalCharge);
                    var sendAmount          = $(".send-amount").val(parseFloat(deductAmount).toFixed(2));
                    var amount              = $("input[name=send_amount]").val();
                    $('.exist').text('').removeClass('text--danger');
                    amountCalculation(amount);
                    chargeCalculation(amount);
                }
                
            }
            
        });
        $(".send-amount").keyup(function(){
            var amount              = $(this).val();
            var walletBalance       = selectedVariable().senderWalletBalance;
            
            amountCalculation(amount);
            chargeCalculation(amount);
        });
        function selectedVariable(){
            var senderCurrency          = $("select[name=sender_wallet] :selected").data('code');
            var senderRate              = $("select[name=sender_wallet] :selected").data('rate');
            var senderWalletBalance     = $("select[name=sender_wallet] :selected").data('balance');
            var receiverCurrency        = $("select[name=receiver_currency] :selected").data("code");
            var receiverRate            = $("select[name=receiver_currency] :selected").data("rate");
            var receiverBalance         = $("select[name=receiver_currency] :selected").data("balance");
            

            return {
                senderCurrency:senderCurrency,
                senderRate:senderRate,
                senderWalletBalance:senderWalletBalance,
                receiverCurrency:receiverCurrency,
                receiverRate:receiverRate,
                receiverBalance:receiverBalance
            };
        }

        //getExchangePreview

        function getExchangePreview(){
            var walletBalance       = selectedVariable().senderWalletBalance;
            var minLimit            = '{{ $transaction_fees->min_limit }}';
            var maxLimit            = '{{ $transaction_fees->max_limit }}';
            var senderCurrency      = selectedVariable().senderCurrency;
            var senderRate          = selectedVariable().senderRate;
            var receiverCurrency    = selectedVariable().receiverCurrency;
            var receiverRate        = selectedVariable().receiverRate;
            var exchangeRate        = parseFloat(receiverRate) / parseFloat(senderRate);
            var totalMinLimit       = minLimit * senderRate;
            var totalMaxLimit       = maxLimit * senderRate;

            $(".exchange-rate").html("1" + " " + senderCurrency + " " + "=" + " " + parseFloat(exchangeRate).toFixed(6) + " " + receiverCurrency);
            $(".available-balance").html(availableBalanceText + ': ' + parseFloat(walletBalance).toFixed(8) + " " + senderCurrency);
            $(".limit").html(limitText + ': ' + parseFloat(totalMinLimit).toFixed(8) + " " + "-" + " " + parseFloat(totalMaxLimit).toFixed(8) + " " + senderCurrency);
        }

        //amount Calculation
        function amountCalculation(amount){
            if(amount == '' || amount == undefined){
                var receiveAmount = $(".receive-money").val(); 
                return receiveAmount;     
            }
            
            var senderRate          = selectedVariable().senderRate;
            var receiverRate        = selectedVariable().receiverRate;
            var exchangeRate        = parseFloat(receiverRate) / parseFloat(senderRate);
            var receiveAmount       = parseFloat(amount) * parseFloat(exchangeRate);

            $(".receive-money").val(parseFloat(receiveAmount).toFixed(8));   
        }

        //charge calculation
        function chargeCalculation(amount){
            var senderCurrency      = selectedVariable().senderCurrency;
            var senderRate          = selectedVariable().senderRate;
            var receiverRate        = selectedVariable().receiverRate;
            var fixedCharge         = '{{ $transaction_fees->fixed_charge }}';
            var percentCharge       = '{{ $transaction_fees->percent_charge }}';
            var exchangeRate        = parseFloat(receiverRate) / parseFloat(senderRate);
            var fixedChargeCalc     = parseFloat(fixedCharge) * senderRate;
            var percentChargeCalc   = (amount / 100) * percentCharge;
            var totalCharge         = parseFloat(fixedChargeCalc) + parseFloat(percentChargeCalc);
           
            $(".charges").html(networkFeesText + ': ' + parseFloat(totalCharge).toFixed(8) + " " + senderCurrency);
        }
        
    </script>
@endpush
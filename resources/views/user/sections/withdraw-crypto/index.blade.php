@extends('user.layouts.master')

@push('css')
    
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Withdraw Crypto")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row justify-content-center mt-30">
        <div class="col-xxl-8 col-xl-8 col-lg-12">
            <div class="custom-card">
                <div class="dashboard-header-wrapper">
                    <h5 class="title">{{ __("Withdraw Crypto") }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ setRoute('user.withdraw.crypto.store') }}" class="card-form" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 form-group text-center exchange-box">
                                <div class="exchange-area">
                                    <code class="d-block text-center exchange-rate"></code>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 form-group">
                                <label>{{ __("Amount") }}<span>*</span></label>
                                <div class="input-group max">
                                    <input type="text" class="form--control amount number-input" name="amount" placeholder="{{ __("Enter Amount") }}...">
                                    <div class="input-group-text two max-amount">{{ __("Max") }}</div>
                                    <select class="form--control nice-select" name="sender_wallet">
                                        @foreach ($currencies as $item)
                                            <option value="{{ $item->id }}"
                                                data-balance="{{ $item->balance }}"
                                                data-rate="{{ $item->currency->rate }}"
                                                data-code="{{ $item->currency->code }}"
                                                >{{ $item->currency->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="wallet-amount-balance text-start"></label>
                                <code class="d-block mt-10 available-balance"></code>
                            </div>
                            <div class="col-xl-6 col-lg-6 form-group">
                                <label>{{ __("Wallet Address") }}<span>*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control checkAddress" name="wallet_address" id="cryptoAddress" placeholder="{{ __("Enter or Paste Address") }}...">
                                    <div class="input-group-text" id="paste-address"><i class="las la-paste"></i></div>
                                    
                                </div>
                                <label class="exist text-start"></label>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <div class="note-area">
                                    <code class="d-block limit"></code>
                                    <code class="d-block network-charge"></code>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12">
                            <button type="submit" class="btn--base w-100 withdraw"><span class="w-100">{{ __("Continue") }}</span></button>
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
        document.getElementById('paste-address').addEventListener('click', function (event) {
    
        
        event.preventDefault();
        
        navigator.clipboard.readText()
            .then((text) => {
            
            document.getElementById('cryptoAddress').value = text;
            })
            .catch((err) => {
            console.error('Failed to read clipboard data', err);
            });
        });
    </script>
    <script>
        var minAmountText               = "{{ __('Min Amount') }}";
        var maxAmountText               = "{{ __('Max Amount') }}";
        var limitText                   = "{{ __('Limit') }}";
        var rateText                    = "{{ __('Rate') }}";
        var networkFeesText             = "{{ __('Network Fees') }}";
        var availableBalanceText        = "{{ __('Available Balance') }}";
        var ExchangeRateText            = "{{ __('Exchange Rate') }}";
        var insufficientBalanceText     = "{{ __('Sorry! Insufficient Balance.') }}";
        $('.checkAddress').on('keyup',function(e){
            var url = '{{ route('user.withdraw.crypto.check.address.exist') }}';
            var value = $(this).val();
            $('.exist').text('');
            
            var token = '{{ csrf_token() }}';
            if ($(this).attr('name') == 'wallet_address') {
                var data = {wallet_address:value,_token:token}

            }
            
            $.post(url,data,function(response) {
                if(response.own){
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exchange-rate').html('');
                    $('.exist').addClass('text--danger').text(response.own);
                    $('.withdraw').attr('disabled',true);
                    return false
                }
                if(response['data'] != null){
                    if($('.exist').hasClass('text--danger')){
                        $('.exist').removeClass('text--danger');
                    }
                    var walletRate     = response['data'].currency.rate;

                    var walletCode     = response['data'].currency.code;
                    var senderRate     = selectedValue().senderRate;
                    var senderBaseRate = senderRate /senderRate;
                    var senderCode     = selectedValue().senderCurrency;
                    var rate           = walletRate / senderRate;
                    
                    $('.exchange-rate').text(ExchangeRateText + " " + parseFloat(senderBaseRate) + " " + senderCode + " = " + parseFloat(rate) + " " + walletCode);

                    $('.exist').text(`{{ __("Valid Address for transaction.") }}`).addClass('text--success');
                    localStorage.setItem('exchangeRate', rate);
                    localStorage.setItem('exchangeCode', walletCode);
                    $('.withdraw').attr('disabled',false);
                } else {
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exchange-rate').html(ExchangeRateText);
                    $('.exist').text('Wallet Address doesn\'t  exists.').addClass('text--danger');
                    $('.withdraw').attr('disabled',true);
                    return false
                }
            });
        });
        $(document).ready(function () {
            getPreview();
            $('.exchange-rate').html(ExchangeRateText);
        });
        $('select[name=sender_wallet]').change(function(){
            var amount      = $('input[name=amount]').val();
            var checkAddress = $('.checkAddress').val();
            if(checkAddress == null || checkAddress == ''){
                localStorage.removeItem("exchangeCode");
                localStorage.removeItem("exchangeRate");
            }
            getPreview();
            getExchangePreview();
            chargeCalculation(amount);
        });
        $('.amount').keyup(function(){
            var amount      = $('input[name=amount]').val();
            chargeCalculation(amount);
        });

        // max amount get
        $(document).on('click','.max-amount',function(){
            $(".amount").val('');
            var walletBalance       = selectedValue().senderBalance;
            if(walletBalance <= 0){
                $('.wallet-amount-balance').text(insufficientBalanceText).addClass('text--danger');
                
            }else{
                var senderRate          = selectedValue().senderRate;
                var fixedCharge         = '{{ $transaction_fees->fixed_charge }}';
                var percentCharge       = '{{ $transaction_fees->percent_charge }}';
                var fixedChargeCalc     = fixedCharge * senderRate;
                var percentChargeCalc   = (walletBalance / 100) * percentCharge;
                var totalCharge         = fixedChargeCalc + percentChargeCalc;
                if(walletBalance <= totalCharge){
                    $('.wallet-amount-balance').text(insufficientBalanceText).addClass('text--danger');
                    
                }else{
                    var amount              = parseFloat(walletBalance) - parseFloat(totalCharge);
                    $('.wallet-amount-balance').text('').removeClass('text--danger');
                    $(".amount").val(amount);
                    chargeCalculation(amount);
                }
            }
        });

        // function for preview
        function getPreview(){
            var walletBalance       = selectedValue().senderBalance;
            var currency            = selectedValue().senderCurrency;
            var rate                = selectedValue().senderRate;
            var minLimit            = '{{ $transaction_fees->min_limit }}';
            var maxLimit            = '{{ $transaction_fees->max_limit }}';
            var totalMinLimit       = minLimit * rate;
            var totalMaxLimit       = maxLimit * rate;
            

            $('.available-balance').text(availableBalanceText + ': ' + " " + parseFloat(walletBalance).toFixed(8) + " " + currency);
            $('.limit').text(limitText + ": " + " " + parseFloat(totalMinLimit).toFixed(8) + " " + currency + " " + "-" + " " + parseFloat(totalMaxLimit).toFixed(8) + " " + currency);
            
        }

        // get exchange preview
        function getExchangePreview(){
            var currency            = selectedValue().senderCurrency;
            var rate                = selectedValue().senderRate;
            var convertRate         = rate / rate;
            let exchangeRate        = localStorage.getItem('exchangeRate');
            let walletCode          = localStorage.getItem('exchangeCode');
            var newRate             = exchangeRate / rate;
            $('.exchange-rate').text(ExchangeRateText + ': ' + " " + parseFloat(convertRate) + " " + currency + " = " + parseFloat(newRate).toFixed(6) + " " + walletCode);
        }

        // function charge calculation
        function chargeCalculation(amount){
            var senderCurrency      = selectedValue().senderCurrency;
            var senderRate          = selectedValue().senderRate;
            var fixedCharge         = '{{ $transaction_fees->fixed_charge }}';
            var percentCharge       = '{{ $transaction_fees->percent_charge }}';
            var fixedChargeCalc     = fixedCharge * senderRate;
            var percentChargeCalc   = (amount / 100) * percentCharge;
            var totalCharge         = fixedChargeCalc + percentChargeCalc;

            $('.network-charge').text(networkFeesText + ": " + " " +  parseFloat(totalCharge).toFixed(2) + " " + senderCurrency);
        }

        //selected value
        function selectedValue(){
            var senderCurrency      = $("select[name=sender_wallet] :selected").data("code");
            var senderRate          = $("select[name=sender_wallet] :selected").data("rate");
            var senderBalance       = $("select[name=sender_wallet] :selected").data("balance");

            return {
                senderCurrency:senderCurrency,
                senderRate:senderRate,
                senderBalance:senderBalance
            };
        }
    </script>
@endpush
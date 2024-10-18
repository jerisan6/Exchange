@if (admin_permission_by_name("admin.currency.store"))

    <div id="currency-add" class="mfp-hide large">

        <div class="modal-data">

            <div class="modal-header px-0">

                <h5 class="modal-title">{{ __("Add Currency") }}</h5>

            </div>

            <div class="modal-form-data">

                <form class="modal-form" method="POST" action="{{ setRoute('admin.currency.store') }}" enctype="multipart/form-data">

                    @csrf

                    <div class="row mb-10-none">

                        <div class="col-xl-12 col-lg-12 form-group">

                            <label for="countryFlag">{{ __("Flag") }}</label>

                            <div class="col-12 col-sm-3 m-auto">

                                @include('admin.components.form.input-file',[

                                    'label'         => false,

                                    'class'         => "file-holder m-auto",

                                    'name'          => "flag",

                                ])

                            </div>

                        </div>

                        <div class="col-xl-6 col-lg-6 form-group">

                            @include('admin.components.form.input',[

                                'label'         => __('Name')."*",

                                'name'          => 'name',

                                'value'         => old('name')

                            ])

                        </div>

                        <div class="col-xl-3 col-lg-3 form-group">

                            @include('admin.components.form.input',[

                                'label'         => __('Code')."*",

                                'name'          => 'code',

                                'class'         => 'currency-code',

                                'value'         => old('code')

                            ])

                        </div>

                        <div class="col-xl-3 col-lg-3 form-group">

                            @include('admin.components.form.input',[

                                'label'         => __('Symbol')."*",

                                'name'          => 'symbol',

                                'value'         => old('symbol')

                            ])

                        </div>

                        <div class="col-xl-12 col-lg-12 form-group">

                            <label>{{ __("Rate") }}*</label>

                            <div class="input-group">

                                <span class="input-group-text append">1 {{ get_default_currency_code() }} = </span>

                                <input type="number" class="form--control" id="rate-input" value="{{ old('rate',0.00) }}" name="rate" readonly>

                                <span class="input-group-text selected-currency"></span>

                            </div>

                        </div>

                        <div class="col-xl-12 col-lg-12 form-group">

                            <div class="custom-inner-card">

                                <div class="card-inner-header">

                                    <h6 class="title">{{ __("Network") }}</h6>

                                    <button type="button" class="btn--base add-network-btn"><i class="fas fa-plus"></i> {{ __("Add Network") }}</button>

                                </div>

                                <div class="card-inner-body">

                                    <div class="results">

                                        @include('admin.components.currency.network',compact('networks'))    

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-xl-12 col-lg-12 form-group">

                            @include('admin.components.form.switcher',[

                                'label'         => __('Option')."*",

                                'name'          => 'option',

                                'value'         => old('option','optional'),

                                'options'       => [__('Optional') => 'optional',__('Default') => 'default'],

                            ])

                        </div>



                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">

                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>

                            <button type="submit" class="btn btn--base">{{ __("Add") }}</button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

    @include('admin.components.currency.network',[

        'networks'  => $networks,

        'class'     => 'network-add-block d-none',

        'select2'   => false

    ])    



    @push("script")

        <script>

            $(document).ready(function(){

                openModalWhenError("currency_add","#currency-add");



                var defaultCurrency = '{{ get_default_currency_code() }}';

                // currency code change 

                $('.currency-code').keyup(function(){

                    var selectedCurrency = $(this).val().toUpperCase();
                    localStorage.setItem('selectedCurrency', selectedCurrency);
                    $('.selected-currency').text(selectedCurrency);
                    fetchExchangeRate(defaultCurrency, selectedCurrency);

                });





                $('.add-network-btn').click(function(){

                    var networkAddBlock     = $('.network-add-block');

                    var cloneNetwork        = networkAddBlock.clone();



                    cloneNetwork.removeClass('d-none network-add-block').prependTo('.results');

                    

                    var selectedCurrency = localStorage.getItem("selectedCurrency");

                    $('.selcted-currency').text(selectedCurrency);

                });  

            });

            function fetchExchangeRate(fromCurrency, toCurrency) {
                $.ajax({
                    url: `https://api.exchangerate-api.com/v4/latest/${fromCurrency}`,
                    method: 'GET',
                    success: function(data) {
                        if (data.rates && data.rates[toCurrency]) {
                            $('#rate-input').val(data.rates[toCurrency].toFixed(4));
                        } else {
                            $('#rate-input').val('N/A');
                        }
                    },
                    error: function() {
                        $('#rate-input').val('Error');
                    }
                });
            }

            // Llamar a la función cuando se carga la página
            var initialCurrency = $('.currency-code').val().toUpperCase();
            if (initialCurrency) {
                fetchExchangeRate(defaultCurrency, initialCurrency);
            }

        </script>

    @endpush

@endif

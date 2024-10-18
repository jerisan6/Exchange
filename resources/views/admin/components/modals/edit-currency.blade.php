@if (admin_permission_by_name("admin.currency.update"))

    <div id="currency-edit" class="mfp-hide large">

        <div class="modal-data">

            <div class="modal-header px-0">

                <h5 class="modal-title">{{ __("Edit Currency") }}</h5>

            </div>

            <div class="modal-form-data">

                <form class="modal-form" method="POST" action="{{ setRoute('admin.currency.update') }}" enctype="multipart/form-data">

                    @csrf

                    @method("PUT")

                    @include('admin.components.form.hidden-input',[

                        'name'          => 'target',

                        'value'         => old('target'),

                    ])

                    <div class="row mb-10-none">

                        <div class="col-xl-12 col-lg-12 form-group">

                            <label for="countryFlag">{{ __("Flag") }}</label>

                            <div class="col-12 col-sm-3 m-auto">

                                @include('admin.components.form.input-file',[

                                    'label'             => false,

                                    'class'             => "file-holder m-auto",

                                    'name'              => "currency_flag",

                                    'old_files_path'    => files_asset_path('currency-flag'),

                                    'old_files'         => old('old_flag'),

                                ])

                            </div>

                        </div>

                        <div class="col-xl-6 col-lg-6 form-group">

                            @include('admin.components.form.input',[

                                'label'         => __('Name')."*",

                                'name'          => 'currency_name',

                                'value'         => old('currency_name'),

                            ])

                        </div>

                        <div class="col-xl-3 col-lg-3 form-group">

                            @include('admin.components.form.input',[

                                'label'         => __('Code')."*",

                                'name'          => 'currency_code',

                                'class'         => 'currency-code',

                                'value'         => old('currency_code'),

                            ])

                        </div>

                        <div class="col-xl-3 col-lg-3 form-group">

                            @include('admin.components.form.input',[

                                'label'         => __('Symbol')."*",

                                'name'          => 'currency_symbol',

                                'value'         => old('currency_symbol'),

                            ])

                        </div>

                        <div class="col-xl-12 col-lg-12 form-group">

                            <div class="input-group">

                                <span class="input-group-text append">1 {{ get_default_currency_code($default_currency) }} = </span>

                                <input type="number" class="form--control" id="rate-input" value="{{ old('currency_rate') }}" name="currency_rate" readonly>

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

                                         

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-xl-12 col-lg-12 form-group">

                            @include('admin.components.form.switcher',[

                                'label'         => __('Option')."*",

                                'name'          => 'currency_option',

                                'value'         => old('currency_option'),

                                'options'       => ['Optional' => 0,'Default' => 1],

                            ])

                        </div>



                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">

                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>

                            <button type="submit" class="btn btn--base">{{ __("Update") }}</button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endif

@push("script")

        <script>

            $(document).ready(function(){

                reloadAllCountries("select[name=currency_country]");

                openModalWhenError("currency_edit","#currency-edit");



                // currency code change 

                $('.currency-code').keyup(function(){

                    var selectedCurrency = $(this).val().toUpperCase();

                    localStorage.setItem('selectedCurrency',selectedCurrency)

                    $('.selected-currency').text(selectedCurrency);

                });



                $(document).on("click",".edit-modal-button",function(){

                    var oldData = JSON.parse($(this).parents("tr").attr("data-item"));

                    var editModal = $("#currency-edit");

                    

                    var readOnly = true;

                    



                    editModal.find(".invalid-feedback").remove();

                    editModal.find(".form--control").removeClass("is-invalid");



                    editModal.find("form").first().find("input[name=target]").val(oldData.code);

                    editModal.find("input[name=currency_code]").val(oldData.code);

                    editModal.find(".selected-currency").text(oldData.code);

                    editModal.find("input[name=currency_name]").val(oldData.name);

                    editModal.find("input[name=currency_symbol]").val(oldData.symbol);

                    editModal.find("input[name=currency_rate]").val(oldData.rate);

                    editModal.find("input[name=currency_type]").val(oldData.type);

                    editModal.find("input[name=currency_flag]").attr("data-preview-name",oldData.flag);

                    editModal.find("input[name=currency_option]").val(oldData.option);

                    editModal.find(".selcted-currency-edit").text(oldData.code);

                    editModal.find("select[name=currency_country]").attr("data-old",oldData.country);

                    var itemData    = '';

                    var options     = '';

                    $.each(oldData.networks,function(index,item){

                        $.each(oldData.all_networks,function(key,value){

                            options  += `<option value="${value.id}" ${(value.id == item.network_id) ? 'selected' : ''}  >${value.name}</option>`;

                        })

                        itemData    += `

                                <div class="row align-items-end">

                                    <div class="col-xl-11 col-lg-11 form-group">

                                        <label>{{ __("Network") }}<span>*</span></label>

                                        

                                        <select class="form--control select2-basic network-select" name="network[]">

                                            ${options}

                                        </select>

                                    </div>

                                    <div class="col-xl-1 col-lg-1 form-group">

                                        <button type="button" class="custom-btn btn--base btn--danger row-cross-btn w-100"><i class="las la-times"></i></button>

                                    </div>

                                </div>

                            `;

                        options = '';

                    })

                    editModal.find(".results").html(itemData);

                    editModal.find(".results select").select2();

                    var selectedCurrency = localStorage.getItem("currencyCode");

                    $('.selcted-currency').text(selectedCurrency);

                    selectFormRadio("#currency-edit input[name=currency_role]",oldData.role);

                    reloadAllCountries("select[name=currency_country]");

                    fileHolderPreviewReInit("#currency-edit input[name=currency_flag]");

                    refreshSwitchers("#currency-edit");

                    openModalBySelector("#currency-edit");



                });

                // Función para actualizar y guardar automáticamente
                function updateAndSaveRate() {
                    var code = $('.currency-code').val().toUpperCase();
                    var rateInput = $('#rate-input');
                    var oldRate = rateInput.val();
                    
                    if (cryptoCurrencies.includes(code)) {
                        fetchCryptoExchangeRate(defaultCurrency, code, rateInput, function(newRate) {
                            if (newRate !== oldRate) {
                                saveUpdatedRate(code, newRate);
                            }
                        });
                    } else {
                        fetchFiatExchangeRate(defaultCurrency, code, rateInput, function(newRate) {
                            if (newRate !== oldRate) {
                                saveUpdatedRate(code, newRate);
                            }
                        });
                    }
                }

                function fetchCryptoExchangeRate(fromCurrency, toCurrency, rateInput, callback) {
                    $.ajax({
                        url: `https://min-api.cryptocompare.com/data/price?fsym=${fromCurrency}&tsyms=${toCurrency}`,
                        method: 'GET',
                        success: function(data) {
                            if (data[toCurrency]) {
                                var newRate = (1 / data[toCurrency]).toFixed(8);
                                rateInput.val(newRate);
                                if (callback) callback(newRate);
                            } else {
                                rateInput.val('N/A');
                                if (callback) callback('N/A');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching crypto rate:", error);
                            rateInput.val('Error');
                            if (callback) callback('Error');
                        }
                    });
                }

                function fetchFiatExchangeRate(fromCurrency, toCurrency, rateInput, callback) {
                    $.ajax({
                        url: `https://api.exchangerate-api.com/v4/latest/${fromCurrency}`,
                        method: 'GET',
                        success: function(data) {
                            if (data.rates && data.rates[toCurrency]) {
                                var newRate = (1 / data.rates[toCurrency]).toFixed(8);
                                rateInput.val(newRate);
                                if (callback) callback(newRate);
                            } else {
                                rateInput.val('N/A');
                                if (callback) callback('N/A');
                            }
                        },
                        error: function() {
                            rateInput.val('Error');
                            if (callback) callback('Error');
                        }
                    });
                }

                function saveUpdatedRate(code, rate) {
                    console.log('Intentando guardar:', { target: code, rate: rate });
                    $.ajax({
                        url: "{{ setRoute('admin.currency.update.rate') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            target: code,
                            rate: rate
                        },
                        success: function(response) {
                            console.log('Tasa actualizada y guardada:', response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al guardar la tasa:', error);
                        }
                    });
                }

                // Actualizar y guardar cuando se abre el modal
                $(document).on('click', '.edit-modal-button', function() {
                    setTimeout(updateAndSaveRate, 500);
                });

            });

            var defaultCurrency = '{{ get_default_currency_code() }}';
            var supportedCurrencies = ['USD', 'EUR', 'JPY', 'GBP', 'AUD', 'CAD', 'CHF', 'CNY', 'HKD', 'NZD', 'SEK', 'KRW', 'SGD', 'NOK', 'MXN', 'INR', 'RUB', 'ZAR', 'TRY', 'BRL', 'TWD', 'DKK', 'PLN', 'THB', 'IDR', 'HUF', 'CZK', 'ILS', 'CLP', 'PHP', 'AED', 'COP', 'SAR', 'MYR', 'RON', 'BTC', 'ETH', 'LTC', 'BCH', 'BNB', 'EOS', 'XRP', 'XLM', 'LINK', 'DOT', 'YFI', 'USDT', 'USDC', 'ADA', 'SOL', 'XMR', 'BUSD', 'DOGE'];
            var cryptoCurrencies = ['BTC', 'ETH', 'LTC', 'BCH', 'BNB', 'EOS', 'XRP', 'XLM', 'LINK', 'DOT', 'YFI', 'USDT', 'USDC', 'ADA', 'SOL', 'XMR', 'BUSD', 'DOGE', 'DAI'];

            // Función para actualizar todas las tasas de cambio
            function updateAllExchangeRates() {
                $('.currency-item').each(function() {
                    var code = $(this).data('code');
                    var rateInput = $(this).find('.rate-input');
                    if (cryptoCurrencies.includes(code)) {
                        fetchCryptoExchangeRate(defaultCurrency, code, rateInput);
                    } else {
                        fetchFiatExchangeRate(defaultCurrency, code, rateInput);
                    }
                });
            }

            // Actualizar tasas cada 5 minutos
            setInterval(updateAllExchangeRates, 5 * 60 * 1000);

            // Actualizar tasas cuando se abre el modal
            $(document).on('click', '.edit-modal-button', function() {
                setTimeout(updateAllExchangeRates, 500); // Pequeño retraso para asegurar que el modal esté completamente cargado
            });

            $('.currency-code').keyup(function(){
                var selectedCurrency = $(this).val().toUpperCase();
                localStorage.setItem('selectedCurrency', selectedCurrency);
                $('.selected-currency').text(selectedCurrency);
                
                var rateInput = $(this).closest('.currency-item').find('.rate-input');
                if (cryptoCurrencies.includes(selectedCurrency)) {
                    fetchCryptoExchangeRate(defaultCurrency, selectedCurrency, rateInput);
                } else {
                    fetchFiatExchangeRate(defaultCurrency, selectedCurrency, rateInput);
                }
            });

            function fetchFiatExchangeRate(fromCurrency, toCurrency, rateInput) {
                $.ajax({
                    url: `https://api.exchangerate-api.com/v4/latest/${fromCurrency}`,
                    method: 'GET',
                    success: function(data) {
                        if (data.rates && data.rates[toCurrency]) {
                            rateInput.val(data.rates[toCurrency].toFixed(8));
                        } else {
                            rateInput.val('N/A');
                        }
                    },
                    error: function() {
                        rateInput.val('Error');
                    }
                });
            }

            function fetchCryptoExchangeRate(fromCurrency, toCurrency, rateInput) {
                $.ajax({
                    url: `https://min-api.cryptocompare.com/data/price?fsym=${fromCurrency}&tsyms=${toCurrency}`,
                    method: 'GET',
                    success: function(data) {
                        if (data[toCurrency]) {
                            rateInput.val(data[toCurrency].toFixed(8));
                        } else {
                            rateInput.val('N/A');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching crypto rate:", error);
                        rateInput.val('Error');
                    }
                });
            }

            // Actualizar tasas al cargar la página
            $(document).ready(function() {
                updateAllExchangeRates();
            });
        </script>

    @endpush





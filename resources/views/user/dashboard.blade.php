@extends('user.layouts.master')

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Dashboard")])
@endsection

@section('content')

<div class="body-wrapper">
    @if (auth()->user()->kyc_verified == global_const()::DEFAULT)
        <div class="dashboard-header-status-wrapper mt-20">
            <h6 class="title">{{ __("Please verify your KYC information before any transactional action.") }} <a href="{{ setRoute('user.authorize.kyc') }}" class="text--base">{{ __("Verify Now.") }}</a></h6>
        </div>
    @else
    @endif
    @if ($wallets->isNotEmpty())
        <div class="dashboard-area mt-20">
            <div class="dashboard-header-wrapper">
                <h4 class="title">{{ __("My Wallets") }}</h4>
                <div class="dashboard-btn-wrapper">
                    <div class="dashboard-btn">
                        <a href="{{ setRoute('user.wallet.index') }}" class="btn--base">{{ __("View More") }}</a>
                    </div>
                </div>
            </div>
            <div class="dashboard-item-area">
                <div class="row mb-20-none">
                    @foreach ($wallets ?? [] as $item)
                        <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-20">
                            <a href="{{ setRoute('user.wallet.details',$item->public_address) }}" class="dashbord-item">
                                <div class="dashboard-content">
                                    <span class="sub-title">{{ @$item->currency->name }}</span>
                                    <h4 class="title">{{ get_amount(@$item->balance,null,"double") }} <span class="text--danger">{{ @$item->currency->code }}</span></h4>
                                </div>
                                <div class="dashboard-icon">
                                    <img src="{{ get_image(@$item->currency->flag , 'currency-flag') }}" alt="flag">
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    <div class="chart-area mt-20">
        <div class="row mb-20-none">
            <div class="col-xxl-6 col-xl-6 col-lg-6 mb-20">
                <div class="chart-wrapper">
                    <div class="dashboard-header-wrapper">
                        <h5 class="title">{{ __("Buy Crypto Chart") }}</h5>
                    </div>
                    <div class="chart-container">
                        <div id="chart1" class="chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-6 mb-20">
                <div class="chart-wrapper">
                    <div class="dashboard-header-wrapper">
                        <h5 class="title">{{ __("Sell Crypto Chart") }}</h5>
                    </div>
                    <div class="chart-container">
                        <div id="chart2" class="chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title">{{ __("Buy Log") }}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn">
                    <a href="{{ setRoute('user.transaction.buy.log') }}" class="btn--base">{{ __("View More") }}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('user.components.crypto-table.buy-crypto',[
                'data'  => $transactions
            ])
        </div>
    </div>
</div>
@endsection
@push('script')
<script>
    let stringMonths = '@json($labels)';
    let chartMonths = JSON.parse(stringMonths);
    let stringData  = '@json($data)';
    let chartData   = JSON.parse(stringData);

    let sellStringData      = '@json($sell_data)';
    let sellData            = JSON.parse(sellStringData);
    let withdrawStringData  = '@json($withdraw_data)';
    let withdrawData        = JSON.parse(withdrawStringData);

    var options = {
        series: [{
            name: 'Total Transactions',
            color: "#0194FC",
            data: chartData
        }],
        chart: {
            height: 350,
            toolbar: {
              show: false
            },
            type: 'bar',
        },
        plotOptions: {
            bar: {
                borderRadius: 10,
                dataLabels: {
                    position: 'top', 
                },
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val;
            },
            offsetY: -20,
            style: {
                fontSize: '12px',
                colors: ["#ffffff"]
            }
        },

        xaxis: {
            categories: chartMonths,
            position: 'top',
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            crosshairs: {
                fill: {
                    type: 'gradient',
                    gradient: {
                        colorFrom: '#8781c6',
                        colorTo: '#8781c6',
                        stops: [0, 100],
                        opacityFrom: 0.4,
                        opacityTo: 0.5,
                    }
                }
            },
            tooltip: {
                enabled: true,
            }
        },
        yaxis: {
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false,
            },
            labels: {
                show: false,
                formatter: function (val) {
                    return val;
                }
            }

        },
        title: {
            text: "{{ __('Transactions Overview') }}",
            floating: true,
            offsetY: 330,
            align: 'center',
            style: {
                color: '#fff'
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart1"), options);
    chart.render();

    var options = {
        series: [{
        name: "{{ __('Buy Crypto') }}",
        color: "#00ABB3",
        data: chartData
        }, {
        name: "{{ __('Sell Crypto') }}",
        color: "#0194FC",
        data: sellData
        }, {
        name: "{{ __('Withdraw Crypto') }}",
        color: "#cdbb71",
        data: withdrawData
        }],
        chart: {
        type: 'bar',
        toolbar: {
            show: false
        },
        height: 350
        },
        plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 5,
            endingShape: 'rounded'
        },
        },
        dataLabels: {
        enabled: false
        },
        stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
        },
        xaxis: {
        categories: chartMonths,
        },
        yaxis: {
        title: {
            text: '$ (thousands)'
        }
        },
        fill: {
        opacity: 1
        },
        tooltip: {
        y: {
            formatter: function (val) {
            return val
            }
        }
        }
        };

    var chart = new ApexCharts(document.querySelector("#chart2"), options);
    chart.render();
</script>
@endpush
@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("Dashboard")])
@endsection

@section('content')
    <div class="dashboard-area">
        <div class="dashboard-item-area">
            <div class="row">
                <div class="col-xxxl-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Users") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ formatNumberInKNotation($data['total_user_count']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--success">{{ __("Active") }} {{ $data['active_user'] }}</span>
                                    <span class="badge badge--info">{{ __("Unverified") }} {{ $data['unverified_user'] }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart6" data-percent="{{ $data['user_percent'] }}"><span>{{ round($data['user_percent']) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Blog Category") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ formatNumberInKNotation($data['total_category_count']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("Active") }} {{ $data['active_category'] }}</span>
                                    <span class="badge badge--warning">{{ __("Inactive") }} {{ $data['inactive_category'] }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart7" data-percent="{{ $data['category_percent'] }}"><span>{{ round($data['category_percent']) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Blogs") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ formatNumberInKNotation($data['total_blog_count']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("Active") }} {{ $data['active_blog'] }}</span>
                                    <span class="badge badge--warning">{{ __("Inactive") }} {{ $data['inactive_blog'] }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart8" data-percent="{{ $data['blog_percent'] }}"><span>{{ round($data['blog_percent']) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Support Ticket") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ formatNumberInkNotation($data['total_ticket_count']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("Active") }} {{ formatNumberInkNotation($data['active_ticket']) }}</span>
                                    <span class="badge badge--warning">{{ __("Pending") }} {{ formatNumberInkNotation($data['pending_ticket']) }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart9" data-percent="{{ $data['percent_ticket'] }}"><span>{{ round($data['percent_ticket']) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Transactions") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ formatNumberInkNotation($data['total_transaction_count']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("Pending") }} {{ formatNumberInkNotation($data['pending_transactions']) }}</span>
                                    <span class="badge badge--warning">{{ __("Confirm") }} {{ formatNumberInkNotation($data['confirm_transactions']) }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart10" data-percent="{{ $data['percent_transactions'] }}"><span>{{ round($data['percent_transactions']) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Buy Crypto Transactions") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ formatNumberInkNotation($data['total_buy_crypto_count']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("Pending") }} {{ formatNumberInkNotation($data['pending_buy_crypto']) }}</span>
                                    <span class="badge badge--warning">{{ __("Confirm") }} {{ formatNumberInkNotation($data['pending_buy_crypto']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="chart-area mt-15">
        <div class="row mb-15-none">
            <div class="col-xxl-6 col-xl-6 col-lg-6 mb-15">
                <div class="chart-wrapper">
                    <div class="chart-area-header">
                        <h5 class="title">{{ __("Transaction Analytics") }}</h5>
                    </div>
                    <div class="chart-container">
                        <div id="chart3" class="order-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-6 mb-15">
                <div class="chart-wrapper">
                    <div class="chart-area-header">
                        <h5 class="title">{{ __("User Analytics") }}</h5>
                    </div>
                    <div class="chart-container">
                        <div id="chart4" class="balance-chart"  data-user_chart_data="{{ json_encode($data['user_chart_data']) }}"></div>
                    </div>
                    <div class="chart-area-footer">
                        <div class="chart-btn">
                            <a href="{{ setRoute('admin.users.index') }}" class="btn--base w-100">{{ __("View User") }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-area mt-15">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("Latest Buy Crypto Transactions") }}</h5>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("S. Wallet") }}</th>
                            <th>{{ __("Amount") }}</th>
                            <th>{{ __("P. Method") }}</th>
                            <th>{{ __("Status") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $item)
                            <tr>
                                <td><span>{{ $item->details->data->wallet->name ?? '' }}</span></td>
                                <td>{{ get_amount($item->amount,$item->details->data->wallet->code,8) }}</td>
                                <td>{{ $item->currency->name ?? '' }} @if($item->currency->gateway->isManual()) ({{ __("Manual") }}) @endif</td>
                                <td>
                                   
                                    @if ($item->status == global_const()::STATUS_PENDING)
                                        <span>{{ __("Pending") }}</span>
                                    @elseif ($item->status == global_const()::STATUS_CONFIRM_PAYMENT)
                                        <span>{{ __("Confirm Payment") }}</span>
                                    @elseif ($item->status == global_const()::STATUS_REJECT)
                                        <span>{{ __("Rejected") }}</span>
                                    @elseif ($item->status == global_const()::STATUS_CANCEL)
                                        <span>{{ __("Canceled") }}</span>
                                    @else
                                        <span>{{ __("Delayed") }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ setRoute('admin.buy.crypto.details',$item->id) }}" class="btn btn--base btn--primary"><i class="las la-info-circle"></i></a>
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 5])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
<!-- apexcharts js -->
<script src="{{ asset('public/backend/js/apexcharts.js') }}"></script>
<!-- chart js -->
<script src="{{ asset('public/backend/js/chart.js') }}"></script> 
<script>

    let stringMonths        = '@json($labels)';
    let chartMonths         = JSON.parse(stringMonths);
    let stringData          = '@json($buy_data)';
    let chartData           = JSON.parse(stringData);

    let sellStringData      = '@json($sell_data)';
    let sellData            = JSON.parse(sellStringData);
    let withdrawStringData  = '@json($withdraw_data)';
    let withdrawData        = JSON.parse(withdrawStringData);
    let exchangeStringData  = '@json($exchange_data)';
    let exchangeData        = JSON.parse(exchangeStringData);

    var options = {
    series: [{
    name: '{{ __("Buy Crypto") }}',
    color: "#5A5278",
    data: chartData
    }, {
    name: '{{ __("Sell Crypto") }}',
    color: "#6F6593",
    data: sellData
    }, {
    name: '{{ __("Withdraw Crypto") }}',
    color: "#8075AA",
    data: withdrawData
    },{
    name: '{{ __("Exchange Crypto") }}',
    color: "#8075AA",
    data: exchangeData
    }],
    chart: {
    type: 'bar',
    toolbar: {
        show: false
    },
    height: 325
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
        text: '{{ __("Total Transactions") }}'
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

    var chart = new ApexCharts(document.querySelector("#chart3"), options);
    chart.render();


    var chart4 = $('#chart4');
        var user_chart_data = chart4.data('user_chart_data');
        var options = {
        series: user_chart_data,
        chart: {
        width: 350,
        type: 'pie'
        },
        colors: ['#5A5278', '#6F6593', '#8075AA', '#A192D9'],
        labels: ['{{ __("Active") }}', '{{ __("Unverified") }}', '{{ __("Banned") }}', '{{ __("All") }}'],
        responsive: [{
        breakpoint: 1480,
        options: {
            chart: {
            width: 280
            },
            legend: {
            position: 'bottom'
            }
        },
        breakpoint: 1199,
        options: {
            chart: {
            width: 380
            },
            legend: {
            position: 'bottom'
            }
        },
        breakpoint: 575,
        options: {
            chart: {
            width: 280
            },
            legend: {
            position: 'bottom'
            }
        }
        }],
        legend: {
        position: 'bottom'
        },
        };

        var chart = new ApexCharts(document.querySelector("#chart4"), options);
        chart.render();
</script>
@endpush
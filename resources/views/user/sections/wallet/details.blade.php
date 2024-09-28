@extends('user.layouts.master')
@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Wallet Details")])
@endsection
@section('content')

<div class="body-wrapper">
    <div class="row justify-content-center mt-30">
        <div class="col-xl-6 col-lg-8">
            <div class="custom-card">
                <div class="dashboard-header-wrapper">
                    <h5 class="title">{{ @$wallet->currency->name }} ({{ @$wallet->currency->code }})</h5>
                </div>
                <div class="card-body">
                    <div class="dashboard-widget-card">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 form-group">
                                <div class="dashbord-item-details">
                                    <div class="dashboard-icon">
                                        <img src="{{ get_image($wallet->currency->flag , 'currency-flag') }}" alt="flag">
                                    </div>
                                    <div class="dashboard-content">
                                        <span class="sub-title">{{ @$wallet->currency->name }}</span>
                                        <h4 class="title">{{ get_amount(@$wallet->balance,null,"double") }} <span class="text--base">{{ @$wallet->currency->code }}</span></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <div class="qr-code-thumb two text-center">
                                    
                                    {!! $qr_code !!}
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group paste-form text-center mb-0">
                                <label id="public-address">{{ @$wallet->public_address ?? '' }}</label>
                                
                                <div class="paste-text" id="copy-address"><i class="las la-copy"></i></div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <div class="dashbord-item-details-list-wrapper">
                                    <h5 class="title">{{ __("Available Network") }}</h5>
                                    <ul class="dashbord-item-details-list">
                                        @foreach ($network_names ?? [] as $item)
                                            <li>{{ __("Network Name") }} <span>{{ $item ?? '' }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>
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
</script>
@endpush
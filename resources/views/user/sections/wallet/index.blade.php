@extends('user.layouts.master')

@push('css')
    
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("All Wallets")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title">{{ __("My Wallets") }}</h4>
        </div>
        <div class="dashboard-item-area">
            <div class="row mb-20-none">
                @forelse ($wallets ?? [] as $item)
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
                @empty
                <div class="alert alert-primary text-center">
                    {{ __("No Wallet Found!") }}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div> 
@endsection

@push('script')

@endpush
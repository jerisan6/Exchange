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
    ], 'active' => __("All Sell Crypto Logs")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("All Sell Crypto Logs") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'sell_crypto_search',
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.sell-crypto-table',[
                    'data'  => $transactions
                ])
                
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    itemSearch($("input[name=sell_crypto_search]"),$(".sell-crypto-search-table"),"{{ setRoute('admin.sell.crypto.search') }}",1);
</script>
@endpush
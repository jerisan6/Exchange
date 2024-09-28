@extends('user.layouts.master')

@push('css')
<style>
    .image-resize{
        width: 20px;
        height: 25px;
    }
</style>
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Sell Log")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title">{{ __("Sell Log") }}</h4>
        </div>
        <div class="dashboard-list-wrapper">
            @include('user.components.crypto-table.sell-crypto',[
                'data'  => $transactions
            ])
        </div>
    </div>
</div>
@endsection
@push('script')
    <script>
        itemSearch($("input[name=search_text]"),$(".transaction-results"),"{{ setRoute('user.transaction.search.sell.log') }}",1);
    </script>
@endpush
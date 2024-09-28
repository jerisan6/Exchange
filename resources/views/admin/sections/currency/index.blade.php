@extends('admin.layouts.master')

@push('css')
    <style>
        .fileholder {
            min-height: 194px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 150px !important;
        }
    </style>
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
    ], 'active' => __("Setup Currency")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            @includeUnless($default_currency,'admin.components.alerts.warning',['message' => "There is no default currency in your system."])
            <div class="table-header">
                <h5 class="title">{{ __("Setup Currency") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'currency_search',
                    ])
                    @include('admin.components.link.add-default',[
                        'text'          => __("Add Currency"),
                        'href'          => "#currency-add",
                        'class'         => "modal-btn",
                        'permission'    => "admin.currency.store", 
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.currency-table',[
                    'data'  => $currencies
                ])
            </div>
        </div>
        {{ get_paginate($currencies) }}
    </div>

    {{-- Currency Edit Modal --}}
    @include('admin.components.modals.edit-currency')

    {{-- Currency Add Modal --}}
    @include('admin.components.modals.add-currency')

@endsection

@push('script')
    <script>
        $(".delete-modal-button").click(function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));

            var actionRoute =  "{{ setRoute('admin.currency.delete') }}";
            var target      = oldData.code;
            var message     = `{{ __("Are you sure to delete") }} <strong>${oldData.code}</strong> {{ __("currency?") }}`;

            openDeleteModal(actionRoute,target,message);
        });
        $('.add-network-btn').click(function(){
            setTimeout(() => {
                $('select[name="network[]"]').first().select2();
            }, 500);
        });
        
        itemSearch($("input[name=currency_search]"),$(".currency-search-table"),"{{ setRoute('admin.currency.search') }}",1);
    </script>
    
@endpush
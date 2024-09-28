@extends('admin.layouts.master')

@push('css')

    <style>
        .fileholder {
            min-height: 374px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 330px !important;
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
    ], 'active' => __("Networks")])
@endsection

@section('content')
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ __($page_title) }}</h5>
            <div class="table-btn-area">
                @include('admin.components.link.add-default',[
                    'text'          => __("Add Network"),
                    'href'          => "#add-network",
                    'class'         => "modal-btn",
                    'permission'    => "admin.network.store",
                ])
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("Coin Name") }}</th>
                        <th>{{ __("Network Name") }}</th>
                        <th>{{ __("Status") }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($networks ?? [] as $key => $item)
                        <tr data-item="{{ $item }}">
                            <td>{{ $item->coin->name ?? ''}}</td>
                            <td>{{ $item->name ?? ''}}</td>
                            <td>
                                @include('admin.components.form.switcher',[
                                    'name'        => 'status',
                                    'value'       => $item->status,
                                    'options'     => ['Enable' => 1, 'Disable' => 0],
                                    'onload'      => true,
                                    'data_target' => $item->id,
                                ])
                            </td>
                            
                            <td>
                                @include('admin.components.link.edit-default',[
                                    'class'         => "edit-modal-button",
                                    'permission'    => "admin.network.update",
                                ])
                                <button class="btn btn--base btn--danger delete-modal-button" ><i class="las la-trash-alt"></i></button>
                            </td>
                        </tr>
                    @empty
                        @include('admin.components.alerts.empty',['colspan' => 4])
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ get_paginate($networks) }}
</div>
@include('admin.components.modals.network.add')

@include('admin.components.modals.network.edit')
@endsection
@push('script')
    <script>
        openModalWhenError("add-network","#add-network")

        $(".delete-modal-button").click(function(){
            var oldData     = JSON.parse($(this).parents("tr").attr("data-item"));
            var actionRoute = "{{ setRoute('admin.network.delete') }}";
            var target      = oldData.id;
            var message     = `{{ __("Are you sure to") }} <strong>{{ __("delete") }}</strong> {{ __("this Network?") }}`;

            openDeleteModal(actionRoute,target,message);

        });

        $(document).ready(function(){
            // Switcher
            switcherAjax("{{ setRoute('admin.network.status.update') }}");
        })
    </script>
@endpush
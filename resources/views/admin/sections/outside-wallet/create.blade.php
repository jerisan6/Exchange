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
    ], 'active' => __("Outside Wallet Create")])
@endsection

@section('content')
<div class="custom-card">
    <div class="card-header">
        <h6 class="title">{{ __($page_title) }}</h6>
    </div>
    <div class="card-body">
        <form class="card-form" action="{{ setRoute('admin.outside.wallet.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row justify-content-center mb-10-none">
                <div class="col-xl-6 col-lg-6 form-group">
                    <label>{{ __("Select Currency") }}*</label>
                    <select class="form--control select2-basic" name="currency">
                        <option disabled selected>{{ __("Select Currency") }}</option>
                        @foreach ($currencies as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}({{ $item->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-6 col-lg-6 form-group">
                    <label>{{ __("Select Network") }}*</label>
                    <select class="form--control select2-basic" name="network">
                        <option disabled selected>{{ __("Select Network") }}</option>
                    </select>
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input',[
                        'label'     => __("Public Address")."*",
                        'name'      => "public_address",
                        'value'     => old("public_address"),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input-text-rich',[
                        'label'     => __("Instruction"),
                        'name'      => "desc",
                        'value'     => old("desc"),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.payment-gateway.manual.input-field-generator')
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.button.form-btn',[
                        'class'         => "w-100 btn-loading",
                        'text'          => __("Submit"),
                        'permission'    => "admin.outside.wallet.store"
                    ])
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@push('script')
    <script>
        $(document).ready(function(){

            var getNetworkURL = "{{ setRoute('admin.outside.wallet.get.networks') }}";

            $('select[name="currency"]').on('change',function(){
                var currency = $(this).val();

                if(currency == "" || currency == null) {
                    return false;
                }

                $.post(getNetworkURL,{currency:currency,_token:"{{ csrf_token() }}"},function(response){
                    var networkOption = '';
                    if(response.data.currency.networks.length > 0){
                        $.each(response.data.currency.networks,function(index,item){
                            networkOption += `<option value="${item.network_id}">
                                ${item.network.name} (Arrival Time: ${item.network.arrival_time} min)</option>
                            `;
                        });
                        $('select[name=network]').html(networkOption);
                        $('select[name=network]').select2();
                    }
                });

            });
        });
    </script>
@endpush

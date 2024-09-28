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
    ], 'active' => __("Outside Wallet Edit")])
@endsection

@section('content')
<div class="custom-card">
    <div class="card-header">
        <h6 class="title">{{ __($page_title) }}</h6>
    </div>
    <div class="card-body">
        <form class="card-form" action="{{ setRoute('admin.outside.wallet.update',$data->public_address) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row justify-content-center mb-10-none">
                <div class="col-xl-6 col-lg-6 form-group">
                    <label>{{ __("Select Currency") }}*</label>
                    <select class="form--control select2-basic" name="currency">
                        
                            <option value="{{ $currencies->id }}" {{ $currencies->id == $data->currency_id ? 'selected' : '' }}>{{ $currencies->name }}({{ $currencies->code }})</option>
                        
                    </select>
                </div>
                <div class="col-xl-6 col-lg-6 form-group">
                    <label>{{ __("Select Network") }}*</label>
                    <select class="form--control select2-basic" name="network">
                        @foreach ($networks as $item)
                            <option value="{{ $item->network->id }}" {{ $item->network->id == $data->network_id ? 'selected' : '' }}>{{ $item->network->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input',[
                        'label'     => __("Public Address")."*",
                        'name'      => "public_address",
                        'value'     => old("public_address",$data->public_address),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input-text-rich',[
                        'label'     => __("Instruction"),
                        'name'      => "desc",
                        'value'     => old("desc",$data->desc),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    <div class="custom-inner-card input-field-generator" data-source="manual_gateway_input_fields">
                        <div class="card-inner-header">
                            <h6 class="title">{{ __("Collect Data") }}</h6>
                            <button type="button" class="btn--base add-row-btn"><i class="fas fa-plus"></i> {{ __("Add") }}</button>
                        </div>
                        <div class="card-inner-body">
                            <div class="results">
                                @foreach ($data->input_fields as $item)
                                    <div class="row add-row-wrapper align-items-end">
                                        <div class="col-xl-3 col-lg-3 form-group">
                                            @include('admin.components.form.input',[
                                                'label'     => __("Field Name")."*",
                                                'name'      => "label[]",
                                                'attribute' => "required",
                                                'value'     => $item->label,
                                            ])
                                        </div>
                                        <div class="col-xl-2 col-lg-2 form-group">
                                            @php
                                                $selectOptions = ['text' => "Input Text", 'file' => "File", 'textarea' => "Textarea"];
                                            @endphp
                                            <label>{{ __("Field Types") }}*</label>
                                            <select class="form--control nice-select field-input-type" name="input_type[]" data-old="{{ $item->type }}" data-show-db="true">
                                                @foreach ($selectOptions as $key => $value)
                                                    <option value="{{ $key }}" {{ ($key == $item->type) ? "selected" : "" }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
    
                                        <div class="field_type_input col-lg-4 col-xl-4">
                                            @if ($item->type == "file")
                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6 form-group">
                                                        @include('admin.components.form.input',[
                                                            'label'         => __("Max File Size (mb)")."*",
                                                            'name'          => "file_max_size[]",
                                                            'type'          => "number",
                                                            'attribute'     => "required",
                                                            'value'         => old('file_max_size[]',$item->validation->max),
                                                            'placeholder'   => "ex: 10",
                                                        ])
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6 form-group">
                                                        @include('admin.components.form.input',[
                                                            'label'         => __("File Extension")."*",
                                                            'name'          => "file_extensions[]",
                                                            'attribute'     => "required",
                                                            'value'         => old('file_extensions[]',implode(",",$item->validation->mimes)),
                                                            'placeholder'   => "ex: jpg, png, pdf",
                                                        ])
                                                    </div>
                                                </div>
                                            @else
                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6 form-group">
                                                        @include('admin.components.form.input',[
                                                            'label'         => __("Min Character")."*",
                                                            'name'          => "min_char[]",
                                                            'type'          => "number",
                                                            'attribute'     => "required",
                                                            'value'         => old('min_char[]',$item->validation->min),
                                                            'placeholder'   => "ex: 6",
                                                        ])
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6 form-group">
                                                        @include('admin.components.form.input',[
                                                            'label'         => __("Max Character")."*",
                                                            'name'          => "max_char[]",
                                                            'type'          => "number",
                                                            'attribute'     => "required",
                                                            'value'         => old('max_char[]',$item->validation->max),
                                                            'placeholder'   => "ex: 16",
                                                        ])
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
    
                                        <div class="col-xl-2 col-lg-2 form-group">
                                            @include('admin.components.form.switcher',[
                                                'label'     => __("Field Necessity")."*",
                                                'name'      => "field_necessity[]",
                                                'options'   => [__('Required') => 1,__('Optional') => 0],
                                                'value'     => old("field_necessity[]",$item->required),
                                            ])
                                        </div>
                                        <div class="col-xl-1 col-lg-1 form-group">
                                            <button type="button" class="custom-btn btn--base btn--danger row-cross-btn w-100 btn-loading"><i class="las la-times"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
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

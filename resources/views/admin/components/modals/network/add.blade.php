@if (admin_permission_by_name("admin.network.store"))
    <div id="add-network" class="mfp-hide large">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Add Network") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="card-form" action="{{ setRoute('admin.network.store') }}" method="POST">
                    @csrf
                    <div class="row mb-10-none">
                        <div class="col-xl-12 col-lg-12 form-group">
                            <label>{{ __("Select Coin") }}*</label>
                            <select class="form--control select2-basic" name="coin">
                                <option disabled selected>{{ __("Select Coin") }}</option>
                                @foreach ($coins as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.form.input',[
                                'label'         => __("Name")."*",
                                'name'          => "name",
                                'data_limit'    => 150,
                                'placeholder'   => __("Write Name")."...",
                                'value'         => old('name'),
                            ])
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            <label>{{ __("Arrival Time") }}*</label>
                            <div class="input-group">
                                <input type="text" class="form--control number-input" placeholder="{{ __("Write Arrival Time") }}..." name="arrival_time">
                                <span class="input-group-text">{{ __("Min") }}</span>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.form.textarea',[
                                'label'         => __("Description"),
                                'name'          => "description",
                                'placeholder'   => __("Write Description")."...",
                                'value'         => old('description'),
                            ])
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.button.form-btn',[
                                'class'         => "w-100 btn-loading",
                                'permission'    => "admin.network.store",
                                'text'          => __("Add"),
                            ])
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@push('script')
    
@endpush
@if (admin_permission_by_name("admin.network.update"))
    <div id="edit-network" class="mfp-hide large">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Edit Network") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="modal-form" method="POST" action="{{ setRoute('admin.network.update') }}">
                    @csrf
                    @method("PUT")
                    <input type="hidden" name="target" value="{{ old('target') }}">
                    <div class="row mb-10-none mt-2">
                        <div class="col-xl-12 col-lg-12 form-group">
                            <label>{{ __("Select Coin") }}*</label>
                            <select class="form--control select2-basic" name="edit_coin">
                                @foreach ($coins as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.form.input',[
                                'label'         => __("Name")."*",
                                'name'          => 'edit_name',
                                'value'         => old('edit_name')
                            ])
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            <label>{{ __("Arrival Time") }}*</label>
                            <div class="input-group">
                                <input type="text" class="form--control number-input" placeholder="{{ __("Write Arrival Time") }}..." name="edit_arrival_time">
                                <span class="input-group-text">{{ __("Min") }}</span>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.form.textarea',[
                                'label'         => __("Description"),
                                'name'          => "edit_description",
                                'placeholder'   => __("Write Description")."...",
                                'value'         => old('edit_description'),
                            ])
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                            <button type="submit" class="btn btn--base">{{ __("Update") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push("script")
        <script>
            openModalWhenError("edit-network","#edit-network");
            $(".edit-modal-button").click(function(){
                var oldData = JSON.parse($(this).parents("tr").attr("data-item"));
                var editModal = $("#edit-network");

                editModal.find("form").first().find("input[name=target]").val(oldData.id);
                editModal.find("input[name=edit_name]").val(oldData.name);
                editModal.find("input[name=edit_arrival_time]").val(oldData.arrival_time);
                editModal.find("textarea[name=edit_description]").val(oldData.description);
                
                editModal.find("select[name=edit_coin] option").each(function() {
                    if ($(this).val() == oldData.coin_id) {
                        $(this).prop('selected', true);
                    }
                });
                editModal.find("select[name=edit_coin]").trigger('change');

                openModalBySelector("#edit-network");
            });

        </script>
    @endpush
@endif
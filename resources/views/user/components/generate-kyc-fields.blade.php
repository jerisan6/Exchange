@if(isset($fields) && count($fields) > 0)
    @foreach ($kyc_fields as $item)
   
        @if ($item->type == "select")
            <div class="col-lg-12 form-group">
                <label for="{{ $item->name }}">{{ $item->label }}
                @if($item->required == true) 
                *
                @endif 
                </label>
                <select name="{{ $item->name }}" id="{{ $item->name }}" class="form--control nice-select">
                    <option selected disabled>{{ __("Choose One") }}</option>
                    @foreach ($item->validation->options as $innerItem)
                        <option value="{{ $innerItem }}">{{ $innerItem }}</option>
                    @endforeach
                </select>
                @error($item->name)
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        @elseif ($item->type == "file")
            <div class="col-lg-12 form-group">
                @include('admin.components.form.input',[
                    'label'             => $item->label,
                    'name'              => $item->name,
                    'type'              => $item->type,
                    'dynamic_required'  => $item->required,
                    'value'             => old($item->name),
                ])
            </div>
        @elseif ($item->type == "text")
            <div class="col-lg-12 form-group">
                @include('admin.components.form.input',[
                    'label'     => $item->label,
                    'name'      => $item->name,
                    'type'      => $item->type,
                    'dynamic_required'  => $item->required,
                    'value'     => old($item->name),
                ])
            </div>
        @elseif ($item->type == "textarea")
            <div class="col-lg-12 form-group">
                @include('admin.components.form.textarea',[
                    'label'     => $item->label,
                    'name'      => $item->name,
                    'dynamic_required'  => $item->required,
                    'value'     => old($item->name),
                ])
            </div>
        @endif
    @endforeach
@endisset
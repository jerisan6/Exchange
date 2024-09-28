
<div class="row align-items-end {{ $class ?? '' }}">
    <div class="col-xl-11 col-lg-11 form-group">
        <label>{{ __("Network") }}<span>*</span></label>
        @php
            $select2    = $select2 ?? true;
        @endphp
        <select class="form--control @if($select2 != false) select2-basic @endif network-select" name="network[]">
            <option disabled selected>{{ __("Select Network") }}</option>
            @foreach ($networks as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="col-xl-1 col-lg-1 form-group">
        <button type="button" class="custom-btn btn--base btn--danger row-cross-btn w-100"><i class="las la-times"></i></button>
    </div>
</div>


@push('script')
   <script>
        // currency code change 
        $('.currency-code').keyup(function(){
            var selectedCurrency = $(this).val();
            localStorage.setItem('selectedCurrency',selectedCurrency)
            $('.selected-currency').text(selectedCurrency);
        });
   </script>
@endpush
<table class="custom-table buy-crypto-search-table">
    <thead>
        <tr>
            <th>{{ __("TRX ID") }}</th>
            <th>{{ __("S. Wallet") }}</th>
            <th>{{ __("Amount") }}</th>
            <th>{{ __("P. Method") }}</th>
            <th>{{ __("Status") }}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($transactions as $item)
            <tr>
                <td><span>{{ $item->trx_id ?? '' }}</span></td>
                <td><span>{{ $item->details->data->wallet->name ?? '' }}</span></td>
                <td>{{ get_amount($item->amount,$item->details->data->wallet->code,8) }}</td>
                <td>{{ $item->currency->name ?? '' }} @if($item->currency->gateway->isManual()) ({{ __("Manual") }}) @endif</td>
                <td>
                   
                    @if ($item->status == global_const()::STATUS_PENDING)
                        <span>{{ __("Pending") }}</span>
                    @elseif ($item->status == global_const()::STATUS_CONFIRM_PAYMENT)
                        <span>{{ __("Confirm Payment") }}</span>
                    @elseif ($item->status == global_const()::STATUS_REJECT)
                        <span>{{ __("Rejected") }}</span>
                    @elseif ($item->status == global_const()::STATUS_CANCEL)
                        <span>{{ __("Canceled") }}</span>
                    @else
                        <span>{{ __("Delayed") }}</span>
                    @endif
                </td>
                <td>
                    <a href="{{ setRoute('admin.buy.crypto.details',$item->id) }}" class="btn btn--base btn--primary"><i class="las la-info-circle"></i></a>
                </td>
            </tr>
        @empty
            @include('admin.components.alerts.empty',['colspan' => 6])
        @endforelse
        
    </tbody>
</table>
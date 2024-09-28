<div class="transaction-results">
    @forelse ($transactions ?? [] as $item)
        <div class="dashboard-list-item-wrapper">
            <div class="dashboard-list-item sent">
                <div class="dashboard-list-left">
                    <div class="dashboard-list-user-wrapper">
                        <div class="dashboard-list-user-icon">
                            <i class="las la-arrow-up"></i>
                        </div>
                        <div class="dashboard-list-user-content">
                            <h4 class="title">{{ __($item->type) ?? '' }} <span>{{ $item->details->data->sender_wallet->name ?? '' }} ({{ $item->details->data->sender_wallet->code ?? '' }})</span></h4>
                            <span class="sub-title text--danger">{{ __("Sent") }} 
                                <span class="badge badge--warning ms-2">
                                    @if ($item->status == global_const()::STATUS_PENDING)
                                        <span>{{ __("Pending") }}</span>
                                    @elseif ($item->status == global_const()::STATUS_CONFIRM_PAYMENT)
                                        <span>{{ __("Confirm Payment") }}</span>
                                    @elseif ($item->status == global_const()::STATUS_CANCEL)
                                        <span>{{ __("Canceled") }}</span>
                                    @elseif ($item->status == global_const()::STATUS_REJECT)
                                        <span>{{ __("Reject") }}</span>
                                    @else
                                        <span>{{ __("Delayed") }}</span>
                                    @endif
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="dashboard-list-right">
                    <h4 class="main-money text--base">{{ $item->details->data->sending_amount ?? '' }} {{ $item->details->data->sender_wallet->code ?? '' }}</h4>
                    <h6 class="exchange-money">{{ $item->details->data->get_amount ?? '' }} {{ $item->details->data->receiver_wallet->code ?? '' }}</h6>
                </div>
            </div>
            <div class="preview-list-wrapper">
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-compact-disc"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Transaction ID") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->trx_id ?? '' }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-keyboard"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("From Wallet") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->details->data->sender_wallet->name ?? '' }} ({{ $item->details->data->sender_wallet->code ?? '' }})</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-hockey-puck"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("To Wallet") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->details->data->receiver_wallet->name ?? '' }} ({{ $item->details->data->receiver_wallet->code ?? '' }})</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-wallet"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Enter Amount") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--success">{{ $item->details->data->sending_amount ?? '' }} {{ $item->details->data->sender_wallet->code ?? '' }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-exchange-alt"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Exchange Rate") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--warning">1 {{ $item->details->data->sender_wallet->code ?? '' }} = {{ get_amount($item->details->data->exchange_rate,$item->details->data->receiver_wallet->code) }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-battery-half"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Network Fees") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--danger">{{ $item->details->data->total_charge ?? '' }} {{ $item->details->data->sender_wallet->code ?? '' }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-money-check-alt"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span class="last">{{ __("Total Payable Amount") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="last">{{ $item->details->data->payable_amount ?? '' }} {{ $item->details->data->sender_wallet->code ?? '' }}</span>
                    </div>
                </div>
                @if ($item->status == global_const()::STATUS_REJECT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-stop-circle"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span class="last">{{ __("Reject Reason") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span class="last">{{ $item->reject_reason ?? '' }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-primary text-center">
            {{ __("Transaction data not found!") }}
        </div>
    @endforelse
</div>
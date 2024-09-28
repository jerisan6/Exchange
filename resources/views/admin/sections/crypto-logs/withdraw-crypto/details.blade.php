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
        ],
        
    ], 'active' => __("Withdraw Crypto Log Details")])
@endsection

@section('content')
<div class="row mb-30-none">
    
    <div class="col-lg-6 mb-30">
        <div class="transaction-area">
            <h4 class="title mb-0"><i class="fas fa-user text--base me-2"></i>{{ __("Sender Information") }}</h4>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("Name") }}<span>{{ $transaction->user->full_name ?? '' }}</span></li>
                        <li>{{ __("Email") }}<span class="text-lowercase">{{ $transaction->user->email ?? '' }}</span></li>
                        <li>{{ __("Wallet Name") }}<span>{{ $transaction->details->data->sender_wallet->name ?? "" }} ({{ $transaction->details->data->sender_wallet->code ?? "" }})</span></li>
                        <hr>
                        <h4 class="title mb-0"><i class="fas fa-user text--base me-2"></i>{{ __("Receiver Information") }}</h4>
                        <li>{{ __("Wallet Address") }}<span>{{ $transaction->details->data->receiver_wallet->address ?? "" }}</span></li>
                        <li>{{ __("Wallet Code") }}<span>{{ $transaction->details->data->receiver_wallet->code ?? "" }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-30">
        <div class="transaction-area">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="title mb-0"><i class="fas fa-user text--base me-2"></i>{{ __("Transaction Device Information") }}</h4>
            </div>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("IP") }}<span>{{ $transaction_device->ip ?? '' }}</span></li>
                        <li>{{ __("Country") }}<span>{{ $transaction_device->country ?? '' }}</span></li>
                        <li>{{ __("City") }}<span>{{ $transaction_device->city ?? '' }}</span></li>
                        <li>{{ __("Browser") }}<span>{{ $transaction_device->browser ?? '' }}</span></li>
                        <li>{{ __("OS") }}<span>{{ $transaction_device->os ?? '' }}</span></li>
                        <li>{{ __("TimeZone") }}<span>{{ $transaction_device->timezone ?? '' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12 mb-30">
        <div class="transaction-area">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="title"><i class="fas fa-user text--base me-2"></i>{{ __("Payment Summary") }}</h4>
                @if ($transaction->status  == global_const()::STATUS_PENDING)
                    <div class="d-flex">
                        @include('admin.components.link.status-update',[
                            'text'          => __("Confirm"),
                            'href'          => "#confirm",
                            'class'         => "modal-btn",
                        ])
                        @include('admin.components.link.status-update',[
                            'text'          => __("Reject"),
                            'href'          => "#reject",
                            'class'         => "modal-btn ms-1",
                        ])
                    </div>
                @elseif($transaction->status  == global_const()::STATUS_CONFIRM_PAYMENT)
                    <button class="btn--base">{{ __("Confirmed") }}</button>
                @elseif($transaction->status  == global_const()::STATUS_REJECT)
                    <button class="btn--base">{{ __("Rejected") }}</button>
                @endif
            </div>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("Transaction Number") }} <span>{{ $transaction->trx_id ?? ''  }}</span> </li>
                        <li>{{ __("Transaction Type") }} <span>{{ $transaction->type ?? ''  }}</span> </li>
                        <li>{{ __("Send Amount") }} <span>{{ get_amount($transaction->amount,$transaction->details->data->sender_wallet->code)  }}</span> </li>
                        <li>{{ __("Exchange Rate") }} <span>{{ $transaction->details->data->sender_ex_rate ?? '' }} {{ $transaction->details->data->sender_wallet->code ?? '' }} = {{ $transaction->details->data->exchange_rate ?? '' }} {{ $transaction->details->data->receiver_wallet->code ?? '' }}</span> </li>
                        <li>{{ __("Total Charge") }} <span>{{ get_amount($transaction->total_charge,$transaction->details->data->sender_wallet->code)  }}</span> </li>
                        <li>{{ __("Payable Amount") }} <span>{{ get_amount($transaction->total_payable,$transaction->currency_code)  }}</span> </li>
                        @php
                            $get_amount = $transaction->amount * $transaction->details->data->exchange_rate; 
                        @endphp
                        <li>{{ __("Will Get Amount") }} <span>{{ get_amount($get_amount,$transaction->details->data->receiver_wallet->code)  }}</span> </li>
                        <li>{{ __("Payment Status") }}
                            @if ($transaction->status == global_const()::STATUS_PENDING)
                                <span>{{ __("Pending") }}</span>
                            @elseif ($transaction->status == global_const()::STATUS_CONFIRM_PAYMENT)
                                <span>{{ __("Confirm Payment") }}</span>
                            @elseif ($transaction->status == global_const()::STATUS_CANCEL)
                                <span>{{ __("Canceled") }}</span>
                            @elseif ($transaction->status == global_const()::STATUS_REJECT)
                                <span>{{ __("Rejected") }}</span>
                            @else
                                <span>{{ __("Delayed") }}</span>
                            @endif
                        </li>
                        <li>{{ __("Remark") }} <span>{{ $transaction->remark ?? 'N/A' }}</span></li>
                        @if ($transaction->status == global_const()::STATUS_REJECT)
                        <li>{{ __("Reject Reason") }} <span>{{ $transaction->reject_reason ?? 'N/A' }}</span></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- confirm modal --}}
<div id="confirm" class="mfp-hide large">
    <div class="modal-data">
        <div class="modal-header px-0">
            <h5 class="modal-title">{{ __("Transaction Number") }} :{{ $transaction->trx_id }}</h5>
        </div>
        <div class="modal-form-data">
            <form class="modal-form" method="POST" action="{{ setRoute('admin.withdraw.crypto.status.update',$transaction->trx_id) }}">
                @csrf
                <div class="row mb-10-none">
                    <h6>{{ __("Are you sure to CONFIRM this transaction?") }}</h6>
                    <input type="hidden" name="status" value="{{ global_const()::STATUS_CONFIRM_PAYMENT }}">
                    <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                        <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base">{{ __("Confirm") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- reject modal --}}
<div id="reject" class="mfp-hide large">
    <div class="modal-data">
        <div class="modal-header px-0">
            <h5 class="modal-title">{{ __("Transaction Number") }} :{{ $transaction->trx_id }}</h5>
        </div>
        <div class="modal-form-data">
            <form class="modal-form" method="POST" action="{{ setRoute('admin.withdraw.crypto.reject',$transaction->trx_id) }}">
                @csrf
                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.textarea',[
                            'label'         => __('Reject Reason'),
                            'name'          => 'reject_reason',
                        ])
                    </div>
                    <input type="hidden" name="status" value="{{ global_const()::STATUS_REJECT }}">
                    <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                        <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base">{{ __("Confirm") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

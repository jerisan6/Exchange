<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\Transaction;
use App\Http\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;

class TransactionLogController extends Controller
{
    /**
     * Method for buy crypto log
     */
    public function buyLog(){
        $status_code    = [1 => 'Pending',2 => 'STATUS_CONFIRM_PAYMENT', 3 => 'STATUS_CANCEL',4 => 'STATUS_REJECT'];
        $buy_log     = Transaction::auth()->where('type',PaymentGatewayConst::BUY_CRYPTO)->get()->map(function($data){
            if($data->currency->gateway->isTatum($data->currency->gateway) && $data->status == global_const()::STATUS_PENDING){
                $submit_url     = route('api.user.buy.crypto.payment.crypto.confirm',$data->trx_id); 
            }else{
                $submit_url     = '';
            }
            
            return [
                'id'                        => $data->id,
                'type'                      => $data->type,
                'user_id'                   => $data->user_id,
                'user_wallet_id'            => $data->user_wallet_id ?? '',
                'payment_gateway_id'        => $data->payment_gateway_id ?? '',
                'trx_id'                    => $data->trx_id,
                'amount'                    => $data->amount,
                'percent_charge'            => $data->percent_charge,
                'fixed_charge'              => $data->fixed_charge,
                'total_charge'              => $data->total_charge,
                'total_payable'             => $data->total_payable,
                'available_balance'         => $data->available_balance,
                'remark'                    => $data->remark,
                'details'                   => $data->details,
                'submit_url'                => $submit_url,
                'requirements'              => $data->details->payment_info->requirements ?? [],
                'reject_reason'             => $data->reject_reason,
                'status'                    => $data->status,
                'created_at'                => $data->created_at,
            ];
            
        });
        return Response::success([__("Buy Crypto Transactions Log")],[
            'status_code'   => $status_code,
            'buy_log'       => $buy_log,
        ],200);
    }
    /**
     * Method for withdraw log
     */
    public function withdrawLog(){
        $status_code    = [1 => 'Pending',2 => 'STATUS_CONFIRM_PAYMENT', 3 => 'STATUS_CANCEL',4 => 'STATUS_REJECT'];
        $withdraw_log       = Transaction::auth()->where('type',PaymentGatewayConst::WITHDRAW_CRYPTO)->get()->map(function($data){
            return [
                'id'                        => $data->id,
                'type'                      => $data->type,
                'user_id'                   => $data->user_id,
                'user_wallet_id'            => $data->user_wallet_id ?? '',
                'payment_gateway_id'        => $data->payment_gateway_id ?? '',
                'trx_id'                    => $data->trx_id,
                'amount'                    => $data->amount,
                'percent_charge'            => $data->percent_charge,
                'fixed_charge'              => $data->fixed_charge,
                'total_charge'              => $data->total_charge,
                'total_payable'             => $data->total_payable,
                'available_balance'         => $data->available_balance,
                'remark'                    => $data->remark,
                'details'                   => $data->details,
                'reject_reason'             => $data->reject_reason,
                'status'                    => $data->status,
                'created_at'                => $data->created_at,
            ];
        });
        return Response::success([__("Withdraw Crypto Transaction Logs")],[
            'status_code'  => $status_code,
            'withdraw_log' => $withdraw_log,
        ],200);
    }
    /**
     * Method for exchange log
     */
    public function exchangeLog(){
        $status_code    = [1 => 'Pending',2 => 'STATUS_CONFIRM_PAYMENT', 3 => 'STATUS_CANCEL',4 => 'STATUS_REJECT'];
        $exchange_log       = Transaction::auth()->where('type',PaymentGatewayConst::EXCHANGE_CRYPTO)->get()->map(function($data){
            
            return [
                'id'                        => $data->id,
                'type'                      => $data->type,
                'user_id'                   => $data->user_id,
                'user_wallet_id'            => $data->user_wallet_id ?? '',
                'trx_id'                    => $data->trx_id,
                'amount'                    => $data->amount,
                'percent_charge'            => $data->percent_charge,
                'fixed_charge'              => $data->fixed_charge,
                'total_charge'              => $data->total_charge,
                'total_payable'             => $data->total_payable,
                'available_balance'         => $data->available_balance,
                'remark'                    => $data->remark,
                'details'                   => $data->details,
                'reject_reason'             => $data->reject_reason,
                'status'                    => $data->status,
                'created_at'                => $data->created_at,
            ];
        });
        return Response::success([__("Exchange Crypto Transaction Logs")],[
            'status_code'  => $status_code,
            'exchange_log' => $exchange_log,
        ],200);
    }
    /**
     * Method for sell log
     */
    public function sellLog(){
        $status_code    = [1 => 'Pending',2 => 'STATUS_CONFIRM_PAYMENT', 3 => 'STATUS_CANCEL',4 => 'STATUS_REJECT'];
        $sell_log       = Transaction::auth()->where('type',PaymentGatewayConst::SELL_CRYPTO)->get()->map(function($data){
            
            return [
                'id'                        => $data->id,
                'type'                      => $data->type,
                'user_id'                   => $data->user_id,
                'user_wallet_id'            => $data->user_wallet_id ?? '',
                'payment_gateway_id'        => $data->payment_gateway_id ?? '',
                'trx_id'                    => $data->trx_id,
                'amount'                    => $data->amount,
                'percent_charge'            => $data->percent_charge,
                'fixed_charge'              => $data->fixed_charge,
                'total_charge'              => $data->total_charge,
                'total_payable'             => $data->total_payable,
                'available_balance'         => $data->available_balance,
                'remark'                    => $data->remark,
                'details'                   => json_decode($data->details->data->details),
                'data'                      => $data->details->data,
                'reject_reason'             => $data->reject_reason,
                'status'                    => $data->status,
                'created_at'                => $data->created_at,
            ];
        });
        return Response::success([__("Sell Crypto Transaction Logs")],[
            'status_code'  => $status_code,
            'sell_log' => $sell_log,
        ],200);
    }
}

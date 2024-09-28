<?php

namespace App\Http\Controllers\Api\V1\User;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\WithdrawCryptoMailNotification;

class WithdrawCryptoController extends Controller
{
    /**
     * Method for withdraw crypto data information
     */
    public function index(){
        $currencies                     = Currency::where('status',true)->orderBy('id')->get()->map(function($data){
            $wallet                     = UserWallet::auth()->where('currency_id',$data->id)->get()->map(function($item){
                return [
                    'id'                => $item->id,
                    'user_id'           => $item->user_id,
                    'currency_id'       => $item->currency_id,
                    'public_address'    => $item->public_address,
                    'balance'           => $item->balance
                ];
            });
            
            return [
                'id'                    => $data->id,
                'name'                  => $data->name,
                'code'                  => $data->code,
                'symbol'                => $data->code,
                'flag'                  => $data->flag,
                'rate'                  => $data->rate,
                'wallets'               => $wallet,
            ];
        });

        $image_paths = [
            'base_url'         => url("/"),
            'path_location'    => files_asset_path_basename("currency-flag"),
            'default_image'    => files_asset_path_basename("default"),

        ];
        $transaction_fees   = TransactionSetting::where('slug','withdraw')->first();
        return Response::success([__("Withdraw Crypto Data")],[
            'currencies'                    => $currencies,
            'transaction_fees'              => $transaction_fees,
            'currency_image_paths'          => $image_paths,
        ],200);
    }
    /**
     * Method for check valid wallet address
     */
    public function checkWalletAddress(Request $request){
        $validator      = Validator::make($request->all(),[
            'wallet_address'    => 'required',
        ]);
        if($validator->fails()) return Response::error($validator->errors()->all(),[]);
        $validated = $validator->validate();
        $user               = UserWallet::auth()->where('public_address',$validated['wallet_address'])->first();
        if($user) return Response::error(['Can\'t withdraw/request to your own'],[],404);
        $receiver_address   = UserWallet::with(['currency'])->where('public_address',$validated['wallet_address'])->first();
        
        if(!$receiver_address) return Response::error(['Receiver address not found'],[],404);
        return Response::success(['Wallet Address is valid.'],[
            'wallet_address'    => $receiver_address->public_address,
            'rate'              => $receiver_address->currency->rate,
            'code'              => $receiver_address->currency->code,
        ],200);
    }
    /**
     * Method for store withdraw crypto information
     */
    public function store(Request $request){
        $validator           = Validator::make($request->all(),[
            'amount'            => 'required',
            'sender_wallet'     => 'required',
            'wallet_address'    => 'required',
        ]);
        if($validator->fails()) return Response::error($validator->errors()->all(),[]);

        $validated          = $validator->validate();
        $amount             = $validated['amount'];
        $sender_wallet      = UserWallet::auth()->with(['currency'])->where('id',$validated['sender_wallet'])->first();
        if(!$sender_wallet) return Response::error(['Wallet not found!'],[],404);
        
        $user               = UserWallet::auth()->where('public_address',$validated['wallet_address'])->first();
        if($user) return Response::error(['Can\'t withdraw/request to your own'],[],404);
        
        if($amount > $sender_wallet->balance){
            return Response::error(['Insufficient Balance!'],[],404);
        }
        $sender_rate        = $sender_wallet->currency->rate / $sender_wallet->currency->rate;

        $receiver_address   = UserWallet::with(['currency'])->where('public_address',$validated['wallet_address'])->first();
        
        if(!$receiver_address) return Response::error(['Receiver address not found'],[],404);
        $exchange_rate      = $receiver_address->currency->rate / $sender_wallet->currency->rate;
        $transaction_fees   = TransactionSetting::where('slug','withdraw')->first();
        
        $min_limit          = $transaction_fees->min_limit;
        $max_limit          = $transaction_fees->max_limit;
        $min_amount         = $min_limit * $sender_wallet->currency->rate;
        $max_amount         = $max_limit * $sender_wallet->currency->rate;

        if($amount < $min_amount || $amount > $max_amount){
            return Response::error(['Please follow the transaction limit!'],[],404);
        }
        
        $fixed_charge               = $transaction_fees->fixed_charge * $sender_wallet->currency->rate;
        $percent_charge             = $transaction_fees->percent_charge;
        $percent_charge_calc        = ($amount / 100) * $percent_charge;
        $total_charge               = $fixed_charge + $percent_charge_calc;
        $payable_amount             = $amount + $total_charge;
        $will_get_amount            = $amount * $exchange_rate;
       
        if($payable_amount > $sender_wallet->balance){
            return Response::error(['Insufficient Balance!'],[],404);
        }

        $data                       = [
            'type'                  => PaymentGatewayConst::WITHDRAW_CRYPTO,
            'identifier'            => Str::uuid(),
            'data'                  => [
                'sender_wallet'     => [
                    'id'            => $sender_wallet->id,
                    'name'          => $sender_wallet->currency->name,
                    'code'          => $sender_wallet->currency->code,
                    'rate'          => $sender_wallet->currency->rate,
                ],
                'receiver_wallet'   => [
                    'address'       => $receiver_address->public_address,
                    'code'          => $receiver_address->currency->code,
                    'rate'          => $receiver_address->currency->rate,
                ],
                'amount'            => floatval($amount),
                'fixed_charge'      => $fixed_charge,
                'percent_charge'    => $percent_charge_calc,
                'total_charge'      => $total_charge,
                'sender_ex_rate'    => $sender_rate,
                'exchange_rate'     => $exchange_rate,
                'payable_amount'    => $payable_amount,
                'will_get'          => $will_get_amount,
            ],
        ];
        
        try{
            $data = TemporaryData::create($data);
        }catch(Exception $e){
            return Response::error(['Something went wrong! Please try again.'],[],404);
        }
        return Response::success([__("Withdraw Crypto Store Successfully.")],[
            'data'                      => $data,
        ],200);
    }
    /**
     * Method for withdraw crypto confirm
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function confirm(Request $request){
        $validator          = Validator::make($request->all(),[
            'identifier'    => 'required|string'
        ]);
        if($validator->fails()) return Response::error($validator->errors()->all(),[]);
        $basic_setting      = BasicSettings::first();
        $user               = auth()->user();
        $data               = TemporaryData::where('identifier',$request->identifier)->first();
        if(!$data) return Response::error(['Data not found!'],[],404);

        $trx_id             = generateTrxString("transactions","trx_id","WC",8);
        $wallet_id          = $data->data->sender_wallet->id;
        $sender_wallet      = UserWallet::auth()->where("id",$wallet_id)->first();
        $receiver_wallet    = UserWallet::where('public_address',$data->data->receiver_wallet->address)->first();
        $available_balance  = $sender_wallet->balance - $data->data->payable_amount;

        $transaction_data           = [
            'type'                  => $data->type,
            'user_id'               => $user->id,
            'user_wallet_id'        => $data->data->sender_wallet->id,
            'trx_id'                => $trx_id,
            'amount'                => $data->data->amount,
            'fixed_charge'          => $data->data->fixed_charge,
            'percent_charge'        => $data->data->percent_charge,
            'total_charge'          => $data->data->total_charge,  
            'total_payable'         => $data->data->payable_amount,
            'available_balance'     => $available_balance,
            'currency_code'         => $data->data->sender_wallet->code,
            'remark'                => ucwords(remove_special_char($data->type," ")) . " With " . $data->data->sender_wallet->name,
            'details'               => [
                'data'              => $data->data
            ],
            'status'                => global_const()::STATUS_CONFIRM_PAYMENT,
            'created_at'            => now(),
        ];

        try{
            $transaction = Transaction::create($transaction_data);
            $this->updateSenderWalletBalance($sender_wallet,$available_balance);
            $this->updateReceiverWalletBalance($receiver_wallet,$data->data->will_get);
            $this->userNotification($data);
            $this->transactionDevice($transaction);
            if($basic_setting->email_notification == true){
                Notification::route('mail',$user->email)->notify(new WithdrawCryptoMailNotification($user,$data,$trx_id));
            }
            $data->delete();
        }catch(Exception $e){
            return Response::error(['Something went wrong! Please try again.'],[],404);
        }
        return Response::success([__("Withdraw Crypto Successfull")],[],200);
    }

    //update sender wallet balance
    function updateSenderWalletBalance($sender_wallet,$available_balance){
        $sender_wallet->update([
            'balance'   => $available_balance,
        ]);
    }

    //update receiver wallet balance 
    function updateReceiverWalletBalance($receiver_wallet,$amount){
        $update_balance = $receiver_wallet->balance + $amount;
        $receiver_wallet->update([
            'balance'   => $update_balance,
        ]);
    }

    //user notification
    function userNotification($data){
        UserNotification::create([
            'user_id'       => auth()->user()->id,
            'message'       => [
                'title'     => "Withdraw Crypto",
                'wallet'    => $data->data->sender_wallet->name,
                'code'      => $data->data->sender_wallet->code,
                'amount'    => doubleval($data->data->amount),
                'status'    => global_const()::STATUS_CONFIRM_PAYMENT,
                'success'   => "Successfull."
            ],
        ]);
    }

    // transaction device
    function transactionDevice($transaction){
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();
        $mac = "";

        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $transaction->id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}

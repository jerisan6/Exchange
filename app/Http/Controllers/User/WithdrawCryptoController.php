<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use App\Http\Helpers\Response;
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
     * Method for view withdraw crypto page
     * @return view
     */
    public function index(){
        $page_title         = "- Withdraw Crypto";
        $currencies         = UserWallet::auth()->with(['currency'])->get();
        $transaction_fees   = TransactionSetting::where('slug','withdraw')->first();

        return view('user.sections.withdraw-crypto.index',compact(
            'page_title',
            'currencies',
            'transaction_fees'
        ));
    }
    /**
     * Method for check wallet address
     */
    public function checkWalletAddress(Request $request){
        $validator    = Validator::make($request->all(),[
            'wallet_address'  => 'required',           
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors()->all());
        }
        $wallet_address     = $request->wallet_address;
        $wallet['data']     = UserWallet::with(['currency'])->where('public_address',$wallet_address)->first();
        $user               = UserWallet::auth()->where('public_address',$wallet_address)->first();

        if($wallet['data'] && @$user->public_address == @$wallet['data']->public_address){
            return response()->json(['own'=>'Can\'t withdraw/request to your own']);
        }
        return response($wallet); 
    }
    /**
     * Method for store the withdraw crypto information
     */
    public function store(Request $request){
        $validator              = Validator::make($request->all(),[
            'amount'            => 'required',
            'sender_wallet'     => 'required',
            'wallet_address'    => 'required',
        ]);
        if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());

        $validated          = $validator->validate();
        $amount             = $validated['amount'];
        $sender_wallet      = UserWallet::auth()->with(['currency'])->where('id',$validated['sender_wallet'])->first();
        if(!$sender_wallet) return back()->with(['error' => ['Wallet not found']]);

        $user               = UserWallet::auth()->where('public_address',$validated['wallet_address'])->first();
        if($user) return back()->with(['error' => ['Can\'t withdraw/request to your own']]);
        if($amount > $sender_wallet->balance){
            return back()->with(['error' => ['Sorry! Insufficient Balance.']]);
        }
        $sender_rate        = $sender_wallet->currency->rate / $sender_wallet->currency->rate;
        $receiver_address   = UserWallet::with(['currency'])->where('public_address',$validated['wallet_address'])->first();
        if(!$receiver_address) return back()->with(['error' => ['Receiver address not found']]);
        $exchange_rate      = $receiver_address->currency->rate / $sender_wallet->currency->rate;
        $transaction_fees   = TransactionSetting::where('slug','withdraw')->first();
        
        $min_limit          = $transaction_fees->min_limit;
        $max_limit          = $transaction_fees->max_limit;
        $min_amount         = $min_limit * $sender_wallet->currency->rate;
        $max_amount         = $max_limit * $sender_wallet->currency->rate;

        if($amount < $min_amount || $amount > $max_amount){
            return back()->with(['error' => ['Please follow the transaction limit.']]);
        }
        
        $fixed_charge           = $transaction_fees->fixed_charge * $sender_wallet->currency->rate;
        $percent_charge         = $transaction_fees->percent_charge;
        $percent_charge_calc    = ($amount / 100) * $percent_charge;
        $total_charge           = $fixed_charge + $percent_charge_calc;
        $payable_amount         = $amount + $total_charge;
        $will_get_amount        = $amount * $exchange_rate;
        if($payable_amount > $sender_wallet->balance){
            return back()->with(['error' => ['Sorry! Insufficient Balance.']]);
        }

        $data                   = [
            'type'              => PaymentGatewayConst::WITHDRAW_CRYPTO,
            'identifier'        => Str::uuid(),
            'data'              => [
                'sender_wallet' => [
                    'id'        => $sender_wallet->id,
                    'name'      => $sender_wallet->currency->name,
                    'code'      => $sender_wallet->currency->code,
                    'rate'      => $sender_wallet->currency->rate,
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
            return back()->with(['error'  => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('user.withdraw.crypto.preview',$data->identifier);
    }
    /**
     * Method for view the preview page
     * @param $identifier
     * @return view
     */
    public function preview($identifier){
        $page_title     = '- Withdraw Crypto Preview';
        $data           = TemporaryData::where('identifier',$identifier)->first();
        if(!$data) return back()->with(['error'  => ['Data not Found']]);

        return view('user.sections.withdraw-crypto.preview',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for withdraw crypto confirm
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function confirm(Request $request,$identifier){
        $basic_setting      = BasicSettings::first();
        $user               = auth()->user();
        $data               = TemporaryData::where('identifier',$identifier)->first();
        if(!$data) return back()->with(['error' => ['Data not found!']]);

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
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('user.withdraw.crypto.index')->with(['success'  => ['Withdraw Crypto Successful!']]);
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

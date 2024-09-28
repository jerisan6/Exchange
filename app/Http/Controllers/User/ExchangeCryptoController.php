<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\ExchangeCryptoMailNotification;

class ExchangeCryptoController extends Controller
{
    /**
     * Method for view exchange crypto page
     * @return view
     */
    public function index(){
        $page_title         = "- Exchange Crypto";
        $currencies         = UserWallet::auth()->with(['currency'])->orderBy('id')->get();
        $reciever_currencies         = UserWallet::auth()->with(['currency'])->orderBy('id','desc')->get();
        $transaction_fees   = TransactionSetting::where('slug','exchange')->first();
        
        return view('user.sections.exchange-crypto.index',compact(
            'page_title',
            'currencies',
            'transaction_fees',
            'reciever_currencies'
        ));
    }
    /**
     * Method for store exchange crypto
     */
    public function store(Request $request){
        $validator   = Validator::make($request->all(),[
            'send_amount'       => 'required',
            'sender_wallet'     => 'required',
            'receive_money'     => 'required',
            'receiver_currency' => 'required',
        ]);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput($request->all());
        }
        $validated                  = $validator->validate();
        $send_amount                = $validated['send_amount'];
        $sender_wallet              = $validated['sender_wallet'];
        $receiver_wallet            = $validated['receiver_currency'];
        $validated['identifier']    = Str::uuid();

        if($sender_wallet == $receiver_wallet ){
            return back()->with(['error' => ['You cannot exchange crypto using the same wallet']]);
        }
        $send_wallet        = UserWallet::auth()->where("id",$sender_wallet)->first();
        
        if(!$send_wallet){
            return back()->with(['error' => ['Sender Wallet not found!']]);
        }
        
        if($send_amount > $send_wallet->balance){
            return back()->with(['error' => ['Sorry! Insufficient Balance.']]);
        }
        $receive_wallet     = UserWallet::auth()->where("id",$receiver_wallet)->first();
        
        if(!$receive_wallet){
            return back()->with(['error' => ['Receiver Wallet not found!']]);
        }

        $sender_rate        = $send_wallet->currency->rate;
        $receiver_rate      = $receive_wallet->currency->rate;
        $exchange_rate      = $receiver_rate / $sender_rate;
        $amount             = $send_amount * $exchange_rate;
        $transaction_fees   = TransactionSetting::where('slug','exchange')->first();
        $min_limit          = $transaction_fees->min_limit;
        $max_limit          = $transaction_fees->max_limit;
        $min_limit_calc     = $min_limit * $exchange_rate;
        $max_limit_calc     = $max_limit * $exchange_rate;

        if($amount < $min_limit_calc || $amount > $max_limit_calc){
            return back()->with(['error' => ['Please follow the transaction limit.']]);
        }
        $charge_rate    = $send_wallet->currency->rate / $send_wallet->currency->rate;
        $fixed_charge   = $transaction_fees->fixed_charge * $sender_rate;
        $percent_charge = ($send_amount / 100) * $transaction_fees->percent_charge;
        
        $total_charge   = $fixed_charge + $percent_charge;
        $payable        = $send_amount + $total_charge;

        if($payable > $send_wallet->balance){
            return back()->with(['error' => ['Sorry! Insufficient Balance.']]);
        }
        
    
        $data               = [
            'type'          => PaymentGatewayConst::EXCHANGE_CRYPTO,
            'identifier'    => $validated['identifier'],
            'data'          => [
                'sender_wallet'     => [
                    'id'            => $send_wallet->id,
                    'name'          => $send_wallet->currency->name,
                    'code'          => $send_wallet->currency->code,
                    'rate'          => $send_wallet->currency->rate,
                    'balance'       => $send_wallet->balance,
                ],
                'receiver_wallet'   => [
                    'id'            => $receive_wallet->id,
                    'name'          => $receive_wallet->currency->name,
                    'code'          => $receive_wallet->currency->code,
                    'rate'          => $receive_wallet->currency->rate,
                    'balance'       => $receive_wallet->balance,
                ],
                'exchange_rate'     => $exchange_rate,
                'sending_amount'    => floatval($send_amount),
                'fixed_charge'      => $fixed_charge,
                'percent_charge'    => $percent_charge,
                'total_charge'      => $total_charge,
                'payable_amount'    => $payable,
                'get_amount'        => $amount,
            ],        
        ];
        
        try{
            $temporary_data = TemporaryData::create($data);
        }catch(Exception $e){
           return back()->with(['error' => ['Something went wrong! Please try again.']]); 
        }
        return redirect()->route('user.exchange.crypto.preview',$temporary_data->identifier);
    }
    /**
     * Method for show exchange data in the preview page
     * @param $identifier
     * @return view
     */
    public function preview($identifier){
        $page_title     = "- Exchange Preview";
        $data           = TemporaryData::where('identifier',$identifier)->first();
        if(!$data) return back()->with(['error' => ['Data not Found!']]);

        return view('user.sections.exchange-crypto.preview',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for confirm exchange crypto
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function confirm($identifier){
        $basic_setting      = BasicSettings::first();
        $user               = auth()->user();
        $record             = TemporaryData::where('identifier',$identifier)->first();
        if(!$record) return back()->with(['error'  => ['Data not found!']]);
        $trx_id = generateTrxString("transactions","trx_id","EC",8);
        
        $send_wallet  = $record->data->sender_wallet->id;
        
        $sender_wallet  = UserWallet::auth()->where("id",$send_wallet)->first();
        
    
        $available_balance  = $sender_wallet->balance - $record->data->payable_amount;
        

        $data            = [
            'type'              => $record->type,
            'user_id'           => auth()->user()->id,
            'user_wallet_id'    => $record->data->sender_wallet->id,
            'trx_id'            => $trx_id,
            'amount'            => $record->data->sending_amount,
            'percent_charge'    => $record->data->percent_charge,
            'fixed_charge'      => $record->data->fixed_charge,
            'total_charge'      => $record->data->total_charge,
            'total_payable'     => $record->data->payable_amount,
            'available_balance' => $available_balance,
            'currency_code'     => $record->data->sender_wallet->code,
            'remark'            => ucwords(remove_special_char($record->type," ")) . " With " . $record->data->sender_wallet->name,
            'details'           => [
                'data' => $record->data
            ],
            'status'            => global_const()::STATUS_CONFIRM_PAYMENT,
            'created_at'        => now(),
        ];

        try{
            $transaction = Transaction::create($data);
            
            $this->updateSenderWalletBalance($sender_wallet,$available_balance);
            $this->updateReceiverWalletBalance($record->identifier);
            $this->userNotification($record);
            $this->transactionDevice($transaction);
            if($basic_setting->email_notification == true){
                Notification::route("mail",$user->email)->notify(new ExchangeCryptoMailNotification($user,$record,$trx_id));
            }
            $record->delete();

        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('user.exchange.crypto.index')->with(['success' => ['Exchange Crypto Successful.']]);       
    }

    //update sender wallet balance
    function updateSenderWalletBalance($sender_wallet,$available_balance){
        $sender_wallet->update([
            'balance'   => $available_balance,
        ]);
    }

    // update receiver wallet balance
    function updateReceiverWalletBalance($identifier){
        $record          = TemporaryData::where('identifier',$identifier)->first();
        if(!$record) return back()->with(['error'  => ['Data not found!']]);
        $wallet  = $record->data->receiver_wallet->id;

        $receiver_wallet  = UserWallet::auth()->where("id",$wallet)->first();

        $balance  = $receiver_wallet->balance + $record->data->get_amount;

        $receiver_wallet->update([
            'balance'   => $balance,
        ]);
    }

    //user notification
    function userNotification($record){
        UserNotification::create([
            'user_id'       => auth()->user()->id,
            'message'       => [
                'title'     => "Exchange Crypto",
                'wallet'    => $record->data->sender_wallet->name,
                'code'      => $record->data->sender_wallet->code,
                'amount'    => doubleval($record->data->sending_amount),
                'status'    => global_const()::STATUS_CONFIRM_PAYMENT,
                'success'   => "Successfully Request Send."
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

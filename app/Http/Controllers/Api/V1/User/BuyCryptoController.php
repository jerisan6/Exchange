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
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\CryptoTransaction;
use App\Models\Admin\CurrencyHasNetwork;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\PaymentGatewayCurrency;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\BuyCryptoManualMailNotification;
use App\Http\Helpers\PaymentGateway as PaymentGatewayHelper;

class BuyCryptoController extends Controller
{
    use ControlDynamicInputFields;
    /**
     * Method for store buy crypto index
     */
    public function index(){
        $wallet_type                    = ['Inside Wallet','Outside Wallet'];
        $currencies                     = Currency::where('status',true)->orderBy('id')->get()->map(function($data){
            $networks                   = CurrencyHasNetwork::where('currency_id',$data->id)->get()->map(function($item){
                return [
                    'id'                => $item->id,
                    'currency_id'       => $item->currency_id,
                    'network_id'        => $item->network_id,
                    'name'              => $item->network->name,
                    'arrival_time'      => $item->network->arrival_time
                ];
            });

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
                'networks'              => $networks,
                'wallet'                => $wallet
            ];
        });

        
        $payment_gateway    = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::payment_method_slug());
            $gateway->where('status', 1);
        })->get();

        $image_paths = [
            'base_url'         => url("/"),
            'path_location'    => files_asset_path_basename("currency-flag"),
            'default_image'    => files_asset_path_basename("default"),

        ];

        $payment_image_paths = [
            'base_url'         => url("/"),
            'path_location'    => files_asset_path_basename("payment-gateways"),
            'default_image'    => files_asset_path_basename("default"),

        ];

        return Response::success([__("Buy Crypto Data")],[
            'wallet_type'               => $wallet_type,
            'currencies'                => $currencies,
            'payment_gateway'           => $payment_gateway,
            'currency_image_paths'      => $image_paths,
            'payment_image_paths'       => $payment_image_paths,
        ],200);
    }
    /**
     * Method for store the buy crypto data
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request){
        if($request->wallet_type == global_const()::INSIDE_WALLET){
            $validator  = Validator::make($request->all(),[
                'sender_currency'   => 'required',
                'network'           => 'required',
                'amount'            => 'required',
                'payment_method'    => 'required'
            ]);
            if($validator->fails()){
                return Response::error($validator->errors()->all(),[]);
            }
            $validated          = $validator->validate();
            $wallet_currency    = $validated['sender_currency'];
            $amount             = $validated['amount'];

            $user_wallet  = UserWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
                $q->where("id",$wallet_currency)->active();
            })->active()->first();
            
            
            if(!$user_wallet){
                return Response::error(['Wallet Not Found!'],[],404);
            }

            $network                    = CurrencyHasNetwork::where('currency_id',$wallet_currency)->where('network_id',$validated['network'])->first();
            if(!$network){
                return Response::error(['network Not Found!'],[],404);
            }
            $payment_gateway_currency   = PaymentGatewayCurrency::with(['gateway'])->where('id',$validated['payment_method'])->first();

            if(!$payment_gateway_currency){
                return Response::error(['Payment Method Not Found!'],[],404);
            }
            $rate           = $payment_gateway_currency->rate / $user_wallet->currency->rate;
            
            $min_max_rate   = $user_wallet->currency->rate / $payment_gateway_currency->rate;
            $min_amount     = $payment_gateway_currency->min_limit * $min_max_rate;
            $max_amount     = $payment_gateway_currency->max_limit * $min_max_rate;
            
            if($amount < $min_amount || $amount > $max_amount){
                return Response::error(['Please follow the transaction limit.'],[],404);
            }
            $fixed_charge   = $payment_gateway_currency->fixed_charge;
            $convert_amount = $amount * $rate;
            $percent_charge = ($convert_amount / 100) * $payment_gateway_currency->percent_charge;
            $total_charge   = $fixed_charge + $percent_charge;
            $payable_amount = $convert_amount + $total_charge;;
            $validated['identifier']    = Str::uuid();
            $data                       = [
                'type'                  => PaymentGatewayConst::BUY_CRYPTO,
                'identifier'            => $validated['identifier'],
                'data'                  => [
                    'wallet'            => [
                        'type'          => $request->wallet_type,
                        'wallet_id'     => $user_wallet->id,
                        'currency_id'   => $user_wallet->currency->id,
                        'name'          => $user_wallet->currency->name,
                        'code'          => $user_wallet->currency->code,
                        'rate'          => $user_wallet->currency->rate,
                        'balance'       => $user_wallet->balance,
                    ],
                    'network'           => [
                        'name'          => $network->network->name,
                        'arrival_time'  => $network->network->arrival_time,
                        'fees'          => $network->fees,
                    ],
                    'payment_method'    => [
                        'id'            => $payment_gateway_currency->id,
                        'name'          => $payment_gateway_currency->name,
                        'code'          => $payment_gateway_currency->currency_code,
                        'alias'         => $payment_gateway_currency->alias,
                        'rate'          => $payment_gateway_currency->rate,
                    ],
                    'amount'            => floatval($amount),
                    'exchange_rate'     => $rate,
                    'min_max_rate'      => $min_max_rate,
                    'fixed_charge'      => floatval($fixed_charge),
                    'percent_charge'    => $percent_charge,
                    'total_charge'      => $total_charge,
                    'payable_amount'    => $payable_amount,
                    'will_get'          => floatval($amount),
                ],
            ];
            try{
                $temporary_data = TemporaryData::create($data);
            }catch(Exception $e){
                return Response::error(['Something went wrong! Please try again.'],[],404);
            }
            return Response::success([__("Buy Crypto Store Successfully.")],[
                'data'            => $temporary_data,
            ],200);
        }else{
            $validator  = Validator::make($request->all(),[
                'sender_currency'   => 'required',
                'network'           => 'required',
                'wallet_address'    => 'required',
                'amount'            => 'required',
                'payment_method'    => 'required'
            ]);
            if($validator->fails()){
                return Response::error($validator->errors()->all(),[]);
            }
            $validated          = $validator->validate();
            $wallet_currency    = $validated['sender_currency'];
            $amount             = $validated['amount'];

            $user_wallet  = UserWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
                $q->where("id",$wallet_currency)->active();
            })->active()->first();

            if(!$user_wallet){
                return Response::error(['Wallet Not Found!'],[],404);
            }

            $network                    = CurrencyHasNetwork::where('currency_id',$wallet_currency)->where('network_id',$validated['network'])->first();
            if(!$network){
                return Response::error(['network Not Found!'],[],404);
            }
            
            $payment_gateway_currency   = PaymentGatewayCurrency::with(['gateway'])->where('id',$validated['payment_method'])->first();

            if(!$payment_gateway_currency){
                return Response::error(['Payment Method Not Found!'],[],404);
            }
            $rate           = $payment_gateway_currency->rate / $user_wallet->currency->rate;
            
            $min_max_rate   = $user_wallet->currency->rate / $payment_gateway_currency->rate;
            $min_amount     = $payment_gateway_currency->min_limit * $min_max_rate;
            $max_amount     = $payment_gateway_currency->max_limit * $min_max_rate;
            if($amount < $min_amount || $amount > $max_amount){
                return Response::error(['Please follow the transaction limit.'],[],404);
            }
            $fixed_charge   = $payment_gateway_currency->fixed_charge;
            $convert_amount = $amount * $rate;
            $percent_charge = ($convert_amount / 100) * $payment_gateway_currency->percent_charge;
            $total_charge   = $fixed_charge + $percent_charge;
            $payable_amount = $convert_amount + $total_charge;
            
            $validated['identifier']    = Str::uuid();
            $data                       = [
                'type'                  => PaymentGatewayConst::BUY_CRYPTO,
                'identifier'            => $validated['identifier'],
                'data'                  => [
                    'wallet'            => [
                        'type'          => $request->wallet_type,
                        'wallet_id'     => $user_wallet->id,
                        'currency_id'   => $user_wallet->currency->id,
                        'name'          => $user_wallet->currency->name,
                        'code'          => $user_wallet->currency->code,
                        'rate'          => $user_wallet->currency->rate,
                        'address'       => $validated['wallet_address'],
                        'balance'       => $user_wallet->balance,
                    ],
                    'network'           => [
                        'name'          => $network->network->name,
                        'arrival_time'  => $network->network->arrival_time,
                        'fees'          => $network->fees,
                    ],
                    'payment_method'    => [
                        'id'            => $payment_gateway_currency->id,
                        'name'          => $payment_gateway_currency->name,
                        'code'          => $payment_gateway_currency->currency_code,
                        'alias'         => $payment_gateway_currency->alias,
                        'rate'          => $payment_gateway_currency->rate,
                    ],
                    'amount'            => floatval($amount),
                    'exchange_rate'     => $rate,
                    'min_max_rate'      => $min_max_rate,
                    'fixed_charge'      => floatval($fixed_charge),
                    'percent_charge'    => $percent_charge,
                    'total_charge'      => $total_charge,
                    'payable_amount'    => $payable_amount,
                    'will_get'          => floatval($amount),
                ],
            ];
            try{
                $temporary_data = TemporaryData::create($data);
            }catch(Exception $e){
                return Response::error(['Something went wrong! Please try again.'],[],404);
            }
            return Response::success([__("Buy Crypto Store Successfully.")],[
                'data'            => $temporary_data,
            ],200);
        }
    }
    /**
     * Method for buy crypto submit
     * @param Illuminate\Http\Request $request
     */
    public function submit(Request $request){
        try{
            $instance = PaymentGatewayHelper::init($request->all())->type(PaymentGatewayConst::BUY_CRYPTO)->gateway()->api()->render();
        }catch(Exception $e) {
            return Response::error([$e->getMessage()],[],500);
        }
        if($instance instanceof RedirectResponse === false && isset($instance['gateway_type']) && $instance['gateway_type'] == PaymentGatewayConst::MANUAL) {
            return Response::error([__('Can\'t submit manual gateway in automatic link')],[],400);
        }
        return Response::success([__('Payment gateway response successful')],[
            'redirect_url'          => $instance['redirect_url'],
            'redirect_links'        => $instance['redirect_links'],
            'action_type'           => $instance['type']  ?? false, 
            'address_info'          => $instance['address_info'] ?? [],
        ],200); 
    }
    public function success(Request $request, $gateway){
        try{
            $token = PaymentGatewayHelper::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();
            if(!$temp_data) {
                if(Transaction::where('callback_ref',$token)->exists()) {
                    return Response::success([__('Transaction request sended successfully!')],[],400);
                }else {
                    return Response::error([__('Transaction failed. Record didn\'t saved properly. Please try again')],[],400);
                }
            }
            $update_temp_data = json_decode(json_encode($temp_data->data),true);
            $update_temp_data['callback_data']  = $request->all();
            $temp_data->update([
                'data'  => $update_temp_data,
            ]);
            $temp_data = $temp_data->toArray();
            $instance = PaymentGatewayHelper::init($temp_data)->type(PaymentGatewayConst::BUY_CRYPTO)->responseReceive();

            if($instance instanceof RedirectResponse) return $instance;
        }catch(Exception $e) {
            return Response::error([$e->getMessage()],[],500);
        }
        return Response::success(["Payment successful, please go back your app"],200);   
    }
    /**
     * Method for buy crypto cancel
     * @param $gateway
     * @param \Illuminate\Http\Request $request
     */
    public function cancel(Request $request,$gateway) {
        $token = PaymentGatewayHelper::getToken($request->all(),$gateway);
        $temp_data = TemporaryData::where("identifier",$token)->first();
        try{
            if($temp_data != null) {
                $temp_data->delete();
            }
        }catch(Exception $e) {
            return Response::error([$e->getMessage()]);
        }
        return Response::success([__('Payment process cancel successfully!')],[],200);
    }
    /**
     * Method for buy crypto SSL Commerz Success
     * @param $gateway
     * @param \Illuminate\Http\Request $request
     */
    public function postSuccess(Request $request, $gateway)
    {
        try{
            $token = PaymentGatewayHelper::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();
            if($temp_data && $temp_data->data->creator_guard != 'api') {
                Auth::guard($temp_data->data->creator_guard)->loginUsingId($temp_data->data->creator_id);
            }
        }catch(Exception $e) {
            return Response::error([$e->getMessage()]);
        }
        return $this->success($request, $gateway);
    }
    /**
     * Method for buy crypto SSL Commerz Cancel
     * @param $gateway
     * @param \Illuminate\Http\Request $request
     */
    public function postCancel(Request $request, $gateway)
    {
        try{
            $token = PaymentGatewayHelper::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();
            if($temp_data && $temp_data->data->creator_guard != 'api') {
                Auth::guard($temp_data->data->creator_guard)->loginUsingId($temp_data->data->creator_id);
            }
        }catch(Exception $e) {
            return Response::error([$e->getMessage()]);
        }
        return $this->cancel($request, $gateway);
    }
    /**
     * Manual Input Fields
     */
    public function manualInputFields(Request $request) {
       
        $validator = Validator::make($request->all(),[
            'alias'         => "required|string|exists:payment_gateway_currencies",
        ]);

        if($validator->fails()) {
            return Response::error($validator->errors()->all(),[],400);
        }

        $validated = $validator->validate();
        $gateway_currency = PaymentGatewayCurrency::where("alias",$validated['alias'])->first();

        $gateway = $gateway_currency->gateway;

        if(!$gateway->isManual()) return Response::error([__('Can\'t get fields. Requested gateway is automatic')],[],400);

        if(!$gateway->input_fields || !is_array($gateway->input_fields)) return Response::error([__("This payment gateway is under constructions. Please try with another payment gateway")],[],503);

        try{
            $input_fields = json_decode(json_encode($gateway->input_fields),true);
            $input_fields = array_reverse($input_fields);
        }catch(Exception $e) {
            return Response::error([__("Something went wrong! Please try again")],[],500);
        }
        
        return Response::success([__('Payment gateway input fields fetch successfully!')],[
            'gateway'           => [
                'desc'          => $gateway->desc
            ],
            'input_fields'      => $input_fields,
            'currency'          => $gateway_currency->only(['alias']),
        ],200);
    }
    /**
     * Method for buy crypto manual Submit
     * @param $token
     * @param \Illuminate\Http\Request $request
     */
    public function manualSubmit(Request $request) {
        
        $basic_setting = BasicSettings::first();
        $user          = auth()->user();
        try{
            $instance = PaymentGatewayHelper::init($request->all())->gateway()->get();
        }catch(Exception $e) {
            return Response::error([$e->getMessage()],[],401);
        }
        $data   = TemporaryData::where('identifier',$request->identifier)->first();
        
        // Check it's manual or automatic
        if(!isset($instance['gateway_type']) || $instance['gateway_type'] != PaymentGatewayConst::MANUAL) return Response::error([__('Can\'t submit automatic gateway in manual link')],[],400);
       
        $gateway_currency = PaymentGatewayCurrency::find($data->data->payment_method->id);
        if(!$gateway_currency || !$gateway_currency->gateway->isManual()) return Response::error([__('Selected Gateway is invalid')],[],400);
        $gateway = $gateway_currency->gateway;
        $amount = $instance['amount'];
    
        if(!$amount) Response::error([__('Transaction Failed. Failed to save information. Please try again')],[],400);
        
        $wallet = UserWallet::find($data->data->wallet->wallet_id ?? null);
        if(!$wallet) return Response::error([__('Your Wallet is invalid')],[],400);
        
        $this->file_store_location = "transaction";
        $dy_validation_rules = $this->generateValidationRules($gateway->input_fields);
        
        $validator = Validator::make($request->all(),$dy_validation_rules);
        
        if($validator->fails()) return Response::error($validator->errors()->all(),[],400);
        $validated = $validator->validate();
        
        $get_values = $this->placeValueWithFields($gateway->input_fields,$validated);
        
        
        $trx_id = generateTrxString("transactions","trx_id","BC",8);
        // Make Transaction
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'type'                          => PaymentGatewayConst::BUY_CRYPTO,
                'user_id'                       => $wallet->user->id,
                'user_wallet_id'                => $wallet->id,
                'payment_gateway_currency_id'   => $gateway_currency->id,
                'trx_id'                        => $trx_id,
                'amount'                        => $amount->requested_amount,
                'percent_charge'                => $amount->percent_charge,
                'fixed_charge'                  => $amount->fixed_charge,
                'total_charge'                  => $amount->total_charge,
                'total_payable'                 => $amount->total_amount,
                'available_balance'             => $wallet->balance,
                'currency_code'                 => $gateway_currency->currency_code,
                'remark'                        => ucwords(remove_special_char(PaymentGatewayConst::BUY_CRYPTO," ")) . " With " . $gateway_currency->name,
                'details'                       => json_encode(['input_values' => $get_values,'data' => $data->data]),
                'status'                        => global_const()::STATUS_PENDING,
                'callback_ref'                  => $output['callback_ref'] ?? null,
                'created_at'                    => now(),
            ]);

            if( $basic_setting->email_notification == true){
                Notification::route("mail",$user->email)->notify(new BuyCryptoManualMailNotification($user,$data,$trx_id));
            }
            $this->transactionDevice($id);
            $this->userNotification($id);
            DB::table("temporary_datas")->where("identifier",$data->identifier)->delete();
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return Response::error([__('Something went wrong! Please try again')],[],400);
        }
        return Response::success([__('Transaction Success. Please wait for admin confirmation')],200);
        
    }
    //user notification
    function userNotification($id){
        $user   = auth()->user();
        $data = Transaction::where('id',$id)->first();
        
        UserNotification::create([
            'user_id'       => $user->id,
            'message'       => [
                'title'     => "Buy Crypto",
                'payment'   => $data->details->data->payment_method->name,
                'wallet'    => $data->details->data->wallet->name,
                'code'      => $data->details->data->wallet->code,
                'amount'    => doubleval($data->amount),
                'success'   => "Successfully Added."
            ],
        ]);
    }
    // transaction device
    function transactionDevice($id){
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();
        $mac = "";
        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $id,
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
    /**
     * Method for gateway additional fields
     */
    public function gatewayAdditionalFields(Request $request) {
        $validator = Validator::make($request->all(),[
            'currency'          => "required|string|exists:payment_gateway_currencies,alias",
        ]);
        if($validator->fails()) return Response::error($validator->errors()->all(),[],400);
        $validated = $validator->validate();

        $gateway_currency = PaymentGatewayCurrency::where("alias",$validated['currency'])->first();

        $gateway = $gateway_currency->gateway;

        $data['available'] = false;
        $data['additional_fields']  = [];
        

        return Response::success([__('Request response fetch successfully!')],$data,200);
    }
    /**
     * Method for buy crypto crypto payment address
     * @param $trx_id
     * @param \Illuminate\Http\Request $request
     */
    public function cryptoPaymentConfirm(Request $request, $trx_id) 
    {
        
        $transaction = Transaction::where('trx_id',$trx_id)->where('status', global_const()::STATUS_PENDING)->firstOrFail();
        

        $dy_input_fields = $transaction->details->payment_info->requirements ?? [];
        $validation_rules = $this->generateValidationRules($dy_input_fields);

        $validated = [];
        if(count($validation_rules) > 0) {
            $validated = Validator::make($request->all(), $validation_rules)->validate();
        }

        if(!isset($validated['txn_hash'])) return Response::error(['Transaction hash is required for verify']);

        $receiver_address = $transaction->details->payment_info->receiver_address ?? "";

        // check hash is valid or not
        $crypto_transaction = CryptoTransaction::where('txn_hash', $validated['txn_hash'])
                                                ->where('receiver_address', $receiver_address)
                                                ->where('asset',$transaction->currency->currency_code)
                                                ->where(function($query) {
                                                    return $query->where('transaction_type',"Native")
                                                                ->orWhere('transaction_type', "native");
                                                })
                                                ->where('status',PaymentGatewayConst::NOT_USED)
                                                ->first();
                                                
        if(!$crypto_transaction) return Response::error(['Transaction hash is not valid! Please input a valid hash'],[],404);

        if($crypto_transaction->amount >= $transaction->total_payable == false) {
            if(!$crypto_transaction) Response::error(['Insufficient amount added. Please contact with system administrator'],[],400);
        }

        DB::beginTransaction();
        try{

            // Update user wallet balance
            DB::table($transaction->user_wallets->getTable())
            ->where('id',$transaction->user_wallets->id)
            ->increment('balance',$transaction->details->data->will_get);

            // update crypto transaction as used
            DB::table($crypto_transaction->getTable())->where('id', $crypto_transaction->id)->update([
                'status'        => PaymentGatewayConst::USED,
            ]);

            // update transaction status
            $transaction_details = json_decode(json_encode($transaction->details), true);
            $transaction_details['payment_info']['txn_hash'] = $validated['txn_hash'];

            DB::table($transaction->getTable())->where('id', $transaction->id)->update([
                'details'       => json_encode($transaction_details),
                'status'        => global_const()::STATUS_CONFIRM_PAYMENT,
            ]);

            DB::commit();

        }catch(Exception $e) {
            DB::rollback();
            return Response::error(['Something went wrong! Please try again'],[],500);
        }

        return Response::success(['Payment Confirmation Success!'],[],200);
    }
    /**
     * Redirect Users for collecting payment via Button Pay (JS Checkout)
     */
    public function redirectBtnPay(Request $request, $gateway)
    {
        try{
            return PaymentGatewayHelper::init([])->type(PaymentGatewayConst::BUY_CRYPTO)->handleBtnPay($gateway, $request->all());
        }catch(Exception $e) {
            return Response::error([$e->getMessage()], [], 500);
        }
    }
}

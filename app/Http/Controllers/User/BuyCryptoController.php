<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\Admin\Network;
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
     * Method for view buy crypto page
     * @return view
     */
    public function index(){
        $page_title         = "- Buy Crypto";
        $currencies         = Currency::with(['networks'])->where('status',true)->orderBy('id')->get();
        $first_currency     = Currency::where('status',true)->first();
        $payment_gateway    = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::payment_method_slug());
            $gateway->where('status', 1);
        })->get();

        return view('user.sections.buy-crypto.index',compact(
            'page_title',
            'currencies',
            'first_currency',
            'payment_gateway'
        ));
    }
    /**
     * Method for get networks
     * @param string $currency
     */
    public function getCurrencyNetworks(Request $request){
        $validator    = Validator::make($request->all(),[
            'currency'  => 'required|integer',           
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        $currency  = Currency::with(['networks' => function($network) {
            $network->with(['network']);
        }])->find($request->currency);
        if(!$currency) return Response::error(['Currency Not Found'],404);

        return Response::success(['Data fetch successfully'],['currency' => $currency],200);
    }

    /**
     * Method for store the buy crypto information
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
                return back()->withErrors($validator)->withInput($request->all());
            }
            $validated          = $validator->validate();
            $wallet_currency    = $validated['sender_currency'];
            $amount             = $validated['amount'];

            $user_wallet  = UserWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
                $q->where("id",$wallet_currency)->active();
            })->active()->first();
            

            if(!$user_wallet){
                return back()->with(['error' => ['Wallet not found!']]);
            }

            $network        = Network::where('id',$validated['network'])->first();
            $network_info   = CurrencyHasNetwork::where('currency_id',$user_wallet->currency->id)->where('network_id',$network->id)->first();
            
            $payment_gateway_currency   = PaymentGatewayCurrency::with(['gateway'])->where('id',$validated['payment_method'])->first();

            if(!$payment_gateway_currency){
                return back()->with(['error' => ['Payment Method not found!']]);
            }
            $rate           = $payment_gateway_currency->rate / $user_wallet->currency->rate;
            
            $min_max_rate   = $user_wallet->currency->rate / $payment_gateway_currency->rate;
            $min_amount     = $payment_gateway_currency->min_limit * $min_max_rate;
            $max_amount     = $payment_gateway_currency->max_limit * $min_max_rate;
            if($amount < $min_amount || $amount > $max_amount){
                return back()->with(['error' => ['Please follow the transaction limit.']]);
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
                        'balance'       => $user_wallet->balance,
                    ],
                    'network'           => [
                        'name'          => $network->name,
                        'arrival_time'  => $network->arrival_time,
                        'fees'          => $network_info->fees,
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
                    'fixed_charge'      => $fixed_charge,
                    'percent_charge'    => $percent_charge,
                    'total_charge'      => $total_charge,
                    'payable_amount'    => $payable_amount,
                    'will_get'          => floatval($amount),
                ],
            ];
            try{
                $temporary_data = TemporaryData::create($data);
            }catch(Exception $e){
                return back()->with(['error' => ['Something went wrong! Please try again.']]);
            }
            return redirect()->route('user.buy.crypto.preview',$temporary_data->identifier);
        }else{
            $validator  = Validator::make($request->all(),[
                'sender_currency'   => 'required',
                'network'           => 'required',
                'wallet_address'    => 'required',
                'amount'            => 'required',
                'payment_method'    => 'required'
            ]);
            if($validator->fails()){
                return back()->withErrors($validator)->withInput($request->all());
            }
            $validated          = $validator->validate();
            $wallet_currency    = $validated['sender_currency'];
            $amount             = $validated['amount'];

            $user_wallet  = UserWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
                $q->where("id",$wallet_currency)->active();
            })->active()->first();

            if(!$user_wallet){
                return back()->with(['error' => ['Wallet not found!']]);
            }

            $network        = Network::where('id',$validated['network'])->first();
            $network_info   = CurrencyHasNetwork::where('currency_id',$user_wallet->currency->id)->where('network_id',$network->id)->first();
            
            $payment_gateway_currency   = PaymentGatewayCurrency::with(['gateway'])->where('id',$validated['payment_method'])->first();

            if(!$payment_gateway_currency){
                return back()->with(['error' => ['Payment Method not found!']]);
            }
            $rate           = $payment_gateway_currency->rate / $user_wallet->currency->rate;
            
            $min_max_rate   = $user_wallet->currency->rate / $payment_gateway_currency->rate;
            $min_amount     = $payment_gateway_currency->min_limit * $min_max_rate;
            $max_amount     = $payment_gateway_currency->max_limit * $min_max_rate;
            if($amount < $min_amount || $amount > $max_amount){
                return back()->with(['error' => ['Please follow the transaction limit.']]);
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
                        'name'          => $network->name,
                        'arrival_time'  => $network->arrival_time,
                        'fees'          => $network_info->fees,
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
                    'fixed_charge'      => $fixed_charge,
                    'percent_charge'    => $percent_charge,
                    'total_charge'      => $total_charge,
                    'payable_amount'    => $payable_amount,
                    'will_get'          => floatval($amount),
                ],
            ];
            try{
                $temporary_data = TemporaryData::create($data);
            }catch(Exception $e){
                return back()->with(['error' => ['Something went wrong! Please try again.']]);
            }
            return redirect()->route('user.buy.crypto.preview',$temporary_data->identifier);
        }

    }
    /**
     * Method for buy crypto preview page
     * @param $identifier
     * @param Illuminate\Http\Request $request
     */
    public function preview($identifier){
        $page_title     = "- Buy Crypto Preview";
        $data           = TemporaryData::where('identifier',$identifier)->first();
        if(!$data) return back()->with(['error' => ['Data not found!']]);
        return view('user.sections.buy-crypto.preview',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for buy crypto submit
     * @param Illuminate\Http\Request $request
     */
    public function submit(Request $request){
       
        try{
            $instance = PaymentGatewayHelper::init($request->all())->type(PaymentGatewayConst::BUY_CRYPTO)->gateway()->render();
           
            if($instance instanceof RedirectResponse === false && isset($instance['gateway_type']) && $instance['gateway_type'] == PaymentGatewayConst::MANUAL) {
                $manual_handler = $instance['distribute'];
                return $this->$manual_handler($instance);
            }
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
        
        return $instance;
    }
    /**
     * Method for buy crypto success
     * @param $gateway
     * @param \Illuminate\Http\Request $request
     */
    public function success(Request $request, $gateway){
    
        try{
            $token = PaymentGatewayHelper::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("type",PaymentGatewayConst::BUY_CRYPTO)->where("identifier",$token)->first();
            

            if(Transaction::where('callback_ref', $token)->exists()) {
                if(!$temp_data) return redirect()->route('user.buy.crypto.index')->with(['success' => ['Successfully added money']]);;
            }else {
                if(!$temp_data) return redirect()->route('user.buy.crypto.index')->with(['error' => ['Transaction failed. Record didn\'t saved properly. Please try again.']]);
            }

            $update_temp_data = json_decode(json_encode($temp_data->data),true);
            
            $update_temp_data['callback_data']  = $request->all();

            $temp_data->update([
                'data'  => $update_temp_data,
            ]);
            $temp_data = $temp_data->toArray();
            
            $instance = PaymentGatewayHelper::init($temp_data)->type(PaymentGatewayConst::BUY_CRYPTO)->setProjectCurrency(PaymentGatewayConst::PROJECT_CURRENCY_MULTIPLE)->responseReceive();
            if($instance instanceof RedirectResponse) return $instance;
        }catch(Exception $e) {
            return redirect()->route("user.buy.crypto.index")->with(['error' => [$e->getMessage()]]);
        }
        return redirect()->route("user.buy.crypto.index")->with(['success' => ['Buy Crypto Successful.']]);
    }
    /**
     * Method for pagadito success
     */
    public function successPagadito(Request $request, $gateway){
        $token = PaymentGatewayHelper::getToken($request->all(),$gateway);
        $temp_data = TemporaryData::where("type",PaymentGatewayConst::BUY_CRYPTO)->where("identifier",$token)->first();
        if($temp_data->data->creator_guard == 'web'){
            Auth::guard($temp_data->data->creator_guard)->loginUsingId($temp_data->data->creator_id);
            try{
                if(Transaction::where('callback_ref', $token)->exists()) {
                    if(!$temp_data) return redirect()->route("user.buy.crypto.index")->with(['success' => [__('Buy Crypto Successful.')]]);
                }else {
                    if(!$temp_data) return redirect()->route('index')->with(['error' => [__("transaction_record")]]);
                }

                $update_temp_data = json_decode(json_encode($temp_data->data),true);
                $update_temp_data['callback_data']  = $request->all();
                $temp_data->update([
                    'data'  => $update_temp_data,
                ]);
                $temp_data = $temp_data->toArray();
                $instance = PaymentGatewayHelper::init($temp_data)->type(PaymentGatewayConst::BUY_CRYPTO)->setProjectCurrency(PaymentGatewayConst::PROJECT_CURRENCY_MULTIPLE)->responseReceive();
            }catch(Exception $e) {
                return back()->with(['error' => [$e->getMessage()]]);
            }
            return redirect()->route("user.buy.crypto.index")->with(['success' => [__('Buy Crypto Successful.')]]);
        }elseif($temp_data->data->creator_guard =='api'){
            $creator_table = $temp_data->data->creator_table ?? null;
            $creator_id = $temp_data->data->creator_id ?? null;
            $creator_guard = $temp_data->data->creator_guard ?? null;
            $api_authenticated_guards = PaymentGatewayConst::apiAuthenticateGuard();
            if($creator_table != null && $creator_id != null && $creator_guard != null) {
                if(!array_key_exists($creator_guard,$api_authenticated_guards)) return Response::success([__('Request user doesn\'t save properly. Please try again')],[],400);
                $creator = DB::table($creator_table)->where("id",$creator_id)->first();
                if(!$creator) return Response::success([__('Request user doesn\'t save properly. Please try again')],[],400);
                $api_user_login_guard = $api_authenticated_guards[$creator_guard];
                Auth::guard($api_user_login_guard)->loginUsingId($creator->id);
            }
            try{
                if(!$temp_data) {
                    if(Transaction::where('callback_ref',$token)->exists()) {
                        return Response::success([__('Buy Crypto Successful.')],[],400);
                    }else {
                        return Response::error([__('transaction_record')],[],400);
                    }
                }
                $update_temp_data = json_decode(json_encode($temp_data->data),true);
                $update_temp_data['callback_data']  = $request->all();
                $temp_data->update([
                    'data'  => $update_temp_data,
                ]);
                $temp_data = $temp_data->toArray();
                $instance = PaymentGatewayHelper::init($temp_data)->type(PaymentGatewayConst::BUY_CRYPTO)->setProjectCurrency(PaymentGatewayConst::PROJECT_CURRENCY_MULTIPLE)->responseReceive();

                // return $instance;
            }catch(Exception $e) {
                return Response::error([$e->getMessage()],[],500);
            }
            return Response::success([__('Buy Crypto Successful.')],200);
        }

    }
    /**
     * Method for buy crypto cancel
     * @param $gateway
     * @param \Illuminate\Http\Request $request
     */
    public function cancel(Request $request, $gateway) {
        if($request->has('token')) {
            $identifier = $request->token;
            if($temp_data = TemporaryData::where('identifier', $identifier)->first()) {
                $temp_data->delete();
            }
        }
        return redirect()->route('user.buy.crypto.index');
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
            
            Auth::guard($temp_data->data->creator_guard)->loginUsingId($temp_data->data->creator_id);
        }catch(Exception $e) {
            return redirect()->route('index');
        }
        return $this->success($request, $gateway);
    }
    /**
     * Method for buy crypto redirect html form
     */
    public function redirectUsingHTMLForm(Request $request, $gateway)
    {
        $temp_data = TemporaryData::where('identifier', $request->token)->first();
        
        if(!$temp_data || $temp_data->data->action_type != PaymentGatewayConst::REDIRECT_USING_HTML_FORM) return back()->with(['error' => ['Request token is invalid!']]);
        $redirect_form_data = $temp_data->data->redirect_form_data;
        $action_url         = $temp_data->data->action_url;
        $form_method        = $temp_data->data->form_method;
        
        return view('payment-gateway.redirect-form', compact('redirect_form_data', 'action_url', 'form_method'));
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
            Auth::guard($temp_data->data->creator_guard)->loginUsingId($temp_data->data->creator_id);
        }catch(Exception $e) {
            
            return redirect()->route('index');
        }
        return $this->cancel($request, $gateway);
    }
    /**
     * Method for buy crypto Callback
     * @param $gateway
     * @param \Illuminate\Http\Request $request
     */
    public function callback(Request $request,$gateway) {

        $callback_token = $request->get('token');
        $callback_data = $request->all();

        try{
            PaymentGatewayHelper::init([])->type(PaymentGatewayConst::BUY_CRYPTO)->setProjectCurrency(PaymentGatewayConst::PROJECT_CURRENCY_MULTIPLE)->handleCallback($callback_token,$callback_data,$gateway);
        }catch(Exception $e) {
            // handle Error
            logger($e);
        }
    }
    /**
     * Method for buy crypto Manual payment store
     * @param $payment_info
     * @param \Illuminate\Http\Request $request
     */
    public function handleManualPayment($payment_info) {

        
        // Insert temp data
        $data = [
            'type'          => PaymentGatewayConst::BUY_CRYPTO,
            'identifier'    => generate_unique_string("temporary_datas","identifier",16),
            'data'          => [
                'gateway_currency_id'    => $payment_info['currency']->id,
                'amount'                 => $payment_info['amount'],
                'wallet_id'              => $payment_info['wallet']->id,
                'form_data'              => $payment_info['form_data']['identifier'],
            ],
        ];

        try{
            TemporaryData::create($data);
        }catch(Exception $e) {
            return redirect()->route('user.buy.crypto.index')->with(['error' => ['Failed to save data. Please try again']]);
        }
        return redirect()->route('user.buy.crypto.manual.form',$data['identifier']);
    }
    /**
     * Method for buy crypto manual form show
     * @param $token
     * @param \Illuminate\Http\Request $request
     */
    public function showManualForm($token) {
        
        $tempData = TemporaryData::search($token)->first();
        if(!$tempData || $tempData->data == null || !isset($tempData->data->gateway_currency_id)) return redirect()->route('user.buy.crypto.index')->with(['error' => ['Invalid request']]);
        $gateway_currency = PaymentGatewayCurrency::find($tempData->data->gateway_currency_id);
        if(!$gateway_currency || !$gateway_currency->gateway->isManual()) return redirect()->route('user.buy.crypto.index')->with(['error' => ['Selected gateway is invalid']]);
        $gateway = $gateway_currency->gateway;
        if(!$gateway->input_fields || !is_array($gateway->input_fields)) return redirect()->route('user.buy.crypto.index')->with(['error' => ['This payment gateway is under constructions. Please try with another payment gateway']]);
        $amount = $tempData->data->amount;
        
        $page_title = "- Payment Instructions";
        return view('user.sections.buy-crypto.manual.instruction',compact("gateway","page_title","token","amount"));
    }
    /**
     * Method for buy crypto manual Submit
     * @param $token
     * @param \Illuminate\Http\Request $request
     */
    public function manualSubmit(Request $request,$token) {
        $basic_setting = BasicSettings::first();
        $user          = auth()->user();
        
        $request->merge(['identifier' => $token]);
        $tempDataValidate = Validator::make($request->all(),[
            'identifier'        => "required|string|exists:temporary_datas",
        ])->validate();
        
        $tempData = TemporaryData::search($tempDataValidate['identifier'])->first();
        
        if(!$tempData || $tempData->data == null || !isset($tempData->data->gateway_currency_id)) return redirect()->route('user.buy.crypto.index')->with(['error' => ['Invalid request']]);
        $gateway_currency = PaymentGatewayCurrency::find($tempData->data->gateway_currency_id);
        if(!$gateway_currency || !$gateway_currency->gateway->isManual()) return redirect()->route('user.buy.crypto.index')->with(['error' => ['Selected gateway is invalid']]);
        $gateway = $gateway_currency->gateway;
        $amount = $tempData->data->amount ?? null;
        if(!$amount) return redirect()->route('user.buy.crypto.index')->with(['error' => ['Transaction Failed. Failed to save information. Please try again']]);
        $wallet = UserWallet::find($tempData->data->wallet_id ?? null);
        if(!$wallet) return redirect()->route('user.buy.crypto.index')->with(['error' => ['Your wallet is invalid!']]);

        $this->file_store_location  = "transaction";
        $dy_validation_rules        = $this->generateValidationRules($gateway->input_fields);
            
        
        $validated  = Validator::make($request->all(),$dy_validation_rules)->validate();
        $get_values = $this->placeValueWithFields($gateway->input_fields,$validated);
        
        $data   = TemporaryData::where('identifier',$tempData->data->form_data)->first();
        
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
            DB::table("temporary_datas")->where("identifier",$token)->delete();
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return redirect()->route('user.buy.crypto.manual.form',$token)->with(['error' => ['Something went wrong! Please try again']]);
        }
        return redirect()->route('user.buy.crypto.index')->with(['success' => ['Transaction Success. Please wait for admin confirmation']]);
    }
    //user notification
    function userNotification($id){
        $user   = auth()->user();
        $data   = Transaction::where('id',$id)->first();
        
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
     * Method for buy crypto crypto payment address
     * @param $trx_id
     * @param \Illuminate\Http\Request $request
     */
    public function cryptoPaymentAddress(Request $request, $trx_id) {

        $page_title = "- Crypto Payment Address";
        $transaction = Transaction::where('trx_id', $trx_id)->firstOrFail();

        if($transaction->currency->gateway->isCrypto() && $transaction->details?->payment_info?->receiver_address ?? false) {
            return view('user.sections.buy-crypto.crypto.address', compact(
                'transaction',
                'page_title',
            ));
        }

        return abort(404);
    }
    /**
     * Method for buy crypto crypto payment confirm
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

        if(!isset($validated['txn_hash'])) return back()->with(['error' => ['Transaction hash is required for verify']]);

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
                                                
        if(!$crypto_transaction) return back()->with(['error' => ['Transaction hash is not valid! Please input a valid hash']]);

        if($crypto_transaction->amount >= $transaction->total_payable == false) {
            if(!$crypto_transaction) return back()->with(['error' => ['Insufficient amount added. Please contact with system administrator']]);
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
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success' => ['Payment Confirmation Success.']]);
    }
    /**
     * Redirect Users for collecting payment via Button Pay (JS Checkout)
     */
    public function redirectBtnPay(Request $request, $gateway)
    {
        try{
            return PaymentGatewayHelper::init([])->type(PaymentGatewayConst::BUY_CRYPTO)->handleBtnPay($gateway, $request->all());
        }catch(Exception $e) {
            return redirect()->route('user.buy.crypto.index')->with(['error' => [$e->getMessage()]]);
        }
    }

}

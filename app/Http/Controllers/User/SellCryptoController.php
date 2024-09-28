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
use App\Constants\PaymentGatewayConst;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\OutsideWalletAddress;
use App\Models\Admin\PaymentGatewayCurrency;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Notifications\User\SellCryptoEmailNotification;

class SellCryptoController extends Controller
{
    use ControlDynamicInputFields;
    /**
     * Method for view sell crypto page
     * @return view
     */
    public function index(){
        $page_title         = "- Sell Crypto";
        $currencies         = Currency::with(['networks'])->where('status',true)->orderBy('id')->get();
        $first_currency     = Currency::where('status',true)->first();
        $payment_gateway    = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::money_out_slug());
            $gateway->where('status', 1);
        })->get();
        
        return view('user.sections.sell-crypto.index',compact(
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
     * Method for store sell crypto data
     */
    public function store(Request $request){
        
        if($request->wallet_type == global_const()::INSIDE_WALLET){
            $validator           = Validator::make($request->all(),[
                'wallet_type'       => 'required',
                'sender_currency'   => 'required',
                'network'           => 'required',
                'amount'            => 'required',
                'payment_method'    => 'required',
            ]);
            if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());

            $validated          = $validator->validate();
            $amount             = $validated['amount'];
            $wallet_currency    = $validated['sender_currency'];
            
            $user_wallet        = UserWallet::auth()->whereHas("currency",function($q) use($wallet_currency) {
                $q->where("currency_id",$wallet_currency)->active();
            })->active()->first();
            
            if(!$user_wallet) return back()->with(['error' => ['Wallet not found!']]);
            if($amount > $user_wallet->balance){
                return back()->with(['error' => ['Sorry! Insufficient Balance.']]);
            }
            
            $network            = Network::where('id',$validated['network'])->first();
           
            $payment_gateway_currency   = PaymentGatewayCurrency::where('id',$validated['payment_method'])->whereHas('gateway', function ($gateway) {
                $gateway->where('slug', PaymentGatewayConst::money_out_slug())->where('status', 1);
            })->first();
            
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
            $fixed_charge   = ($payment_gateway_currency->fixed_charge) * $min_max_rate;
            $percent_charge = ($amount / 100) * $payment_gateway_currency->percent_charge;
            $total_charge   = $fixed_charge + $percent_charge;
            $payable_amount = $amount + $total_charge;
            $will_get       = $amount * $rate;
            if($payable_amount > $user_wallet->balance){
                return back()->with(['error' => ['Sorry! Insufficient Balance.']]);
            }

            $data                       = [
                'type'                  => PaymentGatewayConst::SELL_CRYPTO,
                'identifier'            => Str::uuid(),
                'data'                  => [
                    'sender_wallet'     => [
                        'type'          => $request->wallet_type,
                        'wallet_id'     => $user_wallet->id,
                        'currency_id'   => $user_wallet->currency->id,
                        'name'          => $user_wallet->currency->name,
                        'code'          => $user_wallet->currency->code,
                        'rate'          => floatval($user_wallet->currency->rate),
                        'balance'       => floatval($user_wallet->balance),
                    ],
                    'network'           => [
                        'id'            => $network->id,
                        'name'          => $network->name,
                        'arrival_time'  => $network->arrival_time,
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
                    'total_payable'     => $payable_amount,
                    'will_get'          => floatval($will_get),
                ]
            ];
        
            try{
                $temporary_data         = TemporaryData::create($data);
            }catch(Exception $e){
                return back()->with(['error' => ['Something went wrong! Please try again.']]);
            }
            return redirect()->route('user.sell.crypto.payment.info',$temporary_data->identifier);
        }else{
            $validator           = Validator::make($request->all(),[
                'wallet_type'       => 'required',
                'sender_currency'   => 'required',
                'network'           => 'required',
                'amount'            => 'required',
                'payment_method'    => 'required',
            ]);
            if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());

            $validated          = $validator->validate();
            $amount             = $validated['amount'];
            $wallet_currency    = $validated['sender_currency'];

            if (!OutsideWalletAddress::where('currency_id', $validated['sender_currency'])
                ->where('network_id', $validated['network'])
                ->exists()) {
                throw ValidationException::withMessages([
                    'name'  => "Outside Wallet is not available for this coin and network.",
                ]);
            }
            

            $user_wallet        = UserWallet::auth()->whereHas("currency",function($q) use($wallet_currency) {
                $q->where("currency_id",$wallet_currency)->active();
            })->active()->first();
            
            
            $network            = Network::where('id',$validated['network'])->first();
            
            $payment_gateway_currency   = PaymentGatewayCurrency::where('id',$validated['payment_method'])->whereHas('gateway', function ($gateway) {
                $gateway->where('slug', PaymentGatewayConst::money_out_slug())->where('status', 1);
            })->first();

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
            $fixed_charge   = ($payment_gateway_currency->fixed_charge) * $min_max_rate;
            $percent_charge = ($amount / 100) * $payment_gateway_currency->percent_charge;
            $total_charge   = $fixed_charge + $percent_charge;
            $payable_amount = $amount + $total_charge;
            $will_get       = $amount * $rate;
            

            $data                       = [
                'type'                  => PaymentGatewayConst::SELL_CRYPTO,
                'identifier'            => Str::uuid(),
                'data'                  => [
                    'sender_wallet'     => [
                        'type'          => $request->wallet_type,
                        'wallet_id'     => $user_wallet->id,
                        'currency_id'   => $user_wallet->currency->id,
                        'name'          => $user_wallet->currency->name,
                        'code'          => $user_wallet->currency->code,
                        'rate'          => floatval($user_wallet->currency->rate),
                        'balance'       => floatval($user_wallet->balance),
                    ],
                    'network'           => [
                        'id'            => $network->id,
                        'name'          => $network->name,
                        'arrival_time'  => $network->arrival_time,
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
                    'total_payable'     => $payable_amount,
                    'will_get'          => floatval($will_get),
                ]
            ];
        
            try{
                $temporary_data         = TemporaryData::create($data);
            }catch(Exception $e){
                return back()->with(['error' => ['Something went wrong! Please try again.']]);
            }
            return redirect()->route('user.sell.crypto.sell.payment',$temporary_data->identifier);
        }
    }
    /**
     * Method for sell payment page
     * @param $identifier
     * @return view
     */
    public function sellPayment($identifier){
        $page_title                 = "- Sell Crypto Payment";
        $data                       = TemporaryData::where('identifier',$identifier)->first();
        if(!$data) return back()->with(['error' => ['Data not found!']]);
        $outside_wallet_address     = OutsideWalletAddress::where('currency_id',$data->data->sender_wallet->currency_id)
                                        ->where('network_id',$data->data->network->id)->first();
        $qr_code                    = generateQr($outside_wallet_address->public_address);

        return view('user.sections.sell-crypto.sell-payment',compact(
            'page_title',
            'data',
            'outside_wallet_address',
            'qr_code'
        ));
    }
    /**
     * Method for sell payment store
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function sellPaymentStore(Request $request,$identifier){
        $temp_data          = TemporaryData::where('identifier',$identifier)->first();
        $validator          = Validator::make($request->all(),[
            'slug'          => 'required|string'
        ]);
        if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());
        $outside_wallet     = OutsideWalletAddress::where('slug',$request->slug)->first();
        $validated          = $validator->validate();
        $data               = [
            'type'                  => $temp_data->type,
            'identifier'            => $temp_data->identifier,
            'data'                  => [
                'sender_wallet'     => [
                    'type'          => $temp_data->data->sender_wallet->type,
                    'wallet_id'     => $temp_data->data->sender_wallet->wallet_id,
                    'currency_id'   => $temp_data->data->sender_wallet->currency_id,
                    'name'          => $temp_data->data->sender_wallet->name,
                    'code'          => $temp_data->data->sender_wallet->code,
                    'rate'          => $temp_data->data->sender_wallet->rate,
                    'balance'       => $temp_data->data->sender_wallet->balance,
                ],
                'network'           => [
                    'id'            => $temp_data->data->network->id,
                    'name'          => $temp_data->data->network->name,
                    'arrival_time'  => $temp_data->data->network->arrival_time,
                ],
                'payment_method'    => [
                    'id'            => $temp_data->data->payment_method->id,
                    'name'          => $temp_data->data->payment_method->name,
                    'code'          => $temp_data->data->payment_method->code,
                    'alias'         => $temp_data->data->payment_method->alias,
                    'rate'          => $temp_data->data->payment_method->rate,
                ],
                'outside_address'   => [
                    'id'            => $outside_wallet->id,
                    'public_address'=> $outside_wallet->public_address,
                    'slug'          => $outside_wallet->slug,
                ],
                'amount'            => $temp_data->data->amount,
                'exchange_rate'     => $temp_data->data->exchange_rate,
                'min_max_rate'      => $temp_data->data->min_max_rate,
                'fixed_charge'      => $temp_data->data->fixed_charge,
                'percent_charge'    => $temp_data->data->percent_charge,
                'total_charge'      => $temp_data->data->total_charge,
                'total_payable'     => $temp_data->data->total_payable,
                'will_get'          => $temp_data->data->will_get,
            ]
        ];
        try{
            $temp_data->update($data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('user.sell.crypto.payment.info',$temp_data->identifier);

    }
    /**
     * Method for view payment info page
     * @param $identifier
     * @return view
     */
    public function paymentInfo($identifier){
        $page_title = "- Payment Info";
        $data       = TemporaryData::where('identifier',$identifier)->first();
       
        if(!$data || $data->data == null || !isset($data->data->payment_method->id)) return redirect()->route('user.sell.crypto.index')->with(['error' => ['Invalid request']]);
        $gateway_currency = PaymentGatewayCurrency::find($data->data->payment_method->id);
        if(!$gateway_currency || !$gateway_currency->gateway->isManual()) return redirect()->route('user.sell.crypto.index')->with(['error' => ['Selected gateway is invalid']]);
        $gateway = $gateway_currency->gateway;
        if(!$gateway->input_fields || !is_array($gateway->input_fields)) return redirect()->route('user.sell.crypto.index')->with(['error' => ['This payment gateway is under constructions. Please try with another payment gateway']]);
        $amount = $data->data->amount;
        if($data->data->sender_wallet->type == global_const()::OUTSIDE_WALLET){
            $outside_wallet     = OutsideWalletAddress::where('public_address',$data->data->outside_address->public_address)->first();
        }else{
            $outside_wallet     = [];
        }
        
        return view('user.sections.sell-crypto.payment-info',compact(
            'page_title',
            'data',
            'gateway',
            'amount',
            'outside_wallet'
        ));
    }
    /**
     * Method for payment info store
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function paymentInfoStore(Request $request,$identifier){
        $request->merge(['identifier' => $identifier]);
        $tempDataValidate = Validator::make($request->all(),[
            'identifier'        => "required|string|exists:temporary_datas",
        ])->validate();

        $temp_data = TemporaryData::search($tempDataValidate['identifier'])->first();
        
        if(!$temp_data || $temp_data->data == null || !isset($temp_data->data->payment_method->id)) return redirect()->route('user.sell.crypto.index')->with(['error' => ['Invalid request']]);
        $gateway_currency = PaymentGatewayCurrency::find($temp_data->data->payment_method->id);
        
        if(!$gateway_currency || !$gateway_currency->gateway->isManual()) return redirect()->route('user.sell.crypto.index')->with(['error' => ['Selected gateway is invalid']]);
        if($temp_data->data->sender_wallet->type == global_const()::OUTSIDE_WALLET){
            $outside_address     = OutsideWalletAddress::find($temp_data->data->outside_address->id);
            if(!$outside_address) return redirect()->route('user.sell.crypto.index')->with(['error' => ['Selected Outside Address is invalid']]);
        }else{
            $outside_address    = [];
        }
        
        $gateway = $gateway_currency->gateway;
        $amount = $temp_data->data->amount ?? null;
        if(!$amount) return redirect()->route('user.sell.crypto.index')->with(['error' => ['Transaction Failed. Failed to save information. Please try again']]);
        if($temp_data->data->sender_wallet->type  == global_const()::OUTSIDE_WALLET){
            $dy_validation_rules                        = $this->generateValidationRules($gateway->input_fields);
            $dy_validation_rules_for_outside_address    = $this->generateValidationRules($outside_address->input_fields);
    
            $merged_validation_rules                    = array_merge($dy_validation_rules, $dy_validation_rules_for_outside_address);
            $validated                                  = Validator::make($request->all(),$merged_validation_rules)->validate();
            $get_values                                 = $this->placeValueWithFields($gateway->input_fields,$validated);
            $get_values_for_outside_address             = $this->placeValueWithFields($outside_address->input_fields,$validated);
        }else{
            $dy_validation_rules                        = $this->generateValidationRules($gateway->input_fields);
            $validated                                  = Validator::make($request->all(),$dy_validation_rules)->validate();
            $get_values                                 = $this->placeValueWithFields($gateway->input_fields,$validated);
            $get_values_for_outside_address             = [];
        }

        
        $data                              = [
            'type'                  => $temp_data->type,
            'identifier'            => $temp_data->identifier,
            'data'                  => [
                'sender_wallet'     => [
                    'type'          => $temp_data->data->sender_wallet->type,
                    'wallet_id'     => $temp_data->data->sender_wallet->wallet_id,
                    'currency_id'   => $temp_data->data->sender_wallet->currency_id,
                    'name'          => $temp_data->data->sender_wallet->name,
                    'code'          => $temp_data->data->sender_wallet->code,
                    'rate'          => $temp_data->data->sender_wallet->rate,
                    'balance'       => $temp_data->data->sender_wallet->balance,
                ],
                'network'           => [
                    'id'            => $temp_data->data->network->id,
                    'name'          => $temp_data->data->network->name,
                    'arrival_time'  => $temp_data->data->network->arrival_time,
                ],
                'payment_method'    => [
                    'id'            => $temp_data->data->payment_method->id,
                    'name'          => $temp_data->data->payment_method->name,
                    'code'          => $temp_data->data->payment_method->code,
                    'alias'         => $temp_data->data->payment_method->alias,
                    'rate'          => $temp_data->data->payment_method->rate,
                ],
                'outside_address'   => [
                    'id'            => $temp_data->data->outside_address->id ?? '',
                    'public_address'=> $temp_data->data->outside_address->public_address ?? '',
                    'slug'          => $temp_data->data->outside_address->slug ?? '',
                ],
                'details'           => json_encode(['gateway_input_values' => $get_values,'outside_address_input_values' => $get_values_for_outside_address]),
                'amount'            => $temp_data->data->amount,
                'exchange_rate'     => $temp_data->data->exchange_rate,
                'min_max_rate'      => $temp_data->data->min_max_rate,
                'fixed_charge'      => $temp_data->data->fixed_charge,
                'percent_charge'    => $temp_data->data->percent_charge,
                'total_charge'      => $temp_data->data->total_charge,
                'total_payable'     => $temp_data->data->total_payable,
                'will_get'          => $temp_data->data->will_get,

            ]
        ];
        
        try{
            $temp_data->update($data);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }              
        return redirect()->route('user.sell.crypto.preview',$temp_data->identifier);
        
    }
    /**
     * Method for sell crypto preview page
     * @param $identifier
     */
    public function preview($identifier){
        $page_title     = "- Sell Crypto Preview";
        $data           = TemporaryData::where('identifier',$identifier)->first();
        if(!$data) return back()->with(['error' => ['Data not found!']]);
        $payment_gateway_currency  = PaymentGatewayCurrency::where('id',$data->data->payment_method->id)->first();
        if(!$payment_gateway_currency) return redirect()->route('user.sell.crypto.index')->with(['error' => ['Payment Gateway Currency not found right now. Please try with another payment gateway']]);
        

        return view('user.sections.sell-crypto.preview',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for sell crypto confirm
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function confirm($identifier){
        $basic_setting  = BasicSettings::first();
        $user           = auth()->user();
        $data           = TemporaryData::where('identifier',$identifier)->first();
        if(!$data) return back()->with(['error' => ['Data not found!']]);
        $send_wallet  = $data->data->sender_wallet->wallet_id;
        
        $sender_wallet  = UserWallet::auth()->where("id",$send_wallet)->first();
        if($data->data->sender_wallet->type == global_const()::INSIDE_WALLET){
            $available_balance  = $sender_wallet->balance - $data->data->total_payable;
        }else{
            $available_balance = null;
        }
        $trx_id = generateTrxString("transactions","trx_id","SC",8);
        $transaction_data = [
            'type'                          => PaymentGatewayConst::SELL_CRYPTO,
            'user_id'                       => $user->id,
            'user_wallet_id'                => $data->data->sender_wallet->wallet_id,
            'payment_gateway_currency_id'   => $data->data->payment_method->id,
            'trx_id'                        => $trx_id,
            'amount'                        => $data->data->amount,
            'percent_charge'                => $data->data->percent_charge,
            'fixed_charge'                  => $data->data->fixed_charge,
            'total_charge'                  => $data->data->total_charge,
            'total_payable'                 => $data->data->total_payable,
            'available_balance'             => $available_balance,
            'currency_code'                 => $data->data->payment_method->code,
            'remark'                        => ucwords(remove_special_char(PaymentGatewayConst::SELL_CRYPTO," ")) . " With " . $data->data->payment_method->name,
            'details'                       => ['data' => $data->data],
            'status'                        => global_const()::STATUS_PENDING,
            'created_at'                    => now(),
        ];
        try{
            $transaction    = Transaction::create($transaction_data);

            if( $basic_setting->email_notification == true){
                Notification::route("mail",$user->email)->notify(new SellCryptoEmailNotification($user,$data,$trx_id));
            }
            if($transaction->details->data->sender_wallet->type == global_const()::INSIDE_WALLET){
                $this->updateSenderWalletBalance($sender_wallet,$available_balance);
                $this->userNotification($data);
                $this->transactionDevice($transaction);
            }else{
                $this->userNotification($data);
                $this->transactionDevice($transaction);
            }
            $data->delete();
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('user.sell.crypto.index')->with(['success' => ['Sell Crypto Successful.']]);
    }
    //update sender wallet balance
    function updateSenderWalletBalance($sender_wallet,$available_balance){
        $sender_wallet->update([
            'balance'   => $available_balance,
        ]);
    }
    //user notification
    function userNotification($data){
        UserNotification::create([
            'user_id'       => auth()->user()->id,
            'message'       => [
                'title'     => "Sell Crypto",
                'wallet'    => $data->data->sender_wallet->name,
                'code'      => $data->data->sender_wallet->code,
                'amount'    => doubleval($data->data->amount),
                'status'    => global_const()::STATUS_PENDING,
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

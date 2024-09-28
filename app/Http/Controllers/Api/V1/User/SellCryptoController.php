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
use App\Models\Admin\CurrencyHasNetwork;
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
            $gateway->where('slug', PaymentGatewayConst::money_out_slug());
            $gateway->where('status', 1);
        })->get();

        $outside_wallet_address     = OutsideWalletAddress::orderBy('id')->get();

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

        return Response::success([__("Sell Crypto Data")],[
            'wallet_type'                   => $wallet_type,
            'currencies'                    => $currencies,
            'outside_wallet_address'        => $outside_wallet_address,
            'payment_gateway'               => $payment_gateway,
            'currency_image_paths'          => $image_paths,
            'payment_image_paths'           => $payment_image_paths,
        ],200);
    }
    /**
     * Method for store sell crypto information
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
            if($validator->fails()) return Response::error($validator->errors()->all(),[]);

            $validated          = $validator->validate();
            $amount             = $validated['amount'];
            $wallet_currency    = $validated['sender_currency'];
            
            $user_wallet        = UserWallet::auth()->whereHas("currency",function($q) use($wallet_currency) {
                $q->where("currency_id",$wallet_currency)->active();
            })->active()->first();
            
            if(!$user_wallet) return Response::error(['Wallet not found!'],[],404);
            
            if($amount > $user_wallet->balance){
                return Response::error(['Sorry! Insufficient Balance.'],[],404);
            }
            
            $network                    = CurrencyHasNetwork::where('currency_id',$wallet_currency)->where('network_id',$validated['network'])->first();
            if(!$network){
                return Response::error(['network Not Found!'],[],404);
            }
            
            
            $payment_gateway_currency   = PaymentGatewayCurrency::where('id',$validated['payment_method'])->whereHas('gateway', function ($gateway) {
                $gateway->where('slug', PaymentGatewayConst::money_out_slug())->where('status', 1);
            })->first();
            
            
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
            $fixed_charge   = ($payment_gateway_currency->fixed_charge) * $min_max_rate;
            $percent_charge = ($amount / 100) * $payment_gateway_currency->percent_charge;
            $total_charge   = $fixed_charge + $percent_charge;
            $payable_amount = $amount + $total_charge;
            $will_get       = $amount * $rate;
            if($payable_amount > $user_wallet->balance){
                return Response::error(['Sorry! Insufficient Balance.'],[],404);
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
                        'id'            => $network->network->id,
                        'name'          => $network->network->name,
                        'arrival_time'  => $network->network->arrival_time,
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
                    'total_payable'     => $payable_amount,
                    'will_get'          => floatval($will_get),
                ]
            ];
        
            try{
                $temporary_data         = TemporaryData::create($data);
            }catch(Exception $e){
                return Response::error(['Something went wrong! Please try again.'],[],404);
            }
            return Response::success([__("Sell Crypto Store Successfully using Inside Wallet.")],[
                'data'                      => $temporary_data,
                'payment_gateway_fields'    => $payment_gateway_currency->gateway->input_fields
            ],200);
        }else{
            $validator           = Validator::make($request->all(),[
                'wallet_type'       => 'required',
                'sender_currency'   => 'required',
                'network'           => 'required',
                'amount'            => 'required',
                'payment_method'    => 'required',
            ]);
            if($validator->fails()) return Response::error($validator->errors()->all(),[]);

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
            
            $outside_address    = OutsideWalletAddress::where('currency_id', $validated['sender_currency'])
            ->where('network_id', $validated['network'])->where('status',true)->first();
            

            $user_wallet        = UserWallet::auth()->whereHas("currency",function($q) use($wallet_currency) {
                $q->where("currency_id",$wallet_currency)->active();
            })->active()->first();
            
            
            $network                    = CurrencyHasNetwork::where('currency_id',$wallet_currency)->where('network_id',$validated['network'])->first();
            if(!$network){
                return Response::error(['network Not Found!'],[],404);
            }
            
            $payment_gateway_currency   = PaymentGatewayCurrency::where('id',$validated['payment_method'])->whereHas('gateway', function ($gateway) {
                $gateway->where('slug', PaymentGatewayConst::money_out_slug())->where('status', 1);
            })->first();

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
                        'id'            => $network->network->id,
                        'name'          => $network->network->name,
                        'arrival_time'  => $network->network->arrival_time,
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
                    'total_payable'     => $payable_amount,
                    'will_get'          => floatval($will_get),
                ]
            ];
        
            try{
                $temporary_data         = TemporaryData::create($data);
            }catch(Exception $e){
                return Response::error(['Something went wrong! Please try again.'],[],404);
            }
            return Response::success([__("Sell Crypto Store Successfully using Outside Wallet.")],[
                'data'                      => $temporary_data,
                'payment_gateway_fields'    => $payment_gateway_currency->gateway->input_fields,
                'slug'                      => $outside_address->slug,
                'payment_proof_fields'      => $outside_address->input_fields
            ],200);
        }
    }
    /**
     * Method for sell crypto sell payment store
     * @param $identifier, $slug
     * @param \Illuminate\Http\Request $request
     */
    public function sellPaymentStore(Request $request){
        $validator          = Validator::make($request->all(),[
            'slug'          => 'required|string',
            'identifier'    => 'required|string'
        ]);
        if($validator->fails()) return Response::error($validator->errors()->all(),[]);
        $temp_data          = TemporaryData::where('identifier',$request->identifier)->first();
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
            return Response::error(['Something went wrong! Please try again.'],[],404);
        }
        return Response::success([__("Sell Crypto Store Successfully using Outside Wallet.")],[
            'data'                      => $temp_data,
        ],200);
    }
    /**
     * Method for sell crypto payment info store
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function paymentInfoStore(Request $request){
        $request->merge(['identifier' => $request->identifier]);
        $tempDataValidate = Validator::make($request->all(),[
            'identifier'        => "required|string|exists:temporary_datas",
        ])->validate();

        $temp_data = TemporaryData::search($tempDataValidate['identifier'])->first();
        if(!$temp_data || $temp_data->data == null || !isset($temp_data->data->payment_method->id)) return Response::error(['Invalid Request'],[],404);
        $gateway_currency = PaymentGatewayCurrency::find($temp_data->data->payment_method->id);
        
        if(!$gateway_currency || !$gateway_currency->gateway->isManual()) return Response::error(['Selected gateway is invalid.'],[],404);
        if($temp_data->data->sender_wallet->type == global_const()::OUTSIDE_WALLET){
            $outside_address     = OutsideWalletAddress::find($temp_data->data->outside_address->id);
            if(!$outside_address) return Response::error(['Selected Outside Address is invalid.'],[],404);
        }else{
            $outside_address    = [];
        }
        
        $gateway = $gateway_currency->gateway;
        $amount = $temp_data->data->amount ?? null;
        if(!$amount) return Response::error(['Transaction Failed. Failed to save information. Please try again'],[],404);
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
            return Response::error(['Something went wrong! Please try again.'],[],404);
        }  
        $image_paths = [
            'base_url'         => url("/"),
            'path_location'    => files_asset_path_basename("kyc-files"),
            'default_image'    => files_asset_path_basename("default"),

        ];         
        return Response::success([__("Sell Crypto Store Successfully using Outside Wallet.")],[
            'data'                      => $temp_data,
            'details'                   => json_decode($temp_data->data->details),
            'image_paths'               => $image_paths
        ],200);
    }
    /**
     * Method for sell crypto confirm
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function confirm(Request $request){
        $validator          = Validator::make($request->all(),[
            'identifier'    => 'required|string'
        ]);
        if($validator->fails()) return Response::error($validator->errors()->all(),[]);
        $basic_setting  = BasicSettings::first();
        $user           = auth()->user();
        $data           = TemporaryData::where('identifier',$request->identifier)->first();
        if(!$data) return Response::error(['Data not found!'],[],404);
        $send_wallet  = $data->data->sender_wallet->wallet_id;
        
        $sender_wallet  = UserWallet::auth()->where("id",$send_wallet)->first();
        if($data->data->sender_wallet->type == global_const()::INSIDE_WALLET){
            $available_balance  = $sender_wallet->balance - $data->data->total_payable;
            $status             = global_const()::STATUS_CONFIRM_PAYMENT;
        }else{
            $available_balance = null;
            $status            = global_const()::STATUS_PENDING;
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
            'status'                        => $status,
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
            return Response::error(['Something went wrong! Please try again.'],[],404);
        }
        return Response::success([__("Sell Crypto Successfull")],[],200);
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

<?php

namespace App\Models;

use App\Models\Admin\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\PaymentGatewayCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['confirm', 'dynamic_inputs', 'confirm_url'];

    protected $casts = [
        'id'                          => 'integer',
        'type'                        => "string",
        'user_id'                     => 'integer',
        'user_wallet_id'              => 'integer',
        'payment_gateway_id'          => 'integer',
        'trx_id'                      => 'string',
        'amount'                      => 'decimal:16',
        'percent_charge'              => 'decimal:16',
        'fixed_charge'                => 'decimal:16',
        'total_charge'                => 'decimal:16',
        'total_payable'               => 'decimal:16',
        'available_balance'           => 'decimal:16',
        'currency_code'               => 'string',
        'remark'                      => 'string',
        'details'                     => 'object',
        'reject_reason'               => 'string',
        'callback_ref'                => 'string',
        'status'                      => 'integer',
        'created_at'                  => 'date:Y-m-d',
        'updated_at'                  => 'date:Y-m-d',
    ];

    public function getConfirmAttribute()
    {
        if($this->currency == null) return false;
        if($this->currency->gateway->isTatum($this->currency->gateway) && $this->status == global_const()::STATUS_PENDING) return true;
    }

    public function getDynamicInputsAttribute()
    {
        if($this->confirm == false) return [];
        $input_fields = $this->details->payment_info->requirements;
        return $input_fields;
    }

    public function getConfirmUrlAttribute()
    {
        if($this->confirm == false) return false;
        return setRoute('api.user.buy.crypto.payment.crypto.confirm', $this->trx_id);
    }
    //relation user table
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //relation with user wallet table
    public function user_wallets()
    {
        return $this->belongsTo(UserWallet::class, 'user_wallet_id');
    }

    //relation with payment gateway table
    public function payment_gateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    // relation with payment gateway currency table
    public function currency()
    {
        return $this->belongsTo(PaymentGatewayCurrency::class,'payment_gateway_currency_id');
    }

    //for search transaction log
    public function scopeSearch($query,$data) {
        return $query->where("trx_id",'LIKE','%'.$data.'%')
                     ->orderBy('id','desc');
    }
    //find the auth user
    public function scopeAuth($query){
        return $query->where('user_id',auth()->user()->id);
    }
    
}

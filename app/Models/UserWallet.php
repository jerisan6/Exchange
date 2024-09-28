<?php

namespace App\Models;

use App\Models\Admin\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserWallet extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['balance', 'status','user_id','currency_id','created_at','updated_at'];

    protected $guarded  = ['id'];

    protected $casts            = [
        'id'                    => 'integer',
        'user_id'               => 'integer',
        'currency_id'           => 'integer',
        'public_address'        => 'string',
        'balance'               => 'decimal:16',
        'status'                => 'integer',
        'created_at'            => 'date:Y-m-d',
        'updated_at'            => 'date:Y-m-d',
    ];

    public function scopeAuth($query) {
        return $query->where('user_id',auth()->user()->id);
    }

    public function scopeActive($query) {
        return $query->where("status",true);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }
    
}

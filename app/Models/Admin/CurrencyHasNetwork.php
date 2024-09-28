<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyHasNetwork extends Model
{
    use HasFactory;

    protected $guarded  = ['id'];

    protected $casts    = [
        'id'            => 'integer',
        'currency_id'   => 'integer',
        'network_id'    => 'integer',
        'fees'          => 'decimal:16',
        'created_at'    => 'date:Y-m-d',
        'updated_at'    => 'date:Y-m-d',
    ];

    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }
    public function network(){
        return $this->belongsTo(Network::class,'network_id');
    }
    
}

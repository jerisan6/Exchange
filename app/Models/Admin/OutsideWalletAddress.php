<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutsideWalletAddress extends Model
{
    use HasFactory;

    protected $guarded      = ['id'];

    protected $casts        = [
        'id'                => 'integer',
        'currency_id'       => 'integer',
        'network_id'        => 'integer',
        'slug'              => 'string',
        'public_address'    => 'string',
        'desc'              => 'string',
        'input_fields'      => 'object',
        'status'            => 'integer',
        'created_at'        => 'date:Y-m-d',
        'updated_at'        => 'date:Y-m-d',
    ];

    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }
    public function network(){
        return $this->belongsTo(Network::class,'network_id');
    }
    
}

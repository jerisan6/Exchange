<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    use HasFactory;

    protected $guarded  = ['id'];

    protected $casts    = [
        'id'            => 'integer',
        'coin_id'       => 'integer',
        'slug'          => 'string',
        'name'          => 'string',
        'arrival_time'  => 'double',
        'description'   => 'string',
        'last_edit_by'  => 'integer',
        'status'        => 'integer',
        'created_at'    => 'date:Y-m-d',
        'updated_at'    => 'date:Y-m-d',
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }


}

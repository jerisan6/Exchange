<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'name'         => 'string',
        'code'         => 'string',
        'dir'          => 'string',
        'status'       => 'boolean',
        'last_edit_by' => 'integer',
        'created_at'   => 'date:Y-m-d',
        'updated_at'   => 'date:Y-m-d',
    ];

    public function scopeDefault($query) {
        return $query->where("status",true);
    }

    public function getEditDataAttribute() {
        $data = [];
        
        $data = [
            'id'        => $this->id,
            'name'      => $this->name,
            'code'      => $this->code,
            'dir'       => $this->dir,
        ];

        return json_encode($data);
    }
}

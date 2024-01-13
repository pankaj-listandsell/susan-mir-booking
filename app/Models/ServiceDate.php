<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'start_date',
        'end_date',
        'max_people', 
        'active', 
    ];

    public function fillByAttr($attributes , $input)
    {
        if(!empty($attributes)){
            foreach ( $attributes as $item ){
                $this->$item = isset($input[$item]) ? ($input[$item]) : null;
            }
        }
    }
}

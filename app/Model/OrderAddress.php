<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model {

    protected $table = 'order_address';
    protected $fillable = [
        'fk_order_id',
        'name',
        'mobile',
        'landmark',
        'address_line1',
        'address_line2',
        'latitude',
        'longitude',
        'address_type',
    ];

}

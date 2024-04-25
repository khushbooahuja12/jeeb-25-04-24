<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderDeliverySlot extends Model
{

    protected $table = 'order_delivery_slots';
    protected $fillable = [
        'fk_order_id',
        'delivery_date',
        'delivery_slot',
        'delivery_time',
        'later_time',
        'delivery_preference',
        'expected_eta',
        'actual_eta	'
    ];
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeliverySlotSetting extends Model {

    protected $table = 'delivery_slot_settings';
    protected $fillable = [
        'id',
        'start_date',
        'from',
        'to',
        'block_time',
        'order_limit'
    ];

}

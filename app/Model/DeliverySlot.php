<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeliverySlot extends Model {

    protected $table = 'delivery_slots';
    protected $fillable = [
        'id',
        'date',
        'from',
        'to',
        'order_limit'
    ];

}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model {

    protected $table = 'order_status';
    protected $fillable = [
        'fk_order_id',
        'status'
    ];

}

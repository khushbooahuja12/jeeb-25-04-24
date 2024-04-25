<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderDriver extends Model {

    protected $table = 'order_drivers';
    protected $fillable = [
        'fk_order_id',
        'fk_driver_id',
        'status'
    ];

    public function getDriver() {
        return $this->belongsTo('App\Model\Driver', 'fk_driver_id');
    }

}

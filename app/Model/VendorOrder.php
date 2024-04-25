<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VendorOrder extends Model {

    protected $table = 'vendor_orders';
    protected $fillable = [
        'fk_order_id',
        'fk_vendor_id',
        'order_link'
    ];

    public function getOrder() {
        return $this->belongsTo('App\Model\Order', 'fk_order_id');
    }

}

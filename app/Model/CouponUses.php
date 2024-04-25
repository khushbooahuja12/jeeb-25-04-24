<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CouponUses extends Model {

    protected $table = 'coupon_uses';
    protected $fillable = [
        'fk_user_id',
        'fk_coupon_id',
        'fk_order_id',
        'uses_count',
    ];

}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserCart extends Model {

    protected $table = 'user_cart';
    protected $fillable = [
        'fk_user_id',
        'fk_product_id',
        'fk_product_store_id',
        'quantity',
        'total_price',
        'total_discount',
        'weight',
        'unit',
        'sub_products'
    ];

    public function getProduct() {
        return $this->belongsTo('App\Model\Product', 'fk_product_id', 'id');
    }

    public function getBaseProduct() {
        return $this->belongsTo('App\Model\BaseProductStore', 'fk_product_store_id', 'id')->with('baseProduct');
    }

}

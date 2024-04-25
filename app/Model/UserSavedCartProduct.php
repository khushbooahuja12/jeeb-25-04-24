<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserSavedCartProduct extends Model {

    protected $table = 'user_saved_cart_products';
    protected $fillable = [
        'fk_product_id',
        'fk_product_store_id',
        'fk_saved_cart_id',
        'quantity',
        'total_price',
        'total_discount',
        'weight',
        'unit',
        'sub_products',
        'product_price'
    ];

    public function getProduct() {
        return $this->belongsTo('App\Model\Product', 'fk_product_id', 'id');
    }

}

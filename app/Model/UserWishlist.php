<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserWishlist extends Model {

    protected $table = 'user_wishlist';
    protected $fillable = [
        'fk_user_id',
        'fk_product_id',
        'fk_product_store_id',
        'fk_vendor_id'
    ];

}

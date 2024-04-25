<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserSavedCart extends Model {

    protected $table = 'user_saved_cart';
    protected $fillable = [
        'name',
        'fk_user_id',
        'fk_address_id',
        'total_price'
    ];

}

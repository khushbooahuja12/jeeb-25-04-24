<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserBuyItForMeRequest extends Model {

    protected $table = 'user_buy_it_for_me_request';
    protected $fillable = [
        'from_user_id',
        'to_user_mobile',
        'status',
        'is_read'
    ];

}

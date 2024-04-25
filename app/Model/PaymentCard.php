<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PaymentCard extends Model {

    protected $table = 'payment_cards';
    protected $fillable = [
        'fk_user_id',
        'card_holder_name',
        'card_number',
        'expired_date'
    ];

}

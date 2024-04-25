<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserWalletPayment extends Model {

    protected $table = 'user_wallet_payments';
    protected $fillable = [
        'fk_user_id',
        'amount',
        'status',
        'wallet_type',
        'paymentResponse',
        'transaction_type'
    ];

    public function getUser() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

}

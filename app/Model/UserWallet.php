<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model {

    protected $table = 'user_wallet';
    protected $fillable = [
        'fk_user_id',
        'current_points',
        'total_points',
        'total_money'
    ];

    public function getUser() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

    public function updateOrCreate($user_id, $amount, $order_id = null, $wallet_type=3) {
        $exist = UserWallet::where(['fk_user_id' => $user_id])->first();
        if ($exist) {
            UserWallet::where(['fk_user_id' => $user_id])->update([
                'total_points' => $exist->total_points + $amount
            ]);
        } else {
            UserWallet::create([
                'fk_user_id' => $user_id,
                'total_points' => $amount
            ]);
        }
        \App\Model\UserWalletPayment::create([
            'fk_user_id' => $user_id,
            'amount' => $amount,
            'wallet_type' => $wallet_type,
            'status' => 'success',
            'paymentResponse' => $order_id,
            'transaction_type' => 'credit'
        ]);
    }

}

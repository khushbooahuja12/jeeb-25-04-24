<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserReferralCode extends Model {

    protected $table = 'user_referral_codes';
    protected $fillable = [
        'fk_user_id',
        'referral_code'
    ];

    public function getUser() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

}

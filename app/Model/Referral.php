<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model {

    protected $table = 'referrals';
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'referral_code',
        'is_used'
    ];

    public function getSender() {
        return $this->belongsTo('App\Model\User', 'sender_id');
    }

    public function getReceiver() {
        return $this->belongsTo('App\Model\User', 'receiver_id');
    }

}

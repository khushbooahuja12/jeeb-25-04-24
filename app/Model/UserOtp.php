<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{

    protected $table = 'user_otp';
    protected $fillable = [
        'user_type',
        'user_id',
        'otp_number',
        'expiry_date',
        'type',
        'is_used'
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
    ];

    public function getUser()
    {
        return $this->belongsTo('App\Model\User', 'user_id');
    }
}

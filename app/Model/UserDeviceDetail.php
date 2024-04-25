<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserDeviceDetail extends Model {

    protected $table = 'user_device_detail';
    protected $fillable = [
        'fk_user_id',
        'device_type',
        'device_token'
    ];

}

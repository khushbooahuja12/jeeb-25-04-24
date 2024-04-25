<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GuestDeviceDetail extends Model {

    protected $table = 'guest_device_detail';
    protected $fillable = [
        'device_type',
        'device_token'
    ];

}

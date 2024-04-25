<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverDeviceDetail extends Model {

    protected $table = 'driver_device_detail';
    protected $fillable = [
        'fk_driver_id',
        'device_type',
        'device_token'
    ];

}

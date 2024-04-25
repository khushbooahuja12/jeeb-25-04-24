<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StorekeeperDeviceDetail extends Model
{

    protected $table = 'storekeeper_device_detail';
    protected $fillable = [
        'fk_storekeeper_id',
        'device_type',
        'device_token'
    ];
}

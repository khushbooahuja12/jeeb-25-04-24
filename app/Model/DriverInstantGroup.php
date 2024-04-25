<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverInstantGroup extends Model
{
    protected $table = 'driver_instant_groups';
    protected $fillable = [
        'fk_driver_id',
        'fk_group_id',
        'fk_store_id'
    ];
}

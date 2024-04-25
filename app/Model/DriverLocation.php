<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model {

    protected $table = 'driver_locations';
    protected $fillable = [
        'fk_driver_id',
        'latitude',
        'longitude',
    ];

}

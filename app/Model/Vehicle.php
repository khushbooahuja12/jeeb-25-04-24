<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model {

    protected $table = 'vehicles';
    protected $fillable = [
        'vehicle_number',
        'vehicle_type',
        'vehicle_capacity',
        'status'
    ];

    public function getDriver() {
        return $this->hasOne('App\Model\Driver', 'fk_vehicle_id', 'id');
    }

}

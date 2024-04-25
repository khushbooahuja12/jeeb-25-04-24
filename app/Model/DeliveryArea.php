<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model {

    protected $table = 'delivery_area';
    protected $fillable = [
        'location',
        'latitude',
        'longitude',
        'radius',
        'blocked_timeslots'
    ];

}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{

    protected $table = 'user_address';
    protected $fillable = [
        'fk_user_id',
        'name',
        'mobile',
        'landmark',
        'address_line1',
        'address_line2',
        'latitude',
        'longitude',
        'address_type',
        'image',
        'deleted',
        'is_default',
        'blocked_timeslots'
    ];

    public function getAddressImage()
    {
        return $this->belongsTo('App\Model\File', 'image');
    }

    function getExpectedETA($lat1, $long1, $lat2, $long2)
    {
        $apiKey = 'AIzaSyDGBWqzz573j5ZKwJlG9eHvhC05vCCI_po';
        $endpointURL = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $lat1 . ',' . $long1 . '&destination=' . $lat2 . ',' . $long2 . '&sensor=false&key=' . $apiKey;
        //Call endpoint
        $res = callGetAPI($endpointURL);
        // pp($res);
        return $res->routes[0]->legs[0]->duration->value ?? 1800;
    }
}

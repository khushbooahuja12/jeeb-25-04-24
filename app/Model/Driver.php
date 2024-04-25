<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Driver extends Model
{

    use Sortable;

    protected $table = 'drivers';
    protected $fillable = [
        'fk_vehicle_id',
        'name',
        'driver_image',
        'country_code',
        'mobile',
        'email',
        'driving_licence_no',
        'driving_licence',
        'national_id',
        'status',
        'is_available',
        'fk_store_id',
        'deleted',
        'role'
    ];

    public $sortable = ['id', 'name', 'mobile', 'email'];

    public function getDriverImage()
    {
        return $this->belongsTo('App\Model\File', 'driver_image');
    }

    public function getDrivingLicence()
    {
        return $this->belongsTo('App\Model\File', 'driving_licence');
    }

    public function getNationalId()
    {
        return $this->belongsTo('App\Model\File', 'national_id');
    }

    public function getVehicle()
    {
        return $this->belongsTo('App\Model\Vehicle', 'fk_vehicle_id');
    }

    public function getStore()
    {
        return $this->belongsTo('App\Model\Store', 'fk_store_id');
    }

    public function getLocation()
    {
        return $this->hasOne('App\Model\DriverLocation', 'fk_driver_id', 'id')->latest()->first();
    }
}

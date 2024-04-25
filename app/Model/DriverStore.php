<?php

namespace App\Model;;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class DriverStore extends Model
{
    use Sortable;

    protected $table = 'driver_stores';
    protected $fillable = [
        'fk_driver_id',
        'fk_store_id',
    ];

    public function getDriver() {
        return $this->belongsTo('App\Model\Driver', 'fk_driver_id');
    }
}

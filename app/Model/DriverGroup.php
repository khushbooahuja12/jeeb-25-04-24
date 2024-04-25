<?php

namespace App\Model;

use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;

class DriverGroup extends Model
{
    use Sortable;

    protected $table = 'driver_groups';
    protected $fillable = [
        'fk_driver_id',
        'group_id',
        'fk_store_id'
    ];

    public function getDriver() {
        return $this->belongsTo('App\Model\Driver', 'fk_driver_id');
    }
}

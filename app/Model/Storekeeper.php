<?php

namespace App\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Kyslik\ColumnSortable\Sortable;

class Storekeeper extends Authenticatable
{
    use Sortable;

    protected $table = 'storekeepers';

    protected $fillable = [
        'name',
        'country_code',
        'mobile',
        'email',
        'address',
        'default',
        'deleted',
        'fk_store_id',
        'image',
        'is_test_user',
        'is_available',
        'status'
    ];

    public $sortable = ['id', 'name', 'mobile', 'email', 'address'];

    public function getStorekeeperImage()
    {
        return $this->belongsTo('App\Model\File', 'image');
    }

    public function getSubCategories()
    {
        return $this->hasMany('App\Model\StorekeeperSubcategory', 'fk_storekeeper_id');
    }

    public function getStore()
    {
        return $this->belongsTo('App\Model\Store', 'fk_store_id');
    }
}

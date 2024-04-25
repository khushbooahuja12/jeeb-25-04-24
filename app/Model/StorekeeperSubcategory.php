<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StorekeeperSubcategory extends Model
{

    protected $table = 'storekeeper_sub_categories';
    protected $fillable = [
        'fk_storekeeper_id',
        'fk_sub_category_id'
    ];

    public $timestamps = false;

    public function getStoreKeeper()
    {
        return $this->belongsTo('App\Model\Storekeeper', 'fk_storekeeper_id');
    }

    public function getSubCategory()
    {
        return $this->belongsTo('App\Model\Category', 'fk_sub_category_id');
    }
}

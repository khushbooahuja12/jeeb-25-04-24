<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BrandCategoryMapping extends Model {

    protected $table = 'brand_category_mapping';
    protected $fillable = [
        'fk_brand_id',
        'fk_category_id'
    ];

    public function getCategory() {
        return $this->belongsTo('App\Model\Category', 'fk_category_id');
    }

    public function getBrand() {
        return $this->belongsTo('App\Model\Brand', 'fk_brand_id');
    }

}

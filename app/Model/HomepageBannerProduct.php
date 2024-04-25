<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class HomepageBannerProduct extends Model
{

    protected $table = 'homepage_banner_products';
    protected $fillable = [
        'fk_homepage_data_id',
        'fk_product_id'
    ];

    public function getHomepagedata()
    {
        return $this->belongsTo('App\Model\Homepagedata', 'fk_homepage_data_id');
    }

    public function getProduct()
    {
        return $this->belongsTo('App\Model\Product', 'fk_product_id');
    }
}

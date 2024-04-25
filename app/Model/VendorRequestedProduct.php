<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;


class VendorRequestedProduct extends Model
{

    protected $table = 'vendor_requested_products';
    
    protected $fillable = [
        'itemcode',
        'barcode',
        'fk_category_id',
        'fk_sub_category_id',
        'fk_brand_id',
        'product_name_en',
        'product_name_ar',
        'product_image_url',
        'base_price',
        'unit',
        '_tags',
        'country_code',
        'country_icon',
        'fk_store_id',
        'fk_product_id',
        'stock'
    ];

    public function getProductCategory()
    {
        return $this->belongsTo('App\Model\Category', 'fk_category_id');
    }

    public function getProductSubCategory()
    {
        return $this->belongsTo('App\Model\Category', 'fk_sub_category_id');
    }

    public function getProductBrand()
    {
        return $this->belongsTo('App\Model\Brand', 'fk_brand_id');
    }

}

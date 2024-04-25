<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Laravel\Scout\Searchable;

class Product extends Model
{

    use Sortable;

    use Searchable;

    public function __construct(array $attributes = array(), $value =null)
    {
        /* override your model constructor */
        parent::__construct($attributes);
        $this->table = (env('APP_ENV')=='production') ? 'products' : 'dev_products';
    }
    
    protected $fillable = [
        'parent_id',
        'product_type',
        'recipe_id',
        'recipe_variant_id',
        'recipe_ingredient_id',
        'itemcode',
        'barcode',
        'distributor_id',
        'fk_category_id',
        'category_name',
        'category_name_ar',
        'fk_sub_category_id',
        'sub_category_name',
        'sub_category_name_ar',
        'fk_brand_id',
        'brand_name',
        'brand_name_ar',
        'product_name_en',
        'product_name_ar',
        'product_image',
        'product_image_url',
        'distributor_price',
        'store1_distributor_price',
        'store2_distributor_price',
        'store3_distributor_price',
        'store4_distributor_price',
        'store5_distributor_price',
        'store6_distributor_price',
        'store7_distributor_price',
        'store8_distributor_price',
        'store9_distributor_price',
        'store10_distributor_price',
        'store1_price',
        'store2_price',
        'store3_price',
        'store4_price',
        'store5_price',
        'store6_price',
        'store7_price',
        'store8_price',
        'store9_price',
        'store10_price',
        'allow_margin',
        'margin',
        'product_price',
        'unit',
        '_tags',
        'tags_ar',
        'fk_tag_bundle_id',
        'offered',
        'frequently_bought_together',
        'is_home_screen',
        'deleted',
        'stock',
        'is_stock_update',
        'min_scale',
        'max_scale',
        'store1',
        'store2',
        'store3',
        'store4',
        'store5',
        'store6',
        'store7',
        'store8',
        'store9',
        'store10',
        'fk_company_id',
        'country_code',
        'country_icon_id',
        'country_icon'
    ];
    public $sortable = ['id', 'product_name_en', 'product_name_ar'];

    public function importToDb()
    {
        $path = resource_path('pending-files/*.csv');

        $files = glob($path);

        foreach ($files as $key => $file) {
            $data = array_map('str_getcsv', file($file));

            \App\Jobs\ProcessCsvUpload::dispatch(json_encode($data), $key);

            unlink($file);
        }
    }

    public function getCompany()
    {
        return $this->belongsTo('App\Model\Company', 'fk_company_id');
    }

    public function shouldBeSearchable()
    {
        return $this->deleted == 0 && $this->stock == 1 && $this->product_type == 'product';
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Applies Scout Extended default transformations:
        $array = $this->transform($array);

        // Add an extra attribute:
        $array['_tags'] = explode(',', $array['_tags']);

        return $array;
    }

    public function getProductImage()
    {
        return $this->belongsTo('App\Model\File', 'product_image');
    }

    public function getCountryIcon()
    {
        return $this->belongsTo('App\Model\File', 'country_icon');
    }

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

    public function getProductDetail()
    {
        return $this->hasOne('App\Model\ProductDetail', 'fk_product_id', 'id');
    }

    public function getChildProduct()
    {
        return $this->hasMany('App\Model\Product', 'parent_id', 'id');
    }

    public function getClassification()
    {
        return $this->hasMany('App\Model\ClassifiedProduct', 'fk_product_id', 'id');
    }

    public function existOnHomepage()
    {
        return $this->hasOne('App\Model\Homepagedata', 'fk_product_id', 'id');
    }

    public function existOnReplacedProduct()
    {
        return $this->hasOne('App\Model\TechnicalSupportProduct', 'fk_product_id', 'id');
    }

    public function existOnHomepageBanner()
    {
        return $this->hasOne('App\Model\HomepageBannerProduct', 'fk_product_id', 'id');
    }
}

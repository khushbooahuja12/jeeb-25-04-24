<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Laravel\Scout\Searchable;

class BaseProduct extends Model
{

    use Sortable;

    use Searchable;

    protected $table = 'base_products';
    
    public function searchableAs()
    {
        $table_index = (env('APP_ENV')=='production') ? 'base_products' : 'base_products_dev';
        return $table_index;
    }

    protected $fillable = [
        'fk_product_store_id',
        'product_type',
        'fk_store_id',
        'itemcode',
        'barcode',
        'allow_margin',
        'recipe_id',
        'recipe_variant_id',
        'recipe_ingredient_id',
        'parent_id',
        'fk_category_id',
        'fk_sub_category_id',
        'fk_brand_id',
        'product_name_en',
        'product_name_ar',
        'product_image_url',
        'other_names',
        'unit',
        'min_scale',
        'max_scale',
        'fk_main_tag_id',
        'main_tags',
        '_tags',
        'search_filters',
        'custom_tag_bundle',
        'desc_en',
        'desc_ar',
        'characteristics_en',
        'characteristics_ar',
        'base_price',
        'product_store_price',
        'product_distributor_price',
        'product_distributor_price_before_back_margin',
        'stock',
        'product_store_stock',
        'product_store_updated_at',
        'offers',
        'fk_offer_option_id',
        'fk_price_formula_id',
        'margin',
        'back_margin',
        'base_price_percentage',
        'discount_percentage',
        'country_code',
        'country_icon',
        'paythem_product_id',
        'deleted'
    ];
    public $sortable = ['id', 'product_name_en', 'product_name_ar'];

    public $algoliaSettings = [
        'attributesForFaceting' => [
            'offers'
        ],
    ];

    public function shouldBeSearchable()
    {
        return $this->deleted == 0 && $this->product_type == 'product' || $this->product_type == 'paythem';
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Applies Scout Extended default transformations:
        $array = $this->transform($array);

        // Add an extra attribute:
        $array['_tags'] = isset($array['_tags']) ? explode(',', $array['_tags']) : null;
        $array['offers'] = isset($array['offers']) ? explode(',', $array['offers']) : null;
        $array['search_filters'] = isset($array['search_filters']) ? explode(',', $array['search_filters']) : null;
        $array['category_name'] = $this->getProductCategory ? $this->getProductCategory->category_name_en : null;
        $array['category_name_ar'] = $this->getProductCategory ? $this->getProductCategory->category_name_ar : null;
        $array['sub_category_name'] = $this->getProductSubCategory ? $this->getProductSubCategory->category_name_en : null;
        $array['sub_category_name_ar'] = $this->getProductSubCategory ? $this->getProductSubCategory->category_name_ar: null;
        $array['brand_name'] = $this->getProductBrand ? $this->getProductBrand->brand_name_en : null;
        $array['brand_name_ar'] = $this->getProductBrand ? $this->getProductBrand->brand_name_ar : null;
        $array['product_saving_price'] = ($this->base_price > $this->product_store_price) ? ($this->base_price - $this->product_store_price) : 0;
        $array['product_saving_percentage'] = ($this->base_price > $this->product_store_price) ? floor((($this->base_price - $this->product_store_price) * 100) / $this->base_price) : 0;
        
        return $array;
    }

    public function stocks()
    {
        return $this->hasMany('App\Model\BaseProductStore', 'fk_product_id', 'id')->where('deleted',0);
    }

    public function getCountryIcon()
    {
        return $this->belongsTo('App\Model\File', 'country_icon');
    }

    public function getMainTag()
    {
        return $this->belongsTo('App\Model\ProductTag', 'main_tag');
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

<?php

namespace App\Model;

use App\Jobs\ProcessCsvUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Kyslik\ColumnSortable\Sortable;

class ProductDraft extends Model
{

    use Sortable;

    protected $table = 'products_draft';
    protected $fillable = [
        'parent_id',
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
        'store1_price',
        'store2_price',
        'store3_price',
        'store4_price',
        'allow_margin',
        'margin',
        'product_price',
        'unit',
        '_tags',
        'tags_ar',
        'offered',
        'is_home_screen',
        'deleted',
        'stock',
        'is_stock_update',
        'min_scale',
        'max_scale',
        'country_code',
        'country_icon_id',
        'country_icon'
    ];
    public $sortable = ['id', 'product_name_en', 'product_name_ar'];

    public function importToDb()
    {
        $path = resource_path('pending-files/*.csv');

        $files = glob($path);

        $batch = Bus::batch([])->dispatch();

        foreach ($files as $key => $file) {
            $data = array_map('str_getcsv', file($file));

            $batch->add(new ProcessCsvUpload(json_encode($data), $key));

            // \App\Jobs\ProcessCsvUpload::dispatch(json_encode($data), $key);

            unlink($file);
        }
        return $batch;
    }

    public function getProductImage()
    {
        return $this->belongsTo('App\Model\File', 'product_image');
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
}

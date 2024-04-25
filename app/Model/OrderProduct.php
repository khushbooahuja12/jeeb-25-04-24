<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model {

    protected $table = 'order_products';
    protected $fillable = [
        'fk_order_id',
        'fk_product_id',
        'fk_product_store_id',
        'fk_store_id',
        'product_type',
        'paythem_product_id',
        'single_product_price',
        'total_product_price',
        'itemcode',
        'barcode',
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
        'allow_margin',
        'margin',
        'product_price',
        'unit',
        '_tags',
        'tags_ar',
        'discount',
        'product_quantity',
        'product_weight',
        'product_unit',
        'sub_products',
        'is_out_of_stock',
        'is_missed_product',
        'is_replaced_product',
        'is_added_product',
        'deleted'
    ];

    public function getProduct($fk_product_store_id=0) {
        if ($fk_product_store_id==0) {
            return $this->belongsTo('App\Model\Product', 'fk_product_id', 'id');
        } else {
            return $this->belongsTo('App\Model\BaseProduct', 'fk_product_id', 'id');
        }
    }

    public function getStore() {
        return $this->belongsTo('App\Model\Store', 'fk_store_id', 'id');
    }

    public function getStoreProduct() {
        return $this->belongsTo('App\Model\BaseProductStore', 'fk_product_store_id', 'id');
    }

    public function getOrder() {
        return $this->belongsTo('App\Model\Order', 'fk_order_id', 'id');
    }
 
    public function getTotalOfOutOfStockProduct($order_id) {
        $orderProd = OrderProduct::selectRaw("SUM(total_product_price) as total_amount")
                ->where('is_out_of_stock', '=', 1)
                ->where('fk_order_id', '=', $order_id)
                ->where('deleted', '=', 0)
                ->first();
        if ($orderProd) {
            return $orderProd->total_amount;
        } else {
            return '0';
        }
    }

}

<?php

namespace App\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Kyslik\ColumnSortable\Sortable;

class StorekeeperProduct extends Authenticatable
{
    use Sortable;

    protected $table = 'storekeeper_products';

    protected $fillable = [
        'fk_storekeeper_id',
        'fk_order_id',
        'fk_order_product_id',
        'fk_store_id',
        'fk_product_id',
        'status',
        'fk_driver_id',
        'collection_status',
        'collected_at',
        'delivered_status',
        'delivered_at'
    ];

    public $sortable = ['id'];

    public function getStorekeeper()
    {
        return $this->belongsTo('App\Model\Storekeeper', 'fk_storekeeper_id');
    }
    public function getOrder()
    {
        return $this->belongsTo('App\Model\Order', 'fk_order_id');
    }

    public function getProduct()
    {
        return $this->belongsTo('App\Model\Product', 'fk_product_id');
    }

    public function getProductQuantity($order_id, $product_id)
    {
        return OrderProduct::where(['fk_order_id' => $order_id, 'fk_product_id' => $product_id])->first()??(object)[];
    }
}

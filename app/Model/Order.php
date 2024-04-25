<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Model\StorekeeperProduct;

class Order extends Model
{

    use Sortable;

    protected $table = 'orders';
    protected $fillable = [
        'order_type',
        'products_type',
        'parent_id',
        'fk_user_id',
        'fk_store_id',
        'orderId',
        'buy_it_for_me_request_id',
        'sub_total',
        'delivery_charge',
        'fk_coupon_id',
        'coupon_code',
        'coupon_discount',
        'amount_from_wallet',
        'amount_from_card',
        'grand_total',
        'total_amount',
        'status',
        'payment_type',
        'rps_discount',
        'change_for',
        'test_order',
        'bought_by',
        'is_whatsapp_order',
        'is_paythem_only',
        'order_json_en',
        'order_json_ar'
    ];
    public $sortable = ['id', 'orderId', 'sub_total', 'total_amount', 'status'];

    public function getOrderProducts()
    {
        return $this->hasMany('App\Model\OrderProduct', 'fk_order_id', 'id')->where(['deleted'=>0]);
    }

    public function getCheckedProducts()
    {
        return $this->hasMany('App\Model\StorekeeperProduct', 'fk_order_id', 'id')->whereIn('status',[1,2]);
    }

    public function getOrderActiveProducts()
    {
        return $this->hasMany('App\Model\OrderProduct', 'fk_order_id', 'id')->where(['deleted'=>0,'is_out_of_stock'=>0]);
    }

    public function getOrderOutOfStockProducts()
    {
        return $this->hasMany('App\Model\OrderProduct', 'fk_order_id', 'id')->where(['deleted'=>0,'is_out_of_stock'=>1]);
    }

    public function getOrderReplacedProducts()
    {
        return $this->hasMany('App\Model\OrderProduct', 'fk_order_id', 'id')->where(['deleted'=>0,'is_replaced_product'=>1]);
    }

    public function getOrderAddress()
    {
        return $this->hasOne('App\Model\OrderAddress', 'fk_order_id', 'id');
    }

    public function getUser()
    {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

    public function getOrderDriver()
    {
        return $this->hasOne('App\Model\OrderDriver', 'fk_order_id');
    }

    public function getVendorOrders()
    {
        return $this->hasMany('App\Model\VendorOrder', 'fk_order_id');
    }

    public function getOrderDeliverySlot()
    {
        return $this->hasOne('App\Model\OrderDeliverySlot', 'fk_order_id');
    }

    public function getVendorOrder()
    {
        return $this->hasOne('App\Model\VendorOrder', 'fk_order_id');
    }

    public function getOrderPayment()
    {
        return $this->hasOne('App\Model\OrderPayment', 'fk_order_id');
    }

    public function getSubOrder()
    {
        return $this->hasMany('App\Model\Order', 'parent_id', 'id');
    }

    public function getOrderSubTotal($parent_order_id)
    {
        $order = Order::selectRaw("SUM(total_amount) as sub_orders_total")
            ->where('parent_id', '=', $parent_order_id)
            ->whereNotIn('status', [0,4])
            ->first();
        if ($order) {
            return $order->sub_orders_total;
        } else {
            return 0;
        }
    }

    public function getOrderPaidByCOD($parent_order_id)
    {
        $order = Order::selectRaw("SUM(amount_from_card) as sub_orders_total")
            ->where('payment_type', '=', 'cod')
            ->where('parent_id', '=', $parent_order_id)
            ->whereNotIn('status', [0,4])
            ->first();
        if ($order) {
            return $order->sub_orders_total;
        } else {
            return 0;
        }
    }

    public function getOrderPaidByCart($parent_order_id)
    {
        $order = Order::selectRaw("SUM(amount_from_card) as sub_orders_total")
            ->where('payment_type', '=', 'online')
            ->where('parent_id', '=', $parent_order_id)
            ->whereNotIn('status', [0,4])
            ->first();
        if ($order) {
            return $order->sub_orders_total;
        } else {
            return 0;
        }
    }

    public function getOrderPaidByWallet($parent_order_id)
    {
        $order = Order::selectRaw("SUM(amount_from_wallet) as sub_orders_total")
            ->where('parent_id', '=', $parent_order_id)
            ->whereNotIn('status', [0,4])
            ->first();
        if ($order) {
            return $order->sub_orders_total;
        } else {
            return 0;
        }
    }

    public function getOrderDeliveryCharge($parent_order_id)
    {
        $order = Order::selectRaw("SUM(delivery_charge) as delivery_charge")
            ->where('parent_id', '=', $parent_order_id)
            ->whereNotIn('status', [0,4])
            ->first();
        if ($order) {
            return $order->delivery_charge;
        } else {
            return 0;
        }
    }

    public function getTechnicalSupport()
    {
        return $this->hasOne('App\Model\TechnicalSupport', 'fk_order_id', 'id');
    }

    public function getTechnicalSupportProduct()
    {
        return $this->hasMany('App\Model\TechnicalSupportProduct', 'fk_order_id', 'id');
    }

    public function getOrderStatus()
    {
        return $this->hasMany('App\Model\OrderStatus', 'fk_order_id', 'id');
    }

    public function getStore()
    {
        return $this->belongsTo('App\Model\Store', 'fk_store_id');
    }

    public function getOrderCollectedProducts()
    {
        return $this->hasMany('App\Model\StorekeeperProduct', 'fk_order_id', 'id')->where(['status'=>2]);
    }
}

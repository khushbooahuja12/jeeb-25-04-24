<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class OrderPayment extends Model
{

    use Sortable;

    protected $table = 'order_payments';
    protected $fillable = [
        'fk_order_id',
        'parent_order_id',
        'amount',
        'status',
        'paymentResponse'
    ];
    public $sortable = ['id', 'fk_order_id', 'amount'];

    public function getOrder()
    {
        return $this->belongsTo('App\Model\Order', 'fk_order_id', 'id');
    }

    public function getSubPayment() {
        return $this->hasOne('App\Model\OrderPayment', 'parent_order_id', 'fk_order_id');
    }
}

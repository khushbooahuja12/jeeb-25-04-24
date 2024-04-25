<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TechnicalSupportProduct extends Model
{

    protected $table = 'technical_support_products';
    protected $fillable = [
        'ticket_id',
        'fk_order_id',
        'fk_product_id',
        'in_stock',
        'suggested_by'
    ];

    public function getProduct()
    {
        return $this->belongsTo('App\Model\Product', 'fk_product_id');
    }
    public function getTechnicalSupport()
    {
        return $this->belongsTo('App\Model\TechnicalSupport', 'ticket_id');
    }
}

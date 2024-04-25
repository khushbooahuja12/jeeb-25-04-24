<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'date',
        'time',
        'order_id',
        'selling_price',
        'purchase_price',
    ];

    public $timestamps = false;
    
    public function getOrder()
    {
        return $this->belongsTo('App\Model\Order', 'fk_order_id');
    }

}

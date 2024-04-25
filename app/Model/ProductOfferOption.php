<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ProductOfferOption extends Model {

    use Sortable;

    protected $table = 'product_offer_options';
    protected $fillable = [
        'name',
        'base_price_percentage',
        'discount_percentage',
        'deleted',
        'status',
    ];

}

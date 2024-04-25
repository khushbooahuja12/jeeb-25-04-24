<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PriceFormula extends Model {

    protected $table = 'product_price_formula';
    protected $fillable = [
        'fk_store_id',
        'fk_subcategory_id',
        'fk_brand_id',
        'fk_offer_option_id',
        'x1',
        'x2',
        'x3',
        'x4',
        'x3x4',
        'x2x1'
    ];

}

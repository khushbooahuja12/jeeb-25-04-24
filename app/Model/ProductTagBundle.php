<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProductTagBundle extends Model
{

    protected $table = 'product_tag_bundles';
    protected $fillable = [
        'fk_bundle_id',
        'fk_product_tag_id',
    ];

    public function getProductTags()
    {
        return $this->belongsTo('App\Model\ProductTag','fk_product_tag_id');
    }

}

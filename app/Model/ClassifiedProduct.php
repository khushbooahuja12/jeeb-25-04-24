<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ClassifiedProduct extends Model {

    protected $table = 'classified_products';
    protected $fillable = [
        'fk_classification_id',
        'fk_sub_classification_id',
        'fk_product_id'
    ];

    public function getClassification() {
        return $this->belongsTo('App\Model\Classification', 'fk_classification_id');
    }
    
    public function getProduct() {
        return $this->belongsTo('App\Model\Product', 'fk_product_id');
    }

}

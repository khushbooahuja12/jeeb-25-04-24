<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NotesDetail extends Model {

    protected $table = 'notes_detail';
    protected $fillable = [
        'fk_notes_id',
        'fk_vendor_id',
        'fk_product_id',
        'product_quantity'
    ];

    public function getProduct() {
        return $this->belongsTo('App\Model\Product', 'fk_product_id');
    }

}

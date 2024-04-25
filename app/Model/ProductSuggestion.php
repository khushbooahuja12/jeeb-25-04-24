<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ProductSuggestion extends Model
{

    use Sortable;

    protected $table = 'product_suggestions';
    protected $fillable = [
        'fk_user_id',
        'product_company',
        'product_name',
        'product_description',
        'product_image',
        'status'
    ];

    public $sortable = ['id', 'product_company', 'product_name', 'product_description'];

    public function getUser() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

    public function getProductSuggestionImage() {
        return $this->belongsTo('App\Model\File', 'product_image');
    }
}

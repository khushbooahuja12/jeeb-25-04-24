<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
// use Laravel\Scout\Searchable;

class Category extends Model {

    use Sortable;

    // use Searchable;
    
    // public function __construct(array $attributes = array(), $value =null)
    // {
    //     parent::__construct($attributes);
    //     $this->table = (env('APP_ENV')=='production') ? 'categories' : 'dev_categories';
    // }
    
    // public function searchableAs()
    // {
    //     $table_index = (env('APP_ENV')=='production') ? 'categories' : 'dev_categories';
    //     return $table_index;
    // }

    protected $fillable = [
        'category_name_en',
        'category_name_ar',
        'category_image',
        'category_image2',
        'parent_id',
        'is_home_screen',
        'deleted'
    ];

    public $sortable = ['id', 'category_name_en'];

    public function getCategoryImage() {
        return $this->belongsTo('App\Model\File', 'category_image');
    }

    public function getCategoryImage2() {
        return $this->belongsTo('App\Model\File', 'category_image2');
    }

    public function getSubCategory() {
        return $this->hasMany('App\Model\Category', 'parent_id', 'id');
    }

}

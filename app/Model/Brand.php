<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Brand extends Model
{

    use Sortable;

    protected $table = 'brands';
    protected $fillable = [
        'brand_name_en',
        'brand_name_ar',
        'brand_image',
        'brand_image2',
        'is_home_screen',
        'deleted'
    ];
    public $sortable = ['id', 'brand_name_en', 'brand_name_ar'];

    public function getBrandImage()
    {
        return $this->belongsTo('App\Model\File', 'brand_image');
    }

    public function getBrandImage2()
    {
        return $this->belongsTo('App\Model\File', 'brand_image2');
    }
}

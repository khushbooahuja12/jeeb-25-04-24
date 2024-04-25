<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Banner extends Model
{
    use Sortable;

    protected $table = 'banners';
    protected $fillable = [
        'fk_category_id',
        'fk_brand_id',
        'heading_en',
        'heading_ar',
        'description_en',
        'description_ar',
        'banner_image',
        'banner_name',
        'redirect_type',
        'fk_classification_id'
    ];
    public $sortable = ['id', 'banner_name', 'heading_en'];

    public function getBannerImage()
    {
        return $this->belongsTo('App\Model\File', 'banner_image');
    }

    public function getCategory()
    {
        return $this->belongsTo('App\Model\Category', 'fk_category_id');
    }

    public function getBrand()
    {
        return $this->belongsTo('App\Model\Brand', 'fk_brand_id');
    }

    public function getClassification()
    {
        return $this->belongsTo('App\Model\Classification', 'fk_classification_id');
    }
}

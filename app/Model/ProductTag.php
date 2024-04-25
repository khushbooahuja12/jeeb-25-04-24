<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProductTag extends Model
{

    protected $table = 'product_tags';
    protected $fillable = [
        'title_en',
        'title_ar',
        'tag',
        'is_main_tag',
        'fk_tag_bundle_id',
        'tag_image',
        'banner_image'
    ];

    public function getTagBundle()
    {
        return $this->belongsTo('App\Model\TagBundle', 'fk_tag_bundle_id');
    }

}

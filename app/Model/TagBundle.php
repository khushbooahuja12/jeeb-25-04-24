<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TagBundle extends Model
{

    protected $table = 'tag_bundles';
    protected $fillable = [
        'name_en',
        'name_ar',
        'tag_image',
        'banner_image'
    ];

    public function getBundleTags()
    {
        return $this->hasMany('App\Model\ProductTagBundle','fk_bundle_id');
    }

}

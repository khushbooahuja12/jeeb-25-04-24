<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Homepagedata extends Model
{

    protected $table = 'homepage_data';
    protected $fillable = [
        'title',
        'fk_homepage_id',
        'fk_product_id',
        'image',
        'image2',
        'keyword',
        'redirection_type',
    ];

    public function getHomepage()
    {
        return $this->belongsTo('App\Model\Homepage', 'fk_homepage_id');
    }

    public function getProduct()
    {
        return $this->belongsTo('App\Model\Product', 'fk_product_id');
    }

    public function getImage()
    {
        return $this->belongsTo('App\Model\File', 'image');
    }

    public function getImage2()
    {
        return $this->belongsTo('App\Model\File', 'image2');
    }
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Homepage extends Model
{

    protected $table = 'homepage';
    protected $fillable = [
        'ui_type',
        'banner_type',
        'title',
        'index',
        'background_image',
        'background_color'
    ];

    public function getBackgroundImage()
    {
        return $this->belongsTo('App\Model\File', 'background_image');
    }

    public function getHomepageData()
    {
        return $this->hasMany('App\Model\Homepagedata', 'fk_homepage_id', 'id');
    }
}

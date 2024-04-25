<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model {

    protected $table = 'classification';
    protected $fillable = [
        'name_en',
        'name_ar',
        'banner_image',
        'stamp_image',
        'parent_id',
        'deleted'
    ];

    public function getBannerImage() {
        return $this->belongsTo('App\Model\File', 'banner_image');
    }

    public function getStampImage() {
        return $this->belongsTo('App\Model\File', 'stamp_image');
    }

    public function getSubClassification() {
        return $this->hasMany('App\Model\Classification', 'parent_id', 'id');
    }

    public function getParent() {
        return $this->belongsTo('App\Model\Classification', 'parent_id');
    }

}

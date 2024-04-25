<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model {

    protected $table = 'offers';
    protected $fillable = [
        'heading_en',
        'heading_ar',
        'description_en',
        'description_ar',
        'offer_image'
    ];

    public function getOfferImage() {
        return $this->belongsTo('App\Model\File', 'offer_image');
    }

}

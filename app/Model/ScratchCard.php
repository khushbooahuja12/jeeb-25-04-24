<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ScratchCard extends Model {

    use Sortable;

    protected $table = 'scratch_cards';
    protected $fillable = [
        'apply_on',
        'apply_on_min_amount',
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'image',
        'image_ar',
        'scratch_card_type',
        'type',
        'min_amount',
        'discount',
        'expiry_in',
        'uses_limit',
        'status',
        'deleted',
        'created_at',
        'updated_at'
    ];
    public $sortable = ['id', 'title_en'];

    public function getCategory() {
        return $this->belongsTo('App\Model\Category', 'fk_category_id');
    }

    public function getBrand() {
        return $this->belongsTo('App\Model\Brand', 'fk_brand_id');
    }

    public function getScratchCardImage() {
        return $this->belongsTo('App\Model\File', 'image');
    }

    public function getScratchCardImageAr() {
        return $this->belongsTo('App\Model\File', 'image_ar');
    }

    public function scratchCardUsers() {
        return $this->hasMany('App\Model\ScratchCardUser', 'fk_scratch_card_id');
    }

    public function scratchCardUsersActive() {
        return $this->scratchCardUsers()->where('deleted','=',0)->where('status','!=',4);
    }

    public function scratchCardUsersRedeemed() {
        return $this->scratchCardUsers()->where('deleted','=',0)->where('status','=',3);
    }

    public function redeemed_amount() {
        return $this->scratchCardUsersRedeemed()->sum('redeem_amount');
    }

    public function number_of_coupons() {
        return $this->scratchCardUsersActive()->count();
    }

    public function number_of_coupons_used() {
        return $this->scratchCardUsersRedeemed()->count();
    }

}

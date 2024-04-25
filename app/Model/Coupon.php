<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Coupon extends Model {

    use Sortable;

    protected $table = 'coupons';
    protected $fillable = [
        'fk_user_id',
        'delivery_time',
        'type',
        'fk_category_id',
        'fk_brand_id',
        'offer_type',
        'min_amount',
        'coupon_code',
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'discount',
        'expiry_date',
        'uses_limit',
        'model',
        'status',
        'coupon_image',
        'coupon_image_ar',
        'is_hidden'
    ];

    public $sortable = ['id', 'coupon_code', 'title_en', 'description_en', 'expiry_date'];

    public function getCategory() {
        return $this->belongsTo('App\Model\Category', 'fk_category_id');
    }

    public function getBrand() {
        return $this->belongsTo('App\Model\Brand', 'fk_brand_id');
    }

    public function getCouponImage() {
        return $this->belongsTo('App\Model\File', 'coupon_image');
    }

}

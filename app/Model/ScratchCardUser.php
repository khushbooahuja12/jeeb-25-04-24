<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ScratchCardUser extends Model {

    use Sortable;

    protected $table = 'scratch_cards_user';
    protected $fillable = [
        'fk_user_id',
        'fk_scratch_card_id',
        'fk_order_id',
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'image',
        'image_ar',
        'scratch_card_type',
        'type',
        'coupon_code',
        'min_amount',
        'discount',
        'expiry_date',
        'uses_limit',
        'scratched_at',
        'redeem_amount',
        'redeemed_at',
        'status',
        'deleted',
        'created_at',
        'updated_at'
    ];
    public $sortable = ['id', 'name'];

    public function User() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

    public function getScratchCardImage() {
        return $this->belongsTo('App\Model\File', 'image');
    }

    public function getScratchCardImageAr() {
        return $this->belongsTo('App\Model\File', 'image_ar');
    }

}

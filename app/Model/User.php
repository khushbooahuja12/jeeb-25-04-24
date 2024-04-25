<?php

namespace App\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;

class User extends Authenticatable {

    use Notifiable;

    use Sortable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'mobile',
        'country_code',
        'email',
        'user_image',
        'status',
        'nearest_store',
        'ivp',
        'expected_eta',
        'lang_preference',
        'features_enabled',
        'tap_id'
    ];
    public $sortable = ['id', 'name', 'mobile', 'email'];

    public function getUserImage() {
        return $this->belongsTo('App\Model\File', 'user_image');
    }

    public function getStore() {
        return $this->belongsTo('App\Model\Store', 'nearest_store');
    }

    public function getAddress() {
        return $this->hasOne('App\Model\UserAddress', 'fk_user_id');
    }

    public function getDeviceToken() {
        return $this->hasOne('App\Model\UserDeviceDetail', 'fk_user_id');
    }

    public function getOrders() {
        return $this->hasMany('App\Model\Order', 'fk_user_id');
    }

    public function getDeliveredOrdersOnly() {
        return $this->getOrders()->where([
            'status'=>7
        ])->limit(10);
    }

    public function lastUserTracking() {
        return $this->hasMany('App\Model\UserTracking', 'user_id')->latest()->first();
    }

    public function getUserTrackings() {
        return $this->hasMany('App\Model\UserTracking', 'user_id');
    }

    public function getUserTrackingsCartOnly() {
        return $this->getUserTrackings()->whereIn('key',[
            'update_cart',
            'update_cart_bp',
            'make_order',
            'make_order_bp',
        ]);
    }

    public function cartItems() {
        return $this->hasMany('App\Model\UserCart', 'fk_user_id');
    }

    public function getStoreGroup() {
        return $this->belongsTo('App\Model\InstantStoreGroup', 'nearest_store');
    }

}

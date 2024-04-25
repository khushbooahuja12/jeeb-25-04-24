<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'country_code', 'mobile', 'user_image', 'status', 'expected_eta', 'nearest_store', 'lang_preference', 'tap_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getUserImage() {
        return $this->belongsTo('App\Model\File', 'user_image');
    }

    public function validate_mobile($mobile_with_code, $id) {
        $exists = User::where(DB::raw("CONCAT(`country_code`, ' ', `mobile`)"), '=', $mobile_with_code)
                ->exists();
        if ($exists) {
            return true;
        } else {
            return false;
        }
    }

}

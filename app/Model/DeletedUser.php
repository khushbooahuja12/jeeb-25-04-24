<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DeletedUser extends Authenticatable {

    use Notifiable;

    protected $table = 'users_deleted';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'country_code', 'mobile', 'user_image', 'status','nearest_store', 'reason'
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

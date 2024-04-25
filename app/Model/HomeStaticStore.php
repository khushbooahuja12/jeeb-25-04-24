<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class HomeStaticStore extends Authenticatable
{

    use Notifiable;

    protected $table = 'home_static_stores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_name_en',
        'store_name_ar',
        'store_key'
    ];

    public function getHomeStaticEn($home_static_type)
    {
        return $this->hasMany('App\Model\HomeStatic', 'store_key', 'store_key')->where('home_static_type','=',$home_static_type)->where('lang','=','en')->latest()->limit(3)->get();
    }

    public function getHomeStaticAr($home_static_type)
    {
        return $this->hasMany('App\Model\HomeStatic', 'store_key', 'store_key')->where('home_static_type','=',$home_static_type)->where('lang','=','ar')->latest()->limit(3)->get();
    }

}

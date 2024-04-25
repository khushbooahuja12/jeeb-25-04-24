<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class HomeStatic extends Authenticatable
{

    use Notifiable;

    protected $table = 'home_static_file';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'home_static_type',
        'store_key',
        'file_name',
        'IP',
        'lang',
        'home_static_data_feeded',
        'last_processed_at'
    ];

}

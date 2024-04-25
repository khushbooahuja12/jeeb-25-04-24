<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class RecipeHomeStatic extends Authenticatable
{

    use Notifiable;

    protected $table = 'recipe_home_static_file';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_name',
        'IP',
        'lang'
    ];

}

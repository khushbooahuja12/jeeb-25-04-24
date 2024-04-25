<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model {

    protected $table = 'user_preferences';
    protected $fillable = [
        'fk_user_id',
        'is_notification',
        'lang'
    ];
    public $timestamps = false;
}

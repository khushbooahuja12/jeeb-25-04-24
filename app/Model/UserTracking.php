<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserTracking extends Model {

    protected $table = 'user_tracking';
    protected $fillable = [
        'user_id',
        'key',
        'request',
        'response',
    ];

}

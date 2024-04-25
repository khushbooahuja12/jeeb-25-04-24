<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdminSetting extends Model {

    protected $table = 'admin_settings';
    protected $fillable = [
        'key',
        'value'
    ];
    public $timestamps = false;

}

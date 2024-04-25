<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model {

    protected $table = 'password_resets';
    protected $fillable = [
        'email', 'token', 'type', 'created_at'
    ];
    public $timestamps = false;

}

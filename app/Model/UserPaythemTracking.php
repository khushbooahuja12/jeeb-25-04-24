<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaythemTracking extends Model
{
    use HasFactory;

    protected $table = 'user_paythem_tracking';
    protected $fillable = [
        'user_id',
        'key',
        'request',
        'response',
    ];

}

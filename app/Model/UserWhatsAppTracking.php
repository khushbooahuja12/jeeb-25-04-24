<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWhatsAppTracking extends Model
{
    use HasFactory;

    protected $table = 'user_whatsapp_tracking';
    protected $fillable = [
        'user_id',
        'mobile',
        'key',
        'request',
        'response',
    ];

}

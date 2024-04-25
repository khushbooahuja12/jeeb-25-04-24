<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppUser extends Model
{
    use HasFactory;
    
    protected $table = 'whatsapp_users';
    protected $fillable = [
        'name',
        'email',
        'status',
        'lang_preference',
    ];
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotCommand extends Model
{
    use HasFactory;
    protected $table = 'bot_commands';
    protected $fillable = [
        'command',
        'identifier',
        'status_1',
        'status_2',
        'status_3',
        'status_4',
        'status_5',
        'status_6'
    ];

}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TechnicalSupportChat extends Model
{

    protected $table = 'technical_support_chat';
    protected $fillable = [
        'ticket_id',
        'chatbox_type',
        'message_type',
        'message',
        'action'
    ];

    public function getTechnicalSupport()
    {
        return $this->belongsTo('App\Model\TechnicalSupport', 'ticket_id');
    }
}

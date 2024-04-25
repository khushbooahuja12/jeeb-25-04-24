<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model {

    protected $table = 'user_notifications';
    protected $fillable = [
        'fk_user_id',
        'notification_type',
        'notification_title_en',
        'notification_title_ar',
        'notification_text_en',
        'notification_text_ar',
        'data'
    ];

}

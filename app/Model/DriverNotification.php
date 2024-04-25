<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverNotification extends Model {

    protected $table = 'driver_notifications';
    protected $fillable = [
        'fk_driver_id',
        'related_id',
        'notification_type',
        'notification_title_en',
        'notification_title_ar',
        'notification_text_en',
        'notification_text_ar'
    ];

}

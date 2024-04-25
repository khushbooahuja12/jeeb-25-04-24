<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StoreSchedule extends Model
{
    protected $table = 'store_schedules';

    protected $fillable = [
        'fk_store_id',
        'date',
        'day',
        '24hours_open',
        'from',
        'to',
        'cron_triggered_at',
        'deleted'
    ];
    
}

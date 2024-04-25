<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TechnicalSupport extends Model
{

    use Sortable;

    protected $table = 'technical_support';
    protected $fillable = [
        'fk_user_id',
        'fk_order_id',
        'message_type',
        'last_message',
        'notes',
        'status'
    ];
    public $sortable = ['id', 'last_message', 'notes', 'status'];

    public function getUser()
    {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

    public function getOrder()
    {
        return $this->belongsTo('App\Model\Order', 'fk_order_id');
    }
}

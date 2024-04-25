<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CustomerSupportChat extends Model {

    protected $table = 'customer_support_chat';
    protected $fillable = [
        'type',
        'fk_support_id',
        'fk_user_id',
        'from',
        'message',
        'type'
    ];

    public function getSupport() {
        return $this->belongsTo('App\Model\CustomerSupport', 'fk_support_id');
    }

    public function getUser() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

    public function getFile() {
        return $this->belongsTo('App\Model\File', 'message');
    }

}

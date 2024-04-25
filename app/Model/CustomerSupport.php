<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class CustomerSupport extends Model {

    use Sortable;

    protected $table = 'customer_support';
    protected $fillable = [
        'fk_user_id',
        'email',
        'subject',
        'description',
        'screenshots',
        'status'
    ];
    public $sortable = ['id', 'email', 'subject', 'description', 'status'];

    public function getUser() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

}

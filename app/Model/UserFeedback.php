<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class UserFeedback extends Model {

    use Sortable;

    protected $table = 'user_feedbacks';
    protected $fillable = [
        'fk_user_id',
        'experience',
        'description'
    ];
    public $sortable = ['id', 'experience', 'description'];

    public function getUser() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

}

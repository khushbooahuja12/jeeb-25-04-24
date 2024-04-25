<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Notification extends Model {

    use Sortable;

    protected $table = 'notifications';
    protected $fillable = [
        'title',
        'body',
        'deleted',
        'userIds'
    ];
    public $sortable = ['id', 'title', 'body'];

    public function getUsers($userIds) {
        $userIdsAr = explode(',', $userIds);
        $userNames = \App\Model\User::selectRaw("GROUP_CONCAT('+',country_code,mobile) as mobileNums")
                ->whereIn('id', $userIdsAr)
                ->first();
        return $userNames;
    }

}

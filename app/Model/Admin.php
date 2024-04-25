<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Admin extends Model {

    use Sortable;

    protected $table = 'admins';
    protected $fillable = [
        'name',
        'email',
        'fk_role_id',
        'fk_store_id',
        'fk_company_id',
        'password',
        'status'
    ];

    public function getRole()
    {
        return $this->belongsTo('App\Model\Role','fk_role_id');
    }

}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class RolePermission extends Model
{
    use HasFactory;
    use Sortable;

    protected $table = 'role_permissions';
    protected $fillable = [
        'fk_role_id',
        'fk_permission_id',
    ];
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Role extends Model
{
    use HasFactory;
    use Sortable;

    protected $table = 'roles';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status'
    ];

    public function permissions() {
        return $this->belongsToMany("App\Model\Permission", "role_permissions", "fk_role_id", "fk_permission_id")->withTimestamps();
    }
}

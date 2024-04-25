<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Permission extends Model
{
    use HasFactory;
    use Sortable;

    protected $table = 'permissions';
    protected $fillable = [
        'name',
        'slug',
    ];
}

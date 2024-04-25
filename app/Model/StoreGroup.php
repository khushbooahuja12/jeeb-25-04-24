<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreGroup extends Model
{
    use HasFactory;

    protected $table = 'store_groups';

    protected $fillable = [
        'group_id',
        'group_name',
        'fk_store_id',
        'deleted',
        'status'
    ];

}

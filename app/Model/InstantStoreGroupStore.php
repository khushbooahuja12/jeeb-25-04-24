<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstantStoreGroupStore extends Model
{
    use HasFactory;

    protected $table = 'instant_store_group_stores';

    protected $fillable = [
        'fk_group_id',
        'fk_store_id',
    ];
}

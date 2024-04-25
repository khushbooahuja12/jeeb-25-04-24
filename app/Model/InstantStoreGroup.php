<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class InstantStoreGroup extends Model
{
    use HasFactory;
    use Sortable;

    protected $table = 'instant_store_groups';

    protected $fillable = [
        'name',
        'fk_hub_id',
        'status',
        'deleted',
    ];

    public function getStore() {
        return $this->belongsTo('App\Model\Store', 'fk_hub_id');
    }
}

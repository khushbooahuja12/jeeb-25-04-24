<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TwoStepTag extends Model
{
    use Sortable;

    protected $table = '2step_tags';
    protected $fillable = [
        'name_en',
        'name_ar'
    ];

    public $timestamps = false;

    public $sortable = ['id', 'name_en', 'name_ar'];
}

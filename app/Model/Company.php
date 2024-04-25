<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Company extends Model
{
    use HasFactory;
    use Sortable;
    
    protected $table = 'companies';
    protected $fillable = [
        'name',
        'notes',
        'deleted'
    ];

}

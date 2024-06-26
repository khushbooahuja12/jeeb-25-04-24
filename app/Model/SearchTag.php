<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SearchTag extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tag',
        'search_count'
    ];

}

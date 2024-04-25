<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RecipeDiet extends Model
{

    protected $table = 'recipe_diets';
    protected $fillable = [
        'title_en',
        'title_ar',
        'tag'
    ];

}

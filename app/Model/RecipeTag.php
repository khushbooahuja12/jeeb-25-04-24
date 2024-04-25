<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RecipeTag extends Model
{

    protected $table = 'recipe_tags';
    protected $fillable = [
        'title_en',
        'title_ar',
        'tag'
    ];

}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CategoryRecipe extends Model
{
    protected $table = 'recipe_selected_categories';

    protected $fillable = [
        'category_id',
        'recipe_id',
    ];

    public $timestamps = false;
}

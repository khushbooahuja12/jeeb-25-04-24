<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FavoriteRecipe extends Model
{

    protected $table = 'recipe_favorites';
    protected $fillable = [
        'fk_user_id',
        'fk_recipe_id',
        'is_home'
    ];
}

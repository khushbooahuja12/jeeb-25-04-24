<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RecipeVariant extends Model
{

    protected $table = 'recipe_variants';

    protected $fillable = [
        'fk_recipe_id',
        'fk_product_id',
        'base_product_id',
        'base_product_store_id',
        'serving',
        'price',
        'ingredients',
        'pantry_items'
    ];

}

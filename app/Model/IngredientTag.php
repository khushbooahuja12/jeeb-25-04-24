<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientTag extends Model
{
    use HasFactory;

    protected $table = 'recipe_ingredients_tags';

    protected $fillable = [
        'recipe_id',
        'desc_en',
        'desc_ar',
        'tag',
        'image_url',
        'pantry_item',
        'unit',
        'price',
        'fk_product_id',
        'base_product_id',
        'base_product_store_id'
    ];

}

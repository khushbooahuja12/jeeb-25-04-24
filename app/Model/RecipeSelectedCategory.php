<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RecipeSelectedCategory extends Model
{

    protected $table = 'recipe_selected_categories';

    protected $fillable = [
        'category_id',
        'recipe_id',
    ];

    public $timestamps = false;

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function category()
    {
        return $this->belongsTo(RecipeCategory::class, 'category_id');
    }
}

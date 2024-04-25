<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Laravel\Scout\Searchable;

class Recipe extends Model
{
    use Sortable;

    use Searchable;

    protected $table = 'recipes';

    protected $fillable = [
        'recipe_name_en',
        'recipe_name_ar',
        'duration',
        'serving',
        'nutrition',
        'recipe_desc_en',
        'recipe_desc_ar',
        'recipe_img',
        'recipe_img_url',
        '_tags',
        'homepage_tag_en',
        'homepage_tag_ar',
        'recommended',
        'veg',
        'is_featured',
        'is_home',
        'active',
        'deleted',
    ];

    public $sortable = ['id', 'recipe_name_en', 'recipe_name_ar', 'duration', 'serving', 'nutrition', 'recipe_desc_en', 'recipe_desc_ar'];

    public function searchableAs()
    {
        $table_index = (env('APP_ENV')=='production') ? 'recipes' : 'recipes_dev';
        return $table_index;
    }

    public function shouldBeSearchable()
    {
        return $this->deleted == 0 && $this->active == 1;
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Applies Scout Extended default transformations:
        $array = $this->transform($array);

        // Add an extra attribute:
        $array['_tags'] = explode(',', $array['_tags']);
        $array['variants'] = $this->variants->map(function ($data) {
            return $data;
        })->toArray();
        $array['ingredients'] = $this->ingredients->map(function ($data) {
            return $data;
        })->toArray();
        // if (isset($array['variants']) && isset($array['variants']['ingredients'])) {
        //     $array['variants']['ingredients'] = json_decode($array['variants']['ingredients']);
        // }

        return $array;
    }

    public function ingredients()
    {
        return $this->hasMany(IngredientTag::class, 'recipe_id', 'id');
    }

    public function getRecipeImage()
    {
        return $this->belongsTo('App\Model\File', 'recipe_img');
    }
    
    public function categories()
    {
        return $this->hasManyThrough(RecipeCategory::class,RecipeSelectedCategory::class, 'category_id', 'id', 'recipe_id', 'id');
    }
    
    public function variants()
    {
        return $this->hasMany(RecipeVariant::class, 'fk_recipe_id', 'id');
    }
    
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class RecipeCategory extends Model
{

    use Sortable;

    protected $table = 'recipe_categories';

    protected $fillable = [
        'name_en',
        'name_ar',
        'tag',
        'image',
    ];

    public $sortable = ['id', 'name_en', 'name_ar'];

    public $timestamps = false;

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class);
    }
}

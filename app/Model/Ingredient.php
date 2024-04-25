<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $table = 'recipe_ingredients';

    protected $fillable = [
        'recipe_id',
        'product_id',
        'product_id2',
        'product_id3',
        'quantity',
        'quantity2',
        'quantity3',
        'desc_en',
        'desc_ar',
        'available_product_id'
    ];

    public $timestamps = false;
}

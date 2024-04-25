<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    use HasFactory;

    protected $table = 'recipe_steps';

    protected $fillable = [
        'recipe_id',
        'step_en',
        'step_ar',
        'time',
        'ingredients'
    ];

    public $timestamps = false;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'url',
    ];

    public function recipeDetails()
    {
        return $this->hasOne(Recipe::class);
    }
}

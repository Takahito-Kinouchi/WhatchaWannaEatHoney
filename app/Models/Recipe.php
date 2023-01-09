<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'title',
        'comment',
        'review_count',
    ];

    public function url()
    {
        return $this->belongsTo(RecipeUrl::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class, 'recipe_url_id', 'recipe_url_id');
    }
}

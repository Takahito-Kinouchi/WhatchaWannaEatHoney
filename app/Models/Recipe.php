<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'title',
        'review_count',
    ];

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }
}

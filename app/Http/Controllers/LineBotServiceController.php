<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\LineBotService;
use Illuminate\Http\Request;

class LineBotServiceController extends Controller
{

    public function suggestRecipes(Request $request)
    {
        $messageEvent = $request->collect('events')->first(function ($event) {
            return $event['type'] === 'message';
        });
        $ingredientKeyWords = mb_convert_kana($messageEvent['message']['text'], 's');
        $ingredientKeyWordList = explode(' ', $ingredientKeyWords);

        $recipesMatched = Recipe::query()
            ->with('ingredients');
        
        foreach ($ingredientKeyWordList as $ingredientKeyWord) {
            $recipesMatched = $recipesMatched->whereHas('ingredients', function ($ingredient) use ($ingredientKeyWord) {
                $ingredient->where('name', 'like', '%' . $ingredientKeyWord . '%');
            });
        }
        
        $suggestedRecipes = $recipesMatched
            ->orderByDesc('review_count')
            ->take(15)
            ->get()
            ->random(5);

        LineBotService::suggestRecipes($messageEvent['replyToken'], $suggestedRecipes);
    }
}

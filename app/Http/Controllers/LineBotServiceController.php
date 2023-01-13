<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use App\Services\LineBotService;

final class LineBotServiceController extends Controller
{

    /**
     *
     * @param Request $request
     *
     * @return void
     */
    public function suggestRecipes(Request $request): void
    {
        $lineBot = new LineBotService();

        $messageEvent = $request->collect('events')->first(function ($event) {
            return $event['type'] === 'message';
        });
        $ingredientKeyWords = mb_convert_kana($messageEvent['message']['text'], 's');
        
        if ($ingredientKeyWords === 'おまかせ') {
            $suggestedRecipe = Recipe::query()
                ->inRandomOrder()
                ->first();
            $lineBot->randomRecipe($messageEvent['replyToken'], $suggestedRecipe);
            return;
        }

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
            ->random(4);

        $lineBot->suggestRecipes($messageEvent['replyToken'], $suggestedRecipes);
    }
}

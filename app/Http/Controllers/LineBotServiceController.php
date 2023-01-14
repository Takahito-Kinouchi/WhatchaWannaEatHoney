<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use App\Services\LineBotService;
use App\Http\Requests\RecipeSuggestRequest;

final class LineBotServiceController extends Controller
{

    /**
     *
     * @param Request $request
     *
     * @return void
     */
    public function suggestRecipes(RecipeSuggestRequest $request)
    {
        $lineBot = new LineBotService();

        $messageEvent = $request->collect('events')->first(function ($event) {
            return $event['type'] === 'message';
        });
        $replyToken = $messageEvent['replyToken'];

        $ingredientKeyWords = mb_convert_kana($messageEvent['message']['text'], 's');
        if ($ingredientKeyWords === 'おまかせ') {
            $suggestedRecipes = Recipe::query()
            ->inRandomOrder()
            ->take(3)
            ->get();
            $lineBot->showRandomRecipe($replyToken, $suggestedRecipes);
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

        $recipeCount = $recipesMatched->orderByDesc('review_count')->count();

        if ($recipeCount <= 0) {
            $lineBot->noRecipeMessage($messageEvent['replyToken']);
            return;
        }

        $suggestedRecipes = $recipesMatched
            ->orderByDesc('review_count')
            ->when(15 <= $recipeCount, function ($query) {
                return $query->take(15)->get()->random(3);
            })
            ->when(3 < $recipeCount && $recipeCount < 15, function ($query) {
                return $query->get()->random(3);
            })
            ->when(0 < $recipeCount && $recipeCount <= 3, function ($query) {
                return $query->get();
            });

        $lineBot->suggestRecipes($replyToken, $ingredientKeyWordList, $suggestedRecipes);
    }
}
